#!/usr/bin/env python3
"""Menyusun docs/soal/pbo301-pertemuan-3.json.

PBO301 pertemuan 3 — Constructor, Destructor, Copy Semantics. Konsep yang
diizinkan di produksi: 21 konsep dasar, MODERN_CPP, CLASS_OBJECT,
CONSTRUCTOR_DESTRUCTOR, COPY_SEMANTICS.

Solusi referensi masih menaruh semua anggota di `public:` (ENCAPSULATION baru
di pertemuan 4), tidak memuat objek kelas lain di dalam kelas (pertemuan 5),
dan tidak memakai kontainer STL. Kode mahasiswa tidak dibatasi.

Setiap soal mensyaratkan CLASS_OBJECT dan CONSTRUCTOR_DESTRUCTOR di kode
mahasiswa; soal penyalinan juga COPY_SEMANTICS. Karena judge hanya memeriksa
ADA-nya konstruktor atau destruktor, urutan pemanggilan dibuat terlihat di
keluaran: konstruktor dan destruktor mencetak baris, dan masukan menentukan
nama, banyak, serta urutan objek.

Yang sengaja dihindari: keluaran yang bergantung pada copy elision (objek
dikembalikan dari fungsi oleh kelas yang konstruktor salinnya mencetak).

Dilewati dari silabus pertemuan 3 (tidak bisa dinilai dari keluaran):
demonstrasi double-free dengan debugger; `explicit`, `= delete` dan kasus lain
yang intinya "kode ini tidak terkompilasi".

    python3 docs/soal/buat-pbo301-pertemuan-3.py
"""
from bangun import program, soal, tulis

W = ("CLASS_OBJECT", "CONSTRUCTOR_DESTRUCTOR")
WS = ("CLASS_OBJECT", "CONSTRUCTOR_DESTRUCTOR", "COPY_SEMANTICS")
W_ID = "\n\n**Kode harus memuat class dengan konstruktor atau destruktor**: keluaran yang benar tanpa itu hanya mendapat setengah nilai."
W_EN = "\n\n**The code must contain a class with a constructor or destructor**: correct output without one gets only half the marks."
W_AR = "\n\n**يجب أن يحتوي الكود على صنف له بانٍ أو هادم**: المخرج الصحيح بلا ذلك ينال نصف الدرجة فقط."
S_ID = "\n\n**Kode harus memuat class dengan konstruktor atau destruktor, serta konstruktor salin atau operator penugasan**: keluaran yang benar tanpa itu hanya mendapat setengah nilai."
S_EN = "\n\n**The code must contain a class with a constructor or destructor, and a copy constructor or assignment operator**: correct output without them gets only half the marks."
S_AR = "\n\n**يجب أن يحتوي الكود على صنف له بانٍ أو هادم، وعلى باني نسخ أو عامل إسناد**: المخرج الصحيح بلا ذلك ينال نصف الدرجة فقط."

NAMA = "NAMA = ['Andi', 'Budi', 'Citra', 'Dewi', 'Eko', 'Fajar', 'Gita', 'Hadi', 'Indah', 'Joko', 'Kartika', 'Lina', 'Made', 'Nanda', 'Putri', 'Rudi', 'Sari', 'Tono', 'Wulan', 'Yoga']\n"
BENDA_KATA = "KATA = ['meja', 'kursi', 'lampu', 'buku', 'pena', 'tas', 'jam', 'kunci', 'pintu', 'kaca', 'peta', 'radio', 'kabel', 'layar', 'botol', 'piring', 'gelas', 'sendok', 'payung', 'topi']\n"

BENDA = r'''
class Benda {
public:
    string nama;

    Benda(string n) {
        nama = n;
        cout << "Buat " << nama << endl;
    }

    ~Benda() {
        cout << "Hapus " << nama << endl;
    }
};'''

PAKET = r'''
class Paket {
public:
    string isi;

    Paket(string s) : isi(s) {
        cout << "Buat " << isi << endl;
    }

    Paket(const Paket &lain) : isi(lain.isi) {
        cout << "Salin " << isi << endl;
    }

    ~Paket() {
        cout << "Hapus " << isi << endl;
    }
};'''

LARIK = r'''
class Larik {
public:
    int *data;
    int banyak;

    Larik(int n) : data(new int[n]), banyak(n) {
        for (int k = 0; k < n; k++) {
            data[k] = 0;
        }
    }

    Larik(const Larik &lain) : data(new int[lain.banyak]), banyak(lain.banyak) {
        for (int k = 0; k < banyak; k++) {
            data[k] = lain.data[k];
        }
    }

    Larik &operator=(const Larik &lain) {
        if (this != &lain) {
            delete[] data;
            banyak = lain.banyak;
            data = new int[banyak];
            for (int k = 0; k < banyak; k++) {
                data[k] = lain.data[k];
            }
        }
        return *this;
    }

    ~Larik() {
        delete[] data;
    }

    void baca() {
        for (int k = 0; k < banyak; k++) {
            cin >> data[k];
        }
    }

    void cetak() {
        for (int k = 0; k < banyak; k++) {
            cout << data[k] << " ";
        }
        cout << endl;
    }
};'''

TEKS = r'''
class Teks {
public:
    char *huruf;
    int panjang;

    Teks(const string &s) : huruf(new char[s.size() + 1]), panjang(s.size()) {
        for (int k = 0; k < panjang; k++) {
            huruf[k] = s[k];
        }
        huruf[panjang] = '\0';
    }

    Teks(const Teks &lain) : huruf(new char[lain.panjang + 1]), panjang(lain.panjang) {
        for (int k = 0; k <= panjang; k++) {
            huruf[k] = lain.huruf[k];
        }
    }

    Teks &operator=(const Teks &lain) {
        if (this != &lain) {
            delete[] huruf;
            panjang = lain.panjang;
            huruf = new char[panjang + 1];
            for (int k = 0; k <= panjang; k++) {
                huruf[k] = lain.huruf[k];
            }
        }
        return *this;
    }

    ~Teks() {
        delete[] huruf;
    }
'''

# ============================== MUDAH SEKALI (8) ==============================

soal("mulai-selesai", "mudah_sekali", "trimmed", BENDA_KATA + "print(r.choice(KATA[:10]) if i % 2 == 0 else r.choice(KATA[10:]))\n",
     program(r'''
class Tugas {
public:
    string nama;

    Tugas(string n) {
        nama = n;
        cout << "Mulai " << nama << endl;
    }

    ~Tugas() {
        cout << "Selesai " << nama << endl;
    }
};''', r'''    string nama;
    cin >> nama;
    Tugas t(nama);
    cout << "Mengerjakan " << nama << endl;''', "string"), wajib=W,
     id=("Mulai dan Selesai", "**Konstruktor** adalah method yang berjalan sendiri saat sebuah objek dibuat; namanya sama dengan nama class dan ia tidak punya tipe kembalian. **Destruktor** (`~NamaClass()`) berjalan sendiri saat objek itu berakhir.\n\n"
         "Buat class `Tugas` dengan atribut `string nama`:\n\n- konstruktor `Tugas(string n)` mengisi `nama` dan mencetak `Mulai <nama>`;\n- destruktor `~Tugas()` mencetak `Selesai <nama>`.\n\n"
         "Di `main`, baca sebuah nama, buat satu objek `Tugas`, lalu cetak `Mengerjakan <nama>`. Baris terakhir keluaran dicetak oleh destruktor saat `main` berakhir — tidak ada yang memanggilnya.\n\n"
         "Untuk `laporan`:\n\n```\nMulai laporan\nMengerjakan laporan\nSelesai laporan\n```" + W_ID,
         "Satu kata: nama tugas.", "Tiga baris seperti contoh.", "Nama terdiri dari huruf kecil, paling panjang 20 huruf."),
     en=("Start and Finish", "A **constructor** is a method that runs by itself when an object is created; it has the name of the class and no return type. A **destructor** (`~ClassName()`) runs by itself when the object ends.\n\n"
         "Make a class `Tugas` (task) with an attribute `string nama`:\n\n- the constructor `Tugas(string n)` fills `nama` and prints `Mulai <nama>` (start);\n- the destructor `~Tugas()` prints `Selesai <nama>` (finished).\n\n"
         "In `main`, read a name, create one `Tugas` object, then print `Mengerjakan <nama>` (working on). The last output line is printed by the destructor when `main` ends — nobody calls it.\n\n"
         "For `laporan`:\n\n```\nMulai laporan\nMengerjakan laporan\nSelesai laporan\n```" + W_EN,
         "One word: the name of the task.", "Three lines as in the example.", "The name has lowercase letters, at most 20."),
     ar=("البداية والنهاية", "**الباني** طريقة تعمل تلقائيًا عند إنشاء الكائن؛ اسمها اسم الصنف ولا نوع إرجاع لها. و**الهادم** (`~ClassName()`) يعمل تلقائيًا عند انتهاء الكائن.\n\n"
         "أنشئ صنفًا `Tugas` (مهمة) فيه خاصية `string nama`:\n\n- الباني `Tugas(string n)` يملأ `nama` ويطبع `Mulai <nama>` (بدء)؛\n- الهادم `~Tugas()` يطبع `Selesai <nama>` (انتهاء).\n\n"
         "في `main` اقرأ اسمًا وأنشئ كائن `Tugas` واحدًا ثم اطبع `Mengerjakan <nama>` (جارٍ العمل). السطر الأخير من المخرج يطبعه الهادم عند انتهاء `main` — لا أحد يستدعيه.\n\n"
         "عند `laporan`:\n\n```\nMulai laporan\nMengerjakan laporan\nSelesai laporan\n```" + W_AR,
         "كلمة واحدة: اسم المهمة.", "ثلاثة أسطر كما في المثال.", "الاسم حروف لاتينية صغيرة، 20 حرفًا على الأكثر."))

soal("titik-asal", "mudah_sekali", "trimmed", "if i % 2 == 0:\n    print(0)\nelse:\n    print(2)\n    print(r.randint(-100, 100), r.randint(-100, 100))\n",
     program(r'''
class Titik {
public:
    int x;
    int y;

    Titik() {
        x = 0;
        y = 0;
        cout << "Titik asal dibuat" << endl;
    }

    Titik(int a, int b) {
        x = a;
        y = b;
        cout << "Titik (" << x << ", " << y << ") dibuat" << endl;
    }

    int jumlah() {
        return x + y;
    }
};''', r'''    int k;
    cin >> k;
    if (k == 0) {
        Titik t;
        cout << t.jumlah() << endl;
    } else {
        int a, b;
        cin >> a >> b;
        Titik t(a, b);
        cout << t.jumlah() << endl;
    }'''), wajib=W,
     id=("Titik Asal", "Sebuah class boleh punya beberapa konstruktor dengan parameter berbeda. Konstruktor tanpa parameter disebut *konstruktor bawaan*.\n\n"
         "Buat class `Titik` dengan atribut `int x`, `int y` dan dua konstruktor:\n\n- `Titik()` — membuat titik (0, 0) dan mencetak `Titik asal dibuat`;\n- `Titik(int a, int b)` — membuat titik (a, b) dan mencetak `Titik (a, b) dibuat`.\n\n"
         "Tambahkan method `int jumlah()` yang mengembalikan x + y.\n\n"
         "Masukan diawali k. Bila k = 0, buat titik dengan konstruktor bawaan (`Titik t;` — tanpa tanda kurung). Bila k = 2, baca dua bilangan dan buat titik dengan keduanya. Lalu cetak `jumlah()`.\n\n"
         "Contoh: masukan `2` lalu `3 -5` →\n\n```\nTitik (3, -5) dibuat\n-2\n```" + W_ID,
         "Baris pertama: k (0 atau 2). Bila k = 2, baris kedua berisi x dan y.", "Baris yang dicetak konstruktor, lalu x + y.", "−100 ≤ x, y ≤ 100"),
     en=("Origin", "A class may have several constructors with different parameters. The constructor without parameters is called the *default constructor*.\n\n"
         "Make a class `Titik` (point) with the attributes `int x`, `int y` and two constructors:\n\n- `Titik()` — makes the point (0, 0) and prints `Titik asal dibuat` (origin created);\n- `Titik(int a, int b)` — makes the point (a, b) and prints `Titik (a, b) dibuat`.\n\n"
         "Add a method `int jumlah()` that returns x + y.\n\n"
         "The input starts with k. If k = 0, create the point with the default constructor (`Titik t;` — without brackets). If k = 2, read two numbers and create the point from them. Then print `jumlah()`.\n\n"
         "Example: input `2` then `3 -5` →\n\n```\nTitik (3, -5) dibuat\n-2\n```" + W_EN,
         "First line: k (0 or 2). If k = 2, the second line holds x and y.", "The line printed by the constructor, then x + y.", "−100 ≤ x, y ≤ 100"),
     ar=("نقطة الأصل", "يجوز أن يكون للصنف عدة بانيات بوسائط مختلفة. الباني بلا وسائط يسمى *الباني الافتراضي*.\n\n"
         "أنشئ صنفًا `Titik` (نقطة) خاصيتاه `int x` و `int y` وبانيان:\n\n- `Titik()` — ينشئ النقطة (0, 0) ويطبع `Titik asal dibuat` (أُنشئت نقطة الأصل)؛\n- `Titik(int a, int b)` — ينشئ النقطة (a, b) ويطبع `Titik (a, b) dibuat`.\n\n"
         "أضف طريقة `int jumlah()` تُرجع x + y.\n\n"
         "يبدأ المدخل بالعدد k. إذا كان k = 0 فأنشئ النقطة بالباني الافتراضي (`Titik t;` — بلا أقواس). وإذا كان k = 2 فاقرأ عددين وأنشئ النقطة منهما. ثم اطبع `jumlah()`.\n\n"
         "مثال: المدخل `2` ثم `3 -5` ←\n\n```\nTitik (3, -5) dibuat\n-2\n```" + W_AR,
         "السطر الأول: k (0 أو 2). إذا كان k = 2 ففي السطر الثاني x و y.", "السطر الذي يطبعه الباني ثم x + y.", "−100 ≤ x, y ≤ 100"))

soal("urutan-dua-objek", "mudah_sekali", "trimmed", BENDA_KATA + "print(*r.sample(KATA, 2))\n",
     program(BENDA, r'''    string a, b;
    cin >> a >> b;
    Benda pertama(a);
    Benda kedua(b);''', "string"), wajib=W,
     id=("Urutan Dua Objek", "Objek yang dibuat di dalam sebuah fungsi dihancurkan saat fungsi itu berakhir, dalam **urutan terbalik** dari pembuatannya: yang terakhir dibuat, pertama dihancurkan.\n\n"
         "Buat class `Benda` dengan atribut `string nama`; konstruktornya mencetak `Buat <nama>` dan destruktornya mencetak `Hapus <nama>`.\n\n"
         "Di `main`, baca dua nama dan buat dua objek `Benda` sesuai urutan masukan. Tidak ada perintah lain.\n\n"
         "Untuk `meja kursi`:\n\n```\nBuat meja\nBuat kursi\nHapus kursi\nHapus meja\n```" + W_ID,
         "Satu baris berisi dua nama yang berbeda.", "Empat baris: dua dari konstruktor, dua dari destruktor.", "Nama terdiri dari huruf kecil, paling panjang 20 huruf."),
     en=("Order of Two Objects", "Objects created inside a function are destroyed when the function ends, in the **reverse order** of their creation: last created, first destroyed.\n\n"
         "Make a class `Benda` (thing) with an attribute `string nama`; its constructor prints `Buat <nama>` (create) and its destructor prints `Hapus <nama>` (destroy).\n\n"
         "In `main`, read two names and create two `Benda` objects in input order. There is no other statement.\n\n"
         "For `meja kursi`:\n\n```\nBuat meja\nBuat kursi\nHapus kursi\nHapus meja\n```" + W_EN,
         "One line with two different names.", "Four lines: two from the constructor, two from the destructor.", "Names have lowercase letters, at most 20."),
     ar=("ترتيب كائنين", "الكائنات المنشأة داخل دالة تُهدم عند انتهاء الدالة **بعكس ترتيب** إنشائها: آخر ما أُنشئ أول ما يُهدم.\n\n"
         "أنشئ صنفًا `Benda` (شيء) فيه خاصية `string nama`؛ بانيه يطبع `Buat <nama>` (إنشاء) وهادمه يطبع `Hapus <nama>` (هدم).\n\n"
         "في `main` اقرأ اسمين وأنشئ كائنين من `Benda` بترتيب المدخل. لا تعليمة أخرى.\n\n"
         "عند `meja kursi`:\n\n```\nBuat meja\nBuat kursi\nHapus kursi\nHapus meja\n```" + W_AR,
         "سطر واحد فيه اسمان مختلفان.", "أربعة أسطر: اثنان من الباني واثنان من الهادم.", "الأسماء حروف لاتينية صغيرة، 20 حرفًا على الأكثر."))

soal("nomor-seri", "mudah_sekali", "trimmed", NAMA + "n = {2: 1}.get(i, r.randint(2, 10))\nprint(n)\nprint(*r.sample(NAMA, n))\n",
     program(r'''
class Kartu {
public:
    static int dibuat;
    int nomor;
    string pemilik;

    Kartu() {
        dibuat++;
        nomor = dibuat;
    }
};

int Kartu::dibuat = 0;''', r'''    int n;
    cin >> n;
    Kartu *kartu = new Kartu[n];
    for (int k = 0; k < n; k++) {
        cin >> kartu[k].pemilik;
    }
    for (int k = 0; k < n; k++) {
        cout << kartu[k].pemilik << " #" << kartu[k].nomor << endl;
    }
    delete[] kartu;
    kartu = nullptr;''', "string"), wajib=W,
     id=("Nomor Seri", "Konstruktor cocok untuk pekerjaan yang harus terjadi **setiap kali** objek dibuat, misalnya memberi nomor seri.\n\n"
         "Buat class `Kartu` dengan atribut `static int dibuat` (mula-mula 0), `int nomor`, dan `string pemilik`. Konstruktor bawaannya menaikkan `dibuat` dan memberi `nomor` nilai `dibuat` saat itu, sehingga kartu pertama bernomor 1, kartu kedua 2, dan seterusnya.\n\n"
         "Baca n, buat n kartu sekaligus dengan `new Kartu[n]` (konstruktor bawaan dipanggil untuk tiap elemen, dari indeks 0), baca nama pemiliknya, lalu cetak tiap kartu sebagai `<pemilik> #<nomor>`.\n\n"
         "Untuk `Budi Citra`:\n\n```\nBudi #1\nCitra #2\n```" + W_ID,
         "Baris pertama: n. Baris kedua: n nama, masing-masing satu kata.", "n baris: pemilik dan nomor kartunya.", "1 ≤ n ≤ 10"),
     en=("Serial Numbers", "A constructor suits work that must happen **every time** an object is created, such as handing out a serial number.\n\n"
         "Make a class `Kartu` (card) with the attributes `static int dibuat` (created, starting at 0), `int nomor` (number) and `string pemilik` (owner). Its default constructor raises `dibuat` and gives `nomor` the current value of `dibuat`, so the first card has number 1, the second 2, and so on.\n\n"
         "Read n, create n cards at once with `new Kartu[n]` (the default constructor is called for every element, from index 0), read the owners' names, then print each card as `<owner> #<number>`.\n\n"
         "For `Budi Citra`:\n\n```\nBudi #1\nCitra #2\n```" + W_EN,
         "First line: n. Second line: n names, one word each.", "n lines: the owner and the card number.", "1 ≤ n ≤ 10"),
     ar=("الأرقام التسلسلية", "الباني يناسب العمل الذي يجب أن يقع **في كل مرة** يُنشأ فيها كائن، كإعطاء رقم تسلسلي.\n\n"
         "أنشئ صنفًا `Kartu` (بطاقة) خصائصه `static int dibuat` (المُنشأ، ويبدأ من 0) و `int nomor` (الرقم) و `string pemilik` (المالك). بانيه الافتراضي يزيد `dibuat` ويعطي `nomor` قيمة `dibuat` الحالية، فتكون البطاقة الأولى رقم 1 والثانية 2 وهكذا.\n\n"
         "اقرأ n وأنشئ n بطاقة دفعة واحدة بـ `new Kartu[n]` (يُستدعى الباني الافتراضي لكل عنصر بدءًا من الفهرس 0) واقرأ أسماء المالكين ثم اطبع كل بطاقة على الصورة `<المالك> #<الرقم>`.\n\n"
         "عند `Budi Citra`:\n\n```\nBudi #1\nCitra #2\n```" + W_AR,
         "السطر الأول: n. السطر الثاني: n اسمًا، كل اسم كلمة واحدة.", "n سطرًا: المالك ورقم بطاقته.", "1 ≤ n ≤ 10"))

