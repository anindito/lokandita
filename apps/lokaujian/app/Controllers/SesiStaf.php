<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\Audit;
use App\Libraries\Csv;
use App\Libraries\Hasil;
use App\Libraries\Jadwal;
use App\Libraries\Kendali;
use App\Libraries\SebUjian;
use App\Libraries\Sesi;
use App\Libraries\Ujian;
use CodeIgniter\HTTP\ResponseInterface;
use Lokandita\Auth\Seb\Seb;
use Lokandita\Auth\UserDirectory;

/**
 * Sesi dari sisi staf (SPEC-LOKAUJIAN.md §7, §10–§12): menjalankan kedua mode waktu, tampilan
 * langsung per mahasiswa, tambahan waktu, buka ulang, rilis hasil dan unduhan CSV (tahap U5).
 *
 *   mengatur    — memberikan ujian ke kelas, jadwal, hapus sesi: yang mengelola mata kuliahnya;
 *   menjalankan — halaman sesi, buka, tutup, mulai, atur akhir, akhiri, pratinjau: yang melihat
 *                 kelasnya (superadmin, pengampu, pengajar kelas itu).
 * Sesi di luar lingkup dijawab 404, sama dengan nomor yang tidak ada.
 */
final class SesiStaf extends BaseController
{
    private const PESAN = [
        'dibuat'        => ['sukses', 'Sesi.pesan.dibuat'],
        'dijadwalkan'   => ['sukses', 'Sesi.pesan.dijadwalkan'],
        'dibuka'        => ['sukses', 'Sesi.pesan.dibuka'],
        'ditutup'       => ['sukses', 'Sesi.pesan.ditutup'],
        'dimulai'       => ['sukses', 'Sesi.pesan.dimulai'],
        'akhir'         => ['sukses', 'Sesi.pesan.akhir'],
        'diakhiri'      => ['sukses', 'Sesi.pesan.diakhiri'],
        'g_jadwal'      => ['galat', 'Sesi.galat.jadwal'],
        'g_akhir'       => ['galat', 'Sesi.galat.akhir'],
        'g_sudah'       => ['galat', 'Sesi.galat.sudah'],
        'g_mode'        => ['galat', 'Sesi.galat.mode'],
        'g_diarsipkan'  => ['galat', 'Sesi.galat.diarsipkan'],
        'g_ada_pengerjaan' => ['galat', 'Sesi.galat.adaPengerjaan'],
        // Tahap U5.
        'ditambah'           => ['sukses', 'Hasil.pesan.ditambah'],
        'dibuka_ulang'       => ['sukses', 'Hasil.pesan.dibukaUlang'],
        'dirilis'            => ['sukses', 'Hasil.pesan.dirilis'],
        'ditarik'            => ['sukses', 'Hasil.pesan.ditarik'],
        'g_menit'            => ['galat', 'Hasil.galat.menit'],
        'g_kosong'           => ['galat', 'Hasil.galat.kosong'],
        'g_asing'            => ['galat', 'Hasil.galat.asing'],
        'g_sudah_berakhir'   => ['galat', 'Hasil.galat.sudahBerakhir'],
        'g_alasan'           => ['galat', 'Hasil.galat.alasan'],
        'g_dirilis'          => ['galat', 'Hasil.galat.dirilis'],
        'g_masih_berjalan'   => ['galat', 'Hasil.galat.masihBerjalan'],
        'g_masih_bisa_mulai' => ['galat', 'Hasil.galat.masihBisaMulai'],
        'g_tingkat'          => ['galat', 'Hasil.galat.tingkat'],
        'g_belum_usai'       => ['galat', 'Hasil.galat.belumUsai'],
        'g_bentuk'           => ['galat', 'Hasil.galat.bentuk'],
    ];

    private function kendali(): Kendali
    {
        return new Kendali($this->akses(), $this->roster());
    }

