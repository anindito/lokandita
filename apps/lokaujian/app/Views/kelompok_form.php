<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('kepala') ?><?= view('_kepala_bank') ?><?= $this->endSection() ?>
<?= $this->section('isi') ?>
<?php
/**
 * Form kepala kelompok soal (SPEC-LOKAUJIAN.md §5.2): teks bersama dan/atau satu gambar.
 * Variabel: $matkul, $kelompok (null = baru), $isian (text, alt, gambar), $galat.
 */
use App\Libraries\Aturan;

$dasar = '/mata-kuliah/' . (int) $matkul['id'] . '/bank';
$balik = $kelompok !== null ? $dasar . '/kelompok/' . (int) $kelompok['id'] : $dasar;
?>
<?= view('_nav', ['akses' => $akses]) ?>
<p><a href="<?= $balik ?>"><span class="panah-balik" aria-hidden="true"></span> <?= lang($kelompok !== null ? 'Bank.kelompok.kembali' : 'Bank.kembali') ?></a></p>
<h1><?= lang($kelompok === null ? 'Bank.kelompok.baru' : 'Bank.kelompok.sunting') ?></h1>
<p class="redup"><?= lang('Bank.kelompok.penjelasan', [Aturan::SOAL_KELOMPOK_MAKS]) ?></p>

<?= view('_galat_form', ['galat' => $galat]) ?>

<form method="post" enctype="multipart/form-data" action="<?= $kelompok === null ? $dasar . '/kelompok' : $dasar . '/kelompok/' . (int) $kelompok['id'] ?>" class="kartu">
    <?= csrf_field() ?>
    <label for="kelompok-teks"><?= lang('Bank.medan.teksBersama') ?></label>
    <textarea id="kelompok-teks" name="text" rows="10" maxlength="<?= Aturan::TEKS_KELOMPOK_MAKS ?>" dir="auto"><?= esc($isian['text']) ?></textarea>
    <p class="petunjuk"><?= lang('Bank.medan.markdown') ?> <?= lang('Bank.medan.teksBersamaInfo') ?></p>

    <fieldset>
        <legend><?= lang('Bank.medan.gambarBersama') ?></legend>
        <?= view('_isian_gambar', ['id' => 'kelompok-gambar', 'medan' => 'gambar', 'namaAlt' => 'alt', 'namaHapus' => 'hapus_gambar', 'alt' => $isian['alt'], 'gambar' => $isian['gambar']]) ?>
        <p class="petunjuk"><?= lang('Bank.medan.gambarInfo') ?></p>
    </fieldset>

    <div class="baris-tombol">
        <button type="submit" class="tombol"><?= lang('Bank.simpan') ?></button>
        <a href="<?= $balik ?>"><?= lang('Bank.batal') ?></a>
    </div>
</form>
<?= $this->endSection() ?>
