<?php

declare(strict_types=1);

namespace Tests\Hasil;

use Lokandita\Auth\Testing\Http;
use Tests\Support\UjianCase;

/**
 * Unduhan CSV (SPEC-LOKAUJIAN.md §9.3, §12; KONTRAK.md §24), lewat HTTP sungguhan. Berkasnya dibaca
 * kembali byte demi byte dan dengan pengurai CSV: BOM, CRLF, kedua pemisah, teks Arab, ganti baris
 * di dalam jawaban, sel yang diawali tanda rumus, dan SETIAP jawaban ada.
 *
 * Yang tidak bisa dibuktikan tes mana pun: bahwa Excel menampilkannya dengan benar. Itu gerbang pemilik.
 */
final class CsvTest extends UjianCase
{
    private const URAIAN = "baris satu\nbaris dua; dengan \"kutip\", koma, dan نص عربي";

    /**
     * @return array{0: list<string>, 1: list<array<string, string>>} kepala, baris (kepala => sel)
     */
    private function urai(string $isi, string $pemisah): array
    {
        $this->assertSame("\xEF\xBB\xBF", substr($isi, 0, 3), 'berkas diawali BOM UTF-8');
        $this->assertTrue(mb_check_encoding($isi, 'UTF-8'));
        $this->assertStringEndsWith("\r\n", $isi);
        $f = fopen('php://memory', 'w+');
        fwrite($f, substr($isi, 3));
        rewind($f);
        $baris = [];
        while (($b = fgetcsv($f, null, $pemisah, '"', '')) !== false) {
            $baris[] = $b;
        }
        fclose($f);
        $kepala = array_shift($baris);
        $hasil  = [];
        foreach ($baris as $b) {
            $this->assertCount(count($kepala), $b, 'setiap baris punya jumlah kolom yang sama dengan kepalanya');
            $hasil[] = array_combine($kepala, $b);
        }

        return [$kepala, $hasil];
    }

    /**
     * @param array<string, string> $isian
     */
    private function unduh(Http $http, string $jalur, array $isian): string
    {
        $r = $this->kirim($http, $jalur, $isian);
        $this->assertSame(200, $r->status, substr(strip_tags($r->body), 0, 300));
        $this->assertSame('text/csv; charset=utf-8', strtolower((string) $r->header('Content-Type')));
        $this->assertStringContainsString('no-store', (string) $r->header('Cache-Control'));
        $this->assertSame('nosniff', $r->header('X-Content-Type-Options'));

        return $r->body;
    }