soal("blok-cakupan", "mudah_sekali", "trimmed", BENDA_KATA + "print(*r.sample(KATA, 2))\n",
     program(BENDA, r'''    string a, b;
    cin >> a >> b;
    Benda luar(a);
    {
        Benda dalam(b);
        cout << "Di dalam blok" << endl;
    }
    cout << "Di luar blok" << endl;''', "string"), wajib=W,
     id=("Blok dan Cakupan", "Sepasang kurung kurawal membentuk sebuah **blok**. Objek yang dibuat di dalam blok dihancurkan begitu blok itu berakhir — tidak menunggu `main` selesai.\n\n"
         "Buat class `Benda` (atribut `string nama`; konstruktor mencetak `Buat <nama>`, destruktor mencetak `Hapus <nama>`). Baca dua nama, lalu susun `main` seperti ini:\n\n"
         "1. buat objek pertama;\n2. buka blok `{`, buat objek kedua di dalamnya, cetak `Di dalam blok`, tutup blok `}`;\n3. cetak `Di luar blok`.\n\n"
         "Untuk `meja kursi`:\n\n```\nBuat meja\nBuat kursi\nDi dalam blok\nHapus kursi\nDi luar blok\nHapus meja\n```" + W_ID,
         "Satu baris berisi dua nama yang berbeda.", "Enam baris seperti contoh.", "Nama terdiri dari huruf kecil, paling panjang 20 huruf."),
     en=("Blocks and Scope", "A pair of braces forms a **block**. An object created inside a block is destroyed as soon as the block ends — it does not wait for `main` to finish.\n\n"
         "Make a class `Benda` (attribute `string nama`; the constructor prints `Buat <nama>`, the destructor prints `Hapus <nama>`). Read two names, then build `main` like this:\n\n"
         "1. create the first object;\n2. open a block `{`, create the second object inside it, print `Di dalam blok` (inside the block), close the block `}`;\n3. print `Di luar blok` (outside the block).\n\n"
         "For `meja kursi`:\n\n```\nBuat meja\nBuat kursi\nDi dalam blok\nHapus kursi\nDi luar blok\nHapus meja\n```" + W_EN,
         "One line with two different names.", "Six lines as in the example.", "Names have lowercase letters, at most 20."),
     ar=("الكتل والمجال", "زوج الأقواس المعقوفة يشكّل **كتلة**. الكائن المنشأ داخل كتلة يُهدم فور انتهاء الكتلة — لا ينتظر انتهاء `main`.\n\n"
         "أنشئ صنفًا `Benda` (الخاصية `string nama`؛ الباني يطبع `Buat <nama>` والهادم يطبع `Hapus <nama>`). اقرأ اسمين ثم ابنِ `main` هكذا:\n\n"
         "1. أنشئ الكائن الأول؛\n2. افتح كتلة `{` وأنشئ الكائن الثاني داخلها واطبع `Di dalam blok` (داخل الكتلة) ثم أغلق الكتلة `}`؛\n3. اطبع `Di luar blok` (خارج الكتلة).\n\n"
         "عند `meja kursi`:\n\n```\nBuat meja\nBuat kursi\nDi dalam blok\nHapus kursi\nDi luar blok\nHapus meja\n```" + W_AR,
         "سطر واحد فيه اسمان مختلفان.", "ستة أسطر كما في المثال.", "الأسماء حروف لاتينية صغيرة، 20 حرفًا على الأكثر."))

soal("objek-dinamis", "mudah_sekali", "trimmed", BENDA_KATA + "print(*r.sample(KATA, 2))\n",
     program(BENDA, r'''    string a, b;
    cin >> a >> b;
    Benda *pertama = new Benda(a);
    Benda *kedua = new Benda(b);
    cout << "Dua objek di heap" << endl;
    delete pertama;
    delete kedua;''', "string"), wajib=W,
     id=("Objek Dinamis", "Objek yang dibuat dengan `new` **tidak** dihancurkan otomatis. Ia hidup sampai program memanggil `delete`, dan destruktornya berjalan tepat saat itu. Urutan penghancurannya ditentukan oleh urutan `delete`, bukan oleh urutan pembuatan.\n\n"
         "Buat class `Benda` (atribut `string nama`; konstruktor mencetak `Buat <nama>`, destruktor mencetak `Hapus <nama>`). Baca dua nama. Buat objek pertama lalu objek kedua dengan `new`, cetak `Dua objek di heap`, lalu `delete` objek **pertama** dan sesudah itu objek kedua.\n\n"
         "Untuk `meja kursi`:\n\n```\nBuat meja\nBuat kursi\nDua objek di heap\nHapus meja\nHapus kursi\n```" + W_ID,
         "Satu baris berisi dua nama yang berbeda.", "Lima baris seperti contoh.", "Nama terdiri dari huruf kecil, paling panjang 20 huruf."),
     en=("Dynamic Objects", "An object created with `new` is **not** destroyed automatically. It lives until the program calls `delete`, and its destructor runs exactly then. The order of destruction is set by the order of the `delete`s, not by the order of creation.\n\n"
         "Make a class `Benda` (attribute `string nama`; the constructor prints `Buat <nama>`, the destructor prints `Hapus <nama>`). Read two names. Create the first object and then the second with `new`, print `Dua objek di heap` (two objects on the heap), then `delete` the **first** object and after that the second.\n\n"
         "For `meja kursi`:\n\n```\nBuat meja\nBuat kursi\nDua objek di heap\nHapus meja\nHapus kursi\n```" + W_EN,
         "One line with two different names.", "Five lines as in the example.", "Names have lowercase letters, at most 20."),
     ar=("الكائنات الديناميكية", "الكائن المنشأ بـ `new` **لا** يُهدم تلقائيًا. يبقى حيًّا حتى يستدعي البرنامج `delete`، وعندها بالضبط يعمل هادمه. ترتيب الهدم يحدده ترتيب أوامر `delete` لا ترتيب الإنشاء.\n\n"
         "أنشئ صنفًا `Benda` (الخاصية `string nama`؛ الباني يطبع `Buat <nama>` والهادم يطبع `Hapus <nama>`). اقرأ اسمين. أنشئ الكائن الأول ثم الثاني بـ `new` واطبع `Dua objek di heap` (كائنان في الكومة) ثم نفِّذ `delete` على الكائن **الأول** وبعده الثاني.\n\n"
         "عند `meja kursi`:\n\n```\nBuat meja\nBuat kursi\nDua objek di heap\nHapus meja\nHapus kursi\n```" + W_AR,
         "سطر واحد فيه اسمان مختلفان.", "خمسة أسطر كما في المثال.", "الأسماء حروف لاتينية صغيرة، 20 حرفًا على الأكثر."))

soal("salin-dengan-pesan", "mudah_sekali", "trimmed", BENDA_KATA + "print(r.choice(KATA[:10]) if i % 2 == 0 else r.choice(KATA[10:]))\n",
     program(r'''
class Paket {
public:
    string isi;

    Paket(string s) : isi(s) {
        cout << "Buat " << isi << endl;
    }

    Paket(const Paket &lain) : isi(lain.isi + "-salinan") {
        cout << "Salin " << lain.isi << endl;
    }

    ~Paket() {
        cout << "Hapus " << isi << endl;
    }
};''', r'''    string nama;
    cin >> nama;
    Paket asli(nama);
    Paket tiruan = asli;''', "string"), wajib=WS,
     id=("Konstruktor Salin", "Saat sebuah objek dibuat **dari objek lain yang sejenis** — misalnya `Paket tiruan = asli;` — yang berjalan adalah *konstruktor salin*: `Paket(const Paket &lain)`.\n\n"
         "Buat class `Paket` dengan atribut `string isi` dan:\n\n- konstruktor `Paket(string s)` — mencetak `Buat <isi>`;\n- konstruktor salin — mengisi `isi` dengan isi objek asal ditambah `-salinan`, dan mencetak `Salin <isi objek asal>`;\n- destruktor — mencetak `Hapus <isi>`.\n\n"
         "Baca sebuah nama, buat `Paket asli(nama);` lalu `Paket tiruan = asli;`.\n\n"
         "Untuk `buku`:\n\n```\nBuat buku\nSalin buku\nHapus buku-salinan\nHapus buku\n```\n\nSalinan dibuat belakangan, jadi dihancurkan lebih dulu." + S_ID,
         "Satu kata: isi paket.", "Empat baris seperti contoh.", "Kata terdiri dari huruf kecil, paling panjang 20 huruf."),
     en=("Copy Constructor", "When an object is created **from another object of the same kind** — for example `Paket tiruan = asli;` — what runs is the *copy constructor*: `Paket(const Paket &lain)`.\n\n"
         "Make a class `Paket` (parcel) with an attribute `string isi` (content) and:\n\n- a constructor `Paket(string s)` — prints `Buat <isi>`;\n- a copy constructor — fills `isi` with the content of the source object plus `-salinan` (copy), and prints `Salin <content of the source>`;\n- a destructor — prints `Hapus <isi>`.\n\n"
         "Read a name, create `Paket asli(nama);` and then `Paket tiruan = asli;`.\n\n"
         "For `buku`:\n\n```\nBuat buku\nSalin buku\nHapus buku-salinan\nHapus buku\n```\n\nThe copy is created later, so it is destroyed first." + S_EN,
         "One word: the content of the parcel.", "Four lines as in the example.", "The word has lowercase letters, at most 20."),
     ar=("باني النسخ", "عندما يُنشأ كائن **من كائن آخر من النوع نفسه** — مثل `Paket tiruan = asli;` — فالذي يعمل هو *باني النسخ*: `Paket(const Paket &lain)`.\n\n"
         "أنشئ صنفًا `Paket` (طرد) فيه خاصية `string isi` (المحتوى) و:\n\n- بانٍ `Paket(string s)` — يطبع `Buat <isi>`؛\n- باني نسخ — يملأ `isi` بمحتوى الكائن المصدر مضافًا إليه `-salinan` (نسخة) ويطبع `Salin <محتوى المصدر>`؛\n- هادم — يطبع `Hapus <isi>`.\n\n"
         "اقرأ اسمًا وأنشئ `Paket asli(nama);` ثم `Paket tiruan = asli;`.\n\n"
         "عند `buku`:\n\n```\nBuat buku\nSalin buku\nHapus buku-salinan\nHapus buku\n```\n\nالنسخة أُنشئت لاحقًا فتُهدم أولًا." + S_AR,
         "كلمة واحدة: محتوى الطرد.", "أربعة أسطر كما في المثال.", "الكلمة حروف لاتينية صغيرة، 20 حرفًا على الأكثر."))

soal("waktu-bawaan", "mudah_sekali", "trimmed", "if i % 2 == 0:\n    print(1)\n    print(r.randint(0, 23))\nelse:\n    print(2)\n    print(r.randint(0, 23), r.randint(0, 59))\n",
     program(r'''
class Waktu {
public:
    int jam;
    int menit;

    Waktu(int j, int m = 0) : jam(j), menit(m) {
    }

    void cetak() {
        cout << setfill('0') << setw(2) << jam << ":" << setw(2) << menit << endl;
    }
};''', r'''    int k, j, m;
    cin >> k;
    if (k == 1) {
        cin >> j;
        Waktu w(j);
        w.cetak();
    } else {
        cin >> j >> m;
        Waktu w(j, m);
        w.cetak();
    }''', "iomanip"), wajib=W,
     id=("Daftar Inisialisasi", "Konstruktor sebaiknya mengisi atribut lewat **daftar inisialisasi** — bagian setelah titik dua, sebelum badan konstruktor:\n\n```\nWaktu(int j, int m = 0) : jam(j), menit(m) { }\n```\n\n"
         "Dengan cara itu atribut langsung lahir dengan nilai yang benar, bukan dibuat dulu lalu diisi. Parameter konstruktor juga boleh punya nilai bawaan.\n\n"
         "Buat class `Waktu` dengan atribut `int jam`, `int menit`, konstruktor seperti di atas, dan method `void cetak()` yang mencetak `JJ:MM` (dua digit).\n\n"
         "Masukan berisi k lalu k bilangan: jam saja, atau jam dan menit. Buat objeknya dengan tepat k argumen dan cetak.\n\nContoh: `1` lalu `7` → `07:00`; `2` lalu `7 5` → `07:05`." + W_ID,
         "Baris pertama: k (1 atau 2). Baris kedua: k bilangan bulat.", "Waktu dalam bentuk `JJ:MM`.", "0 ≤ jam ≤ 23; 0 ≤ menit ≤ 59"),
     en=("Initialiser List", "A constructor had better fill the attributes through the **initialiser list** — the part after the colon, before the constructor's body:\n\n```\nWaktu(int j, int m = 0) : jam(j), menit(m) { }\n```\n\n"
         "That way the attributes are born with the right values instead of being made first and filled afterwards. Constructor parameters may have default values too.\n\n"
         "Make a class `Waktu` (time) with the attributes `int jam`, `int menit`, the constructor above, and a method `void cetak()` that prints `HH:MM` (two digits each).\n\n"
         "The input holds k and then k numbers: the hour only, or the hour and the minute. Create the object with exactly k arguments and print it.\n\nExamples: `1` then `7` → `07:00`; `2` then `7 5` → `07:05`." + W_EN,
         "First line: k (1 or 2). Second line: k integers.", "The time in the form `HH:MM`.", "0 ≤ hour ≤ 23; 0 ≤ minute ≤ 59"),
     ar=("قائمة التهيئة", "يحسن بالباني أن يملأ الخصائص عبر **قائمة التهيئة** — الجزء الذي يلي النقطتين ويسبق جسم الباني:\n\n```\nWaktu(int j, int m = 0) : jam(j), menit(m) { }\n```\n\n"
         "بهذا تولد الخصائص بقيمها الصحيحة بدل أن تُنشأ أولًا ثم تُملأ. ويجوز أن تكون لوسائط الباني قيم افتراضية أيضًا.\n\n"
         "أنشئ صنفًا `Waktu` (وقت) خاصيتاه `int jam` و `int menit`، وبانيه كما في الأعلى، وطريقة `void cetak()` تطبع `HH:MM` (رقمان لكل جزء).\n\n"
         "في المدخل k ثم k عددًا: الساعة فقط، أو الساعة والدقيقة. أنشئ الكائن بعدد k من الوسائط بالضبط واطبعه.\n\nأمثلة: `1` ثم `7` ← `07:00`؛ `2` ثم `7 5` ← `07:05`." + W_AR,
         "السطر الأول: k (1 أو 2). السطر الثاني: k عددًا صحيحًا.", "الوقت على الصورة `HH:MM`.", "0 ≤ الساعة ≤ 23؛ 0 ≤ الدقيقة ≤ 59"))

# ================================= MUDAH (8) =================================

soal("barisan-prajurit", "mudah", "trimmed", "print({2: 1, 3: 10}.get(i, r.randint(2, 5) if i % 2 == 0 else r.randint(6, 9)))\n",
     program(r'''
class Prajurit {
public:
    static int banyak;
    int nomor;

    Prajurit() {
        banyak++;
        nomor = banyak;
        cout << "Prajurit " << nomor << " siap" << endl;
    }

    ~Prajurit() {
        cout << "Prajurit " << nomor << " bubar" << endl;
    }
};

int Prajurit::banyak = 0;''', r'''    int n;
    cin >> n;
    Prajurit *regu = new Prajurit[n];
    cout << "Regu lengkap" << endl;
    delete[] regu;
    regu = nullptr;'''), wajib=W,
     id=("Barisan Prajurit", "`new Prajurit[n]` membuat n objek sekaligus: konstruktor bawaan dipanggil untuk tiap elemen, **dari indeks 0 ke atas**. `delete[]` menghancurkan semuanya **dalam urutan terbalik**.\n\n"
         "Buat class `Prajurit` dengan `static int banyak` (mula-mula 0) dan `int nomor`. Konstruktornya menaikkan `banyak`, mengisi `nomor`, dan mencetak `Prajurit <nomor> siap`. Destruktornya mencetak `Prajurit <nomor> bubar`.\n\n"
         "Baca n, buat regu berisi n prajurit dengan `new[]`, cetak `Regu lengkap`, lalu bubarkan dengan `delete[]`.\n\n"
         "Untuk n = 2:\n\n```\nPrajurit 1 siap\nPrajurit 2 siap\nRegu lengkap\nPrajurit 2 bubar\nPrajurit 1 bubar\n```" + W_ID,
         "Satu bilangan bulat n.", "2n + 1 baris seperti contoh.", "1 ≤ n ≤ 10"),
     en=("A Line of Soldiers", "`new Prajurit[n]` creates n objects at once: the default constructor is called for every element, **from index 0 upwards**. `delete[]` destroys them all **in reverse order**.\n\n"
         "Make a class `Prajurit` (soldier) with `static int banyak` (count, starting at 0) and `int nomor`. Its constructor raises `banyak`, sets `nomor`, and prints `Prajurit <number> siap` (ready). Its destructor prints `Prajurit <number> bubar` (dismissed).\n\n"
         "Read n, create a squad of n soldiers with `new[]`, print `Regu lengkap` (squad complete), then dismiss it with `delete[]`.\n\n"
         "For n = 2:\n\n```\nPrajurit 1 siap\nPrajurit 2 siap\nRegu lengkap\nPrajurit 2 bubar\nPrajurit 1 bubar\n```" + W_EN,
         "One integer n.", "2n + 1 lines as in the example.", "1 ≤ n ≤ 10"),
     ar=("صف الجنود", "`new Prajurit[n]` ينشئ n كائنًا دفعة واحدة: يُستدعى الباني الافتراضي لكل عنصر **من الفهرس 0 صعودًا**. و `delete[]` يهدمها كلها **بترتيب معكوس**.\n\n"
         "أنشئ صنفًا `Prajurit` (جندي) فيه `static int banyak` (العدد، ويبدأ من 0) و `int nomor`. بانيه يزيد `banyak` ويضبط `nomor` ويطبع `Prajurit <الرقم> siap` (جاهز). وهادمه يطبع `Prajurit <الرقم> bubar` (انصرف).\n\n"
         "اقرأ n وأنشئ مجموعة من n جنديًا بـ `new[]` واطبع `Regu lengkap` (اكتملت المجموعة) ثم اصرفها بـ `delete[]`.\n\n"
         "عند n = 2:\n\n```\nPrajurit 1 siap\nPrajurit 2 siap\nRegu lengkap\nPrajurit 2 bubar\nPrajurit 1 bubar\n```" + W_AR,
         "عدد صحيح واحد n.", "2n + 1 سطرًا كما في المثال.", "1 ≤ n ≤ 10"))

