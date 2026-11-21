<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<h1><?= lang('Lokandita.halo', [esc($identitas->nama)]) ?></h1>
<p class="redup"><?= lang('Lokandita.peran.' . $identitas->globalRole) ?> · <span class="ltr"><?= esc($identitas->nomorInduk) ?></span></p>

<h2><?= lang('Portal.beranda.aplikasi') ?></h2>
<?php if ($kartu === [] && $admin === null): ?>
    <div class="kartu">
        <p><?= lang('Portal.beranda.kosong') ?>
            <a href="mailto:<?= esc($kontak) ?>"><?= esc($kontak) ?></a></p>
    </div>
<?php else: ?>
    <ul class="petak">
        <?php foreach ($kartu as $kode => $a): ?>
            <li>
                <?php if ($a['url'] !== null): ?>
                    <a class="kartu-aplikasi" href="<?= esc($a['url']) ?>/beranda" data-aplikasi="<?= esc($kode) ?>">
                        <strong><?= esc($a['nama']) ?></strong>
                        <span><?= lang("Portal.aplikasi.{$kode}") ?></span>
                    </a>
                <?php else: ?>
                    <div class="kartu-aplikasi kartu-aplikasi-segera" data-aplikasi="<?= esc($kode) ?>">
                        <strong><?= esc($a['nama']) ?></strong>
                        <span class="lencana"><?= lang('Lokandita.segera') ?></span>
                        <span><?= lang("Portal.aplikasi.{$kode}") ?></span>
                    </div>
                <?php endif ?>
            </li>
        <?php endforeach ?>
        <?php if ($admin !== null): ?>
            <li>
                <a class="kartu-aplikasi" href="<?= esc($admin) ?>" data-aplikasi="auth">
                    <strong>LokaAuth</strong>
                    <span><?= lang('Portal.beranda.admin') ?></span>
                </a>
            </li>
        <?php endif ?>
    </ul>
<?php endif ?>

<p><a href="/data-saya"><?= lang('Portal.data.judul') ?></a></p>
<?= $this->endSection() ?>
