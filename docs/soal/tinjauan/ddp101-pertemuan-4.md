# Lembar tinjauan — DDP101 pertemuan 4

Sumber: `docs/soal/ddp101-pertemuan-4.json`. Status setelah impor: menunggu tinjauan (`PENDING_REVIEW`).

| Tingkat | Jumlah | Poin |
|---|---|---|
| Mudah sekali | 8 | 24 |
| Mudah | 8 | 40 |
| Mudah-sedang | 6 | 48 |
| Sedang | 6 | 60 |
| Sulit | 4 | 60 |
| **Semua** | **32** | **232** |

## Semua soal

- **Mudah sekali:** Positif, Negatif, atau Nol (`tanda-bilangan`); Genap atau Ganjil (`genap-atau-ganjil`); Yang Lebih Besar (`lebih-besar`); Lulus atau Tidak (`lulus-atau-tidak`); Nilai Mutlak (`nilai-mutlak`); Ketiganya Sama? (`tiga-sama`); Habis Dibagi (`habis-dibagi`); Diskon Belanja (`diskon-belanja`)
- **Mudah:** Terbesar dari Tiga (`terbesar-dari-tiga`); Wujud Air (`wujud-air`); Nilai Huruf (`nilai-huruf`); Nama Hari (`nama-hari`); Tahun Kabisat (`tahun-kabisat`); Vokal atau Konsonan (`vokal-konsonan`); Kuadran (`kuadran`); Jenis Karakter (`jenis-karakter`)
- **Mudah-sedang:** Kalkulator dengan Operator (`kalkulator-operator`); Jenis Segitiga (`jenis-segitiga`); Banyak Hari dalam Sebulan (`hari-dalam-bulan`); Urutkan Tiga Bilangan (`urutkan-tiga`); Indeks Massa Tubuh (`kategori-imt`); Jam Dua Belas (`jam-dua-belas`)
- **Sedang:** Pajak Penghasilan (`pajak-penghasilan`); Titik dan Persegi Panjang (`titik-dan-persegi-panjang`); Jadwal Bentrok (`jadwal-bentrok`); Batu Gunting Kertas (`batu-gunting-kertas`); Ongkos Kirim (`ongkos-kirim`); Tarif Parkir (`tarif-parkir`)
- **Sulit:** Terbilang (`terbilang`); Angka Romawi (`angka-romawi`); Selisih Hari (`selisih-hari`); Tiga Dadu (`tiga-dadu`)

## Pernyataan lengkap: dua soal per tingkat

### Mudah sekali — Positif, Negatif, atau Nol (`tanda-bilangan`)

Baca sebuah bilangan bulat, lalu cetak `positif`, `negatif`, atau `nol` sesuai nilainya.

**Masukan.** Satu bilangan bulat n.

**Keluaran.** Satu kata: `positif`, `negatif`, atau `nol`.

**Batasan.** −1000 ≤ n ≤ 1000

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah sekali — Diskon Belanja (`diskon-belanja`)

Sebuah toko memberi **diskon 10 persen** untuk belanja **100000 rupiah atau lebih**. Belanja di bawah itu tidak mendapat diskon.

Baca total belanja, lalu cetak jumlah yang harus dibayar.

Contoh: belanja 150000 dibayar 135000; belanja 80000 tetap dibayar 80000.

**Masukan.** Satu bilangan bulat: total belanja dalam rupiah.

**Keluaran.** Satu bilangan bulat: jumlah yang harus dibayar.

**Batasan.** 1000 ≤ total ≤ 300000, selalu kelipatan 1000.

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah — Terbesar dari Tiga (`terbesar-dari-tiga`)

Baca tiga bilangan bulat, lalu cetak yang terbesar. Beberapa bilangan boleh bernilai sama.

Untuk `4 9 -2` jawabannya 9.

**Masukan.** Satu baris berisi tiga bilangan bulat.

**Keluaran.** Satu bilangan: yang terbesar.

**Batasan.** Tiap bilangan antara −1000 dan 1000.

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah — Jenis Karakter (`jenis-karakter`)

Baca satu karakter, lalu cetak jenisnya:

- `huruf besar` untuk `A` sampai `Z`;
- `huruf kecil` untuk `a` sampai `z`;
- `angka` untuk `0` sampai `9`;
- `simbol` untuk karakter lainnya.

Karakter bisa dibandingkan seperti bilangan: `c >= 'a'` benar bila c adalah `a` atau huruf sesudahnya.

**Masukan.** Satu karakter, bukan spasi.

**Keluaran.** `huruf besar`, `huruf kecil`, `angka`, atau `simbol`.

**Batasan.** Karakter adalah huruf, angka, atau salah satu dari `!@#$%&*+-=?`

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah-sedang — Kalkulator dengan Operator (`kalkulator-operator`)

Buat kalkulator sederhana. Masukannya sebuah hitungan seperti `12 + 5`: bilangan pertama, operator, bilangan kedua.

Operatornya salah satu dari `+`, `-`, `*`, `/`. Pembagian adalah pembagian bilangan bulat (sisanya dibuang), jadi `17 / 5` menghasilkan 3.

Bila diminta membagi dengan nol, cetak `tidak bisa dibagi nol`.

Contoh lain: `7 - 10` menghasilkan -3.

**Masukan.** Satu baris: bilangan bulat a, operator, bilangan bulat b, dipisah spasi.

**Keluaran.** Hasil hitungan, atau `tidak bisa dibagi nol`.

**Batasan.** 0 ≤ a, b ≤ 9000

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Mudah-sedang — Jam Dua Belas (`jam-dua-belas`)

