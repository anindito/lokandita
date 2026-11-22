<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?php
/**
 * Halaman soal mahasiswa, tiga panel (SPEC.md §11).
 *
 * Hanya soal INI yang dikirim ke peramban, dan dari tesnya hanya tes contoh.
 * Variabel: $soal, $pernyataan (per bahasa), $bahasaAwal, $contoh, $nilai, $skor, $data (untuk public/js/soal.js).
 */
use App\Libraries\Latihan;
use Lokandita\Auth\Nonce;

$versi   = esc(substr((string) getenv('LOKANDITA_COMMIT'), 0, 12));
$kerja   = $soal['mode'] === 'kerja';
$nama    = ['id' => 'Indonesia', 'en' => 'English', 'ar' => 'العربية'];
$poin    = Latihan::POIN[$soal['difficulty']];
$tanda   = $akses->identitas()->nomorInduk . ' · ' . $akses->identitas()->nama . ' · ' . \App\Libraries\Waktu::lokal(\App\Libraries\Waktu::kini());
?>
<?= $this->section('kepala') ?>
    <script src="/monaco/vs/loader.js?v=<?= $versi ?>" defer></script>
    <script src="/js/soal.js?v=<?= $versi ?>" defer></script>
<?= $this->endSection() ?>
<?= $this->section('isi') ?>
<p class="hanya-cetak"><?= lang('Latihan.soal.cetak') ?></p>
<noscript><p class="kartu"><strong><?= lang('Latihan.soal.tanpaSkrip') ?></strong></p></noscript>

<div class="soal-tab-sempit" role="tablist" aria-label="<?= esc($judul) ?>" data-tab-sempit>
    <button type="button" role="tab" aria-selected="true" data-panel="soal"><?= lang('Latihan.soal.panelSoal') ?></button>
    <button type="button" role="tab" aria-selected="false" data-panel="editor"><?= lang('Latihan.soal.panelEditor') ?></button>
    <button type="button" role="tab" aria-selected="false" data-panel="hasil"><?= lang('Latihan.soal.panelHasil') ?></button>
</div>

