# Lembar tinjauan — PBO301 pertemuan 6

Sumber: `docs/soal/pbo301-pertemuan-6.json`. Status setelah impor: menunggu tinjauan (`PENDING_REVIEW`).

| Tingkat | Jumlah | Poin |
|---|---|---|
| Mudah sekali | 8 | 24 |
| Mudah | 8 | 40 |
| Mudah-sedang | 6 | 48 |
| Sedang | 6 | 60 |
| Sulit | 4 | 60 |
| **Semua** | **32** | **232** |

## Semua soal

- **Mudah sekali:** Hewan dan Kucing (`hewan-kucing`); Gaji Manajer (`manajer-gaji`); Urutan Induk dan Anak (`urutan-induk-anak`); Bentuk dan Persegi (`bentuk-persegi`); Roda Kendaraan (`roda-kendaraan`); Anggota protected (`tabungan-protected`); Memanggil Method Induk (`panggil-induk`); Infanteri atau Tank (`infanteri-atau-tank`)
- **Mudah:** Tiga Tingkat (`tiga-tingkat`); Nama yang Tersembunyi (`sembunyi-nama`); Tanpa virtual (`tanpa-virtual`); Induk, Anggota, Diri Sendiri (`induk-anggota-diri`); Rekening Giro (`giro-cerukan`); Mengatur dengan Dua Cara (`atur-dua-cara`); Tiga Jenis Pegawai (`pegawai-tiga-jenis`); Dari Bidang ke Ruang (`bangun-ruang`)
- **Mudah-sedang:** Pewarisan Privat (`pewarisan-privat`); Mesin Serbaguna (`mesin-serbaguna`); Kendaraan Amfibi (`kendaraan-amfibi`); Kekuatan Berjenjang (`kekuatan-berjenjang`); Hitung per Tingkat (`hitung-per-tingkat`); Matriks Persegi (`matriks-persegi`)
- **Sedang:** Urutan Lengkap (`urutan-lengkap`); Inventaris Berjenjang (`inventaris-berjenjang`); Tiga Jenis Rekening (`tiga-rekening`); Serangan Udara (`serangan-udara`); Antrean Prioritas (`antrean-prioritas`); Duel Ksatria dan Penyihir (`duel-ksatria-penyihir`)
- **Sulit:** Amfibi Berkelana (`amfibi-berkelana`); Sistem Akademik (`sistem-akademik`); Robot Bertingkat (`robot-bertingkat`); Pustaka Media (`pustaka-media`)

## Pernyataan lengkap: dua soal per tingkat

### Mudah sekali — Hewan dan Kucing (`hewan-kucing`)

Seekor kucing **adalah** hewan. Relasi "adalah" ditulis dengan **pewarisan**: `class Kucing : public Hewan`. Class turunan memperoleh semua atribut dan method induknya, lalu menambahkan miliknya sendiri.

Buat class `Hewan` dengan atribut `protected` `string nama`, konstruktor `Hewan(const string &n)`, dan method `void makan() const` yang mencetak `<nama> makan`.

Buat class `Kucing` turunan `Hewan`. Konstruktornya meneruskan nama ke konstruktor induk — `Kucing(const string &n) : Hewan(n) { }` — dan ia menambah method `void mengeong() const` yang mencetak `<nama> mengeong`.

Baca sebuah nama, buat satu `Kucing`, panggil `makan()` (yang diwarisi) lalu `mengeong()`.

Untuk `Oyen`:

```
Oyen makan
Oyen mengeong
```

**Kode harus memuat class turunan** (`class Anak : public Induk`): keluaran yang benar tanpa pewarisan hanya mendapat setengah nilai.

**Masukan.** Satu kata: nama kucing.

**Keluaran.** Dua baris seperti contoh.

**Batasan.** Nama paling panjang 20 huruf.

**Konsep yang wajib ada di kode mahasiswa.** `CLASS_OBJECT`, `INHERITANCE`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah sekali — Infanteri atau Tank (`infanteri-atau-tank`)

Buat class `Unit` dengan atribut `protected` `int personel`, konstruktor `Unit(int p)`, dan method `int dasar() const` — kekuatan dasar sebuah satuan: personel × 2.

Buat dua turunannya, masing-masing dengan method `int kekuatan() const`:

