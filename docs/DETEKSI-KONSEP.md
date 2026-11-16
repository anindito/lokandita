# Aturan Deteksi Konsep — LokaKode

Dokumen ini menjelaskan, untuk dosen, **konstruksi apa yang dihitung sebagai konsep apa**. Katalog konsep ada di `docs/PETA-KONSEP.md`; kodenya di `judge/images/analisis/` (`cfamili.py` untuk C dan C++, `py.py` untuk Python).

Dipakai untuk dua hal (`SPEC.md` §9):

- **Pembatasan** — solusi referensi sebuah soal ditolak bila memakai konsep yang belum diizinkan di pertemuannya.
- **Persyaratan** — kode mahasiswa diperiksa apakah memakai konsep yang disyaratkan soal (mulai tahap 9).

Kode sumber **diurai** dengan parser sungguhan (tree-sitter untuk C/C++, modul `ast` untuk Python). Tidak ada pencocokan teks: kata `if` di dalam komentar atau di dalam `cout << "if"` tidak dihitung. Kode tidak pernah dijalankan saat dianalisis.

Setiap aturan di bawah punya contoh yang diuji otomatis di `judge/tests/korpus/`: tiap berkas menyebut himpunan konsep yang harus ditemukan, tidak kurang dan tidak lebih.

## Keterbatasan yang perlu diketahui

1. **Tidak ada informasi tipe dari kompiler.** Tipe sebuah variabel diketahui hanya dari deklarasinya di berkas yang sama. `s.size()` dihitung `STRING` hanya bila `s` dideklarasikan sebagai `string` atau `char[]`.
2. **Python tidak punya deklarasi.** Jenis sebuah nama ditebak dari penugasan sederhana (`s = "abc"`, `data = []`). Bila tidak diketahui, `x[i]` dan `x[a:b]` dihitung `PY_LIST`.
3. **Konversi implisit tidak terlihat.** `double r = a / 2.0;` bukan `KONVERSI_TIPE`; hanya cast yang ditulis.
4. **Konstruksi di luar katalog tidak dibatasi**, misalnya `goto` atau operator bit.
5. Konsep OOP yang bersifat rancangan (`ENCAPSULATION`, `KOMPOSISI_AGREGASI`, `INTERFACE`) dikenali dari **bentuk** kodenya. Aturannya di bawah; mohon diperiksa apakah sesuai dengan yang Bapak ajarkan.

## Aturan yang sengaja dibuat longgar

Supaya soal pertemuan awal bisa ditulis sama sekali:

| Konstruksi | Dihitung sebagai | Alasan |
|---|---|---|
| `cout << a << " " << b`, `printf("%d %d\n", a, b)`, `print("x", y)` | `IO_DASAR` saja | Mencetak literal bukan operasi string (`SPEC.md` §9.1) |
| `scanf("%d", &x)` | `IO_DASAR` saja | `&` di sini cara membaca, bukan pelajaran pointer |
| `int(input())`, `float(input())`, `input().split()`, `input().strip()`, `map(int, input().split())` | `IO_DASAR` saja | Satu-satunya cara membaca angka di Python |
| `print(f"a = {a}")` | `IO_DASAR` saja | f-string yang langsung dicetak; dengan format (`{a:.2f}`) menjadi `FORMAT_OUTPUT` |
| `import math`, `import sys` | tidak dihitung | Dipakai sejak pertemuan awal |
| `min`, `max`, `swap`, `abs` di C++ | tidak dihitung | Bukan pelajaran algoritma STL |
| `break` penutup `case` | bagian dari `PERCABANGAN` | Bukan `BREAK_CONTINUE` |
| `char nama[20]` | `STRING`, bukan `ARRAY_1D` | `SPEC.md` §9.1 |
| `*p` dan `&x` untuk parameter pointer sebuah fungsi | `PARAMETER_REF`, bukan `POINTER` | Parameter by-pointer diajarkan sebelum pointer |
| `if __name__ == "__main__":` | `MODUL`, bukan `PERCABANGAN` | Idiom modul |

## C dan C++

