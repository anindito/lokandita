# Lembar tinjauan — PBO301 pertemuan 1

Sumber: `docs/soal/pbo301-pertemuan-1.json`. Status setelah impor: menunggu tinjauan (`PENDING_REVIEW`).

| Tingkat | Jumlah | Poin |
|---|---|---|
| Mudah sekali | 8 | 24 |
| Mudah | 8 | 40 |
| Mudah-sedang | 6 | 48 |
| Sedang | 6 | 60 |
| Sulit | 4 | 60 |
| **Semua** | **32** | **232** |

## Semua soal

- **Mudah sekali:** Terpanas dan Terdingin (`terpanas-terdingin`); Hitung Huruf Besar (`hitung-huruf-besar`); Kuadratkan dengan Lambda (`kuadratkan-lima`); Rata-rata Sepuluh Nilai (`rata-rata-sepuluh`); Ganti Spasi (`ganti-spasi`); Lampu Berikutnya (`lampu-berikutnya`); Nolkan yang Negatif (`nolkan-negatif`); Jumlah Dua Vektor (`jumlah-vektor`)
- **Mudah:** Balik Array (`balik-array`); Frekuensi Digit (`frekuensi-digit`); Nilai Tertinggi (`nilai-tertinggi`); Di Atas Rata-rata (`di-atas-rata-rata`); Kata Terpanjang (`kata-terpanjang`); Jumlah Diagonal (`jumlah-diagonal`); Terbesar Kedua (`terbesar-kedua`); Hapus Huruf Vokal (`hapus-vokal`)
- **Mudah-sedang:** Urutkan Array (`urutkan-array`); Median (`median`); Total Durasi (`total-durasi`); Gabung Dua Daftar Terurut (`gabung-terurut`); Modus (`modus`); Rotasi ke Kiri (`rotasi-kiri`)
- **Sedang:** Perkalian Matriks (`kali-matriks`); Peringkat Kelas (`peringkat-kelas`); Jumlah Bilangan Besar (`jumlah-bilangan-besar`); Segitiga Pascal (`segitiga-pascal`); Anagram (`anagram`); Antrean Bank (`antrean-bank`)
- **Sulit:** Spiral Matriks (`spiral-matriks`); Permainan Kehidupan (`permainan-hidup`); Sudoku Sah (`sudoku-sah`); Ratu Pertama Sudah Ditaruh (`ratu-pertama`)

## Pernyataan lengkap: dua soal per tingkat

### Mudah sekali — Terpanas dan Terdingin (`terpanas-terdingin`)

Suhu sebuah kota dicatat selama tujuh hari. Baca ketujuh suhu itu, lalu cetak suhu tertinggi dan suhu terendah.

Simpan datanya di array dan telusuri dengan *range-based for*: `for (auto s : suhu)` mengunjungi setiap elemen tanpa indeks. Untuk mengisi array, pakai `auto &s` supaya `s` merujuk ke elemennya, bukan salinannya.

Untuk `30 28 33 25 31 29 27` keluarannya `33 25`.

**Masukan.** Satu baris berisi tujuh bilangan bulat.

**Keluaran.** Satu baris: suhu tertinggi, lalu suhu terendah.

**Batasan.** Tiap suhu antara −10 dan 45.

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah sekali — Jumlah Dua Vektor (`jumlah-vektor`)

Sebuah vektor di bidang punya dua komponen, x dan y. Jumlah dua vektor diperoleh dengan menjumlahkan komponen yang bersesuaian.

Buat `struct Vektor { int x; int y; };` dan fungsi `Vektor tambah(Vektor a, Vektor b)`. Dengan inisialisasi seragam, sebuah vektor bisa dibuat langsung: `Vektor{3, 4}`.

Baca dua vektor dan cetak jumlahnya.

Contoh: (3, 4) + (−1, 6) = (2, 10), dicetak `2 10`.

**Masukan.** Dua baris, masing-masing berisi komponen x dan y sebuah vektor.

