<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('kepala') ?><?= view('_kepala_bank') ?>
<script src="/js/konfirmasi.js?v=<?= esc(substr((string) getenv('LOKANDITA_COMMIT'), 0, 12)) ?>" defer></script>
<?= $this->endSection() ?>
<?= $this->section('isi') ?>
<?php
/**
 * Halaman sesi untuk yang menjalankannya (SPEC-LOKAUJIAN.md §7, §10 — tahap U3: kendali paling
 * sedikit). Tidak menampilkan jawaban atau skor. Tidak memuat ulang sendiri: tampilan langsung
 * per mahasiswa adalah tahap U5.
 * Variabel: $sesi (Sesi::lihat), $kelas (roster), $hitungan, $aktif (jumlah mahasiswa AKTIF),
 * $denyut (denyut penjadwal atau null), $seb (konfigurasi SEB ujiannya atau null), $sebWajib,
 * $peristiwa (jumlah peristiwa integritas sesi ini).
 */
use Lokandita\Auth\Waktu;

$ini  = '/sesi/' . (int) $sesi['id'];
$per  = $sesi['mode'] === 'per_mahasiswa';
$k    = $sesi['keadaan'];
$jam  = static fn (?string $utc): string => $utc === null ? '—' : (new DateTimeImmutable($utc, new DateTimeZone('UTC')))->setTimezone(new DateTimeZone('Asia/Jakarta'))->format('H:i:s') . ' WIB';
$jalan = $sesi['ujian_status'] === 'terbit';
?>
<?= view('_nav', ['akses' => $akses]) ?>
<p><a href="/kelas/<?= (int) $kelas['id'] ?>"><span class="panah-balik" aria-hidden="true"></span> <span class="ltr"><?= esc($kelas['matkul_kode']) ?></span> <?= esc($kelas['name']) ?></a></p>
<h1 dir="auto"><?= esc($sesi['title']) ?></h1>
<p>
    <span class="lencana"><?= lang($sesi['kind'] === 'kuis' ? 'Bank.ujian.jenisKuis' : 'Bank.ujian.jenisUjian') ?></span>
    <span class="lencana"><?= lang('Bank.ujian.mode_' . $sesi['mode']) ?></span>
    <span class="lencana" data-keadaan="<?= esc($k) ?>"><?= lang('Sesi.keadaan_' . $k) ?></span>
    <?php if (! $jalan): ?><span class="lencana"><?= lang('Bank.ujian.status_arsip') ?></span><?php endif ?>
</p>

<?php if ($denyut === null): ?>
    <div class="pita-bahaya" role="alert" data-penjadwal="mati"><?= lang('Sesi.penjadwalMati') ?></div>
<?php endif ?>

