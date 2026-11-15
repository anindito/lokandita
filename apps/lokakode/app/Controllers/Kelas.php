<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\Audit;
use App\Libraries\Waktu;
use CodeIgniter\HTTP\ResponseInterface;
use Lokandita\Auth\Aplikasi;
use Lokandita\Auth\UserDirectory;

/**
 * Kelas (SPEC.md §5). Melihat: superadmin, pengampu mata kuliahnya, pengajar
 * kelas itu. Mengubah (nama, kapasitas, arsip, hapus, pengajar): superadmin
 * dan pengampu — lihat BaseController::kelasUntukDiubah().
 */
final class Kelas extends BaseController
{
    private const PESAN = [
        'disimpan'         => ['sukses', 'Kode.pesan.disimpan'],
        'dibuat'           => ['sukses', 'Kode.pesan.kelasDibuat'],
        'diarsipkan'       => ['sukses', 'Kode.pesan.kelasDiarsipkan'],
        'dibuka'           => ['sukses', 'Kode.pesan.kelasDibuka'],
        'pengajar-ditambah' => ['sukses', 'Kode.pesan.pengajarDitambah'],
        'pengajar-dicabut' => ['sukses', 'Kode.pesan.pengajarDicabut'],
        'pengajar-sudah'   => ['galat', 'Kode.pesan.pengajarSudah'],
        // Pendaftaran (Controllers\Pendaftaran). {0} = ?n=<jumlah>.
        'didaftarkan'       => ['sukses', 'Kode.pesan.didaftarkan'],
        'mundur'            => ['sukses', 'Kode.pesan.mundur'],
        'tidak-ada-pilihan' => ['galat', 'Kode.pesan.tidakAdaPilihan'],
        'kelasArsip'        => ['galat', 'Kode.galat.kelasArsip'],
        'berkasCsv'         => ['galat', 'Kode.galat.berkasCsv'],
        'nomorKosong'       => ['galat', 'Kode.galat.nomorKosong'],
        'terlaluBanyak'     => ['galat', 'Kode.galat.terlaluBanyak'],
    ];

    public function lihat(int $id): ResponseInterface
    {
        $kelas = $this->akademik()->kelas($id);
        if ($kelas === null) {
            return $this->tidakDitemukan('class');
        }

        return $this->tampil($kelas, $this->pesanDariQuery(self::PESAN));
    }

    /**
     * Membuat kelas di sebuah mata kuliah. Tahun ajaran: yang aktif; superadmin
     * boleh memilih tahun lain lewat kolom `tahun`.
     */
    public function simpan(int $matkulId): ResponseInterface
    {
        $akademik = $this->akademik();
        $matkul   = $akademik->matkul($matkulId);
        if ($matkul === null) {
            return $this->tidakDitemukan('course');
        }
        if ((int) $matkul['boleh_kelola'] !== 1) {
            return $this->terlarang();
        }
        $tahun = $akademik->tahunAktif();
        if ($this->akses()->superadmin() && (int) $this->request->getPost('tahun') > 0) {
            $tahun = $akademik->tahun((int) $this->request->getPost('tahun'));
        }
        if ($tahun === null) {
            return $this->terlarang('Kode.tahun.belumAda');
        }

        [$isi, $galat] = $this->periksa($matkulId, (int) $tahun['id'], null);
        if ($galat !== []) {
            return redirect()->to('/mata-kuliah/' . $matkulId . '?tahun=' . $tahun['id'] . '&galat=' . reset($galat));
        }

        $db = db_connect();
        $db->transStart();
        $db->table('classes')->insert($isi + [
            'course_id' => $matkulId, 'academic_year_id' => $tahun['id'], 'created_at' => Waktu::kini(), 'updated_at' => Waktu::kini(),
        ]);
        $id = (int) $db->insertID();
        Audit::catat('kelas.dibuat', 'class', $id, $isi + ['tahun' => $tahun['label']], $matkulId, $id);
        $db->transComplete();

        return redirect()->to('/kelas/' . $id . '?pesan=dibuat');
    }

    public function sunting(int $id): ResponseInterface
    {
        $kelas = $this->kelasUntukDiubah($id);
        if ($kelas instanceof ResponseInterface) {
            return $kelas;
        }

        return $this->form($kelas, ['name' => $kelas['name'], 'capacity' => (string) ($kelas['capacity'] ?? '')], []);
    }

