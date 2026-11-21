<?php
/**
 * Foto pembuka sebagai <picture>: AVIF, WebP, lalu JPEG; beberapa lebar; ukuran tertulis
 * supaya halaman tidak bergeser saat foto tiba. Variabel: $foto (App\Libraries\Foto).
 */
$lebar  = \App\Libraries\Gambar::LEBAR;
$daftar = static fn (string $format): string => implode(', ', array_map(
    static fn (int $l): string => esc($foto['dasar'] . '-' . $l . '.' . $format . $foto['versi']) . ' ' . $l . 'w',
    $lebar,
));
// Di layar lebar foto mengisi separuh kolom isi; di layar sempit selebar layar dikurangi tepi.
$ukuran = '(min-width: 56rem) 34rem, calc(100vw - 2.5rem)';
?>
<picture class="pembuka-foto">
    <source type="image/avif" srcset="<?= $daftar('avif') ?>" sizes="<?= $ukuran ?>">
    <source type="image/webp" srcset="<?= $daftar('webp') ?>" sizes="<?= $ukuran ?>">
    <img src="<?= esc($foto['dasar'] . '-1200.jpg' . $foto['versi']) ?>" srcset="<?= $daftar('jpg') ?>" sizes="<?= $ukuran ?>"
         width="<?= (int) $foto['lebar'] ?>" height="<?= (int) $foto['tinggi'] ?>" alt="<?= esc($foto['alt']) ?>" fetchpriority="high" decoding="async">
</picture>
