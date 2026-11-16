<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<?php
use App\Libraries\Soal\Pernyataan;
use App\Libraries\Waktu;

$menunggu = $versi['status'] === 'PENDING_REVIEW';
$dipakai  = $soal['versi_dipakai'] !== null && (int) $soal['versi_dipakai'] === (int) $versi['id'];
$nama     = ['id' => 'Bahasa Indonesia', 'en' => 'English', 'ar' => 'العربية'];
?>
<?= view('_nav', ['akses' => $akses]) ?>
<h1><?= esc($terjemahan['id']['title'] ?? $soal['slug']) ?></h1>
<p class="redup">
    <a href="/pertemuan/<?= (int) $soal['meeting_id'] ?>"><span class="ltr"><?= esc($soal['matkul_kode']) ?></span> — <?= lang('Soal.pertemuan.ke', [(int) $soal['pertemuan_nomor']]) ?></a>
    · <span class="ltr"><?= esc($soal['slug']) ?></span> · <?= lang('Soal.bahasa.' . $soal['language']) ?>
</p>

<div class="kartu">
    <p>
        <?= lang('Soal.soal.versiKe', [(string) (int) $versi['version']]) ?>
        <span class="lencana status-<?= esc(strtolower($versi['status'])) ?>"><?= lang('Soal.status.' . $versi['status']) ?></span>
        <?php if ($dipakai): ?><span class="lencana"><?= lang('Soal.soal.dipakai') ?></span><?php endif ?>
        <span class="lencana tingkat-<?= esc(strtolower($versi['difficulty'])) ?>"><?= lang('Soal.tingkat.' . $versi['difficulty']) ?></span>
    </p>
    <p class="redup">
        <?= lang('Soal.soal.dibuat', [esc($orang[$versi['created_by']]['nama'] ?? lang('Kode.mhs.akunHilang')), esc(Waktu::lokal($versi['created_at']))]) ?>
        <?php if ($versi['reviewed_by'] !== null): ?>
            · <?= lang('Soal.soal.ditinjau', [esc($orang[$versi['reviewed_by']]['nama'] ?? lang('Kode.mhs.akunHilang')), esc(Waktu::lokal($versi['reviewed_at']))]) ?>
        <?php endif ?>
    </p>
    <?php if ($versi['review_note'] !== null): ?><p><strong><?= lang('Soal.soal.catatanTolak') ?>:</strong> <?= esc($versi['review_note']) ?></p><?php endif ?>
    <?php if (count($semua) > 1): ?>
        <p>
            <?= lang('Soal.soal.versiLain') ?>:
            <?php foreach ($semua as $v): ?>
                <a href="/soal/<?= (int) $soal['id'] ?>?versi=<?= (int) $v['version'] ?>" <?= (int) $v['id'] === (int) $versi['id'] ? 'aria-current="true"' : '' ?>><?= (int) $v['version'] ?></a>
                <span class="redup">(<?= lang('Soal.status.' . $v['status']) ?>)</span>
            <?php endforeach ?>
        </p>
    <?php endif ?>
    <div class="baris-tombol">
        <?php if ($menunggu): ?>
            <form method="post" action="/soal/<?= (int) $soal['id'] ?>/setujui">
                <?= csrf_field() ?>
                <input type="hidden" name="versi_id" value="<?= (int) $versi['id'] ?>">
                <button type="submit" class="tombol"><?= lang('Soal.soal.setujui') ?></button>
            </form>
        <?php endif ?>
        <a class="tombol tombol-sekunder" href="/soal/<?= (int) $soal['id'] ?>/ubah"><?= lang('Soal.soal.ubah') ?></a>
    </div>
    <?php if ($menunggu): ?>
        <p class="petunjuk"><?= lang('Soal.soal.setujuiInfo') ?></p>
        <form method="post" action="/soal/<?= (int) $soal['id'] ?>/tolak">
            <?= csrf_field() ?>
            <input type="hidden" name="versi_id" value="<?= (int) $versi['id'] ?>">
            <label for="catatan"><?= lang('Soal.soal.catatanIsi') ?></label>
            <input type="text" id="catatan" name="catatan" minlength="3" maxlength="500" required>
            <div class="baris-tombol">
                <button type="submit" class="tombol tombol-bahaya"><?= lang('Soal.soal.tolak') ?></button>
            </div>
        </form>
    <?php endif ?>
</div>

