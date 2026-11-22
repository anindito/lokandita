#!/usr/bin/env python3
"""Menyusun docs/soal/pbo301-pertemuan-4.json.

PBO301 pertemuan 4 — Encapsulation dan Access Control. Konsep yang diizinkan
di produksi: 21 konsep dasar, MODERN_CPP, CLASS_OBJECT, CONSTRUCTOR_DESTRUCTOR,
COPY_SEMANTICS, ENCAPSULATION.

Mulai pertemuan ini data disembunyikan di bagian `private:`. Soal-soalnya
berpusat pada INVARIAN: sesuatu yang selalu benar tentang objek karena hanya
method-nya sendiri yang boleh mengubah datanya (validasi di setter, batas,
keadaan tersembunyi). Keluaran memperlihatkan invarian itu ditegakkan: setiap
perubahan yang tidak sah ditolak dan dilaporkan.

Solusi referensi belum memuat objek kelas lain di dalam kelas
(KOMPOSISI_AGREGASI, pertemuan 5) dan tidak memakai kontainer STL.

Setiap soal mensyaratkan CLASS_OBJECT dan ENCAPSULATION di kode mahasiswa.
Pemeriksaannya: ada bagian `private:` / `protected:` atau anggota data yang
tidak publik. Apakah datanya benar-benar terlindung tidak diperiksa judge.

Dilewati dari silabus pertemuan 4 (tidak bisa dinilai dari keluaran): notasi
visibility UML, `{readOnly}`, dan kasus "kode ini tidak terkompilasi karena
mengakses anggota private / memanggil method non-const pada objek const".

    python3 docs/soal/buat-pbo301-pertemuan-4.py
"""
from bangun import program, soal, tulis

W = ("CLASS_OBJECT", "ENCAPSULATION")
W_ID = "\n\n**Kode harus memuat class dengan anggota `private`**: keluaran yang benar tanpa itu hanya mendapat setengah nilai."
W_EN = "\n\n**The code must contain a class with `private` members**: correct output without one gets only half the marks."
W_AR = "\n\n**يجب أن يحتوي الكود على صنف له أعضاء `private`**: المخرج الصحيح بلا ذلك ينال نصف الدرجة فقط."

NAMA = "NAMA = ['Andi', 'Budi', 'Citra', 'Dewi', 'Eko', 'Fajar', 'Gita', 'Hadi', 'Indah', 'Joko', 'Kartika', 'Lina', 'Made', 'Nanda', 'Putri', 'Rudi', 'Sari', 'Tono', 'Wulan', 'Yoga']\n"
BARANG = "BARANG = ['beras', 'gula', 'kopi', 'teh', 'susu', 'roti', 'telur', 'minyak', 'garam', 'sabun', 'pensil', 'buku']\n"

# ============================== MUDAH SEKALI (8) ==============================

soal("kotak-terlindung", "mudah_sekali", "trimmed", "print({2: 0, 3: -7, 4: 1}.get(i, r.randint(2, 50) if i % 2 == 0 else r.randint(51, 100)))\n",
     program(r'''
class Kotak {
private:
    int sisi = 1;

public:
    void setSisi(int s) {
        if (s > 0) {
            sisi = s;
        }
    }

    int getSisi() const {
        return sisi;
    }

    int luas() const {
        return sisi * sisi;
    }
};''', r'''    int s;
    cin >> s;
    Kotak k;
    k.setSisi(s);
    cout << k.getSisi() << " " << k.luas() << endl;'''), wajib=W,
     id=("Kotak Terlindung", "Atribut di bagian `private:` hanya bisa disentuh oleh method class itu sendiri. Kode di luar class harus lewat method publik — dan method itulah yang menjaga agar data tidak pernah bernilai ngawur.\n\n"
         "Buat class `Kotak`:\n\n- `private`: `int sisi`, mula-mula 1;\n- `public`: `void setSisi(int s)` — mengubah sisi **hanya bila s positif**; selain itu diabaikan;\n- `public`: `int getSisi() const` dan `int luas() const`.\n\n"
         "Baca sebuah bilangan, berikan ke `setSisi`, lalu cetak sisi dan luas kotak.\n\nContoh: 5 → `5 25`; −7 → `1 1` (ditolak, sisi tetap 1)." + W_ID,
         "Satu bilangan bulat s.", "Satu baris: sisi, lalu luas.", "−50 ≤ s ≤ 100"),
     en=("A Protected Box", "An attribute in the `private:` section can be touched only by the class's own methods. Code outside the class has to go through public methods — and those methods make sure the data never holds nonsense.\n\n"
         "Make a class `Kotak` (box):\n\n- `private`: `int sisi` (side), starting at 1;\n- `public`: `void setSisi(int s)` — changes the side **only when s is positive**; otherwise it is ignored;\n- `public`: `int getSisi() const` and `int luas() const` (area).\n\n"
         "Read a number, pass it to `setSisi`, then print the side and the area of the box.\n\nExamples: 5 → `5 25`; −7 → `1 1` (refused, the side stays 1)." + W_EN,
         "One integer s.", "One line: the side, then the area.", "−50 ≤ s ≤ 100"),
     ar=("علبة محمية", "الخاصية في القسم `private:` لا تمسّها إلا طرائق الصنف نفسه. الكود خارج الصنف يمرّ عبر الطرائق العامة — وتلك الطرائق تضمن ألّا تحمل البيانات قيمة عبثية أبدًا.\n\n"
         "أنشئ صنفًا `Kotak` (علبة):\n\n- `private`: `int sisi` (الضلع)، ويبدأ من 1؛\n- `public`: `void setSisi(int s)` — تغيّر الضلع **فقط إذا كان s موجبًا**؛ وإلا أُهمل؛\n- `public`: `int getSisi() const` و `int luas() const` (المساحة).\n\n"
         "اقرأ عددًا ومرِّره إلى `setSisi` ثم اطبع ضلع العلبة ومساحتها.\n\nأمثلة: 5 ← `5 25`؛ −7 ← `1 1` (رُفض، ويبقى الضلع 1)." + W_AR,
         "عدد صحيح واحد s.", "سطر واحد: الضلع ثم المساحة.", "−50 ≤ s ≤ 100"))

soal("umur-valid", "mudah_sekali", "trimmed", NAMA + "u = {2: 0, 3: 150, 4: 151, 5: -1}.get(i, r.randint(1, 100) if i % 2 == 0 else r.choice([-r.randint(2, 20), r.randint(152, 200)]))\nprint(r.choice(NAMA), u)\n",
     program(r'''
class Orang {
private:
    string nama;
    int umur = 0;

public:
    void setNama(const string &n) {
        nama = n;
    }

    bool setUmur(int u) {
        if (u < 0 || u > 150) {
            return false;
        }
        umur = u;
        return true;
    }

    string getNama() const {
        return nama;
    }

    int getUmur() const {
        return umur;
    }
};''', r'''    string nama;
    int umur;
    cin >> nama >> umur;
    Orang o;
    o.setNama(nama);
    if (!o.setUmur(umur)) {
        cout << "Umur tidak sah" << endl;
    }
    cout << o.getNama() << " " << o.getUmur() << endl;''', "string"), wajib=W,
     id=("Umur yang Sah", "Buat class `Orang` dengan atribut `private` `string nama` dan `int umur` (mula-mula 0), serta method publik:\n\n"
         "- `void setNama(const string &n)`;\n- `bool setUmur(int u)` — menerima hanya umur 0 sampai 150; untuk nilai lain umur **tidak diubah** dan method mengembalikan `false`;\n- `string getNama() const`, `int getUmur() const`.\n\n"
         "Baca nama dan umur. Bila `setUmur` menolak, cetak `Umur tidak sah`. Lalu cetak nama dan umur yang tersimpan.\n\n"
         "Contoh: `Budi 20` → `Budi 20`. `Citra -3` →\n\n```\nUmur tidak sah\nCitra 0\n```" + W_ID,
         "Satu baris berisi nama (satu kata) dan umur.", "Bila ditolak, baris `Umur tidak sah`; lalu nama dan umur yang tersimpan.", "−20 ≤ umur ≤ 200"),
     en=("A Valid Age", "Make a class `Orang` (person) with the `private` attributes `string nama` and `int umur` (age, starting at 0), and the public methods:\n\n"
         "- `void setNama(const string &n)`;\n- `bool setUmur(int u)` — accepts only ages 0 to 150; for any other value the age is **not changed** and the method returns `false`;\n- `string getNama() const`, `int getUmur() const`.\n\n"
         "Read a name and an age. If `setUmur` refuses, print `Umur tidak sah` (invalid age). Then print the stored name and age.\n\n"
         "Examples: `Budi 20` → `Budi 20`. `Citra -3` →\n\n```\nUmur tidak sah\nCitra 0\n```" + W_EN,
         "One line with a name (one word) and an age.", "If refused, the line `Umur tidak sah`; then the stored name and age.", "−20 ≤ age ≤ 200"),
     ar=("عمر صحيح", "أنشئ صنفًا `Orang` (شخص) خاصيتاه الخاصتان (`private`) `string nama` و `int umur` (العمر، ويبدأ من 0)، وطرائقه العامة:\n\n"
         "- `void setNama(const string &n)`؛\n- `bool setUmur(int u)` — تقبل الأعمار من 0 إلى 150 فقط؛ ولأي قيمة أخرى **لا يتغير** العمر وتُرجع `false`؛\n- `string getNama() const` و `int getUmur() const`.\n\n"
         "اقرأ اسمًا وعمرًا. إذا رفضت `setUmur` فاطبع `Umur tidak sah` (عمر غير صحيح). ثم اطبع الاسم والعمر المخزَّنين.\n\n"
         "أمثلة: `Budi 20` ← `Budi 20`. و `Citra -3` ←\n\n```\nUmur tidak sah\nCitra 0\n```" + W_AR,
         "سطر واحد فيه اسم (كلمة واحدة) وعمر.", "عند الرفض السطر `Umur tidak sah`؛ ثم الاسم والعمر المخزَّنان.", "−20 ≤ العمر ≤ 200"))

soal("penghitung-tak-negatif", "mudah_sekali", "trimmed", "n = {2: 1}.get(i, r.randint(2, 50))\np = {3: 0.0, 4: 1.0}.get(i, r.choice([0.3, 0.5, 0.7]))\nprint(''.join('+' if r.random() < p else '-' for _ in range(n)))\n",
     program(r'''
class Penghitung {
private:
    int nilai = 0;

public:
    void naik() {
        nilai++;
    }

    void turun() {
        if (nilai > 0) {
            nilai--;
        }
    }

    int lihat() const {
        return nilai;
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
    cout << p.lihat() << endl;''', "string"), wajib=W,
     id=("Penghitung Tak Negatif", "Sebuah penghitung pengunjung tidak boleh bernilai negatif. Karena `nilai`-nya `private`, satu-satunya cara mengubahnya adalah lewat method — dan method itulah yang menjaga aturannya.\n\n"
         "Buat class `Penghitung`: atribut `private` `int nilai` (mula-mula 0); method publik `void naik()`, `void turun()` yang **tidak berbuat apa-apa bila nilainya sudah 0**, dan `int lihat() const`.\n\n"
         "Masukan berupa deretan tombol: `+` berarti naik, `-` berarti turun. Cetak nilai akhirnya.\n\nUntuk `-+--++` jawabannya 2." + W_ID,
         "Satu baris: deretan karakter `+` dan `-` tanpa spasi.", "Satu bilangan bulat: nilai akhir.", "Panjang deretan 1 sampai 50."),
     en=("A Counter that Never Goes Negative", "A visitor counter must never be negative. Because its `nilai` is `private`, the only way to change it is through methods — and the methods keep the rule.\n\n"
         "Make a class `Penghitung` (counter): a `private` attribute `int nilai` (starting at 0); the public methods `void naik()` (up), `void turun()` (down), which **does nothing when the value is already 0**, and `int lihat() const` (look).\n\n"
         "The input is a run of buttons: `+` means up, `-` means down. Print the final value.\n\nFor `-+--++` the answer is 2." + W_EN,
         "One line: a run of `+` and `-` characters without spaces.", "One integer: the final value.", "The run is 1 to 50 characters long."),
     ar=("عدّاد لا يصير سالبًا", "عدّاد الزوار لا يجوز أن يكون سالبًا. ولأن `nilai` فيه `private` فالسبيل الوحيد لتغييره هو الطرائق — والطرائق تحفظ القاعدة.\n\n"
         "أنشئ صنفًا `Penghitung` (عدّاد): خاصية `private` هي `int nilai` (تبدأ من 0)؛ والطرائق العامة `void naik()` (زيادة) و `void turun()` (نقصان) التي **لا تفعل شيئًا إذا كانت القيمة 0** و `int lihat() const` (انظر).\n\n"
         "المدخل سلسلة أزرار: `+` تعني زيادة و `-` تعني نقصانًا. اطبع القيمة النهائية.\n\nعند `-+--++` الجواب 2." + W_AR,
         "سطر واحد: سلسلة من المحرفين `+` و `-` بلا مسافات.", "عدد صحيح واحد: القيمة النهائية.", "طول السلسلة من 1 إلى 50."))

soal("nilai-terbatas", "mudah_sekali", "trimmed", "n = {2: 1}.get(i, r.randint(2, 10))\nprint(n)\nprint(*[r.choice([r.randint(0, 100), r.randint(-50, -1), r.randint(101, 150), 0, 100]) for _ in range(n)])\n",
     program(r'''
class Nilai {
private:
    int angka = 0;

public:
    void atur(int x) {
        if (x < 0) {
            x = 0;
        }
        if (x > 100) {
            x = 100;
        }
        angka = x;
    }

    int ambil() const {
        return angka;
    }
};''', r'''    int n;
    cin >> n;
    for (int k = 0; k < n; k++) {
        int x;
        cin >> x;
        Nilai nilai;
        nilai.atur(x);
        cout << nilai.ambil() << " ";
    }
    cout << endl;'''), wajib=W,
     id=("Nilai yang Dibatasi", "Setter tidak harus menolak; ia juga bisa **memperbaiki**. Buat class `Nilai` dengan atribut `private` `int angka` dan method publik:\n\n"
         "- `void atur(int x)` — menyimpan x, tetapi nilai di bawah 0 dijadikan 0 dan nilai di atas 100 dijadikan 100;\n- `int ambil() const`.\n\n"
         "Baca n bilangan. Untuk tiap bilangan, masukkan ke sebuah objek `Nilai` lalu cetak isi objek itu. Semua hasil dicetak dalam satu baris.\n\nUntuk `85 -3 120 100` keluarannya `85 0 100 100`." + W_ID,
         "Baris pertama: n. Baris kedua: n bilangan bulat.", "Satu baris: n nilai setelah dibatasi.", "1 ≤ n ≤ 10; tiap bilangan antara −50 dan 150"),
     en=("A Clamped Mark", "A setter does not have to refuse; it can also **correct**. Make a class `Nilai` (mark) with a `private` attribute `int angka` and the public methods:\n\n"
         "- `void atur(int x)` (set) — stores x, but a value below 0 becomes 0 and a value above 100 becomes 100;\n- `int ambil() const` (get).\n\n"
         "Read n numbers. Put each one into a `Nilai` object and print the content of that object. All results go on one line.\n\nFor `85 -3 120 100` the output is `85 0 100 100`." + W_EN,
         "First line: n. Second line: n integers.", "One line: the n values after clamping.", "1 ≤ n ≤ 10; each number is between −50 and 150"),
     ar=("درجة مقيَّدة", "لا يلزم أن يرفض الضابط (setter)؛ بل يمكنه أن **يصحّح**. أنشئ صنفًا `Nilai` (درجة) فيه خاصية `private` هي `int angka` والطريقتان العامتان:\n\n"
         "- `void atur(int x)` (اضبط) — تخزّن x، لكن القيمة الأقل من 0 تصير 0 والأكبر من 100 تصير 100؛\n- `int ambil() const` (خذ).\n\n"
         "اقرأ n عددًا. ضع كل عدد في كائن `Nilai` واطبع محتوى ذلك الكائن. تُطبع كل النتائج في سطر واحد.\n\nعند `85 -3 120 100` يكون المخرج `85 0 100 100`." + W_AR,
         "السطر الأول: n. السطر الثاني: n عددًا صحيحًا.", "سطر واحد: القيم n بعد التقييد.", "1 ≤ n ≤ 10؛ كل عدد بين −50 و 150"))

soal("pin-kartu", "mudah_sekali", "trimmed", "p = r.randint(1000, 9999)\nt = [r.randint(1000, 9999) for _ in range(3)]\nt[r.randint(0, 2)] = p if i % 3 != 2 else t[0]\nprint(p)\nprint(*t)\n",
     program(r'''
class Kartu {
private:
    int pin;

public:
    Kartu(int p) : pin(p) {
    }

    bool cocok(int tebakan) const {
        return tebakan == pin;
    }
};''', r'''    int pin;
    cin >> pin;
    Kartu kartu(pin);
    for (int k = 0; k < 3; k++) {
        int tebakan;
        cin >> tebakan;
        if (kartu.cocok(tebakan)) {
            cout << "benar" << endl;
        } else {
            cout << "salah" << endl;
        }
    }'''), wajib=W,
     id=("PIN Kartu", "Ada data yang **tidak boleh bisa dibaca sama sekali** dari luar. PIN sebuah kartu tidak diberi getter; yang disediakan hanya method untuk memeriksa tebakan.\n\n"
         "Buat class `Kartu` dengan atribut `private` `int pin`, konstruktor `Kartu(int p)`, dan method publik `bool cocok(int tebakan) const`.\n\n"
         "Baca PIN sebuah kartu, lalu tiga tebakan. Untuk tiap tebakan cetak `benar` atau `salah`.\n\nContoh: PIN 4321 dengan tebakan `1234 4321 1111` →\n\n```\nsalah\nbenar\nsalah\n```" + W_ID,
         "Baris pertama: PIN. Baris kedua: tiga tebakan.", "Tiga baris: `benar` atau `salah`.", "Semua bilangan terdiri dari empat digit (1000 sampai 9999)."),
     en=("Card PIN", "Some data **must not be readable at all** from outside. A card's PIN gets no getter; all that is offered is a method to check a guess.\n\n"
         "Make a class `Kartu` (card) with a `private` attribute `int pin`, a constructor `Kartu(int p)`, and a public method `bool cocok(int tebakan) const` (matches the guess).\n\n"
         "Read the PIN of a card, then three guesses. For each guess print `benar` (right) or `salah` (wrong).\n\nExample: PIN 4321 with the guesses `1234 4321 1111` →\n\n```\nsalah\nbenar\nsalah\n```" + W_EN,
         "First line: the PIN. Second line: three guesses.", "Three lines: `benar` or `salah`.", "All numbers have four digits (1000 to 9999)."),
     ar=("رقم البطاقة السري", "بعض البيانات **يجب ألّا تُقرأ إطلاقًا** من الخارج. الرقم السري للبطاقة لا قارئ (getter) له؛ وكل ما يُتاح طريقة لفحص تخمين.\n\n"
         "أنشئ صنفًا `Kartu` (بطاقة) فيه خاصية `private` هي `int pin`، وبانٍ `Kartu(int p)`، وطريقة عامة `bool cocok(int tebakan) const` (هل يطابق التخمين).\n\n"
         "اقرأ الرقم السري لبطاقة ثم ثلاثة تخمينات. لكل تخمين اطبع `benar` (صحيح) أو `salah` (خطأ).\n\nمثال: الرقم 4321 مع التخمينات `1234 4321 1111` ←\n\n```\nsalah\nbenar\nsalah\n```" + W_AR,
         "السطر الأول: الرقم السري. السطر الثاني: ثلاثة تخمينات.", "ثلاثة أسطر: `benar` أو `salah`.", "كل الأعداد من أربعة أرقام (من 1000 إلى 9999)."))

soal("volume-speaker", "mudah_sekali", "trimmed", "n = {2: 1}.get(i, r.randint(2, 40))\np = {3: 1.0, 4: 0.0}.get(i, r.choice([0.3, 0.5, 0.7]))\nprint(''.join('N' if r.random() < p else 'T' for _ in range(n)))\n",
     program(r'''
class Speaker {
private:
    int volume = 5;

public:
    void naik() {
        if (volume < 10) {
            volume++;
        }
    }

    void turun() {
        if (volume > 0) {
            volume--;
        }
    }

    int lihat() const {
        return volume;
    }
};''', r'''    string tombol;
    cin >> tombol;
    Speaker s;
    for (auto t : tombol) {
        if (t == 'N') {
            s.naik();
        } else {
            s.turun();
        }
    }
    cout << s.lihat() << endl;''', "string"), wajib=W,
     id=("Volume Speaker", "Volume sebuah speaker berkisar dari 0 sampai 10 dan mula-mula 5. Menekan tombol naik di volume 10, atau tombol turun di volume 0, tidak berpengaruh.\n\n"
         "Buat class `Speaker` dengan atribut `private` `int volume` dan method publik `void naik()`, `void turun()`, `int lihat() const`.\n\n"
         "Masukan berupa deretan tombol: `N` berarti naik, `T` berarti turun. Cetak volume akhirnya.\n\nUntuk `NNNNNNNT` jawabannya 9." + W_ID,
         "Satu baris: deretan huruf `N` dan `T` tanpa spasi.", "Satu bilangan bulat: volume akhir.", "Panjang deretan 1 sampai 40."),
     en=("Speaker Volume", "The volume of a speaker ranges from 0 to 10 and starts at 5. Pressing up at volume 10, or down at volume 0, has no effect.\n\n"
         "Make a class `Speaker` with a `private` attribute `int volume` and the public methods `void naik()` (up), `void turun()` (down), `int lihat() const`.\n\n"
         "The input is a run of buttons: `N` means up, `T` means down. Print the final volume.\n\nFor `NNNNNNNT` the answer is 9." + W_EN,
         "One line: a run of the letters `N` and `T` without spaces.", "One integer: the final volume.", "The run is 1 to 40 letters long."),
     ar=("مستوى صوت السماعة", "مستوى صوت السماعة من 0 إلى 10 ويبدأ من 5. الضغط على زر الرفع عند 10 أو زر الخفض عند 0 لا أثر له.\n\n"
         "أنشئ صنفًا `Speaker` فيه خاصية `private` هي `int volume` والطرائق العامة `void naik()` (رفع) و `void turun()` (خفض) و `int lihat() const`.\n\n"
         "المدخل سلسلة أزرار: `N` تعني رفعًا و `T` تعني خفضًا. اطبع المستوى النهائي.\n\nعند `NNNNNNNT` الجواب 9." + W_AR,
         "سطر واحد: سلسلة من الحرفين `N` و `T` بلا مسافات.", "عدد صحيح واحد: المستوى النهائي.", "طول السلسلة من 1 إلى 40."))

