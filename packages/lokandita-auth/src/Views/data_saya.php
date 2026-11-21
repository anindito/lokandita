<?php
/**
 * Halaman "Data saya" (KONTRAK.md §14) — sama di LokaAuth dan LokaKode.
 * Variabel: $judul, $butir (list teks: apa saja isi berkas aplikasi ini), $pesan (opsional).
 */
$portal = rtrim((string) getenv('PORTAL_URL'), '/');
?>
<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<article class="prosa">
    <h1><?= esc($judul) ?></h1>
    <p><?= lang('Lokandita.dataSaya.penjelasan', [esc(\Lokandita\Auth\Aplikasi::nama())]) ?></p>
    <ul>
        <?php foreach ($butir as $b): ?>
            <li><?= esc($b) ?></li>
        <?php endforeach ?>
    </ul>
    <p class="petunjuk"><?= lang('Lokandita.dataSaya.tidakTermasuk') ?></p>
    <form method="post" action="/data-saya/unduh">
        <?= csrf_field() ?>
        <button type="submit" class="tombol"><?= lang('Lokandita.dataSaya.unduh') ?></button>
    </form>
    <p class="petunjuk"><?= lang('Lokandita.dataSaya.catatan') ?></p>
    <?php if ($portal !== ''): ?>
        <p><a href="<?= esc($portal) ?>/data-saya"><?= lang('Lokandita.dataSaya.aplikasiLain') ?></a></p>
    <?php endif ?>
</article>
<?= $this->endSection() ?>
