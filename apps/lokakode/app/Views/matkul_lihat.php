<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<?= view('_nav', ['akses' => $akses]) ?>
<h1><span class="ltr"><?= esc($matkul['code']) ?></span> — <?= esc($matkul['name']) ?></h1>
<p class="redup">
    <?= lang('Kode.matkul.pengampu') ?>: <?= esc($orang[$matkul['owner_id']]['nama'] ?? lang('Kode.mhs.akunHilang')) ?>
    <?php if ($akses->superadmin()): ?>
        · <a href="/mata-kuliah/<?= (int) $matkul['id'] ?>/ubah"><?= lang('Kode.matkul.ubah') ?></a>
    <?php endif ?>
</p>

<?php if ($tahun === null): ?>
    <div class="kartu"><p><?= $akses->superadmin() ? lang('Kode.tahun.belumAdaAdmin') : lang('Kode.tahun.belumAda') ?></p></div>
<?php else: ?>
    <form class="saring" method="get" action="/mata-kuliah/<?= (int) $matkul['id'] ?>">
        <div>
            <label for="tahun"><?= lang('Kode.tahun.tampilkan') ?></label>
            <select id="tahun" name="tahun">
                <?php foreach ($semuaTahun as $t): ?>
                    <option value="<?= (int) $t['id'] ?>" <?= (int) $t['id'] === (int) $tahun['id'] ? 'selected' : '' ?>>
                        <?= esc($t['label']) ?><?= $t['status'] === 'archived' ? ' (' . lang('Kode.tahun.diarsipkan') . ')' : '' ?>
                    </option>
                <?php endforeach ?>
            </select>
        </div>
        <div><button type="submit" class="tombol tombol-sekunder"><?= lang('Kode.tahun.lihat') ?></button></div>
    </form>

    <h2><?= lang('Kode.kelas.judul') ?> — <?= esc($tahun['label']) ?></h2>
    <?php if ($kelas === []): ?>
        <div class="kartu"><p><?= lang('Kode.kelas.kosong') ?></p></div>
    <?php else: ?>
        <div class="tabel-wadah kartu">
            <table>
                <thead>
                <tr>
                    <th scope="col"><?= lang('Kode.kelas.nama') ?></th>
                    <th scope="col"><?= lang('Kode.kelas.mahasiswa') ?></th>
                    <th scope="col"><?= lang('Kode.kelas.pengajar') ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($kelas as $k): ?>
                    <tr>
                        <td>
                            <a href="/kelas/<?= (int) $k['id'] ?>"><?= esc($k['name']) ?></a>
                            <?php if ($k['archived_at'] !== null): ?><span class="lencana"><?= lang('Kode.kelas.diarsipkan') ?></span><?php endif ?>
                        </td>
                        <td><?= (int) $k['jumlah_aktif'] ?><?= $k['capacity'] !== null ? ' / ' . (int) $k['capacity'] : '' ?></td>
                        <td><?= view('_pengajar', ['daftar' => $pengajar[(int) $k['id']] ?? [], 'orang' => $orang]) ?></td>
                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>
        </div>
    <?php endif ?>
<?php endif ?>
<?= $this->endSection() ?>
