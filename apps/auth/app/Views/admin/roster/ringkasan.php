<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<?= view('admin/_nav') ?>
<h1><?= lang('Roster.judul') ?></h1>
<p class="redup"><?= lang('Roster.penjelasan') ?></p>

<?php if ($tahun === null): ?>
    <div class="kartu">
        <p><?= lang('Roster.tahun.belumAda') ?></p>
        <div class="baris-tombol"><a class="tombol" href="/admin/tahun-ajaran"><?= lang('Roster.nav.tahun') ?></a></div>
    </div>
<?php else: ?>
    <div class="kartu">
        <p class="redup"><?= lang('Roster.tahun.aktif') ?></p>
        <p class="text-xl font-bold"><?= esc($tahun['label']) ?></p>
        <dl class="flex flex-wrap gap-8">
            <div><dt class="redup"><?= lang('Roster.ringkas.kelas') ?></dt><dd class="text-xl font-bold" data-ringkas="kelas"><?= $ringkas['kelas'] ?></dd></div>
            <div><dt class="redup"><?= lang('Roster.ringkas.mahasiswa') ?></dt><dd class="text-xl font-bold" data-ringkas="mahasiswa"><?= $ringkas['mahasiswa'] ?></dd></div>
            <div><dt class="redup"><?= lang('Roster.ringkas.tanpaPengajar') ?></dt><dd class="text-xl font-bold" data-ringkas="tanpa-pengajar"><?= $ringkas['tanpaPengajar'] ?></dd></div>
        </dl>
    </div>

    <h2><?= lang('Roster.kelas.judul') ?></h2>
    <?php if ($kelas === []): ?>
        <div class="kartu"><p><?= lang('Roster.kelas.kosong') ?></p></div>
    <?php else: ?>
        <div class="tabel-wadah kartu">
            <table>
                <thead>
                <tr>
                    <th scope="col"><?= lang('Roster.matkul.judul') ?></th>
                    <th scope="col"><?= lang('Roster.kelas.nama') ?></th>
                    <th scope="col"><?= lang('Roster.kelas.mahasiswa') ?></th>
                    <th scope="col"><?= lang('Roster.kelas.pengajar') ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($kelas as $k): ?>
                    <tr>
                        <td><a href="/admin/mata-kuliah/<?= (int) $k['course_id'] ?>"><span class="ltr"><?= esc($k['matkul_kode']) ?></span> — <?= esc($k['matkul_nama']) ?></a></td>
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
<?php endif ?>
<?= $this->endSection() ?>
