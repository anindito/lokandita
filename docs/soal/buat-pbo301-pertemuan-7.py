#!/usr/bin/env python3
"""Menyusun docs/soal/pbo301-pertemuan-7.json.

PBO301 pertemuan 7 — Polymorphism dan Virtual Functions. Konsep yang diizinkan
di produksi: 21 konsep dasar, MODERN_CPP, CLASS_OBJECT, CONSTRUCTOR_DESTRUCTOR,
COPY_SEMANTICS, ENCAPSULATION, KOMPOSISI_AGREGASI, INHERITANCE, POLYMORPHISM.

Yang BELUM diizinkan dan tidak dipakai solusi referensi: fungsi virtual murni
(`= 0`, ABSTRACT_CLASS, pertemuan 9) — setiap class induk di sini punya
perilaku bawaan; kontainer STL (pertemuan 13) — objek polimorfik disimpan di
larik pointer induk dengan `new` / `delete`; operator overloading; exception.

Yang dibuat terlihat di keluaran: pemanggilan lewat pointer / referensi induk
mengenai method turunan; beda method virtual dan biasa; destruktor virtual dan
urutannya; irisan objek (slicing); pemanggilan virtual di dalam konstruktor;
`Induk::method()` dari dalam override; `final`; `dynamic_cast`; pola yang
bertumpu pada polimorfisme (template method, pabrik objek, perintah yang bisa
dibatalkan, komposit, pohon ekspresi).

Setiap soal mensyaratkan CLASS_OBJECT, INHERITANCE dan POLYMORPHISM di kode
mahasiswa. Pemeriksaannya: ada class dengan daftar induk dan ada fungsi anggota
`virtual` / `override` / `final`. Apakah pemanggilannya benar-benar lewat
pointer induk tidak diperiksa judge.

Dilewati dari silabus pertemuan 7 (tidak bisa dinilai dari keluaran): notasi
UML; cara kerja vtable dan biaya pemanggilan virtual; kasus "tidak terkompilasi"
(`override` tanpa method induk, menimpa method `final`).

    python3 docs/soal/buat-pbo301-pertemuan-7.py
"""
from bangun import program, soal, tulis

W = ("CLASS_OBJECT", "INHERITANCE", "POLYMORPHISM")
W_ID = "\n\n**Kode harus memuat class turunan dan fungsi anggota `virtual`**: keluaran yang benar tanpa itu hanya mendapat setengah nilai."
W_EN = "\n\n**The code must contain a derived class and a `virtual` member function**: correct output without them gets only half the marks."
W_AR = "\n\n**يجب أن يحتوي الكود على صنف مشتق ودالة عضو `virtual`**: المخرج الصحيح بدونهما ينال نصف الدرجة فقط."

NAMA = "NAMA = ['Andi', 'Budi', 'Citra', 'Dewi', 'Eko', 'Fajar', 'Gita', 'Hadi', 'Indah', 'Joko', 'Kartika', 'Lina']\n"
HEWAN = "HEWAN = ['Belang', 'Oyen', 'Hitam', 'Putih', 'Bruno', 'Molly', 'Rex', 'Kopi', 'Kiki', 'Momo', 'Cemong', 'Gembul']\n"
SATUAN = "SATUAN = ['Alfa', 'Bravo', 'Charlie', 'Delta', 'Elang', 'Garuda', 'Harimau', 'Rajawali', 'Banteng', 'Macan', 'Kobra', 'Hiu']\n"

# Hierarki bentuk yang dipakai beberapa soal (luas bilangan bulat).
BENTUK = r'''
class Bentuk {
public:
    virtual ~Bentuk() {
    }

    virtual string nama() const {
        return "bentuk";
    }

    virtual int luas() const {
        return 0;
    }
};

class Persegi : public Bentuk {
private:
    int sisi;

public:
    Persegi(int s) : sisi(s) {
    }

    string nama() const override {
        return "persegi";
    }

    int luas() const override {
        return sisi * sisi;
    }
};

class PersegiPanjang : public Bentuk {
private:
    int panjang;
    int lebar;

public:
    PersegiPanjang(int p, int l) : panjang(p), lebar(l) {
    }

    string nama() const override {
        return "persegi panjang";
    }

    int luas() const override {
        return panjang * lebar;
    }
};

class Segitiga : public Bentuk {
private:
    int alas;
    int tinggi;

public:
    Segitiga(int a, int t) : alas(a), tinggi(t) {
    }

    string nama() const override {
        return "segitiga";
    }

    int luas() const override {
        return alas * tinggi / 2;
    }
};

Bentuk *bacaBentuk() {
    string jenis;
    cin >> jenis;
    if (jenis == "persegi") {
        int s;
        cin >> s;
        return new Persegi(s);
    }
    int a, b;
    cin >> a >> b;
    if (jenis == "panjang") {
        return new PersegiPanjang(a, b);
    }
    return new Segitiga(a, b);
}'''

GEN_BENTUK = '''n = {2: 1}.get(i, r.randint(2, 8))
print(n)
for _ in range(n):
    j = r.choice(['persegi', 'panjang', 'segitiga'])
    if j == 'persegi':
        print(j, r.randint(1, 30))
    elif j == 'panjang':
        print(j, r.randint(1, 30), r.randint(1, 30))
    else:
        print(j, r.randint(1, 15) * 2, r.randint(1, 30))
'''

# ============================== MUDAH SEKALI (8) ==============================

soal("suara-hewan", "mudah_sekali", "trimmed", HEWAN + "print(['kucing', 'anjing', 'hewan'][i % 3], r.choice(HEWAN))\n",
     program(r'''
class Hewan {
public:
    virtual ~Hewan() {
    }

    virtual string suara() const {
        return "...";
    }
};

class Kucing : public Hewan {
public:
    string suara() const override {
        return "meong";
    }
};

class Anjing : public Hewan {
public:
    string suara() const override {
        return "guk";
    }
};

void bunyikan(const string &nama, const Hewan &h) {
    cout << nama << ": " << h.suara() << endl;
}''', r'''    string jenis, nama;
    cin >> jenis >> nama;
    if (jenis == "kucing") {
        Kucing k;
        bunyikan(nama, k);
    } else if (jenis == "anjing") {
        Anjing a;
        bunyikan(nama, a);
    } else {
        Hewan h;
        bunyikan(nama, h);
    }''', "string"), wajib=W,
     id=("Suara Hewan", "Pada pertemuan lalu, fungsi yang menerima `const Hewan &` selalu memanggil `Hewan::suara()`, apa pun objek yang dikirim. Kata **`virtual`** mengubahnya: method yang dipanggil ditentukan oleh **objek sebenarnya**, bukan oleh tipe yang tertulis.\n\n"
         "Buat class `Hewan` dengan `virtual string suara() const` yang mengembalikan `...`. Buat turunannya `Kucing` (`meong`) dan `Anjing` (`guk`); tandai method penggantinya dengan `override`.\n\n"
         "Tulis **satu** fungsi `void bunyikan(const string &nama, const Hewan &h)` yang mencetak `<nama>: <h.suara()>`.\n\n"
         "Baca jenis (`kucing`, `anjing`, atau `hewan`) dan nama; buat objeknya dan kirim ke `bunyikan`.\n\nContoh: `kucing Oyen` → `Oyen: meong`; `hewan Koko` → `Koko: ...`." + W_ID,
         "Satu baris berisi jenis dan nama.", "Satu baris.", "Jenis `kucing`, `anjing`, atau `hewan`; nama satu kata."),
     en=("Animal Sounds", "In the last meeting a function taking `const Hewan &` always called `Hewan::suara()`, whatever object was passed. The word **`virtual`** changes that: the method called is decided by the **actual object**, not by the type written in the code.\n\n"
         "Make a class `Hewan` (animal) with `virtual string suara() const` (sound) returning `...`. Make its derived classes `Kucing` (cat, `meong`) and `Anjing` (dog, `guk`); mark the replacing methods with `override`.\n\n"
         "Write **one** function `void bunyikan(const string &nama, const Hewan &h)` that prints `<name>: <h.suara()>`.\n\n"
         "Read the kind (`kucing`, `anjing` or `hewan`) and a name; create the object and pass it to `bunyikan`.\n\nExamples: `kucing Oyen` → `Oyen: meong`; `hewan Koko` → `Koko: ...`." + W_EN,
         "One line with the kind and the name.", "One line.", "The kind is `kucing`, `anjing` or `hewan`; the name is one word."),
     ar=("أصوات الحيوانات", "في اللقاء الماضي كانت الدالة التي تستقبل `const Hewan &` تستدعي `Hewan::suara()` دائمًا مهما كان الكائن المرسَل. الكلمة **`virtual`** تغيّر ذلك: الطريقة المستدعاة يحددها **الكائن الفعلي** لا النوع المكتوب.\n\n"
         "أنشئ صنفًا `Hewan` (حيوان) فيه `virtual string suara() const` (الصوت) تُرجع `...`. أنشئ مشتقَّيه `Kucing` (قطة، `meong`) و `Anjing` (كلب، `guk`)؛ وعلِّم الطريقتين البديلتين بـ `override`.\n\n"
         "اكتب دالة **واحدة** `void bunyikan(const string &nama, const Hewan &h)` تطبع `<الاسم>: <h.suara()>`.\n\n"
         "اقرأ النوع (`kucing` أو `anjing` أو `hewan`) واسمًا؛ أنشئ الكائن ومرِّره إلى `bunyikan`.\n\nأمثلة: `kucing Oyen` ← `Oyen: meong`؛ `hewan Koko` ← `Koko: ...`." + W_AR,
         "سطر واحد فيه النوع والاسم.", "سطر واحد.", "النوع `kucing` أو `anjing` أو `hewan`؛ والاسم كلمة واحدة."))

soal("luas-lewat-pointer", "mudah_sekali", "trimmed", "if i % 2 == 0:\n    print('persegi', r.randint(1, 100))\nelse:\n    print('panjang', r.randint(1, 100), r.randint(1, 100))\n",
     program(r'''
class Bentuk {
public:
    virtual ~Bentuk() {
    }

    virtual int luas() const {
        return 0;
    }
};

class Persegi : public Bentuk {
private:
    int sisi;

public:
    Persegi(int s) : sisi(s) {
    }

    int luas() const override {
        return sisi * sisi;
    }
};

class PersegiPanjang : public Bentuk {
private:
    int panjang;
    int lebar;

public:
    PersegiPanjang(int p, int l) : panjang(p), lebar(l) {
    }

    int luas() const override {
        return panjang * lebar;
    }
};''', r'''    string jenis;
    cin >> jenis;
    Bentuk *bentuk = nullptr;
    if (jenis == "persegi") {
        int s;
        cin >> s;
        bentuk = new Persegi(s);
    } else {
        int p, l;
        cin >> p >> l;
        bentuk = new PersegiPanjang(p, l);
    }
    cout << bentuk->luas() << endl;
    delete bentuk;''', "string"), wajib=W,
     id=("Luas lewat Pointer Induk", "Sebuah pointer bertipe `Bentuk *` boleh menunjuk objek turunan mana pun. Bila `luas()` virtual, `bentuk->luas()` menjalankan versi milik objek yang sedang ditunjuk.\n\n"
         "Buat class `Bentuk` dengan `virtual int luas() const` (mengembalikan 0) dan **destruktor virtual**. Buat turunannya `Persegi` (atribut `sisi`) dan `PersegiPanjang` (atribut `panjang`, `lebar`), masing-masing menimpa `luas()`.\n\n"
         "Di `main`, sediakan **satu** variabel `Bentuk *bentuk`. Isi dengan `new Persegi(s)` atau `new PersegiPanjang(p, l)` menurut masukan, cetak `bentuk->luas()`, lalu `delete bentuk`.\n\nContoh: `persegi 7` → 49; `panjang 3 5` → 15." + W_ID,
         "Satu baris: `persegi <sisi>` atau `panjang <panjang> <lebar>`.", "Satu bilangan bulat: luas.", "Tiap ukuran antara 1 dan 100"),
     en=("Area through a Base Pointer", "A pointer of type `Bentuk *` may point to an object of any derived class. If `luas()` is virtual, `bentuk->luas()` runs the version that belongs to the object pointed to.\n\n"
         "Make a class `Bentuk` (shape) with `virtual int luas() const` (area, returning 0) and a **virtual destructor**. Make its derived classes `Persegi` (square; attribute `sisi`) and `PersegiPanjang` (rectangle; attributes `panjang`, `lebar`), each overriding `luas()`.\n\n"
         "In `main` keep **one** variable `Bentuk *bentuk`. Fill it with `new Persegi(s)` or `new PersegiPanjang(p, l)` according to the input, print `bentuk->luas()`, then `delete bentuk`.\n\nExamples: `persegi 7` → 49; `panjang 3 5` → 15." + W_EN,
         "One line: `persegi <side>` or `panjang <length> <width>`.", "One integer: the area.", "Each size is between 1 and 100"),
     ar=("المساحة عبر مؤشر الأساس", "المؤشر من النوع `Bentuk *` يجوز أن يشير إلى كائن من أي صنف مشتق. وإذا كانت `luas()` افتراضية فإن `bentuk->luas()` تنفّذ نسخة الكائن المشار إليه.\n\n"
         "أنشئ صنفًا `Bentuk` (شكل) فيه `virtual int luas() const` (المساحة، تُرجع 0) و**هادم افتراضي**. أنشئ مشتقَّيه `Persegi` (مربع؛ الخاصية `sisi`) و `PersegiPanjang` (مستطيل؛ الخاصيتان `panjang` و `lebar`)، وكلٌّ منهما يعيد تعريف `luas()`.\n\n"
         "في `main` اجعل متغيرًا **واحدًا** `Bentuk *bentuk`. املأه بـ `new Persegi(s)` أو `new PersegiPanjang(p, l)` بحسب المدخل، واطبع `bentuk->luas()` ثم `delete bentuk`.\n\nأمثلة: `persegi 7` ← 49؛ `panjang 3 5` ← 15." + W_AR,
         "سطر واحد: `persegi <الضلع>` أو `panjang <الطول> <العرض>`.", "عدد صحيح واحد: المساحة.", "كل قياس بين 1 و 100"))

soal("virtual-atau-biasa", "mudah_sekali", "trimmed", SATUAN + "print(['tank', 'infanteri', 'artileri'][i % 3], r.choice(SATUAN))\n",
     program(r'''
class Unit {
public:
    virtual ~Unit() {
    }

    string namaBiasa() const {
        return "Unit";
    }

    virtual string namaVirtual() const {
        return "Unit";
    }
};

class Tank : public Unit {
public:
    string namaBiasa() const {
        return "Tank";
    }

    string namaVirtual() const override {
        return "Tank";
    }
};

class Infanteri : public Unit {
public:
    string namaBiasa() const {
        return "Infanteri";
    }

    string namaVirtual() const override {
        return "Infanteri";
    }
};

class Artileri : public Unit {
public:
    string namaBiasa() const {
        return "Artileri";
    }

    string namaVirtual() const override {
        return "Artileri";
    }
};''', r'''    string jenis, nama;
    cin >> jenis >> nama;
    Unit *unit = nullptr;
    if (jenis == "tank") {
        unit = new Tank;
    } else if (jenis == "infanteri") {
        unit = new Infanteri;
    } else {
        unit = new Artileri;
    }
    cout << nama << " biasa: " << unit->namaBiasa() << endl;
    cout << nama << " virtual: " << unit->namaVirtual() << endl;
    delete unit;''', "string"), wajib=W,
     id=("Virtual atau Biasa", "Dua method yang tampak sama bisa berperilaku berbeda bila dipanggil lewat pointer induk: method **biasa** dipilih menurut tipe pointer, method **virtual** menurut objeknya.\n\n"
         "Buat class `Unit` dengan dua method yang sama-sama mengembalikan `Unit`: `string namaBiasa() const` (tanpa `virtual`) dan `virtual string namaVirtual() const`.\n\n"
         "Buat turunannya `Tank`, `Infanteri`, dan `Artileri`. Masing-masing mendefinisikan **kedua** method itu dan mengembalikan nama class-nya sendiri.\n\n"
         "Baca jenis (`tank`, `infanteri`, `artileri`) dan nama satuan, buat objeknya dengan `new` dan simpan di `Unit *`. Lewat pointer itu cetak `<nama> biasa: <namaBiasa()>` lalu `<nama> virtual: <namaVirtual()>`.\n\nUntuk `tank Garuda`:\n\n```\nGaruda biasa: Unit\nGaruda virtual: Tank\n```" + W_ID,
         "Satu baris berisi jenis satuan dan namanya.", "Dua baris seperti contoh.", "Jenis `tank`, `infanteri`, atau `artileri`; nama satu kata."),
     en=("Virtual or Ordinary", "Two methods that look alike can behave differently when called through a base pointer: an **ordinary** method is chosen by the type of the pointer, a **virtual** one by the object.\n\n"
         "Make a class `Unit` with two methods that both return `Unit`: `string namaBiasa() const` (without `virtual`) and `virtual string namaVirtual() const`.\n\n"
         "Make its derived classes `Tank`, `Infanteri` and `Artileri`. Each defines **both** methods and returns its own class name.\n\n"
         "Read the kind (`tank`, `infanteri`, `artileri`) and the name of the unit, create the object with `new` and keep it in a `Unit *`. Through that pointer print `<name> biasa: <namaBiasa()>` and then `<name> virtual: <namaVirtual()>`.\n\nFor `tank Garuda`:\n\n```\nGaruda biasa: Unit\nGaruda virtual: Tank\n```" + W_EN,
         "One line with the kind of unit and its name.", "Two lines as in the example.", "The kind is `tank`, `infanteri` or `artileri`; the name is one word."),
     ar=("افتراضية أم عادية", "طريقتان متشابهتان قد تختلفان إذا استُدعيتا عبر مؤشر الأساس: الطريقة **العادية** تُختار بحسب نوع المؤشر، و**الافتراضية** بحسب الكائن.\n\n"
         "أنشئ صنفًا `Unit` فيه طريقتان تُرجعان كلتاهما `Unit`: `string namaBiasa() const` (بلا `virtual`) و `virtual string namaVirtual() const`.\n\n"
         "أنشئ مشتقاته `Tank` و `Infanteri` و `Artileri`. كلٌّ منها يعرّف الطريقتين **كلتيهما** ويُرجع اسم صنفه.\n\n"
         "اقرأ النوع (`tank` أو `infanteri` أو `artileri`) واسم الوحدة وأنشئ الكائن بـ `new` واحفظه في `Unit *`. عبر ذلك المؤشر اطبع `<الاسم> biasa: <namaBiasa()>` ثم `<الاسم> virtual: <namaVirtual()>`.\n\nعند `tank Garuda`:\n\n```\nGaruda biasa: Unit\nGaruda virtual: Tank\n```" + W_AR,
         "سطر واحد فيه نوع الوحدة واسمها.", "سطران كما في المثال.", "النوع `tank` أو `infanteri` أو `artileri`؛ والاسم كلمة واحدة."))

soal("destruktor-virtual", "mudah_sekali", "trimmed", SATUAN + "print(r.choice(SATUAN[:6]) if i % 2 == 0 else r.choice(SATUAN[6:]))\n",
     program(r'''
class Unit {
protected:
    string nama;

public:
    Unit(const string &n) : nama(n) {
        cout << "+Unit " << nama << endl;
    }

    virtual ~Unit() {
        cout << "-Unit " << nama << endl;
    }
};

class Infanteri : public Unit {
public:
    Infanteri(const string &n) : Unit(n) {
        cout << "+Infanteri " << nama << endl;
    }

    ~Infanteri() override {
        cout << "-Infanteri " << nama << endl;
    }
};''', r'''    string nama;
    cin >> nama;
    Unit *unit = new Infanteri(nama);
    cout << "Bertugas" << endl;
    delete unit;''', "string"), wajib=W,
     id=("Destruktor Virtual", "Bila objek turunan dihapus lewat pointer induk — `delete unit;` dengan `unit` bertipe `Unit *` — destruktor turunan hanya ikut dipanggil kalau destruktor induk **virtual**. Karena itu class yang dimaksudkan menjadi induk polimorfik selalu diberi `virtual ~Induk()`.\n\n"
         "Buat class `Unit` (atribut `protected` `string nama`): konstruktor mencetak `+Unit <nama>`, **destruktor virtual** mencetak `-Unit <nama>`. Buat turunannya `Infanteri`: `+Infanteri <nama>` / `-Infanteri <nama>`.\n\n"
         "Baca nama. Jalankan `Unit *unit = new Infanteri(nama);`, cetak `Bertugas`, lalu `delete unit;`.\n\nUntuk `Garuda`:\n\n```\n+Unit Garuda\n+Infanteri Garuda\nBertugas\n-Infanteri Garuda\n-Unit Garuda\n```" + W_ID,
         "Satu kata: nama satuan.", "Lima baris seperti contoh.", "Nama paling panjang 20 huruf."),
     en=("Virtual Destructor", "When a derived object is deleted through a base pointer — `delete unit;` with `unit` of type `Unit *` — the derived destructor is called only if the base destructor is **virtual**. That is why a class meant to be a polymorphic base always gets `virtual ~Base()`.\n\n"
         "Make a class `Unit` (`protected` attribute `string nama`): the constructor prints `+Unit <name>`, the **virtual destructor** prints `-Unit <name>`. Make its derived class `Infanteri`: `+Infanteri <name>` / `-Infanteri <name>`.\n\n"
         "Read a name. Run `Unit *unit = new Infanteri(nama);`, print `Bertugas` (on duty), then `delete unit;`.\n\nFor `Garuda`:\n\n```\n+Unit Garuda\n+Infanteri Garuda\nBertugas\n-Infanteri Garuda\n-Unit Garuda\n```" + W_EN,
         "One word: the name of the unit.", "Five lines as in the example.", "The name has at most 20 letters."),
     ar=("الهادم الافتراضي", "إذا حُذف كائن مشتق عبر مؤشر الأساس — `delete unit;` و `unit` من النوع `Unit *` — فلا يُستدعى هادم المشتق إلا إذا كان هادم الأساس **افتراضيًا**. لذلك يُعطى كل صنف يُراد أن يكون أساسًا متعدد الأشكال `virtual ~Base()` دائمًا.\n\n"
         "أنشئ صنفًا `Unit` (الخاصية المحمية `string nama`): الباني يطبع `+Unit <الاسم>` و**الهادم الافتراضي** يطبع `-Unit <الاسم>`. أنشئ مشتقه `Infanteri`: `+Infanteri <الاسم>` / `-Infanteri <الاسم>`.\n\n"
         "اقرأ اسمًا. نفّذ `Unit *unit = new Infanteri(nama);` واطبع `Bertugas` (في الخدمة) ثم `delete unit;`.\n\nعند `Garuda`:\n\n```\n+Unit Garuda\n+Infanteri Garuda\nBertugas\n-Infanteri Garuda\n-Unit Garuda\n```" + W_AR,
         "كلمة واحدة: اسم الوحدة.", "خمسة أسطر كما في المثال.", "الاسم 20 حرفًا على الأكثر."))

soal("salam-bahasa", "mudah_sekali", "trimmed", NAMA + "print(['id', 'en', 'ar'][i % 3], r.choice(NAMA))\n",
     program(r'''
class Penyapa {
public:
    virtual ~Penyapa() {
    }

    virtual string salam() const {
        return "Halo";
    }
};

class PenyapaInggris : public Penyapa {
public:
    string salam() const override {
        return "Hello";
    }
};

class PenyapaArab : public Penyapa {
public:
    string salam() const override {
        return "Marhaban";
    }
};

void sapa(const Penyapa &p, const string &nama) {
    cout << p.salam() << ", " << nama << "!" << endl;
}''', r'''    string kode, nama;
    cin >> kode >> nama;
    if (kode == "en") {
        PenyapaInggris p;
        sapa(p, nama);
    } else if (kode == "ar") {
        PenyapaArab p;
        sapa(p, nama);
    } else {
        Penyapa p;
        sapa(p, nama);
    }''', "string"), wajib=W,
     id=("Salam Tiga Bahasa", "Buat class `Penyapa` dengan `virtual string salam() const` yang mengembalikan `Halo`. Buat turunannya `PenyapaInggris` (`Hello`) dan `PenyapaArab` (`Marhaban`).\n\n"
         "Tulis satu fungsi `void sapa(const Penyapa &p, const string &nama)` yang mencetak `<salam>, <nama>!`. Fungsi itu tidak tahu, dan tidak perlu tahu, bahasa apa yang sedang dipakai.\n\n"
         "Baca kode bahasa (`id`, `en`, atau `ar`) dan nama.\n\nContoh: `en Budi` → `Hello, Budi!`; `id Citra` → `Halo, Citra!`." + W_ID,
         "Satu baris berisi kode bahasa dan nama.", "Satu baris.", "Kode `id`, `en`, atau `ar`; nama satu kata."),
     en=("Greeting in Three Languages", "Make a class `Penyapa` (greeter) with `virtual string salam() const` (greeting) returning `Halo`. Make its derived classes `PenyapaInggris` (`Hello`) and `PenyapaArab` (`Marhaban`).\n\n"
         "Write one function `void sapa(const Penyapa &p, const string &nama)` that prints `<greeting>, <name>!`. The function does not know, and need not know, which language is in use.\n\n"
         "Read the language code (`id`, `en` or `ar`) and a name.\n\nExamples: `en Budi` → `Hello, Budi!`; `id Citra` → `Halo, Citra!`." + W_EN,
         "One line with the language code and the name.", "One line.", "The code is `id`, `en` or `ar`; the name is one word."),
     ar=("تحية بثلاث لغات", "أنشئ صنفًا `Penyapa` (مُحيٍّ) فيه `virtual string salam() const` (التحية) تُرجع `Halo`. أنشئ مشتقَّيه `PenyapaInggris` (`Hello`) و `PenyapaArab` (`Marhaban`).\n\n"
         "اكتب دالة واحدة `void sapa(const Penyapa &p, const string &nama)` تطبع `<التحية>, <الاسم>!`. الدالة لا تعرف — ولا يلزمها أن تعرف — أي لغة تُستعمل.\n\n"
         "اقرأ رمز اللغة (`id` أو `en` أو `ar`) واسمًا.\n\nأمثلة: `en Budi` ← `Hello, Budi!`؛ `id Citra` ← `Halo, Citra!`." + W_AR,
         "سطر واحد فيه رمز اللغة والاسم.", "سطر واحد.", "الرمز `id` أو `en` أو `ar`؛ والاسم كلمة واحدة."))

