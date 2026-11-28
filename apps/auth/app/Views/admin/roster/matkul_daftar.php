<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<?= view('admin/_nav') ?>
<h1><?= lang('Roster.matkul.judul') ?></h1>
<p><a class="tombol" href="/admin/mata-kuliah/baru"><?= lang('Roster.matkul.tambah') ?></a></p>

<?php if ($matkul === []): ?>
    <div class="kartu"><p><?= lang('Roster.kosong') ?></p></div>
<?php else: ?>
    <div class="tabel-wadah kartu">
        <table>
            <thead>
            <tr>
                <th scope="col"><?= lang('Roster.matkul.kode') ?></th>
                <th scope="col"><?= lang('Roster.matkul.nama') ?></th>
                <th scope="col"><?= lang('Roster.matkul.pengampu') ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($matkul as $m): ?>
                <tr>
                    <td class="ltr"><a href="/admin/mata-kuliah/<?= (int) $m['id'] ?>"><?= esc($m['code']) ?></a></td>
                    <td><?= esc($m['name']) ?></td>
                    <td><?= esc($m['pengampu_nama'] ?? lang('Roster.mhs.akunHilang')) ?></td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
<?php endif ?>
<?= $this->endSection() ?>
