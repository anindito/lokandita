<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<?php $q = static fn (array $ganti) => '?' . http_build_query(array_filter($ganti + $saring, static fn ($v) => $v !== '' && $v !== null)); ?>
<?= view('admin/_nav') ?>
<h1><?= lang('Admin.audit.judul') ?></h1>
<form class="saring" method="get" action="/admin/audit">
    <div><label for="aksi"><?= lang('Admin.audit.aksi') ?></label><input type="text" id="aksi" name="aksi" class="ltr" value="<?= esc($saring['aksi']) ?>" placeholder="login."></div>
    <div><label for="aktor"><?= lang('Admin.audit.aktor') ?></label><input type="text" id="aktor" name="aktor" class="ltr" value="<?= esc($saring['aktor']) ?>"></div>
    <div><label for="target"><?= lang('Admin.audit.target') ?></label><input type="text" id="target" name="target" class="ltr" value="<?= esc($saring['target']) ?>"></div>
    <div>
        <label for="app"><?= lang('Admin.aplikasi') ?></label>
        <select id="app" name="app">
            <option value=""><?= lang('Admin.semua') ?></option>
            <?php foreach (['auth', 'portal', 'lokakode', 'roster'] as $a): ?>
                <option value="<?= $a ?>" <?= $saring['app'] === $a ? 'selected' : '' ?>><?= $a ?></option>
            <?php endforeach ?>
        </select>
    </div>
    <div><label for="dari"><?= lang('Admin.audit.dari') ?></label><input type="text" id="dari" name="dari" class="ltr" value="<?= esc($saring['dari']) ?>" placeholder="2026-11-15" pattern="\d{4}-\d{2}-\d{2}"></div>
    <div><label for="sampai"><?= lang('Admin.audit.sampai') ?></label><input type="text" id="sampai" name="sampai" class="ltr" value="<?= esc($saring['sampai']) ?>" placeholder="2026-11-15" pattern="\d{4}-\d{2}-\d{2}"></div>
    <div><button type="submit" class="tombol"><?= lang('Admin.terapkan') ?></button></div>
</form>
<p class="redup"><?= lang('Admin.audit.jumlah', [$jumlah]) ?></p>
<div class="tabel-wadah kartu">
    <table>
        <thead>
        <tr>
            <th scope="col"><?= lang('Admin.audit.waktu') ?></th><th scope="col"><?= lang('Admin.audit.aksi') ?></th>
            <th scope="col"><?= lang('Admin.audit.aktor') ?></th><th scope="col"><?= lang('Admin.audit.target') ?></th>
            <th scope="col"><?= lang('Admin.aplikasi') ?></th><th scope="col"><?= lang('Admin.audit.rincian') ?></th><th scope="col">IP</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($baris as $b): ?>
            <tr data-audit="<?= esc($b['action']) ?>">
                <td><?= esc(\App\Libraries\Clock::lokal($b['created_at'])) ?></td>
                <td class="ltr"><?= esc($b['action']) ?></td>
                <td><?= $b['aktor_nomor'] === null ? '—' : '<span class="ltr">' . esc($b['aktor_nomor']) . '</span><br><small>' . esc($b['aktor_nama']) . '</small>' ?></td>
                <td><?= $b['target_nomor'] === null ? '—' : '<span class="ltr">' . esc($b['target_nomor']) . '</span><br><small>' . esc($b['target_nama']) . '</small>' ?></td>
                <td><?= esc($b['app']) ?></td>
                <td class="ltr text-sm"><?= esc((string) $b['detail']) ?></td>
                <td class="ltr text-sm" title="<?= esc((string) $b['user_agent']) ?>"><?= esc((string) $b['ip']) ?></td>
            </tr>
        <?php endforeach ?>
        <?php if ($baris === []): ?><tr><td colspan="7" class="redup"><?= lang('Admin.kosong') ?></td></tr><?php endif ?>
        </tbody>
    </table>
</div>
<nav class="halaman" aria-label="<?= lang('Admin.halaman') ?>">
    <?php if ($hal > 1): ?><a class="tombol tombol-sekunder" href="/admin/audit<?= esc($q(['hal' => $hal - 1])) ?>" rel="prev"><?= lang('Admin.sebelumnya') ?></a><?php endif ?>
    <span><?= lang('Admin.halamanKe', [$hal, $akhir]) ?></span>
    <?php if ($hal < $akhir): ?><a class="tombol tombol-sekunder" href="/admin/audit<?= esc($q(['hal' => $hal + 1])) ?>" rel="next"><?= lang('Admin.berikutnya') ?></a><?php endif ?>
</nav>
<?= $this->endSection() ?>
