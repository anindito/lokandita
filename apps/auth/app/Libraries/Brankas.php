<?php

declare(strict_types=1);

namespace App\Libraries;

/**
 * Simpanan sementara di Redis untuk dua hal milik seorang superadmin:
 *
 *  - 'kredensial': daftar kata sandi sementara, bisa diambil SEKALI (SPEC.md §4.1);
 *  - 'impor'     : hasil pemeriksaan berkas impor, menunggu dijalankan.
 *
 * Isi dienkripsi dengan MFA_ENCRYPTION_KEY, terikat pada ID superadmin yang
 * membuatnya, dan hilang sendiri setelah 30 menit.
 */
final class Brankas
{
    private const UMUR = 1800;

    /**
     * @param array<mixed> $isi
     */
    public static function simpan(string $jenis, string $adminId, array $isi): string
    {
        $token = bin2hex(random_bytes(24));
        $data  = base64_encode(Mfa::encrypt(json_encode(['admin' => $adminId, 'isi' => $isi], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE)));
        RedisConn::get()->setex(self::kunci($jenis, $token), self::UMUR, $data);

        return $token;
    }

    /**
     * Membaca tanpa menghapus. Null bila tidak ada, kedaluwarsa, atau milik superadmin lain.
     *
     * @return array<mixed>|null
     */
    public static function lihat(string $jenis, string $adminId, string $token): ?array
    {
        if (preg_match('/^[0-9a-f]{48}$/', $token) !== 1) {
            return null;
        }

        return self::buka(RedisConn::get()->get(self::kunci($jenis, $token)), $adminId);
    }

    /**
     * Mengambil dan langsung menghapus. Milik superadmin lain tidak ikut terhapus.
     *
     * @return array<mixed>|null
     */
    public static function ambil(string $jenis, string $adminId, string $token): ?array
    {
        $isi = self::lihat($jenis, $adminId, $token);
        if ($isi !== null) {
            RedisConn::get()->del(self::kunci($jenis, $token));
        }

        return $isi;
    }

    /**
     * @return array<mixed>|null
     */
    private static function buka(mixed $data, string $adminId): ?array
    {
        if (! is_string($data)) {
            return null;
        }
        $json = Mfa::decrypt((string) base64_decode($data, true));
        $arr  = $json === null ? null : json_decode($json, true);
        if (! is_array($arr) || ($arr['admin'] ?? null) !== $adminId) {
            return null;
        }

        return $arr['isi'];
    }

    private static function kunci(string $jenis, string $token): string
    {
        return 'auth:brankas:' . $jenis . ':' . hash('sha256', $token);
    }
}
