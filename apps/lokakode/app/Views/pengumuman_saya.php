<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<?= view('_nav', ['akses' => $akses, 'notifBelum' => $notifBelum ?? 0]) ?>
<h1><?= lang('Komunikasi.pengumuman.judul') ?></h1>
<?php if ($daftar === []): ?>
    <div class="kartu"><p><?= lang('Komunikasi.pengumuman.kosongSaya') ?></p></div>
<?php else: ?>
    <?= view('_pengumuman', ['daftar' => $daftar, 'orang' => $orang]) ?>
<?php endif ?>
<?= $this->endSection() ?>
