<?php
/**
 * Foto pembuka halaman depan (KONTRAK.md §16): foto yang sedang tayang, form unggah, dan
 * semua foto yang pernah diunggah. Variabel: $semua (baris `foto`, terbaru dulu), $idTayang
 * (null = foto bawaan), $bawaan, $orang, $isian, $galat, $galatBerkas, $batas.
 */
use App\Controllers\Admin\Teks;
use App\Libraries\Foto;

$salah = static fn (string $nama): bool => in_array($nama, $galat, true);
$atr   = static fn (string $nama): string => $salah($nama) ? ' aria-invalid="true" aria-describedby="g-' . $nama . '"' : '';
?>
<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<p><a class="panah-balik" href="/admin/teks"><?= lang('Sunting.judul') ?></a></p>
<h1><?= lang('Sunting.foto.judul') ?></h1>
<p><?= lang('Sunting.foto.pengantar') ?></p>

<h2><?= lang('Sunting.foto.unggahJudul') ?></h2>
<form method="post" action="/admin/teks/foto" enctype="multipart/form-data" class="kartu" data-form-foto>
    <?= csrf_field() ?>
    <label for="f-foto"><?= lang('Sunting.foto.kolom.berkas') ?></label>
    <input type="file" id="f-foto" name="foto" accept="image/jpeg,image/png,image/webp" required<?= $galatBerkas !== null ? ' aria-invalid="true" aria-describedby="g-foto"' : '' ?>>
    <p class="petunjuk"><?= lang('Sunting.foto.syarat', [$batas['mb'], $batas['lebar'], $batas['tinggi'], $batas['mp']]) ?></p>
    <?php if ($galatBerkas !== null): ?>
        <p class="galat-kolom" id="g-foto" data-galat-berkas="<?= esc($galatBerkas) ?>"><?= lang('Sunting.foto.galatBerkas.' . $galatBerkas, [$batas['mb'], $batas['lebar'], $batas['tinggi'], $batas['mp']]) ?></p>
    <?php endif ?>

    <?php foreach (['id', 'en', 'ar'] as $b): ?>
        <label for="f-alt-<?= $b ?>"><?= lang('Sunting.foto.kolom.alt') ?> — <span lang="<?= $b ?>"><?= lang('Lokandita.bahasa' . ucfirst($b)) ?></span></label>
        <input type="text" id="f-alt-<?= $b ?>" name="alt_<?= $b ?>" value="<?= esc($isian['alt_' . $b] ?? '') ?>" maxlength="300" required dir="<?= \Lokandita\Auth\Locale::arah($b) ?>" lang="<?= $b ?>"<?= $atr('alt_' . $b) ?>>
        <?php if ($salah('alt_' . $b)): ?><p class="galat-kolom" id="g-alt_<?= $b ?>"><?= lang('Sunting.foto.galatWajib') ?></p><?php endif ?>
    <?php endforeach ?>
    <p class="petunjuk"><?= lang('Sunting.foto.petunjukAlt') ?></p>

    <label for="f-sumber"><?= lang('Sunting.foto.kolom.sumber') ?></label>
    <input type="text" id="f-sumber" name="sumber" value="<?= esc($isian['sumber'] ?? '') ?>" maxlength="200" required<?= $atr('sumber') ?>>
    <p class="petunjuk"><?= lang('Sunting.foto.petunjukSumber') ?></p>
    <?php if ($salah('sumber')): ?><p class="galat-kolom" id="g-sumber"><?= lang('Sunting.foto.galatWajib') ?></p><?php endif ?>

    <label for="f-lisensi"><?= lang('Sunting.foto.kolom.lisensi') ?></label>
    <input type="text" id="f-lisensi" name="lisensi" value="<?= esc($isian['lisensi'] ?? '') ?>" maxlength="200" required<?= $atr('lisensi') ?>>
    <p class="petunjuk"><?= lang('Sunting.foto.petunjukLisensi') ?></p>
    <?php if ($salah('lisensi')): ?><p class="galat-kolom" id="g-lisensi"><?= lang('Sunting.foto.galatWajib') ?></p><?php endif ?>

    <label class="centang" for="f-berhak">
        <input type="checkbox" id="f-berhak" name="berhak" value="1" required<?= $atr('berhak') ?>>
        <span><?= lang('Sunting.foto.kolom.berhak') ?></span>
    </label>
    <?php if ($salah('berhak')): ?><p class="galat-kolom" id="g-berhak"><?= lang('Sunting.foto.galatBerhak') ?></p><?php endif ?>

    <div class="baris-tombol">
        <button type="submit" class="tombol"><?= lang('Sunting.foto.tombolUnggah') ?></button>
    </div>
    <p class="petunjuk"><?= lang('Sunting.foto.belumTayang') ?></p>
