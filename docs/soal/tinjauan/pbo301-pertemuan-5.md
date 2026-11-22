# Lembar tinjauan — PBO301 pertemuan 5

Sumber: `docs/soal/pbo301-pertemuan-5.json`. Status setelah impor: menunggu tinjauan (`PENDING_REVIEW`).

| Tingkat | Jumlah | Poin |
|---|---|---|
| Mudah sekali | 8 | 24 |
| Mudah | 8 | 40 |
| Mudah-sedang | 6 | 48 |
| Sedang | 6 | 60 |
| Sulit | 4 | 60 |
| **Semua** | **32** | **232** |

## Semua soal

- **Mudah sekali:** Garis dari Dua Titik (`garis-dua-titik`); Alamat Mahasiswa (`alamat-mahasiswa`); Mesin di Dalam Mobil (`mesin-mobil`); Tim dan Pelatih (`tim-pelatih`); Persegi Panjang dari Dua Sudut (`luas-dari-sudut`); Umur Seseorang (`umur-orang`); Dosen Pembimbing (`dosen-pembimbing`); Sepeda dan Rodanya (`sepeda-roda`)
- **Mudah:** Keranjang Belanja (`keranjang-belanja`); Urutan Komponen (`urutan-komponen`); Regu dan Prajurit (`regu-prajurit`); Salinan atau Rujukan (`salinan-atau-rujukan`); Daftar Putar (`daftar-putar`); Segitiga dari Tiga Titik (`segitiga-titik`); Transkrip dan IPK (`transkrip-ipk`); Kamar Hotel (`kamar-hotel`)
- **Mudah-sedang:** Peleton, Regu, Prajurit (`peleton-regu`); Rumah, Kamar, Jendela (`rumah-kamar`); Gudang Pemilik Peti (`gudang-pemilik-peti`); Pameran Lukisan (`pameran-lukisan`); Peminjaman Buku (`peminjaman-buku`); Jadwal Kuliah Bentrok (`jadwal-kuliah-bentrok`)
- **Sedang:** Peta Jalan (`peta-jalan`); Struktur Kompi (`struktur-kompi`); Tagihan Meja (`tagihan-meja`); Kereta dan Gerbong (`kereta-gerbong`); Tim Proyek (`tim-proyek`); Biaya Rakitan (`biaya-rakitan`)
- **Sulit:** Simulasi Kepemilikan (`simulasi-kepemilikan`); Bank, Nasabah, Rekening (`bank-nasabah`); Rakitan Bertingkat (`rakitan-bertingkat`); Antrean Klinik (`klinik-antrean`)

## Pernyataan lengkap: dua soal per tingkat

### Mudah sekali — Garis dari Dua Titik (`garis-dua-titik`)

Objek bisa **tersusun dari objek lain**. Sebuah garis terdiri dari dua titik ujung: bila garisnya hilang, kedua titik ujungnya ikut hilang. Relasi "memiliki bagian" sekuat ini disebut **komposisi**, dan di C++ ditulis sebagai anggota data bertipe class lain.

Buat class `Titik` (atribut `int x`, `int y`) dan class `Garis` yang anggota datanya dua objek `Titik`: `awal` dan `akhir`. Beri `Garis` konstruktor `Garis(Titik a, Titik b)` dan method `int panjangManhattan() const` — |x awal − x akhir| + |y awal − y akhir|.

Baca dua titik dan cetak panjang Manhattan garisnya.

Contoh: (1, 2) dan (4, 6) → 7.

**Kode harus memuat class yang menyimpan objek class lain sebagai anggota data** (langsung, lewat penunjuk, atau dalam array): keluaran yang benar tanpa itu hanya mendapat setengah nilai.

**Masukan.** Dua baris, masing-masing berisi x dan y sebuah titik.

**Keluaran.** Satu bilangan bulat.

**Batasan.** Semua koordinat antara −100 dan 100.

