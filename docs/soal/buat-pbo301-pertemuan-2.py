#!/usr/bin/env python3
"""Menyusun docs/soal/pbo301-pertemuan-2.json.

PBO301 pertemuan 2 — Class, Object, Class Diagram. Konsep yang diizinkan di
produksi: 21 konsep dasar, MODERN_CPP, CLASS_OBJECT.

Akibat peta konsep bagi solusi referensi pertemuan ini:
- semua anggota `public:` — bagian `private:` adalah ENCAPSULATION (pertemuan 4);
- tanpa konstruktor dan destruktor (pertemuan 3): objek diisi lewat anggota
  publik, inisialisasi agregat, atau nilai bawaan anggota;
- tidak ada kelas yang memuat objek kelas lain (KOMPOSISI_AGREGASI, pertemuan 5);
- tanpa kontainer STL.
Kode mahasiswa tidak dibatasi.

Setiap soal mensyaratkan CLASS_OBJECT di kode mahasiswa dan menyebut nama
kelas serta anggota yang diharapkan. Pemeriksaannya hanya "ada class di
berkas": nama kelas dan anggotanya tidak diperiksa oleh judge.

Dilewati dari silabus pertemuan 2 (tidak bisa dinilai dari keluaran): class
diagram UML dan pemetaan diagram ↔ kode, pemisahan `.h` / `.cpp` dan include
guard (satu berkas per jawaban), `constexpr`.

    python3 docs/soal/buat-pbo301-pertemuan-2.py
"""
from bangun import program, soal, tulis

W = ("CLASS_OBJECT",)
W_ID = "\n\n**Kode harus memuat class**: keluaran yang benar tanpa class hanya mendapat setengah nilai."
W_EN = "\n\n**The code must contain a class**: correct output without a class gets only half the marks."
W_AR = "\n\n**يجب أن يحتوي الكود على صنف (class)**: المخرج الصحيح بلا صنف ينال نصف الدرجة فقط."

NAMA = "NAMA = ['Andi', 'Budi', 'Citra', 'Dewi', 'Eko', 'Fajar', 'Gita', 'Hadi', 'Indah', 'Joko', 'Kartika', 'Lina', 'Made', 'Nanda', 'Putri', 'Rudi', 'Sari', 'Tono', 'Wulan', 'Yoga', 'Zahra', 'Bayu', 'Ayu', 'Rina', 'Dimas', 'Laras', 'Agus', 'Maya', 'Iwan', 'Tari']\n"
BARANG = "BARANG = ['Beras', 'Gula', 'Kopi', 'Teh', 'Susu', 'Roti', 'Telur', 'Minyak', 'Garam', 'Sabun', 'Pensil', 'Buku', 'Kertas', 'Tinta', 'Lampu', 'Kabel', 'Baterai', 'Kursi', 'Meja', 'Rak']\n"

# ============================== MUDAH SEKALI (8) ==============================

soal("kelas-persegi", "mudah_sekali", "trimmed", "print({2: 1, 3: 1000}.get(i, r.randint(2, 500) if i % 2 == 0 else r.randint(501, 999)))\n",
     program(r'''
class Persegi {
public:
    int sisi;

    int luas() {
        return sisi * sisi;
    }

    int keliling() {
        return 4 * sisi;
    }
};''', r'''    Persegi p;
    cin >> p.sisi;
    cout << p.luas() << " " << p.keliling() << endl;'''), wajib=W,
     id=("Class Persegi", "Sebuah class menyatukan **data** (atribut) dan **fungsi yang mengolah data itu** (method).\n\nBuat class `Persegi` dengan:\n\n- atribut `int sisi`;\n- method `int luas()`;\n- method `int keliling()`.\n\n"
         "Di `main`, buat satu objek `Persegi`, isi `sisi`-nya dari masukan, lalu cetak luas dan kelilingnya dengan memanggil kedua method.\n\nSemua anggota ditulis di bagian `public:`.\n\nUntuk sisi 5 keluarannya `25 20`." + W_ID,
         "Satu bilangan bulat: panjang sisi.", "Satu baris: luas, lalu keliling.", "1 ≤ sisi ≤ 1000"),
     en=("The Persegi Class", "A class brings together **data** (attributes) and **the functions that work on that data** (methods).\n\nMake a class `Persegi` (square) with:\n\n- an attribute `int sisi` (side);\n- a method `int luas()` (area);\n- a method `int keliling()` (perimeter).\n\n"
         "In `main`, create one `Persegi` object, fill its `sisi` from the input, then print the area and the perimeter by calling the two methods.\n\nAll members go in the `public:` section.\n\nFor a side of 5 the output is `25 20`." + W_EN,
         "One integer: the side length.", "One line: the area, then the perimeter.", "1 ≤ side ≤ 1000"),
     ar=("الصنف Persegi", "الصنف يجمع **البيانات** (الخصائص) و**الدوال التي تعالج تلك البيانات** (الطرائق).\n\nأنشئ صنفًا `Persegi` (مربع) فيه:\n\n- خاصية `int sisi` (الضلع)؛\n- طريقة `int luas()` (المساحة)؛\n- طريقة `int keliling()` (المحيط).\n\n"
         "في `main` أنشئ كائنًا واحدًا من `Persegi` واملأ `sisi` من المدخل ثم اطبع المساحة والمحيط باستدعاء الطريقتين.\n\nتُكتب كل الأعضاء في القسم `public:`.\n\nلضلع طوله 5 يكون المخرج `25 20`." + W_AR,
         "عدد صحيح واحد: طول الضلع.", "سطر واحد: المساحة ثم المحيط.", "1 ≤ الضلع ≤ 1000"))

soal("kelas-balok", "mudah_sekali", "trimmed", "print(r.randint(1, 100), r.randint(1, 100), r.randint(1, 100))\n",
     program(r'''
class Balok {
public:
    int panjang;
    int lebar;
    int tinggi;

    int volume() {
        return panjang * lebar * tinggi;
    }

    int luasPermukaan() {
        return 2 * (panjang * lebar + panjang * tinggi + lebar * tinggi);
    }
};''', r'''    Balok b;
    cin >> b.panjang >> b.lebar >> b.tinggi;
    cout << b.volume() << " " << b.luasPermukaan() << endl;'''), wajib=W,
     id=("Class Balok", "Buat class `Balok` dengan atribut `int panjang`, `int lebar`, `int tinggi`, serta method:\n\n- `int volume()` — panjang × lebar × tinggi;\n- `int luasPermukaan()` — 2 × (panjang × lebar + panjang × tinggi + lebar × tinggi).\n\n"
         "Baca ukuran sebuah balok ke dalam satu objek, lalu cetak volume dan luas permukaannya.\n\nUntuk `2 3 4` keluarannya `24 52`." + W_ID,
         "Satu baris berisi panjang, lebar, dan tinggi.", "Satu baris: volume, lalu luas permukaan.", "Tiap ukuran antara 1 dan 100."),
     en=("The Balok Class", "Make a class `Balok` (box) with the attributes `int panjang`, `int lebar`, `int tinggi` (length, width, height) and the methods:\n\n- `int volume()` — length × width × height;\n- `int luasPermukaan()` — the surface area, 2 × (length × width + length × height + width × height).\n\n"
         "Read the sizes of a box into one object, then print its volume and surface area.\n\nFor `2 3 4` the output is `24 52`." + W_EN,
         "One line with the length, the width and the height.", "One line: the volume, then the surface area.", "Each size is between 1 and 100."),
     ar=("الصنف Balok", "أنشئ صنفًا `Balok` (متوازي مستطيلات) خصائصه `int panjang` و `int lebar` و `int tinggi` (الطول والعرض والارتفاع) وطرائقه:\n\n- `int volume()` — الطول × العرض × الارتفاع؛\n- `int luasPermukaan()` — المساحة السطحية: 2 × (الطول × العرض + الطول × الارتفاع + العرض × الارتفاع).\n\n"
         "اقرأ قياسات جسم في كائن واحد ثم اطبع حجمه ومساحته السطحية.\n\nعند `2 3 4` يكون المخرج `24 52`." + W_AR,
         "سطر واحد فيه الطول والعرض والارتفاع.", "سطر واحد: الحجم ثم المساحة السطحية.", "كل قياس بين 1 و 100."))

soal("perkenalan", "mudah_sekali", "trimmed", NAMA + "print(r.choice(NAMA), r.randint(2020, 2026))\n",
     program(r'''
class Mahasiswa {
public:
    string nama;
    int angkatan;

    void perkenalan() {
        cout << "Halo, saya " << nama << " angkatan " << angkatan << "." << endl;
    }
};''', r'''    Mahasiswa m;
    cin >> m.nama >> m.angkatan;
    m.perkenalan();''', "string"), wajib=W,
     id=("Perkenalan", "Buat class `Mahasiswa` dengan atribut `string nama` dan `int angkatan`, serta method `void perkenalan()` yang mencetak satu baris:\n\n`Halo, saya <nama> angkatan <angkatan>.`\n\n"
         "Method memakai atribut objeknya sendiri tanpa perlu diberi parameter. Baca nama dan angkatan ke dalam sebuah objek, lalu panggil `perkenalan()`.\n\nUntuk `Citra 2025` keluarannya `Halo, saya Citra angkatan 2025.`" + W_ID,
         "Satu baris berisi nama (satu kata) dan angkatan.", "Satu baris perkenalan.", "2020 ≤ angkatan ≤ 2026"),
     en=("Introduction", "Make a class `Mahasiswa` (student) with the attributes `string nama` (name) and `int angkatan` (year of entry), and a method `void perkenalan()` that prints one line:\n\n`Halo, saya <nama> angkatan <angkatan>.`\n\n"
         "A method uses the attributes of its own object without needing parameters. Read the name and the year into an object, then call `perkenalan()`.\n\nFor `Citra 2025` the output is `Halo, saya Citra angkatan 2025.`" + W_EN,
         "One line with the name (one word) and the year of entry.", "One line of introduction.", "2020 ≤ year ≤ 2026"),
     ar=("التعريف بالنفس", "أنشئ صنفًا `Mahasiswa` (طالب) خاصيتاه `string nama` (الاسم) و `int angkatan` (سنة الالتحاق)، وطريقة `void perkenalan()` تطبع سطرًا واحدًا:\n\n`Halo, saya <nama> angkatan <angkatan>.`\n\n"
         "الطريقة تستعمل خصائص كائنها دون حاجة إلى وسائط. اقرأ الاسم والسنة في كائن ثم استدعِ `perkenalan()`.\n\nعند `Citra 2025` يكون المخرج `Halo, saya Citra angkatan 2025.`" + W_AR,
         "سطر واحد فيه الاسم (كلمة واحدة) وسنة الالتحاق.", "سطر التعريف.", "2020 ≤ السنة ≤ 2026"))

soal("penghitung", "mudah_sekali", "trimmed", "n = {2: 1}.get(i, r.randint(2, 50))\np = {3: 1.0, 4: 0.0}.get(i, 0.6)\nprint(''.join('+' if r.random() < p else '-' for _ in range(n)))\n",
     program(r'''
class Penghitung {
public:
    int nilai = 0;

    void naik() {
        nilai++;
    }

    void turun() {
        nilai--;
    }
};''', r'''    string tombol;
    cin >> tombol;
    Penghitung p;
    for (auto t : tombol) {
        if (t == '+') {
            p.naik();
        } else {
            p.turun();
        }
    }
    cout << p.nilai << endl;''', "string"), wajib=W,
     id=("Penghitung", "Buat class `Penghitung` dengan atribut `int nilai` yang mula-mula 0, dan dua method: `void naik()` menambah nilai satu, `void turun()` menguranginya satu.\n\n"
         "Masukan berupa deretan tombol yang ditekan: `+` berarti naik, `-` berarti turun. Jalankan semuanya pada satu objek, lalu cetak nilai akhirnya.\n\nUntuk `++-+` jawabannya 2.\n\nObjek **mengingat keadaannya** di antara pemanggilan method." + W_ID,
         "Satu baris: deretan karakter `+` dan `-` tanpa spasi.", "Satu bilangan bulat: nilai akhir penghitung.", "Panjang deretan 1 sampai 50."),
     en=("Counter", "Make a class `Penghitung` (counter) with an attribute `int nilai` (value) that starts at 0, and two methods: `void naik()` adds one, `void turun()` takes one away.\n\n"
         "The input is a run of buttons pressed: `+` means up, `-` means down. Apply them all to one object, then print the final value.\n\nFor `++-+` the answer is 2.\n\nAn object **remembers its state** between method calls." + W_EN,
         "One line: a run of `+` and `-` characters without spaces.", "One integer: the final value of the counter.", "The run is 1 to 50 characters long."),
     ar=("العدّاد", "أنشئ صنفًا `Penghitung` (عدّاد) فيه خاصية `int nilai` (القيمة) تبدأ من 0، وطريقتان: `void naik()` تزيد واحدًا و `void turun()` تنقص واحدًا.\n\n"
         "المدخل سلسلة أزرار مضغوطة: `+` تعني زيادة و `-` تعني نقصانًا. طبِّقها كلها على كائن واحد ثم اطبع القيمة النهائية.\n\nعند `++-+` الجواب 2.\n\nالكائن **يتذكر حالته** بين استدعاءات الطرائق." + W_AR,
         "سطر واحد: سلسلة من المحرفين `+` و `-` بلا مسافات.", "عدد صحيح واحد: القيمة النهائية للعدّاد.", "طول السلسلة من 1 إلى 50."))

soal("kelas-suhu", "mudah_sekali", "trimmed", "print(5 * ({2: 0, 3: -10, 4: 30}.get(i, r.randint(-10, 30))))\n",
     program(r'''
class Suhu {
public:
    int celsius;

    int fahrenheit() {
        return celsius * 9 / 5 + 32;
    }

    int kelvin() {
        return celsius + 273;
    }
};''', r'''    Suhu s;
    cin >> s.celsius;
    cout << s.fahrenheit() << " " << s.kelvin() << endl;'''), wajib=W,
     id=("Class Suhu", "Buat class `Suhu` yang menyimpan suhu dalam derajat Celsius (`int celsius`) dan punya dua method:\n\n- `int fahrenheit()` — celsius × 9 / 5 + 32;\n- `int kelvin()` — celsius + 273.\n\n"
         "Objek hanya menyimpan satu data; satuan lain **dihitung** saat diminta. Baca sebuah suhu, lalu cetak nilainya dalam Fahrenheit dan Kelvin.\n\nUntuk 25 keluarannya `77 298`." + W_ID,
         "Satu bilangan bulat: suhu dalam Celsius, selalu kelipatan 5.", "Satu baris: suhu dalam Fahrenheit, lalu dalam Kelvin.", "−50 ≤ celsius ≤ 150"),
     en=("The Suhu Class", "Make a class `Suhu` (temperature) that stores a temperature in degrees Celsius (`int celsius`) and has two methods:\n\n- `int fahrenheit()` — celsius × 9 / 5 + 32;\n- `int kelvin()` — celsius + 273.\n\n"
         "The object stores one piece of data only; other units are **computed** on request. Read a temperature, then print it in Fahrenheit and in Kelvin.\n\nFor 25 the output is `77 298`." + W_EN,
         "One integer: the temperature in Celsius, always a multiple of 5.", "One line: the temperature in Fahrenheit, then in Kelvin.", "−50 ≤ celsius ≤ 150"),
     ar=("الصنف Suhu", "أنشئ صنفًا `Suhu` (درجة حرارة) يخزّن الحرارة بالدرجات المئوية (`int celsius`) وله طريقتان:\n\n- `int fahrenheit()` — celsius × 9 / 5 + 32؛\n- `int kelvin()` — celsius + 273.\n\n"
         "الكائن يخزّن معلومة واحدة فقط؛ والوحدات الأخرى **تُحسب** عند الطلب. اقرأ درجة حرارة ثم اطبعها بالفهرنهايت وبالكلفن.\n\nللدرجة 25 يكون المخرج `77 298`." + W_AR,
         "عدد صحيح واحد: الحرارة بالمئوية، وهي دائمًا من مضاعفات 5.", "سطر واحد: الحرارة بالفهرنهايت ثم بالكلفن.", "−50 ≤ celsius ≤ 150"))

soal("jarak-manhattan", "mudah_sekali", "trimmed", "x, y = r.randint(-1000, 1000), r.randint(-1000, 1000)\nif i == 2:\n    x, y = 0, 0\nif i == 3:\n    x, y = -abs(x) - 1, -abs(y) - 1\nprint(x, y)\n",
     program(r'''
class Titik {
public:
    int x;
    int y;

    int jarakManhattan() {
        int dx = x;
        int dy = y;
        if (dx < 0) dx = -dx;
        if (dy < 0) dy = -dy;
        return dx + dy;
    }
};''', r'''    Titik t;
    cin >> t.x >> t.y;
    cout << t.jarakManhattan() << endl;'''), wajib=W,
     id=("Jarak Manhattan", "Di kota yang jalannya berpetak-petak, orang hanya bisa berjalan mendatar atau tegak. Jarak sebuah titik (x, y) dari titik asal (0, 0) lalu disebut *jarak Manhattan*: |x| + |y|.\n\n"
         "Buat class `Titik` dengan atribut `int x`, `int y` dan method `int jarakManhattan()`. Baca sebuah titik dan cetak jaraknya.\n\nUntuk titik (3, −4) jawabannya 7." + W_ID,
         "Satu baris berisi x dan y.", "Satu bilangan bulat: jarak Manhattan dari titik asal.", "−1001 ≤ x, y ≤ 1000"),
     en=("Manhattan Distance", "In a city laid out as a grid one can only walk horizontally or vertically. The distance of a point (x, y) from the origin (0, 0) is then called the *Manhattan distance*: |x| + |y|.\n\n"
         "Make a class `Titik` (point) with the attributes `int x`, `int y` and a method `int jarakManhattan()`. Read a point and print its distance.\n\nFor the point (3, −4) the answer is 7." + W_EN,
         "One line with x and y.", "One integer: the Manhattan distance from the origin.", "−1001 ≤ x, y ≤ 1000"),
     ar=("مسافة مانهاتن", "في مدينة شوارعها شبكة لا يمكن السير إلا أفقيًا أو رأسيًا. تسمى مسافة النقطة (x, y) عن نقطة الأصل (0, 0) عندئذ *مسافة مانهاتن*: |x| + |y|.\n\n"
         "أنشئ صنفًا `Titik` (نقطة) خاصيتاه `int x` و `int y` وطريقته `int jarakManhattan()`. اقرأ نقطة واطبع مسافتها.\n\nللنقطة (3, −4) الجواب 7." + W_AR,
         "سطر واحد فيه x و y.", "عدد صحيح واحد: مسافة مانهاتن عن نقطة الأصل.", "−1001 ≤ x, y ≤ 1000"))

