<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<?= view('_nav', ['akses' => $akses]) ?>
<h1><?= lang('Kode.tahunBaru.judul') ?></h1>

<?php if ($lama === null): ?>
    <div class="kartu">
        <p><?= lang('Kode.tahunBaru.tidakAdaAktif') ?></p>
        <div class="baris-tombol"><a class="tombol tombol-sekunder" href="/tahun-ajaran"><?= lang('Kode.nav.tahun') ?></a></div>
    </div>
<?php elseif ($ringkasan === null): ?>
    <p><?= lang('Kode.tahunBaru.penjelasan') ?></p>
    <div class="kartu">
        <p class="redup"><?= lang('Kode.tahunBaru.dari') ?></p>
        <p class="text-xl font-bold"><?= esc($lama['label']) ?></p>
        <form method="post" action="/tahun-ajaran/baru">
            <?= csrf_field() ?>
            <?= view('_tahun_kolom', ['isi' => $isi, 'galat' => $galat]) ?>
            <div class="baris-tombol">
                <button type="submit" class="tombol"><?= lang('Kode.tahunBaru.lihatRingkasan') ?></button>
                <a class="tombol tombol-sekunder" href="/tahun-ajaran"><?= lang('Kode.aksi.batal') ?></a>
            </div>
        </form>
    </div>
<?php else: ?>
    <h2><?= lang('Kode.tahunBaru.ringkasan') ?></h2>
    <div class="kartu">
        <dl>
            <dt class="redup"><?= lang('Kode.tahunBaru.baru') ?></dt>
            <dd><strong><?= esc($isi['label']) ?></strong>, <?= esc(\App\Libraries\Waktu::tanggal($isi['starts_on'])) ?> – <?= esc(\App\Libraries\Waktu::tanggal($isi['ends_on'])) ?></dd>
            <dt class="redup"><?= lang('Kode.tahunBaru.dari') ?></dt>
            <dd><?= esc($lama['label']) ?></dd>
        </dl>
        <p data-ringkas="salin"><?= lang('Kode.tahunBaru.akanDisalin', [$ringkasan['kelas'], $ringkasan['pengajar']]) ?></p>
        <?php if ($ringkasan['diarsipkan'] > 0): ?>
            <p class="redup"><?= lang('Kode.tahunBaru.tidakDisalin', [$ringkasan['diarsipkan']]) ?></p>
        <?php endif ?>
    </div>

    <?php if ($ringkasan['masihAktif'] > 0): ?>
        <div class="kartu" role="alert" data-ringkas="masih-aktif">
            <p><?= lang('Kode.tahunBaru.masihAktif', [$ringkasan['masihAktif'], esc($lama['label'])]) ?></p>
        </div>
    <?php endif ?>

    <?php if ($ringkasan['baris'] !== []): ?>
        <div class="tabel-wadah kartu">
            <table>
                <thead>
                <tr>
                    <th scope="col"><?= lang('Kode.matkul.judul') ?></th>
                    <th scope="col"><?= lang('Kode.tahunBaru.kelas') ?></th>
                    <th scope="col"><?= lang('Kode.tahunBaru.pengajar') ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($ringkasan['baris'] as $b): ?>
                    <tr>
                        <td><span class="ltr"><?= esc($b['code']) ?></span> — <?= esc($b['name']) ?></td>
                        <td><?= (int) $b['kelas'] ?></td>
                        <td><?= (int) $b['pengajar'] ?></td>
                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>
        </div>
    <?php endif ?>

    <div class="baris-tombol">
        <form method="post" action="/tahun-ajaran/baru/jalankan">
            <?= csrf_field() ?>
            <?php foreach ($isi as $k => $v): ?><input type="hidden" name="<?= esc($k) ?>" value="<?= esc($v) ?>"><?php endforeach ?>
            <button type="submit" class="tombol"><?= lang('Kode.tahunBaru.jalankan', [esc($isi['label']), esc($lama['label'])]) ?></button>
        </form>
        <a class="tombol tombol-sekunder" href="/tahun-ajaran/baru"><?= lang('Kode.tahunBaru.ubahIsian') ?></a>
    </div>
<?php endif ?>
<?= $this->endSection() ?>