soal("sajikan-minuman", "mudah_sekali", "trimmed", "print(['kopi', 'teh', 'air'][i % 3], r.randint(1, 9))\n",
     program(r'''
class Minuman {
public:
    virtual ~Minuman() {
    }

    virtual string nama() const {
        return "air putih";
    }

    void sajikan(int gelas) const {
        cout << "Menyajikan " << gelas << " gelas " << nama() << endl;
    }
};

class Kopi : public Minuman {
public:
    string nama() const override {
        return "kopi";
    }
};

class Teh : public Minuman {
public:
    string nama() const override {
        return "teh";
    }
};''', r'''    string jenis;
    int gelas;
    cin >> jenis >> gelas;
    Minuman *minuman = nullptr;
    if (jenis == "kopi") {
        minuman = new Kopi;
    } else if (jenis == "teh") {
        minuman = new Teh;
    } else {
        minuman = new Minuman;
    }
    minuman->sajikan(gelas);
    delete minuman;''', "string"), wajib=W,
     id=("Menyajikan Minuman", "Method induk yang **tidak** virtual boleh memanggil method virtual. Saat itu terjadi, yang berjalan tetap versi milik objek sebenarnya — sehingga satu method di induk bisa bekerja untuk semua turunan.\n\n"
         "Buat class `Minuman` dengan `virtual string nama() const` (mengembalikan `air putih`) dan method biasa `void sajikan(int gelas) const` yang mencetak `Menyajikan <gelas> gelas <nama()>`.\n\n"
         "Buat turunannya `Kopi` dan `Teh` yang **hanya** menimpa `nama()`. `sajikan` tidak ditulis ulang.\n\n"
         "Baca jenis (`kopi`, `teh`, atau `air`) dan banyak gelas; panggil `sajikan` lewat `Minuman *`.\n\nContoh: `kopi 2` → `Menyajikan 2 gelas kopi`; `air 1` → `Menyajikan 1 gelas air putih`." + W_ID,
         "Satu baris berisi jenis minuman dan banyak gelas.", "Satu baris.", "Jenis `kopi`, `teh`, atau `air`; 1 ≤ gelas ≤ 9"),
     en=("Serving Drinks", "A base method that is **not** virtual may call a virtual one. When it does, what runs is still the version of the actual object — so one method in the base can work for every derived class.\n\n"
         "Make a class `Minuman` (drink) with `virtual string nama() const` (returning `air putih`, plain water) and an ordinary method `void sajikan(int gelas) const` (serve) that prints `Menyajikan <glasses> gelas <nama()>`.\n\n"
         "Make its derived classes `Kopi` (coffee) and `Teh` (tea), which override **only** `nama()`. `sajikan` is not written again.\n\n"
         "Read the kind (`kopi`, `teh` or `air`) and the number of glasses; call `sajikan` through a `Minuman *`.\n\nExamples: `kopi 2` → `Menyajikan 2 gelas kopi`; `air 1` → `Menyajikan 1 gelas air putih`." + W_EN,
         "One line with the kind of drink and the number of glasses.", "One line.", "The kind is `kopi`, `teh` or `air`; 1 ≤ glasses ≤ 9"),
     ar=("تقديم المشروبات", "طريقة الأساس **غير** الافتراضية يجوز أن تستدعي طريقة افتراضية. وعندئذ تعمل نسخة الكائن الفعلي — فتخدم طريقة واحدة في الأساس كل المشتقات.\n\n"
         "أنشئ صنفًا `Minuman` (مشروب) فيه `virtual string nama() const` (تُرجع `air putih` أي ماء) وطريقة عادية `void sajikan(int gelas) const` (قدِّم) تطبع `Menyajikan <الأكواب> gelas <nama()>`.\n\n"
         "أنشئ مشتقَّيه `Kopi` (قهوة) و `Teh` (شاي) اللذين يعيدان تعريف `nama()` **فقط**. أما `sajikan` فلا تُكتب ثانية.\n\n"
         "اقرأ النوع (`kopi` أو `teh` أو `air`) وعدد الأكواب؛ واستدعِ `sajikan` عبر `Minuman *`.\n\nأمثلة: `kopi 2` ← `Menyajikan 2 gelas kopi`؛ `air 1` ← `Menyajikan 1 gelas air putih`." + W_AR,
         "سطر واحد فيه نوع المشروب وعدد الأكواب.", "سطر واحد.", "النوع `kopi` أو `teh` أو `air`؛ 1 ≤ الأكواب ≤ 9"))

soal("tarif-parkir", "mudah_sekali", "trimmed", "print(['mobil', 'motor', 'sepeda'][i % 3], {3: 1, 4: 24}.get(i, r.randint(1, 24)))\n",
     program(r'''
class Kendaraan {
public:
    virtual ~Kendaraan() {
    }

    virtual int tarif(int jam) const {
        return 1000 * jam;
    }
};

class Mobil : public Kendaraan {
public:
    int tarif(int jam) const override {
        return 5000 + 2000 * (jam - 1);
    }
};

class Motor : public Kendaraan {
public:
    int tarif(int jam) const override {
        auto biaya = 2000 * jam;
        if (biaya > 10000) {
            biaya = 10000;
        }
        return biaya;
    }
};''', r'''    string jenis;
    int jam;
    cin >> jenis >> jam;
    Kendaraan *kendaraan = nullptr;
    if (jenis == "mobil") {
        kendaraan = new Mobil;
    } else if (jenis == "motor") {
        kendaraan = new Motor;
    } else {
        kendaraan = new Kendaraan;
    }
    cout << kendaraan->tarif(jam) << endl;
    delete kendaraan;''', "string"), wajib=W,
     id=("Tarif Parkir", "Buat class `Kendaraan` dengan `virtual int tarif(int jam) const`. Tarif bawaannya 1000 per jam — itulah tarif sepeda.\n\nDua turunannya menimpa `tarif`:\n\n"
         "- `Mobil` — 5000 untuk jam pertama, 2000 untuk tiap jam berikutnya;\n- `Motor` — 2000 per jam, tetapi paling banyak 10000.\n\n"
         "Baca jenis (`mobil`, `motor`, atau `sepeda`) dan lama parkir dalam jam. Buat objeknya di balik `Kendaraan *` dan cetak tarifnya.\n\nContoh: `mobil 3` → 9000; `motor 8` → 10000; `sepeda 4` → 4000." + W_ID,
         "Satu baris berisi jenis kendaraan dan lama parkir (jam).", "Satu bilangan bulat: tarif.", "1 ≤ jam ≤ 24"),
     en=("Parking Fee", "Make a class `Kendaraan` (vehicle) with `virtual int tarif(int jam) const` (fee for a number of hours). The default fee is 1000 per hour — that is the fee for a bicycle.\n\nTwo derived classes override `tarif`:\n\n"
         "- `Mobil` (car) — 5000 for the first hour, 2000 for every further hour;\n- `Motor` (motorcycle) — 2000 per hour, but at most 10000.\n\n"
         "Read the kind (`mobil`, `motor` or `sepeda`) and the parking time in hours. Create the object behind a `Kendaraan *` and print its fee.\n\nExamples: `mobil 3` → 9000; `motor 8` → 10000; `sepeda 4` → 4000." + W_EN,
         "One line with the kind of vehicle and the parking time (hours).", "One integer: the fee.", "1 ≤ hours ≤ 24"),
     ar=("رسم الوقوف", "أنشئ صنفًا `Kendaraan` (مركبة) فيه `virtual int tarif(int jam) const` (الرسم لعدد من الساعات). الرسم الافتراضي 1000 للساعة — وهو رسم الدراجة الهوائية.\n\nمشتقان يعيدان تعريف `tarif`:\n\n"
         "- `Mobil` (سيارة) — 5000 للساعة الأولى و 2000 لكل ساعة تالية؛\n- `Motor` (دراجة نارية) — 2000 للساعة، على ألا يتجاوز 10000.\n\n"
         "اقرأ النوع (`mobil` أو `motor` أو `sepeda`) ومدة الوقوف بالساعات. أنشئ الكائن خلف `Kendaraan *` واطبع رسمه.\n\nأمثلة: `mobil 3` ← 9000؛ `motor 8` ← 10000؛ `sepeda 4` ← 4000." + W_AR,
         "سطر واحد فيه نوع المركبة ومدة الوقوف (بالساعات).", "عدد صحيح واحد: الرسم.", "1 ≤ الساعات ≤ 24"))

soal("catatan-penting", "mudah_sekali", "trimmed", "print('biasa' if i % 2 == 0 else 'penting', r.choice(['rapat', 'belanja', 'ujian', 'servis', 'arisan', 'kuliah', 'olahraga', 'tagihan']))\n",
     program(r'''
class Catatan {
public:
    virtual ~Catatan() {
    }

    virtual void tulis(const string &pesan) const {
        cout << "Catatan: " << pesan << endl;
    }
};

class CatatanPenting : public Catatan {
public:
    void tulis(const string &pesan) const override {
        cout << "PENTING!" << endl;
        Catatan::tulis(pesan);
    }
};''', r'''    string jenis, pesan;
    cin >> jenis >> pesan;
    Catatan *catatan = nullptr;
    if (jenis == "penting") {
        catatan = new CatatanPenting;
    } else {
        catatan = new Catatan;
    }
    catatan->tulis(pesan);
    delete catatan;''', "string"), wajib=W,
     id=("Catatan Penting", "Method pengganti (`override`) tidak harus membuang pekerjaan induknya: ia boleh **menambah** sesuatu lalu memanggil versi induk dengan `Catatan::tulis(pesan)`.\n\n"
         "Buat class `Catatan` dengan `virtual void tulis(const string &pesan) const` yang mencetak `Catatan: <pesan>`.\n\n"
         "Buat turunannya `CatatanPenting` yang menimpa `tulis`: cetak dulu `PENTING!`, lalu panggil versi induk.\n\n"
         "Baca jenis (`biasa` atau `penting`) dan satu kata pesan. Buat objeknya di balik `Catatan *` dan panggil `tulis`.\n\nUntuk `penting rapat`:\n\n```\nPENTING!\nCatatan: rapat\n```\n\nUntuk `biasa belanja` keluarannya hanya `Catatan: belanja`." + W_ID,
         "Satu baris berisi jenis catatan dan pesannya.", "Satu atau dua baris.", "Jenis `biasa` atau `penting`; pesan satu kata."),
     en=("An Important Note", "An overriding method need not throw away the work of its base: it may **add** something and then call the base version with `Catatan::tulis(pesan)`.\n\n"
         "Make a class `Catatan` (note) with `virtual void tulis(const string &pesan) const` (write) that prints `Catatan: <message>`.\n\n"
         "Make its derived class `CatatanPenting` (important note), which overrides `tulis`: first print `PENTING!`, then call the base version.\n\n"
         "Read the kind (`biasa`, ordinary, or `penting`) and a one-word message. Create the object behind a `Catatan *` and call `tulis`.\n\nFor `penting rapat`:\n\n```\nPENTING!\nCatatan: rapat\n```\n\nFor `biasa belanja` the output is only `Catatan: belanja`." + W_EN,
         "One line with the kind of note and its message.", "One or two lines.", "The kind is `biasa` or `penting`; the message is one word."),
     ar=("ملاحظة مهمة", "الطريقة البديلة (`override`) لا يلزمها أن تُلغي عمل أساسها: يجوز أن **تضيف** شيئًا ثم تستدعي نسخة الأساس بـ `Catatan::tulis(pesan)`.\n\n"
         "أنشئ صنفًا `Catatan` (ملاحظة) فيه `virtual void tulis(const string &pesan) const` (اكتب) تطبع `Catatan: <الرسالة>`.\n\n"
         "أنشئ مشتقه `CatatanPenting` (ملاحظة مهمة) الذي يعيد تعريف `tulis`: اطبع أولًا `PENTING!` ثم استدعِ نسخة الأساس.\n\n"
         "اقرأ النوع (`biasa` أي عادية، أو `penting`) ورسالة من كلمة واحدة. أنشئ الكائن خلف `Catatan *` واستدعِ `tulis`.\n\nعند `penting rapat`:\n\n```\nPENTING!\nCatatan: rapat\n```\n\nوعند `biasa belanja` يكون المخرج `Catatan: belanja` فقط." + W_AR,
         "سطر واحد فيه نوع الملاحظة ورسالتها.", "سطر أو سطران.", "النوع `biasa` أو `penting`؛ والرسالة كلمة واحدة."))

# ================================= MUDAH (8) =================================

soal("larik-bentuk", "mudah", "trimmed", GEN_BENTUK,
     program(BENTUK, r'''    int n;
    cin >> n;
    Bentuk *daftar[8];
    for (int k = 0; k < n; k++) {
        daftar[k] = bacaBentuk();
    }
    auto total = 0;
    for (int k = 0; k < n; k++) {
        cout << daftar[k]->nama() << ": " << daftar[k]->luas() << endl;
        total += daftar[k]->luas();
    }
    cout << "Total: " << total << endl;
    for (int k = 0; k < n; k++) {
        delete daftar[k];
    }''', "string"), wajib=W,
     id=("Larik Bentuk", "Kekuatan polimorfisme terlihat saat objek-objek **berlainan jenis** disimpan bersama dan diperlakukan sama. Larik `Bentuk *daftar[8]` bisa memuat persegi, persegi panjang, dan segitiga sekaligus; satu perulangan cukup untuk semuanya.\n\n"
         "Buat class `Bentuk` dengan `virtual string nama() const` dan `virtual int luas() const`, serta destruktor virtual. Buat turunannya:\n\n"
         "- `Persegi` (sisi) — nama `persegi`;\n- `PersegiPanjang` (panjang, lebar) — nama `persegi panjang`;\n- `Segitiga` (alas, tinggi) — nama `segitiga`, luas alas × tinggi / 2.\n\n"
         "Masukan: n bentuk, satu per baris: `persegi <sisi>`, `panjang <panjang> <lebar>`, atau `segitiga <alas> <tinggi>`. Simpan semuanya di larik pointer induk. Cetak `<nama>: <luas>` untuk tiap bentuk, lalu `Total: T`. Jangan lupa `delete`.\n\n"
         "Contoh: `persegi 4`, `panjang 2 5`, `segitiga 6 3` →\n\n```\npersegi: 16\npersegi panjang: 10\nsegitiga: 9\nTotal: 35\n```" + W_ID,
         "Baris pertama: n. Lalu n baris bentuk.", "Satu baris per bentuk, lalu `Total: T`.", "1 ≤ n ≤ 8; tiap ukuran antara 1 dan 30; alas segitiga selalu genap"),
     en=("An Array of Shapes", "The strength of polymorphism shows when objects of **different kinds** are kept together and treated alike. An array `Bentuk *daftar[8]` can hold squares, rectangles and triangles at once; one loop serves them all.\n\n"
         "Make a class `Bentuk` with `virtual string nama() const` and `virtual int luas() const`, and a virtual destructor. Make its derived classes:\n\n"
         "- `Persegi` (side) — name `persegi`;\n- `PersegiPanjang` (length, width) — name `persegi panjang`;\n- `Segitiga` (triangle; base, height) — name `segitiga`, area base × height / 2.\n\n"
         "Input: n shapes, one per line: `persegi <side>`, `panjang <length> <width>`, or `segitiga <base> <height>`. Keep them all in an array of base pointers. Print `<name>: <area>` for each shape, then `Total: T`. Do not forget `delete`.\n\n"
         "Example: `persegi 4`, `panjang 2 5`, `segitiga 6 3` →\n\n```\npersegi: 16\npersegi panjang: 10\nsegitiga: 9\nTotal: 35\n```" + W_EN,
         "First line: n. Then n shape lines.", "One line per shape, then `Total: T`.", "1 ≤ n ≤ 8; each size is between 1 and 30; the base of a triangle is always even"),
     ar=("مصفوفة أشكال", "تظهر قوة تعدد الأشكال حين تُحفظ كائنات **مختلفة الأنواع** معًا وتُعامل معاملة واحدة. المصفوفة `Bentuk *daftar[8]` تسع مربعات ومستطيلات ومثلثات معًا؛ وحلقة واحدة تكفيها جميعًا.\n\n"
         "أنشئ صنفًا `Bentuk` فيه `virtual string nama() const` و `virtual int luas() const` وهادم افتراضي. أنشئ مشتقاته:\n\n"
         "- `Persegi` (الضلع) — الاسم `persegi`؛\n- `PersegiPanjang` (الطول، العرض) — الاسم `persegi panjang`؛\n- `Segitiga` (مثلث؛ القاعدة، الارتفاع) — الاسم `segitiga` والمساحة القاعدة × الارتفاع / 2.\n\n"
         "المدخل: n شكلًا، واحد في كل سطر: `persegi <الضلع>` أو `panjang <الطول> <العرض>` أو `segitiga <القاعدة> <الارتفاع>`. احفظها كلها في مصفوفة مؤشرات الأساس. اطبع `<الاسم>: <المساحة>` لكل شكل ثم `Total: T`. ولا تنسَ `delete`.\n\n"
         "مثال: `persegi 4` و `panjang 2 5` و `segitiga 6 3` ←\n\n```\npersegi: 16\npersegi panjang: 10\nsegitiga: 9\nTotal: 35\n```" + W_AR,
         "السطر الأول: n. ثم n سطر أشكال.", "سطر لكل شكل ثم `Total: T`.", "1 ≤ n ≤ 8؛ كل قياس بين 1 و 30؛ قاعدة المثلث زوجية دائمًا"))

soal("irisan-objek", "mudah", "trimmed", HEWAN + "print('kucing' if i % 2 == 0 else 'anjing', r.choice(HEWAN))\n",
     program(r'''
class Hewan {
protected:
    string nama;

public:
    Hewan(const string &n) : nama(n) {
    }

    virtual ~Hewan() {
    }

    string getNama() const {
        return nama;
    }

    virtual string suara() const {
        return "...";
    }
};

class Kucing : public Hewan {
public:
    Kucing(const string &n) : Hewan(n) {
    }

    string suara() const override {
        return "meong";
    }
};

class Anjing : public Hewan {
public:
    Anjing(const string &n) : Hewan(n) {
    }

    string suara() const override {
        return "guk";
    }
};

void lewatNilai(Hewan h) {
    cout << h.getNama() << " lewat nilai: " << h.suara() << endl;
}

void lewatRujukan(const Hewan &h) {
    cout << h.getNama() << " lewat rujukan: " << h.suara() << endl;
}''', r'''    string jenis, nama;
    cin >> jenis >> nama;
    if (jenis == "kucing") {
        Kucing hewan(nama);
        lewatNilai(hewan);
        lewatRujukan(hewan);
    } else {
        Anjing hewan(nama);
        lewatNilai(hewan);
        lewatRujukan(hewan);
    }''', "string"), wajib=W,
     id=("Irisan Objek", "Polimorfisme hanya bekerja lewat **pointer atau referensi**. Bila objek turunan dikirim **sebagai nilai** ke parameter bertipe induk, yang disalin hanyalah bagian induknya — bagian turunan \"teriris\" (*object slicing*), dan salinan itu benar-benar sebuah `Hewan`.\n\n"
         "Buat class `Hewan` (atribut `string nama`; `getNama()`; `virtual string suara() const` → `...`) dan turunannya `Kucing` (`meong`) dan `Anjing` (`guk`).\n\nTulis dua fungsi:\n\n"
         "- `void lewatNilai(Hewan h)` — mencetak `<nama> lewat nilai: <h.suara()>`;\n- `void lewatRujukan(const Hewan &h)` — mencetak `<nama> lewat rujukan: <h.suara()>`.\n\n"
         "Baca jenis (`kucing` atau `anjing`) dan nama. Buat objeknya dan kirim objek yang sama ke kedua fungsi.\n\nUntuk `kucing Oyen`:\n\n```\nOyen lewat nilai: ...\nOyen lewat rujukan: meong\n```" + W_ID,
         "Satu baris berisi jenis hewan dan namanya.", "Dua baris seperti contoh.", "Jenis `kucing` atau `anjing`; nama satu kata."),
     en=("Object Slicing", "Polymorphism only works through a **pointer or a reference**. When a derived object is passed **by value** to a parameter of the base type, only its base part is copied — the derived part is sliced off (*object slicing*), and that copy really is a `Hewan`.\n\n"
         "Make a class `Hewan` (attribute `string nama`; `getNama()`; `virtual string suara() const` → `...`) and its derived classes `Kucing` (`meong`) and `Anjing` (`guk`).\n\nWrite two functions:\n\n"
         "- `void lewatNilai(Hewan h)` (by value) — prints `<name> lewat nilai: <h.suara()>`;\n- `void lewatRujukan(const Hewan &h)` (by reference) — prints `<name> lewat rujukan: <h.suara()>`.\n\n"
         "Read the kind (`kucing` or `anjing`) and a name. Create the object and pass the same object to both functions.\n\nFor `kucing Oyen`:\n\n```\nOyen lewat nilai: ...\nOyen lewat rujukan: meong\n```" + W_EN,
         "One line with the kind of animal and its name.", "Two lines as in the example.", "The kind is `kucing` or `anjing`; the name is one word."),
     ar=("تقطيع الكائن", "تعدد الأشكال لا يعمل إلا عبر **مؤشر أو مرجع**. فإذا مُرِّر كائن مشتق **بالقيمة** إلى وسيط من نوع الأساس نُسخ جزء الأساس فقط — ويُقطع جزء المشتق (*object slicing*)، وتلك النسخة `Hewan` حقًّا.\n\n"
         "أنشئ صنفًا `Hewan` (الخاصية `string nama`؛ و `getNama()`؛ و `virtual string suara() const` ← `...`) ومشتقَّيه `Kucing` (`meong`) و `Anjing` (`guk`).\n\nاكتب دالتين:\n\n"
         "- `void lewatNilai(Hewan h)` (بالقيمة) — تطبع `<الاسم> lewat nilai: <h.suara()>`؛\n- `void lewatRujukan(const Hewan &h)` (بالمرجع) — تطبع `<الاسم> lewat rujukan: <h.suara()>`.\n\n"
         "اقرأ النوع (`kucing` أو `anjing`) واسمًا. أنشئ الكائن ومرِّر الكائن نفسه إلى الدالتين.\n\nعند `kucing Oyen`:\n\n```\nOyen lewat nilai: ...\nOyen lewat rujukan: meong\n```" + W_AR,
         "سطر واحد فيه نوع الحيوان واسمه.", "سطران كما في المثال.", "النوع `kucing` أو `anjing`؛ والاسم كلمة واحدة."))

