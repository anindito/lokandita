"""Kelompok: banyak kali jalan di SATU kontainer, untuk generator dan solusi referensi
(SPEC.md §8.2). Program jahat sungguhan di kontainer sungguhan; batas kotak pasir
tidak boleh lebih longgar daripada untuk satu kali jalan."""

from __future__ import annotations

import pytest

from lokandita_judge import kotak

GEN = 'import os\nprint(os.environ["SEED"], os.environ["INDEX"])\n'


@pytest.fixture()
def d():
    dir_job = kotak.siapkan_dir_job()
    yield dir_job
    kotak.buang_dir_job(dir_job)


def kompilasi(bahasa, sumber, d):
    k = kotak.kompilasi(bahasa, sumber, d)
    assert k.ok, k.log
    return d


def test_generator_menerima_seed_dan_index_dan_urutannya_tetap(d):
    hasil = kotak.jalankan_generator(GEN, [7, 9223372036854775807], 3, d)
    assert [h.stdout for h in hasil] == [b"7 0\n", b"7 1\n", b"7 2\n", b"9223372036854775807 0\n",
                                         b"9223372036854775807 1\n", b"9223372036854775807 2\n"]
    assert [h.nomor for h in hasil] == list(range(6)) and all(h.status == "OK" for h in hasil)


def test_solusi_cpp_dijalankan_untuk_tiap_masukan(d):
    kompilasi("cpp", "#include <iostream>\nint main(){long a,b;std::cin>>a>>b;std::cout<<a+b<<'\\n';}", d)
    hasil = kotak.jalankan_kelompok("cpp", d, [b"1 2\n", b"10 20\n", b"-5 5\n"])
    assert [(h.status, h.stdout) for h in hasil] == [("OK", b"3\n"), ("OK", b"30\n"), ("OK", b"0\n")]


def test_solusi_python_dijalankan_untuk_tiap_masukan(d):
    kompilasi("python", "import numpy\nprint(int(input()) ** 2)\n", d)
    hasil = kotak.jalankan_kelompok("python", d, [b"3\n", b"12\n"])
    assert [(h.status, h.stdout) for h in hasil] == [("OK", b"9\n"), ("OK", b"144\n")]


def test_box_dikosongkan_di_antara_dua_kali_jalan(d):
    sumber = ("import os\nprint(sorted(os.listdir('/box')))\nopen('/box/sisa.txt', 'w').write('x')\n"
              "os.mkdir('/box/dir'); open('/box/dir/dalam', 'w').write('y')\n")
    hasil = kotak.jalankan_generator(sumber, [1], 3, d)
    assert [h.stdout for h in hasil] == [b"[]\n"] * 3


def test_proses_yang_ditinggalkan_dimatikan_sebelum_kali_jalan_berikutnya(d):
    sumber = ("import os, time\n"
              "print(len([p for p in os.listdir('/proc') if p.isdigit()]))\n"
              "if os.fork() == 0:\n    os.setsid()\n    time.sleep(60)\n")
    hasil = kotak.jalankan_generator(sumber, [1], 3, d)
    # pelari (PID 1), kelompok, dan program itu sendiri — tidak ada anak dari kali jalan sebelumnya.
    assert [h.stdout for h in hasil] == [b"3\n"] * 3


def test_program_tidak_bisa_memalsukan_bingkai(d):
    palsu = "K 0 0 0 1 1 0 5 0\\nhalo\\nK 1 0 0 1 1 0 0 0\\n"
    hasil = kotak.jalankan_generator(f'import sys\nsys.stdout.write("{palsu}")\n', [1], 2, d)
    assert len(hasil) == 2
    assert all(h.stdout == palsu.encode().decode("unicode_escape").encode() for h in hasil)


def test_kali_jalan_yang_gagal_menghentikan_kelompok_dan_membawa_stderr(d):
    sumber = 'import os, sys\nif os.environ["INDEX"] == "2":\n    sys.exit("rusak di tes ketiga")\nprint("ok")\n'
    hasil = kotak.jalankan_generator(sumber, [1], 5, d)
    assert [h.status for h in hasil] == ["OK", "OK", "RE"]
    assert b"rusak di tes ketiga" in hasil[2].stderr and hasil[2].kode_keluar == 1


