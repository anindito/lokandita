<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<h1><?= lang('Lokandita.halo', [esc($akses->identitas()->nama)]) ?></h1>
<?= $this->endSection() ?>
