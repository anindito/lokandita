# RENCANA PEMBELAJARAN SEMESTER (RPS)
# BERBASIS OUTCOME-BASED EDUCATION (OBE)

## PEMROGRAMAN UNTUK TEKNIK SIPIL (PYTHON)

---

## IDENTITAS MATA KULIAH

| Komponen | Keterangan |
|---|---|
| **Nama Mata Kuliah** | Pemrograman untuk Teknik Sipil (Python) |
| **Nama katalog lama/acuan** | Bahasa Pemrograman |
| **Perguruan Tinggi** | Universitas Pertahanan Republik Indonesia |
| **Fakultas** | Fakultas Teknik dan Teknologi Pertahanan |
| **Kode Mata Kuliah** | SIP-113TE-3-D |
| **Bobot SKS** | **3 SKS (2 SKS Teori + 1 SKS Praktikum)** |
| **Semester** | 3 (Tiga) - mengacu RPS lama, perlu diselaraskan dengan kurikulum aktif |
| **Program Studi** | Teknik Sipil |
| **Dosen Pengampu** | Anindito, S.Kom., S.S., S.H., M.TI. [dapat disesuaikan] |
| **Pembimbing Capstone** | Dosen bidang Teknik Sipil sesuai topik kelompok |
| **Tahun Akademik** | 2026/2027 |
| **Bahasa Pemrograman** | Python 3.x |
| **IDE Utama** | Visual Studio Code; alternatif: IDLE / Thonny |
| **Total Pertemuan** | 16 pertemuan: 14 pertemuan materi, UTS di P7, UAS di P16 |
| **Karakter Ujian** | **UTS Teori dan UTS Praktik dinilai terpisah; UAS Teori dan UAS Praktik dinilai terpisah** |

> **Catatan penyelarasan kurikulum.** CPL yang dipakai dalam draft ini mempertahankan CPL-2 dan CPL-5 dari RPS Bahasa Pemrograman 2020 sebagai titik awal. Semester, rumusan CPL, skala nilai, serta ketentuan akademik harus dicocokkan dengan dokumen kurikulum Program Studi yang berlaku sebelum pengesahan.

---

## LANDASAN DAN BEBAN BELAJAR

Permendiktisaintek Nomor 39 Tahun 2025 tentang Penjaminan Mutu Pendidikan Tinggi menetapkan bahwa beban belajar 1 SKS setara dengan 45 jam per semester. Dengan demikian, mata kuliah 3 SKS memiliki beban belajar total **135 jam per semester**. Distribusi antara tatap muka, praktikum, tugas terstruktur, dan belajar mandiri mengikuti kebijakan akademik universitas/program studi.

Untuk kebutuhan penjadwalan kelas, RPS ini menggunakan pola operasional yang konsisten dengan contoh RPS pada Project Knowledge:

| Komponen | Alokasi terjadwal per pertemuan | Bentuk kegiatan |
|---|---:|---|
| **Teori (2 SKS)** | 100 menit | Penjelasan konsep, diskusi, worked example, kuis/ujian teori |
| **Praktikum (1 SKS)** | 150 menit | Live coding, latihan terbimbing, tugas praktik, proyek/ujian praktik |
| **Belajar terstruktur dan mandiri** | Disesuaikan agar total beban semester mencapai 135 jam | Membaca, latihan, debugging, laporan, pengembangan capstone, konsultasi |

---

## DESKRIPSI MATA KULIAH

Mata kuliah **Pemrograman untuk Teknik Sipil** memperkenalkan pemrograman Python kepada mahasiswa Teknik Sipil yang diasumsikan belum memiliki pengalaman pemrograman. Pembelajaran tidak diarahkan untuk menghasilkan software engineer, melainkan untuk membangun kemampuan menerjemahkan persoalan rekayasa ke dalam algoritma dan program komputer yang dapat diuji, divalidasi, dan dipertanggungjawabkan.

Materi dimulai dari algoritma, variabel, operasi matematika, **logika dan percabangan yang dialokasikan satu pertemuan penuh**, serta **perulangan yang juga dialokasikan satu pertemuan penuh**. Selanjutnya mahasiswa mempelajari struktur data, fungsi, pengolahan data dengan Pandas, visualisasi dengan Matplotlib, komputasi array dengan NumPy, dan pengantar metode numerik yang sengaja dibatasi pada konsep dan metode sederhana yang langsung berguna dalam komputasi rekayasa.

Dua pertemuan dialokasikan khusus untuk **pemrograman desktop dengan Tkinter**. Mahasiswa belajar membuat antarmuka input-proses-output dan menghubungkannya dengan fungsi komputasi Teknik Sipil. GUI diposisikan sebagai lapisan antarmuka; kebenaran model, satuan, algoritma, serta validasi hasil tetap menjadi fokus utama.

Pada paruh kedua semester, mahasiswa mengembangkan **Capstone Project** secara berkelompok kecil dengan pembimbing dosen bidang Teknik Sipil. Proyek harus menyelesaikan satu persoalan komputasi nyata atau realistis dalam bidang seperti hidrologi/hidraulika, struktur, geoteknik, transportasi, manajemen konstruksi, atau bidang Teknik Sipil lain yang disetujui pembimbing. UAS Praktik berupa presentasi, demonstrasi, dan viva terhadap capstone tersebut.

---

## PRASYARAT PENGETAHUAN

Tidak dipersyaratkan pengalaman pemrograman sebelumnya. Mahasiswa diasumsikan telah memiliki kemampuan dasar matematika dan sains yang lazim pada tahap awal pendidikan Teknik Sipil, antara lain:

1. Operasi aljabar dasar dan penggunaan rumus.
2. Pemahaman satuan dan konversi satuan.
3. Membaca tabel dan grafik sederhana.
4. Menggunakan komputer untuk pengelolaan berkas dan aplikasi dasar.
5. Memahami bahwa setiap model rekayasa memiliki asumsi dan batas penerapan.

---