soal("saklar-lampu", "mudah_sekali", "trimmed", "print({2: 0, 3: 1, 4: 1000}.get(i, 2 * r.randint(1, 499) + i % 2))\n",
     program(r'''
class Lampu {
public:
    bool menyala = false;

    void tekan() {
        menyala = !menyala;
    }

    string status() {
        if (menyala) {
            return "menyala";
        }
        return "padam";
    }
};''', r'''    int n;
    cin >> n;
    Lampu lampu;
    for (int k = 0; k < n; k++) {
        lampu.tekan();
    }
    cout << lampu.status() << endl;''', "string"), wajib=W,
     id=("Saklar Lampu", "Buat class `Lampu` dengan atribut `bool menyala` yang mula-mula `false`, method `void tekan()` yang membalik keadaannya, dan method `string status()` yang mengembalikan `menyala` atau `padam`.\n\n"
         "Baca n, tekan saklar sebuah lampu sebanyak n kali, lalu cetak statusnya.\n\nUntuk n = 3 keluarannya `menyala`; untuk n = 0 keluarannya `padam`." + W_ID,
         "Satu bilangan bulat n: banyak penekanan saklar.", "`menyala` atau `padam`.", "0 ≤ n ≤ 1000"),
     en=("Light Switch", "Make a class `Lampu` (lamp) with an attribute `bool menyala` (is on) that starts `false`, a method `void tekan()` (press) that flips the state, and a method `string status()` that returns `menyala` (on) or `padam` (off).\n\n"
         "Read n, press the switch of one lamp n times, then print its status.\n\nFor n = 3 the output is `menyala`; for n = 0 it is `padam`." + W_EN,
         "One integer n: the number of presses.", "`menyala` or `padam`.", "0 ≤ n ≤ 1000"),
     ar=("مفتاح المصباح", "أنشئ صنفًا `Lampu` (مصباح) فيه خاصية `bool menyala` (مضيء) تبدأ `false`، وطريقة `void tekan()` (اضغط) تعكس الحالة، وطريقة `string status()` تُرجع `menyala` (مضيء) أو `padam` (مطفأ).\n\n"
         "اقرأ n واضغط مفتاح مصباح واحد n مرة ثم اطبع حالته.\n\nعند n = 3 يكون المخرج `menyala`، وعند n = 0 يكون `padam`." + W_AR,
         "عدد صحيح واحد n: عدد الضغطات.", "`menyala` أو `padam`.", "0 ≤ n ≤ 1000"))

soal("persegi-atau-bukan", "mudah_sekali", "trimmed", "p, l = r.randint(1, 100), r.randint(1, 100)\nif i % 2 == 0:\n    l = p\nprint(p, l)\n",
     program(r'''
class PersegiPanjang {
public:
    int panjang;
    int lebar;

    bool persegi() {
        return panjang == lebar;
    }

    int luas() {
        return panjang * lebar;
    }
};''', r'''    PersegiPanjang pp;
    cin >> pp.panjang >> pp.lebar;
    if (pp.persegi()) {
        cout << "persegi " << pp.luas() << endl;
    } else {
        cout << "bukan persegi " << pp.luas() << endl;
    }'''), wajib=W,
     id=("Persegi atau Bukan", "Buat class `PersegiPanjang` dengan atribut `int panjang`, `int lebar`, method `bool persegi()` yang benar bila panjang sama dengan lebar, dan method `int luas()`.\n\n"
         "Baca sebuah persegi panjang, lalu cetak `persegi` atau `bukan persegi`, diikuti luasnya.\n\nContoh: `5 5` → `persegi 25`; `4 6` → `bukan persegi 24`." + W_ID,
         "Satu baris berisi panjang dan lebar.", "`persegi` atau `bukan persegi`, satu spasi, lalu luasnya.", "1 ≤ panjang, lebar ≤ 100"),
     en=("Square or Not", "Make a class `PersegiPanjang` (rectangle) with the attributes `int panjang`, `int lebar`, a method `bool persegi()` that is true when length equals width, and a method `int luas()` (area).\n\n"
         "Read a rectangle, then print `persegi` (square) or `bukan persegi` (not a square), followed by its area.\n\nExamples: `5 5` → `persegi 25`; `4 6` → `bukan persegi 24`." + W_EN,
         "One line with the length and the width.", "`persegi` or `bukan persegi`, one space, then the area.", "1 ≤ length, width ≤ 100"),
     ar=("مربع أم لا", "أنشئ صنفًا `PersegiPanjang` (مستطيل) خاصيتاه `int panjang` و `int lebar`، وطريقة `bool persegi()` تكون صحيحة إذا تساوى الطول والعرض، وطريقة `int luas()` (المساحة).\n\n"
         "اقرأ مستطيلًا ثم اطبع `persegi` (مربع) أو `bukan persegi` (ليس مربعًا) تليها مساحته.\n\nأمثلة: `5 5` ← `persegi 25`؛ `4 6` ← `bukan persegi 24`." + W_AR,
         "سطر واحد فيه الطول والعرض.", "`persegi` أو `bukan persegi` ثم مسافة ثم المساحة.", "1 ≤ الطول، العرض ≤ 100"))

# ================================= MUDAH (8) =================================

soal("tangki-air", "mudah", "trimmed",
     "kap = r.randint(5, 50) * 10\nprint(kap)\nn = r.randint(1, 15)\nprint(n)\nfor k in range(n):\n    if k == 0 or r.random() < 0.55:\n        print('isi', r.randint(1, kap))\n    else:\n        print('pakai', r.randint(1, kap))\n",
     program(r'''
class Tangki {
public:
    int kapasitas;
    int isi = 0;

    int tuang(int liter) {
        int ruang = kapasitas - isi;
        if (liter > ruang) {
            liter = ruang;
        }
        isi += liter;
        return liter;
    }

    int pakai(int liter) {
        if (liter > isi) {
            liter = isi;
        }
        isi -= liter;
        return liter;
    }
};''', r'''    Tangki t;
    int n;
    cin >> t.kapasitas >> n;
    for (int k = 0; k < n; k++) {
        string perintah;
        int liter;
        cin >> perintah >> liter;
        if (perintah == "isi") {
            cout << t.tuang(liter) << endl;
        } else {
            cout << t.pakai(liter) << endl;
        }
    }
    cout << "Sisa: " << t.isi << endl;''', "string"), wajib=W,
     id=("Tangki Air", "Buat class `Tangki` dengan atribut `int kapasitas` dan `int isi` (mula-mula 0), serta dua method yang **mengembalikan banyak liter yang benar-benar berpindah**:\n\n"
         "- `int tuang(int liter)` — mengisi tangki, tetapi tidak pernah melebihi kapasitas; kelebihannya terbuang;\n- `int pakai(int liter)` — mengambil air, tetapi tidak lebih dari yang ada.\n\n"
         "Masukan: kapasitas, banyak perintah n, lalu n perintah `isi x` atau `pakai x`. Untuk tiap perintah cetak nilai kembalian method. Di akhir cetak `Sisa: k`.\n\n"
         "Contoh: kapasitas 100; `isi 70` → 70; `isi 50` → 30; `pakai 120` → 100; `pakai 5` → 0; lalu `Sisa: 0`." + W_ID,
         "Baris pertama: kapasitas. Baris kedua: n. Lalu n baris perintah.", "n baris berisi liter yang berpindah, lalu satu baris `Sisa: k`.", "50 ≤ kapasitas ≤ 500; 1 ≤ n ≤ 15; 1 ≤ x ≤ 500"),
     en=("Water Tank", "Make a class `Tangki` (tank) with the attributes `int kapasitas` (capacity) and `int isi` (content, starting at 0), and two methods that **return the number of litres actually moved**:\n\n"
         "- `int tuang(int liter)` — fills the tank but never beyond its capacity; the excess is lost;\n- `int pakai(int liter)` — takes water out, but no more than there is.\n\n"
         "Input: the capacity, the number of commands n, then n commands `isi x` (fill) or `pakai x` (use). For each command print the method's return value. At the end print `Sisa: k` (remaining).\n\n"
         "Example: capacity 100; `isi 70` → 70; `isi 50` → 30; `pakai 120` → 100; `pakai 5` → 0; then `Sisa: 0`." + W_EN,
         "First line: the capacity. Second line: n. Then n command lines.", "n lines with the litres moved, then one line `Sisa: k`.", "50 ≤ capacity ≤ 500; 1 ≤ n ≤ 15; 1 ≤ x ≤ 500"),
     ar=("خزان الماء", "أنشئ صنفًا `Tangki` (خزان) خاصيتاه `int kapasitas` (السعة) و `int isi` (المحتوى، ويبدأ من 0)، وطريقتان **تُرجعان عدد اللترات التي انتقلت فعلًا**:\n\n"
         "- `int tuang(int liter)` — تملأ الخزان دون تجاوز سعته أبدًا؛ والزائد يضيع؛\n- `int pakai(int liter)` — تأخذ ماءً لا يزيد على الموجود.\n\n"
         "المدخل: السعة ثم عدد الأوامر n ثم n أمرًا `isi x` (املأ) أو `pakai x` (استعمل). لكل أمر اطبع القيمة التي تُرجعها الطريقة. في النهاية اطبع `Sisa: k` (الباقي).\n\n"
         "مثال: السعة 100؛ `isi 70` ← 70؛ `isi 50` ← 30؛ `pakai 120` ← 100؛ `pakai 5` ← 0؛ ثم `Sisa: 0`." + W_AR,
         "السطر الأول: السعة. السطر الثاني: n. ثم n سطر أوامر.", "n سطرًا فيها اللترات المنتقلة ثم سطر `Sisa: k`.", "50 ≤ السعة ≤ 500؛ 1 ≤ n ≤ 15؛ 1 ≤ x ≤ 500"))

soal("jarak-dua-titik", "mudah", "trimmed", "print(r.randint(-100, 100), r.randint(-100, 100))\nprint(r.randint(-100, 100), r.randint(-100, 100))\nprint(r.randint(-50, 50), r.randint(-50, 50))\n",
     program(r'''
class Titik {
public:
    int x;
    int y;

    void geser(int dx, int dy) {
        x += dx;
        y += dy;
    }

    int jarakKe(Titik lain) {
        int dx = x - lain.x;
        int dy = y - lain.y;
        if (dx < 0) dx = -dx;
        if (dy < 0) dy = -dy;
        return dx + dy;
    }
};''', r'''    Titik a, b;
    int dx, dy;
    cin >> a.x >> a.y;
    cin >> b.x >> b.y;
    cin >> dx >> dy;
    cout << a.jarakKe(b) << " ";
    a.geser(dx, dy);
    cout << a.jarakKe(b) << endl;'''), wajib=W,
     id=("Jarak Dua Titik", "Buat class `Titik` dengan atribut `int x`, `int y` dan dua method:\n\n- `void geser(int dx, int dy)` — memindahkan titik itu;\n- `int jarakKe(Titik lain)` — jarak Manhattan ke titik lain: |x − lain.x| + |y − lain.y|.\n\n"
         "Sebuah method bisa menerima **objek lain dari class yang sama** sebagai parameter.\n\nBaca titik A, titik B, dan sebuah pergeseran (dx, dy). Cetak jarak A ke B, lalu geser A dan cetak jaraknya ke B sekali lagi.\n\n"
         "Contoh: A = (1, 2), B = (4, 6), pergeseran (3, 0) → `7 4`." + W_ID,
         "Baris pertama: x dan y titik A. Baris kedua: x dan y titik B. Baris ketiga: dx dan dy.", "Satu baris: jarak sebelum dan sesudah A digeser.", "Koordinat antara −100 dan 100; dx, dy antara −50 dan 50"),
     en=("Distance between Two Points", "Make a class `Titik` (point) with the attributes `int x`, `int y` and two methods:\n\n- `void geser(int dx, int dy)` — moves the point;\n- `int jarakKe(Titik lain)` — the Manhattan distance to another point: |x − lain.x| + |y − lain.y|.\n\n"
         "A method can take **another object of the same class** as a parameter.\n\nRead point A, point B and a shift (dx, dy). Print the distance from A to B, then move A and print its distance to B once more.\n\n"
         "Example: A = (1, 2), B = (4, 6), shift (3, 0) → `7 4`." + W_EN,
         "First line: x and y of A. Second line: x and y of B. Third line: dx and dy.", "One line: the distance before and after A is moved.", "Coordinates are between −100 and 100; dx, dy between −50 and 50"),
     ar=("المسافة بين نقطتين", "أنشئ صنفًا `Titik` (نقطة) خاصيتاه `int x` و `int y` وطريقتان:\n\n- `void geser(int dx, int dy)` — تحرّك النقطة؛\n- `int jarakKe(Titik lain)` — مسافة مانهاتن إلى نقطة أخرى: |x − lain.x| + |y − lain.y|.\n\n"
         "يمكن أن تأخذ الطريقة **كائنًا آخر من الصنف نفسه** وسيطًا.\n\nاقرأ النقطة A والنقطة B وإزاحة (dx, dy). اطبع المسافة من A إلى B ثم حرِّك A واطبع مسافتها إلى B مرة أخرى.\n\n"
         "مثال: A = (1, 2) و B = (4, 6) والإزاحة (3, 0) ← `7 4`." + W_AR,
         "السطر الأول: x و y للنقطة A. السطر الثاني: x و y للنقطة B. السطر الثالث: dx و dy.", "سطر واحد: المسافة قبل تحريك A وبعده.", "الإحداثيات بين −100 و 100؛ dx و dy بين −50 و 50"))

soal("waktu-tambah-menit", "mudah", "trimmed", "j, m, n = r.randint(0, 23), r.randint(0, 59), r.randint(0, 10000)\nif i == 2:\n    j, m, n = 23, 59, 1\nif i == 3:\n    n = 0\nif i == 4:\n    n = 1440\nprint(j, m, n)\n",
     program(r'''
class Waktu {
public:
    int jam;
    int menit;

    void tambahMenit(int n) {
        int total = (jam * 60 + menit + n) % 1440;
        jam = total / 60;
        menit = total % 60;
    }

    void cetak() {
        cout << setfill('0') << setw(2) << jam << ":" << setw(2) << menit << endl;
    }
};''', r'''    Waktu w;
    int n;
    cin >> w.jam >> w.menit >> n;
    w.tambahMenit(n);
    w.cetak();''', "iomanip"), wajib=W,
     id=("Tambah Menit", "Buat class `Waktu` dengan atribut `int jam`, `int menit` dan dua method:\n\n- `void tambahMenit(int n)` — memajukan waktu n menit; setelah 23:59 waktu kembali ke 00:00;\n- `void cetak()` — mencetak waktu sebagai `JJ:MM`, masing-masing dua digit.\n\n"
         "Baca sebuah waktu dan n, majukan, lalu cetak.\n\nContoh: `9 45 30` → `10:15`; `23 50 20` → `00:10`." + W_ID,
         "Satu baris berisi jam, menit, dan n.", "Waktu yang baru dalam bentuk `JJ:MM`.", "0 ≤ jam ≤ 23; 0 ≤ menit ≤ 59; 0 ≤ n ≤ 10000"),
     en=("Add Minutes", "Make a class `Waktu` (time) with the attributes `int jam`, `int menit` (hour, minute) and two methods:\n\n- `void tambahMenit(int n)` — moves the time forward by n minutes; after 23:59 it wraps to 00:00;\n- `void cetak()` — prints the time as `HH:MM`, two digits each.\n\n"
         "Read a time and n, move it forward, then print it.\n\nExamples: `9 45 30` → `10:15`; `23 50 20` → `00:10`." + W_EN,
         "One line with the hour, the minute and n.", "The new time in the form `HH:MM`.", "0 ≤ hour ≤ 23; 0 ≤ minute ≤ 59; 0 ≤ n ≤ 10000"),
     ar=("إضافة دقائق", "أنشئ صنفًا `Waktu` (وقت) خاصيتاه `int jam` و `int menit` (الساعة والدقيقة) وطريقتان:\n\n- `void tambahMenit(int n)` — تقدّم الوقت n دقيقة؛ وبعد 23:59 يعود إلى 00:00؛\n- `void cetak()` — تطبع الوقت على الصورة `HH:MM` برقمين لكل جزء.\n\n"
         "اقرأ وقتًا و n وقدِّمه ثم اطبعه.\n\nأمثلة: `9 45 30` ← `10:15`؛ `23 50 20` ← `00:10`." + W_AR,
         "سطر واحد فيه الساعة والدقيقة و n.", "الوقت الجديد على الصورة `HH:MM`.", "0 ≤ الساعة ≤ 23؛ 0 ≤ الدقيقة ≤ 59؛ 0 ≤ n ≤ 10000"))

