<?php
/**
 * Isian gambar di form soal, opsi atau kelompok: gambar yang terpasang (bila ada), berkas baru,
 * teks alternatif. Variabel: $id (awalan id elemen), $medan (nama isian berkas), $namaAlt,
 * $namaHapus, $alt (string), $gambar (gambar terpasang atau null).
 */
use App\Libraries\Aturan;
?>
<?php if ($gambar !== null): ?>
    <?= view('_gambar', ['gambar' => $gambar, 'kelas' => 'gambar-kecil']) ?>
    <label class="sebaris"><input type="checkbox" name="<?= esc($namaHapus) ?>" value="1"> <?= lang('Bank.medan.hapusGambar') ?></label>
<?php endif ?>
<label for="<?= esc($id) ?>-berkas"><?= lang($gambar === null ? 'Bank.medan.gambar' : 'Bank.medan.gantiGambar') ?></label>
<input type="file" id="<?= esc($id) ?>-berkas" name="<?= esc($medan) ?>" accept="image/jpeg,image/png,image/webp">
<label for="<?= esc($id) ?>-alt"><?= lang('Bank.medan.alt') ?></label>
<input type="text" id="<?= esc($id) ?>-alt" name="<?= esc($namaAlt) ?>" maxlength="<?= Aturan::ALT_MAKS ?>" value="<?= esc($alt) ?>" dir="auto">
