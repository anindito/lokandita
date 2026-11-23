<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\Audit;
use App\Libraries\BankSoal;
use App\Libraries\Instans;
use App\Libraries\Latihan;
use App\Libraries\Penilaian;
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
        'terbit'    => ['sukses', 'Latihan.pesan.terbit'],
        'ditarik'   => ['sukses', 'Latihan.pesan.ditarik'],
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

        return $this->tampil($p, self::isiPokok($p), []);
    }

    /**
     * @param array<string, mixed>  $p
     * @param array<string, string> $isi
     * @param array<string, string> $galat
     * @param array<string, string>|null $latihan      isian form latihan (null = nilai tersimpan)
     * @param array<string, string>      $galatLatihan kolom => kunci galat (Latihan.galat.*)
     */
    private function tampil(array $p, array $isi, array $galat, int $status = 200, ?array $latihan = null, array $galatLatihan = [], bool $gagalTerbit = false): ResponseInterface
    {
        $kur = $this->kurikulum();
        $latihan ??= self::isiLatihan($p);

        return $this->halaman('pertemuan_lihat', [
            'judul'    => $p['matkul_kode'] . ' — ' . lang('Soal.pertemuan.ke', [(int) $p['number']]),
            'p'        => $p,
            'katalog'  => $kur->katalog($isi['language'] !== '' ? $isi['language'] : $p['language']),
            'asal'     => $kur->asalWarisan((int) $p['course_id'], (int) $p['number']),
            'tambahan' => $kur->konsepTambahan((int) $p['id']),
            'soal'     => (new BankSoal($this->akses()))->daftarSoal((int) $p['id']),
            'isi'      => $isi,
            'galat'    => $galat,
            'latihan'      => $latihan,
            'galatLatihan' => $galatLatihan,
            // Kesiapan dihitung dari nilai yang TERSIMPAN: itulah yang berlaku saat diterbitkan.
            'siap'         => Latihan::kesiapan($p),
            'gagalTerbit'  => $gagalTerbit,
            'benderaSah'   => Latihan::benderaSah((string) $p['language']),
            'templatBawaan' => Latihan::templat(['starter_template' => null, 'language' => $p['language']]),
            'pesan'    => $galat !== [] || $galatLatihan !== [] ? [['jenis' => 'galat', 'teks' => lang('Kode.pesan.periksa')]]
                : ($gagalTerbit ? [['jenis' => 'galat', 'teks' => lang('Latihan.terbit.ditolak')]] : $this->pesanDariQuery(self::PESAN)),
        ], $status);
    }

    /**
     * @param array<string, mixed> $p
     *
     * @return array<string, string>
     */
    private static function isiPokok(array $p): array
    {
        return ['number' => (string) $p['number'], 'title' => (string) $p['title'], 'language' => (string) $p['language'], 'enabled' => (string) $p['enabled'],
            'kind' => (string) ($p['kind'] ?? 'latihan')];
    }

    /**
     * @param array<string, mixed> $p
     *
     * @return array<string, string>
     */
    private static function isiLatihan(array $p): array
    {
        $isi = ['target_points' => (string) $p['target_points'], 'starter_template' => (string) ($p['starter_template'] ?? ''),
            'compiler_flags' => (string) $p['compiler_flags'], 'autocomplete' => (string) $p['autocomplete'],
            'practice_after_close' => (string) $p['practice_after_close'], 'anonymize_leaderboard' => (string) $p['anonymize_leaderboard']];
        foreach (Latihan::KOLOM_MIN as $kolom) {
            $isi[$kolom] = (string) $p[$kolom];
        }

        return $isi;
    }

    /**
     * Pengaturan latihan (SPEC.md §6): target, komposisi minimum, templat awal,
     * bendera kompiler, pelengkapan otomatis.
     */
    public function latihan(int $id): ResponseInterface
    {
        $p = $this->pertemuanKelola($id);
        if ($p instanceof ResponseInterface) {
            return $p;
        }
        $isi = [
            'target_points'    => trim((string) $this->request->getPost('target_points')),
            // Akhir baris dari form selalu CRLF; disimpan sebagai LF.
            'starter_template' => str_replace(["\r\n", "\r"], "\n", (string) $this->request->getPost('starter_template')),
            'compiler_flags'   => trim((string) $this->request->getPost('compiler_flags')),
            'autocomplete'     => $this->request->getPost('autocomplete') === '1' ? '1' : '0',
            'practice_after_close'  => $this->request->getPost('practice_after_close') === '1' ? '1' : '0',
            'anonymize_leaderboard' => $this->request->getPost('anonymize_leaderboard') === '1' ? '1' : '0',
        ];
        foreach (Latihan::KOLOM_MIN as $kolom) {
            $isi[$kolom] = trim((string) $this->request->getPost($kolom));
        }

        // Ujian (SPEC.md §6): target selalu jumlah poin soalnya, tidak ada komposisi minimum, dan
        // tidak ada latihan sesudah tutup. Isian form untuk ketiganya diabaikan.
        if (Latihan::ujian($p)) {
            $isi['target_points']        = (string) $p['target_points'];
            $isi['practice_after_close'] = '0';
            foreach (Latihan::KOLOM_MIN as $kolom) {
                $isi[$kolom] = (string) $p[$kolom];
            }
        }

        $galat = [];
        if (preg_match('/^\d{1,4}$/', $isi['target_points']) !== 1 || (int) $isi['target_points'] < 1 || (int) $isi['target_points'] > 1000) {
            $galat['target_points'] = 'target';
        }
        foreach (Latihan::KOLOM_MIN as $kolom) {
            if (preg_match('/^\d{1,2}$/', $isi[$kolom]) !== 1 || (int) $isi[$kolom] > 50) {
                $galat[$kolom] = 'minimum';
            }
        }
        if (mb_strlen($isi['starter_template']) > Latihan::TEMPLAT_MAKS) {
            $galat['starter_template'] = 'templat';
        }
        $bendera = Latihan::bendera($isi['compiler_flags'], (string) $p['language']);
        if ($bendera === null) {
            $galat['compiler_flags'] = $p['language'] === 'python' ? 'benderaPython' : 'bendera';
        }

        $baru = [];
        if ($galat === []) {
            $baru = ['target_points' => (int) $isi['target_points'], 'autocomplete' => (int) $isi['autocomplete'],
                'practice_after_close' => (int) $isi['practice_after_close'], 'anonymize_leaderboard' => (int) $isi['anonymize_leaderboard'],
                'starter_template' => trim($isi['starter_template']) === '' ? null : $isi['starter_template'],
                'compiler_flags' => implode(' ', $bendera ?? [])];
            foreach (Latihan::KOLOM_MIN as $kolom) {
                $baru[$kolom] = (int) $isi[$kolom];
            }
            // Pertemuan yang sudah terbit tidak boleh diubah menjadi tidak memenuhi syarat terbitnya.
            if ($p['published_at'] !== null && ! Latihan::kesiapan($baru + $p)['siap']) {
                $galat['target_points'] = 'melanggarTerbit';
            }
        }
        if ($galat !== []) {
            return $this->tampil($p, self::isiPokok($p), [], 422, $isi, $galat);
        }

        $sebelum = array_intersect_key($p, $baru);
        $db      = db_connect();
        $db->transStart();
        $db->table('meetings')->where('id', $id)->update($baru + ['updated_at' => Waktu::kini()]);
        Audit::catat('pertemuan.latihanDiubah', 'meeting', $id, [
            // Templat bisa panjang: yang dicatat hanya apakah ia diisi.
            'sebelum' => ['starter_template' => $sebelum['starter_template'] !== null] + $sebelum,
            'sesudah' => ['starter_template' => $baru['starter_template'] !== null] + $baru,
        ], (int) $p['course_id']);
        $db->transComplete();

        // Nilai pertemuan bergantung pada target: semua mahasiswa dihitung ulang.
        if ((int) $baru['target_points'] !== (int) $p['target_points']) {
            Penilaian::hitungUlangPertemuan($id);
        }

        return redirect()->to('/pertemuan/' . $id . '?pesan=disimpan');
    }

    /**
     * Menerbitkan pertemuan: ditolak bila komposisi minimum belum terpenuhi atau
     * poin yang tersedia kurang dari 2 × target; halamannya menyebut persis apa
     * yang kurang (SPEC.md §6).
     */
    public function terbitkan(int $id): ResponseInterface
    {
        $p = $this->pertemuanKelola($id);
        if ($p instanceof ResponseInterface) {
            return $p;
        }
        if ($p['published_at'] !== null) {
            return redirect()->to('/pertemuan/' . $id);
        }
        // Ujian: target disamakan dengan jumlah poin soal yang terpasang sebelum terbit.
        if (Latihan::selaraskanTargetUjian($id)) {
            Penilaian::hitungUlangPertemuan($id);
        }
        if ((int) $p['enabled'] !== 1 || ! Latihan::kesiapan($p)['siap']) {
            return $this->tampil($p, self::isiPokok($p), [], 422, null, [], true);
        }

        $db = db_connect();
        $db->transStart();
        // Syarat di dalam UPDATE: dua klik sekaligus menerbitkan sekali.
        $db->query('UPDATE meetings SET published_at = ?, published_by = ?, updated_at = ? WHERE id = ? AND published_at IS NULL',
            [Waktu::kini(), $this->akses()->identitas()->userId, Waktu::kini(), $id]);
        $terbit = $db->affectedRows() === 1;
        if ($terbit) {
            Audit::catat('pertemuan.diterbitkan', 'meeting', $id, ['number' => (int) $p['number']], (int) $p['course_id']);
        }
        $db->transComplete();

        // Kelas yang jendelanya sudah diatur: instansnya mulai disiapkan sekarang (di luar transaksi).
        if ($terbit) {
            $kelas = $db->query('SELECT class_id FROM meeting_class_windows WHERE meeting_id = ?', [$id])->getResultArray();
            foreach ($kelas as $k) {
                Instans::siapkan($id, (int) $k['class_id'], $this->akses()->identitas()->userId);
            }
        }

        return redirect()->to('/pertemuan/' . $id . '?pesan=terbit');
    }

    /**
     * Menarik pertemuan dari mahasiswa. Kiriman, nilai dan instans yang sudah ada tetap tersimpan.
     */
    public function tarik(int $id): ResponseInterface
    {
        $p = $this->pertemuanKelola($id);
        if ($p instanceof ResponseInterface) {
            return $p;
        }
        $db = db_connect();
        $db->transStart();
        $db->query('UPDATE meetings SET published_at = NULL, published_by = NULL, updated_at = ? WHERE id = ? AND published_at IS NOT NULL', [Waktu::kini(), $id]);
        if ($db->affectedRows() === 1) {
            Audit::catat('pertemuan.ditarik', 'meeting', $id, ['number' => (int) $p['number']], (int) $p['course_id']);
        }
        $db->transComplete();

        return redirect()->to('/pertemuan/' . $id . '?pesan=ditarik');
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
            // Permintaan tanpa isian jenis tidak mengubah jenisnya.
            'kind'     => (string) ($this->request->getPost('kind') ?? $p['kind']),
        ];
        $galat = $this->periksa($isi, (int) $p['course_id'], $p);
        // Jenis (latihan atau ujian) menentukan cara nilai dihitung dan apa yang dilihat mahasiswa:
        // hanya bisa diubah selama pertemuan belum terbit dan belum punya kiriman.
        if (! in_array($isi['kind'], ['latihan', 'ujian'], true)) {
            $galat['kind'] = 'jenis';
        } elseif ($isi['kind'] !== $p['kind'] && ($p['published_at'] !== null || db_connect()->query(
            'SELECT COUNT(*) AS n FROM submissions s JOIN meeting_problems mp ON mp.id = s.meeting_problem_id WHERE mp.meeting_id = ?', [$id])->getRow()->n > 0)) {
            $galat['kind'] = 'jenisTerkunci';
        }
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
            'enabled' => (int) $isi['enabled'], 'kind' => $isi['kind'], 'updated_at' => Waktu::kini(),
        ] + ($isi['kind'] === 'ujian' ? ['practice_after_close' => 0, 'anonymize_leaderboard' => 0] : []));
        $db->table('meeting_concepts')->where('meeting_id', $id)->delete();
        foreach ($dipilih as $kode) {
            $db->table('meeting_concepts')->insert(['meeting_id' => $id, 'concept_code' => $kode]);
        }
        Audit::catat('pertemuan.diubah', 'meeting', $id, [
            'sebelum' => ['number' => (int) $p['number'], 'title' => $p['title'], 'language' => $p['language'], 'enabled' => (int) $p['enabled'], 'kind' => $p['kind'], 'konsep' => $sebelum],
            'sesudah' => ['number' => (int) $isi['number'], 'title' => $isi['title'], 'language' => $isi['language'], 'enabled' => (int) $isi['enabled'], 'kind' => $isi['kind'], 'konsep' => $dipilih],
        ], (int) $p['course_id']);
        $db->transComplete();
        Latihan::selaraskanTargetUjian($id);

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
