<?php

declare(strict_types=1);

namespace Lokandita\Auth;

/**
 * Mengurai DSN berformat KONTRAK.md §10: MySQLi://<user>:<password>@<host>:<port>/<schema>.
 *
 * CI4 bisa mengurai DSN sendiri, tetapi menyerahkan port sebagai string dan
 * mysqli menolaknya; karena itu DSN diurai di sini menjadi kolom-kolom biasa.
 */
final class Dsn
{
    /**
     * @return array{hostname: string, username: string, password: string, database: string, port: int}
     */
    public static function parse(string $dsn): array
    {
        $u = parse_url($dsn);
        if ($u === false || ! isset($u['host'], $u['user'], $u['pass'], $u['path'])) {
            // Sengaja tidak mencetak DSN: di dalamnya ada kata sandi.
            throw new \RuntimeException('DSN basis data tidak sesuai format MySQLi://user:sandi@host:port/skema');
        }

        return [
            'hostname' => $u['host'],
            'username' => $u['user'],
            'password' => $u['pass'],
            'database' => ltrim($u['path'], '/'),
            'port'     => (int) ($u['port'] ?? 3306),
        ];
    }

    /**
     * Menimpa kolom koneksi pada sebuah grup konfigurasi Database CI4.
     *
     * @param array<string, mixed> $group
     *
     * @return array<string, mixed>
     */
    public static function apply(array $group, string $dsn): array
    {
        $group['DSN'] = '';

        return array_merge($group, self::parse($dsn));
    }
}
