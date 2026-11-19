<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\Audit;
use App\Libraries\Papan;
use App\Libraries\Pendaftaran as Layanan;
use App\Libraries\Waktu;
use CodeIgniter\HTTP\ResponseInterface;
use Lokandita\Auth\AuthClient;
use Lokandita\Auth\UserDirectory;

/**
 * Pendaftaran mahasiswa ke kelas (SPEC.md §5): superadmin dan pengampu.
 * Setiap tindakan mulai dari kelasUntukDiubah(); id pendaftaran dari peramban
 * hanya berlaku bila pendaftaran itu memang berada di kelas tersebut.
 */
final class Pendaftaran extends BaseController
{
    private const BATAS_BERKAS = 1024 * 1024;

    /**
     * Langkah 1: pratinjau. Menerima nomor induk dari kolom teks `nomor`
     * dan/atau berkas CSV `berkas`. Tidak menyimpan apa pun.
     */
    public function pratinjau(int $kelasId): ResponseInterface
    {
        $kelas = $this->kelasUntukDiubah($kelasId);
        if ($kelas instanceof ResponseInterface) {
            return $kelas;
        }
        if ($kelas['archived_at'] !== null) {
            return $this->tolakIsian($kelasId, 'kelasArsip');
        }

        $teks   = (string) $this->request->getPost('nomor');
        $berkas = $this->request->getFile('berkas');
        if ($berkas !== null && $berkas->getError() !== UPLOAD_ERR_NO_FILE) {
            if (! $berkas->isValid() || strtolower($berkas->getClientExtension()) !== 'csv' || $berkas->getSize() > self::BATAS_BERKAS) {
                return $this->tolakIsian($kelasId, 'berkasCsv');
            }
            $teks .= "\n" . (string) file_get_contents($berkas->getTempName());
        }

        [$nomor, $salah] = Layanan::bacaNomor($teks);
        if ($nomor === [] && $salah === []) {
            return $this->tolakIsian($kelasId, 'nomorKosong');
        }
        if (count($nomor) > Layanan::BATAS_BARIS) {
            return $this->tolakIsian($kelasId, 'terlaluBanyak');
        }

        $golongan = Layanan::golongkan($kelas, $nomor);
        $aktif    = (int) db_connect()->query("SELECT COUNT(*) AS n FROM enrollments WHERE class_id = ? AND status = 'AKTIF'", [$kelasId])->getRow()->n;
        $akan     = count($golongan['siap']) + count($golongan['aktifkanLagi']);

        return $this->halaman('daftar_pratinjau', [
            'judul'    => lang('Kode.daftar.pratinjau'),
            'kelas'    => $kelas,
            'golongan' => $golongan,
            'salah'    => $salah,
            'akan'     => $akan,
            'melebihi' => $kelas['capacity'] !== null && $aktif + $akan > (int) $kelas['capacity'] ? $aktif + $akan : null,
        ]);
    }

    /**
     * Langkah 2: menjalankan. Menerima `nomor[]` dari halaman pratinjau dan
     * menggolongkannya ulang di server.
     */
    public function jalankan(int $kelasId): ResponseInterface
    {
        $kelas = $this->kelasUntukDiubah($kelasId);
        if ($kelas instanceof ResponseInterface) {
            return $kelas;
        }
        if ($kelas['archived_at'] !== null) {
            return $this->tolakIsian($kelasId, 'kelasArsip');
        }
        $kiriman = $this->request->getPost('nomor');
        [$nomor] = Layanan::bacaNomor(implode("\n", is_array($kiriman) ? array_map('strval', $kiriman) : []));
        if ($nomor === [] || count($nomor) > Layanan::BATAS_BARIS) {
            return $this->tolakIsian($kelasId, 'nomorKosong');
        }

        $hasil = Layanan::jalankan($kelas, $nomor, $this->akses()->identitas()->userId);

        return redirect()->to('/kelas/' . $kelasId . '?pesan=didaftarkan&n=' . ($hasil['didaftarkan'] + $hasil['diaktifkan']));
    }

    /**
     * Menandai pendaftaran terpilih (`ids[]`) sebagai mengundurkan diri.
     */
    public function mundur(int $kelasId): ResponseInterface
    {
        $kelas = $this->kelasUntukDiubah($kelasId);
        if ($kelas instanceof ResponseInterface) {
            return $kelas;
        }
        $n = Layanan::ubahStatus($kelas, (array) $this->request->getPost('ids'), 'AKTIF', 'MENGUNDURKAN_DIRI', 'pendaftaran.mundur');

        return redirect()->to('/kelas/' . $kelasId . '?pesan=' . ($n > 0 ? 'mundur' : 'tidak-ada-pilihan') . '&n=' . $n);
    }

