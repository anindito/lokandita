<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<?= view('_nav', ['akses' => $akses]) ?>
<h1><?= lang('Kode.kelas.ubah') ?> — <span class="ltr"><?= esc($kelas['matkul_kode']) ?></span> <?= esc($kelas['name']) ?></h1>
<div class="kartu">
    <form method="post" action="/kelas/<?= (int) $kelas['id'] ?>">
        <?= csrf_field() ?>
        <label for="name"><?= lang('Kode.kelas.namaKelas') ?></label>
        <input type="text" id="name" name="name" value="<?= esc($isi['name']) ?>" maxlength="60" required>
        <?= view('_galat', ['galat' => $galat, 'kolom' => 'name']) ?>

        <label for="capacity"><?= lang('Kode.kelas.kapasitasIsi') ?></label>
        <input type="number" id="capacity" name="capacity" class="ltr" value="<?= esc($isi['capacity']) ?>" min="1" max="99999">
        <?= view('_galat', ['galat' => $galat, 'kolom' => 'capacity']) ?>

        <div class="baris-tombol">
            <button type="submit" class="tombol"><?= lang('Kode.aksi.simpan') ?></button>
            <a class="tombol tombol-sekunder" href="/kelas/<?= (int) $kelas['id'] ?>"><?= lang('Kode.aksi.batal') ?></a>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
