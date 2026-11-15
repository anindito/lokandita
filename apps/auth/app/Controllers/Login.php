<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\Audit;
use App\Libraries\LoginGuard;
use App\Libraries\Mfa;
use App\Libraries\NextUrl;
use App\Libraries\Pending;
use App\Libraries\Sessions;
use App\Libraries\SignIn;
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

        // Kata sandi benar. Bila akun memakai MFA, sesi baru dibuat setelah langkah kedua.
        if ((int) $user['mfa_enabled'] === 1) {
            $jawab = redirect()->to('/login/kode');
            Pending::start($jawab, ['user_id' => $user['id'], 'next' => $next, 'tahap' => 'kode']);

            return $jawab;
        }
        // Superadmin wajib MFA: pendaftaran dilakukan pada login pertama (SPEC.md §4.3).
        if ($user['global_role'] === 'superadmin') {
            $jawab = redirect()->to('/login/mfa-daftar');
            Pending::start($jawab, [
                'user_id'    => $user['id'],
                'next'       => $next,
                'tahap'      => 'daftar',
                'secret_enc' => base64_encode(Mfa::encrypt(Mfa::newSecret())),
            ]);

            return $jawab;
        }

        $jawab = redirect()->to(SignIn::target($user, $next));
        SignIn::complete($user, $this->request, $jawab, 'password');

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
