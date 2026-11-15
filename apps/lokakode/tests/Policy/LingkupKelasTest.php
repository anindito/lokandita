<?php

declare(strict_types=1);

namespace Tests\Policy;

use Lokandita\Auth\Testing\Http;
use Tests\Support\KodeCase;

/**
 * Lingkup pengampu, pengajar dan mahasiswa (SPEC.md §3) pada halaman baca.
 * Gerbang tahap 6: seorang pengajar tidak bisa membuka kelas pengajar lain dengan mengganti URL.
 */
final class LingkupKelasTest extends KodeCase
{
    /**
     * Isi halaman tanpa bagian yang berubah setiap detik.
     */
    private function inti(Http $r): string
    {
        return (string) preg_replace('/data-sesi-sisa="\d+"/', '', $r->body);
    }

    public function testPengajarTidakBisaMembukaKelasPengajarLainLewatUrl(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengajarA']);

        $milikSendiri = $http->get('/kelas/' . $d['kelasXA']);
        $this->assertSame(200, $milikSendiri->status);
        $this->assertStringContainsString('Mahasiswa Ani', $milikSendiri->body);
        $this->assertStringContainsString($d['mhsA']['nomor_induk'], $milikSendiri->body);

        // Kelas XB: mata kuliah yang sama, pengajar lain.
        $milikLain = $http->get('/kelas/' . $d['kelasXB']);
        $this->assertSame(404, $milikLain->status);
        $this->assertStringNotContainsString('Mahasiswa Budi', $milikLain->body);
        $this->assertStringNotContainsString($d['mhsB']['nomor_induk'], $milikLain->body);
        $this->assertStringNotContainsString('XB', $milikLain->body);

        // Kelas YA: mata kuliah lain.
        $this->assertSame(404, $http->get('/kelas/' . $d['kelasYA'])->status);
    }

    public function testKelasTerlarangDanKelasTidakAdaDijawabSama(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengajarA']);

        $terlarang = $http->get('/kelas/' . $d['kelasXB']);
        $tidakAda  = $http->get('/kelas/99999999');
        $this->assertSame(404, $tidakAda->status);
        // Nomor di URL tidak bisa dipakai menebak kelas mana yang ada.
        $this->assertSame($this->inti($tidakAda), $this->inti($terlarang));
    }

    public function testBerandaPengajarHanyaMemuatKelasnyaSendiri(): void
    {
        $d = $this->dunia();
        $r = $this->sebagai($d['pengajarA'])->get('/beranda');
        $this->assertSame(200, $r->status);
        $this->assertStringContainsString('/kelas/' . $d['kelasXA'] . '"', $r->body);
        $this->assertStringNotContainsString('/kelas/' . $d['kelasXB'] . '"', $r->body);
        $this->assertStringNotContainsString('/kelas/' . $d['kelasYA'] . '"', $r->body);
        $this->assertStringNotContainsString('UJY201', $r->body);
        // Ringkasan hanya menghitung yang boleh dilihatnya.
        $this->assertStringContainsString('data-ringkas="kelas">1<', $r->body);
        $this->assertStringContainsString('data-ringkas="mahasiswa">1<', $r->body);
    }

    public function testPengampuMelihatSemuaKelasMataKuliahnyaSaja(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengampuX']);

        $r = $http->get('/beranda');
        $this->assertStringContainsString('/kelas/' . $d['kelasXA'] . '"', $r->body);
        $this->assertStringContainsString('/kelas/' . $d['kelasXB'] . '"', $r->body);
        $this->assertStringNotContainsString('/kelas/' . $d['kelasYA'] . '"', $r->body);
        $this->assertStringContainsString('data-ringkas="kelas">2<', $r->body);

        $this->assertSame(200, $http->get('/kelas/' . $d['kelasXB'])->status);
        $this->assertSame(404, $http->get('/kelas/' . $d['kelasYA'])->status);
        $this->assertSame(404, $http->get('/mata-kuliah/' . $d['matkulY'])->status);

        $daftar = $http->get('/mata-kuliah')->body;
        $this->assertStringContainsString('UJX101', $daftar);
        $this->assertStringNotContainsString('UJY201', $daftar);
    }

    public function testPengampuSatuMataKuliahSekaligusPengajarKelasMataKuliahLain(): void
    {
        $d = $this->dunia();
        // Pengampu Y juga ditugaskan mengajar kelas XB.
        $this->ajar($d['kelasXB'], $d['pengampuY']);
        $http = $this->sebagai($d['pengampuY']);

        $this->assertSame(200, $http->get('/kelas/' . $d['kelasYA'])->status, 'kelas mata kuliah yang diampunya');
        $this->assertSame(200, $http->get('/kelas/' . $d['kelasXB'])->status, 'kelas yang diajarnya');
        $this->assertSame(404, $http->get('/kelas/' . $d['kelasXA'])->status, 'kelas lain di mata kuliah yang hanya diajarnya');

        // Halaman mata kuliah X baginya hanya memuat kelas XB.
        $x = $http->get('/mata-kuliah/' . $d['matkulX']);
        $this->assertSame(200, $x->status);
        $this->assertStringContainsString('/kelas/' . $d['kelasXB'] . '"', $x->body);
        $this->assertStringNotContainsString('/kelas/' . $d['kelasXA'] . '"', $x->body);
    }

