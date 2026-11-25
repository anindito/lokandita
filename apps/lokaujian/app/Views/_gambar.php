<?php
/**
 * Satu gambar soal. Alamatnya /gambar/<kunci>: dijawab aplikasi sesudah hak akses diperiksa,
 * bukan berkas statis. Variabel: $gambar (baris gambar dengan alt), $kelas (opsional: kelas CSS,
 * string). Data view terbawa dari halaman pemanggil, jadi $kelas yang bukan string diabaikan.
 */
?>
<img src="/gambar/<?= esc($gambar['kunci']) ?>" alt="<?= esc($gambar['alt']) ?>" width="<?= (int) $gambar['lebar'] ?>" height="<?= (int) $gambar['tinggi'] ?>" loading="lazy"<?php if (is_string($kelas ?? null) && $kelas !== ''): ?> class="<?= esc($kelas) ?>"<?php endif ?>>