soal("jam-valid", "mudah_sekali", "trimmed", "j, m = r.randint(0, 23), r.randint(0, 59)\nif i % 2 == 1:\n    j, m = r.choice([(24, r.randint(0, 59)), (r.randint(0, 23), 60), (-1, 30), (12, -5), (30, 70)])\nif i == 2:\n    j, m = 23, 59\nprint(j, m)\n",
     program(r'''
class Jam {
private:
    int jam = 0;
    int menit = 0;

public:
    bool atur(int j, int m) {
        if (j < 0 || j > 23 || m < 0 || m > 59) {
            return false;
        }
        jam = j;
        menit = m;
        return true;
    }

    void cetak() const {
        cout << setfill('0') << setw(2) << jam << ":" << setw(2) << menit << endl;
    }
};''', r'''    int j, m;
    cin >> j >> m;
    Jam jam;
    if (!jam.atur(j, m)) {
        cout << "Waktu tidak sah" << endl;
    }
    jam.cetak();''', "iomanip"), wajib=W,
     id=("Jam yang Sah", "Buat class `Jam` dengan atribut `private` `int jam` dan `int menit` (keduanya mula-mula 0), serta method publik:\n\n"
         "- `bool atur(int j, int m)` — mengatur waktu **hanya bila keduanya sah** (jam 0–23, menit 0–59); bila tidak, waktu tidak diubah sama sekali dan method mengembalikan `false`;\n- `void cetak() const` — mencetak `JJ:MM`.\n\n"
         "Baca jam dan menit. Bila ditolak, cetak `Waktu tidak sah`. Lalu cetak waktu yang tersimpan.\n\n"
         "Contoh: `9 5` → `09:05`. `12 60` →\n\n```\nWaktu tidak sah\n00:00\n```" + W_ID,
         "Satu baris berisi jam dan menit.", "Bila ditolak, baris `Waktu tidak sah`; lalu waktu yang tersimpan.", "−5 ≤ jam ≤ 30; −5 ≤ menit ≤ 70"),
     en=("A Valid Clock", "Make a class `Jam` (clock) with the `private` attributes `int jam` and `int menit` (both starting at 0), and the public methods:\n\n"
         "- `bool atur(int j, int m)` — sets the time **only when both are valid** (hour 0–23, minute 0–59); otherwise the time is not changed at all and the method returns `false`;\n- `void cetak() const` — prints `HH:MM`.\n\n"
         "Read an hour and a minute. If refused, print `Waktu tidak sah` (invalid time). Then print the stored time.\n\n"
         "Examples: `9 5` → `09:05`. `12 60` →\n\n```\nWaktu tidak sah\n00:00\n```" + W_EN,
         "One line with the hour and the minute.", "If refused, the line `Waktu tidak sah`; then the stored time.", "−5 ≤ hour ≤ 30; −5 ≤ minute ≤ 70"),
     ar=("ساعة صحيحة", "أنشئ صنفًا `Jam` (ساعة) خاصيتاه الخاصتان `int jam` و `int menit` (تبدآن من 0)، وطريقتاه العامتان:\n\n"
         "- `bool atur(int j, int m)` — تضبط الوقت **فقط إذا صحّ كلاهما** (الساعة 0–23 والدقيقة 0–59)؛ وإلا لم يتغير الوقت إطلاقًا وأرجعت `false`؛\n- `void cetak() const` — تطبع `HH:MM`.\n\n"
         "اقرأ ساعة ودقيقة. عند الرفض اطبع `Waktu tidak sah` (وقت غير صحيح). ثم اطبع الوقت المخزَّن.\n\n"
         "أمثلة: `9 5` ← `09:05`. و `12 60` ←\n\n```\nWaktu tidak sah\n00:00\n```" + W_AR,
         "سطر واحد فيه الساعة والدقيقة.", "عند الرفض السطر `Waktu tidak sah`؛ ثم الوقت المخزَّن.", "−5 ≤ الساعة ≤ 30؛ −5 ≤ الدقيقة ≤ 70"))

soal("persegi-baca-saja", "mudah_sekali", "trimmed", "print(r.randint(1, 100), r.randint(1, 100))\n",
     program(r'''
class PersegiPanjang {
private:
    int panjang;
    int lebar;

public:
    PersegiPanjang(int p, int l) : panjang(p), lebar(l) {
    }

    int getPanjang() const {
        return panjang;
    }

    int getLebar() const {
        return lebar;
    }

    int luas() const {
        return panjang * lebar;
    }

    int keliling() const {
        return 2 * (panjang + lebar);
    }
};''', r'''    int p, l;
    cin >> p >> l;
    const PersegiPanjang pp(p, l);
    cout << pp.getPanjang() << " x " << pp.getLebar() << endl;
    cout << "Luas: " << pp.luas() << endl;
    cout << "Keliling: " << pp.keliling() << endl;'''), wajib=W,
     id=("Hanya Bisa Dibaca", "Class yang punya getter tetapi **tidak punya setter** menghasilkan objek yang tidak bisa diubah setelah dibuat. Method yang tidak mengubah objek ditandai `const` di belakang namanya; hanya method seperti itu yang boleh dipanggil pada objek `const`.\n\n"
         "Buat class `PersegiPanjang` dengan atribut `private` `int panjang`, `int lebar`, konstruktor `PersegiPanjang(int p, int l)`, dan method publik yang semuanya `const`: `getPanjang()`, `getLebar()`, `luas()`, `keliling()`.\n\n"
         "Baca panjang dan lebar, buat objek `const PersegiPanjang`, lalu cetak tiga baris.\n\nUntuk `4 6`:\n\n```\n4 x 6\nLuas: 24\nKeliling: 20\n```" + W_ID,
         "Satu baris berisi panjang dan lebar.", "Tiga baris seperti contoh.", "1 ≤ panjang, lebar ≤ 100"),
     en=("Read-Only", "A class with getters but **no setters** yields objects that cannot be changed once created. A method that does not change the object is marked `const` after its name; only such methods may be called on a `const` object.\n\n"
         "Make a class `PersegiPanjang` (rectangle) with the `private` attributes `int panjang`, `int lebar`, a constructor `PersegiPanjang(int p, int l)`, and public methods that are all `const`: `getPanjang()`, `getLebar()`, `luas()`, `keliling()`.\n\n"
         "Read the length and the width, create a `const PersegiPanjang` object, then print three lines.\n\nFor `4 6`:\n\n```\n4 x 6\nLuas: 24\nKeliling: 20\n```" + W_EN,
         "One line with the length and the width.", "Three lines as in the example.", "1 ≤ length, width ≤ 100"),
     ar=("للقراءة فقط", "الصنف الذي له قارئات (getters) و**ليس له ضابطات** (setters) ينتج كائنات لا تتغير بعد إنشائها. الطريقة التي لا تغيّر الكائن توسم بـ `const` بعد اسمها؛ ومثلها فقط يجوز استدعاؤه على كائن `const`.\n\n"
         "أنشئ صنفًا `PersegiPanjang` (مستطيل) خاصيتاه الخاصتان `int panjang` و `int lebar`، وبانٍ `PersegiPanjang(int p, int l)`، وطرائق عامة كلها `const`: `getPanjang()` و `getLebar()` و `luas()` و `keliling()`.\n\n"
         "اقرأ الطول والعرض وأنشئ كائنًا `const PersegiPanjang` ثم اطبع ثلاثة أسطر.\n\nعند `4 6`:\n\n```\n4 x 6\nLuas: 24\nKeliling: 20\n```" + W_AR,
         "سطر واحد فيه الطول والعرض.", "ثلاثة أسطر كما في المثال.", "1 ≤ الطول، العرض ≤ 100"))

# ================================= MUDAH (8) =================================

soal("akun-bank", "mudah", "trimmed",
     NAMA + "print(r.choice(NAMA), r.randint(0, 20) * 50000)\nfor _ in range(r.randint(1, 12)):\n    q = r.random()\n    x = r.randint(1, 20) * 25000\n    if q < 0.15:\n        x = -x\n    if q < 0.25 and q >= 0.15:\n        x = 0\n    print(r.choice(['setor', 'tarik']), x)\nprint('selesai')\n",
     program(r'''
class AkunBank {
private:
    string pemilik;
    int saldo;

public:
    AkunBank(const string &p, int awal) : pemilik(p), saldo(awal) {
    }

    bool setor(int jumlah) {
        if (jumlah <= 0) {
            return false;
        }
        saldo += jumlah;
        return true;
    }

    bool tarik(int jumlah) {
        if (jumlah <= 0 || jumlah > saldo) {
            return false;
        }
        saldo -= jumlah;
        return true;
    }

    int lihatSaldo() const {
        return saldo;
    }

    string getPemilik() const {
        return pemilik;
    }
};''', r'''    string nama, perintah;
    int awal;
    cin >> nama >> awal;
    AkunBank akun(nama, awal);
    while (cin >> perintah) {
        if (perintah == "selesai") {
            break;
        }
        int jumlah;
        cin >> jumlah;
        auto berhasil = false;
        if (perintah == "setor") {
            berhasil = akun.setor(jumlah);
        } else {
            berhasil = akun.tarik(jumlah);
        }
        if (berhasil) {
            cout << "Saldo: " << akun.lihatSaldo() << endl;
        } else {
            cout << "Ditolak" << endl;
        }
    }
    cout << akun.getPemilik() << ": " << akun.lihatSaldo() << endl;''', "string"), wajib=W,
     id=("Akun Bank", "Class `AkunBank` yang saldonya `public` bisa diubah siapa saja menjadi berapa saja. Perbaiki: sembunyikan datanya, dan biarkan hanya method yang memeriksa aturan yang boleh mengubahnya.\n\n"
         "Buat class `AkunBank` dengan atribut `private` `string pemilik`, `int saldo`, konstruktor `AkunBank(const string &p, int awal)`, dan method publik:\n\n"
         "- `bool setor(int jumlah)` — ditolak bila jumlahnya nol atau negatif;\n- `bool tarik(int jumlah)` — ditolak bila jumlahnya nol, negatif, atau lebih besar daripada saldo;\n- `int lihatSaldo() const`, `string getPemilik() const`.\n\n"
         "Masukan: nama pemilik dan saldo awal, lalu perintah `setor x` atau `tarik x`, diakhiri `selesai`. Untuk tiap perintah cetak `Saldo: s` bila berhasil atau `Ditolak` bila tidak. Di akhir cetak `<pemilik>: <saldo>`.\n\n"
         "Contoh: `Budi 100000`; `tarik 150000`, `setor -5000`, `setor 25000`, `tarik 125000`, `selesai` →\n\n```\nDitolak\nDitolak\nSaldo: 125000\nSaldo: 0\nBudi: 0\n```" + W_ID,
         "Baris pertama: nama (satu kata) dan saldo awal. Lalu perintah-perintah, diakhiri `selesai`.", "Satu baris per perintah, lalu satu baris ringkasan.", "0 ≤ saldo awal ≤ 1000000; paling banyak 12 perintah; −500000 ≤ x ≤ 500000"),
     en=("Bank Account", "A class `AkunBank` whose balance is `public` can be changed by anyone to anything. Fix it: hide the data, and let only methods that check the rules change it.\n\n"
         "Make a class `AkunBank` with the `private` attributes `string pemilik` (owner), `int saldo` (balance), a constructor `AkunBank(const string &p, int awal)`, and the public methods:\n\n"
         "- `bool setor(int jumlah)` (deposit) — refused when the amount is zero or negative;\n- `bool tarik(int jumlah)` (withdraw) — refused when the amount is zero, negative, or larger than the balance;\n- `int lihatSaldo() const`, `string getPemilik() const`.\n\n"
         "Input: the owner's name and the opening balance, then commands `setor x` or `tarik x`, ending with `selesai`. For each command print `Saldo: s` on success or `Ditolak` (refused) otherwise. At the end print `<owner>: <balance>`.\n\n"
         "Example: `Budi 100000`; `tarik 150000`, `setor -5000`, `setor 25000`, `tarik 125000`, `selesai` →\n\n```\nDitolak\nDitolak\nSaldo: 125000\nSaldo: 0\nBudi: 0\n```" + W_EN,
         "First line: the name (one word) and the opening balance. Then the commands, ending with `selesai`.", "One line per command, then one summary line.", "0 ≤ opening balance ≤ 1000000; at most 12 commands; −500000 ≤ x ≤ 500000"),
     ar=("الحساب المصرفي", "الصنف `AkunBank` الذي رصيده `public` يستطيع أي أحد أن يغيّره إلى أي قيمة. أصلِحه: أخفِ البيانات ودع الطرائق التي تفحص القواعد وحدها تغيّرها.\n\n"
         "أنشئ صنفًا `AkunBank` خاصيتاه الخاصتان `string pemilik` (المالك) و `int saldo` (الرصيد)، وبانٍ `AkunBank(const string &p, int awal)`، وطرائقه العامة:\n\n"
         "- `bool setor(int jumlah)` (إيداع) — يُرفض إذا كان المبلغ صفرًا أو سالبًا؛\n- `bool tarik(int jumlah)` (سحب) — يُرفض إذا كان المبلغ صفرًا أو سالبًا أو أكبر من الرصيد؛\n- `int lihatSaldo() const` و `string getPemilik() const`.\n\n"
         "المدخل: اسم المالك والرصيد الابتدائي ثم أوامر `setor x` أو `tarik x` تنتهي بـ `selesai`. لكل أمر اطبع `Saldo: s` عند النجاح أو `Ditolak` (مرفوض) عند غيره. في النهاية اطبع `<المالك>: <الرصيد>`.\n\n"
         "مثال: `Budi 100000`؛ `tarik 150000` ثم `setor -5000` ثم `setor 25000` ثم `tarik 125000` ثم `selesai` ←\n\n```\nDitolak\nDitolak\nSaldo: 125000\nSaldo: 0\nBudi: 0\n```" + W_AR,
         "السطر الأول: الاسم (كلمة واحدة) والرصيد الابتدائي. ثم الأوامر منتهيةً بـ `selesai`.", "سطر لكل أمر ثم سطر ملخص.", "0 ≤ الرصيد الابتدائي ≤ 1000000؛ 12 أمرًا على الأكثر؛ −500000 ≤ x ≤ 500000"))

soal("kemajuan-membaca", "mudah", "trimmed",
     "JUDUL = ['Algoritma', 'Kalkulus', 'Fisika', 'Sejarah', 'Statistika', 'Biologi', 'Ekonomi', 'Logika']\nh = r.randint(2, 40) * 10\nn = r.randint(1, 8)\nprint(r.choice(JUDUL), h)\nprint(n)\nprint(*[r.randint(1, h // 2 + 10) for _ in range(n)])\n",
     program(r'''
class Buku {
private:
    string judul;
    int halaman;
    int dibaca = 0;

public:
    Buku(const string &j, int h) : judul(j), halaman(h) {
    }

    void baca(int n) {
        dibaca += n;
        if (dibaca > halaman) {
            dibaca = halaman;
        }
    }

    string getJudul() const {
        return judul;
    }

    int sisa() const {
        return halaman - dibaca;
    }

    int persen() const {
        return dibaca * 100 / halaman;
    }

    bool tamat() const {
        return dibaca == halaman;
    }
};

void laporan(const Buku &b) {
    if (b.tamat()) {
        cout << b.getJudul() << ": tamat" << endl;
    } else {
        cout << b.getJudul() << ": " << b.persen() << "% (sisa " << b.sisa() << " halaman)" << endl;
    }
}''', r'''    string judul;
    int halaman, n;
    cin >> judul >> halaman;
    cin >> n;
    Buku buku(judul, halaman);
    for (int k = 0; k < n; k++) {
        int x;
        cin >> x;
        buku.baca(x);
        laporan(buku);
    }''', "string"), wajib=W,
     id=("Kemajuan Membaca", "Buat class `Buku` dengan atribut `private` `string judul`, `int halaman`, `int dibaca` (mula-mula 0), konstruktor `Buku(const string &j, int h)`, dan method publik:\n\n"
         "- `void baca(int n)` — menambah halaman yang sudah dibaca, tetapi **tidak pernah melebihi banyak halaman buku**;\n- `string getJudul() const`, `int sisa() const`, `bool tamat() const`;\n- `int persen() const` — dibaca × 100 / halaman, dengan pembagian bulat.\n\n"
         "Tulis juga fungsi biasa `void laporan(const Buku &b)`. Parameternya `const`, jadi fungsi itu hanya bisa memanggil method yang bertanda `const`. Ia mencetak `<judul>: <persen>% (sisa <sisa> halaman)`, atau `<judul>: tamat` bila bukunya sudah habis dibaca.\n\n"
         "Baca judul, banyak halaman, lalu n sesi membaca. Panggil `laporan` setelah tiap sesi.\n\n"
         "Contoh: `Fisika 200`, sesi `50 120 90` →\n\n```\nFisika: 25% (sisa 150 halaman)\nFisika: 85% (sisa 30 halaman)\nFisika: tamat\n```" + W_ID,
         "Baris pertama: judul (satu kata) dan banyak halaman. Baris kedua: n. Baris ketiga: n bilangan, halaman yang dibaca tiap sesi.", "n baris laporan.", "20 ≤ halaman ≤ 400; 1 ≤ n ≤ 8; tiap sesi antara 1 dan 210 halaman"),
     en=("Reading Progress", "Make a class `Buku` (book) with the `private` attributes `string judul` (title), `int halaman` (pages), `int dibaca` (pages read, starting at 0), a constructor `Buku(const string &j, int h)`, and the public methods:\n\n"
         "- `void baca(int n)` (read) — adds to the pages read, but **never beyond the number of pages in the book**;\n- `string getJudul() const`, `int sisa() const` (pages left), `bool tamat() const` (finished);\n- `int persen() const` — read × 100 / pages, with integer division.\n\n"
         "Also write an ordinary function `void laporan(const Buku &b)` (report). Its parameter is `const`, so the function can call only methods marked `const`. It prints `<title>: <percent>% (sisa <left> halaman)`, or `<title>: tamat` when the book is finished.\n\n"
         "Read the title, the number of pages, then n reading sessions. Call `laporan` after every session.\n\n"
         "Example: `Fisika 200`, sessions `50 120 90` →\n\n```\nFisika: 25% (sisa 150 halaman)\nFisika: 85% (sisa 30 halaman)\nFisika: tamat\n```" + W_EN,
         "First line: the title (one word) and the number of pages. Second line: n. Third line: n numbers, the pages read in each session.", "n report lines.", "20 ≤ pages ≤ 400; 1 ≤ n ≤ 8; each session is between 1 and 210 pages"),
     ar=("التقدم في القراءة", "أنشئ صنفًا `Buku` (كتاب) خصائصه الخاصة `string judul` (العنوان) و `int halaman` (الصفحات) و `int dibaca` (المقروء، ويبدأ من 0)، وبانٍ `Buku(const string &j, int h)`، وطرائقه العامة:\n\n"
         "- `void baca(int n)` (اقرأ) — تزيد الصفحات المقروءة **دون أن تتجاوز عدد صفحات الكتاب أبدًا**؛\n- `string getJudul() const` و `int sisa() const` (الباقي) و `bool tamat() const` (انتهى)؛\n- `int persen() const` — المقروء × 100 / الصفحات بقسمة صحيحة.\n\n"
         "اكتب أيضًا دالة عادية `void laporan(const Buku &b)` (تقرير). وسيطها `const`، فلا تستطيع أن تستدعي إلا الطرائق الموسومة بـ `const`. تطبع `<العنوان>: <النسبة>% (sisa <الباقي> halaman)`، أو `<العنوان>: tamat` إذا انتهى الكتاب.\n\n"
         "اقرأ العنوان وعدد الصفحات ثم n جلسة قراءة. استدعِ `laporan` بعد كل جلسة.\n\n"
         "مثال: `Fisika 200` والجلسات `50 120 90` ←\n\n```\nFisika: 25% (sisa 150 halaman)\nFisika: 85% (sisa 30 halaman)\nFisika: tamat\n```" + W_AR,
         "السطر الأول: العنوان (كلمة واحدة) وعدد الصفحات. السطر الثاني: n. السطر الثالث: n عددًا هي الصفحات المقروءة في كل جلسة.", "n سطر تقرير.", "20 ≤ الصفحات ≤ 400؛ 1 ≤ n ≤ 8؛ كل جلسة بين 1 و 210 صفحات"))

soal("sensor-mutable", "mudah", "trimmed", "print(r.randint(0, 99))\nfor _ in range(r.randint(1, 15)):\n    if r.random() < 0.6:\n        print('baca')\n    else:\n        print('ubah', r.randint(0, 99))\nprint('selesai')\n",
     program(r'''
class Sensor {
private:
    int nilai;
    mutable int dibaca = 0;

public:
    Sensor(int awal) : nilai(awal) {
    }

    int baca() const {
        dibaca++;
        return nilai;
    }

    void ubah(int baru) {
        nilai = baru;
    }

    int banyakBaca() const {
        return dibaca;
    }
};''', r'''    int awal;
    cin >> awal;
    Sensor sensor(awal);
    string perintah;
    while (cin >> perintah) {
        if (perintah == "selesai") {
            break;
        }
        if (perintah == "baca") {
            cout << sensor.baca() << endl;
        } else {
            int x;
            cin >> x;
            sensor.ubah(x);
        }
    }
    cout << "Dibaca: " << sensor.banyakBaca() << endl;''', "string"), wajib=W,
     id=("Sensor dan mutable", "Method `const` berjanji tidak mengubah objek. Namun kadang ada catatan dalam — misalnya penghitung \"sudah berapa kali dibaca\" — yang perlu berubah walaupun, dari luar, objeknya tetap sama. Atribut seperti itu ditandai `mutable`.\n\n"
         "Buat class `Sensor` dengan atribut `private` `int nilai` dan `mutable int dibaca` (mula-mula 0), konstruktor `Sensor(int awal)`, dan method publik:\n\n"
         "- `int baca() const` — menaikkan `dibaca`, lalu mengembalikan nilai;\n- `void ubah(int baru)`;\n- `int banyakBaca() const`.\n\n"
         "Masukan: nilai awal, lalu perintah `baca` atau `ubah x`, diakhiri `selesai`. Untuk tiap `baca` cetak nilainya. Di akhir cetak `Dibaca: k`.\n\n"
         "Contoh: nilai awal 20; `baca`, `ubah 35`, `baca`, `baca`, `selesai` →\n\n```\n20\n35\n35\nDibaca: 3\n```" + W_ID,
         "Baris pertama: nilai awal. Lalu perintah-perintah, diakhiri `selesai`.", "Satu baris untuk tiap `baca`, lalu `Dibaca: k`.", "Paling banyak 15 perintah; semua nilai antara 0 dan 99."),
     en=("Sensor and mutable", "A `const` method promises not to change the object. Yet sometimes an inner note — such as a counter \"how often was this read\" — has to change although, seen from outside, the object stays the same. Such an attribute is marked `mutable`.\n\n"
         "Make a class `Sensor` with the `private` attributes `int nilai` (value) and `mutable int dibaca` (times read, starting at 0), a constructor `Sensor(int awal)`, and the public methods:\n\n"
         "- `int baca() const` (read) — raises `dibaca`, then returns the value;\n- `void ubah(int baru)` (change);\n- `int banyakBaca() const`.\n\n"
         "Input: the starting value, then commands `baca` or `ubah x`, ending with `selesai`. For every `baca` print the value. At the end print `Dibaca: k`.\n\n"
         "Example: starting value 20; `baca`, `ubah 35`, `baca`, `baca`, `selesai` →\n\n```\n20\n35\n35\nDibaca: 3\n```" + W_EN,
         "First line: the starting value. Then the commands, ending with `selesai`.", "One line for every `baca`, then `Dibaca: k`.", "At most 15 commands; all values are between 0 and 99."),
     ar=("المستشعر و mutable", "الطريقة `const` تتعهد ألّا تغيّر الكائن. لكن أحيانًا يلزم أن يتغير تدوين داخلي — كعدّاد «كم مرة قُرئ» — مع أن الكائن من الخارج يبقى كما هو. خاصية كهذه توسم بـ `mutable`.\n\n"
         "أنشئ صنفًا `Sensor` (مستشعر) خاصيتاه الخاصتان `int nilai` (القيمة) و `mutable int dibaca` (مرات القراءة، وتبدأ من 0)، وبانٍ `Sensor(int awal)`، وطرائقه العامة:\n\n"
         "- `int baca() const` (اقرأ) — تزيد `dibaca` ثم تُرجع القيمة؛\n- `void ubah(int baru)` (غيِّر)؛\n- `int banyakBaca() const`.\n\n"
         "المدخل: القيمة الابتدائية ثم أوامر `baca` أو `ubah x` تنتهي بـ `selesai`. لكل `baca` اطبع القيمة. في النهاية اطبع `Dibaca: k`.\n\n"
         "مثال: القيمة الابتدائية 20؛ `baca` ثم `ubah 35` ثم `baca` ثم `baca` ثم `selesai` ←\n\n```\n20\n35\n35\nDibaca: 3\n```" + W_AR,
         "السطر الأول: القيمة الابتدائية. ثم الأوامر منتهيةً بـ `selesai`.", "سطر لكل `baca` ثم `Dibaca: k`.", "15 أمرًا على الأكثر؛ كل القيم بين 0 و 99."))

