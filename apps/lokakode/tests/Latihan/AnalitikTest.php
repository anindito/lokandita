<?php

declare(strict_types=1);

namespace Tests\Latihan;

use App\Libraries\Analitik;
use Lokandita\Auth\Testing\Fixtures;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\Support\KodeCase;

/**
 * Analitik, kiriman mahasiswa dan ekspor nilai untuk dosen (SPEC.md §3, §8.4, §12),
 * lewat HTTP sungguhan dan MySQL sungguhan; siapa yang boleh melihat apa diuji di
 * setiap bagian, selain di tests/Policy/MatriksAksesTest.php.
 */
final class AnalitikTest extends KodeCase
{
    private const SUMBER = "#include <iostream>\n// <script>alert('x')</script>\nint main(){std::cout<<\"rahasia-ani\\n\";}\n";

    /**
     * Kelas XA: mhsA + Dedi, Eka, Fajar, Gita. Kelas XB: mhsB. Pertemuan X1 dan X2 terbit,
     * jendelanya untuk XA sudah TUTUP menurut jadwal; untuk XB terbuka.
     *
     * @return array<string, mixed>
     */
    private function duniaAnalitik(): array
    {
        $d = $this->dunia();
        $d['soal']  = $this->soalSiap($d['pertemuanX1'], 'jumlah-dua-bilangan', 'MUDAH');
        $d['soal2'] = $this->soalSiap($d['pertemuanX2'], 'selisih-dua-bilangan', 'MUDAH');
        foreach ([$d['pertemuanX1'], $d['pertemuanX2']] as $p) {
            $this->terbit($p);
            $this->jendela($p, $d['kelasXA'], ['override' => 'none', 'open_at' => '2020-01-01 00:00:00', 'close_at' => '2020-01-02 00:00:00']);
            $this->jendela($p, $d['kelasXB']);
        }
        foreach (['Dedi', 'Eka', 'Fajar', 'Gita'] as $nama) {
            $d[$nama] = $this->user('mahasiswa', 'Mahasiswa ' . $nama);
            $d['daftar' . $nama] = $this->daftar($d[$nama], $d['kelasXA']);
        }

        return $d;
    }

