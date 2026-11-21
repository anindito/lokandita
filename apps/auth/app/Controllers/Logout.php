<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\Audit;
use App\Libraries\Sessions;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Keluar: sesi dicabut di server, sehingga berakhir di semua aplikasi (SPEC.md §4.4).
 *
 * Sesudah keluar, dari aplikasi mana pun, peramban dibawa ke halaman depan Portal
 * (PORTAL_URL) — keputusan pemilik 2026-11-21, KONTRAK.md §2.
 */
final class Logout extends BaseController
{
    /**
     * POST /keluar — tombol Keluar di LokaAuth sendiri.
     */
    public function keluar(): ResponseInterface
    {
        $token  = Sessions::tokenFrom($this->request);
        $userId = Sessions::revoke($token);
        Audit::log('logout', $userId, $userId);

        return $this->kePortal();
    }

    /**
     * GET /keluar-selesai — tujuan aplikasi klien setelah mereka mencabut sesi
     * lewat API internal. Hanya LokaAuth yang menghapus cookie (KONTRAK.md §2).
     *
     * Cookie hanya dihapus bila sesinya memang sudah mati, sehingga tautan ini
     * tidak bisa dipakai pihak lain untuk mengeluarkan orang yang masih masuk.
     */
    public function selesai(): ResponseInterface
    {
        $token = Sessions::tokenFrom($this->request);
        if (Sessions::identity($token) !== null) {
            return redirect()->to('/');
        }

        return $this->kePortal();
    }

    /**
     * Cookie sesi dihapus, lalu ke halaman depan Portal. Tanpa cookie, Portal menampilkan
     * halaman publiknya dan bukan /beranda.
     */
    private function kePortal(): ResponseInterface
    {
        $jawab = redirect()->to(rtrim((string) getenv('PORTAL_URL'), '/') . '/');
        Sessions::clearCookie($jawab);

        return $jawab;
    }
}
