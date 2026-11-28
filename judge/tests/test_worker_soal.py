"""VALIDATE, GENERATE dan ANALYZE lewat worker sungguhan, MySQL, Redis dan Docker — KONTRAK.md §6."""

from __future__ import annotations

import json
import uuid

import pytest

from test_soal import GEN, soal_baik, versi


@pytest.fixture()
def versi_soal(db_app, db_roster):
    """Satu soal dengan versi yang disetujui dan dipakai sebuah pertemuan, dibuat seperti oleh web."""

    def _buat(status: str = "APPROVED", **ganti) -> dict:
        syarat = json.dumps(ganti.pop("required_in_submission", []))
        mode = ganti.pop("comparison_mode", "trimmed")
        v = versi(**ganti)
        kode = "UJ" + uuid.uuid4().hex[:8].upper()
        # Mata kuliah adalah baris roster: ditulis sebagai auth_app, bukan lokakode_app.
        with db_roster.cursor() as cur:
            cur.execute("INSERT INTO courses (code, name, owner_id, created_at, updated_at)"
                        " VALUES (%s, 'Uji judge', 'pengampu-uji', UTC_TIMESTAMP(), UTC_TIMESTAMP())", (kode,))
            matkul = cur.lastrowid
        with db_app.cursor() as cur:
            cur.execute("INSERT INTO meetings (course_id, number, title, language, created_at, updated_at)"
                        " VALUES (%s, 1, 'Uji', %s, UTC_TIMESTAMP(), UTC_TIMESTAMP())", (matkul, v["language"]))
            pertemuan = cur.lastrowid
            cur.execute("INSERT INTO problems (meeting_id, slug, created_at) VALUES (%s, 'jumlah-dua', UTC_TIMESTAMP())", (pertemuan,))
            soal_id = cur.lastrowid
            cur.execute(
                "INSERT INTO problem_versions (problem_id, version, status, language, difficulty, time_limit_ms, samples_count,"
                " hidden_count, params, translations, generator, reference_solution, concepts, required_in_submission,"
                " comparison_mode, content_hash, created_by, created_at) VALUES (%s, 1, %s, %s, 'MUDAH', %s, %s, %s, '{}', '{}',"
                " %s, %s, '[]', %s, %s, REPEAT('a', 64), 'uji', UTC_TIMESTAMP())",
                (soal_id, status, v["language"], v["time_limit_ms"], v["samples_count"], v["hidden_count"], v["generator"],
                 v["reference_solution"], syarat, mode))
            versi_id = cur.lastrowid
            cur.execute("INSERT INTO meeting_problems (meeting_id, problem_id, problem_version_id, updated_at)"
                        " VALUES (%s, %s, %s, UTC_TIMESTAMP())", (pertemuan, soal_id, versi_id))
            return {"problem_version_id": versi_id, "meeting_problem_id": cur.lastrowid}

    return _buat


def instans(db_app, user: str, mp: int) -> list[dict]:
    with db_app.cursor() as cur:
        cur.execute("SELECT * FROM problem_instances WHERE user_id = %s AND meeting_problem_id = %s", (user, mp))
        return list(cur.fetchall())


def test_validate_soal_yang_baik_lewat_worker(mulai_worker, kirim, tunggu):
    mulai_worker()
    b = tunggu(kirim(soal_baik(), kind="VALIDATE"), waktu=180)
    assert b["status"] == "DONE" and b["attempts"] == 1
    assert b["result"]["ok"] is True and b["result"]["reasons"] == [] and b["result"]["tests"] == 240
    assert sorted(b["result"]["concepts"]) == ["IO_DASAR", "VARIABEL_TIPE"]
    assert b["progress"]["state"] == "done"


def test_validate_soal_yang_salah_adalah_done_dengan_alasan_bukan_failed(mulai_worker, kirim, tunggu):
    """Soal yang ditolak adalah hasil, bukan kegagalan worker: tidak diulang."""
    mulai_worker()
    ref = "#include <iostream>\nint main() {\n    int a;\n    std::cin >> a;\n    if (a) std::cout << a;\n}\n"
    b = tunggu(kirim(soal_baik(reference_solution=ref), kind="VALIDATE"), waktu=180)
    assert b["status"] == "DONE" and b["attempts"] == 1 and b["result"]["ok"] is False
    alasan = b["result"]["reasons"][0]
    assert (alasan["code"], alasan["concept"], alasan["line"], alasan["column"]) == ("concept_not_allowed", "PERCABANGAN", 5, 5)


def test_validate_payload_salah_dijawab_invalid_payload(mulai_worker, kirim, tunggu):
    mulai_worker()
    b = tunggu(kirim(soal_baik(hidden_count=3), kind="VALIDATE"))
    assert b["status"] == "FAILED" and b["result"]["error"] == "invalid_payload" and "hidden_count" in b["result"]["message"]


