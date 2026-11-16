<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<?php use App\Libraries\Kurikulum; ?>
<?= view('_nav', ['akses' => $akses]) ?>
<h1><?= lang('Soal.pertemuan.judul') ?> — <span class="ltr"><?= esc($matkul['code']) ?></span></h1>
<p class="redup"><a href="/mata-kuliah/<?= (int) $matkul['id'] ?>"><?= esc($matkul['name']) ?></a></p>

<?php if ($pertemuan === []): ?>
    <div class="kartu"><p><?= lang('Soal.pertemuan.kosong') ?></p></div>
<?php else: ?>
    <div class="tabel-wadah kartu">
        <table>
            <thead>
            <tr>
                <th scope="col"><?= lang('Soal.pertemuan.nomor') ?></th>
                <th scope="col"><?= lang('Soal.pertemuan.judulKolom') ?></th>
                <th scope="col"><?= lang('Soal.pertemuan.bahasa') ?></th>
                <th scope="col"><?= lang('Soal.soal.jumlah') ?></th>
                <th scope="col"><?= lang('Soal.soal.disetujui') ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($pertemuan as $p): ?>
                <tr>
                    <td><?= (int) $p['number'] ?></td>
                    <td>
                        <a href="/pertemuan/<?= (int) $p['id'] ?>"><?= esc($p['title']) ?></a>
                        <?php if ((int) $p['enabled'] !== 1): ?><span class="lencana"><?= lang('Soal.pertemuan.nonaktif') ?></span><?php endif ?>
                    </td>
                    <td><?= lang('Soal.bahasa.' . $p['language']) ?></td>
                    <td><?= (int) $p['jumlah_soal'] ?></td>
                    <td><?= (int) $p['jumlah_disetujui'] ?></td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
<?php endif ?>

<h2><?= lang('Soal.pertemuan.tambah') ?></h2>
<div class="kartu">
    <form method="post" action="/mata-kuliah/<?= (int) $matkul['id'] ?>/pertemuan">
        <?= csrf_field() ?>
        <label for="number"><?= lang('Soal.pertemuan.nomorIsi') ?></label>
        <input type="number" id="number" name="number" class="ltr" min="1" max="16" value="<?= esc($isi['number']) ?>" required>
        <?= view('_galat_soal', ['galat' => $galat, 'kolom' => 'number']) ?>

        <label for="title"><?= lang('Soal.pertemuan.judulKolom') ?></label>
        <input type="text" id="title" name="title" maxlength="190" value="<?= esc($isi['title']) ?>" required>
        <?= view('_galat_soal', ['galat' => $galat, 'kolom' => 'title']) ?>

        <label for="language"><?= lang('Soal.pertemuan.bahasa') ?></label>
        <select id="language" name="language" required>
            <?php foreach (Kurikulum::BAHASA as $b): ?>
                <option value="<?= $b ?>" <?= $isi['language'] === $b ? 'selected' : '' ?>><?= lang('Soal.bahasa.' . $b) ?></option>
            <?php endforeach ?>
        </select>
        <p class="petunjuk"><?= lang('Soal.pertemuan.bahasaInfo') ?></p>
        <?= view('_galat_soal', ['galat' => $galat, 'kolom' => 'language']) ?>

        <div class="baris-tombol">
            <button type="submit" class="tombol"><?= lang('Soal.pertemuan.tambah') ?></button>
        </div>
    </form>
</div>

<h2><?= lang('Soal.konsep.dasarJudul') ?></h2>
<div class="kartu">
    <p class="petunjuk"><?= lang('Soal.konsep.dasarInfo') ?></p>
    <form method="post" action="/mata-kuliah/<?= (int) $matkul['id'] ?>/konsep-dasar">
        <?= csrf_field() ?>
        <?= view('_konsep_kotak', ['katalog' => $katalog, 'terkunci' => [], 'dipilih' => $dasar]) ?>
        <div class="baris-tombol">
            <button type="submit" class="tombol tombol-sekunder"><?= lang('Soal.konsep.simpanDasar') ?></button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
