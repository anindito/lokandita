<?php
/**
 * Kunci satu soal, untuk staf saja — tidak pernah di dalam partial _soal. Variabel: $soal.
 */
?>
<p class="kunci-staf" data-kunci="<?= (int) $soal['id'] ?>">
    <strong><?= lang('Bank.kunci.judul') ?>:</strong>
    <?php if ($soal['type'] === 'benar_salah'): ?>
        <?= lang($soal['answer'] ? 'Bank.benar' : 'Bank.salah') ?>
    <?php elseif ($soal['type'] === 'pilihan_ganda'): ?>
        <?php foreach ($soal['options'] as $i => $o): ?><?php if ($o['correct']): ?><span class="ltr"><?= chr(65 + $i) ?></span><?php endif ?><?php endforeach ?>
    <?php else: ?>
        <span class="redup"><?= lang('Bank.kunci.dosen') ?></span>
    <?php endif ?>
</p>
