<?php

declare(strict_types=1);

namespace Lokandita\Auth\Testing;

/**
 * Penjaga: tes hanya boleh menyentuh skema yang namanya berakhiran _test.
 */
final class TestSchema
{
    /**
     * Mengembalikan DSN itu sendiri bila skemanya berakhiran _test; selain itu melempar galat.
     */
    public static function dsn(string $dsn): string
    {
        $schema = self::schemaOf($dsn);
        if (! str_ends_with($schema, '_test')) {
            throw new \RuntimeException(
                "Tes menolak berjalan: DB_DSN menunjuk skema '{$schema}', bukan skema *_test. Jalankan lewat infra/tes.sh.",
            );
        }

        return $dsn;
    }

    public static function schemaOf(string $dsn): string
    {
        return ltrim((string) parse_url($dsn, PHP_URL_PATH), '/');
    }

    /**
     * Koneksi mysqli langsung ke skema tes, untuk menyiapkan dan memeriksa data di dalam tes.
     */
    public static function connect(string $dsn): \mysqli
    {
        $u = parse_url(self::dsn($dsn));
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $db = new \mysqli($u['host'], $u['user'], $u['pass'], ltrim($u['path'], '/'), $u['port'] ?? 3306);
        $db->set_charset('utf8mb4');
        $db->query("SET time_zone = '+00:00'");

        return $db;
    }
}
