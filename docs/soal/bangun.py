"""Alat bersama untuk menyusun berkas impor soal (format KONTRAK.md §7).

Setiap berkas `buat-<matkul>-pertemuan-<n>.py` memanggil soal(...) untuk tiap
soal lalu tulis(...). Teks Indonesia ditulis sebagai teks asli; teks Inggris dan
Arab ditulis oleh agen penyusun dan karena itu ditandai `machine: true`, sampai
pengampu memeriksanya dan menghapus tandanya.
"""
import json
from pathlib import Path

AWAL = ('import os\nimport random\n\n'
        'r = random.Random(int(os.environ["SEED"]) * 1000 + int(os.environ["INDEX"]))\n'
        'i = int(os.environ["INDEX"])\n')

SOAL = []


def cpp(isi: str, *pustaka: str) -> str:
    """Program C++ lengkap dari isi main(); <iostream> selalu ada."""
    kepala = "".join(f"#include <{p}>\n" for p in ("iostream", *pustaka))
    return kepala + "using namespace std;\n\nint main() {\n" + isi.rstrip("\n") + "\n    return 0;\n}\n"


def _teks(t, mesin: bool) -> dict:
    judul, pernyataan, masukan, keluaran, batasan = t
    hasil = {"title": judul, "statement": pernyataan, "input_format": masukan, "output_format": keluaran, "constraints": batasan}
    if mesin:
        hasil["machine"] = True
    return hasil


def soal(slug, tingkat, mode, gen, ref, id, en, ar, waktu_ms=2000, wajib=()):
    """wajib: konsep yang harus ada di kode mahasiswa (required_concepts_in_submission)."""
    SOAL.append({
        "slug": slug, "difficulty": tingkat, "comparison_mode": mode, "time_limit_ms": waktu_ms,
        "samples_count": 2, "hidden_count": 10, "required_concepts_in_submission": list(wajib),
        "translations": {"id": _teks(id, False), "en": _teks(en, True), "ar": _teks(ar, True)},
        "generator": AWAL + gen, "reference_solution": ref,
    })


def tulis(skrip: str, matkul: str, pertemuan: int, bahasa: str, harap: dict) -> None:
    hitung = {}
    for s in SOAL:
        hitung[s["difficulty"]] = hitung.get(s["difficulty"], 0) + 1
    assert len({s["slug"] for s in SOAL}) == len(SOAL), "slug kembar"
    assert hitung == harap, f"komposisi {hitung}, seharusnya {harap}"
    tujuan = Path(skrip).with_name(f"{matkul.lower()}-pertemuan-{pertemuan}.json")
    tujuan.write_text(json.dumps({"course": matkul, "meeting": pertemuan, "language": bahasa, "problems": SOAL},
                                 ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
    print(f"{tujuan.name}: {len(SOAL)} soal {hitung}, {tujuan.stat().st_size} byte")