soal("hancur-berurutan", "mudah", "trimmed", SATUAN + "n = {2: 1}.get(i, r.randint(2, 6))\nprint(n)\nfor nama in r.sample(SATUAN, n):\n    print(r.choice(['tank', 'infanteri']), nama)\n",
     program(r'''
class Unit {
protected:
    string nama;

public:
    Unit(const string &n) : nama(n) {
        cout << "+Unit " << nama << endl;
    }

    virtual ~Unit() {
        cout << "-Unit " << nama << endl;
    }
};

class Tank : public Unit {
public:
    Tank(const string &n) : Unit(n) {
        cout << "+Tank " << nama << endl;
    }

    ~Tank() override {
        cout << "-Tank " << nama << endl;
    }
};

class Infanteri : public Unit {
public:
    Infanteri(const string &n) : Unit(n) {
        cout << "+Infanteri " << nama << endl;
    }

    ~Infanteri() override {
        cout << "-Infanteri " << nama << endl;
    }
};''', r'''    int n;
    cin >> n;
    Unit *pasukan[6];
    for (int k = 0; k < n; k++) {
        string jenis, nama;
        cin >> jenis >> nama;
        if (jenis == "tank") {
            pasukan[k] = new Tank(nama);
        } else {
            pasukan[k] = new Infanteri(nama);
        }
    }
    for (int k = n - 1; k >= 0; k--) {
        delete pasukan[k];
    }''', "string"), wajib=W,
     id=("Pasukan Dibubarkan", "Buat class `Unit` (atribut `protected` `string nama`; konstruktor mencetak `+Unit <nama>`; **destruktor virtual** mencetak `-Unit <nama>`) dan dua turunannya, `Tank` dan `Infanteri`, yang konstruktor dan destruktornya mencetak `+Tank <nama>` / `-Tank <nama>` dan `+Infanteri <nama>` / `-Infanteri <nama>`.\n\n"
         "Baca n satuan (jenis dan nama). Buat semuanya dengan `new`, simpan di larik `Unit *pasukan[6]` menurut urutan masukan. Lalu bubarkan dengan `delete` **dari yang terakhir dibuat**.\n\n"
         "Karena destruktornya virtual, `delete pasukan[k]` menjalankan destruktor turunan dulu, baru destruktor `Unit`.\n\n"
         "Contoh: `tank Alfa`, `infanteri Bravo` →\n\n```\n+Unit Alfa\n+Tank Alfa\n+Unit Bravo\n+Infanteri Bravo\n-Infanteri Bravo\n-Unit Bravo\n-Tank Alfa\n-Unit Alfa\n```" + W_ID,
         "Baris pertama: n. Lalu n baris: jenis dan nama.", "Empat baris per satuan.", "1 ≤ n ≤ 6; jenis `tank` atau `infanteri`; nama berbeda-beda."),
     en=("Disbanding the Troops", "Make a class `Unit` (`protected` attribute `string nama`; the constructor prints `+Unit <name>`; the **virtual destructor** prints `-Unit <name>`) and its two derived classes, `Tank` and `Infanteri`, whose constructors and destructors print `+Tank <name>` / `-Tank <name>` and `+Infanteri <name>` / `-Infanteri <name>`.\n\n"
         "Read n units (kind and name). Create them all with `new` and keep them in an array `Unit *pasukan[6]` in input order. Then disband them with `delete`, **starting from the one created last**.\n\n"
         "Because the destructor is virtual, `delete pasukan[k]` runs the derived destructor first and then the destructor of `Unit`.\n\n"
         "Example: `tank Alfa`, `infanteri Bravo` →\n\n```\n+Unit Alfa\n+Tank Alfa\n+Unit Bravo\n+Infanteri Bravo\n-Infanteri Bravo\n-Unit Bravo\n-Tank Alfa\n-Unit Alfa\n```" + W_EN,
         "First line: n. Then n lines: kind and name.", "Four lines per unit.", "1 ≤ n ≤ 6; the kind is `tank` or `infanteri`; all names differ."),
     ar=("حلّ القوات", "أنشئ صنفًا `Unit` (الخاصية المحمية `string nama`؛ الباني يطبع `+Unit <الاسم>`؛ و**الهادم الافتراضي** يطبع `-Unit <الاسم>`) ومشتقَّيه `Tank` و `Infanteri` اللذين تطبع بانياتهما وهادماتهما `+Tank <الاسم>` / `-Tank <الاسم>` و `+Infanteri <الاسم>` / `-Infanteri <الاسم>`.\n\n"
         "اقرأ n وحدة (النوع والاسم). أنشئها كلها بـ `new` واحفظها في المصفوفة `Unit *pasukan[6]` بترتيب المدخل. ثم حُلَّها بـ `delete` **بدءًا بآخر ما أُنشئ**.\n\n"
         "لأن الهادم افتراضي فإن `delete pasukan[k]` ينفّذ هادم المشتق أولًا ثم هادم `Unit`.\n\n"
         "مثال: `tank Alfa` و `infanteri Bravo` ←\n\n```\n+Unit Alfa\n+Tank Alfa\n+Unit Bravo\n+Infanteri Bravo\n-Infanteri Bravo\n-Unit Bravo\n-Tank Alfa\n-Unit Alfa\n```" + W_AR,
         "السطر الأول: n. ثم n سطرًا: النوع والاسم.", "أربعة أسطر لكل وحدة.", "1 ≤ n ≤ 6؛ النوع `tank` أو `infanteri`؛ والأسماء مختلفة."))

soal("virtual-di-konstruktor", "mudah", "trimmed", NAMA + "print('anak' if i % 2 == 0 else 'induk', r.choice(NAMA))\n",
     program(r'''
class Induk {
protected:
    string nama;

public:
    Induk(const string &n) : nama(n) {
        cout << nama << " saat dibangun: " << siapa() << endl;
    }

    virtual ~Induk() {
    }

    string getNama() const {
        return nama;
    }

    virtual string siapa() const {
        return "Induk";
    }
};

class Anak : public Induk {
public:
    Anak(const string &n) : Induk(n) {
    }

    string siapa() const override {
        return "Anak";
    }
};''', r'''    string jenis, nama;
    cin >> jenis >> nama;
    Induk *objek = nullptr;
    if (jenis == "anak") {
        objek = new Anak(nama);
    } else {
        objek = new Induk(nama);
    }
    cout << objek->getNama() << " setelah jadi: " << objek->siapa() << endl;
    delete objek;''', "string"), wajib=W,
     id=("Virtual di Dalam Konstruktor", "Ada satu tempat di mana method virtual **tidak** menuju turunan: di dalam konstruktor induk. Saat konstruktor `Induk` berjalan, bagian `Anak` belum dibangun — objeknya masih sebuah `Induk` — sehingga yang terpanggil adalah `Induk::siapa()`.\n\n"
         "Buat class `Induk` (atribut `string nama`; `getNama()`) dengan `virtual string siapa() const` yang mengembalikan `Induk`. **Konstruktornya** mencetak `<nama> saat dibangun: <siapa()>`.\n\n"
         "Buat class `Anak` turunannya yang menimpa `siapa()` menjadi `Anak`.\n\n"
         "Baca jenis (`induk` atau `anak`) dan nama. Buat objeknya dengan `new` di balik `Induk *`, lalu cetak `<nama> setelah jadi: <siapa()>`.\n\nUntuk `anak Budi`:\n\n```\nBudi saat dibangun: Induk\nBudi setelah jadi: Anak\n```" + W_ID,
         "Satu baris berisi jenis dan nama.", "Dua baris seperti contoh.", "Jenis `induk` atau `anak`; nama satu kata."),
     en=("Virtual inside a Constructor", "There is one place where a virtual method does **not** reach the derived class: inside the base constructor. While the constructor of `Induk` runs, the `Anak` part has not been built yet — the object is still an `Induk` — so it is `Induk::siapa()` that gets called.\n\n"
         "Make a class `Induk` (base; attribute `string nama`; `getNama()`) with `virtual string siapa() const` (who) returning `Induk`. Its **constructor** prints `<name> saat dibangun: <siapa()>` (while being built).\n\n"
         "Make a class `Anak` derived from it that overrides `siapa()` to return `Anak`.\n\n"
         "Read the kind (`induk` or `anak`) and a name. Create the object with `new` behind an `Induk *`, then print `<name> setelah jadi: <siapa()>` (once finished).\n\nFor `anak Budi`:\n\n```\nBudi saat dibangun: Induk\nBudi setelah jadi: Anak\n```" + W_EN,
         "One line with the kind and the name.", "Two lines as in the example.", "The kind is `induk` or `anak`; the name is one word."),
     ar=("افتراضية داخل الباني", "ثمة موضع واحد **لا** تصل فيه الطريقة الافتراضية إلى المشتق: داخل باني الأساس. فحين يعمل باني `Induk` لم يُبنَ جزء `Anak` بعد — والكائن ما زال `Induk` — فتُستدعى `Induk::siapa()`.\n\n"
         "أنشئ صنفًا `Induk` (أساس؛ الخاصية `string nama`؛ و `getNama()`) فيه `virtual string siapa() const` (مَن) تُرجع `Induk`. و**بانيه** يطبع `<الاسم> saat dibangun: <siapa()>` (أثناء البناء).\n\n"
         "أنشئ صنفًا `Anak` مشتقًّا منه يعيد تعريف `siapa()` لتُرجع `Anak`.\n\n"
         "اقرأ النوع (`induk` أو `anak`) واسمًا. أنشئ الكائن بـ `new` خلف `Induk *` ثم اطبع `<الاسم> setelah jadi: <siapa()>` (بعد الاكتمال).\n\nعند `anak Budi`:\n\n```\nBudi saat dibangun: Induk\nBudi setelah jadi: Anak\n```" + W_AR,
         "سطر واحد فيه النوع والاسم.", "سطران كما في المثال.", "النوع `induk` أو `anak`؛ والاسم كلمة واحدة."))

soal("paket-termahal", "mudah", "trimmed", "n = {2: 1}.get(i, r.randint(2, 8))\nprint(n)\nfor k in range(n):\n    print(r.choice(['biasa', 'kilat', 'hemat']), 'P%02d' % (k + 1), r.randint(1, 20))\n",
     program(r'''
class Paket {
protected:
    string kode;
    int berat;

public:
    Paket(const string &k, int b) : kode(k), berat(b) {
    }

    virtual ~Paket() {
    }

    string getKode() const {
        return kode;
    }

    virtual int ongkos() const {
        return berat * 5000;
    }
};

class PaketKilat : public Paket {
public:
    PaketKilat(const string &k, int b) : Paket(k, b) {
    }

    int ongkos() const override {
        return Paket::ongkos() * 2 + 10000;
    }
};

class PaketHemat : public Paket {
public:
    PaketHemat(const string &k, int b) : Paket(k, b) {
    }

    int ongkos() const override {
        auto biaya = berat * 3000;
        if (biaya < 9000) {
            biaya = 9000;
        }
        return biaya;
    }
};''', r'''    int n;
    cin >> n;
    Paket *daftar[8];
    for (int k = 0; k < n; k++) {
        string jenis, kode;
        int berat;
        cin >> jenis >> kode >> berat;
        if (jenis == "kilat") {
            daftar[k] = new PaketKilat(kode, berat);
        } else if (jenis == "hemat") {
            daftar[k] = new PaketHemat(kode, berat);
        } else {
            daftar[k] = new Paket(kode, berat);
        }
    }
    Paket *termahal = daftar[0];
    for (int k = 0; k < n; k++) {
        cout << daftar[k]->getKode() << ": " << daftar[k]->ongkos() << endl;
        if (daftar[k]->ongkos() > termahal->ongkos()) {
            termahal = daftar[k];
        }
    }
    cout << "Termahal: " << termahal->getKode() << endl;
    for (int k = 0; k < n; k++) {
        delete daftar[k];
    }''', "string"), wajib=W,
     id=("Paket Termahal", "Buat class `Paket` (atribut `protected` `string kode`, `int berat`; `getKode()`; `virtual int ongkos() const`). Ongkos paket biasa: berat × 5000.\n\nDua turunannya menimpa `ongkos()`:\n\n"
         "- `PaketKilat` — dua kali ongkos paket biasa (`Paket::ongkos()`), ditambah 10000;\n- `PaketHemat` — berat × 3000, tetapi paling sedikit 9000.\n\n"
         "Masukan: n paket, satu per baris: jenis (`biasa`, `kilat`, `hemat`), kode, dan berat dalam kg. Simpan di larik `Paket *`. Cetak `<kode>: <ongkos>` untuk tiap paket, lalu `Termahal: <kode>` — bila ada yang sama mahal, yang lebih dulu di masukan.\n\n"
         "Contoh: `biasa P01 2`, `kilat P02 1`, `hemat P03 1` →\n\n```\nP01: 10000\nP02: 20000\nP03: 9000\nTermahal: P02\n```" + W_ID,
         "Baris pertama: n. Lalu n baris: jenis, kode, berat.", "Satu baris per paket, lalu `Termahal: <kode>`.", "1 ≤ n ≤ 8; 1 ≤ berat ≤ 20; kode berbeda-beda"),
     en=("The Dearest Parcel", "Make a class `Paket` (parcel; `protected` attributes `string kode`, `int berat` (weight); `getKode()`; `virtual int ongkos() const` (cost)). The cost of an ordinary parcel: weight × 5000.\n\nTwo derived classes override `ongkos()`:\n\n"
         "- `PaketKilat` (express) — twice the cost of an ordinary parcel (`Paket::ongkos()`), plus 10000;\n- `PaketHemat` (economy) — weight × 3000, but at least 9000.\n\n"
         "Input: n parcels, one per line: the kind (`biasa`, `kilat`, `hemat`), the code and the weight in kg. Keep them in an array of `Paket *`. Print `<code>: <cost>` for each parcel, then `Termahal: <code>` (the dearest) — on a tie, the one earlier in the input.\n\n"
         "Example: `biasa P01 2`, `kilat P02 1`, `hemat P03 1` →\n\n```\nP01: 10000\nP02: 20000\nP03: 9000\nTermahal: P02\n```" + W_EN,
         "First line: n. Then n lines: kind, code, weight.", "One line per parcel, then `Termahal: <code>`.", "1 ≤ n ≤ 8; 1 ≤ weight ≤ 20; all codes differ"),
     ar=("أغلى طرد", "أنشئ صنفًا `Paket` (طرد؛ الخاصيتان المحميتان `string kode` و `int berat` (الوزن)؛ و `getKode()`؛ و `virtual int ongkos() const` (الأجرة)). أجرة الطرد العادي: الوزن × 5000.\n\nمشتقان يعيدان تعريف `ongkos()`:\n\n"
         "- `PaketKilat` (سريع) — ضعف أجرة الطرد العادي (`Paket::ongkos()`) زائد 10000؛\n- `PaketHemat` (اقتصادي) — الوزن × 3000، على ألا تقل عن 9000.\n\n"
         "المدخل: n طردًا، واحد في كل سطر: النوع (`biasa` أو `kilat` أو `hemat`) والرمز والوزن بالكيلوغرام. احفظها في مصفوفة `Paket *`. اطبع `<الرمز>: <الأجرة>` لكل طرد ثم `Termahal: <الرمز>` (الأغلى) — وعند التساوي الأسبق في المدخل.\n\n"
         "مثال: `biasa P01 2` و `kilat P02 1` و `hemat P03 1` ←\n\n```\nP01: 10000\nP02: 20000\nP03: 9000\nTermahal: P02\n```" + W_AR,
         "السطر الأول: n. ثم n سطرًا: النوع والرمز والوزن.", "سطر لكل طرد ثم `Termahal: <الرمز>`.", "1 ≤ n ≤ 8؛ 1 ≤ الوزن ≤ 20؛ والرموز مختلفة"))

soal("hitung-kucing", "mudah", "trimmed", HEWAN + "n = {2: 1}.get(i, r.randint(2, 8))\nprint(n)\nfor nama in r.sample(HEWAN, n):\n    print(r.choice(['kucing', 'kucing', 'anjing', 'burung']), nama)\n",
     program(r'''
class Hewan {
protected:
    string nama;

public:
    Hewan(const string &n) : nama(n) {
    }

    virtual ~Hewan() {
    }

    string getNama() const {
        return nama;
    }

    virtual string suara() const {
        return "...";
    }
};

class Kucing : public Hewan {
public:
    Kucing(const string &n) : Hewan(n) {
    }

    string suara() const override {
        return "meong";
    }

    void cakar() const {
        cout << nama << " mencakar" << endl;
    }
};

class Anjing : public Hewan {
public:
    Anjing(const string &n) : Hewan(n) {
    }

    string suara() const override {
        return "guk";
    }
};

class Burung : public Hewan {
public:
    Burung(const string &n) : Hewan(n) {
    }

    string suara() const override {
        return "cuit";
    }
};''', r'''    int n;
    cin >> n;
    Hewan *daftar[8];
    for (int k = 0; k < n; k++) {
        string jenis, nama;
        cin >> jenis >> nama;
        if (jenis == "kucing") {
            daftar[k] = new Kucing(nama);
        } else if (jenis == "anjing") {
            daftar[k] = new Anjing(nama);
        } else {
            daftar[k] = new Burung(nama);
        }
    }
    auto banyak = 0;
    for (int k = 0; k < n; k++) {
        cout << daftar[k]->getNama() << ": " << daftar[k]->suara() << endl;
        Kucing *kucing = dynamic_cast<Kucing *>(daftar[k]);
        if (kucing != nullptr) {
            kucing->cakar();
            banyak++;
        }
    }
    cout << "Kucing: " << banyak << " dari " << n << endl;
    for (int k = 0; k < n; k++) {
        delete daftar[k];
    }''', "string"), wajib=W,
     id=("Mana yang Kucing", "Lewat `Hewan *` hanya method milik `Hewan` yang bisa dipanggil. Untuk memakai method yang **hanya ada di turunan**, pointer itu harus diubah dulu — dan `dynamic_cast` melakukannya dengan aman: `dynamic_cast<Kucing *>(p)` menghasilkan pointer ke kucing bila `p` memang menunjuk `Kucing`, dan `nullptr` bila tidak.\n\n"
         "Buat class `Hewan` (atribut `protected` `string nama`; `getNama()`; `virtual string suara() const`) dan turunannya `Kucing` (`meong`), `Anjing` (`guk`), `Burung` (`cuit`). Hanya `Kucing` yang punya `void cakar() const`, yang mencetak `<nama> mencakar`.\n\n"
         "Masukan: n hewan (jenis dan nama), disimpan di larik `Hewan *`. Untuk tiap hewan cetak `<nama>: <suara>`; **bila ia kucing**, panggil juga `cakar()`. Di akhir cetak `Kucing: <k> dari <n>`.\n\n"
         "Contoh: `kucing Oyen`, `anjing Bruno`, `kucing Belang` →\n\n```\nOyen: meong\nOyen mencakar\nBruno: guk\nBelang: meong\nBelang mencakar\nKucing: 2 dari 3\n```" + W_ID,
         "Baris pertama: n. Lalu n baris: jenis dan nama.", "Baris-baris seperti contoh.", "1 ≤ n ≤ 8; jenis `kucing`, `anjing`, atau `burung`; nama berbeda-beda."),
     en=("Which Ones are Cats", "Through a `Hewan *` only the methods of `Hewan` can be called. To use a method that **exists only in a derived class** the pointer must be converted first — and `dynamic_cast` does that safely: `dynamic_cast<Kucing *>(p)` gives a pointer to the cat when `p` really points to a `Kucing`, and `nullptr` when it does not.\n\n"
         "Make a class `Hewan` (`protected` attribute `string nama`; `getNama()`; `virtual string suara() const`) and its derived classes `Kucing` (`meong`), `Anjing` (`guk`), `Burung` (bird, `cuit`). Only `Kucing` has `void cakar() const` (scratch), which prints `<name> mencakar`.\n\n"
         "Input: n animals (kind and name), kept in an array of `Hewan *`. For each animal print `<name>: <sound>`; **if it is a cat**, also call `cakar()`. At the end print `Kucing: <k> dari <n>` (k of n).\n\n"
         "Example: `kucing Oyen`, `anjing Bruno`, `kucing Belang` →\n\n```\nOyen: meong\nOyen mencakar\nBruno: guk\nBelang: meong\nBelang mencakar\nKucing: 2 dari 3\n```" + W_EN,
         "First line: n. Then n lines: kind and name.", "Lines as in the example.", "1 ≤ n ≤ 8; the kind is `kucing`, `anjing` or `burung`; all names differ."),
     ar=("أيّها قطط", "عبر `Hewan *` لا تُستدعى إلا طرائق `Hewan`. ولاستعمال طريقة **لا توجد إلا في المشتق** يجب تحويل المؤشر أولًا — و `dynamic_cast` يفعل ذلك بأمان: `dynamic_cast<Kucing *>(p)` يعطي مؤشرًا إلى القطة إذا كان `p` يشير حقًّا إلى `Kucing`، ويعطي `nullptr` إن لم يكن.\n\n"
         "أنشئ صنفًا `Hewan` (الخاصية المحمية `string nama`؛ و `getNama()`؛ و `virtual string suara() const`) ومشتقاته `Kucing` (`meong`) و `Anjing` (`guk`) و `Burung` (طائر، `cuit`). ولـ `Kucing` وحدها `void cakar() const` (اخدش) التي تطبع `<الاسم> mencakar`.\n\n"
         "المدخل: n حيوانًا (النوع والاسم) تُحفظ في مصفوفة `Hewan *`. لكل حيوان اطبع `<الاسم>: <الصوت>`؛ و**إذا كان قطة** فاستدعِ `cakar()` أيضًا. في النهاية اطبع `Kucing: <k> dari <n>` (k من n).\n\n"
         "مثال: `kucing Oyen` و `anjing Bruno` و `kucing Belang` ←\n\n```\nOyen: meong\nOyen mencakar\nBruno: guk\nBelang: meong\nBelang mencakar\nKucing: 2 dari 3\n```" + W_AR,
         "السطر الأول: n. ثم n سطرًا: النوع والاسم.", "أسطر كما في المثال.", "1 ≤ n ≤ 8؛ النوع `kucing` أو `anjing` أو `burung`؛ والأسماء مختلفة."))

soal("timpa-sebagian", "mudah", "trimmed", "n = {2: 1}.get(i, r.randint(2, 6))\nprint(n)\nprint(*[r.choice(['dasar', 'tengah', 'atas']) for _ in range(n)])\n",
     program(r'''
class Dasar {
public:
    virtual ~Dasar() {
    }

    virtual string satu() const {
        return "Dasar";
    }

    virtual string dua() const {
        return "Dasar";
    }
};

class Tengah : public Dasar {
public:
    string satu() const override {
        return "Tengah";
    }
};

class Atas : public Tengah {
public:
    string dua() const override {
        return "Atas";
    }
};''', r'''    int n;
    cin >> n;
    for (int k = 0; k < n; k++) {
        string jenis;
        cin >> jenis;
        Dasar *objek = nullptr;
        if (jenis == "dasar") {
            objek = new Dasar;
        } else if (jenis == "tengah") {
            objek = new Tengah;
        } else {
            objek = new Atas;
        }
        cout << "satu=" << objek->satu() << " dua=" << objek->dua() << endl;
        delete objek;
    }''', "string"), wajib=W,
     id=("Menimpa Sebagian", "Turunan tidak harus menimpa semua method virtual. Untuk tiap method, yang berlaku adalah versi **paling bawah** yang ada di jalur dari induk ke class objek itu.\n\n"
         "Buat tiga class bertingkat:\n\n- `Dasar` — `virtual string satu() const` dan `virtual string dua() const`, keduanya mengembalikan `Dasar`;\n- `Tengah` turunan `Dasar` — hanya menimpa `satu()` menjadi `Tengah`;\n- `Atas` turunan `Tengah` — hanya menimpa `dua()` menjadi `Atas`.\n\n"
         "Baca n kata (`dasar`, `tengah`, `atas`). Untuk tiap kata buat objeknya di balik `Dasar *` dan cetak `satu=<…> dua=<…>`.\n\n"
         "Contoh: `dasar atas tengah` →\n\n```\nsatu=Dasar dua=Dasar\nsatu=Tengah dua=Atas\nsatu=Tengah dua=Dasar\n```" + W_ID,
         "Baris pertama: n. Baris kedua: n kata.", "n baris.", "1 ≤ n ≤ 6"),
     en=("Overriding Some", "A derived class need not override every virtual method. For each method, what applies is the **lowest** version found on the path from the base down to the class of the object.\n\n"
         "Make three classes in levels:\n\n- `Dasar` (bottom) — `virtual string satu() const` (one) and `virtual string dua() const` (two), both returning `Dasar`;\n- `Tengah` (middle) derived from `Dasar` — overrides only `satu()`, returning `Tengah`;\n- `Atas` (top) derived from `Tengah` — overrides only `dua()`, returning `Atas`.\n\n"
         "Read n words (`dasar`, `tengah`, `atas`). For each word create the object behind a `Dasar *` and print `satu=<…> dua=<…>`.\n\n"
         "Example: `dasar atas tengah` →\n\n```\nsatu=Dasar dua=Dasar\nsatu=Tengah dua=Atas\nsatu=Tengah dua=Dasar\n```" + W_EN,
         "First line: n. Second line: n words.", "n lines.", "1 ≤ n ≤ 6"),
     ar=("إعادة تعريف جزئية", "لا يلزم المشتق أن يعيد تعريف كل الطرائق الافتراضية. فلكل طريقة تسري النسخة **الأدنى** الموجودة على المسار من الأساس إلى صنف الكائن.\n\n"
         "أنشئ ثلاثة أصناف متدرجة:\n\n- `Dasar` (القاعدة) — `virtual string satu() const` (واحد) و `virtual string dua() const` (اثنان) وكلتاهما تُرجع `Dasar`؛\n- `Tengah` (الأوسط) المشتق من `Dasar` — يعيد تعريف `satu()` فقط لتُرجع `Tengah`؛\n- `Atas` (الأعلى) المشتق من `Tengah` — يعيد تعريف `dua()` فقط لتُرجع `Atas`.\n\n"
         "اقرأ n كلمة (`dasar` أو `tengah` أو `atas`). لكل كلمة أنشئ الكائن خلف `Dasar *` واطبع `satu=<…> dua=<…>`.\n\n"
         "مثال: `dasar atas tengah` ←\n\n```\nsatu=Dasar dua=Dasar\nsatu=Tengah dua=Atas\nsatu=Tengah dua=Dasar\n```" + W_AR,
         "السطر الأول: n. السطر الثاني: n كلمة.", "n سطرًا.", "1 ≤ n ≤ 6"))

