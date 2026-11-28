<?php

declare(strict_types=1);

namespace App\Controllers\Admin\Roster;

use App\Libraries\Roster;
use App\Libraries\RosterDaftar;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Pendaftaran mahasiswa ke kelas (SPEC.md §5). Setiap tindakan mulai dari kelas di URL;
 * id pendaftaran dari peramban hanya berlaku bila pendaftaran itu memang berada di kelas itu.
 */
final class Pendaftaran extends Dasar
{
    private const BATAS_BERKAS = 1024 * 1024;

    /**
     * Langkah 1: pratinjau. Menerima nomor induk dari kolom teks `nomor`
     * dan/atau berkas CSV `berkas`. Tidak menyimpan apa pun.
     */
    public function pratinjau(int $kelasId): ResponseInterface
    {
        $kelas = Roster::kelas($kelasId);
        if ($kelas === null) {
            return $this->tidakAda();
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

        [$nomor, $salah] = RosterDaftar::bacaNomor($teks);
        if ($nomor === [] && $salah === []) {
            return $this->tolakIsian($kelasId, 'nomorKosong');
        }
        if (count($nomor) > RosterDaftar::BATAS_BARIS) {
            return $this->tolakIsian($kelasId, 'terlaluBanyak');
        }

        $golongan = RosterDaftar::golongkan($kelas, $nomor);
        $aktif    = (int) Roster::db()->query('SELECT COUNT(*) AS n FROM ' . Roster::t('enrollments') . " WHERE class_id = ? AND status = 'AKTIF'", [$kelasId])->getRow()->n;
        $akan     = count($golongan['siap']) + count($golongan['aktifkanLagi']);

        return $this->halaman('admin/roster/daftar_pratinjau', [
            'judul'    => lang('Roster.daftar.pratinjau'),
            'kelas'    => $kelas,
            'golongan' => $golongan,
            'salah'    => $salah,
            'akan'     => $akan,
            'melebihi' => $kelas['capacity'] !== null && $aktif + $akan > (int) $kelas['capacity'] ? $aktif + $akan : null,
            'pesan'    => [],
        ]);
    }

    /**
     * Langkah 2: menjalankan. Menerima `nomor[]` dari halaman pratinjau dan
     * menggolongkannya ulang di server.
     */
    public function jalankan(int $kelasId): ResponseInterface
    {
        $kelas = Roster::kelas($kelasId);
        if ($kelas === null) {
            return $this->tidakAda();
        }
        if ($kelas['archived_at'] !== null) {
            return $this->tolakIsian($kelasId, 'kelasArsip');
        }
        $kiriman = $this->request->getPost('nomor');
        [$nomor] = RosterDaftar::bacaNomor(implode("\n", is_array($kiriman) ? array_map(static fn ($v) => is_scalar($v) ? (string) $v : '', $kiriman) : []));
        if ($nomor === [] || count($nomor) > RosterDaftar::BATAS_BARIS) {
            return $this->tolakIsian($kelasId, 'nomorKosong');
        }

        $hasil = RosterDaftar::jalankan($kelas, $nomor, $this->pelaku());

        return redirect()->to('/admin/kelas/' . $kelasId . '?pesan=didaftarkan&n=' . ($hasil['didaftarkan'] + $hasil['diaktifkan']));
    }

    /**
     * Menandai pendaftaran terpilih (`ids[]`) sebagai mengundurkan diri.
     */
    public function mundur(int $kelasId): ResponseInterface
    {
        $kelas = Roster::kelas($kelasId);
        if ($kelas === null) {
            return $this->tidakAda();
        }
        $n = RosterDaftar::ubahStatus($kelas, (array) $this->request->getPost('ids'), 'AKTIF', 'MENGUNDURKAN_DIRI', 'pendaftaran.mundur', $this->pelaku());

        return $this->selesai($kelasId, 'mundur', $n);
    }

    /**
     * Pindah kelas di dalam satu mata kuliah dan tahun ajaran: `ids[]` dan `tujuan`.
     */
    public function pindah(int $kelasId): ResponseInterface
    {
        $kelas = Roster::kelas($kelasId);
        if ($kelas === null) {
            return $this->tidakAda();
        }
        $tujuan = (int) $this->request->getPost('tujuan') > 0 ? Roster::kelas((int) $this->request->getPost('tujuan')) : null;
        if ($tujuan === null
            || (int) $tujuan['id'] === $kelasId
            || (int) $tujuan['course_id'] !== (int) $kelas['course_id']
            || (int) $tujuan['academic_year_id'] !== (int) $kelas['academic_year_id']) {
            return $this->tolakIsian($kelasId, 'tujuanSalah');
        }
        if ($tujuan['archived_at'] !== null) {
            return $this->tolakIsian($kelasId, 'tujuanArsip');
        }
        $n = RosterDaftar::pindah($kelas, $tujuan, (array) $this->request->getPost('ids'), $this->pelaku());

        return $this->selesai($kelasId, 'dipindah', $n);
    }

    /**
     * Kelulusan: `ids[]`, atau `semua=1` untuk seluruh mahasiswa aktif kelas ini,
     * dengan `tanggal` (YYYY-MM-DD). Bisa dibatalkan.
     */
    public function lulus(int $kelasId): ResponseInterface
    {
        $kelas = Roster::kelas($kelasId);
        if ($kelas === null) {
            return $this->tidakAda();
        }
        $tanggal = trim((string) $this->request->getPost('tanggal'));
        if (! Roster::tanggalSah($tanggal)) {
            return $this->tolakIsian($kelasId, 'tanggalLulus');
        }
        $ids = (array) $this->request->getPost('ids');
        if ((string) $this->request->getPost('semua') === '1') {
            $ids = array_column(Roster::db()->query('SELECT id FROM ' . Roster::t('enrollments') . " WHERE class_id = ? AND status = 'AKTIF'", [$kelasId])->getResultArray(), 'id');
        }
        $n = RosterDaftar::ubahStatus($kelas, $ids, 'AKTIF', 'LULUS', 'pendaftaran.lulus', $this->pelaku(), ['graduated_on' => $tanggal]);

        return $this->selesai($kelasId, 'lulus', $n);
    }

    /**
     * Membatalkan kelulusan `ids[]`: kembali aktif, tanggal lulus dihapus.
     */
    public function batalLulus(int $kelasId): ResponseInterface
    {
        $kelas = Roster::kelas($kelasId);
        if ($kelas === null) {
            return $this->tidakAda();
        }
        $n = RosterDaftar::ubahStatus($kelas, (array) $this->request->getPost('ids'), 'LULUS', 'AKTIF', 'pendaftaran.batal-lulus', $this->pelaku(), ['graduated_on' => null]);

        return $this->selesai($kelasId, 'batal-lulus', $n);
    }

    private function selesai(int $kelasId, string $pesan, int $n): ResponseInterface
    {
        return redirect()->to('/admin/kelas/' . $kelasId . '?pesan=' . ($n > 0 ? $pesan : 'tidak-ada-pilihan') . '&n=' . $n);
    }

    private function tolakIsian(int $kelasId, string $kunci): ResponseInterface
    {
        return redirect()->to('/admin/kelas/' . $kelasId . '?pesan=' . $kunci);
    }
}
