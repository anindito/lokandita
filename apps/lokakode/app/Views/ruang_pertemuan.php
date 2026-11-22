<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('kepala') ?>
    <script src="/js/pertemuan.js?v=<?= esc(substr((string) getenv('LOKANDITA_COMMIT'), 0, 12)) ?>" defer></script>
<?= $this->endSection() ?>
<?= $this->section('isi') ?>
<?php
use App\Libraries\Latihan;
use App\Libraries\Waktu;

$mode = $p['mode'];
// Hitung mundur hanya bila pertemuan terbuka MENURUT JADWAL; buka manual tidak punya waktu tutup.
$tutupIso = $p['jenis'] === 'biasa' && $p['override'] === 'none' && $p['close_at'] !== null ? str_replace(' ', 'T', $p['close_at']) . 'Z' : null;
?>
<?= view('_nav', ['akses' => $akses, 'notifBelum' => $notifBelum ?? 0]) ?>
<p><a href="/beranda"><span class="panah-balik" aria-hidden="true"></span> <?= lang('Latihan.ruang.kembali') ?></a></p>
<h1><span class="ltr"><?= esc($p['matkul_kode']) ?></span> — <?= lang('Soal.pertemuan.ke', [(int) $p['number']]) ?>: <?= esc($p['title']) ?></h1>
<p class="redup">
    <?= esc($p['matkul_nama']) ?> · <?= esc($p['kelas_nama']) ?> · <?= lang('Soal.bahasa.' . $p['language']) ?>
    · <span class="lencana mode-<?= esc($mode) ?>"><?= lang(in_array($p['jenis'], ['telat', 'latihan'], true) ? 'Latihan.jenis.' . $p['jenis'] : 'Latihan.mode.' . $mode) ?></span>
</p>

<?php if ($mode === 'tunggu'): ?>
    <div class="kartu" role="status">
        <?php // Mahasiswa diberi tahu SEBABNYA (Ruang::sebab): belum terbit, belum dibuka untuk kelasnya, atau terjadwal. ?>
        <p data-sebab="<?= esc($p['sebab']) ?>"><strong><?= lang('Latihan.sebab.' . $p['sebab']) ?></strong></p>
        <p><?= $p['sebab'] === 'terjadwal' ? lang('Latihan.nilai.bukaPada', [esc(Waktu::lokal($p['open_at']))]) : lang('Latihan.sebab.' . $p['sebab'] . 'Info', [esc($p['kelas_nama'])]) ?></p>
    </div>
<?php else: ?>
    <div class="kartu">
        <?= view('_ringkasan_nilai', ['nilai' => $nilai]) ?>
        <?php if ($nilai['surplus'] > 0): ?>
            <p class="petunjuk"><?= lang('Latihan.nilai.surplus', [angka($nilai['surplus'])]) ?>. <?= lang('Latihan.nilai.surplusInfo') ?></p>
        <?php endif ?>
        <p class="sisa-waktu">
            <?php if ($mode === 'kerja' && $tutupIso !== null): ?>
                <strong><?= lang('Latihan.nilai.sisaWaktu') ?>:</strong>
                <span class="ltr" data-hitung-mundur="<?= esc($tutupIso) ?>" data-teks-jam="<?= esc(lang('Latihan.js.sisa')) ?>" data-teks-menit="<?= esc(lang('Latihan.js.sisaMenit')) ?>" data-teks-habis="<?= esc(lang('Latihan.nilai.habis')) ?>"></span>
                <span class="redup">(<?= lang('Latihan.nilai.tutupPada', [esc(Waktu::lokal($p['close_at']))]) ?>)</span>
            <?php elseif ($p['jenis'] === 'biasa'): ?>
                <span class="redup"><?= lang('Latihan.nilai.bukaManual') ?></span>
            <?php elseif ($mode === 'kerja'): ?>
                <?= view('_jenis_kerja', ['p' => $p]) ?>
            <?php elseif ($p['status_daftar'] === 'LULUS' && Latihan::keadaan($p) === 'buka'): ?>
                <strong><?= lang('Latihan.ruang.lulus') ?></strong>
            <?php else: ?>
                <strong><?= $mode === 'baca' ? lang('Latihan.ruang.tutupBaca') : lang('Latihan.ruang.tutupNilai') ?></strong>
            <?php endif ?>
        </p>
    </div>