soal("parameter-nilai", "mudah", "trimmed", BENDA_KATA + "print(r.choice(KATA[:10]) if i % 2 == 0 else r.choice(KATA[10:]))\n",
     program(PAKET + r'''

void periksa(Paket p) {
    cout << "Periksa " << p.isi << endl;
}''', r'''    string nama;
    cin >> nama;
    Paket asli(nama);
    periksa(asli);
    cout << "Kembali ke main" << endl;''', "string"), wajib=WS,
     id=("Parameter by Value", "Mengirim objek ke fungsi **by value** berarti fungsi menerima *salinan*: konstruktor salin berjalan saat fungsi dipanggil, dan destruktor salinan itu berjalan saat fungsi selesai.\n\n"
         "Buat class `Paket` dengan atribut `string isi`; konstruktornya mencetak `Buat <isi>`, konstruktor salinnya mencetak `Salin <isi>`, destruktornya mencetak `Hapus <isi>`.\n\n"
         "Tulis fungsi `void periksa(Paket p)` yang mencetak `Periksa <isi>`. Di `main`, baca sebuah nama, buat satu paket, panggil `periksa`, lalu cetak `Kembali ke main`.\n\n"
         "Untuk `buku`:\n\n```\nBuat buku\nSalin buku\nPeriksa buku\nHapus buku\nKembali ke main\nHapus buku\n```\n\n`Hapus` yang pertama adalah salinan milik fungsi; yang kedua adalah paket asli." + S_ID,
         "Satu kata: isi paket.", "Enam baris seperti contoh.", "Kata terdiri dari huruf kecil, paling panjang 20 huruf."),
     en=("Passing by Value", "Passing an object to a function **by value** means the function receives a *copy*: the copy constructor runs when the function is called, and the copy's destructor runs when the function finishes.\n\n"
         "Make a class `Paket` with an attribute `string isi`; its constructor prints `Buat <isi>`, its copy constructor prints `Salin <isi>`, its destructor prints `Hapus <isi>`.\n\n"
         "Write a function `void periksa(Paket p)` (inspect) that prints `Periksa <isi>`. In `main`, read a name, create one parcel, call `periksa`, then print `Kembali ke main` (back in main).\n\n"
         "For `buku`:\n\n```\nBuat buku\nSalin buku\nPeriksa buku\nHapus buku\nKembali ke main\nHapus buku\n```\n\nThe first `Hapus` is the function's copy; the second is the original parcel." + S_EN,
         "One word: the content of the parcel.", "Six lines as in the example.", "The word has lowercase letters, at most 20."),
     ar=("التمرير بالقيمة", "تمرير كائن إلى دالة **بالقيمة** يعني أن الدالة تتلقى *نسخة*: يعمل باني النسخ عند استدعاء الدالة، ويعمل هادم النسخة عند انتهاء الدالة.\n\n"
         "أنشئ صنفًا `Paket` فيه خاصية `string isi`؛ بانيه يطبع `Buat <isi>` وباني نسخه يطبع `Salin <isi>` وهادمه يطبع `Hapus <isi>`.\n\n"
         "اكتب دالة `void periksa(Paket p)` (افحص) تطبع `Periksa <isi>`. في `main` اقرأ اسمًا وأنشئ طردًا واحدًا واستدعِ `periksa` ثم اطبع `Kembali ke main` (العودة إلى main).\n\n"
         "عند `buku`:\n\n```\nBuat buku\nSalin buku\nPeriksa buku\nHapus buku\nKembali ke main\nHapus buku\n```\n\n`Hapus` الأولى هي نسخة الدالة، والثانية هي الطرد الأصلي." + S_AR,
         "كلمة واحدة: محتوى الطرد.", "ستة أسطر كما في المثال.", "الكلمة حروف لاتينية صغيرة، 20 حرفًا على الأكثر."))

soal("hitung-salinan", "mudah", "trimmed", "n = {2: 1}.get(i, r.randint(2, 40))\np = {3: 0.0, 4: 1.0}.get(i, 0.5)\nprint(''.join('N' if r.random() < p else 'R' for _ in range(n)))\n",
     program(r'''
class Benda {
public:
    static int salinan;
    int berat;

    Benda() : berat(1) {
    }

    Benda(const Benda &lain) : berat(lain.berat) {
        salinan++;
    }
};

int Benda::salinan = 0;

int lewatNilai(Benda b) {
    return b.berat;
}

int lewatReferensi(const Benda &b) {
    return b.berat;
}''', r'''    string cara;
    cin >> cara;
    Benda benda;
    auto total = 0;
    for (auto c : cara) {
        if (c == 'N') {
            total += lewatNilai(benda);
        } else {
            total += lewatReferensi(benda);
        }
    }
    cout << "Panggilan: " << total << endl;
    cout << "Salinan: " << Benda::salinan << endl;''', "string"), wajib=WS,
     id=("Berapa Kali Disalin?", "Mengirim objek *by value* menyalinnya; mengirim *by reference* (`const Benda &b`) tidak. Untuk objek besar, bedanya terasa.\n\n"
         "Buat class `Benda` dengan atribut `int berat` (diisi 1 oleh konstruktor bawaan) dan `static int salinan`. Konstruktor salinnya menaikkan `salinan`.\n\n"
         "Tulis dua fungsi yang sama-sama mengembalikan `berat` objeknya:\n\n- `int lewatNilai(Benda b)`;\n- `int lewatReferensi(const Benda &b)`.\n\n"
         "Masukan berupa deretan huruf: `N` berarti panggil `lewatNilai`, `R` berarti panggil `lewatReferensi`, selalu dengan satu objek yang sama. Cetak `Panggilan: p` (jumlah semua nilai kembalian) dan `Salinan: s`.\n\n"
         "Untuk `NRNNR`:\n\n```\nPanggilan: 5\nSalinan: 3\n```" + S_ID,
         "Satu baris: deretan huruf `N` dan `R` tanpa spasi.", "Dua baris: `Panggilan: p` dan `Salinan: s`.", "Panjang deretan 1 sampai 40."),
     en=("How Many Copies?", "Passing an object *by value* copies it; passing it *by reference* (`const Benda &b`) does not. For a large object the difference matters.\n\n"
         "Make a class `Benda` with an attribute `int berat` (weight, set to 1 by the default constructor) and `static int salinan` (copies). Its copy constructor raises `salinan`.\n\n"
         "Write two functions that both return the `berat` of their object:\n\n- `int lewatNilai(Benda b)` (by value);\n- `int lewatReferensi(const Benda &b)` (by reference).\n\n"
         "The input is a run of letters: `N` means call `lewatNilai`, `R` means call `lewatReferensi`, always with one and the same object. Print `Panggilan: p` (the sum of all return values) and `Salinan: s`.\n\n"
         "For `NRNNR`:\n\n```\nPanggilan: 5\nSalinan: 3\n```" + S_EN,
         "One line: a run of the letters `N` and `R` without spaces.", "Two lines: `Panggilan: p` and `Salinan: s`.", "The run is 1 to 40 letters long."),
     ar=("كم مرة نُسخ؟", "تمرير الكائن *بالقيمة* ينسخه؛ وتمريره *بالمرجع* (`const Benda &b`) لا ينسخه. وللكائن الكبير يظهر الفرق.\n\n"
         "أنشئ صنفًا `Benda` فيه خاصية `int berat` (الوزن، يضبطها الباني الافتراضي على 1) و `static int salinan` (النسخ). باني نسخه يزيد `salinan`.\n\n"
         "اكتب دالتين تُرجعان كلتاهما `berat` كائنهما:\n\n- `int lewatNilai(Benda b)` (بالقيمة)؛\n- `int lewatReferensi(const Benda &b)` (بالمرجع).\n\n"
         "المدخل سلسلة حروف: `N` تعني استدعِ `lewatNilai` و `R` تعني استدعِ `lewatReferensi`، دائمًا بالكائن نفسه. اطبع `Panggilan: p` (مجموع كل القيم المُرجعة) و `Salinan: s`.\n\n"
         "عند `NRNNR`:\n\n```\nPanggilan: 5\nSalinan: 3\n```" + S_AR,
         "سطر واحد: سلسلة من الحرفين `N` و `R` بلا مسافات.", "سطران: `Panggilan: p` و `Salinan: s`.", "طول السلسلة من 1 إلى 40."))

soal("konstruktor-delegasi", "mudah", "trimmed", "if i % 2 == 0:\n    print(1)\n    print(r.randint(1, 100))\nelse:\n    print(2)\n    print(r.randint(1, 100), r.randint(1, 100))\n",
     program(r'''
class PersegiPanjang {
public:
    int panjang;
    int lebar;

    PersegiPanjang(int p, int l) : panjang(p), lebar(l) {
        cout << "Konstruktor dua parameter" << endl;
    }

    PersegiPanjang(int sisi) : PersegiPanjang(sisi, sisi) {
        cout << "Konstruktor satu parameter" << endl;
    }

    int luas() {
        return panjang * lebar;
    }
};''', r'''    int k, a, b;
    cin >> k;
    if (k == 1) {
        cin >> a;
        PersegiPanjang p(a);
        cout << "Luas: " << p.luas() << endl;
    } else {
        cin >> a >> b;
        PersegiPanjang p(a, b);
        cout << "Luas: " << p.luas() << endl;
    }'''), wajib=W,
     id=("Konstruktor yang Mendelegasikan", "Sebuah konstruktor boleh **menyerahkan pekerjaannya kepada konstruktor lain** dari class yang sama, lewat daftar inisialisasi:\n\n```\nPersegiPanjang(int sisi) : PersegiPanjang(sisi, sisi) { … }\n```\n\n"
         "Konstruktor yang dituju berjalan lebih dulu sampai selesai, baru badan konstruktor yang mendelegasikan.\n\n"
         "Buat class `PersegiPanjang` dengan atribut `int panjang`, `int lebar`, method `int luas()`, dan dua konstruktor:\n\n- dua parameter — mengisi kedua atribut dan mencetak `Konstruktor dua parameter`;\n- satu parameter — mendelegasikan seperti di atas (membuat persegi) dan mencetak `Konstruktor satu parameter`.\n\n"
         "Masukan berisi k lalu k ukuran. Buat objek dengan tepat k argumen, lalu cetak `Luas: L`.\n\nUntuk `1` lalu `5`:\n\n```\nKonstruktor dua parameter\nKonstruktor satu parameter\nLuas: 25\n```" + W_ID,
         "Baris pertama: k (1 atau 2). Baris kedua: k bilangan bulat.", "Baris-baris dari konstruktor, lalu `Luas: L`.", "Tiap ukuran antara 1 dan 100."),
     en=("Delegating Constructor", "A constructor may **hand its work to another constructor** of the same class, through the initialiser list:\n\n```\nPersegiPanjang(int sisi) : PersegiPanjang(sisi, sisi) { … }\n```\n\n"
         "The constructor delegated to runs first, to the end; only then does the body of the delegating one run.\n\n"
         "Make a class `PersegiPanjang` (rectangle) with the attributes `int panjang`, `int lebar`, a method `int luas()`, and two constructors:\n\n- two parameters — fills both attributes and prints `Konstruktor dua parameter`;\n- one parameter — delegates as above (making a square) and prints `Konstruktor satu parameter`.\n\n"
         "The input holds k and then k sizes. Create the object with exactly k arguments, then print `Luas: L` (area).\n\nFor `1` then `5`:\n\n```\nKonstruktor dua parameter\nKonstruktor satu parameter\nLuas: 25\n```" + W_EN,
         "First line: k (1 or 2). Second line: k integers.", "The lines from the constructors, then `Luas: L`.", "Each size is between 1 and 100."),
     ar=("الباني المفوِّض", "يجوز لبانٍ أن **يفوّض عمله إلى بانٍ آخر** من الصنف نفسه عبر قائمة التهيئة:\n\n```\nPersegiPanjang(int sisi) : PersegiPanjang(sisi, sisi) { … }\n```\n\n"
         "الباني المفوَّض إليه يعمل أولًا حتى ينتهي، ثم يعمل جسم الباني المفوِّض.\n\n"
         "أنشئ صنفًا `PersegiPanjang` (مستطيل) خاصيتاه `int panjang` و `int lebar` وطريقة `int luas()` وبانيان:\n\n- بوسيطين — يملأ الخاصيتين ويطبع `Konstruktor dua parameter`؛\n- بوسيط واحد — يفوّض كما في الأعلى (فينشئ مربعًا) ويطبع `Konstruktor satu parameter`.\n\n"
         "في المدخل k ثم k قياسًا. أنشئ الكائن بعدد k من الوسائط بالضبط ثم اطبع `Luas: L` (المساحة).\n\nعند `1` ثم `5`:\n\n```\nKonstruktor dua parameter\nKonstruktor satu parameter\nLuas: 25\n```" + W_AR,
         "السطر الأول: k (1 أو 2). السطر الثاني: k عددًا صحيحًا.", "أسطر البانيات ثم `Luas: L`.", "كل قياس بين 1 و 100."))

soal("objek-dalam-perulangan", "mudah", "trimmed", BENDA_KATA + "n = {2: 1}.get(i, r.randint(2, 8))\nprint(n)\nprint(*r.sample(KATA, n))\n",
     program(BENDA, r'''    int n;
    cin >> n;
    string nama;
    cin >> nama;
    Benda tetap(nama);
    for (int k = 1; k < n; k++) {
        cin >> nama;
        Benda sementara(nama);
    }
    cout << "Perulangan selesai" << endl;''', "string"), wajib=W,
     id=("Objek di Dalam Perulangan", "Badan perulangan adalah sebuah blok. Objek yang dibuat di dalamnya lahir dan mati **pada setiap putaran**.\n\n"
         "Buat class `Benda` (atribut `string nama`; konstruktor mencetak `Buat <nama>`, destruktor mencetak `Hapus <nama>`).\n\n"
         "Baca n nama. Buat satu objek dari nama pertama **di luar** perulangan. Untuk tiap nama berikutnya, buat sebuah objek **di dalam** badan perulangan. Setelah perulangan, cetak `Perulangan selesai`.\n\n"
         "Untuk `meja kursi lampu`:\n\n```\nBuat meja\nBuat kursi\nHapus kursi\nBuat lampu\nHapus lampu\nPerulangan selesai\nHapus meja\n```" + W_ID,
         "Baris pertama: n. Baris kedua: n nama yang berbeda.", "2n + 1 baris seperti contoh.", "1 ≤ n ≤ 8"),
     en=("Objects inside a Loop", "The body of a loop is a block. An object created in it is born and dies **on every pass**.\n\n"
         "Make a class `Benda` (attribute `string nama`; the constructor prints `Buat <nama>`, the destructor prints `Hapus <nama>`).\n\n"
         "Read n names. Create one object from the first name **outside** the loop. For every further name create an object **inside** the loop body. After the loop print `Perulangan selesai` (loop finished).\n\n"
         "For `meja kursi lampu`:\n\n```\nBuat meja\nBuat kursi\nHapus kursi\nBuat lampu\nHapus lampu\nPerulangan selesai\nHapus meja\n```" + W_EN,
         "First line: n. Second line: n different names.", "2n + 1 lines as in the example.", "1 ≤ n ≤ 8"),
     ar=("كائنات داخل حلقة", "جسم الحلقة كتلة. الكائن المنشأ فيه يولد ويموت **في كل دورة**.\n\n"
         "أنشئ صنفًا `Benda` (الخاصية `string nama`؛ الباني يطبع `Buat <nama>` والهادم يطبع `Hapus <nama>`).\n\n"
         "اقرأ n اسمًا. أنشئ كائنًا من الاسم الأول **خارج** الحلقة. ولكل اسم تالٍ أنشئ كائنًا **داخل** جسم الحلقة. بعد الحلقة اطبع `Perulangan selesai` (انتهت الحلقة).\n\n"
         "عند `meja kursi lampu`:\n\n```\nBuat meja\nBuat kursi\nHapus kursi\nBuat lampu\nHapus lampu\nPerulangan selesai\nHapus meja\n```" + W_AR,
         "السطر الأول: n. السطر الثاني: n اسمًا مختلفًا.", "2n + 1 سطرًا كما في المثال.", "1 ≤ n ≤ 8"))

soal("salin-dalam", "mudah", "trimmed", "n = {2: 1}.get(i, r.randint(2, 20))\nprint(n)\nprint(*[r.randint(0, 99) for _ in range(n)])\nprint(r.randint(0, n - 1), r.randint(100, 999))\n",
     program(LARIK, r'''    int n, posisi, nilai;
    cin >> n;
    Larik asli(n);
    asli.baca();
    cin >> posisi >> nilai;
    Larik salinan = asli;
    salinan.data[posisi] = nilai;
    asli.cetak();
    salinan.cetak();'''), wajib=WS,
     id=("Salinan Dalam", "Class `Larik` mengelola array dinamis: `int *data` dan `int banyak`. Bila objek seperti ini disalin begitu saja, yang tersalin hanya **penunjuknya** — kedua objek lalu berbagi array yang sama (*shallow copy*), dan mengubah yang satu mengubah yang lain.\n\n"
         "Konstruktor salin harus membuat array baru dan menyalin isinya (*deep copy*).\n\n"
         "Buat class `Larik` dengan:\n\n- konstruktor `Larik(int n)` — memesan array n elemen;\n- konstruktor salin yang menyalin dalam;\n- destruktor yang membebaskan array;\n- method `void baca()` dan `void cetak()` (elemen dipisah spasi).\n\n"
         "Baca n dan n bilangan ke `asli`, lalu baca posisi p dan nilai v. Buat `Larik salinan = asli;`, ubah `salinan.data[p]` menjadi v, lalu cetak `asli` dan `salinan`.\n\n"
         "Untuk `3`, `5 6 7`, `1 900`:\n\n```\n5 6 7\n5 900 7\n```\n\nBila baris pertama ikut berubah, salinannya masih dangkal." + S_ID,
         "Baris pertama: n. Baris kedua: n bilangan bulat. Baris ketiga: p (mulai dari 0) dan v.", "Dua baris: isi `asli`, lalu isi `salinan`.", "1 ≤ n ≤ 20; 0 ≤ p < n; tiap bilangan antara 0 dan 999"),
     en=("Deep Copy", "The class `Larik` (array) manages a dynamic array: `int *data` and `int banyak` (count). If such an object is copied naively, only the **pointer** is copied — the two objects then share one array (*shallow copy*), and changing one changes the other.\n\n"
         "The copy constructor must make a new array and copy the contents (*deep copy*).\n\n"
         "Make a class `Larik` with:\n\n- a constructor `Larik(int n)` — requests an array of n elements;\n- a copy constructor that copies deeply;\n- a destructor that frees the array;\n- the methods `void baca()` (read) and `void cetak()` (print, elements separated by spaces).\n\n"
         "Read n and n numbers into `asli` (original), then read a position p and a value v. Create `Larik salinan = asli;`, change `salinan.data[p]` to v, then print `asli` and `salinan`.\n\n"
         "For `3`, `5 6 7`, `1 900`:\n\n```\n5 6 7\n5 900 7\n```\n\nIf the first line changes too, the copy is still shallow." + S_EN,
         "First line: n. Second line: n integers. Third line: p (counting from 0) and v.", "Two lines: the contents of `asli`, then of `salinan`.", "1 ≤ n ≤ 20; 0 ≤ p < n; each number is between 0 and 999"),
     ar=("النسخ العميق", "الصنف `Larik` (مصفوفة) يدير مصفوفة ديناميكية: `int *data` و `int banyak` (العدد). إذا نُسخ كائن كهذا نسخًا بسيطًا لم يُنسخ إلا **المؤشر** — فيتشارك الكائنان مصفوفة واحدة (*نسخ سطحي*) وتغيير أحدهما يغيّر الآخر.\n\n"
         "يجب أن ينشئ باني النسخ مصفوفة جديدة وينسخ المحتوى (*نسخ عميق*).\n\n"
         "أنشئ صنفًا `Larik` فيه:\n\n- بانٍ `Larik(int n)` — يطلب مصفوفة من n عنصرًا؛\n- باني نسخ ينسخ نسخًا عميقًا؛\n- هادم يحرر المصفوفة؛\n- الطريقتان `void baca()` (اقرأ) و `void cetak()` (اطبع، والعناصر تفصل بينها مسافات).\n\n"
         "اقرأ n و n عددًا في `asli` (الأصل) ثم اقرأ موضعًا p وقيمة v. أنشئ `Larik salinan = asli;` وغيِّر `salinan.data[p]` إلى v ثم اطبع `asli` و `salinan`.\n\n"
         "عند `3` ثم `5 6 7` ثم `1 900`:\n\n```\n5 6 7\n5 900 7\n```\n\nإذا تغيّر السطر الأول أيضًا فالنسخ ما زال سطحيًا." + S_AR,
         "السطر الأول: n. السطر الثاني: n عددًا صحيحًا. السطر الثالث: p (العدّ من 0) و v.", "سطران: محتوى `asli` ثم محتوى `salinan`.", "1 ≤ n ≤ 20؛ 0 ≤ p < n؛ كل عدد بين 0 و 999"))