## CAPAIAN PEMBELAJARAN LULUSAN (CPL) YANG DIBEBANKAN

| Kode | Capaian Pembelajaran Lulusan |
|---|---|
| **CPL-2** | Mampu menganalisis masalah teknik sipil yang kompleks dengan menggunakan prinsip dasar matematika, sains, dan ilmu keteknikan guna mendapatkan kesimpulan yang dapat dipertanggungjawabkan. |
| **CPL-5** | Mampu menggunakan alat bantu rekayasa dan alat TIK modern yang terkait dengan masalah teknik sipil yang kompleks. |

**Sumber rumusan:** RPS Bahasa Pemrograman Program Studi Teknik Konstruksi Bangunan Militer tahun 2020. Rumusan final wajib diselaraskan dengan CPL kurikulum aktif.

---

## CAPAIAN PEMBELAJARAN MATA KULIAH (CPMK)

| Kode | Capaian Pembelajaran Mata Kuliah | CPL terkait |
|---|---|---|
| **CPMK-1** | Mampu menyusun algoritma dan mengimplementasikan program Python dasar untuk menyelesaikan perhitungan Teknik Sipil secara benar, termasuk pengelolaan variabel, operasi matematika, input, output, dan satuan. | CPL-2, CPL-5 |
| **CPMK-2** | Mampu menerapkan percabangan, perulangan, struktur data, dan fungsi untuk membangun solusi komputasi Teknik Sipil yang terstruktur dan dapat digunakan ulang. | CPL-2, CPL-5 |
| **CPMK-3** | Mampu mengolah, meringkas, dan memvisualisasikan data Teknik Sipil menggunakan pustaka Python yang sesuai. | CPL-2, CPL-5 |
| **CPMK-4** | Mampu menerapkan komputasi numerik sederhana dan model rekayasa Teknik Sipil dengan memperhatikan galat, asumsi, satuan, serta validasi hasil. | CPL-2, CPL-5 |
| **CPMK-5** | Mampu membangun aplikasi desktop Python sederhana untuk komputasi Teknik Sipil dengan pemisahan antara antarmuka, validasi input, dan fungsi perhitungan. | CPL-5 |
| **CPMK-6** | Mampu mengembangkan, menguji, memvalidasi, mendokumentasikan, dan mempresentasikan capstone komputasi Teknik Sipil di bawah bimbingan dosen bidang. | CPL-2, CPL-5 |

---

## MATRIKS KONTRIBUSI CPMK TERHADAP CPL

Legenda: **U = Utama**, **P = Pendukung**.

| CPMK | CPL-2 | CPL-5 |
|---|:---:|:---:|
| CPMK-1 | U | U |
| CPMK-2 | U | U |
| CPMK-3 | U | U |
| CPMK-4 | U | P |
| CPMK-5 | P | U |
| CPMK-6 | U | U |

---

## SUB-CAPAIAN PEMBELAJARAN MATA KULIAH (Sub-CPMK)

| Kode | Sub-CPMK | CPMK | Pertemuan |
|---|---|---|:---:|
| **Sub-CPMK-1.1** | Menjelaskan peran algoritma dan Python dalam komputasi Teknik Sipil serta membuat program input-proses-output sederhana. | CPMK-1 | P1 |
| **Sub-CPMK-1.2** | Menggunakan variabel, tipe data, operator, fungsi matematika, format keluaran, serta konversi satuan dalam perhitungan rekayasa. | CPMK-1 | P2 |
| **Sub-CPMK-2.1** | Menerapkan Boolean, operator perbandingan/logika, dan struktur `if-elif-else` untuk pengambilan keputusan dan validasi kondisi. | CPMK-2 | P3 |
| **Sub-CPMK-2.2** | Menerapkan `for` dan `while` untuk proses berulang, akumulasi, iterasi data, serta kondisi berhenti. | CPMK-2 | P4 |
| **Sub-CPMK-2.3** | Menggunakan struktur data dan fungsi untuk memecah masalah rekayasa menjadi komponen komputasi yang modular. | CPMK-2 | P5 |
| **Sub-CPMK-3.1** | Membaca data tabular, melakukan pembersihan/ringkasan sederhana, dan membuat visualisasi yang benar beserta label dan satuan. | CPMK-3 | P6 |
| **Sub-CPMK-3.2** | Menggunakan array NumPy dan operasi vektor untuk komputasi data numerik Teknik Sipil. | CPMK-3 | P8 |
| **Sub-CPMK-4.1** | Menjelaskan galat dan toleransi serta menerapkan satu atau dua metode numerik sederhana pada contoh rekayasa. | CPMK-4 | P9 |
| **Sub-CPMK-4.2** | Menerjemahkan model Teknik Sipil menjadi fungsi komputasi, memvalidasi parameter, dan menguji hasil terhadap acuan. | CPMK-4, CPMK-6 | P10 |
| **Sub-CPMK-5.1** | Membuat GUI desktop dasar dengan komponen input, tombol, layout, callback, dan output. | CPMK-5 | P11 |
| **Sub-CPMK-5.2** | Mengintegrasikan GUI dengan fungsi perhitungan, validasi input, hasil bersatuan, dan visualisasi/keluaran sederhana. | CPMK-5 | P12 |
| **Sub-CPMK-6.1** | Mengintegrasikan komponen capstone menjadi prototipe aplikasi yang dapat dijalankan. | CPMK-6 | P13 |
| **Sub-CPMK-6.2** | Melakukan verifikasi kode dan validasi model bersama pembimbing bidang serta memperbaiki program berdasarkan hasil pengujian. | CPMK-4, CPMK-6 | P14 |
| **Sub-CPMK-6.3** | Menyelesaikan dokumentasi, pengujian akhir, dan gladi presentasi capstone secara profesional. | CPMK-6 | P15 |

---

## PETA ALOKASI PERTEMUAN

