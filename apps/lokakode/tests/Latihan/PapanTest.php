<?php

declare(strict_types=1);

namespace Tests\Latihan;

use App\Libraries\Papan;
use Lokandita\Auth\Testing\Fixtures;
use Tests\Support\KodeCase;

/**
 * Papan peringkat (SPEC.md §3, §12): urutan, siapa yang ikut, siapa yang boleh
 * melihat apa, penyamaran, dan penyegaran sesudah nilai atau kelas berubah.
 */
final class PapanTest extends KodeCase
{
    /**
     * Kelas XA berisi mhsA + empat mahasiswa lagi, kelas XB berisi mhsB; pertemuan X1
     * terbit dan terbuka untuk kedua kelas. Nilai disisipkan langsung supaya urutannya pasti.
     *
     * @return array<string, mixed>
     */
    private function duniaPapan(): array
    {
        $d = $this->dunia();
        $d['soal'] = $this->soalSiap($d['pertemuanX1'], 'jumlah-dua-bilangan');
        $this->terbit($d['pertemuanX1']);
        $this->jendela($d['pertemuanX1'], $d['kelasXA']);
        $this->jendela($d['pertemuanX1'], $d['kelasXB']);
        foreach (['Dedi', 'Eka', 'Fajar', 'Gita'] as $nama) {
            $d[$nama] = $this->user('mahasiswa', 'Mahasiswa ' . $nama);
            $d['daftar' . $nama] = $this->daftar($d[$nama], $d['kelasXA']);
        }
        // nilai, total poin, surplus, kiriman, waktu tercapai
        $this->nilai($d, 'mhsA', 80, 48, 0, 9, '2026-11-18 03:00:00');
        $this->nilai($d, 'Dedi', 100, 75, 15, 20, '2026-11-18 05:00:00');    // 100, surplus lebih besar
        $this->nilai($d, 'Eka', 100, 66, 6, 3, '2026-11-18 02:00:00');       // 100, surplus lebih kecil
        $this->nilai($d, 'Fajar', 80, 48, 0, 9, '2026-11-18 02:59:59');      // 80, lebih dulu daripada mhsA
        $this->nilai($d, 'Gita', 80, 48, 0, 4, '2026-11-18 03:00:00');       // 80, waktu sama dengan mhsA, kiriman lebih sedikit
        $this->nilai($d, 'mhsB', 90, 54, 0, 5, '2026-11-18 01:00:00');       // kelas XB
        Papan::lupakan();

        return $d;
    }

