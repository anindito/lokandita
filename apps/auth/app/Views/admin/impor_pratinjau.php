<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<?php $tampil = 200; ?>
<?= view('admin/_nav') ?>
<h1><?= lang('Admin.impor.pratinjauJudul') ?></h1>
<p data-ringkasan><?= lang('Admin.impor.ringkasan', [count($hasil['baru']), count($hasil['ada']), count($hasil['salah'])]) ?></p>

<?php if ($hasil['salah'] !== []): ?>
    <h2><?= lang('Admin.impor.salah', [count($hasil['salah'])]) ?></h2>
    <div class="tabel-wadah kartu">
        <table>
            <thead><tr><th scope="col"><?= lang('Admin.impor.baris') ?></th><th scope="col"><?= lang('Auth.nomorInduk') ?></th><th scope="col"><?= lang('Auth.nama') ?></th><th scope="col"><?= lang('Admin.impor.alasan') ?></th></tr></thead>
            <tbody>
            <?php foreach (array_slice($hasil['salah'], 0, $tampil) as $b): ?>
                <tr data-salah>
                    <td><?= (int) $b['baris'] ?></td>
                    <td class="ltr"><?= esc($b['nomor_induk']) ?></td>
                    <td><?= esc($b['nama']) ?></td>
                    <td><?= esc(implode(' ', array_map(static fn ($a) => lang('Admin.galat.' . $a), $b['alasan']))) ?></td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
<?php endif ?>

<?php foreach (['ada' => 'Admin.impor.ada', 'baru' => 'Admin.impor.baru'] as $kunci => $judulKunci): ?>
    <?php if ($hasil[$kunci] !== []): ?>
        <h2><?= lang($judulKunci, [count($hasil[$kunci])]) ?></h2>
        <div class="tabel-wadah kartu">
            <table>
                <thead><tr><th scope="col"><?= lang('Admin.impor.baris') ?></th><th scope="col"><?= lang('Auth.nomorInduk') ?></th><th scope="col"><?= lang('Auth.nama') ?></th><th scope="col"><?= lang('Auth.peran') ?></th><th scope="col"><?= lang('Admin.aplikasi') ?></th></tr></thead>
                <tbody>
                <?php foreach (array_slice($hasil[$kunci], 0, $tampil) as $b): ?>
                    <tr data-<?= $kunci ?>>
                        <td><?= (int) $b['baris'] ?></td>
                        <td class="ltr"><?= esc($b['nomor_induk']) ?></td>
                        <td><?= esc($b['nama']) ?></td>
                        <td><?= lang('Lokandita.peran.' . $b['global_role']) ?></td>
                        <td><?= esc(implode(', ', $b['apps'])) ?></td>
                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>
            <?php if (count($hasil[$kunci]) > $tampil): ?>
                <p class="petunjuk"><?= lang('Admin.impor.hanyaSebagian', [$tampil, count($hasil[$kunci])]) ?></p>
            <?php endif ?>
        </div>
    <?php endif ?>
<?php endforeach ?>

<?php if ($hasil['baru'] !== []): ?>
    <div class="kartu">
        <h2><?= lang('Admin.impor.sandiAwal') ?></h2>
        <form method="post" action="/admin/impor/jalankan">
            <?= csrf_field() ?>
            <input type="hidden" name="token" value="<?= esc($token) ?>">
            <label class="centang"><input type="radio" name="mode" value="acak" checked> <?= lang('Admin.impor.modeAcak') ?></label>
            <label class="centang"><input type="radio" name="mode" value="sama"> <?= lang('Admin.impor.modeSama') ?></label>
            <label for="sandi_bersama"><?= lang('Admin.impor.sandiBersama') ?></label>
            <input type="text" id="sandi_bersama" name="sandi_bersama" class="ltr" autocomplete="off" maxlength="200">
            <p class="petunjuk"><?= lang('Admin.impor.sandiPetunjuk') ?></p>
            <div class="baris-tombol">
                <button type="submit" class="tombol"><?= lang('Admin.impor.jalankan', [count($hasil['baru'])]) ?></button>
                <a class="tombol tombol-sekunder" href="/admin/impor"><?= lang('Admin.batal') ?></a>
            </div>
            <p class="petunjuk"><?= lang('Admin.impor.lama') ?></p>
        </form>
    </div>
<?php else: ?>
    <p><?= lang('Admin.impor.tidakAdaBaru') ?></p>
    <div class="baris-tombol"><a class="tombol tombol-sekunder" href="/admin/impor"><?= lang('Admin.impor.unggahLain') ?></a></div>
<?php endif ?>
<?= $this->endSection() ?>
