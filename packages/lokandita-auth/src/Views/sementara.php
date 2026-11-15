<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<div class="kartu">
    <h1><?= lang('Lokandita.halo', [esc($identitas->nama)]) ?></h1>
    <p><?= lang('Lokandita.sementara', [esc(\Lokandita\Auth\Aplikasi::nama())]) ?></p>
</div>
<?= $this->endSection() ?>
