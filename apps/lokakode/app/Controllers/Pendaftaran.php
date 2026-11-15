<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\Pendaftaran as Layanan;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Pendaftaran mahasiswa ke kelas (SPEC.md §5): superadmin dan pengampu.
 * Setiap tindakan mulai dari kelasUntukDiubah(); id pendaftaran dari peramban
 * hanya berlaku bila pendaftaran itu memang berada di kelas tersebut.
 */
final class Pendaftaran extends BaseController
{
    private const BATAS_BERKAS = 1024 * 1024;

    /**
     * Langkah 1: pratinjau. Menerima nomor induk dari kolom teks `nomor`
     * dan/atau berkas CSV `berkas`. Tidak menyimpan apa pun.
     */
    public function pratinjau(int $kelasId): ResponseInterface
    {
        $kelas = $this->kelasUntukDiubah($kelasId);
        if ($kelas instanceof ResponseInterface) {
            return $kelas;
        }
        if ($kelas['archived_at'] !== null) {
            return $this->tolakIsian($kelasId, 'kelasArsip');
        }

        $teks   = (string) $this->request->getPost('nomor');
        $berkas = $this->request->getFile('berkas');
        if ($berkas !== null && $berkas->getError() !== UPLOAD_ERR_NO_FILE) {
            if (! $berkas->isValid() || strtolower($berkas->getClientExtension()) !== 'csv' || $berkas->getSize() > self::BATAS_BERKAS) {
                return $this->tolakIsian($kelasId, 'berkasCsv');
            }
            $teks .= "\n" . (string) file_get_contents($berkas->getTempName());
        }

        [$nomor, $salah] = Layanan::bacaNomor($teks);
        if ($nomor === [] && $salah === []) {
            return $this->tolakIsian($kelasId, 'nomorKosong');
        }
        if (count($nomor) > Layanan::BATAS_BARIS) {
            return $this->tolakIsian($kelasId, 'terlaluBanyak');
        }

        $golongan = Layanan::golongkan($kelas, $nomor);
        $aktif    = (int) db_connect()->query("SELECT COUNT(*) AS n FROM enrollments WHERE class_id = ? AND status = 'AKTIF'", [$kelasId])->getRow()->n;
        $akan     = count($golongan['siap']) + count($golongan['aktifkanLagi']);

        return $this->halaman('daftar_pratinjau', [
            'judul'    => lang('Kode.daftar.pratinjau'),
            'kelas'    => $kelas,
            'golongan' => $golongan,
            'salah'    => $salah,
            'akan'     => $akan,
            'melebihi' => $kelas['capacity'] !== null && $aktif + $akan > (int) $kelas['capacity'] ? $aktif + $akan : null,
        ]);
    }

    /**
     * Langkah 2: menjalankan. Menerima `nomor[]` dari halaman pratinjau dan
     * menggolongkannya ulang di server.
     */
    public function jalankan(int $kelasId): ResponseInterface
    {
        $kelas = $this->kelasUntukDiubah($kelasId);
        if ($kelas instanceof ResponseInterface) {
            return $kelas;
        }
        if ($kelas['archived_at'] !== null) {
            return $this->tolakIsian($kelasId, 'kelasArsip');
        }
        $kiriman = $this->request->getPost('nomor');
        [$nomor] = Layanan::bacaNomor(implode("\n", is_array($kiriman) ? array_map('strval', $kiriman) : []));
        if ($nomor === [] || count($nomor) > Layanan::BATAS_BARIS) {
            return $this->tolakIsian($kelasId, 'nomorKosong');
        }

        $hasil = Layanan::jalankan($kelas, $nomor, $this->akses()->identitas()->userId);

        return redirect()->to('/kelas/' . $kelasId . '?pesan=didaftarkan&n=' . ($hasil['didaftarkan'] + $hasil['diaktifkan']));
    }

    /**
     * Menandai pendaftaran terpilih (`ids[]`) sebagai mengundurkan diri.
     */
    public function mundur(int $kelasId): ResponseInterface
    {
        $kelas = $this->kelasUntukDiubah($kelasId);
        if ($kelas instanceof ResponseInterface) {
            return $kelas;
        }
        $n = Layanan::ubahStatus($kelas, (array) $this->request->getPost('ids'), 'AKTIF', 'MENGUNDURKAN_DIRI', 'pendaftaran.mundur');

        return redirect()->to('/kelas/' . $kelasId . '?pesan=' . ($n > 0 ? 'mundur' : 'tidak-ada-pilihan') . '&n=' . $n);
    }

    private function tolakIsian(int $kelasId, string $kunci): ResponseInterface
    {
        return redirect()->to('/kelas/' . $kelasId . '?pesan=' . $kunci);
    }
}
