<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<?php $baru = $matkul === null; ?>
<?= view('admin/_nav') ?>
<h1><?= $baru ? lang('Roster.matkul.tambah') : lang('Roster.matkul.ubah') ?></h1>
<div class="kartu">
    <form method="post" action="<?= $baru ? '/admin/mata-kuliah' : '/admin/mata-kuliah/' . (int) $matkul['id'] ?>">
        <?= csrf_field() ?>
        <label for="code"><?= lang('Roster.matkul.kode') ?></label>
        <input type="text" id="code" name="code" class="ltr" value="<?= esc($isi['code']) ?>" maxlength="20" required>
        <?= view('admin/roster/_galat', ['galat' => $galat, 'kolom' => 'code']) ?>

        <label for="name"><?= lang('Roster.matkul.nama') ?></label>
        <input type="text" id="name" name="name" value="<?= esc($isi['name']) ?>" maxlength="190" required>
        <?= view('admin/roster/_galat', ['galat' => $galat, 'kolom' => 'name']) ?>

        <label for="pengampu"><?= lang('Roster.matkul.pengampuNomor') ?></label>
        <input type="text" id="pengampu" name="pengampu" class="ltr" value="<?= esc($isi['pengampu']) ?>" maxlength="40" required>
        <p class="petunjuk"><?= lang('Roster.matkul.pengampuInfo') ?></p>
        <?= view('admin/roster/_galat', ['galat' => $galat, 'kolom' => 'pengampu']) ?>

        <div class="baris-tombol">
            <button type="submit" class="tombol"><?= lang('Roster.aksi.simpan') ?></button>
            <a class="tombol tombol-sekunder" href="<?= $baru ? '/admin/mata-kuliah' : '/admin/mata-kuliah/' . (int) $matkul['id'] ?>"><?= lang('Roster.aksi.batal') ?></a>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
