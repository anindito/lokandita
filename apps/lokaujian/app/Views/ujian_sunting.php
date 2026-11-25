<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('kepala') ?><?= view('_kepala_bank') ?><?= $this->endSection() ?>
<?= $this->section('isi') ?>
<?php
/**
 * Setelan dan susunan butir sebuah DRAF (SPEC-LOKAUJIAN.md §6). Variabel: $matkul, $ujian
 * (Ujian::lihat), $isian (setelan yang diketik), $galat, $bank (Bank::daftar), $dipakai
 * ("s<id>"/"k<id>" yang sudah ada di ujian ini).
 */
use App\Libraries\Markdown;

$dasar = '/mata-kuliah/' . (int) $matkul['id'];
$ini   = $dasar . '/ujian/' . (int) $ujian['id'];
$nomor = 1;
?>
<?= view('_nav', ['akses' => $akses]) ?>
<p><a href="<?= $ini ?>"><span class="panah-balik" aria-hidden="true"></span> <?= lang('Sesi.ujian.kembaliUjian') ?></a></p>
<h1><?= lang('Sesi.ujian.sunting') ?>: <span dir="auto"><?= esc($ujian['title']) ?></span></h1>
<?= view('_galat_form', ['galat' => $galat]) ?>

<form method="post" action="<?= $ini ?>" class="kartu" data-setelan>
    <?= csrf_field() ?>
    <p><strong><?= lang('Sesi.ujian.setelan') ?></strong></p>
    <label for="u-judul"><?= lang('Bank.ujian.judulIsian') ?></label>
    <input type="text" id="u-judul" name="judul" maxlength="190" required dir="auto" value="<?= esc($isian['judul'], 'attr') ?>">
    <label for="u-jenis"><?= lang('Bank.ujian.jenis') ?></label>
    <select id="u-jenis" name="jenis">
        <option value="ujian"<?= $isian['jenis'] === 'ujian' ? ' selected' : '' ?>><?= lang('Bank.ujian.jenisUjian') ?></option>
        <option value="kuis"<?= $isian['jenis'] === 'kuis' ? ' selected' : '' ?>><?= lang('Bank.ujian.jenisKuis') ?></option>
    </select>
    <label for="u-petunjuk"><?= lang('Bank.ujian.petunjuk') ?></label>
    <textarea id="u-petunjuk" name="petunjuk" rows="5" maxlength="5000" dir="auto"><?= esc($isian['petunjuk']) ?></textarea>
    <p class="petunjuk"><?= lang('Sesi.ujian.petunjukInfo') ?></p>
    <label for="u-mode"><?= lang('Bank.ujian.mode') ?></label>
    <select id="u-mode" name="mode">
        <option value="dikendalikan_dosen"<?= $isian['mode'] === 'dikendalikan_dosen' ? ' selected' : '' ?>><?= lang('Bank.ujian.mode_dikendalikan_dosen') ?></option>
        <option value="per_mahasiswa"<?= $isian['mode'] === 'per_mahasiswa' ? ' selected' : '' ?>><?= lang('Bank.ujian.mode_per_mahasiswa') ?></option>
    </select>
    <p class="petunjuk"><?= lang('Sesi.ujian.modeInfo') ?></p>
    <label for="u-durasi"><?= lang('Sesi.ujian.durasi') ?></label>
    <input type="number" id="u-durasi" name="durasi" min="5" max="600" step="1" class="ltr" value="<?= esc($isian['durasi'], 'attr') ?>">
    <p class="petunjuk"><?= lang('Sesi.ujian.durasiInfo') ?></p>
    <label class="sebaris"><input type="checkbox" name="acak_soal" value="1"<?= $isian['acak_soal'] ? ' checked' : '' ?>> <span><?= lang('Sesi.ujian.acakSoal') ?></span></label>
    <label class="sebaris"><input type="checkbox" name="acak_opsi" value="1"<?= $isian['acak_opsi'] ? ' checked' : '' ?>> <span><?= lang('Sesi.ujian.acakOpsi') ?></span></label>
    <div class="baris-tombol"><button type="submit" class="tombol"><?= lang('Sesi.ujian.simpan') ?></button></div>
</form>

