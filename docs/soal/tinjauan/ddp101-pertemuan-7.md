# Lembar tinjauan — DDP101 pertemuan 7

Sumber: `docs/soal/ddp101-pertemuan-7.json`. Status setelah impor: menunggu tinjauan (`PENDING_REVIEW`).

| Tingkat | Jumlah | Poin |
|---|---|---|
| Mudah sekali | 8 | 24 |
| Mudah | 8 | 40 |
| Mudah-sedang | 6 | 48 |
| Sedang | 6 | 60 |
| Sulit | 4 | 60 |
| **Semua** | **32** | **232** |

## Semua soal

- **Mudah sekali:** Faktorial (`faktorial`); Suku ke-n (`suku-ke-n`); Kali dengan Tambah (`kali-dengan-tambah`); Banyak Digit (`banyak-digit`); Jumlah Kuadrat 1 sampai n (`jumlah-kuadrat-deret`); Turun lalu Naik (`naik-turun`); Luas Dua Bentuk (`luas-dua-bentuk`); Bagi Dua Berulang (`bagi-dua-berulang`)
- **Mudah:** Fibonacci (`fibonacci`); Menara Hanoi: Banyak Langkah (`hanoi-langkah`); Banyak Digit Genap (`digit-genap`); Parameter Bawaan (`volume-bawaan`); Gabung atau Jumlah (`gabung-atau-jumlah`); Garis Penggaris (`penggaris`); Fungsi 91 (`fungsi-91`); Naik Tangga (`naik-tangga`)
- **Mudah-sedang:** Kombinasi (`kombinasi`); Format Waktu (`format-waktu`); Berapa Kali Dipanggil? (`hitung-panggilan`); Fungsi Bercabang (`fungsi-bercabang`); Tebak Angka (`tebak-biner`); Pemisah Ribuan (`pemisah-ribuan`)
- **Sedang:** Menara Hanoi (`menara-hanoi`); Pangkat Cepat (`pangkat-cepat`); Fungsi Ackermann (`ackermann`); Lingkaran Josephus (`josephus`); Partisi Bilangan (`partisi`); Bilangan Catalan (`catalan`)
- **Sulit:** Semua Susunan Huruf (`permutasi-huruf`); Kurung Seimbang (`kurung-seimbang`); Himpunan Cantor (`himpunan-cantor`); Kalkulator dengan Kurung (`kalkulator-rekursif`)

## Pernyataan lengkap: dua soal per tingkat

### Mudah sekali — Faktorial (`faktorial`)

Faktorial n, ditulis n!, adalah hasil kali semua bilangan dari 1 sampai n. Jadi 5! = 5 × 4 × 3 × 2 × 1 = 120. Menurut kesepakatan, 0! = 1.

Perhatikan bahwa 5! = 5 × 4!. Faktorial bisa dihitung oleh fungsi yang **memanggil dirinya sendiri** untuk bilangan yang lebih kecil, sampai tiba di kasus yang jawabannya langsung diketahui.

Tulis fungsi rekursif `int faktorial(int n)`, lalu cetak n! untuk n yang dibaca.

**Soal ini mensyaratkan rekursi**: keluaran yang benar tanpa fungsi rekursif tidak mendapat nilai penuh.

**Masukan.** Satu bilangan bulat n.

**Keluaran.** Satu bilangan bulat: n!

**Batasan.** 0 ≤ n ≤ 12

**Konsep yang wajib ada di kode mahasiswa.** `REKURSI`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah sekali — Bagi Dua Berulang (`bagi-dua-berulang`)

Sebuah bilangan dibagi dua berulang-ulang (pembagian bulat, sisanya dibuang) sampai menjadi 1. Berapa kali pembagian itu dilakukan?

Contoh: 10 → 5 → 2 → 1, jadi jawabannya 3. Untuk 1 jawabannya 0.

Tulis fungsi rekursif `int langkah(int n)` dan cetak hasilnya.

**Soal ini mensyaratkan rekursi.**

**Masukan.** Satu bilangan bulat n.

**Keluaran.** Satu bilangan bulat: banyak pembagian sampai menjadi 1.

**Batasan.** 1 ≤ n ≤ 1000000000

**Konsep yang wajib ada di kode mahasiswa.** `REKURSI`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah — Fibonacci (`fibonacci`)

Barisan Fibonacci dimulai dengan 0 dan 1; setiap suku berikutnya adalah jumlah dua suku sebelumnya: 0, 1, 1, 2, 3, 5, 8, 13, …

Suku pertama diberi nomor 0, jadi F(0) = 0, F(1) = 1, dan F(n) = F(n − 1) + F(n − 2).