</form>

<h2><?= lang('Sunting.foto.daftarJudul') ?></h2>
<ul class="daftar-foto">
    <li class="kartu" data-foto="bawaan">
        <img src="<?= esc($bawaan['dasar'] . '-480.jpg' . $bawaan['versi']) ?>" width="480" height="<?= (int) round(480 * $bawaan['tinggi'] / $bawaan['lebar']) ?>" alt="<?= esc($bawaan['alt']) ?>" loading="lazy">
        <div>
            <p><strong><?= lang('Sunting.foto.bawaan') ?></strong>
                <?php if ($idTayang === null): ?><span class="lencana lencana-active" data-tayang><?= lang('Sunting.foto.sedangTayang') ?></span><?php endif ?></p>
            <p class="redup"><?= lang('Sunting.foto.kolom.sumber') ?>: <?= esc($bawaan['sumber']) ?><br><?= lang('Sunting.foto.kolom.lisensi') ?>: <?= esc($bawaan['lisensi']) ?></p>
            <?php if ($idTayang !== null): ?>
                <form method="post" action="/admin/teks/foto/bawaan">
                    <?= csrf_field() ?>
                    <button type="submit" class="tombol tombol-sekunder"><?= lang('Sunting.foto.tombolBawaan') ?></button>
                </form>
            <?php endif ?>
        </div>
    </li>
    <?php foreach ($semua as $f): ?>
        <li class="kartu" data-foto="<?= (int) $f['id'] ?>">
            <img src="<?= esc(Foto::ALAMAT . '/' . $f['kunci'] . '-480.jpg') ?>" width="480" height="<?= (int) round(480 * $f['tinggi'] / $f['lebar']) ?>" alt="<?= esc($f['alt_id']) ?>" loading="lazy">
            <div>
                <p><strong class="ltr"><?= esc($f['nama_asli']) ?></strong>
                    <?php if ($idTayang === (int) $f['id']): ?>
                        <span class="lencana lencana-active" data-tayang><?= lang('Sunting.foto.sedangTayang') ?></span>
                    <?php elseif ($f['published_at'] === null): ?>
                        <span class="lencana lencana-segera"><?= lang('Sunting.foto.belumPernah') ?></span>
                    <?php endif ?></p>
                <p class="redup"><?= lang('Sunting.foto.kolom.sumber') ?>: <?= esc($f['sumber']) ?><br><?= lang('Sunting.foto.kolom.lisensi') ?>: <?= esc($f['lisensi']) ?><br>
                    <?= esc($orang[$f['uploaded_by']]['nama'] ?? '—') ?>, <span class="ltr"><?= esc(Teks::wib($f['uploaded_at'])) ?></span></p>
                <div class="baris-tombol">
                    <a href="/admin/teks/foto/<?= (int) $f['id'] ?>"><?= lang('Sunting.foto.lihat') ?></a>
                    <?php if ($idTayang !== (int) $f['id'] && $f['published_at'] !== null): ?>
                        <form method="post" action="/admin/teks/foto/<?= (int) $f['id'] ?>/tayang">
                            <?= csrf_field() ?>
                            <button type="submit" class="tombol tombol-sekunder"><?= lang('Sunting.foto.tombolLagi') ?></button>
                        </form>
                    <?php endif ?>
                </div>
            </div>
        </li>
    <?php endforeach ?>
</ul>
<p class="petunjuk"><?= lang('Sunting.foto.catatan') ?></p>
<?= $this->endSection() ?>
