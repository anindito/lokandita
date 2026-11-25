<?php

declare(strict_types=1);

namespace Tests\Ujian;

use Tests\Support\UjianCase;

/**
 * Menyusun, mengatur dan menerbitkan ujian (SPEC-LOKAUJIAN.md §6.1, §6.2; KONTRAK.md §22), lewat
 * HTTP sungguhan: setelan, susunan butir, penerbitan yang MEMBEKUKAN isi, penolakan dengan daftar
 * kekurangan, menarik, mengarsipkan, menghapus.
 */
final class TerbitTest extends UjianCase
{
    public function testSetelanDanSusunanDraf(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengampuX']);
        $x    = '/mata-kuliah/' . $d['matkulX'];
        $a    = $this->buatSoal($http, $d['matkulX'], 'isian', ['text' => 'Soal A']);
        $b    = $this->buatSoal($http, $d['matkulX'], 'isian', ['text' => 'Soal B']);
        $c    = $this->buatSoal($http, $d['matkulX'], 'uraian', ['text' => 'Soal C']);
        $u    = $this->buatDraf($http, $d['matkulX'], 'Kuis susun', ["s{$a}", "s{$b}"]);
        $ini  = "{$x}/ujian/{$u}";
        $urut = fn (): array => array_map('intval', array_column($this->db->query("SELECT question_id FROM exam_items WHERE exam_id = {$u} ORDER BY position")->fetch_all(MYSQLI_ASSOC), 'question_id'));

        $this->assertSame(200, $http->get($ini . '/sunting')->status);

        // Mode per mahasiswa tanpa durasi ditolak dengan kalimatnya; tidak ada yang tersimpan.
        $r = $this->kirim($http, $ini, ['judul' => 'Kuis susun', 'jenis' => 'kuis', 'petunjuk' => '', 'mode' => 'per_mahasiswa', 'durasi' => '']);
        $this->assertSame(422, $r->status);
        $this->assertStringContainsString('wajib punya durasi', $r->body);
        $this->assertSame('dikendalikan_dosen', $this->db->query("SELECT mode FROM exams WHERE id = {$u}")->fetch_assoc()['mode']);
        // Durasi di luar 5–600 ditolak.
        $this->assertSame(422, $this->kirim($http, $ini, ['judul' => 'Kuis susun', 'jenis' => 'kuis', 'petunjuk' => '', 'mode' => 'per_mahasiswa', 'durasi' => '4'])->status);

        $r = $this->kirim($http, $ini, ['judul' => 'Kuis tersusun', 'jenis' => 'kuis', 'petunjuk' => 'Baca **dulu**.', 'mode' => 'per_mahasiswa', 'durasi' => '45', 'acak_soal' => '1', 'acak_opsi' => '1']);
        $this->assertStringEndsWith('/sunting?pesan=disimpan', (string) $r->header('Location'));
        $baris = $this->db->query("SELECT title, kind, instructions, mode, duration_minutes, shuffle_questions, shuffle_options FROM exams WHERE id = {$u}")->fetch_assoc();
        $this->assertSame(['Kuis tersusun', 'kuis', 'Baca **dulu**.', 'per_mahasiswa', '45', '1', '1'], array_map('strval', array_values($baris)));

        // Susunan: tambah C, naikkan C dua kali, keluarkan A.
        $this->assertStringEndsWith('?pesan=butir', (string) $this->kirim($http, $ini . '/butir', ['butir' => ["s{$c}"]])->header('Location'));
        $this->assertSame([$a, $b, $c], $urut());
        // Butir yang sudah ada tidak bisa ditambahkan dua kali.
        $this->assertStringEndsWith('?pesan=butir_asing', (string) $this->kirim($http, $ini . '/butir', ['butir' => ["s{$c}"]])->header('Location'));
        $this->kirim($http, $ini . '/butir/3/geser', ['arah' => 'naik']);
        $this->kirim($http, $ini . '/butir/2/geser', ['arah' => 'naik']);
        $this->assertSame([$c, $a, $b], $urut());
        $this->kirim($http, $ini . '/butir/1/geser', ['arah' => 'turun']);
        $this->assertSame([$a, $c, $b], $urut());
        $this->kirim($http, $ini . '/butir/1/hapus');
        $this->assertSame([$c, $b], $urut());
        $this->assertSame(404, $this->kirim($http, $ini . '/butir/9/hapus')->status);
        // Butir terakhir tidak dibuang; soalnya tetap di bank.
        $this->kirim($http, $ini . '/butir/1/hapus');
        $this->assertStringEndsWith('?pesan=terakhir', (string) $this->kirim($http, $ini . '/butir/1/hapus')->header('Location'));
        $this->assertSame([$b], $urut());
        $this->assertSame('3', (string) $this->db->query('SELECT COUNT(*) AS n FROM questions')->fetch_assoc()['n']);
    }

