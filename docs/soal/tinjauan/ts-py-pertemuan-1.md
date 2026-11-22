# Lembar tinjauan — TS-PY pertemuan 1

Sumber: `docs/soal/ts-py-pertemuan-1.json`. Status setelah impor: menunggu tinjauan (`PENDING_REVIEW`).

| Tingkat | Jumlah | Poin |
|---|---|---|
| Mudah sekali | 8 | 24 |
| Mudah | 8 | 40 |
| Mudah-sedang | 6 | 48 |
| Sedang | 6 | 60 |
| Sulit | 4 | 60 |
| **Semua** | **32** | **232** |

## Semua soal

- **Mudah sekali:** Papan Nama Proyek (`papan-proyek`); Luas Pelat Lantai (`luas-pelat`); Pagar Keliling Lahan (`keliling-lahan`); Pencacahan Lalu Lintas (`hitung-kendaraan`); Sisa Stok Semen (`sisa-stok-semen`); Volume Kubus Uji Beton (`kubus-uji`); Label Benda Uji (`data-benda-uji`); Elevasi Rata-Rata Dua Titik (`rata-elevasi`)
- **Mudah:** Kalkulator Volume Beton Balok (`volume-balok-beton`); Truk Penuh dan Sisa Tanah (`truk-penuh`); Durasi Pengecoran (`menit-ke-jam`); Luas Bersih Dinding (`luas-dinding-bersih`); Kemiringan Saluran dalam Persen (`kemiringan-persen`); Tegangan Tekan Kolom Persegi (`tegangan-kolom`); Debit Saluran Persegi (`debit-saluran`); Bacaan Rambu yang Tertukar (`bacaan-tertukar`)
- **Mudah-sedang:** Kebutuhan Bata Dinding (`kebutuhan-bata`); Berat Besi Tulangan (`berat-tulangan`); Takaran Campuran Beton (`campuran-beton`); Volume Air Hujan di Atap (`volume-hujan-atap`); Satuan Mobil Penumpang (`smp-lalu-lintas`); Rencana Anggaran Biaya Sederhana (`rab-sederhana`)
- **Sedang:** Volume Galian Saluran Trapesium (`volume-saluran`); Keramik Lantai dan Jumlah Dus (`keramik-lantai`); Sengkang Balok (`sengkang-balok`); Ritase Truk Pengangkut Tanah (`ritase-truk`); Beton untuk Kolom Bulat (`kolom-bulat`); Elevasi di Antara Dua Patok (`interpolasi-elevasi`)
- **Sulit:** Memotong Besi 12 Meter (`potong-besi`); Kapan Pengecoran Selesai (`selesai-pengecoran`); Galian Pondasi Menerus (`galian-pondasi`); Tangki Penampung Air Hujan (`tangki-hujan`)

## Pernyataan lengkap: dua soal per tingkat

### Mudah sekali — Papan Nama Proyek (`papan-proyek`)

Setiap proyek konstruksi memasang papan nama di pintu masuk lokasi. Buat program yang membaca nama proyek lalu mencetaknya kembali dengan awalan `Proyek:`.

Contoh: masukan `Gedung Kuliah Terpadu` menghasilkan `Proyek: Gedung Kuliah Terpadu`.

**Masukan.** Satu baris: nama proyek (boleh terdiri atas beberapa kata). Panggil `input()` tanpa teks di dalam kurungnya: teks itu ikut tercetak dan membuat jawaban dianggap salah.

**Keluaran.** Satu baris: kata `Proyek:`, satu spasi, lalu nama proyek persis seperti masukan.

**Batasan.** Nama proyek 1 sampai 60 karakter.

Mode banding `trimmed`, batas waktu 5000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah sekali — Elevasi Rata-Rata Dua Titik (`rata-elevasi`)

Juru ukur mencatat elevasi dua titik di lapangan. Hitung elevasi rata-ratanya. Ingat urutan pengerjaan: penjumlahan harus diberi kurung supaya dikerjakan sebelum pembagian.

Contoh: elevasi 120 cm dan 135 cm menghasilkan `127.5`.

**Masukan.** Dua baris: elevasi titik A, lalu elevasi titik B, bilangan bulat dalam cm. Panggil `input()` tanpa teks di dalam kurungnya: teks itu ikut tercetak dan membuat jawaban dianggap salah.

**Keluaran.** Satu baris: elevasi rata-rata dalam cm. Hasil tidak perlu dibulatkan: selisih sampai 0,000001 dari jawaban juri masih diterima.

**Batasan.** Elevasi 0 sampai 5000.

Mode banding `float`, batas waktu 5000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah — Kalkulator Volume Beton Balok (`volume-balok-beton`)

Ukuran sebuah balok beton diberikan dalam sentimeter, sedangkan beton dipesan dalam meter kubik. Hitung volume balok dalam m³. Ingat bahwa 1 m³ = 1 000 000 cm³.

Contoh: balok 400 cm × 30 cm × 50 cm bervolume 600 000 cm³, yaitu `0.6` m³.