    public function testBerkasPanjangDanLebarMemuatSetiapJawabanDenganBentukUntukExcel(): void
    {
        $d    = $this->duniaSesi();
        $http = $d['dosen'];
        $s    = $d['sesi'];
        $ani  = $this->sebagai($d['mhsA']);
        $this->mulaiKerja($ani, $s, (string) $d['mhsA']['id']);
        $t      = $this->bukaHalamanUjian($ani, $s);
        $pgBenar = (int) $this->db->query("SELECT id FROM exam_options WHERE exam_question_id = {$d['q']['PG']} AND correct = 1")->fetch_assoc()['id'];
        $this->simpan($ani, $s, $t, $d['q']['BS'], 'salah', 1);
        $this->simpan($ani, $s, $t, $d['q']['PG'], $pgBenar, 1);
        $this->simpan($ani, $s, $t, $d['q']['ISIAN'], '=1+1', 1);
        $this->simpan($ani, $s, $t, $d['q']['URAIAN'], self::URAIAN, 1);
        $this->simpan($ani, $s, $t, $d['q']['KEL-1'], '-minus di depan', 1);
        $this->kirim($http, "/sesi/{$s}/tambah-waktu", ['mahasiswa' => [(string) $d['mhsA']['id']], 'menit' => '5', 'yakin' => '1']);
        $this->kirim($http, "/mata-kuliah/{$d['matkulX']}/ujian/{$d['ujian']}/koreksi/{$d['q']['PG']}", ['poin' => '2,5']);
        $this->assertSame(200, $ani->postJson("/api/sesi/{$s}/peristiwa", ['jenis' => 'paste_blocked', 'jalur' => 'keyboard'], ['X-CSRF-TOKEN' => $this->csrf($ani)])->status);

        // Selagi sesi berjalan: ekspor boleh, dan kolom status berkas lebar mengatakannya.
        [, $lebar] = $this->urai($this->unduh($http, "/sesi/{$s}/csv", ['bentuk' => 'lebar']), ';');
        $perNama = array_column($lebar, null, 'Nama'); // urutan baris menurut nomor induk, yang acak di dunia uji
        $this->assertSame(['Sedang mengerjakan', 'Belum mulai'], [$perNama['Mahasiswa Ani']['Status'], $perNama['Mahasiswa Citra']['Status']]);
        $this->assertSame('', $perNama['Mahasiswa Ani']['Skor objektif'], 'pengerjaan yang berjalan belum punya skor');

        $this->kirim($ani, "/ujian-saya/{$s}/kumpulkan");
        $this->db->query("UPDATE sessions SET end_at = UTC_TIMESTAMP() - INTERVAL 1 MINUTE WHERE id = {$s}");

        // ---- PANJANG, titik koma (bawaan): satu baris per mahasiswa per soal, hanya yang punya pengerjaan.
        $r = $this->kirim($http, "/sesi/{$s}/csv", ['bentuk' => 'panjang']);
        $this->assertMatchesRegularExpression('/^attachment; filename="lokaujian-UJX101-ujian' . $d['ujian'] . '-XA-\d{8}-panjang\.csv"$/', (string) $r->header('Content-Disposition'));
        $mentah = $r->body;
        // Di luar sel berkutip, setiap akhir baris adalah CRLF: tidak ada LF telanjang selain di dalam jawaban uraian.
        $this->assertSame(substr_count($mentah, "\r\n") + 1, substr_count($mentah, "\n"), 'satu-satunya LF tanpa CR adalah ganti baris di dalam jawaban uraian');
        [$kepala, $baris] = $this->urai($mentah, ';');
        $this->assertSame(['Kelas', 'Nomor induk', 'Nama', 'Nomor soal', 'Nomor yang dilihat mahasiswa', 'Id soal', 'Kelompok', 'Tipe', 'Teks soal', 'Jawaban', 'Kunci', 'Benar', 'Poin maksimum', 'Poin', 'Simpan terakhir'], $kepala);
        $this->assertCount(6, $baris, 'enam soal, satu mahasiswa dengan pengerjaan');
        $per = array_column($baris, null, 'Id soal');
        foreach ($baris as $b) {
            $this->assertSame(['XA', (string) $d['mhsA']['nomor_induk'], 'Mahasiswa Ani'], [$b['Kelas'], $b['Nomor induk'], $b['Nama']]);
        }
        $this->assertSame(['1', '2', '3', '4', '5', '6'], array_column($baris, 'Nomor soal'));
        $bs = $per[$d['q']['BS']];
        $this->assertSame(['Salah', 'Benar', '0', '1', '0', 'BS: bumi itu bulat', ''], [$bs['Jawaban'], $bs['Kunci'], $bs['Benar'], $bs['Poin maksimum'], $bs['Poin'], $bs['Teks soal'], $bs['Kelompok']]);
        $pg = $per[$d['q']['PG']];
        $this->assertSame(['B. Opsi kedua', 'B. Opsi kedua', '1', '2,5', '2,5'], [$pg['Jawaban'], $pg['Kunci'], $pg['Benar'], $pg['Poin maksimum'], $pg['Poin']], 'desimal koma bersama pemisah titik koma');
        // Sel yang diawali tanda rumus diberi apostrof; Benar dan Poin kosong untuk soal yang dinilai dosen.
        $this->assertSame(["'=1+1", '', '', ''], [$per[$d['q']['ISIAN']]['Jawaban'], $per[$d['q']['ISIAN']]['Kunci'], $per[$d['q']['ISIAN']]['Benar'], $per[$d['q']['ISIAN']]['Poin']]);
        $this->assertSame("'-minus di depan", $per[$d['q']['KEL-1']]['Jawaban']);
        $this->assertSame(['1', '1'], [$per[$d['q']['KEL-1']]['Kelompok'], $per[$d['q']['KEL-2']]['Kelompok']]);
        $this->assertSame('', $per[$d['q']['KEL-2']]['Jawaban'], 'soal yang tidak dijawab tetap punya baris');
        // Jawaban uraian utuh: ganti baris, titik koma, tanda kutip, koma dan teks Arab.
        $this->assertSame(self::URAIAN, $per[$d['q']['URAIAN']]['Jawaban']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d\d-\d\d \d\d:\d\d:\d\d$/', $bs['Simpan terakhir']);
        $wib = (new \DateTimeImmutable('now', new \DateTimeZone('Asia/Jakarta')))->format('Y-m-d H');
        $this->assertStringStartsWith(substr($wib, 0, 10), $bs['Simpan terakhir'], 'waktu dalam Asia/Jakarta');

        // ---- PANJANG, koma: isi yang sama, desimal titik.
        [$kepalaKoma, $koma] = $this->urai($this->unduh($http, "/sesi/{$s}/csv", ['bentuk' => 'panjang', 'pemisah' => 'koma']), ',');
        $this->assertSame($kepala, $kepalaKoma);
        $perKoma = array_column($koma, null, 'Id soal');
        $this->assertSame(['2.5', self::URAIAN, "'=1+1"], [$perKoma[$d['q']['PG']]['Poin'], $perKoma[$d['q']['URAIAN']]['Jawaban'], $perKoma[$d['q']['ISIAN']]['Jawaban']]);

        // ---- LEBAR: satu baris per mahasiswa AKTIF, termasuk yang tidak pernah mulai; satu kolom per soal.
        [$kepalaL, $lebar] = $this->urai($this->unduh($http, "/sesi/{$s}/csv", ['bentuk' => 'lebar']), ';');
        $this->assertSame(['Soal 1', 'Soal 2', 'Soal 3', 'Soal 4', 'Soal 5', 'Soal 6'], array_slice($kepalaL, -6));
        $this->assertCount(2, $lebar);
        $perNama = array_column($lebar, null, 'Nama');
        $a       = $perNama['Mahasiswa Ani'];
        $c       = $perNama['Mahasiswa Citra'];
        $nomor   = array_column($lebar, 'Nomor induk');
        $urut    = $nomor;
        sort($urut);
        $this->assertSame($urut, $nomor, 'baris berurut menurut nomor induk');
        $this->assertSame(
            ['XA', 'Mahasiswa Ani', 'Selesai', 'Dikumpulkan mahasiswa', '5', '0', '5', '2,5', '3,5', '4', '1'],
            [$a['Kelas'], $a['Nama'], $a['Status'], $a['Cara kumpul'], $a['Tambahan waktu (menit)'], $a['Buka ulang (menit)'], $a['Soal terjawab'], $a['Skor objektif'], $a['Poin objektif tersedia'], $a['Poin dinilai dosen'], $a['Peristiwa integritas']],
        );
        $this->assertSame(['Salah', 'B. Opsi kedua', "'=1+1", self::URAIAN, "'-minus di depan", ''], array_values(array_slice($a, -6)));
        $this->assertMatchesRegularExpression('/^\d{4}-\d\d-\d\d \d\d:\d\d:\d\d$/', $a['Mulai']);
        $this->assertSame(['Mahasiswa Citra', 'Belum mulai', '', '', '', '', ''], [$c['Nama'], $c['Status'], $c['Mulai'], $c['Dikumpulkan'], $c['Cara kumpul'], $c['Soal terjawab'], $c['Skor objektif']]);

        // ---- PERISTIWA.
        [, $peristiwa] = $this->urai($this->unduh($http, "/sesi/{$s}/csv", ['bentuk' => 'peristiwa']), ';');
        $this->assertSame([['Mahasiswa Ani', 'paste_blocked', 'jalur: keyboard']], array_map(static fn (array $b): array => [$b['Nama'], $b['Jenis'], $b['Rincian']], $peristiwa));

        // Setiap unduhan diaudit: siapa, sesi mana, berapa baris.
        $audit = $this->db->query("SELECT detail, actor_id, class_id FROM audit_log WHERE action = 'csv.diekspor' ORDER BY id")->fetch_all(MYSQLI_ASSOC);
        $this->assertCount(5, $audit);
        $det = json_decode((string) $audit[1]['detail'], true);
        $this->assertSame(['panjang', [$s], 6, 'titik_koma'], [$det['bentuk'], $det['sesi'], $det['baris'], $det['pemisah']]);
        $this->assertSame([(string) $d['pengampuX']['id'], (string) $d['kelasXA']], [(string) $audit[1]['actor_id'], (string) $audit[1]['class_id']]);
        // Bentuk yang tidak dikenal: tidak ada berkas, tidak ada baris audit baru.
        $this->assertStringEndsWith('?pesan=g_bentuk', (string) $this->kirim($http, "/sesi/{$s}/csv", ['bentuk' => 'semua'])->header('Location'));
        $this->assertSame(5, (int) $this->db->query("SELECT COUNT(*) AS n FROM audit_log WHERE action = 'csv.diekspor'")->fetch_assoc()['n']);
    }

    public function testLingkupKelasPadaUnduhanDanMahasiswaYangPindah(): void
    {
        $d    = $this->duniaSesi();
        $http = $d['dosen'];
        $s    = $d['sesi'];
        // Ujian yang sama di kelas XB; Budi mengerjakannya di sana.
        $sb = $this->beri($http, $d['matkulX'], $d['ujian'], $d['kelasXB']);
        $this->kirim($http, "/sesi/{$sb}/mulai", ['menit' => '60']);
        $budi = $this->sebagai($d['mhsB']);
        $this->mulaiKerja($budi, $sb, (string) $d['mhsB']['id']);
        $this->simpan($budi, $sb, $this->bukaHalamanUjian($budi, $sb), $d['q']['ISIAN'], 'RAHASIA-JAWABAN-BUDI', 1);
        $ani = $this->sebagai($d['mhsA']);
        $this->mulaiKerja($ani, $s, (string) $d['mhsA']['id']);
        $this->simpan($ani, $s, $this->bukaHalamanUjian($ani, $s), $d['q']['ISIAN'], 'jawaban Ani', 1);
        // Ani keluar dari kelas XA sesudah mulai: pengerjaannya tetap, dan tetap ada di berkas lebar, bertanda.
        $this->roster->query("UPDATE enrollments SET status = 'MENGUNDURKAN_DIRI' WHERE user_id = '{$d['mhsA']['id']}'");

        $ajarA = $this->sebagai($d['pengajarA']);
        // Pengajar A: semua kelas YANG IA LIHAT = kelas XA saja. Jawaban Budi tidak ada di berkasnya.
        foreach (['panjang', 'lebar'] as $bentuk) {
            $r = $this->kirim($ajarA, "/ujian/{$d['ujian']}/csv", ['bentuk' => $bentuk]);
            $this->assertSame(200, $r->status);
            $this->assertStringContainsString('-semua-kelas-', (string) $r->header('Content-Disposition'));
            $this->assertStringContainsString('jawaban Ani', $r->body, $bentuk);
            $this->assertStringNotContainsString('RAHASIA-JAWABAN-BUDI', $r->body, $bentuk);
            $this->assertStringNotContainsString('Mahasiswa Budi', $r->body, $bentuk);
        }
        [, $lebar] = $this->urai($this->kirim($ajarA, "/sesi/{$s}/csv", ['bentuk' => 'lebar'])->body, ';');
        $this->assertEquals(['Mahasiswa Ani' => 'Sedang mengerjakan (tidak lagi di kelas ini)', 'Mahasiswa Citra' => 'Belum mulai'], array_column($lebar, 'Status', 'Nama'));
        // Berkas panjang memuat kunci, juga bagi pengajar (SPEC-LOKAUJIAN.md §3, §12).
        [, $panjang] = $this->urai($this->kirim($ajarA, "/sesi/{$s}/csv", ['bentuk' => 'panjang'])->body, ';');
        $this->assertSame('Benar', array_column($panjang, 'Kunci', 'Id soal')[$d['q']['BS']]);

        // Pengampu: kedua kelas dalam satu berkas.
        [, $semua] = $this->urai($this->kirim($http, "/ujian/{$d['ujian']}/csv", ['bentuk' => 'lebar'])->body, ';');
        $this->assertSame(['XA', 'XA', 'XB'], array_column($semua, 'Kelas'));
        $this->assertSame(null, json_decode((string) $this->db->query("SELECT class_id FROM audit_log WHERE action = 'csv.diekspor' ORDER BY id DESC LIMIT 1")->fetch_assoc()['class_id']));

        // Di luar lingkup: 404, tanpa isi, tanpa baris audit.
        $sebelum = $this->sidikBank();
        foreach ([[$d['pengajarB'], "/sesi/{$s}/csv"], [$d['pengampuY'], "/sesi/{$s}/csv"], [$d['pengampuY'], "/ujian/{$d['ujian']}/csv"], [$d['pengajarA'], "/sesi/{$sb}/csv"], [$d['pengajarA'], '/ujian/999999/csv']] as [$siapa, $jalur]) {
            $r = $this->kirim($this->sebagai($siapa), $jalur, ['bentuk' => 'panjang']);
            $this->assertSame(404, $r->status, $jalur);
            $this->assertStringNotContainsString('jawaban Ani', $r->body);
        }
        $this->assertSame(403, $this->kirim($budi, "/sesi/{$sb}/csv", ['bentuk' => 'panjang'])->status);
        $this->assertSame($sebelum, $this->sidikBank());
        // "Peristiwa" hanya per sesi.
        $this->assertStringContainsString('?pesan=g_bentuk', (string) $this->kirim($http, "/ujian/{$d['ujian']}/csv", ['bentuk' => 'peristiwa'])->header('Location'));
    }
}
