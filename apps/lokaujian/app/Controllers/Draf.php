<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\Aturan;
use App\Libraries\Berkas\Ekspor;
use App\Libraries\Berkas\Impor;
use App\Libraries\Ujian;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Draf ujian satu mata kuliah — bagian tahap U2 (SPEC-LOKAUJIAN.md §6.3): dibuat dari butir bank
 * yang dicentang atau dari impor berkas JSON, dipratinjau, diekspor, dihapus. Menyusun ulang,
 * mengubah setelan dan menerbitkan adalah tahap U3.
 */
final class Draf extends BaseController
{
    private const PESAN = [
        'dibuat'   => ['sukses', 'Bank.ujian.pesanDibuat'],
        'diimpor'  => ['sukses', 'Bank.ujian.pesanDiimpor'],
        'dihapus'  => ['sukses', 'Bank.ujian.pesanDihapus'],
        'tanpa_butir'     => ['galat', 'Bank.ujian.galatTanpaButir'],
        'butir_asing'     => ['galat', 'Bank.ujian.galatButirAsing'],
        'kelompok_kosong' => ['galat', 'Bank.ujian.galatKelompokKosong'],
        'terlalu_banyak'  => ['galat', 'Bank.ujian.galatTerlaluBanyak'],
        'judul'           => ['galat', 'Bank.ujian.galatJudul'],
    ];

    private function alamat(int $matkulId): string
    {
        return '/mata-kuliah/' . $matkulId . '/ujian';
    }

    /**
     * @param array<string, mixed>                                     $matkul
     * @param list<array{jalur: string, baris: int, pesan: string}> $galatImpor
     */
    private function tampilDaftar(array $matkul, array $galatImpor = [], string $berkas = '', int $status = 200): ResponseInterface
    {
        return $this->halaman('ujian_daftar', [
            'judul'      => lang('Bank.ujian.judul') . ' — ' . $matkul['code'],
            'matkul'     => $matkul,
            'ujian'      => (new Ujian($this->akses()))->daftar((int) $matkul['id']),
            'galatImpor' => $galatImpor,
            'berkas'     => $berkas,
            'pesan'      => $this->pesanDariQuery(self::PESAN),
        ], $status);
    }

    public function daftar(string $matkulId): ResponseInterface
    {
        $matkul = $this->matkulDikelola((int) $matkulId);

        return $matkul instanceof ResponseInterface ? $matkul : $this->tampilDaftar($matkul);
    }

    /**
     * Draf baru dari butir bank yang dicentang (`butir[]` = "s<id soal>" atau "k<id kelompok>"),
     * menurut urutan daftar bank. Setelan bawaan: dikendalikan dosen, tanpa durasi, tanpa acak;
     * mengubahnya adalah tahap U3.
     */
    public function buat(string $matkulId): ResponseInterface
    {
        $matkul = $this->matkulDikelola((int) $matkulId);
        if ($matkul instanceof ResponseInterface) {
            return $matkul;
        }
        $matkulId = (int) $matkul['id'];
        $aturan   = new Aturan();
        $setelan  = $aturan->ujian([
            'title' => (string) $this->request->getPost('judul'), 'kind' => (string) $this->request->getPost('jenis'),
            'settings' => ['mode' => 'dikendalikan_dosen'],
        ]);
        if ($setelan === null) {
            return redirect()->to($this->alamat($matkulId) . '?pesan=judul');
        }
        $butir = [];
        foreach ((array) $this->request->getPost('butir') as $b) {
            if (is_string($b) && preg_match('/^([sk])(\d{1,18})$/', $b, $m) === 1) {
                $butir[] = [$m[1] === 's' ? 'soal' : 'kelompok', (int) $m[2]];
            } else {
                return redirect()->to($this->alamat($matkulId) . '?pesan=butir_asing');
            }
        }
        $id = (new Ujian($this->akses()))->buat($matkulId, $setelan, $butir);

        return redirect()->to(is_string($id) ? $this->alamat($matkulId) . '?pesan=' . $id : $this->alamat($matkulId) . '/' . $id . '?pesan=dibuat');
    }

