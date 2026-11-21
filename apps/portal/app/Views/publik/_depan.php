<?php
/**
 * Isi halaman depan Portal. Dipakai halaman publik dan pratinjau di /admin/teks.
 * Variabel: $t (teks `depan`), $ket (teks `aplikasi`: kode => keterangan), $aplikasi
 * (App\Libraries\Aplikasi::daftar()), $foto (App\Libraries\Foto), $bahasa (bahasa isi ini;
 * di pratinjau bisa berbeda dari bahasa halaman penyuntingnya).
 */
use Lokandita\Auth\Ikon;

$ikonAplikasi = ['lokakode' => 'code-xml', 'lokaujian' => 'clipboard-check', 'lokasiber' => 'shield-check'];
// Jumlah langkah bisa diubah di /admin/teks: empat yang pertama punya ikon sendiri, sisanya ikon umum.
$ikonLangkah  = ['log-in', 'key-round', 'layout-grid', 'log-out'];
$berarah      = ['log-in', 'log-out'];
?>
<section class="pembuka">
    <div class="pembuka-teks">
        <h1><?= esc($t['tajuk']) ?></h1>
        <p><?= esc($t['pengantar']) ?></p>
        <a class="tombol tombol-besar" href="/beranda" rel="nofollow"><?= Ikon::svg('log-in', '', true) ?><span><?= lang('Lokandita.masuk', [], $bahasa) ?></span></a>
        <p class="petunjuk"><?= esc($t['tanpaDaftar']) ?></p>
    </div>
    <?= view('publik/_foto', ['foto' => $foto]) ?>
</section>

<h2 class="judul-bagian"><?= esc($t['aplikasi']) ?></h2>
<ul class="petak">
    <?php foreach ($aplikasi as $kode => $a): ?>
        <li>
            <?php if ($a['url'] !== null): ?>
                <a class="kartu-aplikasi" href="<?= esc($a['url']) ?>/">
                    <span class="ikon-wadah"><?= Ikon::svg($ikonAplikasi[$kode] ?? 'layout-grid') ?></span>
                    <strong><?= esc($a['nama']) ?></strong>
                    <span><?= esc($ket[$kode]) ?></span>
                    <span class="kartu-buka"><?= lang('Portal.buka', [], $bahasa) ?> <?= Ikon::svg('arrow-right', '', true) ?></span>
                </a>
            <?php else: ?>
                <div class="kartu-aplikasi kartu-aplikasi-segera">
                    <span class="ikon-wadah"><?= Ikon::svg($ikonAplikasi[$kode] ?? 'layout-grid') ?></span>
                    <strong><?= esc($a['nama']) ?></strong>
                    <span class="lencana lencana-segera"><?= lang('Lokandita.segera', [], $bahasa) ?></span>
                    <span><?= esc($ket[$kode]) ?></span>
                </div>
            <?php endif ?>
        </li>
    <?php endforeach ?>
</ul>

<h2 class="judul-bagian"><?= esc($t['caraJudul']) ?></h2>
<ol class="langkah" role="list">
    <?php foreach (array_values($t['cara']) as $i => $butir): ?>
        <?php $ikon = $ikonLangkah[$i] ?? 'circle-check' ?>
        <li>
            <span class="ikon-wadah"><?= Ikon::svg($ikon, '', in_array($ikon, $berarah, true)) ?></span>
            <span class="langkah-nomor" aria-hidden="true"><?= $i + 1 ?></span>
            <span><?= esc($butir) ?></span>
        </li>
    <?php endforeach ?>
</ol>
<p class="bantuan"><?= Ikon::svg('mail') ?> <span><?= esc($t['bantuan']) ?> <a href="/tentang"><?= esc(lang('Portal.nav.tentang', [], $bahasa)) ?></a>.</span></p>
