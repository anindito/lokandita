<?php
/**
 * Kotak centang konsep, dikelompokkan seperti docs/PETA-KONSEP.md.
 * Variabel: $katalog (baris concepts), $terkunci (kode yang diwarisi: tercentang
 * dan tidak bisa diubah), $dipilih (kode yang tercentang).
 */
use App\Libraries\Kurikulum;

$grup = [];
foreach ($katalog as $k) {
    $grup[$k['grup']][] = $k;
}
?>
<?php foreach ($grup as $nama => $isiGrup): ?>
    <fieldset class="konsep-grup">
        <legend><?= lang('Soal.konsep.grup.' . $nama) ?></legend>
        <?php foreach ($isiGrup as $k): ?>
            <?php $kunci = in_array($k['code'], $terkunci, true); ?>
            <label class="centang">
                <input type="checkbox" name="konsep[]" value="<?= esc($k['code']) ?>"
                    <?= $kunci || in_array($k['code'], $dipilih, true) ? 'checked' : '' ?> <?= $kunci ? 'disabled' : '' ?>>
                <?= esc(Kurikulum::namaKonsep($k)) ?>
                <span class="redup ltr"><?= esc($k['code']) ?></span>
                <?php if ($kunci): ?><span class="lencana"><?= lang('Soal.konsep.diwarisi') ?></span><?php endif ?>
            </label>
        <?php endforeach ?>
    </fieldset>
<?php endforeach ?>