<h2><?= lang('Soal.soal.pernyataan') ?></h2>
<?php foreach (['id', 'en', 'ar'] as $b): ?>
    <?php $t = $terjemahan[$b] ?? null; ?>
    <div class="kartu" lang="<?= $b ?>" dir="<?= $b === 'ar' ? 'rtl' : 'ltr' ?>">
        <h3>
            <?= esc($nama[$b]) ?>
            <?php if ($t === null): ?>
                <span class="lencana"><?= lang('Soal.soal.belumTerjemah') ?></span>
            <?php elseif (($t['machine'] ?? false) === true): ?>
                <span class="lencana keadaan-menunggu"><?= lang('Soal.soal.terjemahanMesin') ?></span>
            <?php endif ?>
        </h3>
        <?php if ($t === null): ?>
            <p class="petunjuk"><?= lang('Soal.soal.belumTerjemahInfo') ?></p>
        <?php else: ?>
            <p><strong><?= esc($t['title']) ?></strong></p>
            <div class="pernyataan"><?= Pernyataan::html((string) $t['statement'], $params) ?></div>
            <?php foreach (['input_format', 'output_format', 'constraints'] as $kunci): ?>
                <?php if (trim((string) ($t[$kunci] ?? '')) !== ''): ?>
                    <h4><?= lang('Soal.soal.' . $kunci) ?></h4>
                    <div class="pernyataan"><?= Pernyataan::html((string) $t[$kunci], $params) ?></div>
                <?php endif ?>
            <?php endforeach ?>
            <?php if (($t['machine'] ?? false) === true): ?>
                <form method="post" action="/soal/<?= (int) $soal['id'] ?>/terjemahan" dir="<?= \Lokandita\Auth\Locale::arah(service('request')->getLocale()) ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="versi_id" value="<?= (int) $versi['id'] ?>">
                    <input type="hidden" name="bahasa" value="<?= $b ?>">
                    <div class="baris-tombol">
                        <button type="submit" class="tombol tombol-sekunder"><?= lang('Soal.soal.terjemahanDiperiksa') ?></button>
                    </div>
                </form>
            <?php endif ?>
        <?php endif ?>
    </div>
<?php endforeach ?>

<h2><?= lang('Soal.soal.penilaian') ?></h2>
<div class="kartu">
    <table>
        <tbody>
        <tr><th scope="row"><?= lang('Soal.soal.contoh') ?></th><td><?= (int) $versi['samples_count'] ?></td></tr>
        <tr><th scope="row"><?= lang('Soal.soal.tersembunyi') ?></th><td><?= (int) $versi['hidden_count'] ?></td></tr>
        <tr><th scope="row"><?= lang('Soal.soal.batasWaktu') ?></th><td><span class="ltr"><?= (int) $versi['time_limit_ms'] ?> ms</span></td></tr>
        <tr><th scope="row"><?= lang('Soal.soal.pembanding') ?></th><td><span class="ltr"><?= esc($versi['comparison_mode']) ?></span></td></tr>
        <tr>
            <th scope="row"><?= lang('Soal.soal.konsepDipakai') ?></th>
            <td><?= $konsep === [] ? '—' : esc(implode(', ', array_map(static fn ($k) => $katalog[$k] ?? $k, $konsep))) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= lang('Soal.soal.konsepWajib') ?></th>
            <td><?= $syarat === [] ? lang('Soal.soal.tidakAdaWajib') : esc(implode(', ', array_map(static fn ($k) => $katalog[$k] ?? $k, $syarat))) ?></td>
        </tr>
        <?php if ($params !== []): ?>
            <tr><th scope="row"><?= lang('Soal.soal.parameter') ?></th><td><span class="ltr"><?= esc(json_encode($params, JSON_UNESCAPED_UNICODE)) ?></span></td></tr>
        <?php endif ?>
        </tbody>
    </table>
</div>

<h2><?= lang('Soal.soal.solusi') ?> <span class="redup">(<?= lang('Soal.bahasa.' . $versi['language']) ?>)</span></h2>
<pre class="kode-sumber" tabindex="0"><code><?= esc($versi['reference_solution']) ?></code></pre>

<h2><?= lang('Soal.soal.generator') ?> <span class="redup">(Python)</span></h2>
<pre class="kode-sumber" tabindex="0"><code><?= esc($versi['generator']) ?></code></pre>
<?= $this->endSection() ?>
