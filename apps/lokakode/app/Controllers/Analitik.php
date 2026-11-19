<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\Analitik as Pustaka;
use App\Libraries\Audit;
use App\Libraries\Jendela;
use App\Libraries\Latihan;
use App\Libraries\Penilaian;
use App\Libraries\Ruang;
use App\Libraries\Tabel;
use App\Libraries\Waktu;
use CodeIgniter\HTTP\ResponseInterface;
use Lokandita\Auth\UserDirectory;

/**
 * Analitik, kiriman mahasiswa dan ekspor nilai untuk dosen (SPEC.md §3, §12).
 *
 * Per kelas: siapa pun yang melihat kelas itu — superadmin, pengampu, pengajar
 * kelas tersebut. Lintas kelas (perbandingan, ekspor seluruh mata kuliah):
 * superadmin dan pengampu; pengajar yang bukan pengampu 403. Lingkupnya ada di
 * kueri App\Libraries\Analitik; di sini hanya ditentukan jawaban 404 atau 403.
 */
final class Analitik extends BaseController
{
    private const FORMAT = ['csv', 'xlsx'];

    private function pustaka(): Pustaka
    {
        return new Pustaka($this->akses());
    }

    /**
     * Kelas yang boleh dilihat, atau 404 yang sama dengan kelas yang tidak ada.
     *
     * @return array<string, mixed>|ResponseInterface
     */
    private function kelas(int $kelasId): array|ResponseInterface
    {
        return $this->akademik()->kelas($kelasId) ?? $this->tidakDitemukan('class');
    }

    /**
     * Kelas dan pertemuan latihan mata kuliahnya, atau 404.
     *
     * @return array{0: array<string, mixed>, 1: array<string, mixed>}|ResponseInterface
     */
    private function kelasDanPertemuan(int $kelasId, int $pertemuanId): array|ResponseInterface
    {
        $kelas = $this->kelas($kelasId);
        if ($kelas instanceof ResponseInterface) {
            return $kelas;
        }
        $j = (new Jendela($this->akses()))->satu($kelasId, $pertemuanId);

        return $j === null ? $this->tidakDitemukan('meeting') : [$kelas, $j];
    }

    /**
     * Mata kuliah untuk analitik lintas kelas: 404 di luar lingkup, 403 bagi pengajar yang bukan pengampu.
     *
     * @return array<string, mixed>|ResponseInterface
     */
    private function matkulKelola(int $matkulId): array|ResponseInterface
    {
        $matkul = $this->akademik()->matkul($matkulId);
        if ($matkul === null) {
            return $this->tidakDitemukan('course');
        }

        return (int) $matkul['boleh_kelola'] === 1 ? $matkul : $this->terlarang();
    }

    /**
     * @param list<string> $ids
     *
     * @return array<string, array<string, string>>
     */
    private static function orang(array $ids): array
    {
        return UserDirectory::byIds(array_values(array_unique($ids)));
    }

    // ---- satu kelas, semua pertemuan --------------------------------------------------

    public function kelasRingkas(int $kelasId): ResponseInterface
    {
        $kelas = $this->kelas($kelasId);
        if ($kelas instanceof ResponseInterface) {
            return $kelas;
        }
        // Hasil judge yang belum disalin ke kiriman (tab mahasiswa ditutup) diselesaikan dulu.
        Penilaian::selesaikan();
        $a         = $this->pustaka();
        $pertemuan = $a->pertemuanKelas($kelasId);
        $rekap     = $a->rekapKelas($kelasId);

        return $this->halaman('analitik_kelas', [
            'judul'     => lang('Papan.analitik.judul') . ' — ' . $kelas['matkul_kode'] . ' ' . $kelas['name'],
            'kelas'     => $kelas,
            'pertemuan' => $pertemuan,
            'rekap'     => $rekap,
            'berisiko'  => Pustaka::berisiko($pertemuan, $rekap),
            'orang'     => self::orang(array_keys($rekap)),
        ]);
    }

    // ---- satu kelas, satu pertemuan ---------------------------------------------------

    public function pertemuan(int $kelasId, int $pertemuanId): ResponseInterface
    {
        $s = $this->kelasDanPertemuan($kelasId, $pertemuanId);
        if ($s instanceof ResponseInterface) {
            return $s;
        }
        [$kelas, $j] = $s;
        Penilaian::selesaikan();
        $a     = $this->pustaka();
        $nilai = $a->nilaiKelas($kelasId, $pertemuanId);
        // Mahasiswa aktif tanpa kiriman yang dinilai dihitung 0: itulah nilainya saat ini (DECISIONS.md).
        $angka = array_map(static fn (array $n): float => (float) ($n['score'] ?? 0), $nilai);

        return $this->halaman('analitik_pertemuan', [
            'judul'     => lang('Papan.analitik.judul') . ' — ' . lang('Soal.pertemuan.ke', [(int) $j['number']]) . ' ' . $kelas['matkul_kode'] . ' ' . $kelas['name'],
            'kelas'     => $kelas,
            'j'         => $j,
            'keadaan'   => Latihan::keadaan($j),
            'nilai'     => $nilai,
            'statistik' => Pustaka::statistik($angka),
            'belum'     => count(array_filter($nilai, static fn (array $n): bool => $n['score'] === null)),
            'soal'      => $a->soalKelas($kelasId, $pertemuanId),
            'orang'     => self::orang(array_column($nilai, 'user_id')),
        ]);
    }

