<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\Penilaian;
use App\Libraries\Ruang;
use CodeIgniter\HTTP\ResponseInterface;
use Lokandita\Auth\UserDirectory;

/**
 * Beranda: ringkasan untuk staf (SPEC.md §5), daftar kelas sendiri untuk mahasiswa.
 */
final class Beranda extends BaseController
{
    /** Tanpa sesi dan tanpa data: hanya pengalihan. */
    public function akar(): ResponseInterface
    {
        return redirect()->to('/beranda');
    }

    public function index(): ResponseInterface
    {
        $akademik = $this->akademik();

        if (! $this->akses()->staf()) {
            Penilaian::selesaikan($this->akses()->identitas()->userId);

            return $this->halaman('beranda_mahasiswa', [
                'judul'     => lang('Lokandita.beranda'),
                'kelas'     => $akademik->kelasSaya(),
                'pertemuan' => (new Ruang($this->akses()))->daftarPertemuan(),
            ]);
        }

        $tahun    = $akademik->tahunAktif();
        $kelas    = $tahun === null ? [] : $akademik->daftarKelas((int) $tahun['id']);
        $pengajar = $akademik->pengajar(array_map(static fn ($k) => (int) $k['id'], $kelas));

        // Nama pengampu dan pengajar dibaca dari direktori LokaAuth.
        $ids = array_column($kelas, 'owner_id');
        foreach ($pengajar as $daftar) {
            $ids = array_merge($ids, $daftar);
        }

        return $this->halaman('beranda_staf', [
            'judul'    => lang('Lokandita.beranda'),
            'tahun'    => $tahun,
            'kelas'    => $kelas,
            'pengajar' => $pengajar,
            'orang'    => UserDirectory::byIds($ids),
            'ringkas'  => [
                'kelas'         => count($kelas),
                'mahasiswa'     => array_sum(array_map(static fn ($k) => (int) $k['jumlah_aktif'], $kelas)),
                'tanpaPengajar' => count(array_filter($kelas, static fn ($k) => (int) $k['jumlah_pengajar'] === 0 && $k['archived_at'] === null)),
            ],
        ]);
    }
}
