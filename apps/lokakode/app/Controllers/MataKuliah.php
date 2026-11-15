<?php

declare(strict_types=1);

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use Lokandita\Auth\UserDirectory;

/**
 * Mata kuliah (SPEC.md §5). Melihat: superadmin, pengampu, pengajar salah satu
 * kelasnya. Membuat dan mengubah: superadmin.
 */
final class MataKuliah extends BaseController
{
    public function index(): ResponseInterface
    {
        $matkul = $this->akademik()->daftarMatkul();

        return $this->halaman('matkul_daftar', [
            'judul'  => lang('Kode.matkul.judul'),
            'matkul' => $matkul,
            'orang'  => UserDirectory::byIds(array_column($matkul, 'owner_id')),
        ]);
    }

    public function lihat(int $id): ResponseInterface
    {
        $akademik = $this->akademik();
        $matkul   = $akademik->matkul($id);
        if ($matkul === null) {
            return $this->tidakDitemukan('course');
        }

        // Tahun ajaran yang ditampilkan: ?tahun=…, bawaan tahun aktif.
        $semuaTahun = $akademik->semuaTahun();
        $tahun      = $akademik->tahun((int) $this->request->getGet('tahun')) ?? $akademik->tahunAktif();
        $kelas      = $tahun === null ? [] : $akademik->daftarKelas((int) $tahun['id'], $id);
        $pengajar   = $akademik->pengajar(array_map(static fn ($k) => (int) $k['id'], $kelas));

        $ids = [$matkul['owner_id']];
        foreach ($pengajar as $daftar) {
            $ids = array_merge($ids, $daftar);
        }

        return $this->halaman('matkul_lihat', [
            'judul'      => $matkul['code'] . ' — ' . $matkul['name'],
            'matkul'     => $matkul,
            'tahun'      => $tahun,
            'semuaTahun' => $semuaTahun,
            'kelas'      => $kelas,
            'pengajar'   => $pengajar,
            'orang'      => UserDirectory::byIds($ids),
        ]);
    }
}
