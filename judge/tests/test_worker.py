"""Worker sebagai proses sungguhan dengan MySQL, Redis dan Docker sungguhan — KONTRAK.md §6."""

from __future__ import annotations

import json
import os
import signal
import subprocess
import time
import uuid

import pytest

from lokandita_judge import citra, kotak

HALO = {"language": "c", "source": '#include <stdio.h>\nint main(){int a;scanf("%d",&a);printf("%d\\n",a*2);return 0;}', "stdin": "21\n"}
TIDUR_3 = {"language": "python", "source": "import time\ntime.sleep(3)\nprint('bangun')\n"}


def test_run_dari_baris_sampai_hasil(mulai_worker, kirim, tunggu):
    mulai_worker()
    b = tunggu(kirim(HALO))
    assert b["status"] == "DONE" and b["attempts"] == 1
    assert b["result"]["verdict"] == "OK" and b["result"]["stdout"] == "42\n"
    assert b["progress"] == {"state": "done", "current": 1, "total": 1}
    assert b["started_at"] is not None and b["finished_at"] >= b["started_at"]


def test_kemajuan_terlihat_selama_pekerjaan_berjalan(mulai_worker, kirim, db_app):
    mulai_worker()
    id_ = kirim(TIDUR_3)
    terlihat = []
    batas = time.time() + 30
    while time.time() < batas:
        with db_app.cursor() as cur:
            cur.execute("SELECT status, progress FROM judge_jobs WHERE id = %s", (id_,))
            b = cur.fetchone()
        keadaan = json.loads(b["progress"])["state"] if b["progress"] else None
        if keadaan and (not terlihat or terlihat[-1] != keadaan):
            terlihat.append(keadaan)
        if b["status"] == "DONE":
            break
        time.sleep(0.05)
    assert terlihat[-1] == "done"
    assert "running" in terlihat and set(terlihat) <= {"queued", "compiling", "running", "done"}


def test_while_1_menjadi_tle_dan_worker_tetap_sehat(mulai_worker, kirim, tunggu):
    w = mulai_worker()
    jahat = tunggu(kirim({"language": "c", "source": "int main(){while(1);}"}))
    assert jahat["status"] == "DONE" and jahat["result"]["verdict"] == "TLE"
    assert tunggu(kirim(HALO))["result"]["stdout"] == "42\n"
    assert w.proses.poll() is None


def test_galat_kompilasi_adalah_done_dengan_ce(mulai_worker, kirim, tunggu):
    mulai_worker()
    b = tunggu(kirim({"language": "cpp", "source": "int main(){ return x; }"}))
    assert b["status"] == "DONE" and b["result"]["verdict"] == "CE"
    assert "main.cpp:1" in b["result"]["compile_log"]


def test_antrean_normal_didahulukan_dari_low(mulai_worker, kirim, tunggu, db_app):
    mulai_worker(konkurensi=1)
    sibuk = kirim(TIDUR_3)
    time.sleep(1.0)  # worker sedang mengerjakan `sibuk`; sisanya menumpuk di Redis
    rendah = [kirim(HALO, priority="low") for _ in range(3)]
    biasa = [kirim(HALO, priority="normal") for _ in range(3)]
    for id_ in [sibuk, *rendah, *biasa]:
        assert tunggu(id_, 60)["status"] == "DONE"
    with db_app.cursor() as cur:
        cur.execute("SELECT id FROM judge_jobs WHERE id <> %s ORDER BY finished_at, started_at", (sibuk,))
        urutan = [b["id"] for b in cur.fetchall()]
    assert set(urutan[:3]) == set(biasa) and set(urutan[3:]) == set(rendah)


def test_id_yang_sama_tiga_kali_di_redis_dikerjakan_sekali(mulai_worker, kirim, tunggu, r):
    mulai_worker()
    id_ = kirim(HALO)
    r.lpush("lokandita:judge:normal", id_, id_)
    b = tunggu(id_)
    time.sleep(2)
    assert tunggu(id_)["attempts"] == 1 and b["status"] == "DONE"


