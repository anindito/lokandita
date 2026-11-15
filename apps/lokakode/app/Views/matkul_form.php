<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<?php $baru = $matkul === null; ?>
<?= view('_nav', ['akses' => $akses]) ?>
<h1><?= $baru ? lang('Kode.matkul.tambah') : lang('Kode.matkul.ubah') ?></h1>
<div class="kartu">
    <form method="post" action="<?= $baru ? '/mata-kuliah' : '/mata-kuliah/' . (int) $matkul['id'] ?>">
        <?= csrf_field() ?>
        <label for="code"><?= lang('Kode.matkul.kode') ?></label>
        <input type="text" id="code" name="code" class="ltr" value="<?= esc($isi['code']) ?>" maxlength="20" required>
        <?= view('_galat', ['galat' => $galat, 'kolom' => 'code']) ?>

        <label for="name"><?= lang('Kode.matkul.nama') ?></label>
        <input type="text" id="name" name="name" value="<?= esc($isi['name']) ?>" maxlength="190" required>
        <?= view('_galat', ['galat' => $galat, 'kolom' => 'name']) ?>

        <label for="pengampu"><?= lang('Kode.matkul.pengampuNomor') ?></label>
        <input type="text" id="pengampu" name="pengampu" class="ltr" value="<?= esc($isi['pengampu']) ?>" maxlength="40" required>
        <p class="petunjuk"><?= lang('Kode.matkul.pengampuInfo') ?></p>
        <?= view('_galat', ['galat' => $galat, 'kolom' => 'pengampu']) ?>

        <div class="baris-tombol">
            <button type="submit" class="tombol"><?= lang('Kode.aksi.simpan') ?></button>
            <a class="tombol tombol-sekunder" href="<?= $baru ? '/mata-kuliah' : '/mata-kuliah/' . (int) $matkul['id'] ?>"><?= lang('Kode.aksi.batal') ?></a>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
