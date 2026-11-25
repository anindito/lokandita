<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<?php
/**
 * Halaman sebelum unduhan ekspor (SPEC-LOKAUJIAN.md §6.3): menyatakan bahwa berkas memuat semua
 * kunci jawaban. Variabel: $matkul, $ujian.
 */
$ini = '/mata-kuliah/' . (int) $matkul['id'] . '/ujian/' . (int) $ujian['id'];
?>
<?= view('_nav', ['akses' => $akses]) ?>
<p><a href="<?= $ini ?>"><span class="panah-balik" aria-hidden="true"></span> <span dir="auto"><?= esc($ujian['title']) ?></span></a></p>
<h1><?= lang('Bank.ujian.ekspor') ?></h1>
<div class="kartu">
    <p><strong><?= lang('Bank.ekspor.kunci') ?></strong></p>
    <p><?= lang('Bank.ekspor.isi') ?></p>
    <p class="petunjuk"><?= lang('Bank.ekspor.audit') ?></p>
    <form method="post" action="<?= $ini ?>/ekspor">
        <?= csrf_field() ?>
        <div class="baris-tombol">
            <button type="submit" class="tombol"><?= lang('Bank.ekspor.unduh') ?></button>
            <a href="<?= $ini ?>"><?= lang('Bank.batal') ?></a>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
