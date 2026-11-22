<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<?php
/**
 * Peristiwa integritas satu kelas (SPEC.md §13.2). Variabel: $kelas, $baris, $saring, $query,
 * $halaman, $lagi, $orang (mahasiswa kelas, dikunci user id), $pertemuan.
 */
use App\Libraries\Integritas;
use App\Libraries\Waktu;

$alamat = '/kelas/' . (int) $kelas['id'] . '/integritas';
$tanya  = $query === '' ? '' : '?' . $query;
$ke     = static fn (int $h): string => $alamat . '?' . ($query === '' ? '' : $query . '&') . 'halaman=' . $h;
?>
<?= view('_nav', ['akses' => $akses]) ?>
<p><a href="/kelas/<?= (int) $kelas['id'] ?>"><span class="panah-balik" aria-hidden="true"></span> <span class="ltr"><?= esc($kelas['matkul_kode']) ?></span> <?= esc($kelas['name']) ?></a></p>
<h1><?= lang('Integritas.judul') ?>: <span class="ltr"><?= esc($kelas['matkul_kode']) ?></span> <?= esc($kelas['name']) ?></h1>
<p class="redup"><?= lang('Integritas.penjelasan') ?></p>

<form class="saring" method="get" action="<?= $alamat ?>">
    <div>
        <label for="jenis"><?= lang('Integritas.jenisJudul') ?></label>
        <select id="jenis" name="jenis">
            <option value=""><?= lang('Integritas.semua') ?></option>
            <?php foreach (Integritas::JENIS as $j): ?>
                <option value="<?= $j ?>" <?= $saring['jenis'] === $j ? 'selected' : '' ?>><?= lang('Integritas.jenis.' . $j) ?></option>
            <?php endforeach ?>
        </select>
    </div>
    <div>
        <label for="mahasiswa"><?= lang('Integritas.mahasiswa') ?></label>
        <select id="mahasiswa" name="mahasiswa">
            <option value=""><?= lang('Integritas.semua') ?></option>
            <?php foreach ($orang as $id => $o): ?>
                <option value="<?= esc($id) ?>" <?= $saring['mahasiswa'] === $id ? 'selected' : '' ?>><?= esc($o['nama']) ?> (<?= esc($o['nomor_induk']) ?>)</option>
            <?php endforeach ?>
        </select>
    </div>
    <div>
        <label for="pertemuan"><?= lang('Integritas.pertemuan') ?></label>
        <select id="pertemuan" name="pertemuan">
            <option value=""><?= lang('Integritas.semua') ?></option>
            <?php foreach ($pertemuan as $p): ?>
                <option value="<?= (int) $p['id'] ?>" <?= $saring['pertemuan'] === (int) $p['id'] ? 'selected' : '' ?>><?= (int) $p['number'] ?> — <?= esc($p['title']) ?></option>
            <?php endforeach ?>
        </select>
    </div>
    <div><label for="dari"><?= lang('Kode.audit.dari') ?></label><input type="date" id="dari" name="dari" class="ltr" value="<?= esc((string) $saring['dari']) ?>"></div>
    <div><label for="sampai"><?= lang('Kode.audit.sampai') ?></label><input type="date" id="sampai" name="sampai" class="ltr" value="<?= esc((string) $saring['sampai']) ?>"></div>
    <div><button type="submit" class="tombol"><?= lang('Kode.tahun.lihat') ?></button></div>
</form>

<?php if ($baris === []): ?>
    <div class="kartu"><p><?= lang($query === '' ? 'Integritas.kosong' : 'Integritas.kosongSaring') ?></p></div>
<?php else: ?>
    <p class="baris-tombol">
        <a class="tombol tombol-sekunder" href="<?= $alamat ?>/ekspor/csv<?= esc($tanya) ?>"><?= lang('Integritas.unduhCsv') ?></a>
        <a class="tombol tombol-sekunder" href="<?= $alamat ?>/ekspor/xlsx<?= esc($tanya) ?>"><?= lang('Integritas.unduhXlsx') ?></a>
    </p>
    <div class="tabel-wadah kartu">
        <table data-integritas>
            <thead>
            <tr>
                <th scope="col"><?= lang('Integritas.waktu') ?></th>
                <th scope="col"><?= lang('Papan.nama') ?></th>
                <th scope="col"><?= lang('Integritas.jenisJudul') ?></th>
                <th scope="col"><?= lang('Integritas.soal') ?></th>
                <th scope="col"><?= lang('Integritas.rincian') ?></th>
                <th scope="col"><?= lang('Integritas.ip') ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($baris as $b): ?>
                <?php $o = $orang[$b['user_id']] ?? null; ?>
                <tr data-jenis="<?= esc($b['kind']) ?>">
                    <td class="ltr"><?= esc(Waktu::lokal($b['created_at'])) ?></td>
                    <td><?= esc($o['nama'] ?? lang('Kode.mhs.akunHilang')) ?><br><small class="ltr"><?= esc($o['nomor_induk'] ?? '') ?></small></td>
                    <td><span class="lencana"><?= lang('Integritas.jenis.' . $b['kind']) ?></span></td>
                    <td>
                        <?php if ($b['meeting_problem_id'] === null): ?>—<?php else: ?>
                            <?= lang('Soal.pertemuan.ke', [(int) $b['number']]) ?>: <?= esc(Integritas::soal($b)) ?>
                        <?php endif ?>
                    </td>
                    <td><?= esc(Integritas::uraian((string) $b['kind'], $b['detail'])) ?></td>
                    <td class="ltr text-sm" title="<?= esc((string) $b['user_agent']) ?>"><?= esc((string) $b['ip']) ?></td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
    <nav class="halaman" aria-label="<?= lang('Kode.audit.halaman') ?>">
        <?php if ($halaman > 1): ?><a class="tombol tombol-sekunder" href="<?= esc($ke($halaman - 1)) ?>" rel="prev"><?= lang('Kode.audit.sebelumnya') ?></a><?php endif ?>
        <span><?= lang('Integritas.halamanKe', [$halaman]) ?></span>
        <?php if ($lagi): ?><a class="tombol tombol-sekunder" href="<?= esc($ke($halaman + 1)) ?>" rel="next"><?= lang('Kode.audit.berikutnya') ?></a><?php endif ?>
    </nav>
<?php endif ?>
<p class="petunjuk"><?= lang('Integritas.catatan') ?></p>
<?= $this->endSection() ?>