soal("sensor-final", "mudah", "trimmed", "n = {2: 1}.get(i, r.randint(2, 8))\nprint(n)\nfor _ in range(n):\n    print(r.choice(['umum', 'suhu', 'luar']), r.randint(0, 999))\n",
     program(r'''
class Sensor {
public:
    virtual ~Sensor() {
    }

    virtual string label() const {
        return "sensor";
    }

    virtual int baca(int mentah) const {
        return mentah;
    }
};

class SensorSuhu : public Sensor {
public:
    string label() const override {
        return "suhu";
    }

    int baca(int mentah) const final {
        return mentah / 10 - 40;
    }
};

class SensorSuhuLuar : public SensorSuhu {
public:
    string label() const override {
        return "suhu luar";
    }
};''', r'''    int n;
    cin >> n;
    for (int k = 0; k < n; k++) {
        string jenis;
        int mentah;
        cin >> jenis >> mentah;
        Sensor *sensor = nullptr;
        if (jenis == "suhu") {
            sensor = new SensorSuhu;
        } else if (jenis == "luar") {
            sensor = new SensorSuhuLuar;
        } else {
            sensor = new Sensor;
        }
        cout << sensor->label() << ": " << sensor->baca(mentah) << endl;
        delete sensor;
    }''', "string"), wajib=W,
     id=("Sensor dan final", "Kata `final` di belakang sebuah method virtual berarti: **turunan berikutnya tidak boleh menimpanya lagi**. Berguna bila sebuah rumus harus tetap sama di semua class di bawahnya.\n\n"
         "Buat tiga class:\n\n- `Sensor` — `virtual string label() const` → `sensor`; `virtual int baca(int mentah) const` → nilai mentah apa adanya;\n"
         "- `SensorSuhu` turunan `Sensor` — label `suhu`; `int baca(int mentah) const final` → mentah / 10 − 40 (pembagian bilangan bulat);\n"
         "- `SensorSuhuLuar` turunan `SensorSuhu` — hanya menimpa label menjadi `suhu luar`; rumus `baca` diwarisi dan tidak bisa diubah.\n\n"
         "Masukan: n pembacaan, satu per baris: jenis (`umum`, `suhu`, `luar`) dan nilai mentah. Untuk tiap baris buat sensornya di balik `Sensor *` dan cetak `<label>: <baca(mentah)>`.\n\n"
         "Contoh: `umum 512`, `suhu 655`, `luar 409` →\n\n```\nsensor: 512\nsuhu: 25\nsuhu luar: 0\n```" + W_ID,
         "Baris pertama: n. Lalu n baris: jenis dan nilai mentah.", "n baris.", "1 ≤ n ≤ 8; 0 ≤ mentah ≤ 999"),
     en=("Sensors and final", "The word `final` after a virtual method means: **classes further down may not override it again**. Useful when a formula must stay the same in every class below.\n\n"
         "Make three classes:\n\n- `Sensor` — `virtual string label() const` → `sensor`; `virtual int baca(int mentah) const` (read a raw value) → the raw value as it is;\n"
         "- `SensorSuhu` (temperature) derived from `Sensor` — label `suhu`; `int baca(int mentah) const final` → raw / 10 − 40 (integer division);\n"
         "- `SensorSuhuLuar` (outdoor temperature) derived from `SensorSuhu` — overrides only the label, to `suhu luar`; the formula of `baca` is inherited and cannot be changed.\n\n"
         "Input: n readings, one per line: the kind (`umum`, `suhu`, `luar`) and the raw value. For each line create the sensor behind a `Sensor *` and print `<label>: <baca(raw)>`.\n\n"
         "Example: `umum 512`, `suhu 655`, `luar 409` →\n\n```\nsensor: 512\nsuhu: 25\nsuhu luar: 0\n```" + W_EN,
         "First line: n. Then n lines: kind and raw value.", "n lines.", "1 ≤ n ≤ 8; 0 ≤ raw ≤ 999"),
     ar=("المجسّات و final", "الكلمة `final` بعد طريقة افتراضية تعني: **لا يجوز للأصناف الأدنى أن تعيد تعريفها**. وهي نافعة حين يجب أن تبقى صيغةٌ واحدةً في كل الأصناف التي تحتها.\n\n"
         "أنشئ ثلاثة أصناف:\n\n- `Sensor` — `virtual string label() const` ← `sensor`؛ و `virtual int baca(int mentah) const` (اقرأ قيمة خامًا) ← القيمة الخام كما هي؛\n"
         "- `SensorSuhu` (حرارة) المشتق من `Sensor` — التسمية `suhu`؛ و `int baca(int mentah) const final` ← الخام / 10 − 40 (قسمة صحيحة)؛\n"
         "- `SensorSuhuLuar` (حرارة خارجية) المشتق من `SensorSuhu` — يعيد تعريف التسمية فقط إلى `suhu luar`؛ وصيغة `baca` موروثة ولا يمكن تغييرها.\n\n"
         "المدخل: n قراءة، واحدة في كل سطر: النوع (`umum` أو `suhu` أو `luar`) والقيمة الخام. لكل سطر أنشئ المجسّ خلف `Sensor *` واطبع `<التسمية>: <baca(الخام)>`.\n\n"
         "مثال: `umum 512` و `suhu 655` و `luar 409` ←\n\n```\nsensor: 512\nsuhu: 25\nsuhu luar: 0\n```" + W_AR,
         "السطر الأول: n. ثم n سطرًا: النوع والقيمة الخام.", "n سطرًا.", "1 ≤ n ≤ 8؛ 0 ≤ الخام ≤ 999"))

# ============================== MUDAH-SEDANG (6) ==============================

soal("bentuk-terurut", "mudah_sedang", "trimmed", GEN_BENTUK,
     program(BENTUK, r'''    int n;
    cin >> n;
    Bentuk *daftar[8];
    for (int k = 0; k < n; k++) {
        Bentuk *baru = bacaBentuk();
        auto posisi = k;
        while (posisi > 0 && daftar[posisi - 1]->luas() > baru->luas()) {
            daftar[posisi] = daftar[posisi - 1];
            posisi--;
        }
        daftar[posisi] = baru;
    }
    for (int k = 0; k < n; k++) {
        cout << daftar[k]->nama() << " " << daftar[k]->luas() << endl;
        delete daftar[k];
    }''', "string"), wajib=W,
     id=("Bentuk Terurut", "Algoritma yang hanya memakai antarmuka induk bekerja untuk semua turunan tanpa diubah. Di sini: **mengurutkan** bentuk-bentuk berlainan jenis menurut luasnya, cukup dengan membandingkan `luas()` lewat pointer induk.\n\n"
         "Buat class `Bentuk` (`virtual string nama() const`, `virtual int luas() const`, destruktor virtual) dan turunannya `Persegi` (`persegi`), `PersegiPanjang` (`persegi panjang`), `Segitiga` (`segitiga`; luas alas × tinggi / 2).\n\n"
         "Masukan: n bentuk — `persegi <sisi>`, `panjang <panjang> <lebar>`, atau `segitiga <alas> <tinggi>`. Simpan di larik `Bentuk *`, urutkan **pointer-nya** dari luas terkecil, dan cetak `<nama> <luas>` per baris. Bentuk yang sama luas tetap dalam urutan masukan.\n\n"
         "Contoh: `persegi 4`, `segitiga 6 3`, `panjang 2 5` →\n\n```\nsegitiga 9\npersegi panjang 10\npersegi 16\n```" + W_ID,
         "Baris pertama: n. Lalu n baris bentuk.", "n baris, dari luas terkecil.", "1 ≤ n ≤ 8; tiap ukuran antara 1 dan 30; alas segitiga selalu genap"),
     en=("Shapes in Order", "An algorithm that uses only the base interface works for every derived class unchanged. Here: **sorting** shapes of different kinds by area, simply by comparing `luas()` through base pointers.\n\n"
         "Make a class `Bentuk` (`virtual string nama() const`, `virtual int luas() const`, a virtual destructor) and its derived classes `Persegi` (`persegi`), `PersegiPanjang` (`persegi panjang`), `Segitiga` (`segitiga`; area base × height / 2).\n\n"
         "Input: n shapes — `persegi <side>`, `panjang <length> <width>`, or `segitiga <base> <height>`. Keep them in an array of `Bentuk *`, sort **the pointers** from the smallest area, and print `<name> <area>` per line. Shapes of equal area stay in input order.\n\n"
         "Example: `persegi 4`, `segitiga 6 3`, `panjang 2 5` →\n\n```\nsegitiga 9\npersegi panjang 10\npersegi 16\n```" + W_EN,
         "First line: n. Then n shape lines.", "n lines, from the smallest area.", "1 ≤ n ≤ 8; each size is between 1 and 30; the base of a triangle is always even"),
     ar=("أشكال مرتبة", "الخوارزمية التي لا تستعمل إلا واجهة الأساس تعمل لكل المشتقات بلا تغيير. وهنا: **ترتيب** أشكال مختلفة الأنواع بحسب المساحة، بمقارنة `luas()` عبر مؤشرات الأساس فقط.\n\n"
         "أنشئ صنفًا `Bentuk` (`virtual string nama() const` و `virtual int luas() const` وهادم افتراضي) ومشتقاته `Persegi` (`persegi`) و `PersegiPanjang` (`persegi panjang`) و `Segitiga` (`segitiga`؛ المساحة القاعدة × الارتفاع / 2).\n\n"
         "المدخل: n شكلًا — `persegi <الضلع>` أو `panjang <الطول> <العرض>` أو `segitiga <القاعدة> <الارتفاع>`. احفظها في مصفوفة `Bentuk *` ورتّب **المؤشرات** من أصغر مساحة واطبع `<الاسم> <المساحة>` في كل سطر. والأشكال المتساوية المساحة تبقى بترتيب المدخل.\n\n"
         "مثال: `persegi 4` و `segitiga 6 3` و `panjang 2 5` ←\n\n```\nsegitiga 9\npersegi panjang 10\npersegi 16\n```" + W_AR,
         "السطر الأول: n. ثم n سطر أشكال.", "n سطرًا من أصغر مساحة.", "1 ≤ n ≤ 8؛ كل قياس بين 1 و 30؛ قاعدة المثلث زوجية دائمًا"))

soal("salin-virtual", "mudah_sedang", "trimmed", "if i % 2 == 0:\n    print('persegi', r.randint(1, 50), r.randint(2, 9))\nelse:\n    print('panjang', r.randint(1, 50), r.randint(1, 50), r.randint(2, 9))\n",
     program(r'''
class Bentuk {
public:
    virtual ~Bentuk() {
    }

    virtual Bentuk *salin() const {
        return new Bentuk(*this);
    }

    virtual void skala(int) {
    }

    virtual void cetak() const {
        cout << "bentuk" << endl;
    }
};

class Persegi : public Bentuk {
private:
    int sisi;

public:
    Persegi(int s) : sisi(s) {
    }

    Bentuk *salin() const override {
        return new Persegi(*this);
    }

    void skala(int k) override {
        sisi *= k;
    }

    void cetak() const override {
        cout << "persegi " << sisi << endl;
    }
};

class PersegiPanjang : public Bentuk {
private:
    int panjang;
    int lebar;

public:
    PersegiPanjang(int p, int l) : panjang(p), lebar(l) {
    }

    Bentuk *salin() const override {
        return new PersegiPanjang(*this);
    }

    void skala(int k) override {
        panjang *= k;
        lebar *= k;
    }

    void cetak() const override {
        cout << "persegi panjang " << panjang << " x " << lebar << endl;
    }
};''', r'''    string jenis;
    cin >> jenis;
    Bentuk *asli = nullptr;
    if (jenis == "persegi") {
        int s;
        cin >> s;
        asli = new Persegi(s);
    } else {
        int p, l;
        cin >> p >> l;
        asli = new PersegiPanjang(p, l);
    }
    int k;
    cin >> k;
    Bentuk *tiruan = asli->salin();
    asli->skala(k);
    cout << "Asli: ";
    asli->cetak();
    cout << "Salinan: ";
    tiruan->cetak();
    delete asli;
    delete tiruan;''', "string"), wajib=W,
     id=("Salinan lewat Pointer Induk", "Bagaimana menyalin objek bila yang dipegang hanya `Bentuk *` dan jenis sebenarnya tidak diketahui? `new Bentuk(*p)` akan mengiris objeknya. Jalan keluarnya: biarkan **objek itu sendiri** membuat salinannya, lewat method virtual.\n\n"
         "Buat class `Bentuk` dengan tiga method virtual: `Bentuk *salin() const`, `void skala(int k)`, dan `void cetak() const`. Turunannya:\n\n"
         "- `Persegi` (sisi) — `salin()` mengembalikan `new Persegi(*this)`; `skala` mengalikan sisi; `cetak` → `persegi <sisi>`;\n"
         "- `PersegiPanjang` (panjang, lebar) — `salin()` mengembalikan `new PersegiPanjang(*this)`; `skala` mengalikan keduanya; `cetak` → `persegi panjang <p> x <l>`.\n\n"
         "Masukan: `persegi <sisi> <k>` atau `panjang <panjang> <lebar> <k>`. Buat bentuk di balik `Bentuk *asli`; buat salinannya dengan `asli->salin()`; **lalu** perbesar yang asli k kali. Cetak `Asli: …` dan `Salinan: …`.\n\n"
         "Untuk `persegi 3 2`:\n\n```\nAsli: persegi 6\nSalinan: persegi 3\n```\n\nUntuk `panjang 2 5 3`: `Asli: persegi panjang 6 x 15` dan `Salinan: persegi panjang 2 x 5`." + W_ID,
         "Satu baris: jenis, ukuran, lalu faktor k.", "Dua baris seperti contoh.", "Tiap ukuran antara 1 dan 50; 2 ≤ k ≤ 9"),
     en=("A Copy through a Base Pointer", "How do you copy an object when all you hold is a `Bentuk *` and its real kind is unknown? `new Bentuk(*p)` would slice it. The way out: let **the object itself** make its copy, through a virtual method.\n\n"
         "Make a class `Bentuk` with three virtual methods: `Bentuk *salin() const` (copy), `void skala(int k)` (scale), and `void cetak() const` (print). Its derived classes:\n\n"
         "- `Persegi` (side) — `salin()` returns `new Persegi(*this)`; `skala` multiplies the side; `cetak` → `persegi <side>`;\n"
         "- `PersegiPanjang` (length, width) — `salin()` returns `new PersegiPanjang(*this)`; `skala` multiplies both; `cetak` → `persegi panjang <l> x <w>`.\n\n"
         "Input: `persegi <side> <k>` or `panjang <length> <width> <k>`. Create the shape behind `Bentuk *asli` (original); make its copy with `asli->salin()`; **then** enlarge the original k times. Print `Asli: …` and `Salinan: …` (copy).\n\n"
         "For `persegi 3 2`:\n\n```\nAsli: persegi 6\nSalinan: persegi 3\n```\n\nFor `panjang 2 5 3`: `Asli: persegi panjang 6 x 15` and `Salinan: persegi panjang 2 x 5`." + W_EN,
         "One line: the kind, the sizes, then the factor k.", "Two lines as in the example.", "Each size is between 1 and 50; 2 ≤ k ≤ 9"),
     ar=("نسخة عبر مؤشر الأساس", "كيف ننسخ كائنًا إذا لم يكن في يدنا إلا `Bentuk *` ونوعه الحقيقي مجهول؟ إن `new Bentuk(*p)` يقطعه. والمخرج: أن ندع **الكائن نفسه** يصنع نسخته عبر طريقة افتراضية.\n\n"
         "أنشئ صنفًا `Bentuk` فيه ثلاث طرائق افتراضية: `Bentuk *salin() const` (انسخ) و `void skala(int k)` (كبِّر) و `void cetak() const` (اطبع). ومشتقاه:\n\n"
         "- `Persegi` (الضلع) — `salin()` تُرجع `new Persegi(*this)`؛ و `skala` تضرب الضلع؛ و `cetak` ← `persegi <الضلع>`؛\n"
         "- `PersegiPanjang` (الطول، العرض) — `salin()` تُرجع `new PersegiPanjang(*this)`؛ و `skala` تضرب الاثنين؛ و `cetak` ← `persegi panjang <p> x <l>`.\n\n"
         "المدخل: `persegi <الضلع> <k>` أو `panjang <الطول> <العرض> <k>`. أنشئ الشكل خلف `Bentuk *asli` (الأصل)؛ واصنع نسخته بـ `asli->salin()`؛ **ثم** كبِّر الأصل k مرة. اطبع `Asli: …` و `Salinan: …` (النسخة).\n\n"
         "عند `persegi 3 2`:\n\n```\nAsli: persegi 6\nSalinan: persegi 3\n```\n\nوعند `panjang 2 5 3`: `Asli: persegi panjang 6 x 15` و `Salinan: persegi panjang 2 x 5`." + W_AR,
         "سطر واحد: النوع والقياسات ثم المعامل k.", "سطران كما في المثال.", "كل قياس بين 1 و 50؛ 2 ≤ k ≤ 9"))

soal("dokumen-template", "mudah_sedang", "trimmed",
     NAMA + "j = ['surat', 'laporan', 'kosong'][i % 3]\njudul = r.choice(['Undangan', 'Keuangan', 'Catatan', 'Pengumuman', 'Rapat', 'Evaluasi'])\nif j == 'surat':\n    print(j, judul, r.choice(NAMA))\nelif j == 'laporan':\n    print(j, judul, r.randint(1, 200))\nelse:\n    print(j, judul)\n",
     program(r'''
class Dokumen {
protected:
    string judul;

public:
    Dokumen(const string &j) : judul(j) {
    }

    virtual ~Dokumen() {
    }

    void cetak() const {
        kepala();
        isi();
        kaki();
    }

    virtual void kepala() const {
        cout << "== " << judul << " ==" << endl;
    }

    virtual void isi() const {
        cout << "(kosong)" << endl;
    }

    virtual void kaki() const {
        cout << "--" << endl;
    }
};

class Surat : public Dokumen {
private:
    string penerima;

public:
    Surat(const string &j, const string &p) : Dokumen(j), penerima(p) {
    }

    void isi() const override {
        cout << "Kepada " << penerima << endl;
    }

    void kaki() const override {
        cout << "Hormat kami" << endl;
    }
};

class Laporan : public Dokumen {
private:
    int halaman;

public:
    Laporan(const string &j, int h) : Dokumen(j), halaman(h) {
    }

    void kepala() const override {
        cout << "LAPORAN: " << judul << endl;
    }

    void isi() const override {
        cout << halaman << " halaman" << endl;
    }
};''', r'''    string jenis, judul;
    cin >> jenis >> judul;
    Dokumen *dokumen = nullptr;
    if (jenis == "surat") {
        string penerima;
        cin >> penerima;
        dokumen = new Surat(judul, penerima);
    } else if (jenis == "laporan") {
        int halaman;
        cin >> halaman;
        dokumen = new Laporan(judul, halaman);
    } else {
        dokumen = new Dokumen(judul);
    }
    dokumen->cetak();
    delete dokumen;''', "string"), wajib=W,
     id=("Kerangka Dokumen", "Induk boleh menetapkan **urutan langkah** sekali saja, dan membiarkan turunan mengganti langkah-langkahnya satu per satu.\n\n"
         "Buat class `Dokumen` (atribut `protected` `string judul`). Method **biasa** `void cetak() const` memanggil, berurutan, tiga method virtual:\n\n"
         "- `kepala()` — mencetak `== <judul> ==`;\n- `isi()` — mencetak `(kosong)`;\n- `kaki()` — mencetak `--`.\n\nTurunannya hanya menimpa sebagian:\n\n"
         "- `Surat` (tambahan `string penerima`) — `isi()` → `Kepada <penerima>`; `kaki()` → `Hormat kami`;\n- `Laporan` (tambahan `int halaman`) — `kepala()` → `LAPORAN: <judul>`; `isi()` → `<halaman> halaman`.\n\n"
         "Tidak ada turunan yang menulis ulang `cetak()`.\n\nMasukan: `surat <judul> <penerima>`, `laporan <judul> <halaman>`, atau `kosong <judul>`. Buat dokumennya di balik `Dokumen *` dan panggil `cetak()`.\n\n"
         "Untuk `surat Undangan Budi`:\n\n```\n== Undangan ==\nKepada Budi\nHormat kami\n```\n\nUntuk `laporan Keuangan 12`:\n\n```\nLAPORAN: Keuangan\n12 halaman\n--\n```" + W_ID,
         "Satu baris: jenis dokumen, judul, dan (bila ada) satu data lagi.", "Tiga baris.", "Judul dan penerima satu kata; 1 ≤ halaman ≤ 200"),
     en=("A Document Skeleton", "A base class may fix the **order of the steps** once, and let derived classes replace the steps one by one.\n\n"
         "Make a class `Dokumen` (`protected` attribute `string judul`, the title). An **ordinary** method `void cetak() const` calls, in order, three virtual methods:\n\n"
         "- `kepala()` (header) — prints `== <title> ==`;\n- `isi()` (body) — prints `(kosong)`;\n- `kaki()` (footer) — prints `--`.\n\nThe derived classes override only some:\n\n"
         "- `Surat` (letter; extra `string penerima`, the recipient) — `isi()` → `Kepada <recipient>`; `kaki()` → `Hormat kami`;\n- `Laporan` (report; extra `int halaman`, pages) — `kepala()` → `LAPORAN: <title>`; `isi()` → `<pages> halaman`.\n\n"
         "No derived class rewrites `cetak()`.\n\nInput: `surat <title> <recipient>`, `laporan <title> <pages>`, or `kosong <title>`. Create the document behind a `Dokumen *` and call `cetak()`.\n\n"
         "For `surat Undangan Budi`:\n\n```\n== Undangan ==\nKepada Budi\nHormat kami\n```\n\nFor `laporan Keuangan 12`:\n\n```\nLAPORAN: Keuangan\n12 halaman\n--\n```" + W_EN,
         "One line: the kind of document, the title, and (if any) one more item.", "Three lines.", "Title and recipient are one word; 1 ≤ pages ≤ 200"),
     ar=("هيكل المستند", "يجوز أن يثبّت الأساس **ترتيب الخطوات** مرة واحدة ويدع المشتقات تستبدل الخطوات واحدة واحدة.\n\n"
         "أنشئ صنفًا `Dokumen` (الخاصية المحمية `string judul` أي العنوان). الطريقة **العادية** `void cetak() const` تستدعي بالترتيب ثلاث طرائق افتراضية:\n\n"
         "- `kepala()` (الرأس) — تطبع `== <العنوان> ==`؛\n- `isi()` (المتن) — تطبع `(kosong)`؛\n- `kaki()` (التذييل) — تطبع `--`.\n\nوالمشتقان يعيدان تعريف بعضها فقط:\n\n"
         "- `Surat` (رسالة؛ إضافة `string penerima` أي المستلم) — `isi()` ← `Kepada <المستلم>`؛ و `kaki()` ← `Hormat kami`؛\n- `Laporan` (تقرير؛ إضافة `int halaman` أي الصفحات) — `kepala()` ← `LAPORAN: <العنوان>`؛ و `isi()` ← `<الصفحات> halaman`.\n\n"
         "لا يعيد أي مشتق كتابة `cetak()`.\n\nالمدخل: `surat <العنوان> <المستلم>` أو `laporan <العنوان> <الصفحات>` أو `kosong <العنوان>`. أنشئ المستند خلف `Dokumen *` واستدعِ `cetak()`.\n\n"
         "عند `surat Undangan Budi`:\n\n```\n== Undangan ==\nKepada Budi\nHormat kami\n```\n\nوعند `laporan Keuangan 12`:\n\n```\nLAPORAN: Keuangan\n12 halaman\n--\n```" + W_AR,
         "سطر واحد: نوع المستند والعنوان و(إن وُجد) بيان آخر.", "ثلاثة أسطر.", "العنوان والمستلم كلمة واحدة؛ 1 ≤ الصفحات ≤ 200"))

