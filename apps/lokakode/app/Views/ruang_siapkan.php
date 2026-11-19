<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('kepala') ?>
    <script src="/js/siapkan.js?v=<?= esc(substr((string) getenv('LOKANDITA_COMMIT'), 0, 12)) ?>" defer></script>
<?= $this->endSection() ?>
<?= $this->section('isi') ?>
<?php $detik = $keadaan['perkiraan']; ?>
<p><a href="/latihan/<?= (int) $soal['pertemuan_id'] ?>"><span class="panah-balik" aria-hidden="true"></span> <?= lang('Latihan.soal.kembali') ?></a></p>
<h1><?= lang('Latihan.siapkan.judul') ?></h1>
<div class="kartu" role="status" aria-live="polite" data-siapkan="/api/soal/<?= (int) $soal['mp_id'] ?>/keadaan"
     data-teks-detik="<?= esc(lang('Latihan.siapkan.perkiraan')) ?>" data-teks-menit="<?= esc(lang('Latihan.siapkan.perkiraanLama')) ?>"
     data-teks-mati="<?= esc(lang('Latihan.siapkan.workerMati')) ?>" data-teks-gagal="<?= esc(lang('Latihan.siapkan.gagal')) ?>">
    <p><?= lang('Latihan.siapkan.info') ?></p>
    <progress></progress>
    <p data-siapkan-pesan>
        <?php if (! $keadaan['worker'] || $detik === null): ?>
            <strong class="teks-bahaya"><?= lang('Latihan.siapkan.workerMati') ?></strong>
        <?php elseif ($detik >= 120): ?>
            <?= lang('Latihan.siapkan.perkiraanLama', [(int) ceil($detik / 60)]) ?>
        <?php else: ?>
            <?= lang('Latihan.siapkan.perkiraan', [max(1, (int) $detik)]) ?>
        <?php endif ?>
    </p>
    <noscript><p class="petunjuk"><?= lang('Latihan.siapkan.tanpaSkrip') ?></p></noscript>
    <div class="baris-tombol">
        <a class="tombol tombol-sekunder" href="/latihan/soal/<?= (int) $soal['mp_id'] ?>"><?= lang('Latihan.siapkan.muatUlang') ?></a>
    </div>
</div>
<?= $this->endSection() ?>
