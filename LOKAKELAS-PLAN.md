# LokaKelas — perencanaan

Ruang kelas daring Lokandita: **materi, tugas, pengumuman, dan presensi** per kelas mata kuliah. Ditulis agar bisa diserahkan ke chat baru yang belum melihat percakapan sebelumnya.

Setiap butir ditandai **Ditetapkan** (keputusan pemilik) atau **Usulan** (saran Claude, belum diterima). Disusun 2026-11-27.

**Revisi 2026-11-27:** presensi hanya manual oleh dosen, tanpa QR dan tanpa pengajuan izin oleh mahasiswa (§2.5); urutan pembangunan materi → tugas → pengumuman → presensi; batas berkas materi 50 MB dan kiriman 20 MB, dengan tautan sebagai pengganti berkas yang lebih besar (§2.2, §2.3); LokaKelas tidak berkaitan dengan SIAKAD atau sistem resmi kampus (§6). Keputusan L1, L2, L5 dan urutan ditetapkan (§7).

**Revisi 2026-11-27 (2):** pemberitahuan email ditiadakan (L4); tugas kelompok ditambahkan, kelompok bisa dibentuk mahasiswa atau dosen (§2.3a, L6).

---

## 1. Ringkasan

| Hal | Isi |
|---|---|
| Nama | **LokaKelas** (Ditetapkan, `LOKANDITA-BRAND.md`) |
| Cakupan | Materi, tugas, pengumuman, presensi (Ditetapkan 2026-11-27) |
| Pengguna | Semua kelas di roster Lokandita, termasuk mahasiswa prodi lain (mis. Teknik Sipil di TS-PY). Berbeda dengan LokaSiswa yang hanya Prodi Informatika (Usulan) |
| Bukan cakupan | Ujian dan kuis (LokaUjian), latihan kode (LokaKode), nilai akhir mata kuliah, forum diskusi, video konferensi, cek plagiarisme, pembuatan materi dengan AI di dalam aplikasi (Usulan) |
| Host | `lokakelas.lokandita.com` menurut pola (Usulan), di VPS Contabo yang ada |
| Stack | Sama dengan Lokandita v2: CodeIgniter 4, MySQL 8.4, Redis, Tailwind + Alpine.js, login lewat Akun Lokandita, tiga bahasa, mode gelap (Usulan) |
| Urutan | Dimulai setelah LokaUjian dan **setelah roster pindah ke inti Lokandita** (tahap A1 dan A2 di `LOKAUJIAN-START.md`) (Usulan) |

**Catatan aturan penamaan.** `LOKANDITA-BRAND.md` mengusulkan aplikasi Loka baru hanya bila butuh mesin sendiri. LokaKelas tidak butuh mesin, tetapi sudah ditetapkan sebagai aplikasi fungsi yang umum lintas mata kuliah, sejajar dengan LokaUjian. Dengan cakupan ini, butir terbuka no. 2 di `LOKANDITA-BRAND.md` terjawab: pengumuman dan pengumpulan tugas masuk; rekap nilai lintas aplikasi tetap di luar.

---

## 2. Fitur

### 2.1 Struktur kelas (Usulan)

- **Pertemuan** menjadi tulang punggung: setiap mata kuliah punya daftar pertemuan 1–16 (UTS di pertemuan 8, UAS di 16 sebagai bawaan, bisa diubah). Materi, tugas, dan presensi menempel ke pertemuan.
- **Tingkat mata kuliah dan tingkat kelas.** Pengampu menyusun materi dan tugas sekali di tingkat mata kuliah, lalu berlaku untuk semua kelasnya. Pengajar bisa menambah butir khusus untuk kelasnya sendiri dan mengatur tanggal per kelas (kelas A pertemuan 3 hari Senin, kelas B hari Rabu).
- **Salin dari semester lalu.** Satu mata kuliah bisa disalin dari tahun akademik sebelumnya: struktur pertemuan, materi, dan tugas tanpa kiriman mahasiswa, presensi, atau pengumuman.

### 2.2 Materi

| Hal | Usulan |
|---|---|
| Jenis | Berkas (PDF, PPTX, DOCX, XLSX, ZIP, gambar), tautan (YouTube, Google Drive, LokaKode, LokaUjian), teks singkat bermarkdown |
| Terbit | Langsung, terjadwal (tanggal dan jam), atau draf |
| Versi | Mengganti berkas menyimpan versi lama; mahasiswa melihat yang terbaru |
| Pratinjau | PDF dan gambar tampil di peramban; jenis lain diunduh |
| Jejak | Siapa sudah membuka atau mengunduh, untuk dosen (ringkas: X dari Y mahasiswa) |
| Batas | 50 MB per berkas materi (Ditetapkan). Materi yang lebih besar (video, dataset) dibagikan sebagai tautan (Ditetapkan) |