**Keluaran.** Satu baris: komponen x dan y hasil penjumlahan.

**Batasan.** Tiap komponen antara −100 dan 100.

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah — Balik Array (`balik-array`)

Baca n bilangan bulat, lalu cetak semuanya dalam urutan terbalik.

Banyak datanya baru diketahui saat program berjalan, jadi arraynya dipesan dengan `new int[n]` dan dikembalikan dengan `delete[]`. Setelah itu penunjuknya diisi `nullptr`, penanda "tidak menunjuk ke mana-mana" di C++ modern (pengganti `NULL`).

Untuk `3 8 -2 5` keluarannya `5 -2 8 3`.

**Masukan.** Baris pertama: n. Baris kedua: n bilangan bulat.

**Keluaran.** Satu baris: bilangan-bilangan itu dalam urutan terbalik.

**Batasan.** 1 ≤ n ≤ 100; tiap bilangan antara −1000 dan 1000

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah — Hapus Huruf Vokal (`hapus-vokal`)

Baca satu baris teks, lalu cetak teks itu tanpa huruf vokalnya (`a`, `i`, `u`, `e`, `o`, baik huruf kecil maupun besar). Karakter lain, termasuk spasi, dibiarkan.

Pemeriksaan "apakah ini vokal" enak ditulis sebagai lambda yang mengembalikan `bool`.

Untuk `Belajar Kode` keluarannya `Bljr Kd`.

**Masukan.** Satu baris teks.

**Keluaran.** Teks itu tanpa huruf vokal.

**Batasan.** Teks terdiri dari huruf dan spasi tunggal, paling panjang 80 karakter; setiap kata memuat paling sedikit satu konsonan.

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah-sedang — Urutkan Array (`urutkan-array`)

Baca n bilangan bulat, lalu cetak semuanya **terurut dari yang terkecil**.

Tulis sendiri pengurutannya (misalnya *selection sort*: cari yang terkecil di bagian yang belum terurut, tukar ke depan, ulangi). Pustaka pengurutan baru dipakai di pertemuan-pertemuan akhir.

Untuk `5 -1 9 3 3` keluarannya `-1 3 3 5 9`.

**Masukan.** Baris pertama: n. Baris kedua: n bilangan bulat.

**Keluaran.** Satu baris: bilangan-bilangan itu, terurut naik.

**Batasan.** 1 ≤ n ≤ 100; tiap bilangan antara −1000 dan 1000

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah-sedang — Rotasi ke Kiri (`rotasi-kiri`)

Merotasi sebuah daftar ke kiri satu langkah berarti memindahkan elemen pertama ke ujung belakang: `1 2 3 4 5` menjadi `2 3 4 5 1`.

Baca n bilangan dan bilangan k, lalu cetak daftar itu setelah dirotasi ke kiri **k langkah**. Nilai k bisa jauh lebih besar daripada n; merotasi n langkah mengembalikan daftar ke keadaan semula.

Contoh: `1 2 3 4 5` dengan k = 2 menjadi `3 4 5 1 2`; dengan k = 7 hasilnya sama.

**Masukan.** Baris pertama: n dan k. Baris kedua: n bilangan bulat.

**Keluaran.** Satu baris: daftar setelah dirotasi.

**Batasan.** 1 ≤ n ≤ 100; 0 ≤ k ≤ 1000000000; tiap bilangan antara 0 dan 99

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Sedang — Perkalian Matriks (`kali-matriks`)

Matriks A berukuran b × t dan matriks B berukuran t × k dapat dikalikan. Hasilnya matriks C berukuran b × k, dengan

**C[i][j] = A[i][1] × B[1][j] + A[i][2] × B[2][j] + … + A[i][t] × B[t][j]**

yaitu baris ke-i dari A "dikalikan" dengan kolom ke-j dari B.

Baca kedua matriks dan cetak hasil kalinya.

