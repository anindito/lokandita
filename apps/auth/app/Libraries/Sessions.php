<?php

declare(strict_types=1);

namespace App\Libraries;

use CodeIgniter\HTTP\ResponseInterface;
use Lokandita\Auth\Identity;

/**
 * Sesi buram berbasis basis data dan cookie `lokandita_sid` (KONTRAK.md §2).
 * Basis data hanya menyimpan SHA-256 dari nilai cookie.
 */
final class Sessions
{
    public const COOKIE = 'lokandita_sid';

    public const UMUR = 43200; // 12 jam, bergeser

    public static function hash(string $token): string
    {
        return hash('sha256', $token);
    }

    public static function looksValid(string $token): bool
    {
        return preg_match('/^[A-Za-z0-9_-]{43}$/', $token) === 1;
    }

    /**
     * Membuat sesi dan mengembalikan nilai cookie (hanya ada di sini dan di peramban).
     */
    public static function create(string $userId, string $ip, string $userAgent): string
    {
        $token = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
        db_connect()->table('sessions')->insert([
            'id'           => Uuid::v4(),
            'token_hash'   => self::hash($token),
            'user_id'      => $userId,
            'created_at'   => Clock::now(),
            'expires_at'   => Clock::now(self::UMUR),
            'last_seen_at' => Clock::now(),
            'ip'           => $ip,
            'user_agent'   => substr($userAgent, 0, 255),
        ]);

        return $token;
    }

    /**
     * Identitas untuk nilai cookie ini, lewat view yang sama dengan yang dibaca aplikasi klien.
     */
    public static function identity(string $token): ?Identity
    {
        if (! self::looksValid($token)) {
            return null;
        }
        $row = db_connect()->table('session_identity')->where('token_hash', self::hash($token))->get()->getRowArray();

        return $row === null ? null : Identity::fromRow($row);
    }

    /**
     * Memperpanjang sesi yang masih hidup. Mengembalikan expires_at baru, atau null bila sesi sudah mati.
     */
    public static function touch(string $token): ?string
    {
        if (self::identity($token) === null) {
            return null;
        }
        $expires = Clock::now(self::UMUR);
        db_connect()->table('sessions')->where('token_hash', self::hash($token))->where('revoked_at', null)
            ->update(['last_seen_at' => Clock::now(), 'expires_at' => $expires]);

        return $expires;
    }

    /**
     * Mencabut satu sesi. Mengembalikan user_id pemiliknya bila sesi itu ada.
     */
    public static function revoke(string $token): ?string
    {
        if (! self::looksValid($token)) {
            return null;
        }
        $db  = db_connect();
        $row = $db->table('sessions')->select('user_id')->where('token_hash', self::hash($token))->get()->getRowArray();
        if ($row === null) {
            return null;
        }
        $db->table('sessions')->where('token_hash', self::hash($token))->where('revoked_at', null)->update(['revoked_at' => Clock::now()]);

        return (string) $row['user_id'];
    }

    /**
     * Mencabut semua sesi seorang pengguna, kecuali (bila diberikan) sesi dengan token ini.
     * Mengembalikan jumlah sesi yang dicabut.
     */
    public static function revokeAllFor(string $userId, ?string $exceptToken = null): int
    {
        $q = db_connect()->table('sessions')->where('user_id', $userId)->where('revoked_at', null);
        if ($exceptToken !== null) {
            $q->where('token_hash !=', self::hash($exceptToken));
        }
        $q->update(['revoked_at' => Clock::now()]);

        return db_connect()->affectedRows();
    }

    public static function setCookie(ResponseInterface $response, string $token): void
    {
        $response->setCookie(self::COOKIE, $token, self::UMUR, (string) getenv('COOKIE_DOMAIN'), '/', '', true, true, 'Lax');
    }

    public static function clearCookie(ResponseInterface $response): void
    {
        $response->setCookie(self::COOKIE, '', 0, (string) getenv('COOKIE_DOMAIN'), '/', '', true, true, 'Lax');
    }

    public static function tokenFrom(\CodeIgniter\HTTP\RequestInterface $request): string
    {
        return (string) ($_COOKIE[self::COOKIE] ?? '');
    }
}
