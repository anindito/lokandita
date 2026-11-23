# Lembar tinjauan — TS-PY pertemuan 4

Sumber: `docs/soal/ts-py-pertemuan-4.json`. Status setelah impor: menunggu tinjauan (`PENDING_REVIEW`).

| Tingkat | Jumlah | Poin |
|---|---|---|
| Mudah sekali | 8 | 24 |
| Mudah | 8 | 40 |
| Mudah-sedang | 6 | 48 |
| Sedang | 6 | 60 |
| Sulit | 4 | 60 |
| **Semua** | **32** | **232** |

## Semua soal

- **Mudah sekali:** Total Volume Angkutan (`total-ritase`); Posisi Patok di Sepanjang Jalan (`patok-berjarak`); Tabel Inci ke Milimeter (`tabel-inci`); Rata-rata Kuat Tekan (`rata-kuat-tekan`); Banyaknya Hari Hujan (`hari-hujan-sebulan`); Beban Gandar Terbesar (`beban-terbesar`); Ketinggian Setiap Anak Tangga (`anak-tangga`); Menjumlah Muatan sampai Angka Nol (`muatan-sampai-nol`)
- **Mudah:** Rekap Curah Hujan (`rekap-hujan`); Memompa Genangan (`pompa-banjir`); Persentase Benda Uji yang Lulus (`persen-lulus`); Tabel Kecepatan Rencana (`tabel-kecepatan`); Jam Puncak Lalu Lintas (`jam-puncak`); Kapan Memesan Semen Lagi (`stok-semen`); Pertumbuhan Lalu Lintas (`pertumbuhan-lhr`); Total Tanjakan dan Turunan (`naik-turun`)
- **Mudah-sedang:** Deret Hari Kering Terpanjang (`kemarau-terpanjang`); Luas Penampang Sungai (`luas-penampang-sungai`); Dua Truk Tiba Bersamaan (`truk-bersamaan`); Penyusutan Nilai Alat Berat (`penyusutan-alat`); Koordinat Tiang Pancang (`grid-tiang`); Menyaring Bacaan Sensor Muka Air (`saring-sensor`)
- **Sedang:** Denah Titik Kolom (`denah-kolom`); Cicilan Pembelian Alat (`cicilan-alat`); Hujan Tiga Harian Maksimum (`hujan-tiga-hari`); Memilih Tulangan Balok (`pilih-tulangan`); Memotong Besi Berurutan (`potong-besi-berurutan`); Ubin Persegi Terbesar (`ubin-terbesar`)
- **Sulit:** Simulasi Tampungan Embung (`routing-waduk`); Kedalaman Normal dengan Metode Bagi Dua (`kedalaman-biseksi`); Kombinasi Truk Bermuatan Penuh (`kombinasi-truk`); Antrean Truk Mixer di Pompa Beton (`antrean-mixer`)

## Pernyataan lengkap: dua soal per tingkat

### Mudah sekali — Total Volume Angkutan (`total-ritase`)

Mandor mencatat volume tanah setiap ritase truk. Jumlahkan semua ritase hari itu.

Contoh: masukan `3`, lalu `7`, `7`, `5` menghasilkan `Total: 19 m3`.

**Masukan.** Baris pertama: banyaknya ritase N. Lalu N baris, masing-masing volume satu ritase (m³, bilangan bulat). Panggil `input()` tanpa teks di dalam kurungnya.

**Keluaran.** Satu baris berbentuk `Total: X m3`.

**Batasan.** N 1 sampai 30; setiap volume 3 sampai 12.

Mode banding `trimmed`, batas waktu 5000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah sekali — Menjumlah Muatan sampai Angka Nol (`muatan-sampai-nol`)

Petugas gudang mengetik berat setiap kiriman material satu per satu dan menutup daftar dengan angka `0`. Banyaknya kiriman tidak diketahui sebelumnya. Jumlahkan semua berat sebelum angka 0 itu. Bila angka pertama sudah 0, totalnya 0.

Contoh: masukan `1200`, `800`, `1500`, `0` menghasilkan `Total: 3500 kg`.

**Masukan.** Beberapa baris, masing-masing satu berat (kg, bilangan bulat positif), diakhiri satu baris berisi `0`. Panggil `input()` tanpa teks di dalam kurungnya.

**Keluaran.** Satu baris berbentuk `Total: X kg`.

**Batasan.** Paling banyak 25 kiriman; setiap berat 50 sampai 1500.

Mode banding `trimmed`, batas waktu 5000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah — Rekap Curah Hujan (`rekap-hujan`)

Dari catatan curah hujan harian, buat rekap: jumlah seluruhnya, rata-rata per hari (satu angka di belakang titik), dan curah hujan harian terbesar.

Contoh: masukan `4`, lalu `10`, `0`, `25`, `7` menghasilkan

```
Total: 42 mm
Rata-rata: 10.5 mm
Maksimum: 25 mm
```