Ubah waktu format 24 jam menjadi format 12 jam dengan keterangan `AM` atau `PM`.

- Jam 0 sampai 11 adalah `AM`, jam 12 sampai 23 adalah `PM`.
- Jam 0 ditulis 12 (tengah malam adalah `12:00 AM`), dan jam 12 tetap 12 (tengah hari adalah `12:00 PM`).
- Jam 13 sampai 23 ditulis 1 sampai 11.

Jam ditulis apa adanya, menit selalu dua digit.

Contoh: `0 5` menjadi `12:05 AM`, `9 30` menjadi `9:30 AM`, `15 7` menjadi `3:07 PM`.

**Masukan.** Satu baris berisi jam dan menit.

**Keluaran.** Waktu dalam format 12 jam, misalnya `3:07 PM`.

**Batasan.** 0 ≤ jam ≤ 23; 0 ≤ menit ≤ 59

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Sedang — Pajak Penghasilan (`pajak-penghasilan`)

Sebuah negara memungut pajak penghasilan **berlapis**. Penghasilan setahun dipotong per lapisan:

- 60 juta pertama: 5 persen
- di atas 60 juta sampai 250 juta: 15 persen
- di atas 250 juta: 25 persen

Tarif yang lebih tinggi hanya berlaku untuk **bagian** penghasilan di lapisan itu, bukan untuk seluruh penghasilan.

Baca penghasilan setahun dalam **juta rupiah**, lalu cetak pajaknya dalam **ribu rupiah**.

Contoh: penghasilan 100 juta. Lapisan pertama 60 juta × 5% = 3 juta; sisanya 40 juta × 15% = 6 juta. Pajak 9 juta, jadi keluarannya 9000.

Contoh lain: penghasilan 300 juta menghasilkan 44000.

**Masukan.** Satu bilangan bulat p: penghasilan dalam juta rupiah.

**Keluaran.** Satu bilangan bulat: pajak dalam ribu rupiah.

**Batasan.** 1 ≤ p ≤ 1000

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Sedang — Tarif Parkir (`tarif-parkir`)

Sebuah gedung parkir memasang tarif berikut.

- 1 = motor: jam pertama 2000, tiap jam berikutnya 1000, paling banyak 8000 sehari
- 2 = mobil: jam pertama 5000, tiap jam berikutnya 3000, paling banyak 30000 sehari

Biaya tidak pernah melebihi batas "paling banyak sehari".

Baca jenis kendaraan (1 atau 2) dan lama parkir dalam jam, lalu cetak biayanya.

Contoh: mobil 4 jam → 5000 + 3 × 3000 = 14000. Motor 10 jam → 2000 + 9 × 1000 = 11000, tetapi yang dibayar hanya 8000.

**Masukan.** Satu baris berisi jenis kendaraan dan lama parkir (jam).

**Keluaran.** Satu bilangan bulat: biaya parkir dalam rupiah.

**Batasan.** jenis 1 atau 2; 1 ≤ lama ≤ 24

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Sulit — Terbilang (`terbilang`)

Di kuitansi, jumlah uang ditulis juga dengan kata-kata. Baca sebuah bilangan dari 0 sampai 999, lalu cetak cara membacanya dalam bahasa Indonesia, dengan huruf kecil dan satu spasi di antara kata.

Aturannya:

- 0 dibaca `nol`; 1 sampai 9: `satu`, `dua`, `tiga`, `empat`, `lima`, `enam`, `tujuh`, `delapan`, `sembilan`;
- 10 dibaca `sepuluh`, 11 dibaca `sebelas`, 12 sampai 19 memakai `belas`: `dua belas`, `tiga belas`, …;
- puluhan memakai `puluh`: `dua puluh`, `tiga puluh lima`;
- 100 sampai 199 diawali `seratus`; ratusan lain memakai `ratus`: `dua ratus`, `sembilan ratus`.

Contoh: 7 → `tujuh`; 15 → `lima belas`; 40 → `empat puluh`; 111 → `seratus sebelas`; 205 → `dua ratus lima`; 999 → `sembilan ratus sembilan puluh sembilan`.

Kerjakan tanpa perulangan dan tanpa array. Pecah bilangan menjadi ratusan, puluhan, dan satuan terlebih dahulu.

**Masukan.** Satu bilangan bulat n.

**Keluaran.** Satu baris: n dalam kata-kata.

**Batasan.** 0 ≤ n ≤ 999

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.

### Sulit — Tiga Dadu (`tiga-dadu`)

Dalam sebuah permainan, tiga dadu dilempar sekaligus. Hasilnya dinilai dengan aturan berikut, diperiksa dari atas ke bawah:

- `kembar tiga` — ketiga dadu sama; poin: 100
- `sepasang` — tepat dua dadu sama; poin: 20 + mata dadu yang kembar
- `berurutan` — tiga mata dadu berurutan, dalam urutan lemparan apa pun (misalnya 4, 2, 3); poin: 60
- `biasa` — selain itu; poin: mata dadu terbesar

Baca ketiga mata dadu, lalu cetak nama hasilnya dan poinnya, dipisah satu spasi.

Contoh: `5 5 5` → `kembar tiga 100`; `4 2 3` → `berurutan 60`; `6 2 6` → `sepasang 26`; `1 6 3` → `biasa 6`.

Kerjakan tanpa perulangan dan tanpa array.

**Masukan.** Satu baris berisi tiga bilangan bulat: mata ketiga dadu.

**Keluaran.** Nama hasil, satu spasi, lalu poinnya.

**Batasan.** Tiap mata dadu antara 1 dan 6.

Mode banding `trimmed`, batas waktu 2000 ms, 2 tes contoh dan 10 tes tersembunyi.
