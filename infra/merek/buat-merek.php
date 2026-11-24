<?php

// Membuat berkas merek TURUNAN ketiga situs dari berkas asli di docs/branding/baru/.
// Dijalankan oleh infra/buat-merek.sh di dalam kontainer tanpa jaringan; tidak dijalankan
// langsung. Berkas asli hanya dibaca. Hasilnya selalu sama untuk masukan yang sama.
//
//   php buat-merek.php <direktori brands/> <direktori keluaran>
//
// Keluaran: <keluaran>/<auth|portal|lokakode|lokaujian>/ = isi direktori public/ aplikasi itu.

declare(strict_types=1);

[, $asal, $keluar] = $argv;

// Situs => merek yang dibawanya (docs/branding/LOKANDITA-BRAND.md): situs identitas dan Portal
// membawa merek induk, LokaKode membawa mereknya sendiri. `nama` tampil di manifest.
// Gambar Open Graph Portal tetap potongan foto pembuka (keputusan pemilik 2026-11-22), dibuat
// saat build image oleh infra/docker/foto-bawaan.php — karena itu `og` Portal false.
const SITUS = [
    'auth'     => ['merek' => 'Lokandita', 'nama' => 'Akun Lokandita', 'singkat' => 'Lokandita', 'og' => true,  'pembuka' => false, 'kartu' => false],
    'portal'   => ['merek' => 'Lokandita', 'nama' => 'Lokandita',      'singkat' => 'Lokandita', 'og' => false, 'pembuka' => false, 'kartu' => true],
    'lokakode' => ['merek' => 'LokaKode',  'nama' => 'LokaKode',       'singkat' => 'LokaKode',  'og' => true,  'pembuka' => true,  'kartu' => false],
    // LokaUjian tidak punya halaman yang boleh diindeks atau dibagikan: tanpa gambar Open Graph, tanpa logo pembuka.
    'lokaujian' => ['merek' => 'LokaUjian', 'nama' => 'LokaUjian',     'singkat' => 'LokaUjian', 'og' => false, 'pembuka' => false, 'kartu' => false],
];
// Warna latar yang dianjurkan README berkas merek. Dipakai hanya di tempat yang TIDAK boleh
// tembus pandang: ikon iOS (iOS mengecat bagian tembus pandang dengan hitam) dan gambar Open Graph.
const LATAR_TERANG = [0xf4, 0xf7, 0xfb];
// Warna kepala halaman (--kartu di lokandita.css): warna bilah peramban di ponsel.
const WARNA_KEPALA = '#ffffff';

const TINGGI_LOGO  = 80;   // logo kepala: tampil 40 px, berkas 2×
const SISI_KARTU   = 112;  // ikon kartu aplikasi Portal: tampil 56 px, berkas 2×
const SISI_PEMBUKA = 704;  // logo pembuka LokaKode: tampil sampai 352 px, berkas 2×
const MUTU_WEBP    = 90;

function gagal(string $pesan): never
{
    fwrite(STDERR, "buat-merek: {$pesan}\n");
    exit(1);
}

function buka(string $berkas): GdImage
{
    $g = is_file($berkas) ? @imagecreatefrompng($berkas) : false;
    if ($g === false) {
        gagal("tidak bisa membuka {$berkas}");
    }
    imagepalettetotruecolor($g);
    imagealphablending($g, false);
    imagesavealpha($g, true);

    return $g;
}

function kanvas(int $l, int $t, ?array $latar = null): GdImage
{
    $g = imagecreatetruecolor($l, $t);
    if ($latar === null) {
        imagealphablending($g, false);
        imagesavealpha($g, true);
        imagefill($g, 0, 0, imagecolorallocatealpha($g, 0, 0, 0, 127));
    } else {
        imagefill($g, 0, 0, imagecolorallocate($g, ...$latar));
    }

    return $g;
}