| Konsep | Dihitung bila ada |
|---|---|
| `IO_DASAR` | `cin`, `cout`, `cerr`, `printf`, `scanf`, `puts`, `putchar`, `getchar` |
| `VARIABEL_TIPE` | deklarasi variabel apa pun |
| `ARITMETIKA` | `+ - * / %`, `++ --`, `+= -= *= /= %=` |
| `KONVERSI_TIPE` | `(int) x`, `int(x)`, `static_cast<…>` dan cast C++ lainnya |
| `FORMAT_OUTPUT` | `setw`, `setprecision`, `setfill`, `fixed`, `left`, `right`, …; `printf` dengan lebar atau presisi (`%.2f`, `%5d`) |
| `STRING` | variabel `string` atau `char[]`/`char*`; `getline`; `length`, `size`, `substr`, `find`, … pada variabel string; indeks pada string; `+` dengan string; `strlen`, `strcpy`, `strcmp`, …; `to_string`, `stoi`, … |
| `PERCABANGAN` | `if`, `else`, `switch`, `? :`, `&&`, `\|\|`, `!` |
| `PERULANGAN` | `for`, `while`, `do … while`, `for (x : v)` |
| `NESTED_LOOP` | perulangan di dalam perulangan |
| `BREAK_CONTINUE` | `break` di dalam perulangan, `continue` |
| `ARRAY_1D` / `ARRAY_2D` | deklarasi `a[n]` / `m[i][j]`; indeks `a[i]` / `m[i][j]`; parameter `int a[]` |
| `POINTER` | deklarasi `T *p`; `*p`; `&x` (kecuali dua pengecualian di atas); `p->x` |
| `MEMORI_DINAMIS` | `new`, `delete`, `malloc`, `calloc`, `realloc`, `free` |
| `REFERENSI` | variabel referensi `int &r = a;` (bukan parameter) |
| `STRUCT` | `struct`, `union`, `enum` dengan isi; `typedef`; `using X = …` |
| `FUNGSI` | fungsi buatan sendiri selain `main` (bukan metode kelas); lambda |
| `PARAMETER_REF` | parameter `T &x` atau `T *x` pada fungsi buatan sendiri |
| `OVERLOAD_FUNGSI` | dua fungsi bebas bernama sama; parameter bawaan (`int b = 1`) |
| `REKURSI` | fungsi yang memanggil dirinya, langsung atau lewat fungsi lain |
| `FILE_IO` | `ifstream`, `ofstream`, `fstream`, `FILE`, `fopen`, `fclose`, `fscanf`, `fprintf` ke berkas |
| `EXCEPTION` | `try`, `throw` |
| `MODUL` | `#include "berkas.h"` (bertanda kutip), `namespace` buatan sendiri, `extern` |
| `MODERN_CPP` | `auto`, `for (x : v)`, `nullptr`, `int z{5}`, lambda |
| `CLASS_OBJECT` | `class`; `struct` yang punya fungsi anggota, hak akses, atau induk |
| `CONSTRUCTOR_DESTRUCTOR` | konstruktor atau destruktor di dalam kelas |
| `COPY_SEMANTICS` | konstruktor salin/pindah (`K(const K&)`, `K(K&&)`), `operator=`, `std::move` |
| `ENCAPSULATION` | bagian `private:` atau `protected:`; anggota di bagian privat bawaan `class` |
| `KOMPOSISI_AGREGASI` | anggota data bertipe kelas lain buatan sendiri (langsung, pointer, atau di dalam kontainer) |
| `INHERITANCE` | `class Anak : public Induk` |
| `POLYMORPHISM` | fungsi anggota `virtual`, `override`, `final` |
| `ABSTRACT_CLASS` | fungsi virtual murni (`= 0`) |
| `INTERFACE` | kelas yang semua metodenya virtual murni dan tanpa anggota data (destruktor boleh ada) |
| `OPERATOR_OVERLOAD` | `operator+`, `operator<<`, … (kecuali `operator=`, yang masuk `COPY_SEMANTICS`) |
| `TEMPLATE_FUNGSI` / `TEMPLATE_CLASS` | `template <…>` di depan fungsi / kelas (termasuk spesialisasi) |
| `STL_CONTAINER` | `vector`, `map`, `set`, `list`, `deque`, `queue`, `stack`, `pair`, `array`, … |
| `STL_ALGORITMA` | `sort`, `find`, `count`, `accumulate`, `reverse`, `max_element`, `binary_search`, … dipanggil sebagai fungsi bebas |
| `SMART_POINTER` | `unique_ptr`, `shared_ptr`, `weak_ptr`, `make_unique`, `make_shared` |

