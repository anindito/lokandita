<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<div class="isi-sempit">
    <div class="kartu">
        <h1><?= esc($judul) ?></h1>
        <p><?= esc($teks) ?></p>
        <div class="baris-tombol">
            <a class="tombol tombol-sekunder" href="<?= esc((string) getenv('PORTAL_URL')) ?>"><?= lang('Lokandita.kePortal') ?></a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
