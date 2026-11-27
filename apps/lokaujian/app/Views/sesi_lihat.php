<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('kepala') ?><?= view('_kepala_bank') ?>
<script src="/js/konfirmasi.js?v=<?= esc(substr((string) getenv('LOKANDITA_COMMIT'), 0, 12)) ?>" defer></script>
<script src="/js/langsung.js?v=<?= esc(substr((string) getenv('LOKANDITA_COMMIT'), 0, 12)) ?>" defer></script>
<?= $this->endSection() ?>
<?= $this->section('isi') ?>
<?php
/**
 * Halaman sesi untuk yang menjalankannya (SPEC-LOKAUJIAN.md §7, §10): keadaan sesi, tampilan
 * langsung per mahasiswa (disegarkan public/js/langsung.js tiap 5 detik), kendali, tambahan
 * waktu, buka ulang, unduhan CSV dan — bagi yang mengelola mata kuliahnya — rilis hasil.
 * Tidak menampilkan jawaban atau skor.
 * Variabel: $sesi (Sesi::lihat), $kelas (roster), $hitungan, $langsung (Kendali::langsung),
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

<div class="pita-bahaya" role="alert" data-penjadwal="<?= $denyut === null ? 'mati' : 'hidup' ?>"<?= $denyut === null ? '' : ' hidden' ?>><?= lang('Sesi.penjadwalMati') ?></div>
<?php // Antrean judge LokaKode (SPEC-LOKAUJIAN.md §14): peringatan saja, tidak menghalangi apa pun. ?>
<p class="kartu-peringatan" role="status" data-judge="<?= $judge['sibuk'] ? 'sibuk' : 'lengang' ?>"<?= $judge['sibuk'] ? '' : ' hidden' ?>><?= lang('Sesi.judgeSibuk', [(int) $judge['antre']]) ?></p>

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
    <p><strong data-ringkas><?= esc($langsung['ringkas_teks']) ?></strong></p>
    <div class="baris-tombol">
        <a class="tombol tombol-sekunder" href="<?= $ini ?>"><?= lang('Sesi.muatUlang') ?></a>
        <a class="tombol tombol-sekunder" href="<?= $ini ?>/pratinjau"><?= lang('Sesi.pratinjau') ?></a>
        <a class="tombol tombol-sekunder" href="<?= $ini ?>/peristiwa" data-peristiwa-taut><?= lang('SebUjian.peristiwa.tautan', [(int) $peristiwa]) ?></a>
    </div>
    <p class="petunjuk"><?= lang('Hasil.langsung.info') ?></p>
    <p class="pita-bahaya" role="status" data-usang hidden><?= lang('Hasil.langsung.usang') ?> <a href="<?= $ini ?>"><?= lang('Sesi.muatUlang') ?></a></p>
    <p class="redup" role="status" data-langsung-gagal hidden><?= lang('Hasil.langsung.gagal') ?></p>
</div>

<h2><?= lang('Hasil.langsung.judul') ?></h2>
<?php if ($langsung['baris'] === []): ?>
    <div class="kartu"><p><?= lang('Hasil.langsung.kosong') ?></p></div>
<?php else: ?>
<form method="post" action="<?= $ini ?>/tambah-waktu" data-langsung="<?= (int) $sesi['id'] ?>" data-keadaan="<?= esc($k) ?>" data-segar="0">
    <?= csrf_field() ?>
    <div class="tabel-wadah kartu">
        <table>
            <thead><tr>
                <th scope="col" class="sel-centang"><input type="checkbox" data-centang-semua aria-label="<?= esc(lang('Hasil.langsung.centangSemua'), 'attr') ?>"></th>
                <th scope="col"><?= lang('Ujian.mhs.nomorInduk') ?></th>
                <th scope="col"><?= lang('Ujian.mhs.nama') ?></th>
                <th scope="col"><?= lang('Hasil.langsung.kolStatus') ?></th>
                <th scope="col"><?= lang('Hasil.langsung.kolTerjawab') ?></th>
                <th scope="col"><?= lang('Hasil.langsung.kolSimpan') ?></th>
                <th scope="col"><?= lang('Hasil.langsung.kolSisa') ?></th>
                <th scope="col"><?= lang('Hasil.langsung.kolTambahan') ?></th>
                <th scope="col"><?= lang('Hasil.langsung.kolPeristiwa') ?></th>
            </tr></thead>
            <tbody>
            <?php foreach ($langsung['baris'] as $b): ?>
                <tr data-id="<?= esc($b['id'], 'attr') ?>" data-status="<?= esc($b['status'], 'attr') ?>">
                    <td class="sel-centang"><input type="checkbox" name="mahasiswa[]" value="<?= esc($b['id'], 'attr') ?>" aria-label="<?= esc($b['nama'], 'attr') ?>"></td>
                    <td class="ltr"><?= esc($b['nomor_induk'] === '' ? '—' : $b['nomor_induk']) ?></td>
                    <td><?= esc($b['nama'] === '' ? lang('Ujian.akunHilang') : $b['nama']) ?></td>
                    <?php foreach (['status', 'terjawab', 'simpan', 'sisa', 'tambahan', 'peristiwa'] as $sel): ?>
                        <td data-sel="<?= $sel ?>"<?= $sel === 'status' ? '' : ' class="ltr"' ?>><?= esc($b['teks'][$sel]) ?></td>
                    <?php endforeach ?>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
    <?php if ($jalan): ?>
        <div class="kartu" data-tambah-waktu>
            <p><strong><?= lang('Hasil.tambah.judul') ?></strong></p>
            <p class="petunjuk"><?= lang('Hasil.tambah.info') ?></p>
            <label for="t-menit"><?= lang('Hasil.tambah.menit') ?></label>
            <input type="number" id="t-menit" name="menit" min="1" max="240" step="1" class="ltr" required>
            <div class="baris-tombol"><button type="submit" class="tombol tombol-sekunder"><?= lang('Hasil.tambah.lanjut') ?></button></div>
        </div>
    <?php endif ?>
</form>
<?php endif ?>

<?php if ($jalan && $langsung['baris'] !== []): ?>
    <form method="post" action="<?= $ini ?>/buka-ulang" class="kartu" data-buka-ulang>
        <?= csrf_field() ?>
        <p><strong><?= lang('Hasil.buka.judul') ?></strong></p>
        <p class="petunjuk"><?= lang('Hasil.buka.info') ?></p>
        <label for="b-mhs"><?= lang('SebUjian.peristiwa.mahasiswa') ?></label>
        <select id="b-mhs" name="mahasiswa" required>
            <?php foreach ($langsung['baris'] as $b): ?>
                <option value="<?= esc($b['id'], 'attr') ?>"><?= esc($b['nomor_induk'] . ' — ' . $b['nama']) ?></option>
            <?php endforeach ?>
        </select>
        <label for="b-menit"><?= lang('Hasil.tambah.menit') ?></label>
        <input type="number" id="b-menit" name="menit" min="1" max="600" step="1" class="ltr" required>
        <label for="b-alasan"><?= lang('Hasil.buka.alasan') ?></label>
        <input type="text" id="b-alasan" name="alasan" maxlength="500" required dir="auto">
        <div class="baris-tombol"><button type="submit" class="tombol tombol-sekunder"><?= lang('Hasil.tambah.lanjut') ?></button></div>
    </form>
<?php endif ?>

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

<form method="post" action="<?= $ini ?>/csv" class="kartu" data-csv>
    <?= csrf_field() ?>
    <p><strong><?= lang('Hasil.csv.judul') ?></strong></p>
    <p class="petunjuk"><?= lang('Hasil.csv.info') ?></p>
    <label for="c-bentuk"><?= lang('Hasil.csv.bentuk') ?></label>
    <select id="c-bentuk" name="bentuk">
        <option value="lebar"><?= lang('Hasil.csv.lebar') ?></option>
        <option value="panjang"><?= lang('Hasil.csv.panjang') ?></option>
        <option value="peristiwa"><?= lang('Hasil.csv.peristiwa') ?></option>
    </select>
    <label for="c-pemisah"><?= lang('Hasil.csv.pemisah') ?></label>
    <select id="c-pemisah" name="pemisah">
        <option value="titik_koma"><?= lang('Hasil.csv.titikKoma') ?></option>
        <option value="koma"><?= lang('Hasil.csv.koma') ?></option>
    </select>
    <?php if ((int) $sesi['usai'] !== 1): ?><p class="petunjuk" data-csv-berjalan><?= lang('Hasil.csv.berjalan') ?></p><?php endif ?>
    <div class="baris-tombol">
        <button type="submit" class="tombol tombol-sekunder" data-csv-kelas><?= lang('Hasil.csv.kelasIni') ?></button>
        <button type="submit" class="tombol tombol-sekunder" formaction="/ujian/<?= (int) $sesi['exam_id'] ?>/csv" data-csv-semua><?= lang('Hasil.csv.semuaKelas') ?></button>
    </div>
</form>

<?php if ($sesi['boleh_atur']): ?>
    <div class="kartu" data-rilis="<?= esc($sesi['release_level'] ?? 'tidak') ?>">
        <p><strong><?= lang('Hasil.rilis.judul') ?></strong></p>
        <p><?= lang('Hasil.rilis.kini_' . ($sesi['release_level'] ?? 'tidak')) ?></p>
        <?php if ($jalan && (int) $sesi['usai'] === 1): ?>
            <form method="post" action="<?= $ini ?>/rilis">
                <?= csrf_field() ?>
                <label for="r-tingkat"><?= lang('Hasil.rilis.tingkat') ?></label>
                <select id="r-tingkat" name="tingkat">
                    <option value="nilai"><?= lang('Hasil.rilis.nilai') ?></option>
                    <option value="nilai_dan_kunci"><?= lang('Hasil.rilis.nilaiDanKunci') ?></option>
                </select>
                <div class="baris-tombol"><button type="submit" class="tombol tombol-sekunder"><?= lang('Hasil.rilis.kirim') ?></button></div>
            </form>
        <?php elseif ($jalan): ?>
            <p class="petunjuk"><?= lang('Hasil.rilis.belumUsai') ?></p>
        <?php endif ?>
        <?php if ($jalan && $sesi['release_level'] !== null): ?>
            <form method="post" action="<?= $ini ?>/rilis/tarik" data-konfirmasi="<?= esc(lang('Hasil.rilis.tarikYakin'), 'attr') ?>">
                <?= csrf_field() ?>
                <div class="baris-tombol"><button type="submit" class="tombol tombol-sekunder"><?= lang('Hasil.rilis.tarik') ?></button></div>
            </form>
        <?php endif ?>
        <div class="baris-tombol"><a class="tombol tombol-sekunder" href="/mata-kuliah/<?= (int) $sesi['course_id'] ?>/ujian/<?= (int) $sesi['exam_id'] ?>/koreksi"><?= lang('Hasil.koreksi.judul') ?></a></div>
    </div>
<?php endif ?>

<?php if ($sesi['boleh_atur'] && (int) $hitungan['hadir'] === 0): ?>
    <form method="post" action="<?= $ini ?>/hapus" class="kartu">
        <?= csrf_field() ?>
        <p class="petunjuk"><?= lang('Sesi.hapusInfo') ?></p>
        <div class="baris-tombol"><button type="submit" class="tombol tombol-sekunder"><?= lang('Sesi.aksi.hapus') ?></button></div>
    </form>
<?php endif ?>
<?= $this->endSection() ?>
