<?php
/**
 * Halaman depan publik LokaKode. Tata letak dan komponennya sama dengan halaman depan
 * Portal (pembuka terbelah, kartu berikon, langkah bernomor), supaya keduanya sekeluarga.
 */
use Lokandita\Auth\Ikon;

// Ikon mengikuti urutan butir di berkas bahasa Depan.php; butir tambahan mendapat ikon umum.
$ikonFitur   = ['circle-check-big', 'dices', 'repeat', 'target', 'square-terminal', 'trophy'];
$ikonLangkah = ['log-in', 'book-open', 'play', 'send', 'refresh-cw'];
$berarah     = ['log-in', 'send'];
?>
<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<section class="pembuka">
    <div class="pembuka-teks">
        <h1><?= lang('Depan.tajuk') ?></h1>
        <p><?= lang('Depan.pengantar') ?></p>
        <a class="tombol tombol-besar" href="/beranda" rel="nofollow"><?= Ikon::svg('log-in', '', true) ?><span><?= lang('Lokandita.masuk') ?></span></a>
        <p class="petunjuk"><?= lang('Depan.tanpaDaftar') ?></p>
    </div>
    <picture class="pembuka-logo">
        <?php $versi = substr((string) getenv('LOKANDITA_COMMIT'), 0, 12) ?>
        <source media="(prefers-color-scheme: dark)" type="image/webp" srcset="/merek/pembuka-gelap.webp?v=<?= esc($versi) ?>">
        <source media="(prefers-color-scheme: dark)" srcset="/merek/pembuka-gelap.png?v=<?= esc($versi) ?>">
        <source type="image/webp" srcset="/merek/pembuka-terang.webp?v=<?= esc($versi) ?>">
        <img src="/merek/pembuka-terang.png?v=<?= esc($versi) ?>" width="352" height="352" alt="<?= lang('Depan.logoAlt') ?>" fetchpriority="high" decoding="async">
    </picture>
</section>

<h2 class="judul-bagian"><?= lang('Depan.fiturJudul') ?></h2>
<ul class="petak">
    <?php foreach (array_values(lang('Depan.fitur')) as $i => $f): ?>
        <li class="kartu-aplikasi">
            <span class="ikon-wadah"><?= Ikon::svg($ikonFitur[$i] ?? 'circle-check') ?></span>
            <strong><?= esc($f['judul']) ?></strong>
            <span><?= esc($f['isi']) ?></span>
        </li>
    <?php endforeach ?>
</ul>

<h2 class="judul-bagian"><?= lang('Depan.caraJudul') ?></h2>
<ol class="langkah" role="list">
    <?php foreach (array_values(lang('Depan.cara')) as $i => $butir): ?>
        <?php $ikon = $ikonLangkah[$i] ?? 'circle-check' ?>
        <li>
            <span class="ikon-wadah"><?= Ikon::svg($ikon, '', in_array($ikon, $berarah, true)) ?></span>
            <span class="langkah-nomor" aria-hidden="true"><?= $i + 1 ?></span>
            <span><?= esc($butir) ?></span>
        </li>
    <?php endforeach ?>
</ol>

<article class="prosa">
    <h2><?= lang('Depan.bahasaJudul') ?></h2>
    <ul>
        <?php foreach (lang('Depan.bahasaProgram') as $butir): ?>
            <li><?= esc($butir) ?></li>
        <?php endforeach ?>
    </ul>

    <h2 id="seb"><?= lang('Depan.sebJudul') ?></h2>
    <?php foreach (lang($sebBerlaku ? 'Depan.sebBerlaku' : 'Depan.seb') as $p): ?>
        <p><?= esc($p) ?></p>
    <?php endforeach ?>
    <p><a href="/seb"><?= lang('Depan.sebPetunjuk') ?></a></p>
    <p><a class="tautan-ikon" href="<?= esc($sebUnduh) ?>" rel="noopener noreferrer external"><?= Ikon::svg('external-link', '', true) ?><span><?= lang('Depan.sebTautan') ?></span></a></p>

    <h2><?= lang('Depan.tanyaJudul') ?></h2>
    <?php foreach (lang('Depan.tanya') as $t): ?>
        <details class="tanya">
            <summary><?= esc($t['tanya']) ?></summary>
            <p><?= esc($t['jawab']) ?></p>
        </details>
    <?php endforeach ?>

    <h2><?= lang('Lokandita.kontak') ?></h2>
    <p class="bantuan"><?= Ikon::svg('mail') ?> <span><?= lang('Depan.kontak') ?> <a href="mailto:<?= esc($kontak) ?>"><?= esc($kontak) ?></a></span></p>
    <p><a class="tautan-ikon" href="<?= esc($portal) ?>/"><?= Ikon::svg('arrow-right', '', true) ?><span><?= lang('Depan.kePortal') ?></span></a></p>
</article>
<?= $this->endSection() ?>
