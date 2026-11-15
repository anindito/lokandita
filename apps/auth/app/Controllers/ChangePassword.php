<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\Audit;
use App\Libraries\NextUrl;
use App\Libraries\Passwords;
use App\Libraries\Sessions;
use App\Libraries\Users;
use CodeIgniter\HTTP\ResponseInterface;
use Lokandita\Auth\Current;

/**
 * Ganti kata sandi sendiri (SPEC.md §4.2). Wajib setelah login pertama dan setelah reset.
 */
final class ChangePassword extends BaseController
{
    public function form(): ResponseInterface
    {
        return $this->tampilkan(NextUrl::safe($this->request->getGet('next')), []);
    }

    public function submit(): ResponseInterface
    {
        $identitas = Current::identity();
        $user      = Users::find($identitas->userId);
        $next      = NextUrl::safe($this->request->getPost('next'));
        $lama      = (string) $this->request->getPost('password_lama');
        $baru      = (string) $this->request->getPost('password_baru');
        $ulang     = (string) $this->request->getPost('password_ulang');

        $galat = [];
        if (! password_verify($lama, (string) $user['password_hash'])) {
            $galat['password_lama'] = [lang('Auth.sandi.lamaSalah')];
        }
        foreach (Passwords::problems($baru, $user) as $kunci) {
            $galat['password_baru'][] = lang('Auth.sandi.' . $kunci, [Passwords::MIN]);
        }
        if ($baru !== $ulang) {
            $galat['password_ulang'] = [lang('Auth.sandi.tidakSama')];
        }
        if ($galat !== []) {
            Audit::log('password.change_rejected', $identitas->userId, $identitas->userId, ['fields' => implode(',', array_keys($galat))]);

            return $this->tampilkan($next, $galat, 422);
        }

        Passwords::set($identitas->userId, $baru, false);
        $dicabut = Sessions::revokeAllFor($identitas->userId, Sessions::tokenFrom($this->request));
        Audit::log('password.changed', $identitas->userId, $identitas->userId, ['other_sessions_revoked' => $dicabut]);

        return redirect()->to($next ?? '/?pesan=sandi-diganti');
    }

    /**
     * @param array<string, list<string>> $galat
     */
    private function tampilkan(?string $next, array $galat, int $status = 200): ResponseInterface
    {
        $identitas = Current::identity();

        return $this->halaman('ganti_password', [
            'judul' => lang('Auth.gantiSandi'),
            'next'  => $next,
            'galat' => $galat,
            'wajib' => $identitas->mustChangePassword,
            'pesan' => $galat === [] ? [] : $this->galat(lang('Auth.periksaIsian')),
        ], $status);
    }
}
