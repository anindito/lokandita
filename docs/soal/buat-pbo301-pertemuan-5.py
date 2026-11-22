#!/usr/bin/env python3
"""Menyusun docs/soal/pbo301-pertemuan-5.json.

PBO301 pertemuan 5 — Relasi Antar Obyek, Object Diagram. Konsep yang diizinkan
di produksi: 21 konsep dasar, MODERN_CPP, CLASS_OBJECT, CONSTRUCTOR_DESTRUCTOR,
COPY_SEMANTICS, ENCAPSULATION, KOMPOSISI_AGREGASI.

Tiga relasi dari silabus dan cara soal-soal ini membuatnya terlihat di keluaran:
- komposisi (bagian mati bersama keseluruhan): objek anggota langsung; urutan
  konstruktor/destruktor bagian dan keseluruhan dicetak;
- agregasi (bagian hidup tanpa keseluruhan): penunjuk ke objek yang dikelola
  pihak lain; bagian tetap ada, dan perubahan pada bagian terlihat dari semua
  yang merujuknya;
- asosiasi (saling kenal tanpa memiliki): penunjuk yang boleh kosong (0..1),
  banyak (1..*), atau dua arah.

Setiap soal mensyaratkan CLASS_OBJECT dan KOMPOSISI_AGREGASI di kode
mahasiswa. Detektor mengenali relasi dari ANGGOTA DATA bertipe kelas lain
(langsung, penunjuk, atau array); ia tidak membedakan ketiga relasi, dan
asosiasi yang hanya lewat parameter method tidak dikenali. Karena itu setiap
solusi referensi menyimpan relasinya sebagai anggota data.

Solusi referensi memakai `struct`-bergaya-kelas hanya lewat `class`; tanpa
kontainer STL (array biasa dan penunjuk).

Dilewati dari silabus pertemuan 5 (tidak bisa dinilai dari keluaran): notasi
relasi UML, role name, object diagram.

    python3 docs/soal/buat-pbo301-pertemuan-5.py
"""
from bangun import program, soal, tulis

W = ("CLASS_OBJECT", "KOMPOSISI_AGREGASI")
W_ID = "\n\n**Kode harus memuat class yang menyimpan objek class lain sebagai anggota data** (langsung, lewat penunjuk, atau dalam array): keluaran yang benar tanpa itu hanya mendapat setengah nilai."
W_EN = "\n\n**The code must contain a class that keeps an object of another class as a data member** (directly, through a pointer, or in an array): correct output without that gets only half the marks."
W_AR = "\n\n**يجب أن يحتوي الكود على صنف يحفظ كائنًا من صنف آخر عضوَ بيانات** (مباشرة أو عبر مؤشر أو في مصفوفة): المخرج الصحيح بلا ذلك ينال نصف الدرجة فقط."

NAMA = "NAMA = ['Andi', 'Budi', 'Citra', 'Dewi', 'Eko', 'Fajar', 'Gita', 'Hadi', 'Indah', 'Joko', 'Kartika', 'Lina', 'Made', 'Nanda', 'Putri', 'Rudi', 'Sari', 'Tono', 'Wulan', 'Yoga']\n"
KOTA = "KOTA = ['Bandung', 'Bogor', 'Cirebon', 'Depok', 'Garut', 'Jakarta', 'Kudus', 'Malang', 'Medan', 'Padang', 'Solo', 'Tegal']\n"
BENDA = "BENDA = ['meja', 'kursi', 'lampu', 'buku', 'pena', 'tas', 'jam', 'kunci', 'pintu', 'kaca', 'peta', 'radio']\n"

TITIK = r'''
class Titik {
public:
    int x = 0;
    int y = 0;
};'''

# ============================== MUDAH SEKALI (8) ==============================

soal("garis-dua-titik", "mudah_sekali", "trimmed", "print(r.randint(-100, 100), r.randint(-100, 100))\nprint(r.randint(-100, 100), r.randint(-100, 100))\n",
     program(TITIK + r'''

class Garis {
private:
    Titik awal;
    Titik akhir;

public:
    Garis(Titik a, Titik b) : awal(a), akhir(b) {
    }

    int panjangManhattan() const {
        int dx = awal.x - akhir.x;
        int dy = awal.y - akhir.y;
        if (dx < 0) dx = -dx;
        if (dy < 0) dy = -dy;
        return dx + dy;
    }
};''', r'''    Titik a, b;
    cin >> a.x >> a.y;
    cin >> b.x >> b.y;
    Garis g(a, b);
    cout << g.panjangManhattan() << endl;'''), wajib=W,
     id=("Garis dari Dua Titik", "Objek bisa **tersusun dari objek lain**. Sebuah garis terdiri dari dua titik ujung: bila garisnya hilang, kedua titik ujungnya ikut hilang. Relasi \"memiliki bagian\" sekuat ini disebut **komposisi**, dan di C++ ditulis sebagai anggota data bertipe class lain.\n\n"
         "Buat class `Titik` (atribut `int x`, `int y`) dan class `Garis` yang anggota datanya dua objek `Titik`: `awal` dan `akhir`. Beri `Garis` konstruktor `Garis(Titik a, Titik b)` dan method `int panjangManhattan() const` — |x awal − x akhir| + |y awal − y akhir|.\n\n"
         "Baca dua titik dan cetak panjang Manhattan garisnya.\n\nContoh: (1, 2) dan (4, 6) → 7." + W_ID,
         "Dua baris, masing-masing berisi x dan y sebuah titik.", "Satu bilangan bulat.", "Semua koordinat antara −100 dan 100."),
     en=("A Line from Two Points", "An object can be **built from other objects**. A line consists of two end points: when the line goes, its end points go with it. A \"has a part\" relation this strong is called **composition**, and in C++ it is written as a data member whose type is another class.\n\n"
         "Make a class `Titik` (point; attributes `int x`, `int y`) and a class `Garis` (line) whose data members are two `Titik` objects: `awal` (start) and `akhir` (end). Give `Garis` a constructor `Garis(Titik a, Titik b)` and a method `int panjangManhattan() const` — |x start − x end| + |y start − y end|.\n\n"
         "Read two points and print the Manhattan length of the line.\n\nExample: (1, 2) and (4, 6) → 7." + W_EN,
         "Two lines, each with the x and y of a point.", "One integer.", "All coordinates are between −100 and 100."),
     ar=("خط من نقطتين", "يمكن أن **يُبنى كائن من كائنات أخرى**. الخط يتكون من نقطتي طرفين: إذا زال الخط زالت نقطتاه. علاقة «له جزء» بهذه القوة تسمى **التركيب**، وتُكتب في ++C عضوَ بيانات نوعه صنف آخر.\n\n"
         "أنشئ صنفًا `Titik` (نقطة؛ الخاصيتان `int x` و `int y`) وصنفًا `Garis` (خط) عضوا بياناته كائنان من `Titik`: `awal` (البداية) و `akhir` (النهاية). أعطِ `Garis` بانيًا `Garis(Titik a, Titik b)` وطريقة `int panjangManhattan() const` — |x البداية − x النهاية| + |y البداية − y النهاية|.\n\n"
         "اقرأ نقطتين واطبع طول مانهاتن للخط.\n\nمثال: (1, 2) و (4, 6) ← 7." + W_AR,
         "سطران في كل منهما x و y لنقطة.", "عدد صحيح واحد.", "كل الإحداثيات بين −100 و 100."))

soal("alamat-mahasiswa", "mudah_sekali", "trimmed", NAMA + KOTA + "print(r.choice(NAMA), r.choice(KOTA), r.randint(10000, 99999))\n",
     program(r'''
class Alamat {
private:
    string kota;
    int kodePos;

public:
    Alamat(const string &k, int p) : kota(k), kodePos(p) {
    }

    void cetak() const {
        cout << kota << " (" << kodePos << ")";
    }
};

class Mahasiswa {
private:
    string nama;
    Alamat alamat;

public:
    Mahasiswa(const string &n, const string &kota, int kodePos) : nama(n), alamat(kota, kodePos) {
    }

    void cetak() const {
        cout << nama << " tinggal di ";
        alamat.cetak();
        cout << endl;
    }
};''', r'''    string nama, kota;
    int kodePos;
    cin >> nama >> kota >> kodePos;
    Mahasiswa m(nama, kota, kodePos);
    m.cetak();''', "string"), wajib=W,
     id=("Alamat Mahasiswa", "Buat class `Alamat` (atribut `private` `string kota`, `int kodePos`; konstruktor; method `void cetak() const` yang mencetak `<kota> (<kodePos>)` tanpa pindah baris).\n\n"
         "Buat class `Mahasiswa` dengan atribut `private` `string nama` dan **sebuah objek `Alamat`**. Konstruktornya menerima nama, kota, dan kode pos; objek anggota yang konstruktornya berparameter harus dibuat di **daftar inisialisasi**:\n\n```\nMahasiswa(const string &n, const string &kota, int kodePos) : nama(n), alamat(kota, kodePos) { }\n```\n\n"
         "Method `void cetak() const` mencetak `<nama> tinggal di ` lalu **menyerahkan sisanya kepada `alamat.cetak()`**.\n\nUntuk `Citra Bandung 40132` keluarannya `Citra tinggal di Bandung (40132)`." + W_ID,
         "Satu baris berisi nama, kota, dan kode pos.", "Satu baris seperti contoh.", "Nama dan kota satu kata; kode pos lima digit."),
     en=("A Student's Address", "Make a class `Alamat` (address; `private` attributes `string kota` (town), `int kodePos` (postcode); a constructor; a method `void cetak() const` that prints `<town> (<postcode>)` without a line break).\n\n"
         "Make a class `Mahasiswa` (student) with the `private` attributes `string nama` and **an `Alamat` object**. Its constructor takes the name, the town and the postcode; a member object whose constructor has parameters must be built in the **initialiser list**:\n\n```\nMahasiswa(const string &n, const string &kota, int kodePos) : nama(n), alamat(kota, kodePos) { }\n```\n\n"
         "The method `void cetak() const` prints `<name> tinggal di ` (lives in) and then **leaves the rest to `alamat.cetak()`**.\n\nFor `Citra Bandung 40132` the output is `Citra tinggal di Bandung (40132)`." + W_EN,
         "One line with the name, the town and the postcode.", "One line as in the example.", "Name and town are one word; the postcode has five digits."),
     ar=("عنوان الطالب", "أنشئ صنفًا `Alamat` (عنوان؛ الخاصيتان الخاصتان `string kota` (المدينة) و `int kodePos` (الرمز البريدي)؛ وبانٍ؛ وطريقة `void cetak() const` تطبع `<المدينة> (<الرمز>)` دون سطر جديد).\n\n"
         "أنشئ صنفًا `Mahasiswa` (طالب) خاصيتاه الخاصتان `string nama` و**كائن من `Alamat`**. بانيه يأخذ الاسم والمدينة والرمز البريدي؛ والكائن العضو الذي لبانيه وسائط يجب أن يُبنى في **قائمة التهيئة**:\n\n```\nMahasiswa(const string &n, const string &kota, int kodePos) : nama(n), alamat(kota, kodePos) { }\n```\n\n"
         "الطريقة `void cetak() const` تطبع `<الاسم> tinggal di ` (يسكن في) ثم **تترك الباقي لـ `alamat.cetak()`**.\n\nعند `Citra Bandung 40132` يكون المخرج `Citra tinggal di Bandung (40132)`." + W_AR,
         "سطر واحد فيه الاسم والمدينة والرمز البريدي.", "سطر واحد كما في المثال.", "الاسم والمدينة كلمة واحدة؛ والرمز البريدي خمسة أرقام."))

soal("mesin-mobil", "mudah_sekali", "trimmed", "print(r.choice(['Avanza', 'Kijang', 'Ertiga', 'Jazz', 'Brio', 'Agya', 'Xenia', 'Calya']), r.randint(60, 300))\n",
     program(r'''
class Mesin {
private:
    int tenaga;

public:
    Mesin(int t) : tenaga(t) {
        cout << "Mesin " << tenaga << " dibuat" << endl;
    }

    ~Mesin() {
        cout << "Mesin " << tenaga << " dihancurkan" << endl;
    }
};

class Mobil {
private:
    string nama;
    Mesin mesin;

public:
    Mobil(const string &n, int tenaga) : nama(n), mesin(tenaga) {
        cout << "Mobil " << nama << " dibuat" << endl;
    }

    ~Mobil() {
        cout << "Mobil " << nama << " dihancurkan" << endl;
    }
};''', r'''    string nama;
    int tenaga;
    cin >> nama >> tenaga;
    Mobil mobil(nama, tenaga);''', "string"), wajib=W,
     id=("Mesin di Dalam Mobil", "Pada **komposisi**, bagian lahir dan mati bersama keseluruhannya — dan C++ mengatur urutannya sendiri: objek anggota dibuat **sebelum** badan konstruktor pemiliknya berjalan, dan dihancurkan **sesudah** badan destruktor pemiliknya selesai.\n\n"
         "Buat class `Mesin` (atribut `int tenaga`; konstruktor mencetak `Mesin <tenaga> dibuat`; destruktor mencetak `Mesin <tenaga> dihancurkan`).\n\n"
         "Buat class `Mobil` dengan atribut `string nama` dan **objek `Mesin` sebagai anggota**; konstruktornya mencetak `Mobil <nama> dibuat`, destruktornya `Mobil <nama> dihancurkan`.\n\n"
         "Baca nama dan tenaga, buat satu `Mobil`, selesai.\n\nUntuk `Kijang 100`:\n\n```\nMesin 100 dibuat\nMobil Kijang dibuat\nMobil Kijang dihancurkan\nMesin 100 dihancurkan\n```" + W_ID,
         "Satu baris berisi nama mobil (satu kata) dan tenaga mesinnya.", "Empat baris seperti contoh.", "60 ≤ tenaga ≤ 300"),
     en=("The Engine inside the Car", "In **composition** a part is born and dies with its whole — and C++ arranges the order itself: member objects are created **before** the body of the owner's constructor runs, and destroyed **after** the body of the owner's destructor has finished.\n\n"
         "Make a class `Mesin` (engine; attribute `int tenaga` (power); the constructor prints `Mesin <power> dibuat` (created); the destructor prints `Mesin <power> dihancurkan` (destroyed)).\n\n"
         "Make a class `Mobil` (car) with an attribute `string nama` and **a `Mesin` object as a member**; its constructor prints `Mobil <name> dibuat`, its destructor `Mobil <name> dihancurkan`.\n\n"
         "Read the name and the power, create one `Mobil`, done.\n\nFor `Kijang 100`:\n\n```\nMesin 100 dibuat\nMobil Kijang dibuat\nMobil Kijang dihancurkan\nMesin 100 dihancurkan\n```" + W_EN,
         "One line with the car's name (one word) and its engine power.", "Four lines as in the example.", "60 ≤ power ≤ 300"),
     ar=("المحرك داخل السيارة", "في **التركيب** يولد الجزء ويموت مع الكل — و ++C ترتّب ذلك بنفسها: الكائنات الأعضاء تُنشأ **قبل** تنفيذ جسم باني المالك، وتُهدم **بعد** انتهاء جسم هادمه.\n\n"
         "أنشئ صنفًا `Mesin` (محرك؛ الخاصية `int tenaga` (القدرة)؛ الباني يطبع `Mesin <القدرة> dibuat` (أُنشئ)؛ والهادم يطبع `Mesin <القدرة> dihancurkan` (هُدم)).\n\n"
         "أنشئ صنفًا `Mobil` (سيارة) فيه خاصية `string nama` و**كائن `Mesin` عضوًا**؛ بانيه يطبع `Mobil <الاسم> dibuat` وهادمه `Mobil <الاسم> dihancurkan`.\n\n"
         "اقرأ الاسم والقدرة وأنشئ `Mobil` واحدة وانتهى.\n\nعند `Kijang 100`:\n\n```\nMesin 100 dibuat\nMobil Kijang dibuat\nMobil Kijang dihancurkan\nMesin 100 dihancurkan\n```" + W_AR,
         "سطر واحد فيه اسم السيارة (كلمة واحدة) وقدرة محركها.", "أربعة أسطر كما في المثال.", "60 ≤ القدرة ≤ 300"))

soal("tim-pelatih", "mudah_sekali", "trimmed", NAMA + "print(r.choice(['Garuda', 'Elang', 'Rajawali', 'Harimau', 'Banteng', 'Macan']), r.choice(NAMA))\n",
     program(r'''
class Pelatih {
private:
    string nama;

public:
    Pelatih(const string &n) : nama(n) {
        cout << "Pelatih " << nama << " datang" << endl;
    }

    ~Pelatih() {
        cout << "Pelatih " << nama << " pulang" << endl;
    }

    string getNama() const {
        return nama;
    }
};

class Tim {
private:
    string nama;
    Pelatih *pelatih;

public:
    Tim(const string &n, Pelatih *p) : nama(n), pelatih(p) {
        cout << "Tim " << nama << " dibentuk bersama " << pelatih->getNama() << endl;
    }

    ~Tim() {
        cout << "Tim " << nama << " bubar" << endl;
    }
};''', r'''    string namaTim, namaPelatih;
    cin >> namaTim >> namaPelatih;
    Pelatih pelatih(namaPelatih);
    {
        Tim tim(namaTim, &pelatih);
    }
    cout << "Pelatih " << pelatih.getNama() << " masih ada" << endl;''', "string"), wajib=W,
     id=("Tim dan Pelatih", "Sebuah tim *punya* pelatih, tetapi pelatih tidak ikut lenyap ketika timnya bubar. Relasi \"punya, tetapi tidak memiliki hidup-matinya\" disebut **agregasi**. Di C++ agregasi ditulis sebagai **penunjuk** ke objek yang dibuat dan dikelola pihak lain.\n\n"
         "Buat class `Pelatih` (atribut `string nama`; konstruktor mencetak `Pelatih <nama> datang`; destruktor mencetak `Pelatih <nama> pulang`; `getNama()`).\n\n"
         "Buat class `Tim` dengan atribut `string nama` dan `Pelatih *pelatih`. Konstruktor `Tim(const string &n, Pelatih *p)` mencetak `Tim <nama> dibentuk bersama <nama pelatih>`; destruktornya mencetak `Tim <nama> bubar` dan **tidak** men-`delete` pelatih.\n\n"
         "Di `main`: buat pelatih; di dalam sebuah blok `{ }` buat tim dengan pelatih itu; setelah blok, cetak `Pelatih <nama> masih ada`.\n\n"
         "Untuk `Garuda Budi`:\n\n```\nPelatih Budi datang\nTim Garuda dibentuk bersama Budi\nTim Garuda bubar\nPelatih Budi masih ada\nPelatih Budi pulang\n```" + W_ID,
         "Satu baris berisi nama tim dan nama pelatih.", "Lima baris seperti contoh.", "Kedua nama satu kata."),
     en=("Team and Coach", "A team *has* a coach, but the coach does not vanish when the team breaks up. A relation \"has, but does not own its life\" is called **aggregation**. In C++ aggregation is written as a **pointer** to an object created and managed elsewhere.\n\n"
         "Make a class `Pelatih` (coach; attribute `string nama`; the constructor prints `Pelatih <name> datang` (arrives); the destructor prints `Pelatih <name> pulang` (goes home); `getNama()`).\n\n"
         "Make a class `Tim` (team) with the attributes `string nama` and `Pelatih *pelatih`. The constructor `Tim(const string &n, Pelatih *p)` prints `Tim <name> dibentuk bersama <coach's name>` (formed with); its destructor prints `Tim <name> bubar` (disbanded) and does **not** `delete` the coach.\n\n"
         "In `main`: create the coach; inside a block `{ }` create the team with that coach; after the block print `Pelatih <name> masih ada` (is still here).\n\n"
         "For `Garuda Budi`:\n\n```\nPelatih Budi datang\nTim Garuda dibentuk bersama Budi\nTim Garuda bubar\nPelatih Budi masih ada\nPelatih Budi pulang\n```" + W_EN,
         "One line with the team's name and the coach's name.", "Five lines as in the example.", "Both names are one word."),
     ar=("الفريق والمدرب", "للفريق مدرب، لكن المدرب لا يزول إذا انحلّ الفريق. علاقة «له، لكنه لا يملك حياته» تسمى **التجميع**. وفي ++C يُكتب التجميع **مؤشرًا** إلى كائن أُنشئ ويُدار في مكان آخر.\n\n"
         "أنشئ صنفًا `Pelatih` (مدرب؛ الخاصية `string nama`؛ الباني يطبع `Pelatih <الاسم> datang` (وصل)؛ والهادم يطبع `Pelatih <الاسم> pulang` (انصرف)؛ و `getNama()`).\n\n"
         "أنشئ صنفًا `Tim` (فريق) خاصيتاه `string nama` و `Pelatih *pelatih`. الباني `Tim(const string &n, Pelatih *p)` يطبع `Tim <الاسم> dibentuk bersama <اسم المدرب>` (تشكّل مع)؛ وهادمه يطبع `Tim <الاسم> bubar` (انحلّ) و**لا** ينفّذ `delete` على المدرب.\n\n"
         "في `main`: أنشئ المدرب؛ وداخل كتلة `{ }` أنشئ الفريق بهذا المدرب؛ وبعد الكتلة اطبع `Pelatih <الاسم> masih ada` (ما زال موجودًا).\n\n"
         "عند `Garuda Budi`:\n\n```\nPelatih Budi datang\nTim Garuda dibentuk bersama Budi\nTim Garuda bubar\nPelatih Budi masih ada\nPelatih Budi pulang\n```" + W_AR,
         "سطر واحد فيه اسم الفريق واسم المدرب.", "خمسة أسطر كما في المثال.", "الاسمان كلمة واحدة."))

soal("luas-dari-sudut", "mudah_sekali", "trimmed", "x1, y1 = r.randint(-50, 49), r.randint(-50, 49)\nprint(x1, y1)\nprint(r.randint(x1 + 1, 50), r.randint(y1 + 1, 50))\n",
     program(TITIK + r'''

class PersegiPanjang {
private:
    Titik kiriBawah;
    Titik kananAtas;

public:
    PersegiPanjang(Titik a, Titik b) : kiriBawah(a), kananAtas(b) {
    }

    int lebar() const {
        return kananAtas.x - kiriBawah.x;
    }

    int tinggi() const {
        return kananAtas.y - kiriBawah.y;
    }

    int luas() const {
        return lebar() * tinggi();
    }

    int keliling() const {
        return 2 * (lebar() + tinggi());
    }
};''', r'''    Titik a, b;
    cin >> a.x >> a.y;
    cin >> b.x >> b.y;
    PersegiPanjang p(a, b);
    cout << p.luas() << " " << p.keliling() << endl;'''), wajib=W,
     id=("Persegi Panjang dari Dua Sudut", "Sebuah persegi panjang yang sisinya sejajar sumbu koordinat cukup disimpan sebagai **dua titik**: sudut kiri bawah dan sudut kanan atas.\n\n"
         "Buat class `Titik` (atribut `int x`, `int y`) dan class `PersegiPanjang` dengan dua anggota `Titik`: `kiriBawah` dan `kananAtas`. Lebar, tinggi, luas, dan keliling tidak disimpan; semuanya dihitung dari kedua titik itu oleh method `lebar()`, `tinggi()`, `luas()`, `keliling()`.\n\n"
         "Baca kedua sudut, lalu cetak luas dan keliling.\n\nContoh: sudut (1, 1) dan (5, 4) → `12 14`." + W_ID,
         "Baris pertama: x dan y sudut kiri bawah. Baris kedua: x dan y sudut kanan atas.", "Satu baris: luas, lalu keliling.", "Koordinat antara −50 dan 50; sudut kanan atas benar-benar di kanan atas."),
     en=("A Rectangle from Two Corners", "A rectangle with sides parallel to the axes can be stored as just **two points**: its lower-left and its upper-right corner.\n\n"
         "Make a class `Titik` (attributes `int x`, `int y`) and a class `PersegiPanjang` with two `Titik` members: `kiriBawah` (lower left) and `kananAtas` (upper right). Width, height, area and perimeter are not stored; they are all computed from the two points by the methods `lebar()`, `tinggi()`, `luas()`, `keliling()`.\n\n"
         "Read the two corners, then print the area and the perimeter.\n\nExample: corners (1, 1) and (5, 4) → `12 14`." + W_EN,
         "First line: x and y of the lower-left corner. Second line: x and y of the upper-right corner.", "One line: the area, then the perimeter.", "Coordinates are between −50 and 50; the upper-right corner really lies up and to the right."),
     ar=("مستطيل من رأسين", "المستطيل الذي توازي أضلاعه المحورين يكفي أن يُخزَّن **نقطتين**: رأسه الأسفل الأيسر ورأسه الأعلى الأيمن.\n\n"
         "أنشئ صنفًا `Titik` (الخاصيتان `int x` و `int y`) وصنفًا `PersegiPanjang` فيه عضوان من `Titik`: `kiriBawah` (الأسفل الأيسر) و `kananAtas` (الأعلى الأيمن). العرض والارتفاع والمساحة والمحيط لا تُخزَّن؛ بل تُحسب كلها من النقطتين بالطرائق `lebar()` و `tinggi()` و `luas()` و `keliling()`.\n\n"
         "اقرأ الرأسين ثم اطبع المساحة والمحيط.\n\nمثال: الرأسان (1, 1) و (5, 4) ← `12 14`." + W_AR,
         "السطر الأول: x و y للرأس الأسفل الأيسر. السطر الثاني: x و y للرأس الأعلى الأيمن.", "سطر واحد: المساحة ثم المحيط.", "الإحداثيات بين −50 و 50؛ والرأس الأعلى الأيمن يقع فعلًا أعلى ويمينًا."))

soal("umur-orang", "mudah_sekali", "trimmed",
     NAMA + "import datetime\nlahir = datetime.date(1960, 1, 1) + datetime.timedelta(days=r.randint(0, 20000))\nkini = lahir + datetime.timedelta(days=r.randint(0, 20000))\nif i == 2:\n    kini = datetime.date(lahir.year + 20, lahir.month, min(lahir.day, 28))\n    lahir = datetime.date(lahir.year, lahir.month, min(lahir.day, 28))\nif i == 3:\n    lahir = datetime.date(2000, 6, 15)\n    kini = datetime.date(2026, 6, 14)\n"
     "print(r.choice(NAMA))\nprint(lahir.day, lahir.month, lahir.year)\nprint(kini.day, kini.month, kini.year)\n",
     program(r'''
class Tanggal {
public:
    int hari = 1;
    int bulan = 1;
    int tahun = 2000;

    bool sebelum(const Tanggal &lain) const {
        if (bulan != lain.bulan) {
            return bulan < lain.bulan;
        }
        return hari < lain.hari;
    }
};

class Orang {
private:
    string nama;
    Tanggal lahir;

public:
    Orang(const string &n, const Tanggal &l) : nama(n), lahir(l) {
    }

    string getNama() const {
        return nama;
    }

    int umurPada(const Tanggal &hariIni) const {
        auto umur = hariIni.tahun - lahir.tahun;
        if (hariIni.sebelum(lahir)) {
            umur--;
        }
        return umur;
    }
};''', r'''    string nama;
    Tanggal lahir, kini;
    cin >> nama;
    cin >> lahir.hari >> lahir.bulan >> lahir.tahun;
    cin >> kini.hari >> kini.bulan >> kini.tahun;
    Orang orang(nama, lahir);
    cout << orang.getNama() << " berumur " << orang.umurPada(kini) << " tahun" << endl;''', "string"), wajib=W,
     id=("Umur Seseorang", "Buat class `Tanggal` (atribut `int hari`, `int bulan`, `int tahun`) dan class `Orang` yang menyimpan `string nama` serta **tanggal lahir sebagai objek `Tanggal`**.\n\n"
         "Beri `Orang` method `int umurPada(const Tanggal &hariIni) const`: selisih tahun, **dikurangi satu bila ulang tahunnya pada tahun itu belum tiba**. Pembandingan \"bulan dan tanggal mana yang lebih dulu\" sebaiknya menjadi method `Tanggal` sendiri.\n\n"
         "Baca nama, tanggal lahir, dan tanggal hari ini (masing-masing hari, bulan, tahun). Cetak `<nama> berumur <umur> tahun`.\n\n"
         "Contoh: lahir 15 6 2000, hari ini 14 6 2026 → 25 tahun; hari ini 15 6 2026 → 26 tahun." + W_ID,
         "Baris pertama: nama. Baris kedua: tanggal lahir. Baris ketiga: tanggal hari ini.", "Satu baris: `<nama> berumur <umur> tahun`.", "Kedua tanggal sah; hari ini tidak lebih awal daripada tanggal lahir."),
     en=("A Person's Age", "Make a class `Tanggal` (date; attributes `int hari`, `int bulan`, `int tahun`) and a class `Orang` (person) that stores `string nama` and **the date of birth as a `Tanggal` object**.\n\n"
         "Give `Orang` a method `int umurPada(const Tanggal &hariIni) const` (age on): the difference in years, **less one when the birthday of that year has not come yet**. The comparison \"which month and day come first\" had better be a method of `Tanggal` itself.\n\n"
         "Read the name, the date of birth and today's date (each as day, month, year). Print `<name> berumur <age> tahun` (is … years old).\n\n"
         "Example: born 15 6 2000, today 14 6 2026 → 25 years; today 15 6 2026 → 26 years." + W_EN,
         "First line: the name. Second line: the date of birth. Third line: today's date.", "One line: `<name> berumur <age> tahun`.", "Both dates are valid; today is not earlier than the date of birth."),
     ar=("عمر شخص", "أنشئ صنفًا `Tanggal` (تاريخ؛ الخصائص `int hari` و `int bulan` و `int tahun`) وصنفًا `Orang` (شخص) يخزّن `string nama` و**تاريخ الميلاد كائنًا من `Tanggal`**.\n\n"
         "أعطِ `Orang` طريقة `int umurPada(const Tanggal &hariIni) const` (العمر في): فرق السنوات **ناقصًا واحدًا إذا لم يحلّ عيد الميلاد في تلك السنة بعد**. ويحسن أن تكون مقارنة «أي شهر ويوم أسبق» طريقة من `Tanggal` نفسه.\n\n"
         "اقرأ الاسم وتاريخ الميلاد وتاريخ اليوم (كلٌّ يوم ثم شهر ثم سنة). اطبع `<الاسم> berumur <العمر> tahun` (عمره … سنة).\n\n"
         "مثال: الميلاد 15 6 2000 واليوم 14 6 2026 ← 25 سنة؛ واليوم 15 6 2026 ← 26 سنة." + W_AR,
         "السطر الأول: الاسم. السطر الثاني: تاريخ الميلاد. السطر الثالث: تاريخ اليوم.", "سطر واحد: `<الاسم> berumur <العمر> tahun`.", "التاريخان صحيحان؛ واليوم ليس قبل تاريخ الميلاد."))

