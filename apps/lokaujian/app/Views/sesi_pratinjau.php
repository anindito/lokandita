<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('kepala') ?><?= view('_kepala_bank') ?><?= $this->endSection() ?>
<?= $this->section('isi') ?>
<?php
/**
 * Pratinjau ujian terbit bagi yang menjalankan sesinya (SPEC-LOKAUJIAN.md §3). Partial yang sama
 * dengan halaman mahasiswa; tanpa kunci. Variabel: $sesi, $butir (isi beku).
 */
use App\Libraries\Markdown;

$nomor = 1;
?>
<?= view('_nav', ['akses' => $akses]) ?>
<p><a href="/sesi/<?= (int) $sesi['id'] ?>"><span class="panah-balik" aria-hidden="true"></span> <?= lang('Sesi.kembali') ?></a></p>
<h1 dir="auto"><?= esc($sesi['title']) ?></h1>
<p><span class="pratinjau-pita"><?= lang('Bank.pratinjau') ?></span></p>
<p class="redup"><?= lang('Bank.pratinjauInfo') ?></p>
<?php if ($sesi['instructions'] !== ''): ?>
    <h2><?= lang('Bank.ujian.petunjuk') ?></h2>
    <div class="kartu soal-teks" dir="auto"><?= Markdown::html($sesi['instructions']) ?></div>
<?php endif ?>
<?php foreach ($butir as $b): ?>
    <?php if ($b['jenis'] === 'soal'): ?>
        <?= view('_soal', ['soal' => $b['soal'], 'nomor' => $nomor]) ?>
        <?php $nomor++; ?>
    <?php else: ?>
        <?= view('_kelompok', ['kelompok' => $b['kelompok'], 'nomorAwal' => $nomor]) ?>
        <?php $nomor += count($b['kelompok']['questions']); ?>
    <?php endif ?>
<?php endforeach ?>
<?= $this->endSection() ?>
