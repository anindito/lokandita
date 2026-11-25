<?php

declare(strict_types=1);

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

/**
 * Beranda: kelas yang boleh dilihat staf; daftar ujian (masih kosong) untuk mahasiswa.
 */
final class Beranda extends BaseController
{
    public function index(): ResponseInterface
    {
        $judul = lang('Lokandita.beranda');

        return $this->akses()->staf()
            ? $this->halaman('beranda_staf', ['judul' => $judul])
            : $this->halaman('beranda_mahasiswa', ['judul' => $judul]);
    }
}
