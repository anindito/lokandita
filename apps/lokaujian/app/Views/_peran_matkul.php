<?php
/**
 * Peran dosen ini di sebuah mata kuliah. Variabel: $akses, $matkul (baris dengan owner_id).
 * Dosen yang melihat mata kuliah tanpa mengampunya pasti pengajar salah satu kelasnya.
 */
?>
<?php if ($matkul['owner_id'] === $akses->identitas()->userId): ?>
    <span class="lencana lencana-active"><?= lang('Ujian.matkul.andaPengampu') ?></span>
<?php elseif ($akses->dosen()): ?>
    <span class="lencana"><?= lang('Ujian.matkul.andaPengajar') ?></span>
<?php endif ?>
