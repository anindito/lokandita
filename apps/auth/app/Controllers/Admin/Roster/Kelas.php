<?php

declare(strict_types=1);

namespace App\Controllers\Admin\Roster;

use App\Libraries\Clock;
use App\Libraries\Roster;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Kelas (SPEC.md §5): buat, ubah nama dan kapasitas, arsip, hapus bila kosong,
 * dan penugasan pengajar.
 */
final class Kelas extends Dasar
{
    public const PESAN = [
        'disimpan'          => ['sukses', 'Roster.pesan.disimpan'],
        'dibuat'            => ['sukses', 'Roster.pesan.kelasDibuat'],
        'diarsipkan'        => ['sukses', 'Roster.pesan.kelasDiarsipkan'],
        'dibuka'            => ['sukses', 'Roster.pesan.kelasDibuka'],
        'pengajar-ditambah' => ['sukses', 'Roster.pesan.pengajarDitambah'],
        'pengajar-dicabut'  => ['sukses', 'Roster.pesan.pengajarDicabut'],
        'pengajar-sudah'    => ['galat', 'Roster.pesan.pengajarSudah'],
        // Pendaftaran (Controllers\Admin\Roster\Pendaftaran). {0} = ?n=<jumlah>.
        'didaftarkan'       => ['sukses', 'Roster.pesan.didaftarkan'],
        'mundur'            => ['sukses', 'Roster.pesan.mundur'],
        'tidak-ada-pilihan' => ['galat', 'Roster.pesan.tidakAdaPilihan'],
        'dipindah'          => ['sukses', 'Roster.pesan.dipindah'],
        'lulus'             => ['sukses', 'Roster.pesan.lulus'],
        'batal-lulus'       => ['sukses', 'Roster.pesan.batalLulus'],
        'tujuanSalah'       => ['galat', 'Roster.galat.tujuanSalah'],
        'tujuanArsip'       => ['galat', 'Roster.galat.tujuanArsip'],
        'tanggalLulus'      => ['galat', 'Roster.galat.tanggalLulus'],
        'kelasArsip'        => ['galat', 'Roster.galat.kelasArsip'],
        'berkasCsv'         => ['galat', 'Roster.galat.berkasCsv'],
        'nomorKosong'       => ['galat', 'Roster.galat.nomorKosong'],
        'terlaluBanyak'     => ['galat', 'Roster.galat.terlaluBanyak'],
    ];

    public function lihat(int $id): ResponseInterface
    {
        $kelas = Roster::kelas($id);
        if ($kelas === null) {
            return $this->tidakAda();
        }

        return $this->tampil($kelas, $this->pesanDariQuery(self::PESAN));
    }

    /**
     * Membuat kelas di sebuah mata kuliah. Tahun ajaran: kolom `tahun`, bawaan yang aktif.
     */
    public function simpan(int $matkulId): ResponseInterface
    {
        $matkul = Roster::matkul($matkulId);
        if ($matkul === null) {
            return $this->tidakAda();
        }
        $tahun = (int) $this->request->getPost('tahun') > 0 ? Roster::tahun((int) $this->request->getPost('tahun')) : Roster::tahunAktif();
        if ($tahun === null) {
            return $this->halaman('galat', ['judul' => lang('Lokandita.tidakDitemukan'), 'teks' => lang('Roster.tahun.belumAda')], 404);
        }

        [$isi, $galat] = $this->periksa($matkulId, (int) $tahun['id'], null);
        if ($galat !== []) {
            return redirect()->to('/admin/mata-kuliah/' . $matkulId . '?tahun=' . $tahun['id'] . '&galat=' . reset($galat));
        }

        $db = Roster::db();
        Roster::mulai();
        $db->table(Roster::t('classes'))->insert($isi + [
            'course_id' => $matkulId, 'academic_year_id' => $tahun['id'], 'created_at' => Clock::now(), 'updated_at' => Clock::now(),
        ]);
        $id = (int) $db->insertID();
        Roster::audit('kelas.dibuat', $this->pelaku(), 'class', $id, $isi + ['tahun' => $tahun['label']], $matkulId, $id);
        $db->transComplete();

        return redirect()->to('/admin/kelas/' . $id . '?pesan=dibuat');
    }

    public function sunting(int $id): ResponseInterface
    {
        $kelas = Roster::kelas($id);
        if ($kelas === null) {
            return $this->tidakAda();
        }

        return $this->form($kelas, ['name' => $kelas['name'], 'capacity' => (string) ($kelas['capacity'] ?? '')], []);
    }