soal("kalkulator-operasi", "mudah_sedang", "trimmed", "n = {2: 1}.get(i, r.randint(2, 10))\nprint(n)\nfor _ in range(n):\n    print(r.choice(['tambah', 'kurang', 'kali', 'bagi', 'sisa']), r.randint(0, 99), r.randint(1, 20))\n",
     program(r'''
class Operasi {
public:
    virtual ~Operasi() {
    }

    virtual char simbol() const {
        return '?';
    }

    virtual int hitung(int, int) const {
        return 0;
    }
};

class Tambah : public Operasi {
public:
    char simbol() const override {
        return '+';
    }

    int hitung(int a, int b) const override {
        return a + b;
    }
};

class Kurang : public Operasi {
public:
    char simbol() const override {
        return '-';
    }

    int hitung(int a, int b) const override {
        return a - b;
    }
};

class Kali : public Operasi {
public:
    char simbol() const override {
        return '*';
    }

    int hitung(int a, int b) const override {
        return a * b;
    }
};

class Bagi : public Operasi {
public:
    char simbol() const override {
        return '/';
    }

    int hitung(int a, int b) const override {
        return a / b;
    }
};

class Sisa : public Operasi {
public:
    char simbol() const override {
        return '%';
    }

    int hitung(int a, int b) const override {
        return a % b;
    }
};''', r'''    string nama[5] = {"tambah", "kurang", "kali", "bagi", "sisa"};
    Operasi *daftar[5] = {new Tambah, new Kurang, new Kali, new Bagi, new Sisa};
    int n;
    cin >> n;
    for (int k = 0; k < n; k++) {
        string kata;
        int a, b;
        cin >> kata >> a >> b;
        for (int j = 0; j < 5; j++) {
            if (nama[j] == kata) {
                cout << a << " " << daftar[j]->simbol() << " " << b << " = " << daftar[j]->hitung(a, b) << endl;
            }
        }
    }
    for (int j = 0; j < 5; j++) {
        delete daftar[j];
    }''', "string"), wajib=W,
     id=("Kalkulator Tanpa Percabangan Panjang", "Rangkaian `if … else if …` yang memilih operasi bisa diganti dengan **tabel objek**: tiap operasi adalah satu objek, dan pemanggilan virtual memilih rumusnya.\n\n"
         "Buat class `Operasi` dengan `virtual char simbol() const` dan `virtual int hitung(int a, int b) const`. Buat lima turunannya: `Tambah` (`+`), `Kurang` (`-`), `Kali` (`*`), `Bagi` (`/`, pembagian bilangan bulat), `Sisa` (`%`).\n\n"
         "Di `main`, siapkan larik `Operasi *daftar[5]` dan larik nama `tambah`, `kurang`, `kali`, `bagi`, `sisa`. Untuk tiap soal hitungan, cari namanya di larik nama, lalu cetak `<a> <simbol> <b> = <hasil>` lewat pointer yang sepadan.\n\n"
         "Contoh: `kali 6 7`, `sisa 17 5` →\n\n```\n6 * 7 = 42\n17 % 5 = 2\n```" + W_ID,
         "Baris pertama: n. Lalu n baris: nama operasi, a, b.", "n baris.", "1 ≤ n ≤ 10; 0 ≤ a ≤ 99; 1 ≤ b ≤ 20"),
     en=("A Calculator without a Long Branch", "A chain of `if … else if …` that selects an operation can be replaced by a **table of objects**: each operation is one object, and the virtual call selects the formula.\n\n"
         "Make a class `Operasi` with `virtual char simbol() const` and `virtual int hitung(int a, int b) const` (compute). Make five derived classes: `Tambah` (`+`), `Kurang` (`-`), `Kali` (`*`), `Bagi` (`/`, integer division), `Sisa` (`%`).\n\n"
         "In `main` prepare an array `Operasi *daftar[5]` and an array of the names `tambah`, `kurang`, `kali`, `bagi`, `sisa`. For each calculation look the name up in the array of names, then print `<a> <symbol> <b> = <result>` through the matching pointer.\n\n"
         "Example: `kali 6 7`, `sisa 17 5` →\n\n```\n6 * 7 = 42\n17 % 5 = 2\n```" + W_EN,
         "First line: n. Then n lines: the operation name, a, b.", "n lines.", "1 ≤ n ≤ 10; 0 ≤ a ≤ 99; 1 ≤ b ≤ 20"),
     ar=("حاسبة بلا تفريع طويل", "سلسلة `if … else if …` التي تختار العملية يمكن استبدالها بـ**جدول كائنات**: كل عملية كائن، والاستدعاء الافتراضي يختار الصيغة.\n\n"
         "أنشئ صنفًا `Operasi` فيه `virtual char simbol() const` و `virtual int hitung(int a, int b) const` (احسب). أنشئ خمسة مشتقات: `Tambah` (`+`) و `Kurang` (`-`) و `Kali` (`*`) و `Bagi` (`/` قسمة صحيحة) و `Sisa` (`%`).\n\n"
         "في `main` جهّز مصفوفة `Operasi *daftar[5]` ومصفوفة الأسماء `tambah` و `kurang` و `kali` و `bagi` و `sisa`. لكل عملية حسابية ابحث عن الاسم في مصفوفة الأسماء ثم اطبع `<a> <الرمز> <b> = <الناتج>` عبر المؤشر المقابل.\n\n"
         "مثال: `kali 6 7` و `sisa 17 5` ←\n\n```\n6 * 7 = 42\n17 % 5 = 2\n```" + W_AR,
         "السطر الأول: n. ثم n سطرًا: اسم العملية و a و b.", "n سطرًا.", "1 ≤ n ≤ 10؛ 0 ≤ a ≤ 99؛ 1 ≤ b ≤ 20"))

soal("metode-pembayaran", "mudah_sedang", "trimmed", "print(r.randint(1, 20) * 50000)\nn = {2: 1}.get(i, r.randint(2, 10))\nprint(n)\nfor _ in range(n):\n    print(r.choice(['tunai', 'kartu', 'dompet', 'dompet']), r.randint(1, 16) * 50000)\n",
     program(r'''
class Pembayaran {
public:
    virtual ~Pembayaran() {
    }

    virtual string nama() const {
        return "Pembayaran";
    }

    virtual bool bayar(int) {
        return true;
    }
};

class Tunai : public Pembayaran {
public:
    string nama() const override {
        return "Tunai";
    }
};

class Kartu : public Pembayaran {
public:
    string nama() const override {
        return "Kartu";
    }

    bool bayar(int jumlah) override {
        return jumlah <= 500000;
    }
};

class Dompet : public Pembayaran {
private:
    int saldo;

public:
    Dompet(int s) : saldo(s) {
    }

    string nama() const override {
        return "Dompet";
    }

    bool bayar(int jumlah) override {
        if (jumlah > saldo) {
            return false;
        }
        saldo -= jumlah;
        return true;
    }
};''', r'''    int saldo, n;
    cin >> saldo >> n;
    string kata[3] = {"tunai", "kartu", "dompet"};
    Pembayaran *metode[3] = {new Tunai, new Kartu, new Dompet(saldo)};
    auto terbayar = 0;
    for (int k = 0; k < n; k++) {
        string pilihan;
        int jumlah;
        cin >> pilihan >> jumlah;
        for (int j = 0; j < 3; j++) {
            if (kata[j] != pilihan) {
                continue;
            }
            if (metode[j]->bayar(jumlah)) {
                terbayar += jumlah;
                cout << metode[j]->nama() << ": berhasil" << endl;
            } else {
                cout << metode[j]->nama() << ": ditolak" << endl;
            }
        }
    }
    cout << "Terbayar: " << terbayar << endl;
    for (int j = 0; j < 3; j++) {
        delete metode[j];
    }''', "string"), wajib=W,
     id=("Metode Pembayaran", "Sebuah kasir menerima tiga cara bayar. Buat class `Pembayaran` dengan `virtual string nama() const` dan `virtual bool bayar(int jumlah)` (bawaannya selalu berhasil), dan tiga turunan:\n\n"
         "- `Tunai` — nama `Tunai`; `bayar` bawaan;\n- `Kartu` — nama `Kartu`; satu transaksi paling banyak 500000, selebihnya ditolak;\n- `Dompet` — nama `Dompet`; punya `int saldo`; ditolak bila jumlah melebihi saldo, selain itu saldo berkurang.\n\n"
         "Kasir memegang **satu objek untuk tiap cara** (dompet yang sama dipakai terus) di larik `Pembayaran *metode[3]`.\n\n"
         "Masukan: saldo awal dompet; n; lalu n transaksi `<tunai|kartu|dompet> <jumlah>`. Untuk tiap transaksi cetak `<Nama>: berhasil` atau `<Nama>: ditolak`. Di akhir cetak `Terbayar: T` — jumlah semua transaksi yang berhasil.\n\n"
         "Contoh: saldo 300000; `dompet 200000`, `dompet 150000`, `kartu 600000`, `tunai 50000` →\n\n```\nDompet: berhasil\nDompet: ditolak\nKartu: ditolak\nTunai: berhasil\nTerbayar: 250000\n```" + W_ID,
         "Baris pertama: saldo dompet. Baris kedua: n. Lalu n baris transaksi.", "Satu baris per transaksi, lalu `Terbayar: T`.", "1 ≤ n ≤ 10; semua jumlah kelipatan 50000, paling banyak 1000000"),
     en=("Ways to Pay", "A till accepts three ways to pay. Make a class `Pembayaran` (payment) with `virtual string nama() const` and `virtual bool bayar(int jumlah)` (pay; by default it always succeeds), and three derived classes:\n\n"
         "- `Tunai` (cash) — name `Tunai`; the default `bayar`;\n- `Kartu` (card) — name `Kartu`; one transaction may be at most 500000, anything more is refused;\n- `Dompet` (wallet) — name `Dompet`; has `int saldo`; refused when the amount exceeds the balance, otherwise the balance drops.\n\n"
         "The till holds **one object per way** (the same wallet is used throughout) in an array `Pembayaran *metode[3]`.\n\n"
         "Input: the wallet's opening balance; n; then n transactions `<tunai|kartu|dompet> <amount>`. For each transaction print `<Name>: berhasil` (succeeded) or `<Name>: ditolak` (refused). At the end print `Terbayar: T` — the sum of all transactions that succeeded.\n\n"
         "Example: balance 300000; `dompet 200000`, `dompet 150000`, `kartu 600000`, `tunai 50000` →\n\n```\nDompet: berhasil\nDompet: ditolak\nKartu: ditolak\nTunai: berhasil\nTerbayar: 250000\n```" + W_EN,
         "First line: the wallet balance. Second line: n. Then n transaction lines.", "One line per transaction, then `Terbayar: T`.", "1 ≤ n ≤ 10; all amounts are multiples of 50000, at most 1000000"),
     ar=("طرق الدفع", "صندوق يقبل ثلاث طرق للدفع. أنشئ صنفًا `Pembayaran` (دفع) فيه `virtual string nama() const` و `virtual bool bayar(int jumlah)` (ادفع؛ وتنجح افتراضيًا دائمًا)، وثلاثة مشتقات:\n\n"
         "- `Tunai` (نقدًا) — الاسم `Tunai`؛ و `bayar` الافتراضية؛\n- `Kartu` (بطاقة) — الاسم `Kartu`؛ العملية الواحدة 500000 على الأكثر وما زاد يُرفض؛\n- `Dompet` (محفظة) — الاسم `Dompet`؛ فيها `int saldo`؛ يُرفض إذا زاد المبلغ على الرصيد وإلا نقص الرصيد.\n\n"
         "يمسك الصندوق **كائنًا واحدًا لكل طريقة** (المحفظة نفسها تُستعمل دائمًا) في المصفوفة `Pembayaran *metode[3]`.\n\n"
         "المدخل: الرصيد الابتدائي للمحفظة؛ ثم n؛ ثم n عملية `<tunai|kartu|dompet> <المبلغ>`. لكل عملية اطبع `<الاسم>: berhasil` (نجحت) أو `<الاسم>: ditolak` (رُفضت). في النهاية اطبع `Terbayar: T` — مجموع العمليات الناجحة.\n\n"
         "مثال: الرصيد 300000؛ `dompet 200000` و `dompet 150000` و `kartu 600000` و `tunai 50000` ←\n\n```\nDompet: berhasil\nDompet: ditolak\nKartu: ditolak\nTunai: berhasil\nTerbayar: 250000\n```" + W_AR,
         "السطر الأول: رصيد المحفظة. السطر الثاني: n. ثم n سطر عمليات.", "سطر لكل عملية ثم `Terbayar: T`.", "1 ≤ n ≤ 10؛ كل المبالغ من مضاعفات 50000 وأقصاها 1000000"))

soal("gambar-bentuk", "mudah_sedang", "trimmed", "n = {2: 1}.get(i, r.randint(2, 5))\nprint(n)\nfor _ in range(n):\n    print(r.choice(['garis', 'persegi', 'segitiga']), r.randint(1, 6))\n",
     program(r'''
class Gambar {
protected:
    int ukuran;

public:
    Gambar(int u) : ukuran(u) {
    }

    virtual ~Gambar() {
    }

    virtual string nama() const {
        return "garis";
    }

    void judul() const {
        cout << nama() << " " << ukuran << endl;
    }

    virtual void lukis() const {
        for (int k = 0; k < ukuran; k++) {
            cout << "#";
        }
        cout << endl;
    }
};

class GambarPersegi : public Gambar {
public:
    GambarPersegi(int u) : Gambar(u) {
    }

    string nama() const override {
        return "persegi";
    }

    void lukis() const override {
        for (int b = 0; b < ukuran; b++) {
            Gambar::lukis();
        }
    }
};

class GambarSegitiga : public Gambar {
public:
    GambarSegitiga(int u) : Gambar(u) {
    }

    string nama() const override {
        return "segitiga";
    }

    void lukis() const override {
        for (int b = 1; b <= ukuran; b++) {
            for (int k = 0; k < b; k++) {
                cout << "#";
            }
            cout << endl;
        }
    }
};''', r'''    int n;
    cin >> n;
    Gambar *daftar[5];
    for (int k = 0; k < n; k++) {
        string jenis;
        int ukuran;
        cin >> jenis >> ukuran;
        if (jenis == "persegi") {
            daftar[k] = new GambarPersegi(ukuran);
        } else if (jenis == "segitiga") {
            daftar[k] = new GambarSegitiga(ukuran);
        } else {
            daftar[k] = new Gambar(ukuran);
        }
    }
    for (int k = 0; k < n; k++) {
        daftar[k]->judul();
        daftar[k]->lukis();
        delete daftar[k];
    }''', "string"), wajib=W,
     id=("Melukis Bentuk", "Buat class `Gambar` (atribut `protected` `int ukuran`) dengan:\n\n- `virtual string nama() const` → `garis`;\n- method biasa `void judul() const` yang mencetak `<nama()> <ukuran>`;\n- `virtual void lukis() const` yang mencetak satu baris berisi `ukuran` tanda `#`.\n\nDua turunannya:\n\n"
         "- `GambarPersegi` — nama `persegi`; `lukis()` mencetak `ukuran` baris, tiap baris `ukuran` tanda `#` (boleh dengan memanggil `Gambar::lukis()` berulang);\n"
         "- `GambarSegitiga` — nama `segitiga`; `lukis()` mencetak baris berisi 1, 2, …, `ukuran` tanda `#`.\n\n"
         "Masukan: n gambar (`garis`, `persegi`, atau `segitiga`, diikuti ukuran). Simpan di larik `Gambar *`; lalu untuk tiap gambar panggil `judul()` dan `lukis()`.\n\n"
         "Contoh: `segitiga 3`, `garis 4` →\n\n```\nsegitiga 3\n#\n##\n###\ngaris 4\n####\n```" + W_ID,
         "Baris pertama: n. Lalu n baris: jenis dan ukuran.", "Untuk tiap gambar: satu baris judul, lalu lukisannya.", "1 ≤ n ≤ 5; 1 ≤ ukuran ≤ 6"),
     en=("Drawing Shapes", "Make a class `Gambar` (picture; `protected` attribute `int ukuran`, the size) with:\n\n- `virtual string nama() const` → `garis` (line);\n- an ordinary method `void judul() const` (title) that prints `<nama()> <size>`;\n- `virtual void lukis() const` (draw) that prints one line of `ukuran` `#` signs.\n\nTwo derived classes:\n\n"
         "- `GambarPersegi` — name `persegi`; `lukis()` prints `ukuran` lines of `ukuran` `#` signs each (calling `Gambar::lukis()` repeatedly is fine);\n"
         "- `GambarSegitiga` — name `segitiga`; `lukis()` prints lines of 1, 2, …, `ukuran` `#` signs.\n\n"
         "Input: n pictures (`garis`, `persegi` or `segitiga`, followed by the size). Keep them in an array of `Gambar *`; then for each picture call `judul()` and `lukis()`.\n\n"
         "Example: `segitiga 3`, `garis 4` →\n\n```\nsegitiga 3\n#\n##\n###\ngaris 4\n####\n```" + W_EN,
         "First line: n. Then n lines: kind and size.", "For each picture: one title line, then its drawing.", "1 ≤ n ≤ 5; 1 ≤ size ≤ 6"),
     ar=("رسم الأشكال", "أنشئ صنفًا `Gambar` (رسم؛ الخاصية المحمية `int ukuran` أي الحجم) فيه:\n\n- `virtual string nama() const` ← `garis` (خط)؛\n- طريقة عادية `void judul() const` (العنوان) تطبع `<nama()> <الحجم>`؛\n- `virtual void lukis() const` (ارسم) تطبع سطرًا واحدًا فيه `ukuran` علامة `#`.\n\nمشتقان:\n\n"
         "- `GambarPersegi` — الاسم `persegi`؛ و `lukis()` تطبع `ukuran` سطرًا في كل منها `ukuran` علامة `#` (ويجوز ذلك باستدعاء `Gambar::lukis()` مرارًا)؛\n"
         "- `GambarSegitiga` — الاسم `segitiga`؛ و `lukis()` تطبع أسطرًا فيها 1 ثم 2 … ثم `ukuran` علامة `#`.\n\n"
         "المدخل: n رسمًا (`garis` أو `persegi` أو `segitiga` يليه الحجم). احفظها في مصفوفة `Gambar *`؛ ثم لكل رسم استدعِ `judul()` و `lukis()`.\n\n"
         "مثال: `segitiga 3` و `garis 4` ←\n\n```\nsegitiga 3\n#\n##\n###\ngaris 4\n####\n```" + W_AR,
         "السطر الأول: n. ثم n سطرًا: النوع والحجم.", "لكل رسم: سطر العنوان ثم الرسم.", "1 ≤ n ≤ 5؛ 1 ≤ الحجم ≤ 6"))

# ================================ SEDANG (6) ================================

soal("batal-perintah", "sedang", "trimmed",
     "print(r.randint(-20, 20))\nkali = 0\nfor _ in range(r.randint(1, 16)):\n    q = r.random()\n    if q < 0.45:\n        print('tambah', r.randint(-50, 50))\n    elif q < 0.65 and kali < 6:\n        kali += 1\n        print('kali', r.randint(2, 4))\n    else:\n        print('batal')\nprint('selesai')\n",
     program(r'''
class Perintah {
public:
    virtual ~Perintah() {
    }

    virtual int jalankan(int nilai) const {
        return nilai;
    }

    virtual int batalkan(int nilai) const {
        return nilai;
    }
};

class Tambah : public Perintah {
private:
    int x;

public:
    Tambah(int a) : x(a) {
    }

    int jalankan(int nilai) const override {
        return nilai + x;
    }

    int batalkan(int nilai) const override {
        return nilai - x;
    }
};

class Kali : public Perintah {
private:
    int x;

public:
    Kali(int a) : x(a) {
    }

    int jalankan(int nilai) const override {
        return nilai * x;
    }

    int batalkan(int nilai) const override {
        return nilai / x;
    }
};''', r'''    int nilai;
    cin >> nilai;
    Perintah *riwayat[16];
    auto banyak = 0;
    string kata;
    while (cin >> kata) {
        if (kata == "selesai") {
            break;
        }
        if (kata == "batal") {
            if (banyak == 0) {
                cout << "Tidak ada yang dibatalkan" << endl;
                continue;
            }
            banyak--;
            nilai = riwayat[banyak]->batalkan(nilai);
            delete riwayat[banyak];
        } else {
            int x;
            cin >> x;
            if (kata == "tambah") {
                riwayat[banyak] = new Tambah(x);
            } else {
                riwayat[banyak] = new Kali(x);
            }
            nilai = riwayat[banyak]->jalankan(nilai);
            banyak++;
        }
        cout << nilai << endl;
    }
    cout << "Akhir: " << nilai << ", riwayat " << banyak << endl;
    for (int k = 0; k < banyak; k++) {
        delete riwayat[k];
    }''', "string"), wajib=W,
     id=("Perintah yang Bisa Dibatalkan", "Tombol *undo* bekerja karena setiap perintah **disimpan sebagai objek** yang tahu cara menjalankan dan cara membatalkan dirinya.\n\n"
         "Buat class `Perintah` dengan `virtual int jalankan(int nilai) const` dan `virtual int batalkan(int nilai) const` (keduanya mengembalikan nilai baru). Turunannya:\n\n"
         "- `Tambah` (menyimpan x) — menjalankan: nilai + x; membatalkan: nilai − x;\n- `Kali` (menyimpan x) — menjalankan: nilai × x; membatalkan: nilai / x.\n\n"
         "Program memegang satu bilangan dan riwayat berupa **tumpukan** `Perintah *riwayat[16]`.\n\n"
         "Masukan: nilai awal; lalu perintah:\n\n- `tambah x` / `kali x` — buat objeknya, jalankan, simpan di riwayat, cetak nilai sekarang;\n- `batal` — ambil perintah **terakhir** dari riwayat, batalkan, `delete`, cetak nilai sekarang; bila riwayat kosong cetak `Tidak ada yang dibatalkan`;\n- `selesai` — cetak `Akhir: <nilai>, riwayat <banyak perintah tersisa>`.\n\n"
         "Contoh: nilai awal 5; `tambah 3`, `kali 4`, `batal`, `batal`, `batal`, `selesai` →\n\n```\n8\n32\n8\n5\nTidak ada yang dibatalkan\nAkhir: 5, riwayat 0\n```" + W_ID,
         "Baris pertama: nilai awal. Lalu perintah-perintah, diakhiri `selesai`.", "Satu baris per perintah, lalu baris akhir.", "Paling banyak 16 perintah; −50 ≤ x ≤ 50 untuk `tambah`; 2 ≤ x ≤ 4 untuk `kali`, paling banyak 6 kali"),
     en=("Commands that can be Undone", "An *undo* button works because every command is **kept as an object** that knows how to carry itself out and how to undo itself.\n\n"
         "Make a class `Perintah` (command) with `virtual int jalankan(int nilai) const` (run) and `virtual int batalkan(int nilai) const` (undo); both return the new value. Its derived classes:\n\n"
         "- `Tambah` (keeps x) — run: value + x; undo: value − x;\n- `Kali` (keeps x) — run: value × x; undo: value / x.\n\n"
         "The program holds one number and a history that is a **stack** `Perintah *riwayat[16]`.\n\n"
         "Input: the starting value; then commands:\n\n- `tambah x` / `kali x` — create the object, run it, keep it in the history, print the current value;\n- `batal` — take the **last** command from the history, undo it, `delete` it, print the current value; when the history is empty print `Tidak ada yang dibatalkan` (nothing to undo);\n- `selesai` — print `Akhir: <value>, riwayat <commands left>`.\n\n"
         "Example: starting value 5; `tambah 3`, `kali 4`, `batal`, `batal`, `batal`, `selesai` →\n\n```\n8\n32\n8\n5\nTidak ada yang dibatalkan\nAkhir: 5, riwayat 0\n```" + W_EN,
         "First line: the starting value. Then the commands, ending with `selesai`.", "One line per command, then the closing line.", "At most 16 commands; −50 ≤ x ≤ 50 for `tambah`; 2 ≤ x ≤ 4 for `kali`, at most 6 times"),
     ar=("أوامر يمكن التراجع عنها", "زر *التراجع* يعمل لأن كل أمر **يُحفظ كائنًا** يعرف كيف ينفّذ نفسه وكيف يتراجع عنها.\n\n"
         "أنشئ صنفًا `Perintah` (أمر) فيه `virtual int jalankan(int nilai) const` (نفِّذ) و `virtual int batalkan(int nilai) const` (تراجع)؛ وكلتاهما تُرجع القيمة الجديدة. ومشتقاه:\n\n"
         "- `Tambah` (يحفظ x) — التنفيذ: القيمة + x؛ التراجع: القيمة − x؛\n- `Kali` (يحفظ x) — التنفيذ: القيمة × x؛ التراجع: القيمة / x.\n\n"
         "يمسك البرنامج عددًا واحدًا وسجلًّا هو **مكدّس** `Perintah *riwayat[16]`.\n\n"
         "المدخل: القيمة الابتدائية؛ ثم أوامر:\n\n- `tambah x` / `kali x` — أنشئ الكائن ونفّذه واحفظه في السجل واطبع القيمة الحالية؛\n- `batal` — خذ الأمر **الأخير** من السجل وتراجع عنه واحذفه بـ `delete` واطبع القيمة الحالية؛ وإذا كان السجل فارغًا فاطبع `Tidak ada yang dibatalkan` (لا شيء يُتراجع عنه)؛\n- `selesai` — اطبع `Akhir: <القيمة>, riwayat <عدد الأوامر الباقية>`.\n\n"
         "مثال: القيمة الابتدائية 5؛ `tambah 3` ثم `kali 4` ثم `batal` ثم `batal` ثم `batal` ثم `selesai` ←\n\n```\n8\n32\n8\n5\nTidak ada yang dibatalkan\nAkhir: 5, riwayat 0\n```" + W_AR,
         "السطر الأول: القيمة الابتدائية. ثم الأوامر منتهيةً بـ `selesai`.", "سطر لكل أمر ثم السطر الختامي.", "16 أمرًا على الأكثر؛ −50 ≤ x ≤ 50 في `tambah`؛ 2 ≤ x ≤ 4 في `kali` وست مرات على الأكثر"))

