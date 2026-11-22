<?php
/**
 * Ikon satu kartu aplikasi. Variabel: $ikon (nama berkas di /merek/aplikasi tanpa akhiran, atau
 * null). Ragam terang untuk mode terang, ragam gelap untuk mode gelap; berkas aslinya tidak
 * diwarnai ulang. Ikon itu hiasan: nama aplikasi tertulis di sebelahnya, jadi alt-nya kosong.
 * Tanpa ikon: lingkaran netral seukuran, supaya baris kartu tetap rata.
 */
$versi = substr((string) getenv('LOKANDITA_COMMIT'), 0, 12);
?>
<?php if ($ikon !== null): ?>
    <?php $dasar = '/merek/aplikasi/' . $ikon ?>
    <picture class="kartu-ikon">
        <source media="(prefers-color-scheme: dark)" type="image/webp" srcset="<?= esc($dasar) ?>-gelap.webp?v=<?= esc($versi) ?>">
        <source media="(prefers-color-scheme: dark)" srcset="<?= esc($dasar) ?>-gelap.png?v=<?= esc($versi) ?>">
        <source type="image/webp" srcset="<?= esc($dasar) ?>-terang.webp?v=<?= esc($versi) ?>">
        <img src="<?= esc($dasar) ?>-terang.png?v=<?= esc($versi) ?>" alt="" width="56" height="56" loading="lazy" decoding="async">
    </picture>
<?php else: ?>
    <span class="kartu-ikon kartu-ikon-kosong" aria-hidden="true"></span>
<?php endif ?>
