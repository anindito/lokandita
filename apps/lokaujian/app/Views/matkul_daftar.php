<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<?= view('_nav', ['akses' => $akses]) ?>
<h1><?= lang('Ujian.matkul.judul') ?></h1>

<?php if ($matkul === []): ?>
    <div class="kartu"><p><?= $akses->superadmin() ? lang('Ujian.matkul.kosongAdmin') : lang('Ujian.matkul.kosong') ?></p></div>
<?php else: ?>
    <div class="tabel-wadah kartu">
        <table>
            <thead>
            <tr>
                <th scope="col"><?= lang('Ujian.matkul.kode') ?></th>
                <th scope="col"><?= lang('Ujian.matkul.nama') ?></th>
                <th scope="col"><?= lang('Ujian.matkul.pengampu') ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($matkul as $m): ?>
                <tr>
                    <td class="ltr"><a href="/mata-kuliah/<?= (int) $m['id'] ?>"><?= esc($m['code']) ?></a></td>
                    <td><?= esc($m['name']) ?></td>
                    <td>
                        <?= esc($orang[$m['owner_id']]['nama'] ?? lang('Ujian.akunHilang')) ?>
                        <?= view('_peran_matkul', ['akses' => $akses, 'matkul' => $m]) ?>
                    </td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
<?php endif ?>
<?= $this->endSection() ?>