    public function testMenerbitkanMembekukanIsi(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengampuX']);
        $x    = '/mata-kuliah/' . $d['matkulX'];
        $u    = $this->ujianTerbit($http, $d['matkulX'], 'Ujian beku');
        $ini  = "{$x}/ujian/{$u}";

        $this->assertSame('6', (string) $this->db->query("SELECT COUNT(*) AS n FROM exam_questions WHERE exam_id = {$u}")->fetch_assoc()['n']);
        $this->assertSame('1', (string) $this->db->query("SELECT COUNT(*) AS n FROM exam_groups WHERE exam_id = {$u}")->fetch_assoc()['n']);
        $this->assertSame('3', (string) $this->db->query("SELECT COUNT(*) AS n FROM exam_options o JOIN exam_questions q ON q.id = o.exam_question_id WHERE q.exam_id = {$u}")->fetch_assoc()['n']);
        $this->assertSame('1', (string) $this->db->query("SELECT COUNT(*) AS n FROM audit_log WHERE action = 'ujian.diterbitkan' AND target_id = '{$u}'")->fetch_assoc()['n']);
        $eksporAwal = $this->kirim($http, $ini . '/ekspor')->body;
        $this->assertStringContainsString('ISIAN: ibu kota Indonesia', $eksporAwal);

        // Suntingan di bank SESUDAH terbit tidak sampai ke ujian: pratinjau, salinan beku dan ekspor tetap.
        $soalBank = (int) $this->db->query("SELECT id FROM questions WHERE text LIKE 'ISIAN:%'")->fetch_assoc()['id'];
        $r        = $this->kirimBerkas($http, "{$x}/bank/soal/{$soalBank}", ['type' => 'isian', 'text' => 'DIUBAH di bank sesudah terbit', 'points' => '7'], []);
        $this->assertContains($r->status, [302, 303]);
        $halaman = $http->get($ini)->body;
        $this->assertStringContainsString('ISIAN: ibu kota Indonesia', $halaman);
        $this->assertStringNotContainsString('DIUBAH di bank', $halaman);
        $this->assertSame($eksporAwal, $this->kirim($http, $ini . '/ekspor')->body, 'ekspor ujian terbit berubah sesudah bank disunting');

        // Ujian terbit tidak disunting: rute draf menjawab 404 dan tidak mengubah apa pun.
        $sebelum = $this->sidikBank(false);
        foreach ([[$ini, ['judul' => 'Ganti', 'jenis' => 'ujian', 'mode' => 'dikendalikan_dosen']], [$ini . '/butir/1/hapus', []], [$ini . '/butir', ['butir' => ["s{$soalBank}"]]], [$ini . '/terbit', []]] as [$jalur, $isian]) {
            $this->assertSame(404, $this->kirim($http, $jalur, $isian)->status, $jalur);
        }
        $this->assertSame(404, $http->get($ini . '/sunting')->status);
        $this->assertSame($sebelum, $this->sidikBank(false));

        // Ditarik ke draf (belum ada pengerjaan): salinan beku hilang, draf mengikuti bank lagi.
        $this->assertStringEndsWith('?pesan=ditarik', (string) $this->kirim($http, $ini . '/tarik')->header('Location'));
        $this->assertSame('0', (string) $this->db->query("SELECT COUNT(*) AS n FROM exam_questions WHERE exam_id = {$u}")->fetch_assoc()['n']);
        $this->assertStringContainsString('DIUBAH di bank', $http->get($ini)->body);
        // Diterbitkan lagi: yang dibekukan sekarang isi yang baru.
        $this->assertStringEndsWith('?pesan=diterbitkan', (string) $this->kirim($http, $ini . '/terbit')->header('Location'));
        $this->assertSame('7.00', $this->db->query("SELECT points FROM exam_questions WHERE exam_id = {$u} AND text LIKE 'DIUBAH%'")->fetch_assoc()['points']);
    }

