<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<?php
/**
 * Pengumuman satu kelas untuk dosen (SPEC.md §14). Variabel: $kelas, $daftar (Pengumuman::kelas,
 * termasuk yang ditarik), $orang, $bolehTulis, $isian (judul, isi yang sedang diketik).
 */
use App\Libraries\Pengumuman;
use App\Libraries\Waktu;

$alamat = '/kelas/' . (int) $kelas['id'] . '/pengumuman';
?>
<?= view('_nav', ['akses' => $akses]) ?>
<p><a href="/kelas/<?= (int) $kelas['id'] ?>"><span class="panah-balik" aria-hidden="true"></span> <span class="ltr"><?= esc($kelas['matkul_kode']) ?></span> <?= esc($kelas['name']) ?></a></p>
<h1><?= lang('Komunikasi.pengumuman.judul') ?>: <span class="ltr"><?= esc($kelas['matkul_kode']) ?></span> <?= esc($kelas['name']) ?></h1>
<p class="redup"><?= lang('Komunikasi.pengumuman.penjelasan') ?></p>

<?php if ($bolehTulis): ?>
    <div class="kartu">
        <form method="post" action="<?= $alamat ?>">
            <?= csrf_field() ?>
            <label for="judul-pengumuman"><?= lang('Komunikasi.pengumuman.judulIsian') ?></label>
            <input type="text" id="judul-pengumuman" name="judul" maxlength="<?= Pengumuman::JUDUL_MAKS ?>" value="<?= esc($isian['judul']) ?>" required>
            <label for="isi-pengumuman"><?= lang('Komunikasi.pengumuman.isi') ?></label>
            <textarea id="isi-pengumuman" name="isi" rows="5" maxlength="<?= Pengumuman::ISI_MAKS ?>" required><?= esc($isian['isi']) ?></textarea>
            <p class="petunjuk"><?= lang('Komunikasi.pengumuman.info') ?></p>
            <div class="baris-tombol"><button type="submit" class="tombol"><?= lang('Komunikasi.pengumuman.kirim') ?></button></div>
        </form>
    </div>
<?php else: ?>
    <p class="petunjuk"><?= lang('Kode.kelas.tahunArsip') ?></p>
<?php endif ?>

<?php if ($daftar === []): ?>
    <div class="kartu"><p><?= lang('Komunikasi.pengumuman.kosong') ?></p></div>
<?php else: ?>
    <ul class="daftar-kabar" data-pengumuman>
    <?php foreach ($daftar as $a): ?>
        <?php $ditarik = $a['withdrawn_at'] !== null; ?>
        <li class="kartu" data-ditarik="<?= $ditarik ? '1' : '0' ?>">
            <p><strong><?= esc($a['title']) ?></strong>
                <?php if ($ditarik): ?><span class="lencana lencana-galat"><?= lang('Komunikasi.pengumuman.ditarik') ?></span><?php endif ?></p>
            <p class="kabar-isi<?= $ditarik ? ' redup' : '' ?>"><?= esc($a['body']) ?></p>
            <p class="petunjuk"><?= esc($orang[$a['author_id']]['nama'] ?? '—') ?> · <span class="ltr"><?= esc(Waktu::lokal($a['created_at'])) ?></span>
                <?php if ($ditarik): ?> · <?= lang('Komunikasi.pengumuman.ditarikOleh', [esc($orang[$a['withdrawn_by']]['nama'] ?? '—'), esc(Waktu::lokal($a['withdrawn_at']))]) ?><?php endif ?></p>
            <?php if (! $ditarik && $bolehTulis): ?>
                <form method="post" action="<?= $alamat ?>/<?= (int) $a['id'] ?>/tarik" class="inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="tombol tombol-sekunder"><?= lang('Komunikasi.pengumuman.tarik') ?></button>
                </form>
            <?php endif ?>
        </li>
    <?php endforeach ?>
    </ul>
<?php endif ?>
<?= $this->endSection() ?>