soal("filter-berantai", "sedang", "trimmed",
     "f = {2: 1}.get(i, r.randint(2, 5))\nprint(f)\nfor _ in range(f):\n    j = r.choice(['kali', 'tambah', 'batas', 'mutlak'])\n    if j == 'kali':\n        print(j, r.randint(-5, 5))\n    elif j == 'tambah':\n        print(j, r.randint(-20, 20))\n    elif j == 'batas':\n        a = r.randint(-30, 20)\n        print(j, a, a + r.randint(1, 40))\n    else:\n        print(j)\nm = r.randint(1, 8)\nprint(m)\nprint(*[r.randint(-50, 50) for _ in range(m)])\n",
     program(r'''
class Filter {
public:
    virtual ~Filter() {
    }

    virtual int proses(int x) const {
        return x;
    }
};

class FilterKali : public Filter {
private:
    int k;

public:
    FilterKali(int a) : k(a) {
    }

    int proses(int x) const override {
        return x * k;
    }
};

class FilterTambah : public Filter {
private:
    int k;

public:
    FilterTambah(int a) : k(a) {
    }

    int proses(int x) const override {
        return x + k;
    }
};

class FilterBatas : public Filter {
private:
    int bawah;
    int atas;

public:
    FilterBatas(int b, int a) : bawah(b), atas(a) {
    }

    int proses(int x) const override {
        if (x < bawah) {
            return bawah;
        }
        if (x > atas) {
            return atas;
        }
        return x;
    }
};

class FilterMutlak : public Filter {
public:
    int proses(int x) const override {
        if (x < 0) {
            return -x;
        }
        return x;
    }
};''', r'''    int f;
    cin >> f;
    Filter *rantai[5];
    for (int k = 0; k < f; k++) {
        string jenis;
        cin >> jenis;
        if (jenis == "kali") {
            int a;
            cin >> a;
            rantai[k] = new FilterKali(a);
        } else if (jenis == "tambah") {
            int a;
            cin >> a;
            rantai[k] = new FilterTambah(a);
        } else if (jenis == "batas") {
            int a, b;
            cin >> a >> b;
            rantai[k] = new FilterBatas(a, b);
        } else {
            rantai[k] = new FilterMutlak;
        }
    }
    int m;
    cin >> m;
    for (int j = 0; j < m; j++) {
        int x;
        cin >> x;
        for (int k = 0; k < f; k++) {
            x = rantai[k]->proses(x);
        }
        cout << x << endl;
    }
    for (int k = 0; k < f; k++) {
        delete rantai[k];
    }''', "string"), wajib=W,
     id=("Filter Berantai", "Sebuah **rantai filter** mengolah bilangan selangkah demi selangkah: keluaran filter pertama menjadi masukan filter kedua, dan seterusnya. Susunan rantainya baru diketahui saat program berjalan.\n\n"
         "Buat class `Filter` dengan `virtual int proses(int x) const` (bawaannya mengembalikan x), dan empat turunan:\n\n"
         "- `FilterKali` (k) — x × k;\n- `FilterTambah` (k) — x + k;\n- `FilterBatas` (bawah, atas) — x dijepit ke rentang itu: di bawah `bawah` menjadi `bawah`, di atas `atas` menjadi `atas`;\n- `FilterMutlak` — nilai mutlak x.\n\n"
         "Masukan: f, lalu f baris filter — `kali k`, `tambah k`, `batas bawah atas`, atau `mutlak` — yang disimpan berurutan di `Filter *rantai[5]`. Lalu m dan m bilangan. Lewatkan tiap bilangan melalui seluruh rantai dan cetak hasilnya.\n\n"
         "Contoh: rantai `kali 2`, `tambah -5`, `batas 0 10`; bilangan `1 4 9` →\n\n```\n0\n3\n10\n```" + W_ID,
         "Baris pertama: f. Lalu f baris filter. Lalu m, dan satu baris berisi m bilangan.", "m baris.", "1 ≤ f ≤ 5; 1 ≤ m ≤ 8; bilangan antara −50 dan 50; −5 ≤ k ≤ 5 untuk `kali`; −20 ≤ k ≤ 20 untuk `tambah`; bawah < atas"),
     en=("A Chain of Filters", "A **chain of filters** processes a number step by step: the output of the first filter is the input of the second, and so on. The make-up of the chain is known only when the program runs.\n\n"
         "Make a class `Filter` with `virtual int proses(int x) const` (process; by default it returns x), and four derived classes:\n\n"
         "- `FilterKali` (k) — x × k;\n- `FilterTambah` (k) — x + k;\n- `FilterBatas` (low, high) — x clamped to that range: below `bawah` becomes `bawah`, above `atas` becomes `atas`;\n- `FilterMutlak` — the absolute value of x.\n\n"
         "Input: f, then f filter lines — `kali k`, `tambah k`, `batas low high`, or `mutlak` — kept in order in `Filter *rantai[5]`. Then m and m numbers. Pass each number through the whole chain and print the result.\n\n"
         "Example: the chain `kali 2`, `tambah -5`, `batas 0 10`; numbers `1 4 9` →\n\n```\n0\n3\n10\n```" + W_EN,
         "First line: f. Then f filter lines. Then m, and one line of m numbers.", "m lines.", "1 ≤ f ≤ 5; 1 ≤ m ≤ 8; numbers are between −50 and 50; −5 ≤ k ≤ 5 for `kali`; −20 ≤ k ≤ 20 for `tambah`; low < high"),
     ar=("سلسلة مرشِّحات", "**سلسلة المرشِّحات** تعالج العدد خطوة خطوة: مخرج المرشِّح الأول مدخل الثاني وهكذا. وتركيب السلسلة لا يُعرف إلا عند تشغيل البرنامج.\n\n"
         "أنشئ صنفًا `Filter` فيه `virtual int proses(int x) const` (عالِج؛ وتُرجع افتراضيًا x)، وأربعة مشتقات:\n\n"
         "- `FilterKali` (k) — x × k؛\n- `FilterTambah` (k) — x + k؛\n- `FilterBatas` (الأدنى، الأعلى) — يُحصر x في ذلك المجال: ما دون `bawah` يصير `bawah` وما فوق `atas` يصير `atas`؛\n- `FilterMutlak` — القيمة المطلقة لـ x.\n\n"
         "المدخل: f ثم f سطر مرشِّحات — `kali k` أو `tambah k` أو `batas الأدنى الأعلى` أو `mutlak` — تُحفظ بالترتيب في `Filter *rantai[5]`. ثم m و m عددًا. مرِّر كل عدد عبر السلسلة كلها واطبع الناتج.\n\n"
         "مثال: السلسلة `kali 2` ثم `tambah -5` ثم `batas 0 10`؛ والأعداد `1 4 9` ←\n\n```\n0\n3\n10\n```" + W_AR,
         "السطر الأول: f. ثم f سطر مرشِّحات. ثم m وسطر فيه m عددًا.", "m سطرًا.", "1 ≤ f ≤ 5؛ 1 ≤ m ≤ 8؛ الأعداد بين −50 و 50؛ −5 ≤ k ≤ 5 في `kali`؛ −20 ≤ k ≤ 20 في `tambah`؛ الأدنى < الأعلى"))

soal("pabrik-hewan", "sedang", "trimmed", HEWAN + "n = {2: 1}.get(i, r.randint(2, 8))\nprint(n)\nfor nama in r.sample(HEWAN, n):\n    print(r.choice(['kucing', 'anjing', 'burung', 'kucing', 'anjing', 'burung', 'naga', 'kuda']), nama)\n",
     program(r'''
class Hewan {
protected:
    string nama;

public:
    Hewan(const string &n) : nama(n) {
    }

    virtual ~Hewan() {
        cout << nama << " pulang" << endl;
    }

    string getNama() const {
        return nama;
    }

    virtual string suara() const {
        return "...";
    }
};

class Kucing : public Hewan {
public:
    Kucing(const string &n) : Hewan(n) {
    }

    string suara() const override {
        return "meong";
    }
};

class Anjing : public Hewan {
public:
    Anjing(const string &n) : Hewan(n) {
    }

    string suara() const override {
        return "guk";
    }
};

class Burung : public Hewan {
public:
    Burung(const string &n) : Hewan(n) {
    }

    string suara() const override {
        return "cuit";
    }
};

Hewan *buat(const string &jenis, const string &nama) {
    if (jenis == "kucing") {
        return new Kucing(nama);
    }
    if (jenis == "anjing") {
        return new Anjing(nama);
    }
    if (jenis == "burung") {
        return new Burung(nama);
    }
    return nullptr;
}''', r'''    int n;
    cin >> n;
    Hewan *daftar[8];
    auto banyak = 0;
    for (int k = 0; k < n; k++) {
        string jenis, nama;
        cin >> jenis >> nama;
        Hewan *hewan = buat(jenis, nama);
        if (hewan == nullptr) {
            cout << "Jenis " << jenis << " tidak dikenal" << endl;
            continue;
        }
        cout << hewan->getNama() << ": " << hewan->suara() << endl;
        daftar[banyak] = hewan;
        banyak++;
    }
    cout << "Dibuat: " << banyak << endl;
    for (int k = 0; k < banyak; k++) {
        delete daftar[k];
    }''', "string"), wajib=W,
     id=("Pabrik Hewan", "Satu-satunya tempat yang perlu tahu nama-nama class turunan adalah tempat objek **dibuat**. Kumpulkan pengetahuan itu di satu fungsi \"pabrik\"; sisa program cukup mengenal `Hewan *`.\n\n"
         "Buat class `Hewan` (atribut `protected` `string nama`; `getNama()`; `virtual string suara() const`; **destruktor virtual** yang mencetak `<nama> pulang`) dan turunannya `Kucing` (`meong`), `Anjing` (`guk`), `Burung` (`cuit`).\n\n"
         "Tulis fungsi `Hewan *buat(const string &jenis, const string &nama)` yang mengembalikan objek baru untuk `kucing`, `anjing`, `burung`, dan **`nullptr`** untuk jenis lain.\n\n"
         "Masukan: n baris `jenis nama`. Untuk tiap baris panggil `buat`. Bila hasilnya `nullptr` cetak `Jenis <jenis> tidak dikenal`; selain itu cetak `<nama>: <suara>` dan simpan pointernya. Sesudah semua baris, cetak `Dibuat: <banyak>`, lalu `delete` semua hewan menurut urutan dibuat.\n\n"
         "Contoh: `kucing Oyen`, `naga Api`, `burung Kiki` →\n\n```\nOyen: meong\nJenis naga tidak dikenal\nKiki: cuit\nDibuat: 2\nOyen pulang\nKiki pulang\n```" + W_ID,
         "Baris pertama: n. Lalu n baris: jenis dan nama.", "Baris-baris seperti contoh.", "1 ≤ n ≤ 8; jenis satu kata huruf kecil; nama berbeda-beda."),
     en=("An Animal Factory", "The only place that has to know the names of the derived classes is the place where objects are **created**. Gather that knowledge in one \"factory\" function; the rest of the program only knows `Hewan *`.\n\n"
         "Make a class `Hewan` (`protected` attribute `string nama`; `getNama()`; `virtual string suara() const`; a **virtual destructor** that prints `<name> pulang` (goes home)) and its derived classes `Kucing` (`meong`), `Anjing` (`guk`), `Burung` (`cuit`).\n\n"
         "Write a function `Hewan *buat(const string &jenis, const string &nama)` (make) that returns a new object for `kucing`, `anjing`, `burung`, and **`nullptr`** for any other kind.\n\n"
         "Input: n lines `kind name`. For each line call `buat`. If the result is `nullptr` print `Jenis <kind> tidak dikenal` (unknown kind); otherwise print `<name>: <sound>` and keep the pointer. After all lines print `Dibuat: <count>` (created), then `delete` all animals in the order they were created.\n\n"
         "Example: `kucing Oyen`, `naga Api`, `burung Kiki` →\n\n```\nOyen: meong\nJenis naga tidak dikenal\nKiki: cuit\nDibuat: 2\nOyen pulang\nKiki pulang\n```" + W_EN,
         "First line: n. Then n lines: kind and name.", "Lines as in the example.", "1 ≤ n ≤ 8; the kind is one lowercase word; all names differ."),
     ar=("مصنع الحيوانات", "الموضع الوحيد الذي يلزمه أن يعرف أسماء الأصناف المشتقة هو موضع **إنشاء** الكائنات. اجمع تلك المعرفة في دالة «مصنع» واحدة؛ وبقية البرنامج يكفيها أن تعرف `Hewan *`.\n\n"
         "أنشئ صنفًا `Hewan` (الخاصية المحمية `string nama`؛ و `getNama()`؛ و `virtual string suara() const`؛ و**هادم افتراضي** يطبع `<الاسم> pulang` أي عاد إلى بيته) ومشتقاته `Kucing` (`meong`) و `Anjing` (`guk`) و `Burung` (`cuit`).\n\n"
         "اكتب دالة `Hewan *buat(const string &jenis, const string &nama)` (اصنع) تُرجع كائنًا جديدًا عند `kucing` و `anjing` و `burung`، و**`nullptr`** عند أي نوع آخر.\n\n"
         "المدخل: n سطرًا `النوع الاسم`. لكل سطر استدعِ `buat`. إذا كان الناتج `nullptr` فاطبع `Jenis <النوع> tidak dikenal` (نوع غير معروف)؛ وإلا فاطبع `<الاسم>: <الصوت>` واحفظ المؤشر. بعد كل الأسطر اطبع `Dibuat: <العدد>` (أُنشئ) ثم احذف كل الحيوانات بـ `delete` بترتيب إنشائها.\n\n"
         "مثال: `kucing Oyen` و `naga Api` و `burung Kiki` ←\n\n```\nOyen: meong\nJenis naga tidak dikenal\nKiki: cuit\nDibuat: 2\nOyen pulang\nKiki pulang\n```" + W_AR,
         "السطر الأول: n. ثم n سطرًا: النوع والاسم.", "أسطر كما في المثال.", "1 ≤ n ≤ 8؛ النوع كلمة واحدة بحروف صغيرة؛ والأسماء مختلفة."))

soal("suit-batu-gunting-kertas", "sedang", "trimmed", "n = {2: 1}.get(i, r.randint(2, 10))\nprint(n)\nfor _ in range(n):\n    print(r.choice(['batu', 'gunting', 'kertas']), r.choice(['batu', 'gunting', 'kertas']))\n",
     program(r'''
class Tangan {
public:
    virtual ~Tangan() {
    }

    virtual string nama() const {
        return "?";
    }

    virtual string mangsa() const {
        return "";
    }

    bool mengalahkan(const Tangan &lain) const {
        return mangsa() == lain.nama();
    }
};

class Batu : public Tangan {
public:
    string nama() const override {
        return "batu";
    }

    string mangsa() const override {
        return "gunting";
    }
};

class Gunting : public Tangan {
public:
    string nama() const override {
        return "gunting";
    }

    string mangsa() const override {
        return "kertas";
    }
};

class Kertas : public Tangan {
public:
    string nama() const override {
        return "kertas";
    }

    string mangsa() const override {
        return "batu";
    }
};

Tangan *buat(const string &kata) {
    if (kata == "batu") {
        return new Batu;
    }
    if (kata == "gunting") {
        return new Gunting;
    }
    return new Kertas;
}''', r'''    int n;
    cin >> n;
    auto menangA = 0;
    auto menangB = 0;
    auto seri = 0;
    for (int k = 0; k < n; k++) {
        string a, b;
        cin >> a >> b;
        Tangan *tanganA = buat(a);
        Tangan *tanganB = buat(b);
        if (tanganA->mengalahkan(*tanganB)) {
            menangA++;
            cout << "A menang" << endl;
        } else if (tanganB->mengalahkan(*tanganA)) {
            menangB++;
            cout << "B menang" << endl;
        } else {
            seri++;
            cout << "Seri" << endl;
        }
        delete tanganA;
        delete tanganB;
    }
    cout << "A " << menangA << ", B " << menangB << ", seri " << seri << endl;''', "string"), wajib=W,
     id=("Batu Gunting Kertas", "Dua pemain, A dan B, bermain suit n ronde: batu mengalahkan gunting, gunting mengalahkan kertas, kertas mengalahkan batu.\n\n"
         "Buat class `Tangan` dengan dua method virtual — `string nama() const` dan `string mangsa() const` (nama tangan yang dikalahkannya) — dan satu method **biasa**:\n\n```\nbool mengalahkan(const Tangan &lain) const {\n    return mangsa() == lain.nama();\n}\n```\n\n"
         "Buat turunannya `Batu`, `Gunting`, dan `Kertas`, yang masing-masing hanya menimpa `nama()` dan `mangsa()`. Aturan permainan tidak ditulis sebagai percabangan sembilan kemungkinan; ia tersebar di tiga class kecil.\n\n"
         "Tulis fungsi pabrik `Tangan *buat(const string &kata)`.\n\nMasukan: n ronde, tiap baris berisi pilihan A lalu pilihan B. Cetak `A menang`, `B menang`, atau `Seri` untuk tiap ronde; lalu ringkasan `A <a>, B <b>, seri <s>`.\n\n"
         "Contoh: `batu gunting`, `kertas gunting`, `batu batu` →\n\n```\nA menang\nB menang\nSeri\nA 1, B 1, seri 1\n```" + W_ID,
         "Baris pertama: n. Lalu n baris: pilihan A dan pilihan B.", "Satu baris per ronde, lalu ringkasan.", "1 ≤ n ≤ 10; pilihan `batu`, `gunting`, atau `kertas`"),
     en=("Rock Paper Scissors", "Two players, A and B, play n rounds: rock (`batu`) beats scissors (`gunting`), scissors beat paper (`kertas`), paper beats rock.\n\n"
         "Make a class `Tangan` (hand) with two virtual methods — `string nama() const` and `string mangsa() const` (prey: the name of the hand it beats) — and one **ordinary** method:\n\n```\nbool mengalahkan(const Tangan &lain) const {\n    return mangsa() == lain.nama();\n}\n```\n\n"
         "Make its derived classes `Batu`, `Gunting` and `Kertas`, each overriding only `nama()` and `mangsa()`. The rule of the game is not written as a branch over nine cases; it is spread over three small classes.\n\n"
         "Write a factory function `Tangan *buat(const string &kata)`.\n\nInput: n rounds, each line holding A's choice and then B's. Print `A menang` (A wins), `B menang`, or `Seri` (draw) for each round; then the summary `A <a>, B <b>, seri <s>`.\n\n"
         "Example: `batu gunting`, `kertas gunting`, `batu batu` →\n\n```\nA menang\nB menang\nSeri\nA 1, B 1, seri 1\n```" + W_EN,
         "First line: n. Then n lines: A's choice and B's choice.", "One line per round, then the summary.", "1 ≤ n ≤ 10; a choice is `batu`, `gunting` or `kertas`"),
     ar=("حجر ورقة مقص", "لاعبان A و B يلعبان n جولة: الحجر (`batu`) يغلب المقص (`gunting`)، والمقص يغلب الورقة (`kertas`)، والورقة تغلب الحجر.\n\n"
         "أنشئ صنفًا `Tangan` (يد) فيه طريقتان افتراضيتان — `string nama() const` و `string mangsa() const` (الفريسة: اسم اليد التي تغلبها) — وطريقة **عادية** واحدة:\n\n```\nbool mengalahkan(const Tangan &lain) const {\n    return mangsa() == lain.nama();\n}\n```\n\n"
         "أنشئ مشتقاته `Batu` و `Gunting` و `Kertas`، وكلٌّ منها يعيد تعريف `nama()` و `mangsa()` فقط. قاعدة اللعبة لا تُكتب تفريعًا على تسع حالات؛ بل تتوزع على ثلاثة أصناف صغيرة.\n\n"
         "اكتب دالة مصنع `Tangan *buat(const string &kata)`.\n\nالمدخل: n جولة، في كل سطر اختيار A ثم اختيار B. اطبع `A menang` (فاز A) أو `B menang` أو `Seri` (تعادل) لكل جولة؛ ثم الملخص `A <a>, B <b>, seri <s>`.\n\n"
         "مثال: `batu gunting` و `kertas gunting` و `batu batu` ←\n\n```\nA menang\nB menang\nSeri\nA 1, B 1, seri 1\n```" + W_AR,
         "السطر الأول: n. ثم n سطرًا: اختيار A واختيار B.", "سطر لكل جولة ثم الملخص.", "1 ≤ n ≤ 10؛ الاختيار `batu` أو `gunting` أو `kertas`"))

soal("irisan-larik", "sedang", "trimmed", "n = {2: 1}.get(i, r.randint(2, 8))\nprint(n)\nfor _ in range(n):\n    print(r.choice(['biasa', 'ganda', 'bonus']), r.randint(-20, 50))\n",
     program(r'''
class Nilai {
protected:
    int v;

public:
    Nilai(int x = 0) : v(x) {
    }

    virtual ~Nilai() {
    }

    virtual int hasil() const {
        return v;
    }
};

class Ganda : public Nilai {
public:
    Ganda(int x) : Nilai(x) {
    }

    int hasil() const override {
        return v * 2;
    }
};

class Bonus : public Nilai {
public:
    Bonus(int x) : Nilai(x) {
    }

    int hasil() const override {
        return v + 10;
    }
};''', r'''    int n;
    cin >> n;
    Nilai terpotong[8];
    Nilai *utuh[8];
    for (int k = 0; k < n; k++) {
        string jenis;
        int x;
        cin >> jenis >> x;
        if (jenis == "ganda") {
            terpotong[k] = Ganda(x);
            utuh[k] = new Ganda(x);
        } else if (jenis == "bonus") {
            terpotong[k] = Bonus(x);
            utuh[k] = new Bonus(x);
        } else {
            terpotong[k] = Nilai(x);
            utuh[k] = new Nilai(x);
        }
    }
    auto jumlahTerpotong = 0;
    auto jumlahUtuh = 0;
    for (int k = 0; k < n; k++) {
        jumlahTerpotong += terpotong[k].hasil();
        jumlahUtuh += utuh[k]->hasil();
        delete utuh[k];
    }
    cout << "Terpotong: " << jumlahTerpotong << endl;
    cout << "Utuh: " << jumlahUtuh << endl;''', "string"), wajib=W,
     id=("Larik Objek atau Larik Pointer", "Larik `Nilai terpotong[8]` menyimpan **objek `Nilai`**, tidak lebih. Memasukkan objek turunan ke dalamnya (`terpotong[k] = Ganda(x);`) hanya menyalin bagian `Nilai`-nya: objeknya teriris dan kehilangan perilaku turunan. Larik `Nilai *utuh[8]` menyimpan **alamat**; objeknya tetap utuh.\n\n"
         "Buat class `Nilai` (atribut `protected` `int v`; konstruktor `Nilai(int x = 0)`; `virtual int hasil() const` → v) dan turunannya `Ganda` (`hasil()` → v × 2) dan `Bonus` (`hasil()` → v + 10).\n\n"
         "Masukan: n baris `biasa x`, `ganda x`, atau `bonus x`. Masukkan **setiap** data ke kedua larik: sebagai objek ke `terpotong`, dan sebagai `new …` ke `utuh`. Lalu jumlahkan `hasil()` seluruh isi tiap larik.\n\n"
         "Contoh: `biasa 5`, `ganda 5`, `bonus 5` →\n\n```\nTerpotong: 15\nUtuh: 30\n```" + W_ID,
         "Baris pertama: n. Lalu n baris: jenis dan x.", "Dua baris seperti contoh.", "1 ≤ n ≤ 8; −20 ≤ x ≤ 50"),
     en=("Array of Objects or Array of Pointers", "The array `Nilai terpotong[8]` holds **`Nilai` objects** and nothing more. Putting a derived object into it (`terpotong[k] = Ganda(x);`) copies only its `Nilai` part: the object is sliced and loses its derived behaviour. The array `Nilai *utuh[8]` holds **addresses**; the objects stay whole.\n\n"
         "Make a class `Nilai` (value; `protected` attribute `int v`; a constructor `Nilai(int x = 0)`; `virtual int hasil() const` (result) → v) and its derived classes `Ganda` (double; `hasil()` → v × 2) and `Bonus` (`hasil()` → v + 10).\n\n"
         "Input: n lines `biasa x`, `ganda x`, or `bonus x`. Put **every** item into both arrays: as an object into `terpotong` (sliced), and as `new …` into `utuh` (whole). Then sum `hasil()` over each array.\n\n"
         "Example: `biasa 5`, `ganda 5`, `bonus 5` →\n\n```\nTerpotong: 15\nUtuh: 30\n```" + W_EN,
         "First line: n. Then n lines: kind and x.", "Two lines as in the example.", "1 ≤ n ≤ 8; −20 ≤ x ≤ 50"),
     ar=("مصفوفة كائنات أم مصفوفة مؤشرات", "المصفوفة `Nilai terpotong[8]` تحفظ **كائنات `Nilai`** لا غير. وإدخال كائن مشتق فيها (`terpotong[k] = Ganda(x);`) ينسخ جزء `Nilai` منه فقط: يُقطع الكائن ويفقد سلوك المشتق. أما المصفوفة `Nilai *utuh[8]` فتحفظ **عناوين**؛ وتبقى الكائنات كاملة.\n\n"
         "أنشئ صنفًا `Nilai` (قيمة؛ الخاصية المحمية `int v`؛ وبانٍ `Nilai(int x = 0)`؛ و `virtual int hasil() const` (الناتج) ← v) ومشتقَّيه `Ganda` (مضاعف؛ `hasil()` ← v × 2) و `Bonus` (`hasil()` ← v + 10).\n\n"
         "المدخل: n سطرًا `biasa x` أو `ganda x` أو `bonus x`. أدخل **كل** بيان في المصفوفتين: كائنًا في `terpotong` (مقطوع) و `new …` في `utuh` (كامل). ثم اجمع `hasil()` لكل مصفوفة.\n\n"
         "مثال: `biasa 5` و `ganda 5` و `bonus 5` ←\n\n```\nTerpotong: 15\nUtuh: 30\n```" + W_AR,
         "السطر الأول: n. ثم n سطرًا: النوع و x.", "سطران كما في المثال.", "1 ≤ n ≤ 8؛ −20 ≤ x ≤ 50"))

