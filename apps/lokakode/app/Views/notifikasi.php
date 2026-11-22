<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<?php
/** Pemberitahuan mahasiswa itu sendiri. Variabel: $daftar (Notifikasi::daftar), $orang (nama pereset kata sandi). */
use App\Libraries\Waktu;

$belum = count(array_filter($daftar, static fn (array $n): bool => $n['read_at'] === null));
?>
<?= view('_nav', ['akses' => $akses, 'notifBelum' => $notifBelum ?? 0]) ?>
<h1><?= lang('Komunikasi.notifikasi.judul') ?></h1>
<p class="redup"><?= lang('Komunikasi.notifikasi.penjelasan') ?></p>
<?php if ($daftar === []): ?>
    <div class="kartu"><p><?= lang('Komunikasi.notifikasi.kosong') ?></p></div>
<?php else: ?>
    <?php if ($belum > 0): ?>
        <form method="post" action="/notifikasi/baca" class="baris-tombol">
            <?= csrf_field() ?>
            <button type="submit" class="tombol tombol-sekunder"><?= lang('Komunikasi.notifikasi.tandai') ?></button>
        </form>
    <?php endif ?>
    <ul class="daftar-kabar" data-notifikasi>
    <?php foreach ($daftar as $n): ?>
        <?php $d = $n['data']; ?>
        <li class="kartu<?= $n['read_at'] === null ? ' kabar-baru' : '' ?>" data-jenis="<?= esc($n['kind']) ?>">
            <p>
                <?php if ($n['read_at'] === null): ?><span class="lencana"><?= lang('Komunikasi.notifikasi.baru') ?></span><?php endif ?>
                <?php if ($n['kind'] === 'sandi_direset'): ?>
                    <?= lang('Komunikasi.notifikasi.sandi_direset', [esc($orang[$d['oleh'] ?? '']['nama'] ?? lang('Komunikasi.notifikasi.seorangDosen')), esc((string) ($d['kelas'] ?? ''))]) ?>
                <?php else: ?>
                    <a href="/latihan/<?= (int) ($d['pertemuan'] ?? 0) ?>"><?= lang('Komunikasi.notifikasi.' . $n['kind'], [esc((string) ($d['matkul'] ?? '')), (int) ($d['nomor'] ?? 0), esc((string) ($d['judul'] ?? ''))]) ?></a>
                    <?php if (($d['tutup'] ?? null) !== null): ?><br><?= lang('Komunikasi.notifikasi.tutupPada', [esc(Waktu::lokal($d['tutup']))]) ?><?php endif ?>
                <?php endif ?>
            </p>
            <p class="petunjuk ltr"><?= esc(Waktu::lokal($n['created_at'])) ?></p>
        </li>
    <?php endforeach ?>
    </ul>
<?php endif ?>
<?= $this->endSection() ?>
