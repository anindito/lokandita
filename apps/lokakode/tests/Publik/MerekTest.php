<?php

declare(strict_types=1);

namespace Tests\Publik;

use Lokandita\Auth\Testing\MerekTes;
use Tests\Support\KodeCase;

/**
 * LokaKode membawa mereknya sendiri (docs/branding/LOKANDITA-BRAND.md).
 */
final class MerekTest extends KodeCase
{
    use MerekTes;

    private const CSP = "default-src 'self'; script-src 'self' 'nonce-N'; style-src 'self'; img-src 'self' data:; font-src 'self'; connect-src 'self'; object-src 'none'; base-uri 'self'; frame-ancestors 'none'; form-action 'self' https://lokakode.lokandita.com https://lokandita.com https://auth.lokandita.com";

    public function testIkonLogoDanManifestLokaKode(): void
    {
        $this->periksaMerek($this->tamu(), '/', 'LokaKode', '/merek/og-image.png', self::CSP);
    }

    /** Logo pembuka: ragam terang dan gelap, WebP dengan cadangan PNG, 704 px untuk tampil 352 px. */
    public function testLogoPembuka(): void
    {
        $r = $this->tamu()->get('/');
        $this->assertSame(1, preg_match('#<picture class="pembuka-logo">(.*?)</picture>#s', $r->body, $m));
        $this->assertSame(2, preg_match_all('#<source media="\(prefers-color-scheme: dark\)"[^>]*srcset="/merek/pembuka-gelap\.(webp|png)\?v=#', $m[1]));
        $this->assertMatchesRegularExpression('#<img src="/merek/pembuka-terang\.png\?v=[^"]*" width="352" height="352" alt="Logo LokaKode"#', $m[1]);
        foreach (['terang', 'gelap'] as $ragam) {
            foreach (['png' => 'image/png', 'webp' => 'image/webp'] as $akhiran => $tipe) {
                $b = $this->tamu()->get("/merek/pembuka-{$ragam}.{$akhiran}");
                $this->assertSame(200, $b->status);
                $this->assertSame($tipe, $b->header('Content-Type'));
                $this->assertSame([704, 704], array_slice(getimagesizefromstring($b->body), 0, 2));
            }
        }
        foreach (['/merek/logo.png', '/merek/logo.webp', '/merek/apple-touch-icon.png'] as $lama) {
            $this->assertSame(404, $this->tamu()->get($lama)->status, "berkas merek lama masih disajikan: {$lama}");
        }
    }

    /** Halaman staf menyebut "Akun Lokandita"; nama lama tidak ada di teks antarmuka mana pun. */
    public function testBerkasBahasaTidakMemuatLokaAuth(): void
    {
        $berkas = glob(APPPATH . 'Language/*/*.php');
        $this->assertNotSame([], $berkas);
        foreach ($berkas as $b) {
            $isi = require $b;
            array_walk_recursive($isi, function ($teks) use ($b): void {
                $this->assertStringNotContainsStringIgnoringCase('LokaAuth', (string) $teks, $b);
            });
        }
    }
}
