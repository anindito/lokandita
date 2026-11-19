"""Kotak pasir: menjalankan satu program di dalam satu kontainer baru (SPEC.md §10).

Aturan yang tidak boleh dilonggarkan demi kemudahan (CLAUDE.md):
tanpa jaringan, root hanya-baca, tanpa capability, bukan root, profil seccomp.
Semua bendera Docker ada di satu tempat: `bendera_kontainer`.

Kode mahasiswa tidak pernah dijalankan di host. Bila Docker tidak bisa dipakai,
fungsi di sini melempar GalatInfrastruktur dan tidak menjalankan apa pun.
"""

from __future__ import annotations

import json
import os
import re
import secrets
import shutil
import subprocess
import threading
import time
from dataclasses import dataclass
from pathlib import Path

from . import citra

DOCKER = "docker"
AKAR_JUDGE = Path(__file__).resolve().parent.parent
PROFIL_SECCOMP = AKAR_JUDGE / "seccomp" / "judge.json"

LABEL = "lokandita.judge=true"
LABEL_BERAKHIR = "lokandita.judge.berakhir"
LABEL_INSTANS = "lokandita.judge.instans"

# Satu worker per skema basis data. Nama skema menjadi nama instans, sehingga
# worker produksi dan worker tes di server yang sama tidak saling menyentuh
# kontainer dan direktori kerja. Diatur sekali saat mulai (worker, conftest).
INSTANS = "lepas"


def dir_job() -> Path:
    return AKAR_JUDGE / "var" / "job" / INSTANS


MEMORI_MB = 256
# SPEC.md §10: satu CPU. Tes menurunkannya untuk meniru server yang sibuk; tidak pernah dinaikkan.
CPU_KONTAINER = "1"
BATAS_KELUARAN = 1024 * 1024  # stdout dan stderr, masing-masing
BATAS_BINER = 8 * 1024 * 1024
BATAS_KOMPILASI_S = 10
# Kompilasi yang kena batas waktu dengan CPU di bawah bagian ini dari 10 s dianggap
# kelaparan CPU (HasilKompilasi.kelaparan). Angkanya diukur; lihat DECISIONS.md.
AMBANG_KELAPARAN = 0.8
BATAS_CPU_MS = {"c": 2000, "cpp": 2000, "python": 5000}
# Kelonggaran untuk menyalakan kontainer; tidak pernah dihitung sebagai waktu program.
KELONGGARAN_MULAI_S = 4
BERKAS_SUMBER = {"c": "main.c", "cpp": "main.cpp", "python": "main.py"}

SIGKILL, SIGXCPU, SIGXFSZ = 9, 24, 25


class GalatInfrastruktur(Exception):
    """Docker atau host bermasalah. Bukan kesalahan program mahasiswa; pekerjaan boleh diulang."""


@dataclass
class Hasil:
    """Hasil satu kali jalan. status: OK, RE, TLE, MLE, OLE."""

    status: str
    stdout: bytes = b""
    stderr: bytes = b""
    kode_keluar: int = -1
    sinyal: int = 0
    cpu_ms: int = 0
    wall_ms: int = 0
    memori_kb: int = -1


@dataclass
class HasilKompilasi:
    ok: bool
    log: str = ""
    # '' bila berhasil atau kompiler sendiri menolak; selain itu:
    # waktu_habis, memori_habis, keluaran_terlalu_besar
    alasan: str = ""
    # CPU yang dipakai seluruh kontainer kompilasi (cgroup), -1 bila tidak diketahui
    # (kontainer dimatikan dari luar sebelum pelari sempat melapor).
    cpu_ms: int = -1

    @property
    def kelaparan(self) -> bool:
        """Batas waktu kena padahal kompiler tidak mendapat CPU: server yang sibuk,
        bukan sumber yang berat. Pemanggil memperlakukannya sebagai galat infrastruktur."""
        return self.alasan == "waktu_habis" and self.cpu_ms < BATAS_KOMPILASI_S * 1000 * AMBANG_KELAPARAN