| Pertemuan | Pokok Bahasan | Status |
|:---:|---|---|
| 1 | Pengenalan Pemrograman, Algoritma, dan Python | Materi |
| 2 | Variabel, Tipe Data, Operasi Matematika, dan Satuan | Materi |
| 3 | **Logika dan Percabangan** | Materi - pendalaman |
| 4 | **Perulangan** | Materi - pendalaman |
| 5 | Struktur Data dan Fungsi | Materi |
| 6 | Pengolahan Data Sederhana dan Visualisasi Awal | Materi + inisiasi capstone |
| 7 | **UTS Teori + UTS Praktik (terpisah)** | Ujian |
| 8 | NumPy dan Komputasi Data Numerik | Materi |
| 9 | Pengantar Metode Numerik Praktis | Materi - dibatasi |
| 10 | Penerapan Komputasi Teknik Sipil, Desain Program, dan Pengujian | Materi + capstone |
| 11 | **GUI Desktop I - Dasar Tkinter** | Materi |
| 12 | **GUI Desktop II - Aplikasi Komputasi Teknik Sipil** | Materi |
| 13 | Integrasi dan Pengembangan Capstone | Project-based learning |
| 14 | Validasi Model dan Klinik Capstone | Project-based learning |
| 15 | Finalisasi, Dokumentasi, dan Gladi Presentasi | Project-based learning |
| 16 | **UAS Teori + UAS Praktik Capstone (terpisah)** | Ujian |

---

# RENCANA PEMBELAJARAN SEMESTER PER PERTEMUAN

## PERTEMUAN 1 - Pengenalan Pemrograman, Algoritma, dan Python

| Komponen | Rencana |
|---|---|
| **Sub-CPMK** | Sub-CPMK-1.1 |
| **Indikator** | Mahasiswa dapat menjelaskan input-process-output, menulis pseudocode/flowchart sederhana, menjalankan script Python, dan menghasilkan keluaran yang benar. |
| **Bahan Kajian Teori** | Peran komputasi dalam Teknik Sipil; masalah, algoritma, program; input-process-output; pseudocode/flowchart; Python dan lingkungan kerja; `print()`, `input()`, komentar; error dasar. |
| **Praktikum** | Instalasi/cek Python dan VS Code; menjalankan script; membuat kalkulator volume beton atau kebutuhan material sederhana. |
| **Metode** | Ceramah interaktif, demonstrasi, live coding, latihan terbimbing. |
| **Pengalaman Belajar** | Mahasiswa mengubah satu rumus Teknik Sipil sederhana menjadi algoritma lalu program. |
| **Asesmen Formatif** | Exit ticket algoritma + hasil praktikum. |
| **Alokasi** | Teori 100 menit; Praktik 150 menit. |

## PERTEMUAN 2 - Variabel, Tipe Data, Operasi Matematika, dan Satuan

| Komponen | Rencana |
|---|---|
| **Sub-CPMK** | Sub-CPMK-1.2 |
| **Indikator** | Mahasiswa dapat memilih tipe data, melakukan konversi, menulis ekspresi matematika dengan prioritas yang benar, dan menampilkan hasil beserta satuan. |
| **Bahan Kajian Teori** | Variabel; `int`, `float`, `str`, `bool`; konversi tipe; operator aritmetika; prioritas operasi; modul `math`; pembulatan; format string; satuan dan konsistensi dimensi. |
| **Praktikum** | Kalkulator luas/volume, berat sendiri elemen, atau tekanan hidrostatik; membandingkan hasil dengan hitungan manual. |
| **Metode** | Worked example, live coding, latihan individual. |
| **Asesmen Formatif** | Kuis singkat + praktikum. |
| **Alokasi** | Teori 100 menit; Praktik 150 menit. |

## PERTEMUAN 3 - Logika dan Percabangan

| Komponen | Rencana |
|---|---|
| **Sub-CPMK** | Sub-CPMK-2.1 |
| **Indikator** | Mahasiswa dapat mengevaluasi ekspresi Boolean, memilih struktur percabangan yang tepat, menangani nilai batas, dan menguji seluruh cabang program. |
| **Bahan Kajian Teori** | Boolean; operator `==`, `!=`, `<`, `<=`, `>`, `>=`; `and`, `or`, `not`; tabel kebenaran sederhana; `if`, `if-else`, `if-elif-else`; nested decision; urutan evaluasi; boundary condition; validasi input. |
| **Praktikum** | Latihan bertingkat: kondisi tunggal -> dua cabang -> multi-cabang -> kondisi majemuk -> nested condition. Kasus: pemeriksaan rentang input, klasifikasi parameter, atau pemilihan prosedur perhitungan berdasarkan ketentuan yang diberikan. |
| **Metode** | Ceramah interaktif, tracing manual, live coding, pair checking, latihan individual. |
| **Catatan Pedagogis** | **Satu pertemuan penuh khusus percabangan** karena mahasiswa diasumsikan awam pemrograman. Pustaka tingkat tinggi belum digunakan agar alur keputusan terlihat jelas. |
| **Asesmen Formatif** | Kuis tracing + praktikum pengujian cabang dan nilai batas. |
| **Alokasi** | Teori 100 menit; Praktik 150 menit. |

## PERTEMUAN 4 - Perulangan

| Komponen | Rencana |
|---|---|
| **Sub-CPMK** | Sub-CPMK-2.2 |
| **Indikator** | Mahasiswa dapat membedakan penggunaan `for` dan `while`, membuat counter/accumulator, menentukan kondisi berhenti, serta melakukan tracing iterasi. |
| **Bahan Kajian Teori** | Konsep iterasi; `for`; `range()`; iterasi list sederhana; `while`; counter; accumulator; sentinel/kondisi berhenti; `break` dan `continue` secukupnya; nested loop sederhana; risiko infinite loop. |
| **Praktikum** | Latihan bertingkat: pengulangan tetap -> akumulasi -> iterasi data -> `while` berbasis kondisi -> nested loop sederhana. Kasus: total volume beberapa segmen, tabel variasi dimensi, atau rekap curah hujan. |
| **Metode** | Tracing manual, live coding, latihan terbimbing dan individual. |
| **Catatan Pedagogis** | **Satu pertemuan penuh khusus perulangan.** Fokus pada pemahaman perubahan nilai variabel dari iterasi ke iterasi sebelum memakai vectorized operation. |
| **Asesmen Formatif** | Kuis tracing + praktikum perulangan. |
| **Alokasi** | Teori 100 menit; Praktik 150 menit. |