def test_generate_menulis_instans_dan_hasil_pekerjaan_tidak_memuat_tes(mulai_worker, kirim, tunggu, versi_soal, db_app):
    w = mulai_worker()
    v = versi_soal()
    user = str(uuid.uuid4())
    b = tunggu(kirim({**v, "user_id": user, "seed": 987654321}, kind="GENERATE"), waktu=120)
    assert b["status"] == "DONE" and b["result"] == {"ok": True, "existed": False, "tests": 12}

    baris = instans(db_app, user, v["meeting_problem_id"])
    assert len(baris) == 1 and baris[0]["seed"] == 987654321 and baris[0]["problem_version_id"] == v["problem_version_id"]
    tes = json.loads(baris[0]["tests"])
    assert len(tes) == 12
    for t in tes:
        a, c = (int(x) for x in t["in"].split())
        assert t["out"] == f"{a + c}\n"
    # Tidak ada masukan atau keluaran tes di hasil pekerjaan maupun di log worker.
    contoh = tes[0]["in"].strip()
    assert contoh not in json.dumps(b["result"]) and contoh not in json.dumps(w.log())


def test_generate_idempoten_instans_yang_ada_tidak_dibuat_ulang(mulai_worker, kirim, tunggu, versi_soal, db_app):
    mulai_worker()
    v = versi_soal()
    user = str(uuid.uuid4())
    tunggu(kirim({**v, "user_id": user, "seed": 5}, kind="GENERATE"), waktu=120)
    pertama = instans(db_app, user, v["meeting_problem_id"])[0]
    # Membuka ulang, bahkan dengan seed lain, tidak mengubah instans (SPEC.md §8.2).
    b = tunggu(kirim({**v, "user_id": user, "seed": 6}, kind="GENERATE"), waktu=120)
    assert b["status"] == "DONE" and b["result"] == {"ok": True, "existed": True}
    assert instans(db_app, user, v["meeting_problem_id"]) == [pertama]


def test_generate_dua_mahasiswa_mendapat_tes_berbeda(mulai_worker, kirim, tunggu, versi_soal, db_app):
    mulai_worker()
    v = versi_soal()
    a, b = str(uuid.uuid4()), str(uuid.uuid4())
    id_a = kirim({**v, "user_id": a, "seed": 111}, kind="GENERATE", priority="low")
    id_b = kirim({**v, "user_id": b, "seed": 222}, kind="GENERATE", priority="low")
    assert tunggu(id_a, waktu=120)["status"] == "DONE" and tunggu(id_b, waktu=120)["status"] == "DONE"
    assert instans(db_app, a, v["meeting_problem_id"])[0]["tests"] != instans(db_app, b, v["meeting_problem_id"])[0]["tests"]


def test_generate_untuk_versi_yang_belum_disetujui_ditolak(mulai_worker, kirim, tunggu, versi_soal, db_app):
    mulai_worker()
    v = versi_soal(status="PENDING_REVIEW")
    user = str(uuid.uuid4())
    b = tunggu(kirim({**v, "user_id": user, "seed": 5}, kind="GENERATE"))
    assert b["status"] == "FAILED" and b["result"]["error"] == "invalid_payload"
    assert instans(db_app, user, v["meeting_problem_id"]) == []


def test_generate_yang_gagal_tidak_membocorkan_pesan_generator(mulai_worker, kirim, tunggu, versi_soal, db_app):
    mulai_worker()
    v = versi_soal(generator='import sys\nsys.exit("RAHASIA-DARI-GENERATOR")\n')
    user = str(uuid.uuid4())
    b = tunggu(kirim({**v, "user_id": user, "seed": 5}, kind="GENERATE"), waktu=120)
    assert b["status"] == "FAILED" and b["attempts"] == 1
    assert b["result"] == {"error": "generation_failed", "reason": {"code": "generator_failed", "where": "generator", "verdict": "RE", "index": 0}}
    assert instans(db_app, user, v["meeting_problem_id"]) == []


@pytest.mark.parametrize("payload", [
    {"problem_version_id": "1", "meeting_problem_id": 1, "user_id": str(uuid.uuid4()), "seed": 1},
    {"problem_version_id": 1, "meeting_problem_id": 1, "user_id": "bukan-uuid", "seed": 1},
    {"problem_version_id": 999999999, "meeting_problem_id": 1, "user_id": str(uuid.uuid4()), "seed": 1},
    {"problem_version_id": 1, "meeting_problem_id": 1, "user_id": str(uuid.uuid4())},
])
def test_generate_payload_salah_dijawab_invalid_payload(mulai_worker, kirim, tunggu, payload):
    mulai_worker()
    b = tunggu(kirim(payload, kind="GENERATE"))
    assert b["status"] == "FAILED" and b["result"]["error"] == "invalid_payload" and b["attempts"] == 1


def test_analyze_lewat_worker(mulai_worker, kirim, tunggu):
    w = mulai_worker()
    sumber = "#include <iostream>\nclass Hewan { public: int kaki; };\nint main() { Hewan h; std::cin >> h.kaki; std::cout << h.kaki; }\n"
    b = tunggu(kirim({"language": "cpp", "source": sumber, "required_concepts": ["CLASS_OBJECT", "INHERITANCE"]}, kind="ANALYZE"))
    assert b["status"] == "DONE"
    assert "CLASS_OBJECT" in b["result"]["concepts"] and b["result"]["missing"] == ["INHERITANCE"]
    assert "Hewan" not in json.dumps(w.log())


def test_analyze_kode_yang_tidak_terurai_dijawab_analysis_failed(mulai_worker, kirim, tunggu):
    mulai_worker()
    b = tunggu(kirim({"language": "python", "source": "def f(:\n", "required_concepts": []}, kind="ANALYZE"))
    assert b["status"] == "FAILED" and b["result"]["error"] == "analysis_failed" and b["attempts"] == 1
