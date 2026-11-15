<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<?= view('admin/_nav') ?>
<div class="kartu">
    <h1><?= lang('Admin.impor.hasilJudul') ?></h1>
    <p data-hasil><?= lang('Admin.impor.hasil', [$dibuat]) ?></p>
    <?php if ($dilewati > 0): ?><p><?= lang('Admin.impor.dilewati', [$dilewati]) ?></p><?php endif ?>
    <?php if ($token !== null): ?>
        <?= view('admin/_unduh_kredensial', ['token' => $token]) ?>
    <?php endif ?>
    <div class="baris-tombol"><a class="tombol tombol-sekunder" href="/admin/pengguna"><?= lang('Admin.kembaliKeDaftar') ?></a></div>
</div>
<?= $this->endSection() ?>
