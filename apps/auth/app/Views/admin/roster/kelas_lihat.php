<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<?php
$aktif   = count(array_filter($pendaftaran, static fn ($p) => $p['status'] === 'AKTIF'));
$dasar   = '/admin/kelas/' . (int) $kelas['id'];
$pembaca = \App\Libraries\Roster::PEMBACA;
?>
<?= view('admin/_nav') ?>
<p><a href="/admin/mata-kuliah/<?= (int) $kelas['course_id'] ?>?tahun=<?= (int) $kelas['academic_year_id'] ?>"><span class="panah-balik" aria-hidden="true"></span> <?= lang('Roster.kelas.kembali') ?></a></p>
<h1>
    <span class="ltr"><?= esc($kelas['matkul_kode']) ?></span> <?= esc($kelas['name']) ?>
    <?php if ($kelas['archived_at'] !== null): ?><span class="lencana"><?= lang('Roster.kelas.diarsipkan') ?></span><?php endif ?>
</h1>

<div class="kartu">
    <dl>
        <dt class="redup"><?= lang('Roster.matkul.judul') ?></dt>
        <dd><?= esc($kelas['matkul_nama']) ?></dd>
        <dt class="redup"><?= lang('Roster.tahun.tampilkan') ?></dt>
        <dd><?= esc($kelas['tahun_label']) ?><?= $kelas['tahun_status'] === 'archived' ? ' (' . lang('Roster.tahun.diarsipkan') . ')' : '' ?></dd>
        <dt class="redup"><?= lang('Roster.matkul.pengampu') ?></dt>
        <dd><?= esc($kelas['pengampu_nama'] ?? lang('Roster.mhs.akunHilang')) ?></dd>
        <dt class="redup"><?= lang('Roster.kelas.pengajar') ?></dt>
        <dd>
            <?php if ($pengajar === []): ?>
                <?= view('admin/roster/_pengajar', ['daftar' => $pengajar]) ?>
            <?php else: ?>
                <ul>
                <?php foreach ($pengajar as $p): ?>
                    <?php $nm = $p['nama'] ?? lang('Roster.mhs.akunHilang'); ?>
                    <li>
                        <?= esc($nm) ?>
                        <form method="post" action="<?= $dasar ?>/pengajar/hapus" class="inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="user_id" value="<?= esc($p['user_id']) ?>">
                            <button type="submit" class="tombol tombol-sekunder" aria-label="<?= esc(lang('Roster.kelas.cabutLabel', [$nm])) ?>"><?= lang('Roster.kelas.cabut') ?></button>
                        </form>
                    </li>
                <?php endforeach ?>
                </ul>
            <?php endif ?>
        </dd>
        <dt class="redup"><?= lang('Roster.kelas.kapasitas') ?></dt>
        <dd><?= $kelas['capacity'] !== null ? (int) $kelas['capacity'] : lang('Roster.kelas.tanpaBatas') ?></dd>
    </dl>
</div>

<h2><?= lang('Roster.kelas.kelola') ?></h2>
<div class="kartu">
    <form method="post" action="<?= $dasar ?>/pengajar">
        <?= csrf_field() ?>
        <label for="nomor_induk"><?= lang('Roster.kelas.nomorPengajar') ?></label>
        <input type="text" id="nomor_induk" name="nomor_induk" class="ltr" value="<?= esc($nomorPengajar) ?>" maxlength="40" required>
        <div class="baris-tombol">
            <button type="submit" class="tombol"><?= lang('Roster.kelas.tambahPengajar') ?></button>
        </div>
    </form>

    <div class="baris-tombol">
        <a class="tombol tombol-sekunder" href="<?= $dasar ?>/ubah"><?= lang('Roster.kelas.ubah') ?></a>
        <form method="post" action="<?= $dasar ?>/arsip">
            <?= csrf_field() ?>
            <input type="hidden" name="arsip" value="<?= $kelas['archived_at'] === null ? '1' : '0' ?>">
            <button type="submit" class="tombol tombol-sekunder"><?= $kelas['archived_at'] === null ? lang('Roster.kelas.arsipkan') : lang('Roster.kelas.bukaArsip') ?></button>
        </form>
    </div>
    <p class="petunjuk"><?= lang('Roster.kelas.arsipInfo') ?></p>

    <?php if ($pendaftaran === []): ?>
        <form method="post" action="<?= $dasar ?>/hapus">
            <?= csrf_field() ?>
            <label class="centang"><input type="checkbox" name="yakin" value="1" required> <?= lang('Roster.kelas.hapusYakin') ?></label>
            <div class="baris-tombol">
                <button type="submit" class="tombol tombol-bahaya"><?= lang('Roster.kelas.hapus') ?></button>
            </div>
        </form>
    <?php endif ?>
    <p class="petunjuk"><?= lang('Roster.kelas.hapusInfo') ?></p>
</div>

<?php if ($kelas['archived_at'] === null): ?>
    <h2><?= lang('Roster.daftar.judul') ?></h2>
    <div class="kartu">
        <form method="post" action="<?= $dasar ?>/daftar" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <label for="nomor"><?= lang('Roster.daftar.nomor') ?></label>
            <textarea id="nomor" name="nomor" class="ltr" rows="4"></textarea>
            <p class="petunjuk"><?= lang('Roster.daftar.nomorPetunjuk') ?></p>
            <label for="berkas"><?= lang('Roster.daftar.berkas') ?></label>
            <input type="file" id="berkas" name="berkas" accept=".csv,text/csv">
            <p class="petunjuk"><?= lang('Roster.daftar.berkasPetunjuk') ?></p>
            <div class="baris-tombol">
                <button type="submit" class="tombol"><?= lang('Roster.daftar.periksa') ?></button>
            </div>
        </form>
    </div>
