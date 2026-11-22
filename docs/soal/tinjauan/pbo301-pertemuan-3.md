# Lembar tinjauan — PBO301 pertemuan 3

Sumber: `docs/soal/pbo301-pertemuan-3.json`. Status setelah impor: menunggu tinjauan (`PENDING_REVIEW`).

| Tingkat | Jumlah | Poin |
|---|---|---|
| Mudah sekali | 8 | 24 |
| Mudah | 8 | 40 |
| Mudah-sedang | 6 | 48 |
| Sedang | 6 | 60 |
| Sulit | 4 | 60 |
| **Semua** | **32** | **232** |

## Semua soal

- **Mudah sekali:** Mulai dan Selesai (`mulai-selesai`); Titik Asal (`titik-asal`); Urutan Dua Objek (`urutan-dua-objek`); Nomor Seri (`nomor-seri`); Blok dan Cakupan (`blok-cakupan`); Objek Dinamis (`objek-dinamis`); Konstruktor Salin (`salin-dengan-pesan`); Daftar Inisialisasi (`waktu-bawaan`)
- **Mudah:** Barisan Prajurit (`barisan-prajurit`); Parameter by Value (`parameter-nilai`); Berapa Kali Disalin? (`hitung-salinan`); Konstruktor yang Mendelegasikan (`konstruktor-delegasi`); Objek di Dalam Perulangan (`objek-dalam-perulangan`); Salinan Dalam (`salin-dalam`); Operator Penugasan (`penugasan-salin`); Anggota const (`rekening-const`)
- **Mudah-sedang:** Tumpukan Peti (`tumpukan-objek`); Aturan Tiga (`aturan-tiga-teks`); Penugasan Berantai (`penugasan-berantai`); Kunci yang Selalu Dilepas (`kunci-otomatis`); Objek Sementara (`objek-sementara`); Daftar yang Tumbuh (`daftar-tumbuh`)
- **Sedang:** Transpos Matriks (`matriks-transpos`); Pelacak Kebocoran (`pelacak-bocor`); Tumpukan dengan Cadangan (`tumpukan-cadangan`); Global, Statis, Lokal (`objek-statis`); Harga Sebuah Pengurutan (`hitung-salinan-urut`); Menggabung Teks (`teks-gabung`)
- **Sulit:** Simulasi Cakupan (`simulasi-cakupan`); Batal dan Ulang (`batal-ulang`); Pangkat Besar (`pangkat-besar`); Pelacak Objek (`pelacak-objek`)

## Pernyataan lengkap: dua soal per tingkat

### Mudah sekali — Mulai dan Selesai (`mulai-selesai`)

**Konstruktor** adalah method yang berjalan sendiri saat sebuah objek dibuat; namanya sama dengan nama class dan ia tidak punya tipe kembalian. **Destruktor** (`~NamaClass()`) berjalan sendiri saat objek itu berakhir.

Buat class `Tugas` dengan atribut `string nama`:

- konstruktor `Tugas(string n)` mengisi `nama` dan mencetak `Mulai <nama>`;
- destruktor `~Tugas()` mencetak `Selesai <nama>`.

Di `main`, baca sebuah nama, buat satu objek `Tugas`, lalu cetak `Mengerjakan <nama>`. Baris terakhir keluaran dicetak oleh destruktor saat `main` berakhir — tidak ada yang memanggilnya.

Untuk `laporan`:

```
Mulai laporan
Mengerjakan laporan
Selesai laporan
```

**Kode harus memuat class dengan konstruktor atau destruktor**: keluaran yang benar tanpa itu hanya mendapat setengah nilai.

**Masukan.** Satu kata: nama tugas.

**Keluaran.** Tiga baris seperti contoh.

**Batasan.** Nama terdiri dari huruf kecil, paling panjang 20 huruf.

**Konsep yang wajib ada di kode mahasiswa.** `CLASS_OBJECT`, `CONSTRUCTOR_DESTRUCTOR`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah sekali — Daftar Inisialisasi (`waktu-bawaan`)

Konstruktor sebaiknya mengisi atribut lewat **daftar inisialisasi** — bagian setelah titik dua, sebelum badan konstruktor:

```
Waktu(int j, int m = 0) : jam(j), menit(m) { }
```

Dengan cara itu atribut langsung lahir dengan nilai yang benar, bukan dibuat dulu lalu diisi. Parameter konstruktor juga boleh punya nilai bawaan.

Buat class `Waktu` dengan atribut `int jam`, `int menit`, konstruktor seperti di atas, dan method `void cetak()` yang mencetak `JJ:MM` (dua digit).

Masukan berisi k lalu k bilangan: jam saja, atau jam dan menit. Buat objeknya dengan tepat k argumen dan cetak.

