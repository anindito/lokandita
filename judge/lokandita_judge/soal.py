"""Pekerjaan judge untuk soal (KONTRAK.md §6, SPEC.md §8, §9).

  validasi   VALIDATE: sebelum sebuah soal disimpan — solusi referensi terkompilasi,
             hanya memakai konsep yang diizinkan, generator deterministik, dan
             solusi referensi menyelesaikan semua tes dari 20 seed acak
  bangkit    GENERATE: tes satu mahasiswa untuk satu soal, dari seed-nya
  periksa    ANALYZE: konsep yang dipakai sebuah kode sumber

Generator dan solusi referensi adalah kode dosen; semua tesnya dijalankan di
satu kontainer (kotak.jalankan_generator, kotak.jalankan_kelompok). Hasil
VALIDATE dan GENERATE tidak pernah memuat isi tes.
"""

from __future__ import annotations

import re
import secrets
from typing import Callable

from . import banding, kotak
from .mesin import BAHASA, BATAS_SUMBER, MasukanSalah

JUMLAH_SEED = 20          # SPEC.md §8.3: semua tes dari 20 seed acak
SEED_ULANG = 3            # seed yang dijalankan tiga kali untuk memeriksa determinisme
BATAS_SAMPEL = (2, 3)
BATAS_TERSEMBUNYI = (10, 50)
BATAS_WAKTU_MS = (100, 10_000)
SEED_MAKS = 2**63 - 1

GALAT_GCC = re.compile(r"main\.c(?:pp)?:(\d+):(\d+): (?:fatal )?error: (.*)")
BARIS_PYTHON = re.compile(r'File "/src/(?:gen|main)\.py", line (\d+)')
BARIS_SINTAKS = re.compile(r'File "main\.py", line (\d+)')


def _teks(x, nama: str, maks: int = BATAS_SUMBER) -> str:
    if not isinstance(x, str) or not x.strip():
        raise MasukanSalah(f"{nama} kosong")
    if len(x.encode("utf-8")) > maks:
        raise MasukanSalah(f"{nama} lebih dari {maks // 1024} KB")
    return x


def _bulat(x, nama: str, batas: tuple[int, int]) -> int:
    if not isinstance(x, int) or isinstance(x, bool) or not batas[0] <= x <= batas[1]:
        raise MasukanSalah(f"{nama} harus bilangan bulat {batas[0]}–{batas[1]}")
    return x


def _daftar_kode(x, nama: str) -> list[str]:
    if not isinstance(x, list) or not all(isinstance(k, str) for k in x):
        raise MasukanSalah(f"{nama} harus daftar kode konsep")
    return x


def _ekor(stderr: bytes) -> str:
    """Baris terakhir stderr yang tidak kosong: pesan galat program, tanpa jejak panjang."""
    baris = [b.strip() for b in stderr.decode("utf-8", "replace").splitlines() if b.strip()]
    return baris[-1][:300] if baris else ""


def _baris_python(stderr: bytes) -> int | None:
    cocok = BARIS_PYTHON.findall(stderr.decode("utf-8", "replace"))
    return int(cocok[-1]) if cocok else None


def _alasan_kompilasi(bahasa: str, k: kotak.HasilKompilasi) -> dict:
    alasan = {"code": "reference_compile_error", "where": "reference_solution", "message": ""}
    if k.alasan:
        alasan["message"] = {"waktu_habis": "kompilasi melewati 10 detik", "memori_habis": "kompilasi kehabisan memori",
                             "keluaran_terlalu_besar": "log kompiler terlalu besar"}[k.alasan]
        return alasan
    if bahasa == "python":
        cocok = BARIS_SINTAKS.search(k.log)
        if cocok:
            alasan["line"] = int(cocok.group(1))
        alasan["message"] = _ekor(k.log.encode())
    else:
        cocok = GALAT_GCC.search(k.log)
        if cocok:
            alasan["line"], alasan["column"], alasan["message"] = int(cocok.group(1)), int(cocok.group(2)), cocok.group(3)[:300]
        else:
            alasan["message"] = _ekor(k.log.encode())
    return alasan


def _alasan_jalan(kode: str, di: str, h: kotak.HasilKelompok, seed: int, indeks: int) -> dict:
    alasan = {"code": kode, "where": di, "verdict": h.status, "seed": seed, "index": indeks, "message": _ekor(h.stderr)}
    baris = _baris_python(h.stderr)
    if baris is not None:
        alasan["line"] = baris
    return alasan


