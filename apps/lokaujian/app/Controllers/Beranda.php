<?php

declare(strict_types=1);

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use Lokandita\Auth\UserDirectory;

/**
 * Beranda: kelas yang boleh dilihat staf pada tahun ajaran aktif; untuk mahasiswa daftar
 * ujiannya (belum ada sampai tahap U3) dan kelas-kelasnya.
 */
final class Beranda extends BaseController
{
    public function index(): ResponseInterface
    {
        $roster = $this->roster();

        if (! $this->akses()->staf()) {
            return $this->halaman('beranda_mahasiswa', [
                'judul' => lang('Lokandita.beranda'),
                'kelas' => $roster->kelasSaya(),
            ]);
        }

        $tahun    = $roster->tahunAktif();
        $kelas    = $tahun === null ? [] : $roster->daftarKelas((int) $tahun['id']);
        $pengajar = $roster->pengajar(array_map(static fn ($k) => (int) $k['id'], $kelas));

        return $this->halaman('beranda_staf', [
            'judul'    => lang('Lokandita.beranda'),
            'tahun'    => $tahun,
            'kelas'    => $kelas,
            'pengajar' => $pengajar,
            // Nama pengajar dibaca dari direktori LokaAuth; tidak disalin.
            'orang'    => UserDirectory::byIds(array_merge([], ...array_values($pengajar))),
        ]);
    }
}