soal("kelas-pecahan", "mudah", "trimmed", "g = r.randint(1, 100)\na, b = g * r.randint(1, 100), g * r.randint(1, 100)\nif i == 2:\n    b = a\nif i == 3:\n    a, b = r.choice([(7, 13), (97, 89), (1, 1)])\nif i == 4:\n    b = 1\nprint(a, b)\n",
     program(r'''
class Pecahan {
public:
    int pembilang;
    int penyebut;

    void sederhanakan() {
        int a = pembilang;
        int b = penyebut;
        while (b != 0) {
            int sisa = a % b;
            a = b;
            b = sisa;
        }
        pembilang /= a;
        penyebut /= a;
    }

    void cetak() {
        cout << pembilang << "/" << penyebut << endl;
    }
};''', r'''    Pecahan p;
    cin >> p.pembilang >> p.penyebut;
    p.sederhanakan();
    p.cetak();'''), wajib=W,
     id=("Class Pecahan", "Buat class `Pecahan` dengan atribut `int pembilang`, `int penyebut` dan dua method:\n\n- `void sederhanakan()` — membagi pembilang dan penyebut dengan faktor persekutuan terbesarnya, sehingga objek itu sendiri berubah;\n- `void cetak()` — mencetak `pembilang/penyebut`.\n\n"
         "Baca sebuah pecahan, sederhanakan, lalu cetak. Penyebut 1 tetap ditulis.\n\nContoh: `12 18` → `2/3`; `10 5` → `2/1`." + W_ID,
         "Satu baris berisi pembilang dan penyebut.", "Pecahan paling sederhana dalam bentuk `a/b`.", "1 ≤ pembilang, penyebut ≤ 10000"),
     en=("The Pecahan Class", "Make a class `Pecahan` (fraction) with the attributes `int pembilang`, `int penyebut` (numerator, denominator) and two methods:\n\n- `void sederhanakan()` — divides numerator and denominator by their greatest common divisor, so the object itself changes;\n- `void cetak()` — prints `numerator/denominator`.\n\n"
         "Read a fraction, reduce it, then print it. A denominator of 1 is still written.\n\nExamples: `12 18` → `2/3`; `10 5` → `2/1`." + W_EN,
         "One line with the numerator and the denominator.", "The fraction in lowest terms, in the form `a/b`.", "1 ≤ numerator, denominator ≤ 10000"),
     ar=("الصنف Pecahan", "أنشئ صنفًا `Pecahan` (كسر) خاصيتاه `int pembilang` و `int penyebut` (البسط والمقام) وطريقتان:\n\n- `void sederhanakan()` — تقسم البسط والمقام على قاسمهما المشترك الأكبر فيتغير الكائن نفسه؛\n- `void cetak()` — تطبع `البسط/المقام`.\n\n"
         "اقرأ كسرًا وبسِّطه ثم اطبعه. المقام 1 يُكتب أيضًا.\n\nأمثلة: `12 18` ← `2/3`؛ `10 5` ← `2/1`." + W_AR,
         "سطر واحد فيه البسط والمقام.", "الكسر في أبسط صورة على الشكل `a/b`.", "1 ≤ البسط، المقام ≤ 10000"))

soal("prajurit", "mudah", "trimmed",
     NAMA + "k = r.randint(50, 150)\nn = r.randint(1, 10)\nd = [r.randint(5, 60) for _ in range(n)]\nif i % 2 == 0:\n    d = [r.randint(1, max(1, k // (n + 1))) for _ in range(n)]\nif i == 3:\n    d = [k]\nprint(r.choice(NAMA), k)\nprint(len(d))\nprint(*d)\n",
     program(r'''
class Prajurit {
public:
    string nama;
    int kesehatan;

    void terluka(int besar) {
        kesehatan -= besar;
        if (kesehatan < 0) {
            kesehatan = 0;
        }
    }

    bool gugur() {
        return kesehatan == 0;
    }
};''', r'''    Prajurit p;
    int n;
    cin >> p.nama >> p.kesehatan;
    cin >> n;
    for (int k = 0; k < n; k++) {
        int luka;
        cin >> luka;
        if (!p.gugur()) {
            p.terluka(luka);
            cout << "Kesehatan: " << p.kesehatan << endl;
        }
    }
    if (p.gugur()) {
        cout << p.nama << " gugur" << endl;
    } else {
        cout << p.nama << " selamat" << endl;
    }''', "string"), wajib=W,
     id=("Prajurit", "Buat class `Prajurit` dengan atribut `string nama`, `int kesehatan` dan dua method:\n\n- `void terluka(int besar)` — mengurangi kesehatan, tetapi tidak pernah sampai di bawah 0;\n- `bool gugur()` — benar bila kesehatannya 0.\n\n"
         "Seorang prajurit menerima n serangan berturut-turut. Setelah tiap serangan cetak `Kesehatan: k`. **Begitu prajurit gugur, serangan sesudahnya diabaikan** dan tidak dicetak. Di akhir cetak `<nama> gugur` atau `<nama> selamat`.\n\n"
         "Contoh: `Budi 50` dengan serangan `20 40 10` →\n\n```\nKesehatan: 30\nKesehatan: 0\nBudi gugur\n```" + W_ID,
         "Baris pertama: nama dan kesehatan awal. Baris kedua: n. Baris ketiga: n bilangan, besar tiap serangan.", "Satu baris per serangan yang diterima, lalu satu baris akhir.", "50 ≤ kesehatan ≤ 150; 1 ≤ n ≤ 10; 1 ≤ serangan ≤ 150"),
     en=("Soldier", "Make a class `Prajurit` (soldier) with the attributes `string nama` (name), `int kesehatan` (health) and two methods:\n\n- `void terluka(int besar)` (wounded) — lowers the health, but never below 0;\n- `bool gugur()` (fallen) — true when the health is 0.\n\n"
         "A soldier takes n hits in a row. After each hit print `Kesehatan: k`. **Once the soldier has fallen, later hits are ignored** and not printed. At the end print `<name> gugur` (fallen) or `<name> selamat` (safe).\n\n"
         "Example: `Budi 50` with hits `20 40 10` →\n\n```\nKesehatan: 30\nKesehatan: 0\nBudi gugur\n```" + W_EN,
         "First line: the name and the starting health. Second line: n. Third line: n numbers, the size of each hit.", "One line per hit taken, then one final line.", "50 ≤ health ≤ 150; 1 ≤ n ≤ 10; 1 ≤ hit ≤ 150"),
     ar=("الجندي", "أنشئ صنفًا `Prajurit` (جندي) خاصيتاه `string nama` (الاسم) و `int kesehatan` (الصحة) وطريقتان:\n\n- `void terluka(int besar)` (جُرح) — تنقص الصحة دون أن تنزل عن 0 أبدًا؛\n- `bool gugur()` (سقط) — صحيحة إذا كانت الصحة 0.\n\n"
         "يتلقى جندي n ضربة متتالية. بعد كل ضربة اطبع `Kesehatan: k`. **متى سقط الجندي أُهملت الضربات التالية** ولم تُطبع. في النهاية اطبع `<الاسم> gugur` (سقط) أو `<الاسم> selamat` (نجا).\n\n"
         "مثال: `Budi 50` مع الضربات `20 40 10` ←\n\n```\nKesehatan: 30\nKesehatan: 0\nBudi gugur\n```" + W_AR,
         "السطر الأول: الاسم والصحة الابتدائية. السطر الثاني: n. السطر الثالث: n عددًا هي مقادير الضربات.", "سطر لكل ضربة مُتلقّاة ثم سطر ختامي.", "50 ≤ الصحة ≤ 150؛ 1 ≤ n ≤ 10؛ 1 ≤ الضربة ≤ 150"))

soal("rantai-method", "mudah", "trimmed", "print(r.randint(-100, 100), r.randint(-100, 100), r.randint(-20, 20), r.randint(-100, 100))\n",
     program(r'''
class Akumulator {
public:
    int total = 0;

    Akumulator &tambah(int x) {
        this->total += x;
        return *this;
    }

    Akumulator &kali(int x) {
        this->total *= x;
        return *this;
    }
};''', r'''    int a, b, c, d;
    cin >> a >> b >> c >> d;
    Akumulator ak;
    ak.tambah(a).tambah(b).kali(c).tambah(d);
    cout << ak.total << endl;'''), wajib=W,
     id=("Rantai Method", "Di dalam method, `this` adalah penunjuk ke objek yang sedang dipakai. Bila sebuah method mengembalikan `*this` — objek itu sendiri — pemanggilan bisa **dirantai**: `ak.tambah(2).kali(3)`.\n\n"
         "Buat class `Akumulator` dengan atribut `int total` (mula-mula 0) dan dua method yang sama-sama mengembalikan `*this` sebagai `Akumulator &`:\n\n- `tambah(int x)` — menambahkan x ke total;\n- `kali(int x)` — mengalikan total dengan x.\n\n"
         "Baca a, b, c, d, lalu hitung dalam **satu pernyataan**: `ak.tambah(a).tambah(b).kali(c).tambah(d);` dan cetak totalnya.\n\nUntuk `2 3 4 1` jawabannya (2 + 3) × 4 + 1 = 21." + W_ID,
         "Satu baris berisi a, b, c, d.", "Satu bilangan bulat: (a + b) × c + d.", "−100 ≤ a, b, d ≤ 100; −20 ≤ c ≤ 20"),
     en=("Method Chaining", "Inside a method, `this` is a pointer to the object being used. When a method returns `*this` — the object itself — calls can be **chained**: `ak.tambah(2).kali(3)`.\n\n"
         "Make a class `Akumulator` with an attribute `int total` (starting at 0) and two methods that both return `*this` as `Akumulator &`:\n\n- `tambah(int x)` — adds x to the total;\n- `kali(int x)` — multiplies the total by x.\n\n"
         "Read a, b, c, d, then compute in **one statement**: `ak.tambah(a).tambah(b).kali(c).tambah(d);` and print the total.\n\nFor `2 3 4 1` the answer is (2 + 3) × 4 + 1 = 21." + W_EN,
         "One line with a, b, c, d.", "One integer: (a + b) × c + d.", "−100 ≤ a, b, d ≤ 100; −20 ≤ c ≤ 20"),
     ar=("تسلسل الطرائق", "داخل الطريقة يكون `this` مؤشرًا إلى الكائن المستعمل. إذا أرجعت الطريقة `*this` — أي الكائن نفسه — أمكن **تسلسل** الاستدعاءات: `ak.tambah(2).kali(3)`.\n\n"
         "أنشئ صنفًا `Akumulator` فيه خاصية `int total` (تبدأ من 0) وطريقتان تُرجعان كلتاهما `*this` على أنه `Akumulator &`:\n\n- `tambah(int x)` — تضيف x إلى المجموع؛\n- `kali(int x)` — تضرب المجموع في x.\n\n"
         "اقرأ a و b و c و d ثم احسب في **تعليمة واحدة**: `ak.tambah(a).tambah(b).kali(c).tambah(d);` واطبع المجموع.\n\nعند `2 3 4 1` الجواب (2 + 3) × 4 + 1 = 21." + W_AR,
         "سطر واحد فيه a و b و c و d.", "عدد صحيح واحد: (a + b) × c + d.", "−100 ≤ a, b, d ≤ 100؛ −20 ≤ c ≤ 20"))

soal("tiket-statis", "mudah", "trimmed", NAMA + "n = {2: 1}.get(i, r.randint(2, 15))\nprint(n)\nprint(*r.sample(NAMA, n))\n",
     program(r'''
class Tiket {
public:
    static int terjual;
    string pemilik;
    int nomor;

    void pesan(string nama) {
        pemilik = nama;
        terjual++;
        nomor = terjual;
    }

    void cetak() {
        cout << "Tiket " << nomor << ": " << pemilik << endl;
    }

    static int banyakTerjual() {
        return terjual;
    }
};

int Tiket::terjual = 0;''', r'''    int n;
    cin >> n;
    Tiket tiket[15];
    for (int k = 0; k < n; k++) {
        string nama;
        cin >> nama;
        tiket[k].pesan(nama);
    }
    for (int k = 0; k < n; k++) {
        tiket[k].cetak();
    }
    cout << "Terjual: " << Tiket::banyakTerjual() << endl;''', "string"), wajib=W,
     id=("Tiket dan Anggota Statis", "Atribut biasa dimiliki **tiap objek**. Atribut `static` dimiliki **class-nya**: hanya ada satu, dan semua objek melihat nilai yang sama.\n\n"
         "Buat class `Tiket` dengan:\n\n- atribut `static int terjual` — banyak tiket yang sudah dipesan, mula-mula 0;\n- atribut `string pemilik` dan `int nomor`;\n- method `void pesan(string nama)` — mencatat pemilik, menaikkan `terjual`, dan memberi tiket itu nomor urut berikutnya;\n- method `void cetak()` — mencetak `Tiket <nomor>: <pemilik>`;\n- method `static int banyakTerjual()`.\n\n"
         "Atribut statis didefinisikan sekali di luar class: `int Tiket::terjual = 0;`. Method statis dipanggil lewat nama class: `Tiket::banyakTerjual()`.\n\n"
         "Baca n nama pemesan. Pesan satu tiket untuk tiap nama, cetak semua tiket, lalu cetak `Terjual: n`.\n\nUntuk `Budi Citra`:\n\n```\nTiket 1: Budi\nTiket 2: Citra\nTerjual: 2\n```" + W_ID,
         "Baris pertama: n. Baris kedua: n nama, masing-masing satu kata.", "n baris tiket, lalu satu baris `Terjual: n`.", "1 ≤ n ≤ 15"),
     en=("Tickets and Static Members", "An ordinary attribute belongs to **each object**. A `static` attribute belongs to **the class**: there is only one, and all objects see the same value.\n\n"
         "Make a class `Tiket` (ticket) with:\n\n- an attribute `static int terjual` — the number of tickets booked so far, starting at 0;\n- the attributes `string pemilik` (owner) and `int nomor` (number);\n- a method `void pesan(string nama)` (book) — records the owner, raises `terjual`, and gives the ticket the next number;\n- a method `void cetak()` — prints `Tiket <number>: <owner>`;\n- a method `static int banyakTerjual()`.\n\n"
         "A static attribute is defined once outside the class: `int Tiket::terjual = 0;`. A static method is called through the class name: `Tiket::banyakTerjual()`.\n\n"
         "Read n names. Book one ticket for each, print all tickets, then print `Terjual: n` (sold).\n\nFor `Budi Citra`:\n\n```\nTiket 1: Budi\nTiket 2: Citra\nTerjual: 2\n```" + W_EN,
         "First line: n. Second line: n names, one word each.", "n ticket lines, then one line `Terjual: n`.", "1 ≤ n ≤ 15"),
     ar=("التذاكر والأعضاء الساكنة", "الخاصية العادية يملكها **كل كائن**. أما الخاصية `static` فيملكها **الصنف**: منها واحدة فقط، وكل الكائنات ترى القيمة نفسها.\n\n"
         "أنشئ صنفًا `Tiket` (تذكرة) فيه:\n\n- خاصية `static int terjual` — عدد التذاكر المحجوزة حتى الآن، وتبدأ من 0؛\n- الخاصيتان `string pemilik` (المالك) و `int nomor` (الرقم)؛\n- طريقة `void pesan(string nama)` (احجز) — تسجّل المالك وتزيد `terjual` وتعطي التذكرة الرقم التالي؛\n- طريقة `void cetak()` — تطبع `Tiket <الرقم>: <المالك>`؛\n- طريقة `static int banyakTerjual()`.\n\n"
         "تُعرَّف الخاصية الساكنة مرة واحدة خارج الصنف: `int Tiket::terjual = 0;`. وتُستدعى الطريقة الساكنة باسم الصنف: `Tiket::banyakTerjual()`.\n\n"
         "اقرأ n اسمًا. احجز تذكرة لكل اسم واطبع كل التذاكر ثم اطبع `Terjual: n` (المباع).\n\nعند `Budi Citra`:\n\n```\nTiket 1: Budi\nTiket 2: Citra\nTerjual: 2\n```" + W_AR,
         "السطر الأول: n. السطر الثاني: n اسمًا، كل اسم كلمة واحدة.", "n سطر تذاكر ثم سطر `Terjual: n`.", "1 ≤ n ≤ 15"))