soal("pecahan-sama", "mudah", "trimmed", "a, b = r.randint(1, 30), r.randint(1, 30)\nk = r.randint(1, 30)\nc, d = a * k, b * k\nif i % 2 == 1:\n    c += r.choice([-1, 1]) if c > 1 else 1\nif i == 2:\n    c, d = a, b\nprint(a, b)\nprint(c, d)\n",
     program(r'''
class Pecahan {
private:
    int pembilang;
    int penyebut;

public:
    Pecahan(int a, int b) : pembilang(a), penyebut(b) {
    }

    friend bool sama(const Pecahan &x, const Pecahan &y);
};

bool sama(const Pecahan &x, const Pecahan &y) {
    return x.pembilang * y.penyebut == y.pembilang * x.penyebut;
}''', r'''    int a, b, c, d;
    cin >> a >> b;
    cin >> c >> d;
    Pecahan p(a, b);
    Pecahan q(c, d);
    if (sama(p, q)) {
        cout << "sama" << endl;
    } else {
        cout << "beda" << endl;
    }'''), wajib=W,
     id=("Fungsi friend", "Sebuah fungsi biasa — bukan method — tidak boleh menyentuh anggota `private`. Class dapat memberi pengecualian dengan menyatakan fungsi itu sebagai **`friend`** di dalam class.\n\n"
         "Buat class `Pecahan` dengan atribut `private` `int pembilang`, `int penyebut`, konstruktor `Pecahan(int a, int b)`, **tanpa getter**, dan deklarasi\n\n```\nfriend bool sama(const Pecahan &x, const Pecahan &y);\n```\n\n"
         "Tulis fungsi `sama` di luar class. Dua pecahan a/b dan c/d bernilai sama tepat bila a × d = c × b; tidak perlu menyederhanakan.\n\n"
         "Baca dua pecahan dan cetak `sama` atau `beda`.\n\nContoh: `1 2` dan `3 6` → `sama`; `2 3` dan `3 4` → `beda`.\n\n`friend` membuka pintu enkapsulasi; pakailah hanya bila memang perlu." + W_ID,
         "Dua baris, masing-masing berisi pembilang dan penyebut sebuah pecahan.", "`sama` atau `beda`.", "Semua bilangan antara 1 dan 900."),
     en=("A friend Function", "An ordinary function — not a method — may not touch `private` members. A class can make an exception by declaring that function a **`friend`** inside the class.\n\n"
         "Make a class `Pecahan` (fraction) with the `private` attributes `int pembilang`, `int penyebut`, a constructor `Pecahan(int a, int b)`, **no getters**, and the declaration\n\n```\nfriend bool sama(const Pecahan &x, const Pecahan &y);\n```\n\n"
         "Write the function `sama` (equal) outside the class. Two fractions a/b and c/d are equal exactly when a × d = c × b; there is no need to reduce them.\n\n"
         "Read two fractions and print `sama` (equal) or `beda` (different).\n\nExamples: `1 2` and `3 6` → `sama`; `2 3` and `3 4` → `beda`.\n\n`friend` opens the door of encapsulation; use it only when it is really needed." + W_EN,
         "Two lines, each with the numerator and the denominator of a fraction.", "`sama` or `beda`.", "All numbers are between 1 and 900."),
     ar=("دالة صديقة", "الدالة العادية — التي ليست طريقة — لا يجوز لها أن تمسّ الأعضاء `private`. يستطيع الصنف أن يستثنيها بالتصريح بها **`friend`** داخل الصنف.\n\n"
         "أنشئ صنفًا `Pecahan` (كسر) خاصيتاه الخاصتان `int pembilang` و `int penyebut`، وبانٍ `Pecahan(int a, int b)`، **بلا قارئات**، والتصريح\n\n```\nfriend bool sama(const Pecahan &x, const Pecahan &y);\n```\n\n"
         "اكتب الدالة `sama` (متساويان) خارج الصنف. الكسران a/b و c/d متساويان تمامًا إذا كان a × d = c × b؛ ولا حاجة إلى التبسيط.\n\n"
         "اقرأ كسرين واطبع `sama` (متساويان) أو `beda` (مختلفان).\n\nأمثلة: `1 2` و `3 6` ← `sama`؛ `2 3` و `3 4` ← `beda`.\n\n`friend` تفتح باب التغليف؛ فلا تستعملها إلا عند الحاجة الحقيقية." + W_AR,
         "سطران في كل منهما بسط كسر ومقامه.", "`sama` أو `beda`.", "كل الأعداد بين 1 و 900."))

soal("auditor-brankas", "mudah", "trimmed", "n = {2: 1}.get(i, r.randint(2, 8))\nprint(n)\nfor _ in range(n):\n    k = r.randint(0, 5)\n    print(k, *[r.choice([r.randint(1, 50) * 1000, -r.randint(1, 9) * 1000, 0]) for _ in range(k)])\n",
     program(r'''
class Brankas {
private:
    int isi = 0;

public:
    void simpan(int jumlah) {
        if (jumlah > 0) {
            isi += jumlah;
        }
    }

    friend class Auditor;
};

class Auditor {
private:
    int total = 0;
    int terbesar = 0;

public:
    void periksa(const Brankas &b) {
        total += b.isi;
        if (b.isi > terbesar) {
            terbesar = b.isi;
        }
    }

    int getTotal() const {
        return total;
    }

    int getTerbesar() const {
        return terbesar;
    }
};''', r'''    int n;
    cin >> n;
    Auditor auditor;
    for (int k = 0; k < n; k++) {
        int banyak;
        cin >> banyak;
        Brankas brankas;
        for (int s = 0; s < banyak; s++) {
            int jumlah;
            cin >> jumlah;
            brankas.simpan(jumlah);
        }
        auditor.periksa(brankas);
    }
    cout << "Total: " << auditor.getTotal() << endl;
    cout << "Terbesar: " << auditor.getTerbesar() << endl;'''), wajib=W,
     id=("Auditor Brankas", "Isi sebuah brankas tidak boleh bisa dilihat siapa pun — kecuali auditor. Sebuah class dapat menunjuk **class lain** sebagai `friend`; semua method class sahabat itu lalu boleh membaca anggota `private`-nya.\n\n"
         "Buat class `Brankas` dengan atribut `private` `int isi` (mula-mula 0), method publik `void simpan(int jumlah)` yang hanya menerima jumlah positif, **tanpa getter**, dan baris `friend class Auditor;`.\n\n"
         "Buat class `Auditor` dengan atribut `private` `int total`, `int terbesar` dan method publik `void periksa(const Brankas &b)` — membaca `b.isi` langsung — serta `getTotal()` dan `getTerbesar()`.\n\n"
         "Masukan berisi n brankas. Tiap baris: banyak setoran k, lalu k jumlah (jumlah nol atau negatif diabaikan `simpan`). Auditor memeriksa tiap brankas. Cetak `Total: T` dan `Terbesar: M` (isi brankas yang paling banyak).\n\n"
         "Contoh: brankas `2 5000 3000` dan `3 10000 -2000 1000` →\n\n```\nTotal: 19000\nTerbesar: 11000\n```" + W_ID,
         "Baris pertama: n. Lalu n baris: k diikuti k bilangan bulat.", "Dua baris: `Total: T` dan `Terbesar: M`.", "1 ≤ n ≤ 8; 0 ≤ k ≤ 5; tiap jumlah antara −9000 dan 50000"),
     en=("The Vault Auditor", "Nobody may see what is in a vault — except the auditor. A class can name **another class** as a `friend`; all methods of that friend class may then read its `private` members.\n\n"
         "Make a class `Brankas` (vault) with a `private` attribute `int isi` (content, starting at 0), a public method `void simpan(int jumlah)` (store) that accepts positive amounts only, **no getter**, and the line `friend class Auditor;`.\n\n"
         "Make a class `Auditor` with the `private` attributes `int total`, `int terbesar` (largest) and a public method `void periksa(const Brankas &b)` (inspect) — which reads `b.isi` directly — plus `getTotal()` and `getTerbesar()`.\n\n"
         "The input holds n vaults. Each line: the number of deposits k, then k amounts (zero or negative amounts are ignored by `simpan`). The auditor inspects every vault. Print `Total: T` and `Terbesar: M` (the content of the fullest vault).\n\n"
         "Example: the vaults `2 5000 3000` and `3 10000 -2000 1000` →\n\n```\nTotal: 19000\nTerbesar: 11000\n```" + W_EN,
         "First line: n. Then n lines: k followed by k integers.", "Two lines: `Total: T` and `Terbesar: M`.", "1 ≤ n ≤ 8; 0 ≤ k ≤ 5; each amount is between −9000 and 50000"),
     ar=("مدقق الخزائن", "لا يجوز لأحد أن يرى ما في الخزنة — إلا المدقق. يستطيع صنف أن يسمّي **صنفًا آخر** صديقًا (`friend`)؛ فتستطيع كل طرائق الصنف الصديق قراءة أعضائه `private`.\n\n"
         "أنشئ صنفًا `Brankas` (خزنة) فيه خاصية `private` هي `int isi` (المحتوى، ويبدأ من 0)، وطريقة عامة `void simpan(int jumlah)` (خزِّن) تقبل المبالغ الموجبة فقط، **بلا قارئ**، والسطر `friend class Auditor;`.\n\n"
         "أنشئ صنفًا `Auditor` (مدقق) خاصيتاه الخاصتان `int total` و `int terbesar` (الأكبر) وطريقة عامة `void periksa(const Brankas &b)` (افحص) — تقرأ `b.isi` مباشرة — مع `getTotal()` و `getTerbesar()`.\n\n"
         "في المدخل n خزنة. في كل سطر: عدد الإيداعات k ثم k مبلغًا (المبالغ الصفرية أو السالبة تهملها `simpan`). يفحص المدقق كل خزنة. اطبع `Total: T` و `Terbesar: M` (محتوى أكثر الخزائن امتلاءً).\n\n"
         "مثال: الخزنتان `2 5000 3000` و `3 10000 -2000 1000` ←\n\n```\nTotal: 19000\nTerbesar: 11000\n```" + W_AR,
         "السطر الأول: n. ثم n سطرًا: k يليها k عددًا صحيحًا.", "سطران: `Total: T` و `Terbesar: M`.", "1 ≤ n ≤ 8؛ 0 ≤ k ≤ 5؛ كل مبلغ بين −9000 و 50000"))

soal("rentang-terjaga", "mudah", "trimmed", "n = r.randint(1, 10)\nprint(n)\nfor _ in range(n):\n    print(r.choice(['bawah', 'atas']), r.randint(-20, 120))\n",
     program(r'''
class Rentang {
private:
    int bawah = 0;
    int atas = 100;

public:
    bool setBawah(int b) {
        if (b > atas) {
            return false;
        }
        bawah = b;
        return true;
    }

    bool setAtas(int a) {
        if (a < bawah) {
            return false;
        }
        atas = a;
        return true;
    }

    int getBawah() const {
        return bawah;
    }

    int getAtas() const {
        return atas;
    }
};''', r'''    int n;
    cin >> n;
    Rentang r;
    for (int k = 0; k < n; k++) {
        string sisi;
        int x;
        cin >> sisi >> x;
        auto berhasil = false;
        if (sisi == "bawah") {
            berhasil = r.setBawah(x);
        } else {
            berhasil = r.setAtas(x);
        }
        if (berhasil) {
            cout << "ok" << endl;
        } else {
            cout << "ditolak" << endl;
        }
    }
    cout << "[" << r.getBawah() << ", " << r.getAtas() << "]" << endl;''', "string"), wajib=W,
     id=("Rentang yang Terjaga", "Kadang aturan menyangkut **dua atribut sekaligus**. Sebuah rentang [bawah, atas] hanya masuk akal bila bawah tidak lebih besar daripada atas. Bila keduanya `public`, tidak ada yang bisa menjamin itu.\n\n"
         "Buat class `Rentang` dengan atribut `private` `int bawah` (mula-mula 0) dan `int atas` (mula-mula 100), serta method publik:\n\n"
         "- `bool setBawah(int b)` — ditolak bila b lebih besar daripada `atas` saat itu;\n- `bool setAtas(int a)` — ditolak bila a lebih kecil daripada `bawah` saat itu;\n- `int getBawah() const`, `int getAtas() const`.\n\n"
         "Baca n perintah `bawah x` atau `atas x`. Untuk tiap perintah cetak `ok` atau `ditolak`. Di akhir cetak rentangnya sebagai `[bawah, atas]`.\n\n"
         "Contoh: `atas 50`, `bawah 70`, `bawah 50`, `atas 10` →\n\n```\nok\nditolak\nok\nditolak\n[50, 50]\n```" + W_ID,
         "Baris pertama: n. Lalu n baris perintah.", "n baris `ok` atau `ditolak`, lalu rentang akhir.", "1 ≤ n ≤ 10; −20 ≤ x ≤ 120"),
     en=("A Guarded Range", "Sometimes a rule concerns **two attributes at once**. A range [low, high] makes sense only when low is not larger than high. If both are `public`, nothing can guarantee that.\n\n"
         "Make a class `Rentang` (range) with the `private` attributes `int bawah` (low, starting at 0) and `int atas` (high, starting at 100), and the public methods:\n\n"
         "- `bool setBawah(int b)` — refused when b is larger than the current `atas`;\n- `bool setAtas(int a)` — refused when a is smaller than the current `bawah`;\n- `int getBawah() const`, `int getAtas() const`.\n\n"
         "Read n commands `bawah x` or `atas x`. For each command print `ok` or `ditolak` (refused). At the end print the range as `[low, high]`.\n\n"
         "Example: `atas 50`, `bawah 70`, `bawah 50`, `atas 10` →\n\n```\nok\nditolak\nok\nditolak\n[50, 50]\n```" + W_EN,
         "First line: n. Then n command lines.", "n lines `ok` or `ditolak`, then the final range.", "1 ≤ n ≤ 10; −20 ≤ x ≤ 120"),
     ar=("مجال محروس", "أحيانًا تتعلق القاعدة **بخاصيتين معًا**. المجال [الأدنى، الأعلى] لا معنى له إلا إذا لم يزد الأدنى على الأعلى. وإذا كانتا `public` فلا شيء يضمن ذلك.\n\n"
         "أنشئ صنفًا `Rentang` (مجال) خاصيتاه الخاصتان `int bawah` (الأدنى، ويبدأ من 0) و `int atas` (الأعلى، ويبدأ من 100)، وطرائقه العامة:\n\n"
         "- `bool setBawah(int b)` — تُرفض إذا كان b أكبر من `atas` الحالي؛\n- `bool setAtas(int a)` — تُرفض إذا كان a أصغر من `bawah` الحالي؛\n- `int getBawah() const` و `int getAtas() const`.\n\n"
         "اقرأ n أمرًا `bawah x` أو `atas x`. لكل أمر اطبع `ok` أو `ditolak` (مرفوض). في النهاية اطبع المجال على الصورة `[الأدنى, الأعلى]`.\n\n"
         "مثال: `atas 50` ثم `bawah 70` ثم `bawah 50` ثم `atas 10` ←\n\n```\nok\nditolak\nok\nditolak\n[50, 50]\n```" + W_AR,
         "السطر الأول: n. ثم n سطر أوامر.", "n سطرًا `ok` أو `ditolak` ثم المجال النهائي.", "1 ≤ n ≤ 10؛ −20 ≤ x ≤ 120"))

soal("warna-heks", "mudah", "trimmed", "v = [r.randint(0, 255) for _ in range(3)]\nif i % 2 == 1:\n    v[r.randint(0, 2)] = r.choice([-r.randint(1, 100), r.randint(256, 400)])\nif i == 2:\n    v = [0, 0, 0]\nif i == 4:\n    v = [255, 255, 255]\nif i == 6:\n    v = [10, 171, 5]\nprint(*v)\n",
     program(r'''
class Warna {
private:
    int merah;
    int hijau;
    int biru;

    static int batasi(int v) {
        if (v < 0) {
            return 0;
        }
        if (v > 255) {
            return 255;
        }
        return v;
    }

    static string duaHeks(int v) {
        string digit = "0123456789ABCDEF";
        string hasil = "";
        hasil += digit[v / 16];
        hasil += digit[v % 16];
        return hasil;
    }

public:
    Warna(int r, int g, int b) : merah(batasi(r)), hijau(batasi(g)), biru(batasi(b)) {
    }

    string heks() const {
        return "#" + duaHeks(merah) + duaHeks(hijau) + duaHeks(biru);
    }
};''', r'''    int r, g, b;
    cin >> r >> g >> b;
    Warna w(r, g, b);
    cout << w.heks() << endl;''', "string"), wajib=W,
     id=("Warna Heksadesimal", "Sebuah warna di layar terdiri dari tiga komponen — merah, hijau, biru — masing-masing antara 0 dan 255, dan biasa ditulis sebagai `#RRGGBB` dengan dua digit heksadesimal (huruf besar) per komponen.\n\n"
         "Buat class `Warna` dengan atribut `private` `int merah`, `int hijau`, `int biru`. Konstruktor `Warna(int r, int g, int b)` **membatasi** tiap komponen: nilai di bawah 0 menjadi 0, di atas 255 menjadi 255. Method publik `string heks() const` mengembalikan tulisannya.\n\n"
         "Method bantu yang hanya dipakai di dalam class — misalnya untuk membatasi nilai atau mengubah satu komponen menjadi dua digit heksadesimal — sebaiknya juga `private`.\n\n"
         "Contoh: `255 128 0` → `#FF8000`; `300 -20 10` → `#FF000A`." + W_ID,
         "Satu baris berisi tiga bilangan bulat: merah, hijau, biru.", "Warna dalam bentuk `#RRGGBB`.", "Tiap bilangan antara −100 dan 400."),
     en=("Hexadecimal Colour", "A colour on screen has three components — red, green, blue — each between 0 and 255, and is usually written `#RRGGBB` with two hexadecimal digits (capital letters) per component.\n\n"
         "Make a class `Warna` (colour) with the `private` attributes `int merah`, `int hijau`, `int biru`. The constructor `Warna(int r, int g, int b)` **clamps** each component: a value below 0 becomes 0, above 255 becomes 255. A public method `string heks() const` returns the written form.\n\n"
         "Helper methods used only inside the class — for clamping a value, or turning one component into two hexadecimal digits — had better be `private` too.\n\n"
         "Examples: `255 128 0` → `#FF8000`; `300 -20 10` → `#FF000A`." + W_EN,
         "One line with three integers: red, green, blue.", "The colour in the form `#RRGGBB`.", "Each number is between −100 and 400."),
     ar=("لون بالست عشري", "اللون على الشاشة من ثلاث مركّبات — أحمر وأخضر وأزرق — كل منها بين 0 و 255، ويُكتب عادة `#RRGGBB` برقمين ست عشريين (بحروف كبيرة) لكل مركّبة.\n\n"
         "أنشئ صنفًا `Warna` (لون) خصائصه الخاصة `int merah` و `int hijau` و `int biru`. الباني `Warna(int r, int g, int b)` **يقيّد** كل مركّبة: ما دون 0 يصير 0 وما فوق 255 يصير 255. والطريقة العامة `string heks() const` تُرجع الصيغة المكتوبة.\n\n"
         "الطرائق المساعدة المستعملة داخل الصنف فقط — لتقييد قيمة أو لتحويل مركّبة إلى رقمين ست عشريين — يحسن أن تكون `private` أيضًا.\n\n"
         "أمثلة: `255 128 0` ← `#FF8000`؛ `300 -20 10` ← `#FF000A`." + W_AR,
         "سطر واحد فيه ثلاثة أعداد صحيحة: الأحمر والأخضر والأزرق.", "اللون على الصورة `#RRGGBB`.", "كل عدد بين −100 و 400."))

soal("ganti-sandi", "mudah", "trimmed",
     "def sandi(kuat):\n    n = r.randint(8, 12) if kuat else r.randint(3, 7)\n    t = [r.choice('abcdefghijklmnopqrstuvwxyz') for _ in range(n)]\n    if kuat or r.random() < 0.5:\n        t[r.randint(0, n - 1)] = r.choice('0123456789')\n    return ''.join(t)\n"
     "kini = sandi(True)\nprint(kini)\nn = r.randint(1, 6)\nprint(n)\nfor _ in range(n):\n    q = r.random()\n    if q < 0.25:\n        print(sandi(True), sandi(True))\n    elif q < 0.5:\n        print(kini, r.choice([sandi(False), 'abcdefghij']))\n    else:\n        baru = sandi(True)\n        print(kini, baru)\n        kini = baru\nprint(kini if i % 2 == 0 else sandi(True))\n",
     program(r'''
class Akun {
private:
    string sandi;

    static bool kuat(const string &s) {
        auto adaAngka = false;
        for (auto c : s) {
            if (c >= '0' && c <= '9') {
                adaAngka = true;
            }
        }
        return s.size() >= 8 && adaAngka;
    }

public:
    Akun(const string &awal) : sandi(awal) {
    }

    int ganti(const string &lama, const string &baru) {
        if (lama != sandi) {
            return 1;
        }
        if (!kuat(baru)) {
            return 2;
        }
        sandi = baru;
        return 0;
    }

    bool masuk(const string &tebakan) const {
        return tebakan == sandi;
    }
};''', r'''    string awal;
    int n;
    cin >> awal >> n;
    Akun akun(awal);
    for (int k = 0; k < n; k++) {
        string lama, baru;
        cin >> lama >> baru;
        auto hasil = akun.ganti(lama, baru);
        if (hasil == 0) {
            cout << "Berhasil" << endl;
        } else if (hasil == 1) {
            cout << "Sandi lama salah" << endl;
        } else {
            cout << "Sandi baru lemah" << endl;
        }
    }
    string tebakan;
    cin >> tebakan;
    if (akun.masuk(tebakan)) {
        cout << "Masuk berhasil" << endl;
    } else {
        cout << "Masuk gagal" << endl;
    }''', "string"), wajib=W,
     id=("Ganti Kata Sandi", "Buat class `Akun` dengan atribut `private` `string sandi`, konstruktor `Akun(const string &awal)`, dan method publik:\n\n"
         "- `int ganti(const string &lama, const string &baru)` — mengganti kata sandi. Diperiksa berurutan: bila `lama` tidak sama dengan sandi saat ini, kembalikan 1; bila `baru` lemah (kurang dari 8 karakter, **atau** tidak memuat angka), kembalikan 2; selain itu ganti sandinya dan kembalikan 0;\n"
         "- `bool masuk(const string &tebakan) const`.\n\nKata sandi tidak punya getter.\n\n"
         "Masukan: sandi awal, banyak percobaan ganti n, lalu n baris berisi `lama baru`, dan terakhir satu tebakan untuk masuk. Cetak `Berhasil`, `Sandi lama salah`, atau `Sandi baru lemah` untuk tiap percobaan, lalu `Masuk berhasil` atau `Masuk gagal`.\n\n"
         "Contoh: sandi awal `rahasia123`; percobaan `salah999x kuat12345` dan `rahasia123 pendek1` dan `rahasia123 sandibaru7`; tebakan `sandibaru7` →\n\n```\nSandi lama salah\nSandi baru lemah\nBerhasil\nMasuk berhasil\n```" + W_ID,
         "Baris pertama: sandi awal. Baris kedua: n. Lalu n baris berisi dua kata. Baris terakhir: satu tebakan.", "n baris hasil, lalu satu baris hasil masuk.", "1 ≤ n ≤ 6; kata sandi terdiri dari huruf kecil dan angka, 3 sampai 12 karakter."),
     en=("Changing a Password", "Make a class `Akun` (account) with a `private` attribute `string sandi` (password), a constructor `Akun(const string &awal)`, and the public methods:\n\n"
         "- `int ganti(const string &lama, const string &baru)` (change) — changes the password. Checked in order: if `lama` (old) differs from the current password, return 1; if `baru` (new) is weak (fewer than 8 characters, **or** no digit in it), return 2; otherwise change the password and return 0;\n"
         "- `bool masuk(const string &tebakan) const` (log in).\n\nThe password has no getter.\n\n"
         "Input: the starting password, the number of change attempts n, then n lines `old new`, and finally one guess for logging in. Print `Berhasil` (done), `Sandi lama salah` (old password wrong) or `Sandi baru lemah` (new password weak) for each attempt, then `Masuk berhasil` or `Masuk gagal`.\n\n"
         "Example: starting password `rahasia123`; attempts `salah999x kuat12345`, `rahasia123 pendek1` and `rahasia123 sandibaru7`; guess `sandibaru7` →\n\n```\nSandi lama salah\nSandi baru lemah\nBerhasil\nMasuk berhasil\n```" + W_EN,
         "First line: the starting password. Second line: n. Then n lines of two words. Last line: one guess.", "n result lines, then one login line.", "1 ≤ n ≤ 6; passwords have lowercase letters and digits, 3 to 12 characters."),
     ar=("تغيير كلمة المرور", "أنشئ صنفًا `Akun` (حساب) فيه خاصية `private` هي `string sandi` (كلمة المرور)، وبانٍ `Akun(const string &awal)`، وطريقتاه العامتان:\n\n"
         "- `int ganti(const string &lama, const string &baru)` (غيِّر) — تغيّر كلمة المرور. يُفحص بالترتيب: إذا اختلفت `lama` (القديمة) عن كلمة المرور الحالية فأرجع 1؛ وإذا كانت `baru` (الجديدة) ضعيفة (أقل من 8 محارف، **أو** بلا رقم) فأرجع 2؛ وإلا فغيِّرها وأرجع 0؛\n"
         "- `bool masuk(const string &tebakan) const` (ادخل).\n\nلا قارئ لكلمة المرور.\n\n"
         "المدخل: كلمة المرور الابتدائية ثم عدد محاولات التغيير n ثم n سطرًا فيها `القديمة الجديدة` وأخيرًا تخمين واحد للدخول. اطبع `Berhasil` (تمّ) أو `Sandi lama salah` (القديمة خطأ) أو `Sandi baru lemah` (الجديدة ضعيفة) لكل محاولة ثم `Masuk berhasil` أو `Masuk gagal`.\n\n"
         "مثال: الكلمة الابتدائية `rahasia123`؛ المحاولات `salah999x kuat12345` و `rahasia123 pendek1` و `rahasia123 sandibaru7`؛ والتخمين `sandibaru7` ←\n\n```\nSandi lama salah\nSandi baru lemah\nBerhasil\nMasuk berhasil\n```" + W_AR,
         "السطر الأول: كلمة المرور الابتدائية. السطر الثاني: n. ثم n سطرًا فيها كلمتان. السطر الأخير: تخمين واحد.", "n سطر نتيجة ثم سطر نتيجة الدخول.", "1 ≤ n ≤ 6؛ كلمات المرور حروف لاتينية صغيرة وأرقام، من 3 إلى 12 محرفًا."))

