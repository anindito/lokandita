# Lembar tinjauan — PBO301 pertemuan 2

Sumber: `docs/soal/pbo301-pertemuan-2.json`. Status setelah impor: menunggu tinjauan (`PENDING_REVIEW`).

| Tingkat | Jumlah | Poin |
|---|---|---|
| Mudah sekali | 8 | 24 |
| Mudah | 8 | 40 |
| Mudah-sedang | 6 | 48 |
| Sedang | 6 | 60 |
| Sulit | 4 | 60 |
| **Semua** | **32** | **232** |

## Semua soal

- **Mudah sekali:** Class Persegi (`kelas-persegi`); Class Balok (`kelas-balok`); Perkenalan (`perkenalan`); Penghitung (`penghitung`); Class Suhu (`kelas-suhu`); Jarak Manhattan (`jarak-manhattan`); Saklar Lampu (`saklar-lampu`); Persegi atau Bukan (`persegi-atau-bukan`)
- **Mudah:** Tangki Air (`tangki-air`); Jarak Dua Titik (`jarak-dua-titik`); Tambah Menit (`waktu-tambah-menit`); Class Pecahan (`kelas-pecahan`); Prajurit (`prajurit`); Rantai Method (`rantai-method`); Tiket dan Anggota Statis (`tiket-statis`); Kendaraan dan Bensin (`kendaraan-bensin`)
- **Mudah-sedang:** Daftar Lulus (`daftar-lulus`); Persegi Panjang Terluas (`persegi-panjang-terluas`); Senjata (`senjata`); Operasi Vektor (`operasi-vektor`); Jam Berdetak (`jam-berdetak`); Nilai Gudang (`nilai-gudang`)
- **Sedang:** Pangkat Matriks 2 × 2 (`matriks-pangkat`); Class Tumpukan (`kelas-tumpukan`); Class Polinom (`kelas-polinom`); Hari Apa? (`nama-hari-tanggal`); Bank Mini (`bank-mini`); Robot Penjelajah (`robot-penjelajah`)
- **Sulit:** Tangan Poker (`tangan-poker`); Penjadwal Bergilir (`penjadwal-bergilir`); Faktorial Besar (`faktorial-besar`); Mesin Penjual Otomatis (`mesin-penjual`)

## Pernyataan lengkap: dua soal per tingkat

### Mudah sekali — Class Persegi (`kelas-persegi`)

Sebuah class menyatukan **data** (atribut) dan **fungsi yang mengolah data itu** (method).

Buat class `Persegi` dengan:

- atribut `int sisi`;
- method `int luas()`;
- method `int keliling()`.

Di `main`, buat satu objek `Persegi`, isi `sisi`-nya dari masukan, lalu cetak luas dan kelilingnya dengan memanggil kedua method.

Semua anggota ditulis di bagian `public:`.

Untuk sisi 5 keluarannya `25 20`.

**Kode harus memuat class**: keluaran yang benar tanpa class hanya mendapat setengah nilai.

**Masukan.** Satu bilangan bulat: panjang sisi.

**Keluaran.** Satu baris: luas, lalu keliling.

**Batasan.** 1 ≤ sisi ≤ 1000

**Konsep yang wajib ada di kode mahasiswa.** `CLASS_OBJECT`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah sekali — Persegi atau Bukan (`persegi-atau-bukan`)

Buat class `PersegiPanjang` dengan atribut `int panjang`, `int lebar`, method `bool persegi()` yang benar bila panjang sama dengan lebar, dan method `int luas()`.

Baca sebuah persegi panjang, lalu cetak `persegi` atau `bukan persegi`, diikuti luasnya.

Contoh: `5 5` → `persegi 25`; `4 6` → `bukan persegi 24`.

**Kode harus memuat class**: keluaran yang benar tanpa class hanya mendapat setengah nilai.

**Masukan.** Satu baris berisi panjang dan lebar.

**Keluaran.** `persegi` atau `bukan persegi`, satu spasi, lalu luasnya.

**Batasan.** 1 ≤ panjang, lebar ≤ 100