    /**
     * Pratinjau seluruh ujian dengan partial yang sama dengan yang akan dipakai halaman mahasiswa
     * (SPEC-LOKAUJIAN.md §5.4). Tidak membuat pengerjaan dan tidak mencatat apa pun.
     */
    public function lihat(string $matkulId, string $id): ResponseInterface
    {
        $matkul = $this->matkulDikelola((int) $matkulId);
        if ($matkul instanceof ResponseInterface) {
            return $matkul;
        }
        $ujian = (new Ujian($this->akses()))->lihat((int) $matkul['id'], (int) $id);
        if ($ujian === null) {
            return $this->tidakDitemukan('exam');
        }

        return $this->halaman('ujian_lihat', [
            'judul'  => $ujian['title'] . ' — ' . $matkul['code'],
            'matkul' => $matkul,
            'ujian'  => $ujian,
            'pesan'  => $this->pesanDariQuery(self::PESAN),
        ]);
    }

    /**
     * Halaman sebelum unduhan: menyatakan bahwa berkas memuat semua kunci jawaban.
     */
    public function eksporHalaman(string $matkulId, string $id): ResponseInterface
    {
        $matkul = $this->matkulDikelola((int) $matkulId);
        if ($matkul instanceof ResponseInterface) {
            return $matkul;
        }
        $ujian = (new Ujian($this->akses()))->lihat((int) $matkul['id'], (int) $id);
        if ($ujian === null) {
            return $this->tidakDitemukan('exam');
        }

        return $this->halaman('ujian_ekspor', [
            'judul'  => lang('Bank.ujian.ekspor') . ' — ' . $ujian['title'],
            'matkul' => $matkul,
            'ujian'  => $ujian,
        ]);
    }

    public function ekspor(string $matkulId, string $id): ResponseInterface
    {
        $matkul = $this->matkulDikelola((int) $matkulId);
        if ($matkul instanceof ResponseInterface) {
            return $matkul;
        }
        $ujian = (new Ujian($this->akses()))->lihat((int) $matkul['id'], (int) $id);
        if ($ujian === null) {
            return $this->tidakDitemukan('exam');
        }
        ini_set('memory_limit', '512M');
        $isi  = Ekspor::berkas($ujian, (string) $matkul['code']);
        $nama = 'lokaujian-' . preg_replace('/[^A-Za-z0-9_-]/', '', (string) $matkul['code']) . '-' . (int) $ujian['id'] . '.json';
        \App\Libraries\Audit::catat('ujian.diekspor', 'ujian', (int) $ujian['id'], [
            'berkas' => $nama, 'soal' => $ujian['ringkasan']['soal'], 'byte' => strlen($isi),
        ], (int) $matkul['id']);

        return $this->response
            ->setStatusCode(200)
            ->setHeader('Content-Type', 'application/json; charset=utf-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $nama . '"')
            ->setHeader('Cache-Control', 'no-store')
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setBody($isi);
    }

    public function impor(string $matkulId): ResponseInterface
    {
        $matkul = $this->matkulDikelola((int) $matkulId);
        if ($matkul instanceof ResponseInterface) {
            return $matkul;
        }
        $matkulId = (int) $matkul['id'];
        $berkas   = $this->request->getFile('berkas');
        if ($berkas === null || $berkas->getError() !== UPLOAD_ERR_OK) {
            $kode = $berkas !== null && in_array($berkas->getError(), [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true) ? 'berkasBesar' : 'berkasTidakAda';

            return $this->tampilDaftar($matkul, [['jalur' => '', 'baris' => 1, 'pesan' => lang('Bank.galat.' . $kode, [24])]], '', 422);
        }
        $nama  = (string) $berkas->getClientName();
        $hasil = (new Impor($this->akses()))->jalankan((string) file_get_contents($berkas->getTempName()), $nama, $matkulId);
        if (! $hasil['ok']) {
            return $this->tampilDaftar($matkul, $hasil['galat'], $nama, 422);
        }

        return redirect()->to($this->alamat($matkulId) . '/' . $hasil['ujian'] . '?pesan=diimpor');
    }

    public function hapus(string $matkulId, string $id): ResponseInterface
    {
        $matkul = $this->matkulDikelola((int) $matkulId);
        if ($matkul instanceof ResponseInterface) {
            return $matkul;
        }
        $hasil = (new Ujian($this->akses()))->hapus((int) $matkul['id'], (int) $id);
        if ($hasil !== 'ok') {
            return $this->tidakDitemukan('exam');
        }

        return redirect()->to($this->alamat((int) $matkul['id']) . '?pesan=dihapus');
    }
}
