<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<?php
$q   = static fn (array $ganti) => '?' . http_build_query(array_filter($ganti + $saring + ['per' => $per], static fn ($v) => $v !== '' && $v !== null));
?>
<?= view('admin/_nav') ?>
<h1><?= lang('Admin.pengguna') ?></h1>

<form class="saring" method="get" action="/admin/pengguna">
    <div>
        <label for="q"><?= lang('Admin.cari') ?></label>
        <input type="search" id="q" name="q" value="<?= esc($saring['q']) ?>" placeholder="<?= lang('Admin.cariPetunjuk') ?>">
    </div>
    <div>
        <label for="peran"><?= lang('Auth.peran') ?></label>
        <select id="peran" name="peran">
            <option value=""><?= lang('Admin.semua') ?></option>
            <?php foreach (\App\Libraries\UserAdmin::PERAN as $p): ?>
                <option value="<?= $p ?>" <?= $saring['peran'] === $p ? 'selected' : '' ?>><?= lang('Lokandita.peran.' . $p) ?></option>
            <?php endforeach ?>
        </select>
    </div>
    <div>
        <label for="status"><?= lang('Admin.status') ?></label>
        <select id="status" name="status">
            <option value=""><?= lang('Admin.statusBawaan') ?></option>
            <?php foreach (\App\Libraries\UserAdmin::STATUS as $s): ?>
                <option value="<?= $s ?>" <?= $saring['status'] === $s ? 'selected' : '' ?>><?= lang('Admin.statusNama.' . $s) ?></option>
            <?php endforeach ?>
            <option value="semua" <?= $saring['status'] === 'semua' ? 'selected' : '' ?>><?= lang('Admin.statusSemua') ?></option>
        </select>
    </div>
    <div>
        <label for="per"><?= lang('Admin.perHalaman') ?></label>
        <select id="per" name="per">
            <?php foreach ([50, 100, 200] as $n): ?>
                <option value="<?= $n ?>" <?= $per === $n ? 'selected' : '' ?>><?= $n ?></option>
            <?php endforeach ?>
        </select>
    </div>
    <div><button type="submit" class="tombol"><?= lang('Admin.terapkan') ?></button></div>
</form>

<p class="redup">
    <?= lang('Admin.jumlahDitemukan', [$jumlah]) ?> ·
    <a href="/admin/pengguna/ekspor<?= esc($q(['format' => 'csv'])) ?>"><?= lang('Admin.eksporCsv') ?></a> ·
    <a href="/admin/pengguna/ekspor<?= esc($q(['format' => 'xlsx'])) ?>"><?= lang('Admin.eksporXlsx') ?></a>
</p>

<form method="post" action="/admin/pengguna/massal">
    <?= csrf_field() ?>
    <div class="tabel-wadah kartu">
        <table>
            <thead>
            <tr>
                <th scope="col"><span class="hanya-pembaca"><?= lang('Admin.pilih') ?></span></th>
                <th scope="col"><?= lang('Auth.nomorInduk') ?></th>
                <th scope="col"><?= lang('Auth.nama') ?></th>
                <th scope="col"><?= lang('Auth.peran') ?></th>
                <th scope="col"><?= lang('Admin.status') ?></th>
                <th scope="col"><?= lang('Admin.aplikasi') ?></th>
                <th scope="col"><?= lang('Admin.loginTerakhir') ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($baris as $b): ?>
                <tr>
                    <td><input type="checkbox" name="ids[]" value="<?= esc($b['id']) ?>" aria-label="<?= lang('Admin.pilih') ?> <?= esc($b['nomor_induk']) ?>"></td>
                    <td class="ltr"><a href="/admin/pengguna/<?= esc($b['id']) ?>"><?= esc($b['nomor_induk']) ?></a></td>
                    <td><?= esc($b['nama']) ?></td>
                    <td><?= lang('Lokandita.peran.' . $b['global_role']) ?><?= (int) $b['mfa_enabled'] === 1 ? ' · MFA' : '' ?></td>
                    <td>
                        <span class="lencana lencana-<?= esc($b['status']) ?>"><?= lang('Admin.statusNama.' . $b['status']) ?></span>
                        <?php if ($b['locked_until'] !== null && strtotime($b['locked_until'] . ' UTC') > time()): ?>
                            <span class="lencana lencana-galat"><?= lang('Admin.terkunci') ?></span>
                        <?php endif ?>
                    </td>
                    <td><?= esc(implode(', ', $akses[$b['id']] ?? [])) ?></td>
                    <td><?= esc(\App\Libraries\Clock::lokal($b['last_login_at'])) ?></td>
                </tr>
            <?php endforeach ?>
            <?php if ($baris === []): ?>
                <tr><td colspan="7" class="redup"><?= lang('Admin.kosong') ?></td></tr>
            <?php endif ?>
            </tbody>
        </table>
    </div>

    <div class="saring massal">
        <div>
            <label for="aksi"><?= lang('Admin.massal.untukTerpilih') ?></label>
            <select id="aksi" name="aksi" required>
                <option value=""><?= lang('Admin.massal.pilihAksi') ?></option>
                <?php foreach (\App\Libraries\UserAdmin::APPS as $app): ?>
                    <option value="akses:+:<?= $app ?>"><?= lang('Admin.massal.beriAkses', [$app]) ?></option>
                    <option value="akses:-:<?= $app ?>"><?= lang('Admin.massal.cabutAkses', [$app]) ?></option>
                <?php endforeach ?>
                <?php foreach (\App\Libraries\UserAdmin::STATUS as $s): ?>
                    <option value="status:<?= $s ?>"><?= lang('Admin.massal.ubahStatus', [lang('Admin.statusNama.' . $s)]) ?></option>
                <?php endforeach ?>
                <option value="reset"><?= lang('Admin.massal.reset') ?></option>
            </select>
        </div>
        <div><button type="submit" class="tombol tombol-sekunder"><?= lang('Admin.massal.lanjut') ?></button></div>
    </div>
</form>

<nav class="halaman" aria-label="<?= lang('Admin.halaman') ?>">
    <?php if ($hal > 1): ?><a class="tombol tombol-sekunder" href="/admin/pengguna<?= esc($q(['hal' => $hal - 1])) ?>" rel="prev"><?= lang('Admin.sebelumnya') ?></a><?php endif ?>
    <span><?= lang('Admin.halamanKe', [$hal, $akhir]) ?></span>
    <?php if ($hal < $akhir): ?><a class="tombol tombol-sekunder" href="/admin/pengguna<?= esc($q(['hal' => $hal + 1])) ?>" rel="next"><?= lang('Admin.berikutnya') ?></a><?php endif ?>
</nav>
<?= $this->endSection() ?>
