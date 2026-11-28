<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<?php
$urutan  = ['siap', 'aktifkanLagi', 'kelasLain', 'sudah', 'lulus', 'statusLain', 'bukanMahasiswa', 'tidakAda'];
$kerja   = \App\Libraries\RosterDaftar::DIKERJAKAN;
$pembaca = \App\Libraries\Roster::PEMBACA;
?>
<?= view('admin/_nav') ?>
<h1><?= lang('Roster.daftar.pratinjau') ?> — <span class="ltr"><?= esc($kelas['matkul_kode']) ?></span> <?= esc($kelas['name']) ?></h1>
<p><?= lang('Roster.daftar.belumDisimpan') ?></p>

<?php if ($melebihi !== null): ?>
    <div class="kartu" role="alert"><p><?= lang('Roster.daftar.melebihi', [$melebihi, (int) $kelas['capacity']]) ?></p></div>
<?php endif ?>

<?php foreach ($urutan as $g): ?>
    <?php if ($golongan[$g] === []) { continue; } ?>
    <h2 data-golongan="<?= $g ?>"><?= lang('Roster.daftar.golongan.' . $g) ?> (<?= count($golongan[$g]) ?>)</h2>
    <?php if ($g === 'tidakAda'): ?>
        <p class="petunjuk"><a href="/admin/pengguna/baru"><?= lang('Roster.daftar.buatAkun') ?></a> · <a href="/admin/impor"><?= lang('Admin.impor.judul') ?></a></p>
    <?php endif ?>
    <div class="tabel-wadah kartu">
        <table>
            <thead>
            <tr>
                <th scope="col"><?= lang('Roster.mhs.nomorInduk') ?></th>
                <th scope="col"><?= lang('Roster.mhs.nama') ?></th>
                <th scope="col"><?= lang('Roster.mhs.catatan') ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($golongan[$g] as $b): ?>
                <tr>
                    <td class="ltr"><?= esc($b['nomor_induk']) ?></td>
                    <td><?= esc($b['nama'] ?? '—') ?></td>
                    <td>
                        <?php if (isset($b['kelas_nama'])): ?><?= lang('Roster.daftar.dariKelas', [esc($b['kelas_nama'])]) ?><?php endif ?>
                        <?php if (in_array($g, $kerja, true) && $b['tidakAktif']): ?>
                            <span class="lencana lencana-disabled"><?= lang('Roster.mhs.akunTidakAktif') ?></span>
                        <?php endif ?>
                        <?php if (in_array($g, $kerja, true) && $b['tanpaAkses'] !== []): ?>
                            <span class="lencana lencana-suspended"><?= lang('Roster.mhs.tanpaAkses', [esc(implode(', ', array_map(static fn ($a) => $pembaca[$a], $b['tanpaAkses'])))]) ?></span>
                        <?php endif ?>
                    </td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
<?php endforeach ?>

<?php if ($salah !== []): ?>
    <h2 data-golongan="tidakSah"><?= lang('Roster.daftar.golongan.tidakSah') ?> (<?= count($salah) ?>)</h2>
    <div class="kartu"><ul><?php foreach ($salah as $s): ?><li class="ltr"><?= esc($s) ?></li><?php endforeach ?></ul></div>
<?php endif ?>

<form method="post" action="/admin/kelas/<?= (int) $kelas['id'] ?>/daftar/jalankan">
    <?= csrf_field() ?>
    <?php foreach ($kerja as $g): ?>
        <?php foreach ($golongan[$g] as $b): ?>
            <input type="hidden" name="nomor[]" value="<?= esc($b['nomor_induk']) ?>">
        <?php endforeach ?>
    <?php endforeach ?>
    <?php if ($akan === 0): ?>
        <p><?= lang('Roster.daftar.tidakAdaYangBisa') ?></p>
    <?php endif ?>
    <div class="baris-tombol">
        <?php if ($akan > 0): ?>
            <button type="submit" class="tombol"><?= lang('Roster.daftar.jalankan', [$akan]) ?></button>
        <?php endif ?>
        <a class="tombol tombol-sekunder" href="/admin/kelas/<?= (int) $kelas['id'] ?>"><?= lang('Roster.daftar.kembali') ?></a>
    </div>
</form>
<?= $this->endSection() ?>
