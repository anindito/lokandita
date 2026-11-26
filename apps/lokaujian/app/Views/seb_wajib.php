<?php
/**
 * Penolakan filter SEB LokaUjian bagi mahasiswa (SPEC-LOKAUJIAN.md §9.1). Variabel: $judul,
 * $alasan (tanpa_header | tanpa_konfigurasi | kunci_konfigurasi | kunci_peramban).
 * Kalimatnya berbicara tentang berkas UJIAN, bukan mata kuliah: konfigurasi di sini per ujian.
 */
?>
<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<div class="isi-sempit">
    <div class="kartu" data-seb-wajib="<?= esc($alasan) ?>">
        <h1><?= esc($judul) ?></h1>
        <p><?= lang('SebUjian.wajib.teks') ?></p>
        <p><strong><?= lang('SebUjian.wajib.alasan.' . $alasan) ?></strong></p>
        <div class="baris-tombol">
            <a class="tombol" href="/beranda"><?= lang('SebUjian.wajib.beranda') ?></a>
            <a class="tombol tombol-sekunder" href="/seb"><?= lang('SebUjian.wajib.petunjuk') ?></a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
