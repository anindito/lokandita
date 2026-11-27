<?php
/**
 * Navigasi staf LokaUjian. Variabel: $akses (App\Policy\Akses). Mahasiswa belum punya halaman
 * selain beranda, jadi tidak mendapat navigasi. Menu akun bersama tidak diubah (KONTRAK.md §4).
 */
?>
<?php if ($akses->staf()): ?>
<nav class="admin-nav" aria-label="<?= lang('Ujian.navigasi') ?>">
    <a href="/beranda"><?= lang('Ujian.nav.beranda') ?></a>
    <a href="/mata-kuliah"><?= lang('Ujian.nav.matkul') ?></a>
    <?php if ($akses->superadmin()): ?><a href="/audit"><?= lang('Hasil.audit.judul') ?></a><?php endif ?>
</nav>
<?php endif ?>
