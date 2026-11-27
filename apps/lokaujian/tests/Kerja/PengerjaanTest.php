<?php

declare(strict_types=1);

namespace Tests\Kerja;

use Lokandita\Auth\Testing\Http;
use Tests\Support\UjianCase;

/**
 * Pengerjaan mahasiswa (SPEC-LOKAUJIAN.md §7, §8, §9.2; KONTRAK.md §22) lewat HTTP sungguhan ke
 * kontainer lokaujian-tes, MySQL dan Redis sungguhan.
 *
 * Waktu tidak ditunggu dan tidak dipalsukan di aplikasi: tes MENGGESER waktu yang tersimpan di
 * skema tes (kapan pengerjaan dimulai, kapan sesi berakhir). Aplikasi tetap membaca jam MySQL.
 */
final class PengerjaanTest extends UjianCase
{
    /**
     * Ujian per mahasiswa 20 menit, terbuka, di kelas XA; mahasiswa A sudah mulai.
     *
     * @return array<string, mixed>
     */
    private function kerjaPer(): array
    {
        $d         = $this->dunia();
        $d['http'] = $this->sebagai($d['pengampuX']);
        $d['ujian'] = $this->ujianTerbit($d['http'], $d['matkulX'], 'Kuis kerja', ['mode' => 'per_mahasiswa', 'durasi' => '20']);
        $d['sesi']  = $this->beri($d['http'], $d['matkulX'], $d['ujian'], $d['kelasXA']);
        $d['mhs']   = $this->sebagai($d['mhsA']);
        $d['a']     = $this->mulaiKerja($d['mhs'], $d['sesi'], (string) $d['mhsA']['id']);
        $d['token'] = $this->bukaHalamanUjian($d['mhs'], $d['sesi']);
        $d['q']     = $this->soalBeku($d['ujian']);

        return $d;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function jawaban(int $attempt, int $soal): ?array
    {
        return $this->db->query("SELECT * FROM answers WHERE attempt_id = {$attempt} AND exam_question_id = {$soal}")->fetch_assoc();
    }

    /** Menggeser saat mulai sehingga tenggat pengerjaan per_mahasiswa 20 menit jatuh $detik detik yang lalu. */
    private function tenggatLalu(int $attempt, int $detik): void
    {
        $this->db->query("UPDATE attempts SET started_at = UTC_TIMESTAMP() - INTERVAL 20 MINUTE - INTERVAL {$detik} SECOND WHERE id = {$attempt}");
    }

    public function testDaftarMulaiDanSatuPengerjaan(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengampuX']);
        $u    = $this->ujianTerbit($http, $d['matkulX'], 'Kuis daftar', ['mode' => 'per_mahasiswa', 'durasi' => '20']);
        $wib  = static fn (string $g): string => (new \DateTimeImmutable($g, new \DateTimeZone('Asia/Jakarta')))->format('Y-m-d\TH:i');
        $sesi = $this->beri($http, $d['matkulX'], $u, $d['kelasXA'], $wib('+1 hour'), $wib('+2 hours'));
        $mhs  = $this->sebagai($d['mhsA']);

        // Sejak diberikan, ujian tampil di daftar mahasiswa kelas itu — tanpa isi apa pun.
        $beranda = $mhs->get('/beranda')->body;
        $this->assertStringContainsString('Kuis daftar', $beranda);
        $this->assertStringContainsString('data-ujian-saya="datang"', $beranda);
        foreach (['ISIAN:', 'BACAAN:', 'PG:'] as $isi) {
            $this->assertStringNotContainsString($isi, $beranda);
        }
        $this->assertStringNotContainsString('Kuis daftar', $this->sebagai($d['mhsB'])->get('/beranda')->body);
        // Belum dibuka: halaman menunggu, tanpa tombol Mulai; POST mulai tidak membuat pengerjaan.
        $r = $mhs->get("/ujian-saya/{$sesi}");
        $this->assertSame(200, $r->status);
        $this->assertStringContainsString('data-menunggu', $r->body);
        $this->assertStringNotContainsString('data-mulai>', $r->body);
        $this->assertStringContainsString('MODE UJI', $r->body, 'pita MODE UJI harus tampil selama penegakan SEB dinonaktifkan');
        $this->assertStringEndsWith('?pesan=belum', (string) $this->kirim($mhs, "/ujian-saya/{$sesi}/mulai")->header('Location'));
        $this->assertSame('0', (string) $this->db->query('SELECT COUNT(*) AS n FROM attempts')->fetch_assoc()['n']);
        $this->assertFalse($mhs->get("/api/sesi/{$sesi}/keadaan")->json()['menerima']);
        // Halaman ujian tanpa pengerjaan: kembali ke halaman sesi.
        $this->assertStringEndsWith("/ujian-saya/{$sesi}", (string) $mhs->get("/ujian-saya/{$sesi}/kerjakan")->header('Location'));

        // Dibuka: Mulai membuat tepat satu pengerjaan; Mulai kedua mengembalikan yang sama.
        $this->db->query("UPDATE sessions SET open_at = UTC_TIMESTAMP() - INTERVAL 1 MINUTE WHERE id = {$sesi}");
        $this->assertTrue($mhs->get("/api/sesi/{$sesi}/keadaan")->json()['menerima']);
        $a1 = $this->mulaiKerja($mhs, $sesi, (string) $d['mhsA']['id']);
        $a2 = $this->mulaiKerja($mhs, $sesi, (string) $d['mhsA']['id']);
        $this->assertSame($a1['id'], $a2['id']);
        $this->assertSame($a1['started_at'], $a2['started_at']);
        $this->assertSame('1', (string) $this->db->query('SELECT COUNT(*) AS n FROM attempts')->fetch_assoc()['n']);
        // Basis data sendiri menolak pengerjaan kedua untuk mahasiswa dan ujian yang sama.
        try {
            $this->db->query("INSERT INTO attempts (session_id, exam_id, class_id, user_id, started_at, urutan, page_token) VALUES ({$sesi}, {$u}, {$d['kelasXA']}, '{$d['mhsA']['id']}', UTC_TIMESTAMP(), '{}', REPEAT('a', 32))");
            $this->fail('pengerjaan kedua diterima basis data');
        } catch (\mysqli_sql_exception $e) {
            $this->assertSame(1062, $e->getCode());
        }
        $this->assertStringContainsString('data-ujian-saya="kerja"', $mhs->get('/beranda')->body);

        // SPEC §7.1: yang mulai tepat sebelum sesi ditutup tetap mendapat durasi penuh.
        $this->db->query("UPDATE sessions SET close_at = UTC_TIMESTAMP() - INTERVAL 1 SECOND WHERE id = {$sesi}");
        $k = $mhs->get("/api/sesi/{$sesi}/keadaan")->json();
        $this->assertFalse($k['menerima']);
        $this->assertTrue($k['pengerjaan']['berjalan']);
        $this->assertEqualsWithDelta(1200, $k['pengerjaan']['sisa'], 10);
    }

    public function testHalamanUjianTanpaKunciDanUrutanTetap(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengampuX']);
        $u    = $this->ujianTerbit($http, $d['matkulX'], 'Kuis acak', ['acak_soal' => '1', 'acak_opsi' => '1']);
        $sesi = $this->beri($http, $d['matkulX'], $u, $d['kelasXA']);
        $this->kirim($http, "/sesi/{$sesi}/mulai", ['menit' => '30']);
        $q      = $this->soalBeku($u);
        $urutan = static function (string $isi): array {
            preg_match_all('/data-halaman-soal="\d+" data-id="(\d+)"/', $isi, $m);

            return array_map('intval', $m[1]);
        };
        $terlihat = [];
        foreach (['mhsA', 'mhsLulus'] as $i => $siapa) {
            // mhsLulus bukan anggota aktif; dipakai mahasiswa aktif kedua yang didaftarkan di sini.
            $orang = $siapa === 'mhsA' ? $d['mhsA'] : $this->user('mahasiswa', 'Mahasiswa Kedua');
            if ($siapa !== 'mhsA') {
                $this->daftar($orang, $d['kelasXA']);
            }
            $mhs = $this->sebagai($orang);
            $this->mulaiKerja($mhs, $sesi, (string) $orang['id']);
            $r1 = $mhs->get("/ujian-saya/{$sesi}/kerjakan");
            $r1isi = $r1->body;
            $this->assertSame(200, $r1->status);
            $urut = $urutan($r1isi);
            $this->assertCount(6, $urut);
            $this->assertEqualsCanonicalizing(array_values($q), $urut);
            // Kelompok tetap bersama dan berurutan di bawah acakan apa pun.
            $this->assertSame(array_search($q['KEL-1'], $urut, true) + 1, array_search($q['KEL-2'], $urut, true), 'soal kelompok terpisah atau terbalik');
            // Membuka lagi tidak mengacak ulang: urutan soal dan urutan opsi sama.
            $r2isi = $mhs->get("/ujian-saya/{$sesi}/kerjakan")->body;
            $this->assertSame($urut, $urutan($r2isi));
            preg_match_all('/name="jawab-' . $q['PG'] . '" value="(\d+)"/', $r1isi, $o1);
            preg_match_all('/name="jawab-' . $q['PG'] . '" value="(\d+)"/', $r2isi, $o2);
            $this->assertCount(3, $o1[1]);
            $this->assertSame($o1[1], $o2[1]);
            $tersimpan = json_decode((string) $this->pengerjaan($sesi, (string) $orang['id'])['urutan'], true);
            $this->assertSame($urut, $tersimpan['soal']);
            $this->assertSame(array_map('intval', $o1[1]), $tersimpan['opsi'][(string) $q['PG']]);
            // Tidak ada kunci, skor atau petunjuk kebenaran di halaman.
            $this->assertStringNotContainsString(' checked', $r1isi);
            foreach (['kunci-staf', '"answer"', 'data-benar', 'data-kunci'] as $terlarang) {
                $this->assertStringNotContainsString($terlarang, $r1isi, $terlarang);
            }
            // "correct" hanya boleh muncul sebagai atribut autocorrect kotak jawaban.
            $this->assertSame(0, preg_match('/(?<!auto)correct/', $r1isi));
            $this->assertStringContainsString('BACAAN: teks bersama kelompok', $r1isi);
            $this->assertStringContainsString((string) $orang['nomor_induk'], $r1isi);
            $terlihat[$i] = $urut;
        }
        // Pencegah tes yang lulus karena acakan tidak pernah terjadi: di antara 30 acakan lima
        // satuan, paling sedikit satu harus berbeda dari urutan ujian.
        $butir = \App\Libraries\Ujian::beku(\Config\Database::connect(), $u);
        $asli  = \App\Libraries\Pengerjaan::acak($butir, false, false)['soal'];
        $beda  = 0;
        for ($i = 0; $i < 30; $i++) {
            $beda += (int) (\App\Libraries\Pengerjaan::acak($butir, true, true)['soal'] !== $asli);
        }
        $this->assertGreaterThan(0, $beda);
        $this->assertSame(array_values($q), $asli);
    }

    public function testSimpanJawabanDanPencacah(): void
    {
        $d   = $this->kerjaPer();
        $mhs = $d['mhs'];
        $a   = (int) $d['a']['id'];
        $q   = $d['q'];
        $opsi = array_map('intval', array_column($this->db->query("SELECT id FROM exam_options WHERE exam_question_id = {$q['PG']} ORDER BY position")->fetch_all(MYSQLI_ASSOC), 'id'));

        $r = $this->simpan($mhs, $d['sesi'], $d['token'], $q['ISIAN'], 'Jakarta', 1);
        $this->assertSame(200, $r->status, $r->body);
        $j = $r->json();
        $this->assertTrue($j['ok']);
        $this->assertSame(1, $j['n']);
        $this->assertMatchesRegularExpression('/^\d\d:\d\d:\d\d$/', $j['disimpan']);
        $this->assertEqualsWithDelta(1200, $j['sisa'], 15);
        $this->assertStringContainsString('no-store', (string) $r->header('Cache-Control'));
        $this->assertSame('Jakarta', $this->jawaban($a, $q['ISIAN'])['text']);

        // Simpan yang lebih tua tidak menimpa yang lebih baru.
        $this->assertSame(200, $this->simpan($mhs, $d['sesi'], $d['token'], $q['ISIAN'], 'Jakarta Raya', 5)->status);
        $tua = $this->simpan($mhs, $d['sesi'], $d['token'], $q['ISIAN'], 'tertinggal', 3)->json();
        $this->assertSame(5, $tua['n']);
        $baris = $this->jawaban($a, $q['ISIAN']);
        $this->assertSame(['Jakarta Raya', '5'], [$baris['text'], (string) $baris['counter']]);

        // Keempat tipe, ragu-ragu, dan menghapus pilihan.
        $this->assertSame(200, $this->simpan($mhs, $d['sesi'], $d['token'], $q['BS'], 'salah', 1)->status);
        $this->assertSame('0', (string) $this->jawaban($a, $q['BS'])['benar']);
        $this->assertSame(200, $this->simpan($mhs, $d['sesi'], $d['token'], $q['PG'], $opsi[2], 1, true)->status);
        $baris = $this->jawaban($a, $q['PG']);
        $this->assertSame([(string) $opsi[2], '1'], [(string) $baris['option_id'], (string) $baris['ragu']]);
        $this->assertSame(200, $this->simpan($mhs, $d['sesi'], $d['token'], $q['PG'], null, 2)->status);
        $this->assertNull($this->jawaban($a, $q['PG'])['option_id']);
        $this->assertSame(200, $this->simpan($mhs, $d['sesi'], $d['token'], $q['URAIAN'], "baris satu\r\nbaris dua — ünï 字", 1)->status);
        $this->assertSame("baris satu\nbaris dua — ünï 字", $this->jawaban($a, $q['URAIAN'])['text']);
        $this->assertNotNull($this->pengerjaan($d['sesi'], (string) $d['mhsA']['id'])['last_saved_at']);

        // Yang tidak sah ditolak 422 dan tidak mengubah apa pun.
        $sebelum = $this->sidikBank();
        $opsiLain = (int) $this->db->query('SELECT MAX(id) + 1000 AS id FROM exam_options')->fetch_assoc()['id'];
        foreach ([
            'opsi bukan milik soal'   => [$q['PG'], $opsiLain, 9],
            'opsi sebagai teks'       => [$q['PG'], (string) $opsi[0], 9],
            'benar/salah nilai asing' => [$q['BS'], 'mungkin', 9],
            'isian terlalu panjang'   => [$q['ISIAN'], str_repeat('a', 201), 9],
            'uraian terlalu panjang'  => [$q['URAIAN'], str_repeat('b', 20001), 9],
            'teks untuk soal pilihan' => [$q['ISIAN'], 12, 9],
            'soal ujian lain'         => [99999999, 'x', 9],
            'pencacah nol'            => [$q['ISIAN'], 'x', 0],
        ] as $apa => [$soal, $nilai, $n]) {
            $this->assertSame(422, $this->simpan($mhs, $d['sesi'], $d['token'], $soal, $nilai, $n)->status, $apa);
        }
        $this->assertSame(422, $mhs->postJson("/api/sesi/{$d['sesi']}/jawaban", ['soal' => $q['ISIAN']], ['X-CSRF-TOKEN' => $this->csrf($mhs)])->status);
        $this->assertSame($sebelum, $this->sidikBank(), 'simpan yang ditolak mengubah data');
        // Tanpa token CSRF tidak ada yang tersimpan.
        $tanpa = $mhs->postJson("/api/sesi/{$d['sesi']}/jawaban", ['halaman' => $d['token'], 'soal' => $q['ISIAN'], 'nilai' => 'curang', 'ragu' => false, 'n' => 50]);
        $this->assertNotSame(200, $tanpa->status);
        $this->assertSame('Jakarta Raya', $this->jawaban($a, $q['ISIAN'])['text']);
        // Batas 200 karakter dihitung per karakter, bukan per byte.
        $this->assertSame(200, $this->simpan($mhs, $d['sesi'], $d['token'], $q['ISIAN'], str_repeat('字', 200), 10)->status);
    }

    /**
     * Gerbang: "submitting after the deadline is refused". Satu aturan untuk setiap simpan:
     * sebelum tenggat diterima; sampai 10 detik sesudahnya masih diterima; sesudah itu ditolak.
     */
    public function testAturanWaktuDanTenggangSepuluhDetik(): void
    {
        $d   = $this->kerjaPer();
        $mhs = $d['mhs'];
        $a   = (int) $d['a']['id'];
        $q   = $d['q'];

        $this->tenggatLalu($a, -5); // tenggat 5 detik lagi
        $this->assertSame(200, $this->simpan($mhs, $d['sesi'], $d['token'], $q['ISIAN'], 'sebelum tenggat', 1)->status);
        $this->tenggatLalu($a, 7);  // tenggat 7 detik lalu: masih dalam tenggang
        $r = $this->simpan($mhs, $d['sesi'], $d['token'], $q['ISIAN'], 'dalam tenggang', 2);
        $this->assertSame(200, $r->status, $r->body);
        $this->assertLessThan(0, $r->json()['sisa']);
        // Bagi halaman, pengerjaan itu sudah berakhir walau simpan terakhirnya masih diterima.
        $k = $mhs->get("/api/sesi/{$d['sesi']}/keadaan?halaman={$d['token']}")->json()['pengerjaan'];
        $this->assertFalse($k['berjalan']);
        $this->assertFalse($k['dikumpulkan']);

        $this->tenggatLalu($a, 12); // 12 detik lalu: di luar tenggang
        $sebelum = $this->sidikBank();
        $r       = $this->simpan($mhs, $d['sesi'], $d['token'], $q['ISIAN'], 'terlambat', 3);
        $this->assertSame(409, $r->status);
        $this->assertSame('waktu_habis', $r->json()['error']);
        $this->assertSame($sebelum, $this->sidikBank(), 'simpan sesudah tenggat mengubah data');
        $this->assertSame('dalam tenggang', $this->jawaban($a, $q['ISIAN'])['text']);

        // Halaman ujian tidak dibuka lagi; "Kumpulkan" sesudah tenggat tidak mengubah apa pun;
        // tanda terima sudah tampil walau penjadwal belum lewat (keadaan diturunkan dari waktu).
        $this->assertStringEndsWith("/ujian-saya/{$d['sesi']}", (string) $mhs->get("/ujian-saya/{$d['sesi']}/kerjakan")->header('Location'));
        $this->kirim($mhs, "/ujian-saya/{$d['sesi']}/kumpulkan");
        $this->assertNull($this->pengerjaan($d['sesi'], (string) $d['mhsA']['id'])['submitted_at']);
        $terima = $mhs->get("/ujian-saya/{$d['sesi']}")->body;
        $this->assertStringContainsString('data-tanda-terima', $terima);
        $this->assertStringContainsString('1 dari 6', $terima);
        $this->assertStringContainsString('data-ujian-saya="selesai"', $mhs->get('/beranda')->body);

        // Penjadwal membawa keadaan tersimpan menyusul: waktu_habis, pada tenggatnya sendiri.
        [$kode, $keluar] = $this->spark(['ujian:jadwal', '--sekali']);
        $this->assertSame(0, $kode, $keluar);
        $this->assertStringContainsString('1 pengerjaan ditandai', $keluar);
        $b = $this->pengerjaan($d['sesi'], (string) $d['mhsA']['id']);
        $this->assertSame('waktu_habis', $b['submit_how']);
        $this->assertSame(1200, strtotime($b['submitted_at']) - strtotime($b['started_at']), 'waktu kumpul harus tenggatnya sendiri');
        [, $lagi] = $this->spark(['ujian:jadwal', '--sekali']);
        $this->assertStringContainsString('0 pengerjaan ditandai', $lagi, 'lintasan kedua harus tidak mengubah apa pun');
    }

    public function testPenjadwalMenungguTenggangDanTambahanWaktuMenggeserTenggat(): void
    {
        $d = $this->kerjaPer();
        $a = (int) $d['a']['id'];
        // Di dalam tenggang penjadwal belum menandai: simpan terakhir yang di jalan masih diterima.
        $this->tenggatLalu($a, 5);
        [, $keluar] = $this->spark(['ujian:jadwal', '--sekali']);
        $this->assertStringContainsString('0 pengerjaan ditandai', $keluar);
        // Tambahan waktu masuk ke rumus tenggat (pemberiannya sendiri tahap U5).
        $this->db->query("UPDATE attempts SET extra_minutes = 3 WHERE id = {$a}");
        $k = $d['mhs']->get("/api/sesi/{$d['sesi']}/keadaan")->json()['pengerjaan'];
        $this->assertTrue($k['berjalan']);
        $this->assertEqualsWithDelta(175, $k['sisa'], 5);
        $this->assertSame(3, $k['tambahan']);
        $this->assertSame(200, $this->simpan($d['mhs'], $d['sesi'], $d['token'], $d['q']['ISIAN'], 'berkat tambahan waktu', 1)->status);
    }

    public function testKumpulkanMengunciJawaban(): void
    {
        $d   = $this->kerjaPer();
        $mhs = $d['mhs'];
        $q   = $d['q'];
        $this->simpan($mhs, $d['sesi'], $d['token'], $q['ISIAN'], 'jawaban akhir', 1);
        $this->simpan($mhs, $d['sesi'], $d['token'], $q['BS'], 'benar', 1);
        $r = $this->kirim($mhs, "/ujian-saya/{$d['sesi']}/kumpulkan");
        $this->assertStringEndsWith("/ujian-saya/{$d['sesi']}", (string) $r->header('Location'));
        $b = $this->pengerjaan($d['sesi'], (string) $d['mhsA']['id']);
        $this->assertSame('mahasiswa', $b['submit_how']);
        $this->assertNotNull($b['submitted_at']);

        $terima = $mhs->get("/ujian-saya/{$d['sesi']}")->body;
        $this->assertStringContainsString('data-tanda-terima', $terima);
        $this->assertStringContainsString('2 dari 6', $terima);
        // Tanda terima tidak memuat skor, kunci atau soal.
        foreach (['ISIAN:', 'BS:', 'poin', 'Poin', 'skor', 'Skor', 'nilai objektif'] as $terlarang) {
            $this->assertStringNotContainsString($terlarang, $terima, $terlarang);
        }
        // Sesudah dikumpulkan: simpan ditolak, halaman ujian tidak terbuka, kumpul kedua tidak mengubah waktu.
        $sebelum = $this->sidikBank();
        $r       = $this->simpan($mhs, $d['sesi'], $d['token'], $q['ISIAN'], 'diubah sesudah kumpul', 2);
        $this->assertSame(409, $r->status);
        $this->assertSame('dikumpulkan', $r->json()['error']);
        $this->assertStringEndsWith("/ujian-saya/{$d['sesi']}", (string) $mhs->get("/ujian-saya/{$d['sesi']}/kerjakan")->header('Location'));
        $this->kirim($mhs, "/ujian-saya/{$d['sesi']}/kumpulkan");
        $this->kirim($mhs, "/ujian-saya/{$d['sesi']}/mulai");
        $this->assertSame($sebelum, $this->sidikBank());
        $this->assertTrue($mhs->get("/api/sesi/{$d['sesi']}/keadaan")->json()['pengerjaan']['dikumpulkan']);
    }

    public function testDuaHalamanYangBaruMengambilAlih(): void
    {
        $d   = $this->kerjaPer();
        $mhs = $d['mhs'];
        $q   = $d['q'];
        $a   = (int) $d['a']['id'];
        $this->assertSame(200, $this->simpan($mhs, $d['sesi'], $d['token'], $q['ISIAN'], 'dari halaman lama', 1)->status);
        // Komputer kedua: sesi login lain, pengerjaan yang sama.
        $kedua     = $this->sebagai($d['mhsA']);
        $tokenBaru = $this->bukaHalamanUjian($kedua, $d['sesi']);
        $this->assertNotSame($d['token'], $tokenBaru);
        // Halaman baru memuat jawaban yang sudah tersimpan.
        $this->assertStringContainsString('value="dari halaman lama"', $kedua->get("/ujian-saya/{$d['sesi']}/kerjakan")->body);
        $tokenBaru = $this->bukaHalamanUjian($kedua, $d['sesi']);

        // Halaman lama diberi tahu pada simpan berikutnya dan berhenti; jawabannya tidak masuk.
        $r = $this->simpan($mhs, $d['sesi'], $d['token'], $q['ISIAN'], 'dari halaman lama lagi', 2);
        $this->assertSame(409, $r->status);
        $this->assertSame('diambil_alih', $r->json()['error']);
        $this->simpan($mhs, $d['sesi'], $d['token'], $q['ISIAN'], 'dan lagi', 3);
        $this->assertTrue($mhs->get("/api/sesi/{$d['sesi']}/keadaan?halaman={$d['token']}")->json()['pengerjaan']['diambil_alih']);
        $this->assertFalse($kedua->get("/api/sesi/{$d['sesi']}/keadaan?halaman={$tokenBaru}")->json()['pengerjaan']['diambil_alih']);
        $this->assertSame('dari halaman lama', $this->jawaban($a, $q['ISIAN'])['text']);
        // Halaman baru melanjutkan, dengan pencacah di atas yang tersimpan.
        $this->assertSame(200, $this->simpan($kedua, $d['sesi'], $tokenBaru, $q['ISIAN'], 'dari halaman baru', 2)->status);
        $this->assertSame('dari halaman baru', $this->jawaban($a, $q['ISIAN'])['text']);
        // Satu peristiwa integritas untuk halaman lama itu, walau ia mencoba dua kali.
        $e = $this->db->query("SELECT user_id, attempt_id, session_id, kind, ip FROM integrity_events WHERE kind = 'page_taken_over'")->fetch_all(MYSQLI_ASSOC);
        $this->assertCount(1, $e);
        $this->assertSame([(string) $d['mhsA']['id'], (string) $a, (string) $d['sesi']], [$e[0]['user_id'], (string) $e[0]['attempt_id'], (string) $e[0]['session_id']]);
        $this->assertNotSame('', (string) $e[0]['ip']);
    }

    public function testDikendalikanDosenMenungguTerlambatDanDiakhiri(): void
    {
        $d     = $this->dunia();
        $http  = $this->sebagai($d['pengampuX']);
        $u     = $this->ujianTerbit($http, $d['matkulX'], 'Ujian serentak');
        $sesi  = $this->beri($http, $d['matkulX'], $u, $d['kelasXA']);
        $q     = $this->soalBeku($u);
        $kedua = $this->user('mahasiswa', 'Mahasiswa Terlambat');
        $this->daftar($kedua, $d['kelasXA']);
        $mhs  = $this->sebagai($d['mhsA']);
        $telat = $this->sebagai($kedua);

        // Sebelum dosen memulai: halaman menunggu; Mulai tidak membuat pengerjaan.
        $r = $mhs->get("/ujian-saya/{$sesi}");
        $this->assertStringContainsString('Menunggu dosen memulai ujian', $r->body);
        $this->assertStringContainsString('data-otomatis="1"', $r->body);
        $this->assertStringEndsWith('?pesan=belum', (string) $this->kirim($mhs, "/ujian-saya/{$sesi}/mulai")->header('Location'));
        $this->assertFalse($mhs->get("/api/sesi/{$sesi}/keadaan")->json()['menerima']);

        // Dosen memulai tanpa waktu akhir: mahasiswa melihat waktu berlalu, bukan hitung mundur.
        $this->kirim($http, "/sesi/{$sesi}/mulai");
        $this->assertTrue($mhs->get("/api/sesi/{$sesi}/keadaan")->json()['menerima']);
        $a = $this->mulaiKerja($mhs, $sesi, (string) $d['mhsA']['id']);
        $token = $this->bukaHalamanUjian($mhs, $sesi);
        $k = $mhs->get("/api/sesi/{$sesi}/keadaan")->json()['pengerjaan'];
        $this->assertTrue($k['tanpa_akhir']);
        $this->assertTrue($k['berjalan']);
        // Dosen menetapkan akhir 40 menit lagi; sesi sudah berjalan 25 menit.
        $this->db->query("UPDATE sessions SET started_at = UTC_TIMESTAMP() - INTERVAL 25 MINUTE WHERE id = {$sesi}");
        $this->kirim($http, "/sesi/{$sesi}/akhir", ['menit' => '40']);
        $k = $mhs->get("/api/sesi/{$sesi}/keadaan")->json()['pengerjaan'];
        $this->assertFalse($k['tanpa_akhir']);
        $this->assertEqualsWithDelta(2400, $k['sisa'], 5);
        $this->assertEqualsWithDelta(1500, $k['berlalu'], 5);
        // Yang datang terlambat bergabung dengan sisa waktu yang sama, bukan durasi baru.
        $this->mulaiKerja($telat, $sesi, (string) $kedua['id']);
        $tokenTelat = $this->bukaHalamanUjian($telat, $sesi);
        $this->assertEqualsWithDelta(2400, $telat->get("/api/sesi/{$sesi}/keadaan")->json()['pengerjaan']['sisa'], 5);
        $this->assertSame(200, $this->simpan($telat, $sesi, $tokenTelat, $q['ISIAN'], 'jawaban si terlambat', 1)->status);
        // Hitungan di halaman sesi dosen.
        $hal = $http->get("/sesi/{$sesi}")->body;
        $this->assertStringContainsString('Hadir 2 dari 2 · mengerjakan 2', $hal);
        $this->assertStringNotContainsString('jawaban si terlambat', $hal, 'halaman sesi tidak menampilkan jawaban');

        // Mahasiswa terlambat mendapat tambahan 5 menit; lalu dosen mengakhiri.
        $this->db->query("UPDATE attempts SET extra_minutes = 5 WHERE user_id = '{$kedua['id']}'");
        $this->kirim($http, "/sesi/{$sesi}/akhiri");
        $k = $mhs->get("/api/sesi/{$sesi}/keadaan")->json()['pengerjaan'];
        $this->assertTrue($k['diakhiri']);
        $this->assertEqualsWithDelta(30, $k['sisa'], 3);
        $this->assertSame(200, $this->simpan($mhs, $sesi, $token, $q['ISIAN'], 'ketikan terakhir', 1)->status, 'selama 30 detik pengumuman, simpan masih diterima');
        // Akhir sesi lewat 15 detik: yang tanpa tambahan ditolak, yang punya tambahan masih berjalan.
        $this->db->query("UPDATE sessions SET end_at = UTC_TIMESTAMP() - INTERVAL 15 SECOND WHERE id = {$sesi}");
        $this->assertSame(409, $this->simpan($mhs, $sesi, $token, $q['ISIAN'], 'sesudah diakhiri', 2)->status);
        $this->assertSame(200, $this->simpan($telat, $sesi, $tokenTelat, $q['ISIAN'], 'masih punya tambahan', 2)->status);
        $this->assertEqualsWithDelta(285, $telat->get("/api/sesi/{$sesi}/keadaan")->json()['pengerjaan']['sisa'], 5);
        // Sesi sudah berakhir: mahasiswa ketiga tidak bisa lagi mulai.
        $ketiga = $this->user('mahasiswa', 'Mahasiswa Kesiangan');
        $this->daftar($ketiga, $d['kelasXA']);
        $this->assertStringEndsWith('?pesan=belum', (string) $this->kirim($this->sebagai($ketiga), "/ujian-saya/{$sesi}/mulai")->header('Location'));
        $this->assertStringContainsString('data-lewat', $this->sebagai($ketiga)->get("/ujian-saya/{$sesi}")->body);

        [, $keluar] = $this->spark(['ujian:jadwal', '--sekali']);
        $this->assertStringContainsString('1 pengerjaan ditandai', $keluar);
        $this->assertSame('diakhiri_dosen', $this->pengerjaan($sesi, (string) $d['mhsA']['id'])['submit_how']);
        $this->assertNull($this->pengerjaan($sesi, (string) $kedua['id'])['submitted_at']);
        $this->assertSame('ketikan terakhir', $this->jawaban((int) $a['id'], $q['ISIAN'])['text']);
        // Tambahan waktunya pun habis: ia berakhir karena waktu, bukan karena dosen.
        $this->db->query("UPDATE sessions SET end_at = UTC_TIMESTAMP() - INTERVAL 6 MINUTE WHERE id = {$sesi}");
        $this->spark(['ujian:jadwal', '--sekali']);
        $this->assertSame('waktu_habis', $this->pengerjaan($sesi, (string) $kedua['id'])['submit_how']);
        $this->assertStringContainsString('Ujian diakhiri dosen', $mhs->get("/ujian-saya/{$sesi}")->body);
    }

    /**
     * Lintas kelas (IDOR): mahasiswa B bukan anggota kelas XA.
     */
    public function testMahasiswaKelasLainTidakMelihatDanTidakMenyentuh(): void
    {
        $d    = $this->kerjaPer();
        $sesi = $d['sesi'];
        $q    = $d['q'];
        $this->simpan($d['mhs'], $sesi, $d['token'], $q['ISIAN'], 'milik mahasiswa A', 1);

        foreach (['mhsB', 'mhsY', 'mhsLulus', 'mhsMundur', 'mhsArsip', 'mhsLama'] as $siapa) {
            $asing = $this->sebagai($d[$siapa]);
            $ada   = $asing->get("/ujian-saya/{$sesi}");
            $this->assertSame(404, $ada->status, $siapa);
            $adaIsi = self::inti($ada->body, "/ujian-saya/{$sesi}");
            $this->assertSame(self::inti($asing->get('/ujian-saya/99999999')->body, '/ujian-saya/99999999'), $adaIsi, "{$siapa}: harus sama dengan sesi yang tidak ada");
            $this->assertStringNotContainsString('Kuis kerja', $adaIsi);
            $this->assertStringNotContainsString('Kuis kerja', $asing->get('/beranda')->body);
            $sebelum = $this->sidikBank();
            $this->assertSame(404, $this->kirim($asing, "/ujian-saya/{$sesi}/mulai")->status, $siapa);
            $this->assertSame(404, $asing->get("/ujian-saya/{$sesi}/kerjakan")->status, $siapa);
            $this->assertSame(404, $this->kirim($asing, "/ujian-saya/{$sesi}/kumpulkan")->status, $siapa);
            $this->assertSame(404, $asing->get("/api/sesi/{$sesi}/keadaan?halaman={$d['token']}")->status, $siapa);
            // Token halaman mahasiswa A pun tidak berguna di tangan orang lain.
            $r = $this->simpan($asing, $sesi, $d['token'], $q['ISIAN'], 'ditimpa orang lain', 9);
            $this->assertSame(404, $r->status, $siapa);
            $this->assertSame(404, $asing->postJson("/api/sesi/{$sesi}/peristiwa", ['jenis' => 'paste_blocked', 'jalur' => 'paste'], ['X-CSRF-TOKEN' => $this->csrf($asing)])->status, $siapa);
            $this->assertSame($sebelum, $this->sidikBank(), "{$siapa} mengubah data sesi kelas lain");
        }
        $this->assertSame('milik mahasiswa A', $this->jawaban((int) $d['a']['id'], $q['ISIAN'])['text']);
        $this->assertSame('1', (string) $this->db->query('SELECT COUNT(*) AS n FROM attempts')->fetch_assoc()['n']);

        // Mahasiswa yang keluar dari kelas SESUDAH mulai tetap memegang pengerjaannya (SPEC §7.4);
        // yang belum mulai kehilangan sesinya.
        $this->roster->query("UPDATE enrollments SET status = 'MENGUNDURKAN_DIRI' WHERE user_id = '{$d['mhsA']['id']}'");
        $this->assertSame(200, $this->simpan($d['mhs'], $sesi, $d['token'], $q['ISIAN'], 'tetap bisa', 2)->status);
        $this->assertStringContainsString('Kuis kerja', $d['mhs']->get('/beranda')->body);
    }

    public function testGambarHanyaSelamaPengerjaanBerjalan(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengampuX']);
        $x    = $d['matkulX'];
        $s    = $this->buatSoal($http, $x, 'isian', ['text' => 'Lihat gambar', 'alt' => 'Gambar soal'], ['gambar' => ['a.png', self::gambar()]]);
        $u    = $this->buatDraf($http, $x, 'Kuis bergambar', ["s{$s}"]);
        $this->kirim($http, "/mata-kuliah/{$x}/ujian/{$u}", ['judul' => 'Kuis bergambar', 'jenis' => 'kuis', 'petunjuk' => '', 'mode' => 'per_mahasiswa', 'durasi' => '10']);
        $kunci = (string) $this->db->query('SELECT kunci FROM images')->fetch_assoc()['kunci'];
        $mhs   = $this->sebagai($d['mhsA']);
        $ajar  = $this->sebagai($d['pengajarA']);

        // Draf: pengajar dan mahasiswa tidak mendapat gambarnya.
        $this->assertSame(404, $ajar->get("/gambar/{$kunci}")->status);
        $this->kirim($http, "/mata-kuliah/{$x}/ujian/{$u}/terbit");
        $sesi = $this->beri($http, $x, $u, $d['kelasXA']);
        // Terbit dan diberikan ke kelas XA: pengajar A ya, pengajar B tidak; mahasiswa belum (belum mulai).
        $this->assertSame(200, $ajar->get("/gambar/{$kunci}")->status);
        $this->assertSame(404, $this->sebagai($d['pengajarB'])->get("/gambar/{$kunci}")->status);
        $this->assertSame(404, $mhs->get("/gambar/{$kunci}")->status);
        $this->mulaiKerja($mhs, $sesi, (string) $d['mhsA']['id']);
        $r = $mhs->get("/gambar/{$kunci}");
        $this->assertSame(200, $r->status);
        $this->assertSame('image/png', $r->header('Content-Type'));
        $this->assertSame(404, $this->sebagai($d['mhsB'])->get("/gambar/{$kunci}")->status);
        // Sesudah dikumpulkan: tidak lagi.
        $this->kirim($mhs, "/ujian-saya/{$sesi}/kumpulkan");
        $this->assertSame(404, $mhs->get("/gambar/{$kunci}")->status);
    }

    public function testPeristiwaTempelDanPengecualianPasteAllowed(): void
    {
        $d   = $this->kerjaPer();
        $mhs = $d['mhs'];
        $kirim = fn (Http $h, array $badan): Http => $h->postJson("/api/sesi/{$d['sesi']}/peristiwa", $badan, ['X-CSRF-TOKEN' => $this->csrf($h)]);

        $this->assertSame(200, $kirim($mhs, ['jenis' => 'paste_blocked', 'jalur' => 'keyboard'])->status);
        $this->assertSame(422, $kirim($mhs, ['jenis' => 'paste_blocked', 'jalur' => 'jalur-karangan'])->status);
        // Peristiwa SEB hanya ditulis server sendiri; halaman tidak bisa mengarangnya.
        foreach (['seb_rejected', 'seb_terminated', 'page_taken_over', 'karangan'] as $jenis) {
            $this->assertSame(422, $kirim($mhs, ['jenis' => $jenis, 'jalur' => 'paste'])->status, $jenis);
        }
        // Tahap U4: tiga peristiwa halaman lainnya, dengan nilai yang diperiksa; teks bebas dibuang.
        $this->assertSame(200, $kirim($mhs, ['jenis' => 'focus_lost', 'durasi' => 12, 'catatan' => '<script>'])->status);
        $this->assertSame(200, $kirim($mhs, ['jenis' => 'display_changed', 'lebar' => 1920, 'tinggi' => 1080, 'layar' => true])->status);
        $this->assertSame(200, $kirim($mhs, ['jenis' => 'reconnected', 'detik' => 75])->status);
        foreach ([['jenis' => 'focus_lost', 'durasi' => '12'], ['jenis' => 'focus_lost', 'durasi' => -1], ['jenis' => 'display_changed', 'lebar' => 0, 'tinggi' => 10], ['jenis' => 'reconnected', 'detik' => 59], ['jenis' => 'reconnected']] as $salah) {
            $this->assertSame(422, $kirim($mhs, $salah)->status, json_encode($salah));
        }
        $lain = $this->db->query("SELECT kind, detail FROM integrity_events WHERE kind <> 'paste_blocked' ORDER BY id")->fetch_all(MYSQLI_ASSOC);
        // assertEquals: MySQL mengurutkan kunci kolom JSON sendiri. Kunci `catatan` kiriman tidak ikut tersimpan.
        $this->assertEquals(
            [['focus_lost', ['durasi' => 12]], ['display_changed', ['lebar' => 1920, 'tinggi' => 1080, 'layar' => true]], ['reconnected', ['detik' => 75]]],
            array_map(static fn (array $b): array => [$b['kind'], json_decode((string) $b['detail'], true)], $lain),
        );
        $this->db->query("DELETE FROM integrity_events WHERE kind <> 'paste_blocked'");
        $e = $this->db->query('SELECT kind, detail, user_id, attempt_id, user_agent FROM integrity_events')->fetch_all(MYSQLI_ASSOC);
        $this->assertCount(1, $e);
        $this->assertSame(['paste_blocked', ['jalur' => 'keyboard'], (string) $d['mhsA']['id'], (string) $d['a']['id']], [$e[0]['kind'], json_decode((string) $e[0]['detail'], true), $e[0]['user_id'], (string) $e[0]['attempt_id']]);

        // paste_allowed (LokaAuth, D12): tidak ada yang dicatat, dan halamannya diberi tahu.
        $this->idn->query("UPDATE users SET paste_allowed = 1 WHERE id = '{$d['mhsA']['id']}'");
        $bebas = $this->sebagai($d['mhsA']);
        $this->assertStringContainsString('data-boleh-tempel="1"', $bebas->get("/ujian-saya/{$d['sesi']}/kerjakan")->body);
        $this->assertSame(200, $kirim($bebas, ['jenis' => 'paste_blocked', 'jalur' => 'paste'])->status);
        $this->assertSame('1', (string) $this->db->query('SELECT COUNT(*) AS n FROM integrity_events')->fetch_assoc()['n']);
    }

    public function testBatasLajuSimpanTidakMenolakPemakaianBiasa(): void
    {
        $d = $this->kerjaPer();
        $q = $d['q'];
        // 30 simpan beruntun (jauh di atas pengetik mana pun dalam beberapa detik) semuanya diterima.
        for ($n = 1; $n <= 30; $n++) {
            $this->assertSame(200, $this->simpan($d['mhs'], $d['sesi'], $d['token'], $q['URAIAN'], 'ketikan ke-' . $n, $n)->status);
        }
        // Di atas batas per menit: 429, dan jawaban terakhir yang sah tetap ada.
        $u = parse_url((string) getenv('REDIS_URL'));
        $r = new \Redis();
        $r->connect((string) $u['host'], (int) ($u['port'] ?? 6379));
        $r->auth((string) $u['pass']);
        $this->assertSame('15', ltrim((string) $u['path'], '/'), 'tes hanya memakai Redis DB 15');
        $r->select(15);
        $kunci = $r->keys('lokaujian:laju:simpan:' . $d['sesi'] . ':' . $d['mhsA']['id'] . ':*');
        $this->assertNotSame([], $kunci);
        foreach ($kunci as $k) {
            $r->set($k, (string) \App\Libraries\Pengerjaan::LAJU_PER_MENIT, 120);
        }
        $menit = intdiv(time(), 60);
        $r->set('lokaujian:laju:simpan:' . $d['sesi'] . ':' . $d['mhsA']['id'] . ':' . $menit, (string) \App\Libraries\Pengerjaan::LAJU_PER_MENIT, 120);
        $r->set('lokaujian:laju:simpan:' . $d['sesi'] . ':' . $d['mhsA']['id'] . ':' . ($menit + 1), (string) \App\Libraries\Pengerjaan::LAJU_PER_MENIT, 120);
        $tolak = $this->simpan($d['mhs'], $d['sesi'], $d['token'], $q['URAIAN'], 'banjir', 99);
        $this->assertSame(429, $tolak->status);
        $this->assertSame('ketikan ke-30', $this->jawaban((int) $d['a']['id'], $q['URAIAN'])['text']);
        // Redis dikosongkan (SPEC §14: tidak ada yang hilang bila Redis kosong): simpan diterima lagi.
        $r->del($r->keys('lokaujian:laju:simpan:*'));
        $this->assertSame(200, $this->simpan($d['mhs'], $d['sesi'], $d['token'], $q['URAIAN'], 'sesudah Redis kosong', 100)->status);
    }

    /**
     * Tahap U3 berjalan dengan SEB_ENFORCEMENT=disabled. Di kontainer kedua, dengan `enabled`,
     * mahasiswa DITOLAK di setiap rute ujian — bukan dibiarkan lewat tanpa pemeriksaan — sementara
     * beranda (daftar, hanya dibaca) dan staf tidak terpengaruh.
     */
    public function testDenganPenegakanAktifMahasiswaDitolak(): void
    {
        $d   = $this->kerjaPer();
        $seb = new Http((string) getenv('APP_SEB_TES_URL'));
        $seb->cookies = $d['mhs']->cookies;
        $this->assertSame(200, $seb->get('/beranda')->status);
        $this->assertStringNotContainsString('MODE UJI', $seb->body);
        $csrf = preg_match('/data-csrf="([^"]+)"/', $seb->body, $m) === 1 ? $m[1] : '';
        foreach (["/ujian-saya/{$d['sesi']}", "/ujian-saya/{$d['sesi']}/kerjakan"] as $jalur) {
            $r = $seb->get($jalur);
            $this->assertSame(403, $r->status, $jalur);
            $this->assertStringNotContainsString('ISIAN:', $r->body);
        }
        $r = $seb->get("/api/sesi/{$d['sesi']}/keadaan");
        $this->assertSame(403, $r->status);
        $this->assertSame('seb_wajib', $r->json()['error']);
        $sebelum = $this->sidikBank();
        $r       = $seb->postJson("/api/sesi/{$d['sesi']}/jawaban", ['halaman' => $d['token'], 'soal' => $d['q']['ISIAN'], 'nilai' => 'tanpa SEB', 'ragu' => false, 'n' => 5], ['X-CSRF-TOKEN' => $csrf]);
        $this->assertSame(403, $r->status);
        $this->assertSame(403, $seb->post("/ujian-saya/{$d['sesi']}/kumpulkan", ['csrf' => $csrf])->status);
        $this->assertSame($sebelum, $this->sidikBank());
        // Staf memakai peramban biasa.
        $staf = new Http((string) getenv('APP_SEB_TES_URL'));
        $staf->cookies = $d['http']->cookies;
        $this->assertSame(200, $staf->get("/sesi/{$d['sesi']}")->status);
    }
}
