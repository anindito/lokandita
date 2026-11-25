<?php
/**
 * Nama-nama pengajar sebuah kelas. Variabel: $daftar (list id), $orang (direktori, dikunci id).
 */
$nama = [];
foreach ($daftar as $id) {
    $nama[] = esc($orang[$id]['nama'] ?? lang('Ujian.akunHilang'));
}
?>
<?php if ($nama === []): ?>
    <span class="lencana lencana-suspended"><?= lang('Ujian.kelas.tanpaPengajar') ?></span>
<?php else: ?>
    <?= implode(', ', $nama) ?>
<?php endif ?>