    public function perbarui(int $id): ResponseInterface
    {
        $kelas = $this->kelasUntukDiubah($id);
        if ($kelas instanceof ResponseInterface) {
            return $kelas;
        }
        [$isi, $galat] = $this->periksa((int) $kelas['course_id'], (int) $kelas['academic_year_id'], $id);
        if ($galat !== []) {
            return $this->form($kelas, ['name' => $isi['name'], 'capacity' => (string) $this->request->getPost('capacity')], $galat, 422);
        }

        $db = db_connect();
        $db->transStart();
        $db->table('classes')->where('id', $id)->update($isi + ['updated_at' => Waktu::kini()]);
        Audit::catat('kelas.diubah', 'class', $id, [
            'sebelum' => ['name' => $kelas['name'], 'capacity' => $kelas['capacity'] === null ? null : (int) $kelas['capacity']],
            'sesudah' => $isi,
        ], (int) $kelas['course_id'], $id);
        $db->transComplete();

        return redirect()->to('/kelas/' . $id . '?pesan=disimpan');
    }

    /**
     * Mengarsipkan atau membuka kembali kelas (kolom `arsip` = 1 atau 0).
     */
    public function arsip(int $id): ResponseInterface
    {
        $kelas = $this->kelasUntukDiubah($id);
        if ($kelas instanceof ResponseInterface) {
            return $kelas;
        }
        $arsipkan = (string) $this->request->getPost('arsip') === '1';
        if ($arsipkan === ($kelas['archived_at'] !== null)) {
            return redirect()->to('/kelas/' . $id);
        }

        $db = db_connect();
        $db->transStart();
        $db->table('classes')->where('id', $id)->update(['archived_at' => $arsipkan ? Waktu::kini() : null, 'updated_at' => Waktu::kini()]);
        Audit::catat($arsipkan ? 'kelas.diarsipkan' : 'kelas.dibuka', 'class', $id, ['name' => $kelas['name']], (int) $kelas['course_id'], $id);
        $db->transComplete();

        return redirect()->to('/kelas/' . $id . '?pesan=' . ($arsipkan ? 'diarsipkan' : 'dibuka'));
    }

    /**
     * Menghapus kelas — hanya bila kosong. Kelas yang punya pendaftaran (status
     * apa pun) ditolak dengan jumlah data yang bergantung padanya (SPEC.md §5).
     */
    public function hapus(int $id): ResponseInterface
    {
        $kelas = $this->kelasUntukDiubah($id);
        if ($kelas instanceof ResponseInterface) {
            return $kelas;
        }
        $db      = db_connect();
        $daftar  = (int) $db->query('SELECT COUNT(*) AS n FROM enrollments WHERE class_id = ?', [$id])->getRow()->n;
        $riwayat = (int) $db->query('SELECT COUNT(*) AS n FROM enrollment_moves WHERE from_class_id = ? OR to_class_id = ?', [$id, $id])->getRow()->n;
        if ($daftar > 0 || $riwayat > 0) {
            return $this->tampil($kelas, [['jenis' => 'galat', 'teks' => lang('Kode.galat.kelasBerisi', [$daftar, $riwayat])]], 409);
        }
        if ((string) $this->request->getPost('yakin') !== '1') {
            return $this->tampil($kelas, [['jenis' => 'galat', 'teks' => lang('Kode.galat.belumYakin')]], 422);
        }

        $pengajar = $this->akademik()->pengajar([$id])[$id] ?? [];
        $db->transStart();
        $db->table('classes')->where('id', $id)->delete(); // class_teachers ikut terhapus (ON DELETE CASCADE)
        Audit::catat('kelas.dihapus', 'class', $id, [
            'name' => $kelas['name'], 'matkul' => $kelas['matkul_kode'], 'tahun' => $kelas['tahun_label'], 'pengajar' => $pengajar,
        ], (int) $kelas['course_id'], $id);
        $db->transComplete();

        return redirect()->to('/mata-kuliah/' . $kelas['course_id'] . '?tahun=' . $kelas['academic_year_id'] . '&pesan=kelas-dihapus');
    }

