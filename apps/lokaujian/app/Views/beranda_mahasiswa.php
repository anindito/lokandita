<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<?php
/**
 * Beranda mahasiswa (SPEC-LOKAUJIAN.md §8): ujian dan kuis kelas-kelasnya — sedang dikerjakan,
 * akan datang atau terbuka, dan selesai — lalu kelas-kelasnya. Halaman ini di luar SEB dan hanya
 * untuk dibaca: tidak memuat apa pun dari isi ujian. Berkas .seb tiap ujian diunduh dari sini
 * (KONTRAK.md §23). Variabel: $ujian (Pengerjaan::daftar), $kelas, $seb (id ujian => keadaan
 * konfigurasinya), $sebWajib.
 */
use Lokandita\Auth\Waktu;

$kerja   = array_values(array_filter($ujian, static fn (array $u): bool => $u['attempt_id'] !== null && (int) $u['berjalan'] === 1));
$selesai = array_values(array_filter($ujian, static fn (array $u): bool => $u['attempt_id'] !== null && (int) $u['berjalan'] !== 1));
$datang  = array_values(array_filter($ujian, static fn (array $u): bool => $u['attempt_id'] === null));
$jenis   = static fn (array $u): string => lang($u['kind'] === 'kuis' ? 'Bank.ujian.jenisKuis' : 'Bank.ujian.jenisUjian');
$waktu   = static function (array $u): string {
    if ($u['mode'] === 'per_mahasiswa') {
        return lang('Kerja.daftar.jadwal', [esc(Waktu::lokal($u['open_at'])), $u['close_at'] === null ? '—' : esc(Waktu::lokal($u['close_at'])), (int) $u['duration_minutes']]);
    }

    return lang('Kerja.daftar.dosen');
};
?>
<h1><?= lang('Lokandita.halo', [esc($akses->identitas()->nama)]) ?></h1>

<h2><?= lang('Ujian.saya.ujianJudul') ?></h2>
<div class="kartu" data-seb-info>
    <p><?= lang('SebUjian.beranda.info') ?></p>
    <p><a href="/seb"><?= lang('SebUjian.beranda.petunjuk') ?></a></p>
</div>
<?php if ($ujian === []): ?>
    <div class="kartu" data-ujian-kosong><p><?= $kelas === [] ? lang('Ujian.saya.tanpaKelas') : lang('Ujian.saya.ujianKosong') ?></p></div>
<?php else: ?>
    <?php foreach ([['kerja', $kerja], ['datang', $datang], ['selesai', $selesai]] as [$bagian, $daftar]): ?>
        <?php if ($daftar === []) { continue; } ?>
        <h3><?= lang('Kerja.daftar.' . $bagian) ?></h3>
        <div class="tabel-wadah kartu">
            <table data-ujian-saya="<?= $bagian ?>">
                <thead><tr>
                    <th scope="col"><?= lang('Bank.ujian.judulIsian') ?></th>
                    <th scope="col"><?= lang('Bank.ujian.jenis') ?></th>
                    <th scope="col"><?= lang('Sesi.waktu') ?></th>
                    <th scope="col"><?= lang('Kerja.daftar.keterangan') ?></th>
                </tr></thead>
                <tbody>
                <?php foreach ($daftar as $u): ?>
                    <tr>
                        <td dir="auto"><a href="/ujian-saya/<?= (int) $u['sesi_id'] ?>"><?= esc($u['title']) ?></a></td>
                        <td><?= $jenis($u) ?></td>
                        <td><?= $waktu($u) ?></td>
                        <td>
                            <?php if ($bagian !== 'selesai'): ?>
                                <?php if (isset($seb[(int) $u['exam_id']])): ?>
                                    <a class="tombol tombol-sekunder" href="/ujian-saya/<?= (int) $u['sesi_id'] ?>/seb" data-unduh-seb><?= lang('SebUjian.beranda.unduh') ?></a>
                                <?php elseif ($sebWajib): ?>
                                    <span class="redup" data-seb-belum><?= lang('SebUjian.beranda.belumAda') ?></span>
                                <?php endif ?>
                            <?php endif ?>
                            <?php if ($bagian === 'kerja'): ?>
                                <a class="tombol" href="/ujian-saya/<?= (int) $u['sesi_id'] ?>/kerjakan"><?= lang('Kerja.lanjutkan') ?></a>
                            <?php elseif ($bagian === 'selesai'): ?>
                                <?= lang('Kerja.daftar.diterima', [esc(Waktu::lokal($u['submitted_at'] ?? $u['tenggat']))]) ?>
                                <?php // Hasil hanya tampil bila kelasnya dirilis (SPEC-LOKAUJIAN.md §11). Tinjauan dengan kunci dibuka di dalam SEB. ?>
                                <?php if ($u['release_level'] !== null && $u['score'] !== null): ?>
                                    <br><strong data-nilai><?= lang('Hasil.nilai.objektif', [esc(\App\Libraries\Nilai::tampil($u['score'])), esc(\App\Libraries\Nilai::tampil($u['poin_objektif']))]) ?></strong>
                                    <br><span class="redup"><?= lang('Hasil.nilai.manual') ?></span>
                                    <?php if ($u['release_level'] === 'nilai_dan_kunci'): ?>
                                        <br><span data-tinjau-info><?= lang('Hasil.nilai.tinjauDiSeb') ?></span>
                                        <?php if (isset($seb[(int) $u['exam_id']])): ?>
                                            <a class="tombol tombol-sekunder" href="/ujian-saya/<?= (int) $u['sesi_id'] ?>/seb" data-unduh-seb><?= lang('SebUjian.beranda.unduh') ?></a>
                                        <?php endif ?>
                                    <?php endif ?>
                                <?php endif ?>
                            <?php elseif ((int) $u['menerima'] === 1): ?>
                                <span class="lencana"><?= lang('Kerja.daftar.terbuka') ?></span>
                            <?php else: ?>
                                <span class="redup"><?= lang('Kerja.daftar.belum') ?></span>
                            <?php endif ?>
                        </td>
                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>
        </div>
    <?php endforeach ?>
<?php endif ?>

<?php if ($kelas !== []): ?>
    <h2><?= lang('Ujian.saya.kelasJudul') ?></h2>
    <div class="tabel-wadah kartu">
        <table>
            <thead>
            <tr>
                <th scope="col"><?= lang('Ujian.matkul.satu') ?></th>
                <th scope="col"><?= lang('Ujian.kelas.satu') ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($kelas as $k): ?>
                <tr data-kelas-saya>
                    <td><span class="ltr"><?= esc($k['matkul_kode']) ?></span> — <?= esc($k['matkul_nama']) ?></td>
                    <td><?= esc($k['kelas_nama']) ?></td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
<?php endif ?>
<?= $this->endSection() ?>