soal("penugasan-salin", "mudah", "trimmed", "n, m = r.randint(1, 15), r.randint(1, 15)\nif i == 2:\n    m = n\nprint(n)\nprint(*[r.randint(0, 99) for _ in range(n)])\nprint(m)\nprint(*[r.randint(0, 99) for _ in range(m)])\nprint(r.randint(100, 999))\n",
     program(LARIK, r'''    int n, m, x;
    cin >> n;
    Larik a(n);
    a.baca();
    cin >> m;
    Larik b(m);
    b.baca();
    cin >> x;
    b = a;
    b = b;
    a.data[0] = x;
    a.cetak();
    b.cetak();'''), wajib=WS,
     id=("Operator Penugasan", "`Larik b = a;` membuat objek baru (konstruktor salin). `b = a;` berbeda: `b` **sudah ada** dan sudah punya array sendiri. Yang berjalan adalah *operator penugasan salin*:\n\n```\nLarik &operator=(const Larik &lain)\n```\n\n"
         "Operator itu harus: (1) tidak berbuat apa-apa bila sebuah objek ditugaskan ke dirinya sendiri (`if (this != &lain)`), (2) membebaskan array lama, (3) memesan array baru seukuran `lain` dan menyalin isinya, (4) mengembalikan `*this`.\n\n"
         "Lengkapi class `Larik` (atribut `int *data`, `int banyak`; konstruktor `Larik(int n)`, konstruktor salin, destruktor, `baca()`, `cetak()`) dengan operator itu.\n\n"
         "Baca larik `a` dan larik `b` (ukurannya boleh berbeda), lalu sebuah bilangan x. Jalankan `b = a;`, lalu `b = b;`, lalu ubah `a.data[0]` menjadi x. Cetak `a`, lalu `b`.\n\n"
         "Untuk a = `1 2 3`, b = `7 8`, x = 500:\n\n```\n500 2 3\n1 2 3\n```" + S_ID,
         "Baris 1: n. Baris 2: n bilangan (larik a). Baris 3: m. Baris 4: m bilangan (larik b). Baris 5: x.", "Dua baris: isi `a`, lalu isi `b`.", "1 ≤ n, m ≤ 15; tiap bilangan antara 0 dan 999"),
     en=("Assignment Operator", "`Larik b = a;` creates a new object (copy constructor). `b = a;` is different: `b` **already exists** and already owns an array. What runs is the *copy assignment operator*:\n\n```\nLarik &operator=(const Larik &lain)\n```\n\n"
         "That operator must: (1) do nothing when an object is assigned to itself (`if (this != &lain)`), (2) free the old array, (3) request a new array the size of `lain` and copy its contents, (4) return `*this`.\n\n"
         "Complete the class `Larik` (attributes `int *data`, `int banyak`; constructor `Larik(int n)`, copy constructor, destructor, `baca()`, `cetak()`) with that operator.\n\n"
         "Read array `a` and array `b` (their sizes may differ), then a number x. Run `b = a;`, then `b = b;`, then change `a.data[0]` to x. Print `a`, then `b`.\n\n"
         "For a = `1 2 3`, b = `7 8`, x = 500:\n\n```\n500 2 3\n1 2 3\n```" + S_EN,
         "Line 1: n. Line 2: n numbers (array a). Line 3: m. Line 4: m numbers (array b). Line 5: x.", "Two lines: the contents of `a`, then of `b`.", "1 ≤ n, m ≤ 15; each number is between 0 and 999"),
     ar=("عامل الإسناد", "`Larik b = a;` ينشئ كائنًا جديدًا (باني النسخ). أما `b = a;` فمختلف: `b` **موجود أصلًا** وله مصفوفته. الذي يعمل هو *عامل إسناد النسخ*:\n\n```\nLarik &operator=(const Larik &lain)\n```\n\n"
         "يجب على هذا العامل: (1) ألّا يفعل شيئًا إذا أُسند الكائن إلى نفسه (`if (this != &lain)`)، (2) أن يحرر المصفوفة القديمة، (3) أن يطلب مصفوفة جديدة بحجم `lain` وينسخ محتواها، (4) أن يُرجع `*this`.\n\n"
         "أكمل الصنف `Larik` (الخاصيتان `int *data` و `int banyak`؛ الباني `Larik(int n)` وباني النسخ والهادم و `baca()` و `cetak()`) بهذا العامل.\n\n"
         "اقرأ المصفوفة `a` والمصفوفة `b` (قد يختلف حجماهما) ثم عددًا x. نفِّذ `b = a;` ثم `b = b;` ثم غيِّر `a.data[0]` إلى x. اطبع `a` ثم `b`.\n\n"
         "عند a = `1 2 3` و b = `7 8` و x = 500:\n\n```\n500 2 3\n1 2 3\n```" + S_AR,
         "السطر 1: n. السطر 2: n عددًا (المصفوفة a). السطر 3: m. السطر 4: m عددًا (المصفوفة b). السطر 5: x.", "سطران: محتوى `a` ثم محتوى `b`.", "1 ≤ n, m ≤ 15؛ كل عدد بين 0 و 999"))

soal("rekening-const", "mudah", "trimmed", "n = {2: 1}.get(i, r.randint(2, 10))\nprint(n, r.randint(1, 10))\nfor k in r.sample(range(1000, 9999), n):\n    print(k, r.randint(0, 500) * 1000)\n",
     program(r'''
class Rekening {
public:
    const int nomor;
    int saldo;

    Rekening(int n, int s) : nomor(n), saldo(s) {
    }

    void beriBunga(int persen) {
        saldo += saldo / 100 * persen;
    }
};''', r'''    int n, persen;
    cin >> n >> persen;
    for (int k = 0; k < n; k++) {
        int nomor, saldo;
        cin >> nomor >> saldo;
        Rekening rek(nomor, saldo);
        rek.beriBunga(persen);
        cout << rek.nomor << ": " << rek.saldo << endl;
    }'''), wajib=W,
     id=("Anggota const", "Atribut yang nilainya tidak boleh berubah seumur hidup objek ditandai `const`. Atribut seperti itu **tidak bisa diisi dengan `=` di dalam badan konstruktor**; satu-satunya tempat mengisinya adalah daftar inisialisasi.\n\n"
         "Buat class `Rekening` dengan atribut `const int nomor` dan `int saldo`, konstruktor `Rekening(int n, int s) : nomor(n), saldo(s) { }`, dan method `void beriBunga(int persen)` yang menambah saldo sebesar persen dari saldo.\n\n"
         "Baca n rekening dan persen bunganya. Untuk tiap rekening buat sebuah objek, beri bunga, lalu cetak `<nomor>: <saldo>`.\n\n"
         "Contoh: bunga 5 persen; rekening `1234 200000` → `1234: 210000`." + W_ID,
         "Baris pertama: n dan persen bunga. Lalu n baris, masing-masing berisi nomor rekening dan saldo.", "n baris: nomor rekening dan saldo setelah bunga.", "1 ≤ n ≤ 10; 1 ≤ persen ≤ 10; saldo kelipatan 1000 antara 0 dan 500000"),
     en=("const Members", "An attribute whose value must not change during the object's life is marked `const`. Such an attribute **cannot be filled with `=` in the constructor's body**; the only place to fill it is the initialiser list.\n\n"
         "Make a class `Rekening` (account) with the attributes `const int nomor` (number) and `int saldo` (balance), a constructor `Rekening(int n, int s) : nomor(n), saldo(s) { }`, and a method `void beriBunga(int persen)` (add interest) that raises the balance by that percentage of the balance.\n\n"
         "Read n accounts and the interest percentage. For each account create an object, add the interest, then print `<number>: <balance>`.\n\n"
         "Example: 5 percent interest; the account `1234 200000` → `1234: 210000`." + W_EN,
         "First line: n and the interest percentage. Then n lines, each with an account number and a balance.", "n lines: the account number and the balance after interest.", "1 ≤ n ≤ 10; 1 ≤ percentage ≤ 10; balances are multiples of 1000 between 0 and 500000"),
     ar=("الأعضاء الثابتة", "الخاصية التي يجب ألّا تتغير قيمتها طوال حياة الكائن توسم بـ `const`. خاصية كهذه **لا يمكن ملؤها بـ `=` في جسم الباني**؛ والمكان الوحيد لملئها قائمة التهيئة.\n\n"
         "أنشئ صنفًا `Rekening` (حساب) خاصيتاه `const int nomor` (الرقم) و `int saldo` (الرصيد)، وبانٍ `Rekening(int n, int s) : nomor(n), saldo(s) { }`، وطريقة `void beriBunga(int persen)` (أضف الفائدة) تزيد الرصيد بتلك النسبة المئوية منه.\n\n"
         "اقرأ n حسابًا ونسبة الفائدة. لكل حساب أنشئ كائنًا وأضف الفائدة ثم اطبع `<الرقم>: <الرصيد>`.\n\n"
         "مثال: الفائدة 5 بالمئة؛ الحساب `1234 200000` ← `1234: 210000`." + W_AR,
         "السطر الأول: n ونسبة الفائدة. ثم n سطرًا في كل منها رقم حساب ورصيد.", "n سطرًا: رقم الحساب والرصيد بعد الفائدة.", "1 ≤ n ≤ 10؛ 1 ≤ النسبة ≤ 10؛ الأرصدة من مضاعفات 1000 بين 0 و 500000"))

# ============================== MUDAH-SEDANG (6) ==============================

soal("tumpukan-objek", "mudah_sedang", "trimmed", "n = {2: 1}.get(i, r.randint(2, 30))\np = {3: 1.0, 4: 0.3}.get(i, 0.6)\nprint(''.join('B' if r.random() < p else 'H' for _ in range(n)))\n",
     program(r'''
class Peti {
public:
    static int dibuat;
    int nomor;

    Peti() {
        dibuat++;
        nomor = dibuat;
        cout << "Buat " << nomor << endl;
    }

    ~Peti() {
        cout << "Hapus " << nomor << endl;
    }
};

int Peti::dibuat = 0;''', r'''    string perintah;
    cin >> perintah;
    Peti *tumpuk[30];
    auto banyak = 0;
    for (auto p : perintah) {
        if (p == 'B') {
            tumpuk[banyak] = new Peti();
            banyak++;
        } else if (banyak == 0) {
            cout << "Kosong" << endl;
        } else {
            banyak--;
            delete tumpuk[banyak];
        }
    }
    cout << "Akhir perintah" << endl;
    while (banyak > 0) {
        banyak--;
        delete tumpuk[banyak];
    }'''), wajib=W,
     id=("Tumpukan Peti", "Peti-peti ditumpuk di gudang. Tiap peti yang dibuat mendapat nomor urut berikutnya (1, 2, 3, …); nomor yang sudah dipakai tidak pernah dipakai lagi.\n\n"
         "Buat class `Peti` dengan `static int dibuat` dan `int nomor`; konstruktornya memberi nomor dan mencetak `Buat <nomor>`, destruktornya mencetak `Hapus <nomor>`.\n\n"
         "Masukan berupa deretan perintah:\n\n- `B` — buat peti baru dengan `new` dan taruh di puncak tumpukan;\n- `H` — hancurkan peti di puncak dengan `delete`; bila tumpukan kosong, cetak `Kosong`.\n\n"
         "Setelah semua perintah, cetak `Akhir perintah`, lalu hancurkan peti yang tersisa **dari puncak ke dasar**. Tidak boleh ada peti yang bocor.\n\n"
         "Untuk `BBHBHHH`:\n\n```\nBuat 1\nBuat 2\nHapus 2\nBuat 3\nHapus 3\nHapus 1\nKosong\nAkhir perintah\n```" + W_ID,
         "Satu baris: deretan huruf `B` dan `H` tanpa spasi.", "Satu baris per kejadian, seperti contoh.", "Panjang deretan 1 sampai 30."),
     en=("A Stack of Crates", "Crates are stacked in a warehouse. Every crate created gets the next number (1, 2, 3, …); a number once used is never used again.\n\n"
         "Make a class `Peti` (crate) with `static int dibuat` and `int nomor`; its constructor hands out the number and prints `Buat <number>`, its destructor prints `Hapus <number>`.\n\n"
         "The input is a run of commands:\n\n- `B` — create a new crate with `new` and put it on top of the stack;\n- `H` — destroy the top crate with `delete`; if the stack is empty, print `Kosong` (empty).\n\n"
         "After all commands print `Akhir perintah` (end of commands), then destroy the remaining crates **from the top to the bottom**. No crate may leak.\n\n"
         "For `BBHBHHH`:\n\n```\nBuat 1\nBuat 2\nHapus 2\nBuat 3\nHapus 3\nHapus 1\nKosong\nAkhir perintah\n```" + W_EN,
         "One line: a run of the letters `B` and `H` without spaces.", "One line per event, as in the example.", "The run is 1 to 30 letters long."),
     ar=("كومة صناديق", "تُكدَّس صناديق في مستودع. كل صندوق يُنشأ ينال الرقم التالي (1، 2، 3، …)؛ والرقم المستعمل لا يُستعمل ثانية.\n\n"
         "أنشئ صنفًا `Peti` (صندوق) فيه `static int dibuat` و `int nomor`؛ بانيه يعطي الرقم ويطبع `Buat <الرقم>` وهادمه يطبع `Hapus <الرقم>`.\n\n"
         "المدخل سلسلة أوامر:\n\n- `B` — أنشئ صندوقًا جديدًا بـ `new` وضعه في قمة الكومة؛\n- `H` — اهدم صندوق القمة بـ `delete`؛ وإذا كانت الكومة فارغة فاطبع `Kosong` (فارغة).\n\n"
         "بعد كل الأوامر اطبع `Akhir perintah` (نهاية الأوامر) ثم اهدم الصناديق الباقية **من القمة إلى القاعدة**. لا يجوز أن يتسرب أي صندوق.\n\n"
         "عند `BBHBHHH`:\n\n```\nBuat 1\nBuat 2\nHapus 2\nBuat 3\nHapus 3\nHapus 1\nKosong\nAkhir perintah\n```" + W_AR,
         "سطر واحد: سلسلة من الحرفين `B` و `H` بلا مسافات.", "سطر لكل حدث كما في المثال.", "طول السلسلة من 1 إلى 30."))

soal("aturan-tiga-teks", "mudah_sedang", "trimmed",
     "KATA = ['meja', 'kursi', 'lampu', 'buku', 'pena', 'tas', 'jam', 'kunci', 'pintu', 'kaca', 'peta', 'radio', 'kabel', 'layar', 'botol']\na, b = r.sample(KATA, 2)\nprint(a, b)\n",
     program(TEKS + r'''
    void kapital(int posisi) {
        if (huruf[posisi] >= 'a' && huruf[posisi] <= 'z') {
            huruf[posisi] = (char) (huruf[posisi] - 'a' + 'A');
        }
    }

    void cetak() {
        cout << huruf << endl;
    }
};''', r'''    string satu, dua;
    cin >> satu >> dua;
    Teks a(satu);
    Teks b(dua);
    Teks c = a;
    b = c;
    c.kapital(0);
    a.kapital(a.panjang - 1);
    a.cetak();
    b.cetak();
    c.cetak();''', "string"), wajib=WS,
     id=("Aturan Tiga", "**Aturan tiga**: class yang memerlukan destruktor buatan sendiri (karena mengelola memori) hampir pasti juga memerlukan konstruktor salin dan operator penugasan buatan sendiri.\n\n"
         "Buat class `Teks` yang menyimpan teksnya sendiri di memori dinamis: atribut `char *huruf` dan `int panjang`. Lengkapi dengan:\n\n- konstruktor `Teks(const string &s)`;\n- konstruktor salin (salinan dalam);\n- operator penugasan salin;\n- destruktor;\n"
         "- method `void kapital(int posisi)` — mengubah huruf di posisi itu menjadi huruf besar;\n- method `void cetak()`.\n\n"
         "Baca dua kata. Jalankan, persis dalam urutan ini:\n\n```\nTeks a(kata1);\nTeks b(kata2);\nTeks c = a;\nb = c;\nc.kapital(0);\na.kapital(a.panjang - 1);\n```\n\nlalu cetak a, b, dan c.\n\n"
         "Untuk `meja kursi`:\n\n```\nmejA\nmeja\nMeja\n```\n\nKetiga objek harus benar-benar terpisah: tiap perubahan hanya terlihat pada satu objek." + S_ID,
         "Satu baris berisi dua kata dari huruf kecil.", "Tiga baris: isi a, b, dan c.", "Panjang tiap kata 2 sampai 10 huruf."),
     en=("The Rule of Three", "**The rule of three**: a class that needs a destructor of its own (because it manages memory) almost certainly needs a copy constructor and an assignment operator of its own too.\n\n"
         "Make a class `Teks` (text) that keeps its text in dynamic memory: the attributes `char *huruf` and `int panjang` (length). Give it:\n\n- a constructor `Teks(const string &s)`;\n- a copy constructor (deep copy);\n- a copy assignment operator;\n- a destructor;\n"
         "- a method `void kapital(int posisi)` — turns the letter at that position into a capital;\n- a method `void cetak()`.\n\n"
         "Read two words. Run, exactly in this order:\n\n```\nTeks a(kata1);\nTeks b(kata2);\nTeks c = a;\nb = c;\nc.kapital(0);\na.kapital(a.panjang - 1);\n```\n\nthen print a, b and c.\n\n"
         "For `meja kursi`:\n\n```\nmejA\nmeja\nMeja\n```\n\nThe three objects must be truly separate: each change shows in one object only." + S_EN,
         "One line with two words of lowercase letters.", "Three lines: the contents of a, b and c.", "Each word is 2 to 10 letters long."),
     ar=("قاعدة الثلاثة", "**قاعدة الثلاثة**: الصنف الذي يحتاج إلى هادم خاص (لأنه يدير ذاكرة) يحتاج شبه يقين إلى باني نسخ وعامل إسناد خاصين أيضًا.\n\n"
         "أنشئ صنفًا `Teks` (نص) يحفظ نصه في ذاكرة ديناميكية: الخاصيتان `char *huruf` و `int panjang` (الطول). أكمله بـ:\n\n- بانٍ `Teks(const string &s)`؛\n- باني نسخ (نسخ عميق)؛\n- عامل إسناد نسخ؛\n- هادم؛\n"
         "- طريقة `void kapital(int posisi)` — تحوّل الحرف في ذلك الموضع إلى حرف كبير؛\n- طريقة `void cetak()`.\n\n"
         "اقرأ كلمتين. نفِّذ بهذا الترتيب تمامًا:\n\n```\nTeks a(kata1);\nTeks b(kata2);\nTeks c = a;\nb = c;\nc.kapital(0);\na.kapital(a.panjang - 1);\n```\n\nثم اطبع a و b و c.\n\n"
         "عند `meja kursi`:\n\n```\nmejA\nmeja\nMeja\n```\n\nيجب أن تكون الكائنات الثلاثة منفصلة حقًّا: كل تغيير يظهر في كائن واحد فقط." + S_AR,
         "سطر واحد فيه كلمتان من حروف لاتينية صغيرة.", "ثلاثة أسطر: محتوى a و b و c.", "طول كل كلمة من 2 إلى 10 حروف."))