    public function perbarui(int $id): ResponseInterface
    {
        $kelas = Roster::kelas($id);
        if ($kelas === null) {
            return $this->tidakAda();
        }
        [$isi, $galat] = $this->periksa((int) $kelas['course_id'], (int) $kelas['academic_year_id'], $id);
        if ($galat !== []) {
            return $this->form($kelas, ['name' => $isi['name'], 'capacity' => (string) $this->request->getPost('capacity')], $galat, 422);
        }

        $db = Roster::db();
        Roster::mulai();
        $db->table(Roster::t('classes'))->where('id', $id)->update($isi + ['updated_at' => Clock::now()]);
        Roster::audit('kelas.diubah', $this->pelaku(), 'class', $id, [
            'sebelum' => ['name' => $kelas['name'], 'capacity' => $kelas['capacity'] === null ? null : (int) $kelas['capacity']],
            'sesudah' => $isi,
        ], (int) $kelas['course_id'], $id);
        $db->transComplete();

        return redirect()->to('/admin/kelas/' . $id . '?pesan=disimpan');
    }

    /**
     * Mengarsipkan atau membuka kembali kelas (kolom `arsip` = 1 atau 0).
     */
    public function arsip(int $id): ResponseInterface
    {
        $kelas = Roster::kelas($id);
        if ($kelas === null) {
            return $this->tidakAda();
        }
        $arsipkan = (string) $this->request->getPost('arsip') === '1';
        if ($arsipkan === ($kelas['archived_at'] !== null)) {
            return redirect()->to('/admin/kelas/' . $id);
        }

        $db = Roster::db();
        Roster::mulai();
        $db->table(Roster::t('classes'))->where('id', $id)->update(['archived_at' => $arsipkan ? Clock::now() : null, 'updated_at' => Clock::now()]);
        Roster::audit($arsipkan ? 'kelas.diarsipkan' : 'kelas.dibuka', $this->pelaku(), 'class', $id, ['name' => $kelas['name']], (int) $kelas['course_id'], $id);
        $db->transComplete();

        return redirect()->to('/admin/kelas/' . $id . '?pesan=' . ($arsipkan ? 'diarsipkan' : 'dibuka'));
    }

    /**
     * Menghapus kelas — hanya bila kosong. Kelas yang punya pendaftaran (status apa pun)
     * atau riwayat pindah ditolak dengan jumlahnya (SPEC.md §5).
     *
     * Data aplikasi lain yang bergantung pada kelas ini tidak bisa dibaca dari sini.
     * Yang milik LokaKode (jendela pertemuan, pengumuman, klarifikasi) dijaga kunci asing
     * lintas skema (KONTRAK.md §18): basis data menolak penghapusannya, dan penolakan
     * itu disampaikan tanpa jumlah.
     */
    public function hapus(int $id): ResponseInterface
    {
        $kelas = Roster::kelas($id);
        if ($kelas === null) {
            return $this->tidakAda();
        }
        $db      = Roster::db();
        $daftar  = (int) $db->query('SELECT COUNT(*) AS n FROM ' . Roster::t('enrollments') . ' WHERE class_id = ?', [$id])->getRow()->n;
        $riwayat = (int) $db->query('SELECT COUNT(*) AS n FROM ' . Roster::t('enrollment_moves') . ' WHERE from_class_id = ? OR to_class_id = ?', [$id, $id])->getRow()->n;
        if ($daftar > 0 || $riwayat > 0) {
            return $this->tampil($kelas, $this->galat(lang('Roster.galat.kelasBerisi', [$daftar, $riwayat])), 409);
        }
        if ((string) $this->request->getPost('yakin') !== '1') {
            return $this->tampil($kelas, $this->galat(lang('Roster.galat.belumYakin')), 422);
        }

        $pengajar = array_column(Roster::pengajar([$id])[$id] ?? [], 'user_id');
        try {
            Roster::mulai();
            $db->table(Roster::t('classes'))->where('id', $id)->delete(); // class_teachers ikut terhapus (ON DELETE CASCADE)
            Roster::audit('kelas.dihapus', $this->pelaku(), 'class', $id, [
                'name' => $kelas['name'], 'matkul' => $kelas['matkul_kode'], 'tahun' => $kelas['tahun_label'], 'pengajar' => $pengajar,
            ], (int) $kelas['course_id'], $id);
            $db->transComplete();
        } catch (DatabaseException $e) {
            // 1451: baris ini masih dirujuk kunci asing — data LokaKode bergantung pada kelas ini.
            // Transaksinya sudah dibatalkan sebelum galat dilempar: kelas dan baris audit tidak berubah.
            if ($e->getCode() !== 1451 && ! str_contains($e->getMessage(), 'foreign key constraint')) {
                throw $e;
            }

            return $this->tampil($kelas, $this->galat(lang('Roster.galat.kelasDipakai')), 409);
        }

        return redirect()->to('/admin/mata-kuliah/' . $kelas['course_id'] . '?tahun=' . $kelas['academic_year_id'] . '&pesan=kelas-dihapus');
    }