    /**
     * Memberikan ujian terbit kepada satu kelas mata kuliah itu.
     */
    public function buat(string $matkulId, string $ujianId): ResponseInterface
    {
        $matkul = $this->matkulDikelola((int) $matkulId);
        if ($matkul instanceof ResponseInterface) {
            return $matkul;
        }
        $kembali = '/mata-kuliah/' . (int) $matkul['id'] . '/ujian/' . (int) $ujianId;
        // Kelas dibaca dari roster DENGAN syarat hak; kelas mata kuliah lain ditolak di Sesi::buat.
        $kelas = $this->roster()->kelas((int) $this->request->getPost('kelas'));
        if ($kelas === null) {
            return redirect()->to($kembali . '?pesan=kelas_asing');
        }
        $hasil = (new Sesi($this->akses()))->buat(
            (int) $matkul['id'],
            (int) $ujianId,
            $kelas,
            Sesi::keUtc((string) $this->request->getPost('buka')),
            Sesi::keUtc((string) $this->request->getPost('tutup')),
        );
        if ($hasil === 'tidak_ada') {
            return $this->tidakDitemukan('exam');
        }

        return redirect()->to($kembali . '?pesan=' . (is_int($hasil) ? 'sesi_dibuat' : $hasil));
    }

    public function lihat(string $id): ResponseInterface
    {
        // Halaman yang membaca hasil memutakhirkan keadaan tersimpan sendiri, supaya tetap benar
        // ketika penjadwal mati (SPEC-LOKAUJIAN.md §7.5).
        Jadwal::lintasan();
        $pustaka = new Sesi($this->akses());
        $sesi    = $pustaka->lihat((int) $id);
        if ($sesi === null) {
            return $this->tidakDitemukan('session');
        }
        $kelas = $this->roster()->kelas((int) $sesi['class_id']);
        if ($kelas === null) {
            return $this->tidakDitemukan('session_class');
        }

        $langsung = $this->kendali()->langsung($sesi);

        return $this->halaman('sesi_lihat', [
            'judul'    => $sesi['title'] . ' — ' . $kelas['matkul_kode'] . ' ' . $kelas['name'],
            'sesi'     => $sesi,
            'kelas'    => $kelas,
            'hitungan' => $pustaka->hitungan((int) $sesi['id']),
            'langsung' => $langsung,
            'denyut'   => Jadwal::denyut(),
            'seb'      => SebUjian::aktif((int) $sesi['exam_id']),
            'sebWajib' => Seb::diberlakukan(),
            'peristiwa' => $pustaka->jumlahPeristiwa((int) $sesi['id']),
            'pesan'    => $this->pesanDariQuery(self::PESAN),
        ]);
    }

    /**
     * Tampilan langsung sebagai JSON, ditanyakan halaman sesi tiap 5 detik (SPEC-LOKAUJIAN.md §10).
     * Tanpa jawaban dan tanpa skor. Seperti halaman sesi, lintasan penjadwal dijalankan dulu,
     * supaya angkanya benar ketika kontainer penjadwal mati.
     */
    public function langsung(string $id): ResponseInterface
    {
        Jadwal::lintasan();
        $sesi = (new Sesi($this->akses()))->lihat((int) $id);
        if ($sesi === null) {
            return $this->tidakDitemukan('session');
        }
        $jam = static fn (?string $utc): ?string => $utc === null ? null : (new \DateTimeImmutable($utc, new \DateTimeZone('UTC')))->setTimezone(new \DateTimeZone('Asia/Jakarta'))->format('H:i:s');

        return $this->response->setStatusCode(200)->setHeader('Cache-Control', 'no-store')->setJSON([
            'kini'      => $jam((string) $sesi['kini']),
            'keadaan'   => $sesi['keadaan'],
            'akhir'     => $sesi['mode'] === 'dikendalikan_dosen' && $sesi['started_at'] !== null ? $jam($sesi['akhir']) : null,
            'penjadwal' => Jadwal::denyut() !== null,
            'usai'      => (int) $sesi['usai'] === 1,
        ] + $this->kendali()->langsung($sesi));
    }

    /**
     * Halaman konfirmasi bersama untuk tindakan yang harus menyebut mahasiswa dan menitnya
     * (SPEC-LOKAUJIAN.md §7.3) atau kelas yang belum selesai (§11).
     *
     * @param array<string, mixed>         $sesi
     * @param list<string>                 $kalimat sudah di-escape
     * @param array<string, string|list<string>> $isian  isian tersembunyi yang dikirim ulang
     */
    private function konfirmasi(array $sesi, string $judul, array $kalimat, string $aksi, array $isian, string $tombol): ResponseInterface
    {
        return $this->halaman('sesi_konfirmasi', ['judul' => $judul, 'sesi' => $sesi, 'kalimat' => $kalimat, 'aksi' => $aksi, 'isian' => $isian, 'tombol' => $tombol]);
    }

