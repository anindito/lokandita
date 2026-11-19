<?php
/**
 * Penjelasan untuk mahasiswa bila kiriman yang sekarang dibuat BUKAN kiriman biasa
 * (App\Libraries\Ruang::jenis): terlambat, atau latihan sesudah tutup.
 * Variabel: $p (baris pertemuan atau soal dari Ruang, dengan jenis, late_factor, late_until).
 */
use App\Libraries\Waktu;

?>
<?php if ($p['jenis'] === 'telat'): ?>
    <p class="kartu-peringatan" role="status" data-jenis-kerja="telat">
        <strong><?= lang('Latihan.jenis.telatJudul') ?></strong>
        <?= lang('Latihan.jenis.telatInfo', [angka((float) $p['late_factor'])]) ?>
        <?= $p['late_until'] !== null ? lang('Latihan.jenis.telatSampai', [esc(Waktu::lokal($p['late_until']))]) : lang('Latihan.jenis.telatTanpaBatas') ?>
    </p>
<?php elseif ($p['jenis'] === 'latihan'): ?>
    <p class="kartu-peringatan" role="status" data-jenis-kerja="latihan">
        <strong><?= lang('Latihan.jenis.latihanJudul') ?></strong>
        <?= lang('Latihan.jenis.latihanInfo') ?>
    </p>
<?php endif ?>
