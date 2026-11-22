<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\Notifikasi as Pustaka;
use CodeIgniter\HTTP\ResponseInterface;
use Lokandita\Auth\UserDirectory;

/**
 * Pemberitahuan mahasiswa itu sendiri (SPEC.md §14, KONTRAK.md §17). Tidak ada rute yang
 * menerima id: semuanya terikat pada pengguna sesi ini.
 */
final class Notifikasi extends BaseController
{
    public function index(): ResponseInterface
    {
        $saya = $this->akses()->identitas()->userId;
        Pustaka::segarkan($saya, true);
        $daftar = Pustaka::daftar($saya);
        // Nama dosen yang mereset kata sandi.
        $oleh = array_values(array_unique(array_filter(array_map(static fn (array $n): string => (string) ($n['data']['oleh'] ?? ''), $daftar))));

        return $this->halaman('notifikasi', [
            'judul'  => lang('Komunikasi.notifikasi.judul'),
            'daftar' => $daftar,
            'orang'  => $oleh === [] ? [] : UserDirectory::byIds($oleh),
        ]);
    }

    public function baca(): ResponseInterface
    {
        Pustaka::tandaiDibaca($this->akses()->identitas()->userId);

        return redirect()->to('/notifikasi');
    }
}
