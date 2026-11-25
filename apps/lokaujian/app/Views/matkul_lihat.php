<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<?= view('_nav', ['akses' => $akses]) ?>
<p><a href="/mata-kuliah"><span class="panah-balik" aria-hidden="true"></span> <?= lang('Ujian.matkul.kembali') ?></a></p>
<h1><span class="ltr"><?= esc($matkul['code']) ?></span> — <?= esc($matkul['name']) ?></h1>

<div class="kartu">
    <dl>
        <dt class="redup"><?= lang('Ujian.matkul.pengampu') ?></dt>
        <dd>
            <?= esc($orang[$matkul['owner_id']]['nama'] ?? lang('Ujian.akunHilang')) ?>
            <?= view('_peran_matkul', ['akses' => $akses, 'matkul' => $matkul]) ?>
        </dd>
        <?php if ($tahun !== null): ?>
            <dt class="redup"><?= lang('Ujian.tahun.aktif') ?></dt>
            <dd><?= esc($tahun['label']) ?></dd>
        <?php endif ?>
    </dl>
</div>

<?php if ((int) $matkul['diampu'] === 1): ?>
    <div class="baris-tombol" data-kelola>
        <a class="tombol" href="/mata-kuliah/<?= (int) $matkul['id'] ?>/bank"><?= lang('Bank.judul') ?></a>
        <a class="tombol tombol-sekunder" href="/mata-kuliah/<?= (int) $matkul['id'] ?>/ujian"><?= lang('Bank.ujian.judul') ?></a>
    </div>
<?php endif ?>

<h2><?= lang('Ujian.kelas.judul') ?></h2>
<?php if ($tahun === null): ?>
    <div class="kartu"><p><?= lang('Ujian.tahun.belumAda') ?></p></div>
<?php elseif ($kelas === []): ?>
    <div class="kartu"><p><?= lang('Ujian.kelas.kosong') ?></p></div>
<?php else: ?>
    <?= view('_kelas_tabel', ['kelas' => $kelas, 'pengajar' => $pengajar, 'orang' => $orang, 'denganMatkul' => false]) ?>
<?php endif ?>
<?= $this->endSection() ?>
