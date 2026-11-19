"""Mesin penilai: dari sumber dan masukan menjadi hasil (KONTRAK.md §6).

  jalankan_sekali  untuk RUN: kompilasi, satu kali jalan, keluaran dikembalikan
  jalankan_contoh  untuk RUN pada tes contoh sebuah instans: keluaran program
                   dikembalikan, karena tes contoh terbuka bagi mahasiswanya
  nilai_tes        untuk SUBMIT: kompilasi sekali, tiap tes di kontainer baru;
                   hasilnya tidak pernah memuat masukan atau keluaran tes
"""

from __future__ import annotations

from dataclasses import dataclass
from typing import Callable

from . import banding, kotak

BAHASA = ("c", "cpp", "python")
BATAS_SUMBER = 256 * 1024  # byte
BATAS_STDIN = 1024 * 1024


class MasukanSalah(Exception):
    """Payload tidak bisa dikerjakan. Bukan galat infrastruktur: tidak diulang."""


@dataclass
class Tes:
    stdin: str
    harapan: str


# Bendera kompiler tambahan per pertemuan (KONTRAK.md §6). Daftar ini sengaja
# pendek dan tertutup: bendera sampai ke GCC, jadi teks bebas tidak diterima.
BENDERA_UMUM = ("-Wall", "-Wextra", "-Wshadow", "-Wpedantic", "-O0", "-O1", "-O2", "-lm")
BENDERA_STD = {"c": ("c11", "c17", "gnu17"), "cpp": ("c++14", "c++17", "c++20", "gnu++17")}
BENDERA_MAKS = 8


def periksa_bendera(bahasa: str, bendera) -> tuple[str, ...]:
    """Bendera yang sah untuk bahasa itu, atau MasukanSalah. None dan [] berarti tanpa bendera."""
    if bendera is None or bendera == []:
        return ()
    if not isinstance(bendera, list) or not all(isinstance(b, str) for b in bendera):
        raise MasukanSalah("flags harus daftar teks")
    if bahasa not in BENDERA_STD:
        raise MasukanSalah("flags hanya untuk c dan cpp")
    if len(bendera) > BENDERA_MAKS:
        raise MasukanSalah(f"flags paling banyak {BENDERA_MAKS}")
    sah = set(BENDERA_UMUM) | {"-std=" + s for s in BENDERA_STD[bahasa]}
    for b in bendera:
        if b not in sah:
            raise MasukanSalah(f"flags: {b[:40]!r} tidak diizinkan")
    return tuple(bendera)


def _periksa(bahasa: str, sumber: str) -> None:
    if bahasa not in BAHASA:
        raise MasukanSalah("language harus c, cpp atau python")
    if not isinstance(sumber, str) or not sumber.strip():
        raise MasukanSalah("source kosong")
    if len(sumber.encode("utf-8")) > BATAS_SUMBER:
        raise MasukanSalah("source lebih dari 256 KB")


# Mengapa kompilasi gagal, dengan kode yang dipakai di KONTRAK.md §6.
ALASAN_CE = {"": "compiler", "waktu_habis": "timeout", "memori_habis": "memory", "keluaran_terlalu_besar": "output_too_large"}


def _hasil_ce(k: kotak.HasilKompilasi) -> dict:
    # Kompilasi yang kehabisan waktu karena server sibuk bukan kesalahan mahasiswa:
    # worker mengulanginya, dan tidak pernah menjadi CE (KONTRAK.md §6).
    if k.kelaparan:
        raise kotak.GalatInfrastruktur(f"kompilasi kena batas waktu dengan hanya {k.cpu_ms} ms CPU: server sibuk")
    return {"verdict": "CE", "compile_log": k.log, "compile_error": ALASAN_CE[k.alasan]}


def jalankan_sekali(bahasa: str, sumber: str, stdin: str = "", maju: Callable[[str, int, int], None] | None = None,
                    bendera=None) -> dict:
    _periksa(bahasa, sumber)
    bendera = periksa_bendera(bahasa, bendera)
    masukan = stdin.encode("utf-8")
    if len(masukan) > BATAS_STDIN:
        raise MasukanSalah("stdin lebih dari 1 MB")
    maju = maju or (lambda *_: None)

    dir_job = kotak.siapkan_dir_job()
    try:
        maju("compiling", 0, 1)
        k = kotak.kompilasi(bahasa, sumber, dir_job, bendera)
        if not k.ok:
            return _hasil_ce(k)
        maju("running", 0, 1)
        h = kotak.jalankan(bahasa, dir_job, masukan)
        maju("running", 1, 1)
        return {
            "verdict": h.status,
            "compile_log": k.log,
            "stdout": h.stdout.decode("utf-8", "replace"),
            "stderr": h.stderr.decode("utf-8", "replace"),
            "exit_code": h.kode_keluar,
            "signal": h.sinyal,
            "duration_ms": h.cpu_ms,
            "memory_kb": h.memori_kb,
        }
    finally:
        kotak.buang_dir_job(dir_job)