    /**
     * @param list<string> $ids
     *
     * @return list<string> "nomor induk — nama", sudah di-escape
     */
    private function sebut(array $ids): array
    {
        $orang = UserDirectory::byIds($ids);

        return array_map(static fn (string $u): string => esc(($orang[$u]['nomor_induk'] ?? '—') . ' — ' . ($orang[$u]['nama'] ?? lang('Ujian.akunHilang'))), $ids);
    }

    private function menit(): int
    {
        $m = trim((string) $this->request->getPost('menit'));

        return preg_match('/^\d{1,4}$/', $m) === 1 ? (int) $m : 0;
    }

    /**
     * Tambahan waktu untuk mahasiswa yang dicentang. Kiriman pertama hanya memeriksa dan
     * menampilkan konfirmasi; yang kedua (yakin=1) menerapkannya.
     */
    public function tambahWaktu(string $id): ResponseInterface
    {
        $ids   = array_values(array_unique(array_filter((array) $this->request->getPost('mahasiswa'), 'is_string')));
        $menit = $this->menit();
        $yakin = $this->request->getPost('yakin') === '1';
        $hasil = $this->kendali()->tambahWaktu((int) $id, $ids, $menit, $yakin);
        if ($hasil !== 'ok' || $yakin) {
            return $this->selesai($id, $hasil, 'ditambah');
        }
        $sesi = (new Sesi($this->akses()))->lihat((int) $id);
        if ($sesi === null) {
            return $this->tidakDitemukan('session');
        }

        return $this->konfirmasi(
            $sesi,
            lang('Hasil.tambah.judul'),
            array_merge([lang('Hasil.tambah.yakin', [$menit, count($ids)])], $this->sebut($ids)),
            '/sesi/' . (int) $id . '/tambah-waktu',
            ['mahasiswa[]' => $ids, 'menit' => (string) $menit],
            lang('Hasil.tambah.kirim', [$menit]),
        );
    }

    /**
     * Buka ulang untuk satu mahasiswa, dengan menit dan alasan; dua langkah seperti tambahan waktu.
     */
    public function bukaUlang(string $id): ResponseInterface
    {
        $u      = (string) $this->request->getPost('mahasiswa');
        $menit  = $this->menit();
        $alasan = (string) $this->request->getPost('alasan');
        $yakin  = $this->request->getPost('yakin') === '1';
        $hasil  = $this->kendali()->bukaUlang((int) $id, $u, $menit, $alasan, $yakin);
        if ($hasil !== 'ok' || $yakin) {
            return $this->selesai($id, $hasil, 'dibuka_ulang');
        }
        $sesi = (new Sesi($this->akses()))->lihat((int) $id);
        if ($sesi === null) {
            return $this->tidakDitemukan('session');
        }

        return $this->konfirmasi(
            $sesi,
            lang('Hasil.buka.judul'),
            [lang('Hasil.buka.yakin', [$menit]), $this->sebut([$u])[0], lang('Hasil.buka.alasanDisebut', [esc(trim($alasan))])],
            '/sesi/' . (int) $id . '/buka-ulang',
            ['mahasiswa' => $u, 'menit' => (string) $menit, 'alasan' => $alasan],
            lang('Hasil.buka.kirim', [$menit]),
        );
    }

    /**
     * Merilis hasil kelas ini (yang mengelola mata kuliahnya). Merilis kunci selagi kelas lain
     * belum selesai menampilkan peringatan yang menyebut kelasnya dan meminta konfirmasi kedua.
     */
    public function rilis(string $id): ResponseInterface
    {
        Jadwal::lintasan();
        $tingkat = (string) $this->request->getPost('tingkat');
        $pustaka = new Sesi($this->akses());
        $hasil   = $pustaka->rilis((int) $id, $tingkat, $this->request->getPost('yakin') === '1');
        if ($hasil !== 'kelas_lain') {
            return $this->selesai($id, $hasil, 'dirilis');
        }
        $sesi = $pustaka->lihat((int) $id);
        if ($sesi === null) {
            return $this->tidakDitemukan('session');
        }
        $nama = [];
        foreach ($pustaka->kelasBelumUsai((int) $sesi['exam_id'], (int) $sesi['id']) as $k) {
            $nama[] = esc((string) ($this->roster()->kelas($k)['name'] ?? '#' . $k));
        }

        return $this->konfirmasi($sesi, lang('Hasil.rilis.peringatanJudul'), array_merge([lang('Hasil.rilis.peringatan')], $nama), '/sesi/' . (int) $id . '/rilis', ['tingkat' => $tingkat], lang('Hasil.rilis.tetap'));
    }