<div class="soal-tata" data-soal data-panel-aktif="soal">
    <section class="panel panel-soal" aria-label="<?= lang('Latihan.soal.panelSoal') ?>">
        <header class="panel-kepala">
            <a href="/latihan/<?= (int) $soal['pertemuan_id'] ?>"><span class="panah-balik" aria-hidden="true"></span> <?= lang('Latihan.soal.kembali') ?></a>
            <span class="lencana lencana-tingkat tingkat-<?= esc(strtolower($soal['difficulty'])) ?>"><?= lang('Soal.tingkat.' . $soal['difficulty']) ?></span>
            <span class="lencana"><?= lang('Latihan.ruang.poin', [$poin]) ?></span>
            <?php if (! $kerja): ?><span class="lencana mode-baca"><?= lang('Latihan.soal.hanyaBaca') ?></span><?php endif ?>
            <?php if (in_array($soal['jenis'], ['telat', 'latihan'], true)): ?><span class="lencana jenis-<?= $soal['jenis'] ?>"><?= lang('Latihan.jenis.' . $soal['jenis']) ?></span><?php endif ?>
            <?php if (count($pernyataan) > 1): ?>
                <span class="pilih-bahasa" role="group" aria-label="<?= lang('Latihan.soal.bahasaSoal') ?>">
                    <?php foreach ($pernyataan as $b => $t): ?>
                        <button type="button" class="tombol-kecil" data-bahasa-soal="<?= $b ?>" aria-pressed="<?= $b === $bahasaAwal ? 'true' : 'false' ?>" lang="<?= $b ?>"><?= esc($nama[$b]) ?></button>
                    <?php endforeach ?>
                </span>
            <?php endif ?>
        </header>
        <p class="skor-soal" data-skor-soal><?= $skor === null ? lang('Latihan.soal.belumKirim') : lang('Latihan.soal.terbaik', [angka($skor['best_score']), $poin]) ?></p>
        <?= view('_ringkasan_nilai', ['nilai' => $nilai]) ?>
        <?= view('_jenis_kerja', ['p' => $soal]) ?>

        <?php // Soal tidak bisa disalin atau dicetak, dan ditutupi tanda air (SPEC.md §11). ?>
        <div class="pernyataan-wadah tanpa-salin" data-tanpa-salin>
            <div class="tanda-air" aria-hidden="true" data-tanda-air>
                <?php for ($i = 0; $i < 40; $i++): ?><span><?= esc($tanda) ?></span><?php endfor ?>
            </div>
            <?php if (! isset($pernyataan[service('request')->getLocale()]) && service('request')->getLocale() !== 'id'): ?>
                <p class="lencana"><?= lang('Latihan.soal.belumTerjemah') ?></p>
            <?php endif ?>
            <?php foreach ($pernyataan as $b => $t): ?>
                <article class="pernyataan" lang="<?= $b ?>" dir="<?= $b === 'ar' ? 'rtl' : 'ltr' ?>" data-pernyataan="<?= $b ?>" <?= $b === $bahasaAwal ? '' : 'hidden' ?>>
                    <h1><?= esc($t['judul']) ?> <?php if ($t['mesin']): ?><span class="lencana"><?= lang('Latihan.soal.terjemahanMesin') ?></span><?php endif ?></h1>
                    <?= $t['statement'] ?>
                    <?php if ($t['input_format'] !== ''): ?><h2><?= lang('Latihan.soal.formatMasukan', [], $b) ?></h2><?= $t['input_format'] ?><?php endif ?>
                    <?php if ($t['output_format'] !== ''): ?><h2><?= lang('Latihan.soal.formatKeluaran', [], $b) ?></h2><?= $t['output_format'] ?><?php endif ?>
                    <?php if ($t['constraints'] !== ''): ?><h2><?= lang('Latihan.soal.batasan', [], $b) ?></h2><?= $t['constraints'] ?><?php endif ?>
                </article>
            <?php endforeach ?>
            <p class="redup"><?= lang('Latihan.soal.batasWaktu', [angka((int) $soal['time_limit_ms'] / 1000)]) ?></p>

            <?php if ($contoh === []): ?>
                <p class="redup"><?= lang('Latihan.soal.tanpaContoh') ?></p>
            <?php endif ?>
            <?php foreach ($contoh as $i => $c): ?>
                <h2><?= lang('Latihan.soal.contoh', [$i + 1]) ?></h2>
                <div class="contoh-pasang">
                    <div><p class="redup"><?= lang('Latihan.soal.masukan') ?></p><pre class="kode-sumber"><?= esc($c['in']) ?></pre></div>
                    <div><p class="redup"><?= lang('Latihan.soal.keluaran') ?></p><pre class="kode-sumber"><?= esc($c['out']) ?></pre></div>
                </div>
            <?php endforeach ?>
        </div>
    </section>

    <div class="pembagi pembagi-tegak" role="separator" aria-orientation="vertical" tabindex="0" aria-label="<?= lang('Latihan.soal.pembagiTegak') ?>" data-pembagi="kiri"></div>

    <div class="soal-kanan">
        <section class="panel panel-editor" aria-label="<?= lang('Latihan.soal.editor') ?>">
            <header class="panel-kepala">
                <strong><?= lang('Soal.bahasa.' . $soal['versi_bahasa']) ?></strong>
                <span class="redup status-draf" role="status" aria-live="polite" data-status-draf></span>
                <?php if ($kerja): ?>
                    <span class="baris-aksi">
                        <button type="button" class="tombol tombol-sekunder tombol-kecil" data-aksi="reset"><?= lang('Latihan.soal.reset') ?></button>
                        <button type="button" class="tombol tombol-sekunder" data-aksi="jalankan" title="<?= lang('Latihan.soal.pintasJalankan') ?>"><?= lang('Latihan.soal.jalankan') ?></button>
                        <button type="button" class="tombol" data-aksi="kirim" title="<?= lang('Latihan.soal.pintasKirim') ?>"><?= lang('Latihan.soal.kirim') ?></button>
                    </span>
                <?php endif ?>
            </header>
            <?php // Editor, keluaran program dan log kompiler selalu kiri-ke-kanan, juga di halaman berbahasa Arab. ?>
            <div class="editor-wadah" dir="ltr" data-editor>
                <textarea class="kode-isian editor-cadangan" data-editor-cadangan spellcheck="false" autocomplete="off" autocapitalize="off"
                          aria-label="<?= lang('Latihan.soal.editor') ?>" <?= $kerja ? '' : 'readonly' ?>></textarea>
            </div>
        </section>

        <div class="pembagi pembagi-datar" role="separator" aria-orientation="horizontal" tabindex="0" aria-label="<?= lang('Latihan.soal.pembagiDatar') ?>" data-pembagi="atas"></div>

        <section class="panel panel-hasil" aria-label="<?= lang('Latihan.soal.panelHasil') ?>">
            <div class="tab-baris" role="tablist" data-tab-hasil>
                <button type="button" role="tab" id="tab-keluaran" aria-controls="isi-keluaran" aria-selected="true"><?= lang('Latihan.soal.tabKeluaran') ?></button>
                <button type="button" role="tab" id="tab-tes" aria-controls="isi-tes" aria-selected="false" tabindex="-1"><?= lang('Latihan.soal.tabTes') ?></button>
                <?php if ($kerja): ?>
                    <button type="button" role="tab" id="tab-kustom" aria-controls="isi-kustom" aria-selected="false" tabindex="-1"><?= lang('Latihan.soal.tabKustom') ?></button>
                <?php endif ?>
                <button type="button" role="tab" id="tab-riwayat" aria-controls="isi-riwayat" aria-selected="false" tabindex="-1"><?= lang('Latihan.soal.tabRiwayat') ?></button>
                <button type="button" role="tab" id="tab-klarifikasi" aria-controls="isi-klarifikasi" aria-selected="false" tabindex="-1"><?= lang('Latihan.soal.tabKlarifikasi') ?></button>
            </div>

            <div class="tab-isi" role="tabpanel" id="isi-keluaran" aria-labelledby="tab-keluaran" tabindex="0">
                <p class="status-jalan" role="status" aria-live="polite" data-status-jalan></p>
                <div data-keluaran><p class="redup"><?= $kerja ? lang('Latihan.soal.keluaranKosong') : lang('Latihan.ruang.tutupBaca') ?></p></div>
            </div>

            <div class="tab-isi" role="tabpanel" id="isi-tes" aria-labelledby="tab-tes" tabindex="0" hidden>
                <?php if ($contoh === []): ?><p class="redup"><?= lang('Latihan.soal.tanpaContoh') ?></p><?php endif ?>
                <?php foreach ($contoh as $i => $c): ?>
                    <div class="tes-contoh" data-tes-contoh="<?= $i ?>">
                        <h3><?= lang('Latihan.soal.contoh', [$i + 1]) ?> <span class="lencana" data-tes-verdict hidden></span></h3>
                        <div class="contoh-pasang tanpa-salin">
                            <div><p class="redup"><?= lang('Latihan.soal.masukan') ?></p><pre class="kode-sumber"><?= esc($c['in']) ?></pre></div>
                            <div><p class="redup"><?= lang('Latihan.soal.keluaran') ?></p><pre class="kode-sumber"><?= esc($c['out']) ?></pre></div>
                        </div>
                        <div data-tes-nyata hidden><p class="redup"><?= lang('Latihan.soal.keluaranAnda') ?></p><pre class="kode-sumber" data-tes-keluaran></pre></div>
                    </div>
                <?php endforeach ?>
            </div>

            <?php if ($kerja): ?>
                <div class="tab-isi" role="tabpanel" id="isi-kustom" aria-labelledby="tab-kustom" tabindex="0" hidden>
                    <label for="masukan-kustom"><?= lang('Latihan.soal.kustomLabel') ?></label>
                    <textarea id="masukan-kustom" class="kode-isian" rows="5" dir="ltr" spellcheck="false" data-masukan-kustom></textarea>
                    <p class="petunjuk"><?= lang('Latihan.soal.kustomInfo') ?></p>
                    <div class="baris-tombol">
                        <button type="button" class="tombol tombol-sekunder" data-aksi="jalankan-kustom"><?= lang('Latihan.soal.jalankanKustom') ?></button>
                    </div>
                </div>
            <?php endif ?>

            <div class="tab-isi" role="tabpanel" id="isi-riwayat" aria-labelledby="tab-riwayat" tabindex="0" hidden>
                <div data-riwayat><p class="redup"><?= lang('Latihan.soal.riwayatKosong') ?></p></div>
            </div>

            <?php // Klarifikasi soal (SPEC.md §14): daftar diisi public/js/soal.js saat tab dibuka. ?>
            <div class="tab-isi" role="tabpanel" id="isi-klarifikasi" aria-labelledby="tab-klarifikasi" tabindex="0" hidden>
                <form data-klarifikasi-form>
                    <label for="pertanyaan-klarifikasi"><?= lang('Latihan.soal.klarLabel') ?></label>
                    <textarea id="pertanyaan-klarifikasi" rows="3" maxlength="2000" required data-klarifikasi-isian></textarea>
                    <p class="petunjuk"><?= lang('Latihan.soal.klarInfo') ?></p>
                    <div class="baris-tombol"><button type="submit" class="tombol tombol-sekunder"><?= lang('Latihan.soal.klarKirim') ?></button></div>
                </form>
                <div data-klarifikasi-daftar></div>
            </div>
        </section>
    </div>
</div>

<div class="toast-wadah toast-soal" aria-live="assertive" data-toast-soal></div>
<?php // Blok data, bukan skrip yang dijalankan; nonce tetap dipasang. ?>
<script type="application/json" id="data-soal" nonce="<?= esc(Nonce::value()) ?>"><?= json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_THROW_ON_ERROR) ?></script>
<?= $this->endSection() ?>