<div class="kartu">
    <dl>
        <dt class="redup"><?= lang('Sesi.waktuServer') ?></dt>
        <dd class="ltr" data-waktu-server><?= esc($jam($sesi['kini'])) ?></dd>
        <?php if ($per): ?>
            <dt class="redup"><?= lang('Sesi.buka') ?></dt>
            <dd class="ltr"><?= esc(Waktu::lokal($sesi['open_at'])) ?></dd>
            <dt class="redup"><?= lang('Sesi.tutup') ?></dt>
            <dd class="ltr"><?= $sesi['close_at'] === null && $sesi['open_at'] !== null ? lang('Sesi.sampaiDitutup') : esc(Waktu::lokal($sesi['close_at'])) ?></dd>
            <dt class="redup"><?= lang('Sesi.ujian.durasi') ?></dt>
            <dd><?= lang('Bank.ujian.durasi', [(int) $sesi['duration_minutes']]) ?></dd>
        <?php else: ?>
            <dt class="redup"><?= lang('Sesi.dimulai') ?></dt>
            <dd class="ltr"><?= esc($jam($sesi['started_at'])) ?></dd>
            <dt class="redup"><?= lang('Sesi.akhir') ?></dt>
            <dd class="ltr" data-akhir><?= $sesi['started_at'] === null ? '—' : esc($jam($sesi['akhir'])) ?><?= $sesi['started_at'] !== null && $sesi['end_at'] === null ? ' (' . lang('Sesi.akhirOtomatis') . ')' : '' ?></dd>
        <?php endif ?>
        <dt class="redup"><?= lang('Sesi.penjadwal') ?></dt>
        <dd data-penjadwal-teks><?= $denyut === null ? lang('Sesi.penjadwalTidak') : lang('Sesi.penjadwalHidup') ?></dd>
    </dl>
    <p>
        <strong data-hitung-hadir><?= lang('Sesi.hitung.hadir', [(int) $hitungan['hadir'], (int) $aktif]) ?></strong> ·
        <span data-hitung-kerja><?= lang('Sesi.hitung.mengerjakan', [(int) $hitungan['mengerjakan']]) ?></span> ·
        <span data-hitung-selesai><?= lang('Sesi.hitung.selesai', [(int) $hitungan['selesai']]) ?></span>
    </p>
    <div class="baris-tombol">
        <a class="tombol tombol-sekunder" href="<?= $ini ?>"><?= lang('Sesi.muatUlang') ?></a>
        <a class="tombol tombol-sekunder" href="<?= $ini ?>/pratinjau"><?= lang('Sesi.pratinjau') ?></a>
        <a class="tombol tombol-sekunder" href="<?= $ini ?>/peristiwa" data-peristiwa-taut><?= lang('SebUjian.peristiwa.tautan', [(int) $peristiwa]) ?></a>
    </div>
    <p class="petunjuk"><?= lang('Sesi.infoU3') ?></p>
</div>

<div class="kartu" data-seb-ringkas="<?= $seb === null ? 'tanpa' : ((int) $seb['jumlah_kunci'] === 0 ? 'tanpa_kunci' : 'siap') ?>">
    <p><strong><?= lang('SebUjian.ringkas.judul') ?></strong></p>
    <p><?= $seb === null ? lang('SebUjian.ringkas.tanpa') : ((int) $seb['jumlah_kunci'] === 0 ? lang('SebUjian.ringkas.tanpaKunci') : lang('SebUjian.ringkas.siap', [(int) $seb['version'], (int) $seb['jumlah_kunci']])) ?></p>
<?php if ($sesi['boleh_atur']): ?>    <div class="baris-tombol"><a class="tombol tombol-sekunder" href="/mata-kuliah/<?= (int) $sesi['course_id'] ?>/ujian/<?= (int) $sesi['exam_id'] ?>/seb"><?= lang('SebUjian.ringkas.kelola') ?></a></div>
<?php endif ?>
</div>

<?php if ($jalan && $per): ?>
    <div class="kartu" data-kendali>
        <p><strong><?= lang('Sesi.kendali') ?></strong></p>
        <p class="petunjuk"><?= lang('Sesi.infoPer') ?></p>
        <div class="baris-tombol">
            <?php if ($k !== 'terbuka'): ?>
                <form method="post" action="<?= $ini ?>/buka" class="inline"><?= csrf_field() ?><button type="submit" class="tombol" data-buka><?= lang('Sesi.aksi.buka') ?></button></form>
            <?php else: ?>
                <form method="post" action="<?= $ini ?>/tutup" class="inline"><?= csrf_field() ?><button type="submit" class="tombol" data-tutup><?= lang('Sesi.aksi.tutup') ?></button></form>
            <?php endif ?>
        </div>
    </div>
    <?php if ($sesi['boleh_atur']): ?>
        <form method="post" action="<?= $ini ?>/jadwal" class="kartu" data-jadwal>
            <?= csrf_field() ?>
            <p><strong><?= lang('Sesi.jadwal') ?></strong></p>
            <label for="j-buka"><?= lang('Sesi.buka') ?></label>
            <input type="datetime-local" id="j-buka" name="buka" required value="<?= esc(\App\Libraries\Sesi::keLokal($sesi['open_at']), 'attr') ?>">
            <label for="j-tutup"><?= lang('Sesi.tutup') ?></label>
            <input type="datetime-local" id="j-tutup" name="tutup" required value="<?= esc(\App\Libraries\Sesi::keLokal($sesi['close_at']), 'attr') ?>">
            <p class="petunjuk"><?= lang('Sesi.zona') ?></p>
            <div class="baris-tombol"><button type="submit" class="tombol tombol-sekunder"><?= lang('Sesi.aksi.jadwal') ?></button></div>
        </form>
    <?php endif ?>
