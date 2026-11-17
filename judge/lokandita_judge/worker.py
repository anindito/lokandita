"""Worker judge — KONTRAK.md §6, SPEC.md §10.

    python -m lokandita_judge.worker

Basis data adalah sumber kebenaran; Redis hanya membawa id pekerjaan.

  utas utama   BRPOP antrean normal lalu low, menyerahkan id ke kolam utas
  kolam utas   JUDGE_CONCURRENCY pekerjaan sekaligus; tiap pekerjaan mengklaim
               barisnya dengan UPDATE bersyarat QUEUED -> RUNNING
  pemindai     tiap 5 detik: mengantrekan ulang baris QUEUED yang tidak ada di
               Redis, menulis denyut, memulihkan baris RUNNING yang macet
  penyapu      tiap 30 detik: membuang kontainer judge yang masa hidupnya lewat

Worker menolak mulai bila host tidak bisa menjamin batas kotak pasir.
Log adalah satu baris JSON per peristiwa; tidak pernah memuat kode sumber,
masukan, keluaran program, atau rahasia.
"""

from __future__ import annotations

import json
import os
import re
import signal
import subprocess
import sys
import threading
import time
import traceback
from concurrent.futures import ThreadPoolExecutor

import pymysql
import redis

from . import citra, konfig, kotak, mesin, sambungan, soal

ANTREAN = {"normal": "lokandita:judge:normal", "low": "lokandita:judge:low"}
KUNCI_DENYUT = "lokandita:judge:worker"
UMUR_DENYUT_S = 15
JEDA_PINDAI_S = 5
JEDA_SAPU_S = 30
POLA_ID = re.compile(r"[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}")
PERCOBAAN_MAKS = 2  # attempts > 2 pada galat infrastruktur -> FAILED (KONTRAK.md §6)


def catat(peristiwa: str, **data) -> None:
    baris = {"waktu": time.strftime("%Y-%m-%dT%H:%M:%SZ", time.gmtime()), "peristiwa": peristiwa, **data}
    print(json.dumps(baris, ensure_ascii=False), flush=True)


def commit_berjalan() -> str:
    """Commit pohon kerja saat worker mulai: itulah kode yang sedang berjalan."""
    try:
        hasil = subprocess.run(["git", "-C", str(kotak.AKAR_JUDGE.parent), "rev-parse", "HEAD"],
                               capture_output=True, text=True, timeout=10)
        return hasil.stdout.strip() if hasil.returncode == 0 else "tidak-diketahui"
    except (OSError, subprocess.TimeoutExpired):
        return "tidak-diketahui"


class Db:
    """Satu sambungan MySQL per utas; tersambung ulang sekali bila sambungan putus."""

    def __init__(self, k: konfig.Konfig):
        self._k = k
        self._lokal = threading.local()

    def _jalankan(self, sql: str, argumen: tuple, ambil: bool):
        for percobaan in (1, 2):
            try:
                sambung = getattr(self._lokal, "sambung", None)
                if sambung is None:
                    sambung = self._lokal.sambung = sambungan.mysql(self._k)
                with sambung.cursor() as kursor:
                    jumlah = kursor.execute(sql, argumen)
                    return kursor.fetchall() if ambil else jumlah
            except (pymysql.err.OperationalError, pymysql.err.InterfaceError):
                self._lokal.sambung = None
                if percobaan == 2:
                    raise
        raise AssertionError("tidak tercapai")

    def baca(self, sql: str, argumen: tuple = ()) -> list[dict]:
        return list(self._jalankan(sql, argumen, True))

    def ubah(self, sql: str, argumen: tuple = ()) -> int:
        return self._jalankan(sql, argumen, False)


def kemajuan(keadaan: str, sekarang: int = 0, total: int = 0) -> str:
    return json.dumps({"state": keadaan, "current": sekarang, "total": total})