/** Kotak yang memuat semua piksel yang tidak tembus pandang: [x, y, lebar, tinggi]. */
function kotakIsi(GdImage $g): array
{
    $l = imagesx($g);
    $t = imagesy($g);
    [$x0, $y0, $x1, $y1] = [$l, $t, -1, -1];
    for ($y = 0; $y < $t; $y++) {
        for ($x = 0; $x < $l; $x++) {
            // Alfa GD: 0 = padat, 127 = tembus pandang.
            if (((imagecolorat($g, $x, $y) >> 24) & 0x7f) < 123) {
                $x0 = min($x0, $x);
                $x1 = max($x1, $x);
                $y0 = min($y0, $y);
                $y1 = max($y1, $y);
            }
        }
    }
    if ($x1 < 0) {
        gagal('gambar kosong');
    }

    return [$x0, $y0, $x1 - $x0 + 1, $y1 - $y0 + 1];
}

function ubahUkuran(GdImage $g, array $kotak, int $l, int $t): GdImage
{
    $h = kanvas($l, $t);
    imagecopyresampled($h, $g, 0, 0, $kotak[0], $kotak[1], $l, $t, $kotak[2], $kotak[3]);

    return $h;
}

function tulis(string $berkas, string $isi): void
{
    @mkdir(dirname($berkas), 0755, true);
    if ($isi === '' || file_put_contents($berkas, $isi) === false) {
        gagal("tidak bisa menulis {$berkas}");
    }
}

function png(GdImage $g): string
{
    ob_start();
    imagepng($g, null, 9);

    return (string) ob_get_clean();
}

function webp(GdImage $g): string
{
    ob_start();
    imagewebp($g, null, MUTU_WEBP);

    return (string) ob_get_clean();
}

/** ICO berisi PNG apa adanya (didukung semua peramban sejak lama): 16, 32 dan 48 piksel. */
function ico(array $png): string
{
    $kepala = pack('vvv', 0, 1, count($png));
    $letak  = 6 + 16 * count($png);
    $data   = '';
    foreach ($png as $sisi => $isi) {
        $kepala .= pack('CCCCvvVV', $sisi, $sisi, 0, 0, 1, 32, strlen($isi), $letak + strlen($data));
        $data   .= $isi;
    }

    return $kepala . $data;
}

function asli(string $asal, string $merek, string $jalur): string
{
    $berkas = "{$asal}/{$merek}/{$jalur}";
    $isi    = is_file($berkas) ? file_get_contents($berkas) : false;
    if ($isi === false || $isi === '') {
        gagal("berkas asli tidak ada: {$berkas}");
    }

    return $isi;
}

