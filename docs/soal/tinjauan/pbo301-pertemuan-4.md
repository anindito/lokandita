# Lembar tinjauan — PBO301 pertemuan 4

Sumber: `docs/soal/pbo301-pertemuan-4.json`. Status setelah impor: menunggu tinjauan (`PENDING_REVIEW`).

| Tingkat | Jumlah | Poin |
|---|---|---|
| Mudah sekali | 8 | 24 |
| Mudah | 8 | 40 |
| Mudah-sedang | 6 | 48 |
| Sedang | 6 | 60 |
| Sulit | 4 | 60 |
| **Semua** | **32** | **232** |

## Semua soal

- **Mudah sekali:** Kotak Terlindung (`kotak-terlindung`); Umur yang Sah (`umur-valid`); Penghitung Tak Negatif (`penghitung-tak-negatif`); Nilai yang Dibatasi (`nilai-terbatas`); PIN Kartu (`pin-kartu`); Volume Speaker (`volume-speaker`); Jam yang Sah (`jam-valid`); Hanya Bisa Dibaca (`persegi-baca-saja`)
- **Mudah:** Akun Bank (`akun-bank`); Kemajuan Membaca (`kemajuan-membaca`); Sensor dan mutable (`sensor-mutable`); Fungsi friend (`pecahan-sama`); Auditor Brankas (`auditor-brankas`); Rentang yang Terjaga (`rentang-terjaga`); Warna Heksadesimal (`warna-heks`); Ganti Kata Sandi (`ganti-sandi`)
- **Mudah-sedang:** Pecahan yang Selalu Rapi (`jumlah-pecahan`); Stok Gudang (`stok-gudang`); Termostat (`termostat`); Antrean Terbatas (`antrean-terbatas`); Statistik Terlindung (`statistik-terlindung`); Gembok Kombinasi (`gembok-kombinasi`)
- **Sedang:** Mutasi Rekening (`mutasi-rekening`); Papan XO (`papan-xo`); Himpunan Bilangan (`himpunan-bilangan`); Class Uang (`kelas-uang`); Pabrik Kartu (`pabrik-kartu`); Larik Aman (`larik-aman`)
- **Sulit:** Kartu ATM (`kartu-atm`); Pemesanan Ruang (`pesan-ruang`); Lift (`lift`); Cache LRU (`cache-lru`)

## Pernyataan lengkap: dua soal per tingkat

### Mudah sekali — Kotak Terlindung (`kotak-terlindung`)

Atribut di bagian `private:` hanya bisa disentuh oleh method class itu sendiri. Kode di luar class harus lewat method publik — dan method itulah yang menjaga agar data tidak pernah bernilai ngawur.

Buat class `Kotak`:

- `private`: `int sisi`, mula-mula 1;
- `public`: `void setSisi(int s)` — mengubah sisi **hanya bila s positif**; selain itu diabaikan;
- `public`: `int getSisi() const` dan `int luas() const`.

Baca sebuah bilangan, berikan ke `setSisi`, lalu cetak sisi dan luas kotak.

Contoh: 5 → `5 25`; −7 → `1 1` (ditolak, sisi tetap 1).

**Kode harus memuat class dengan anggota `private`**: keluaran yang benar tanpa itu hanya mendapat setengah nilai.

**Masukan.** Satu bilangan bulat s.

**Keluaran.** Satu baris: sisi, lalu luas.

**Batasan.** −50 ≤ s ≤ 100

**Konsep yang wajib ada di kode mahasiswa.** `CLASS_OBJECT`, `ENCAPSULATION`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah sekali — Hanya Bisa Dibaca (`persegi-baca-saja`)

Class yang punya getter tetapi **tidak punya setter** menghasilkan objek yang tidak bisa diubah setelah dibuat. Method yang tidak mengubah objek ditandai `const` di belakang namanya; hanya method seperti itu yang boleh dipanggil pada objek `const`.

Buat class `PersegiPanjang` dengan atribut `private` `int panjang`, `int lebar`, konstruktor `PersegiPanjang(int p, int l)`, dan method publik yang semuanya `const`: `getPanjang()`, `getLebar()`, `luas()`, `keliling()`.

Baca panjang dan lebar, buat objek `const PersegiPanjang`, lalu cetak tiga baris.

Untuk `4 6`:

