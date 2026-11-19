<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<?php
/**
 * Papan peringkat kelas mahasiswa itu sendiri (SPEC.md §3: "own class only").
 * Variabel: $p (baris Ruang::pertemuan), $papan (Papan::untukMahasiswa — tanpa id dan
 * tanpa nomor induk siapa pun), $samar (bool).
 */
$saya = null;
foreach ($papan as $b) {
    if ($b['saya']) {
        $saya = $b;
    }
}
?>
<p><a href="/latihan/<?= (int) $p['pertemuan_id'] ?>"><span class="panah-balik" aria-hidden="true"></span> <?= lang('Soal.pertemuan.ke', [(int) $p['number']]) ?>: <?= esc($p['title']) ?></a></p>
<h1><?= lang('Papan.judul') ?></h1>
<p class="redup"><span class="ltr"><?= esc($p['matkul_kode']) ?></span> — <?= lang('Soal.pertemuan.ke', [(int) $p['number']]) ?> · <?= lang('Papan.kelasSaya', [esc($p['kelas_nama'])]) ?></p>

<div class="kartu" role="status">
    <p><strong><?= $saya === null ? lang('Papan.belumAda') : lang('Papan.posisiAnda', [(int) $saya['peringkat'], count($papan)]) ?></strong></p>
    <?php if ($samar): ?><p class="petunjuk"><?= lang('Papan.samar') ?></p><?php endif ?>
</div>

<?php if ($papan === []): ?>
    <div class="kartu"><p><?= lang('Papan.kosong') ?></p></div>
<?php else: ?>
    <div class="tabel-wadah kartu">
        <table data-papan>
            <thead>
            <tr>
                <th scope="col"><?= lang('Papan.peringkat') ?></th>
                <th scope="col"><?= lang('Papan.nama') ?></th>
                <th scope="col"><?= lang('Papan.nilai') ?></th>
                <th scope="col"><?= lang('Papan.total') ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($papan as $b): ?>
                <tr <?= $b['saya'] ? 'class="baris-saya" aria-current="true"' : '' ?>>
                    <td class="ltr"><?= (int) $b['peringkat'] ?></td>
                    <td><?= $b['nama'] === null ? lang('Papan.peserta', [(int) $b['urut']]) : esc($b['nama']) ?><?= $b['saya'] ? ' <strong>' . lang('Papan.anda') . '</strong>' : '' ?></td>
                    <td class="ltr"><strong><?= angka($b['nilai']) ?></strong></td>
                    <td class="ltr"><?= angka($b['total']) ?></td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
    <p class="petunjuk"><?= lang('Papan.urutan') ?></p>
<?php endif ?>
<?= $this->endSection() ?>
