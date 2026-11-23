# Lembar tinjauan — TS-PY pertemuan 2

Sumber: `docs/soal/ts-py-pertemuan-2.json`. Status setelah impor: menunggu tinjauan (`PENDING_REVIEW`).

| Tingkat | Jumlah | Poin |
|---|---|---|
| Mudah sekali | 8 | 24 |
| Mudah | 8 | 40 |
| Mudah-sedang | 6 | 48 |
| Sedang | 6 | 60 |
| Sulit | 4 | 60 |
| **Semua** | **32** | **232** |

## Semua soal

- **Mudah sekali:** Panjang dengan Dua Desimal (`format-panjang`); Milimeter ke Meter (`mm-ke-m`); Meter Utuh dari Sisa Besi (`meter-utuh`); Kecepatan Rencana dalam m/detik (`km-jam-ke-m-detik`); Keliling Tiang Pancang Bulat (`keliling-tiang`); Mutu Beton: MPa ke kg/cm² (`mpa-ke-kgcm2`); Luas Lahan dalam Hektar (`luas-ke-hektar`); Jam Kerja Alat dalam Desimal (`jam-desimal`)
- **Mudah:** Berat Sendiri Balok Beton (`berat-sendiri-balok`); Tekanan Hidrostatik di Dasar Kolam (`tekanan-hidrostatik`); Panjang Kaki Kuda-Kuda (`kaki-kuda-kuda`); Satu Baris Rincian Material (`rincian-material`); Debit Pipa Penuh (`debit-pipa`); Kadar Air Tanah (`kadar-air-tanah`); Jarak Sebenarnya dari Peta (`skala-peta`); Sudut Kemiringan Lereng (`sudut-lereng`)
- **Mudah-sedang:** Tegangan Izin dalam Tiga Satuan (`satuan-tegangan`); Kuat Tekan Silinder Beton (`kuat-tekan-silinder`); Tanah Asli, Lepas, dan Padat (`timbunan-gembur`); Intensitas Hujan Rumus Mononobe (`intensitas-mononobe`); Kuitansi Berkolom Rapi (`kuitansi-material`); Azimut ke Derajat Desimal (`sudut-desimal`)
- **Sedang:** Lendutan Balok Sederhana (`lendutan-balok`); Debit Saluran dengan Rumus Manning (`debit-manning`); Momen dan Tegangan Lentur Balok (`tegangan-lentur`); Timbunan Pasir Berbentuk Kerucut (`timbunan-kerucut`); Koordinat Titik dari Jarak dan Azimut (`koordinat-polar`); Jarak Pandang Henti (`jarak-henti`)
- **Sulit:** Taksiran Tulangan Lentur Balok (`tulangan-perlu`); Dari Hujan Harian ke Lebar Saluran (`debit-rasional`); Luas dan Nilai Lahan Segitiga (`lahan-segitiga`); Waktu Tempuh Truk Molen (`waktu-tempuh`)

## Pernyataan lengkap: dua soal per tingkat

### Mudah sekali — Panjang dengan Dua Desimal (`format-panjang`)

Alat ukur jarak mencatat panjang sampai tiga angka di belakang koma, tetapi laporan hanya memuat dua. Baca panjang itu sebagai `float`, lalu cetak dengan f-string berformat `:.2f`.

Contoh: masukan `12.348` menghasilkan `Panjang: 12.35 m`.

**Masukan.** Satu baris: panjang dalam meter (bilangan pecahan). Panggil `input()` tanpa teks di dalam kurungnya.

**Keluaran.** Satu baris berbentuk `Panjang: P m`, dengan P tepat dua angka di belakang titik.

**Batasan.** Panjang 0.100 sampai 99.999.

Mode banding `trimmed`, batas waktu 5000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah sekali — Jam Kerja Alat dalam Desimal (`jam-desimal`)

Sewa alat berat dihitung per jam, dengan jam ditulis sebagai bilangan desimal. Operator mencatat lama kerja dalam menit. Ubah menjadi jam desimal.

Contoh: `135` menit menghasilkan `2.25 jam`.

**Masukan.** Satu baris: lama kerja dalam menit (bilangan bulat). Panggil `input()` tanpa teks di dalam kurungnya.

**Keluaran.** Satu baris berbentuk `J jam`, dengan J dua angka di belakang titik.

**Batasan.** Lama kerja 1 sampai 2000.

Mode banding `trimmed`, batas waktu 5000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah — Berat Sendiri Balok Beton (`berat-sendiri-balok`)

