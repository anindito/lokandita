<?php

declare(strict_types=1);

namespace Tests;

use Lokandita\Auth\Testing\Http;
use PHPUnit\Framework\TestCase;

/**
 * Tahap U0: kerangka LokaUjian lewat HTTP sungguhan ke lokaujian-tes. Aplikasinya belum punya
 * halaman; yang dibuktikan di sini adalah bahwa ia menjawab sebagai dirinya dan tidak
 * membuka apa pun selain yang disebut KONTRAK.md §4.
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
     * Belum ada halaman: tidak ada beranda, tidak ada rute akun, tidak ada rute SEB, tidak ada
     * API internal. Semuanya 404.
     */
    public function testBelumAdaHalamanLain(): void
    {
        foreach (['/', '/beranda', '/data-saya', '/seb', '/seb/periksa', '/mata-kuliah/1/seb', '/internal/session/touch'] as $jalur) {
            $r = $this->tamu()->get($jalur);
            $this->assertSame(404, $r->status, $jalur);
        }
        foreach (['/keluar', '/sesi/perpanjang', '/preferensi', '/api/seb/denyut'] as $jalur) {
            $this->assertContains($this->tamu()->post($jalur, [])->status, [403, 404], $jalur);
        }
    }
}
