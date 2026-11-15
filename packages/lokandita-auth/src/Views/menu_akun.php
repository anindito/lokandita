<?php
/**
 * Menu akun — sama persis di ketiga aplikasi (SPEC.md §4.5).
 * Variabel: $identitas (\Lokandita\Auth\Identity).
 *
 * Tanpa JavaScript menu ini tetap bisa dipakai: <details> adalah elemen bawaan
 * peramban, dan setiap aksi adalah form biasa. "Keluar" selalu butir terakhir.
 */
$auth    = rtrim((string) (getenv('AUTH_PUBLIC_URL') ?: getenv('APP_URL')), '/');
$locale  = service('request')->getLocale();
$kembali = '/' . ltrim(service('request')->getUri()->getPath(), '/');
$wajib   = $identitas->globalRole === 'mahasiswa';
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
        <a class="akun-butir" href="<?= esc($auth) ?>/ganti-password"><?= lang('Lokandita.gantiSandi') ?></a>

        <form class="akun-bagian" method="post" action="/preferensi">
            <?= csrf_field() ?>
            <input type="hidden" name="kembali" value="<?= esc($kembali) ?>">
            <p class="akun-label"><?= lang('Lokandita.bahasa') ?></p>
            <div class="akun-bahasa">
                <?php foreach (['id' => 'bahasaId', 'en' => 'bahasaEn', 'ar' => 'bahasaAr'] as $kode => $kunci): ?>
                    <button type="submit" name="locale" value="<?= $kode ?>" lang="<?= $kode ?>"
                            aria-pressed="<?= $kode === $locale ? 'true' : 'false' ?>"><?= lang('Lokandita.' . $kunci) ?></button>
                <?php endforeach ?>
            </div>
        </form>

        <form class="akun-bagian" method="post" action="/preferensi">
            <?= csrf_field() ?>
            <input type="hidden" name="kembali" value="<?= esc($kembali) ?>">
            <button type="submit" class="akun-saklar" name="idle_logout" value="<?= $identitas->idleLogout ? '0' : '1' ?>"
                    role="switch" aria-checked="<?= $identitas->idleLogout ? 'true' : 'false' ?>" <?= $wajib ? 'disabled' : '' ?>>
                <span class="akun-saklar-tanda" aria-hidden="true"></span>
                <span><?= lang('Lokandita.keluarOtomatis') ?></span>
            </button>
            <?php if ($wajib): ?>
                <p class="petunjuk"><?= lang('Lokandita.wajibMahasiswa') ?></p>
            <?php endif ?>
        </form>

        <form class="akun-form" method="post" action="/keluar" data-form-keluar>
            <?= csrf_field() ?>
            <button type="submit" class="akun-butir akun-keluar"><?= lang('Lokandita.keluar') ?></button>
        </form>
    </div>
</details>