Contoh: `1` lalu `7` → `07:00`; `2` lalu `7 5` → `07:05`.

**Kode harus memuat class dengan konstruktor atau destruktor**: keluaran yang benar tanpa itu hanya mendapat setengah nilai.

**Masukan.** Baris pertama: k (1 atau 2). Baris kedua: k bilangan bulat.

**Keluaran.** Waktu dalam bentuk `JJ:MM`.

**Batasan.** 0 ≤ jam ≤ 23; 0 ≤ menit ≤ 59

**Konsep yang wajib ada di kode mahasiswa.** `CLASS_OBJECT`, `CONSTRUCTOR_DESTRUCTOR`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah — Barisan Prajurit (`barisan-prajurit`)

`new Prajurit[n]` membuat n objek sekaligus: konstruktor bawaan dipanggil untuk tiap elemen, **dari indeks 0 ke atas**. `delete[]` menghancurkan semuanya **dalam urutan terbalik**.

Buat class `Prajurit` dengan `static int banyak` (mula-mula 0) dan `int nomor`. Konstruktornya menaikkan `banyak`, mengisi `nomor`, dan mencetak `Prajurit <nomor> siap`. Destruktornya mencetak `Prajurit <nomor> bubar`.

Baca n, buat regu berisi n prajurit dengan `new[]`, cetak `Regu lengkap`, lalu bubarkan dengan `delete[]`.

Untuk n = 2:

```
Prajurit 1 siap
Prajurit 2 siap
Regu lengkap
Prajurit 2 bubar
Prajurit 1 bubar
```

**Kode harus memuat class dengan konstruktor atau destruktor**: keluaran yang benar tanpa itu hanya mendapat setengah nilai.

**Masukan.** Satu bilangan bulat n.

**Keluaran.** 2n + 1 baris seperti contoh.

**Batasan.** 1 ≤ n ≤ 10

**Konsep yang wajib ada di kode mahasiswa.** `CLASS_OBJECT`, `CONSTRUCTOR_DESTRUCTOR`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah — Anggota const (`rekening-const`)

Atribut yang nilainya tidak boleh berubah seumur hidup objek ditandai `const`. Atribut seperti itu **tidak bisa diisi dengan `=` di dalam badan konstruktor**; satu-satunya tempat mengisinya adalah daftar inisialisasi.

Buat class `Rekening` dengan atribut `const int nomor` dan `int saldo`, konstruktor `Rekening(int n, int s) : nomor(n), saldo(s) { }`, dan method `void beriBunga(int persen)` yang menambah saldo sebesar persen dari saldo.

Baca n rekening dan persen bunganya. Untuk tiap rekening buat sebuah objek, beri bunga, lalu cetak `<nomor>: <saldo>`.

Contoh: bunga 5 persen; rekening `1234 200000` → `1234: 210000`.

**Kode harus memuat class dengan konstruktor atau destruktor**: keluaran yang benar tanpa itu hanya mendapat setengah nilai.

**Masukan.** Baris pertama: n dan persen bunga. Lalu n baris, masing-masing berisi nomor rekening dan saldo.

**Keluaran.** n baris: nomor rekening dan saldo setelah bunga.

**Batasan.** 1 ≤ n ≤ 10; 1 ≤ persen ≤ 10; saldo kelipatan 1000 antara 0 dan 500000

**Konsep yang wajib ada di kode mahasiswa.** `CLASS_OBJECT`, `CONSTRUCTOR_DESTRUCTOR`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah-sedang — Tumpukan Peti (`tumpukan-objek`)

Peti-peti ditumpuk di gudang. Tiap peti yang dibuat mendapat nomor urut berikutnya (1, 2, 3, …); nomor yang sudah dipakai tidak pernah dipakai lagi.

Buat class `Peti` dengan `static int dibuat` dan `int nomor`; konstruktornya memberi nomor dan mencetak `Buat <nomor>`, destruktornya mencetak `Hapus <nomor>`.

Masukan berupa deretan perintah:

- `B` — buat peti baru dengan `new` dan taruh di puncak tumpukan;
- `H` — hancurkan peti di puncak dengan `delete`; bila tumpukan kosong, cetak `Kosong`.

Setelah semua perintah, cetak `Akhir perintah`, lalu hancurkan peti yang tersisa **dari puncak ke dasar**. Tidak boleh ada peti yang bocor.

Untuk `BBHBHHH`:

```
Buat 1
Buat 2
Hapus 2
Buat 3
Hapus 3
Hapus 1
Kosong
Akhir perintah
```

**Kode harus memuat class dengan konstruktor atau destruktor**: keluaran yang benar tanpa itu hanya mendapat setengah nilai.

