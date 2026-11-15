<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<?= view('_nav', ['akses' => $akses]) ?>
<h1><?= lang('Lokandita.halo', [esc($akses->identitas()->nama)]) ?></h1>

<?php if ($tahun === null): ?>
    <div class="kartu">
        <p><?= $akses->superadmin() ? lang('Kode.tahun.belumAdaAdmin') : lang('Kode.tahun.belumAda') ?></p>
    </div>
<?php else: ?>
    <div class="kartu">
        <p class="redup"><?= lang('Kode.tahun.aktif') ?></p>
        <p class="text-xl font-bold"><?= esc($tahun['label']) ?></p>
        <dl class="flex flex-wrap gap-8">
            <div><dt class="redup"><?= lang('Kode.ringkas.kelas') ?></dt><dd class="text-xl font-bold" data-ringkas="kelas"><?= $ringkas['kelas'] ?></dd></div>
            <div><dt class="redup"><?= lang('Kode.ringkas.mahasiswa') ?></dt><dd class="text-xl font-bold" data-ringkas="mahasiswa"><?= $ringkas['mahasiswa'] ?></dd></div>
            <div><dt class="redup"><?= lang('Kode.ringkas.tanpaPengajar') ?></dt><dd class="text-xl font-bold" data-ringkas="tanpa-pengajar"><?= $ringkas['tanpaPengajar'] ?></dd></div>
        </dl>
    </div>

    <h2><?= lang('Kode.kelas.judul') ?></h2>
    <?php if ($kelas === []): ?>
        <div class="kartu"><p><?= $akses->superadmin() ? lang('Kode.kelas.kosong') : lang('Kode.kelas.kosongDosen') ?></p></div>
    <?php else: ?>
        <div class="tabel-wadah kartu">
            <table>
                <thead>
                <tr>
                    <th scope="col"><?= lang('Kode.matkul.judul') ?></th>
                    <th scope="col"><?= lang('Kode.kelas.nama') ?></th>
                    <th scope="col"><?= lang('Kode.kelas.mahasiswa') ?></th>
                    <th scope="col"><?= lang('Kode.kelas.pengajar') ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($kelas as $k): ?>
                    <tr>
                        <td><a href="/mata-kuliah/<?= (int) $k['course_id'] ?>"><span class="ltr"><?= esc($k['matkul_kode']) ?></span> — <?= esc($k['matkul_nama']) ?></a></td>
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
