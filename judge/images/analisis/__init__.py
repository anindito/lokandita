"""Analisis konsep atas kode sumber (SPEC.md §9) — berjalan di dalam image judge-analisis.

    python3 -m analisis <c|cpp|python> <berkas> [berkas…]

Untuk setiap berkas dicetak satu baris JSON:

    {"file": "...", "concepts": [{"code", "line", "column", "construct"}], "error": null}

`concepts` memuat kemunculan PERTAMA setiap konsep yang ditemukan. Kode sumber
hanya diurai (tree-sitter untuk C dan C++, modul `ast` untuk Python); tidak
pernah dijalankan dan tidak pernah dicocokkan sebagai teks.

Aturan deteksi dijelaskan untuk pembaca manusia di docs/DETEKSI-KONSEP.md.
"""

from __future__ import annotations


class Temuan:
    """Kemunculan pertama (baris, kolom terkecil) tiap konsep."""

    def __init__(self) -> None:
        self._isi: dict[str, tuple[int, int, str]] = {}

    def catat(self, kode: str, baris: int, kolom: int, konstruksi: str) -> None:
        lama = self._isi.get(kode)
        if lama is None or (baris, kolom) < lama[:2]:
            self._isi[kode] = (baris, kolom, konstruksi[:60])

    def daftar(self) -> list[dict]:
        urut = sorted(self._isi.items(), key=lambda b: (b[1][0], b[1][1], b[0]))
        return [{"code": k, "line": b, "column": c, "construct": t} for k, (b, c, t) in urut]


def analisis(bahasa: str, sumber: bytes) -> list[dict]:
    """Konsep yang dipakai `sumber`. Melempar ValueError bila sumber tidak bisa diurai."""
    if bahasa == "python":
        from . import py
        return py.analisis(sumber)
    if bahasa in ("c", "cpp"):
        from . import cfamili
        return cfamili.analisis(bahasa, sumber)
    raise ValueError(f"bahasa tidak dikenal: {bahasa}")


def kode_terdeteksi(bahasa: str) -> list[str]:
    """Kode konsep yang punya detektor untuk bahasa itu (diuji terhadap katalog)."""
    if bahasa == "python":
        from . import py
        return sorted(py.KODE)
    from . import cfamili
    return sorted(cfamili.KODE_C if bahasa == "c" else cfamili.KODE_CPP)
