<?php
/**
 * Panel SEB satu mata kuliah (SPEC.md §13.1). Variabel: $matkul, $aktif (baris seb_configs atau null),
 * $kunci (list hex), $isianKunci, $galat (kolom => kunci Seb.galat.*), $diberlakukan, $asal (list asal),
 * $nav (HTML navigasi aplikasi ini, dari Sumber::navigasi).
 */
$id = (int) $matkul['id'];
?>
<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<?= $nav ?>
<h1><?= lang('Seb.kelola.judul') ?> — <span class="ltr"><?= esc($matkul['code']) ?></span></h1>
<p class="redup"><a href="/mata-kuliah/<?= $id ?>"><?= esc($matkul['name']) ?></a> · <a href="/seb"><?= lang('Seb.nav') ?></a> · <a href="/seb/periksa"><?= lang('Seb.periksa.judul') ?></a></p>

<?php if (! $diberlakukan): ?>
    <div class="kartu" data-seb-mode-uji><p><strong><?= lang('Seb.kelola.modeUji') ?></strong></p></div>
<?php endif ?>

<div class="kartu">
    <?php if ($aktif === null): ?>
        <p data-seb-versi="0"><?= lang('Seb.kelola.belumAda') ?></p>
    <?php else: ?>
        <p data-seb-versi="<?= (int) $aktif['version'] ?>"><?= lang('Seb.kelola.keadaan', [(int) $aktif['version'], esc(\Lokandita\Auth\Waktu::lokal($aktif['created_at']))]) ?></p>
        <p><?= lang('Seb.kelola.kunciKonfig') ?>: <code class="ltr" data-kunci-konfig><?= esc($aktif['config_key']) ?></code></p>
        <div class="baris-tombol">
            <a class="tombol" href="/seb/unduh/<?= esc($matkul['code']) ?>.seb"><?= lang('Seb.kelola.unduh') ?></a>
        </div>
    <?php endif ?>
    <p><?= lang('Seb.kelola.asal') ?>:</p>
    <ul>
        <?php foreach ($asal as $a): ?>
            <li><code class="ltr"><?= esc($a) ?></code></li>
        <?php endforeach ?>
    </ul>
</div>

<?php if ($aktif !== null): ?>
    <h2><?= lang('Seb.kelola.kunciJudul') ?></h2>
    <div class="kartu">
        <p><?= lang('Seb.kelola.kunciTeks') ?></p>
        <?php if ($kunci === []): ?>
            <p class="teks-bahaya" data-kunci-kosong><?= lang('Seb.kelola.kunciKosong') ?></p>
        <?php endif ?>
        <form method="post" action="/mata-kuliah/<?= $id ?>/seb/kunci">
            <?= csrf_field() ?>
            <label for="kunci"><?= lang('Seb.kelola.kunciLabel') ?></label>
            <textarea id="kunci" name="kunci" rows="4" class="kode-isian" spellcheck="false" autocomplete="off"><?= esc($isianKunci) ?></textarea>
            <?php if (isset($galat['kunci'])): ?><p class="galat-kolom" role="alert"><?= lang('Seb.galat.' . $galat['kunci']) ?></p><?php endif ?>
            <div class="baris-tombol"><button type="submit" class="tombol"><?= lang('Seb.kelola.kunciSimpan') ?></button></div>
        </form>
    </div>
<?php endif ?>

<h2><?= lang($aktif === null ? 'Seb.kelola.buatJudul' : 'Seb.kelola.rotasiJudul') ?></h2>
<div class="kartu">
    <?php if ($aktif !== null): ?><p><?= lang('Seb.kelola.rotasiTeks') ?></p><?php endif ?>
    <form method="post" action="/mata-kuliah/<?= $id ?>/seb">
        <?= csrf_field() ?>
        <label for="sandi_keluar"><?= lang('Seb.kelola.sandiKeluar') ?></label>
        <input type="password" id="sandi_keluar" name="sandi_keluar" minlength="12" maxlength="200" required autocomplete="new-password">
        <p class="petunjuk"><?= lang('Seb.kelola.sandiPetunjuk') ?></p>
        <?php if (isset($galat['sandi_keluar'])): ?><p class="galat-kolom" role="alert"><?= lang('Seb.galat.' . $galat['sandi_keluar']) ?></p><?php endif ?>
        <div class="baris-tombol"><button type="submit" class="tombol<?= $aktif !== null ? ' tombol-bahaya' : '' ?>"><?= lang($aktif === null ? 'Seb.kelola.buat' : 'Seb.kelola.rotasi') ?></button></div>
    </form>
</div>
<?= $this->endSection() ?>
