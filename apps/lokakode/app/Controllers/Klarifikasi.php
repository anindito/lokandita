<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\Analitik;
use App\Libraries\Klarifikasi as Pustaka;
use CodeIgniter\HTTP\ResponseInterface;
use Lokandita\Auth\UserDirectory;

/**
 * Klarifikasi soal untuk dosen (SPEC.md §14, KONTRAK.md §17): siapa pun yang melihat kelas
 * itu — superadmin, pengampu, pengajar kelas tersebut — membaca dan menjawab.
 * Sisi mahasiswa ada di RuangLatihan (halaman soal).
 */
final class Klarifikasi extends BaseController
{
    private const PESAN = ['dijawab' => ['sukses', 'Komunikasi.klarifikasi.tersimpan'], 'kosong' => ['galat', 'Komunikasi.klarifikasi.jawabanSalah']];

    public function index(int $kelasId): ResponseInterface
    {
        $kelas = $this->akademik()->kelas($kelasId);
        if ($kelas === null) {
            return $this->tidakDitemukan('class');
        }
        $pertemuan = (int) $this->request->getGet('pertemuan') > 0 ? (int) $this->request->getGet('pertemuan') : null;
        $daftar    = (new Pustaka($this->akses()))->kelas($kelasId, $pertemuan);
        $ids       = array_merge(array_column($daftar, 'user_id'), array_filter(array_column($daftar, 'answered_by')));
        $this->response->setHeader('Cache-Control', 'no-store');

        return $this->halaman('klarifikasi_kelas', [
            'judul'     => lang('Komunikasi.klarifikasi.judul') . ' — ' . $kelas['matkul_kode'] . ' ' . $kelas['name'],
            'kelas'     => $kelas,
            'daftar'    => $daftar,
            'pilih'     => $pertemuan,
            'pertemuan' => (new Analitik($this->akses()))->pertemuanKelas($kelasId),
            'orang'     => UserDirectory::byIds(array_values(array_unique($ids))),
            'bolehJawab' => $this->bolehMenulis($kelas),
            'pesan'     => $this->pesanDariQuery(self::PESAN),
        ]);
    }

    public function jawab(int $kelasId, int $id): ResponseInterface
    {
        $kelas = $this->akademik()->kelas($kelasId);
        if ($kelas === null) {
            return $this->tidakDitemukan('class');
        }
        $pustaka = new Pustaka($this->akses());
        $q       = $pustaka->satu($kelasId, $id);
        if ($q === null) {
            return $this->tidakDitemukan('clarification');
        }
        if (! $this->bolehMenulis($kelas)) {
            return $this->terlarang('Kode.galat.tahunArsip');
        }
        $jawaban = Pustaka::teks((string) $this->request->getPost('jawaban'));
        $tampil  = (string) $this->request->getPost('tampil');
        if ($jawaban === null || ! in_array($tampil, ['pribadi', 'kelas'], true)) {
            return redirect()->to('/kelas/' . $kelasId . '/klarifikasi?pesan=kosong#k' . $id);
        }
        $pustaka->jawab($q, $jawaban, $tampil === 'kelas');

        return redirect()->to('/kelas/' . $kelasId . '/klarifikasi?pesan=dijawab#k' . $id);
    }
}
