<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('kepala') ?><?= view('_kepala_bank') ?><?= $this->endSection() ?>
<?= $this->section('isi') ?>
<?php
/**
 * Ujian dan kuis satu mata kuliah, dan impor berkas JSON (SPEC-LOKAUJIAN.md §6.3).
 * Variabel: $matkul, $ujian (Ujian::daftar), $galatImpor (jalur, baris, pesan), $berkas.
 */
use App\Controllers\Soal;
use Lokandita\Auth\Waktu;

$dasar = '/mata-kuliah/' . (int) $matkul['id'];
$angka = static fn (float $n): string => Soal::poinTampil(number_format($n, 2, '.', ''));
?>
<?= view('_nav', ['akses' => $akses]) ?>
<p><a href="<?= $dasar ?>"><span class="panah-balik" aria-hidden="true"></span> <span class="ltr"><?= esc($matkul['code']) ?></span> — <?= esc($matkul['name']) ?></a></p>
<h1><?= lang('Bank.ujian.judul') ?>: <span class="ltr"><?= esc($matkul['code']) ?></span></h1>
<p class="redup"><?= lang('Bank.ujian.penjelasan') ?> <a href="<?= $dasar ?>/bank"><?= lang('Bank.ujian.keBank') ?></a></p>

<p><a href="<?= $dasar ?>/ujian<?= $arsip ? '' : '?arsip=1' ?>" data-arsip-taut><?= lang($arsip ? 'Sesi.ujian.lihatAktif' : 'Sesi.ujian.lihatArsip') ?></a></p>
<?php if ($galatImpor !== []): ?>
    <div class="kartu" role="alert" data-galat-impor>
        <p><strong><?= lang('Bank.impor.ditolak', [esc($berkas)]) ?></strong></p>
        <ul class="daftar-galat">
            <?php foreach ($galatImpor as $g): ?>
                <li><?php if ($g['jalur'] !== ''): ?><code class="ltr"><?= esc($g['jalur']) ?></code> (<?= lang('Bank.impor.baris', [(int) $g['baris']]) ?>): <?php endif ?><?= esc($g['pesan']) ?></li>
            <?php endforeach ?>
        </ul>
    </div>
<?php endif ?>

<?php if ($ujian === []): ?>
    <div class="kartu"><p><?= lang('Bank.ujian.kosong') ?></p></div>
<?php else: ?>
    <div class="tabel-wadah kartu">
        <table data-ujian>
            <thead><tr>
                <th scope="col"><?= lang('Bank.ujian.judulIsian') ?></th>
                <th scope="col"><?= lang('Bank.ujian.jenis') ?></th>
                <th scope="col"><?= lang('Bank.ujian.status') ?></th>
                <th scope="col"><?= lang('Bank.ujian.soal') ?></th>
                <th scope="col"><?= lang('Bank.ujian.poin') ?></th>
                <th scope="col"><?= lang('Bank.ujian.dibuat') ?></th>
            </tr></thead>
            <tbody>
            <?php foreach ($ujian as $u): ?>
                <tr>
                    <td dir="auto"><a href="<?= $dasar ?>/ujian/<?= (int) $u['id'] ?>"><?= esc($u['title']) ?></a></td>
                    <td><?= lang($u['kind'] === 'kuis' ? 'Bank.ujian.jenisKuis' : 'Bank.ujian.jenisUjian') ?></td>
                    <td><span class="lencana"><?= lang('Bank.ujian.status_' . $u['status']) ?></span></td>
                    <td class="ltr"><?= (int) $u['ringkasan']['soal'] ?></td>
                    <td><?= lang('Bank.ujian.poinRinci', [$angka($u['ringkasan']['poin']), $angka($u['ringkasan']['otomatis']), $angka($u['ringkasan']['manual'])]) ?></td>
                    <td class="ltr"><?= esc(Waktu::lokal($u['created_at'])) ?></td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
<?php endif ?>

<form method="post" enctype="multipart/form-data" action="<?= $dasar ?>/ujian/impor" class="kartu">
    <?= csrf_field() ?>
    <p><strong><?= lang('Bank.impor.judul') ?></strong></p>
    <p class="petunjuk"><?= lang('Bank.impor.info', [esc($matkul['code'])]) ?></p>
    <label for="impor-berkas"><?= lang('Bank.impor.berkas') ?></label>
    <input type="file" id="impor-berkas" name="berkas" accept=".json,application/json" required>
    <div class="baris-tombol"><button type="submit" class="tombol"><?= lang('Bank.impor.kirim') ?></button></div>
</form>
<?= $this->endSection() ?>
