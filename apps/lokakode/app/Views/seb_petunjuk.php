<?php
/**
 * Petunjuk SEB, publik (SPEC.md §13.1). Variabel: $matkul (list of code, name — hanya yang punya
 * konfigurasi), $unduhSeb (alamat situs resmi), $diberlakukan (bool).
 */
use Lokandita\Auth\Ikon;
?>
<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<div class="isi-sempit">
    <h1><?= lang('Seb.petunjuk.judul') ?></h1>
    <div class="kartu"><p data-seb-keadaan="<?= $diberlakukan ? 'berlaku' : 'belum' ?>"><?= lang($diberlakukan ? 'Seb.petunjuk.berlaku' : 'Seb.petunjuk.belum') ?></p></div>

    <h2><?= lang('Seb.petunjuk.batasJudul') ?></h2>
    <p><?= lang('Seb.petunjuk.batas') ?></p>
    <p><a class="tautan-ikon" href="<?= esc($unduhSeb) ?>" rel="noopener noreferrer external"><?= Ikon::svg('external-link', '', true) ?><span><?= lang('Seb.petunjuk.unduhSeb') ?></span></a></p>

    <h2><?= lang('Seb.petunjuk.langkahJudul') ?></h2>
    <ol>
        <?php foreach (lang('Seb.petunjuk.langkah') as $l): ?>
            <li><?= esc($l) ?></li>
        <?php endforeach ?>
    </ol>

    <h2 id="berkas"><?= lang('Seb.petunjuk.berkasJudul') ?></h2>
    <?php if ($matkul === []): ?>
        <div class="kartu"><p><?= lang('Seb.petunjuk.berkasKosong') ?></p></div>
    <?php else: ?>
        <ul>
            <?php foreach ($matkul as $m): ?>
                <li><a href="/seb/unduh/<?= esc($m['code']) ?>.seb" rel="nofollow"><?= lang('Seb.petunjuk.berkasUnduh', ['<span class="ltr">' . esc($m['code']) . '</span> — ' . esc($m['name'])]) ?></a></li>
            <?php endforeach ?>
        </ul>
    <?php endif ?>

    <h2><?= lang('Seb.petunjuk.selamaJudul') ?></h2>
    <ul>
        <?php foreach (lang('Seb.petunjuk.selama') as $s): ?>
            <li><?= esc($s) ?></li>
        <?php endforeach ?>
    </ul>
    <p><a href="/seb/periksa" rel="nofollow"><?= lang('Seb.petunjuk.periksa') ?></a></p>
</div>
<?= $this->endSection() ?>