soal("dosen-pembimbing", "mudah_sekali", "trimmed", NAMA + "a, b = r.sample(NAMA, 2)\nprint(a)\nif i % 2 == 0:\n    print(1)\n    print(b)\nelse:\n    print(0)\n",
     program(r'''
class Dosen {
private:
    string nama;

public:
    Dosen(const string &n) : nama(n) {
    }

    string getNama() const {
        return nama;
    }
};

class Mahasiswa {
private:
    string nama;
    Dosen *pembimbing = nullptr;

public:
    Mahasiswa(const string &n) : nama(n) {
    }

    void setPembimbing(Dosen *d) {
        pembimbing = d;
    }

    void cetak() const {
        if (pembimbing == nullptr) {
            cout << nama << " belum punya pembimbing" << endl;
        } else {
            cout << nama << " dibimbing " << pembimbing->getNama() << endl;
        }
    }
};''', r'''    string nama;
    int k;
    cin >> nama >> k;
    Mahasiswa m(nama);
    Dosen dosen("-");
    if (k == 1) {
        string namaDosen;
        cin >> namaDosen;
        dosen = Dosen(namaDosen);
        m.setPembimbing(&dosen);
    }
    m.cetak();''', "string"), wajib=W,
     id=("Dosen Pembimbing", "Mahasiswa *mengenal* dosen pembimbingnya, tetapi tidak memilikinya, dan seorang mahasiswa baru mungkin **belum punya** pembimbing. Relasi \"kenal\" seperti ini disebut **asosiasi**; karena boleh tidak ada, kelipatannya 0..1 — di C++ sebuah penunjuk yang boleh bernilai `nullptr`.\n\n"
         "Buat class `Dosen` (atribut `string nama`, `getNama()`) dan class `Mahasiswa` dengan atribut `string nama` dan `Dosen *pembimbing` yang mula-mula `nullptr`, method `void setPembimbing(Dosen *d)`, dan `void cetak() const`:\n\n"
         "- bila ada pembimbing: `<nama> dibimbing <nama dosen>`;\n- bila tidak: `<nama> belum punya pembimbing`.\n\n"
         "Masukan: nama mahasiswa, lalu 0 atau 1. Bila 1, baris berikutnya berisi nama dosennya.\n\nContoh: `Andi`, `1`, `Sari` → `Andi dibimbing Sari`. `Andi`, `0` → `Andi belum punya pembimbing`." + W_ID,
         "Baris pertama: nama mahasiswa. Baris kedua: 0 atau 1. Bila 1, baris ketiga: nama dosen.", "Satu baris.", "Semua nama satu kata."),
     en=("Supervisor", "A student *knows* their supervisor but does not own them, and a new student may **not have** a supervisor yet. A \"knows\" relation like this is called **association**; as it may be absent its multiplicity is 0..1 — in C++ a pointer that may be `nullptr`.\n\n"
         "Make a class `Dosen` (lecturer; attribute `string nama`, `getNama()`) and a class `Mahasiswa` (student) with the attributes `string nama` and `Dosen *pembimbing` (supervisor), `nullptr` at first, a method `void setPembimbing(Dosen *d)`, and `void cetak() const`:\n\n"
         "- with a supervisor: `<name> dibimbing <lecturer's name>` (is supervised by);\n- without: `<name> belum punya pembimbing` (has no supervisor yet).\n\n"
         "Input: the student's name, then 0 or 1. If 1, the next line holds the lecturer's name.\n\nExamples: `Andi`, `1`, `Sari` → `Andi dibimbing Sari`. `Andi`, `0` → `Andi belum punya pembimbing`." + W_EN,
         "First line: the student's name. Second line: 0 or 1. If 1, third line: the lecturer's name.", "One line.", "All names are one word."),
     ar=("المشرف", "الطالب *يعرف* مشرفه لكنه لا يملكه، والطالب الجديد قد **لا يكون له** مشرف بعد. علاقة «يعرف» كهذه تسمى **الارتباط**؛ ولأنها قد تغيب فتعدّدها 0..1 — وفي ++C مؤشر يجوز أن يكون `nullptr`.\n\n"
         "أنشئ صنفًا `Dosen` (محاضر؛ الخاصية `string nama` و `getNama()`) وصنفًا `Mahasiswa` (طالب) خاصيتاه `string nama` و `Dosen *pembimbing` (المشرف) الذي يبدأ `nullptr`، وطريقة `void setPembimbing(Dosen *d)`، و `void cetak() const`:\n\n"
         "- مع مشرف: `<الاسم> dibimbing <اسم المحاضر>` (يشرف عليه)؛\n- بلا مشرف: `<الاسم> belum punya pembimbing` (لا مشرف له بعد).\n\n"
         "المدخل: اسم الطالب ثم 0 أو 1. إذا كان 1 ففي السطر التالي اسم المحاضر.\n\nأمثلة: `Andi` ثم `1` ثم `Sari` ← `Andi dibimbing Sari`. و `Andi` ثم `0` ← `Andi belum punya pembimbing`." + W_AR,
         "السطر الأول: اسم الطالب. السطر الثاني: 0 أو 1. إذا كان 1 فالسطر الثالث: اسم المحاضر.", "سطر واحد.", "كل الأسماء كلمة واحدة."))

soal("sepeda-roda", "mudah_sekali", "trimmed", "k = i % 4\nd = r.randint(30, 60) if k in (0, 1) else r.randint(5, 29)\nb = r.randint(30, 60) if k in (0, 2) else r.randint(5, 29)\nprint(d, b)\n",
     program(r'''
class Roda {
private:
    int tekanan;

public:
    Roda(int t) : tekanan(t) {
    }

    bool cukup() const {
        return tekanan >= 30;
    }
};

class Sepeda {
private:
    Roda depan;
    Roda belakang;

public:
    Sepeda(int d, int b) : depan(d), belakang(b) {
    }

    string status() const {
        if (depan.cukup() && belakang.cukup()) {
            return "siap jalan";
        }
        if (!depan.cukup() && !belakang.cukup()) {
            return "pompa kedua roda";
        }
        if (!depan.cukup()) {
            return "pompa roda depan";
        }
        return "pompa roda belakang";
    }
};''', r'''    int d, b;
    cin >> d >> b;
    Sepeda sepeda(d, b);
    cout << sepeda.status() << endl;''', "string"), wajib=W,
     id=("Sepeda dan Rodanya", "Buat class `Roda` dengan atribut `private` `int tekanan`, konstruktor `Roda(int t)`, dan method `bool cukup() const` — benar bila tekanannya 30 atau lebih.\n\n"
         "Buat class `Sepeda` yang **terdiri dari dua `Roda`**: `depan` dan `belakang`. Konstruktor `Sepeda(int d, int b)` meneruskan tekanan ke masing-masing roda lewat daftar inisialisasi. Method `string status() const` bertanya kepada kedua rodanya — sepeda tidak mengintip `tekanan` secara langsung:\n\n"
         "- kedua roda cukup: `siap jalan`;\n- hanya roda depan kurang: `pompa roda depan`;\n- hanya roda belakang kurang: `pompa roda belakang`;\n- keduanya kurang: `pompa kedua roda`.\n\n"
         "Contoh: `35 20` → `pompa roda belakang`." + W_ID,
         "Satu baris berisi tekanan roda depan dan roda belakang.", "Satu baris status.", "Tiap tekanan antara 5 dan 60."),
     en=("A Bicycle and its Wheels", "Make a class `Roda` (wheel) with a `private` attribute `int tekanan` (pressure), a constructor `Roda(int t)`, and a method `bool cukup() const` (enough) — true when the pressure is 30 or more.\n\n"
         "Make a class `Sepeda` (bicycle) that **consists of two `Roda`**: `depan` (front) and `belakang` (rear). The constructor `Sepeda(int d, int b)` passes the pressures on to the wheels through the initialiser list. The method `string status() const` asks its two wheels — the bicycle does not peek at `tekanan` directly:\n\n"
         "- both wheels fine: `siap jalan` (ready to go);\n- only the front low: `pompa roda depan` (pump the front wheel);\n- only the rear low: `pompa roda belakang`;\n- both low: `pompa kedua roda` (pump both wheels).\n\n"
         "Example: `35 20` → `pompa roda belakang`." + W_EN,
         "One line with the pressure of the front and of the rear wheel.", "One status line.", "Each pressure is between 5 and 60."),
     ar=("الدراجة وعجلتاها", "أنشئ صنفًا `Roda` (عجلة) فيه خاصية `private` هي `int tekanan` (الضغط)، وبانٍ `Roda(int t)`، وطريقة `bool cukup() const` (كافٍ) — صحيحة إذا كان الضغط 30 أو أكثر.\n\n"
         "أنشئ صنفًا `Sepeda` (دراجة) **يتكون من عجلتين `Roda`**: `depan` (الأمامية) و `belakang` (الخلفية). الباني `Sepeda(int d, int b)` يمرّر الضغطين إلى العجلتين عبر قائمة التهيئة. والطريقة `string status() const` تسأل عجلتيها — فالدراجة لا تطّلع على `tekanan` مباشرة:\n\n"
         "- العجلتان كافيتان: `siap jalan` (جاهزة للسير)؛\n- الأمامية فقط ناقصة: `pompa roda depan` (انفخ الأمامية)؛\n- الخلفية فقط ناقصة: `pompa roda belakang`؛\n- كلتاهما ناقصة: `pompa kedua roda` (انفخ العجلتين).\n\n"
         "مثال: `35 20` ← `pompa roda belakang`." + W_AR,
         "سطر واحد فيه ضغط العجلة الأمامية ثم الخلفية.", "سطر حالة واحد.", "كل ضغط بين 5 و 60."))

# ================================= MUDAH (8) =================================

soal("keranjang-belanja", "mudah", "trimmed", BENDA + "n = {2: 1}.get(i, r.randint(2, 10))\nprint(n)\nfor nama in r.sample(BENDA, n):\n    print(nama, r.randint(1, 50) * 500, r.randint(1, 9))\n",
     program(r'''
class Butir {
private:
    string nama;
    int harga = 0;
    int jumlah = 0;

public:
    void isi(const string &n, int h, int j) {
        nama = n;
        harga = h;
        jumlah = j;
    }

    string getNama() const {
        return nama;
    }

    int subtotal() const {
        return harga * jumlah;
    }
};

class Keranjang {
private:
    Butir isi[10];
    int banyak = 0;

public:
    void tambah(const string &n, int h, int j) {
        isi[banyak].isi(n, h, j);
        banyak++;
    }

    int total() const {
        auto t = 0;
        for (int k = 0; k < banyak; k++) {
            t += isi[k].subtotal();
        }
        return t;
    }

    void cetak() const {
        for (int k = 0; k < banyak; k++) {
            cout << isi[k].getNama() << ": " << isi[k].subtotal() << endl;
        }
        cout << "Total: " << total() << endl;
    }
};''', r'''    int n;
    cin >> n;
    Keranjang keranjang;
    for (int k = 0; k < n; k++) {
        string nama;
        int harga, jumlah;
        cin >> nama >> harga >> jumlah;
        keranjang.tambah(nama, harga, jumlah);
    }
    keranjang.cetak();''', "string"), wajib=W,
     id=("Keranjang Belanja", "Satu keseluruhan bisa memuat **banyak** bagian (kelipatan 1..*). Di sini bagian-bagian itu disimpan dalam array objek di dalam class.\n\n"
         "Buat class `Butir` (atribut `private` `string nama`, `int harga`, `int jumlah`; method `void isi(...)`, `string getNama() const`, `int subtotal() const`).\n\n"
         "Buat class `Keranjang` yang menyimpan `Butir isi[10]` dan `int banyak`, dengan method `void tambah(const string &n, int h, int j)`, `int total() const` (menjumlahkan `subtotal()` tiap butir), dan `void cetak() const`.\n\n"
         "Baca n barang (nama, harga satuan, jumlah). Cetak `<nama>: <subtotal>` untuk tiap barang, lalu `Total: T`.\n\n"
         "Contoh: `buku 12000 2` dan `pena 3500 4` →\n\n```\nbuku: 24000\npena: 14000\nTotal: 38000\n```" + W_ID,
         "Baris pertama: n. Lalu n baris berisi nama (satu kata), harga, dan jumlah.", "n baris subtotal, lalu `Total: T`.", "1 ≤ n ≤ 10; harga kelipatan 500 sampai 25000; 1 ≤ jumlah ≤ 9"),
     en=("Shopping Basket", "One whole can hold **many** parts (multiplicity 1..*). Here the parts are kept in an array of objects inside the class.\n\n"
         "Make a class `Butir` (item; `private` attributes `string nama`, `int harga` (price), `int jumlah` (quantity); methods `void isi(...)` (fill), `string getNama() const`, `int subtotal() const`).\n\n"
         "Make a class `Keranjang` (basket) that keeps `Butir isi[10]` and `int banyak`, with the methods `void tambah(const string &n, int h, int j)` (add), `int total() const` (which adds up `subtotal()` of every item), and `void cetak() const`.\n\n"
         "Read n goods (name, unit price, quantity). Print `<name>: <subtotal>` for each, then `Total: T`.\n\n"
         "Example: `buku 12000 2` and `pena 3500 4` →\n\n```\nbuku: 24000\npena: 14000\nTotal: 38000\n```" + W_EN,
         "First line: n. Then n lines with a name (one word), a price and a quantity.", "n subtotal lines, then `Total: T`.", "1 ≤ n ≤ 10; prices are multiples of 500 up to 25000; 1 ≤ quantity ≤ 9"),
     ar=("سلة التسوق", "يمكن أن يحوي الكل **أجزاء كثيرة** (التعدّد 1..*). هنا تُحفظ الأجزاء في مصفوفة كائنات داخل الصنف.\n\n"
         "أنشئ صنفًا `Butir` (بند؛ الخصائص الخاصة `string nama` و `int harga` (السعر) و `int jumlah` (الكمية)؛ والطرائق `void isi(...)` (املأ) و `string getNama() const` و `int subtotal() const`).\n\n"
         "أنشئ صنفًا `Keranjang` (سلة) يحفظ `Butir isi[10]` و `int banyak`، وطرائقه `void tambah(const string &n, int h, int j)` (أضف) و `int total() const` (تجمع `subtotal()` لكل بند) و `void cetak() const`.\n\n"
         "اقرأ n سلعة (الاسم وسعر الوحدة والكمية). اطبع `<الاسم>: <المجموع الفرعي>` لكل سلعة ثم `Total: T`.\n\n"
         "مثال: `buku 12000 2` و `pena 3500 4` ←\n\n```\nbuku: 24000\npena: 14000\nTotal: 38000\n```" + W_AR,
         "السطر الأول: n. ثم n سطرًا فيها اسم (كلمة واحدة) وسعر وكمية.", "n سطر مجموع فرعي ثم `Total: T`.", "1 ≤ n ≤ 10؛ الأسعار من مضاعفات 500 حتى 25000؛ 1 ≤ الكمية ≤ 9"))

soal("urutan-komponen", "mudah", "trimmed", "print(r.choice(['Kantor', 'Lab', 'Rumah', 'Server', 'Kasir', 'Studio']), r.choice(['i3', 'i5', 'i7', 'R5', 'R7', 'M2']), r.choice([4, 8, 16, 32, 64]))\n",
     program(r'''
class Prosesor {
private:
    string seri;

public:
    Prosesor(const string &s) : seri(s) {
        cout << "+Prosesor " << seri << endl;
    }

    ~Prosesor() {
        cout << "-Prosesor " << seri << endl;
    }
};

class Memori {
private:
    int gb;

public:
    Memori(int g) : gb(g) {
        cout << "+Memori " << gb << endl;
    }

    ~Memori() {
        cout << "-Memori " << gb << endl;
    }
};

class Komputer {
private:
    string nama;
    Prosesor cpu;
    Memori ram;

public:
    Komputer(const string &n, const string &seri, int gb) : nama(n), cpu(seri), ram(gb) {
        cout << "+Komputer " << nama << endl;
    }

    ~Komputer() {
        cout << "-Komputer " << nama << endl;
    }
};''', r'''    string nama, seri;
    int gb;
    cin >> nama >> seri >> gb;
    Komputer komputer(nama, seri, gb);
    cout << "Komputer menyala" << endl;''', "string"), wajib=W,
     id=("Urutan Komponen", "Bila sebuah objek punya beberapa objek anggota, anggota-anggota itu dibuat **menurut urutan deklarasinya di dalam class** — bukan menurut urutan di daftar inisialisasi — lalu badan konstruktor pemiliknya berjalan. Penghancurannya persis terbalik.\n\n"
         "Buat tiga class yang konstruktornya mencetak `+…` dan destruktornya mencetak `-…`:\n\n- `Prosesor` (atribut `string seri`): `+Prosesor <seri>` / `-Prosesor <seri>`;\n- `Memori` (atribut `int gb`): `+Memori <gb>` / `-Memori <gb>`;\n"
         "- `Komputer` dengan atribut, **dalam urutan ini**: `string nama`, `Prosesor cpu`, `Memori ram`: `+Komputer <nama>` / `-Komputer <nama>`.\n\n"
         "Baca nama, seri prosesor, dan besar memori. Buat satu `Komputer`, lalu cetak `Komputer menyala`.\n\n"
         "Untuk `Lab i5 16`:\n\n```\n+Prosesor i5\n+Memori 16\n+Komputer Lab\nKomputer menyala\n-Komputer Lab\n-Memori 16\n-Prosesor i5\n```" + W_ID,
         "Satu baris berisi nama komputer, seri prosesor, dan besar memori.", "Tujuh baris seperti contoh.", "Nama dan seri satu kata; memori antara 4 dan 64."),
     en=("Order of Components", "When an object has several member objects, they are created **in the order of their declaration inside the class** — not in the order of the initialiser list — and then the body of the owner's constructor runs. Destruction is exactly the reverse.\n\n"
         "Make three classes whose constructors print `+…` and whose destructors print `-…`:\n\n- `Prosesor` (attribute `string seri`): `+Prosesor <series>` / `-Prosesor <series>`;\n- `Memori` (attribute `int gb`): `+Memori <gb>` / `-Memori <gb>`;\n"
         "- `Komputer` with the attributes, **in this order**: `string nama`, `Prosesor cpu`, `Memori ram`: `+Komputer <name>` / `-Komputer <name>`.\n\n"
         "Read the name, the processor series and the amount of memory. Create one `Komputer`, then print `Komputer menyala` (the computer is on).\n\n"
         "For `Lab i5 16`:\n\n```\n+Prosesor i5\n+Memori 16\n+Komputer Lab\nKomputer menyala\n-Komputer Lab\n-Memori 16\n-Prosesor i5\n```" + W_EN,
         "One line with the computer's name, the processor series and the amount of memory.", "Seven lines as in the example.", "Name and series are one word; the memory is between 4 and 64."),
     ar=("ترتيب المكوّنات", "إذا كان للكائن عدة كائنات أعضاء فإنها تُنشأ **بترتيب التصريح بها داخل الصنف** — لا بترتيب قائمة التهيئة — ثم يعمل جسم باني المالك. والهدم بالعكس تمامًا.\n\n"
         "أنشئ ثلاثة أصناف تطبع بانياتها `+…` وهادماتها `-…`:\n\n- `Prosesor` (الخاصية `string seri`): `+Prosesor <الطراز>` / `-Prosesor <الطراز>`؛\n- `Memori` (الخاصية `int gb`): `+Memori <gb>` / `-Memori <gb>`؛\n"
         "- `Komputer` بالخصائص **بهذا الترتيب**: `string nama` ثم `Prosesor cpu` ثم `Memori ram`: `+Komputer <الاسم>` / `-Komputer <الاسم>`.\n\n"
         "اقرأ الاسم وطراز المعالج وحجم الذاكرة. أنشئ `Komputer` واحدًا ثم اطبع `Komputer menyala` (الحاسوب يعمل).\n\n"
         "عند `Lab i5 16`:\n\n```\n+Prosesor i5\n+Memori 16\n+Komputer Lab\nKomputer menyala\n-Komputer Lab\n-Memori 16\n-Prosesor i5\n```" + W_AR,
         "سطر واحد فيه اسم الحاسوب وطراز المعالج وحجم الذاكرة.", "سبعة أسطر كما في المثال.", "الاسم والطراز كلمة واحدة؛ والذاكرة بين 4 و 64."))

soal("regu-prajurit", "mudah", "trimmed",
     NAMA + "n = r.randint(2, 8)\nprint(n)\nfor nama in r.sample(NAMA, n):\n    print(nama, r.randint(20, 100))\nm = r.randint(1, n)\nprint(m)\nprint(*r.sample(range(1, n + 1), m))\nprint(r.randint(1, n), r.randint(5, 120))\n",
     program(r'''
class Prajurit {
private:
    string nama;
    int kesehatan = 0;

public:
    void isi(const string &n, int k) {
        nama = n;
        kesehatan = k;
    }

    void terluka(int besar) {
        kesehatan -= besar;
        if (kesehatan < 0) {
            kesehatan = 0;
        }
    }

    int getKesehatan() const {
        return kesehatan;
    }
};

class Regu {
private:
    Prajurit *anggota[10];
    int banyak = 0;

public:
    void gabung(Prajurit *p) {
        anggota[banyak] = p;
        banyak++;
    }

    int kekuatan() const {
        auto total = 0;
        for (int k = 0; k < banyak; k++) {
            total += anggota[k]->getKesehatan();
        }
        return total;
    }
};''', r'''    int n, m;
    cin >> n;
    Prajurit barak[10];
    for (int k = 0; k < n; k++) {
        string nama;
        int sehat;
        cin >> nama >> sehat;
        barak[k].isi(nama, sehat);
    }
    cin >> m;
    Regu regu;
    for (int k = 0; k < m; k++) {
        int nomor;
        cin >> nomor;
        regu.gabung(&barak[nomor - 1]);
    }
    int korban, besar;
    cin >> korban >> besar;
    cout << regu.kekuatan() << endl;
    barak[korban - 1].terluka(besar);
    cout << regu.kekuatan() << endl;''', "string"), wajib=W,
     id=("Regu dan Prajurit", "Sebuah regu **beranggotakan** prajurit, tetapi prajurit tidak dibuat oleh regu dan tidak ikut hilang bila regunya dibubarkan: mereka tercatat di barak. Itu **agregasi**: regu hanya menyimpan penunjuk ke prajurit-prajuritnya.\n\n"
         "Akibat pentingnya: regu tidak punya salinan. Bila seorang prajurit terluka di barak, kekuatan regunya ikut berubah.\n\n"
         "Buat class `Prajurit` (atribut `private` `string nama`, `int kesehatan`; method `isi`, `void terluka(int besar)` yang tidak membuat kesehatan negatif, `int getKesehatan() const`).\n\n"
         "Buat class `Regu` dengan `Prajurit *anggota[10]`, `int banyak`, method `void gabung(Prajurit *p)`, dan `int kekuatan() const` — jumlah kesehatan anggotanya.\n\n"
         "Masukan: n prajurit (nama dan kesehatan); lalu m dan m nomor prajurit yang menjadi anggota regu; lalu satu serangan: nomor prajurit dan besar lukanya. Cetak kekuatan regu sebelum serangan, lalu sesudahnya. Serangan mengenai prajurit **di barak** — yang mungkin bukan anggota regu.\n\n"
         "Contoh: prajurit `Andi 50`, `Budi 80`, `Citra 60`; anggota `1 3`; serangan `3 25` → `110` lalu `85`." + W_ID,
         "Baris pertama: n. Lalu n baris nama dan kesehatan. Lalu m; lalu m nomor prajurit (mulai dari 1). Baris terakhir: nomor prajurit yang terluka dan besar lukanya.", "Dua baris: kekuatan regu sebelum dan sesudah serangan.", "2 ≤ n ≤ 8; 1 ≤ m ≤ n; kesehatan antara 20 dan 100; luka antara 5 dan 120"),
     en=("Squad and Soldiers", "A squad **has** soldiers as members, but the soldiers are not created by the squad and do not vanish when it is disbanded: they are on the roll of the barracks. That is **aggregation**: the squad keeps only pointers to its soldiers.\n\n"
         "An important consequence: the squad has no copies. When a soldier is wounded in the barracks, the squad's strength changes with it.\n\n"
         "Make a class `Prajurit` (soldier; `private` attributes `string nama`, `int kesehatan` (health); methods `isi`, `void terluka(int besar)` (wounded), which never makes the health negative, `int getKesehatan() const`).\n\n"
         "Make a class `Regu` (squad) with `Prajurit *anggota[10]` (members), `int banyak`, a method `void gabung(Prajurit *p)` (join), and `int kekuatan() const` (strength) — the summed health of its members.\n\n"
         "Input: n soldiers (name and health); then m and the m numbers of the soldiers in the squad; then one hit: a soldier's number and the size of the wound. Print the squad's strength before the hit, then after it. The hit lands on a soldier **in the barracks** — who may not be in the squad.\n\n"
         "Example: soldiers `Andi 50`, `Budi 80`, `Citra 60`; members `1 3`; hit `3 25` → `110` then `85`." + W_EN,
         "First line: n. Then n lines of name and health. Then m; then m soldier numbers (from 1). Last line: the number of the wounded soldier and the size of the wound.", "Two lines: the squad's strength before and after the hit.", "2 ≤ n ≤ 8; 1 ≤ m ≤ n; health is between 20 and 100; wounds between 5 and 120"),
     ar=("المجموعة والجنود", "المجموعة **أعضاؤها** جنود، لكن الجنود لا تنشئهم المجموعة ولا يزولون إذا حُلّت: هم مسجَّلون في الثكنة. هذا **تجميع**: المجموعة تحفظ مؤشرات إلى جنودها فقط.\n\n"
         "ونتيجة مهمة: ليس عند المجموعة نسخ. إذا جُرح جندي في الثكنة تغيّرت قوة مجموعته معه.\n\n"
         "أنشئ صنفًا `Prajurit` (جندي؛ الخاصيتان الخاصتان `string nama` و `int kesehatan` (الصحة)؛ والطرائق `isi` و `void terluka(int besar)` (جُرح) التي لا تجعل الصحة سالبة و `int getKesehatan() const`).\n\n"
         "أنشئ صنفًا `Regu` (مجموعة) فيه `Prajurit *anggota[10]` (الأعضاء) و `int banyak`، وطريقة `void gabung(Prajurit *p)` (انضمّ)، و `int kekuatan() const` (القوة) — مجموع صحة أعضائها.\n\n"
         "المدخل: n جنديًا (الاسم والصحة)؛ ثم m وأرقام الجنود m الأعضاء في المجموعة؛ ثم ضربة واحدة: رقم جندي ومقدار الجرح. اطبع قوة المجموعة قبل الضربة ثم بعدها. الضربة تصيب جنديًا **في الثكنة** — وقد لا يكون عضوًا في المجموعة.\n\n"
         "مثال: الجنود `Andi 50` و `Budi 80` و `Citra 60`؛ الأعضاء `1 3`؛ الضربة `3 25` ← `110` ثم `85`." + W_AR,
         "السطر الأول: n. ثم n سطرًا فيها الاسم والصحة. ثم m؛ ثم m رقم جندي (من 1). السطر الأخير: رقم الجندي المجروح ومقدار الجرح.", "سطران: قوة المجموعة قبل الضربة وبعدها.", "2 ≤ n ≤ 8؛ 1 ≤ m ≤ n؛ الصحة بين 20 و 100؛ الجرح بين 5 و 120"))

