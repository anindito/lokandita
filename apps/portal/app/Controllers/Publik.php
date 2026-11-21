<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\Aplikasi;
use App\Libraries\Foto;
use App\Libraries\Teks;
use CodeIgniter\HTTP\ResponseInterface;
use Lokandita\Auth\Publik as HalamanPublik;

/**
 * Halaman publik Portal (SPEC.md §15). Tidak membaca sesi: isinya sama untuk semua orang
 * dan boleh diindeks mesin pencari. Teksnya dari App\Libraries\Teks — berkas bahasa, atau
 * pengganti yang ditulis superadmin (KONTRAK.md §15).
 */
final class Publik extends BaseController
{
    /**
     * Halaman teks. Kontak bukan halaman lagi: sejak 2026-11-21 isinya menjadi bagian
     * halaman Tentang (keputusan pemilik), dan /kontak dialihkan ke /tentang.
     */
    private const HALAMAN = ['tentang', 'privasi', 'ketentuan'];

    public function depan(): ResponseInterface
    {
        // Pembawa cookie sesi dibawa ke beranda; nilainya tidak dibaca di sini.
        $alih = HalamanPublik::keBerandaBilaMembawaSesi();
        if ($alih !== null) {
            return $alih;
        }

        $bahasa = $this->request->getLocale();
        $t      = Teks::ambil('depan', $bahasa);

        $foto   = Foto::tayang($bahasa);

        return $this->tampil('publik/depan', $t['judul'], $t['deskripsi'], [
            't'          => $t,
            'ket'        => Teks::ambil('aplikasi', $bahasa),
            'aplikasi'   => Aplikasi::daftar(),
            'foto'       => $foto,
            'bahasa'     => $bahasa,
            // Kredit foto di kaki halaman berasal dari isian foto yang sedang tayang.
            'kreditFoto' => $foto['sumber'],
        ]);
    }

    public function tentang(): ResponseInterface
    {
        return $this->teks('tentang');
    }

    /**
     * Alamat lama halaman Kontak: tautan dan penanda yang sudah beredar tetap sampai ke isinya.
     */
    public function kontak(): ResponseInterface
    {
        return redirect()->to('/tentang', 301);
    }

    public function privasi(): ResponseInterface
    {
        return $this->teks('privasi');
    }

    public function ketentuan(): ResponseInterface
    {
        return $this->teks('ketentuan');
    }

    /**
     * GET /sitemap.xml — hanya halaman publik.
     */
    public function peta(): ResponseInterface
    {
        $akar = rtrim((string) getenv('APP_URL'), '/');
        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach (array_merge([''], self::HALAMAN) as $h) {
            $xml .= '  <url><loc>' . esc($akar . '/' . $h) . '</loc></url>' . "\n";
        }

        return $this->response->setContentType('application/xml')->setBody($xml . '</urlset>' . "\n");
    }

    /**
     * GET /robots.txt — halaman publik boleh diindeks; halaman di belakang sesi tidak.
     */
    public function robot(): ResponseInterface
    {
        $baris = ['User-agent: *'];
        foreach (['/beranda', '/data-saya', '/bahasa/', '/keluar', '/sesi/', '/preferensi', '/healthz'] as $j) {
            $baris[] = 'Disallow: ' . $j;
        }
        $baris[] = '';
        $baris[] = 'Sitemap: ' . rtrim((string) getenv('APP_URL'), '/') . '/sitemap.xml';

        return $this->response->setContentType('text/plain')->setBody(implode("\n", $baris) . "\n");
    }

    private function teks(string $kunci): ResponseInterface
    {
        $halaman = Teks::ambil($kunci, $this->request->getLocale());

        return $this->tampil('publik/teks', $halaman['judul'], $halaman['deskripsi'], ['halaman' => $halaman]);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function tampil(string $view, string $judul, string $deskripsi, array $data): ResponseInterface
    {
        $nav = [];
        foreach (self::HALAMAN as $h) {
            $nav[] = ['href' => '/' . $h, 'teks' => lang("Portal.nav.{$h}")];
        }

        return $this->response->setBody(view($view, $data + HalamanPublik::data($judul, $deskripsi, $nav) + [
            'kontak' => (string) getenv('CONTACT_EMAIL'),
        ]));
    }
}
