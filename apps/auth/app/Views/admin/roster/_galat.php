<?php
/** Pesan galat di bawah satu kolom form. Variabel: $galat (kolom => kunci), $kolom. */
?>
<?php if (isset($galat[$kolom])): ?>
    <p class="galat-kolom" role="alert" id="galat-<?= esc($kolom) ?>"><?= lang('Roster.galat.' . $galat[$kolom]) ?></p>
<?php endif ?>
