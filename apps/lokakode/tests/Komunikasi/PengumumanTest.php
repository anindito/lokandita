<?php

declare(strict_types=1);

namespace Tests\Komunikasi;

use Lokandita\Auth\Testing\Fixtures;
use Lokandita\Auth\Testing\Http;
use Tests\Support\KodeCase;

/**
 * Pengumuman kelas (SPEC.md §14, KONTRAK.md §17) lewat HTTP sungguhan: siapa yang menulis dan
 * menarik, siapa yang membaca, dan bahwa rute mahasiswa yang baru di tahap 10b ikut dijaga SEB.
 */
final class PengumumanTest extends KodeCase
{
    /**
     * @param array<string, mixed> $dosen
     */
    private function umumkan(array $dosen, int $kelas, string $judul, string $isi): Http
    {
        $http = $this->sebagai($dosen);

        return $http->post("/kelas/{$kelas}/pengumuman", ['csrf' => $this->csrf($http), 'judul' => $judul, 'isi' => $isi]);
    }

    public function testDosenMengumumkanDanMahasiswaKelasItuMembacanya(): void
    {
        $d = $this->dunia();
        $r = $this->umumkan($d['pengajarA'], $d['kelasXA'], '  Kuis <b>minggu</b> depan ', "Kuis diundur ke Jumat.\r\n\r\nBawa <script>alert(1)</script> kartu ujian.");
        $this->assertContains($r->status, [302, 303]);
        $a = $this->row('SELECT * FROM announcements');
        $this->assertSame([(string) $d['kelasXA'], $d['pengajarA']['id'], 'Kuis <b>minggu</b> depan', "Kuis diundur ke Jumat.\n\nBawa <script>alert(1)</script> kartu ujian.", null],
            [$a['class_id'], $a['author_id'], $a['title'], $a['body'], $a['withdrawn_at']]);
        $audit = $this->row("SELECT actor_id, class_id, target_id FROM audit_log WHERE action = 'pengumuman.dibuat'");
        $this->assertSame([$d['pengajarA']['id'], (string) $d['kelasXA'], (string) $a['id']], array_values($audit));

        // Mahasiswa kelas XA: di beranda dan di halaman pengumuman, sebagai teks.
        foreach (['/beranda', '/pengumuman'] as $jalur) {
            $r = $this->sebagai($d['mhsA'])->get($jalur);
            $this->assertSame(200, $r->status, $jalur);
            $this->assertStringContainsString('Kuis &lt;b&gt;minggu&lt;/b&gt; depan', $r->body, $jalur);
            $this->assertStringContainsString('Bawa &lt;script&gt;alert(1)&lt;/script&gt; kartu ujian.', $r->body, $jalur);
            $this->assertStringNotContainsString('<script>alert(1)</script>', $r->body, $jalur);
            $this->assertStringContainsString('Pengajar Aminah', $r->body, $jalur);
            $this->assertStringContainsString('UJX101', $r->body, $jalur);
        }
        // Kelas lain dan mata kuliah lain tidak melihatnya.
        foreach (['mhsB', 'mhsY'] as $lain) {
            foreach (['/beranda', '/pengumuman'] as $jalur) {
                $this->assertStringNotContainsString('Kuis', $this->sebagai($d[$lain])->get($jalur)->body, "{$lain} {$jalur}");
            }
        }
        $this->assertStringContainsString('Belum ada pengumuman untuk kelas Anda.', $this->sebagai($d['mhsB'])->get('/pengumuman')->body);

        // Pengampu dan superadmin menulis juga; beranda memuat tiga yang terbaru, halaman pengumuman semuanya.
        foreach ([['pengampuX', 'Kedua'], ['superadmin', 'Ketiga'], ['pengajarA', 'Keempat']] as [$siapa, $judul]) {
            $this->assertContains($this->umumkan($d[$siapa], $d['kelasXA'], $judul, "Isi {$judul}")->status, [302, 303], $siapa);
        }
        $beranda = $this->sebagai($d['mhsA'])->get('/beranda')->body;
        $this->assertStringContainsString('Isi Keempat', $beranda);
        $this->assertStringContainsString('Isi Kedua', $beranda);
        $this->assertStringNotContainsString('Kuis diundur', $beranda);
        $semua = $this->sebagai($d['mhsA'])->get('/pengumuman')->body;
        $this->assertSame(4, substr_count($semua, '<li class="kartu">'));
        $this->assertLessThan(strpos($semua, 'Isi Kedua'), strpos($semua, 'Isi Keempat'), 'terbaru lebih dulu');

        // Mahasiswa yang lulus masih membaca; yang mengundurkan diri tidak.
        $this->roster->query("UPDATE enrollments SET status = 'LULUS' WHERE id = {$d['daftarA']}");
        $this->assertStringContainsString('Isi Keempat', $this->sebagai($d['mhsA'])->get('/pengumuman')->body);
        $this->roster->query("UPDATE enrollments SET status = 'MENGUNDURKAN_DIRI' WHERE id = {$d['daftarA']}");
        $this->assertStringNotContainsString('Isi Keempat', $this->sebagai($d['mhsA'])->get('/pengumuman')->body);
    }

