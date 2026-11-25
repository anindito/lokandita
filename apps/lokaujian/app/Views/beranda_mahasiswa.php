<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<h1><?= lang('Lokandita.halo', [esc($akses->identitas()->nama)]) ?></h1>

<h2><?= lang('Ujian.saya.ujianJudul') ?></h2>
<?php // Daftar ujian baru terisi di tahap U3; sampai saat itu selalu kosong. ?>
<div class="kartu" data-ujian-kosong><p><?= $kelas === [] ? lang('Ujian.saya.tanpaKelas') : lang('Ujian.saya.ujianKosong') ?></p></div>

<?php if ($kelas !== []): ?>
    <h2><?= lang('Ujian.saya.kelasJudul') ?></h2>
    <div class="tabel-wadah kartu">
        <table>
            <thead>
            <tr>
                <th scope="col"><?= lang('Ujian.matkul.satu') ?></th>
                <th scope="col"><?= lang('Ujian.kelas.satu') ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($kelas as $k): ?>
                <tr data-kelas-saya>
                    <td><span class="ltr"><?= esc($k['matkul_kode']) ?></span> — <?= esc($k['matkul_nama']) ?></td>
                    <td><?= esc($k['kelas_nama']) ?></td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
<?php endif ?>
<?= $this->endSection() ?>
