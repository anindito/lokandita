<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<?php
/**
 * Satu kiriman mahasiswa untuk dosen: kode sumber dan status per tes (SPEC.md §12).
 * Tidak ada masukan atau keluaran tes: tabel kiriman tidak menyimpannya.
 * Variabel: $kelas, $k, $judulSoal, $tes, $kurang, $orang.
 */
use App\Libraries\Latihan;
use App\Libraries\Waktu;

$alamat = '/kelas/' . (int) $kelas['id'] . '/pertemuan/' . (int) $k['pertemuan_id'];
$vonis  = $k['status'] === 'GAGAL' ? 'FAILED' : (string) $k['verdict'];
?>
<?= view('_nav', ['akses' => $akses]) ?>
<p><a href="<?= $alamat ?>/kiriman?mahasiswa=<?= esc($k['user_id']) ?>"><span class="panah-balik" aria-hidden="true"></span> <?= lang('Papan.kiriman.judul') ?> — <?= lang('Soal.pertemuan.ke', [(int) $k['number']]) ?></a></p>
<h1><?= esc($judulSoal) ?></h1>
<div class="kartu">
    <dl data-kiriman-ringkas>
        <dt class="redup"><?= lang('Papan.nama') ?></dt>
        <dd><?= esc($orang['nama'] ?? '—') ?> · <span class="ltr"><?= esc($orang['nomor_induk'] ?? '—') ?></span></dd>
        <dt class="redup"><?= lang('Papan.kiriman.waktu') ?></dt>
        <dd class="ltr"><?= esc(Waktu::lokal($k['created_at'])) ?></dd>
        <dt class="redup"><?= lang('Papan.kiriman.hasil') ?></dt>
        <dd>
            <?php if ($k['status'] === 'MENUNGGU'): ?>
                <span class="lencana"><?= lang('Papan.kiriman.menunggu') ?></span>
            <?php else: ?>
                <span class="lencana verdict-<?= esc(strtolower($vonis)) ?> ltr"><?= esc($vonis) ?></span>
            <?php endif ?>
            <?php if ((int) $k['late'] === 1): ?><span class="lencana jenis-telat"><?= lang('Latihan.jenis.telat') ?> <span class="ltr">× <?= angka((float) $k['late_factor']) ?></span></span><?php endif ?>
            <?php if ((int) $k['practice'] === 1): ?><span class="lencana jenis-latihan"><?= lang('Latihan.jenis.latihan') ?></span> <?= lang('Papan.kiriman.latihanInfo') ?><?php endif ?>
        </dd>
        <dt class="redup"><?= lang('Papan.nilai') ?></dt>
        <dd class="ltr"><?= $k['score'] === null ? '—' : angka($k['score']) . ' / ' . Latihan::POIN[$k['difficulty']] ?><?= (int) $k['total'] > 0 ? ' · ' . (int) $k['passed'] . ' / ' . (int) $k['total'] : '' ?></dd>
    </dl>
    <?php if ($kurang !== []): ?><p class="teks-peringatan"><?= lang('Papan.kiriman.konsepKurang') ?> <span class="ltr"><?= esc(implode(', ', $kurang)) ?></span></p><?php endif ?>
</div>

<h2><?= lang('Papan.kiriman.sumber') ?> <span class="redup">(<?= lang('Soal.bahasa.' . $k['language']) ?>)</span></h2>
<pre class="kode-sumber" dir="ltr" data-sumber><code><?= esc($k['source']) ?></code></pre>

<?php if ($tes !== []): ?>
    <h2><?= lang('Papan.kiriman.tes') ?></h2>
    <div class="kartu">
        <ol class="tes-daftar" data-tes>
            <?php foreach ($tes as $t): ?>
                <li><?= lang('Papan.kiriman.tesKe', [(int) $t['index'] + 1]) ?>
                    <span class="lencana verdict-<?= esc(strtolower((string) $t['verdict'])) ?> ltr"><?= esc($t['verdict']) ?></span>
                    <span class="redup ltr"><?= (int) ($t['duration_ms'] ?? 0) ?> ms</span></li>
            <?php endforeach ?>
        </ol>
        <p class="petunjuk"><?= lang('Papan.kiriman.tesInfo') ?></p>
    </div>
<?php endif ?>

<?php if (trim((string) $k['compile_log']) !== ''): ?>
    <h2><?= lang('Papan.kiriman.log') ?></h2>
    <pre class="kode-sumber" dir="ltr"><code><?= esc($k['compile_log']) ?></code></pre>
<?php endif ?>
<?= $this->endSection() ?>