    private function nilai(array $d, string $siapa, float $nilai, float $total, float $surplus, int $kiriman, string $pada): void
    {
        Fixtures::insert($this->db, 'meeting_scores', [
            'user_id' => $d[$siapa]['id'], 'meeting_id' => $d['pertemuanX1'], 'total_points' => $total,
            'counted_points' => min($total, 60), 'target_points' => 60, 'score' => $nilai, 'surplus' => $surplus,
            'very_easy_counted' => 0, 'submissions' => $kiriman, 'reached_at' => $pada, 'updated_at' => gmdate('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Baris papan di halaman: [peringkat, sel kedua, sel ketiga, …] sebagai teks.
     *
     * @return list<list<string>>
     */
    private function baris(string $html): array
    {
        $this->assertSame(1, preg_match('#<table data-papan>.*?<tbody>(.*?)</tbody>#s', $html, $m), 'tabel papan');
        preg_match_all('#<tr[^>]*>(.*?)</tr>#s', $m[1], $tr);

        return array_map(static function (string $isi): array {
            preg_match_all('#<td[^>]*>(.*?)</td>#s', $isi, $td);

            return array_map(static fn (string $s): string => trim(html_entity_decode(strip_tags($s))), $td[1]);
        }, $tr[1]);
    }

    public function testUrutanNilaiSurplusWaktuLaluJumlahKiriman(): void
    {
        $d    = $this->duniaPapan();
        $http = $this->sebagai($d['pengajarA']);
        $r    = $http->get('/kelas/' . $d['kelasXA'] . '/pertemuan/' . $d['pertemuanX1'] . '/papan');
        $this->assertSame(200, $r->status);

        $baris = $this->baris($r->body);
        $this->assertSame(
            [['1', 'Mahasiswa Dedi'], ['2', 'Mahasiswa Eka'], ['3', 'Mahasiswa Fajar'], ['4', 'Mahasiswa Gita'], ['5', 'Mahasiswa Ani']],
            array_map(static fn (array $b): array => [$b[0], $b[2]], $baris),
        );
        // Dosen melihat nomor induk, nilai, total poin, surplus, jumlah kiriman dan waktu (WIB).
        $this->assertSame([$d['Dedi']['nomor_induk'], 'Mahasiswa Dedi', '100', '75', '15', '20', '18-11-2026 12:00 WIB'], array_slice($baris[0], 1));
        $this->assertStringContainsString('5 dari 5 mahasiswa aktif', $r->body);
        $this->assertStringNotContainsString('Mahasiswa Budi', $r->body, 'mahasiswa kelas XB tidak ada di papan kelas XA');

        // Sama persis di keempat kunci: peringkat yang sama.
        $this->db->query("UPDATE meeting_scores SET submissions = 9, reached_at = '2026-11-18 03:00:00' WHERE user_id IN ('{$d['Fajar']['id']}', '{$d['Gita']['id']}')");
        Papan::lupakan();
        $peringkat = array_column($this->baris($http->get('/kelas/' . $d['kelasXA'] . '/pertemuan/' . $d['pertemuanX1'] . '/papan')->body), 0);
        $this->assertSame(['1', '2', '3', '3', '3'], $peringkat);

        // Hanya pendaftaran AKTIF yang ikut (SPEC.md §5).
        $this->roster->query("UPDATE enrollments SET status = 'MENGUNDURKAN_DIRI' WHERE id = {$d['daftarDedi']}");
        $this->roster->query("UPDATE enrollments SET status = 'LULUS', graduated_on = '2026-11-01' WHERE id = {$d['daftarEka']}");
        Papan::lupakan();
        $r = $http->get('/kelas/' . $d['kelasXA'] . '/pertemuan/' . $d['pertemuanX1'] . '/papan');
        $this->assertStringNotContainsString('Mahasiswa Dedi', $r->body);
        $this->assertStringNotContainsString('Mahasiswa Eka', $r->body);
        $this->assertSame(['1', '1', '1'], array_column($this->baris($r->body), 0));
    }

    public function testPapanMataKuliahUntukPengampuDanSuperadminSaja(): void
    {
        $d   = $this->duniaPapan();
        $url = '/pertemuan/' . $d['pertemuanX1'] . '/papan';
        foreach (['pengampuX', 'superadmin'] as $siapa) {
            $r = $this->sebagai($d[$siapa])->get($url);
            $this->assertSame(200, $r->status, $siapa);
            $baris = $this->baris($r->body);
            // Lintas kelas: mhsB (90, kelas XB) berada di antara yang 100 dan yang 80; kolom kelas tampil.
            $this->assertSame(['Mahasiswa Dedi', 'Mahasiswa Eka', 'Mahasiswa Budi', 'Mahasiswa Fajar', 'Mahasiswa Gita', 'Mahasiswa Ani'], array_column($baris, 2));
            $this->assertSame(['3', $d['mhsB']['nomor_induk'], 'Mahasiswa Budi', 'XB', '90'], array_slice($baris[2], 0, 5));
        }
        // Pengajar melihat mata kuliahnya tetapi bukan papan lintas kelas: 403 tanpa satu nama pun.
        foreach (['pengajarA', 'pengajarB'] as $siapa) {
            $r = $this->sebagai($d[$siapa])->get($url);
            $this->assertSame(403, $r->status, $siapa);
            $this->assertStringNotContainsString('Mahasiswa', $r->body);
        }
        $this->assertSame(404, $this->sebagai($d['pengampuY'])->get($url)->status);

        // Tahun ajaran lain tidak ikut: pendaftaran di kelas tahun yang diarsipkan tidak tampil.
        $lama  = $this->tahun('2025/2026 Genap', 'archived', '2026-02-01', '2026-07-31');
        $kelas = $this->kelas($d['matkulX'], $lama, 'XLama');
        $alumni = $this->user('mahasiswa', 'Mahasiswa Lama');
        $this->daftar($alumni, $kelas);
        $d['alumni'] = $alumni;
        $this->nilai($d, 'alumni', 100, 90, 30, 1, '2026-03-01 00:00:00');
        Papan::lupakan();
        $this->assertStringNotContainsString('Mahasiswa Lama', $this->sebagai($d['pengampuX'])->get($url)->body);
    }

    public function testPengajarKelasLainTidakMelihatPapanKelasIni(): void
    {
        $d   = $this->duniaPapan();
        $url = '/kelas/' . $d['kelasXA'] . '/pertemuan/' . $d['pertemuanX1'] . '/papan';
        // Papan sudah tersimpan di cache oleh pengajar yang berhak; yang tidak berhak tetap tidak mendapatnya.
        $this->assertSame(200, $this->sebagai($d['pengajarA'])->get($url)->status);

        $http  = $this->sebagai($d['pengajarB']);
        $r     = $http->get($url);
        $tolak = self::inti($r->body, $url);
        $this->assertSame(404, $r->status);
        foreach (['Mahasiswa', 'XA', $d['mhsA']['nomor_induk']] as $bocor) {
            $this->assertStringNotContainsString($bocor, $r->body);
        }
        $tiada = $http->get('/kelas/999999/pertemuan/' . $d['pertemuanX1'] . '/papan');
        $this->assertSame(404, $tiada->status);
        $this->assertSame($tolak, self::inti($tiada->body, '/kelas/999999/pertemuan/' . $d['pertemuanX1'] . '/papan'));
        // Kelasnya sendiri dengan pertemuan mata kuliah lain: tidak ada.
        $this->assertSame(404, $http->get('/kelas/' . $d['kelasXB'] . '/pertemuan/' . $d['pertemuanY1'] . '/papan')->status);
        $this->assertSame(200, $http->get('/kelas/' . $d['kelasXB'] . '/pertemuan/' . $d['pertemuanX1'] . '/papan')->status);
    }

    public function testMahasiswaHanyaMelihatPapanKelasnyaSendiriTanpaNomorInduk(): void
    {
        $d    = $this->duniaPapan();
        $url  = '/latihan/' . $d['pertemuanX1'] . '/papan';
        $http = $this->sebagai($d['mhsA']);
        $r    = $http->get($url);
        $this->assertSame(200, $r->status);
        $baris = $this->baris($r->body);
        $this->assertSame(['Mahasiswa Dedi', 'Mahasiswa Eka', 'Mahasiswa Fajar', 'Mahasiswa Gita', 'Mahasiswa Ani (Anda)'], array_column($baris, 1));
        $this->assertSame(['5', 'Mahasiswa Ani (Anda)', '80', '48'], $baris[4]);
        $this->assertStringContainsString('Peringkat Anda: 5 dari 5', $r->body);
        $this->assertStringContainsString('aria-current="true"', $r->body);
        // Tidak ada nomor induk atau id siapa pun, dan tidak ada mahasiswa kelas lain.
        foreach (['mhsA', 'mhsB', 'Dedi', 'Eka', 'Fajar', 'Gita'] as $siapa) {
            $this->assertStringNotContainsString($d[$siapa]['id'], $r->body, "id {$siapa}");
            if ($siapa !== 'mhsA') {   // nomor induknya sendiri ada di menu akun
                $this->assertStringNotContainsString($d[$siapa]['nomor_induk'], $r->body, "nomor induk {$siapa}");
            }
        }
        $this->assertStringNotContainsString('Mahasiswa Budi', $r->body);

        // Mahasiswa kelas XB membuka alamat yang sama: papan kelas XB, bukan XA.
        $lain = $this->sebagai($d['mhsB'])->get($url);
        $this->assertSame([['1', 'Mahasiswa Budi (Anda)', '90', '54']], $this->baris($lain->body));
        $this->assertStringNotContainsString('Mahasiswa Dedi', $lain->body);

        // Rute papan dosen tertutup bagi mahasiswa, juga untuk kelasnya sendiri; mata kuliah lain tidak ada.
        $this->assertSame(403, $http->get('/kelas/' . $d['kelasXA'] . '/pertemuan/' . $d['pertemuanX1'] . '/papan')->status);
        $this->assertSame(403, $http->get('/pertemuan/' . $d['pertemuanX1'] . '/papan')->status);
        $this->assertSame(404, $http->get('/latihan/' . $d['pertemuanY1'] . '/papan')->status);

        // Disamarkan: peserta lain tanpa nama, barisnya sendiri tetap bernama; dosen tetap melihat nama.
        $this->db->query("UPDATE meetings SET anonymize_leaderboard = 1 WHERE id = {$d['pertemuanX1']}");
        $r = $http->get($url);
        $this->assertSame(['Peserta 1', 'Peserta 2', 'Peserta 3', 'Peserta 4', 'Mahasiswa Ani (Anda)'], array_column($this->baris($r->body), 1));
        foreach (['Dedi', 'Eka', 'Fajar', 'Gita'] as $nama) {
            $this->assertStringNotContainsString('Mahasiswa ' . $nama, $r->body);
        }
        $this->assertStringContainsString('Nama peserta lain disamarkan', $r->body);
        $staf = $this->sebagai($d['pengajarA'])->get('/kelas/' . $d['kelasXA'] . '/pertemuan/' . $d['pertemuanX1'] . '/papan');
        $this->assertStringContainsString('Mahasiswa Dedi', $staf->body);
        $this->assertStringContainsString('Mahasiswa melihat papan ini dengan nama peserta lain disamarkan', $staf->body);

        // Belum dibuka untuk kelasnya, atau belum terbit: belum ada papan.
        $this->db->query("UPDATE meeting_class_windows SET override = 'none' WHERE class_id = {$d['kelasXA']}");
        $this->assertSame(404, $http->get($url)->status);
        $this->db->query("UPDATE meeting_class_windows SET override = 'open' WHERE class_id = {$d['kelasXA']}");
        $this->db->query("UPDATE meetings SET published_at = NULL WHERE id = {$d['pertemuanX1']}");
        $this->assertSame(404, $http->get($url)->status);
    }

    public function testPapanDisegarkanSaatKirimanDinilaiDanSaatMahasiswaPindahKelas(): void
    {
        $d    = $this->duniaPapan();
        $http = $this->sebagai($d['pengampuX']);
        $xa   = '/kelas/' . $d['kelasXA'] . '/pertemuan/' . $d['pertemuanX1'] . '/papan';
        $xb   = '/kelas/' . $d['kelasXB'] . '/pertemuan/' . $d['pertemuanX1'] . '/papan';
        $nama = fn (string $url): array => array_column($this->baris($http->get($url)->body), 2);

        $this->assertSame('Mahasiswa Ani', $nama($xa)[4]);
        $this->assertSame(['Mahasiswa Budi'], $nama($xb));

        // Papan memang disimpan: perubahan langsung di tabel, tanpa lewat penilaian, belum terlihat.
        $this->db->query("UPDATE meeting_scores SET score = 100, surplus = 99 WHERE user_id = '{$d['mhsA']['id']}'");
        $this->assertSame('Mahasiswa Ani', $nama($xa)[4]);

        // Sebuah kiriman selesai dinilai (jalur yang sama dengan Submit sungguhan): papan langsung berubah.
        // mhsA kini hanya punya 1 kiriman bernilai 5 dari target 60 → 8,33, tetap terbawah tetapi dengan angka baru.
        $this->kirimanDinilai($d['mhsA'], $d['soal'], $this->instans($d['mhsA'], $d['soal']), 10);
        $baris = $this->baris($http->get($xa)->body);
        $this->assertSame(['5', 'Mahasiswa Ani', '8.33'], [$baris[4][0], $baris[4][2], $baris[4][3]]);

        // Pindah kelas terjadi di Akun Lokandita (tahap A2), yang tidak bisa membuang cache LokaKode:
        // di sini rosternya diubah langsung, TANPA Papan::lupakan(). Dedi (100) pindah ke XB; nilainya
        // ikut, dan kedua papan berubah seketika karena kunci cachenya memuat keadaan pendaftaran.
        $this->roster->query("UPDATE enrollments SET class_id = {$d['kelasXB']}, updated_at = UTC_TIMESTAMP() WHERE id = {$d['daftarDedi']}");
        $this->assertSame(['Mahasiswa Eka', 'Mahasiswa Fajar', 'Mahasiswa Gita', 'Mahasiswa Ani'], $nama($xa));
        $this->assertSame(['Mahasiswa Dedi', 'Mahasiswa Budi'], $nama($xb));
        $this->assertSame('100.00', $this->row("SELECT score FROM meeting_scores WHERE user_id = '{$d['Dedi']['id']}'")['score']);

        // Mengundurkan diri: hilang dari papan seketika.
        $this->roster->query("UPDATE enrollments SET status = 'MENGUNDURKAN_DIRI', updated_at = UTC_TIMESTAMP() WHERE id = {$d['daftarEka']}");
        $this->assertSame(['Mahasiswa Fajar', 'Mahasiswa Gita', 'Mahasiswa Ani'], $nama($xa));
    }
}
