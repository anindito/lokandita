<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\Aturan;
use App\Libraries\Berkas\Ekspor;
use App\Libraries\Bank;
use App\Libraries\Berkas\Impor;
use App\Libraries\Sesi;
use App\Libraries\Ujian;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Ujian dan kuis satu mata kuliah, bagi yang mengelolanya (SPEC-LOKAUJIAN.md §6): draf dibuat
 * dari butir bank yang dicentang atau dari impor berkas JSON, disusun dan diatur, diterbitkan
 * (isinya dibekukan), ditarik, diarsipkan, diekspor, dihapus. Memberikan ujian ke kelas ada di
 * App\Controllers\SesiStaf.
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
        'disimpan'        => ['sukses', 'Sesi.ujian.pesanDisimpan'],
        'butir'           => ['sukses', 'Sesi.ujian.pesanButir'],
        'terakhir'        => ['galat', 'Sesi.ujian.galatTerakhir'],
        'diterbitkan'     => ['sukses', 'Sesi.ujian.pesanDiterbitkan'],
        'ditarik'         => ['sukses', 'Sesi.ujian.pesanDitarik'],
        'diarsipkan'      => ['sukses', 'Sesi.ujian.pesanDiarsipkan'],
        'dipulihkan'      => ['sukses', 'Sesi.ujian.pesanDipulihkan'],
        'ada_pengerjaan'  => ['galat', 'Sesi.ujian.galatAdaPengerjaan'],
        'sedang_berjalan' => ['galat', 'Sesi.ujian.galatSedangBerjalan'],
        'sesi_dibuat'     => ['sukses', 'Sesi.pesan.dibuat'],
        'sesi_dihapus'    => ['sukses', 'Sesi.pesan.dihapus'],
        'bukan_terbit'    => ['galat', 'Sesi.galat.bukanTerbit'],
        'kelas_asing'     => ['galat', 'Sesi.galat.kelasAsing'],
        'sudah_ada'       => ['galat', 'Sesi.galat.sudahAda'],
        'jadwal'          => ['galat', 'Sesi.galat.jadwal'],
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
            'ujian'      => (new Ujian($this->akses()))->daftar((int) $matkul['id'], $this->request->getGet('arsip') === '1'),
            'arsip'      => $this->request->getGet('arsip') === '1',
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

        return $ujian === null ? $this->tidakDitemukan('exam') : $this->tampilUjian($matkul, $ujian);
    }

    /**
     * @param array<string, mixed>                    $matkul
     * @param array<string, mixed>                    $ujian
     * @param list<array{kode: string, nomor: int}> $kurang kekurangan yang membuat penerbitan ditolak
     */
    private function tampilUjian(array $matkul, array $ujian, array $kurang = [], int $status = 200): ResponseInterface
    {
        $roster = $this->roster();
        $tahun  = $roster->tahunAktif();
        $sesi   = $ujian['status'] === 'draf' ? [] : (new Sesi($this->akses()))->untukUjian((int) $matkul['id'], (int) $ujian['id']);
        $kelas  = $tahun === null ? [] : $roster->daftarKelas((int) $tahun['id'], (int) $matkul['id']);

        return $this->halaman('ujian_lihat', [
            'judul'  => $ujian['title'] . ' — ' . $matkul['code'],
            'matkul' => $matkul,
            'ujian'  => $ujian,
            'sesi'   => $sesi,
            // Bukan `kelas`: nama itu dipakai partial _gambar untuk kelas CSS, dan data view terbawa ke partial.
            'daftarKelas' => $kelas,
            'kurang' => $kurang,
            'pesan'  => $this->pesanDariQuery(self::PESAN),
        ], $status);
    }

    /**
     * Form setelan dan susunan butir sebuah DRAF.
     *
     * @param array<string, mixed>|null $isian yang diketik, bila form ditolak
     * @param list<string>              $galat
     */
    private function tampilSunting(array $matkul, array $ujian, ?array $isian = null, array $galat = [], int $status = 200): ResponseInterface
    {
        $dipakai = [];
        foreach ($ujian['butir'] as $b) {
            $dipakai[$b['jenis'] === 'soal' ? 's' . $b['soal']['id'] : 'k' . $b['kelompok']['id']] = true;
        }

        return $this->halaman('ujian_sunting', [
            'judul'   => lang('Sesi.ujian.sunting') . ' — ' . $ujian['title'],
            'matkul'  => $matkul,
            'ujian'   => $ujian,
            'isian'   => $isian ?? [
                'judul' => $ujian['title'], 'jenis' => $ujian['kind'], 'petunjuk' => $ujian['instructions'], 'mode' => $ujian['mode'],
                'durasi' => $ujian['duration_minutes'] === null ? '' : (string) $ujian['duration_minutes'],
                'acak_soal' => (bool) $ujian['shuffle_questions'], 'acak_opsi' => (bool) $ujian['shuffle_options'],
            ],
            'galat'   => $galat,
            'bank'    => (new Bank($this->akses()))->daftar((int) $matkul['id']),
            'dipakai' => $dipakai,
            'pesan'   => $this->pesanDariQuery(self::PESAN),
        ], $status);
    }

    /**
     * Draf yang diminta, atau jawaban penolakannya. Ujian yang sudah terbit tidak disunting: 404.
     *
     * @return array{0: array<string, mixed>, 1: array<string, mixed>}|ResponseInterface [mata kuliah, ujian]
     */
    private function drafDiminta(string $matkulId, string $id): array|ResponseInterface
    {
        $matkul = $this->matkulDikelola((int) $matkulId);
        if ($matkul instanceof ResponseInterface) {
            return $matkul;
        }
        $ujian = (new Ujian($this->akses()))->lihat((int) $matkul['id'], (int) $id);
        if ($ujian === null || $ujian['status'] !== 'draf') {
            return $this->tidakDitemukan('exam');
        }

        return [$matkul, $ujian];
    }

    public function sunting(string $matkulId, string $id): ResponseInterface
    {
        $d = $this->drafDiminta($matkulId, $id);

        return $d instanceof ResponseInterface ? $d : $this->tampilSunting($d[0], $d[1]);
    }

    public function simpan(string $matkulId, string $id): ResponseInterface
    {
        $d = $this->drafDiminta($matkulId, $id);
        if ($d instanceof ResponseInterface) {
            return $d;
        }
        [$matkul, $ujian] = $d;
        $durasi           = trim((string) $this->request->getPost('durasi'));
        $isian            = [
            'judul' => (string) $this->request->getPost('judul'), 'jenis' => (string) $this->request->getPost('jenis'),
            'petunjuk' => (string) $this->request->getPost('petunjuk'), 'mode' => (string) $this->request->getPost('mode'), 'durasi' => $durasi,
            'acak_soal' => $this->request->getPost('acak_soal') === '1', 'acak_opsi' => $this->request->getPost('acak_opsi') === '1',
        ];
        // Form dan berkas impor melewati aturan yang sama (App\Libraries\Aturan).
        $aturan  = new Aturan();
        $setelan = $aturan->ujian([
            'title' => $isian['judul'], 'kind' => $isian['jenis'], 'instructions' => $isian['petunjuk'],
            'settings' => [
                'mode' => $isian['mode'],
                'duration_minutes' => $durasi === '' ? null : (preg_match('/^\d{1,4}$/', $durasi) === 1 ? (int) $durasi : -1),
                'shuffle_questions' => $isian['acak_soal'], 'shuffle_options' => $isian['acak_opsi'],
            ],
        ]);
        if ($setelan === null) {
            $label = ['title' => 'Bank.ujian.judulIsian', 'kind' => 'Bank.ujian.jenis', 'instructions' => 'Bank.ujian.petunjuk', 'settings.mode' => 'Bank.ujian.mode', 'settings.duration_minutes' => 'Sesi.ujian.durasi'];
            $galat = [];
            foreach ($aturan->galat() as [$jalur, $kode, $arg]) {
                $galat[] = lang($label[$jalur] ?? 'Sesi.ujian.setelan') . ': ' . lang('Bank.galat.' . $kode, $arg);
            }

            return $this->tampilSunting($matkul, $ujian, $isian, $galat, 422);
        }
        if (! (new Ujian($this->akses()))->simpanSetelan((int) $matkul['id'], (int) $ujian['id'], $setelan)) {
            return $this->tidakDitemukan('exam');
        }

        return redirect()->to($this->alamat((int) $matkul['id']) . '/' . (int) $ujian['id'] . '/sunting?pesan=disimpan');
    }

    public function tambahButir(string $matkulId, string $id): ResponseInterface
    {
        $d = $this->drafDiminta($matkulId, $id);
        if ($d instanceof ResponseInterface) {
            return $d;
        }
        $ini   = $this->alamat((int) $d[0]['id']) . '/' . (int) $d[1]['id'] . '/sunting';
        $butir = [];
        foreach ((array) $this->request->getPost('butir') as $b) {
            if (! is_string($b) || preg_match('/^([sk])(\d{1,18})$/', $b, $m) !== 1) {
                return redirect()->to($ini . '?pesan=butir_asing');
            }
            $butir[] = [$m[1] === 's' ? 'soal' : 'kelompok', (int) $m[2]];
        }
        $hasil = (new Ujian($this->akses()))->tambahButir((int) $d[0]['id'], (int) $d[1]['id'], $butir);

        return $hasil === 'tidak_ada' ? $this->tidakDitemukan('exam') : redirect()->to($ini . '?pesan=' . ($hasil === 'ok' ? 'butir' : $hasil));
    }

    private function ubahButir(string $matkulId, string $id, string $posisi, string $apa): ResponseInterface
    {
        $d = $this->drafDiminta($matkulId, $id);
        if ($d instanceof ResponseInterface) {
            return $d;
        }
        $hasil = (new Ujian($this->akses()))->ubahButir((int) $d[0]['id'], (int) $d[1]['id'], (int) $posisi, $apa);
        if ($hasil === 'tidak_ada') {
            return $this->tidakDitemukan('exam_item');
        }

        return redirect()->to($this->alamat((int) $d[0]['id']) . '/' . (int) $d[1]['id'] . '/sunting?pesan=' . ($hasil === 'ok' ? 'butir' : $hasil));
    }

    public function geserButir(string $matkulId, string $id, string $posisi): ResponseInterface
    {
        return $this->ubahButir($matkulId, $id, $posisi, $this->request->getPost('arah') === 'naik' ? 'naik' : 'turun');
    }

    public function hapusButir(string $matkulId, string $id, string $posisi): ResponseInterface
    {
        return $this->ubahButir($matkulId, $id, $posisi, 'hapus');
    }

    /**
     * Menerbitkan: isi ujian dibekukan. Ditolak (422) dengan daftar persis apa yang kurang.
     */
    public function terbit(string $matkulId, string $id): ResponseInterface
    {
        $d = $this->drafDiminta($matkulId, $id);
        if ($d instanceof ResponseInterface) {
            return $d;
        }
        $hasil = (new Ujian($this->akses()))->terbitkan((int) $d[0]['id'], (int) $d[1]['id']);
        if (is_array($hasil)) {
            return $this->tampilUjian($d[0], $d[1], $hasil, 422);
        }

        return $hasil === 'ok' ? redirect()->to($this->alamat((int) $d[0]['id']) . '/' . (int) $d[1]['id'] . '?pesan=diterbitkan') : $this->tidakDitemukan('exam');
    }

    public function tarik(string $matkulId, string $id): ResponseInterface
    {
        $matkul = $this->matkulDikelola((int) $matkulId);
        if ($matkul instanceof ResponseInterface) {
            return $matkul;
        }
        $hasil = (new Ujian($this->akses()))->tarik((int) $matkul['id'], (int) $id);

        return $hasil === 'tidak_ada' ? $this->tidakDitemukan('exam')
            : redirect()->to($this->alamat((int) $matkul['id']) . '/' . (int) $id . '?pesan=' . ($hasil === 'ok' ? 'ditarik' : $hasil));
    }

    public function arsip(string $matkulId, string $id): ResponseInterface
    {
        $matkul = $this->matkulDikelola((int) $matkulId);
        if ($matkul instanceof ResponseInterface) {
            return $matkul;
        }
        $arsip = $this->request->getPost('arsip') !== '0';
        $hasil = (new Ujian($this->akses()))->arsipkan((int) $matkul['id'], (int) $id, $arsip);

        return $hasil === 'tidak_ada' ? $this->tidakDitemukan('exam')
            : redirect()->to($this->alamat((int) $matkul['id']) . '/' . (int) $id . '?pesan=' . ($hasil === 'ok' ? ($arsip ? 'diarsipkan' : 'dipulihkan') : $hasil));
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
        if ($hasil === 'tidak_ada') {
            return $this->tidakDitemukan('exam');
        }
        if ($hasil !== 'ok') {
            return redirect()->to($this->alamat((int) $matkul['id']) . '/' . (int) $id . '?pesan=' . $hasil);
        }

        return redirect()->to($this->alamat((int) $matkul['id']) . '?pesan=dihapus');
    }
}
