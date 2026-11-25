<?php
/**
 * Satu kelompok soal sebagaimana dilihat mahasiswa: bagian bersama (teks dan/atau gambar), lalu
 * soal-soalnya dalam urutan tetap. Variabel: $kelompok, $nomorAwal (nomor soal pertamanya).
 */
use App\Libraries\Markdown;
?>
<section class="kelompok" data-kelompok="<?= (int) $kelompok['id'] ?>">
    <div class="kelompok-bersama">
        <?php if ($kelompok['text'] !== ''): ?><div class="soal-teks" dir="auto"><?= Markdown::html($kelompok['text']) ?></div><?php endif ?>
        <?php if ($kelompok['image'] !== null): ?>
            <figure class="soal-gambar"><?= view('_gambar', ['gambar' => $kelompok['image']]) ?></figure>
        <?php endif ?>
    </div>
    <?php foreach ($kelompok['questions'] as $i => $s): ?>
        <?= view('_soal', ['soal' => $s, 'nomor' => $nomorAwal + $i]) ?>
    <?php endforeach ?>
</section>