soal("kendaraan-bensin", "mudah", "trimmed",
     "b, k = r.randint(5, 60) * 1000, r.randint(40, 120)\nn = r.randint(1, 8)\nprint(b, k)\nprint(n)\nprint(*[r.randint(1, 3 * b // k // n + 1) for _ in range(n)])\n",
     program(r'''
class Kendaraan {
public:
    int bensin;
    int konsumsi;
    int odometer = 0;

    bool jalan(int km) {
        int perlu = km * konsumsi;
        if (perlu > bensin) {
            return false;
        }
        bensin -= perlu;
        odometer += km;
        return true;
    }
};''', r'''    Kendaraan mobil;
    int n;
    cin >> mobil.bensin >> mobil.konsumsi;
    cin >> n;
    for (int k = 0; k < n; k++) {
        int km;
        cin >> km;
        if (mobil.jalan(km)) {
            cout << "jalan" << endl;
        } else {
            cout << "bensin tidak cukup" << endl;
        }
    }
    cout << "Odometer: " << mobil.odometer << " km, bensin: " << mobil.bensin << " ml" << endl;'''), wajib=W,
     id=("Kendaraan dan Bensin", "Buat class `Kendaraan` dengan atribut `int bensin` (dalam mililiter), `int konsumsi` (mililiter per kilometer), `int odometer` (kilometer yang sudah ditempuh, mula-mula 0), dan method:\n\n"
         "- `bool jalan(int km)` — bila bensinnya cukup untuk menempuh km kilometer, kurangi bensin, naikkan odometer, dan kembalikan `true`; bila tidak cukup, **jangan ubah apa pun** dan kembalikan `false`.\n\n"
         "Kendaraan diminta menempuh n perjalanan berturut-turut. Untuk tiap perjalanan cetak `jalan` atau `bensin tidak cukup`. Di akhir cetak `Odometer: x km, bensin: y ml`.\n\n"
         "Contoh: bensin 10000 ml, konsumsi 80 ml/km, perjalanan `50 100 60` →\n\n```\njalan\nbensin tidak cukup\njalan\nOdometer: 110 km, bensin: 1200 ml\n```" + W_ID,
         "Baris pertama: bensin dan konsumsi. Baris kedua: n. Baris ketiga: n bilangan, jarak tiap perjalanan.", "Satu baris per perjalanan, lalu satu baris ringkasan.", "5000 ≤ bensin ≤ 60000; 40 ≤ konsumsi ≤ 120; 1 ≤ n ≤ 8; 1 ≤ jarak ≤ 5000"),
     en=("Vehicle and Fuel", "Make a class `Kendaraan` (vehicle) with the attributes `int bensin` (fuel, in millilitres), `int konsumsi` (millilitres per kilometre), `int odometer` (kilometres driven, starting at 0), and a method:\n\n"
         "- `bool jalan(int km)` (drive) — if there is enough fuel for km kilometres, lower the fuel, raise the odometer and return `true`; if not, **change nothing** and return `false`.\n\n"
         "The vehicle is asked to make n trips in a row. For each trip print `jalan` (driven) or `bensin tidak cukup` (not enough fuel). At the end print `Odometer: x km, bensin: y ml`.\n\n"
         "Example: fuel 10000 ml, consumption 80 ml/km, trips `50 100 60` →\n\n```\njalan\nbensin tidak cukup\njalan\nOdometer: 110 km, bensin: 1200 ml\n```" + W_EN,
         "First line: the fuel and the consumption. Second line: n. Third line: n numbers, the length of each trip.", "One line per trip, then one summary line.", "5000 ≤ fuel ≤ 60000; 40 ≤ consumption ≤ 120; 1 ≤ n ≤ 8; 1 ≤ trip ≤ 5000"),
     ar=("المركبة والوقود", "أنشئ صنفًا `Kendaraan` (مركبة) خصائصه `int bensin` (الوقود بالمليلتر) و `int konsumsi` (مليلتر لكل كيلومتر) و `int odometer` (الكيلومترات المقطوعة، وتبدأ من 0)، وطريقة:\n\n"
         "- `bool jalan(int km)` (سِر) — إذا كفى الوقود لقطع km كيلومترًا فأنقص الوقود وزِد العدّاد وأرجع `true`؛ وإلا **فلا تغيّر شيئًا** وأرجع `false`.\n\n"
         "يُطلب من المركبة n رحلة متتالية. لكل رحلة اطبع `jalan` (سارت) أو `bensin tidak cukup` (الوقود لا يكفي). في النهاية اطبع `Odometer: x km, bensin: y ml`.\n\n"
         "مثال: الوقود 10000 مل والاستهلاك 80 مل/كم والرحلات `50 100 60` ←\n\n```\njalan\nbensin tidak cukup\njalan\nOdometer: 110 km, bensin: 1200 ml\n```" + W_AR,
         "السطر الأول: الوقود والاستهلاك. السطر الثاني: n. السطر الثالث: n عددًا هي أطوال الرحلات.", "سطر لكل رحلة ثم سطر ملخص.", "5000 ≤ الوقود ≤ 60000؛ 40 ≤ الاستهلاك ≤ 120؛ 1 ≤ n ≤ 8؛ 1 ≤ الرحلة ≤ 5000"))

# ============================== MUDAH-SEDANG (6) ==============================

soal("daftar-lulus", "mudah_sedang", "trimmed",
     NAMA + "n = {2: 1}.get(i, r.randint(2, 20))\nprint(n)\nfor nama in r.sample(NAMA, n):\n    a = r.randint(30, 100)\n    v = [min(100, max(0, a + r.randint(-25, 25))) for _ in range(3)]\n    if i == 3:\n        v = [r.randint(0, 50) for _ in range(3)]\n    if i == 4:\n        v = [60, 60, 60]\n    print(nama, *v)\n",
     program(r'''
class Mahasiswa {
public:
    string nama;
    int tugas;
    int uts;
    int uas;

    int nilaiAkhir() {
        return (30 * tugas + 30 * uts + 40 * uas) / 100;
    }

    bool lulus() {
        return nilaiAkhir() >= 60;
    }
};''', r'''    int n;
    cin >> n;
    Mahasiswa daftar[20];
    for (int k = 0; k < n; k++) {
        cin >> daftar[k].nama >> daftar[k].tugas >> daftar[k].uts >> daftar[k].uas;
    }
    auto banyak = 0;
    for (int k = 0; k < n; k++) {
        if (daftar[k].lulus()) {
            cout << daftar[k].nama << " " << daftar[k].nilaiAkhir() << endl;
            banyak++;
        }
    }
    cout << "Lulus: " << banyak << " dari " << n << endl;''', "string"), wajib=W,
     id=("Daftar Lulus", "Buat class `Mahasiswa` dengan atribut `string nama`, `int tugas`, `int uts`, `int uas` dan dua method:\n\n"
         "- `int nilaiAkhir()` — (30 × tugas + 30 × uts + 40 × uas) / 100, dengan pembagian bulat;\n- `bool lulus()` — benar bila nilai akhirnya 60 atau lebih. Method ini **memanggil method lain** dari objek yang sama.\n\n"
         "Simpan n mahasiswa dalam **array of objects**. Cetak nama dan nilai akhir mahasiswa yang lulus, sesuai urutan masukan, lalu `Lulus: k dari n`.\n\n"
         "Contoh: `Andi 80 70 60` dan `Budi 40 50 55` →\n\n```\nAndi 69\nLulus: 1 dari 2\n```" + W_ID,
         "Baris pertama: n. Lalu n baris, masing-masing berisi nama, nilai tugas, UTS, dan UAS.", "Satu baris `nama nilaiAkhir` untuk tiap mahasiswa yang lulus, lalu `Lulus: k dari n`.", "1 ≤ n ≤ 20; tiap nilai antara 0 dan 100"),
     en=("Pass List", "Make a class `Mahasiswa` (student) with the attributes `string nama`, `int tugas`, `int uts`, `int uas` (name; assignment, mid-term and final marks) and two methods:\n\n"
         "- `int nilaiAkhir()` (final mark) — (30 × tugas + 30 × uts + 40 × uas) / 100, with integer division;\n- `bool lulus()` (passed) — true when the final mark is 60 or more. This method **calls another method** of the same object.\n\n"
         "Keep n students in an **array of objects**. Print the name and final mark of every student who passed, in input order, then `Lulus: k dari n` (k of n passed).\n\n"
         "Example: `Andi 80 70 60` and `Budi 40 50 55` →\n\n```\nAndi 69\nLulus: 1 dari 2\n```" + W_EN,
         "First line: n. Then n lines, each with the name and the three marks.", "One line `name finalMark` for every student who passed, then `Lulus: k dari n`.", "1 ≤ n ≤ 20; each mark is between 0 and 100"),
     ar=("قائمة الناجحين", "أنشئ صنفًا `Mahasiswa` (طالب) خصائصه `string nama` و `int tugas` و `int uts` و `int uas` (الاسم ودرجات الواجبات ومنتصف الفصل والنهائي) وطريقتان:\n\n"
         "- `int nilaiAkhir()` (الدرجة النهائية) — (30 × tugas + 30 × uts + 40 × uas) / 100 بقسمة صحيحة؛\n- `bool lulus()` (ناجح) — صحيحة إذا كانت الدرجة النهائية 60 أو أكثر. هذه الطريقة **تستدعي طريقة أخرى** للكائن نفسه.\n\n"
         "احفظ n طالبًا في **مصفوفة كائنات**. اطبع اسم كل ناجح ودرجته النهائية بترتيب المدخل ثم `Lulus: k dari n` (نجح k من n).\n\n"
         "مثال: `Andi 80 70 60` و `Budi 40 50 55` ←\n\n```\nAndi 69\nLulus: 1 dari 2\n```" + W_AR,
         "السطر الأول: n. ثم n سطرًا في كل منها الاسم والدرجات الثلاث.", "سطر `الاسم الدرجة` لكل ناجح ثم `Lulus: k dari n`.", "1 ≤ n ≤ 20؛ كل درجة بين 0 و 100"))

soal("persegi-panjang-terluas", "mudah_sedang", "trimmed",
     "n = {2: 1}.get(i, r.randint(2, 20))\nv = [(r.randint(1, 50), r.randint(1, 50)) for _ in range(n)]\nif i == 3:\n    v = [(4, 6), (3, 8), (2, 12), (24, 1)]\nprint(len(v))\nfor p, l in v:\n    print(p, l)\n",
     program(r'''
class PersegiPanjang {
public:
    int panjang;
    int lebar;

    int luas() {
        return panjang * lebar;
    }

    bool lebihLuasDari(PersegiPanjang lain) {
        return luas() > lain.luas();
    }
};''', r'''    int n;
    cin >> n;
    PersegiPanjang daftar[20];
    for (int k = 0; k < n; k++) {
        cin >> daftar[k].panjang >> daftar[k].lebar;
    }
    auto terluas = 0;
    for (int k = 1; k < n; k++) {
        if (daftar[k].lebihLuasDari(daftar[terluas])) {
            terluas = k;
        }
    }
    cout << terluas + 1 << " " << daftar[terluas].luas() << endl;'''), wajib=W,
     id=("Persegi Panjang Terluas", "Buat class `PersegiPanjang` dengan atribut `int panjang`, `int lebar` dan dua method:\n\n- `int luas()`;\n- `bool lebihLuasDari(PersegiPanjang lain)` — benar bila objek ini lebih luas daripada objek `lain`.\n\n"
         "Baca n persegi panjang ke dalam array of objects, lalu cetak **nomor urut** (mulai dari 1) persegi panjang yang terluas dan luasnya. Bila ada beberapa yang sama luas, pilih yang paling dulu.\n\n"
         "Contoh: `4 6`, `3 9`, `5 5` → `2 27`." + W_ID,
         "Baris pertama: n. Lalu n baris, masing-masing berisi panjang dan lebar.", "Satu baris: nomor urut persegi panjang terluas, lalu luasnya.", "1 ≤ n ≤ 20; 1 ≤ panjang, lebar ≤ 50"),
     en=("Largest Rectangle", "Make a class `PersegiPanjang` (rectangle) with the attributes `int panjang`, `int lebar` and two methods:\n\n- `int luas()` (area);\n- `bool lebihLuasDari(PersegiPanjang lain)` — true when this object is larger in area than the object `lain`.\n\n"
         "Read n rectangles into an array of objects, then print the **position** (counting from 1) of the largest one and its area. If several are equally large, choose the earliest.\n\n"
         "Example: `4 6`, `3 9`, `5 5` → `2 27`." + W_EN,
         "First line: n. Then n lines, each with a length and a width.", "One line: the position of the largest rectangle, then its area.", "1 ≤ n ≤ 20; 1 ≤ length, width ≤ 50"),
     ar=("أوسع مستطيل", "أنشئ صنفًا `PersegiPanjang` (مستطيل) خاصيتاه `int panjang` و `int lebar` وطريقتان:\n\n- `int luas()` (المساحة)؛\n- `bool lebihLuasDari(PersegiPanjang lain)` — صحيحة إذا كان هذا الكائن أوسع من الكائن `lain`.\n\n"
         "اقرأ n مستطيلًا في مصفوفة كائنات ثم اطبع **ترتيب** (بدءًا من 1) أوسعها ومساحته. إذا تساوت عدة مستطيلات فاختر أسبقها.\n\n"
         "مثال: `4 6` و `3 9` و `5 5` ← `2 27`." + W_AR,
         "السطر الأول: n. ثم n سطرًا في كل منها طول وعرض.", "سطر واحد: ترتيب أوسع مستطيل ثم مساحته.", "1 ≤ n ≤ 20؛ 1 ≤ الطول، العرض ≤ 50"))

soal("senjata", "mudah_sedang", "trimmed",
     "kap = r.randint(1, 8)\nn = r.randint(1, 30)\np = {2: 1.0, 3: 0.5}.get(i, 0.8)\nprint(r.choice(['Pindad', 'Senapan', 'Pistol', 'Karabin']), kap)\nprint(''.join('T' if r.random() < p else 'R' for _ in range(n)))\n",
     program(r'''
class Senjata {
public:
    string nama;
    int kapasitas;
    int peluru;

    bool tembak() {
        if (peluru == 0) {
            return false;
        }
        peluru--;
        return true;
    }

    void isiUlang() {
        peluru = kapasitas;
    }
};''', r'''    Senjata s;
    string perintah;
    cin >> s.nama >> s.kapasitas;
    cin >> perintah;
    s.isiUlang();
    for (auto p : perintah) {
        if (p == 'T') {
            if (s.tembak()) {
                cout << "Dor! sisa " << s.peluru << endl;
            } else {
                cout << "Klik! peluru habis" << endl;
            }
        } else {
            s.isiUlang();
            cout << "Isi ulang: " << s.peluru << endl;
        }
    }
    cout << s.nama << ": " << s.peluru << "/" << s.kapasitas << endl;''', "string"), wajib=W,
     id=("Senjata", "Buat class `Senjata` dengan atribut `string nama`, `int kapasitas`, `int peluru` dan dua method:\n\n- `bool tembak()` — bila masih ada peluru, kurangi satu dan kembalikan `true`; bila habis, kembalikan `false`;\n- `void isiUlang()` — mengisi peluru sampai penuh.\n\n"
         "Senjata mula-mula terisi penuh. Masukan berisi deretan perintah: `T` berarti tembak, `R` berarti isi ulang. Untuk tiap perintah cetak satu baris:\n\n"
         "- tembakan berhasil: `Dor! sisa k`;\n- peluru habis: `Klik! peluru habis`;\n- isi ulang: `Isi ulang: k`.\n\nDi akhir cetak `<nama>: <peluru>/<kapasitas>`.\n\n"
         "Contoh: `Pindad 2` dengan perintah `TTTRT` →\n\n```\nDor! sisa 1\nDor! sisa 0\nKlik! peluru habis\nIsi ulang: 2\nDor! sisa 1\nPindad: 1/2\n```" + W_ID,
         "Baris pertama: nama senjata (satu kata) dan kapasitasnya. Baris kedua: deretan huruf `T` dan `R`.", "Satu baris per perintah, lalu satu baris ringkasan.", "1 ≤ kapasitas ≤ 8; panjang deretan perintah 1 sampai 30"),
     en=("Weapon", "Make a class `Senjata` (weapon) with the attributes `string nama`, `int kapasitas`, `int peluru` (name, capacity, rounds) and two methods:\n\n- `bool tembak()` (fire) — if a round is left, use one and return `true`; if empty, return `false`;\n- `void isiUlang()` (reload) — fills the weapon up.\n\n"
         "The weapon starts full. The input holds a run of commands: `T` means fire, `R` means reload. For each command print one line:\n\n"
         "- a shot fired: `Dor! sisa k` (k left);\n- no rounds: `Klik! peluru habis`;\n- reload: `Isi ulang: k`.\n\nAt the end print `<name>: <rounds>/<capacity>`.\n\n"
         "Example: `Pindad 2` with the commands `TTTRT` →\n\n```\nDor! sisa 1\nDor! sisa 0\nKlik! peluru habis\nIsi ulang: 2\nDor! sisa 1\nPindad: 1/2\n```" + W_EN,
         "First line: the weapon's name (one word) and its capacity. Second line: a run of the letters `T` and `R`.", "One line per command, then one summary line.", "1 ≤ capacity ≤ 8; the run of commands is 1 to 30 long"),
     ar=("السلاح", "أنشئ صنفًا `Senjata` (سلاح) خصائصه `string nama` و `int kapasitas` و `int peluru` (الاسم والسعة والطلقات) وطريقتان:\n\n- `bool tembak()` (أطلق) — إذا بقيت طلقة فاستعمل واحدة وأرجع `true`؛ وإذا فرغ فأرجع `false`؛\n- `void isiUlang()` (أعد التعبئة) — تملأ السلاح.\n\n"
         "يبدأ السلاح ممتلئًا. في المدخل سلسلة أوامر: `T` تعني أطلق و `R` تعني أعد التعبئة. لكل أمر اطبع سطرًا:\n\n"
         "- طلقة ناجحة: `Dor! sisa k` (بقي k)؛\n- لا طلقات: `Klik! peluru habis`؛\n- إعادة تعبئة: `Isi ulang: k`.\n\nفي النهاية اطبع `<الاسم>: <الطلقات>/<السعة>`.\n\n"
         "مثال: `Pindad 2` مع الأوامر `TTTRT` ←\n\n```\nDor! sisa 1\nDor! sisa 0\nKlik! peluru habis\nIsi ulang: 2\nDor! sisa 1\nPindad: 1/2\n```" + W_AR,
         "السطر الأول: اسم السلاح (كلمة واحدة) وسعته. السطر الثاني: سلسلة من الحرفين `T` و `R`.", "سطر لكل أمر ثم سطر ملخص.", "1 ≤ السعة ≤ 8؛ طول سلسلة الأوامر من 1 إلى 30"))

