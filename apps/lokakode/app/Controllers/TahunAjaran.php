<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\Audit;
use App\Libraries\Waktu;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Tahun ajaran (SPEC.md §5): superadmin saja. Tepat satu yang aktif;
 * mengaktifkan satu tahun mengarsipkan yang sebelumnya aktif.
 */
final class TahunAjaran extends BaseController
{
    private const PESAN = [
        'disimpan'   => ['sukses', 'Kode.pesan.disimpan'],
        'diaktifkan' => ['sukses', 'Kode.pesan.tahunDiaktifkan'],
    ];

    public function index(): ResponseInterface
    {
        return $this->daftar(['label' => '', 'starts_on' => '', 'ends_on' => ''], []);
    }

    public function simpan(): ResponseInterface
    {
        [$isi, $galat] = $this->periksa(null);
        if ($galat !== []) {
            return $this->daftar($isi, $galat, 422);
        }

        $db = db_connect();
        $db->transStart();
        $db->table('academic_years')->insert($isi + ['status' => 'archived', 'created_at' => Waktu::kini(), 'updated_at' => Waktu::kini()]);
        $id = (int) $db->insertID();
        Audit::catat('tahun.dibuat', 'academic_year', $id, $isi);
        $db->transComplete();

        return redirect()->to('/tahun-ajaran?pesan=disimpan');
    }

    public function sunting(int $id): ResponseInterface
    {
        $tahun = $this->akademik()->tahun($id);
        if ($tahun === null) {
            return $this->tidakDitemukan('academic_year');
        }

        return $this->form($tahun, $tahun, []);
    }

    public function perbarui(int $id): ResponseInterface
    {
        $tahun = $this->akademik()->tahun($id);
        if ($tahun === null) {
            return $this->tidakDitemukan('academic_year');
        }
        [$isi, $galat] = $this->periksa($id);
        if ($galat !== []) {
            return $this->form($tahun, $isi, $galat, 422);
        }

        $db = db_connect();
        $db->transStart();
        $db->table('academic_years')->where('id', $id)->update($isi + ['updated_at' => Waktu::kini()]);
        Audit::catat('tahun.diubah', 'academic_year', $id, [
            'sebelum' => ['label' => $tahun['label'], 'starts_on' => $tahun['starts_on'], 'ends_on' => $tahun['ends_on']],
            'sesudah' => $isi,
        ]);
        $db->transComplete();

        return redirect()->to('/tahun-ajaran?pesan=disimpan');
    }

    /**
     * Menjadikan tahun ini satu-satunya yang aktif. Yang sebelumnya aktif diarsipkan
     * dalam transaksi yang sama; kunci unik di basis data menolak dua tahun aktif.
     */
    public function aktifkan(int $id): ResponseInterface
    {
        $tahun = $this->akademik()->tahun($id);
        if ($tahun === null) {
            return $this->tidakDitemukan('academic_year');
        }
        if ($tahun['status'] !== 'active') {
            $db   = db_connect();
            $lama = $this->akademik()->tahunAktif();
            $db->transStart();
            $db->query("UPDATE academic_years SET status = 'archived', updated_at = ? WHERE status = 'active'", [Waktu::kini()]);
            $db->query("UPDATE academic_years SET status = 'active', updated_at = ? WHERE id = ?", [Waktu::kini(), $id]);
            Audit::catat('tahun.diaktifkan', 'academic_year', $id, [
                'label'              => $tahun['label'],
                'diarsipkan_id'      => $lama['id'] ?? null,
                'diarsipkan_label'   => $lama['label'] ?? null,
            ]);
            $db->transComplete();
        }

        return redirect()->to('/tahun-ajaran?pesan=diaktifkan');
    }

    /**
     * @return array{0: array{label: string, starts_on: string, ends_on: string}, 1: array<string, string>}
     */
    private function periksa(?int $kecuali): array
    {
        $isi = [
            'label'     => trim((string) $this->request->getPost('label')),
            'starts_on' => trim((string) $this->request->getPost('starts_on')),
            'ends_on'   => trim((string) $this->request->getPost('ends_on')),
        ];
        $galat = [];
        if (mb_strlen($isi['label']) < 3 || mb_strlen($isi['label']) > 40) {
            $galat['label'] = 'labelPanjang';
        } else {
            $sama = db_connect()->table('academic_years')->where('label', $isi['label']);
            if ($kecuali !== null) {
                $sama->where('id !=', $kecuali);
            }
            if ($sama->countAllResults() > 0) {
                $galat['label'] = 'labelDipakai';
            }
        }
        foreach (['starts_on', 'ends_on'] as $k) {
            if (! Waktu::tanggalSah($isi[$k])) {
                $galat[$k] = 'tanggal';
            }
        }
        if (! isset($galat['starts_on']) && ! isset($galat['ends_on']) && $isi['ends_on'] <= $isi['starts_on']) {
            $galat['ends_on'] = 'tanggalUrut';
        }

        return [$isi, $galat];
    }

    /**
     * @param array<string, string> $isi
     * @param array<string, string> $galat
     */
    private function daftar(array $isi, array $galat, int $status = 200): ResponseInterface
    {
        $db    = db_connect();
        $kelas = [];
        foreach ($db->query('SELECT academic_year_id AS t, COUNT(*) AS n FROM classes GROUP BY academic_year_id')->getResultArray() as $b) {
            $kelas[(int) $b['t']] = (int) $b['n'];
        }

        return $this->halaman('tahun_daftar', [
            'judul'  => lang('Kode.nav.tahun'),
            'tahun'  => $this->akademik()->semuaTahun(),
            'kelas'  => $kelas,
            'isi'    => $isi,
            'galat'  => $galat,
            'pesan'  => $galat !== [] ? [['jenis' => 'galat', 'teks' => lang('Kode.pesan.periksa')]] : $this->pesanDariQuery(self::PESAN),
        ], $status);
    }

    /**
     * @param array<string, mixed>  $tahun
     * @param array<string, mixed>  $isi
     * @param array<string, string> $galat
     */
    private function form(array $tahun, array $isi, array $galat, int $status = 200): ResponseInterface
    {
        return $this->halaman('tahun_form', [
            'judul' => lang('Kode.tahun.ubah'),
            'tahun' => $tahun,
            'isi'   => $isi,
            'galat' => $galat,
            'pesan' => $galat !== [] ? [['jenis' => 'galat', 'teks' => lang('Kode.pesan.periksa')]] : [],
        ], $status);
    }
}
