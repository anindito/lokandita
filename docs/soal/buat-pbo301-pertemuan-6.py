#!/usr/bin/env python3
"""Menyusun docs/soal/pbo301-pertemuan-6.json.

PBO301 pertemuan 6 — Inheritance. Konsep yang diizinkan di produksi: 21 konsep
dasar, MODERN_CPP, CLASS_OBJECT, CONSTRUCTOR_DESTRUCTOR, COPY_SEMANTICS,
ENCAPSULATION, KOMPOSISI_AGREGASI, INHERITANCE.

POLYMORPHISM (fungsi anggota `virtual`, `override`, `final`) baru diizinkan di
pertemuan 7. Karena itu di sini semua pemanggilan bersifat statis: method
turunan MENYEMBUNYIKAN method induk, tidak menimpanya, dan objek dipakai lewat
tipe aslinya. `virtual` pada daftar induk (`class B : virtual public A`) adalah
pewarisan virtual untuk masalah berlian — detektor tidak menghitungnya sebagai
POLYMORPHISM (diperiksa pada penganalisis sungguhan, lihat PROGRESS.md).

Yang dibuat terlihat di keluaran: anggota yang diwarisi dipakai turunan, urutan
konstruktor/destruktor di hierarki (induk dulu saat dibuat, turunan dulu saat
dihancurkan), pemanggilan `Induk::method()`, penyembunyian nama, pewarisan
bertingkat, ganda, privat, dan virtual.

Setiap soal mensyaratkan CLASS_OBJECT dan INHERITANCE di kode mahasiswa.
Pemeriksaannya: ada kelas dengan daftar induk. Hierarki yang diminta
pernyataan tidak diperiksa judge.

Dilewati dari silabus pertemuan 6 (tidak bisa dinilai dari keluaran): notasi
generalisasi UML; akibat `protected` / `private` inheritance yang berupa galat
kompilasi; uraian "kapan memilih komposisi".

    python3 docs/soal/buat-pbo301-pertemuan-6.py
"""
from bangun import program, soal, tulis

W = ("CLASS_OBJECT", "INHERITANCE")
W_ID = "\n\n**Kode harus memuat class turunan** (`class Anak : public Induk`): keluaran yang benar tanpa pewarisan hanya mendapat setengah nilai."
W_EN = "\n\n**The code must contain a derived class** (`class Anak : public Induk`): correct output without inheritance gets only half the marks."
W_AR = "\n\n**يجب أن يحتوي الكود على صنف مشتق** (`class Anak : public Induk`): المخرج الصحيح بلا وراثة ينال نصف الدرجة فقط."

NAMA = "NAMA = ['Andi', 'Budi', 'Citra', 'Dewi', 'Eko', 'Fajar', 'Gita', 'Hadi', 'Indah', 'Joko', 'Kartika', 'Lina', 'Made', 'Nanda', 'Putri', 'Rudi', 'Sari', 'Tono', 'Wulan', 'Yoga']\n"
SATUAN = "SATUAN = ['Alfa', 'Bravo', 'Charlie', 'Delta', 'Elang', 'Garuda', 'Harimau', 'Rajawali', 'Banteng', 'Macan', 'Kobra', 'Hiu']\n"

# ============================== MUDAH SEKALI (8) ==============================

soal("hewan-kucing", "mudah_sekali", "trimmed", "print(r.choice(['Belang', 'Oyen', 'Hitam', 'Putih', 'Manis', 'Gembul', 'Kumis', 'Cemong']))\n",
     program(r'''
class Hewan {
protected:
    string nama;

public:
    Hewan(const string &n) : nama(n) {
    }

    void makan() const {
        cout << nama << " makan" << endl;
    }
};

class Kucing : public Hewan {
public:
    Kucing(const string &n) : Hewan(n) {
    }

    void mengeong() const {
        cout << nama << " mengeong" << endl;
    }
};''', r'''    string nama;
    cin >> nama;
    Kucing kucing(nama);
    kucing.makan();
    kucing.mengeong();''', "string"), wajib=W,
     id=("Hewan dan Kucing", "Seekor kucing **adalah** hewan. Relasi \"adalah\" ditulis dengan **pewarisan**: `class Kucing : public Hewan`. Class turunan memperoleh semua atribut dan method induknya, lalu menambahkan miliknya sendiri.\n\n"
         "Buat class `Hewan` dengan atribut `protected` `string nama`, konstruktor `Hewan(const string &n)`, dan method `void makan() const` yang mencetak `<nama> makan`.\n\n"
         "Buat class `Kucing` turunan `Hewan`. Konstruktornya meneruskan nama ke konstruktor induk — `Kucing(const string &n) : Hewan(n) { }` — dan ia menambah method `void mengeong() const` yang mencetak `<nama> mengeong`.\n\n"
         "Baca sebuah nama, buat satu `Kucing`, panggil `makan()` (yang diwarisi) lalu `mengeong()`.\n\nUntuk `Oyen`:\n\n```\nOyen makan\nOyen mengeong\n```" + W_ID,
         "Satu kata: nama kucing.", "Dua baris seperti contoh.", "Nama paling panjang 20 huruf."),
     en=("Animal and Cat", "A cat **is an** animal. The \"is a\" relation is written with **inheritance**: `class Kucing : public Hewan`. The derived class gets all attributes and methods of its base, and then adds its own.\n\n"
         "Make a class `Hewan` (animal) with a `protected` attribute `string nama`, a constructor `Hewan(const string &n)`, and a method `void makan() const` that prints `<name> makan` (eats).\n\n"
         "Make a class `Kucing` (cat) derived from `Hewan`. Its constructor passes the name on to the base constructor — `Kucing(const string &n) : Hewan(n) { }` — and it adds a method `void mengeong() const` that prints `<name> mengeong` (meows).\n\n"
         "Read a name, create one `Kucing`, call `makan()` (inherited) and then `mengeong()`.\n\nFor `Oyen`:\n\n```\nOyen makan\nOyen mengeong\n```" + W_EN,
         "One word: the cat's name.", "Two lines as in the example.", "The name has at most 20 letters."),
     ar=("الحيوان والقطة", "القطة **هي** حيوان. علاقة «هو» تُكتب **بالوراثة**: `class Kucing : public Hewan`. الصنف المشتق ينال كل خصائص صنفه الأساسي وطرائقه ثم يضيف ما يخصه.\n\n"
         "أنشئ صنفًا `Hewan` (حيوان) فيه خاصية `protected` هي `string nama`، وبانٍ `Hewan(const string &n)`، وطريقة `void makan() const` تطبع `<الاسم> makan` (يأكل).\n\n"
         "أنشئ صنفًا `Kucing` (قطة) مشتقًّا من `Hewan`. بانيه يمرّر الاسم إلى باني الأساس — `Kucing(const string &n) : Hewan(n) { }` — ويضيف طريقة `void mengeong() const` تطبع `<الاسم> mengeong` (تموء).\n\n"
         "اقرأ اسمًا وأنشئ `Kucing` واحدة واستدعِ `makan()` (الموروثة) ثم `mengeong()`.\n\nعند `Oyen`:\n\n```\nOyen makan\nOyen mengeong\n```" + W_AR,
         "كلمة واحدة: اسم القطة.", "سطران كما في المثال.", "الاسم 20 حرفًا على الأكثر."))

soal("manajer-gaji", "mudah_sekali", "trimmed", NAMA + "print(r.choice(NAMA), r.randint(30, 150) * 100000, r.randint(0, 40) * 100000)\n",
     program(r'''
class Pegawai {
protected:
    string nama;
    int gaji;

public:
    Pegawai(const string &n, int g) : nama(n), gaji(g) {
    }

    string getNama() const {
        return nama;
    }

    int gajiTahunan() const {
        return gaji * 12;
    }
};

class Manajer : public Pegawai {
private:
    int tunjangan;

public:
    Manajer(const string &n, int g, int t) : Pegawai(n, g), tunjangan(t) {
    }

    int totalTahunan() const {
        return gajiTahunan() + tunjangan * 12;
    }
};''', r'''    string nama;
    int gaji, tunjangan;
    cin >> nama >> gaji >> tunjangan;
    Manajer m(nama, gaji, tunjangan);
    cout << m.getNama() << ": " << m.totalTahunan() << endl;''', "string"), wajib=W,
     id=("Gaji Manajer", "Seorang manajer **adalah** pegawai yang mendapat tunjangan tambahan. Semua yang berlaku untuk pegawai tidak perlu ditulis ulang.\n\n"
         "Buat class `Pegawai` (atribut `protected` `string nama`, `int gaji`; konstruktor; `getNama()`; `int gajiTahunan() const` — gaji × 12).\n\n"
         "Buat class `Manajer` turunan `Pegawai` dengan atribut tambahan `int tunjangan`, konstruktor `Manajer(n, g, t) : Pegawai(n, g), tunjangan(t)`, dan method `int totalTahunan() const` yang **memakai `gajiTahunan()` warisan** lalu menambah tunjangan × 12.\n\n"
         "Baca nama, gaji per bulan, dan tunjangan per bulan. Cetak `<nama>: <total setahun>`.\n\nUntuk `Budi 5000000 1000000` keluarannya `Budi: 72000000`." + W_ID,
         "Satu baris berisi nama, gaji per bulan, dan tunjangan per bulan.", "Satu baris: nama dan total setahun.", "Gaji 3 sampai 15 juta; tunjangan 0 sampai 4 juta; keduanya kelipatan 100000."),
     en=("A Manager's Pay", "A manager **is an** employee who gets an extra allowance. Everything that holds for an employee need not be written again.\n\n"
         "Make a class `Pegawai` (employee; `protected` attributes `string nama`, `int gaji` (monthly pay); a constructor; `getNama()`; `int gajiTahunan() const` — pay × 12).\n\n"
         "Make a class `Manajer` derived from `Pegawai` with an extra attribute `int tunjangan` (allowance), a constructor `Manajer(n, g, t) : Pegawai(n, g), tunjangan(t)`, and a method `int totalTahunan() const` that **uses the inherited `gajiTahunan()`** and adds allowance × 12.\n\n"
         "Read the name, the monthly pay and the monthly allowance. Print `<name>: <yearly total>`.\n\nFor `Budi 5000000 1000000` the output is `Budi: 72000000`." + W_EN,
         "One line with the name, the monthly pay and the monthly allowance.", "One line: the name and the yearly total.", "Pay 3 to 15 million; allowance 0 to 4 million; both multiples of 100000."),
     ar=("راتب المدير", "المدير **هو** موظف ينال بدلًا إضافيًا. كل ما يصح للموظف لا حاجة إلى كتابته ثانية.\n\n"
         "أنشئ صنفًا `Pegawai` (موظف؛ الخاصيتان المحميتان `string nama` و `int gaji` (الراتب الشهري)؛ وبانٍ؛ و `getNama()`؛ و `int gajiTahunan() const` — الراتب × 12).\n\n"
         "أنشئ صنفًا `Manajer` مشتقًّا من `Pegawai` فيه خاصية إضافية `int tunjangan` (البدل)، وبانٍ `Manajer(n, g, t) : Pegawai(n, g), tunjangan(t)`، وطريقة `int totalTahunan() const` **تستعمل `gajiTahunan()` الموروثة** ثم تضيف البدل × 12.\n\n"
         "اقرأ الاسم والراتب الشهري والبدل الشهري. اطبع `<الاسم>: <الإجمالي السنوي>`.\n\nعند `Budi 5000000 1000000` يكون المخرج `Budi: 72000000`." + W_AR,
         "سطر واحد فيه الاسم والراتب الشهري والبدل الشهري.", "سطر واحد: الاسم والإجمالي السنوي.", "الراتب من 3 إلى 15 مليونًا؛ والبدل من 0 إلى 4 ملايين؛ وكلاهما من مضاعفات 100000."))

soal("urutan-induk-anak", "mudah_sekali", "trimmed", SATUAN + "print(r.choice(SATUAN[:6]) if i % 2 == 0 else r.choice(SATUAN[6:]))\n",
     program(r'''
class Unit {
protected:
    string nama;

public:
    Unit(const string &n) : nama(n) {
        cout << "Unit " << nama << " dibentuk" << endl;
    }

    ~Unit() {
        cout << "Unit " << nama << " dibubarkan" << endl;
    }
};

class Infanteri : public Unit {
public:
    Infanteri(const string &n) : Unit(n) {
        cout << "Infanteri " << nama << " siap" << endl;
    }

    ~Infanteri() {
        cout << "Infanteri " << nama << " mundur" << endl;
    }
};''', r'''    string nama;
    cin >> nama;
    Infanteri pasukan(nama);''', "string"), wajib=W,
     id=("Urutan Induk dan Anak", "Objek turunan memuat bagian induknya di dalam dirinya. Saat dibuat, **bagian induk dibangun lebih dulu**, baru badan konstruktor turunan berjalan. Saat dihancurkan urutannya terbalik: destruktor turunan, lalu destruktor induk.\n\n"
         "Buat class `Unit` (atribut `protected` `string nama`): konstruktornya mencetak `Unit <nama> dibentuk`, destruktornya `Unit <nama> dibubarkan`.\n\n"
         "Buat class `Infanteri` turunan `Unit`: konstruktornya mencetak `Infanteri <nama> siap`, destruktornya `Infanteri <nama> mundur`.\n\n"
         "Baca sebuah nama dan buat satu `Infanteri`.\n\nUntuk `Garuda`:\n\n```\nUnit Garuda dibentuk\nInfanteri Garuda siap\nInfanteri Garuda mundur\nUnit Garuda dibubarkan\n```" + W_ID,
         "Satu kata: nama satuan.", "Empat baris seperti contoh.", "Nama paling panjang 20 huruf."),
     en=("Order of Base and Derived", "A derived object carries its base part inside it. On creation **the base part is built first**, and only then does the body of the derived constructor run. On destruction the order is reversed: the derived destructor, then the base destructor.\n\n"
         "Make a class `Unit` (`protected` attribute `string nama`): its constructor prints `Unit <name> dibentuk` (formed), its destructor `Unit <name> dibubarkan` (disbanded).\n\n"
         "Make a class `Infanteri` (infantry) derived from `Unit`: its constructor prints `Infanteri <name> siap` (ready), its destructor `Infanteri <name> mundur` (withdraws).\n\n"
         "Read a name and create one `Infanteri`.\n\nFor `Garuda`:\n\n```\nUnit Garuda dibentuk\nInfanteri Garuda siap\nInfanteri Garuda mundur\nUnit Garuda dibubarkan\n```" + W_EN,
         "One word: the name of the unit.", "Four lines as in the example.", "The name has at most 20 letters."),
     ar=("ترتيب الأساس والمشتق", "الكائن المشتق يحمل جزء الأساس في داخله. عند الإنشاء **يُبنى جزء الأساس أولًا** ثم يعمل جسم باني المشتق. وعند الهدم ينعكس الترتيب: هادم المشتق ثم هادم الأساس.\n\n"
         "أنشئ صنفًا `Unit` (وحدة؛ الخاصية المحمية `string nama`): بانيه يطبع `Unit <الاسم> dibentuk` (تشكّلت) وهادمه `Unit <الاسم> dibubarkan` (حُلّت).\n\n"
         "أنشئ صنفًا `Infanteri` (مشاة) مشتقًّا من `Unit`: بانيه يطبع `Infanteri <الاسم> siap` (جاهزة) وهادمه `Infanteri <الاسم> mundur` (انسحبت).\n\n"
         "اقرأ اسمًا وأنشئ `Infanteri` واحدة.\n\nعند `Garuda`:\n\n```\nUnit Garuda dibentuk\nInfanteri Garuda siap\nInfanteri Garuda mundur\nUnit Garuda dibubarkan\n```" + W_AR,
         "كلمة واحدة: اسم الوحدة.", "أربعة أسطر كما في المثال.", "الاسم 20 حرفًا على الأكثر."))

soal("bentuk-persegi", "mudah_sekali", "trimmed", "print({2: 1, 3: 100}.get(i, r.randint(2, 50) if i % 2 == 0 else r.randint(51, 99)))\n",
     program(r'''
class Bentuk {
protected:
    string nama;

public:
    Bentuk(const string &n) : nama(n) {
    }

    void sapa() const {
        cout << "Ini " << nama << endl;
    }
};

class Persegi : public Bentuk {
private:
    int sisi;

public:
    Persegi(int s) : Bentuk("persegi"), sisi(s) {
    }

    int luas() const {
        return sisi * sisi;
    }
};''', r'''    int s;
    cin >> s;
    Persegi p(s);
    p.sapa();
    cout << "Luas: " << p.luas() << endl;''', "string"), wajib=W,
     id=("Bentuk dan Persegi", "Buat class `Bentuk` dengan atribut `protected` `string nama`, konstruktor `Bentuk(const string &n)`, dan method `void sapa() const` yang mencetak `Ini <nama>`.\n\n"
         "Buat class `Persegi` turunan `Bentuk` dengan atribut `int sisi`. Konstruktornya `Persegi(int s) : Bentuk(\"persegi\"), sisi(s) { }` — turunan sendirilah yang memberi tahu induknya siapa namanya. Tambahkan `int luas() const`.\n\n"
         "Baca panjang sisi. Buat sebuah `Persegi`, panggil `sapa()`, lalu cetak `Luas: <luas>`.\n\nUntuk 7:\n\n```\nIni persegi\nLuas: 49\n```" + W_ID,
         "Satu bilangan bulat: panjang sisi.", "Dua baris seperti contoh.", "1 ≤ sisi ≤ 100"),
     en=("Shape and Square", "Make a class `Bentuk` (shape) with a `protected` attribute `string nama`, a constructor `Bentuk(const string &n)`, and a method `void sapa() const` that prints `Ini <name>` (this is a …).\n\n"
         "Make a class `Persegi` (square) derived from `Bentuk` with an attribute `int sisi`. Its constructor is `Persegi(int s) : Bentuk(\"persegi\"), sisi(s) { }` — the derived class itself tells its base what its name is. Add `int luas() const` (area).\n\n"
         "Read the side length. Create a `Persegi`, call `sapa()`, then print `Luas: <area>`.\n\nFor 7:\n\n```\nIni persegi\nLuas: 49\n```" + W_EN,
         "One integer: the side length.", "Two lines as in the example.", "1 ≤ side ≤ 100"),
     ar=("الشكل والمربع", "أنشئ صنفًا `Bentuk` (شكل) فيه خاصية `protected` هي `string nama`، وبانٍ `Bentuk(const string &n)`، وطريقة `void sapa() const` تطبع `Ini <الاسم>` (هذا …).\n\n"
         "أنشئ صنفًا `Persegi` (مربع) مشتقًّا من `Bentuk` فيه خاصية `int sisi`. بانيه `Persegi(int s) : Bentuk(\"persegi\"), sisi(s) { }` — المشتق نفسه يخبر أساسه باسمه. أضف `int luas() const` (المساحة).\n\n"
         "اقرأ طول الضلع. أنشئ `Persegi` واستدعِ `sapa()` ثم اطبع `Luas: <المساحة>`.\n\nللعدد 7:\n\n```\nIni persegi\nLuas: 49\n```" + W_AR,
         "عدد صحيح واحد: طول الضلع.", "سطران كما في المثال.", "1 ≤ الضلع ≤ 100"))

soal("roda-kendaraan", "mudah_sekali", "trimmed", "print('mobil' if i % 2 == 0 else 'motor', r.choice(['Avanza', 'Kijang', 'Ertiga', 'Jazz', 'Supra', 'Vario', 'Beat', 'Mio']))\n",
     program(r'''
class Kendaraan {
protected:
    string merek;
    int roda;

public:
    Kendaraan(const string &m, int r) : merek(m), roda(r) {
    }

    void info() const {
        cout << merek << " beroda " << roda << endl;
    }
};

class Mobil : public Kendaraan {
public:
    Mobil(const string &m) : Kendaraan(m, 4) {
    }
};

class Motor : public Kendaraan {
public:
    Motor(const string &m) : Kendaraan(m, 2) {
    }
};''', r'''    string jenis, merek;
    cin >> jenis >> merek;
    if (jenis == "mobil") {
        Mobil kendaraan(merek);
        kendaraan.info();
    } else {
        Motor kendaraan(merek);
        kendaraan.info();
    }''', "string"), wajib=W,
     id=("Roda Kendaraan", "Satu induk boleh punya **beberapa turunan**. Buat class `Kendaraan` dengan atribut `protected` `string merek`, `int roda`, konstruktor `Kendaraan(const string &m, int r)`, dan method `void info() const` yang mencetak `<merek> beroda <roda>`.\n\n"
         "Buat dua turunannya:\n\n- `Mobil` — konstruktor `Mobil(const string &m) : Kendaraan(m, 4) { }`;\n- `Motor` — konstruktor `Motor(const string &m) : Kendaraan(m, 2) { }`.\n\n"
         "Keduanya tidak menambah apa-apa selain menentukan banyak roda; `info()` cukup ditulis sekali di induk.\n\nBaca jenis (`mobil` atau `motor`) dan merek. Buat objek yang sesuai dan panggil `info()`.\n\nContoh: `mobil Kijang` → `Kijang beroda 4`; `motor Vario` → `Vario beroda 2`." + W_ID,
         "Satu baris berisi jenis kendaraan dan mereknya.", "Satu baris.", "Jenis `mobil` atau `motor`; merek satu kata."),
     en=("Wheels of a Vehicle", "One base may have **several derived classes**. Make a class `Kendaraan` (vehicle) with the `protected` attributes `string merek` (make), `int roda` (wheels), a constructor `Kendaraan(const string &m, int r)`, and a method `void info() const` that prints `<make> beroda <wheels>` (has … wheels).\n\n"
         "Make its two derived classes:\n\n- `Mobil` (car) — constructor `Mobil(const string &m) : Kendaraan(m, 4) { }`;\n- `Motor` (motorcycle) — constructor `Motor(const string &m) : Kendaraan(m, 2) { }`.\n\n"
         "Neither adds anything but the number of wheels; `info()` is written once, in the base.\n\nRead the kind (`mobil` or `motor`) and the make. Create the matching object and call `info()`.\n\nExamples: `mobil Kijang` → `Kijang beroda 4`; `motor Vario` → `Vario beroda 2`." + W_EN,
         "One line with the kind of vehicle and its make.", "One line.", "The kind is `mobil` or `motor`; the make is one word."),
     ar=("عجلات المركبة", "يجوز أن يكون للأساس الواحد **عدة أصناف مشتقة**. أنشئ صنفًا `Kendaraan` (مركبة) خاصيتاه المحميتان `string merek` (الماركة) و `int roda` (العجلات)، وبانٍ `Kendaraan(const string &m, int r)`، وطريقة `void info() const` تطبع `<الماركة> beroda <العجلات>` (لها … عجلات).\n\n"
         "أنشئ مشتقَّيه:\n\n- `Mobil` (سيارة) — الباني `Mobil(const string &m) : Kendaraan(m, 4) { }`؛\n- `Motor` (دراجة نارية) — الباني `Motor(const string &m) : Kendaraan(m, 2) { }`.\n\n"
         "لا يضيف أيٌّ منهما غير عدد العجلات؛ و `info()` تُكتب مرة واحدة في الأساس.\n\nاقرأ النوع (`mobil` أو `motor`) والماركة. أنشئ الكائن المناسب واستدعِ `info()`.\n\nأمثلة: `mobil Kijang` ← `Kijang beroda 4`؛ `motor Vario` ← `Vario beroda 2`." + W_AR,
         "سطر واحد فيه نوع المركبة وماركتها.", "سطر واحد.", "النوع `mobil` أو `motor`؛ والماركة كلمة واحدة."))

soal("tabungan-protected", "mudah_sekali", "trimmed", "print(r.randint(1, 500) * 10000, r.randint(1, 12))\n",
     program(r'''
class Akun {
protected:
    int saldo;

public:
    Akun(int awal) : saldo(awal) {
    }

    int lihat() const {
        return saldo;
    }
};

class Tabungan : public Akun {
public:
    Tabungan(int awal) : Akun(awal) {
    }

    void beriBunga(int persen) {
        saldo += saldo / 100 * persen;
    }
};''', r'''    int saldo, persen;
    cin >> saldo >> persen;
    Tabungan t(saldo);
    t.beriBunga(persen);
    cout << t.lihat() << endl;'''), wajib=W,
     id=("Anggota protected", "Anggota `private` milik induk **tidak bisa** disentuh turunannya. Anggota `protected` adalah jalan tengah: tertutup bagi kode luar, tetapi terbuka bagi class turunan.\n\n"
         "Buat class `Akun` dengan atribut `protected` `int saldo`, konstruktor `Akun(int awal)`, dan `int lihat() const`.\n\n"
         "Buat class `Tabungan` turunan `Akun` dengan method `void beriBunga(int persen)` yang **langsung mengubah `saldo`** warisan: saldo bertambah sebesar persen dari saldo.\n\n"
         "Baca saldo dan persen bunga, beri bunga satu kali, lalu cetak saldonya.\n\nUntuk `200000 5` jawabannya 210000." + W_ID,
         "Satu baris berisi saldo dan persen bunga.", "Satu bilangan bulat: saldo setelah bunga.", "Saldo kelipatan 10000 antara 10000 dan 5000000; 1 ≤ persen ≤ 12"),
     en=("protected Members", "A `private` member of the base **cannot** be touched by its derived classes. A `protected` member is the middle way: closed to outside code, but open to derived classes.\n\n"
         "Make a class `Akun` (account) with a `protected` attribute `int saldo` (balance), a constructor `Akun(int awal)`, and `int lihat() const`.\n\n"
         "Make a class `Tabungan` (savings) derived from `Akun` with a method `void beriBunga(int persen)` (add interest) that **changes the inherited `saldo` directly**: the balance grows by that percentage of the balance.\n\n"
         "Read the balance and the interest percentage, add interest once, then print the balance.\n\nFor `200000 5` the answer is 210000." + W_EN,
         "One line with the balance and the interest percentage.", "One integer: the balance after interest.", "Balances are multiples of 10000 between 10000 and 5000000; 1 ≤ percentage ≤ 12"),
     ar=("الأعضاء المحمية", "العضو `private` في الأساس **لا تستطيع** أصنافه المشتقة أن تمسّه. والعضو `protected` حل وسط: مغلق أمام الكود الخارجي ومفتوح للأصناف المشتقة.\n\n"
         "أنشئ صنفًا `Akun` (حساب) فيه خاصية `protected` هي `int saldo` (الرصيد)، وبانٍ `Akun(int awal)`، و `int lihat() const`.\n\n"
         "أنشئ صنفًا `Tabungan` (توفير) مشتقًّا من `Akun` فيه طريقة `void beriBunga(int persen)` (أضف الفائدة) **تغيّر `saldo` الموروث مباشرة**: يزيد الرصيد بتلك النسبة منه.\n\n"
         "اقرأ الرصيد ونسبة الفائدة وأضف الفائدة مرة واحدة ثم اطبع الرصيد.\n\nعند `200000 5` الجواب 210000." + W_AR,
         "سطر واحد فيه الرصيد ونسبة الفائدة.", "عدد صحيح واحد: الرصيد بعد الفائدة.", "الرصيد من مضاعفات 10000 بين 10000 و 5000000؛ 1 ≤ النسبة ≤ 12"))

