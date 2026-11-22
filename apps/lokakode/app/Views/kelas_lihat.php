<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<?php
$aktif = count(array_filter($pendaftaran, static fn ($p) => $p['status'] === 'AKTIF'));
?>
<?= view('_nav', ['akses' => $akses]) ?>
<p><a href="/mata-kuliah/<?= (int) $kelas['course_id'] ?>?tahun=<?= (int) $kelas['academic_year_id'] ?>"><span class="panah-balik" aria-hidden="true"></span> <?= lang('Kode.kelas.kembali') ?></a></p>
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
        <dd>
            <?php if (! $bolehUbah || $pengajar === []): ?>
                <?= view('_pengajar', ['daftar' => $pengajar, 'orang' => $orang]) ?>
            <?php else: ?>
                <ul>
                <?php foreach ($pengajar as $pid): ?>
                    <?php $nm = $orang[$pid]['nama'] ?? lang('Kode.mhs.akunHilang'); ?>
                    <li>
                        <?= esc($nm) ?>
                        <form method="post" action="/kelas/<?= (int) $kelas['id'] ?>/pengajar/hapus" class="inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="user_id" value="<?= esc($pid) ?>">
                            <button type="submit" class="tombol tombol-sekunder" aria-label="<?= esc(lang('Kode.kelas.cabutLabel', [$nm])) ?>"><?= lang('Kode.kelas.cabut') ?></button>
                        </form>
                    </li>
                <?php endforeach ?>
                </ul>
            <?php endif ?>
        </dd>
        <dt class="redup"><?= lang('Kode.kelas.kapasitas') ?></dt>
        <dd><?= $kelas['capacity'] !== null ? (int) $kelas['capacity'] : lang('Kode.kelas.tanpaBatas') ?></dd>
    </dl>
    <p><a class="tombol tombol-sekunder" href="/kelas/<?= (int) $kelas['id'] ?>/pertemuan"><?= lang('Latihan.jendela.judul') ?></a>
        <a class="tombol tombol-sekunder" href="/kelas/<?= (int) $kelas['id'] ?>/analitik"><?= lang('Papan.analitik.judul') ?></a>
        <a class="tombol tombol-sekunder" href="/kelas/<?= (int) $kelas['id'] ?>/integritas"><?= lang('Integritas.judul') ?></a></p>
    <?php if ((int) $kelas['boleh_kelola'] !== 1): ?>
        <p class="petunjuk"><?= lang('Kode.kelas.hanyaLihat') ?></p>
    <?php elseif (! $bolehUbah): ?>
        <p class="petunjuk"><?= lang('Kode.kelas.tahunArsip') ?></p>
    <?php endif ?>
</div>

<?php if ($bolehUbah): ?>
    <h2><?= lang('Kode.kelas.kelola') ?></h2>
    <div class="kartu">
        <form method="post" action="/kelas/<?= (int) $kelas['id'] ?>/pengajar">
            <?= csrf_field() ?>
            <label for="nomor_induk"><?= lang('Kode.kelas.nomorPengajar') ?></label>
            <input type="text" id="nomor_induk" name="nomor_induk" class="ltr" value="<?= esc($nomorPengajar) ?>" maxlength="40" required>
            <div class="baris-tombol">
                <button type="submit" class="tombol"><?= lang('Kode.kelas.tambahPengajar') ?></button>
            </div>
        </form>

        <div class="baris-tombol">
            <a class="tombol tombol-sekunder" href="/kelas/<?= (int) $kelas['id'] ?>/ubah"><?= lang('Kode.kelas.ubah') ?></a>
            <form method="post" action="/kelas/<?= (int) $kelas['id'] ?>/arsip">
                <?= csrf_field() ?>
                <input type="hidden" name="arsip" value="<?= $kelas['archived_at'] === null ? '1' : '0' ?>">
                <button type="submit" class="tombol tombol-sekunder"><?= $kelas['archived_at'] === null ? lang('Kode.kelas.arsipkan') : lang('Kode.kelas.bukaArsip') ?></button>
            </form>
        </div>
        <p class="petunjuk"><?= lang('Kode.kelas.arsipInfo') ?></p>

        <?php if ($pendaftaran === []): ?>
            <form method="post" action="/kelas/<?= (int) $kelas['id'] ?>/hapus">
                <?= csrf_field() ?>
                <label class="centang"><input type="checkbox" name="yakin" value="1" required> <?= lang('Kode.kelas.hapusYakin') ?></label>
                <div class="baris-tombol">
                    <button type="submit" class="tombol tombol-bahaya"><?= lang('Kode.kelas.hapus') ?></button>
                </div>
            </form>
        <?php endif ?>
        <p class="petunjuk"><?= lang('Kode.kelas.hapusInfo') ?></p>
    </div>
<?php endif ?>

<?php if ($bolehUbah && $kelas['archived_at'] === null): ?>
    <h2><?= lang('Kode.daftar.judul') ?></h2>
    <div class="kartu">
        <form method="post" action="/kelas/<?= (int) $kelas['id'] ?>/daftar" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <label for="nomor"><?= lang('Kode.daftar.nomor') ?></label>
            <textarea id="nomor" name="nomor" class="ltr" rows="4"></textarea>
            <p class="petunjuk"><?= lang('Kode.daftar.nomorPetunjuk') ?></p>
            <label for="berkas"><?= lang('Kode.daftar.berkas') ?></label>
            <input type="file" id="berkas" name="berkas" accept=".csv,text/csv">
            <p class="petunjuk"><?= lang('Kode.daftar.berkasPetunjuk') ?></p>
            <div class="baris-tombol">
                <button type="submit" class="tombol"><?= lang('Kode.daftar.periksa') ?></button>
            </div>
        </form>
    </div>
