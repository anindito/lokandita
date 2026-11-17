<?php
/**
 * Ringkasan nilai pertemuan yang selalu terlihat mahasiswa (SPEC.md §7):
 * poin menuju target, kuota Mudah sekali, poin yang masih dibutuhkan.
 * Variabel: $nilai (dari App\Libraries\Ruang::nilai()). Elemen ber-data-* diperbarui skrip sesudah Submit.
 */
?>
<div class="ringkasan-nilai" data-ringkasan>
    <div class="ringkasan-angka">
        <span class="redup"><?= lang('Latihan.nilai.pertemuan') ?></span>
        <strong class="angka-besar ltr" data-nilai><?= angka($nilai['nilai']) ?></strong>
    </div>
    <div class="ringkasan-rinci">
        <progress max="<?= (int) $nilai['target'] ?>" value="<?= esc((string) min($nilai['terhitung'], $nilai['target'])) ?>" data-batang aria-label="<?= lang('Latihan.nilai.poin') ?>"></progress>
        <ul class="ringkasan-butir">
            <li data-poin><?= lang('Latihan.nilai.poinDari', [angka($nilai['terhitung']), (int) $nilai['target']]) ?></li>
            <li data-mudah title="<?= lang('Latihan.nilai.kuotaInfo') ?>"><?= lang('Latihan.nilai.mudahSekali', [(int) $nilai['mudahSekali']]) ?></li>
            <li data-kurang><?= $nilai['kurang'] > 0 ? lang('Latihan.nilai.kurang', [angka($nilai['kurang'])]) : lang('Latihan.nilai.tercapai') ?></li>
        </ul>
    </div>
</div>
