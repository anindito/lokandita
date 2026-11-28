<?php

declare(strict_types=1);

namespace App\Controllers\Admin\Roster;

use App\Libraries\Roster;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Ringkasan roster (SPEC.md §5): tahun ajaran aktif, jumlah kelas dan mahasiswa aktif,
 * dan kelas yang belum punya pengajar.
 */
final class Ringkasan extends Dasar
{
    public function index(): ResponseInterface
    {
        $tahun = Roster::tahunAktif();
        $kelas = $tahun === null ? [] : Roster::daftarKelas((int) $tahun['id']);

        return $this->halaman('admin/roster/ringkasan', [
            'judul'    => lang('Roster.judul'),
            'tahun'    => $tahun,
            'kelas'    => $kelas,
            'pengajar' => Roster::pengajar(array_map(static fn ($k) => (int) $k['id'], $kelas)),
            'ringkas'  => [
                'kelas'         => count($kelas),
                'mahasiswa'     => array_sum(array_map(static fn ($k) => (int) $k['jumlah_aktif'], $kelas)),
                'tanpaPengajar' => count(array_filter($kelas, static fn ($k) => (int) $k['jumlah_pengajar'] === 0 && $k['archived_at'] === null)),
            ],
            'pesan' => [],
        ]);
    }
}
