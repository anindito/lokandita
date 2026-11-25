<?php

declare(strict_types=1);

namespace Tests\Policy;

use Tests\Support\UjianCase;

/**
 * Lingkup pengampu, pengajar dan mahasiswa (SPEC-LOKAUJIAN.md §3, §4), dibaca dari view roster.
 * Gerbang tahap U1: seorang pengajar hanya melihat kelasnya sendiri; mengganti nomor kelas di
 * URL memberi "Halaman tidak ditemukan"; mahasiswa melihat daftar kosong.
 */
final class LingkupKelasTest extends UjianCase
{
    public function testPengajarTidakBisaMembukaKelasPengajarLainLewatUrl(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengajarA']);

        $milikSendiri = $http->get('/kelas/' . $d['kelasXA']);
        $this->assertSame(200, $milikSendiri->status);
        $this->assertStringContainsString('Mahasiswa Ani', $milikSendiri->body);
        $this->assertStringContainsString($d['mhsA']['nomor_induk'], $milikSendiri->body);
        $this->assertStringContainsString('Pengajar Aminah', $milikSendiri->body);
        $this->assertStringContainsString('Pengampu Xaverius', $milikSendiri->body);
        // Hanya pendaftaran AKTIF: yang lulus dan yang mengundurkan diri tidak tampil.
        $this->assertStringContainsString('data-jumlah-mahasiswa>1<', $milikSendiri->body);
        $this->assertStringNotContainsString('Mahasiswa Lulusan', $milikSendiri->body);
        $this->assertStringNotContainsString('Mahasiswa Mundur', $milikSendiri->body);
        $this->assertStringContainsString('<meta name="robots" content="noindex, nofollow">', $milikSendiri->body);
        $this->assertStringContainsString('no-store', (string) $milikSendiri->header('Cache-Control'));

        // Kelas XB: mata kuliah yang sama, pengajar lain.
        $milikLain = $http->get('/kelas/' . $d['kelasXB']);
        $this->assertSame(404, $milikLain->status);
        $this->assertStringContainsString('Halaman tidak ditemukan', $milikLain->body);
        $this->assertStringNotContainsString('Mahasiswa Budi', $milikLain->body);
        $this->assertStringNotContainsString($d['mhsB']['nomor_induk'], $milikLain->body);
        $this->assertStringNotContainsString('Pengajar Bambang', $milikLain->body);
        $this->assertStringNotContainsString('XB', $milikLain->body);

        // Kelas YA: mata kuliah lain.
        $this->assertSame(404, $http->get('/kelas/' . $d['kelasYA'])->status);
        $this->assertSame(404, $http->get('/mata-kuliah/' . $d['matkulY'])->status);
    }

    public function testKelasTerlarangDanKelasTidakAdaDijawabSama(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengajarA']);

        foreach ([['/kelas/' . $d['kelasXB'], '/kelas/99999999'], ['/mata-kuliah/' . $d['matkulY'], '/mata-kuliah/99999999']] as [$jalurTerlarang, $jalurTidakAda]) {
            // Http mengembalikan dirinya sendiri, jadi isi jawaban pertama disalin dulu.
            $r = $http->get($jalurTerlarang);
            $this->assertSame(404, $r->status);
            $terlarang = self::inti($r->body, $jalurTerlarang);

            $r = $http->get($jalurTidakAda);
            $this->assertSame(404, $r->status);
            $tidakAda = self::inti($r->body, $jalurTidakAda);

            // Nomor di URL tidak bisa dipakai menebak kelas atau mata kuliah mana yang ada.
            $this->assertGreaterThan(500, strlen($terlarang));
            $this->assertSame($tidakAda, $terlarang, $jalurTerlarang);
        }
    }

