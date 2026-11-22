# Lembar tinjauan — PBO301 pertemuan 7

Sumber: `docs/soal/pbo301-pertemuan-7.json`. Status setelah impor: menunggu tinjauan (`PENDING_REVIEW`).

| Tingkat | Jumlah | Poin |
|---|---|---|
| Mudah sekali | 8 | 24 |
| Mudah | 8 | 40 |
| Mudah-sedang | 6 | 48 |
| Sedang | 6 | 60 |
| Sulit | 4 | 60 |
| **Semua** | **32** | **232** |

## Semua soal

- **Mudah sekali:** Suara Hewan (`suara-hewan`); Luas lewat Pointer Induk (`luas-lewat-pointer`); Virtual atau Biasa (`virtual-atau-biasa`); Destruktor Virtual (`destruktor-virtual`); Salam Tiga Bahasa (`salam-bahasa`); Menyajikan Minuman (`sajikan-minuman`); Tarif Parkir (`tarif-parkir`); Catatan Penting (`catatan-penting`)
- **Mudah:** Larik Bentuk (`larik-bentuk`); Irisan Objek (`irisan-objek`); Pasukan Dibubarkan (`hancur-berurutan`); Virtual di Dalam Konstruktor (`virtual-di-konstruktor`); Paket Termahal (`paket-termahal`); Mana yang Kucing (`hitung-kucing`); Menimpa Sebagian (`timpa-sebagian`); Sensor dan final (`sensor-final`)
- **Mudah-sedang:** Bentuk Terurut (`bentuk-terurut`); Salinan lewat Pointer Induk (`salin-virtual`); Kerangka Dokumen (`dokumen-template`); Kalkulator Tanpa Percabangan Panjang (`kalkulator-operasi`); Metode Pembayaran (`metode-pembayaran`); Melukis Bentuk (`gambar-bentuk`)
- **Sedang:** Perintah yang Bisa Dibatalkan (`batal-perintah`); Filter Berantai (`filter-berantai`); Pabrik Hewan (`pabrik-hewan`); Batu Gunting Kertas (`suit-batu-gunting-kertas`); Larik Objek atau Larik Pointer (`irisan-larik`); Editor Bentuk (`editor-bentuk`)
- **Sulit:** Pohon Ekspresi (`pohon-ekspresi`); Pohon Berkas (`pohon-berkas`); Papan Bidak (`papan-bidak`); Penjadwal Tugas (`penjadwal-tugas`)

## Pernyataan lengkap: dua soal per tingkat

### Mudah sekali — Suara Hewan (`suara-hewan`)

Pada pertemuan lalu, fungsi yang menerima `const Hewan &` selalu memanggil `Hewan::suara()`, apa pun objek yang dikirim. Kata **`virtual`** mengubahnya: method yang dipanggil ditentukan oleh **objek sebenarnya**, bukan oleh tipe yang tertulis.

Buat class `Hewan` dengan `virtual string suara() const` yang mengembalikan `...`. Buat turunannya `Kucing` (`meong`) dan `Anjing` (`guk`); tandai method penggantinya dengan `override`.

Tulis **satu** fungsi `void bunyikan(const string &nama, const Hewan &h)` yang mencetak `<nama>: <h.suara()>`.

Baca jenis (`kucing`, `anjing`, atau `hewan`) dan nama; buat objeknya dan kirim ke `bunyikan`.

Contoh: `kucing Oyen` → `Oyen: meong`; `hewan Koko` → `Koko: ...`.

**Kode harus memuat class turunan dan fungsi anggota `virtual`**: keluaran yang benar tanpa itu hanya mendapat setengah nilai.

**Masukan.** Satu baris berisi jenis dan nama.

**Keluaran.** Satu baris.

**Batasan.** Jenis `kucing`, `anjing`, atau `hewan`; nama satu kata.

**Konsep yang wajib ada di kode mahasiswa.** `CLASS_OBJECT`, `INHERITANCE`, `POLYMORPHISM`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah sekali — Catatan Penting (`catatan-penting`)

Method pengganti (`override`) tidak harus membuang pekerjaan induknya: ia boleh **menambah** sesuatu lalu memanggil versi induk dengan `Catatan::tulis(pesan)`.