    public function tarikRilis(string $id): ResponseInterface
    {
        return $this->selesai($id, (new Sesi($this->akses()))->tarikRilis((int) $id), 'ditarik');
    }

    /**
     * Unduhan CSV satu sesi (SPEC-LOKAUJIAN.md §9.3, §12): bentuk `panjang`, `lebar` atau `peristiwa`.
     */
    public function csv(string $id): ResponseInterface
    {
        Jadwal::lintasan();
        $sesi = (new Sesi($this->akses()))->lihat((int) $id);
        if ($sesi === null) {
            return $this->tidakDitemukan('session');
        }
        $bentuk = (string) $this->request->getPost('bentuk');
        if (! in_array($bentuk, ['panjang', 'lebar', 'peristiwa'], true)) {
            return redirect()->to('/sesi/' . (int) $id . '?pesan=g_bentuk');
        }

        return $this->kirimCsv([$sesi], $bentuk, true);
    }

    /**
     * Unduhan CSV semua sesi satu ujian YANG BOLEH DILIHAT pemintanya: pengajar mendapat kelasnya
     * sendiri saja. Ujian tanpa sesi yang dilihatnya dijawab 404, sama dengan yang tidak ada.
     */
    public function csvUjian(string $ujianId): ResponseInterface
    {
        Jadwal::lintasan();
        $sesi = (new Sesi($this->akses()))->dilihatUntukUjian((int) $ujianId);
        if ($sesi === []) {
            return $this->tidakDitemukan('exam_sessions');
        }
        $bentuk = (string) $this->request->getPost('bentuk');
        if (! in_array($bentuk, ['panjang', 'lebar'], true)) {
            return redirect()->to('/sesi/' . (int) $sesi[0]['id'] . '?pesan=g_bentuk');
        }

        return $this->kirimCsv($sesi, $bentuk, false);
    }