**Masukan.** Tiga baris: panjang, lebar, dan tinggi balok, bilangan bulat dalam cm. Panggil `input()` tanpa teks di dalam kurungnya: teks itu ikut tercetak dan membuat jawaban dianggap salah.

**Keluaran.** Satu baris: volume balok dalam m³. Hasil tidak perlu dibulatkan: selisih sampai 0,000001 dari jawaban juri masih diterima.

**Batasan.** Panjang 100–800, lebar 15–60, tinggi 20–90.

Mode banding `float`, batas waktu 5000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah — Bacaan Rambu yang Tertukar (`bacaan-tertukar`)

Pada pengukuran sipat datar, beda tinggi dua titik adalah bacaan rambu belakang dikurangi bacaan rambu muka. Seorang asisten menulis kedua bacaan di kolom yang salah: angka yang tercatat sebagai bacaan belakang sebenarnya bacaan muka, dan sebaliknya. Tukar isi kedua variabel, lalu cetak bacaan yang benar dan beda tingginya.

Contoh: tercatat belakang 1250 dan muka 1875. Yang benar: belakang 1875 dan muka 1250, sehingga keluarannya

```
1875 1250
625
```

**Masukan.** Dua baris: angka yang tercatat sebagai bacaan belakang, lalu angka yang tercatat sebagai bacaan muka; bilangan bulat dalam mm. Panggil `input()` tanpa teks di dalam kurungnya: teks itu ikut tercetak dan membuat jawaban dianggap salah.

**Keluaran.** Baris pertama: bacaan belakang yang benar dan bacaan muka yang benar, dipisah satu spasi. Baris kedua: beda tinggi (belakang − muka, boleh negatif).

**Batasan.** Bacaan 500 sampai 2500; keduanya berbeda.

Mode banding `trimmed`, batas waktu 5000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah-sedang — Kebutuhan Bata Dinding (`kebutuhan-bata`)

Jumlah bata untuk sebuah dinding ditaksir dengan membagi luas dinding dengan luas satu bata (ukuran bata sudah termasuk siar). Bata tidak bisa dibeli sebagian, jadi hasil bagi dibulatkan ke atas.

Pembulatan ke atas dari a ÷ b untuk bilangan bulat positif dapat ditulis tanpa `if`: `(a + b - 1) // b`.

Contoh: dinding 300 cm × 300 cm dengan bata 22 cm × 7 cm: 90 000 ÷ 154 = 584,4…, jadi dibutuhkan `585` bata.

**Masukan.** Empat baris: panjang dinding, tinggi dinding, panjang bata, tinggi bata; bilangan bulat dalam cm. Panggil `input()` tanpa teks di dalam kurungnya: teks itu ikut tercetak dan membuat jawaban dianggap salah.

**Keluaran.** Satu baris: jumlah bata (angkanya saja).

**Batasan.** Panjang dinding 200–1200, tinggi dinding 200–400, panjang bata 20–25, tinggi bata 6–12.

Mode banding `trimmed`, batas waktu 5000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah-sedang — Rencana Anggaran Biaya Sederhana (`rab-sederhana`)

Sebuah rencana anggaran biaya (RAB) memuat tiga pekerjaan. Tiap pekerjaan punya volume dan harga satuan (dalam ribu rupiah). Hitung jumlah biaya ketiganya, PPN 11 % dari jumlah itu dibulatkan ke bawah dengan `//`, dan total setelah PPN (tarif di sini parameter latihan).

Contoh: pekerjaan (10, 850), (25, 120), dan (4, 300): 8500 + 3000 + 1200 = 12 700; PPN = 12 700 × 11 // 100 = 1397. Keluarannya

```
Jumlah: 12700
PPN: 1397
Total: 14097
```

**Masukan.** Enam baris: volume dan harga satuan pekerjaan pertama, lalu pekerjaan kedua, lalu pekerjaan ketiga; bilangan bulat. Panggil `input()` tanpa teks di dalam kurungnya: teks itu ikut tercetak dan membuat jawaban dianggap salah.

**Keluaran.** Tiga baris: `Jumlah: J`, `PPN: P`, `Total: T`, semuanya dalam ribu rupiah.

**Batasan.** Volume 1–200; harga satuan 50–1500.

Mode banding `trimmed`, batas waktu 5000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Sedang — Volume Galian Saluran Trapesium (`volume-saluran`)

Saluran drainase digali dengan penampang trapesium. Luas trapesium = (lebar atas + lebar bawah) × kedalaman ÷ 2. Volume galian = luas penampang × panjang saluran. Ukuran penampang diberikan dalam cm dan panjang dalam m; hasil diminta dalam m³ (1 m² = 10 000 cm²).

Contoh: lebar atas 120 cm, lebar bawah 60 cm, kedalaman 80 cm, panjang 50 m: luas 7200 cm² = 0,72 m², volume `36.0` m³.

**Masukan.** Empat baris: lebar atas (cm), lebar bawah (cm), kedalaman (cm), panjang saluran (m); bilangan bulat. Panggil `input()` tanpa teks di dalam kurungnya: teks itu ikut tercetak dan membuat jawaban dianggap salah.

