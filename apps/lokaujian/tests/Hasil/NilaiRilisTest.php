<?php

declare(strict_types=1);

namespace Tests\Hasil;

use Tests\Support\UjianCase;

/**
 * Skor objektif, koreksi sesudah ujian, rilis hasil per kelas dan tinjauan (SPEC-LOKAUJIAN.md §11;
 * KONTRAK.md §24), lewat HTTP sungguhan.
 */
final class NilaiRilisTest extends UjianCase
{
    private function opsi(int $soal, bool $benar): int
    {
        return (int) $this->db->query("SELECT id FROM exam_options WHERE exam_question_id = {$soal} AND correct = " . (int) $benar . ' ORDER BY position LIMIT 1')->fetch_assoc()['id'];
    }

    private function skor(int $sesi, array $mhs): ?string
    {
        return $this->pengerjaan($sesi, (string) $mhs['id'])['score'];
    }

    /**
     * Ani: BS benar (1 poin), PG salah. Citra: BS salah, PG benar (1 poin). Ani mengumpulkan
     * sendiri; Citra berakhir oleh waktu.
     *
     * @return array<string, mixed>
     */
    private function duniaDinilai(): array
    {
        $d   = $this->duniaSesi();
        $ani = $this->sebagai($d['mhsA']);
        $cit = $this->sebagai($d['mhsC']);
        $this->mulaiKerja($ani, $d['sesi'], (string) $d['mhsA']['id']);
        $this->mulaiKerja($cit, $d['sesi'], (string) $d['mhsC']['id']);
        $ta = $this->bukaHalamanUjian($ani, $d['sesi']);
        $tc = $this->bukaHalamanUjian($cit, $d['sesi']);
        $d['pgSalah'] = $this->opsi($d['q']['PG'], false);
        $this->simpan($ani, $d['sesi'], $ta, $d['q']['BS'], 'benar', 1);
        $this->simpan($ani, $d['sesi'], $ta, $d['q']['PG'], $d['pgSalah'], 1);
        $this->simpan($ani, $d['sesi'], $ta, $d['q']['ISIAN'], 'Jakarta menurut Ani', 1);
        $this->simpan($cit, $d['sesi'], $tc, $d['q']['BS'], 'salah', 1);
        $this->simpan($cit, $d['sesi'], $tc, $d['q']['PG'], $this->opsi($d['q']['PG'], true), 1);
        $this->simpan($cit, $d['sesi'], $tc, $d['q']['ISIAN'], 'RAHASIA-JAWABAN-CITRA', 1);
        // Skor dihitung saat pengerjaan berakhir: Ani pada Kumpulkan, dalam permintaan itu juga.
        $this->kirim($ani, "/ujian-saya/{$d['sesi']}/kumpulkan");
        $this->assertSame('1.00', $this->skor($d['sesi'], $d['mhsA']));
        $this->assertNull($this->skor($d['sesi'], $d['mhsC']), 'pengerjaan yang masih berjalan belum bernilai');
        $this->db->query("UPDATE sessions SET end_at = UTC_TIMESTAMP() - INTERVAL 5 MINUTE WHERE id = {$d['sesi']}");
        $this->spark(['ujian:jadwal', '--sekali']);
        $this->assertSame('1.00', $this->skor($d['sesi'], $d['mhsC']));

        return $d;
    }

