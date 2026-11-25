<?php

declare(strict_types=1);

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;
use Lokandita\Auth\Locale;
use Lokandita\Auth\Publik;

/**
 * Halaman depan LokaUjian (KONTRAK.md §4 "Public routes"): satu kartu dengan tombol Masuk.
 * Tanpa identitas dan tanpa basis data; isinya hanya teks dari berkas bahasa. Tidak untuk
 * diindeks: LokaUjian tidak punya halaman untuk dicari.
 */
final class Depan extends Controller
{
    public function index(): ResponseInterface
    {
        // Pembawa cookie sesi dibawa ke beranda; nilainya tidak dibaca di sini.
        $alih = Publik::keBerandaBilaMembawaSesi();
        if ($alih !== null) {
            return $alih;
        }
        Locale::apply($this->request);

        return $this->response->setBody(view('depan', Publik::data(lang('Ujian.depan.judul'), lang('Ujian.depan.pembuka'), [], false)));
    }
}
