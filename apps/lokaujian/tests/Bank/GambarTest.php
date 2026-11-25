<?php

declare(strict_types=1);

namespace Tests\Bank;

use Tests\Support\UjianCase;

/**
 * Gambar soal (SPEC-LOKAUJIAN.md §5.3, KONTRAK.md §20): pemeriksaan unggahan, penulisan ulang
 * dari piksel, teks alternatif wajib, dan — yang terpenting — gambar hanya keluar lewat
 * GET /gambar/<kunci> sesudah hak akses diperiksa, tidak pernah sebagai berkas statis.
 */
final class GambarTest extends UjianCase
{
    public function testGambarDitulisUlangDanHanyaKeluarSesudahHakAksesDiperiksa(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengampuX']);
        // PNG dengan "muatan" yang ditempelkan di belakangnya: tidak boleh ikut tersimpan.
        $kiriman = self::gambar('png') . '<?php echo "MUATAN-RAHASIA"; ?>';
        $soal    = $this->buatSoal($http, $d['matkulX'], 'isian', ['text' => 'Lihat gambar', 'alt' => 'Diagram alir sederhana'], ['gambar' => ['../../etc/passwd.php', $kiriman]]);

        $g = $this->db->query('SELECT * FROM images')->fetch_all(MYSQLI_ASSOC);
        $this->assertCount(1, $g);
        $g = $g[0];
        $this->assertSame(['png', '320', '200', (string) $d['matkulX']], [$g['jenis'], $g['lebar'], $g['tinggi'], $g['course_id']]);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{32}$/', $g['kunci']);
        $this->assertSame('Diagram alir sederhana', $this->db->query("SELECT image_alt FROM questions WHERE id = {$soal}")->fetch_assoc()['image_alt']);

        // Pengelola mata kuliahnya mendapat gambarnya, dari aplikasi.
        $r = $http->get('/gambar/' . $g['kunci']);
        $this->assertSame(200, $r->status);
        $this->assertSame('image/png', $r->header('Content-Type'));
        $this->assertStringContainsString('no-store', (string) $r->header('Cache-Control'));
        $this->assertSame('nosniff', $r->header('X-Content-Type-Options'));
        $this->assertSame($g['sha256'], hash('sha256', $r->body));
        $this->assertSame((int) $g['byte'], strlen($r->body));
        $this->assertStringNotContainsString('MUATAN-RAHASIA', $r->body, 'yang ditempelkan di belakang unggahan ikut tersimpan');
        $this->assertNotSame(hash('sha256', $kiriman), $g['sha256'], 'berkas kiriman disimpan apa adanya');
        $this->assertSame([320, 200], array_slice((array) getimagesizefromstring($r->body), 0, 2));
        $this->assertSame(200, $this->sebagai($d['superadmin'])->get('/gambar/' . $g['kunci'])->status);

        // Pratinjau menautkan /gambar/<kunci> dengan teks alternatifnya.
        $this->assertStringContainsString('src="/gambar/' . $g['kunci'] . '" alt="Diagram alir sederhana"', $http->get("/mata-kuliah/{$d['matkulX']}/bank/soal/{$soal}")->body);

        // Bukan berkas statis: di bawah /media/ nama berkasnya dijawab 403, dengan atau tanpa sesi.
        foreach (["/media/soal/{$g['kunci']}.png", "/media/soal/{$g['kunci']}.jpg", '/media/soal/', "/media/{$g['kunci']}.png"] as $jalur) {
            $this->assertContains($http->get($jalur)->status, [403, 404], $jalur);
            $this->assertContains($this->tamu()->get($jalur)->status, [403, 404], $jalur);
            $this->assertStringNotContainsString("\x89PNG", $http->body, $jalur . ' menyajikan gambar');
        }
        $this->assertSame(403, $this->tamu()->get("/media/soal/{$g['kunci']}.png")->status);

        // Tanpa sesi: ke halaman masuk. Di luar pengelola mata kuliahnya: 404, sama dengan kunci yang tidak ada.
        $r = $this->tamu()->get('/gambar/' . $g['kunci']);
        $this->assertContains($r->status, [302, 303, 307]);
        $this->assertStringStartsWith((string) getenv('AUTH_LOGIN_URL'), (string) $r->header('Location'));
        foreach (['pengampuY', 'pengajarA', 'mhsA'] as $p) {
            $klien = $this->sebagai($d[$p]);
            $r     = $klien->get('/gambar/' . $g['kunci']);
            $this->assertSame(404, $r->status, $p);
            $this->assertStringNotContainsString("\x89PNG", $r->body, $p);
            $terlarang = self::inti($r->body, '/gambar/' . $g['kunci']);
            $tidakAda  = self::inti($klien->get('/gambar/' . str_repeat('0', 32))->body, '/gambar/' . str_repeat('0', 32));
            $this->assertSame($tidakAda, $terlarang, $p . ': gambar terlarang dan kunci yang tidak ada dijawab berbeda');
        }
        $this->assertSame(404, $http->get('/gambar/' . strtoupper($g['kunci']))->status);
    }