**Masukan.** Satu baris: deretan huruf `B` dan `H` tanpa spasi.

**Keluaran.** Satu baris per kejadian, seperti contoh.

**Batasan.** Panjang deretan 1 sampai 30.

**Konsep yang wajib ada di kode mahasiswa.** `CLASS_OBJECT`, `CONSTRUCTOR_DESTRUCTOR`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah-sedang — Daftar yang Tumbuh (`daftar-tumbuh`)

Begitulah `std::vector` bekerja di balik layar. Buat class `DaftarDinamis` dengan atribut `int *data`, `int banyak`, `int kapasitas`:

- konstruktor bawaan memesan array berkapasitas 1;
- `void tambah(int x)` — bila array penuh, pesan array baru **dua kali lebih besar**, salin isinya, bebaskan yang lama, dan cetak `Tumbuh <lama> -> <baru>`; lalu simpan x;
- `void cetak()` — mencetak `Isi:` diikuti semua elemen;
- destruktor mencetak `Bebas <kapasitas>` dan membebaskan array.

Baca n bilangan, tambahkan satu per satu, lalu panggil `cetak()`. Baris `Bebas` muncul paling akhir, saat objek dihancurkan.

Untuk `5 6 7`:

```
Tumbuh 1 -> 2
Tumbuh 2 -> 4
Isi: 5 6 7
Bebas 4
```

**Kode harus memuat class dengan konstruktor atau destruktor**: keluaran yang benar tanpa itu hanya mendapat setengah nilai.

**Masukan.** Baris pertama: n. Baris kedua: n bilangan bulat.

**Keluaran.** Baris `Tumbuh` untuk tiap pembesaran, lalu `Isi: …`, lalu `Bebas k`.

**Batasan.** 1 ≤ n ≤ 40; tiap bilangan antara 0 dan 99

**Konsep yang wajib ada di kode mahasiswa.** `CLASS_OBJECT`, `CONSTRUCTOR_DESTRUCTOR`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Sedang — Transpos Matriks (`matriks-transpos`)

Buat class `Matriks` yang mengelola matriks berukuran sembarang di memori dinamis: atribut `int **sel`, `int baris`, `int kolom`. Class ini mengelola memori, jadi berlaku aturan tiga:

- konstruktor `Matriks(int b, int k)` — memesan b baris berisi k kolom, semua bernilai 0;
- konstruktor salin dan operator penugasan yang menyalin dalam;
- destruktor yang membebaskan **setiap baris** lalu array penunjuknya;
- `Matriks transpos() const` — mengembalikan matriks baru berukuran k × b, dengan baris dan kolom ditukar;
- `void cetak() const`.

Baca sebuah matriks `m`. Lalu jalankan:

```
Matriks cadangan = m;
m = m.transpos();
m.cetak();
cadangan.sel[0][0] += 1000;
```

dan cetak `Sudut: <m.sel[0][0]> <cadangan.sel[0][0]>`.

Untuk matriks 2 × 3 `1 2 3` / `4 5 6`:

```
1 4
2 5
3 6
Sudut: 1 1001
```

**Kode harus memuat class dengan konstruktor atau destruktor, serta konstruktor salin atau operator penugasan**: keluaran yang benar tanpa itu hanya mendapat setengah nilai.

**Masukan.** Baris pertama: b dan k. Lalu b baris berisi k bilangan.

**Keluaran.** k baris transpos, lalu satu baris `Sudut: …`.

**Batasan.** 1 ≤ b, k ≤ 6; tiap bilangan antara 0 dan 99

**Konsep yang wajib ada di kode mahasiswa.** `CLASS_OBJECT`, `CONSTRUCTOR_DESTRUCTOR`, `COPY_SEMANTICS`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Sedang — Menggabung Teks (`teks-gabung`)

Lanjutkan class `Teks` (atribut `char *huruf`, `int panjang`; konstruktor dari `string`, konstruktor salin, operator penugasan, destruktor) dengan method:

- `Teks gabung(const Teks &lain) const` — mengembalikan objek `Teks` **baru** berisi teks ini disambung teks `lain`; kedua objek asal tidak berubah.

Baca n kata. Gabungkan semuanya menjadi satu `Teks`, dengan tanda hubung di antara kata, lewat pernyataan berbentuk `hasil = hasil.gabung(…);`. Setiap pernyataan itu membuat objek baru, menugaskannya ke `hasil` (memori lama `hasil` harus dibebaskan), lalu menghancurkan objek sementaranya.

Cetak teks hasil, lalu `Panjang: p`.

Untuk `meja kursi lampu`:

```
meja-kursi-lampu
Panjang: 16
```