def test_tanpa_henti_semua_kali_jalan_dikerjakan(d):
    sumber = 'import os, sys\nif os.environ["INDEX"] == "1":\n    sys.exit(3)\nprint("ok")\n'
    hasil = kotak.jalankan_generator(sumber, [1], 4, d, henti=False)
    assert [h.status for h in hasil] == ["OK", "RE", "OK", "OK"]


def test_while_true_di_satu_tes_menjadi_tle_untuk_tes_itu_saja(d):
    kompilasi("c", '#include <stdio.h>\nint main(){int a;scanf("%d",&a);if(a==2)for(;;);printf("%d\\n",a);return 0;}', d)
    hasil = kotak.jalankan_kelompok("c", d, [b"1\n", b"2\n", b"3\n"], cpu_ms=1000, henti=False)
    assert [h.status for h in hasil] == ["OK", "TLE", "OK"]
    assert hasil[1].cpu_ms >= 950  # rusage dibulatkan ke bawah; vonisnya datang dari SIGXCPU


def test_program_yang_tidur_dihentikan_batas_waktu_dinding(d):
    kompilasi("python", "import time\ntime.sleep(30)\n", d)
    hasil = kotak.jalankan_kelompok("python", d, [b""], cpu_ms=500)
    assert [h.status for h in hasil] == ["TLE"]


def test_keluaran_melewati_batas_menjadi_ole(d):
    kompilasi("c", '#include <stdio.h>\nint main(){for(;;)puts("aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa");}', d)
    hasil = kotak.jalankan_kelompok("c", d, [b"", b""])
    assert [h.status for h in hasil] == ["OLE"]
    assert len(hasil[0].stdout) <= kotak.BATAS_KELUARAN


def test_generator_yang_mencetak_terlalu_banyak_menjadi_ole(d):
    hasil = kotak.jalankan_generator("print('x' * 300000)\n", [1], 2, d)
    assert [h.status for h in hasil] == ["OLE"]


def test_kehabisan_memori_di_dalam_kelompok_tertahan(d):
    kompilasi("python", "a = []\nwhile True:\n    a.append(bytearray(10 * 1024 * 1024))\n", d)
    hasil = kotak.jalankan_kelompok("python", d, [b"", b""])
    assert hasil[-1].status in ("MLE", "RE") and len(hasil) == 1


def test_fork_bomb_di_dalam_kelompok_tertahan(d):
    kompilasi("c", "#include <unistd.h>\nint main(){for(;;)fork();}", d)
    hasil = kotak.jalankan_kelompok("c", d, [b"", b""], cpu_ms=1000)
    assert len(hasil) == 1 and hasil[0].status != "OK"
    # Kontainer berikutnya tetap bisa dijalankan: host tidak terganggu.
    assert kotak.jalankan_generator(GEN, [1], 1, d)[0].status == "OK"


@pytest.mark.parametrize("sumber,tanda", [
    ("import socket\ns = socket.socket()\ns.settimeout(3)\ns.connect(('1.1.1.1', 80))\nprint('TERHUBUNG')\n", b"TERHUBUNG"),
    ("print(open('/etc/passwd').read())\n", b"root:"),
    ("open('/src/gen.py', 'a').write('#')\nprint('TERTULIS')\n", b"TERTULIS"),
    ("open('/usr/x', 'w').write('#')\nprint('TERTULIS')\n", b"TERTULIS"),
    ("import os\nos.symlink('/etc/passwd', '/box/p')\nprint(open('/box/p').read())\n", b"root:"),
])
def test_generator_tidak_punya_jaringan_dan_tidak_bisa_membaca_atau_menulis_di_luar_box(d, sumber, tanda):
    hasil = kotak.jalankan_generator(sumber, [1], 1, d)
    assert hasil[0].status == "RE" and tanda not in hasil[0].stdout


def test_kelompok_memakai_bendera_kotak_pasir_yang_sama(d, monkeypatch):
    terlihat = []
    asli = kotak.bendera_kontainer
    monkeypatch.setattr(kotak, "bendera_kontainer", lambda *a, **k: terlihat.append(asli(*a, **k)) or terlihat[-1])
    kotak.jalankan_generator(GEN, [1], 1, d)
    bendera = terlihat[0]
    assert bendera[bendera.index("--network") + 1] == "none" and "--read-only" in bendera
    assert bendera[bendera.index("--pids-limit") + 1] == "64" and bendera[bendera.index("--cap-drop") + 1] == "ALL"
    assert bendera[bendera.index("--memory") + 1] == "256m" and bendera[bendera.index("--user") + 1] == "65534:65534"