soal("salinan-atau-rujukan", "mudah", "trimmed", NAMA + "a, b = r.sample(NAMA, 2)\nprint(r.choice(['Algoritma', 'Kalkulus', 'Fisika', 'Sejarah', 'Statistika', 'Biologi']), a, b)\n",
     program(r'''
class Penulis {
private:
    string nama;

public:
    Penulis(const string &n) : nama(n) {
    }

    void gantiNama(const string &n) {
        nama = n;
    }

    string getNama() const {
        return nama;
    }
};

class BukuSalin {
private:
    string judul;
    Penulis penulis;

public:
    BukuSalin(const string &j, const Penulis &p) : judul(j), penulis(p) {
    }

    void cetak() const {
        cout << "Salinan: " << judul << " oleh " << penulis.getNama() << endl;
    }
};

class BukuRujuk {
private:
    string judul;
    Penulis *penulis;

public:
    BukuRujuk(const string &j, Penulis *p) : judul(j), penulis(p) {
    }

    void cetak() const {
        cout << "Rujukan: " << judul << " oleh " << penulis->getNama() << endl;
    }
};''', r'''    string judul, lama, baru;
    cin >> judul >> lama >> baru;
    Penulis penulis(lama);
    BukuSalin a(judul, penulis);
    BukuRujuk b(judul, &penulis);
    penulis.gantiNama(baru);
    a.cetak();
    b.cetak();''', "string"), wajib=W,
     id=("Salinan atau Rujukan", "Menyimpan objek lain **sebagai anggota langsung** berarti menyimpan *salinannya*. Menyimpan **penunjuk** berarti merujuk ke objek aslinya. Bedanya terlihat begitu objek asli berubah.\n\n"
         "Buat class `Penulis` (atribut `string nama`; `gantiNama`, `getNama`). Lalu buat dua class buku:\n\n"
         "- `BukuSalin` — atribut `string judul` dan `Penulis penulis` (objek); `cetak()` mencetak `Salinan: <judul> oleh <nama>`;\n- `BukuRujuk` — atribut `string judul` dan `Penulis *penulis` (penunjuk); `cetak()` mencetak `Rujukan: <judul> oleh <nama>`.\n\n"
         "Baca judul, nama lama, dan nama baru. Buat seorang `Penulis` dengan nama lama; buat satu `BukuSalin` dan satu `BukuRujuk` dari penulis itu; **setelah itu** ganti nama penulis menjadi nama baru; lalu cetak kedua buku.\n\n"
         "Untuk `Fisika Budi Citra`:\n\n```\nSalinan: Fisika oleh Budi\nRujukan: Fisika oleh Citra\n```" + W_ID,
         "Satu baris berisi judul, nama lama, dan nama baru.", "Dua baris seperti contoh.", "Semuanya satu kata; nama lama dan nama baru berbeda."),
     en=("Copy or Reference", "Keeping another object **as a direct member** means keeping *a copy of it*. Keeping a **pointer** means referring to the original. The difference shows as soon as the original changes.\n\n"
         "Make a class `Penulis` (author; attribute `string nama`; `gantiNama` (rename), `getNama`). Then make two book classes:\n\n"
         "- `BukuSalin` — attributes `string judul` (title) and `Penulis penulis` (an object); `cetak()` prints `Salinan: <title> oleh <name>` (copy … by);\n- `BukuRujuk` — attributes `string judul` and `Penulis *penulis` (a pointer); `cetak()` prints `Rujukan: <title> oleh <name>` (reference … by).\n\n"
         "Read a title, an old name and a new name. Create a `Penulis` with the old name; create one `BukuSalin` and one `BukuRujuk` from that author; **after that** rename the author to the new name; then print both books.\n\n"
         "For `Fisika Budi Citra`:\n\n```\nSalinan: Fisika oleh Budi\nRujukan: Fisika oleh Citra\n```" + W_EN,
         "One line with the title, the old name and the new name.", "Two lines as in the example.", "All are one word; the old and new names differ."),
     ar=("نسخة أم مرجع", "حفظ كائن آخر **عضوًا مباشرًا** يعني حفظ *نسخة منه*. وحفظ **مؤشر** يعني الإشارة إلى الأصل. ويظهر الفرق حالما يتغير الأصل.\n\n"
         "أنشئ صنفًا `Penulis` (مؤلف؛ الخاصية `string nama`؛ و `gantiNama` (غيِّر الاسم) و `getNama`). ثم أنشئ صنفي كتاب:\n\n"
         "- `BukuSalin` — الخاصيتان `string judul` (العنوان) و `Penulis penulis` (كائن)؛ و `cetak()` تطبع `Salinan: <العنوان> oleh <الاسم>` (نسخة … تأليف)؛\n- `BukuRujuk` — الخاصيتان `string judul` و `Penulis *penulis` (مؤشر)؛ و `cetak()` تطبع `Rujukan: <العنوان> oleh <الاسم>` (مرجع … تأليف).\n\n"
         "اقرأ عنوانًا واسمًا قديمًا واسمًا جديدًا. أنشئ `Penulis` بالاسم القديم؛ وأنشئ `BukuSalin` و `BukuRujuk` من ذلك المؤلف؛ **وبعد ذلك** غيِّر اسم المؤلف إلى الجديد؛ ثم اطبع الكتابين.\n\n"
         "عند `Fisika Budi Citra`:\n\n```\nSalinan: Fisika oleh Budi\nRujukan: Fisika oleh Citra\n```" + W_AR,
         "سطر واحد فيه العنوان والاسم القديم والاسم الجديد.", "سطران كما في المثال.", "كلها كلمة واحدة؛ والاسمان القديم والجديد مختلفان."))

soal("daftar-putar", "mudah", "trimmed",
     "JUDUL = ['Pagi', 'Senja', 'Hujan', 'Laut', 'Rindu', 'Kota', 'Langit', 'Jalan', 'Rumah', 'Bintang', 'Angin', 'Bunga']\nn = {2: 1}.get(i, r.randint(2, 12))\nprint(n)\nfor j in r.sample(JUDUL, n):\n    print(j, r.randint(30, 420) if i != 3 else 200)\n",
     program(r'''
class Lagu {
private:
    string judul;
    int detik = 0;

public:
    void isi(const string &j, int d) {
        judul = j;
        detik = d;
    }

    string getJudul() const {
        return judul;
    }

    int getDetik() const {
        return detik;
    }
};

class DaftarPutar {
private:
    Lagu lagu[20];
    int banyak = 0;

public:
    void tambah(const string &j, int d) {
        lagu[banyak].isi(j, d);
        banyak++;
    }

    int totalDetik() const {
        auto total = 0;
        for (int k = 0; k < banyak; k++) {
            total += lagu[k].getDetik();
        }
        return total;
    }

    string terpanjang() const {
        auto p = 0;
        for (int k = 1; k < banyak; k++) {
            if (lagu[k].getDetik() > lagu[p].getDetik()) {
                p = k;
            }
        }
        return lagu[p].getJudul();
    }
};''', r'''    int n;
    cin >> n;
    DaftarPutar daftar;
    for (int k = 0; k < n; k++) {
        string judul;
        int detik;
        cin >> judul >> detik;
        daftar.tambah(judul, detik);
    }
    auto total = daftar.totalDetik();
    cout << "Total: " << total / 60 << ":" << setfill('0') << setw(2) << total % 60 << endl;
    cout << "Terpanjang: " << daftar.terpanjang() << endl;''', "iomanip", "string"), wajib=W,
     id=("Daftar Putar", "Buat class `Lagu` (atribut `private` `string judul`, `int detik`) dan class `DaftarPutar` yang menyimpan `Lagu lagu[20]` dan `int banyak`, dengan method:\n\n"
         "- `void tambah(const string &j, int d)`;\n- `int totalDetik() const`;\n- `string terpanjang() const` — judul lagu terpanjang; bila ada yang sama panjang, yang lebih dulu ditambahkan.\n\n"
         "Baca n lagu (judul dan durasi dalam detik). Cetak `Total: M:DD` (menit apa adanya, detik dua digit) dan `Terpanjang: <judul>`.\n\n"
         "Contoh: `Pagi 200`, `Senja 245`, `Hujan 185` →\n\n```\nTotal: 10:30\nTerpanjang: Senja\n```" + W_ID,
         "Baris pertama: n. Lalu n baris berisi judul (satu kata) dan durasi dalam detik.", "Dua baris seperti contoh.", "1 ≤ n ≤ 12; 30 ≤ durasi ≤ 420"),
     en=("Playlist", "Make a class `Lagu` (song; `private` attributes `string judul` (title), `int detik` (seconds)) and a class `DaftarPutar` (playlist) that keeps `Lagu lagu[20]` and `int banyak`, with the methods:\n\n"
         "- `void tambah(const string &j, int d)`;\n- `int totalDetik() const`;\n- `string terpanjang() const` — the title of the longest song; if several are equally long, the one added first.\n\n"
         "Read n songs (title and duration in seconds). Print `Total: M:SS` (minutes as they are, seconds with two digits) and `Terpanjang: <title>` (longest).\n\n"
         "Example: `Pagi 200`, `Senja 245`, `Hujan 185` →\n\n```\nTotal: 10:30\nTerpanjang: Senja\n```" + W_EN,
         "First line: n. Then n lines with a title (one word) and a duration in seconds.", "Two lines as in the example.", "1 ≤ n ≤ 12; 30 ≤ duration ≤ 420"),
     ar=("قائمة التشغيل", "أنشئ صنفًا `Lagu` (أغنية؛ الخاصيتان الخاصتان `string judul` (العنوان) و `int detik` (الثواني)) وصنفًا `DaftarPutar` (قائمة تشغيل) يحفظ `Lagu lagu[20]` و `int banyak`، وطرائقه:\n\n"
         "- `void tambah(const string &j, int d)`؛\n- `int totalDetik() const`؛\n- `string terpanjang() const` — عنوان أطول أغنية؛ وإذا تساوت عدة أغانٍ فالتي أُضيفت أولًا.\n\n"
         "اقرأ n أغنية (العنوان والمدة بالثواني). اطبع `Total: M:SS` (الدقائق كما هي والثواني برقمين) و `Terpanjang: <العنوان>` (الأطول).\n\n"
         "مثال: `Pagi 200` و `Senja 245` و `Hujan 185` ←\n\n```\nTotal: 10:30\nTerpanjang: Senja\n```" + W_AR,
         "السطر الأول: n. ثم n سطرًا فيها عنوان (كلمة واحدة) ومدة بالثواني.", "سطران كما في المثال.", "1 ≤ n ≤ 12؛ 30 ≤ المدة ≤ 420"))

soal("segitiga-titik", "mudah", "trimmed",
     "p = [(r.randint(-20, 20), r.randint(-20, 20)) for _ in range(3)]\nif i % 3 == 2:\n    a, b = r.randint(-5, 5), r.randint(-5, 5)\n    p = [(a * t, b * t + 3) for t in r.sample(range(-4, 5), 3)]\nfor x, y in p:\n    print(x, y)\n",
     program(TITIK + r'''

class Segitiga {
private:
    Titik sudut[3];

public:
    Segitiga(Titik a, Titik b, Titik c) {
        sudut[0] = a;
        sudut[1] = b;
        sudut[2] = c;
    }

    int luasGanda() const {
        auto hasil = sudut[0].x * (sudut[1].y - sudut[2].y)
                   + sudut[1].x * (sudut[2].y - sudut[0].y)
                   + sudut[2].x * (sudut[0].y - sudut[1].y);
        if (hasil < 0) {
            hasil = -hasil;
        }
        return hasil;
    }
};''', r'''    Titik a, b, c;
    cin >> a.x >> a.y;
    cin >> b.x >> b.y;
    cin >> c.x >> c.y;
    Segitiga s(a, b, c);
    if (s.luasGanda() == 0) {
        cout << "Ketiga titik segaris" << endl;
    } else {
        cout << "Luas x 2 = " << s.luasGanda() << endl;
    }'''), wajib=W,
     id=("Segitiga dari Tiga Titik", "Buat class `Titik` (atribut `int x`, `int y`) dan class `Segitiga` yang menyimpan ketiga sudutnya sebagai `Titik sudut[3]`.\n\n"
         "Dua kali luas segitiga dengan sudut (x₁, y₁), (x₂, y₂), (x₃, y₃) adalah nilai mutlak dari\n\n**x₁(y₂ − y₃) + x₂(y₃ − y₁) + x₃(y₁ − y₂)**\n\nBilangan itu selalu bulat, jadi tidak perlu pecahan. Beri `Segitiga` method `int luasGanda() const`.\n\n"
         "Baca tiga titik. Bila hasilnya 0, ketiganya terletak pada satu garis: cetak `Ketiga titik segaris`. Selain itu cetak `Luas x 2 = <nilai>`.\n\n"
         "Contoh: (0, 0), (4, 0), (0, 3) → `Luas x 2 = 12`. (0, 0), (2, 2), (5, 5) → `Ketiga titik segaris`." + W_ID,
         "Tiga baris, masing-masing berisi x dan y sebuah titik.", "Satu baris.", "Semua koordinat antara −25 dan 25."),
     en=("A Triangle from Three Points", "Make a class `Titik` (attributes `int x`, `int y`) and a class `Segitiga` (triangle) that stores its three corners as `Titik sudut[3]`.\n\n"
         "Twice the area of a triangle with corners (x₁, y₁), (x₂, y₂), (x₃, y₃) is the absolute value of\n\n**x₁(y₂ − y₃) + x₂(y₃ − y₁) + x₃(y₁ − y₂)**\n\nThat number is always an integer, so no fractions are needed. Give `Segitiga` a method `int luasGanda() const` (double area).\n\n"
         "Read three points. If the result is 0 the three lie on one line: print `Ketiga titik segaris` (the three points are collinear). Otherwise print `Luas x 2 = <value>`.\n\n"
         "Examples: (0, 0), (4, 0), (0, 3) → `Luas x 2 = 12`. (0, 0), (2, 2), (5, 5) → `Ketiga titik segaris`." + W_EN,
         "Three lines, each with the x and y of a point.", "One line.", "All coordinates are between −25 and 25."),
     ar=("مثلث من ثلاث نقاط", "أنشئ صنفًا `Titik` (الخاصيتان `int x` و `int y`) وصنفًا `Segitiga` (مثلث) يخزّن رؤوسه الثلاثة على صورة `Titik sudut[3]`.\n\n"
         "ضعف مساحة المثلث الذي رؤوسه (x₁, y₁) و (x₂, y₂) و (x₃, y₃) هو القيمة المطلقة للمقدار\n\n**x₁(y₂ − y₃) + x₂(y₃ − y₁) + x₃(y₁ − y₂)**\n\nوهو عدد صحيح دائمًا، فلا حاجة إلى الكسور. أعطِ `Segitiga` طريقة `int luasGanda() const` (ضعف المساحة).\n\n"
         "اقرأ ثلاث نقاط. إذا كان الناتج 0 فالثلاث على استقامة واحدة: اطبع `Ketiga titik segaris` (النقاط الثلاث على خط واحد). وإلا فاطبع `Luas x 2 = <القيمة>`.\n\n"
         "أمثلة: (0, 0) و (4, 0) و (0, 3) ← `Luas x 2 = 12`. و (0, 0) و (2, 2) و (5, 5) ← `Ketiga titik segaris`." + W_AR,
         "ثلاثة أسطر في كل منها x و y لنقطة.", "سطر واحد.", "كل الإحداثيات بين −25 و 25."))

soal("transkrip-ipk", "mudah", "trimmed",
     "while True:\n    n = {2: 1}.get(i, r.randint(2, 10))\n    data = [(r.choice([1, 2, 3, 4]), r.choice('AABBBCCDE')) for _ in range(n)]\n    sks = sum(s for s, _ in data)\n    mutu = sum(s * 'EDCBA'.index(h) for s, h in data)\n    if abs(mutu * 100 / sks % 1 - 0.5) > 1e-3:\n        break\nprint(n)\nfor k, (s, h) in enumerate(data):\n    print('MK' + str(101 + k), s, h)\n",
     program(r'''
class MataKuliah {
private:
    string kode;
    int sks = 0;
    char huruf = 'E';

public:
    void isi(const string &k, int s, char h) {
        kode = k;
        sks = s;
        huruf = h;
    }

    int getSks() const {
        return sks;
    }

    int mutu() const {
        switch (huruf) {
            case 'A': return 4 * sks;
            case 'B': return 3 * sks;
            case 'C': return 2 * sks;
            case 'D': return sks;
            default: return 0;
        }
    }
};

class Transkrip {
private:
    MataKuliah mk[10];
    int banyak = 0;

public:
    void tambah(const string &k, int s, char h) {
        mk[banyak].isi(k, s, h);
        banyak++;
    }

    int totalSks() const {
        auto total = 0;
        for (int k = 0; k < banyak; k++) {
            total += mk[k].getSks();
        }
        return total;
    }

    double ipk() const {
        auto mutu = 0;
        for (int k = 0; k < banyak; k++) {
            mutu += mk[k].mutu();
        }
        return (double) mutu / totalSks();
    }
};''', r'''    int n;
    cin >> n;
    Transkrip t;
    for (int k = 0; k < n; k++) {
        string kode;
        int sks;
        char huruf;
        cin >> kode >> sks >> huruf;
        t.tambah(kode, sks, huruf);
    }
    cout << "SKS: " << t.totalSks() << endl;
    cout << "IPK: " << fixed << setprecision(2) << t.ipk() << endl;''', "iomanip", "string"), wajib=W,
     id=("Transkrip dan IPK", "Buat class `MataKuliah` (atribut `private` `string kode`, `int sks`, `char huruf`) dengan method `int getSks() const` dan `int mutu() const` — sks dikali bobot hurufnya: A = 4, B = 3, C = 2, D = 1, E = 0.\n\n"
         "Buat class `Transkrip` yang menyimpan `MataKuliah mk[10]` dan `int banyak`, dengan `void tambah(...)`, `int totalSks() const`, dan `double ipk() const` — jumlah mutu dibagi jumlah sks. `Transkrip` tidak perlu tahu bobot huruf; ia cukup bertanya kepada tiap `MataKuliah`.\n\n"
         "Baca n mata kuliah (kode, sks, nilai huruf). Cetak `SKS: s` dan `IPK: x.xx`.\n\n"
         "Contoh: `MK101 3 A`, `MK102 2 C`, `MK103 4 B` →\n\n```\nSKS: 9\nIPK: 3.11\n```" + W_ID,
         "Baris pertama: n. Lalu n baris berisi kode, sks, dan nilai huruf.", "Dua baris: `SKS: s` dan `IPK: x.xx`.", "1 ≤ n ≤ 10; sks antara 1 dan 4; huruf `A` sampai `E`"),
     en=("Transcript and Grade Point Average", "Make a class `MataKuliah` (course; `private` attributes `string kode`, `int sks` (credits), `char huruf` (letter grade)) with the methods `int getSks() const` and `int mutu() const` (grade points) — the credits times the weight of the letter: A = 4, B = 3, C = 2, D = 1, E = 0.\n\n"
         "Make a class `Transkrip` that keeps `MataKuliah mk[10]` and `int banyak`, with `void tambah(...)`, `int totalSks() const`, and `double ipk() const` — total grade points divided by total credits. `Transkrip` need not know the letter weights; it just asks each `MataKuliah`.\n\n"
         "Read n courses (code, credits, letter grade). Print `SKS: s` and `IPK: x.xx`.\n\n"
         "Example: `MK101 3 A`, `MK102 2 C`, `MK103 4 B` →\n\n```\nSKS: 9\nIPK: 3.11\n```" + W_EN,
         "First line: n. Then n lines with a code, the credits and a letter grade.", "Two lines: `SKS: s` and `IPK: x.xx`.", "1 ≤ n ≤ 10; credits are between 1 and 4; letters `A` to `E`"),
     ar=("كشف الدرجات والمعدل", "أنشئ صنفًا `MataKuliah` (مقرر؛ الخصائص الخاصة `string kode` و `int sks` (الساعات المعتمدة) و `char huruf` (الدرجة بالحرف)) وطريقتاه `int getSks() const` و `int mutu() const` (نقاط الجودة) — الساعات مضروبة في وزن الحرف: A = 4، B = 3، C = 2، D = 1، E = 0.\n\n"
         "أنشئ صنفًا `Transkrip` (كشف) يحفظ `MataKuliah mk[10]` و `int banyak`، وله `void tambah(...)` و `int totalSks() const` و `double ipk() const` — مجموع النقاط مقسومًا على مجموع الساعات. لا يلزم `Transkrip` أن يعرف أوزان الحروف؛ يكفيه أن يسأل كل `MataKuliah`.\n\n"
         "اقرأ n مقررًا (الرمز والساعات والدرجة بالحرف). اطبع `SKS: s` و `IPK: x.xx`.\n\n"
         "مثال: `MK101 3 A` و `MK102 2 C` و `MK103 4 B` ←\n\n```\nSKS: 9\nIPK: 3.11\n```" + W_AR,
         "السطر الأول: n. ثم n سطرًا فيها الرمز والساعات والدرجة بالحرف.", "سطران: `SKS: s` و `IPK: x.xx`.", "1 ≤ n ≤ 10؛ الساعات بين 1 و 4؛ الحروف من `A` إلى `E`"))

soal("kamar-hotel", "mudah", "trimmed",
     NAMA + "tamu = r.sample(NAMA, 10)\nk = 0\nfor _ in range(r.randint(1, 15)):\n    if r.random() < 0.6 and k < 10:\n        print('masuk', r.randint(1, 5), tamu[k])\n        k += 1\n    else:\n        print('keluar', r.randint(1, 5))\nprint('selesai')\n",
     program(r'''
class Tamu {
private:
    string nama;

public:
    void setNama(const string &n) {
        nama = n;
    }

    string getNama() const {
        return nama;
    }
};

class Kamar {
private:
    Tamu *penghuni = nullptr;

public:
    bool masuk(Tamu *t) {
        if (penghuni != nullptr) {
            return false;
        }
        penghuni = t;
        return true;
    }

    bool keluar() {
        if (penghuni == nullptr) {
            return false;
        }
        penghuni = nullptr;
        return true;
    }

    string namaPenghuni() const {
        if (penghuni == nullptr) {
            return "-";
        }
        return penghuni->getNama();
    }
};''', r'''    Kamar kamar[5];
    Tamu tamu[15];
    auto banyakTamu = 0;
    string perintah;
    while (cin >> perintah) {
        if (perintah == "selesai") {
            break;
        }
        int nomor;
        cin >> nomor;
        if (perintah == "masuk") {
            string nama;
            cin >> nama;
            tamu[banyakTamu].setNama(nama);
            if (kamar[nomor - 1].masuk(&tamu[banyakTamu])) {
                cout << "Kamar " << nomor << ": " << nama << endl;
            } else {
                cout << "Kamar " << nomor << " sudah terisi" << endl;
            }
            banyakTamu++;
        } else if (kamar[nomor - 1].keluar()) {
            cout << "Kamar " << nomor << " dikosongkan" << endl;
        } else {
            cout << "Kamar " << nomor << " memang kosong" << endl;
        }
    }
    for (int k = 0; k < 5; k++) {
        cout << k + 1 << ": " << kamar[k].namaPenghuni() << endl;
    }''', "string"), wajib=W,
     id=("Kamar Hotel", "Sebuah penginapan punya 5 kamar. Tiap kamar ditempati **paling banyak satu tamu** — asosiasi berkelipatan 0..1, yang bisa berubah dari waktu ke waktu.\n\n"
         "Buat class `Tamu` (atribut `string nama`) dan class `Kamar` dengan atribut `private` `Tamu *penghuni` (mula-mula `nullptr`) dan method:\n\n"
         "- `bool masuk(Tamu *t)` — gagal bila kamar sudah berpenghuni;\n- `bool keluar()` — gagal bila kamar kosong;\n- `string namaPenghuni() const` — nama penghuninya, atau `-`.\n\n"
         "Perintah:\n\n- `masuk <kamar> <nama>` → `Kamar <k>: <nama>`, atau `Kamar <k> sudah terisi`;\n- `keluar <kamar>` → `Kamar <k> dikosongkan`, atau `Kamar <k> memang kosong`;\n- `selesai` → cetak lima baris `<k>: <nama atau ->`.\n\n"
         "Contoh: `masuk 2 Andi`, `masuk 2 Budi`, `keluar 4`, `keluar 2`, `masuk 5 Citra`, `selesai` →\n\n```\nKamar 2: Andi\nKamar 2 sudah terisi\nKamar 4 memang kosong\nKamar 2 dikosongkan\nKamar 5: Citra\n1: -\n2: -\n3: -\n4: -\n5: Citra\n```" + W_ID,
         "Beberapa baris perintah, diakhiri `selesai`.", "Satu baris per perintah, lalu lima baris keadaan kamar.", "Paling banyak 15 perintah; nomor kamar 1 sampai 5; nama satu kata."),
     en=("Hotel Rooms", "A guest house has 5 rooms. Each room is occupied by **at most one guest** — an association of multiplicity 0..1 that can change over time.\n\n"
         "Make a class `Tamu` (guest; attribute `string nama`) and a class `Kamar` (room) with a `private` attribute `Tamu *penghuni` (occupant, `nullptr` at first) and the methods:\n\n"
         "- `bool masuk(Tamu *t)` (check in) — fails when the room is occupied;\n- `bool keluar()` (check out) — fails when the room is empty;\n- `string namaPenghuni() const` — the occupant's name, or `-`.\n\n"
         "Commands:\n\n- `masuk <room> <name>` → `Kamar <k>: <name>`, or `Kamar <k> sudah terisi` (already occupied);\n- `keluar <room>` → `Kamar <k> dikosongkan` (vacated), or `Kamar <k> memang kosong` (was empty anyway);\n- `selesai` → print five lines `<k>: <name or ->`.\n\n"
         "Example: `masuk 2 Andi`, `masuk 2 Budi`, `keluar 4`, `keluar 2`, `masuk 5 Citra`, `selesai` →\n\n```\nKamar 2: Andi\nKamar 2 sudah terisi\nKamar 4 memang kosong\nKamar 2 dikosongkan\nKamar 5: Citra\n1: -\n2: -\n3: -\n4: -\n5: Citra\n```" + W_EN,
         "Several command lines, ending with `selesai`.", "One line per command, then five lines with the state of the rooms.", "At most 15 commands; room numbers 1 to 5; names are one word."),
     ar=("غرف الفندق", "في نُزُل 5 غرف. يسكن كل غرفة **نزيل واحد على الأكثر** — ارتباط تعدّده 0..1 قد يتغير مع الوقت.\n\n"
         "أنشئ صنفًا `Tamu` (نزيل؛ الخاصية `string nama`) وصنفًا `Kamar` (غرفة) فيه خاصية `private` هي `Tamu *penghuni` (الساكن، ويبدأ `nullptr`) وطرائقه:\n\n"
         "- `bool masuk(Tamu *t)` (دخول) — تفشل إذا كانت الغرفة مسكونة؛\n- `bool keluar()` (خروج) — تفشل إذا كانت الغرفة فارغة؛\n- `string namaPenghuni() const` — اسم الساكن، أو `-`.\n\n"
         "الأوامر:\n\n- `masuk <الغرفة> <الاسم>` ← `Kamar <k>: <الاسم>` أو `Kamar <k> sudah terisi` (مشغولة)؛\n- `keluar <الغرفة>` ← `Kamar <k> dikosongkan` (أُخليت) أو `Kamar <k> memang kosong` (فارغة أصلًا)؛\n- `selesai` ← اطبع خمسة أسطر `<k>: <الاسم أو ->`.\n\n"
         "مثال: `masuk 2 Andi` ثم `masuk 2 Budi` ثم `keluar 4` ثم `keluar 2` ثم `masuk 5 Citra` ثم `selesai` ←\n\n```\nKamar 2: Andi\nKamar 2 sudah terisi\nKamar 4 memang kosong\nKamar 2 dikosongkan\nKamar 5: Citra\n1: -\n2: -\n3: -\n4: -\n5: Citra\n```" + W_AR,
         "عدة أسطر أوامر تنتهي بـ `selesai`.", "سطر لكل أمر ثم خمسة أسطر لحالة الغرف.", "15 أمرًا على الأكثر؛ أرقام الغرف من 1 إلى 5؛ الاسم كلمة واحدة."))