# ============================== MUDAH-SEDANG (6) ==============================

soal("jumlah-pecahan", "mudah_sedang", "trimmed",
     "n = {2: 1}.get(i, r.randint(2, 8))\nprint(n)\nfor _ in range(n):\n    b = r.choice([1, 2, 3, 4, 5, 6, 8, 10, 12, -2, -3, -4, -6, 0])\n    print(r.randint(-20, 20), b)\n",
     program(r'''
class Pecahan {
private:
    int pembilang;
    int penyebut;

    void rapikan() {
        if (penyebut < 0) {
            pembilang = -pembilang;
            penyebut = -penyebut;
        }
        int a = pembilang;
        if (a < 0) {
            a = -a;
        }
        int b = penyebut;
        while (b != 0) {
            int sisa = a % b;
            a = b;
            b = sisa;
        }
        pembilang /= a;
        penyebut /= a;
    }

public:
    Pecahan(int a, int b) : pembilang(a), penyebut(b) {
        rapikan();
    }

    static bool sah(int penyebut) {
        return penyebut != 0;
    }

    Pecahan tambah(const Pecahan &lain) const {
        return Pecahan(pembilang * lain.penyebut + lain.pembilang * penyebut, penyebut * lain.penyebut);
    }

    void cetak() const {
        cout << pembilang << "/" << penyebut << endl;
    }
};''', r'''    int n;
    cin >> n;
    Pecahan jumlah(0, 1);
    for (int k = 0; k < n; k++) {
        int a, b;
        cin >> a >> b;
        if (!Pecahan::sah(b)) {
            cout << "Dilewati" << endl;
        } else {
            jumlah = jumlah.tambah(Pecahan(a, b));
        }
    }
    jumlah.cetak();'''), wajib=W,
     id=("Pecahan yang Selalu Rapi", "Sebuah objek `Pecahan` yang baik **selalu** dalam bentuk rapi: penyebutnya positif, dan pembilang serta penyebutnya tidak punya faktor persekutuan selain 1. Nol ditulis `0/1`.\n\n"
         "Bila atributnya `private` dan satu-satunya jalan masuk adalah konstruktor yang merapikan, tidak mungkin ada objek yang tidak rapi.\n\n"
         "Buat class `Pecahan` dengan:\n\n- atribut `private` `int pembilang`, `int penyebut`;\n- method `private` `void rapikan()`;\n- konstruktor `Pecahan(int a, int b)` yang memanggil `rapikan()`;\n- `static bool sah(int penyebut)` — salah bila penyebutnya 0;\n"
         "- `Pecahan tambah(const Pecahan &lain) const` — mengembalikan pecahan baru;\n- `void cetak() const`.\n\n"
         "Baca n pasang bilangan (pembilang dan penyebut; penyebut boleh negatif). Pasangan berpenyebut 0 tidak bisa menjadi pecahan: cetak `Dilewati` dan abaikan. Jumlahkan sisanya dan cetak hasilnya.\n\n"
         "Contoh: `1 2`, `1 -3`, `5 0`, `1 6` →\n\n```\nDilewati\n1/3\n```" + W_ID,
         "Baris pertama: n. Lalu n baris berisi pembilang dan penyebut.", "`Dilewati` untuk tiap pasangan berpenyebut 0, lalu jumlahnya dalam bentuk rapi.", "1 ≤ n ≤ 8; pembilang antara −20 dan 20; penyebut antara −6 dan 12"),
     en=("A Fraction that is Always Tidy", "A good `Pecahan` object is **always** in tidy form: its denominator is positive, and numerator and denominator have no common factor other than 1. Zero is written `0/1`.\n\n"
         "When the attributes are `private` and the only way in is a constructor that tidies, an untidy object cannot exist.\n\n"
         "Make a class `Pecahan` (fraction) with:\n\n- the `private` attributes `int pembilang`, `int penyebut`;\n- a `private` method `void rapikan()` (tidy);\n- a constructor `Pecahan(int a, int b)` that calls `rapikan()`;\n- `static bool sah(int penyebut)` (valid) — false when the denominator is 0;\n"
         "- `Pecahan tambah(const Pecahan &lain) const` (add) — returns a new fraction;\n- `void cetak() const`.\n\n"
         "Read n pairs of numbers (numerator and denominator; the denominator may be negative). A pair with denominator 0 cannot become a fraction: print `Dilewati` (skipped) and ignore it. Add up the rest and print the result.\n\n"
         "Example: `1 2`, `1 -3`, `5 0`, `1 6` →\n\n```\nDilewati\n1/3\n```" + W_EN,
         "First line: n. Then n lines with a numerator and a denominator.", "`Dilewati` for every pair with denominator 0, then the sum in tidy form.", "1 ≤ n ≤ 8; numerators are between −20 and 20; denominators between −6 and 12"),
     ar=("كسر مرتّب دائمًا", "كائن `Pecahan` الجيد يكون **دائمًا** في صورة مرتّبة: مقامه موجب، ولا عامل مشترك بين بسطه ومقامه غير 1. ويُكتب الصفر `0/1`.\n\n"
         "إذا كانت الخصائص `private` وكان المدخل الوحيد بانيًا يرتّب، استحال وجود كائن غير مرتّب.\n\n"
         "أنشئ صنفًا `Pecahan` (كسر) فيه:\n\n- الخاصيتان الخاصتان `int pembilang` و `int penyebut`؛\n- طريقة `private` هي `void rapikan()` (رتِّب)؛\n- بانٍ `Pecahan(int a, int b)` يستدعي `rapikan()`؛\n- `static bool sah(int penyebut)` (صحيح) — خطأ إذا كان المقام 0؛\n"
         "- `Pecahan tambah(const Pecahan &lain) const` (اجمع) — تُرجع كسرًا جديدًا؛\n- `void cetak() const`.\n\n"
         "اقرأ n زوجًا من الأعداد (بسط ومقام؛ وقد يكون المقام سالبًا). الزوج الذي مقامه 0 لا يصير كسرًا: اطبع `Dilewati` (تُجوِّز) وأهمله. اجمع الباقي واطبع الناتج.\n\n"
         "مثال: `1 2` و `1 -3` و `5 0` و `1 6` ←\n\n```\nDilewati\n1/3\n```" + W_AR,
         "السطر الأول: n. ثم n سطرًا فيها بسط ومقام.", "`Dilewati` لكل زوج مقامه 0 ثم المجموع في صورة مرتّبة.", "1 ≤ n ≤ 8؛ البسط بين −20 و 20؛ المقام بين −6 و 12"))

soal("stok-gudang", "mudah_sedang", "trimmed",
     BARANG + "kenal = []\nfor _ in range(r.randint(1, 18)):\n    q = r.random()\n    if q < 0.5 or not kenal:\n        n = r.choice(BARANG[:6])\n        k = r.randint(1, 30) if r.random() < 0.85 else r.randint(-5, 0)\n        print('masuk', n, k)\n        if k > 0 and n not in kenal:\n            kenal.append(n)\n    else:\n        n = r.choice(kenal) if r.random() < 0.8 else r.choice(BARANG[6:])\n        print('keluar', n, r.randint(1, 30) if r.random() < 0.9 else 0)\nprint('selesai')\n",
     program(r'''
class Gudang {
private:
    string nama[10];
    int stok[10];
    int banyak = 0;

    int cari(const string &n) const {
        for (int k = 0; k < banyak; k++) {
            if (nama[k] == n) {
                return k;
            }
        }
        return -1;
    }

public:
    bool masuk(const string &n, int jumlah) {
        if (jumlah <= 0) {
            return false;
        }
        auto k = cari(n);
        if (k < 0) {
            k = banyak;
            nama[k] = n;
            stok[k] = 0;
            banyak++;
        }
        stok[k] += jumlah;
        return true;
    }

    bool keluar(const string &n, int jumlah) {
        auto k = cari(n);
        if (k < 0 || jumlah <= 0 || jumlah > stok[k]) {
            return false;
        }
        stok[k] -= jumlah;
        return true;
    }

    void laporan() const {
        for (int k = 0; k < banyak; k++) {
            cout << nama[k] << ": " << stok[k] << endl;
        }
    }
};''', r'''    Gudang gudang;
    string perintah;
    while (cin >> perintah) {
        if (perintah == "selesai") {
            break;
        }
        string nama;
        int jumlah;
        cin >> nama >> jumlah;
        auto berhasil = false;
        if (perintah == "masuk") {
            berhasil = gudang.masuk(nama, jumlah);
        } else {
            berhasil = gudang.keluar(nama, jumlah);
        }
        if (!berhasil) {
            cout << "Ditolak" << endl;
        }
    }
    gudang.laporan();''', "string"), wajib=W,
     id=("Stok Gudang", "Buat class `Gudang` yang mencatat stok beberapa barang. Seluruh catatannya `private`: `string nama[10]`, `int stok[10]`, `int banyak`, dan method bantu `int cari(const string &n) const`. Method publiknya:\n\n"
         "- `bool masuk(const string &n, int jumlah)` — menambah stok; barang yang belum dikenal didaftarkan. Ditolak bila jumlahnya nol atau negatif;\n"
         "- `bool keluar(const string &n, int jumlah)` — mengurangi stok. Ditolak bila barangnya tidak dikenal, jumlahnya nol atau negatif, atau melebihi stok;\n"
         "- `void laporan() const` — mencetak `<nama>: <stok>` untuk tiap barang, menurut urutan pertama kali didaftarkan.\n\n"
         "Perintah: `masuk <nama> <jumlah>`, `keluar <nama> <jumlah>`, `selesai`. Cetak `Ditolak` untuk tiap perintah yang gagal, lalu laporannya. Stok tidak pernah bisa negatif — bukan karena pemakainya berhati-hati, tetapi karena class tidak memberi jalan.\n\n"
         "Contoh: `masuk kopi 10`, `keluar teh 2`, `keluar kopi 15`, `masuk gula 5`, `keluar kopi 4`, `selesai` →\n\n```\nDitolak\nDitolak\nkopi: 6\ngula: 5\n```" + W_ID,
         "Beberapa baris perintah, diakhiri `selesai`.", "`Ditolak` untuk tiap perintah yang gagal, lalu satu baris per barang.", "Paling banyak 18 perintah; paling banyak 6 jenis barang; −5 ≤ jumlah ≤ 30"),
     en=("Warehouse Stock", "Make a class `Gudang` (warehouse) that records the stock of several items. All its records are `private`: `string nama[10]`, `int stok[10]`, `int banyak`, and a helper method `int cari(const string &n) const` (find). Its public methods:\n\n"
         "- `bool masuk(const string &n, int jumlah)` (in) — adds stock; an item not known yet is registered. Refused when the amount is zero or negative;\n"
         "- `bool keluar(const string &n, int jumlah)` (out) — removes stock. Refused when the item is unknown, the amount is zero or negative, or it exceeds the stock;\n"
         "- `void laporan() const` (report) — prints `<name>: <stock>` for every item, in the order of first registration.\n\n"
         "Commands: `masuk <name> <amount>`, `keluar <name> <amount>`, `selesai`. Print `Ditolak` for every command that fails, then the report. The stock can never be negative — not because users are careful, but because the class offers no way.\n\n"
         "Example: `masuk kopi 10`, `keluar teh 2`, `keluar kopi 15`, `masuk gula 5`, `keluar kopi 4`, `selesai` →\n\n```\nDitolak\nDitolak\nkopi: 6\ngula: 5\n```" + W_EN,
         "Several command lines, ending with `selesai`.", "`Ditolak` for every failed command, then one line per item.", "At most 18 commands; at most 6 kinds of items; −5 ≤ amount ≤ 30"),
     ar=("مخزون المستودع", "أنشئ صنفًا `Gudang` (مستودع) يسجّل مخزون عدة سلع. كل سجلاته `private`: `string nama[10]` و `int stok[10]` و `int banyak` وطريقة مساعدة `int cari(const string &n) const` (ابحث). وطرائقه العامة:\n\n"
         "- `bool masuk(const string &n, int jumlah)` (وارد) — تزيد المخزون؛ والسلعة غير المعروفة تُسجَّل. تُرفض إذا كانت الكمية صفرًا أو سالبة؛\n"
         "- `bool keluar(const string &n, int jumlah)` (صادر) — تنقص المخزون. تُرفض إذا كانت السلعة غير معروفة أو الكمية صفرًا أو سالبة أو تزيد على المخزون؛\n"
         "- `void laporan() const` (تقرير) — تطبع `<الاسم>: <المخزون>` لكل سلعة بترتيب أول تسجيل.\n\n"
         "الأوامر: `masuk <الاسم> <الكمية>` و `keluar <الاسم> <الكمية>` و `selesai`. اطبع `Ditolak` لكل أمر يفشل ثم التقرير. لا يصير المخزون سالبًا أبدًا — لا لأن المستعملين حذرون بل لأن الصنف لا يتيح طريقًا لذلك.\n\n"
         "مثال: `masuk kopi 10` ثم `keluar teh 2` ثم `keluar kopi 15` ثم `masuk gula 5` ثم `keluar kopi 4` ثم `selesai` ←\n\n```\nDitolak\nDitolak\nkopi: 6\ngula: 5\n```" + W_AR,
         "عدة أسطر أوامر تنتهي بـ `selesai`.", "`Ditolak` لكل أمر فاشل ثم سطر لكل سلعة.", "18 أمرًا على الأكثر؛ 6 أنواع سلع على الأكثر؛ −5 ≤ الكمية ≤ 30"))

soal("termostat", "mudah_sedang", "trimmed",
     "for _ in range(r.randint(1, 20)):\n    q = r.random()\n    if q < 0.35:\n        print('naik')\n    elif q < 0.7:\n        print('turun')\n    else:\n        print('ruang', r.randint(14, 32))\nprint('ruang', r.randint(14, 32))\nprint('selesai')\n",
     program(r'''
class Termostat {
private:
    int target = 24;

public:
    void naik() {
        if (target < 30) {
            target++;
        }
    }

    void turun() {
        if (target > 16) {
            target--;
        }
    }

    int lihat() const {
        return target;
    }

    string status(int suhuRuang) const {
        if (suhuRuang > target) {
            return "mendinginkan";
        }
        if (suhuRuang < target) {
            return "memanaskan";
        }
        return "diam";
    }
};''', r'''    Termostat t;
    string perintah;
    while (cin >> perintah) {
        if (perintah == "selesai") {
            break;
        }
        if (perintah == "naik") {
            t.naik();
        } else if (perintah == "turun") {
            t.turun();
        } else {
            int suhu;
            cin >> suhu;
            cout << t.lihat() << ": " << t.status(suhu) << endl;
        }
    }''', "string"), wajib=W,
     id=("Termostat", "Sebuah termostat menyimpan **suhu target**, mula-mula 24 derajat, yang hanya boleh berada di antara 16 dan 30.\n\n"
         "Buat class `Termostat` dengan atribut `private` `int target` dan method publik:\n\n- `void naik()` dan `void turun()` — mengubah target satu derajat, tetapi tidak pernah keluar dari 16–30;\n- `int lihat() const`;\n"
         "- `string status(int suhuRuang) const` — `mendinginkan` bila ruangan lebih panas daripada target, `memanaskan` bila lebih dingin, `diam` bila sama.\n\n"
         "Perintah: `naik`, `turun`, `ruang <suhu>`, `selesai`. Untuk tiap `ruang` cetak `<target>: <status>`.\n\n"
         "Contoh: `naik`, `ruang 27`, `turun`, `turun`, `ruang 23`, `ruang 20`, `selesai` →\n\n```\n25: mendinginkan\n23: diam\n23: memanaskan\n```" + W_ID,
         "Beberapa baris perintah, diakhiri `selesai`.", "Satu baris untuk tiap perintah `ruang`.", "Paling banyak 21 perintah; suhu ruang antara 14 dan 32."),
     en=("Thermostat", "A thermostat stores a **target temperature**, 24 degrees at first, which may only lie between 16 and 30.\n\n"
         "Make a class `Termostat` with a `private` attribute `int target` and the public methods:\n\n- `void naik()` and `void turun()` — change the target by one degree, but never outside 16–30;\n- `int lihat() const`;\n"
         "- `string status(int suhuRuang) const` — `mendinginkan` (cooling) when the room is warmer than the target, `memanaskan` (heating) when it is colder, `diam` (idle) when equal.\n\n"
         "Commands: `naik`, `turun`, `ruang <temperature>` (room), `selesai`. For every `ruang` print `<target>: <status>`.\n\n"
         "Example: `naik`, `ruang 27`, `turun`, `turun`, `ruang 23`, `ruang 20`, `selesai` →\n\n```\n25: mendinginkan\n23: diam\n23: memanaskan\n```" + W_EN,
         "Several command lines, ending with `selesai`.", "One line for every `ruang` command.", "At most 21 commands; room temperatures are between 14 and 32."),
     ar=("منظِّم الحرارة", "منظِّم حرارة يخزّن **درجة مستهدفة** تبدأ من 24 ولا يجوز أن تخرج عن 16 إلى 30.\n\n"
         "أنشئ صنفًا `Termostat` فيه خاصية `private` هي `int target` وطرائقه العامة:\n\n- `void naik()` و `void turun()` — تغيّران الهدف درجة واحدة دون الخروج عن 16–30 أبدًا؛\n- `int lihat() const`؛\n"
         "- `string status(int suhuRuang) const` — `mendinginkan` (تبريد) إذا كانت الغرفة أدفأ من الهدف، و `memanaskan` (تدفئة) إذا كانت أبرد، و `diam` (ساكن) إذا تساويا.\n\n"
         "الأوامر: `naik` و `turun` و `ruang <الحرارة>` (الغرفة) و `selesai`. لكل `ruang` اطبع `<الهدف>: <الحالة>`.\n\n"
         "مثال: `naik` ثم `ruang 27` ثم `turun` ثم `turun` ثم `ruang 23` ثم `ruang 20` ثم `selesai` ←\n\n```\n25: mendinginkan\n23: diam\n23: memanaskan\n```" + W_AR,
         "عدة أسطر أوامر تنتهي بـ `selesai`.", "سطر لكل أمر `ruang`.", "21 أمرًا على الأكثر؛ حرارة الغرفة بين 14 و 32."))

soal("antrean-terbatas", "mudah_sedang", "trimmed",
     "p = {2: 0.9, 3: 0.3}.get(i, 0.6)\nfor _ in range(r.randint(1, 25)):\n    if r.random() < p:\n        print('masuk', r.randint(1, 99))\n    else:\n        print('keluar')\nprint('selesai')\n",
     program(r'''
class AntreanTerbatas {
private:
    int data[5];
    int depan = 0;
    int banyak = 0;

public:
    bool penuh() const {
        return banyak == 5;
    }

    bool kosong() const {
        return banyak == 0;
    }

    int ukuran() const {
        return banyak;
    }

    bool masuk(int x) {
        if (penuh()) {
            return false;
        }
        data[(depan + banyak) % 5] = x;
        banyak++;
        return true;
    }

    bool keluar(int &x) {
        if (kosong()) {
            return false;
        }
        x = data[depan];
        depan = (depan + 1) % 5;
        banyak--;
        return true;
    }
};''', r'''    AntreanTerbatas antre;
    string perintah;
    while (cin >> perintah) {
        if (perintah == "selesai") {
            break;
        }
        if (perintah == "masuk") {
            int x;
            cin >> x;
            if (!antre.masuk(x)) {
                cout << "Penuh" << endl;
            }
        } else {
            int x;
            if (antre.keluar(x)) {
                cout << x << endl;
            } else {
                cout << "Kosong" << endl;
            }
        }
    }
    cout << "Sisa: " << antre.ukuran() << endl;''', "string"), wajib=W,
     id=("Antrean Terbatas", "Buat class `AntreanTerbatas`: antrean bilangan yang muat paling banyak **5** elemen. Cara menyimpannya disembunyikan: atribut `private` `int data[5]`, `int depan`, `int banyak` (array melingkar — elemen baru ditaruh di `(depan + banyak) % 5`). Method publiknya:\n\n"
         "- `bool masuk(int x)` — menaruh x di belakang; gagal bila antrean penuh;\n- `bool keluar(int &x)` — mengambil elemen terdepan ke x; gagal bila antrean kosong;\n- `bool penuh() const`, `bool kosong() const`, `int ukuran() const`.\n\n"
         "Perintah: `masuk x`, `keluar`, `selesai`. Untuk `masuk` yang gagal cetak `Penuh`. Untuk `keluar` cetak elemennya, atau `Kosong`. Di akhir cetak `Sisa: k`.\n\n"
         "Pemakai class tidak perlu tahu bahwa di dalamnya ada array melingkar; yang ia lihat hanya antrean.\n\n"
         "Contoh: `keluar`, `masuk 7`, `masuk 9`, `keluar`, `selesai` →\n\n```\nKosong\n7\nSisa: 1\n```" + W_ID,
         "Beberapa baris perintah, diakhiri `selesai`.", "Satu baris untuk tiap `keluar` dan tiap `masuk` yang gagal, lalu `Sisa: k`.", "Paling banyak 25 perintah; 1 ≤ x ≤ 99"),
     en=("A Bounded Queue", "Make a class `AntreanTerbatas` (bounded queue): a queue of numbers that holds at most **5** elements. How it is stored is hidden: the `private` attributes `int data[5]`, `int depan` (front), `int banyak` (count) — a circular array, a new element goes to `(depan + banyak) % 5`. Its public methods:\n\n"
         "- `bool masuk(int x)` (enqueue) — puts x at the back; fails when the queue is full;\n- `bool keluar(int &x)` (dequeue) — takes the front element into x; fails when the queue is empty;\n- `bool penuh() const` (full), `bool kosong() const` (empty), `int ukuran() const` (size).\n\n"
         "Commands: `masuk x`, `keluar`, `selesai`. For a `masuk` that fails print `Penuh`. For `keluar` print the element, or `Kosong`. At the end print `Sisa: k`.\n\n"
         "The user of the class need not know there is a circular array inside; all they see is a queue.\n\n"
         "Example: `keluar`, `masuk 7`, `masuk 9`, `keluar`, `selesai` →\n\n```\nKosong\n7\nSisa: 1\n```" + W_EN,
         "Several command lines, ending with `selesai`.", "One line for every `keluar` and every failed `masuk`, then `Sisa: k`.", "At most 25 commands; 1 ≤ x ≤ 99"),
     ar=("طابور محدود", "أنشئ صنفًا `AntreanTerbatas` (طابور محدود): طابور أعداد يسع **5** عناصر على الأكثر. طريقة التخزين مخفية: الخصائص الخاصة `int data[5]` و `int depan` (المقدمة) و `int banyak` (العدد) — مصفوفة دائرية، والعنصر الجديد يوضع في `(depan + banyak) % 5`. وطرائقه العامة:\n\n"
         "- `bool masuk(int x)` (أدخل) — تضع x في الخلف؛ وتفشل إذا امتلأ الطابور؛\n- `bool keluar(int &x)` (أخرج) — تأخذ عنصر المقدمة إلى x؛ وتفشل إذا كان الطابور فارغًا؛\n- `bool penuh() const` (ممتلئ) و `bool kosong() const` (فارغ) و `int ukuran() const` (الحجم).\n\n"
         "الأوامر: `masuk x` و `keluar` و `selesai`. للأمر `masuk` الفاشل اطبع `Penuh`. وللأمر `keluar` اطبع العنصر أو `Kosong`. في النهاية اطبع `Sisa: k`.\n\n"
         "مستعمل الصنف لا يلزمه أن يعرف أن في داخله مصفوفة دائرية؛ فهو لا يرى إلا طابورًا.\n\n"
         "مثال: `keluar` ثم `masuk 7` ثم `masuk 9` ثم `keluar` ثم `selesai` ←\n\n```\nKosong\n7\nSisa: 1\n```" + W_AR,
         "عدة أسطر أوامر تنتهي بـ `selesai`.", "سطر لكل `keluar` ولكل `masuk` فاشل ثم `Sisa: k`.", "25 أمرًا على الأكثر؛ 1 ≤ x ≤ 99"))