Tulis fungsi rekursif `int fibonacci(int n)` dan cetak F(n). Fungsi ini memanggil dirinya **dua kali**.

F(10) = 55.

**Soal ini mensyaratkan rekursi.**

**Masukan.** Satu bilangan bulat n.

**Keluaran.** Satu bilangan bulat: F(n).

**Batasan.** 0 ≤ n ≤ 30

**Konsep yang wajib ada di kode mahasiswa.** `REKURSI`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah — Naik Tangga (`naik-tangga`)

Sebuah tangga punya n anak tangga. Sekali melangkah, Anda boleh naik **1, 2, atau 3** anak tangga. Ada berapa cara berbeda untuk sampai tepat di puncak?

Untuk n = 3 ada 4 cara: 1+1+1, 1+2, 2+1, dan 3. Untuk n = 4 ada 7 cara.

Pikirkan langkah **pertama**: setelah naik 1, tersisa n − 1 anak tangga; setelah naik 2, tersisa n − 2; setelah naik 3, tersisa n − 3. Banyak cara seluruhnya adalah jumlah ketiga kemungkinan itu. Bila tidak ada anak tangga tersisa, ada tepat satu cara: diam.

Tulis fungsi rekursif `int cara(int n)` dan cetak hasilnya.

**Soal ini mensyaratkan rekursi.**

**Masukan.** Satu bilangan bulat n.

**Keluaran.** Satu bilangan bulat: banyak cara.

**Batasan.** 1 ≤ n ≤ 25

**Konsep yang wajib ada di kode mahasiswa.** `REKURSI`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah-sedang — Kombinasi (`kombinasi`)

Dari n orang akan dipilih k orang untuk menjadi panitia. Banyak cara memilihnya ditulis C(n, k).

Perhatikan satu orang tertentu, misalnya Andi. Panitia itu memuat Andi — lalu k − 1 orang lain dipilih dari n − 1 orang sisanya — atau tidak memuat Andi, dan k orang dipilih dari n − 1 orang sisanya. Jadi:

**C(n, k) = C(n − 1, k − 1) + C(n − 1, k)**

dengan C(n, 0) = 1 dan C(n, n) = 1.

Tulis fungsi rekursif `int kombinasi(int n, int k)` dan cetak C(n, k). Contoh: C(5, 2) = 10.

**Soal ini mensyaratkan rekursi.**

**Masukan.** Satu baris berisi n dan k.

**Keluaran.** Satu bilangan bulat: C(n, k).

**Batasan.** 1 ≤ n ≤ 25; 0 ≤ k ≤ n

**Konsep yang wajib ada di kode mahasiswa.** `REKURSI`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah-sedang — Pemisah Ribuan (`pemisah-ribuan`)

Bilangan besar lebih mudah dibaca bila diberi titik setiap tiga digit dari kanan: 1234567 ditulis `1.234.567`.

Tulis fungsi rekursif `void cetak(int n)`: bilangan di bawah 1000 dicetak apa adanya; bilangan lain dicetak sebagai bagian depannya (n / 1000, oleh pemanggilan rekursif), lalu titik, lalu tiga digit terakhirnya.

Hati-hati dengan nol: 5003020 harus menjadi `5.003.020`, bukan `5.3.20`.

Contoh lain: 999 → `999`; 1000 → `1.000`.

**Soal ini mensyaratkan rekursi.**

**Masukan.** Satu bilangan bulat n.

**Keluaran.** n dengan titik pemisah ribuan.

**Batasan.** 0 ≤ n ≤ 2000000000

**Konsep yang wajib ada di kode mahasiswa.** `REKURSI`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Sedang — Menara Hanoi (`menara-hanoi`)

Tiga tiang bernama A, B, dan C. Di tiang A bertumpuk n cakram bernomor 1 (terkecil, paling atas) sampai n (terbesar, paling bawah). Pindahkan semuanya ke tiang C. Cakram dipindahkan satu per satu, dan cakram besar tidak boleh berada di atas cakram kecil.

Cara memindahkan n cakram dari tiang asal ke tiang tujuan:

1. pindahkan n − 1 cakram teratas dari asal ke tiang cadangan;
2. pindahkan cakram n dari asal ke tujuan;
3. pindahkan n − 1 cakram tadi dari cadangan ke tujuan.

Tulis fungsi rekursif `void pindah(int n, char asal, char tujuan, char cadangan)` yang mencetak setiap langkah di baris sendiri, berbentuk `Cakram k: X -> Y`.

Untuk n = 2:

```
Cakram 1: A -> B
Cakram 2: A -> C
Cakram 1: B -> C
```

**Soal ini mensyaratkan rekursi.**