def test_baris_yang_tidak_pernah_sampai_ke_redis_tetap_dikerjakan(mulai_worker, kirim, tunggu, r):
    # Web sempat menyisipkan baris tetapi mati sebelum LPUSH: pemindai menemukannya.
    mulai_worker()
    id_ = kirim(HALO, dorong=False, umur_s=20)
    rendah = kirim(HALO, priority="low", dorong=False, umur_s=20)
    assert r.llen("lokandita:judge:normal") == 0
    assert tunggu(id_, 20)["result"]["stdout"] == "42\n"
    assert tunggu(rendah, 20)["status"] == "DONE"


def test_id_di_redis_tanpa_baris_diabaikan(mulai_worker, kirim, tunggu, r):
    w = mulai_worker()
    r.lpush("lokandita:judge:normal", str(uuid.uuid4()), "bukan-uuid'; DROP TABLE judge_jobs; --")
    assert tunggu(kirim(HALO))["status"] == "DONE"
    assert w.proses.poll() is None


# SUBMIT dan RUN pada tes contoh: tests/test_worker_kiriman.py (tahap 9a).


@pytest.mark.parametrize("payload", [
    {"language": "java", "source": "x"},
    {"language": "c"},
    {"language": "c", "source": ""},
    {"language": "c", "source": "int main(){}", "stdin": 5},
    {"language": ["c"], "source": "int main(){}"},
    {"language": "c", "source": "int main(){}" + " " * 300_000},
    [1, 2, 3],
])
def test_payload_salah_dijawab_failed_invalid_payload_tanpa_diulang(mulai_worker, kirim, tunggu, payload):
    mulai_worker()
    b = tunggu(kirim(payload))
    assert b["status"] == "FAILED" and b["result"]["error"] == "invalid_payload" and b["attempts"] == 1
    assert b["result"]["message"]


# ---- galat infrastruktur ------------------------------------------------------------------

@pytest.fixture()
def docker_rusak(tmp_path):
    """`docker` pembungkus di depan PATH worker: `docker run` gagal selama berkas bendera ada.

    Worker tetap memakai Docker sungguhan untuk semua hal lain; tidak ada kait tes di kode worker.
    """
    asli = subprocess.run(["which", "docker"], capture_output=True, text=True, check=True).stdout.strip()
    bendera = tmp_path / "docker-rusak"
    hitung = tmp_path / "hitung"
    d = tmp_path / "bin"
    d.mkdir()
    (d / "docker").write_text(
        "#!/bin/sh\n"
        f'if [ "$1" = run ] && [ -e "{bendera}" ]; then\n'
        f'  echo x >> "{hitung}"\n'
        f'  if [ -e "{bendera}.sekali" ]; then rm -f "{bendera}" "{bendera}.sekali"; fi\n'
        "  echo 'Cannot connect to the Docker daemon' >&2\n  exit 125\nfi\n"
        f'exec "{asli}" "$@"\n'
    )
    (d / "docker").chmod(0o755)
    return {"env": {"PATH": f"{d}:{os.environ['PATH']}"}, "bendera": bendera, "hitung": hitung}


def test_galat_infrastruktur_diulang_dua_kali_lalu_failed(mulai_worker, kirim, tunggu, docker_rusak):
    w = mulai_worker(env=docker_rusak["env"])
    docker_rusak["bendera"].touch()
    b = tunggu(kirim(HALO), 60)
    assert b["status"] == "FAILED" and b["result"] == {"error": "judge_unavailable"}
    assert b["attempts"] == 3  # satu kali ditambah dua kali ulang
    assert len(docker_rusak["hitung"].read_text().split()) == 3
    galat = [p for p in w.log() if p["peristiwa"] == "galat_infrastruktur"]
    assert len(galat) == 3 and "Cannot connect" in galat[0]["pesan"]
    # Docker pulih: pekerjaan berikutnya berjalan biasa.
    docker_rusak["bendera"].unlink()
    assert tunggu(kirim(HALO))["result"]["stdout"] == "42\n"


def test_galat_infrastruktur_sekali_lalu_berhasil(mulai_worker, kirim, tunggu, docker_rusak):
    mulai_worker(env=docker_rusak["env"])
    docker_rusak["bendera"].touch()
    (docker_rusak["bendera"].parent / "docker-rusak.sekali").touch()
    b = tunggu(kirim(HALO), 60)
    assert b["status"] == "DONE" and b["result"]["stdout"] == "42\n" and b["attempts"] == 2


