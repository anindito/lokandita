<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<h1><?= lang('Lokandita.halo', [esc($akses->identitas()->nama)]) ?></h1>

<h2><?= lang('Ujian.saya.ujianJudul') ?></h2>
<div class="kartu" data-ujian-kosong><p><?= lang('Ujian.saya.ujianKosong') ?></p></div>
<?= $this->endSection() ?>
