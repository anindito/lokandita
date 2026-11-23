<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<?php
use App\Libraries\Kurikulum;
use App\Libraries\Latihan;
use App\Libraries\Waktu;
?>
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

<p><a href="/pertemuan/<?= (int) $p['id'] ?>/papan"><?= lang('Papan.seMatkul') ?></a></p>

<h2 id="latihan"><?= lang('Latihan.terbit.judul') ?></h2>
<div class="kartu">
    <?php if ($p['published_at'] !== null): ?>
        <p><span class="lencana status-approved"><?= lang('Latihan.terbit.sudah') ?></span>
            <span class="redup"><?= esc(Waktu::lokal($p['published_at'])) ?></span></p>
        <p class="petunjuk"><?= lang('Latihan.terbit.sudahInfo') ?></p>
    <?php else: ?>
        <p><span class="lencana status-pending_review"><?= lang('Latihan.terbit.belum') ?></span></p>
        <p class="petunjuk"><?= lang('Latihan.terbit.belumInfo') ?></p>
    <?php endif ?>

    <div class="tabel-wadah">
        <table>
            <caption class="redup"><?= lang('Latihan.terbit.komposisi') ?></caption>
            <thead>
            <tr>
                <th scope="col"><?= lang('Soal.soal.tingkat') ?></th>
                <th scope="col"><?= lang('Latihan.terbit.poin') ?></th>
                <th scope="col"><?= lang('Latihan.terbit.disetujui') ?></th>
                <th scope="col"><?= lang('Latihan.terbit.minimum') ?></th>
                <th scope="col"><?= lang('Latihan.terbit.keterangan') ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($siap['komposisi'] as $tingkat => $k): ?>
                <tr>
                    <td><span class="lencana tingkat-<?= esc(strtolower($tingkat)) ?>"><?= lang('Soal.tingkat.' . $tingkat) ?></span></td>
                    <td><?= Latihan::POIN[$tingkat] ?></td>
                    <td><?= $k['ada'] ?></td>
                    <td><?= $k['minimum'] ?></td>
                    <td><?= $k['kurang'] > 0 ? '<strong class="teks-bahaya">' . lang('Latihan.terbit.kurangSoal', [$k['kurang']]) . '</strong>' : lang('Latihan.terbit.cukup') ?></td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
    <?php if ($siap['ujian']): ?>
        <p data-ujian-terbit><span class="lencana"><?= lang('Latihan.ujian.lencana') ?></span>
            <?= lang('Latihan.ujian.terbitInfo', [$siap['poin']]) ?>
            <?php if ($siap['poin'] < 1): ?><strong class="teks-bahaya"><?= lang('Latihan.ujian.terbitKosong') ?></strong><?php endif ?></p>
    <?php endif ?>
    <p <?= $siap['ujian'] ? 'hidden' : '' ?>>
        <?= lang('Latihan.terbit.poinTersedia', [$siap['poin'], $siap['target'], $siap['butuh']]) ?>
        <?php if ($siap['kurangPoin'] > 0): ?>
            <strong class="teks-bahaya"><?= lang('Latihan.terbit.kurangPoin', [$siap['kurangPoin']]) ?></strong>
        <?php elseif ($siap['peringatan']): ?>
            <strong class="teks-peringatan"><?= lang('Latihan.terbit.peringatan', [3 * $siap['target']]) ?></strong>
        <?php endif ?>
    </p>
    <?php if ((int) $p['enabled'] !== 1): ?><p><strong class="teks-bahaya"><?= lang('Latihan.terbit.nonaktif') ?></strong></p><?php endif ?>

    <?php if ($p['published_at'] === null): ?>
        <form method="post" action="/pertemuan/<?= (int) $p['id'] ?>/terbitkan">
            <?= csrf_field() ?>
            <div class="baris-tombol">
                <button type="submit" class="tombol"><?= lang('Latihan.terbit.terbitkan') ?></button>
            </div>
        </form>
    <?php else: ?>
        <form method="post" action="/pertemuan/<?= (int) $p['id'] ?>/tarik">
            <?= csrf_field() ?>
            <div class="baris-tombol">
                <button type="submit" class="tombol tombol-sekunder"><?= lang('Latihan.terbit.tarik') ?></button>
            </div>
            <p class="petunjuk"><?= lang('Latihan.terbit.tarikInfo') ?></p>
        </form>
    <?php endif ?>
</div>