Berat sendiri balok = volume × berat jenis. Untuk beton bertulang dipakai 24 kN/m³ (parameter latihan). Penampang balok diberikan dalam cm dan panjangnya dalam m: ubah dulu penampang ke meter supaya satuannya konsisten.

Contoh: balok 30 cm × 50 cm sepanjang 6.5 m: 0,3 × 0,5 × 6,5 × 24 = `23.40 kN`.

**Masukan.** Tiga baris: lebar (cm, bilangan bulat), tinggi (cm, bilangan bulat), panjang (m, bilangan pecahan). Panggil `input()` tanpa teks di dalam kurungnya.

**Keluaran.** Satu baris berbentuk `W kN`, dengan W dua angka di belakang titik.

**Batasan.** Lebar 20–60, tinggi 30–90, panjang 2.0–12.0.

Mode banding `trimmed`, batas waktu 5000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah — Sudut Kemiringan Lereng (`sudut-lereng`)

Sudut lereng terhadap bidang datar didapat dari tinggi dan jarak datarnya: sudut = arctan(tinggi ÷ jarak datar). `math.atan` mengembalikan sudut dalam radian; ubah ke derajat dengan `math.degrees`.

Contoh: tinggi `3.0` m dan jarak datar `4.5` m menghasilkan `33.7 derajat`.

**Masukan.** Dua baris: tinggi lereng, lalu jarak datarnya; bilangan pecahan dalam meter. Panggil `input()` tanpa teks di dalam kurungnya.

**Keluaran.** Satu baris berbentuk `S derajat`, dengan S satu angka di belakang titik.

**Batasan.** Tinggi 0.5–20.0; jarak datar 1.0–40.0.

Mode banding `trimmed`, batas waktu 5000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah-sedang — Tegangan Izin dalam Tiga Satuan (`satuan-tegangan`)

Tabel lama mencantumkan tegangan izin dalam kgf/cm². Ubah ke MPa dengan 1 kgf/cm² = 0,0980665 MPa, lalu ke kPa (1 MPa = 1000 kPa).

Contoh: `250.0` kgf/cm² menghasilkan

```
24.517 MPa
24516.6 kPa
```

**Masukan.** Satu baris: tegangan dalam kgf/cm² (bilangan pecahan). Panggil `input()` tanpa teks di dalam kurungnya.

**Keluaran.** Baris pertama `A MPa` dengan tiga angka di belakang titik; baris kedua `B kPa` dengan satu angka di belakang titik.

**Batasan.** Tegangan 10.0 sampai 900.0.

Mode banding `trimmed`, batas waktu 5000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah-sedang — Azimut ke Derajat Desimal (`sudut-desimal`)

Teodolit menampilkan sudut dalam derajat, menit, dan detik, tetapi fungsi trigonometri memerlukan derajat desimal. Satu derajat = 60 menit = 3600 detik.

Contoh: 112° 30′ 45″ adalah 112 + 30 ÷ 60 + 45 ÷ 3600 = `112.5125`.

**Masukan.** Tiga baris: derajat, menit, detik (bilangan bulat). Panggil `input()` tanpa teks di dalam kurungnya.

**Keluaran.** Satu baris: sudut dalam derajat desimal, empat angka di belakang titik.

**Batasan.** Derajat 0–359; menit dan detik 0–59.

Mode banding `trimmed`, batas waktu 5000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Sedang — Lendutan Balok Sederhana (`lendutan-balok`)

Lendutan maksimum balok di atas dua tumpuan dengan beban merata adalah

δ = 5 × q × L⁴ ÷ (384 × E × I)

Rumus ini hanya benar bila semua besaran memakai satuan yang konsisten. Pakai N dan mm:

- q diberikan dalam kN/m; 1 kN/m sama dengan 1 N/mm;
- L diberikan dalam m; ubah ke mm;
- E diberikan dalam MPa, yaitu N/mm²;
- I diberikan dalam cm⁴; 1 cm⁴ = 10 000 mm⁴.

Contoh: q = `12.0`, L = `6.0`, E = `25000`, I = `180000` menghasilkan `4.50 mm`.

**Masukan.** Empat baris: q (kN/m, pecahan), L (m, pecahan), E (MPa, bilangan bulat), I (cm⁴, bilangan bulat). Panggil `input()` tanpa teks di dalam kurungnya.

**Keluaran.** Satu baris berbentuk `D mm`, dengan D dua angka di belakang titik.

