<?php

declare(strict_types=1);

namespace Tests\Bank;

use Tests\Support\UjianCase;

/**
 * Bank soal (SPEC-LOKAUJIAN.md §5.1, §5.4): keempat tipe lewat form sungguhan, aturan isinya,
 * arsip dan hapus, cari dan saring, pratinjau tanpa kunci, dan lingkup lintas mata kuliah.
 */
final class SoalTest extends UjianCase
{
    public function testKeempatTipeDibuatDisuntingDanDipratinjau(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengampuX']);
        $x    = '/mata-kuliah/' . $d['matkulX'] . '/bank';

        $bs = $this->buatSoal($http, $d['matkulX'], 'benar_salah', ['text' => 'Bumi mengelilingi matahari.', 'answer' => 'benar', 'points' => '1']);
        $pg = $this->buatSoal($http, $d['matkulX'], 'pilihan_ganda', [
            'text' => 'Hasil dari `2 + 3 * 4` adalah …', 'points' => '2,5',
            'opsi_teks[0]' => '20', 'opsi_teks[1]' => '14', 'opsi_teks[2]' => '', 'opsi_teks[3]' => '24', 'benar' => '1',
        ]);
        $is = $this->buatSoal($http, $d['matkulX'], 'isian', ['text' => 'Ibu kota Indonesia?', 'points' => '0.25']);
        $ur = $this->buatSoal($http, $d['matkulX'], 'uraian', ['text' => "Jelaskan **rekursi**.\n\n<script>alert(1)</script>", 'points' => '100']);

        $baris = [];
        foreach ($this->db->query('SELECT id, type, points, answer, course_id, group_id, created_by FROM questions ORDER BY id')->fetch_all(MYSQLI_ASSOC) as $b) {
            $baris[(int) $b['id']] = $b;
        }
        $this->assertSame(['benar_salah', '1.00', '1'], [$baris[$bs]['type'], $baris[$bs]['points'], $baris[$bs]['answer']]);
        $this->assertSame(['pilihan_ganda', '2.50', null], [$baris[$pg]['type'], $baris[$pg]['points'], $baris[$pg]['answer']]);
        $this->assertSame('0.25', $baris[$is]['points']);
        $this->assertSame('100.00', $baris[$ur]['points']);
        $this->assertSame((string) $d['matkulX'], (string) $baris[$ur]['course_id']);
        $this->assertSame($d['pengampuX']['id'], $baris[$ur]['created_by']);
        // Baris opsi yang dikosongkan diabaikan; urutan dan kuncinya tersimpan.
        $opsi = $this->db->query("SELECT position, text, correct FROM question_options WHERE question_id = {$pg} ORDER BY position")->fetch_all(MYSQLI_NUM);
        $this->assertSame([['1', '20', '0'], ['2', '14', '1'], ['3', '24', '0']], $opsi);

        // Daftar bank: tipe, poin, kata-kata pertama.
        $daftar = $http->get($x)->body;
        foreach (['Bumi mengelilingi matahari.', 'Hasil dari 2 + 3 * 4 adalah', 'Ibu kota Indonesia?', 'Jelaskan rekursi.'] as $teks) {
            $this->assertStringContainsString($teks, $daftar);
        }
        $this->assertStringContainsString('>2.5<', $daftar);

        // Pratinjau: Markdown dijalankan, HTML mentah tampil sebagai teks, dan partial soal tidak memuat kunci.
        $r = $http->get("{$x}/soal/{$ur}");
        $this->assertSame(200, $r->status);
        $this->assertStringContainsString('<strong>rekursi</strong>', $r->body);
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $r->body);
        $this->assertStringNotContainsString('<script>alert(1)', $r->body);
        $this->assertStringContainsString('dir="auto"', $r->body);

        $r = $http->get("{$x}/soal/{$pg}");
        $this->assertSame(1, preg_match('#<article class="soal".*?</article>#s', $r->body, $m));
        $this->assertStringNotContainsString('Kunci', $m[0], 'partial soal (yang dilihat mahasiswa) memuat kunci');
        $this->assertStringNotContainsString('checked', $m[0]);
        $this->assertSame(3, substr_count($m[0], 'type="radio"'));
        // Kuncinya ada untuk staf, di luar partial itu.
        $this->assertMatchesRegularExpression('#data-kunci="' . $pg . '">.*?<span class="ltr">B</span>#s', $r->body);

