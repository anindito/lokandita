<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<?php
[$jenis, $a, $b] = array_pad(explode(':', $aksi), 3, '');
$uraian = match ($jenis) {
    'akses'  => lang($a === '+' ? 'Admin.massal.beriAkses' : 'Admin.massal.cabutAkses', [$b]),
    'status' => lang('Admin.massal.ubahStatus', [lang('Admin.statusNama.' . $a)]),
    default  => lang('Admin.massal.reset'),
};
?>
<?= view('admin/_nav') ?>
<div class="kartu">
    <h1><?= lang('Admin.massal.konfirmasiJudul') ?></h1>
    <p data-konfirmasi><strong><?= lang('Admin.massal.konfirmasi', [count($pengguna), $uraian]) ?></strong></p>
    <?php if ($jenis === 'reset'): ?><p><?= lang('Admin.massal.resetAkibat') ?></p><?php endif ?>
    <?php if ($jenis === 'status' && $a !== 'active'): ?><p><?= lang('Admin.massal.statusAkibat') ?></p><?php endif ?>
    <form method="post" action="/admin/pengguna/massal">
        <?= csrf_field() ?>
        <input type="hidden" name="aksi" value="<?= esc($aksi) ?>">
        <input type="hidden" name="yakin" value="1">
        <div class="tabel-wadah">
            <table>
                <thead><tr><th scope="col"><?= lang('Auth.nomorInduk') ?></th><th scope="col"><?= lang('Auth.nama') ?></th><th scope="col"><?= lang('Auth.peran') ?></th><th scope="col"><?= lang('Admin.status') ?></th></tr></thead>
                <tbody>
                <?php foreach ($pengguna as $u): ?>
                    <tr>
                        <td class="ltr"><input type="hidden" name="ids[]" value="<?= esc($u['id']) ?>"><?= esc($u['nomor_induk']) ?></td>
                        <td><?= esc($u['nama']) ?></td>
                        <td><?= lang('Lokandita.peran.' . $u['global_role']) ?></td>
                        <td><?= lang('Admin.statusNama.' . $u['status']) ?></td>
                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>
        </div>
        <div class="baris-tombol">
            <button type="submit" class="tombol tombol-bahaya"><?= lang('Admin.massal.yaJalankan', [count($pengguna)]) ?></button>
            <a class="tombol tombol-sekunder" href="/admin/pengguna"><?= lang('Admin.batal') ?></a>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
