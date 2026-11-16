<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<?php use App\Libraries\Kurikulum; ?>
<?= view('_nav', ['akses' => $akses]) ?>
<h1><span class="ltr"><?= esc($p['matkul_kode']) ?></span> — <?= lang('Soal.pertemuan.ke', [(int) $p['number']]) ?>: <?= esc($p['title']) ?></h1>
<p class="redup">
    <a href="/mata-kuliah/<?= (int) $p['course_id'] ?>/pertemuan"><?= lang('Soal.pertemuan.semua') ?></a>
    · <?= lang('Soal.bahasa.' . $p['language']) ?>
    <?php if ((int) $p['enabled'] !== 1): ?> · <span class="lencana"><?= lang('Soal.pertemuan.nonaktif') ?></span><?php endif ?>
</p>

<h2><?= lang('Soal.soal.bank') ?></h2>
<?php if ($soal === []): ?>
    <div class="kartu"><p><?= $akses->superadmin() ? lang('Soal.soal.kosongAdmin') : lang('Soal.soal.kosong') ?></p></div>
<?php else: ?>
    <div class="tabel-wadah kartu">
        <table>
            <thead>
            <tr>
                <th scope="col"><?= lang('Soal.soal.judul') ?></th>
                <th scope="col"><?= lang('Soal.soal.tingkat') ?></th>
                <th scope="col"><?= lang('Soal.soal.versi') ?></th>
                <th scope="col"><?= lang('Soal.soal.status') ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($soal as $s): ?>
                <?php $tr = json_decode((string) $s['translations'], true); ?>
                <tr>
                    <td>
                        <a href="/soal/<?= (int) $s['id'] ?>"><?= esc($tr['id']['title'] ?? $s['slug']) ?></a>
                        <span class="redup ltr"><?= esc($s['slug']) ?></span>
                    </td>
                    <td><span class="lencana tingkat-<?= esc(strtolower($s['difficulty'])) ?>"><?= lang('Soal.tingkat.' . $s['difficulty']) ?></span></td>
                    <td><?= (int) $s['version'] ?></td>
                    <td>
                        <span class="lencana status-<?= esc(strtolower($s['status'])) ?>"><?= lang('Soal.status.' . $s['status']) ?></span>
                        <?php if ($s['versi_dipakai'] !== null && (int) $s['versi_dipakai'] !== (int) $s['versi_id']): ?>
                            <span class="redup"><?= lang('Soal.soal.versiLamaDipakai') ?></span>
                        <?php endif ?>
                    </td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
<?php endif ?>
<?php if ($akses->superadmin()): ?>
    <p><a class="tombol tombol-sekunder" href="/impor"><?= lang('Soal.impor.unggah') ?></a></p>
<?php endif ?>

<h2><?= lang('Soal.pertemuan.pengaturan') ?></h2>
<div class="kartu">
    <form method="post" action="/pertemuan/<?= (int) $p['id'] ?>">
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
        <?php if ((int) $p['jumlah_soal'] > 0): ?><p class="petunjuk"><?= lang('Soal.pertemuan.terkunciInfo') ?></p><?php endif ?>
        <?= view('_galat_soal', ['galat' => $galat, 'kolom' => 'language']) ?>

        <label class="centang">
            <input type="checkbox" name="enabled" value="1" <?= $isi['enabled'] === '1' ? 'checked' : '' ?>>
            <?= lang('Soal.pertemuan.aktif') ?>
        </label>
        <p class="petunjuk"><?= lang('Soal.pertemuan.aktifInfo') ?></p>

        <h3><?= lang('Soal.konsep.judul') ?></h3>
        <p class="petunjuk"><?= lang('Soal.konsep.info') ?></p>
        <?= view('_konsep_kotak', ['katalog' => $katalog, 'terkunci' => $warisan, 'dipilih' => $tambahan]) ?>

        <div class="baris-tombol">
            <button type="submit" class="tombol"><?= lang('Kode.aksi.simpan') ?></button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
