<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('kepala') ?><?= view('_kepala_bank') ?><?= $this->endSection() ?>
<?= $this->section('isi') ?>
<?php
/**
 * Pratinjau satu ujian (SPEC-LOKAUJIAN.md §5.4): butirnya ditampilkan dengan partial yang sama
 * dengan halaman mahasiswa, dalam urutan ujian. Variabel: $matkul, $ujian (Ujian::lihat),
 * $seb (konfigurasi SEB yang berlaku atau null), $sesi (Sesi::untukUjian), $daftarKelas (kelas mata kuliah itu pada tahun aktif), $kurang.
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
    <div class="baris-tombol">
        <?php if ($ujian['status'] === 'draf'): ?>
            <a class="tombol" href="<?= $ini ?>/sunting"><?= lang('Sesi.ujian.sunting') ?></a>
            <form method="post" action="<?= $ini ?>/terbit" class="inline">
                <?= csrf_field() ?>
                <button type="submit" class="tombol" data-terbit><?= lang('Sesi.ujian.terbitkan') ?></button>
            </form>
        <?php endif ?>
        <a class="tombol tombol-sekunder" href="<?= $ini ?>/ekspor"><?= lang('Bank.ujian.ekspor') ?></a>
        <?php if ($ujian['status'] === 'terbit' && (int) $ujian['pengerjaan'] === 0): ?>
            <form method="post" action="<?= $ini ?>/tarik" class="inline">
                <?= csrf_field() ?>
                <button type="submit" class="tombol tombol-sekunder"><?= lang('Sesi.ujian.tarik') ?></button>
            </form>
        <?php endif ?>
        <?php if ($ujian['status'] !== 'draf'): ?>
            <form method="post" action="<?= $ini ?>/arsip" class="inline">
                <?= csrf_field() ?>
                <input type="hidden" name="arsip" value="<?= $ujian['status'] === 'arsip' ? '0' : '1' ?>">
                <button type="submit" class="tombol tombol-sekunder"><?= lang($ujian['status'] === 'arsip' ? 'Sesi.ujian.pulihkan' : 'Sesi.ujian.arsipkan') ?></button>
            </form>
        <?php endif ?>
        <?php if ((int) $ujian['pengerjaan'] === 0): ?>
            <form method="post" action="<?= $ini ?>/hapus" class="inline">
                <?= csrf_field() ?>
                <button type="submit" class="tombol tombol-sekunder"><?= lang($ujian['status'] === 'draf' ? 'Bank.ujian.hapus' : 'Sesi.ujian.hapus') ?></button>
            </form>
        <?php endif ?>
    </div>
    <p class="petunjuk" data-info-status><?= lang('Sesi.ujian.info_' . $ujian['status'], [(int) $ujian['pengerjaan']]) ?></p>
</div>

<div class="kartu" data-seb-ringkas="<?= $seb === null ? 'tanpa' : ((int) $seb['jumlah_kunci'] === 0 ? 'tanpa_kunci' : 'siap') ?>">
    <p><strong><?= lang('SebUjian.ringkas.judul') ?></strong></p>
    <p><?= $seb === null ? lang('SebUjian.ringkas.tanpa') : ((int) $seb['jumlah_kunci'] === 0 ? lang('SebUjian.ringkas.tanpaKunci') : lang('SebUjian.ringkas.siap', [(int) $seb['version'], (int) $seb['jumlah_kunci']])) ?></p>
    <div class="baris-tombol"><a class="tombol tombol-sekunder" href="<?= $ini ?>/seb"><?= lang('SebUjian.ringkas.kelola') ?></a></div>
</div>

<?php if ($kurang !== []): ?>
    <div class="kartu" role="alert" data-kurang>
        <p><strong><?= lang('Sesi.ujian.belumBisaTerbit') ?></strong></p>
        <ul class="daftar-galat">
            <?php foreach ($kurang as $k): ?><li><?= lang('Sesi.kurang.' . $k['kode'], [(int) $k['nomor']]) ?></li><?php endforeach ?>
        </ul>
    </div>
<?php endif ?>

<?php if ($ujian['status'] !== 'draf'): ?>
    <h2><?= lang('Sesi.judul') ?></h2>
    <?php if ($sesi === []): ?>
        <div class="kartu"><p><?= lang('Sesi.kosong') ?></p></div>
    <?php else: ?>
        <?= view('_sesi_tabel', ['sesi' => $sesi, 'namaKelas' => array_column($daftarKelas, 'name', 'id') + [0 => '']]) ?>
    <?php endif ?>
    <?php
    $sudah  = array_map('intval', array_column($sesi, 'class_id'));
    $tersisa = array_values(array_filter($daftarKelas, static fn (array $k): bool => $k['archived_at'] === null && ! in_array((int) $k['id'], $sudah, true)));
    ?>
    <?php if ($ujian['status'] === 'terbit' && $tersisa !== []): ?>
        <form method="post" action="<?= $ini ?>/sesi" class="kartu" data-beri-kelas>
            <?= csrf_field() ?>
            <p><strong><?= lang('Sesi.beri.judul') ?></strong></p>
            <p class="petunjuk"><?= lang('Sesi.beri.info') ?></p>
            <label for="sesi-kelas"><?= lang('Ujian.kelas.satu') ?></label>
            <select id="sesi-kelas" name="kelas" required>
                <?php foreach ($tersisa as $k): ?><option value="<?= (int) $k['id'] ?>"><?= esc($k['name']) ?></option><?php endforeach ?>
            </select>
            <?php if ($ujian['mode'] === 'per_mahasiswa'): ?>
                <label for="sesi-buka"><?= lang('Sesi.buka') ?></label>
                <input type="datetime-local" id="sesi-buka" name="buka" required>
                <label for="sesi-tutup"><?= lang('Sesi.tutup') ?></label>
                <input type="datetime-local" id="sesi-tutup" name="tutup" required>
                <p class="petunjuk"><?= lang('Sesi.zona') ?></p>
            <?php else: ?>
                <p class="petunjuk"><?= lang('Sesi.beri.infoDosen') ?></p>
            <?php endif ?>
            <div class="baris-tombol"><button type="submit" class="tombol"><?= lang('Sesi.beri.kirim') ?></button></div>
        </form>
    <?php endif ?>
<?php endif ?>

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