- `Infanteri` — kekuatannya sama dengan `dasar()`;
- `Tank` — punya atribut tambahan `int lapisBaja`; kekuatannya `dasar()` ditambah lapisBaja × 10.

Bagian yang sama ditulis sekali di induk; tiap turunan hanya menulis yang berbeda.

Masukan: `infanteri <personel>`, atau `tank <awak> <lapisBaja>`. Cetak kekuatannya.

Contoh: `infanteri 30` → 60; `tank 4 12` → 128.

**Kode harus memuat class turunan** (`class Anak : public Induk`): keluaran yang benar tanpa pewarisan hanya mendapat setengah nilai.

**Masukan.** Satu baris: jenis satuan diikuti satu atau dua bilangan.

**Keluaran.** Satu bilangan bulat: kekuatan.

**Batasan.** Personel 5 sampai 200; awak 2 sampai 6; lapis baja 1 sampai 30

**Konsep yang wajib ada di kode mahasiswa.** `CLASS_OBJECT`, `INHERITANCE`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah — Tiga Tingkat (`tiga-tingkat`)

Pewarisan bisa **bertingkat**: `Infanteri` adalah `UnitDarat`, dan `UnitDarat` adalah `Unit`. Objek `Infanteri` dibangun dari tingkat paling atas ke bawah, dan dihancurkan dari bawah ke atas.

Buat tiga class — `Unit` (atribut `protected` `string nama`), `UnitDarat` turunan `Unit`, dan `Infanteri` turunan `UnitDarat`. Konstruktor tiap class mencetak `+<NamaClass> <nama>`, destruktornya `-<NamaClass> <nama>`. Tiap konstruktor meneruskan nama ke induk **langsungnya**.

Baca sebuah nama, buat satu `Infanteri`, lalu cetak `Siap tempur`.

Untuk `Elang`:

```
+Unit Elang
+UnitDarat Elang
+Infanteri Elang
Siap tempur
-Infanteri Elang
-UnitDarat Elang
-Unit Elang
```

**Kode harus memuat class turunan** (`class Anak : public Induk`): keluaran yang benar tanpa pewarisan hanya mendapat setengah nilai.

**Masukan.** Satu kata: nama satuan.

**Keluaran.** Tujuh baris seperti contoh.

**Batasan.** Nama paling panjang 20 huruf.

**Konsep yang wajib ada di kode mahasiswa.** `CLASS_OBJECT`, `INHERITANCE`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah — Dari Bidang ke Ruang (`bangun-ruang`)

Sebuah kubus adalah persegi yang "ditarik ke atas"; sebuah balok adalah persegi panjang yang ditarik ke atas. Volume keduanya adalah **luas alas** dikali tinggi — dan luas alas sudah bisa dihitung oleh induknya.

Buat class `Persegi` (atribut `protected` `int sisi`; `int luas() const`) dan turunannya `Kubus` dengan `int volume() const` = `luas()` × sisi.

Buat class `PersegiPanjang` (atribut `protected` `int panjang`, `int lebar`; `int luas() const`) dan turunannya `Balok` dengan atribut `int tinggi` dan `int volume() const` = `luas()` × tinggi.

Masukan: n bangun, satu per baris: `kubus <sisi>` atau `balok <panjang> <lebar> <tinggi>`. Cetak volume tiap bangun, lalu `Total: T`.

Contoh: `kubus 3`, `balok 2 3 4` →

```
27
24
Total: 51
```

**Kode harus memuat class turunan** (`class Anak : public Induk`): keluaran yang benar tanpa pewarisan hanya mendapat setengah nilai.

**Masukan.** Baris pertama: n. Lalu n baris bangun.

**Keluaran.** Satu baris per bangun, lalu `Total: T`.

**Batasan.** 1 ≤ n ≤ 8; tiap ukuran antara 1 dan 30

**Konsep yang wajib ada di kode mahasiswa.** `CLASS_OBJECT`, `INHERITANCE`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah-sedang — Pewarisan Privat (`pewarisan-privat`)

Pewarisan `public` berkata "turunan *adalah* induk". Pewarisan **`private`** berkata lain: "turunan *dibuat dengan memakai* induk" — semua anggota induk menjadi urusan dalam turunan, dan kode luar hanya melihat apa yang sengaja dibuka.

