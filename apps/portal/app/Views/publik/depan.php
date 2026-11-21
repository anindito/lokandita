<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<section class="pembuka">
    <h1><?= lang('Portal.depan.tajuk') ?></h1>
    <p><?= lang('Portal.depan.pengantar') ?></p>
    <a class="tombol" href="/beranda" rel="nofollow"><?= lang('Lokandita.masuk') ?></a>
    <p class="petunjuk"><?= lang('Portal.depan.tanpaDaftar') ?></p>
</section>

<h2><?= lang('Portal.depan.aplikasi') ?></h2>
<ul class="petak">
    <?php foreach ($aplikasi as $kode => $a): ?>
        <li>
            <?php if ($a['url'] !== null): ?>
                <a class="kartu-aplikasi" href="<?= esc($a['url']) ?>/">
                    <strong><?= esc($a['nama']) ?></strong>
                    <span><?= lang("Portal.aplikasi.{$kode}") ?></span>
                </a>
            <?php else: ?>
                <div class="kartu-aplikasi kartu-aplikasi-segera">
                    <strong><?= esc($a['nama']) ?></strong>
                    <span class="lencana"><?= lang('Lokandita.segera') ?></span>
                    <span><?= lang("Portal.aplikasi.{$kode}") ?></span>
                </div>
            <?php endif ?>
        </li>
    <?php endforeach ?>
</ul>

<h2><?= lang('Portal.depan.caraJudul') ?></h2>
<ol>
    <?php foreach (lang('Portal.depan.cara') as $butir): ?>
        <li><?= esc($butir) ?></li>
    <?php endforeach ?>
</ol>
<p><?= lang('Portal.depan.bantuan') ?> <a href="/kontak"><?= lang('Portal.nav.kontak') ?></a>.</p>
<?= $this->endSection() ?>
