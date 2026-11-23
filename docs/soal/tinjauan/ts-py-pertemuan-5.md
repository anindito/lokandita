# Lembar tinjauan — TS-PY pertemuan 5

Sumber: `docs/soal/ts-py-pertemuan-5.json`. Status setelah impor: menunggu tinjauan (`PENDING_REVIEW`).

| Tingkat | Jumlah | Poin |
|---|---|---|
| Mudah sekali | 8 | 24 |
| Mudah | 8 | 40 |
| Mudah-sedang | 6 | 48 |
| Sedang | 6 | 60 |
| Sulit | 4 | 60 |
| **Semua** | **32** | **232** |

## Semua soal

- **Mudah sekali:** Daftar Elevasi Dibalik (`elevasi-terbalik`); Jumlah Beban Terpusat (`total-beban`); Nama Proyek Huruf Kapital (`nama-kapital`); Mutu Beton dari Kode Benda Uji (`mutu-dari-kode`); Fungsi Luas Pelat (`fungsi-luas-pelat`); Daftar Harga Material (`harga-material`); Bacaan Terkecil dan Terbesar (`min-maks-bacaan`); Mengambil Bacaan Tertentu (`bacaan-ke`)
- **Mudah:** Benda Uji di Atas Rata-rata (`di-atas-rata`); Median Hasil Uji (`median-kuat-tekan`); Fungsi Volume Pondasi Telapak (`fungsi-volume-telapak`); Mencacah Golongan Kendaraan (`hitung-golongan`); Diameter Besi yang Dipakai (`diameter-unik`); Stasiun ke Meter (`sta-ke-meter`); Singkatan Nama Proyek (`inisial-proyek`); Selisih Elevasi Terpasang dari Rencana (`deviasi-rencana`)
- **Mudah-sedang:** Rekap Panjang Besi per Diameter (`rekap-besi`); Kurva Massa Hujan (`kurva-massa-hujan`); Daftar Benda Uji yang Gagal (`benda-uji-gagal`); Memeriksa Kode Gambar (`kode-gambar`); Fungsi Kemiringan Memanjang (`fungsi-kemiringan`); Rata-rata Tanpa Nilai Ekstrem (`rata-tanpa-ekstrem`)
- **Sedang:** Interpolasi Tabel Tampungan Waduk (`interpolasi-tabel`); Histogram Curah Hujan (`histogram-hujan`); Material yang Kurang (`material-kurang`); Titik Berat dan Momen Inersia Penampang Tersusun (`inersia-komposit`); Volume Galian dari Petak-petak (`grid-galian`); Peringkat Penawaran (`peringkat-penawaran`)
- **Sulit:** Luas dan Keliling Lahan Poligon (`luas-poligon`); Rekap Hasil Uji per Mutu Beton (`rekap-per-mutu`); Jadwal Proyek: Hitungan Maju (`jadwal-maju`); Rekap Log Ritase Truk (`log-ritase`)

## Pernyataan lengkap: dua soal per tingkat

### Mudah sekali — Daftar Elevasi Dibalik (`elevasi-terbalik`)

Juru ukur mencatat elevasi dari hulu ke hilir, tetapi gambar memerlukannya dari hilir ke hulu. Baca semua elevasi ke dalam sebuah list, lalu cetak dalam urutan terbalik.

Contoh: `120 135 128` menghasilkan `128 135 120`.

**Masukan.** Satu baris: beberapa elevasi (cm, bilangan bulat) dipisah satu spasi.

**Keluaran.** Satu baris: elevasi yang sama dalam urutan terbalik, dipisah satu spasi.

**Batasan.** 1 sampai 15 elevasi; setiap elevasi 100 sampai 2500.

Mode banding `trimmed`, batas waktu 5000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah sekali — Mengambil Bacaan Tertentu (`bacaan-ke`)

Pos duga air mencatat debit beberapa kali sehari. Simpan catatan itu dalam list, lalu cetak bacaan dengan nomor urut yang diminta. Nomor urut dimulai dari 1, sedangkan indeks list dimulai dari 0.

Contoh: bacaan `85 92 110 97` dan nomor `3` menghasilkan `Bacaan ke-3: 110`.

**Masukan.** Baris pertama: beberapa bacaan debit (liter/detik, bilangan bulat) dipisah satu spasi. Baris kedua: nomor urut yang diminta.

**Keluaran.** Satu baris berbentuk `Bacaan ke-K: X`.

**Batasan.** 1 sampai 15 bacaan; nomor urut selalu ada.

Mode banding `trimmed`, batas waktu 5000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah — Benda Uji di Atas Rata-rata (`di-atas-rata`)

Dari sederet hasil uji kuat tekan, hitung rata-ratanya, lalu hitung berapa hasil yang lebih besar dari rata-rata itu. Karena rata-rata baru diketahui setelah semua data dibaca, data perlu disimpan dalam list.

Contoh: `300 320 280 340` menghasilkan

