<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<?= view('_nav', ['akses' => $akses, 'notifBelum' => $notifBelum ?? 0]) ?>
<h1><?= lang('Lokandita.halo', [esc($akses->identitas()->nama)]) ?></h1>
<?php if (service('request')->getHeaderLine(\Lokandita\Auth\Seb\Seb::HEADER_KONFIG) !== ''): ?>
    <?php // Hanya di dalam SEB: membuka alamat ini menutup SEB (quitURL konfigurasi). ?>
    <p class="baris-tombol"><a class="tombol tombol-sekunder" href="/seb/keluar" data-seb-keluar><?= lang('Seb.keluar.tautan') ?></a>
        <a href="/seb/periksa" data-seb-periksa-tautan><?= lang('Seb.periksa.judul') ?></a></p>
<?php endif ?>

<?php if ($pengumuman !== []): ?>
    <h2><?= lang('Komunikasi.pengumuman.terbaru') ?></h2>
    <?= view('_pengumuman', ['daftar' => $pengumuman, 'orang' => $penulis]) ?>
    <p><a href="/pengumuman"><?= lang('Komunikasi.pengumuman.semua') ?></a></p>
<?php endif ?>

<h2><?= lang('Latihan.saya.judul') ?></h2>
<?php if ($pertemuan === []): ?>
    <div class="kartu"><p><?= lang('Latihan.saya.kosong') ?></p></div>
<?php else: ?>
    <div class="tabel-wadah kartu">
        <table>
            <thead>
            <tr>
                <th scope="col"><?= lang('Soal.pertemuan.judul') ?></th>
                <th scope="col"><?= lang('Latihan.jendela.keadaan') ?></th>
                <th scope="col"><?= lang('Latihan.jendela.tutup') ?></th>
                <th scope="col"><?= lang('Latihan.nilai.nilai') ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($pertemuan as $p): ?>
                <tr>
                    <td>
                        <a href="/latihan/<?= (int) $p['pertemuan_id'] ?>"><span class="ltr"><?= esc($p['matkul_kode']) ?></span> — <?= lang('Soal.pertemuan.ke', [(int) $p['number']]) ?>: <?= esc($p['title']) ?></a>
                    </td>
                    <td><span class="lencana mode-<?= esc($p['mode']) ?>"><?= lang(in_array($p['jenis'], ['telat', 'latihan'], true) ? 'Latihan.jenis.' . $p['jenis'] : 'Latihan.mode.' . $p['mode']) ?></span>
                        <?php if ($p['mode'] === 'tunggu'): ?><br><small class="redup" data-sebab="<?= esc($p['sebab']) ?>"><?= $p['sebab'] === 'terjadwal' ? lang('Latihan.nilai.bukaPada', [esc(\App\Libraries\Waktu::lokal($p['open_at']))]) : lang('Latihan.sebab.' . $p['sebab'] . 'Pendek') ?></small><?php endif ?></td>
                    <td><?= esc(\App\Libraries\Waktu::lokal($p['close_at'])) ?></td>
                    <td><?= $p['score'] === null ? '—' : angka($p['score']) ?></td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
<?php endif ?>

<h2><?= lang('Kode.saya.judul') ?></h2>
<?php if ($kelas === []): ?>
    <div class="kartu"><p><?= lang('Kode.saya.kosong') ?></p></div>
<?php else: ?>
    <div class="tabel-wadah kartu">
        <table>
            <thead>
            <tr>
                <th scope="col"><?= lang('Kode.matkul.judul') ?></th>
                <th scope="col"><?= lang('Kode.kelas.nama') ?></th>
                <th scope="col"><?= lang('Kode.saya.tahun') ?></th>
                <th scope="col"><?= lang('Kode.mhs.status') ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($kelas as $k): ?>
                <tr>
                    <td><span class="ltr"><?= esc($k['matkul_kode']) ?></span> — <?= esc($k['matkul_nama']) ?></td>
                    <td><?= esc($k['kelas_nama']) ?></td>
                    <td><?= esc($k['tahun_label']) ?></td>
                    <td><?= lang('Kode.mhs.statusNama.' . $k['status']) ?></td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
    <p class="redup"><?= lang('Kode.saya.info') ?></p>
<?php endif ?>
<?= $this->endSection() ?>