Contoh: A = `1 2` / `3 4` dan B = `5 6` / `7 8` menghasilkan

```
19 22
43 50
```

**Masukan.** Baris pertama: b, t, k. Lalu b baris berisi t bilangan (matriks A), lalu t baris berisi k bilangan (matriks B).

**Keluaran.** b baris, masing-masing k bilangan: matriks hasil kali.

**Batasan.** 1 ≤ b, t, k ≤ 5; tiap bilangan antara −9 dan 9

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Sedang — Antrean Bank (`antrean-bank`)

Tirukan antrean di sebuah bank. Perintah datang satu per baris:

- `datang <nama>` — seorang nasabah masuk ke ujung belakang antrean;
- `layani` — nasabah terdepan dilayani dan keluar dari antrean;
- `selesai` — tidak ada perintah lagi.

Untuk tiap `layani`, cetak `Melayani <nama>`; bila antreannya kosong, cetak `Antrean kosong`. Di akhir, cetak `Sisa antrean: k` dengan k banyak nasabah yang belum dilayani.

Contoh masukan:

```
datang Budi
datang Citra
layani
layani
layani
datang Eko
selesai
```

Keluarannya:

```
Melayani Budi
Melayani Citra
Antrean kosong
Sisa antrean: 1
```

Antrean cocok disimpan sebagai *linked list*: simpul dibuat dengan `new`, dihapus dengan `delete`, dan ujung daftar ditandai `nullptr`.

**Masukan.** Beberapa baris perintah, diakhiri `selesai`.

**Keluaran.** Satu baris untuk tiap `layani`, lalu satu baris `Sisa antrean: k`.

**Batasan.** Paling banyak 30 perintah sebelum `selesai`; nama satu kata.

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Sulit — Spiral Matriks (`spiral-matriks`)

Baca sebuah matriks, lalu cetak semua isinya **menurut jalur spiral**: mulai dari kiri atas, ke kanan sepanjang baris teratas, turun sepanjang kolom terkanan, ke kiri sepanjang baris terbawah, naik sepanjang kolom terkiri, lalu masuk ke lapisan dalam, dan seterusnya.

Untuk matriks

```
1 2 3 4
5 6 7 8
9 10 11 12
```

keluarannya `1 2 3 4 8 12 11 10 9 5 6 7`.

Hati-hati dengan matriks yang hanya punya satu baris atau satu kolom: tidak ada elemen yang boleh tercetak dua kali.

**Masukan.** Baris pertama: banyak baris dan banyak kolom. Lalu isi matriks, baris demi baris.

**Keluaran.** Satu baris: semua elemen dalam urutan spiral.

**Batasan.** 1 ≤ baris, kolom ≤ 10; tiap elemen antara 0 dan 99

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Sulit — Ratu Pertama Sudah Ditaruh (`ratu-pertama`)

Dalam teka-teki *n ratu*, n buah ratu catur harus ditaruh di papan n × n sehingga **tidak ada dua ratu yang saling menyerang**: tidak sebaris, tidak sekolom, dan tidak sediagonal.

Ratu di baris pertama sudah ditaruh di kolom k. Dengan berapa cara ratu-ratu di baris lainnya dapat ditaruh?

Contoh: untuk papan 4 × 4, ratu pertama di kolom 2 memberi 1 cara, sedangkan ratu pertama di kolom 1 memberi 0 cara. Untuk papan 8 × 8 dengan ratu pertama di kolom 1 ada 4 cara.

Gagasan: taruh ratu baris demi baris dengan fungsi rekursif. Di tiap baris, coba setiap kolom; bila aman terhadap semua ratu di baris-baris sebelumnya, lanjutkan ke baris berikutnya. Kolom ratu tiap baris cukup dicatat dalam sebuah array.

**Masukan.** Satu baris berisi n dan k.

**Keluaran.** Satu bilangan bulat: banyak cara.

**Batasan.** 1 ≤ k ≤ n ≤ 9

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.