soal("operasi-vektor", "mudah_sedang", "trimmed", "print(r.randint(-50, 50), r.randint(-50, 50))\nprint(r.randint(-50, 50), r.randint(-50, 50))\nprint({2: 0, 3: -1}.get(i, r.randint(-10, 10)))\n",
     program(r'''
class Vektor {
public:
    int x;
    int y;

    Vektor tambah(Vektor lain) {
        return Vektor{x + lain.x, y + lain.y};
    }

    Vektor kali(int k) {
        return Vektor{x * k, y * k};
    }

    int titik(Vektor lain) {
        return x * lain.x + y * lain.y;
    }
};''', r'''    Vektor a{}, b{};
    int k;
    cin >> a.x >> a.y;
    cin >> b.x >> b.y;
    cin >> k;
    auto c = a.tambah(b.kali(k));
    cout << c.x << " " << c.y << endl;
    cout << a.titik(b) << endl;'''), wajib=W,
     id=("Operasi Vektor", "Buat class `Vektor` dengan atribut `int x`, `int y` dan tiga method. Dua di antaranya **mengembalikan objek `Vektor` baru** tanpa mengubah objek asalnya:\n\n"
         "- `Vektor tambah(Vektor lain)` — jumlah dua vektor;\n- `Vektor kali(int k)` — vektor dikali bilangan k;\n- `int titik(Vektor lain)` — hasil kali titik: x × lain.x + y × lain.y.\n\n"
         "Baca vektor a, vektor b, dan bilangan k. Cetak vektor a + k·b di baris pertama, lalu hasil kali titik a dan b di baris kedua.\n\n"
         "Contoh: a = (1, 2), b = (3, −1), k = 2 → `7 0` lalu `1`." + W_ID,
         "Baris pertama: x dan y vektor a. Baris kedua: x dan y vektor b. Baris ketiga: k.", "Baris pertama: komponen vektor a + k·b. Baris kedua: hasil kali titik a dan b.", "Tiap komponen antara −50 dan 50; −10 ≤ k ≤ 10"),
     en=("Vector Operations", "Make a class `Vektor` with the attributes `int x`, `int y` and three methods. Two of them **return a new `Vektor` object** without changing the object they are called on:\n\n"
         "- `Vektor tambah(Vektor lain)` — the sum of two vectors;\n- `Vektor kali(int k)` — the vector times a number k;\n- `int titik(Vektor lain)` — the dot product: x × lain.x + y × lain.y.\n\n"
         "Read a vector a, a vector b and a number k. Print the vector a + k·b on the first line, then the dot product of a and b on the second.\n\n"
         "Example: a = (1, 2), b = (3, −1), k = 2 → `7 0` then `1`." + W_EN,
         "First line: x and y of a. Second line: x and y of b. Third line: k.", "First line: the components of a + k·b. Second line: the dot product of a and b.", "Each component is between −50 and 50; −10 ≤ k ≤ 10"),
     ar=("عمليات المتجهات", "أنشئ صنفًا `Vektor` خاصيتاه `int x` و `int y` وثلاث طرائق. اثنتان منها **تُرجعان كائن `Vektor` جديدًا** دون تغيير الكائن الأصلي:\n\n"
         "- `Vektor tambah(Vektor lain)` — مجموع متجهين؛\n- `Vektor kali(int k)` — المتجه مضروبًا في العدد k؛\n- `int titik(Vektor lain)` — الضرب النقطي: x × lain.x + y × lain.y.\n\n"
         "اقرأ المتجه a والمتجه b والعدد k. اطبع المتجه a + k·b في السطر الأول ثم الضرب النقطي للمتجهين a و b في السطر الثاني.\n\n"
         "مثال: a = (1, 2) و b = (3, −1) و k = 2 ← `7 0` ثم `1`." + W_AR,
         "السطر الأول: x و y للمتجه a. السطر الثاني: x و y للمتجه b. السطر الثالث: k.", "السطر الأول: مركّبتا a + k·b. السطر الثاني: الضرب النقطي للمتجهين.", "كل مركّبة بين −50 و 50؛ −10 ≤ k ≤ 10"))

soal("jam-berdetak", "mudah_sedang", "trimmed", "j, m, d, n = r.randint(0, 23), r.randint(0, 59), r.randint(0, 59), r.randint(0, 100000)\nif i == 2:\n    j, m, d, n = 23, 59, 59, 1\nif i == 3:\n    n = 86400\nif i == 4:\n    j, m, d, n = 9, 59, 58, 3\nprint(j, m, d, n)\n",
     program(r'''
class Jam {
public:
    int jam;
    int menit;
    int detik;

    void detak() {
        detik++;
        if (detik == 60) {
            detik = 0;
            menit++;
        }
        if (menit == 60) {
            menit = 0;
            jam++;
        }
        if (jam == 24) {
            jam = 0;
        }
    }

    void cetak() {
        cout << setfill('0') << setw(2) << jam << ":" << setw(2) << menit << ":" << setw(2) << detik << endl;
    }
};''', r'''    Jam j;
    int n;
    cin >> j.jam >> j.menit >> j.detik >> n;
    for (int k = 0; k < n; k++) {
        j.detak();
    }
    j.cetak();''', "iomanip"), wajib=W,
     id=("Jam Berdetak", "Buat class `Jam` dengan atribut `int jam`, `int menit`, `int detik` dan dua method:\n\n- `void detak()` — memajukan jam **satu detik**, lengkap dengan perpindahan menit, jam, dan tengah malam;\n- `void cetak()` — mencetak `JJ:MM:DD`, masing-masing dua digit.\n\n"
         "Baca waktu awal dan n, panggil `detak()` sebanyak n kali, lalu cetak waktunya.\n\nContoh: `9 59 58 3` → `10:00:01`; `23 59 59 1` → `00:00:00`." + W_ID,
         "Satu baris berisi jam, menit, detik, dan n.", "Waktu setelah n detik, dalam bentuk `JJ:MM:DD`.", "0 ≤ jam ≤ 23; 0 ≤ menit, detik ≤ 59; 0 ≤ n ≤ 100000"),
     en=("Ticking Clock", "Make a class `Jam` (clock) with the attributes `int jam`, `int menit`, `int detik` (hour, minute, second) and two methods:\n\n- `void detak()` (tick) — moves the clock forward by **one second**, including the roll-over of minutes, hours and midnight;\n- `void cetak()` — prints `HH:MM:SS`, two digits each.\n\n"
         "Read the starting time and n, call `detak()` n times, then print the time.\n\nExamples: `9 59 58 3` → `10:00:01`; `23 59 59 1` → `00:00:00`." + W_EN,
         "One line with the hour, the minute, the second and n.", "The time after n seconds, in the form `HH:MM:SS`.", "0 ≤ hour ≤ 23; 0 ≤ minute, second ≤ 59; 0 ≤ n ≤ 100000"),
     ar=("ساعة تدقّ", "أنشئ صنفًا `Jam` (ساعة) خصائصه `int jam` و `int menit` و `int detik` (الساعة والدقيقة والثانية) وطريقتان:\n\n- `void detak()` (دقّة) — تقدّم الساعة **ثانية واحدة** مع انتقال الدقائق والساعات ومنتصف الليل؛\n- `void cetak()` — تطبع `HH:MM:SS` برقمين لكل جزء.\n\n"
         "اقرأ الوقت الابتدائي و n واستدعِ `detak()` n مرة ثم اطبع الوقت.\n\nأمثلة: `9 59 58 3` ← `10:00:01`؛ `23 59 59 1` ← `00:00:00`." + W_AR,
         "سطر واحد فيه الساعة والدقيقة والثانية و n.", "الوقت بعد n ثانية على الصورة `HH:MM:SS`.", "0 ≤ الساعة ≤ 23؛ 0 ≤ الدقيقة، الثانية ≤ 59؛ 0 ≤ n ≤ 100000"))

soal("nilai-gudang", "mudah_sedang", "trimmed",
     BARANG + "n = {2: 1}.get(i, r.randint(2, 20))\nprint(n)\nfor nama in r.sample(BARANG, n):\n    print(nama, r.randint(1, 100) * 500, r.randint(0, 50))\n",
     program(r'''
class Barang {
public:
    string nama;
    int harga;
    int stok;

    int nilai() {
        return harga * stok;
    }
};''', r'''    int n;
    cin >> n;
    Barang gudang[20];
    auto total = 0;
    auto termahal = 0;
    for (int k = 0; k < n; k++) {
        cin >> gudang[k].nama >> gudang[k].harga >> gudang[k].stok;
        total += gudang[k].nilai();
        if (gudang[k].nilai() > gudang[termahal].nilai()) {
            termahal = k;
        }
    }
    cout << "Total: " << total << endl;
    cout << "Terbesar: " << gudang[termahal].nama << endl;''', "string"), wajib=W,
     id=("Nilai Gudang", "Buat class `Barang` dengan atribut `string nama`, `int harga`, `int stok` dan method `int nilai()` yang mengembalikan harga × stok.\n\n"
         "Sebuah gudang menyimpan n jenis barang. Cetak dua baris:\n\n- `Total: T` — jumlah nilai semua barang;\n- `Terbesar: <nama>` — barang yang nilainya paling besar; bila ada yang sama, yang paling dulu di masukan.\n\n"
         "Contoh: `Beras 12000 10`, `Gula 15000 4`, `Kopi 40000 3` →\n\n```\nTotal: 300000\nTerbesar: Beras\n```" + W_ID,
         "Baris pertama: n. Lalu n baris, masing-masing berisi nama (satu kata), harga, dan stok.", "Dua baris seperti contoh.", "1 ≤ n ≤ 20; 500 ≤ harga ≤ 50000; 0 ≤ stok ≤ 50"),
     en=("Warehouse Value", "Make a class `Barang` (item) with the attributes `string nama`, `int harga`, `int stok` (name, price, stock) and a method `int nilai()` (value) that returns price × stock.\n\n"
         "A warehouse keeps n kinds of items. Print two lines:\n\n- `Total: T` — the summed value of all items;\n- `Terbesar: <name>` — the item with the largest value; on a tie, the earliest in the input.\n\n"
         "Example: `Beras 12000 10`, `Gula 15000 4`, `Kopi 40000 3` →\n\n```\nTotal: 300000\nTerbesar: Beras\n```" + W_EN,
         "First line: n. Then n lines, each with a name (one word), a price and a stock.", "Two lines as in the example.", "1 ≤ n ≤ 20; 500 ≤ price ≤ 50000; 0 ≤ stock ≤ 50"),
     ar=("قيمة المستودع", "أنشئ صنفًا `Barang` (سلعة) خصائصه `string nama` و `int harga` و `int stok` (الاسم والسعر والمخزون) وطريقة `int nilai()` (القيمة) تُرجع السعر × المخزون.\n\n"
         "في مستودع n نوعًا من السلع. اطبع سطرين:\n\n- `Total: T` — مجموع قيم كل السلع؛\n- `Terbesar: <الاسم>` — السلعة ذات أكبر قيمة؛ وعند التساوي الأسبق في المدخل.\n\n"
         "مثال: `Beras 12000 10` و `Gula 15000 4` و `Kopi 40000 3` ←\n\n```\nTotal: 300000\nTerbesar: Beras\n```" + W_AR,
         "السطر الأول: n. ثم n سطرًا في كل منها اسم (كلمة واحدة) وسعر ومخزون.", "سطران كما في المثال.", "1 ≤ n ≤ 20؛ 500 ≤ السعر ≤ 50000؛ 0 ≤ المخزون ≤ 50"))

# ================================ SEDANG (6) ================================

soal("matriks-pangkat", "sedang", "trimmed", "v = [r.randint(0, 3) for _ in range(4)]\nn = r.randint(1, 10)\nif i == 2:\n    v, n = [1, 1, 1, 0], 10\nif i == 3:\n    v, n = [3, 3, 3, 3], 10\nif i == 4:\n    n = 1\nprint(*v)\nprint(n)\n",
     program(r'''
class Matriks2 {
public:
    long long a, b, c, d;

    Matriks2 kali(Matriks2 lain) {
        return Matriks2{a * lain.a + b * lain.c, a * lain.b + b * lain.d,
                        c * lain.a + d * lain.c, c * lain.b + d * lain.d};
    }

    long long determinan() {
        return a * d - b * c;
    }
};''', r'''    Matriks2 m{};
    int n;
    cin >> m.a >> m.b >> m.c >> m.d;
    cin >> n;
    auto hasil = m;
    for (int k = 1; k < n; k++) {
        hasil = hasil.kali(m);
    }
    cout << hasil.a << " " << hasil.b << endl;
    cout << hasil.c << " " << hasil.d << endl;
    cout << hasil.determinan() << endl;'''), wajib=W,
     id=("Pangkat Matriks 2 × 2", "Buat class `Matriks2` yang mewakili matriks 2 × 2\n\n```\na b\nc d\n```\n\ndengan atribut `long long a, b, c, d` dan dua method:\n\n"
         "- `Matriks2 kali(Matriks2 lain)` — hasil kali matriks ini dengan `lain` (matriks ini di sebelah kiri), sebagai objek baru;\n- `long long determinan()` — a × d − b × c.\n\n"
         "Hasil kali dua matriks 2 × 2:\n\n```\na·a' + b·c'   a·b' + b·d'\nc·a' + d·c'   c·b' + d·d'\n```\n\n"
         "Baca sebuah matriks M dan bilangan n. Cetak Mⁿ (M dikalikan dengan dirinya n kali) dalam dua baris, lalu determinannya di baris ketiga.\n\n"
         "Contoh: M = `1 1` / `1 0` dan n = 5 →\n\n```\n8 5\n5 3\n-1\n```" + W_ID,
         "Baris pertama: a, b, c, d. Baris kedua: n.", "Dua baris isi Mⁿ, lalu satu baris determinannya.", "0 ≤ a, b, c, d ≤ 3; 1 ≤ n ≤ 10"),
     en=("Power of a 2 × 2 Matrix", "Make a class `Matriks2` that stands for a 2 × 2 matrix\n\n```\na b\nc d\n```\n\nwith the attributes `long long a, b, c, d` and two methods:\n\n"
         "- `Matriks2 kali(Matriks2 lain)` — the product of this matrix and `lain` (this matrix on the left), as a new object;\n- `long long determinan()` — a × d − b × c.\n\n"
         "The product of two 2 × 2 matrices:\n\n```\na·a' + b·c'   a·b' + b·d'\nc·a' + d·c'   c·b' + d·d'\n```\n\n"
         "Read a matrix M and a number n. Print Mⁿ (M multiplied by itself n times) on two lines, then its determinant on a third.\n\n"
         "Example: M = `1 1` / `1 0` and n = 5 →\n\n```\n8 5\n5 3\n-1\n```" + W_EN,
         "First line: a, b, c, d. Second line: n.", "Two lines with Mⁿ, then one line with its determinant.", "0 ≤ a, b, c, d ≤ 3; 1 ≤ n ≤ 10"),
     ar=("قوة مصفوفة 2 × 2", "أنشئ صنفًا `Matriks2` يمثّل مصفوفة 2 × 2\n\n```\na b\nc d\n```\n\nخصائصه `long long a, b, c, d` وطريقتان:\n\n"
         "- `Matriks2 kali(Matriks2 lain)` — حاصل ضرب هذه المصفوفة في `lain` (هذه المصفوفة على اليسار) كائنًا جديدًا؛\n- `long long determinan()` — a × d − b × c.\n\n"
         "حاصل ضرب مصفوفتين 2 × 2:\n\n```\na·a' + b·c'   a·b' + b·d'\nc·a' + d·c'   c·b' + d·d'\n```\n\n"
         "اقرأ مصفوفة M وعددًا n. اطبع Mⁿ (M مضروبة في نفسها n مرة) في سطرين ثم محدِّدها في سطر ثالث.\n\n"
         "مثال: M = `1 1` / `1 0` و n = 5 ←\n\n```\n8 5\n5 3\n-1\n```" + W_AR,
         "السطر الأول: a و b و c و d. السطر الثاني: n.", "سطران فيهما Mⁿ ثم سطر فيه محدِّدها.", "0 ≤ a, b, c, d ≤ 3؛ 1 ≤ n ≤ 10"))