**Konsep yang wajib ada di kode mahasiswa.** `CLASS_OBJECT`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah — Tangki Air (`tangki-air`)

Buat class `Tangki` dengan atribut `int kapasitas` dan `int isi` (mula-mula 0), serta dua method yang **mengembalikan banyak liter yang benar-benar berpindah**:

- `int tuang(int liter)` — mengisi tangki, tetapi tidak pernah melebihi kapasitas; kelebihannya terbuang;
- `int pakai(int liter)` — mengambil air, tetapi tidak lebih dari yang ada.

Masukan: kapasitas, banyak perintah n, lalu n perintah `isi x` atau `pakai x`. Untuk tiap perintah cetak nilai kembalian method. Di akhir cetak `Sisa: k`.

Contoh: kapasitas 100; `isi 70` → 70; `isi 50` → 30; `pakai 120` → 100; `pakai 5` → 0; lalu `Sisa: 0`.

**Kode harus memuat class**: keluaran yang benar tanpa class hanya mendapat setengah nilai.

**Masukan.** Baris pertama: kapasitas. Baris kedua: n. Lalu n baris perintah.

**Keluaran.** n baris berisi liter yang berpindah, lalu satu baris `Sisa: k`.

**Batasan.** 50 ≤ kapasitas ≤ 500; 1 ≤ n ≤ 15; 1 ≤ x ≤ 500

**Konsep yang wajib ada di kode mahasiswa.** `CLASS_OBJECT`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah — Kendaraan dan Bensin (`kendaraan-bensin`)

Buat class `Kendaraan` dengan atribut `int bensin` (dalam mililiter), `int konsumsi` (mililiter per kilometer), `int odometer` (kilometer yang sudah ditempuh, mula-mula 0), dan method:

- `bool jalan(int km)` — bila bensinnya cukup untuk menempuh km kilometer, kurangi bensin, naikkan odometer, dan kembalikan `true`; bila tidak cukup, **jangan ubah apa pun** dan kembalikan `false`.

Kendaraan diminta menempuh n perjalanan berturut-turut. Untuk tiap perjalanan cetak `jalan` atau `bensin tidak cukup`. Di akhir cetak `Odometer: x km, bensin: y ml`.

Contoh: bensin 10000 ml, konsumsi 80 ml/km, perjalanan `50 100 60` →

```
jalan
bensin tidak cukup
jalan
Odometer: 110 km, bensin: 1200 ml
```

**Kode harus memuat class**: keluaran yang benar tanpa class hanya mendapat setengah nilai.

**Masukan.** Baris pertama: bensin dan konsumsi. Baris kedua: n. Baris ketiga: n bilangan, jarak tiap perjalanan.

**Keluaran.** Satu baris per perjalanan, lalu satu baris ringkasan.

**Batasan.** 5000 ≤ bensin ≤ 60000; 40 ≤ konsumsi ≤ 120; 1 ≤ n ≤ 8; 1 ≤ jarak ≤ 5000

**Konsep yang wajib ada di kode mahasiswa.** `CLASS_OBJECT`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah-sedang — Daftar Lulus (`daftar-lulus`)

Buat class `Mahasiswa` dengan atribut `string nama`, `int tugas`, `int uts`, `int uas` dan dua method:

- `int nilaiAkhir()` — (30 × tugas + 30 × uts + 40 × uas) / 100, dengan pembagian bulat;
- `bool lulus()` — benar bila nilai akhirnya 60 atau lebih. Method ini **memanggil method lain** dari objek yang sama.

Simpan n mahasiswa dalam **array of objects**. Cetak nama dan nilai akhir mahasiswa yang lulus, sesuai urutan masukan, lalu `Lulus: k dari n`.

Contoh: `Andi 80 70 60` dan `Budi 40 50 55` →

```
Andi 69
Lulus: 1 dari 2
```

**Kode harus memuat class**: keluaran yang benar tanpa class hanya mendapat setengah nilai.

**Masukan.** Baris pertama: n. Lalu n baris, masing-masing berisi nama, nilai tugas, UTS, dan UAS.