    public function testPengumumanDitarikTidakDihapus(): void
    {
        $d = $this->dunia();
        $this->umumkan($d['pengajarA'], $d['kelasXA'], 'Salah tanggal', 'Kuis hari Senin.');
        $id     = (int) $this->row('SELECT id FROM announcements')['id'];
        $alamat = '/kelas/' . $d['kelasXA'] . '/pengumuman';

        // Pengampu menarik pengumuman pengajar.
        $x = $this->sebagai($d['pengampuX']);
        $r = $x->post("{$alamat}/{$id}/tarik", ['csrf' => $this->csrf($x)]);
        $this->assertContains($r->status, [302, 303]);
        $a = $this->row("SELECT withdrawn_by, withdrawn_at, title FROM announcements WHERE id = {$id}");
        $this->assertSame([$d['pengampuX']['id'], 'Salah tanggal'], [$a['withdrawn_by'], $a['title']]);
        $this->assertNotNull($a['withdrawn_at']);
        $this->assertSame(1, $this->hitung("SELECT COUNT(*) FROM audit_log WHERE action = 'pengumuman.ditarik'"));

        // Mahasiswa tidak melihatnya lagi; dosen masih melihatnya, ditandai, tanpa tombol tarik.
        foreach (['/beranda', '/pengumuman'] as $jalur) {
            $this->assertStringNotContainsString('Salah tanggal', $this->sebagai($d['mhsA'])->get($jalur)->body, $jalur);
        }
        $r = $this->sebagai($d['pengajarA'])->get($alamat);
        $this->assertStringContainsString('Salah tanggal', $r->body);
        $this->assertStringContainsString('data-ditarik="1"', $r->body);
        $this->assertStringContainsString('ditarik oleh Pengampu Xaverius', $r->body);
        $this->assertStringNotContainsString("/{$id}/tarik", $r->body);
        // Menarik dua kali (klik ganda): bukan galat, tidak ada yang berubah, tidak ada audit kedua.
        $this->assertContains($x->post("{$alamat}/{$id}/tarik", ['csrf' => $this->csrf($x)])->status, [302, 303]);
        $this->assertSame($d['pengampuX']['id'], $this->row("SELECT withdrawn_by FROM announcements WHERE id = {$id}")['withdrawn_by']);
        $this->assertSame(404, $x->post("{$alamat}/999999/tarik", ['csrf' => $this->csrf($x)])->status);
        $this->assertSame(1, $this->hitung("SELECT COUNT(*) FROM audit_log WHERE action = 'pengumuman.ditarik'"));
    }