    public function testSkorSaatBerakhirDanKoreksiMenilaiUlangPenuh(): void
    {
        $d    = $this->duniaDinilai();
        $http = $d['dosen'];
        $ini  = "/mata-kuliah/{$d['matkulX']}/ujian/{$d['ujian']}/koreksi";

        // Halaman koreksi menampilkan kunci; hanya bagi yang mengelola mata kuliahnya.
        $r = $http->get($ini);
        $this->assertSame(200, $r->status);
        $this->assertMatchesRegularExpression('/name="kunci" value="benar" checked/', $r->body);
        $this->assertStringContainsString('data-koreksi="' . $d['q']['PG'] . '"', $r->body);
        $this->assertSame(403, $this->sebagai($d['pengajarA'])->get($ini)->status);
        $this->assertSame(404, $this->sebagai($d['pengampuY'])->get($ini)->status);

        // Kunci BS dikoreksi menjadi Salah: Ani 1 → 0, Citra 1 → 2. Dua pengerjaan dinilai ulang, dua berubah.
        $r = $this->kirim($http, "{$ini}/{$d['q']['BS']}", ['kunci' => 'salah', 'poin' => '1']);
        $this->assertStringEndsWith('?pesan=dikoreksi&p=2&b=2', (string) $r->header('Location'));
        $this->assertSame(['0.00', '2.00'], [$this->skor($d['sesi'], $d['mhsA']), $this->skor($d['sesi'], $d['mhsC'])]);
        $audit = $this->db->query("SELECT detail, course_id, actor_id FROM audit_log WHERE action = 'soal.dikoreksi' ORDER BY id DESC LIMIT 1")->fetch_assoc();
        $det   = json_decode((string) $audit['detail'], true);
        $this->assertSame(['benar', 'salah', 2, 2, 1], [$det['kunci_lama'], $det['kunci_baru'], $det['pengerjaan'], $det['nilai_berubah'], $det['soal']]);
        $this->assertArrayNotHasKey('poin_baru', $det);
        $this->assertSame((string) $d['matkulX'], (string) $audit['course_id']);

        // Poin PG menjadi 2,5 (koma diterima): hanya skor Citra yang berubah.
        $r = $this->kirim($http, "{$ini}/{$d['q']['PG']}", ['kunci' => '', 'poin' => '2,5']);
        $this->assertStringEndsWith('?pesan=dikoreksi&p=2&b=1', (string) $r->header('Location'));
        $this->assertSame(['0.00', '3.50'], [$this->skor($d['sesi'], $d['mhsA']), $this->skor($d['sesi'], $d['mhsC'])]);
        // Kunci PG dipindah ke opsi yang dipilih Ani: dicatat sebagai huruf opsi.
        $this->kirim($http, "{$ini}/{$d['q']['PG']}", ['kunci' => (string) $d['pgSalah'], 'poin' => '2.5']);
        $this->assertSame(['2.50', '1.00'], [$this->skor($d['sesi'], $d['mhsA']), $this->skor($d['sesi'], $d['mhsC'])]);
        $det = json_decode((string) $this->db->query("SELECT detail FROM audit_log WHERE action = 'soal.dikoreksi' ORDER BY id DESC LIMIT 1")->fetch_assoc()['detail'], true);
        $this->assertSame(['B', 'A'], [$det['kunci_lama'], $det['kunci_baru']]);
        // Poin isian (dinilai dosen) boleh diubah; skor objektif tidak ikut berubah.
        $this->assertStringEndsWith('&p=2&b=0', (string) $this->kirim($http, "{$ini}/{$d['q']['ISIAN']}", ['poin' => '4'])->header('Location'));

        // Yang ditolak tidak mengubah apa pun.
        $sebelum = $this->sidikBank();
        foreach ([
            'g_poin'  => [$d['q']['BS'], ['poin' => '0.3']],
            'g_poin ' => [$d['q']['BS'], ['poin' => '101']],
            'g_kunci' => [$d['q']['PG'], ['poin' => '2.5', 'kunci' => 'benar']],
            'g_kunci ' => [$d['q']['ISIAN'], ['poin' => '4', 'kunci' => 'benar']],
            'g_kunci  ' => [$d['q']['PG'], ['poin' => '2.5', 'kunci' => '999999999']], // opsi yang bukan milik soal ini
            'g_sama'  => [$d['q']['BS'], ['poin' => '1', 'kunci' => 'salah']],
        ] as $pesan => [$soal, $isian]) {
            $this->assertStringEndsWith('?pesan=' . trim($pesan), (string) $this->kirim($http, "{$ini}/{$soal}", $isian)->header('Location'), $pesan);
        }
        $this->assertSame(404, $this->kirim($http, "{$ini}/999999", ['poin' => '1'])->status);
        $this->assertSame(403, $this->kirim($this->sebagai($d['pengajarA']), "{$ini}/{$d['q']['BS']}", ['poin' => '5', 'kunci' => 'benar'])->status);
        $this->assertSame($sebelum, $this->sidikBank());

        // Soal di bank dan teks soal beku tidak berubah; draf tidak punya halaman koreksi.
        $this->assertSame('1', (string) $this->db->query("SELECT answer FROM questions WHERE text LIKE 'BS:%'")->fetch_assoc()['answer']);
        $this->assertSame('BS: bumi itu bulat', $this->db->query("SELECT text FROM exam_questions WHERE id = {$d['q']['BS']}")->fetch_assoc()['text']);
        $draf = $this->buatDraf($http, $d['matkulX'], 'Masih draf', ['s' . (int) $this->db->query("SELECT id FROM questions WHERE text LIKE 'URAIAN:%'")->fetch_assoc()['id']]);
        $this->assertSame(404, $http->get("/mata-kuliah/{$d['matkulX']}/ujian/{$draf}/koreksi")->status);
    }