    public function testUnggahanYangBukanGambarAtauTanpaTeksAlternatifDitolak(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengampuX']);
        $x    = "/mata-kuliah/{$d['matkulX']}/bank/soal";
        $soal = ['type' => 'isian', 'text' => 'Soal bergambar', 'points' => '1', 'alt' => 'Sebuah gambar'];
        $svg  = '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>';
        $tolak = [
            'PHP bernama .png'       => ['x.png', '<?php phpinfo();', 'bukan gambar JPEG, PNG atau WebP'],
            'SVG'                    => ['x.svg', $svg, 'bukan gambar JPEG, PNG atau WebP'],
            'GIF'                    => ['x.gif', "GIF89a\x01\x00\x01\x00\x80\x00\x00\xff\xff\xff\x00\x00\x00,\x00\x00\x00\x00\x01\x00\x01\x00\x00\x02\x02D\x01\x00;", 'bukan gambar JPEG, PNG atau WebP'],
            'PNG terpotong'          => ['x.png', substr(self::gambar('png', 800, 600), 0, 300), 'tidak dapat dibaca'],
            'lebih dari 5 MB'        => ['x.png', self::gambar('png') . str_repeat("\0", 5 * 1024 * 1024), 'paling besar 5 MB'],
        ];
        foreach ($tolak as $nama => [$berkas, $isi, $pesan]) {
            $r = $this->kirimBerkas($http, $x, $soal, ['gambar' => [$berkas, $isi]]);
            $this->assertSame(422, $r->status, $nama);
            $this->assertStringContainsString($pesan, $r->body, $nama);
        }
        // Teks alternatif wajib (soal dan opsi).
        $r = $this->kirimBerkas($http, $x, ['alt' => ''] + $soal, ['gambar' => ['x.png', self::gambar()]]);
        $this->assertSame(422, $r->status);
        $this->assertStringContainsString('teks alternatif', $r->body);
        $r = $this->kirimBerkas($http, $x, ['type' => 'pilihan_ganda', 'text' => 'Pilih', 'points' => '1', 'opsi_teks[0]' => 'a', 'opsi_teks[1]' => 'b', 'benar' => '0'], ['opsi_gambar_1' => ['x.png', self::gambar()]]);
        $this->assertSame(422, $r->status);
        $this->assertStringContainsString('Opsi 2: setiap gambar wajib punya teks alternatif', $r->body);

        $this->assertSame('0', $this->db->query('SELECT COUNT(*) AS n FROM questions')->fetch_assoc()['n']);
        $this->assertSame('0', $this->db->query('SELECT COUNT(*) AS n FROM images')->fetch_assoc()['n']);
    }