**Konsep yang wajib ada di kode mahasiswa.** `CLASS_OBJECT`, `KOMPOSISI_AGREGASI`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah sekali — Sepeda dan Rodanya (`sepeda-roda`)

Buat class `Roda` dengan atribut `private` `int tekanan`, konstruktor `Roda(int t)`, dan method `bool cukup() const` — benar bila tekanannya 30 atau lebih.

Buat class `Sepeda` yang **terdiri dari dua `Roda`**: `depan` dan `belakang`. Konstruktor `Sepeda(int d, int b)` meneruskan tekanan ke masing-masing roda lewat daftar inisialisasi. Method `string status() const` bertanya kepada kedua rodanya — sepeda tidak mengintip `tekanan` secara langsung:

- kedua roda cukup: `siap jalan`;
- hanya roda depan kurang: `pompa roda depan`;
- hanya roda belakang kurang: `pompa roda belakang`;
- keduanya kurang: `pompa kedua roda`.

Contoh: `35 20` → `pompa roda belakang`.

**Kode harus memuat class yang menyimpan objek class lain sebagai anggota data** (langsung, lewat penunjuk, atau dalam array): keluaran yang benar tanpa itu hanya mendapat setengah nilai.

**Masukan.** Satu baris berisi tekanan roda depan dan roda belakang.

**Keluaran.** Satu baris status.

**Batasan.** Tiap tekanan antara 5 dan 60.

**Konsep yang wajib ada di kode mahasiswa.** `CLASS_OBJECT`, `KOMPOSISI_AGREGASI`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah — Keranjang Belanja (`keranjang-belanja`)

Satu keseluruhan bisa memuat **banyak** bagian (kelipatan 1..*). Di sini bagian-bagian itu disimpan dalam array objek di dalam class.

Buat class `Butir` (atribut `private` `string nama`, `int harga`, `int jumlah`; method `void isi(...)`, `string getNama() const`, `int subtotal() const`).

Buat class `Keranjang` yang menyimpan `Butir isi[10]` dan `int banyak`, dengan method `void tambah(const string &n, int h, int j)`, `int total() const` (menjumlahkan `subtotal()` tiap butir), dan `void cetak() const`.

Baca n barang (nama, harga satuan, jumlah). Cetak `<nama>: <subtotal>` untuk tiap barang, lalu `Total: T`.

Contoh: `buku 12000 2` dan `pena 3500 4` →

```
buku: 24000
pena: 14000
Total: 38000
```

**Kode harus memuat class yang menyimpan objek class lain sebagai anggota data** (langsung, lewat penunjuk, atau dalam array): keluaran yang benar tanpa itu hanya mendapat setengah nilai.

**Masukan.** Baris pertama: n. Lalu n baris berisi nama (satu kata), harga, dan jumlah.

**Keluaran.** n baris subtotal, lalu `Total: T`.

**Batasan.** 1 ≤ n ≤ 10; harga kelipatan 500 sampai 25000; 1 ≤ jumlah ≤ 9

**Konsep yang wajib ada di kode mahasiswa.** `CLASS_OBJECT`, `KOMPOSISI_AGREGASI`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah — Kamar Hotel (`kamar-hotel`)

Sebuah penginapan punya 5 kamar. Tiap kamar ditempati **paling banyak satu tamu** — asosiasi berkelipatan 0..1, yang bisa berubah dari waktu ke waktu.

Buat class `Tamu` (atribut `string nama`) dan class `Kamar` dengan atribut `private` `Tamu *penghuni` (mula-mula `nullptr`) dan method:

- `bool masuk(Tamu *t)` — gagal bila kamar sudah berpenghuni;
- `bool keluar()` — gagal bila kamar kosong;
- `string namaPenghuni() const` — nama penghuninya, atau `-`.

Perintah:

