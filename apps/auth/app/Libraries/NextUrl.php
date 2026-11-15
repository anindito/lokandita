<?php

declare(strict_types=1);

namespace App\Libraries;

/**
 * Tujuan setelah login (SPEC.md §4.6): hanya https, dan host harus persis
 * salah satu dari ALLOWED_HOSTS. Selain itu pengguna dibawa ke Portal.
 */
final class NextUrl
{
    public static function safe(mixed $next): ?string
    {
        if (! is_string($next) || $next === '' || strlen($next) > 2000) {
            return null;
        }
        // Karakter kendali, spasi dan garis miring terbalik sering dipakai untuk mengelabui pengurai URL.
        if (preg_match('/[\x00-\x20\x7f\\\\]/', $next) === 1) {
            return null;
        }
        $u = parse_url($next);
        if ($u === false || ($u['scheme'] ?? '') !== 'https' || ! isset($u['host'])) {
            return null;
        }
        if (isset($u['user']) || isset($u['pass']) || isset($u['port'])) {
            return null;
        }
        $hosts = array_filter(array_map('trim', explode(',', (string) getenv('ALLOWED_HOSTS'))));
        if (! in_array(strtolower($u['host']), $hosts, true)) {
            return null;
        }

        return $next;
    }

    public static function orPortal(?string $next): string
    {
        return $next ?? rtrim((string) getenv('PORTAL_URL'), '/') . '/';
    }
}
