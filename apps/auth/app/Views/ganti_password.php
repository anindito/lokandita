<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<?php
$galatUntuk = static function (string $kolom) use ($galat): string {
    $html = '';
    foreach ($galat[$kolom] ?? [] as $teks) {
        $html .= '<p class="galat-kolom" role="alert">' . esc($teks) . '</p>';
    }

    return $html;
};
?>
<div class="isi-sempit">
    <div class="kartu">
        <h1><?= lang('Auth.gantiSandi') ?></h1>
        <?php if ($wajib): ?>
            <p><?= lang('Auth.wajibGanti') ?></p>
        <?php endif ?>
        <form method="post" action="/ganti-password">
            <?= csrf_field() ?>
            <?php if ($next !== null): ?>
                <input type="hidden" name="next" value="<?= esc($next) ?>">
            <?php endif ?>
            <label for="password_lama"><?= lang('Auth.sandiLama') ?></label>
            <input type="password" id="password_lama" name="password_lama" class="ltr" autocomplete="current-password" required maxlength="200">
            <?= $galatUntuk('password_lama') ?>
            <label for="password_baru"><?= lang('Auth.sandiBaru') ?></label>
            <input type="password" id="password_baru" name="password_baru" class="ltr" autocomplete="new-password" required minlength="10" maxlength="200">
            <p class="petunjuk"><?= lang('Auth.sandiAturan', [\App\Libraries\Passwords::MIN]) ?></p>
            <?= $galatUntuk('password_baru') ?>
            <label for="password_ulang"><?= lang('Auth.sandiUlang') ?></label>
            <input type="password" id="password_ulang" name="password_ulang" class="ltr" autocomplete="new-password" required maxlength="200">
            <?= $galatUntuk('password_ulang') ?>
            <button type="submit" class="tombol tombol-lebar"><?= lang('Auth.simpanSandi') ?></button>
        </form>
        <p class="petunjuk"><?= lang('Auth.sesiLainDicabut') ?></p>
    </div>
</div>
<?= $this->endSection() ?>