    public function testBerandaPengajarHanyaMemuatKelasnyaSendiri(): void
    {
        $d = $this->dunia();
        $r = $this->sebagai($d['pengajarA'])->get('/beranda');
        $this->assertSame(200, $r->status);
        $this->assertStringContainsString('/kelas/' . $d['kelasXA'] . '"', $r->body);
        $this->assertStringNotContainsString('/kelas/' . $d['kelasXB'] . '"', $r->body);
        $this->assertStringNotContainsString('/kelas/' . $d['kelasYA'] . '"', $r->body);
        $this->assertStringNotContainsString('/kelas/' . $d['kelasXArsip'] . '"', $r->body);
        // Beranda hanya memuat tahun ajaran aktif; kelasnya di tahun yang diarsipkan tidak di sini.
        $this->assertStringNotContainsString('/kelas/' . $d['kelasXLama'] . '"', $r->body);
        $this->assertStringNotContainsString('UJY201', $r->body);
        $this->assertStringNotContainsString('Pengajar Bambang', $r->body);
        // Ringkasan hanya menghitung yang boleh dilihatnya.
        $this->assertStringContainsString('data-ringkas="kelas">1<', $r->body);
        $this->assertStringContainsString('data-ringkas="mahasiswa">1<', $r->body);
    }

    public function testPengajarMelihatMataKuliahnyaDenganKelasnyaSendiriSaja(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengajarA']);

        $r = $http->get('/mata-kuliah');
        $this->assertSame(200, $r->status);
        $this->assertStringContainsString('UJX101', $r->body);
        $this->assertStringNotContainsString('UJY201', $r->body);
        $this->assertStringContainsString('Anda pengajar', $r->body);
        $this->assertStringNotContainsString('Anda pengampu', $r->body);

        $r = $http->get('/mata-kuliah/' . $d['matkulX']);
        $this->assertSame(200, $r->status);
        $this->assertStringContainsString('/kelas/' . $d['kelasXA'] . '"', $r->body);
        $this->assertStringNotContainsString('/kelas/' . $d['kelasXB'] . '"', $r->body);
        $this->assertStringNotContainsString('Pengajar Bambang', $r->body);
    }

    public function testKelasDiTahunYangDiarsipkanTetapTerbukaLewatAlamatnyaDanDitandai(): void
    {
        $d = $this->dunia();
        $r = $this->sebagai($d['pengajarA'])->get('/kelas/' . $d['kelasXLama']);
        $this->assertSame(200, $r->status);
        $this->assertStringContainsString('2025/2026 Genap (diarsipkan)', $r->body);
        $this->assertStringContainsString('Mahasiswa Tahunlama', $r->body);
        // Pengajar B tidak mengajar kelas itu.
        $this->assertSame(404, $this->sebagai($d['pengajarB'])->get('/kelas/' . $d['kelasXLama'])->status);
    }

    public function testPengampuMelihatSemuaKelasMataKuliahnyaSaja(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengampuX']);

        $r = $http->get('/beranda');
        $this->assertStringContainsString('/kelas/' . $d['kelasXA'] . '"', $r->body);
        $this->assertStringContainsString('/kelas/' . $d['kelasXB'] . '"', $r->body);
        // Kelas yang diarsipkan tampil bagi stafnya, dengan tandanya.
        $this->assertStringContainsString('/kelas/' . $d['kelasXArsip'] . '"', $r->body);
        $this->assertStringContainsString('Diarsipkan', $r->body);
        $this->assertStringNotContainsString('/kelas/' . $d['kelasYA'] . '"', $r->body);
        $this->assertStringNotContainsString('UJY201', $r->body);
        $this->assertStringContainsString('data-ringkas="kelas">3<', $r->body);

        $this->assertSame(200, $http->get('/kelas/' . $d['kelasXB'])->status);
        $this->assertSame(404, $http->get('/kelas/' . $d['kelasYA'])->status);
        $this->assertSame(404, $http->get('/mata-kuliah/' . $d['matkulY'])->status);

        $r = $http->get('/mata-kuliah');
        $this->assertStringContainsString('UJX101', $r->body);
        $this->assertStringNotContainsString('UJY201', $r->body);
        $this->assertStringContainsString('Anda pengampu', $r->body);
    }