def batas_wall_s(cpu_ms: int) -> float:
    """Keputusan pemilik (tahap 7): batas waktu dinding = 2 × batas CPU + 1 detik.

    Dipaksakan dua kali: oleh pelari di dalam kontainer (tepat, dihitung sejak
    program mulai), dan oleh worker dari luar dengan kelonggaran untuk
    menyalakan kontainer — yang dari luar tidak bergantung pada kode di dalam.
    """
    return 2 * cpu_ms / 1000 + 1


def bendera_kontainer(nama: str, dir_job: Path, tanda: str, memori_mb: int, umur_s: float) -> list[str]:
    return [
        "--name", nama,
        "--network", "none",
        "--read-only",
        "--tmpfs", "/box:rw,size=64m,mode=1777,nosuid,nodev,noexec",
        "--memory", f"{memori_mb}m", "--memory-swap", f"{memori_mb}m",
        "--cpus", CPU_KONTAINER,
        "--pids-limit", "64",
        "--cap-drop", "ALL",
        "--security-opt", "no-new-privileges",
        "--security-opt", f"seccomp={PROFIL_SECCOMP}",
        "--user", "65534:65534",
        "--ulimit", "fsize=8388608", "--ulimit", "nofile=64", "--ulimit", "core=0",
        "--ipc", "none",
        "--log-driver", "none",
        "--label", LABEL,
        "--label", f"{LABEL_INSTANS}={INSTANS}",
        "--label", f"{LABEL_BERAKHIR}={int(time.time() + umur_s)}",
        "--env", f"PELARI_TANDA={tanda}",
        "--volume", f"{dir_job}:/src:ro",
    ]


def _docker(*argumen: str, waktu: float = 30) -> subprocess.CompletedProcess:
    try:
        return subprocess.run([DOCKER, *argumen], capture_output=True, text=True, timeout=waktu)
    except (OSError, subprocess.TimeoutExpired) as e:
        raise GalatInfrastruktur(f"docker {argumen[0]} tidak bisa dijalankan: {e}") from e


def _buang(nama: str) -> None:
    """Membuang kontainer dan baru kembali setelah ia benar-benar tidak ada.

    Dua `docker rm -f` untuk kontainer yang sama bisa bertumpuk (pengawas waktu
    dan pembersihan akhir); yang kedua langsung dijawab "removal ... is already
    in progress". Tanpa menunggu, pemanggil kembali selagi kontainernya masih ada."""
    for _ in range(50):  # paling lama sekitar 10 detik
        try:
            hasil = subprocess.run([DOCKER, "rm", "-f", nama], capture_output=True, text=True, timeout=60)
        except (OSError, subprocess.TimeoutExpired):
            return  # reaper akan membuangnya lewat label
        if hasil.returncode == 0 or "No such container" in hasil.stderr:
            return
        time.sleep(0.2)


class _Penampung:
    """Membaca satu pipa sampai habis, menyimpan paling banyak `batas` byte."""

    def __init__(self, pipa, batas: int, saat_lewat):
        self.data = bytearray()
        self.lewat = False
        self._pipa, self._batas, self._saat_lewat = pipa, batas, saat_lewat
        self.utas = threading.Thread(target=self._baca, daemon=True)
        self.utas.start()

    def _baca(self) -> None:
        while True:
            potong = self._pipa.read1(65536)
            if not potong:
                return
            sisa = self._batas - len(self.data)
            if len(potong) > sisa:
                self.data += potong[: max(sisa, 0)]
                if not self.lewat:
                    self.lewat = True
                    self._saat_lewat()
                # tetap dibaca dan dibuang, supaya docker tidak macet menunggu pembaca
            else:
                self.data += potong


