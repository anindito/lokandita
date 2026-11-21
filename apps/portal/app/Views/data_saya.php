<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<article class="prosa">
    <p><a class="panah-balik" href="/beranda"><?= lang('Lokandita.beranda') ?></a></p>
    <h1><?= lang('Portal.data.judul') ?></h1>
    <p><?= lang('Portal.data.penjelasan') ?></p>
    <ul>
        <?php foreach ($tautan as $nama => $url): ?>
            <li><a href="<?= esc($url) ?>"><?= lang('Portal.data.unduhDari', [esc($nama)]) ?></a></li>
        <?php endforeach ?>
    </ul>
    <p class="petunjuk"><?= lang('Portal.data.catatan') ?></p>
</article>
<?= $this->endSection() ?>