    public function testSuperadminMelihatSemuanya(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['superadmin']);
        $r    = $http->get('/beranda');
        foreach (['kelasXA', 'kelasXB', 'kelasYA', 'kelasXArsip'] as $k) {
            $this->assertStringContainsString('/kelas/' . $d[$k] . '"', $r->body, $k);
        }
        $r = $http->get('/mata-kuliah');
        $this->assertStringContainsString('UJX101', $r->body);
        $this->assertStringContainsString('UJY201', $r->body);
        $this->assertSame(200, $http->get('/kelas/' . $d['kelasYA'])->status);
        $this->assertSame(200, $http->get('/kelas/' . $d['kelasXLama'])->status);
    }

    /**
     * Peran lingkup diturunkan pada setiap permintaan (SPEC-LOKAUJIAN.md §3): penugasan yang
     * dicabut berhenti berlaku pada permintaan berikutnya, dengan sesi yang sama.
     */
    public function testPenugasanYangDicabutLangsungTidakBerlaku(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengajarA']);
        $this->assertSame(200, $http->get('/kelas/' . $d['kelasXA'])->status);

        $this->roster->query("DELETE FROM class_teachers WHERE class_id = {$d['kelasXA']} AND user_id = '{$d['pengajarA']['id']}'");
        $this->assertSame(404, $http->get('/kelas/' . $d['kelasXA'])->status);
        $this->assertStringNotContainsString('/kelas/' . $d['kelasXA'] . '"', $http->get('/beranda')->body);

        // Ditugaskan ke kelas XB: kelas itu, dan hanya itu, yang sekarang terbuka.
        $this->ajar($d['kelasXB'], $d['pengajarA']);
        $this->assertSame(200, $http->get('/kelas/' . $d['kelasXB'])->status);
        $this->assertSame(404, $http->get('/kelas/' . $d['kelasXA'])->status);

        // Pengampu yang diganti kehilangan mata kuliahnya seketika.
        $x = $this->sebagai($d['pengampuX']);
        $this->assertSame(200, $x->get('/mata-kuliah/' . $d['matkulX'])->status);
        $this->roster->query("UPDATE courses SET owner_id = '{$d['pengampuY']['id']}' WHERE id = {$d['matkulX']}");
        $this->assertSame(404, $x->get('/mata-kuliah/' . $d['matkulX'])->status);
        $this->assertSame(404, $x->get('/kelas/' . $d['kelasXA'])->status);
    }

    public function testDosenTanpaKelasMelihatDaftarKosong(): void
    {
        $this->dunia();
        $http = $this->sebagai($this->user('dosen', 'Dosen Baru'));
        $r    = $http->get('/beranda');
        $this->assertSame(200, $r->status);
        $this->assertStringContainsString('Anda belum mengampu atau mengajar kelas pada tahun ajaran ini.', $r->body);
        $this->assertStringNotContainsString('/kelas/', $r->body);
        $this->assertStringNotContainsString('UJX101', $r->body);
        $r = $http->get('/mata-kuliah');
        $this->assertStringContainsString('Belum ada mata kuliah yang Anda ampu atau ajar.', $r->body);
        $this->assertStringNotContainsString('UJX101', $r->body);
    }

    public function testTanpaTahunAjaranAktifBerandaStafTetapTerbuka(): void
    {
        $d = $this->dunia();
        $this->roster->query("UPDATE academic_years SET status = 'archived' WHERE id = {$d['tahun']}");
        $r = $this->sebagai($d['pengampuX'])->get('/beranda');
        $this->assertSame(200, $r->status);
        $this->assertStringContainsString('Belum ada tahun ajaran yang aktif.', $r->body);
        $this->assertSame(200, $this->sebagai($d['pengampuX'])->get('/mata-kuliah/' . $d['matkulX'])->status);
    }

    /**
     * Gerbang tahap U1: mahasiswa melihat daftar ujian yang kosong — dan dari roster hanya
     * kelasnya sendiri.
     */
    public function testMahasiswaMelihatDaftarKosongDanKelasnyaSendiri(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['mhsA']);
        $r    = $http->get('/beranda');
        $this->assertSame(200, $r->status);
        $this->assertStringContainsString('data-ujian-kosong', $r->body);
        $this->assertStringContainsString('Belum ada ujian atau kuis untuk Anda.', $r->body);
        $this->assertSame(1, substr_count($r->body, 'data-kelas-saya'));
        $this->assertStringContainsString('UJX101', $r->body);
        $this->assertStringContainsString('>XA<', $r->body);
        $this->assertStringNotContainsString('XB', $r->body);
        $this->assertStringNotContainsString('UJY201', $r->body);
        // Tidak ada nama orang lain dan tidak ada tautan ke halaman staf.
        $this->assertStringNotContainsString('Mahasiswa Budi', $r->body);
        $this->assertStringNotContainsString('Pengajar Aminah', $r->body);
        $this->assertStringNotContainsString('href="/kelas/', $r->body);
        $this->assertStringNotContainsString('href="/mata-kuliah', $r->body);

        // Halaman staf: 403 bagi mahasiswa, juga untuk kelasnya sendiri.
        foreach (['/mata-kuliah', '/mata-kuliah/' . $d['matkulX'], '/kelas/' . $d['kelasXA'], '/kelas/' . $d['kelasXB']] as $jalur) {
            $t = $http->get($jalur);
            $this->assertSame(403, $t->status, $jalur);
            // Namanya sendiri ada di menu akun; yang tidak boleh ada adalah isi halaman staf itu.
            foreach (['UJX101', 'Mahasiswa Budi', 'Pengajar Aminah', 'Pengampu Xaverius', 'data-jumlah-mahasiswa'] as $rahasia) {
                $this->assertStringNotContainsString($rahasia, $t->body, "{$jalur}: «{$rahasia}»");
            }
        }

        // Pindah kelas mengubah class_id pada baris yang sama (KONTRAK.md §18): langsung terlihat.
        $this->roster->query("UPDATE enrollments SET class_id = {$d['kelasXB']} WHERE user_id = '{$d['mhsA']['id']}'");
        $r = $http->get('/beranda');
        $this->assertStringContainsString('>XB<', $r->body);
        $this->assertStringNotContainsString('>XA<', $r->body);
    }

    /**
     * SPEC-LOKAUJIAN.md §3: mahasiswa sebuah kelas = pendaftaran AKTIF, kelas tidak diarsipkan,
     * tahun ajaran aktif. Selain itu ia tidak punya kelas di LokaUjian.
     */
    public function testYangBukanMahasiswaAktifKelasBerjalanTidakPunyaKelas(): void
    {
        $d = $this->dunia();
        foreach (['mhsLulus', 'mhsMundur', 'mhsArsip', 'mhsLama'] as $siapa) {
            $r = $this->sebagai($d[$siapa])->get('/beranda');
            $this->assertSame(200, $r->status, $siapa);
            $this->assertStringContainsString('Anda belum terdaftar di kelas mana pun', $r->body, $siapa);
            $this->assertStringNotContainsString('data-kelas-saya', $r->body, $siapa);
            $this->assertStringNotContainsString('UJX101', $r->body, $siapa);
        }
        $r = $this->sebagai($this->user('mahasiswa', 'Mahasiswa Tanpakelas'))->get('/beranda');
        $this->assertSame(200, $r->status);
        $this->assertStringContainsString('Anda belum terdaftar di kelas mana pun', $r->body);
    }

    public function testHalamanStafDalamBahasaArabBerarahKananKeKiri(): void
    {
        $d = $this->dunia();
        $this->idn->query("UPDATE users SET locale = 'ar' WHERE id = '{$d['pengajarA']['id']}'");
        $r = $this->sebagai($d['pengajarA'])->get('/kelas/' . $d['kelasXA']);
        $this->assertSame(200, $r->status);
        $this->assertStringContainsString('<html lang="ar" dir="rtl">', $r->body);
        $this->assertStringContainsString('الطلاب النشطون', $r->body);
        $this->assertStringNotContainsString('Ujian.', $r->body, 'kunci bahasa yang tidak diterjemahkan');
    }
}