### 2.3 Tugas

| Hal | Usulan |
|---|---|
| Bentuk kiriman | Unggah berkas (satu atau beberapa), jawaban teks, atau keduanya, diatur per tugas |
| Batas berkas | 20 MB per berkas kiriman (Ditetapkan). Bila lebih besar, mahasiswa bisa mengirim **tautan** (mis. Google Drive, GitHub) sebagai pengganti (Ditetapkan). Tautan disimpan apa adanya dan diberi cap waktu server; isi di balik tautan bisa berubah setelah tenggat, jadi dosen menilai dengan pengetahuan itu (Usulan) |
| Tenggat | Waktu server, bukan peramban. Tenggat per kelas bisa berbeda |
| Terlambat | Per tugas: ditolak, atau diterima dengan tanda **Terlambat** sampai batas akhir tertentu |
| Perpanjangan | Per mahasiswa, oleh pengajar, tercatat di log audit |
| Kiriman ulang | Boleh sampai tenggat; versi sebelumnya disimpan, yang dinilai versi terakhir |
| Penilaian | Skor angka (skala per tugas) dan umpan balik teks, opsional berkas umpan balik. Rubrik di tahap awal tidak ada (L7) |
| Rilis nilai | Disembunyikan sampai pengajar merilisnya, per tugas |
| Ekspor | CSV nilai per kelas (UTF-8 dengan BOM, seperti LokaUjian) dan ZIP semua kiriman, berkas dinamai `NIM_Nama_namaasli` |
| Individu atau kelompok | Diatur per tugas (Ditetapkan); rincian kelompok di §2.3a |
| Plagiarisme | Tidak ada, konsisten dengan LokaUjian |

### 2.3a Tugas kelompok

**Ditetapkan:** ada tugas kelompok; kelompok bisa dibentuk oleh mahasiswa atau oleh dosen. Rinciannya Usulan.

| Hal | Usulan |
|---|---|
| Set kelompok | Kelompok disimpan sebagai **set** per kelas (mis. "Kelompok proyek akhir"), sehingga satu set bisa dipakai beberapa tugas. Tugas kelompok memilih satu set |
| Dibentuk dosen | Dosen membuat kelompok dan memasukkan anggota, atau memakai **acak otomatis** dengan ukuran kelompok tertentu, lalu menyesuaikan |
| Dibentuk mahasiswa | Dosen membuka pendaftaran: ukuran minimum dan maksimum, jumlah kelompok maksimum, dan **batas waktu pembentukan**. Mahasiswa membuat kelompok atau bergabung ke kelompok yang belum penuh, dan bisa keluar sebelum batas waktu. Setelah batas waktu set **dikunci** |
| Setelah dikunci | Hanya dosen yang bisa memindah anggota. Mahasiswa yang belum punya kelompok tampil di daftar "belum berkelompok" untuk ditempatkan dosen, atau dimasukkan acak dengan satu tombol |
| Ketua | Opsional; dipilih dosen atau anggota. Tanpa hak khusus selain label |
| Kiriman | Satu kiriman per kelompok. Anggota mana pun bisa mengirim atau mengganti sebelum tenggat; semua anggota melihat kiriman yang sama; tercatat siapa yang mengirim setiap versi |
| Tenggat dan perpanjangan | Berlaku per kelompok |
| Nilai | Satu nilai kelompok yang otomatis turun ke setiap anggota; dosen bisa **menyesuaikan nilai per anggota** (mis. anggota yang tidak berkontribusi), dengan alasan tercatat |
| Perubahan roster | Mahasiswa yang pindah kelas atau keluar dari roster tetap tercatat di kelompok pada tugas yang sudah dikirim; untuk tugas berikutnya ia dikeluarkan dari set |
| Ekspor | CSV nilai memuat nama kelompok; ZIP dinamai `NamaKelompok_namaasli` |
| Penilaian sejawat | Tidak di tahap awal |

### 2.4 Pengumuman

