<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<?= view('admin/_nav') ?>
<h1><?= lang('Roster.kelas.ubah') ?> — <span class="ltr"><?= esc($kelas['matkul_kode']) ?></span> <?= esc($kelas['name']) ?></h1>
<div class="kartu">
    <form method="post" action="/admin/kelas/<?= (int) $kelas['id'] ?>">
        <?= csrf_field() ?>
        <label for="name"><?= lang('Roster.kelas.namaKelas') ?></label>
        <input type="text" id="name" name="name" value="<?= esc($isi['name']) ?>" maxlength="60" required>
        <?= view('admin/roster/_galat', ['galat' => $galat, 'kolom' => 'name']) ?>

        <label for="capacity"><?= lang('Roster.kelas.kapasitasIsi') ?></label>
        <input type="number" id="capacity" name="capacity" class="ltr" value="<?= esc($isi['capacity']) ?>" min="1" max="99999">
        <?= view('admin/roster/_galat', ['galat' => $galat, 'kolom' => 'capacity']) ?>

        <div class="baris-tombol">
            <button type="submit" class="tombol"><?= lang('Roster.aksi.simpan') ?></button>
            <a class="tombol tombol-sekunder" href="/admin/kelas/<?= (int) $kelas['id'] ?>"><?= lang('Roster.aksi.batal') ?></a>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
