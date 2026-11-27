<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<?php
/**
 * Log audit LokaUjian (SPEC-LOKAUJIAN.md §13), superadmin saja. Hanya untuk dibaca.
 * Variabel: $baris, $jumlah, $hal, $akhir, $saring, $aksi (daftar aksi yang pernah dicatat),
 * $matkul (id => kode), $orang (direktori LokaAuth untuk pelaku).
 */
$jam   = static fn (string $utc): string => (new DateTimeImmutable($utc, new DateTimeZone('UTC')))->setTimezone(new DateTimeZone('Asia/Jakarta'))->format('d-m-Y H:i:s');
$query = static fn (int $h): string => '/audit?' . http_build_query(array_filter($saring, static fn (string $v): bool => $v !== '') + ['hal' => $h]);
?>
<?= view('_nav', ['akses' => $akses]) ?>
<h1><?= lang('Hasil.audit.judul') ?></h1>
<p class="redup"><?= lang('Hasil.audit.info') ?></p>
<form method="get" action="/audit" class="kartu" data-audit-saring>
    <label for="a-aksi"><?= lang('Hasil.audit.aksi') ?></label>
    <select id="a-aksi" name="aksi">
        <option value=""><?= lang('Hasil.audit.semua') ?></option>
        <?php foreach ($aksi as $a): ?><option value="<?= esc($a, 'attr') ?>"<?= $saring['aksi'] === $a ? ' selected' : '' ?>><?= esc($a) ?></option><?php endforeach ?>
    </select>
    <label for="a-matkul"><?= lang('Ujian.matkul.satu') ?></label>
    <select id="a-matkul" name="matkul">
        <option value=""><?= lang('Hasil.audit.semua') ?></option>
        <?php foreach ($matkul as $id => $kode): ?><option value="<?= (int) $id ?>"<?= $saring['matkul'] === (string) $id ? ' selected' : '' ?>><?= esc($kode) ?></option><?php endforeach ?>
    </select>
    <label for="a-pelaku"><?= lang('Hasil.audit.pelakuNomor') ?></label>
    <input type="text" id="a-pelaku" name="pelaku" class="ltr" maxlength="40" value="<?= esc($saring['pelaku'], 'attr') ?>">
    <label for="a-dari"><?= lang('Hasil.audit.dari') ?></label>
    <input type="date" id="a-dari" name="dari" value="<?= esc($saring['dari'], 'attr') ?>">
    <label for="a-sampai"><?= lang('Hasil.audit.sampai') ?></label>
    <input type="date" id="a-sampai" name="sampai" value="<?= esc($saring['sampai'], 'attr') ?>">
    <div class="baris-tombol"><button type="submit" class="tombol tombol-sekunder"><?= lang('Hasil.audit.saring') ?></button></div>
</form>
<p data-audit-jumlah><?= lang('Hasil.audit.jumlah', [(int) $jumlah, (int) $hal, (int) $akhir]) ?></p>
<?php if ($baris !== []): ?>
    <div class="tabel-wadah kartu">
        <table data-audit>
            <thead><tr>
                <th scope="col"><?= lang('SebUjian.peristiwa.waktu') ?></th>
                <th scope="col"><?= lang('Hasil.audit.pelaku') ?></th>
                <th scope="col"><?= lang('Hasil.audit.aksi') ?></th>
                <th scope="col"><?= lang('Hasil.audit.sasaran') ?></th>
                <th scope="col"><?= lang('SebUjian.peristiwa.rincian') ?></th>
            </tr></thead>
            <tbody>
            <?php foreach ($baris as $b): ?>
                <?php $o = $orang[(string) $b['actor_id']] ?? null; ?>
                <tr data-aksi="<?= esc($b['action'], 'attr') ?>">
                    <td class="ltr"><?= esc($jam((string) $b['created_at'])) ?></td>
                    <td><span class="ltr"><?= esc($o['nomor_induk'] ?? '—') ?></span> <?= esc($o['nama'] ?? lang('Ujian.akunHilang')) ?></td>
                    <td class="ltr"><?= esc($b['action']) ?></td>
                    <td class="ltr"><?= esc(trim(($b['target_tipe'] ?? '') . ' ' . ($b['target_id'] ?? ''))) ?><?= $b['course_id'] === null ? '' : ' · ' . esc($matkul[(int) $b['course_id']] ?? '#' . $b['course_id']) ?><?= $b['class_id'] === null ? '' : ' · ' . lang('Hasil.audit.kelas', [(int) $b['class_id']]) ?></td>
                    <td class="ltr" dir="ltr"><code><?= esc((string) $b['detail']) ?></code></td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
    <div class="baris-tombol">
        <?php if ($hal > 1): ?><a class="tombol tombol-sekunder" href="<?= esc($query($hal - 1), 'attr') ?>" data-audit-sebelum><?= lang('Hasil.audit.sebelumnya') ?></a><?php endif ?>
        <?php if ($hal < $akhir): ?><a class="tombol tombol-sekunder" href="<?= esc($query($hal + 1), 'attr') ?>" data-audit-berikut><?= lang('Hasil.audit.berikutnya') ?></a><?php endif ?>
    </div>
<?php endif ?>
<?= $this->endSection() ?>
