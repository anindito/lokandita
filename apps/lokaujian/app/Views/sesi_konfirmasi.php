<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<?php
/**
 * Konfirmasi sebelum tambahan waktu, buka ulang, atau rilis kunci selagi kelas lain belum selesai
 * (SPEC-LOKAUJIAN.md §7.3, §11). Belum ada yang berubah ketika halaman ini tampil; tombolnya
 * mengirim ulang isian yang sama dengan yakin=1.
 * Variabel: $sesi, $judul, $kalimat (list teks yang SUDAH di-escape; yang pertama kalimatnya,
 * sisanya butir yang disebut), $aksi, $isian (nama => nilai atau list nilai), $tombol.
 */
$butir = array_slice($kalimat, 1);
?>
<?= view('_nav', ['akses' => $akses]) ?>
<p><a href="/sesi/<?= (int) $sesi['id'] ?>"><span class="panah-balik" aria-hidden="true"></span> <?= lang('Sesi.kembali') ?></a></p>
<h1><?= esc($judul) ?></h1>
<form method="post" action="<?= esc($aksi, 'attr') ?>" class="kartu" data-konfirmasi-halaman>
    <?= csrf_field() ?>
    <input type="hidden" name="yakin" value="1">
    <?php foreach ($isian as $nama => $nilai): ?>
        <?php foreach ((array) $nilai as $v): ?>
            <input type="hidden" name="<?= esc($nama, 'attr') ?>" value="<?= esc($v, 'attr') ?>">
        <?php endforeach ?>
    <?php endforeach ?>
    <p><strong><?= $kalimat[0] ?></strong></p>
    <?php if ($butir !== []): ?>
        <ul data-disebut>
            <?php foreach ($butir as $b): ?><li dir="auto"><?= $b ?></li><?php endforeach ?>
        </ul>
    <?php endif ?>
    <p class="petunjuk"><?= lang('Hasil.konfirmasi.info') ?></p>
    <div class="baris-tombol">
        <button type="submit" class="tombol" data-yakin><?= esc($tombol) ?></button>
        <a class="tombol tombol-sekunder" href="/sesi/<?= (int) $sesi['id'] ?>"><?= lang('Hasil.konfirmasi.batal') ?></a>
    </div>
</form>
<?= $this->endSection() ?>
