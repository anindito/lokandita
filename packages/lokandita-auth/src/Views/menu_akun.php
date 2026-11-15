<?php
/**
 * Menu akun — sama persis di ketiga aplikasi (SPEC.md §4.5).
 * Variabel: $identitas (\Lokandita\Auth\Identity).
 * Tanpa JavaScript menu ini tetap bisa dipakai: <details> adalah elemen bawaan peramban.
 */
$auth = rtrim((string) (getenv('AUTH_PUBLIC_URL') ?: getenv('APP_URL')), '/');
?>
<details class="akun" data-menu-akun>
    <summary class="akun-tombol" aria-label="<?= lang('Lokandita.menuAkun') ?>">
        <span class="akun-inisial" aria-hidden="true"><?= esc($identitas->inisial()) ?></span>
        <span class="akun-nama">
            <strong><?= esc($identitas->nama) ?></strong>
            <small dir="ltr"><?= esc($identitas->nomorInduk) ?></small>
        </span>
    </summary>
    <div class="akun-panel">
        <p class="akun-siapa">
            <strong><?= esc($identitas->nama) ?></strong>
            <span><?= lang('Lokandita.peran.' . $identitas->globalRole) ?></span>
        </p>
        <a class="akun-butir" href="<?= esc($auth, 'attr') ?>/ganti-password"><?= lang('Lokandita.gantiSandi') ?></a>
        <form class="akun-form" method="post" action="/keluar">
            <?= csrf_field() ?>
            <button type="submit" class="akun-butir akun-keluar"><?= lang('Lokandita.keluar') ?></button>
        </form>
    </div>
</details>