<h2><?= lang('Sesi.ujian.susunan') ?></h2>
<p class="redup"><?= lang('Sesi.ujian.susunanInfo') ?></p>
<div class="tabel-wadah kartu">
    <table data-butir>
        <thead><tr>
            <th scope="col"><?= lang('Sesi.ujian.nomor') ?></th>
            <th scope="col"><?= lang('Bank.kolom.tipe') ?></th>
            <th scope="col"><?= lang('Bank.kolom.isi') ?></th>
            <th scope="col"><?= lang('Bank.kolom.tindakan') ?></th>
        </tr></thead>
        <tbody>
        <?php foreach ($ujian['butir'] as $i => $b): ?>
            <?php
            $kelompok = $b['jenis'] === 'kelompok';
            $x        = $kelompok ? $b['kelompok'] : $b['soal'];
            $jumlah   = $kelompok ? count($x['questions']) : 1;
            $posisi   = $i + 1;
            ?>
            <tr>
                <td class="ltr"><?= $jumlah === 1 ? $nomor : $nomor . '–' . ($nomor + $jumlah - 1) ?></td>
                <td><?= $kelompok ? lang('Bank.kelompok.satu') . ' (' . $jumlah . ')' : lang('Bank.tipe.' . $x['type']) ?></td>
                <td dir="auto"><?= esc(Markdown::ringkas($x['text'] !== '' ? $x['text'] : ($x['image']['alt'] ?? ''))) ?></td>
                <td>
                    <div class="baris-tombol">
                        <?php foreach (['naik', 'turun'] as $arah): ?>
                            <form method="post" action="<?= $ini ?>/butir/<?= $posisi ?>/geser" class="inline">
                                <?= csrf_field() ?>
                                <input type="hidden" name="arah" value="<?= $arah ?>">
                                <button type="submit" class="tombol tombol-sekunder" aria-label="<?= lang('Sesi.ujian.' . $arah) ?>: <?= $nomor ?>"<?= ($arah === 'naik' && $i === 0) || ($arah === 'turun' && $i === count($ujian['butir']) - 1) ? ' disabled' : '' ?>><?= $arah === 'naik' ? '↑' : '↓' ?></button>
                            </form>
                        <?php endforeach ?>
                        <form method="post" action="<?= $ini ?>/butir/<?= $posisi ?>/hapus" class="inline">
                            <?= csrf_field() ?>
                            <button type="submit" class="tombol tombol-sekunder"><?= lang('Sesi.ujian.buang') ?></button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php $nomor += $jumlah; ?>
        <?php endforeach ?>
        </tbody>
    </table>
</div>

<?php
$tambah = array_values(array_filter($bank, static function (array $b) use ($dipakai): bool {
    $kode = $b['jenis'] === 'soal' ? 's' . $b['soal']['id'] : 'k' . $b['kelompok']['id'];

    return ! isset($dipakai[$kode]) && ($b['jenis'] === 'soal' || $b['kelompok']['questions'] !== []);
}));
?>
<h2><?= lang('Sesi.ujian.tambah') ?></h2>
<?php if ($tambah === []): ?>
    <div class="kartu"><p><?= lang('Sesi.ujian.tambahKosong') ?> <a href="<?= $dasar ?>/bank"><?= lang('Bank.ujian.keBank') ?></a></p></div>
<?php else: ?>
    <form method="post" action="<?= $ini ?>/butir" class="kartu" data-tambah-butir>
        <?= csrf_field() ?>
        <div class="tabel-wadah">
            <table>
                <tbody>
                <?php foreach ($tambah as $b): ?>
                    <?php
                    $kelompok = $b['jenis'] === 'kelompok';
                    $x        = $kelompok ? $b['kelompok'] : $b['soal'];
                    $kode     = ($kelompok ? 'k' : 's') . (int) $x['id'];
                    $ringkas  = Markdown::ringkas($x['text'] !== '' ? $x['text'] : ($x['image']['alt'] ?? ''));
                    ?>
                    <tr>
                        <td class="sel-centang"><input type="checkbox" name="butir[]" value="<?= $kode ?>" aria-label="<?= lang('Bank.kolom.pilih') ?>: <?= esc($ringkas) ?>"></td>
                        <td><?= $kelompok ? lang('Bank.kelompok.satu') . ' (' . count($x['questions']) . ')' : lang('Bank.tipe.' . $x['type']) ?></td>
                        <td dir="auto"><?= esc($ringkas) ?></td>
                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>
        </div>
        <div class="baris-tombol"><button type="submit" class="tombol"><?= lang('Sesi.ujian.tambahKirim') ?></button></div>
    </form>
<?php endif ?>
<?= $this->endSection() ?>
