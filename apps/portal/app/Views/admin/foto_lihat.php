<?php
/**
 * Pratinjau satu foto pada tata letak halaman depan yang sebenarnya, dalam bahasa
 * Indonesia dan Arab (KONTRAK.md §16). Variabel: $baris (baris `foto`), $depan
 * (bahasa => data untuk publik/_depan), $tayang (foto ini sedang tayang).
 */
?>
<?= $this->extend('Lokandita\Auth\Views\tata_letak') ?>
<?= $this->section('isi') ?>
<p><a class="panah-balik" href="/admin/teks/foto"><?= lang('Sunting.foto.judul') ?></a></p>
<h1><?= lang('Sunting.foto.pratinjauJudul') ?> — <span class="ltr"><?= esc($baris['nama_asli']) ?></span></h1>

<?php if ($tayang): ?>
    <p><span class="lencana lencana-active" data-tayang><?= lang('Sunting.foto.sedangTayang') ?></span></p>
<?php else: ?>
    <form method="post" action="/admin/teks/foto/<?= (int) $baris['id'] ?>/tayang" class="kartu">
        <?= csrf_field() ?>
        <p><?= lang('Sunting.foto.tayangPenjelasan') ?></p>
        <div class="baris-tombol">
            <button type="submit" class="tombol"><?= lang($baris['published_at'] === null ? 'Sunting.foto.tombolTayang' : 'Sunting.foto.tombolLagi') ?></button>
            <a href="/admin/teks/foto"><?= lang('Sunting.batal') ?></a>
        </div>
    </form>
<?php endif ?>

<?php foreach ($depan as $b => $data): ?>
    <section class="pratinjau" aria-label="<?= lang('Sunting.pratinjau') ?> — <?= lang('Lokandita.bahasa' . ucfirst($b)) ?>">
        <p class="pratinjau-label"><strong><?= lang('Sunting.pratinjau') ?></strong> — <span lang="<?= $b ?>"><?= lang('Lokandita.bahasa' . ucfirst($b)) ?></span></p>
        <div dir="<?= \Lokandita\Auth\Locale::arah($b) ?>" lang="<?= $b ?>" data-pratinjau="<?= $b ?>">
            <?= view('publik/_depan', $data) ?>
            <p class="kaki-kredit"><?= \Lokandita\Auth\Ikon::svg('camera') ?> <?= lang('Lokandita.kreditFoto', [esc($data['foto']['sumber'])], $b) ?></p>
        </div>
    </section>
<?php endforeach ?>
<?= $this->endSection() ?>