<h2><?= lang('Latihan.atur.judul') ?></h2>
<div class="kartu">
    <form method="post" action="/pertemuan/<?= (int) $p['id'] ?>/latihan">
        <?= csrf_field() ?>
        <?php if ($siap['ujian']): ?><p class="petunjuk"><strong><?= lang('Latihan.ujian.atur') ?></strong></p><?php endif ?>
        <label for="target_points"><?= lang('Latihan.atur.target') ?></label>
        <input type="number" id="target_points" name="target_points" class="ltr" min="1" max="1000" value="<?= esc($latihan['target_points']) ?>" required>
        <p class="petunjuk"><?= lang('Latihan.atur.targetInfo') ?></p>
        <?= view('_galat_latihan', ['galat' => $galatLatihan, 'kolom' => 'target_points']) ?>

        <fieldset>
            <legend><?= lang('Latihan.atur.komposisi') ?></legend>
            <p class="petunjuk"><?= lang('Latihan.atur.komposisiInfo') ?></p>
            <?php foreach (Latihan::KOLOM_MIN as $tingkat => $kolom): ?>
                <label for="<?= $kolom ?>"><?= lang('Soal.tingkat.' . $tingkat) ?> (<?= lang('Latihan.atur.poinSatuan', [Latihan::POIN[$tingkat]]) ?>)</label>
                <input type="number" id="<?= $kolom ?>" name="<?= $kolom ?>" class="ltr" min="0" max="50" value="<?= esc($latihan[$kolom]) ?>" required>
                <?= view('_galat_latihan', ['galat' => $galatLatihan, 'kolom' => $kolom]) ?>
            <?php endforeach ?>
        </fieldset>

        <label for="starter_template"><?= lang('Latihan.atur.templat') ?></label>
        <textarea id="starter_template" name="starter_template" class="kode-isian" rows="9" spellcheck="false" placeholder="<?= esc($templatBawaan) ?>"><?= esc($latihan['starter_template']) ?></textarea>
        <p class="petunjuk"><?= lang('Latihan.atur.templatInfo') ?></p>
        <?= view('_galat_latihan', ['galat' => $galatLatihan, 'kolom' => 'starter_template']) ?>

        <?php if ($benderaSah !== []): ?>
            <label for="compiler_flags"><?= lang('Latihan.atur.bendera') ?></label>
            <input type="text" id="compiler_flags" name="compiler_flags" class="ltr" maxlength="190" value="<?= esc($latihan['compiler_flags']) ?>">
            <p class="petunjuk"><?= lang('Latihan.atur.benderaInfo') ?> <span class="ltr"><?= esc(implode('  ', $benderaSah)) ?></span></p>
        <?php endif ?>
        <?= view('_galat_latihan', ['galat' => $galatLatihan, 'kolom' => 'compiler_flags']) ?>

        <label class="centang">
            <input type="checkbox" name="autocomplete" value="1" <?= $latihan['autocomplete'] === '1' ? 'checked' : '' ?>>
            <?= lang('Latihan.atur.lengkapi') ?>
        </label>
        <p class="petunjuk"><?= lang('Latihan.atur.lengkapiInfo') ?></p>

        <label class="centang">
            <input type="checkbox" name="practice_after_close" value="1" <?= $latihan['practice_after_close'] === '1' ? 'checked' : '' ?>>
            <?= lang('Latihan.atur.latihanBebas') ?>
        </label>
        <p class="petunjuk"><?= lang('Latihan.atur.latihanBebasInfo') ?></p>

        <label class="centang">
            <input type="checkbox" name="anonymize_leaderboard" value="1" <?= $latihan['anonymize_leaderboard'] === '1' ? 'checked' : '' ?>>
            <?= lang('Latihan.atur.anonim') ?>
        </label>
        <p class="petunjuk"><?= lang('Latihan.atur.anonimInfo') ?></p>

        <div class="baris-tombol">
            <button type="submit" class="tombol"><?= lang('Latihan.atur.simpan') ?></button>
        </div>
    </form>
</div>

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

        <label for="kind"><?= lang('Soal.pertemuan.jenis') ?></label>
        <select id="kind" name="kind">
            <option value="latihan" <?= $isi['kind'] === 'latihan' ? 'selected' : '' ?>><?= lang('Soal.pertemuan.jenisLatihan') ?></option>
            <option value="ujian" <?= $isi['kind'] === 'ujian' ? 'selected' : '' ?>><?= lang('Soal.pertemuan.jenisUjian') ?></option>
        </select>
        <p class="petunjuk"><?= lang('Soal.pertemuan.jenisInfo') ?></p>
        <?= view('_galat_soal', ['galat' => $galat, 'kolom' => 'kind']) ?>

        <h3><?= lang('Soal.konsep.judul') ?></h3>
        <p class="petunjuk"><?= lang('Soal.konsep.info') ?></p>
        <?php if (in_array(0, $asal, true)): ?>
            <p class="petunjuk"><?= lang('Soal.konsep.dasarTerkunci') ?>
                <a href="/mata-kuliah/<?= (int) $p['course_id'] ?>/pertemuan#konsep-dasar"><?= lang('Soal.konsep.dasarUbah') ?></a></p>
        <?php endif ?>
        <?= view('_konsep_kotak', ['katalog' => $katalog, 'asal' => $asal, 'dipilih' => $tambahan]) ?>

        <div class="baris-tombol">
            <button type="submit" class="tombol"><?= lang('Kode.aksi.simpan') ?></button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
