"""SUBMIT dan RUN pada tes contoh lewat worker sungguhan, MySQL, Redis dan Docker — KONTRAK.md §6, SPEC.md §7, §10.

Instansnya dibuat oleh pekerjaan GENERATE sungguhan, persis seperti di produksi."""

from __future__ import annotations

import json
import uuid

import pytest

from test_worker_soal import instans, versi_soal  # noqa: F401  (versi_soal adalah fixture)

BENAR = '#include <iostream>\nint main(){long a,b;std::cin>>a>>b;std::cout<<a+b<<"\\n";}\n'
# Salah untuk a genap: mencetak 0.
SEBAGIAN = '#include <iostream>\nint main(){long a,b;std::cin>>a>>b;std::cout<<(a%2?a+b:0)<<"\\n";}\n'
# Berputar selamanya untuk a genap; benar untuk a ganjil.
MACET = '#include <iostream>\nint main(){long a,b;std::cin>>a>>b;if(a%2==0)for(;;){}std::cout<<a+b<<"\\n";}\n'
# Keluar dengan kode 3 untuk a genap.
JATUH = '#include <iostream>\nint main(){long a,b;std::cin>>a>>b;if(a%2==0)return 3;std::cout<<a+b<<"\\n";}\n'
DENGAN_PERULANGAN = ('#include <iostream>\nint main(){long a,b,s=0;std::cin>>a>>b;'
                     'for(int i=0;i<1;i++){s=a+b;}std::cout<<s<<"\\n";}\n')


@pytest.fixture()
def siap(mulai_worker, kirim, tunggu, versi_soal, db_app):  # noqa: F811
    """Worker berjalan dan satu mahasiswa punya instans. Mengembalikan (user, id instans, tes, worker)."""

    def _siap(**ganti):
        w = mulai_worker()
        v = versi_soal(**ganti)
        user = str(uuid.uuid4())
        b = tunggu(kirim({**v, "user_id": user, "seed": 424242}, kind="GENERATE"), waktu=120)
        assert b["status"] == "DONE", b
        baris = instans(db_app, user, v["meeting_problem_id"])[0]
        return user, baris["id"], json.loads(baris["tests"]), w

    return _siap


def submit(kirim, tunggu, user, iid, sumber, **ganti):
    payload = {"language": "cpp", "source": sumber, "instance_id": iid, "submission_id": 1, **ganti}
    return tunggu(kirim(payload, kind="SUBMIT", pembuat=user), waktu=180)


def genap(tes: list[dict]) -> list[bool]:
    """Untuk tiap tes TERSEMBUNYI (sesudah dua contoh): apakah a genap."""
    return [int(t["in"].split()[0]) % 2 == 0 for t in tes[2:]]


def test_submit_benar_semua_tes_tersembunyi_lulus_dan_hasil_tidak_memuat_tes(siap, kirim, tunggu):
    user, iid, tes, w = siap()
    b = submit(kirim, tunggu, user, iid, BENAR)
    assert b["status"] == "DONE" and b["attempts"] == 1
    h = b["result"]
    assert (h["verdict"], h["passed"], h["total"]) == ("AC", 10, 10)
    assert [t["index"] for t in h["tests"]] == list(range(10)), "hanya 10 tes tersembunyi; 2 contoh tidak dinilai"
    assert all(set(t) == {"index", "verdict", "duration_ms"} and t["verdict"] == "AC" for t in h["tests"])
    assert h["concepts"] is None and h["missing"] == []
    assert set(h) == {"verdict", "compile_log", "tests", "passed", "total", "concepts", "missing"}
    # SPEC.md §10: masukan dan keluaran tes tersembunyi tidak pernah keluar dari worker.
    semua = json.dumps(b["result"]) + json.dumps(b["progress"]) + json.dumps(w.log())
    for t in tes:
        assert t["in"].strip() not in semua and t["out"].strip() not in semua