    public function kirimanDaftar(int $kelasId, int $pertemuanId): ResponseInterface
    {
        $s = $this->kelasDanPertemuan($kelasId, $pertemuanId);
        if ($s instanceof ResponseInterface) {
            return $s;
        }
        [$kelas, $j] = $s;
        Penilaian::selesaikan();
        $mhs  = (string) $this->request->getGet('mahasiswa');
        $mhs  = preg_match('/^[0-9a-f-]{36}$/', $mhs) === 1 ? $mhs : null;
        $soal = (int) $this->request->getGet('soal') > 0 ? (int) $this->request->getGet('soal') : null;
        $daftar = $this->pustaka()->kirimanKelas($kelasId, $pertemuanId, $mhs, $soal);

        return $this->halaman('analitik_kiriman', [
            'judul'   => lang('Papan.kiriman.judul') . ' — ' . lang('Soal.pertemuan.ke', [(int) $j['number']]) . ' ' . $kelas['matkul_kode'] . ' ' . $kelas['name'],
            'kelas'   => $kelas,
            'j'       => $j,
            'daftar'  => $daftar,
            'saring'  => $mhs !== null || $soal !== null,
            'orang'   => self::orang(array_column($daftar, 'user_id')),
        ]);
    }

    public function kiriman(int $kelasId, int $id): ResponseInterface
    {
        $kelas = $this->kelas($kelasId);
        if ($kelas instanceof ResponseInterface) {
            return $kelas;
        }
        Penilaian::selesaikan();
        $k = $this->pustaka()->kiriman($kelasId, $id);
        if ($k === null) {
            return $this->tidakDitemukan('submission');
        }
        $this->response->setHeader('Cache-Control', 'no-store');

        return $this->halaman('analitik_kiriman_lihat', [
            'judul' => lang('Papan.kiriman.satu', [$id]),
            'kelas' => $kelas,
            'k'     => $k,
            'judulSoal' => Ruang::terjemahan(json_decode((string) $k['translations'], true) ?? [])['title'] ?? $k['slug'],
            'tes'   => json_decode((string) ($k['tests'] ?? '[]'), true) ?? [],
            'kurang' => json_decode((string) ($k['missing_concepts'] ?? '[]'), true) ?? [],
            'orang' => self::orang([$k['user_id']])[$k['user_id']] ?? null,
        ]);
    }

    // ---- lintas kelas -----------------------------------------------------------------

    public function matkul(int $matkulId): ResponseInterface
    {
        $matkul = $this->matkulKelola($matkulId);
        if ($matkul instanceof ResponseInterface) {
            return $matkul;
        }
        Penilaian::selesaikan();
        $tahun   = $this->akademik()->tahunAktif();
        $a       = $this->pustaka();
        $banding = $tahun === null ? ['kelas' => [], 'pertemuan' => [], 'sel' => []] : $a->banding($matkulId, (int) $tahun['id']);
        // Kalibrasi (SPEC.md §8.4) untuk satu pertemuan yang dipilih; bawaannya pertemuan terbit yang terakhir.
        $pilih = (int) $this->request->getGet('pertemuan');
        $ada   = array_map(static fn (array $p): int => (int) $p['id'], $banding['pertemuan']);
        if (! in_array($pilih, $ada, true)) {
            $pilih = $ada === [] ? 0 : (int) end($ada);
        }

        return $this->halaman('analitik_matkul', [
            'judul'   => lang('Papan.banding.judul') . ' — ' . $matkul['code'],
            'matkul'  => $matkul,
            'tahun'   => $tahun,
            'banding' => $banding,
            'pilih'   => $pilih,
            'soal'    => $pilih === 0 || $tahun === null ? [] : $a->soalMatkul($pilih, (int) $tahun['id']),
        ]);
    }

    // ---- ekspor -----------------------------------------------------------------------

