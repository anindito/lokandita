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

from . import citra, konfig, kotak, mesin, sambungan

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
            baris = self.db.baca("SELECT kind, payload, attempts FROM judge_jobs WHERE id = %s", (id_,))[0]
            try:
                status, hasil = self.proses(id_, baris["kind"], baris["payload"])
            except Exception as e:  # galat infrastruktur atau kesalahan worker sendiri: diulang
                self.gagal_infrastruktur(id_, baris["attempts"], e)
                return
            self.simpan(id_, status, hasil)
            catat("selesai", job=id_, kind=baris["kind"], status=status,
                  verdict=hasil.get("verdict", hasil.get("error")), ms=int((time.time() - mulai) * 1000))
        finally:
            with self.kunci:
                self.sedang.discard(id_)

    def proses(self, id_: str, kind: str, payload_mentah) -> tuple[str, dict]:
        try:
            payload = json.loads(payload_mentah) if isinstance(payload_mentah, (str, bytes)) else payload_mentah
        except ValueError:
            payload = None
        if not isinstance(payload, dict):
            return "FAILED", {"error": "invalid_payload", "message": "payload harus obyek JSON"}

        if kind != "RUN":
            # SUBMIT, GENERATE, VALIDATE, ANALYZE membutuhkan tabel tahap 8–9.
            return "FAILED", {"error": "unsupported_kind", "message": f"{kind} belum didukung worker ini"}
        if payload.get("instance_id") is not None:
            return "FAILED", {"error": "unsupported_kind", "message": "RUN dengan instance_id belum didukung worker ini"}

        def maju(keadaan: str, sekarang: int, total: int) -> None:
            try:
                self.db.ubah("UPDATE judge_jobs SET progress = %s WHERE id = %s AND status = 'RUNNING'",
                             (kemajuan(keadaan, sekarang, total), id_))
            except pymysql.err.MySQLError as e:
                catat("kemajuan_tidak_tertulis", job=id_, galat=type(e).__name__)

        stdin = payload.get("stdin")
        if stdin is not None and not isinstance(stdin, str):
            return "FAILED", {"error": "invalid_payload", "message": "stdin harus teks"}
        try:
            return "DONE", mesin.jalankan_sekali(payload.get("language"), payload.get("source"), stdin or "", maju)
        except mesin.MasukanSalah as e:
            return "FAILED", {"error": "invalid_payload", "message": str(e)}

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