def test_submit_sebagian_benar_menghitung_tes_yang_lulus(siap, kirim, tunggu):
    user, iid, tes, _ = siap()
    g = genap(tes)
    assert 0 < sum(g) < 10, "instans uji harus punya tes a genap dan a ganjil"
    h = submit(kirim, tunggu, user, iid, SEBAGIAN)["result"]
    assert [t["verdict"] for t in h["tests"]] == ["WA" if x else "AC" for x in g]
    assert (h["verdict"], h["passed"], h["total"]) == ("WA", 10 - sum(g), 10)


def test_submit_tle_dan_re_hanya_menggagalkan_tes_itu(siap, kirim, tunggu):
    """SPEC.md §7: RE / TLE menggagalkan tes itu saja; yang lain tetap dijalankan."""
    user, iid, tes, _ = siap(time_limit_ms=300)
    g = genap(tes)
    h = submit(kirim, tunggu, user, iid, MACET)["result"]
    assert [t["verdict"] for t in h["tests"]] == ["TLE" if x else "AC" for x in g]
    assert h["verdict"] == "TLE" and h["passed"] == 10 - sum(g)
    h = submit(kirim, tunggu, user, iid, JATUH)["result"]
    assert [t["verdict"] for t in h["tests"]] == ["RE" if x else "AC" for x in g]
    assert h["verdict"] == "RE" and h["passed"] == 10 - sum(g)


def test_submit_galat_kompilasi_nol_dengan_log_kompiler(siap, kirim, tunggu):
    user, iid, _, _ = siap()
    b = submit(kirim, tunggu, user, iid, "int main() { ini bukan C++; }\n")
    h = b["result"]
    assert b["status"] == "DONE" and h["verdict"] == "CE" and h["compile_error"] == "compiler"
    assert (h["tests"], h["passed"], h["total"]) == ([], 0, 10)
    assert "error" in h["compile_log"] and "main.cpp:1" in h["compile_log"]


def test_submit_konsep_yang_disyaratkan_tidak_ada_menjadi_concept_not_met(siap, kirim, tunggu):
    """SPEC.md §9.2: keluaran benar tetapi konsep yang disyaratkan tidak dipakai."""
    user, iid, _, _ = siap(required_in_submission=["PERULANGAN"])
    h = submit(kirim, tunggu, user, iid, BENAR)["result"]
    assert (h["verdict"], h["passed"], h["total"], h["missing"]) == ("CONCEPT_NOT_MET", 10, 10, ["PERULANGAN"])
    assert "PERULANGAN" not in h["concepts"] and "IO_DASAR" in h["concepts"]

    h = submit(kirim, tunggu, user, iid, DENGAN_PERULANGAN)["result"]
    assert (h["verdict"], h["missing"]) == ("AC", []) and "PERULANGAN" in h["concepts"]

    # Kiriman yang belum lulus semua tes tidak dianalisis: verdict-nya tetap verdict tesnya.
    h = submit(kirim, tunggu, user, iid, SEBAGIAN)["result"]
    assert h["verdict"] == "WA" and h["concepts"] is None and h["missing"] == []


def test_instans_orang_lain_dan_pekerjaan_sistem_ditolak(siap, kirim, tunggu):
    user, iid, _, _ = siap()
    lain = str(uuid.uuid4())
    for kind, payload, pembuat in [
        ("SUBMIT", {"language": "cpp", "source": BENAR, "instance_id": iid, "submission_id": 1}, lain),
        ("SUBMIT", {"language": "cpp", "source": BENAR, "instance_id": iid, "submission_id": 1}, None),
        ("RUN", {"language": "cpp", "source": BENAR, "instance_id": iid}, lain),
        ("SUBMIT", {"language": "cpp", "source": BENAR, "instance_id": 99999999, "submission_id": 1}, user),
        ("SUBMIT", {"language": "cpp", "source": BENAR, "instance_id": str(iid), "submission_id": 1}, user),
        ("SUBMIT", {"language": "cpp", "source": BENAR, "instance_id": iid}, user),                 # tanpa submission_id
        ("SUBMIT", {"language": "python", "source": "print(1)", "instance_id": iid, "submission_id": 1}, user),
        ("RUN", {"language": "cpp", "source": BENAR, "instance_id": iid, "stdin": "1 2\n"}, user),  # dua-duanya
    ]:
        b = tunggu(kirim(payload, kind=kind, pembuat=pembuat))
        assert b["status"] == "FAILED" and b["result"]["error"] == "invalid_payload" and b["attempts"] == 1, (payload, pembuat, b)