class Worker:
    def __init__(self, k: konfig.Konfig):
        self.k = k
        self.db = Db(k)
        self.r = sambungan.antrean(k)
        self.berhenti = threading.Event()
        self.slot = threading.Semaphore(k.konkurensi)
        self.sedang: set[str] = set()
        self.kunci = threading.Lock()
        self.commit = commit_berjalan()
        self.mulai_pada = int(time.time())

    # ---- satu pekerjaan --------------------------------------------------------

    def kerjakan(self, id_: str) -> None:
        if not POLA_ID.fullmatch(id_):
            catat("id_ditolak", alasan="bukan UUID")
            return
        # Klaim bersyarat: id yang sama dua kali di Redis, atau pekerjaan yang
        # sudah selesai, tidak dikerjakan lagi.
        diklaim = self.db.ubah(
            "UPDATE judge_jobs SET status = 'RUNNING', started_at = UTC_TIMESTAMP(), finished_at = NULL,"
            " attempts = attempts + 1, progress = %s WHERE id = %s AND status = 'QUEUED'",
            (kemajuan("queued"), id_),
        )
        if diklaim == 0:
            return
        with self.kunci:
            self.sedang.add(id_)
        mulai = time.time()
        try:
            baris = self.db.baca("SELECT kind, payload, attempts, created_by FROM judge_jobs WHERE id = %s", (id_,))[0]
            try:
                status, hasil = self.proses(id_, baris["kind"], baris["payload"], baris["created_by"])
            except Exception as e:  # galat infrastruktur atau kesalahan worker sendiri: diulang
                self.gagal_infrastruktur(id_, baris["attempts"], e)
                return
            self.simpan(id_, status, hasil)
            catat("selesai", job=id_, kind=baris["kind"], status=status,
                  verdict=hasil.get("verdict", hasil.get("error")), ms=int((time.time() - mulai) * 1000))
        finally:
            with self.kunci:
                self.sedang.discard(id_)

    def proses(self, id_: str, kind: str, payload_mentah, pembuat: str | None = None) -> tuple[str, dict]:
        try:
            payload = json.loads(payload_mentah) if isinstance(payload_mentah, (str, bytes)) else payload_mentah
        except ValueError:
            payload = None
        if not isinstance(payload, dict):
            return "FAILED", {"error": "invalid_payload", "message": "payload harus obyek JSON"}

        def maju(keadaan: str, sekarang: int, total: int) -> None:
            try:
                self.db.ubah("UPDATE judge_jobs SET progress = %s WHERE id = %s AND status = 'RUNNING'",
                             (kemajuan(keadaan, sekarang, total), id_))
            except pymysql.err.MySQLError as e:
                catat("kemajuan_tidak_tertulis", job=id_, galat=type(e).__name__)

        try:
            if kind == "RUN":
                stdin = payload.get("stdin")
                if stdin is not None and not isinstance(stdin, str):
                    return "FAILED", {"error": "invalid_payload", "message": "stdin harus teks"}
                if payload.get("instance_id") is not None:
                    if stdin is not None:
                        return "FAILED", {"error": "invalid_payload", "message": "stdin dan instance_id tidak boleh bersama"}
                    return "DONE", self.contoh(payload, pembuat, maju)
                return "DONE", mesin.jalankan_sekali(payload.get("language"), payload.get("source"), stdin or "", maju,
                                                     payload.get("flags"))
            if kind == "SUBMIT":
                return "DONE", self.nilai(id_, payload, pembuat, maju)
            if kind == "VALIDATE":
                return "DONE", soal.validasi(payload, maju)
            if kind == "GENERATE":
                return self.bangkit(payload)
            if kind == "ANALYZE":
                return "DONE", soal.periksa(payload)
        except mesin.MasukanSalah as e:
            return "FAILED", {"error": "invalid_payload", "message": str(e)}
        except kotak.AnalisisGagal as e:
            return "FAILED", {"error": "analysis_failed", "message": str(e)}
        return "FAILED", {"error": "unsupported_kind", "message": f"{kind} tidak dikenal worker ini"}

    def _instans(self, payload: dict, pembuat: str | None) -> dict:
        """Instans untuk RUN pada tes contoh dan SUBMIT (KONTRAK.md §6).

        Worker memeriksa sendiri bahwa instans itu milik pembuat pekerjaan: kalau
        pun web keliru, tes seorang mahasiswa tidak dipakai menilai kode orang lain.
        Batas waktu dan mode banding diambil dari versi soal, bukan dari payload."""
        iid = payload.get("instance_id")
        if not isinstance(iid, int) or isinstance(iid, bool):
            raise mesin.MasukanSalah("instance_id harus bilangan bulat")
        baris = self.db.baca(
            "SELECT i.user_id, i.tests, v.language, v.comparison_mode, v.time_limit_ms, v.samples_count, v.required_in_submission"
            " FROM problem_instances i JOIN problem_versions v ON v.id = i.problem_version_id WHERE i.id = %s", (iid,))
        if not baris or pembuat is None or baris[0]["user_id"] != pembuat:
            raise mesin.MasukanSalah("instance_id tidak ada atau bukan milik pembuat pekerjaan")
        b = baris[0]
        if payload.get("language") != b["language"]:
            raise mesin.MasukanSalah("language tidak sama dengan bahasa soal")
        tes = [mesin.Tes(t["in"], t["out"]) for t in json.loads(b["tests"])]
        n = int(b["samples_count"])
        syarat = b["required_in_submission"]
        return {
            "contoh": tes[:n], "tersembunyi": tes[n:], "mode": b["comparison_mode"], "cpu_ms": int(b["time_limit_ms"]),
            "syarat": json.loads(syarat) if isinstance(syarat, (str, bytes)) else (syarat or []),
        }

    def contoh(self, payload: dict, pembuat: str | None, maju) -> dict:
        """RUN dengan instance_id: tes contoh instans itu saja; tes tersembunyi tidak disentuh."""
        ins = self._instans(payload, pembuat)
        return mesin.jalankan_contoh(payload.get("language"), payload.get("source"), ins["contoh"], ins["mode"],
                                     ins["cpu_ms"], maju, payload.get("flags"))

    def nilai(self, id_: str, payload: dict, pembuat: str | None, maju) -> dict:
        """SUBMIT: semua tes tersembunyi, lalu konsep yang disyaratkan (SPEC.md §7, §9.2).

        Hasilnya tidak memuat masukan, keluaran yang diharapkan, atau keluaran
        program. Worker tidak menyentuh tabel submissions; web yang menyalin
        hasil ini dan menghitung nilai."""
        sid = payload.get("submission_id")
        if not isinstance(sid, int) or isinstance(sid, bool):
            raise mesin.MasukanSalah("submission_id harus bilangan bulat")
        ins = self._instans(payload, pembuat)
        bahasa, sumber = payload.get("language"), payload.get("source")
        hasil = mesin.nilai_tes(bahasa, sumber, ins["tersembunyi"], ins["mode"], cpu_ms=ins["cpu_ms"], maju=maju,
                                bendera=payload.get("flags"))
        hasil["concepts"], hasil["missing"] = None, []
        if hasil["verdict"] == "AC" and ins["syarat"]:
            try:
                kode = [t["code"] for t in kotak.analisis(bahasa, sumber)]
                hasil["concepts"] = kode
                hasil["missing"] = [s for s in ins["syarat"] if s not in kode]
                if hasil["missing"]:
                    hasil["verdict"] = "CONCEPT_NOT_MET"
            except kotak.AnalisisGagal as e:
                # Kegagalan pengurai kita tidak dibebankan kepada mahasiswa.
                hasil["analysis_failed"] = True
                catat("analisis_kiriman_gagal", job=id_, kiriman=sid, pesan=str(e)[:200])
        return hasil

    def bangkit(self, payload: dict) -> tuple[str, dict]:
        """GENERATE: instans satu mahasiswa untuk satu soal pertemuan (SPEC.md §8.2).

        Tes ditulis langsung ke problem_instances; hasil pekerjaan tidak memuatnya,
        karena hasil itu bisa dibaca pembuat pekerjaannya. Idempoten: instans yang
        sudah ada tidak pernah dibuat ulang."""
        versi_id, mp_id, user, seed = (payload.get(k) for k in ("problem_version_id", "meeting_problem_id", "user_id", "seed"))
        if not all(isinstance(x, int) and not isinstance(x, bool) for x in (versi_id, mp_id, seed)) \
                or not isinstance(user, str) or not POLA_ID.fullmatch(user):
            return "FAILED", {"error": "invalid_payload", "message": "GENERATE butuh problem_version_id, meeting_problem_id, seed (bilangan) dan user_id (UUID)"}
        if self.db.baca("SELECT id FROM problem_instances WHERE user_id = %s AND meeting_problem_id = %s", (user, mp_id)):
            return "DONE", {"ok": True, "existed": True}
        versi = self.db.baca(
            "SELECT language, generator, reference_solution, samples_count, hidden_count, time_limit_ms, status"
            " FROM problem_versions WHERE id = %s", (versi_id,))
        if not versi:
            return "FAILED", {"error": "invalid_payload", "message": "problem_version_id tidak ada"}
        if versi[0]["status"] != "APPROVED":
            return "FAILED", {"error": "invalid_payload", "message": "versi soal belum disetujui"}
        try:
            tes = soal.bangkit(versi[0], seed)
        except soal.BangkitGagal as e:
            catat("bangkit_gagal", versi=versi_id, alasan=e.alasan)
            return "FAILED", {"error": "generation_failed", "reason": e.alasan}
        # INSERT IGNORE: bila dua pekerjaan untuk instans yang sama berpacu, yang pertama menang.
        self.db.ubah(
            "INSERT IGNORE INTO problem_instances (user_id, meeting_problem_id, problem_version_id, seed, tests, created_at)"
            " VALUES (%s, %s, %s, %s, %s, UTC_TIMESTAMP())",
            (user, mp_id, versi_id, seed, json.dumps(tes, ensure_ascii=False)),
        )
        return "DONE", {"ok": True, "existed": False, "tests": len(tes)}

    def simpan(self, id_: str, status: str, hasil: dict) -> None:
        for percobaan in range(1, 6):
            try:
                self.db.ubah(
                    "UPDATE judge_jobs SET status = %s, result = %s, progress = %s, finished_at = UTC_TIMESTAMP()"
                    " WHERE id = %s AND status = 'RUNNING'",
                    (status, json.dumps(hasil), kemajuan("done", 1, 1), id_),
                )
                return
            except pymysql.err.MySQLError as e:
                catat("hasil_tidak_tertulis", job=id_, percobaan=percobaan, galat=type(e).__name__)
                time.sleep(2)
        # Baris tetap RUNNING; pemindai akan memulihkannya (pekerjaan idempoten).

    def gagal_infrastruktur(self, id_: str, percobaan: int, galat: Exception) -> None:
        catat("galat_infrastruktur", job=id_, percobaan=percobaan, galat=type(galat).__name__, pesan=str(galat)[:500])
        if not isinstance(galat, kotak.GalatInfrastruktur):
            catat("jejak", job=id_, jejak=traceback.format_exc()[-2000:])
        if percobaan > PERCOBAAN_MAKS:
            self.simpan(id_, "FAILED", {"error": "judge_unavailable"})
            catat("selesai", job=id_, status="FAILED", verdict="judge_unavailable")
        else:
            # Kembali ke QUEUED; pemindai mengantrekannya lagi dalam beberapa detik.
            self.db.ubah("UPDATE judge_jobs SET status = 'QUEUED', progress = %s WHERE id = %s AND status = 'RUNNING'",
                         (kemajuan("queued"), id_))

    def _kerjakan_aman(self, id_: str) -> None:
        try:
            self.kerjakan(id_)
        except Exception as e:
            catat("pekerjaan_terhenti", job=id_, galat=type(e).__name__, pesan=str(e)[:300])
        finally:
            self.slot.release()

    # ---- pemulihan, pemindai, penyapu --------------------------------------------

    def pulihkan(self, kondisi: str, kecuali: set[str] = frozenset()) -> int:
        """Baris RUNNING tanpa pemilik: kembali ke QUEUED, atau FAILED bila sudah diulang dua kali."""
        jumlah = 0
        for baris in self.db.baca(f"SELECT id, priority, attempts FROM judge_jobs WHERE status = 'RUNNING' AND {kondisi}"):
            if baris["id"] in kecuali:
                continue
            if baris["attempts"] > PERCOBAAN_MAKS:
                self.simpan(baris["id"], "FAILED", {"error": "judge_unavailable"})
            elif self.db.ubah("UPDATE judge_jobs SET status = 'QUEUED', progress = %s WHERE id = %s AND status = 'RUNNING'",
                              (kemajuan("queued"), baris["id"])):
                self.r.lpush(ANTREAN[baris["priority"]], baris["id"])
            jumlah += 1
        return jumlah

    def pindai(self) -> None:
        baris = self.db.baca(
            "SELECT id, priority FROM judge_jobs WHERE status = 'QUEUED'"
            " AND created_at < UTC_TIMESTAMP() - INTERVAL 10 SECOND ORDER BY created_at LIMIT 2000"
        )
        if baris:
            di_redis = set(self.r.lrange(ANTREAN["normal"], 0, -1)) | set(self.r.lrange(ANTREAN["low"], 0, -1))
            for b in baris:
                if b["id"] not in di_redis:
                    self.r.lpush(ANTREAN[b["priority"]], b["id"])
                    catat("diantrekan_ulang", job=b["id"], antrean=b["priority"])
        with self.kunci:
            sedang = set(self.sedang)
        macet = self.pulihkan("started_at < UTC_TIMESTAMP() - INTERVAL 15 MINUTE", sedang)
        if macet:
            catat("dipulihkan", jumlah=macet, sebab="RUNNING lebih dari 15 menit tanpa pemilik")

    def denyut(self) -> None:
        self.r.set(KUNCI_DENYUT, json.dumps({
            "commit": self.commit, "pid": os.getpid(), "mulai": self.mulai_pada, "waktu": int(time.time()),
            "konkurensi": self.k.konkurensi, "image": citra.tag(), "skema": self.k.db_skema,
        }), ex=UMUR_DENYUT_S)

    def _berkala(self, nama: str, jeda: float, kerja) -> None:
        while not self.berhenti.is_set():
            try:
                kerja()
            except Exception as e:
                catat("tugas_berkala_gagal", tugas=nama, galat=type(e).__name__, pesan=str(e)[:300])
            self.berhenti.wait(jeda)

    # ---- hidup dan mati ------------------------------------------------------------

    def jalan(self) -> int:
        kotak.INSTANS = self.k.db_skema
        kotak.periksa_host()  # gagal tertutup: melempar bila host tidak aman
        self.db.baca("SELECT 1 FROM judge_jobs LIMIT 1")
        self.r.ping()

        sisa = kotak.bersihkan_instans()
        dipulihkan = self.pulihkan("1 = 1")
        catat("mulai", commit=self.commit, konkurensi=self.k.konkurensi, image=citra.tag(), skema=self.k.db_skema,
              redis_db=self.k.redis_db, kontainer_lama_dibuang=sisa, pekerjaan_dipulihkan=dipulihkan)

        signal.signal(signal.SIGTERM, lambda *_: self.berhenti.set())
        signal.signal(signal.SIGINT, lambda *_: self.berhenti.set())

        def pindai_dan_denyut() -> None:
            self.denyut()
            self.pindai()

        for nama, jeda, kerja in [("pindai", JEDA_PINDAI_S, pindai_dan_denyut), ("sapu", JEDA_SAPU_S, kotak.sapu)]:
            threading.Thread(target=self._berkala, args=(nama, jeda, kerja), daemon=True).start()

        kolam = ThreadPoolExecutor(max_workers=self.k.konkurensi, thread_name_prefix="job")
        while not self.berhenti.is_set():
            # Slot diambil sebelum BRPOP: id tetap di Redis sampai ada utas yang
            # bebas, sehingga antrean normal selalu didahulukan.
            if not self.slot.acquire(timeout=1):
                continue
            try:
                butir = self.r.brpop([ANTREAN["normal"], ANTREAN["low"]], timeout=1)
            except redis.RedisError as e:
                self.slot.release()
                catat("redis_bermasalah", galat=type(e).__name__)
                self.berhenti.wait(2)
                continue
            if butir is None:
                self.slot.release()
                continue
            kolam.submit(self._kerjakan_aman, butir[1])

        catat("berhenti", menunggu=len(self.sedang))
        kolam.shutdown(wait=True)
        try:
            denyut = json.loads(self.r.get(KUNCI_DENYUT) or "{}")
            if denyut.get("pid") == os.getpid():
                self.r.delete(KUNCI_DENYUT)
        except (redis.RedisError, ValueError):
            pass
        catat("selesai_berhenti")
        return 0


def main() -> int:
    try:
        k = konfig.muat()
    except konfig.KonfigSalah as e:
        catat("tidak_mulai", sebab=str(e))
        return 78  # EX_CONFIG
    try:
        return Worker(k).jalan()
    except kotak.GalatInfrastruktur as e:
        catat("tidak_mulai", sebab=str(e))
        return 69  # EX_UNAVAILABLE
    except (pymysql.err.MySQLError, redis.RedisError) as e:
        catat("tidak_mulai", sebab=f"{type(e).__name__}: tidak bisa memakai MySQL atau Redis")
        return 69


if __name__ == "__main__":
    sys.exit(main())
