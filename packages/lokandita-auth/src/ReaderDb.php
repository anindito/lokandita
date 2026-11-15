<?php

declare(strict_types=1);

namespace Lokandita\Auth;

/**
 * Satu koneksi MySQL sebagai `identity_reader` (KONTRAK.md §3) per permintaan.
 * Hanya bisa membaca session_identity, user_directory dan app_access.
 */
final class ReaderDb
{
    private static ?\mysqli $db = null;

    public static function conn(): \mysqli
    {
        if (self::$db === null) {
            $c = Dsn::parse((string) getenv('IDENTITY_READER_DSN'));
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            self::$db = new \mysqli($c['hostname'], $c['username'], $c['password'], $c['database'], $c['port']);
            self::$db->set_charset('utf8mb4');
            self::$db->query("SET time_zone = '+00:00'");
        }

        return self::$db;
    }
}