<?php elseif ($jalan): ?>
    <?php if ($k === 'menunggu'): ?>
        <form method="post" action="<?= $ini ?>/mulai" class="kartu" data-kendali>
            <?= csrf_field() ?>
            <p><strong><?= lang('Sesi.aksi.mulai') ?></strong></p>
            <p class="petunjuk"><?= lang('Sesi.infoMulai') ?></p>
            <label for="m-menit"><?= lang('Sesi.akhirMenit') ?></label>
            <input type="number" id="m-menit" name="menit" min="1" max="480" step="1" class="ltr" value="<?= $sesi['duration_minutes'] === null ? '' : (int) min(480, (int) $sesi['duration_minutes']) ?>">
            <p class="petunjuk"><?= lang('Sesi.akhirKosong') ?></p>
            <div class="baris-tombol"><button type="submit" class="tombol" data-mulai><?= lang('Sesi.aksi.mulai') ?></button></div>
        </form>
    <?php elseif ($k === 'berlangsung'): ?>
        <form method="post" action="<?= $ini ?>/akhir" class="kartu" data-kendali>
            <?= csrf_field() ?>
            <p><strong><?= lang('Sesi.aksi.akhir') ?></strong></p>
            <label for="a-menit"><?= lang('Sesi.akhirMenit') ?></label>
            <input type="number" id="a-menit" name="menit" min="1" max="480" step="1" class="ltr">
            <label for="a-jam"><?= lang('Sesi.akhirJam') ?></label>
            <input type="time" id="a-jam" name="jam" class="ltr">
            <p class="petunjuk"><?= lang('Sesi.akhirInfo') ?></p>
            <div class="baris-tombol"><button type="submit" class="tombol tombol-sekunder" data-akhir-kirim><?= lang('Sesi.aksi.akhir') ?></button></div>
        </form>
        <form method="post" action="<?= $ini ?>/akhiri" class="kartu" data-konfirmasi="<?= esc(lang('Sesi.akhiriYakin'), 'attr') ?>">
            <?= csrf_field() ?>
            <p><strong><?= lang('Sesi.aksi.akhiri') ?></strong></p>
            <p class="petunjuk"><?= lang('Sesi.akhiriInfo') ?></p>
            <div class="baris-tombol"><button type="submit" class="tombol tombol-bahaya" data-akhiri><?= lang('Sesi.aksi.akhiri') ?></button></div>
        </form>
    <?php elseif ($k === 'diakhiri'): ?>
        <div class="kartu" role="status"><p><?= lang('Sesi.sedangDiakhiri', [esc($jam($sesi['akhir']))]) ?></p></div>
    <?php endif ?>
<?php endif ?>

<?php if ($sesi['boleh_atur'] && (int) $hitungan['hadir'] === 0): ?>
    <form method="post" action="<?= $ini ?>/hapus" class="kartu">
        <?= csrf_field() ?>
        <p class="petunjuk"><?= lang('Sesi.hapusInfo') ?></p>
        <div class="baris-tombol"><button type="submit" class="tombol tombol-sekunder"><?= lang('Sesi.aksi.hapus') ?></button></div>
    </form>
<?php endif ?>
<?= $this->endSection() ?>
