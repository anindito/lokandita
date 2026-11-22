<?php

declare(strict_types=1);

namespace Tests\Login;

use Lokandita\Auth\Testing\MerekTes;
use Tests\Support\AuthCase;

/**
 * Situs identitas membawa merek induk Lokandita dan tampil sebagai "Akun Lokandita"
 * (docs/branding/LOKANDITA-BRAND.md). Nama lama "LokaAuth" tidak tampil lagi ke pengguna.
 */
final class MerekTest extends AuthCase
{
    use MerekTes;

    private const CSP = "default-src 'self'; script-src 'self' 'nonce-N'; style-src 'self'; img-src 'self' data:; font-src 'self'; connect-src 'self'; object-src 'none'; base-uri 'self'; frame-ancestors 'none'; form-action 'self' https://auth.lokandita.com https://lokandita.com https://www.lokandita.com https://lokakode.lokandita.com";

    public function testIkonLogoDanManifestSitusIdentitas(): void
    {
        $this->periksaMerek($this->http(), '/login', 'Akun Lokandita', '/merek/og-image.png', self::CSP);
    }

    public function testHalamanLoginTidakMenyebutLokaAuthDalamTigaBahasa(): void
    {
        foreach (['id', 'en', 'ar'] as $bahasa) {
            $http = $this->http();
            $http->get('/bahasa/' . $bahasa . '?kembali=/login');
            $r = $http->get('/login');
            $this->assertSame(200, $r->status, $bahasa);
            $this->assertStringContainsString('<html lang="' . $bahasa . '"', $r->body);
            $this->assertStringNotContainsStringIgnoringCase('LokaAuth', $r->body, $bahasa);
            $this->assertMatchesRegularExpression('#<title>[^<]+ — Akun Lokandita</title>#', $r->body, $bahasa);
        }
    }

    /** Tidak satu pun teks antarmuka situs identitas, dalam bahasa apa pun, memuat nama lama. */
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

    /** Pendaftaran TOTP baru bernama "Lokandita" di aplikasi autentikator. */
    public function testNamaDiAplikasiAutentikator(): void
    {
        $uri = \App\Libraries\Mfa::provisioningUri('JBSWY3DPEHPK3PXP', '1234567890');
        $this->assertStringContainsString('issuer=Lokandita', $uri);
        $this->assertStringNotContainsString('LokaAuth', $uri);
    }
}