```
4 x 6
Luas: 24
Keliling: 20
```

**Kode harus memuat class dengan anggota `private`**: keluaran yang benar tanpa itu hanya mendapat setengah nilai.

**Masukan.** Satu baris berisi panjang dan lebar.

**Keluaran.** Tiga baris seperti contoh.

**Batasan.** 1 ≤ panjang, lebar ≤ 100

**Konsep yang wajib ada di kode mahasiswa.** `CLASS_OBJECT`, `ENCAPSULATION`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah — Akun Bank (`akun-bank`)

Class `AkunBank` yang saldonya `public` bisa diubah siapa saja menjadi berapa saja. Perbaiki: sembunyikan datanya, dan biarkan hanya method yang memeriksa aturan yang boleh mengubahnya.

Buat class `AkunBank` dengan atribut `private` `string pemilik`, `int saldo`, konstruktor `AkunBank(const string &p, int awal)`, dan method publik:

- `bool setor(int jumlah)` — ditolak bila jumlahnya nol atau negatif;
- `bool tarik(int jumlah)` — ditolak bila jumlahnya nol, negatif, atau lebih besar daripada saldo;
- `int lihatSaldo() const`, `string getPemilik() const`.

Masukan: nama pemilik dan saldo awal, lalu perintah `setor x` atau `tarik x`, diakhiri `selesai`. Untuk tiap perintah cetak `Saldo: s` bila berhasil atau `Ditolak` bila tidak. Di akhir cetak `<pemilik>: <saldo>`.

Contoh: `Budi 100000`; `tarik 150000`, `setor -5000`, `setor 25000`, `tarik 125000`, `selesai` →

```
Ditolak
Ditolak
Saldo: 125000
Saldo: 0
Budi: 0
```

**Kode harus memuat class dengan anggota `private`**: keluaran yang benar tanpa itu hanya mendapat setengah nilai.

**Masukan.** Baris pertama: nama (satu kata) dan saldo awal. Lalu perintah-perintah, diakhiri `selesai`.

**Keluaran.** Satu baris per perintah, lalu satu baris ringkasan.

**Batasan.** 0 ≤ saldo awal ≤ 1000000; paling banyak 12 perintah; −500000 ≤ x ≤ 500000

**Konsep yang wajib ada di kode mahasiswa.** `CLASS_OBJECT`, `ENCAPSULATION`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah — Ganti Kata Sandi (`ganti-sandi`)

Buat class `Akun` dengan atribut `private` `string sandi`, konstruktor `Akun(const string &awal)`, dan method publik:

- `int ganti(const string &lama, const string &baru)` — mengganti kata sandi. Diperiksa berurutan: bila `lama` tidak sama dengan sandi saat ini, kembalikan 1; bila `baru` lemah (kurang dari 8 karakter, **atau** tidak memuat angka), kembalikan 2; selain itu ganti sandinya dan kembalikan 0;
- `bool masuk(const string &tebakan) const`.

Kata sandi tidak punya getter.

Masukan: sandi awal, banyak percobaan ganti n, lalu n baris berisi `lama baru`, dan terakhir satu tebakan untuk masuk. Cetak `Berhasil`, `Sandi lama salah`, atau `Sandi baru lemah` untuk tiap percobaan, lalu `Masuk berhasil` atau `Masuk gagal`.

Contoh: sandi awal `rahasia123`; percobaan `salah999x kuat12345` dan `rahasia123 pendek1` dan `rahasia123 sandibaru7`; tebakan `sandibaru7` →

```
Sandi lama salah
Sandi baru lemah
Berhasil
Masuk berhasil
```

**Kode harus memuat class dengan anggota `private`**: keluaran yang benar tanpa itu hanya mendapat setengah nilai.

**Masukan.** Baris pertama: sandi awal. Baris kedua: n. Lalu n baris berisi dua kata. Baris terakhir: satu tebakan.

**Keluaran.** n baris hasil, lalu satu baris hasil masuk.

**Batasan.** 1 ≤ n ≤ 6; kata sandi terdiri dari huruf kecil dan angka, 3 sampai 12 karakter.

**Konsep yang wajib ada di kode mahasiswa.** `CLASS_OBJECT`, `ENCAPSULATION`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah-sedang — Pecahan yang Selalu Rapi (`jumlah-pecahan`)