    public function eksporKelas(int $kelasId, string $format): ResponseInterface
    {
        $kelas = $this->kelas($kelasId);
        if ($kelas instanceof ResponseInterface) {
            return $kelas;
        }
        if (! in_array($format, self::FORMAT, true)) {
            return $this->tidakDitemukan('format');
        }
        Penilaian::selesaikan();
        $a = $this->pustaka();

        return $this->ekspor(
            $format, $kelas['matkul_kode'] . '-' . $kelas['name'] . '-nilai',
            $a->pertemuanKelas($kelasId), $a->rekapKelas($kelasId, ['AKTIF', 'LULUS']),
            (int) $kelas['course_id'], $kelasId,
        );
    }

    public function eksporMatkul(int $matkulId, string $format): ResponseInterface
    {
        $matkul = $this->matkulKelola($matkulId);
        if ($matkul instanceof ResponseInterface) {
            return $matkul;
        }
        $tahun = $this->akademik()->tahunAktif();
        if (! in_array($format, self::FORMAT, true) || $tahun === null) {
            return $this->tidakDitemukan('format');
        }
        Penilaian::selesaikan();
        $a         = $this->pustaka();
        $pertemuan = $a->banding($matkulId, (int) $tahun['id'])['pertemuan'];

        return $this->ekspor(
            $format, $matkul['code'] . '-nilai',
            $pertemuan, $a->rekapMatkul($matkulId, (int) $tahun['id'], ['AKTIF', 'LULUS']),
            $matkulId, null,
        );
    }

    /**
     * Nilai pertemuan per mahasiswa, disusun untuk dijumlahkan di luar sistem (SPEC.md §7, §12):
     * satu baris per mahasiswa, satu kolom nilai per pertemuan terbit. Sel kosong berarti
     * mahasiswa itu belum punya kiriman yang dinilai di pertemuan tersebut. XLSX punya lembar
     * kedua dengan rincian (total poin, surplus, jumlah kiriman) satu baris per mahasiswa per pertemuan.
     *
     * @param list<array<string, mixed>>          $pertemuan
     * @param array<string, array<string, mixed>> $rekap
     */
    private function ekspor(string $format, string $nama, array $pertemuan, array $rekap, int $matkulId, ?int $kelasId): ResponseInterface
    {
        $orang = self::orang(array_keys($rekap));
        $judul = [lang('Papan.nomorInduk'), lang('Papan.nama'), lang('Papan.kelas'), lang('Papan.ekspor.status')];
        foreach ($pertemuan as $p) {
            $judul[] = 'P' . str_pad((string) $p['number'], 2, '0', STR_PAD_LEFT);
        }
        $nilai  = [];
        $rinci  = [];
        foreach ($rekap as $u => $r) {
            $kepala = [$orang[$u]['nomor_induk'] ?? '', $orang[$u]['nama'] ?? '', $r['kelas'], lang('Kode.mhs.statusNama.' . $r['status'])];
            $baris  = $kepala;
            foreach ($pertemuan as $p) {
                $n       = $r['nilai'][(int) $p['id']] ?? null;
                $baris[] = $n === null ? null : $n['nilai'];
                if ($n !== null) {
                    $rinci[] = [$kepala[0], $kepala[1], $kepala[2], (int) $p['number'], (string) $p['title'], $n['nilai'], $n['total'], $n['surplus'], $n['kiriman']];
                }
            }
            $nilai[] = $baris;
        }
        // Urut kelas lalu nomor induk, supaya mudah dicocokkan dengan daftar hadir.
        usort($nilai, static fn (array $a, array $b): int => [$a[2], $a[0]] <=> [$b[2], $b[0]]);
        usort($rinci, static fn (array $a, array $b): int => [$a[2], $a[0], $a[3]] <=> [$b[2], $b[0], $b[3]]);

        // Ekspor memuat data pribadi banyak mahasiswa sekaligus: dicatat siapa yang mengambilnya.
        Audit::catat('nilai.diekspor', $kelasId === null ? 'course' : 'class', $kelasId ?? $matkulId, [
            'format' => $format, 'mahasiswa' => count($nilai), 'pertemuan' => count($pertemuan),
        ], $matkulId, $kelasId);

        $isi = $format === 'csv' ? Tabel::csv($judul, $nilai) : Tabel::xlsx([
            lang('Papan.ekspor.lembarNilai')  => [$judul, $nilai],
            lang('Papan.ekspor.lembarRinci') => [[
                lang('Papan.nomorInduk'), lang('Papan.nama'), lang('Papan.kelas'), lang('Papan.ekspor.pertemuan'), lang('Papan.ekspor.judulPertemuan'),
                lang('Papan.nilai'), lang('Papan.total'), lang('Papan.surplus'), lang('Papan.jumlahKiriman'),
            ], $rinci],
        ]);

        return Tabel::unduh($this->response, $nama . '-' . gmdate('Ymd', strtotime(Waktu::kini()) + 7 * 3600), $format, $isi);
    }
}
