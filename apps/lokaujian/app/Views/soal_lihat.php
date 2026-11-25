<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('kepala') ?><?= view('_kepala_bank') ?><?= $this->endSection() ?>
<?= $this->section('isi') ?>
<?php
/**
 * Pratinjau satu soal lepas, dengan kunci dan tindakan untuk staf. Variabel: $matkul, $soal,
 * $dipakai (ujian yang memakainya).
 */
$dasar = '/mata-kuliah/' . (int) $matkul['id'] . '/bank';
$ini   = $dasar . '/soal/' . (int) $soal['id'];
?>
<?= view('_nav', ['akses' => $akses]) ?>
<p><a href="<?= $dasar ?>"><span class="panah-balik" aria-hidden="true"></span> <?= lang('Bank.kembali') ?></a></p>
<h1><?= lang('Bank.pratinjau') ?> <span class="pratinjau-pita"><?= lang('Bank.pratinjau') ?></span>
    <?php if ($soal['archived_at'] !== null): ?><span class="lencana lencana-galat"><?= lang('Bank.diarsipkan') ?></span><?php endif ?></h1>
<p class="redup"><?= lang('Bank.pratinjauInfo') ?></p>

<?= view('_soal', ['soal' => $soal, 'nomor' => 1]) ?>

<div class="kartu">
    <?= view('_kunci', ['soal' => $soal]) ?>
    <?= view('_dipakai', ['dipakai' => $dipakai, 'matkul' => $matkul]) ?>
    <div class="baris-tombol">
        <a class="tombol" href="<?= $ini ?>/sunting"><?= lang('Bank.sunting') ?></a>
        <form method="post" action="<?= $ini ?>/arsip" class="inline">
            <?= csrf_field() ?>
            <input type="hidden" name="arsip" value="<?= $soal['archived_at'] === null ? '1' : '0' ?>">
            <button type="submit" class="tombol tombol-sekunder"><?= lang($soal['archived_at'] === null ? 'Bank.arsipkan' : 'Bank.pulihkan') ?></button>
        </form>
        <?php if ($dipakai === []): ?>
            <form method="post" action="<?= $ini ?>/hapus" class="inline">
                <?= csrf_field() ?>
                <button type="submit" class="tombol tombol-sekunder"><?= lang('Bank.hapus') ?></button>
            </form>
        <?php endif ?>
    </div>
    <?php if ($dipakai !== []): ?><p class="petunjuk"><?= lang('Bank.hapusInfo') ?></p><?php endif ?>
</div>
<?= $this->endSection() ?>