soal("editor-bentuk", "sedang", "trimmed",
     "banyak = 0\nskala = 0\nfor _ in range(r.randint(2, 16)):\n    q = r.random()\n    if (q < 0.45 or banyak == 0) and banyak < 10:\n        banyak += 1\n        if r.random() < 0.5:\n            print('persegi', r.randint(1, 9))\n        else:\n            print('panjang', r.randint(1, 9), r.randint(1, 9))\n    elif q < 0.75 and skala < 4:\n        skala += 1\n        print('skala', r.randint(1, banyak + 1), r.randint(2, 3))\n    else:\n        print('hapus', r.randint(1, banyak + 1))\nprint('selesai')\n",
     program(r'''
class Bentuk {
public:
    virtual ~Bentuk() {
    }

    virtual string nama() const {
        return "bentuk";
    }

    virtual int luas() const {
        return 0;
    }

    virtual void skala(int) {
    }
};

class Persegi : public Bentuk {
private:
    int sisi;

public:
    Persegi(int s) : sisi(s) {
    }

    string nama() const override {
        return "persegi";
    }

    int luas() const override {
        return sisi * sisi;
    }

    void skala(int k) override {
        sisi *= k;
    }
};

class PersegiPanjang : public Bentuk {
private:
    int panjang;
    int lebar;

public:
    PersegiPanjang(int p, int l) : panjang(p), lebar(l) {
    }

    string nama() const override {
        return "persegi panjang";
    }

    int luas() const override {
        return panjang * lebar;
    }

    void skala(int k) override {
        panjang *= k;
        lebar *= k;
    }
};''', r'''    Bentuk *daftar[10];
    auto banyak = 0;
    string kata;
    while (cin >> kata) {
        if (kata == "selesai") {
            break;
        }
        if (kata == "persegi" || kata == "panjang") {
            if (kata == "persegi") {
                int s;
                cin >> s;
                daftar[banyak] = new Persegi(s);
            } else {
                int p, l;
                cin >> p >> l;
                daftar[banyak] = new PersegiPanjang(p, l);
            }
            banyak++;
            cout << "Bentuk " << banyak << " ditambahkan" << endl;
            continue;
        }
        int nomor;
        cin >> nomor;
        auto ada = nomor >= 1 && nomor <= banyak && daftar[nomor - 1] != nullptr;
        if (kata == "skala") {
            int faktor;
            cin >> faktor;
            if (ada) {
                daftar[nomor - 1]->skala(faktor);
                cout << "Bentuk " << nomor << ": " << daftar[nomor - 1]->nama() << " " << daftar[nomor - 1]->luas() << endl;
            }
        } else if (ada) {
            delete daftar[nomor - 1];
            daftar[nomor - 1] = nullptr;
            cout << "Bentuk " << nomor << " dihapus" << endl;
        }
        if (!ada) {
            cout << "Tidak ada bentuk " << nomor << endl;
        }
    }
    auto total = 0;
    for (int k = 0; k < banyak; k++) {
        if (daftar[k] != nullptr) {
            cout << k + 1 << ". " << daftar[k]->nama() << " " << daftar[k]->luas() << endl;
            total += daftar[k]->luas();
            delete daftar[k];
        }
    }
    cout << "Total luas: " << total << endl;''', "string"), wajib=W,
     id=("Editor Bentuk", "Sebuah editor gambar sederhana menyimpan bentuk-bentuknya di `Bentuk *daftar[10]`. Buat class `Bentuk` dengan tiga method virtual — `string nama() const`, `int luas() const`, `void skala(int k)` — dan turunannya `Persegi` (`persegi`) dan `PersegiPanjang` (`persegi panjang`); `skala` mengalikan setiap ukuran dengan k.\n\n"
         "Bentuk diberi nomor 1, 2, 3, … menurut urutan ditambahkan; nomor tidak pernah dipakai ulang.\n\nPerintah:\n\n"
         "- `persegi <sisi>` / `panjang <panjang> <lebar>` — menambah bentuk → `Bentuk <nomor> ditambahkan`;\n"
         "- `skala <nomor> <k>` — memperbesar → `Bentuk <nomor>: <nama> <luas baru>`;\n- `hapus <nomor>` — `delete` bentuk itu dan mengisi tempatnya dengan `nullptr` → `Bentuk <nomor> dihapus`;\n"
         "- bila nomornya belum ada atau sudah dihapus, `skala` dan `hapus` mencetak `Tidak ada bentuk <nomor>`;\n- `selesai` — cetak bentuk yang masih ada sebagai `<nomor>. <nama> <luas>`, lalu `Total luas: T`.\n\n"
         "Contoh: `persegi 2`, `panjang 2 3`, `skala 1 3`, `hapus 2`, `skala 2 2`, `selesai` →\n\n```\nBentuk 1 ditambahkan\nBentuk 2 ditambahkan\nBentuk 1: persegi 36\nBentuk 2 dihapus\nTidak ada bentuk 2\n1. persegi 36\nTotal luas: 36\n```" + W_ID,
         "Beberapa baris perintah, diakhiri `selesai`.", "Satu baris per perintah, lalu daftar dan total.", "Paling banyak 16 perintah dan 10 bentuk; ukuran 1–9; k adalah 2 atau 3, paling banyak 4 perintah `skala`"),
     en=("A Shape Editor", "A simple drawing editor keeps its shapes in `Bentuk *daftar[10]`. Make a class `Bentuk` with three virtual methods — `string nama() const`, `int luas() const`, `void skala(int k)` — and its derived classes `Persegi` (`persegi`) and `PersegiPanjang` (`persegi panjang`); `skala` multiplies every size by k.\n\n"
         "Shapes are numbered 1, 2, 3, … in the order they are added; a number is never reused.\n\nCommands:\n\n"
         "- `persegi <side>` / `panjang <length> <width>` — add a shape → `Bentuk <number> ditambahkan` (added);\n"
         "- `skala <number> <k>` — enlarge → `Bentuk <number>: <name> <new area>`;\n- `hapus <number>` — `delete` that shape and put `nullptr` in its place → `Bentuk <number> dihapus` (removed);\n"
         "- when the number does not exist yet or was removed, `skala` and `hapus` print `Tidak ada bentuk <number>` (no such shape);\n- `selesai` — print the shapes still present as `<number>. <name> <area>`, then `Total luas: T`.\n\n"
         "Example: `persegi 2`, `panjang 2 3`, `skala 1 3`, `hapus 2`, `skala 2 2`, `selesai` →\n\n```\nBentuk 1 ditambahkan\nBentuk 2 ditambahkan\nBentuk 1: persegi 36\nBentuk 2 dihapus\nTidak ada bentuk 2\n1. persegi 36\nTotal luas: 36\n```" + W_EN,
         "Several command lines, ending with `selesai`.", "One line per command, then the list and the total.", "At most 16 commands and 10 shapes; sizes 1–9; k is 2 or 3, at most 4 `skala` commands"),
     ar=("محرِّر الأشكال", "محرِّر رسم بسيط يحفظ أشكاله في `Bentuk *daftar[10]`. أنشئ صنفًا `Bentuk` فيه ثلاث طرائق افتراضية — `string nama() const` و `int luas() const` و `void skala(int k)` — ومشتقَّيه `Persegi` (`persegi`) و `PersegiPanjang` (`persegi panjang`)؛ و `skala` تضرب كل قياس في k.\n\n"
         "تُرقَّم الأشكال 1 ثم 2 ثم 3 … بترتيب إضافتها؛ ولا يُعاد استعمال رقم أبدًا.\n\nالأوامر:\n\n"
         "- `persegi <الضلع>` / `panjang <الطول> <العرض>` — يضيف شكلًا ← `Bentuk <الرقم> ditambahkan` (أُضيف)؛\n"
         "- `skala <الرقم> <k>` — يكبّر ← `Bentuk <الرقم>: <الاسم> <المساحة الجديدة>`؛\n- `hapus <الرقم>` — يحذف ذلك الشكل بـ `delete` ويضع `nullptr` مكانه ← `Bentuk <الرقم> dihapus` (حُذف)؛\n"
         "- إذا لم يوجد الرقم بعد أو كان محذوفًا طبع `skala` و `hapus` العبارة `Tidak ada bentuk <الرقم>` (لا يوجد شكل)؛\n- `selesai` — اطبع الأشكال الباقية على الصورة `<الرقم>. <الاسم> <المساحة>` ثم `Total luas: T`.\n\n"
         "مثال: `persegi 2` ثم `panjang 2 3` ثم `skala 1 3` ثم `hapus 2` ثم `skala 2 2` ثم `selesai` ←\n\n```\nBentuk 1 ditambahkan\nBentuk 2 ditambahkan\nBentuk 1: persegi 36\nBentuk 2 dihapus\nTidak ada bentuk 2\n1. persegi 36\nTotal luas: 36\n```" + W_AR,
         "عدة أسطر أوامر تنتهي بـ `selesai`.", "سطر لكل أمر ثم القائمة والمجموع.", "16 أمرًا و 10 أشكال على الأكثر؛ القياسات 1–9؛ k يساوي 2 أو 3، وأربعة أوامر `skala` على الأكثر"))

# ================================= SULIT (4) =================================

soal("pohon-ekspresi", "sulit", "trimmed",
     "def buat(dalam):\n    if dalam == 0 or r.random() < 0.25:\n        return [str(r.randint(0, 9))]\n    return [r.choice('+-*')] + buat(dalam - 1) + buat(dalam - 1)\nif i == 2:\n    print(r.randint(0, 9))\nelse:\n    print(*([r.choice('+-*')] + buat(2) + buat(2)))\n",
     program(r'''
class Simpul {
public:
    virtual ~Simpul() {
    }

    virtual int nilai() const {
        return 0;
    }

    virtual void cetak() const {
    }
};

class Angka : public Simpul {
private:
    int v;

public:
    Angka(int x) : v(x) {
    }

    int nilai() const override {
        return v;
    }

    void cetak() const override {
        cout << v;
    }
};

class Biner : public Simpul {
protected:
    Simpul *kiri;
    Simpul *kanan;

public:
    Biner(Simpul *a, Simpul *b) : kiri(a), kanan(b) {
    }

    ~Biner() override {
        delete kiri;
        delete kanan;
    }

    virtual char simbol() const {
        return '?';
    }

    void cetak() const override {
        cout << "(";
        kiri->cetak();
        cout << " " << simbol() << " ";
        kanan->cetak();
        cout << ")";
    }
};

class Tambah : public Biner {
public:
    Tambah(Simpul *a, Simpul *b) : Biner(a, b) {
    }

    char simbol() const override {
        return '+';
    }

    int nilai() const override {
        return kiri->nilai() + kanan->nilai();
    }
};

class Kurang : public Biner {
public:
    Kurang(Simpul *a, Simpul *b) : Biner(a, b) {
    }

    char simbol() const override {
        return '-';
    }

    int nilai() const override {
        return kiri->nilai() - kanan->nilai();
    }
};

class Kali : public Biner {
public:
    Kali(Simpul *a, Simpul *b) : Biner(a, b) {
    }

    char simbol() const override {
        return '*';
    }

    int nilai() const override {
        return kiri->nilai() * kanan->nilai();
    }
};

Simpul *baca() {
    string token;
    cin >> token;
    if (token == "+" || token == "-" || token == "*") {
        Simpul *kiri = baca();
        Simpul *kanan = baca();
        if (token == "+") {
            return new Tambah(kiri, kanan);
        }
        if (token == "-") {
            return new Kurang(kiri, kanan);
        }
        return new Kali(kiri, kanan);
    }
    return new Angka(token[0] - '0');
}''', r'''    Simpul *akar = baca();
    akar->cetak();
    cout << " = " << akar->nilai() << endl;
    delete akar;''', "string"), wajib=W,
     id=("Pohon Ekspresi", "Sebuah ekspresi hitung bisa disimpan sebagai **pohon**: daunnya angka, dan tiap simpul dalam adalah satu operasi dengan dua anak. Setiap simpul tahu cara menghitung nilainya dan cara mencetak dirinya — dan untuk itu ia cukup meminta anak-anaknya melakukan hal yang sama.\n\n"
         "Susun hierarkinya:\n\n- `Simpul` — `virtual int nilai() const`, `virtual void cetak() const`, destruktor virtual;\n"
         "- `Angka` turunan `Simpul` — menyimpan satu bilangan;\n- `Biner` turunan `Simpul` — memegang `Simpul *kiri` dan `Simpul *kanan`; destruktornya menghapus keduanya; punya `virtual char simbol() const`; `cetak()` mencetak `(<kiri> <simbol> <kanan>)`;\n"
         "- `Tambah`, `Kurang`, `Kali` turunan `Biner` — menimpa `simbol()` dan `nilai()`.\n\n"
         "Masukan berupa ekspresi dalam **notasi awalan** (operator ditulis sebelum kedua operandnya), tiap token dipisah spasi; operandnya angka satu digit. Fungsi rekursif `Simpul *baca()` membaca satu token: bila operator, ia membaca dua subpohon lalu membuat simpulnya; bila angka, ia membuat `Angka`.\n\n"
         "Cetak ekspresi itu dalam notasi biasa dengan kurung lengkap, lalu ` = ` dan nilainya. Akhiri dengan `delete akar`.\n\n"
         "Contoh: `+ 3 * 4 5` → `(3 + (4 * 5)) = 23`; `- 2 9` → `(2 - 9) = -7`; `7` → `7 = 7`." + W_ID,
         "Satu baris: ekspresi dalam notasi awalan.", "Satu baris: ekspresi berkurung, ` = `, dan nilainya.", "Operator `+`, `-`, `*`; operand 0 sampai 9; paling banyak 8 operand"),
     en=("An Expression Tree", "An arithmetic expression can be kept as a **tree**: its leaves are numbers, and every inner node is one operation with two children. Each node knows how to compute its value and how to print itself — and for that it only asks its children to do the same.\n\n"
         "Build the hierarchy:\n\n- `Simpul` (node) — `virtual int nilai() const` (value), `virtual void cetak() const`, a virtual destructor;\n"
         "- `Angka` (number) derived from `Simpul` — keeps one number;\n- `Biner` (binary) derived from `Simpul` — holds `Simpul *kiri` (left) and `Simpul *kanan` (right); its destructor deletes both; it has `virtual char simbol() const`; `cetak()` prints `(<left> <symbol> <right>)`;\n"
         "- `Tambah`, `Kurang`, `Kali` derived from `Biner` — override `simbol()` and `nilai()`.\n\n"
         "The input is an expression in **prefix notation** (the operator is written before its two operands), tokens separated by spaces; operands are single digits. A recursive function `Simpul *baca()` reads one token: for an operator it reads two subtrees and then makes the node; for a digit it makes an `Angka`.\n\n"
         "Print the expression in ordinary notation, fully parenthesised, then ` = ` and its value. Finish with `delete akar` (the root).\n\n"
         "Examples: `+ 3 * 4 5` → `(3 + (4 * 5)) = 23`; `- 2 9` → `(2 - 9) = -7`; `7` → `7 = 7`." + W_EN,
         "One line: an expression in prefix notation.", "One line: the parenthesised expression, ` = `, and its value.", "Operators `+`, `-`, `*`; operands 0 to 9; at most 8 operands"),
     ar=("شجرة التعبير", "التعبير الحسابي يمكن حفظه **شجرةً**: أوراقها أعداد، وكل عقدة داخلية عملية لها ابنان. كل عقدة تعرف كيف تحسب قيمتها وكيف تطبع نفسها — ولذلك يكفيها أن تطلب من أبنائها الشيء نفسه.\n\n"
         "ابنِ التسلسل:\n\n- `Simpul` (عقدة) — `virtual int nilai() const` (القيمة) و `virtual void cetak() const` وهادم افتراضي؛\n"
         "- `Angka` (عدد) المشتق من `Simpul` — يحفظ عددًا واحدًا؛\n- `Biner` (ثنائي) المشتق من `Simpul` — يمسك `Simpul *kiri` (اليسار) و `Simpul *kanan` (اليمين)؛ وهادمه يحذفهما؛ وله `virtual char simbol() const`؛ و `cetak()` تطبع `(<اليسار> <الرمز> <اليمين>)`؛\n"
         "- `Tambah` و `Kurang` و `Kali` المشتقة من `Biner` — تعيد تعريف `simbol()` و `nilai()`.\n\n"
         "المدخل تعبير **بالترميز البادئ** (يُكتب العامل قبل معامليه)، والرموز مفصولة بمسافات؛ والمعاملات أرقام من خانة واحدة. الدالة العودية `Simpul *baca()` تقرأ رمزًا واحدًا: إن كان عاملًا قرأت شجرتين فرعيتين ثم صنعت العقدة؛ وإن كان رقمًا صنعت `Angka`.\n\n"
         "اطبع التعبير بالترميز العادي بأقواس كاملة ثم ` = ` وقيمته. واختم بـ `delete akar` (الجذر).\n\n"
         "أمثلة: `+ 3 * 4 5` ← `(3 + (4 * 5)) = 23`؛ `- 2 9` ← `(2 - 9) = -7`؛ `7` ← `7 = 7`." + W_AR,
         "سطر واحد: تعبير بالترميز البادئ.", "سطر واحد: التعبير بالأقواس ثم ` = ` وقيمته.", "العوامل `+` و `-` و `*`؛ المعاملات من 0 إلى 9؛ ثمانية معاملات على الأكثر"))

soal("pohon-berkas", "sulit", "trimmed",
     "FOLDER = ['dok', 'foto', 'musik', 'kerja', 'arsip', 'unduhan']\nBERKAS = ['a.txt', 'b.txt', 'c.jpg', 'd.jpg', 'e.mp3', 'f.pdf', 'g.doc', 'h.png']\nr.shuffle(FOLDER)\nr.shuffle(BERKAS)\nfolder = ['akar']\nberkas = []\nfor _ in range(r.randint(2, 10)):\n    q = r.random()\n    if q < 0.8:\n        induk = r.choice(folder)\n    elif q < 0.9 and berkas:\n        induk = r.choice(berkas)\n    else:\n        induk = 'hilang'\n    if r.random() < 0.35 and FOLDER:\n        nama = FOLDER.pop()\n        print('folder', nama, induk)\n        if induk in folder:\n            folder.append(nama)\n    elif BERKAS:\n        nama = BERKAS.pop()\n        print('berkas', nama, r.randint(1, 500), induk)\n        if induk in folder:\n            berkas.append(nama)\nprint('selesai')\n",
     program(r'''
class Entri {
protected:
    string nama;

public:
    Entri(const string &n) : nama(n) {
    }

    virtual ~Entri() {
    }

    virtual int ukuran() const {
        return 0;
    }

    virtual bool tambah(Entri *) {
        return false;
    }

    virtual Entri *cari(const string &dicari) {
        if (nama == dicari) {
            return this;
        }
        return nullptr;
    }

    void garis(int tingkat) const {
        for (int k = 0; k < tingkat; k++) {
            cout << "--";
        }
    }

    virtual void cetak(int tingkat) const {
        garis(tingkat);
        cout << nama << endl;
    }
};

class Berkas : public Entri {
private:
    int besar;

public:
    Berkas(const string &n, int b) : Entri(n), besar(b) {
    }

    int ukuran() const override {
        return besar;
    }

    void cetak(int tingkat) const override {
        garis(tingkat);
        cout << nama << " (" << besar << ")" << endl;
    }
};

class Folder : public Entri {
private:
    Entri *isi[10];
    int banyak = 0;

public:
    Folder(const string &n) : Entri(n) {
    }

    ~Folder() override {
        for (int k = 0; k < banyak; k++) {
            delete isi[k];
        }
    }

    int ukuran() const override {
        auto total = 0;
        for (int k = 0; k < banyak; k++) {
            total += isi[k]->ukuran();
        }
        return total;
    }

    bool tambah(Entri *e) override {
        isi[banyak] = e;
        banyak++;
        return true;
    }

    Entri *cari(const string &dicari) override {
        if (nama == dicari) {
            return this;
        }
        for (int k = 0; k < banyak; k++) {
            Entri *hasil = isi[k]->cari(dicari);
            if (hasil != nullptr) {
                return hasil;
            }
        }
        return nullptr;
    }

    void cetak(int tingkat) const override {
        garis(tingkat);
        cout << nama << "/ (" << ukuran() << ")" << endl;
        for (int k = 0; k < banyak; k++) {
            isi[k]->cetak(tingkat + 1);
        }
    }
};''', r'''    Folder akar("akar");
    string kata;
    while (cin >> kata) {
        if (kata == "selesai") {
            break;
        }
        string nama, induk;
        Entri *baru = nullptr;
        if (kata == "folder") {
            cin >> nama >> induk;
            baru = new Folder(nama);
        } else {
            int besar;
            cin >> nama >> besar >> induk;
            baru = new Berkas(nama, besar);
        }
        Entri *tempat = akar.cari(induk);
        if (tempat == nullptr || !tempat->tambah(baru)) {
            cout << "Gagal: " << nama << endl;
            delete baru;
        }
    }
    akar.cetak(0);''', "string"), wajib=W,
     id=("Pohon Berkas", "Sebuah folder berisi berkas **dan folder lain**. Agar keduanya bisa diperlakukan sama, keduanya diturunkan dari satu induk.\n\n"
         "- `Entri` — atribut `protected` `string nama`; method virtual `int ukuran() const` (0), `bool tambah(Entri *e)` (menolak: `false`), `Entri *cari(const string &n)` (dirinya sendiri bila namanya cocok, selain itu `nullptr`), `void cetak(int tingkat) const`;\n"
         "- `Berkas` turunan `Entri` — menyimpan ukurannya; dicetak sebagai `<nama> (<ukuran>)`;\n"
         "- `Folder` turunan `Entri` — memegang `Entri *isi[10]`; `ukuran()` = jumlah ukuran isinya; `tambah` menerima; `cari` memeriksa dirinya lalu isinya menurut urutan; dicetak sebagai `<nama>/ (<ukuran>)` diikuti seluruh isinya satu tingkat lebih dalam; destruktornya menghapus isinya.\n\n"
         "Tiap tingkat kedalaman dicetak sebagai `--` di depan nama.\n\n"
         "Mula-mula hanya ada folder `akar`. Perintah:\n\n- `folder <nama> <induk>` — membuat folder di dalam `<induk>`;\n- `berkas <nama> <ukuran> <induk>` — membuat berkas di dalam `<induk>`;\n"
         "- bila `<induk>` tidak ditemukan, atau ternyata sebuah berkas (`tambah` mengembalikan `false`), cetak `Gagal: <nama>` dan entri baru itu dibuang;\n- `selesai` — cetak seluruh pohon mulai dari `akar`.\n\n"
         "Contoh: `folder dok akar`, `berkas a.txt 10 dok`, `berkas b.txt 5 akar`, `berkas c.txt 7 a.txt`, `folder x hilang`, `selesai` →\n\n```\nGagal: c.txt\nGagal: x\nakar/ (15)\n--dok/ (10)\n----a.txt (10)\n--b.txt (5)\n```" + W_ID,
         "Beberapa baris perintah, diakhiri `selesai`.", "Satu baris untuk tiap perintah yang gagal, lalu pohonnya.", "Paling banyak 10 perintah; semua nama berbeda dan bukan `akar`; 1 ≤ ukuran ≤ 500"),
     en=("A File Tree", "A folder contains files **and other folders**. So that both can be treated alike, both derive from one base.\n\n"
         "- `Entri` (entry) — `protected` attribute `string nama`; virtual methods `int ukuran() const` (size; 0), `bool tambah(Entri *e)` (add; refuses: `false`), `Entri *cari(const string &n)` (find; itself when the name matches, otherwise `nullptr`), `void cetak(int tingkat) const` (print at a level);\n"
         "- `Berkas` (file) derived from `Entri` — keeps its size; printed as `<name> (<size>)`;\n"
         "- `Folder` derived from `Entri` — holds `Entri *isi[10]`; `ukuran()` = the summed size of its contents; `tambah` accepts; `cari` checks itself and then its contents in order; printed as `<name>/ (<size>)` followed by all its contents one level deeper; its destructor deletes its contents.\n\n"
         "Each level of depth is printed as `--` in front of the name.\n\n"
         "At first there is only the folder `akar` (root). Commands:\n\n- `folder <name> <parent>` — make a folder inside `<parent>`;\n- `berkas <name> <size> <parent>` — make a file inside `<parent>`;\n"
         "- when `<parent>` is not found, or turns out to be a file (`tambah` returns `false`), print `Gagal: <name>` (failed) and the new entry is discarded;\n- `selesai` — print the whole tree from `akar`.\n\n"
         "Example: `folder dok akar`, `berkas a.txt 10 dok`, `berkas b.txt 5 akar`, `berkas c.txt 7 a.txt`, `folder x hilang`, `selesai` →\n\n```\nGagal: c.txt\nGagal: x\nakar/ (15)\n--dok/ (10)\n----a.txt (10)\n--b.txt (5)\n```" + W_EN,
         "Several command lines, ending with `selesai`.", "One line for each command that fails, then the tree.", "At most 10 commands; all names differ and none is `akar`; 1 ≤ size ≤ 500"),
     ar=("شجرة الملفات", "المجلد يحوي ملفات **ومجلدات أخرى**. وليُعاملا معاملة واحدة يُشتقان كلاهما من أساس واحد.\n\n"
         "- `Entri` (مدخلة) — الخاصية المحمية `string nama`؛ والطرائق الافتراضية `int ukuran() const` (الحجم؛ 0) و `bool tambah(Entri *e)` (أضف؛ ترفض: `false`) و `Entri *cari(const string &n)` (ابحث؛ نفسها إذا طابق الاسم وإلا `nullptr`) و `void cetak(int tingkat) const` (اطبع عند مستوى)؛\n"
         "- `Berkas` (ملف) المشتق من `Entri` — يحفظ حجمه؛ ويُطبع `<الاسم> (<الحجم>)`؛\n"
         "- `Folder` المشتق من `Entri` — يمسك `Entri *isi[10]`؛ و `ukuran()` = مجموع أحجام محتواه؛ و `tambah` تقبل؛ و `cari` تفحص نفسها ثم محتواها بالترتيب؛ ويُطبع `<الاسم>/ (<الحجم>)` يليه كل محتواه أعمق بمستوى؛ وهادمه يحذف محتواه.\n\n"
         "كل مستوى عمق يُطبع `--` أمام الاسم.\n\n"
         "في البداية يوجد المجلد `akar` (الجذر) فقط. الأوامر:\n\n- `folder <الاسم> <الأب>` — ينشئ مجلدًا داخل `<الأب>`؛\n- `berkas <الاسم> <الحجم> <الأب>` — ينشئ ملفًا داخل `<الأب>`؛\n"
         "- إذا لم يوجد `<الأب>` أو تبيّن أنه ملف (`tambah` تُرجع `false`) فاطبع `Gagal: <الاسم>` (فشل) وتُهمل المدخلة الجديدة؛\n- `selesai` — اطبع الشجرة كلها بدءًا من `akar`.\n\n"
         "مثال: `folder dok akar` ثم `berkas a.txt 10 dok` ثم `berkas b.txt 5 akar` ثم `berkas c.txt 7 a.txt` ثم `folder x hilang` ثم `selesai` ←\n\n```\nGagal: c.txt\nGagal: x\nakar/ (15)\n--dok/ (10)\n----a.txt (10)\n--b.txt (5)\n```" + W_AR,
         "عدة أسطر أوامر تنتهي بـ `selesai`.", "سطر لكل أمر فشل ثم الشجرة.", "10 أوامر على الأكثر؛ كل الأسماء مختلفة وليس منها `akar`؛ 1 ≤ الحجم ≤ 500"))

