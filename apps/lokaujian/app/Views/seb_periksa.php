<?php
/**
 * Apakah kedua header SEB tiba pada permintaan ini. Variabel: $konfig, $peramban (bool),
 * $diberlakukan. Tidak pernah menampilkan kunci, dan tidak menyebut ujian mana pun.
 */
?>
<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<div class="isi-sempit">
    <h1><?= lang('SebUjian.periksa.judul') ?></h1>
    <div class="kartu" data-seb-periksa="<?= $konfig && $peramban ? 'ada_header' : 'tanpa_header' ?>">
        <p data-konfig="<?= $konfig ? '1' : '0' ?>"><?= lang($konfig ? 'SebUjian.periksa.konfigAda' : 'SebUjian.periksa.konfigTidak') ?></p>
        <p data-peramban="<?= $peramban ? '1' : '0' ?>"><?= lang($peramban ? 'SebUjian.periksa.perambanAda' : 'SebUjian.periksa.perambanTidak') ?></p>
        <p class="petunjuk"><?= lang('SebUjian.periksa.info') ?></p>
    </div>
    <p><a href="/seb"><?= lang('SebUjian.wajib.petunjuk') ?></a></p>
</div>
<?= $this->endSection() ?>
