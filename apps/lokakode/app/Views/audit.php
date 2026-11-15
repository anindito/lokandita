<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<?php
$q     = static fn (array $ganti) => '?' . http_build_query(array_filter($ganti + $saring, static fn ($v) => $v !== '' && $v !== null));
$siapa = static function (?string $id) use ($orang): string {
    if ($id === null) {
        return '—';
    }
    $o = $orang[$id] ?? null;

    return $o === null ? '<span class="ltr">' . esc($id) . '</span>' : '<span class="ltr">' . esc($o['nomor_induk']) . '</span><br><small>' . esc($o['nama']) . '</small>';
};
?>
<?= view('_nav', ['akses' => $akses]) ?>
<h1><?= lang('Kode.nav.audit') ?></h1>
<p class="redup"><?= lang('Kode.audit.penjelasan') ?></p>
<form class="saring" method="get" action="/audit">
    <div><label for="aksi"><?= lang('Kode.audit.aksi') ?></label><input type="text" id="aksi" name="aksi" class="ltr" value="<?= esc($saring['aksi']) ?>" placeholder="pendaftaran."></div>
    <div><label for="aktor"><?= lang('Kode.audit.aktor') ?></label><input type="text" id="aktor" name="aktor" class="ltr" value="<?= esc($saring['aktor']) ?>"></div>
    <div><label for="dari"><?= lang('Kode.audit.dari') ?></label><input type="date" id="dari" name="dari" class="ltr" value="<?= esc($saring['dari']) ?>"></div>
    <div><label for="sampai"><?= lang('Kode.audit.sampai') ?></label><input type="date" id="sampai" name="sampai" class="ltr" value="<?= esc($saring['sampai']) ?>"></div>
    <div><button type="submit" class="tombol"><?= lang('Kode.tahun.lihat') ?></button></div>
</form>
<p class="redup"><?= lang('Kode.audit.jumlah', [$jumlah]) ?></p>
<div class="tabel-wadah kartu">
    <table>
        <thead>
        <tr>
            <th scope="col"><?= lang('Kode.audit.waktu') ?></th><th scope="col"><?= lang('Kode.audit.aksi') ?></th>
            <th scope="col"><?= lang('Kode.audit.aktor') ?></th><th scope="col"><?= lang('Kode.audit.target') ?></th>
            <th scope="col"><?= lang('Kode.audit.rincian') ?></th><th scope="col">IP</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($baris as $b): ?>
            <tr data-audit="<?= esc($b['action']) ?>">
                <td><?= esc(\App\Libraries\Waktu::lokal($b['created_at'])) ?></td>
                <td class="ltr"><?= esc($b['action']) ?></td>
                <td><?= $siapa($b['actor_id']) ?></td>
                <td>
                    <?php if ($b['target_tipe'] === 'user'): ?>
                        <?= $siapa($b['target_id']) ?>
                    <?php elseif ($b['target_tipe'] !== null): ?>
                        <span class="ltr"><?= esc($b['target_tipe']) ?> #<?= esc((string) $b['target_id']) ?></span>
                    <?php else: ?>—<?php endif ?>
                    <?php if ($b['class_id'] !== null): ?><br><small class="ltr"><a href="/kelas/<?= (int) $b['class_id'] ?>">class #<?= (int) $b['class_id'] ?></a></small><?php endif ?>
                </td>
                <td class="ltr text-sm"><?= esc((string) $b['detail']) ?></td>
                <td class="ltr text-sm" title="<?= esc((string) $b['user_agent']) ?>"><?= esc((string) $b['ip']) ?></td>
            </tr>
        <?php endforeach ?>
        <?php if ($baris === []): ?><tr><td colspan="6" class="redup"><?= lang('Kode.kosong') ?></td></tr><?php endif ?>
        </tbody>
    </table>
</div>
<nav class="halaman" aria-label="<?= lang('Kode.audit.halaman') ?>">
    <?php if ($hal > 1): ?><a class="tombol tombol-sekunder" href="/audit<?= esc($q(['hal' => $hal - 1])) ?>" rel="prev"><?= lang('Kode.audit.sebelumnya') ?></a><?php endif ?>
    <span><?= lang('Kode.audit.halamanKe', [$hal, $akhir]) ?></span>
    <?php if ($hal < $akhir): ?><a class="tombol tombol-sekunder" href="/audit<?= esc($q(['hal' => $hal + 1])) ?>" rel="next"><?= lang('Kode.audit.berikutnya') ?></a><?php endif ?>
</nav>
<?= $this->endSection() ?>
