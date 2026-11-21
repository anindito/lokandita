<?php

// Dijalankan saat build image Portal (app.Dockerfile, tahap "foto"): membuat berkas foto
// pembuka BAWAAN dari docs/branding/foto/hero-portal.jpg dengan kode yang sama yang
// mengolah foto unggahan (App\Libraries\Gambar), dan gambar Open Graph Portal (potongan
// 1200 × 630 dari foto yang sama, tanpa tulisan).
//
//   php foto-bawaan.php <Gambar.php> <foto sumber> <direktori keluaran>

declare(strict_types=1);

[, $kelas, $sumber, $keluar] = $argv;
require $kelas;

use App\Libraries\Gambar;

ini_set('memory_limit', '512M');
$periksa = Gambar::periksa($sumber);
if (! is_array($periksa)) {
    fwrite(STDERR, "foto bawaan ditolak: {$periksa}\n");
    exit(1);
}
@mkdir($keluar . '/hero', 0755, true);
$ukuran = Gambar::buat($sumber, $keluar . '/hero/hero');
if ($ukuran === null) {
    fwrite(STDERR, "foto bawaan tidak bisa diolah\n");
    exit(1);
}
file_put_contents($keluar . '/hero/hero.json', json_encode($ukuran) . "\n");

// Open Graph: 1200 × 630, dipotong di tengah dari berkas 1600.
$g      = imagecreatefromjpeg($keluar . '/hero/hero-1600.jpg');
$tinggi = (int) round(imagesx($g) * 630 / 1200);
$og     = imagecreatetruecolor(1200, 630);
imagecopyresampled($og, $g, 0, 0, 0, intdiv(imagesy($g) - $tinggi, 2), 1200, 630, imagesx($g), $tinggi);
imagejpeg($og, $keluar . '/og-image.jpg', 82) || exit(1);
echo 'foto bawaan: ', json_encode($ukuran), ', ', count(glob($keluar . '/hero/*')), " berkas\n";
