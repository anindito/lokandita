<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('kepala') ?><?= view('_kepala_bank') ?>
<script src="/js/ujian.js?v=<?= esc(substr((string) getenv('LOKANDITA_COMMIT'), 0, 12)) ?>" defer></script>
<?= $this->endSection() ?>
<?= $this->section('isi') ?>
<?php
/**
 * HALAMAN UJIAN mahasiswa (SPEC-LOKAUJIAN.md §8, §9.2): dimuat sekali dengan seluruh ujian; satu
 * soal (atau satu kelompok dengan soalnya yang sedang dibuka) tampil pada satu waktu. Soal
 * digambar dengan partial `_soal`, yang sama dengan pratinjau dosen.
 *
 * Tidak ada skor, kunci atau petunjuk kebenaran di halaman ini: Pengerjaan::bukaHalaman sudah
 * membuang kunci sebelum data sampai ke sini.
 *
 * Perilakunya ada di /js/ujian.js; semua yang diperlukannya ditulis di atribut data-*.
 * Variabel: $sesi (Pengerjaan::bukaHalaman), $bolehTempel (paste_allowed LokaAuth).
 */
use App\Libraries\Markdown;
use App\Libraries\Pengerjaan;

$id        = (int) $sesi['sesi_id'];
$identitas = $akses->identitas();
$jumlah    = count($sesi['soal']);
$teks      = [];
foreach (['tersimpan', 'menyimpan', 'belumTersimpan', 'sisa', 'berjalan', 'umum10', 'umum5', 'umum1', 'waktuHabis', 'dikumpulkan', 'diambilAlih', 'sebBerhenti',
    'tempelDiblokir', 'salinDiblokir', 'petaSoal', 'petaTerjawab', 'petaBelum', 'petaRagu', 'karakter', 'tinjauTerjawab', 'tinjauTidakAda',
    'yakin', 'yakinKosong', 'tungguSimpan', 'diakhiri', 'tinggalkan'] as $k) {
    $teks[$k] = lang('Kerja.js.' . $k);
}
?>
<div id="ujian"
     data-sesi="<?= $id ?>"
     data-halaman="<?= esc($sesi['page_token'], 'attr') ?>"
     data-sisa="<?= (int) $sesi['sisa'] ?>"
     data-tanpa-akhir="<?= $sesi['mode'] === 'dikendalikan_dosen' && $sesi['end_at'] === null ? '1' : '0' ?>"
     data-berlalu="<?= (int) ($sesi['berlalu'] ?? 0) ?>"
     data-diakhiri="<?= $sesi['ended_by'] !== null ? '1' : '0' ?>"
     data-boleh-tempel="<?= $bolehTempel ? '1' : '0' ?>"
     data-dimuat="<?= esc((new DateTimeImmutable('now', new DateTimeZone('Asia/Jakarta')))->format('H:i:s'), 'attr') ?>"
     data-tanda-air="<?= esc($identitas->nomorInduk . ' · ' . $identitas->nama, 'attr') ?>"
     data-teks="<?= esc(json_encode($teks, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 'attr') ?>">

    <div class="ujian-kepala" data-kepala>
        <div class="ujian-judul">
            <strong dir="auto"><?= esc($sesi['title']) ?></strong>
            <span class="redup"><span class="ltr"><?= esc($identitas->nomorInduk) ?></span> · <?= esc($identitas->nama) ?></span>
        </div>
        <div class="ujian-jam" role="timer" aria-label="<?= lang('Kerja.jam') ?>"><span class="ltr" data-jam>--:--</span></div>
        <div class="ujian-simpan" role="status" aria-live="polite" data-simpan data-keadaan="tersimpan"><?= lang('Kerja.js.tersimpan') ?> <span class="ltr" data-simpan-jam></span></div>
    </div>
    <p class="sr-saja" aria-live="assertive" data-umumkan></p>
    <div class="pita-bahaya" role="alert" data-luring hidden><?= lang('Kerja.luring') ?></div>
    <div class="pita-bahaya" role="alert" data-pita-akhiri hidden><?= lang('Kerja.js.diakhiri') ?></div>

    <div class="ujian-badan" data-badan>
        <div class="ujian-area" data-area>
            <div class="tanda-air" aria-hidden="true" data-tanda-air-wadah></div>
            <?php foreach ($sesi['kelompok'] as $k): ?>
                <div class="kelompok-bersama ujian-bersama" data-bersama="<?= (int) $k['id'] ?>" hidden>
                    <p class="redup"><?= lang('Kerja.bersama') ?></p>
                    <?php if ($k['text'] !== ''): ?><div class="soal-teks" dir="auto"><?= Markdown::html($k['text']) ?></div><?php endif ?>
                    <?php if ($k['image'] !== null): ?><figure class="soal-gambar"><?= view('_gambar', ['gambar' => $k['image']]) ?></figure><?php endif ?>
                </div>
            <?php endforeach ?>
            <?php foreach ($sesi['soal'] as $i => $s): ?>
                <?php
                $j        = $s['jawab'];
                $terjawab = $j !== null && ($j['option_id'] !== null || $j['benar'] !== null || $j['text'] !== '');
                $teksBox  = in_array($s['type'], ['isian', 'uraian'], true);
                ?>
                <section class="ujian-soal" data-halaman-soal="<?= $i ?>" data-id="<?= (int) $s['id'] ?>" data-tipe="<?= esc($s['type']) ?>"
                         data-kelompok="<?= $s['group_id'] === null ? '' : (int) $s['group_id'] ?>" data-n="<?= (int) ($j['counter'] ?? 0) ?>"
                         data-terjawab="<?= $terjawab ? '1' : '0' ?>" hidden>
                    <?= view('_soal', ['soal' => $s, 'nomor' => $i + 1, 'jawab' => $j ?? ['option_id' => null, 'benar' => null, 'text' => '']]) ?>
                    <div class="ujian-alat">
                        <?php if ($s['type'] === 'pilihan_ganda' || $s['type'] === 'benar_salah'): ?>
                            <button type="button" class="tombol tombol-sekunder" data-hapus-pilihan><?= lang('Kerja.hapusPilihan') ?></button>
                        <?php endif ?>
                        <?php if ($teksBox): ?>
                            <span class="redup ltr" data-hitung data-maks="<?= $s['type'] === 'isian' ? Pengerjaan::ISIAN_MAKS : Pengerjaan::URAIAN_MAKS ?>"></span>
                        <?php endif ?>
                        <label class="sebaris"><input type="checkbox" data-ragu<?= ($j['ragu'] ?? false) ? ' checked' : '' ?>> <span><?= lang('Kerja.ragu') ?></span></label>
                    </div>
                </section>
            <?php endforeach ?>
            <div class="baris-tombol ujian-pindah">
                <button type="button" class="tombol tombol-sekunder" data-sebelumnya><?= lang('Kerja.sebelumnya') ?></button>
                <button type="button" class="tombol tombol-sekunder" data-berikutnya><?= lang('Kerja.berikutnya') ?></button>
                <button type="button" class="tombol" data-selesai><?= lang('Kerja.selesai') ?></button>
            </div>
        </div>
        <nav class="ujian-peta" aria-label="<?= lang('Kerja.peta') ?>">
            <p class="redup"><?= lang('Kerja.peta') ?></p>
            <div class="peta-kisi">
                <?php for ($i = 0; $i < $jumlah; $i++): ?>
                    <button type="button" class="peta-tombol ltr" data-ke="<?= $i ?>"><?= $i + 1 ?></button>
                <?php endfor ?>
            </div>
            <p class="petunjuk"><span class="peta-contoh terjawab"></span> <?= lang('Kerja.js.petaTerjawab') ?> · <span class="peta-contoh ragu"></span> <?= lang('Kerja.js.petaRagu') ?></p>
        </nav>
    </div>

    <section class="kartu" data-tinjau hidden aria-labelledby="tinjau-judul">
        <h2 id="tinjau-judul"><?= lang('Kerja.tinjau.judul') ?></h2>
        <p data-tinjau-ringkas></p>
        <p><strong><?= lang('Kerja.tinjau.belum') ?></strong> <span data-tinjau-belum></span></p>
        <p><strong><?= lang('Kerja.tinjau.ragu') ?></strong> <span data-tinjau-ragu></span></p>
        <p class="petunjuk" data-tinjau-tunggu hidden><?= lang('Kerja.js.tungguSimpan') ?></p>
        <form method="post" action="/ujian-saya/<?= $id ?>/kumpulkan" data-kumpul-form>
            <?= csrf_field() ?>
            <div class="baris-tombol">
                <button type="button" class="tombol tombol-sekunder" data-tinjau-kembali><?= lang('Kerja.tinjau.kembali') ?></button>
                <button type="submit" class="tombol" data-kumpulkan><?= lang('Kerja.kumpulkan') ?></button>
            </div>
        </form>
    </section>

    <div class="kartu" role="alert" data-henti hidden>
        <p><strong data-henti-teks></strong></p>
        <p><?= lang('Kerja.hentiInfo') ?></p>
        <div class="baris-tombol"><a class="tombol" href="/ujian-saya/<?= $id ?>"><?= lang('Kerja.keTandaTerima') ?></a></div>
    </div>
    <div class="ujian-toast" role="status" aria-live="polite" data-toast-ujian hidden></div>
</div>
<?= $this->endSection() ?>
