# Lembar tinjauan — TS-PY pertemuan 3

Sumber: `docs/soal/ts-py-pertemuan-3.json`. Status setelah impor: menunggu tinjauan (`PENDING_REVIEW`).

| Tingkat | Jumlah | Poin |
|---|---|---|
| Mudah sekali | 8 | 24 |
| Mudah | 8 | 40 |
| Mudah-sedang | 6 | 48 |
| Sedang | 6 | 60 |
| Sulit | 4 | 60 |
| **Semua** | **32** | **232** |

## Semua soal

- **Mudah sekali:** Kuat Tekan Memenuhi atau Tidak (`cek-kuat-tekan`); Hari Hujan atau Hari Kering (`hari-hujan`); Elevasi yang Lebih Tinggi (`elevasi-tertinggi`); Patok Kiri atau Kanan (`sisi-patok`); Muatan Truk di Jembatan Timbang (`cek-muatan-truk`); Selisih Dua Bacaan Rambu (`selisih-bacaan`); Potongan Harga Pembelian Semen (`diskon-semen`); Tanah Berbutir Halus atau Kasar (`lolos-saringan`)
- **Mudah:** Tegangan Kolom terhadap Tegangan Izin (`tegangan-aman`); Pemeriksaan Slump Beton (`cek-slump`); Galian atau Timbunan di Satu Titik (`galian-timbunan`); Kelas Jalan dari Lalu Lintas Harian (`kelas-jalan`); Kategori Hujan Harian (`intensitas-hujan`); Lajur Terpadat (`lajur-terpadat`); Sewa Ekskavator dengan Jam Minimum (`sewa-alat`); Shift Kerja Pengecoran (`shift-kerja`)
- **Mudah-sedang:** Kelas Kemiringan Lereng (`kelas-lereng`); Nilai Tengah Tiga Kubus Uji (`nilai-tengah-kubus`); Penerimaan Mutu Beton (`terima-beton`); Tagihan Air Bertingkat (`tarif-air`); Jenis Aliran dalam Pipa (`bilangan-reynolds`); Upah Mingguan dengan Lembur (`upah-lembur`)
- **Sedang:** Pemeriksaan Siku Tiga Patok (`segitiga-patok`); Azimut ke Sudut Jurusan Kuadran (`kuadran-azimut`); Rasio Tulangan Balok (`rasio-tulangan`); Klasifikasi Tanah Sederhana (`klasifikasi-tanah`); Pemeriksaan Saluran Drainase (`cek-saluran`); Kombinasi Beban yang Menentukan (`kombinasi-beban`)
- **Sulit:** Gaya Lintang dan Momen di Satu Potongan (`gaya-dalam-balok`); Umur Benda Uji dari Dua Tanggal (`umur-benda-uji`); Faktor Reduksi dan Kuat Lentur Balok (`faktor-reduksi`); Pemenang Tender (`pemenang-tender`)

## Pernyataan lengkap: dua soal per tingkat

### Mudah sekali — Kuat Tekan Memenuhi atau Tidak (`cek-kuat-tekan`)

Hasil uji kuat tekan sebuah benda uji dibandingkan dengan kuat tekan rencana. Hasil dinyatakan memenuhi bila nilainya sama dengan atau lebih besar dari rencana.

Contoh: hasil uji `32` dengan rencana `30` menghasilkan `MEMENUHI`; hasil uji `28` dengan rencana `30` menghasilkan `TIDAK MEMENUHI`.

**Masukan.** Dua baris: hasil uji (MPa), lalu kuat tekan rencana (MPa). Keduanya bilangan bulat. Panggil `input()` tanpa teks di dalam kurungnya.

**Keluaran.** Satu baris: `MEMENUHI` atau `TIDAK MEMENUHI`.

**Batasan.** Kedua nilai 14 sampai 46.

Mode banding `trimmed`, batas waktu 5000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah sekali — Tanah Berbutir Halus atau Kasar (`lolos-saringan`)

Pada analisis saringan, tanah disebut berbutir halus bila lebih dari 50% beratnya lolos saringan No. 200. Bila tepat 50% atau kurang, tanah itu berbutir kasar (parameter latihan).

Contoh: `62.5` menghasilkan `BERBUTIR HALUS`; `50.0` menghasilkan `BERBUTIR KASAR`.

**Masukan.** Satu baris: persen lolos saringan No. 200 (bilangan pecahan dengan satu angka di belakang titik). Panggil `input()` tanpa teks di dalam kurungnya.

**Keluaran.** Satu baris: `BERBUTIR HALUS` atau `BERBUTIR KASAR`.

**Batasan.** Persen lolos 0.0 sampai 100.0.

Mode banding `trimmed`, batas waktu 5000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah — Tegangan Kolom terhadap Tegangan Izin (`tegangan-aman`)

Sebuah kolom memikul gaya tekan P (kN) pada luas penampang A (mm²). Tegangannya adalah P × 1000 / A dalam MPa. Kolom dinyatakan aman bila tegangan itu tidak melebihi tegangan izin.

Contoh: P = `900`, A = `90000`, izin = `12` menghasilkan

