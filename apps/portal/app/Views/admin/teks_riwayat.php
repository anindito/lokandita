<?php
/**
 * Riwayat perubahan teks (log audit Portal). Variabel: $baris, $orang.
 * `sebelum` dan `sesudah` sudah diratakan menjadi baris "kunci: teks".
 */
use App\Controllers\Admin\Teks;

?>
<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<p><a class="panah-balik" href="/admin/teks"><?= lang('Sunting.judul') ?></a></p>
<h1><?= lang('Sunting.riwayat.judul') ?></h1>
<p><?= lang('Sunting.riwayat.pengantar') ?></p>
<?php if ($baris === []): ?>
    <div class="kartu"><p><?= lang('Sunting.riwayat.kosong') ?></p></div>
<?php endif ?>
<?php foreach ($baris as $b): ?>
    <article class="kartu" data-riwayat="<?= esc($b['action']) ?>">
        <p>
            <strong><?= lang('Sunting.halaman.' . $b['halaman']) ?></strong> ·
            <?php if ($b['bahasa'] !== null): ?>
                <span lang="<?= esc($b['bahasa']) ?>"><?= lang('Lokandita.bahasa' . ucfirst((string) $b['bahasa'])) ?></span> ·
            <?php endif ?>
            <?= lang('Sunting.riwayat.aksi.' . str_replace('.', '_', (string) $b['action'])) ?> ·
            <?= esc($orang[$b['actor_id']]['nama'] ?? '—') ?> ·
            <span class="ltr"><?= esc(Teks::wib($b['created_at'])) ?></span>
        </p>
        <?php foreach (['sebelum', 'sesudah'] as $k): ?>
            <?php if ($b[$k] !== null): ?>
                <details>
                    <summary><?= lang('Sunting.riwayat.' . $k) ?></summary>
                    <pre class="riwayat-teks" dir="auto"><?= esc(implode("\n", $b[$k])) ?></pre>
                </details>
            <?php endif ?>
        <?php endforeach ?>
    </article>
<?php endforeach ?>
<?= $this->endSection() ?>
