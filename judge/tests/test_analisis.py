"""Analisis konsep (SPEC.md §9) di kontainer judge-analisis yang sungguhan.

Setiap berkas di tests/korpus/<bahasa>/ menyebut di baris pertamanya himpunan
konsep yang HARUS ditemukan — tidak kurang dan tidak lebih. Dengan begitu
setiap berkas sekaligus menjadi contoh positif dan contoh negatif.
"""

from __future__ import annotations

import json
import re
from pathlib import Path

import pytest

from lokandita_judge import kotak

KORPUS = Path(__file__).parent / "korpus"
KATALOG = json.loads((Path(__file__).parents[2] / "apps/lokakode/app/Data/konsep.json").read_text(encoding="utf-8"))
BAHASA = ("c", "cpp", "python")


def korpus(bahasa: str) -> dict[str, tuple[set[str], str]]:
    hasil = {}
    for berkas in sorted((KORPUS / bahasa).iterdir()):
        sumber = berkas.read_text(encoding="utf-8")
        cocok = re.search(r"KONSEP: (.*)", sumber.splitlines()[0])
        assert cocok, f"{berkas.name}: baris pertama harus menyebut KONSEP:"
        hasil[berkas.name] = (set(cocok.group(1).split()), sumber)
    return hasil


@pytest.fixture(scope="module")
def hasil_korpus() -> dict[str, dict]:
    return {b: kotak.analisis_banyak(b, {nama: sumber for nama, (_, sumber) in korpus(b).items()}) for b in BAHASA}


@pytest.mark.parametrize("bahasa", BAHASA)
def test_korpus_konsep_persis_seperti_yang_dinyatakan(bahasa, hasil_korpus):
    beda = []
    for nama, (harapan, _) in korpus(bahasa).items():
        hasil = hasil_korpus[bahasa][nama]
        assert isinstance(hasil, list), f"{nama}: {hasil}"
        dapat = {k["code"] for k in hasil}
        if dapat != harapan:
            beda.append(f"{nama}: kurang {sorted(harapan - dapat)}, lebih {sorted(dapat - harapan)}")
    assert not beda, "\n".join(beda)


@pytest.mark.parametrize("bahasa", BAHASA)
def test_setiap_konsep_katalog_punya_detektor_dan_contoh(bahasa, hasil_korpus):
    """Konsep adalah data (apps/lokakode/app/Data/konsep.json). Setiap konsep yang
    berlaku untuk sebuah bahasa harus benar-benar terdeteksi pada minimal satu contoh."""
    katalog = {k["code"] for k in KATALOG if bahasa in k["languages"]}
    terdeteksi = {k["code"] for hasil in hasil_korpus[bahasa].values() for k in hasil}
    assert terdeteksi == katalog, f"tanpa contoh: {sorted(katalog - terdeteksi)}; di luar katalog: {sorted(terdeteksi - katalog)}"


def test_gerbang_cout_dengan_literal_di_antara_dua_double_bukan_string():
    """Gerbang tahap 8 (SPEC.md §18)."""
    sumber = '#include <iostream>\nusing namespace std;\nint main() {\n    double x, y;\n    cin >> x >> y;\n    cout << x << " " << y;\n}\n'
    kode = {k["code"] for k in kotak.analisis("cpp", sumber)}
    assert "STRING" not in kode
    assert kode == {"IO_DASAR", "VARIABEL_TIPE"}


def test_temuan_menyebut_konstruksi_baris_dan_kolom():
    sumber = "#include <iostream>\nint main() {\n    int n;\n    std::cin >> n;\n    if (n > 0) std::cout << n;\n}\n"
    temuan = {k["code"]: k for k in kotak.analisis("cpp", sumber)}
    assert temuan["PERCABANGAN"] == {"code": "PERCABANGAN", "line": 5, "column": 5, "construct": "if"}
    assert (temuan["VARIABEL_TIPE"]["line"], temuan["VARIABEL_TIPE"]["column"]) == (3, 5)

    py = {k["code"]: k for k in kotak.analisis("python", "n = int(input())\nfor i in range(n):\n    print(i)\n")}
    assert (py["PERULANGAN"]["line"], py["PERULANGAN"]["column"], py["PERULANGAN"]["construct"]) == (2, 1, "for")


def test_kemunculan_pertama_yang_dilaporkan():
    sumber = "int main() {\n    int a = 1;\n    while (a < 3) a++;\n    for (;;) { break; }\n}\n"
    temuan = {k["code"]: k for k in kotak.analisis("c", sumber)}
    assert (temuan["PERULANGAN"]["line"], temuan["PERULANGAN"]["construct"]) == (3, "while")


def test_python_yang_tidak_bisa_diurai_ditolak_bukan_galat_infrastruktur():
    with pytest.raises(kotak.AnalisisGagal):
        kotak.analisis("python", "def f(:\n    pass\n")


@pytest.mark.parametrize("bahasa,sumber", [
    ("python", "x = " + "(" * 3000 + "1" + ")" * 3000 + "\n"),
    ("cpp", "int main() { int x = " + "(" * 20000 + "1" + ")" * 20000 + "; }\n"),
])
def test_kode_bersarang_sangat_dalam_tidak_menjatuhkan_penganalisis(bahasa, sumber):
    """Kode yang sengaja dibuat untuk menghabiskan tumpukan pengurai: hasilnya penolakan
    yang jelas atau analisis biasa, tidak pernah galat infrastruktur."""
    try:
        hasil = kotak.analisis(bahasa, sumber)
    except kotak.AnalisisGagal:
        return
    assert isinstance(hasil, list)


def test_sumber_256_kb_selesai_dalam_batas_waktu():
    baris = "    total = total + i * 2 - 1;\n"
    sumber = "int main() {\n    int total = 0, i = 1;\n" + baris * (250_000 // len(baris)) + "    return total;\n}\n"
    kode = {k["code"] for k in kotak.analisis("c", sumber)}
    assert kode == {"VARIABEL_TIPE", "ARITMETIKA"}


def test_nama_berkas_yang_tidak_sah_ditolak():
    with pytest.raises(kotak.AnalisisGagal):
        kotak.analisis_banyak("c", {"../keluar.c": "int main(){}"})


def test_kontainer_analisis_memakai_batas_kotak_pasir_yang_sama(monkeypatch):
    """Analisis tidak punya jalur kontainer sendiri: ia lewat _jalankan_kontainer dan bendera_kontainer."""
    terlihat = {}
    asli = kotak.bendera_kontainer

    def catat(*a, **k):
        terlihat["bendera"] = asli(*a, **k)
        return terlihat["bendera"]

    monkeypatch.setattr(kotak, "bendera_kontainer", catat)
    kotak.analisis("c", "int main(void) { return 0; }\n")
    bendera = terlihat["bendera"]
    for wajib in ("--read-only", "no-new-privileges", "none", "ALL", "65534:65534"):
        assert wajib in bendera
    assert bendera[bendera.index("--network") + 1] == "none"