    public function testRilisPerKelasTingkatPenarikanDanTinjauan(): void
    {
        $d    = $this->duniaDinilai();
        $http = $d['dosen'];
        $ani  = $this->sebagai($d['mhsA']);
        $s    = $d['sesi'];
        // Ujian yang sama diberikan ke kelas XB, yang belum dimulai.
        $sesiB = $this->beri($http, $d['matkulX'], $d['ujian'], $d['kelasXB']);

        // Bawaan: mahasiswa tidak melihat apa pun selain tanda terima.
        foreach (['/beranda', "/ujian-saya/{$s}"] as $jalur) {
            $this->assertStringNotContainsString('data-nilai', $ani->get($jalur)->body, $jalur);
        }
        $r = $ani->get("/ujian-saya/{$s}/tinjau");
        $this->assertSame([302, true], [$r->status, str_ends_with((string) $r->header('Location'), "/ujian-saya/{$s}")]);

        // Hanya yang mengelola mata kuliahnya: bagi pengajar A rilis tidak ada (404).
        $sebelum = $this->sidikBank();
        $this->assertSame(404, $this->kirim($this->sebagai($d['pengajarA']), "/sesi/{$s}/rilis", ['tingkat' => 'nilai'])->status);
        $this->assertStringEndsWith('?pesan=g_tingkat', (string) $this->kirim($http, "/sesi/{$s}/rilis", ['tingkat' => 'semua'])->header('Location'));
        // Sesi kelas XB belum usai: hasilnya belum bisa dirilis.
        $this->assertStringEndsWith('?pesan=g_belum_usai', (string) $this->kirim($http, "/sesi/{$sesiB}/rilis", ['tingkat' => 'nilai'])->header('Location'));
        $this->assertSame($sebelum, $this->sidikBank());

        // Tingkat `nilai`: skor objektif dari poin objektif, dengan kalimat tentang isian dan uraian.
        $this->assertStringEndsWith('?pesan=dirilis', (string) $this->kirim($http, "/sesi/{$s}/rilis", ['tingkat' => 'nilai'])->header('Location'));
        foreach (['/beranda', "/ujian-saya/{$s}"] as $jalur) {
            $isi = $ani->get($jalur)->body;
            $this->assertStringContainsString('Skor objektif: 1 dari 2', $isi, $jalur);
            $this->assertStringContainsString('dinilai dosen di luar aplikasi', $isi, $jalur);
        }
        $this->assertSame(302, $ani->get("/ujian-saya/{$s}/tinjau")->status, 'tingkat nilai tidak membuka tinjauan');
        $this->assertStringEndsWith('?pesan=g_sudah', (string) $this->kirim($http, "/sesi/{$s}/rilis", ['tingkat' => 'nilai'])->header('Location'));

        // `nilai_dan_kunci` selagi kelas XB belum selesai: peringatan yang menyebut kelasnya, dan
        // tidak ada yang berubah sampai konfirmasi kedua.
        $sebelum = $this->sidikBank();
        $r       = $this->kirim($http, "/sesi/{$s}/rilis", ['tingkat' => 'nilai_dan_kunci']);
        $this->assertSame(200, $r->status);
        $this->assertMatchesRegularExpression('/<li[^>]*>XB<\/li>/', $r->body);
        $this->assertStringContainsString('name="yakin" value="1"', $r->body);
        $this->assertSame($sebelum, $this->sidikBank());
        $this->assertStringEndsWith('?pesan=dirilis', (string) $this->kirim($http, "/sesi/{$s}/rilis", ['tingkat' => 'nilai_dan_kunci', 'yakin' => '1'])->header('Location'));
        $det = json_decode((string) $this->db->query("SELECT detail FROM audit_log WHERE action = 'hasil.dirilis' ORDER BY id DESC LIMIT 1")->fetch_assoc()['detail'], true);
        $this->assertSame(['nilai_dan_kunci', 'nilai', [$d['kelasXB']]], [$det['tingkat'], $det['sebelumnya'], $det['kelas_belum_usai']]);

        // Tinjauan: soal, jawaban sendiri, kunci soal otomatis; tidak ada jawaban orang lain.
        $r = $ani->get("/ujian-saya/{$s}/tinjau");
        $this->assertSame(200, $r->status);
        $this->assertStringContainsString('no-store', (string) $r->header('Cache-Control'));
        foreach (['BS: bumi itu bulat', 'Jakarta menurut Ani', 'Skor objektif: 1 dari 2', 'data-hasil="benar"', 'data-hasil="salah"', 'data-hasil="manual"', 'data-kunci'] as $teks) {
            $this->assertStringContainsString($teks, $r->body, $teks);
        }
        $this->assertStringNotContainsString('RAHASIA-JAWABAN-CITRA', $r->body);
        $this->assertSame(404, $this->sebagai($d['mhsB'])->get("/ujian-saya/{$s}/tinjau")->status);
        $this->assertSame(403, $http->get("/ujian-saya/{$s}/tinjau")->status);

        // Selama dirilis beserta kunci, buka ulang ditolak.
        $this->assertStringEndsWith('?pesan=g_dirilis', (string) $this->kirim($http, "/sesi/{$s}/buka-ulang", ['mahasiswa' => (string) $d['mhsA']['id'], 'menit' => '10', 'alasan' => 'uji', 'yakin' => '1'])->header('Location'));

        // Koreksi sesudah rilis sampai ke mahasiswa: skor yang dilihatnya ikut berubah.
        $this->kirim($http, "/mata-kuliah/{$d['matkulX']}/ujian/{$d['ujian']}/koreksi/{$d['q']['BS']}", ['kunci' => 'salah', 'poin' => '1']);
        $this->assertStringContainsString('Skor objektif: 0 dari 2', $ani->get('/beranda')->body);

        // Ditarik: kembali tidak melihat apa pun.
        $this->assertStringEndsWith('?pesan=ditarik', (string) $this->kirim($http, "/sesi/{$s}/rilis/tarik")->header('Location'));
        $this->assertStringNotContainsString('data-nilai', $ani->get('/beranda')->body);
        $this->assertSame(302, $ani->get("/ujian-saya/{$s}/tinjau")->status);
        $this->assertSame(1, (int) $this->db->query("SELECT COUNT(*) AS n FROM audit_log WHERE action = 'hasil.ditarik'")->fetch_assoc()['n']);
    }

