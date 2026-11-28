<?php
/**
 * Navigasi LokaKode. Variabel: $akses (App\Policy\Akses); untuk mahasiswa juga $notifBelum
 * (jumlah pemberitahuan yang belum dibaca, diisi BaseController::halaman).
 * Mahasiswa tidak mendapat menu pengelolaan. Menu akun bersama tidak diubah (KONTRAK.md §4).
 */
?>
<?php if ($akses->staf()): ?>
<nav class="admin-nav" aria-label="<?= lang('Kode.navigasi') ?>">
    <a href="/beranda"><?= lang('Kode.nav.beranda') ?></a>
    <a href="/mata-kuliah"><?= lang('Kode.nav.matkul') ?></a>
    <?php if ($akses->superadmin()): ?>
        <a href="<?= esc(\App\Controllers\Dipindahkan::alamat('roster')) ?>"><?= lang('Kode.roster.nav') ?></a>
        <a href="/impor"><?= lang('Soal.impor.nav') ?></a>
        <a href="/audit"><?= lang('Kode.nav.audit') ?></a>
    <?php endif ?>
</nav>
<?php elseif ($akses->mahasiswa()): ?>
<nav class="nav-mahasiswa" aria-label="<?= lang('Kode.navigasi') ?>" data-nav-mahasiswa>
    <a href="/beranda"><?= lang('Kode.nav.beranda') ?></a>
    <a href="/pengumuman"><?= lang('Komunikasi.pengumuman.judul') ?></a>
    <a href="/notifikasi" data-notif-belum="<?= (int) ($notifBelum ?? 0) ?>"><?= lang('Komunikasi.notifikasi.judul') ?><?php if (($notifBelum ?? 0) > 0): ?>
        <span class="lencana lencana-galat" aria-label="<?= esc(lang('Komunikasi.notifikasi.belumDibaca', [(int) $notifBelum])) ?>"><?= (int) $notifBelum ?></span><?php endif ?></a>
</nav>
<?php endif ?>
