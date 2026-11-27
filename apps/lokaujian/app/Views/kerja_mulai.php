<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('kepala') ?><?= view('_kepala_bank') ?>
<script src="/js/tunggu.js?v=<?= esc(substr((string) getenv('LOKANDITA_COMMIT'), 0, 12)) ?>" defer></script>
<?= $this->endSection() ?>
<?= $this->section('isi') ?>
<?php
/**
 * Satu sesi bagi mahasiswa, SEBELUM dan SESUDAH mengerjakan (SPEC-LOKAUJIAN.md §8):
 *   belum mulai   judul, jumlah soal, durasi atau mode, petunjuk, aturan, lalu Mulai — atau
 *                 halaman menunggu yang berubah sendiri ketika dosen memulai;
 *   berjalan      tautan Lanjutkan;
 *   berakhir      tanda terima: jawaban diterima, kapan, berapa yang terjawab. Skor objektif
 *                 hanya bila hasil kelasnya dirilis, dan tautan tinjauan bila dirilis dengan kunci.
 * Tidak memuat satu soal pun. Variabel: $sesi (Pengerjaan::sesi).
 */
use App\Libraries\Markdown;
use Lokandita\Auth\Waktu;

$id      = (int) $sesi['sesi_id'];
$ada     = $sesi['attempt_id'] !== null;
$jalan   = $ada && (int) $sesi['berjalan'] === 1;
$dosen   = $sesi['mode'] === 'dikendalikan_dosen';
$terima  = (int) $sesi['menerima'] === 1;
?>
<p><a href="/beranda"><span class="panah-balik" aria-hidden="true"></span> <?= lang('Lokandita.beranda') ?></a></p>
<h1 dir="auto"><?= esc($sesi['title']) ?></h1>
<p>
    <span class="lencana"><?= lang($sesi['kind'] === 'kuis' ? 'Bank.ujian.jenisKuis' : 'Bank.ujian.jenisUjian') ?></span>
    <span class="lencana"><?= lang('Bank.ujian.mode_' . $sesi['mode']) ?></span>
</p>

<?php if ($ada && ! $jalan): ?>
    <div class="kartu" role="status" data-tanda-terima>
        <p><strong><?= lang('Kerja.terima.judul') ?></strong></p>
        <dl>
            <dt class="redup"><?= lang('Kerja.terima.kapan') ?></dt>
            <dd class="ltr"><?= esc(Waktu::lokal($sesi['submitted_at'] ?? $sesi['tenggat'])) ?></dd>
            <dt class="redup"><?= lang('Kerja.terima.cara') ?></dt>
            <dd><?= lang('Kerja.terima.cara_' . ($sesi['submit_how'] ?? 'waktu_habis')) ?></dd>
            <dt class="redup"><?= lang('Kerja.terima.terjawab') ?></dt>
            <dd data-terjawab><?= lang('Kerja.terima.dari', [(int) $sesi['terjawab'], (int) $sesi['jumlah_soal']]) ?></dd>
        </dl>
        <p class="petunjuk"><?= lang('Kerja.terima.info') ?></p>
    </div>
    <?php // Hasil hanya tampil bila kelasnya dirilis (SPEC-LOKAUJIAN.md §11). ?>
    <?php if ($sesi['release_level'] !== null && $sesi['score'] !== null): ?>
        <div class="kartu" data-nilai>
            <p><strong><?= lang('Hasil.nilai.objektif', [esc(\App\Libraries\Nilai::tampil($sesi['score'])), esc(\App\Libraries\Nilai::tampil($sesi['poin_objektif']))]) ?></strong></p>
            <p class="petunjuk"><?= lang('Hasil.nilai.manual') ?></p>
            <?php if ($sesi['release_level'] === 'nilai_dan_kunci'): ?>
                <div class="baris-tombol"><a class="tombol" href="/ujian-saya/<?= $id ?>/tinjau" data-tinjau><?= lang('Hasil.tinjau.buka') ?></a></div>
            <?php endif ?>
        </div>
    <?php endif ?>
<?php elseif ($jalan): ?>
    <div class="kartu" data-berjalan>
        <p><?= lang('Kerja.berjalan') ?></p>
        <div class="baris-tombol"><a class="tombol" href="/ujian-saya/<?= $id ?>/kerjakan"><?= lang('Kerja.lanjutkan') ?></a></div>
    </div>
<?php else: ?>
    <div class="kartu">
        <dl>
            <dt class="redup"><?= lang('Bank.ujian.soal') ?></dt>
            <dd data-jumlah-soal><?= (int) $sesi['jumlah_soal'] ?></dd>
            <dt class="redup"><?= lang('Sesi.waktu') ?></dt>
            <dd>
                <?php if ($dosen): ?>
                    <?= lang('Kerja.daftar.dosen') ?>
                <?php else: ?>
                    <?= lang('Kerja.daftar.jadwal', [esc(Waktu::lokal($sesi['open_at'])), $sesi['close_at'] === null ? '—' : esc(Waktu::lokal($sesi['close_at'])), (int) $sesi['duration_minutes']]) ?>
                <?php endif ?>
            </dd>
        </dl>
    </div>
    <?php if ($sesi['instructions'] !== ''): ?>
        <h2><?= lang('Bank.ujian.petunjuk') ?></h2>
        <div class="kartu soal-teks" dir="auto"><?= Markdown::html($sesi['instructions']) ?></div>
    <?php endif ?>
    <h2><?= lang('Kerja.aturan.judul') ?></h2>
    <div class="kartu">
        <ul>
            <li><?= lang('Kerja.aturan.satu') ?></li>
            <li><?= lang('Kerja.aturan.simpan') ?></li>
            <li><?= lang('Kerja.aturan.jam') ?></li>
            <li><?= lang($dosen ? 'Kerja.aturan.waktuDosen' : 'Kerja.aturan.waktuPer', [(int) $sesi['duration_minutes']]) ?></li>
        </ul>
    </div>
    <?php // Halaman menunggu: bertanya ke server tiap 5 detik. Dikendalikan dosen: begitu dosen memulai,
          // form Mulai dikirim sendiri. Per mahasiswa: halaman dimuat ulang supaya tombol Mulai muncul;
          // jamnya baru berjalan ketika mahasiswa menekannya. ?>
    <form method="post" action="/ujian-saya/<?= $id ?>/mulai" class="kartu" data-mulai-form data-sesi="<?= $id ?>" data-menerima="<?= $terima ? '1' : '0' ?>" data-otomatis="<?= $dosen ? '1' : '0' ?>">
        <?= csrf_field() ?>
        <?php if ($terima): ?>
            <p><?= lang($dosen ? 'Kerja.mulaiDosen' : 'Kerja.mulaiPer') ?></p>
            <div class="baris-tombol"><button type="submit" class="tombol" data-mulai><?= lang('Kerja.mulai') ?></button></div>
        <?php elseif ((int) $sesi['sesi_selesai'] === 1 || (! $dosen && $sesi['close_at'] !== null && $sesi['close_at'] <= gmdate('Y-m-d H:i:s'))): ?>
            <p data-lewat><?= lang('Kerja.lewat') ?></p>
        <?php else: ?>
            <p role="status" data-menunggu><strong><?= lang($dosen ? 'Kerja.menungguDosen' : 'Kerja.menungguBuka') ?></strong></p>
            <p class="petunjuk"><?= lang('Kerja.menungguInfo') ?></p>
        <?php endif ?>
    </form>
<?php endif ?>
<?= $this->endSection() ?>