def test_run_pada_tes_contoh_mengembalikan_keluaran_dan_tidak_menyentuh_tes_tersembunyi(siap, kirim, tunggu):
    user, iid, tes, w = siap()
    b = tunggu(kirim({"language": "cpp", "source": BENAR, "instance_id": iid}, pembuat=user), waktu=60)
    h = b["result"]
    assert b["status"] == "DONE" and h["verdict"] == "AC" and len(h["samples"]) == 2
    for i, c in enumerate(h["samples"]):
        assert (c["index"], c["verdict"], c["stdout"], c["exit_code"]) == (i, "AC", tes[i]["out"], 0)
    assert set(h) == {"verdict", "compile_log", "samples"}

    salah = '#include <iostream>\nint main(){std::cout<<"tidak tahu\\n";}\n'
    h = tunggu(kirim({"language": "cpp", "source": salah, "instance_id": iid}, pembuat=user), waktu=60)["result"]
    assert h["verdict"] == "WA" and [c["verdict"] for c in h["samples"]] == ["WA", "WA"]
    assert h["samples"][0]["stdout"] == "tidak tahu\n"

    # Program yang mencetak masukannya hanya pernah melihat dua tes contoh.
    gema = '#include <iostream>\n#include <string>\nint main(){std::string s;std::getline(std::cin,s);std::cout<<s<<"\\n";}\n'
    h = tunggu(kirim({"language": "cpp", "source": gema, "instance_id": iid}, pembuat=user), waktu=60)["result"]
    terlihat = json.dumps(h) + json.dumps(w.log())
    for t in tes[2:]:
        assert t["in"].strip() not in terlihat

    h = tunggu(kirim({"language": "cpp", "source": "int main( {", "instance_id": iid}, pembuat=user), waktu=60)["result"]
    assert h["verdict"] == "CE" and h["samples"] == []


def test_bendera_kompiler_pertemuan_dipakai_dan_yang_di_luar_daftar_ditolak(mulai_worker, kirim, tunggu):
    mulai_worker()
    sumber = '#include <iostream>\nint main(){int tidak_dipakai;std::cout<<"ok\\n";}\n'
    h = tunggu(kirim({"language": "cpp", "source": sumber}))["result"]
    assert h["verdict"] == "OK" and "tidak_dipakai" not in h["compile_log"]
    h = tunggu(kirim({"language": "cpp", "source": sumber, "flags": ["-Wall"]}))["result"]
    assert h["verdict"] == "OK" and "unused variable" in h["compile_log"] and h["stdout"] == "ok\n"

    # -std=c++20 benar-benar sampai ke kompiler: <numbers> tidak ada di C++17.
    pi = '#include <iostream>\n#include <numbers>\nint main(){std::cout<<(std::numbers::pi>3)<<"\\n";}\n'
    assert tunggu(kirim({"language": "cpp", "source": pi}))["result"]["verdict"] == "CE"
    h = tunggu(kirim({"language": "cpp", "source": pi, "flags": ["-std=c++20"]}))["result"]
    assert h["verdict"] == "OK" and h["stdout"] == "1\n"

    for bendera, bahasa in [(["-o/box/x"], "cpp"), (["-include/etc/passwd"], "cpp"), (["-Wall -Wextra"], "cpp"), ("-Wall", "cpp"),
                            (["-std=c++20"], "c"), (["-Wall"], "python"), (["-Wall"] * 9, "cpp"), ([7], "cpp")]:
        b = tunggu(kirim({"language": bahasa, "source": "int main(){}", "flags": bendera}))
        assert b["status"] == "FAILED" and b["result"]["error"] == "invalid_payload", (bendera, bahasa, b)