**Keluaran.** Satu baris: volume galian dalam m³. Hasil tidak perlu dibulatkan: selisih sampai 0,000001 dari jawaban juri masih diterima.

**Batasan.** Lebar atas 60–400, lebar bawah 30 sampai lebar atas, kedalaman 30–200, panjang 10–2000.

Mode banding `float`, batas waktu 5000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Sedang — Elevasi di Antara Dua Patok (`interpolasi-elevasi`)

Elevasi tanah diketahui di dua patok. Elevasi sebuah titik di antara keduanya ditaksir dengan interpolasi linear, seolah-olah tanah di antara kedua patok berupa garis lurus:

elevasi = e₁ + (e₂ − e₁) × (x − x₁) ÷ (x₂ − x₁)

dengan x₁, x₂ posisi kedua patok, e₁, e₂ elevasinya, dan x posisi titik yang ditanyakan.

Contoh: patok di 0 m berelevasi 1200 cm dan patok di 50 m berelevasi 1300 cm; titik di 20 m: 1200 + 100 × 20 ÷ 50 = `1240.0` cm.

**Masukan.** Lima baris: x₁ (m), e₁ (cm), x₂ (m), e₂ (cm), lalu x (m); bilangan bulat. Panggil `input()` tanpa teks di dalam kurungnya: teks itu ikut tercetak dan membuat jawaban dianggap salah.

**Keluaran.** Satu baris: elevasi titik itu dalam cm. Hasil tidak perlu dibulatkan: selisih sampai 0,000001 dari jawaban juri masih diterima.

**Batasan.** x₁ < x₂; x₁ ≤ x ≤ x₂; posisi 0–700; elevasi 500–9000.

Mode banding `float`, batas waktu 5000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Sulit — Memotong Besi 12 Meter (`potong-besi`)

Besi tulangan dijual dalam batang sepanjang 12 m (12 000 mm). Proyek membutuhkan sejumlah potongan yang sama panjang. Potongan tidak boleh disambung, jadi dari satu batang hanya didapat 12000 // panjang potongan.

Hitung: (1) banyaknya potongan dari satu batang; (2) jumlah batang yang harus dibeli, dibulatkan ke atas; (3) panjang besi yang tidak terpakai: seluruh panjang batang yang dibeli dikurangi seluruh panjang potongan yang dibutuhkan.

Pembulatan ke atas dari a ÷ b untuk bilangan bulat positif dapat ditulis tanpa `if`: `(a + b - 1) // b`.

Contoh: 10 potongan sepanjang 2750 mm: satu batang menghasilkan 4 potongan, jadi dibeli 3 batang; sisa 36 000 − 27 500 = 8500 mm:

```
Potongan per batang: 4
Batang: 3
Sisa: 8500 mm
```

**Masukan.** Dua baris: panjang satu potongan (mm), lalu jumlah potongan yang dibutuhkan; bilangan bulat. Panggil `input()` tanpa teks di dalam kurungnya: teks itu ikut tercetak dan membuat jawaban dianggap salah.

**Keluaran.** Tiga baris: `Potongan per batang: A`, `Batang: B`, `Sisa: C mm`.

**Batasan.** Panjang potongan 300–6001; jumlah potongan 1–400.

Mode banding `trimmed`, batas waktu 5000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Sulit — Tangki Penampung Air Hujan (`tangki-hujan`)

Air hujan dari atap dialirkan ke tangki kosong. Hujan 1 mm di atas 1 m² sama dengan 1 liter, jadi air yang masuk tiap jam (liter/jam) = luas atap (m²) × intensitas hujan (mm/jam).

Hitung debit itu, lalu berapa lama sampai tangki penuh bila hujan turun terus dengan intensitas tetap. Lamanya dihitung dalam menit penuh, dibulatkan ke atas: menit = volume tangki × 60 ÷ debit, lalu ditulis sebagai jam dan menit.

Pembulatan ke atas dari a ÷ b untuk bilangan bulat positif dapat ditulis tanpa `if`: `(a + b - 1) // b`.

Contoh: atap 100 m², hujan 12 mm/jam, tangki 2000 liter: debit 1200 liter/jam; 2000 × 60 ÷ 1200 = 100 menit:

```
Debit: 1200 liter/jam
Penuh: 1 jam 40 menit
```

**Masukan.** Tiga baris: luas atap (m²), intensitas hujan (mm/jam), volume tangki (liter); bilangan bulat. Panggil `input()` tanpa teks di dalam kurungnya: teks itu ikut tercetak dan membuat jawaban dianggap salah.

**Keluaran.** Dua baris: `Debit: D liter/jam` lalu `Penuh: J jam M menit`, dengan M dari 0 sampai 59.

**Batasan.** Luas atap 20–800; intensitas 2–80; volume tangki 200–20000.

Mode banding `trimmed`, batas waktu 5000 ms, 2 tes contoh dan 10 tes tersembunyi.
