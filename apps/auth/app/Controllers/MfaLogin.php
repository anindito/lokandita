<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\Audit;
use App\Libraries\LoginGuard;
use App\Libraries\Mfa;
use App\Libraries\Pending;
use App\Libraries\SignIn;
use App\Libraries\Users;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Langkah kedua login: kode TOTP, atau pendaftaran TOTP pada login pertama superadmin (SPEC.md §4.3).
 */
final class MfaLogin extends BaseController
{
    /**
     * @var array<string, mixed>|null
     */
    private ?array $tunda = null;

    /**
     * @var array<string, mixed>|null
     */
    private ?array $user = null;

    /**
     * Memuat login yang tertunda. Mengembalikan redirect bila tidak ada atau akun tidak lagi boleh masuk.
     */
    private function muat(string $tahap): ?ResponseInterface
    {
        $this->tunda = Pending::get();
        $this->user  = $this->tunda === null ? null : Users::find((string) $this->tunda['user_id']);

        if ($this->tunda === null || $this->user === null || $this->tunda['tahap'] !== $tahap
            || $this->user['status'] !== 'active' || LoginGuard::isLocked($this->user)) {
            $jawab = redirect()->to('/login');
            Pending::clear($jawab);

            return $jawab;
        }

        return null;
    }

    public function formKode(): ResponseInterface
    {
        return $this->muat('kode') ?? $this->tampilkanKode([]);
    }

    public function kirimKode(): ResponseInterface
    {
        if (($tolak = $this->muat('kode')) !== null) {
            return $tolak;
        }
        $user   = $this->user;
        $kode   = trim((string) $this->request->getPost('kode'));
        $secret = Mfa::decrypt((string) $user['mfa_secret_enc']);

        $cara = null;
        if ($secret !== null && Mfa::verifyCode((string) $user['id'], $secret, $kode)) {
            $cara = 'totp';
        } elseif (Mfa::useBackupCode((string) $user['id'], $kode)) {
            $cara = 'backup_code';
        }

        if ($cara === null) {
            LoginGuard::recordFailure($user);
            db_connect()->table('login_attempts')->insert([
                'nomor_induk' => $user['nomor_induk'], 'ip' => $this->request->getIPAddress(), 'success' => 0, 'created_at' => \App\Libraries\Clock::now(),
            ]);
            Audit::log('login.failed', null, $user['id'], ['reason' => 'wrong_totp', 'nomor_induk' => $user['nomor_induk']]);

            return $this->tampilkanKode($this->galat(lang('Auth.loginGagal')), 422);
        }

        if ($cara === 'backup_code') {
            Audit::log('mfa.backup_code_used', $user['id'], $user['id']);
        }

        $jawab = redirect()->to(SignIn::target($user, $this->tunda['next'] ?? null));
        Pending::clear($jawab);
        SignIn::complete($user, $this->request, $jawab, $cara);

        return $jawab;
    }

    public function formDaftar(): ResponseInterface
    {
        return $this->muat('daftar') ?? $this->tampilkanDaftar([]);
    }

    public function kirimDaftar(): ResponseInterface
    {
        if (($tolak = $this->muat('daftar')) !== null) {
            return $tolak;
        }
        $user   = $this->user;
        $secret = Mfa::decrypt((string) base64_decode((string) $this->tunda['secret_enc'], true));

        if ($secret === null || ! Mfa::verifyCode((string) $user['id'], $secret, (string) $this->request->getPost('kode'))) {
            Audit::log('mfa.enroll_failed', $user['id'], $user['id']);

            return $this->tampilkanDaftar($this->galat(lang('Auth.mfa.kodeSalah')), 422);
        }

        Mfa::enable((string) $user['id'], $secret);
        $kodeCadangan = Mfa::regenerateBackupCodes((string) $user['id']);
        Audit::log('mfa.enrolled', $user['id'], $user['id']);

        // Kode cadangan ditampilkan sekali, di jawaban ini saja.
        $jawab = $this->halaman('mfa_cadangan', [
            'judul'  => lang('Auth.mfa.cadanganJudul'),
            'kode'   => $kodeCadangan,
            'lanjut' => SignIn::target($user, $this->tunda['next'] ?? null),
        ])->setHeader('Cache-Control', 'no-store');
        Pending::clear($jawab);
        SignIn::complete($user, $this->request, $jawab, 'totp_enroll');

        return $jawab;
    }

    /**
     * @param list<array{jenis: string, teks: string}> $pesan
     */
    private function tampilkanKode(array $pesan, int $status = 200): ResponseInterface
    {
        return $this->halaman('mfa_kode', ['judul' => lang('Auth.mfa.kodeJudul'), 'pesan' => $pesan], $status)
            ->setHeader('Cache-Control', 'no-store');
    }

    /**
     * @param list<array{jenis: string, teks: string}> $pesan
     */
    private function tampilkanDaftar(array $pesan, int $status = 200): ResponseInterface
    {
        $secret = (string) Mfa::decrypt((string) base64_decode((string) $this->tunda['secret_enc'], true));

        return $this->halaman('mfa_daftar', [
            'judul'  => lang('Auth.mfa.daftarJudul'),
            'secret' => $secret,
            'qr'     => Mfa::qrSvg(Mfa::provisioningUri($secret, (string) $this->user['nomor_induk'])),
            'pesan'  => $pesan,
        ], $status)->setHeader('Cache-Control', 'no-store');
    }
}
