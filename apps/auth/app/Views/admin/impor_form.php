<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<?= view('admin/_nav') ?>
<h1><?= lang('Admin.impor.judul') ?></h1>
<div class="kartu">
    <p><?= lang('Admin.impor.pengantar') ?></p>
    <p class="kode-besar">nomor_induk, nama, email, global_role, apps</p>
    <ul>
        <li><?= lang('Admin.impor.aturanPeran') ?></li>
        <li><?= lang('Admin.impor.aturanApps') ?></li>
        <li><?= lang('Admin.impor.aturanEmail') ?></li>
        <li><?= lang('Admin.impor.aturanBatas', [\App\Libraries\Importer::MAKS_BARIS]) ?></li>
    </ul>
    <form method="post" action="/admin/impor" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <label for="berkas"><?= lang('Admin.impor.berkas') ?></label>
        <input type="file" id="berkas" name="berkas" accept=".csv,.xlsx" required>
        <button type="submit" class="tombol tombol-lebar"><?= lang('Admin.impor.periksa') ?></button>
    </form>
    <p class="petunjuk"><?= lang('Admin.impor.belumDisimpan') ?></p>
</div>
<?= $this->endSection() ?>
