<?php

declare(strict_types=1);

namespace Lokandita\Auth;

/**
 * Identitas untuk permintaan yang sedang berjalan. Diisi oleh filter sesi.
 */
final class Current
{
    private static ?Identity $identity = null;

    public static function set(?Identity $identity): void
    {
        self::$identity = $identity;
    }

    public static function identity(): ?Identity
    {
        return self::$identity;
    }
}
