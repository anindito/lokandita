<?php

declare(strict_types=1);

namespace App\Controllers\Admin\Roster;

use App\Libraries\Clock;
use App\Libraries\Roster;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Tahun ajaran (SPEC.md §5). Tepat satu yang aktif; mengaktifkan satu tahun
 * mengarsipkan yang sebelumnya aktif.
 */
final class TahunAjaran extends Dasar
{
    private const PESAN = [
        'disimpan'   => ['sukses', 'Roster.pesan.disimpan'],
        'diaktifkan' => ['sukses', 'Roster.pesan.tahunDiaktifkan'],
        'tahun-baru' => ['sukses', 'Roster.pesan.tahunBaru'],
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

        $db = Roster::db();
        Roster::mulai();
        $db->table(Roster::t('academic_years'))->insert($isi + ['status' => 'archived', 'created_at' => Clock::now(), 'updated_at' => Clock::now()]);
        $id = (int) $db->insertID();
        Roster::audit('tahun.dibuat', $this->pelaku(), 'academic_year', $id, $isi);
        $db->transComplete();

        return redirect()->to('/admin/tahun-ajaran?pesan=disimpan');
    }

    public function sunting(int $id): ResponseInterface
    {
        $tahun = Roster::tahun($id);
        if ($tahun === null) {
            return $this->tidakAda();
        }

        return $this->form($tahun, $tahun, []);
    }

    public function perbarui(int $id): ResponseInterface
    {
        $tahun = Roster::tahun($id);
        if ($tahun === null) {
            return $this->tidakAda();
        }
        [$isi, $galat] = $this->periksa($id);
        if ($galat !== []) {
            return $this->form($tahun, $isi, $galat, 422);
        }

        $db = Roster::db();
        Roster::mulai();
        $db->table(Roster::t('academic_years'))->where('id', $id)->update($isi + ['updated_at' => Clock::now()]);
        Roster::audit('tahun.diubah', $this->pelaku(), 'academic_year', $id, [
            'sebelum' => ['label' => $tahun['label'], 'starts_on' => $tahun['starts_on'], 'ends_on' => $tahun['ends_on']],
            'sesudah' => $isi,
        ]);
        $db->transComplete();

        return redirect()->to('/admin/tahun-ajaran?pesan=disimpan');
    }

    /**
     * Menjadikan tahun ini satu-satunya yang aktif. Yang sebelumnya aktif diarsipkan
     * dalam transaksi yang sama; kunci unik di basis data menolak dua tahun aktif.
     */
    public function aktifkan(int $id): ResponseInterface
    {
        $tahun = Roster::tahun($id);
        if ($tahun === null) {
            return $this->tidakAda();
        }
        if ($tahun['status'] !== 'active') {
            $db   = Roster::db();
            $lama = Roster::tahunAktif();
            $t    = Roster::t('academic_years');
            Roster::mulai();
            $db->query("UPDATE {$t} SET status = 'archived', updated_at = ? WHERE status = 'active'", [Clock::now()]);
            $db->query("UPDATE {$t} SET status = 'active', updated_at = ? WHERE id = ?", [Clock::now(), $id]);
            Roster::audit('tahun.diaktifkan', $this->pelaku(), 'academic_year', $id, [
                'label'            => $tahun['label'],
                'diarsipkan_id'    => $lama['id'] ?? null,
                'diarsipkan_label' => $lama['label'] ?? null,
            ]);
            $db->transComplete();
        }

        return redirect()->to('/admin/tahun-ajaran?pesan=diaktifkan');
    }

    // ---- Alat tahun ajaran baru (SPEC.md §5): ringkasan dulu, baru dijalankan. ----

    public function baruForm(): ResponseInterface
    {
        return $this->baruHalaman(['label' => '', 'starts_on' => '', 'ends_on' => ''], [], null);
    }

    /**
     * Langkah 1: ringkasan apa yang akan disalin. Tidak menyimpan apa pun.
     */
    public function baruRingkas(): ResponseInterface
    {
        [$isi, $galat] = $this->periksa(null);
        $lama          = Roster::tahunAktif();
        if ($galat !== [] || $lama === null) {
            return $this->baruHalaman($isi, $galat, null, 422);
        }

        return $this->baruHalaman($isi, [], $this->ringkasanSalin((int) $lama['id']));
    }

