<?php

declare(strict_types=1);

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use Lokandita\Auth\Aplikasi;
use Lokandita\Auth\UserDirectory;

/**
 * Kelas (SPEC.md §5). Melihat: superadmin, pengampu mata kuliahnya, pengajar
 * kelas itu. Mengelola: superadmin dan pengampu.
 */
final class Kelas extends BaseController
{
    public function lihat(int $id): ResponseInterface
    {
        $akademik = $this->akademik();
        $kelas    = $akademik->kelas($id);
        if ($kelas === null) {
            return $this->tidakDitemukan('class');
        }

        $pengajar    = $akademik->pengajar([$id])[$id] ?? [];
        $pendaftaran = $akademik->pendaftaranKelas($id);
        $idMahasiswa = array_column($pendaftaran, 'user_id');
        $orang       = UserDirectory::byIds(array_merge([$kelas['owner_id']], $pengajar, $idMahasiswa));

        // Urut menurut nomor induk; yang tidak lagi ada di direktori di akhir.
        usort($pendaftaran, static fn ($a, $b) => strcmp($orang[$a['user_id']]['nomor_induk'] ?? '~', $orang[$b['user_id']]['nomor_induk'] ?? '~'));

        return $this->halaman('kelas_lihat', [
            'judul'       => $kelas['matkul_kode'] . ' ' . $kelas['name'],
            'kelas'       => $kelas,
            'pengajar'    => $pengajar,
            'pendaftaran' => $pendaftaran,
            'orang'       => $orang,
            'berakses'    => UserDirectory::withAccess($idMahasiswa, Aplikasi::kode()),
        ]);
    }
}
