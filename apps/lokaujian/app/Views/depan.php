<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<div class="isi-sempit">
    <div class="kartu">
        <h1><?= lang('Ujian.depan.judul') ?></h1>
        <p><?= lang('Ujian.depan.pembuka') ?></p>
        <p><?= lang('Ujian.depan.seb') ?></p>
        <div class="baris-tombol">
            <a class="tombol" href="/beranda" rel="nofollow"><?= lang('Lokandita.masuk') ?></a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