## PERTEMUAN 5 - Struktur Data dan Fungsi

| Komponen | Rencana |
|---|---|
| **Sub-CPMK** | Sub-CPMK-2.3 |
| **Indikator** | Mahasiswa dapat menyimpan sekumpulan data dalam struktur yang sesuai dan menulis fungsi dengan parameter serta return value. |
| **Bahan Kajian Teori** | List, tuple, dictionary; indexing/slicing secukupnya; iterasi koleksi; fungsi; parameter; `return`; scope sederhana; docstring; prinsip menghindari duplikasi kode. |
| **Praktikum** | Fungsi perhitungan luas/volume/debit sederhana untuk banyak elemen/lokasi; menyimpan hasil dalam list/dictionary. |
| **Metode** | Demonstrasi, refactoring contoh, praktik individual. |
| **Asesmen Formatif** | Tugas teori singkat + praktikum fungsi modular. |
| **Alokasi** | Teori 100 menit; Praktik 150 menit. |

## PERTEMUAN 6 - Pengolahan Data Sederhana dan Visualisasi Awal

| Komponen | Rencana |
|---|---|
| **Sub-CPMK** | Sub-CPMK-3.1 |
| **Indikator** | Mahasiswa dapat membaca CSV, memeriksa data, menghitung ringkasan sederhana, membuat grafik dengan label/satuan, dan menyimpan hasil. |
| **Bahan Kajian Teori** | File CSV; pengantar Pandas `DataFrame`; membaca/menyeleksi kolom; data kosong secara sederhana; mean, minimum, maksimum, median/standar deviasi sebagai pengenalan; Matplotlib: line, bar, scatter; label, legend, unit. |
| **Praktikum** | Mengolah dataset kecil: curah hujan, kuat tekan beton, hasil uji tanah, atau volume lalu lintas. Membuat tabel ringkasan dan grafik. |
| **Capstone** | Pembentukan kelompok 2-3 mahasiswa; memilih bidang/topik awal; mengusulkan calon pembimbing Teknik Sipil. |
| **Asesmen Formatif** | Praktikum data + one-page concept note capstone. |
| **Alokasi** | Teori 100 menit; Praktik 150 menit. |

## PERTEMUAN 7 - UJIAN TENGAH SEMESTER (UTS)

### UTS TEORI - DINILAI TERPISAH

| Komponen | Ketentuan |
|---|---|
| **Bobot Mata Kuliah** | **10%** |
| **Durasi** | 100 menit |
| **Sifat** | Individual |
| **Cakupan** | P1-P6: algoritma, tipe data, operasi matematika, satuan, logika/percabangan, perulangan, struktur data, fungsi, dasar data/visualisasi. |
| **Bentuk** | Soal konsep, tracing program, analisis kesalahan, pemilihan struktur kontrol, dan penyusunan algoritma/pseudocode untuk kasus Teknik Sipil. |
| **CPMK diukur** | CPMK-1, CPMK-2, sebagian CPMK-3 |

### UTS PRAKTIK - DINILAI TERPISAH

| Komponen | Ketentuan |
|---|---|
| **Bobot Mata Kuliah** | **10%** |
| **Durasi** | 150 menit |
| **Sifat** | Individual, live coding |
| **Bentuk** | Menyelesaikan satu persoalan Teknik Sipil terstruktur menggunakan input-output, percabangan, perulangan, fungsi, dan/atau data sederhana. |
| **Bukti Kinerja** | Source code, keluaran program, kasus uji, dan penjelasan singkat hasil/satuan. |
| **CPMK diukur** | CPMK-1, CPMK-2, CPMK-3 |

> Nilai UTS Teori dan UTS Praktik **tidak digabung sebagai satu komponen tunggal**. Keduanya dicatat dan dianalisis secara terpisah untuk evaluasi ketercapaian CPMK.

## PERTEMUAN 8 - NumPy dan Komputasi Data Numerik

| Komponen | Rencana |
|---|---|
| **Sub-CPMK** | Sub-CPMK-3.2 |
| **Indikator** | Mahasiswa dapat membuat array, melakukan operasi elemen dan agregasi, serta menjelaskan perbedaan loop eksplisit dan operasi vektor pada kasus sederhana. |
| **Bahan Kajian Teori** | NumPy array; shape; indexing; operasi elemen; agregasi; operasi vektor; matriks dasar yang relevan tanpa pendalaman aljabar linear. |
| **Praktikum** | Mengolah serangkaian data pengukuran atau variasi parameter rekayasa dengan array. |
| **Asesmen Formatif** | Praktikum NumPy. |
| **Alokasi** | Teori 100 menit; Praktik 150 menit. |

## PERTEMUAN 9 - Pengantar Metode Numerik Praktis

| Komponen | Rencana |
|---|---|
| **Sub-CPMK** | Sub-CPMK-4.1 |
| **Indikator** | Mahasiswa dapat menjelaskan galat/toleransi secara intuitif dan menerapkan metode numerik sederhana dengan program. |
| **Bahan Kajian Teori** | Mengapa metode numerik; galat dan toleransi; iterasi; aturan trapesium; metode bagi dua sebagai contoh pencarian akar. Tidak membahas derivasi matematis mendalam atau metode numerik lanjut. |
| **Praktikum** | Contoh utama: luas penampang tidak beraturan dengan aturan trapesium; contoh tambahan terarah: pencarian akar sederhana dengan bisection. |
| **Metode** | Worked example, visualisasi proses, live coding, pembandingan dengan nilai acuan. |
| **Asesmen Formatif** | Praktikum metode numerik sederhana. |
| **Alokasi** | Teori 100 menit; Praktik 150 menit. |