Buat class `Catatan` dengan `virtual void tulis(const string &pesan) const` yang mencetak `Catatan: <pesan>`.

Buat turunannya `CatatanPenting` yang menimpa `tulis`: cetak dulu `PENTING!`, lalu panggil versi induk.

Baca jenis (`biasa` atau `penting`) dan satu kata pesan. Buat objeknya di balik `Catatan *` dan panggil `tulis`.

Untuk `penting rapat`:

```
PENTING!
Catatan: rapat
```

Untuk `biasa belanja` keluarannya hanya `Catatan: belanja`.

**Kode harus memuat class turunan dan fungsi anggota `virtual`**: keluaran yang benar tanpa itu hanya mendapat setengah nilai.

**Masukan.** Satu baris berisi jenis catatan dan pesannya.

**Keluaran.** Satu atau dua baris.

**Batasan.** Jenis `biasa` atau `penting`; pesan satu kata.

**Konsep yang wajib ada di kode mahasiswa.** `CLASS_OBJECT`, `INHERITANCE`, `POLYMORPHISM`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah — Larik Bentuk (`larik-bentuk`)

Kekuatan polimorfisme terlihat saat objek-objek **berlainan jenis** disimpan bersama dan diperlakukan sama. Larik `Bentuk *daftar[8]` bisa memuat persegi, persegi panjang, dan segitiga sekaligus; satu perulangan cukup untuk semuanya.

Buat class `Bentuk` dengan `virtual string nama() const` dan `virtual int luas() const`, serta destruktor virtual. Buat turunannya:

- `Persegi` (sisi) — nama `persegi`;
- `PersegiPanjang` (panjang, lebar) — nama `persegi panjang`;
- `Segitiga` (alas, tinggi) — nama `segitiga`, luas alas × tinggi / 2.

Masukan: n bentuk, satu per baris: `persegi <sisi>`, `panjang <panjang> <lebar>`, atau `segitiga <alas> <tinggi>`. Simpan semuanya di larik pointer induk. Cetak `<nama>: <luas>` untuk tiap bentuk, lalu `Total: T`. Jangan lupa `delete`.

Contoh: `persegi 4`, `panjang 2 5`, `segitiga 6 3` →

```
persegi: 16
persegi panjang: 10
segitiga: 9
Total: 35
```

**Kode harus memuat class turunan dan fungsi anggota `virtual`**: keluaran yang benar tanpa itu hanya mendapat setengah nilai.

**Masukan.** Baris pertama: n. Lalu n baris bentuk.

**Keluaran.** Satu baris per bentuk, lalu `Total: T`.

**Batasan.** 1 ≤ n ≤ 8; tiap ukuran antara 1 dan 30; alas segitiga selalu genap

**Konsep yang wajib ada di kode mahasiswa.** `CLASS_OBJECT`, `INHERITANCE`, `POLYMORPHISM`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah — Sensor dan final (`sensor-final`)

Kata `final` di belakang sebuah method virtual berarti: **turunan berikutnya tidak boleh menimpanya lagi**. Berguna bila sebuah rumus harus tetap sama di semua class di bawahnya.

Buat tiga class:

- `Sensor` — `virtual string label() const` → `sensor`; `virtual int baca(int mentah) const` → nilai mentah apa adanya;
- `SensorSuhu` turunan `Sensor` — label `suhu`; `int baca(int mentah) const final` → mentah / 10 − 40 (pembagian bilangan bulat);
- `SensorSuhuLuar` turunan `SensorSuhu` — hanya menimpa label menjadi `suhu luar`; rumus `baca` diwarisi dan tidak bisa diubah.

Masukan: n pembacaan, satu per baris: jenis (`umum`, `suhu`, `luar`) dan nilai mentah. Untuk tiap baris buat sensornya di balik `Sensor *` dan cetak `<label>: <baca(mentah)>`.

Contoh: `umum 512`, `suhu 655`, `luar 409` →

```
sensor: 512
suhu: 25
suhu luar: 0
```

**Kode harus memuat class turunan dan fungsi anggota `virtual`**: keluaran yang benar tanpa itu hanya mendapat setengah nilai.

**Masukan.** Baris pertama: n. Lalu n baris: jenis dan nilai mentah.

**Keluaran.** n baris.

**Batasan.** 1 ≤ n ≤ 8; 0 ≤ mentah ≤ 999