    public function testPencabutanTugasLangsungBerlaku(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengajarA']);
        $this->assertSame(200, $http->get('/kelas/' . $d['kelasXA'])->status);

        $this->db->query("DELETE FROM class_teachers WHERE class_id = {$d['kelasXA']} AND user_id = '{$d['pengajarA']['id']}'");
        $this->assertSame(404, $http->get('/kelas/' . $d['kelasXA'])->status);
        $this->assertSame(404, $http->get('/mata-kuliah/' . $d['matkulX'])->status);
    }

    public function testSuperadminMelihatSemuanyaDanKelasTanpaPengajarDitandai(): void
    {
        $d = $this->dunia();
        $r = $this->sebagai($d['superadmin'])->get('/beranda');
        foreach (['kelasXA', 'kelasXB', 'kelasYA'] as $k) {
            $this->assertStringContainsString('/kelas/' . $d[$k] . '"', $r->body);
        }
        $this->assertStringContainsString('data-ringkas="kelas">3<', $r->body);
        $this->assertStringContainsString('data-ringkas="mahasiswa">3<', $r->body);
        $this->assertStringContainsString('data-ringkas="tanpa-pengajar">1<', $r->body); // kelas YA
        $this->assertStringContainsString('Belum ada pengajar', $r->body);
        $this->assertStringContainsString('Pengajar Aminah', $r->body, 'nama pengajar dibaca dari direktori LokaAuth');
    }

    public function testHanyaMahasiswaAktifYangDihitung(): void
    {
        $d = $this->dunia();
        $this->daftar($this->user('mahasiswa', 'Mahasiswa Mundur'), $d['kelasXA'], 'MENGUNDURKAN_DIRI');
        $this->daftar($this->user('mahasiswa', 'Mahasiswa Lulus'), $d['kelasXA'], 'LULUS');

        $r = $this->sebagai($d['pengajarA'])->get('/beranda');
        $this->assertStringContainsString('data-ringkas="mahasiswa">1<', $r->body);

        $kelas = $this->sebagai($d['pengajarA'])->get('/kelas/' . $d['kelasXA'])->body;
        $this->assertStringContainsString('3 mahasiswa, 1 aktif', $kelas);
        $this->assertStringContainsString('Mengundurkan diri', $kelas);
    }

    public function testMahasiswaHanyaMelihatKelasnyaSendiriDanTanpaMenuPengelolaan(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['mhsA']);

        $r = $http->get('/beranda');
        $this->assertSame(200, $r->status);
        $this->assertStringContainsString('UJX101', $r->body);
        $this->assertStringContainsString('XA', $r->body);
        $this->assertStringNotContainsString('UJY201', $r->body);
        $this->assertStringNotContainsString('Mahasiswa Budi', $r->body);
        $this->assertStringNotContainsString('admin-nav', $r->body);
        $this->assertStringNotContainsString('href="/kelas/', $r->body);

        // Halaman staf ditolak, juga untuk kelasnya sendiri.
        $this->assertSame(403, $http->get('/kelas/' . $d['kelasXA'])->status);
        $this->assertSame(403, $http->get('/mata-kuliah')->status);
        $this->assertSame(403, $http->get('/mata-kuliah/' . $d['matkulX'])->status);
    }

    public function testDaftarKelasMenandaiAkunTanpaAksesDanTidakAktif(): void
    {
        $d = $this->dunia();
        $this->daftar($this->user('mahasiswa', 'Tanpa Akses', ['apps' => ['portal']]), $d['kelasXA']);
        $this->daftar($this->user('mahasiswa', 'Akun Ditangguhkan', ['status' => 'suspended']), $d['kelasXA']);

        $isi = $this->sebagai($d['pengampuX'])->get('/kelas/' . $d['kelasXA'])->body;
        $this->assertMatchesRegularExpression('/Tanpa Akses.*?Belum diberi akses LokaKode/s', $isi);
        $this->assertMatchesRegularExpression('/Akun Ditangguhkan.*?Akun tidak aktif/s', $isi);
        // Mahasiswa Ani: aktif dan berakses, tanpa tanda.
        $this->assertSame(1, substr_count($isi, 'Belum diberi akses LokaKode'));
        $this->assertSame(1, substr_count($isi, 'Akun tidak aktif'));
    }

    public function testNomorKelasBukanAngkaTidakSampaiKeKueri(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['superadmin']);
        foreach (["/kelas/1%20OR%201=1", '/kelas/abc', '/kelas/-1', "/kelas/{$d['kelasXA']}'"] as $jalur) {
            // CI4 sendiri menolak karakter terlarang di URL dengan 400; sisanya tidak cocok dengan rute angka.
            $this->assertContains($http->get($jalur)->status, [400, 404], $jalur);
        }
        $this->assertSame(3, $this->hitung('SELECT COUNT(*) FROM classes'), 'tidak ada yang berubah');
    }

    public function testTahunAjaranLainBisaDipilihDiHalamanMataKuliah(): void
    {
        $d    = $this->dunia();
        $lama = $this->tahun('2025/2026 Genap', 'archived', '2026-02-01', '2026-07-31');
        $kl   = $this->kelas($d['matkulX'], $lama, 'XLAMA');
        $http = $this->sebagai($d['pengampuX']);

        $kini = $http->get('/mata-kuliah/' . $d['matkulX'])->body;
        $this->assertStringNotContainsString('XLAMA', $kini);
        $dulu = $http->get('/mata-kuliah/' . $d['matkulX'] . '?tahun=' . $lama)->body;
        $this->assertStringContainsString('/kelas/' . $kl . '"', $dulu);
        $this->assertStringNotContainsString('/kelas/' . $d['kelasXA'] . '"', $dulu);
    }
}
