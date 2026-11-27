<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('kepala') ?><?= view('_kepala_bank') ?><?= $this->endSection() ?>
<?= $this->section('isi') ?>
<?php
/**
 * TINJAUAN sesudah hasil dirilis dengan kunci (SPEC-LOKAUJIAN.md §11), di dalam SEB: tiap soal
 * menurut urutan mahasiswa itu, jawabannya sendiri, dan kunci soal yang dinilai otomatis. Isian
 * dan uraian dinilai dosen di luar aplikasi dan tidak punya kunci. Hanya untuk dibaca.
 * Variabel: $sesi (Pengerjaan::tinjau): soal[], kelompok[], score, poin_objektif.
 */
use App\Libraries\Markdown;
use App\Libraries\Nilai;

$terakhirKelompok = null;
?>
<div id="ujian" data-tinjauan>
<p><a href="/ujian-saya/<?= (int) $sesi['sesi_id'] ?>"><span class="panah-balik" aria-hidden="true"></span> <span dir="auto"><?= esc($sesi['title']) ?></span></a></p>
<h1><?= lang('Hasil.tinjau.judul') ?></h1>
<div class="kartu" data-nilai>
    <p><strong><?= lang('Hasil.nilai.objektif', [esc(Nilai::tampil($sesi['score'] ?? 0)), esc(Nilai::tampil($sesi['poin_objektif']))]) ?></strong></p>
    <p class="petunjuk"><?= lang('Hasil.nilai.manual') ?></p>
</div>
<?php foreach ($sesi['soal'] as $n => $q): ?>
    <?php
    $j      = $q['jawab'];
    $oto    = in_array($q['type'], Nilai::OTOMATIS, true);
    $kosong = $j === null || ($j['option_id'] === null && $j['benar'] === null && $j['text'] === '');
    $benar  = false;
    if ($q['type'] === 'benar_salah') {
        $benar = ! $kosong && $j['benar'] === $q['answer'];
    } elseif ($q['type'] === 'pilihan_ganda') {
        foreach ($q['options'] as $o) {
            $benar = $benar || ($o['correct'] && ! $kosong && $j['option_id'] === $o['id']);
        }
    }
    ?>
    <?php if ($q['group_id'] !== null && $q['group_id'] !== $terakhirKelompok): ?>
        <?php $g = $sesi['kelompok'][$q['group_id']]; ?>
        <div class="ujian-bersama">
            <?php if ($g['text'] !== ''): ?><div class="soal-teks" dir="auto"><?= Markdown::html($g['text']) ?></div><?php endif ?>
            <?php if ($g['image'] !== null): ?><figure class="soal-gambar"><?= view('_gambar', ['gambar' => $g['image']]) ?></figure><?php endif ?>
        </div>
    <?php endif ?>
    <?php $terakhirKelompok = $q['group_id']; ?>
    <article class="soal" data-tinjau-soal="<?= (int) $q['id'] ?>" data-hasil="<?= $oto ? ($benar ? 'benar' : 'salah') : 'manual' ?>">
        <div class="soal-kepala">
            <span class="soal-nomor"><?= lang('Bank.soal.nomor', [$n + 1]) ?></span>
            <span class="lencana"><?= lang('Bank.tipe.' . $q['type']) ?></span>
            <?php if ($oto): ?>
                <span class="lencana"><?= lang($benar ? 'Hasil.tinjau.benar' : 'Hasil.tinjau.salah') ?> · <?= lang('Hasil.tinjau.poin', [esc(Nilai::tampil($benar ? $q['points'] : 0)), esc(Nilai::tampil($q['points']))]) ?></span>
            <?php else: ?>
                <span class="redup"><?= lang('Hasil.tinjau.dinilaiDosen', [esc(Nilai::tampil($q['points']))]) ?></span>
            <?php endif ?>
        </div>
        <div class="soal-teks" dir="auto"><?= Markdown::html($q['text']) ?></div>
        <?php if ($q['image'] !== null): ?><figure class="soal-gambar"><?= view('_gambar', ['gambar' => $q['image']]) ?></figure><?php endif ?>
        <?php if ($q['type'] === 'benar_salah'): ?>
            <p data-jawab><?= lang('Hasil.tinjau.jawabanAnda') ?>: <strong><?= $kosong ? lang('Hasil.tinjau.kosong') : lang($j['benar'] ? 'Bank.benar' : 'Bank.salah') ?></strong></p>
            <p data-kunci><?= lang('Hasil.tinjau.kunci') ?>: <strong><?= lang($q['answer'] ? 'Bank.benar' : 'Bank.salah') ?></strong></p>
        <?php elseif ($q['type'] === 'pilihan_ganda'): ?>
            <?php if ($kosong): ?><p data-jawab><?= lang('Hasil.tinjau.jawabanAnda') ?>: <strong><?= lang('Hasil.tinjau.kosong') ?></strong></p><?php endif ?>
            <?php foreach ($q['options'] as $i => $o): ?>
                <div class="opsi"<?= $o['correct'] ? ' data-kunci' : '' ?><?= ! $kosong && $j['option_id'] === $o['id'] ? ' data-jawab' : '' ?>>
                    <span class="opsi-huruf ltr"><?= chr(65 + $i) ?>.</span>
                    <span class="opsi-isi" dir="auto">
                        <?= $o['text'] === '' ? '' : Markdown::html($o['text']) ?>
                        <?php if ($o['image'] !== null): ?><?= view('_gambar', ['gambar' => $o['image']]) ?><?php endif ?>
                        <?php if (! $kosong && $j['option_id'] === $o['id']): ?><span class="lencana"><?= lang('Hasil.tinjau.jawabanAnda') ?></span><?php endif ?>
                        <?php if ($o['correct']): ?><span class="lencana"><?= lang('Hasil.tinjau.kunci') ?></span><?php endif ?>
                    </span>
                </div>
            <?php endforeach ?>
        <?php else: ?>
            <p class="redup"><?= lang('Hasil.tinjau.jawabanAnda') ?>:</p>
            <div class="kartu" dir="auto" data-jawab><?= $kosong ? lang('Hasil.tinjau.kosong') : nl2br(esc($j['text'])) ?></div>
        <?php endif ?>
    </article>
<?php endforeach ?>
</div>
<?= $this->endSection() ?>