    /**
     * Pindah kelas di dalam satu mata kuliah (SPEC.md §5): `ids[]` dan `tujuan`.
     * Pendaftarannya tetap satu baris, jadi semua yang kelak menunjuk ke
     * pendaftaran itu (instance, submission, nilai) ikut dengan sendirinya.
     *
     * Agregat per kelas dan papan peringkat (SPEC.md §5) tidak disimpan per kelas
     * kecuali di cache papan, yang dibuang di akhir fungsi ini; selebihnya dihitung
     * dari pendaftaran yang sekarang setiap kali dibaca.
     */
    public function pindah(int $kelasId): ResponseInterface
    {
        $kelas = $this->kelasUntukDiubah($kelasId);
        if ($kelas instanceof ResponseInterface) {
            return $kelas;
        }
        if ((int) $this->request->getPost('tujuan') <= 0) {
            return $this->tolakIsian($kelasId, 'tujuanSalah');
        }
        // Kelas tujuan melewati pemeriksaan yang sama dengan kelas asal.
        $tujuan = $this->kelasUntukDiubah((int) $this->request->getPost('tujuan'));
        if ($tujuan instanceof ResponseInterface) {
            return $tujuan;
        }
        if ((int) $tujuan['id'] === $kelasId
            || (int) $tujuan['course_id'] !== (int) $kelas['course_id']
            || (int) $tujuan['academic_year_id'] !== (int) $kelas['academic_year_id']) {
            return $this->tolakIsian($kelasId, 'tujuanSalah');
        }
        if ($tujuan['archived_at'] !== null) {
            return $this->tolakIsian($kelasId, 'tujuanArsip');
        }

        $ids = array_values(array_unique(array_filter(array_map('intval', (array) $this->request->getPost('ids')), static fn ($i) => $i > 0)));
        $n   = 0;
        if ($ids !== []) {
            $db     = db_connect();
            $pelaku = $this->akses()->identitas()->userId;
            $db->transStart();
            $baris = $db->query("SELECT id, user_id FROM enrollments WHERE class_id = ? AND status = 'AKTIF' AND id IN ? FOR UPDATE", [$kelasId, $ids])->getResultArray();
            foreach ($baris as $b) {
                $db->table('enrollments')->where('id', $b['id'])->update(['class_id' => $tujuan['id'], 'updated_at' => Waktu::kini()]);
                $db->table('enrollment_moves')->insert([
                    'enrollment_id' => $b['id'], 'from_class_id' => $kelasId, 'to_class_id' => $tujuan['id'],
                    'moved_by' => $pelaku, 'moved_at' => Waktu::kini(),
                ]);
                Audit::catat('pendaftaran.dipindah', 'user', $b['user_id'], [
                    'enrollment_id' => (int) $b['id'], 'dari' => $kelas['name'], 'ke' => $tujuan['name'], 'dari_id' => $kelasId, 'ke_id' => (int) $tujuan['id'],
                ], (int) $kelas['course_id'], (int) $tujuan['id']);
                $n++;
            }
            $db->transComplete();
            // Nilai mengikuti mahasiswanya (kunci user_id); yang berubah adalah kelas tempat ia dihitung.
            // Papan peringkat disimpan per kelas, jadi dibuang; analitik per kelas selalu dihitung langsung.
            Papan::lupakan();
        }

        return redirect()->to('/kelas/' . $kelasId . '?pesan=' . ($n > 0 ? 'dipindah' : 'tidak-ada-pilihan') . '&n=' . $n);
    }

