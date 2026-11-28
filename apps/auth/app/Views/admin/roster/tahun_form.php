<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<?= view('admin/_nav') ?>
<h1><?= lang('Roster.tahun.ubah') ?></h1>
<div class="kartu">
    <form method="post" action="/admin/tahun-ajaran/<?= (int) $tahun['id'] ?>">
        <?= csrf_field() ?>
        <?= view('admin/roster/_tahun_kolom', ['isi' => $isi, 'galat' => $galat]) ?>
        <div class="baris-tombol">
            <button type="submit" class="tombol"><?= lang('Roster.aksi.simpan') ?></button>
            <a class="tombol tombol-sekunder" href="/admin/tahun-ajaran"><?= lang('Roster.aksi.batal') ?></a>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
