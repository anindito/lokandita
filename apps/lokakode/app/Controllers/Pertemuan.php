<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\Audit;
use App\Libraries\BankSoal;
use App\Libraries\Kurikulum;
use App\Libraries\Waktu;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Pertemuan dan konsepnya (SPEC.md §6 bentuk minimal, §9). Mengelola:
 * superadmin dan pengampu mata kuliah itu. Pengajar yang bukan pengampu
 * melihat mata kuliahnya tetapi mendapat 403 di sini (keputusan pemilik,
 * tahap 8); di luar mata kuliahnya 404.
 */
final class Pertemuan extends BaseController
{
    private const PESAN = [
        'disimpan'  => ['sukses', 'Kode.pesan.disimpan'],
        'disetujui' => ['sukses', 'Soal.pesan.disetujui'],
        'ditolak'   => ['sukses', 'Soal.pesan.ditolak'],
    ];

    private function kurikulum(): Kurikulum
    {
        return new Kurikulum($this->akses());
    }

    /**
     * Mata kuliah untuk dikelola: 404 di luar lingkup, 403 bila hanya melihat.
     *
     * @return array<string, mixed>|ResponseInterface
     */
    private function matkulKelola(int $id): array|ResponseInterface
    {
        $matkul = $this->akademik()->matkul($id);
        if ($matkul === null) {
            return $this->tidakDitemukan('course');
        }

        return (int) $matkul['boleh_kelola'] === 1 ? $matkul : $this->terlarang();
    }

    /**
     * @return array<string, mixed>|ResponseInterface
     */
    private function pertemuanKelola(int $id): array|ResponseInterface
    {
        $p = $this->kurikulum()->pertemuan($id);
        if ($p === null) {
            return $this->tidakDitemukan('meeting');
        }

        return (int) $p['boleh_kelola'] === 1 ? $p : $this->terlarang();
    }

    public function index(int $matkulId): ResponseInterface
    {
        $matkul = $this->matkulKelola($matkulId);
        if ($matkul instanceof ResponseInterface) {
            return $matkul;
        }

        return $this->daftar($matkul, ['number' => '', 'title' => '', 'language' => ''], []);
    }

    /**
     * @param array<string, mixed>  $matkul
     * @param array<string, string> $isi
     * @param array<string, string> $galat
     */
    private function daftar(array $matkul, array $isi, array $galat, int $status = 200): ResponseInterface
    {
        $kur = $this->kurikulum();

        return $this->halaman('pertemuan_daftar', [
            'judul'     => lang('Soal.pertemuan.judul') . ' — ' . $matkul['code'],
            'matkul'    => $matkul,
            'pertemuan' => $kur->daftarPertemuan((int) $matkul['id']),
            'katalog'   => $kur->katalog(),
            'dasar'     => $kur->konsepDasar((int) $matkul['id']),
            'isi'       => $isi,
            'galat'     => $galat,
            'pesan'     => $galat !== [] ? [['jenis' => 'galat', 'teks' => lang('Kode.pesan.periksa')]] : $this->pesanDariQuery(self::PESAN),
        ], $status);
    }

    public function simpan(int $matkulId): ResponseInterface
    {
        $matkul = $this->matkulKelola($matkulId);
        if ($matkul instanceof ResponseInterface) {
            return $matkul;
        }
        $isi = [
            'number'   => trim((string) $this->request->getPost('number')),
            'title'    => trim((string) $this->request->getPost('title')),
            'language' => (string) $this->request->getPost('language'),
        ];
        $galat = $this->periksa($isi, $matkulId, null);
        if ($galat !== []) {
            return $this->daftar($matkul, $isi, $galat, 422);
        }

        $db = db_connect();
        $db->transStart();
        $db->table('meetings')->insert([
            'course_id' => $matkulId, 'number' => (int) $isi['number'], 'title' => $isi['title'], 'language' => $isi['language'],
            'enabled' => 1, 'created_at' => Waktu::kini(), 'updated_at' => Waktu::kini(),
        ]);
        $id = (int) $db->insertID();
        Audit::catat('pertemuan.dibuat', 'meeting', $id, ['number' => (int) $isi['number'], 'title' => $isi['title'], 'language' => $isi['language']], $matkulId);
        $db->transComplete();

        return redirect()->to('/pertemuan/' . $id . '?pesan=disimpan');
    }

    /**
     * Konsep dasar mata kuliah: sudah dikuasai sebelum pertemuan 1.
     */
    public function konsepDasar(int $matkulId): ResponseInterface
    {
        $matkul = $this->matkulKelola($matkulId);
        if ($matkul instanceof ResponseInterface) {
            return $matkul;
        }
        $kur    = $this->kurikulum();
        $sah    = array_column($kur->katalog(), 'code');
        $dipilih = array_values(array_intersect($sah, (array) ($this->request->getPost('konsep') ?? [])));
        $sebelum = $kur->konsepDasar($matkulId);

        $db = db_connect();
        $db->transStart();
        $db->table('course_base_concepts')->where('course_id', $matkulId)->delete();
        foreach ($dipilih as $kode) {
            $db->table('course_base_concepts')->insert(['course_id' => $matkulId, 'concept_code' => $kode]);
        }
        Audit::catat('matkul.konsepDasar', 'course', $matkulId, ['sebelum' => $sebelum, 'sesudah' => $dipilih], $matkulId);
        $db->transComplete();

        return redirect()->to('/mata-kuliah/' . $matkulId . '/pertemuan?pesan=disimpan');
    }

