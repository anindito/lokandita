<?php
/**
 * Isi halaman depan Portal. Dipakai halaman publik dan pratinjau di /admin/teks.
 * Variabel: $t (teks `depan`), $ket (teks `aplikasi`: kode => keterangan), $aplikasi (App\Libraries\Aplikasi::daftar()).
 */
?>
<section class="pembuka">
    <h1><?= esc($t['tajuk']) ?></h1>
    <p><?= esc($t['pengantar']) ?></p>
    <a class="tombol" href="/beranda" rel="nofollow"><?= lang('Lokandita.masuk') ?></a>
    <p class="petunjuk"><?= esc($t['tanpaDaftar']) ?></p>
</section>

<h2><?= esc($t['aplikasi']) ?></h2>
<ul class="petak">
    <?php foreach ($aplikasi as $kode => $a): ?>
        <li>
            <?php if ($a['url'] !== null): ?>
                <a class="kartu-aplikasi" href="<?= esc($a['url']) ?>/">
                    <strong><?= esc($a['nama']) ?></strong>
                    <span><?= esc($ket[$kode]) ?></span>
                </a>
            <?php else: ?>
                <div class="kartu-aplikasi kartu-aplikasi-segera">
                    <strong><?= esc($a['nama']) ?></strong>
                    <span class="lencana"><?= lang('Lokandita.segera') ?></span>
                    <span><?= esc($ket[$kode]) ?></span>
                </div>
            <?php endif ?>
        </li>
    <?php endforeach ?>
</ul>

<h2><?= esc($t['caraJudul']) ?></h2>
<ol>
    <?php foreach ($t['cara'] as $butir): ?>
        <li><?= esc($butir) ?></li>
    <?php endforeach ?>
</ol>
<p><?= esc($t['bantuan']) ?> <a href="/kontak"><?= lang('Portal.nav.kontak') ?></a>.</p>