- `masuk <kamar> <nama>` → `Kamar <k>: <nama>`, atau `Kamar <k> sudah terisi`;
- `keluar <kamar>` → `Kamar <k> dikosongkan`, atau `Kamar <k> memang kosong`;
- `selesai` → cetak lima baris `<k>: <nama atau ->`.

Contoh: `masuk 2 Andi`, `masuk 2 Budi`, `keluar 4`, `keluar 2`, `masuk 5 Citra`, `selesai` →

```
Kamar 2: Andi
Kamar 2 sudah terisi
Kamar 4 memang kosong
Kamar 2 dikosongkan
Kamar 5: Citra
1: -
2: -
3: -
4: -
5: Citra
```

**Kode harus memuat class yang menyimpan objek class lain sebagai anggota data** (langsung, lewat penunjuk, atau dalam array): keluaran yang benar tanpa itu hanya mendapat setengah nilai.

**Masukan.** Beberapa baris perintah, diakhiri `selesai`.

**Keluaran.** Satu baris per perintah, lalu lima baris keadaan kamar.

**Batasan.** Paling banyak 15 perintah; nomor kamar 1 sampai 5; nama satu kata.

**Konsep yang wajib ada di kode mahasiswa.** `CLASS_OBJECT`, `KOMPOSISI_AGREGASI`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah-sedang — Peleton, Regu, Prajurit (`peleton-regu`)

Dalam organisasi militer, sebuah **peleton tersusun dari regu-regu** (komposisi: regu tidak ada di luar peletonnya), sedangkan sebuah **regu beranggotakan prajurit** (agregasi: prajurit tetap ada bila regunya dibubarkan).

Buat tiga class:

- `Prajurit` — `string nama`, `int kesehatan`;
- `Regu` — `string nama`, `Prajurit *anggota[10]`, `int banyak`; method `gabung(Prajurit *p)`, `getBanyak()`, `kekuatan()` (jumlah kesehatan anggotanya);
- `Peleton` — **tiga objek `Regu`** sebagai anggota langsung (`Regu regu[3]`); method `Regu &ambil(int nomor)`, `int totalAnggota() const`, dan `int terkuat() const` (nomor regu yang kekuatannya terbesar; bila sama, yang nomornya lebih kecil).

Masukan: nama ketiga regu; lalu n prajurit, masing-masing dengan nama, kesehatan, dan nomor regunya (1–3). Prajurit-prajurit itu sendiri disimpan di `main`.

Cetak untuk tiap regu `<nama>: <n> prajurit, kekuatan <k>`, lalu `Terkuat: <nama regu>` dan `Total: <banyak prajurit>`.

Contoh: regu `Alfa Bravo Charlie`; prajurit `Andi 50 1`, `Budi 80 3`, `Citra 60 1` →

```
Alfa: 2 prajurit, kekuatan 110
Bravo: 0 prajurit, kekuatan 0
Charlie: 1 prajurit, kekuatan 80
Terkuat: Alfa
Total: 3
```

**Kode harus memuat class yang menyimpan objek class lain sebagai anggota data** (langsung, lewat penunjuk, atau dalam array): keluaran yang benar tanpa itu hanya mendapat setengah nilai.

**Masukan.** Baris pertama: tiga nama regu. Baris kedua: n. Lalu n baris: nama, kesehatan, nomor regu.

**Keluaran.** Tiga baris regu, lalu `Terkuat: …` dan `Total: …`.

**Batasan.** 1 ≤ n ≤ 10; kesehatan antara 20 dan 100

**Konsep yang wajib ada di kode mahasiswa.** `CLASS_OBJECT`, `KOMPOSISI_AGREGASI`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah-sedang — Jadwal Kuliah Bentrok (`jadwal-kuliah-bentrok`)

Sebuah kuliah **berhubungan** dengan satu ruang dan satu dosen. Ruang dan dosen dipakai bersama oleh banyak kuliah, jadi `Kuliah` menyimpan **penunjuk** ke keduanya — dan dua kuliah memakai ruang yang sama tepat bila kedua penunjuknya sama.