# ============================== MUDAH-SEDANG (6) ==============================

REGU_PRAJURIT = r'''
class Prajurit {
private:
    string nama;
    int kesehatan = 0;

public:
    void isi(const string &n, int k) {
        nama = n;
        kesehatan = k;
    }

    int getKesehatan() const {
        return kesehatan;
    }
};

class Regu {
private:
    string nama;
    Prajurit *anggota[10];
    int banyak = 0;

public:
    void setNama(const string &n) {
        nama = n;
    }

    string getNama() const {
        return nama;
    }

    void gabung(Prajurit *p) {
        anggota[banyak] = p;
        banyak++;
    }

    int getBanyak() const {
        return banyak;
    }

    int kekuatan() const {
        auto total = 0;
        for (int k = 0; k < banyak; k++) {
            total += anggota[k]->getKesehatan();
        }
        return total;
    }
};

class Peleton {
private:
    Regu regu[3];

public:
    Regu &ambil(int nomor) {
        return regu[nomor - 1];
    }

    int totalAnggota() const {
        return regu[0].getBanyak() + regu[1].getBanyak() + regu[2].getBanyak();
    }

    int terkuat() const {
        auto t = 0;
        for (int k = 1; k < 3; k++) {
            if (regu[k].kekuatan() > regu[t].kekuatan()) {
                t = k;
            }
        }
        return t + 1;
    }
};'''

soal("peleton-regu", "mudah_sedang", "trimmed",
     NAMA + "print(*r.sample(['Alfa', 'Bravo', 'Charlie', 'Delta', 'Elang', 'Garuda'], 3))\nn = r.randint(1, 10)\nprint(n)\nfor nama in r.sample(NAMA, n):\n    print(nama, r.randint(20, 100), r.randint(1, 3) if i != 2 else 2)\n",
     program(REGU_PRAJURIT, r'''    Peleton peleton;
    for (int k = 1; k <= 3; k++) {
        string nama;
        cin >> nama;
        peleton.ambil(k).setNama(nama);
    }
    int n;
    cin >> n;
    Prajurit barak[10];
    for (int k = 0; k < n; k++) {
        string nama;
        int sehat, nomor;
        cin >> nama >> sehat >> nomor;
        barak[k].isi(nama, sehat);
        peleton.ambil(nomor).gabung(&barak[k]);
    }
    for (int k = 1; k <= 3; k++) {
        cout << peleton.ambil(k).getNama() << ": " << peleton.ambil(k).getBanyak() << " prajurit, kekuatan "
             << peleton.ambil(k).kekuatan() << endl;
    }
    cout << "Terkuat: " << peleton.ambil(peleton.terkuat()).getNama() << endl;
    cout << "Total: " << peleton.totalAnggota() << endl;''', "string"), wajib=W,
     id=("Peleton, Regu, Prajurit", "Dalam organisasi militer, sebuah **peleton tersusun dari regu-regu** (komposisi: regu tidak ada di luar peletonnya), sedangkan sebuah **regu beranggotakan prajurit** (agregasi: prajurit tetap ada bila regunya dibubarkan).\n\n"
         "Buat tiga class:\n\n- `Prajurit` — `string nama`, `int kesehatan`;\n- `Regu` — `string nama`, `Prajurit *anggota[10]`, `int banyak`; method `gabung(Prajurit *p)`, `getBanyak()`, `kekuatan()` (jumlah kesehatan anggotanya);\n"
         "- `Peleton` — **tiga objek `Regu`** sebagai anggota langsung (`Regu regu[3]`); method `Regu &ambil(int nomor)`, `int totalAnggota() const`, dan `int terkuat() const` (nomor regu yang kekuatannya terbesar; bila sama, yang nomornya lebih kecil).\n\n"
         "Masukan: nama ketiga regu; lalu n prajurit, masing-masing dengan nama, kesehatan, dan nomor regunya (1–3). Prajurit-prajurit itu sendiri disimpan di `main`.\n\n"
         "Cetak untuk tiap regu `<nama>: <n> prajurit, kekuatan <k>`, lalu `Terkuat: <nama regu>` dan `Total: <banyak prajurit>`.\n\n"
         "Contoh: regu `Alfa Bravo Charlie`; prajurit `Andi 50 1`, `Budi 80 3`, `Citra 60 1` →\n\n```\nAlfa: 2 prajurit, kekuatan 110\nBravo: 0 prajurit, kekuatan 0\nCharlie: 1 prajurit, kekuatan 80\nTerkuat: Alfa\nTotal: 3\n```" + W_ID,
         "Baris pertama: tiga nama regu. Baris kedua: n. Lalu n baris: nama, kesehatan, nomor regu.", "Tiga baris regu, lalu `Terkuat: …` dan `Total: …`.", "1 ≤ n ≤ 10; kesehatan antara 20 dan 100"),
     en=("Platoon, Squads, Soldiers", "In a military organisation a **platoon is made up of squads** (composition: a squad does not exist outside its platoon), while a **squad has soldiers as members** (aggregation: a soldier is still there when the squad is disbanded).\n\n"
         "Make three classes:\n\n- `Prajurit` (soldier) — `string nama`, `int kesehatan` (health);\n- `Regu` (squad) — `string nama`, `Prajurit *anggota[10]`, `int banyak`; methods `gabung(Prajurit *p)` (join), `getBanyak()`, `kekuatan()` (strength: the summed health of its members);\n"
         "- `Peleton` (platoon) — **three `Regu` objects** as direct members (`Regu regu[3]`); methods `Regu &ambil(int nomor)` (get), `int totalAnggota() const`, and `int terkuat() const` (the number of the strongest squad; on a tie the lower number).\n\n"
         "Input: the names of the three squads; then n soldiers, each with a name, a health and the number of their squad (1–3). The soldiers themselves are kept in `main`.\n\n"
         "For each squad print `<name>: <n> prajurit, kekuatan <k>`, then `Terkuat: <squad name>` (strongest) and `Total: <number of soldiers>`.\n\n"
         "Example: squads `Alfa Bravo Charlie`; soldiers `Andi 50 1`, `Budi 80 3`, `Citra 60 1` →\n\n```\nAlfa: 2 prajurit, kekuatan 110\nBravo: 0 prajurit, kekuatan 0\nCharlie: 1 prajurit, kekuatan 80\nTerkuat: Alfa\nTotal: 3\n```" + W_EN,
         "First line: three squad names. Second line: n. Then n lines: name, health, squad number.", "Three squad lines, then `Terkuat: …` and `Total: …`.", "1 ≤ n ≤ 10; health is between 20 and 100"),
     ar=("الفصيل والمجموعات والجنود", "في التنظيم العسكري **يتكون الفصيل من مجموعات** (تركيب: لا وجود للمجموعة خارج فصيلها)، أما **المجموعة فأعضاؤها جنود** (تجميع: يبقى الجندي إذا حُلّت مجموعته).\n\n"
         "أنشئ ثلاثة أصناف:\n\n- `Prajurit` (جندي) — `string nama` و `int kesehatan` (الصحة)؛\n- `Regu` (مجموعة) — `string nama` و `Prajurit *anggota[10]` و `int banyak`؛ والطرائق `gabung(Prajurit *p)` (انضمّ) و `getBanyak()` و `kekuatan()` (القوة: مجموع صحة أعضائها)؛\n"
         "- `Peleton` (فصيل) — **ثلاثة كائنات `Regu`** أعضاءً مباشرين (`Regu regu[3]`)؛ والطرائق `Regu &ambil(int nomor)` (خذ) و `int totalAnggota() const` و `int terkuat() const` (رقم أقوى مجموعة؛ وعند التساوي الأصغر رقمًا).\n\n"
         "المدخل: أسماء المجموعات الثلاث؛ ثم n جنديًا لكل منهم اسم وصحة ورقم مجموعته (1–3). الجنود أنفسهم يُحفظون في `main`.\n\n"
         "اطبع لكل مجموعة `<الاسم>: <n> prajurit, kekuatan <k>` ثم `Terkuat: <اسم المجموعة>` (الأقوى) و `Total: <عدد الجنود>`.\n\n"
         "مثال: المجموعات `Alfa Bravo Charlie`؛ الجنود `Andi 50 1` و `Budi 80 3` و `Citra 60 1` ←\n\n```\nAlfa: 2 prajurit, kekuatan 110\nBravo: 0 prajurit, kekuatan 0\nCharlie: 1 prajurit, kekuatan 80\nTerkuat: Alfa\nTotal: 3\n```" + W_AR,
         "السطر الأول: ثلاثة أسماء مجموعات. السطر الثاني: n. ثم n سطرًا: الاسم والصحة ورقم المجموعة.", "ثلاثة أسطر للمجموعات ثم `Terkuat: …` و `Total: …`.", "1 ≤ n ≤ 10؛ الصحة بين 20 و 100"))

soal("rumah-kamar", "mudah_sedang", "trimmed", "print(r.choice(['Melati', 'Mawar', 'Anggrek', 'Kenanga', 'Dahlia']), *r.sample(['tamu', 'tidur', 'makan', 'dapur', 'kerja', 'baca'], 2))\n",
     program(r'''
class Jendela {
private:
    string kamar;

public:
    Jendela(const string &k) : kamar(k) {
        cout << "+jendela " << kamar << endl;
    }

    ~Jendela() {
        cout << "-jendela " << kamar << endl;
    }
};

class Kamar {
private:
    string nama;
    Jendela jendela;

public:
    Kamar(const string &n) : nama(n), jendela(n) {
        cout << "+kamar " << nama << endl;
    }

    ~Kamar() {
        cout << "-kamar " << nama << endl;
    }
};

class Rumah {
private:
    string nama;
    Kamar depan;
    Kamar belakang;

public:
    Rumah(const string &n, const string &a, const string &b) : nama(n), depan(a), belakang(b) {
        cout << "+rumah " << nama << endl;
    }

    ~Rumah() {
        cout << "-rumah " << nama << endl;
    }
};''', r'''    string nama, a, b;
    cin >> nama >> a >> b;
    Rumah rumah(nama, a, b);''', "string"), wajib=W,
     id=("Rumah, Kamar, Jendela", "Komposisi bisa bertingkat: sebuah rumah terdiri dari kamar, dan tiap kamar punya jendela. Aturannya tetap sama di setiap tingkat — bagian dibuat lebih dulu daripada pemiliknya, dan dihancurkan sesudahnya.\n\n"
         "Buat tiga class yang konstruktor dan destruktornya mencetak satu baris:\n\n- `Jendela` (atribut `string kamar`): `+jendela <kamar>` / `-jendela <kamar>`;\n- `Kamar` (atribut `string nama`, lalu `Jendela jendela`): `+kamar <nama>` / `-kamar <nama>`;\n"
         "- `Rumah` (atribut `string nama`, lalu `Kamar depan`, lalu `Kamar belakang`): `+rumah <nama>` / `-rumah <nama>`.\n\n"
         "Baca nama rumah dan nama kedua kamarnya (depan, belakang). Buat satu `Rumah`; tidak ada perintah lain.\n\n"
         "Untuk `Melati tamu tidur`:\n\n```\n+jendela tamu\n+kamar tamu\n+jendela tidur\n+kamar tidur\n+rumah Melati\n-rumah Melati\n-kamar tidur\n-jendela tidur\n-kamar tamu\n-jendela tamu\n```" + W_ID,
         "Satu baris berisi nama rumah, nama kamar depan, dan nama kamar belakang.", "Sepuluh baris seperti contoh.", "Semua nama satu kata; kedua kamar berbeda nama."),
     en=("House, Rooms, Windows", "Composition can have levels: a house consists of rooms, and each room has a window. The rule stays the same at every level — a part is created before its owner and destroyed after it.\n\n"
         "Make three classes whose constructors and destructors print one line:\n\n- `Jendela` (window; attribute `string kamar`): `+jendela <room>` / `-jendela <room>`;\n- `Kamar` (room; attribute `string nama`, then `Jendela jendela`): `+kamar <name>` / `-kamar <name>`;\n"
         "- `Rumah` (house; attribute `string nama`, then `Kamar depan` (front), then `Kamar belakang` (back)): `+rumah <name>` / `-rumah <name>`.\n\n"
         "Read the name of the house and the names of its two rooms (front, back). Create one `Rumah`; there is no other statement.\n\n"
         "For `Melati tamu tidur`:\n\n```\n+jendela tamu\n+kamar tamu\n+jendela tidur\n+kamar tidur\n+rumah Melati\n-rumah Melati\n-kamar tidur\n-jendela tidur\n-kamar tamu\n-jendela tamu\n```" + W_EN,
         "One line with the name of the house, of the front room and of the back room.", "Ten lines as in the example.", "All names are one word; the two rooms have different names."),
     ar=("بيت وغرف ونوافذ", "للتركيب مستويات: البيت يتكون من غرف، ولكل غرفة نافذة. والقاعدة واحدة في كل مستوى — يُنشأ الجزء قبل مالكه ويُهدم بعده.\n\n"
         "أنشئ ثلاثة أصناف يطبع كلٌّ من بانيها وهادمها سطرًا:\n\n- `Jendela` (نافذة؛ الخاصية `string kamar`): `+jendela <الغرفة>` / `-jendela <الغرفة>`؛\n- `Kamar` (غرفة؛ الخاصية `string nama` ثم `Jendela jendela`): `+kamar <الاسم>` / `-kamar <الاسم>`؛\n"
         "- `Rumah` (بيت؛ الخاصية `string nama` ثم `Kamar depan` (الأمامية) ثم `Kamar belakang` (الخلفية)): `+rumah <الاسم>` / `-rumah <الاسم>`.\n\n"
         "اقرأ اسم البيت واسمي غرفتيه (الأمامية ثم الخلفية). أنشئ `Rumah` واحدًا؛ ولا تعليمة أخرى.\n\n"
         "عند `Melati tamu tidur`:\n\n```\n+jendela tamu\n+kamar tamu\n+jendela tidur\n+kamar tidur\n+rumah Melati\n-rumah Melati\n-kamar tidur\n-jendela tidur\n-kamar tamu\n-jendela tamu\n```" + W_AR,
         "سطر واحد فيه اسم البيت واسم الغرفة الأمامية واسم الغرفة الخلفية.", "عشرة أسطر كما في المثال.", "كل الأسماء كلمة واحدة؛ واسما الغرفتين مختلفان."))

soal("gudang-pemilik-peti", "mudah_sedang", "trimmed", BENDA + "n = {2: 1}.get(i, r.randint(2, 8))\nprint(r.choice(['Utara', 'Selatan', 'Timur', 'Barat', 'Pusat']), n)\nprint(*r.sample(BENDA, n))\n",
     program(r'''
class Peti {
private:
    string label;

public:
    Peti(const string &l) : label(l) {
        cout << "+peti " << label << endl;
    }

    ~Peti() {
        cout << "-peti " << label << endl;
    }
};

class Gudang {
private:
    string nama;
    Peti *peti[10];
    int banyak = 0;

public:
    Gudang(const string &n) : nama(n) {
        cout << "+gudang " << nama << endl;
    }

    ~Gudang() {
        for (int k = 0; k < banyak; k++) {
            delete peti[k];
        }
        cout << "-gudang " << nama << endl;
    }

    void terima(const string &label) {
        peti[banyak] = new Peti(label);
        banyak++;
    }

    int getBanyak() const {
        return banyak;
    }
};''', r'''    string nama;
    int n;
    cin >> nama >> n;
    Gudang gudang(nama);
    for (int k = 0; k < n; k++) {
        string label;
        cin >> label;
        gudang.terima(label);
    }
    cout << "Gudang berisi " << gudang.getBanyak() << " peti" << endl;''', "string"), wajib=W,
     id=("Gudang Pemilik Peti", "Komposisi tidak selalu berupa anggota langsung. Bila banyak bagiannya baru diketahui saat program berjalan, pemilik membuat bagian-bagiannya dengan `new` — dan karena ia **pemiliknya**, ia pula yang wajib menghancurkannya di destruktor.\n\n"
         "Buat class `Peti` (atribut `string label`; konstruktor mencetak `+peti <label>`, destruktor mencetak `-peti <label>`).\n\n"
         "Buat class `Gudang` dengan atribut `string nama`, `Peti *peti[10]`, `int banyak`:\n\n"
         "- konstruktor mencetak `+gudang <nama>`;\n- `void terima(const string &label)` — **gudang sendiri** yang membuat peti baru dengan `new`;\n"
         "- destruktor men-`delete` semua petinya **menurut urutan diterima**, lalu mencetak `-gudang <nama>`.\n\n"
         "Baca nama gudang dan n label. Buat gudang, terima semua peti, lalu cetak `Gudang berisi <n> peti`.\n\n"
         "Untuk `Utara 2` dan label `meja kursi`:\n\n```\n+gudang Utara\n+peti meja\n+peti kursi\nGudang berisi 2 peti\n-peti meja\n-peti kursi\n-gudang Utara\n```" + W_ID,
         "Baris pertama: nama gudang dan n. Baris kedua: n label.", "Baris-baris seperti contoh.", "1 ≤ n ≤ 8; semua label berbeda."),
     en=("The Warehouse Owns its Crates", "Composition is not always a direct member. When the number of parts is known only at run time, the owner creates its parts with `new` — and because it is the **owner**, it must also destroy them in its destructor.\n\n"
         "Make a class `Peti` (crate; attribute `string label`; the constructor prints `+peti <label>`, the destructor prints `-peti <label>`).\n\n"
         "Make a class `Gudang` (warehouse) with the attributes `string nama`, `Peti *peti[10]`, `int banyak`:\n\n"
         "- the constructor prints `+gudang <name>`;\n- `void terima(const string &label)` (receive) — **the warehouse itself** creates the new crate with `new`;\n"
         "- the destructor `delete`s all its crates **in the order they were received**, then prints `-gudang <name>`.\n\n"
         "Read the warehouse's name and n labels. Create the warehouse, receive all crates, then print `Gudang berisi <n> peti` (the warehouse holds n crates).\n\n"
         "For `Utara 2` and the labels `meja kursi`:\n\n```\n+gudang Utara\n+peti meja\n+peti kursi\nGudang berisi 2 peti\n-peti meja\n-peti kursi\n-gudang Utara\n```" + W_EN,
         "First line: the warehouse's name and n. Second line: n labels.", "Lines as in the example.", "1 ≤ n ≤ 8; all labels differ."),
     ar=("المستودع يملك صناديقه", "ليس التركيب دائمًا عضوًا مباشرًا. إذا كان عدد الأجزاء لا يُعرف إلا عند التشغيل فالمالك ينشئ أجزاءه بـ `new` — ولأنه **المالك** فعليه هو أن يهدمها في هادمه.\n\n"
         "أنشئ صنفًا `Peti` (صندوق؛ الخاصية `string label`؛ الباني يطبع `+peti <الملصق>` والهادم يطبع `-peti <الملصق>`).\n\n"
         "أنشئ صنفًا `Gudang` (مستودع) خصائصه `string nama` و `Peti *peti[10]` و `int banyak`:\n\n"
         "- الباني يطبع `+gudang <الاسم>`؛\n- `void terima(const string &label)` (استلم) — **المستودع نفسه** ينشئ الصندوق الجديد بـ `new`؛\n"
         "- الهادم ينفّذ `delete` على كل صناديقه **بترتيب استلامها** ثم يطبع `-gudang <الاسم>`.\n\n"
         "اقرأ اسم المستودع و n ملصقًا. أنشئ المستودع واستلم كل الصناديق ثم اطبع `Gudang berisi <n> peti` (في المستودع n صندوقًا).\n\n"
         "عند `Utara 2` والملصقين `meja kursi`:\n\n```\n+gudang Utara\n+peti meja\n+peti kursi\nGudang berisi 2 peti\n-peti meja\n-peti kursi\n-gudang Utara\n```" + W_AR,
         "السطر الأول: اسم المستودع و n. السطر الثاني: n ملصقًا.", "أسطر كما في المثال.", "1 ≤ n ≤ 8؛ الملصقات كلها مختلفة."))

soal("pameran-lukisan", "mudah_sedang", "trimmed",
     "JUDUL = ['Senja', 'Ombak', 'Gunung', 'Pasar', 'Sawah', 'Hutan', 'Kota', 'Perahu']\nn = r.randint(1, 6)\nm = r.randint(0, n)\nprint(r.choice(['Nusantara', 'Kemerdekaan', 'Warna', 'Alam']), n)\nprint(*r.sample(JUDUL, n))\nprint(m, *r.sample(range(1, n + 1), m))\n",
     program(r'''
class Lukisan {
private:
    string judul;

public:
    Lukisan(const string &j) : judul(j) {
        cout << "+lukisan " << judul << endl;
    }

    ~Lukisan() {
        cout << "-lukisan " << judul << endl;
    }

    string getJudul() const {
        return judul;
    }
};

class Pameran {
private:
    string nama;
    Lukisan *karya[10];
    int banyak = 0;

public:
    Pameran(const string &n) : nama(n) {
        cout << "+pameran " << nama << endl;
    }

    ~Pameran() {
        cout << "-pameran " << nama << endl;
    }

    void pajang(Lukisan *l) {
        karya[banyak] = l;
        banyak++;
        cout << "Dipajang: " << l->getJudul() << endl;
    }
};''', r'''    string nama;
    int n, m;
    cin >> nama >> n;
    Lukisan *koleksi[10];
    for (int k = 0; k < n; k++) {
        string judul;
        cin >> judul;
        koleksi[k] = new Lukisan(judul);
    }
    cin >> m;
    {
        Pameran pameran(nama);
        for (int k = 0; k < m; k++) {
            int nomor;
            cin >> nomor;
            pameran.pajang(koleksi[nomor - 1]);
        }
    }
    cout << "Koleksi tetap " << n << " lukisan" << endl;
    for (int k = 0; k < n; k++) {
        delete koleksi[k];
    }''', "string"), wajib=W,
     id=("Pameran Lukisan", "Sebuah pameran **memajang** lukisan, tetapi tidak memilikinya: lukisan itu milik kolektor dan kembali ke kolektor setelah pameran usai. Karena itu destruktor `Pameran` **tidak** men-`delete` lukisan.\n\n"
         "Buat class `Lukisan` (atribut `string judul`; konstruktor mencetak `+lukisan <judul>`, destruktor `-lukisan <judul>`).\n\n"
         "Buat class `Pameran` dengan atribut `string nama`, `Lukisan *karya[10]`, `int banyak`; konstruktor mencetak `+pameran <nama>`, destruktor `-pameran <nama>`; method `void pajang(Lukisan *l)` mencatat lukisan dan mencetak `Dipajang: <judul>`.\n\n"
         "Di `main`: buat n lukisan dengan `new` (koleksi). Lalu **di dalam sebuah blok** buat pameran dan pajang lukisan-lukisan yang nomornya disebut. Setelah blok berakhir, cetak `Koleksi tetap <n> lukisan`, baru kemudian hancurkan koleksi menurut urutan pembuatannya.\n\n"
         "Untuk pameran `Alam`, koleksi `Senja Ombak`, yang dipajang nomor 2:\n\n```\n+lukisan Senja\n+lukisan Ombak\n+pameran Alam\nDipajang: Ombak\n-pameran Alam\nKoleksi tetap 2 lukisan\n-lukisan Senja\n-lukisan Ombak\n```" + W_ID,
         "Baris pertama: nama pameran dan n. Baris kedua: n judul. Baris ketiga: m, lalu m nomor lukisan yang dipajang (mulai dari 1).", "Baris-baris seperti contoh.", "1 ≤ n ≤ 6; 0 ≤ m ≤ n; nomor yang dipajang berbeda-beda."),
     en=("An Exhibition of Paintings", "An exhibition **shows** paintings but does not own them: they belong to the collector and go back to the collector when the exhibition is over. So the destructor of `Pameran` does **not** `delete` the paintings.\n\n"
         "Make a class `Lukisan` (painting; attribute `string judul`; the constructor prints `+lukisan <title>`, the destructor `-lukisan <title>`).\n\n"
         "Make a class `Pameran` (exhibition) with the attributes `string nama`, `Lukisan *karya[10]`, `int banyak`; the constructor prints `+pameran <name>`, the destructor `-pameran <name>`; a method `void pajang(Lukisan *l)` (hang) records the painting and prints `Dipajang: <title>`.\n\n"
         "In `main`: create n paintings with `new` (the collection). Then, **inside a block**, create the exhibition and hang the paintings whose numbers are given. After the block ends print `Koleksi tetap <n> lukisan` (the collection still has n paintings), and only then destroy the collection in the order of creation.\n\n"
         "For the exhibition `Alam`, the collection `Senja Ombak`, and number 2 on show:\n\n```\n+lukisan Senja\n+lukisan Ombak\n+pameran Alam\nDipajang: Ombak\n-pameran Alam\nKoleksi tetap 2 lukisan\n-lukisan Senja\n-lukisan Ombak\n```" + W_EN,
         "First line: the exhibition's name and n. Second line: n titles. Third line: m, then the m numbers of the paintings on show (from 1).", "Lines as in the example.", "1 ≤ n ≤ 6; 0 ≤ m ≤ n; the numbers on show are all different."),
     ar=("معرض لوحات", "المعرض **يعرض** اللوحات لكنه لا يملكها: هي ملك لصاحب المجموعة وتعود إليه بعد انتهاء المعرض. لذلك هادم `Pameran` **لا** ينفّذ `delete` على اللوحات.\n\n"
         "أنشئ صنفًا `Lukisan` (لوحة؛ الخاصية `string judul`؛ الباني يطبع `+lukisan <العنوان>` والهادم `-lukisan <العنوان>`).\n\n"
         "أنشئ صنفًا `Pameran` (معرض) خصائصه `string nama` و `Lukisan *karya[10]` و `int banyak`؛ الباني يطبع `+pameran <الاسم>` والهادم `-pameran <الاسم>`؛ والطريقة `void pajang(Lukisan *l)` (علِّق) تسجّل اللوحة وتطبع `Dipajang: <العنوان>`.\n\n"
         "في `main`: أنشئ n لوحة بـ `new` (المجموعة). ثم **داخل كتلة** أنشئ المعرض وعلِّق اللوحات المذكورة أرقامها. بعد انتهاء الكتلة اطبع `Koleksi tetap <n> lukisan` (ما زالت المجموعة n لوحة) ثم اهدم المجموعة بترتيب إنشائها.\n\n"
         "للمعرض `Alam` والمجموعة `Senja Ombak` والمعروضة رقم 2:\n\n```\n+lukisan Senja\n+lukisan Ombak\n+pameran Alam\nDipajang: Ombak\n-pameran Alam\nKoleksi tetap 2 lukisan\n-lukisan Senja\n-lukisan Ombak\n```" + W_AR,
         "السطر الأول: اسم المعرض و n. السطر الثاني: n عنوانًا. السطر الثالث: m ثم m رقمًا للوحات المعروضة (من 1).", "أسطر كما في المثال.", "1 ≤ n ≤ 6؛ 0 ≤ m ≤ n؛ أرقام المعروضات مختلفة."))