    public function testPenerbitanDitolakDenganDaftarKekurangan(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengampuX']);
        $x    = '/mata-kuliah/' . $d['matkulX'];
        $is   = $this->buatSoal($http, $d['matkulX'], 'isian');
        $pg   = $this->buatSoal($http, $d['matkulX'], 'pilihan_ganda');
        $u    = $this->buatDraf($http, $d['matkulX'], 'Ujian kurang', ["s{$is}", "s{$pg}"]);
        // Keadaan yang tidak bisa dicapai lewat form, ditulis langsung ke skema tes: pilihan ganda
        // tanpa opsi benar, dan mode per mahasiswa tanpa durasi.
        $this->db->query("UPDATE question_options SET correct = 0 WHERE question_id = {$pg}");
        $this->db->query("UPDATE exams SET mode = 'per_mahasiswa', duration_minutes = NULL WHERE id = {$u}");

        $sebelum = $this->sidikBank();
        $r       = $this->kirim($http, "{$x}/ujian/{$u}/terbit");
        $this->assertSame(422, $r->status);
        $this->assertSame(1, preg_match('#data-kurang>(.*?)</div>#s', $r->body, $m));
        $this->assertStringContainsString('Soal 2: pilihan ganda harus punya', $m[1]);
        $this->assertStringContainsString('memerlukan durasi', $m[1]);
        $this->assertSame(2, substr_count($m[1], '<li>'), 'daftar kekurangan harus persis dua butir');
        $this->assertSame($sebelum, $this->sidikBank(), 'penerbitan yang ditolak menyimpan sesuatu');
    }

    public function testTarikArsipDanHapusMengikutiPengerjaan(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengampuX']);
        $x    = '/mata-kuliah/' . $d['matkulX'];
        $u    = $this->ujianTerbit($http, $d['matkulX'], 'Ujian dipakai');
        $ini  = "{$x}/ujian/{$u}";
        $sesi = $this->beri($http, $d['matkulX'], $u, $d['kelasXA']);
        $this->kirim($http, "/sesi/{$sesi}/mulai", ['menit' => '30']);
        $mhs = $this->sebagai($d['mhsA']);
        $this->mulaiKerja($mhs, $sesi, (string) $d['mhsA']['id']);

        // Ada pengerjaan: tidak ditarik, tidak dihapus; selama masih berjalan tidak diarsipkan.
        $this->assertStringEndsWith('?pesan=ada_pengerjaan', (string) $this->kirim($http, $ini . '/tarik')->header('Location'));
        $this->assertStringEndsWith('?pesan=ada_pengerjaan', (string) $this->kirim($http, $ini . '/hapus')->header('Location'));
        $this->assertStringEndsWith('?pesan=sedang_berjalan', (string) $this->kirim($http, $ini . '/arsip', ['arsip' => '1'])->header('Location'));
        $this->assertSame('terbit', $this->db->query("SELECT status FROM exams WHERE id = {$u}")->fetch_assoc()['status']);

        $this->kirim($mhs, "/ujian-saya/{$sesi}/kumpulkan");
        $this->assertStringEndsWith('?pesan=diarsipkan', (string) $this->kirim($http, $ini . '/arsip', ['arsip' => '1'])->header('Location'));
        // Arsip: hilang dari daftar biasa dan dari beranda mahasiswa; ada di daftar arsip; datanya utuh.
        $this->assertStringNotContainsString('Ujian dipakai', $http->get("{$x}/ujian")->body);
        $this->assertStringContainsString('Ujian dipakai', $http->get("{$x}/ujian?arsip=1")->body);
        $this->assertStringNotContainsString('Ujian dipakai', $mhs->get('/beranda')->body);
        $this->assertSame('1', (string) $this->db->query("SELECT COUNT(*) AS n FROM attempts WHERE exam_id = {$u}")->fetch_assoc()['n']);
        $this->assertStringEndsWith('?pesan=dipulihkan', (string) $this->kirim($http, $ini . '/arsip', ['arsip' => '0'])->header('Location'));
        $this->assertStringContainsString('Ujian dipakai', $mhs->get('/beranda')->body);

        // Ujian terbit TANPA pengerjaan boleh dihapus; sesinya ikut, soalnya tetap di bank.
        $u2    = $this->buatDraf($http, $d['matkulX'], 'Ujian kosong', ['s' . (int) $this->db->query("SELECT id FROM questions WHERE text LIKE 'URAIAN:%'")->fetch_assoc()['id']]);
        $this->kirim($http, "{$x}/ujian/{$u2}/terbit");
        $sesi2 = $this->beri($http, $d['matkulX'], $u2, $d['kelasXB']);
        $soal  = (string) $this->db->query('SELECT COUNT(*) AS n FROM questions')->fetch_assoc()['n'];
        $this->assertStringEndsWith('/ujian?pesan=dihapus', (string) $this->kirim($http, "{$x}/ujian/{$u2}/hapus")->header('Location'));
        $this->assertNull($this->db->query("SELECT id FROM sessions WHERE id = {$sesi2}")->fetch_assoc());
        $this->assertNull($this->db->query("SELECT id FROM exams WHERE id = {$u2}")->fetch_assoc());
        $this->assertSame($soal, (string) $this->db->query('SELECT COUNT(*) AS n FROM questions')->fetch_assoc()['n']);
    }
}