<?php endif ?>

<h2><?= lang('Kode.kelas.daftar') ?></h2>
<p class="redup"><?= lang('Kode.kelas.jumlah', [count($pendaftaran), $aktif]) ?></p>
<?php if ($pendaftaran === []): ?>
    <div class="kartu"><p><?= lang('Kode.kelas.daftarKosong') ?></p></div>
<?php else: ?>
    <?php /* Satu form untuk semua tindakan pada daftar; setiap tombol membawa tujuannya sendiri (formaction).
             Tombol pertama sengaja dimatikan: menurut aturan HTML, menekan Enter di kolom tanggal lalu tidak
             mengirim form sama sekali. Tujuan bawaan form pun tidak mengubah apa-apa (tanpa user_id dijawab 404). */ ?>
    <form method="post" action="/kelas/<?= (int) $kelas['id'] ?>/reset-sandi" data-daftar-mahasiswa>
    <?= csrf_field() ?>
    <button type="submit" disabled hidden></button>
    <div class="tabel-wadah kartu">
        <table>
            <thead>
            <tr>
                <?php if ($bolehUbah): ?>
                    <th scope="col"><input type="checkbox" data-pilih-semua aria-label="<?= lang('Kode.mhs.pilihSemua') ?>"></th>
                <?php endif ?>
                <th scope="col"><?= lang('Kode.mhs.nomorInduk') ?></th>
                <th scope="col"><?= lang('Kode.mhs.nama') ?></th>
                <th scope="col"><?= lang('Kode.mhs.status') ?></th>
                <th scope="col"><?= lang('Kode.mhs.catatan') ?></th>
                <th scope="col"><?= lang('Kode.mhs.tindakan') ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($pendaftaran as $p): ?>
                <?php $o = $orang[$p['user_id']] ?? null; ?>
                <tr>
                    <?php if ($bolehUbah): ?>
                        <td><input type="checkbox" name="ids[]" value="<?= (int) $p['id'] ?>" aria-label="<?= lang('Kode.mhs.pilih') ?> <?= esc($o['nomor_induk'] ?? '') ?>"></td>
                    <?php endif ?>
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
                    <td>
                        <?php if ($o !== null && $p['status'] === 'AKTIF'): ?>
                            <button type="submit" class="tombol tombol-sekunder" formaction="/kelas/<?= (int) $kelas['id'] ?>/reset-sandi" name="user_id" value="<?= esc($p['user_id']) ?>" aria-label="<?= esc(lang('Kode.mhs.resetLabel', [$o['nama']])) ?>"><?= lang('Kode.mhs.resetSandi') ?></button>
                        <?php endif ?>
                    </td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
    <?php if ($bolehUbah): ?>
        <div class="kartu massal">
            <p class="petunjuk"><?= lang('Kode.mhs.tindakanPetunjuk') ?></p>
            <div class="baris-tombol">
                <button type="submit" class="tombol tombol-sekunder" formaction="/kelas/<?= (int) $kelas['id'] ?>/mundur"><?= lang('Kode.mhs.tandaiMundur') ?></button>
            </div>
            <p class="petunjuk"><?= lang('Kode.mhs.mundurPetunjuk') ?></p>

            <?php if ($tujuanPindah !== []): ?>
                <label for="tujuan"><?= lang('Kode.mhs.pindahKe') ?></label>
                <select id="tujuan" name="tujuan">
                    <?php foreach ($tujuanPindah as $t): ?>
                        <option value="<?= (int) $t['id'] ?>"><?= esc($t['name']) ?> (<?= (int) $t['jumlah_aktif'] ?><?= $t['capacity'] !== null ? ' / ' . (int) $t['capacity'] : '' ?>)</option>
                    <?php endforeach ?>
                </select>
                <div class="baris-tombol">
                    <button type="submit" class="tombol tombol-sekunder" formaction="/kelas/<?= (int) $kelas['id'] ?>/pindah"><?= lang('Kode.mhs.pindahkan') ?></button>
                </div>
                <p class="petunjuk"><?= lang('Kode.mhs.pindahPetunjuk') ?></p>
            <?php endif ?>

            <label for="tanggal"><?= lang('Kode.mhs.tanggalLulusIsi') ?></label>
            <input type="date" id="tanggal" name="tanggal" class="ltr" value="<?= esc($hariIni) ?>">
            <div class="baris-tombol">
                <button type="submit" class="tombol tombol-sekunder" formaction="/kelas/<?= (int) $kelas['id'] ?>/lulus"><?= lang('Kode.mhs.luluskan') ?></button>
                <button type="submit" class="tombol tombol-sekunder" formaction="/kelas/<?= (int) $kelas['id'] ?>/lulus" name="semua" value="1"><?= lang('Kode.mhs.luluskanSemua', [$aktif]) ?></button>
                <button type="submit" class="tombol tombol-sekunder" formaction="/kelas/<?= (int) $kelas['id'] ?>/batal-lulus"><?= lang('Kode.mhs.batalLulus') ?></button>
            </div>
            <p class="petunjuk"><?= lang('Kode.mhs.lulusPetunjuk') ?></p>
        </div>
    <?php endif ?>
    </form>
<?php endif ?>
<?= $this->endSection() ?>
