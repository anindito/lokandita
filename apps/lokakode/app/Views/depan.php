<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<section class="pembuka">
    <h1><?= lang('Depan.tajuk') ?></h1>
    <p><?= lang('Depan.pengantar') ?></p>
    <a class="tombol" href="/beranda" rel="nofollow"><?= lang('Lokandita.masuk') ?></a>
    <p class="petunjuk"><?= lang('Depan.tanpaDaftar') ?></p>
</section>

<h2><?= lang('Depan.fiturJudul') ?></h2>
<ul class="petak">
    <?php foreach (lang('Depan.fitur') as $f): ?>
        <li class="kartu-aplikasi">
            <strong><?= esc($f['judul']) ?></strong>
            <span><?= esc($f['isi']) ?></span>
        </li>
    <?php endforeach ?>
</ul>

<article class="prosa">
    <h2><?= lang('Depan.caraJudul') ?></h2>
    <ol>
        <?php foreach (lang('Depan.cara') as $butir): ?>
            <li><?= esc($butir) ?></li>
        <?php endforeach ?>
    </ol>

    <h2><?= lang('Depan.bahasaJudul') ?></h2>
    <ul>
        <?php foreach (lang('Depan.bahasaProgram') as $butir): ?>
            <li><?= esc($butir) ?></li>
        <?php endforeach ?>
    </ul>

    <h2 id="seb"><?= lang('Depan.sebJudul') ?></h2>
    <?php foreach (lang('Depan.seb') as $p): ?>
        <p><?= esc($p) ?></p>
    <?php endforeach ?>
    <p><a href="<?= esc($sebUnduh) ?>" rel="noopener noreferrer external"><?= lang('Depan.sebTautan') ?></a></p>

    <h2><?= lang('Depan.tanyaJudul') ?></h2>
    <?php foreach (lang('Depan.tanya') as $t): ?>
        <details class="tanya">
            <summary><?= esc($t['tanya']) ?></summary>
            <p><?= esc($t['jawab']) ?></p>
        </details>
    <?php endforeach ?>

    <h2><?= lang('Lokandita.kontak') ?></h2>
    <p><?= lang('Depan.kontak') ?> <a href="mailto:<?= esc($kontak) ?>"><?= esc($kontak) ?></a></p>
    <p><a href="<?= esc($portal) ?>/"><?= lang('Depan.kePortal') ?></a></p>
</article>
<?= $this->endSection() ?>
