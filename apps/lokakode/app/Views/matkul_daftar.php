<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<?= view('_nav', ['akses' => $akses]) ?>
<h1><?= lang('Kode.matkul.judul') ?></h1>
<?php if ($akses->superadmin()): ?>
    <p><a class="tombol" href="/mata-kuliah/baru"><?= lang('Kode.matkul.tambah') ?></a></p>
<?php endif ?>

<?php if ($matkul === []): ?>
    <div class="kartu"><p><?= $akses->superadmin() ? lang('Kode.kosong') : lang('Kode.matkul.kosong') ?></p></div>
<?php else: ?>
    <div class="tabel-wadah kartu">
        <table>
            <thead>
            <tr>
                <th scope="col"><?= lang('Kode.matkul.kode') ?></th>
                <th scope="col"><?= lang('Kode.matkul.nama') ?></th>
                <th scope="col"><?= lang('Kode.matkul.pengampu') ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($matkul as $m): ?>
                <tr>
                    <td class="ltr"><a href="/mata-kuliah/<?= (int) $m['id'] ?>"><?= esc($m['code']) ?></a></td>
                    <td><?= esc($m['name']) ?></td>
                    <td>
                        <?= esc($orang[$m['owner_id']]['nama'] ?? lang('Kode.mhs.akunHilang')) ?>
                        <?php if ($m['owner_id'] === $akses->identitas()->userId): ?>
                            <span class="lencana lencana-active"><?= lang('Kode.matkul.andaPengampu') ?></span>
                        <?php elseif ($akses->dosen()): ?>
                            <span class="lencana"><?= lang('Kode.matkul.andaPengajar') ?></span>
                        <?php endif ?>
                    </td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
<?php endif ?>
<?= $this->endSection() ?>
