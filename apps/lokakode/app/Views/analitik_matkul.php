<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?php
/**
 * Perbandingan antarkelas dan kalibrasi soal untuk pengampu (SPEC.md §8.4, §12).
 * Variabel: $matkul, $tahun, $banding (kelas, pertemuan, sel), $pilih (id pertemuan), $soal.
 */
$versi = esc(substr((string) getenv('LOKANDITA_COMMIT'), 0, 12));
$mid   = (int) $matkul['id'];
?>
<?= $this->section('kepala') ?>
    <script src="/grafik/chart.umd.min.js?v=<?= $versi ?>" defer></script>
    <script src="/js/grafik.js?v=<?= $versi ?>" defer></script>
<?= $this->endSection() ?>
<?= $this->section('isi') ?>
<?= view('_nav', ['akses' => $akses]) ?>
<p><a href="/mata-kuliah/<?= $mid ?>"><span class="panah-balik" aria-hidden="true"></span> <span class="ltr"><?= esc($matkul['code']) ?></span> — <?= esc($matkul['name']) ?></a></p>
<h1><?= lang('Papan.banding.judul') ?>: <span class="ltr"><?= esc($matkul['code']) ?></span></h1>
<?php if ($tahun === null): ?>
    <div class="kartu"><p><?= lang('Papan.tanpaTahun') ?></p></div>
<?php else: ?>
    <p class="redup"><?= lang('Papan.lingkupMatkul', [esc($tahun['label'])]) ?></p>
    <div class="baris-tombol">
        <a class="tombol tombol-sekunder" href="/mata-kuliah/<?= $mid ?>/ekspor/xlsx"><?= lang('Papan.ekspor.xlsx') ?></a>
        <a class="tombol tombol-sekunder" href="/mata-kuliah/<?= $mid ?>/ekspor/csv"><?= lang('Papan.ekspor.csv') ?></a>
    </div>

    <?php if ($banding['kelas'] === [] || $banding['pertemuan'] === []): ?>
        <div class="kartu"><p><?= lang('Papan.banding.kosong') ?></p></div>
    <?php else: ?>
        <div class="kartu">
            <div class="grafik-wadah"><canvas data-grafik="banding" data-label="<?= esc(lang('Papan.sebaran.rata')) ?>" role="img" aria-label="<?= esc(lang('Papan.banding.judul')) ?>"></canvas></div>
            <div class="tabel-wadah">
                <table data-banding>
                    <caption class="redup"><?= lang('Papan.banding.tabel') ?></caption>
                    <thead>
                    <tr>
                        <th scope="col"><?= lang('Soal.pertemuan.judul') ?></th>
                        <?php foreach ($banding['kelas'] as $k): ?>
                            <th scope="col" data-kelas="<?= esc($k['name']) ?>"><a href="/kelas/<?= (int) $k['id'] ?>/analitik"><?= esc($k['name']) ?></a> <span class="redup ltr">(<?= (int) $k['aktif'] ?>)</span></th>
                        <?php endforeach ?>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($banding['pertemuan'] as $p): ?>
                        <tr>
                            <th scope="row"><a href="/pertemuan/<?= (int) $p['id'] ?>/papan" title="<?= esc($p['title']) ?>"><span class="ltr" data-pertemuan>P<?= (int) $p['number'] ?></span></a></th>
                            <?php foreach ($banding['kelas'] as $k): ?>
                                <?php $s = $banding['sel'][(int) $p['id']][(int) $k['id']]; ?>
                                <td class="ltr" data-rata="<?= $s['rata'] === null ? '' : esc((string) $s['rata']) ?>">
                                    <?php if ($s['n'] === 0): ?>—<?php else: ?>
                                        <strong><?= angka($s['rata']) ?></strong> · <?= angka($s['median']) ?> · <?= (int) $s['mengirim'] ?>/<?= (int) $s['n'] ?>
                                    <?php endif ?>
                                </td>
                            <?php endforeach ?>
                        </tr>
                    <?php endforeach ?>
                    </tbody>
                </table>
            </div>
            <p class="petunjuk"><?= lang('Papan.banding.info') ?></p>
        </div>

        <h2><?= lang('Papan.soal.kalibrasi') ?></h2>
        <form method="get" action="/mata-kuliah/<?= $mid ?>/analitik" class="baris-tombol">
            <label for="pertemuan"><?= lang('Soal.pertemuan.judul') ?></label>
            <select id="pertemuan" name="pertemuan">
                <?php foreach ($banding['pertemuan'] as $p): ?>
                    <option value="<?= (int) $p['id'] ?>" <?= (int) $p['id'] === $pilih ? 'selected' : '' ?>><?= lang('Soal.pertemuan.ke', [(int) $p['number']]) ?>: <?= esc($p['title']) ?></option>
                <?php endforeach ?>
            </select>
            <button type="submit" class="tombol tombol-sekunder"><?= lang('Papan.soal.tampilkan') ?></button>
        </form>
        <?= view('_statistik_soal', ['soal' => $soal, 'tautKiriman' => null]) ?>
    <?php endif ?>
<?php endif ?>
<?= $this->endSection() ?>
