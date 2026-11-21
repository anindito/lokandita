<?php
/**
 * Form satu halaman dalam satu bahasa. Bentuk form mengikuti $bawaan (teks dari berkas
 * bahasa): teks satu baris menjadi kotak isian, daftar menjadi kotak teks satu-per-baris.
 * Variabel: $halaman, $bahasa, $bawaan, $nilai, $galat (kunci yang salah), $diubah,
 * $pratinjau (teks atau null), $pratinjauDepan, $kontak.
 */
$arah  = \Lokandita\Auth\Locale::arah($bahasa);
$salah = static fn (string $kunci): bool => in_array($kunci, $galat, true);
$atr   = static fn (string $kunci): string => 'dir="' . $arah . '" lang="' . $bahasa . '"' . ($salah($kunci) ? ' aria-invalid="true"' : '');
$baris = static fn (mixed $daftar): string => esc(implode("\n", is_array($daftar) ? $daftar : []));
$aksi  = '/admin/teks/' . $halaman . '/' . $bahasa;
?>
<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<p><a class="panah-balik" href="/admin/teks"><?= lang('Sunting.judul') ?></a></p>
<h1><?= lang('Sunting.halaman.' . $halaman) ?> — <span lang="<?= $bahasa ?>"><?= lang('Lokandita.bahasa' . ucfirst($bahasa)) ?></span></h1>

<?php if ($pratinjau !== null): ?>
    <section class="pratinjau" aria-label="<?= lang('Sunting.pratinjau') ?>">
        <p class="pratinjau-label"><strong><?= lang('Sunting.pratinjau') ?></strong> — <?= lang('Sunting.belumDisimpan') ?></p>
        <div dir="<?= $arah ?>" lang="<?= $bahasa ?>" data-pratinjau>
            <?php if (in_array($halaman, ['depan', 'aplikasi'], true)): ?>
                <?= view('publik/_depan', $pratinjauDepan) ?>
            <?php else: ?>
                <?= view('publik/_teks', ['halaman' => $pratinjau, 'kontak' => $kontak]) ?>
            <?php endif ?>
        </div>
    </section>
<?php endif ?>

<form method="post" action="<?= esc($aksi) ?>" class="kartu">
    <?= csrf_field() ?>
    <p class="petunjuk"><?= lang('Sunting.petunjuk') ?></p>
    <?php foreach ($bawaan as $kunci => $contoh): ?>
        <?php if ($kunci === 'diperbarui'): ?>
            <?php continue ?>
        <?php elseif ($kunci === 'bagian'): ?>
            <?php foreach ($contoh as $i => $bagian): ?>
                <?php $isian = $nilai['bagian'][$i] ?? [] ?>
                <fieldset class="bagian-sunting">
                    <legend><?= lang('Sunting.bagian', [$i + 1]) ?></legend>
                    <label for="b<?= $i ?>-judul"><?= lang('Sunting.kolom.bagianJudul') ?></label>
                    <input type="text" id="b<?= $i ?>-judul" name="t[bagian][<?= $i ?>][judul]" value="<?= esc($isian['judul'] ?? '') ?>" maxlength="400" required <?= $atr("bagian.{$i}.judul") ?>>
                    <label for="b<?= $i ?>-isi"><?= lang('Sunting.kolom.bagianIsi') ?></label>
                    <textarea id="b<?= $i ?>-isi" name="t[bagian][<?= $i ?>][isi]" rows="<?= max(3, count($isian['isi'] ?? []) * 3) ?>" <?= $atr("bagian.{$i}.isi") ?>><?= $baris($isian['isi'] ?? []) ?></textarea>
                    <?php if (array_key_exists('butir', $bagian)): ?>
                        <label for="b<?= $i ?>-butir"><?= lang('Sunting.kolom.bagianButir') ?></label>
                        <textarea id="b<?= $i ?>-butir" name="t[bagian][<?= $i ?>][butir]" rows="<?= max(3, count($isian['butir'] ?? []) * 2) ?>" <?= $atr("bagian.{$i}.butir") ?>><?= $baris($isian['butir'] ?? []) ?></textarea>
                    <?php endif ?>
                    <?php if ($salah("bagian.{$i}.judul") || $salah("bagian.{$i}.isi") || $salah("bagian.{$i}.butir")): ?>
                        <p class="galat-kolom"><?= lang('Sunting.galatBagian') ?></p>
                    <?php endif ?>
                </fieldset>
            <?php endforeach ?>
        <?php elseif (is_array($contoh)): ?>
            <label for="k-<?= esc($kunci) ?>"><?= lang('Sunting.kolom.' . $kunci) ?></label>
            <textarea id="k-<?= esc($kunci) ?>" name="t[<?= esc($kunci) ?>]" rows="<?= max(3, count($nilai[$kunci] ?? []) * 2) ?>" required <?= $atr((string) $kunci) ?>><?= $baris($nilai[$kunci] ?? []) ?></textarea>
            <p class="petunjuk"><?= lang('Sunting.satuPerBaris') ?></p>
            <?php if ($salah((string) $kunci)): ?><p class="galat-kolom"><?= lang('Sunting.galatKolom') ?></p><?php endif ?>
        <?php else: ?>
            <label for="k-<?= esc($kunci) ?>"><?= lang('Sunting.kolom.' . $kunci) ?></label>
            <input type="text" id="k-<?= esc($kunci) ?>" name="t[<?= esc($kunci) ?>]" value="<?= esc($nilai[$kunci] ?? '') ?>" maxlength="400" required <?= $atr((string) $kunci) ?>>
            <?php if ($salah((string) $kunci)): ?><p class="galat-kolom"><?= lang('Sunting.galatKolom') ?></p><?php endif ?>
        <?php endif ?>
    <?php endforeach ?>

    <div class="baris-tombol">
        <button type="submit" class="tombol tombol-sekunder" name="aksi" value="pratinjau"><?= lang('Sunting.tombolPratinjau') ?></button>
        <button type="submit" class="tombol" name="aksi" value="simpan"><?= lang('Sunting.tombolSimpan') ?></button>
        <a href="/admin/teks"><?= lang('Sunting.batal') ?></a>
    </div>
    <p class="petunjuk"><?= lang('Sunting.langsungBerlaku') ?></p>
</form>

<?php if ($diubah): ?>
    <form method="post" action="<?= esc($aksi) ?>/bawaan" class="kartu">
        <?= csrf_field() ?>
        <p><?= lang('Sunting.kembalikanPenjelasan') ?></p>
        <button type="submit" class="tombol tombol-bahaya"><?= lang('Sunting.kembalikan') ?></button>
    </form>
<?php endif ?>
<?= $this->endSection() ?>
