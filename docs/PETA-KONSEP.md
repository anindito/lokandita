# Peta Konsep per Pertemuan — LokaKode

> Revisi v2: `VARIABEL_TIPE` diizinkan sejak pertemuan 1 (setiap program yang membaca input harus mendeklarasikan variabel). Aturan deteksi `STRING` ada di `SPEC.md` §9.1.

Diturunkan dari tiga dokumen kurikulum:

| Kode | Mata Kuliah | Semester | Prodi | Bahasa |
|---|---|---|---|---|
| DDP101 | Dasar-Dasar Pemrograman | 1 | Informatika | C++ |
| PBO301 | Pemrograman Berorientasi Obyek | 3 | Informatika | C++ |
| TS-PY | Pemrograman (Python) | 3 | Teknik Sipil | Python |

---

## 1. KATALOG KONSEP

Setiap konsep punya kode, nama, dan daftar bahasa tempat ia berlaku.

### Kelompok DASAR
| Kode | Nama | Bahasa |
|---|---|---|
| `IO_DASAR` | Input/output dasar | c, cpp, python |
| `VARIABEL_TIPE` | Variabel, tipe data, konstanta | c, cpp, python |
| `ARITMETIKA` | Operator aritmetika dan ekspresi | c, cpp, python |
| `KONVERSI_TIPE` | Type casting | c, cpp, python |
| `FORMAT_OUTPUT` | Manipulator/format keluaran | c, cpp, python |
| `STRING` | Operasi string | c, cpp, python |

### Kelompok KONTROL
| Kode | Nama | Bahasa |
|---|---|---|
| `PERCABANGAN` | if, else, switch, ternary, operator logika | c, cpp, python |
| `PERULANGAN` | for, while, do-while | c, cpp, python |
| `NESTED_LOOP` | Perulangan bersarang | c, cpp, python |
| `BREAK_CONTINUE` | Kontrol alur perulangan | c, cpp, python |

### Kelompok DATA
| Kode | Nama | Bahasa |
|---|---|---|
| `ARRAY_1D` | Array satu dimensi | c, cpp |
| `ARRAY_2D` | Array multidimensi | c, cpp |
| `POINTER` | Pointer dan alamat memori | c, cpp |
| `MEMORI_DINAMIS` | new/delete, malloc/free | c, cpp |
| `REFERENSI` | Reference variable | cpp |
| `STRUCT` | Struct, union, typedef, enum | c, cpp |
| `PY_LIST` | List dan slicing | python |
| `PY_DICT_SET` | Dictionary, tuple, set | python |
| `PY_COMPREHENSION` | List/dict comprehension | python |

### Kelompok FUNGSI
| Kode | Nama | Bahasa |
|---|---|---|
| `FUNGSI` | Definisi dan pemanggilan fungsi | c, cpp, python |
| `PARAMETER_REF` | Parameter by-reference/by-pointer | c, cpp |
| `OVERLOAD_FUNGSI` | Function overloading, default parameter | cpp |
| `REKURSI` | Fungsi rekursif | c, cpp, python |

### Kelompok LANJUT
| Kode | Nama | Bahasa |
|---|---|---|
| `FILE_IO` | Baca/tulis berkas | c, cpp, python |
| `EXCEPTION` | try-catch, penanganan galat | cpp, python |
| `MODUL` | Modularisasi, import, header | c, cpp, python |

### Kelompok OOP
| Kode | Nama | Bahasa |
|---|---|---|
| `MODERN_CPP` | auto, range-for, nullptr, uniform init | cpp |
| `CLASS_OBJECT` | Class dan object | cpp, python |
| `CONSTRUCTOR_DESTRUCTOR` | Konstruktor dan destruktor | cpp, python |
| `COPY_SEMANTICS` | Copy constructor, assignment, move | cpp |
| `ENCAPSULATION` | Access modifier, getter/setter | cpp, python |
| `KOMPOSISI_AGREGASI` | Relasi antar objek | cpp, python |
| `INHERITANCE` | Pewarisan | cpp, python |
| `POLYMORPHISM` | Polimorfisme, virtual function, override | cpp, python |
| `ABSTRACT_CLASS` | Kelas abstrak, pure virtual | cpp, python |
| `INTERFACE` | Interface, desain polimorfik | cpp, python |
| `OPERATOR_OVERLOAD` | Operator overloading | cpp, python |
| `TEMPLATE_FUNGSI` | Function template | cpp |
| `TEMPLATE_CLASS` | Class template, specialization | cpp |
| `STL_CONTAINER` | vector, map, set, dll. | cpp |
| `STL_ALGORITMA` | sort, find, accumulate, dll. | cpp |
| `SMART_POINTER` | unique_ptr, shared_ptr | cpp |

