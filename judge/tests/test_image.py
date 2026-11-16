"""Image judge dan profil seccomp (SPEC.md §10) — terhadap Docker sungguhan."""

from __future__ import annotations

import importlib.util
import json
import subprocess
from pathlib import Path

import pytest

from lokandita_judge import citra

JUDGE = Path(__file__).resolve().parent.parent


def di_image(bahasa: str, *perintah: str) -> str:
    """Menjalankan perintah di image judge, tanpa jaringan, dan mengembalikan stdout."""
    hasil = subprocess.run(
        ["docker", "run", "--rm", "--network", "none", "--read-only", "--tmpfs", "/box:mode=1777", "--cap-drop", "ALL",
         "--label", "lokandita.judge=true", citra.nama(bahasa), *perintah],
        capture_output=True, text=True, timeout=60,
    )
    assert hasil.returncode == 0, hasil.stderr
    return hasil.stdout.strip()


@pytest.mark.parametrize("bahasa", ["c", "cpp", "python", "analisis"])
def test_image_ada_dengan_tag_sekarang(bahasa):
    assert citra.ada(bahasa), f"{citra.nama(bahasa)} belum dibangun; jalankan judge/bangun-image.sh"


def test_tag_berubah_bila_isi_images_berubah(tmp_path, monkeypatch):
    import shutil

    lama = citra.tag()
    salinan = tmp_path / "images"
    shutil.copytree(citra.DIR_IMAGES, salinan, ignore=shutil.ignore_patterns("__pycache__"))
    monkeypatch.setattr(citra, "DIR_IMAGES", salinan)

    def tag_baru() -> str:
        monkeypatch.setattr(citra, "_TAG", None)  # tag dihitung sekali per proses
        return citra.tag()

    assert tag_baru() == lama
    (salinan / "pelari.c").write_text("// diubah\n")
    diubah = tag_baru()
    assert diubah != lama
    # Kode analisis ada di subdirektori dan ikut menentukan tag.
    (salinan / "analisis" / "py.py").write_text("# diubah\n")
    assert tag_baru() not in (lama, diubah)


def test_tag_tetap_selama_proses_berjalan(tmp_path, monkeypatch):
    """Worker yang sedang berjalan tidak boleh berpindah image karena pohon kerja diedit."""
    lama = citra.tag()
    monkeypatch.setattr(citra, "DIR_IMAGES", tmp_path)
    assert citra.tag() == lama


@pytest.mark.parametrize("bahasa,kompiler", [("c", "gcc"), ("cpp", "gcc"), ("cpp", "g++")])
def test_gcc_13(bahasa, kompiler):
    assert di_image(bahasa, kompiler, "-dumpversion") == "13"


def test_image_c_tidak_membawa_kompiler_cpp():
    hasil = subprocess.run(["docker", "run", "--rm", "--network", "none", citra.nama("c"), "sh", "-c", "command -v g++"],
                           capture_output=True, text=True, timeout=60)
    assert hasil.returncode != 0 and hasil.stdout == ""


def test_python_dan_pustaka_dikunci():
    keluaran = di_image(
        "python", "python3", "-c",
        "import sys, numpy, matplotlib; print(sys.version_info[:2], numpy.__version__, matplotlib.__version__, matplotlib.get_backend())",
    )
    kunci = dict(b.split(" ")[0].split("==") for b in (citra.DIR_IMAGES / "requirements-python.txt").read_text().splitlines()
                 if "==" in b)
    assert keluaran == f"(3, 12) {kunci['numpy']} {kunci['matplotlib']} Agg"


@pytest.mark.parametrize("daftar", ["requirements-python.txt", "requirements-analisis.txt"])
def test_setiap_pustaka_python_dikunci_dengan_hash(daftar):
    for baris in (citra.DIR_IMAGES / daftar).read_text().splitlines():
        if baris and not baris.startswith("#"):
            assert "==" in baris and "--hash=sha256:" in baris, baris


def test_image_analisis_tidak_membawa_kompiler():
    hasil = subprocess.run(["docker", "run", "--rm", "--network", "none", citra.nama("analisis"), "sh", "-c",
                            "command -v gcc g++ cc"], capture_output=True, text=True, timeout=60)
    assert hasil.returncode != 0 and hasil.stdout == ""


@pytest.mark.parametrize("bahasa", ["c", "cpp", "python", "analisis"])
def test_image_berjalan_bukan_sebagai_root(bahasa):
    assert di_image(bahasa, "id", "-u") == "65534"
    konfig = json.loads(subprocess.run(["docker", "image", "inspect", citra.nama(bahasa)],
                                       capture_output=True, text=True, check=True).stdout)[0]["Config"]
    assert konfig["User"] == "65534:65534"
    assert konfig["Env"].count("HOME=/box") == 1


@pytest.mark.parametrize("bahasa", ["c", "cpp", "python", "analisis"])
def test_tidak_ada_berkas_setuid_di_image(bahasa):
    # no-new-privileges sudah mematikan setuid; ini memastikan tidak ada yang bergantung padanya.
    ditemukan = di_image(bahasa, "sh", "-c", "find / -xdev -perm /6000 -type f 2>/dev/null | sort | tr '\\n' ' '")
    assert all(Path(p).name in {"su", "mount", "umount", "passwd", "chsh", "chfn", "gpasswd", "newgrp", "chage",
                                "expiry", "unix_chkpwd", "wall", "pam_extrausers_chkpwd"} for p in ditemukan.split())


def muat_pembuat_profil():
    spec = importlib.util.spec_from_file_location("buat_profil", JUDGE / "seccomp" / "buat-profil.py")
    modul = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(modul)
    return modul


def test_profil_seccomp_sama_dengan_keluaran_pembuatnya():
    assert (JUDGE / "seccomp" / "judge.json").read_text() == muat_pembuat_profil().teks()


def test_profil_seccomp_menolak_secara_bawaan_dan_hanya_x86_64():
    profil = json.loads((JUDGE / "seccomp" / "judge.json").read_text())
    assert profil["defaultAction"] == "SCMP_ACT_ERRNO"
    assert profil["architectures"] == ["SCMP_ARCH_X86_64"]
    diizinkan = {n for a in profil["syscalls"] if a["action"] == "SCMP_ACT_ALLOW" and not a.get("args") for n in a["names"]}
    for terlarang in ["ptrace", "process_vm_readv", "mount", "unshare", "setns", "bpf", "io_uring_setup", "keyctl",
                      "userfaultfd", "perf_event_open", "socket", "clone", "clone3", "kexec_load", "shmget", "mq_open",
                      "open_by_handle_at", "personality", "seccomp"]:
        assert terlarang not in diizinkan, terlarang
    soket = [a for a in profil["syscalls"] if a["names"] == ["socket"]]
    assert len(soket) == 1 and soket[0]["args"] == [{"index": 0, "op": "SCMP_CMP_EQ", "value": 1}]
