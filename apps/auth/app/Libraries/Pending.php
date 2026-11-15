<?php

declare(strict_types=1);

namespace App\Libraries;

use CodeIgniter\HTTP\ResponseInterface;

/**
 * Login yang belum selesai: kata sandi sudah benar, langkah MFA belum.
 *
 * Disimpan di Redis selama 10 menit dan ditunjuk oleh cookie `__Host-tunda`.
 * Selama langkah ini belum selesai TIDAK ada baris di tabel sessions, sehingga
 * tidak ada aplikasi yang menganggap pengguna sudah masuk.
 */
final class Pending
{
    public const COOKIE = '__Host-tunda';

    private const UMUR = 600;

    /**
     * @param array<string, mixed> $data  user_id, next, tahap ('kode' | 'daftar'), dan untuk 'daftar': secret_enc (base64)
     */
    public static function start(ResponseInterface $response, array $data): void
    {
        $id = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
        RedisConn::get()->setex(self::key($id), self::UMUR, json_encode($data, JSON_THROW_ON_ERROR));
        $response->setCookie(self::COOKIE, $id, 0, '', '/', '', true, true, 'Lax');
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function get(): ?array
    {
        $id = (string) ($_COOKIE[self::COOKIE] ?? '');
        if (preg_match('/^[A-Za-z0-9_-]{43}$/', $id) !== 1) {
            return null;
        }
        $json = RedisConn::get()->get(self::key($id));

        return is_string($json) ? json_decode($json, true) : null;
    }

    public static function clear(ResponseInterface $response): void
    {
        $id = (string) ($_COOKIE[self::COOKIE] ?? '');
        if ($id !== '') {
            RedisConn::get()->del(self::key($id));
        }
        $response->setCookie(self::COOKIE, '', 0, '', '/', '', true, true, 'Lax');
    }

    private static function key(string $id): string
    {
        return 'auth:tunda:' . hash('sha256', $id);
    }
}