| Hal | Usulan |
|---|---|
| Sasaran | Satu kelas, semua kelas satu mata kuliah, atau satu pertemuan |
| Isi | Teks bermarkdown dengan lampiran opsional |
| Terbit | Langsung atau terjadwal; bisa disematkan di atas |
| Pemberitahuan | Lencana di aplikasi dan di portal Lokandita. **Tanpa email** (Ditetapkan) |
| Dibaca | Dosen melihat berapa mahasiswa sudah membaca |

### 2.5 Presensi

| Hal | Usulan |
|---|---|
| Cara | **Manual oleh dosen** (Ditetapkan). Tidak ada QR, tidak ada presensi mandiri oleh mahasiswa |
| Status | Hadir, Terlambat, Izin, Sakit, Alpa (Usulan) |
| Pengisian | Satu lembar per kelas per pertemuan berisi daftar mahasiswa dari roster. Tombol "Tandai semua Hadir", lalu dosen mengubah yang tidak hadir; keterangan singkat opsional per mahasiswa (Usulan) |
| Izin/Sakit | Dicatat dosen, bukan diajukan mahasiswa lewat aplikasi (Ditetapkan, mengikuti presensi manual). **Tidak ada unggah surat dokter** (§5) |
| Koreksi | Pengajar bisa mengubah sampai batas tertentu (mis. 7 hari), setelah itu hanya pengampu; semua perubahan tercatat (Usulan) |
| Rekap | Per mahasiswa dan per kelas, dengan persentase kehadiran dan tanda bila di bawah ambang yang diatur per mata kuliah (Usulan) |
| Mahasiswa | Melihat rekap presensinya sendiri (Usulan, L9) |
| Ekspor | CSV rekap presensi per kelas (Usulan) |

---

## 3. Peran dan akses (Usulan)

Mengikuti pola LokaKode dan LokaUjian.

| Peran | Bisa |
|---|---|
| Superadmin | Semua, termasuk pengaturan dan batas penyimpanan |
| Pengampu | Menyusun materi, tugas, dan struktur pertemuan untuk mata kuliahnya; melihat semua kelas mata kuliah itu |
| Pengajar | Untuk kelasnya sendiri: menambah materi atau tugas khusus kelas, mengatur tanggal, membentuk dan mengunci kelompok, menilai, menjalankan presensi, mengumumkan |
| Mahasiswa | Melihat kelasnya, mengunduh materi, mengirim tugas, membuat atau bergabung ke kelompok saat pendaftaran dibuka, melihat rekap presensinya sendiri |

Aturan pendukung:

- Setiap titik akhir diuji lintas kelas: mengganti ID kelas, tugas, atau kiriman di URL memberi *Halaman tidak ditemukan*.
- Mahasiswa tidak pernah melihat kiriman mahasiswa lain, kecuali kiriman kelompoknya sendiri.
- Mahasiswa hanya bisa bergabung ke kelompok di kelasnya sendiri; kelompok kelas lain tidak terlihat.
- Mahasiswa yang dipindah kelas membawa kiriman dan presensinya; yang dikeluarkan dari roster kehilangan akses tetapi datanya tetap.
- Log audit untuk perubahan nilai, perpanjangan, koreksi presensi, dan ekspor.

---

## 4. Hubungan dengan platform Lokandita (Usulan)

| Topik | Pendekatan |
|---|---|
| Lokasi kode | `apps/lokakelas` di monorepo yang sama |
| Basis data | Skema `lokakelas` dengan pengguna MySQL sendiri |
| Roster | Dibaca dari inti Lokandita lewat view baca-saja dan pengguna MySQL baca-saja, seperti LokaUjian (D1). Tidak disalin, tidak ditulis |
| Pertemuan | LokaKode sudah punya data pertemuan sendiri. Tahap awal: LokaKelas memegang daftar pertemuannya sendiri dan menautkan ke LokaKode/LokaUjian lewat tautan. Menyatukan definisi pertemuan di inti adalah keputusan terpisah (L8) |
| Unggah gambar | Jalur unggah aman yang sudah ada (tipe dari isi, encode ulang, metadata dibuang) |
| Portal | Kartu "Kelas saya" dan lencana pengumuman/tugas di `lokandita.com` |
| Yang dibagikan ke aplikasi lain | Tidak ada di tahap awal. Rekap presensi kelak bisa menjadi view baca-saja |

---

## 5. Data dan keamanan (Usulan)