        // Menyunting: tipe tetap, isi dan kunci berubah.
        $r = $this->kirimBerkas($http, "{$x}/soal/{$pg}", [
            'type' => 'isian', 'text' => 'Hasil dari 10 / 4?', 'points' => '3',
            'opsi_teks[0]' => '2', 'opsi_teks[1]' => '2,5', 'benar' => '1',
        ], []);
        $this->assertContains($r->status, [302, 303], substr(strip_tags($r->body), 0, 400));
        $b = $this->db->query("SELECT type, text, points FROM questions WHERE id = {$pg}")->fetch_assoc();
        $this->assertSame(['pilihan_ganda', 'Hasil dari 10 / 4?', '3.00'], [$b['type'], $b['text'], $b['points']]);
        $this->assertSame('2', $this->db->query("SELECT COUNT(*) AS n FROM question_options WHERE question_id = {$pg}")->fetch_assoc()['n']);
        // Form sunting memuat isinya.
        $this->assertStringContainsString('Hasil dari 10 / 4?', $http->get("{$x}/soal/{$pg}/sunting")->body);
    }

    public function testIsiYangSalahDitolakDanTidakAdaYangTersimpan(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengampuX']);
        $x    = '/mata-kuliah/' . $d['matkulX'] . '/bank/soal';
        $pg   = ['type' => 'pilihan_ganda', 'text' => 'Pilih satu', 'points' => '1', 'opsi_teks[0]' => 'a', 'opsi_teks[1]' => 'b', 'benar' => '0'];
        $salah = [
            'poin bukan kelipatan 0,25' => [['points' => '0.3'] + $pg, 'kelipatan 0,25'],
            'poin di atas 100'          => [['points' => '100.25'] + $pg, 'kelipatan 0,25'],
            'poin bukan angka'          => [['points' => 'dua'] + $pg, 'kelipatan 0,25'],
            'tanpa opsi benar'          => [['benar' => ''] + $pg, 'tepat satu opsi'],
            'opsi benar di baris kosong' => [['benar' => '4'] + $pg, 'tepat satu opsi'],
            'hanya satu opsi'           => [['opsi_teks[1]' => ''] + $pg, 'harus ada 2 sampai 6 opsi'],
            'teks kosong'               => [['text' => '   '] + $pg, 'tidak boleh kosong'],
            'benar/salah tanpa kunci'   => [['type' => 'benar_salah', 'text' => 'x', 'points' => '1'], 'pilih kunci jawabannya'],
            'teks terlalu panjang'      => [['type' => 'isian', 'text' => str_repeat('a', 10001), 'points' => '1'], 'paling banyak 10000 karakter; sekarang 10001'],
        ];
        foreach ($salah as $nama => [$isian, $pesan]) {
            $r = $this->kirimBerkas($http, $x, $isian, []);
            $this->assertSame(422, $r->status, $nama);
            $this->assertStringContainsString($pesan, $r->body, $nama);
            $this->assertStringContainsString('data-galat', $r->body, $nama);
        }
        // Yang sudah diketik tidak hilang.
        $r = $this->kirimBerkas($http, $x, ['text' => 'Teks yang sudah saya ketik', 'points' => '0.3'] + $pg, []);
        $this->assertStringContainsString('Teks yang sudah saya ketik', $r->body);
        $this->assertSame('0', $this->db->query('SELECT COUNT(*) AS n FROM questions')->fetch_assoc()['n']);
        // Tipe yang tidak dikenal bukan sebuah halaman.
        $this->assertSame(404, $this->kirimBerkas($http, $x, ['type' => 'esai', 'text' => 'x', 'points' => '1'], [])->status);
        $this->assertSame(404, $http->get($x . '/baru?tipe=esai')->status);
    }

    public function testArsipHapusCariDanSaring(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengampuX']);
        $x    = '/mata-kuliah/' . $d['matkulX'];
        $a    = $this->buatSoal($http, $d['matkulX'], 'isian', ['text' => 'Soal tentang percabangan']);
        $b    = $this->buatSoal($http, $d['matkulX'], 'uraian', ['text' => 'Soal tentang perulangan 100%']);
        $c    = $this->buatSoal($http, $d['matkulX'], 'benar_salah', ['text' => 'Soal untuk dihapus']);

        // Cari dan saring menurut tipe; tanda % di kata cari bukan wildcard.
        $r = $http->get("{$x}/bank?q=percabangan");
        $this->assertStringContainsString('data-butir="s' . $a . '"', $r->body);
        $this->assertStringNotContainsString('data-butir="s' . $b . '"', $r->body);
        $r = $http->get("{$x}/bank?tipe=uraian");
        $this->assertStringContainsString('data-butir="s' . $b . '"', $r->body);
        $this->assertStringNotContainsString('data-butir="s' . $a . '"', $r->body);
        $r = $http->get("{$x}/bank?q=" . rawurlencode('100%'));
        $this->assertStringContainsString('data-butir="s' . $b . '"', $r->body);
        $this->assertStringNotContainsString('data-butir="s' . $a . '"', $http->get("{$x}/bank?q=" . rawurlencode('%'))->body);

        // Soal yang dipakai sebuah ujian — draf sekalipun — tidak dapat dihapus, hanya diarsipkan.
        $ujian = $this->buatDraf($http, $d['matkulX'], 'Kuis pemakai', ['s' . $a], 'kuis');
        $r     = $this->kirim($http, "{$x}/bank/soal/{$a}/hapus");
        $this->assertStringEndsWith("/bank/soal/{$a}?pesan=dipakai", (string) $r->header('Location'));
        $this->assertSame('1', $this->db->query("SELECT COUNT(*) AS n FROM questions WHERE id = {$a}")->fetch_assoc()['n']);
        $this->assertStringContainsString('Kuis pemakai', $http->get("{$x}/bank")->body);
        $this->assertStringNotContainsString("/bank/soal/{$a}/hapus", $http->get("{$x}/bank/soal/{$a}")->body, 'tombol hapus tampil untuk soal yang dipakai');

        $this->kirim($http, "{$x}/bank/soal/{$a}/arsip", ['arsip' => '1']);
        $this->assertStringNotContainsString('data-butir="s' . $a . '"', $http->get("{$x}/bank")->body);
        $this->assertStringContainsString('data-butir="s' . $a . '"', $http->get("{$x}/bank?arsip=1")->body);
        // Ujian yang memakainya tetap memuatnya.
        $this->assertStringContainsString('Soal tentang percabangan', $http->get("{$x}/ujian/{$ujian}")->body);
        $this->kirim($http, "{$x}/bank/soal/{$a}/arsip", ['arsip' => '0']);
        $this->assertStringContainsString('data-butir="s' . $a . '"', $http->get("{$x}/bank")->body);

        // Soal yang tidak dipakai dihapus untuk selamanya, dengan baris audit.
        $r = $this->kirim($http, "{$x}/bank/soal/{$c}/hapus");
        $this->assertStringEndsWith('/bank?pesan=dihapus', (string) $r->header('Location'));
        $this->assertSame('0', $this->db->query("SELECT COUNT(*) AS n FROM questions WHERE id = {$c}")->fetch_assoc()['n']);
        $audit = $this->db->query("SELECT actor_id, target_id, course_id FROM audit_log WHERE action = 'soal.dihapus'")->fetch_all(MYSQLI_ASSOC);
        $this->assertSame([['actor_id' => $d['pengampuX']['id'], 'target_id' => (string) $c, 'course_id' => (string) $d['matkulX']]], $audit);
        $this->assertSame(404, $this->kirim($http, "{$x}/bank/soal/{$c}/hapus")->status);
    }

    /**
     * Lintas mata kuliah (IDOR): pengampu Y tidak dapat membaca atau mengubah soal mata kuliah X,
     * baik lewat alamat mata kuliah X maupun dengan menyelipkan id soal X ke alamat mata kuliahnya
     * sendiri. Jawabannya sama dengan untuk id yang tidak ada.
     */
    public function testSoalMataKuliahLainTidakTerbacaDanTidakTerubah(): void
    {
        $d     = $this->dunia();
        $milik = $this->sebagai($d['pengampuX']);
        $soal  = $this->buatSoal($milik, $d['matkulX'], 'isian', ['text' => 'Rahasia milik X']);
        $ySoal = $this->buatSoal($this->sebagai($d['pengampuY']), $d['matkulY'], 'isian', ['text' => 'Milik Y']);
        $http  = $this->sebagai($d['pengampuY']);
        $y     = '/mata-kuliah/' . $d['matkulY'] . '/bank';
        $x     = '/mata-kuliah/' . $d['matkulX'] . '/bank';

        $this->assertSame(200, $http->get("{$y}/soal/{$ySoal}")->status);
        $sebelum = $this->sidikBank();
        foreach (["{$y}/soal/{$soal}", "{$y}/soal/{$soal}/sunting", "{$x}/soal/{$soal}", "{$x}"] as $jalur) {
            $r = $http->get($jalur);
            $this->assertSame(404, $r->status, $jalur);
            $this->assertStringNotContainsString('Rahasia milik X', $r->body, $jalur);
        }
        // Sama dengan id yang tidak ada.
        $r         = $http->get("{$y}/soal/{$soal}");
        $terlarang = self::inti($r->body, "{$y}/soal/{$soal}");
        $r         = $http->get("{$y}/soal/99999999");
        $this->assertSame(self::inti($r->body, "{$y}/soal/99999999"), $terlarang);

        foreach (["{$y}/soal/{$soal}", "{$y}/soal/{$soal}/arsip", "{$y}/soal/{$soal}/hapus", "{$x}/soal/{$soal}/hapus"] as $jalur) {
            $r = $this->kirimBerkas($http, $jalur, ['type' => 'isian', 'text' => 'Diganti Y', 'points' => '1', 'arsip' => '1'], []);
            $this->assertSame(404, $r->status, 'POST ' . $jalur);
        }
        // Draf ujian di mata kuliah Y tidak bisa memuat soal X.
        $r = $this->kirim($http, '/mata-kuliah/' . $d['matkulY'] . '/ujian', ['judul' => 'Curian', 'jenis' => 'ujian', 'butir' => ['s' . $soal]]);
        $this->assertStringEndsWith('?pesan=butir_asing', (string) $r->header('Location'));
        $this->assertSame($sebelum, $this->sidikBank(), 'permintaan pengampu Y mengubah bank');
        // Daftar bank Y hanya memuat soal Y.
        $daftar = $http->get($y)->body;
        $this->assertStringContainsString('Milik Y', $daftar);
        $this->assertStringNotContainsString('Rahasia milik X', $daftar);

        // Superadmin mengelola semua mata kuliah; pengampu yang diganti kehilangan haknya pada permintaan berikutnya.
        $this->assertSame(200, $this->sebagai($d['superadmin'])->get("{$x}/soal/{$soal}")->status);
        $this->roster->query("UPDATE courses SET owner_id = '{$d['pengampuY']['id']}' WHERE id = {$d['matkulX']}");
        $this->assertSame(200, $http->get("{$x}/soal/{$soal}")->status);
        $this->assertContains($milik->get("{$x}/soal/{$soal}")->status, [403, 404]);
    }

    public function testHalamanMataKuliahMenautkanBankHanyaUntukPengelola(): void
    {
        $d = $this->dunia();
        $x = '/mata-kuliah/' . $d['matkulX'];
        $this->assertStringContainsString($x . '/bank"', $this->sebagai($d['pengampuX'])->get($x)->body);
        $this->assertStringContainsString($x . '/bank"', $this->sebagai($d['superadmin'])->get($x)->body);
        $this->assertStringNotContainsString('/bank"', $this->sebagai($d['pengajarA'])->get($x)->body);
    }
}
