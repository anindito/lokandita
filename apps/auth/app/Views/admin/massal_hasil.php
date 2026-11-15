<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<?= view('admin/_nav') ?>
<div class="kartu">
    <h1><?= lang('Admin.massal.hasilJudul') ?></h1>
    <p data-hasil><?= lang('Admin.massal.hasil', [$berhasil]) ?></p>
    <?php if ($ditolak !== []): ?>
        <h2><?= lang('Admin.massal.ditolak', [count($ditolak)]) ?></h2>
        <ul>
            <?php foreach ($ditolak as $d): ?>
                <li><span class="ltr"><?= esc($d['nomor_induk']) ?></span> — <?= lang('Admin.galat.' . $d['alasan']) ?></li>
            <?php endforeach ?>
        </ul>
    <?php endif ?>
    <?php if ($token !== null): ?>
        <?= view('admin/_unduh_kredensial', ['token' => $token]) ?>
    <?php endif ?>
    <div class="baris-tombol"><a class="tombol tombol-sekunder" href="/admin/pengguna"><?= lang('Admin.kembaliKeDaftar') ?></a></div>
</div>
<?= $this->endSection() ?>