soal("penugasan-berantai", "mudah_sedang", "trimmed", "print(*r.sample(range(1, 100), 3))\n",
     program(r'''
class Kotak {
public:
    char nama;
    int isi;

    Kotak(char n, int i) : nama(n), isi(i) {
    }

    Kotak &operator=(const Kotak &lain) {
        cout << nama << " <- " << lain.nama << " (" << lain.isi << ")" << endl;
        isi = lain.isi;
        return *this;
    }
};''', r'''    int x, y, z;
    cin >> x >> y >> z;
    Kotak a('a', x);
    Kotak b('b', y);
    Kotak c('c', z);
    a = b = c;
    cout << a.isi << " " << b.isi << " " << c.isi << endl;'''), wajib=WS,
     id=("Penugasan Berantai", "Untuk bilangan, `a = b = c;` dikerjakan **dari kanan**: mula-mula `b = c`, lalu hasilnya ditugaskan ke `a`. Agar rantai seperti itu juga berlaku untuk objek, operator penugasan harus mengembalikan `*this` sebagai referensi.\n\n"
         "Buat class `Kotak` dengan atribut `char nama` dan `int isi`, konstruktor `Kotak(char n, int i)`, dan operator penugasan yang:\n\n- mencetak `<nama objek ini> <- <nama objek asal> (<isi objek asal>)`;\n- menyalin **hanya `isi`** — nama kotak tidak ikut berubah;\n- mengembalikan `*this`.\n\n"
         "Baca tiga bilangan. Buat kotak `a`, `b`, `c` berisi ketiganya, jalankan `a = b = c;`, lalu cetak isi a, b, c.\n\n"
         "Untuk `1 2 3`:\n\n```\nb <- c (3)\na <- b (3)\n3 3 3\n```" + S_ID,
         "Satu baris berisi tiga bilangan bulat yang berbeda.", "Dua baris dari operator penugasan, lalu isi ketiga kotak.", "Tiap bilangan antara 1 dan 99."),
     en=("Chained Assignment", "For numbers, `a = b = c;` is carried out **from the right**: first `b = c`, then the result is assigned to `a`. For such a chain to work with objects too, the assignment operator must return `*this` as a reference.\n\n"
         "Make a class `Kotak` (box) with the attributes `char nama` and `int isi` (content), a constructor `Kotak(char n, int i)`, and an assignment operator that:\n\n- prints `<name of this object> <- <name of the source> (<content of the source>)`;\n- copies **only `isi`** — the box's name does not change;\n- returns `*this`.\n\n"
         "Read three numbers. Create the boxes `a`, `b`, `c` holding them, run `a = b = c;`, then print the contents of a, b, c.\n\n"
         "For `1 2 3`:\n\n```\nb <- c (3)\na <- b (3)\n3 3 3\n```" + S_EN,
         "One line with three different integers.", "Two lines from the assignment operator, then the contents of the three boxes.", "Each number is between 1 and 99."),
     ar=("الإسناد المتسلسل", "في الأعداد يُنفَّذ `a = b = c;` **من اليمين**: أولًا `b = c` ثم يُسند الناتج إلى `a`. ليصح تسلسل كهذا مع الكائنات أيضًا يجب أن يُرجع عامل الإسناد `*this` مرجعًا.\n\n"
         "أنشئ صنفًا `Kotak` (علبة) خاصيتاه `char nama` و `int isi` (المحتوى)، وبانٍ `Kotak(char n, int i)`، وعامل إسناد:\n\n- يطبع `<اسم هذا الكائن> <- <اسم المصدر> (<محتوى المصدر>)`؛\n- ينسخ **`isi` فقط** — اسم العلبة لا يتغير؛\n- يُرجع `*this`.\n\n"
         "اقرأ ثلاثة أعداد. أنشئ العلب `a` و `b` و `c` بها ونفِّذ `a = b = c;` ثم اطبع محتويات a و b و c.\n\n"
         "عند `1 2 3`:\n\n```\nb <- c (3)\na <- b (3)\n3 3 3\n```" + S_AR,
         "سطر واحد فيه ثلاثة أعداد صحيحة مختلفة.", "سطران من عامل الإسناد ثم محتويات العلب الثلاث.", "كل عدد بين 1 و 99."))

soal("kunci-otomatis", "mudah_sedang", "trimmed",
     "KATA = ['gudang', 'arsip', 'brankas', 'lemari', 'gerbang', 'server', 'laci', 'ruang']\nn = r.randint(1, 10)\nv = [r.randint(0, 99) for _ in range(n)]\nif i % 2 == 1:\n    v[r.randint(0, n - 1)] = -r.randint(1, 9)\nprint(r.choice(KATA), n)\nprint(*v)\n",
     program(r'''
class Kunci {
public:
    string nama;

    Kunci(string n) : nama(n) {
        cout << "Kunci " << nama << " diambil" << endl;
    }

    ~Kunci() {
        cout << "Kunci " << nama << " dilepas" << endl;
    }
};

int proses(string nama, int n) {
    Kunci kunci(nama);
    auto jumlah = 0;
    for (int k = 0; k < n; k++) {
        int x;
        cin >> x;
        if (x < 0) {
            cout << "Data rusak" << endl;
            return -1;
        }
        cout << "Baca " << x << endl;
        jumlah += x;
    }
    return jumlah;
}''', r'''    string nama;
    int n;
    cin >> nama >> n;
    auto hasil = proses(nama, n);
    if (hasil >= 0) {
        cout << "Jumlah: " << hasil << endl;
    } else {
        cout << "Gagal" << endl;
    }''', "string"), wajib=W,
     id=("Kunci yang Selalu Dilepas", "Destruktor berjalan saat objek berakhir, **lewat jalan mana pun fungsi itu keluar** — juga lewat `return` di tengah. Karena itu pekerjaan \"lepaskan kembali\" paling aman dititipkan pada destruktor.\n\n"
         "Buat class `Kunci` dengan atribut `string nama`; konstruktornya mencetak `Kunci <nama> diambil`, destruktornya mencetak `Kunci <nama> dilepas`.\n\n"
         "Tulis fungsi `int proses(string nama, int n)` yang:\n\n1. membuat objek `Kunci`;\n2. membaca n bilangan satu per satu. Untuk bilangan tak negatif, cetak `Baca <x>` dan jumlahkan. Bila bertemu bilangan negatif, cetak `Data rusak` dan **langsung** `return -1`;\n3. mengembalikan jumlahnya.\n\n"
         "Di `main`, baca nama dan n, panggil `proses`, lalu cetak `Jumlah: s` atau, bila hasilnya −1, `Gagal`.\n\n"
         "Untuk `gudang 3` dan data `4 -1 7`:\n\n```\nKunci gudang diambil\nBaca 4\nData rusak\nKunci gudang dilepas\nGagal\n```" + W_ID,
         "Baris pertama: nama (satu kata) dan n. Baris kedua: n bilangan bulat.", "Baris-baris seperti contoh.", "1 ≤ n ≤ 10; tiap bilangan antara −9 dan 99"),
     en=("A Lock that is Always Released", "A destructor runs when the object ends, **whichever way the function leaves** — also through a `return` in the middle. That is why \"give it back\" work is safest in a destructor.\n\n"
         "Make a class `Kunci` (lock) with an attribute `string nama`; its constructor prints `Kunci <nama> diambil` (taken), its destructor prints `Kunci <nama> dilepas` (released).\n\n"
         "Write a function `int proses(string nama, int n)` that:\n\n1. creates a `Kunci` object;\n2. reads n numbers one at a time. For a non-negative number, print `Baca <x>` (read) and add it. On a negative number, print `Data rusak` (bad data) and `return -1` **at once**;\n3. returns the sum.\n\n"
         "In `main`, read the name and n, call `proses`, then print `Jumlah: s` (sum) or, when the result is −1, `Gagal` (failed).\n\n"
         "For `gudang 3` and the data `4 -1 7`:\n\n```\nKunci gudang diambil\nBaca 4\nData rusak\nKunci gudang dilepas\nGagal\n```" + W_EN,
         "First line: the name (one word) and n. Second line: n integers.", "Lines as in the example.", "1 ≤ n ≤ 10; each number is between −9 and 99"),
     ar=("قفل يُحرَّر دائمًا", "يعمل الهادم عند انتهاء الكائن **أيًّا كان طريق خروج الدالة** — حتى عبر `return` في وسطها. لذلك كان الهادم أأمن مكان لعمل «الإعادة».\n\n"
         "أنشئ صنفًا `Kunci` (قفل) فيه خاصية `string nama`؛ بانيه يطبع `Kunci <nama> diambil` (أُخذ) وهادمه يطبع `Kunci <nama> dilepas` (حُرِّر).\n\n"
         "اكتب دالة `int proses(string nama, int n)`:\n\n1. تنشئ كائن `Kunci`؛\n2. تقرأ n عددًا واحدًا واحدًا. للعدد غير السالب اطبع `Baca <x>` (قُرئ) واجمعه. وعند عدد سالب اطبع `Data rusak` (بيانات تالفة) ونفِّذ `return -1` **فورًا**؛\n3. تُرجع المجموع.\n\n"
         "في `main` اقرأ الاسم و n واستدعِ `proses` ثم اطبع `Jumlah: s` (المجموع) أو، إذا كان الناتج −1، `Gagal` (فشل).\n\n"
         "عند `gudang 3` والبيانات `4 -1 7`:\n\n```\nKunci gudang diambil\nBaca 4\nData rusak\nKunci gudang dilepas\nGagal\n```" + W_AR,
         "السطر الأول: الاسم (كلمة واحدة) و n. السطر الثاني: n عددًا صحيحًا.", "أسطر كما في المثال.", "1 ≤ n ≤ 10؛ كل عدد بين −9 و 99"))

soal("objek-sementara", "mudah_sedang", "trimmed", BENDA_KATA + "print(*r.sample(KATA, 3))\n",
     program(BENDA, r'''    string a, b, c;
    cin >> a >> b >> c;
    Benda tetap(a);
    Benda{b};
    cout << "Setelah objek sementara" << endl;
    Benda akhir(c);''', "string"), wajib=W,
     id=("Objek Sementara", "Objek boleh dibuat **tanpa nama**: `Benda{b};`. Objek sementara seperti itu hidup hanya sampai akhir pernyataan tempat ia dibuat — destruktornya berjalan di titik koma itu juga.\n\n"
         "Buat class `Benda` (atribut `string nama`; konstruktor mencetak `Buat <nama>`, destruktor mencetak `Hapus <nama>`). Baca tiga nama, lalu:\n\n"
         "1. buat objek bernama dari nama pertama;\n2. buat objek **sementara** dari nama kedua, dengan kurung kurawal: `Benda{b};`;\n3. cetak `Setelah objek sementara`;\n4. buat objek bernama dari nama ketiga.\n\n"
         "Untuk `meja kursi lampu`:\n\n```\nBuat meja\nBuat kursi\nHapus kursi\nSetelah objek sementara\nBuat lampu\nHapus lampu\nHapus meja\n```\n\n(Catatan: `Benda(b);` dengan kurung biasa dibaca kompilator sebagai deklarasi variabel bernama `b`, bukan objek sementara.)" + W_ID,
         "Satu baris berisi tiga nama yang berbeda.", "Tujuh baris seperti contoh.", "Nama terdiri dari huruf kecil, paling panjang 20 huruf."),
     en=("Temporary Objects", "An object may be created **without a name**: `Benda{b};`. Such a temporary lives only until the end of the statement in which it is created — its destructor runs at that very semicolon.\n\n"
         "Make a class `Benda` (attribute `string nama`; the constructor prints `Buat <nama>`, the destructor prints `Hapus <nama>`). Read three names, then:\n\n"
         "1. create a named object from the first name;\n2. create a **temporary** object from the second name, with braces: `Benda{b};`;\n3. print `Setelah objek sementara` (after the temporary);\n4. create a named object from the third name.\n\n"
         "For `meja kursi lampu`:\n\n```\nBuat meja\nBuat kursi\nHapus kursi\nSetelah objek sementara\nBuat lampu\nHapus lampu\nHapus meja\n```\n\n(Note: `Benda(b);` with round brackets is read by the compiler as the declaration of a variable named `b`, not as a temporary.)" + W_EN,
         "One line with three different names.", "Seven lines as in the example.", "Names have lowercase letters, at most 20."),
     ar=("الكائنات المؤقتة", "يجوز إنشاء كائن **بلا اسم**: `Benda{b};`. كائن مؤقت كهذا يعيش حتى نهاية التعليمة التي أُنشئ فيها فقط — يعمل هادمه عند الفاصلة المنقوطة نفسها.\n\n"
         "أنشئ صنفًا `Benda` (الخاصية `string nama`؛ الباني يطبع `Buat <nama>` والهادم يطبع `Hapus <nama>`). اقرأ ثلاثة أسماء ثم:\n\n"
         "1. أنشئ كائنًا مسمًّى من الاسم الأول؛\n2. أنشئ كائنًا **مؤقتًا** من الاسم الثاني بأقواس معقوفة: `Benda{b};`؛\n3. اطبع `Setelah objek sementara` (بعد الكائن المؤقت)؛\n4. أنشئ كائنًا مسمًّى من الاسم الثالث.\n\n"
         "عند `meja kursi lampu`:\n\n```\nBuat meja\nBuat kursi\nHapus kursi\nSetelah objek sementara\nBuat lampu\nHapus lampu\nHapus meja\n```\n\n(ملاحظة: `Benda(b);` بأقواس عادية يقرؤها المترجم تصريحًا بمتغير اسمه `b` لا كائنًا مؤقتًا.)" + W_AR,
         "سطر واحد فيه ثلاثة أسماء مختلفة.", "سبعة أسطر كما في المثال.", "الأسماء حروف لاتينية صغيرة، 20 حرفًا على الأكثر."))

soal("daftar-tumbuh", "mudah_sedang", "trimmed", "n = {2: 1, 3: 2, 4: 33}.get(i, r.randint(3, 40))\nprint(n)\nprint(*[r.randint(0, 99) for _ in range(n)])\n",
     program(r'''
class DaftarDinamis {
public:
    int *data;
    int banyak;
    int kapasitas;

    DaftarDinamis() : data(new int[1]), banyak(0), kapasitas(1) {
    }

    ~DaftarDinamis() {
        cout << "Bebas " << kapasitas << endl;
        delete[] data;
    }

    void tambah(int x) {
        if (banyak == kapasitas) {
            auto baru = new int[kapasitas * 2];
            for (int k = 0; k < banyak; k++) {
                baru[k] = data[k];
            }
            delete[] data;
            data = baru;
            cout << "Tumbuh " << kapasitas << " -> " << kapasitas * 2 << endl;
            kapasitas *= 2;
        }
        data[banyak] = x;
        banyak++;
    }

    void cetak() {
        cout << "Isi:";
        for (int k = 0; k < banyak; k++) {
            cout << " " << data[k];
        }
        cout << endl;
    }
};''', r'''    int n;
    cin >> n;
    DaftarDinamis daftar;
    for (int k = 0; k < n; k++) {
        int x;
        cin >> x;
        daftar.tambah(x);
    }
    daftar.cetak();'''), wajib=W,
     id=("Daftar yang Tumbuh", "Begitulah `std::vector` bekerja di balik layar. Buat class `DaftarDinamis` dengan atribut `int *data`, `int banyak`, `int kapasitas`:\n\n"
         "- konstruktor bawaan memesan array berkapasitas 1;\n- `void tambah(int x)` — bila array penuh, pesan array baru **dua kali lebih besar**, salin isinya, bebaskan yang lama, dan cetak `Tumbuh <lama> -> <baru>`; lalu simpan x;\n"
         "- `void cetak()` — mencetak `Isi:` diikuti semua elemen;\n- destruktor mencetak `Bebas <kapasitas>` dan membebaskan array.\n\n"
         "Baca n bilangan, tambahkan satu per satu, lalu panggil `cetak()`. Baris `Bebas` muncul paling akhir, saat objek dihancurkan.\n\n"
         "Untuk `5 6 7`:\n\n```\nTumbuh 1 -> 2\nTumbuh 2 -> 4\nIsi: 5 6 7\nBebas 4\n```" + W_ID,
         "Baris pertama: n. Baris kedua: n bilangan bulat.", "Baris `Tumbuh` untuk tiap pembesaran, lalu `Isi: …`, lalu `Bebas k`.", "1 ≤ n ≤ 40; tiap bilangan antara 0 dan 99"),
     en=("A Growing List", "This is how `std::vector` works behind the scenes. Make a class `DaftarDinamis` (dynamic list) with the attributes `int *data`, `int banyak` (count), `int kapasitas` (capacity):\n\n"
         "- the default constructor requests an array of capacity 1;\n- `void tambah(int x)` (add) — if the array is full, request a new array **twice as large**, copy the contents, free the old one, and print `Tumbuh <old> -> <new>` (grow); then store x;\n"
         "- `void cetak()` — prints `Isi:` followed by all elements;\n- the destructor prints `Bebas <capacity>` (freed) and frees the array.\n\n"
         "Read n numbers, add them one at a time, then call `cetak()`. The `Bebas` line comes last, when the object is destroyed.\n\n"
         "For `5 6 7`:\n\n```\nTumbuh 1 -> 2\nTumbuh 2 -> 4\nIsi: 5 6 7\nBebas 4\n```" + W_EN,
         "First line: n. Second line: n integers.", "A `Tumbuh` line for every growth, then `Isi: …`, then `Bebas k`.", "1 ≤ n ≤ 40; each number is between 0 and 99"),
     ar=("قائمة تنمو", "هكذا يعمل `std::vector` خلف الستار. أنشئ صنفًا `DaftarDinamis` (قائمة ديناميكية) خصائصه `int *data` و `int banyak` (العدد) و `int kapasitas` (السعة):\n\n"
         "- الباني الافتراضي يطلب مصفوفة سعتها 1؛\n- `void tambah(int x)` (أضف) — إذا امتلأت المصفوفة فاطلب مصفوفة جديدة **بضعف الحجم** وانسخ المحتوى وحرِّر القديمة واطبع `Tumbuh <القديمة> -> <الجديدة>` (نمو)؛ ثم خزِّن x؛\n"
         "- `void cetak()` — تطبع `Isi:` تليها كل العناصر؛\n- الهادم يطبع `Bebas <السعة>` (حُرِّر) ويحرر المصفوفة.\n\n"
         "اقرأ n عددًا وأضفها واحدًا واحدًا ثم استدعِ `cetak()`. سطر `Bebas` يأتي أخيرًا عند هدم الكائن.\n\n"
         "عند `5 6 7`:\n\n```\nTumbuh 1 -> 2\nTumbuh 2 -> 4\nIsi: 5 6 7\nBebas 4\n```" + W_AR,
         "السطر الأول: n. السطر الثاني: n عددًا صحيحًا.", "سطر `Tumbuh` لكل تكبير ثم `Isi: …` ثم `Bebas k`.", "1 ≤ n ≤ 40؛ كل عدد بين 0 و 99"))

# ================================ SEDANG (6) ================================

