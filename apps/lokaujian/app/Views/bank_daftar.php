<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('kepala') ?><?= view('_kepala_bank') ?><?= $this->endSection() ?>
<?= $this->section('isi') ?>
<?php
/**
 * Bank soal satu mata kuliah (SPEC-LOKAUJIAN.md §5.4). Variabel: $matkul, $butir
 * (Bank::daftar), $dipakai (Bank::pemakaian), $saring (tipe, q, arsip).
 */
use App\Controllers\Soal;
use App\Libraries\Aturan;
use App\Libraries\Markdown;

$dasar = '/mata-kuliah/' . (int) $matkul['id'];
?>
<?= view('_nav', ['akses' => $akses]) ?>
<p><a href="<?= $dasar ?>"><span class="panah-balik" aria-hidden="true"></span> <span class="ltr"><?= esc($matkul['code']) ?></span> — <?= esc($matkul['name']) ?></a></p>
<h1><?= lang('Bank.judul') ?>: <span class="ltr"><?= esc($matkul['code']) ?></span></h1>
<p class="redup"><?= lang('Bank.penjelasan') ?></p>

<div class="kartu">
    <p><strong><?= lang('Bank.tambah') ?></strong></p>
    <div class="baris-tombol">
        <?php foreach (Aturan::TIPE as $t): ?>
            <a class="tombol tombol-sekunder" href="<?= $dasar ?>/bank/soal/baru?tipe=<?= $t ?>"><?= lang('Bank.tipe.' . $t) ?></a>
        <?php endforeach ?>
        <a class="tombol tombol-sekunder" href="<?= $dasar ?>/bank/kelompok/baru"><?= lang('Bank.kelompok.satu') ?></a>
        <a href="<?= $dasar ?>/ujian"><?= lang('Bank.ujian.keDaftar') ?></a>
    </div>
</div>

<form method="get" action="<?= $dasar ?>/bank" class="kartu">
    <div class="dua-kolom">
        <div>
            <label for="saring-q"><?= lang('Bank.saring.cari') ?></label>
            <input type="search" id="saring-q" name="q" maxlength="100" value="<?= esc($saring['q']) ?>" dir="auto">
        </div>
        <div>
            <label for="saring-tipe"><?= lang('Bank.saring.tipe') ?></label>
            <select id="saring-tipe" name="tipe">
                <option value=""><?= lang('Bank.saring.semua') ?></option>
                <?php foreach ([...Aturan::TIPE, 'kelompok'] as $t): ?>
                    <option value="<?= $t ?>"<?= $saring['tipe'] === $t ? ' selected' : '' ?>><?= lang($t === 'kelompok' ? 'Bank.kelompok.satu' : 'Bank.tipe.' . $t) ?></option>
                <?php endforeach ?>
            </select>
        </div>
    </div>
    <label class="sebaris"><input type="checkbox" name="arsip" value="1"<?= $saring['arsip'] ? ' checked' : '' ?>> <?= lang('Bank.saring.arsip') ?></label>
    <div class="baris-tombol"><button type="submit" class="tombol tombol-sekunder"><?= lang('Bank.saring.terapkan') ?></button></div>
</form>

<?php if ($butir === []): ?>
    <div class="kartu"><p><?= lang($saring['q'] !== '' || $saring['tipe'] !== '' || $saring['arsip'] ? 'Bank.kosongSaring' : 'Bank.kosong') ?></p></div>
<?php else: ?>
    <form method="post" action="<?= $dasar ?>/ujian">
        <?= csrf_field() ?>
        <div class="tabel-wadah kartu">
            <table data-bank>
                <thead>
                <tr>
                    <?php if (! $saring['arsip']): ?><th scope="col" class="sel-centang"><span title="<?= lang('Bank.kolom.pilih') ?>">✓</span></th><?php endif ?>
                    <th scope="col"><?= lang('Bank.kolom.tipe') ?></th>
                    <th scope="col"><?= lang('Bank.kolom.poin') ?></th>
                    <th scope="col"><?= lang('Bank.kolom.isi') ?></th>
                    <th scope="col"><?= lang('Bank.kolom.dipakai') ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($butir as $b): ?>
                    <?php
                    $kelompok = $b['jenis'] === 'kelompok';
                    $x        = $kelompok ? $b['kelompok'] : $b['soal'];
                    $kode     = ($kelompok ? 'k' : 's') . (int) $x['id'];
                    $poin     = $kelompok ? number_format(array_sum(array_map('floatval', array_column($x['questions'], 'points'))), 2, '.', '') : $x['points'];
                    $ringkas  = Markdown::ringkas($x['text']);
                    if ($ringkas === '') {
                        $ringkas = $x['image'] !== null ? '[' . $x['image']['alt'] . ']' : '—';
                    }
                    ?>
                    <tr data-butir="<?= $kode ?>">
                        <?php if (! $saring['arsip']): ?>
                            <td><input type="checkbox" name="butir[]" value="<?= $kode ?>" aria-label="<?= lang('Bank.kolom.pilih') ?>: <?= esc($ringkas) ?>"<?= $kelompok && $x['questions'] === [] ? ' disabled' : '' ?>></td>
                        <?php endif ?>
                        <td>
                            <?php if ($kelompok): ?>
                                <span class="lencana"><?= lang('Bank.kelompok.satu') ?></span> <span class="redup"><?= lang('Bank.kelompok.jumlahSoal', [count($x['questions'])]) ?></span>
                            <?php else: ?>
                                <span class="lencana"><?= lang('Bank.tipe.' . $x['type']) ?></span>
                            <?php endif ?>
                        </td>
                        <td class="ltr"><?= esc(Soal::poinTampil($poin)) ?></td>
                        <td dir="auto"><a href="<?= $dasar ?>/bank/<?= $kelompok ? 'kelompok' : 'soal' ?>/<?= (int) $x['id'] ?>"><?= esc($ringkas) ?></a></td>
                        <td>
                            <?php foreach ($dipakai[$kode] ?? [] as $u): ?>
                                <a href="<?= $dasar ?>/ujian/<?= (int) $u['id'] ?>" dir="auto"><?= esc($u['title']) ?></a><br>
                            <?php endforeach ?>
                            <?php if (($dipakai[$kode] ?? []) === []): ?><span class="redup">—</span><?php endif ?>
                        </td>
                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>
        </div>
        <?php if (! $saring['arsip']): ?>
            <div class="kartu">
                <p><strong><?= lang('Bank.ujian.buatJudul') ?></strong></p>
                <p class="petunjuk"><?= lang('Bank.ujian.buatInfo') ?></p>
                <div class="dua-kolom">
                    <div>
                        <label for="draf-judul"><?= lang('Bank.ujian.judulIsian') ?></label>
                        <input type="text" id="draf-judul" name="judul" maxlength="<?= Aturan::JUDUL_MAKS ?>" required dir="auto">
                    </div>
                    <div>
                        <label for="draf-jenis"><?= lang('Bank.ujian.jenis') ?></label>
                        <select id="draf-jenis" name="jenis">
                            <option value="ujian"><?= lang('Bank.ujian.jenisUjian') ?></option>
                            <option value="kuis"><?= lang('Bank.ujian.jenisKuis') ?></option>
                        </select>
                    </div>
                </div>
                <div class="baris-tombol"><button type="submit" class="tombol"><?= lang('Bank.ujian.buat') ?></button></div>
            </div>
        <?php endif ?>
    </form>
<?php endif ?>
<?= $this->endSection() ?>