def _jalankan_kontainer(
    image: str, perintah: list[str], dir_job: Path, stdin: bytes, wall_s: float,
    batas_stdout: int, batas_stderr: int, memori_mb: int,
) -> dict:
    """Satu kontainer: dibuat, dijalankan, dibuang. Mengembalikan data mentah."""
    nama = "judge-" + secrets.token_hex(8)
    tanda = secrets.token_hex(16)
    argumen = [DOCKER, "run", "-i", *bendera_kontainer(nama, dir_job, tanda, memori_mb, wall_s + KELONGGARAN_MULAI_S + 15), image, *perintah]
    try:
        proses = subprocess.Popen(argumen, stdin=subprocess.PIPE, stdout=subprocess.PIPE, stderr=subprocess.PIPE)
    except OSError as e:
        raise GalatInfrastruktur(f"docker tidak bisa dijalankan: {e}") from e

    dimatikan = threading.Event()

    def matikan() -> None:
        # `docker rm -f` diulang sampai klien docker selesai: bila kontainer
        # belum sempat dibuat saat perintah pertama, perintah berikutnya mengenainya.
        if dimatikan.is_set():
            return
        dimatikan.set()

        def ulang() -> None:
            while proses.poll() is None:
                _buang(nama)
                time.sleep(0.2)

        threading.Thread(target=ulang, daemon=True).start()

    waktu_habis = threading.Event()

    def habis() -> None:
        waktu_habis.set()
        matikan()

    try:
        keluar = _Penampung(proses.stdout, batas_stdout, matikan)
        # +256: ruang untuk baris laporan pelari di ujung stderr
        galat = _Penampung(proses.stderr, batas_stderr + 256, matikan)

        def tulis() -> None:
            try:
                proses.stdin.write(stdin)
                proses.stdin.close()
            except OSError:
                pass  # program selesai tanpa membaca seluruh masukan

        threading.Thread(target=tulis, daemon=True).start()
        pengawas = threading.Timer(wall_s + KELONGGARAN_MULAI_S, habis)
        pengawas.daemon = True
        pengawas.start()
        try:
            kode_docker = proses.wait()
        finally:
            pengawas.cancel()
        keluar.utas.join(10)
        galat.utas.join(10)

        stderr = bytes(galat.data)
        laporan = None
        posisi = stderr.rfind(b"\n" + tanda.encode() + b" ")
        if posisi >= 0:
            baris = stderr[posisi + 1:].split(b"\n", 1)[0].decode("ascii", "replace")
            laporan = {k: int(v) for k, v in re.findall(r"(\w+)=(-?\d+)", baris)}
            stderr = stderr[:posisi]

        oom_docker = False
        if laporan is None and not dimatikan.is_set():
            periksa = _docker("inspect", "--format", "{{.State.OOMKilled}}", nama)
            oom_docker = periksa.returncode == 0 and periksa.stdout.strip() == "true"

        return {
            "kode_docker": kode_docker,
            "stdout": bytes(keluar.data),
            "stderr": stderr,
            "lewat_stdout": keluar.lewat,
            "lewat_stderr": galat.lewat or len(stderr) > batas_stderr,
            "waktu_habis": waktu_habis.is_set(),
            "laporan": laporan,
            "oom_docker": oom_docker,
        }
    finally:
        if proses.poll() is None:
            matikan()
            proses.wait()
        _buang(nama)


def siapkan_dir_job() -> Path:
    dir_job().mkdir(parents=True, exist_ok=True)
    d = dir_job() / secrets.token_hex(8)
    d.mkdir(mode=0o755)
    os.chmod(d, 0o755)
    return d


def buang_dir_job(d: Path) -> None:
    shutil.rmtree(d, ignore_errors=True)


def _galat_bila_docker_gagal(mentah: dict) -> None:
    # 125: docker sendiri gagal membuat atau menjalankan kontainer.
    if mentah["laporan"] is None and not mentah["waktu_habis"] and not mentah["lewat_stdout"] \
            and not mentah["lewat_stderr"] and not mentah["oom_docker"]:
        pesan = mentah["stderr"][-400:].decode("utf-8", "replace").strip()
        raise GalatInfrastruktur(f"kontainer berhenti tanpa laporan (docker keluar {mentah['kode_docker']}): {pesan}")