soal("matriks-transpos", "sedang", "trimmed", "b, k = r.randint(1, 6), r.randint(1, 6)\nif i == 2:\n    b, k = 1, 1\nif i == 3:\n    b, k = 1, 6\nprint(b, k)\nfor _ in range(b):\n    print(*[r.randint(0, 99) for _ in range(k)])\n",
     program(r'''
class Matriks {
public:
    int **sel;
    int baris;
    int kolom;

    Matriks(int b, int k) : sel(new int *[b]), baris(b), kolom(k) {
        for (int i = 0; i < baris; i++) {
            sel[i] = new int[kolom];
            for (int j = 0; j < kolom; j++) {
                sel[i][j] = 0;
            }
        }
    }

    Matriks(const Matriks &lain) : sel(new int *[lain.baris]), baris(lain.baris), kolom(lain.kolom) {
        for (int i = 0; i < baris; i++) {
            sel[i] = new int[kolom];
            for (int j = 0; j < kolom; j++) {
                sel[i][j] = lain.sel[i][j];
            }
        }
    }

    Matriks &operator=(const Matriks &lain) {
        if (this != &lain) {
            bebas();
            baris = lain.baris;
            kolom = lain.kolom;
            sel = new int *[baris];
            for (int i = 0; i < baris; i++) {
                sel[i] = new int[kolom];
                for (int j = 0; j < kolom; j++) {
                    sel[i][j] = lain.sel[i][j];
                }
            }
        }
        return *this;
    }

    ~Matriks() {
        bebas();
    }

    void bebas() {
        for (int i = 0; i < baris; i++) {
            delete[] sel[i];
        }
        delete[] sel;
    }

    Matriks transpos() const {
        Matriks t(kolom, baris);
        for (int i = 0; i < baris; i++) {
            for (int j = 0; j < kolom; j++) {
                t.sel[j][i] = sel[i][j];
            }
        }
        return t;
    }

    void cetak() const {
        for (int i = 0; i < baris; i++) {
            for (int j = 0; j < kolom; j++) {
                cout << sel[i][j] << " ";
            }
            cout << endl;
        }
    }
};''', r'''    int b, k;
    cin >> b >> k;
    Matriks m(b, k);
    for (int i = 0; i < b; i++) {
        for (int j = 0; j < k; j++) {
            cin >> m.sel[i][j];
        }
    }
    Matriks cadangan = m;
    m = m.transpos();
    m.cetak();
    cadangan.sel[0][0] += 1000;
    cout << "Sudut: " << m.sel[0][0] << " " << cadangan.sel[0][0] << endl;'''), wajib=WS,
     id=("Transpos Matriks", "Buat class `Matriks` yang mengelola matriks berukuran sembarang di memori dinamis: atribut `int **sel`, `int baris`, `int kolom`. Class ini mengelola memori, jadi berlaku aturan tiga:\n\n"
         "- konstruktor `Matriks(int b, int k)` — memesan b baris berisi k kolom, semua bernilai 0;\n- konstruktor salin dan operator penugasan yang menyalin dalam;\n- destruktor yang membebaskan **setiap baris** lalu array penunjuknya;\n"
         "- `Matriks transpos() const` — mengembalikan matriks baru berukuran k × b, dengan baris dan kolom ditukar;\n- `void cetak() const`.\n\n"
         "Baca sebuah matriks `m`. Lalu jalankan:\n\n```\nMatriks cadangan = m;\nm = m.transpos();\nm.cetak();\ncadangan.sel[0][0] += 1000;\n```\n\ndan cetak `Sudut: <m.sel[0][0]> <cadangan.sel[0][0]>`.\n\n"
         "Untuk matriks 2 × 3 `1 2 3` / `4 5 6`:\n\n```\n1 4\n2 5\n3 6\nSudut: 1 1001\n```" + S_ID,
         "Baris pertama: b dan k. Lalu b baris berisi k bilangan.", "k baris transpos, lalu satu baris `Sudut: …`.", "1 ≤ b, k ≤ 6; tiap bilangan antara 0 dan 99"),
     en=("Matrix Transpose", "Make a class `Matriks` that manages a matrix of any size in dynamic memory: the attributes `int **sel` (cells), `int baris` (rows), `int kolom` (columns). The class manages memory, so the rule of three applies:\n\n"
         "- a constructor `Matriks(int b, int k)` — requests b rows of k columns, all zero;\n- a copy constructor and an assignment operator that copy deeply;\n- a destructor that frees **every row** and then the array of pointers;\n"
         "- `Matriks transpos() const` — returns a new k × b matrix with rows and columns swapped;\n- `void cetak() const`.\n\n"
         "Read a matrix `m`. Then run:\n\n```\nMatriks cadangan = m;\nm = m.transpos();\nm.cetak();\ncadangan.sel[0][0] += 1000;\n```\n\nand print `Sudut: <m.sel[0][0]> <cadangan.sel[0][0]>` (corner).\n\n"
         "For the 2 × 3 matrix `1 2 3` / `4 5 6`:\n\n```\n1 4\n2 5\n3 6\nSudut: 1 1001\n```" + S_EN,
         "First line: b and k. Then b lines of k numbers.", "The k lines of the transpose, then one line `Sudut: …`.", "1 ≤ b, k ≤ 6; each number is between 0 and 99"),
     ar=("منقول المصفوفة", "أنشئ صنفًا `Matriks` يدير مصفوفة بأي حجم في الذاكرة الديناميكية: الخصائص `int **sel` (الخلايا) و `int baris` (الصفوف) و `int kolom` (الأعمدة). الصنف يدير ذاكرة فتنطبق قاعدة الثلاثة:\n\n"
         "- بانٍ `Matriks(int b, int k)` — يطلب b صفًّا في كل منها k عمودًا، كلها أصفار؛\n- باني نسخ وعامل إسناد ينسخان نسخًا عميقًا؛\n- هادم يحرر **كل صف** ثم مصفوفة المؤشرات؛\n"
         "- `Matriks transpos() const` — تُرجع مصفوفة جديدة k × b بعد تبديل الصفوف والأعمدة؛\n- `void cetak() const`.\n\n"
         "اقرأ مصفوفة `m`. ثم نفِّذ:\n\n```\nMatriks cadangan = m;\nm = m.transpos();\nm.cetak();\ncadangan.sel[0][0] += 1000;\n```\n\nواطبع `Sudut: <m.sel[0][0]> <cadangan.sel[0][0]>` (الزاوية).\n\n"
         "للمصفوفة 2 × 3 `1 2 3` / `4 5 6`:\n\n```\n1 4\n2 5\n3 6\nSudut: 1 1001\n```" + S_AR,
         "السطر الأول: b و k. ثم b سطرًا في كل منها k عددًا.", "k سطرًا للمنقول ثم سطر `Sudut: …`.", "1 ≤ b, k ≤ 6؛ كل عدد بين 0 و 99"))

soal("pelacak-bocor", "sedang", "trimmed",
     BENDA_KATA + "hidup, sisa = [], r.sample(KATA, 10)\nfor _ in range(r.randint(1, 20)):\n    q = r.random()\n    if q < 0.55 and sisa:\n        n = sisa.pop()\n        hidup.append(n)\n        print('buat', n)\n    elif q < 0.9 and hidup:\n        n = hidup.pop(r.randint(0, len(hidup) - 1))\n        print('hapus', n)\n    else:\n        print('hapus', r.choice(KATA[10:]))\nif i == 2:\n    for n in hidup:\n        print('hapus', n)\nprint('selesai')\n",
     program(r'''
class Sumber {
public:
    static int hidup;
    string nama;

    Sumber(string n) : nama(n) {
        hidup++;
        cout << "+" << nama << endl;
    }

    ~Sumber() {
        hidup--;
        cout << "-" << nama << endl;
    }
};

int Sumber::hidup = 0;''', r'''    Sumber *daftar[20];
    auto banyak = 0;
    string perintah, nama;
    while (cin >> perintah) {
        if (perintah == "selesai") {
            break;
        }
        cin >> nama;
        if (perintah == "buat") {
            daftar[banyak] = new Sumber(nama);
            banyak++;
        } else {
            auto ketemu = false;
            for (int k = 0; k < banyak; k++) {
                if (daftar[k] != nullptr && daftar[k]->nama == nama) {
                    delete daftar[k];
                    daftar[k] = nullptr;
                    ketemu = true;
                }
            }
            if (!ketemu) {
                cout << "Tidak ada " << nama << endl;
            }
        }
    }
    cout << "Bocor: " << Sumber::hidup << endl;
    for (int k = 0; k < banyak; k++) {
        if (daftar[k] != nullptr) {
            delete daftar[k];
            daftar[k] = nullptr;
        }
    }
    cout << "Hidup: " << Sumber::hidup << endl;''', "string"), wajib=W,
     id=("Pelacak Kebocoran", "Objek yang dibuat dengan `new` tetapi tidak pernah di-`delete` disebut **bocor**. Penghitung di konstruktor dan destruktor adalah cara sederhana untuk menemukannya.\n\n"
         "Buat class `Sumber` dengan `static int hidup` dan `string nama`. Konstruktornya menaikkan `hidup` dan mencetak `+<nama>`; destruktornya menurunkan `hidup` dan mencetak `-<nama>`.\n\n"
         "Perintah datang satu per baris:\n\n- `buat <nama>` — buat objek baru dengan `new`;\n- `hapus <nama>` — hancurkan objek bernama itu; bila tidak ada objek hidup dengan nama itu, cetak `Tidak ada <nama>`;\n- `selesai`.\n\n"
         "Setelah `selesai`: cetak `Bocor: k` (banyak objek yang masih hidup), hancurkan semua yang tersisa **menurut urutan pembuatannya**, lalu cetak `Hidup: 0`.\n\n"
         "Contoh: `buat meja`, `buat kursi`, `hapus lampu`, `hapus meja`, `buat pena`, `selesai` →\n\n```\n+meja\n+kursi\nTidak ada lampu\n-meja\n+pena\nBocor: 2\n-kursi\n-pena\nHidup: 0\n```" + W_ID,
         "Beberapa baris perintah, diakhiri `selesai`.", "Satu baris per kejadian, seperti contoh.", "Paling banyak 20 perintah; nama objek yang hidup pada saat yang sama selalu berbeda, dan sebuah nama tidak pernah dipakai dua kali."),
     en=("Leak Tracker", "An object created with `new` but never `delete`d is said to **leak**. A counter in the constructor and the destructor is a simple way to find leaks.\n\n"
         "Make a class `Sumber` (resource) with `static int hidup` (alive) and `string nama`. Its constructor raises `hidup` and prints `+<nama>`; its destructor lowers `hidup` and prints `-<nama>`.\n\n"
         "Commands come one per line:\n\n- `buat <name>` — create a new object with `new`;\n- `hapus <name>` — destroy the object of that name; if no live object has that name, print `Tidak ada <name>` (no such);\n- `selesai`.\n\n"
         "After `selesai`: print `Bocor: k` (the number of objects still alive), destroy all that remain **in the order of their creation**, then print `Hidup: 0`.\n\n"
         "Example: `buat meja`, `buat kursi`, `hapus lampu`, `hapus meja`, `buat pena`, `selesai` →\n\n```\n+meja\n+kursi\nTidak ada lampu\n-meja\n+pena\nBocor: 2\n-kursi\n-pena\nHidup: 0\n```" + W_EN,
         "Several command lines, ending with `selesai`.", "One line per event, as in the example.", "At most 20 commands; objects alive at the same time always have different names, and a name is never used twice."),
     ar=("متتبع التسرب", "الكائن المنشأ بـ `new` ولم يُنفَّذ عليه `delete` قط يقال إنه **تسرّب**. عدّاد في الباني والهادم طريقة بسيطة لاكتشاف التسرب.\n\n"
         "أنشئ صنفًا `Sumber` (مورد) فيه `static int hidup` (الأحياء) و `string nama`. بانيه يزيد `hidup` ويطبع `+<nama>`؛ وهادمه ينقص `hidup` ويطبع `-<nama>`.\n\n"
         "تأتي الأوامر أمرًا في كل سطر:\n\n- `buat <الاسم>` — أنشئ كائنًا جديدًا بـ `new`؛\n- `hapus <الاسم>` — اهدم الكائن الذي له هذا الاسم؛ وإذا لم يوجد كائن حي بهذا الاسم فاطبع `Tidak ada <الاسم>` (لا يوجد)؛\n- `selesai`.\n\n"
         "بعد `selesai`: اطبع `Bocor: k` (عدد الكائنات التي ما زالت حية) واهدم كل ما بقي **بترتيب إنشائه** ثم اطبع `Hidup: 0`.\n\n"
         "مثال: `buat meja` ثم `buat kursi` ثم `hapus lampu` ثم `hapus meja` ثم `buat pena` ثم `selesai` ←\n\n```\n+meja\n+kursi\nTidak ada lampu\n-meja\n+pena\nBocor: 2\n-kursi\n-pena\nHidup: 0\n```" + W_AR,
         "عدة أسطر أوامر تنتهي بـ `selesai`.", "سطر لكل حدث كما في المثال.", "20 أمرًا على الأكثر؛ الكائنات الحية في الوقت نفسه أسماؤها مختلفة دائمًا، ولا يُستعمل الاسم مرتين."))

soal("tumpukan-cadangan", "sedang", "trimmed",
     "for _ in range(r.randint(1, 30)):\n    q = r.random()\n    if q < 0.45:\n        print('dorong', r.randint(0, 99))\n    elif q < 0.65:\n        print('ambil')\n    elif q < 0.85:\n        print('simpan')\n    else:\n        print('pulihkan')\nprint('selesai')\n",
     program(r'''
class Tumpukan {
public:
    int *data;
    int banyak;

    Tumpukan() : data(new int[30]), banyak(0) {
    }

    Tumpukan(const Tumpukan &lain) : data(new int[30]), banyak(lain.banyak) {
        for (int k = 0; k < banyak; k++) {
            data[k] = lain.data[k];
        }
    }

    Tumpukan &operator=(const Tumpukan &lain) {
        if (this != &lain) {
            banyak = lain.banyak;
            for (int k = 0; k < banyak; k++) {
                data[k] = lain.data[k];
            }
        }
        return *this;
    }

    ~Tumpukan() {
        delete[] data;
    }
};''', r'''    Tumpukan utama;
    Tumpukan cadangan;
    string perintah;
    while (cin >> perintah) {
        if (perintah == "selesai") {
            break;
        }
        if (perintah == "dorong") {
            cin >> utama.data[utama.banyak];
            utama.banyak++;
        } else if (perintah == "ambil") {
            if (utama.banyak == 0) {
                cout << "kosong" << endl;
            } else {
                utama.banyak--;
                cout << utama.data[utama.banyak] << endl;
            }
        } else if (perintah == "simpan") {
            cadangan = utama;
        } else {
            utama = cadangan;
        }
    }
    cout << "Isi:";
    for (int k = 0; k < utama.banyak; k++) {
        cout << " " << utama.data[k];
    }
    cout << endl;''', "string"), wajib=WS,
     id=("Tumpukan dengan Cadangan", "Buat class `Tumpukan` yang menyimpan isinya di memori dinamis (`int *data`, `int banyak`), dengan konstruktor bawaan, konstruktor salin, operator penugasan, dan destruktor.\n\n"
         "Program memakai **dua** objek: tumpukan `utama` dan tumpukan `cadangan` (keduanya mula-mula kosong). Perintah datang satu per baris:\n\n"
         "- `dorong x` — menaruh x di puncak `utama`;\n- `ambil` — mengambil puncak `utama` dan mencetaknya, atau mencetak `kosong`;\n- `simpan` — `cadangan = utama;`\n- `pulihkan` — `utama = cadangan;`\n- `selesai`.\n\n"
         "Di akhir cetak `Isi:` diikuti isi `utama` dari dasar ke puncak.\n\n"
         "Karena penugasannya menyalin dalam, perubahan pada `utama` setelah `simpan` tidak mengusik `cadangan`.\n\n"
         "Contoh: `dorong 1`, `dorong 2`, `simpan`, `ambil`, `dorong 9`, `pulihkan`, `dorong 3`, `selesai` →\n\n```\n2\nIsi: 1 2 3\n```" + S_ID,
         "Beberapa baris perintah, diakhiri `selesai`.", "Satu baris untuk tiap `ambil`, lalu `Isi: …`.", "Paling banyak 30 perintah; 0 ≤ x ≤ 99"),
     en=("A Stack with a Backup", "Make a class `Tumpukan` (stack) that keeps its contents in dynamic memory (`int *data`, `int banyak`), with a default constructor, a copy constructor, an assignment operator and a destructor.\n\n"
         "The program uses **two** objects: the stack `utama` (main) and the stack `cadangan` (backup), both empty at first. Commands come one per line:\n\n"
         "- `dorong x` — puts x on top of `utama`;\n- `ambil` — takes the top of `utama` and prints it, or prints `kosong`;\n- `simpan` (save) — `cadangan = utama;`\n- `pulihkan` (restore) — `utama = cadangan;`\n- `selesai`.\n\n"
         "At the end print `Isi:` followed by the contents of `utama` from bottom to top.\n\n"
         "Because the assignment copies deeply, changes to `utama` after `simpan` do not disturb `cadangan`.\n\n"
         "Example: `dorong 1`, `dorong 2`, `simpan`, `ambil`, `dorong 9`, `pulihkan`, `dorong 3`, `selesai` →\n\n```\n2\nIsi: 1 2 3\n```" + S_EN,
         "Several command lines, ending with `selesai`.", "One line for every `ambil`, then `Isi: …`.", "At most 30 commands; 0 ≤ x ≤ 99"),
     ar=("مكدّس بنسخة احتياطية", "أنشئ صنفًا `Tumpukan` (مكدّس) يحفظ محتواه في ذاكرة ديناميكية (`int *data` و `int banyak`)، له بانٍ افتراضي وباني نسخ وعامل إسناد وهادم.\n\n"
         "يستعمل البرنامج **كائنين**: المكدّس `utama` (الرئيسي) والمكدّس `cadangan` (الاحتياطي)، وكلاهما فارغ في البداية. تأتي الأوامر أمرًا في كل سطر:\n\n"
         "- `dorong x` — تضع x في قمة `utama`؛\n- `ambil` — تأخذ قمة `utama` وتطبعها، أو تطبع `kosong`؛\n- `simpan` (احفظ) — `cadangan = utama;`\n- `pulihkan` (استعد) — `utama = cadangan;`\n- `selesai`.\n\n"
         "في النهاية اطبع `Isi:` يليها محتوى `utama` من القاعدة إلى القمة.\n\n"
         "لأن الإسناد ينسخ نسخًا عميقًا فالتغييرات على `utama` بعد `simpan` لا تمسّ `cadangan`.\n\n"
         "مثال: `dorong 1` ثم `dorong 2` ثم `simpan` ثم `ambil` ثم `dorong 9` ثم `pulihkan` ثم `dorong 3` ثم `selesai` ←\n\n```\n2\nIsi: 1 2 3\n```" + S_AR,
         "عدة أسطر أوامر تنتهي بـ `selesai`.", "سطر لكل `ambil` ثم `Isi: …`.", "30 أمرًا على الأكثر؛ 0 ≤ x ≤ 99"))

soal("objek-statis", "sedang", "trimmed", "print({2: 0, 3: 1}.get(i, r.randint(2, 3) if i % 2 == 0 else r.randint(4, 5)))\n",
     program(r'''
class Catat {
public:
    string nama;

    Catat(string n) : nama(n) {
        cout << "Buat " << nama << endl;
    }

    ~Catat() {
        cout << "Hapus " << nama << endl;
    }
};

Catat global("global");

void layani(int k) {
    static Catat sekali("statis");
    Catat lokal("lokal");
    cout << "Panggilan " << k << endl;
}''', r'''    int n;
    cin >> n;
    cout << "main mulai" << endl;
    for (int k = 1; k <= n; k++) {
        layani(k);
    }
    cout << "main selesai" << endl;''', "string"), wajib=W,
     id=("Global, Statis, Lokal", "Umur sebuah objek bergantung pada tempat ia dideklarasikan:\n\n"
         "- objek **global** dibuat sebelum `main` mulai dan dihancurkan setelah `main` selesai;\n- objek **lokal `static`** dibuat saat fungsinya dipanggil **untuk pertama kali**, dan hidup sampai program berakhir;\n- objek **lokal biasa** dibuat dan dihancurkan pada setiap pemanggilan.\n\n"
         "Di akhir program, objek statis dan global dihancurkan dalam urutan terbalik dari pembuatannya.\n\n"
         "Buat class `Catat` (atribut `string nama`; konstruktor mencetak `Buat <nama>`, destruktor mencetak `Hapus <nama>`). Lalu:\n\n"
         "- deklarasikan objek global `Catat global(\"global\");`\n- tulis fungsi `void layani(int k)` yang berisi `static Catat sekali(\"statis\");`, lalu `Catat lokal(\"lokal\");`, lalu mencetak `Panggilan <k>`;\n"
         "- di `main`: baca n, cetak `main mulai`, panggil `layani(1)` sampai `layani(n)`, cetak `main selesai`.\n\n"
         "Untuk n = 2:\n\n```\nBuat global\nmain mulai\nBuat statis\nBuat lokal\nPanggilan 1\nHapus lokal\nBuat lokal\nPanggilan 2\nHapus lokal\nmain selesai\nHapus statis\nHapus global\n```\n\nUntuk n = 0 objek statis tidak pernah dibuat, sehingga tidak pernah dihancurkan." + W_ID,
         "Satu bilangan bulat n.", "Baris-baris seperti contoh.", "0 ≤ n ≤ 5"),
     en=("Global, Static, Local", "The lifetime of an object depends on where it is declared:\n\n"
         "- a **global** object is created before `main` starts and destroyed after `main` ends;\n- a **`static` local** object is created when its function is called **for the first time**, and lives until the program ends;\n- an **ordinary local** object is created and destroyed on every call.\n\n"
         "At the end of the program, static and global objects are destroyed in the reverse order of their creation.\n\n"
         "Make a class `Catat` (attribute `string nama`; the constructor prints `Buat <nama>`, the destructor prints `Hapus <nama>`). Then:\n\n"
         "- declare a global object `Catat global(\"global\");`\n- write a function `void layani(int k)` that contains `static Catat sekali(\"statis\");`, then `Catat lokal(\"lokal\");`, and then prints `Panggilan <k>` (call k);\n"
         "- in `main`: read n, print `main mulai`, call `layani(1)` up to `layani(n)`, print `main selesai`.\n\n"
         "For n = 2:\n\n```\nBuat global\nmain mulai\nBuat statis\nBuat lokal\nPanggilan 1\nHapus lokal\nBuat lokal\nPanggilan 2\nHapus lokal\nmain selesai\nHapus statis\nHapus global\n```\n\nFor n = 0 the static object is never created, so it is never destroyed." + W_EN,
         "One integer n.", "Lines as in the example.", "0 ≤ n ≤ 5"),
     ar=("عام، ساكن، محلي", "عمر الكائن يتبع موضع التصريح به:\n\n"
         "- الكائن **العام** يُنشأ قبل بدء `main` ويُهدم بعد انتهائها؛\n- الكائن **المحلي `static`** يُنشأ عند استدعاء دالته **أول مرة** ويعيش حتى نهاية البرنامج؛\n- الكائن **المحلي العادي** يُنشأ ويُهدم في كل استدعاء.\n\n"
         "في نهاية البرنامج تُهدم الكائنات الساكنة والعامة بعكس ترتيب إنشائها.\n\n"
         "أنشئ صنفًا `Catat` (الخاصية `string nama`؛ الباني يطبع `Buat <nama>` والهادم يطبع `Hapus <nama>`). ثم:\n\n"
         "- صرِّح بكائن عام `Catat global(\"global\");`\n- اكتب دالة `void layani(int k)` فيها `static Catat sekali(\"statis\");` ثم `Catat lokal(\"lokal\");` ثم تطبع `Panggilan <k>` (الاستدعاء k)؛\n"
         "- في `main`: اقرأ n واطبع `main mulai` واستدعِ `layani(1)` حتى `layani(n)` واطبع `main selesai`.\n\n"
         "عند n = 2:\n\n```\nBuat global\nmain mulai\nBuat statis\nBuat lokal\nPanggilan 1\nHapus lokal\nBuat lokal\nPanggilan 2\nHapus lokal\nmain selesai\nHapus statis\nHapus global\n```\n\nعند n = 0 لا يُنشأ الكائن الساكن أبدًا فلا يُهدم أبدًا." + W_AR,
         "عدد صحيح واحد n.", "أسطر كما في المثال.", "0 ≤ n ≤ 5"))