soal("panggil-induk", "mudah_sekali", "trimmed", NAMA + "print(r.choice(NAMA), r.randint(21001, 26999))\n",
     program(r'''
class Orang {
protected:
    string nama;

public:
    Orang(const string &n) : nama(n) {
    }

    void perkenalan() const {
        cout << "Saya " << nama << endl;
    }
};

class Mahasiswa : public Orang {
private:
    int nim;

public:
    Mahasiswa(const string &n, int nomor) : Orang(n), nim(nomor) {
    }

    void perkenalan() const {
        Orang::perkenalan();
        cout << "NIM saya " << nim << endl;
    }
};''', r'''    string nama;
    int nim;
    cin >> nama >> nim;
    Mahasiswa m(nama, nim);
    m.perkenalan();''', "string"), wajib=W,
     id=("Memanggil Method Induk", "Turunan boleh punya method yang **bernama sama** dengan method induknya. Method turunan lalu menutupi method induk — tetapi ia masih bisa memanggil versi induk dengan menyebut nama class-nya: `Orang::perkenalan()`.\n\n"
         "Buat class `Orang` (atribut `protected` `string nama`) dengan method `void perkenalan() const` yang mencetak `Saya <nama>`.\n\n"
         "Buat class `Mahasiswa` turunan `Orang` dengan atribut `int nim` dan method `void perkenalan() const` sendiri, yang **lebih dulu memanggil `Orang::perkenalan()`** lalu mencetak `NIM saya <nim>`.\n\n"
         "Baca nama dan NIM, buat satu `Mahasiswa`, dan panggil `perkenalan()` satu kali.\n\nUntuk `Dewi 23017`:\n\n```\nSaya Dewi\nNIM saya 23017\n```" + W_ID,
         "Satu baris berisi nama dan NIM.", "Dua baris seperti contoh.", "Nama satu kata; NIM lima digit."),
     en=("Calling the Base Method", "A derived class may have a method with the **same name** as a method of its base. The derived method then covers the base one — but it can still call the base version by naming the class: `Orang::perkenalan()`.\n\n"
         "Make a class `Orang` (person; `protected` attribute `string nama`) with a method `void perkenalan() const` (introduction) that prints `Saya <name>` (I am …).\n\n"
         "Make a class `Mahasiswa` (student) derived from `Orang` with an attribute `int nim` (student number) and its own method `void perkenalan() const`, which **first calls `Orang::perkenalan()`** and then prints `NIM saya <nim>` (my student number is …).\n\n"
         "Read a name and a student number, create one `Mahasiswa`, and call `perkenalan()` once.\n\nFor `Dewi 23017`:\n\n```\nSaya Dewi\nNIM saya 23017\n```" + W_EN,
         "One line with the name and the student number.", "Two lines as in the example.", "The name is one word; the student number has five digits."),
     ar=("استدعاء طريقة الأساس", "يجوز أن تكون للمشتق طريقة **بالاسم نفسه** لطريقة في أساسه. عندئذ تحجب طريقةُ المشتق طريقةَ الأساس — لكنها تستطيع استدعاء نسخة الأساس بذكر اسم الصنف: `Orang::perkenalan()`.\n\n"
         "أنشئ صنفًا `Orang` (شخص؛ الخاصية المحمية `string nama`) فيه طريقة `void perkenalan() const` (تعريف) تطبع `Saya <الاسم>` (أنا …).\n\n"
         "أنشئ صنفًا `Mahasiswa` (طالب) مشتقًّا من `Orang` فيه خاصية `int nim` (الرقم الجامعي) وطريقته الخاصة `void perkenalan() const` التي **تستدعي أولًا `Orang::perkenalan()`** ثم تطبع `NIM saya <nim>` (رقمي الجامعي …).\n\n"
         "اقرأ اسمًا ورقمًا جامعيًا وأنشئ `Mahasiswa` واحدًا واستدعِ `perkenalan()` مرة واحدة.\n\nعند `Dewi 23017`:\n\n```\nSaya Dewi\nNIM saya 23017\n```" + W_AR,
         "سطر واحد فيه الاسم والرقم الجامعي.", "سطران كما في المثال.", "الاسم كلمة واحدة؛ والرقم الجامعي خمسة أرقام."))

soal("infanteri-atau-tank", "mudah_sekali", "trimmed", "if i % 2 == 0:\n    print('infanteri', r.randint(5, 200))\nelse:\n    print('tank', r.randint(2, 6), r.randint(1, 30))\n",
     program(r'''
class Unit {
protected:
    int personel;

public:
    Unit(int p) : personel(p) {
    }

    int dasar() const {
        return personel * 2;
    }
};

class Infanteri : public Unit {
public:
    Infanteri(int p) : Unit(p) {
    }

    int kekuatan() const {
        return dasar();
    }
};

class Tank : public Unit {
private:
    int lapisBaja;

public:
    Tank(int awak, int baja) : Unit(awak), lapisBaja(baja) {
    }

    int kekuatan() const {
        return dasar() + lapisBaja * 10;
    }
};''', r'''    string jenis;
    cin >> jenis;
    if (jenis == "infanteri") {
        int p;
        cin >> p;
        Infanteri unit(p);
        cout << unit.kekuatan() << endl;
    } else {
        int awak, baja;
        cin >> awak >> baja;
        Tank unit(awak, baja);
        cout << unit.kekuatan() << endl;
    }''', "string"), wajib=W,
     id=("Infanteri atau Tank", "Buat class `Unit` dengan atribut `protected` `int personel`, konstruktor `Unit(int p)`, dan method `int dasar() const` — kekuatan dasar sebuah satuan: personel × 2.\n\n"
         "Buat dua turunannya, masing-masing dengan method `int kekuatan() const`:\n\n- `Infanteri` — kekuatannya sama dengan `dasar()`;\n- `Tank` — punya atribut tambahan `int lapisBaja`; kekuatannya `dasar()` ditambah lapisBaja × 10.\n\n"
         "Bagian yang sama ditulis sekali di induk; tiap turunan hanya menulis yang berbeda.\n\n"
         "Masukan: `infanteri <personel>`, atau `tank <awak> <lapisBaja>`. Cetak kekuatannya.\n\nContoh: `infanteri 30` → 60; `tank 4 12` → 128." + W_ID,
         "Satu baris: jenis satuan diikuti satu atau dua bilangan.", "Satu bilangan bulat: kekuatan.", "Personel 5 sampai 200; awak 2 sampai 6; lapis baja 1 sampai 30"),
     en=("Infantry or Tank", "Make a class `Unit` with a `protected` attribute `int personel`, a constructor `Unit(int p)`, and a method `int dasar() const` — the basic strength of a unit: personnel × 2.\n\n"
         "Make its two derived classes, each with a method `int kekuatan() const` (strength):\n\n- `Infanteri` — its strength equals `dasar()`;\n- `Tank` — has an extra attribute `int lapisBaja` (armour); its strength is `dasar()` plus armour × 10.\n\n"
         "What they share is written once in the base; each derived class writes only what differs.\n\n"
         "Input: `infanteri <personnel>`, or `tank <crew> <armour>`. Print the strength.\n\nExamples: `infanteri 30` → 60; `tank 4 12` → 128." + W_EN,
         "One line: the kind of unit followed by one or two numbers.", "One integer: the strength.", "Personnel 5 to 200; crew 2 to 6; armour 1 to 30"),
     ar=("مشاة أم دبابة", "أنشئ صنفًا `Unit` (وحدة) فيه خاصية `protected` هي `int personel` (الأفراد)، وبانٍ `Unit(int p)`، وطريقة `int dasar() const` — القوة الأساسية للوحدة: الأفراد × 2.\n\n"
         "أنشئ مشتقَّيه، ولكل منهما طريقة `int kekuatan() const` (القوة):\n\n- `Infanteri` (مشاة) — قوتها تساوي `dasar()`؛\n- `Tank` (دبابة) — لها خاصية إضافية `int lapisBaja` (الدرع)؛ وقوتها `dasar()` زائد الدرع × 10.\n\n"
         "المشترك يُكتب مرة واحدة في الأساس؛ وكل مشتق يكتب ما يختلف فقط.\n\n"
         "المدخل: `infanteri <الأفراد>` أو `tank <الطاقم> <الدرع>`. اطبع القوة.\n\nأمثلة: `infanteri 30` ← 60؛ `tank 4 12` ← 128." + W_AR,
         "سطر واحد: نوع الوحدة يليه عدد أو عددان.", "عدد صحيح واحد: القوة.", "الأفراد من 5 إلى 200؛ الطاقم من 2 إلى 6؛ الدرع من 1 إلى 30"))

# ================================= MUDAH (8) =================================

soal("tiga-tingkat", "mudah", "trimmed", SATUAN + "print(r.choice(SATUAN[:6]) if i % 2 == 0 else r.choice(SATUAN[6:]))\n",
     program(r'''
class Unit {
protected:
    string nama;

public:
    Unit(const string &n) : nama(n) {
        cout << "+Unit " << nama << endl;
    }

    ~Unit() {
        cout << "-Unit " << nama << endl;
    }
};

class UnitDarat : public Unit {
public:
    UnitDarat(const string &n) : Unit(n) {
        cout << "+UnitDarat " << nama << endl;
    }

    ~UnitDarat() {
        cout << "-UnitDarat " << nama << endl;
    }
};

class Infanteri : public UnitDarat {
public:
    Infanteri(const string &n) : UnitDarat(n) {
        cout << "+Infanteri " << nama << endl;
    }

    ~Infanteri() {
        cout << "-Infanteri " << nama << endl;
    }
};''', r'''    string nama;
    cin >> nama;
    Infanteri pasukan(nama);
    cout << "Siap tempur" << endl;''', "string"), wajib=W,
     id=("Tiga Tingkat", "Pewarisan bisa **bertingkat**: `Infanteri` adalah `UnitDarat`, dan `UnitDarat` adalah `Unit`. Objek `Infanteri` dibangun dari tingkat paling atas ke bawah, dan dihancurkan dari bawah ke atas.\n\n"
         "Buat tiga class — `Unit` (atribut `protected` `string nama`), `UnitDarat` turunan `Unit`, dan `Infanteri` turunan `UnitDarat`. Konstruktor tiap class mencetak `+<NamaClass> <nama>`, destruktornya `-<NamaClass> <nama>`. Tiap konstruktor meneruskan nama ke induk **langsungnya**.\n\n"
         "Baca sebuah nama, buat satu `Infanteri`, lalu cetak `Siap tempur`.\n\nUntuk `Elang`:\n\n```\n+Unit Elang\n+UnitDarat Elang\n+Infanteri Elang\nSiap tempur\n-Infanteri Elang\n-UnitDarat Elang\n-Unit Elang\n```" + W_ID,
         "Satu kata: nama satuan.", "Tujuh baris seperti contoh.", "Nama paling panjang 20 huruf."),
     en=("Three Levels", "Inheritance can go **several levels deep**: an `Infanteri` is a `UnitDarat`, and a `UnitDarat` is a `Unit`. An `Infanteri` object is built from the top level down, and destroyed from the bottom up.\n\n"
         "Make three classes — `Unit` (`protected` attribute `string nama`), `UnitDarat` (ground unit) derived from `Unit`, and `Infanteri` derived from `UnitDarat`. The constructor of each class prints `+<ClassName> <name>`, its destructor `-<ClassName> <name>`. Each constructor passes the name to its **direct** base.\n\n"
         "Read a name, create one `Infanteri`, then print `Siap tempur` (ready for battle).\n\nFor `Elang`:\n\n```\n+Unit Elang\n+UnitDarat Elang\n+Infanteri Elang\nSiap tempur\n-Infanteri Elang\n-UnitDarat Elang\n-Unit Elang\n```" + W_EN,
         "One word: the name of the unit.", "Seven lines as in the example.", "The name has at most 20 letters."),
     ar=("ثلاثة مستويات", "الوراثة قد تكون **متعددة المستويات**: `Infanteri` هي `UnitDarat`، و `UnitDarat` هي `Unit`. يُبنى كائن `Infanteri` من المستوى الأعلى نزولًا، ويُهدم من الأسفل صعودًا.\n\n"
         "أنشئ ثلاثة أصناف — `Unit` (الخاصية المحمية `string nama`) و `UnitDarat` (وحدة برية) المشتق من `Unit` و `Infanteri` المشتق من `UnitDarat`. باني كل صنف يطبع `+<اسم الصنف> <الاسم>` وهادمه `-<اسم الصنف> <الاسم>`. وكل بانٍ يمرّر الاسم إلى أساسه **المباشر**.\n\n"
         "اقرأ اسمًا وأنشئ `Infanteri` واحدة ثم اطبع `Siap tempur` (جاهزة للقتال).\n\nعند `Elang`:\n\n```\n+Unit Elang\n+UnitDarat Elang\n+Infanteri Elang\nSiap tempur\n-Infanteri Elang\n-UnitDarat Elang\n-Unit Elang\n```" + W_AR,
         "كلمة واحدة: اسم الوحدة.", "سبعة أسطر كما في المثال.", "الاسم 20 حرفًا على الأكثر."))

soal("sembunyi-nama", "mudah", "trimmed", "print(r.randint(-99, 99), r.randint(-99, 99))\n",
     program(r'''
class Induk {
protected:
    int x;

public:
    Induk(int a) : x(a) {
    }

    void cetak() const {
        cout << "Induk: " << x << endl;
    }
};

class Anak : public Induk {
private:
    int y;

public:
    Anak(int a, int b) : Induk(a), y(b) {
    }

    void cetak() const {
        cout << "Anak: " << y << endl;
    }
};''', r'''    int a, b;
    cin >> a >> b;
    Anak anak(a, b);
    anak.cetak();
    anak.Induk::cetak();'''), wajib=W,
     id=("Nama yang Tersembunyi", "Bila turunan mendefinisikan method bernama sama dengan method induk, method induk **tersembunyi**: pemanggilan biasa pada objek turunan selalu mengenai versi turunan. Versi induk tidak hilang — ia masih bisa dipanggil dari luar dengan menyebut class-nya: `anak.Induk::cetak()`.\n\n"
         "Buat class `Induk` (atribut `protected` `int x`; `void cetak() const` mencetak `Induk: <x>`) dan class `Anak` turunannya (atribut `int y`; `void cetak() const` mencetak `Anak: <y>`).\n\n"
         "Baca x dan y. Buat satu objek `Anak`, panggil `cetak()` secara biasa, lalu panggil versi induknya.\n\nUntuk `5 8`:\n\n```\nAnak: 8\nInduk: 5\n```" + W_ID,
         "Satu baris berisi x dan y.", "Dua baris seperti contoh.", "−99 ≤ x, y ≤ 99"),
     en=("A Hidden Name", "When a derived class defines a method with the same name as a base method, the base method is **hidden**: an ordinary call on a derived object always reaches the derived version. The base version is not gone — it can still be called from outside by naming the class: `anak.Induk::cetak()`.\n\n"
         "Make a class `Induk` (base; `protected` attribute `int x`; `void cetak() const` prints `Induk: <x>`) and a class `Anak` (derived; attribute `int y`; `void cetak() const` prints `Anak: <y>`).\n\n"
         "Read x and y. Create one `Anak` object, call `cetak()` the ordinary way, then call the base version.\n\nFor `5 8`:\n\n```\nAnak: 8\nInduk: 5\n```" + W_EN,
         "One line with x and y.", "Two lines as in the example.", "−99 ≤ x, y ≤ 99"),
     ar=("اسم محجوب", "إذا عرّف المشتق طريقة بالاسم نفسه لطريقة في الأساس صارت طريقة الأساس **محجوبة**: الاستدعاء العادي على كائن مشتق يصيب نسخة المشتق دائمًا. لكن نسخة الأساس لم تزُل — يمكن استدعاؤها من الخارج بذكر الصنف: `anak.Induk::cetak()`.\n\n"
         "أنشئ صنفًا `Induk` (أساس؛ الخاصية المحمية `int x`؛ و `void cetak() const` تطبع `Induk: <x>`) وصنفًا `Anak` مشتقًّا منه (الخاصية `int y`؛ و `void cetak() const` تطبع `Anak: <y>`).\n\n"
         "اقرأ x و y. أنشئ كائن `Anak` واحدًا واستدعِ `cetak()` بالطريقة العادية ثم استدعِ نسخة الأساس.\n\nعند `5 8`:\n\n```\nAnak: 8\nInduk: 5\n```" + W_AR,
         "سطر واحد فيه x و y.", "سطران كما في المثال.", "−99 ≤ x, y ≤ 99"))

soal("tanpa-virtual", "mudah", "trimmed", "print('kucing' if i % 2 == 0 else 'anjing', r.choice(['Belang', 'Oyen', 'Hitam', 'Putih', 'Bruno', 'Molly', 'Rex', 'Kopi']))\n",
     program(r'''
class Hewan {
protected:
    string nama;

public:
    Hewan(const string &n) : nama(n) {
    }

    string getNama() const {
        return nama;
    }

    string suara() const {
        return "...";
    }
};

class Kucing : public Hewan {
public:
    Kucing(const string &n) : Hewan(n) {
    }

    string suara() const {
        return "meong";
    }
};

class Anjing : public Hewan {
public:
    Anjing(const string &n) : Hewan(n) {
    }

    string suara() const {
        return "guk";
    }
};

void lewatInduk(const Hewan &h) {
    cout << h.getNama() << " lewat Hewan: " << h.suara() << endl;
}''', r'''    string jenis, nama;
    cin >> jenis >> nama;
    if (jenis == "kucing") {
        Kucing hewan(nama);
        cout << nama << " langsung: " << hewan.suara() << endl;
        lewatInduk(hewan);
    } else {
        Anjing hewan(nama);
        cout << nama << " langsung: " << hewan.suara() << endl;
        lewatInduk(hewan);
    }''', "string"), wajib=W,
     id=("Tanpa virtual", "Objek turunan boleh dikirim ke fungsi yang meminta induknya: seekor kucing *adalah* hewan. Tetapi **tanpa `virtual`**, method yang dipanggil ditentukan oleh tipe yang tertulis di kode, bukan oleh objek sebenarnya. Lewat `const Hewan &`, yang terpanggil adalah `Hewan::suara()`.\n\n"
         "Buat class `Hewan` (atribut `protected` `string nama`; `getNama()`; `string suara() const` mengembalikan `...`), dan dua turunannya yang masing-masing punya `suara()` sendiri: `Kucing` mengembalikan `meong`, `Anjing` mengembalikan `guk`.\n\n"
         "Tulis fungsi `void lewatInduk(const Hewan &h)` yang mencetak `<nama> lewat Hewan: <h.suara()>`.\n\n"
         "Baca jenis (`kucing` atau `anjing`) dan nama. Buat objeknya, cetak `<nama> langsung: <suara()>` dari objek itu sendiri, lalu kirim objek yang sama ke `lewatInduk`.\n\n"
         "Untuk `kucing Oyen`:\n\n```\nOyen langsung: meong\nOyen lewat Hewan: ...\n```\n\nBaris kedua itulah masalah yang dipecahkan `virtual` pada pertemuan berikutnya." + W_ID,
         "Satu baris berisi jenis hewan dan namanya.", "Dua baris seperti contoh.", "Jenis `kucing` atau `anjing`; nama satu kata."),
     en=("Without virtual", "A derived object may be passed to a function that asks for its base: a cat *is an* animal. But **without `virtual`** the method called is decided by the type written in the code, not by the actual object. Through `const Hewan &` it is `Hewan::suara()` that gets called.\n\n"
         "Make a class `Hewan` (animal; `protected` attribute `string nama`; `getNama()`; `string suara() const` (sound) returns `...`), and two derived classes, each with its own `suara()`: `Kucing` (cat) returns `meong`, `Anjing` (dog) returns `guk`.\n\n"
         "Write a function `void lewatInduk(const Hewan &h)` (through the base) that prints `<name> lewat Hewan: <h.suara()>`.\n\n"
         "Read the kind (`kucing` or `anjing`) and a name. Create the object, print `<name> langsung: <suara()>` (directly) from the object itself, then pass the same object to `lewatInduk`.\n\n"
         "For `kucing Oyen`:\n\n```\nOyen langsung: meong\nOyen lewat Hewan: ...\n```\n\nThat second line is the problem `virtual` solves in the next meeting." + W_EN,
         "One line with the kind of animal and its name.", "Two lines as in the example.", "The kind is `kucing` or `anjing`; the name is one word."),
     ar=("بلا virtual", "يجوز تمرير كائن مشتق إلى دالة تطلب أساسه: القطة *هي* حيوان. لكن **بلا `virtual`** تتحدد الطريقة المستدعاة بالنوع المكتوب في الكود لا بالكائن الفعلي. فعبر `const Hewan &` تُستدعى `Hewan::suara()`.\n\n"
         "أنشئ صنفًا `Hewan` (حيوان؛ الخاصية المحمية `string nama`؛ و `getNama()`؛ و `string suara() const` (الصوت) تُرجع `...`)، ومشتقَّين لكل منهما `suara()` خاصة: `Kucing` (قطة) تُرجع `meong` و `Anjing` (كلب) يُرجع `guk`.\n\n"
         "اكتب دالة `void lewatInduk(const Hewan &h)` (عبر الأساس) تطبع `<الاسم> lewat Hewan: <h.suara()>`.\n\n"
         "اقرأ النوع (`kucing` أو `anjing`) واسمًا. أنشئ الكائن واطبع `<الاسم> langsung: <suara()>` (مباشرة) من الكائن نفسه ثم مرِّر الكائن نفسه إلى `lewatInduk`.\n\n"
         "عند `kucing Oyen`:\n\n```\nOyen langsung: meong\nOyen lewat Hewan: ...\n```\n\nذلك السطر الثاني هو المشكلة التي يحلّها `virtual` في اللقاء التالي." + W_AR,
         "سطر واحد فيه نوع الحيوان واسمه.", "سطران كما في المثال.", "النوع `kucing` أو `anjing`؛ والاسم كلمة واحدة."))

soal("induk-anggota-diri", "mudah", "trimmed", "print(r.choice(['Avanza', 'Kijang', 'Ertiga', 'Jazz', 'Brio', 'Agya']), r.randint(60, 300))\n",
     program(r'''
class Kendaraan {
public:
    Kendaraan() {
        cout << "+Kendaraan" << endl;
    }

    ~Kendaraan() {
        cout << "-Kendaraan" << endl;
    }
};

class Mesin {
private:
    int tenaga;

public:
    Mesin(int t) : tenaga(t) {
        cout << "+Mesin " << tenaga << endl;
    }

    ~Mesin() {
        cout << "-Mesin " << tenaga << endl;
    }
};

class Mobil : public Kendaraan {
private:
    string nama;
    Mesin mesin;

public:
    Mobil(const string &n, int tenaga) : nama(n), mesin(tenaga) {
        cout << "+Mobil " << nama << endl;
    }

    ~Mobil() {
        cout << "-Mobil " << nama << endl;
    }
};''', r'''    string nama;
    int tenaga;
    cin >> nama >> tenaga;
    Mobil mobil(nama, tenaga);''', "string"), wajib=W,
     id=("Induk, Anggota, Diri Sendiri", "Sebuah mobil **adalah** kendaraan (pewarisan) dan **punya** mesin (komposisi). Saat objeknya dibuat, urutannya selalu: **bagian induk**, lalu **objek-objek anggota**, lalu **badan konstruktornya sendiri**. Penghancurannya terbalik.\n\n"
         "Buat class `Kendaraan` (konstruktor mencetak `+Kendaraan`, destruktor `-Kendaraan`), class `Mesin` (atribut `int tenaga`; `+Mesin <tenaga>` / `-Mesin <tenaga>`), dan class `Mobil` yang **mewarisi `Kendaraan`** dan **memuat sebuah `Mesin`** (`+Mobil <nama>` / `-Mobil <nama>`).\n\n"
         "Baca nama dan tenaga, lalu buat satu `Mobil`.\n\nUntuk `Kijang 100`:\n\n```\n+Kendaraan\n+Mesin 100\n+Mobil Kijang\n-Mobil Kijang\n-Mesin 100\n-Kendaraan\n```" + W_ID,
         "Satu baris berisi nama mobil dan tenaga mesinnya.", "Enam baris seperti contoh.", "60 ≤ tenaga ≤ 300"),
     en=("Base, Members, Self", "A car **is a** vehicle (inheritance) and **has an** engine (composition). When the object is created the order is always: the **base part**, then the **member objects**, then **its own constructor body**. Destruction is the reverse.\n\n"
         "Make a class `Kendaraan` (vehicle; the constructor prints `+Kendaraan`, the destructor `-Kendaraan`), a class `Mesin` (engine; attribute `int tenaga`; `+Mesin <power>` / `-Mesin <power>`), and a class `Mobil` (car) that **inherits `Kendaraan`** and **contains a `Mesin`** (`+Mobil <name>` / `-Mobil <name>`).\n\n"
         "Read the name and the power, then create one `Mobil`.\n\nFor `Kijang 100`:\n\n```\n+Kendaraan\n+Mesin 100\n+Mobil Kijang\n-Mobil Kijang\n-Mesin 100\n-Kendaraan\n```" + W_EN,
         "One line with the car's name and its engine power.", "Six lines as in the example.", "60 ≤ power ≤ 300"),
     ar=("الأساس ثم الأعضاء ثم الذات", "السيارة **هي** مركبة (وراثة) و**لها** محرك (تركيب). عند إنشاء الكائن يكون الترتيب دائمًا: **جزء الأساس** ثم **الكائنات الأعضاء** ثم **جسم بانيه هو**. والهدم بالعكس.\n\n"
         "أنشئ صنفًا `Kendaraan` (مركبة؛ الباني يطبع `+Kendaraan` والهادم `-Kendaraan`)، وصنفًا `Mesin` (محرك؛ الخاصية `int tenaga`؛ `+Mesin <القدرة>` / `-Mesin <القدرة>`)، وصنفًا `Mobil` (سيارة) **يرث `Kendaraan`** و**يحوي `Mesin`** (`+Mobil <الاسم>` / `-Mobil <الاسم>`).\n\n"
         "اقرأ الاسم والقدرة ثم أنشئ `Mobil` واحدة.\n\nعند `Kijang 100`:\n\n```\n+Kendaraan\n+Mesin 100\n+Mobil Kijang\n-Mobil Kijang\n-Mesin 100\n-Kendaraan\n```" + W_AR,
         "سطر واحد فيه اسم السيارة وقدرة محركها.", "ستة أسطر كما في المثال.", "60 ≤ القدرة ≤ 300"))