soal("peminjaman-buku", "mudah_sedang", "trimmed",
     NAMA + "JUDUL = ['Algoritma', 'Kalkulus', 'Fisika', 'Sejarah', 'Statistika', 'Biologi', 'Ekonomi', 'Logika']\nb = r.randint(2, 5)\na = r.randint(1, 4)\njudul = r.sample(JUDUL, b)\nanggota = r.sample(NAMA, a)\nprint(b, *judul)\nprint(a, *anggota)\nfor _ in range(r.randint(1, 15)):\n    if r.random() < 0.65:\n        print('pinjam', r.choice(judul), r.choice(anggota))\n    else:\n        print('kembali', r.choice(judul))\nprint('selesai')\n",
     program(r'''
class Anggota {
private:
    string nama;
    int pinjaman = 0;

public:
    void setNama(const string &n) {
        nama = n;
    }

    string getNama() const {
        return nama;
    }

    int getPinjaman() const {
        return pinjaman;
    }

    void ubahPinjaman(int selisih) {
        pinjaman += selisih;
    }
};

class Buku {
private:
    string judul;
    Anggota *peminjam = nullptr;

public:
    void setJudul(const string &j) {
        judul = j;
    }

    string getJudul() const {
        return judul;
    }

    Anggota *getPeminjam() const {
        return peminjam;
    }

    void setPeminjam(Anggota *a) {
        peminjam = a;
    }
};

class Perpustakaan {
private:
    Buku buku[5];
    Anggota anggota[4];
    int banyakBuku = 0;
    int banyakAnggota = 0;

    Buku *cariBuku(const string &judul) {
        for (int k = 0; k < banyakBuku; k++) {
            if (buku[k].getJudul() == judul) {
                return &buku[k];
            }
        }
        return nullptr;
    }

    Anggota *cariAnggota(const string &nama) {
        for (int k = 0; k < banyakAnggota; k++) {
            if (anggota[k].getNama() == nama) {
                return &anggota[k];
            }
        }
        return nullptr;
    }

public:
    void tambahBuku(const string &judul) {
        buku[banyakBuku].setJudul(judul);
        banyakBuku++;
    }

    void tambahAnggota(const string &nama) {
        anggota[banyakAnggota].setNama(nama);
        banyakAnggota++;
    }

    string pinjam(const string &judul, const string &nama) {
        auto b = cariBuku(judul);
        auto a = cariAnggota(nama);
        if (b->getPeminjam() != nullptr) {
            return "Sedang dipinjam " + b->getPeminjam()->getNama();
        }
        if (a->getPinjaman() == 2) {
            return "Batas pinjaman tercapai";
        }
        b->setPeminjam(a);
        a->ubahPinjaman(1);
        return "Dipinjam " + nama;
    }

    string kembali(const string &judul) {
        auto b = cariBuku(judul);
        if (b->getPeminjam() == nullptr) {
            return "Buku tidak sedang dipinjam";
        }
        b->getPeminjam()->ubahPinjaman(-1);
        b->setPeminjam(nullptr);
        return "Dikembalikan";
    }

    void laporan() const {
        for (int k = 0; k < banyakAnggota; k++) {
            cout << anggota[k].getNama() << ": " << anggota[k].getPinjaman() << " buku" << endl;
        }
    }
};''', r'''    Perpustakaan perpus;
    int b, a;
    cin >> b;
    for (int k = 0; k < b; k++) {
        string judul;
        cin >> judul;
        perpus.tambahBuku(judul);
    }
    cin >> a;
    for (int k = 0; k < a; k++) {
        string nama;
        cin >> nama;
        perpus.tambahAnggota(nama);
    }
    string perintah;
    while (cin >> perintah) {
        if (perintah == "selesai") {
            break;
        }
        string judul;
        cin >> judul;
        if (perintah == "pinjam") {
            string nama;
            cin >> nama;
            cout << perpus.pinjam(judul, nama) << endl;
        } else {
            cout << perpus.kembali(judul) << endl;
        }
    }
    perpus.laporan();''', "string"), wajib=W,
     id=("Peminjaman Buku", "Sebuah perpustakaan **terdiri dari** buku dan anggota (komposisi). Di antara buku dan anggota ada **asosiasi**: sebuah buku sedang dipinjam oleh paling banyak satu anggota (0..1), dan seorang anggota boleh meminjam paling banyak **dua** buku.\n\n"
         "Buat class `Anggota` (`string nama`, `int pinjaman`), class `Buku` (`string judul`, `Anggota *peminjam` yang mula-mula `nullptr`), dan class `Perpustakaan` yang menyimpan `Buku buku[5]` dan `Anggota anggota[4]` serta method:\n\n"
         "- `string pinjam(judul, nama)` — diperiksa berurutan: buku sedang dipinjam → `Sedang dipinjam <nama peminjam>`; anggota sudah meminjam dua buku → `Batas pinjaman tercapai`; selain itu → `Dipinjam <nama>`;\n"
         "- `string kembali(judul)` → `Dikembalikan`, atau `Buku tidak sedang dipinjam`;\n- `void laporan() const` — tiap anggota: `<nama>: <n> buku`.\n\n"
         "Masukan: daftar judul; daftar anggota; lalu perintah `pinjam <judul> <nama>` dan `kembali <judul>`, diakhiri `selesai`. Cetak pesan tiap perintah, lalu laporannya.\n\n"
         "Contoh: buku `Fisika Logika Biologi`, anggota `Andi Budi`; `pinjam Fisika Andi`, `pinjam Fisika Budi`, `pinjam Logika Andi`, `pinjam Biologi Andi`, `kembali Fisika`, `kembali Fisika`, `selesai` →\n\n```\nDipinjam Andi\nSedang dipinjam Andi\nDipinjam Andi\nBatas pinjaman tercapai\nDikembalikan\nBuku tidak sedang dipinjam\nAndi: 1 buku\nBudi: 0 buku\n```" + W_ID,
         "Baris pertama: banyak buku, lalu judul-judulnya. Baris kedua: banyak anggota, lalu nama-namanya. Lalu perintah-perintah, diakhiri `selesai`.", "Satu baris per perintah, lalu satu baris per anggota.", "2 sampai 5 buku; 1 sampai 4 anggota; paling banyak 15 perintah; judul dan nama dalam perintah selalu terdaftar."),
     en=("Lending Books", "A library **consists of** books and members (composition). Between books and members there is an **association**: a book is on loan to at most one member (0..1), and a member may borrow at most **two** books.\n\n"
         "Make a class `Anggota` (member; `string nama`, `int pinjaman` (loans)), a class `Buku` (book; `string judul`, `Anggota *peminjam` (borrower), `nullptr` at first), and a class `Perpustakaan` (library) that keeps `Buku buku[5]` and `Anggota anggota[4]`, with the methods:\n\n"
         "- `string pinjam(judul, nama)` (borrow) — checked in order: the book is on loan → `Sedang dipinjam <borrower's name>`; the member already has two books → `Batas pinjaman tercapai` (loan limit reached); otherwise → `Dipinjam <name>`;\n"
         "- `string kembali(judul)` (return) → `Dikembalikan` (returned), or `Buku tidak sedang dipinjam` (the book is not on loan);\n- `void laporan() const` — for every member: `<name>: <n> buku`.\n\n"
         "Input: the list of titles; the list of members; then commands `pinjam <title> <name>` and `kembali <title>`, ending with `selesai`. Print the message of every command, then the report.\n\n"
         "Example: books `Fisika Logika Biologi`, members `Andi Budi`; `pinjam Fisika Andi`, `pinjam Fisika Budi`, `pinjam Logika Andi`, `pinjam Biologi Andi`, `kembali Fisika`, `kembali Fisika`, `selesai` →\n\n```\nDipinjam Andi\nSedang dipinjam Andi\nDipinjam Andi\nBatas pinjaman tercapai\nDikembalikan\nBuku tidak sedang dipinjam\nAndi: 1 buku\nBudi: 0 buku\n```" + W_EN,
         "First line: the number of books, then their titles. Second line: the number of members, then their names. Then the commands, ending with `selesai`.", "One line per command, then one line per member.", "2 to 5 books; 1 to 4 members; at most 15 commands; titles and names in commands are always registered."),
     ar=("إعارة الكتب", "المكتبة **تتكون من** كتب وأعضاء (تركيب). وبين الكتب والأعضاء **ارتباط**: الكتاب معار لعضو واحد على الأكثر (0..1)، والعضو يستعير **كتابين** على الأكثر.\n\n"
         "أنشئ صنفًا `Anggota` (عضو؛ `string nama` و `int pinjaman` (الإعارات))، وصنفًا `Buku` (كتاب؛ `string judul` و `Anggota *peminjam` (المستعير) الذي يبدأ `nullptr`)، وصنفًا `Perpustakaan` (مكتبة) يحفظ `Buku buku[5]` و `Anggota anggota[4]` وطرائقه:\n\n"
         "- `string pinjam(judul, nama)` (استعر) — يُفحص بالترتيب: الكتاب معار ← `Sedang dipinjam <اسم المستعير>`؛ العضو عنده كتابان ← `Batas pinjaman tercapai` (بلغ حد الإعارة)؛ وإلا ← `Dipinjam <الاسم>`؛\n"
         "- `string kembali(judul)` (أعِد) ← `Dikembalikan` (أُعيد) أو `Buku tidak sedang dipinjam` (الكتاب غير معار)؛\n- `void laporan() const` — لكل عضو: `<الاسم>: <n> buku`.\n\n"
         "المدخل: قائمة العناوين؛ قائمة الأعضاء؛ ثم أوامر `pinjam <العنوان> <الاسم>` و `kembali <العنوان>` تنتهي بـ `selesai`. اطبع رسالة كل أمر ثم التقرير.\n\n"
         "مثال: الكتب `Fisika Logika Biologi` والأعضاء `Andi Budi`؛ `pinjam Fisika Andi` ثم `pinjam Fisika Budi` ثم `pinjam Logika Andi` ثم `pinjam Biologi Andi` ثم `kembali Fisika` ثم `kembali Fisika` ثم `selesai` ←\n\n```\nDipinjam Andi\nSedang dipinjam Andi\nDipinjam Andi\nBatas pinjaman tercapai\nDikembalikan\nBuku tidak sedang dipinjam\nAndi: 1 buku\nBudi: 0 buku\n```" + W_AR,
         "السطر الأول: عدد الكتب ثم عناوينها. السطر الثاني: عدد الأعضاء ثم أسماؤهم. ثم الأوامر منتهيةً بـ `selesai`.", "سطر لكل أمر ثم سطر لكل عضو.", "من 2 إلى 5 كتب؛ من 1 إلى 4 أعضاء؛ 15 أمرًا على الأكثر؛ العناوين والأسماء في الأوامر مسجَّلة دائمًا."))

soal("jadwal-kuliah-bentrok", "mudah_sedang", "trimmed",
     NAMA + "ruang = r.sample(['R101', 'R102', 'R201', 'Lab1', 'Lab2', 'Aula'], r.randint(1, 4))\ndosen = r.sample(NAMA, r.randint(1, 4))\nprint(len(ruang), *ruang)\nprint(len(dosen), *dosen)\nn = r.randint(1, 7)\nprint(n)\nfor k in range(n):\n    print('MK' + str(k + 1), r.randint(1, len(ruang)), r.randint(1, len(dosen)), r.randint(1, 2 if i % 2 == 0 else 5), r.randint(8, 9 if i % 2 == 0 else 15))\n",
     program(r'''
class Ruang {
public:
    string nama;
};

class Dosen {
public:
    string nama;
};

class Kuliah {
private:
    string kode;
    Ruang *ruang = nullptr;
    Dosen *dosen = nullptr;
    int hari = 0;
    int jam = 0;

public:
    void isi(const string &k, Ruang *r, Dosen *d, int h, int j) {
        kode = k;
        ruang = r;
        dosen = d;
        hari = h;
        jam = j;
    }

    string getKode() const {
        return kode;
    }

    string bentrokDengan(const Kuliah &lain) const {
        if (hari != lain.hari || jam != lain.jam) {
            return "";
        }
        if (ruang == lain.ruang && dosen == lain.dosen) {
            return "ruang " + ruang->nama + " dan dosen " + dosen->nama;
        }
        if (ruang == lain.ruang) {
            return "ruang " + ruang->nama;
        }
        if (dosen == lain.dosen) {
            return "dosen " + dosen->nama;
        }
        return "";
    }
};''', r'''    Ruang ruang[4];
    Dosen dosen[4];
    Kuliah kuliah[7];
    int r, d, n;
    cin >> r;
    for (int k = 0; k < r; k++) {
        cin >> ruang[k].nama;
    }
    cin >> d;
    for (int k = 0; k < d; k++) {
        cin >> dosen[k].nama;
    }
    cin >> n;
    for (int k = 0; k < n; k++) {
        string kode;
        int nr, nd, hari, jam;
        cin >> kode >> nr >> nd >> hari >> jam;
        kuliah[k].isi(kode, &ruang[nr - 1], &dosen[nd - 1], hari, jam);
    }
    auto ada = false;
    for (int a = 0; a < n; a++) {
        for (int b = a + 1; b < n; b++) {
            auto sebab = kuliah[a].bentrokDengan(kuliah[b]);
            if (sebab != "") {
                cout << kuliah[a].getKode() << " dan " << kuliah[b].getKode() << ": " << sebab << endl;
                ada = true;
            }
        }
    }
    if (!ada) {
        cout << "Tidak ada bentrok" << endl;
    }''', "string"), wajib=W,
     id=("Jadwal Kuliah Bentrok", "Sebuah kuliah **berhubungan** dengan satu ruang dan satu dosen. Ruang dan dosen dipakai bersama oleh banyak kuliah, jadi `Kuliah` menyimpan **penunjuk** ke keduanya — dan dua kuliah memakai ruang yang sama tepat bila kedua penunjuknya sama.\n\n"
         "Buat class `Ruang` dan `Dosen` (masing-masing `string nama`) dan class `Kuliah` dengan atribut `string kode`, `Ruang *ruang`, `Dosen *dosen`, `int hari`, `int jam`, serta method `string bentrokDengan(const Kuliah &lain) const`.\n\n"
         "Dua kuliah bentrok bila **hari dan jamnya sama** dan ruangnya sama, dosennya sama, atau keduanya. Method itu mengembalikan sebabnya: `ruang <nama>`, `dosen <nama>`, `ruang <nama> dan dosen <nama>`, atau teks kosong bila tidak bentrok.\n\n"
         "Masukan: daftar ruang; daftar dosen; lalu n kuliah — kode, nomor ruang, nomor dosen, hari, jam. Untuk setiap pasang kuliah yang bentrok (pasangan diperiksa menurut urutan masukan) cetak `<kode1> dan <kode2>: <sebab>`. Bila tidak ada, cetak `Tidak ada bentrok`.\n\n"
         "Contoh: ruang `R101 Lab1`, dosen `Andi Budi`; kuliah `MK1 1 1 2 8`, `MK2 1 2 2 8`, `MK3 2 1 2 8`, `MK4 1 1 3 8` →\n\n```\nMK1 dan MK2: ruang R101\nMK1 dan MK3: dosen Andi\n```" + W_ID,
         "Baris pertama: banyak ruang dan namanya. Baris kedua: banyak dosen dan namanya. Baris ketiga: n. Lalu n baris kuliah.", "Satu baris per pasangan yang bentrok, atau `Tidak ada bentrok`.", "1 sampai 4 ruang; 1 sampai 4 dosen; 1 ≤ n ≤ 7; hari 1–5; jam 8–15"),
     en=("Clashing Timetable", "A lecture is **linked** to one room and one lecturer. Rooms and lecturers are shared by many lectures, so `Kuliah` keeps **pointers** to both — and two lectures use the same room exactly when the two pointers are equal.\n\n"
         "Make the classes `Ruang` (room) and `Dosen` (lecturer), each with `string nama`, and a class `Kuliah` (lecture) with the attributes `string kode`, `Ruang *ruang`, `Dosen *dosen`, `int hari` (day), `int jam` (hour), and a method `string bentrokDengan(const Kuliah &lain) const` (clashes with).\n\n"
         "Two lectures clash when **day and hour are the same** and the room is the same, the lecturer is the same, or both. The method returns the reason: `ruang <name>`, `dosen <name>`, `ruang <name> dan dosen <name>`, or an empty text when they do not clash.\n\n"
         "Input: the list of rooms; the list of lecturers; then n lectures — code, room number, lecturer number, day, hour. For every clashing pair of lectures (pairs are checked in input order) print `<code1> dan <code2>: <reason>`. If there is none, print `Tidak ada bentrok` (no clash).\n\n"
         "Example: rooms `R101 Lab1`, lecturers `Andi Budi`; lectures `MK1 1 1 2 8`, `MK2 1 2 2 8`, `MK3 2 1 2 8`, `MK4 1 1 3 8` →\n\n```\nMK1 dan MK2: ruang R101\nMK1 dan MK3: dosen Andi\n```" + W_EN,
         "First line: the number of rooms and their names. Second line: the number of lecturers and their names. Third line: n. Then n lecture lines.", "One line per clashing pair, or `Tidak ada bentrok`.", "1 to 4 rooms; 1 to 4 lecturers; 1 ≤ n ≤ 7; days 1–5; hours 8–15"),
     ar=("تعارض الجدول الدراسي", "المحاضرة **مرتبطة** بقاعة واحدة ومحاضر واحد. والقاعات والمحاضرون مشتركون بين محاضرات كثيرة، فيحفظ `Kuliah` **مؤشرين** إليهما — وتستعمل محاضرتان القاعة نفسها تمامًا إذا تساوى المؤشران.\n\n"
         "أنشئ الصنفين `Ruang` (قاعة) و `Dosen` (محاضر) ولكل منهما `string nama`، وصنفًا `Kuliah` (محاضرة) خصائصه `string kode` و `Ruang *ruang` و `Dosen *dosen` و `int hari` (اليوم) و `int jam` (الساعة)، وطريقة `string bentrokDengan(const Kuliah &lain) const` (تتعارض مع).\n\n"
         "تتعارض محاضرتان إذا **تساوى اليوم والساعة** وتساوت القاعة أو المحاضر أو كلاهما. تُرجع الطريقة السبب: `ruang <الاسم>` أو `dosen <الاسم>` أو `ruang <الاسم> dan dosen <الاسم>`، أو نصًّا فارغًا إذا لم تتعارضا.\n\n"
         "المدخل: قائمة القاعات؛ قائمة المحاضرين؛ ثم n محاضرة — الرمز ورقم القاعة ورقم المحاضر واليوم والساعة. لكل زوج متعارض (تُفحص الأزواج بترتيب المدخل) اطبع `<الرمز1> dan <الرمز2>: <السبب>`. وإذا لم يوجد فاطبع `Tidak ada bentrok` (لا تعارض).\n\n"
         "مثال: القاعتان `R101 Lab1` والمحاضران `Andi Budi`؛ المحاضرات `MK1 1 1 2 8` و `MK2 1 2 2 8` و `MK3 2 1 2 8` و `MK4 1 1 3 8` ←\n\n```\nMK1 dan MK2: ruang R101\nMK1 dan MK3: dosen Andi\n```" + W_AR,
         "السطر الأول: عدد القاعات وأسماؤها. السطر الثاني: عدد المحاضرين وأسماؤهم. السطر الثالث: n. ثم n سطر محاضرات.", "سطر لكل زوج متعارض، أو `Tidak ada bentrok`.", "من 1 إلى 4 قاعات؛ من 1 إلى 4 محاضرين؛ 1 ≤ n ≤ 7؛ الأيام 1–5؛ الساعات 8–15"))

# ================================ SEDANG (6) ================================

soal("peta-jalan", "sedang", "trimmed",
     KOTA + "n = r.randint(2, 8)\nkota = r.sample(KOTA, n)\nprint(n, *kota)\npasang = [(a, b) for a in range(n) for b in range(a + 1, n)]\nm = r.randint(1, min(12, len(pasang)))\nprint(m)\nfor a, b in r.sample(pasang, m):\n    print(kota[a], kota[b], r.randint(5, 300))\n",
     program(r'''
class Kota {
public:
    string nama;
};

class Jalan {
private:
    Kota *dari = nullptr;
    Kota *ke = nullptr;
    int km = 0;

public:
    void isi(Kota *a, Kota *b, int k) {
        dari = a;
        ke = b;
        km = k;
    }

    bool menyentuh(const Kota *k) const {
        return dari == k || ke == k;
    }

    int getKm() const {
        return km;
    }
};

class Peta {
private:
    Kota kota[8];
    Jalan jalan[12];
    int banyakKota = 0;
    int banyakJalan = 0;

    Kota *cari(const string &nama) {
        for (int k = 0; k < banyakKota; k++) {
            if (kota[k].nama == nama) {
                return &kota[k];
            }
        }
        return nullptr;
    }

public:
    void tambahKota(const string &nama) {
        kota[banyakKota].nama = nama;
        banyakKota++;
    }

    void tambahJalan(const string &a, const string &b, int km) {
        jalan[banyakJalan].isi(cari(a), cari(b), km);
        banyakJalan++;
    }

    int jumlahJalan(int k) const {
        auto n = 0;
        for (int j = 0; j < banyakJalan; j++) {
            if (jalan[j].menyentuh(&kota[k])) {
                n++;
            }
        }
        return n;
    }

    int totalKm(int k) const {
        auto n = 0;
        for (int j = 0; j < banyakJalan; j++) {
            if (jalan[j].menyentuh(&kota[k])) {
                n += jalan[j].getKm();
            }
        }
        return n;
    }

    void laporan() const {
        auto sibuk = 0;
        for (int k = 0; k < banyakKota; k++) {
            cout << kota[k].nama << ": " << jumlahJalan(k) << " jalan, " << totalKm(k) << " km" << endl;
            if (jumlahJalan(k) > jumlahJalan(sibuk)) {
                sibuk = k;
            }
        }
        cout << "Tersibuk: " << kota[sibuk].nama << endl;
    }
};''', r'''    Peta peta;
    int n, m;
    cin >> n;
    for (int k = 0; k < n; k++) {
        string nama;
        cin >> nama;
        peta.tambahKota(nama);
    }
    cin >> m;
    for (int k = 0; k < m; k++) {
        string a, b;
        int km;
        cin >> a >> b >> km;
        peta.tambahJalan(a, b, km);
    }
    peta.laporan();''', "string"), wajib=W,
     id=("Peta Jalan", "Sebuah peta **terdiri dari** kota-kota dan jalan-jalan (komposisi). Sebuah jalan **menghubungkan** dua kota (asosiasi): ia menyimpan penunjuk ke kedua kota ujungnya, yang hidup di dalam peta.\n\n"
         "Buat class `Kota` (`string nama`), class `Jalan` (`Kota *dari`, `Kota *ke`, `int km`; method `bool menyentuh(const Kota *k) const`, `int getKm() const`), dan class `Peta` yang menyimpan `Kota kota[8]` dan `Jalan jalan[12]` dengan method `tambahKota`, `tambahJalan(namaA, namaB, km)`, `int jumlahJalan(int k) const`, `int totalKm(int k) const`, dan `void laporan() const`.\n\n"
         "Masukan: daftar nama kota; lalu m jalan — nama dua kota dan panjangnya. Jalan bisa dilalui dua arah.\n\n"
         "Untuk tiap kota, menurut urutan masukan, cetak `<nama>: <banyak jalan> jalan, <jumlah km> km`. Lalu cetak `Tersibuk: <nama>` — kota dengan jalan terbanyak; bila sama, yang lebih dulu di masukan.\n\n"
         "Contoh: kota `Bandung Garut Solo`; jalan `Bandung Garut 60`, `Bandung Solo 450` →\n\n```\nBandung: 2 jalan, 510 km\nGarut: 1 jalan, 60 km\nSolo: 1 jalan, 450 km\nTersibuk: Bandung\n```" + W_ID,
         "Baris pertama: n dan nama n kota. Baris kedua: m. Lalu m baris: dua nama kota dan panjang jalan.", "Satu baris per kota, lalu `Tersibuk: …`.", "2 ≤ n ≤ 8; 1 ≤ m ≤ 12; tidak ada dua jalan yang menghubungkan pasangan kota yang sama; 5 ≤ km ≤ 300"),
     en=("Road Map", "A map **consists of** towns and roads (composition). A road **connects** two towns (association): it keeps pointers to its two end towns, which live inside the map.\n\n"
         "Make a class `Kota` (town; `string nama`), a class `Jalan` (road; `Kota *dari` (from), `Kota *ke` (to), `int km`; methods `bool menyentuh(const Kota *k) const` (touches), `int getKm() const`), and a class `Peta` (map) that keeps `Kota kota[8]` and `Jalan jalan[12]`, with the methods `tambahKota`, `tambahJalan(nameA, nameB, km)`, `int jumlahJalan(int k) const` (number of roads), `int totalKm(int k) const`, and `void laporan() const`.\n\n"
         "Input: the list of town names; then m roads — the names of two towns and the length. Roads run both ways.\n\n"
         "For every town, in input order, print `<name>: <number of roads> jalan, <total km> km`. Then print `Tersibuk: <name>` (busiest) — the town with the most roads; on a tie the one earlier in the input.\n\n"
         "Example: towns `Bandung Garut Solo`; roads `Bandung Garut 60`, `Bandung Solo 450` →\n\n```\nBandung: 2 jalan, 510 km\nGarut: 1 jalan, 60 km\nSolo: 1 jalan, 450 km\nTersibuk: Bandung\n```" + W_EN,
         "First line: n and the names of the n towns. Second line: m. Then m lines: two town names and the length of the road.", "One line per town, then `Tersibuk: …`.", "2 ≤ n ≤ 8; 1 ≤ m ≤ 12; no two roads connect the same pair of towns; 5 ≤ km ≤ 300"),
     ar=("خريطة الطرق", "الخريطة **تتكون من** مدن وطرق (تركيب). والطريق **يصل** بين مدينتين (ارتباط): يحفظ مؤشرين إلى مدينتي طرفيه اللتين تعيشان داخل الخريطة.\n\n"
         "أنشئ صنفًا `Kota` (مدينة؛ `string nama`)، وصنفًا `Jalan` (طريق؛ `Kota *dari` (من) و `Kota *ke` (إلى) و `int km`؛ والطريقتان `bool menyentuh(const Kota *k) const` (يمسّ) و `int getKm() const`)، وصنفًا `Peta` (خريطة) يحفظ `Kota kota[8]` و `Jalan jalan[12]` وطرائقه `tambahKota` و `tambahJalan(namaA, namaB, km)` و `int jumlahJalan(int k) const` (عدد الطرق) و `int totalKm(int k) const` و `void laporan() const`.\n\n"
         "المدخل: قائمة أسماء المدن؛ ثم m طريقًا — اسما مدينتين والطول. الطرق تُسلك في الاتجاهين.\n\n"
         "لكل مدينة بترتيب المدخل اطبع `<الاسم>: <عدد الطرق> jalan, <مجموع الكيلومترات> km`. ثم اطبع `Tersibuk: <الاسم>` (الأكثر ازدحامًا) — المدينة ذات أكثر الطرق؛ وعند التساوي الأسبق في المدخل.\n\n"
         "مثال: المدن `Bandung Garut Solo`؛ الطريقان `Bandung Garut 60` و `Bandung Solo 450` ←\n\n```\nBandung: 2 jalan, 510 km\nGarut: 1 jalan, 60 km\nSolo: 1 jalan, 450 km\nTersibuk: Bandung\n```" + W_AR,
         "السطر الأول: n وأسماء المدن. السطر الثاني: m. ثم m سطرًا: اسما مدينتين وطول الطريق.", "سطر لكل مدينة ثم `Tersibuk: …`.", "2 ≤ n ≤ 8؛ 1 ≤ m ≤ 12؛ لا يصل طريقان بين الزوج نفسه من المدن؛ 5 ≤ km ≤ 300"))