soal("statistik-terlindung", "mudah_sedang", "trimmed",
     "while True:\n    n = {2: 0, 3: 1}.get(i, r.randint(2, 20))\n    v = [r.randint(-100, 100) for _ in range(n)]\n    if n == 0 or abs(sum(v) * 100 / n % 1 - 0.5) > 1e-3:\n        break\nprint(n)\nprint(*v)\n",
     program(r'''
class Statistik {
private:
    int banyak = 0;
    int jumlah = 0;
    int kecil = 0;
    int besar = 0;

public:
    void tambah(int x) {
        if (banyak == 0 || x < kecil) {
            kecil = x;
        }
        if (banyak == 0 || x > besar) {
            besar = x;
        }
        jumlah += x;
        banyak++;
    }

    bool kosong() const {
        return banyak == 0;
    }

    int minimum() const {
        return kecil;
    }

    int maksimum() const {
        return besar;
    }

    double rata() const {
        return (double) jumlah / banyak;
    }
};''', r'''    int n;
    cin >> n;
    Statistik s;
    for (int k = 0; k < n; k++) {
        int x;
        cin >> x;
        s.tambah(x);
    }
    if (s.kosong()) {
        cout << "Belum ada data" << endl;
    } else {
        cout << "Min: " << s.minimum() << endl;
        cout << "Maks: " << s.maksimum() << endl;
        cout << "Rata-rata: " << fixed << setprecision(2) << s.rata() << endl;
    }''', "iomanip"), wajib=W,
     id=("Statistik Terlindung", "Buat class `Statistik` yang menerima bilangan satu per satu dan selalu siap menjawab pertanyaan tentang semua bilangan yang sudah diterimanya — tanpa menyimpan bilangan-bilangan itu.\n\n"
         "Atributnya `private`: `int banyak`, `int jumlah`, `int kecil`, `int besar`. Karena tidak ada setter, keempatnya **selalu cocok satu sama lain**. Method publik: `void tambah(int x)`, `bool kosong() const`, `int minimum() const`, `int maksimum() const`, `double rata() const`.\n\n"
         "Baca n bilangan dan masukkan semuanya. Bila tidak ada data (n = 0), cetak `Belum ada data`. Bila ada, cetak:\n\n```\nMin: …\nMaks: …\nRata-rata: …\n```\n\ndengan rata-rata dua angka di belakang koma.\n\n"
         "Contoh: `4 -2 7` → `Min: -2`, `Maks: 7`, `Rata-rata: 3.00`." + W_ID,
         "Baris pertama: n. Baris kedua: n bilangan bulat (kosong bila n = 0).", "`Belum ada data`, atau tiga baris statistik.", "0 ≤ n ≤ 20; tiap bilangan antara −100 dan 100"),
     en=("Protected Statistics", "Make a class `Statistik` that takes numbers one at a time and is always ready to answer questions about all the numbers received so far — without storing them.\n\n"
         "Its attributes are `private`: `int banyak` (count), `int jumlah` (sum), `int kecil` (smallest), `int besar` (largest). As there are no setters, the four **always agree with one another**. Public methods: `void tambah(int x)` (add), `bool kosong() const`, `int minimum() const`, `int maksimum() const`, `double rata() const` (average).\n\n"
         "Read n numbers and add them all. If there is no data (n = 0), print `Belum ada data` (no data yet). Otherwise print:\n\n```\nMin: …\nMaks: …\nRata-rata: …\n```\n\nwith the average to two decimal places.\n\n"
         "Example: `4 -2 7` → `Min: -2`, `Maks: 7`, `Rata-rata: 3.00`." + W_EN,
         "First line: n. Second line: n integers (empty when n = 0).", "`Belum ada data`, or three lines of statistics.", "0 ≤ n ≤ 20; each number is between −100 and 100"),
     ar=("إحصاء محمي", "أنشئ صنفًا `Statistik` يتلقى الأعداد واحدًا واحدًا وهو جاهز دائمًا للإجابة عن كل الأعداد التي تلقاها — دون أن يخزّنها.\n\n"
         "خصائصه `private`: `int banyak` (العدد) و `int jumlah` (المجموع) و `int kecil` (الأصغر) و `int besar` (الأكبر). ولأنه بلا ضابطات فالأربع **متوافقة دائمًا**. الطرائق العامة: `void tambah(int x)` (أضف) و `bool kosong() const` و `int minimum() const` و `int maksimum() const` و `double rata() const` (المتوسط).\n\n"
         "اقرأ n عددًا وأضفها كلها. إذا لم توجد بيانات (n = 0) فاطبع `Belum ada data` (لا بيانات بعد). وإلا فاطبع:\n\n```\nMin: …\nMaks: …\nRata-rata: …\n```\n\nوالمتوسط برقمين بعد الفاصلة.\n\n"
         "مثال: `4 -2 7` ← `Min: -2` و `Maks: 7` و `Rata-rata: 3.00`." + W_AR,
         "السطر الأول: n. السطر الثاني: n عددًا صحيحًا (فارغ إذا كان n = 0).", "`Belum ada data` أو ثلاثة أسطر إحصاء.", "0 ≤ n ≤ 20؛ كل عدد بين −100 و 100"))

soal("gembok-kombinasi", "mudah_sedang", "trimmed",
     "kode = [r.randint(0, 9) for _ in range(3)]\nn = r.randint(1, 20)\nv = [r.randint(0, 9) for _ in range(n)]\nif i % 2 == 0:\n    p = r.randint(0, n)\n    v = v[:p] + kode + v[p:]\nif i == 4:\n    kode = [3, 3, 7]\n    v = [3, 3, 3, 7]\nif i == 6:\n    kode = [1, 2, 1]\n    v = [1, 2, 1, 2, 1]\nprint(*kode)\nprint(len(v))\nprint(*v)\n",
     program(r'''
class Gembok {
private:
    int kode[3];
    int cocok = 0;
    bool terbuka = false;

public:
    Gembok(int a, int b, int c) {
        kode[0] = a;
        kode[1] = b;
        kode[2] = c;
    }

    void putar(int angka) {
        if (terbuka) {
            return;
        }
        if (angka == kode[cocok]) {
            cocok++;
        } else if (angka == kode[0]) {
            cocok = 1;
        } else {
            cocok = 0;
        }
        if (cocok == 3) {
            terbuka = true;
        }
    }

    bool sudahTerbuka() const {
        return terbuka;
    }
};''', r'''    int a, b, c, n;
    cin >> a >> b >> c;
    cin >> n;
    Gembok gembok(a, b, c);
    auto pada = 0;
    for (int k = 1; k <= n; k++) {
        int angka;
        cin >> angka;
        gembok.putar(angka);
        if (pada == 0 && gembok.sudahTerbuka()) {
            pada = k;
        }
    }
    if (pada > 0) {
        cout << "Terbuka pada putaran " << pada << endl;
    } else {
        cout << "Tetap terkunci" << endl;
    }'''), wajib=W,
     id=("Gembok Kombinasi", "Sebuah gembok terbuka bila tiga angka kodenya diputar **berturut-turut dalam urutan yang benar**. Gembok mengingat sudah berapa angka yang cocok; ingatan itu tersembunyi.\n\n"
         "Buat class `Gembok` dengan atribut `private` `int kode[3]`, `int cocok` (mula-mula 0), `bool terbuka`; konstruktor `Gembok(int a, int b, int c)`; dan method publik `void putar(int angka)` serta `bool sudahTerbuka() const`.\n\n"
         "Aturan `putar`, bila gembok belum terbuka:\n\n- bila angkanya sama dengan angka kode yang sedang ditunggu, `cocok` bertambah satu;\n- bila tidak, tetapi angkanya sama dengan angka **pertama** kode, `cocok` menjadi 1;\n- selain itu `cocok` menjadi 0.\n\n"
         "Begitu `cocok` mencapai 3, gembok terbuka dan putaran berikutnya diabaikan.\n\n"
         "Baca kode, lalu n angka yang diputar. Cetak `Terbuka pada putaran k` (putaran pertama yang membukanya) atau `Tetap terkunci`.\n\n"
         "Contoh: kode `3 3 7`, putaran `3 3 3 7` → `Tetap terkunci` (pada angka 3 yang ketiga, hitungan kembali ke 1).\nContoh: kode `4 1 9`, putaran `2 4 1 9 5` → `Terbuka pada putaran 4`." + W_ID,
         "Baris pertama: tiga angka kode. Baris kedua: n. Baris ketiga: n angka.", "`Terbuka pada putaran k` atau `Tetap terkunci`.", "Tiap angka antara 0 dan 9; 1 ≤ n ≤ 23"),
     en=("Combination Lock", "A lock opens when the three digits of its code are dialled **one after another in the right order**. The lock remembers how many digits have matched so far; that memory is hidden.\n\n"
         "Make a class `Gembok` (lock) with the `private` attributes `int kode[3]`, `int cocok` (matched, starting at 0), `bool terbuka` (open); a constructor `Gembok(int a, int b, int c)`; and the public methods `void putar(int angka)` (dial) and `bool sudahTerbuka() const`.\n\n"
         "The rule of `putar`, while the lock is not open yet:\n\n- if the digit equals the code digit now awaited, `cocok` grows by one;\n- if not, but the digit equals the **first** code digit, `cocok` becomes 1;\n- otherwise `cocok` becomes 0.\n\n"
         "As soon as `cocok` reaches 3 the lock opens and later dials are ignored.\n\n"
         "Read the code, then n digits dialled. Print `Terbuka pada putaran k` (opened at dial k, the first dial that opens it) or `Tetap terkunci` (still locked).\n\n"
         "Example: code `3 3 7`, dials `3 3 3 7` → `Tetap terkunci` (at the third 3 the count falls back to 1).\nExample: code `4 1 9`, dials `2 4 1 9 5` → `Terbuka pada putaran 4`." + W_EN,
         "First line: the three code digits. Second line: n. Third line: n digits.", "`Terbuka pada putaran k` or `Tetap terkunci`.", "Each digit is between 0 and 9; 1 ≤ n ≤ 23"),
     ar=("قفل الأرقام", "ينفتح القفل إذا أُديرت أرقام رمزه الثلاثة **متتالية بالترتيب الصحيح**. يتذكر القفل عدد الأرقام التي طابقت حتى الآن؛ وهذه الذاكرة مخفية.\n\n"
         "أنشئ صنفًا `Gembok` (قفل) خصائصه الخاصة `int kode[3]` و `int cocok` (المطابق، ويبدأ من 0) و `bool terbuka` (مفتوح)؛ وبانٍ `Gembok(int a, int b, int c)`؛ والطريقتان العامتان `void putar(int angka)` (أدِر) و `bool sudahTerbuka() const`.\n\n"
         "قاعدة `putar` ما دام القفل لم ينفتح:\n\n- إذا ساوى الرقمُ رقمَ الرمز المنتظر الآن زاد `cocok` واحدًا؛\n- وإلا فإذا ساوى الرقمُ الرقمَ **الأول** من الرمز صار `cocok` 1؛\n- وإلا صار `cocok` 0.\n\n"
         "متى بلغ `cocok` 3 انفتح القفل وأُهملت الإدارات التالية.\n\n"
         "اقرأ الرمز ثم n رقمًا مُدارًا. اطبع `Terbuka pada putaran k` (انفتح عند الإدارة k، وهي أول إدارة تفتحه) أو `Tetap terkunci` (بقي مقفلًا).\n\n"
         "مثال: الرمز `3 3 7` والإدارات `3 3 3 7` ← `Tetap terkunci` (عند الرقم 3 الثالث يعود العدّ إلى 1).\nمثال: الرمز `4 1 9` والإدارات `2 4 1 9 5` ← `Terbuka pada putaran 4`." + W_AR,
         "السطر الأول: أرقام الرمز الثلاثة. السطر الثاني: n. السطر الثالث: n رقمًا.", "`Terbuka pada putaran k` أو `Tetap terkunci`.", "كل رقم بين 0 و 9؛ 1 ≤ n ≤ 23"))

# ================================ SEDANG (6) ================================

soal("mutasi-rekening", "sedang", "trimmed",
     "print(r.randint(0, 40) * 50000)\nfor _ in range(r.randint(1, 15)):\n    q = r.random()\n    if q < 0.4:\n        print('setor', r.choice([r.randint(1, 20) * 50000, 0, -50000]))\n    else:\n        print('tarik', r.choice([r.randint(1, 12) * 50000, r.randint(1, 12) * 50000, 0, 700000]))\nprint('selesai')\n",
     program(r'''
class Rekening {
private:
    int saldo;
    int mutasi[20];
    int banyak = 0;
    int ditarik = 0;

    void catat(int perubahan) {
        mutasi[banyak] = perubahan;
        banyak++;
    }

public:
    Rekening(int awal) : saldo(awal) {
    }

    bool setor(int jumlah) {
        if (jumlah <= 0) {
            return false;
        }
        saldo += jumlah;
        catat(jumlah);
        return true;
    }

    bool tarik(int jumlah) {
        if (jumlah <= 0 || jumlah > saldo || ditarik + jumlah > 1000000) {
            return false;
        }
        saldo -= jumlah;
        ditarik += jumlah;
        catat(-jumlah);
        return true;
    }

    void cetakMutasi() const {
        for (int k = 0; k < banyak; k++) {
            if (mutasi[k] > 0) {
                cout << "+" << mutasi[k] << endl;
            } else {
                cout << mutasi[k] << endl;
            }
        }
        cout << "Saldo: " << saldo << endl;
    }
};''', r'''    int awal;
    cin >> awal;
    Rekening rek(awal);
    string perintah;
    while (cin >> perintah) {
        if (perintah == "selesai") {
            break;
        }
        int jumlah;
        cin >> jumlah;
        auto berhasil = false;
        if (perintah == "setor") {
            berhasil = rek.setor(jumlah);
        } else {
            berhasil = rek.tarik(jumlah);
        }
        if (!berhasil) {
            cout << "Ditolak" << endl;
        }
    }
    rek.cetakMutasi();''', "string"), wajib=W,
     id=("Mutasi Rekening", "Buat class `Rekening` yang selain saldo juga menyimpan **riwayat mutasi** dan menegakkan **batas penarikan harian**. Semua datanya `private`: `int saldo`, `int mutasi[20]`, `int banyak`, `int ditarik` (total penarikan yang berhasil), dan method bantu `void catat(int perubahan)`.\n\n"
         "Method publik:\n\n- `Rekening(int awal)`;\n- `bool setor(int jumlah)` — ditolak bila jumlah nol atau negatif;\n- `bool tarik(int jumlah)` — ditolak bila jumlah nol atau negatif, melebihi saldo, atau membuat **total penarikan melebihi 1000000**;\n"
         "- `void cetakMutasi() const` — mencetak tiap transaksi yang **berhasil** menurut urutannya, setoran sebagai `+x` dan penarikan sebagai `-x`, lalu `Saldo: s`.\n\n"
         "Transaksi yang ditolak tidak mengubah apa pun dan tidak masuk riwayat. Karena riwayat hanya bisa diisi oleh `setor` dan `tarik`, riwayat dan saldo tidak mungkin tidak cocok.\n\n"
         "Masukan: saldo awal, lalu perintah `setor x` / `tarik x`, diakhiri `selesai`. Cetak `Ditolak` untuk tiap perintah yang gagal, lalu mutasinya.\n\n"
         "Contoh: saldo awal 2000000; `tarik 700000`, `tarik 400000`, `setor 100000`, `tarik 300000`, `selesai` →\n\n```\nDitolak\n-700000\n+100000\n-300000\nSaldo: 1100000\n```" + W_ID,
         "Baris pertama: saldo awal. Lalu perintah-perintah, diakhiri `selesai`.", "`Ditolak` untuk tiap perintah yang gagal, lalu riwayat mutasi dan saldo.", "0 ≤ saldo awal ≤ 2000000; paling banyak 15 perintah; −50000 ≤ x ≤ 1000000"),
     en=("Account Statement", "Make a class `Rekening` (account) that keeps, besides the balance, a **history of transactions** and enforces a **daily withdrawal limit**. All its data is `private`: `int saldo`, `int mutasi[20]`, `int banyak`, `int ditarik` (total of successful withdrawals), and a helper method `void catat(int perubahan)` (record).\n\n"
         "Public methods:\n\n- `Rekening(int awal)`;\n- `bool setor(int jumlah)` — refused when the amount is zero or negative;\n- `bool tarik(int jumlah)` — refused when the amount is zero or negative, exceeds the balance, or makes the **total withdrawn exceed 1000000**;\n"
         "- `void cetakMutasi() const` — prints every **successful** transaction in order, a deposit as `+x` and a withdrawal as `-x`, then `Saldo: s`.\n\n"
         "A refused transaction changes nothing and does not enter the history. Because the history can be filled only by `setor` and `tarik`, history and balance cannot disagree.\n\n"
         "Input: the opening balance, then commands `setor x` / `tarik x`, ending with `selesai`. Print `Ditolak` for every command that fails, then the statement.\n\n"
         "Example: opening balance 2000000; `tarik 700000`, `tarik 400000`, `setor 100000`, `tarik 300000`, `selesai` →\n\n```\nDitolak\n-700000\n+100000\n-300000\nSaldo: 1100000\n```" + W_EN,
         "First line: the opening balance. Then the commands, ending with `selesai`.", "`Ditolak` for every failed command, then the statement and the balance.", "0 ≤ opening balance ≤ 2000000; at most 15 commands; −50000 ≤ x ≤ 1000000"),
     ar=("كشف الحساب", "أنشئ صنفًا `Rekening` (حساب) يحفظ مع الرصيد **سجل الحركات** ويفرض **حدًّا يوميًا للسحب**. كل بياناته `private`: `int saldo` و `int mutasi[20]` و `int banyak` و `int ditarik` (مجموع السحوبات الناجحة) وطريقة مساعدة `void catat(int perubahan)` (سجِّل).\n\n"
         "الطرائق العامة:\n\n- `Rekening(int awal)`؛\n- `bool setor(int jumlah)` — يُرفض إذا كان المبلغ صفرًا أو سالبًا؛\n- `bool tarik(int jumlah)` — يُرفض إذا كان المبلغ صفرًا أو سالبًا، أو زاد على الرصيد، أو جعل **مجموع المسحوب يتجاوز 1000000**؛\n"
         "- `void cetakMutasi() const` — تطبع كل حركة **ناجحة** بترتيبها، الإيداع `+x` والسحب `-x`، ثم `Saldo: s`.\n\n"
         "الحركة المرفوضة لا تغيّر شيئًا ولا تدخل السجل. ولأن السجل لا يملؤه إلا `setor` و `tarik` فلا يمكن أن يتعارض السجل والرصيد.\n\n"
         "المدخل: الرصيد الابتدائي ثم أوامر `setor x` / `tarik x` تنتهي بـ `selesai`. اطبع `Ditolak` لكل أمر يفشل ثم الكشف.\n\n"
         "مثال: الرصيد الابتدائي 2000000؛ `tarik 700000` ثم `tarik 400000` ثم `setor 100000` ثم `tarik 300000` ثم `selesai` ←\n\n```\nDitolak\n-700000\n+100000\n-300000\nSaldo: 1100000\n```" + W_AR,
         "السطر الأول: الرصيد الابتدائي. ثم الأوامر منتهيةً بـ `selesai`.", "`Ditolak` لكل أمر فاشل ثم سجل الحركات والرصيد.", "0 ≤ الرصيد الابتدائي ≤ 2000000؛ 15 أمرًا على الأكثر؛ −50000 ≤ x ≤ 1000000"))

