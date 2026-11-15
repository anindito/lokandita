"""Sambungan ke MySQL dan Redis di 127.0.0.1 (KONTRAK.md §6)."""

from __future__ import annotations

import ssl

import pymysql
import pymysql.cursors
import redis

from .konfig import Konfig


def mysql(k: Konfig) -> pymysql.connections.Connection:
    # MySQL 8.4 meminta sambungan terenkripsi untuk caching_sha2_password.
    # Sertifikatnya buatan MySQL sendiri dan lawan bicaranya 127.0.0.1, jadi
    # tidak diverifikasi; tanpa TLS kita butuh pustaka `cryptography` tambahan.
    tls = ssl.create_default_context()
    tls.check_hostname = False
    tls.verify_mode = ssl.CERT_NONE
    return pymysql.connect(
        host=k.db_host,
        port=k.db_port,
        user=k.db_user,
        password=k.db_sandi,
        database=k.db_skema,
        charset="utf8mb4",
        autocommit=True,
        ssl=tls,
        connect_timeout=5,
        read_timeout=30,
        write_timeout=30,
        cursorclass=pymysql.cursors.DictCursor,
        init_command="SET time_zone = '+00:00'",
    )


def antrean(k: Konfig) -> redis.Redis:
    return redis.Redis.from_url(k.redis_url, decode_responses=True, socket_connect_timeout=5)
