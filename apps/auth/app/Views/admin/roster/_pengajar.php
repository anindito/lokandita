<?php
/**
 * Nama-nama pengajar sebuah kelas. Variabel: $daftar (list baris dari Roster::pengajar()).
 */
$nama = [];
foreach ($daftar as $p) {
    $nama[] = esc($p['nama'] ?? lang('Roster.mhs.akunHilang'));
}
?>
<?php if ($nama === []): ?>
    <span class="lencana lencana-suspended"><?= lang('Roster.kelas.tanpaPengajar') ?></span>
<?php else: ?>
    <?= implode(', ', $nama) ?>
<?php endif ?>
