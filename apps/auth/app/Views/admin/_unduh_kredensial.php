<h2><?= lang('Admin.kredensial.judul') ?></h2>
<p><?= lang('Admin.kredensial.peringatan') ?></p>
<div class="baris-tombol">
    <a class="tombol" href="/admin/kredensial/<?= esc($token) ?>?format=xlsx" data-unduh-kredensial><?= lang('Admin.kredensial.unduhXlsx') ?></a>
    <a class="tombol tombol-sekunder" href="/admin/kredensial/<?= esc($token) ?>?format=csv"><?= lang('Admin.kredensial.unduhCsv') ?></a>
</div>
