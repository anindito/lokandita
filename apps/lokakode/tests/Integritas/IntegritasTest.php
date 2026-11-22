<?php

declare(strict_types=1);

namespace Tests\Integritas;

use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\Support\KodeCase;

/**
 * Ketikan tak wajar dan halaman peristiwa integritas per kelas (SPEC.md §13.2, KONTRAK.md §17),
 * lewat HTTP sungguhan ke lokakode-tes dan MySQL sungguhan.
 */
final class IntegritasTest extends KodeCase
{
    /**
     * Pertemuan X1 terbit dan terbuka untuk kelas XA dan XB, satu soal.
     *
     * @return array<string, mixed>
     */
    private function duniaTerbuka(): array
    {
        $d         = $this->dunia();
        $d['soal'] = $this->soalSiap($d['pertemuanX1'], 'jumlah-dua-bilangan');
        $this->terbit($d['pertemuanX1']);
        $this->jendela($d['pertemuanX1'], $d['kelasXA']);
        $this->jendela($d['pertemuanX1'], $d['kelasXB']);

        return $d;
    }

    /**
     * @param array<string, mixed>      $mhs
     * @param array<string, mixed>|null $detail
     */
    private function peristiwa(array $mhs, string $jenis, ?int $mp, ?array $detail, string $waktu = 'UTC_TIMESTAMP()', string $ip = '10.0.0.9'): void
    {
        $this->db->query(sprintf(
            "INSERT INTO integrity_events (user_id, kind, meeting_problem_id, detail, ip, user_agent, created_at) VALUES ('%s', '%s', %s, %s, '%s', 'PerambanUji/1.0', %s)",
            $mhs['id'], $jenis, $mp ?? 'NULL', $detail === null ? 'NULL' : "'" . json_encode($detail) . "'", $ip, $waktu,
        ));
    }

    public function testKetikanTakWajarDicatatHanyaBilaKeduaAmbangTerpenuhi(): void
    {
        $d    = $this->duniaTerbuka();
        $http = $this->sebagai($d['mhsA']);
        $csrf = $this->csrf($http);
        $mp   = (string) $d['soal']['mp'];
        $kirim = fn (array $isi): int => $http->post('/api/integritas', ['csrf' => $csrf, 'mp' => $mp, 'jenis' => 'ketik'] + $isi)->status;

        // Di bawah ambang: 299 karakter; 300 karakter tetapi lebih lambat dari 150 per detik; isian rusak.
        $this->assertSame(422, $kirim(['karakter' => '299', 'ms' => '0']));
        $this->assertSame(422, $kirim(['karakter' => '300', 'ms' => '2001']));
        $this->assertSame(422, $kirim(['karakter' => '5000', 'ms' => '-1']));
        $this->assertSame(422, $kirim(['karakter' => 'banyak', 'ms' => '0']));
        $this->assertSame(422, $kirim([]));
        $this->assertSame(0, $this->hitung('SELECT COUNT(*) FROM integrity_events'));

        // Tepat di ambang: 300 karakter dalam 2 detik = 150 per detik.
        $this->assertSame(200, $kirim(['karakter' => '300', 'ms' => '2000']));
        $e = $this->row('SELECT * FROM integrity_events');
        $this->assertSame([$d['mhsA']['id'], 'typing_anomaly', $mp], [$e['user_id'], $e['kind'], $e['meeting_problem_id']]);
        // MySQL mengurutkan ulang kunci JSON, jadi isinya dibandingkan tanpa urutan.
        $this->assertEquals(['karakter' => 300, 'ms' => 2000, 'cps' => 150, 'sebab' => 'ketik'], json_decode((string) $e['detail'], true));
        $this->assertNotNull($e['ip']);

        // Satu sisipan besar (ms = 0) dari undo: laju dihitung server, sebab hanya 'ketik' atau 'undo'.
        $this->lupakanLaju();
        $this->assertSame(200, $kirim(['karakter' => '1200', 'ms' => '0', 'sebab' => 'undo', 'cps' => '1']));
        $this->lupakanLaju();
        $this->assertSame(200, $kirim(['karakter' => '400', 'ms' => '1000', 'sebab' => '<script>']));
        $detail = array_map(static fn (array $b): array => json_decode((string) $b['detail'], true), $this->db->query('SELECT detail FROM integrity_events ORDER BY id')->fetch_all(MYSQLI_ASSOC));
        $this->assertEquals(['karakter' => 1200, 'ms' => 0, 'cps' => 1200, 'sebab' => 'undo'], $detail[1]);
        $this->assertEquals(['karakter' => 400, 'ms' => 1000, 'cps' => 400, 'sebab' => 'ketik'], $detail[2]);

        // Satu peristiwa per detik per pengguna, seperti tempel.
        $this->assertSame(200, $kirim(['karakter' => '900', 'ms' => '10']));
        $this->assertSame(3, $this->hitung('SELECT COUNT(*) FROM integrity_events'));

        // Soal yang bukan miliknya: 404, tidak ada yang dicatat. Jalur tempel lama tetap bekerja.
        $this->lupakanLaju();
        $this->assertSame(404, $http->post('/api/integritas', ['csrf' => $csrf, 'mp' => '999999', 'jenis' => 'ketik', 'karakter' => '900', 'ms' => '10'])->status);
        $this->assertSame(200, $http->post('/api/integritas', ['csrf' => $csrf, 'mp' => $mp, 'jalur' => 'keyboard'])->status);
        $this->assertSame(200, $http->post('/api/integritas', ['csrf' => $csrf, 'mp' => $mp, 'jenis' => 'tempel', 'jalur' => 'drop'])->status);
        $this->assertSame(1, $this->hitung("SELECT COUNT(*) FROM integrity_events WHERE kind = 'paste_blocked'"));
    }

