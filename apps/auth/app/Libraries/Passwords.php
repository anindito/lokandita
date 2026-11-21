<?php

declare(strict_types=1);

namespace App\Libraries;

/**
 * Kata sandi: Argon2id, aturan mutu, dan riwayat (SPEC.md §4.2).
 */
final class Passwords
{
    public const MIN = 10;

    public const RIWAYAT = 3;

    public static function hash(string $password): string
    {
        return password_hash($password, PASSWORD_ARGON2ID);
    }

    /**
     * Masalah pada kata sandi baru, sebagai kunci bahasa `Auth.sandi.*`. Kosong berarti boleh.
     *
     * @param array<string, mixed>|null $user baris users; null untuk kata sandi awal yang dipilih admin
     *
     * @return list<string>
     */
    public static function problems(string $password, ?array $user = null): array
    {
        $masalah = [];
        if (mb_strlen($password) < self::MIN) {
            $masalah[] = 'terlaluPendek';
        }
        if (strlen($password) > 200) {
            $masalah[] = 'terlaluPanjang';
        }
        if (self::isCommon($password)) {
            $masalah[] = 'terlaluUmum';
        }
        if ($user !== null) {
            if (mb_strtolower($password) === mb_strtolower((string) $user['nomor_induk'])) {
                $masalah[] = 'samaNomorInduk';
            }
            if (self::usedRecently((string) $user['id'], $password)) {
                $masalah[] = 'pernahDipakai';
            }
        }

        return $masalah;
    }

    public static function isCommon(string $password): bool
    {
        static $daftar = null;
        $daftar ??= array_flip(file(APPPATH . 'Data/kata-sandi-umum.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: []);
        $kecil = mb_strtolower($password);

        // Satu huruf diulang-ulang, atau deret angka naik/turun, juga dianggap umum.
        return isset($daftar[$kecil])
            || preg_match('/^(.)\1+$/u', $kecil) === 1
            || str_contains('01234567890123456789', $kecil)
            || str_contains('98765432109876543210', $kecil);
    }

    /**
     * Benar bila kata sandi sama dengan salah satu dari tiga yang terakhir (termasuk yang sekarang).
     */
    public static function usedRecently(string $userId, string $password): bool
    {
        $rows = db_connect()->table('password_history')->select('password_hash')
            ->where('user_id', $userId)->orderBy('id', 'DESC')->limit(self::RIWAYAT)->get()->getResultArray();
        foreach ($rows as $row) {
            if (password_verify($password, $row['password_hash'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Menyimpan kata sandi baru dan mencatatnya di riwayat.
     */
    public static function set(string $userId, string $password, bool $mustChange): void
    {
        $hash = self::hash($password);
        $db   = db_connect();
        $db->table('users')->where('id', $userId)->update([
            'password_hash'        => $hash,
            'must_change_password' => $mustChange ? 1 : 0,
            'initial_password_expires_at' => null, // kata sandi baru, batas kata sandi awal tidak berlaku lagi
            'failed_attempts'      => 0,
            'locked_until'         => null,
            'updated_at'           => Clock::now(),
        ]);
        $db->table('password_history')->insert(['user_id' => $userId, 'password_hash' => $hash, 'created_at' => Clock::now()]);
    }

    /**
     * Hash untuk kata sandi SEMENTARA yang ACAK (dari self::random(), ±92 bit).
     *
     * Tetap Argon2id, tetapi dengan biaya lebih kecil (±40 md, bukan ±300 md),
     * supaya impor ratusan akun selesai dalam hitungan detik. Aman karena kata
     * sandi acak sepanjang itu tidak bisa ditebak, dan pemiliknya wajib
     * menggantinya pada login pertama (hash barunya memakai biaya penuh).
     * JANGAN dipakai untuk kata sandi pilihan manusia.
     */
    public static function hashTemporary(string $randomPassword): string
    {
        return password_hash($randomPassword, PASSWORD_ARGON2ID, ['memory_cost' => 19456, 'time_cost' => 2, 'threads' => 1]);
    }

    /**
     * Menyimpan hash yang sudah jadi sebagai kata sandi sementara (wajib diganti) dan mencatatnya di riwayat.
     */
    public static function setHash(string $userId, string $hash): void
    {
        $db = db_connect();
        $db->table('users')->where('id', $userId)->update([
            'password_hash'        => $hash,
            'must_change_password' => 1,
            'initial_password_expires_at' => null,
            'failed_attempts'      => 0,
            'locked_until'         => null,
            'updated_at'           => Clock::now(),
        ]);
        $db->table('password_history')->insert(['user_id' => $userId, 'password_hash' => $hash, 'created_at' => Clock::now()]);
    }

    /**
     * Kata sandi acak yang mudah diketik: 4 kelompok, tanpa huruf yang mirip (0/O, 1/l/I).
     */
    public static function random(): string
    {
        $abjad = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789';
        $kelompok = [];
        for ($k = 0; $k < 4; $k++) {
            $s = '';
            for ($i = 0; $i < 4; $i++) {
                $s .= $abjad[random_int(0, strlen($abjad) - 1)];
            }
            $kelompok[] = $s;
        }

        return implode('-', $kelompok);
    }
}
