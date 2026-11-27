<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('kepala') ?><?= view('_kepala_bank') ?>
<script src="/js/konfirmasi.js?v=<?= esc(substr((string) getenv('LOKANDITA_COMMIT'), 0, 12)) ?>" defer></script>
<?= $this->endSection() ?>
<?= $this->section('isi') ?>
<?php
/**
 * Koreksi kunci atau poin sesudah ujian (SPEC-LOKAUJIAN.md §11). Halaman ini MENAMPILKAN kunci;
 * hanya yang mengelola mata kuliahnya yang sampai ke sini. Teks dan opsi tidak bisa diubah.
 * Variabel: $matkul, $ujian (baris exams), $butir (Ujian::beku), $pengerjaan (jumlah).
 */
use App\Libraries\Markdown;
use App\Libraries\Nilai;

$ini  = '/mata-kuliah/' . (int) $matkul['id'] . '/ujian/' . (int) $ujian['id'];
$soal = [];
foreach ($butir as $b) {
    foreach ($b['jenis'] === 'soal' ? [$b['soal']] : $b['kelompok']['questions'] as $q) {
        $soal[] = $q;
    }
}
?>
<?= view('_nav', ['akses' => $akses]) ?>
<p><a href="<?= $ini ?>"><span class="panah-balik" aria-hidden="true"></span> <span dir="auto"><?= esc($ujian['title']) ?></span></a></p>
<h1><?= lang('Hasil.koreksi.judul') ?></h1>
<div class="kartu">
    <p><?= lang('Hasil.koreksi.info') ?></p>
    <p class="petunjuk" data-jumlah-pengerjaan><?= lang('Hasil.koreksi.pengerjaan', [(int) $pengerjaan]) ?></p>
</div>
<?php foreach ($soal as $n => $q): ?>
    <form method="post" action="<?= $ini ?>/koreksi/<?= (int) $q['id'] ?>" class="soal" data-koreksi="<?= (int) $q['id'] ?>" data-konfirmasi="<?= esc(lang('Hasil.koreksi.yakin'), 'attr') ?>">
        <?= csrf_field() ?>
        <div class="soal-kepala">
            <span class="soal-nomor"><?= lang('Bank.soal.nomor', [$n + 1]) ?></span>
            <span class="lencana"><?= lang('Bank.tipe.' . $q['type']) ?></span>
        </div>
        <div class="soal-teks" dir="auto"><?= Markdown::html($q['text']) ?></div>
        <?php if ($q['type'] === 'benar_salah'): ?>
            <fieldset class="soal-jawab">
                <legend class="redup"><?= lang('Hasil.koreksi.kunci') ?></legend>
                <label class="opsi"><input type="radio" name="kunci" value="benar"<?= $q['answer'] ? ' checked' : '' ?>> <span><?= lang('Bank.benar') ?></span></label>
                <label class="opsi"><input type="radio" name="kunci" value="salah"<?= $q['answer'] ? '' : ' checked' ?>> <span><?= lang('Bank.salah') ?></span></label>
            </fieldset>
        <?php elseif ($q['type'] === 'pilihan_ganda'): ?>
            <fieldset class="soal-jawab">
                <legend class="redup"><?= lang('Hasil.koreksi.kunci') ?></legend>
                <?php foreach ($q['options'] as $i => $o): ?>
                    <label class="opsi">
                        <input type="radio" name="kunci" value="<?= (int) $o['id'] ?>"<?= $o['correct'] ? ' checked' : '' ?>>
                        <span class="opsi-huruf ltr"><?= chr(65 + $i) ?>.</span>
                        <span class="opsi-isi" dir="auto">
                            <?= $o['text'] === '' ? '' : Markdown::html($o['text']) ?>
                            <?php if ($o['image'] !== null): ?><?= view('_gambar', ['gambar' => $o['image']]) ?><?php endif ?>
                        </span>
                    </label>
                <?php endforeach ?>
            </fieldset>
        <?php else: ?>
            <p class="petunjuk"><?= lang('Hasil.koreksi.tanpaKunci') ?></p>
        <?php endif ?>
        <label for="poin-<?= (int) $q['id'] ?>"><?= lang('Hasil.koreksi.poin') ?></label>
        <input type="text" inputmode="decimal" id="poin-<?= (int) $q['id'] ?>" name="poin" class="ltr" value="<?= esc(Nilai::tampil($q['points'])) ?>" required>
        <div class="baris-tombol"><button type="submit" class="tombol tombol-sekunder"><?= lang('Hasil.koreksi.simpan') ?></button></div>
    </form>
<?php endforeach ?>
<?= $this->endSection() ?>
