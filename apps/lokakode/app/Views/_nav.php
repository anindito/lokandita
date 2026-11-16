<?php
/** Navigasi LokaKode. Variabel: $akses (App\Policy\Akses). Mahasiswa tidak mendapat menu pengelolaan. */
?>
<?php if ($akses->staf()): ?>
<nav class="admin-nav" aria-label="<?= lang('Kode.navigasi') ?>">
    <a href="/beranda"><?= lang('Kode.nav.beranda') ?></a>
    <a href="/mata-kuliah"><?= lang('Kode.nav.matkul') ?></a>
    <?php if ($akses->superadmin()): ?>
        <a href="/tahun-ajaran"><?= lang('Kode.nav.tahun') ?></a>
        <a href="/impor"><?= lang('Soal.impor.nav') ?></a>
        <a href="/audit"><?= lang('Kode.nav.audit') ?></a>
    <?php endif ?>
</nav>
<?php endif ?>
