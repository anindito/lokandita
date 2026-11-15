<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<div class="isi-sempit">
    <div class="kartu">
        <h1><?= lang('Auth.mfa.kodeJudul') ?></h1>
        <p class="redup"><?= lang('Auth.mfa.kodePengantar') ?></p>
        <form method="post" action="/login/kode" autocomplete="off">
            <?= csrf_field() ?>
            <label for="kode"><?= lang('Auth.mfa.kode') ?></label>
            <input type="text" id="kode" name="kode" class="ltr kode-besar" inputmode="text" autocomplete="one-time-code"
                   spellcheck="false" required autofocus maxlength="20">
            <p class="petunjuk"><?= lang('Auth.mfa.kodePetunjuk') ?></p>
            <button type="submit" class="tombol tombol-lebar"><?= lang('Auth.mfa.periksa') ?></button>
        </form>
        <p class="petunjuk"><a href="/login"><?= lang('Auth.mfa.ulangDariAwal') ?></a></p>
    </div>
</div>
<?= $this->endSection() ?>