foreach (SITUS as $situs => $s) {
    $m   = $s['merek'];
    $dir = "{$keluar}/{$situs}";
    $fav = fn (string $ragam, int $sisi): string => asli($asal, $m, "04-favicon/{$m}_{$ragam}_favicon_{$sisi}.png");

    // Ikon: berkas asli disalin apa adanya. 512 berasal dari "logo saja" (berkas merek tidak
    // punya ikon 512). Ragam terang adalah bawaan; ragam gelap 16 dan 32 px untuk tab bermode gelap.
    tulis("{$dir}/favicon.ico", ico([16 => $fav('light', 16), 32 => $fav('light', 32), 48 => $fav('light', 48)]));
    tulis("{$dir}/merek/icon-16.png", $fav('light', 16));
    tulis("{$dir}/merek/icon-16-gelap.png", $fav('dark', 16));
    tulis("{$dir}/merek/icon-32.png", $fav('light', 32));
    tulis("{$dir}/merek/icon-32-gelap.png", $fav('dark', 32));
    tulis("{$dir}/merek/icon-192.png", $fav('light', 192));
    tulis("{$dir}/merek/icon-512.png", asli($asal, $m, "01-logo-only/{$m}_light_square_512.png"));

    // Ikon iOS 180 px: di atas latar padat.
    $ikon = buka("{$asal}/{$m}/04-favicon/{$m}_light_favicon_180.png");
    $apel = kanvas(180, 180, LATAR_TERANG);
    imagecopy($apel, $ikon, 0, 0, 0, 0, 180, 180);
    tulis("{$dir}/merek/icon-180.png", png($apel));

    tulis("{$dir}/merek/site.webmanifest", json_encode([
        'name'             => $s['nama'],
        'short_name'       => $s['singkat'],
        'start_url'        => '/',
        'display'          => 'browser',
        'background_color' => sprintf('#%02x%02x%02x', ...LATAR_TERANG),
        'theme_color'      => WARNA_KEPALA,
        'icons'            => [
            ['src' => '/merek/icon-192.png', 'sizes' => '192x192', 'type' => 'image/png'],
            ['src' => '/merek/icon-512.png', 'sizes' => '512x512', 'type' => 'image/png'],
        ],
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

    // Logo kepala: "nama di kanan", tepi tembus pandangnya dipotong, tinggi 80 px.
    $ukuranLogo = [];
    foreach (['light' => 'terang', 'dark' => 'gelap'] as $ragam => $nama) {
        $g     = buka("{$asal}/{$m}/03-logo-name-right/{$m}_{$ragam}_landscape_1600x520.png");
        $kotak = kotakIsi($g);
        // Lebar genap, supaya ukuran tampilnya (separuh berkas) bilangan bulat.
        $lebar = 2 * (int) round($kotak[2] * TINGGI_LOGO / $kotak[3] / 2);
        $h     = ubahUkuran($g, $kotak, $lebar, TINGGI_LOGO);
        tulis("{$dir}/merek/logo-{$nama}.png", png($h));
        tulis("{$dir}/merek/logo-{$nama}.webp", webp($h));
        $ukuranLogo[$nama] = [$lebar, TINGGI_LOGO];

        if ($ragam === 'light' && $s['og']) {
            // Open Graph 1200 × 630: logo yang sama, di tengah latar padat, selebar 760 px.
            $og = kanvas(1200, 630, LATAR_TERANG);
            $t  = (int) round($kotak[3] * 760 / $kotak[2]);
            imagecopyresampled($og, $g, 220, intdiv(630 - $t, 2), $kotak[0], $kotak[1], 760, $t, $kotak[2], $kotak[3]);
            tulis("{$dir}/merek/og-image.png", png($og));
        }
    }
    if ($ukuranLogo['terang'] !== $ukuranLogo['gelap']) {
        gagal("logo terang dan gelap {$m} berbeda ukuran");
    }
    // Ukuran tampil (separuh ukuran berkas) dibaca tata letak bersama untuk width/height.
    tulis("{$dir}/merek/logo.json", json_encode(['lebar' => intdiv($ukuranLogo['terang'][0], 2), 'tinggi' => intdiv(TINGGI_LOGO, 2)]) . "\n");

    if ($s['pembuka']) {
        foreach (['light' => 'terang', 'dark' => 'gelap'] as $ragam => $nama) {
            $g = buka("{$asal}/{$m}/02-logo-name-below/{$m}_{$ragam}_square_1024.png");
            $h = ubahUkuran($g, [0, 0, 1024, 1024], SISI_PEMBUKA, SISI_PEMBUKA);
            tulis("{$dir}/merek/pembuka-{$nama}.png", png($h));
            tulis("{$dir}/merek/pembuka-{$nama}.webp", webp($h));
        }
    }

    if ($s['kartu']) {
        // Satu ikon kartu untuk setiap merek aplikasi yang punya "logo saja"; namanya kode
        // aplikasi (nama merek dalam huruf kecil), yang dirujuk Config\Aplikasi Portal.
        foreach (glob("{$asal}/*", GLOB_ONLYDIR) as $d) {
            $merek = basename($d);
            if ($merek === 'Lokandita' || preg_match('/^[A-Za-z]+$/', $merek) !== 1) {
                continue;
            }
            foreach (['light' => 'terang', 'dark' => 'gelap'] as $ragam => $nama) {
                $berkas = "{$d}/01-logo-only/{$merek}_{$ragam}_square_512.png";
                if (! is_file($berkas)) {
                    continue 2;
                }
                $g = buka($berkas);
                $h = ubahUkuran($g, kotakIsi($g), SISI_KARTU, SISI_KARTU);
                $k = strtolower($merek);
                tulis("{$dir}/merek/aplikasi/{$k}-{$nama}.png", png($h));
                tulis("{$dir}/merek/aplikasi/{$k}-{$nama}.webp", webp($h));
            }
        }
    }
}
echo "buat-merek: selesai\n";
