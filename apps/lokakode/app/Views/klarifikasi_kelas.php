<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<?php
/**
 * Klarifikasi soal satu kelas untuk dosen (SPEC.md §14). Variabel: $kelas, $daftar
 * (Klarifikasi::kelas), $pilih (id pertemuan atau null), $pertemuan, $orang, $bolehJawab.
 */
use App\Libraries\Klarifikasi;
use App\Libraries\Ruang;
use App\Libraries\Waktu;

$alamat = '/kelas/' . (int) $kelas['id'] . '/klarifikasi';
?>
<?= view('_nav', ['akses' => $akses]) ?>
<p><a href="/kelas/<?= (int) $kelas['id'] ?>"><span class="panah-balik" aria-hidden="true"></span> <span class="ltr"><?= esc($kelas['matkul_kode']) ?></span> <?= esc($kelas['name']) ?></a></p>
<h1><?= lang('Komunikasi.klarifikasi.judul') ?>: <span class="ltr"><?= esc($kelas['matkul_kode']) ?></span> <?= esc($kelas['name']) ?></h1>
<p class="redup"><?= lang('Komunikasi.klarifikasi.penjelasanDosen') ?></p>

<form class="saring" method="get" action="<?= $alamat ?>">
    <div>
        <label for="pertemuan"><?= lang('Integritas.pertemuan') ?></label>
        <select id="pertemuan" name="pertemuan">
            <option value=""><?= lang('Integritas.semua') ?></option>
            <?php foreach ($pertemuan as $p): ?>
                <option value="<?= (int) $p['id'] ?>" <?= $pilih === (int) $p['id'] ? 'selected' : '' ?>><?= (int) $p['number'] ?> — <?= esc($p['title']) ?></option>
            <?php endforeach ?>
        </select>
    </div>
    <div><button type="submit" class="tombol"><?= lang('Kode.tahun.lihat') ?></button></div>
</form>

<?php if ($daftar === []): ?>
    <div class="kartu"><p><?= lang('Komunikasi.klarifikasi.kosongDosen') ?></p></div>
<?php else: ?>
    <ul class="daftar-kabar" data-klarifikasi>
    <?php foreach ($daftar as $q): ?>
        <?php
        $id      = (int) $q['id'];
        $dijawab = $q['answer'] !== null;
        $soal    = Ruang::terjemahan(json_decode((string) $q['translations'], true) ?? [])['title'] ?? $q['slug'];
        ?>
        <li class="kartu<?= $dijawab ? '' : ' kabar-baru' ?>" id="k<?= $id ?>" data-dijawab="<?= $dijawab ? '1' : '0' ?>">
            <p>
                <strong><?= esc($orang[$q['user_id']]['nama'] ?? lang('Kode.mhs.akunHilang')) ?></strong>
                <small class="ltr"><?= esc($orang[$q['user_id']]['nomor_induk'] ?? '') ?></small>
                · <?= lang('Soal.pertemuan.ke', [(int) $q['number']]) ?>: <?= esc($soal) ?>
                · <span class="ltr"><?= esc(Waktu::lokal($q['created_at'])) ?></span>
                <?php if (! $dijawab): ?><span class="lencana lencana-galat"><?= lang('Komunikasi.klarifikasi.belum') ?></span>
                <?php elseif ($q['visibility'] === 'class'): ?><span class="lencana"><?= lang('Komunikasi.klarifikasi.keKelas') ?></span>
                <?php else: ?><span class="lencana"><?= lang('Komunikasi.klarifikasi.pribadi') ?></span><?php endif ?>
            </p>
            <p class="kabar-isi" data-pertanyaan><?= esc($q['question']) ?></p>
            <p><a href="/kelas/<?= (int) $kelas['id'] ?>/pertemuan/<?= (int) $q['pertemuan_id'] ?>/kiriman?mahasiswa=<?= esc($q['user_id']) ?>&amp;soal=<?= (int) $q['meeting_problem_id'] ?>"><?= lang('Komunikasi.klarifikasi.kirimanPenanya') ?></a></p>
            <?php if ($dijawab): ?>
                <p class="petunjuk"><?= lang('Komunikasi.klarifikasi.dijawabOleh', [esc($orang[$q['answered_by']]['nama'] ?? '—'), esc(Waktu::lokal($q['answered_at']))]) ?></p>
            <?php endif ?>
            <?php if ($bolehJawab): ?>
                <form method="post" action="<?= $alamat ?>/<?= $id ?>/jawab">
                    <?= csrf_field() ?>
                    <label for="jawaban-<?= $id ?>"><?= lang($dijawab ? 'Komunikasi.klarifikasi.ubahJawaban' : 'Komunikasi.klarifikasi.jawaban') ?></label>
                    <textarea id="jawaban-<?= $id ?>" name="jawaban" rows="3" maxlength="<?= Klarifikasi::PANJANG_MAKS ?>" required><?= esc((string) $q['answer']) ?></textarea>
                    <fieldset>
                        <legend><?= lang('Komunikasi.klarifikasi.siapaMelihat') ?></legend>
                        <label class="centang"><input type="radio" name="tampil" value="pribadi" <?= $q['visibility'] === 'class' ? '' : 'checked' ?>> <?= lang('Komunikasi.klarifikasi.pilihPribadi') ?></label>
                        <label class="centang"><input type="radio" name="tampil" value="kelas" <?= $q['visibility'] === 'class' ? 'checked' : '' ?>> <?= lang('Komunikasi.klarifikasi.pilihKelas') ?></label>
                    </fieldset>
                    <div class="baris-tombol"><button type="submit" class="tombol"><?= lang('Komunikasi.klarifikasi.simpan') ?></button></div>
                </form>
            <?php elseif ($dijawab): ?>
                <p class="kabar-isi" data-jawaban><?= esc($q['answer']) ?></p>
            <?php endif ?>
        </li>
    <?php endforeach ?>
    </ul>
    <p class="petunjuk"><?= lang('Komunikasi.klarifikasi.catatanDosen') ?></p>
<?php endif ?>
<?= $this->endSection() ?>
