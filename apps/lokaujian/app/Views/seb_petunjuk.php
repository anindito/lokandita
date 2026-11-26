<?php
/**
 * Petunjuk Safe Exam Browser LokaUjian, terbuka tanpa sesi. Tidak menyebut satu ujian pun:
 * berkas tiap ujian diunduh mahasiswa dari berandanya. Variabel: $unduhSeb, $diberlakukan.
 */
use Lokandita\Auth\Ikon;
?>
<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<div class="isi-sempit">
    <h1><?= lang('SebUjian.petunjuk.judul') ?></h1>
    <div class="kartu"><p data-seb-keadaan="<?= $diberlakukan ? 'berlaku' : 'belum' ?>"><?= lang($diberlakukan ? 'SebUjian.petunjuk.berlaku' : 'SebUjian.petunjuk.belum') ?></p></div>

    <h2><?= lang('SebUjian.petunjuk.batasJudul') ?></h2>
    <p><?= lang('SebUjian.petunjuk.batas') ?></p>
    <p><a class="tautan-ikon" href="<?= esc($unduhSeb) ?>" rel="noopener noreferrer external"><?= Ikon::svg('external-link', '', true) ?><span><?= lang('SebUjian.petunjuk.unduhSeb') ?></span></a></p>

    <h2><?= lang('SebUjian.petunjuk.langkahJudul') ?></h2>
    <ol>
        <?php foreach (lang('SebUjian.petunjuk.langkah') as $l): ?><li><?= esc($l) ?></li><?php endforeach ?>
    </ol>

    <h2><?= lang('SebUjian.petunjuk.selamaJudul') ?></h2>
    <ul>
        <?php foreach (lang('SebUjian.petunjuk.selama') as $l): ?><li><?= esc($l) ?></li><?php endforeach ?>
    </ul>
    <p><a href="/seb/periksa" rel="nofollow"><?= lang('SebUjian.petunjuk.periksa') ?></a></p>
</div>
<?= $this->endSection() ?>