def jalankan_contoh(
    bahasa: str, sumber: str, daftar_tes: list[Tes], mode: str = "trimmed", cpu_ms: int | None = None,
    maju: Callable[[str, int, int], None] | None = None, bendera=None,
) -> dict:
    """RUN pada tes contoh: seperti nilai_tes, tetapi keluaran program ikut dikembalikan.

    Hanya untuk tes contoh, yang masukan dan keluarannya memang ditampilkan
    kepada mahasiswa pemilik instans. Tes tersembunyi tidak pernah lewat sini."""
    _periksa(bahasa, sumber)
    bendera = periksa_bendera(bahasa, bendera)
    if mode not in banding.MODE:
        raise MasukanSalah("comparison_mode tidak dikenal")
    maju = maju or (lambda *_: None)
    total = len(daftar_tes)

    dir_job = kotak.siapkan_dir_job()
    try:
        maju("compiling", 0, total)
        k = kotak.kompilasi(bahasa, sumber, dir_job, bendera)
        if not k.ok:
            return {**_hasil_ce(k), "samples": []}
        contoh = []
        for i, tes in enumerate(daftar_tes):
            maju("running", i, total)
            h = kotak.jalankan(bahasa, dir_job, tes.stdin.encode("utf-8"), cpu_ms)
            keluaran = h.stdout.decode("utf-8", "replace")
            verdict = h.status
            if verdict == "OK":
                verdict = "AC" if banding.sama(keluaran, tes.harapan, mode) else "WA"
            contoh.append({
                "index": i, "verdict": verdict, "stdout": keluaran, "stderr": h.stderr.decode("utf-8", "replace"),
                "exit_code": h.kode_keluar, "signal": h.sinyal, "duration_ms": h.cpu_ms,
            })
        maju("running", total, total)
        return {
            "verdict": next((c["verdict"] for c in contoh if c["verdict"] != "AC"), "AC"),
            "compile_log": k.log,
            "samples": contoh,
        }
    finally:
        kotak.buang_dir_job(dir_job)


def nilai_tes(
    bahasa: str, sumber: str, daftar_tes: list[Tes], mode: str = "trimmed", epsilon: float = banding.EPSILON,
    cpu_ms: int | None = None, memori_mb: int = kotak.MEMORI_MB, maju: Callable[[str, int, int], None] | None = None,
    bendera=None,
) -> dict:
    _periksa(bahasa, sumber)
    bendera = periksa_bendera(bahasa, bendera)
    if mode not in banding.MODE:
        raise MasukanSalah("comparison_mode tidak dikenal")
    maju = maju or (lambda *_: None)
    total = len(daftar_tes)

    dir_job = kotak.siapkan_dir_job()
    try:
        maju("compiling", 0, total)
        k = kotak.kompilasi(bahasa, sumber, dir_job, bendera)
        if not k.ok:
            return {**_hasil_ce(k), "tests": [], "passed": 0, "total": total}

        hasil_tes = []
        for i, tes in enumerate(daftar_tes):
            maju("running", i, total)
            # RE, TLE, MLE, OLE menggagalkan tes ini saja; tes berikutnya tetap dijalankan.
            h = kotak.jalankan(bahasa, dir_job, tes.stdin.encode("utf-8"), cpu_ms, memori_mb)
            verdict = h.status
            if verdict == "OK":
                cocok = banding.sama(h.stdout.decode("utf-8", "replace"), tes.harapan, mode, epsilon)
                verdict = "AC" if cocok else "WA"
            hasil_tes.append({"index": i, "verdict": verdict, "duration_ms": h.cpu_ms})
        maju("running", total, total)
        lulus = sum(1 for t in hasil_tes if t["verdict"] == "AC")
        return {
            "verdict": "AC" if lulus == total else next(t["verdict"] for t in hasil_tes if t["verdict"] != "AC"),
            "compile_log": k.log,
            "tests": hasil_tes,
            "passed": lulus,
            "total": total,
        }
    finally:
        kotak.buang_dir_job(dir_job)