**Batasan.** q 2.0–40.0; L 2.0–10.0; E 20000–200000; I 20000–900000.

Mode banding `trimmed`, batas waktu 5000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Sedang — Jarak Pandang Henti (`jarak-henti`)

Jarak pandang henti terdiri atas jarak yang ditempuh selama pengemudi bereaksi dan jarak pengereman. Dengan kecepatan V dalam km/jam:

- jarak reaksi = 0,278 × V × t (0,278 mengubah km/jam ke m/detik);
- jarak pengereman = V² ÷ (254 × f);
- jarak henti = jumlah keduanya,

dengan t waktu reaksi (detik) dan f koefisien gesek memanjang (rumus dan angka: parameter latihan).

Contoh: V = `60`, t = `2.5`, f = `0.35` menghasilkan

```
Reaksi: 41.7 m
Rem: 40.5 m
Henti: 82.2 m
```

**Masukan.** Tiga baris: V (km/jam, bilangan bulat), t (detik, pecahan), f (pecahan). Panggil `input()` tanpa teks di dalam kurungnya.

**Keluaran.** Tiga baris: `Reaksi: … m`, `Rem: … m`, `Henti: … m`, masing-masing satu angka di belakang titik. Jarak henti dihitung dari nilai yang belum dibulatkan.

**Batasan.** V 30–120; t 1.0–3.0; f 0.28–0.40.

Mode banding `trimmed`, batas waktu 5000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Sulit — Taksiran Tulangan Lentur Balok (`tulangan-perlu`)

Luas tulangan tarik sebuah balok dapat ditaksir dengan rumus pendekatan (parameter latihan, bukan prosedur desain):

As = M ÷ (0,9 × fy × 0,9 × d)

dengan M momen dalam Nmm, fy tegangan leleh baja dalam MPa, dan d tinggi efektif dalam mm; hasilnya mm². Momen diberikan dalam kNm (1 kNm = 1 000 000 Nmm).

Tulangan dipasang dari batang berdiameter D mm. Luas satu batang π × D² ÷ 4; jumlah batang dibulatkan ke atas (`math.ceil`). Hitung juga luas tulangan yang benar-benar terpasang.

Contoh: M = `85.0`, fy = `400`, d = `440`, D = `16` menghasilkan

```
As perlu = 596.2 mm2
Jumlah = 3 D16
As terpasang = 603.2 mm2
```

**Masukan.** Empat baris: M (kNm, pecahan), fy (MPa, bilangan bulat), d (mm, bilangan bulat), D (mm, bilangan bulat). Panggil `input()` tanpa teks di dalam kurungnya.

**Keluaran.** Tiga baris: `As perlu = … mm2` (satu desimal), `Jumlah = N D…` (N jumlah batang, lalu huruf D langsung diikuti diameternya), `As terpasang = … mm2` (satu desimal).

**Batasan.** M 20.0–400.0; fy 280, 400 atau 420; d 250–700; D 10–25. Jumlah batang tidak lebih dari 12.

Mode banding `trimmed`, batas waktu 5000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Sulit — Waktu Tempuh Truk Molen (`waktu-tempuh`)

Beton segar harus tiba di lokasi sebelum mulai mengeras, jadi waktu tempuh truk molen dihitung dengan teliti. Waktu tempuh (jam) = jarak ÷ kecepatan.

Cetak waktu itu dua kali: sebagai jam desimal dengan tiga angka di belakang titik, dan sebagai `JJ:MM:DD`. Untuk bentuk kedua, ubah waktu ke detik dan bulatkan ke detik terdekat dengan `round()`, lalu uraikan dengan `//` dan `%`. Jam, menit, dan detik masing-masing ditulis dua angka dengan nol di depan (format `:02d`).

Contoh: jarak `87.5` km dengan kecepatan `42` km/jam menghasilkan

```
2.083 jam
02:05:00
```

**Masukan.** Dua baris: jarak (km, bilangan pecahan), lalu kecepatan rata-rata (km/jam, bilangan bulat). Panggil `input()` tanpa teks di dalam kurungnya.

**Keluaran.** Baris pertama `T jam` dengan tiga angka di belakang titik; baris kedua `JJ:MM:DD`.

**Batasan.** Jarak 0.5–400.0; kecepatan 15–100. Waktu tempuh kurang dari 100 jam.

Mode banding `trimmed`, batas waktu 5000 ms, 2 tes contoh dan 10 tes tersembunyi.