soal("struktur-kompi", "sedang", "trimmed",
     "p = r.randint(1, 3)\nprint(p)\nfor _ in range(p):\n    g = r.randint(1, 3)\n    print(g)\n    for _ in range(g):\n        n = r.randint(1, 5)\n        print(n, *[r.randint(10, 100) for _ in range(n)])\n",
     program(r'''
class Prajurit {
public:
    int kesehatan = 0;
};

class Regu {
private:
    Prajurit anggota[5];
    int banyak = 0;

public:
    void tambah(int kesehatan) {
        anggota[banyak].kesehatan = kesehatan;
        banyak++;
    }

    int kekuatan() const {
        auto total = 0;
        for (int k = 0; k < banyak; k++) {
            total += anggota[k].kesehatan;
        }
        return total;
    }
};

class Peleton {
private:
    Regu regu[3];
    int banyak = 0;

public:
    Regu &reguBaru() {
        banyak++;
        return regu[banyak - 1];
    }

    int getBanyak() const {
        return banyak;
    }

    const Regu &ambil(int k) const {
        return regu[k];
    }

    int kekuatan() const {
        auto total = 0;
        for (int k = 0; k < banyak; k++) {
            total += regu[k].kekuatan();
        }
        return total;
    }
};

class Kompi {
private:
    Peleton peleton[3];
    int banyak = 0;

public:
    Peleton &peletonBaru() {
        banyak++;
        return peleton[banyak - 1];
    }

    int kekuatan() const {
        auto total = 0;
        for (int k = 0; k < banyak; k++) {
            total += peleton[k].kekuatan();
        }
        return total;
    }

    void laporan() const {
        auto lemahP = 0;
        auto lemahR = 0;
        for (int p = 0; p < banyak; p++) {
            cout << "Peleton " << p + 1 << ": " << peleton[p].kekuatan() << endl;
            for (int g = 0; g < peleton[p].getBanyak(); g++) {
                if (peleton[p].ambil(g).kekuatan() < peleton[lemahP].ambil(lemahR).kekuatan()) {
                    lemahP = p;
                    lemahR = g;
                }
            }
        }
        cout << "Kompi: " << kekuatan() << endl;
        cout << "Terlemah: peleton " << lemahP + 1 << " regu " << lemahR + 1 << " ("
             << peleton[lemahP].ambil(lemahR).kekuatan() << ")" << endl;
    }
};''', r'''    Kompi kompi;
    int p;
    cin >> p;
    for (int a = 0; a < p; a++) {
        auto &peleton = kompi.peletonBaru();
        int g;
        cin >> g;
        for (int b = 0; b < g; b++) {
            auto &regu = peleton.reguBaru();
            int n;
            cin >> n;
            for (int c = 0; c < n; c++) {
                int sehat;
                cin >> sehat;
                regu.tambah(sehat);
            }
        }
    }
    kompi.laporan();'''), wajib=W,
     id=("Struktur Kompi", "Susun organisasi militer bertingkat dengan komposisi murni: **kompi** terdiri dari peleton, **peleton** terdiri dari regu, **regu** terdiri dari prajurit. Setiap tingkat menyimpan tingkat di bawahnya sebagai array objek, dan menghitung kekuatannya dengan **bertanya kepada bagian-bagiannya**.\n\n"
         "Buat class `Prajurit` (`int kesehatan`), `Regu` (`Prajurit anggota[5]`, `int banyak`), `Peleton` (`Regu regu[3]`, `int banyak`), dan `Kompi` (`Peleton peleton[3]`, `int banyak`). Ketiga class terakhir punya method `int kekuatan() const`: kekuatan regu adalah jumlah kesehatan prajuritnya; kekuatan peleton adalah jumlah kekuatan regunya; dan seterusnya.\n\n"
         "Masukan: banyak peleton p. Untuk tiap peleton: banyak regu g; lalu g baris, masing-masing berisi banyak prajurit n diikuti n nilai kesehatan.\n\n"
         "Cetak `Peleton <k>: <kekuatan>` untuk tiap peleton, `Kompi: <kekuatan>`, dan `Terlemah: peleton <p> regu <g> (<kekuatan>)` — regu dengan kekuatan terkecil di seluruh kompi; bila sama, yang lebih dulu di masukan.\n\n"
         "Contoh: 2 peleton. Peleton 1 punya 2 regu: `2 50 60` dan `1 40`. Peleton 2 punya 1 regu: `3 30 30 30` →\n\n```\nPeleton 1: 150\nPeleton 2: 90\nKompi: 240\nTerlemah: peleton 1 regu 2 (40)\n```" + W_ID,
         "Baris pertama: p. Untuk tiap peleton: satu baris g, lalu g baris regu.", "Satu baris per peleton, lalu `Kompi: …` dan `Terlemah: …`.", "1 ≤ p ≤ 3; 1 ≤ g ≤ 3; 1 ≤ n ≤ 5; kesehatan antara 10 dan 100"),
     en=("Structure of a Company", "Build a military organisation in levels with pure composition: a **company** consists of platoons, a **platoon** of squads, a **squad** of soldiers. Each level keeps the level below as an array of objects, and computes its strength by **asking its parts**.\n\n"
         "Make the classes `Prajurit` (soldier; `int kesehatan`), `Regu` (squad; `Prajurit anggota[5]`, `int banyak`), `Peleton` (platoon; `Regu regu[3]`, `int banyak`) and `Kompi` (company; `Peleton peleton[3]`, `int banyak`). The last three have a method `int kekuatan() const` (strength): a squad's strength is the summed health of its soldiers; a platoon's is the summed strength of its squads; and so on.\n\n"
         "Input: the number of platoons p. For each platoon: the number of squads g; then g lines, each with the number of soldiers n followed by n health values.\n\n"
         "Print `Peleton <k>: <strength>` for each platoon, `Kompi: <strength>`, and `Terlemah: peleton <p> regu <g> (<strength>)` (weakest) — the squad with the least strength in the whole company; on a tie the one earlier in the input.\n\n"
         "Example: 2 platoons. Platoon 1 has 2 squads: `2 50 60` and `1 40`. Platoon 2 has 1 squad: `3 30 30 30` →\n\n```\nPeleton 1: 150\nPeleton 2: 90\nKompi: 240\nTerlemah: peleton 1 regu 2 (40)\n```" + W_EN,
         "First line: p. For each platoon: one line g, then g squad lines.", "One line per platoon, then `Kompi: …` and `Terlemah: …`.", "1 ≤ p ≤ 3; 1 ≤ g ≤ 3; 1 ≤ n ≤ 5; health is between 10 and 100"),
     ar=("بنية السرية", "ابنِ تنظيمًا عسكريًا متدرّجًا بتركيب خالص: **السرية** تتكون من فصائل، و**الفصيل** من مجموعات، و**المجموعة** من جنود. كل مستوى يحفظ المستوى الذي تحته مصفوفة كائنات، ويحسب قوته **بسؤال أجزائه**.\n\n"
         "أنشئ الأصناف `Prajurit` (جندي؛ `int kesehatan`) و `Regu` (مجموعة؛ `Prajurit anggota[5]` و `int banyak`) و `Peleton` (فصيل؛ `Regu regu[3]` و `int banyak`) و `Kompi` (سرية؛ `Peleton peleton[3]` و `int banyak`). وللثلاثة الأخيرة طريقة `int kekuatan() const` (القوة): قوة المجموعة مجموع صحة جنودها؛ وقوة الفصيل مجموع قوى مجموعاته؛ وهكذا.\n\n"
         "المدخل: عدد الفصائل p. لكل فصيل: عدد المجموعات g؛ ثم g سطرًا في كل منها عدد الجنود n تليه n قيمة صحة.\n\n"
         "اطبع `Peleton <k>: <القوة>` لكل فصيل، و `Kompi: <القوة>`، و `Terlemah: peleton <p> regu <g> (<القوة>)` (الأضعف) — المجموعة الأقل قوة في السرية كلها؛ وعند التساوي الأسبق في المدخل.\n\n"
         "مثال: فصيلان. للفصيل 1 مجموعتان: `2 50 60` و `1 40`. وللفصيل 2 مجموعة واحدة: `3 30 30 30` ←\n\n```\nPeleton 1: 150\nPeleton 2: 90\nKompi: 240\nTerlemah: peleton 1 regu 2 (40)\n```" + W_AR,
         "السطر الأول: p. لكل فصيل: سطر فيه g ثم g سطر مجموعات.", "سطر لكل فصيل ثم `Kompi: …` و `Terlemah: …`.", "1 ≤ p ≤ 3؛ 1 ≤ g ≤ 3؛ 1 ≤ n ≤ 5؛ الصحة بين 10 و 100"))

soal("tagihan-meja", "sedang", "trimmed",
     "MENU = ['nasi', 'mie', 'sate', 'soto', 'bakso', 'gado', 'teh', 'kopi', 'jus', 'air']\nk = r.randint(1, 6)\nmenu = r.sample(MENU, k)\nprint(k)\nfor m in menu:\n    print(m, r.randint(3, 40) * 1000)\nlain = [m for m in MENU if m not in menu]\nfor _ in range(r.randint(1, 10)):\n    print(r.choice(menu) if r.random() < 0.8 or not lain else r.choice(lain), r.randint(1, 5))\nprint('selesai')\n",
     program(r'''
class Menu {
public:
    string nama;
    int harga = 0;
};

class Pesanan {
private:
    Menu *menu = nullptr;
    int jumlah = 0;

public:
    void isi(Menu *m, int j) {
        menu = m;
        jumlah = j;
    }

    void tambah(int j) {
        jumlah += j;
    }

    const Menu *getMenu() const {
        return menu;
    }

    int getJumlah() const {
        return jumlah;
    }

    int subtotal() const {
        return menu->harga * jumlah;
    }
};

class Meja {
private:
    Pesanan pesanan[10];
    int banyak = 0;

public:
    void pesan(Menu *m, int j) {
        for (int k = 0; k < banyak; k++) {
            if (pesanan[k].getMenu() == m) {
                pesanan[k].tambah(j);
                return;
            }
        }
        pesanan[banyak].isi(m, j);
        banyak++;
    }

    void tagihan() const {
        auto subtotal = 0;
        for (int k = 0; k < banyak; k++) {
            cout << pesanan[k].getMenu()->nama << " x " << pesanan[k].getJumlah() << " = " << pesanan[k].subtotal() << endl;
            subtotal += pesanan[k].subtotal();
        }
        cout << "Subtotal: " << subtotal << endl;
        cout << "Pajak: " << subtotal / 10 << endl;
        cout << "Total: " << subtotal + subtotal / 10 << endl;
    }
};''', r'''    Menu daftar[6];
    int k;
    cin >> k;
    for (int m = 0; m < k; m++) {
        cin >> daftar[m].nama >> daftar[m].harga;
    }
    Meja meja;
    string nama;
    while (cin >> nama) {
        if (nama == "selesai") {
            break;
        }
        int jumlah;
        cin >> jumlah;
        Menu *ketemu = nullptr;
        for (int m = 0; m < k; m++) {
            if (daftar[m].nama == nama) {
                ketemu = &daftar[m];
            }
        }
        if (ketemu == nullptr) {
            cout << "Menu tidak ada: " << nama << endl;
        } else {
            meja.pesan(ketemu, jumlah);
        }
    }
    meja.tagihan();''', "string"), wajib=W,
     id=("Tagihan Meja", "Di sebuah rumah makan ada tiga macam objek dengan relasi berbeda:\n\n"
         "- `Menu` (`string nama`, `int harga`) — daftar menu milik rumah makan, dipakai bersama oleh semua meja;\n"
         "- `Pesanan` — satu baris pesanan: **merujuk** ke sebuah `Menu` (`Menu *menu`, asosiasi) dan mencatat `int jumlah`; method `int subtotal() const`;\n"
         "- `Meja` — **terdiri dari** pesanan-pesanannya (`Pesanan pesanan[10]`, komposisi); method `void pesan(Menu *m, int j)` dan `void tagihan() const`.\n\n"
         "Bila menu yang sama dipesan lagi di meja itu, jumlahnya **ditambahkan ke baris yang sudah ada**, bukan menjadi baris baru.\n\n"
         "Masukan: k menu (nama dan harga); lalu pesanan `<nama> <jumlah>`, diakhiri `selesai`. Pesanan untuk nama yang tidak ada di daftar menu ditolak dengan `Menu tidak ada: <nama>`.\n\n"
         "Tagihan mencetak tiap baris pesanan sebagai `<nama> x <jumlah> = <subtotal>` menurut urutan pertama kali dipesan, lalu `Subtotal`, `Pajak` (10 persen), dan `Total`.\n\n"
         "Contoh: menu `nasi 15000`, `teh 5000`; pesanan `nasi 2`, `kopi 1`, `teh 3`, `nasi 1`, `selesai` →\n\n```\nMenu tidak ada: kopi\nnasi x 3 = 45000\nteh x 3 = 15000\nSubtotal: 60000\nPajak: 6000\nTotal: 66000\n```" + W_ID,
         "Baris pertama: k. Lalu k baris menu. Lalu baris-baris pesanan, diakhiri `selesai`.", "Penolakan bila ada, lalu tagihan.", "1 ≤ k ≤ 6; harga kelipatan 1000 antara 3000 dan 40000; paling banyak 10 pesanan; 1 ≤ jumlah ≤ 5"),
     en=("A Table's Bill", "In a restaurant there are three kinds of objects with different relations:\n\n"
         "- `Menu` (`string nama`, `int harga`) — the restaurant's menu list, shared by all tables;\n"
         "- `Pesanan` (order line) — **refers** to a `Menu` (`Menu *menu`, association) and records `int jumlah` (quantity); method `int subtotal() const`;\n"
         "- `Meja` (table) — **consists of** its order lines (`Pesanan pesanan[10]`, composition); methods `void pesan(Menu *m, int j)` (order) and `void tagihan() const` (bill).\n\n"
         "When the same menu item is ordered again at the table, the quantity is **added to the existing line**; it does not become a new line.\n\n"
         "Input: k menu items (name and price); then orders `<name> <quantity>`, ending with `selesai`. An order for a name not on the menu is refused with `Menu tidak ada: <name>`.\n\n"
         "The bill prints every order line as `<name> x <quantity> = <subtotal>` in the order of first ordering, then `Subtotal`, `Pajak` (tax, 10 percent) and `Total`.\n\n"
         "Example: menu `nasi 15000`, `teh 5000`; orders `nasi 2`, `kopi 1`, `teh 3`, `nasi 1`, `selesai` →\n\n```\nMenu tidak ada: kopi\nnasi x 3 = 45000\nteh x 3 = 15000\nSubtotal: 60000\nPajak: 6000\nTotal: 66000\n```" + W_EN,
         "First line: k. Then k menu lines. Then the order lines, ending with `selesai`.", "Refusals if any, then the bill.", "1 ≤ k ≤ 6; prices are multiples of 1000 between 3000 and 40000; at most 10 orders; 1 ≤ quantity ≤ 5"),
     ar=("فاتورة الطاولة", "في مطعم ثلاثة أنواع من الكائنات بعلاقات مختلفة:\n\n"
         "- `Menu` (`string nama` و `int harga`) — قائمة طعام المطعم، مشتركة بين كل الطاولات؛\n"
         "- `Pesanan` (سطر طلب) — **يشير** إلى `Menu` (`Menu *menu`، ارتباط) ويسجّل `int jumlah` (الكمية)؛ والطريقة `int subtotal() const`؛\n"
         "- `Meja` (طاولة) — **تتكون من** أسطر طلبها (`Pesanan pesanan[10]`، تركيب)؛ والطريقتان `void pesan(Menu *m, int j)` (اطلب) و `void tagihan() const` (الفاتورة).\n\n"
         "إذا طُلب الصنف نفسه ثانية على الطاولة **أُضيفت الكمية إلى السطر الموجود** ولم يُنشأ سطر جديد.\n\n"
         "المدخل: k صنفًا (الاسم والسعر)؛ ثم طلبات `<الاسم> <الكمية>` تنتهي بـ `selesai`. الطلب لاسم غير موجود في القائمة يُرفض بـ `Menu tidak ada: <الاسم>`.\n\n"
         "تطبع الفاتورة كل سطر طلب على الصورة `<الاسم> x <الكمية> = <المجموع الفرعي>` بترتيب أول طلب، ثم `Subtotal` و `Pajak` (الضريبة 10 بالمئة) و `Total`.\n\n"
         "مثال: القائمة `nasi 15000` و `teh 5000`؛ الطلبات `nasi 2` و `kopi 1` و `teh 3` و `nasi 1` ثم `selesai` ←\n\n```\nMenu tidak ada: kopi\nnasi x 3 = 45000\nteh x 3 = 15000\nSubtotal: 60000\nPajak: 6000\nTotal: 66000\n```" + W_AR,
         "السطر الأول: k. ثم k سطر أصناف. ثم أسطر الطلبات منتهيةً بـ `selesai`.", "الرفض إن وُجد ثم الفاتورة.", "1 ≤ k ≤ 6؛ الأسعار من مضاعفات 1000 بين 3000 و 40000؛ 10 طلبات على الأكثر؛ 1 ≤ الكمية ≤ 5"))

soal("kereta-gerbong", "sedang", "trimmed",
     "g = r.randint(1, 6)\nprint(g, *[r.randint(2, 20) for _ in range(g)])\nn = r.randint(1, 8)\nprint(n)\nprint(*[r.randint(1, 25) for _ in range(n)])\n",
     program(r'''
class Gerbong {
private:
    int kapasitas = 0;
    int terisi = 0;

public:
    void setKapasitas(int k) {
        kapasitas = k;
    }

    int naik(int orang) {
        auto bisa = kapasitas - terisi;
        if (orang < bisa) {
            bisa = orang;
        }
        terisi += bisa;
        return bisa;
    }

    int getTerisi() const {
        return terisi;
    }

    int getKapasitas() const {
        return kapasitas;
    }
};

class Kereta {
private:
    Gerbong gerbong[6];
    int banyak = 0;

public:
    void tambahGerbong(int kapasitas) {
        gerbong[banyak].setKapasitas(kapasitas);
        banyak++;
    }

    void naikRombongan(int nomor, int orang) {
        cout << "Rombongan " << nomor << ":";
        for (int k = 0; k < banyak && orang > 0; k++) {
            auto masuk = gerbong[k].naik(orang);
            if (masuk > 0) {
                cout << " gerbong " << k + 1 << " (" << masuk << ")";
                orang -= masuk;
            }
        }
        if (orang > 0) {
            cout << " tertinggal " << orang;
        }
        cout << endl;
    }

    void laporan() const {
        for (int k = 0; k < banyak; k++) {
            cout << "Gerbong " << k + 1 << ": " << gerbong[k].getTerisi() << "/" << gerbong[k].getKapasitas() << endl;
        }
    }
};''', r'''    Kereta kereta;
    int g, n;
    cin >> g;
    for (int k = 0; k < g; k++) {
        int kapasitas;
        cin >> kapasitas;
        kereta.tambahGerbong(kapasitas);
    }
    cin >> n;
    for (int k = 1; k <= n; k++) {
        int orang;
        cin >> orang;
        kereta.naikRombongan(k, orang);
    }
    kereta.laporan();'''), wajib=W,
     id=("Kereta dan Gerbong", "Sebuah kereta **terdiri dari** gerbong-gerbong. Tiap gerbong tahu kapasitasnya dan berapa kursi yang terisi; kereta tidak mengutak-atik angka itu sendiri, ia hanya **meminta** tiap gerbong menaikkan penumpang.\n\n"
         "Buat class `Gerbong` (atribut `private` `int kapasitas`, `int terisi`) dengan method `int naik(int orang)` — menaikkan sebanyak yang muat dan **mengembalikan banyak orang yang benar-benar naik**.\n\n"
         "Buat class `Kereta` yang menyimpan `Gerbong gerbong[6]` dan `int banyak`, dengan method `void naikRombongan(int nomor, int orang)`: rombongan mengisi gerbong **dari yang paling depan** yang masih punya tempat, dan boleh terpecah ke beberapa gerbong. Method itu mencetak satu baris:\n\n"
         "`Rombongan <nomor>:` diikuti ` gerbong <k> (<banyak>)` untuk tiap gerbong yang dinaiki, dan ` tertinggal <banyak>` bila ada yang tidak mendapat tempat.\n\n"
         "Di akhir, `void laporan() const` mencetak `Gerbong <k>: <terisi>/<kapasitas>` untuk tiap gerbong.\n\n"
         "Contoh: gerbong berkapasitas `4 3`; rombongan `3 5 2` →\n\n```\nRombongan 1: gerbong 1 (3)\nRombongan 2: gerbong 1 (1) gerbong 2 (3) tertinggal 1\nRombongan 3: tertinggal 2\nGerbong 1: 4/4\nGerbong 2: 3/3\n```" + W_ID,
         "Baris pertama: banyak gerbong g dan kapasitas tiap gerbong. Baris kedua: n. Baris ketiga: besar tiap rombongan.", "Satu baris per rombongan, lalu satu baris per gerbong.", "1 ≤ g ≤ 6; kapasitas antara 2 dan 20; 1 ≤ n ≤ 8; rombongan antara 1 dan 25 orang"),
     en=("Train and Carriages", "A train **consists of** carriages. Each carriage knows its capacity and how many seats are taken; the train does not fiddle with those numbers itself, it only **asks** each carriage to take passengers.\n\n"
         "Make a class `Gerbong` (carriage; `private` attributes `int kapasitas`, `int terisi` (taken)) with a method `int naik(int orang)` (board) — takes as many as fit and **returns the number of people who actually boarded**.\n\n"
         "Make a class `Kereta` (train) that keeps `Gerbong gerbong[6]` and `int banyak`, with a method `void naikRombongan(int nomor, int orang)` (board a group): a group fills the carriages **from the front-most** one that still has room, and may be split over several carriages. The method prints one line:\n\n"
         "`Rombongan <number>:` followed by ` gerbong <k> (<count>)` for every carriage boarded, and ` tertinggal <count>` (left behind) when some found no seat.\n\n"
         "At the end `void laporan() const` prints `Gerbong <k>: <taken>/<capacity>` for every carriage.\n\n"
         "Example: carriages of capacity `4 3`; groups `3 5 2` →\n\n```\nRombongan 1: gerbong 1 (3)\nRombongan 2: gerbong 1 (1) gerbong 2 (3) tertinggal 1\nRombongan 3: tertinggal 2\nGerbong 1: 4/4\nGerbong 2: 3/3\n```" + W_EN,
         "First line: the number of carriages g and the capacity of each. Second line: n. Third line: the size of each group.", "One line per group, then one line per carriage.", "1 ≤ g ≤ 6; capacities are between 2 and 20; 1 ≤ n ≤ 8; groups have 1 to 25 people"),
     ar=("القطار والعربات", "القطار **يتكون من** عربات. كل عربة تعرف سعتها وعدد مقاعدها المشغولة؛ والقطار لا يعبث بهذه الأرقام بنفسه، بل **يطلب** من كل عربة أن تُركب الركاب.\n\n"
         "أنشئ صنفًا `Gerbong` (عربة؛ الخاصيتان الخاصتان `int kapasitas` و `int terisi` (المشغول)) وطريقة `int naik(int orang)` (اركب) — تُركب ما يتسع و**تُرجع عدد من ركبوا فعلًا**.\n\n"
         "أنشئ صنفًا `Kereta` (قطار) يحفظ `Gerbong gerbong[6]` و `int banyak`، وطريقة `void naikRombongan(int nomor, int orang)` (أركب مجموعة): تملأ المجموعة العربات **من الأولى** التي ما زال فيها متسع، وقد تتوزع على عدة عربات. تطبع الطريقة سطرًا واحدًا:\n\n"
         "`Rombongan <الرقم>:` تليها ` gerbong <k> (<العدد>)` لكل عربة رُكبت، و ` tertinggal <العدد>` (تخلّف) إذا لم يجد بعضهم مكانًا.\n\n"
         "وفي النهاية تطبع `void laporan() const` السطر `Gerbong <k>: <المشغول>/<السعة>` لكل عربة.\n\n"
         "مثال: عربتان سعتاهما `4 3`؛ المجموعات `3 5 2` ←\n\n```\nRombongan 1: gerbong 1 (3)\nRombongan 2: gerbong 1 (1) gerbong 2 (3) tertinggal 1\nRombongan 3: tertinggal 2\nGerbong 1: 4/4\nGerbong 2: 3/3\n```" + W_AR,
         "السطر الأول: عدد العربات g وسعة كل عربة. السطر الثاني: n. السطر الثالث: حجم كل مجموعة.", "سطر لكل مجموعة ثم سطر لكل عربة.", "1 ≤ g ≤ 6؛ السعة بين 2 و 20؛ 1 ≤ n ≤ 8؛ المجموعة من 1 إلى 25 شخصًا"))

