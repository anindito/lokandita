<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('kepala') ?><?= view('_kepala_bank') ?><?= $this->endSection() ?>
<?= $this->section('isi') ?>
<?php
/**
 * Panel Safe Exam Browser satu ujian (KONTRAK.md §23). Variabel: $matkul, $ujian, $aktif
 * (konfigurasi yang berlaku atau null), $kunci (list), $kunciDiketik, $dijalankan (bool),
 * $diberlakukan (bool), $urlMulai, $galat. Tidak pernah menampilkan kata sandi keluar.
 */
use App\Libraries\SebUjian;
use Lokandita\Auth\Seb\Seb;
use Lokandita\Auth\Waktu;

$ini = '/mata-kuliah/' . (int) $matkul['id'] . '/ujian/' . (int) $ujian['id'];
?>
<?= view('_nav', ['akses' => $akses]) ?>
<p><a href="<?= $ini ?>"><span class="panah-balik" aria-hidden="true"></span> <?= lang('SebUjian.panel.kembali') ?></a></p>
<h1><?= lang('SebUjian.panel.judul') ?>: <span dir="auto"><?= esc($ujian['title']) ?></span></h1>
<?php if (! $diberlakukan): ?>
    <div class="pita-bahaya" role="alert" data-mode-uji><?= lang('SebUjian.panel.modeUji') ?></div>
<?php endif ?>
<?= view('_galat_form', ['galat' => $galat]) ?>

<div class="kartu" data-seb-konfig="<?= $aktif === null ? '0' : (int) $aktif['version'] ?>">
    <p><strong><?= lang('SebUjian.panel.keadaan') ?></strong></p>
    <?php if ($aktif === null): ?>
        <p><?= lang('SebUjian.panel.belum') ?></p>
    <?php else: ?>
        <p><?= lang('SebUjian.panel.versi', [(int) $aktif['version'], esc(Waktu::lokal($aktif['created_at']))]) ?></p>
        <p data-jumlah-kunci="<?= count($kunci) ?>"><?= $kunci === [] ? lang('SebUjian.panel.tanpaKunci') : lang('SebUjian.panel.kunciJumlah', [count($kunci)]) ?></p>
        <p class="petunjuk"><?= lang('SebUjian.panel.mulaiDi') ?> <span class="ltr"><?= esc($urlMulai) ?></span></p>
        <div class="baris-tombol"><a class="tombol" href="<?= $ini ?>/seb/unduh" data-unduh-seb><?= lang('SebUjian.panel.unduh') ?></a></div>
        <p class="petunjuk"><?= lang('SebUjian.panel.unduhInfo') ?></p>
    <?php endif ?>
</div>

<?php if ($aktif !== null): ?>
    <form method="post" action="<?= $ini ?>/seb/kunci" class="kartu" data-kunci-form>
        <?= csrf_field() ?>
        <p><strong><?= lang('SebUjian.panel.kunciJudul') ?></strong></p>
        <p class="petunjuk"><?= lang('SebUjian.panel.kunciInfo', [SebUjian::KUNCI_MAKS]) ?></p>
        <label for="seb-kunci"><?= lang('SebUjian.panel.kunciIsian') ?></label>
        <textarea id="seb-kunci" name="kunci" rows="4" class="ltr" spellcheck="false" autocomplete="off"><?= esc($kunciDiketik !== '' ? $kunciDiketik : implode("\n", $kunci)) ?></textarea>
        <div class="baris-tombol"><button type="submit" class="tombol"><?= lang('SebUjian.panel.kunciSimpan') ?></button></div>
        <p><strong><?= lang('SebUjian.panel.langkahJudul') ?></strong></p>
        <ol>
            <?php foreach (lang('SebUjian.panel.langkah') as $l): ?><li><?= esc($l) ?></li><?php endforeach ?>
        </ol>
    </form>
<?php endif ?>

<?php if ($dijalankan && $aktif !== null): ?>
    <div class="kartu" role="status" data-seb-dijalankan><p><?= lang('SebUjian.panel.dijalankan') ?></p></div>
<?php else: ?>
    <form method="post" action="<?= $ini ?>/seb" class="kartu" data-seb-form>
        <?= csrf_field() ?>
        <p><strong><?= lang($aktif === null ? 'SebUjian.panel.buatJudul' : 'SebUjian.panel.rotasiJudul') ?></strong></p>
        <?php if ($aktif !== null): ?><p class="petunjuk"><?= lang('SebUjian.panel.rotasiInfo') ?></p><?php endif ?>
        <label for="seb-sandi"><?= lang('SebUjian.panel.sandi') ?></label>
        <input type="password" id="seb-sandi" name="sandi_keluar" minlength="<?= Seb::SANDI_KELUAR_MIN ?>" maxlength="200" required autocomplete="new-password">
        <p class="petunjuk"><?= lang('SebUjian.panel.sandiInfo', [Seb::SANDI_KELUAR_MIN]) ?></p>
        <div class="baris-tombol"><button type="submit" class="tombol<?= $aktif === null ? '' : ' tombol-sekunder' ?>"><?= lang($aktif === null ? 'SebUjian.panel.buat' : 'SebUjian.panel.rotasi') ?></button></div>
    </form>
<?php endif ?>
<?= $this->endSection() ?>
