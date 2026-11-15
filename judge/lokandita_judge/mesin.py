"""Mesin penilai: dari sumber dan masukan menjadi hasil (KONTRAK.md §6).

  jalankan_sekali  untuk RUN: kompilasi, satu kali jalan, keluaran dikembalikan
  nilai_tes        untuk SUBMIT (tahap 9): kompilasi sekali, tiap tes di kontainer
                   baru; hasilnya tidak pernah memuat masukan atau keluaran tes
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
    return {"verdict": "CE", "compile_log": k.log, "compile_error": ALASAN_CE[k.alasan]}


def jalankan_sekali(bahasa: str, sumber: str, stdin: str = "", maju: Callable[[str, int, int], None] | None = None) -> dict:
    _periksa(bahasa, sumber)
    masukan = stdin.encode("utf-8")
    if len(masukan) > BATAS_STDIN:
        raise MasukanSalah("stdin lebih dari 1 MB")
    maju = maju or (lambda *_: None)

    dir_job = kotak.siapkan_dir_job()
    try:
        maju("compiling", 0, 1)
        k = kotak.kompilasi(bahasa, sumber, dir_job)
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


def nilai_tes(
    bahasa: str, sumber: str, daftar_tes: list[Tes], mode: str = "trimmed", epsilon: float = banding.EPSILON,
    cpu_ms: int | None = None, memori_mb: int = kotak.MEMORI_MB, maju: Callable[[str, int, int], None] | None = None,
) -> dict:
    _periksa(bahasa, sumber)
    if mode not in banding.MODE:
        raise MasukanSalah("comparison_mode tidak dikenal")
    maju = maju or (lambda *_: None)
    total = len(daftar_tes)

    dir_job = kotak.siapkan_dir_job()
    try:
        maju("compiling", 0, total)
        k = kotak.kompilasi(bahasa, sumber, dir_job)
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
