# Lembar tinjauan — DDP101 pertemuan 6

Sumber: `docs/soal/ddp101-pertemuan-6.json`. Status setelah impor: menunggu tinjauan (`PENDING_REVIEW`).

| Tingkat | Jumlah | Poin |
|---|---|---|
| Mudah sekali | 8 | 24 |
| Mudah | 8 | 40 |
| Mudah-sedang | 6 | 48 |
| Sedang | 6 | 60 |
| Sulit | 4 | 60 |
| **Semua** | **32** | **232** |

## Semua soal

- **Mudah sekali:** Volume Kubus (`volume-kubus`); Sapa Tiga Orang (`sapa-tiga-orang`); Jarak Dua Bilangan (`jarak-dua-bilangan`); Tiga Garis (`tiga-garis`); Tukar Dua Bilangan (`tukar-dua-bilangan`); Gandakan (`gandakan`); Bandingkan Luas (`bandingkan-luas`); Jumlah Kuadrat (`jumlah-kuadrat`)
- **Mudah:** Selisih Dua Waktu (`selisih-dua-waktu`); Hitung Tahun Kabisat (`hitung-kabisat`); Terkecil, Terbesar, Rentang (`rentang-tiga`); Banyak Faktor (`banyak-faktor`); Bingkai (`bingkai`); Digit Terbesar (`digit-terbesar`); Poin Pertandingan (`poin-pertandingan`); Kelipatan Terdekat ke Atas (`kelipatan-terdekat`)
- **Mudah-sedang:** Tukar Melingkar (`tukar-melingkar`); Urutkan Empat Bilangan (`urutkan-empat`); Bilangan Sempurna (`bilangan-sempurna`); Bilangan Armstrong (`bilangan-armstrong`); Geser Titik (`geser-titik`); Akar Digital (`akar-digital`)
- **Sedang:** Maju k Hari (`maju-k-hari`); Rekap Penjualan (`rekap-penjualan`); KPK Tiga Bilangan (`kpk-tiga-bilangan`); Hitung Kata (`hitung-kata`); Ubah ke Basis Lain (`ke-basis`); Keliling Segitiga dari Titik (`keliling-segitiga-titik`)
- **Sulit:** Kalkulator Pecahan (`kalkulator-pecahan`); Dugaan Goldbach (`goldbach`); Algoritma Luhn (`luhn`); Angka Romawi ke Bilangan (`romawi-ke-angka`)

## Pernyataan lengkap: dua soal per tingkat

### Mudah sekali — Volume Kubus (`volume-kubus`)

Tulis fungsi `int volumeKubus(int sisi)` yang mengembalikan volume kubus dengan panjang sisi tertentu, yaitu sisi × sisi × sisi.

Di `main`, baca panjang sisi, panggil fungsi itu, lalu cetak hasilnya.

Untuk sisi 3 jawabannya 27.

**Masukan.** Satu bilangan bulat: panjang sisi.

**Keluaran.** Satu bilangan bulat: volume kubus.

**Batasan.** 1 ≤ sisi ≤ 100

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah sekali — Jumlah Kuadrat (`jumlah-kuadrat`)

Tulis fungsi `int kuadrat(int x)` yang mengembalikan x dikali x.

Di `main`, baca tiga bilangan, lalu cetak jumlah kuadrat ketiganya dengan memanggil `kuadrat` tiga kali. Nilai kembalian fungsi bisa langsung dipakai di dalam hitungan.

Untuk `1 -2 3` jawabannya 1 + 4 + 9 = 14.

**Masukan.** Satu baris berisi tiga bilangan bulat.

**Keluaran.** Satu bilangan bulat: jumlah kuadrat ketiganya.

**Batasan.** Tiap bilangan antara −100 dan 100.

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah — Selisih Dua Waktu (`selisih-dua-waktu`)

Tulis fungsi `int keDetik(int jam, int menit, int detik)` yang mengubah sebuah waktu menjadi banyak detik sejak tengah malam.

