<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<?php
use App\Libraries\Kurikulum;
use App\Libraries\Soal\Skema;

$nama   = ['id' => 'Bahasa Indonesia', 'en' => 'English', 'ar' => 'العربية'];
$syarat = (array) ($isi['required_concepts_in_submission'] ?? []);
$params = $isi['params'] ?? [];
$paramsTeks = is_array($params) ? ($params === [] ? '' : json_encode($params, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) : (string) $params;
?>
<?= view('_nav', ['akses' => $akses]) ?>
<h1><?= lang('Soal.soal.ubah') ?>: <span class="ltr"><?= esc($soal['slug']) ?></span></h1>
<p class="redup">
    <a href="/soal/<?= (int) $soal['id'] ?>"><?= lang('Kode.aksi.batal') ?></a>
    · <span class="ltr"><?= esc($soal['matkul_kode']) ?></span> — <?= lang('Soal.pertemuan.ke', [(int) $soal['pertemuan_nomor']]) ?> · <?= lang('Soal.bahasa.' . $soal['language']) ?>
</p>

<?php if ($galat !== []): ?>
    <div class="kartu" role="alert">
        <ul class="daftar-alasan">
            <?php foreach ($galat as $g): ?>
                <li><?php if ($g['field'] !== ''): ?><span class="letak ltr"><?= esc($g['field']) ?>:</span> <?php endif ?><?= esc(lang('Soal.skema.' . $g['kode'], $g['argumen'])) ?></li>
            <?php endforeach ?>
        </ul>
    </div>
<?php endif ?>

<form method="post" action="/soal/<?= (int) $soal['id'] ?>/ubah">
    <?= csrf_field() ?>
    <div class="kartu">
        <p class="petunjuk"><?= lang('Soal.soal.ubahInfo') ?></p>
        <div class="dua-kolom">
            <div>
                <label for="difficulty"><?= lang('Soal.soal.tingkat') ?></label>
                <select id="difficulty" name="difficulty">
                    <?php foreach (Skema::TINGKAT as $t): ?>
                        <option value="<?= $t ?>" <?= ($isi['difficulty'] ?? '') === $t ? 'selected' : '' ?>><?= lang('Soal.tingkat.' . strtoupper($t)) ?></option>
                    <?php endforeach ?>
                </select>
            </div>
            <div>
                <label for="comparison_mode"><?= lang('Soal.soal.pembanding') ?></label>
                <select id="comparison_mode" name="comparison_mode" class="ltr">
                    <?php foreach (Skema::MODE as $m): ?>
                        <option value="<?= $m ?>" <?= ($isi['comparison_mode'] ?? '') === $m ? 'selected' : '' ?>><?= $m ?></option>
                    <?php endforeach ?>
                </select>
            </div>
            <div>
                <label for="time_limit_ms"><?= lang('Soal.soal.batasWaktu') ?> (ms)</label>
                <input type="number" id="time_limit_ms" name="time_limit_ms" class="ltr" min="100" max="10000" value="<?= esc((string) ($isi['time_limit_ms'] ?? '')) ?>" required>
            </div>
            <div>
                <label for="samples_count"><?= lang('Soal.soal.contoh') ?> (2–3)</label>
                <input type="number" id="samples_count" name="samples_count" class="ltr" min="2" max="3" value="<?= esc((string) ($isi['samples_count'] ?? '')) ?>" required>
            </div>
            <div>
                <label for="hidden_count"><?= lang('Soal.soal.tersembunyi') ?> (10–50)</label>
                <input type="number" id="hidden_count" name="hidden_count" class="ltr" min="10" max="50" value="<?= esc((string) ($isi['hidden_count'] ?? '')) ?>" required>
            </div>
        </div>
        <label for="params"><?= lang('Soal.soal.parameter') ?></label>
        <textarea id="params" name="params" class="kode-isian" rows="3" spellcheck="false"><?= esc($paramsTeks) ?></textarea>
        <p class="petunjuk"><?= lang('Soal.soal.parameterInfo') ?></p>
    </div>

    <?php foreach (['id', 'en', 'ar'] as $b): ?>
        <?php $t = $isi['translations'][$b] ?? []; ?>
        <h2><?= esc($nama[$b]) ?></h2>
        <div class="kartu">
            <?php if ($b !== 'id'): ?><p class="petunjuk"><?= lang('Soal.soal.terjemahanKosongInfo') ?></p><?php endif ?>
            <label for="t_<?= $b ?>_title"><?= lang('Soal.soal.judulSoal') ?></label>
            <input type="text" id="t_<?= $b ?>_title" name="t_<?= $b ?>_title" maxlength="190" lang="<?= $b ?>" dir="<?= $b === 'ar' ? 'rtl' : 'ltr' ?>" value="<?= esc((string) ($t['title'] ?? '')) ?>" <?= $b === 'id' ? 'required' : '' ?>>
            <?php foreach (['statement' => 8, 'input_format' => 3, 'output_format' => 3, 'constraints' => 3] as $kunci => $baris): ?>
                <label for="t_<?= $b ?>_<?= $kunci ?>"><?= lang('Soal.soal.' . $kunci) ?></label>
                <textarea id="t_<?= $b ?>_<?= $kunci ?>" name="t_<?= $b ?>_<?= $kunci ?>" rows="<?= $baris ?>" lang="<?= $b ?>" dir="<?= $b === 'ar' ? 'rtl' : 'ltr' ?>"><?= esc((string) ($t[$kunci] ?? '')) ?></textarea>
            <?php endforeach ?>
            <?php if ($b === 'id'): ?>
                <p class="petunjuk"><?= lang('Soal.soal.markdownInfo') ?></p>
            <?php else: ?>
                <label class="centang">
                    <input type="checkbox" name="t_<?= $b ?>_machine" value="1" <?= ($t['machine'] ?? false) === true ? 'checked' : '' ?>>
                    <?= lang('Soal.soal.terjemahanMesin') ?>
                </label>
            <?php endif ?>
        </div>
    <?php endforeach ?>

    <h2><?= lang('Soal.soal.konsepWajib') ?></h2>
    <div class="kartu">
        <p class="petunjuk"><?= lang('Soal.soal.konsepWajibInfo') ?></p>
        <?php foreach ($katalog as $k): ?>
            <label class="centang">
                <input type="checkbox" name="syarat[]" value="<?= esc($k['code']) ?>" <?= in_array($k['code'], $syarat, true) ? 'checked' : '' ?>>
                <?= esc(Kurikulum::namaKonsep($k)) ?> <span class="redup ltr"><?= esc($k['code']) ?></span>
            </label>
        <?php endforeach ?>
    </div>

    <h2><?= lang('Soal.soal.solusi') ?> <span class="redup">(<?= lang('Soal.bahasa.' . $soal['language']) ?>)</span></h2>
    <div class="kartu">
        <label for="reference_solution" class="lewati"><?= lang('Soal.soal.solusi') ?></label>
        <textarea id="reference_solution" name="reference_solution" class="kode-isian" rows="18" spellcheck="false" required><?= esc((string) ($isi['reference_solution'] ?? '')) ?></textarea>
    </div>

    <h2><?= lang('Soal.soal.generator') ?> <span class="redup">(Python)</span></h2>
    <div class="kartu">
        <label for="generator" class="lewati"><?= lang('Soal.soal.generator') ?></label>
        <textarea id="generator" name="generator" class="kode-isian" rows="12" spellcheck="false" required><?= esc((string) ($isi['generator'] ?? '')) ?></textarea>
        <p class="petunjuk"><?= lang('Soal.soal.generatorInfo') ?></p>
    </div>

    <div class="baris-tombol">
        <button type="submit" class="tombol"><?= lang('Soal.soal.simpanValidasi') ?></button>
        <a class="tombol tombol-sekunder" href="/soal/<?= (int) $soal['id'] ?>"><?= lang('Kode.aksi.batal') ?></a>
    </div>
</form>
<?= $this->endSection() ?>
