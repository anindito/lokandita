<?php

declare(strict_types=1);

namespace Lokandita\Auth\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Lokandita\Auth\Nonce;

/**
 * Header keamanan pada setiap jawaban aplikasi (SPEC.md §16).
 *
 * HSTS dipasang oleh Caddy (KONTRAK.md §9). Referrer-Policy sengaja
 * `same-origin`: `no-referrer` merusak pemeriksaan asal pada POST.
 */
final class SecurityHeaders implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $response->setHeader('Content-Security-Policy', self::policy());
        $response->setHeader('X-Content-Type-Options', 'nosniff');
        $response->setHeader('X-Frame-Options', 'DENY');
        $response->setHeader('Referrer-Policy', 'same-origin');
        $response->setHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        return $response;
    }

    public static function policy(): string
    {
        return implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'nonce-" . Nonce::value() . "'",
            "style-src 'self'",
            "img-src 'self' data:",
            "font-src 'self'",
            "connect-src 'self'",
            "object-src 'none'",
            "base-uri 'self'",
            "frame-ancestors 'none'",
            // Peramban juga memeriksa tujuan redirect setelah form dikirim,
            // jadi semua host Lokandita harus tercantum (login -> next, keluar -> LokaAuth).
            "form-action 'self' " . implode(' ', self::formTargets()),
        ]);
    }

    /**
     * @return list<string>
     */
    private static function formTargets(): array
    {
        $origins = [];
        foreach (['APP_URL', 'PORTAL_URL', 'AUTH_PUBLIC_URL'] as $key) {
            $url = (string) getenv($key);
            if ($url !== '') {
                $origins[] = rtrim($url, '/');
            }
        }
        foreach (explode(',', (string) getenv('ALLOWED_HOSTS')) as $host) {
            if (trim($host) !== '') {
                $origins[] = 'https://' . trim($host);
            }
        }

        return array_values(array_unique($origins));
    }
}
