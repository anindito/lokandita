"""VALIDATE, GENERATE dan ANALYZE (KONTRAK.md §6, SPEC.md §8.3, §9) — fungsi mesinnya,
dengan kontainer sungguhan. Lewat worker dan basis data: test_worker_soal.py."""

from __future__ import annotations

import time

import pytest

from lokandita_judge import mesin, soal

GEN = ('import os, random\n'
       'r = random.Random(int(os.environ["SEED"]) * 1000 + int(os.environ["INDEX"]))\n'
       'print(r.randint(1, 1000), r.randint(1, 1000))\n')
REF_CPP = ('#include <iostream>\nusing namespace std;\nint main() {\n    double x, y;\n    cin >> x >> y;\n'
           '    cout << x << " " << y << endl;\n    return 0;\n}\n')
DASAR = ["IO_DASAR", "VARIABEL_TIPE", "ARITMETIKA"]


def soal_baik(**ganti) -> dict:
    return {"language": "cpp", "generator": GEN, "reference_solution": REF_CPP, "samples_count": 2, "hidden_count": 10,
            "time_limit_ms": 2000, "allowed_concepts": DASAR, "comparison_mode": "trimmed", **ganti}


def alasan(hasil: dict, kode: str) -> dict:
    cocok = [a for a in hasil["reasons"] if a["code"] == kode]
    assert cocok, f"tidak ada alasan {kode}: {hasil['reasons']}"
    return cocok[0]


def test_soal_yang_baik_diterima_dan_gerbang_cout_double():
    """Gerbang tahap 8: `cout << x << " " << y` dengan double diterima di pertemuan tanpa STRING."""
    mulai = time.time()
    hasil = soal.validasi(soal_baik())
    lama = time.time() - mulai
    assert hasil["ok"] is True and hasil["reasons"] == [] and hasil["warnings"] == []
    assert sorted(hasil["concepts"]) == ["IO_DASAR", "VARIABEL_TIPE"]
    assert hasil["tests"] == 20 * 12
    print(f"\nvalidasi satu soal C++ (12 tes × 20 seed): {lama:.1f} detik")
    assert lama < 90


def test_soal_python_yang_baik_diterima():
    ref = "a, b = map(int, input().split())\nprint(a + b)\n"
    hasil = soal.validasi(soal_baik(language="python", reference_solution=ref, time_limit_ms=5000))
    assert hasil["ok"] is True, hasil["reasons"]
    assert sorted(hasil["concepts"]) == ["ARITMETIKA", "IO_DASAR", "VARIABEL_TIPE"]


def test_konsep_di_luar_pertemuan_ditolak_dengan_konstruksi_baris_dan_kolom():
    ref = ('#include <iostream>\nusing namespace std;\nint main() {\n    int a, b;\n    cin >> a >> b;\n'
           '    if (a > b) cout << a;\n    else cout << b;\n    return 0;\n}\n')
    hasil = soal.validasi(soal_baik(reference_solution=ref))
    assert hasil["ok"] is False
    a = alasan(hasil, "concept_not_allowed")
    assert (a["where"], a["concept"], a["construct"], a["line"], a["column"]) == ("reference_solution", "PERCABANGAN", "if", 6, 5)
    # Dengan konsep itu diizinkan, soal yang sama diterima.
    assert soal.validasi(soal_baik(reference_solution=ref, allowed_concepts=DASAR + ["PERCABANGAN"]))["ok"] is True


def test_string_sungguhan_ditolak_di_pertemuan_tanpa_string():
    ref = '#include <iostream>\n#include <string>\nint main() {\n    std::string s;\n    std::cin >> s;\n    std::cout << s;\n}\n'
    a = alasan(soal.validasi(soal_baik(reference_solution=ref)), "concept_not_allowed")
    assert (a["concept"], a["line"]) == ("STRING", 4)


def test_solusi_referensi_yang_tidak_terkompilasi_menyebut_baris_dan_kolom():
    ref = "#include <iostream>\nint main() {\n    int a\n    std::cin >> a;\n}\n"
    hasil = soal.validasi(soal_baik(reference_solution=ref))
    a = alasan(hasil, "reference_compile_error")
    assert a["where"] == "reference_solution" and a["line"] in (3, 4) and a["column"] > 0 and "expected" in a["message"]
    assert hasil["ok"] is False


def test_solusi_python_bersintaks_salah_menyebut_baris():
    hasil = soal.validasi(soal_baik(language="python", reference_solution="a = int(input())\nprint(a +\n", time_limit_ms=5000))
    assert alasan(hasil, "reference_compile_error")["line"] == 2


def test_generator_bersintaks_salah_menyebut_baris():
    hasil = soal.validasi(soal_baik(generator="import os\nprint(os.environ['SEED']\n\nx = 1\n"))
    a = alasan(hasil, "generator_failed")
    assert a["where"] == "generator" and a["line"] in (2, 4) and a["verdict"] == "RE" and "SyntaxError" in a["message"]