def kompilasi(bahasa: str, sumber: str, dir_job: Path, bendera: tuple[str, ...] = ()) -> HasilKompilasi:
    """Menulis sumber ke dir_job dan mengompilasinya (C, C++) atau memeriksa sintaksnya (Python).

    `bendera` adalah bendera kompiler tambahan pertemuan; pemanggil sudah
    memeriksanya terhadap daftar yang diizinkan (mesin.periksa_bendera)."""
    berkas = dir_job / BERKAS_SUMBER[bahasa]
    berkas.write_text(sumber, encoding="utf-8")
    os.chmod(berkas, 0o644)

    perintah = ["pelari", str(BATAS_KOMPILASI_S * 1000), str(BATAS_KOMPILASI_S * 1000)]
    perintah += ["periksa-python"] if bahasa == "python" else ["kompilasi", bahasa, *bendera]
    mentah = _jalankan_kontainer(
        citra.nama(bahasa), perintah, dir_job, b"", BATAS_KOMPILASI_S,
        batas_stdout=BATAS_BINER, batas_stderr=BATAS_KELUARAN, memori_mb=MEMORI_MB,
    )
    log = mentah["stderr"].decode("utf-8", "replace")
    lap = mentah["laporan"]
    cpu = lap.get("cg_cpu_ms", -1) if lap else -1

    if mentah["oom_docker"] or (lap and lap["oom"] > 0):
        return HasilKompilasi(False, log, "memori_habis", cpu)
    if mentah["waktu_habis"] or (lap and (lap["wall"] or lap["cpu_ms"] >= BATAS_KOMPILASI_S * 1000)):
        return HasilKompilasi(False, log, "waktu_habis", cpu)
    if mentah["lewat_stdout"] or mentah["lewat_stderr"]:
        return HasilKompilasi(False, log, "keluaran_terlalu_besar")
    _galat_bila_docker_gagal(mentah)
    if lap["exit"] != 0:
        return HasilKompilasi(False, log)
    if bahasa != "python":
        if not mentah["stdout"]:
            raise GalatInfrastruktur("kompilasi berhasil tetapi tidak ada program yang keluar")
        program = dir_job / "prog"
        program.write_bytes(mentah["stdout"])
        os.chmod(program, 0o755)
    return HasilKompilasi(True, log)


def jalankan(bahasa: str, dir_job: Path, stdin: bytes, cpu_ms: int | None = None, memori_mb: int = MEMORI_MB) -> Hasil:
    """Menjalankan program hasil `kompilasi` sekali, di kontainer baru dengan /box baru."""
    cpu_ms = cpu_ms or BATAS_CPU_MS[bahasa]
    wall_s = batas_wall_s(cpu_ms)
    perintah = ["pelari", str(cpu_ms), str(int(wall_s * 1000))]
    perintah += ["jalankan-python"] if bahasa == "python" else ["/src/prog"]
    mentah = _jalankan_kontainer(
        citra.nama(bahasa), perintah, dir_job, stdin, wall_s,
        batas_stdout=BATAS_KELUARAN, batas_stderr=BATAS_KELUARAN, memori_mb=memori_mb,
    )
    lap = mentah["laporan"]
    hasil = Hasil("OK", mentah["stdout"], mentah["stderr"])
    if lap:
        hasil.kode_keluar, hasil.sinyal = lap["exit"], lap["signal"]
        hasil.cpu_ms, hasil.wall_ms, hasil.memori_kb = lap["cpu_ms"], lap["wall_ms"], lap["mem_kb"]

    if mentah["lewat_stdout"] or mentah["lewat_stderr"]:
        hasil.status = "OLE"
    elif mentah["oom_docker"] or (lap and lap["oom"] > 0):
        hasil.status = "MLE"
    elif mentah["waktu_habis"]:
        hasil.status = "TLE"
        hasil.wall_ms = hasil.wall_ms or int(wall_s * 1000)
    else:
        _galat_bila_docker_gagal(mentah)
        if lap["wall"] or lap["cpu_ms"] > cpu_ms or lap["signal"] == SIGXCPU:
            hasil.status = "TLE"
        elif lap["exit"] != 0 or lap["signal"] != 0:
            hasil.status = "RE"
    return hasil


# ---- kelompok: banyak kali jalan di satu kontainer (generator dan solusi referensi) ----

BATAS_MASUKAN_TES = 256 * 1024      # satu masukan tes hasil generator
BATAS_CPU_GENERATOR_MS = 2000       # satu kali jalan generator
BATAS_KELOMPOK = 64 * 1024 * 1024   # seluruh keluaran satu kontainer kelompok
BERKAS_GENERATOR = "gen.py"


