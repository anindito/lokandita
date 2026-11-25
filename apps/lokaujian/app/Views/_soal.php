<?php
/**
 * SATU soal sebagaimana dilihat mahasiswa (SPEC-LOKAUJIAN.md §5.4: pratinjau memakai partial
 * yang sama dengan halaman ujian). Tidak memuat kunci, skor atau petunjuk kebenaran apa pun.
 *
 * Variabel: $soal (bentuk App\Libraries\Bank, atau salinan beku ujian terbit), $nomor (int),
 * $jawab (opsional; halaman ujian saja): jawaban tersimpan mahasiswa itu — option_id, benar, text.
 * Nilai tombol radio pilihan ganda adalah id opsinya, bukan urutannya: urutan boleh diacak per mahasiswa.
 * Teks adalah Markdown; arah tulisnya mengikuti isinya (dir="auto").
 */
use App\Controllers\Soal;
use App\Libraries\Markdown;

$nama  = 'jawab-' . (int) $soal['id'];
$jawab = $jawab ?? null;
?>
<article class="soal" data-soal="<?= (int) $soal['id'] ?>" data-tipe="<?= esc($soal['type']) ?>">
    <div class="soal-kepala">
        <span class="soal-nomor"><?= lang('Bank.soal.nomor', [(int) $nomor]) ?></span>
        <span class="lencana"><?= lang('Bank.tipe.' . $soal['type']) ?></span>
        <span class="redup"><?= lang('Bank.soal.poin', [esc(Soal::poinTampil($soal['points']))]) ?></span>
    </div>
    <div class="soal-teks" dir="auto"><?= Markdown::html($soal['text']) ?></div>
    <?php if ($soal['image'] !== null): ?>
        <figure class="soal-gambar"><?= view('_gambar', ['gambar' => $soal['image']]) ?></figure>
    <?php endif ?>

    <?php if ($soal['type'] === 'benar_salah'): ?>
        <fieldset class="soal-jawab">
            <legend class="redup"><?= lang('Bank.soal.jawaban') ?></legend>
            <label class="opsi"><input type="radio" name="<?= $nama ?>" value="benar"<?= ($jawab['benar'] ?? null) === true ? ' checked' : '' ?>> <span><?= lang('Bank.benar') ?></span></label>
            <label class="opsi"><input type="radio" name="<?= $nama ?>" value="salah"<?= ($jawab['benar'] ?? null) === false ? ' checked' : '' ?>> <span><?= lang('Bank.salah') ?></span></label>
        </fieldset>
    <?php elseif ($soal['type'] === 'pilihan_ganda'): ?>
        <fieldset class="soal-jawab">
            <legend class="redup"><?= lang('Bank.soal.pilihSatu') ?></legend>
            <?php foreach ($soal['options'] as $i => $o): ?>
                <label class="opsi">
                    <input type="radio" name="<?= $nama ?>" value="<?= (int) ($o['id'] ?? $i) ?>"<?= $jawab !== null && $jawab['option_id'] !== null && $jawab['option_id'] === ($o['id'] ?? null) ? ' checked' : '' ?>>
                    <span class="opsi-huruf ltr"><?= chr(65 + $i) ?>.</span>
                    <span class="opsi-isi" dir="auto">
                        <?= $o['text'] === '' ? '' : Markdown::html($o['text']) ?>
                        <?php if ($o['image'] !== null): ?><?= view('_gambar', ['gambar' => $o['image']]) ?><?php endif ?>
                    </span>
                </label>
            <?php endforeach ?>
        </fieldset>
    <?php elseif ($soal['type'] === 'isian'): ?>
        <label for="<?= $nama ?>" class="redup"><?= lang('Bank.soal.jawaban') ?></label>
        <input type="text" id="<?= $nama ?>" name="<?= $nama ?>" maxlength="200" dir="auto" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" value="<?= esc($jawab['text'] ?? '') ?>">
    <?php else: ?>
        <label for="<?= $nama ?>" class="redup"><?= lang('Bank.soal.jawaban') ?></label>
        <textarea id="<?= $nama ?>" name="<?= $nama ?>" rows="8" maxlength="20000" dir="auto" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"><?= esc($jawab['text'] ?? '') ?></textarea>
    <?php endif ?>
</article>
