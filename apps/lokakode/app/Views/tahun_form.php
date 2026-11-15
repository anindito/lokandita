<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<?= view('_nav', ['akses' => $akses]) ?>
<h1><?= lang('Kode.tahun.ubah') ?></h1>
<div class="kartu">
    <form method="post" action="/tahun-ajaran/<?= (int) $tahun['id'] ?>">
        <?= csrf_field() ?>
        <?= view('_tahun_kolom', ['isi' => $isi, 'galat' => $galat]) ?>
        <div class="baris-tombol">
            <button type="submit" class="tombol"><?= lang('Kode.aksi.simpan') ?></button>
            <a class="tombol tombol-sekunder" href="/tahun-ajaran"><?= lang('Kode.aksi.batal') ?></a>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
