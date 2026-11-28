<?php

declare(strict_types=1);

namespace App\Controllers\Admin\Roster;

use App\Libraries\Clock;
use App\Libraries\Roster;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Mata kuliah (SPEC.md §5): kode, nama, satu pengampu. Tidak ada hapus.
 */
final class MataKuliah extends Dasar
{
    private const PESAN = [
        'disimpan'      => ['sukses', 'Roster.pesan.disimpan'],
        'kelas-dihapus' => ['sukses', 'Roster.pesan.kelasDihapus'],
    ];

    // Galat form "tambah kelas" di halaman mata kuliah: ?galat=<kunci>.
    private const GALAT_KELAS = ['kelasNama', 'kelasNamaDipakai', 'kelasKapasitas'];

    public function index(): ResponseInterface
    {
        return $this->halaman('admin/roster/matkul_daftar', [
            'judul'  => lang('Roster.matkul.judul'),
            'matkul' => Roster::daftarMatkul(),
            'pesan'  => $this->pesanDariQuery(self::PESAN),
        ]);
    }

    public function baru(): ResponseInterface
    {
        return $this->form(null, ['code' => '', 'name' => '', 'pengampu' => ''], []);
    }

    public function simpan(): ResponseInterface
    {
        [$isi, $galat, $pengampu] = $this->periksa(null);
        if ($galat !== []) {
            return $this->form(null, $isi, $galat, 422);
        }

        $db = Roster::db();
        Roster::mulai();
        $db->table(Roster::t('courses'))->insert([
            'code' => $isi['code'], 'name' => $isi['name'], 'owner_id' => $pengampu['id'],
            'created_at' => Clock::now(), 'updated_at' => Clock::now(),
        ]);
        $id = (int) $db->insertID();
        Roster::audit('matkul.dibuat', $this->pelaku(), 'course', $id, ['code' => $isi['code'], 'name' => $isi['name'], 'owner_id' => $pengampu['id']], $id);
        $db->transComplete();

        return redirect()->to('/admin/mata-kuliah/' . $id . '?pesan=disimpan');
    }

    public function sunting(int $id): ResponseInterface
    {
        $matkul = Roster::matkul($id);
        if ($matkul === null) {
            return $this->tidakAda();
        }

        return $this->form($matkul, ['code' => $matkul['code'], 'name' => $matkul['name'], 'pengampu' => (string) $matkul['pengampu_nomor']], []);
    }

    public function perbarui(int $id): ResponseInterface
    {
        $matkul = Roster::matkul($id);
        if ($matkul === null) {
            return $this->tidakAda();
        }
        [$isi, $galat, $pengampu] = $this->periksa($id);
        if ($galat !== []) {
            return $this->form($matkul, $isi, $galat, 422);
        }

        $db = Roster::db();
        Roster::mulai();
        $db->table(Roster::t('courses'))->where('id', $id)->update([
            'code' => $isi['code'], 'name' => $isi['name'], 'owner_id' => $pengampu['id'], 'updated_at' => Clock::now(),
        ]);
        Roster::audit('matkul.diubah', $this->pelaku(), 'course', $id, [
            'sebelum' => ['code' => $matkul['code'], 'name' => $matkul['name'], 'owner_id' => $matkul['owner_id']],
            'sesudah' => ['code' => $isi['code'], 'name' => $isi['name'], 'owner_id' => $pengampu['id']],
        ], $id);
        $db->transComplete();

        return redirect()->to('/admin/mata-kuliah/' . $id . '?pesan=disimpan');
    }

    public function lihat(int $id): ResponseInterface
    {
        $matkul = Roster::matkul($id);
        if ($matkul === null) {
            return $this->tidakAda();
        }

        // Tahun ajaran yang ditampilkan: ?tahun=…, bawaan tahun aktif.
        $tahun = Roster::tahun((int) $this->request->getGet('tahun')) ?? Roster::tahunAktif();
        $kelas = $tahun === null ? [] : Roster::daftarKelas((int) $tahun['id'], $id);
        $galat = (string) $this->request->getGet('galat');

        return $this->halaman('admin/roster/matkul_lihat', [
            'judul'      => $matkul['code'] . ' — ' . $matkul['name'],
            'matkul'     => $matkul,
            'tahun'      => $tahun,
            'semuaTahun' => Roster::semuaTahun(),
            'kelas'      => $kelas,
            'pengajar'   => Roster::pengajar(array_map(static fn ($k) => (int) $k['id'], $kelas)),
            'pesan'      => in_array($galat, self::GALAT_KELAS, true)
                ? $this->galat(lang('Roster.galat.' . $galat))
                : $this->pesanDariQuery(self::PESAN),
        ]);
    }

    /**
     * @return array{0: array{code: string, name: string, pengampu: string}, 1: array<string, string>, 2: array<string, mixed>|null}
     */
    private function periksa(?int $kecuali): array
    {
        $isi = [
            'code'     => strtoupper(trim((string) $this->request->getPost('code'))),
            'name'     => trim((string) $this->request->getPost('name')),
            'pengampu' => trim((string) $this->request->getPost('pengampu')),
        ];
        $galat = [];
        if (preg_match('/^[A-Z0-9][A-Z0-9-]{1,19}$/', $isi['code']) !== 1) {
            $galat['code'] = 'kodeBentuk';
        } else {
            $sama = Roster::db()->table(Roster::t('courses'))->where('code', $isi['code']);
            if ($kecuali !== null) {
                $sama->where('id !=', $kecuali);
            }
            if ($sama->countAllResults() > 0) {
                $galat['code'] = 'kodeDipakai';
            }
        }
        if (mb_strlen($isi['name']) < 3 || mb_strlen($isi['name']) > 190) {
            $galat['name'] = 'namaPanjang';
        }

        // Pengampu: tepat satu, harus dosen yang akunnya aktif.
        $pengampu = Roster::dosenAktif($isi['pengampu']);
        if (is_string($pengampu)) {
            $galat['pengampu'] = 'pengampu' . ucfirst($pengampu);
            $pengampu          = null;
        }

        return [$isi, $galat, $pengampu];
    }

    /**
     * @param array<string, mixed>|null $matkul null = mata kuliah baru
     * @param array<string, string>     $isi
     * @param array<string, string>     $galat
     */
    private function form(?array $matkul, array $isi, array $galat, int $status = 200): ResponseInterface
    {
        return $this->halaman('admin/roster/matkul_form', [
            'judul'  => $matkul === null ? lang('Roster.matkul.tambah') : lang('Roster.matkul.ubah'),
            'matkul' => $matkul,
            'isi'    => $isi,
            'galat'  => $galat,
            'pesan'  => $galat !== [] ? $this->periksaLagi() : [],
        ], $status);
    }
}
