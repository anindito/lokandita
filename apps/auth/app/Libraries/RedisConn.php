<?php

declare(strict_types=1);

namespace App\Libraries;

/**
 * Koneksi Redis dari REDIS_URL (KONTRAK.md §10): redis://:<sandi>@redis:6379/<db>.
 */
final class RedisConn
{
    private static ?\Redis $redis = null;

    public static function get(): \Redis
    {
        if (self::$redis === null) {
            $u = parse_url((string) getenv('REDIS_URL'));
            $r = new \Redis();
            $r->connect((string) $u['host'], (int) ($u['port'] ?? 6379), 2.0);
            if (($u['pass'] ?? '') !== '') {
                $r->auth($u['pass']);
            }
            $r->select((int) ltrim((string) ($u['path'] ?? '/0'), '/'));
            self::$redis = $r;
        }

        return self::$redis;
    }
}
