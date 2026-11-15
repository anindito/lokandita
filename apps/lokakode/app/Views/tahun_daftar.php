<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<?= view('_nav', ['akses' => $akses]) ?>
<h1><?= lang('Kode.nav.tahun') ?></h1>
<p class="redup"><?= lang('Kode.tahun.aktifkanInfo') ?></p>
<p><a class="tombol" href="/tahun-ajaran/baru"><?= lang('Kode.tahunBaru.tombol') ?></a></p>

<?php if ($tahun === []): ?>
    <div class="kartu"><p><?= lang('Kode.tahun.kosong') ?></p></div>
<?php else: ?>
    <div class="tabel-wadah kartu">
        <table>
            <thead>
            <tr>
                <th scope="col"><?= lang('Kode.tahun.nama') ?></th>
                <th scope="col"><?= lang('Kode.tahun.mulai') ?></th>
                <th scope="col"><?= lang('Kode.tahun.selesai') ?></th>
                <th scope="col"><?= lang('Kode.tahun.jumlahKelas') ?></th>
                <th scope="col"><?= lang('Kode.tahun.status') ?></th>
                <th scope="col"><span class="sr-only"><?= lang('Kode.aksi.ubah') ?></span></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($tahun as $t): ?>
                <tr>
                    <td><a href="/tahun-ajaran/<?= (int) $t['id'] ?>"><?= esc($t['label']) ?></a></td>
                    <td><?= esc(\App\Libraries\Waktu::tanggal($t['starts_on'])) ?></td>
                    <td><?= esc(\App\Libraries\Waktu::tanggal($t['ends_on'])) ?></td>
                    <td><?= $kelas[(int) $t['id']] ?? 0 ?></td>
                    <td><span class="lencana <?= $t['status'] === 'active' ? 'lencana-active' : '' ?>"><?= lang('Kode.tahun.statusNama.' . $t['status']) ?></span></td>
                    <td>
                        <?php if ($t['status'] !== 'active'): ?>
                            <form method="post" action="/tahun-ajaran/<?= (int) $t['id'] ?>/aktifkan">
                                <?= csrf_field() ?>
                                <button type="submit" class="tombol tombol-sekunder"><?= lang('Kode.tahun.aktifkan') ?></button>
                            </form>
                        <?php endif ?>
                    </td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
<?php endif ?>

<h2><?= lang('Kode.tahun.tambah') ?></h2>
<div class="kartu">
    <form method="post" action="/tahun-ajaran">
        <?= csrf_field() ?>
        <?= view('_tahun_kolom', ['isi' => $isi, 'galat' => $galat]) ?>
        <p class="petunjuk"><?= lang('Kode.tahun.baruArsip') ?></p>
        <div class="baris-tombol">
            <button type="submit" class="tombol"><?= lang('Kode.aksi.simpan') ?></button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
