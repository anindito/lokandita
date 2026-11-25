# Contoh berkas ujian LokaUjian

`ujian-contoh.json` adalah satu kuis dalam bentuk `KONTRAK.md` §20 yang memuat semua yang bisa
ditulis di bank soal: keempat tipe soal (benar/salah, pilihan ganda, isian singkat, uraian), soal
bergambar, pilihan ganda dengan opsi berupa gambar, dan satu kelompok soal dengan teks dan gambar
bersama.

- Berkasnya menyebut mata kuliah `DDP101`. Untuk mata kuliah lain, ubah nilai `"course"`.
- Impor: halaman *Ujian dan kuis* mata kuliah itu, atau
  `docker exec -i lokaujian php spark ujian:impor --oleh <nomor induk> < apps/lokaujian/contoh/ujian-contoh.json`.
- Impor selalu membuat ujian baru berstatus draf. Tidak ada yang sampai ke mahasiswa.
- Berkas ini sudah berbentuk baku: mengimpornya lalu mengekspor ujiannya menghasilkan berkas yang
  sama byte demi byte (`tests/Berkas/ImporEksporTest.php`).
- Berkas ini memuat kunci jawabannya, seperti setiap ekspor ujian.