    public function lihat(int $id): ResponseInterface
    {
        $p = $this->pertemuanKelola($id);
        if ($p instanceof ResponseInterface) {
            return $p;
        }

        return $this->tampil($p, ['number' => (string) $p['number'], 'title' => $p['title'], 'language' => $p['language'], 'enabled' => (string) $p['enabled']], []);
    }

    /**
     * @param array<string, mixed>  $p
     * @param array<string, string> $isi
     * @param array<string, string> $galat
     */
    private function tampil(array $p, array $isi, array $galat, int $status = 200): ResponseInterface
    {
        $kur = $this->kurikulum();

        return $this->halaman('pertemuan_lihat', [
            'judul'    => $p['matkul_kode'] . ' — ' . lang('Soal.pertemuan.ke', [(int) $p['number']]),
            'p'        => $p,
            'katalog'  => $kur->katalog($isi['language'] !== '' ? $isi['language'] : $p['language']),
            'warisan'  => $kur->konsepWarisan((int) $p['course_id'], (int) $p['number']),
            'tambahan' => $kur->konsepTambahan((int) $p['id']),
            'soal'     => (new BankSoal($this->akses()))->daftarSoal((int) $p['id']),
            'isi'      => $isi,
            'galat'    => $galat,
            'pesan'    => $galat !== [] ? [['jenis' => 'galat', 'teks' => lang('Kode.pesan.periksa')]] : $this->pesanDariQuery(self::PESAN),
        ], $status);
    }

    public function perbarui(int $id): ResponseInterface
    {
        $p = $this->pertemuanKelola($id);
        if ($p instanceof ResponseInterface) {
            return $p;
        }
        $isi = [
            'number'   => trim((string) $this->request->getPost('number')),
            'title'    => trim((string) $this->request->getPost('title')),
            'language' => (string) $this->request->getPost('language'),
            'enabled'  => $this->request->getPost('enabled') === '1' ? '1' : '0',
        ];
        $galat = $this->periksa($isi, (int) $p['course_id'], $p);
        if ($galat !== []) {
            return $this->tampil($p, $isi, $galat, 422);
        }

        // Konsep tambahan: hanya kode katalog yang berlaku untuk bahasa pertemuan
        // dan belum diwarisi. Kotak yang terkunci di form tidak ikut terkirim.
        $kur     = $this->kurikulum();
        $warisan = $kur->konsepWarisan((int) $p['course_id'], (int) $isi['number']);
        $sah     = array_diff(array_column($kur->katalog($isi['language']), 'code'), $warisan);
        $dipilih = array_values(array_intersect($sah, (array) ($this->request->getPost('konsep') ?? [])));
        $sebelum = $kur->konsepTambahan($id);

        $db = db_connect();
        $db->transStart();
        $db->table('meetings')->where('id', $id)->update([
            'number' => (int) $isi['number'], 'title' => $isi['title'], 'language' => $isi['language'],
            'enabled' => (int) $isi['enabled'], 'updated_at' => Waktu::kini(),
        ]);
        $db->table('meeting_concepts')->where('meeting_id', $id)->delete();
        foreach ($dipilih as $kode) {
            $db->table('meeting_concepts')->insert(['meeting_id' => $id, 'concept_code' => $kode]);
        }
        Audit::catat('pertemuan.diubah', 'meeting', $id, [
            'sebelum' => ['number' => (int) $p['number'], 'title' => $p['title'], 'language' => $p['language'], 'enabled' => (int) $p['enabled'], 'konsep' => $sebelum],
            'sesudah' => ['number' => (int) $isi['number'], 'title' => $isi['title'], 'language' => $isi['language'], 'enabled' => (int) $isi['enabled'], 'konsep' => $dipilih],
        ], (int) $p['course_id']);
        $db->transComplete();

        return redirect()->to('/pertemuan/' . $id . '?pesan=disimpan');
    }

    /**
     * @param array<string, string>     $isi
     * @param array<string, mixed>|null $lama null = pertemuan baru
     *
     * @return array<string, string> kolom => kunci galat (Soal.galat.*)
     */
    private function periksa(array $isi, int $matkulId, ?array $lama): array
    {
        $galat = [];
        if (preg_match('/^\d{1,2}$/', $isi['number']) !== 1 || (int) $isi['number'] < 1 || (int) $isi['number'] > 16) {
            $galat['number'] = 'nomor';
        } else {
            $sama = db_connect()->table('meetings')->where('course_id', $matkulId)->where('number', (int) $isi['number']);
            if ($lama !== null) {
                $sama->where('id !=', (int) $lama['id']);
            }
            if ($sama->countAllResults() > 0) {
                $galat['number'] = 'nomorDipakai';
            }
        }
        if (mb_strlen($isi['title']) < 3 || mb_strlen($isi['title']) > 190) {
            $galat['title'] = 'judul';
        }
        if (! in_array($isi['language'], Kurikulum::BAHASA, true)) {
            $galat['language'] = 'bahasa';
        }
        // Soal yang sudah ada divalidasi terhadap bahasa dan urutan konsep pertemuan ini.
        if ($lama !== null && (int) $lama['jumlah_soal'] > 0) {
            if ($isi['language'] !== $lama['language']) {
                $galat['language'] = 'bahasaTerkunci';
            }
            if ((int) $isi['number'] !== (int) $lama['number']) {
                $galat['number'] = 'nomorTerkunci';
            }
        }

        return $galat;
    }
}
