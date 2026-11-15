<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\Audit;
use App\Libraries\Waktu;
use CodeIgniter\HTTP\ResponseInterface;
use Lokandita\Auth\UserDirectory;

/**
 * Mata kuliah (SPEC.md §5). Melihat: superadmin, pengampu, pengajar salah satu
 * kelasnya. Membuat dan mengubah: superadmin.
 */
final class MataKuliah extends BaseController
{
    private const PESAN = [
        'disimpan' => ['sukses', 'Kode.pesan.disimpan'],
    ];

    public function index(): ResponseInterface
    {
        $matkul = $this->akademik()->daftarMatkul();

        return $this->halaman('matkul_daftar', [
            'judul'  => lang('Kode.matkul.judul'),
            'matkul' => $matkul,
            'orang'  => UserDirectory::byIds(array_column($matkul, 'owner_id')),
            'pesan'  => $this->pesanDariQuery(self::PESAN),
        ]);
    }

    // ---- Membuat dan mengubah: superadmin saja (filter rute). ----

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

        $db = db_connect();
        $db->transStart();
        $db->table('courses')->insert([
            'code' => $isi['code'], 'name' => $isi['name'], 'owner_id' => $pengampu['id'],
            'created_at' => Waktu::kini(), 'updated_at' => Waktu::kini(),
        ]);
        $id = (int) $db->insertID();
        Audit::catat('matkul.dibuat', 'course', $id, ['code' => $isi['code'], 'name' => $isi['name'], 'owner_id' => $pengampu['id']], $id);
        $db->transComplete();

        return redirect()->to('/mata-kuliah/' . $id . '?pesan=disimpan');
    }

    public function sunting(int $id): ResponseInterface
    {
        $matkul = $this->akademik()->matkul($id);
        if ($matkul === null) {
            return $this->tidakDitemukan('course');
        }
        $pengampu = UserDirectory::byIds([$matkul['owner_id']])[$matkul['owner_id']] ?? null;

        return $this->form($matkul, ['code' => $matkul['code'], 'name' => $matkul['name'], 'pengampu' => $pengampu['nomor_induk'] ?? ''], []);
    }

    public function perbarui(int $id): ResponseInterface
    {
        $matkul = $this->akademik()->matkul($id);
        if ($matkul === null) {
            return $this->tidakDitemukan('course');
        }
        [$isi, $galat, $pengampu] = $this->periksa($id);
        if ($galat !== []) {
            return $this->form($matkul, $isi, $galat, 422);
        }

        $db = db_connect();
        $db->transStart();
        $db->table('courses')->where('id', $id)->update([
            'code' => $isi['code'], 'name' => $isi['name'], 'owner_id' => $pengampu['id'], 'updated_at' => Waktu::kini(),
        ]);
        Audit::catat('matkul.diubah', 'course', $id, [
            'sebelum' => ['code' => $matkul['code'], 'name' => $matkul['name'], 'owner_id' => $matkul['owner_id']],
            'sesudah' => ['code' => $isi['code'], 'name' => $isi['name'], 'owner_id' => $pengampu['id']],
        ], $id);
        $db->transComplete();

        return redirect()->to('/mata-kuliah/' . $id . '?pesan=disimpan');
    }

    /**
     * @return array{0: array{code: string, name: string, pengampu: string}, 1: array<string, string>, 2: array<string, string>|null}
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
            $sama = db_connect()->table('courses')->where('code', $isi['code']);
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

        // Pengampu: tepat satu, harus dosen yang akunnya aktif di LokaAuth.
        $pengampu = $isi['pengampu'] === '' ? null : (UserDirectory::byNomorInduk([$isi['pengampu']])[$isi['pengampu']] ?? null);
        if ($pengampu === null) {
            $galat['pengampu'] = 'pengampuTidakAda';
        } elseif ($pengampu['global_role'] !== 'dosen') {
            $galat['pengampu'] = 'pengampuBukanDosen';
        } elseif ($pengampu['status'] !== 'active') {
            $galat['pengampu'] = 'pengampuTidakAktif';
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
        return $this->halaman('matkul_form', [
            'judul'  => $matkul === null ? lang('Kode.matkul.tambah') : lang('Kode.matkul.ubah'),
            'matkul' => $matkul,
            'isi'    => $isi,
            'galat'  => $galat,
            'pesan'  => $galat !== [] ? [['jenis' => 'galat', 'teks' => lang('Kode.pesan.periksa')]] : [],
        ], $status);
    }

    public function lihat(int $id): ResponseInterface
    {
        $akademik = $this->akademik();
        $matkul   = $akademik->matkul($id);
        if ($matkul === null) {
            return $this->tidakDitemukan('course');
        }

        // Tahun ajaran yang ditampilkan: ?tahun=…, bawaan tahun aktif.
        $semuaTahun = $akademik->semuaTahun();
        $tahun      = $akademik->tahun((int) $this->request->getGet('tahun')) ?? $akademik->tahunAktif();
        $kelas      = $tahun === null ? [] : $akademik->daftarKelas((int) $tahun['id'], $id);
        $pengajar   = $akademik->pengajar(array_map(static fn ($k) => (int) $k['id'], $kelas));

        $ids = [$matkul['owner_id']];
        foreach ($pengajar as $daftar) {
            $ids = array_merge($ids, $daftar);
        }

        return $this->halaman('matkul_lihat', [
            'judul'      => $matkul['code'] . ' — ' . $matkul['name'],
            'matkul'     => $matkul,
            'tahun'      => $tahun,
            'semuaTahun' => $semuaTahun,
            'kelas'      => $kelas,
            'pengajar'   => $pengajar,
            'orang'      => UserDirectory::byIds($ids),
            'pesan'      => $this->pesanDariQuery(self::PESAN),
        ]);
    }
}
