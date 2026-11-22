<?php

declare(strict_types=1);

namespace Lokandita\Auth\Testing;

/**
 * Pemeriksaan merek satu situs lewat HTTP sungguhan (KONTRAK.md §16 "Brand files"): tag di
 * <head>, logo kepala, dan setiap berkas turunan infra/buat-merek.sh — dijawab, bertipe benar,
 * berukuran piksel benar. Dipakai tes ketiga aplikasi supaya ketiganya diukur dengan cara sama.
 */
trait MerekTes
{
    /**
     * @param Http    $tamu    klien tanpa sesi ke aplikasi yang diuji
     * @param string  $halaman halaman yang terbuka tanpa sesi
     * @param string  $nama    nama situs: alt logo kepala dan `name` di manifest
     * @param ?string $og      jalur gambar Open Graph situs ini, atau null bila bukan dari berkas merek
     * @param string  $csp     isi header CSP yang diharapkan, dengan nonce ditulis sebagai N
     */
    protected function periksaMerek(Http $tamu, string $halaman, string $nama, ?string $og, string $csp): void
    {
        $r = $tamu->get($halaman);
        $this->assertSame(200, $r->status, $halaman);

        // CSP tidak berubah karena merek: sama persis dengan sebelum 2026-11-22, kecuali nonce.
        $this->assertSame($csp, preg_replace("/'nonce-[A-Za-z0-9_-]+'/", "'nonce-N'", (string) $r->header('Content-Security-Policy')));

        // <head>: ikon terang dan gelap, ikon iOS, manifest, warna bilah peramban.
        $v = '\?v=[0-9a-z]*';
        foreach ([
            '#<link rel="icon" href="/favicon\.ico" sizes="16x16 32x32 48x48">#',
            '#<link rel="icon" type="image/png" sizes="16x16" href="/merek/icon-16\.png' . $v . '" media="\(prefers-color-scheme: light\)">#',
            '#<link rel="icon" type="image/png" sizes="32x32" href="/merek/icon-32\.png' . $v . '" media="\(prefers-color-scheme: light\)">#',
            '#<link rel="icon" type="image/png" sizes="16x16" href="/merek/icon-16-gelap\.png' . $v . '" media="\(prefers-color-scheme: dark\)">#',
            '#<link rel="icon" type="image/png" sizes="32x32" href="/merek/icon-32-gelap\.png' . $v . '" media="\(prefers-color-scheme: dark\)">#',
            '#<link rel="apple-touch-icon" sizes="180x180" href="/merek/icon-180\.png' . $v . '">#',
            '#<link rel="manifest" href="/merek/site\.webmanifest' . $v . '">#',
            '#<meta name="theme-color" content="\#ffffff" media="\(prefers-color-scheme: light\)">#',
            '#<meta name="theme-color" content="\#191d33" media="\(prefers-color-scheme: dark\)">#',
        ] as $pola) {
            $this->assertMatchesRegularExpression($pola, $r->body);
        }

        // Logo kepala: satu <picture>, ragam gelap dan terang, WebP dengan cadangan PNG, ukuran tertulis.
        $this->assertSame(1, preg_match('#<a class="merek" href="/">\s*<picture>(.*?)</picture>\s*</a>#s', $r->body, $m), 'logo kepala');
        $this->assertSame(2, preg_match_all('#<source media="\(prefers-color-scheme: dark\)"[^>]*srcset="/merek/logo-gelap\.(webp|png)' . $v . '"#', $m[1]));
        $this->assertSame(1, preg_match('#<source type="image/webp" srcset="/merek/logo-terang\.webp' . $v . '">#', $m[1]));
        $this->assertSame(1, preg_match('#<img src="/merek/logo-terang\.png' . $v . '" alt="' . preg_quote($nama, '#') . '" width="(\d+)" height="(\d+)">#', $m[1], $u), 'img logo kepala');
        $this->assertSame(40, (int) $u[2]);

        // Berkas: tipe dan ukuran piksel. Logo kepala 2× ukuran tampilnya.
        $berkas = [
            '/merek/icon-16.png'       => ['image/png', 16, 16],
            '/merek/icon-16-gelap.png' => ['image/png', 16, 16],
            '/merek/icon-32.png'       => ['image/png', 32, 32],
            '/merek/icon-32-gelap.png' => ['image/png', 32, 32],
            '/merek/icon-180.png'      => ['image/png', 180, 180],
            '/merek/icon-192.png'      => ['image/png', 192, 192],
            '/merek/icon-512.png'      => ['image/png', 512, 512],
        ];
        foreach (['terang', 'gelap'] as $ragam) {
            $berkas["/merek/logo-{$ragam}.png"]  = ['image/png', 2 * (int) $u[1], 80];
            $berkas["/merek/logo-{$ragam}.webp"] = ['image/webp', 2 * (int) $u[1], 80];
        }
        if ($og !== null) {
            $berkas[$og] = ['image/png', 1200, 630];
            $this->assertFileTypeOpaque($tamu->get($og)->body, $og);
        }
        foreach ($berkas as $jalur => [$tipe, $lebar, $tinggi]) {
            $b = $tamu->get($jalur);
            $this->assertSame(200, $b->status, $jalur);
            $this->assertSame($tipe, $b->header('Content-Type'), $jalur);
            $ukuran = getimagesizefromstring($b->body);
            $this->assertNotFalse($ukuran, $jalur);
            $this->assertSame([$lebar, $tinggi], [$ukuran[0], $ukuran[1]], $jalur);
            $this->assertSame($tipe, $ukuran['mime'], $jalur);
        }
        // Ikon iOS tidak boleh tembus pandang (iOS mengecatnya hitam).
        $this->assertFileTypeOpaque($tamu->get('/merek/icon-180.png')->body, 'icon-180');

        // favicon.ico: tiga gambar — 16, 32 dan 48 piksel.
        $ico = $tamu->get('/favicon.ico');
        $this->assertSame(200, $ico->status);
        $this->assertSame([0, 1, 3], array_values(unpack('v3', $ico->body)), 'kepala ICO');
        $sisi = [];
        for ($i = 0; $i < 3; $i++) {
            $sisi[] = ord($ico->body[6 + 16 * $i]);
        }
        $this->assertSame([16, 32, 48], $sisi);

        // Manifest: tipe yang benar, nama situs, dan ikon 192 serta 512 yang memang ada.
        $man = $tamu->get('/merek/site.webmanifest');
        $this->assertSame(200, $man->status);
        $this->assertSame('application/manifest+json', $man->header('Content-Type'));
        $isi = json_decode($man->body, true);
        $this->assertSame($nama, $isi['name'] ?? null);
        $this->assertSame(['192x192', '512x512'], array_column($isi['icons'] ?? [], 'sizes'));
        foreach ($isi['icons'] as $ikon) {
            $this->assertArrayHasKey($ikon['src'], $berkas, 'ikon manifest harus berkas yang sudah diperiksa di atas');
        }
    }

    /** PNG tanpa saluran alfa: jenis warna (byte ke-26) adalah 2, RGB. */
    private function assertFileTypeOpaque(string $png, string $apa): void
    {
        $this->assertSame("\x89PNG", substr($png, 0, 4), $apa);
        $this->assertSame(2, ord($png[25]), "{$apa} harus PNG RGB tanpa alfa");
    }
}
