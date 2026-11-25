<?php

declare(strict_types=1);

namespace Tests\Bank;

use Tests\Support\UjianCase;

/**
 * Kelompok soal (SPEC-LOKAUJIAN.md §5.2): bagian bersama (teks dan/atau gambar), 1–20 soal
 * dalam urutan tetap, dan aturan hapus.
 */
final class KelompokTest extends UjianCase
{
    public function testKelompokDenganBagianBersamaSoalBerurutanDanPratinjau(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengampuX']);
        $x    = "/mata-kuliah/{$d['matkulX']}/bank";

        // Tanpa teks dan tanpa gambar: tidak ada bagian bersama.
        $r = $this->kirimBerkas($http, "{$x}/kelompok", ['text' => '  ', 'alt' => ''], []);
        $this->assertSame(422, $r->status);
        $this->assertStringContainsString('isi teks bersama, atau pasang gambar bersama', $r->body);

        $k  = $this->buatKelompok($http, $d['matkulX'], "Bacalah teks berikut.\n\nPak Budi menanam **padi**.", ['gambar' => ['denah.jpg', self::gambar('jpeg')]], 'Denah sawah Pak Budi');
        $s1 = $this->buatSoal($http, $d['matkulX'], 'pilihan_ganda', ['text' => 'Apa yang ditanam Pak Budi?'], [], $k);
        $s2 = $this->buatSoal($http, $d['matkulX'], 'isian', ['text' => 'Siapa nama petaninya?'], [], $k);
        $s3 = $this->buatSoal($http, $d['matkulX'], 'benar_salah', ['text' => 'Pak Budi menanam jagung.', 'answer' => 'salah'], [], $k);
        $urut = fn (): array => array_map('intval', array_column($this->db->query("SELECT id FROM questions WHERE group_id = {$k} ORDER BY position")->fetch_all(MYSQLI_ASSOC), 'id'));
        $this->assertSame([$s1, $s2, $s3], $urut());

        // Pratinjau: bagian bersama di atas, soal bernomor urut, kunci di luar partial soal.
        $r = $http->get("{$x}/kelompok/{$k}");
        $this->assertSame(200, $r->status);
        $this->assertSame(1, preg_match('#<section class="kelompok".*?</section>#s', $r->body, $m));
        $this->assertStringContainsString('<strong>padi</strong>', $m[0]);
        $this->assertStringContainsString('alt="Denah sawah Pak Budi"', $m[0]);
        $posisi = array_map(static fn (string $t): int => (int) strpos($m[0], $t), ['Apa yang ditanam', 'Siapa nama petaninya', 'Pak Budi menanam jagung']);
        $this->assertSame($posisi, array_values(array_filter($posisi)), 'ketiga soal tampil');
        $terurut = $posisi;
        sort($terurut);
        $this->assertSame($terurut, $posisi, 'urutan soal di pratinjau');
        $this->assertStringNotContainsString('Kunci', $m[0]);
        // Soal anggota kelompok dipratinjau bersama kelompoknya.
        $this->assertStringEndsWith("/bank/kelompok/{$k}", (string) $http->get("{$x}/soal/{$s2}")->header('Location'));

        // Di daftar bank kelompok adalah satu butir; soal anggotanya tidak tampil sendiri.
        $daftar = $http->get($x)->body;
        $this->assertStringContainsString('data-butir="k' . $k . '"', $daftar);
        $this->assertStringNotContainsString('data-butir="s' . $s1 . '"', $daftar);
        $this->assertStringContainsString('data-butir="k' . $k . '"', $http->get("{$x}?q=petaninya")->body, 'pencarian menemukan kelompok lewat teks soalnya');
        $this->assertStringNotContainsString('data-butir="k' . $k . '"', $http->get("{$x}?tipe=isian")->body);

        // Urutan diubah satu langkah; di ujung tidak berubah.
        $this->kirim($http, "{$x}/soal/{$s3}/geser", ['arah' => 'naik']);
        $this->assertSame([$s1, $s3, $s2], $urut());
        $this->kirim($http, "{$x}/soal/{$s1}/geser", ['arah' => 'naik']);
        $this->assertSame([$s1, $s3, $s2], $urut());
        $this->kirim($http, "{$x}/soal/{$s1}/geser", ['arah' => 'turun']);
        $this->assertSame([$s3, $s1, $s2], $urut());

        // Soal anggota tidak diarsipkan sendiri, dan tidak bisa masuk ujian sendirian.
        $this->assertSame(404, $this->kirim($http, "{$x}/soal/{$s1}/arsip", ['arsip' => '1'])->status);
        $r = $this->kirim($http, "/mata-kuliah/{$d['matkulX']}/ujian", ['judul' => 'Salah', 'jenis' => 'ujian', 'butir' => ['s' . $s1]]);
        $this->assertStringEndsWith('?pesan=butir_asing', (string) $r->header('Location'));

        // Menghapus satu soal anggota merapatkan urutan.
        $this->kirim($http, "{$x}/soal/{$s1}/hapus");
        $this->assertSame([['1', (string) $s3], ['2', (string) $s2]], $this->db->query("SELECT position, id FROM questions WHERE group_id = {$k} ORDER BY position")->fetch_all(MYSQLI_NUM));

        // Dipakai sebuah ujian: kelompok dan soalnya tidak dapat dihapus.
        $this->buatDraf($http, $d['matkulX'], 'Ujian bacaan', ['k' . $k]);
        $this->assertStringEndsWith('?pesan=dipakai', (string) $this->kirim($http, "{$x}/kelompok/{$k}/hapus")->header('Location'));
        $this->assertStringEndsWith('?pesan=dipakai', (string) $this->kirim($http, "{$x}/soal/{$s2}/hapus")->header('Location'));
        $this->assertSame('2', $this->db->query("SELECT COUNT(*) AS n FROM questions WHERE group_id = {$k}")->fetch_assoc()['n']);
    }