Buat class `Ruang` dan `Dosen` (masing-masing `string nama`) dan class `Kuliah` dengan atribut `string kode`, `Ruang *ruang`, `Dosen *dosen`, `int hari`, `int jam`, serta method `string bentrokDengan(const Kuliah &lain) const`.

Dua kuliah bentrok bila **hari dan jamnya sama** dan ruangnya sama, dosennya sama, atau keduanya. Method itu mengembalikan sebabnya: `ruang <nama>`, `dosen <nama>`, `ruang <nama> dan dosen <nama>`, atau teks kosong bila tidak bentrok.

Masukan: daftar ruang; daftar dosen; lalu n kuliah — kode, nomor ruang, nomor dosen, hari, jam. Untuk setiap pasang kuliah yang bentrok (pasangan diperiksa menurut urutan masukan) cetak `<kode1> dan <kode2>: <sebab>`. Bila tidak ada, cetak `Tidak ada bentrok`.

Contoh: ruang `R101 Lab1`, dosen `Andi Budi`; kuliah `MK1 1 1 2 8`, `MK2 1 2 2 8`, `MK3 2 1 2 8`, `MK4 1 1 3 8` →

```
MK1 dan MK2: ruang R101
MK1 dan MK3: dosen Andi
```

**Kode harus memuat class yang menyimpan objek class lain sebagai anggota data** (langsung, lewat penunjuk, atau dalam array): keluaran yang benar tanpa itu hanya mendapat setengah nilai.

**Masukan.** Baris pertama: banyak ruang dan namanya. Baris kedua: banyak dosen dan namanya. Baris ketiga: n. Lalu n baris kuliah.

**Keluaran.** Satu baris per pasangan yang bentrok, atau `Tidak ada bentrok`.

**Batasan.** 1 sampai 4 ruang; 1 sampai 4 dosen; 1 ≤ n ≤ 7; hari 1–5; jam 8–15

**Konsep yang wajib ada di kode mahasiswa.** `CLASS_OBJECT`, `KOMPOSISI_AGREGASI`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Sedang — Peta Jalan (`peta-jalan`)

Sebuah peta **terdiri dari** kota-kota dan jalan-jalan (komposisi). Sebuah jalan **menghubungkan** dua kota (asosiasi): ia menyimpan penunjuk ke kedua kota ujungnya, yang hidup di dalam peta.

Buat class `Kota` (`string nama`), class `Jalan` (`Kota *dari`, `Kota *ke`, `int km`; method `bool menyentuh(const Kota *k) const`, `int getKm() const`), dan class `Peta` yang menyimpan `Kota kota[8]` dan `Jalan jalan[12]` dengan method `tambahKota`, `tambahJalan(namaA, namaB, km)`, `int jumlahJalan(int k) const`, `int totalKm(int k) const`, dan `void laporan() const`.

Masukan: daftar nama kota; lalu m jalan — nama dua kota dan panjangnya. Jalan bisa dilalui dua arah.

Untuk tiap kota, menurut urutan masukan, cetak `<nama>: <banyak jalan> jalan, <jumlah km> km`. Lalu cetak `Tersibuk: <nama>` — kota dengan jalan terbanyak; bila sama, yang lebih dulu di masukan.

Contoh: kota `Bandung Garut Solo`; jalan `Bandung Garut 60`, `Bandung Solo 450` →

```
Bandung: 2 jalan, 510 km
Garut: 1 jalan, 60 km
Solo: 1 jalan, 450 km
Tersibuk: Bandung
```

**Kode harus memuat class yang menyimpan objek class lain sebagai anggota data** (langsung, lewat penunjuk, atau dalam array): keluaran yang benar tanpa itu hanya mendapat setengah nilai.

**Masukan.** Baris pertama: n dan nama n kota. Baris kedua: m. Lalu m baris: dua nama kota dan panjang jalan.

**Keluaran.** Satu baris per kota, lalu `Tersibuk: …`.