def test_worker_yang_dibunuh_di_tengah_pekerjaan_pulih_setelah_dinyalakan_lagi(mulai_worker, kirim, tunggu, db_app):
    w = mulai_worker()
    berjalan = [kirim({"language": "python", "source": "import time\ntime.sleep(4)\nprint('selesai')\n"}) for _ in range(2)]
    menunggu = kirim(HALO, dorong=False)  # QUEUED di basis data, tidak di Redis
    batas = time.time() + 20
    while time.time() < batas:
        with db_app.cursor() as cur:
            cur.execute("SELECT COUNT(*) AS n FROM judge_jobs WHERE status = 'RUNNING'")
            if cur.fetchone()["n"] == 2 and len(kotak.kontainer_judge()) >= 1:
                break
        time.sleep(0.1)
    w.hentikan(signal.SIGKILL)
    with db_app.cursor() as cur:
        cur.execute("SELECT COUNT(*) AS n FROM judge_jobs WHERE status = 'RUNNING'")
        assert cur.fetchone()["n"] == 2  # baris yatim: tidak ada yang mengerjakannya

    w2 = mulai_worker()
    for id_ in berjalan:
        b = tunggu(id_, 60)
        assert b["status"] == "DONE" and b["result"]["stdout"] == "selesai\n" and b["attempts"] == 2
    assert tunggu(menunggu, 30)["status"] == "DONE"
    mulai = next(p for p in w2.log() if p["peristiwa"] == "mulai")
    assert mulai["pekerjaan_dipulihkan"] == 2


def test_pekerjaan_yang_tiga_kali_mematikan_worker_menjadi_failed(mulai_worker, kirim, tunggu, db_app):
    id_ = kirim(HALO, dorong=False)
    with db_app.cursor() as cur:
        cur.execute("UPDATE judge_jobs SET status = 'RUNNING', attempts = 3, started_at = UTC_TIMESTAMP() WHERE id = %s", (id_,))
    mulai_worker()
    b = tunggu(id_, 20)
    assert b["status"] == "FAILED" and b["result"] == {"error": "judge_unavailable"} and b["attempts"] == 3


def test_berhenti_dengan_sigterm_menyelesaikan_pekerjaan_yang_berjalan(mulai_worker, kirim, tunggu, db_app, r):
    w = mulai_worker()
    id_ = kirim(TIDUR_3)
    batas = time.time() + 20
    while time.time() < batas:
        with db_app.cursor() as cur:
            cur.execute("SELECT status FROM judge_jobs WHERE id = %s", (id_,))
            if cur.fetchone()["status"] == "RUNNING":
                break
        time.sleep(0.1)
    assert w.hentikan(signal.SIGTERM) == 0
    b = tunggu(id_, 1)
    assert b["status"] == "DONE" and b["result"]["stdout"] == "bangun\n"
    assert r.get("lokandita:judge:worker") is None  # denyut dihapus saat berhenti dengan baik


# ---- gagal tertutup saat mulai --------------------------------------------------------------

def test_worker_menolak_mulai_tanpa_docker_dan_tidak_mengambil_pekerjaan(mulai_worker, kirim, tmp_path, db_app, r):
    kosong = tmp_path / "kosong"
    kosong.mkdir()
    id_ = kirim(HALO)
    w = mulai_worker(env={"PATH": f"{kosong}:/usr/bin/tidak-ada"}, siap=False)
    assert w.proses.wait(30) == 69
    sebab = [p for p in w.log() if p["peristiwa"] == "tidak_mulai"]
    assert len(sebab) == 1 and "docker" in sebab[0]["sebab"].lower()
    with db_app.cursor() as cur:
        cur.execute("SELECT status, attempts FROM judge_jobs WHERE id = %s", (id_,))
        assert cur.fetchone() == {"status": "QUEUED", "attempts": 0}
    assert r.get("lokandita:judge:worker") is None