    public function testHanyaYangMelihatKelasItuDanIsianDiperiksa(): void
    {
        $d = $this->dunia();
        $this->umumkan($d['pengajarB'], $d['kelasXB'], 'Rahasia XB', 'Hanya untuk kelas XB.');
        $idB    = (int) $this->row('SELECT id FROM announcements')['id'];
        $alamat = '/kelas/' . $d['kelasXA'] . '/pengumuman';

        // IDOR: pengajar kelas lain dan pengampu mata kuliah lain 404, mahasiswa 403 — membaca, menulis, menarik.
        foreach (['pengajarB' => 404, 'pengampuY' => 404, 'mhsA' => 403] as $siapa => $harap) {
            $http = $this->sebagai($d[$siapa]);
            $csrf = $this->csrf($http);
            $this->assertSame($harap, $http->get($alamat)->status, $siapa);
            $this->assertSame($harap, $http->post($alamat, ['csrf' => $csrf, 'judul' => 'Dibajak', 'isi' => 'Dibajak'])->status, $siapa);
            $this->assertSame($harap, $http->post("{$alamat}/{$idB}/tarik", ['csrf' => $csrf])->status, $siapa);
        }
        // Pengajar A tidak bisa menarik pengumuman kelas XB lewat alamat kelasnya sendiri, dan tidak melihatnya.
        $a = $this->sebagai($d['pengajarA']);
        $this->assertSame(404, $a->post("{$alamat}/{$idB}/tarik", ['csrf' => $this->csrf($a)])->status);
        $this->assertSame(404, $a->post('/kelas/' . $d['kelasXB'] . "/pengumuman/{$idB}/tarik", ['csrf' => $this->csrf($a)])->status);
        $this->assertStringNotContainsString('Rahasia XB', $a->get($alamat)->body);
        $this->assertSame([1, 0], [$this->hitung('SELECT COUNT(*) FROM announcements'), $this->hitung('SELECT COUNT(*) FROM announcements WHERE withdrawn_at IS NOT NULL')]);

        // Isian: judul dan isi wajib, 150 dan 5.000 karakter; yang sudah diketik tidak hilang.
        foreach ([['', 'Isi'], ['Judul', "  \n"], [str_repeat('j', 151), 'Isi'], ['Judul', str_repeat('i', 5001)]] as [$judul, $isi]) {
            $r = $this->umumkan($d['pengajarA'], $d['kelasXA'], $judul, $isi);
            $this->assertSame(422, $r->status);
            $this->assertStringContainsString('Pengumuman belum dikirim', $r->body);
        }
        $r = $this->umumkan($d['pengajarA'], $d['kelasXA'], '', 'Teks yang sudah diketik');
        $this->assertStringContainsString('Teks yang sudah diketik</textarea>', $r->body);
        $this->assertSame(403, $a->post($alamat, ['judul' => 'Tanpa CSRF', 'isi' => 'x'])->status);
        $this->assertSame(1, $this->hitung('SELECT COUNT(*) FROM announcements'));
        // Tepat di batas: diterima (karakter, bukan byte).
        $this->assertContains($this->umumkan($d['pengajarA'], $d['kelasXA'], str_repeat('é', 150), str_repeat('é', 5000))->status, [302, 303]);
    }

