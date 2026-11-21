<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\Aplikasi;
use CodeIgniter\HTTP\ResponseInterface;
use Lokandita\Auth\Publik as HalamanPublik;

/**
 * Halaman publik Portal (SPEC.md §15). Tidak membaca sesi maupun basis data:
 * isinya sama untuk semua orang dan boleh diindeks mesin pencari.
 */
final class Publik extends BaseController
{
    /** Halaman teks: alamat => kunci di berkas bahasa Portal. */
    private const HALAMAN = ['tentang', 'kontak', 'privasi', 'ketentuan'];

    public function depan(): ResponseInterface
    {
        // Pembawa cookie sesi dibawa ke beranda; nilainya tidak dibaca di sini.
        $alih = HalamanPublik::keBerandaBilaMembawaSesi();
        if ($alih !== null) {
            return $alih;
        }

        return $this->tampil('publik/depan', lang('Portal.depan.judul'), lang('Portal.depan.deskripsi'), [
            'aplikasi' => Aplikasi::daftar(),
        ]);
    }

    public function tentang(): ResponseInterface
    {
        return $this->teks('tentang');
    }

    public function kontak(): ResponseInterface
    {
        return $this->teks('kontak');
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
        return $this->tampil('publik/teks', lang("Portal.{$kunci}.judul"), lang("Portal.{$kunci}.deskripsi"), [
            'halaman' => lang("Portal.{$kunci}"),
        ]);
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