    public function testTinjauanDanGambarnyaHanyaDiDalamSebUjianItu(): void
    {
        $d    = $this->dunia();
        // Pengampu bekerja lewat kontainer ber-SEB: kontainer tes tidak berbagi volume gambar, jadi
        // berkas gambar harus diunggah ke kontainer yang nanti menyajikannya.
        $http = $this->sebagaiSeb($d['pengampuX']);
        $soal = $this->buatSoal($http, $d['matkulX'], 'benar_salah', ['text' => 'BS: bergambar', 'alt' => 'Diagram tinjauan'], ['gambar' => ['g.png', self::gambar()]]);
        $u    = $this->buatDraf($http, $d['matkulX'], 'Ujian tinjauan SEB', ["s{$soal}"]);
        $this->kirim($http, "/mata-kuliah/{$d['matkulX']}/ujian/{$u}/terbit");
        $s     = $this->beri($http, $d['matkulX'], $u, $d['kelasXA']);
        $kunci = $this->sebSiap($http, $d['matkulX'], $u);
        $this->kirim($http, "/sesi/{$s}/mulai", ['menit' => '30']);
        // Mahasiswa mengerjakan di kontainer tanpa penegakan, lalu sesi berakhir dan hasil dirilis beserta kunci.
        $ani = $this->sebagai($d['mhsA']);
        $this->mulaiKerja($ani, $s, (string) $d['mhsA']['id']);
        $this->kirim($ani, "/ujian-saya/{$s}/kumpulkan");
        $this->db->query("UPDATE sessions SET end_at = UTC_TIMESTAMP() - INTERVAL 1 MINUTE WHERE id = {$s}");
        $gambar = '/gambar/' . $this->db->query('SELECT kunci FROM images')->fetch_assoc()['kunci'];
        $seb    = $this->sebagaiSeb($d['mhsA']);
        // Sebelum rilis: gambar tidak lagi dijawab (pengerjaannya sudah berakhir), di dalam SEB sekalipun.
        $this->assertSame(404, $seb->get($gambar, self::sebHeader($gambar, $kunci))->status);
        $this->assertStringEndsWith('?pesan=dirilis', (string) $this->kirim($http, "/sesi/{$s}/rilis", ['tingkat' => 'nilai_dan_kunci'])->header('Location'));

        // Dengan penegakan aktif: beranda (di luar SEB) menampilkan skor dan tautan berkas .seb;
        // tinjauan dan gambarnya ditolak di luar SEB dan dijawab di dalamnya.
        $beranda = $seb->get('/beranda');
        $this->assertSame(200, $beranda->status);
        $this->assertStringContainsString('Skor objektif: 0 dari 1', $beranda->body);
        $this->assertStringContainsString("/ujian-saya/{$s}/seb", $beranda->body);
        $tinjau = "/ujian-saya/{$s}/tinjau";
        $this->assertSame(403, $seb->get($tinjau)->status);
        $this->assertSame(403, $seb->get($gambar)->status);
        $r = $seb->get($tinjau, self::sebHeader($tinjau, $kunci));
        $this->assertSame(200, $r->status);
        $this->assertStringContainsString('Diagram tinjauan', $r->body);
        $this->assertStringContainsString($gambar, $r->body);
        $g = $seb->get($gambar, self::sebHeader($gambar, $kunci));
        $this->assertSame([200, 'image/png'], [$g->status, $g->header('Content-Type')]);
        // Mahasiswa kelas lain tidak mendapat gambarnya.
        $this->assertSame(404, $this->sebagai($d['mhsB'])->get($gambar)->status);
    }
}