**Masukan.** Satu bilangan bulat n.

**Keluaran.** Semua langkah, satu per baris, sesuai urutan cara di atas.

**Batasan.** 1 ≤ n ≤ 10

**Konsep yang wajib ada di kode mahasiswa.** `REKURSI`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Sedang — Bilangan Catalan (`catalan`)

Sebuah segi banyak cembung dengan n + 2 sisi dapat dipotong menjadi segitiga-segitiga dengan menarik diagonal yang tidak saling berpotongan. Banyak cara memotongnya adalah bilangan Catalan ke-n, C(n).

Segi empat (n = 2) bisa dipotong dengan 2 cara; segi lima (n = 3) dengan 5 cara; segi enam (n = 4) dengan 14 cara.

Bilangan Catalan memenuhi C(0) = 1 dan

**C(n) = C(0) × C(n − 1) + C(1) × C(n − 2) + … + C(n − 1) × C(0)**

Tulis fungsi rekursif `int catalan(int n)` yang menghitung jumlah itu dengan sebuah perulangan yang memanggil dirinya sendiri di dalamnya, lalu cetak C(n).

**Soal ini mensyaratkan rekursi.**

**Masukan.** Satu bilangan bulat n.

**Keluaran.** Satu bilangan bulat: C(n).

**Batasan.** 0 ≤ n ≤ 15

**Konsep yang wajib ada di kode mahasiswa.** `REKURSI`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Sulit — Semua Susunan Huruf (`permutasi-huruf`)

Baca sebuah kata yang semua hurufnya berbeda, lalu cetak **semua susunan** huruf-hurufnya, satu per baris, **dalam urutan kamus**.

Untuk `cab` keluarannya:

```
abc
acb
bac
bca
cab
cba
```

Gagasan rekursifnya: sebuah susunan terdiri dari satu huruf pilihan, diikuti susunan dari huruf-huruf sisanya. Tulis `void susun(string awalan, string sisa)`: bila `sisa` kosong, `awalan` sudah lengkap dan dicetak; bila tidak, untuk setiap huruf di `sisa` — **dicoba menurut abjad** — panggil `susun` dengan huruf itu ditambahkan ke `awalan` dan dibuang dari `sisa`.

Kata dengan 6 huruf punya 720 susunan.

**Soal ini mensyaratkan rekursi.**

**Masukan.** Satu kata dari huruf kecil yang semuanya berbeda; urutannya bisa acak.

**Keluaran.** Semua susunan hurufnya, satu per baris, urut kamus.

**Batasan.** Panjang kata 1 sampai 6 huruf.

**Konsep yang wajib ada di kode mahasiswa.** `REKURSI`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Sulit — Kalkulator dengan Kurung (`kalkulator-rekursif`)

Baca sebuah hitungan seperti `2*(3+4)-5` dan cetak hasilnya. Hitungan itu berisi bilangan bulat tak negatif, operator `+`, `-`, `*`, dan tanda kurung, tanpa spasi.

Aturannya seperti di matematika: perkalian dikerjakan sebelum penjumlahan dan pengurangan; operator yang setingkat dikerjakan dari kiri ke kanan; isi kurung dikerjakan lebih dulu.

Contoh: `2+3*4` → 14; `(2+3)*4` → 20; `9-4-3` → 2; `2*(3+(4-1))*5` → 60.

Hitungan seperti ini punya susunan yang rekursif:

- sebuah **ekspresi** adalah satu atau beberapa *suku* yang dipisah `+` atau `-`;
- sebuah **suku** adalah satu atau beberapa *faktor* yang dipisah `*`;
- sebuah **faktor** adalah sebuah bilangan, atau `(`, sebuah *ekspresi*, lalu `)`.

Tulis satu fungsi untuk tiap bagian itu. Ketiganya membaca teks yang sama dan berbagi satu penunjuk posisi, misalnya `int ekspresi(const string &s, int &posisi)`. Fungsi `faktor` memanggil `ekspresi`, yang memanggil `suku`, yang memanggil `faktor` lagi: rekursi lewat fungsi lain. Karena saling memanggil, salah satu fungsi perlu dideklarasikan lebih dulu sebelum didefinisikan.

**Soal ini mensyaratkan rekursi.**

**Masukan.** Satu baris: hitungan tanpa spasi.

**Keluaran.** Satu bilangan bulat: hasilnya.

**Batasan.** Panjang hitungan paling banyak 60 karakter; tiap bilangan di dalamnya satu digit (0 sampai 9); hasil dan semua hasil antara muat dalam `int`.

**Konsep yang wajib ada di kode mahasiswa.** `REKURSI`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.
