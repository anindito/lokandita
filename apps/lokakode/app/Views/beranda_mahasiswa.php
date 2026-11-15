<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<h1><?= lang('Lokandita.halo', [esc($akses->identitas()->nama)]) ?></h1>

<h2><?= lang('Kode.saya.judul') ?></h2>
<?php if ($kelas === []): ?>
    <div class="kartu"><p><?= lang('Kode.saya.kosong') ?></p></div>
<?php else: ?>
    <div class="tabel-wadah kartu">
        <table>
            <thead>
            <tr>
                <th scope="col"><?= lang('Kode.matkul.judul') ?></th>
                <th scope="col"><?= lang('Kode.kelas.nama') ?></th>
                <th scope="col"><?= lang('Kode.saya.tahun') ?></th>
                <th scope="col"><?= lang('Kode.mhs.status') ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($kelas as $k): ?>
                <tr>
                    <td><span class="ltr"><?= esc($k['matkul_kode']) ?></span> — <?= esc($k['matkul_nama']) ?></td>
                    <td><?= esc($k['kelas_nama']) ?></td>
                    <td><?= esc($k['tahun_label']) ?></td>
                    <td><?= lang('Kode.mhs.statusNama.' . $k['status']) ?></td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
    <p class="redup"><?= lang('Kode.saya.info') ?></p>
<?php endif ?>
<?= $this->endSection() ?>
