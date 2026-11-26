<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<?php
/**
 * Peristiwa integritas satu sesi (SPEC-LOKAUJIAN.md §9.3). Variabel: $sesi, $peristiwa
 * (Sesi::peristiwa), $orang (direktori LokaAuth). Rinciannya hanya nilai yang sudah diperiksa
 * server saat dicatat; tidak ada teks bebas dari peramban.
 */
$rinci = static function (array $d): string {
    $bagian = [];
    foreach (['jalur', 'alasan'] as $k) {
        if (is_string($d[$k] ?? null)) {
            $bagian[] = lang('SebUjian.peristiwa.detail.' . $k, [esc($d[$k])]);
        }
    }
    if (is_int($d['durasi'] ?? null)) {
        $bagian[] = lang('SebUjian.peristiwa.detail.durasi', [$d['durasi']]);
    }
    if (is_int($d['detik'] ?? null)) {
        $bagian[] = lang('SebUjian.peristiwa.detail.detik', [$d['detik']]);
    }
    if (is_int($d['lebar'] ?? null) && is_int($d['tinggi'] ?? null)) {
        $bagian[] = lang('SebUjian.peristiwa.detail.layar', [$d['lebar'], $d['tinggi']]);
    }

    return implode(' · ', $bagian);
};
$jam = static fn (string $utc): string => (new DateTimeImmutable($utc, new DateTimeZone('UTC')))->setTimezone(new DateTimeZone('Asia/Jakarta'))->format('d-m-Y H:i:s');
?>
<?= view('_nav', ['akses' => $akses]) ?>
<p><a href="/sesi/<?= (int) $sesi['id'] ?>"><span class="panah-balik" aria-hidden="true"></span> <?= lang('Sesi.kembali') ?></a></p>
<h1><?= lang('SebUjian.peristiwa.judul') ?>: <span dir="auto"><?= esc($sesi['title']) ?></span></h1>
<p class="redup"><?= lang('SebUjian.peristiwa.info') ?></p>
<?php if ($peristiwa === []): ?>
    <div class="kartu"><p><?= lang('SebUjian.peristiwa.kosong') ?></p></div>
<?php else: ?>
    <div class="tabel-wadah kartu">
        <table data-peristiwa>
            <thead><tr>
                <th scope="col"><?= lang('SebUjian.peristiwa.waktu') ?></th>
                <th scope="col"><?= lang('SebUjian.peristiwa.mahasiswa') ?></th>
                <th scope="col"><?= lang('SebUjian.peristiwa.jenis') ?></th>
                <th scope="col"><?= lang('SebUjian.peristiwa.rincian') ?></th>
            </tr></thead>
            <tbody>
            <?php foreach ($peristiwa as $p): ?>
                <?php $o = $orang[(string) $p['user_id']] ?? null; ?>
                <tr data-jenis="<?= esc($p['kind']) ?>">
                    <td class="ltr"><?= esc($jam((string) $p['created_at'])) ?></td>
                    <td><span class="ltr"><?= esc($o['nomor_induk'] ?? '—') ?></span> <?= esc($o['nama'] ?? lang('Ujian.akunHilang')) ?></td>
                    <td><?= lang('SebUjian.peristiwa.kind.' . $p['kind']) ?></td>
                    <td><?= $rinci((array) json_decode((string) $p['detail'], true)) ?></td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
<?php endif ?>
<?= $this->endSection() ?>