<?php endif ?>

<h2><?= lang('Roster.kelas.daftar') ?></h2>
<p class="redup"><?= lang('Roster.kelas.jumlah', [count($pendaftaran), $aktif]) ?></p>
<?php if ($pendaftaran === []): ?>
    <div class="kartu"><p><?= lang('Roster.kelas.daftarKosong') ?></p></div>
<?php else: ?>
    <?php /* Satu form untuk semua tindakan pada daftar; setiap tombol membawa tujuannya sendiri (formaction).
             Tombol pertama sengaja dimatikan: menekan Enter di kolom tanggal lalu tidak mengirim form sama sekali.
             Tujuan bawaan form (mundur) tanpa pilihan tidak mengubah apa-apa. */ ?>
    <form method="post" action="<?= $dasar ?>/mundur" data-daftar-mahasiswa>
    <?= csrf_field() ?>
    <button type="submit" disabled hidden></button>
    <div class="tabel-wadah kartu">
        <table>
            <thead>
            <tr>
                <th scope="col"><input type="checkbox" data-pilih-semua aria-label="<?= lang('Roster.mhs.pilihSemua') ?>"></th>
                <th scope="col"><?= lang('Roster.mhs.nomorInduk') ?></th>
                <th scope="col"><?= lang('Roster.mhs.nama') ?></th>
                <th scope="col"><?= lang('Roster.mhs.status') ?></th>
                <th scope="col"><?= lang('Roster.mhs.catatan') ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($pendaftaran as $p): ?>
                <?php $ada = $p['nomor_induk'] !== null; ?>
                <tr>
                    <td><input type="checkbox" name="ids[]" value="<?= (int) $p['id'] ?>" aria-label="<?= lang('Roster.mhs.pilih') ?> <?= esc((string) $p['nomor_induk']) ?>"></td>
                    <td class="ltr"><?php if ($ada): ?><a href="/admin/pengguna/<?= esc($p['user_id']) ?>"><?= esc($p['nomor_induk']) ?></a><?php else: ?>—<?php endif ?></td>
                    <td><?= esc($p['nama'] ?? lang('Roster.mhs.akunHilang')) ?></td>
                    <td>
                        <?= lang('Roster.mhs.statusNama.' . $p['status']) ?>
                        <?php if ($p['status'] === 'LULUS' && $p['graduated_on'] !== null): ?>
                            <span class="redup">(<?= lang('Roster.mhs.tanggalLulus', [esc(\App\Libraries\Roster::tanggal($p['graduated_on']))]) ?>)</span>
                        <?php endif ?>
                    </td>
                    <td>
                        <?php if ($ada && $p['akun_status'] !== 'active'): ?>
                            <span class="lencana lencana-disabled"><?= lang('Roster.mhs.akunTidakAktif') ?></span>
                        <?php endif ?>
                        <?php if ($ada && isset($tanpaAkses[$p['user_id']])): ?>
                            <span class="lencana lencana-suspended"><?= lang('Roster.mhs.tanpaAkses', [esc(implode(', ', array_map(static fn ($a) => $pembaca[$a], $tanpaAkses[$p['user_id']])))]) ?></span>
                        <?php endif ?>
                    </td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
    <div class="kartu massal">
        <p class="petunjuk"><?= lang('Roster.mhs.tindakanPetunjuk') ?></p>
        <div class="baris-tombol">
            <button type="submit" class="tombol tombol-sekunder" formaction="<?= $dasar ?>/mundur"><?= lang('Roster.mhs.tandaiMundur') ?></button>
        </div>
        <p class="petunjuk"><?= lang('Roster.mhs.mundurPetunjuk') ?></p>

        <?php if ($tujuanPindah !== []): ?>
            <label for="tujuan"><?= lang('Roster.mhs.pindahKe') ?></label>
            <select id="tujuan" name="tujuan">
                <?php foreach ($tujuanPindah as $t): ?>
                    <option value="<?= (int) $t['id'] ?>"><?= esc($t['name']) ?> (<?= (int) $t['jumlah_aktif'] ?><?= $t['capacity'] !== null ? ' / ' . (int) $t['capacity'] : '' ?>)</option>
                <?php endforeach ?>
            </select>
            <div class="baris-tombol">
                <button type="submit" class="tombol tombol-sekunder" formaction="<?= $dasar ?>/pindah"><?= lang('Roster.mhs.pindahkan') ?></button>
            </div>
            <p class="petunjuk"><?= lang('Roster.mhs.pindahPetunjuk') ?></p>
        <?php endif ?>

        <label for="tanggal"><?= lang('Roster.mhs.tanggalLulusIsi') ?></label>
        <input type="date" id="tanggal" name="tanggal" class="ltr" value="<?= esc($hariIni) ?>">
        <div class="baris-tombol">
            <button type="submit" class="tombol tombol-sekunder" formaction="<?= $dasar ?>/lulus"><?= lang('Roster.mhs.luluskan') ?></button>
            <button type="submit" class="tombol tombol-sekunder" formaction="<?= $dasar ?>/lulus" name="semua" value="1"><?= lang('Roster.mhs.luluskanSemua', [$aktif]) ?></button>
            <button type="submit" class="tombol tombol-sekunder" formaction="<?= $dasar ?>/batal-lulus"><?= lang('Roster.mhs.batalLulus') ?></button>
        </div>
        <p class="petunjuk"><?= lang('Roster.mhs.lulusPetunjuk') ?></p>
    </div>
    </form>
<?php endif ?>
<?= $this->endSection() ?>
