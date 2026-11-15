<?php
/** Navigasi LokaKode. Variabel: $akses (App\Policy\Akses). Mahasiswa tidak mendapat menu pengelolaan. */
?>
<?php if ($akses->staf()): ?>
<nav class="admin-nav" aria-label="<?= lang('Kode.navigasi') ?>">
    <a href="/beranda"><?= lang('Kode.nav.beranda') ?></a>
    <a href="/mata-kuliah"><?= lang('Kode.nav.matkul') ?></a>
</nav>
<?php endif ?>