    /**
     * Menugaskan seorang dosen sebagai pengajar kelas ini (kolom `nomor_induk`).
     */
    public function tambahPengajar(int $id): ResponseInterface
    {
        $kelas = Roster::kelas($id);
        if ($kelas === null) {
            return $this->tidakAda();
        }
        $nomor = trim((string) $this->request->getPost('nomor_induk'));
        $dosen = Roster::dosenAktif($nomor);
        if (is_string($dosen)) {
            return $this->tampil($kelas, $this->galat(lang('Roster.galat.pengajar' . ucfirst($dosen))), 422, $nomor);
        }

        $db = Roster::db();
        if ($db->table(Roster::t('class_teachers'))->where(['class_id' => $id, 'user_id' => $dosen['id']])->countAllResults() > 0) {
            return redirect()->to('/admin/kelas/' . $id . '?pesan=pengajar-sudah');
        }
        Roster::mulai();
        $db->table(Roster::t('class_teachers'))->insert([
            'class_id' => $id, 'user_id' => $dosen['id'], 'assigned_by' => $this->pelaku(), 'assigned_at' => Clock::now(),
        ]);
        Roster::audit('pengajar.ditugaskan', $this->pelaku(), 'user', $dosen['id'], ['kelas' => $kelas['name']], (int) $kelas['course_id'], $id);
        $db->transComplete();

        return redirect()->to('/admin/kelas/' . $id . '?pesan=pengajar-ditambah');
    }

    /**
     * Mencabut tugas seorang pengajar dari kelas ini (kolom `user_id`). Pengguna yang
     * bukan pengajar kelas ini tidak mengubah apa pun dan tidak dicatat.
     */
    public function hapusPengajar(int $id): ResponseInterface
    {
        $kelas = Roster::kelas($id);
        if ($kelas === null) {
            return $this->tidakAda();
        }
        $userId = (string) $this->request->getPost('user_id');
        $db     = Roster::db();
        Roster::mulai();
        $db->table(Roster::t('class_teachers'))->where(['class_id' => $id, 'user_id' => $userId])->delete();
        if ($db->affectedRows() === 1) {
            Roster::audit('pengajar.dicabut', $this->pelaku(), 'user', $userId, ['kelas' => $kelas['name']], (int) $kelas['course_id'], $id);
        }
        $db->transComplete();

        return redirect()->to('/admin/kelas/' . $id . '?pesan=pengajar-dicabut');
    }

    /**
     * @return array{0: array{name: string, capacity: int|null}, 1: array<string, string>}
     */
    private function periksa(int $matkulId, int $tahunId, ?int $kecuali): array
    {
        $nama      = trim((string) $this->request->getPost('name'));
        $kapasitas = trim((string) $this->request->getPost('capacity'));
        $galat     = [];

        if ($nama === '' || mb_strlen($nama) > 60) {
            $galat['name'] = 'kelasNama';
        } else {
            $sama = Roster::db()->table(Roster::t('classes'))->where(['course_id' => $matkulId, 'academic_year_id' => $tahunId, 'name' => $nama]);
            if ($kecuali !== null) {
                $sama->where('id !=', $kecuali);
            }
            if ($sama->countAllResults() > 0) {
                $galat['name'] = 'kelasNamaDipakai';
            }
        }
        if ($kapasitas !== '' && (preg_match('/^\d{1,5}$/', $kapasitas) !== 1 || (int) $kapasitas < 1)) {
            $galat['capacity'] = 'kelasKapasitas';
        }

        return [['name' => $nama, 'capacity' => $kapasitas === '' ? null : (int) $kapasitas], $galat];
    }

    /**
     * @param array<string, mixed>                     $kelas
     * @param list<array{jenis: string, teks: string}> $pesan
     */
    private function tampil(array $kelas, array $pesan, int $status = 200, string $nomorPengajar = ''): ResponseInterface
    {
        $id          = (int) $kelas['id'];
        $pendaftaran = Roster::pendaftaranKelas($id);

        return $this->halaman('admin/roster/kelas_lihat', [
            'judul'         => $kelas['matkul_kode'] . ' ' . $kelas['name'],
            'kelas'         => $kelas,
            'pengajar'      => Roster::pengajar([$id])[$id] ?? [],
            'pendaftaran'   => $pendaftaran,
            'tanpaAkses'    => Roster::tanpaAkses(array_column($pendaftaran, 'user_id')),
            'nomorPengajar' => $nomorPengajar,
            // Tujuan pindah kelas: kelas lain mata kuliah dan tahun yang sama, yang tidak diarsipkan.
            'tujuanPindah'  => array_values(array_filter(
                Roster::daftarKelas((int) $kelas['academic_year_id'], (int) $kelas['course_id']),
                static fn ($k) => (int) $k['id'] !== $id && $k['archived_at'] === null,
            )),
            'hariIni'       => (new \DateTimeImmutable('now', new \DateTimeZone('Asia/Jakarta')))->format('Y-m-d'),
            'pesan'         => $pesan,
        ], $status);
    }

    /**
     * @param array<string, mixed>  $kelas
     * @param array<string, string> $isi
     * @param array<string, string> $galat
     */
    private function form(array $kelas, array $isi, array $galat, int $status = 200): ResponseInterface
    {
        return $this->halaman('admin/roster/kelas_form', [
            'judul' => lang('Roster.kelas.ubah'),
            'kelas' => $kelas,
            'isi'   => $isi,
            'galat' => $galat,
            'pesan' => $galat !== [] ? $this->periksaLagi() : [],
        ], $status);
    }
}