soal("papan-bidak", "sulit", "trimmed",
     '''def sah(j, db, dk):
    a, b = abs(db), abs(dk)
    return {'benteng': (a == 0) != (b == 0), 'gajah': a == b and a > 0, 'kuda': sorted((a, b)) == [1, 2], 'raja': max(a, b) == 1}[j]
def bebas(pos, s, d):
    sb = (d[0] > s[0]) - (d[0] < s[0])
    sk = (d[1] > s[1]) - (d[1] < s[1])
    b, k = s[0] + sb, s[1] + sk
    while (b, k) != d:
        if (b, k) in pos:
            return False
        b, k = b + sb, k + sk
    return True
petak = [(b, k) for b in range(1, 9) for k in range(1, 9)]
n = r.randint(1, 5)
pos = {}
print(n)
for p in r.sample(petak, n):
    pos[p] = r.choice(['benteng', 'gajah', 'kuda', 'raja'])
    print(pos[p], *p)
m = r.randint(1, 10)
print(m)
for _ in range(m):
    s = r.choice(list(pos)) if r.random() < 0.85 else r.choice(petak)
    d = r.choice(petak)
    if s in pos and r.random() < 0.7:
        for _ in range(40):
            d = r.choice(petak)
            if sah(pos[s], d[0] - s[0], d[1] - s[1]):
                break
    print(*s, *d)
    if s in pos and d not in pos and sah(pos[s], d[0] - s[0], d[1] - s[1]) and (pos[s] == 'kuda' or bebas(pos, s, d)):
        pos[d] = pos.pop(s)
''',
     program(r'''
int mutlak(int x) {
    if (x < 0) {
        return -x;
    }
    return x;
}

int arah(int x) {
    if (x > 0) {
        return 1;
    }
    if (x < 0) {
        return -1;
    }
    return 0;
}

class Bidak {
public:
    virtual ~Bidak() {
    }

    virtual char huruf() const {
        return '?';
    }

    virtual bool bolehLangkah(int, int) const {
        return false;
    }

    virtual bool melompat() const {
        return false;
    }
};

class Benteng : public Bidak {
public:
    char huruf() const override {
        return 'B';
    }

    bool bolehLangkah(int db, int dk) const override {
        return db == 0 || dk == 0;
    }
};

class Gajah : public Bidak {
public:
    char huruf() const override {
        return 'G';
    }

    bool bolehLangkah(int db, int dk) const override {
        return mutlak(db) == mutlak(dk);
    }
};

class Kuda : public Bidak {
public:
    char huruf() const override {
        return 'K';
    }

    bool bolehLangkah(int db, int dk) const override {
        return mutlak(db) * mutlak(dk) == 2;
    }

    bool melompat() const override {
        return true;
    }
};

class Raja : public Bidak {
public:
    char huruf() const override {
        return 'R';
    }

    bool bolehLangkah(int db, int dk) const override {
        return mutlak(db) <= 1 && mutlak(dk) <= 1;
    }
};''', r'''    Bidak *papan[9][9];
    for (int b = 1; b <= 8; b++) {
        for (int k = 1; k <= 8; k++) {
            papan[b][k] = nullptr;
        }
    }
    int n;
    cin >> n;
    for (int j = 0; j < n; j++) {
        string jenis;
        int b, k;
        cin >> jenis >> b >> k;
        if (jenis == "benteng") {
            papan[b][k] = new Benteng;
        } else if (jenis == "gajah") {
            papan[b][k] = new Gajah;
        } else if (jenis == "kuda") {
            papan[b][k] = new Kuda;
        } else {
            papan[b][k] = new Raja;
        }
    }
    int m;
    cin >> m;
    for (int j = 0; j < m; j++) {
        int b1, k1, b2, k2;
        cin >> b1 >> k1 >> b2 >> k2;
        Bidak *bidak = papan[b1][k1];
        if (bidak == nullptr) {
            cout << "Kosong" << endl;
            continue;
        }
        auto db = b2 - b1;
        auto dk = k2 - k1;
        if ((db == 0 && dk == 0) || !bidak->bolehLangkah(db, dk)) {
            cout << "Langkah tidak sah" << endl;
            continue;
        }
        auto terhalang = false;
        if (!bidak->melompat()) {
            auto b = b1 + arah(db);
            auto k = k1 + arah(dk);
            while (b != b2 || k != k2) {
                if (papan[b][k] != nullptr) {
                    terhalang = true;
                }
                b += arah(db);
                k += arah(dk);
            }
        }
        if (terhalang) {
            cout << "Terhalang" << endl;
        } else if (papan[b2][k2] != nullptr) {
            cout << "Petak terisi" << endl;
        } else {
            papan[b2][k2] = bidak;
            papan[b1][k1] = nullptr;
            cout << bidak->huruf() << " ke " << b2 << " " << k2 << endl;
        }
    }
    for (int b = 1; b <= 8; b++) {
        for (int k = 1; k <= 8; k++) {
            if (papan[b][k] == nullptr) {
                cout << ".";
            } else {
                cout << papan[b][k]->huruf();
                delete papan[b][k];
            }
        }
        cout << endl;
    }''', "string"), wajib=W,
     id=("Papan Bidak", "Di papan 8 × 8 ada beberapa bidak. Papan tidak perlu tahu aturan tiap bidak: ia cukup **bertanya kepada bidaknya**.\n\n"
         "Buat class `Bidak` dengan tiga method virtual:\n\n- `char huruf() const`;\n- `bool bolehLangkah(int db, int dk) const` — apakah perpindahan sejauh db baris dan dk kolom sesuai gerak bidak itu;\n- `bool melompat() const` — bawaannya `false`.\n\nEmpat turunannya:\n\n"
         "- `Benteng` (`B`) — lurus: db = 0 atau dk = 0;\n- `Gajah` (`G`) — diagonal: |db| = |dk|;\n- `Kuda` (`K`) — huruf L: |db| × |dk| = 2; **melompat**;\n- `Raja` (`R`) — satu petak ke arah mana pun: |db| ≤ 1 dan |dk| ≤ 1.\n\n"
         "Papan disimpan sebagai larik dua dimensi berisi `Bidak *` (`nullptr` untuk petak kosong). Baris dan kolom bernomor 1 sampai 8.\n\n"
         "Masukan: n, lalu n bidak `jenis baris kolom` (`benteng`, `gajah`, `kuda`, `raja`); lalu m, dan m langkah `b1 k1 b2 k2` (dari petak pertama ke petak kedua). Untuk tiap langkah, periksa **berurutan** dan cetak satu baris:\n\n"
         "1. petak asal kosong → `Kosong`;\n2. petak tujuan sama dengan petak asal, atau `bolehLangkah` menolak → `Langkah tidak sah`;\n3. bidaknya tidak melompat dan ada bidak lain di antara petak asal dan tujuan → `Terhalang`;\n4. petak tujuan sudah berisi bidak → `Petak terisi`;\n5. selain itu bidak pindah → `<huruf> ke <b2> <k2>`.\n\n"
         "Di akhir cetak papan: 8 baris berisi 8 karakter, `.` untuk petak kosong.\n\n"
         "Contoh: bidak `benteng 1 1`, `kuda 1 2`; langkah `1 1 1 5`, `1 2 3 3`, `1 1 1 5`, `1 5 2 6`, `4 4 5 5` →\n\n```\nTerhalang\nK ke 3 3\nB ke 1 5\nLangkah tidak sah\nKosong\n....B...\n........\n..K.....\n........\n........\n........\n........\n........\n```" + W_ID,
         "Baris pertama: n. Lalu n baris bidak. Lalu m, dan m baris langkah.", "Satu baris per langkah, lalu 8 baris papan.", "1 ≤ n ≤ 5; 1 ≤ m ≤ 10; semua bidak di petak berbeda; semua koordinat antara 1 dan 8"),
     en=("A Board of Pieces", "On an 8 × 8 board stand a few pieces. The board need not know the rule of each piece: it only **asks the piece**.\n\n"
         "Make a class `Bidak` (piece) with three virtual methods:\n\n- `char huruf() const` (letter);\n- `bool bolehLangkah(int db, int dk) const` — whether a move of db rows and dk columns fits the way this piece moves;\n- `bool melompat() const` (jumps) — `false` by default.\n\nFour derived classes:\n\n"
         "- `Benteng` (rook, `B`) — straight: db = 0 or dk = 0;\n- `Gajah` (bishop, `G`) — diagonal: |db| = |dk|;\n- `Kuda` (knight, `K`) — an L: |db| × |dk| = 2; **jumps**;\n- `Raja` (king, `R`) — one square in any direction: |db| ≤ 1 and |dk| ≤ 1.\n\n"
         "The board is kept as a two-dimensional array of `Bidak *` (`nullptr` for an empty square). Rows and columns are numbered 1 to 8.\n\n"
         "Input: n, then n pieces `kind row column` (`benteng`, `gajah`, `kuda`, `raja`); then m, and m moves `b1 k1 b2 k2` (from the first square to the second). For each move check **in order** and print one line:\n\n"
         "1. the source square is empty → `Kosong`;\n2. the target equals the source, or `bolehLangkah` refuses → `Langkah tidak sah` (illegal move);\n3. the piece does not jump and another piece stands between source and target → `Terhalang` (blocked);\n4. the target square already holds a piece → `Petak terisi` (square taken);\n5. otherwise the piece moves → `<letter> ke <b2> <k2>`.\n\n"
         "At the end print the board: 8 lines of 8 characters, `.` for an empty square.\n\n"
         "Example: pieces `benteng 1 1`, `kuda 1 2`; moves `1 1 1 5`, `1 2 3 3`, `1 1 1 5`, `1 5 2 6`, `4 4 5 5` →\n\n```\nTerhalang\nK ke 3 3\nB ke 1 5\nLangkah tidak sah\nKosong\n....B...\n........\n..K.....\n........\n........\n........\n........\n........\n```" + W_EN,
         "First line: n. Then n piece lines. Then m, and m move lines.", "One line per move, then 8 board lines.", "1 ≤ n ≤ 5; 1 ≤ m ≤ 10; all pieces stand on different squares; all coordinates are between 1 and 8"),
     ar=("رقعة القطع", "على رقعة 8 × 8 بضع قطع. الرقعة لا يلزمها أن تعرف قاعدة كل قطعة: يكفيها أن **تسأل القطعة**.\n\n"
         "أنشئ صنفًا `Bidak` (قطعة) فيه ثلاث طرائق افتراضية:\n\n- `char huruf() const` (الحرف)؛\n- `bool bolehLangkah(int db, int dk) const` — هل النقلة بمقدار db صفًّا و dk عمودًا توافق حركة هذه القطعة؛\n- `bool melompat() const` (تقفز) — وهي `false` افتراضيًا.\n\nأربعة مشتقات:\n\n"
         "- `Benteng` (القلعة، `B`) — مستقيمة: db = 0 أو dk = 0؛\n- `Gajah` (الفيل، `G`) — قطرية: |db| = |dk|؛\n- `Kuda` (الحصان، `K`) — على شكل L: |db| × |dk| = 2؛ و**يقفز**؛\n- `Raja` (الملك، `R`) — مربع واحد في أي اتجاه: |db| ≤ 1 و |dk| ≤ 1.\n\n"
         "تُحفظ الرقعة مصفوفة ثنائية من `Bidak *` (`nullptr` للمربع الفارغ). والصفوف والأعمدة مرقّمة من 1 إلى 8.\n\n"
         "المدخل: n ثم n قطعة `النوع الصف العمود` (`benteng` أو `gajah` أو `kuda` أو `raja`)؛ ثم m و m نقلة `b1 k1 b2 k2` (من المربع الأول إلى الثاني). لكل نقلة افحص **بالترتيب** واطبع سطرًا واحدًا:\n\n"
         "1. مربع الانطلاق فارغ ← `Kosong`؛\n2. الهدف هو مربع الانطلاق نفسه، أو رفضت `bolehLangkah` ← `Langkah tidak sah` (نقلة غير قانونية)؛\n3. القطعة لا تقفز وبين المربعين قطعة أخرى ← `Terhalang` (محجوبة)؛\n4. مربع الهدف فيه قطعة ← `Petak terisi` (المربع مشغول)؛\n5. وإلا تنتقل القطعة ← `<الحرف> ke <b2> <k2>`.\n\n"
         "في النهاية اطبع الرقعة: 8 أسطر في كل منها 8 محارف، و `.` للمربع الفارغ.\n\n"
         "مثال: القطعتان `benteng 1 1` و `kuda 1 2`؛ والنقلات `1 1 1 5` و `1 2 3 3` و `1 1 1 5` و `1 5 2 6` و `4 4 5 5` ←\n\n```\nTerhalang\nK ke 3 3\nB ke 1 5\nLangkah tidak sah\nKosong\n....B...\n........\n..K.....\n........\n........\n........\n........\n........\n```" + W_AR,
         "السطر الأول: n. ثم n سطر قطع. ثم m و m سطر نقلات.", "سطر لكل نقلة ثم 8 أسطر للرقعة.", "1 ≤ n ≤ 5؛ 1 ≤ m ≤ 10؛ كل القطع على مربعات مختلفة؛ وكل الإحداثيات بين 1 و 8"))

soal("penjadwal-tugas", "sulit", "trimmed",
     "TUGAS = ['cadang', 'surel', 'lapor', 'pindai', 'bersih', 'sinkron']\nn = {2: 1}.get(i, r.randint(2, 6))\nT = r.randint(4, 20)\nprint(n, T)\nfor nama in r.sample(TUGAS, n):\n    j = r.choice(['tetap', 'sekali', 'berulang', 'terbatas'])\n    if j == 'tetap':\n        print(j, nama)\n    elif j == 'sekali':\n        print(j, nama, r.randint(1, T + 2))\n    elif j == 'berulang':\n        print(j, nama, r.randint(2, 5))\n    else:\n        print(j, nama, r.randint(1, 5), r.randint(1, 4))\n",
     program(r'''
class Tugas {
protected:
    string nama;

public:
    Tugas(const string &n) : nama(n) {
    }

    virtual ~Tugas() {
    }

    string getNama() const {
        return nama;
    }

    virtual bool waktunya(int) const {
        return true;
    }

    virtual void jalan() {
    }

    virtual bool tuntas() const {
        return false;
    }
};

class Sekali : public Tugas {
private:
    int kapan;
    bool sudah = false;

public:
    Sekali(const string &n, int t) : Tugas(n), kapan(t) {
    }

    bool waktunya(int t) const override {
        return t == kapan;
    }

    void jalan() override {
        sudah = true;
    }

    bool tuntas() const override {
        return sudah;
    }
};

class Berulang : public Tugas {
protected:
    int selang;

public:
    Berulang(const string &n, int s) : Tugas(n), selang(s) {
    }

    bool waktunya(int t) const override {
        return t % selang == 0;
    }
};

class Terbatas : public Berulang {
private:
    int sisa;

public:
    Terbatas(const string &n, int s, int kali) : Berulang(n, s), sisa(kali) {
    }

    void jalan() override {
        sisa--;
    }

    bool tuntas() const override {
        return sisa == 0;
    }
};''', r'''    int n, lama;
    cin >> n >> lama;
    Tugas *daftar[6];
    for (int k = 0; k < n; k++) {
        string jenis, nama;
        cin >> jenis >> nama;
        if (jenis == "sekali") {
            int t;
            cin >> t;
            daftar[k] = new Sekali(nama, t);
        } else if (jenis == "berulang") {
            int s;
            cin >> s;
            daftar[k] = new Berulang(nama, s);
        } else if (jenis == "terbatas") {
            int s, kali;
            cin >> s >> kali;
            daftar[k] = new Terbatas(nama, s, kali);
        } else {
            daftar[k] = new Tugas(nama);
        }
    }
    for (int t = 1; t <= lama; t++) {
        for (int k = 0; k < n; k++) {
            if (daftar[k] == nullptr || !daftar[k]->waktunya(t)) {
                continue;
            }
            daftar[k]->jalan();
            cout << t << ": " << daftar[k]->getNama() << endl;
            if (daftar[k]->tuntas()) {
                cout << t << ": " << daftar[k]->getNama() << " selesai" << endl;
                delete daftar[k];
                daftar[k] = nullptr;
            }
        }
    }
    auto tersisa = 0;
    for (int k = 0; k < n; k++) {
        if (daftar[k] != nullptr) {
            tersisa++;
            delete daftar[k];
        }
    }
    cout << "Tersisa: " << tersisa << endl;''', "string"), wajib=W,
     id=("Penjadwal Tugas", "Sebuah penjadwal menjalankan tugas-tugas pada detik 1, 2, …, T. Penjadwal tidak tahu jenis tugasnya; pada tiap detik ia hanya bertanya kepada setiap tugas tiga hal, lewat tiga method virtual.\n\n"
         "**`Tugas`** — atribut `protected` `string nama`; `getNama()`;\n- `virtual bool waktunya(int t) const` — apakah tugas berjalan pada detik t (bawaan: selalu);\n- `virtual void jalan()` — dipanggil saat tugas berjalan (bawaan: tidak berbuat apa-apa);\n- `virtual bool tuntas() const` — apakah tugas sudah tidak diperlukan lagi (bawaan: tidak pernah).\n\n"
         "Turunannya:\n\n- `Sekali` (detik `kapan`) — berjalan hanya pada detik itu, lalu tuntas;\n- `Berulang` (selang s) — berjalan pada setiap detik kelipatan s; tidak pernah tuntas;\n- `Terbatas` turunan **`Berulang`** (selang s, sebanyak `kali`) — seperti `Berulang`, tetapi tuntas setelah berjalan `kali` kali.\n\n"
         "Masukan: n dan T; lalu n tugas: `tetap <nama>` (sebuah `Tugas` biasa), `sekali <nama> <detik>`, `berulang <nama> <s>`, atau `terbatas <nama> <s> <kali>`. Simpan di `Tugas *daftar[6]`.\n\n"
         "Untuk tiap detik t = 1 … T, periksa tugas-tugas **menurut urutan masukan**. Bila `waktunya(t)`: panggil `jalan()` dan cetak `<t>: <nama>`; bila sesudah itu `tuntas()`, cetak `<t>: <nama> selesai`, `delete` tugas itu, dan jangan periksa lagi. Di akhir cetak `Tersisa: <banyak tugas yang belum tuntas>`.\n\n"
         "Contoh: n = 3, T = 6; `sekali cadang 2`, `berulang surel 3`, `terbatas lapor 2 2` →\n\n```\n2: cadang\n2: cadang selesai\n2: lapor\n3: surel\n4: lapor\n4: lapor selesai\n6: surel\nTersisa: 1\n```" + W_ID,
         "Baris pertama: n dan T. Lalu n baris tugas.", "Baris-baris seperti contoh.", "1 ≤ n ≤ 6; 4 ≤ T ≤ 20; nama berbeda-beda; selang 1 sampai 5; `kali` 1 sampai 4"),
     en=("A Task Scheduler", "A scheduler runs tasks at seconds 1, 2, …, T. The scheduler does not know the kinds of task; every second it only asks each task three things, through three virtual methods.\n\n"
         "**`Tugas`** (task) — `protected` attribute `string nama`; `getNama()`;\n- `virtual bool waktunya(int t) const` — whether the task runs at second t (default: always);\n- `virtual void jalan()` — called when the task runs (default: does nothing);\n- `virtual bool tuntas() const` — whether the task is no longer needed (default: never).\n\n"
         "Its derived classes:\n\n- `Sekali` (once; second `kapan`) — runs only at that second, and is then finished;\n- `Berulang` (repeating; interval s) — runs at every second that is a multiple of s; never finished;\n- `Terbatas` (limited) derived from **`Berulang`** (interval s, `kali` times) — like `Berulang`, but finished after running `kali` times.\n\n"
         "Input: n and T; then n tasks: `tetap <name>` (a plain `Tugas`), `sekali <name> <second>`, `berulang <name> <s>`, or `terbatas <name> <s> <times>`. Keep them in `Tugas *daftar[6]`.\n\n"
         "For each second t = 1 … T, check the tasks **in input order**. If `waktunya(t)`: call `jalan()` and print `<t>: <name>`; if `tuntas()` holds afterwards, print `<t>: <name> selesai`, `delete` the task, and do not check it again. At the end print `Tersisa: <number of tasks not finished>`.\n\n"
         "Example: n = 3, T = 6; `sekali cadang 2`, `berulang surel 3`, `terbatas lapor 2 2` →\n\n```\n2: cadang\n2: cadang selesai\n2: lapor\n3: surel\n4: lapor\n4: lapor selesai\n6: surel\nTersisa: 1\n```" + W_EN,
         "First line: n and T. Then n task lines.", "Lines as in the example.", "1 ≤ n ≤ 6; 4 ≤ T ≤ 20; all names differ; intervals 1 to 5; `kali` 1 to 4"),
     ar=("مُجدوِل المهام", "مُجدوِل ينفّذ المهام في الثواني 1 و 2 … و T. المُجدوِل لا يعرف أنواع المهام؛ ففي كل ثانية يسأل كل مهمة ثلاثة أسئلة فقط عبر ثلاث طرائق افتراضية.\n\n"
         "**`Tugas`** (مهمة) — الخاصية المحمية `string nama`؛ و `getNama()`؛\n- `virtual bool waktunya(int t) const` — هل تعمل المهمة في الثانية t (الافتراضي: دائمًا)؛\n- `virtual void jalan()` — تُستدعى حين تعمل المهمة (الافتراضي: لا تفعل شيئًا)؛\n- `virtual bool tuntas() const` — هل لم تعد المهمة لازمة (الافتراضي: أبدًا).\n\n"
         "ومشتقاته:\n\n- `Sekali` (مرة واحدة؛ الثانية `kapan`) — تعمل في تلك الثانية فقط ثم تنتهي؛\n- `Berulang` (متكررة؛ الفاصل s) — تعمل في كل ثانية من مضاعفات s؛ ولا تنتهي أبدًا؛\n- `Terbatas` (محدودة) المشتق من **`Berulang`** (الفاصل s، و `kali` مرة) — مثل `Berulang` لكنها تنتهي بعد أن تعمل `kali` مرة.\n\n"
         "المدخل: n و T؛ ثم n مهمة: `tetap <الاسم>` (`Tugas` عادية) أو `sekali <الاسم> <الثانية>` أو `berulang <الاسم> <s>` أو `terbatas <الاسم> <s> <المرات>`. احفظها في `Tugas *daftar[6]`.\n\n"
         "لكل ثانية t = 1 … T افحص المهام **بترتيب المدخل**. إذا تحقق `waktunya(t)`: استدعِ `jalan()` واطبع `<t>: <الاسم>`؛ وإذا تحقق `tuntas()` بعد ذلك فاطبع `<t>: <الاسم> selesai` واحذف المهمة بـ `delete` ولا تفحصها ثانية. في النهاية اطبع `Tersisa: <عدد المهام غير المنتهية>`.\n\n"
         "مثال: n = 3 و T = 6؛ `sekali cadang 2` و `berulang surel 3` و `terbatas lapor 2 2` ←\n\n```\n2: cadang\n2: cadang selesai\n2: lapor\n3: surel\n4: lapor\n4: lapor selesai\n6: surel\nTersisa: 1\n```" + W_AR,
         "السطر الأول: n و T. ثم n سطر مهام.", "أسطر كما في المثال.", "1 ≤ n ≤ 6؛ 4 ≤ T ≤ 20؛ الأسماء مختلفة؛ الفواصل من 1 إلى 5؛ و `kali` من 1 إلى 4"))

tulis(__file__, "PBO301", 7, "cpp", {"mudah_sekali": 8, "mudah": 8, "mudah_sedang": 6, "sedang": 6, "sulit": 4})