    public function testJenisUkuranDanPemakaianUlang(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengampuX']);
        $x    = "/mata-kuliah/{$d['matkulX']}/bank";

        // WebP disimpan sebagai JPEG; gambar besar dikecilkan sampai sisi terpanjang 2000 piksel.
        $this->buatSoal($http, $d['matkulX'], 'isian', ['alt' => 'Foto lebar'], ['gambar' => ['a.webp', self::gambar('webp', 3000, 1500)]]);
        $g = $this->db->query('SELECT jenis, lebar, tinggi, kunci FROM images ORDER BY id DESC LIMIT 1')->fetch_assoc();
        $this->assertSame(['jpeg', '2000', '1000'], [$g['jenis'], $g['lebar'], $g['tinggi']]);
        $r = $http->get('/gambar/' . $g['kunci']);
        $this->assertSame('image/jpeg', $r->header('Content-Type'));
        $this->assertSame(IMAGETYPE_JPEG, getimagesizefromstring($r->body)[2]);

        // Gambar yang sama dipakai dua soal dengan teks alternatif berbeda: satu berkas, dua teks.
        $png = self::gambar('png', 200, 100, 'sama');
        $a   = $this->buatSoal($http, $d['matkulX'], 'isian', ['alt' => 'Teks alternatif pertama'], ['gambar' => ['a.png', $png]]);
        $b   = $this->buatSoal($http, $d['matkulX'], 'isian', ['alt' => 'Teks alternatif kedua'], ['gambar' => ['b.png', $png]]);
        $this->assertSame('2', $this->db->query('SELECT COUNT(*) AS n FROM images')->fetch_assoc()['n']);
        $pakai = $this->db->query("SELECT image_id, image_alt FROM questions WHERE id IN ({$a}, {$b}) ORDER BY id")->fetch_all(MYSQLI_NUM);
        $this->assertSame($pakai[0][0], $pakai[1][0]);
        $this->assertSame(['Teks alternatif pertama', 'Teks alternatif kedua'], [$pakai[0][1], $pakai[1][1]]);
        // Mata kuliah lain mendapat barisnya sendiri untuk gambar yang sama.
        $this->buatSoal($this->sebagai($d['pengampuY']), $d['matkulY'], 'isian', ['alt' => 'Di mata kuliah Y'], ['gambar' => ['a.png', $png]]);
        $this->assertSame('3', $this->db->query('SELECT COUNT(*) AS n FROM images')->fetch_assoc()['n']);

        // Opsi boleh berupa gambar tanpa teks (keputusan pemilik 2026-11-25); pratinjau menampilkannya.
        $pg = $this->buatSoal($http, $d['matkulX'], 'pilihan_ganda', [
            'text' => 'Simbol keputusan pada diagram alir adalah …', 'opsi_teks[0]' => '', 'opsi_teks[1]' => '', 'opsi_teks[2]' => 'Tidak ada', 'benar' => '0',
            'opsi_alt[0]' => 'Belah ketupat', 'opsi_alt[1]' => 'Persegi panjang',
        ], ['opsi_gambar_0' => ['k.png', self::gambar('png', 120, 120, 'ketupat')], 'opsi_gambar_1' => ['p.png', self::gambar('png', 120, 80, 'persegi')]]);
        $opsi = $this->db->query("SELECT text, correct, image_alt FROM question_options WHERE question_id = {$pg} ORDER BY position")->fetch_all(MYSQLI_NUM);
        $this->assertSame([['', '1', 'Belah ketupat'], ['', '0', 'Persegi panjang'], ['Tidak ada', '0', null]], $opsi);
        $r = $http->get("{$x}/soal/{$pg}");
        $this->assertSame(2, preg_match_all('#<span class="opsi-isi" dir="auto">\s*<img src="/gambar/[0-9a-f]{32}" alt="(Belah ketupat|Persegi panjang)"#', $r->body));

        // Menyunting tanpa memilih berkas baru mempertahankan gambar; "lepas gambar" melepasnya.
        $form = ['type' => 'isian', 'text' => 'Teks baru', 'points' => '1', 'alt' => 'Teks alternatif diganti'];
        $this->kirimBerkas($http, "{$x}/soal/{$a}", $form, []);
        $s = $this->db->query("SELECT image_id, image_alt FROM questions WHERE id = {$a}")->fetch_assoc();
        $this->assertSame([$pakai[0][0], 'Teks alternatif diganti'], [$s['image_id'], $s['image_alt']]);
        $this->kirimBerkas($http, "{$x}/soal/{$a}", ['hapus_gambar' => '1'] + $form, []);
        $this->assertNull($this->db->query("SELECT image_id FROM questions WHERE id = {$a}")->fetch_assoc()['image_id']);
        // Berkasnya tidak dihapus: soal lain masih memakainya.
        $kunci = $this->db->query("SELECT kunci FROM images WHERE id = {$pakai[0][0]}")->fetch_assoc()['kunci'];
        $this->assertSame(200, $http->get('/gambar/' . $kunci)->status);
    }
}
