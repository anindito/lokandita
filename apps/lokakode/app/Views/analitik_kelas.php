<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<?php
/**
 * Analitik satu kelas, semua pertemuan (SPEC.md §12). Variabel: $kelas, $pertemuan (terbit),
 * $rekap (mahasiswa AKTIF => nilai per pertemuan), $berisiko, $orang (direktori nama).
 */
use App\Libraries\Analitik;

$kid  = (int) $kelas['id'];
$nama = static fn (string $u): string => $orang[$u]['nama'] ?? '—';
$urut = $rekap;
uasort($urut, static fn (array $a, array $b): int => ($orang[$a['user_id']]['nomor_induk'] ?? '') <=> ($orang[$b['user_id']]['nomor_induk'] ?? ''));
?>
<?= view('_nav', ['akses' => $akses]) ?>
<p><a href="/kelas/<?= $kid ?>"><span class="panah-balik" aria-hidden="true"></span> <span class="ltr"><?= esc($kelas['matkul_kode']) ?></span> <?= esc($kelas['name']) ?></a></p>
<h1><?= lang('Papan.analitik.judul') ?>: <span class="ltr"><?= esc($kelas['matkul_kode']) ?></span> <?= esc($kelas['name']) ?></h1>
<p class="redup"><?= lang('Papan.analitik.lingkup', [count($rekap)]) ?></p>

<div class="baris-tombol">
    <a class="tombol tombol-sekunder" href="/kelas/<?= $kid ?>/ekspor/xlsx"><?= lang('Papan.ekspor.xlsx') ?></a>
    <a class="tombol tombol-sekunder" href="/kelas/<?= $kid ?>/ekspor/csv"><?= lang('Papan.ekspor.csv') ?></a>
    <?php if ((int) $kelas['boleh_kelola'] === 1): ?>
        <a class="tombol tombol-sekunder" href="/mata-kuliah/<?= (int) $kelas['course_id'] ?>/analitik"><?= lang('Papan.banding.judul') ?></a>
    <?php endif ?>
</div>
<p class="petunjuk"><?= lang('Papan.ekspor.info') ?></p>

<h2><?= lang('Papan.risiko.judul') ?></h2>
<?php if ($berisiko === []): ?>
    <div class="kartu"><p><?= lang('Papan.risiko.kosong') ?></p></div>
<?php else: ?>
    <div class="tabel-wadah kartu">
        <table data-berisiko>
            <thead>
            <tr>
                <th scope="col"><?= lang('Papan.nomorInduk') ?></th>
                <th scope="col"><?= lang('Papan.nama') ?></th>
                <th scope="col"><?= lang('Papan.risiko.tanpa') ?></th>
                <th scope="col"><?= lang('Papan.risiko.rendah', [Analitik::NILAI_RENDAH]) ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($berisiko as $b): ?>
                <tr>
                    <td class="ltr"><?= esc($orang[$b['user_id']]['nomor_induk'] ?? '—') ?></td>
                    <td><?= esc($nama($b['user_id'])) ?></td>
                    <td class="ltr"><?= $b['tanpa'] === [] ? '—' : esc(implode(', ', $b['tanpa'])) ?></td>
                    <td class="ltr"><?= $b['rendah'] === [] ? '—' : esc(implode(', ', $b['rendah'])) ?></td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
<?php endif ?>
<p class="petunjuk"><?= lang('Papan.risiko.info', [Analitik::NILAI_RENDAH, Analitik::RENDAH_BERULANG]) ?></p>

<h2><?= lang('Papan.analitik.rekap') ?></h2>
<?php if ($pertemuan === []): ?>
    <div class="kartu"><p><?= lang('Papan.analitik.tanpaPertemuan') ?></p></div>
<?php else: ?>
    <div class="tabel-wadah kartu">
        <table data-rekap>
            <thead>
            <tr>
                <th scope="col"><?= lang('Papan.nomorInduk') ?></th>
                <th scope="col"><?= lang('Papan.nama') ?></th>
                <?php foreach ($pertemuan as $p): ?>
                    <th scope="col"><a href="/kelas/<?= $kid ?>/pertemuan/<?= (int) $p['id'] ?>/analitik" title="<?= esc($p['title']) ?>"><span class="ltr">P<?= (int) $p['number'] ?></span></a></th>
                <?php endforeach ?>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($urut as $r): ?>
                <tr>
                    <td class="ltr"><?= esc($orang[$r['user_id']]['nomor_induk'] ?? '—') ?></td>
                    <td><?= esc($nama($r['user_id'])) ?></td>
                    <?php foreach ($pertemuan as $p): ?>
                        <?php $n = $r['nilai'][(int) $p['id']] ?? null; ?>
                        <td class="ltr"><?= $n === null ? '—' : angka($n['nilai']) ?></td>
                    <?php endforeach ?>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
    <p class="petunjuk"><?= lang('Papan.analitik.rekapInfo') ?></p>
<?php endif ?>
<?= $this->endSection() ?>
