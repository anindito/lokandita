<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<?php
/**
 * Papan peringkat untuk dosen. Variabel: $lingkup ('kelas' | 'matkul'), $kelas (atau null),
 * $p (baris pertemuan), $papan (Papan::bernama), $aktif (jumlah mahasiswa aktif, hanya lingkup kelas),
 * $tahun (hanya lingkup matkul).
 */
use App\Libraries\Waktu;

$pid = (int) $p['pertemuan_id'];
?>
<?= view('_nav', ['akses' => $akses]) ?>
<?php if ($lingkup === 'kelas'): ?>
    <p><a href="/kelas/<?= (int) $kelas['id'] ?>/pertemuan/<?= $pid ?>"><span class="panah-balik" aria-hidden="true"></span> <?= lang('Soal.pertemuan.ke', [(int) $p['number']]) ?> — <span class="ltr"><?= esc($kelas['matkul_kode']) ?></span> <?= esc($kelas['name']) ?></a></p>
<?php else: ?>
    <p><a href="/pertemuan/<?= $pid ?>"><span class="panah-balik" aria-hidden="true"></span> <?= lang('Soal.pertemuan.ke', [(int) $p['number']]) ?> — <span class="ltr"><?= esc($p['matkul_kode']) ?></span></a></p>
<?php endif ?>
<h1><?= lang('Papan.judul') ?>: <?= lang('Soal.pertemuan.ke', [(int) $p['number']]) ?> — <?= esc($p['title']) ?></h1>
<p class="redup">
    <?php if ($lingkup === 'kelas'): ?>
        <span class="ltr"><?= esc($kelas['matkul_kode']) ?></span> <?= esc($kelas['name']) ?> · <?= lang('Papan.sudahKirim', [count($papan), (int) $aktif]) ?>
    <?php elseif ($tahun === null): ?>
        <?= lang('Papan.tanpaTahun') ?>
    <?php else: ?>
        <?= lang('Papan.lingkupMatkul', [esc($tahun['label'])]) ?>
    <?php endif ?>
</p>
<?php if ($lingkup === 'kelas' && (int) $kelas['boleh_kelola'] === 1): ?>
    <p><a href="/pertemuan/<?= $pid ?>/papan"><?= lang('Papan.seMatkul') ?></a></p>
<?php endif ?>

<?php if ($papan === []): ?>
    <div class="kartu"><p><?= lang('Papan.kosong') ?></p></div>
<?php else: ?>
    <div class="tabel-wadah kartu">
        <table data-papan>
            <thead>
            <tr>
                <th scope="col"><?= lang('Papan.peringkat') ?></th>
                <th scope="col"><?= lang('Papan.nomorInduk') ?></th>
                <th scope="col"><?= lang('Papan.nama') ?></th>
                <?php if ($lingkup === 'matkul'): ?><th scope="col"><?= lang('Papan.kelas') ?></th><?php endif ?>
                <th scope="col"><?= lang('Papan.nilai') ?></th>
                <th scope="col"><?= lang('Papan.total') ?></th>
                <th scope="col"><?= lang('Papan.surplus') ?></th>
                <th scope="col"><?= lang('Papan.jumlahKiriman') ?></th>
                <th scope="col"><?= lang('Papan.tercapai') ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($papan as $b): ?>
                <tr>
                    <td class="ltr"><?= (int) $b['peringkat'] ?></td>
                    <td class="ltr"><?= esc($b['nomor_induk']) ?></td>
                    <td><?= esc($b['nama']) ?></td>
                    <?php if ($lingkup === 'matkul'): ?><td><?= esc($b['kelas']) ?></td><?php endif ?>
                    <td class="ltr"><strong><?= angka($b['nilai']) ?></strong></td>
                    <td class="ltr"><?= angka($b['total']) ?></td>
                    <td class="ltr"><?= angka($b['surplus']) ?></td>
                    <td class="ltr"><?= (int) $b['kiriman'] ?></td>
                    <td class="ltr"><?= esc(Waktu::lokal($b['tercapai'])) ?></td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
    <p class="petunjuk"><?= lang('Papan.urutan') ?></p>
    <?php if ((int) ($p['anonymize_leaderboard'] ?? 0) === 1): ?><p class="petunjuk"><?= lang('Papan.samarStaf') ?></p><?php endif ?>
<?php endif ?>
<?= $this->endSection() ?>