soal("kelas-tumpukan", "sedang", "trimmed",
     "m = r.randint(1, 40)\np = {2: 0.3, 3: 0.9}.get(i, 0.55)\nfor _ in range(m):\n    q = r.random()\n    if q < p:\n        print('dorong', r.randint(-99, 99))\n    elif q < p + (1 - p) * 0.6:\n        print('ambil')\n    else:\n        print('puncak')\nprint('selesai')\n",
     program(r'''
class Tumpukan {
public:
    int data[100];
    int banyak = 0;

    bool kosong() {
        return banyak == 0;
    }

    void dorong(int x) {
        data[banyak] = x;
        banyak++;
    }

    int ambil() {
        banyak--;
        return data[banyak];
    }

    int puncak() {
        return data[banyak - 1];
    }
};''', r'''    Tumpukan t;
    string perintah;
    while (cin >> perintah) {
        if (perintah == "selesai") {
            break;
        }
        if (perintah == "dorong") {
            int x;
            cin >> x;
            t.dorong(x);
        } else if (t.kosong()) {
            cout << "kosong" << endl;
        } else if (perintah == "ambil") {
            cout << t.ambil() << endl;
        } else {
            cout << t.puncak() << endl;
        }
    }
    cout << "Isi: " << t.banyak << endl;''', "string"), wajib=W,
     id=("Class Tumpukan", "Tumpukan (*stack*) dari mata kuliah Struktur Data sekarang dibungkus menjadi class. Buat class `Tumpukan` dengan atribut `int data[100]` dan `int banyak` (mula-mula 0), serta method:\n\n"
         "- `void dorong(int x)` — menaruh x di puncak;\n- `int ambil()` — mengambil dan mengembalikan elemen puncak;\n- `int puncak()` — mengembalikan elemen puncak tanpa mengambilnya;\n- `bool kosong()`.\n\n"
         "Perintah datang satu per baris: `dorong x`, `ambil`, `puncak`, dan terakhir `selesai`. Untuk `ambil` dan `puncak` cetak nilainya, atau `kosong` bila tumpukannya kosong. Di akhir cetak `Isi: k`.\n\n"
         "Contoh: `dorong 5`, `dorong 8`, `puncak`, `ambil`, `ambil`, `ambil`, `selesai` →\n\n```\n8\n8\n5\nkosong\nIsi: 0\n```" + W_ID,
         "Beberapa baris perintah, diakhiri `selesai`.", "Satu baris untuk tiap `ambil` dan `puncak`, lalu `Isi: k`.", "Paling banyak 40 perintah sebelum `selesai`; −99 ≤ x ≤ 99"),
     en=("The Tumpukan Class", "The stack from the Data Structures course is now wrapped into a class. Make a class `Tumpukan` (stack) with the attributes `int data[100]` and `int banyak` (count, starting at 0), and the methods:\n\n"
         "- `void dorong(int x)` (push) — puts x on top;\n- `int ambil()` (pop) — removes and returns the top element;\n- `int puncak()` (top) — returns the top element without removing it;\n- `bool kosong()` (empty).\n\n"
         "Commands come one per line: `dorong x`, `ambil`, `puncak`, and finally `selesai`. For `ambil` and `puncak` print the value, or `kosong` when the stack is empty. At the end print `Isi: k` (contents).\n\n"
         "Example: `dorong 5`, `dorong 8`, `puncak`, `ambil`, `ambil`, `ambil`, `selesai` →\n\n```\n8\n8\n5\nkosong\nIsi: 0\n```" + W_EN,
         "Several command lines, ending with `selesai`.", "One line for every `ambil` and `puncak`, then `Isi: k`.", "At most 40 commands before `selesai`; −99 ≤ x ≤ 99"),
     ar=("الصنف Tumpukan", "المكدّس (*stack*) من مقرر هياكل البيانات يُغلَّف الآن في صنف. أنشئ صنفًا `Tumpukan` (مكدّس) خاصيتاه `int data[100]` و `int banyak` (العدد، ويبدأ من 0) وطرائقه:\n\n"
         "- `void dorong(int x)` (ادفع) — تضع x في القمة؛\n- `int ambil()` (اسحب) — تزيل عنصر القمة وتُرجعه؛\n- `int puncak()` (القمة) — تُرجع عنصر القمة دون إزالته؛\n- `bool kosong()` (فارغ).\n\n"
         "تأتي الأوامر أمرًا في كل سطر: `dorong x` و `ambil` و `puncak` وأخيرًا `selesai`. للأمرين `ambil` و `puncak` اطبع القيمة، أو `kosong` إذا كان المكدّس فارغًا. في النهاية اطبع `Isi: k` (المحتوى).\n\n"
         "مثال: `dorong 5` ثم `dorong 8` ثم `puncak` ثم `ambil` ثم `ambil` ثم `ambil` ثم `selesai` ←\n\n```\n8\n8\n5\nkosong\nIsi: 0\n```" + W_AR,
         "عدة أسطر أوامر تنتهي بـ `selesai`.", "سطر لكل `ambil` و `puncak` ثم `Isi: k`.", "40 أمرًا على الأكثر قبل `selesai`؛ −99 ≤ x ≤ 99"))

soal("kelas-polinom", "sedang", "trimmed", "d = {2: 0, 3: 8}.get(i, r.randint(1, 8))\nk = [r.choice([-1, 1]) * r.randint(1, 9)] + [r.randint(-9, 9) for _ in range(d)]\nprint(d)\nprint(*k)\nprint({4: 0, 5: -5}.get(i, r.randint(-5, 5)))\n",
     program(r'''
class Polinom {
public:
    int derajat;
    int koef[10];

    long long nilai(int x) {
        long long hasil = 0;
        for (int k = derajat; k >= 0; k--) {
            hasil = hasil * x + koef[k];
        }
        return hasil;
    }

    Polinom turunan() {
        Polinom t;
        t.derajat = derajat - 1;
        if (t.derajat < 0) {
            t.derajat = 0;
            t.koef[0] = 0;
        }
        for (int k = 1; k <= derajat; k++) {
            t.koef[k - 1] = k * koef[k];
        }
        return t;
    }
};''', r'''    Polinom p;
    int x;
    cin >> p.derajat;
    for (int k = p.derajat; k >= 0; k--) {
        cin >> p.koef[k];
    }
    cin >> x;
    cout << p.nilai(x) << endl;
    cout << p.turunan().nilai(x) << endl;'''), wajib=W,
     id=("Class Polinom", "Sebuah polinom seperti 2x³ − x + 5 ditentukan oleh derajatnya dan koefisien-koefisiennya. Buat class `Polinom` dengan atribut `int derajat`, `int koef[10]` (`koef[k]` adalah koefisien xᵏ), dan dua method:\n\n"
         "- `long long nilai(int x)` — nilai polinom di x;\n- `Polinom turunan()` — polinom turunannya, sebagai objek baru. Turunan dari a·xᵏ adalah k·a·xᵏ⁻¹; turunan dari bilangan tetap adalah 0.\n\n"
         "Masukan: derajat d, lalu d + 1 koefisien **dari pangkat tertinggi ke terendah**, lalu x. Cetak P(x), lalu P′(x).\n\n"
         "Contoh: derajat 3, koefisien `2 0 -1 5`, x = 2. P(2) = 16 − 2 + 5 = 19. Turunannya 6x² − 1, jadi P′(2) = 23. Keluarannya `19` lalu `23`." + W_ID,
         "Baris pertama: d. Baris kedua: d + 1 koefisien. Baris ketiga: x.", "Dua baris: P(x), lalu P′(x).", "0 ≤ d ≤ 8; tiap koefisien antara −9 dan 9; koefisien pertama bukan nol; −5 ≤ x ≤ 5"),
     en=("The Polinom Class", "A polynomial such as 2x³ − x + 5 is fixed by its degree and its coefficients. Make a class `Polinom` with the attributes `int derajat` (degree), `int koef[10]` (`koef[k]` is the coefficient of xᵏ), and two methods:\n\n"
         "- `long long nilai(int x)` — the value of the polynomial at x;\n- `Polinom turunan()` — its derivative, as a new object. The derivative of a·xᵏ is k·a·xᵏ⁻¹; the derivative of a constant is 0.\n\n"
         "Input: the degree d, then d + 1 coefficients **from the highest power to the lowest**, then x. Print P(x), then P′(x).\n\n"
         "Example: degree 3, coefficients `2 0 -1 5`, x = 2. P(2) = 16 − 2 + 5 = 19. The derivative is 6x² − 1, so P′(2) = 23. The output is `19` then `23`." + W_EN,
         "First line: d. Second line: d + 1 coefficients. Third line: x.", "Two lines: P(x), then P′(x).", "0 ≤ d ≤ 8; each coefficient is between −9 and 9; the first coefficient is not zero; −5 ≤ x ≤ 5"),
     ar=("الصنف Polinom", "كثير حدود مثل 2x³ − x + 5 يتحدد بدرجته ومعاملاته. أنشئ صنفًا `Polinom` خاصيتاه `int derajat` (الدرجة) و `int koef[10]` (`koef[k]` معامل xᵏ) وطريقتان:\n\n"
         "- `long long nilai(int x)` — قيمة كثير الحدود عند x؛\n- `Polinom turunan()` — مشتقته كائنًا جديدًا. مشتقة a·xᵏ هي k·a·xᵏ⁻¹، ومشتقة الثابت 0.\n\n"
         "المدخل: الدرجة d ثم d + 1 معاملًا **من أعلى أُسّ إلى أدناه** ثم x. اطبع P(x) ثم P′(x).\n\n"
         "مثال: الدرجة 3 والمعاملات `2 0 -1 5` و x = 2. P(2) = 16 − 2 + 5 = 19. المشتقة 6x² − 1، فيكون P′(2) = 23. المخرج `19` ثم `23`." + W_AR,
         "السطر الأول: d. السطر الثاني: d + 1 معاملًا. السطر الثالث: x.", "سطران: P(x) ثم P′(x).", "0 ≤ d ≤ 8؛ كل معامل بين −9 و 9؛ المعامل الأول ليس صفرًا؛ −5 ≤ x ≤ 5"))

soal("nama-hari-tanggal", "sedang", "trimmed",
     "import datetime\nt = datetime.date(1900, 1, 1) + datetime.timedelta(days=r.randint(0, 73413))\nif i == 2:\n    t = datetime.date(1900, 1, 1)\nif i == 3:\n    t = datetime.date(2024, 2, 29)\nif i == 4:\n    t = datetime.date(2100, 12, 31)\nif i == 5:\n    t = datetime.date(1945, 8, 17)\nprint(t.day, t.month, t.year)\n",
     program(r'''
class Tanggal {
public:
    int hari;
    int bulan;
    int tahun;

    static bool kabisat(int t) {
        return t % 400 == 0 || (t % 4 == 0 && t % 100 != 0);
    }

    int hariKe() {
        int hariBulan[12]{31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31};
        if (kabisat(tahun)) {
            hariBulan[1] = 29;
        }
        auto jumlah = hari;
        for (int b = 0; b < bulan - 1; b++) {
            jumlah += hariBulan[b];
        }
        return jumlah;
    }

    string namaHari() {
        auto total = hariKe() - 1;
        for (int t = 1900; t < tahun; t++) {
            total += 365;
            if (kabisat(t)) {
                total++;
            }
        }
        string nama[7]{"Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu", "Minggu"};
        return nama[total % 7];
    }
};''', r'''    Tanggal t;
    cin >> t.hari >> t.bulan >> t.tahun;
    cout << t.namaHari() << endl;''', "string"), wajib=W,
     id=("Hari Apa?", "Buat class `Tanggal` dengan atribut `int hari`, `int bulan`, `int tahun` dan method:\n\n"
         "- `static bool kabisat(int tahun)` — tahun kabisat: habis dibagi 400, atau habis dibagi 4 tetapi tidak habis dibagi 100;\n- `int hariKe()` — tanggal itu hari ke berapa dalam tahunnya (1 Januari adalah hari ke-1);\n- `string namaHari()` — nama harinya: `Senin`, `Selasa`, `Rabu`, `Kamis`, `Jumat`, `Sabtu`, atau `Minggu`.\n\n"
         "Pegangan: **1 Januari 1900 jatuh pada hari Senin**. Hitung berapa hari sudah lewat sejak tanggal itu, lalu ambil sisa pembagiannya dengan 7.\n\n"
         "Baca sebuah tanggal dan cetak nama harinya.\n\nContoh: `17 8 1945` → `Jumat`; `1 1 2000` → `Sabtu`." + W_ID,
         "Satu baris berisi hari, bulan, dan tahun.", "Nama hari.", "Tanggal sah antara 1 Januari 1900 dan 31 Desember 2100."),
     en=("Which Day?", "Make a class `Tanggal` (date) with the attributes `int hari`, `int bulan`, `int tahun` (day, month, year) and the methods:\n\n"
         "- `static bool kabisat(int tahun)` — leap year: divisible by 400, or divisible by 4 but not by 100;\n- `int hariKe()` — which day of its year the date is (1 January is day 1);\n- `string namaHari()` — the name of the weekday in Indonesian: `Senin`, `Selasa`, `Rabu`, `Kamis`, `Jumat`, `Sabtu` or `Minggu` (Monday to Sunday).\n\n"
         "The anchor: **1 January 1900 was a Monday (Senin)**. Count how many days have passed since that date, then take the remainder modulo 7.\n\n"
         "Read a date and print the name of its weekday.\n\nExamples: `17 8 1945` → `Jumat`; `1 1 2000` → `Sabtu`." + W_EN,
         "One line with the day, the month and the year.", "The name of the weekday.", "A valid date between 1 January 1900 and 31 December 2100."),
     ar=("أي يوم؟", "أنشئ صنفًا `Tanggal` (تاريخ) خصائصه `int hari` و `int bulan` و `int tahun` (اليوم والشهر والسنة) وطرائقه:\n\n"
         "- `static bool kabisat(int tahun)` — السنة الكبيسة: تقبل القسمة على 400، أو تقبل القسمة على 4 ولا تقبلها على 100؛\n- `int hariKe()` — ترتيب التاريخ بين أيام سنته (1 يناير هو اليوم الأول)؛\n- `string namaHari()` — اسم يوم الأسبوع بالإندونيسية: `Senin` أو `Selasa` أو `Rabu` أو `Kamis` أو `Jumat` أو `Sabtu` أو `Minggu` (من الاثنين إلى الأحد).\n\n"
         "المرتكز: **1 يناير 1900 كان يوم اثنين (Senin)**. احسب عدد الأيام المنقضية منذ ذلك التاريخ ثم خذ باقي القسمة على 7.\n\n"
         "اقرأ تاريخًا واطبع اسم يومه.\n\nأمثلة: `17 8 1945` ← `Jumat`؛ `1 1 2000` ← `Sabtu`." + W_AR,
         "سطر واحد فيه اليوم والشهر والسنة.", "اسم اليوم.", "تاريخ صحيح بين 1 يناير 1900 و 31 ديسمبر 2100."))

soal("bank-mini", "sedang", "trimmed",
     "n = r.randint(2, 6)\nprint(n)\nprint(*[r.randint(0, 20) * 50000 for _ in range(n)])\nfor _ in range(r.randint(1, 20)):\n    q = r.random()\n    a = r.randint(1, n)\n    if q < 0.3:\n        print('setor', a, r.randint(1, 20) * 10000)\n    elif q < 0.6:\n        print('tarik', a, r.randint(1, 30) * 20000)\n    else:\n        b = r.choice([x for x in range(1, n + 1) if x != a])\n        print('transfer', a, b, r.randint(1, 30) * 20000)\nprint('selesai')\n",
     program(r'''
class Rekening {
public:
    int saldo;

    void setor(int jumlah) {
        saldo += jumlah;
    }

    bool tarik(int jumlah) {
        if (jumlah > saldo) {
            return false;
        }
        saldo -= jumlah;
        return true;
    }

    bool transferKe(Rekening &tujuan, int jumlah) {
        if (!tarik(jumlah)) {
            return false;
        }
        tujuan.setor(jumlah);
        return true;
    }
};''', r'''    int n;
    cin >> n;
    Rekening bank[6];
    for (int k = 0; k < n; k++) {
        cin >> bank[k].saldo;
    }
    string perintah;
    while (cin >> perintah) {
        if (perintah == "selesai") {
            break;
        }
        int a, b, jumlah;
        auto berhasil = true;
        if (perintah == "setor") {
            cin >> a >> jumlah;
            bank[a - 1].setor(jumlah);
        } else if (perintah == "tarik") {
            cin >> a >> jumlah;
            berhasil = bank[a - 1].tarik(jumlah);
        } else {
            cin >> a >> b >> jumlah;
            berhasil = bank[a - 1].transferKe(bank[b - 1], jumlah);
        }
        if (!berhasil) {
            cout << "Gagal" << endl;
        }
    }
    for (int k = 0; k < n; k++) {
        cout << "Rekening " << k + 1 << ": " << bank[k].saldo << endl;
    }''', "string"), wajib=W,
     id=("Bank Mini", "Buat class `Rekening` dengan atribut `int saldo` dan tiga method:\n\n- `void setor(int jumlah)`;\n- `bool tarik(int jumlah)` — gagal (`false`, saldo tidak berubah) bila saldo kurang;\n"
         "- `bool transferKe(Rekening &tujuan, int jumlah)` — menarik dari rekening ini lalu menyetor ke `tujuan`; gagal bila saldo kurang. Parameternya **referensi**, supaya rekening tujuan yang asli ikut berubah, bukan salinannya.\n\n"
         "Sebuah bank punya n rekening bernomor 1 sampai n. Masukan memberi saldo awalnya, lalu perintah satu per baris: `setor a x`, `tarik a x`, `transfer a b x` (dari a ke b), dan terakhir `selesai`.\n\n"
         "Untuk tiap perintah yang gagal cetak `Gagal`. Di akhir cetak saldo semua rekening, satu per baris: `Rekening k: saldo`.\n\n"
         "Contoh: 2 rekening bersaldo `100000 50000`; `tarik 2 80000`, `transfer 1 2 30000`, `selesai` →\n\n```\nGagal\nRekening 1: 70000\nRekening 2: 80000\n```" + W_ID,
         "Baris pertama: n. Baris kedua: n saldo awal. Lalu perintah-perintah, diakhiri `selesai`.", "`Gagal` untuk tiap perintah yang gagal, lalu n baris saldo akhir.", "2 ≤ n ≤ 6; paling banyak 20 perintah; semua jumlah uang antara 0 dan 1000000"),
     en=("Mini Bank", "Make a class `Rekening` (account) with an attribute `int saldo` (balance) and three methods:\n\n- `void setor(int jumlah)` (deposit);\n- `bool tarik(int jumlah)` (withdraw) — fails (`false`, balance unchanged) when the balance is too low;\n"
         "- `bool transferKe(Rekening &tujuan, int jumlah)` — withdraws from this account and deposits into `tujuan`; fails when the balance is too low. The parameter is a **reference**, so that the real target account changes and not a copy.\n\n"
         "A bank has n accounts numbered 1 to n. The input gives their opening balances, then commands one per line: `setor a x`, `tarik a x`, `transfer a b x` (from a to b), and finally `selesai`.\n\n"
         "For every command that fails print `Gagal`. At the end print all balances, one per line: `Rekening k: balance`.\n\n"
         "Example: 2 accounts with balances `100000 50000`; `tarik 2 80000`, `transfer 1 2 30000`, `selesai` →\n\n```\nGagal\nRekening 1: 70000\nRekening 2: 80000\n```" + W_EN,
         "First line: n. Second line: n opening balances. Then the commands, ending with `selesai`.", "`Gagal` for every failed command, then n lines of closing balances.", "2 ≤ n ≤ 6; at most 20 commands; all amounts are between 0 and 1000000"),
     ar=("مصرف صغير", "أنشئ صنفًا `Rekening` (حساب) فيه خاصية `int saldo` (الرصيد) وثلاث طرائق:\n\n- `void setor(int jumlah)` (إيداع)؛\n- `bool tarik(int jumlah)` (سحب) — تفشل (`false` ولا يتغير الرصيد) إذا لم يكفِ الرصيد؛\n"
         "- `bool transferKe(Rekening &tujuan, int jumlah)` — تسحب من هذا الحساب وتودع في `tujuan`؛ وتفشل إذا لم يكفِ الرصيد. الوسيط **مرجع** ليتغير الحساب الهدف الحقيقي لا نسخة منه.\n\n"
         "في مصرف n حسابًا مرقّمة من 1 إلى n. يعطي المدخل أرصدتها الابتدائية ثم أوامر، أمرًا في كل سطر: `setor a x` و `tarik a x` و `transfer a b x` (من a إلى b) وأخيرًا `selesai`.\n\n"
         "لكل أمر يفشل اطبع `Gagal`. في النهاية اطبع كل الأرصدة، رصيدًا في كل سطر: `Rekening k: الرصيد`.\n\n"
         "مثال: حسابان رصيداهما `100000 50000`؛ `tarik 2 80000` ثم `transfer 1 2 30000` ثم `selesai` ←\n\n```\nGagal\nRekening 1: 70000\nRekening 2: 80000\n```" + W_AR,
         "السطر الأول: n. السطر الثاني: n رصيدًا ابتدائيًا. ثم الأوامر منتهيةً بـ `selesai`.", "`Gagal` لكل أمر فاشل ثم n سطرًا للأرصدة النهائية.", "2 ≤ n ≤ 6؛ 20 أمرًا على الأكثر؛ كل المبالغ بين 0 و 1000000"))