## PERTEMUAN 10 - Penerapan Komputasi Teknik Sipil, Desain Program, dan Pengujian

| Komponen | Rencana |
|---|---|
| **Sub-CPMK** | Sub-CPMK-4.2 |
| **Indikator** | Mahasiswa dapat menetapkan input, persamaan, asumsi, satuan, fungsi komputasi, validasi, dan kasus uji untuk satu model Teknik Sipil. |
| **Bahan Kajian Teori** | Translasi model rekayasa ke algoritma; pemisahan input-proses-output; fungsi modular; validasi input; exception sederhana; test case; nilai batas; verifikasi hasil terhadap hitungan manual/acuan. |
| **Praktikum** | Satu studi kasus kelas, misalnya perhitungan debit/metode hidrologi atau hidraulika sederhana yang rumusnya ditetapkan dosen bidang; alternatif setara dari struktur, geoteknik, transportasi, atau manajemen konstruksi. |
| **Capstone** | Proposal teknis: tujuan, pengguna, input, metode/persamaan, asumsi, output, dataset jika ada, rencana validasi, dan persetujuan pembimbing bidang. |
| **Asesmen** | Praktikum + milestone proposal capstone. |
| **Alokasi** | Teori 100 menit; Praktik 150 menit. |

## PERTEMUAN 11 - GUI Desktop I: Dasar Tkinter

| Komponen | Rencana |
|---|---|
| **Sub-CPMK** | Sub-CPMK-5.1 |
| **Indikator** | Mahasiswa dapat membuat jendela dengan input, label, tombol, layout, callback, dan output yang berfungsi. |
| **Bahan Kajian Teori** | Konsep GUI dan event-driven programming; `Tk`, `Frame`, `Label`, `Entry`, `Button`; `grid()`; variable binding secukupnya; callback; `mainloop()`; separation of concerns secara sederhana. |
| **Praktikum** | Membuat GUI kalkulator luas/volume atau perhitungan Teknik Sipil sederhana dengan tombol Hitung, Reset, dan Keluar. |
| **Catatan** | Tidak membahas OOP GUI secara mendalam. Fokus pada pola aplikasi sederhana yang dapat dipahami pemula. |
| **Asesmen Formatif** | Praktikum GUI I. |
| **Alokasi** | Teori 100 menit; Praktik 150 menit. |

## PERTEMUAN 12 - GUI Desktop II: Aplikasi Komputasi Teknik Sipil

| Komponen | Rencana |
|---|---|
| **Sub-CPMK** | Sub-CPMK-5.2 |
| **Indikator** | Mahasiswa dapat menghubungkan GUI dengan fungsi perhitungan terpisah, melakukan validasi input, menangani error, dan menampilkan hasil bersatuan/visual. |
| **Bahan Kajian Teori** | Integrasi GUI-fungsi; konversi dan validasi input; `try-except` sederhana; message box; pilihan parameter; hasil bersatuan; integrasi grafik sederhana bila relevan. |
| **Praktikum** | Aplikasi contoh: kalkulator analisis hidrologi/hidraulika sederhana atau kasus Teknik Sipil setara. GUI harus memanggil fungsi komputasi yang dapat diuji tanpa GUI. |
| **Asesmen Formatif** | Praktikum GUI II. |
| **Alokasi** | Teori 100 menit; Praktik 150 menit. |

## PERTEMUAN 13 - Integrasi dan Pengembangan Capstone

| Komponen | Rencana |
|---|---|
| **Sub-CPMK** | Sub-CPMK-6.1 |
| **Indikator** | Prototipe capstone dapat dijalankan dari input sampai output utama dan struktur program dapat dijelaskan oleh seluruh anggota. |
| **Bahan Kajian Teori** | Integrasi modul; struktur folder; dependency sederhana; penyimpanan data/hasil bila perlu; prinsip reproduksibilitas; penggunaan Git/GitHub dasar untuk versi dan kontribusi. |
| **Praktikum/Project** | Sprint capstone: menyelesaikan minimum viable prototype. Dosen pemrograman memeriksa struktur kode; pembimbing Teknik Sipil memeriksa model. |
| **Asesmen** | Milestone prototipe + log kontribusi. |
| **Alokasi** | Teori 100 menit; Praktik 150 menit. |

## PERTEMUAN 14 - Validasi Model dan Klinik Capstone

| Komponen | Rencana |
|---|---|
| **Sub-CPMK** | Sub-CPMK-6.2 |
| **Indikator** | Mahasiswa dapat menunjukkan tabel/rekam uji, membandingkan hasil program dengan acuan, menjelaskan selisih, dan memperbaiki error yang ditemukan. |
| **Bahan Kajian Teori** | Verifikasi kode vs validasi model; test case normal, batas, dan invalid; konsistensi satuan; sensitivitas sederhana; batas penerapan model; interpretasi hasil. |
| **Praktikum/Project** | Klinik kelompok dengan pembimbing; minimal satu validasi terhadap hitungan manual, contoh referensi, dataset berlabel, atau perangkat/metode pembanding yang disetujui pembimbing. |
| **Asesmen** | Milestone validasi + catatan konsultasi. |
| **Alokasi** | Teori 100 menit; Praktik 150 menit. |

## PERTEMUAN 15 - Finalisasi, Dokumentasi, dan Gladi Presentasi

| Komponen | Rencana |
|---|---|
| **Sub-CPMK** | Sub-CPMK-6.3 |
| **Indikator** | Program final dapat dijalankan dari lingkungan yang bersih/terdokumentasi, README lengkap, hasil uji tersedia, dan kelompok mampu melakukan demo tanpa bergantung pada satu anggota. |
| **Bahan Kajian Teori** | Dokumentasi teknis; README; cara instalasi/menjalankan; data contoh; keterbatasan; struktur presentasi rekayasa; etika penggunaan pustaka/AI; reproducibility. |
| **Praktikum/Project** | Freeze versi kandidat final; peer review; gladi demo dan tanya jawab; perbaikan terakhir. |
| **Asesmen** | Milestone finalisasi capstone. |
| **Alokasi** | Teori 100 menit; Praktik 150 menit. |