Di `main`, baca dua waktu pada hari yang sama (waktu kedua tidak lebih awal), lalu cetak selisihnya dalam detik. Dengan fungsi itu, hitungan yang sama tidak perlu ditulis dua kali.

Contoh: dari 8:15:30 sampai 10:00:00 ada 6270 detik.

**Masukan.** Baris pertama: jam, menit, detik waktu pertama. Baris kedua: jam, menit, detik waktu kedua.

**Keluaran.** Satu bilangan bulat: selisih dalam detik.

**Batasan.** 0 ≤ jam ≤ 23; 0 ≤ menit, detik ≤ 59

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah — Kelipatan Terdekat ke Atas (`kelipatan-terdekat`)

Barang dijual dalam kemasan berisi k buah, jadi pesanan harus dibulatkan ke atas ke kelipatan k.

Tulis fungsi `int bulatkanKeAtas(int n, int k)` yang mengembalikan kelipatan k terkecil yang tidak kurang dari n. Di `main`, baca n dan k dan cetak hasilnya.

Contoh: `17 5` → 20; `20 5` → 20 (sudah kelipatan 5).

**Masukan.** Satu baris berisi n dan k.

**Keluaran.** Satu bilangan bulat: kelipatan k terkecil yang ≥ n.

**Batasan.** 1 ≤ n ≤ 10000; 1 ≤ k ≤ 100

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah-sedang — Tukar Melingkar (`tukar-melingkar`)

Tiga orang duduk melingkar dan masing-masing memegang sebuah kartu bernomor. Pada satu putaran, setiap orang menyerahkan kartunya ke orang di sebelah kanannya: kartu a pindah ke b, kartu b pindah ke c, dan kartu c pindah ke a.

Tulis fungsi `void putar(int &a, int &b, int &c)` yang melakukan satu putaran. Di `main`, baca ketiga nomor kartu dan banyak putaran k, panggil `putar` k kali, lalu cetak kartu yang kini dipegang a, b, c.

Contoh: kartu `1 2 3` setelah 1 putaran menjadi `3 1 2`, setelah 2 putaran menjadi `2 3 1`.

**Masukan.** Baris pertama: tiga bilangan bulat, kartu a, b, c. Baris kedua: k.

**Keluaran.** Satu baris: kartu a, b, c setelah k putaran.

**Batasan.** Nomor kartu antara 1 dan 99; 0 ≤ k ≤ 20

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah-sedang — Akar Digital (`akar-digital`)

Jumlahkan digit-digit sebuah bilangan. Bila hasilnya masih lebih dari satu digit, jumlahkan lagi digit-digitnya, dan seterusnya sampai tersisa satu digit. Digit terakhir itu disebut *akar digital*.

Contoh: 9875 → 9 + 8 + 7 + 5 = 29 → 2 + 9 = 11 → 1 + 1 = 2. Akar digitalnya 2, diperoleh dalam 3 langkah.

Tulis fungsi `int jumlahDigit(int n)`. Di `main`, panggil fungsi itu berulang-ulang, lalu cetak akar digital dan banyak langkahnya. Bilangan yang sudah satu digit tidak memerlukan langkah apa pun: untuk 7 keluarannya `7 0`.

**Masukan.** Satu bilangan bulat n.

**Keluaran.** Satu baris: akar digital n, lalu banyak langkah.

**Batasan.** 0 ≤ n ≤ 1000000000

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Sedang — Maju k Hari (`maju-k-hari`)

Diberikan sebuah tanggal dan bilangan k. Tanggal berapakah k hari sesudahnya?

Susun program dari tiga fungsi, masing-masing memakai fungsi sebelumnya:

- `bool kabisat(int tahun)` — tahun kabisat: habis dibagi 400, atau habis dibagi 4 tetapi tidak habis dibagi 100;
- `int hariDalamBulan(int bulan, int tahun)` — 28 atau 29 untuk Februari; 30 untuk April, Juni, September, November; 31 untuk lainnya;
- `void besok(int &hari, int &bulan, int &tahun)` — memajukan tanggal milik pemanggil satu hari.

Di `main`, panggil `besok` sebanyak k kali, lalu cetak tanggalnya.