Buat class `DaftarBilangan` yang serba bisa: atribut `protected` `int data[20]`, `int banyak`; method `tambahBelakang(int)`, `int ambilDepan()`, `int ambilBelakang()`, `bool kosong() const`, `int ukuran() const`.

Dari class itu turunkan **secara privat** dua class yang masing-masing hanya membuka sebagian kemampuan:

- `Antrean` — `void masuk(int)` (ke belakang) dan `int keluar()` (dari depan);
- `Tumpukan` — `void dorong(int)` (ke belakang) dan `int ambil()` (dari belakang).

Keduanya membuka `kosong` dan `ukuran` milik induk dengan `using DaftarBilangan::kosong;` dan seterusnya. Sebuah `Antrean` tidak punya jalan untuk diambil dari belakang.

Program memakai satu antrean dan satu tumpukan. Perintah: `antre x`, `layani` (cetak elemen terdepan antrean, atau `antrean kosong`), `tumpuk x`, `angkat` (cetak elemen teratas tumpukan, atau `tumpukan kosong`), `selesai`. Di akhir cetak `Antrean: <ukuran>` dan `Tumpukan: <ukuran>`.

Contoh: `antre 1`, `antre 2`, `tumpuk 1`, `tumpuk 2`, `layani`, `angkat`, `selesai` →

```
1
2
Antrean: 1
Tumpukan: 1
```

**Kode harus memuat class turunan** (`class Anak : public Induk`): keluaran yang benar tanpa pewarisan hanya mendapat setengah nilai.

**Masukan.** Beberapa baris perintah, diakhiri `selesai`.

**Keluaran.** Satu baris untuk tiap `layani` dan `angkat`, lalu dua baris ukuran.

**Batasan.** Paling banyak 20 perintah; 1 ≤ x ≤ 99

**Konsep yang wajib ada di kode mahasiswa.** `CLASS_OBJECT`, `INHERITANCE`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah-sedang — Matriks Persegi (`matriks-persegi`)

Matriks persegi adalah matriks yang banyak baris dan kolomnya sama. Ia bisa melakukan semua yang bisa dilakukan matriks biasa, **ditambah** beberapa hal yang hanya masuk akal untuk matriks persegi.

Buat class `Matriks` (atribut `protected` `int sel[5][5]`, `int baris`, `int kolom`; konstruktor `Matriks(int b, int k)`; `void baca()`; `int jumlah() const` — jumlah semua elemen).

Buat class `MatriksPersegi` turunan `Matriks`, dengan konstruktor `MatriksPersegi(int n) : Matriks(n, n) { }` dan dua method tambahan:

- `int jejak() const` — jumlah elemen diagonal utama;
- `bool simetris() const` — benar bila elemen baris i kolom j selalu sama dengan elemen baris j kolom i.

Baca n dan matriks n × n. Cetak `Jumlah: …`, `Jejak: …`, lalu `simetris` atau `tidak simetris`.

Contoh: matriks `1 2` / `2 5` →

```
Jumlah: 10
Jejak: 6
simetris
```

**Kode harus memuat class turunan** (`class Anak : public Induk`): keluaran yang benar tanpa pewarisan hanya mendapat setengah nilai.

**Masukan.** Baris pertama: n. Lalu n baris berisi n bilangan bulat.

**Keluaran.** Tiga baris seperti contoh.

**Batasan.** 1 ≤ n ≤ 5; tiap elemen antara −9 dan 9

**Konsep yang wajib ada di kode mahasiswa.** `CLASS_OBJECT`, `INHERITANCE`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Sedang — Urutan Lengkap (`urutan-lengkap`)

Aturan lengkap urutan pembangunan sebuah objek: (1) **semua induk**, menurut urutan penulisannya di daftar induk; (2) **semua objek anggota**, menurut urutan deklarasinya di dalam class; (3) **badan konstruktor** class itu sendiri. Urutan di daftar inisialisasi tidak berpengaruh. Penghancuran persis terbalik.

Buat lima class yang konstruktor dan destruktornya mencetak `+…` / `-…`:

