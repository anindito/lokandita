"""Membandingkan keluaran program dengan keluaran yang diharapkan (SPEC.md §7).

  exact    byte demi byte
  trimmed  spasi di ujung tiap baris dan baris kosong di ujung berkas diabaikan (bawaan)
  token    hanya urutan kata yang dibandingkan; spasi dan ganti baris bebas
  float    seperti token, tetapi kata yang berupa bilangan boleh berselisih ≤ ε
           (mutlak atau relatif), bawaan 1e-6
"""

from __future__ import annotations

import math

MODE = ("exact", "trimmed", "token", "float")
EPSILON = 1e-6


def _baris_rapi(teks: str) -> list[str]:
    baris = [b.rstrip() for b in teks.replace("\r\n", "\n").split("\n")]
    while baris and baris[-1] == "":
        baris.pop()
    return baris


def _angka(kata: str) -> float | None:
    try:
        nilai = float(kata)
    except ValueError:
        return None
    return nilai if math.isfinite(nilai) else None


def sama(keluaran: str, harapan: str, mode: str = "trimmed", epsilon: float = EPSILON) -> bool:
    if mode == "exact":
        return keluaran == harapan
    if mode == "trimmed":
        return _baris_rapi(keluaran) == _baris_rapi(harapan)
    if mode == "token":
        return keluaran.split() == harapan.split()
    if mode == "float":
        a, b = keluaran.split(), harapan.split()
        if len(a) != len(b):
            return False
        for x, y in zip(a, b):
            if x == y:
                continue
            fx, fy = _angka(x), _angka(y)
            if fx is None or fy is None:
                return False
            if abs(fx - fy) > epsilon * max(1.0, abs(fy)):
                return False
        return True
    raise ValueError(f"mode perbandingan tidak dikenal: {mode}")