@dataclass
class HasilKelompok:
    """Satu kali jalan di dalam kontainer kelompok. status: OK, RE, TLE, MLE, OLE."""

    nomor: int
    status: str
    stdout: bytes
    stderr: bytes
    cpu_ms: int
    kode_keluar: int
    sinyal: int


class KelompokTerlaluBesar(Exception):
    """Seluruh keluaran satu kontainer kelompok melewati BATAS_KELOMPOK."""


def _kelompok(image: str, mode: str, n: int, cpu_ms: int, batas: int, henti: bool, seed: list[int],
              program: list[str], dir_job: Path, memori_mb: int) -> list[HasilKelompok]:
    kali = n * (len(seed) if mode == "gen" else 1)
    wall_ms = int(batas_wall_s(cpu_ms) * 1000)
    wall_total_s = kali * wall_ms / 1000 + 5
    perintah = ["pelari", str(kali * cpu_ms + 5000), str(int(wall_total_s * 1000)),
                "kelompok", mode, str(n), str(cpu_ms), str(wall_ms), str(batas), "1" if henti else "0",
                ",".join(str(s) for s in seed) if mode == "gen" else "-", "--", *program]
    mentah = _jalankan_kontainer(image, perintah, dir_job, b"", wall_total_s,
                                 batas_stdout=BATAS_KELOMPOK, batas_stderr=BATAS_KELUARAN, memori_mb=memori_mb)
    if mentah["lewat_stdout"]:
        raise KelompokTerlaluBesar()
    lap = mentah["laporan"]
    oom = mentah["oom_docker"] or bool(lap and lap["oom"] > 0)

    hasil: list[HasilKelompok] = []
    data, posisi = mentah["stdout"], 0
    while posisi < len(data):
        akhir = data.find(b"\n", posisi)
        kepala = data[posisi:akhir].split() if akhir >= 0 else []
        if len(kepala) != 9 or kepala[0] != b"K":
            raise GalatInfrastruktur("bingkai kelompok rusak")
        nomor, kode, sinyal, cpu, _wall, bendera, nk, ng = (int(x) for x in kepala[1:])
        keluar = data[akhir + 1:akhir + 1 + nk]
        galat = data[akhir + 1 + nk:akhir + 1 + nk + ng]
        posisi = akhir + 1 + nk + ng
        if posisi > len(data):
            raise GalatInfrastruktur("bingkai kelompok terpotong")
        if bendera & 2:
            status = "OLE"
        elif bendera & 1 or cpu > cpu_ms or sinyal == SIGXCPU:
            status = "TLE"
        elif sinyal == SIGKILL and oom:
            status = "MLE"
        elif kode != 0 or sinyal != 0:
            status = "RE"
        else:
            status = "OK"
        hasil.append(HasilKelompok(nomor, status, keluar, galat, cpu, kode, sinyal))

    gagal = any(h.status != "OK" for h in hasil)
    if len(hasil) < kali and not (henti and gagal):
        # Kontainer berhenti sebelum semua kali jalan selesai: batas memori atau waktu total.
        if oom:
            hasil.append(HasilKelompok(len(hasil), "MLE", b"", b"", 0, -1, SIGKILL))
        elif mentah["waktu_habis"] or (lap and lap["wall"]):
            hasil.append(HasilKelompok(len(hasil), "TLE", b"", b"", 0, -1, SIGKILL))
        else:
            _galat_bila_docker_gagal(mentah)
            raise GalatInfrastruktur(f"kelompok berhenti setelah {len(hasil)} dari {kali} kali jalan")
    return hasil


def jalankan_generator(sumber: str, seed: list[int], n: int, dir_job: Path, henti: bool = True) -> list[HasilKelompok]:
    """Generator (Python) untuk tiap seed dan INDEX 0..n-1, semuanya di satu kontainer
    judge-python. Urutan hasil: seed pertama INDEX 0..n-1, lalu seed kedua, dan seterusnya."""
    berkas = dir_job / BERKAS_GENERATOR
    berkas.write_text(sumber, encoding="utf-8")
    os.chmod(berkas, 0o644)
    return _kelompok(citra.nama("python"), "gen", n, BATAS_CPU_GENERATOR_MS, BATAS_MASUKAN_TES, henti, seed,
                     ["python3", f"/src/{BERKAS_GENERATOR}"], dir_job, MEMORI_MB)