soal("papan-xo", "sedang", "trimmed",
     "n = r.randint(1, 12)\nsel = [(b, k) for b in range(1, 4) for k in range(1, 4)]\nr.shuffle(sel)\nlangkah = []\nfor j in range(n):\n    q = r.random()\n    if q < 0.75 and sel:\n        langkah.append(sel.pop())\n    elif q < 0.9 and langkah:\n        langkah.append(r.choice(langkah))\n    else:\n        langkah.append((r.choice([0, 4, 2]), r.choice([0, 4, 1])))\n"
     "if i == 2:\n    langkah = [(1, 1), (2, 1), (1, 2), (2, 2), (1, 3), (3, 3)]\nif i == 3:\n    langkah = [(1, 1), (1, 2), (1, 3), (2, 2), (2, 1), (2, 3), (3, 2), (3, 1), (3, 3)]\nprint(len(langkah))\nfor b, k in langkah:\n    print(b, k)\n",
     program(r'''
class Papan {
private:
    char sel[3][3];
    char giliran = 'X';
    int terisi = 0;

    bool sebaris(char p, int b1, int k1, int b2, int k2, int b3, int k3) const {
        return sel[b1][k1] == p && sel[b2][k2] == p && sel[b3][k3] == p;
    }

public:
    Papan() {
        for (auto &baris : sel) {
            for (auto &s : baris) {
                s = '.';
            }
        }
    }

    char pemenang() const {
        for (char p : {'X', 'O'}) {
            for (int k = 0; k < 3; k++) {
                if (sebaris(p, k, 0, k, 1, k, 2) || sebaris(p, 0, k, 1, k, 2, k)) {
                    return p;
                }
            }
            if (sebaris(p, 0, 0, 1, 1, 2, 2) || sebaris(p, 0, 2, 1, 1, 2, 0)) {
                return p;
            }
        }
        return ' ';
    }

    bool selesai() const {
        return pemenang() != ' ' || terisi == 9;
    }

    bool taruh(int b, int k) {
        if (selesai() || b < 1 || b > 3 || k < 1 || k > 3 || sel[b - 1][k - 1] != '.') {
            return false;
        }
        sel[b - 1][k - 1] = giliran;
        terisi++;
        if (giliran == 'X') {
            giliran = 'O';
        } else {
            giliran = 'X';
        }
        return true;
    }

    void cetak() const {
        for (int b = 0; b < 3; b++) {
            for (int k = 0; k < 3; k++) {
                cout << sel[b][k];
            }
            cout << endl;
        }
    }
};''', r'''    int n;
    cin >> n;
    Papan papan;
    for (int j = 0; j < n; j++) {
        int b, k;
        cin >> b >> k;
        if (!papan.taruh(b, k)) {
            cout << "Langkah ditolak" << endl;
        }
    }
    papan.cetak();
    if (papan.pemenang() == 'X') {
        cout << "X menang" << endl;
    } else if (papan.pemenang() == 'O') {
        cout << "O menang" << endl;
    } else if (papan.selesai()) {
        cout << "Seri" << endl;
    } else {
        cout << "Belum selesai" << endl;
    }'''), wajib=W,
     id=("Papan XO", "Buat class `Papan` untuk permainan XO (tic-tac-toe) 3 × 3. Papan, giliran, dan banyak petak terisi semuanya `private`; pemain tidak bisa menaruh tanda sembarangan, apalagi dua kali berturut-turut.\n\n"
         "Atribut `private`: `char sel[3][3]` (mula-mula semua `.`), `char giliran` (mula-mula `X`), `int terisi`. Method publik:\n\n"
         "- `bool taruh(int b, int k)` — pemain yang sedang mendapat giliran menaruh tandanya di baris b, kolom k (dihitung dari 1). **Ditolak** bila permainan sudah selesai, posisinya di luar papan, atau petaknya sudah terisi. Giliran berpindah hanya bila langkahnya diterima;\n"
         "- `char pemenang() const` — `X`, `O`, atau spasi bila belum ada. Menang berarti tiga tanda sebaris: mendatar, tegak, atau diagonal;\n- `bool selesai() const` — ada pemenang, atau papan penuh;\n- `void cetak() const`.\n\n"
         "Baca n langkah. Cetak `Langkah ditolak` untuk tiap langkah yang ditolak. Lalu cetak papan (tiga baris) dan hasilnya: `X menang`, `O menang`, `Seri`, atau `Belum selesai`.\n\n"
         "Contoh: langkah `1 1`, `2 1`, `1 2`, `2 2`, `1 3`, `3 3` →\n\n```\nLangkah ditolak\nXXX\nOO.\n...\nX menang\n```" + W_ID,
         "Baris pertama: n. Lalu n baris berisi baris dan kolom sebuah langkah.", "`Langkah ditolak` untuk tiap langkah yang ditolak, lalu papan dan hasilnya.", "1 ≤ n ≤ 12; baris dan kolom antara 0 dan 4"),
     en=("Noughts and Crosses", "Make a class `Papan` (board) for a 3 × 3 game of noughts and crosses. The board, whose turn it is, and the number of filled squares are all `private`; players cannot put marks anywhere they like, let alone twice in a row.\n\n"
         "`private` attributes: `char sel[3][3]` (all `.` at first), `char giliran` (turn, `X` at first), `int terisi` (filled). Public methods:\n\n"
         "- `bool taruh(int b, int k)` (place) — the player whose turn it is puts their mark in row b, column k (counted from 1). **Refused** when the game is already over, the position is off the board, or the square is taken. The turn passes only when the move is accepted;\n"
         "- `char pemenang() const` (winner) — `X`, `O`, or a space when there is none yet. Winning means three marks in a line: across, down or diagonal;\n- `bool selesai() const` (over) — there is a winner, or the board is full;\n- `void cetak() const`.\n\n"
         "Read n moves. Print `Langkah ditolak` (move refused) for every refused move. Then print the board (three lines) and the result: `X menang`, `O menang`, `Seri` (draw) or `Belum selesai` (not over).\n\n"
         "Example: moves `1 1`, `2 1`, `1 2`, `2 2`, `1 3`, `3 3` →\n\n```\nLangkah ditolak\nXXX\nOO.\n...\nX menang\n```" + W_EN,
         "First line: n. Then n lines with the row and column of a move.", "`Langkah ditolak` for every refused move, then the board and the result.", "1 ≤ n ≤ 12; rows and columns are between 0 and 4"),
     ar=("رقعة إكس-أو", "أنشئ صنفًا `Papan` (رقعة) للعبة إكس-أو 3 × 3. الرقعة وصاحب الدور وعدد الخانات المملوءة كلها `private`؛ فلا يستطيع اللاعبون وضع علاماتهم حيث شاؤوا، فضلًا عن مرتين متتاليتين.\n\n"
         "الخصائص الخاصة: `char sel[3][3]` (كلها `.` في البداية) و `char giliran` (الدور، ويبدأ `X`) و `int terisi` (المملوء). الطرائق العامة:\n\n"
         "- `bool taruh(int b, int k)` (ضع) — يضع صاحب الدور علامته في الصف b والعمود k (العدّ من 1). **تُرفض** إذا انتهت اللعبة أو كان الموضع خارج الرقعة أو الخانة مشغولة. ينتقل الدور فقط إذا قُبلت الحركة؛\n"
         "- `char pemenang() const` (الفائز) — `X` أو `O` أو مسافة إذا لم يوجد بعد. الفوز ثلاث علامات على خط: أفقي أو رأسي أو قطري؛\n- `bool selesai() const` (انتهت) — يوجد فائز أو امتلأت الرقعة؛\n- `void cetak() const`.\n\n"
         "اقرأ n حركة. اطبع `Langkah ditolak` (حركة مرفوضة) لكل حركة مرفوضة. ثم اطبع الرقعة (ثلاثة أسطر) والنتيجة: `X menang` أو `O menang` أو `Seri` (تعادل) أو `Belum selesai` (لم تنتهِ).\n\n"
         "مثال: الحركات `1 1` و `2 1` و `1 2` و `2 2` و `1 3` و `3 3` ←\n\n```\nLangkah ditolak\nXXX\nOO.\n...\nX menang\n```" + W_AR,
         "السطر الأول: n. ثم n سطرًا فيها صف الحركة وعمودها.", "`Langkah ditolak` لكل حركة مرفوضة ثم الرقعة والنتيجة.", "1 ≤ n ≤ 12؛ الصفوف والأعمدة بين 0 و 4"))

soal("himpunan-bilangan", "sedang", "trimmed",
     "for _ in range(r.randint(1, 25)):\n    q = r.random()\n    x = r.randint(1, 12)\n    if q < 0.5:\n        print('tambah', x)\n    elif q < 0.75:\n        print('hapus', x)\n    else:\n        print('punya', x)\nprint('selesai')\n",
     program(r'''
class Himpunan {
private:
    int isi[50];
    int banyak = 0;

    int cari(int x) const {
        for (int k = 0; k < banyak; k++) {
            if (isi[k] == x) {
                return k;
            }
        }
        return -1;
    }

public:
    bool punya(int x) const {
        return cari(x) >= 0;
    }

    bool tambah(int x) {
        if (punya(x)) {
            return false;
        }
        auto k = banyak;
        while (k > 0 && isi[k - 1] > x) {
            isi[k] = isi[k - 1];
            k--;
        }
        isi[k] = x;
        banyak++;
        return true;
    }

    bool hapus(int x) {
        auto k = cari(x);
        if (k < 0) {
            return false;
        }
        for (int p = k; p < banyak - 1; p++) {
            isi[p] = isi[p + 1];
        }
        banyak--;
        return true;
    }

    void cetak() const {
        cout << "{";
        for (int k = 0; k < banyak; k++) {
            if (k > 0) {
                cout << ", ";
            }
            cout << isi[k];
        }
        cout << "}" << endl;
    }
};''', r'''    Himpunan h;
    string perintah;
    while (cin >> perintah) {
        if (perintah == "selesai") {
            break;
        }
        int x;
        cin >> x;
        if (perintah == "tambah") {
            if (!h.tambah(x)) {
                cout << "sudah ada" << endl;
            }
        } else if (perintah == "hapus") {
            if (!h.hapus(x)) {
                cout << "tidak ada" << endl;
            }
        } else if (h.punya(x)) {
            cout << "ya" << endl;
        } else {
            cout << "tidak" << endl;
        }
    }
    h.cetak();''', "string"), wajib=W,
     id=("Himpunan Bilangan", "Di mata kuliah Struktur Data, *abstract data type* (ADT) berarti memisahkan **apa yang bisa dilakukan** dari **bagaimana data disimpan**. Enkapsulasi adalah cara C++ menegakkan pemisahan itu.\n\n"
         "Buat class `Himpunan` — kumpulan bilangan tanpa duplikat. Penyimpanannya `private`: `int isi[50]`, `int banyak`, dan method bantu `int cari(int x) const`. Simpan isinya **selalu terurut naik**. Method publik:\n\n"
         "- `bool tambah(int x)` — gagal bila x sudah ada;\n- `bool hapus(int x)` — gagal bila x tidak ada;\n- `bool punya(int x) const`;\n- `void cetak() const` — mencetak isi sebagai `{1, 3, 7}`; himpunan kosong dicetak `{}`.\n\n"
         "Perintah: `tambah x` (cetak `sudah ada` bila gagal), `hapus x` (cetak `tidak ada` bila gagal), `punya x` (cetak `ya` atau `tidak`), `selesai`. Di akhir cetak himpunannya.\n\n"
         "Contoh: `tambah 7`, `tambah 3`, `tambah 7`, `punya 3`, `hapus 5`, `tambah 1`, `selesai` →\n\n```\nsudah ada\nya\ntidak ada\n{1, 3, 7}\n```" + W_ID,
         "Beberapa baris perintah, diakhiri `selesai`.", "Satu baris untuk tiap perintah yang menghasilkan keluaran, lalu isi himpunan.", "Paling banyak 25 perintah; 1 ≤ x ≤ 12"),
     en=("A Set of Numbers", "In the Data Structures course an *abstract data type* (ADT) means separating **what can be done** from **how the data is stored**. Encapsulation is how C++ enforces that separation.\n\n"
         "Make a class `Himpunan` (set) — a collection of numbers without duplicates. Its storage is `private`: `int isi[50]`, `int banyak`, and a helper method `int cari(int x) const`. Keep the contents **always sorted ascending**. Public methods:\n\n"
         "- `bool tambah(int x)` (add) — fails when x is already there;\n- `bool hapus(int x)` (remove) — fails when x is not there;\n- `bool punya(int x) const` (has);\n- `void cetak() const` — prints the contents as `{1, 3, 7}`; an empty set is printed `{}`.\n\n"
         "Commands: `tambah x` (print `sudah ada`, already there, on failure), `hapus x` (print `tidak ada`, not there, on failure), `punya x` (print `ya` or `tidak`), `selesai`. At the end print the set.\n\n"
         "Example: `tambah 7`, `tambah 3`, `tambah 7`, `punya 3`, `hapus 5`, `tambah 1`, `selesai` →\n\n```\nsudah ada\nya\ntidak ada\n{1, 3, 7}\n```" + W_EN,
         "Several command lines, ending with `selesai`.", "One line for every command that produces output, then the contents of the set.", "At most 25 commands; 1 ≤ x ≤ 12"),
     ar=("مجموعة أعداد", "في مقرر هياكل البيانات يعني *نوع البيانات المجرد* (ADT) فصل **ما يمكن فعله** عن **كيفية تخزين البيانات**. والتغليف هو وسيلة ++C لفرض هذا الفصل.\n\n"
         "أنشئ صنفًا `Himpunan` (مجموعة) — تجمّع أعداد بلا تكرار. تخزينه `private`: `int isi[50]` و `int banyak` وطريقة مساعدة `int cari(int x) const`. احفظ المحتوى **مرتبًا تصاعديًا دائمًا**. الطرائق العامة:\n\n"
         "- `bool tambah(int x)` (أضف) — تفشل إذا كان x موجودًا؛\n- `bool hapus(int x)` (احذف) — تفشل إذا لم يكن x موجودًا؛\n- `bool punya(int x) const` (يحوي)؛\n- `void cetak() const` — تطبع المحتوى على الصورة `{1, 3, 7}`؛ والمجموعة الفارغة تُطبع `{}`.\n\n"
         "الأوامر: `tambah x` (اطبع `sudah ada` أي موجود عند الفشل) و `hapus x` (اطبع `tidak ada` أي غير موجود عند الفشل) و `punya x` (اطبع `ya` أو `tidak`) و `selesai`. في النهاية اطبع المجموعة.\n\n"
         "مثال: `tambah 7` ثم `tambah 3` ثم `tambah 7` ثم `punya 3` ثم `hapus 5` ثم `tambah 1` ثم `selesai` ←\n\n```\nsudah ada\nya\ntidak ada\n{1, 3, 7}\n```" + W_AR,
         "عدة أسطر أوامر تنتهي بـ `selesai`.", "سطر لكل أمر ينتج مخرجًا ثم محتوى المجموعة.", "25 أمرًا على الأكثر؛ 1 ≤ x ≤ 12"))

soal("kelas-uang", "sedang", "trimmed",
     "print(r.randint(0, 2000000), r.randint(0, 99))\nkali = 0\nfor _ in range(r.randint(1, 8)):\n    q = r.random()\n    if q < 0.4:\n        print('tambah', r.randint(0, 500000), r.randint(0, 99))\n    elif q < 0.8 or kali >= 2:\n        print('kurang', r.randint(0, 900000), r.randint(0, 99))\n    else:\n        kali += 1\n        print('kali', r.randint(0, 12))\nprint('selesai')\n",
     program(r'''
class Uang {
private:
    long long sen;

    static void cetakRibuan(long long n) {
        if (n < 1000) {
            cout << n;
            return;
        }
        cetakRibuan(n / 1000);
        cout << "." << setfill('0') << setw(3) << n % 1000;
    }

public:
    Uang(long long rupiah, int s) : sen(rupiah * 100 + s) {
    }

    void tambah(const Uang &lain) {
        sen += lain.sen;
    }

    bool kurang(const Uang &lain) {
        if (lain.sen > sen) {
            return false;
        }
        sen -= lain.sen;
        return true;
    }

    void kali(int k) {
        sen *= k;
    }

    void cetak() const {
        cout << "Rp ";
        cetakRibuan(sen / 100);
        cout << "," << setfill('0') << setw(2) << sen % 100 << endl;
    }
};''', r'''    long long rupiah;
    int sen;
    cin >> rupiah >> sen;
    Uang uang(rupiah, sen);
    string perintah;
    while (cin >> perintah) {
        if (perintah == "selesai") {
            break;
        }
        if (perintah == "kali") {
            int k;
            cin >> k;
            uang.kali(k);
            uang.cetak();
        } else {
            cin >> rupiah >> sen;
            Uang lain(rupiah, sen);
            if (perintah == "tambah") {
                uang.tambah(lain);
                uang.cetak();
            } else if (uang.kurang(lain)) {
                uang.cetak();
            } else {
                cout << "Ditolak" << endl;
            }
        }
    }''', "iomanip", "string"), wajib=W,
     id=("Class Uang", "Jumlah uang tidak boleh dihitung dengan `double`: 0.1 + 0.2 tidak tepat 0.3. Cara yang aman adalah menyimpan **banyak sen** sebagai bilangan bulat — tetapi itu urusan dalam class. Dari luar, yang terlihat hanya rupiah dan sen.\n\n"
         "Buat class `Uang` dengan satu atribut `private` `long long sen` dan method publik:\n\n"
         "- `Uang(long long rupiah, int s)`;\n- `void tambah(const Uang &lain)`;\n- `bool kurang(const Uang &lain)` — ditolak bila hasilnya akan negatif;\n- `void kali(int k)`;\n"
         "- `void cetak() const` — mencetak `Rp <rupiah>,<sen>`: rupiah dengan titik pemisah ribuan, sen selalu dua digit.\n\n"
         "Masukan: jumlah awal (rupiah dan sen), lalu perintah `tambah <rupiah> <sen>`, `kurang <rupiah> <sen>`, `kali <k>`, diakhiri `selesai`. Setelah tiap perintah cetak jumlah yang baru, atau `Ditolak`.\n\n"
         "Contoh: awal `12500 50`; `tambah 1000000 75`, `kurang 2000000 0`, `kali 3`, `selesai` →\n\n```\nRp 1.012.501,25\nDitolak\nRp 3.037.503,75\n```" + W_ID,
         "Baris pertama: rupiah dan sen awal. Lalu perintah-perintah, diakhiri `selesai`.", "Satu baris per perintah.", "0 ≤ rupiah ≤ 2000000; 0 ≤ sen ≤ 99; 0 ≤ k ≤ 12; paling banyak 8 perintah, paling banyak dua di antaranya `kali`"),
     en=("The Uang Class", "Amounts of money must not be computed with `double`: 0.1 + 0.2 is not exactly 0.3. The safe way is to store **the number of cents** as an integer — but that is the class's own business. From outside only rupiah and cents (`sen`) are seen.\n\n"
         "Make a class `Uang` (money) with a single `private` attribute `long long sen` and the public methods:\n\n"
         "- `Uang(long long rupiah, int s)`;\n- `void tambah(const Uang &lain)` (add);\n- `bool kurang(const Uang &lain)` (subtract) — refused when the result would be negative;\n- `void kali(int k)` (multiply);\n"
         "- `void cetak() const` — prints `Rp <rupiah>,<sen>`: the rupiah with dots as thousands separators, the cents always two digits.\n\n"
         "Input: the starting amount (rupiah and cents), then commands `tambah <rupiah> <sen>`, `kurang <rupiah> <sen>`, `kali <k>`, ending with `selesai`. After each command print the new amount, or `Ditolak`.\n\n"
         "Example: start `12500 50`; `tambah 1000000 75`, `kurang 2000000 0`, `kali 3`, `selesai` →\n\n```\nRp 1.012.501,25\nDitolak\nRp 3.037.503,75\n```" + W_EN,
         "First line: the starting rupiah and cents. Then the commands, ending with `selesai`.", "One line per command.", "0 ≤ rupiah ≤ 2000000; 0 ≤ cents ≤ 99; 0 ≤ k ≤ 12; at most 8 commands, at most two of them `kali`"),
     ar=("الصنف Uang", "لا يجوز حساب المبالغ المالية بـ `double`: 0.1 + 0.2 لا يساوي 0.3 تمامًا. الطريقة الآمنة تخزين **عدد السنتات** عددًا صحيحًا — لكن هذا شأن داخلي للصنف. من الخارج لا تُرى إلا الروبية والسنت (`sen`).\n\n"
         "أنشئ صنفًا `Uang` (مال) فيه خاصية `private` واحدة هي `long long sen` وطرائقه العامة:\n\n"
         "- `Uang(long long rupiah, int s)`؛\n- `void tambah(const Uang &lain)` (اجمع)؛\n- `bool kurang(const Uang &lain)` (اطرح) — تُرفض إذا كان الناتج سيصير سالبًا؛\n- `void kali(int k)` (اضرب)؛\n"
         "- `void cetak() const` — تطبع `Rp <الروبية>,<السنت>`: الروبية بنقاط تفصل الآلاف، والسنت برقمين دائمًا.\n\n"
         "المدخل: المبلغ الابتدائي (روبية وسنت) ثم أوامر `tambah <rupiah> <sen>` و `kurang <rupiah> <sen>` و `kali <k>` تنتهي بـ `selesai`. بعد كل أمر اطبع المبلغ الجديد أو `Ditolak`.\n\n"
         "مثال: البداية `12500 50`؛ `tambah 1000000 75` ثم `kurang 2000000 0` ثم `kali 3` ثم `selesai` ←\n\n```\nRp 1.012.501,25\nDitolak\nRp 3.037.503,75\n```" + W_AR,
         "السطر الأول: الروبية والسنت الابتدائيان. ثم الأوامر منتهيةً بـ `selesai`.", "سطر لكل أمر.", "0 ≤ الروبية ≤ 2000000؛ 0 ≤ السنت ≤ 99؛ 0 ≤ k ≤ 12؛ 8 أوامر على الأكثر، منها أمران `kali` على الأكثر"))

soal("pabrik-kartu", "sedang", "trimmed",
     NAMA + "dibuat = 0\nfor _ in range(r.randint(1, 15)):\n    if r.random() < 0.6:\n        print('buat', r.choice(NAMA))\n        dibuat += 1\n    else:\n        print('hapus', r.randint(1, max(1, dibuat + 1)))\nprint('selesai')\n",
     program(r'''
class Kartu {
private:
    static int berikut;
    static int hidup;
    int nomor;
    string pemilik;

    Kartu(const string &p) : pemilik(p) {
        berikut++;
        nomor = berikut;
        hidup++;
    }

public:
    static Kartu *buat(const string &p) {
        if (hidup == 3) {
            return nullptr;
        }
        return new Kartu(p);
    }

    ~Kartu() {
        hidup--;
    }

    int getNomor() const {
        return nomor;
    }

    string getPemilik() const {
        return pemilik;
    }
};

int Kartu::berikut = 0;
int Kartu::hidup = 0;''', r'''    Kartu *daftar[15];
    auto banyak = 0;
    string perintah;
    while (cin >> perintah) {
        if (perintah == "selesai") {
            break;
        }
        if (perintah == "buat") {
            string nama;
            cin >> nama;
            auto kartu = Kartu::buat(nama);
            if (kartu == nullptr) {
                cout << "Kuota penuh" << endl;
            } else {
                daftar[banyak] = kartu;
                banyak++;
                cout << "Kartu " << kartu->getNomor() << " untuk " << nama << endl;
            }
        } else {
            int nomor;
            cin >> nomor;
            auto ketemu = false;
            for (int k = 0; k < banyak; k++) {
                if (daftar[k] != nullptr && daftar[k]->getNomor() == nomor) {
                    delete daftar[k];
                    daftar[k] = nullptr;
                    ketemu = true;
                }
            }
            if (ketemu) {
                cout << "Kartu " << nomor << " dihapus" << endl;
            } else {
                cout << "Tidak ada kartu " << nomor << endl;
            }
        }
    }
    for (int k = 0; k < banyak; k++) {
        if (daftar[k] != nullptr) {
            cout << daftar[k]->getNomor() << " " << daftar[k]->getPemilik() << endl;
            delete daftar[k];
        }
    }''', "string"), wajib=W,
     id=("Pabrik Kartu", "**Konstruktor pun boleh `private`.** Bila begitu, kode di luar class tidak bisa membuat objek sendiri; ia harus meminta kepada sebuah method `static` — yang bisa menolak.\n\n"
         "Sebuah perpustakaan hanya boleh mengedarkan **paling banyak 3 kartu** pada satu waktu. Buat class `Kartu`:\n\n"
         "- `private`: `static int berikut` (nomor terakhir yang dipakai), `static int hidup` (banyak kartu yang beredar), `int nomor`, `string pemilik`, dan **konstruktor** `Kartu(const string &p)` yang memberi nomor urut berikutnya dan menaikkan `hidup`;\n"
         "- `public`: `static Kartu *buat(const string &p)` — mengembalikan `nullptr` bila sudah ada 3 kartu beredar; selain itu membuat kartu dengan `new`;\n- `public`: destruktor yang menurunkan `hidup`; `int getNomor() const`; `string getPemilik() const`.\n\n"
         "Perintah:\n\n- `buat <nama>` — cetak `Kartu <nomor> untuk <nama>`, atau `Kuota penuh`;\n- `hapus <nomor>` — hancurkan kartu itu dan cetak `Kartu <nomor> dihapus`, atau `Tidak ada kartu <nomor>`;\n- `selesai` — cetak kartu yang masih beredar sebagai `<nomor> <pemilik>`, menurut urutan pembuatan, lalu hancurkan.\n\n"
         "Nomor kartu tidak pernah dipakai ulang.\n\nContoh: `buat Andi`, `buat Budi`, `buat Citra`, `buat Dewi`, `hapus 2`, `buat Dewi`, `hapus 2`, `selesai` →\n\n```\nKartu 1 untuk Andi\nKartu 2 untuk Budi\nKartu 3 untuk Citra\nKuota penuh\nKartu 2 dihapus\nKartu 4 untuk Dewi\nTidak ada kartu 2\n1 Andi\n3 Citra\n4 Dewi\n```" + W_ID,
         "Beberapa baris perintah, diakhiri `selesai`.", "Satu baris per perintah, lalu daftar kartu yang tersisa.", "Paling banyak 15 perintah; nama satu kata."),
     en=("Card Factory", "**Even a constructor may be `private`.** Then code outside the class cannot create objects by itself; it has to ask a `static` method — which can say no.\n\n"
         "A library may have **at most 3 cards** in circulation at any time. Make a class `Kartu` (card):\n\n"
         "- `private`: `static int berikut` (last number used), `static int hidup` (cards in circulation), `int nomor`, `string pemilik`, and the **constructor** `Kartu(const string &p)`, which hands out the next number and raises `hidup`;\n"
         "- `public`: `static Kartu *buat(const string &p)` (make) — returns `nullptr` when 3 cards are already out; otherwise creates a card with `new`;\n- `public`: a destructor that lowers `hidup`; `int getNomor() const`; `string getPemilik() const`.\n\n"
         "Commands:\n\n- `buat <name>` — print `Kartu <number> untuk <name>`, or `Kuota penuh` (quota full);\n- `hapus <number>` — destroy that card and print `Kartu <number> dihapus`, or `Tidak ada kartu <number>` (no such card);\n- `selesai` — print the cards still out as `<number> <owner>`, in order of creation, then destroy them.\n\n"
         "A card number is never reused.\n\nExample: `buat Andi`, `buat Budi`, `buat Citra`, `buat Dewi`, `hapus 2`, `buat Dewi`, `hapus 2`, `selesai` →\n\n```\nKartu 1 untuk Andi\nKartu 2 untuk Budi\nKartu 3 untuk Citra\nKuota penuh\nKartu 2 dihapus\nKartu 4 untuk Dewi\nTidak ada kartu 2\n1 Andi\n3 Citra\n4 Dewi\n```" + W_EN,
         "Several command lines, ending with `selesai`.", "One line per command, then the list of cards left.", "At most 15 commands; names are one word."),
     ar=("مصنع البطاقات", "**حتى الباني يجوز أن يكون `private`.** عندئذ لا يستطيع الكود خارج الصنف إنشاء الكائنات بنفسه؛ بل عليه أن يطلب من طريقة `static` — وهي تستطيع الرفض.\n\n"
         "لا يجوز لمكتبة أن تتداول **أكثر من 3 بطاقات** في وقت واحد. أنشئ صنفًا `Kartu` (بطاقة):\n\n"
         "- `private`: `static int berikut` (آخر رقم استُعمل) و `static int hidup` (البطاقات المتداولة) و `int nomor` و `string pemilik` و**الباني** `Kartu(const string &p)` الذي يعطي الرقم التالي ويزيد `hidup`؛\n"
         "- `public`: `static Kartu *buat(const string &p)` (اصنع) — تُرجع `nullptr` إذا كانت 3 بطاقات متداولة؛ وإلا أنشأت بطاقة بـ `new`؛\n- `public`: هادم ينقص `hidup`؛ و `int getNomor() const`؛ و `string getPemilik() const`.\n\n"
         "الأوامر:\n\n- `buat <الاسم>` — اطبع `Kartu <الرقم> untuk <الاسم>` أو `Kuota penuh` (امتلأت الحصة)؛\n- `hapus <الرقم>` — اهدم تلك البطاقة واطبع `Kartu <الرقم> dihapus` أو `Tidak ada kartu <الرقم>` (لا توجد)؛\n- `selesai` — اطبع البطاقات الباقية على الصورة `<الرقم> <المالك>` بترتيب الإنشاء ثم اهدمها.\n\n"
         "رقم البطاقة لا يُعاد استعماله أبدًا.\n\nمثال: `buat Andi` ثم `buat Budi` ثم `buat Citra` ثم `buat Dewi` ثم `hapus 2` ثم `buat Dewi` ثم `hapus 2` ثم `selesai` ←\n\n```\nKartu 1 untuk Andi\nKartu 2 untuk Budi\nKartu 3 untuk Citra\nKuota penuh\nKartu 2 dihapus\nKartu 4 untuk Dewi\nTidak ada kartu 2\n1 Andi\n3 Citra\n4 Dewi\n```" + W_AR,
         "عدة أسطر أوامر تنتهي بـ `selesai`.", "سطر لكل أمر ثم قائمة البطاقات الباقية.", "15 أمرًا على الأكثر؛ الاسم كلمة واحدة."))

