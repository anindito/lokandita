"""KONTRAK.md §6 dan §10 terhadap MySQL dan Redis sungguhan."""

from __future__ import annotations

import os

import pytest

from conftest import galat_mysql
from lokandita_judge import konfig

DITOLAK_TABEL = 1142  # ER_TABLEACCESS_DENIED_ERROR
DITOLAK_SKEMA = 1044  # ER_DBACCESS_DENIED_ERROR


def test_kolom_judge_jobs_sesuai_kontrak(db_app):
    with db_app.cursor() as cur:
        cur.execute(
            "SELECT column_name AS n, column_type AS t, is_nullable AS nul FROM information_schema.columns"
            " WHERE table_schema = DATABASE() AND table_name = 'judge_jobs' ORDER BY ordinal_position"
        )
        kolom = {b["n"]: (b["t"], b["nul"]) for b in cur.fetchall()}
    assert list(kolom) == [
        "id", "kind", "priority", "status", "payload", "progress", "result", "attempts",
        "idempotency_key", "created_by", "created_at", "started_at", "finished_at",
    ]
    assert kolom["kind"][0] == "enum('RUN','SUBMIT','GENERATE','VALIDATE','ANALYZE')"
    assert kolom["priority"][0] == "enum('normal','low')"
    assert kolom["status"][0] == "enum('QUEUED','RUNNING','DONE','FAILED')"
    assert kolom["payload"] == ("json", "NO")
    assert kolom["created_by"] == ("char(36)", "YES")


def test_idempotency_key_unik(db_app):
    with db_app.cursor() as cur:
        cur.execute("DELETE FROM judge_jobs WHERE idempotency_key = 'uji-unik'")
        sisip = (
            "INSERT INTO judge_jobs (id, kind, payload, idempotency_key, created_at)"
            " VALUES (UUID(), 'RUN', '{}', 'uji-unik', UTC_TIMESTAMP())"
        )
        cur.execute(sisip)
    assert galat_mysql(db_app, sisip) == 1062  # ER_DUP_ENTRY


def test_worker_membaca_dan_mengubah_judge_jobs(db_worker, buat_job):
    id_ = buat_job()
    with db_worker.cursor() as cur:
        diubah = cur.execute(
            "UPDATE judge_jobs SET status = 'RUNNING', started_at = UTC_TIMESTAMP(), attempts = attempts + 1"
            " WHERE id = %s AND status = 'QUEUED'",
            (id_,),
        )
        assert diubah == 1
        cur.execute("SELECT status, attempts FROM judge_jobs WHERE id = %s", (id_,))
        assert cur.fetchone() == {"status": "RUNNING", "attempts": 1}


def test_worker_tidak_boleh_menyisipkan_atau_menghapus_job(db_worker, buat_job):
    id_ = buat_job()
    sisip = "INSERT INTO judge_jobs (id, kind, payload, created_at) VALUES (UUID(), 'RUN', '{}', UTC_TIMESTAMP())"
    assert galat_mysql(db_worker, sisip) == DITOLAK_TABEL
    assert galat_mysql(db_worker, f"DELETE FROM judge_jobs WHERE id = '{id_}'") == DITOLAK_TABEL
    assert galat_mysql(db_worker, "DROP TABLE judge_jobs") == DITOLAK_TABEL


@pytest.mark.parametrize(
    "tabel",
    ["academic_years", "courses", "classes", "class_teachers", "enrollments", "enrollment_moves", "audit_log", "migrations"],
)
def test_worker_tidak_bisa_membaca_tabel_lain_lokakode(db_worker, tabel):
    assert galat_mysql(db_worker, f"SELECT COUNT(*) FROM {tabel}") == DITOLAK_TABEL


def test_worker_hanya_melihat_judge_jobs(db_worker):
    with db_worker.cursor() as cur:
        cur.execute("SHOW TABLES")
        assert [list(b.values())[0] for b in cur.fetchall()] == ["judge_jobs"]


@pytest.mark.parametrize("skema", ["identity_test", "identity", "portal_test"])
def test_worker_tidak_bisa_masuk_skema_lain(db_worker, skema):
    assert galat_mysql(db_worker, f"SELECT COUNT(*) FROM `{skema}`.users") in (DITOLAK_TABEL, DITOLAK_SKEMA)


def test_redis_tes_adalah_db_15(r, k):
    assert k.redis_db == 15
    assert r.ping() is True
    assert r.connection_pool.connection_kwargs["db"] == 15


def test_variabel_wajib_diambil_dari_env_example():
    assert konfig.kunci_wajib() == ["DB_DSN", "REDIS_URL", "JUDGE_CONCURRENCY"]


@pytest.mark.parametrize("hilang", ["DB_DSN", "REDIS_URL", "JUDGE_CONCURRENCY"])
def test_worker_menolak_mulai_tanpa_variabel_wajib(hilang):
    env = {n: os.environ[n] for n in konfig.kunci_wajib()}
    env[hilang] = ""
    with pytest.raises(konfig.KonfigSalah) as info:
        konfig.muat(env)
    assert hilang in str(info.value)


def test_galat_konfigurasi_tidak_membocorkan_sandi():
    env = {n: os.environ[n] for n in konfig.kunci_wajib()}
    sandi = konfig.muat(env).db_sandi
    env["DB_DSN"] = env["DB_DSN"].replace("MySQLi://", "postgres://")
    with pytest.raises(konfig.KonfigSalah) as info:
        konfig.muat(env)
    assert sandi not in str(info.value)
