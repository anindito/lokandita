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
$aplikasi  = $namaAplikasi ?? 'Lokandita';
$versi     = substr((string) getenv('LOKANDITA_COMMIT'), 0, 12);
?>
<!doctype html>
<html lang="<?= esc($locale, 'attr') ?>" dir="<?= \Lokandita\Auth\Locale::arah($locale) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= esc($judul ?? $aplikasi) ?> — <?= esc($aplikasi) ?></title>
    <link rel="icon" href="/favicon.ico">
    <link rel="stylesheet" href="/aset/lokandita.css?v=<?= esc($versi, 'attr') ?>">
    <script src="/aset/lokandita.js?v=<?= esc($versi, 'attr') ?>" defer></script>
</head>
<body>
<a class="lewati" href="#isi"><?= lang('Lokandita.lewati') ?></a>
<header class="kepala">
    <a class="merek" href="/">
        <img src="/merek/icon-192.png" alt="" width="36" height="36">
        <span><?= esc($aplikasi) ?></span>
    </a>
    <?php if ($identitas !== null): ?>
        <?= view('Lokandita\Auth\Views\menu_akun', ['identitas' => $identitas]) ?>
    <?php endif ?>
</header>

<div class="toast-wadah" aria-live="assertive">
    <?php foreach (($pesan ?? []) as $p): ?>
        <div class="toast toast-<?= esc($p['jenis'], 'attr') ?>" role="<?= $p['jenis'] === 'galat' ? 'alert' : 'status' ?>" data-toast>
            <span><?= esc($p['teks']) ?></span>
            <button type="button" class="toast-tutup" data-toast-tutup aria-label="<?= lang('Lokandita.tutup') ?>">&times;</button>
        </div>
    <?php endforeach ?>
</div>

<main id="isi" class="isi">
    <?= $this->renderSection('isi') ?>
</main>

<footer class="kaki">
    <span><?= lang('Lokandita.kontak') ?>:
        <a href="mailto:<?= esc((string) getenv('CONTACT_EMAIL'), 'attr') ?>"><?= esc((string) getenv('CONTACT_EMAIL')) ?></a></span>
</footer>
</body>
</html>
