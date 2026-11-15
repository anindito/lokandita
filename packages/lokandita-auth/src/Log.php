<?php

declare(strict_types=1);

namespace Lokandita\Auth;

/**
 * Log terstruktur: satu baris JSON ke stderr kontainer (`docker logs <app>`).
 * Jangan pernah memasukkan kata sandi, token, atau cookie ke $context.
 */
final class Log
{
    /**
     * @param array<string, scalar|null> $context
     */
    public static function event(string $event, array $context = []): void
    {
        $line = json_encode(
            ['t' => gmdate('Y-m-d\TH:i:s\Z'), 'app' => (string) getenv('LOKANDITA_APP'), 'event' => $event] + $context,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
        );
        @file_put_contents('php://stderr', $line . "\n");
    }
}
