<?php

declare(strict_types=1);

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;
use Lokandita\Auth\Locale;
use Lokandita\Auth\Publik;
use Lokandita\Auth\Seb\Seb;

/**
 * Halaman Safe Exam Browser LokaUjian yang terbuka tanpa sesi (KONTRAK.md §4, §23): petunjuk dan
 * pemeriksaan header. Tidak membaca basis data dan tidak menyebut satu ujian pun — berkas .seb
 * tiap ujian diunduh mahasiswa dari berandanya sendiri, sesudah masuk.
 */
final class SebPublik extends Controller
{
    private const UNDUH_SEB = 'https://safeexambrowser.org/download_en.html';

    public function petunjuk(): ResponseInterface
    {
        Locale::apply($this->request);

        return $this->response->setBody(view('seb_petunjuk', Publik::data(lang('SebUjian.petunjuk.judul'), lang('SebUjian.petunjuk.deskripsi'), [], false) + [
            'unduhSeb'     => self::UNDUH_SEB,
            'diberlakukan' => Seb::diberlakukan(),
        ]));
    }

    /**
     * Apakah kedua header SEB tiba pada permintaan ini. Tidak mencocokkannya dengan ujian mana
     * pun: itu dilakukan saat ujian dibuka, dan alasannya tertulis di halaman penolakannya.
     */
    public function periksa(): ResponseInterface
    {
        Locale::apply($this->request);
        $konfig   = trim($this->request->getHeaderLine(Seb::HEADER_KONFIG)) !== '';
        $peramban = trim($this->request->getHeaderLine(Seb::HEADER_PERAMBAN)) !== '';

        return $this->response->setHeader('Cache-Control', 'no-store')->setBody(view('seb_periksa', [
            'judul'        => lang('SebUjian.periksa.judul'),
            'konfig'       => $konfig,
            'peramban'     => $peramban,
            'diberlakukan' => Seb::diberlakukan(),
        ]));
    }
}