soal("hitung-salinan-urut", "sedang", "trimmed", "n = {2: 1}.get(i, r.randint(2, 30))\nv = r.sample(range(1, 100), n)\nif i == 3:\n    v.sort()\nif i == 4:\n    v.sort(reverse=True)\nprint(n)\nprint(*v)\n",
     program(r'''
class Nilai {
public:
    static int salin;
    static int tugas;
    int angka;

    Nilai() : angka(0) {
    }

    Nilai(const Nilai &lain) : angka(lain.angka) {
        salin++;
    }

    Nilai &operator=(const Nilai &lain) {
        angka = lain.angka;
        tugas++;
        return *this;
    }
};

int Nilai::salin = 0;
int Nilai::tugas = 0;''', r'''    int n;
    cin >> n;
    Nilai data[30];
    for (int k = 0; k < n; k++) {
        cin >> data[k].angka;
    }
    for (int putaran = 0; putaran < n - 1; putaran++) {
        for (int k = 0; k < n - 1 - putaran; k++) {
            if (data[k].angka > data[k + 1].angka) {
                Nilai sementara = data[k];
                data[k] = data[k + 1];
                data[k + 1] = sementara;
            }
        }
    }
    for (int k = 0; k < n; k++) {
        cout << data[k].angka << " ";
    }
    cout << endl;
    cout << "Salin: " << Nilai::salin << endl;
    cout << "Tugas: " << Nilai::tugas << endl;'''), wajib=WS,
     id=("Harga Sebuah Pengurutan", "Menukar dua objek dengan cara biasa memerlukan **satu penyalinan dan dua penugasan**:\n\n```\nNilai sementara = data[k];   // konstruktor salin\ndata[k] = data[k + 1];       // operator penugasan\ndata[k + 1] = sementara;     // operator penugasan\n```\n\n"
         "Buat class `Nilai` dengan atribut `int angka` dan dua penghitung `static int salin`, `static int tugas`. Konstruktor bawaannya mengisi 0; konstruktor salinnya menaikkan `salin`; operator penugasannya menaikkan `tugas`.\n\n"
         "Baca n bilangan ke dalam array of `Nilai` dengan **mengisi atribut `angka` langsung** (tanpa menyalin objek). Urutkan naik dengan *bubble sort*: bandingkan elemen yang **bersebelahan**, dan tukar dengan tiga baris di atas hanya bila yang kiri lebih besar daripada yang kanan.\n\n"
         "Cetak bilangan yang sudah terurut, lalu `Salin: s` dan `Tugas: t`.\n\n"
         "Untuk `3 1 2`:\n\n```\n1 2 3\nSalin: 2\nTugas: 4\n```" + S_ID,
         "Baris pertama: n. Baris kedua: n bilangan bulat yang semuanya berbeda.", "Tiga baris: bilangan terurut, `Salin: s`, `Tugas: t`.", "1 ≤ n ≤ 30; tiap bilangan antara 1 dan 99"),
     en=("The Price of a Sort", "Swapping two objects the usual way takes **one copy and two assignments**:\n\n```\nNilai sementara = data[k];   // copy constructor\ndata[k] = data[k + 1];       // assignment operator\ndata[k + 1] = sementara;     // assignment operator\n```\n\n"
         "Make a class `Nilai` (value) with an attribute `int angka` and two counters `static int salin` (copies), `static int tugas` (assignments). Its default constructor sets 0; its copy constructor raises `salin`; its assignment operator raises `tugas`.\n\n"
         "Read n numbers into an array of `Nilai` by **setting the attribute `angka` directly** (without copying objects). Sort ascending with *bubble sort*: compare **neighbouring** elements, and swap them with the three lines above only when the left one is larger than the right one.\n\n"
         "Print the sorted numbers, then `Salin: s` and `Tugas: t`.\n\n"
         "For `3 1 2`:\n\n```\n1 2 3\nSalin: 2\nTugas: 4\n```" + S_EN,
         "First line: n. Second line: n integers, all different.", "Three lines: the sorted numbers, `Salin: s`, `Tugas: t`.", "1 ≤ n ≤ 30; each number is between 1 and 99"),
     ar=("ثمن الترتيب", "تبديل كائنين بالطريقة المعتادة يتطلب **نسخًا واحدًا وإسنادين**:\n\n```\nNilai sementara = data[k];   // باني النسخ\ndata[k] = data[k + 1];       // عامل الإسناد\ndata[k + 1] = sementara;     // عامل الإسناد\n```\n\n"
         "أنشئ صنفًا `Nilai` (قيمة) فيه خاصية `int angka` وعدّادان `static int salin` (النسخ) و `static int tugas` (الإسنادات). بانيه الافتراضي يضع 0؛ وباني نسخه يزيد `salin`؛ وعامل إسناده يزيد `tugas`.\n\n"
         "اقرأ n عددًا في مصفوفة من `Nilai` **بضبط الخاصية `angka` مباشرة** (دون نسخ كائنات). رتِّب تصاعديًا *بالترتيب الفقاعي*: قارن العناصر **المتجاورة** وبدِّلها بالأسطر الثلاثة أعلاه فقط إذا كان الأيسر أكبر من الأيمن.\n\n"
         "اطبع الأعداد المرتبة ثم `Salin: s` و `Tugas: t`.\n\n"
         "عند `3 1 2`:\n\n```\n1 2 3\nSalin: 2\nTugas: 4\n```" + S_AR,
         "السطر الأول: n. السطر الثاني: n عددًا صحيحًا كلها مختلفة.", "ثلاثة أسطر: الأعداد المرتبة ثم `Salin: s` ثم `Tugas: t`.", "1 ≤ n ≤ 30؛ كل عدد بين 1 و 99"))

soal("teks-gabung", "sedang", "trimmed", BENDA_KATA + "n = {2: 1}.get(i, r.randint(2, 8))\nprint(n)\nprint(*[r.choice(KATA) for _ in range(n)])\n",
     program(TEKS + r'''
    Teks gabung(const Teks &lain) const {
        string semua = "";
        for (int k = 0; k < panjang; k++) {
            semua += huruf[k];
        }
        for (int k = 0; k < lain.panjang; k++) {
            semua += lain.huruf[k];
        }
        return Teks(semua);
    }
};''', r'''    int n;
    cin >> n;
    string kata;
    cin >> kata;
    Teks hasil(kata);
    Teks pemisah("-");
    for (int k = 1; k < n; k++) {
        cin >> kata;
        hasil = hasil.gabung(pemisah);
        hasil = hasil.gabung(Teks(kata));
    }
    cout << hasil.huruf << endl;
    cout << "Panjang: " << hasil.panjang << endl;''', "string"), wajib=WS,
     id=("Menggabung Teks", "Lanjutkan class `Teks` (atribut `char *huruf`, `int panjang`; konstruktor dari `string`, konstruktor salin, operator penugasan, destruktor) dengan method:\n\n"
         "- `Teks gabung(const Teks &lain) const` — mengembalikan objek `Teks` **baru** berisi teks ini disambung teks `lain`; kedua objek asal tidak berubah.\n\n"
         "Baca n kata. Gabungkan semuanya menjadi satu `Teks`, dengan tanda hubung di antara kata, lewat pernyataan berbentuk `hasil = hasil.gabung(…);`. Setiap pernyataan itu membuat objek baru, menugaskannya ke `hasil` (memori lama `hasil` harus dibebaskan), lalu menghancurkan objek sementaranya.\n\n"
         "Cetak teks hasil, lalu `Panjang: p`.\n\nUntuk `meja kursi lampu`:\n\n```\nmeja-kursi-lampu\nPanjang: 16\n```" + S_ID,
         "Baris pertama: n. Baris kedua: n kata dari huruf kecil.", "Dua baris: teks gabungan dan panjangnya.", "1 ≤ n ≤ 8; tiap kata paling panjang 10 huruf"),
     en=("Joining Texts", "Extend the class `Teks` (attributes `char *huruf`, `int panjang`; constructor from `string`, copy constructor, assignment operator, destructor) with a method:\n\n"
         "- `Teks gabung(const Teks &lain) const` (join) — returns a **new** `Teks` object holding this text followed by the text of `lain`; neither source object changes.\n\n"
         "Read n words. Join them all into one `Teks`, with a hyphen between words, through statements of the form `hasil = hasil.gabung(…);`. Each such statement creates a new object, assigns it to `hasil` (the old memory of `hasil` must be freed), then destroys the temporary.\n\n"
         "Print the resulting text, then `Panjang: p` (length).\n\nFor `meja kursi lampu`:\n\n```\nmeja-kursi-lampu\nPanjang: 16\n```" + S_EN,
         "First line: n. Second line: n words of lowercase letters.", "Two lines: the joined text and its length.", "1 ≤ n ≤ 8; each word has at most 10 letters"),
     ar=("وصل النصوص", "وسِّع الصنف `Teks` (الخاصيتان `char *huruf` و `int panjang`؛ بانٍ من `string` وباني نسخ وعامل إسناد وهادم) بطريقة:\n\n"
         "- `Teks gabung(const Teks &lain) const` (وصل) — تُرجع كائن `Teks` **جديدًا** فيه هذا النص متبوعًا بنص `lain`؛ ولا يتغير أي من الكائنين الأصليين.\n\n"
         "اقرأ n كلمة. اوصلها كلها في `Teks` واحد، بشرطة بين الكلمات، بتعليمات على الصورة `hasil = hasil.gabung(…);`. كل تعليمة كهذه تنشئ كائنًا جديدًا وتسنده إلى `hasil` (ويجب تحرير ذاكرة `hasil` القديمة) ثم تهدم الكائن المؤقت.\n\n"
         "اطبع النص الناتج ثم `Panjang: p` (الطول).\n\nعند `meja kursi lampu`:\n\n```\nmeja-kursi-lampu\nPanjang: 16\n```" + S_AR,
         "السطر الأول: n. السطر الثاني: n كلمة من حروف لاتينية صغيرة.", "سطران: النص الموصول وطوله.", "1 ≤ n ≤ 8؛ كل كلمة 10 حروف على الأكثر"))

# ================================= SULIT (4) =================================

soal("simulasi-cakupan", "sulit", "trimmed",
     "def blok(d):\n    t = ''\n    for _ in range(r.randint(0, 4)):\n        if d > 0 and r.random() < 0.35:\n            t += '{' + blok(d - 1) + '}'\n        else:\n            t += r.choice('abcdefghijklmnopqrstuvwxyz')\n    return t\nt = '{' + blok(4) + '}'\nwhile len(t) > 40 or not any(c.isalpha() for c in t):\n    t = '{' + blok(3) + '}'\nif i == 2:\n    t = '{' + r.choice('abcdefghijklmnopqrstuvwxyz') + '}'\nif i == 3:\n    t = '{a{b{c{d}}}}'\nif i == 4:\n    t = '{{}{x}y}'\nprint(t)\n",
     program(r'''
class Benda {
public:
    char nama;

    Benda(char c) : nama(c) {
        cout << "Buat " << nama << endl;
    }

    ~Benda() {
        cout << "Hapus " << nama << endl;
    }
};

void isiBlok(const string &kode, int &posisi) {
    if (kode[posisi] == '}') {
        posisi++;
        return;
    }
    if (kode[posisi] == '{') {
        posisi++;
        isiBlok(kode, posisi);
        isiBlok(kode, posisi);
        return;
    }
    Benda benda(kode[posisi]);
    posisi++;
    isiBlok(kode, posisi);
}''', r'''    string kode;
    cin >> kode;
    auto posisi = 1;
    isiBlok(kode, posisi);''', "string"), wajib=W,
     id=("Simulasi Cakupan", "Masukan adalah \"program\" mini yang ditulis dengan tiga macam karakter:\n\n- `{` membuka sebuah blok;\n- `}` menutup blok;\n- sebuah huruf kecil berarti \"buat objek bernama huruf itu di blok yang sedang terbuka\".\n\n"
         "Cetak apa yang akan dicetak program C++ yang sebenarnya: `Buat <huruf>` saat objek dibuat, dan `Hapus <huruf>` saat ia dihancurkan — yaitu ketika bloknya ditutup, dalam urutan terbalik dari pembuatan di blok itu.\n\n"
         "Untuk `{a{bc}d}`:\n\n```\nBuat a\nBuat b\nBuat c\nHapus c\nHapus b\nBuat d\nHapus d\nHapus a\n```\n\n"
         "**Biarkan C++ yang mengerjakannya.** Buat class `Benda` yang konstruktor dan destruktornya mencetak kedua baris itu, lalu tulis fungsi rekursif yang membaca kode karakter demi karakter: "
         "bila bertemu huruf, ia membuat objek `Benda` **lokal** lalu memanggil dirinya untuk sisa blok — sehingga objek itu baru hancur setelah sisa bloknya selesai diproses. Tidak perlu array, tumpukan buatan, atau `new`." + W_ID,
         "Satu baris: kode yang diawali `{` dan diakhiri `}` pasangannya, dengan semua kurung berpasangan.", "Satu baris per pembuatan dan penghancuran objek.", "Panjang kode 3 sampai 40 karakter; kedalaman blok paling banyak 5; paling sedikit satu objek dibuat."),
     en=("Scope Simulation", "The input is a mini \"program\" written with three kinds of characters:\n\n- `{` opens a block;\n- `}` closes a block;\n- a lowercase letter means \"create an object named by that letter in the block now open\".\n\n"
         "Print what the real C++ program would print: `Buat <letter>` when an object is created, and `Hapus <letter>` when it is destroyed — that is, when its block closes, in the reverse order of creation within that block.\n\n"
         "For `{a{bc}d}`:\n\n```\nBuat a\nBuat b\nBuat c\nHapus c\nHapus b\nBuat d\nHapus d\nHapus a\n```\n\n"
         "**Let C++ do the work.** Make a class `Benda` whose constructor and destructor print those two lines, then write a recursive function that reads the code character by character: "
         "on a letter it creates a **local** `Benda` object and then calls itself for the rest of the block — so the object is destroyed only after the rest of its block has been processed. No array, hand-made stack or `new` is needed." + W_EN,
         "One line: code that starts with `{` and ends with its matching `}`, all braces paired.", "One line per creation and destruction of an object.", "The code is 3 to 40 characters long; blocks are nested at most 5 deep; at least one object is created."),
     ar=("محاكاة المجال", "المدخل «برنامج» مصغّر مكتوب بثلاثة أنواع من المحارف:\n\n- `{` تفتح كتلة؛\n- `}` تغلق كتلة؛\n- الحرف الصغير يعني «أنشئ كائنًا اسمه هذا الحرف في الكتلة المفتوحة الآن».\n\n"
         "اطبع ما كان سيطبعه برنامج ++C الحقيقي: `Buat <الحرف>` عند إنشاء الكائن و `Hapus <الحرف>` عند هدمه — أي عند إغلاق كتلته، بعكس ترتيب الإنشاء داخل تلك الكتلة.\n\n"
         "عند `{a{bc}d}`:\n\n```\nBuat a\nBuat b\nBuat c\nHapus c\nHapus b\nBuat d\nHapus d\nHapus a\n```\n\n"
         "**دع ++C تقوم بالعمل.** أنشئ صنفًا `Benda` يطبع بانيه وهادمه هذين السطرين ثم اكتب دالة عودية تقرأ الكود محرفًا محرفًا: "
         "عند حرف تنشئ كائن `Benda` **محليًا** ثم تستدعي نفسها لبقية الكتلة — فلا يُهدم الكائن إلا بعد معالجة بقية كتلته. لا حاجة إلى مصفوفة ولا مكدّس مصنوع ولا `new`." + W_AR,
         "سطر واحد: كود يبدأ بـ `{` وينتهي بـ `}` المقابلة لها، وكل الأقواس متقابلة.", "سطر لكل إنشاء وكل هدم.", "طول الكود من 3 إلى 40 محرفًا؛ وتتداخل الكتل 5 مستويات على الأكثر؛ ويُنشأ كائن واحد على الأقل."))

