<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<?= view('_nav', ['akses' => $akses]) ?>
<h1><?= lang('Lokandita.halo', [esc($akses->identitas()->nama)]) ?></h1>

<?php if ($tahun === null): ?>
    <div class="kartu"><p><?= lang('Ujian.tahun.belumAda') ?></p></div>
<?php else: ?>
    <div class="kartu">
        <p class="redup"><?= lang('Ujian.tahun.aktif') ?></p>
        <p class="text-xl font-bold"><?= esc($tahun['label']) ?></p>
        <dl class="flex flex-wrap gap-8">
            <div><dt class="redup"><?= lang('Ujian.kelas.judul') ?></dt><dd class="text-xl font-bold" data-ringkas="kelas"><?= count($kelas) ?></dd></div>
            <div><dt class="redup"><?= lang('Ujian.kelas.mahasiswa') ?></dt><dd class="text-xl font-bold" data-ringkas="mahasiswa"><?= array_sum(array_map(static fn ($k) => (int) $k['jumlah_aktif'], $kelas)) ?></dd></div>
        </dl>
    </div>

    <h2><?= lang('Ujian.kelas.judul') ?></h2>
    <?php if ($kelas === []): ?>
        <div class="kartu"><p><?= $akses->superadmin() ? lang('Ujian.kelas.kosong') : lang('Ujian.kelas.kosongDosen') ?></p></div>
    <?php else: ?>
        <?= view('_kelas_tabel', ['kelas' => $kelas, 'pengajar' => $pengajar, 'orang' => $orang, 'denganMatkul' => true]) ?>
    <?php endif ?>
<?php endif ?>
<?= $this->endSection() ?>