soal("giro-cerukan", "mudah", "trimmed", "print(r.randint(0, 20) * 50000, r.randint(1, 10) * 100000)\nfor _ in range(r.randint(1, 12)):\n    print(r.choice(['tarik', 'tarik', 'setor']), r.randint(1, 20) * 50000)\nprint('selesai')\n",
     program(r'''
class Rekening {
protected:
    int saldo;

public:
    Rekening(int awal) : saldo(awal) {
    }

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

    int lihat() const {
        return saldo;
    }
};

class Giro : public Rekening {
private:
    int batas;

public:
    Giro(int awal, int b) : Rekening(awal), batas(b) {
    }

    bool tarik(int jumlah) {
        if (saldo - jumlah < -batas) {
            return false;
        }
        saldo -= jumlah;
        return true;
    }
};''', r'''    int awal, batas;
    cin >> awal >> batas;
    Giro giro(awal, batas);
    string perintah;
    while (cin >> perintah) {
        if (perintah == "selesai") {
            break;
        }
        int jumlah;
        cin >> jumlah;
        if (perintah == "setor") {
            giro.setor(jumlah);
            cout << "Saldo: " << giro.lihat() << endl;
        } else if (giro.tarik(jumlah)) {
            cout << "Saldo: " << giro.lihat() << endl;
        } else {
            cout << "Ditolak" << endl;
        }
    }''', "string"), wajib=W,
     id=("Rekening Giro", "Rekening biasa menolak penarikan yang melebihi saldo. **Rekening giro** boleh bersaldo negatif sampai batas tertentu (cerukan). Selebihnya ia sama dengan rekening biasa — jadi cukup mewarisinya dan **mengganti satu method**.\n\n"
         "Buat class `Rekening` (atribut `protected` `int saldo`; `void setor(int)`; `bool tarik(int)` yang gagal bila jumlah melebihi saldo; `int lihat() const`).\n\n"
         "Buat class `Giro` turunannya dengan atribut `int batas` dan method `bool tarik(int jumlah)` sendiri: penarikan diterima selama saldo sesudahnya **tidak kurang dari −batas**. `setor` dan `lihat` dipakai dari induk apa adanya.\n\n"
         "Masukan: saldo awal dan batas cerukan; lalu perintah `setor x` atau `tarik x`, diakhiri `selesai`. Untuk tiap perintah cetak `Saldo: s`, atau `Ditolak`.\n\n"
         "Contoh: `100000 200000`; `tarik 250000`, `tarik 100000`, `setor 50000`, `selesai` →\n\n```\nSaldo: -150000\nDitolak\nSaldo: -100000\n```" + W_ID,
         "Baris pertama: saldo awal dan batas cerukan. Lalu perintah-perintah, diakhiri `selesai`.", "Satu baris per perintah.", "Saldo awal 0 sampai 1000000; batas 100000 sampai 1000000; paling banyak 12 perintah; jumlah kelipatan 50000"),
     en=("Current Account", "An ordinary account refuses a withdrawal larger than its balance. A **current account** (`Giro`) may go negative down to a limit (overdraft). In every other respect it is an ordinary account — so it is enough to inherit from it and **replace one method**.\n\n"
         "Make a class `Rekening` (account; `protected` attribute `int saldo`; `void setor(int)`; `bool tarik(int)`, which fails when the amount exceeds the balance; `int lihat() const`).\n\n"
         "Make a class `Giro` derived from it with an attribute `int batas` (limit) and its own method `bool tarik(int jumlah)`: a withdrawal is accepted as long as the balance afterwards is **not below −limit**. `setor` and `lihat` are used from the base as they are.\n\n"
         "Input: the opening balance and the overdraft limit; then commands `setor x` or `tarik x`, ending with `selesai`. For each command print `Saldo: s`, or `Ditolak`.\n\n"
         "Example: `100000 200000`; `tarik 250000`, `tarik 100000`, `setor 50000`, `selesai` →\n\n```\nSaldo: -150000\nDitolak\nSaldo: -100000\n```" + W_EN,
         "First line: the opening balance and the overdraft limit. Then the commands, ending with `selesai`.", "One line per command.", "Opening balance 0 to 1000000; limit 100000 to 1000000; at most 12 commands; amounts are multiples of 50000"),
     ar=("الحساب الجاري", "الحساب العادي يرفض سحبًا يزيد على رصيده. أما **الحساب الجاري** (`Giro`) فيجوز أن يصير رصيده سالبًا حتى حدٍّ معيّن (السحب على المكشوف). وفي ما عدا ذلك هو حساب عادي — فيكفي أن يرثه و**يستبدل طريقة واحدة**.\n\n"
         "أنشئ صنفًا `Rekening` (حساب؛ الخاصية المحمية `int saldo`؛ و `void setor(int)`؛ و `bool tarik(int)` التي تفشل إذا زاد المبلغ على الرصيد؛ و `int lihat() const`).\n\n"
         "أنشئ صنفًا `Giro` مشتقًّا منه فيه خاصية `int batas` (الحد) وطريقته الخاصة `bool tarik(int jumlah)`: يُقبل السحب ما دام الرصيد بعده **لا يقل عن −الحد**. و `setor` و `lihat` تُستعملان من الأساس كما هما.\n\n"
         "المدخل: الرصيد الابتدائي وحد المكشوف؛ ثم أوامر `setor x` أو `tarik x` تنتهي بـ `selesai`. لكل أمر اطبع `Saldo: s` أو `Ditolak`.\n\n"
         "مثال: `100000 200000`؛ `tarik 250000` ثم `tarik 100000` ثم `setor 50000` ثم `selesai` ←\n\n```\nSaldo: -150000\nDitolak\nSaldo: -100000\n```" + W_AR,
         "السطر الأول: الرصيد الابتدائي وحد المكشوف. ثم الأوامر منتهيةً بـ `selesai`.", "سطر لكل أمر.", "الرصيد الابتدائي من 0 إلى 1000000؛ الحد من 100000 إلى 1000000؛ 12 أمرًا على الأكثر؛ المبالغ من مضاعفات 50000"))

soal("atur-dua-cara", "mudah", "trimmed", "KATA = ['merah', 'biru', 'hijau', 'kuning', 'putih', 'hitam']\nn = r.randint(1, 8)\nprint(n)\nfor _ in range(n):\n    if r.random() < 0.5:\n        print('angka', r.randint(-99, 99))\n    else:\n        print('teks', r.choice(KATA))\n",
     program(r'''
class Induk {
protected:
    int nilai = 0;

public:
    void atur(int x) {
        nilai = x;
    }

    int getNilai() const {
        return nilai;
    }
};

class Anak : public Induk {
private:
    string label = "-";

public:
    using Induk::atur;

    void atur(const string &s) {
        label = s;
    }

    string getLabel() const {
        return label;
    }
};''', r'''    int n;
    cin >> n;
    Anak anak;
    for (int k = 0; k < n; k++) {
        string jenis;
        cin >> jenis;
        if (jenis == "angka") {
            int x;
            cin >> x;
            anak.atur(x);
        } else {
            string s;
            cin >> s;
            anak.atur(s);
        }
    }
    cout << anak.getNilai() << " " << anak.getLabel() << endl;''', "string"), wajib=W,
     id=("Mengatur dengan Dua Cara", "Penyembunyian nama berlaku untuk **semua** method induk yang bernama sama, apa pun parameternya. Bila `Induk` punya `atur(int)` dan `Anak` menambah `atur(const string &)`, maka `anak.atur(5)` tidak lagi menemukan versi `int`.\n\n"
         "Obatnya satu baris di dalam `Anak`: `using Induk::atur;` — baris itu membawa semua `atur` milik induk ke dalam turunan, sehingga keduanya menjadi *overload* yang sejajar.\n\n"
         "Buat class `Induk` (atribut `protected` `int nilai`, mula-mula 0; `void atur(int x)`; `getNilai()`) dan class `Anak` turunannya (atribut `string label`, mula-mula `-`; `using Induk::atur;`; `void atur(const string &s)`; `getLabel()`).\n\n"
         "Baca n perintah berbentuk `angka <bilangan>` atau `teks <kata>`, dan panggil `atur` dengan argumen bertipe sesuai. Di akhir cetak nilai dan label.\n\n"
         "Contoh: `angka 5`, `teks merah`, `angka -2` → `-2 merah`." + W_ID,
         "Baris pertama: n. Lalu n baris perintah.", "Satu baris: nilai, lalu label.", "1 ≤ n ≤ 8; bilangan antara −99 dan 99; kata dari huruf kecil"),
     en=("Setting it Two Ways", "Name hiding applies to **all** base methods of the same name, whatever their parameters. If `Induk` has `atur(int)` and `Anak` adds `atur(const string &)`, then `anak.atur(5)` no longer finds the `int` version.\n\n"
         "The cure is one line inside `Anak`: `using Induk::atur;` — it brings all of the base's `atur` into the derived class, so that both become equal overloads.\n\n"
         "Make a class `Induk` (`protected` attribute `int nilai`, 0 at first; `void atur(int x)` (set); `getNilai()`) and a class `Anak` derived from it (attribute `string label`, `-` at first; `using Induk::atur;`; `void atur(const string &s)`; `getLabel()`).\n\n"
         "Read n commands of the form `angka <number>` or `teks <word>`, and call `atur` with an argument of the matching type. At the end print the value and the label.\n\n"
         "Example: `angka 5`, `teks merah`, `angka -2` → `-2 merah`." + W_EN,
         "First line: n. Then n command lines.", "One line: the value, then the label.", "1 ≤ n ≤ 8; numbers are between −99 and 99; words have lowercase letters"),
     ar=("الضبط بطريقتين", "حجب الاسم يسري على **كل** طرائق الأساس التي لها الاسم نفسه مهما كانت وسائطها. فإذا كان في `Induk` الطريقة `atur(int)` وأضاف `Anak` الطريقة `atur(const string &)` فإن `anak.atur(5)` لا يجد نسخة `int` بعدها.\n\n"
         "والعلاج سطر واحد داخل `Anak`: `using Induk::atur;` — يجلب كل طرائق `atur` الخاصة بالأساس إلى المشتق فتصير الاثنتان تحميلين زائدين متكافئين.\n\n"
         "أنشئ صنفًا `Induk` (الخاصية المحمية `int nilai` وتبدأ من 0؛ و `void atur(int x)` (اضبط)؛ و `getNilai()`) وصنفًا `Anak` مشتقًّا منه (الخاصية `string label` وتبدأ `-`؛ و `using Induk::atur;`؛ و `void atur(const string &s)`؛ و `getLabel()`).\n\n"
         "اقرأ n أمرًا على الصورة `angka <عدد>` أو `teks <كلمة>` واستدعِ `atur` بوسيط من النوع المناسب. في النهاية اطبع القيمة والتسمية.\n\n"
         "مثال: `angka 5` ثم `teks merah` ثم `angka -2` ← `-2 merah`." + W_AR,
         "السطر الأول: n. ثم n سطر أوامر.", "سطر واحد: القيمة ثم التسمية.", "1 ≤ n ≤ 8؛ الأعداد بين −99 و 99؛ الكلمات حروف صغيرة"))

soal("pegawai-tiga-jenis", "mudah", "trimmed",
     NAMA + "n = {2: 1}.get(i, r.randint(2, 8))\nprint(n)\nfor nama in r.sample(NAMA, n):\n    j = r.choice(['tetap', 'kontrak', 'magang'])\n    if j == 'tetap':\n        print(j, nama, r.randint(30, 100) * 100000, r.randint(0, 20) * 100000)\n    elif j == 'kontrak':\n        print(j, nama, r.randint(20, 180), r.randint(2, 10) * 10000)\n    else:\n        print(j, nama, r.randint(10, 40) * 200000)\n",
     program(r'''
class Pegawai {
protected:
    string nama;
    int pokok;

public:
    Pegawai(const string &n, int p) : nama(n), pokok(p) {
    }

    string getNama() const {
        return nama;
    }

    int gaji() const {
        return pokok;
    }
};

class Tetap : public Pegawai {
private:
    int tunjangan;

public:
    Tetap(const string &n, int p, int t) : Pegawai(n, p), tunjangan(t) {
    }

    int gaji() const {
        return Pegawai::gaji() + tunjangan;
    }
};

class Kontrak : public Pegawai {
private:
    int jam;

public:
    Kontrak(const string &n, int j, int tarif) : Pegawai(n, tarif), jam(j) {
    }

    int gaji() const {
        return Pegawai::gaji() * jam;
    }
};

class Magang : public Pegawai {
public:
    Magang(const string &n, int p) : Pegawai(n, p) {
    }

    int gaji() const {
        return Pegawai::gaji() / 2;
    }
};''', r'''    int n;
    cin >> n;
    auto total = 0;
    for (int k = 0; k < n; k++) {
        string jenis, nama;
        int a;
        cin >> jenis >> nama >> a;
        auto gaji = 0;
        if (jenis == "tetap") {
            int b;
            cin >> b;
            Tetap p(nama, a, b);
            gaji = p.gaji();
        } else if (jenis == "kontrak") {
            int b;
            cin >> b;
            Kontrak p(nama, a, b);
            gaji = p.gaji();
        } else {
            Magang p(nama, a);
            gaji = p.gaji();
        }
        cout << nama << ": " << gaji << endl;
        total += gaji;
    }
    cout << "Total: " << total << endl;''', "string"), wajib=W,
     id=("Tiga Jenis Pegawai", "Buat class `Pegawai` (atribut `protected` `string nama`, `int pokok`; `getNama()`; `int gaji() const` mengembalikan `pokok`) dan tiga turunannya. Masing-masing punya `gaji()` sendiri yang **dibangun dari `Pegawai::gaji()`**:\n\n"
         "- `Tetap` — atribut tambahan `int tunjangan`; gajinya `Pegawai::gaji()` + tunjangan;\n- `Kontrak` — dibayar per jam: `pokok` diisi **tarif per jam**, dan atribut tambahan `int jam`; gajinya `Pegawai::gaji()` × jam;\n- `Magang` — gajinya separuh `Pegawai::gaji()`.\n\n"
         "Masukan: n pegawai, satu per baris:\n\n- `tetap <nama> <pokok> <tunjangan>`\n- `kontrak <nama> <jam> <tarif per jam>`\n- `magang <nama> <pokok>`\n\n"
         "Cetak `<nama>: <gaji>` untuk tiap pegawai, lalu `Total: T`.\n\n"
         "Contoh: `tetap Andi 5000000 1000000`, `kontrak Budi 100 50000`, `magang Citra 3000000` →\n\n```\nAndi: 6000000\nBudi: 5000000\nCitra: 1500000\nTotal: 12500000\n```" + W_ID,
         "Baris pertama: n. Lalu n baris pegawai.", "Satu baris per pegawai, lalu `Total: T`.", "1 ≤ n ≤ 8; semua gaji bulat dan totalnya muat di `int`."),
     en=("Three Kinds of Employee", "Make a class `Pegawai` (employee; `protected` attributes `string nama`, `int pokok` (base pay); `getNama()`; `int gaji() const` (pay) returns `pokok`) and three derived classes. Each has its own `gaji()`, **built from `Pegawai::gaji()`**:\n\n"
         "- `Tetap` (permanent) — extra attribute `int tunjangan` (allowance); the pay is `Pegawai::gaji()` + allowance;\n- `Kontrak` (contract) — paid by the hour: `pokok` holds the **hourly rate**, and an extra attribute `int jam` (hours); the pay is `Pegawai::gaji()` × hours;\n- `Magang` (intern) — the pay is half of `Pegawai::gaji()`.\n\n"
         "Input: n employees, one per line:\n\n- `tetap <name> <base pay> <allowance>`\n- `kontrak <name> <hours> <hourly rate>`\n- `magang <name> <base pay>`\n\n"
         "Print `<name>: <pay>` for each employee, then `Total: T`.\n\n"
         "Example: `tetap Andi 5000000 1000000`, `kontrak Budi 100 50000`, `magang Citra 3000000` →\n\n```\nAndi: 6000000\nBudi: 5000000\nCitra: 1500000\nTotal: 12500000\n```" + W_EN,
         "First line: n. Then n employee lines.", "One line per employee, then `Total: T`.", "1 ≤ n ≤ 8; all pays are whole numbers and the total fits in `int`."),
     ar=("ثلاثة أنواع من الموظفين", "أنشئ صنفًا `Pegawai` (موظف؛ الخاصيتان المحميتان `string nama` و `int pokok` (الأجر الأساسي)؛ و `getNama()`؛ و `int gaji() const` (الراتب) تُرجع `pokok`) وثلاثة أصناف مشتقة. لكل منها `gaji()` خاصة **مبنية على `Pegawai::gaji()`**:\n\n"
         "- `Tetap` (دائم) — خاصية إضافية `int tunjangan` (البدل)؛ راتبه `Pegawai::gaji()` + البدل؛\n- `Kontrak` (متعاقد) — يُدفع له بالساعة: `pokok` فيه **أجر الساعة**، وخاصية إضافية `int jam` (الساعات)؛ راتبه `Pegawai::gaji()` × الساعات؛\n- `Magang` (متدرب) — راتبه نصف `Pegawai::gaji()`.\n\n"
         "المدخل: n موظفًا، واحد في كل سطر:\n\n- `tetap <الاسم> <الأساسي> <البدل>`\n- `kontrak <الاسم> <الساعات> <أجر الساعة>`\n- `magang <الاسم> <الأساسي>`\n\n"
         "اطبع `<الاسم>: <الراتب>` لكل موظف ثم `Total: T`.\n\n"
         "مثال: `tetap Andi 5000000 1000000` و `kontrak Budi 100 50000` و `magang Citra 3000000` ←\n\n```\nAndi: 6000000\nBudi: 5000000\nCitra: 1500000\nTotal: 12500000\n```" + W_AR,
         "السطر الأول: n. ثم n سطر موظفين.", "سطر لكل موظف ثم `Total: T`.", "1 ≤ n ≤ 8؛ كل الرواتب أعداد صحيحة ومجموعها يسعه `int`."))

soal("bangun-ruang", "mudah", "trimmed", "n = {2: 1}.get(i, r.randint(2, 8))\nprint(n)\nfor _ in range(n):\n    if r.random() < 0.5:\n        print('kubus', r.randint(1, 30))\n    else:\n        print('balok', r.randint(1, 30), r.randint(1, 30), r.randint(1, 30))\n",
     program(r'''
class Persegi {
protected:
    int sisi;

public:
    Persegi(int s) : sisi(s) {
    }

    int luas() const {
        return sisi * sisi;
    }
};

class Kubus : public Persegi {
public:
    Kubus(int s) : Persegi(s) {
    }

    int volume() const {
        return luas() * sisi;
    }
};

class PersegiPanjang {
protected:
    int panjang;
    int lebar;

public:
    PersegiPanjang(int p, int l) : panjang(p), lebar(l) {
    }

    int luas() const {
        return panjang * lebar;
    }
};

class Balok : public PersegiPanjang {
private:
    int tinggi;

public:
    Balok(int p, int l, int t) : PersegiPanjang(p, l), tinggi(t) {
    }

    int volume() const {
        return luas() * tinggi;
    }
};''', r'''    int n;
    cin >> n;
    auto total = 0;
    for (int k = 0; k < n; k++) {
        string jenis;
        cin >> jenis;
        auto v = 0;
        if (jenis == "kubus") {
            int s;
            cin >> s;
            Kubus kubus(s);
            v = kubus.volume();
        } else {
            int p, l, t;
            cin >> p >> l >> t;
            Balok balok(p, l, t);
            v = balok.volume();
        }
        cout << v << endl;
        total += v;
    }
    cout << "Total: " << total << endl;''', "string"), wajib=W,
     id=("Dari Bidang ke Ruang", "Sebuah kubus adalah persegi yang \"ditarik ke atas\"; sebuah balok adalah persegi panjang yang ditarik ke atas. Volume keduanya adalah **luas alas** dikali tinggi — dan luas alas sudah bisa dihitung oleh induknya.\n\n"
         "Buat class `Persegi` (atribut `protected` `int sisi`; `int luas() const`) dan turunannya `Kubus` dengan `int volume() const` = `luas()` × sisi.\n\n"
         "Buat class `PersegiPanjang` (atribut `protected` `int panjang`, `int lebar`; `int luas() const`) dan turunannya `Balok` dengan atribut `int tinggi` dan `int volume() const` = `luas()` × tinggi.\n\n"
         "Masukan: n bangun, satu per baris: `kubus <sisi>` atau `balok <panjang> <lebar> <tinggi>`. Cetak volume tiap bangun, lalu `Total: T`.\n\n"
         "Contoh: `kubus 3`, `balok 2 3 4` →\n\n```\n27\n24\nTotal: 51\n```" + W_ID,
         "Baris pertama: n. Lalu n baris bangun.", "Satu baris per bangun, lalu `Total: T`.", "1 ≤ n ≤ 8; tiap ukuran antara 1 dan 30"),
     en=("From Plane to Space", "A cube is a square \"pulled upwards\"; a box is a rectangle pulled upwards. The volume of both is the **base area** times the height — and the base area can already be computed by the base class.\n\n"
         "Make a class `Persegi` (square; `protected` attribute `int sisi`; `int luas() const`) and its derived class `Kubus` (cube) with `int volume() const` = `luas()` × side.\n\n"
         "Make a class `PersegiPanjang` (rectangle; `protected` attributes `int panjang`, `int lebar`; `int luas() const`) and its derived class `Balok` (box) with an attribute `int tinggi` (height) and `int volume() const` = `luas()` × height.\n\n"
         "Input: n solids, one per line: `kubus <side>` or `balok <length> <width> <height>`. Print the volume of each, then `Total: T`.\n\n"
         "Example: `kubus 3`, `balok 2 3 4` →\n\n```\n27\n24\nTotal: 51\n```" + W_EN,
         "First line: n. Then n lines of solids.", "One line per solid, then `Total: T`.", "1 ≤ n ≤ 8; each size is between 1 and 30"),
     ar=("من المستوى إلى الفراغ", "المكعب مربع «مسحوب إلى الأعلى»؛ ومتوازي المستطيلات مستطيل مسحوب إلى الأعلى. حجم كليهما **مساحة القاعدة** في الارتفاع — ومساحة القاعدة يحسبها الصنف الأساسي أصلًا.\n\n"
         "أنشئ صنفًا `Persegi` (مربع؛ الخاصية المحمية `int sisi`؛ و `int luas() const`) ومشتقه `Kubus` (مكعب) فيه `int volume() const` = `luas()` × الضلع.\n\n"
         "أنشئ صنفًا `PersegiPanjang` (مستطيل؛ الخاصيتان المحميتان `int panjang` و `int lebar`؛ و `int luas() const`) ومشتقه `Balok` (متوازي مستطيلات) فيه خاصية `int tinggi` (الارتفاع) و `int volume() const` = `luas()` × الارتفاع.\n\n"
         "المدخل: n مجسمًا، واحد في كل سطر: `kubus <الضلع>` أو `balok <الطول> <العرض> <الارتفاع>`. اطبع حجم كل مجسم ثم `Total: T`.\n\n"
         "مثال: `kubus 3` و `balok 2 3 4` ←\n\n```\n27\n24\nTotal: 51\n```" + W_AR,
         "السطر الأول: n. ثم n سطر مجسمات.", "سطر لكل مجسم ثم `Total: T`.", "1 ≤ n ≤ 8؛ كل قياس بين 1 و 30"))

# ============================== MUDAH-SEDANG (6) ==============================