```
Tegangan: 10.00 MPa
AMAN
```

sedangkan P = `1500`, A = `90000`, izin = `12` menghasilkan `Tegangan: 16.67 MPa` lalu `TIDAK AMAN`.

**Masukan.** Tiga baris: gaya P (kN), luas A (mm²), tegangan izin (MPa). Semuanya bilangan bulat. Panggil `input()` tanpa teks di dalam kurungnya.

**Keluaran.** Baris pertama `Tegangan: T MPa` dengan T dua angka di belakang titik. Baris kedua `AMAN` atau `TIDAK AMAN`.

**Batasan.** P 100 sampai 9000; A 40000 sampai 300000; tegangan izin 8 sampai 15.

Mode banding `trimmed`, batas waktu 5000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah — Shift Kerja Pengecoran (`shift-kerja`)

Proyek bekerja tiga shift. Truk mixer yang tiba pada jam 7 sampai 14 dilayani shift pagi, jam 15 sampai 22 shift sore, dan jam lainnya (23, serta 0 sampai 6) shift malam. Tentukan shift dari jam kedatangan (angka jamnya saja, 0 sampai 23).

Contoh: `14` menghasilkan `PAGI`; `23` menghasilkan `MALAM`.

**Masukan.** Satu baris: jam kedatangan (bilangan bulat). Panggil `input()` tanpa teks di dalam kurungnya.

**Keluaran.** Satu baris: `PAGI`, `SORE`, atau `MALAM`.

**Batasan.** Jam 0 sampai 23.

Mode banding `trimmed`, batas waktu 5000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah-sedang — Kelas Kemiringan Lereng (`kelas-lereng`)

Kemiringan lereng dalam persen adalah beda tinggi dibagi jarak datar, dikali 100. Lereng dikelompokkan (parameter latihan):

- kurang dari 8%: `DATAR`
- 8% sampai kurang dari 15%: `LANDAI`
- 15% sampai kurang dari 25%: `AGAK CURAM`
- 25% sampai kurang dari 45%: `CURAM`
- 45% atau lebih: `SANGAT CURAM`

Cetak kemiringan dengan satu angka di belakang titik, tanda `%`, satu spasi, lalu kelasnya.

Contoh: beda tinggi `6` dan jarak datar `50` menghasilkan `12.0% LANDAI`.

**Masukan.** Dua baris: beda tinggi (m), lalu jarak datar (m). Keduanya bilangan bulat. Panggil `input()` tanpa teks di dalam kurungnya.

**Keluaran.** Satu baris berbentuk `P% KELAS`, dengan P satu angka di belakang titik.

**Batasan.** Beda tinggi 1 sampai 150; jarak datar 10 sampai 200. Kemiringan tidak pernah tepat di batas kelas.

Mode banding `trimmed`, batas waktu 5000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah-sedang — Upah Mingguan dengan Lembur (`upah-lembur`)

Seorang tukang dibayar per jam. Aturan lembur proyek (parameter latihan):

- 40 jam pertama dalam seminggu dibayar dengan upah biasa;
- jam ke-41 sampai ke-48 dibayar 1.5 kali upah biasa;
- jam di atas 48 dibayar 2 kali upah biasa.

Upah per jam selalu kelipatan 1000 rupiah, sehingga hasilnya bilangan bulat; cetak tanpa angka di belakang titik.

Contoh: `50` jam dengan upah `20000` menghasilkan `Upah: Rp 1120000` (800000 + 240000 + 80000).

**Masukan.** Dua baris: jam kerja seminggu, lalu upah per jam (rupiah). Keduanya bilangan bulat. Panggil `input()` tanpa teks di dalam kurungnya.

**Keluaran.** Satu baris berbentuk `Upah: Rp X`, dengan X bilangan bulat.

**Batasan.** Jam kerja 20 sampai 70; upah per jam 15000 sampai 40000.

Mode banding `trimmed`, batas waktu 5000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Sedang — Pemeriksaan Siku Tiga Patok (`segitiga-patok`)

Tukang memeriksa kesikuan sudut bangunan dengan mengukur tiga sisi segitiga di antara tiga patok. Dari tiga panjang sisi (urutannya bebas), tentukan jenis segitiganya. Misalkan p sisi terpanjang dan q, s dua sisi lainnya:

- bila p sama dengan atau lebih dari q + s, ketiga panjang itu tidak membentuk segitiga: `BUKAN SEGITIGA`
- bila p² = q² + s²: `SIKU-SIKU`
- bila p² < q² + s²: `LANCIP`
- bila p² > q² + s²: `TUMPUL`

Contoh: `300`, `500`, `400` menghasilkan `SIKU-SIKU`; `2`, `6`, `3` menghasilkan `BUKAN SEGITIGA`.

**Masukan.** Tiga baris: panjang tiga sisi (cm, bilangan bulat), dalam urutan apa pun. Panggil `input()` tanpa teks di dalam kurungnya.

**Keluaran.** Satu baris: `BUKAN SEGITIGA`, `SIKU-SIKU`, `LANCIP`, atau `TUMPUL`.

