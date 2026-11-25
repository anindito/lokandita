<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('kepala') ?><?= view('_kepala_bank') ?><?= $this->endSection() ?>
<?= $this->section('isi') ?>
<?php
/**
 * Satu kelompok soal: pratinjau seperti dilihat mahasiswa, lalu soal-soalnya dengan kunci dan
 * tindakan untuk staf. Variabel: $matkul, $kelompok, $dipakai.
 */
use App\Libraries\Aturan;
use App\Libraries\Markdown;

$dasar  = '/mata-kuliah/' . (int) $matkul['id'] . '/bank';
$ini    = $dasar . '/kelompok/' . (int) $kelompok['id'];
$jumlah = count($kelompok['questions']);
?>
<?= view('_nav', ['akses' => $akses]) ?>
<p><a href="<?= $dasar ?>"><span class="panah-balik" aria-hidden="true"></span> <?= lang('Bank.kembali') ?></a></p>
<h1><?= lang('Bank.kelompok.satu') ?> <span class="pratinjau-pita"><?= lang('Bank.pratinjau') ?></span>
    <?php if ($kelompok['archived_at'] !== null): ?><span class="lencana lencana-galat"><?= lang('Bank.diarsipkan') ?></span><?php endif ?></h1>
<p class="redup"><?= lang('Bank.pratinjauInfo') ?></p>

<?= view('_kelompok', ['kelompok' => $kelompok, 'nomorAwal' => 1]) ?>
<?php if ($jumlah === 0): ?><div class="kartu"><p><?= lang('Bank.kelompok.kosong') ?></p></div><?php endif ?>

<div class="kartu">
    <?= view('_dipakai', ['dipakai' => $dipakai, 'matkul' => $matkul]) ?>
    <div class="baris-tombol">
        <a class="tombol" href="<?= $ini ?>/sunting"><?= lang('Bank.kelompok.suntingBersama') ?></a>
        <form method="post" action="<?= $ini ?>/arsip" class="inline">
            <?= csrf_field() ?>
            <input type="hidden" name="arsip" value="<?= $kelompok['archived_at'] === null ? '1' : '0' ?>">
            <button type="submit" class="tombol tombol-sekunder"><?= lang($kelompok['archived_at'] === null ? 'Bank.arsipkan' : 'Bank.pulihkan') ?></button>
        </form>
        <?php if ($dipakai === []): ?>
            <form method="post" action="<?= $ini ?>/hapus" class="inline">
                <?= csrf_field() ?>
                <button type="submit" class="tombol tombol-sekunder"><?= lang('Bank.kelompok.hapus') ?></button>
            </form>
        <?php endif ?>
    </div>
    <?php if ($dipakai !== []): ?><p class="petunjuk"><?= lang('Bank.hapusInfo') ?></p><?php endif ?>
</div>

<h2><?= lang('Bank.kelompok.soalnya') ?></h2>
<?php if ($jumlah > 0): ?>
    <div class="tabel-wadah kartu">
        <table data-soal-kelompok>
            <thead><tr>
                <th scope="col">#</th>
                <th scope="col"><?= lang('Bank.kolom.tipe') ?></th>
                <th scope="col"><?= lang('Bank.kolom.isi') ?></th>
                <th scope="col"><?= lang('Bank.kunci.judul') ?></th>
                <th scope="col"><?= lang('Bank.kolom.tindakan') ?></th>
            </tr></thead>
            <tbody>
            <?php foreach ($kelompok['questions'] as $i => $s): ?>
                <tr data-soal-baris="<?= (int) $s['id'] ?>">
                    <td class="ltr"><?= $i + 1 ?></td>
                    <td><span class="lencana"><?= lang('Bank.tipe.' . $s['type']) ?></span></td>
                    <td dir="auto"><?= esc(Markdown::ringkas($s['text'])) ?></td>
                    <td><?= view('_kunci', ['soal' => $s]) ?></td>
                    <td>
                        <a href="<?= $dasar ?>/soal/<?= (int) $s['id'] ?>/sunting"><?= lang('Bank.sunting') ?></a>
                        <?php foreach (['naik' => $i > 0, 'turun' => $i < $jumlah - 1] as $arah => $bisa): ?>
                            <?php if ($bisa): ?>
                                <form method="post" action="<?= $dasar ?>/soal/<?= (int) $s['id'] ?>/geser" class="inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="arah" value="<?= $arah ?>">
                                    <button type="submit" class="tombol tombol-sekunder"><?= lang('Bank.kelompok.' . $arah) ?></button>
                                </form>
                            <?php endif ?>
                        <?php endforeach ?>
                        <?php if ($dipakai === []): ?>
                            <form method="post" action="<?= $dasar ?>/soal/<?= (int) $s['id'] ?>/hapus" class="inline">
                                <?= csrf_field() ?>
                                <button type="submit" class="tombol tombol-sekunder"><?= lang('Bank.hapus') ?></button>
                            </form>
                        <?php endif ?>
                    </td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
<?php endif ?>

<?php if ($jumlah < Aturan::SOAL_KELOMPOK_MAKS): ?>
    <div class="kartu">
        <p><strong><?= lang('Bank.kelompok.tambahSoal') ?></strong></p>
        <div class="baris-tombol">
            <?php foreach (Aturan::TIPE as $t): ?>
                <a class="tombol tombol-sekunder" href="<?= $dasar ?>/soal/baru?tipe=<?= $t ?>&amp;kelompok=<?= (int) $kelompok['id'] ?>"><?= lang('Bank.tipe.' . $t) ?></a>
            <?php endforeach ?>
        </div>
    </div>
<?php else: ?>
    <p class="petunjuk"><?= lang('Bank.galat.kelompokPenuh', [Aturan::SOAL_KELOMPOK_MAKS]) ?></p>
<?php endif ?>
<?= $this->endSection() ?>