<?php endif ?>

<?php if ($mode !== 'tunggu'): ?>
    <p><a href="/latihan/<?= (int) $p['pertemuan_id'] ?>/papan"><?= lang('Papan.lihat') ?></a></p>
<?php endif ?>

<?php if (in_array($mode, ['kerja', 'baca'], true)): ?>
    <h2><?= lang('Latihan.ruang.soal') ?></h2>
    <?php if ($kartu === []): ?>
        <div class="kartu"><p><?= lang('Latihan.ruang.kosong') ?></p></div>
    <?php else: ?>
        <form class="saring" data-saring-soal aria-label="<?= lang('Latihan.ruang.saring') ?>" hidden>
            <div>
                <label for="saring-tingkat"><?= lang('Latihan.ruang.tingkat') ?></label>
                <select id="saring-tingkat" data-saring="tingkat">
                    <option value=""><?= lang('Latihan.ruang.semuaTingkat') ?></option>
                    <?php foreach (Latihan::TINGKAT as $t): ?><option value="<?= $t ?>"><?= lang('Soal.tingkat.' . $t) ?></option><?php endforeach ?>
                </select>
            </div>
            <div>
                <label for="saring-status"><?= lang('Latihan.ruang.status') ?></label>
                <select id="saring-status" data-saring="status">
                    <option value=""><?= lang('Latihan.ruang.semuaStatus') ?></option>
                    <?php foreach (['belum', 'dicoba', 'selesai', 'dinilai'] as $s): ?><option value="<?= $s ?>"><?= lang('Latihan.kartu.' . $s) ?></option><?php endforeach ?>
                </select>
            </div>
            <div>
                <label for="saring-urut"><?= lang('Latihan.ruang.urut') ?></label>
                <select id="saring-urut" data-urut>
                    <option value="nomor"><?= lang('Latihan.ruang.urutNomor') ?></option>
                    <option value="poin-naik"><?= lang('Latihan.ruang.urutPoinNaik') ?></option>
                    <option value="poin-turun"><?= lang('Latihan.ruang.urutPoinTurun') ?></option>
                    <option value="status"><?= lang('Latihan.ruang.urutStatus') ?></option>
                </select>
            </div>
        </form>
        <ul class="kartu-soal-daftar" data-kartu-soal>
            <?php foreach ($kartu as $k): ?>
                <li class="kartu-soal status-kartu-<?= esc($k['status']) ?>" data-nomor="<?= (int) $k['nomor'] ?>" data-tingkat="<?= esc($k['tingkat']) ?>" data-status="<?= esc($k['status']) ?>" data-poin="<?= (int) $k['poin'] ?>">
                    <a href="/latihan/soal/<?= (int) $k['id'] ?>">
                        <span class="kartu-soal-nomor ltr"><?= (int) $k['nomor'] ?></span>
                        <span class="kartu-soal-judul"><?= esc($k['judul']) ?></span>
                    </a>
                    <p>
                        <span class="lencana lencana-tingkat tingkat-<?= esc(strtolower($k['tingkat'])) ?>"><?= lang('Soal.tingkat.' . $k['tingkat']) ?></span>
                        <span class="lencana"><?= lang('Latihan.ruang.poin', [(int) $k['poin']]) ?></span>
                    </p>
                    <p>
                        <span class="lencana kartu-status-<?= esc($k['status']) ?>"><?= lang('Latihan.kartu.' . $k['status']) ?></span>
                        <?php if ($k['nilai'] !== null): ?>
                            <span><?= lang('Latihan.ruang.didapat', [angka($k['nilai']), (int) $k['poin']]) ?></span>
                            <span class="redup">· <?= lang('Latihan.ruang.percobaan', [(int) $k['percobaan']]) ?></span>
                        <?php endif ?>
                    </p>
                </li>
            <?php endforeach ?>
        </ul>
        <p class="redup" data-tidak-cocok hidden><?= lang('Latihan.ruang.tidakCocok') ?></p>
    <?php endif ?>
<?php endif ?>
<?= $this->endSection() ?>
