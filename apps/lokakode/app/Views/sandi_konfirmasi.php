<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<?= view('_nav', ['akses' => $akses]) ?>
<div class="isi-sempit">
    <div class="kartu">
        <h1><?= lang('Kode.sandi.tanya') ?></h1>
        <table>
            <tr><th scope="row"><?= lang('Kode.mhs.nomorInduk') ?></th><td class="ltr"><?= esc($mhs['nomor_induk']) ?></td></tr>
            <tr><th scope="row"><?= lang('Kode.mhs.nama') ?></th><td><?= esc($mhs['nama']) ?></td></tr>
            <tr><th scope="row"><?= lang('Kode.kelas.nama') ?></th><td><span class="ltr"><?= esc($kelas['matkul_kode']) ?></span> <?= esc($kelas['name']) ?></td></tr>
        </table>
        <p><?= lang('Kode.sandi.akibat') ?></p>
        <form method="post" action="/kelas/<?= (int) $kelas['id'] ?>/reset-sandi">
            <?= csrf_field() ?>
            <input type="hidden" name="user_id" value="<?= esc($mhs['id']) ?>">
            <input type="hidden" name="yakin" value="1">
            <div class="baris-tombol">
                <button type="submit" class="tombol tombol-bahaya"><?= lang('Kode.sandi.ya') ?></button>
                <a class="tombol tombol-sekunder" href="/kelas/<?= (int) $kelas['id'] ?>"><?= lang('Kode.sandi.batal') ?></a>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