**Konsep yang wajib ada di kode mahasiswa.** `CLASS_OBJECT`, `INHERITANCE`, `POLYMORPHISM`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah-sedang — Bentuk Terurut (`bentuk-terurut`)

Algoritma yang hanya memakai antarmuka induk bekerja untuk semua turunan tanpa diubah. Di sini: **mengurutkan** bentuk-bentuk berlainan jenis menurut luasnya, cukup dengan membandingkan `luas()` lewat pointer induk.

Buat class `Bentuk` (`virtual string nama() const`, `virtual int luas() const`, destruktor virtual) dan turunannya `Persegi` (`persegi`), `PersegiPanjang` (`persegi panjang`), `Segitiga` (`segitiga`; luas alas × tinggi / 2).

Masukan: n bentuk — `persegi <sisi>`, `panjang <panjang> <lebar>`, atau `segitiga <alas> <tinggi>`. Simpan di larik `Bentuk *`, urutkan **pointer-nya** dari luas terkecil, dan cetak `<nama> <luas>` per baris. Bentuk yang sama luas tetap dalam urutan masukan.

Contoh: `persegi 4`, `segitiga 6 3`, `panjang 2 5` →

```
segitiga 9
persegi panjang 10
persegi 16
```

**Kode harus memuat class turunan dan fungsi anggota `virtual`**: keluaran yang benar tanpa itu hanya mendapat setengah nilai.

**Masukan.** Baris pertama: n. Lalu n baris bentuk.

**Keluaran.** n baris, dari luas terkecil.

**Batasan.** 1 ≤ n ≤ 8; tiap ukuran antara 1 dan 30; alas segitiga selalu genap

**Konsep yang wajib ada di kode mahasiswa.** `CLASS_OBJECT`, `INHERITANCE`, `POLYMORPHISM`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah-sedang — Melukis Bentuk (`gambar-bentuk`)

Buat class `Gambar` (atribut `protected` `int ukuran`) dengan:

- `virtual string nama() const` → `garis`;
- method biasa `void judul() const` yang mencetak `<nama()> <ukuran>`;
- `virtual void lukis() const` yang mencetak satu baris berisi `ukuran` tanda `#`.

Dua turunannya:

- `GambarPersegi` — nama `persegi`; `lukis()` mencetak `ukuran` baris, tiap baris `ukuran` tanda `#` (boleh dengan memanggil `Gambar::lukis()` berulang);
- `GambarSegitiga` — nama `segitiga`; `lukis()` mencetak baris berisi 1, 2, …, `ukuran` tanda `#`.

Masukan: n gambar (`garis`, `persegi`, atau `segitiga`, diikuti ukuran). Simpan di larik `Gambar *`; lalu untuk tiap gambar panggil `judul()` dan `lukis()`.

Contoh: `segitiga 3`, `garis 4` →

```
segitiga 3
#
##
###
garis 4
####
```

**Kode harus memuat class turunan dan fungsi anggota `virtual`**: keluaran yang benar tanpa itu hanya mendapat setengah nilai.

**Masukan.** Baris pertama: n. Lalu n baris: jenis dan ukuran.

**Keluaran.** Untuk tiap gambar: satu baris judul, lalu lukisannya.

**Batasan.** 1 ≤ n ≤ 5; 1 ≤ ukuran ≤ 6

**Konsep yang wajib ada di kode mahasiswa.** `CLASS_OBJECT`, `INHERITANCE`, `POLYMORPHISM`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Sedang — Perintah yang Bisa Dibatalkan (`batal-perintah`)

Tombol *undo* bekerja karena setiap perintah **disimpan sebagai objek** yang tahu cara menjalankan dan cara membatalkan dirinya.

Buat class `Perintah` dengan `virtual int jalankan(int nilai) const` dan `virtual int batalkan(int nilai) const` (keduanya mengembalikan nilai baru). Turunannya:

- `Tambah` (menyimpan x) — menjalankan: nilai + x; membatalkan: nilai − x;
- `Kali` (menyimpan x) — menjalankan: nilai × x; membatalkan: nilai / x.

Program memegang satu bilangan dan riwayat berupa **tumpukan** `Perintah *riwayat[16]`.

Masukan: nilai awal; lalu perintah:

