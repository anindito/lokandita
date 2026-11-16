<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<?php
use App\Libraries\Soal\Impor;
use App\Libraries\Waktu;

$h = $lap['hitung'];
?>
<?= view('_nav', ['akses' => $akses]) ?>
<h1><?= lang('Soal.impor.laporan') ?></h1>
<p class="redup">
    <span class="ltr"><?= esc($lap['file_name']) ?></span> · <?= lang('Soal.impor.sumber.' . $lap['source']) ?>
    · <?= esc($orang[$lap['actor_id']]['nama'] ?? lang('Kode.mhs.akunHilang')) ?> · <?= esc(Waktu::lokal($lap['created_at'])) ?>
    <?php if ($lap['meeting_id'] !== null): ?>
        · <a href="/pertemuan/<?= (int) $lap['meeting_id'] ?>"><span class="ltr"><?= esc($lap['matkul_kode']) ?></span> — <?= lang('Soal.pertemuan.ke', [(int) $lap['pertemuan_nomor']]) ?></a>
    <?php endif ?>
</p>

<?php if ($lap['status'] === 'DITOLAK'): ?>
    <div class="kartu">
        <p><strong><?= lang('Soal.impor.berkasDitolak') ?></strong></p>
        <ul class="daftar-alasan">
            <?php foreach ($lap['file_errors'] as $g): ?>
                <li><?php if ($g['field'] !== ''): ?><span class="letak ltr"><?= esc($g['field']) ?>:</span> <?php endif ?><?= esc(lang('Soal.skema.' . $g['kode'], $g['argumen'])) ?></li>
            <?php endforeach ?>
        </ul>
    </div>
<?php else: ?>
    <div class="kartu" role="status">
        <?php if ($lap['status'] === 'MENUNGGU'): ?>
            <p><strong><?= lang('Soal.impor.sedang', [(string) ($h['total'] - $h['waiting']), (string) $h['total']]) ?></strong></p>
            <progress max="<?= (int) $h['total'] ?>" value="<?= (int) ($h['total'] - $h['waiting']) ?>"></progress>
            <p class="petunjuk"><?= lang('Soal.impor.sedangInfo') ?> <a href="/impor/<?= esc($lap['id']) ?>"><?= lang('Soal.impor.muatUlang') ?></a></p>
        <?php else: ?>
            <p><strong><?= lang('Soal.impor.selesai', [(string) $h['accepted'], (string) $h['rejected'], (string) $h['unchanged'], (string) $h['total']]) ?></strong></p>
            <?php if ($h['accepted'] > 0): ?><p class="petunjuk"><?= lang('Soal.impor.selesaiInfo') ?></p><?php endif ?>
        <?php endif ?>
    </div>

    <div class="tabel-wadah kartu">
        <table>
            <thead>
            <tr>
                <th scope="col"><?= lang('Soal.pertemuan.nomor') ?></th>
                <th scope="col"><?= lang('Soal.soal.judul') ?></th>
                <th scope="col"><?= lang('Soal.impor.hasil') ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($lap['butir'] as $b): ?>
                <tr>
                    <td><?= (int) $b['urutan'] ?></td>
                    <td>
                        <?php if ($b['problem_id'] !== null): ?>
                            <a class="ltr" href="/soal/<?= (int) $b['problem_id'] ?>?versi=<?= (int) $b['version'] ?>"><?= esc($b['slug']) ?></a>
                        <?php else: ?>
                            <span class="ltr"><?= esc($b['slug'] ?? '—') ?></span>
                        <?php endif ?>
                        <?php if ($b['json_line'] !== null): ?><span class="redup"><?= lang('Soal.letak.barisJson', [(string) (int) $b['json_line']]) ?></span><?php endif ?>
                    </td>
                    <td>
                        <span class="lencana keadaan-<?= esc(strtolower($b['state'])) ?>"><?= lang('Soal.impor.keadaan.' . $b['state']) ?></span>
                        <?php if ($b['state'] === 'DITERIMA'): ?><?= lang('Soal.impor.versiBaru', [(string) (int) $b['version']]) ?><?php endif ?>
                        <?php if ($b['state'] === 'MENUNGGU' && is_array($b['progress']) && ($b['progress']['total'] ?? 0) > 0): ?>
                            <span class="redup"><?= lang('Soal.impor.langkah', [(string) (int) $b['progress']['current'], (string) (int) $b['progress']['total']]) ?></span>
                        <?php endif ?>
                        <?php if ($b['reasons'] !== []): ?>
                            <ul class="daftar-alasan">
                                <?php foreach ($b['reasons'] as $a): ?>
                                    <?php $letak = Impor::letak($a); ?>
                                    <li><?php if ($letak !== ''): ?><span class="letak"><?= esc($letak) ?>:</span> <?php endif ?><?= esc(Impor::kalimat($a)) ?></li>
                                <?php endforeach ?>
                            </ul>
                        <?php endif ?>
                    </td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
<?php endif ?>
<?php if ($akses->superadmin()): ?>
    <p><a href="/impor"><?= lang('Soal.impor.kembali') ?></a></p>
<?php endif ?>
<?= $this->endSection() ?>
