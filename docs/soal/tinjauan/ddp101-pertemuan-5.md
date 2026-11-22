# Lembar tinjauan — DDP101 pertemuan 5

Sumber: `docs/soal/ddp101-pertemuan-5.json`. Status setelah impor: menunggu tinjauan (`PENDING_REVIEW`).

| Tingkat | Jumlah | Poin |
|---|---|---|
| Mudah sekali | 8 | 24 |
| Mudah | 8 | 40 |
| Mudah-sedang | 6 | 48 |
| Sedang | 6 | 60 |
| Sulit | 4 | 60 |
| **Semua** | **32** | **232** |

## Semua soal

- **Mudah sekali:** Hitung Sampai n (`hitung-sampai-n`); Hitung Mundur (`hitung-mundur`); Jumlah n Bilangan (`jumlah-n-bilangan`); Garis Bintang (`garis-bintang`); Tabel Perkalian (`tabel-perkalian`); Bilangan Genap sampai n (`genap-sampai-n`); Pangkat (`pangkat`); Hitung yang Positif (`hitung-positif`)
- **Mudah:** Rata-rata n Nilai (`rata-rata-n`); Terbesar dari n Bilangan (`terbesar-dari-n`); Jumlah Digit (`jumlah-digit`); Hitung Huruf Vokal (`hitung-vokal`); Persegi Bintang (`persegi-bintang`); Baca sampai Nol (`baca-sampai-nol`); Balik Kata (`balik-kata`); Jumlah Kelipatan 3 atau 5 (`jumlah-kelipatan`)
- **Mudah-sedang:** Segitiga Bintang (`segitiga-bintang`); Bilangan Prima (`bilangan-prima`); Faktor Persekutuan Terbesar (`fpb`); Cari yang Pertama (`cari-pertama`); Balik Bilangan (`balik-bilangan`); Langkah Collatz (`langkah-collatz`)
- **Sedang:** Kompres Huruf (`kompres-huruf`); Faktor Prima (`faktor-prima`); Statistik Nilai (`statistik-nilai`); Kalimat Palindrom (`palindrom-kalimat`); Prima di Rentang (`prima-di-rentang`); Papan Catur (`papan-catur`)
- **Sulit:** Ketupat Berongga (`ketupat-berongga`); Tripel Pythagoras (`tripel-pythagoras`); Mesin ATM (`mesin-atm`); Runtun Naik Terpanjang (`runtun-naik-terpanjang`)

## Pernyataan lengkap: dua soal per tingkat

### Mudah sekali — Hitung Sampai n (`hitung-sampai-n`)

Baca sebuah bilangan n, lalu cetak bilangan 1 sampai n, masing-masing di baris sendiri.

**Masukan.** Satu bilangan bulat n.

**Keluaran.** n baris: 1, 2, …, n.

**Batasan.** 1 ≤ n ≤ 20

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah sekali — Hitung yang Positif (`hitung-positif`)

Baca n bilangan bulat, lalu cetak berapa banyak di antaranya yang **positif** (lebih besar dari nol). Nol tidak termasuk positif.

Untuk bilangan `3 -2 0 8` jawabannya 2.

**Masukan.** Baris pertama: n. Baris kedua: n bilangan bulat.

**Keluaran.** Satu bilangan: banyak bilangan positif.

**Batasan.** 1 ≤ n ≤ 12; tiap bilangan antara −50 dan 50

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah — Rata-rata n Nilai (`rata-rata-n`)

Baca n nilai ujian, lalu cetak rata-ratanya dengan dua angka di belakang koma.

Untuk nilai `70 85 90` rata-ratanya `81.67`.

Hati-hati: jumlah dan banyak nilai adalah bilangan bulat, sehingga pembagiannya perlu diubah menjadi pembagian pecahan.

**Masukan.** Baris pertama: n. Baris kedua: n bilangan bulat.

**Keluaran.** Rata-rata dengan dua angka di belakang koma.

**Batasan.** 2 ≤ n ≤ 10; tiap nilai antara 0 dan 100

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah — Jumlah Kelipatan 3 atau 5 (`jumlah-kelipatan`)

Baca sebuah bilangan n, lalu cetak jumlah semua bilangan dari 1 sampai n yang merupakan **kelipatan 3 atau kelipatan 5**. Bilangan yang kelipatan keduanya (misalnya 15) dihitung satu kali saja.

Untuk n = 10 bilangannya 3, 5, 6, 9, 10, sehingga jawabannya 33.

