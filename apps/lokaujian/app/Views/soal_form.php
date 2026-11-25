<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('kepala') ?><?= view('_kepala_bank') ?><?= $this->endSection() ?>
<?= $this->section('isi') ?>
<?php
/**
 * Form satu soal (SPEC-LOKAUJIAN.md §5.1). Variabel: $matkul, $soal (null = baru), $kelompok
 * (null = soal lepas), $isian (App\Controllers\Soal::isianDari), $galat (list of string).
 * Form biasa: salin dan tempel tidak diblokir di sini (§9.2 hanya untuk halaman ujian mahasiswa).
 */
use App\Libraries\Aturan;

$dasar  = '/mata-kuliah/' . (int) $matkul['id'] . '/bank';
$tipe   = $isian['type'];
$balik  = $kelompok !== null ? $dasar . '/kelompok/' . (int) $kelompok['id'] : ($soal !== null ? $dasar . '/soal/' . (int) $soal['id'] : $dasar);
?>
<?= view('_nav', ['akses' => $akses]) ?>
<p><a href="<?= $balik ?>"><span class="panah-balik" aria-hidden="true"></span> <?= lang($kelompok !== null ? 'Bank.kelompok.kembali' : 'Bank.kembali') ?></a></p>
<h1><?= lang($soal === null ? 'Bank.soal.baru' : 'Bank.soal.sunting') ?>: <?= lang('Bank.tipe.' . $tipe) ?></h1>
<?php if ($kelompok !== null): ?><p class="redup"><?= lang('Bank.soal.dalamKelompok') ?></p><?php endif ?>

<?= view('_galat_form', ['galat' => $galat]) ?>

<form method="post" enctype="multipart/form-data" action="<?= $soal === null ? $dasar . '/soal' : $dasar . '/soal/' . (int) $soal['id'] ?>" class="kartu">
    <?= csrf_field() ?>
    <input type="hidden" name="type" value="<?= esc($tipe) ?>">
    <?php if ($soal === null && $kelompok !== null): ?><input type="hidden" name="kelompok" value="<?= (int) $kelompok['id'] ?>"><?php endif ?>

    <label for="soal-teks"><?= lang('Bank.medan.teks') ?></label>
    <textarea id="soal-teks" name="text" rows="6" maxlength="<?= Aturan::TEKS_SOAL_MAKS ?>" required dir="auto"><?= esc($isian['text']) ?></textarea>
    <p class="petunjuk"><?= lang('Bank.medan.markdown') ?></p>

    <label for="soal-poin"><?= lang('Bank.medan.poin') ?></label>
    <input type="text" id="soal-poin" name="points" inputmode="decimal" value="<?= esc($isian['points']) ?>" required class="ltr">
    <p class="petunjuk"><?= lang('Bank.medan.poinInfo') ?></p>

    <fieldset>
        <legend><?= lang('Bank.medan.gambarSoal') ?></legend>
        <?= view('_isian_gambar', ['id' => 'soal-gambar', 'medan' => 'gambar', 'namaAlt' => 'alt', 'namaHapus' => 'hapus_gambar', 'alt' => $isian['alt'], 'gambar' => $isian['gambar']]) ?>
        <p class="petunjuk"><?= lang('Bank.medan.gambarInfo') ?></p>
    </fieldset>

    <?php if ($tipe === 'benar_salah'): ?>
        <fieldset>
            <legend><?= lang('Bank.medan.kunci') ?></legend>
            <label class="sebaris"><input type="radio" name="answer" value="benar"<?= $isian['answer'] === 'benar' ? ' checked' : '' ?> required> <?= lang('Bank.benar') ?></label>
            <label class="sebaris"><input type="radio" name="answer" value="salah"<?= $isian['answer'] === 'salah' ? ' checked' : '' ?>> <?= lang('Bank.salah') ?></label>
        </fieldset>
    <?php elseif ($tipe === 'pilihan_ganda'): ?>
        <fieldset>
            <legend><?= lang('Bank.medan.opsi') ?></legend>
            <p class="petunjuk"><?= lang('Bank.medan.opsiInfo', [Aturan::OPSI_MIN, Aturan::OPSI_MAKS]) ?></p>
            <?php foreach ($isian['opsi'] as $i => $o): ?>
                <div class="baris-opsi" data-opsi="<?= (int) $i ?>">
                    <label class="sebaris"><input type="radio" name="benar" value="<?= (int) $i ?>"<?= $isian['benar'] === $i ? ' checked' : '' ?>> <strong><?= lang('Bank.medan.opsiKe', [$i + 1]) ?></strong> — <?= lang('Bank.medan.opsiBenar') ?></label>
                    <label for="opsi-teks-<?= (int) $i ?>"><?= lang('Bank.medan.opsiTeks') ?></label>
                    <textarea id="opsi-teks-<?= (int) $i ?>" name="opsi_teks[<?= (int) $i ?>]" rows="2" maxlength="<?= Aturan::TEKS_OPSI_MAKS ?>" dir="auto"><?= esc($o['text']) ?></textarea>
                    <?= view('_isian_gambar', ['id' => 'opsi-gambar-' . $i, 'medan' => 'opsi_gambar_' . $i, 'namaAlt' => 'opsi_alt[' . $i . ']', 'namaHapus' => 'opsi_hapus_gambar[' . $i . ']', 'alt' => $o['alt'], 'gambar' => $o['gambar']]) ?>
                </div>
            <?php endforeach ?>
        </fieldset>
    <?php else: ?>
        <p class="petunjuk"><?= lang($tipe === 'isian' ? 'Bank.medan.isianInfo' : 'Bank.medan.uraianInfo') ?></p>
    <?php endif ?>

    <div class="baris-tombol">
        <button type="submit" class="tombol"><?= lang('Bank.simpan') ?></button>
        <a href="<?= $balik ?>"><?= lang('Bank.batal') ?></a>
    </div>
</form>
<?= $this->endSection() ?>
