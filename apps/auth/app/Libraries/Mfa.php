<?php

declare(strict_types=1);

namespace App\Libraries;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use OTPHP\TOTP;

/**
 * TOTP dan kode cadangan (SPEC.md §4.3).
 *
 * Rahasia TOTP disimpan terenkripsi dengan MFA_ENCRYPTION_KEY (libsodium secretbox).
 * Kode cadangan disimpan sebagai SHA-256 dan hanya bisa dipakai sekali.
 */
final class Mfa
{
    public const JUMLAH_KODE_CADANGAN = 8;

    // Toleransi selisih jam antara server dan ponsel, dalam detik (harus < 30).
    private const KELONGGARAN = 15;

    public static function newSecret(): string
    {
        return TOTP::generate(new SystemClock(), 20)->getSecret();
    }

    public static function encrypt(string $secret): string
    {
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

        return $nonce . sodium_crypto_secretbox($secret, $nonce, self::key());
    }

    public static function decrypt(string $stored): ?string
    {
        if (strlen($stored) <= SODIUM_CRYPTO_SECRETBOX_NONCEBYTES) {
            return null;
        }
        $plain = sodium_crypto_secretbox_open(
            substr($stored, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES),
            substr($stored, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES),
            self::key(),
        );

        return $plain === false ? null : $plain;
    }

    private static function key(): string
    {
        $key = @hex2bin((string) getenv('MFA_ENCRYPTION_KEY'));
        if ($key === false || strlen($key) !== SODIUM_CRYPTO_SECRETBOX_KEYBYTES) {
            throw new \RuntimeException('MFA_ENCRYPTION_KEY harus 64 karakter heksadesimal (openssl rand -hex 32).');
        }

        return $key;
    }

    public static function provisioningUri(string $secret, string $nomorInduk): string
    {
        $totp = TOTP::createFromSecret($secret, new SystemClock());
        $totp->setLabel($nomorInduk);
        $totp->setIssuer('LokaAuth');

        return $totp->getProvisioningUri();
    }

    /**
     * Kode QR sebagai SVG sebaris: tidak ada permintaan ke layanan luar.
     */
    public static function qrSvg(string $uri): string
    {
        $writer = new Writer(new ImageRenderer(new RendererStyle(240, 2), new SvgImageBackEnd()));
        $svg    = $writer->writeString($uri);

        // Buang deklarasi XML supaya bisa ditanam langsung di HTML.
        return (string) preg_replace('/^<\?xml[^>]*\?>\s*/', '', $svg);
    }

    /**
     * Memeriksa kode 6 angka. Kode yang sudah dipakai tidak bisa dipakai lagi
     * selama masih berlaku (dicatat di Redis selama 2 menit).
     */
    public static function verifyCode(string $userId, string $secret, string $code): bool
    {
        $code = preg_replace('/\s+/', '', $code) ?? '';
        if (preg_match('/^\d{6}$/', $code) !== 1) {
            return false;
        }
        if (! TOTP::createFromSecret($secret, new SystemClock())->verify($code, null, self::KELONGGARAN)) {
            return false;
        }
        $baru = RedisConn::get()->set('auth:mfa:terpakai:' . $userId . ':' . $code, '1', ['nx', 'ex' => 120]);

        return $baru === true;
    }

    /**
     * Membuat 8 kode cadangan baru (yang lama dihapus). Mengembalikan teks aslinya,
     * yang hanya ditampilkan sekali.
     *
     * @return list<string>
     */
    public static function regenerateBackupCodes(string $userId): array
    {
        $db = db_connect();
        $db->table('mfa_backup_codes')->where('user_id', $userId)->delete();

        $abjad = 'abcdefghjkmnpqrstuvwxyz23456789';
        $kode  = [];
        for ($n = 0; $n < self::JUMLAH_KODE_CADANGAN; $n++) {
            $s = '';
            for ($i = 0; $i < 10; $i++) {
                $s .= $abjad[random_int(0, strlen($abjad) - 1)];
            }
            $kode[] = substr($s, 0, 5) . '-' . substr($s, 5);
            $db->table('mfa_backup_codes')->insert(['user_id' => $userId, 'code_hash' => self::hashBackup($s)]);
        }

        return $kode;
    }

    /**
     * Memakai satu kode cadangan. Benar hanya bila kode itu ada dan belum pernah dipakai.
     */
    public static function useBackupCode(string $userId, string $code): bool
    {
        $bersih = strtolower(preg_replace('/[^A-Za-z0-9]/', '', $code) ?? '');
        if (strlen($bersih) !== 10) {
            return false;
        }
        $db = db_connect();
        $db->table('mfa_backup_codes')->where('user_id', $userId)->where('code_hash', self::hashBackup($bersih))
            ->where('used_at', null)->update(['used_at' => Clock::now()]);

        return $db->affectedRows() === 1;
    }

    private static function hashBackup(string $bersih): string
    {
        return hash('sha256', $bersih);
    }

    /**
     * Mengaktifkan MFA untuk seorang pengguna dengan rahasia yang sudah dibuktikan.
     */
    public static function enable(string $userId, string $secret): void
    {
        db_connect()->table('users')->where('id', $userId)->update([
            'mfa_enabled'    => 1,
            'mfa_secret_enc' => self::encrypt($secret),
            'updated_at'     => Clock::now(),
        ]);
    }

    /**
     * Menghapus MFA seorang pengguna (dipakai superadmin; pemanggil yang mencatat audit).
     */
    public static function clear(string $userId): void
    {
        $db = db_connect();
        $db->table('users')->where('id', $userId)->update(['mfa_enabled' => 0, 'mfa_secret_enc' => null, 'updated_at' => Clock::now()]);
        $db->table('mfa_backup_codes')->where('user_id', $userId)->delete();
    }
}
