<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<?= view('admin/_nav') ?>
<p><a href="/admin/mata-kuliah"><span class="panah-balik" aria-hidden="true"></span> <?= lang('Roster.matkul.kembali') ?></a></p>
<h1><span class="ltr"><?= esc($matkul['code']) ?></span> — <?= esc($matkul['name']) ?></h1>
<p class="redup">
    <?= lang('Roster.matkul.pengampu') ?>: <?= esc($matkul['pengampu_nama'] ?? lang('Roster.mhs.akunHilang')) ?>
    · <a href="/admin/mata-kuliah/<?= (int) $matkul['id'] ?>/ubah"><?= lang('Roster.matkul.ubah') ?></a>
</p>

<?php if ($tahun === null): ?>
    <div class="kartu"><p><?= lang('Roster.tahun.belumAda') ?></p></div>
<?php else: ?>
    <form class="saring" method="get" action="/admin/mata-kuliah/<?= (int) $matkul['id'] ?>">
        <div>
            <label for="tahun"><?= lang('Roster.tahun.tampilkan') ?></label>
            <select id="tahun" name="tahun">
                <?php foreach ($semuaTahun as $t): ?>
                    <option value="<?= (int) $t['id'] ?>" <?= (int) $t['id'] === (int) $tahun['id'] ? 'selected' : '' ?>>
                        <?= esc($t['label']) ?><?= $t['status'] === 'archived' ? ' (' . lang('Roster.tahun.diarsipkan') . ')' : '' ?>
                    </option>
                <?php endforeach ?>
            </select>
        </div>
        <div><button type="submit" class="tombol tombol-sekunder"><?= lang('Roster.tahun.lihat') ?></button></div>
    </form>

    <h2><?= lang('Roster.kelas.judul') ?> — <?= esc($tahun['label']) ?></h2>
    <?php if ($kelas === []): ?>
        <div class="kartu"><p><?= lang('Roster.kelas.kosong') ?></p></div>
    <?php else: ?>
        <div class="tabel-wadah kartu">
            <table>
                <thead>
                <tr>
                    <th scope="col"><?= lang('Roster.kelas.nama') ?></th>
                    <th scope="col"><?= lang('Roster.kelas.mahasiswa') ?></th>
                    <th scope="col"><?= lang('Roster.kelas.pengajar') ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($kelas as $k): ?>
                    <tr>
                        <td>
                            <a href="/admin/kelas/<?= (int) $k['id'] ?>"><?= esc($k['name']) ?></a>
                            <?php if ($k['archived_at'] !== null): ?><span class="lencana"><?= lang('Roster.kelas.diarsipkan') ?></span><?php endif ?>
                        </td>
                        <td><?= (int) $k['jumlah_aktif'] ?><?= $k['capacity'] !== null ? ' / ' . (int) $k['capacity'] : '' ?></td>
                        <td><?= view('admin/roster/_pengajar', ['daftar' => $pengajar[(int) $k['id']] ?? []]) ?></td>
                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>
        </div>
    <?php endif ?>

    <h2><?= lang('Roster.kelas.tambah') ?></h2>
    <div class="kartu">
        <form method="post" action="/admin/mata-kuliah/<?= (int) $matkul['id'] ?>/kelas">
            <?= csrf_field() ?>
            <input type="hidden" name="tahun" value="<?= (int) $tahun['id'] ?>">
            <label for="name"><?= lang('Roster.kelas.namaKelas') ?></label>
            <input type="text" id="name" name="name" maxlength="60" required>
            <p class="petunjuk"><?= lang('Roster.kelas.namaPetunjuk') ?></p>
            <label for="capacity"><?= lang('Roster.kelas.kapasitasIsi') ?></label>
            <input type="number" id="capacity" name="capacity" class="ltr" min="1" max="99999">
            <div class="baris-tombol">
                <button type="submit" class="tombol"><?= lang('Roster.kelas.tambah') ?></button>
            </div>
        </form>
    </div>
<?php endif ?>
<?= $this->endSection() ?>
