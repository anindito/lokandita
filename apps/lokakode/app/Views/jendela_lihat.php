<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<?php
use App\Libraries\Waktu;

$alamat = '/kelas/' . (int) $kelas['id'] . '/pertemuan/' . (int) $j['pertemuan_id'];
$terbit = $j['published_at'] !== null;
?>
<?= view('_nav', ['akses' => $akses]) ?>
<p><a href="/kelas/<?= (int) $kelas['id'] ?>/pertemuan"><span class="panah-balik" aria-hidden="true"></span> <?= lang('Latihan.jendela.judul') ?> — <span class="ltr"><?= esc($kelas['matkul_kode']) ?></span> <?= esc($kelas['name']) ?></a></p>
<h1><?= lang('Soal.pertemuan.ke', [(int) $j['number']]) ?>: <?= esc($j['title']) ?></h1>
<p class="redup">
    <?= lang('Soal.bahasa.' . $j['language']) ?> · <?= lang('Latihan.jendela.jumlahSoal', [(int) $j['jumlah_soal']]) ?>
</p>

<p><a href="<?= $alamat ?>/papan"><?= lang('Papan.lihat') ?></a> · <a href="<?= $alamat ?>/analitik"><?= lang('Papan.analitik.judul') ?></a> · <a href="<?= $alamat ?>/kiriman"><?= lang('Papan.kiriman.judul') ?></a></p>

<div class="kartu" role="status">
    <p>
        <span class="lencana keadaan-<?= $keadaan ?>"><?= lang('Latihan.keadaan.' . $keadaan) ?></span>
        <?php if (in_array($j['override'], ['open', 'closed'], true)): ?><span class="redup"><?= lang('Latihan.jendela.manual') ?></span><?php endif ?>
    </p>
    <p><?= lang('Latihan.keadaanInfo.' . $keadaan) ?></p>
    <?php if (! $terbit): ?>
        <p><strong class="teks-peringatan"><?= lang('Latihan.jendela.belumTerbit') ?></strong> <?= lang('Latihan.jendela.belumTerbitInfo') ?></p>
    <?php endif ?>
</div>

<?php if ($bolehUbah): ?>
    <h2><?= lang('Latihan.jendela.manualJudul') ?></h2>
    <div class="kartu">
        <form method="post" action="<?= $alamat ?>/status">
            <?= csrf_field() ?>
            <p class="petunjuk"><?= lang('Latihan.jendela.manualInfo') ?></p>
            <div class="baris-tombol">
                <button type="submit" name="override" value="open" class="tombol" <?= $j['override'] === 'open' ? 'disabled' : '' ?>><?= lang('Latihan.jendela.bukaSekarang') ?></button>
                <button type="submit" name="override" value="closed" class="tombol tombol-bahaya" <?= $j['override'] === 'closed' ? 'disabled' : '' ?>><?= lang('Latihan.jendela.tutupSekarang') ?></button>
                <button type="submit" name="override" value="none" class="tombol tombol-sekunder" <?= in_array($j['override'], ['open', 'closed'], true) ? '' : 'disabled' ?>><?= lang('Latihan.jendela.ikutJadwal') ?></button>
            </div>
            <?= view('_galat_latihan', ['galat' => $galat, 'kolom' => 'override']) ?>
        </form>
    </div>

    <h2><?= lang('Latihan.jendela.jadwal') ?></h2>
    <div class="kartu">
        <form method="post" action="<?= $alamat ?>">
            <?= csrf_field() ?>
            <label for="open_at"><?= lang('Latihan.jendela.buka') ?> (WIB)</label>
            <input type="datetime-local" id="open_at" name="open_at" class="ltr" value="<?= esc($isi['open_at']) ?>">
            <?= view('_galat_latihan', ['galat' => $galat, 'kolom' => 'open_at']) ?>

            <label for="close_at"><?= lang('Latihan.jendela.tutup') ?> (WIB)</label>
            <input type="datetime-local" id="close_at" name="close_at" class="ltr" value="<?= esc($isi['close_at']) ?>">
            <?= view('_galat_latihan', ['galat' => $galat, 'kolom' => 'close_at']) ?>
            <p class="petunjuk"><?= lang('Latihan.jendela.jadwalInfo') ?></p>

            <label for="post_close_visibility"><?= lang('Latihan.jendela.sesudahTutup') ?></label>
            <select id="post_close_visibility" name="post_close_visibility">
                <?php foreach (['hidden', 'review_only'] as $t): ?>
                    <option value="<?= $t ?>" <?= $isi['post_close_visibility'] === $t ? 'selected' : '' ?>><?= lang('Latihan.tampak.' . $t) ?></option>
                <?php endforeach ?>
            </select>
            <?= view('_galat_latihan', ['galat' => $galat, 'kolom' => 'post_close_visibility']) ?>

            <fieldset>
                <legend><?= lang('Latihan.telat.judul') ?></legend>
                <?php foreach (['reject', 'multiplier'] as $k): ?>
                    <label class="centang"><input type="radio" name="late_policy" value="<?= $k ?>" <?= $isi['late_policy'] === $k ? 'checked' : '' ?>> <?= lang('Latihan.telat.' . $k) ?></label>
                <?php endforeach ?>
                <?= view('_galat_latihan', ['galat' => $galat, 'kolom' => 'late_policy']) ?>

                <label for="late_factor"><?= lang('Latihan.telat.faktor') ?></label>
                <input type="text" inputmode="decimal" id="late_factor" name="late_factor" class="ltr" value="<?= esc($isi['late_factor']) ?>">
                <?= view('_galat_latihan', ['galat' => $galat, 'kolom' => 'late_factor']) ?>

                <label for="late_until"><?= lang('Latihan.telat.sampai') ?> (WIB)</label>
                <input type="datetime-local" id="late_until" name="late_until" class="ltr" value="<?= esc($isi['late_until']) ?>">
                <?= view('_galat_latihan', ['galat' => $galat, 'kolom' => 'late_until']) ?>
                <p class="petunjuk"><?= lang('Latihan.telat.info') ?></p>
            </fieldset>

            <div class="baris-tombol">
                <button type="submit" class="tombol"><?= lang('Latihan.jendela.simpan') ?></button>
            </div>
        </form>
    </div>