**Masukan.** Satu bilangan bulat n.

**Keluaran.** Satu bilangan: jumlahnya (0 bila tidak ada).

**Batasan.** 1 ≤ n ≤ 1000

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah-sedang — Segitiga Bintang (`segitiga-bintang`)

Baca sebuah bilangan n, lalu cetak segitiga siku-siku setinggi n baris: baris pertama berisi 1 bintang, baris kedua 2 bintang, dan seterusnya.

Untuk n = 4:

```
*
**
***
****
```

**Masukan.** Satu bilangan bulat n.

**Keluaran.** n baris; baris ke-b berisi b bintang.

**Batasan.** 1 ≤ n ≤ 12

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah-sedang — Langkah Collatz (`langkah-collatz`)

Mulai dari sebuah bilangan n, ulangi langkah berikut sampai bilangannya menjadi 1:

- bila genap, bagi dua;
- bila ganjil, kalikan tiga lalu tambah satu.

Cetak berapa langkah yang diperlukan.

Contoh: dari 6 urutannya 6 → 3 → 10 → 5 → 16 → 8 → 4 → 2 → 1, yaitu 8 langkah. Dari 1 tidak perlu langkah apa pun, jadi jawabannya 0.

**Masukan.** Satu bilangan bulat n.

**Keluaran.** Banyak langkah sampai mencapai 1.

**Batasan.** 1 ≤ n ≤ 10000

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Sedang — Kompres Huruf (`kompres-huruf`)

Sebuah kata berisi huruf-huruf yang sering berulang berturutan, misalnya `aaabbc`. Kata seperti itu bisa dipendekkan dengan menulis tiap huruf **sekali**, diikuti berapa kali ia berulang berturutan.

`aaabbc` menjadi `a3b2c1`. `abca` menjadi `a1b1c1a1`: huruf `a` yang terpisah dihitung sendiri-sendiri.

Baca sebuah kata, lalu cetak bentuk pendeknya.

**Masukan.** Satu kata dari huruf kecil.

**Keluaran.** Bentuk pendek kata itu.

**Batasan.** Panjang kata paling banyak 63 huruf; satu huruf berulang paling banyak 9 kali berturutan.

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Sedang — Papan Catur (`papan-catur`)

Cetak pola papan catur berukuran b baris dan k kolom. Petak gelap ditulis `#`, petak terang ditulis `.`, dan keduanya berselang-seling ke kanan maupun ke bawah. Petak kiri atas selalu `#`.

Untuk 3 baris dan 5 kolom:

```
#.#.#
.#.#.
#.#.#
```

**Masukan.** Satu baris berisi b dan k.

**Keluaran.** b baris, masing-masing k karakter.

**Batasan.** 1 ≤ b, k ≤ 12

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Sulit — Ketupat Berongga (`ketupat-berongga`)

Cetak belah ketupat berongga dari tanda bintang. Bilangan n adalah banyak baris dari puncak sampai baris terlebar; seluruhnya ada 2n − 1 baris.

Untuk n = 3:

```
  *
 * *
*   *
 * *
  *
```

Untuk n = 1 keluarannya satu bintang saja.

Baris teratas dan terbawah berisi satu bintang; baris lainnya dua bintang dengan spasi di antaranya. Spasi di **depan** tiap baris harus tepat; jangan menulis apa pun setelah bintang terakhir di tiap baris.

**Masukan.** Satu bilangan bulat n.

**Keluaran.** 2n − 1 baris yang membentuk belah ketupat berongga.

**Batasan.** 1 ≤ n ≤ 10

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Sulit — Runtun Naik Terpanjang (`runtun-naik-terpanjang`)

Suhu sebuah kota dicatat setiap hari. Carilah **runtun hari berturutan terpanjang** yang suhunya terus naik: tiap hari harus lebih tinggi daripada hari sebelumnya (sama saja tidak dihitung naik).

Baca n catatan suhu, lalu cetak panjang runtun naik terpanjang itu.

Contoh: untuk `3 5 5 6 7 2` runtun terpanjangnya `5 6 7`, jadi jawabannya 3. Bila suhunya tidak pernah naik, jawabannya 1.

Kerjakan tanpa array: cukup ingat suhu hari sebelumnya dan panjang runtun saat ini.

**Masukan.** Baris pertama: n. Baris kedua: n bilangan bulat.

**Keluaran.** Satu bilangan: panjang runtun naik terpanjang.

**Batasan.** 1 ≤ n ≤ 41; tiap bilangan antara 1 dan 20

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.