Contoh: `28 2 2024 2` → `1 3 2024`; `31 12 2025 1` → `1 1 2026`.

**Masukan.** Satu baris berisi empat bilangan bulat: hari, bulan, tahun, dan k.

**Keluaran.** Satu baris: hari, bulan, tahun dari tanggal k hari kemudian.

**Batasan.** Tanggal sah antara tahun 1900 dan 2099; 0 ≤ k ≤ 1000

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Sedang — Keliling Segitiga dari Titik (`keliling-segitiga-titik`)

Jarak dua titik (x1, y1) dan (x2, y2) adalah akar dari (x2 − x1)² + (y2 − y1)².

Tulis fungsi `double jarak(int x1, int y1, int x2, int y2)`. Fungsi `sqrt` untuk akar kuadrat ada di `<cmath>`.

Di `main`, baca tiga titik sudut sebuah segitiga, lalu cetak kelilingnya dengan dua angka di belakang koma. Fungsi `jarak` dipanggil tiga kali, sekali untuk tiap sisi.

Contoh: titik (0, 0), (3, 0), dan (0, 4) membentuk segitiga dengan sisi 3, 4, 5, sehingga kelilingnya `12.00`.

**Masukan.** Tiga baris, masing-masing berisi x dan y sebuah titik sudut.

**Keluaran.** Keliling segitiga dengan dua angka di belakang koma.

**Batasan.** Semua koordinat bilangan bulat antara −20 dan 20; ketiga titik berbeda.

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Sulit — Kalkulator Pecahan (`kalkulator-pecahan`)

Buat kalkulator untuk dua pecahan. Masukannya berbentuk `a/b op c/d`, misalnya `1/2 + 1/3`, dengan `op` salah satu dari `+`, `-`, `*`, `/`.

Hasilnya harus berupa pecahan **paling sederhana**:

- pembilang dan penyebut tidak punya faktor persekutuan selain 1;
- tanda minus, bila ada, ditulis di depan pembilang;
- bila penyebutnya 1, cukup tulis pembilangnya saja.

Tulis fungsi `int fpb(int a, int b)` dan `void sederhanakan(int &pembilang, int &penyebut)` yang menyederhanakan pecahan milik pemanggil.

Contoh:

- `1/2 + 1/3` → `5/6`
- `1/3 - 1/2` → `-1/6`
- `3/4 * 2/3` → `1/2`
- `1/2 / 1/4` → `2`
- `3/7 - 3/7` → `0`

Petunjuk membaca masukan: `cin >> a >> garis >> b >> op >> c >> garis >> d` dengan `garis` dan `op` bertipe `char`.

**Masukan.** Satu baris: pecahan pertama, operator, pecahan kedua, dipisah spasi.

**Keluaran.** Hasilnya dalam bentuk paling sederhana.

**Batasan.** Semua pembilang dan penyebut di masukan antara 1 dan 100.

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Sulit — Angka Romawi ke Bilangan (`romawi-ke-angka`)

Kebalikan dari soal pertemuan lalu: baca sebuah angka Romawi, lalu cetak nilainya sebagai bilangan biasa.

Nilai lambangnya: `I` = 1, `V` = 5, `X` = 10, `L` = 50, `C` = 100, `D` = 500, `M` = 1000.

Aturannya sederhana: lambang-lambang dijumlahkan, **kecuali** lambang yang nilainya lebih kecil daripada lambang tepat di sebelah kanannya — lambang seperti itu dikurangkan. Pada `XIV`, `I` berada di kiri `V` yang lebih besar, sehingga nilainya 10 − 1 + 5 = 14.

Tulis fungsi `int nilai(char c)` yang mengembalikan nilai satu lambang, lalu pakai di `main`.

Contoh: `XLIX` → 49; `MCMXCIV` → 1994; `MMXXVI` → 2026.

**Masukan.** Satu baris: angka Romawi yang ditulis dengan benar, huruf besar.

**Keluaran.** Satu bilangan bulat: nilainya.

**Batasan.** Nilainya antara 1 dan 3999.

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.