### Kelompok PUSTAKA (Python)
| Kode | Nama | Bahasa |
|---|---|---|
| `PY_NUMPY` | NumPy array dan operasi numerik | python |
| `PY_MATPLOTLIB` | Visualisasi grafik | python |
| `PY_TKINTER` | GUI desktop | python |

---

## 2. PEMETAAN — DDP101 (C++, Semester 1)

Kolom "Tambahan" berisi konsep **baru** di pertemuan itu. Konsep bersifat kumulatif.

| Pert. | Topik | Tambahan | Dapat dinilai otomatis |
|:---:|---|---|:---:|
| 1 | Pengenalan Pemrograman dan C++ | `IO_DASAR`, `ARITMETIKA`, `VARIABEL_TIPE` | ya |
| 2 | Tipe Data, Variabel, Operator | `KONVERSI_TIPE` | ya |
| 3 | Input dan Output | `FORMAT_OUTPUT`, `STRING` | ya |
| 4 | Struktur Kontrol — Percabangan | `PERCABANGAN` | ya |
| 5 | Struktur Kontrol — Perulangan | `PERULANGAN`, `NESTED_LOOP`, `BREAK_CONTINUE` | ya |
| 6 | Fungsi dan Parameter Passing | `FUNGSI`, `PARAMETER_REF`, `REFERENSI` | ya |
| 7 | Fungsi Lanjutan, Rekursi, Debugging | `REKURSI`, `OVERLOAD_FUNGSI` | ya |
| 8 | **UTS** | — | — |
| 9 | Array 1D | `ARRAY_1D` | ya |
| 10 | Array Multidimensi | `ARRAY_2D` | ya |
| 11 | Pointer dan Memori Dinamis | `POINTER`, `MEMORI_DINAMIS` | ya |
| 12 | Struct, Union, Typedef | `STRUCT` | ya |
| 13 | Pengenalan OOP | `CLASS_OBJECT`, `ENCAPSULATION` | sebagian |
| 14 | File Handling dan Exception | `FILE_IO`, `EXCEPTION` | sebagian |
| 15 | C++ di Platform Lain, Tools | — | tidak |
| 16 | **UAS** | — | — |

**Catatan pertemuan 1–12:** cocok sepenuhnya untuk LokaKode. Ini adalah mata kuliah dengan kesesuaian terbaik dari ketiganya.

**Pertemuan 14:** soal `FILE_IO` tidak bisa dinilai lewat stdin/stdout apa adanya. Sandbox melarang penulisan di luar `/box`. Solusinya: soal menulis ke berkas di `/box` lalu membacanya kembali dan mencetak isinya ke stdout.

---

## 3. PEMETAAN — PBO301 (C++, Semester 3)

Baseline pertemuan 1 = **seluruh konsep DDP101** (prasyarat DDP + SDA).

| Pert. | Topik | Tambahan | Dapat dinilai otomatis |
|:---:|---|---|:---:|
| 1 | Pengantar OOP, C++ Modern, UML | `MODERN_CPP` | sebagian (UML tidak) |
| 2 | Class, Object, Class Diagram | `CLASS_OBJECT` | sebagian |
| 3 | Constructor, Destructor, Copy Semantics | `CONSTRUCTOR_DESTRUCTOR`, `COPY_SEMANTICS` | sebagian |
| 4 | Encapsulation dan Access Control | `ENCAPSULATION` | sebagian |
| 5 | Relasi Antar Obyek, Object Diagram | `KOMPOSISI_AGREGASI` | sebagian |
| 6 | Inheritance | `INHERITANCE` | sebagian |
| 7 | Polymorphism dan Virtual Functions | `POLYMORPHISM` | sebagian |
| 8 | **UTS** | — | — |
| 9 | Abstraction 1 — Abstract Class | `ABSTRACT_CLASS` | sebagian |
| 10 | Abstraction 2 — Interface | `INTERFACE` | sebagian |
| 11 | Operator Overloading | `OPERATOR_OVERLOAD` | sebagian |
| 12 | Templates 1 | `TEMPLATE_FUNGSI`, `TEMPLATE_CLASS` | sebagian |
| 13 | Templates 2 — Specialization dan STL | `STL_CONTAINER`, `STL_ALGORITMA`, `SMART_POINTER` | sebagian |
| 14 | Exception Handling Lanjut | `EXCEPTION` | sebagian |
| 15 | Sequence Diagram, Use Case, Studi Kasus | — | tidak |
| 16 | **UAS** | — | — |

**"Sebagian" di sini berarti masalah serius, bukan keterbatasan kecil.** Lihat §5.

---

## 4. PEMETAAN — TS-PY (Python, Semester 3, Teknik Sipil)

