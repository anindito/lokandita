<?php
/**
 * Tata letak bersama ketiga aplikasi.
 *
 * Variabel: $judul (string), $namaAplikasi (string, opsional), $pesan (list
 * of ['jenis' => 'galat'|'sukses', 'teks' => string], opsional), $kelasBadan
 * (string, opsional: kelas CSS pada <body>, misalnya untuk halaman selebar layar).
 * Halaman publik (Lokandita\Auth\Publik::data): $publik (true = boleh diindeks, tombol Masuk
 * dan pilihan bahasa di kepala), $deskripsi (string), $navPublik (list of ['href', 'teks']),
 * $kreditFoto (string, opsional: sumber foto yang tampil di halaman itu; ditulis di kaki).
 * Tanpa $publik halaman bertanda noindex — itu bawaannya, supaya halaman bersesi tidak
 * pernah terindeks karena lupa.
 * Bagian: 'isi'; 'kepala' (opsional: tag tambahan di <head>, misalnya skrip halaman itu).
 */
$publik    = ($publik ?? false) === true;
$portal    = rtrim((string) getenv('PORTAL_URL'), '/');
$jalur     = '/' . ltrim(service('request')->getUri()->getPath(), '/');
$kanonik   = rtrim((string) getenv('APP_URL'), '/') . ($jalur === '/' ? '/' : $jalur);
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
<?php if ($publik): ?>
    <meta name="robots" content="index, follow">
    <meta name="description" content="<?= esc($deskripsi ?? '') ?>">
    <link rel="canonical" href="<?= esc($kanonik) ?>">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?= esc($aplikasi) ?>">
    <meta property="og:title" content="<?= esc($judul ?? $aplikasi) ?>">
    <meta property="og:description" content="<?= esc($deskripsi ?? '') ?>">
    <meta property="og:url" content="<?= esc($kanonik) ?>">
    <?php foreach (['png', 'jpg'] as $og): ?>
        <?php if (is_file(FCPATH . 'merek/og-image.' . $og)): ?>
    <meta property="og:image" content="<?= esc(rtrim((string) getenv('APP_URL'), '/')) ?>/merek/og-image.<?= $og ?>">
            <?php break ?>
        <?php endif ?>
    <?php endforeach ?>
<?php else: ?>
    <meta name="robots" content="noindex, nofollow">
<?php endif ?>
    <title><?= esc($judul ?? $aplikasi) ?> — <?= esc($aplikasi) ?></title>
    <link rel="icon" href="/favicon.ico">
    <link rel="stylesheet" href="/aset/lokandita.css?v=<?= esc($versi) ?>">
    <script src="/aset/lokandita.js?v=<?= esc($versi) ?>" defer></script>
    <?= $this->renderSection('kepala') ?>
</head>
<body<?php if (($kelasBadan ?? '') !== ''): ?> class="<?= esc($kelasBadan) ?>"<?php endif ?><?php if ($identitas !== null): ?> data-sesi-sisa="<?= $identitas->detikTersisa() ?>" data-sesi-idle="<?= $identitas->idleLogout ? '1' : '0' ?>" data-csrf="<?= esc(csrf_hash()) ?>"<?php endif ?>>
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
    <?php elseif ($publik): ?>
        <div class="kepala-publik">
            <?php if (($navPublik ?? []) !== []): ?>
                <nav class="nav-publik" aria-label="<?= lang('Lokandita.navUtama') ?>">
                    <?php foreach ($navPublik as $n): ?>
                        <a href="<?= esc($n['href']) ?>" <?= $n['href'] === $jalur ? 'aria-current="page"' : '' ?>><?= esc($n['teks']) ?></a>
                    <?php endforeach ?>
                </nav>
            <?php endif ?>
            <nav class="bahasa bahasa-kepala" aria-label="<?= lang('Lokandita.bahasa') ?>">
                <?php foreach (['id' => 'bahasaId', 'en' => 'bahasaEn', 'ar' => 'bahasaAr'] as $kode => $kunci): ?>
                    <a href="/bahasa/<?= $kode ?>?kembali=<?= esc(rawurlencode($jalur)) ?>" lang="<?= $kode ?>" rel="nofollow" <?= $kode === $locale ? 'aria-current="true"' : '' ?>><?= lang('Lokandita.' . $kunci) ?></a>
                <?php endforeach ?>
            </nav>
            <a class="tombol" href="/beranda" rel="nofollow"><?= lang('Lokandita.masuk') ?></a>
        </div>
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
    <span class="kaki-butir"><?= \Lokandita\Auth\Ikon::svg('mail') ?> <span><?= lang('Lokandita.kontak') ?>:
        <a href="mailto:<?= esc((string) getenv('CONTACT_EMAIL')) ?>"><?= esc((string) getenv('CONTACT_EMAIL')) ?></a></span></span>
    <?php if ($portal !== ''): ?>
        <span class="kaki-tautan">
            <a class="kaki-butir" href="<?= esc($portal) ?>/privasi"><?= \Lokandita\Auth\Ikon::svg('lock') ?> <span><?= lang('Lokandita.privasi') ?></span></a>
            <a class="kaki-butir" href="<?= esc($portal) ?>/ketentuan"><?= \Lokandita\Auth\Ikon::svg('file-text') ?> <span><?= lang('Lokandita.ketentuan') ?></span></a>
        </span>
    <?php endif ?>
    <?php if (($kreditFoto ?? '') !== ''): ?>
        <span class="kaki-butir kaki-kredit" data-kredit-foto><?= \Lokandita\Auth\Ikon::svg('camera') ?> <span><?= lang('Lokandita.kreditFoto', [esc($kreditFoto)]) ?></span></span>
    <?php endif ?>
</footer>
</body>
</html>