<?php else: ?>
    <div class="kartu">
        <dl>
            <dt class="redup"><?= lang('Latihan.jendela.buka') ?></dt><dd><?= esc(Waktu::lokal($j['open_at'])) ?></dd>
            <dt class="redup"><?= lang('Latihan.jendela.tutup') ?></dt><dd><?= esc(Waktu::lokal($j['close_at'])) ?></dd>
            <dt class="redup"><?= lang('Latihan.telat.judul') ?></dt>
            <dd><?= lang('Latihan.telat.' . ($j['late_policy'] ?? 'reject')) ?><?php if (($j['late_policy'] ?? '') === 'multiplier'): ?>
                — <span class="ltr">× <?= angka((float) $j['late_factor']) ?></span><?= $j['late_until'] !== null ? ', ' . esc(Waktu::lokal($j['late_until'])) : '' ?><?php endif ?></dd>
        </dl>
        <p class="petunjuk"><?= lang('Kode.kelas.tahunArsip') ?></p>
    </div>
<?php endif ?>

<h2 id="instans"><?= lang('Latihan.siap.judul') ?></h2>
<div class="kartu" role="status">
    <p><strong><?= lang('Latihan.siap.hitung', [$maju['siap'], $maju['harapan']]) ?></strong>
        <span class="redup"><?= lang('Latihan.siap.rincian', [$maju['mahasiswa'], $maju['soal']]) ?></span></p>
    <progress max="<?= max(1, $maju['harapan']) ?>" value="<?= $maju['siap'] ?>"></progress>
    <?php if ($maju['antre'] > 0): ?>
        <p>
            <?= lang('Latihan.siap.antre', [$maju['antre']]) ?>
            <?php if ($maju['perkiraan'] !== null): ?>
                <?= lang('Latihan.siap.perkiraan', [max(1, (int) ceil($maju['perkiraan'] / 60))]) ?>
            <?php endif ?>
            <a href="<?= $alamat ?>"><?= lang('Soal.impor.muatUlang') ?></a>
        </p>
    <?php endif ?>
    <?php if (! $maju['worker']): ?>
        <p><strong class="teks-bahaya"><?= lang('Latihan.siap.workerMati') ?></strong></p>
    <?php endif ?>
    <?php if ($maju['gagal'] > 0): ?>
        <p><strong class="teks-bahaya"><?= lang('Latihan.siap.gagal', [$maju['gagal']]) ?></strong></p>
    <?php endif ?>
    <p class="petunjuk"><?= lang('Latihan.siap.info') ?></p>
    <?php if ($bolehUbah && $terbit && $maju['siap'] < $maju['harapan']): ?>
        <form method="post" action="<?= $alamat ?>/siapkan">
            <?= csrf_field() ?>
            <div class="baris-tombol">
                <button type="submit" class="tombol tombol-sekunder"><?= lang('Latihan.siap.tombol') ?></button>
            </div>
        </form>
    <?php endif ?>
</div>
<?= $this->endSection() ?>
