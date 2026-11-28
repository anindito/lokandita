<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<?= view('admin/_nav') ?>
<h1><?= lang('Roster.nav.tahun') ?></h1>
<p class="redup"><?= lang('Roster.tahun.aktifkanInfo') ?></p>
<p><a class="tombol" href="/admin/tahun-ajaran/baru"><?= lang('Roster.tahunBaru.tombol') ?></a></p>

<?php if ($tahun === []): ?>
    <div class="kartu"><p><?= lang('Roster.tahun.kosong') ?></p></div>
<?php else: ?>
    <div class="tabel-wadah kartu">
        <table>
            <thead>
            <tr>
                <th scope="col"><?= lang('Roster.tahun.nama') ?></th>
                <th scope="col"><?= lang('Roster.tahun.mulai') ?></th>
                <th scope="col"><?= lang('Roster.tahun.selesai') ?></th>
                <th scope="col"><?= lang('Roster.tahun.jumlahKelas') ?></th>
                <th scope="col"><?= lang('Roster.tahun.status') ?></th>
                <th scope="col"><span class="sr-only"><?= lang('Roster.aksi.ubah') ?></span></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($tahun as $t): ?>
                <tr>
                    <td><a href="/admin/tahun-ajaran/<?= (int) $t['id'] ?>"><?= esc($t['label']) ?></a></td>
                    <td><?= esc(\App\Libraries\Roster::tanggal($t['starts_on'])) ?></td>
                    <td><?= esc(\App\Libraries\Roster::tanggal($t['ends_on'])) ?></td>
                    <td><?= $kelas[(int) $t['id']] ?? 0 ?></td>
                    <td><span class="lencana <?= $t['status'] === 'active' ? 'lencana-active' : '' ?>"><?= lang('Roster.tahun.statusNama.' . $t['status']) ?></span></td>
                    <td>
                        <?php if ($t['status'] !== 'active'): ?>
                            <form method="post" action="/admin/tahun-ajaran/<?= (int) $t['id'] ?>/aktifkan">
                                <?= csrf_field() ?>
                                <button type="submit" class="tombol tombol-sekunder"><?= lang('Roster.tahun.aktifkan') ?></button>
                            </form>
                        <?php endif ?>
                    </td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
<?php endif ?>

<h2><?= lang('Roster.tahun.tambah') ?></h2>
<div class="kartu">
    <form method="post" action="/admin/tahun-ajaran">
        <?= csrf_field() ?>
        <?= view('admin/roster/_tahun_kolom', ['isi' => $isi, 'galat' => $galat]) ?>
        <p class="petunjuk"><?= lang('Roster.tahun.baruArsip') ?></p>
        <div class="baris-tombol">
            <button type="submit" class="tombol"><?= lang('Roster.aksi.simpan') ?></button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