soal("pewarisan-privat", "mudah_sedang", "trimmed",
     "for _ in range(r.randint(1, 20)):\n    q = r.random()\n    if q < 0.3:\n        print('antre', r.randint(1, 99))\n    elif q < 0.5:\n        print('layani')\n    elif q < 0.8:\n        print('tumpuk', r.randint(1, 99))\n    else:\n        print('angkat')\nprint('selesai')\n",
     program(r'''
class DaftarBilangan {
protected:
    int data[20];
    int banyak = 0;

public:
    void tambahBelakang(int x) {
        data[banyak] = x;
        banyak++;
    }

    int ambilDepan() {
        auto x = data[0];
        for (int k = 0; k < banyak - 1; k++) {
            data[k] = data[k + 1];
        }
        banyak--;
        return x;
    }

    int ambilBelakang() {
        banyak--;
        return data[banyak];
    }

    bool kosong() const {
        return banyak == 0;
    }

    int ukuran() const {
        return banyak;
    }
};

class Antrean : private DaftarBilangan {
public:
    using DaftarBilangan::kosong;
    using DaftarBilangan::ukuran;

    void masuk(int x) {
        tambahBelakang(x);
    }

    int keluar() {
        return ambilDepan();
    }
};

class Tumpukan : private DaftarBilangan {
public:
    using DaftarBilangan::kosong;
    using DaftarBilangan::ukuran;

    void dorong(int x) {
        tambahBelakang(x);
    }

    int ambil() {
        return ambilBelakang();
    }
};''', r'''    Antrean antrean;
    Tumpukan tumpukan;
    string perintah;
    while (cin >> perintah) {
        if (perintah == "selesai") {
            break;
        }
        if (perintah == "antre" || perintah == "tumpuk") {
            int x;
            cin >> x;
            if (perintah == "antre") {
                antrean.masuk(x);
            } else {
                tumpukan.dorong(x);
            }
        } else if (perintah == "layani") {
            if (antrean.kosong()) {
                cout << "antrean kosong" << endl;
            } else {
                cout << antrean.keluar() << endl;
            }
        } else if (tumpukan.kosong()) {
            cout << "tumpukan kosong" << endl;
        } else {
            cout << tumpukan.ambil() << endl;
        }
    }
    cout << "Antrean: " << antrean.ukuran() << endl;
    cout << "Tumpukan: " << tumpukan.ukuran() << endl;''', "string"), wajib=W,
     id=("Pewarisan Privat", "Pewarisan `public` berkata \"turunan *adalah* induk\". Pewarisan **`private`** berkata lain: \"turunan *dibuat dengan memakai* induk\" — semua anggota induk menjadi urusan dalam turunan, dan kode luar hanya melihat apa yang sengaja dibuka.\n\n"
         "Buat class `DaftarBilangan` yang serba bisa: atribut `protected` `int data[20]`, `int banyak`; method `tambahBelakang(int)`, `int ambilDepan()`, `int ambilBelakang()`, `bool kosong() const`, `int ukuran() const`.\n\n"
         "Dari class itu turunkan **secara privat** dua class yang masing-masing hanya membuka sebagian kemampuan:\n\n"
         "- `Antrean` — `void masuk(int)` (ke belakang) dan `int keluar()` (dari depan);\n- `Tumpukan` — `void dorong(int)` (ke belakang) dan `int ambil()` (dari belakang).\n\n"
         "Keduanya membuka `kosong` dan `ukuran` milik induk dengan `using DaftarBilangan::kosong;` dan seterusnya. Sebuah `Antrean` tidak punya jalan untuk diambil dari belakang.\n\n"
         "Program memakai satu antrean dan satu tumpukan. Perintah: `antre x`, `layani` (cetak elemen terdepan antrean, atau `antrean kosong`), `tumpuk x`, `angkat` (cetak elemen teratas tumpukan, atau `tumpukan kosong`), `selesai`. Di akhir cetak `Antrean: <ukuran>` dan `Tumpukan: <ukuran>`.\n\n"
         "Contoh: `antre 1`, `antre 2`, `tumpuk 1`, `tumpuk 2`, `layani`, `angkat`, `selesai` →\n\n```\n1\n2\nAntrean: 1\nTumpukan: 1\n```" + W_ID,
         "Beberapa baris perintah, diakhiri `selesai`.", "Satu baris untuk tiap `layani` dan `angkat`, lalu dua baris ukuran.", "Paling banyak 20 perintah; 1 ≤ x ≤ 99"),
     en=("Private Inheritance", "`public` inheritance says \"the derived class *is a* base\". **`private`** inheritance says something else: \"the derived class *is built using* the base\" — all base members become the derived class's own business, and outside code sees only what is deliberately opened.\n\n"
         "Make an all-round class `DaftarBilangan` (list of numbers): `protected` attributes `int data[20]`, `int banyak`; methods `tambahBelakang(int)` (add at the back), `int ambilDepan()` (take from the front), `int ambilBelakang()` (take from the back), `bool kosong() const`, `int ukuran() const`.\n\n"
         "From it derive **privately** two classes that each open only part of those abilities:\n\n"
         "- `Antrean` (queue) — `void masuk(int)` (at the back) and `int keluar()` (from the front);\n- `Tumpukan` (stack) — `void dorong(int)` (at the back) and `int ambil()` (from the back).\n\n"
         "Both open the base's `kosong` and `ukuran` with `using DaftarBilangan::kosong;` and so on. An `Antrean` offers no way to take from the back.\n\n"
         "The program uses one queue and one stack. Commands: `antre x`, `layani` (print the front of the queue, or `antrean kosong`), `tumpuk x`, `angkat` (print the top of the stack, or `tumpukan kosong`), `selesai`. At the end print `Antrean: <size>` and `Tumpukan: <size>`.\n\n"
         "Example: `antre 1`, `antre 2`, `tumpuk 1`, `tumpuk 2`, `layani`, `angkat`, `selesai` →\n\n```\n1\n2\nAntrean: 1\nTumpukan: 1\n```" + W_EN,
         "Several command lines, ending with `selesai`.", "One line for every `layani` and `angkat`, then two size lines.", "At most 20 commands; 1 ≤ x ≤ 99"),
     ar=("الوراثة الخاصة", "الوراثة `public` تقول «المشتق *هو* الأساس». أما الوراثة **`private`** فتقول شيئًا آخر: «المشتق *مبني باستعمال* الأساس» — كل أعضاء الأساس تصير شأنًا داخليًا للمشتق، ولا يرى الكود الخارجي إلا ما فُتح عمدًا.\n\n"
         "أنشئ صنفًا شاملًا `DaftarBilangan` (قائمة أعداد): الخاصيتان المحميتان `int data[20]` و `int banyak`؛ والطرائق `tambahBelakang(int)` (أضف في الخلف) و `int ambilDepan()` (خذ من الأمام) و `int ambilBelakang()` (خذ من الخلف) و `bool kosong() const` و `int ukuran() const`.\n\n"
         "اشتقّ منه **وراثة خاصة** صنفين يفتح كلٌّ منهما جزءًا فقط من تلك القدرات:\n\n"
         "- `Antrean` (طابور) — `void masuk(int)` (في الخلف) و `int keluar()` (من الأمام)؛\n- `Tumpukan` (مكدّس) — `void dorong(int)` (في الخلف) و `int ambil()` (من الخلف).\n\n"
         "يفتح كلاهما `kosong` و `ukuran` الخاصتين بالأساس بـ `using DaftarBilangan::kosong;` وهكذا. و `Antrean` لا يتيح طريقًا للأخذ من الخلف.\n\n"
         "يستعمل البرنامج طابورًا واحدًا ومكدّسًا واحدًا. الأوامر: `antre x` و `layani` (اطبع مقدمة الطابور أو `antrean kosong`) و `tumpuk x` و `angkat` (اطبع قمة المكدّس أو `tumpukan kosong`) و `selesai`. في النهاية اطبع `Antrean: <الحجم>` و `Tumpukan: <الحجم>`.\n\n"
         "مثال: `antre 1` ثم `antre 2` ثم `tumpuk 1` ثم `tumpuk 2` ثم `layani` ثم `angkat` ثم `selesai` ←\n\n```\n1\n2\nAntrean: 1\nTumpukan: 1\n```" + W_AR,
         "عدة أسطر أوامر تنتهي بـ `selesai`.", "سطر لكل `layani` و `angkat` ثم سطرا الحجم.", "20 أمرًا على الأكثر؛ 1 ≤ x ≤ 99"))

soal("mesin-serbaguna", "mudah_sedang", "trimmed",
     "print(r.choice(['Kantor', 'Lab', 'Aula', 'Loket']), r.randint(0, 60))\nfor _ in range(r.randint(1, 12)):\n    print(r.choice(['cetak', 'pindai', 'salin']), r.randint(1, 25))\nprint('selesai')\n",
     program(r'''
class Printer {
protected:
    int kertas;

public:
    Printer(int k) : kertas(k) {
        cout << "+Printer" << endl;
    }

    ~Printer() {
        cout << "-Printer" << endl;
    }

    bool cetak(int halaman) {
        if (halaman > kertas) {
            cout << "Kertas kurang" << endl;
            return false;
        }
        kertas -= halaman;
        cout << "Mencetak " << halaman << " halaman" << endl;
        return true;
    }
};

class Pemindai {
protected:
    int dipindai = 0;

public:
    Pemindai() {
        cout << "+Pemindai" << endl;
    }

    ~Pemindai() {
        cout << "-Pemindai" << endl;
    }

    void pindai(int halaman) {
        dipindai += halaman;
        cout << "Memindai " << halaman << " halaman" << endl;
    }
};

class MesinSerbaguna : public Printer, public Pemindai {
private:
    string nama;

public:
    MesinSerbaguna(const string &n, int k) : Printer(k), nama(n) {
        cout << "+MesinSerbaguna " << nama << endl;
    }

    ~MesinSerbaguna() {
        cout << "-MesinSerbaguna " << nama << endl;
    }

    void salin(int halaman) {
        pindai(halaman);
        cetak(halaman);
    }

    void laporan() const {
        cout << "Kertas: " << kertas << ", dipindai: " << dipindai << endl;
    }
};''', r'''    string nama;
    int kertas;
    cin >> nama >> kertas;
    MesinSerbaguna mesin(nama, kertas);
    string perintah;
    while (cin >> perintah) {
        if (perintah == "selesai") {
            break;
        }
        int halaman;
        cin >> halaman;
        if (perintah == "cetak") {
            mesin.cetak(halaman);
        } else if (perintah == "pindai") {
            mesin.pindai(halaman);
        } else {
            mesin.salin(halaman);
        }
    }
    mesin.laporan();''', "string"), wajib=W,
     id=("Mesin Serbaguna", "Sebuah class boleh mewarisi **lebih dari satu induk**: `class MesinSerbaguna : public Printer, public Pemindai`. Bagian-bagian induk dibangun **menurut urutan penulisannya** di daftar itu.\n\n"
         "Buat class `Printer` (atribut `protected` `int kertas`; konstruktor `Printer(int k)` mencetak `+Printer`, destruktor `-Printer`; `bool cetak(int halaman)` — bila kertas cukup, kurangi kertas dan cetak `Mencetak <h> halaman`; bila tidak, cetak `Kertas kurang`).\n\n"
         "Buat class `Pemindai` (atribut `protected` `int dipindai`; konstruktor mencetak `+Pemindai`, destruktor `-Pemindai`; `void pindai(int halaman)` menambah hitungan dan mencetak `Memindai <h> halaman`).\n\n"
         "Buat class `MesinSerbaguna` yang mewarisi keduanya (`+MesinSerbaguna <nama>` / `-MesinSerbaguna <nama>`), dengan method `void salin(int halaman)` — memindai, **lalu** mencetak — dan `void laporan() const` yang mencetak `Kertas: <k>, dipindai: <d>`.\n\n"
         "Masukan: nama dan banyak kertas; lalu perintah `cetak h`, `pindai h`, `salin h`, diakhiri `selesai`; lalu `laporan()`.\n\n"
         "Contoh: `Lab 5`; `salin 3`, `cetak 4`, `selesai` →\n\n```\n+Printer\n+Pemindai\n+MesinSerbaguna Lab\nMemindai 3 halaman\nMencetak 3 halaman\nKertas kurang\nKertas: 2, dipindai: 3\n-MesinSerbaguna Lab\n-Pemindai\n-Printer\n```" + W_ID,
         "Baris pertama: nama mesin dan banyak kertas. Lalu perintah-perintah, diakhiri `selesai`.", "Baris-baris seperti contoh.", "0 ≤ kertas ≤ 60; paling banyak 12 perintah; 1 ≤ halaman ≤ 25"),
     en=("All-in-One Machine", "A class may inherit from **more than one base**: `class MesinSerbaguna : public Printer, public Pemindai`. The base parts are built **in the order they are written** in that list.\n\n"
         "Make a class `Printer` (`protected` attribute `int kertas` (paper); the constructor `Printer(int k)` prints `+Printer`, the destructor `-Printer`; `bool cetak(int halaman)` (print) — if there is enough paper, use it and print `Mencetak <h> halaman`; if not, print `Kertas kurang` (not enough paper)).\n\n"
         "Make a class `Pemindai` (scanner; `protected` attribute `int dipindai` (pages scanned); the constructor prints `+Pemindai`, the destructor `-Pemindai`; `void pindai(int halaman)` (scan) raises the count and prints `Memindai <h> halaman`).\n\n"
         "Make a class `MesinSerbaguna` that inherits both (`+MesinSerbaguna <name>` / `-MesinSerbaguna <name>`), with a method `void salin(int halaman)` (copy) — scan, **then** print — and `void laporan() const` that prints `Kertas: <k>, dipindai: <d>`.\n\n"
         "Input: the name and the amount of paper; then commands `cetak h`, `pindai h`, `salin h`, ending with `selesai`; then `laporan()`.\n\n"
         "Example: `Lab 5`; `salin 3`, `cetak 4`, `selesai` →\n\n```\n+Printer\n+Pemindai\n+MesinSerbaguna Lab\nMemindai 3 halaman\nMencetak 3 halaman\nKertas kurang\nKertas: 2, dipindai: 3\n-MesinSerbaguna Lab\n-Pemindai\n-Printer\n```" + W_EN,
         "First line: the machine's name and the amount of paper. Then the commands, ending with `selesai`.", "Lines as in the example.", "0 ≤ paper ≤ 60; at most 12 commands; 1 ≤ pages ≤ 25"),
     ar=("الآلة المتعددة الوظائف", "يجوز أن يرث الصنف من **أكثر من أساس**: `class MesinSerbaguna : public Printer, public Pemindai`. وتُبنى أجزاء الأساس **بترتيب كتابتها** في تلك القائمة.\n\n"
         "أنشئ صنفًا `Printer` (طابعة؛ الخاصية المحمية `int kertas` (الورق)؛ الباني `Printer(int k)` يطبع `+Printer` والهادم `-Printer`؛ و `bool cetak(int halaman)` (اطبع) — إذا كفى الورق فاستعمله واطبع `Mencetak <h> halaman`؛ وإلا فاطبع `Kertas kurang` (الورق لا يكفي)).\n\n"
         "أنشئ صنفًا `Pemindai` (ماسح؛ الخاصية المحمية `int dipindai` (الممسوح)؛ الباني يطبع `+Pemindai` والهادم `-Pemindai`؛ و `void pindai(int halaman)` (امسح) تزيد العدّ وتطبع `Memindai <h> halaman`).\n\n"
         "أنشئ صنفًا `MesinSerbaguna` يرث كليهما (`+MesinSerbaguna <الاسم>` / `-MesinSerbaguna <الاسم>`)، وله طريقة `void salin(int halaman)` (انسخ) — تمسح **ثم** تطبع — و `void laporan() const` تطبع `Kertas: <k>, dipindai: <d>`.\n\n"
         "المدخل: الاسم وكمية الورق؛ ثم أوامر `cetak h` و `pindai h` و `salin h` تنتهي بـ `selesai`؛ ثم `laporan()`.\n\n"
         "مثال: `Lab 5`؛ `salin 3` ثم `cetak 4` ثم `selesai` ←\n\n```\n+Printer\n+Pemindai\n+MesinSerbaguna Lab\nMemindai 3 halaman\nMencetak 3 halaman\nKertas kurang\nKertas: 2, dipindai: 3\n-MesinSerbaguna Lab\n-Pemindai\n-Printer\n```" + W_AR,
         "السطر الأول: اسم الآلة وكمية الورق. ثم الأوامر منتهيةً بـ `selesai`.", "أسطر كما في المثال.", "0 ≤ الورق ≤ 60؛ 12 أمرًا على الأكثر؛ 1 ≤ الصفحات ≤ 25"))

soal("kendaraan-amfibi", "mudah_sedang", "trimmed", "print(r.choice(['Hiu', 'Buaya', 'Katak', 'Penyu', 'Berang', 'Kepiting']), r.randint(20, 120), r.randint(5, 40))\n",
     program(r'''
class Kendaraan {
protected:
    string nama;

public:
    Kendaraan(const string &n) : nama(n) {
        cout << "+Kendaraan " << nama << endl;
    }

    ~Kendaraan() {
        cout << "-Kendaraan " << nama << endl;
    }
};

class Darat : virtual public Kendaraan {
protected:
    int kecepatanDarat;

public:
    Darat(const string &n, int k) : Kendaraan(n), kecepatanDarat(k) {
        cout << "+Darat" << endl;
    }

    ~Darat() {
        cout << "-Darat" << endl;
    }

    void melaju() const {
        cout << nama << " melaju " << kecepatanDarat << " km/jam" << endl;
    }
};

class Air : virtual public Kendaraan {
protected:
    int kecepatanAir;

public:
    Air(const string &n, int k) : Kendaraan(n), kecepatanAir(k) {
        cout << "+Air" << endl;
    }

    ~Air() {
        cout << "-Air" << endl;
    }

    void berlayar() const {
        cout << nama << " berlayar " << kecepatanAir << " knot" << endl;
    }
};

class Amfibi : public Darat, public Air {
public:
    Amfibi(const string &n, int d, int a) : Kendaraan(n), Darat(n, d), Air(n, a) {
        cout << "+Amfibi" << endl;
    }

    ~Amfibi() {
        cout << "-Amfibi" << endl;
    }
};''', r'''    string nama;
    int darat, air;
    cin >> nama >> darat >> air;
    Amfibi amfibi(nama, darat, air);
    amfibi.melaju();
    amfibi.berlayar();''', "string"), wajib=W,
     id=("Kendaraan Amfibi", "Kendaraan amfibi adalah kendaraan darat **dan** kendaraan air. Keduanya turunan `Kendaraan` — sehingga tanpa penanganan khusus, sebuah `Amfibi` akan memuat **dua** bagian `Kendaraan` (masalah berlian) dan punya dua nama.\n\n"
         "Obatnya **pewarisan virtual**: `class Darat : virtual public Kendaraan` dan `class Air : virtual public Kendaraan`. Bagian `Kendaraan` lalu hanya ada satu, dan dibangun **paling awal** — oleh konstruktor class paling bawah, yang harus menyebutnya sendiri:\n\n```\nAmfibi(const string &n, int d, int a) : Kendaraan(n), Darat(n, d), Air(n, a) { … }\n```\n\n"
         "Buat keempat class. Konstruktor tiap class mencetak `+Kendaraan <nama>`, `+Darat`, `+Air`, `+Amfibi`; destruktornya `-…` yang sepadan. `Darat` punya `void melaju() const` → `<nama> melaju <k> km/jam`; `Air` punya `void berlayar() const` → `<nama> berlayar <k> knot`.\n\n"
         "Baca nama, kecepatan darat, dan kecepatan air. Buat satu `Amfibi`, panggil `melaju()` lalu `berlayar()`.\n\n"
         "Untuk `Hiu 80 12`:\n\n```\n+Kendaraan Hiu\n+Darat\n+Air\n+Amfibi\nHiu melaju 80 km/jam\nHiu berlayar 12 knot\n-Amfibi\n-Air\n-Darat\n-Kendaraan Hiu\n```" + W_ID,
         "Satu baris berisi nama, kecepatan darat, dan kecepatan air.", "Sepuluh baris seperti contoh.", "Nama satu kata; kecepatan darat 20–120; kecepatan air 5–40"),
     en=("Amphibious Vehicle", "An amphibious vehicle is a land vehicle **and** a water vehicle. Both derive from `Kendaraan` — so without special care an `Amfibi` would contain **two** `Kendaraan` parts (the diamond problem) and have two names.\n\n"
         "The cure is **virtual inheritance**: `class Darat : virtual public Kendaraan` and `class Air : virtual public Kendaraan`. There is then only one `Kendaraan` part, and it is built **first of all** — by the constructor of the bottom-most class, which has to name it itself:\n\n```\nAmfibi(const string &n, int d, int a) : Kendaraan(n), Darat(n, d), Air(n, a) { … }\n```\n\n"
         "Make the four classes. The constructor of each prints `+Kendaraan <name>`, `+Darat`, `+Air`, `+Amfibi`; the destructors print the matching `-…`. `Darat` (land) has `void melaju() const` → `<name> melaju <k> km/jam`; `Air` (water) has `void berlayar() const` → `<name> berlayar <k> knot`.\n\n"
         "Read the name, the land speed and the water speed. Create one `Amfibi`, call `melaju()` and then `berlayar()`.\n\n"
         "For `Hiu 80 12`:\n\n```\n+Kendaraan Hiu\n+Darat\n+Air\n+Amfibi\nHiu melaju 80 km/jam\nHiu berlayar 12 knot\n-Amfibi\n-Air\n-Darat\n-Kendaraan Hiu\n```" + W_EN,
         "One line with the name, the land speed and the water speed.", "Ten lines as in the example.", "The name is one word; land speed 20–120; water speed 5–40"),
     ar=("المركبة البرمائية", "المركبة البرمائية مركبة برية **و**مركبة مائية. وكلتاهما مشتقة من `Kendaraan` — فبلا معالجة خاصة يحوي `Amfibi` **جزأين** من `Kendaraan` (مشكلة المعيَّن) ويكون له اسمان.\n\n"
         "والعلاج **الوراثة الافتراضية**: `class Darat : virtual public Kendaraan` و `class Air : virtual public Kendaraan`. عندئذ يوجد جزء `Kendaraan` واحد فقط، ويُبنى **أولًا** — يبنيه باني الصنف الأدنى الذي عليه أن يذكره بنفسه:\n\n```\nAmfibi(const string &n, int d, int a) : Kendaraan(n), Darat(n, d), Air(n, a) { … }\n```\n\n"
         "أنشئ الأصناف الأربعة. باني كل صنف يطبع `+Kendaraan <الاسم>` و `+Darat` و `+Air` و `+Amfibi`؛ والهادمات تطبع `-…` المقابلة. في `Darat` (بري) الطريقة `void melaju() const` ← `<الاسم> melaju <k> km/jam`؛ وفي `Air` (مائي) الطريقة `void berlayar() const` ← `<الاسم> berlayar <k> knot`.\n\n"
         "اقرأ الاسم والسرعة البرية والسرعة المائية. أنشئ `Amfibi` واحدة واستدعِ `melaju()` ثم `berlayar()`.\n\n"
         "عند `Hiu 80 12`:\n\n```\n+Kendaraan Hiu\n+Darat\n+Air\n+Amfibi\nHiu melaju 80 km/jam\nHiu berlayar 12 knot\n-Amfibi\n-Air\n-Darat\n-Kendaraan Hiu\n```" + W_AR,
         "سطر واحد فيه الاسم والسرعة البرية والسرعة المائية.", "عشرة أسطر كما في المثال.", "الاسم كلمة واحدة؛ السرعة البرية 20–120؛ والمائية 5–40"))

UNIT3 = r'''
class Unit {
protected:
    int personel;

public:
    Unit(int p) : personel(p) {
    }

    int kekuatan() const {
        return personel;
    }
};

class UnitDarat : public Unit {
protected:
    int kendaraan;

public:
    UnitDarat(int p, int k) : Unit(p), kendaraan(k) {
    }

    int kekuatan() const {
        return Unit::kekuatan() + kendaraan * 5;
    }
};

class Tank : public UnitDarat {
private:
    int lapisBaja;

public:
    Tank(int p, int k, int b) : UnitDarat(p, k), lapisBaja(b) {
    }

    int kekuatan() const {
        return UnitDarat::kekuatan() + lapisBaja * 10;
    }
};'''