def jalankan_kelompok(bahasa: str, dir_job: Path, masukan: list[bytes], cpu_ms: int | None = None,
                      memori_mb: int = MEMORI_MB, henti: bool = True) -> list[HasilKelompok]:
    """Program hasil `kompilasi` dijalankan sekali untuk tiap masukan, semuanya di satu
    kontainer. Hanya untuk solusi referensi (kode dosen); kode mahasiswa memakai `jalankan`."""
    dir_masukan = dir_job / "in"
    shutil.rmtree(dir_masukan, ignore_errors=True)
    dir_masukan.mkdir(mode=0o755)
    os.chmod(dir_masukan, 0o755)
    for i, isi in enumerate(masukan):
        (dir_masukan / str(i)).write_bytes(isi)
        os.chmod(dir_masukan / str(i), 0o644)
    program = ["jalankan-python"] if bahasa == "python" else ["/src/prog"]
    return _kelompok(citra.nama(bahasa), "uji", len(masukan), cpu_ms or BATAS_CPU_MS[bahasa], BATAS_KELUARAN, henti, [],
                     program, dir_job, memori_mb)


class AnalisisGagal(Exception):
    """Kode sumber tidak bisa dianalisis (tidak terurai, terlalu besar, waktu habis).
    Bukan galat infrastruktur: mengulang tidak akan mengubah hasilnya."""


BATAS_ANALISIS_MS = 10_000


def analisis_banyak(bahasa: str, berkas: dict[str, str]) -> dict[str, list[dict] | str]:
    """Konsep yang dipakai tiap berkas sumber (SPEC.md §9), dianalisis di SATU kontainer
    judge-analisis dengan batas yang sama seperti kontainer judge lain. Kode itu hanya
    diurai, tidak dijalankan. Nilai tiap berkas: daftar {code, line, column, construct},
    atau teks galat bila berkas itu tidak bisa diurai."""
    if bahasa not in BERKAS_SUMBER:
        raise AnalisisGagal("bahasa tidak dikenal")
    for nama in berkas:
        if not re.fullmatch(r"[A-Za-z0-9][A-Za-z0-9._-]{0,80}", nama):
            raise AnalisisGagal(f"nama berkas tidak sah: {nama!r}")
    d = siapkan_dir_job()
    try:
        for nama, sumber in berkas.items():
            (d / nama).write_text(sumber, encoding="utf-8")
            os.chmod(d / nama, 0o644)
        wall_s = batas_wall_s(BATAS_ANALISIS_MS)
        mentah = _jalankan_kontainer(
            citra.nama(citra.ANALISIS),
            ["pelari", str(BATAS_ANALISIS_MS), str(int(wall_s * 1000)), "python3", "-m", "analisis", bahasa,
             *[f"/src/{nama}" for nama in berkas]],
            d, b"", wall_s, batas_stdout=4 * BATAS_KELUARAN, batas_stderr=BATAS_KELUARAN, memori_mb=MEMORI_MB,
        )
    finally:
        buang_dir_job(d)
    lap = mentah["laporan"]
    if mentah["oom_docker"] or (lap and lap["oom"] > 0):
        raise AnalisisGagal("analisis kehabisan memori")
    if mentah["waktu_habis"] or (lap and (lap["wall"] or lap["signal"] == SIGXCPU or lap["cpu_ms"] >= BATAS_ANALISIS_MS)):
        raise AnalisisGagal("analisis melewati batas waktu")
    if mentah["lewat_stdout"] or mentah["lewat_stderr"]:
        raise AnalisisGagal("hasil analisis terlalu besar")
    _galat_bila_docker_gagal(mentah)
    if lap["exit"] != 0 or lap["signal"] != 0:
        # Penganalisis sendiri yang gagal: kesalahan kita, bukan kesalahan kode yang dianalisis.
        pesan = mentah["stderr"][-400:].decode("utf-8", "replace").strip()
        raise GalatInfrastruktur(f"penganalisis berhenti (exit {lap['exit']}, sinyal {lap['signal']}): {pesan}")
    hasil: dict[str, list[dict] | str] = {}
    for baris in mentah["stdout"].decode("utf-8", "replace").splitlines():
        try:
            data = json.loads(baris)
        except ValueError as e:
            raise GalatInfrastruktur("keluaran penganalisis bukan JSON") from e
        nama = str(data.get("file", "")).rsplit("/", 1)[-1]
        hasil[nama] = data["error"] if data.get("error") else data.get("concepts", [])
    if set(hasil) != set(berkas):
        raise GalatInfrastruktur("penganalisis tidak melaporkan semua berkas")
    return hasil


