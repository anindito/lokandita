<?php

declare(strict_types=1);

namespace App\Libraries;

/**
 * Pemeriksaan nomor induk + kata sandi, kunci bertahap, dan batas per IP (SPEC.md §4.2).
 *
 * Keputusan pemilik (2026-11-15): kunci 1, 5, 15, lalu 60 menit setiap kali
 * genap 5 kegagalan; batas per IP 100 kegagalan per 15 menit.
 * Keputusan pemilik (2026-11-24): akun mahasiswa tidak lagi dikunci makin lama;
 * setiap genap 5 kegagalan selalu 5 menit. Akun lain tetap bertahap.
 */
final class LoginGuard
{
    public const PER_PUTARAN = 5;

    public const JENDELA = 900; // 15 menit

    public const KUNCI_MENIT = [1, 5, 15, 60];

    public const KUNCI_MAHASISWA_MENIT = 5;

    public const BATAS_IP = 100;

    // Hash dari teks sembarang (bukan rahasia), dipakai supaya nomor induk yang tidak ada memakan waktu yang sama.
    private const HASH_PALSU = '$argon2id$v=19$m=65536,t=4,p=1$aW5CTlduZGtadUpVaGVWbA$/IeSs5/Wtq2S9YxNiuNWegTQB1gAZ0xUOArKh21J8w8';

    /**
     * @return array{ok: bool, reason: string, user: array<string, mixed>|null}
     */
    public static function check(string $nomorInduk, string $password, string $ip): array
    {
        $hasil = self::decide($nomorInduk, $password, $ip);

        db_connect()->table('login_attempts')->insert([
            'nomor_induk' => substr($nomorInduk, 0, 40),
            'ip'          => $ip,
            'success'     => $hasil['ok'] ? 1 : 0,
            'created_at'  => Clock::now(),
        ]);

        return $hasil;
    }

    /**
     * @param array<string, mixed> $user
     */
    public static function sandiAwalKedaluwarsa(array $user): bool
    {
        $batas = $user['initial_password_expires_at'] ?? null;

        return (int) $user['must_change_password'] === 1 && $batas !== null && Clock::toUnix((string) $batas) <= time();
    }

    /**
     * @return array{ok: bool, reason: string, user: array<string, mixed>|null}
     */
    private static function decide(string $nomorInduk, string $password, string $ip): array
    {
        if (self::ipLimited($ip)) {
            return ['ok' => false, 'reason' => 'ip_limited', 'user' => null];
        }

        $user = $nomorInduk === '' ? null : Users::findByNomorInduk($nomorInduk);
        if ($user === null) {
            password_verify($password, self::HASH_PALSU);

            return ['ok' => false, 'reason' => 'unknown_user', 'user' => null];
        }

        $benar = password_verify($password, (string) $user['password_hash']);

        if (self::isLocked($user)) {
            return ['ok' => false, 'reason' => 'locked', 'user' => $user];
        }
        if ($user['status'] !== 'active') {
            return ['ok' => false, 'reason' => 'status_' . $user['status'], 'user' => $user];
        }
        if (! $benar) {
            self::recordFailure($user);

            return ['ok' => false, 'reason' => 'wrong_password', 'user' => $user];
        }
        // Kata sandi awal yang tidak diganti sampai batasnya tidak berlaku lagi (KONTRAK.md §13).
        // Bukan percobaan yang salah, jadi tidak menambah hitungan kunci; jalan keluarnya reset.
        if (self::sandiAwalKedaluwarsa($user)) {
            return ['ok' => false, 'reason' => 'initial_password_expired', 'user' => $user];
        }

        return ['ok' => true, 'reason' => 'ok', 'user' => $user];
    }

    public static function ipLimited(string $ip): bool
    {
        $gagal = db_connect()->table('login_attempts')->where('ip', $ip)->where('success', 0)
            ->where('created_at >', Clock::now(-self::JENDELA))->countAllResults();

        return $gagal >= self::BATAS_IP;
    }

    /**
     * @param array<string, mixed> $user
     */
    public static function isLocked(array $user): bool
    {
        return $user['locked_until'] !== null && Clock::toUnix((string) $user['locked_until']) > time();
    }

    /**
     * Menambah hitungan gagal; setiap genap 5 akun dikunci: mahasiswa selalu 5 menit,
     * akun lain lebih lama dari sebelumnya.
     * Dipakai juga untuk kode verifikasi yang salah.
     *
     * @param array<string, mixed> $user
     */
    public static function recordFailure(array $user): void
    {
        $db    = db_connect();
        $gagal = (int) $user['failed_attempts'];

        // Kegagalan lama tidak dihitung: sebelum kunci pertama jendelanya 15 menit,
        // setelah pernah terkunci hitungan baru hilang setelah 24 jam tenang.
        $terakhir = $db->table('login_attempts')->selectMax('created_at', 't')
            ->where('nomor_induk', $user['nomor_induk'])->where('success', 0)->get()->getRowArray()['t'] ?? null;
        if ($terakhir !== null) {
            $diam = time() - Clock::toUnix((string) $terakhir);
            if (($gagal < self::PER_PUTARAN && $diam > self::JENDELA) || $diam > 86400) {
                $gagal = 0;
            }
        }

        $gagal++;
        $kunci = null;
        if ($gagal % self::PER_PUTARAN === 0) {
            $putaran = min(intdiv($gagal, self::PER_PUTARAN), count(self::KUNCI_MENIT));
            $menit   = $user['global_role'] === 'mahasiswa' ? self::KUNCI_MAHASISWA_MENIT : self::KUNCI_MENIT[$putaran - 1];
            $kunci   = Clock::now($menit * 60);
        }
        $db->table('users')->where('id', $user['id'])->update(['failed_attempts' => $gagal, 'locked_until' => $kunci]);
    }

    /**
     * @param array<string, mixed> $user
     */
    public static function recordSuccess(array $user): void
    {
        $data = ['failed_attempts' => 0, 'locked_until' => null, 'last_login_at' => Clock::now()];
        if (password_needs_rehash((string) $user['password_hash'], PASSWORD_ARGON2ID)) {
            // Tidak mungkin terjadi untuk akun yang dibuat di sini; dibiarkan untuk perubahan parameter kelak.
            Audit::log('password.rehash_needed', $user['id'], $user['id']);
        }
        db_connect()->table('users')->where('id', $user['id'])->update($data);
    }
}