- `Kendaraan` — `+Kendaraan`;
- `Terdaftar` (atribut `string nomor`) — `+Terdaftar <nomor>`;
- `Mesin` (atribut `int tenaga`) — `+Mesin <tenaga>`;
- `Radio` (atribut `int frekuensi`) — `+Radio <frekuensi>`;
- `Mobil` — mewarisi `Kendaraan` lalu `Terdaftar` (dalam urutan itu), dan punya anggota, dalam urutan ini: `string nama`, `Mesin mesin`, `Radio radio` — `+Mobil <nama>`.

Baca nama, nomor polisi, tenaga, dan frekuensi. Buat satu `Mobil`, lalu cetak `Mobil siap`.

Untuk `Kijang D5678AB 100 99`:

```
+Kendaraan
+Terdaftar D5678AB
+Mesin 100
+Radio 99
+Mobil Kijang
Mobil siap
-Mobil Kijang
-Radio 99
-Mesin 100
-Terdaftar D5678AB
-Kendaraan
```

**Kode harus memuat class turunan** (`class Anak : public Induk`): keluaran yang benar tanpa pewarisan hanya mendapat setengah nilai.

**Masukan.** Satu baris berisi nama, nomor polisi, tenaga, dan frekuensi.

**Keluaran.** Sebelas baris seperti contoh.

**Batasan.** Nama dan nomor polisi satu kata; 60 ≤ tenaga ≤ 300; 88 ≤ frekuensi ≤ 107

**Konsep yang wajib ada di kode mahasiswa.** `CLASS_OBJECT`, `INHERITANCE`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Sedang — Duel Ksatria dan Penyihir (`duel-ksatria-penyihir`)

Buat class `Karakter` (atribut `protected` `string nama`, `int hp`; `getNama()`, `getHp()`, `bool hidup() const`; dan `void terima(int luka)` — hp berkurang, tetapi tidak di bawah 0). Dua turunannya bertarung:

- `Ksatria` (tambahan `int zirah`): `int serang() const` selalu 15. Ia **mengganti `terima`**: luka dikurangi zirah lebih dulu, tetapi tidak pernah kurang dari 1; sisanya diserahkan ke `Karakter::terima`;
- `Penyihir` (tambahan `int mana`): `int serang()` — bila mana 10 atau lebih, mana berkurang 10 dan serangannya 30; bila tidak, serangannya 5. `terima`-nya warisan apa adanya.

Jalannya duel, paling banyak **6 ronde**: tiap ronde ksatria menyerang lebih dulu; bila penyihir masih hidup, ia membalas. Setiap serangan dicetak sebagai `<penyerang> menyerang: <nama lawan> <hp lawan sesudahnya>`. Duel berhenti begitu ada yang hp-nya 0.

Di akhir cetak `<nama> menang`, atau `Seri` bila setelah 6 ronde keduanya masih hidup.

Masukan: baris pertama nama, hp, dan zirah ksatria; baris kedua nama, hp, dan mana penyihir.

Contoh: `Andi 50 5` dan `Budi 40 10` →

```
Andi menyerang: Budi 25
Budi menyerang: Andi 25
Andi menyerang: Budi 10
Budi menyerang: Andi 24
Andi menyerang: Budi 0
Andi menang
```

**Kode harus memuat class turunan** (`class Anak : public Induk`): keluaran yang benar tanpa pewarisan hanya mendapat setengah nilai.

**Masukan.** Dua baris: data ksatria, lalu data penyihir.

**Keluaran.** Satu baris per serangan, lalu hasil duel.

**Batasan.** 40 ≤ hp ≤ 150; 0 ≤ zirah ≤ 12; 0 ≤ mana ≤ 60

**Konsep yang wajib ada di kode mahasiswa.** `CLASS_OBJECT`, `INHERITANCE`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Sulit — Amfibi Berkelana (`amfibi-berkelana`)

Buat hierarki berlian dengan **pewarisan virtual**, lengkap dengan data yang dipakai bersama.

- `Kendaraan` — atribut `protected` `string nama`, `int bensin`; `bool pakai(int liter)` (gagal bila bensin kurang); `int getBensin() const`;
- `Darat : virtual public Kendaraan` — `int kmDarat`; `bool jalan(int km)` memakai **2 liter per km**;
- `Air : virtual public Kendaraan` — `int kmAir`; `bool berlayar(int km)` memakai **5 liter per km**;
- `Amfibi : public Darat, public Air` — atribut `bool diAir` (mula-mula di darat); `void ganti()`, `void maju(int km)`, `void laporan() const`.