def test_generator_yang_gagal_di_satu_index_menyebut_seed_index_dan_baris():
    gen = GEN + 'if os.environ["INDEX"] == "7":\n    raise ValueError("tidak ada data")\n'
    a = alasan(soal.validasi(soal_baik(generator=gen)), "generator_failed")
    assert a["index"] == 7 and a["seed"] > 0 and a["line"] == 5 and "tidak ada data" in a["message"]


@pytest.mark.parametrize("gen", [
    "import random\nprint(random.randint(1, 10**9), random.randint(1, 10**9))\n",
    "import time\nprint(time.time_ns(), 1)\n",
    "import os\nprint(os.getpid(), os.urandom(4).hex())\n",
])
def test_generator_yang_tidak_deterministik_ditolak(gen):
    a = alasan(soal.validasi(soal_baik(generator=gen)), "generator_not_deterministic")
    assert a["where"] == "generator" and 0 <= a["index"] < 12


def test_generator_yang_mengabaikan_seed_diberi_peringatan():
    gen = 'import os\nprint(int(os.environ["INDEX"]) + 1, 5)\n'
    hasil = soal.validasi(soal_baik(generator=gen))
    assert hasil["ok"] is True
    assert [p["code"] for p in hasil["warnings"]] == ["generator_ignores_seed"]


def test_generator_yang_keluarannya_terlalu_besar_ditolak():
    a = alasan(soal.validasi(soal_baik(generator="print('1 ' * 200000)\n")), "generator_output_too_large")
    assert a["where"] == "generator"


def test_solusi_referensi_yang_jatuh_pada_sebagian_tes_menyebut_seed_dan_index():
    gen = 'import os\nprint(int(os.environ["INDEX"]) - 5, int(os.environ["SEED"]) % 7)\n'
    ref = '#include <iostream>\nint main() {\n    int a, b;\n    std::cin >> a >> b;\n    std::cout << b / a;\n}\n'
    a = alasan(soal.validasi(soal_baik(generator=gen, reference_solution=ref)), "reference_failed")
    assert (a["where"], a["verdict"], a["index"]) == ("reference_solution", "RE", 5)


def test_solusi_referensi_yang_terlalu_lambat_ditolak_sebagai_tle():
    ref = ('#include <iostream>\nint main() {\n    long a, b;\n    std::cin >> a >> b;\n    volatile long s = 0;\n'
           '    for (long i = 0; i < 4000000000L; i++) s += i;\n    std::cout << s;\n}\n')
    hasil = soal.validasi(soal_baik(reference_solution=ref, time_limit_ms=300, allowed_concepts=DASAR + ["PERULANGAN"]))
    assert alasan(hasil, "reference_failed")["verdict"] == "TLE"


def test_semua_alasan_dilaporkan_sekaligus():
    """Konsep terlarang DAN generator rusak: penulis soal melihat keduanya dalam satu kali impor."""
    ref = "#include <iostream>\nint main() {\n    int a;\n    std::cin >> a;\n    while (a > 0) a--;\n}\n"
    hasil = soal.validasi(soal_baik(reference_solution=ref, generator="raise SystemExit(2)\n"))
    assert {a["code"] for a in hasil["reasons"]} == {"concept_not_allowed", "generator_failed"}


@pytest.mark.parametrize("ganti", [
    {"language": "java"}, {"generator": ""}, {"reference_solution": 5}, {"samples_count": 1}, {"samples_count": 4},
    {"hidden_count": 9}, {"hidden_count": 51}, {"time_limit_ms": 50}, {"time_limit_ms": "2000"},
    {"allowed_concepts": "IO_DASAR"}, {"comparison_mode": "mirip"}, {"samples_count": True},
])
def test_payload_validate_yang_salah_ditolak_sebelum_ada_kontainer(ganti):
    with pytest.raises(mesin.MasukanSalah):
        soal.validasi(soal_baik(**ganti))


def versi(**ganti) -> dict:
    return {"language": "cpp", "generator": GEN, "samples_count": 2, "hidden_count": 10, "time_limit_ms": 2000,
            "reference_solution": '#include <iostream>\nint main(){long a,b;std::cin>>a>>b;std::cout<<a+b<<"\\n";}\n', **ganti}


def test_bangkit_menghasilkan_tes_dari_generator_dan_solusi_referensi():
    tes = soal.bangkit(versi(), 123456789)
    assert len(tes) == 12
    for t in tes:
        a, b = (int(x) for x in t["in"].split())
        assert t["out"] == f"{a + b}\n"


def test_bangkit_seed_sama_tes_sama_seed_lain_tes_lain():
    assert soal.bangkit(versi(), 42) == soal.bangkit(versi(), 42)
    assert soal.bangkit(versi(), 42) != soal.bangkit(versi(), 43)