    /**
     * Menugaskan seorang dosen sebagai pengajar kelas ini (kolom `nomor_induk`).
     */
    public function tambahPengajar(int $id): ResponseInterface
    {
        $kelas = $this->kelasUntukDiubah($id);
        if ($kelas instanceof ResponseInterface) {
            return $kelas;
        }
        $nomor = trim((string) $this->request->getPost('nomor_induk'));
        $dosen = $nomor === '' ? null : (UserDirectory::byNomorInduk([$nomor])[$nomor] ?? null);
        $galat = match (true) {
            $dosen === null                   => 'pengajarTidakAda',
            $dosen['global_role'] !== 'dosen' => 'pengajarBukanDosen',
            $dosen['status'] !== 'active'     => 'pengajarTidakAktif',
            default                           => null,
        };
        if ($galat !== null) {
            return $this->tampil($kelas, [['jenis' => 'galat', 'teks' => lang('Kode.galat.' . $galat)]], 422, $nomor);
        }

        $db = db_connect();
        if ($db->table('class_teachers')->where(['class_id' => $id, 'user_id' => $dosen['id']])->countAllResults() > 0) {
            return redirect()->to('/kelas/' . $id . '?pesan=pengajar-sudah');
        }
        $db->transStart();
        $db->table('class_teachers')->insert([
            'class_id' => $id, 'user_id' => $dosen['id'], 'assigned_by' => $this->akses()->identitas()->userId, 'assigned_at' => Waktu::kini(),
        ]);
        Audit::catat('pengajar.ditugaskan', 'user', $dosen['id'], ['kelas' => $kelas['name']], (int) $kelas['course_id'], $id);
        $db->transComplete();

        return redirect()->to('/kelas/' . $id . '?pesan=pengajar-ditambah');
    }

    /**
     * Mencabut tugas seorang pengajar dari kelas ini (kolom `user_id`).
     */
    public function hapusPengajar(int $id): ResponseInterface
    {
        $kelas = $this->kelasUntukDiubah($id);
        if ($kelas instanceof ResponseInterface) {
            return $kelas;
        }
        $userId = (string) $this->request->getPost('user_id');
        $db     = db_connect();
        $db->transStart();
        $db->table('class_teachers')->where(['class_id' => $id, 'user_id' => $userId])->delete();
        if ($db->affectedRows() === 1) {
            Audit::catat('pengajar.dicabut', 'user', $userId, ['kelas' => $kelas['name']], (int) $kelas['course_id'], $id);
        }
        $db->transComplete();

        return redirect()->to('/kelas/' . $id . '?pesan=pengajar-dicabut');
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
            $sama = db_connect()->table('classes')->where(['course_id' => $matkulId, 'academic_year_id' => $tahunId, 'name' => $nama]);
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
     * @param array<string, mixed>                       $kelas
     * @param list<array{jenis: string, teks: string}>   $pesan
     */
    private function tampil(array $kelas, array $pesan, int $status = 200, string $nomorPengajar = ''): ResponseInterface
    {
        $id          = (int) $kelas['id'];
        $akademik    = $this->akademik();
        $pengajar    = $akademik->pengajar([$id])[$id] ?? [];
        $pendaftaran = $akademik->pendaftaranKelas($id);
        $idMahasiswa = array_column($pendaftaran, 'user_id');
        $orang       = UserDirectory::byIds(array_merge([$kelas['owner_id']], $pengajar, $idMahasiswa));

        // Urut menurut nomor induk; yang tidak lagi ada di direktori di akhir.
        usort($pendaftaran, static fn ($a, $b) => strcmp($orang[$a['user_id']]['nomor_induk'] ?? '~', $orang[$b['user_id']]['nomor_induk'] ?? '~'));

        return $this->halaman('kelas_lihat', [
            'judul'         => $kelas['matkul_kode'] . ' ' . $kelas['name'],
            'kelas'         => $kelas,
            'bolehUbah'     => $this->bolehUbahKelas($kelas),
            'pengajar'      => $pengajar,
            'pendaftaran'   => $pendaftaran,
            'orang'         => $orang,
            'berakses'      => UserDirectory::withAccess($idMahasiswa, Aplikasi::kode()),
            'nomorPengajar' => $nomorPengajar,
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
        return $this->halaman('kelas_form', [
            'judul' => lang('Kode.kelas.ubah'),
            'kelas' => $kelas,
            'isi'   => $isi,
            'galat' => $galat,
            'pesan' => $galat !== [] ? [['jenis' => 'galat', 'teks' => lang('Kode.pesan.periksa')]] : [],
        ], $status);
    }
}