soal("batal-ulang", "sulit", "trimmed",
     BENDA_KATA + "for _ in range(r.randint(1, 25)):\n    q = r.random()\n    if q < 0.4:\n        print('tulis', r.choice(KATA))\n    elif q < 0.65:\n        print('batal')\n    elif q < 0.8:\n        print('ulang')\n    else:\n        print('cetak')\nprint('cetak')\nprint('selesai')\n",
     program(r'''
class Dokumen {
public:
    char *teks;
    int panjang;

    Dokumen() : teks(new char[1]), panjang(0) {
        teks[0] = '\0';
    }

    Dokumen(const Dokumen &lain) : teks(new char[lain.panjang + 1]), panjang(lain.panjang) {
        for (int k = 0; k <= panjang; k++) {
            teks[k] = lain.teks[k];
        }
    }

    Dokumen &operator=(const Dokumen &lain) {
        if (this != &lain) {
            delete[] teks;
            panjang = lain.panjang;
            teks = new char[panjang + 1];
            for (int k = 0; k <= panjang; k++) {
                teks[k] = lain.teks[k];
            }
        }
        return *this;
    }

    ~Dokumen() {
        delete[] teks;
    }

    void tulis(const string &kata) {
        auto tambahan = (int) kata.size();
        auto spasi = 0;
        if (panjang > 0) {
            spasi = 1;
        }
        auto baru = new char[panjang + spasi + tambahan + 1];
        for (int k = 0; k < panjang; k++) {
            baru[k] = teks[k];
        }
        if (spasi == 1) {
            baru[panjang] = ' ';
        }
        for (int k = 0; k < tambahan; k++) {
            baru[panjang + spasi + k] = kata[k];
        }
        panjang = panjang + spasi + tambahan;
        baru[panjang] = '\0';
        delete[] teks;
        teks = baru;
    }
};''', r'''    Dokumen kini;
    Dokumen riwayat[26];
    Dokumen ulang[26];
    auto banyakRiwayat = 0;
    auto banyakUlang = 0;
    string perintah;
    while (cin >> perintah) {
        if (perintah == "selesai") {
            break;
        }
        if (perintah == "tulis") {
            string kata;
            cin >> kata;
            riwayat[banyakRiwayat] = kini;
            banyakRiwayat++;
            banyakUlang = 0;
            kini.tulis(kata);
        } else if (perintah == "batal") {
            if (banyakRiwayat == 0) {
                cout << "Tidak bisa batal" << endl;
            } else {
                ulang[banyakUlang] = kini;
                banyakUlang++;
                banyakRiwayat--;
                kini = riwayat[banyakRiwayat];
            }
        } else if (perintah == "ulang") {
            if (banyakUlang == 0) {
                cout << "Tidak bisa ulang" << endl;
            } else {
                riwayat[banyakRiwayat] = kini;
                banyakRiwayat++;
                banyakUlang--;
                kini = ulang[banyakUlang];
            }
        } else if (kini.panjang == 0) {
            cout << "(kosong)" << endl;
        } else {
            cout << kini.teks << endl;
        }
    }''', "string"), wajib=WS,
     id=("Batal dan Ulang", "Buat fitur *undo* dan *redo* sebuah penyunting teks dengan **menyimpan salinan** dokumen.\n\n"
         "Buat class `Dokumen` yang menyimpan teksnya di memori dinamis (`char *teks`, `int panjang`) dengan konstruktor bawaan (dokumen kosong), konstruktor salin, operator penugasan, destruktor, dan method `void tulis(const string &kata)` yang menambahkan sebuah kata di akhir — didahului satu spasi bila dokumennya tidak kosong.\n\n"
         "Program menyimpan dokumen saat ini, tumpukan *riwayat*, dan tumpukan *ulang* (keduanya berisi objek `Dokumen`). Perintah:\n\n"
         "- `tulis <kata>` — simpan salinan dokumen saat ini ke riwayat, **kosongkan tumpukan ulang**, lalu tambahkan kata;\n"
         "- `batal` — bila riwayat kosong, cetak `Tidak bisa batal`; bila tidak, simpan dokumen saat ini ke tumpukan ulang dan kembalikan dokumen ke salinan teratas riwayat;\n"
         "- `ulang` — bila tumpukan ulang kosong, cetak `Tidak bisa ulang`; bila tidak, simpan dokumen saat ini ke riwayat dan ambil salinan teratas tumpukan ulang;\n"
         "- `cetak` — cetak isi dokumen, atau `(kosong)`;\n- `selesai`.\n\n"
         "Contoh: `tulis meja`, `tulis kursi`, `batal`, `cetak`, `ulang`, `cetak`, `batal`, `tulis lampu`, `ulang`, `cetak`, `selesai` →\n\n```\nmeja\nmeja kursi\nTidak bisa ulang\nmeja lampu\n```" + S_ID,
         "Beberapa baris perintah, diakhiri `selesai`.", "Satu baris untuk tiap `cetak` dan tiap perintah yang tidak bisa dijalankan.", "Paling banyak 27 perintah; kata terdiri dari huruf kecil, paling panjang 10 huruf."),
     en=("Undo and Redo", "Build the *undo* and *redo* of a text editor by **keeping copies** of the document.\n\n"
         "Make a class `Dokumen` that keeps its text in dynamic memory (`char *teks`, `int panjang`) with a default constructor (empty document), a copy constructor, an assignment operator, a destructor, and a method `void tulis(const string &kata)` (write) that appends a word — preceded by one space when the document is not empty.\n\n"
         "The program keeps the current document, a *history* stack and a *redo* stack (both holding `Dokumen` objects). Commands:\n\n"
         "- `tulis <word>` — save a copy of the current document to the history, **empty the redo stack**, then append the word;\n"
         "- `batal` (undo) — if the history is empty, print `Tidak bisa batal`; otherwise save the current document to the redo stack and set the document back to the top copy of the history;\n"
         "- `ulang` (redo) — if the redo stack is empty, print `Tidak bisa ulang`; otherwise save the current document to the history and take the top copy of the redo stack;\n"
         "- `cetak` — print the document, or `(kosong)` (empty);\n- `selesai`.\n\n"
         "Example: `tulis meja`, `tulis kursi`, `batal`, `cetak`, `ulang`, `cetak`, `batal`, `tulis lampu`, `ulang`, `cetak`, `selesai` →\n\n```\nmeja\nmeja kursi\nTidak bisa ulang\nmeja lampu\n```" + S_EN,
         "Several command lines, ending with `selesai`.", "One line for every `cetak` and for every command that cannot be carried out.", "At most 27 commands; words have lowercase letters, at most 10."),
     ar=("تراجع وإعادة", "ابنِ خاصيتي *التراجع* و*الإعادة* لمحرر نصوص **بحفظ نسخ** من المستند.\n\n"
         "أنشئ صنفًا `Dokumen` يحفظ نصه في ذاكرة ديناميكية (`char *teks` و `int panjang`) له بانٍ افتراضي (مستند فارغ) وباني نسخ وعامل إسناد وهادم وطريقة `void tulis(const string &kata)` (اكتب) تضيف كلمة في آخره — تسبقها مسافة واحدة إذا لم يكن المستند فارغًا.\n\n"
         "يحفظ البرنامج المستند الحالي ومكدّس *السجل* ومكدّس *الإعادة* (وكلاهما يحوي كائنات `Dokumen`). الأوامر:\n\n"
         "- `tulis <كلمة>` — احفظ نسخة من المستند الحالي في السجل، **وأفرغ مكدّس الإعادة**، ثم أضف الكلمة؛\n"
         "- `batal` (تراجع) — إذا كان السجل فارغًا فاطبع `Tidak bisa batal`؛ وإلا فاحفظ المستند الحالي في مكدّس الإعادة وأعد المستند إلى أعلى نسخة في السجل؛\n"
         "- `ulang` (إعادة) — إذا كان مكدّس الإعادة فارغًا فاطبع `Tidak bisa ulang`؛ وإلا فاحفظ المستند الحالي في السجل وخذ أعلى نسخة من مكدّس الإعادة؛\n"
         "- `cetak` — اطبع المستند، أو `(kosong)` (فارغ)؛\n- `selesai`.\n\n"
         "مثال: `tulis meja` ثم `tulis kursi` ثم `batal` ثم `cetak` ثم `ulang` ثم `cetak` ثم `batal` ثم `tulis lampu` ثم `ulang` ثم `cetak` ثم `selesai` ←\n\n```\nmeja\nmeja kursi\nTidak bisa ulang\nmeja lampu\n```" + S_AR,
         "عدة أسطر أوامر تنتهي بـ `selesai`.", "سطر لكل `cetak` ولكل أمر يتعذر تنفيذه.", "27 أمرًا على الأكثر؛ الكلمات حروف لاتينية صغيرة، 10 حروف على الأكثر."))

soal("pangkat-besar", "sulit", "trimmed", "a, b = r.randint(2, 99), r.randint(1, 100)\nif i == 2:\n    b = 0\nif i == 3:\n    a, b = 99, 100\nif i == 4:\n    a, b = 2, 100\nif i == 5:\n    a, b = 10, r.randint(1, 100)\nprint(a, b)\n",
     program(r'''
class BilanganBesar {
public:
    int *digit;
    int panjang;

    BilanganBesar(int nilai) : digit(new int[10]), panjang(0) {
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

    BilanganBesar(const BilanganBesar &lain) : digit(new int[lain.panjang]), panjang(lain.panjang) {
        for (int k = 0; k < panjang; k++) {
            digit[k] = lain.digit[k];
        }
    }

    BilanganBesar &operator=(const BilanganBesar &lain) {
        if (this != &lain) {
            delete[] digit;
            panjang = lain.panjang;
            digit = new int[panjang];
            for (int k = 0; k < panjang; k++) {
                digit[k] = lain.digit[k];
            }
        }
        return *this;
    }

    ~BilanganBesar() {
        delete[] digit;
    }

    BilanganBesar kali(int k) const {
        BilanganBesar hasil(0);
        delete[] hasil.digit;
        hasil.digit = new int[panjang + 3];
        hasil.panjang = 0;
        auto simpan = 0;
        for (int p = 0; p < panjang; p++) {
            auto nilai = digit[p] * k + simpan;
            hasil.digit[hasil.panjang] = nilai % 10;
            hasil.panjang++;
            simpan = nilai / 10;
        }
        while (simpan > 0) {
            hasil.digit[hasil.panjang] = simpan % 10;
            hasil.panjang++;
            simpan /= 10;
        }
        return hasil;
    }

    void cetak() const {
        for (int p = panjang - 1; p >= 0; p--) {
            cout << digit[p];
        }
        cout << endl;
    }
};''', r'''    int a, b;
    cin >> a >> b;
    BilanganBesar hasil(1);
    for (int k = 0; k < b; k++) {
        hasil = hasil.kali(a);
    }
    hasil.cetak();'''), wajib=WS,
     id=("Pangkat Besar", "Hitung a pangkat b **dengan tepat**, untuk a sampai 99 dan b sampai 100 — hasilnya bisa 200 digit.\n\n"
         "Buat class `BilanganBesar` yang menyimpan digit-digitnya di memori dinamis **seukuran yang diperlukan** (`int *digit`, `int panjang`; `digit[0]` adalah digit satuan). Karena ukurannya berubah-ubah, class ini memerlukan aturan tiga:\n\n"
         "- konstruktor `BilanganBesar(int nilai)`;\n- konstruktor salin, operator penugasan, destruktor;\n- `BilanganBesar kali(int k) const` — mengembalikan objek **baru** berisi bilangan ini dikali k;\n- `void cetak() const`.\n\n"
         "Di `main`, mulai dari `BilanganBesar hasil(1);` dan ulangi `hasil = hasil.kali(a);` sebanyak b kali. Setiap putaran membuat objek baru dan membuang yang lama; tanpa operator penugasan yang benar, memori bocor atau program rusak.\n\n"
         "Contoh: `2 10` → `1024`; `7 0` → `1`; `99 3` → `970299`; `2 100` → `1267650600228229401496703205376`." + S_ID,
         "Satu baris berisi a dan b.", "Satu baris: a pangkat b, tanpa nol di depan.", "2 ≤ a ≤ 99; 0 ≤ b ≤ 100"),
     en=("Big Power", "Compute a to the power b **exactly**, for a up to 99 and b up to 100 — the result can have 200 digits.\n\n"
         "Make a class `BilanganBesar` (big number) that keeps its digits in dynamic memory **of just the size needed** (`int *digit`, `int panjang`; `digit[0]` is the units digit). Because the size keeps changing, the class needs the rule of three:\n\n"
         "- a constructor `BilanganBesar(int nilai)`;\n- a copy constructor, an assignment operator, a destructor;\n- `BilanganBesar kali(int k) const` — returns a **new** object holding this number times k;\n- `void cetak() const`.\n\n"
         "In `main`, start from `BilanganBesar hasil(1);` and repeat `hasil = hasil.kali(a);` b times. Every pass creates a new object and discards the old one; without a correct assignment operator, memory leaks or the program breaks.\n\n"
         "Examples: `2 10` → `1024`; `7 0` → `1`; `99 3` → `970299`; `2 100` → `1267650600228229401496703205376`." + S_EN,
         "One line with a and b.", "One line: a to the power b, without leading zeros.", "2 ≤ a ≤ 99; 0 ≤ b ≤ 100"),
     ar=("القوة الكبيرة", "احسب a أُسّ b **بدقة**، حيث a حتى 99 و b حتى 100 — وقد يبلغ الناتج 200 رقم.\n\n"
         "أنشئ صنفًا `BilanganBesar` (عدد كبير) يحفظ أرقامه في ذاكرة ديناميكية **بالحجم اللازم فقط** (`int *digit` و `int panjang`؛ و `digit[0]` رقم الآحاد). ولأن الحجم يتغير يحتاج الصنف إلى قاعدة الثلاثة:\n\n"
         "- بانٍ `BilanganBesar(int nilai)`؛\n- باني نسخ وعامل إسناد وهادم؛\n- `BilanganBesar kali(int k) const` — تُرجع كائنًا **جديدًا** فيه هذا العدد مضروبًا في k؛\n- `void cetak() const`.\n\n"
         "في `main` ابدأ من `BilanganBesar hasil(1);` وكرِّر `hasil = hasil.kali(a);` b مرة. كل دورة تنشئ كائنًا جديدًا وتطرح القديم؛ وبلا عامل إسناد صحيح تتسرب الذاكرة أو يتعطل البرنامج.\n\n"
         "أمثلة: `2 10` ← `1024`؛ `7 0` ← `1`؛ `99 3` ← `970299`؛ `2 100` ← `1267650600228229401496703205376`." + S_AR,
         "سطر واحد فيه a و b.", "سطر واحد: a أُسّ b بلا أصفار في أوله.", "2 ≤ a ≤ 99؛ 0 ≤ b ≤ 100"))

soal("pelacak-objek", "sulit", "trimmed",
     "hidup = 0\nfor _ in range(r.randint(1, 20)):\n    q = r.random()\n    if hidup == 0 or q < 0.3:\n        if hidup < 10:\n            print('buat', r.randint(1, 99))\n            hidup += 1\n        else:\n            print('cetak')\n    elif q < 0.5 and hidup < 10:\n        print('salin', r.randint(1, hidup))\n        hidup += 1\n    elif q < 0.7:\n        print('tugas', r.randint(1, hidup), r.randint(1, hidup))\n    elif q < 0.85:\n        print('hapus')\n        hidup -= 1\n    else:\n        print('cetak')\nif i == 2:\n    print('hapus')\n    print('hapus')\n    print('hapus')\nprint('selesai')\n",
     program(r'''
class Pelacak {
public:
    static int dibuat;
    int id;
    int nilai;

    Pelacak(int v) : nilai(v) {
        dibuat++;
        id = dibuat;
        cout << "+" << id << endl;
    }

    Pelacak(const Pelacak &lain) : nilai(lain.nilai) {
        dibuat++;
        id = dibuat;
        cout << "+" << id << " salin " << lain.id << endl;
    }

    Pelacak &operator=(const Pelacak &lain) {
        nilai = lain.nilai;
        cout << id << " = " << lain.id << endl;
        return *this;
    }

    ~Pelacak() {
        cout << "-" << id << endl;
    }
};

int Pelacak::dibuat = 0;''', r'''    Pelacak *daftar[30];
    auto banyak = 0;
    string perintah;
    while (cin >> perintah) {
        if (perintah == "selesai") {
            break;
        }
        if (perintah == "buat") {
            int v;
            cin >> v;
            daftar[banyak] = new Pelacak(v);
            banyak++;
        } else if (perintah == "salin") {
            int k;
            cin >> k;
            daftar[banyak] = new Pelacak(*daftar[k - 1]);
            banyak++;
        } else if (perintah == "tugas") {
            int a, b;
            cin >> a >> b;
            *daftar[a - 1] = *daftar[b - 1];
        } else if (perintah == "hapus") {
            if (banyak == 0) {
                cout << "kosong" << endl;
            } else {
                banyak--;
                delete daftar[banyak];
            }
        } else {
            cout << "Isi:";
            for (int k = 0; k < banyak; k++) {
                cout << " " << daftar[k]->id << ":" << daftar[k]->nilai;
            }
            cout << endl;
        }
    }
    while (banyak > 0) {
        banyak--;
        delete daftar[banyak];
    }''', "string"), wajib=WS,
     id=("Pelacak Objek", "Buat class `Pelacak` yang melaporkan setiap peristiwa dalam hidupnya. Atribut: `static int dibuat`, `int id`, `int nilai`. Setiap objek baru — juga hasil penyalinan — mendapat `id` berikutnya (1, 2, 3, …).\n\n"
         "- konstruktor `Pelacak(int v)` mencetak `+<id>`;\n- konstruktor salin mencetak `+<id> salin <id asal>` dan menyalin `nilai`;\n- operator penugasan menyalin `nilai` saja (id tetap) dan mencetak `<id> = <id asal>`;\n- destruktor mencetak `-<id>`.\n\n"
         "Objek-objek disimpan berurutan dalam sebuah daftar (array penunjuk); **posisi** dihitung mulai dari 1. Perintah:\n\n"
         "- `buat v` — objek baru dengan `new`, di akhir daftar;\n- `salin k` — objek baru hasil **menyalin** objek di posisi k, di akhir daftar;\n- `tugas a b` — objek di posisi a `=` objek di posisi b;\n"
         "- `hapus` — hancurkan objek terakhir di daftar; bila daftar kosong, cetak `kosong`;\n- `cetak` — cetak `Isi:` diikuti `<id>:<nilai>` tiap objek;\n- `selesai` — hancurkan semua yang tersisa, dari yang terakhir ke yang pertama.\n\n"
         "Contoh: `buat 5`, `buat 8`, `salin 1`, `tugas 2 3`, `cetak`, `hapus`, `selesai` →\n\n```\n+1\n+2\n+3 salin 1\n2 = 3\nIsi: 1:5 2:5 3:5\n-3\n-2\n-1\n```" + S_ID,
         "Beberapa baris perintah, diakhiri `selesai`.", "Satu baris per peristiwa, seperti contoh.", "Paling banyak 23 perintah; paling banyak 10 objek hidup; posisi yang disebut selalu ada; 1 ≤ v ≤ 99"),
     en=("Object Tracker", "Make a class `Pelacak` (tracker) that reports every event of its life. Attributes: `static int dibuat`, `int id`, `int nilai` (value). Every new object — copies too — gets the next `id` (1, 2, 3, …).\n\n"
         "- the constructor `Pelacak(int v)` prints `+<id>`;\n- the copy constructor prints `+<id> salin <id of the source>` and copies `nilai`;\n- the assignment operator copies `nilai` only (the id stays) and prints `<id> = <id of the source>`;\n- the destructor prints `-<id>`.\n\n"
         "The objects are kept in order in a list (an array of pointers); **positions** are counted from 1. Commands:\n\n"
         "- `buat v` — a new object with `new`, at the end of the list;\n- `salin k` — a new object made by **copying** the object at position k, at the end of the list;\n- `tugas a b` — the object at position a `=` the object at position b;\n"
         "- `hapus` — destroy the last object of the list; if the list is empty, print `kosong`;\n- `cetak` — print `Isi:` followed by `<id>:<value>` of every object;\n- `selesai` — destroy all that remain, from the last to the first.\n\n"
         "Example: `buat 5`, `buat 8`, `salin 1`, `tugas 2 3`, `cetak`, `hapus`, `selesai` →\n\n```\n+1\n+2\n+3 salin 1\n2 = 3\nIsi: 1:5 2:5 3:5\n-3\n-2\n-1\n```" + S_EN,
         "Several command lines, ending with `selesai`.", "One line per event, as in the example.", "At most 23 commands; at most 10 live objects; every position named exists; 1 ≤ v ≤ 99"),
     ar=("متتبع الكائنات", "أنشئ صنفًا `Pelacak` (متتبع) يبلّغ عن كل حدث في حياته. الخصائص: `static int dibuat` و `int id` و `int nilai` (القيمة). كل كائن جديد — والنسخ كذلك — ينال `id` التالي (1، 2، 3، …).\n\n"
         "- الباني `Pelacak(int v)` يطبع `+<id>`؛\n- باني النسخ يطبع `+<id> salin <id المصدر>` وينسخ `nilai`؛\n- عامل الإسناد ينسخ `nilai` فقط (ويبقى id) ويطبع `<id> = <id المصدر>`؛\n- الهادم يطبع `-<id>`.\n\n"
         "تُحفظ الكائنات بالترتيب في قائمة (مصفوفة مؤشرات)؛ و**المواضع** تُعدّ من 1. الأوامر:\n\n"
         "- `buat v` — كائن جديد بـ `new` في آخر القائمة؛\n- `salin k` — كائن جديد **بنسخ** الكائن الذي في الموضع k، في آخر القائمة؛\n- `tugas a b` — الكائن في الموضع a `=` الكائن في الموضع b؛\n"
         "- `hapus` — اهدم آخر كائن في القائمة؛ وإذا كانت القائمة فارغة فاطبع `kosong`؛\n- `cetak` — اطبع `Isi:` يليها `<id>:<القيمة>` لكل كائن؛\n- `selesai` — اهدم كل ما بقي من الأخير إلى الأول.\n\n"
         "مثال: `buat 5` ثم `buat 8` ثم `salin 1` ثم `tugas 2 3` ثم `cetak` ثم `hapus` ثم `selesai` ←\n\n```\n+1\n+2\n+3 salin 1\n2 = 3\nIsi: 1:5 2:5 3:5\n-3\n-2\n-1\n```" + S_AR,
         "عدة أسطر أوامر تنتهي بـ `selesai`.", "سطر لكل حدث كما في المثال.", "23 أمرًا على الأكثر؛ 10 كائنات حية على الأكثر؛ كل موضع مذكور موجود؛ 1 ≤ v ≤ 99"))

tulis(__file__, "PBO301", 3, "cpp", {"mudah_sekali": 8, "mudah": 8, "mudah_sedang": 6, "sedang": 6, "sulit": 4})