**Keluaran.** Satu baris `nama nilaiAkhir` untuk tiap mahasiswa yang lulus, lalu `Lulus: k dari n`.

**Batasan.** 1 ≤ n ≤ 20; tiap nilai antara 0 dan 100

**Konsep yang wajib ada di kode mahasiswa.** `CLASS_OBJECT`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah-sedang — Nilai Gudang (`nilai-gudang`)

Buat class `Barang` dengan atribut `string nama`, `int harga`, `int stok` dan method `int nilai()` yang mengembalikan harga × stok.

Sebuah gudang menyimpan n jenis barang. Cetak dua baris:

- `Total: T` — jumlah nilai semua barang;
- `Terbesar: <nama>` — barang yang nilainya paling besar; bila ada yang sama, yang paling dulu di masukan.

Contoh: `Beras 12000 10`, `Gula 15000 4`, `Kopi 40000 3` →

```
Total: 300000
Terbesar: Beras
```

**Kode harus memuat class**: keluaran yang benar tanpa class hanya mendapat setengah nilai.

**Masukan.** Baris pertama: n. Lalu n baris, masing-masing berisi nama (satu kata), harga, dan stok.

**Keluaran.** Dua baris seperti contoh.

**Batasan.** 1 ≤ n ≤ 20; 500 ≤ harga ≤ 50000; 0 ≤ stok ≤ 50

**Konsep yang wajib ada di kode mahasiswa.** `CLASS_OBJECT`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Sedang — Pangkat Matriks 2 × 2 (`matriks-pangkat`)

Buat class `Matriks2` yang mewakili matriks 2 × 2

```
a b
c d
```

dengan atribut `long long a, b, c, d` dan dua method:

- `Matriks2 kali(Matriks2 lain)` — hasil kali matriks ini dengan `lain` (matriks ini di sebelah kiri), sebagai objek baru;
- `long long determinan()` — a × d − b × c.

Hasil kali dua matriks 2 × 2:

```
a·a' + b·c'   a·b' + b·d'
c·a' + d·c'   c·b' + d·d'
```

Baca sebuah matriks M dan bilangan n. Cetak Mⁿ (M dikalikan dengan dirinya n kali) dalam dua baris, lalu determinannya di baris ketiga.

Contoh: M = `1 1` / `1 0` dan n = 5 →

```
8 5
5 3
-1
```

**Kode harus memuat class**: keluaran yang benar tanpa class hanya mendapat setengah nilai.

**Masukan.** Baris pertama: a, b, c, d. Baris kedua: n.

**Keluaran.** Dua baris isi Mⁿ, lalu satu baris determinannya.

**Batasan.** 0 ≤ a, b, c, d ≤ 3; 1 ≤ n ≤ 10

**Konsep yang wajib ada di kode mahasiswa.** `CLASS_OBJECT`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Sedang — Robot Penjelajah (`robot-penjelajah`)

Sebuah robot berada di titik (0, 0) dan menghadap **utara** (arah y positif). Ia mengerti tiga perintah:

- `M` — maju satu langkah ke arah yang dihadapinya;
- `L` — berputar 90° ke kiri;
- `R` — berputar 90° ke kanan.

Buat class `Robot` dengan atribut `int x`, `int y`, `int arah` dan method `void maju()`, `void kiri()`, `void kanan()`, serta `string namaArah()` yang mengembalikan `utara`, `timur`, `selatan`, atau `barat`.

Baca deretan perintah, jalankan, lalu cetak posisi akhir dan arah hadap robot.

Contoh: `MMRMLM` → `1 3 utara`. Contoh lain: `RRM` → `0 -1 selatan`.

**Kode harus memuat class**: keluaran yang benar tanpa class hanya mendapat setengah nilai.

**Masukan.** Satu baris: deretan huruf `M`, `L`, `R` tanpa spasi.

**Keluaran.** Satu baris: x, y, dan arah hadap.

**Batasan.** Panjang deretan 1 sampai 100.

**Konsep yang wajib ada di kode mahasiswa.** `CLASS_OBJECT`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Sulit — Tangan Poker (`tangan-poker`)

