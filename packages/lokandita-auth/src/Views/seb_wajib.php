<?php
/**
 * Penolakan filter SEB bagi mahasiswa (SPEC.md §13.1). Variabel: $judul, $alasan (kunci Seb.wajib.alasan.*).
 * Kalimat kedua (apa yang tetap tersimpan) milik aplikasi: SebApl.tersimpan.
 */
?>
<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<div class="isi-sempit">
    <div class="kartu" data-seb-wajib="<?= esc($alasan) ?>">
        <h1><?= esc($judul) ?></h1>
        <p><?= lang('Seb.wajib.teks', [\Lokandita\Auth\Aplikasi::nama()]) ?> <?= lang('SebApl.tersimpan') ?></p>
        <p><strong><?= lang('Seb.wajib.alasan.' . $alasan) ?></strong></p>
        <div class="baris-tombol">
            <a class="tombol" href="/seb"><?= lang('Seb.wajib.petunjuk') ?></a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
