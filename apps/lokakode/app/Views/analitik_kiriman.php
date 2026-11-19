<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<?php
/** Kiriman mahasiswa satu kelas pada satu pertemuan, tanpa kode sumber. Variabel: $kelas, $j, $daftar, $saring, $orang. */
use App\Libraries\Latihan;
use App\Libraries\Ruang;
use App\Libraries\Waktu;

$alamat = '/kelas/' . (int) $kelas['id'] . '/pertemuan/' . (int) $j['pertemuan_id'];
?>
<?= view('_nav', ['akses' => $akses]) ?>
<p><a href="<?= $alamat ?>/analitik"><span class="panah-balik" aria-hidden="true"></span> <?= lang('Papan.analitik.judul') ?> — <?= lang('Soal.pertemuan.ke', [(int) $j['number']]) ?></a></p>
<h1><?= lang('Papan.kiriman.judul') ?>: <?= lang('Soal.pertemuan.ke', [(int) $j['number']]) ?> — <span class="ltr"><?= esc($kelas['matkul_kode']) ?></span> <?= esc($kelas['name']) ?></h1>
<?php if ($saring): ?><p><a href="<?= $alamat ?>/kiriman"><?= lang('Papan.kiriman.semua') ?></a></p><?php endif ?>

<?php if ($daftar === []): ?>
    <div class="kartu"><p><?= lang('Papan.kiriman.kosong') ?></p></div>
<?php else: ?>
    <div class="tabel-wadah kartu">
        <table data-kiriman>
            <thead>
            <tr>
                <th scope="col"><?= lang('Papan.kiriman.waktu') ?></th>
                <th scope="col"><?= lang('Papan.nama') ?></th>
                <th scope="col"><?= lang('Papan.soal.soal') ?></th>
                <th scope="col"><?= lang('Papan.kiriman.hasil') ?></th>
                <th scope="col"><?= lang('Papan.nilai') ?></th>
                <th scope="col"><?= lang('Papan.kiriman.tes') ?></th>
                <th scope="col"><?= lang('Papan.kiriman.sumber') ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($daftar as $k): ?>
                <?php $vonis = $k['status'] === 'GAGAL' ? 'FAILED' : (string) $k['verdict']; ?>
                <tr>
                    <td class="ltr"><?= esc(Waktu::lokal($k['created_at'])) ?></td>
                    <td><a href="<?= $alamat ?>/kiriman?mahasiswa=<?= esc($k['user_id']) ?>"><?= esc($orang[$k['user_id']]['nama'] ?? '—') ?></a></td>
                    <td><?= esc(Ruang::terjemahan(json_decode((string) $k['translations'], true) ?? [])['title'] ?? $k['slug']) ?></td>
                    <td>
                        <?php if ($k['status'] === 'MENUNGGU'): ?>
                            <span class="lencana"><?= lang('Papan.kiriman.menunggu') ?></span>
                        <?php else: ?>
                            <span class="lencana verdict-<?= esc(strtolower($vonis)) ?> ltr"><?= esc($vonis) ?></span>
                        <?php endif ?>
                        <?php if ((int) $k['late'] === 1): ?><span class="lencana jenis-telat"><?= lang('Latihan.jenis.telat') ?></span><?php endif ?>
                        <?php if ((int) $k['practice'] === 1): ?><span class="lencana jenis-latihan"><?= lang('Latihan.jenis.latihan') ?></span><?php endif ?>
                    </td>
                    <td class="ltr"><?= $k['score'] === null ? '—' : angka($k['score']) . ' / ' . Latihan::POIN[$k['difficulty']] ?></td>
                    <td class="ltr"><?= (int) $k['total'] > 0 ? (int) $k['passed'] . ' / ' . (int) $k['total'] : '—' ?></td>
                    <td><a href="/kelas/<?= (int) $kelas['id'] ?>/kiriman/<?= (int) $k['id'] ?>"><?= lang('Papan.kiriman.lihat') ?></a></td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
    <p class="petunjuk"><?= lang('Papan.kiriman.info') ?></p>
<?php endif ?>
<?= $this->endSection() ?>
