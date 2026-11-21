<?php

declare(strict_types=1);

namespace Tests\Publik;

use Tests\Support\KodeCase;

/**
 * Halaman depan publik LokaKode (SPEC.md §15), lewat HTTP sungguhan ke lokakode-tes.
 * Bahwa pembawa cookie dialihkan ke /beranda dan pilihan bahasa bekerja diuji
 * ClientAppTest di paket bersama; bahwa halaman ini tidak memuat data diuji MatriksAksesTest.
 */
final class DepanTest extends KodeCase
{
    public function testHalamanDepanPublikDalamTigaBahasa(): void
    {
        $tamu = $this->tamu();
        $r    = $tamu->get('/');
        $this->assertSame(200, $r->status);
        $this->assertStringContainsString('<html lang="id" dir="ltr">', $r->body);
        $this->assertStringContainsString('<meta name="robots" content="index, follow">', $r->body);
        $this->assertStringContainsString('<meta property="og:image" content="https://lokakode.lokandita.com/merek/og-image.png">', $r->body);
        $this->assertStringContainsString('Latihan pemrograman yang langsung dinilai', $r->body);
        foreach (['C (GCC 13', 'C++ (GCC 13', 'Python 3.12'] as $bahasa) {
            $this->assertStringContainsString($bahasa, $r->body);
        }
        // Tanpa sesi tidak ada menu akun, dan tidak ada cookie sesi yang dipasang.
        $this->assertStringNotContainsString('data-menu-akun', $r->body);
        $this->assertArrayNotHasKey('lokandita_sid', $tamu->cookies);
        // Alamat kontak dari CONTACT_EMAIL; tautan ke Portal untuk privasi dan ketentuan.
        $this->assertStringContainsString('mailto:' . getenv('CONTACT_EMAIL'), $r->body);
        $this->assertStringContainsString('href="https://lokandita.com/privasi"', $r->body);

        $tamu->get('/bahasa/en?kembali=/');
        $this->assertStringContainsString('Programming practice that is judged at once', $tamu->get('/')->body);
        $tamu->get('/bahasa/ar?kembali=/');
        $ar = $tamu->get('/')->body;
        $this->assertStringContainsString('<html lang="ar" dir="rtl">', $ar);
        $this->assertStringContainsString('تدريب على البرمجة يُقيَّم فورًا', $ar);
    }

    /**
     * Keputusan pemilik 2026-11-21: SEB diumumkan dengan tautan ke situs resminya; berkas .seb
     * dan petunjuk menyusul di tahap 10.
     */
    public function testSebDiumumkanDenganTautanResmiTanpaBerkasKonfigurasi(): void
    {
        $r = $this->tamu()->get('/');
        $this->assertStringContainsString('3.10.x', $r->body);
        $this->assertStringContainsString('belum diberlakukan', $r->body);
        $this->assertStringContainsString('href="https://safeexambrowser.org/download_en.html"', $r->body);
        $this->assertStringNotContainsString('.seb"', $r->body);
    }

    /**
     * CSP melarang CDN: halaman depan tidak memuat apa pun dari host lain. Tautan <a> ke luar boleh.
     */
    public function testTidakAdaAsetDariHostLain(): void
    {
        $r = $this->tamu()->get('/');
        preg_match_all('#<(?:script|link|img|iframe|source)\b[^>]*?\b(?:src|href)="([^"]+)"#i', $r->body, $m);
        $this->assertNotSame([], $m[1]);
        foreach ($m[1] as $alamat) {
            $this->assertTrue(
                str_starts_with($alamat, '/') && ! str_starts_with($alamat, '//') || str_starts_with($alamat, 'https://lokakode.lokandita.com/'),
                "aset dari host lain: {$alamat}",
            );
        }
        $this->assertSame(200, $this->tamu()->get('/merek/og-image.png')->status);
        $robot = $this->tamu()->get('/robots.txt');
        $this->assertSame(200, $robot->status);
        $this->assertStringContainsString('Disallow: /', $robot->body);
    }

    public function testBerandaTetapDiBelakangSesi(): void
    {
        $r = $this->tamu()->get('/beranda');
        $this->assertSame('https://auth.lokandita.com/login?next=' . rawurlencode('https://lokakode.lokandita.com/beranda'), $r->header('Location'));
    }
}
