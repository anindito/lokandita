<?php

declare(strict_types=1);

namespace Lokandita\Auth\Seb;

/**
 * Enkripsi konfigurasi SEB yang disimpan (KONTRAK.md §8): XSalsa20-Poly1305 (libsodium
 * secretbox), kunci dari SEB_ENCRYPTION_KEY. Nilai tersimpan = nonce 24 byte + sandi.
 */
final class Brankas
{
    public static function kunci(string $polos): string
    {
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

        return $nonce . sodium_crypto_secretbox($polos, $nonce, self::kunciRahasia());
    }

    public static function buka(string $tersimpan): string
    {
        $n     = SODIUM_CRYPTO_SECRETBOX_NONCEBYTES;
        $polos = strlen($tersimpan) > $n ? sodium_crypto_secretbox_open(substr($tersimpan, $n), substr($tersimpan, 0, $n), self::kunciRahasia()) : false;
        if ($polos === false) {
            throw new \RuntimeException('Konfigurasi SEB tidak bisa dibuka: SEB_ENCRYPTION_KEY berbeda dari saat disimpan, atau datanya rusak.');
        }

        return $polos;
    }

    private static function kunciRahasia(): string
    {
        $env = (string) getenv('SEB_ENCRYPTION_KEY');
        if ($env === '') {
            throw new \RuntimeException('SEB_ENCRYPTION_KEY kosong.');
        }

        // Apa pun bentuk nilainya, kuncinya selalu 32 byte.
        return hash('sha256', $env, true);
    }
}