    /**
     * Langkah 2: membuat tahun baru, menyalin kelas (yang tidak diarsipkan) dan
     * penugasan pengajarnya TANPA pendaftaran, mengarsipkan tahun lama dan
     * mengaktifkan yang baru — semuanya dalam satu transaksi.
     *
     * Pendaftaran di tahun lama tidak diubah (keputusan pemilik): mahasiswa
     * yang masih AKTIF di sana hanya diperingatkan di ringkasan.
     */
    public function baruJalankan(): ResponseInterface
    {
        [$isi, $galat] = $this->periksa(null);
        $lama          = Roster::tahunAktif();
        if ($galat !== [] || $lama === null) {
            return $this->baruHalaman($isi, $galat, null, 422);
        }
        $lamaId = (int) $lama['id'];
        $pelaku = $this->pelaku();
        $kini   = Clock::now();
        $db     = Roster::db();
        $tahun  = Roster::t('academic_years');
        $kelasT = Roster::t('classes');
        $ajarT  = Roster::t('class_teachers');

        Roster::mulai();
        $db->table($tahun)->insert($isi + ['status' => 'archived', 'created_at' => $kini, 'updated_at' => $kini]);
        $baruId = (int) $db->insertID();

        $db->query("
            INSERT INTO {$kelasT} (course_id, academic_year_id, name, capacity, created_at, updated_at)
            SELECT course_id, ?, name, capacity, ?, ? FROM {$kelasT}
            WHERE academic_year_id = ? AND archived_at IS NULL", [$baruId, $kini, $kini, $lamaId]);
        $kelas = $db->affectedRows();

        $db->query("
            INSERT INTO {$ajarT} (class_id, user_id, assigned_by, assigned_at)
            SELECT kb.id, ct.user_id, ?, ?
            FROM {$ajarT} ct
            JOIN {$kelasT} kl ON kl.id = ct.class_id
            JOIN {$kelasT} kb ON kb.course_id = kl.course_id AND kb.name = kl.name AND kb.academic_year_id = ?
            WHERE kl.academic_year_id = ? AND kl.archived_at IS NULL", [$pelaku, $kini, $baruId, $lamaId]);
        $pengajar = $db->affectedRows();

        $db->query("UPDATE {$tahun} SET status = 'archived', updated_at = ? WHERE id = ?", [$kini, $lamaId]);
        $db->query("UPDATE {$tahun} SET status = 'active', updated_at = ? WHERE id = ?", [$kini, $baruId]);
        Roster::audit('tahun.baru', $pelaku, 'academic_year', $baruId, [
            'label' => $isi['label'], 'dari_id' => $lamaId, 'dari_label' => $lama['label'],
            'kelas_disalin' => $kelas, 'penugasan_disalin' => $pengajar,
        ]);
        $db->transComplete();

        return redirect()->to('/admin/tahun-ajaran?pesan=tahun-baru&n=' . $kelas);
    }

    /**
     * Apa yang akan disalin dari tahun $lamaId, per mata kuliah.
     *
     * @return array{baris: list<array<string, mixed>>, kelas: int, pengajar: int, diarsipkan: int, masihAktif: int}
     */
    private function ringkasanSalin(int $lamaId): array
    {
        $db    = Roster::db();
        $baris = $db->query('
            SELECT c.code, c.name,
                   COUNT(DISTINCT k.id) AS kelas,
                   COUNT(ct.user_id)    AS pengajar
            FROM ' . Roster::t('classes') . ' k
            JOIN ' . Roster::t('courses') . ' c ON c.id = k.course_id
            LEFT JOIN ' . Roster::t('class_teachers') . ' ct ON ct.class_id = k.id
            WHERE k.academic_year_id = ? AND k.archived_at IS NULL
            GROUP BY c.id, c.code, c.name
            ORDER BY c.code', [$lamaId])->getResultArray();

        return [
            'baris'      => $baris,
            'kelas'      => array_sum(array_map(static fn ($b) => (int) $b['kelas'], $baris)),
            'pengajar'   => array_sum(array_map(static fn ($b) => (int) $b['pengajar'], $baris)),
            'diarsipkan' => (int) $db->query('SELECT COUNT(*) AS n FROM ' . Roster::t('classes') . ' WHERE academic_year_id = ? AND archived_at IS NOT NULL', [$lamaId])->getRow()->n,
            'masihAktif' => (int) $db->query('SELECT COUNT(*) AS n FROM ' . Roster::t('enrollments') . " WHERE academic_year_id = ? AND status = 'AKTIF'", [$lamaId])->getRow()->n,
        ];
    }

    /**
     * @param array<string, string>     $isi
     * @param array<string, string>     $galat
     * @param array<string, mixed>|null $ringkasan null = langkah isi form
     */
    private function baruHalaman(array $isi, array $galat, ?array $ringkasan, int $status = 200): ResponseInterface
    {
        return $this->halaman('admin/roster/tahun_baru', [
            'judul'     => lang('Roster.tahunBaru.judul'),
            'lama'      => Roster::tahunAktif(),
            'isi'       => $isi,
            'galat'     => $galat,
            'ringkasan' => $ringkasan,
            'pesan'     => $galat !== [] ? $this->periksaLagi() : [],
        ], $status);
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
            $sama = Roster::db()->table(Roster::t('academic_years'))->where('label', $isi['label']);
            if ($kecuali !== null) {
                $sama->where('id !=', $kecuali);
            }
            if ($sama->countAllResults() > 0) {
                $galat['label'] = 'labelDipakai';
            }
        }
        foreach (['starts_on', 'ends_on'] as $k) {
            if (! Roster::tanggalSah($isi[$k])) {
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
        $kelas = [];
        foreach (Roster::db()->query('SELECT academic_year_id AS t, COUNT(*) AS n FROM ' . Roster::t('classes') . ' GROUP BY academic_year_id')->getResultArray() as $b) {
            $kelas[(int) $b['t']] = (int) $b['n'];
        }

        return $this->halaman('admin/roster/tahun_daftar', [
            'judul' => lang('Roster.nav.tahun'),
            'tahun' => Roster::semuaTahun(),
            'kelas' => $kelas,
            'isi'   => $isi,
            'galat' => $galat,
            'pesan' => $galat !== [] ? $this->periksaLagi() : $this->pesanDariQuery(self::PESAN),
        ], $status);
    }

    /**
     * @param array<string, mixed>  $tahun
     * @param array<string, mixed>  $isi
     * @param array<string, string> $galat
     */
    private function form(array $tahun, array $isi, array $galat, int $status = 200): ResponseInterface
    {
        return $this->halaman('admin/roster/tahun_form', [
            'judul' => lang('Roster.tahun.ubah'),
            'tahun' => $tahun,
            'isi'   => $isi,
            'galat' => $galat,
            'pesan' => $galat !== [] ? $this->periksaLagi() : [],
        ], $status);
    }
}