**Masukan.** Baris pertama: banyaknya hari N. Lalu N baris, masing-masing curah hujan satu hari (mm, bilangan bulat). Panggil `input()` tanpa teks di dalam kurungnya.

**Keluaran.** Tiga baris: `Total: T mm`, `Rata-rata: R mm` (satu desimal), `Maksimum: M mm`.

**Batasan.** N 3 sampai 31; curah hujan 0 sampai 120.

Mode banding `trimmed`, batas waktu 5000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah — Total Tanjakan dan Turunan (`naik-turun`)

Elevasi sumbu jalan diukur di titik-titik yang berurutan. Hitung jumlah seluruh kenaikan dan jumlah seluruh penurunan di antara titik-titik yang bersebelahan.

Contoh: masukan `5`, lalu `100`, `130`, `120`, `160`, `150` menghasilkan

```
Naik: 70 cm
Turun: 20 cm
```

(naik 30 dan 40; turun 10 dan 10).

**Masukan.** Baris pertama: banyaknya titik N. Lalu N baris elevasi berurutan (cm, bilangan bulat). Panggil `input()` tanpa teks di dalam kurungnya.

**Keluaran.** Dua baris: `Naik: A cm`, lalu `Turun: B cm`.

**Batasan.** N 2 sampai 25; beda elevasi dua titik bersebelahan paling banyak 80.

Mode banding `trimmed`, batas waktu 5000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah-sedang — Deret Hari Kering Terpanjang (`kemarau-terpanjang`)

Untuk merencanakan penyiraman timbunan, pelaksana ingin tahu deret hari kering terpanjang: berapa hari berturut-turut paling lama curah hujannya 0. Bila tidak ada hari kering, jawabannya 0.

Contoh: masukan `8`, lalu `0`, `0`, `5`, `0`, `0`, `0`, `12`, `0` menghasilkan `Kering terpanjang: 3 hari`.

**Masukan.** Baris pertama: banyaknya hari N. Lalu N baris curah hujan harian (mm, bilangan bulat; 0 berarti kering). Panggil `input()` tanpa teks di dalam kurungnya.

**Keluaran.** Satu baris berbentuk `Kering terpanjang: K hari`.

**Batasan.** N 5 sampai 40; curah hujan 0 sampai 90.

Mode banding `trimmed`, batas waktu 5000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah-sedang — Menyaring Bacaan Sensor Muka Air (`saring-sensor`)

Sensor muka air sungai mengirim bacaan tinggi air dalam sentimeter. Bila sensor gagal membaca, ia mengirim `-1`. Lewati bacaan yang gagal, lalu cetak banyaknya bacaan yang sah dan rata-ratanya (satu angka di belakang titik). Bila tidak ada bacaan yang sah, cetak `TIDAK ADA DATA` sebagai baris kedua.

Contoh: masukan `5`, lalu `120`, `-1`, `130`, `-1`, `125` menghasilkan

```
Sah: 3
Rata-rata: 125.0 cm
```

**Masukan.** Baris pertama: banyaknya bacaan N. Lalu N baris bacaan (cm, bilangan bulat; `-1` berarti gagal). Panggil `input()` tanpa teks di dalam kurungnya.

**Keluaran.** Baris pertama `Sah: K`. Baris kedua `Rata-rata: R cm` (satu desimal) atau `TIDAK ADA DATA`.

**Batasan.** N 3 sampai 30; bacaan sah 20 sampai 400.

Mode banding `trimmed`, batas waktu 5000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Sedang — Denah Titik Kolom (`denah-kolom`)

Denah sebuah bangunan digambar sebagai kisi titik berjarak 1 m: titik (x, y) dengan x dari 0 sampai panjang dan y dari 0 sampai lebar. Kolom berdiri di setiap titik yang x dan y-nya sama-sama kelipatan jarak kolom (0 termasuk kelipatan). Gambar denah itu baris demi baris, mulai dari y = 0: huruf `K` untuk titik kolom dan `.` untuk titik lain, tanpa spasi. Lalu cetak banyaknya kolom.

Contoh: panjang `6`, lebar `3`, jarak `3` menghasilkan

```
K..K..K
.......
.......
K..K..K
Kolom: 6
```

Untuk mencetak tanpa pindah baris pakai `print("K", end="")`; `print()` tanpa isi memindahkan baris.

**Masukan.** Tiga baris: panjang (m), lebar (m), jarak kolom (m). Semuanya bilangan bulat. Panggil `input()` tanpa teks di dalam kurungnya.

**Keluaran.** Lebar + 1 baris gambar, masing-masing panjang + 1 karakter, lalu satu baris `Kolom: N`.

**Batasan.** Jarak 2 sampai 6; panjang dan lebar 2 sampai 35.

Mode banding `trimmed`, batas waktu 5000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Sedang — Ubin Persegi Terbesar (`ubin-terbesar`)

Sebuah lantai berbentuk persegi panjang akan ditutup ubin persegi yang semuanya utuh, tanpa dipotong. Cari sisi ubin terbesar yang memungkinkan itu, yaitu faktor persekutuan terbesar dari panjang dan lebar lantai, lalu hitung banyaknya ubin.

