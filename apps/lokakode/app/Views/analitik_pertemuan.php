<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?php
/**
 * Analitik satu pertemuan untuk satu kelas (SPEC.md §12): sebaran nilai, statistik per soal,
 * dan nilai tiap mahasiswa aktif. Variabel: $kelas, $j, $keadaan, $nilai, $statistik, $belum, $soal, $orang.
 */
$versi  = esc(substr((string) getenv('LOKANDITA_COMMIT'), 0, 12));
$alamat = '/kelas/' . (int) $kelas['id'] . '/pertemuan/' . (int) $j['pertemuan_id'];
$rentang = ['0–9', '10–19', '20–29', '30–39', '40–49', '50–59', '60–69', '70–79', '80–89', '90–100'];
?>
<?= $this->section('kepala') ?>
    <script src="/grafik/chart.umd.min.js?v=<?= $versi ?>" defer></script>
    <script src="/js/grafik.js?v=<?= $versi ?>" defer></script>
<?= $this->endSection() ?>
<?= $this->section('isi') ?>
<?= view('_nav', ['akses' => $akses]) ?>
<p><a href="/kelas/<?= (int) $kelas['id'] ?>/analitik"><span class="panah-balik" aria-hidden="true"></span> <?= lang('Papan.analitik.judul') ?> — <span class="ltr"><?= esc($kelas['matkul_kode']) ?></span> <?= esc($kelas['name']) ?></a></p>
<h1><?= lang('Soal.pertemuan.ke', [(int) $j['number']]) ?>: <?= esc($j['title']) ?></h1>
<p class="redup">
    <span class="ltr"><?= esc($kelas['matkul_kode']) ?></span> <?= esc($kelas['name']) ?>
    · <span class="lencana keadaan-<?= $keadaan ?>"><?= lang('Latihan.keadaan.' . $keadaan) ?></span>
</p>
<p>
    <a href="<?= $alamat ?>/papan"><?= lang('Papan.lihat') ?></a>
    · <a href="<?= $alamat ?>/kiriman"><?= lang('Papan.kiriman.judul') ?></a>
    · <a href="<?= $alamat ?>"><?= lang('Latihan.jendela.jadwal') ?></a>
</p>

<h2><?= lang('Papan.sebaran.judul') ?></h2>
<?php if ($statistik['n'] === 0): ?>
    <div class="kartu"><p><?= lang('Papan.sebaran.kosong') ?></p></div>
<?php else: ?>
    <div class="kartu">
        <dl class="ringkasan-butir" data-statistik>
            <div><dt class="redup"><?= lang('Papan.sebaran.n') ?></dt><dd class="ltr"><?= (int) $statistik['n'] ?></dd></div>
            <div><dt class="redup"><?= lang('Papan.sebaran.belum') ?></dt><dd class="ltr"><?= (int) $belum ?></dd></div>
            <div><dt class="redup"><?= lang('Papan.sebaran.rata') ?></dt><dd class="ltr" data-rata><?= angka($statistik['rata']) ?></dd></div>
            <div><dt class="redup"><?= lang('Papan.sebaran.median') ?></dt><dd class="ltr" data-median><?= angka($statistik['median']) ?></dd></div>
            <div><dt class="redup"><?= lang('Papan.sebaran.simpangan') ?></dt><dd class="ltr" data-simpangan><?= angka($statistik['simpangan']) ?></dd></div>
            <div><dt class="redup"><?= lang('Papan.sebaran.rentang') ?></dt><dd class="ltr"><?= angka($statistik['min']) ?> – <?= angka($statistik['maks']) ?></dd></div>
        </dl>
        <?php // Grafik digambar /js/grafik.js dari tabel di bawahnya; tanpa skrip, tabelnya tetap terbaca. ?>
        <div class="grafik-wadah"><canvas data-grafik="sebaran" data-label="<?= esc(lang('Papan.sebaran.jumlah')) ?>" role="img" aria-label="<?= esc(lang('Papan.sebaran.judul')) ?>"></canvas></div>
        <div class="tabel-wadah">
            <table data-sebaran>
                <caption class="redup"><?= lang('Papan.sebaran.tabel') ?></caption>
                <thead><tr><?php foreach ($rentang as $r): ?><th scope="col" class="ltr"><?= $r ?></th><?php endforeach ?></tr></thead>
                <tbody><tr><?php foreach ($statistik['sebaran'] as $n): ?><td class="ltr"><?= (int) $n ?></td><?php endforeach ?></tr></tbody>
            </table>
        </div>
        <p class="petunjuk"><?= lang('Papan.sebaran.info') ?></p>
    </div>
<?php endif ?>

<h2><?= lang('Papan.soal.judul') ?></h2>
<?= view('_statistik_soal', ['soal' => $soal, 'tautKiriman' => $alamat . '/kiriman']) ?>

<h2><?= lang('Papan.analitik.mahasiswa') ?></h2>
<?php if ($nilai === []): ?>
    <div class="kartu"><p><?= lang('Papan.sebaran.kosong') ?></p></div>
<?php else: ?>
    <div class="tabel-wadah kartu">
        <table data-nilai-mahasiswa>
            <thead>
            <tr>
                <th scope="col"><?= lang('Papan.nomorInduk') ?></th>
                <th scope="col"><?= lang('Papan.nama') ?></th>
                <th scope="col"><?= lang('Papan.nilai') ?></th>
                <th scope="col"><?= lang('Papan.total') ?></th>
                <th scope="col"><?= lang('Papan.jumlahKiriman') ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($nilai as $n): ?>
                <tr>
                    <td class="ltr"><?= esc($orang[$n['user_id']]['nomor_induk'] ?? '—') ?></td>
                    <td><?= esc($orang[$n['user_id']]['nama'] ?? '—') ?></td>
                    <?php if ($n['score'] === null): ?>
                        <td colspan="3"><span class="teks-peringatan"><?= lang('Papan.analitik.belumKirim') ?></span></td>
                    <?php else: ?>
                        <td class="ltr"><strong><?= angka($n['score']) ?></strong></td>
                        <td class="ltr"><?= angka($n['total_points']) ?></td>
                        <td class="ltr"><a href="<?= $alamat ?>/kiriman?mahasiswa=<?= esc($n['user_id']) ?>"><?= (int) $n['submissions'] ?></a></td>
                    <?php endif ?>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
<?php endif ?>
<?= $this->endSection() ?>
