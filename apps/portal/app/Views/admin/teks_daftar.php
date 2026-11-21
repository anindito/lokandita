<?php
/**
 * Daftar teks halaman publik: enam halaman × tiga bahasa.
 * Variabel: $halaman, $bahasa, $baris (bahasa => halaman => baris pengganti), $orang.
 */
use App\Controllers\Admin\Teks;

?>
<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<p><a class="panah-balik" href="/beranda"><?= lang('Lokandita.beranda') ?></a></p>
<h1><?= lang('Sunting.judul') ?></h1>
<p><?= lang('Sunting.pengantar') ?></p>

<div class="tabel-wadah kartu">
    <table>
        <thead>
        <tr>
            <th scope="col"><?= lang('Sunting.kolomHalaman') ?></th>
            <?php foreach ($bahasa as $b): ?>
                <th scope="col" lang="<?= $b ?>"><?= lang('Lokandita.bahasa' . ucfirst($b)) ?></th>
            <?php endforeach ?>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($halaman as $h): ?>
            <tr>
                <th scope="row"><?= lang('Sunting.halaman.' . $h) ?></th>
                <?php foreach ($bahasa as $b): ?>
                    <?php
                    $r    = $baris[$b][$h] ?? null;
                    $asal = $baris['id'][$h] ?? null;
                    // Terjemahan lebih lama daripada teks Indonesia yang sudah diubah.
                    $usang = $b !== 'id' && $asal !== null && ($r === null || $r['updated_at'] < $asal['updated_at']);
                    ?>
                    <td data-teks="<?= esc($h . '-' . $b) ?>">
                        <a href="/admin/teks/<?= esc($h) ?>/<?= $b ?>"><?= lang('Sunting.ubah') ?></a><br>
                        <?php if ($r === null): ?>
                            <span class="redup"><?= lang('Sunting.masihBawaan') ?></span>
                        <?php else: ?>
                            <span class="lencana lencana-active"><?= lang('Sunting.sudahDiubah') ?></span><br>
                            <small class="redup"><?= esc($orang[$r['updated_by']]['nama'] ?? '—') ?>, <span class="ltr"><?= esc(Teks::wib($r['updated_at'])) ?></span></small>
                        <?php endif ?>
                        <?php if ($usang): ?>
                            <br><span class="lencana lencana-suspended"><?= lang('Sunting.usang') ?></span>
                        <?php endif ?>
                    </td>
                <?php endforeach ?>
            </tr>
        <?php endforeach ?>
        </tbody>
    </table>
</div>
<p><a href="/admin/teks/riwayat"><?= lang('Sunting.riwayat.judul') ?></a></p>
<p class="petunjuk"><?= lang('Sunting.catatan') ?></p>
<?= $this->endSection() ?>
