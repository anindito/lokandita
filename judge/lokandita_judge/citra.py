"""Image Docker judge: nama, tag, dan pembangunannya (SPEC.md §10).

Tag sebuah image adalah 12 digit pertama sha256 atas seluruh isi judge/images/
(termasuk subdirektorinya). Mengubah Dockerfile, pelari.c, kode analisis atau
daftar pustaka dengan sendirinya mengubah tag; worker hanya mau memakai image
bertag itu, jadi image lama yang tidak cocok dengan kode tidak akan pernah
terpakai diam-diam (gagal tertutup).

Tag dihitung SEKALI per proses. Worker yang sedang berjalan karena itu tetap
memakai image yang ada saat ia mulai, walau isi judge/images/ di pohon kerja
berubah sesudahnya; image lama baru dibuang oleh `bersih`, yang dipanggil
infra/deploy.sh judge setelah worker baru terbukti berjalan.

    python3 -m lokandita_judge.citra tag       mencetak tag sekarang
    python3 -m lokandita_judge.citra bangun    membangun semua image
    python3 -m lokandita_judge.citra periksa   gagal bila ada image yang belum dibangun
    python3 -m lokandita_judge.citra bersih    membuang image judge bertag lain
"""

from __future__ import annotations

import hashlib
import subprocess
import sys
from pathlib import Path

DIR_IMAGES = Path(__file__).resolve().parent.parent / "images"

# kunci -> (nama image, Dockerfile, build-arg). Tiga bahasa, dan image analisis
# konsep (SPEC.md §9), yang hanya mengurai kode dan tidak pernah menjalankannya.
ANALISIS = "analisis"
BAHASA = {
    "c": ("lokandita/judge-c", "gcc.Dockerfile", ["--build-arg", "BAHASA=c"]),
    "cpp": ("lokandita/judge-cpp", "gcc.Dockerfile", ["--build-arg", "BAHASA=cpp"]),
    "python": ("lokandita/judge-python", "python.Dockerfile", []),
    ANALISIS: ("lokandita/judge-analisis", "analisis.Dockerfile", []),
}

_TAG: str | None = None


def tag() -> str:
    global _TAG
    if _TAG is None:
        h = hashlib.sha256()
        for berkas in sorted(p for p in DIR_IMAGES.rglob("*") if p.is_file() and "__pycache__" not in p.parts):
            h.update(str(berkas.relative_to(DIR_IMAGES)).encode() + b"\0" + berkas.read_bytes() + b"\0")
        _TAG = h.hexdigest()[:12]
    return _TAG


def nama(bahasa: str) -> str:
    return f"{BAHASA[bahasa][0]}:{tag()}"


def ada(bahasa: str) -> bool:
    return subprocess.run(["docker", "image", "inspect", nama(bahasa)], capture_output=True).returncode == 0


def bangun() -> None:
    for bahasa, (_, dockerfile, argumen) in BAHASA.items():
        print(f"membangun {nama(bahasa)} …", flush=True)
        subprocess.run(
            ["docker", "build", "--quiet", "-f", str(DIR_IMAGES / dockerfile), *argumen,
             "--label", "lokandita.judge.image=true", "-t", nama(bahasa), str(DIR_IMAGES)],
            check=True,
        )


def bersih() -> list[str]:
    """Membuang image judge yang tagnya bukan tag sekarang. Hanya dipanggil saat
    deploy, setelah worker baru berjalan: worker lama memakai image lama sampai ia diganti."""
    daftar = subprocess.run(
        ["docker", "images", "--filter", "label=lokandita.judge.image=true", "--format", "{{.Repository}}:{{.Tag}}"],
        capture_output=True, text=True, check=True,
    ).stdout.split()
    lama = [d for d in daftar if not d.endswith(":" + tag()) and not d.endswith(":<none>")]
    if lama:
        subprocess.run(["docker", "rmi", *lama], capture_output=True)
    return lama


def main() -> int:
    perintah = sys.argv[1] if len(sys.argv) > 1 else ""
    if perintah == "tag":
        print(tag())
    elif perintah == "bangun":
        bangun()
        subprocess.run(["docker", "images", "--filter", "label=lokandita.judge.image=true",
                        "--format", "  {{.Repository}}:{{.Tag}}  {{.Size}}"], check=True)
    elif perintah == "bersih":
        for nama_lama in bersih():
            print(f"dibuang: {nama_lama}")
    elif perintah == "periksa":
        kurang = [nama(b) for b in BAHASA if not ada(b)]
        if kurang:
            print("image judge belum dibangun: " + ", ".join(kurang) + " — jalankan judge/bangun-image.sh", file=sys.stderr)
            return 1
        print(f"semua image judge ada ({len(BAHASA)}), tag {tag()}")
    else:
        print(__doc__, file=sys.stderr)
        return 2
    return 0


if __name__ == "__main__":
    sys.exit(main())