soal("kekuatan-berjenjang", "mudah_sedang", "trimmed",
     "n = {2: 1}.get(i, r.randint(2, 8))\nprint(n)\nfor _ in range(n):\n    j = r.choice(['unit', 'darat', 'tank'])\n    v = [r.randint(5, 200)]\n    if j != 'unit':\n        v.append(r.randint(1, 20))\n    if j == 'tank':\n        v.append(r.randint(1, 30))\n    print(j, *v)\n",
     program(UNIT3, r'''    int n;
    cin >> n;
    auto total = 0;
    for (int k = 0; k < n; k++) {
        string jenis;
        int p;
        cin >> jenis >> p;
        auto kuat = 0;
        if (jenis == "unit") {
            Unit u(p);
            kuat = u.kekuatan();
        } else if (jenis == "darat") {
            int kendaraan;
            cin >> kendaraan;
            UnitDarat u(p, kendaraan);
            kuat = u.kekuatan();
        } else {
            int kendaraan, baja;
            cin >> kendaraan >> baja;
            Tank u(p, kendaraan, baja);
            kuat = u.kekuatan();
        }
        cout << kuat << endl;
        total += kuat;
    }
    cout << "Total: " << total << endl;''', "string"), wajib=W,
     id=("Kekuatan Berjenjang", "Dalam hierarki bertingkat, tiap tingkat cukup **menambahkan bagiannya sendiri** di atas hasil tingkat di atasnya.\n\n"
         "Buat tiga class, masing-masing dengan `int kekuatan() const`:\n\n- `Unit` (atribut `int personel`) — kekuatannya `personel`;\n- `UnitDarat` turunan `Unit` (tambahan `int kendaraan`) — `Unit::kekuatan()` + kendaraan × 5;\n- `Tank` turunan `UnitDarat` (tambahan `int lapisBaja`) — `UnitDarat::kekuatan()` + lapisBaja × 10.\n\n"
         "`Tank` tidak perlu tahu bagaimana `Unit` menghitung; ia hanya memanggil induk langsungnya.\n\n"
         "Masukan: n satuan, satu per baris: `unit <personel>`, `darat <personel> <kendaraan>`, atau `tank <personel> <kendaraan> <lapisBaja>`. Cetak kekuatan tiap satuan, lalu `Total: T`.\n\n"
         "Contoh: `unit 40`, `darat 30 4`, `tank 4 1 12` →\n\n```\n40\n50\n129\nTotal: 219\n```" + W_ID,
         "Baris pertama: n. Lalu n baris satuan.", "Satu baris per satuan, lalu `Total: T`.", "1 ≤ n ≤ 8; personel 5–200; kendaraan 1–20; lapis baja 1–30"),
     en=("Strength Level by Level", "In a hierarchy of several levels, each level only has to **add its own part** on top of the result of the level above.\n\n"
         "Make three classes, each with `int kekuatan() const` (strength):\n\n- `Unit` (attribute `int personel`) — its strength is `personel`;\n- `UnitDarat` derived from `Unit` (extra `int kendaraan` (vehicles)) — `Unit::kekuatan()` + vehicles × 5;\n- `Tank` derived from `UnitDarat` (extra `int lapisBaja` (armour)) — `UnitDarat::kekuatan()` + armour × 10.\n\n"
         "`Tank` need not know how `Unit` computes; it only calls its direct base.\n\n"
         "Input: n units, one per line: `unit <personnel>`, `darat <personnel> <vehicles>`, or `tank <personnel> <vehicles> <armour>`. Print the strength of each unit, then `Total: T`.\n\n"
         "Example: `unit 40`, `darat 30 4`, `tank 4 1 12` →\n\n```\n40\n50\n129\nTotal: 219\n```" + W_EN,
         "First line: n. Then n unit lines.", "One line per unit, then `Total: T`.", "1 ≤ n ≤ 8; personnel 5–200; vehicles 1–20; armour 1–30"),
     ar=("القوة مستوًى فمستوًى", "في تسلسل هرمي متعدد المستويات يكفي كل مستوى أن **يضيف جزءه** فوق ناتج المستوى الذي فوقه.\n\n"
         "أنشئ ثلاثة أصناف لكل منها `int kekuatan() const` (القوة):\n\n- `Unit` (الخاصية `int personel`) — قوتها `personel`؛\n- `UnitDarat` المشتق من `Unit` (إضافة `int kendaraan` (المركبات)) — `Unit::kekuatan()` + المركبات × 5؛\n- `Tank` المشتق من `UnitDarat` (إضافة `int lapisBaja` (الدرع)) — `UnitDarat::kekuatan()` + الدرع × 10.\n\n"
         "لا يلزم `Tank` أن يعرف كيف يحسب `Unit`؛ فهو يستدعي أساسه المباشر فقط.\n\n"
         "المدخل: n وحدة، واحدة في كل سطر: `unit <الأفراد>` أو `darat <الأفراد> <المركبات>` أو `tank <الأفراد> <المركبات> <الدرع>`. اطبع قوة كل وحدة ثم `Total: T`.\n\n"
         "مثال: `unit 40` و `darat 30 4` و `tank 4 1 12` ←\n\n```\n40\n50\n129\nTotal: 219\n```" + W_AR,
         "السطر الأول: n. ثم n سطر وحدات.", "سطر لكل وحدة ثم `Total: T`.", "1 ≤ n ≤ 8؛ الأفراد 5–200؛ المركبات 1–20؛ الدرع 1–30"))

soal("hitung-per-tingkat", "mudah_sedang", "trimmed", "n = {2: 1}.get(i, r.randint(2, 15))\nprint(n)\nprint(*[r.choice(['unit', 'darat', 'tank', 'infanteri']) for _ in range(n)])\n",
     program(r'''
class Unit {
public:
    static int banyak;

    Unit() {
        banyak++;
    }
};

class UnitDarat : public Unit {
public:
    static int banyak;

    UnitDarat() {
        banyak++;
    }
};

class Tank : public UnitDarat {
public:
    static int banyak;

    Tank() {
        banyak++;
    }
};

class Infanteri : public UnitDarat {
public:
    static int banyak;

    Infanteri() {
        banyak++;
    }
};

int Unit::banyak = 0;
int UnitDarat::banyak = 0;
int Tank::banyak = 0;
int Infanteri::banyak = 0;''', r'''    int n;
    cin >> n;
    for (int k = 0; k < n; k++) {
        string jenis;
        cin >> jenis;
        if (jenis == "unit") {
            Unit u;
        } else if (jenis == "darat") {
            UnitDarat u;
        } else if (jenis == "tank") {
            Tank u;
        } else {
            Infanteri u;
        }
    }
    cout << "Unit: " << Unit::banyak << endl;
    cout << "UnitDarat: " << UnitDarat::banyak << endl;
    cout << "Tank: " << Tank::banyak << endl;
    cout << "Infanteri: " << Infanteri::banyak << endl;''', "string"), wajib=W,
     id=("Hitung per Tingkat", "Sebuah `Tank` adalah `UnitDarat`, dan `UnitDarat` adalah `Unit`. Jadi setiap kali sebuah tank dibuat, **tiga** konstruktor berjalan — dan penghitung di tiap tingkat ikut naik.\n\n"
         "Buat hierarki: `Unit`; `UnitDarat` turunan `Unit`; `Tank` dan `Infanteri`, keduanya turunan `UnitDarat`. Tiap class punya atribut `static int banyak` **miliknya sendiri**, yang dinaikkan oleh konstruktor class itu.\n\n"
         "(Atribut `banyak` di turunan menyembunyikan `banyak` milik induk; dari luar keduanya dibedakan dengan nama class: `Unit::banyak`, `Tank::banyak`.)\n\n"
         "Baca n jenis satuan (`unit`, `darat`, `tank`, `infanteri`) dan buat satu objek untuk tiap jenis. Lalu cetak keempat penghitung.\n\n"
         "Contoh: `tank infanteri unit tank` →\n\n```\nUnit: 4\nUnitDarat: 3\nTank: 2\nInfanteri: 1\n```" + W_ID,
         "Baris pertama: n. Baris kedua: n kata jenis satuan.", "Empat baris seperti contoh.", "1 ≤ n ≤ 15"),
     en=("Counting per Level", "A `Tank` is a `UnitDarat`, and a `UnitDarat` is a `Unit`. So every time a tank is created **three** constructors run — and the counter at each level goes up.\n\n"
         "Make the hierarchy: `Unit`; `UnitDarat` derived from `Unit`; `Tank` and `Infanteri`, both derived from `UnitDarat`. Each class has an attribute `static int banyak` (count) **of its own**, raised by that class's constructor.\n\n"
         "(The `banyak` of a derived class hides the base's `banyak`; from outside they are told apart by the class name: `Unit::banyak`, `Tank::banyak`.)\n\n"
         "Read n kinds of unit (`unit`, `darat`, `tank`, `infanteri`) and create one object for each. Then print the four counters.\n\n"
         "Example: `tank infanteri unit tank` →\n\n```\nUnit: 4\nUnitDarat: 3\nTank: 2\nInfanteri: 1\n```" + W_EN,
         "First line: n. Second line: n words, the kinds of unit.", "Four lines as in the example.", "1 ≤ n ≤ 15"),
     ar=("العدّ في كل مستوى", "`Tank` هي `UnitDarat`، و `UnitDarat` هي `Unit`. فكلما أُنشئت دبابة عملت **ثلاثة** بانيات — وارتفع العدّاد في كل مستوى.\n\n"
         "أنشئ التسلسل: `Unit`؛ و `UnitDarat` المشتق من `Unit`؛ و `Tank` و `Infanteri` المشتقان كلاهما من `UnitDarat`. لكل صنف خاصية `static int banyak` (العدد) **خاصة به** يزيدها باني ذلك الصنف.\n\n"
         "(الخاصية `banyak` في المشتق تحجب `banyak` الأساس؛ ومن الخارج يُفرَّق بينهما باسم الصنف: `Unit::banyak` و `Tank::banyak`.)\n\n"
         "اقرأ n نوع وحدة (`unit` و `darat` و `tank` و `infanteri`) وأنشئ كائنًا لكل نوع. ثم اطبع العدّادات الأربعة.\n\n"
         "مثال: `tank infanteri unit tank` ←\n\n```\nUnit: 4\nUnitDarat: 3\nTank: 2\nInfanteri: 1\n```" + W_AR,
         "السطر الأول: n. السطر الثاني: n كلمة هي أنواع الوحدات.", "أربعة أسطر كما في المثال.", "1 ≤ n ≤ 15"))

soal("matriks-persegi", "mudah_sedang", "trimmed",
     "n = {2: 1}.get(i, r.randint(2, 5))\ng = [[r.randint(-9, 9) for _ in range(n)] for _ in range(n)]\nif i % 2 == 0:\n    for a in range(n):\n        for b in range(a):\n            g[a][b] = g[b][a]\nprint(n)\nfor baris in g:\n    print(*baris)\n",
     program(r'''
class Matriks {
protected:
    int sel[5][5];
    int baris;
    int kolom;

public:
    Matriks(int b, int k) : baris(b), kolom(k) {
    }

    void baca() {
        for (int i = 0; i < baris; i++) {
            for (int j = 0; j < kolom; j++) {
                cin >> sel[i][j];
            }
        }
    }

    int jumlah() const {
        auto total = 0;
        for (int i = 0; i < baris; i++) {
            for (int j = 0; j < kolom; j++) {
                total += sel[i][j];
            }
        }
        return total;
    }
};

class MatriksPersegi : public Matriks {
public:
    MatriksPersegi(int n) : Matriks(n, n) {
    }

    int jejak() const {
        auto total = 0;
        for (int i = 0; i < baris; i++) {
            total += sel[i][i];
        }
        return total;
    }

    bool simetris() const {
        for (int i = 0; i < baris; i++) {
            for (int j = 0; j < i; j++) {
                if (sel[i][j] != sel[j][i]) {
                    return false;
                }
            }
        }
        return true;
    }
};''', r'''    int n;
    cin >> n;
    MatriksPersegi m(n);
    m.baca();
    cout << "Jumlah: " << m.jumlah() << endl;
    cout << "Jejak: " << m.jejak() << endl;
    if (m.simetris()) {
        cout << "simetris" << endl;
    } else {
        cout << "tidak simetris" << endl;
    }'''), wajib=W,
     id=("Matriks Persegi", "Matriks persegi adalah matriks yang banyak baris dan kolomnya sama. Ia bisa melakukan semua yang bisa dilakukan matriks biasa, **ditambah** beberapa hal yang hanya masuk akal untuk matriks persegi.\n\n"
         "Buat class `Matriks` (atribut `protected` `int sel[5][5]`, `int baris`, `int kolom`; konstruktor `Matriks(int b, int k)`; `void baca()`; `int jumlah() const` — jumlah semua elemen).\n\n"
         "Buat class `MatriksPersegi` turunan `Matriks`, dengan konstruktor `MatriksPersegi(int n) : Matriks(n, n) { }` dan dua method tambahan:\n\n"
         "- `int jejak() const` — jumlah elemen diagonal utama;\n- `bool simetris() const` — benar bila elemen baris i kolom j selalu sama dengan elemen baris j kolom i.\n\n"
         "Baca n dan matriks n × n. Cetak `Jumlah: …`, `Jejak: …`, lalu `simetris` atau `tidak simetris`.\n\n"
         "Contoh: matriks `1 2` / `2 5` →\n\n```\nJumlah: 10\nJejak: 6\nsimetris\n```" + W_ID,
         "Baris pertama: n. Lalu n baris berisi n bilangan bulat.", "Tiga baris seperti contoh.", "1 ≤ n ≤ 5; tiap elemen antara −9 dan 9"),
     en=("Square Matrix", "A square matrix is a matrix with as many rows as columns. It can do everything an ordinary matrix can, **plus** a few things that only make sense for square matrices.\n\n"
         "Make a class `Matriks` (`protected` attributes `int sel[5][5]`, `int baris`, `int kolom`; a constructor `Matriks(int b, int k)`; `void baca()` (read); `int jumlah() const` — the sum of all elements).\n\n"
         "Make a class `MatriksPersegi` derived from `Matriks`, with the constructor `MatriksPersegi(int n) : Matriks(n, n) { }` and two extra methods:\n\n"
         "- `int jejak() const` (trace) — the sum of the main diagonal;\n- `bool simetris() const` — true when the element in row i, column j always equals the one in row j, column i.\n\n"
         "Read n and an n × n matrix. Print `Jumlah: …`, `Jejak: …`, then `simetris` or `tidak simetris` (not symmetric).\n\n"
         "Example: the matrix `1 2` / `2 5` →\n\n```\nJumlah: 10\nJejak: 6\nsimetris\n```" + W_EN,
         "First line: n. Then n lines of n integers.", "Three lines as in the example.", "1 ≤ n ≤ 5; each element is between −9 and 9"),
     ar=("المصفوفة المربعة", "المصفوفة المربعة مصفوفة يتساوى عدد صفوفها وأعمدتها. تستطيع كل ما تستطيعه المصفوفة العادية، **وتزيد** أمورًا لا معنى لها إلا في المصفوفات المربعة.\n\n"
         "أنشئ صنفًا `Matriks` (الخصائص المحمية `int sel[5][5]` و `int baris` و `int kolom`؛ وبانٍ `Matriks(int b, int k)`؛ و `void baca()` (اقرأ)؛ و `int jumlah() const` — مجموع كل العناصر).\n\n"
         "أنشئ صنفًا `MatriksPersegi` مشتقًّا من `Matriks`، بانيه `MatriksPersegi(int n) : Matriks(n, n) { }` وله طريقتان إضافيتان:\n\n"
         "- `int jejak() const` (الأثر) — مجموع عناصر القطر الرئيسي؛\n- `bool simetris() const` — صحيحة إذا ساوى العنصر في الصف i والعمود j نظيره في الصف j والعمود i دائمًا.\n\n"
         "اقرأ n ومصفوفة n × n. اطبع `Jumlah: …` و `Jejak: …` ثم `simetris` أو `tidak simetris` (غير متماثلة).\n\n"
         "مثال: المصفوفة `1 2` / `2 5` ←\n\n```\nJumlah: 10\nJejak: 6\nsimetris\n```" + W_AR,
         "السطر الأول: n. ثم n سطرًا في كل منها n عددًا صحيحًا.", "ثلاثة أسطر كما في المثال.", "1 ≤ n ≤ 5؛ كل عنصر بين −9 و 9"))

# ================================ SEDANG (6) ================================

soal("urutan-lengkap", "sedang", "trimmed", "print(r.choice(['Avanza', 'Kijang', 'Ertiga', 'Jazz']), r.choice(['B1234XY', 'D5678AB', 'F9012CD', 'L3456EF']), r.randint(60, 300), r.randint(88, 107))\n",
     program(r'''
class Kendaraan {
public:
    Kendaraan() {
        cout << "+Kendaraan" << endl;
    }

    ~Kendaraan() {
        cout << "-Kendaraan" << endl;
    }
};

class Terdaftar {
protected:
    string nomor;

public:
    Terdaftar(const string &n) : nomor(n) {
        cout << "+Terdaftar " << nomor << endl;
    }

    ~Terdaftar() {
        cout << "-Terdaftar " << nomor << endl;
    }
};

class Mesin {
private:
    int tenaga;

public:
    Mesin(int t) : tenaga(t) {
        cout << "+Mesin " << tenaga << endl;
    }

    ~Mesin() {
        cout << "-Mesin " << tenaga << endl;
    }
};

class Radio {
private:
    int frekuensi;

public:
    Radio(int f) : frekuensi(f) {
        cout << "+Radio " << frekuensi << endl;
    }

    ~Radio() {
        cout << "-Radio " << frekuensi << endl;
    }
};

class Mobil : public Kendaraan, public Terdaftar {
private:
    string nama;
    Mesin mesin;
    Radio radio;

public:
    Mobil(const string &n, const string &nomor, int tenaga, int frekuensi)
        : Terdaftar(nomor), nama(n), mesin(tenaga), radio(frekuensi) {
        cout << "+Mobil " << nama << endl;
    }

    ~Mobil() {
        cout << "-Mobil " << nama << endl;
    }
};''', r'''    string nama, nomor;
    int tenaga, frekuensi;
    cin >> nama >> nomor >> tenaga >> frekuensi;
    Mobil mobil(nama, nomor, tenaga, frekuensi);
    cout << "Mobil siap" << endl;''', "string"), wajib=W,
     id=("Urutan Lengkap", "Aturan lengkap urutan pembangunan sebuah objek: (1) **semua induk**, menurut urutan penulisannya di daftar induk; (2) **semua objek anggota**, menurut urutan deklarasinya di dalam class; (3) **badan konstruktor** class itu sendiri. Urutan di daftar inisialisasi tidak berpengaruh. Penghancuran persis terbalik.\n\n"
         "Buat lima class yang konstruktor dan destruktornya mencetak `+…` / `-…`:\n\n"
         "- `Kendaraan` — `+Kendaraan`;\n- `Terdaftar` (atribut `string nomor`) — `+Terdaftar <nomor>`;\n- `Mesin` (atribut `int tenaga`) — `+Mesin <tenaga>`;\n- `Radio` (atribut `int frekuensi`) — `+Radio <frekuensi>`;\n"
         "- `Mobil` — mewarisi `Kendaraan` lalu `Terdaftar` (dalam urutan itu), dan punya anggota, dalam urutan ini: `string nama`, `Mesin mesin`, `Radio radio` — `+Mobil <nama>`.\n\n"
         "Baca nama, nomor polisi, tenaga, dan frekuensi. Buat satu `Mobil`, lalu cetak `Mobil siap`.\n\n"
         "Untuk `Kijang D5678AB 100 99`:\n\n```\n+Kendaraan\n+Terdaftar D5678AB\n+Mesin 100\n+Radio 99\n+Mobil Kijang\nMobil siap\n-Mobil Kijang\n-Radio 99\n-Mesin 100\n-Terdaftar D5678AB\n-Kendaraan\n```" + W_ID,
         "Satu baris berisi nama, nomor polisi, tenaga, dan frekuensi.", "Sebelas baris seperti contoh.", "Nama dan nomor polisi satu kata; 60 ≤ tenaga ≤ 300; 88 ≤ frekuensi ≤ 107"),
     en=("The Full Order", "The full rule for the order in which an object is built: (1) **all bases**, in the order they are written in the base list; (2) **all member objects**, in the order of their declaration inside the class; (3) **the constructor body** of the class itself. The order of the initialiser list has no effect. Destruction is exactly the reverse.\n\n"
         "Make five classes whose constructors and destructors print `+…` / `-…`:\n\n"
         "- `Kendaraan` — `+Kendaraan`;\n- `Terdaftar` (registered; attribute `string nomor`) — `+Terdaftar <number>`;\n- `Mesin` (attribute `int tenaga`) — `+Mesin <power>`;\n- `Radio` (attribute `int frekuensi`) — `+Radio <frequency>`;\n"
         "- `Mobil` — inherits `Kendaraan` and then `Terdaftar` (in that order), and has the members, in this order: `string nama`, `Mesin mesin`, `Radio radio` — `+Mobil <name>`.\n\n"
         "Read the name, the registration number, the power and the frequency. Create one `Mobil`, then print `Mobil siap` (the car is ready).\n\n"
         "For `Kijang D5678AB 100 99`:\n\n```\n+Kendaraan\n+Terdaftar D5678AB\n+Mesin 100\n+Radio 99\n+Mobil Kijang\nMobil siap\n-Mobil Kijang\n-Radio 99\n-Mesin 100\n-Terdaftar D5678AB\n-Kendaraan\n```" + W_EN,
         "One line with the name, the registration number, the power and the frequency.", "Eleven lines as in the example.", "Name and registration number are one word; 60 ≤ power ≤ 300; 88 ≤ frequency ≤ 107"),
     ar=("الترتيب الكامل", "القاعدة الكاملة لترتيب بناء الكائن: (1) **كل الأسس** بترتيب كتابتها في قائمة الأسس؛ (2) **كل الكائنات الأعضاء** بترتيب التصريح بها داخل الصنف؛ (3) **جسم باني** الصنف نفسه. ترتيب قائمة التهيئة لا أثر له. والهدم بالعكس تمامًا.\n\n"
         "أنشئ خمسة أصناف تطبع بانياتها وهادماتها `+…` / `-…`:\n\n"
         "- `Kendaraan` — `+Kendaraan`؛\n- `Terdaftar` (مسجَّل؛ الخاصية `string nomor`) — `+Terdaftar <الرقم>`؛\n- `Mesin` (الخاصية `int tenaga`) — `+Mesin <القدرة>`؛\n- `Radio` (الخاصية `int frekuensi`) — `+Radio <التردد>`؛\n"
         "- `Mobil` — يرث `Kendaraan` ثم `Terdaftar` (بهذا الترتيب)، وأعضاؤه بهذا الترتيب: `string nama` ثم `Mesin mesin` ثم `Radio radio` — `+Mobil <الاسم>`.\n\n"
         "اقرأ الاسم ورقم اللوحة والقدرة والتردد. أنشئ `Mobil` واحدة ثم اطبع `Mobil siap` (السيارة جاهزة).\n\n"
         "عند `Kijang D5678AB 100 99`:\n\n```\n+Kendaraan\n+Terdaftar D5678AB\n+Mesin 100\n+Radio 99\n+Mobil Kijang\nMobil siap\n-Mobil Kijang\n-Radio 99\n-Mesin 100\n-Terdaftar D5678AB\n-Kendaraan\n```" + W_AR,
         "سطر واحد فيه الاسم ورقم اللوحة والقدرة والتردد.", "أحد عشر سطرًا كما في المثال.", "الاسم ورقم اللوحة كلمة واحدة؛ 60 ≤ القدرة ≤ 300؛ 88 ≤ التردد ≤ 107"))

soal("inventaris-berjenjang", "sedang", "trimmed",
     "BARANG = ['Meja', 'Kursi', 'Lemari', 'Rak', 'Kipas', 'Lampu', 'Monitor', 'Printer', 'Aspire', 'Vivo', 'Idea', 'Pavilion']\nn = {2: 1}.get(i, r.randint(2, 8))\nprint(n)\nfor nama in r.sample(BARANG, n):\n    j = r.choice(['barang', 'elektronik', 'laptop'])\n    v = [nama, r.randint(1, 200) * 100000]\n    if j != 'barang':\n        v.append(r.choice([6, 12, 24, 36]))\n    if j == 'laptop':\n        v.append(r.choice([4, 8, 16, 32]))\n    print(j, *v)\n",
     program(r'''
class Barang {
protected:
    string nama;
    int harga;

public:
    Barang(const string &n, int h) : nama(n), harga(h) {
    }

    int getHarga() const {
        return harga;
    }

    void cetak() const {
        cout << nama << " Rp " << harga;
    }
};

class Elektronik : public Barang {
protected:
    int garansi;

public:
    Elektronik(const string &n, int h, int g) : Barang(n, h), garansi(g) {
    }

    void cetak() const {
        Barang::cetak();
        cout << ", garansi " << garansi << " bulan";
    }
};

class Laptop : public Elektronik {
private:
    int ram;

public:
    Laptop(const string &n, int h, int g, int r) : Elektronik(n, h, g), ram(r) {
    }

    void cetak() const {
        Elektronik::cetak();
        cout << ", RAM " << ram << " GB";
    }
};''', r'''    int n;
    cin >> n;
    auto total = 0;
    for (int k = 0; k < n; k++) {
        string jenis, nama;
        int harga;
        cin >> jenis >> nama >> harga;
        if (jenis == "barang") {
            Barang b(nama, harga);
            b.cetak();
        } else if (jenis == "elektronik") {
            int garansi;
            cin >> garansi;
            Elektronik e(nama, harga, garansi);
            e.cetak();
        } else {
            int garansi, ram;
            cin >> garansi >> ram;
            Laptop l(nama, harga, garansi, ram);
            l.cetak();
        }
        cout << endl;
        total += harga;
    }
    cout << "Total: Rp " << total << endl;''', "string"), wajib=W,
     id=("Inventaris Berjenjang", "Tiap tingkat hierarki cukup mencetak **bagiannya sendiri**, setelah meminta induknya mencetak bagian induk.\n\n"
         "Buat tiga class, masing-masing dengan `void cetak() const` yang **tidak** pindah baris:\n\n"
         "- `Barang` (atribut `string nama`, `int harga`) — mencetak `<nama> Rp <harga>`;\n- `Elektronik` turunan `Barang` (tambahan `int garansi`) — memanggil `Barang::cetak()`, lalu mencetak `, garansi <g> bulan`;\n"
         "- `Laptop` turunan `Elektronik` (tambahan `int ram`) — memanggil `Elektronik::cetak()`, lalu mencetak `, RAM <r> GB`.\n\n"
         "Masukan: n barang, satu per baris: `barang <nama> <harga>`, `elektronik <nama> <harga> <garansi>`, atau `laptop <nama> <harga> <garansi> <ram>`. Cetak tiap barang di satu baris, lalu `Total: Rp <jumlah harga>`.\n\n"
         "Contoh: `barang Meja 500000`, `laptop Aspire 9000000 12 16` →\n\n```\nMeja Rp 500000\nAspire Rp 9000000, garansi 12 bulan, RAM 16 GB\nTotal: Rp 9500000\n```" + W_ID,
         "Baris pertama: n. Lalu n baris barang.", "Satu baris per barang, lalu `Total: Rp …`.", "1 ≤ n ≤ 8; harga kelipatan 100000 sampai 20000000"),
     en=("Inventory in Levels", "Each level of a hierarchy only has to print **its own part**, after asking its base to print the base's part.\n\n"
         "Make three classes, each with a `void cetak() const` that does **not** end the line:\n\n"
         "- `Barang` (item; attributes `string nama`, `int harga`) — prints `<name> Rp <price>`;\n- `Elektronik` derived from `Barang` (extra `int garansi` (warranty)) — calls `Barang::cetak()`, then prints `, garansi <g> bulan`;\n"
         "- `Laptop` derived from `Elektronik` (extra `int ram`) — calls `Elektronik::cetak()`, then prints `, RAM <r> GB`.\n\n"
         "Input: n items, one per line: `barang <name> <price>`, `elektronik <name> <price> <warranty>`, or `laptop <name> <price> <warranty> <ram>`. Print each item on one line, then `Total: Rp <summed price>`.\n\n"
         "Example: `barang Meja 500000`, `laptop Aspire 9000000 12 16` →\n\n```\nMeja Rp 500000\nAspire Rp 9000000, garansi 12 bulan, RAM 16 GB\nTotal: Rp 9500000\n```" + W_EN,
         "First line: n. Then n item lines.", "One line per item, then `Total: Rp …`.", "1 ≤ n ≤ 8; prices are multiples of 100000 up to 20000000"),
     ar=("جرد متدرّج", "يكفي كل مستوى في التسلسل أن يطبع **جزءه**، بعد أن يطلب من أساسه طباعة جزء الأساس.\n\n"
         "أنشئ ثلاثة أصناف لكل منها `void cetak() const` **لا** تنهي السطر:\n\n"
         "- `Barang` (سلعة؛ الخاصيتان `string nama` و `int harga`) — تطبع `<الاسم> Rp <السعر>`؛\n- `Elektronik` المشتق من `Barang` (إضافة `int garansi` (الضمان)) — تستدعي `Barang::cetak()` ثم تطبع `, garansi <g> bulan`؛\n"
         "- `Laptop` المشتق من `Elektronik` (إضافة `int ram`) — تستدعي `Elektronik::cetak()` ثم تطبع `, RAM <r> GB`.\n\n"
         "المدخل: n سلعة، واحدة في كل سطر: `barang <الاسم> <السعر>` أو `elektronik <الاسم> <السعر> <الضمان>` أو `laptop <الاسم> <السعر> <الضمان> <ram>`. اطبع كل سلعة في سطر ثم `Total: Rp <مجموع الأسعار>`.\n\n"
         "مثال: `barang Meja 500000` و `laptop Aspire 9000000 12 16` ←\n\n```\nMeja Rp 500000\nAspire Rp 9000000, garansi 12 bulan, RAM 16 GB\nTotal: Rp 9500000\n```" + W_AR,
         "السطر الأول: n. ثم n سطر سلع.", "سطر لكل سلعة ثم `Total: Rp …`.", "1 ≤ n ≤ 8؛ الأسعار من مضاعفات 100000 حتى 20000000"))