**Kode harus memuat class dengan konstruktor atau destruktor, serta konstruktor salin atau operator penugasan**: keluaran yang benar tanpa itu hanya mendapat setengah nilai.

**Masukan.** Baris pertama: n. Baris kedua: n kata dari huruf kecil.

**Keluaran.** Dua baris: teks gabungan dan panjangnya.

**Batasan.** 1 ≤ n ≤ 8; tiap kata paling panjang 10 huruf

**Konsep yang wajib ada di kode mahasiswa.** `CLASS_OBJECT`, `CONSTRUCTOR_DESTRUCTOR`, `COPY_SEMANTICS`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Sulit — Simulasi Cakupan (`simulasi-cakupan`)

Masukan adalah "program" mini yang ditulis dengan tiga macam karakter:

- `{` membuka sebuah blok;
- `}` menutup blok;
- sebuah huruf kecil berarti "buat objek bernama huruf itu di blok yang sedang terbuka".

Cetak apa yang akan dicetak program C++ yang sebenarnya: `Buat <huruf>` saat objek dibuat, dan `Hapus <huruf>` saat ia dihancurkan — yaitu ketika bloknya ditutup, dalam urutan terbalik dari pembuatan di blok itu.

Untuk `{a{bc}d}`:

```
Buat a
Buat b
Buat c
Hapus c
Hapus b
Buat d
Hapus d
Hapus a
```

**Biarkan C++ yang mengerjakannya.** Buat class `Benda` yang konstruktor dan destruktornya mencetak kedua baris itu, lalu tulis fungsi rekursif yang membaca kode karakter demi karakter: bila bertemu huruf, ia membuat objek `Benda` **lokal** lalu memanggil dirinya untuk sisa blok — sehingga objek itu baru hancur setelah sisa bloknya selesai diproses. Tidak perlu array, tumpukan buatan, atau `new`.

**Kode harus memuat class dengan konstruktor atau destruktor**: keluaran yang benar tanpa itu hanya mendapat setengah nilai.

**Masukan.** Satu baris: kode yang diawali `{` dan diakhiri `}` pasangannya, dengan semua kurung berpasangan.

**Keluaran.** Satu baris per pembuatan dan penghancuran objek.

**Batasan.** Panjang kode 3 sampai 40 karakter; kedalaman blok paling banyak 5; paling sedikit satu objek dibuat.

**Konsep yang wajib ada di kode mahasiswa.** `CLASS_OBJECT`, `CONSTRUCTOR_DESTRUCTOR`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Sulit — Pelacak Objek (`pelacak-objek`)

Buat class `Pelacak` yang melaporkan setiap peristiwa dalam hidupnya. Atribut: `static int dibuat`, `int id`, `int nilai`. Setiap objek baru — juga hasil penyalinan — mendapat `id` berikutnya (1, 2, 3, …).

- konstruktor `Pelacak(int v)` mencetak `+<id>`;
- konstruktor salin mencetak `+<id> salin <id asal>` dan menyalin `nilai`;
- operator penugasan menyalin `nilai` saja (id tetap) dan mencetak `<id> = <id asal>`;
- destruktor mencetak `-<id>`.

Objek-objek disimpan berurutan dalam sebuah daftar (array penunjuk); **posisi** dihitung mulai dari 1. Perintah:

- `buat v` — objek baru dengan `new`, di akhir daftar;
- `salin k` — objek baru hasil **menyalin** objek di posisi k, di akhir daftar;
- `tugas a b` — objek di posisi a `=` objek di posisi b;
- `hapus` — hancurkan objek terakhir di daftar; bila daftar kosong, cetak `kosong`;
- `cetak` — cetak `Isi:` diikuti `<id>:<nilai>` tiap objek;
- `selesai` — hancurkan semua yang tersisa, dari yang terakhir ke yang pertama.

Contoh: `buat 5`, `buat 8`, `salin 1`, `tugas 2 3`, `cetak`, `hapus`, `selesai` →

```
+1
+2
+3 salin 1
2 = 3
Isi: 1:5 2:5 3:5
-3
-2
-1
```

**Kode harus memuat class dengan konstruktor atau destruktor, serta konstruktor salin atau operator penugasan**: keluaran yang benar tanpa itu hanya mendapat setengah nilai.

**Masukan.** Beberapa baris perintah, diakhiri `selesai`.

**Keluaran.** Satu baris per peristiwa, seperti contoh.

**Batasan.** Paling banyak 23 perintah; paling banyak 10 objek hidup; posisi yang disebut selalu ada; 1 ≤ v ≤ 99

**Konsep yang wajib ada di kode mahasiswa.** `CLASS_OBJECT`, `CONSTRUCTOR_DESTRUCTOR`, `COPY_SEMANTICS`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.