    private function nilai(array $d, string $siapa, int $pertemuan, float $nilai, int $kiriman = 1): void
    {
        Fixtures::insert($this->db, 'meeting_scores', [
            'user_id' => $d[$siapa]['id'], 'meeting_id' => $pertemuan, 'total_points' => $nilai * 0.6, 'counted_points' => $nilai * 0.6,
            'target_points' => 60, 'score' => $nilai, 'surplus' => 0, 'very_easy_counted' => 0, 'submissions' => $kiriman,
            'reached_at' => '2026-11-18 03:00:00', 'updated_at' => gmdate('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @return list<list<string>> baris <tbody> sebuah tabel bertanda data-<nama>
     */
    private function tabel(string $html, string $nama): array
    {
        $this->assertSame(1, preg_match('#<table data-' . $nama . '>.*?<tbody>(.*?)</tbody>#s', $html, $m), "tabel {$nama}");
        preg_match_all('#<tr[^>]*>(.*?)</tr>#s', $m[1], $tr);

        return array_map(static function (string $isi): array {
            preg_match_all('#<t[dh][^>]*>(.*?)</t[dh]>#s', $isi, $td);

            return array_map(static fn (string $s): string => trim((string) preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($s)))), $td[1]);
        }, $tr[1]);
    }

    public function testStatistikMedianDanSebaran(): void
    {
        $s = Analitik::statistik([100.0, 80.0, 80.0, 0.0]);
        $this->assertSame([4, 65.0, 80.0, 38.41, 0.0, 100.0], [$s['n'], $s['rata'], $s['median'], $s['simpangan'], $s['min'], $s['maks']]);
        $this->assertSame([1, 0, 0, 0, 0, 0, 0, 0, 2, 1], $s['sebaran']);
        // Batas rentang: 9,99 → 0–9; 10 → 10–19; 89,99 → 80–89; 90 dan 100 → 90–100.
        $this->assertSame([1, 1, 0, 0, 0, 0, 0, 0, 1, 2], Analitik::statistik([9.99, 10.0, 89.99, 90.0, 100.0])['sebaran']);
        $this->assertSame(55.0, Analitik::statistik([10.0, 100.0])['median']);
        $this->assertSame(0.0, Analitik::statistik([70.0])['simpangan']);
        $kosong = Analitik::statistik([]);
        $this->assertSame([0, null, null, null], [$kosong['n'], $kosong['rata'], $kosong['median'], $kosong['simpangan']]);
        $this->assertNull(Analitik::median([]));
        $this->assertSame(2.5, Analitik::median([4, 1, 3, 2]));
    }

    public function testPetunjukKalibrasiHanyaBilaCukupYangMencoba(): void
    {
        // SPEC.md §8.4: soal MUDAH dengan persentase lulus di bawah 30 %.
        $this->assertSame('sulit', Analitik::petunjuk('MUDAH', 5, 29.9));
        $this->assertNull(Analitik::petunjuk('MUDAH', 5, 30.0));
        $this->assertNull(Analitik::petunjuk('MUDAH', 4, 0.0), 'empat mahasiswa belum cukup');
        $this->assertNull(Analitik::petunjuk('MUDAH', 0, null));
        $this->assertSame('sulit', Analitik::petunjuk('MUDAH_SEKALI', 10, 49.0));
        $this->assertNull(Analitik::petunjuk('SULIT', 10, 0.0), 'soal sulit boleh tidak lulus sama sekali');
        $this->assertSame('mudah', Analitik::petunjuk('SULIT', 10, 95.0));
        $this->assertSame('mudah', Analitik::petunjuk('SEDANG', 10, 90.1));
        $this->assertNull(Analitik::petunjuk('MUDAH', 10, 100.0), 'soal mudah yang semua lulus itu wajar');
    }

    public function testSebaranNilaiDanNilaiMahasiswaSatuPertemuan(): void
    {
        $d = $this->duniaAnalitik();
        $this->nilai($d, 'mhsA', $d['pertemuanX1'], 100, 3);
        $this->nilai($d, 'Dedi', $d['pertemuanX1'], 80);
        $this->nilai($d, 'Eka', $d['pertemuanX1'], 80);
        $this->nilai($d, 'Fajar', $d['pertemuanX1'], 45);
        $this->nilai($d, 'mhsB', $d['pertemuanX1'], 10);   // kelas lain: tidak boleh ikut
        // Gita belum mengirim: dihitung 0.
        $url = '/kelas/' . $d['kelasXA'] . '/pertemuan/' . $d['pertemuanX1'] . '/analitik';

        foreach (['pengajarA', 'pengampuX', 'superadmin'] as $siapa) {
            $r = $this->sebagai($d[$siapa])->get($url);
            $this->assertSame(200, $r->status, $siapa);
            // 100, 80, 80, 45, 0 → rata-rata 61, median 80, simpangan baku √(6220/5) = 35,27.
            $this->assertMatchesRegularExpression('#data-rata>61<#', $r->body);
            $this->assertMatchesRegularExpression('#data-median>80<#', $r->body);
            $this->assertMatchesRegularExpression('#data-simpangan>35\.27<#', $r->body);
            $this->assertSame([['1', '0', '0', '0', '1', '0', '0', '0', '2', '1']], $this->tabel($r->body, 'sebaran'));
            $baris = $this->tabel($r->body, 'nilai-mahasiswa');
            $this->assertCount(5, $baris);
            $this->assertSame([$d['mhsA']['nomor_induk'], 'Mahasiswa Ani', '100', '60', '3'], $baris[0]);
            $this->assertSame([$d['Gita']['nomor_induk'], 'Mahasiswa Gita', 'Belum ada kiriman yang dinilai'], $baris[4]);
            $this->assertStringNotContainsString('Mahasiswa Budi', $r->body);
        }
        // Grafik: Chart.js dan skripnya dari host sendiri; CSP halaman ini tidak dilonggarkan.
        $this->assertStringContainsString('<script src="/grafik/chart.umd.min.js', $r->body);
        $this->assertStringContainsString('<script src="/js/grafik.js', $r->body);
        $this->assertStringContainsString("style-src 'self';", (string) $r->header('Content-Security-Policy'));
        $tamu = $this->tamu();
        foreach (['/grafik/chart.umd.min.js', '/grafik/LICENSE.md', '/js/grafik.js'] as $aset) {
            $this->assertSame(200, $tamu->get($aset)->status, $aset);
        }
        $this->assertStringContainsString('Chart.js v4.6.1', $tamu->get('/grafik/chart.umd.min.js')->body);

        // Hanya AKTIF: yang mengundurkan diri keluar dari hitungan.
        $this->db->query("UPDATE enrollments SET status = 'MENGUNDURKAN_DIRI' WHERE id = {$d['daftarFajar']}");
        $r = $this->sebagai($d['pengajarA'])->get($url);
        $this->assertStringNotContainsString('Mahasiswa Fajar', $r->body);
        $this->assertMatchesRegularExpression('#data-rata>65<#', $r->body);

        // Pengajar kelas lain: 404 yang sama dengan kelas yang tidak ada, tanpa satu nama pun.
        $http  = $this->sebagai($d['pengajarB']);
        $r     = $http->get($url);
        $tolak = self::inti($r->body, $url);
        $this->assertSame(404, $r->status);
        $this->assertStringNotContainsString('Mahasiswa', $r->body);
        $lain = '/kelas/999999/pertemuan/' . $d['pertemuanX1'] . '/analitik';
        $this->assertSame($tolak, self::inti($http->get($lain)->body, $lain));
        // Kelasnya sendiri dengan pertemuan mata kuliah lain: tidak ada.
        $this->assertSame(404, $http->get('/kelas/' . $d['kelasXB'] . '/pertemuan/' . $d['pertemuanY1'] . '/analitik')->status);
    }

    public function testStatistikPerSoalDanPetunjukKalibrasi(): void
    {
        $d = $this->duniaAnalitik();
        // Soal MUDAH: lima mahasiswa XA mencoba, satu lulus penuh (20 %) → tampak lebih sulit daripada labelnya.
        foreach (['mhsA' => [10, 1], 'Dedi' => [5, 2], 'Eka' => [5, 1], 'Fajar' => [0, 1], 'Gita' => [2, 3]] as $siapa => [$lulus, $kali]) {
            $ins = $this->instans($d[$siapa], $d['soal']);
            for ($i = 1; $i < $kali; $i++) {
                $this->kirimanDinilai($d[$siapa], $d['soal'], $ins, 0);
            }
            $this->kirimanDinilai($d[$siapa], $d['soal'], $ins, $lulus);
        }
        // Mahasiswa kelas XB lulus penuh: tidak boleh memengaruhi angka kelas XA.
        $this->kirimanDinilai($d['mhsB'], $d['soal'], $this->instans($d['mhsB'], $d['soal']), 10);
        // Kiriman latihan sesudah tutup yang benar semua: tidak dihitung.
        $this->kirimanDinilai($d['Fajar'], $d['soal'], (int) $this->row("SELECT id FROM problem_instances WHERE user_id = '{$d['Fajar']['id']}'")['id'], 10, 10, 'int main(){}', ['practice' => 1]);

        $r = $this->sebagai($d['pengajarA'])->get('/kelas/' . $d['kelasXA'] . '/pertemuan/' . $d['pertemuanX1'] . '/analitik');
        $baris = $this->tabel($r->body, 'statistik-soal');
        $this->assertCount(1, $baris);
        // mencoba 5; lulus penuh 1/5 = 20 %; tes lulus rata-rata (100+50+50+0+20)/5 = 44 %; percobaan (1+2+1+1+3)/5 = 1,6.
        $this->assertSame(['Jumlah Dua Bilangan', 'Mudah', '5', '20%', '44%', '1.6', '2 ms', 'Tampak lebih sulit daripada labelnya'], $baris[0]);

        // Seluruh mata kuliah (pengampu): enam mencoba, dua lulus (33,3 %) → tidak ada catatan lagi.
        $r = $this->sebagai($d['pengampuX'])->get('/mata-kuliah/' . $d['matkulX'] . '/analitik?pertemuan=' . $d['pertemuanX1']);
        $this->assertSame(200, $r->status);
        $baris = $this->tabel($r->body, 'statistik-soal');
        $this->assertSame(['Jumlah Dua Bilangan', 'Mudah', '6', '33.3%'], array_slice($baris[0], 0, 4));
        $this->assertSame('', $baris[0][7]);
    }

    public function testDosenMembacaKirimanDanKodeSumberMahasiswaKelasnyaSaja(): void
    {
        $d    = $this->duniaAnalitik();
        $insA = $this->instans($d['mhsA'], $d['soal']);
        $kA   = $this->kirimanDinilai($d['mhsA'], $d['soal'], $insA, 7, 10, self::SUMBER);
        $kLat = $this->kirimanDinilai($d['mhsA'], $d['soal'], $insA, 10, 10, 'int main(){ /* latihan */ }', ['practice' => 1]);
        $kB   = $this->kirimanDinilai($d['mhsB'], $d['soal'], $this->instans($d['mhsB'], $d['soal']), 10, 10, 'int main(){ /* rahasia-budi */ }');
        $daftar = '/kelas/' . $d['kelasXA'] . '/pertemuan/' . $d['pertemuanX1'] . '/kiriman';
        $satu   = '/kelas/' . $d['kelasXA'] . '/kiriman/' . $kA;

        foreach (['pengajarA', 'pengampuX', 'superadmin'] as $siapa) {
            $http = $this->sebagai($d[$siapa]);
            $r    = $http->get($daftar);
            $this->assertSame(200, $r->status, $siapa);
            $baris = $this->tabel($r->body, 'kiriman');
            $this->assertCount(2, $baris, 'dua kiriman mhsA; kiriman mhsB (kelas XB) tidak ikut');
            $this->assertSame(['Mahasiswa Ani', 'Jumlah Dua Bilangan', 'AC Latihan bebas', '5 / 5', '10 / 10'], array_slice($baris[0], 1, 5));
            $this->assertSame(['Mahasiswa Ani', 'Jumlah Dua Bilangan', 'WA', '3.5 / 5', '7 / 10'], array_slice($baris[1], 1, 5));
            $this->assertStringNotContainsString('rahasia-ani', $r->body, 'daftar tidak memuat kode sumber');
            $this->assertStringNotContainsString('Mahasiswa Budi', $r->body);

            $r = $http->get($satu);
            $this->assertSame(200, $r->status, $siapa);
            $this->assertStringContainsString('rahasia-ani', $r->body);
            // Kode mahasiswa ditampilkan sebagai teks, tidak pernah dijalankan peramban.
            $this->assertStringContainsString('&lt;script&gt;alert(&#039;x&#039;)&lt;/script&gt;', $r->body);
            $this->assertStringNotContainsString("<script>alert('x')</script>", $r->body);
            $this->assertStringContainsString($d['mhsA']['nomor_induk'], $r->body);
            $this->assertSame(10, preg_match_all('/Tes \d+\s/', $r->body), 'status sepuluh tes tersembunyi');
            $this->assertSame(7, substr_count($r->body, 'verdict-ac ltr">AC'));
            $this->assertStringContainsString('no-store', (string) $r->header('Cache-Control'));
            // Tidak ada isi tes di halaman mana pun.
            foreach (array_slice(self::tesInstans(), 2) as $t) {
                $this->assertStringNotContainsString(trim($t['in']), $r->body);
                $this->assertStringNotContainsString(trim($t['out']), $r->body);
            }
        }
        // Saringan per mahasiswa dan per soal.
        $http = $this->sebagai($d['pengajarA']);
        $this->assertCount(2, $this->tabel($http->get($daftar . '?mahasiswa=' . $d['mhsA']['id'])->body, 'kiriman'));
        $this->assertStringContainsString('Belum ada kiriman.', $http->get($daftar . '?mahasiswa=' . $d['mhsB']['id'])->body);
        $this->assertStringContainsString('Belum ada kiriman.', $http->get($daftar . '?soal=' . $d['soal2']['mp'])->body);
        $this->assertStringContainsString('Tidak dihitung ke nilai.', $http->get('/kelas/' . $d['kelasXA'] . '/kiriman/' . $kLat)->body);

        // Kiriman mahasiswa kelas XB lewat alamat kelas XA: tidak ada, juga bagi pengajar XA.
        $r = $http->get('/kelas/' . $d['kelasXA'] . '/kiriman/' . $kB);
        $this->assertSame(404, $r->status);
        $this->assertStringNotContainsString('rahasia-budi', $r->body);

        // Pengajar B: kelas XA dan isinya tidak ada baginya — jawaban yang sama dengan kiriman yang tidak ada.
        $lain = $this->sebagai($d['pengajarB']);
        $r    = $lain->get($satu);
        $this->assertSame(404, $r->status);
        $this->assertStringNotContainsString('rahasia-ani', $r->body);
        $this->assertStringNotContainsString('Mahasiswa Ani', $r->body);
        $tolak = self::inti($r->body, $satu);
        $tiada = '/kelas/' . $d['kelasXA'] . '/kiriman/999999';
        $this->assertSame($tolak, self::inti($lain->get($tiada)->body, $tiada));
        $this->assertSame(404, $lain->get($daftar)->status);
        // Lewat kelasnya sendiri pun kiriman mhsA tidak terbaca; kiriman mhsB terbaca.
        $this->assertSame(404, $lain->get('/kelas/' . $d['kelasXB'] . '/kiriman/' . $kA)->status);
        $this->assertStringContainsString('rahasia-budi', $lain->get('/kelas/' . $d['kelasXB'] . '/kiriman/' . $kB)->body);
        // Pengampu mata kuliah lain dan mahasiswa.
        $this->assertSame(404, $this->sebagai($d['pengampuY'])->get($satu)->status);
        $mhs = $this->sebagai($d['mhsA']);
        $this->assertSame(403, $mhs->get($satu)->status);
        $this->assertSame(403, $mhs->get($daftar)->status);

        // Pindah kelas: kiriman ikut mahasiswanya. Pengajar A tidak lagi membacanya; pengajar B membacanya.
        $this->db->query("UPDATE enrollments SET class_id = {$d['kelasXB']} WHERE id = {$d['daftarA']}");
        $this->assertSame(404, $http->get($satu)->status);
        $this->assertStringContainsString('rahasia-ani', $lain->get('/kelas/' . $d['kelasXB'] . '/kiriman/' . $kA)->body);
    }

    public function testMahasiswaBerisikoDanRekapKelas(): void
    {
        $d = $this->duniaAnalitik();
        // X1 dan X2 sudah tutup untuk kelas XA.
        $this->nilai($d, 'mhsA', $d['pertemuanX1'], 90);
        $this->nilai($d, 'mhsA', $d['pertemuanX2'], 49.99);          // rendah sekali saja: tidak berisiko
        $this->nilai($d, 'Dedi', $d['pertemuanX1'], 20);
        $this->nilai($d, 'Dedi', $d['pertemuanX2'], 49.99);          // rendah dua kali: berisiko
        $this->nilai($d, 'Eka', $d['pertemuanX1'], 100);             // tidak mengirim di X2: berisiko
        $this->nilai($d, 'Fajar', $d['pertemuanX1'], 50);
        $this->nilai($d, 'Fajar', $d['pertemuanX2'], 50);            // tepat 50 bukan rendah
        // Gita tidak mengirim di keduanya: berisiko, paling atas.
        $url = '/kelas/' . $d['kelasXA'] . '/analitik';
        $r   = $this->sebagai($d['pengajarA'])->get($url);
        $this->assertSame(200, $r->status);
        $this->assertSame([
            [$d['Gita']['nomor_induk'], 'Mahasiswa Gita', '1, 2', '—'],
            [$d['Eka']['nomor_induk'], 'Mahasiswa Eka', '2', '—'],
            [$d['Dedi']['nomor_induk'], 'Mahasiswa Dedi', '—', '1, 2'],
        ], $this->tabel($r->body, 'berisiko'));
        $rekap = $this->tabel($r->body, 'rekap');
        $this->assertCount(5, $rekap);
        $ani = array_values(array_filter($rekap, static fn (array $b): bool => $b[1] === 'Mahasiswa Ani'))[0];
        $this->assertSame(['90', '49.99'], array_slice($ani, 2));

        // Pertemuan yang masih terbuka tidak dipakai menilai risiko: dibuka manual lagi → Eka tidak lagi berisiko.
        $this->db->query("UPDATE meeting_class_windows SET override = 'open' WHERE meeting_id = {$d['pertemuanX2']} AND class_id = {$d['kelasXA']}");
        $baris = $this->tabel($this->sebagai($d['pengajarA'])->get($url)->body, 'berisiko');
        $this->assertSame(['Mahasiswa Gita'], array_column($baris, 1));

        $lain = $this->sebagai($d['pengajarB'])->get($url);
        $this->assertSame(404, $lain->status);
        $this->assertStringNotContainsString('Mahasiswa', $lain->body);
    }

    public function testEksporNilaiCsvDanXlsxDicatatDanHanyaUntukYangBerhak(): void
    {
        $d = $this->duniaAnalitik();
        // Nama yang bisa dibaca lembar kerja sebagai rumus.
        $this->idn->query("UPDATE users SET nama = '=HYPERLINK(\"http://jahat\")' WHERE id = '{$d['Dedi']['id']}'");
        $this->nilai($d, 'mhsA', $d['pertemuanX1'], 100, 4);
        $this->nilai($d, 'mhsA', $d['pertemuanX2'], 62.5);
        $this->nilai($d, 'Dedi', $d['pertemuanX1'], 80);
        $this->nilai($d, 'Eka', $d['pertemuanX2'], 30);
        $this->nilai($d, 'Fajar', $d['pertemuanX1'], 70);
        $this->nilai($d, 'mhsB', $d['pertemuanX1'], 55);
        $this->db->query("UPDATE enrollments SET status = 'LULUS', graduated_on = '2026-11-01' WHERE id = {$d['daftarEka']}");
        $this->db->query("UPDATE enrollments SET status = 'MENGUNDURKAN_DIRI' WHERE id = {$d['daftarFajar']}");
        $dasar = '/kelas/' . $d['kelasXA'] . '/ekspor/';
        $http  = $this->sebagai($d['pengajarA']);

        // CSV: satu baris per mahasiswa, satu kolom per pertemuan terbit, sel kosong bila belum ada kiriman.
        $r = $http->get($dasar . 'csv');
        $this->assertSame(200, $r->status);
        $this->assertStringContainsString('text/csv', (string) $r->header('Content-Type'));
        $this->assertMatchesRegularExpression('/attachment; filename="UJX101-XA-nilai-\d{8}\.csv"/', (string) $r->header('Content-Disposition'));
        $this->assertStringContainsString('no-store', (string) $r->header('Cache-Control'));
        $this->assertStringStartsWith("\xEF\xBB\xBF", $r->body);
        $baris = array_map(static fn (string $b): array => str_getcsv($b, ',', '"', ''), explode("\n", trim(substr($r->body, 3))));
        $this->assertSame(['Nomor induk', 'Nama', 'Kelas', 'Status', 'P01', 'P02'], $baris[0]);
        $per = [];
        foreach (array_slice($baris, 1) as $b) {
            $per[$b[1]] = $b;
        }
        $this->assertSame(["'=HYPERLINK(\"http://jahat\")", 'Mahasiswa Ani', 'Mahasiswa Eka', 'Mahasiswa Gita'], $this->urut(array_keys($per)));
        $this->assertSame([$d['mhsA']['nomor_induk'], 'Mahasiswa Ani', 'XA', 'Aktif', '100', '62.5'], $per['Mahasiswa Ani']);
        $this->assertSame(['XA', 'Aktif', '80', ''], array_slice($per["'=HYPERLINK(\"http://jahat\")"], 2));
        $this->assertSame(['XA', 'Lulus', '', '30'], array_slice($per['Mahasiswa Eka'], 2));
        $this->assertSame(['XA', 'Aktif', '', ''], array_slice($per['Mahasiswa Gita'], 2));
        $this->assertStringNotContainsString('Mahasiswa Fajar', $r->body, 'yang mengundurkan diri tidak diekspor');
        $this->assertStringNotContainsString('Mahasiswa Budi', $r->body, 'kelas lain tidak ikut');

        // XLSX: angka sebagai angka, nomor induk dan nama sebagai teks (bukan rumus), lembar rincian.
        $r = $http->get($dasar . 'xlsx');
        $this->assertSame(200, $r->status);
        $this->assertStringContainsString('spreadsheetml', (string) $r->header('Content-Type'));
        $berkas = tempnam(sys_get_temp_dir(), 'uji');
        file_put_contents($berkas, $r->body);
        $buku = IOFactory::load($berkas);
        unlink($berkas);
        $this->assertSame(['Nilai', 'Rincian'], $buku->getSheetNames());
        $lembar = $buku->getSheet(0);
        $this->assertSame(['Nomor induk', 'Nama', 'Kelas', 'Status', 'P01', 'P02'], $lembar->rangeToArray('A1:F1')[0]);
        $ditemukan = 0;
        foreach ($lembar->getRowIterator(2) as $row) {
            $i    = $row->getRowIndex();
            $nama = (string) $lembar->getCell("B{$i}")->getValue();
            $this->assertSame(DataType::TYPE_STRING, $lembar->getCell("A{$i}")->getDataType(), 'nomor induk adalah teks');
            if ($nama === 'Mahasiswa Ani') {
                $this->assertSame([DataType::TYPE_NUMERIC, 100.0, 62.5], [$lembar->getCell("E{$i}")->getDataType(), (float) $lembar->getCell("E{$i}")->getValue(), (float) $lembar->getCell("F{$i}")->getValue()]);
                $ditemukan++;
            }
            if (str_starts_with($nama, '=HYPERLINK')) {
                $this->assertSame(DataType::TYPE_STRING, $lembar->getCell("B{$i}")->getDataType(), 'nama yang mirip rumus tetap teks');
                $this->assertNull($lembar->getCell("F{$i}")->getValue(), 'belum ada kiriman: sel kosong');
                $ditemukan++;
            }
        }
        $this->assertSame(2, $ditemukan);
        $this->assertSame(4, $lembar->getHighestRow() - 1);
        $rinci = $buku->getSheet(1)->toArray(null, false, false, false);
        $this->assertSame(['Nomor induk', 'Nama', 'Kelas', 'Pertemuan', 'Judul pertemuan', 'Nilai', 'Total poin', 'Surplus', 'Kiriman'], $rinci[0]);
        $this->assertCount(1 + 4, $rinci, 'satu baris per mahasiswa per pertemuan yang ada nilainya');
        $ani1 = array_values(array_filter($rinci, static fn (array $b): bool => $b[1] === 'Mahasiswa Ani' && (int) $b[3] === 1))[0];
        $this->assertEquals([100, 60, 0, 4], array_slice($ani1, 5));
        $this->assertIsNotString($ani1[5], 'nilai di lembar rincian adalah angka');

        // Dua unduhan, dua baris audit dengan pelaku dan kelasnya.
        $audit = $this->db->query("SELECT actor_id, class_id, course_id, detail FROM audit_log WHERE action = 'nilai.diekspor' ORDER BY id")->fetch_all(MYSQLI_ASSOC);
        $this->assertCount(2, $audit);
        $this->assertSame([$d['pengajarA']['id'], (string) $d['kelasXA'], (string) $d['matkulX']], [$audit[0]['actor_id'], (string) $audit[0]['class_id'], (string) $audit[0]['course_id']]);
        $this->assertSame(['format' => 'csv', 'mahasiswa' => 4, 'pertemuan' => 2], json_decode($audit[0]['detail'], true));

        // Format lain tidak ada; yang tidak berhak tidak mendapat apa pun dan tidak tercatat mengunduh.
        $this->assertSame(404, $http->get($dasar . 'pdf')->status);
        $lain = $this->sebagai($d['pengajarB']);
        foreach (['csv', 'xlsx'] as $f) {
            $r = $lain->get($dasar . $f);
            $this->assertSame(404, $r->status);
            $this->assertStringNotContainsString('Mahasiswa', $r->body);
            $this->assertNull($r->header('Content-Disposition'));
        }
        $this->assertSame(403, $this->sebagai($d['mhsA'])->get($dasar . 'csv')->status);

        // Seluruh mata kuliah: pengampu dan superadmin; kedua kelas ikut. Pengajar 403.
        $r = $this->sebagai($d['pengampuX'])->get('/mata-kuliah/' . $d['matkulX'] . '/ekspor/csv');
        $this->assertSame(200, $r->status);
        $this->assertStringContainsString('"Mahasiswa Budi",XB,Aktif,55,', $r->body);
        $this->assertStringContainsString('"Mahasiswa Ani",XA,Aktif,100,62.5', $r->body);
        $this->assertMatchesRegularExpression('/filename="UJX101-nilai-\d{8}\.csv"/', (string) $r->header('Content-Disposition'));
        foreach (['pengajarA', 'pengajarB'] as $siapa) {
            $r = $this->sebagai($d[$siapa])->get('/mata-kuliah/' . $d['matkulX'] . '/ekspor/csv');
            $this->assertSame(403, $r->status, $siapa);
            $this->assertStringNotContainsString('Mahasiswa', $r->body);
        }
        $this->assertSame(404, $this->sebagai($d['pengampuY'])->get('/mata-kuliah/' . $d['matkulX'] . '/ekspor/csv')->status);
        $this->assertSame(3, $this->hitung("SELECT COUNT(*) FROM audit_log WHERE action = 'nilai.diekspor'"), 'hanya unduhan yang berhasil yang tercatat');
    }

    /**
     * @param list<string> $a
     *
     * @return list<string>
     */
    private function urut(array $a): array
    {
        sort($a);

        return $a;
    }

    public function testPerbandinganKelasUntukPengampu(): void
    {
        $d = $this->duniaAnalitik();
        // XA (5 aktif): 100, 80, tiga belum → rata-rata 36, median 0. XB (1 aktif): 50.
        $this->nilai($d, 'mhsA', $d['pertemuanX1'], 100);
        $this->nilai($d, 'Dedi', $d['pertemuanX1'], 80);
        $this->nilai($d, 'mhsB', $d['pertemuanX1'], 50);
        $url = '/mata-kuliah/' . $d['matkulX'] . '/analitik';
        foreach (['pengampuX', 'superadmin'] as $siapa) {
            $r = $this->sebagai($d[$siapa])->get($url);
            $this->assertSame(200, $r->status, $siapa);
            $baris = $this->tabel($r->body, 'banding');
            $this->assertSame(['P1', '36 · 0 · 2/5', '50 · 50 · 1/1'], $baris[0]);
            $this->assertSame(['P2', '0 · 0 · 0/5', '0 · 0 · 0/1'], $baris[1]);
            $this->assertStringContainsString('data-grafik="banding"', $r->body);
        }
        foreach (['pengajarA', 'pengajarB'] as $siapa) {
            $r = $this->sebagai($d[$siapa])->get($url);
            $this->assertSame(403, $r->status, $siapa);
            $this->assertStringNotContainsString('data-banding', $r->body);
        }
        $this->assertSame(404, $this->sebagai($d['pengampuY'])->get($url)->status);
    }
}