soal("tim-proyek", "sedang", "trimmed",
     NAMA + "karyawan = r.sample(NAMA, r.randint(2, 6))\nproyek = r.sample(['Atlas', 'Borneo', 'Citra', 'Delta'], r.randint(1, 4))\nprint(len(karyawan), *karyawan)\nprint(len(proyek), *proyek)\nfor _ in range(r.randint(1, 18)):\n    print('gabung' if r.random() < 0.7 else 'keluar', r.choice(karyawan), r.choice(proyek))\nprint('selesai')\n",
     program(r'''
class Proyek;

class Karyawan {
public:
    string nama;
    Proyek *proyek[2];
    int banyak = 0;
};

class Proyek {
public:
    string nama;
    Karyawan *anggota[3];
    int banyak = 0;

    bool punya(const Karyawan *k) const {
        for (int p = 0; p < banyak; p++) {
            if (anggota[p] == k) {
                return true;
            }
        }
        return false;
    }
};

class Kantor {
private:
    Karyawan karyawan[6];
    Proyek proyek[4];
    int banyakKaryawan = 0;
    int banyakProyek = 0;

    Karyawan *cariKaryawan(const string &nama) {
        for (int k = 0; k < banyakKaryawan; k++) {
            if (karyawan[k].nama == nama) {
                return &karyawan[k];
            }
        }
        return nullptr;
    }

    Proyek *cariProyek(const string &nama) {
        for (int k = 0; k < banyakProyek; k++) {
            if (proyek[k].nama == nama) {
                return &proyek[k];
            }
        }
        return nullptr;
    }

public:
    void tambahKaryawan(const string &nama) {
        karyawan[banyakKaryawan].nama = nama;
        banyakKaryawan++;
    }

    void tambahProyek(const string &nama) {
        proyek[banyakProyek].nama = nama;
        banyakProyek++;
    }

    string gabung(const string &namaK, const string &namaP) {
        auto k = cariKaryawan(namaK);
        auto p = cariProyek(namaP);
        if (p->punya(k)) {
            return "Sudah bergabung";
        }
        if (k->banyak == 2) {
            return "Karyawan penuh";
        }
        if (p->banyak == 3) {
            return "Proyek penuh";
        }
        k->proyek[k->banyak] = p;
        k->banyak++;
        p->anggota[p->banyak] = k;
        p->banyak++;
        return "Bergabung";
    }

    string keluar(const string &namaK, const string &namaP) {
        auto k = cariKaryawan(namaK);
        auto p = cariProyek(namaP);
        if (!p->punya(k)) {
            return "Bukan anggota";
        }
        auto geser = false;
        for (int a = 0; a < p->banyak; a++) {
            if (p->anggota[a] == k) {
                geser = true;
            }
            if (geser && a + 1 < p->banyak) {
                p->anggota[a] = p->anggota[a + 1];
            }
        }
        p->banyak--;
        geser = false;
        for (int a = 0; a < k->banyak; a++) {
            if (k->proyek[a] == p) {
                geser = true;
            }
            if (geser && a + 1 < k->banyak) {
                k->proyek[a] = k->proyek[a + 1];
            }
        }
        k->banyak--;
        return "Keluar";
    }

    void laporan() const {
        for (int p = 0; p < banyakProyek; p++) {
            cout << proyek[p].nama << ":";
            if (proyek[p].banyak == 0) {
                cout << " -";
            }
            for (int a = 0; a < proyek[p].banyak; a++) {
                cout << " " << proyek[p].anggota[a]->nama;
            }
            cout << endl;
        }
    }
};''', r'''    Kantor kantor;
    int n;
    cin >> n;
    for (int k = 0; k < n; k++) {
        string nama;
        cin >> nama;
        kantor.tambahKaryawan(nama);
    }
    cin >> n;
    for (int k = 0; k < n; k++) {
        string nama;
        cin >> nama;
        kantor.tambahProyek(nama);
    }
    string perintah;
    while (cin >> perintah) {
        if (perintah == "selesai") {
            break;
        }
        string namaK, namaP;
        cin >> namaK >> namaP;
        if (perintah == "gabung") {
            cout << kantor.gabung(namaK, namaP) << endl;
        } else {
            cout << kantor.keluar(namaK, namaP) << endl;
        }
    }
    kantor.laporan();''', "string"), wajib=W,
     id=("Tim Proyek", "Karyawan dan proyek saling berhubungan **banyak-ke-banyak** dan **dua arah**: seorang karyawan boleh ikut paling banyak **2** proyek, dan sebuah proyek beranggotakan paling banyak **3** karyawan. Tiap sisi menyimpan penunjuk ke sisi lainnya, dan kedua catatan itu harus selalu cocok.\n\n"
         "Buat class `Karyawan` (`string nama`, `Proyek *proyek[2]`, `int banyak`) dan class `Proyek` (`string nama`, `Karyawan *anggota[3]`, `int banyak`). Karena keduanya saling menyebut, salah satu perlu dideklarasikan lebih dulu: `class Proyek;`.\n\n"
         "Buat class `Kantor` yang **memiliki** semua karyawan dan proyek (`Karyawan karyawan[6]`, `Proyek proyek[4]`) dan mengurus relasinya:\n\n"
         "- `string gabung(namaKaryawan, namaProyek)` — diperiksa berurutan: sudah anggota → `Sudah bergabung`; karyawan sudah di 2 proyek → `Karyawan penuh`; proyek sudah 3 anggota → `Proyek penuh`; selain itu catat **di kedua sisi** → `Bergabung`;\n"
         "- `string keluar(namaKaryawan, namaProyek)` → `Keluar` (hapus di kedua sisi; anggota lain bergeser merapat), atau `Bukan anggota`;\n"
         "- `void laporan() const` — tiap proyek: `<nama>:` diikuti nama anggotanya menurut urutan bergabung, atau ` -` bila kosong.\n\n"
         "Contoh: karyawan `Andi Budi`, proyek `Atlas Delta`; `gabung Andi Atlas`, `gabung Andi Atlas`, `gabung Budi Atlas`, `keluar Budi Delta`, `keluar Andi Atlas`, `selesai` →\n\n```\nBergabung\nSudah bergabung\nBergabung\nBukan anggota\nKeluar\nAtlas: Budi\nDelta: -\n```" + W_ID,
         "Baris pertama: banyak karyawan dan namanya. Baris kedua: banyak proyek dan namanya. Lalu perintah `gabung`/`keluar`, diakhiri `selesai`.", "Satu baris per perintah, lalu satu baris per proyek.", "2 sampai 6 karyawan; 1 sampai 4 proyek; paling banyak 18 perintah; nama dalam perintah selalu terdaftar."),
     en=("Project Teams", "Employees and projects are related **many-to-many** and **in both directions**: an employee may be in at most **2** projects, and a project has at most **3** members. Each side keeps pointers to the other, and the two records must always agree.\n\n"
         "Make a class `Karyawan` (employee; `string nama`, `Proyek *proyek[2]`, `int banyak`) and a class `Proyek` (project; `string nama`, `Karyawan *anggota[3]`, `int banyak`). As each names the other, one has to be declared first: `class Proyek;`.\n\n"
         "Make a class `Kantor` (office) that **owns** all employees and projects (`Karyawan karyawan[6]`, `Proyek proyek[4]`) and manages the relation:\n\n"
         "- `string gabung(employee, project)` (join) — checked in order: already a member → `Sudah bergabung`; the employee is in 2 projects → `Karyawan penuh`; the project has 3 members → `Proyek penuh`; otherwise record it **on both sides** → `Bergabung`;\n"
         "- `string keluar(employee, project)` (leave) → `Keluar` (removed on both sides; the others close up), or `Bukan anggota` (not a member);\n"
         "- `void laporan() const` — for every project: `<name>:` followed by its members in joining order, or ` -` when empty.\n\n"
         "Example: employees `Andi Budi`, projects `Atlas Delta`; `gabung Andi Atlas`, `gabung Andi Atlas`, `gabung Budi Atlas`, `keluar Budi Delta`, `keluar Andi Atlas`, `selesai` →\n\n```\nBergabung\nSudah bergabung\nBergabung\nBukan anggota\nKeluar\nAtlas: Budi\nDelta: -\n```" + W_EN,
         "First line: the number of employees and their names. Second line: the number of projects and their names. Then `gabung`/`keluar` commands, ending with `selesai`.", "One line per command, then one line per project.", "2 to 6 employees; 1 to 4 projects; at most 18 commands; names in commands are always registered."),
     ar=("فِرق المشاريع", "الموظفون والمشاريع بينهم علاقة **كثير-إلى-كثير** و**في الاتجاهين**: الموظف يشارك في **مشروعين** على الأكثر، والمشروع له **3** أعضاء على الأكثر. كل طرف يحفظ مؤشرات إلى الآخر، ويجب أن يتوافق السجلان دائمًا.\n\n"
         "أنشئ صنفًا `Karyawan` (موظف؛ `string nama` و `Proyek *proyek[2]` و `int banyak`) وصنفًا `Proyek` (مشروع؛ `string nama` و `Karyawan *anggota[3]` و `int banyak`). ولأن كلًّا منهما يذكر الآخر فلا بد من التصريح بأحدهما أولًا: `class Proyek;`.\n\n"
         "أنشئ صنفًا `Kantor` (مكتب) **يملك** كل الموظفين والمشاريع (`Karyawan karyawan[6]` و `Proyek proyek[4]`) ويدير العلاقة:\n\n"
         "- `string gabung(الموظف, المشروع)` (انضمّ) — يُفحص بالترتيب: عضو أصلًا ← `Sudah bergabung`؛ الموظف في مشروعين ← `Karyawan penuh`؛ المشروع له 3 أعضاء ← `Proyek penuh`؛ وإلا سُجِّل **في الطرفين** ← `Bergabung`؛\n"
         "- `string keluar(الموظف, المشروع)` (غادر) ← `Keluar` (يُحذف من الطرفين ويتراصّ الباقون) أو `Bukan anggota` (ليس عضوًا)؛\n"
         "- `void laporan() const` — لكل مشروع: `<الاسم>:` يليها أعضاؤه بترتيب الانضمام، أو ` -` إذا كان فارغًا.\n\n"
         "مثال: الموظفان `Andi Budi` والمشروعان `Atlas Delta`؛ `gabung Andi Atlas` ثم `gabung Andi Atlas` ثم `gabung Budi Atlas` ثم `keluar Budi Delta` ثم `keluar Andi Atlas` ثم `selesai` ←\n\n```\nBergabung\nSudah bergabung\nBergabung\nBukan anggota\nKeluar\nAtlas: Budi\nDelta: -\n```" + W_AR,
         "السطر الأول: عدد الموظفين وأسماؤهم. السطر الثاني: عدد المشاريع وأسماؤها. ثم أوامر `gabung`/`keluar` تنتهي بـ `selesai`.", "سطر لكل أمر ثم سطر لكل مشروع.", "من 2 إلى 6 موظفين؛ من 1 إلى 4 مشاريع؛ 18 أمرًا على الأكثر؛ الأسماء في الأوامر مسجَّلة دائمًا."))

soal("biaya-rakitan", "sedang", "trimmed",
     "BAHAN = ['baut', 'mur', 'papan', 'roda', 'kabel', 'lampu', 'engsel', 'cat']\nb = r.randint(1, 6)\nbahan = r.sample(BAHAN, b)\nprint(b)\nfor n in bahan:\n    print(n, r.randint(1, 50) * 500)\nq = r.randint(1, 5)\nprint(q)\nfor k in range(q):\n    m = r.randint(1, min(4, b))\n    isi = []\n    for x in r.sample(range(1, b + 1), m):\n        isi += [x, r.randint(1, 9)]\n    print(['Kursi', 'Meja', 'Rak', 'Lemari', 'Troli'][k], r.randint(0, 20) * 1000, m, *isi)\n",
     program(r'''
class Bahan {
public:
    string nama;
    int harga = 0;
};

class Rakitan {
private:
    string nama;
    int ongkos = 0;
    Bahan *bahan[4];
    int jumlah[4];
    int banyak = 0;

public:
    void isi(const string &n, int o) {
        nama = n;
        ongkos = o;
    }

    void pakai(Bahan *b, int j) {
        bahan[banyak] = b;
        jumlah[banyak] = j;
        banyak++;
    }

    string getNama() const {
        return nama;
    }

    int biaya() const {
        auto total = ongkos;
        for (int k = 0; k < banyak; k++) {
            total += bahan[k]->harga * jumlah[k];
        }
        return total;
    }
};''', r'''    Bahan gudang[6];
    Rakitan rakitan[5];
    int b, q;
    cin >> b;
    for (int k = 0; k < b; k++) {
        cin >> gudang[k].nama >> gudang[k].harga;
    }
    cin >> q;
    auto mahal = 0;
    for (int k = 0; k < q; k++) {
        string nama;
        int ongkos, m;
        cin >> nama >> ongkos >> m;
        rakitan[k].isi(nama, ongkos);
        for (int p = 0; p < m; p++) {
            int nomor, jumlah;
            cin >> nomor >> jumlah;
            rakitan[k].pakai(&gudang[nomor - 1], jumlah);
        }
        cout << rakitan[k].getNama() << ": " << rakitan[k].biaya() << endl;
        if (rakitan[k].biaya() > rakitan[mahal].biaya()) {
            mahal = k;
        }
    }
    cout << "Termahal: " << rakitan[mahal].getNama() << endl;''', "string"), wajib=W,
     id=("Biaya Rakitan", "Sebuah bengkel merakit perabot dari bahan-bahan di gudang. Bahan yang sama dipakai oleh banyak rakitan, jadi `Rakitan` tidak menyalin bahan — ia menyimpan **penunjuk ke bahan** beserta jumlah yang dipakai. Bila harga bahan di gudang berubah, biaya semua rakitan ikut berubah.\n\n"
         "Buat class `Bahan` (`string nama`, `int harga`) dan class `Rakitan` dengan atribut `string nama`, `int ongkos`, `Bahan *bahan[4]`, `int jumlah[4]`, `int banyak`, serta method `void pakai(Bahan *b, int j)` dan `int biaya() const` — ongkos kerja ditambah harga × jumlah tiap bahan.\n\n"
         "Masukan: b bahan (nama dan harga); lalu q rakitan, masing-masing satu baris: nama, ongkos, banyak bahan m, lalu m pasang `nomor bahan` dan `jumlah`.\n\n"
         "Cetak `<nama>: <biaya>` untuk tiap rakitan, lalu `Termahal: <nama>`; bila ada yang sama, yang lebih dulu di masukan.\n\n"
         "Contoh: bahan `papan 20000`, `baut 500`; rakitan `Rak 10000 2 1 3 2 12` dan `Kursi 5000 1 1 1` →\n\n```\nRak: 76000\nKursi: 25000\nTermahal: Rak\n```" + W_ID,
         "Baris pertama: b. Lalu b baris bahan. Lalu q. Lalu q baris rakitan.", "Satu baris per rakitan, lalu `Termahal: …`.", "1 ≤ b ≤ 6; 1 ≤ q ≤ 5; 1 ≤ m ≤ 4; harga kelipatan 500 sampai 25000; ongkos sampai 20000; jumlah 1 sampai 9"),
     en=("Assembly Cost", "A workshop assembles furniture from materials in its store. The same material is used by many assemblies, so `Rakitan` does not copy materials — it keeps **pointers to the materials** together with the quantity used. If a price in the store changes, the cost of every assembly changes with it.\n\n"
         "Make a class `Bahan` (material; `string nama`, `int harga`) and a class `Rakitan` (assembly) with the attributes `string nama`, `int ongkos` (labour), `Bahan *bahan[4]`, `int jumlah[4]`, `int banyak`, and the methods `void pakai(Bahan *b, int j)` (use) and `int biaya() const` (cost) — the labour plus price × quantity of every material.\n\n"
         "Input: b materials (name and price); then q assemblies, one line each: name, labour, number of materials m, then m pairs `material number` and `quantity`.\n\n"
         "Print `<name>: <cost>` for every assembly, then `Termahal: <name>` (most expensive); on a tie the one earlier in the input.\n\n"
         "Example: materials `papan 20000`, `baut 500`; assemblies `Rak 10000 2 1 3 2 12` and `Kursi 5000 1 1 1` →\n\n```\nRak: 76000\nKursi: 25000\nTermahal: Rak\n```" + W_EN,
         "First line: b. Then b material lines. Then q. Then q assembly lines.", "One line per assembly, then `Termahal: …`.", "1 ≤ b ≤ 6; 1 ≤ q ≤ 5; 1 ≤ m ≤ 4; prices are multiples of 500 up to 25000; labour up to 20000; quantities 1 to 9"),
     ar=("تكلفة التجميع", "ورشة تجمّع الأثاث من مواد في مخزنها. المادة نفسها تستعملها تجميعات كثيرة، فلا ينسخ `Rakitan` المواد — بل يحفظ **مؤشرات إلى المواد** مع الكمية المستعملة. فإذا تغيّر سعر في المخزن تغيّرت تكلفة كل التجميعات معه.\n\n"
         "أنشئ صنفًا `Bahan` (مادة؛ `string nama` و `int harga`) وصنفًا `Rakitan` (تجميعة) خصائصه `string nama` و `int ongkos` (أجرة العمل) و `Bahan *bahan[4]` و `int jumlah[4]` و `int banyak`، وطريقتاه `void pakai(Bahan *b, int j)` (استعمل) و `int biaya() const` (التكلفة) — الأجرة زائد السعر × الكمية لكل مادة.\n\n"
         "المدخل: b مادة (الاسم والسعر)؛ ثم q تجميعة، كلٌّ في سطر: الاسم والأجرة وعدد المواد m ثم m زوجًا `رقم المادة` و `الكمية`.\n\n"
         "اطبع `<الاسم>: <التكلفة>` لكل تجميعة ثم `Termahal: <الاسم>` (الأغلى)؛ وعند التساوي الأسبق في المدخل.\n\n"
         "مثال: المادتان `papan 20000` و `baut 500`؛ التجميعتان `Rak 10000 2 1 3 2 12` و `Kursi 5000 1 1 1` ←\n\n```\nRak: 76000\nKursi: 25000\nTermahal: Rak\n```" + W_AR,
         "السطر الأول: b. ثم b سطر مواد. ثم q. ثم q سطر تجميعات.", "سطر لكل تجميعة ثم `Termahal: …`.", "1 ≤ b ≤ 6؛ 1 ≤ q ≤ 5؛ 1 ≤ m ≤ 4؛ الأسعار من مضاعفات 500 حتى 25000؛ الأجرة حتى 20000؛ الكميات من 1 إلى 9"))

# ================================= SULIT (4) =================================

soal("simulasi-kepemilikan", "sulit", "trimmed",
     "bagian = list('abcdefgh')\npemilik = list('PQRS')\nr.shuffle(bagian)\nr.shuffle(pemilik)\nnb, np = 0, 0\nfor _ in range(r.randint(2, 18)):\n    q = r.random()\n    if q < 0.3 and nb < 8:\n        print('bagian', bagian[nb])\n        nb += 1\n    elif q < 0.45 and np < 4:\n        print('pemilik', pemilik[np])\n        np += 1\n    elif q < 0.7:\n        print('miliki', r.choice(pemilik), r.choice(bagian))\n    elif q < 0.85:\n        print('pinjam', r.choice(pemilik), r.choice(bagian))\n    else:\n        print('hapus', r.choice(pemilik))\nprint('selesai')\n",
     program(r'''
class Bagian {
private:
    string nama;

public:
    Bagian(const string &n) : nama(n) {
        cout << "+" << nama << endl;
    }

    ~Bagian() {
        cout << "-" << nama << endl;
    }

    string getNama() const {
        return nama;
    }
};

class Pemilik {
private:
    string nama;
    Bagian *milik[8];
    int banyakMilik = 0;
    Bagian *pinjaman[20];
    int banyakPinjaman = 0;

public:
    Pemilik(const string &n) : nama(n) {
        cout << "+" << nama << endl;
    }

    ~Pemilik() {
        cout << "-" << nama << endl;
        for (int k = 0; k < banyakMilik; k++) {
            delete milik[k];
        }
    }

    string getNama() const {
        return nama;
    }

    void miliki(Bagian *b) {
        milik[banyakMilik] = b;
        banyakMilik++;
    }

    void pinjam(Bagian *b) {
        pinjaman[banyakPinjaman] = b;
        banyakPinjaman++;
    }
};''', r'''    Bagian *bebas[8];
    Pemilik *pemilik[4];
    auto nb = 0;
    auto np = 0;
    string perintah;
    while (cin >> perintah) {
        if (perintah == "selesai") {
            break;
        }
        string a;
        cin >> a;
        if (perintah == "bagian") {
            bebas[nb] = new Bagian(a);
            nb++;
            continue;
        }
        if (perintah == "pemilik") {
            pemilik[np] = new Pemilik(a);
            np++;
            continue;
        }
        auto p = -1;
        for (int k = 0; k < np; k++) {
            if (pemilik[k] != nullptr && pemilik[k]->getNama() == a) {
                p = k;
            }
        }
        if (perintah == "hapus") {
            if (p < 0) {
                cout << "Tidak bisa" << endl;
            } else {
                delete pemilik[p];
                pemilik[p] = nullptr;
            }
            continue;
        }
        string b;
        cin >> b;
        auto x = -1;
        for (int k = 0; k < nb; k++) {
            if (bebas[k] != nullptr && bebas[k]->getNama() == b) {
                x = k;
            }
        }
        if (p < 0 || x < 0) {
            cout << "Tidak bisa" << endl;
        } else if (perintah == "miliki") {
            pemilik[p]->miliki(bebas[x]);
            bebas[x] = nullptr;
        } else {
            pemilik[p]->pinjam(bebas[x]);
        }
    }
    for (int k = 0; k < np; k++) {
        if (pemilik[k] != nullptr) {
            delete pemilik[k];
        }
    }
    for (int k = 0; k < nb; k++) {
        if (bebas[k] != nullptr) {
            delete bebas[k];
        }
    }''', "string"), wajib=W,
     id=("Simulasi Kepemilikan", "Pertanyaan terpenting tentang relasi dua objek adalah: **siapa yang bertanggung jawab menghancurkannya?** Pemilik (komposisi) menghancurkan bagiannya; peminjam (agregasi) tidak.\n\n"
         "Buat class `Bagian` (atribut `string nama`; konstruktor mencetak `+<nama>`, destruktor `-<nama>`).\n\n"
         "Buat class `Pemilik` yang menyimpan dua daftar penunjuk ke `Bagian`: `milik` (dimiliki) dan `pinjaman` (dipinjam). Konstruktornya mencetak `+<nama>`. **Destruktornya mencetak `-<nama>`, lalu men-`delete` semua bagian yang dimilikinya menurut urutan dimiliki; bagian pinjaman tidak disentuh.**\n\n"
         "Perintah:\n\n- `bagian X` — buat bagian bebas bernama X;\n- `pemilik P` — buat pemilik bernama P;\n- `miliki P X` — P mengambil alih bagian **bebas** X; sejak itu X bukan bagian bebas lagi;\n- `pinjam P X` — P mencatat bagian bebas X sebagai pinjaman; X tetap bebas;\n"
         "- `hapus P` — hancurkan pemilik P;\n- `selesai` — hancurkan semua pemilik yang tersisa menurut urutan pembuatan, lalu semua bagian yang masih bebas menurut urutan pembuatan.\n\n"
         "Perintah `miliki`, `pinjam`, atau `hapus` yang menyebut pemilik yang tidak ada (belum dibuat atau sudah dihapus), atau bagian yang tidak bebas (belum dibuat atau sudah dimiliki), dijawab `Tidak bisa`.\n\n"
         "Contoh: `bagian a`, `bagian b`, `pemilik P`, `miliki P a`, `pinjam P b`, `miliki P a`, `hapus P`, `selesai` →\n\n```\n+a\n+b\n+P\nTidak bisa\n-P\n-a\n-b\n```" + W_ID,
         "Beberapa baris perintah, diakhiri `selesai`.", "Satu baris per pembuatan, penghancuran, dan penolakan.", "Paling banyak 18 perintah; nama bagian satu huruf kecil, nama pemilik satu huruf besar; sebuah nama tidak pernah dibuat dua kali."),
     en=("Ownership Simulation", "The most important question about a relation between two objects is: **who is responsible for destroying it?** An owner (composition) destroys its parts; a borrower (aggregation) does not.\n\n"
         "Make a class `Bagian` (part; attribute `string nama`; the constructor prints `+<name>`, the destructor `-<name>`).\n\n"
         "Make a class `Pemilik` (owner) that keeps two lists of pointers to `Bagian`: `milik` (owned) and `pinjaman` (borrowed). Its constructor prints `+<name>`. **Its destructor prints `-<name>`, then `delete`s all the parts it owns in the order it took them; borrowed parts are left alone.**\n\n"
         "Commands:\n\n- `bagian X` — create a free part named X;\n- `pemilik P` — create an owner named P;\n- `miliki P X` (own) — P takes over the **free** part X; from then on X is no longer free;\n- `pinjam P X` (borrow) — P records the free part X as borrowed; X stays free;\n"
         "- `hapus P` — destroy the owner P;\n- `selesai` — destroy all remaining owners in order of creation, then all parts still free in order of creation.\n\n"
         "A `miliki`, `pinjam` or `hapus` that names an owner that does not exist (not created yet, or already destroyed), or a part that is not free (not created yet, or already owned), is answered with `Tidak bisa` (cannot).\n\n"
         "Example: `bagian a`, `bagian b`, `pemilik P`, `miliki P a`, `pinjam P b`, `miliki P a`, `hapus P`, `selesai` →\n\n```\n+a\n+b\n+P\nTidak bisa\n-P\n-a\n-b\n```" + W_EN,
         "Several command lines, ending with `selesai`.", "One line per creation, destruction and refusal.", "At most 18 commands; part names are one lowercase letter, owner names one capital letter; a name is never created twice."),
     ar=("محاكاة الملكية", "أهم سؤال عن العلاقة بين كائنين: **من المسؤول عن هدمه؟** المالك (تركيب) يهدم أجزاءه؛ والمستعير (تجميع) لا يهدمها.\n\n"
         "أنشئ صنفًا `Bagian` (جزء؛ الخاصية `string nama`؛ الباني يطبع `+<الاسم>` والهادم `-<الاسم>`).\n\n"
         "أنشئ صنفًا `Pemilik` (مالك) يحفظ قائمتي مؤشرات إلى `Bagian`: `milik` (المملوكة) و `pinjaman` (المستعارة). بانيه يطبع `+<الاسم>`. **وهادمه يطبع `-<الاسم>` ثم ينفّذ `delete` على كل الأجزاء التي يملكها بترتيب تملّكها؛ أما المستعارة فلا يمسّها.**\n\n"
         "الأوامر:\n\n- `bagian X` — أنشئ جزءًا حرًّا اسمه X؛\n- `pemilik P` — أنشئ مالكًا اسمه P؛\n- `miliki P X` (تملَّك) — يستولي P على الجزء **الحر** X؛ ومنذئذ لا يكون X حرًّا؛\n- `pinjam P X` (استعر) — يسجّل P الجزء الحر X مستعارًا؛ ويبقى X حرًّا؛\n"
         "- `hapus P` — اهدم المالك P؛\n- `selesai` — اهدم كل المالكين الباقين بترتيب إنشائهم ثم كل الأجزاء التي ما زالت حرة بترتيب إنشائها.\n\n"
         "الأمر `miliki` أو `pinjam` أو `hapus` الذي يذكر مالكًا غير موجود (لم يُنشأ بعد أو هُدم) أو جزءًا غير حر (لم يُنشأ بعد أو مملوكًا) يُجاب بـ `Tidak bisa` (لا يمكن).\n\n"
         "مثال: `bagian a` ثم `bagian b` ثم `pemilik P` ثم `miliki P a` ثم `pinjam P b` ثم `miliki P a` ثم `hapus P` ثم `selesai` ←\n\n```\n+a\n+b\n+P\nTidak bisa\n-P\n-a\n-b\n```" + W_AR,
         "عدة أسطر أوامر تنتهي بـ `selesai`.", "سطر لكل إنشاء وكل هدم وكل رفض.", "18 أمرًا على الأكثر؛ اسم الجزء حرف صغير واحد واسم المالك حرف كبير واحد؛ ولا يُنشأ الاسم مرتين."))

