<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\Seb\Seb;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Panel SEB satu mata kuliah (SPEC.md §13.1): superadmin dan pengampunya. Pengajar yang
 * melihat mata kuliah itu mendapat 403; yang tidak melihatnya 404.
 */
final class SebKelola extends BaseController
{
    private const PESAN = [
        'dibuat'   => ['sukses', 'Seb.kelola.pesanDibuat'],
        'dirotasi' => ['sukses', 'Seb.kelola.pesanDirotasi'],
        'kunci'    => ['sukses', 'Seb.kelola.pesanKunci'],
    ];

    public function lihat(int $matkulId): ResponseInterface
    {
        $matkul = $this->matkulUntukDikelola($matkulId);

        return $matkul instanceof ResponseInterface ? $matkul : $this->tampil($matkul, [], '');
    }

    /**
     * Membuat konfigurasi pertama, atau merotasinya (kunci lama tidak berlaku lagi).
     */
    public function simpan(int $matkulId): ResponseInterface
    {
        $matkul = $this->matkulUntukDikelola($matkulId);
        if ($matkul instanceof ResponseInterface) {
            return $matkul;
        }
        $sandi = (string) $this->request->getPost('sandi_keluar');
        if (mb_strlen($sandi) < Seb::SANDI_KELUAR_MIN || mb_strlen($sandi) > 200) {
            return $this->tampil($matkul, ['sandi_keluar' => 'sandiKeluar'], '', 422);
        }
        $versi = Seb::buat($matkulId, $sandi);

        return redirect()->to('/mata-kuliah/' . $matkulId . '/seb?pesan=' . ($versi === 1 ? 'dibuat' : 'dirotasi'));
    }

    /**
     * Mendaftarkan Browser Exam Key, satu per baris; isi kotak menggantikan seluruh daftar.
     */
    public function kunci(int $matkulId): ResponseInterface
    {
        $matkul = $this->matkulUntukDikelola($matkulId);
        if ($matkul instanceof ResponseInterface) {
            return $matkul;
        }
        $isian = (string) $this->request->getPost('kunci');
        if (Seb::aktif($matkulId) === null) {
            return $this->tampil($matkul, ['kunci' => 'belumAda'], $isian, 422);
        }
        $kunci = [];
        foreach (preg_split('/\R/', $isian) ?: [] as $baris) {
            $baris = strtolower(trim($baris));
            if ($baris === '') {
                continue;
            }
            if (preg_match('/^[0-9a-f]{64}$/', $baris) !== 1) {
                return $this->tampil($matkul, ['kunci' => 'kunciBentuk'], $isian, 422);
            }
            $kunci[] = $baris;
        }
        if (count($kunci) > 20) {
            return $this->tampil($matkul, ['kunci' => 'kunciBanyak'], $isian, 422);
        }
        Seb::daftarkanKunci($matkulId, $kunci);

        return redirect()->to('/mata-kuliah/' . $matkulId . '/seb?pesan=kunci');
    }

    /**
     * @return array<string, mixed>|ResponseInterface
     */
    private function matkulUntukDikelola(int $id): array|ResponseInterface
    {
        $matkul = $this->akademik()->matkul($id);
        if ($matkul === null) {
            return $this->tidakDitemukan('course');
        }

        return (int) $matkul['boleh_kelola'] === 1 ? $matkul : $this->terlarang();
    }

    /**
     * @param array<string, mixed>  $matkul
     * @param array<string, string> $galat  kolom => kunci Seb.galat.*
     */
    private function tampil(array $matkul, array $galat, string $isianKunci, int $status = 200): ResponseInterface
    {
        $aktif = Seb::aktif((int) $matkul['id']);
        $kunci = $aktif === null ? [] : Seb::kunciTerdaftar((int) $aktif['id']);

        return $this->halaman('seb_kelola', [
            'judul'       => lang('Seb.kelola.judul'),
            'matkul'      => $matkul,
            'aktif'       => $aktif,
            'kunci'       => $kunci,
            'isianKunci'  => $galat === [] ? implode("\n", $kunci) : $isianKunci,
            'galat'       => $galat,
            'pesan'       => $this->pesanDariQuery(self::PESAN),
            'diberlakukan' => Seb::diberlakukan(),
            'asal'        => Seb::asal(),
        ], $status);
    }
}