soal("larik-aman", "sedang", "trimmed",
     "n = r.randint(1, 8)\nprint(n)\nfor _ in range(r.randint(1, 20)):\n    q = r.random()\n    if q < 0.45:\n        print('atur', r.randint(-2, n + 2), r.randint(1, 99))\n    elif q < 0.8:\n        print('ambil', r.randint(-2, n + 2))\n    else:\n        n = r.randint(1, 10)\n        print('ukuran', n)\nprint('selesai')\n",
     program(r'''
class LarikAman {
private:
    int *data;
    int ukuran;

    bool sah(int i) const {
        return i >= 0 && i < ukuran;
    }

public:
    LarikAman(int n) : data(new int[n]), ukuran(n) {
        for (int k = 0; k < n; k++) {
            data[k] = 0;
        }
    }

    LarikAman(const LarikAman &lain) : data(new int[lain.ukuran]), ukuran(lain.ukuran) {
        for (int k = 0; k < ukuran; k++) {
            data[k] = lain.data[k];
        }
    }

    LarikAman &operator=(const LarikAman &lain) {
        if (this != &lain) {
            delete[] data;
            ukuran = lain.ukuran;
            data = new int[ukuran];
            for (int k = 0; k < ukuran; k++) {
                data[k] = lain.data[k];
            }
        }
        return *this;
    }

    ~LarikAman() {
        delete[] data;
    }

    bool atur(int i, int x) {
        if (!sah(i)) {
            return false;
        }
        data[i] = x;
        return true;
    }

    bool ambil(int i, int &x) const {
        if (!sah(i)) {
            return false;
        }
        x = data[i];
        return true;
    }

    void ubahUkuran(int n) {
        auto baru = new int[n];
        for (int k = 0; k < n; k++) {
            if (k < ukuran) {
                baru[k] = data[k];
            } else {
                baru[k] = 0;
            }
        }
        delete[] data;
        data = baru;
        ukuran = n;
    }

    int panjang() const {
        return ukuran;
    }
};''', r'''    int n;
    cin >> n;
    LarikAman larik(n);
    string perintah;
    while (cin >> perintah) {
        if (perintah == "selesai") {
            break;
        }
        if (perintah == "atur") {
            int i, x;
            cin >> i >> x;
            if (!larik.atur(i, x)) {
                cout << "Indeks di luar batas" << endl;
            }
        } else if (perintah == "ambil") {
            int i, x;
            cin >> i;
            if (larik.ambil(i, x)) {
                cout << x << endl;
            } else {
                cout << "Indeks di luar batas" << endl;
            }
        } else {
            int baru;
            cin >> baru;
            larik.ubahUkuran(baru);
        }
    }
    cout << "Isi:";
    for (int k = 0; k < larik.panjang(); k++) {
        int x;
        larik.ambil(k, x);
        cout << " " << x;
    }
    cout << endl;''', "string"), wajib=W,
     id=("Larik Aman", "Array biasa di C++ tidak memeriksa indeks: `a[10]` pada array berisi 5 elemen merusak memori tanpa peringatan. Bungkus array dinamis dalam class yang **tidak memberi jalan** untuk mengakses di luar batas.\n\n"
         "Buat class `LarikAman` dengan atribut `private` `int *data`, `int ukuran`, method bantu `private` `bool sah(int i) const`, dan method publik:\n\n"
         "- `LarikAman(int n)` — n elemen, semuanya 0; lengkapi dengan konstruktor salin, operator penugasan, dan destruktor (aturan tiga);\n"
         "- `bool atur(int i, int x)` dan `bool ambil(int i, int &x) const` — gagal bila i di luar 0 … ukuran − 1;\n"
         "- `void ubahUkuran(int n)` — mengubah banyak elemen; elemen lama yang masih muat dipertahankan, elemen baru bernilai 0;\n- `int panjang() const`.\n\n"
         "Masukan: ukuran awal, lalu perintah `atur i x`, `ambil i`, `ukuran n`, diakhiri `selesai`. Untuk `atur` atau `ambil` yang gagal cetak `Indeks di luar batas`; untuk `ambil` yang berhasil cetak nilainya. Di akhir cetak `Isi:` diikuti semua elemen.\n\n"
         "Contoh: ukuran 3; `atur 1 5`, `atur 3 9`, `ukuran 5`, `atur 3 9`, `ambil 1`, `ambil -1`, `selesai` →\n\n```\nIndeks di luar batas\n5\nIndeks di luar batas\nIsi: 0 5 0 9 0\n```" + W_ID,
         "Baris pertama: ukuran awal. Lalu perintah-perintah, diakhiri `selesai`.", "Satu baris untuk tiap `ambil` dan tiap `atur` yang gagal, lalu `Isi: …`.", "1 ≤ ukuran ≤ 10; paling banyak 20 perintah; −2 ≤ i ≤ 12; 1 ≤ x ≤ 99"),
     en=("A Safe Array", "An ordinary C++ array does not check indices: `a[10]` on an array of 5 elements corrupts memory without warning. Wrap a dynamic array in a class that **offers no way** to reach outside the bounds.\n\n"
         "Make a class `LarikAman` (safe array) with the `private` attributes `int *data`, `int ukuran` (size), a `private` helper `bool sah(int i) const` (valid), and the public methods:\n\n"
         "- `LarikAman(int n)` — n elements, all 0; complete it with a copy constructor, an assignment operator and a destructor (rule of three);\n"
         "- `bool atur(int i, int x)` (set) and `bool ambil(int i, int &x) const` (get) — fail when i is outside 0 … size − 1;\n"
         "- `void ubahUkuran(int n)` (resize) — changes the number of elements; old elements that still fit are kept, new ones are 0;\n- `int panjang() const` (length).\n\n"
         "Input: the starting size, then commands `atur i x`, `ambil i`, `ukuran n`, ending with `selesai`. For an `atur` or `ambil` that fails print `Indeks di luar batas` (index out of bounds); for a successful `ambil` print the value. At the end print `Isi:` followed by all elements.\n\n"
         "Example: size 3; `atur 1 5`, `atur 3 9`, `ukuran 5`, `atur 3 9`, `ambil 1`, `ambil -1`, `selesai` →\n\n```\nIndeks di luar batas\n5\nIndeks di luar batas\nIsi: 0 5 0 9 0\n```" + W_EN,
         "First line: the starting size. Then the commands, ending with `selesai`.", "One line for every `ambil` and every failed `atur`, then `Isi: …`.", "1 ≤ size ≤ 10; at most 20 commands; −2 ≤ i ≤ 12; 1 ≤ x ≤ 99"),
     ar=("مصفوفة آمنة", "المصفوفة العادية في ++C لا تفحص الفهارس: `a[10]` على مصفوفة من 5 عناصر يفسد الذاكرة بلا تحذير. غلِّف مصفوفة ديناميكية في صنف **لا يتيح طريقًا** للوصول خارج الحدود.\n\n"
         "أنشئ صنفًا `LarikAman` (مصفوفة آمنة) خاصيتاه الخاصتان `int *data` و `int ukuran` (الحجم)، وطريقة مساعدة `private` هي `bool sah(int i) const` (صحيح)، وطرائقه العامة:\n\n"
         "- `LarikAman(int n)` — n عنصرًا كلها 0؛ وأكمله بباني نسخ وعامل إسناد وهادم (قاعدة الثلاثة)؛\n"
         "- `bool atur(int i, int x)` (اضبط) و `bool ambil(int i, int &x) const` (خذ) — تفشلان إذا كان i خارج 0 … الحجم − 1؛\n"
         "- `void ubahUkuran(int n)` (غيِّر الحجم) — تغيّر عدد العناصر؛ العناصر القديمة التي ما زالت تتسع تبقى، والجديدة 0؛\n- `int panjang() const` (الطول).\n\n"
         "المدخل: الحجم الابتدائي ثم أوامر `atur i x` و `ambil i` و `ukuran n` تنتهي بـ `selesai`. للأمر `atur` أو `ambil` الفاشل اطبع `Indeks di luar batas` (فهرس خارج الحدود)؛ وللأمر `ambil` الناجح اطبع القيمة. في النهاية اطبع `Isi:` تليها كل العناصر.\n\n"
         "مثال: الحجم 3؛ `atur 1 5` ثم `atur 3 9` ثم `ukuran 5` ثم `atur 3 9` ثم `ambil 1` ثم `ambil -1` ثم `selesai` ←\n\n```\nIndeks di luar batas\n5\nIndeks di luar batas\nIsi: 0 5 0 9 0\n```" + W_AR,
         "السطر الأول: الحجم الابتدائي. ثم الأوامر منتهيةً بـ `selesai`.", "سطر لكل `ambil` ولكل `atur` فاشل ثم `Isi: …`.", "1 ≤ الحجم ≤ 10؛ 20 أمرًا على الأكثر؛ −2 ≤ i ≤ 12؛ 1 ≤ x ≤ 99"))

# ================================= SULIT (4) =================================

soal("kartu-atm", "sulit", "trimmed",
     "pin = r.randint(1000, 9999)\nprint(pin, r.randint(2, 40) * 50000)\nfor _ in range(r.randint(1, 20)):\n    q = r.random()\n    if q < 0.3:\n        print('masuk', pin if r.random() < (0.3 if i % 3 == 0 else 0.75) else r.randint(1000, 9999))\n    elif q < 0.6:\n        print('tarik', r.choice([r.randint(1, 10) * 50000, r.randint(1, 10) * 50000, 75000, 0, 5000000]))\n    elif q < 0.8:\n        print('keluar')\n    else:\n        baru = r.choice([r.randint(1000, 9999), 123, 12345, pin])\n        lama = pin if r.random() < 0.7 else r.randint(1000, 9999)\n        print('ganti', lama, baru)\nprint('selesai')\n",
     program(r'''
class KartuAtm {
private:
    int pin;
    int saldo;
    int salah = 0;
    bool diblokir = false;
    bool sedangMasuk = false;

public:
    KartuAtm(int p, int s) : pin(p), saldo(s) {
    }

    string masuk(int tebakan) {
        if (diblokir) {
            return "Kartu diblokir";
        }
        if (sedangMasuk) {
            return "Sudah masuk";
        }
        if (tebakan == pin) {
            salah = 0;
            sedangMasuk = true;
            return "Selamat datang";
        }
        salah++;
        if (salah == 3) {
            diblokir = true;
            return "Kartu diblokir";
        }
        return "PIN salah";
    }

    string tarik(int jumlah) {
        if (!sedangMasuk) {
            return "Belum masuk";
        }
        if (jumlah <= 0 || jumlah % 50000 != 0 || jumlah > saldo) {
            return "Ditolak";
        }
        saldo -= jumlah;
        return "Sisa saldo " + to_string(saldo);
    }

    string ganti(int lama, int baru) {
        if (!sedangMasuk) {
            return "Belum masuk";
        }
        if (lama != pin) {
            return "PIN lama salah";
        }
        if (baru < 1000 || baru > 9999 || baru == pin) {
            return "PIN baru tidak sah";
        }
        pin = baru;
        return "PIN diganti";
    }

    string keluar() {
        if (!sedangMasuk) {
            return "Belum masuk";
        }
        sedangMasuk = false;
        return "Sampai jumpa";
    }
};''', r'''    int pin, saldo;
    cin >> pin >> saldo;
    KartuAtm kartu(pin, saldo);
    string perintah;
    while (cin >> perintah) {
        if (perintah == "selesai") {
            break;
        }
        if (perintah == "masuk") {
            int p;
            cin >> p;
            cout << kartu.masuk(p) << endl;
        } else if (perintah == "tarik") {
            int x;
            cin >> x;
            cout << kartu.tarik(x) << endl;
        } else if (perintah == "ganti") {
            int lama, baru;
            cin >> lama >> baru;
            cout << kartu.ganti(lama, baru) << endl;
        } else {
            cout << kartu.keluar() << endl;
        }
    }''', "string"), wajib=W,
     id=("Kartu ATM", "Buat class `KartuAtm`. Seluruh keadaannya tersembunyi: `int pin`, `int saldo`, `int salah` (banyak PIN salah berturut-turut), `bool diblokir`, `bool sedangMasuk`. Tiap method publik mengembalikan **pesan** (`string`) yang harus dicetak.\n\n"
         "**`string masuk(int tebakan)`** — diperiksa berurutan:\n1. kartu diblokir → `Kartu diblokir`;\n2. sudah masuk → `Sudah masuk`;\n3. PIN benar → hitungan salah menjadi 0, status masuk → `Selamat datang`;\n4. PIN salah → hitungan salah bertambah; bila mencapai 3, kartu diblokir → `Kartu diblokir`; bila belum → `PIN salah`.\n\n"
         "**`string tarik(int jumlah)`** — belum masuk → `Belum masuk`; jumlah nol, negatif, bukan kelipatan 50000, atau melebihi saldo → `Ditolak`; selain itu saldo berkurang → `Sisa saldo <saldo>`.\n\n"
         "**`string ganti(int lama, int baru)`** — belum masuk → `Belum masuk`; `lama` bukan PIN saat ini → `PIN lama salah`; `baru` bukan empat digit (1000–9999) atau sama dengan PIN saat ini → `PIN baru tidak sah`; selain itu PIN berganti → `PIN diganti`.\n\n"
         "**`string keluar()`** — belum masuk → `Belum masuk`; selain itu → `Sampai jumpa`.\n\n"
         "Pemblokiran tidak mengeluarkan orang yang sedang masuk, tetapi setelah keluar ia tidak bisa masuk lagi.\n\n"
         "Masukan: PIN dan saldo awal, lalu perintah `masuk p`, `tarik x`, `ganti lama baru`, `keluar`, diakhiri `selesai`.\n\n"
         "Contoh: `1234 500000`; `tarik 100000`, `masuk 1111`, `masuk 1234`, `tarik 75000`, `tarik 200000`, `ganti 1234 99`, `keluar`, `selesai` →\n\n```\nBelum masuk\nPIN salah\nSelamat datang\nDitolak\nSisa saldo 300000\nPIN baru tidak sah\nSampai jumpa\n```" + W_ID,
         "Baris pertama: PIN dan saldo awal. Lalu perintah-perintah, diakhiri `selesai`.", "Satu baris pesan per perintah.", "PIN awal empat digit; 100000 ≤ saldo ≤ 2000000; paling banyak 20 perintah"),
     en=("ATM Card", "Make a class `KartuAtm`. Its whole state is hidden: `int pin`, `int saldo`, `int salah` (wrong PINs in a row), `bool diblokir` (blocked), `bool sedangMasuk` (logged in). Every public method returns a **message** (`string`) that is to be printed.\n\n"
         "**`string masuk(int tebakan)`** (log in) — checked in order:\n1. the card is blocked → `Kartu diblokir`;\n2. already logged in → `Sudah masuk`;\n3. right PIN → the wrong count becomes 0, logged in → `Selamat datang`;\n4. wrong PIN → the wrong count grows; if it reaches 3 the card is blocked → `Kartu diblokir`; if not → `PIN salah`.\n\n"
         "**`string tarik(int jumlah)`** (withdraw) — not logged in → `Belum masuk`; amount zero, negative, not a multiple of 50000, or above the balance → `Ditolak`; otherwise the balance drops → `Sisa saldo <balance>`.\n\n"
         "**`string ganti(int lama, int baru)`** (change PIN) — not logged in → `Belum masuk`; `lama` is not the current PIN → `PIN lama salah`; `baru` is not four digits (1000–9999) or equals the current PIN → `PIN baru tidak sah`; otherwise the PIN changes → `PIN diganti`.\n\n"
         "**`string keluar()`** (log out) — not logged in → `Belum masuk`; otherwise → `Sampai jumpa`.\n\n"
         "Blocking does not throw out someone who is logged in, but after logging out they cannot log in again.\n\n"
         "Input: the PIN and the opening balance, then commands `masuk p`, `tarik x`, `ganti old new`, `keluar`, ending with `selesai`.\n\n"
         "Example: `1234 500000`; `tarik 100000`, `masuk 1111`, `masuk 1234`, `tarik 75000`, `tarik 200000`, `ganti 1234 99`, `keluar`, `selesai` →\n\n```\nBelum masuk\nPIN salah\nSelamat datang\nDitolak\nSisa saldo 300000\nPIN baru tidak sah\nSampai jumpa\n```" + W_EN,
         "First line: the PIN and the opening balance. Then the commands, ending with `selesai`.", "One message line per command.", "The starting PIN has four digits; 100000 ≤ balance ≤ 2000000; at most 20 commands"),
     ar=("بطاقة الصرّاف", "أنشئ صنفًا `KartuAtm`. حالته كلها مخفية: `int pin` و `int saldo` و `int salah` (عدد الأرقام السرية الخاطئة المتتالية) و `bool diblokir` (محجوبة) و `bool sedangMasuk` (داخل). كل طريقة عامة تُرجع **رسالة** (`string`) تُطبع.\n\n"
         "**`string masuk(int tebakan)`** (دخول) — يُفحص بالترتيب:\n1. البطاقة محجوبة ← `Kartu diblokir`؛\n2. داخل أصلًا ← `Sudah masuk`؛\n3. الرقم صحيح ← يصير عدّ الخطأ 0 ويتم الدخول ← `Selamat datang`؛\n4. الرقم خطأ ← يزيد عدّ الخطأ؛ فإذا بلغ 3 حُجبت البطاقة ← `Kartu diblokir`؛ وإلا ← `PIN salah`.\n\n"
         "**`string tarik(int jumlah)`** (سحب) — لم يدخل ← `Belum masuk`؛ المبلغ صفر أو سالب أو ليس من مضاعفات 50000 أو يزيد على الرصيد ← `Ditolak`؛ وإلا نقص الرصيد ← `Sisa saldo <الرصيد>`.\n\n"
         "**`string ganti(int lama, int baru)`** (تغيير الرقم) — لم يدخل ← `Belum masuk`؛ `lama` ليس الرقم الحالي ← `PIN lama salah`؛ `baru` ليس من أربعة أرقام (1000–9999) أو يساوي الرقم الحالي ← `PIN baru tidak sah`؛ وإلا تغيّر الرقم ← `PIN diganti`.\n\n"
         "**`string keluar()`** (خروج) — لم يدخل ← `Belum masuk`؛ وإلا ← `Sampai jumpa`.\n\n"
         "الحجب لا يُخرج من هو داخل، لكنه بعد الخروج لا يستطيع الدخول ثانية.\n\n"
         "المدخل: الرقم السري والرصيد الابتدائي ثم أوامر `masuk p` و `tarik x` و `ganti lama baru` و `keluar` تنتهي بـ `selesai`.\n\n"
         "مثال: `1234 500000`؛ `tarik 100000` ثم `masuk 1111` ثم `masuk 1234` ثم `tarik 75000` ثم `tarik 200000` ثم `ganti 1234 99` ثم `keluar` ثم `selesai` ←\n\n```\nBelum masuk\nPIN salah\nSelamat datang\nDitolak\nSisa saldo 300000\nPIN baru tidak sah\nSampai jumpa\n```" + W_AR,
         "السطر الأول: الرقم السري والرصيد الابتدائي. ثم الأوامر منتهيةً بـ `selesai`.", "سطر رسالة لكل أمر.", "الرقم السري الابتدائي من أربعة أرقام؛ 100000 ≤ الرصيد ≤ 2000000؛ 20 أمرًا على الأكثر"))