- **Berkas** disimpan di luar webroot dengan nama acak, dilayani lewat pengendali yang memeriksa hak, dengan `Content-Disposition: attachment` untuk semua jenis kecuali PDF dan gambar, dan tidak pernah dieksekusi. Volume berkas tidak dipasang ke kontainer lain, terutama judge.
- **Tipe berkas** ditentukan dari isi, bukan nama; daftar putih per tugas (mis. hanya PDF).
- **Pemindai malware** (ClamAV) untuk berkas kiriman: berguna, tetapi butuh sekitar 1 GB RAM di server 8 GB yang sudah memikul LokaKode, judge, dan LokaUjian (L3).
- **Presensi Sakit tanpa surat dokter dan tanpa diagnosis.** Surat dokter adalah data kesehatan dan menambah beban PDP. Keterangan presensi cukup "Sakit"; bila prodi mewajibkan surat, diserahkan di luar LokaKelas.
- **Kiriman tugas dan presensi adalah data pribadi** dan bisa menjadi catatan penilaian. Masa retensi ditetapkan sebelum pemakaian pertama dan dimuat di teks privasi.
- **Tidak ada penghapusan permanen** materi, kiriman, presensi, atau log; yang dihapus dari tampilan menjadi arsip.
- **Cadangan** mencakup berkas, bukan hanya basis data, dan diuji pemulihannya.

### 5a. Kapasitas penyimpanan

Ini risiko utama LokaKelas di VPS yang ada. Perkiraan kasar satu semester: 235 mahasiswa × ±10 tugas × ±5 MB ≈ **12 GB kiriman**, ditambah materi dan versi lama, dan semuanya ikut cadangan. Usulan:

- Cek ruang disk dan ruang cadangan sebelum K0.
- Batas per berkas (L5) dan kuota per mata kuliah, dengan peringatan di dasbor superadmin saat 80%.
- Arsip tahun akademik lama dipindah ke penyimpanan dingin (mis. object storage) bila disk menipis, tanpa mengubah kode (jalur penyimpanan lewat satu antarmuka).

---

## 6. Prasyarat non-teknis

| # | Hal | Mengapa |
|---|---|---|
| P1 | **Hubungan dengan sistem resmi kampus — Ditetapkan: tidak berkaitan.** LokaKelas berdiri sendiri; tidak ada sinkronisasi atau format khusus untuk SIAKAD | — |
| P2 | **Ambang kehadiran** dan aturan Izin/Sakit yang berlaku di prodi | Menentukan rekap dan tanda peringatan |
| P3 | **Masa retensi** kiriman tugas dan presensi | Teks privasi dan rencana penyimpanan |
| P4 | Roster sudah di inti Lokandita (A1 dan A2 selesai) | LokaKelas membaca roster dari sana sejak awal, sehingga tidak perlu memindah view lagi |

---

## 7. Keputusan terbuka

| # | Keputusan | Usulan bawaan |
|---|---|---|
| L0 | Urutan pembangunan fitur | **Ditetapkan:** materi → tugas → pengumuman → presensi |
| L1 | Cara presensi | **Ditetapkan:** manual oleh dosen |
| L2 | Pengaman presensi QR | **Gugur:** tidak ada QR |
| L3 | Pemindai malware untuk kiriman | Tidak di tahap awal; berkas tidak pernah dieksekusi dan diunduh sebagai lampiran |
| L4 | Pemberitahuan email | **Ditetapkan:** tidak ada; hanya lencana di aplikasi dan portal |
| L5 | Batas ukuran berkas | **Ditetapkan:** materi 50 MB, kiriman 20 MB per berkas; yang lebih besar diganti tautan |
| L6 | Tugas kelompok | **Ditetapkan:** ada; kelompok dibentuk mahasiswa atau dosen (§2.3a) |
| L7 | Rubrik penilaian | Tidak di tahap awal; skor + umpan balik |
| L8 | Satu definisi pertemuan untuk semua aplikasi | Tidak di tahap awal; LokaKelas memegang daftarnya sendiri |
| L9 | Mahasiswa melihat rekap presensinya sendiri | Ya |
| L10 | Tampilan ponsel untuk mahasiswa | Ya, setara dengan desktop; tampilan dosen mengutamakan desktop, tetapi lembar presensi nyaman di ponsel |

---

## 8. Model data awal (Usulan)

