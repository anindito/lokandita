<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<?php
$aktif = count(array_filter($pendaftaran, static fn ($p) => $p['status'] === 'AKTIF'));
?>
<?= view('_nav', ['akses' => $akses]) ?>
<p><a href="/mata-kuliah/<?= (int) $kelas['course_id'] ?>?tahun=<?= (int) $kelas['academic_year_id'] ?>">&larr; <?= lang('Kode.kelas.kembali') ?></a></p>
<h1>
    <span class="ltr"><?= esc($kelas['matkul_kode']) ?></span> <?= esc($kelas['name']) ?>
    <?php if ($kelas['archived_at'] !== null): ?><span class="lencana"><?= lang('Kode.kelas.diarsipkan') ?></span><?php endif ?>
</h1>

<div class="kartu">
    <dl>
        <dt class="redup"><?= lang('Kode.matkul.judul') ?></dt>
        <dd><?= esc($kelas['matkul_nama']) ?></dd>
        <dt class="redup"><?= lang('Kode.tahun.tampilkan') ?></dt>
        <dd><?= esc($kelas['tahun_label']) ?><?= $kelas['tahun_status'] === 'archived' ? ' (' . lang('Kode.tahun.diarsipkan') . ')' : '' ?></dd>
        <dt class="redup"><?= lang('Kode.matkul.pengampu') ?></dt>
        <dd><?= esc($orang[$kelas['owner_id']]['nama'] ?? lang('Kode.mhs.akunHilang')) ?></dd>
        <dt class="redup"><?= lang('Kode.kelas.pengajar') ?></dt>
        <dd><?= view('_pengajar', ['daftar' => $pengajar, 'orang' => $orang]) ?></dd>
        <dt class="redup"><?= lang('Kode.kelas.kapasitas') ?></dt>
        <dd><?= $kelas['capacity'] !== null ? (int) $kelas['capacity'] : lang('Kode.kelas.tanpaBatas') ?></dd>
    </dl>
    <?php if ((int) $kelas['boleh_kelola'] !== 1): ?>
        <p class="petunjuk"><?= lang('Kode.kelas.hanyaLihat') ?></p>
    <?php endif ?>
</div>

<h2><?= lang('Kode.kelas.daftar') ?></h2>
<p class="redup"><?= lang('Kode.kelas.jumlah', [count($pendaftaran), $aktif]) ?></p>
<?php if ($pendaftaran === []): ?>
    <div class="kartu"><p><?= lang('Kode.kelas.daftarKosong') ?></p></div>
<?php else: ?>
    <div class="tabel-wadah kartu">
        <table>
            <thead>
            <tr>
                <th scope="col"><?= lang('Kode.mhs.nomorInduk') ?></th>
                <th scope="col"><?= lang('Kode.mhs.nama') ?></th>
                <th scope="col"><?= lang('Kode.mhs.status') ?></th>
                <th scope="col"><?= lang('Kode.mhs.catatan') ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($pendaftaran as $p): ?>
                <?php $o = $orang[$p['user_id']] ?? null; ?>
                <tr>
                    <td class="ltr"><?= esc($o['nomor_induk'] ?? '—') ?></td>
                    <td><?= esc($o['nama'] ?? lang('Kode.mhs.akunHilang')) ?></td>
                    <td>
                        <?= lang('Kode.mhs.statusNama.' . $p['status']) ?>
                        <?php if ($p['status'] === 'LULUS' && $p['graduated_on'] !== null): ?>
                            <span class="redup">(<?= lang('Kode.mhs.tanggalLulus', [esc(date('d-m-Y', strtotime($p['graduated_on'])))]) ?>)</span>
                        <?php endif ?>
                    </td>
                    <td>
                        <?php if ($o !== null && $o['status'] !== 'active'): ?>
                            <span class="lencana lencana-disabled"><?= lang('Kode.mhs.akunTidakAktif') ?></span>
                        <?php endif ?>
                        <?php if ($o !== null && ! isset($berakses[$p['user_id']])): ?>
                            <span class="lencana lencana-suspended"><?= lang('Kode.mhs.tanpaAkses') ?></span>
                        <?php endif ?>
                    </td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
<?php endif ?>
<?= $this->endSection() ?>
