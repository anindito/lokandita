<?php

declare(strict_types=1);

namespace Tests;

use Lokandita\Auth\Testing\Http;
use PHPUnit\Framework\TestCase;

/**
 * Kerangka LokaUjian lewat HTTP sungguhan ke lokaujian-tes: ia menjawab sebagai dirinya dan
 * tidak membuka apa pun selain yang disebut KONTRAK.md §4. Jalan masuknya (login, menu akun,
 * halaman depan) diuji ClientAppTest dari paket bersama; lingkupnya di tests/Policy/.
 */
final class KerangkaTest extends TestCase
{
    private function tamu(): Http
    {
        return new Http((string) getenv('APP_TES_URL'));
    }

    public function testHealthzMenyebutAplikasiDanCommit(): void
    {
        $r = $this->tamu()->get('/healthz');
        $this->assertSame(200, $r->status);
        $this->assertSame(['app' => 'lokaujian', 'commit' => 'tes'], json_decode($r->body, true));
        $this->assertStringContainsString('no-store', (string) $r->header('Cache-Control'));
        // Header keamanan aplikasi dipasang filter `keamanan` pada setiap jawaban yang lewat rute.
        $this->assertStringContainsString("default-src 'self'", (string) $r->header('Content-Security-Policy'));
        $this->assertSame('nosniff', $r->header('X-Content-Type-Options'));
    }

    public function testRobotsMelarangSemuanya(): void
    {
        $r = $this->tamu()->get('/robots.txt');
        $this->assertSame(200, $r->status);
        $this->assertSame("User-agent: *\nDisallow: /\n", $r->body);
    }

    public function testPilihanBahasaDisimpanDanKembaliHanyaKeJalurLokal(): void
    {
        $r = $this->tamu()->get('/bahasa/ar?kembali=https://evil.example/');
        $this->assertContains($r->status, [302, 303]);
        $this->assertStringNotContainsString('evil.example', (string) $r->header('Location'));
        // Kode bahasa yang tidak dikenal tidak mengubah apa pun; pengalihannya tetap lokal.
        $r = $this->tamu()->get('/bahasa/xx');
        $this->assertContains($r->status, [302, 303]);
        $this->assertStringStartsWith('/', (string) parse_url((string) $r->header('Location'), PHP_URL_PATH));
    }

    /**
     * Yang belum dibangun tetap 404: ekspor data sendiri (U5), SEB (U4), dan API internal, yang
     * hanya milik LokaAuth. Rute akun menolak POST tanpa token CSRF.
     */
    public function testYangBelumDibangunTetapTidakAda(): void
    {
        foreach (['/data-saya', '/seb', '/seb/periksa', '/mata-kuliah/1/seb', '/internal/session/touch'] as $jalur) {
            $r = $this->tamu()->get($jalur);
            $this->assertSame(404, $r->status, $jalur);
        }
        $this->assertContains($this->tamu()->post('/api/seb/denyut', [])->status, [403, 404]);
        foreach (['/keluar', '/sesi/perpanjang', '/preferensi'] as $jalur) {
            $this->assertSame(403, $this->tamu()->post($jalur, [])->status, $jalur);
        }
    }

    /**
     * Seluruh LokaUjian tidak untuk diindeks, juga halaman depannya yang tanpa sesi.
     */
    public function testHalamanDepanPublikTetapiTidakDiindeks(): void
    {
        $r = $this->tamu()->get('/');
        $this->assertSame(200, $r->status);
        $this->assertStringContainsString('<meta name="robots" content="noindex, nofollow">', $r->body);
        $this->assertStringNotContainsString('og:', $r->body);
        $this->assertStringContainsString('href="/beranda"', $r->body);
        $this->assertStringContainsString('Safe Exam Browser', $r->body);
    }
}