| Pert. | Topik | Tambahan | Dapat dinilai otomatis |
|:---:|---|---|:---:|
| 1 | Pengenalan Pemrograman, Algoritma, Python | `IO_DASAR`, `ARITMETIKA`, `VARIABEL_TIPE` | ya |
| 2 | Variabel, Tipe Data, Operasi Matematika, Satuan | `KONVERSI_TIPE`, `FORMAT_OUTPUT` | ya |
| 3 | Logika dan Percabangan | `PERCABANGAN` | ya |
| 4 | Perulangan | `PERULANGAN`, `NESTED_LOOP`, `BREAK_CONTINUE` | ya |
| 5 | Struktur Data dan Fungsi | `PY_LIST`, `PY_DICT_SET`, `FUNGSI`, `STRING` | ya |
| 6 | Pengolahan Data dan Visualisasi Awal | `FILE_IO`, `PY_COMPREHENSION`, `PY_MATPLOTLIB` | sebagian |
| 7 | **UTS Teori + Praktik** | — | — |
| 8 | NumPy dan Komputasi Numerik | `PY_NUMPY` | ya |
| 9 | Pengantar Metode Numerik | — | ya |
| 10 | Penerapan, Desain Program, Pengujian | `MODUL`, `EXCEPTION` | ya |
| 11 | GUI Desktop I — Tkinter | `PY_TKINTER` | **tidak** |
| 12 | GUI Desktop II — Aplikasi Teknik Sipil | `PY_TKINTER` | **tidak** |
| 13 | Integrasi dan Capstone | — | **tidak** |
| 14 | Validasi Model dan Klinik Capstone | — | **tidak** |
| 15 | Finalisasi dan Presentasi | — | **tidak** |
| 16 | **UAS Teori + Capstone** | — | **tidak** |

**Pertemuan 1–10 cocok untuk LokaKode. Pertemuan 11–16 tidak.**

Dua hal yang perlu disiapkan bila mata kuliah ini dijalankan di LokaKode:
- Sandbox `judge-python` saat ini **tanpa paket pihak ketiga**. NumPy dan Matplotlib harus ditambahkan ke image untuk pertemuan 6 dan 8–10, dan itu menambah ukuran image serta permukaan serangan. Perlu whitelist paket yang eksplisit.
- Matplotlib tidak boleh mencoba membuka jendela. Paksa backend `Agg` di image, dan nilai soal dari data numeriknya, bukan dari gambarnya.

---

## 5. TEMUAN PENTING: PENILAIAN OUTPUT TIDAK CUKUP UNTUK PBO

LokaKode menilai dengan membandingkan stdout terhadap solusi referensi. Untuk DDP101 ini memadai — kalau keluarannya benar, soalnya selesai.

Untuk PBO301 ini **tidak memadai sama sekali**. Contoh: soal "buat class `Segitiga` dengan enkapsulasi dan hitung luasnya". Mahasiswa yang menulis satu fungsi prosedural tanpa class apa pun akan menghasilkan keluaran yang identik dan mendapat nilai penuh. Seluruh tujuan pembelajaran mata kuliah itu lolos tanpa diuji.

Hal yang sama berlaku untuk `INHERITANCE`, `POLYMORPHISM`, `ABSTRACT_CLASS`, `OPERATOR_OVERLOAD`, dan `TEMPLATE_*`. Justru semua pilar OOP.

**Solusi: validasi AST terhadap kode mahasiswa, bukan hanya solusi referensi.**

Tambahkan field `requiredConceptsInSubmission` pada Problem:
- Soal PBO pertemuan 6 dapat mensyaratkan `INHERITANCE` benar-benar muncul di kode mahasiswa.
- Verdict baru: `CONCEPT_NOT_MET` — kode berjalan benar tetapi tidak memakai konsep yang disyaratkan.
- Pesan ke mahasiswa harus spesifik: "Keluaran benar, tetapi soal ini mensyaratkan pewarisan kelas."
- Jangan dijadikan skor 0 penuh: beri skor parsial (misalnya 50%) agar mahasiswa tahu logikanya sudah benar dan hanya pendekatannya yang keliru.

Ini kebalikan dari pembatasan konsep: yang satu **melarang** konstruksi yang belum diajarkan, yang lain **mewajibkan** konstruksi yang sedang diajarkan. Keduanya memakai parser yang sama.

---

## 6. RINGKASAN KESESUAIAN

| Mata Kuliah | Pertemuan cocok | Catatan |
|---|---|---|
| DDP101 | 1–14 dari 16 | Kesesuaian terbaik. Bisa dipakai penuh. |
| PBO301 | 2–14, **butuh validasi AST kode mahasiswa** | Tanpa itu, penilaian OOP tidak bermakna. |
| TS-PY | 1–10 dari 16 | Butuh NumPy/Matplotlib di image. GUI dan capstone di luar jangkauan. |

Saran urutan pemakaian: **mulai dari DDP101**. Mata kuliah itu tidak butuh fitur tambahan apa pun, sehingga dapat dipakai semester ini juga sementara fitur untuk PBO dan Python disiapkan.
