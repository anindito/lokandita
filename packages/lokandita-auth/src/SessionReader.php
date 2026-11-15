<?php

declare(strict_types=1);

namespace Lokandita\Auth;

/**
 * Membaca sesi lewat view `session_identity` sebagai `identity_reader` (KONTRAK.md §3, §4).
 * Dibaca pada setiap permintaan, tanpa cache: sesi yang dicabut langsung tidak berlaku.
 */
final class SessionReader
{
    public const COOKIE = 'lokandita_sid';

    private static ?\mysqli $db = null;

    private static function db(): \mysqli
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

    public static function looksValid(string $sid): bool
    {
        return preg_match('/^[A-Za-z0-9_-]{43}$/', $sid) === 1;
    }

    public static function identity(string $sid): ?Identity
    {
        if (! self::looksValid($sid)) {
            return null;
        }
        $stmt = self::db()->prepare('SELECT * FROM session_identity WHERE token_hash = ?');
        $stmt->execute([hash('sha256', $sid)]);
        $row = $stmt->get_result()->fetch_assoc();

        return $row === null ? null : Identity::fromRow($row);
    }

    public static function hasAccess(string $userId, string $app): bool
    {
        $stmt = self::db()->prepare('SELECT 1 FROM app_access WHERE user_id = ? AND app = ? AND enabled = 1');
        $stmt->execute([$userId, $app]);

        return $stmt->get_result()->num_rows === 1;
    }

    /**
     * Mengirim ulang cookie yang sama dengan umur baru, setelah sesi diperpanjang (KONTRAK.md §2).
     */
    public static function refreshCookie(\CodeIgniter\HTTP\ResponseInterface $response, string $sid): void
    {
        $response->setCookie(self::COOKIE, $sid, 43200, (string) getenv('COOKIE_DOMAIN'), '/', '', true, true, 'Lax');
    }
}
