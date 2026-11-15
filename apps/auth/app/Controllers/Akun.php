<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\Audit;
use App\Libraries\Clock;
use App\Libraries\Sessions;
use CodeIgniter\HTTP\ResponseInterface;
use Lokandita\Auth\Controllers\Akun as AkunKlien;
use Lokandita\Auth\Current;
use Lokandita\Auth\Locale;

/**
 * Aksi menu akun di LokaAuth sendiri. Alamat dan bentuknya sama dengan di
 * aplikasi klien (KONTRAK.md §4), tetapi di sini langsung ke basis data.
 */
final class Akun extends BaseController
{
    /**
     * POST /sesi/perpanjang — menjawab JSON { sisa: detik }.
     */
    public function perpanjang(): ResponseInterface
    {
        $token   = Sessions::tokenFrom($this->request);
        $expires = Sessions::touch($token);
        if ($expires === null) {
            return $this->response->setStatusCode(502)->setHeader('Cache-Control', 'no-store')->setJSON(['error' => 'tidak_bisa_diperpanjang']);
        }
        Sessions::setCookie($this->response, $token);

        return $this->response->setHeader('Cache-Control', 'no-store')->setJSON(['sisa' => max(0, Clock::toUnix($expires) - time())]);
    }

    /**
     * POST /preferensi — bahasa dan keluar-otomatis.
     */
    public function preferensi(): ResponseInterface
    {
        $identitas = Current::identity();
        $ubah      = [];
        $locale    = $this->request->getPost('locale');
        if (is_string($locale) && in_array($locale, Locale::DAFTAR, true)) {
            $ubah['locale'] = $locale;
        }
        $idle = $this->request->getPost('idle_logout');
        // Keluar-otomatis wajib menyala untuk mahasiswa (SPEC.md §4.4).
        if ($idle === '1' || ($idle === '0' && $identitas->globalRole !== 'mahasiswa')) {
            $ubah['idle_logout'] = (int) $idle;
        }
        if ($ubah !== []) {
            db_connect()->table('users')->where('id', $identitas->userId)->update($ubah + ['updated_at' => Clock::now()]);
            Audit::log('prefs.changed', $identitas->userId, $identitas->userId, $ubah);
        }

        return redirect()->to(AkunKlien::kembali((string) $this->request->getPost('kembali')));
    }
}