soal("tiga-rekening", "sedang", "trimmed",
     "print(r.randint(1, 20) * 50000, r.randint(0, 10) * 50000, r.randint(10, 40) * 50000)\nfor _ in range(r.randint(1, 15)):\n    print(r.choice(['setor', 'tarik', 'tarik']), r.choice('tgd'), r.randint(1, 16) * 50000)\nprint('selesai')\n",
     program(r'''
class Rekening {
protected:
    int saldo;

public:
    Rekening(int awal) : saldo(awal) {
    }

    bool setor(int jumlah) {
        saldo += jumlah;
        return true;
    }

    bool tarik(int jumlah) {
        if (jumlah > saldo) {
            return false;
        }
        saldo -= jumlah;
        return true;
    }

    int lihat() const {
        return saldo;
    }
};

class Tabungan : public Rekening {
public:
    Tabungan(int awal) : Rekening(awal) {
    }

    bool tarik(int jumlah) {
        if (saldo - jumlah < 50000) {
            return false;
        }
        return Rekening::tarik(jumlah);
    }
};

class Giro : public Rekening {
public:
    Giro(int awal) : Rekening(awal) {
    }

    bool tarik(int jumlah) {
        if (saldo - jumlah < -500000) {
            return false;
        }
        saldo -= jumlah;
        return true;
    }
};

class Deposito : public Rekening {
public:
    Deposito(int awal) : Rekening(awal) {
    }

    bool setor(int) {
        return false;
    }

    bool tarik(int) {
        return false;
    }
};''', r'''    int t, g, d;
    cin >> t >> g >> d;
    Tabungan tabungan(t);
    Giro giro(g);
    Deposito deposito(d);
    string perintah;
    while (cin >> perintah) {
        if (perintah == "selesai") {
            break;
        }
        char mana;
        int jumlah;
        cin >> mana >> jumlah;
        auto berhasil = false;
        auto saldo = 0;
        if (mana == 't') {
            berhasil = perintah == "setor" ? tabungan.setor(jumlah) : tabungan.tarik(jumlah);
            saldo = tabungan.lihat();
        } else if (mana == 'g') {
            berhasil = perintah == "setor" ? giro.setor(jumlah) : giro.tarik(jumlah);
            saldo = giro.lihat();
        } else {
            berhasil = perintah == "setor" ? deposito.setor(jumlah) : deposito.tarik(jumlah);
            saldo = deposito.lihat();
        }
        if (berhasil) {
            cout << saldo << endl;
        } else {
            cout << "Ditolak" << endl;
        }
    }
    cout << "Tabungan: " << tabungan.lihat() << endl;
    cout << "Giro: " << giro.lihat() << endl;
    cout << "Deposito: " << deposito.lihat() << endl;''', "string"), wajib=W,
     id=("Tiga Jenis Rekening", "Buat class `Rekening` (atribut `protected` `int saldo`; `bool setor(int)` selalu berhasil; `bool tarik(int)` gagal bila jumlah melebihi saldo; `int lihat() const`) dan tiga turunan yang **mengganti aturan sebagian method**:\n\n"
         "- `Tabungan` — `tarik` hanya berhasil bila saldo sesudahnya masih **paling sedikit 50000**;\n- `Giro` — `tarik` boleh membuat saldo negatif, paling jauh sampai **−500000**;\n- `Deposito` — `setor` dan `tarik` **selalu ditolak**.\n\n"
         "Method yang tidak diganti dipakai dari induk apa adanya.\n\n"
         "Masukan: saldo awal tabungan, giro, dan deposito; lalu perintah `setor <t|g|d> <jumlah>` atau `tarik <t|g|d> <jumlah>`, diakhiri `selesai`. Huruf `t`, `g`, `d` menunjuk rekeningnya. Untuk tiap perintah cetak saldo rekening itu sesudahnya, atau `Ditolak`. Di akhir cetak ketiga saldo.\n\n"
         "Contoh: saldo `200000 0 1000000`; `tarik t 160000`, `tarik g 300000`, `setor d 50000`, `tarik t 150000`, `selesai` →\n\n```\nDitolak\n-300000\nDitolak\n50000\nTabungan: 50000\nGiro: -300000\nDeposito: 1000000\n```" + W_ID,
         "Baris pertama: tiga saldo awal. Lalu perintah-perintah, diakhiri `selesai`.", "Satu baris per perintah, lalu tiga baris saldo akhir.", "Paling banyak 15 perintah; semua jumlah kelipatan 50000, paling banyak 2000000"),
     en=("Three Kinds of Account", "Make a class `Rekening` (account; `protected` attribute `int saldo`; `bool setor(int)` always succeeds; `bool tarik(int)` fails when the amount exceeds the balance; `int lihat() const`) and three derived classes that **replace the rule of some methods**:\n\n"
         "- `Tabungan` (savings) — `tarik` succeeds only when the balance afterwards is still **at least 50000**;\n- `Giro` (current account) — `tarik` may make the balance negative, at most down to **−500000**;\n- `Deposito` (time deposit) — `setor` and `tarik` are **always refused**.\n\n"
         "Methods that are not replaced are used from the base as they are.\n\n"
         "Input: the opening balances of the savings, the current account and the deposit; then commands `setor <t|g|d> <amount>` or `tarik <t|g|d> <amount>`, ending with `selesai`. The letters `t`, `g`, `d` name the account. For each command print that account's balance afterwards, or `Ditolak`. At the end print the three balances.\n\n"
         "Example: balances `200000 0 1000000`; `tarik t 160000`, `tarik g 300000`, `setor d 50000`, `tarik t 150000`, `selesai` →\n\n```\nDitolak\n-300000\nDitolak\n50000\nTabungan: 50000\nGiro: -300000\nDeposito: 1000000\n```" + W_EN,
         "First line: three opening balances. Then the commands, ending with `selesai`.", "One line per command, then three lines of closing balances.", "At most 15 commands; all amounts are multiples of 50000, at most 2000000"),
     ar=("ثلاثة أنواع من الحسابات", "أنشئ صنفًا `Rekening` (حساب؛ الخاصية المحمية `int saldo`؛ و `bool setor(int)` تنجح دائمًا؛ و `bool tarik(int)` تفشل إذا زاد المبلغ على الرصيد؛ و `int lihat() const`) وثلاثة أصناف مشتقة **تستبدل قاعدة بعض الطرائق**:\n\n"
         "- `Tabungan` (توفير) — ينجح `tarik` فقط إذا بقي الرصيد بعده **50000 على الأقل**؛\n- `Giro` (جارٍ) — يجوز أن يجعل `tarik` الرصيد سالبًا حتى **−500000** على الأكثر؛\n- `Deposito` (وديعة لأجل) — `setor` و `tarik` **مرفوضان دائمًا**.\n\n"
         "الطرائق التي لم تُستبدل تُستعمل من الأساس كما هي.\n\n"
         "المدخل: الأرصدة الابتدائية للتوفير والجاري والوديعة؛ ثم أوامر `setor <t|g|d> <المبلغ>` أو `tarik <t|g|d> <المبلغ>` تنتهي بـ `selesai`. الحروف `t` و `g` و `d` تعيّن الحساب. لكل أمر اطبع رصيد ذلك الحساب بعده، أو `Ditolak`. في النهاية اطبع الأرصدة الثلاثة.\n\n"
         "مثال: الأرصدة `200000 0 1000000`؛ `tarik t 160000` ثم `tarik g 300000` ثم `setor d 50000` ثم `tarik t 150000` ثم `selesai` ←\n\n```\nDitolak\n-300000\nDitolak\n50000\nTabungan: 50000\nGiro: -300000\nDeposito: 1000000\n```" + W_AR,
         "السطر الأول: ثلاثة أرصدة ابتدائية. ثم الأوامر منتهيةً بـ `selesai`.", "سطر لكل أمر ثم ثلاثة أسطر للأرصدة النهائية.", "15 أمرًا على الأكثر؛ كل المبالغ من مضاعفات 50000 وأقصاها 2000000"))

soal("serangan-udara", "sedang", "trimmed",
     SATUAN + "n = {2: 1}.get(i, r.randint(2, 8))\nprint(n, r.choice([20, 40, 60, 80, 100]))\nfor nama in r.sample(SATUAN, n):\n    print(r.choice(['infanteri', 'tank', 'artileri']), nama, r.randint(1, 30) * 100)\n",
     program(r'''
class Unit {
protected:
    string nama;
    int personel;

public:
    Unit(const string &n, int p) : nama(n), personel(p) {
    }

    string getNama() const {
        return nama;
    }

    int getPersonel() const {
        return personel;
    }

    void berkurang(int persen) {
        personel -= personel / 100 * persen;
    }
};

class Infanteri : public Unit {
public:
    Infanteri(const string &n, int p) : Unit(n, p) {
    }

    void kenaSerangan(int persen) {
        berkurang(persen);
    }
};

class Tank : public Unit {
public:
    Tank(const string &n, int p) : Unit(n, p) {
    }

    void kenaSerangan(int persen) {
        berkurang(persen / 4);
    }
};

class Artileri : public Unit {
public:
    Artileri(const string &n, int p) : Unit(n, p) {
    }

    void kenaSerangan(int persen) {
        berkurang(persen / 2);
    }
};''', r'''    int n, persen;
    cin >> n >> persen;
    auto total = 0;
    for (int k = 0; k < n; k++) {
        string jenis, nama;
        int personel;
        cin >> jenis >> nama >> personel;
        auto sisa = 0;
        if (jenis == "infanteri") {
            Infanteri u(nama, personel);
            u.kenaSerangan(persen);
            sisa = u.getPersonel();
        } else if (jenis == "tank") {
            Tank u(nama, personel);
            u.kenaSerangan(persen);
            sisa = u.getPersonel();
        } else {
            Artileri u(nama, personel);
            u.kenaSerangan(persen);
            sisa = u.getPersonel();
        }
        cout << nama << ": " << sisa << endl;
        total += sisa;
    }
    cout << "Total: " << total << endl;''', "string"), wajib=W,
     id=("Serangan Udara", "Sebuah serangan udara berkekuatan p persen menimpa beberapa satuan. Akibatnya bergantung pada jenis satuan:\n\n"
         "- **infanteri** kehilangan p persen personelnya;\n- **artileri** setengahnya: p / 2 persen;\n- **tank**, yang berlapis baja, seperempatnya: p / 4 persen.\n\n"
         "Buat class `Unit` (atribut `protected` `string nama`, `int personel`; `getNama()`, `getPersonel()`; dan `void berkurang(int persen)` — personel berkurang sebesar persen dari personel). Buat turunannya `Infanteri`, `Artileri`, dan `Tank`, masing-masing dengan `void kenaSerangan(int persen)` yang memanggil `berkurang` dengan persen yang sesuai.\n\n"
         "Cara mengurangi personel ditulis sekali di induk; tiap turunan hanya menentukan *seberapa besar*.\n\n"
         "Masukan: n dan p; lalu n satuan: jenis, nama, dan personel. Cetak `<nama>: <sisa personel>` untuk tiap satuan, lalu `Total: T`.\n\n"
         "Contoh: p = 40; `infanteri Alfa 500`, `tank Bravo 200`, `artileri Delta 300` →\n\n```\nAlfa: 300\nBravo: 180\nDelta: 240\nTotal: 720\n```" + W_ID,
         "Baris pertama: n dan p. Lalu n baris: jenis, nama, personel.", "Satu baris per satuan, lalu `Total: T`.", "1 ≤ n ≤ 8; p adalah 20, 40, 60, 80, atau 100; personel kelipatan 100 antara 100 dan 3000"),
     en=("Air Raid", "An air raid of strength p percent hits several units. The effect depends on the kind of unit:\n\n"
         "- **infantry** (`infanteri`) lose p percent of their personnel;\n- **artillery** (`artileri`) half of that: p / 2 percent;\n- **tanks**, being armoured, a quarter: p / 4 percent.\n\n"
         "Make a class `Unit` (`protected` attributes `string nama`, `int personel`; `getNama()`, `getPersonel()`; and `void berkurang(int persen)` (reduce) — the personnel drop by that percentage of the personnel). Make its derived classes `Infanteri`, `Artileri` and `Tank`, each with `void kenaSerangan(int persen)` (hit by the raid), which calls `berkurang` with the fitting percentage.\n\n"
         "How personnel are reduced is written once in the base; each derived class only decides *by how much*.\n\n"
         "Input: n and p; then n units: kind, name and personnel. Print `<name>: <personnel left>` for each unit, then `Total: T`.\n\n"
         "Example: p = 40; `infanteri Alfa 500`, `tank Bravo 200`, `artileri Delta 300` →\n\n```\nAlfa: 300\nBravo: 180\nDelta: 240\nTotal: 720\n```" + W_EN,
         "First line: n and p. Then n lines: kind, name, personnel.", "One line per unit, then `Total: T`.", "1 ≤ n ≤ 8; p is 20, 40, 60, 80 or 100; personnel are multiples of 100 between 100 and 3000"),
     ar=("غارة جوية", "غارة جوية شدتها p بالمئة تصيب عدة وحدات. وأثرها يتبع نوع الوحدة:\n\n"
         "- **المشاة** (`infanteri`) يفقدون p بالمئة من أفرادهم؛\n- **المدفعية** (`artileri`) نصف ذلك: p / 2 بالمئة؛\n- **الدبابات** لأنها مدرّعة ربعه: p / 4 بالمئة.\n\n"
         "أنشئ صنفًا `Unit` (الخاصيتان المحميتان `string nama` و `int personel`؛ و `getNama()` و `getPersonel()`؛ و `void berkurang(int persen)` (انقص) — ينقص الأفراد بتلك النسبة منهم). أنشئ مشتقاته `Infanteri` و `Artileri` و `Tank`، ولكل منها `void kenaSerangan(int persen)` (أصابته الغارة) تستدعي `berkurang` بالنسبة المناسبة.\n\n"
         "طريقة إنقاص الأفراد تُكتب مرة في الأساس؛ وكل مشتق يحدد *المقدار* فقط.\n\n"
         "المدخل: n و p؛ ثم n وحدة: النوع والاسم والأفراد. اطبع `<الاسم>: <الأفراد الباقون>` لكل وحدة ثم `Total: T`.\n\n"
         "مثال: p = 40؛ `infanteri Alfa 500` و `tank Bravo 200` و `artileri Delta 300` ←\n\n```\nAlfa: 300\nBravo: 180\nDelta: 240\nTotal: 720\n```" + W_AR,
         "السطر الأول: n و p. ثم n سطرًا: النوع والاسم والأفراد.", "سطر لكل وحدة ثم `Total: T`.", "1 ≤ n ≤ 8؛ p يساوي 20 أو 40 أو 60 أو 80 أو 100؛ الأفراد من مضاعفات 100 بين 100 و 3000"))

soal("antrean-prioritas", "sedang", "trimmed",
     "for _ in range(r.randint(1, 22)):\n    q = r.random()\n    if q < 0.3:\n        print('biasa', r.randint(1, 20))\n    elif q < 0.6:\n        print('prio', r.randint(1, 20))\n    elif q < 0.8:\n        print('layani', 'biasa')\n    else:\n        print('layani', 'prio')\nprint('selesai')\n",
     program(r'''
class Daftar {
protected:
    int data[25];
    int banyak = 0;

public:
    int panjang() const {
        return banyak;
    }

    bool kosong() const {
        return banyak == 0;
    }
};

class Antrean : public Daftar {
public:
    void masuk(int x) {
        data[banyak] = x;
        banyak++;
    }

    int keluar() {
        auto x = data[0];
        for (int k = 0; k < banyak - 1; k++) {
            data[k] = data[k + 1];
        }
        banyak--;
        return x;
    }
};

class AntreanPrioritas : public Antrean {
public:
    void masuk(int x) {
        auto posisi = banyak;
        while (posisi > 0 && data[posisi - 1] < x) {
            data[posisi] = data[posisi - 1];
            posisi--;
        }
        data[posisi] = x;
        banyak++;
    }
};''', r'''    Antrean biasa;
    AntreanPrioritas prio;
    string perintah;
    while (cin >> perintah) {
        if (perintah == "selesai") {
            break;
        }
        if (perintah == "biasa" || perintah == "prio") {
            int x;
            cin >> x;
            if (perintah == "biasa") {
                biasa.masuk(x);
            } else {
                prio.masuk(x);
            }
        } else {
            string mana;
            cin >> mana;
            if (mana == "biasa") {
                if (biasa.kosong()) {
                    cout << "kosong" << endl;
                } else {
                    cout << biasa.keluar() << endl;
                }
            } else if (prio.kosong()) {
                cout << "kosong" << endl;
            } else {
                cout << prio.keluar() << endl;
            }
        }
    }
    cout << "Biasa: " << biasa.panjang() << endl;
    cout << "Prioritas: " << prio.panjang() << endl;''', "string"), wajib=W,
     id=("Antrean Prioritas", "Susun tiga tingkat struktur data:\n\n"
         "- `Daftar` — penyimpanan dasar: atribut `protected` `int data[25]`, `int banyak`; method `int panjang() const`, `bool kosong() const`;\n"
         "- `Antrean` turunan `Daftar` — `void masuk(int x)` menaruh di belakang; `int keluar()` mengambil dari depan;\n"
         "- `AntreanPrioritas` turunan `Antrean` — **hanya mengganti `masuk`**: elemen baru disisipkan sehingga isi antrean selalu terurut **dari yang terbesar**; di antara elemen yang sama besar, yang datang lebih dulu tetap di depan. `keluar` diwarisi tanpa diubah — dan karena susunannya terjaga, ia selalu mengeluarkan yang terbesar.\n\n"
         "Program memakai satu `Antrean` dan satu `AntreanPrioritas`. Perintah: `biasa x`, `prio x`, `layani biasa`, `layani prio` (cetak elemen yang keluar, atau `kosong`), `selesai`. Di akhir cetak `Biasa: <panjang>` dan `Prioritas: <panjang>`.\n\n"
         "Contoh: `biasa 3`, `biasa 9`, `prio 3`, `prio 9`, `prio 5`, `layani biasa`, `layani prio`, `layani prio`, `selesai` →\n\n```\n3\n9\n5\nBiasa: 1\nPrioritas: 1\n```" + W_ID,
         "Beberapa baris perintah, diakhiri `selesai`.", "Satu baris untuk tiap `layani`, lalu dua baris panjang.", "Paling banyak 22 perintah; 1 ≤ x ≤ 20"),
     en=("Priority Queue", "Build a data structure in three levels:\n\n"
         "- `Daftar` (list) — basic storage: `protected` attributes `int data[25]`, `int banyak`; methods `int panjang() const` (length), `bool kosong() const`;\n"
         "- `Antrean` (queue) derived from `Daftar` — `void masuk(int x)` puts at the back; `int keluar()` takes from the front;\n"
         "- `AntreanPrioritas` (priority queue) derived from `Antrean` — **replaces only `masuk`**: a new element is inserted so that the queue is always sorted **from the largest**; among equal elements the one that came first stays in front. `keluar` is inherited unchanged — and because the order is maintained it always yields the largest.\n\n"
         "The program uses one `Antrean` and one `AntreanPrioritas`. Commands: `biasa x` (ordinary), `prio x`, `layani biasa`, `layani prio` (print the element that leaves, or `kosong`), `selesai`. At the end print `Biasa: <length>` and `Prioritas: <length>`.\n\n"
         "Example: `biasa 3`, `biasa 9`, `prio 3`, `prio 9`, `prio 5`, `layani biasa`, `layani prio`, `layani prio`, `selesai` →\n\n```\n3\n9\n5\nBiasa: 1\nPrioritas: 1\n```" + W_EN,
         "Several command lines, ending with `selesai`.", "One line for every `layani`, then two length lines.", "At most 22 commands; 1 ≤ x ≤ 20"),
     ar=("طابور الأولوية", "ابنِ هيكل بيانات من ثلاثة مستويات:\n\n"
         "- `Daftar` (قائمة) — التخزين الأساسي: الخاصيتان المحميتان `int data[25]` و `int banyak`؛ والطريقتان `int panjang() const` (الطول) و `bool kosong() const`؛\n"
         "- `Antrean` (طابور) المشتق من `Daftar` — `void masuk(int x)` تضع في الخلف؛ و `int keluar()` تأخذ من الأمام؛\n"
         "- `AntreanPrioritas` (طابور أولوية) المشتق من `Antrean` — **يستبدل `masuk` فقط**: يُدرج العنصر الجديد بحيث يبقى الطابور مرتبًا **من الأكبر** دائمًا؛ وبين العناصر المتساوية يبقى الأسبق وصولًا في الأمام. و `keluar` موروثة بلا تغيير — ولأن الترتيب محفوظ فهي تُخرج الأكبر دائمًا.\n\n"
         "يستعمل البرنامج `Antrean` واحدًا و `AntreanPrioritas` واحدًا. الأوامر: `biasa x` (عادي) و `prio x` و `layani biasa` و `layani prio` (اطبع العنصر الخارج أو `kosong`) و `selesai`. في النهاية اطبع `Biasa: <الطول>` و `Prioritas: <الطول>`.\n\n"
         "مثال: `biasa 3` ثم `biasa 9` ثم `prio 3` ثم `prio 9` ثم `prio 5` ثم `layani biasa` ثم `layani prio` ثم `layani prio` ثم `selesai` ←\n\n```\n3\n9\n5\nBiasa: 1\nPrioritas: 1\n```" + W_AR,
         "عدة أسطر أوامر تنتهي بـ `selesai`.", "سطر لكل `layani` ثم سطرا الطول.", "22 أمرًا على الأكثر؛ 1 ≤ x ≤ 20"))

