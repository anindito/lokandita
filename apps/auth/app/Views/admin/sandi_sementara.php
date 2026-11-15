<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<?= view('admin/_nav') ?>
<div class="isi-sempit">
    <div class="kartu">
        <h1><?= lang('Admin.sandiSementara.' . $peristiwa) ?></h1>
        <table>
            <tr><th scope="row"><?= lang('Auth.nomorInduk') ?></th><td class="ltr"><?= esc($user['nomor_induk']) ?></td></tr>
            <tr><th scope="row"><?= lang('Auth.nama') ?></th><td><?= esc($user['nama']) ?></td></tr>
            <tr><th scope="row"><?= lang('Admin.sandiSementara.judul') ?></th><td class="kode-besar" data-sandi-sementara><?= esc($sandi) ?></td></tr>
        </table>
        <p class="petunjuk"><?= lang('Admin.sandiSementara.peringatan') ?></p>
        <div class="baris-tombol">
            <a class="tombol" href="/admin/pengguna/<?= esc($user['id']) ?>"><?= lang('Admin.kembaliKePengguna') ?></a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