def analisis(bahasa: str, sumber: str) -> list[dict]:
    """Konsep yang dipakai satu sumber. Melempar AnalisisGagal bila tidak bisa diurai."""
    nama = BERKAS_SUMBER.get(bahasa, "main.txt")
    hasil = analisis_banyak(bahasa, {nama: sumber})[nama]
    if isinstance(hasil, str):
        raise AnalisisGagal(hasil)
    return hasil


def kontainer_judge() -> list[str]:
    """Nama semua kontainer judge instans ini yang masih ada (berjalan atau tidak)."""
    daftar = _docker("ps", "-a", "--filter", f"label={LABEL}", "--filter", f"label={LABEL_INSTANS}={INSTANS}",
                     "--format", "{{.Names}}")
    if daftar.returncode != 0:
        raise GalatInfrastruktur("docker ps gagal: " + daftar.stderr.strip())
    return daftar.stdout.split()


def bersihkan_instans() -> int:
    """Saat worker mulai: kontainer dan direktori kerja instans ini pasti peninggalan
    worker sebelumnya (hanya ada satu worker per instans), jadi semuanya dibuang."""
    nama = kontainer_judge()
    for n in nama:
        _buang(n)
    shutil.rmtree(dir_job(), ignore_errors=True)
    return len(nama)


def sapu(sekarang: float | None = None) -> list[str]:
    """Reaper: membuang kontainer berlabel judge yang masa hidupnya sudah lewat.

    Hanya kontainer dengan label `lokandita.judge=true` DAN label masa berakhir
    yang sudah lewat yang disentuh; kontainer lain tidak pernah.
    """
    sekarang = time.time() if sekarang is None else sekarang
    daftar = _docker("ps", "-a", "--filter", f"label={LABEL}",
                     "--format", '{{.Names}}\t{{.Label "' + LABEL_BERAKHIR + '"}}')
    if daftar.returncode != 0:
        raise GalatInfrastruktur("docker ps gagal: " + daftar.stderr.strip())
    dibuang = []
    for baris in daftar.stdout.splitlines():
        nama, _, akhir = baris.partition("\t")
        if akhir.isdigit() and int(akhir) < sekarang:
            _buang(nama)
            dibuang.append(nama)
    return dibuang


def periksa_host() -> None:
    """Gagal tertutup: melempar GalatInfrastruktur bila host tidak bisa menjamin batas SPEC.md §10."""
    info = _docker("info", "--format", "{{json .}}")
    if info.returncode != 0:
        raise GalatInfrastruktur("Docker tidak bisa dihubungi: " + info.stderr.strip()[:300])
    d = json.loads(info.stdout)
    kurang = []
    if d.get("CgroupVersion") != "2":
        kurang.append("cgroup v2")
    if not d.get("MemoryLimit"):
        kurang.append("batas memori")
    if not d.get("SwapLimit"):
        kurang.append("batas swap")
    if not d.get("PidsLimit"):
        kurang.append("batas jumlah proses")
    if not d.get("CpuCfsQuota"):
        kurang.append("batas CPU")
    if not any("seccomp" in o for o in d.get("SecurityOptions", [])):
        kurang.append("seccomp")
    if kurang:
        raise GalatInfrastruktur("host tidak mendukung: " + ", ".join(kurang))
    if not PROFIL_SECCOMP.is_file():
        raise GalatInfrastruktur(f"profil seccomp tidak ada: {PROFIL_SECCOMP}")
    for bahasa in citra.BAHASA:
        if not citra.ada(bahasa):
            raise GalatInfrastruktur(f"image {citra.nama(bahasa)} belum dibangun (judge/bangun-image.sh)")
