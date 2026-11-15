<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<div class="isi-sempit">
    <div class="kartu">
        <h1><?= lang('Auth.mfa.cadanganJudul') ?></h1>
        <p><?= lang('Auth.mfa.cadanganPengantar') ?></p>
        <ul class="daftar-kode">
            <?php foreach ($kode as $k): ?>
                <li class="kode-besar" data-kode-cadangan><?= esc($k) ?></li>
            <?php endforeach ?>
        </ul>
        <p class="petunjuk"><?= lang('Auth.mfa.cadanganPeringatan') ?></p>
        <div class="baris-tombol">
            <a class="tombol" href="<?= esc($lanjut, 'attr') ?>"><?= lang('Auth.mfa.sudahDisimpan') ?></a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
