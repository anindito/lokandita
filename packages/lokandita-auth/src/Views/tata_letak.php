<?php
/**
 * Tata letak bersama ketiga aplikasi.
 *
 * Variabel: $judul (string), $namaAplikasi (string, opsional), $pesan (list
 * of ['jenis' => 'galat'|'sukses', 'teks' => string], opsional).
 * Bagian: 'isi'.
 */
$locale    = service('request')->getLocale();
$identitas = \Lokandita\Auth\Current::identity();
$aplikasi  = $namaAplikasi ?? \Lokandita\Auth\Aplikasi::nama();
$versi     = substr((string) getenv('LOKANDITA_COMMIT'), 0, 12);
?>
<!doctype html>
<html lang="<?= esc($locale) ?>" dir="<?= \Lokandita\Auth\Locale::arah($locale) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= esc($judul ?? $aplikasi) ?> — <?= esc($aplikasi) ?></title>
    <link rel="icon" href="/favicon.ico">
    <link rel="stylesheet" href="/aset/lokandita.css?v=<?= esc($versi) ?>">
    <script src="/aset/lokandita.js?v=<?= esc($versi) ?>" defer></script>
</head>
<body<?php if ($identitas !== null): ?> data-sesi-sisa="<?= $identitas->detikTersisa() ?>" data-sesi-idle="<?= $identitas->idleLogout ? '1' : '0' ?>" data-csrf="<?= esc(csrf_hash()) ?>"<?php endif ?>>
<a class="lewati" href="#isi"><?= lang('Lokandita.lewati') ?></a>
<header class="kepala">
    <a class="merek" href="/">
        <?php if (is_file(FCPATH . 'merek/icon-192.png')): ?>
            <img src="/merek/icon-192.png" alt="" width="36" height="36">
        <?php endif ?>
        <span><?= esc($aplikasi) ?></span>
    </a>
    <?php if ($identitas !== null): ?>
        <?= view('Lokandita\Auth\Views\menu_akun', ['identitas' => $identitas]) ?>
    <?php endif ?>
</header>

<div class="toast-wadah" aria-live="assertive">
    <?php foreach (($pesan ?? []) as $p): ?>
        <div class="toast toast-<?= esc($p['jenis']) ?>" role="<?= $p['jenis'] === 'galat' ? 'alert' : 'status' ?>" data-toast>
            <span><?= esc($p['teks']) ?></span>
            <button type="button" class="toast-tutup" data-toast-tutup aria-label="<?= lang('Lokandita.tutup') ?>">&times;</button>
        </div>
    <?php endforeach ?>
</div>

<main id="isi" class="isi">
    <?= $this->renderSection('isi') ?>
</main>

<?php if ($identitas !== null): ?>
    <div class="sesi-peringatan" role="alert" hidden data-sesi-peringatan data-pesan-gagal="<?= esc(lang('Lokandita.gagalPerpanjang')) ?>">
        <span data-sesi-teks><?= lang('Lokandita.sesiHampirHabis') ?></span>
        <button type="button" class="tombol" data-sesi-tetap><?= lang('Lokandita.tetapMasuk') ?></button>
    </div>
<?php endif ?>

<footer class="kaki">
    <span><?= lang('Lokandita.kontak') ?>:
        <a href="mailto:<?= esc((string) getenv('CONTACT_EMAIL')) ?>"><?= esc((string) getenv('CONTACT_EMAIL')) ?></a></span>
</footer>
</body>
</html>
