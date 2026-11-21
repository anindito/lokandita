<?php

declare(strict_types=1);

namespace Tests;

use Tests\Support\PortalCase;

/**
 * Halaman publik Portal (SPEC.md §15, KONTRAK.md §4 "Public routes"), lewat HTTP sungguhan.
 */
final class PublikTest extends PortalCase
{
    private const HALAMAN = [
        '/'          => 'Satu akun untuk semua aplikasi belajar Anda',
        '/tentang'   => 'Tentang Lokandita',
        '/privasi'   => 'Kebijakan privasi',
        '/ketentuan' => 'Ketentuan penggunaan',
    ];

    public function testSetiapHalamanPublikTerbukaTanpaSesiDanBolehDiindeks(): void
    {
        $surel = (string) getenv('CONTACT_EMAIL');
        $this->assertNotSame('', $surel);
        foreach (self::HALAMAN as $jalur => $tajuk) {
            $tamu = $this->tamu();
            $r    = $tamu->get($jalur);
            $this->assertSame(200, $r->status, $jalur);
            $this->assertStringContainsString('<html lang="id" dir="ltr">', $r->body, $jalur);
            $this->assertStringContainsString('<h1>' . $tajuk . '</h1>', $r->body, $jalur);
            $this->assertStringContainsString('<meta name="robots" content="index, follow">', $r->body, $jalur);
            $this->assertStringNotContainsString('noindex', $r->body, $jalur);
            $this->assertStringContainsString('<link rel="canonical" href="https://lokandita.com' . $jalur . '">', $r->body, $jalur);
            $this->assertMatchesRegularExpression('/<meta name="description" content="[^"]{40,}">/', $r->body, $jalur);
            // Tombol Masuk, navigasi, dan pilihan bahasa yang kembali ke halaman ini.
            $this->assertStringContainsString('<a class="tombol" href="/beranda" rel="nofollow">Masuk</a>', $r->body, $jalur);
            foreach (['/tentang', '/privasi', '/ketentuan'] as $nav) {
                $this->assertStringContainsString('href="' . $nav . '"', $r->body, $jalur);
            }
            $this->assertStringNotContainsString('href="/kontak"', $r->body, "{$jalur}: Kontak bukan halaman sendiri lagi");
            $this->assertStringContainsString('href="/bahasa/en?kembali=' . rawurlencode($jalur) . '"', $r->body, $jalur);
            // Tanpa sesi: tidak ada menu akun, tidak ada cookie sesi.
            $this->assertStringNotContainsString('data-menu-akun', $r->body, $jalur);
            $this->assertArrayNotHasKey('lokandita_sid', $tamu->cookies, $jalur);
            // Alamat kontak hanya dari CONTACT_EMAIL; tidak ada tempat isian yang tertinggal.
            $this->assertStringContainsString('mailto:' . $surel, $r->body, $jalur);
            $this->assertStringNotContainsString('{email}', $r->body, $jalur);
            // Header keamanan (SPEC.md §16).
            $this->assertMatchesRegularExpression("/script-src 'self' 'nonce-/", (string) $r->header('Content-Security-Policy'), $jalur);
            $this->assertSame('DENY', $r->header('X-Frame-Options'), $jalur);
            $this->assertSame('nosniff', $r->header('X-Content-Type-Options'), $jalur);
            $this->assertSame('same-origin', $r->header('Referrer-Policy'), $jalur);
        }
    }