- `tambah x` / `kali x` — buat objeknya, jalankan, simpan di riwayat, cetak nilai sekarang;
- `batal` — ambil perintah **terakhir** dari riwayat, batalkan, `delete`, cetak nilai sekarang; bila riwayat kosong cetak `Tidak ada yang dibatalkan`;
- `selesai` — cetak `Akhir: <nilai>, riwayat <banyak perintah tersisa>`.

Contoh: nilai awal 5; `tambah 3`, `kali 4`, `batal`, `batal`, `batal`, `selesai` →

```
8
32
8
5
Tidak ada yang dibatalkan
Akhir: 5, riwayat 0
```

**Kode harus memuat class turunan dan fungsi anggota `virtual`**: keluaran yang benar tanpa itu hanya mendapat setengah nilai.

**Masukan.** Baris pertama: nilai awal. Lalu perintah-perintah, diakhiri `selesai`.

**Keluaran.** Satu baris per perintah, lalu baris akhir.

**Batasan.** Paling banyak 16 perintah; −50 ≤ x ≤ 50 untuk `tambah`; 2 ≤ x ≤ 4 untuk `kali`, paling banyak 6 kali

**Konsep yang wajib ada di kode mahasiswa.** `CLASS_OBJECT`, `INHERITANCE`, `POLYMORPHISM`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Sedang — Editor Bentuk (`editor-bentuk`)

Sebuah editor gambar sederhana menyimpan bentuk-bentuknya di `Bentuk *daftar[10]`. Buat class `Bentuk` dengan tiga method virtual — `string nama() const`, `int luas() const`, `void skala(int k)` — dan turunannya `Persegi` (`persegi`) dan `PersegiPanjang` (`persegi panjang`); `skala` mengalikan setiap ukuran dengan k.

Bentuk diberi nomor 1, 2, 3, … menurut urutan ditambahkan; nomor tidak pernah dipakai ulang.

Perintah:

- `persegi <sisi>` / `panjang <panjang> <lebar>` — menambah bentuk → `Bentuk <nomor> ditambahkan`;
- `skala <nomor> <k>` — memperbesar → `Bentuk <nomor>: <nama> <luas baru>`;
- `hapus <nomor>` — `delete` bentuk itu dan mengisi tempatnya dengan `nullptr` → `Bentuk <nomor> dihapus`;
- bila nomornya belum ada atau sudah dihapus, `skala` dan `hapus` mencetak `Tidak ada bentuk <nomor>`;
- `selesai` — cetak bentuk yang masih ada sebagai `<nomor>. <nama> <luas>`, lalu `Total luas: T`.

Contoh: `persegi 2`, `panjang 2 3`, `skala 1 3`, `hapus 2`, `skala 2 2`, `selesai` →

```
Bentuk 1 ditambahkan
Bentuk 2 ditambahkan
Bentuk 1: persegi 36
Bentuk 2 dihapus
Tidak ada bentuk 2
1. persegi 36
Total luas: 36
```

**Kode harus memuat class turunan dan fungsi anggota `virtual`**: keluaran yang benar tanpa itu hanya mendapat setengah nilai.

**Masukan.** Beberapa baris perintah, diakhiri `selesai`.

**Keluaran.** Satu baris per perintah, lalu daftar dan total.

**Batasan.** Paling banyak 16 perintah dan 10 bentuk; ukuran 1–9; k adalah 2 atau 3, paling banyak 4 perintah `skala`

**Konsep yang wajib ada di kode mahasiswa.** `CLASS_OBJECT`, `INHERITANCE`, `POLYMORPHISM`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Sulit — Pohon Ekspresi (`pohon-ekspresi`)

Sebuah ekspresi hitung bisa disimpan sebagai **pohon**: daunnya angka, dan tiap simpul dalam adalah satu operasi dengan dua anak. Setiap simpul tahu cara menghitung nilainya dan cara mencetak dirinya — dan untuk itu ia cukup meminta anak-anaknya melakukan hal yang sama.

Susun hierarkinya:

- `Simpul` — `virtual int nilai() const`, `virtual void cetak() const`, destruktor virtual;
- `Angka` turunan `Simpul` — menyimpan satu bilangan;
- `Biner` turunan `Simpul` — memegang `Simpul *kiri` dan `Simpul *kanan`; destruktornya menghapus keduanya; punya `virtual char simbol() const`; `cetak()` mencetak `(<kiri> <simbol> <kanan>)`;
- `Tambah`, `Kurang`, `Kali` turunan `Biner` — menimpa `simbol()` dan `nilai()`.

Masukan berupa ekspresi dalam **notasi awalan** (operator ditulis sebelum kedua operandnya), tiap token dipisah spasi; operandnya angka satu digit. Fungsi rekursif `Simpul *baca()` membaca satu token: bila operator, ia membaca dua subpohon lalu membuat simpulnya; bila angka, ia membuat `Angka`.

Cetak ekspresi itu dalam notasi biasa dengan kurung lengkap, lalu ` = ` dan nilainya. Akhiri dengan `delete akar`.

Contoh: `+ 3 * 4 5` → `(3 + (4 * 5)) = 23`; `- 2 9` → `(2 - 9) = -7`; `7` → `7 = 7`.

**Kode harus memuat class turunan dan fungsi anggota `virtual`**: keluaran yang benar tanpa itu hanya mendapat setengah nilai.

**Masukan.** Satu baris: ekspresi dalam notasi awalan.

**Keluaran.** Satu baris: ekspresi berkurung, ` = `, dan nilainya.

**Batasan.** Operator `+`, `-`, `*`; operand 0 sampai 9; paling banyak 8 operand

**Konsep yang wajib ada di kode mahasiswa.** `CLASS_OBJECT`, `INHERITANCE`, `POLYMORPHISM`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Sulit — Penjadwal Tugas (`penjadwal-tugas`)

Sebuah penjadwal menjalankan tugas-tugas pada detik 1, 2, …, T. Penjadwal tidak tahu jenis tugasnya; pada tiap detik ia hanya bertanya kepada setiap tugas tiga hal, lewat tiga method virtual.

**`Tugas`** — atribut `protected` `string nama`; `getNama()`;
- `virtual bool waktunya(int t) const` — apakah tugas berjalan pada detik t (bawaan: selalu);
- `virtual void jalan()` — dipanggil saat tugas berjalan (bawaan: tidak berbuat apa-apa);
- `virtual bool tuntas() const` — apakah tugas sudah tidak diperlukan lagi (bawaan: tidak pernah).

Turunannya:

- `Sekali` (detik `kapan`) — berjalan hanya pada detik itu, lalu tuntas;
- `Berulang` (selang s) — berjalan pada setiap detik kelipatan s; tidak pernah tuntas;
- `Terbatas` turunan **`Berulang`** (selang s, sebanyak `kali`) — seperti `Berulang`, tetapi tuntas setelah berjalan `kali` kali.

Masukan: n dan T; lalu n tugas: `tetap <nama>` (sebuah `Tugas` biasa), `sekali <nama> <detik>`, `berulang <nama> <s>`, atau `terbatas <nama> <s> <kali>`. Simpan di `Tugas *daftar[6]`.

Untuk tiap detik t = 1 … T, periksa tugas-tugas **menurut urutan masukan**. Bila `waktunya(t)`: panggil `jalan()` dan cetak `<t>: <nama>`; bila sesudah itu `tuntas()`, cetak `<t>: <nama> selesai`, `delete` tugas itu, dan jangan periksa lagi. Di akhir cetak `Tersisa: <banyak tugas yang belum tuntas>`.

Contoh: n = 3, T = 6; `sekali cadang 2`, `berulang surel 3`, `terbatas lapor 2 2` →

```
2: cadang
2: cadang selesai
2: lapor
3: surel
4: lapor
4: lapor selesai
6: surel
Tersisa: 1
```

**Kode harus memuat class turunan dan fungsi anggota `virtual`**: keluaran yang benar tanpa itu hanya mendapat setengah nilai.

**Masukan.** Baris pertama: n dan T. Lalu n baris tugas.

**Keluaran.** Baris-baris seperti contoh.

**Batasan.** 1 ≤ n ≤ 6; 4 ≤ T ≤ 20; nama berbeda-beda; selang 1 sampai 5; `kali` 1 sampai 4

**Konsep yang wajib ada di kode mahasiswa.** `CLASS_OBJECT`, `INHERITANCE`, `POLYMORPHISM`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.