    public function testBatasDuaPuluhSoalDanKelompokKosong(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengampuX']);
        $x    = "/mata-kuliah/{$d['matkulX']}/bank";
        $k    = $this->buatKelompok($http, $d['matkulX'], 'Bacaan panjang');

        // Kelompok tanpa soal tidak dapat masuk ujian.
        $r = $this->kirim($http, "/mata-kuliah/{$d['matkulX']}/ujian", ['judul' => 'Kosong', 'jenis' => 'ujian', 'butir' => ['k' . $k]]);
        $this->assertStringEndsWith('?pesan=kelompok_kosong', (string) $r->header('Location'));

        for ($i = 1; $i <= 20; $i++) {
            $this->buatSoal($http, $d['matkulX'], 'isian', ['text' => "Soal ke-{$i}"], [], $k);
        }
        $r = $this->kirimBerkas($http, "{$x}/soal", ['type' => 'isian', 'text' => 'Soal ke-21', 'points' => '1', 'kelompok' => (string) $k], []);
        $this->assertSame(422, $r->status);
        $this->assertStringContainsString('paling banyak 20 soal', $r->body);
        $this->assertSame('20', $this->db->query("SELECT COUNT(*) AS n FROM questions WHERE group_id = {$k}")->fetch_assoc()['n']);

        // Kelompok yang tidak dipakai dihapus beserta soalnya, dengan baris audit.
        $this->assertStringEndsWith('/bank?pesan=dihapus', (string) $this->kirim($http, "{$x}/kelompok/{$k}/hapus")->header('Location'));
        $this->assertSame('0', $this->db->query('SELECT COUNT(*) AS n FROM questions')->fetch_assoc()['n']);
        $this->assertSame('1', $this->db->query("SELECT COUNT(*) AS n FROM audit_log WHERE action = 'kelompok.dihapus' AND target_id = '{$k}'")->fetch_assoc()['n']);

        // Soal tidak bisa ditambahkan ke kelompok mata kuliah lain.
        $kY = $this->buatKelompok($this->sebagai($d['pengampuY']), $d['matkulY'], 'Bacaan Y');
        $r  = $this->kirimBerkas($http, "{$x}/soal", ['type' => 'isian', 'text' => 'Menyusup', 'points' => '1', 'kelompok' => (string) $kY], []);
        $this->assertSame(404, $r->status);
        $this->assertSame('0', $this->db->query('SELECT COUNT(*) AS n FROM questions')->fetch_assoc()['n']);
    }
}