**Batasan.** 2 ≤ n ≤ 8; 1 ≤ m ≤ 12; tidak ada dua jalan yang menghubungkan pasangan kota yang sama; 5 ≤ km ≤ 300

**Konsep yang wajib ada di kode mahasiswa.** `CLASS_OBJECT`, `KOMPOSISI_AGREGASI`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Sedang — Biaya Rakitan (`biaya-rakitan`)

Sebuah bengkel merakit perabot dari bahan-bahan di gudang. Bahan yang sama dipakai oleh banyak rakitan, jadi `Rakitan` tidak menyalin bahan — ia menyimpan **penunjuk ke bahan** beserta jumlah yang dipakai. Bila harga bahan di gudang berubah, biaya semua rakitan ikut berubah.

Buat class `Bahan` (`string nama`, `int harga`) dan class `Rakitan` dengan atribut `string nama`, `int ongkos`, `Bahan *bahan[4]`, `int jumlah[4]`, `int banyak`, serta method `void pakai(Bahan *b, int j)` dan `int biaya() const` — ongkos kerja ditambah harga × jumlah tiap bahan.

Masukan: b bahan (nama dan harga); lalu q rakitan, masing-masing satu baris: nama, ongkos, banyak bahan m, lalu m pasang `nomor bahan` dan `jumlah`.

Cetak `<nama>: <biaya>` untuk tiap rakitan, lalu `Termahal: <nama>`; bila ada yang sama, yang lebih dulu di masukan.

Contoh: bahan `papan 20000`, `baut 500`; rakitan `Rak 10000 2 1 3 2 12` dan `Kursi 5000 1 1 1` →

```
Rak: 76000
Kursi: 25000
Termahal: Rak
```

**Kode harus memuat class yang menyimpan objek class lain sebagai anggota data** (langsung, lewat penunjuk, atau dalam array): keluaran yang benar tanpa itu hanya mendapat setengah nilai.

**Masukan.** Baris pertama: b. Lalu b baris bahan. Lalu q. Lalu q baris rakitan.

**Keluaran.** Satu baris per rakitan, lalu `Termahal: …`.

**Batasan.** 1 ≤ b ≤ 6; 1 ≤ q ≤ 5; 1 ≤ m ≤ 4; harga kelipatan 500 sampai 25000; ongkos sampai 20000; jumlah 1 sampai 9

**Konsep yang wajib ada di kode mahasiswa.** `CLASS_OBJECT`, `KOMPOSISI_AGREGASI`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Sulit — Simulasi Kepemilikan (`simulasi-kepemilikan`)

Pertanyaan terpenting tentang relasi dua objek adalah: **siapa yang bertanggung jawab menghancurkannya?** Pemilik (komposisi) menghancurkan bagiannya; peminjam (agregasi) tidak.

Buat class `Bagian` (atribut `string nama`; konstruktor mencetak `+<nama>`, destruktor `-<nama>`).

Buat class `Pemilik` yang menyimpan dua daftar penunjuk ke `Bagian`: `milik` (dimiliki) dan `pinjaman` (dipinjam). Konstruktornya mencetak `+<nama>`. **Destruktornya mencetak `-<nama>`, lalu men-`delete` semua bagian yang dimilikinya menurut urutan dimiliki; bagian pinjaman tidak disentuh.**

Perintah:

- `bagian X` — buat bagian bebas bernama X;
- `pemilik P` — buat pemilik bernama P;
- `miliki P X` — P mengambil alih bagian **bebas** X; sejak itu X bukan bagian bebas lagi;
- `pinjam P X` — P mencatat bagian bebas X sebagai pinjaman; X tetap bebas;
- `hapus P` — hancurkan pemilik P;
- `selesai` — hancurkan semua pemilik yang tersisa menurut urutan pembuatan, lalu semua bagian yang masih bebas menurut urutan pembuatan.