```
Rata-rata: 310.00
Di atas rata-rata: 2
```

**Masukan.** Satu baris: beberapa hasil uji (kg/cm², bilangan bulat) dipisah satu spasi.

**Keluaran.** Dua baris: `Rata-rata: R` (dua angka di belakang titik), lalu `Di atas rata-rata: K`.

**Batasan.** 2 sampai 20 hasil; setiap hasil 180 sampai 420.

Mode banding `trimmed`, batas waktu 5000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah — Selisih Elevasi Terpasang dari Rencana (`deviasi-rencana`)

Setelah pemadatan, elevasi terpasang di setiap titik dibandingkan dengan elevasi rencana. Untuk setiap titik hitung selisihnya (terpasang dikurangi rencana; boleh negatif), cetak semua selisih dalam satu baris, lalu banyaknya titik yang selisihnya bukan nol.

Contoh: rencana `500 520 540` dan terpasang `502 519 540` menghasilkan

```
2 -1 0
Menyimpang: 2
```

**Masukan.** Baris pertama: elevasi rencana tiap titik. Baris kedua: elevasi terpasang tiap titik, sama banyaknya. Semuanya bilangan bulat (mm) dipisah satu spasi.

**Keluaran.** Baris pertama: selisih tiap titik dipisah satu spasi. Baris kedua: `Menyimpang: K`.

**Batasan.** 1 sampai 15 titik; selisih paling besar 9 mm.

Mode banding `trimmed`, batas waktu 5000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah-sedang — Rekap Panjang Besi per Diameter (`rekap-besi`)

Daftar kebutuhan besi berisi banyak baris; setiap baris menyebut diameter dan panjang satu batang. Jumlahkan panjang untuk setiap diameter dengan dictionary, lalu cetak rekapnya terurut dari diameter terkecil.

Contoh: masukan

```
4
13 6
10 12
13 9
10 4
```

menghasilkan

```
D10: 16 m
D13: 15 m
```

**Masukan.** Baris pertama: banyaknya baris data N. Lalu N baris, masing-masing diameter (mm) dan panjang (m), bilangan bulat dipisah spasi.

**Keluaran.** Satu baris untuk setiap diameter yang muncul, berbentuk `Dd: P m`, terurut dari diameter terkecil.

**Batasan.** N 1 sampai 25; diameter 8 sampai 25; panjang 1 sampai 12.

Mode banding `trimmed`, batas waktu 5000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah-sedang — Rata-rata Tanpa Nilai Ekstrem (`rata-tanpa-ekstrem`)

Supaya satu benda uji yang cacat tidak merusak hasil, laboratorium membuang satu nilai terkecil dan satu nilai terbesar, lalu merata-ratakan sisanya. Bila nilai terkecil atau terbesar muncul lebih dari sekali, hanya satu yang dibuang. Urutkan list, ambil irisannya, lalu cetak kedua nilai yang dibuang dan rata-rata sisanya (dua angka di belakang titik).

Contoh: `300 250 310 290 400` menghasilkan

```
Dibuang: 250 dan 400
Rata-rata: 300.00
```

**Masukan.** Satu baris: beberapa hasil uji (kg/cm², bilangan bulat) dipisah satu spasi.

**Keluaran.** Baris pertama: `Dibuang: A dan B` (terkecil lalu terbesar). Baris kedua: `Rata-rata: R` dengan dua angka di belakang titik.

**Batasan.** 3 sampai 15 hasil; setiap hasil 180 sampai 420.

Mode banding `trimmed`, batas waktu 5000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Sedang — Interpolasi Tabel Tampungan Waduk (`interpolasi-tabel`)

Tabel lengkung kapasitas waduk memberi volume tampungan pada beberapa elevasi muka air, terurut naik. Untuk elevasi di antara dua baris tabel, volume dihitung dengan interpolasi linear antara baris tepat di bawah dan tepat di atasnya.

Tulis fungsi `interpolasi(x, x1, y1, x2, y2)` yang mengembalikan y1 + (x − x1) × (y2 − y1) / (x2 − x1). Simpan tabel dalam dua list, cari dua baris yang mengapit elevasi yang ditanyakan, lalu panggil fungsi itu. Bila elevasi tepat sama dengan sebuah baris tabel, hasilnya volume baris itu.

Contoh: masukan

```
3
20 100
25 400
30 900
27.0
```

menghasilkan `Volume: 600.00 ribu m3`.

Kode Anda harus mendefinisikan dan memakai paling sedikit satu fungsi (`def`); kiriman tanpa fungsi tidak diterima.

**Masukan.** Baris pertama: banyaknya baris tabel N. Lalu N baris, masing-masing elevasi (m) dan volume (ribu m³), bilangan bulat dipisah spasi, elevasi terurut naik. Baris terakhir: elevasi yang ditanyakan (m, bilangan pecahan dengan satu angka di belakang titik).

**Keluaran.** Satu baris berbentuk `Volume: V ribu m3`, dengan V dua angka di belakang titik.

