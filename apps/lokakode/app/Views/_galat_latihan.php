<?php
/** Pesan galat di bawah satu kolom form (teks dari Latihan.galat.*). Variabel: $galat (kolom => kunci), $kolom. */
?>
<?php if (isset($galat[$kolom])): ?>
    <p class="galat-kolom" role="alert" id="galat-<?= esc($kolom) ?>"><?= lang('Latihan.galat.' . $galat[$kolom]) ?></p>
<?php endif ?>