    public function testPenggunaYangBolehMenempelTidakDicatatSebagaiKetikanTakWajar(): void
    {
        $d = $this->duniaTerbuka();
        $this->idn->query("UPDATE users SET paste_allowed = 1 WHERE id = '{$d['mhsA']['id']}'");
        $http = $this->sebagai($d['mhsA']);
        $r    = $http->post('/api/integritas', ['csrf' => $this->csrf($http), 'mp' => (string) $d['soal']['mp'], 'jenis' => 'ketik', 'karakter' => '2000', 'ms' => '0']);
        $this->assertSame([200, ['ok' => true]], [$r->status, $r->json()]);
        $this->assertSame(0, $this->hitung('SELECT COUNT(*) FROM integrity_events'));
    }

    public function testHalamanKelasHanyaMemuatPeristiwaMahasiswaKelasItu(): void
    {
        $d  = $this->duniaTerbuka();
        $mp = $d['soal']['mp'];
        // Soal mata kuliah Y: mahasiswa A kebetulan juga terdaftar di sana.
        $soalY = $this->soalSiap($d['pertemuanY1'], 'soal-matkul-y');
        $this->daftar($d['mhsA'], $d['kelasYA']);

        $this->peristiwa($d['mhsA'], 'paste_blocked', $mp, ['jalur' => 'shift_insert'], 'UTC_TIMESTAMP()', '10.1.1.1');
        $this->peristiwa($d['mhsA'], 'typing_anomaly', $mp, ['karakter' => 420, 'ms' => 900, 'cps' => 467, 'sebab' => 'undo']);
        $this->peristiwa($d['mhsA'], 'seb_terminated', null, ['alasan' => 'tanpa_header', 'jalur' => '/latihan/7']);
        $this->peristiwa($d['mhsA'], 'paste_blocked', $soalY['mp'], ['jalur' => 'drop'], 'UTC_TIMESTAMP()', '10.2.2.2');
        $this->peristiwa($d['mhsB'], 'paste_blocked', $mp, ['jalur' => 'middle_click'], 'UTC_TIMESTAMP()', '10.3.3.3');
        // Sebelum mahasiswa A terdaftar di kelas ini (misalnya tahun ajaran sebelumnya): bukan urusan kelas ini.
        $this->peristiwa($d['mhsA'], 'paste_blocked', $mp, ['jalur' => 'paste'], "'2020-01-01 00:00:00'", '10.4.4.4');

        $alamat = '/kelas/' . $d['kelasXA'] . '/integritas';
        foreach (['superadmin', 'pengampuX', 'pengajarA'] as $siapa) {
            $r = $this->sebagai($d[$siapa])->get($alamat);
            $this->assertSame(200, $r->status, $siapa);
            $this->assertSame(3, substr_count($r->body, '<tr data-jenis='), $siapa);
            $this->assertStringContainsString('Mahasiswa Ani', $r->body);
            $this->assertStringContainsString($d['mhsA']['nomor_induk'], $r->body);
            $this->assertStringContainsString('Shift+Insert', $r->body);
            $this->assertStringContainsString('420 karakter dalam 0.9 detik (467 per detik), dari undo atau redo', $r->body);
            $this->assertStringContainsString('Tanpa tanda pengenal SEB. /latihan/7', $r->body);
            $this->assertStringContainsString('Jumlah Dua Bilangan', $r->body);
            $this->assertStringContainsString('no-store', (string) $r->header('Cache-Control'));
            foreach (['10.2.2.2', '10.3.3.3', '10.4.4.4', 'Mahasiswa Budi', 'Klik tengah'] as $bukan) {
                $this->assertStringNotContainsString($bukan, $r->body, "{$siapa}: {$bukan}");
            }
        }

        // Kelas XB: hanya peristiwa mahasiswa B. Kelas YA: peristiwa A pada soal Y dan peristiwa SEB-nya.
        $r = $this->sebagai($d['pengajarB'])->get('/kelas/' . $d['kelasXB'] . '/integritas');
        $this->assertSame([200, 1], [$r->status, substr_count($r->body, '<tr data-jenis=')]);
        $this->assertStringContainsString('Klik tengah', $r->body);
        $this->assertStringNotContainsString('Mahasiswa Ani', $r->body);
        $r = $this->sebagai($d['pengampuY'])->get('/kelas/' . $d['kelasYA'] . '/integritas');
        $this->assertSame([200, 2], [$r->status, substr_count($r->body, '<tr data-jenis=')]);
        $this->assertStringContainsString('10.2.2.2', $r->body);
        $this->assertStringNotContainsString('10.1.1.1', $r->body);

        // IDOR: pengajar kelas lain dan pengampu mata kuliah lain 404, mahasiswa 403 — juga untuk ekspor.
        foreach (['', '/ekspor/csv', '/ekspor/xlsx'] as $ekor) {
            $this->assertSame(404, $this->sebagai($d['pengajarB'])->get($alamat . $ekor)->status, "pengajarB {$ekor}");
            $this->assertSame(404, $this->sebagai($d['pengampuY'])->get($alamat . $ekor)->status, "pengampuY {$ekor}");
            $this->assertSame(403, $this->sebagai($d['mhsA'])->get($alamat . $ekor)->status, "mhsA {$ekor}");
        }
        $this->assertSame(404, $this->sebagai($d['pengajarA'])->get($alamat . '/ekspor/pdf')->status);
        $this->assertSame(0, $this->hitung("SELECT COUNT(*) FROM audit_log WHERE action = 'integritas.diekspor'"), 'ekspor yang ditolak tidak tercatat sebagai ekspor');
    }

