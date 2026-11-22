<?php
/**
 * Daftar pengumuman untuk mahasiswa. Variabel: $daftar (Pengumuman::untukSaya), $orang (penulis, dikunci user id).
 * Teks polos: di-escape, baris baru dipertahankan oleh CSS (.kabar-isi).
 */
use App\Libraries\Waktu;

?>
<ul class="daftar-kabar" data-pengumuman>
<?php foreach ($daftar as $a): ?>
    <li class="kartu">
        <p><strong><?= esc($a['title']) ?></strong></p>
        <p class="kabar-isi"><?= esc($a['body']) ?></p>
        <p class="petunjuk"><span class="ltr"><?= esc($a['matkul_kode']) ?></span> <?= esc($a['kelas_nama']) ?> · <?= esc($orang[$a['author_id']]['nama'] ?? '—') ?> · <span class="ltr"><?= esc(Waktu::lokal($a['created_at'])) ?></span></p>
    </li>
<?php endforeach ?>
</ul>