    /**
     * Halaman Kontak dihapus dan isinya menjadi bagian halaman Tentang (keputusan pemilik
     * 2026-11-21). Alamat lama /kontak dialihkan untuk selamanya ke /tentang.
     */
    public function testIsiKontakAdaDiHalamanTentang(): void
    {
        $r = $this->tamu()->get('/kontak');
        $this->assertSame(301, $r->status);
        $this->assertSame('https://lokandita.com/tentang', $r->header('Location'));

        $r = $this->tamu()->get('/tentang');
        $this->assertSame(1, preg_match_all('/<h1>/', $r->body), 'satu judul utama');
        $urut = [];
        foreach (['<h1>Tentang Lokandita</h1>', '<h2>Pengelola</h2>', '<h2>Kontak</h2>', '<h2>Lupa kata sandi</h2>', '<h2>Belum punya akun</h2>', '<h2>Melaporkan masalah</h2>'] as $judul) {
            $letak = strpos($r->body, $judul);
            $this->assertNotFalse($letak, $judul);
            $urut[] = $letak;
        }
        $sudah = $urut;
        sort($sudah);
        $this->assertSame($sudah, $urut, 'isi Kontak menyusul isi Tentang, dalam urutan semula');
        $surel = (string) getenv('CONTACT_EMAIL');
        $this->assertStringContainsString('dihubungi lewat surel: <a href="mailto:' . $surel . '">' . $surel . '</a>', $r->body);
        // Navigasi: satu butir; halaman depan menaut ke halaman itu.
        $this->assertStringContainsString('<a href="/tentang" aria-current="page">Tentang &amp; kontak</a>', $r->body);
        $this->assertStringContainsString('<a href="/tentang">Tentang &amp; kontak</a>.', $this->tamu()->get('/')->body);
        foreach (['en' => ['Contact', 'Forgotten password'], 'ar' => ['اتصل بنا', 'نسيت كلمة المرور']] as $b => $judul) {
            $tamu = $this->tamu();
            $tamu->get('/bahasa/' . $b);
            $isi = $tamu->get('/tentang')->body;
            foreach ($judul as $j) {
                $this->assertStringContainsString('<h2>' . $j . '</h2>', $isi, $b);
            }
        }
    }

    public function testTigaBahasa(): void
    {
        $harap = [
            'en' => ['<html lang="en" dir="ltr">', ['/' => 'One account for all your learning applications', '/privasi' => 'Privacy policy', '/ketentuan' => 'Terms of use', '/tentang' => 'About Lokandita']],
            'ar' => ['<html lang="ar" dir="rtl">', ['/' => 'حساب واحد لكل تطبيقاتك التعليمية', '/privasi' => 'سياسة الخصوصية', '/ketentuan' => 'شروط الاستخدام', '/tentang' => 'عن Lokandita']],
        ];
        foreach ($harap as $bahasa => [$html, $halaman]) {
            $tamu = $this->tamu();
            $r    = $tamu->get('/bahasa/' . $bahasa . '?kembali=' . rawurlencode('/privasi'));
            $this->assertSame('https://lokandita.com/privasi', $r->header('Location'));
            foreach ($halaman as $jalur => $tajuk) {
                $r = $tamu->get($jalur);
                $this->assertSame(200, $r->status, "{$bahasa} {$jalur}");
                $this->assertStringContainsString($html, $r->body, "{$bahasa} {$jalur}");
                $this->assertStringContainsString('<h1>' . $tajuk . '</h1>', $r->body, "{$bahasa} {$jalur}");
                $this->assertDoesNotMatchRegularExpression('/\b(Portal|Lokandita)\.[a-zA-Z]+(\.[a-zA-Z0-9]+)*\b(?!\.com)/', strip_tags($r->body), "{$bahasa} {$jalur}: kunci bahasa yang tidak terjemah");
            }
        }
    }

    /**
     * SPEC.md §15: LokaKode aktif; LokaUjian dan LokaSiber bertanda "segera" dan tidak bisa diklik.
     */
    public function testKartuAplikasiDiHalamanDepan(): void
    {
        $r = $this->tamu()->get('/');
        $this->assertStringContainsString('<a class="kartu-aplikasi" href="https://lokakode.lokandita.com/">', $r->body);
        $this->assertSame(1, preg_match_all('/<a class="kartu-aplikasi"/', $r->body));
        $this->assertSame(2, preg_match_all('/<div class="kartu-aplikasi kartu-aplikasi-segera">/', $r->body));
        $this->assertSame(2, preg_match_all('/<span class="lencana lencana-segera">Segera<\/span>/', $r->body));
        foreach (['LokaUjian', 'LokaSiber'] as $nama) {
            $this->assertStringContainsString('<strong>' . $nama . '</strong>', $r->body);
            $this->assertDoesNotMatchRegularExpression('/<a[^>]*>\s*<strong>' . $nama . '/', $r->body, "{$nama} tidak boleh berupa tautan");
        }
        $this->assertDoesNotMatchRegularExpression('/href="[^"]*(lokaujian|lokasiber)/i', $r->body);
    }

