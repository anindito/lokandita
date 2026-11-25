<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<?= view('_nav', ['akses' => $akses]) ?>
<p><a href="/mata-kuliah/<?= (int) $kelas['course_id'] ?>"><span class="panah-balik" aria-hidden="true"></span> <?= lang('Ujian.kelas.kembali') ?></a></p>
<h1>
    <span class="ltr"><?= esc($kelas['matkul_kode']) ?></span> <?= esc($kelas['name']) ?>
    <?php if ($kelas['archived_at'] !== null): ?><span class="lencana"><?= lang('Ujian.kelas.diarsipkan') ?></span><?php endif ?>
</h1>

<div class="kartu">
    <dl>
        <dt class="redup"><?= lang('Ujian.matkul.satu') ?></dt>
        <dd><?= esc($kelas['matkul_nama']) ?></dd>
        <dt class="redup"><?= lang('Ujian.tahun.satu') ?></dt>
        <dd><?= esc($kelas['tahun_label']) ?><?= $kelas['tahun_status'] === 'archived' ? ' (' . lang('Ujian.tahun.diarsipkan') . ')' : '' ?></dd>
        <dt class="redup"><?= lang('Ujian.matkul.pengampu') ?></dt>
        <dd><?= esc($orang[$kelas['owner_id']]['nama'] ?? lang('Ujian.akunHilang')) ?></dd>
        <dt class="redup"><?= lang('Ujian.kelas.pengajar') ?></dt>
        <dd><?= view('_pengajar', ['daftar' => $pengajar, 'orang' => $orang]) ?></dd>
    </dl>
</div>

<h2><?= lang('Sesi.judulKelas') ?></h2>
<?php if ($sesi === []): ?>
    <div class="kartu"><p><?= lang('Sesi.kosongKelas') ?></p></div>
<?php else: ?>
    <?= view('_sesi_tabel', ['sesi' => $sesi]) ?>
<?php endif ?>

<h2><?= lang('Ujian.kelas.mahasiswa') ?> (<span data-jumlah-mahasiswa><?= count($mahasiswa) ?></span>)</h2>
<?php if ($mahasiswa === []): ?>
    <div class="kartu"><p><?= lang('Ujian.kelas.tanpaMahasiswa') ?></p></div>
<?php else: ?>
    <div class="tabel-wadah kartu">
        <table>
            <thead>
            <tr>
                <th scope="col"><?= lang('Ujian.mhs.nomorInduk') ?></th>
                <th scope="col"><?= lang('Ujian.mhs.nama') ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($mahasiswa as $id): ?>
                <tr>
                    <td class="ltr"><?= esc($orang[$id]['nomor_induk'] ?? '—') ?></td>
                    <td><?= esc($orang[$id]['nama'] ?? lang('Ujian.akunHilang')) ?></td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
<?php endif ?>
<?= $this->endSection() ?>
