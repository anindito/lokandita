<?php
/**
 * Halaman teks publik (tentang, kontak, privasi, ketentuan). Isinya dari berkas bahasa:
 * $halaman = ['judul', 'deskripsi', 'diperbarui' (opsional), 'pembuka' => list paragraf,
 * 'bagian' => list of ['judul', 'isi' => list paragraf, 'butir' => list (opsional)]].
 * {email} di dalam teks diganti alamat kontak dari CONTACT_EMAIL.
 */
$isi = static fn (string $t): string => str_replace(
    '{email}',
    '<a href="mailto:' . esc($kontak) . '">' . esc($kontak) . '</a>',
    esc($t),
);
?>
<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<article class="prosa">
    <h1><?= esc($halaman['judul']) ?></h1>
    <?php if (($halaman['diperbarui'] ?? '') !== ''): ?>
        <p class="redup"><?= esc($halaman['diperbarui']) ?></p>
    <?php endif ?>
    <?php foreach ($halaman['pembuka'] as $p): ?>
        <p><?= $isi($p) ?></p>
    <?php endforeach ?>
    <?php foreach ($halaman['bagian'] as $b): ?>
        <h2><?= esc($b['judul']) ?></h2>
        <?php foreach ($b['isi'] as $p): ?>
            <p><?= $isi($p) ?></p>
        <?php endforeach ?>
        <?php if (($b['butir'] ?? []) !== []): ?>
            <ul>
                <?php foreach ($b['butir'] as $butir): ?>
                    <li><?= $isi($butir) ?></li>
                <?php endforeach ?>
            </ul>
        <?php endif ?>
    <?php endforeach ?>
</article>
<?= $this->endSection() ?>
