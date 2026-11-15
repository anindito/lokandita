<?php

declare(strict_types=1);

namespace App\Libraries;

/**
 * Waktu dalam UTC, berformat DATETIME MySQL.
 */
final class Clock
{
    public static function now(int $offsetSeconds = 0): string
    {
        return gmdate('Y-m-d H:i:s', time() + $offsetSeconds);
    }

    public static function toUnix(string $utcDatetime): int
    {
        return (int) strtotime($utcDatetime . ' UTC');
    }
}