## PERTEMUAN 16 - UJIAN AKHIR SEMESTER (UAS)

### UAS TEORI - DINILAI TERPISAH

| Komponen | Ketentuan |
|---|---|
| **Bobot Mata Kuliah** | **15%** |
| **Durasi** | 100 menit |
| **Sifat** | Individual |
| **Cakupan** | P8-P15 dengan integrasi konsep P1-P6: NumPy, pengolahan numerik, metode numerik dasar, desain fungsi rekayasa, validasi/pengujian, konsep GUI, integrasi aplikasi, dan interpretasi hasil. |
| **Bentuk** | Analisis kasus, membaca/memperbaiki potongan kode, desain algoritma, penalaran tentang validasi dan batas model; bukan hafalan sintaks semata. |
| **CPMK diukur** | CPMK-3, CPMK-4, CPMK-5, sebagian CPMK-6 |

### UAS PRAKTIK - PRESENTASI CAPSTONE PROJECT - DINILAI TERPISAH

| Komponen | Ketentuan |
|---|---|
| **Bobot Mata Kuliah** | **20%** |
| **Bentuk** | Presentasi teknis, demonstrasi aplikasi, pemeriksaan source code, serta viva/tanya jawab individu. |
| **Kelompok** | Disarankan 2-3 mahasiswa. Nilai memiliki komponen kelompok dan individu. |
| **Pembimbing/Penguji** | Dosen pemrograman + dosen pembimbing/penguji bidang Teknik Sipil. |
| **Wajib diserahkan** | Source code; README; data contoh (jika ada); dokumen metode/persamaan dan sumber; test cases; hasil validasi; batasan model; slide presentasi; log kontribusi anggota. |
| **Validasi** | Hasil harus dibandingkan dengan hitungan manual, contoh acuan, dataset, atau metode/perangkat pembanding yang disetujui pembimbing Teknik Sipil. |
| **CPMK diukur** | CPMK-4, CPMK-5, CPMK-6 |
| **Penjadwalan** | Dilaksanakan pada P16 dalam slot/gelombang sesuai jumlah kelompok dan ketentuan akademik. |

> UAS Praktik bukan lomba estetika GUI. **Kebenaran model, keterlacakan asumsi/satuan, validasi, dan kemampuan mahasiswa mempertanggungjawabkan program** memiliki bobot lebih besar daripada tampilan antarmuka.

---

# STRUKTUR CAPSTONE PROJECT

## Tujuan

Capstone menjadi bukti kinerja utama bahwa mahasiswa mampu menggabungkan pemrograman dengan satu bidang Teknik Sipil. Topik ditetapkan bersama pembimbing bidang agar kedalaman rekayasa sesuai tingkat mahasiswa.

## Bidang yang Dapat Dipilih

1. Hidrologi dan hidraulika.
2. Struktur.
3. Geoteknik.
4. Transportasi.
5. Manajemen konstruksi.
6. Survei/geomatika atau bidang Teknik Sipil lain yang disetujui.

## Contoh Ruang Lingkup

- **Hidrologi/hidraulika:** analisis data curah hujan, debit rencana sederhana, kapasitas saluran, atau aplikasi perhitungan hidraulik dengan model yang disetujui pembimbing.
- **Struktur:** reaksi perletakan, beban sederhana, diagram gaya dalam sederhana, atau kalkulator elemen berdasarkan formulasi yang diberikan pembimbing.
- **Geoteknik:** pengolahan hasil uji tanah, tekanan tanah, daya dukung/perhitungan sederhana sesuai materi yang telah dipelajari.
- **Transportasi:** rekap volume lalu lintas, kecepatan/waktu tempuh, atau analisis data survei sederhana.
- **Manajemen konstruksi:** volume pekerjaan, kebutuhan material, estimasi sederhana, atau pengolahan jadwal/data proyek.

## Tahapan Capstone

| Tahap | Waktu | Bukti |
|---|---|---|
| Ide & pembentukan kelompok | P6 | Concept note + calon pembimbing |
| Proposal teknis | P10 | Masalah, metode, input-output, asumsi, sumber, rencana validasi |
| Prototipe | P13 | Aplikasi minimum yang dapat berjalan |
| Validasi | P14 | Test cases + pembandingan hasil + catatan pembimbing |
| Finalisasi | P15 | Release candidate + README + dokumentasi |
| UAS Praktik | P16 | Presentasi, demo, source inspection, viva |

---

# KOMPONEN PENILAIAN

## Penilaian Teori dan Praktik Dipisahkan

| Komponen | Jenis | Bobot |
|---|---|:---:|
| Kuis teori | Teori | 10% |
| Tugas/analisis teori | Teori | 10% |
| Praktikum mingguan | Praktik | 15% |
| **UTS Teori** | **Teori** | **10%** |
| **UTS Praktik** | **Praktik** | **10%** |
| Milestone Capstone (P10, P13, P14, P15) | Praktik/Project | 10% |
| **UAS Teori** | **Teori** | **15%** |
| **UAS Praktik - Capstone** | **Praktik/Project** | **20%** |
| **TOTAL** |  | **100%** |

Ringkasan domain: **Teori 45%** dan **Praktik/Project 55%**. Pemisahan ini memungkinkan analisis ketercapaian kompetensi konseptual dan performa praktik secara terpisah.

---

## RUBRIK UTS PRAKTIK

| Aspek | Bobot dalam UTS Praktik | Kriteria utama |
|---|:---:|---|
| Ketepatan algoritma | 25% | Alur menyelesaikan masalah sesuai spesifikasi. |
| Percabangan/perulangan | 25% | Struktur kontrol tepat, termasuk kondisi batas/berhenti. |
| Fungsi dan struktur data | 15% | Program modular dan menggunakan struktur data yang sesuai. |
| Correctness hasil | 20% | Hasil dan satuan benar pada test case. |
| Validasi/error handling dasar | 10% | Input tidak valid ditangani secara wajar. |
| Keterbacaan kode | 5% | Nama variabel, indentasi, dan struktur mudah dipahami. |

