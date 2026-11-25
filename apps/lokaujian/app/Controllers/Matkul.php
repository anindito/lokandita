<?php

declare(strict_types=1);

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use Lokandita\Auth\UserDirectory;

/**
 * Mata kuliah yang boleh dilihat staf (SPEC-LOKAUJIAN.md §3, §4). Hanya membaca: mata kuliah
 * dan kelas dikelola di tempatnya sendiri, bukan di LokaUjian.
 */
final class Matkul extends BaseController
{
    public function daftar(): ResponseInterface
    {
        $matkul = $this->roster()->daftarMatkul();

        return $this->halaman('matkul_daftar', [
            'judul'  => lang('Ujian.matkul.judul'),
            'matkul' => $matkul,
            'orang'  => UserDirectory::byIds(array_map('strval', array_column($matkul, 'owner_id'))),
        ]);
    }

    public function lihat(string $id): ResponseInterface
    {
        $roster = $this->roster();
        $matkul = $roster->matkul((int) $id);
        if ($matkul === null) {
            return $this->tidakDitemukan('course');
        }
        $tahun    = $roster->tahunAktif();
        $kelas    = $tahun === null ? [] : $roster->daftarKelas((int) $tahun['id'], (int) $matkul['id']);
        $pengajar = $roster->pengajar(array_map(static fn ($k) => (int) $k['id'], $kelas));

        return $this->halaman('matkul_lihat', [
            'judul'    => $matkul['code'] . ' — ' . $matkul['name'],
            'matkul'   => $matkul,
            'tahun'    => $tahun,
            'kelas'    => $kelas,
            'pengajar' => $pengajar,
            'orang'    => UserDirectory::byIds(array_merge([(string) $matkul['owner_id']], ...array_values($pengajar))),
        ]);
    }
}
