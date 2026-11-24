<?php
/**
 * Halaman periksa SEB (publik, tidak diindeks). Variabel: $adaKonfig, $adaPeramban (bool: header
 * diterima), $hasil (Seb::periksa), $url. Tidak pernah menampilkan kunci.
 */
$ya = static fn (bool $b): string => '<strong class="' . ($b ? 'teks-sukses' : 'teks-bahaya') . '">' . lang($b ? 'Seb.periksa.ya' : 'Seb.periksa.tidak') . '</strong>';
$cocok = static fn (bool $b): string => '<strong class="' . ($b ? 'teks-sukses' : 'teks-bahaya') . '">' . lang($b ? 'Seb.periksa.cocok' : 'Seb.periksa.tidakCocok') . '</strong>';
?>
<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('kepala') ?>
<script src="/js/seb-periksa.js" defer></script>
<?= $this->endSection() ?>
<?= $this->section('isi') ?>
<div class="isi-sempit">
    <h1><?= lang('Seb.periksa.judul') ?></h1>
    <div class="kartu" data-seb-periksa="<?= $hasil['lolos'] ? 'lolos' : esc($hasil['alasan']) ?>">
        <p><?= lang('Seb.periksa.alamat') ?>: <code class="ltr"><?= esc($url) ?></code></p>
        <p><?= lang('Seb.periksa.headerKonfig') ?>: <?= $ya($adaKonfig) ?></p>
        <p><?= lang('Seb.periksa.headerPeramban') ?>: <?= $ya($adaPeramban) ?></p>
        <p><?= lang('Seb.periksa.versi') ?>: <code class="ltr" data-seb-versi data-tidak-ada="<?= esc(lang('Seb.periksa.versiTidakAda')) ?>"><?= lang('Seb.periksa.versiTidakAda') ?></code></p>
    </div>
    <?php if ($hasil['rincian'] === []): ?>
        <div class="kartu"><p><?= lang('Seb.periksa.kosong') ?></p></div>
    <?php else: ?>
        <div class="tabel-wadah kartu">
            <table>
                <thead>
                <tr>
                    <th scope="col"><?= lang('Seb.periksa.matkul') ?></th>
                    <th scope="col"><?= lang('Seb.periksa.konfig') ?></th>
                    <th scope="col"><?= lang('Seb.periksa.peramban') ?></th>
                    <th scope="col"><?= lang('Seb.periksa.terdaftar') ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($hasil['rincian'] as $r): ?>
                    <tr data-matkul="<?= esc($r['code']) ?>">
                        <td class="ltr"><?= esc($r['code']) ?></td>
                        <td data-konfig="<?= $r['konfig'] ? '1' : '0' ?>"><?= $cocok($r['konfig']) ?></td>
                        <td data-peramban="<?= $r['peramban'] ? '1' : '0' ?>"><?= $cocok($r['peramban']) ?></td>
                        <td><?= (int) $r['kunci'] ?></td>
                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>
        </div>
    <?php endif ?>
    <p class="redup"><?= lang('Seb.periksa.catatan') ?></p>
</div>
<?= $this->endSection() ?>
