<?php

declare(strict_types=1);

namespace Lokandita\Auth\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;
use Lokandita\Auth\AuthClient;
use Lokandita\Auth\Current;
use Lokandita\Auth\Locale;
use Lokandita\Auth\Log;
use Lokandita\Auth\SessionReader;

/**
 * Tiga aksi menu akun di aplikasi klien (KONTRAK.md §4): keluar, perpanjang sesi, preferensi.
 * Semuanya POST dengan CSRF dan diteruskan ke LokaAuth lewat API internal.
 */
final class Akun extends Controller
{
    private function sid(): string
    {
        return (string) ($_COOKIE[SessionReader::COOKIE] ?? '');
    }

    /**
     * POST /keluar — sesi dicabut di LokaAuth, lalu peramban dibawa ke LokaAuth yang menghapus cookie.
     * Bila LokaAuth tidak bisa dihubungi, pengguna diberi tahu bahwa ia BELUM keluar.
     */
    public function keluar(): ResponseInterface
    {
        if (! (new AuthClient())->revoke($this->sid())) {
            Log::event('logout.failed', ['user' => Current::identity()?->userId]);

            return $this->response->setStatusCode(502)->setHeader('Cache-Control', 'no-store')
                ->setBody(view('Lokandita\Auth\Views\pesan', [
                    'judul' => lang('Lokandita.keluarGagalJudul'),
                    'teks'  => lang('Lokandita.keluarGagal'),
                ]));
        }

        return redirect()->to(rtrim((string) getenv('AUTH_PUBLIC_URL'), '/') . '/keluar-selesai');
    }

    /**
     * POST /sesi/perpanjang — tombol "Tetap masuk" dan tanda aktivitas. Menjawab JSON { sisa: detik }.
     */
    public function perpanjang(): ResponseInterface
    {
        $expires = (new AuthClient())->touch($this->sid());
        if ($expires === null) {
            return $this->response->setStatusCode(502)->setHeader('Cache-Control', 'no-store')->setJSON(['error' => 'tidak_bisa_diperpanjang']);
        }
        SessionReader::refreshCookie($this->response, $this->sid());

        return $this->response->setHeader('Cache-Control', 'no-store')->setJSON(['sisa' => max(0, (int) strtotime($expires) - time())]);
    }

    /**
     * POST /preferensi — bahasa dan keluar-otomatis, disimpan di LokaAuth.
     */
    public function preferensi(): ResponseInterface
    {
        $prefs  = [];
        $locale = $this->request->getPost('locale');
        if (is_string($locale) && in_array($locale, Locale::DAFTAR, true)) {
            $prefs['locale'] = $locale;
        }
        $idle = $this->request->getPost('idle_logout');
        if ($idle === '0' || $idle === '1') {
            $prefs['idle_logout'] = $idle === '1';
        }
        if ($prefs !== []) {
            (new AuthClient())->prefs($this->sid(), $prefs);
        }

        return redirect()->to(self::kembali((string) $this->request->getPost('kembali')));
    }

    /**
     * Hanya jalur lokal yang diterima sebagai tujuan kembali.
     */
    public static function kembali(string $jalur): string
    {
        if ($jalur === '' || $jalur[0] !== '/' || str_starts_with($jalur, '//') || preg_match('/[\x00-\x20\\\\]/', $jalur) === 1) {
            return '/';
        }

        return $jalur;
    }
}