def test_worker_menolak_mulai_bila_image_belum_dibangun(mulai_worker, tmp_path):
    # `docker image inspect` menjawab "tidak ada" untuk image judge; yang lain diteruskan ke Docker sungguhan.
    asli = subprocess.run(["which", "docker"], capture_output=True, text=True, check=True).stdout.strip()
    d = tmp_path / "bin"
    d.mkdir()
    (d / "docker").write_text(f'#!/bin/sh\nif [ "$1" = image ]; then echo "No such image" >&2; exit 1; fi\nexec "{asli}" "$@"\n')
    (d / "docker").chmod(0o755)
    w = mulai_worker(env={"PATH": f"{d}:{os.environ['PATH']}"}, siap=False)
    assert w.proses.wait(30) == 69
    assert "belum dibangun" in next(p for p in w.log() if p["peristiwa"] == "tidak_mulai")["sebab"]


@pytest.mark.parametrize("hilang", ["DB_DSN", "REDIS_URL", "JUDGE_CONCURRENCY"])
def test_worker_menolak_mulai_tanpa_variabel_wajib_dan_menyebut_namanya(mulai_worker, hilang):
    w = mulai_worker(env={hilang: ""}, siap=False) if hilang != "JUDGE_CONCURRENCY" else None
    if w is None:
        lingkungan = {k: v for k, v in os.environ.items() if k != "JUDGE_CONCURRENCY"}
        hasil = subprocess.run([os.sys.executable, "-m", "lokandita_judge.worker"], cwd=citra.DIR_IMAGES.parent,
                               env=lingkungan, capture_output=True, text=True, timeout=30)
        kode, keluaran = hasil.returncode, hasil.stdout
    else:
        kode = w.proses.wait(30)
        keluaran = w.berkas_log.read_text()
    assert kode == 78
    assert hilang in keluaran
    assert "sandi" not in keluaran.lower() and os.environ["DB_DSN"].split(":")[2].split("@")[0] not in keluaran


def test_log_worker_tidak_memuat_sumber_keluaran_atau_rahasia(mulai_worker, kirim, tunggu):
    w = mulai_worker()
    tunggu(kirim({"language": "python", "source": "print('RAHASIA-KELUARAN-123')  # RAHASIA-SUMBER-456", "stdin": "RAHASIA-MASUKAN-789"}))
    teks = w.berkas_log.read_text()
    for rahasia in ["RAHASIA-KELUARAN-123", "RAHASIA-SUMBER-456", "RAHASIA-MASUKAN-789",
                    os.environ["DB_DSN"].split(":")[2].split("@")[0], os.environ["REDIS_URL"].split(":")[2].split("@")[0]]:
        assert rahasia not in teks
    assert all(p["peristiwa"] != "bukan_json" for p in w.log())


# ---- denyut dan penyapu ---------------------------------------------------------------------

def test_denyut_memuat_commit_yang_berjalan(mulai_worker, r):
    w = mulai_worker(konkurensi=2)
    denyut = json.loads(r.get("lokandita:judge:worker"))
    kepala = subprocess.run(["git", "rev-parse", "HEAD"], capture_output=True, text=True, check=True).stdout.strip()
    assert denyut["commit"] == kepala and denyut["pid"] == w.proses.pid
    assert denyut["konkurensi"] == 2 and denyut["image"] == citra.tag() and denyut["skema"].endswith("_test")
    assert 0 < r.ttl("lokandita:judge:worker") <= 15
    time.sleep(6)
    assert json.loads(r.get("lokandita:judge:worker"))["waktu"] > denyut["waktu"]  # diperbarui


