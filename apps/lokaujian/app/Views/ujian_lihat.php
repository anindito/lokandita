<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('kepala') ?><?= view('_kepala_bank') ?><?= $this->endSection() ?>
<?= $this->section('isi') ?>
<?php
/**
 * Pratinjau satu ujian (SPEC-LOKAUJIAN.md §5.4): butirnya ditampilkan dengan partial yang sama
 * dengan halaman mahasiswa, dalam urutan ujian. Variabel: $matkul, $ujian (Ujian::lihat).
 */
use App\Controllers\Soal;
use App\Libraries\Markdown;

$dasar = '/mata-kuliah/' . (int) $matkul['id'];
$ini   = $dasar . '/ujian/' . (int) $ujian['id'];
$r     = $ujian['ringkasan'];
$angka = static fn (float $n): string => Soal::poinTampil(number_format($n, 2, '.', ''));
$nomor = 1;
?>
<?= view('_nav', ['akses' => $akses]) ?>
<p><a href="<?= $dasar ?>/ujian"><span class="panah-balik" aria-hidden="true"></span> <?= lang('Bank.ujian.kembali') ?></a></p>
<h1 dir="auto"><?= esc($ujian['title']) ?></h1>
<p>
    <span class="lencana"><?= lang($ujian['kind'] === 'kuis' ? 'Bank.ujian.jenisKuis' : 'Bank.ujian.jenisUjian') ?></span>
    <span class="lencana"><?= lang('Bank.ujian.status_' . $ujian['status']) ?></span>
    <span class="pratinjau-pita"><?= lang('Bank.pratinjau') ?></span>
</p>

<div class="kartu">
    <dl>
        <dt class="redup"><?= lang('Bank.ujian.soal') ?></dt>
        <dd data-jumlah-soal><?= (int) $r['soal'] ?></dd>
        <dt class="redup"><?= lang('Bank.ujian.poin') ?></dt>
        <dd><?= lang('Bank.ujian.poinRinci', [$angka($r['poin']), $angka($r['otomatis']), $angka($r['manual'])]) ?></dd>
        <dt class="redup"><?= lang('Bank.ujian.mode') ?></dt>
        <dd><?= lang('Bank.ujian.mode_' . $ujian['mode']) ?><?php if ($ujian['duration_minutes'] !== null): ?> · <?= lang('Bank.ujian.durasi', [(int) $ujian['duration_minutes']]) ?><?php endif ?></dd>
        <dt class="redup"><?= lang('Bank.ujian.acak') ?></dt>
        <dd><?= lang($ujian['shuffle_questions'] ? 'Bank.ujian.acakSoalYa' : 'Bank.ujian.acakSoalTidak') ?>; <?= lang($ujian['shuffle_options'] ? 'Bank.ujian.acakOpsiYa' : 'Bank.ujian.acakOpsiTidak') ?></dd>
    </dl>
    <p class="petunjuk"><?= lang('Bank.ujian.setelanInfo') ?></p>
    <div class="baris-tombol">
        <a class="tombol" href="<?= $ini ?>/ekspor"><?= lang('Bank.ujian.ekspor') ?></a>
        <?php if ($ujian['status'] === 'draf'): ?>
            <form method="post" action="<?= $ini ?>/hapus" class="inline">
                <?= csrf_field() ?>
                <button type="submit" class="tombol tombol-sekunder"><?= lang('Bank.ujian.hapus') ?></button>
            </form>
        <?php endif ?>
    </div>
    <p class="petunjuk"><?= lang('Bank.ujian.hapusInfo') ?></p>
</div>

<?php if ($ujian['instructions'] !== ''): ?>
    <h2><?= lang('Bank.ujian.petunjuk') ?></h2>
    <div class="kartu soal-teks" dir="auto"><?= Markdown::html($ujian['instructions']) ?></div>
<?php endif ?>

<h2><?= lang('Bank.ujian.isi') ?></h2>
<p class="redup"><?= lang('Bank.pratinjauInfo') ?></p>
<?php foreach ($ujian['butir'] as $b): ?>
    <?php if ($b['jenis'] === 'soal'): ?>
        <?= view('_soal', ['soal' => $b['soal'], 'nomor' => $nomor]) ?>
        <?php $nomor++; ?>
    <?php else: ?>
        <?= view('_kelompok', ['kelompok' => $b['kelompok'], 'nomorAwal' => $nomor]) ?>
        <?php $nomor += count($b['kelompok']['questions']); ?>
    <?php endif ?>
<?php endforeach ?>
<?= $this->endSection() ?>