soal("robot-penjelajah", "sedang", "trimmed", "n = {2: 1}.get(i, r.randint(2, 100))\nabjad = {3: 'M', 4: 'LR'}.get(i, 'MMMLR')\nprint(''.join(r.choice(abjad) for _ in range(n)))\n",
     program(r'''
class Robot {
public:
    int x = 0;
    int y = 0;
    int arah = 0;

    void kiri() {
        arah = (arah + 3) % 4;
    }

    void kanan() {
        arah = (arah + 1) % 4;
    }

    void maju() {
        switch (arah) {
            case 0: y++; break;
            case 1: x++; break;
            case 2: y--; break;
            default: x--; break;
        }
    }

    string namaArah() {
        string nama[4]{"utara", "timur", "selatan", "barat"};
        return nama[arah];
    }
};''', r'''    string perintah;
    cin >> perintah;
    Robot robot;
    for (auto p : perintah) {
        if (p == 'M') {
            robot.maju();
        } else if (p == 'L') {
            robot.kiri();
        } else {
            robot.kanan();
        }
    }
    cout << robot.x << " " << robot.y << " " << robot.namaArah() << endl;''', "string"), wajib=W,
     id=("Robot Penjelajah", "Sebuah robot berada di titik (0, 0) dan menghadap **utara** (arah y positif). Ia mengerti tiga perintah:\n\n- `M` — maju satu langkah ke arah yang dihadapinya;\n- `L` — berputar 90° ke kiri;\n- `R` — berputar 90° ke kanan.\n\n"
         "Buat class `Robot` dengan atribut `int x`, `int y`, `int arah` dan method `void maju()`, `void kiri()`, `void kanan()`, serta `string namaArah()` yang mengembalikan `utara`, `timur`, `selatan`, atau `barat`.\n\n"
         "Baca deretan perintah, jalankan, lalu cetak posisi akhir dan arah hadap robot.\n\nContoh: `MMRMLM` → `1 3 utara`. Contoh lain: `RRM` → `0 -1 selatan`." + W_ID,
         "Satu baris: deretan huruf `M`, `L`, `R` tanpa spasi.", "Satu baris: x, y, dan arah hadap.", "Panjang deretan 1 sampai 100."),
     en=("Rover", "A robot stands at the point (0, 0) facing **north** (the positive y direction). It understands three commands:\n\n- `M` — move one step in the direction it faces;\n- `L` — turn 90° to the left;\n- `R` — turn 90° to the right.\n\n"
         "Make a class `Robot` with the attributes `int x`, `int y`, `int arah` (direction) and the methods `void maju()`, `void kiri()`, `void kanan()` (forward, left, right), and `string namaArah()` that returns `utara`, `timur`, `selatan` or `barat` (north, east, south, west).\n\n"
         "Read a run of commands, carry them out, then print the robot's final position and the direction it faces.\n\nExample: `MMRMLM` → `1 3 utara`. Another: `RRM` → `0 -1 selatan`." + W_EN,
         "One line: a run of the letters `M`, `L`, `R` without spaces.", "One line: x, y and the direction faced.", "The run is 1 to 100 letters long."),
     ar=("الروبوت المستكشف", "يقف روبوت عند النقطة (0, 0) متجهًا نحو **الشمال** (اتجاه y الموجب). يفهم ثلاثة أوامر:\n\n- `M` — تقدَّم خطوة في الاتجاه الذي تواجهه؛\n- `L` — استدر 90° إلى اليسار؛\n- `R` — استدر 90° إلى اليمين.\n\n"
         "أنشئ صنفًا `Robot` خصائصه `int x` و `int y` و `int arah` (الاتجاه) وطرائقه `void maju()` و `void kiri()` و `void kanan()` (تقدَّم، يسار، يمين) و `string namaArah()` التي تُرجع `utara` أو `timur` أو `selatan` أو `barat` (شمال، شرق، جنوب، غرب).\n\n"
         "اقرأ سلسلة أوامر ونفِّذها ثم اطبع الموضع النهائي للروبوت والاتجاه الذي يواجهه.\n\nمثال: `MMRMLM` ← `1 3 utara`. ومثال آخر: `RRM` ← `0 -1 selatan`." + W_AR,
         "سطر واحد: سلسلة من الحروف `M` و `L` و `R` بلا مسافات.", "سطر واحد: x و y والاتجاه.", "طول السلسلة من 1 إلى 100."))

# ================================= SULIT (4) =================================

soal("tangan-poker", "sulit", "trimmed",
     "J = 'SHDC'\ndef kartu(nilai, jenis):\n    return list(zip(nilai, jenis))\nk = i % 9\nif k == 0:\n    a = r.randint(2, 10)\n    t = kartu(range(a, a + 5), [r.choice(J)] * 5)\nelif k == 1:\n    a, b = r.sample(range(2, 15), 2)\n    t = kartu([a] * 4 + [b], list(J) + [r.choice(J)])\nelif k == 2:\n    a, b = r.sample(range(2, 15), 2)\n    t = kartu([a] * 3 + [b] * 2, r.sample(J, 3) + r.sample(J, 2))\n"
     "elif k == 3:\n    while True:\n        v = sorted(r.sample(range(2, 15), 5))\n        if v[4] - v[0] != 4:\n            break\n    t = kartu(v, [r.choice(J)] * 5)\nelif k == 4:\n    a = r.randint(2, 10)\n    js = [r.choice(J) for _ in range(5)]\n    js[1] = [x for x in J if x != js[0]][0]\n    t = kartu(range(a, a + 5), js)\n"
     "elif k == 5:\n    a, b, c = r.sample(range(2, 15), 3)\n    t = kartu([a] * 3 + [b, c], r.sample(J, 3) + [r.choice(J), r.choice(J)])\nelif k == 6:\n    a, b, c = r.sample(range(2, 15), 3)\n    t = kartu([a] * 2 + [b] * 2 + [c], r.sample(J, 2) + r.sample(J, 2) + [r.choice(J)])\n"
     "elif k == 7:\n    a, b, c, d = r.sample(range(2, 15), 4)\n    t = kartu([a] * 2 + [b, c, d], r.sample(J, 2) + [r.choice(J) for _ in range(3)])\nelse:\n    while True:\n        v = sorted(r.sample(range(2, 15), 5))\n        if v[4] - v[0] != 4:\n            break\n    js = [r.choice(J) for _ in range(5)]\n    js[1] = [x for x in J if x != js[0]][0]\n    t = kartu(v, js)\n"
     "t = list(t)\nr.shuffle(t)\nfor n, j in t:\n    print(n, j)\n",
     program(r'''
class TanganPoker {
public:
    int nilai[5];
    char jenis[5];

    bool sewarna() {
        for (int k = 1; k < 5; k++) {
            if (jenis[k] != jenis[0]) {
                return false;
            }
        }
        return true;
    }

    int banyakNilai(int n) {
        auto banyak = 0;
        for (auto v : nilai) {
            if (v == n) {
                banyak++;
            }
        }
        return banyak;
    }

    bool berurutan() {
        auto kecil = nilai[0];
        auto besar = nilai[0];
        for (auto v : nilai) {
            if (v < kecil) kecil = v;
            if (v > besar) besar = v;
            if (banyakNilai(v) != 1) {
                return false;
            }
        }
        return besar - kecil == 4;
    }

    string peringkat() {
        auto empat = 0;
        auto tiga = 0;
        auto pasang = 0;
        for (int n = 2; n <= 14; n++) {
            auto b = banyakNilai(n);
            if (b == 4) empat++;
            if (b == 3) tiga++;
            if (b == 2) pasang++;
        }
        if (berurutan() && sewarna()) return "straight flush";
        if (empat == 1) return "four of a kind";
        if (tiga == 1 && pasang == 1) return "full house";
        if (sewarna()) return "flush";
        if (berurutan()) return "straight";
        if (tiga == 1) return "three of a kind";
        if (pasang == 2) return "two pair";
        if (pasang == 1) return "one pair";
        return "high card";
    }
};''', r'''    TanganPoker t;
    for (int k = 0; k < 5; k++) {
        cin >> t.nilai[k] >> t.jenis[k];
    }
    cout << t.peringkat() << endl;''', "string"), wajib=W,
     id=("Tangan Poker", "Lima kartu di tangan seorang pemain poker dinilai menurut susunannya. Tiap kartu punya **nilai** 2 sampai 14 (11 = Jack, 12 = Queen, 13 = King, 14 = As) dan **jenis** `S`, `H`, `D`, atau `C`.\n\n"
         "Peringkatnya, dari yang tertinggi; yang dipakai adalah yang pertama cocok:\n\n"
         "1. `straight flush` — lima nilai berurutan, semua sejenis;\n2. `four of a kind` — empat kartu bernilai sama;\n3. `full house` — tiga kartu bernilai sama dan sepasang;\n4. `flush` — semua sejenis;\n5. `straight` — lima nilai berurutan;\n"
         "6. `three of a kind` — tiga kartu bernilai sama;\n7. `two pair` — dua pasang;\n8. `one pair` — sepasang;\n9. `high card` — selain itu.\n\n"
         "As hanya bernilai 14: `14 2 3 4 5` bukan urutan.\n\n"
         "Buat class `TanganPoker` dengan atribut `int nilai[5]`, `char jenis[5]` dan method `bool sewarna()`, `bool berurutan()`, `int banyakNilai(int n)` (berapa kartu bernilai n), serta `string peringkat()` yang memakai ketiganya.\n\n"
         "Contoh: kartu `10 H`, `11 H`, `12 H`, `13 H`, `14 H` → `straight flush`. Kartu `5 S`, `5 D`, `9 C`, `9 H`, `2 S` → `two pair`." + W_ID,
         "Lima baris, masing-masing berisi nilai dan jenis satu kartu.", "Satu baris: peringkat tangan itu.", "2 ≤ nilai ≤ 14; jenis `S`, `H`, `D`, atau `C`; kelima kartu berbeda."),
     en=("Poker Hand", "The five cards in a poker player's hand are ranked by their pattern. Each card has a **value** from 2 to 14 (11 = Jack, 12 = Queen, 13 = King, 14 = Ace) and a **suit** `S`, `H`, `D` or `C`.\n\n"
         "The ranks, from the highest; the first one that fits is used:\n\n"
         "1. `straight flush` — five consecutive values, all of one suit;\n2. `four of a kind` — four cards of the same value;\n3. `full house` — three cards of one value and a pair;\n4. `flush` — all of one suit;\n5. `straight` — five consecutive values;\n"
         "6. `three of a kind` — three cards of the same value;\n7. `two pair`;\n8. `one pair`;\n9. `high card` — anything else.\n\n"
         "The Ace only counts as 14: `14 2 3 4 5` is not a straight.\n\n"
         "Make a class `TanganPoker` with the attributes `int nilai[5]`, `char jenis[5]` and the methods `bool sewarna()` (one suit), `bool berurutan()` (consecutive), `int banyakNilai(int n)` (how many cards have value n), and `string peringkat()` (rank), which uses the other three.\n\n"
         "Example: the cards `10 H`, `11 H`, `12 H`, `13 H`, `14 H` → `straight flush`. The cards `5 S`, `5 D`, `9 C`, `9 H`, `2 S` → `two pair`." + W_EN,
         "Five lines, each with the value and the suit of one card.", "One line: the rank of the hand.", "2 ≤ value ≤ 14; the suit is `S`, `H`, `D` or `C`; the five cards are different."),
     ar=("يد البوكر", "الأوراق الخمس في يد لاعب البوكر تُقيَّم بحسب تشكيلتها. لكل ورقة **قيمة** من 2 إلى 14 (11 = الولد، 12 = الملكة، 13 = الملك، 14 = الآس) و**نوع** `S` أو `H` أو `D` أو `C`.\n\n"
         "المراتب من الأعلى؛ وتُعتمد أول مرتبة تنطبق:\n\n"
         "1. `straight flush` — خمس قيم متتالية كلها من نوع واحد؛\n2. `four of a kind` — أربع أوراق متساوية القيمة؛\n3. `full house` — ثلاث أوراق متساوية القيمة وزوج؛\n4. `flush` — كلها من نوع واحد؛\n5. `straight` — خمس قيم متتالية؛\n"
         "6. `three of a kind` — ثلاث أوراق متساوية القيمة؛\n7. `two pair` — زوجان؛\n8. `one pair` — زوج واحد؛\n9. `high card` — غير ذلك.\n\n"
         "الآس قيمته 14 فقط: `14 2 3 4 5` ليست متتالية.\n\n"
         "أنشئ صنفًا `TanganPoker` خاصيتاه `int nilai[5]` و `char jenis[5]` وطرائقه `bool sewarna()` (نوع واحد) و `bool berurutan()` (متتالية) و `int banyakNilai(int n)` (عدد الأوراق التي قيمتها n) و `string peringkat()` (المرتبة) التي تستعمل الثلاث الأخرى.\n\n"
         "مثال: الأوراق `10 H` و `11 H` و `12 H` و `13 H` و `14 H` ← `straight flush`. والأوراق `5 S` و `5 D` و `9 C` و `9 H` و `2 S` ← `two pair`." + W_AR,
         "خمسة أسطر في كل منها قيمة ورقة ونوعها.", "سطر واحد: مرتبة اليد.", "2 ≤ القيمة ≤ 14؛ النوع `S` أو `H` أو `D` أو `C`؛ الأوراق الخمس مختلفة."))

