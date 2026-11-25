<?php
/**
 * Satu gambar soal. Alamatnya /gambar/<kunci>: dijawab aplikasi sesudah hak akses diperiksa,
 * bukan berkas statis. Variabel: $gambar (baris gambar dengan alt), $kelas (opsional).
 */
?>
<img src="/gambar/<?= esc($gambar['kunci']) ?>" alt="<?= esc($gambar['alt']) ?>" width="<?= (int) $gambar['lebar'] ?>" height="<?= (int) $gambar['tinggi'] ?>" loading="lazy"<?php if (($kelas ?? '') !== ''): ?> class="<?= esc($kelas) ?>"<?php endif ?>>