## Python

| Konsep | Dihitung bila ada |
|---|---|
| `IO_DASAR` | `print()`, `input()` dan idiom membaca di atas |
| `VARIABEL_TIPE` | penugasan apa pun |
| `ARITMETIKA` | `+ - * / // % **` dan bentuk `+=`-nya |
| `KONVERSI_TIPE` | `int()`, `float()`, `str()`, `bool()` di luar idiom membaca |
| `FORMAT_OUTPUT` | f-string dengan format (`{x:.2f}`), `"%.2f" % x`, `"{}".format(x)`, `format(x, ".2f")` |
| `STRING` | metode string (`upper`, `split`, `join`, `replace`, …); indeks atau irisan pada string; `+` atau `*` dengan string; f-string yang tidak langsung dicetak |
| `PERCABANGAN` | `if`, `elif`, `else`, `x if c else y`, `and`, `or`, `not`, `match` |
| `PERULANGAN`, `NESTED_LOOP`, `BREAK_CONTINUE` | `for`, `while`; bersarang; `break`, `continue` |
| `PY_LIST` | `[...]`, `list()`, `sorted()`, `append`, `extend`, `insert`, `pop`, `remove`, `sort`, `reverse`; indeks atau irisan pada selain string dan dict |
| `PY_DICT_SET` | `{...}`, `dict()`, `set()`, `tuple()`; `items`, `keys`, `values`, `get`, `add`, …; tuple yang disimpan ke satu nama atau menjadi isi list/dict |
| `PY_COMPREHENSION` | list/dict/set comprehension, generator expression |
| `FUNGSI` | `def` di luar kelas; `lambda` |
| `REKURSI` | fungsi yang memanggil dirinya, langsung atau lewat fungsi lain |
| `FILE_IO` | `open()` |
| `EXCEPTION` | `try`, `raise` |
| `MODUL` | `import` selain `math`, `sys`, `abc`, NumPy, Matplotlib, Tkinter; `if __name__ == "__main__":` |
| `CLASS_OBJECT` | `class` |
| `CONSTRUCTOR_DESTRUCTOR` | `__init__`, `__del__` |
| `ENCAPSULATION` | atribut `self._nama` atau `self.__nama`; `@property` |
| `KOMPOSISI_AGREGASI` | di dalam kelas: `self.x = KelasLain(...)`, atau `self.x = parameter` yang beranotasi kelas lain |
| `INHERITANCE` | kelas dengan induk selain `object` dan `ABC` |
| `POLYMORPHISM` | metode yang menimpa metode bernama sama di kelas induk (di berkas yang sama) |
| `ABSTRACT_CLASS` | induk `ABC`, `@abstractmethod` |
| `INTERFACE` | kelas yang semua metodenya `@abstractmethod` dan tanpa atribut kelas |
| `OPERATOR_OVERLOAD` | `__add__`, `__eq__`, `__lt__`, `__getitem__`, `__len__`, … (bukan `__str__`/`__repr__`) |
| `PY_NUMPY`, `PY_MATPLOTLIB`, `PY_TKINTER` | `import numpy`, `import matplotlib…`, `import tkinter` |

## Mengubah aturan

Sebutkan konstruksi dan konsep yang seharusnya. Perubahannya: satu baris di detektor, satu contoh di `judge/tests/korpus/`, lalu `infra/tes.sh judge` dan `infra/deploy.sh judge`. Soal yang sudah diimpor tidak diperiksa ulang secara otomatis.