    public function testSaringanDanPeristiwaMengikutiMahasiswaYangPindahKelas(): void
    {
        $d  = $this->duniaTerbuka();
        $mp = $d['soal']['mp'];
        $ke2 = $this->soalSiap($d['pertemuanX2'], 'soal-kedua');
        $cici = $this->user('mahasiswa', 'Mahasiswa Cici');
        $this->daftar($cici, $d['kelasXA']);
        $this->peristiwa($d['mhsA'], 'paste_blocked', $mp, ['jalur' => 'paste']);
        $this->peristiwa($d['mhsA'], 'typing_anomaly', $ke2['mp'], ['karakter' => 300, 'ms' => 0, 'cps' => 300, 'sebab' => 'ketik']);
        $this->peristiwa($cici, 'seb_rejected', null, ['alasan' => 'kunci_peramban', 'jalur' => '/beranda']);
        $this->peristiwa($cici, 'paste_blocked', $mp, ['jalur' => 'drop'], "UTC_TIMESTAMP() + INTERVAL 3 DAY");

        $http   = $this->sebagai($d['pengajarA']);
        $alamat = '/kelas/' . $d['kelasXA'] . '/integritas';
        $jumlah = fn (string $q): int => substr_count($http->get($alamat . $q)->body, '<tr data-jenis=');
        $this->assertSame(4, $jumlah(''));
        $this->assertSame(1, $jumlah('?jenis=typing_anomaly'));
        $this->assertSame(2, $jumlah('?mahasiswa=' . $cici['id']));
        $this->assertSame(1, $jumlah('?pertemuan=' . $d['pertemuanX2']));
        $this->assertSame(1, $jumlah('?jenis=paste_blocked&mahasiswa=' . $d['mhsA']['id']));
        // Tanggal adalah tanggal Asia/Jakarta: hari ini memuat tiga peristiwa, lusa ke atas satu.
        $jkt = new \DateTimeImmutable('now', new \DateTimeZone('Asia/Jakarta'));
        $this->assertSame(3, $jumlah('?sampai=' . $jkt->format('Y-m-d')));
        $this->assertSame(1, $jumlah('?dari=' . $jkt->modify('+2 days')->format('Y-m-d')));
        $this->assertSame(0, $jumlah('?dari=' . $jkt->modify('+10 days')->format('Y-m-d')));
        // Saringan yang tidak sah diabaikan, bukan diteruskan ke kueri.
        $this->assertSame(4, $jumlah("?jenis=x'--&mahasiswa=bukan-uuid&pertemuan=abc&dari=31-12-2026"));

        // Cici pindah ke kelas XB: peristiwanya ikut, dan pengajar A tidak lagi melihatnya.
        $this->db->query("UPDATE enrollments SET class_id = {$d['kelasXB']} WHERE user_id = '{$cici['id']}'");
        $this->assertSame(2, $jumlah(''));
        $r = $this->sebagai($d['pengajarB'])->get('/kelas/' . $d['kelasXB'] . '/integritas');
        $this->assertSame(2, substr_count($r->body, '<tr data-jenis='));
        $this->assertStringContainsString('Mahasiswa Cici', $r->body);
        // Yang mengundurkan diri tidak tampil.
        $this->db->query("UPDATE enrollments SET status = 'MENGUNDURKAN_DIRI' WHERE user_id = '{$cici['id']}'");
        $this->assertSame(0, substr_count($this->sebagai($d['pengajarB'])->get('/kelas/' . $d['kelasXB'] . '/integritas')->body, '<tr data-jenis='));
    }

