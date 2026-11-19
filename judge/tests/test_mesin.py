"""Mesin penilai: vonis, log kompilasi, dan bentuk hasil (KONTRAK.md §6)."""

from __future__ import annotations

import json

import pytest

from bahan import KOMPILASI_BERAT_KIRIMAN as KOMPILASI_BERAT
from lokandita_judge import kotak, mesin
from lokandita_judge.mesin import Tes

TAMBAH_CPP = '#include <iostream>\nint main(){long a,b;std::cin>>a>>b;std::cout<<a+b<<"\\n";}\n'


def test_run_cpp_dengan_stdin():
    h = mesin.jalankan_sekali("cpp", TAMBAH_CPP, "20 22\n")
    assert h["verdict"] == "OK" and h["stdout"] == "42\n" and h["stderr"] == "" and h["exit_code"] == 0
    assert 0 <= h["duration_ms"] < 500  # waktu menyalakan kontainer tidak dibebankan
    assert set(h) == {"verdict", "compile_log", "stdout", "stderr", "exit_code", "signal", "duration_ms", "memory_kb"}


def test_run_c_dan_python():
    c = '#include <stdio.h>\n#include <math.h>\nint main(){double x;scanf("%lf",&x);printf("%.3f\\n",sqrt(x));return 0;}'
    assert mesin.jalankan_sekali("c", c, "2")["stdout"] == "1.414\n"
    assert mesin.jalankan_sekali("python", "print(sum(map(int, input().split())))", "1 2 3")["stdout"] == "6\n"


def test_cout_dengan_spasi_literal_dan_double():
    sumber = '#include <iostream>\nusing namespace std;\nint main(){double x,y;cin>>x>>y;cout << x << " " << y << endl;}'
    assert mesin.jalankan_sekali("cpp", sumber, "1.5 2.25")["stdout"] == "1.5 2.25\n"


def test_numpy_dan_matplotlib_berjalan_di_bawah_semua_batas():
    sumber = ("import numpy as np\nimport matplotlib.pyplot as plt\nimport os\n"
              "a = np.arange(6).reshape(2, 3)\nprint(int(a.sum()), a.T.shape)\n"
              "plt.plot([1, 2, 3], [1, 4, 9])\nplt.savefig('/box/g.png')\nprint(os.path.getsize('/box/g.png') > 1000)\n")
    h = mesin.jalankan_sekali("python", sumber)
    assert h["verdict"] == "OK", h["stderr"]
    assert h["stdout"] == "15 (3, 2)\nTrue\n"
    assert h["duration_ms"] < 5000


def test_utf8_masuk_dan_keluar():
    h = mesin.jalankan_sekali("python", "print(input().upper())", "nilai é ش\n")
    assert h["stdout"] == "NILAI É ش\n"


def test_galat_kompilasi_c_memuat_log_lengkap_dengan_nomor_baris():
    h = mesin.jalankan_sekali("c", "int main(){\n  int x = ;\n  return 0;\n}\n")
    assert h["verdict"] == "CE" and h["compile_error"] == "compiler"
    assert "main.c:2:" in h["compile_log"] and "error" in h["compile_log"]
    assert "stdout" not in h


def test_peringatan_kompilasi_tidak_menggagalkan():
    h = mesin.jalankan_sekali("c", '#include <stdio.h>\nint main(){int x;printf("%d\\n", 7);return 0;}')
    assert h["verdict"] == "OK" and h["stdout"] == "7\n"


def test_galat_sintaks_python_adalah_ce_dan_tidak_menjalankan_apa_pun():
    h = mesin.jalankan_sekali("python", "print('sebelum')\nif True\n    print(1)\n")
    assert h["verdict"] == "CE"
    assert "line 2" in h["compile_log"] and "SyntaxError" in h["compile_log"]


def test_galat_saat_jalan():
    nol = mesin.jalankan_sekali("c", "#include <stdlib.h>\nint main(){exit(3);}")
    assert (nol["verdict"], nol["exit_code"], nol["signal"]) == ("RE", 3, 0)
    segv = mesin.jalankan_sekali("c", "int main(){int *p=0;*p=1;return 0;}")
    assert (segv["verdict"], segv["signal"]) == ("RE", 11)
    py = mesin.jalankan_sekali("python", "print(1/0)")
    assert py["verdict"] == "RE" and "ZeroDivisionError" in py["stderr"]


def test_program_yang_tidak_membaca_stdin_besar_tetap_selesai():
    h = mesin.jalankan_sekali("c", '#include <stdio.h>\nint main(){puts("ok");return 0;}', "x" * 900_000)
    assert h["verdict"] == "OK" and h["stdout"] == "ok\n"


@pytest.mark.parametrize("bahasa,sumber,stdin,pesan", [
    ("java", "class A{}", "", "language"),
    ("c", "   ", "", "source kosong"),
    ("c", "int main(){}" + " " * 300_000, "", "256 KB"),
    ("c", "int main(){}", "x" * 1_100_000, "stdin"),
])
def test_masukan_salah_ditolak_sebelum_ada_kontainer(bahasa, sumber, stdin, pesan):
    with pytest.raises(mesin.MasukanSalah) as info:
        mesin.jalankan_sekali(bahasa, sumber, stdin)
    assert pesan in str(info.value)