Karena `Kendaraan` diwarisi secara virtual, `jalan` dan `berlayar` menguras **tangki yang sama**. Konstruktor `Amfibi` harus menyebut `Kendaraan(n, b)` sendiri.

Konstruktor tiap class mencetak `+Kendaraan <nama>`, `+Darat`, `+Air`, `+Amfibi`; destruktornya `-…` yang sepadan.

Perintah:

- `ganti` — berpindah mode dan mencetak `Mode air` atau `Mode darat`;
- `maju <km>` — bergerak di mode saat ini: bila bensin cukup cetak `Maju <km> km, bensin <sisa>`; bila tidak, cetak `Bensin tidak cukup` dan tidak bergerak;
- `selesai` — cetak `Darat <x> km, air <y> km`.

Contoh: `Hiu 50`; `maju 10`, `ganti`, `maju 10`, `maju 5`, `selesai` →

```
+Kendaraan Hiu
+Darat
+Air
+Amfibi
Maju 10 km, bensin 30
Mode air
Bensin tidak cukup
Maju 5 km, bensin 5
Darat 10 km, air 5 km
-Amfibi
-Air
-Darat
-Kendaraan Hiu
```

**Kode harus memuat class turunan** (`class Anak : public Induk`): keluaran yang benar tanpa pewarisan hanya mendapat setengah nilai.

**Masukan.** Baris pertama: nama dan bensin awal. Lalu perintah-perintah, diakhiri `selesai`.

**Keluaran.** Baris-baris seperti contoh.

**Batasan.** 20 ≤ bensin ≤ 200; paling banyak 15 perintah; 1 ≤ km ≤ 25

**Konsep yang wajib ada di kode mahasiswa.** `CLASS_OBJECT`, `INHERITANCE`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Sulit — Pustaka Media (`pustaka-media`)

Sebuah pustaka pribadi menyimpan buku, film, dan film seri. Susun hierarkinya:

- `Media` — atribut `protected` `string judul`, `int tahun`; `void cetak() const` mencetak `<judul> (<tahun>)` tanpa pindah baris;
- `Buku` turunan `Media` — `int halaman`; `cetak()` menambah `, <h> halaman`; `int menit() const` = halaman × 2 (perkiraan waktu baca);
- `Film` turunan `Media` — `int durasi`; `cetak()` menambah `, <d> menit`; `menit()` = durasi;
- `FilmSeri` turunan **`Film`** — `int episode`; `cetak()` menambah ` x <e> episode` di belakang cetakan film; `menit()` = `Film::menit()` × episode.

Perintah:

- `buku <judul> <tahun> <halaman>`;
- `film <judul> <tahun> <durasi>`;
- `seri <judul> <tahun> <durasi per episode> <episode>`;
- `hapus <judul>` → `Dihapus: <judul>`, atau `Tidak ada: <judul>`;
- `selesai`.

Di akhir cetak tiga bagian — `Buku:`, `Film:`, `Seri:` — masing-masing diikuti isinya yang masih ada, satu per baris, menurut urutan ditambahkan. Lalu cetak `Total: <j> jam <m> menit`: jumlah `menit()` semua media yang masih ada.

Contoh: `buku Arus 2001 150`, `seri Kabut 2020 45 8`, `film Bumi 1999 120`, `hapus Bumi`, `hapus Bumi`, `selesai` →

```
Dihapus: Bumi
Tidak ada: Bumi
Buku:
Arus (2001), 150 halaman
Film:
Seri:
Kabut (2020), 45 menit x 8 episode
Total: 11 jam 0 menit
```

**Kode harus memuat class turunan** (`class Anak : public Induk`): keluaran yang benar tanpa pewarisan hanya mendapat setengah nilai.

**Masukan.** Beberapa baris perintah, diakhiri `selesai`.

**Keluaran.** Satu baris per perintah `hapus`, lalu tiga bagian daftar dan satu baris total.

**Batasan.** Paling banyak 16 perintah; semua judul berbeda; halaman 50–800; durasi 40–180; episode 2–24

**Konsep yang wajib ada di kode mahasiswa.** `CLASS_OBJECT`, `INHERITANCE`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.