**Batasan.** Setiap panjang 1 sampai 2000.

Mode banding `trimmed`, batas waktu 5000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Sedang — Kombinasi Beban yang Menentukan (`kombinasi-beban`)

Sebuah kolom memikul beban mati D, beban hidup L, dan beban angin W. Perencana memeriksa tiga kombinasi beban terfaktor (disederhanakan untuk latihan):

- Kombinasi 1: 1.4 D
- Kombinasi 2: 1.2 D + 1.6 L
- Kombinasi 3: 1.2 D + 1.0 L + 1.6 W

Kombinasi yang menentukan adalah yang nilainya terbesar. Cetak nomornya dan nilainya dengan satu angka di belakang titik.

Contoh: D = `100`, L = `50`, W = `20` menghasilkan

```
Kombinasi 3
202.0 kN
```

(nilainya 140.0, 200.0, dan 202.0).

**Masukan.** Tiga baris: D, L, W (kN, bilangan bulat). Panggil `input()` tanpa teks di dalam kurungnya.

**Keluaran.** Baris pertama `Kombinasi K`. Baris kedua `U kN` dengan U satu angka di belakang titik.

**Batasan.** D 20 sampai 400; L 0 sampai 300; W 0 sampai 250. Nilai terbesar tidak pernah kembar.

Mode banding `trimmed`, batas waktu 5000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Sulit — Gaya Lintang dan Momen di Satu Potongan (`gaya-dalam-balok`)

Balok sederhana berbentang L (tumpuan A di kiri, B di kanan) memikul dua beban terpusat: P1 pada jarak a1 dari A dan P2 pada jarak a2 dari A, dengan a1 < a2. Reaksi tumpuan A adalah

RA = (P1 × (L − a1) + P2 × (L − a2)) / L.

Gaya lintang V dan momen lentur M pada potongan sejauh x dari A bergantung pada letak potongan itu:

- x tidak lebih dari a1: V = RA, M = RA × x
- x lebih dari a1 dan tidak lebih dari a2: V = RA − P1, M = RA × x − P1 × (x − a1)
- x lebih dari a2: V = RA − P1 − P2, M = RA × x − P1 × (x − a1) − P2 × (x − a2)

Tepat di bawah sebuah beban, pakai rumus potongan di sebelah kirinya (seperti aturan di atas). V boleh negatif.

Contoh: L = `10`, P1 = `20`, a1 = `2`, P2 = `30`, a2 = `6`, x = `4.0` menghasilkan

```
V = 8.00 kN
M = 72.00 kNm
```

karena RA = 28.

**Masukan.** Enam baris: L (m), P1 (kN), a1 (m), P2 (kN), a2 (m), semuanya bilangan bulat; lalu x (m, bilangan pecahan dengan satu angka di belakang titik). Panggil `input()` tanpa teks di dalam kurungnya.

**Keluaran.** Baris pertama `V = N kN`, baris kedua `M = N kNm`, masing-masing dengan dua angka di belakang titik.

**Batasan.** L 4 sampai 20; 0 < a1 < a2 < L; beban 5 sampai 120; 0 < x < L. V dan M tidak pernah nol.

Mode banding `trimmed`, batas waktu 5000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Sulit — Pemenang Tender (`pemenang-tender`)

Tiga kontraktor memasukkan penawaran untuk sebuah pekerjaan. Setiap penawaran berisi harga (juta rupiah) dan waktu pelaksanaan (hari). Panitia memakai aturan berikut:

1. Penawaran yang waktunya melebihi batas waktu gugur. Waktu yang tepat sama dengan batas masih sah.
2. Di antara penawaran yang sah, pemenangnya yang harganya paling rendah.
3. Bila harga terendah kembar, menang yang waktunya lebih singkat.
4. Bila masih kembar, menang penawar dengan nomor lebih kecil.
5. Bila tidak ada penawaran yang sah, tender gagal.

Pertemuan ini belum memakai perulangan atau list.

Contoh: batas waktu `120`; penawar 1 harga `950` waktu `110`; penawar 2 harga `900` waktu `130`; penawar 3 harga `950` waktu `100` menghasilkan

```
Pemenang: Penawar 3
Harga: 950 juta
```

Penawar 2 gugur karena waktunya, lalu penawar 3 menang atas penawar 1 karena lebih singkat. Bila ketiga waktu melebihi batas, keluarannya satu baris `TENDER GAGAL`.

**Masukan.** Tujuh baris: batas waktu (hari); lalu harga dan waktu penawar 1, penawar 2, penawar 3, masing-masing di barisnya sendiri. Semuanya bilangan bulat. Panggil `input()` tanpa teks di dalam kurungnya.

**Keluaran.** Dua baris `Pemenang: Penawar K` dan `Harga: H juta`, atau satu baris `TENDER GAGAL`.

**Batasan.** Batas waktu 60 sampai 240; harga 400 sampai 2000; waktu 20 sampai 270.

Mode banding `trimmed`, batas waktu 5000 ms, 2 tes contoh dan 10 tes tersembunyi.