def test_bangkit_yang_gagal_tidak_membawa_pesan_program():
    gen = 'import sys\nsys.exit("RAHASIA-DARI-GENERATOR")\n'
    with pytest.raises(soal.BangkitGagal) as info:
        soal.bangkit(versi(generator=gen), 1)
    assert info.value.alasan == {"code": "generator_failed", "where": "generator", "verdict": "RE", "index": 0}


@pytest.mark.parametrize("seed", [-1, 2**63, "5", 1.5, True])
def test_bangkit_menolak_seed_yang_bukan_bilangan_63_bit(seed):
    with pytest.raises(mesin.MasukanSalah):
        soal.bangkit(versi(), seed)


def test_periksa_menyebut_konsep_yang_dipakai_dan_yang_kurang():
    sumber = "class Hewan:\n    def suara(self):\n        return 'x'\n\n\nprint(Hewan().suara())\n"
    hasil = soal.periksa({"language": "python", "source": sumber, "required_concepts": ["CLASS_OBJECT", "INHERITANCE"]})
    assert "CLASS_OBJECT" in hasil["concepts"] and hasil["missing"] == ["INHERITANCE"]
    assert soal.periksa({"language": "python", "source": sumber})["missing"] == []


# ---- berkas contoh untuk gerbang pemilik (docs/contoh-soal) ----

CONTOH = __import__("pathlib").Path(__file__).parents[2] / "docs" / "contoh-soal"
# DDP101 pertemuan 2 menurut docs/PETA-KONSEP.md: konsep pertemuan 1 dan 2.
DDP101_PERTEMUAN_2 = ["IO_DASAR", "ARITMETIKA", "VARIABEL_TIPE", "KONVERSI_TIPE"]


def _validasi_contoh(nama: str) -> dict[str, dict]:
    import json

    berkas = json.loads((CONTOH / nama).read_text(encoding="utf-8"))
    assert (berkas["course"], berkas["meeting"], berkas["language"]) == ("DDP101", 2, "cpp")
    return {s["slug"]: soal.validasi({
        "language": berkas["language"], "generator": s["generator"], "reference_solution": s["reference_solution"],
        "samples_count": s["samples_count"], "hidden_count": s["hidden_count"], "time_limit_ms": s["time_limit_ms"],
        "allowed_concepts": DDP101_PERTEMUAN_2, "comparison_mode": s["comparison_mode"],
    }) for s in berkas["problems"]}


def test_berkas_contoh_gerbang_seluruhnya_lolos_validasi():
    hasil = _validasi_contoh("ddp101-pertemuan-2.json")
    assert set(hasil) == {"tukar-dua-pecahan", "rata-rata-tiga-nilai", "luas-keliling-persegi-panjang"}
    for slug, h in hasil.items():
        assert h["ok"] is True and h["warnings"] == [], f"{slug}: {h['reasons']} {h['warnings']}"
    # Gerbang: cout << y << " " << x dengan double, di pertemuan yang belum mengizinkan STRING.
    assert sorted(hasil["tukar-dua-pecahan"]["concepts"]) == ["IO_DASAR", "VARIABEL_TIPE"]
    assert "KONVERSI_TIPE" in hasil["rata-rata-tiga-nilai"]["concepts"]


def test_berkas_contoh_yang_sengaja_salah_ditolak_dengan_nomor_baris():
    hasil = _validasi_contoh("ddp101-pertemuan-2-salah.json")
    assert hasil["luas-keliling-persegi-panjang"]["ok"] is True
    salah = hasil["sisi-terpanjang"]
    assert salah["ok"] is False and len(salah["reasons"]) == 1
    a = salah["reasons"][0]
    assert (a["code"], a["concept"], a["construct"], a["line"], a["column"]) == ("concept_not_allowed", "PERCABANGAN", "if", 7, 5)


def test_bangkit_kompilasi_yang_kehabisan_waktu_adalah_galat_infrastruktur_bukan_soal_rusak(monkeypatch):
    """Ditemukan saat mengukur pra-pembangkitan 1.200 instans (tahap 9a): di bawah beban, satu
    kompilasi solusi referensi yang sah melewati 10 detik dan pekerjaannya gagal untuk selamanya."""
    from lokandita_judge import kotak

    asli = kotak.kompilasi
    for alasan in ("waktu_habis", "memori_habis"):
        monkeypatch.setattr(kotak, "kompilasi", lambda *a, _alasan=alasan, **k: kotak.HasilKompilasi(False, "", _alasan))
        with pytest.raises(kotak.GalatInfrastruktur):
            soal.bangkit(versi(), 7)
    # Solusi referensi yang memang tidak terkompilasi tetap soal yang rusak, tidak diulang.
    monkeypatch.setattr(kotak, "kompilasi", asli)
    with pytest.raises(soal.BangkitGagal) as info:
        soal.bangkit(versi(reference_solution="int main( {"), 7)
    assert info.value.alasan == {"code": "reference_compile_error", "where": "reference_solution"}
