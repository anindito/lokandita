<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\Jendela;
use App\Libraries\Kurikulum;
use App\Libraries\Papan;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Papan peringkat untuk dosen (SPEC.md §3, §12): per kelas untuk siapa pun yang
 * melihat kelas itu (superadmin, pengampu, pengajar kelas tersebut), dan per mata
 * kuliah untuk superadmin dan pengampu. Dosen selalu melihat nama dan nomor induk;
 * penyamaran hanya berlaku di papan mahasiswa (RuangLatihan::papan).
 */
final class Peringkat extends BaseController
{
    public function kelas(int $kelasId, int $pertemuanId): ResponseInterface
    {
        // Jendela::satu memuat syarat lihatKelas: kelas di luar hak pengguna tidak terbaca.
        $j = (new Jendela($this->akses()))->satu($kelasId, $pertemuanId);
        if ($j === null) {
            return $this->tidakDitemukan('class');
        }
        $kelas = $this->akademik()->kelas($kelasId);

        return $this->halaman('papan_staf', [
            'judul'   => lang('Papan.judul') . ' — ' . $kelas['matkul_kode'] . ' ' . $kelas['name'],
            'lingkup' => 'kelas',
            'kelas'   => $kelas,
            'p'       => $j,
            'papan'   => Papan::bernama(Papan::kelas($pertemuanId, $kelasId)),
            'aktif'   => count(array_filter($this->akademik()->pendaftaranKelas($kelasId), static fn ($e) => $e['status'] === 'AKTIF')),
        ]);
    }

    public function matkul(int $pertemuanId): ResponseInterface
    {
        $p = (new Kurikulum($this->akses()))->pertemuan($pertemuanId);
        if ($p === null) {
            return $this->tidakDitemukan('meeting');
        }
        // Pengajar yang bukan pengampu melihat mata kuliahnya, tetapi papan lintas kelas bukan haknya.
        if ((int) $p['boleh_kelola'] !== 1) {
            return $this->terlarang();
        }
        $tahun = $this->akademik()->tahunAktif();

        return $this->halaman('papan_staf', [
            'judul'   => lang('Papan.judul') . ' — ' . $p['matkul_kode'] . ' ' . lang('Soal.pertemuan.ke', [(int) $p['number']]),
            'lingkup' => 'matkul',
            'kelas'   => null,
            'p'       => $p + ['pertemuan_id' => (int) $p['id']],
            'tahun'   => $tahun,
            'papan'   => $tahun === null ? [] : Papan::bernama(Papan::matkul($pertemuanId, (int) $tahun['id'])),
            'aktif'   => null,
        ]);
    }
}