    /**
     * Halaman publik tidak membaca sesi: dengan cookie karangan pun isinya sama dan tanpa menu akun.
     * (Halaman depan `/` sengaja lain: pembawa cookie dibawa ke /beranda — ClientAppTest.)
     */
    public function testHalamanPublikTidakMembacaSesi(): void
    {
        $bersih = $this->tamu()->get('/tentang')->body;
        $http   = $this->tamu();
        $http->cookies['lokandita_sid'] = str_repeat('A', 43);
        $r = $http->get('/tentang');
        $this->assertSame(200, $r->status);
        $this->assertSame($bersih, $r->body);

        // Juga bagi sesi yang hidup: halaman publik tetap halaman publik, tanpa nama pengguna.
        $user  = $this->user(['nama' => 'Nama Tidak Boleh Muncul']);
        $masuk = $this->sebagai($user);
        $r     = $masuk->get('/privasi');
        $this->assertSame(200, $r->status);
        $this->assertStringNotContainsString('Nama Tidak Boleh Muncul', $r->body);
        $this->assertStringNotContainsString($user['nomor_induk'], $r->body);
    }

    /**
     * CSP melarang CDN: tidak ada skrip, gaya, gambar atau bingkai dari host lain.
     */
    public function testTidakAdaAsetDariHostLain(): void
    {
        foreach (array_keys(self::HALAMAN) as $jalur) {
            $r = $this->tamu()->get($jalur);
            preg_match_all('#<(?:script|link|img|iframe|source)\b[^>]*?\b(?:src|href)="([^"]+)"#i', $r->body, $m);
            $this->assertNotSame([], $m[1], $jalur);
            foreach ($m[1] as $alamat) {
                $this->assertTrue(
                    (str_starts_with($alamat, '/') && ! str_starts_with($alamat, '//')) || str_starts_with($alamat, 'https://lokandita.com/'),
                    "{$jalur}: aset dari host lain: {$alamat}",
                );
            }
            // srcset (foto pembuka): setiap alamatnya juga dari host sendiri.
            preg_match_all('#\bsrcset="([^"]+)"#i', $r->body, $s);
            foreach ($s[1] as $daftar) {
                foreach (explode(',', $daftar) as $butir) {
                    $alamat = trim(explode(' ', trim($butir))[0]);
                    $this->assertTrue(str_starts_with($alamat, '/') && ! str_starts_with($alamat, '//'), "{$jalur}: srcset dari host lain: {$alamat}");
                }
            }
            $this->assertStringNotContainsString(' style="', $r->body, "{$jalur}: gaya sebaris");
            $this->assertStringNotContainsString('<style', $r->body, $jalur);
            $this->assertDoesNotMatchRegularExpression('/<script(?![^>]*\bsrc=)/', $r->body, "{$jalur}: skrip sebaris");
        }
        foreach (['/aset/lokandita.css', '/aset/lokandita.js', '/favicon.ico', '/merek/icon-192.png'] as $aset) {
            $this->assertSame(200, $this->tamu()->get($aset)->status, $aset);
        }
    }

    public function testPetaSitusDanRobots(): void
    {
        $r = $this->tamu()->get('/sitemap.xml');
        $this->assertSame(200, $r->status);
        $this->assertStringContainsString('xml', (string) $r->header('Content-Type'));
        preg_match_all('#<loc>([^<]+)</loc>#', $r->body, $m);
        $this->assertSame(['https://lokandita.com/', 'https://lokandita.com/tentang', 'https://lokandita.com/privasi', 'https://lokandita.com/ketentuan'], $m[1]);

        $r = $this->tamu()->get('/robots.txt');
        $this->assertSame(200, $r->status);
        $this->assertStringContainsString('text/plain', (string) $r->header('Content-Type'));
        $this->assertStringContainsString("Disallow: /beranda\n", $r->body);
        $this->assertStringContainsString("Disallow: /data-saya\n", $r->body);
        $this->assertStringContainsString('Sitemap: https://lokandita.com/sitemap.xml', $r->body);
        $this->assertStringNotContainsString("Disallow: /\n", $r->body);
    }

    public function testAlamatYangTidakAdaDijawab404(): void
    {
        foreach (['/tidak-ada', '/privasi/lagi', '/admin', '/internal/session/touch'] as $jalur) {
            $this->assertSame(404, $this->tamu()->get($jalur)->status, $jalur);
        }
    }
}
