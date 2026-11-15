<?php

declare(strict_types=1);

namespace App\Libraries;

use Psr\Clock\ClockInterface;

/**
 * Jam sistem untuk pustaka TOTP.
 */
final class SystemClock implements ClockInterface
{
    public function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
    }
}
