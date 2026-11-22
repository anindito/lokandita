<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\Pengumuman as Pustaka;
use CodeIgniter\HTTP\ResponseInterface;
use Lokandita\Auth\UserDirectory;

/**
 * Pengumuman kelas (SPEC.md §14, KONTRAK.md §17). Dosen: siapa pun yang melihat kelas itu
 * menulis dan menarik. Mahasiswa: membaca pengumuman kelasnya sendiri.
 */
final class Pengumuman extends BaseController
{
    private const PESAN = [
        'dibuat'  => ['sukses', 'Komunikasi.pengumuman.dibuat'],
        'ditarik' => ['sukses', 'Komunikasi.pengumuman.ditarikPesan'],
    ];

    /**
     * @param array<string, mixed>  $kelas
     * @param array<string, string> $isian
     * @param list<array{jenis: string, teks: string}> $pesan
     */
    private function tampil(array $kelas, array $isian, array $pesan, int $status = 200): ResponseInterface
    {
        $daftar = (new Pustaka($this->akses()))->kelas((int) $kelas['id']);
        $ids    = array_merge(array_column($daftar, 'author_id'), array_filter(array_column($daftar, 'withdrawn_by')));

        return $this->halaman('pengumuman_kelas', [
            'judul'      => lang('Komunikasi.pengumuman.judul') . ' — ' . $kelas['matkul_kode'] . ' ' . $kelas['name'],
            'kelas'      => $kelas,
            'daftar'     => $daftar,
            'orang'      => UserDirectory::byIds(array_values(array_unique($ids))),
            'bolehTulis' => $this->bolehMenulis($kelas),
            'isian'      => $isian + ['judul' => '', 'isi' => ''],
            'pesan'      => $pesan,
        ], $status);
    }

    public function index(int $kelasId): ResponseInterface
    {
        $kelas = $this->akademik()->kelas($kelasId);

        return $kelas === null ? $this->tidakDitemukan('class') : $this->tampil($kelas, [], $this->pesanDariQuery(self::PESAN));
    }

    public function simpan(int $kelasId): ResponseInterface
    {
        $kelas = $this->akademik()->kelas($kelasId);
        if ($kelas === null) {
            return $this->tidakDitemukan('class');
        }
        if (! $this->bolehMenulis($kelas)) {
            return $this->terlarang('Kode.galat.tahunArsip');
        }
        $mentah = ['judul' => (string) $this->request->getPost('judul'), 'isi' => (string) $this->request->getPost('isi')];
        $judul  = Pustaka::teks(str_replace("\n", ' ', str_replace(["\r\n", "\r"], "\n", $mentah['judul'])), Pustaka::JUDUL_MAKS);
        $isi    = Pustaka::teks($mentah['isi'], Pustaka::ISI_MAKS);
        if ($judul === null || $isi === null) {
            // Yang sudah diketik tidak hilang.
            return $this->tampil($kelas, $mentah, [['jenis' => 'galat', 'teks' => lang('Komunikasi.pengumuman.isianSalah')]], 422);
        }
        (new Pustaka($this->akses()))->buat($kelas, $judul, $isi);

        return redirect()->to('/kelas/' . $kelasId . '/pengumuman?pesan=dibuat');
    }

    public function tarik(int $kelasId, int $id): ResponseInterface
    {
        $kelas = $this->akademik()->kelas($kelasId);
        if ($kelas === null) {
            return $this->tidakDitemukan('class');
        }
        if (! $this->bolehMenulis($kelas)) {
            return $this->terlarang('Kode.galat.tahunArsip');
        }
        // Menarik yang sudah ditarik (klik ganda) tidak mengubah apa pun dan bukan galat.
        if ((new Pustaka($this->akses()))->tarik($kelas, $id) === null) {
            return $this->tidakDitemukan('announcement');
        }

        return redirect()->to('/kelas/' . $kelasId . '/pengumuman?pesan=ditarik');
    }

    /**
     * Mahasiswa: pengumuman kelasnya sendiri. Kelasnya diambil dari pendaftarannya, tidak dari URL.
     */
    public function saya(): ResponseInterface
    {
        $daftar = (new Pustaka($this->akses()))->untukSaya();

        return $this->halaman('pengumuman_saya', [
            'judul'  => lang('Komunikasi.pengumuman.judul'),
            'daftar' => $daftar,
            'orang'  => UserDirectory::byIds(array_values(array_unique(array_column($daftar, 'author_id')))),
        ]);
    }
}
