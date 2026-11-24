<?php

declare(strict_types=1);

namespace Lokandita\Auth;

/**
 * Satu koneksi MySQL sebagai `roster_reader` (KONTRAK.md §18) per permintaan, dari
 * ROSTER_READER_DSN. Hanya bisa membaca lima view roster_*; tidak bisa membaca tabel mana pun
 * dan tidak bisa menulis. Terpisah dari koneksi skema aplikasi itu sendiri: tidak ada join
 * antara keduanya.
 *
 * Kode yang membaca roster memakai nama view saja, tanpa nama skema: skemanya ada di DSN dan
 * berganti saat tabel roster pindah ke inti Lokandita (tahap A1).
 */
final class RosterDb
{
    private static ?\mysqli $db = null;

    public static function conn(): \mysqli
    {
        if (self::$db === null) {
            $c = Dsn::parse((string) getenv('ROSTER_READER_DSN'));
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            self::$db = new \mysqli($c['hostname'], $c['username'], $c['password'], $c['database'], $c['port']);
            self::$db->set_charset('utf8mb4');
            self::$db->query("SET time_zone = '+00:00'");
        }

        return self::$db;
    }
}
