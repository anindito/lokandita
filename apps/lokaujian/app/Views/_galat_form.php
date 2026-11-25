<?php
/**
 * Daftar kesalahan isian form. Variabel: $galat (list of string).
 */
?>
<?php if ($galat !== []): ?>
    <div class="kartu" role="alert" data-galat>
        <p><strong><?= lang('Bank.galatJudul') ?></strong></p>
        <ul class="daftar-galat">
            <?php foreach ($galat as $g): ?><li><?= esc($g) ?></li><?php endforeach ?>
        </ul>
    </div>
<?php endif ?>