```
pertemuan            id, course_id, class_id (boleh kosong = tingkat mata kuliah), nomor,
                     judul, jenis (BIASA|UTS|UAS), tanggal_per_kelas → tabel jadwal_pertemuan
jadwal_pertemuan     pertemuan_id, class_id, mulai, selesai, ruang
materi               id, course_id, class_id?, pertemuan_id?, jenis (BERKAS|TAUTAN|TEKS),
                     judul, isi, terbit_pada, status (DRAF|TERBIT|ARSIP), dibuat_oleh
materi_versi         materi_id, versi, dokumen_id
tugas                id, course_id, class_id?, pertemuan_id?, judul, instruksi, bentuk
                     (BERKAS|TEKS|KEDUANYA), tipe_berkas_diizinkan, skor_maks,
                     kebijakan_terlambat, rilis_nilai, mode (INDIVIDU|KELOMPOK), set_kelompok_id?
set_kelompok         id, class_id, nama, pembentuk (DOSEN|MAHASISWA), ukuran_min, ukuran_maks,
                     jumlah_maks, batas_pembentukan, dikunci_pada, dibuat_oleh
kelompok             id, set_id, nama, ketua_user_id?, dibuat_oleh
anggota_kelompok     kelompok_id, user_id, bergabung_pada, keluar_pada?   ← riwayat, tidak ditimpa
tugas_jadwal         tugas_id, class_id, buka, tenggat, batas_akhir_terlambat
perpanjangan         tugas_id, user_id? | kelompok_id?, tenggat_baru, alasan, diberi_oleh
kiriman              id, tugas_id, user_id? | kelompok_id?, versi, dikirim_oleh, teks, tautan?,
                     dikirim_pada, terlambat (turunan)
kiriman_berkas       kiriman_id, dokumen_id
penilaian            tugas_id, user_id? | kelompok_id?, kiriman_id, skor, umpan_balik,
                     dokumen_id?, dinilai_oleh
penyesuaian_nilai    tugas_id, kelompok_id, user_id, skor_akhir, alasan, oleh
pengumuman           id, sasaran (KELAS|MATA_KULIAH|PERTEMUAN), sasaran_id, judul, isi,
                     disematkan, terbit_pada, dibuat_oleh
pengumuman_baca      pengumuman_id, user_id, dibaca_pada
lembar_presensi      id, class_id, pertemuan_id, tanggal, dibuat_oleh, dikunci_pada
presensi             lembar_id, user_id, status (HADIR|TERLAMBAT|IZIN|SAKIT|ALPA),
                     keterangan, dicatat_pada, dicatat_oleh
dokumen              id, path_acak, nama_asli, sha256, mime, ukuran, diunggah_oleh
akses_materi         materi_id, user_id, pertama_dibuka, terakhir_dibuka
log_audit            siapa, apa, kapan, sebelum, sesudah
```

`course_id`, `class_id`, dan `user_id` merujuk ke roster di inti Lokandita lewat view; tidak ada kunci asing lintas skema.

---

## 9. Layar utama (Usulan)

**Dosen**
1. **Beranda kelas**: pertemuan minggu ini, tugas yang menunggu dinilai, pengumuman terakhir, tombol "Buka presensi".
2. **Susun mata kuliah**: daftar pertemuan 1–16 dengan materi dan tugas di tiap pertemuan; salin dari semester lalu.
3. **Penilaian tugas**: daftar kiriman dengan status (belum, terkirim, terlambat, dinilai), panel nilai dan umpan balik, unduh ZIP, ekspor CSV.
4. **Kelompok**: buat set, bentuk manual atau acak, buka pendaftaran mahasiswa, daftar "belum berkelompok", kunci.
5. **Presensi**: lembar per pertemuan dengan "Tandai semua Hadir", ubah per mahasiswa, rekap kelas.
6. **Pengumuman**: tulis, jadwalkan, sematkan, lihat jumlah pembaca.

**Mahasiswa**
1. **Kelas saya**: daftar kelas dengan lencana tugas dan pengumuman baru.
2. **Halaman kelas**: linimasa pertemuan, materi, tugas dengan tenggat, pengumuman.
3. **Kirim tugas**: unggah, tulis jawaban, atau kirim tautan; lihat status dan nilai bila dirilis.
4. **Kelompok saya**: buat atau gabung kelompok selama pendaftaran dibuka, lihat anggota.
5. **Presensi**: lihat rekap sendiri.

---

## 10. Tahapan

Satu tahap per sesi Claude Code, dengan gerbang manual oleh pemilik, sama seperti LokaUjian.