**Batasan.** N 2 sampai 10; elevasi yang ditanyakan selalu di antara elevasi pertama dan terakhir tabel (keduanya termasuk).

**Konsep yang wajib ada di kode mahasiswa.** `FUNGSI`

Mode banding `trimmed`, batas waktu 5000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Sedang — Peringkat Penawaran (`peringkat-penawaran`)

Panitia tender menerima penawaran harga dari beberapa perusahaan dan menyusun peringkatnya: harga terendah di peringkat 1. Bila dua harga sama, perusahaan yang namanya lebih dahulu menurut abjad didahulukan. Simpan setiap penawaran sebagai tuple `(harga, nama)` dalam sebuah list; list berisi tuple diurutkan menurut unsur pertama, lalu unsur kedua.

Contoh: masukan

```
3
PT-DAYA 950
CV-ERA 900
PT-ADHI 950
```

menghasilkan

```
1. CV-ERA 900
2. PT-ADHI 950
3. PT-DAYA 950
```

**Masukan.** Baris pertama: banyaknya penawaran N. Lalu N baris, masing-masing nama perusahaan (tanpa spasi, huruf kapital dan tanda hubung) dan harga (juta rupiah, bilangan bulat).

**Keluaran.** N baris berbentuk `K. NAMA HARGA`, dari peringkat 1.

**Batasan.** N 1 sampai 10; nama perusahaan tidak berulang; harga 400 sampai 3005.

Mode banding `trimmed`, batas waktu 5000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Sulit — Luas dan Keliling Lahan Poligon (`luas-poligon`)

Batas sebidang lahan diukur sebagai titik-titik sudut berkoordinat (x, y) yang berurutan mengelilingi lahan; titik terakhir tersambung kembali ke titik pertama. Luasnya dihitung dengan rumus koordinat:

Luas = | jumlah (x_k × y_(k+1) − x_(k+1) × y_k) | / 2,

dijumlahkan untuk setiap titik k dengan titik berikutnya (titik setelah yang terakhir adalah yang pertama). Tanda mutlak diperlukan karena titik boleh diberikan searah atau berlawanan arah jarum jam. Keliling adalah jumlah panjang semua sisi.

Tulis fungsi `luas_poligon(xs, ys)` yang menerima list koordinat x dan list koordinat y, dan fungsi `jarak(x1, y1, x2, y2)`.

Contoh: masukan

```
4
0 0
40 0
40 30
0 30
```

menghasilkan

```
Luas: 1200.0 m2
Keliling: 140.00 m
```

Kode Anda harus mendefinisikan dan memakai paling sedikit satu fungsi (`def`); kiriman tanpa fungsi tidak diterima.

**Masukan.** Baris pertama: banyaknya titik N. Lalu N baris, masing-masing x dan y (m, bilangan bulat) dipisah spasi.

**Keluaran.** Dua baris: `Luas: A m2` (satu desimal), lalu `Keliling: K m` (dua desimal).

**Batasan.** N 3 sampai 10; koordinat 0 sampai 500; sisi-sisi lahan tidak saling berpotongan.

**Konsep yang wajib ada di kode mahasiswa.** `FUNGSI`

Mode banding `trimmed`, batas waktu 5000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Sulit — Rekap Log Ritase Truk (`log-ritase`)

Pos jaga proyek mencatat setiap truk tanah yang keluar: waktu (`JJ:MM`), nomor polisi tanpa spasi, dan volume muatannya, misalnya `07:42 BK8123AB 7`. Dari log satu hari, buat rekap:

1. untuk setiap truk: banyaknya ritase dan jumlah volumenya, terurut dari jumlah volume terbesar; bila jumlah volume sama, terurut menurut nomor polisi secara abjad;
2. jam tersibuk: jam (dua angka di depan tanda titik dua) dengan ritase terbanyak dan banyaknya ritase pada jam itu; bila ada beberapa, ambil jam yang paling awal.

Contoh: masukan

```
4
07:42 BK8123AB 7
07:55 BK9001CD 12
08:10 BK8123AB 7
08:40 BK7745EF 12
```

menghasilkan

```
BK8123AB: 2 rit, 14 m3
BK7745EF: 1 rit, 12 m3
BK9001CD: 1 rit, 12 m3
Jam tersibuk: 07 (2 rit)
```

**Masukan.** Baris pertama: banyaknya catatan N. Lalu N baris, masing-masing waktu, nomor polisi, dan volume (m³, bilangan bulat) dipisah satu spasi, terurut menurut waktu.

**Keluaran.** Satu baris `PLAT: R rit, V m3` untuk setiap truk, lalu satu baris `Jam tersibuk: JJ (K rit)`.

**Batasan.** N 1 sampai 30; paling banyak 6 truk; waktu antara 06:00 dan 23:59.

Mode banding `trimmed`, batas waktu 5000 ms, 2 tes contoh dan 10 tes tersembunyi.
