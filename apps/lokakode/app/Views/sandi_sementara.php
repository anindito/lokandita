<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<?= view('_nav', ['akses' => $akses]) ?>
<div class="isi-sempit">
    <div class="kartu">
        <h1><?= lang('Kode.sandi.selesai') ?></h1>
        <table>
            <tr><th scope="row"><?= lang('Kode.mhs.nomorInduk') ?></th><td class="ltr"><?= esc($mhs['nomor_induk']) ?></td></tr>
            <tr><th scope="row"><?= lang('Kode.mhs.nama') ?></th><td><?= esc($mhs['nama']) ?></td></tr>
            <tr><th scope="row"><?= lang('Kode.sandi.sementara') ?></th><td class="kode-besar" data-sandi-sementara><?= esc($sandi) ?></td></tr>
        </table>
        <p class="petunjuk"><?= lang('Kode.sandi.sekali') ?></p>
        <div class="baris-tombol">
            <a class="tombol" href="/kelas/<?= (int) $kelas['id'] ?>"><?= lang('Kode.sandi.kembali') ?></a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
