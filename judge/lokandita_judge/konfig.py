"""Konfigurasi worker dari variabel lingkungan (KONTRAK.md §10).

Daftar variabel wajib adalah judge/.env.example, sama seperti aplikasi PHP:
menambah kunci di sana berarti menambahnya ke pemeriksaan saat mulai.
Nilai rahasia tidak pernah dicetak; galat hanya menyebut nama variabel.
"""

from __future__ import annotations

import os
import re
from dataclasses import dataclass
from pathlib import Path
from urllib.parse import urlsplit

AKAR_JUDGE = Path(__file__).resolve().parent.parent


class KonfigSalah(Exception):
    pass


@dataclass(frozen=True)
class Konfig:
    db_host: str
    db_port: int
    db_user: str
    db_sandi: str
    db_skema: str
    redis_url: str
    redis_db: int
    konkurensi: int


def kunci_wajib() -> list[str]:
    teks = (AKAR_JUDGE / ".env.example").read_text(encoding="utf-8")
    return re.findall(r"^([A-Z][A-Z0-9_]*)=", teks, flags=re.MULTILINE)


def muat(env: dict[str, str] | None = None) -> Konfig:
    env = dict(os.environ) if env is None else env
    for kunci in kunci_wajib():
        if not env.get(kunci):
            raise KonfigSalah(f"variabel lingkungan {kunci} tidak ada atau kosong")

    dsn = urlsplit(env["DB_DSN"])
    if dsn.scheme.lower() != "mysqli" or not dsn.hostname or not dsn.username or not dsn.path.strip("/"):
        raise KonfigSalah("DB_DSN harus berbentuk MySQLi://<pengguna>:<sandi>@<host>:<port>/<skema>")

    redis = urlsplit(env["REDIS_URL"])
    if redis.scheme != "redis" or not redis.path.strip("/").isdigit():
        raise KonfigSalah("REDIS_URL harus berbentuk redis://:<sandi>@<host>:<port>/<db>")

    if not env["JUDGE_CONCURRENCY"].isdigit() or not 1 <= int(env["JUDGE_CONCURRENCY"]) <= 32:
        raise KonfigSalah("JUDGE_CONCURRENCY harus bilangan bulat 1–32")

    return Konfig(
        db_host=dsn.hostname,
        db_port=dsn.port or 3306,
        db_user=dsn.username,
        db_sandi=dsn.password or "",
        db_skema=dsn.path.strip("/"),
        redis_url=env["REDIS_URL"],
        redis_db=int(redis.path.strip("/")),
        konkurensi=int(env["JUDGE_CONCURRENCY"]),
    )
