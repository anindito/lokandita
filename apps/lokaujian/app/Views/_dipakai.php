<?php
/**
 * Ujian yang memakai sebuah soal atau kelompok. Variabel: $dipakai, $matkul.
 */
?>
<p data-dipakai>
    <strong><?= lang('Bank.kolom.dipakai') ?>:</strong>
    <?php if ($dipakai === []): ?>
        <span class="redup"><?= lang('Bank.tidakDipakai') ?></span>
    <?php else: ?>
        <?php foreach ($dipakai as $n => $u): ?><?= $n > 0 ? ', ' : '' ?><a href="/mata-kuliah/<?= (int) $matkul['id'] ?>/ujian/<?= (int) $u['id'] ?>" dir="auto"><?= esc($u['title']) ?></a><?php endforeach ?>
    <?php endif ?>
</p>