Lima kartu di tangan seorang pemain poker dinilai menurut susunannya. Tiap kartu punya **nilai** 2 sampai 14 (11 = Jack, 12 = Queen, 13 = King, 14 = As) dan **jenis** `S`, `H`, `D`, atau `C`.

Peringkatnya, dari yang tertinggi; yang dipakai adalah yang pertama cocok:

1. `straight flush` — lima nilai berurutan, semua sejenis;
2. `four of a kind` — empat kartu bernilai sama;
3. `full house` — tiga kartu bernilai sama dan sepasang;
4. `flush` — semua sejenis;
5. `straight` — lima nilai berurutan;
6. `three of a kind` — tiga kartu bernilai sama;
7. `two pair` — dua pasang;
8. `one pair` — sepasang;
9. `high card` — selain itu.

As hanya bernilai 14: `14 2 3 4 5` bukan urutan.

Buat class `TanganPoker` dengan atribut `int nilai[5]`, `char jenis[5]` dan method `bool sewarna()`, `bool berurutan()`, `int banyakNilai(int n)` (berapa kartu bernilai n), serta `string peringkat()` yang memakai ketiganya.

Contoh: kartu `10 H`, `11 H`, `12 H`, `13 H`, `14 H` → `straight flush`. Kartu `5 S`, `5 D`, `9 C`, `9 H`, `2 S` → `two pair`.

**Kode harus memuat class**: keluaran yang benar tanpa class hanya mendapat setengah nilai.

**Masukan.** Lima baris, masing-masing berisi nilai dan jenis satu kartu.

**Keluaran.** Satu baris: peringkat tangan itu.

**Batasan.** 2 ≤ nilai ≤ 14; jenis `S`, `H`, `D`, atau `C`; kelima kartu berbeda.

**Konsep yang wajib ada di kode mahasiswa.** `CLASS_OBJECT`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Sulit — Mesin Penjual Otomatis (`mesin-penjual`)

Tirukan sebuah mesin penjual otomatis dengan k slot barang. Tiap slot punya nama barang, harga, dan stok.

Buat class `MesinPenjual` yang menyimpan semua slot (`string nama[5]`, `int harga[5]`, `int stok[5]`, `int banyak`), uang yang sedang dimasukkan pembeli (`int saldo`), dan `int pendapatan`, dengan method:

- `void isiSlot(string nama, int harga, int stok)` — menambah satu slot;
- `void masukkan(int uang)` — menambah saldo;
- `void pilih(int nomor)` — pembeli memilih slot (nomor mulai dari 1). Diperiksa berurutan: bila stoknya 0, cetak `Habis`; bila saldo kurang dari harga, cetak `Uang kurang`; selain itu stok berkurang satu, saldo berkurang sebesar harga, pendapatan bertambah, dan dicetak `Keluar: <nama>`;
- `void batal()` — mencetak `Kembali: <saldo>` lalu menolkan saldo.

Perintah datang satu per baris: `uang x`, `pilih n`, `batal`, dan terakhir `selesai`. Di akhir cetak `Pendapatan: T`. Saldo yang masih tersisa saat `selesai` tidak dikembalikan dan tidak dihitung sebagai pendapatan.

Contoh: satu slot `Teh 3000 1`; perintah `uang 2000`, `pilih 1`, `uang 2000`, `pilih 1`, `pilih 1`, `batal`, `selesai` →

```
Uang kurang
Keluar: Teh
Habis
Kembali: 1000
Pendapatan: 3000
```

**Kode harus memuat class**: keluaran yang benar tanpa class hanya mendapat setengah nilai.

**Masukan.** Baris pertama: k. Lalu k baris berisi nama (satu kata), harga, dan stok. Lalu perintah-perintah, diakhiri `selesai`.

**Keluaran.** Satu baris untuk tiap `pilih` dan `batal`, lalu `Pendapatan: T`.

**Batasan.** 1 ≤ k ≤ 5; paling banyak 25 perintah; harga kelipatan 1000 antara 1000 dan 10000; stok 0 sampai 3

**Konsep yang wajib ada di kode mahasiswa.** `CLASS_OBJECT`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.