Sebuah objek `Pecahan` yang baik **selalu** dalam bentuk rapi: penyebutnya positif, dan pembilang serta penyebutnya tidak punya faktor persekutuan selain 1. Nol ditulis `0/1`.

Bila atributnya `private` dan satu-satunya jalan masuk adalah konstruktor yang merapikan, tidak mungkin ada objek yang tidak rapi.

Buat class `Pecahan` dengan:

- atribut `private` `int pembilang`, `int penyebut`;
- method `private` `void rapikan()`;
- konstruktor `Pecahan(int a, int b)` yang memanggil `rapikan()`;
- `static bool sah(int penyebut)` — salah bila penyebutnya 0;
- `Pecahan tambah(const Pecahan &lain) const` — mengembalikan pecahan baru;
- `void cetak() const`.

Baca n pasang bilangan (pembilang dan penyebut; penyebut boleh negatif). Pasangan berpenyebut 0 tidak bisa menjadi pecahan: cetak `Dilewati` dan abaikan. Jumlahkan sisanya dan cetak hasilnya.

Contoh: `1 2`, `1 -3`, `5 0`, `1 6` →

```
Dilewati
1/3
```

**Kode harus memuat class dengan anggota `private`**: keluaran yang benar tanpa itu hanya mendapat setengah nilai.

**Masukan.** Baris pertama: n. Lalu n baris berisi pembilang dan penyebut.

**Keluaran.** `Dilewati` untuk tiap pasangan berpenyebut 0, lalu jumlahnya dalam bentuk rapi.

**Batasan.** 1 ≤ n ≤ 8; pembilang antara −20 dan 20; penyebut antara −6 dan 12

**Konsep yang wajib ada di kode mahasiswa.** `CLASS_OBJECT`, `ENCAPSULATION`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah-sedang — Gembok Kombinasi (`gembok-kombinasi`)

Sebuah gembok terbuka bila tiga angka kodenya diputar **berturut-turut dalam urutan yang benar**. Gembok mengingat sudah berapa angka yang cocok; ingatan itu tersembunyi.

Buat class `Gembok` dengan atribut `private` `int kode[3]`, `int cocok` (mula-mula 0), `bool terbuka`; konstruktor `Gembok(int a, int b, int c)`; dan method publik `void putar(int angka)` serta `bool sudahTerbuka() const`.

Aturan `putar`, bila gembok belum terbuka:

- bila angkanya sama dengan angka kode yang sedang ditunggu, `cocok` bertambah satu;
- bila tidak, tetapi angkanya sama dengan angka **pertama** kode, `cocok` menjadi 1;
- selain itu `cocok` menjadi 0.

Begitu `cocok` mencapai 3, gembok terbuka dan putaran berikutnya diabaikan.

Baca kode, lalu n angka yang diputar. Cetak `Terbuka pada putaran k` (putaran pertama yang membukanya) atau `Tetap terkunci`.

Contoh: kode `3 3 7`, putaran `3 3 3 7` → `Tetap terkunci` (pada angka 3 yang ketiga, hitungan kembali ke 1).
Contoh: kode `4 1 9`, putaran `2 4 1 9 5` → `Terbuka pada putaran 4`.

**Kode harus memuat class dengan anggota `private`**: keluaran yang benar tanpa itu hanya mendapat setengah nilai.

**Masukan.** Baris pertama: tiga angka kode. Baris kedua: n. Baris ketiga: n angka.

**Keluaran.** `Terbuka pada putaran k` atau `Tetap terkunci`.

**Batasan.** Tiap angka antara 0 dan 9; 1 ≤ n ≤ 23

**Konsep yang wajib ada di kode mahasiswa.** `CLASS_OBJECT`, `ENCAPSULATION`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Sedang — Mutasi Rekening (`mutasi-rekening`)

Buat class `Rekening` yang selain saldo juga menyimpan **riwayat mutasi** dan menegakkan **batas penarikan harian**. Semua datanya `private`: `int saldo`, `int mutasi[20]`, `int banyak`, `int ditarik` (total penarikan yang berhasil), dan method bantu `void catat(int perubahan)`.

Method publik:

- `Rekening(int awal)`;
- `bool setor(int jumlah)` — ditolak bila jumlah nol atau negatif;
- `bool tarik(int jumlah)` — ditolak bila jumlah nol atau negatif, melebihi saldo, atau membuat **total penarikan melebihi 1000000**;
- `void cetakMutasi() const` — mencetak tiap transaksi yang **berhasil** menurut urutannya, setoran sebagai `+x` dan penarikan sebagai `-x`, lalu `Saldo: s`.

Transaksi yang ditolak tidak mengubah apa pun dan tidak masuk riwayat. Karena riwayat hanya bisa diisi oleh `setor` dan `tarik`, riwayat dan saldo tidak mungkin tidak cocok.

Masukan: saldo awal, lalu perintah `setor x` / `tarik x`, diakhiri `selesai`. Cetak `Ditolak` untuk tiap perintah yang gagal, lalu mutasinya.

Contoh: saldo awal 2000000; `tarik 700000`, `tarik 400000`, `setor 100000`, `tarik 300000`, `selesai` →

```
Ditolak
-700000
+100000
-300000
Saldo: 1100000
```

**Kode harus memuat class dengan anggota `private`**: keluaran yang benar tanpa itu hanya mendapat setengah nilai.

**Masukan.** Baris pertama: saldo awal. Lalu perintah-perintah, diakhiri `selesai`.

**Keluaran.** `Ditolak` untuk tiap perintah yang gagal, lalu riwayat mutasi dan saldo.

**Batasan.** 0 ≤ saldo awal ≤ 2000000; paling banyak 15 perintah; −50000 ≤ x ≤ 1000000

**Konsep yang wajib ada di kode mahasiswa.** `CLASS_OBJECT`, `ENCAPSULATION`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Sedang — Larik Aman (`larik-aman`)

Array biasa di C++ tidak memeriksa indeks: `a[10]` pada array berisi 5 elemen merusak memori tanpa peringatan. Bungkus array dinamis dalam class yang **tidak memberi jalan** untuk mengakses di luar batas.

Buat class `LarikAman` dengan atribut `private` `int *data`, `int ukuran`, method bantu `private` `bool sah(int i) const`, dan method publik:

- `LarikAman(int n)` — n elemen, semuanya 0; lengkapi dengan konstruktor salin, operator penugasan, dan destruktor (aturan tiga);
- `bool atur(int i, int x)` dan `bool ambil(int i, int &x) const` — gagal bila i di luar 0 … ukuran − 1;
- `void ubahUkuran(int n)` — mengubah banyak elemen; elemen lama yang masih muat dipertahankan, elemen baru bernilai 0;
- `int panjang() const`.

Masukan: ukuran awal, lalu perintah `atur i x`, `ambil i`, `ukuran n`, diakhiri `selesai`. Untuk `atur` atau `ambil` yang gagal cetak `Indeks di luar batas`; untuk `ambil` yang berhasil cetak nilainya. Di akhir cetak `Isi:` diikuti semua elemen.

Contoh: ukuran 3; `atur 1 5`, `atur 3 9`, `ukuran 5`, `atur 3 9`, `ambil 1`, `ambil -1`, `selesai` →

```
Indeks di luar batas
5
Indeks di luar batas
Isi: 0 5 0 9 0
```

**Kode harus memuat class dengan anggota `private`**: keluaran yang benar tanpa itu hanya mendapat setengah nilai.

**Masukan.** Baris pertama: ukuran awal. Lalu perintah-perintah, diakhiri `selesai`.

**Keluaran.** Satu baris untuk tiap `ambil` dan tiap `atur` yang gagal, lalu `Isi: …`.

**Batasan.** 1 ≤ ukuran ≤ 10; paling banyak 20 perintah; −2 ≤ i ≤ 12; 1 ≤ x ≤ 99

**Konsep yang wajib ada di kode mahasiswa.** `CLASS_OBJECT`, `ENCAPSULATION`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Sulit — Kartu ATM (`kartu-atm`)

Buat class `KartuAtm`. Seluruh keadaannya tersembunyi: `int pin`, `int saldo`, `int salah` (banyak PIN salah berturut-turut), `bool diblokir`, `bool sedangMasuk`. Tiap method publik mengembalikan **pesan** (`string`) yang harus dicetak.

**`string masuk(int tebakan)`** — diperiksa berurutan:
1. kartu diblokir → `Kartu diblokir`;
2. sudah masuk → `Sudah masuk`;
3. PIN benar → hitungan salah menjadi 0, status masuk → `Selamat datang`;
4. PIN salah → hitungan salah bertambah; bila mencapai 3, kartu diblokir → `Kartu diblokir`; bila belum → `PIN salah`.

