<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<?php use App\Libraries\Waktu; ?>
<?= view('_nav', ['akses' => $akses]) ?>
<h1><?= lang('Soal.impor.judul') ?></h1>

<div class="kartu">
    <?php if (! $worker): ?>
        <p class="galat-kolom" role="alert"><?= lang('Soal.impor.galat.judgeMati') ?></p>
    <?php endif ?>
    <form method="post" action="/impor" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <label for="berkas"><?= lang('Soal.impor.berkas') ?></label>
        <input type="file" id="berkas" name="berkas" accept=".json,application/json" required>
        <p class="petunjuk"><?= lang('Soal.impor.berkasInfo') ?></p>
        <div class="baris-tombol">
            <button type="submit" class="tombol"><?= lang('Soal.impor.unggah') ?></button>
        </div>
    </form>
</div>

<h2><?= lang('Soal.impor.riwayat') ?></h2>
<?php if ($impor === []): ?>
    <div class="kartu"><p><?= lang('Soal.impor.kosong') ?></p></div>
<?php else: ?>
    <div class="tabel-wadah kartu">
        <table>
            <thead>
            <tr>
                <th scope="col"><?= lang('Soal.impor.waktu') ?></th>
                <th scope="col"><?= lang('Soal.impor.namaBerkas') ?></th>
                <th scope="col"><?= lang('Soal.impor.tujuan') ?></th>
                <th scope="col"><?= lang('Soal.impor.oleh') ?></th>
                <th scope="col"><?= lang('Soal.impor.hasil') ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($impor as $i): ?>
                <tr>
                    <td><?= esc(Waktu::lokal($i['created_at'])) ?></td>
                    <td><a href="/impor/<?= esc($i['id']) ?>"><?= esc($i['file_name']) ?></a> <span class="redup"><?= lang('Soal.impor.sumber.' . $i['source']) ?></span></td>
                    <td><?= $i['matkul_kode'] !== null ? '<span class="ltr">' . esc($i['matkul_kode']) . '</span> · ' . lang('Soal.pertemuan.ke', [(int) $i['pertemuan_nomor']]) : '—' ?></td>
                    <td><?= esc($orang[$i['actor_id']]['nama'] ?? lang('Kode.mhs.akunHilang')) ?></td>
                    <td>
                        <span class="lencana keadaan-<?= esc(strtolower($i['status'])) ?>"><?= lang('Soal.impor.status.' . $i['status']) ?></span>
                        <?php if ($i['status'] !== 'DITOLAK'): ?>
                            <?= lang('Soal.impor.ringkas', [(string) (int) $i['diterima'], (string) (int) $i['ditolak'], (string) (int) $i['total']]) ?>
                        <?php endif ?>
                    </td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
<?php endif ?>
<?= $this->endSection() ?>