Faktor persekutuan terbesar dapat dicari dengan perulangan: selama b bukan 0, ganti (a, b) dengan (b, sisa a dibagi b); jawabannya nilai a yang terakhir.

Contoh: lantai `360` × `240` menghasilkan

```
Sisi ubin: 120 cm
Jumlah: 6
```

**Masukan.** Dua baris: panjang lantai, lalu lebar lantai (cm, bilangan bulat). Panggil `input()` tanpa teks di dalam kurungnya.

**Keluaran.** Dua baris: `Sisi ubin: S cm`, lalu `Jumlah: N`.

**Batasan.** Panjang dan lebar 10 sampai 2400.

Mode banding `trimmed`, batas waktu 5000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Sulit — Simulasi Tampungan Embung (`routing-waduk`)

Sebuah embung mempunyai kapasitas tertentu dan tampungan awal. Setiap jam terjadi hal berikut, berurutan:

1. air masuk jam itu ditambahkan ke tampungan;
2. embung melepas air untuk kebutuhan irigasi: bila tampungan tidak kurang dari kebutuhan, kebutuhan dipenuhi seluruhnya; bila kurang, semua air yang ada dilepas (tampungan menjadi 0) dan jam itu dihitung sebagai jam kekurangan;
3. bila setelah itu tampungan melebihi kapasitas, kelebihannya melimpas lewat pelimpah, tampungan menjadi sama dengan kapasitas, dan jam itu dihitung sebagai jam melimpas.

Cetak tampungan akhir, jumlah seluruh limpasan, banyaknya jam melimpas, dan banyaknya jam kekurangan.

Contoh: kapasitas `100`, tampungan awal `60`, kebutuhan `20`, `4` jam dengan air masuk `50`, `80`, `0`, `5` menghasilkan

```
Tampungan akhir: 65
Total limpasan: 50
Jam melimpas: 1
Jam kekurangan: 0
```

(tampungan di akhir tiap jam: 90, 100 dengan limpasan 50, 80, 65).

**Masukan.** Baris pertama: kapasitas. Baris kedua: tampungan awal. Baris ketiga: kebutuhan per jam. Baris keempat: banyaknya jam N. Lalu N baris air masuk per jam. Semuanya bilangan bulat dalam ribu m³. Panggil `input()` tanpa teks di dalam kurungnya.

**Keluaran.** Empat baris: `Tampungan akhir: S`, `Total limpasan: L`, `Jam melimpas: A`, `Jam kekurangan: B`.

**Batasan.** Kapasitas 80 sampai 600; tampungan awal tidak melebihi kapasitas; kebutuhan 5 sampai 60; N 3 sampai 30; air masuk 0 sampai 180.

Mode banding `trimmed`, batas waktu 5000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Sulit — Antrean Truk Mixer di Pompa Beton (`antrean-mixer`)

Satu pompa beton melayani truk mixer satu per satu sesuai urutan kedatangan. Menuang satu truk selalu memakan waktu yang sama. Untuk setiap truk:

1. truk mulai menuang pada menit ia tiba bila pompa sedang bebas, atau pada menit pompa selesai dengan truk sebelumnya bila pompa masih sibuk;
2. waktu tunggunya adalah menit mulai dikurangi menit tiba;
3. bila waktu tunggu itu melebihi batas tunggu, beton di truk itu sudah terlalu lama: truk ditolak, langsung pergi, dan tidak memakai pompa sama sekali;
4. bila tidak, truk dilayani dan pompa baru bebas lagi setelah lama menuang berlalu.

Cetak banyaknya truk yang dilayani, banyaknya yang ditolak, waktu tunggu terlama di antara truk yang dilayani, dan menit pompa selesai dengan truk terakhir yang dilayani.

Contoh: `4` truk, lama menuang `20`, batas tunggu `15`, tiba pada menit `0`, `10`, `15`, `50` menghasilkan

```
Dilayani: 3
Ditolak: 1
Tunggu terlama: 10 menit
Selesai: menit 70
```

(truk kedua menunggu 10 menit; truk ketiga harus menunggu 25 menit sehingga ditolak; truk keempat langsung dilayani).

**Masukan.** Baris pertama: banyaknya truk N. Baris kedua: lama menuang (menit). Baris ketiga: batas tunggu (menit). Lalu N baris menit kedatangan, tidak menurun. Semuanya bilangan bulat. Panggil `input()` tanpa teks di dalam kurungnya.

**Keluaran.** Empat baris: `Dilayani: A`, `Ditolak: B`, `Tunggu terlama: W menit`, `Selesai: menit T`.

**Batasan.** N 2 sampai 25; lama menuang 8 sampai 30; batas tunggu 5 sampai 45. Truk pertama selalu dilayani.

Mode banding `trimmed`, batas waktu 5000 ms, 2 tes contoh dan 10 tes tersembunyi.