soal("pesan-ruang", "sulit", "trimmed",
     "mulai = []\nfor _ in range(r.randint(1, 10)):\n    q = r.random()\n    if q < 0.7:\n        a = r.randint(0, 22)\n        b = a + r.randint(1, 4) if r.random() < 0.85 else a - r.randint(0, 2)\n        print('pesan', a, b)\n        mulai.append(a)\n    else:\n        print('batal', r.choice(mulai) if mulai and r.random() < 0.7 else r.randint(0, 23))\nprint('selesai')\n",
     program(r'''
class Ruang {
private:
    int mulai[10];
    int akhir[10];
    int banyak = 0;

    bool bentrok(int m, int s) const {
        for (int k = 0; k < banyak; k++) {
            if (m < akhir[k] && mulai[k] < s) {
                return true;
            }
        }
        return false;
    }

public:
    int pesan(int m, int s) {
        if (m < 0 || s > 24 || m >= s) {
            return 2;
        }
        if (bentrok(m, s)) {
            return 1;
        }
        auto k = banyak;
        while (k > 0 && mulai[k - 1] > m) {
            mulai[k] = mulai[k - 1];
            akhir[k] = akhir[k - 1];
            k--;
        }
        mulai[k] = m;
        akhir[k] = s;
        banyak++;
        return 0;
    }

    bool batal(int m) {
        for (int k = 0; k < banyak; k++) {
            if (mulai[k] == m) {
                for (int p = k; p < banyak - 1; p++) {
                    mulai[p] = mulai[p + 1];
                    akhir[p] = akhir[p + 1];
                }
                banyak--;
                return true;
            }
        }
        return false;
    }

    void cetak() const {
        if (banyak == 0) {
            cout << "Kosong" << endl;
        }
        for (int k = 0; k < banyak; k++) {
            cout << mulai[k] << "-" << akhir[k] << endl;
        }
    }
};''', r'''    Ruang ruang;
    string perintah;
    while (cin >> perintah) {
        if (perintah == "selesai") {
            break;
        }
        if (perintah == "pesan") {
            int m, s;
            cin >> m >> s;
            auto hasil = ruang.pesan(m, s);
            if (hasil == 0) {
                cout << "Dipesan" << endl;
            } else if (hasil == 1) {
                cout << "Bentrok" << endl;
            } else {
                cout << "Tidak sah" << endl;
            }
        } else {
            int m;
            cin >> m;
            if (ruang.batal(m)) {
                cout << "Dibatalkan" << endl;
            } else {
                cout << "Tidak ada" << endl;
            }
        }
    }
    ruang.cetak();''', "string"), wajib=W,
     id=("Pemesanan Ruang", "Buat class `Ruang` yang mencatat pemesanan sebuah ruang rapat dalam satu hari. Sebuah pemesanan adalah selang jam `mulai`–`akhir` (bilangan bulat, 0 sampai 24).\n\n"
         "Class menjamin dua hal tentang daftarnya, **selalu**: tidak ada dua pemesanan yang bertabrakan, dan daftarnya terurut menurut jam mulai. Jaminan itu hanya mungkin karena daftarnya `private`: `int mulai[10]`, `int akhir[10]`, `int banyak`, serta method bantu `bool bentrok(int m, int s) const`.\n\n"
         "Method publik:\n\n- `int pesan(int m, int s)` — mengembalikan 2 bila selangnya tidak sah (m negatif, s lebih dari 24, atau m tidak lebih kecil dari s); 1 bila bertabrakan dengan pemesanan yang ada; 0 bila berhasil dicatat. Pemesanan yang berakhir tepat saat pemesanan lain mulai **tidak** bertabrakan;\n"
         "- `bool batal(int m)` — menghapus pemesanan yang mulai tepat pada jam m;\n- `void cetak() const` — mencetak tiap pemesanan sebagai `<mulai>-<akhir>`, atau `Kosong`.\n\n"
         "Perintah: `pesan m s` (cetak `Dipesan`, `Bentrok`, atau `Tidak sah`), `batal m` (cetak `Dibatalkan` atau `Tidak ada`), `selesai`. Di akhir cetak daftarnya.\n\n"
         "Contoh: `pesan 13 15`, `pesan 9 11`, `pesan 10 12`, `pesan 11 13`, `pesan 16 16`, `batal 9`, `batal 9`, `selesai` →\n\n```\nDipesan\nDipesan\nBentrok\nDipesan\nTidak sah\nDibatalkan\nTidak ada\n11-13\n13-15\n```" + W_ID,
         "Beberapa baris perintah, diakhiri `selesai`.", "Satu baris per perintah, lalu daftar pemesanan.", "Paling banyak 10 perintah; semua jam antara −2 dan 27."),
     en=("Room Booking", "Make a class `Ruang` (room) that records the bookings of a meeting room for one day. A booking is an interval of hours `mulai`–`akhir` (start–end, integers 0 to 24).\n\n"
         "The class guarantees two things about its list, **always**: no two bookings overlap, and the list is sorted by starting hour. That guarantee is possible only because the list is `private`: `int mulai[10]`, `int akhir[10]`, `int banyak`, plus a helper `bool bentrok(int m, int s) const` (clashes).\n\n"
         "Public methods:\n\n- `int pesan(int m, int s)` (book) — returns 2 when the interval is invalid (m negative, s above 24, or m not smaller than s); 1 when it overlaps an existing booking; 0 when it is recorded. A booking that ends exactly when another starts does **not** overlap;\n"
         "- `bool batal(int m)` (cancel) — removes the booking that starts exactly at hour m;\n- `void cetak() const` — prints every booking as `<start>-<end>`, or `Kosong` (empty).\n\n"
         "Commands: `pesan m s` (print `Dipesan`, `Bentrok` or `Tidak sah`), `batal m` (print `Dibatalkan` or `Tidak ada`), `selesai`. At the end print the list.\n\n"
         "Example: `pesan 13 15`, `pesan 9 11`, `pesan 10 12`, `pesan 11 13`, `pesan 16 16`, `batal 9`, `batal 9`, `selesai` →\n\n```\nDipesan\nDipesan\nBentrok\nDipesan\nTidak sah\nDibatalkan\nTidak ada\n11-13\n13-15\n```" + W_EN,
         "Several command lines, ending with `selesai`.", "One line per command, then the list of bookings.", "At most 10 commands; all hours are between −2 and 27."),
     ar=("حجز القاعة", "أنشئ صنفًا `Ruang` (قاعة) يسجّل حجوزات قاعة اجتماعات في يوم واحد. الحجز مجال ساعات `mulai`–`akhir` (البداية–النهاية، أعداد صحيحة من 0 إلى 24).\n\n"
         "يضمن الصنف أمرين عن قائمته **دائمًا**: لا يتداخل حجزان، والقائمة مرتبة بحسب ساعة البداية. وهذا الضمان ممكن فقط لأن القائمة `private`: `int mulai[10]` و `int akhir[10]` و `int banyak` مع طريقة مساعدة `bool bentrok(int m, int s) const` (يتعارض).\n\n"
         "الطرائق العامة:\n\n- `int pesan(int m, int s)` (احجز) — تُرجع 2 إذا كان المجال غير صحيح (m سالب، أو s أكبر من 24، أو m ليس أصغر من s)؛ و 1 إذا تداخل مع حجز موجود؛ و 0 إذا سُجِّل. الحجز الذي ينتهي لحظة بدء حجز آخر **لا** يتداخل معه؛\n"
         "- `bool batal(int m)` (ألغِ) — تحذف الحجز الذي يبدأ عند الساعة m تمامًا؛\n- `void cetak() const` — تطبع كل حجز على الصورة `<البداية>-<النهاية>`، أو `Kosong` (فارغة).\n\n"
         "الأوامر: `pesan m s` (اطبع `Dipesan` أو `Bentrok` أو `Tidak sah`) و `batal m` (اطبع `Dibatalkan` أو `Tidak ada`) و `selesai`. في النهاية اطبع القائمة.\n\n"
         "مثال: `pesan 13 15` ثم `pesan 9 11` ثم `pesan 10 12` ثم `pesan 11 13` ثم `pesan 16 16` ثم `batal 9` ثم `batal 9` ثم `selesai` ←\n\n```\nDipesan\nDipesan\nBentrok\nDipesan\nTidak sah\nDibatalkan\nTidak ada\n11-13\n13-15\n```" + W_AR,
         "عدة أسطر أوامر تنتهي بـ `selesai`.", "سطر لكل أمر ثم قائمة الحجوزات.", "10 أوامر على الأكثر؛ كل الساعات بين −2 و 27."))

soal("lift", "sulit", "trimmed",
     "n = r.randint(3, 20)\ns = r.randint(1, n)\nk = r.randint(1, 10)\nv = [r.randint(1, n) for _ in range(k)]\nif r.random() < 0.4:\n    v[r.randint(0, k - 1)] = r.choice([0, n + 1, s])\nif i == 2:\n    s, v = 1, [n]\nif i == 3:\n    s, v = n, [1, n - 1]\nprint(n, s)\nprint(len(v))\nprint(*v)\n",
     program(r'''
class Lift {
private:
    int lantai;
    int tertinggi;
    int arah = 1;
    int langkah = 0;
    bool diminta[21];

    bool adaDiArah() const {
        for (int l = lantai + arah; l >= 1 && l <= tertinggi; l += arah) {
            if (diminta[l]) {
                return true;
            }
        }
        return false;
    }

public:
    Lift(int n, int awal) : lantai(awal), tertinggi(n) {
        for (auto &d : diminta) {
            d = false;
        }
    }

    bool minta(int l) {
        if (l < 1 || l > tertinggi || l == lantai || diminta[l]) {
            return false;
        }
        diminta[l] = true;
        return true;
    }

    bool adaPermintaan() const {
        for (int l = 1; l <= tertinggi; l++) {
            if (diminta[l]) {
                return true;
            }
        }
        return false;
    }

    int maju() {
        if (!adaDiArah()) {
            arah = -arah;
        }
        lantai += arah;
        langkah++;
        if (diminta[lantai]) {
            diminta[lantai] = false;
            return lantai;
        }
        return 0;
    }

    int totalLangkah() const {
        return langkah;
    }
};''', r'''    int n, awal, k;
    cin >> n >> awal;
    cin >> k;
    Lift lift(n, awal);
    for (int p = 0; p < k; p++) {
        int l;
        cin >> l;
        if (!lift.minta(l)) {
            cout << "Ditolak " << l << endl;
        }
    }
    cout << "Urutan:";
    while (lift.adaPermintaan()) {
        auto berhenti = lift.maju();
        if (berhenti > 0) {
            cout << " " << berhenti;
        }
    }
    cout << endl;
    cout << "Langkah: " << lift.totalLangkah() << endl;'''), wajib=W,
     id=("Lift", "Sebuah lift melayani gedung berlantai 1 sampai n. Lift tidak mondar-mandir mengikuti urutan permintaan: ia **terus bergerak ke satu arah** selama masih ada lantai yang diminta di arah itu, baru kemudian berbalik.\n\n"
         "Buat class `Lift`. Keadaannya `private`: `int lantai` (posisi sekarang), `int tertinggi`, `int arah` (1 = naik, −1 = turun; mula-mula naik), `int langkah`, `bool diminta[21]`, dan method bantu `bool adaDiArah() const`. Method publik:\n\n"
         "- `Lift(int n, int awal)`;\n- `bool minta(int l)` — mencatat permintaan ke lantai l. Ditolak bila l di luar 1 … n, sama dengan lantai lift saat itu, atau sudah diminta;\n- `bool adaPermintaan() const`;\n"
         "- `int maju()` — bila tidak ada permintaan di arah yang sedang dituju, berbalik arah; lalu bergerak **satu lantai**. Bila lantai yang dicapai diminta, permintaan itu selesai dan nomor lantainya dikembalikan; bila tidak, kembalikan 0;\n- `int totalLangkah() const`.\n\n"
         "Masukan: n dan lantai awal, lalu k permintaan (semuanya diajukan sebelum lift bergerak). Cetak `Ditolak <l>` untuk tiap permintaan yang ditolak. Lalu jalankan lift sampai semua permintaan selesai, dan cetak `Urutan:` diikuti lantai-lantai tempat lift berhenti, serta `Langkah: <banyak lantai yang ditempuh>`.\n\n"
         "Contoh: n = 10, lantai awal 4, permintaan `2 9 4 6 9 12` →\n\n```\nDitolak 4\nDitolak 9\nDitolak 12\nUrutan: 6 9 2\nLangkah: 12\n```\n\n(Lift naik dari 4 ke 9, lalu turun ke 2: 5 + 7 = 12 lantai.)" + W_ID,
         "Baris pertama: n dan lantai awal. Baris kedua: k. Baris ketiga: k bilangan, lantai yang diminta.", "Baris `Ditolak` bila ada, lalu `Urutan: …` dan `Langkah: …`.", "3 ≤ n ≤ 20; 1 ≤ k ≤ 10; tiap permintaan antara 0 dan n + 1"),
     en=("Elevator", "An elevator serves a building with floors 1 to n. It does not rush back and forth in the order of the requests: it **keeps moving in one direction** as long as a requested floor lies that way, and only then turns round.\n\n"
         "Make a class `Lift`. Its state is `private`: `int lantai` (current floor), `int tertinggi` (top floor), `int arah` (direction: 1 = up, −1 = down; up at first), `int langkah` (floors travelled), `bool diminta[21]` (requested), and a helper `bool adaDiArah() const`. Public methods:\n\n"
         "- `Lift(int n, int awal)`;\n- `bool minta(int l)` (request) — records a request for floor l. Refused when l is outside 1 … n, equals the elevator's current floor, or is already requested;\n- `bool adaPermintaan() const` (any request left);\n"
         "- `int maju()` (advance) — if no request lies in the current direction, turn round; then move **one floor**. If the floor reached is requested, that request is done and the floor number is returned; otherwise return 0;\n- `int totalLangkah() const`.\n\n"
         "Input: n and the starting floor, then k requests (all made before the elevator moves). Print `Ditolak <l>` for every refused request. Then run the elevator until all requests are done, and print `Urutan:` (order) followed by the floors at which it stops, and `Langkah: <floors travelled>`.\n\n"
         "Example: n = 10, starting floor 4, requests `2 9 4 6 9 12` →\n\n```\nDitolak 4\nDitolak 9\nDitolak 12\nUrutan: 6 9 2\nLangkah: 12\n```\n\n(The elevator goes up from 4 to 9, then down to 2: 5 + 7 = 12 floors.)" + W_EN,
         "First line: n and the starting floor. Second line: k. Third line: k numbers, the floors requested.", "`Ditolak` lines if any, then `Urutan: …` and `Langkah: …`.", "3 ≤ n ≤ 20; 1 ≤ k ≤ 10; each request is between 0 and n + 1"),
     ar=("المصعد", "مصعد يخدم مبنى طوابقه من 1 إلى n. لا يتنقل المصعد جيئة وذهابًا بترتيب الطلبات: بل **يواصل الحركة في اتجاه واحد** ما دام في ذلك الاتجاه طابق مطلوب، ثم يستدير.\n\n"
         "أنشئ صنفًا `Lift`. حالته `private`: `int lantai` (الطابق الحالي) و `int tertinggi` (أعلى طابق) و `int arah` (الاتجاه: 1 = صعود، −1 = نزول؛ ويبدأ صعودًا) و `int langkah` (الطوابق المقطوعة) و `bool diminta[21]` (المطلوبة) وطريقة مساعدة `bool adaDiArah() const`. الطرائق العامة:\n\n"
         "- `Lift(int n, int awal)`؛\n- `bool minta(int l)` (اطلب) — تسجّل طلبًا للطابق l. يُرفض إذا كان l خارج 1 … n، أو يساوي طابق المصعد الحالي، أو مطلوبًا أصلًا؛\n- `bool adaPermintaan() const` (هل بقي طلب)؛\n"
         "- `int maju()` (تقدَّم) — إذا لم يكن في الاتجاه الحالي طلب فاستدر؛ ثم تحرَّك **طابقًا واحدًا**. إذا كان الطابق الذي بلغه مطلوبًا انتهى ذلك الطلب وأُرجع رقم الطابق؛ وإلا أُرجع 0؛\n- `int totalLangkah() const`.\n\n"
         "المدخل: n والطابق الابتدائي ثم k طلبًا (تُقدَّم كلها قبل أن يتحرك المصعد). اطبع `Ditolak <l>` لكل طلب مرفوض. ثم شغِّل المصعد حتى تنتهي كل الطلبات واطبع `Urutan:` (الترتيب) تليها الطوابق التي يتوقف عندها، و `Langkah: <الطوابق المقطوعة>`.\n\n"
         "مثال: n = 10 والطابق الابتدائي 4 والطلبات `2 9 4 6 9 12` ←\n\n```\nDitolak 4\nDitolak 9\nDitolak 12\nUrutan: 6 9 2\nLangkah: 12\n```\n\n(يصعد المصعد من 4 إلى 9 ثم ينزل إلى 2: 5 + 7 = 12 طابقًا.)" + W_AR,
         "السطر الأول: n والطابق الابتدائي. السطر الثاني: k. السطر الثالث: k عددًا هي الطوابق المطلوبة.", "أسطر `Ditolak` إن وُجدت ثم `Urutan: …` و `Langkah: …`.", "3 ≤ n ≤ 20؛ 1 ≤ k ≤ 10؛ كل طلب بين 0 و n + 1"))

soal("cache-lru", "sulit", "trimmed",
     "for _ in range(r.randint(1, 25)):\n    if r.random() < 0.55:\n        print('taruh', r.randint(1, 7), r.randint(10, 99))\n    else:\n        print('ambil', r.randint(1, 7))\nprint('selesai')\n",
     program(r'''
class CacheLru {
private:
    int kunci[3];
    int nilai[3];
    int terakhir[3];
    int banyak = 0;
    int jam = 0;

    int cari(int k) const {
        for (int p = 0; p < banyak; p++) {
            if (kunci[p] == k) {
                return p;
            }
        }
        return -1;
    }

    int tertua() const {
        auto t = 0;
        for (int p = 1; p < banyak; p++) {
            if (terakhir[p] < terakhir[t]) {
                t = p;
            }
        }
        return t;
    }

public:
    bool ambil(int k, int &v) {
        auto p = cari(k);
        if (p < 0) {
            return false;
        }
        jam++;
        terakhir[p] = jam;
        v = nilai[p];
        return true;
    }

    int taruh(int k, int v) {
        auto dibuang = 0;
        auto p = cari(k);
        if (p < 0) {
            if (banyak == 3) {
                p = tertua();
                dibuang = kunci[p];
            } else {
                p = banyak;
                banyak++;
            }
            kunci[p] = k;
        }
        jam++;
        terakhir[p] = jam;
        nilai[p] = v;
        return dibuang;
    }

    void cetak() const {
        cout << "Isi:";
        auto batas = jam + 1;
        for (int n = 0; n < banyak; n++) {
            auto pilih = -1;
            for (int p = 0; p < banyak; p++) {
                if (terakhir[p] < batas && (pilih < 0 || terakhir[p] > terakhir[pilih])) {
                    pilih = p;
                }
            }
            cout << " " << kunci[pilih] << "=" << nilai[pilih];
            batas = terakhir[pilih];
        }
        cout << endl;
    }
};''', r'''    CacheLru cache;
    string perintah;
    while (cin >> perintah) {
        if (perintah == "selesai") {
            break;
        }
        if (perintah == "taruh") {
            int k, v;
            cin >> k >> v;
            auto dibuang = cache.taruh(k, v);
            if (dibuang > 0) {
                cout << "Buang " << dibuang << endl;
            }
        } else {
            int k, v;
            cin >> k;
            if (cache.ambil(k, v)) {
                cout << v << endl;
            } else {
                cout << "tidak ada" << endl;
            }
        }
    }
    cache.cetak();''', "string"), wajib=W,
     id=("Cache LRU", "*Cache* menyimpan sejumlah kecil data yang sering dipakai. Bila penuh, salah satu harus dibuang; aturan **LRU** (*least recently used*) membuang data yang **paling lama tidak disentuh**.\n\n"
         "Buat class `CacheLru` berkapasitas **3** pasangan kunci–nilai. Pembukuannya `private`: `int kunci[3]`, `int nilai[3]`, `int terakhir[3]` (kapan terakhir disentuh), `int banyak`, `int jam` (penghitung yang naik pada setiap sentuhan), serta method bantu `int cari(int k) const` dan `int tertua() const`. Method publik:\n\n"
         "- `bool ambil(int k, int &v)` — bila kunci k ada, isi v dengan nilainya; pengambilan yang berhasil **dihitung sebagai sentuhan**;\n"
         "- `int taruh(int k, int v)` — bila k sudah ada, ganti nilainya; bila belum dan cache penuh, buang dulu pasangan yang paling lama tidak disentuh. Selalu dihitung sebagai sentuhan. Mengembalikan kunci yang dibuang, atau 0 bila tidak ada;\n"
         "- `void cetak() const` — mencetak `Isi:` diikuti pasangan `<kunci>=<nilai>`, **dari yang paling baru disentuh**.\n\n"
         "Perintah: `taruh k v` (cetak `Buang <kunci>` bila ada yang dibuang), `ambil k` (cetak nilainya atau `tidak ada`), `selesai`. Di akhir panggil `cetak()`.\n\n"
         "Contoh: `taruh 1 10`, `taruh 2 20`, `taruh 3 30`, `ambil 1`, `taruh 4 40`, `ambil 2`, `selesai` →\n\n```\n10\nBuang 2\ntidak ada\nIsi: 4=40 1=10 3=30\n```" + W_ID,
         "Beberapa baris perintah, diakhiri `selesai`.", "Satu baris untuk tiap `ambil` dan tiap pembuangan, lalu `Isi: …`.", "Paling banyak 25 perintah; 1 ≤ k ≤ 7; 10 ≤ v ≤ 99"),
     en=("LRU Cache", "A *cache* keeps a small amount of data that is used often. When it is full something must go; the **LRU** rule (*least recently used*) discards what has **gone untouched the longest**.\n\n"
         "Make a class `CacheLru` with room for **3** key–value pairs. Its bookkeeping is `private`: `int kunci[3]` (keys), `int nilai[3]` (values), `int terakhir[3]` (when last touched), `int banyak`, `int jam` (a counter that rises with every touch), and the helpers `int cari(int k) const` and `int tertua() const` (oldest). Public methods:\n\n"
         "- `bool ambil(int k, int &v)` (get) — if key k is present, put its value into v; a successful get **counts as a touch**;\n"
         "- `int taruh(int k, int v)` (put) — if k is present, replace its value; if not and the cache is full, first discard the pair untouched the longest. Always counts as a touch. Returns the discarded key, or 0 if none;\n"
         "- `void cetak() const` — prints `Isi:` followed by the pairs `<key>=<value>`, **most recently touched first**.\n\n"
         "Commands: `taruh k v` (print `Buang <key>`, discard, when something is discarded), `ambil k` (print the value or `tidak ada`), `selesai`. At the end call `cetak()`.\n\n"
         "Example: `taruh 1 10`, `taruh 2 20`, `taruh 3 30`, `ambil 1`, `taruh 4 40`, `ambil 2`, `selesai` →\n\n```\n10\nBuang 2\ntidak ada\nIsi: 4=40 1=10 3=30\n```" + W_EN,
         "Several command lines, ending with `selesai`.", "One line for every `ambil` and every discard, then `Isi: …`.", "At most 25 commands; 1 ≤ k ≤ 7; 10 ≤ v ≤ 99"),
     ar=("ذاكرة LRU المؤقتة", "*الذاكرة المؤقتة* تحفظ قدرًا صغيرًا من البيانات الكثيرة الاستعمال. إذا امتلأت وجب طرح شيء؛ وقاعدة **LRU** (*الأقل استعمالًا حديثًا*) تطرح ما **طال عهده بلا لمس**.\n\n"
         "أنشئ صنفًا `CacheLru` يسع **3** أزواج مفتاح–قيمة. دفاتره `private`: `int kunci[3]` (المفاتيح) و `int nilai[3]` (القيم) و `int terakhir[3]` (آخر لمس) و `int banyak` و `int jam` (عدّاد يزيد مع كل لمسة) والطريقتان المساعدتان `int cari(int k) const` و `int tertua() const` (الأقدم). الطرائق العامة:\n\n"
         "- `bool ambil(int k, int &v)` (خذ) — إذا وُجد المفتاح k فضع قيمته في v؛ والأخذ الناجح **يُعدّ لمسة**؛\n"
         "- `int taruh(int k, int v)` (ضع) — إذا وُجد k فاستبدل قيمته؛ وإن لم يوجد والذاكرة ممتلئة فاطرح أولًا الزوج الأطول عهدًا بلا لمس. تُعدّ لمسة دائمًا. تُرجع المفتاح المطروح أو 0 إن لم يُطرح شيء؛\n"
         "- `void cetak() const` — تطبع `Isi:` تليها الأزواج `<المفتاح>=<القيمة>` **من الأحدث لمسًا**.\n\n"
         "الأوامر: `taruh k v` (اطبع `Buang <المفتاح>` أي طرح، إذا طُرح شيء) و `ambil k` (اطبع القيمة أو `tidak ada`) و `selesai`. في النهاية استدعِ `cetak()`.\n\n"
         "مثال: `taruh 1 10` ثم `taruh 2 20` ثم `taruh 3 30` ثم `ambil 1` ثم `taruh 4 40` ثم `ambil 2` ثم `selesai` ←\n\n```\n10\nBuang 2\ntidak ada\nIsi: 4=40 1=10 3=30\n```" + W_AR,
         "عدة أسطر أوامر تنتهي بـ `selesai`.", "سطر لكل `ambil` ولكل طرح ثم `Isi: …`.", "25 أمرًا على الأكثر؛ 1 ≤ k ≤ 7؛ 10 ≤ v ≤ 99"))

tulis(__file__, "PBO301", 4, "cpp", {"mudah_sekali": 8, "mudah": 8, "mudah_sedang": 6, "sedang": 6, "sulit": 4})
