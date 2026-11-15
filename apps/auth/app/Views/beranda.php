<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<div class="kartu">
    <h1><?= lang('Auth.akunSaya') ?></h1>
    <table>
        <tr><th scope="row"><?= lang('Auth.nama') ?></th><td><?= esc($identitas->nama) ?></td></tr>
        <tr><th scope="row"><?= lang('Auth.nomorInduk') ?></th><td class="ltr"><?= esc($identitas->nomorInduk) ?></td></tr>
        <tr><th scope="row"><?= lang('Auth.peran') ?></th><td><?= lang('Lokandita.peran.' . $identitas->globalRole) ?></td></tr>
    </table>
    <div class="baris-tombol">
        <?php if ($identitas->globalRole === 'superadmin'): ?>
            <a class="tombol" href="/admin/pengguna"><?= lang('Admin.pengguna') ?></a>
            <a class="tombol tombol-sekunder" href="/admin/audit"><?= lang('Admin.audit.judul') ?></a>
        <?php endif ?>
        <a class="tombol tombol-sekunder" href="/ganti-password"><?= lang('Auth.gantiSandi') ?></a>
        <a class="tombol tombol-sekunder" href="<?= esc((string) getenv('PORTAL_URL')) ?>"><?= lang('Lokandita.kePortal') ?></a>
    </div>
</div>
<?= $this->endSection() ?>
