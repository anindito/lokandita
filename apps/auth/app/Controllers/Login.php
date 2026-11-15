<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\Audit;
use App\Libraries\LoginGuard;
use App\Libraries\NextUrl;
use App\Libraries\Sessions;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Masuk dengan nomor induk dan kata sandi (SPEC.md §4.1, §4.2, §4.6).
 */
final class Login extends BaseController
{
    public function form(): ResponseInterface
    {
        $next = NextUrl::safe($this->request->getGet('next'));

        // Sudah masuk: tidak perlu melihat form lagi.
        if (Sessions::identity(Sessions::tokenFrom($this->request)) !== null) {
            return redirect()->to(NextUrl::orPortal($next));
        }

        $pesan = $this->request->getGet('keluar') !== null
            ? [['jenis' => 'sukses', 'teks' => lang('Auth.sudahKeluar')]]
            : [];

        return $this->tampilkan($next, '', $pesan);
    }

    public function submit(): ResponseInterface
    {
        $nomor = trim((string) $this->request->getPost('nomor_induk'));
        $sandi = (string) $this->request->getPost('password');
        $next  = NextUrl::safe($this->request->getPost('next'));
        $ip    = $this->request->getIPAddress();

        $hasil = LoginGuard::check($nomor, $sandi, $ip);
        $user  = $hasil['user'];

        if (! $hasil['ok']) {
            // Alasan sebenarnya hanya masuk log; pengguna selalu melihat teks yang sama.
            Audit::log('login.failed', null, $user['id'] ?? null, ['reason' => $hasil['reason'], 'nomor_induk' => substr($nomor, 0, 40)]);

            return $this->tampilkan($next, $nomor, $this->galat(lang('Auth.loginGagal')), 422);
        }

        // Superadmin wajib MFA; langkah itu belum dibangun, jadi tidak ada sesi tanpa MFA.
        if ($user['global_role'] === 'superadmin' || (int) $user['mfa_enabled'] === 1) {
            Audit::log('login.failed', null, $user['id'], ['reason' => 'mfa_not_available', 'nomor_induk' => substr($nomor, 0, 40)]);

            return $this->tampilkan($next, $nomor, $this->galat(lang('Auth.loginGagal')), 422);
        }

        return $this->masukkan($user, $next);
    }

    /**
     * Membuat sesi untuk pengguna yang sudah lolos semua pemeriksaan.
     *
     * @param array<string, mixed> $user
     */
    private function masukkan(array $user, ?string $next): ResponseInterface
    {
        LoginGuard::recordSuccess($user);
        $token = Sessions::create((string) $user['id'], $this->request->getIPAddress(), (string) $this->request->getUserAgent());
        Audit::log('login.success', $user['id'], $user['id']);

        $tujuan = (int) $user['must_change_password'] === 1
            ? '/ganti-password' . ($next !== null ? '?next=' . rawurlencode($next) : '')
            : NextUrl::orPortal($next);

        $jawab = redirect()->to($tujuan);
        Sessions::setCookie($jawab, $token);

        return $jawab;
    }

    /**
     * @param list<array{jenis: string, teks: string}> $pesan
     */
    private function tampilkan(?string $next, string $nomor, array $pesan, int $status = 200): ResponseInterface
    {
        return $this->halaman('login', [
            'judul' => lang('Auth.masuk'),
            'next'  => $next,
            'nomor' => $nomor,
            'pesan' => $pesan,
        ], $status)->setHeader('Cache-Control', 'no-store');
    }
}