soal("penjadwal-bergilir", "sulit", "trimmed",
     "n = {2: 1}.get(i, r.randint(2, 8))\nq = {3: 1, 4: 100}.get(i, r.randint(1, 10))\nprint(n, q)\nfor k in range(n):\n    print('P' + str(k + 1), r.randint(1, 30))\n",
     program(r'''
class Penjadwal {
public:
    string nama[8];
    int sisa[8];
    int banyak = 0;
    int waktu = 0;

    void tambah(string n, int lama) {
        nama[banyak] = n;
        sisa[banyak] = lama;
        banyak++;
    }

    bool masihAda() {
        for (int k = 0; k < banyak; k++) {
            if (sisa[k] > 0) {
                return true;
            }
        }
        return false;
    }

    void putaran(int kuantum) {
        for (int k = 0; k < banyak; k++) {
            if (sisa[k] == 0) {
                continue;
            }
            auto jatah = kuantum;
            if (sisa[k] < jatah) {
                jatah = sisa[k];
            }
            sisa[k] -= jatah;
            waktu += jatah;
            if (sisa[k] == 0) {
                cout << nama[k] << " " << waktu << endl;
            }
        }
    }
};''', r'''    int n, kuantum;
    cin >> n >> kuantum;
    Penjadwal p;
    for (int k = 0; k < n; k++) {
        string nama;
        int lama;
        cin >> nama >> lama;
        p.tambah(nama, lama);
    }
    while (p.masihAda()) {
        p.putaran(kuantum);
    }''', "string"), wajib=W,
     id=("Penjadwal Bergilir", "Sistem operasi membagi waktu prosesor kepada beberapa proses secara **bergilir** (*round-robin*). Setiap proses punya lama kerja yang dibutuhkan. Dalam satu putaran, proses-proses didatangi menurut urutan masukan; "
         "setiap proses yang belum selesai mendapat jatah paling banyak q satuan waktu. Proses yang kebutuhannya habis langsung selesai; yang belum, menunggu putaran berikutnya. Jam berjalan terus tanpa jeda.\n\n"
         "Buat class `Penjadwal` dengan atribut `string nama[8]`, `int sisa[8]`, `int banyak`, `int waktu`, dan method:\n\n"
         "- `void tambah(string nama, int lama)` — mendaftarkan sebuah proses;\n- `bool masihAda()` — masih adakah proses yang belum selesai;\n- `void putaran(int kuantum)` — menjalankan satu putaran dan mencetak `<nama> <waktu>` untuk tiap proses yang selesai di putaran itu, dengan waktu saat ia selesai.\n\n"
         "Cetak proses-proses menurut urutan selesainya.\n\n"
         "Contoh: q = 3; proses `P1 5`, `P2 2`, `P3 4`. Putaran pertama: P1 bekerja 3 (waktu 3), P2 bekerja 2 dan selesai (waktu 5), P3 bekerja 3 (waktu 8). Putaran kedua: P1 bekerja 2 dan selesai (waktu 10), P3 bekerja 1 dan selesai (waktu 11).\n\n```\nP2 5\nP1 10\nP3 11\n```" + W_ID,
         "Baris pertama: banyak proses n dan jatah q. Lalu n baris, masing-masing berisi nama proses dan lama kerjanya.", "n baris: nama proses dan waktu selesainya, menurut urutan selesai.", "1 ≤ n ≤ 8; 1 ≤ q ≤ 100; 1 ≤ lama kerja ≤ 30"),
     en=("Round-Robin Scheduler", "An operating system shares processor time among several processes **in turns** (round-robin). Every process needs a certain amount of work. In one round the processes are visited in input order; "
         "each unfinished process gets at most q time units. A process whose need is used up finishes at once; the others wait for the next round. The clock runs on without gaps.\n\n"
         "Make a class `Penjadwal` (scheduler) with the attributes `string nama[8]`, `int sisa[8]` (remaining), `int banyak` (count), `int waktu` (time), and the methods:\n\n"
         "- `void tambah(string nama, int lama)` — registers a process;\n- `bool masihAda()` — is any process still unfinished;\n- `void putaran(int kuantum)` — runs one round and prints `<name> <time>` for each process that finishes in it, with the time at which it finishes.\n\n"
         "Print the processes in the order in which they finish.\n\n"
         "Example: q = 3; processes `P1 5`, `P2 2`, `P3 4`. First round: P1 works 3 (time 3), P2 works 2 and finishes (time 5), P3 works 3 (time 8). Second round: P1 works 2 and finishes (time 10), P3 works 1 and finishes (time 11).\n\n```\nP2 5\nP1 10\nP3 11\n```" + W_EN,
         "First line: the number of processes n and the share q. Then n lines, each with a process name and its amount of work.", "n lines: the process name and its finishing time, in finishing order.", "1 ≤ n ≤ 8; 1 ≤ q ≤ 100; 1 ≤ work ≤ 30"),
     ar=("المجدول الدوري", "يوزّع نظام التشغيل وقت المعالج على عدة عمليات **بالتناوب** (round-robin). لكل عملية مقدار عمل تحتاج إليه. في الدورة الواحدة تُزار العمليات بترتيب المدخل؛ "
         "وكل عملية لم تنتهِ تنال q وحدة زمن على الأكثر. العملية التي استنفدت حاجتها تنتهي فورًا، وغيرها ينتظر الدورة التالية. والساعة تمضي بلا توقف.\n\n"
         "أنشئ صنفًا `Penjadwal` (مجدول) خصائصه `string nama[8]` و `int sisa[8]` (الباقي) و `int banyak` (العدد) و `int waktu` (الزمن) وطرائقه:\n\n"
         "- `void tambah(string nama, int lama)` — تسجّل عملية؛\n- `bool masihAda()` — هل بقيت عملية لم تنتهِ؛\n- `void putaran(int kuantum)` — تنفّذ دورة واحدة وتطبع `<الاسم> <الزمن>` لكل عملية تنتهي فيها، مع زمن انتهائها.\n\n"
         "اطبع العمليات بترتيب انتهائها.\n\n"
         "مثال: q = 3؛ العمليات `P1 5` و `P2 2` و `P3 4`. الدورة الأولى: P1 تعمل 3 (الزمن 3)، P2 تعمل 2 وتنتهي (الزمن 5)، P3 تعمل 3 (الزمن 8). الدورة الثانية: P1 تعمل 2 وتنتهي (الزمن 10)، P3 تعمل 1 وتنتهي (الزمن 11).\n\n```\nP2 5\nP1 10\nP3 11\n```" + W_AR,
         "السطر الأول: عدد العمليات n والحصة q. ثم n سطرًا في كل منها اسم عملية ومقدار عملها.", "n سطرًا: اسم العملية وزمن انتهائها، بترتيب الانتهاء.", "1 ≤ n ≤ 8؛ 1 ≤ q ≤ 100؛ 1 ≤ مقدار العمل ≤ 30"))

soal("faktorial-besar", "sulit", "trimmed", "print({2: 0, 3: 100, 4: 1, 5: 25}.get(i, r.randint(13, 50) if i % 2 == 0 else r.randint(51, 99)))\n",
     program(r'''
class BilanganBesar {
public:
    int digit[200];
    int panjang;

    void isi(int nilai) {
        panjang = 0;
        if (nilai == 0) {
            digit[0] = 0;
            panjang = 1;
        }
        while (nilai > 0) {
            digit[panjang] = nilai % 10;
            panjang++;
            nilai /= 10;
        }
    }

    void kali(int k) {
        auto simpan = 0;
        for (int p = 0; p < panjang; p++) {
            auto hasil = digit[p] * k + simpan;
            digit[p] = hasil % 10;
            simpan = hasil / 10;
        }
        while (simpan > 0) {
            digit[panjang] = simpan % 10;
            panjang++;
            simpan /= 10;
        }
    }

    void cetak() {
        for (int p = panjang - 1; p >= 0; p--) {
            cout << digit[p];
        }
        cout << endl;
    }
};''', r'''    int n;
    cin >> n;
    BilanganBesar f;
    f.isi(1);
    for (int k = 2; k <= n; k++) {
        f.kali(k);
    }
    f.cetak();'''), wajib=W,
     id=("Faktorial Besar", "13! sudah tidak muat di `int`, dan 21! tidak muat di `long long`. Untuk menghitung 100! — bilangan dengan 158 digit — kita perlu tipe bilangan buatan sendiri.\n\n"
         "Buat class `BilanganBesar` yang menyimpan digit-digit sebuah bilangan dalam array (`int digit[200]`, dengan `digit[0]` digit satuan) dan banyak digitnya (`int panjang`), dengan method:\n\n"
         "- `void isi(int nilai)` — mengisi objek dengan sebuah bilangan biasa;\n- `void kali(int k)` — mengalikan bilangan itu dengan k, digit demi digit dengan simpanan, seperti perkalian bersusun;\n- `void cetak()` — mencetak bilangannya.\n\n"
         "Baca n dan cetak n! dengan tepat.\n\nContoh: 5 → `120`; 25 → `15511210043330985984000000`; 0 → `1`." + W_ID,
         "Satu bilangan bulat n.", "Satu baris: n! tanpa nol di depan.", "0 ≤ n ≤ 100"),
     en=("Big Factorial", "13! no longer fits in an `int`, and 21! does not fit in a `long long`. To compute 100! — a number of 158 digits — we need a number type of our own.\n\n"
         "Make a class `BilanganBesar` (big number) that keeps the digits of a number in an array (`int digit[200]`, with `digit[0]` the units digit) and the number of digits (`int panjang`), with the methods:\n\n"
         "- `void isi(int nilai)` — fills the object with an ordinary number;\n- `void kali(int k)` — multiplies the number by k, digit by digit with a carry, as in long multiplication;\n- `void cetak()` — prints the number.\n\n"
         "Read n and print n! exactly.\n\nExamples: 5 → `120`; 25 → `15511210043330985984000000`; 0 → `1`." + W_EN,
         "One integer n.", "One line: n! without leading zeros.", "0 ≤ n ≤ 100"),
     ar=("المضروب الكبير", "13! لا يسعه `int`، و 21! لا يسعه `long long`. لحساب 100! — وهو عدد من 158 رقمًا — نحتاج إلى نوع عددي من صنعنا.\n\n"
         "أنشئ صنفًا `BilanganBesar` (عدد كبير) يحفظ أرقام عدد في مصفوفة (`int digit[200]` حيث `digit[0]` رقم الآحاد) وعدد الأرقام (`int panjang`)، وطرائقه:\n\n"
         "- `void isi(int nilai)` — تملأ الكائن بعدد عادي؛\n- `void kali(int k)` — تضرب العدد في k رقمًا رقمًا مع رقم محمول، كالضرب العمودي؛\n- `void cetak()` — تطبع العدد.\n\n"
         "اقرأ n واطبع n! بدقة.\n\nأمثلة: 5 ← `120`؛ 25 ← `15511210043330985984000000`؛ 0 ← `1`." + W_AR,
         "عدد صحيح واحد n.", "سطر واحد: n! بلا أصفار في أوله.", "0 ≤ n ≤ 100"))

soal("mesin-penjual", "sulit", "trimmed",
     BARANG + "k = r.randint(1, 5)\nprint(k)\nfor nama in r.sample(BARANG, k):\n    print(nama, r.randint(1, 10) * 1000, r.randint(0, 3))\nfor _ in range(r.randint(1, 25)):\n    q = r.random()\n    if q < 0.4:\n        print('uang', r.choice([1000, 2000, 5000, 10000]))\n    elif q < 0.9:\n        print('pilih', r.randint(1, k))\n    else:\n        print('batal')\nprint('selesai')\n",
     program(r'''
class MesinPenjual {
public:
    string nama[5];
    int harga[5];
    int stok[5];
    int banyak = 0;
    int saldo = 0;
    int pendapatan = 0;

    void isiSlot(string n, int h, int s) {
        nama[banyak] = n;
        harga[banyak] = h;
        stok[banyak] = s;
        banyak++;
    }

    void masukkan(int uang) {
        saldo += uang;
    }

    void pilih(int nomor) {
        auto k = nomor - 1;
        if (stok[k] == 0) {
            cout << "Habis" << endl;
        } else if (saldo < harga[k]) {
            cout << "Uang kurang" << endl;
        } else {
            stok[k]--;
            saldo -= harga[k];
            pendapatan += harga[k];
            cout << "Keluar: " << nama[k] << endl;
        }
    }

    void batal() {
        cout << "Kembali: " << saldo << endl;
        saldo = 0;
    }
};''', r'''    int k;
    cin >> k;
    MesinPenjual mesin;
    for (int s = 0; s < k; s++) {
        string nama;
        int harga, stok;
        cin >> nama >> harga >> stok;
        mesin.isiSlot(nama, harga, stok);
    }
    string perintah;
    while (cin >> perintah) {
        if (perintah == "selesai") {
            break;
        }
        if (perintah == "batal") {
            mesin.batal();
        } else {
            int x;
            cin >> x;
            if (perintah == "uang") {
                mesin.masukkan(x);
            } else {
                mesin.pilih(x);
            }
        }
    }
    cout << "Pendapatan: " << mesin.pendapatan << endl;''', "string"), wajib=W,
     id=("Mesin Penjual Otomatis", "Tirukan sebuah mesin penjual otomatis dengan k slot barang. Tiap slot punya nama barang, harga, dan stok.\n\n"
         "Buat class `MesinPenjual` yang menyimpan semua slot (`string nama[5]`, `int harga[5]`, `int stok[5]`, `int banyak`), uang yang sedang dimasukkan pembeli (`int saldo`), dan `int pendapatan`, dengan method:\n\n"
         "- `void isiSlot(string nama, int harga, int stok)` — menambah satu slot;\n- `void masukkan(int uang)` — menambah saldo;\n"
         "- `void pilih(int nomor)` — pembeli memilih slot (nomor mulai dari 1). Diperiksa berurutan: bila stoknya 0, cetak `Habis`; bila saldo kurang dari harga, cetak `Uang kurang`; selain itu stok berkurang satu, saldo berkurang sebesar harga, pendapatan bertambah, dan dicetak `Keluar: <nama>`;\n"
         "- `void batal()` — mencetak `Kembali: <saldo>` lalu menolkan saldo.\n\n"
         "Perintah datang satu per baris: `uang x`, `pilih n`, `batal`, dan terakhir `selesai`. Di akhir cetak `Pendapatan: T`. Saldo yang masih tersisa saat `selesai` tidak dikembalikan dan tidak dihitung sebagai pendapatan.\n\n"
         "Contoh: satu slot `Teh 3000 1`; perintah `uang 2000`, `pilih 1`, `uang 2000`, `pilih 1`, `pilih 1`, `batal`, `selesai` →\n\n```\nUang kurang\nKeluar: Teh\nHabis\nKembali: 1000\nPendapatan: 3000\n```" + W_ID,
         "Baris pertama: k. Lalu k baris berisi nama (satu kata), harga, dan stok. Lalu perintah-perintah, diakhiri `selesai`.", "Satu baris untuk tiap `pilih` dan `batal`, lalu `Pendapatan: T`.", "1 ≤ k ≤ 5; paling banyak 25 perintah; harga kelipatan 1000 antara 1000 dan 10000; stok 0 sampai 3"),
     en=("Vending Machine", "Imitate a vending machine with k slots. Each slot has an item name, a price and a stock.\n\n"
         "Make a class `MesinPenjual` that keeps all slots (`string nama[5]`, `int harga[5]`, `int stok[5]`, `int banyak`), the money the buyer has put in (`int saldo`) and `int pendapatan` (takings), with the methods:\n\n"
         "- `void isiSlot(string nama, int harga, int stok)` — adds one slot;\n- `void masukkan(int uang)` — adds to the balance;\n"
         "- `void pilih(int nomor)` — the buyer chooses a slot (numbered from 1). Checked in order: if its stock is 0, print `Habis` (sold out); if the balance is below the price, print `Uang kurang` (not enough money); otherwise the stock drops by one, the balance drops by the price, the takings grow, and `Keluar: <name>` is printed;\n"
         "- `void batal()` (cancel) — prints `Kembali: <balance>` (returned) and sets the balance to zero.\n\n"
         "Commands come one per line: `uang x`, `pilih n`, `batal`, and finally `selesai`. At the end print `Pendapatan: T`. A balance still left at `selesai` is not returned and does not count as takings.\n\n"
         "Example: one slot `Teh 3000 1`; commands `uang 2000`, `pilih 1`, `uang 2000`, `pilih 1`, `pilih 1`, `batal`, `selesai` →\n\n```\nUang kurang\nKeluar: Teh\nHabis\nKembali: 1000\nPendapatan: 3000\n```" + W_EN,
         "First line: k. Then k lines with a name (one word), a price and a stock. Then the commands, ending with `selesai`.", "One line for every `pilih` and `batal`, then `Pendapatan: T`.", "1 ≤ k ≤ 5; at most 25 commands; prices are multiples of 1000 between 1000 and 10000; stock 0 to 3"),
     ar=("آلة البيع", "حاكِ آلة بيع فيها k خانة. لكل خانة اسم سلعة وسعر ومخزون.\n\n"
         "أنشئ صنفًا `MesinPenjual` يحفظ كل الخانات (`string nama[5]` و `int harga[5]` و `int stok[5]` و `int banyak`) والمال الذي أدخله المشتري (`int saldo`) و `int pendapatan` (الإيراد)، وطرائقه:\n\n"
         "- `void isiSlot(string nama, int harga, int stok)` — تضيف خانة؛\n- `void masukkan(int uang)` — تزيد الرصيد؛\n"
         "- `void pilih(int nomor)` — يختار المشتري خانة (الترقيم من 1). يُفحص بالترتيب: إذا كان المخزون 0 فاطبع `Habis` (نفد)؛ وإذا كان الرصيد أقل من السعر فاطبع `Uang kurang` (المال لا يكفي)؛ وإلا نقص المخزون واحدًا ونقص الرصيد بمقدار السعر وزاد الإيراد وطُبع `Keluar: <الاسم>`؛\n"
         "- `void batal()` (إلغاء) — تطبع `Kembali: <الرصيد>` (المُعاد) ثم تصفّر الرصيد.\n\n"
         "تأتي الأوامر أمرًا في كل سطر: `uang x` و `pilih n` و `batal` وأخيرًا `selesai`. في النهاية اطبع `Pendapatan: T`. الرصيد الباقي عند `selesai` لا يُعاد ولا يُحسب إيرادًا.\n\n"
         "مثال: خانة واحدة `Teh 3000 1`؛ الأوامر `uang 2000` ثم `pilih 1` ثم `uang 2000` ثم `pilih 1` ثم `pilih 1` ثم `batal` ثم `selesai` ←\n\n```\nUang kurang\nKeluar: Teh\nHabis\nKembali: 1000\nPendapatan: 3000\n```" + W_AR,
         "السطر الأول: k. ثم k سطرًا فيها اسم (كلمة واحدة) وسعر ومخزون. ثم الأوامر منتهيةً بـ `selesai`.", "سطر لكل `pilih` و `batal` ثم `Pendapatan: T`.", "1 ≤ k ≤ 5؛ 25 أمرًا على الأكثر؛ الأسعار من مضاعفات 1000 بين 1000 و 10000؛ المخزون من 0 إلى 3"))

tulis(__file__, "PBO301", 2, "cpp", {"mudah_sekali": 8, "mudah": 8, "mudah_sedang": 6, "sedang": 6, "sulit": 4})