def test_penyapu_membuang_kontainer_judge_kedaluwarsa_dan_tidak_menyentuh_yang_lain():
    def buat(nama: str, *label: str) -> None:
        argumen = [x for l in label for x in ("--label", l)]
        subprocess.run(["docker", "create", "--name", nama, "--network", "none", *argumen, citra.nama("c"), "sleep", "60"],
                       capture_output=True, check=True)

    # Worker produksi di server ini juga menyapu tiap 30 detik, menurut label saja. Supaya ia tidak
    # mendahului tes ini, kontainer "kedaluwarsa" baru berakhir 5 menit lagi dan penyapu tes
    # dijalankan seolah-olah sekarang 10 menit lagi.
    kelak = time.time() + 600
    lewat, nanti = int(time.time()) + 300, int(time.time()) + 3600
    akhiran = uuid.uuid4().hex[:8]
    n = {k: f"judge-uji-{k}-{akhiran}" for k in ["kedaluwarsa", "masih-hidup", "tanpa-label", "label-lain"]}
    instans = f"lokandita.judge.instans={kotak.INSTANS}"
    try:
        buat(n["kedaluwarsa"], "lokandita.judge=true", instans, f"lokandita.judge.berakhir={lewat}")
        buat(n["masih-hidup"], "lokandita.judge=true", instans, f"lokandita.judge.berakhir={nanti}")
        buat(n["tanpa-label"])  # namanya mirip, tetapi bukan kontainer judge
        buat(n["label-lain"], "lokandita.judge=false", f"lokandita.judge.berakhir={lewat}")
        sebelum = subprocess.run(["docker", "ps", "-aq"], capture_output=True, text=True).stdout.split()

        dibuang = kotak.sapu(kelak)

        assert n["kedaluwarsa"] in dibuang
        assert not {n["masih-hidup"], n["tanpa-label"], n["label-lain"]} & set(dibuang)
        sesudah = subprocess.run(["docker", "ps", "-a", "--format", "{{.Names}}"], capture_output=True, text=True).stdout.split()
        assert n["kedaluwarsa"] not in sesudah
        assert {n["masih-hidup"], n["tanpa-label"], n["label-lain"]} <= set(sesudah)
        assert {"mysql", "redis", "caddy", "auth", "lokakode", "portal"} <= set(sesudah)
        assert len(sesudah) == len(sebelum) - len(dibuang)
    finally:
        subprocess.run(["docker", "rm", "-f", *n.values()], capture_output=True)


def test_worker_baru_membuang_kontainer_peninggalan_worker_lama(mulai_worker):
    nama = f"judge-yatim-{uuid.uuid4().hex[:8]}"
    subprocess.run(["docker", "run", "-d", "--name", nama, "--network", "none", "--label", "lokandita.judge=true",
                    "--label", f"lokandita.judge.instans={kotak.INSTANS}",
                    "--label", f"lokandita.judge.berakhir={int(time.time()) + 3600}", citra.nama("c"), "sleep", "600"],
                   capture_output=True, check=True)
    try:
        w = mulai_worker()
        assert nama not in kotak.kontainer_judge()
        assert next(p for p in w.log() if p["peristiwa"] == "mulai")["kontainer_lama_dibuang"] == 1
    finally:
        subprocess.run(["docker", "rm", "-f", nama], capture_output=True)


# ---- beban: 200 pekerjaan sekaligus ----------------------------------------------------------

def test_200_pekerjaan_sekaligus_semuanya_selesai_dan_tidak_ada_kontainer_tersisa(mulai_worker, kirim, db_app, r):
    w = mulai_worker(konkurensi=int(os.environ["JUDGE_CONCURRENCY"]))
    sumber = '#include <stdio.h>\nint main(){int a;scanf("%d",&a);printf("%d\\n",a*a);return 0;}'
    mulai = time.time()
    job = {kirim({"language": "c", "source": sumber, "stdin": f"{i}\n"}, priority="low" if i % 4 == 0 else "normal"): i
           for i in range(200)}
    assert r.llen("lokandita:judge:normal") + r.llen("lokandita:judge:low") > 150  # benar-benar menumpuk

    batas = time.time() + 540
    while time.time() < batas:
        with db_app.cursor() as cur:
            cur.execute("SELECT status, COUNT(*) AS n FROM judge_jobs GROUP BY status")
            hitung = {b["status"]: b["n"] for b in cur.fetchall()}
        if hitung.get("DONE", 0) + hitung.get("FAILED", 0) == 200:
            break
        assert w.proses.poll() is None, "worker mati di tengah beban"
        time.sleep(1)
    lama = time.time() - mulai
    assert hitung == {"DONE": 200}, hitung

    with db_app.cursor() as cur:
        cur.execute("SELECT id, result, attempts FROM judge_jobs")
        baris = cur.fetchall()
    for b in baris:
        hasil = json.loads(b["result"])
        i = job[b["id"]]
        assert (hasil["verdict"], hasil["stdout"], b["attempts"]) == ("OK", f"{i * i}\n", 1), (i, hasil)
    assert r.llen("lokandita:judge:normal") == 0 and r.llen("lokandita:judge:low") == 0
    assert kotak.kontainer_judge() == []
    print(f"\n200 pekerjaan RUN selesai dalam {lama:.0f} detik dengan JUDGE_CONCURRENCY={os.environ['JUDGE_CONCURRENCY']}")