soal("duel-ksatria-penyihir", "sedang", "trimmed",
     NAMA + "a, b = r.sample(NAMA, 2)\nif i % 3 == 0:\n    print(a, r.randint(100, 150), r.randint(5, 12))\n    print(b, r.randint(95, 150), r.randint(0, 20))\nelse:\n    print(a, r.randint(40, 150), r.randint(0, 12))\n    print(b, r.randint(40, 150), r.randint(0, 60))\n",
     program(r'''
class Karakter {
protected:
    string nama;
    int hp;

public:
    Karakter(const string &n, int h) : nama(n), hp(h) {
    }

    string getNama() const {
        return nama;
    }

    int getHp() const {
        return hp;
    }

    bool hidup() const {
        return hp > 0;
    }

    void terima(int luka) {
        hp -= luka;
        if (hp < 0) {
            hp = 0;
        }
    }
};

class Ksatria : public Karakter {
private:
    int zirah;

public:
    Ksatria(const string &n, int h, int z) : Karakter(n, h), zirah(z) {
    }

    int serang() const {
        return 15;
    }

    void terima(int luka) {
        luka -= zirah;
        if (luka < 1) {
            luka = 1;
        }
        Karakter::terima(luka);
    }
};

class Penyihir : public Karakter {
private:
    int mana;

public:
    Penyihir(const string &n, int h, int m) : Karakter(n, h), mana(m) {
    }

    int serang() {
        if (mana >= 10) {
            mana -= 10;
            return 30;
        }
        return 5;
    }
};''', r'''    string nama;
    int hp, lain;
    cin >> nama >> hp >> lain;
    Ksatria ksatria(nama, hp, lain);
    cin >> nama >> hp >> lain;
    Penyihir penyihir(nama, hp, lain);
    for (int ronde = 1; ronde <= 6 && ksatria.hidup() && penyihir.hidup(); ronde++) {
        penyihir.terima(ksatria.serang());
        cout << ksatria.getNama() << " menyerang: " << penyihir.getNama() << " " << penyihir.getHp() << endl;
        if (penyihir.hidup()) {
            ksatria.terima(penyihir.serang());
            cout << penyihir.getNama() << " menyerang: " << ksatria.getNama() << " " << ksatria.getHp() << endl;
        }
    }
    if (!penyihir.hidup()) {
        cout << ksatria.getNama() << " menang" << endl;
    } else if (!ksatria.hidup()) {
        cout << penyihir.getNama() << " menang" << endl;
    } else {
        cout << "Seri" << endl;
    }''', "string"), wajib=W,
     id=("Duel Ksatria dan Penyihir", "Buat class `Karakter` (atribut `protected` `string nama`, `int hp`; `getNama()`, `getHp()`, `bool hidup() const`; dan `void terima(int luka)` — hp berkurang, tetapi tidak di bawah 0). Dua turunannya bertarung:\n\n"
         "- `Ksatria` (tambahan `int zirah`): `int serang() const` selalu 15. Ia **mengganti `terima`**: luka dikurangi zirah lebih dulu, tetapi tidak pernah kurang dari 1; sisanya diserahkan ke `Karakter::terima`;\n"
         "- `Penyihir` (tambahan `int mana`): `int serang()` — bila mana 10 atau lebih, mana berkurang 10 dan serangannya 30; bila tidak, serangannya 5. `terima`-nya warisan apa adanya.\n\n"
         "Jalannya duel, paling banyak **6 ronde**: tiap ronde ksatria menyerang lebih dulu; bila penyihir masih hidup, ia membalas. Setiap serangan dicetak sebagai `<penyerang> menyerang: <nama lawan> <hp lawan sesudahnya>`. Duel berhenti begitu ada yang hp-nya 0.\n\n"
         "Di akhir cetak `<nama> menang`, atau `Seri` bila setelah 6 ronde keduanya masih hidup.\n\n"
         "Masukan: baris pertama nama, hp, dan zirah ksatria; baris kedua nama, hp, dan mana penyihir.\n\n"
         "Contoh: `Andi 50 5` dan `Budi 40 10` →\n\n```\nAndi menyerang: Budi 25\nBudi menyerang: Andi 25\nAndi menyerang: Budi 10\nBudi menyerang: Andi 24\nAndi menyerang: Budi 0\nAndi menang\n```" + W_ID,
         "Dua baris: data ksatria, lalu data penyihir.", "Satu baris per serangan, lalu hasil duel.", "40 ≤ hp ≤ 150; 0 ≤ zirah ≤ 12; 0 ≤ mana ≤ 60"),
     en=("A Knight against a Wizard", "Make a class `Karakter` (character; `protected` attributes `string nama`, `int hp`; `getNama()`, `getHp()`, `bool hidup() const` (alive); and `void terima(int luka)` (take a wound) — hp drops, but not below 0). Its two derived classes fight:\n\n"
         "- `Ksatria` (knight; extra `int zirah` (armour)): `int serang() const` (attack) is always 15. It **replaces `terima`**: the wound is first reduced by the armour, but never below 1; the rest is passed to `Karakter::terima`;\n"
         "- `Penyihir` (wizard; extra `int mana`): `int serang()` — with 10 mana or more, the mana drops by 10 and the attack is 30; otherwise the attack is 5. Its `terima` is the inherited one.\n\n"
         "The duel lasts at most **6 rounds**: in each round the knight attacks first; if the wizard is still alive, the wizard strikes back. Every attack is printed as `<attacker> menyerang: <opponent's name> <opponent's hp afterwards>`. The duel stops as soon as someone's hp is 0.\n\n"
         "At the end print `<name> menang` (wins), or `Seri` (draw) when both are still alive after 6 rounds.\n\n"
         "Input: first line the knight's name, hp and armour; second line the wizard's name, hp and mana.\n\n"
         "Example: `Andi 50 5` and `Budi 40 10` →\n\n```\nAndi menyerang: Budi 25\nBudi menyerang: Andi 25\nAndi menyerang: Budi 10\nBudi menyerang: Andi 24\nAndi menyerang: Budi 0\nAndi menang\n```" + W_EN,
         "Two lines: the knight's data, then the wizard's.", "One line per attack, then the result of the duel.", "40 ≤ hp ≤ 150; 0 ≤ armour ≤ 12; 0 ≤ mana ≤ 60"),
     ar=("مبارزة الفارس والساحر", "أنشئ صنفًا `Karakter` (شخصية؛ الخاصيتان المحميتان `string nama` و `int hp`؛ و `getNama()` و `getHp()` و `bool hidup() const` (حي)؛ و `void terima(int luka)` (تلقَّ جرحًا) — تنقص hp دون أن تنزل عن 0). ومشتقاه يتبارزان:\n\n"
         "- `Ksatria` (فارس؛ إضافة `int zirah` (الدرع)): `int serang() const` (هجوم) دائمًا 15. وهو **يستبدل `terima`**: يُنقص الجرح بمقدار الدرع أولًا دون أن يقل عن 1؛ والباقي يُمرَّر إلى `Karakter::terima`؛\n"
         "- `Penyihir` (ساحر؛ إضافة `int mana`): `int serang()` — إذا كانت mana 10 أو أكثر نقصت 10 وكان الهجوم 30؛ وإلا فالهجوم 5. و `terima` عنده هي الموروثة.\n\n"
         "تستمر المبارزة **6 جولات** على الأكثر: في كل جولة يهاجم الفارس أولًا؛ فإذا بقي الساحر حيًّا ردّ. تُطبع كل ضربة على الصورة `<المهاجم> menyerang: <اسم الخصم> <hp الخصم بعدها>`. وتتوقف المبارزة حالما تصير hp أحدهما 0.\n\n"
         "في النهاية اطبع `<الاسم> menang` (فاز)، أو `Seri` (تعادل) إذا بقي كلاهما حيًّا بعد 6 جولات.\n\n"
         "المدخل: السطر الأول اسم الفارس و hp ودرعه؛ والسطر الثاني اسم الساحر و hp و mana.\n\n"
         "مثال: `Andi 50 5` و `Budi 40 10` ←\n\n```\nAndi menyerang: Budi 25\nBudi menyerang: Andi 25\nAndi menyerang: Budi 10\nBudi menyerang: Andi 24\nAndi menyerang: Budi 0\nAndi menang\n```" + W_AR,
         "سطران: بيانات الفارس ثم بيانات الساحر.", "سطر لكل ضربة ثم نتيجة المبارزة.", "40 ≤ hp ≤ 150؛ 0 ≤ الدرع ≤ 12؛ 0 ≤ mana ≤ 60"))

# ================================= SULIT (4) =================================

soal("amfibi-berkelana", "sulit", "trimmed",
     "print(r.choice(['Hiu', 'Buaya', 'Katak', 'Penyu']), r.randint(20, 200))\nfor _ in range(r.randint(1, 15)):\n    if r.random() < 0.7:\n        print('maju', r.randint(1, 25))\n    else:\n        print('ganti')\nprint('selesai')\n",
     program(r'''
class Kendaraan {
protected:
    string nama;
    int bensin;

public:
    Kendaraan(const string &n, int b) : nama(n), bensin(b) {
        cout << "+Kendaraan " << nama << endl;
    }

    ~Kendaraan() {
        cout << "-Kendaraan " << nama << endl;
    }

    bool pakai(int liter) {
        if (liter > bensin) {
            return false;
        }
        bensin -= liter;
        return true;
    }

    int getBensin() const {
        return bensin;
    }
};

class Darat : virtual public Kendaraan {
protected:
    int kmDarat = 0;

public:
    Darat(const string &n, int b) : Kendaraan(n, b) {
        cout << "+Darat" << endl;
    }

    ~Darat() {
        cout << "-Darat" << endl;
    }

    bool jalan(int km) {
        if (!pakai(km * 2)) {
            return false;
        }
        kmDarat += km;
        return true;
    }
};

class Air : virtual public Kendaraan {
protected:
    int kmAir = 0;

public:
    Air(const string &n, int b) : Kendaraan(n, b) {
        cout << "+Air" << endl;
    }

    ~Air() {
        cout << "-Air" << endl;
    }

    bool berlayar(int km) {
        if (!pakai(km * 5)) {
            return false;
        }
        kmAir += km;
        return true;
    }
};

class Amfibi : public Darat, public Air {
private:
    bool diAir = false;

public:
    Amfibi(const string &n, int b) : Kendaraan(n, b), Darat(n, b), Air(n, b) {
        cout << "+Amfibi" << endl;
    }

    ~Amfibi() {
        cout << "-Amfibi" << endl;
    }

    void ganti() {
        diAir = !diAir;
        if (diAir) {
            cout << "Mode air" << endl;
        } else {
            cout << "Mode darat" << endl;
        }
    }

    void maju(int km) {
        auto berhasil = false;
        if (diAir) {
            berhasil = berlayar(km);
        } else {
            berhasil = jalan(km);
        }
        if (berhasil) {
            cout << "Maju " << km << " km, bensin " << getBensin() << endl;
        } else {
            cout << "Bensin tidak cukup" << endl;
        }
    }

    void laporan() const {
        cout << "Darat " << kmDarat << " km, air " << kmAir << " km" << endl;
    }
};''', r'''    string nama;
    int bensin;
    cin >> nama >> bensin;
    Amfibi amfibi(nama, bensin);
    string perintah;
    while (cin >> perintah) {
        if (perintah == "selesai") {
            break;
        }
        if (perintah == "ganti") {
            amfibi.ganti();
        } else {
            int km;
            cin >> km;
            amfibi.maju(km);
        }
    }
    amfibi.laporan();''', "string"), wajib=W,
     id=("Amfibi Berkelana", "Buat hierarki berlian dengan **pewarisan virtual**, lengkap dengan data yang dipakai bersama.\n\n"
         "- `Kendaraan` — atribut `protected` `string nama`, `int bensin`; `bool pakai(int liter)` (gagal bila bensin kurang); `int getBensin() const`;\n"
         "- `Darat : virtual public Kendaraan` — `int kmDarat`; `bool jalan(int km)` memakai **2 liter per km**;\n- `Air : virtual public Kendaraan` — `int kmAir`; `bool berlayar(int km)` memakai **5 liter per km**;\n"
         "- `Amfibi : public Darat, public Air` — atribut `bool diAir` (mula-mula di darat); `void ganti()`, `void maju(int km)`, `void laporan() const`.\n\n"
         "Karena `Kendaraan` diwarisi secara virtual, `jalan` dan `berlayar` menguras **tangki yang sama**. Konstruktor `Amfibi` harus menyebut `Kendaraan(n, b)` sendiri.\n\n"
         "Konstruktor tiap class mencetak `+Kendaraan <nama>`, `+Darat`, `+Air`, `+Amfibi`; destruktornya `-…` yang sepadan.\n\n"
         "Perintah:\n\n- `ganti` — berpindah mode dan mencetak `Mode air` atau `Mode darat`;\n- `maju <km>` — bergerak di mode saat ini: bila bensin cukup cetak `Maju <km> km, bensin <sisa>`; bila tidak, cetak `Bensin tidak cukup` dan tidak bergerak;\n- `selesai` — cetak `Darat <x> km, air <y> km`.\n\n"
         "Contoh: `Hiu 50`; `maju 10`, `ganti`, `maju 10`, `maju 5`, `selesai` →\n\n```\n+Kendaraan Hiu\n+Darat\n+Air\n+Amfibi\nMaju 10 km, bensin 30\nMode air\nBensin tidak cukup\nMaju 5 km, bensin 5\nDarat 10 km, air 5 km\n-Amfibi\n-Air\n-Darat\n-Kendaraan Hiu\n```" + W_ID,
         "Baris pertama: nama dan bensin awal. Lalu perintah-perintah, diakhiri `selesai`.", "Baris-baris seperti contoh.", "20 ≤ bensin ≤ 200; paling banyak 15 perintah; 1 ≤ km ≤ 25"),
     en=("A Roaming Amphibian", "Build a diamond hierarchy with **virtual inheritance**, complete with shared data.\n\n"
         "- `Kendaraan` — `protected` attributes `string nama`, `int bensin` (fuel); `bool pakai(int liter)` (use; fails when the fuel is short); `int getBensin() const`;\n"
         "- `Darat : virtual public Kendaraan` — `int kmDarat`; `bool jalan(int km)` (drive) uses **2 litres per km**;\n- `Air : virtual public Kendaraan` — `int kmAir`; `bool berlayar(int km)` (sail) uses **5 litres per km**;\n"
         "- `Amfibi : public Darat, public Air` — attribute `bool diAir` (on water; on land at first); `void ganti()` (switch), `void maju(int km)` (advance), `void laporan() const`.\n\n"
         "Because `Kendaraan` is inherited virtually, `jalan` and `berlayar` drain **the same tank**. The constructor of `Amfibi` has to name `Kendaraan(n, b)` itself.\n\n"
         "The constructor of each class prints `+Kendaraan <name>`, `+Darat`, `+Air`, `+Amfibi`; the destructors print the matching `-…`.\n\n"
         "Commands:\n\n- `ganti` — switches mode and prints `Mode air` or `Mode darat`;\n- `maju <km>` — moves in the current mode: if the fuel suffices print `Maju <km> km, bensin <left>`; if not, print `Bensin tidak cukup` and do not move;\n- `selesai` — print `Darat <x> km, air <y> km`.\n\n"
         "Example: `Hiu 50`; `maju 10`, `ganti`, `maju 10`, `maju 5`, `selesai` →\n\n```\n+Kendaraan Hiu\n+Darat\n+Air\n+Amfibi\nMaju 10 km, bensin 30\nMode air\nBensin tidak cukup\nMaju 5 km, bensin 5\nDarat 10 km, air 5 km\n-Amfibi\n-Air\n-Darat\n-Kendaraan Hiu\n```" + W_EN,
         "First line: the name and the starting fuel. Then the commands, ending with `selesai`.", "Lines as in the example.", "20 ≤ fuel ≤ 200; at most 15 commands; 1 ≤ km ≤ 25"),
     ar=("برمائية جوّالة", "ابنِ تسلسلًا معيَّنيًّا **بالوراثة الافتراضية** مع بيانات مشتركة.\n\n"
         "- `Kendaraan` — الخاصيتان المحميتان `string nama` و `int bensin` (الوقود)؛ و `bool pakai(int liter)` (استعمل؛ تفشل إذا نقص الوقود)؛ و `int getBensin() const`؛\n"
         "- `Darat : virtual public Kendaraan` — `int kmDarat`؛ و `bool jalan(int km)` (سِر) تستهلك **لترين لكل كم**؛\n- `Air : virtual public Kendaraan` — `int kmAir`؛ و `bool berlayar(int km)` (أبحر) تستهلك **5 لترات لكل كم**؛\n"
         "- `Amfibi : public Darat, public Air` — الخاصية `bool diAir` (في الماء؛ وتبدأ على البر)؛ و `void ganti()` (بدِّل) و `void maju(int km)` (تقدَّم) و `void laporan() const`.\n\n"
         "لأن `Kendaraan` موروث افتراضيًا فإن `jalan` و `berlayar` تستنزفان **الخزان نفسه**. وباني `Amfibi` عليه أن يذكر `Kendaraan(n, b)` بنفسه.\n\n"
         "باني كل صنف يطبع `+Kendaraan <الاسم>` و `+Darat` و `+Air` و `+Amfibi`؛ والهادمات تطبع `-…` المقابلة.\n\n"
         "الأوامر:\n\n- `ganti` — تبدّل الوضع وتطبع `Mode air` أو `Mode darat`؛\n- `maju <km>` — تتحرك في الوضع الحالي: إذا كفى الوقود اطبع `Maju <km> km, bensin <الباقي>`؛ وإلا فاطبع `Bensin tidak cukup` ولا تتحرك؛\n- `selesai` — اطبع `Darat <x> km, air <y> km`.\n\n"
         "مثال: `Hiu 50`؛ `maju 10` ثم `ganti` ثم `maju 10` ثم `maju 5` ثم `selesai` ←\n\n```\n+Kendaraan Hiu\n+Darat\n+Air\n+Amfibi\nMaju 10 km, bensin 30\nMode air\nBensin tidak cukup\nMaju 5 km, bensin 5\nDarat 10 km, air 5 km\n-Amfibi\n-Air\n-Darat\n-Kendaraan Hiu\n```" + W_AR,
         "السطر الأول: الاسم والوقود الابتدائي. ثم الأوامر منتهيةً بـ `selesai`.", "أسطر كما في المثال.", "20 ≤ الوقود ≤ 200؛ 15 أمرًا على الأكثر؛ 1 ≤ km ≤ 25"))

soal("sistem-akademik", "sulit", "trimmed",
     NAMA + "nama = r.sample(NAMA, 12)\nk = 0\nterdaftar = []\nfor _ in range(r.randint(2, 16)):\n    q = r.random()\n    if q < 0.6 and k < 12:\n        j = r.choice(['dosen', 'mahasiswa', 'pasca', 'pasca'])\n        print(j, nama[k], r.randint(1000, 9999))\n        terdaftar.append(nama[k])\n        k += 1\n    else:\n        a = r.choice(terdaftar) if terdaftar and r.random() < 0.85 else 'Zaki'\n        b = r.choice(terdaftar) if terdaftar and r.random() < 0.85 else 'Zaki'\n        print('bimbing', a, b)\nprint('selesai')\n",
     program(r'''
class Orang {
protected:
    string nama;

public:
    static int banyak;

    void setNama(const string &n) {
        nama = n;
        banyak++;
    }

    string getNama() const {
        return nama;
    }

    void cetak() const {
        cout << nama;
    }
};

class Dosen : public Orang {
private:
    int nidn = 0;
    int bimbingan = 0;

public:
    void isi(const string &n, int nomor) {
        setNama(n);
        nidn = nomor;
    }

    bool terimaBimbingan() {
        if (bimbingan == 2) {
            return false;
        }
        bimbingan++;
        return true;
    }

    void cetak() const {
        Orang::cetak();
        cout << " (dosen, NIDN " << nidn << "), membimbing " << bimbingan;
    }
};

class Mahasiswa : public Orang {
protected:
    int nim = 0;

public:
    void isi(const string &n, int nomor) {
        setNama(n);
        nim = nomor;
    }

    void cetak() const {
        Orang::cetak();
        cout << " (mahasiswa, NIM " << nim << ")";
    }
};

class Pascasarjana : public Mahasiswa {
private:
    Dosen *pembimbing = nullptr;

public:
    bool sudahDibimbing() const {
        return pembimbing != nullptr;
    }

    void setPembimbing(Dosen *d) {
        pembimbing = d;
    }

    void cetak() const {
        Mahasiswa::cetak();
        cout << ", pascasarjana, pembimbing ";
        if (pembimbing == nullptr) {
            cout << "-";
        } else {
            cout << pembimbing->getNama();
        }
    }
};

int Orang::banyak = 0;''', r'''    Dosen dosen[12];
    Mahasiswa mahasiswa[12];
    Pascasarjana pasca[12];
    auto nd = 0;
    auto nm = 0;
    auto np = 0;
    char urutan[12];
    int nomor[12];
    auto total = 0;
    string perintah;
    while (cin >> perintah) {
        if (perintah == "selesai") {
            break;
        }
        if (perintah == "bimbing") {
            string a, b;
            cin >> a >> b;
            Pascasarjana *p = nullptr;
            Dosen *d = nullptr;
            for (int k = 0; k < np; k++) {
                if (pasca[k].getNama() == a) {
                    p = &pasca[k];
                }
            }
            for (int k = 0; k < nd; k++) {
                if (dosen[k].getNama() == b) {
                    d = &dosen[k];
                }
            }
            if (p == nullptr || d == nullptr) {
                cout << "Tidak ditemukan" << endl;
            } else if (p->sudahDibimbing()) {
                cout << "Sudah punya pembimbing" << endl;
            } else if (!d->terimaBimbingan()) {
                cout << "Dosen penuh" << endl;
            } else {
                p->setPembimbing(d);
                cout << a << " dibimbing " << b << endl;
            }
            continue;
        }
        string nama;
        int id;
        cin >> nama >> id;
        if (perintah == "dosen") {
            dosen[nd].isi(nama, id);
            urutan[total] = 'd';
            nomor[total] = nd;
            nd++;
        } else if (perintah == "mahasiswa") {
            mahasiswa[nm].isi(nama, id);
            urutan[total] = 'm';
            nomor[total] = nm;
            nm++;
        } else {
            pasca[np].isi(nama, id);
            urutan[total] = 'p';
            nomor[total] = np;
            np++;
        }
        total++;
    }
    for (int k = 0; k < total; k++) {
        if (urutan[k] == 'd') {
            dosen[nomor[k]].cetak();
        } else if (urutan[k] == 'm') {
            mahasiswa[nomor[k]].cetak();
        } else {
            pasca[nomor[k]].cetak();
        }
        cout << endl;
    }
    cout << "Orang: " << Orang::banyak << ", dosen: " << nd << ", mahasiswa: " << nm + np << ", pascasarjana: " << np << endl;''', "string"), wajib=W,
     id=("Sistem Akademik", "Susun hierarki orang-orang di kampus:\n\n"
         "- `Orang` — atribut `protected` `string nama`; `static int banyak` yang naik setiap ada orang terdaftar; `void cetak() const` mencetak nama (tanpa pindah baris);\n"
         "- `Dosen` turunan `Orang` — `int nidn`, `int bimbingan`; `cetak()`: nama, lalu ` (dosen, NIDN <nidn>), membimbing <k>`;\n"
         "- `Mahasiswa` turunan `Orang` — `int nim`; `cetak()`: nama, lalu ` (mahasiswa, NIM <nim>)`;\n"
         "- `Pascasarjana` turunan **`Mahasiswa`** — `Dosen *pembimbing` (asosiasi 0..1); `cetak()`: seperti mahasiswa, lalu `, pascasarjana, pembimbing <nama dosen atau ->`.\n\n"
         "Tiap `cetak()` memanggil `cetak()` induk **langsungnya** lebih dulu.\n\n"
         "Perintah:\n\n- `dosen <nama> <nidn>`, `mahasiswa <nama> <nim>`, `pasca <nama> <nim>` — mendaftarkan orang (tidak mencetak apa-apa);\n"
         "- `bimbing <nama pasca> <nama dosen>` — diperiksa berurutan: nama pertama bukan mahasiswa pascasarjana terdaftar, atau nama kedua bukan dosen terdaftar → `Tidak ditemukan`; mahasiswa itu sudah punya pembimbing → `Sudah punya pembimbing`; dosen itu sudah membimbing 2 orang → `Dosen penuh`; selain itu → `<pasca> dibimbing <dosen>`;\n"
         "- `selesai` — cetak semua orang **menurut urutan pendaftaran**, satu per baris, lalu ringkasan `Orang: <n>, dosen: <d>, mahasiswa: <m>, pascasarjana: <p>`. Mahasiswa pascasarjana **juga** mahasiswa.\n\n"
         "Contoh: `dosen Sari 1111`, `pasca Andi 2222`, `mahasiswa Budi 3333`, `bimbing Andi Sari`, `bimbing Budi Sari`, `selesai` →\n\n```\nAndi dibimbing Sari\nTidak ditemukan\nSari (dosen, NIDN 1111), membimbing 1\nAndi (mahasiswa, NIM 2222), pascasarjana, pembimbing Sari\nBudi (mahasiswa, NIM 3333)\nOrang: 3, dosen: 1, mahasiswa: 2, pascasarjana: 1\n```" + W_ID,
         "Beberapa baris perintah, diakhiri `selesai`.", "Satu baris per perintah `bimbing`, lalu daftar orang dan ringkasan.", "Paling banyak 16 perintah; paling banyak 12 orang; semua nama berbeda; nomor empat digit."),
     en=("Academic System", "Build the hierarchy of people on a campus:\n\n"
         "- `Orang` (person) — `protected` attribute `string nama`; `static int banyak`, which rises whenever a person is registered; `void cetak() const` prints the name (without a line break);\n"
         "- `Dosen` (lecturer) derived from `Orang` — `int nidn`, `int bimbingan` (students supervised); `cetak()`: the name, then ` (dosen, NIDN <nidn>), membimbing <k>`;\n"
         "- `Mahasiswa` (student) derived from `Orang` — `int nim`; `cetak()`: the name, then ` (mahasiswa, NIM <nim>)`;\n"
         "- `Pascasarjana` (postgraduate) derived from **`Mahasiswa`** — `Dosen *pembimbing` (supervisor; association 0..1); `cetak()`: as for a student, then `, pascasarjana, pembimbing <lecturer's name or ->`.\n\n"
         "Each `cetak()` first calls the `cetak()` of its **direct** base.\n\n"
         "Commands:\n\n- `dosen <name> <nidn>`, `mahasiswa <name> <nim>`, `pasca <name> <nim>` — register a person (print nothing);\n"
         "- `bimbing <postgraduate's name> <lecturer's name>` (supervise) — checked in order: the first name is not a registered postgraduate, or the second is not a registered lecturer → `Tidak ditemukan` (not found); the student already has a supervisor → `Sudah punya pembimbing`; the lecturer already supervises 2 → `Dosen penuh` (lecturer full); otherwise → `<postgraduate> dibimbing <lecturer>`;\n"
         "- `selesai` — print everybody **in order of registration**, one per line, then the summary `Orang: <n>, dosen: <d>, mahasiswa: <m>, pascasarjana: <p>`. A postgraduate is **also** a student.\n\n"
         "Example: `dosen Sari 1111`, `pasca Andi 2222`, `mahasiswa Budi 3333`, `bimbing Andi Sari`, `bimbing Budi Sari`, `selesai` →\n\n```\nAndi dibimbing Sari\nTidak ditemukan\nSari (dosen, NIDN 1111), membimbing 1\nAndi (mahasiswa, NIM 2222), pascasarjana, pembimbing Sari\nBudi (mahasiswa, NIM 3333)\nOrang: 3, dosen: 1, mahasiswa: 2, pascasarjana: 1\n```" + W_EN,
         "Several command lines, ending with `selesai`.", "One line per `bimbing` command, then the list of people and the summary.", "At most 16 commands; at most 12 people; all names differ; numbers have four digits."),
     ar=("النظام الأكاديمي", "ابنِ تسلسل الأشخاص في الجامعة:\n\n"
         "- `Orang` (شخص) — الخاصية المحمية `string nama`؛ و `static int banyak` الذي يزيد كلما سُجِّل شخص؛ و `void cetak() const` تطبع الاسم (بلا سطر جديد)؛\n"
         "- `Dosen` (محاضر) المشتق من `Orang` — `int nidn` و `int bimbingan` (عدد من يشرف عليهم)؛ و `cetak()`: الاسم ثم ` (dosen, NIDN <nidn>), membimbing <k>`؛\n"
         "- `Mahasiswa` (طالب) المشتق من `Orang` — `int nim`؛ و `cetak()`: الاسم ثم ` (mahasiswa, NIM <nim>)`؛\n"
         "- `Pascasarjana` (طالب دراسات عليا) المشتق من **`Mahasiswa`** — `Dosen *pembimbing` (المشرف؛ ارتباط 0..1)؛ و `cetak()`: كما للطالب ثم `, pascasarjana, pembimbing <اسم المحاضر أو ->`.\n\n"
         "كل `cetak()` تستدعي أولًا `cetak()` أساسها **المباشر**.\n\n"
         "الأوامر:\n\n- `dosen <الاسم> <nidn>` و `mahasiswa <الاسم> <nim>` و `pasca <الاسم> <nim>` — تسجّل شخصًا (ولا تطبع شيئًا)؛\n"
         "- `bimbing <اسم طالب الدراسات العليا> <اسم المحاضر>` (أشرِف) — يُفحص بالترتيب: الاسم الأول ليس طالب دراسات عليا مسجَّلًا، أو الثاني ليس محاضرًا مسجَّلًا ← `Tidak ditemukan` (غير موجود)؛ للطالب مشرف أصلًا ← `Sudah punya pembimbing`؛ المحاضر يشرف على اثنين ← `Dosen penuh` (المحاضر مكتمل)؛ وإلا ← `<الطالب> dibimbing <المحاضر>`؛\n"
         "- `selesai` — اطبع الجميع **بترتيب التسجيل**، واحدًا في كل سطر، ثم الملخص `Orang: <n>, dosen: <d>, mahasiswa: <m>, pascasarjana: <p>`. وطالب الدراسات العليا طالب **أيضًا**.\n\n"
         "مثال: `dosen Sari 1111` ثم `pasca Andi 2222` ثم `mahasiswa Budi 3333` ثم `bimbing Andi Sari` ثم `bimbing Budi Sari` ثم `selesai` ←\n\n```\nAndi dibimbing Sari\nTidak ditemukan\nSari (dosen, NIDN 1111), membimbing 1\nAndi (mahasiswa, NIM 2222), pascasarjana, pembimbing Sari\nBudi (mahasiswa, NIM 3333)\nOrang: 3, dosen: 1, mahasiswa: 2, pascasarjana: 1\n```" + W_AR,
         "عدة أسطر أوامر تنتهي بـ `selesai`.", "سطر لكل أمر `bimbing` ثم قائمة الأشخاص والملخص.", "16 أمرًا على الأكثر؛ 12 شخصًا على الأكثر؛ كل الأسماء مختلفة؛ والأرقام من أربعة أرقام."))

