"""Image Docker judge: nama, tag, dan pembangunannya (SPEC.md §10).

Tag sebuah image adalah 12 digit pertama sha256 atas seluruh isi judge/images/.
Mengubah Dockerfile, pelari.c atau daftar pustaka dengan sendirinya mengubah
tag; worker hanya mau memakai image bertag itu, jadi image lama yang tidak
cocok dengan kode tidak akan pernah terpakai diam-diam (gagal tertutup).

    python3 -m lokandita_judge.citra tag       mencetak tag sekarang
    python3 -m lokandita_judge.citra bangun    membangun ketiga image
    python3 -m lokandita_judge.citra periksa   gagal bila ada image yang belum dibangun
"""

from __future__ import annotations

import hashlib
import subprocess
import sys
from pathlib import Path

DIR_IMAGES = Path(__file__).resolve().parent.parent / "images"

# bahasa -> (nama image, Dockerfile, build-arg)
BAHASA = {
    "c": ("lokandita/judge-c", "gcc.Dockerfile", ["--build-arg", "BAHASA=c"]),
    "cpp": ("lokandita/judge-cpp", "gcc.Dockerfile", ["--build-arg", "BAHASA=cpp"]),
    "python": ("lokandita/judge-python", "python.Dockerfile", []),
}


def tag() -> str:
    h = hashlib.sha256()
    for berkas in sorted(DIR_IMAGES.iterdir()):
        if berkas.is_file():
            h.update(berkas.name.encode() + b"\0" + berkas.read_bytes() + b"\0")
    return h.hexdigest()[:12]


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
    # Image judge dengan tag lama tidak dipakai lagi.
    daftar = subprocess.run(
        ["docker", "images", "--filter", "label=lokandita.judge.image=true", "--format", "{{.Repository}}:{{.Tag}}"],
        capture_output=True, text=True, check=True,
    ).stdout.split()
    lama = [d for d in daftar if not d.endswith(":" + tag()) and not d.endswith(":<none>")]
    if lama:
        subprocess.run(["docker", "rmi", *lama], capture_output=True)


def main() -> int:
    perintah = sys.argv[1] if len(sys.argv) > 1 else ""
    if perintah == "tag":
        print(tag())
    elif perintah == "bangun":
        bangun()
        subprocess.run(["docker", "images", "--filter", "label=lokandita.judge.image=true",
                        "--format", "  {{.Repository}}:{{.Tag}}  {{.Size}}"], check=True)
    elif perintah == "periksa":
        kurang = [nama(b) for b in BAHASA if not ada(b)]
        if kurang:
            print("image judge belum dibangun: " + ", ".join(kurang) + " — jalankan judge/bangun-image.sh", file=sys.stderr)
            return 1
        print(f"ketiga image judge ada, tag {tag()}")
    else:
        print(__doc__, file=sys.stderr)
        return 2
    return 0


if __name__ == "__main__":
    sys.exit(main())