def _bangkitkan(generator: str, seed: list[int], n: int, dir_job, alasan: list[dict]) -> list[bytes] | None:
    """Masukan tes untuk tiap seed × INDEX. None (dan satu alasan) bila generator gagal."""
    try:
        hasil = kotak.jalankan_generator(generator, seed, n, dir_job)
    except kotak.KelompokTerlaluBesar:
        alasan.append({"code": "generator_output_too_large", "where": "generator",
                       "message": f"seluruh masukan tes melewati {kotak.BATAS_KELOMPOK // (1024 * 1024)} MB"})
        return None
    for h in hasil:
        s, i = seed[h.nomor // n], h.nomor % n
        if h.status == "OLE":
            alasan.append({"code": "generator_output_too_large", "where": "generator", "seed": s, "index": i,
                           "message": f"satu masukan tes melewati {kotak.BATAS_MASUKAN_TES // 1024} KB"})
            return None
        if h.status != "OK":
            alasan.append(_alasan_jalan("generator_failed", "generator", h, s, i))
            return None
        try:
            h.stdout.decode("utf-8")
        except UnicodeDecodeError:
            alasan.append({"code": "generator_output_not_utf8", "where": "generator", "seed": s, "index": i, "message": ""})
            return None
    return [h.stdout for h in hasil]


def validasi(payload: dict, maju: Callable[[str, int, int], None] | None = None) -> dict:
    """VALIDATE. Mengembalikan { ok, reasons[], warnings[], concepts[], tests, max_cpu_ms }."""
    maju = maju or (lambda *_: None)
    bahasa = payload.get("language")
    if bahasa not in BAHASA:
        raise MasukanSalah("language harus c, cpp atau python")
    generator = _teks(payload.get("generator"), "generator")
    referensi = _teks(payload.get("reference_solution"), "reference_solution")
    n = _bulat(payload.get("samples_count"), "samples_count", BATAS_SAMPEL) \
        + _bulat(payload.get("hidden_count"), "hidden_count", BATAS_TERSEMBUNYI)
    batas_ms = _bulat(payload.get("time_limit_ms"), "time_limit_ms", BATAS_WAKTU_MS)
    diizinkan = set(_daftar_kode(payload.get("allowed_concepts"), "allowed_concepts"))
    if payload.get("comparison_mode") not in banding.MODE:
        raise MasukanSalah("comparison_mode tidak dikenal")

    alasan: list[dict] = []
    peringatan: list[dict] = []
    konsep: list[str] = []
    cpu_maks = 0
    langkah = 6

    dir_job = kotak.siapkan_dir_job()
    try:
        # 1. Solusi referensi terkompilasi.
        maju("compiling", 0, langkah)
        k = kotak.kompilasi(bahasa, referensi, dir_job)
        if k.kelaparan:
            raise kotak.GalatInfrastruktur(f"kompilasi solusi referensi kena batas waktu dengan hanya {k.cpu_ms} ms CPU")
        if not k.ok:
            alasan.append(_alasan_kompilasi(bahasa, k))

        # 2. Solusi referensi hanya memakai konsep yang diizinkan di pertemuannya (SPEC.md §9.1).
        maju("running", 1, langkah)
        if k.ok:
            try:
                temuan = kotak.analisis(bahasa, referensi)
                konsep = [t["code"] for t in temuan]
                for t in temuan:
                    if t["code"] not in diizinkan:
                        alasan.append({"code": "concept_not_allowed", "where": "reference_solution", "concept": t["code"],
                                       "construct": t["construct"], "line": t["line"], "column": t["column"], "message": ""})
            except kotak.AnalisisGagal as e:
                alasan.append({"code": "analysis_failed", "where": "reference_solution", "message": str(e)})

        # 3. Generator berjalan untuk 20 seed acak.
        maju("running", 2, langkah)
        seed = [secrets.randbelow(SEED_MAKS) + 1 for _ in range(JUMLAH_SEED)]
        masukan = _bangkitkan(generator, seed, n, dir_job, alasan)

        # 4. Generator deterministik: SEED dan INDEX yang sama tiga kali, keluaran yang sama.
        maju("running", 3, langkah)
        if masukan is not None:
            for _ in range(2):
                ulang = _bangkitkan(generator, seed[:SEED_ULANG], n, dir_job, alasan)
                if ulang is None:
                    masukan = None
                    break
                beda = next((j for j in range(len(ulang)) if ulang[j] != masukan[j]), None)
                if beda is not None:
                    alasan.append({"code": "generator_not_deterministic", "where": "generator",
                                   "seed": seed[beda // n], "index": beda % n, "message": ""})
                    masukan = None
                    break
        if masukan is not None:
            per_seed = [masukan[j * n:(j + 1) * n] for j in range(JUMLAH_SEED)]
            if all(p == per_seed[0] for p in per_seed):
                peringatan.append({"code": "generator_ignores_seed", "where": "generator",
                                   "message": "semua seed menghasilkan masukan yang sama"})

        # 5. Solusi referensi menyelesaikan semua tes dari 20 seed tanpa galat dan tanpa TLE.
        maju("running", 4, langkah)
        if k.ok and masukan is not None:
            hasil = kotak.jalankan_kelompok(bahasa, dir_job, masukan, cpu_ms=batas_ms)
            for h in hasil:
                cpu_maks = max(cpu_maks, h.cpu_ms)
                if h.status != "OK":
                    alasan.append(_alasan_jalan("reference_failed", "reference_solution", h, seed[h.nomor // n], h.nomor % n))
                    break
            else:
                if any(not h.stdout.strip() for h in hasil):
                    peringatan.append({"code": "reference_empty_output", "where": "reference_solution",
                                       "message": "ada tes yang keluarannya kosong"})
                if cpu_maks * 2 > batas_ms:
                    peringatan.append({"code": "reference_slow", "where": "reference_solution",
                                       "message": f"tes terlama memakai {cpu_maks} ms dari batas {batas_ms} ms"})
        maju("running", langkah, langkah)
    finally:
        kotak.buang_dir_job(dir_job)

    return {"ok": not alasan, "reasons": alasan, "warnings": peringatan, "concepts": konsep,
            "tests": n * JUMLAH_SEED, "max_cpu_ms": cpu_maks}


class BangkitGagal(Exception):
    """Soal yang sudah disetujui gagal menghasilkan tes untuk satu seed. Bukan galat infrastruktur."""

    def __init__(self, alasan: dict):
        super().__init__(alasan.get("code", ""))
        # Pesan program tidak ikut: hasil pekerjaan ini bisa dibaca pembuatnya (mahasiswa).
        self.alasan = {k: v for k, v in alasan.items() if k in ("code", "where", "verdict", "index")}


def bangkit(versi: dict, seed: int) -> list[dict]:
    """GENERATE: tes satu mahasiswa — [{in, out}], contoh lebih dulu lalu tes tersembunyi.

    `versi` adalah baris problem_versions. Masukan dari generator; keluaran yang
    diharapkan dari solusi referensi (SPEC.md §8.2)."""
    if not isinstance(seed, int) or isinstance(seed, bool) or not 0 <= seed <= SEED_MAKS:
        raise MasukanSalah("seed harus bilangan bulat 0 … 2^63−1")
    bahasa = versi["language"]
    n = int(versi["samples_count"]) + int(versi["hidden_count"])
    alasan: list[dict] = []
    dir_job = kotak.siapkan_dir_job()
    try:
        masukan = _bangkitkan(versi["generator"], [seed], n, dir_job, alasan)
        if masukan is None:
            raise BangkitGagal(alasan[0])
        k = kotak.kompilasi(bahasa, versi["reference_solution"], dir_job)
        if not k.ok:
            # Versi ini sudah DISETUJUI, jadi solusi referensinya pernah terkompilasi saat
            # validasi. Kompilasi yang kehabisan waktu atau memori sekarang berarti server
            # sedang penuh (terjadi 1 dari 1.200 kali saat pra-pembangkitan satu kelas):
            # itu galat infrastruktur yang diulang worker, bukan soal yang rusak.
            if k.alasan in ("waktu_habis", "memori_habis"):
                raise kotak.GalatInfrastruktur(f"kompilasi solusi referensi {k.alasan} saat membangkitkan instans")
            raise BangkitGagal({"code": "reference_compile_error", "where": "reference_solution"})
        hasil = kotak.jalankan_kelompok(bahasa, dir_job, masukan, cpu_ms=int(versi["time_limit_ms"]))
        for h in hasil:
            if h.status != "OK":
                raise BangkitGagal({"code": "reference_failed", "where": "reference_solution", "verdict": h.status, "index": h.nomor})
        return [{"in": masukan[i].decode("utf-8"), "out": hasil[i].stdout.decode("utf-8", "replace")} for i in range(n)]
    finally:
        kotak.buang_dir_job(dir_job)


def periksa(payload: dict) -> dict:
    """ANALYZE: konsep yang dipakai sebuah sumber, dan mana dari yang disyaratkan yang tidak ada."""
    bahasa = payload.get("language")
    if bahasa not in BAHASA:
        raise MasukanSalah("language harus c, cpp atau python")
    sumber = _teks(payload.get("source"), "source")
    syarat = _daftar_kode(payload.get("required_concepts", []), "required_concepts")
    temuan = kotak.analisis(bahasa, sumber)
    kode = [t["code"] for t in temuan]
    return {"concepts": kode, "missing": [s for s in syarat if s not in kode]}
