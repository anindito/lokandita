<?php

declare(strict_types=1);

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use Lokandita\Auth\UserDirectory;

/**
 * Satu kelas dengan mahasiswa aktifnya, untuk siapa pun yang boleh melihat kelas itu
 * (superadmin, pengampu mata kuliahnya, pengajarnya). Hanya membaca.
 */
final class Kelas extends BaseController
{
    public function lihat(string $id): ResponseInterface
    {
        $roster = $this->roster();
        $kelas  = $roster->kelas((int) $id);
        if ($kelas === null) {
            return $this->tidakDitemukan('class');
        }
        $pengajar  = $roster->pengajar([(int) $kelas['id']])[(int) $kelas['id']] ?? [];
        $mahasiswa = $roster->mahasiswaAktif((int) $kelas['id']);
        $orang     = UserDirectory::byIds(array_merge([(string) $kelas['owner_id']], $pengajar, $mahasiswa));

        // Urut nomor induk; akun yang tidak ditemukan di LokaAuth diletakkan di akhir.
        usort($mahasiswa, static fn (string $a, string $b): int => [! isset($orang[$a]), $orang[$a]['nomor_induk'] ?? '', $a] <=> [! isset($orang[$b]), $orang[$b]['nomor_induk'] ?? '', $b]);

        return $this->halaman('kelas_lihat', [
            'judul'     => $kelas['matkul_kode'] . ' ' . $kelas['name'],
            'kelas'     => $kelas,
            'pengajar'  => $pengajar,
            'mahasiswa' => $mahasiswa,
            'orang'     => $orang,
            // Sesi kelas ini (tahap U3): siapa pun yang melihat kelasnya boleh menjalankannya.
            'sesi'      => (new \App\Libraries\Sesi($this->akses()))->untukKelas((int) $kelas['id']),
        ]);
    }
}