    public function testTahunArsipHanyaBacaDanKelasBerpengumumanTidakBisaDihapus(): void
    {
        $d = $this->dunia();
        // Kelas kosong (tanpa pendaftaran) yang punya pengumuman tidak bisa dihapus: kunci asing
        // lintas skema menolaknya, juga bagi auth_app — satu-satunya yang boleh menghapus kelas.
        $kosong = $this->kelas($d['matkulX'], $d['tahun'], 'XC');
        $this->umumkan($d['pengampuX'], $kosong, 'Selamat datang', 'Kelas baru.');
        try {
            $this->roster->query("DELETE FROM classes WHERE id = {$kosong}");
            $this->fail('Kelas yang punya pengumuman terhapus.');
        } catch (\mysqli_sql_exception $e) {
            $this->assertSame(1451, $e->getCode());
        }
        $this->assertSame(1, $this->hitung("SELECT COUNT(*) FROM classes WHERE id = {$kosong}"));

        // Tahun ajaran diarsipkan: dosen membaca, tidak menulis dan tidak menarik; superadmin masih bisa.
        $this->umumkan($d['pengajarA'], $d['kelasXA'], 'Sebelum arsip', 'Isi.');
        $id = (int) $this->row("SELECT id FROM announcements WHERE class_id = {$d['kelasXA']}")['id'];
        $this->roster->query("UPDATE academic_years SET status = 'archived' WHERE id = {$d['tahun']}");
        $a = $this->sebagai($d['pengajarA']);
        $r = $a->get('/kelas/' . $d['kelasXA'] . '/pengumuman');
        $this->assertSame(200, $r->status);
        $this->assertStringContainsString('Sebelum arsip', $r->body);
        $this->assertStringNotContainsString('name="judul"', $r->body);
        $this->assertSame(403, $this->umumkan($d['pengajarA'], $d['kelasXA'], 'Sesudah arsip', 'Isi.')->status);
        $this->assertSame(403, $a->post('/kelas/' . $d['kelasXA'] . "/pengumuman/{$id}/tarik", ['csrf' => $this->csrf($a)])->status);
        $this->assertContains($this->umumkan($d['superadmin'], $d['kelasXA'], 'Dari pengelola', 'Isi.')->status, [302, 303]);
        $this->assertSame(0, $this->hitung('SELECT COUNT(*) FROM announcements WHERE withdrawn_at IS NOT NULL'));
    }

    /**
     * Rute mahasiswa yang baru di tahap 10b berada di belakang filter `seb` (KONTRAK.md §8):
     * di kontainer dengan penegakan aktif, peramban biasa ditolak 403 di semuanya.
     */
    public function testRuteMahasiswaBaruDitolakTanpaSafeExamBrowser(): void
    {
        $d    = $this->dunia();
        $soal = $this->soalSiap($d['pertemuanX1'], 'jumlah-dua-bilangan');
        $this->terbit($d['pertemuanX1']);
        $this->jendela($d['pertemuanX1'], $d['kelasXA']);
        $this->umumkan($d['pengajarA'], $d['kelasXA'], 'Hanya di dalam SEB', 'Isi.');

        $mhs = new Http((string) getenv('APP_SEB_TES_URL'));
        $mhs->cookies['lokandita_sid'] = Fixtures::session($this->idn, (string) $d['mhsA']['id']);
        foreach (['/pengumuman', '/notifikasi'] as $jalur) {
            $r = $mhs->get($jalur);
            $this->assertSame(403, $r->status, $jalur);
            $this->assertStringNotContainsString('Hanya di dalam SEB', $r->body);
        }
        $api = '/api/soal/' . $soal['mp'] . '/klarifikasi';
        $r   = $mhs->get($api);
        $this->assertSame([403, 'seb_wajib'], [$r->status, $r->json()['error']]);
        $this->assertSame(403, $mhs->post($api, ['pertanyaan' => 'Dari peramban biasa'])->status);
        $this->assertSame(403, $mhs->post('/notifikasi/baca', [])->status);
        $this->assertSame(403, $mhs->post('/api/integritas', ['mp' => (string) $soal['mp'], 'jenis' => 'ketik', 'karakter' => '900', 'ms' => '0'])->status);
        $this->assertSame([0, 0], [$this->hitung('SELECT COUNT(*) FROM clarifications'), $this->hitung("SELECT COUNT(*) FROM integrity_events WHERE kind = 'typing_anomaly'")]);
        // Dosen di kontainer yang sama tidak diperiksa SEB.
        $dosen = new Http((string) getenv('APP_SEB_TES_URL'));
        $dosen->cookies['lokandita_sid'] = Fixtures::session($this->idn, (string) $d['pengajarA']['id']);
        foreach (['pengumuman', 'klarifikasi', 'integritas'] as $hal) {
            $this->assertSame(200, $dosen->get('/kelas/' . $d['kelasXA'] . '/' . $hal)->status, $hal);
        }
    }
}