Perintah `miliki`, `pinjam`, atau `hapus` yang menyebut pemilik yang tidak ada (belum dibuat atau sudah dihapus), atau bagian yang tidak bebas (belum dibuat atau sudah dimiliki), dijawab `Tidak bisa`.

Contoh: `bagian a`, `bagian b`, `pemilik P`, `miliki P a`, `pinjam P b`, `miliki P a`, `hapus P`, `selesai` →

```
+a
+b
+P
Tidak bisa
-P
-a
-b
```

**Kode harus memuat class yang menyimpan objek class lain sebagai anggota data** (langsung, lewat penunjuk, atau dalam array): keluaran yang benar tanpa itu hanya mendapat setengah nilai.

**Masukan.** Beberapa baris perintah, diakhiri `selesai`.

**Keluaran.** Satu baris per pembuatan, penghancuran, dan penolakan.

**Batasan.** Paling banyak 18 perintah; nama bagian satu huruf kecil, nama pemilik satu huruf besar; sebuah nama tidak pernah dibuat dua kali.

**Konsep yang wajib ada di kode mahasiswa.** `CLASS_OBJECT`, `KOMPOSISI_AGREGASI`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Sulit — Antrean Klinik (`klinik-antrean`)

Sebuah klinik **memiliki** dokter-dokter dan catatan semua pasien yang datang (komposisi). Tiap dokter punya **antrean pasien** — penunjuk ke pasien di catatan klinik (agregasi): pasien tidak dimiliki dokter, hanya sedang menunggunya.

Buat class `Pasien` (`string nama`, `bool darurat`), class `Dokter` (`string nama`, `Pasien *antre[20]`, `int banyak`), dan class `Klinik` (`Dokter dokter[3]`, `Pasien pasien[20]`).

Perintah:

- `datang <nama> <biasa|darurat>` — pasien dicatat klinik, lalu diarahkan ke **dokter yang antreannya paling pendek** (bila sama, dokter yang lebih dulu di masukan). Pasien `biasa` masuk di belakang antrean. Pasien `darurat` masuk **di depan semua pasien biasa, tetapi di belakang pasien darurat yang sudah menunggu**. Cetak `<nama> -> Dokter <dokter> (urutan <k>)`, dengan k posisinya di antrean itu;
- `panggil <dokter>` — dokter memeriksa pasien terdepan: `Dokter <dokter> memeriksa <nama>`, atau `Dokter <dokter> tidak punya pasien`;
- `selesai` — untuk tiap dokter cetak `<dokter>:` diikuti nama-nama di antreannya, atau ` -` bila kosong.

Contoh: dokter `Hasan Ratna`; `datang Andi biasa`, `datang Budi biasa`, `datang Citra darurat`, `datang Dewi darurat`, `panggil Hasan`, `panggil Wati`, `selesai` →

```
Andi -> Dokter Hasan (urutan 1)
Budi -> Dokter Ratna (urutan 1)
Citra -> Dokter Hasan (urutan 1)
Dewi -> Dokter Ratna (urutan 1)
Dokter Hasan memeriksa Citra
Hasan: Andi
Ratna: Dewi Budi
```

(Perintah `panggil` untuk dokter yang tidak ada di klinik tidak mencetak apa-apa.)

**Kode harus memuat class yang menyimpan objek class lain sebagai anggota data** (langsung, lewat penunjuk, atau dalam array): keluaran yang benar tanpa itu hanya mendapat setengah nilai.

**Masukan.** Baris pertama: banyak dokter dan namanya. Lalu perintah-perintah, diakhiri `selesai`.

**Keluaran.** Satu baris per perintah yang menghasilkan keluaran, lalu satu baris per dokter.

**Batasan.** 1 sampai 3 dokter; paling banyak 20 perintah; nama pasien semuanya berbeda.

**Konsep yang wajib ada di kode mahasiswa.** `CLASS_OBJECT`, `KOMPOSISI_AGREGASI`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.
