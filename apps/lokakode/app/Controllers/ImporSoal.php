<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\Antrean;
use App\Libraries\BankSoal;
use App\Libraries\Soal\Impor;
use CodeIgniter\HTTP\ResponseInterface;
use Lokandita\Auth\UserDirectory;

/**
 * Unggah soal JSON (SPEC.md §8.3). Mengunggah dan melihat daftar: superadmin
 * saja (filter rute); pengampu dan pengajar mendapat 403 di halaman dan di
 * titik akhirnya. Halaman laporan satu impor juga dibuka untuk pengampu bila
 * impor itu adalah suntingan soal di mata kuliahnya sendiri.
 */
final class ImporSoal extends BaseController
{
    public function index(): ResponseInterface
    {
        return $this->daftar([]);
    }

    /**
     * @param list<array{jenis: string, teks: string}> $pesan
     */
    private function daftar(array $pesan, int $status = 200): ResponseInterface
    {
        $impor = (new BankSoal($this->akses()))->daftarImpor();

        return $this->halaman('impor_daftar', [
            'judul'  => lang('Soal.impor.judul'),
            'impor'  => $impor,
            'orang'  => UserDirectory::byIds(array_column($impor, 'actor_id')),
            'worker' => Antrean::denyut() !== null,
            'pesan'  => $pesan,
        ], $status);
    }

    public function unggah(): ResponseInterface
    {
        $galat  = null;
        $berkas = $this->request->getFile('berkas');
        if ($berkas === null || ! $berkas->isValid()) {
            // Berkas yang melewati batas PHP tiba tanpa isi; katakan batasnya.
            $galat = 'unggahKosong';
        } elseif (strtolower($berkas->getClientExtension()) !== 'json') {
            $galat = 'unggahBukanJson';
        } elseif ($berkas->getSize() > Impor::MAKS_BYTE) {
            $galat = 'unggahBesar';
        } elseif (Antrean::denyut() === null) {
            // Tanpa worker impor akan menunggu selamanya; lebih baik ditolak dengan jelas.
            $galat = 'judgeMati';
        }
        if ($galat !== null) {
            return $this->daftar([['jenis' => 'galat', 'teks' => lang('Soal.impor.galat.' . $galat)]], 422);
        }

        $isi = (string) file_get_contents($berkas->getTempName());
        $id  = (new Impor())->mulai($isi, $berkas->getClientName(), $this->akses()->identitas()->userId, 'web');

        return redirect()->to('/impor/' . $id);
    }

    public function lihat(string $id): ResponseInterface
    {
        if (! Antrean::uuidSah($id) || (new BankSoal($this->akses()))->impor($id) === null) {
            return $this->tidakDitemukan('import');
        }
        $impor = new Impor();
        $impor->majukan($id);
        $lap = $impor->laporan($id);

        $jawab = $this->halaman('impor_lihat', [
            'judul' => lang('Soal.impor.laporan') . ' — ' . $lap['file_name'],
            'lap'   => $lap,
            'orang' => UserDirectory::byIds([$lap['actor_id']]),
        ])->setHeader('Cache-Control', 'no-store');

        // Selama judge masih bekerja, peramban memuat ulang halaman ini tiap 2 detik
        // (kepala HTTP Refresh; tanpa JavaScript). Halaman juga punya tautan muat ulang.
        return $lap['status'] === 'MENUNGGU' ? $jawab->setHeader('Refresh', '2') : $jawab;
    }
}
