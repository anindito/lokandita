<?php

declare(strict_types=1);

namespace Lokandita\Auth;

/**
 * Nama aplikasi yang sedang berjalan (LOKANDITA_APP ditanam ke image oleh deploy.sh).
 */
final class Aplikasi
{
    private const NAMA = ['auth' => 'Akun Lokandita', 'lokakode' => 'LokaKode', 'lokaujian' => 'LokaUjian', 'portal' => 'Lokandita'];

    public static function kode(): string
    {
        return (string) getenv('LOKANDITA_APP');
    }

    public static function nama(): string
    {
        return self::NAMA[self::kode()] ?? 'Lokandita';
    }

    /**
     * URL mutlak permintaan ini, dibangun dari APP_URL — header Host dari klien tidak dipercaya.
     */
    public static function urlSekarang(\CodeIgniter\HTTP\RequestInterface $request): string
    {
        $uri   = $request->getUri();
        $query = $uri->getQuery();

        return rtrim((string) getenv('APP_URL'), '/') . '/' . ltrim($uri->getPath(), '/') . ($query !== '' ? '?' . $query : '');
    }
}