    /**
     * Kelulusan (SPEC.md §5): `ids[]`, atau `semua=1` untuk seluruh mahasiswa
     * aktif kelas ini, dengan `tanggal` (YYYY-MM-DD). Bisa dibatalkan.
     *
     * Tahap 9: mahasiswa berstatus LULUS tidak boleh lagi mengirim jawaban di kelas ini.
     */
    public function lulus(int $kelasId): ResponseInterface
    {
        $kelas = $this->kelasUntukDiubah($kelasId);
        if ($kelas instanceof ResponseInterface) {
            return $kelas;
        }
        $tanggal = trim((string) $this->request->getPost('tanggal'));
        if (! Waktu::tanggalSah($tanggal)) {
            return $this->tolakIsian($kelasId, 'tanggalLulus');
        }
        $ids = (array) $this->request->getPost('ids');
        if ((string) $this->request->getPost('semua') === '1') {
            $ids = array_column(db_connect()->query("SELECT id FROM enrollments WHERE class_id = ? AND status = 'AKTIF'", [$kelasId])->getResultArray(), 'id');
        }
        $n = Layanan::ubahStatus($kelas, $ids, 'AKTIF', 'LULUS', 'pendaftaran.lulus', ['graduated_on' => $tanggal]);

        return redirect()->to('/kelas/' . $kelasId . '?pesan=' . ($n > 0 ? 'lulus' : 'tidak-ada-pilihan') . '&n=' . $n);
    }

    /**
     * Membatalkan kelulusan `ids[]`: kembali aktif, tanggal lulus dihapus.
     */
    public function batalLulus(int $kelasId): ResponseInterface
    {
        $kelas = $this->kelasUntukDiubah($kelasId);
        if ($kelas instanceof ResponseInterface) {
            return $kelas;
        }
        $n = Layanan::ubahStatus($kelas, (array) $this->request->getPost('ids'), 'LULUS', 'AKTIF', 'pendaftaran.batal-lulus', ['graduated_on' => null]);

        return redirect()->to('/kelas/' . $kelasId . '?pesan=' . ($n > 0 ? 'batal-lulus' : 'tidak-ada-pilihan') . '&n=' . $n);
    }

    /**
     * Reset kata sandi seorang mahasiswa (SPEC.md §3): superadmin, pengampu
     * mata kuliahnya, dan pengajar kelasnya — yaitu siapa pun yang boleh MELIHAT
     * kelas ini, untuk mahasiswa yang AKTIF di kelas ini saja.
     *
     * Dua langkah: tanpa `yakin=1` hanya halaman konfirmasi. Kata sandi
     * sementara ditampilkan sekali dan tidak disimpan atau dicatat di LokaKode.
     */
    public function resetSandi(int $kelasId): ResponseInterface
    {
        $kelas = $this->akademik()->kelas($kelasId);
        if ($kelas === null) {
            return $this->tidakDitemukan('class');
        }
        // Mahasiswa itu harus aktif DI KELAS INI; user_id dari peramban tidak dipercaya.
        $userId = (string) $this->request->getPost('user_id');
        $aktif  = array_filter($this->akademik()->pendaftaranKelas($kelasId), static fn ($e) => $e['user_id'] === $userId && $e['status'] === 'AKTIF');
        $mhs    = $aktif === [] ? null : (UserDirectory::byIds([$userId])[$userId] ?? null);
        if ($mhs === null) {
            return $this->tidakDitemukan('enrollment');
        }

        if ((string) $this->request->getPost('yakin') !== '1') {
            return $this->halaman('sandi_konfirmasi', ['judul' => lang('Kode.sandi.judul'), 'kelas' => $kelas, 'mhs' => $mhs]);
        }

        $pelaku = $this->akses()->identitas()->userId;
        $r      = (new AuthClient())->passwordReset($pelaku, $userId);
        $sandi  = $r['status'] === 200 ? (string) ($r['json']['temp_password'] ?? '') : '';
        if ($sandi === '') {
            // Kegagalan sudah dicatat oleh AuthClient. Katakan terus terang bahwa kata sandi TIDAK berubah.
            $kunci = $r['status'] === 403 ? 'Kode.sandi.bukanMahasiswa' : 'Kode.sandi.gagal';

            return $this->halaman('sandi_konfirmasi', [
                'judul' => lang('Kode.sandi.judul'), 'kelas' => $kelas, 'mhs' => $mhs,
                'pesan' => [['jenis' => 'galat', 'teks' => lang($kunci)]],
            ], $r['status'] === 403 ? 403 : 502);
        }

        Audit::catat('sandi.direset', 'user', $userId, ['kelas' => $kelas['name']], (int) $kelas['course_id'], $kelasId);

        return $this->halaman('sandi_sementara', ['judul' => lang('Kode.sandi.judul'), 'kelas' => $kelas, 'mhs' => $mhs, 'sandi' => $sandi]);
    }

    private function tolakIsian(int $kelasId, string $kunci): ResponseInterface
    {
        return redirect()->to('/kelas/' . $kelasId . '?pesan=' . $kunci);
    }
}
