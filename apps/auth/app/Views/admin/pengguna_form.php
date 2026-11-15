<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<?php
$g = static fn (string $kolom) => isset($galat[$kolom]) ? '<p class="galat-kolom" role="alert">' . esc(lang('Admin.galat.' . $galat[$kolom])) . '</p>' : '';
$baru = $user === null;
?>
<?= view('admin/_nav') ?>
<h1><?= $baru ? lang('Admin.tambah') : lang('Admin.sunting') ?></h1>

<div class="kartu">
    <form method="post" action="<?= $baru ? '/admin/pengguna' : '/admin/pengguna/' . esc($user['id']) ?>">
        <?= csrf_field() ?>
        <label for="nomor_induk"><?= lang('Auth.nomorInduk') ?></label>
        <input type="text" id="nomor_induk" name="nomor_induk" class="ltr" value="<?= esc($isi['nomor_induk']) ?>" maxlength="40" required <?= $baru ? '' : 'readonly' ?>>
        <?php if (! $baru): ?><p class="petunjuk"><?= lang('Admin.nomorTetap') ?></p><?php endif ?>
        <?= $g('nomor_induk') ?>

        <label for="nama"><?= lang('Auth.nama') ?></label>
        <input type="text" id="nama" name="nama" value="<?= esc($isi['nama']) ?>" maxlength="190" required>
        <?= $g('nama') ?>

        <label for="email"><?= lang('Admin.email') ?></label>
        <input type="email" id="email" name="email" class="ltr" value="<?= esc((string) $isi['email']) ?>" maxlength="190">
        <?= $g('email') ?>

        <label for="global_role"><?= lang('Auth.peran') ?></label>
        <select id="global_role" name="global_role">
            <?php foreach (\App\Libraries\UserAdmin::PERAN as $p): ?>
                <option value="<?= $p ?>" <?= $isi['global_role'] === $p ? 'selected' : '' ?>><?= lang('Lokandita.peran.' . $p) ?></option>
            <?php endforeach ?>
        </select>
        <?= $g('global_role') ?>

        <fieldset>
            <legend><?= lang('Admin.aksesAplikasi') ?></legend>
            <?php foreach (\App\Libraries\UserAdmin::APPS as $app): ?>
                <label class="centang"><input type="checkbox" name="apps[]" value="<?= $app ?>" <?= in_array($app, (array) $isi['apps'], true) ? 'checked' : '' ?>> <?= $app ?></label>
            <?php endforeach ?>
            <?= $g('apps') ?>
        </fieldset>

        <?php if (! $baru): ?>
            <label for="status"><?= lang('Admin.status') ?></label>
            <select id="status" name="status">
                <?php foreach (\App\Libraries\UserAdmin::STATUS as $s): ?>
                    <option value="<?= $s ?>" <?= $user['status'] === $s ? 'selected' : '' ?>><?= lang('Admin.statusNama.' . $s) ?></option>
                <?php endforeach ?>
            </select>
            <p class="petunjuk"><?= lang('Admin.statusPetunjuk') ?></p>
            <?= $g('status') ?>

            <label class="centang"><input type="checkbox" name="paste_allowed" value="1" <?= (int) $user['paste_allowed'] === 1 ? 'checked' : '' ?>> <?= lang('Admin.bolehTempel') ?></label>
            <p class="petunjuk"><?= lang('Admin.bolehTempelPetunjuk') ?></p>
        <?php else: ?>
            <p class="petunjuk"><?= lang('Admin.sandiDibuatOtomatis') ?></p>
        <?php endif ?>

        <div class="baris-tombol">
            <button type="submit" class="tombol"><?= lang('Admin.simpan') ?></button>
            <a class="tombol tombol-sekunder" href="/admin/pengguna"><?= lang('Admin.batal') ?></a>
        </div>
    </form>
</div>

<?php if (! $baru): ?>
    <h2><?= lang('Admin.tindakan') ?></h2>
    <div class="kartu">
        <p class="redup text-sm">
            <?= lang('Admin.dibuat') ?>: <?= esc(\App\Libraries\Clock::lokal($user['created_at'])) ?> ·
            <?= lang('Admin.loginTerakhir') ?>: <?= esc(\App\Libraries\Clock::lokal($user['last_login_at'])) ?> ·
            MFA: <?= (int) $user['mfa_enabled'] === 1 ? lang('Admin.aktif') : lang('Admin.tidakAktif') ?>
        </p>
        <div class="baris-tombol">
            <form method="post" action="/admin/pengguna/<?= esc($user['id']) ?>/reset-sandi">
                <?= csrf_field() ?>
                <button type="submit" class="tombol tombol-sekunder"><?= lang('Admin.resetSandi') ?></button>
            </form>
            <?php if ((int) $user['mfa_enabled'] === 1): ?>
                <form method="post" action="/admin/pengguna/<?= esc($user['id']) ?>/hapus-mfa">
                    <?= csrf_field() ?>
                    <button type="submit" class="tombol tombol-bahaya"><?= lang('Admin.hapusMfa') ?></button>
                </form>
            <?php endif ?>
        </div>
        <p class="petunjuk"><?= lang('Admin.resetPetunjuk') ?></p>
    </div>
<?php endif ?>
<?= $this->endSection() ?>