soal("bank-nasabah", "sulit", "trimmed",
     NAMA + "nama = r.sample(NAMA, 4)\nkenal, rek = [], 0\nfor _ in range(r.randint(2, 20)):\n    q = r.random()\n    if q < 0.2 or not kenal:\n        n = r.choice(nama)\n        print('nasabah', n)\n        if n not in kenal:\n            kenal.append(n)\n    elif q < 0.45:\n        n = r.choice(kenal) if r.random() < 0.85 else r.choice(nama)\n        print('buka', n, r.randint(0, 20) * 50000)\n        rek += 1\n    elif q < 0.6:\n        print('setor', r.randint(1, rek + 1), r.randint(1, 10) * 50000)\n    elif q < 0.8:\n        print('tarik', r.randint(1, rek + 1), r.randint(1, 12) * 50000)\n    else:\n        print('transfer', r.randint(1, rek + 1), r.randint(1, rek + 1), r.randint(1, 10) * 50000)\nprint('selesai')\n",
     program(r'''
class Nasabah;

class Rekening {
public:
    int nomor = 0;
    int saldo = 0;
    Nasabah *pemilik = nullptr;
};

class Nasabah {
public:
    string nama;
    Rekening *rekening[3];
    int banyak = 0;

    int totalSaldo() const {
        auto total = 0;
        for (int k = 0; k < banyak; k++) {
            total += rekening[k]->saldo;
        }
        return total;
    }
};

class Bank {
private:
    Nasabah nasabah[4];
    Rekening rekening[20];
    int banyakNasabah = 0;
    int banyakRekening = 0;

    Nasabah *cariNasabah(const string &nama) {
        for (int k = 0; k < banyakNasabah; k++) {
            if (nasabah[k].nama == nama) {
                return &nasabah[k];
            }
        }
        return nullptr;
    }

    Rekening *cariRekening(int nomor) {
        if (nomor < 1 || nomor > banyakRekening) {
            return nullptr;
        }
        return &rekening[nomor - 1];
    }

public:
    string daftar(const string &nama) {
        if (cariNasabah(nama) != nullptr) {
            return "Sudah terdaftar";
        }
        nasabah[banyakNasabah].nama = nama;
        banyakNasabah++;
        return "Nasabah " + nama + " terdaftar";
    }

    string buka(const string &nama, int saldo) {
        auto n = cariNasabah(nama);
        if (n == nullptr) {
            return "Nasabah tidak ada";
        }
        if (n->banyak == 3) {
            return "Batas rekening tercapai";
        }
        auto r = &rekening[banyakRekening];
        banyakRekening++;
        r->nomor = banyakRekening;
        r->saldo = saldo;
        r->pemilik = n;
        n->rekening[n->banyak] = r;
        n->banyak++;
        return "Rekening " + to_string(r->nomor) + " dibuka";
    }

    string setor(int nomor, int jumlah) {
        auto r = cariRekening(nomor);
        if (r == nullptr) {
            return "Rekening tidak ada";
        }
        r->saldo += jumlah;
        return "Saldo " + to_string(nomor) + ": " + to_string(r->saldo);
    }

    string tarik(int nomor, int jumlah) {
        auto r = cariRekening(nomor);
        if (r == nullptr) {
            return "Rekening tidak ada";
        }
        if (jumlah > r->saldo) {
            return "Ditolak";
        }
        r->saldo -= jumlah;
        return "Saldo " + to_string(nomor) + ": " + to_string(r->saldo);
    }

    string transfer(int dari, int ke, int jumlah) {
        auto a = cariRekening(dari);
        auto b = cariRekening(ke);
        if (a == nullptr || b == nullptr) {
            return "Rekening tidak ada";
        }
        if (a == b || jumlah > a->saldo) {
            return "Ditolak";
        }
        a->saldo -= jumlah;
        b->saldo += jumlah;
        if (a->pemilik == b->pemilik) {
            return "Pindah buku";
        }
        return "Transfer ke " + b->pemilik->nama;
    }

    void laporan() const {
        for (int k = 0; k < banyakNasabah; k++) {
            cout << nasabah[k].nama << ": " << nasabah[k].banyak << " rekening, total " << nasabah[k].totalSaldo() << endl;
        }
    }
};''', r'''    Bank bank;
    string perintah;
    while (cin >> perintah) {
        if (perintah == "selesai") {
            break;
        }
        if (perintah == "nasabah") {
            string nama;
            cin >> nama;
            cout << bank.daftar(nama) << endl;
        } else if (perintah == "buka") {
            string nama;
            int saldo;
            cin >> nama >> saldo;
            cout << bank.buka(nama, saldo) << endl;
        } else if (perintah == "transfer") {
            int a, b, x;
            cin >> a >> b >> x;
            cout << bank.transfer(a, b, x) << endl;
        } else {
            int nomor, x;
            cin >> nomor >> x;
            if (perintah == "setor") {
                cout << bank.setor(nomor, x) << endl;
            } else {
                cout << bank.tarik(nomor, x) << endl;
            }
        }
    }
    bank.laporan();''', "string"), wajib=W,
     id=("Bank, Nasabah, Rekening", "Sebuah bank **memiliki** catatan nasabah dan rekening (komposisi). Antara nasabah dan rekening ada asosiasi **dua arah**: seorang nasabah punya paling banyak **3** rekening (`Rekening *rekening[3]`), dan tiap rekening tahu pemiliknya (`Nasabah *pemilik`).\n\n"
         "Buat class `Rekening` (`int nomor`, `int saldo`, `Nasabah *pemilik`), class `Nasabah` (`string nama`, `Rekening *rekening[3]`, `int banyak`, method `int totalSaldo() const`), dan class `Bank` (`Nasabah nasabah[4]`, `Rekening rekening[20]`) yang method-methodnya mengembalikan pesan:\n\n"
         "- `nasabah <nama>` → `Nasabah <nama> terdaftar`, atau `Sudah terdaftar`;\n"
         "- `buka <nama> <saldo>` → diperiksa berurutan: nasabah tidak dikenal → `Nasabah tidak ada`; sudah punya 3 rekening → `Batas rekening tercapai`; selain itu rekening mendapat nomor urut berikutnya (1, 2, …) → `Rekening <nomor> dibuka`;\n"
         "- `setor <nomor> <jumlah>` → `Saldo <nomor>: <saldo>`, atau `Rekening tidak ada`;\n- `tarik <nomor> <jumlah>` → `Saldo <nomor>: <saldo>`; `Ditolak` bila saldo kurang; `Rekening tidak ada`;\n"
         "- `transfer <dari> <ke> <jumlah>` → `Rekening tidak ada` bila salah satunya tidak ada; `Ditolak` bila keduanya rekening yang sama atau saldo kurang; bila berhasil: `Pindah buku` bila kedua rekening milik nasabah yang sama, selain itu `Transfer ke <nama pemilik rekening tujuan>`;\n"
         "- `selesai` → tiap nasabah menurut urutan pendaftaran: `<nama>: <n> rekening, total <jumlah saldo>`.\n\n"
         "Contoh: `nasabah Andi`, `nasabah Budi`, `buka Andi 100000`, `buka Andi 0`, `buka Budi 50000`, `transfer 1 2 30000`, `transfer 1 3 50000`, `tarik 1 50000`, `selesai` →\n\n```\nNasabah Andi terdaftar\nNasabah Budi terdaftar\nRekening 1 dibuka\nRekening 2 dibuka\nRekening 3 dibuka\nPindah buku\nTransfer ke Budi\nDitolak\nAndi: 2 rekening, total 50000\nBudi: 1 rekening, total 100000\n```" + W_ID,
         "Beberapa baris perintah, diakhiri `selesai`.", "Satu baris per perintah, lalu satu baris per nasabah.", "Paling banyak 20 perintah; paling banyak 4 nasabah; semua jumlah uang kelipatan 50000, antara 0 dan 1000000."),
     en=("Bank, Customers, Accounts", "A bank **owns** its records of customers and accounts (composition). Between customers and accounts there is an association **in both directions**: a customer has at most **3** accounts (`Rekening *rekening[3]`), and every account knows its owner (`Nasabah *pemilik`).\n\n"
         "Make a class `Rekening` (account; `int nomor`, `int saldo`, `Nasabah *pemilik`), a class `Nasabah` (customer; `string nama`, `Rekening *rekening[3]`, `int banyak`, method `int totalSaldo() const`), and a class `Bank` (`Nasabah nasabah[4]`, `Rekening rekening[20]`) whose methods return messages:\n\n"
         "- `nasabah <name>` → `Nasabah <name> terdaftar` (registered), or `Sudah terdaftar` (already registered);\n"
         "- `buka <name> <balance>` (open) → checked in order: unknown customer → `Nasabah tidak ada`; already 3 accounts → `Batas rekening tercapai`; otherwise the account gets the next number (1, 2, …) → `Rekening <number> dibuka`;\n"
         "- `setor <number> <amount>` → `Saldo <number>: <balance>`, or `Rekening tidak ada` (no such account);\n- `tarik <number> <amount>` → `Saldo <number>: <balance>`; `Ditolak` when the balance is too low; `Rekening tidak ada`;\n"
         "- `transfer <from> <to> <amount>` → `Rekening tidak ada` when either does not exist; `Ditolak` when both are the same account or the balance is too low; on success: `Pindah buku` (book transfer) when both accounts belong to the same customer, otherwise `Transfer ke <name of the target account's owner>`;\n"
         "- `selesai` → every customer in order of registration: `<name>: <n> rekening, total <summed balance>`.\n\n"
         "Example: `nasabah Andi`, `nasabah Budi`, `buka Andi 100000`, `buka Andi 0`, `buka Budi 50000`, `transfer 1 2 30000`, `transfer 1 3 50000`, `tarik 1 50000`, `selesai` →\n\n```\nNasabah Andi terdaftar\nNasabah Budi terdaftar\nRekening 1 dibuka\nRekening 2 dibuka\nRekening 3 dibuka\nPindah buku\nTransfer ke Budi\nDitolak\nAndi: 2 rekening, total 50000\nBudi: 1 rekening, total 100000\n```" + W_EN,
         "Several command lines, ending with `selesai`.", "One line per command, then one line per customer.", "At most 20 commands; at most 4 customers; all amounts are multiples of 50000 between 0 and 1000000."),
     ar=("مصرف وعملاء وحسابات", "المصرف **يملك** سجلات العملاء والحسابات (تركيب). وبين العملاء والحسابات ارتباط **في الاتجاهين**: للعميل **3** حسابات على الأكثر (`Rekening *rekening[3]`)، وكل حساب يعرف مالكه (`Nasabah *pemilik`).\n\n"
         "أنشئ صنفًا `Rekening` (حساب؛ `int nomor` و `int saldo` و `Nasabah *pemilik`)، وصنفًا `Nasabah` (عميل؛ `string nama` و `Rekening *rekening[3]` و `int banyak` والطريقة `int totalSaldo() const`)، وصنفًا `Bank` (`Nasabah nasabah[4]` و `Rekening rekening[20]`) تُرجع طرائقه رسائل:\n\n"
         "- `nasabah <الاسم>` ← `Nasabah <الاسم> terdaftar` (سُجِّل) أو `Sudah terdaftar` (مسجَّل أصلًا)؛\n"
         "- `buka <الاسم> <الرصيد>` (افتح) ← يُفحص بالترتيب: عميل غير معروف ← `Nasabah tidak ada`؛ له 3 حسابات ← `Batas rekening tercapai`؛ وإلا نال الحساب الرقم التالي (1، 2، …) ← `Rekening <الرقم> dibuka`؛\n"
         "- `setor <الرقم> <المبلغ>` ← `Saldo <الرقم>: <الرصيد>` أو `Rekening tidak ada` (لا حساب)؛\n- `tarik <الرقم> <المبلغ>` ← `Saldo <الرقم>: <الرصيد>`؛ و `Ditolak` إذا لم يكفِ الرصيد؛ و `Rekening tidak ada`؛\n"
         "- `transfer <من> <إلى> <المبلغ>` ← `Rekening tidak ada` إذا غاب أحدهما؛ و `Ditolak` إذا كانا الحساب نفسه أو لم يكفِ الرصيد؛ وعند النجاح: `Pindah buku` (نقل دفتري) إذا كان الحسابان للعميل نفسه، وإلا `Transfer ke <اسم مالك الحساب الهدف>`؛\n"
         "- `selesai` ← كل عميل بترتيب التسجيل: `<الاسم>: <n> rekening, total <مجموع الأرصدة>`.\n\n"
         "مثال: `nasabah Andi` ثم `nasabah Budi` ثم `buka Andi 100000` ثم `buka Andi 0` ثم `buka Budi 50000` ثم `transfer 1 2 30000` ثم `transfer 1 3 50000` ثم `tarik 1 50000` ثم `selesai` ←\n\n```\nNasabah Andi terdaftar\nNasabah Budi terdaftar\nRekening 1 dibuka\nRekening 2 dibuka\nRekening 3 dibuka\nPindah buku\nTransfer ke Budi\nDitolak\nAndi: 2 rekening, total 50000\nBudi: 1 rekening, total 100000\n```" + W_AR,
         "عدة أسطر أوامر تنتهي بـ `selesai`.", "سطر لكل أمر ثم سطر لكل عميل.", "20 أمرًا على الأكثر؛ 4 عملاء على الأكثر؛ كل المبالغ من مضاعفات 50000 بين 0 و 1000000."))

soal("rakitan-bertingkat", "sulit", "trimmed",
     "BAHAN = ['baut', 'mur', 'papan', 'roda', 'kabel', 'lampu']\nb = r.randint(1, 5)\nprint(b)\nfor n in r.sample(BAHAN, b):\n    print(n, r.randint(1, 20) * 500)\nq = r.randint(1, 5)\nprint(q)\nfor k in range(q):\n    m = r.randint(1, 4)\n    isi = []\n    for _ in range(m):\n        if k > 0 and r.random() < 0.5:\n            isi += ['R', r.randint(1, k), r.randint(1, 3)]\n        else:\n            isi += ['B', r.randint(1, b), r.randint(1, 5)]\n    print('R' + str(k + 1), m, *isi)\n",
     program(r'''
class Bahan {
public:
    string nama;
    int harga = 0;
};

class Rakitan {
private:
    string nama;
    Bahan *bahan[4];
    int jumlahBahan[4];
    int banyakBahan = 0;
    Rakitan *sub[4];
    int jumlahSub[4];
    int banyakSub = 0;

public:
    void setNama(const string &n) {
        nama = n;
    }

    string getNama() const {
        return nama;
    }

    void pakaiBahan(Bahan *b, int j) {
        bahan[banyakBahan] = b;
        jumlahBahan[banyakBahan] = j;
        banyakBahan++;
    }

    void pakaiRakitan(Rakitan *r, int j) {
        sub[banyakSub] = r;
        jumlahSub[banyakSub] = j;
        banyakSub++;
    }

    long long biaya() const {
        long long total = 0;
        for (int k = 0; k < banyakBahan; k++) {
            total += (long long) bahan[k]->harga * jumlahBahan[k];
        }
        for (int k = 0; k < banyakSub; k++) {
            total += sub[k]->biaya() * jumlahSub[k];
        }
        return total;
    }

    int bautTotal(const Bahan *dicari) const {
        auto total = 0;
        for (int k = 0; k < banyakBahan; k++) {
            if (bahan[k] == dicari) {
                total += jumlahBahan[k];
            }
        }
        for (int k = 0; k < banyakSub; k++) {
            total += sub[k]->bautTotal(dicari) * jumlahSub[k];
        }
        return total;
    }
};''', r'''    Bahan gudang[5];
    Rakitan rakitan[5];
    int b, q;
    cin >> b;
    for (int k = 0; k < b; k++) {
        cin >> gudang[k].nama >> gudang[k].harga;
    }
    cin >> q;
    for (int k = 0; k < q; k++) {
        string nama;
        int m;
        cin >> nama >> m;
        rakitan[k].setNama(nama);
        for (int p = 0; p < m; p++) {
            char jenis;
            int nomor, jumlah;
            cin >> jenis >> nomor >> jumlah;
            if (jenis == 'B') {
                rakitan[k].pakaiBahan(&gudang[nomor - 1], jumlah);
            } else {
                rakitan[k].pakaiRakitan(&rakitan[nomor - 1], jumlah);
            }
        }
    }
    for (int k = 0; k < q; k++) {
        cout << rakitan[k].getNama() << ": " << rakitan[k].biaya() << endl;
    }
    cout << gudang[0].nama << " untuk " << rakitan[q - 1].getNama() << ": " << rakitan[q - 1].bautTotal(&gudang[0]) << endl;''', "string"), wajib=W,
     id=("Rakitan Bertingkat", "Sebuah rakitan dibuat dari **bahan** dan dari **rakitan lain** yang lebih kecil: sepeda terdiri dari rangka dan dua roda; tiap roda terdiri dari pelek, ban, dan jari-jari. Daftar seperti itu disebut *bill of materials*.\n\n"
         "Buat class `Bahan` (`string nama`, `int harga`) dan class `Rakitan` yang menyimpan dua daftar: penunjuk ke bahan beserta jumlahnya (`Bahan *bahan[4]`, `int jumlahBahan[4]`) dan penunjuk ke rakitan lain beserta jumlahnya (`Rakitan *sub[4]`, `int jumlahSub[4]`). Beri dua method yang **bertanya kepada sub-rakitannya**:\n\n"
         "- `long long biaya() const` — harga × jumlah tiap bahan, ditambah biaya × jumlah tiap sub-rakitan;\n- `int bautTotal(const Bahan *dicari) const` — berapa buah bahan `dicari` yang dibutuhkan seluruhnya, termasuk di dalam sub-rakitan.\n\n"
         "Masukan: b bahan (nama dan harga); lalu q rakitan. Tiap rakitan satu baris: nama, banyak komponen m, lalu m komponen berbentuk `B <nomor bahan> <jumlah>` atau `R <nomor rakitan> <jumlah>`. Sebuah rakitan hanya boleh memakai rakitan yang **sudah didefinisikan sebelumnya**.\n\n"
         "Cetak `<nama>: <biaya>` untuk tiap rakitan. Lalu cetak `<nama bahan pertama> untuk <nama rakitan terakhir>: <jumlah>`.\n\n"
         "Contoh: bahan `baut 500`, `papan 10000`; rakitan `R1 2 B 1 4 B 2 1` dan `R2 2 R 1 3 B 1 2` →\n\n```\nR1: 12000\nR2: 37000\nbaut untuk R2: 14\n```" + W_ID,
         "Baris pertama: b. Lalu b baris bahan. Lalu q. Lalu q baris rakitan.", "Satu baris per rakitan, lalu satu baris kebutuhan bahan pertama.", "1 ≤ b ≤ 5; 1 ≤ q ≤ 5; 1 ≤ m ≤ 4; harga kelipatan 500 sampai 10000; jumlah bahan 1–5; jumlah sub-rakitan 1–3"),
     en=("Nested Assemblies", "An assembly is made from **materials** and from other, smaller **assemblies**: a bicycle consists of a frame and two wheels; each wheel consists of a rim, a tyre and spokes. Such a list is called a *bill of materials*.\n\n"
         "Make a class `Bahan` (material; `string nama`, `int harga`) and a class `Rakitan` (assembly) that keeps two lists: pointers to materials with their quantities (`Bahan *bahan[4]`, `int jumlahBahan[4]`) and pointers to other assemblies with their quantities (`Rakitan *sub[4]`, `int jumlahSub[4]`). Give it two methods that **ask its sub-assemblies**:\n\n"
         "- `long long biaya() const` (cost) — price × quantity of every material, plus cost × quantity of every sub-assembly;\n- `int bautTotal(const Bahan *dicari) const` — how many pieces of the material `dicari` are needed in all, including inside sub-assemblies.\n\n"
         "Input: b materials (name and price); then q assemblies. Each assembly is one line: name, number of components m, then m components of the form `B <material number> <quantity>` or `R <assembly number> <quantity>`. An assembly may only use assemblies **defined before it**.\n\n"
         "Print `<name>: <cost>` for every assembly. Then print `<name of the first material> untuk <name of the last assembly>: <quantity>` (… for …).\n\n"
         "Example: materials `baut 500`, `papan 10000`; assemblies `R1 2 B 1 4 B 2 1` and `R2 2 R 1 3 B 1 2` →\n\n```\nR1: 12000\nR2: 37000\nbaut untuk R2: 14\n```" + W_EN,
         "First line: b. Then b material lines. Then q. Then q assembly lines.", "One line per assembly, then one line with the need for the first material.", "1 ≤ b ≤ 5; 1 ≤ q ≤ 5; 1 ≤ m ≤ 4; prices are multiples of 500 up to 10000; material quantities 1–5; sub-assembly quantities 1–3"),
     ar=("تجميعات متداخلة", "تُصنع التجميعة من **مواد** ومن **تجميعات أخرى** أصغر: الدراجة تتكون من هيكل وعجلتين؛ وكل عجلة من طوق وإطار وأسلاك. قائمة كهذه تسمى *قائمة المواد*.\n\n"
         "أنشئ صنفًا `Bahan` (مادة؛ `string nama` و `int harga`) وصنفًا `Rakitan` (تجميعة) يحفظ قائمتين: مؤشرات إلى مواد مع كمياتها (`Bahan *bahan[4]` و `int jumlahBahan[4]`) ومؤشرات إلى تجميعات أخرى مع كمياتها (`Rakitan *sub[4]` و `int jumlahSub[4]`). أعطه طريقتين **تسألان تجميعاته الفرعية**:\n\n"
         "- `long long biaya() const` (التكلفة) — السعر × الكمية لكل مادة، زائد التكلفة × الكمية لكل تجميعة فرعية؛\n- `int bautTotal(const Bahan *dicari) const` — كم قطعة من المادة `dicari` تلزم إجمالًا، ومنها ما في التجميعات الفرعية.\n\n"
         "المدخل: b مادة (الاسم والسعر)؛ ثم q تجميعة. كل تجميعة في سطر: الاسم وعدد المكوّنات m ثم m مكوّنًا على الصورة `B <رقم المادة> <الكمية>` أو `R <رقم التجميعة> <الكمية>`. لا تستعمل التجميعة إلا تجميعات **عُرِّفت قبلها**.\n\n"
         "اطبع `<الاسم>: <التكلفة>` لكل تجميعة. ثم اطبع `<اسم المادة الأولى> untuk <اسم التجميعة الأخيرة>: <الكمية>` (… لـ …).\n\n"
         "مثال: المادتان `baut 500` و `papan 10000`؛ التجميعتان `R1 2 B 1 4 B 2 1` و `R2 2 R 1 3 B 1 2` ←\n\n```\nR1: 12000\nR2: 37000\nbaut untuk R2: 14\n```" + W_AR,
         "السطر الأول: b. ثم b سطر مواد. ثم q. ثم q سطر تجميعات.", "سطر لكل تجميعة ثم سطر حاجة المادة الأولى.", "1 ≤ b ≤ 5؛ 1 ≤ q ≤ 5؛ 1 ≤ m ≤ 4؛ الأسعار من مضاعفات 500 حتى 10000؛ كميات المواد 1–5؛ كميات التجميعات الفرعية 1–3"))

soal("klinik-antrean", "sulit", "trimmed",
     NAMA + "dokter = r.sample(['Hasan', 'Ratna', 'Surya', 'Wati'], r.randint(1, 3))\nprint(len(dokter), *dokter)\npasien = r.sample(NAMA, 20)\nk = 0\nfor _ in range(r.randint(1, 20)):\n    if r.random() < 0.65 and k < 20:\n        print('datang', pasien[k], 'darurat' if r.random() < 0.3 else 'biasa')\n        k += 1\n    else:\n        print('panggil', r.choice(dokter))\nprint('selesai')\n",
     program(r'''
class Pasien {
public:
    string nama;
    bool darurat = false;
};

class Dokter {
private:
    string nama;
    Pasien *antre[20];
    int banyak = 0;

public:
    void setNama(const string &n) {
        nama = n;
    }

    string getNama() const {
        return nama;
    }

    int getBanyak() const {
        return banyak;
    }

    int terima(Pasien *p) {
        auto posisi = banyak;
        if (p->darurat) {
            posisi = 0;
            while (posisi < banyak && antre[posisi]->darurat) {
                posisi++;
            }
        }
        for (int k = banyak; k > posisi; k--) {
            antre[k] = antre[k - 1];
        }
        antre[posisi] = p;
        banyak++;
        return posisi + 1;
    }

    Pasien *panggil() {
        if (banyak == 0) {
            return nullptr;
        }
        auto p = antre[0];
        for (int k = 0; k < banyak - 1; k++) {
            antre[k] = antre[k + 1];
        }
        banyak--;
        return p;
    }

    void cetak() const {
        cout << nama << ":";
        if (banyak == 0) {
            cout << " -";
        }
        for (int k = 0; k < banyak; k++) {
            cout << " " << antre[k]->nama;
        }
        cout << endl;
    }
};

class Klinik {
private:
    Dokter dokter[3];
    Pasien pasien[20];
    int banyakDokter = 0;
    int banyakPasien = 0;

public:
    void tambahDokter(const string &nama) {
        dokter[banyakDokter].setNama(nama);
        banyakDokter++;
    }

    void datang(const string &nama, bool darurat) {
        auto p = &pasien[banyakPasien];
        banyakPasien++;
        p->nama = nama;
        p->darurat = darurat;
        auto pilih = 0;
        for (int k = 1; k < banyakDokter; k++) {
            if (dokter[k].getBanyak() < dokter[pilih].getBanyak()) {
                pilih = k;
            }
        }
        auto urutan = dokter[pilih].terima(p);
        cout << nama << " -> Dokter " << dokter[pilih].getNama() << " (urutan " << urutan << ")" << endl;
    }

    void panggil(const string &nama) {
        for (int k = 0; k < banyakDokter; k++) {
            if (dokter[k].getNama() == nama) {
                auto p = dokter[k].panggil();
                if (p == nullptr) {
                    cout << "Dokter " << nama << " tidak punya pasien" << endl;
                } else {
                    cout << "Dokter " << nama << " memeriksa " << p->nama << endl;
                }
            }
        }
    }

    void laporan() const {
        for (int k = 0; k < banyakDokter; k++) {
            dokter[k].cetak();
        }
    }
};''', r'''    Klinik klinik;
    int d;
    cin >> d;
    for (int k = 0; k < d; k++) {
        string nama;
        cin >> nama;
        klinik.tambahDokter(nama);
    }
    string perintah;
    while (cin >> perintah) {
        if (perintah == "selesai") {
            break;
        }
        string nama;
        cin >> nama;
        if (perintah == "datang") {
            string jenis;
            cin >> jenis;
            klinik.datang(nama, jenis == "darurat");
        } else {
            klinik.panggil(nama);
        }
    }
    klinik.laporan();''', "string"), wajib=W,
     id=("Antrean Klinik", "Sebuah klinik **memiliki** dokter-dokter dan catatan semua pasien yang datang (komposisi). Tiap dokter punya **antrean pasien** — penunjuk ke pasien di catatan klinik (agregasi): pasien tidak dimiliki dokter, hanya sedang menunggunya.\n\n"
         "Buat class `Pasien` (`string nama`, `bool darurat`), class `Dokter` (`string nama`, `Pasien *antre[20]`, `int banyak`), dan class `Klinik` (`Dokter dokter[3]`, `Pasien pasien[20]`).\n\n"
         "Perintah:\n\n"
         "- `datang <nama> <biasa|darurat>` — pasien dicatat klinik, lalu diarahkan ke **dokter yang antreannya paling pendek** (bila sama, dokter yang lebih dulu di masukan). Pasien `biasa` masuk di belakang antrean. Pasien `darurat` masuk **di depan semua pasien biasa, tetapi di belakang pasien darurat yang sudah menunggu**. Cetak `<nama> -> Dokter <dokter> (urutan <k>)`, dengan k posisinya di antrean itu;\n"
         "- `panggil <dokter>` — dokter memeriksa pasien terdepan: `Dokter <dokter> memeriksa <nama>`, atau `Dokter <dokter> tidak punya pasien`;\n"
         "- `selesai` — untuk tiap dokter cetak `<dokter>:` diikuti nama-nama di antreannya, atau ` -` bila kosong.\n\n"
         "Contoh: dokter `Hasan Ratna`; `datang Andi biasa`, `datang Budi biasa`, `datang Citra darurat`, `datang Dewi darurat`, `panggil Hasan`, `panggil Wati`, `selesai` →\n\n```\nAndi -> Dokter Hasan (urutan 1)\nBudi -> Dokter Ratna (urutan 1)\nCitra -> Dokter Hasan (urutan 1)\nDewi -> Dokter Ratna (urutan 1)\nDokter Hasan memeriksa Citra\nHasan: Andi\nRatna: Dewi Budi\n```\n\n(Perintah `panggil` untuk dokter yang tidak ada di klinik tidak mencetak apa-apa.)" + W_ID,
         "Baris pertama: banyak dokter dan namanya. Lalu perintah-perintah, diakhiri `selesai`.", "Satu baris per perintah yang menghasilkan keluaran, lalu satu baris per dokter.", "1 sampai 3 dokter; paling banyak 20 perintah; nama pasien semuanya berbeda."),
     en=("Clinic Queues", "A clinic **owns** its doctors and its records of all patients who arrive (composition). Each doctor has a **queue of patients** — pointers to patients in the clinic's records (aggregation): a patient is not owned by a doctor, only waiting for one.\n\n"
         "Make a class `Pasien` (patient; `string nama`, `bool darurat` (emergency)), a class `Dokter` (doctor; `string nama`, `Pasien *antre[20]`, `int banyak`), and a class `Klinik` (`Dokter dokter[3]`, `Pasien pasien[20]`).\n\n"
         "Commands:\n\n"
         "- `datang <name> <biasa|darurat>` (arrives; ordinary or emergency) — the clinic records the patient, then sends them to **the doctor with the shortest queue** (on a tie the doctor earlier in the input). A `biasa` patient joins the back of the queue. A `darurat` patient goes **ahead of all ordinary patients, but behind emergency patients already waiting**. Print `<name> -> Dokter <doctor> (urutan <k>)`, where k is their position in that queue;\n"
         "- `panggil <doctor>` (call) — the doctor sees the patient at the front: `Dokter <doctor> memeriksa <name>`, or `Dokter <doctor> tidak punya pasien` (has no patient);\n"
         "- `selesai` — for every doctor print `<doctor>:` followed by the names in their queue, or ` -` when empty.\n\n"
         "Example: doctors `Hasan Ratna`; `datang Andi biasa`, `datang Budi biasa`, `datang Citra darurat`, `datang Dewi darurat`, `panggil Hasan`, `panggil Wati`, `selesai` →\n\n```\nAndi -> Dokter Hasan (urutan 1)\nBudi -> Dokter Ratna (urutan 1)\nCitra -> Dokter Hasan (urutan 1)\nDewi -> Dokter Ratna (urutan 1)\nDokter Hasan memeriksa Citra\nHasan: Andi\nRatna: Dewi Budi\n```\n\n(A `panggil` for a doctor who is not in the clinic prints nothing.)" + W_EN,
         "First line: the number of doctors and their names. Then the commands, ending with `selesai`.", "One line per command that produces output, then one line per doctor.", "1 to 3 doctors; at most 20 commands; patient names are all different."),
     ar=("طوابير العيادة", "العيادة **تملك** أطباءها وسجلات كل المرضى الواصلين (تركيب). ولكل طبيب **طابور مرضى** — مؤشرات إلى مرضى في سجلات العيادة (تجميع): المريض لا يملكه الطبيب، بل ينتظره فقط.\n\n"
         "أنشئ صنفًا `Pasien` (مريض؛ `string nama` و `bool darurat` (طارئ))، وصنفًا `Dokter` (طبيب؛ `string nama` و `Pasien *antre[20]` و `int banyak`)، وصنفًا `Klinik` (`Dokter dokter[3]` و `Pasien pasien[20]`).\n\n"
         "الأوامر:\n\n"
         "- `datang <الاسم> <biasa|darurat>` (وصل؛ عادي أو طارئ) — تسجّل العيادة المريض ثم توجّهه إلى **الطبيب صاحب أقصر طابور** (وعند التساوي الطبيب الأسبق في المدخل). المريض `biasa` ينضم إلى آخر الطابور. والمريض `darurat` يتقدم **على كل المرضى العاديين، لكنه يقف خلف الطارئين المنتظرين قبله**. اطبع `<الاسم> -> Dokter <الطبيب> (urutan <k>)` حيث k موضعه في ذلك الطابور؛\n"
         "- `panggil <الطبيب>` (نادِ) — يفحص الطبيب المريض الذي في المقدمة: `Dokter <الطبيب> memeriksa <الاسم>` أو `Dokter <الطبيب> tidak punya pasien` (لا مريض له)؛\n"
         "- `selesai` — لكل طبيب اطبع `<الطبيب>:` تليها الأسماء في طابوره، أو ` -` إذا كان فارغًا.\n\n"
         "مثال: الطبيبان `Hasan Ratna`؛ `datang Andi biasa` ثم `datang Budi biasa` ثم `datang Citra darurat` ثم `datang Dewi darurat` ثم `panggil Hasan` ثم `panggil Wati` ثم `selesai` ←\n\n```\nAndi -> Dokter Hasan (urutan 1)\nBudi -> Dokter Ratna (urutan 1)\nCitra -> Dokter Hasan (urutan 1)\nDewi -> Dokter Ratna (urutan 1)\nDokter Hasan memeriksa Citra\nHasan: Andi\nRatna: Dewi Budi\n```\n\n(الأمر `panggil` لطبيب ليس في العيادة لا يطبع شيئًا.)" + W_AR,
         "السطر الأول: عدد الأطباء وأسماؤهم. ثم الأوامر منتهيةً بـ `selesai`.", "سطر لكل أمر ينتج مخرجًا ثم سطر لكل طبيب.", "من 1 إلى 3 أطباء؛ 20 أمرًا على الأكثر؛ أسماء المرضى كلها مختلفة."))

tulis(__file__, "PBO301", 5, "cpp", {"mudah_sekali": 8, "mudah": 8, "mudah_sedang": 6, "sedang": 6, "sulit": 4})
