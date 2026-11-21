<?php

declare(strict_types=1);

namespace App\Libraries;

use Lokandita\Auth\Ekspor;

/**
 * Ekspor data milik satu pengguna dari LokaAuth (SPEC.md §16, KONTRAK.md §14).
 *
 * Setiap kueri terikat pada satu user id dan kolomnya disebut satu per satu:
 * kolom rahasia (password_hash, mfa_secret_enc, token_hash, kode cadangan) dan
 * `detail` baris audit tidak pernah diambil, jadi tidak bisa ikut terkirim.
 */
final class DataSaya
{
    /**
     * @return array{0: array<string, mixed>, 1: list<string>} isi berkas dan nama daftar yang terpotong
     */
    public static function susun(string $userId): array
    {
        $db        = db_connect();
        $ambil     = Ekspor::BATAS + 1;
        $terpotong = [];

        $akun = $db->query(
            'SELECT id, nomor_induk, nama, email, global_role, status, locale, idle_logout, paste_allowed,
                    mfa_enabled, must_change_password, created_at, last_login_at
             FROM users WHERE id = ?',
            [$userId],
        )->getRowArray();
        if ($akun === null) {
            throw new \RuntimeException('Pengguna sesi ini tidak ada.');
        }
        foreach (['idle_logout', 'paste_allowed', 'mfa_enabled', 'must_change_password'] as $k) {
            $akun[$k] = (bool) $akun[$k];
        }
        foreach (['created_at', 'last_login_at'] as $k) {
            $akun[$k] = Ekspor::waktu($akun[$k]);
        }

        $akses = $db->query('SELECT app, enabled, granted_at FROM app_access WHERE user_id = ? ORDER BY app', [$userId])->getResultArray();
        foreach ($akses as &$a) {
            $a['enabled']    = (bool) $a['enabled'];
            $a['granted_at'] = Ekspor::waktu($a['granted_at']);
        }
        unset($a);

        $sesi = $db->query(
            "SELECT created_at, last_seen_at, expires_at, revoked_at, ip, user_agent
             FROM sessions WHERE user_id = ? ORDER BY created_at DESC LIMIT {$ambil}",
            [$userId],
        )->getResultArray();
        foreach ($sesi as &$s) {
            foreach (['created_at', 'last_seen_at', 'expires_at', 'revoked_at'] as $k) {
                $s[$k] = Ekspor::waktu($s[$k]);
            }
        }
        unset($s);

        // Percobaan masuk dicatat per nomor induk, juga yang gagal dan yang dilakukan orang lain
        // atas nomor induk ini: itu catatan tentang akun pengguna ini.
        $percobaan = $db->query(
            "SELECT created_at, ip, success FROM login_attempts WHERE nomor_induk = ? ORDER BY created_at DESC, id DESC LIMIT {$ambil}",
            [$akun['nomor_induk']],
        )->getResultArray();
        foreach ($percobaan as &$p) {
            $p['created_at'] = Ekspor::waktu($p['created_at']);
            $p['success']    = (bool) $p['success'];
        }
        unset($p);

        // Baris audit: hanya waktu, aksi dan aplikasi. `detail` bisa memuat data orang lain
        // (mis. nama akun yang dibuat seorang superadmin), jadi tidak diambil.
        $riwayat = $db->query(
            "SELECT created_at, action, app, IF(actor_id = ?, 'pelaku', 'sasaran') AS sebagai
             FROM audit_log WHERE actor_id = ? OR target_id = ? ORDER BY id DESC LIMIT {$ambil}",
            [$userId, $userId, $userId],
        )->getResultArray();
        foreach ($riwayat as &$r) {
            $r['created_at'] = Ekspor::waktu($r['created_at']);
        }
        unset($r);

        return [[
            'akun'            => $akun,
            'akses_aplikasi'  => $akses,
            'sesi'            => Ekspor::potong('sesi', $sesi, $terpotong),
            'percobaan_masuk' => Ekspor::potong('percobaan_masuk', $percobaan, $terpotong),
            'riwayat'         => Ekspor::potong('riwayat', $riwayat, $terpotong),
        ], $terpotong];
    }
}