soal("robot-bertingkat", "sulit", "trimmed",
     "print(r.randint(30, 150), r.randint(5, 30))\nfor _ in range(r.randint(1, 16)):\n    q = r.random()\n    if q < 0.55:\n        print('maju', r.randint(1, 15))\n    elif q < 0.8:\n        print('angkat', r.randint(1, 35))\n    else:\n        print('taruh')\nprint('selesai')\n",
     program(r'''
class Robot {
protected:
    int posisi = 0;
    int baterai;

public:
    Robot(int b) : baterai(b) {
    }

    bool gerak(int langkah, int biaya) {
        if (langkah * biaya > baterai) {
            return false;
        }
        baterai -= langkah * biaya;
        posisi += langkah;
        return true;
    }

    bool maju(int langkah) {
        return gerak(langkah, 1);
    }

    int getPosisi() const {
        return posisi;
    }

    int getBaterai() const {
        return baterai;
    }
};

class RobotPengangkut : public Robot {
protected:
    int kapasitas;
    int muatan = 0;

public:
    RobotPengangkut(int b, int k) : Robot(b), kapasitas(k) {
    }

    bool angkat(int berat) {
        if (muatan > 0 || berat > kapasitas) {
            return false;
        }
        muatan = berat;
        return true;
    }

    int taruh() {
        auto berat = muatan;
        muatan = 0;
        return berat;
    }

    bool maju(int langkah) {
        if (muatan == 0) {
            return Robot::maju(langkah);
        }
        return gerak(langkah, 2);
    }
};

class RobotHemat : public RobotPengangkut {
private:
    int ditolak = 0;

public:
    RobotHemat(int b, int k) : RobotPengangkut(b, k) {
    }

    bool maju(int langkah) {
        auto biaya = 1;
        if (muatan > 0) {
            biaya = 2;
        }
        if (baterai - langkah * biaya < 10) {
            ditolak++;
            return false;
        }
        return RobotPengangkut::maju(langkah);
    }

    int getDitolak() const {
        return ditolak;
    }
};''', r'''    int baterai, kapasitas;
    cin >> baterai >> kapasitas;
    RobotHemat robot(baterai, kapasitas);
    auto terantar = 0;
    string perintah;
    while (cin >> perintah) {
        if (perintah == "selesai") {
            break;
        }
        if (perintah == "taruh") {
            auto berat = robot.taruh();
            if (berat == 0) {
                cout << "Tidak membawa apa-apa" << endl;
            } else {
                terantar += berat;
                cout << "Menaruh " << berat << " kg di posisi " << robot.getPosisi() << endl;
            }
            continue;
        }
        int x;
        cin >> x;
        if (perintah == "angkat") {
            if (robot.angkat(x)) {
                cout << "Mengangkat " << x << " kg" << endl;
            } else {
                cout << "Tidak bisa mengangkat" << endl;
            }
        } else if (robot.maju(x)) {
            cout << "Posisi " << robot.getPosisi() << ", baterai " << robot.getBaterai() << endl;
        } else {
            cout << "Baterai dijaga" << endl;
        }
    }
    cout << "Terantar " << terantar << " kg, ditolak " << robot.getDitolak() << " kali" << endl;''', "string"), wajib=W,
     id=("Robot Bertingkat", "Tiga generasi robot, masing-masing mewarisi generasi sebelumnya dan **mengganti cara `maju`**:\n\n"
         "**`Robot`** — atribut `protected` `int posisi` (mula-mula 0), `int baterai`. `bool gerak(int langkah, int biaya)` memakai baterai sebesar langkah × biaya dan menambah posisi; gagal (tanpa perubahan) bila baterai kurang. `bool maju(int langkah)` adalah `gerak(langkah, 1)`.\n\n"
         "**`RobotPengangkut`** turunan `Robot` — atribut `int kapasitas`, `int muatan` (mula-mula 0).\n- `bool angkat(int berat)` — gagal bila sedang membawa muatan atau berat melebihi kapasitas;\n- `int taruh()` — melepas muatan dan mengembalikan beratnya (0 bila tidak membawa apa-apa);\n- `bool maju(int langkah)` — tanpa muatan: `Robot::maju(langkah)`; dengan muatan: `gerak(langkah, 2)`.\n\n"
         "**`RobotHemat`** turunan `RobotPengangkut` — `bool maju(int langkah)` menolak bergerak bila sesudahnya baterai akan **kurang dari 10**, dan menghitung penolakan itu (`int ditolak`); selain itu ia memanggil `RobotPengangkut::maju(langkah)`.\n\n"
         "Program memakai satu `RobotHemat`. Masukan: baterai dan kapasitas; lalu perintah:\n\n"
         "- `maju <langkah>` → `Posisi <p>, baterai <b>`, atau `Baterai dijaga`;\n- `angkat <berat>` → `Mengangkat <berat> kg`, atau `Tidak bisa mengangkat`;\n- `taruh` → `Menaruh <berat> kg di posisi <p>`, atau `Tidak membawa apa-apa`;\n- `selesai` → `Terantar <jumlah kg yang ditaruh> kg, ditolak <k> kali`.\n\n"
         "Contoh: `40 20`; `maju 5`, `angkat 25`, `angkat 10`, `maju 10`, `maju 8`, `taruh`, `selesai` →\n\n```\nPosisi 5, baterai 35\nTidak bisa mengangkat\nMengangkat 10 kg\nPosisi 15, baterai 15\nBaterai dijaga\nMenaruh 10 kg di posisi 15\nTerantar 10 kg, ditolak 1 kali\n```" + W_ID,
         "Baris pertama: baterai dan kapasitas. Lalu perintah-perintah, diakhiri `selesai`.", "Satu baris per perintah, lalu ringkasan.", "30 ≤ baterai ≤ 150; 5 ≤ kapasitas ≤ 30; paling banyak 16 perintah; 1 ≤ langkah ≤ 15; 1 ≤ berat ≤ 35"),
     en=("Robots in Generations", "Three generations of robot, each inheriting the one before and **replacing the way it advances** (`maju`):\n\n"
         "**`Robot`** — `protected` attributes `int posisi` (0 at first), `int baterai`. `bool gerak(int langkah, int biaya)` (move: steps, cost) uses steps × cost of battery and raises the position; it fails (changing nothing) when the battery is short. `bool maju(int langkah)` is `gerak(langkah, 1)`.\n\n"
         "**`RobotPengangkut`** (carrier) derived from `Robot` — attributes `int kapasitas`, `int muatan` (load, 0 at first).\n- `bool angkat(int berat)` (lift) — fails when it already carries a load or the weight exceeds the capacity;\n- `int taruh()` (put down) — drops the load and returns its weight (0 when carrying nothing);\n- `bool maju(int langkah)` — without a load: `Robot::maju(langkah)`; with a load: `gerak(langkah, 2)`.\n\n"
         "**`RobotHemat`** (thrifty) derived from `RobotPengangkut` — `bool maju(int langkah)` refuses to move when the battery would afterwards be **below 10**, and counts such refusals (`int ditolak`); otherwise it calls `RobotPengangkut::maju(langkah)`.\n\n"
         "The program uses one `RobotHemat`. Input: the battery and the capacity; then commands:\n\n"
         "- `maju <steps>` → `Posisi <p>, baterai <b>`, or `Baterai dijaga` (battery kept);\n- `angkat <weight>` → `Mengangkat <weight> kg`, or `Tidak bisa mengangkat` (cannot lift);\n- `taruh` → `Menaruh <weight> kg di posisi <p>`, or `Tidak membawa apa-apa` (carrying nothing);\n- `selesai` → `Terantar <kilograms put down> kg, ditolak <k> kali` (delivered …, refused … times).\n\n"
         "Example: `40 20`; `maju 5`, `angkat 25`, `angkat 10`, `maju 10`, `maju 8`, `taruh`, `selesai` →\n\n```\nPosisi 5, baterai 35\nTidak bisa mengangkat\nMengangkat 10 kg\nPosisi 15, baterai 15\nBaterai dijaga\nMenaruh 10 kg di posisi 15\nTerantar 10 kg, ditolak 1 kali\n```" + W_EN,
         "First line: the battery and the capacity. Then the commands, ending with `selesai`.", "One line per command, then the summary.", "30 ≤ battery ≤ 150; 5 ≤ capacity ≤ 30; at most 16 commands; 1 ≤ steps ≤ 15; 1 ≤ weight ≤ 35"),
     ar=("روبوتات بأجيال", "ثلاثة أجيال من الروبوتات، كل جيل يرث سابقه و**يستبدل طريقة التقدّم** (`maju`):\n\n"
         "**`Robot`** — الخاصيتان المحميتان `int posisi` (يبدأ من 0) و `int baterai`. الطريقة `bool gerak(int langkah, int biaya)` (تحرَّك: الخطوات، الكلفة) تستهلك من البطارية الخطوات × الكلفة وتزيد الموضع؛ وتفشل (بلا أي تغيير) إذا نقصت البطارية. و `bool maju(int langkah)` هي `gerak(langkah, 1)`.\n\n"
         "**`RobotPengangkut`** (ناقل) المشتق من `Robot` — الخاصيتان `int kapasitas` و `int muatan` (الحمولة، وتبدأ من 0).\n- `bool angkat(int berat)` (ارفع) — تفشل إذا كان يحمل حمولة أو زاد الوزن على السعة؛\n- `int taruh()` (ضع) — تُنزل الحمولة وتُرجع وزنها (0 إذا لم يحمل شيئًا)؛\n- `bool maju(int langkah)` — بلا حمولة: `Robot::maju(langkah)`؛ ومع حمولة: `gerak(langkah, 2)`.\n\n"
         "**`RobotHemat`** (مقتصد) المشتق من `RobotPengangkut` — `bool maju(int langkah)` ترفض الحركة إذا كانت البطارية ستصير بعدها **أقل من 10**، وتعدّ هذه الرفضات (`int ditolak`)؛ وإلا استدعت `RobotPengangkut::maju(langkah)`.\n\n"
         "يستعمل البرنامج `RobotHemat` واحدًا. المدخل: البطارية والسعة؛ ثم أوامر:\n\n"
         "- `maju <الخطوات>` ← `Posisi <p>, baterai <b>` أو `Baterai dijaga` (حُفظت البطارية)؛\n- `angkat <الوزن>` ← `Mengangkat <الوزن> kg` أو `Tidak bisa mengangkat` (لا يستطيع الرفع)؛\n- `taruh` ← `Menaruh <الوزن> kg di posisi <p>` أو `Tidak membawa apa-apa` (لا يحمل شيئًا)؛\n- `selesai` ← `Terantar <الكيلوغرامات الموضوعة> kg, ditolak <k> kali` (سُلِّم …، رُفض … مرة).\n\n"
         "مثال: `40 20`؛ `maju 5` ثم `angkat 25` ثم `angkat 10` ثم `maju 10` ثم `maju 8` ثم `taruh` ثم `selesai` ←\n\n```\nPosisi 5, baterai 35\nTidak bisa mengangkat\nMengangkat 10 kg\nPosisi 15, baterai 15\nBaterai dijaga\nMenaruh 10 kg di posisi 15\nTerantar 10 kg, ditolak 1 kali\n```" + W_AR,
         "السطر الأول: البطارية والسعة. ثم الأوامر منتهيةً بـ `selesai`.", "سطر لكل أمر ثم الملخص.", "30 ≤ البطارية ≤ 150؛ 5 ≤ السعة ≤ 30؛ 16 أمرًا على الأكثر؛ 1 ≤ الخطوات ≤ 15؛ 1 ≤ الوزن ≤ 35"))

soal("pustaka-media", "sulit", "trimmed",
     "JUDUL = ['Arus', 'Bumi', 'Cahaya', 'Dermaga', 'Embun', 'Fajar', 'Gerimis', 'Hutan', 'Ilalang', 'Jejak', 'Kabut', 'Langit']\njudul = r.sample(JUDUL, 12)\nk = 0\nada = []\nfor _ in range(r.randint(2, 16)):\n    q = r.random()\n    if q < 0.65 and k < 12:\n        j = r.choice(['buku', 'film', 'seri'])\n        v = [judul[k], r.randint(1990, 2026)]\n        v.append(r.randint(5, 80) * 10 if j == 'buku' else r.randint(40, 180))\n        if j == 'seri':\n            v.append(r.randint(2, 24))\n        print(j, *v)\n        ada.append(judul[k])\n        k += 1\n    else:\n        print('hapus', r.choice(ada) if ada and r.random() < 0.8 else 'Zaman')\nprint('selesai')\n",
     program(r'''
class Media {
protected:
    string judul;
    int tahun = 0;
    bool aktif = false;

public:
    void isiDasar(const string &j, int t) {
        judul = j;
        tahun = t;
        aktif = true;
    }

    string getJudul() const {
        return judul;
    }

    int getTahun() const {
        return tahun;
    }

    bool masihAda() const {
        return aktif;
    }

    void hapus() {
        aktif = false;
    }

    void cetak() const {
        cout << judul << " (" << tahun << ")";
    }
};

class Buku : public Media {
private:
    int halaman = 0;

public:
    void isi(const string &j, int t, int h) {
        isiDasar(j, t);
        halaman = h;
    }

    int menit() const {
        return halaman * 2;
    }

    void cetak() const {
        Media::cetak();
        cout << ", " << halaman << " halaman";
    }
};

class Film : public Media {
protected:
    int durasi = 0;

public:
    void isi(const string &j, int t, int d) {
        isiDasar(j, t);
        durasi = d;
    }

    int menit() const {
        return durasi;
    }

    void cetak() const {
        Media::cetak();
        cout << ", " << durasi << " menit";
    }
};

class FilmSeri : public Film {
private:
    int episode = 0;

public:
    void isi(const string &j, int t, int d, int e) {
        Film::isi(j, t, d);
        episode = e;
    }

    int menit() const {
        return Film::menit() * episode;
    }

    void cetak() const {
        Film::cetak();
        cout << " x " << episode << " episode";
    }
};''', r'''    Buku buku[12];
    Film film[12];
    FilmSeri seri[12];
    auto nb = 0;
    auto nf = 0;
    auto ns = 0;
    string perintah;
    while (cin >> perintah) {
        if (perintah == "selesai") {
            break;
        }
        string judul;
        cin >> judul;
        if (perintah == "hapus") {
            auto ketemu = false;
            for (int k = 0; k < nb; k++) {
                if (buku[k].masihAda() && buku[k].getJudul() == judul) {
                    buku[k].hapus();
                    ketemu = true;
                }
            }
            for (int k = 0; k < nf; k++) {
                if (film[k].masihAda() && film[k].getJudul() == judul) {
                    film[k].hapus();
                    ketemu = true;
                }
            }
            for (int k = 0; k < ns; k++) {
                if (seri[k].masihAda() && seri[k].getJudul() == judul) {
                    seri[k].hapus();
                    ketemu = true;
                }
            }
            if (ketemu) {
                cout << "Dihapus: " << judul << endl;
            } else {
                cout << "Tidak ada: " << judul << endl;
            }
            continue;
        }
        int tahun, a;
        cin >> tahun >> a;
        if (perintah == "buku") {
            buku[nb].isi(judul, tahun, a);
            nb++;
        } else if (perintah == "film") {
            film[nf].isi(judul, tahun, a);
            nf++;
        } else {
            int episode;
            cin >> episode;
            seri[ns].isi(judul, tahun, a, episode);
            ns++;
        }
    }
    auto total = 0;
    cout << "Buku:" << endl;
    for (int k = 0; k < nb; k++) {
        if (buku[k].masihAda()) {
            buku[k].cetak();
            cout << endl;
            total += buku[k].menit();
        }
    }
    cout << "Film:" << endl;
    for (int k = 0; k < nf; k++) {
        if (film[k].masihAda()) {
            film[k].cetak();
            cout << endl;
            total += film[k].menit();
        }
    }
    cout << "Seri:" << endl;
    for (int k = 0; k < ns; k++) {
        if (seri[k].masihAda()) {
            seri[k].cetak();
            cout << endl;
            total += seri[k].menit();
        }
    }
    cout << "Total: " << total / 60 << " jam " << total % 60 << " menit" << endl;''', "string"), wajib=W,
     id=("Pustaka Media", "Sebuah pustaka pribadi menyimpan buku, film, dan film seri. Susun hierarkinya:\n\n"
         "- `Media` — atribut `protected` `string judul`, `int tahun`; `void cetak() const` mencetak `<judul> (<tahun>)` tanpa pindah baris;\n"
         "- `Buku` turunan `Media` — `int halaman`; `cetak()` menambah `, <h> halaman`; `int menit() const` = halaman × 2 (perkiraan waktu baca);\n"
         "- `Film` turunan `Media` — `int durasi`; `cetak()` menambah `, <d> menit`; `menit()` = durasi;\n"
         "- `FilmSeri` turunan **`Film`** — `int episode`; `cetak()` menambah ` x <e> episode` di belakang cetakan film; `menit()` = `Film::menit()` × episode.\n\n"
         "Perintah:\n\n- `buku <judul> <tahun> <halaman>`;\n- `film <judul> <tahun> <durasi>`;\n- `seri <judul> <tahun> <durasi per episode> <episode>`;\n"
         "- `hapus <judul>` → `Dihapus: <judul>`, atau `Tidak ada: <judul>`;\n- `selesai`.\n\n"
         "Di akhir cetak tiga bagian — `Buku:`, `Film:`, `Seri:` — masing-masing diikuti isinya yang masih ada, satu per baris, menurut urutan ditambahkan. Lalu cetak `Total: <j> jam <m> menit`: jumlah `menit()` semua media yang masih ada.\n\n"
         "Contoh: `buku Arus 2001 150`, `seri Kabut 2020 45 8`, `film Bumi 1999 120`, `hapus Bumi`, `hapus Bumi`, `selesai` →\n\n```\nDihapus: Bumi\nTidak ada: Bumi\nBuku:\nArus (2001), 150 halaman\nFilm:\nSeri:\nKabut (2020), 45 menit x 8 episode\nTotal: 11 jam 0 menit\n```" + W_ID,
         "Beberapa baris perintah, diakhiri `selesai`.", "Satu baris per perintah `hapus`, lalu tiga bagian daftar dan satu baris total.", "Paling banyak 16 perintah; semua judul berbeda; halaman 50–800; durasi 40–180; episode 2–24"),
     en=("Media Library", "A private library keeps books, films and series. Build the hierarchy:\n\n"
         "- `Media` — `protected` attributes `string judul`, `int tahun` (year); `void cetak() const` prints `<title> (<year>)` without a line break;\n"
         "- `Buku` (book) derived from `Media` — `int halaman` (pages); `cetak()` adds `, <h> halaman`; `int menit() const` (minutes) = pages × 2 (an estimate of reading time);\n"
         "- `Film` derived from `Media` — `int durasi`; `cetak()` adds `, <d> menit`; `menit()` = duration;\n"
         "- `FilmSeri` (series) derived from **`Film`** — `int episode`; `cetak()` adds ` x <e> episode` after the film's print; `menit()` = `Film::menit()` × episodes.\n\n"
         "Commands:\n\n- `buku <title> <year> <pages>`;\n- `film <title> <year> <duration>`;\n- `seri <title> <year> <duration per episode> <episodes>`;\n"
         "- `hapus <title>` → `Dihapus: <title>` (removed), or `Tidak ada: <title>` (not there);\n- `selesai`.\n\n"
         "At the end print three sections — `Buku:`, `Film:`, `Seri:` — each followed by its entries still present, one per line, in the order they were added. Then print `Total: <h> jam <m> menit`: the summed `menit()` of all media still present.\n\n"
         "Example: `buku Arus 2001 150`, `seri Kabut 2020 45 8`, `film Bumi 1999 120`, `hapus Bumi`, `hapus Bumi`, `selesai` →\n\n```\nDihapus: Bumi\nTidak ada: Bumi\nBuku:\nArus (2001), 150 halaman\nFilm:\nSeri:\nKabut (2020), 45 menit x 8 episode\nTotal: 11 jam 0 menit\n```" + W_EN,
         "Several command lines, ending with `selesai`.", "One line per `hapus` command, then the three list sections and one total line.", "At most 16 commands; all titles differ; pages 50–800; duration 40–180; episodes 2–24"),
     ar=("مكتبة الوسائط", "مكتبة خاصة تحفظ كتبًا وأفلامًا ومسلسلات. ابنِ التسلسل:\n\n"
         "- `Media` — الخاصيتان المحميتان `string judul` و `int tahun` (السنة)؛ و `void cetak() const` تطبع `<العنوان> (<السنة>)` بلا سطر جديد؛\n"
         "- `Buku` (كتاب) المشتق من `Media` — `int halaman` (الصفحات)؛ و `cetak()` تضيف `, <h> halaman`؛ و `int menit() const` (الدقائق) = الصفحات × 2 (تقدير لزمن القراءة)؛\n"
         "- `Film` المشتق من `Media` — `int durasi`؛ و `cetak()` تضيف `, <d> menit`؛ و `menit()` = المدة؛\n"
         "- `FilmSeri` (مسلسل) المشتق من **`Film`** — `int episode`؛ و `cetak()` تضيف ` x <e> episode` بعد طباعة الفيلم؛ و `menit()` = `Film::menit()` × الحلقات.\n\n"
         "الأوامر:\n\n- `buku <العنوان> <السنة> <الصفحات>`؛\n- `film <العنوان> <السنة> <المدة>`؛\n- `seri <العنوان> <السنة> <مدة الحلقة> <الحلقات>`؛\n"
         "- `hapus <العنوان>` ← `Dihapus: <العنوان>` (حُذف) أو `Tidak ada: <العنوان>` (غير موجود)؛\n- `selesai`.\n\n"
         "في النهاية اطبع ثلاثة أقسام — `Buku:` و `Film:` و `Seri:` — يلي كلًّا منها ما بقي من محتواه، واحدًا في كل سطر، بترتيب الإضافة. ثم اطبع `Total: <h> jam <m> menit`: مجموع `menit()` لكل الوسائط الباقية.\n\n"
         "مثال: `buku Arus 2001 150` ثم `seri Kabut 2020 45 8` ثم `film Bumi 1999 120` ثم `hapus Bumi` ثم `hapus Bumi` ثم `selesai` ←\n\n```\nDihapus: Bumi\nTidak ada: Bumi\nBuku:\nArus (2001), 150 halaman\nFilm:\nSeri:\nKabut (2020), 45 menit x 8 episode\nTotal: 11 jam 0 menit\n```" + W_AR,
         "عدة أسطر أوامر تنتهي بـ `selesai`.", "سطر لكل أمر `hapus` ثم أقسام القوائم الثلاثة وسطر المجموع.", "16 أمرًا على الأكثر؛ كل العناوين مختلفة؛ الصفحات 50–800؛ المدة 40–180؛ الحلقات 2–24"))

tulis(__file__, "PBO301", 6, "cpp", {"mudah_sekali": 8, "mudah": 8, "mudah_sedang": 6, "sedang": 6, "sulit": 4})
