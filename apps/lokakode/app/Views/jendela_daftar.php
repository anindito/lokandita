<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<?php
use App\Libraries\Latihan;
use App\Libraries\Waktu;
?>
<?= view('_nav', ['akses' => $akses]) ?>
<p><a href="/kelas/<?= (int) $kelas['id'] ?>">&larr; <span class="ltr"><?= esc($kelas['matkul_kode']) ?></span> <?= esc($kelas['name']) ?></a></p>
<h1><?= lang('Latihan.jendela.judul') ?></h1>
<p class="redup"><?= lang('Latihan.jendela.info') ?></p>

<?php if ($pertemuan === []): ?>
    <div class="kartu"><p><?= lang('Latihan.jendela.kosong') ?></p></div>
<?php else: ?>
    <div class="tabel-wadah kartu">
        <table>
            <thead>
            <tr>
                <th scope="col"><?= lang('Soal.pertemuan.judul') ?></th>
                <th scope="col"><?= lang('Latihan.jendela.keadaan') ?></th>
                <th scope="col"><?= lang('Latihan.jendela.buka') ?></th>
                <th scope="col"><?= lang('Latihan.jendela.tutup') ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($pertemuan as $j): ?>
                <?php $keadaan = Latihan::keadaan($j); ?>
                <tr>
                    <td>
                        <a href="/kelas/<?= (int) $kelas['id'] ?>/pertemuan/<?= (int) $j['pertemuan_id'] ?>"><?= lang('Soal.pertemuan.ke', [(int) $j['number']]) ?>: <?= esc($j['title']) ?></a>
                        <?php if ($j['published_at'] === null): ?><span class="lencana"><?= lang('Latihan.terbit.belum') ?></span><?php endif ?>
                    </td>
                    <td>
                        <span class="lencana keadaan-<?= $keadaan ?>"><?= lang('Latihan.keadaan.' . $keadaan) ?></span>
                        <?php if (in_array($j['override'], ['open', 'closed'], true)): ?><span class="redup"><?= lang('Latihan.jendela.manual') ?></span><?php endif ?>
                    </td>
                    <td><?= esc(Waktu::lokal($j['open_at'])) ?></td>
                    <td><?= esc(Waktu::lokal($j['close_at'])) ?></td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
<?php endif ?>
<?= $this->endSection() ?>
