<?php
/** Kolom form tahun ajaran. Variabel: $isi, $galat. */
?>
<label for="label"><?= lang('Kode.tahun.nama') ?></label>
<input type="text" id="label" name="label" value="<?= esc($isi['label']) ?>" maxlength="40" required>
<?= view('_galat', ['galat' => $galat, 'kolom' => 'label']) ?>

<label for="starts_on"><?= lang('Kode.tahun.mulai') ?></label>
<input type="date" id="starts_on" name="starts_on" class="ltr" value="<?= esc($isi['starts_on']) ?>" required>
<?= view('_galat', ['galat' => $galat, 'kolom' => 'starts_on']) ?>

<label for="ends_on"><?= lang('Kode.tahun.selesai') ?></label>
<input type="date" id="ends_on" name="ends_on" class="ltr" value="<?= esc($isi['ends_on']) ?>" required>
<?= view('_galat', ['galat' => $galat, 'kolom' => 'ends_on']) ?>