---

## RUBRIK UAS PRAKTIK - CAPSTONE PROJECT

| Aspek | Bobot dalam UAS Praktik | Penilai utama |
|---|:---:|---|
| Ketepatan model/persamaan Teknik Sipil | 20% | Dosen Teknik Sipil |
| Asumsi, satuan, dan batas penerapan | 10% | Dosen Teknik Sipil |
| Validasi terhadap acuan dan interpretasi hasil | 15% | Dosen Teknik Sipil + dosen pemrograman |
| Ketepatan algoritma dan hasil program | 15% | Dosen pemrograman |
| Struktur/modularitas dan kualitas kode | 10% | Dosen pemrograman |
| GUI/usability dan validasi input | 10% | Dosen pemrograman |
| Dokumentasi dan reproducibility | 5% | Bersama |
| Presentasi dan kualitas demonstrasi | 5% | Bersama |
| **Viva/penguasaan individu** | **10%** | Bersama |
| **Total** | **100%** | |

**Prinsip penilaian individu:** nilai UAS Praktik tidak boleh seluruhnya menjadi nilai kelompok. Komponen viva 10% wajib individual dan dapat diperkuat dengan log kontribusi/riwayat commit serta pertanyaan terhadap bagian kode yang dikerjakan masing-masing mahasiswa.

---

## RUBRIK MILESTONE CAPSTONE

| Tahap | Bobot dalam komponen Milestone | Kriteria |
|---|:---:|---|
| Proposal teknis P10 | 20% | Masalah jelas, metode relevan, input-output, asumsi, sumber, rencana validasi. |
| Prototipe P13 | 30% | Alur utama berjalan dan fungsi inti dapat diuji. |
| Validasi P14 | 30% | Test cases memadai, pembandingan hasil terdokumentasi, perbaikan dilakukan. |
| Finalisasi P15 | 20% | README, struktur file, dokumentasi, dan kesiapan demo. |

---

# CONSTRUCTIVE ALIGNMENT: CPMK - ASESMEN

| CPMK | Bukti/asesmen utama |
|---|---|
| **CPMK-1** | Praktikum P1-P2, kuis, UTS Teori, UTS Praktik |
| **CPMK-2** | Praktikum P3-P5, kuis, UTS Teori, UTS Praktik |
| **CPMK-3** | Praktikum P6 & P8, UTS/UAS Teori, tugas data |
| **CPMK-4** | Praktikum P9-P10, milestone validasi, UAS Teori, UAS Praktik |
| **CPMK-5** | Praktikum GUI P11-P12, UAS Teori, UAS Praktik |
| **CPMK-6** | Milestone P10/P13/P14/P15, UAS Praktik, viva |

## Target Ketercapaian OBE (Usulan Operasional)

Untuk evaluasi internal mata kuliah, digunakan target awal: **sekurang-kurangnya 70% mahasiswa memperoleh skor minimal 70/100 pada bukti asesmen utama setiap CPMK**. Angka ini merupakan usulan operasional dan harus diganti apabila Program Studi memiliki standar ketercapaian CPL/CPMK tersendiri.

Jika target suatu CPMK tidak tercapai, dosen mendokumentasikan tindak lanjut pada Rencana Tindak Lanjut (RTL), misalnya remediasi terarah, perubahan contoh/latihan, penambahan waktu latihan, atau revisi urutan materi pada semester berikutnya.

---

# STRATEGI PEMBELAJARAN

Mata kuliah menggunakan kombinasi:

- Ceramah interaktif singkat untuk konsep inti.
- Worked example yang menggunakan masalah Teknik Sipil.
- Live coding dan tracing program.
- Latihan terbimbing lalu latihan individual.
- Problem-based learning untuk studi kasus.
- Project-based learning untuk capstone.
- Peer review dan konsultasi pembimbing bidang.
- Demonstrasi, presentasi, dan viva pada UAS Praktik.

**Prinsip untuk mahasiswa pemula:** satu konsep baru diperkenalkan melalui contoh kecil terlebih dahulu, kemudian dimodifikasi, ditelusuri secara manual, diuji dengan kasus batas, dan baru diterapkan pada konteks Teknik Sipil.

---

# MEDIA DAN SUMBER BELAJAR

## Perangkat Keras

- Laptop/PC yang mampu menjalankan Python 3.x dan editor kode.
- Koneksi internet untuk mengakses dokumentasi dan repository.

## Perangkat Lunak

| Kategori | Perangkat | Sumber |
|---|---|---|
| Bahasa | Python 3.x | https://www.python.org/ |
| IDE utama | Visual Studio Code + Python extension | https://code.visualstudio.com/ |
| IDE alternatif pemula | Thonny / IDLE | https://thonny.org/ ; termasuk pada distribusi Python untuk IDLE |
| Data numerik | NumPy | https://numpy.org/ |
| Data tabular | Pandas | https://pandas.pydata.org/ |
| Visualisasi | Matplotlib | https://matplotlib.org/ |
| GUI desktop | Tkinter | https://docs.python.org/3/library/tkinter.html |
| Version control | Git + GitHub | https://git-scm.com/ ; https://github.com/ |

---

# REFERENSI

## Referensi Pemrograman

1. Python Software Foundation. *Python Tutorial* dan *Python Standard Library Documentation*. https://docs.python.org/3/
2. Matthes, E. *Python Crash Course*. No Starch Press. Edisi terbaru yang tersedia.
3. Sweigart, A. *Automate the Boring Stuff with Python*. Sumber belajar daring tersedia di https://automatetheboringstuff.com/
4. NumPy Developers. *NumPy User Guide*. https://numpy.org/doc/
5. pandas development team. *pandas User Guide*. https://pandas.pydata.org/docs/
6. Matplotlib Development Team. *Matplotlib Documentation*. https://matplotlib.org/stable/
7. Python Software Foundation. *tkinter - Python interface to Tcl/Tk*. https://docs.python.org/3/library/tkinter.html

