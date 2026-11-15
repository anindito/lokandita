<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<?php $locale = service('request')->getLocale(); ?>
<div class="isi-sempit">
    <div class="kartu">
        <h1><?= lang('Auth.masuk') ?></h1>
        <p class="redup"><?= lang('Auth.masukPengantar') ?></p>
        <form method="post" action="/login" autocomplete="on">
            <?= csrf_field() ?>
            <?php if ($next !== null): ?>
                <input type="hidden" name="next" value="<?= esc($next) ?>">
            <?php endif ?>
            <label for="nomor_induk"><?= lang('Auth.nomorInduk') ?></label>
            <input type="text" id="nomor_induk" name="nomor_induk" value="<?= esc($nomor) ?>" class="ltr"
                   inputmode="text" autocomplete="username" autocapitalize="none" spellcheck="false" required autofocus maxlength="40">
            <p class="petunjuk"><?= lang('Auth.nomorIndukPetunjuk') ?></p>
            <label for="password"><?= lang('Auth.kataSandi') ?></label>
            <input type="password" id="password" name="password" class="ltr" autocomplete="current-password" required maxlength="200">
            <button type="submit" class="tombol tombol-lebar"><?= lang('Auth.masuk') ?></button>
        </form>
        <p class="petunjuk"><?= lang('Auth.lupaSandi') ?></p>
    </div>
    <nav class="bahasa" aria-label="<?= lang('Lokandita.bahasa') ?>">
        <?php foreach (['id' => 'bahasaId', 'en' => 'bahasaEn', 'ar' => 'bahasaAr'] as $kode => $kunci): ?>
            <a href="/bahasa/<?= $kode ?>" lang="<?= $kode ?>" <?= $kode === $locale ? 'aria-current="true"' : '' ?>><?= lang('Lokandita.' . $kunci) ?></a>
        <?php endforeach ?>
    </nav>
</div>
<?= $this->endSection() ?>
