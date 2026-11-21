<?php

declare(strict_types=1);

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;
use Lokandita\Auth\Locale;
use Lokandita\Auth\Publik;

/**
 * Halaman depan publik LokaKode (SPEC.md §15). Sengaja tidak mewarisi BaseController:
 * halaman ini tidak punya identitas, tidak menyentuh basis data, dan isinya hanya teks
 * dari berkas bahasa — tidak ada soal, kelas atau data mahasiswa.
 */
final class Depan extends Controller
{
    /** Halaman unduh resmi Safe Exam Browser. Hanya tautan; tidak ada berkas yang dimuat dari sana. */
    private const SEB_UNDUH = 'https://safeexambrowser.org/download_en.html';

    public function index(): ResponseInterface
    {
        // Pembawa cookie sesi dibawa ke beranda; nilainya tidak dibaca di sini.
        $alih = Publik::keBerandaBilaMembawaSesi();
        if ($alih !== null) {
            return $alih;
        }
        Locale::apply($this->request);

        return $this->response->setBody(view('depan', Publik::data(lang('Depan.judul'), lang('Depan.deskripsi')) + [
            'sebUnduh' => self::SEB_UNDUH,
            'kontak'   => (string) getenv('CONTACT_EMAIL'),
            'portal'   => rtrim((string) getenv('PORTAL_URL'), '/'),
        ]));
    }
}