def test_kemajuan_dilaporkan():
    langkah = []
    mesin.jalankan_sekali("c", "int main(){return 0;}", "", maju=lambda *a: langkah.append(a))
    assert langkah == [("compiling", 0, 1), ("running", 0, 1), ("running", 1, 1)]


# ---- nilai_tes: dasar SUBMIT (tahap 9) ---------------------------------------------------

def test_semua_tes_lulus():
    tes = [Tes("1 2\n", "3\n"), Tes("10 20\n", "30"), Tes("-5 5\n", "0\n\n")]
    h = mesin.nilai_tes("cpp", TAMBAH_CPP, tes)
    assert (h["verdict"], h["passed"], h["total"]) == ("AC", 3, 3)
    assert [t["verdict"] for t in h["tests"]] == ["AC", "AC", "AC"]
    assert [t["index"] for t in h["tests"]] == [0, 1, 2]


def test_tes_yang_gagal_tidak_menghentikan_tes_berikutnya():
    # 0: benar · 1: salah · 2: RE · 3: TLE · 4: MLE · 5: OLE · 6: benar lagi
    sumber = r'''
import sys
n = int(input())
if n == 0 or n == 6: print("ok")
elif n == 1: print("salah")
elif n == 2: sys.exit(1)
elif n == 3:
    while True: pass
elif n == 4:
    x = []
    while True: x.append(bytearray(10**7))
elif n == 5:
    while True: print("a" * 1000)
'''
    tes = [Tes(f"{i}\n", "ok\n") for i in range(7)]
    langkah = []
    h = mesin.nilai_tes("python", sumber, tes, cpu_ms=1000, maju=lambda *a: langkah.append(a))
    assert [t["verdict"] for t in h["tests"]] == ["AC", "WA", "RE", "TLE", "MLE", "OLE", "AC"]
    assert (h["verdict"], h["passed"], h["total"]) == ("WA", 2, 7)
    assert langkah[0] == ("compiling", 0, 7) and langkah[-1] == ("running", 7, 7)


def test_hasil_tidak_pernah_memuat_masukan_atau_keluaran_tes():
    tes = [Tes("11111 22222\n", "33333\n"), Tes("44444 55555\n", "SALAH-99999\n")]
    h = mesin.nilai_tes("cpp", TAMBAH_CPP, tes)
    teks = json.dumps(h)
    for rahasia in ["11111", "22222", "33333", "44444", "55555", "99999"]:
        assert rahasia not in teks
    assert set(h) == {"verdict", "compile_log", "tests", "passed", "total"}
    assert all(set(t) == {"index", "verdict", "duration_ms"} for t in h["tests"])


def test_galat_kompilasi_pada_nilai_tes():
    h = mesin.nilai_tes("c", "int main(){ salah }", [Tes("", "")] * 3)
    assert (h["verdict"], h["passed"], h["total"], h["tests"]) == ("CE", 0, 3, [])


def test_mode_perbandingan_dipakai():
    sumber = 'print("3.14159265  2.71828183")'
    assert mesin.nilai_tes("python", sumber, [Tes("", "3.1415927 2.7182818\n")], mode="float")["verdict"] == "AC"
    assert mesin.nilai_tes("python", sumber, [Tes("", "3.14159265 2.71828183\n")], mode="token")["verdict"] == "AC"
    assert mesin.nilai_tes("python", sumber, [Tes("", "3.14159265 2.71828183\n")], mode="trimmed")["verdict"] == "WA"


# ---- kompilasi yang kehabisan waktu: sumber yang berat, atau server yang sibuk? ---------

def test_kompilasi_berat_adalah_ce_timeout_untuk_run_dan_submit():
    h = mesin.jalankan_sekali("c", KOMPILASI_BERAT)
    assert h["verdict"] == "CE" and h["compile_error"] == "timeout"
    n = mesin.nilai_tes("c", KOMPILASI_BERAT, [Tes("", "")])
    assert n["verdict"] == "CE" and n["compile_error"] == "timeout" and n["passed"] == 0


def test_kompilasi_yang_kelaparan_cpu_bukan_ce_melainkan_galat_infrastruktur(monkeypatch):
    # Kontainer kompilasi hanya diberi 0,3 CPU: batas 10 detik kena padahal kompiler baru
    # bekerja sekitar 3 detik. Itu bukan kesalahan mahasiswa; worker mengulang pekerjaannya
    # (test_worker: dua kali, lalu judge_unavailable) dan tidak ada vonis CE.
    monkeypatch.setattr(kotak, "CPU_KONTAINER", "0.3")
    with pytest.raises(kotak.GalatInfrastruktur, match="server sibuk"):
        mesin.jalankan_sekali("c", KOMPILASI_BERAT)
    with pytest.raises(kotak.GalatInfrastruktur, match="server sibuk"):
        mesin.nilai_tes("c", KOMPILASI_BERAT, [Tes("", "")])
    with pytest.raises(kotak.GalatInfrastruktur, match="server sibuk"):
        mesin.jalankan_contoh("c", KOMPILASI_BERAT, [Tes("", "")])