    /**
     * @param list<array<string, mixed>> $sesi sesi-sesi satu ujian, sudah dalam lingkup pemintanya
     */
    private function kirimCsv(array $sesi, string $bentuk, bool $satuKelas): ResponseInterface
    {
        $pemisah = Csv::PEMISAH[(string) $this->request->getPost('pemisah')] ?? ';';
        $hasil   = new Hasil($this->akses(), $this->roster());
        [$kepala, $baris] = match ($bentuk) {
            'panjang' => $hasil->panjang($sesi),
            'lebar'   => $hasil->lebar($sesi),
            default   => $hasil->peristiwa($sesi[0]),
        };
        $kelas  = $this->roster()->kelas((int) $sesi[0]['class_id']);
        $bersih = static fn (string $t): string => trim((string) preg_replace('/[^A-Za-z0-9]+/', '-', $t), '-');
        $nama   = implode('-', array_filter([
            'lokaujian', $bersih((string) ($kelas['matkul_kode'] ?? '')), 'ujian' . (int) $sesi[0]['exam_id'],
            $satuKelas ? $bersih((string) ($kelas['name'] ?? '')) : 'semua-kelas',
            (new \DateTimeImmutable('now', new \DateTimeZone('Asia/Jakarta')))->format('Ymd'), $bentuk,
        ], static fn (string $b): bool => $b !== '')) . '.csv';
        Audit::catat('csv.diekspor', 'ujian', (int) $sesi[0]['exam_id'], [
            'bentuk' => $bentuk, 'sesi' => array_map(static fn (array $s): int => (int) $s['id'], $sesi), 'baris' => count($baris),
            'pemisah' => $pemisah === ';' ? 'titik_koma' : 'koma', 'berkas' => $nama,
        ], (int) $sesi[0]['course_id'], $satuKelas ? (int) $sesi[0]['class_id'] : null);

        return $this->response
            ->setStatusCode(200)
            ->setHeader('Content-Type', 'text/csv; charset=utf-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $nama . '"')
            ->setHeader('Cache-Control', 'no-store')
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setBody(Csv::tulis($kepala, $baris, $pemisah));
    }

    /**
     * Peristiwa integritas sesi ini (SPEC-LOKAUJIAN.md §9.3), bagi siapa pun yang boleh
     * menjalankannya. Peristiwa melaporkan; dosen yang memutuskan.
     */
    public function peristiwa(string $id): ResponseInterface
    {
        $pustaka = new Sesi($this->akses());
        $sesi    = $pustaka->lihat((int) $id);
        if ($sesi === null) {
            return $this->tidakDitemukan('session');
        }
        $baris = $pustaka->peristiwa((int) $sesi['id']);

        return $this->halaman('sesi_peristiwa', [
            'judul'     => lang('SebUjian.peristiwa.judul') . ' — ' . $sesi['title'],
            'sesi'      => $sesi,
            'peristiwa' => $baris,
            // Nama dan nomor induk dibaca dari direktori LokaAuth; tidak disalin.
            'orang'     => UserDirectory::byIds(array_values(array_unique(array_map(static fn (array $b): string => (string) $b['user_id'], $baris)))),
        ]);
    }

    /**
     * Pratinjau ujian terbit sebagaimana dilihat mahasiswa, bagi siapa pun yang boleh menjalankan
     * sesi ini — termasuk pengajar, yang tidak punya jalan ke bank soal (SPEC-LOKAUJIAN.md §3).
     * Tanpa kunci jawaban; tidak membuat pengerjaan.
     */
    public function pratinjau(string $id): ResponseInterface
    {
        $sesi = (new Sesi($this->akses()))->lihat((int) $id);
        if ($sesi === null) {
            return $this->tidakDitemukan('session');
        }
        $butir = Ujian::beku(db_connect(), (int) $sesi['exam_id']);

        return $this->halaman('sesi_pratinjau', [
            'judul' => lang('Bank.pratinjau') . ' — ' . $sesi['title'],
            'sesi'  => $sesi,
            'butir' => $butir,
        ]);
    }

    private function selesai(string $id, string $hasil, string $pesanOk): ResponseInterface
    {
        if ($hasil === 'tidak_ada') {
            return $this->tidakDitemukan('session');
        }

        return redirect()->to('/sesi/' . (int) $id . '?pesan=' . ($hasil === 'ok' ? $pesanOk : 'g_' . $hasil));
    }

    public function jadwal(string $id): ResponseInterface
    {
        $hasil = (new Sesi($this->akses()))->jadwal((int) $id, Sesi::keUtc((string) $this->request->getPost('buka')), Sesi::keUtc((string) $this->request->getPost('tutup')));

        return $this->selesai($id, $hasil, 'dijadwalkan');
    }

    public function buka(string $id): ResponseInterface
    {
        return $this->selesai($id, (new Sesi($this->akses()))->buka((int) $id), 'dibuka');
    }

    public function tutup(string $id): ResponseInterface
    {
        return $this->selesai($id, (new Sesi($this->akses()))->tutup((int) $id), 'ditutup');
    }

    public function mulai(string $id): ResponseInterface
    {
        $hasil = (new Sesi($this->akses()))->mulai((int) $id, (string) $this->request->getPost('menit'), (string) $this->request->getPost('jam'));

        return $this->selesai($id, $hasil, 'dimulai');
    }

    public function akhir(string $id): ResponseInterface
    {
        $hasil = (new Sesi($this->akses()))->aturAkhir((int) $id, (string) $this->request->getPost('menit'), (string) $this->request->getPost('jam'));

        return $this->selesai($id, $hasil, 'akhir');
    }

    public function akhiri(string $id): ResponseInterface
    {
        return $this->selesai($id, (new Sesi($this->akses()))->akhiri((int) $id), 'diakhiri');
    }

    public function hapus(string $id): ResponseInterface
    {
        $pustaka = new Sesi($this->akses());
        $sesi    = $pustaka->lihat((int) $id);
        $hasil   = $pustaka->hapus((int) $id);
        if ($hasil !== 'ok' || $sesi === null) {
            return $this->selesai($id, $hasil, '');
        }

        return redirect()->to('/mata-kuliah/' . (int) $sesi['course_id'] . '/ujian/' . (int) $sesi['exam_id'] . '?pesan=sesi_dihapus');
    }
}