    public function testHalamanDibagiPerSeratusBaris(): void
    {
        $d = $this->duniaTerbuka();
        for ($i = 0; $i < 105; $i++) {
            $this->peristiwa($d['mhsA'], 'paste_blocked', $d['soal']['mp'], ['jalur' => 'paste']);
        }
        $http   = $this->sebagai($d['pengajarA']);
        $alamat = '/kelas/' . $d['kelasXA'] . '/integritas';
        $r      = $http->get($alamat . '?jenis=paste_blocked');
        $this->assertSame(100, substr_count($r->body, '<tr data-jenis='));
        $this->assertStringContainsString('jenis=paste_blocked&amp;halaman=2', $r->body);
        $this->assertSame(5, substr_count($http->get($alamat . '?jenis=paste_blocked&halaman=2')->body, '<tr data-jenis='));
        $this->assertSame(0, substr_count($http->get($alamat . '?halaman=3')->body, '<tr data-jenis='));
    }

    public function testEksporCsvDanXlsxMemuatBarisYangSamaDanDicatat(): void
    {
        $d  = $this->duniaTerbuka();
        $mp = $d['soal']['mp'];
        $jahat = $this->user('mahasiswa', '=HYPERLINK("http://jahat")');
        $this->daftar($jahat, $d['kelasXA']);
        $this->peristiwa($d['mhsA'], 'typing_anomaly', $mp, ['karakter' => 600, 'ms' => 1500, 'cps' => 400, 'sebab' => 'ketik'], 'UTC_TIMESTAMP()', '10.1.1.1');
        $this->peristiwa($jahat, 'seb_rejected', null, ['alasan' => 'tanpa_header', 'jalur' => '/beranda'], 'UTC_TIMESTAMP()', '10.5.5.5');
        $this->peristiwa($d['mhsB'], 'paste_blocked', $mp, ['jalur' => 'paste'], 'UTC_TIMESTAMP()', '10.3.3.3');

        $http   = $this->sebagai($d['pengajarA']);
        $alamat = '/kelas/' . $d['kelasXA'] . '/integritas/ekspor/';
        $r      = $http->get($alamat . 'csv');
        $this->assertSame(200, $r->status);
        $this->assertStringContainsString('text/csv', (string) $r->header('Content-Type'));
        $this->assertStringContainsString('UJX101-XA-integritas.csv', (string) $r->header('Content-Disposition'));
        $this->assertStringContainsString('no-store', (string) $r->header('Cache-Control'));
        $baris = array_map(static fn (string $b): array => str_getcsv($b, ',', '"', ''), array_filter(explode("\n", substr($r->body, 3))));
        $this->assertSame(['Waktu', 'Nomor induk', 'Nama', 'Jenis', 'Pertemuan', 'Soal', 'Rincian', 'Alamat IP', 'Peramban'], $baris[0]);
        $this->assertCount(3, $baris);
        // Terbaru lebih dulu. Nama yang berbentuk rumus diberi tanda petik; peristiwa SEB tanpa pertemuan dan soal.
        $this->assertSame([$jahat['nomor_induk'], "'=HYPERLINK(\"http://jahat\")", 'Ditolak tanpa SEB', '', '', 'Tanpa tanda pengenal SEB. /beranda', '10.5.5.5', 'PerambanUji/1.0'], array_slice($baris[1], 1));
        $this->assertSame([$d['mhsA']['nomor_induk'], 'Mahasiswa Ani', 'Ketikan tak wajar', '1', 'Jumlah Dua Bilangan', '600 karakter dalam 1.5 detik (400 per detik)', '10.1.1.1', 'PerambanUji/1.0'], array_slice($baris[2], 1));
        $this->assertMatchesRegularExpression('/^\d{2}-\d{2}-\d{4} \d{2}:\d{2} WIB$/', $baris[1][0]);
        $this->assertStringNotContainsString('10.3.3.3', $r->body, 'kelas lain tidak ikut');

        // Saringan berlaku juga untuk ekspor.
        $r = $http->get($alamat . 'csv?jenis=seb_rejected');
        $this->assertCount(2, array_filter(explode("\n", $r->body)));
        $this->assertStringNotContainsString('Mahasiswa Ani', $r->body);

        $r = $http->get($alamat . 'xlsx');
        $this->assertSame(200, $r->status);
        $this->assertStringContainsString('spreadsheetml', (string) $r->header('Content-Type'));
        $berkas = tempnam(sys_get_temp_dir(), 'uji');
        file_put_contents($berkas, $r->body);
        $lembar = IOFactory::load($berkas)->getSheet(0);
        unlink($berkas);
        $this->assertSame('Integritas', $lembar->getTitle());
        $this->assertSame(3, $lembar->getHighestDataRow());
        $this->assertSame(['=HYPERLINK("http://jahat")', 'Ditolak tanpa SEB'], [(string) $lembar->getCell('C2')->getValue(), (string) $lembar->getCell('D2')->getValue()]);
        $this->assertSame('s', $lembar->getCell('C2')->getDataType(), 'nama adalah teks, bukan rumus');
        $this->assertSame(['Mahasiswa Ani', 1], [(string) $lembar->getCell('C3')->getValue(), (int) $lembar->getCell('E3')->getValue()]);

        // Tiga unduhan, tiga baris audit dengan jumlah barisnya; tanpa nama atau nomor induk.
        $audit = $this->db->query("SELECT actor_id, class_id, detail FROM audit_log WHERE action = 'integritas.diekspor' ORDER BY id")->fetch_all(MYSQLI_ASSOC);
        $this->assertCount(3, $audit);
        $this->assertSame([$d['pengajarA']['id'], (string) $d['kelasXA']], [$audit[0]['actor_id'], $audit[0]['class_id']]);
        $this->assertEquals([['format' => 'csv', 'baris' => 2], ['format' => 'csv', 'baris' => 1], ['format' => 'xlsx', 'baris' => 2]], array_map(static fn (array $a): array => json_decode((string) $a['detail'], true), $audit));
    }

    public function testHalamanKelasMenautkanKeIntegritas(): void
    {
        $d = $this->duniaTerbuka();
        $r = $this->sebagai($d['pengajarA'])->get('/kelas/' . $d['kelasXA']);
        $this->assertStringContainsString('href="/kelas/' . $d['kelasXA'] . '/integritas"', $r->body);
        // Tanpa peristiwa: halaman kosong yang mengatakan demikian, tanpa tombol unduh.
        $r = $this->sebagai($d['pengajarA'])->get('/kelas/' . $d['kelasXA'] . '/integritas');
        $this->assertStringContainsString('Belum ada peristiwa integritas', $r->body);
        $this->assertStringNotContainsString('/ekspor/csv', $r->body);
    }
}
