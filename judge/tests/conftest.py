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
    # Instans tes = nama skema tes: kontainer dan direktori kerja worker produksi tidak tersentuh.
    from lokandita_judge import kotak
    kotak.INSTANS = k.db_skema
    kotak.bersihkan_instans()


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
    assert not kotak.dir_job().exists() or list(kotak.dir_job().iterdir()) == [], "ada direktori job yang tertinggal"


# ---- worker sebagai proses sungguhan ----------------------------------------------------

import json  # noqa: E402
import signal  # noqa: E402
import sys  # noqa: E402
import time  # noqa: E402
from pathlib import Path  # noqa: E402

JUDGE = Path(__file__).resolve().parent.parent


class ProsesWorker:
    """Worker yang dijalankan persis seperti di produksi: `python -m lokandita_judge.worker`."""

    def __init__(self, r, env: dict, berkas_log: Path):
        self.r = r
        self.berkas_log = berkas_log
        self._log = open(berkas_log, "wb")
        self.proses = subprocess.Popen([sys.executable, "-m", "lokandita_judge.worker"], cwd=JUDGE, env=env,
                                       stdout=self._log, stderr=subprocess.STDOUT)

    def log(self) -> list[dict]:
        baris = []
        for b in self.berkas_log.read_text(errors="replace").splitlines():
            try:
                baris.append(json.loads(b))
            except ValueError:
                baris.append({"peristiwa": "bukan_json", "teks": b})
        return baris

    def tunggu_siap(self, waktu: float = 30) -> "ProsesWorker":
        batas = time.time() + waktu
        while time.time() < batas:
            if self.proses.poll() is not None:
                raise AssertionError(f"worker berhenti saat mulai (kode {self.proses.returncode}): {self.log()}")
            denyut = self.r.get("lokandita:judge:worker")
            if denyut and json.loads(denyut)["pid"] == self.proses.pid:
                return self
            time.sleep(0.1)
        raise AssertionError(f"worker tidak siap dalam {waktu} detik: {self.log()}")

    def hentikan(self, sinyal=signal.SIGTERM, waktu: float = 60) -> int:
        if self.proses.poll() is None:
            self.proses.send_signal(sinyal)
        try:
            return self.proses.wait(waktu)
        finally:
            self._log.close()


@pytest.fixture()
def mulai_worker(r, tmp_path, db_app):
    """Pabrik worker. Antrean Redis dan tabel judge_jobs dikosongkan lebih dulu."""
    r.delete("lokandita:judge:normal", "lokandita:judge:low", "lokandita:judge:worker")
    with db_app.cursor() as cur:
        cur.execute("DELETE FROM judge_jobs")
    daftar: list[ProsesWorker] = []

    def _mulai(konkurensi: int = 4, env: dict | None = None, siap: bool = True) -> ProsesWorker:
        lingkungan = dict(os.environ, JUDGE_CONCURRENCY=str(konkurensi), **(env or {}))
        w = ProsesWorker(r, lingkungan, tmp_path / f"worker-{len(daftar)}.log")
        daftar.append(w)
        return w.tunggu_siap() if siap else w

    yield _mulai
    for w in daftar:
        w.hentikan()
    r.delete("lokandita:judge:normal", "lokandita:judge:low", "lokandita:judge:worker")


@pytest.fixture()
def kirim(db_app, r):
    """Berperan sebagai web: sisipkan baris, commit, lalu LPUSH id-nya (KONTRAK.md §6)."""

    def _kirim(payload: dict, kind: str = "RUN", priority: str = "normal", dorong: bool = True, umur_s: int = 0,
               pembuat: str | None = None) -> str:
        id_ = str(uuid.uuid4())
        with db_app.cursor() as cur:
            cur.execute(
                "INSERT INTO judge_jobs (id, kind, priority, status, payload, created_by, created_at)"
                " VALUES (%s, %s, %s, 'QUEUED', %s, %s, UTC_TIMESTAMP() - INTERVAL %s SECOND)",
                (id_, kind, priority, json.dumps(payload), pembuat, umur_s),
            )
        if dorong:
            r.lpush(f"lokandita:judge:{priority}", id_)
        return id_

    return _kirim


@pytest.fixture()
def tunggu(db_app):
    """Menunggu sampai pekerjaan DONE atau FAILED, seperti polling web; mengembalikan barisnya."""

    def _tunggu(id_: str, waktu: float = 40) -> dict:
        batas = time.time() + waktu
        baris = None
        while time.time() < batas:
            with db_app.cursor() as cur:
                cur.execute("SELECT * FROM judge_jobs WHERE id = %s", (id_,))
                baris = cur.fetchone()
            if baris["status"] in ("DONE", "FAILED"):
                baris["result"] = json.loads(baris["result"]) if baris["result"] else None
                baris["progress"] = json.loads(baris["progress"]) if baris["progress"] else None
                return baris
            time.sleep(0.1)
        raise AssertionError(f"pekerjaan {id_} tidak selesai dalam {waktu} detik: {baris}")

    return _tunggu
