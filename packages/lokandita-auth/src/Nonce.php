<?php

declare(strict_types=1);

namespace Lokandita\Auth;

/**
 * Nonce CSP untuk satu permintaan. Dipakai filter SecurityHeaders dan view:
 * <script nonce="<?= esc(\Lokandita\Auth\Nonce::value(), 'attr') ?>">.
 */
final class Nonce
{
    private static ?string $value = null;

    public static function value(): string
    {
        return self::$value ??= rtrim(strtr(base64_encode(random_bytes(18)), '+/', '-_'), '=');
    }
}
