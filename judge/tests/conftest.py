"""Alat tes judge. Dijalankan hanya lewat infra/tes.sh judge.

Semua tes memakai MySQL, Redis dan Docker yang sungguhan. Tes menolak berjalan
di luar skema *_test dan Redis DB 15, gagal bila Docker tidak tersedia, dan
gagal bila ada tes yang dilewati (CLAUDE.md: tes isolasi tidak pernah dilewati).
"""

from __future__ import annotations

import os
import subprocess
import uuid

import pymysql
import pytest

from lokandita_judge import konfig, sambungan


def pytest_sessionstart(session):
    k = konfig.muat()
    if not k.db_skema.endswith("_test"):
        pytest.exit(f"menolak berjalan: skema '{k.db_skema}' bukan skema *_test", returncode=3)
    if k.redis_db != 15:
        pytest.exit(f"menolak berjalan: Redis DB {k.redis_db}, tes hanya boleh memakai DB 15", returncode=3)
    if not os.environ.get("TES_APP_DSN", "").endswith("_test"):
        pytest.exit("menolak berjalan: TES_APP_DSN tidak menunjuk skema *_test", returncode=3)
    docker = subprocess.run(["docker", "info", "--format", "{{.ServerVersion}}"], capture_output=True, text=True)
    if docker.returncode != 0:
        pytest.exit("Docker tidak tersedia: tes judge GAGAL, tidak dilewati", returncode=3)


def pytest_sessionfinish(session, exitstatus):
    pelapor = session.config.pluginmanager.get_plugin("terminalreporter")
    if pelapor and pelapor.stats.get("skipped"):
        session.exitstatus = 1
        pelapor.write_line("GAGAL: ada tes yang dilewati; tes judge tidak boleh dilewati", red=True)


@pytest.fixture(scope="session")
def k() -> konfig.Konfig:
    return konfig.muat()


@pytest.fixture()
def db_worker(k):
    """MySQL sebagai judge_worker — persis seperti worker."""
    c = sambungan.mysql(k)
    yield c
    c.close()


@pytest.fixture()
def db_app():
    """MySQL sebagai lokakode_app pada lokakode_test — peran web: yang membuat baris."""
    env = dict(os.environ, DB_DSN=os.environ["TES_APP_DSN"])
    c = sambungan.mysql(konfig.muat(env))
    yield c
    c.close()


@pytest.fixture()
def r(k):
    c = sambungan.antrean(k)
    yield c
    c.close()


@pytest.fixture()
def buat_job(db_app):
    """Menyisipkan baris judge_jobs seperti web (tanpa LPUSH) dan mengembalikan id-nya."""

    def _buat(kind="RUN", payload="{}", priority="normal", status="QUEUED") -> str:
        id_ = str(uuid.uuid4())
        with db_app.cursor() as cur:
            cur.execute(
                "INSERT INTO judge_jobs (id, kind, priority, status, payload, created_at)"
                " VALUES (%s, %s, %s, %s, %s, UTC_TIMESTAMP())",
                (id_, kind, priority, status, payload),
            )
        return id_

    return _buat


def galat_mysql(sambung, sql: str) -> int:
    """Menjalankan SQL yang harus ditolak dan mengembalikan nomor galat MySQL."""
    with sambung.cursor() as cur:
        with pytest.raises(pymysql.err.MySQLError) as info:
            cur.execute(sql)
    return info.value.args[0]


@pytest.fixture()
def jalan():
    """Mengompilasi dan menjalankan satu program di kotak pasir; mengembalikan kotak.Hasil."""
    from lokandita_judge import kotak

    def _jalan(bahasa: str, sumber: str, stdin: bytes = b"", **batas):
        d = kotak.siapkan_dir_job()
        try:
            k = kotak.kompilasi(bahasa, sumber, d)
            assert k.ok, f"kompilasi gagal ({k.alasan}): {k.log}"
            return kotak.jalankan(bahasa, d, stdin, **batas)
        finally:
            kotak.buang_dir_job(d)

    return _jalan


@pytest.fixture(autouse=True)
def tidak_ada_sisa():
    """Setelah setiap tes: tidak ada kontainer judge dan tidak ada direktori job yang tertinggal."""
    from lokandita_judge import kotak

    yield
    assert kotak.kontainer_judge() == [], "ada kontainer judge yang tertinggal"
    assert not kotak.DIR_JOB.exists() or list(kotak.DIR_JOB.iterdir()) == [], "ada direktori job yang tertinggal"