| # | Tahap | Gerbang |
|---|---|---|
| K0 | **Prasyarat** P2–P4, keputusan sisa (L3, L7–L10), cek ruang disk dan cadangan | Roster di inti (A2 ditandai); keputusan tercatat; perkiraan penyimpanan cukup |
| K1 | **Kontrak dan kerangka.** Tambahan `KONTRAK.md` (host, skema, pengguna MySQL, view roster, variabel lingkungan, volume berkas), kerangka `apps/lokakelas` di balik halaman pemeliharaan, masuk Caddy, deploy, smoke, dan cadangan | Halaman pemeliharaan tampil lewat HTTPS; smoke test lulus dengan cek baru; volume berkas tidak terlihat dari kontainer judge |
| K2 | **Akses dan struktur.** Login, peran dan cakupan kelas dari roster, pertemuan dan jadwal per kelas, salin dari semester lalu | Pengajar hanya melihat kelasnya; ID kelas lain di URL ditolak; mahasiswa hanya melihat kelas yang ia ikuti |
| K3 | **Materi.** Unggah aman, tautan, teks, terbit terjadwal, versi, pratinjau, jejak akses | Pemilik mengunggah PDF, PPTX, dan tautan; materi terjadwal muncul tepat waktu; berkas tidak bisa dibuka lewat URL langsung tanpa hak |
| K4 | **Tugas.** Tenggat per kelas, terlambat, perpanjangan, kiriman ulang, kiriman berupa tautan, penilaian, rilis nilai, ZIP, CSV | Mahasiswa uji mengirim sebelum dan sesudah tenggat dengan hasil sesuai kebijakan; berkas 21 MB ditolak dengan saran mengirim tautan; nilai tidak terlihat sebelum dirilis; CSV terbuka benar di Excel dan memuat tautan; ZIP berisi nama berkas yang benar |
| K4b | **Tugas kelompok.** Set kelompok, pembentukan oleh dosen (manual dan acak) dan oleh mahasiswa (pendaftaran, batas waktu, kunci), satu kiriman per kelompok, nilai kelompok dan penyesuaian per anggota | Tiga mahasiswa uji membentuk kelompok sendiri; anggota keempat ditolak saat ukuran maksimum 3; setelah dikunci mahasiswa tidak bisa keluar; satu anggota mengirim dan anggota lain melihat kiriman yang sama; mahasiswa di luar kelompok tidak bisa membukanya; penyesuaian nilai satu anggota tercatat dengan alasan |
| K5 | **Pengumuman.** Sasaran, jadwal, sematkan, lencana di aplikasi dan portal, jumlah pembaca | Pengumuman ke satu kelas tidak terlihat oleh kelas lain; lencana hilang setelah dibaca |
| K6 | **Presensi.** Lembar manual per pertemuan, "Tandai semua Hadir", koreksi tercatat, rekap, ambang, ekspor | Dosen mengisi satu pertemuan di ponsel dalam waktu singkat; pengajar lain tidak bisa membuka lembar kelas ini; koreksi setelah batas hanya oleh pengampu dan tercatat; rekap cocok dengan hitungan manual |
| K7 | **Kesiapan.** Uji akses lintas kelas menyeluruh, uji beban unggah menjelang tenggat, pemulihan cadangan termasuk berkas, tinjauan tiga bahasa dan tampilan ponsel, halaman pemeliharaan dimatikan | Semua uji lulus; satu kelas memakai LokaKelas satu pertemuan penuh sebelum semua kelas |

Urutan K3–K6 ditetapkan: materi, tugas (K4 individu, K4b kelompok), pengumuman, presensi.

---

## 11. Hal yang perlu diperhatikan

- **Menjelang tenggat semua mengunggah bersamaan.** Seperti ujian di LokaUjian, beban berkumpul di menit-menit terakhir. Batas ukuran berkas dan uji beban di K7 ada untuk itu; hindari tenggat tugas besar bersamaan dengan sesi LokaKode atau ujian.
- **Penyimpanan tumbuh setiap semester** dan tidak pernah dihapus. Pantau dari awal (§5a).
- **Presensi menjadi bahan sengketa.** Setiap koreksi harus tercatat siapa, kapan, dari apa ke apa.
- **Kiriman berupa tautan bisa berubah setelah tenggat.** LokaKelas hanya bisa membuktikan kapan tautan dikirim, bukan isi di baliknya pada saat itu. Untuk tugas yang ketat, dosen bisa mewajibkan tautan yang tidak bisa diubah (mis. tag atau commit GitHub tertentu).
- **Pelajaran dari LokaKode** di `CLAUDE.md` berlaku: uji lulus bukan bukti; deploy hanya lewat `infra/deploy.sh`; satu `.env` per komponen; panggilan antaraplikasi lewat jaringan `internal`; tidak ada penghapusan permanen.