**`string tarik(int jumlah)`** — belum masuk → `Belum masuk`; jumlah nol, negatif, bukan kelipatan 50000, atau melebihi saldo → `Ditolak`; selain itu saldo berkurang → `Sisa saldo <saldo>`.

**`string ganti(int lama, int baru)`** — belum masuk → `Belum masuk`; `lama` bukan PIN saat ini → `PIN lama salah`; `baru` bukan empat digit (1000–9999) atau sama dengan PIN saat ini → `PIN baru tidak sah`; selain itu PIN berganti → `PIN diganti`.

**`string keluar()`** — belum masuk → `Belum masuk`; selain itu → `Sampai jumpa`.

Pemblokiran tidak mengeluarkan orang yang sedang masuk, tetapi setelah keluar ia tidak bisa masuk lagi.

Masukan: PIN dan saldo awal, lalu perintah `masuk p`, `tarik x`, `ganti lama baru`, `keluar`, diakhiri `selesai`.

Contoh: `1234 500000`; `tarik 100000`, `masuk 1111`, `masuk 1234`, `tarik 75000`, `tarik 200000`, `ganti 1234 99`, `keluar`, `selesai` →

```
Belum masuk
PIN salah
Selamat datang
Ditolak
Sisa saldo 300000
PIN baru tidak sah
Sampai jumpa
```

**Kode harus memuat class dengan anggota `private`**: keluaran yang benar tanpa itu hanya mendapat setengah nilai.

**Masukan.** Baris pertama: PIN dan saldo awal. Lalu perintah-perintah, diakhiri `selesai`.

**Keluaran.** Satu baris pesan per perintah.

**Batasan.** PIN awal empat digit; 100000 ≤ saldo ≤ 2000000; paling banyak 20 perintah

**Konsep yang wajib ada di kode mahasiswa.** `CLASS_OBJECT`, `ENCAPSULATION`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Sulit — Cache LRU (`cache-lru`)

*Cache* menyimpan sejumlah kecil data yang sering dipakai. Bila penuh, salah satu harus dibuang; aturan **LRU** (*least recently used*) membuang data yang **paling lama tidak disentuh**.

Buat class `CacheLru` berkapasitas **3** pasangan kunci–nilai. Pembukuannya `private`: `int kunci[3]`, `int nilai[3]`, `int terakhir[3]` (kapan terakhir disentuh), `int banyak`, `int jam` (penghitung yang naik pada setiap sentuhan), serta method bantu `int cari(int k) const` dan `int tertua() const`. Method publik:

- `bool ambil(int k, int &v)` — bila kunci k ada, isi v dengan nilainya; pengambilan yang berhasil **dihitung sebagai sentuhan**;
- `int taruh(int k, int v)` — bila k sudah ada, ganti nilainya; bila belum dan cache penuh, buang dulu pasangan yang paling lama tidak disentuh. Selalu dihitung sebagai sentuhan. Mengembalikan kunci yang dibuang, atau 0 bila tidak ada;
- `void cetak() const` — mencetak `Isi:` diikuti pasangan `<kunci>=<nilai>`, **dari yang paling baru disentuh**.

Perintah: `taruh k v` (cetak `Buang <kunci>` bila ada yang dibuang), `ambil k` (cetak nilainya atau `tidak ada`), `selesai`. Di akhir panggil `cetak()`.

Contoh: `taruh 1 10`, `taruh 2 20`, `taruh 3 30`, `ambil 1`, `taruh 4 40`, `ambil 2`, `selesai` →

```
10
Buang 2
tidak ada
Isi: 4=40 1=10 3=30
```

**Kode harus memuat class dengan anggota `private`**: keluaran yang benar tanpa itu hanya mendapat setengah nilai.

**Masukan.** Beberapa baris perintah, diakhiri `selesai`.

**Keluaran.** Satu baris untuk tiap `ambil` dan tiap pembuangan, lalu `Isi: …`.

**Batasan.** Paling banyak 25 perintah; 1 ≤ k ≤ 7; 10 ≤ v ≤ 99

**Konsep yang wajib ada di kode mahasiswa.** `CLASS_OBJECT`, `ENCAPSULATION`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.
