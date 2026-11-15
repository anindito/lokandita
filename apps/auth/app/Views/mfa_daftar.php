<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<div class="isi-sempit">
    <div class="kartu">
        <h1><?= lang('Auth.mfa.daftarJudul') ?></h1>
        <p><?= lang('Auth.mfa.daftarPengantar') ?></p>
        <ol>
            <li><?= lang('Auth.mfa.langkah1') ?></li>
            <li><?= lang('Auth.mfa.langkah2') ?></li>
            <li><?= lang('Auth.mfa.langkah3') ?></li>
        </ol>
        <div class="qr" role="img" aria-label="<?= lang('Auth.mfa.qrAlt') ?>"><?= $qr ?></div>
        <p class="petunjuk"><?= lang('Auth.mfa.kunciManual') ?></p>
        <p class="kode-besar" data-rahasia><?= esc(trim(chunk_split($secret, 4, ' '))) ?></p>
        <form method="post" action="/login/mfa-daftar" autocomplete="off">
            <?= csrf_field() ?>
            <label for="kode"><?= lang('Auth.mfa.kode') ?></label>
            <input type="text" id="kode" name="kode" class="ltr kode-besar" inputmode="numeric" autocomplete="one-time-code"
                   pattern="[0-9 ]*" required autofocus maxlength="10">
            <button type="submit" class="tombol tombol-lebar"><?= lang('Auth.mfa.aktifkan') ?></button>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