## Referensi Komputasi Rekayasa

8. Chapra, S. C. & Canale, R. P. *Numerical Methods for Engineers*. Digunakan terbatas untuk konsep galat, iterasi, aturan trapesium, dan metode bagi dua.
9. Referensi/SNI/manual teknis bidang Teknik Sipil yang relevan dengan tiap capstone, **ditetapkan atau disetujui oleh dosen pembimbing bidang**.
10. Dataset, contoh hitungan, atau perangkat pembanding yang digunakan untuk validasi harus dicantumkan secara eksplisit pada dokumentasi proyek.

## Acuan Kurikulum dan Regulasi

11. RPS Bahasa Pemrograman, Program Studi Teknik Konstruksi Bangunan Militer, Universitas Pertahanan, 2020 - digunakan sebagai referensi historis CPL dan arah aplikasi Teknik Sipil.
12. Peraturan Menteri Pendidikan Tinggi, Sains, dan Teknologi Republik Indonesia Nomor 39 Tahun 2025 tentang Penjaminan Mutu Pendidikan Tinggi, khususnya ketentuan beban belajar 1 SKS setara 45 jam per semester. https://peraturan.bpk.go.id/Details/333967

---

# ATURAN PERKULIAHAN (DRAFT UNTUK DISESUAIKAN)

1. Kehadiran mengikuti ketentuan akademik Universitas/Program Studi yang berlaku.
2. Praktikum harus dikerjakan dan dapat dijelaskan kembali oleh mahasiswa.
3. UTS Teori, UTS Praktik, dan UAS Teori bersifat individual.
4. Capstone dapat dikerjakan dalam kelompok kecil, tetapi viva dan evaluasi penguasaan tetap individual.
5. Penggunaan AI coding assistant diperbolehkan untuk pembelajaran, eksplorasi, dan debugging **apabila diizinkan oleh kebijakan mata kuliah**, tetapi penggunaannya harus dideklarasikan. Mahasiswa tetap wajib memahami dan mampu menjelaskan seluruh kode yang diserahkan.
6. Kode/sumber dari pihak lain harus dicantumkan sumber dan lisensinya.
7. Model, rumus, parameter, asumsi, dan interpretasi Teknik Sipil pada capstone wajib mendapat persetujuan pembimbing bidang.
8. Source code dan dokumen capstone disimpan dalam repository yang ditentukan dosen.
9. Data sensitif/terbatas tidak boleh digunakan tanpa izin yang sesuai.
10. Ketentuan keterlambatan, remedial, ujian susulan, dan skala nilai mengikuti peraturan akademik yang berlaku.

---

# CATATAN DESAIN MATA KULIAH

1. **Percabangan dan perulangan dipisahkan.** P3 dan P4 masing-masing satu pertemuan penuh karena mahasiswa diasumsikan awam pemrograman.
2. **Metode numerik tidak diperdalam.** P9 hanya memberikan pemahaman praktis mengenai galat, toleransi, aturan trapesium, dan metode bagi dua sebagai contoh. Mata kuliah ini bukan pengganti Mata Kuliah Metode Numerik.
3. **GUI mendapat dua pertemuan.** P11 untuk dasar Tkinter; P12 untuk integrasi GUI dengan fungsi komputasi Teknik Sipil.
4. **Capstone berorientasi rekayasa.** Tampilan aplikasi bukan tujuan utama; ketepatan model, satuan, hasil, validasi, dan keterjelasan batasan lebih penting.
5. **Pembimbing Teknik Sipil wajib terlibat.** Dosen pemrograman tidak menjadi satu-satunya pihak yang mengesahkan kebenaran model rekayasa.
6. **UTS dan UAS dipisahkan teori/praktik.** Masing-masing memiliki bobot, instrumen, bukti kinerja, dan analisis ketercapaian CPMK tersendiri.
7. **Statistik tidak menjadi blok besar.** Statistik deskriptif dasar terintegrasi pada P6 sehingga fokus mata kuliah tetap pemrograman komputasi.
8. **Pandas/NumPy diberikan setelah logika dasar.** Mahasiswa terlebih dahulu memahami alur percabangan dan perulangan sebelum memakai operasi library yang mengabstraksikan proses tersebut.

---

# DOKUMENTASI EVALUASI OBE

Pada akhir semester, dosen pengampu menyiapkan ringkasan:

| Elemen Evaluasi | Isi |
|---|---|
| Capaian CPMK | Persentase mahasiswa yang memenuhi target setiap CPMK |
| Instrumen utama | Nilai ujian teori/praktik, praktikum, milestone, capstone |
| CPMK belum tercapai | Identifikasi konsep/keterampilan yang paling lemah |
| Analisis penyebab | Materi, waktu, kompleksitas soal, prasyarat, fasilitas, atau faktor lain |
| RTL | Tindakan perbaikan untuk semester berikutnya |
| Bukti | Rubrik, sampel pekerjaan, rekap nilai, komentar pembimbing, dokumentasi capstone |

---

# PENGESAHAN

**Disusun oleh:**  
Anindito, S.Kom., S.S., S.H., M.TI.  
Dosen Pengampu / Penanggung Jawab Mata Kuliah

**Diperiksa oleh:**  
Koordinator/Penelaah Bidang Teknik Sipil

**Disetujui oleh:**  
Kolonel Czi (K) Dr. Hidayati, S.T., S.H., M.T., M.H.  
Ketua Program Studi Teknik Sipil

**Tanggal:** ____________________

---

*Draft RPS ini disusun berdasarkan prinsip Outcome-Based Education (OBE), RPS lama sebagai acuan historis, arahan pembelajaran Python untuk mahasiswa Teknik Sipil pemula, serta desain UTS/UAS teori dan praktik yang dinilai secara terpisah.*
