#!/usr/bin/env python3
"""Menyusun docs/soal/pbo301-pertemuan-1.json.

PBO301 pertemuan 1 — Pengantar OOP, C++ Modern, UML. Pertemuan ini diajarkan
SEBELUM class: soal-soalnya prosedural, ditulis dengan gaya C++ modern.
Konsep yang diizinkan di produksi: 21 konsep dasar mata kuliah (seluruh DDP101
kecuali CLASS_OBJECT, ENCAPSULATION, EXCEPTION) ditambah MODERN_CPP (`auto`,
range-based for, `nullptr`, inisialisasi seragam, lambda).

Solusi referensi TIDAK memakai class, kontainer STL (vector, array, pair —
baru diizinkan di pertemuan 13), algoritma STL, atau exception. Data disimpan
di array biasa, struct, dan memori dinamis. 32 soal: 8 / 8 / 6 / 6 / 4.

Dilewati dari silabus pertemuan 1 karena tidak bisa dinilai dari keluaran
program atau atas keputusan pemilik (2026-11-23): diagram UML dan tool UML,
perbandingan paradigma sebagai uraian, sejarah dan evolusi C++, penyiapan
toolchain (Code::Blocks, flag kompilator).

Soal-soal ini tidak mensyaratkan konsep di kode mahasiswa.

    python3 docs/soal/buat-pbo301-pertemuan-1.py
"""
from bangun import cpp, program, soal, tulis

NAMA = "NAMA = ['Andi', 'Budi', 'Citra', 'Dewi', 'Eko', 'Fajar', 'Gita', 'Hadi', 'Indah', 'Joko', 'Kartika', 'Lina', 'Made', 'Nanda', 'Putri', 'Rudi', 'Sari', 'Tono', 'Wulan', 'Yoga', 'Zahra', 'Bayu', 'Ayu', 'Rina', 'Dimas', 'Laras', 'Agus', 'Maya', 'Iwan', 'Tari']\n"
KATA = "KATA = ['belajar', 'program', 'komputer', 'kampus', 'buku', 'meja', 'layar', 'kode', 'data', 'angka', 'huruf', 'kelas', 'tugas', 'ujian', 'pagi', 'malam', 'cepat', 'rapi', 'benar', 'coba', 'objek', 'fungsi', 'memori', 'kompilator']\n"

# ============================== MUDAH SEKALI (8) ==============================

soal("terpanas-terdingin", "mudah_sekali", "trimmed", "v = [r.randint(-10, 45) for _ in range(7)]\nif i == 2:\n    v = [v[0]] * 7\nprint(*v)\n",
     cpp(r'''    int suhu[7]{};
    for (auto &s : suhu) {
        cin >> s;
    }
    auto panas = suhu[0];
    auto dingin = suhu[0];
    for (auto s : suhu) {
        if (s > panas) panas = s;
        if (s < dingin) dingin = s;
    }
    cout << panas << " " << dingin << endl;'''),
     id=("Terpanas dan Terdingin", "Suhu sebuah kota dicatat selama tujuh hari. Baca ketujuh suhu itu, lalu cetak suhu tertinggi dan suhu terendah.\n\n"
         "Simpan datanya di array dan telusuri dengan *range-based for*: `for (auto s : suhu)` mengunjungi setiap elemen tanpa indeks. Untuk mengisi array, pakai `auto &s` supaya `s` merujuk ke elemennya, bukan salinannya.\n\nUntuk `30 28 33 25 31 29 27` keluarannya `33 25`.",
         "Satu baris berisi tujuh bilangan bulat.", "Satu baris: suhu tertinggi, lalu suhu terendah.", "Tiap suhu antara −10 dan 45."),
     en=("Hottest and Coldest", "A city's temperature is recorded for seven days. Read the seven temperatures, then print the highest and the lowest.\n\n"
         "Keep the data in an array and walk through it with a *range-based for*: `for (auto s : suhu)` visits every element without an index. To fill the array use `auto &s`, so that `s` refers to the element and not to a copy.\n\nFor `30 28 33 25 31 29 27` the output is `33 25`.",
         "One line with seven integers.", "One line: the highest temperature, then the lowest.", "Each temperature is between −10 and 45."),
     ar=("الأشد حرًّا والأشد بردًا", "تُسجَّل درجة حرارة مدينة سبعة أيام. اقرأ الدرجات السبع ثم اطبع أعلاها وأدناها.\n\n"
         "احفظ البيانات في مصفوفة واعبرها بحلقة *range-based for*: `for (auto s : suhu)` تزور كل عنصر بلا فهرس. لملء المصفوفة استخدم `auto &s` ليشير `s` إلى العنصر نفسه لا إلى نسخة منه.\n\nعند `30 28 33 25 31 29 27` يكون المخرج `33 25`.",
         "سطر واحد فيه سبعة أعداد صحيحة.", "سطر واحد: أعلى درجة ثم أدناها.", "كل درجة بين −10 و 45."))

soal("hitung-huruf-besar", "mudah_sekali", "trimmed",
     KATA + "t = ' '.join(r.choice(KATA) for _ in range(r.randint(1, 6)))\np = 0 if i == 2 else r.random()\nprint(''.join(c.upper() if r.random() < p else c for c in t))\n",
     cpp(r'''    string baris;
    getline(cin, baris);
    auto banyak = 0;
    for (auto c : baris) {
        if (c >= 'A' && c <= 'Z') {
            banyak++;
        }
    }
    cout << banyak << endl;''', "string"),
     id=("Hitung Huruf Besar", "Baca satu baris teks, lalu cetak berapa banyak huruf besarnya.\n\n*Range-based for* juga berlaku untuk `string`: `for (auto c : baris)` mengunjungi karakter demi karakter.\n\nUntuk `Belajar KODE di kampus` jawabannya 5.",
         "Satu baris teks.", "Satu bilangan bulat: banyak huruf besar.", "Teks terdiri dari huruf dan spasi, paling panjang 80 karakter."),
     en=("Count the Capitals", "Read one line of text, then print how many capital letters it has.\n\nA *range-based for* works on a `string` too: `for (auto c : baris)` visits it character by character.\n\nFor `Belajar KODE di kampus` the answer is 5.",
         "One line of text.", "One integer: the number of capital letters.", "The text has letters and spaces, at most 80 characters."),
     ar=("عدّ الحروف الكبيرة", "اقرأ سطرًا واحدًا من النص ثم اطبع عدد الحروف الكبيرة فيه.\n\nحلقة *range-based for* تعمل على `string` أيضًا: `for (auto c : baris)` تزوره محرفًا محرفًا.\n\nللنص `Belajar KODE di kampus` الجواب 5.",
         "سطر واحد من النص.", "عدد صحيح واحد: عدد الحروف الكبيرة.", "النص حروف لاتينية ومسافات، 80 محرفًا على الأكثر."))

soal("kuadratkan-lima", "mudah_sekali", "trimmed", "print(*[r.randint(-100, 100) for _ in range(5)])\n",
     cpp(r'''    auto kuadrat = [](int x) { return x * x; };
    int data[5]{};
    for (auto &d : data) {
        cin >> d;
    }
    for (auto d : data) {
        cout << kuadrat(d) << " ";
    }
    cout << endl;'''),
     id=("Kuadratkan dengan Lambda", "Lambda adalah fungsi kecil tanpa nama yang ditulis langsung di tempat ia dipakai dan bisa disimpan dalam variabel:\n\n```\nauto kuadrat = [](int x) { return x * x; };\n```\n\n"
         "Baca lima bilangan bulat, lalu cetak kuadrat masing-masing dalam satu baris, dipisah spasi, dengan memanggil lambda itu.\n\nUntuk `1 -2 3 10 0` keluarannya `1 4 9 100 0`.",
         "Satu baris berisi lima bilangan bulat.", "Satu baris: kuadrat kelima bilangan itu, sesuai urutan.", "Tiap bilangan antara −100 dan 100."),
     en=("Square with a Lambda", "A lambda is a small nameless function written right where it is used; it can be kept in a variable:\n\n```\nauto kuadrat = [](int x) { return x * x; };\n```\n\n"
         "Read five integers, then print the square of each on one line, separated by spaces, by calling that lambda.\n\nFor `1 -2 3 10 0` the output is `1 4 9 100 0`.",
         "One line with five integers.", "One line: the squares of the five numbers, in order.", "Each number is between −100 and 100."),
     ar=("التربيع بدالة لامبدا", "اللامبدا دالة صغيرة بلا اسم تُكتب حيث تُستعمل مباشرة، ويمكن حفظها في متغير:\n\n```\nauto kuadrat = [](int x) { return x * x; };\n```\n\n"
         "اقرأ خمسة أعداد صحيحة ثم اطبع مربع كل منها في سطر واحد تفصل بينها مسافات، باستدعاء تلك اللامبدا.\n\nعند `1 -2 3 10 0` يكون المخرج `1 4 9 100 0`.",
         "سطر واحد فيه خمسة أعداد صحيحة.", "سطر واحد: مربعات الأعداد الخمسة بالترتيب.", "كل عدد بين −100 و 100."))

soal("rata-rata-sepuluh", "mudah_sekali", "trimmed", "print(*[r.randint(0, 100) for _ in range(10)])\n",
     cpp(r'''    int nilai[10]{};
    auto jumlah = 0;
    for (auto &n : nilai) {
        cin >> n;
        jumlah += n;
    }
    cout << fixed << setprecision(2) << jumlah / 10.0 << endl;''', "iomanip"),
     id=("Rata-rata Sepuluh Nilai", "Baca sepuluh nilai ujian, lalu cetak rata-ratanya dengan dua angka di belakang koma.\n\n`auto jumlah = 0;` membuat `jumlah` bertipe `int`, karena `0` adalah `int`. Supaya pembagiannya tidak dibulatkan, bagilah dengan `10.0`.\n\nUntuk `70 80 90 60 75 85 95 65 55 100` keluarannya `77.50`.",
         "Satu baris berisi sepuluh bilangan bulat.", "Rata-rata dengan dua angka di belakang koma.", "Tiap nilai antara 0 dan 100."),
     en=("Average of Ten Marks", "Read ten exam marks, then print their average with two digits after the decimal point.\n\n`auto jumlah = 0;` makes `jumlah` an `int`, because `0` is an `int`. So that the division is not truncated, divide by `10.0`.\n\nFor `70 80 90 60 75 85 95 65 55 100` the output is `77.50`.",
         "One line with ten integers.", "The average with two digits after the decimal point.", "Each mark is between 0 and 100."),
     ar=("متوسط عشر درجات", "اقرأ عشر درجات امتحان ثم اطبع متوسطها برقمين بعد الفاصلة.\n\n`auto jumlah = 0;` تجعل `jumlah` من النوع `int` لأن `0` من النوع `int`. حتى لا تُبتر القسمة اقسم على `10.0`.\n\nعند `70 80 90 60 75 85 95 65 55 100` يكون المخرج `77.50`.",
         "سطر واحد فيه عشرة أعداد صحيحة.", "المتوسط برقمين بعد الفاصلة.", "كل درجة بين 0 و 100."))

soal("ganti-spasi", "mudah_sekali", "trimmed", KATA + "print(' '.join(r.choice(KATA) for _ in range(1 if i == 2 else r.randint(2, 7))))\n",
     cpp(r'''    string baris;
    getline(cin, baris);
    for (auto &c : baris) {
        if (c == ' ') {
            c = '_';
        }
    }
    cout << baris << endl;''', "string"),
     id=("Ganti Spasi", "Nama berkas sebaiknya tidak memuat spasi. Baca satu baris teks, lalu cetak teks itu dengan setiap spasi diganti garis bawah (`_`).\n\n"
         "Untuk **mengubah** isi `string` di dalam *range-based for*, variabelnya harus berupa referensi: `for (auto &c : baris)`.\n\nUntuk `laporan tugas akhir` keluarannya `laporan_tugas_akhir`.",
         "Satu baris teks.", "Teks yang sama dengan spasi diganti `_`.", "Teks terdiri dari huruf kecil dan spasi tunggal, paling panjang 80 karakter."),
     en=("Replace the Spaces", "A file name had better not contain spaces. Read one line of text, then print it with every space replaced by an underscore (`_`).\n\n"
         "To **change** a `string` inside a *range-based for*, the variable must be a reference: `for (auto &c : baris)`.\n\nFor `laporan tugas akhir` the output is `laporan_tugas_akhir`.",
         "One line of text.", "The same text with spaces replaced by `_`.", "The text has lowercase letters and single spaces, at most 80 characters."),
     ar=("استبدال المسافات", "يُستحسن ألّا يحتوي اسم الملف على مسافات. اقرأ سطرًا واحدًا من النص ثم اطبعه بعد استبدال شرطة سفلية (`_`) بكل مسافة.\n\n"
         "**لتغيير** محتوى `string` داخل حلقة *range-based for* يجب أن يكون المتغير مرجعًا: `for (auto &c : baris)`.\n\nعند `laporan tugas akhir` يكون المخرج `laporan_tugas_akhir`.",
         "سطر واحد من النص.", "النص نفسه بعد استبدال `_` بالمسافات.", "النص حروف لاتينية صغيرة ومسافات مفردة، 80 محرفًا على الأكثر."))

soal("lampu-berikutnya", "mudah_sekali", "trimmed", "print(['merah', 'hijau', 'kuning'][(i + int(os.environ['SEED'])) % 3])\n",
     program(r'''
enum class Lampu { Merah, Kuning, Hijau };

Lampu berikutnya(Lampu sekarang) {
    switch (sekarang) {
        case Lampu::Merah: return Lampu::Hijau;
        case Lampu::Hijau: return Lampu::Kuning;
        default: return Lampu::Merah;
    }
}''', r'''    string kata;
    cin >> kata;
    auto lampu = Lampu::Merah;
    if (kata == "kuning") {
        lampu = Lampu::Kuning;
    } else if (kata == "hijau") {
        lampu = Lampu::Hijau;
    }
    switch (berikutnya(lampu)) {
        case Lampu::Merah: cout << "merah" << endl; break;
        case Lampu::Kuning: cout << "kuning" << endl; break;
        case Lampu::Hijau: cout << "hijau" << endl; break;
    }''', "string"),
     id=("Lampu Berikutnya", "Lampu lalu lintas menyala bergiliran: merah → hijau → kuning → merah → …\n\nBaca warna yang sedang menyala (`merah`, `kuning`, atau `hijau`), lalu cetak warna yang menyala sesudahnya.\n\n"
         "Di C++ modern, pilihan yang terbatas seperti ini ditulis sebagai `enum class`, misalnya `enum class Lampu { Merah, Kuning, Hijau };`. Nilainya ditulis `Lampu::Merah` dan tidak bisa tertukar dengan bilangan biasa.",
         "Satu kata: `merah`, `kuning`, atau `hijau`.", "Satu kata: warna berikutnya.", "Kata ditulis dengan huruf kecil."),
     en=("The Next Light", "A traffic light cycles: red → green → yellow → red → … In Indonesian: `merah` → `hijau` → `kuning` → `merah`.\n\nRead the colour that is on now (`merah`, `kuning` or `hijau`), then print the colour that comes on next.\n\n"
         "In modern C++ a small fixed choice like this is written as an `enum class`, for example `enum class Lampu { Merah, Kuning, Hijau };`. Its values are written `Lampu::Merah` and cannot be mixed up with plain numbers.",
         "One word: `merah`, `kuning` or `hijau`.", "One word: the next colour.", "The word is in lowercase."),
     ar=("الضوء التالي", "تتناوب إشارة المرور: أحمر ← أخضر ← أصفر ← أحمر ← … وبالإندونيسية: `merah` ← `hijau` ← `kuning` ← `merah`.\n\nاقرأ اللون المضيء الآن (`merah` أو `kuning` أو `hijau`) ثم اطبع اللون الذي يضيء بعده.\n\n"
         "في ++C الحديثة يُكتب اختيار محدود كهذا على صورة `enum class`، مثل `enum class Lampu { Merah, Kuning, Hijau };`. تُكتب قيمه `Lampu::Merah` ولا تختلط بالأعداد العادية.",
         "كلمة واحدة: `merah` أو `kuning` أو `hijau`.", "كلمة واحدة: اللون التالي.", "الكلمة بحروف صغيرة."))

soal("nolkan-negatif", "mudah_sekali", "trimmed", "v = [r.randint(-50, 50) for _ in range(8)]\nif i == 2:\n    v = [-abs(x) - 1 for x in v]\nif i == 3:\n    v = [abs(x) for x in v]\nprint(*v)\n",
     cpp(r'''    int data[8]{};
    for (auto &d : data) {
        cin >> d;
        if (d < 0) {
            d = 0;
        }
    }
    for (auto d : data) {
        cout << d << " ";
    }
    cout << endl;'''),
     id=("Nolkan yang Negatif", "Sebuah sensor kadang mengirim nilai negatif yang tidak masuk akal. Baca delapan bilangan bulat, ganti setiap bilangan negatif dengan 0, lalu cetak kedelapan bilangan itu dalam satu baris.\n\n"
         "Elemen array diubah lewat `for (auto &d : data)`.\n\nUntuk `5 -3 0 12 -7 8 -1 4` keluarannya `5 0 0 12 0 8 0 4`.",
         "Satu baris berisi delapan bilangan bulat.", "Satu baris: kedelapan bilangan setelah yang negatif dinolkan.", "Tiap bilangan antara −51 dan 50."),
     en=("Zero the Negatives", "A sensor sometimes sends negative values that make no sense. Read eight integers, replace every negative one by 0, then print the eight numbers on one line.\n\n"
         "The array elements are changed through `for (auto &d : data)`.\n\nFor `5 -3 0 12 -7 8 -1 4` the output is `5 0 0 12 0 8 0 4`.",
         "One line with eight integers.", "One line: the eight numbers after the negatives were set to zero.", "Each number is between −51 and 50."),
     ar=("تصفير القيم السالبة", "يرسل مستشعر أحيانًا قيمًا سالبة لا معنى لها. اقرأ ثمانية أعداد صحيحة واستبدل 0 بكل عدد سالب ثم اطبع الأعداد الثمانية في سطر واحد.\n\n"
         "تُغيَّر عناصر المصفوفة عبر `for (auto &d : data)`.\n\nعند `5 -3 0 12 -7 8 -1 4` يكون المخرج `5 0 0 12 0 8 0 4`.",
         "سطر واحد فيه ثمانية أعداد صحيحة.", "سطر واحد: الأعداد الثمانية بعد تصفير السالب منها.", "كل عدد بين −51 و 50."))

soal("jumlah-vektor", "mudah_sekali", "trimmed", "print(r.randint(-100, 100), r.randint(-100, 100))\nprint(r.randint(-100, 100), r.randint(-100, 100))\n",
     program(r'''
struct Vektor {
    int x;
    int y;
};

Vektor tambah(Vektor a, Vektor b) {
    return Vektor{a.x + b.x, a.y + b.y};
}''', r'''    Vektor a{}, b{};
    cin >> a.x >> a.y;
    cin >> b.x >> b.y;
    auto c = tambah(a, b);
    cout << c.x << " " << c.y << endl;'''),
     id=("Jumlah Dua Vektor", "Sebuah vektor di bidang punya dua komponen, x dan y. Jumlah dua vektor diperoleh dengan menjumlahkan komponen yang bersesuaian.\n\n"
         "Buat `struct Vektor { int x; int y; };` dan fungsi `Vektor tambah(Vektor a, Vektor b)`. Dengan inisialisasi seragam, sebuah vektor bisa dibuat langsung: `Vektor{3, 4}`.\n\nBaca dua vektor dan cetak jumlahnya.\n\nContoh: (3, 4) + (−1, 6) = (2, 10), dicetak `2 10`.",
         "Dua baris, masing-masing berisi komponen x dan y sebuah vektor.", "Satu baris: komponen x dan y hasil penjumlahan.", "Tiap komponen antara −100 dan 100."),
     en=("Sum of Two Vectors", "A vector in the plane has two components, x and y. Two vectors are added by adding the matching components.\n\n"
         "Make a `struct Vektor { int x; int y; };` and a function `Vektor tambah(Vektor a, Vektor b)`. With uniform initialisation a vector can be built directly: `Vektor{3, 4}`.\n\nRead two vectors and print their sum.\n\nExample: (3, 4) + (−1, 6) = (2, 10), printed as `2 10`.",
         "Two lines, each with the x and y components of a vector.", "One line: the x and y components of the sum.", "Each component is between −100 and 100."),
     ar=("مجموع متجهين", "للمتجه في المستوى مركّبتان x و y. يُجمع متجهان بجمع المركّبات المتقابلة.\n\n"
         "أنشئ `struct Vektor { int x; int y; };` ودالة `Vektor tambah(Vektor a, Vektor b)`. بالتهيئة الموحَّدة يمكن إنشاء متجه مباشرة: `Vektor{3, 4}`.\n\nاقرأ متجهين واطبع مجموعهما.\n\nمثال: (3, 4) + (−1, 6) = (2, 10)، ويُطبع `2 10`.",
         "سطران في كل منهما المركّبتان x و y لمتجه.", "سطر واحد: المركّبتان x و y للمجموع.", "كل مركّبة بين −100 و 100."))

# ================================= MUDAH (8) =================================

soal("balik-array", "mudah", "trimmed", "n = {2: 1, 3: 100}.get(i, r.randint(2, 40))\nprint(n)\nprint(*[r.randint(-1000, 1000) for _ in range(n)])\n",
     cpp(r'''    int n;
    cin >> n;
    int *data = new int[n];
    for (int k = 0; k < n; k++) {
        cin >> data[k];
    }
    for (int k = n - 1; k >= 0; k--) {
        cout << data[k] << " ";
    }
    cout << endl;
    delete[] data;
    data = nullptr;'''),
     id=("Balik Array", "Baca n bilangan bulat, lalu cetak semuanya dalam urutan terbalik.\n\nBanyak datanya baru diketahui saat program berjalan, jadi arraynya dipesan dengan `new int[n]` dan dikembalikan dengan `delete[]`. Setelah itu penunjuknya diisi `nullptr`, penanda \"tidak menunjuk ke mana-mana\" di C++ modern (pengganti `NULL`).\n\n"
         "Untuk `3 8 -2 5` keluarannya `5 -2 8 3`.",
         "Baris pertama: n. Baris kedua: n bilangan bulat.", "Satu baris: bilangan-bilangan itu dalam urutan terbalik.", "1 ≤ n ≤ 100; tiap bilangan antara −1000 dan 1000"),
     en=("Reverse an Array", "Read n integers, then print them all in reverse order.\n\nThe amount of data is known only while the program runs, so the array is requested with `new int[n]` and given back with `delete[]`. After that the pointer is set to `nullptr`, the modern C++ mark for \"points nowhere\" (it replaces `NULL`).\n\n"
         "For `3 8 -2 5` the output is `5 -2 8 3`.",
         "First line: n. Second line: n integers.", "One line: the numbers in reverse order.", "1 ≤ n ≤ 100; each number is between −1000 and 1000"),
     ar=("عكس مصفوفة", "اقرأ n عددًا صحيحًا ثم اطبعها كلها بترتيب معكوس.\n\nعدد البيانات لا يُعرف إلا أثناء تشغيل البرنامج، فتُطلب المصفوفة بـ `new int[n]` وتُعاد بـ `delete[]`. بعد ذلك يُعطى المؤشر القيمة `nullptr`، وهي علامة «لا يشير إلى شيء» في ++C الحديثة (بديل `NULL`).\n\n"
         "عند `3 8 -2 5` يكون المخرج `5 -2 8 3`.",
         "السطر الأول: n. السطر الثاني: n عددًا صحيحًا.", "سطر واحد: الأعداد بترتيب معكوس.", "1 ≤ n ≤ 100؛ كل عدد بين −1000 و 1000"))

soal("frekuensi-digit", "mudah", "trimmed", "n = r.randint(1, 50)\nabjad = '0123456789' if i % 2 == 0 else ''.join(r.sample('0123456789', r.randint(1, 4)))\nprint(''.join(r.choice(abjad) for _ in range(n)))\n",
     cpp(r'''    string digit;
    cin >> digit;
    int hitung[10]{};
    for (auto c : digit) {
        hitung[c - '0']++;
    }
    for (int d = 0; d < 10; d++) {
        if (hitung[d] > 0) {
            cout << d << ": " << hitung[d] << endl;
        }
    }''', "string"),
     id=("Frekuensi Digit", "Baca sederet digit (bisa sangat panjang, jadi dibaca sebagai `string`), lalu cetak berapa kali tiap digit muncul. Digit yang tidak muncul tidak dicetak. Urutkan dari digit terkecil.\n\n"
         "Sediakan sepuluh penghitung dalam array yang langsung dinolkan: `int hitung[10]{};`.\n\nUntuk `2026202` keluarannya:\n\n```\n0: 2\n2: 4\n6: 1\n```",
         "Satu baris: deretan digit tanpa spasi.", "Untuk tiap digit yang muncul, satu baris `digit: banyak`, urut naik.", "Panjang deretan 1 sampai 50 digit."),
     en=("Digit Frequencies", "Read a run of digits (it may be very long, so read it as a `string`), then print how often each digit occurs. Digits that do not occur are not printed. Order them from the smallest digit.\n\n"
         "Keep ten counters in an array that is zeroed at once: `int hitung[10]{};`.\n\nFor `2026202` the output is:\n\n```\n0: 2\n2: 4\n6: 1\n```",
         "One line: a run of digits without spaces.", "For every digit that occurs, one line `digit: count`, ascending.", "The run is 1 to 50 digits long."),
     ar=("تكرار الأرقام", "اقرأ سلسلة أرقام (قد تكون طويلة جدًّا فتُقرأ على أنها `string`) ثم اطبع عدد مرات ظهور كل رقم. الأرقام التي لا تظهر لا تُطبع. رتِّبها من أصغر رقم.\n\n"
         "ضع عشرة عدّادات في مصفوفة تُصفَّر فورًا: `int hitung[10]{};`.\n\nعند `2026202` يكون المخرج:\n\n```\n0: 2\n2: 4\n6: 1\n```",
         "سطر واحد: سلسلة أرقام بلا مسافات.", "لكل رقم يظهر سطر واحد `الرقم: العدد`، تصاعديًا.", "طول السلسلة من 1 إلى 50 رقمًا."))

soal("nilai-tertinggi", "mudah", "trimmed", NAMA + "n = {2: 1}.get(i, r.randint(2, 20))\nnama = r.sample(NAMA, n)\nnilai = [r.randint(40, 100) for _ in range(n)]\nif i == 3 and n > 2:\n    nilai[1] = nilai[n - 1] = 100\nprint(n)\nfor a, b in zip(nama, nilai):\n    print(a, b)\n",
     program(r'''
struct Mahasiswa {
    string nama;
    int nilai;
};''', r'''    int n;
    cin >> n;
    Mahasiswa daftar[20]{};
    for (int k = 0; k < n; k++) {
        cin >> daftar[k].nama >> daftar[k].nilai;
    }
    auto terbaik = 0;
    for (int k = 1; k < n; k++) {
        if (daftar[k].nilai > daftar[terbaik].nilai) {
            terbaik = k;
        }
    }
    cout << daftar[terbaik].nama << endl;''', "string"),
     id=("Nilai Tertinggi", "Data n mahasiswa terdiri dari nama dan nilai. Simpan dalam array of `struct Mahasiswa { string nama; int nilai; };`, lalu cetak **nama** mahasiswa dengan nilai tertinggi. Bila ada beberapa, cetak yang paling dulu muncul di masukan.\n\n"
         "Contoh: `Andi 80`, `Citra 95`, `Eko 95` → `Citra`.",
         "Baris pertama: n. Lalu n baris, masing-masing berisi nama (satu kata) dan nilai.", "Satu kata: nama mahasiswa dengan nilai tertinggi.", "1 ≤ n ≤ 20; nilai antara 40 dan 100"),
     en=("Highest Mark", "The data of n students consist of a name and a mark. Keep them in an array of `struct Mahasiswa { string nama; int nilai; };`, then print the **name** of the student with the highest mark. If there are several, print the one that comes first in the input.\n\n"
         "Example: `Andi 80`, `Citra 95`, `Eko 95` → `Citra`.",
         "First line: n. Then n lines, each with a name (one word) and a mark.", "One word: the name of the student with the highest mark.", "1 ≤ n ≤ 20; marks are between 40 and 100"),
     ar=("أعلى درجة", "بيانات n طالبًا هي الاسم والدرجة. احفظها في مصفوفة من `struct Mahasiswa { string nama; int nilai; };` ثم اطبع **اسم** الطالب صاحب أعلى درجة. إذا تعدّدوا فاطبع أولهم ظهورًا في المدخل.\n\n"
         "مثال: `Andi 80` و `Citra 95` و `Eko 95` ← `Citra`.",
         "السطر الأول: n. ثم n سطرًا في كل منها اسم (كلمة واحدة) ودرجة.", "كلمة واحدة: اسم صاحب أعلى درجة.", "1 ≤ n ≤ 20؛ الدرجات بين 40 و 100"))

soal("di-atas-rata-rata", "mudah", "trimmed", "n = {2: 1}.get(i, r.randint(2, 50))\nv = [r.randint(0, 100) for _ in range(n)]\nif i == 3:\n    v = [v[0]] * n\nif i == 4:\n    v = [50] * (n - 1) + [51]\nprint(n)\nprint(*v)\n",
     cpp(r'''    int n;
    cin >> n;
    int *nilai = new int[n];
    auto jumlah = 0;
    for (int k = 0; k < n; k++) {
        cin >> nilai[k];
        jumlah += nilai[k];
    }
    auto banyak = 0;
    for (int k = 0; k < n; k++) {
        if (nilai[k] * n > jumlah) {
            banyak++;
        }
    }
    cout << banyak << endl;
    delete[] nilai;
    nilai = nullptr;'''),
     id=("Di Atas Rata-rata", "Baca n nilai, lalu cetak berapa banyak nilai yang **lebih besar** daripada rata-rata semuanya.\n\nRata-rata baru diketahui setelah semua nilai dibaca, jadi nilainya harus disimpan dulu.\n\n"
         "Untuk `60 70 80 90` rata-ratanya 75, sehingga jawabannya 2.\n\nCatatan: membandingkan `nilai × n` dengan `jumlah` menghindari pembagian dan masalah pembulatan.",
         "Baris pertama: n. Baris kedua: n bilangan bulat.", "Satu bilangan bulat: banyak nilai di atas rata-rata.", "1 ≤ n ≤ 50; tiap nilai antara 0 dan 100"),
     en=("Above Average", "Read n marks, then print how many of them are **greater** than the average of all.\n\nThe average is known only after all marks have been read, so they must be stored first.\n\n"
         "For `60 70 80 90` the average is 75, so the answer is 2.\n\nNote: comparing `mark × n` with the sum avoids division and rounding trouble.",
         "First line: n. Second line: n integers.", "One integer: how many marks are above the average.", "1 ≤ n ≤ 50; each mark is between 0 and 100"),
     ar=("فوق المتوسط", "اقرأ n درجة ثم اطبع كم منها **أكبر** من متوسطها جميعًا.\n\nلا يُعرف المتوسط إلا بعد قراءة كل الدرجات، فيجب تخزينها أولًا.\n\n"
         "للدرجات `60 70 80 90` المتوسط 75، فالجواب 2.\n\nملاحظة: مقارنة `الدرجة × n` بالمجموع تُغني عن القسمة ومشكلات التقريب.",
         "السطر الأول: n. السطر الثاني: n عددًا صحيحًا.", "عدد صحيح واحد: عدد الدرجات فوق المتوسط.", "1 ≤ n ≤ 50؛ كل درجة بين 0 و 100"))

soal("kata-terpanjang", "mudah", "trimmed", KATA + "print(' '.join(r.choice(KATA) for _ in range(1 if i == 2 else r.randint(2, 8))))\n",
     cpp(r'''    string baris;
    getline(cin, baris);
    string terpanjang = "";
    string kata = "";
    baris += ' ';
    for (auto c : baris) {
        if (c == ' ') {
            if (kata.size() > terpanjang.size()) {
                terpanjang = kata;
            }
            kata = "";
        } else {
            kata += c;
        }
    }
    cout << terpanjang << endl;''', "string"),
     id=("Kata Terpanjang", "Baca satu baris kalimat, lalu cetak kata terpanjangnya. Bila ada beberapa kata yang sama panjang, cetak yang paling dulu muncul.\n\nUntuk `kode program komputer kampus` keluarannya `komputer`.",
         "Satu baris: kata-kata dipisah satu spasi.", "Satu kata: kata terpanjang.", "Kalimat terdiri dari huruf kecil dan spasi tunggal, paling panjang 90 karakter."),
     en=("Longest Word", "Read one line holding a sentence, then print its longest word. If several words are equally long, print the one that comes first.\n\nFor `kode program komputer kampus` the output is `komputer`.",
         "One line: words separated by single spaces.", "One word: the longest word.", "The sentence has lowercase letters and single spaces, at most 90 characters."),
     ar=("أطول كلمة", "اقرأ سطرًا فيه جملة ثم اطبع أطول كلماتها. إذا تساوت عدة كلمات في الطول فاطبع أولها ظهورًا.\n\nعند `kode program komputer kampus` يكون المخرج `komputer`.",
         "سطر واحد: كلمات تفصل بينها مسافة واحدة.", "كلمة واحدة: أطول كلمة.", "الجملة حروف لاتينية صغيرة ومسافات مفردة، 90 محرفًا على الأكثر."))

soal("jumlah-diagonal", "mudah", "trimmed", "n = {2: 1, 3: 10}.get(i, r.randint(2, 9))\nprint(n)\nfor _ in range(n):\n    print(*[r.randint(-50, 50) for _ in range(n)])\n",
     cpp(r'''    int n;
    cin >> n;
    int m[10][10]{};
    for (int b = 0; b < n; b++) {
        for (int k = 0; k < n; k++) {
            cin >> m[b][k];
        }
    }
    auto utama = 0;
    auto samping = 0;
    for (int k = 0; k < n; k++) {
        utama += m[k][k];
        samping += m[k][n - 1 - k];
    }
    cout << utama << " " << samping << endl;'''),
     id=("Jumlah Diagonal", "Baca sebuah matriks persegi berukuran n × n. Cetak jumlah **diagonal utama** (dari kiri atas ke kanan bawah), lalu jumlah **diagonal samping** (dari kanan atas ke kiri bawah).\n\n"
         "Untuk matriks\n\n```\n1 2 3\n4 5 6\n7 8 9\n```\n\nkeluarannya `15 15` (1 + 5 + 9 dan 3 + 5 + 7).",
         "Baris pertama: n. Lalu n baris, masing-masing berisi n bilangan bulat.", "Satu baris: jumlah diagonal utama, lalu jumlah diagonal samping.", "1 ≤ n ≤ 10; tiap bilangan antara −50 dan 50"),
     en=("Diagonal Sums", "Read a square matrix of size n × n. Print the sum of the **main diagonal** (top left to bottom right), then the sum of the **anti-diagonal** (top right to bottom left).\n\n"
         "For the matrix\n\n```\n1 2 3\n4 5 6\n7 8 9\n```\n\nthe output is `15 15` (1 + 5 + 9 and 3 + 5 + 7).",
         "First line: n. Then n lines of n integers each.", "One line: the sum of the main diagonal, then of the anti-diagonal.", "1 ≤ n ≤ 10; each number is between −50 and 50"),
     ar=("مجموع القطرين", "اقرأ مصفوفة مربعة n × n. اطبع مجموع **القطر الرئيسي** (من أعلى اليسار إلى أسفل اليمين) ثم مجموع **القطر الثانوي** (من أعلى اليمين إلى أسفل اليسار).\n\n"
         "للمصفوفة\n\n```\n1 2 3\n4 5 6\n7 8 9\n```\n\nيكون المخرج `15 15` (1 + 5 + 9 و 3 + 5 + 7).",
         "السطر الأول: n. ثم n سطرًا في كل منها n عددًا صحيحًا.", "سطر واحد: مجموع القطر الرئيسي ثم مجموع القطر الثانوي.", "1 ≤ n ≤ 10؛ كل عدد بين −50 و 50"))

soal("terbesar-kedua", "mudah", "trimmed", "n = {2: 1}.get(i, r.randint(2, 30))\nv = [r.randint(-100, 100) for _ in range(n)]\nif i == 3:\n    v = [v[0]] * n\nif i == 4:\n    v = [max(v)] * 2 + v\nif i == 5:\n    v = sorted(set(v), reverse=True)\nprint(len(v))\nprint(*v)\n",
     cpp(r'''    int n;
    cin >> n;
    int *data = new int[n];
    for (int k = 0; k < n; k++) {
        cin >> data[k];
    }
    int *besar = &data[0];
    int *kedua = nullptr;
    for (int k = 1; k < n; k++) {
        if (data[k] > *besar) {
            kedua = besar;
            besar = &data[k];
        } else if (data[k] < *besar && (kedua == nullptr || data[k] > *kedua)) {
            kedua = &data[k];
        }
    }
    if (kedua == nullptr) {
        cout << "tidak ada" << endl;
    } else {
        cout << *kedua << endl;
    }
    delete[] data;
    data = nullptr;'''),
     id=("Terbesar Kedua", "Baca n bilangan bulat, lalu cetak **nilai terbesar kedua**: nilai terbesar di antara bilangan yang lebih kecil daripada nilai terbesar. Bilangan yang sama dihitung sebagai satu nilai.\n\n"
         "Bila tidak ada (semua bilangan sama, atau hanya ada satu bilangan), cetak `tidak ada`.\n\nContoh: `4 9 9 7` → 7; `5 5 5` → `tidak ada`.\n\n"
         "Salah satu cara mencatat \"belum ada jawaban\" adalah penunjuk yang bernilai `nullptr`.",
         "Baris pertama: n. Baris kedua: n bilangan bulat.", "Nilai terbesar kedua, atau `tidak ada`.", "1 ≤ n ≤ 32; tiap bilangan antara −100 dan 100"),
     en=("Second Largest", "Read n integers, then print the **second largest value**: the largest among the numbers that are smaller than the largest. Equal numbers count as one value.\n\n"
         "If there is none (all numbers are equal, or there is only one), print `tidak ada` (none).\n\nExamples: `4 9 9 7` → 7; `5 5 5` → `tidak ada`.\n\n"
         "One way to record \"no answer yet\" is a pointer holding `nullptr`.",
         "First line: n. Second line: n integers.", "The second largest value, or `tidak ada`.", "1 ≤ n ≤ 32; each number is between −100 and 100"),
     ar=("ثاني أكبر قيمة", "اقرأ n عددًا صحيحًا ثم اطبع **ثاني أكبر قيمة**: أكبر الأعداد التي هي أصغر من القيمة الكبرى. الأعداد المتساوية تُعدّ قيمة واحدة.\n\n"
         "إذا لم توجد (كل الأعداد متساوية، أو يوجد عدد واحد فقط) فاطبع `tidak ada` (لا يوجد).\n\nأمثلة: `4 9 9 7` ← 7؛ `5 5 5` ← `tidak ada`.\n\n"
         "من طرق تسجيل «لا جواب بعد» مؤشر قيمته `nullptr`.",
         "السطر الأول: n. السطر الثاني: n عددًا صحيحًا.", "ثاني أكبر قيمة، أو `tidak ada`.", "1 ≤ n ≤ 32؛ كل عدد بين −100 و 100"))

soal("hapus-vokal", "mudah", "trimmed", KATA + "t = ' '.join(r.choice(KATA) for _ in range(r.randint(1, 6)))\nprint(''.join(c.upper() if r.random() < 0.2 else c for c in t))\n",
     cpp(r'''    string baris;
    getline(cin, baris);
    auto vokal = [](char c) {
        return c == 'a' || c == 'i' || c == 'u' || c == 'e' || c == 'o'
            || c == 'A' || c == 'I' || c == 'U' || c == 'E' || c == 'O';
    };
    string hasil = "";
    for (auto c : baris) {
        if (!vokal(c)) {
            hasil += c;
        }
    }
    cout << hasil << endl;''', "string"),
     id=("Hapus Huruf Vokal", "Baca satu baris teks, lalu cetak teks itu tanpa huruf vokalnya (`a`, `i`, `u`, `e`, `o`, baik huruf kecil maupun besar). Karakter lain, termasuk spasi, dibiarkan.\n\n"
         "Pemeriksaan \"apakah ini vokal\" enak ditulis sebagai lambda yang mengembalikan `bool`.\n\nUntuk `Belajar Kode` keluarannya `Bljr Kd`.",
         "Satu baris teks.", "Teks itu tanpa huruf vokal.", "Teks terdiri dari huruf dan spasi tunggal, paling panjang 80 karakter; setiap kata memuat paling sedikit satu konsonan."),
     en=("Remove the Vowels", "Read one line of text, then print it without its vowels (`a`, `i`, `u`, `e`, `o`, lowercase or capital). Other characters, spaces included, stay.\n\n"
         "The test \"is this a vowel\" is nicely written as a lambda that returns a `bool`.\n\nFor `Belajar Kode` the output is `Bljr Kd`.",
         "One line of text.", "The text without vowels.", "The text has letters and single spaces, at most 80 characters; every word holds at least one consonant."),
     ar=("حذف حروف العلة", "اقرأ سطرًا واحدًا من النص ثم اطبعه بلا حروف العلة (`a` و `i` و `u` و `e` و `o`، صغيرة كانت أم كبيرة). تبقى المحارف الأخرى ومنها المسافات.\n\n"
         "يحسن كتابة فحص «هل هذا حرف علة» على صورة لامبدا تُرجع `bool`.\n\nعند `Belajar Kode` يكون المخرج `Bljr Kd`.",
         "سطر واحد من النص.", "النص بلا حروف العلة.", "النص حروف لاتينية ومسافات مفردة، 80 محرفًا على الأكثر؛ وفي كل كلمة حرف ساكن واحد على الأقل."))

# ============================== MUDAH-SEDANG (6) ==============================

soal("urutkan-array", "mudah_sedang", "trimmed", "n = {2: 1, 3: 100}.get(i, r.randint(2, 60))\nv = [r.randint(-1000, 1000) for _ in range(n)]\nif i == 4:\n    v.sort(reverse=True)\nif i == 5:\n    v = [r.randint(1, 5) for _ in range(n)]\nprint(n)\nprint(*v)\n",
     cpp(r'''    int n;
    cin >> n;
    int *data = new int[n];
    for (int k = 0; k < n; k++) {
        cin >> data[k];
    }
    for (int a = 0; a < n - 1; a++) {
        auto kecil = a;
        for (int b = a + 1; b < n; b++) {
            if (data[b] < data[kecil]) {
                kecil = b;
            }
        }
        auto sementara = data[a];
        data[a] = data[kecil];
        data[kecil] = sementara;
    }
    for (int k = 0; k < n; k++) {
        cout << data[k] << " ";
    }
    cout << endl;
    delete[] data;
    data = nullptr;'''),
     id=("Urutkan Array", "Baca n bilangan bulat, lalu cetak semuanya **terurut dari yang terkecil**.\n\nTulis sendiri pengurutannya (misalnya *selection sort*: cari yang terkecil di bagian yang belum terurut, tukar ke depan, ulangi). Pustaka pengurutan baru dipakai di pertemuan-pertemuan akhir.\n\n"
         "Untuk `5 -1 9 3 3` keluarannya `-1 3 3 5 9`.",
         "Baris pertama: n. Baris kedua: n bilangan bulat.", "Satu baris: bilangan-bilangan itu, terurut naik.", "1 ≤ n ≤ 100; tiap bilangan antara −1000 dan 1000"),
     en=("Sort an Array", "Read n integers, then print them all **sorted from the smallest**.\n\nWrite the sorting yourself (for example *selection sort*: find the smallest in the unsorted part, swap it to the front, repeat). Library sorting comes in the last meetings.\n\n"
         "For `5 -1 9 3 3` the output is `-1 3 3 5 9`.",
         "First line: n. Second line: n integers.", "One line: the numbers in ascending order.", "1 ≤ n ≤ 100; each number is between −1000 and 1000"),
     ar=("ترتيب مصفوفة", "اقرأ n عددًا صحيحًا ثم اطبعها كلها **مرتبة من الأصغر**.\n\nاكتب الترتيب بنفسك (مثل *الترتيب بالاختيار*: أوجد الأصغر في الجزء غير المرتب وبدِّله إلى المقدمة وكرِّر). ترتيب المكتبة يأتي في اللقاءات الأخيرة.\n\n"
         "عند `5 -1 9 3 3` يكون المخرج `-1 3 3 5 9`.",
         "السطر الأول: n. السطر الثاني: n عددًا صحيحًا.", "سطر واحد: الأعداد مرتبة تصاعديًا.", "1 ≤ n ≤ 100؛ كل عدد بين −1000 و 1000"))

soal("median", "mudah_sedang", "trimmed", "n = {2: 1, 3: 2, 4: 99}.get(i, r.randint(3, 60))\nprint(n)\nprint(*[r.randint(0, 100) for _ in range(n)])\n",
     cpp(r'''    int n;
    cin >> n;
    int data[100]{};
    for (int k = 0; k < n; k++) {
        cin >> data[k];
    }
    for (int a = 1; a < n; a++) {
        auto x = data[a];
        auto b = a - 1;
        while (b >= 0 && data[b] > x) {
            data[b + 1] = data[b];
            b--;
        }
        data[b + 1] = x;
    }
    cout << fixed << setprecision(1);
    if (n % 2 == 1) {
        cout << (double) data[n / 2] << endl;
    } else {
        cout << (data[n / 2 - 1] + data[n / 2]) / 2.0 << endl;
    }''', "iomanip"),
     id=("Median", "Median adalah nilai tengah sekumpulan data **setelah diurutkan**. Bila banyak datanya ganjil, median adalah data yang tepat di tengah. Bila genap, median adalah rata-rata dua data di tengah.\n\n"
         "Baca n bilangan bulat, lalu cetak mediannya dengan satu angka di belakang koma.\n\nContoh: `7 1 5` → `5.0`; `8 2 4 6` → `5.0`; `1 2 3 6` → `2.5`.",
         "Baris pertama: n. Baris kedua: n bilangan bulat.", "Median dengan satu angka di belakang koma.", "1 ≤ n ≤ 99; tiap bilangan antara 0 dan 100"),
     en=("Median", "The median is the middle value of a set of data **after sorting**. When the count is odd, the median is the item exactly in the middle. When it is even, the median is the average of the two middle items.\n\n"
         "Read n integers, then print their median with one digit after the decimal point.\n\nExamples: `7 1 5` → `5.0`; `8 2 4 6` → `5.0`; `1 2 3 6` → `2.5`.",
         "First line: n. Second line: n integers.", "The median with one digit after the decimal point.", "1 ≤ n ≤ 99; each number is between 0 and 100"),
     ar=("الوسيط", "الوسيط هو القيمة الوسطى لمجموعة بيانات **بعد ترتيبها**. إذا كان العدد فرديًا فالوسيط هو العنصر الأوسط تمامًا، وإذا كان زوجيًا فهو متوسط العنصرين الأوسطين.\n\n"
         "اقرأ n عددًا صحيحًا ثم اطبع وسيطها برقم واحد بعد الفاصلة.\n\nأمثلة: `7 1 5` ← `5.0`؛ `8 2 4 6` ← `5.0`؛ `1 2 3 6` ← `2.5`.",
         "السطر الأول: n. السطر الثاني: n عددًا صحيحًا.", "الوسيط برقم واحد بعد الفاصلة.", "1 ≤ n ≤ 99؛ كل عدد بين 0 و 100"))

soal("total-durasi", "mudah_sedang", "trimmed", "n = {2: 1}.get(i, r.randint(2, 20))\nprint(n)\nfor _ in range(n):\n    print(r.randint(0, 5), r.randint(0, 59), r.randint(0, 59))\n",
     program(r'''
struct Durasi {
    int jam;
    int menit;
    int detik;
};

Durasi tambah(Durasi a, Durasi b) {
    auto total = (a.jam + b.jam) * 3600 + (a.menit + b.menit) * 60 + a.detik + b.detik;
    return Durasi{total / 3600, total / 60 % 60, total % 60};
}''', r'''    int n;
    cin >> n;
    Durasi total{0, 0, 0};
    for (int k = 0; k < n; k++) {
        Durasi d{};
        cin >> d.jam >> d.menit >> d.detik;
        total = tambah(total, d);
    }
    cout << total.jam << ":" << setfill('0') << setw(2) << total.menit << ":" << setw(2) << total.detik << endl;''', "iomanip"),
     id=("Total Durasi", "Sebuah daftar putar berisi n lagu. Durasi tiap lagu diberikan sebagai jam, menit, dan detik. Hitung durasi seluruhnya.\n\n"
         "Buat `struct Durasi { int jam; int menit; int detik; };` dan fungsi `Durasi tambah(Durasi a, Durasi b)` yang hasilnya selalu rapi: menit dan detik di bawah 60.\n\n"
         "Cetak totalnya sebagai `J:MM:DD` — jam apa adanya, menit dan detik dua digit.\n\nContoh: durasi `0 45 50` dan `1 20 15` berjumlah `2:06:05`.",
         "Baris pertama: n. Lalu n baris, masing-masing berisi jam, menit, detik.", "Total durasi dalam bentuk `J:MM:DD`.", "1 ≤ n ≤ 20; 0 ≤ jam ≤ 5; 0 ≤ menit, detik ≤ 59"),
     en=("Total Duration", "A playlist holds n songs. The duration of each is given as hours, minutes and seconds. Compute the total duration.\n\n"
         "Make a `struct Durasi { int jam; int menit; int detik; };` and a function `Durasi tambah(Durasi a, Durasi b)` whose result is always tidy: minutes and seconds below 60.\n\n"
         "Print the total as `H:MM:SS` — hours as they are, minutes and seconds with two digits.\n\nExample: the durations `0 45 50` and `1 20 15` add up to `2:06:05`.",
         "First line: n. Then n lines, each with hours, minutes, seconds.", "The total duration in the form `H:MM:SS`.", "1 ≤ n ≤ 20; 0 ≤ hours ≤ 5; 0 ≤ minutes, seconds ≤ 59"),
     ar=("المدة الكلية", "قائمة تشغيل فيها n أغنية. مدة كل أغنية تُعطى بالساعات والدقائق والثواني. احسب المدة الكلية.\n\n"
         "أنشئ `struct Durasi { int jam; int menit; int detik; };` ودالة `Durasi tambah(Durasi a, Durasi b)` ناتجها مرتّب دائمًا: الدقائق والثواني أقل من 60.\n\n"
         "اطبع المجموع على الصورة `H:MM:SS` — الساعات كما هي، والدقائق والثواني برقمين.\n\nمثال: المدتان `0 45 50` و `1 20 15` مجموعهما `2:06:05`.",
         "السطر الأول: n. ثم n سطرًا في كل منها الساعات والدقائق والثواني.", "المدة الكلية على الصورة `H:MM:SS`.", "1 ≤ n ≤ 20؛ 0 ≤ الساعات ≤ 5؛ 0 ≤ الدقائق، الثواني ≤ 59"))

soal("gabung-terurut", "mudah_sedang", "trimmed",
     "n, m = r.randint(1, 50), r.randint(1, 50)\na = sorted(r.randint(-1000, 1000) for _ in range(n))\nb = sorted(r.randint(-1000, 1000) for _ in range(m))\nif i == 2:\n    b = [x + 3000 for x in b]\nif i == 3:\n    a = [x + 3000 for x in a]\nif i == 4:\n    b = a[:]\n    m = n\nprint(n, m)\nprint(*a)\nprint(*b)\n",
     cpp(r'''    int n, m;
    cin >> n >> m;
    int a[50]{}, b[50]{};
    for (int k = 0; k < n; k++) {
        cin >> a[k];
    }
    for (int k = 0; k < m; k++) {
        cin >> b[k];
    }
    auto p = 0;
    auto q = 0;
    while (p < n || q < m) {
        if (q == m || (p < n && a[p] <= b[q])) {
            cout << a[p] << " ";
            p++;
        } else {
            cout << b[q] << " ";
            q++;
        }
    }
    cout << endl;'''),
     id=("Gabung Dua Daftar Terurut", "Dua daftar bilangan **sudah terurut naik**. Gabungkan keduanya menjadi satu daftar yang juga terurut naik.\n\n"
         "Karena keduanya sudah terurut, tidak perlu mengurutkan ulang: cukup bandingkan elemen terdepan kedua daftar, ambil yang lebih kecil, dan ulangi.\n\nContoh: `1 4 9` dan `2 3 10 12` menjadi `1 2 3 4 9 10 12`.",
         "Baris pertama: n dan m. Baris kedua: n bilangan terurut naik. Baris ketiga: m bilangan terurut naik.", "Satu baris: n + m bilangan, terurut naik.", "1 ≤ n, m ≤ 50; tiap bilangan antara −1000 dan 4000"),
     en=("Merge Two Sorted Lists", "Two lists of numbers are **already sorted ascending**. Merge them into one list that is sorted ascending too.\n\n"
         "Because both are sorted there is no need to sort again: compare the front elements of the two lists, take the smaller, and repeat.\n\nExample: `1 4 9` and `2 3 10 12` become `1 2 3 4 9 10 12`.",
         "First line: n and m. Second line: n numbers in ascending order. Third line: m numbers in ascending order.", "One line: the n + m numbers in ascending order.", "1 ≤ n, m ≤ 50; each number is between −1000 and 4000"),
     ar=("دمج قائمتين مرتبتين", "قائمتا أعداد **مرتبتان تصاعديًا أصلًا**. ادمجهما في قائمة واحدة مرتبة تصاعديًا أيضًا.\n\n"
         "لأن القائمتين مرتبتان فلا حاجة إلى ترتيب جديد: قارن العنصرين الأولين في القائمتين وخذ الأصغر وكرِّر.\n\nمثال: `1 4 9` و `2 3 10 12` تصيران `1 2 3 4 9 10 12`.",
         "السطر الأول: n و m. السطر الثاني: n عددًا مرتبة تصاعديًا. السطر الثالث: m عددًا مرتبة تصاعديًا.", "سطر واحد: الأعداد n + m مرتبة تصاعديًا.", "1 ≤ n, m ≤ 50؛ كل عدد بين −1000 و 4000"))

soal("modus", "mudah_sedang", "trimmed", "n = {2: 1}.get(i, r.randint(2, 100))\nv = [r.randint(1, 100 if i % 2 else 8) for _ in range(n)]\nif i == 3:\n    v = [7, 3, 7, 3, 9]\nprint(len(v))\nprint(*v)\n",
     cpp(r'''    int n;
    cin >> n;
    int hitung[101]{};
    for (int k = 0; k < n; k++) {
        int x;
        cin >> x;
        hitung[x]++;
    }
    auto modus = 1;
    for (int x = 2; x <= 100; x++) {
        if (hitung[x] > hitung[modus]) {
            modus = x;
        }
    }
    cout << modus << " " << hitung[modus] << endl;'''),
     id=("Modus", "Modus adalah nilai yang paling sering muncul. Baca n bilangan, lalu cetak modusnya dan berapa kali ia muncul. Bila ada beberapa nilai yang sama seringnya, pilih yang **terkecil**.\n\n"
         "Karena nilainya hanya 1 sampai 100, sebuah array penghitung sudah cukup.\n\nContoh: `7 3 7 3 9` → `3 2`.",
         "Baris pertama: n. Baris kedua: n bilangan bulat.", "Satu baris: modus, lalu banyak kemunculannya.", "1 ≤ n ≤ 100; tiap bilangan antara 1 dan 100"),
     en=("Mode", "The mode is the value that occurs most often. Read n numbers, then print the mode and how many times it occurs. If several values are equally frequent, choose the **smallest**.\n\n"
         "Because the values are only 1 to 100, an array of counters is enough.\n\nExample: `7 3 7 3 9` → `3 2`.",
         "First line: n. Second line: n integers.", "One line: the mode, then how many times it occurs.", "1 ≤ n ≤ 100; each number is between 1 and 100"),
     ar=("المنوال", "المنوال هو القيمة الأكثر تكرارًا. اقرأ n عددًا ثم اطبع المنوال وعدد مرات ظهوره. إذا تساوت عدة قيم في التكرار فاختر **الأصغر**.\n\n"
         "لأن القيم من 1 إلى 100 فقط تكفي مصفوفة عدّادات.\n\nمثال: `7 3 7 3 9` ← `3 2`.",
         "السطر الأول: n. السطر الثاني: n عددًا صحيحًا.", "سطر واحد: المنوال ثم عدد مرات ظهوره.", "1 ≤ n ≤ 100؛ كل عدد بين 1 و 100"))

soal("rotasi-kiri", "mudah_sedang", "trimmed", "n = {2: 1}.get(i, r.randint(2, 100))\nk = {3: 0, 4: n, 5: 1000000000, 6: n * 7 + 1}.get(i, r.randint(1, 200))\nprint(n, k)\nprint(*[r.randint(0, 99) for _ in range(n)])\n",
     cpp(r'''    int n, k;
    cin >> n >> k;
    int *data = new int[n];
    for (int p = 0; p < n; p++) {
        cin >> data[p];
    }
    k = k % n;
    for (int p = 0; p < n; p++) {
        cout << data[(p + k) % n] << " ";
    }
    cout << endl;
    delete[] data;
    data = nullptr;'''),
     id=("Rotasi ke Kiri", "Merotasi sebuah daftar ke kiri satu langkah berarti memindahkan elemen pertama ke ujung belakang: `1 2 3 4 5` menjadi `2 3 4 5 1`.\n\n"
         "Baca n bilangan dan bilangan k, lalu cetak daftar itu setelah dirotasi ke kiri **k langkah**. Nilai k bisa jauh lebih besar daripada n; merotasi n langkah mengembalikan daftar ke keadaan semula.\n\n"
         "Contoh: `1 2 3 4 5` dengan k = 2 menjadi `3 4 5 1 2`; dengan k = 7 hasilnya sama.",
         "Baris pertama: n dan k. Baris kedua: n bilangan bulat.", "Satu baris: daftar setelah dirotasi.", "1 ≤ n ≤ 100; 0 ≤ k ≤ 1000000000; tiap bilangan antara 0 dan 99"),
     en=("Rotate Left", "Rotating a list left by one step moves its first element to the back: `1 2 3 4 5` becomes `2 3 4 5 1`.\n\n"
         "Read n numbers and a number k, then print the list after rotating it left by **k steps**. k may be far larger than n; rotating n steps puts the list back as it was.\n\n"
         "Example: `1 2 3 4 5` with k = 2 becomes `3 4 5 1 2`; with k = 7 the result is the same.",
         "First line: n and k. Second line: n integers.", "One line: the list after the rotation.", "1 ≤ n ≤ 100; 0 ≤ k ≤ 1000000000; each number is between 0 and 99"),
     ar=("تدوير إلى اليسار", "تدوير قائمة إلى اليسار خطوة واحدة يعني نقل عنصرها الأول إلى آخرها: `1 2 3 4 5` تصير `2 3 4 5 1`.\n\n"
         "اقرأ n عددًا وعددًا k ثم اطبع القائمة بعد تدويرها إلى اليسار **k خطوة**. قد يكون k أكبر كثيرًا من n؛ والتدوير n خطوة يعيد القائمة كما كانت.\n\n"
         "مثال: `1 2 3 4 5` مع k = 2 تصير `3 4 5 1 2`، ومع k = 7 الناتج نفسه.",
         "السطر الأول: n و k. السطر الثاني: n عددًا صحيحًا.", "سطر واحد: القائمة بعد التدوير.", "1 ≤ n ≤ 100؛ 0 ≤ k ≤ 1000000000؛ كل عدد بين 0 و 99"))

# ================================ SEDANG (6) ================================

soal("kali-matriks", "sedang", "trimmed",
     "b, t, k = r.randint(1, 5), r.randint(1, 5), r.randint(1, 5)\nif i == 2:\n    b = t = k = 1\nif i == 3:\n    b, t, k = 5, 5, 5\nprint(b, t, k)\nfor _ in range(b):\n    print(*[r.randint(-9, 9) for _ in range(t)])\nfor _ in range(t):\n    print(*[r.randint(-9, 9) for _ in range(k)])\n",
     cpp(r'''    int baris, tengah, kolom;
    cin >> baris >> tengah >> kolom;
    int a[5][5]{}, b[5][5]{};
    for (int i = 0; i < baris; i++) {
        for (int j = 0; j < tengah; j++) {
            cin >> a[i][j];
        }
    }
    for (int i = 0; i < tengah; i++) {
        for (int j = 0; j < kolom; j++) {
            cin >> b[i][j];
        }
    }
    for (int i = 0; i < baris; i++) {
        for (int j = 0; j < kolom; j++) {
            auto jumlah = 0;
            for (int t = 0; t < tengah; t++) {
                jumlah += a[i][t] * b[t][j];
            }
            cout << jumlah << " ";
        }
        cout << endl;
    }'''),
     id=("Perkalian Matriks", "Matriks A berukuran b × t dan matriks B berukuran t × k dapat dikalikan. Hasilnya matriks C berukuran b × k, dengan\n\n**C[i][j] = A[i][1] × B[1][j] + A[i][2] × B[2][j] + … + A[i][t] × B[t][j]**\n\n"
         "yaitu baris ke-i dari A \"dikalikan\" dengan kolom ke-j dari B.\n\nBaca kedua matriks dan cetak hasil kalinya.\n\nContoh: A = `1 2` / `3 4` dan B = `5 6` / `7 8` menghasilkan\n\n```\n19 22\n43 50\n```",
         "Baris pertama: b, t, k. Lalu b baris berisi t bilangan (matriks A), lalu t baris berisi k bilangan (matriks B).", "b baris, masing-masing k bilangan: matriks hasil kali.", "1 ≤ b, t, k ≤ 5; tiap bilangan antara −9 dan 9"),
     en=("Matrix Multiplication", "A matrix A of size b × t and a matrix B of size t × k can be multiplied. The result is a matrix C of size b × k, with\n\n**C[i][j] = A[i][1] × B[1][j] + A[i][2] × B[2][j] + … + A[i][t] × B[t][j]**\n\n"
         "that is, row i of A \"times\" column j of B.\n\nRead both matrices and print their product.\n\nExample: A = `1 2` / `3 4` and B = `5 6` / `7 8` give\n\n```\n19 22\n43 50\n```",
         "First line: b, t, k. Then b lines of t numbers (matrix A), then t lines of k numbers (matrix B).", "b lines of k numbers each: the product matrix.", "1 ≤ b, t, k ≤ 5; each number is between −9 and 9"),
     ar=("ضرب المصفوفات", "المصفوفة A بحجم b × t والمصفوفة B بحجم t × k يمكن ضربهما. الناتج مصفوفة C بحجم b × k حيث\n\n**C[i][j] = A[i][1] × B[1][j] + A[i][2] × B[2][j] + … + A[i][t] × B[t][j]**\n\n"
         "أي الصف i من A «مضروبًا» في العمود j من B.\n\nاقرأ المصفوفتين واطبع حاصل ضربهما.\n\nمثال: A = `1 2` / `3 4` و B = `5 6` / `7 8` تعطيان\n\n```\n19 22\n43 50\n```",
         "السطر الأول: b و t و k. ثم b سطرًا في كل منها t عددًا (المصفوفة A)، ثم t سطرًا في كل منها k عددًا (المصفوفة B).", "b سطرًا في كل منها k عددًا: مصفوفة الناتج.", "1 ≤ b, t, k ≤ 5؛ كل عدد بين −9 و 9"))

soal("peringkat-kelas", "sedang", "trimmed", NAMA + "n = {2: 1}.get(i, r.randint(2, 30))\nnama = r.sample(NAMA, n)\nnilai = [r.randint(50, 100) if i % 2 else r.choice([70, 80, 90]) for _ in range(n)]\nprint(n)\nfor a, b in zip(nama, nilai):\n    print(a, b)\n",
     program(r'''
struct Mahasiswa {
    string nama;
    int nilai;
};''', r'''    int n;
    cin >> n;
    Mahasiswa daftar[30]{};
    for (int k = 0; k < n; k++) {
        cin >> daftar[k].nama >> daftar[k].nilai;
    }
    auto lebihDulu = [](const Mahasiswa &a, const Mahasiswa &b) {
        if (a.nilai != b.nilai) {
            return a.nilai > b.nilai;
        }
        return a.nama < b.nama;
    };
    for (int a = 0; a < n - 1; a++) {
        auto terbaik = a;
        for (int b = a + 1; b < n; b++) {
            if (lebihDulu(daftar[b], daftar[terbaik])) {
                terbaik = b;
            }
        }
        auto sementara = daftar[a];
        daftar[a] = daftar[terbaik];
        daftar[terbaik] = sementara;
    }
    for (int k = 0; k < n; k++) {
        cout << k + 1 << ". " << daftar[k].nama << " " << daftar[k].nilai << endl;
    }''', "string"),
     id=("Peringkat Kelas", "Buat daftar peringkat sebuah kelas. Urutkan mahasiswa dari **nilai tertinggi**; bila nilainya sama, urutkan menurut **nama secara abjad**.\n\n"
         "Cetak tiap mahasiswa di satu baris: nomor urut, titik, spasi, nama, spasi, nilai.\n\nUntuk `Budi 80`, `Citra 95`, `Andi 80`:\n\n```\n1. Citra 95\n2. Andi 80\n3. Budi 80\n```\n\n"
         "Aturan \"siapa lebih dulu\" bisa ditulis sebagai lambda yang menerima dua mahasiswa dan mengembalikan `bool`; pengurutannya sendiri ditulis dengan perulangan. Dua `string` bisa dibandingkan dengan `<`.",
         "Baris pertama: n. Lalu n baris, masing-masing berisi nama (satu kata, diawali huruf besar) dan nilai.", "n baris daftar peringkat.", "1 ≤ n ≤ 30; semua nama berbeda; nilai antara 50 dan 100"),
     en=("Class Ranking", "Make the ranking list of a class. Order the students from the **highest mark**; when marks are equal, order by **name alphabetically**.\n\n"
         "Print each student on one line: position, full stop, space, name, space, mark.\n\nFor `Budi 80`, `Citra 95`, `Andi 80`:\n\n```\n1. Citra 95\n2. Andi 80\n3. Budi 80\n```\n\n"
         "The rule \"who comes first\" can be written as a lambda that takes two students and returns a `bool`; the sorting itself is written with loops. Two `string` values can be compared with `<`.",
         "First line: n. Then n lines, each with a name (one word starting with a capital) and a mark.", "The n lines of the ranking list.", "1 ≤ n ≤ 30; all names differ; marks are between 50 and 100"),
     ar=("ترتيب الصف", "أنشئ قائمة ترتيب لصف. رتِّب الطلاب من **أعلى درجة**؛ وإذا تساوت الدرجات فرتِّب بحسب **الاسم أبجديًا**.\n\n"
         "اطبع كل طالب في سطر: رقم الترتيب ثم نقطة ثم مسافة ثم الاسم ثم مسافة ثم الدرجة.\n\nللبيانات `Budi 80` و `Citra 95` و `Andi 80`:\n\n```\n1. Citra 95\n2. Andi 80\n3. Budi 80\n```\n\n"
         "قاعدة «من يسبق» يمكن كتابتها لامبدا تأخذ طالبين وتُرجع `bool`؛ أما الترتيب نفسه فيُكتب بحلقات. يمكن مقارنة قيمتين من النوع `string` بالعامل `<`.",
         "السطر الأول: n. ثم n سطرًا في كل منها اسم (كلمة واحدة تبدأ بحرف كبير) ودرجة.", "n سطرًا لقائمة الترتيب.", "1 ≤ n ≤ 30؛ الأسماء كلها مختلفة؛ الدرجات بين 50 و 100"))

soal("jumlah-bilangan-besar", "sedang", "trimmed",
     "def acak(d):\n    return str(r.randint(1, 9)) + ''.join(r.choice('0123456789') for _ in range(d - 1))\na, b = acak(r.randint(1, 100)), acak(r.randint(1, 100))\nif i == 2:\n    a, b = '9' * 100, '1'\nif i == 3:\n    a, b = '0', acak(30)\nif i == 4:\n    a, b = '9' * 50, '9' * 50\nif i == 5:\n    a, b = acak(3), acak(3)\nprint(a)\nprint(b)\n",
     cpp(r'''    string a, b;
    cin >> a >> b;
    string hasil = "";
    auto p = (int) a.size() - 1;
    auto q = (int) b.size() - 1;
    auto simpan = 0;
    while (p >= 0 || q >= 0 || simpan > 0) {
        auto jumlah = simpan;
        if (p >= 0) {
            jumlah += a[p] - '0';
            p--;
        }
        if (q >= 0) {
            jumlah += b[q] - '0';
            q--;
        }
        hasil = (char) ('0' + jumlah % 10) + hasil;
        simpan = jumlah / 10;
    }
    cout << hasil << endl;''', "string"),
     id=("Jumlah Bilangan Besar", "Tipe `int` hanya muat sampai sekitar dua miliar, dan `long long` sampai sekitar 19 digit. Bagaimana menjumlahkan dua bilangan yang panjangnya 100 digit?\n\n"
         "Baca kedua bilangan sebagai `string`, lalu jumlahkan seperti di sekolah dasar: dari digit paling kanan, satu per satu, dengan angka simpanan.\n\nContoh: `999` + `1` = `1000`; `12345678901234567890` + `98765432109876543210` = `111111111011111111100`.",
         "Dua baris, masing-masing satu bilangan bulat tak negatif tanpa nol di depan.", "Satu baris: jumlah kedua bilangan.", "Tiap bilangan paling panjang 100 digit."),
     en=("Adding Big Numbers", "The type `int` holds up to about two billion, and `long long` up to about 19 digits. How can two numbers of 100 digits be added?\n\n"
         "Read both numbers as `string`, then add them the primary-school way: from the rightmost digit, one at a time, with a carry.\n\nExamples: `999` + `1` = `1000`; `12345678901234567890` + `98765432109876543210` = `111111111011111111100`.",
         "Two lines, each with one non-negative integer without leading zeros.", "One line: the sum of the two numbers.", "Each number has at most 100 digits."),
     ar=("جمع الأعداد الكبيرة", "النوع `int` يسع حتى نحو مليارين، و `long long` حتى نحو 19 رقمًا. فكيف يُجمع عددان من 100 رقم؟\n\n"
         "اقرأ العددين على أنهما `string` ثم اجمعهما بطريقة المدرسة الابتدائية: من الرقم الأيمن، رقمًا رقمًا، مع رقم محمول.\n\nأمثلة: `999` + `1` = `1000`؛ `12345678901234567890` + `98765432109876543210` = `111111111011111111100`.",
         "سطران في كل منهما عدد صحيح غير سالب بلا أصفار في أوله.", "سطر واحد: مجموع العددين.", "كل عدد 100 رقم على الأكثر."))

soal("segitiga-pascal", "sedang", "trimmed", "print({2: 1, 3: 20}.get(i, r.randint(2, 10) if i % 2 == 0 else r.randint(11, 19)))\n",
     cpp(r'''    int n;
    cin >> n;
    int baris[20]{};
    for (int b = 0; b < n; b++) {
        baris[b] = 1;
        for (int k = b - 1; k > 0; k--) {
            baris[k] = baris[k] + baris[k - 1];
        }
        for (int k = 0; k <= b; k++) {
            cout << baris[k] << " ";
        }
        cout << endl;
    }'''),
     id=("Segitiga Pascal", "Segitiga Pascal dimulai dengan 1 di puncak. Setiap baris berikutnya diawali dan diakhiri 1, dan setiap bilangan di antaranya adalah jumlah dua bilangan tepat di atasnya.\n\n"
         "Baca n, lalu cetak n baris pertama segitiga itu; bilangan dalam satu baris dipisah satu spasi, tanpa spasi di depan.\n\nUntuk n = 5:\n\n```\n1\n1 1\n1 2 1\n1 3 3 1\n1 4 6 4 1\n```",
         "Satu bilangan bulat n.", "n baris segitiga Pascal.", "1 ≤ n ≤ 20"),
     en=("Pascal's Triangle", "Pascal's triangle starts with 1 at the top. Every next row begins and ends with 1, and every number between is the sum of the two numbers right above it.\n\n"
         "Read n, then print the first n rows of the triangle; numbers in a row are separated by single spaces, with no spaces in front.\n\nFor n = 5:\n\n```\n1\n1 1\n1 2 1\n1 3 3 1\n1 4 6 4 1\n```",
         "One integer n.", "The first n rows of Pascal's triangle.", "1 ≤ n ≤ 20"),
     ar=("مثلث باسكال", "يبدأ مثلث باسكال بالعدد 1 في القمة. كل صف تالٍ يبدأ وينتهي بالعدد 1، وكل عدد بينهما هو مجموع العددين اللذين فوقه مباشرة.\n\n"
         "اقرأ n ثم اطبع الصفوف n الأولى من المثلث؛ تفصل بين أعداد الصف مسافة واحدة، وبلا مسافات في أوله.\n\nعند n = 5:\n\n```\n1\n1 1\n1 2 1\n1 3 3 1\n1 4 6 4 1\n```",
         "عدد صحيح واحد n.", "الصفوف n الأولى من مثلث باسكال.", "1 ≤ n ≤ 20"))

soal("anagram", "sedang", "trimmed",
     "n = r.randint(1, 30)\na = [r.choice('abcdefghijklmnopqrstuvwxyz' if i % 4 < 2 else 'abc') for _ in range(n)]\nb = a[:]\nr.shuffle(b)\nif i % 2 == 1:\n    p = r.randint(0, n - 1)\n    b[p] = 'z' if b[p] != 'z' else 'y'\nif i == 5:\n    b = b + ['a']\nif i == 2:\n    a, b = list('kasur'), list('rusak')\nprint(''.join(a), ''.join(b))\n",
     cpp(r'''    string a, b;
    cin >> a >> b;
    int selisih[26]{};
    for (auto c : a) {
        selisih[c - 'a']++;
    }
    for (auto c : b) {
        selisih[c - 'a']--;
    }
    auto sama = true;
    for (auto s : selisih) {
        if (s != 0) {
            sama = false;
        }
    }
    if (sama) {
        cout << "anagram" << endl;
    } else {
        cout << "bukan anagram" << endl;
    }''', "string"),
     id=("Anagram", "Dua kata disebut anagram bila huruf-huruf yang satu dapat disusun ulang menjadi kata yang lain: `kasur` dan `rusak`, `malam` dan `lamam`.\n\n"
         "Baca dua kata, lalu cetak `anagram` atau `bukan anagram`.\n\nGagasan: hitung kemunculan tiap huruf di kedua kata dengan array 26 penghitung; keduanya anagram bila semua hitungannya sama.\n\nContoh: `kasur rusak` → `anagram`; `buku kubus` → `bukan anagram`.",
         "Satu baris berisi dua kata dari huruf kecil.", "`anagram` atau `bukan anagram`.", "Panjang tiap kata 1 sampai 31 huruf."),
     en=("Anagram", "Two words are anagrams when the letters of one can be rearranged into the other: `kasur` and `rusak`, `malam` and `lamam`.\n\n"
         "Read two words, then print `anagram` or `bukan anagram` (not an anagram).\n\nIdea: count the occurrences of each letter in both words with an array of 26 counters; they are anagrams when all counts agree.\n\nExamples: `kasur rusak` → `anagram`; `buku kubus` → `bukan anagram`.",
         "One line with two words of lowercase letters.", "`anagram` or `bukan anagram`.", "Each word is 1 to 31 letters long."),
     ar=("الجناس التصحيفي", "تكون كلمتان جناسًا تصحيفيًا (anagram) إذا أمكن إعادة ترتيب حروف إحداهما لتصير الأخرى: `kasur` و `rusak`، `malam` و `lamam`.\n\n"
         "اقرأ كلمتين ثم اطبع `anagram` أو `bukan anagram` (ليست جناسًا).\n\nالفكرة: عُدَّ ظهور كل حرف في الكلمتين بمصفوفة من 26 عدّادًا؛ تكونان جناسًا إذا تطابقت كل الأعداد.\n\nأمثلة: `kasur rusak` ← `anagram`؛ `buku kubus` ← `bukan anagram`.",
         "سطر واحد فيه كلمتان من حروف لاتينية صغيرة.", "`anagram` أو `bukan anagram`.", "طول كل كلمة من 1 إلى 31 حرفًا."))

soal("antrean-bank", "sedang", "trimmed",
     NAMA + "m = r.randint(1, 30)\nnama = r.sample(NAMA, 30)\np = {2: 0.2, 3: 0.9}.get(i, 0.6)\nk = 0\nfor _ in range(m):\n    if r.random() < p:\n        print('datang', nama[k])\n        k += 1\n    else:\n        print('layani')\nprint('selesai')\n",
     program(r'''
struct Simpul {
    string nama;
    Simpul *berikut;
};''', r'''    Simpul *depan = nullptr;
    Simpul *belakang = nullptr;
    auto banyak = 0;
    string perintah;
    while (cin >> perintah) {
        if (perintah == "selesai") {
            break;
        }
        if (perintah == "datang") {
            auto *baru = new Simpul{"", nullptr};
            cin >> baru->nama;
            if (belakang == nullptr) {
                depan = baru;
            } else {
                belakang->berikut = baru;
            }
            belakang = baru;
            banyak++;
        } else if (depan == nullptr) {
            cout << "Antrean kosong" << endl;
        } else {
            cout << "Melayani " << depan->nama << endl;
            auto *lama = depan;
            depan = depan->berikut;
            if (depan == nullptr) {
                belakang = nullptr;
            }
            delete lama;
            banyak--;
        }
    }
    cout << "Sisa antrean: " << banyak << endl;
    while (depan != nullptr) {
        auto *lama = depan;
        depan = depan->berikut;
        delete lama;
    }''', "string"),
     id=("Antrean Bank", "Tirukan antrean di sebuah bank. Perintah datang satu per baris:\n\n"
         "- `datang <nama>` — seorang nasabah masuk ke ujung belakang antrean;\n- `layani` — nasabah terdepan dilayani dan keluar dari antrean;\n- `selesai` — tidak ada perintah lagi.\n\n"
         "Untuk tiap `layani`, cetak `Melayani <nama>`; bila antreannya kosong, cetak `Antrean kosong`. Di akhir, cetak `Sisa antrean: k` dengan k banyak nasabah yang belum dilayani.\n\n"
         "Contoh masukan:\n\n```\ndatang Budi\ndatang Citra\nlayani\nlayani\nlayani\ndatang Eko\nselesai\n```\n\nKeluarannya:\n\n```\nMelayani Budi\nMelayani Citra\nAntrean kosong\nSisa antrean: 1\n```\n\n"
         "Antrean cocok disimpan sebagai *linked list*: simpul dibuat dengan `new`, dihapus dengan `delete`, dan ujung daftar ditandai `nullptr`.",
         "Beberapa baris perintah, diakhiri `selesai`.", "Satu baris untuk tiap `layani`, lalu satu baris `Sisa antrean: k`.", "Paling banyak 30 perintah sebelum `selesai`; nama satu kata."),
     en=("Bank Queue", "Imitate the queue in a bank. Commands come one per line:\n\n"
         "- `datang <name>` (arrives) — a customer joins the back of the queue;\n- `layani` (serve) — the customer at the front is served and leaves the queue;\n- `selesai` (done) — no more commands.\n\n"
         "For every `layani` print `Melayani <name>`; when the queue is empty print `Antrean kosong`. At the end print `Sisa antrean: k`, where k is the number of customers not served yet.\n\n"
         "Example input:\n\n```\ndatang Budi\ndatang Citra\nlayani\nlayani\nlayani\ndatang Eko\nselesai\n```\n\nIts output:\n\n```\nMelayani Budi\nMelayani Citra\nAntrean kosong\nSisa antrean: 1\n```\n\n"
         "A queue is naturally kept as a *linked list*: nodes are made with `new`, removed with `delete`, and the end of the list is marked by `nullptr`.",
         "Several command lines, ending with `selesai`.", "One line for every `layani`, then one line `Sisa antrean: k`.", "At most 30 commands before `selesai`; names are one word."),
     ar=("طابور المصرف", "حاكِ الطابور في مصرف. تأتي الأوامر أمرًا في كل سطر:\n\n"
         "- `datang <الاسم>` (وصل) — ينضم عميل إلى آخر الطابور؛\n- `layani` (اخدم) — يُخدَم العميل الذي في المقدمة ويغادر الطابور؛\n- `selesai` (انتهى) — لا أوامر بعده.\n\n"
         "لكل `layani` اطبع `Melayani <الاسم>`؛ وإذا كان الطابور فارغًا فاطبع `Antrean kosong`. في النهاية اطبع `Sisa antrean: k` حيث k عدد العملاء الذين لم يُخدَموا بعد.\n\n"
         "مثال للمدخل:\n\n```\ndatang Budi\ndatang Citra\nlayani\nlayani\nlayani\ndatang Eko\nselesai\n```\n\nومخرجه:\n\n```\nMelayani Budi\nMelayani Citra\nAntrean kosong\nSisa antrean: 1\n```\n\n"
         "يناسب الطابورَ أن يُحفظ *قائمة مترابطة*: تُنشأ العقد بـ `new` وتُحذف بـ `delete` وتُعلَّم نهاية القائمة بـ `nullptr`.",
         "عدة أسطر أوامر تنتهي بـ `selesai`.", "سطر لكل `layani` ثم سطر `Sisa antrean: k`.", "30 أمرًا على الأكثر قبل `selesai`؛ الاسم كلمة واحدة."))

# ================================= SULIT (4) =================================

soal("spiral-matriks", "sulit", "trimmed",
     "b, k = r.randint(1, 10), r.randint(1, 10)\nif i == 2:\n    b, k = 1, r.randint(2, 10)\nif i == 3:\n    b, k = r.randint(2, 10), 1\nif i == 4:\n    b, k = 10, 10\nif i == 5:\n    b, k = 3, 4\nprint(b, k)\nfor _ in range(b):\n    print(*[r.randint(0, 99) for _ in range(k)])\n",
     cpp(r'''    int baris, kolom;
    cin >> baris >> kolom;
    int m[10][10]{};
    for (int b = 0; b < baris; b++) {
        for (int k = 0; k < kolom; k++) {
            cin >> m[b][k];
        }
    }
    auto atas = 0;
    auto bawah = baris - 1;
    auto kiri = 0;
    auto kanan = kolom - 1;
    while (atas <= bawah && kiri <= kanan) {
        for (int k = kiri; k <= kanan; k++) {
            cout << m[atas][k] << " ";
        }
        atas++;
        for (int b = atas; b <= bawah; b++) {
            cout << m[b][kanan] << " ";
        }
        kanan--;
        if (atas <= bawah) {
            for (int k = kanan; k >= kiri; k--) {
                cout << m[bawah][k] << " ";
            }
            bawah--;
        }
        if (kiri <= kanan) {
            for (int b = bawah; b >= atas; b--) {
                cout << m[b][kiri] << " ";
            }
            kiri++;
        }
    }
    cout << endl;'''),
     id=("Spiral Matriks", "Baca sebuah matriks, lalu cetak semua isinya **menurut jalur spiral**: mulai dari kiri atas, ke kanan sepanjang baris teratas, turun sepanjang kolom terkanan, ke kiri sepanjang baris terbawah, naik sepanjang kolom terkiri, lalu masuk ke lapisan dalam, dan seterusnya.\n\n"
         "Untuk matriks\n\n```\n1 2 3 4\n5 6 7 8\n9 10 11 12\n```\n\nkeluarannya `1 2 3 4 8 12 11 10 9 5 6 7`.\n\nHati-hati dengan matriks yang hanya punya satu baris atau satu kolom: tidak ada elemen yang boleh tercetak dua kali.",
         "Baris pertama: banyak baris dan banyak kolom. Lalu isi matriks, baris demi baris.", "Satu baris: semua elemen dalam urutan spiral.", "1 ≤ baris, kolom ≤ 10; tiap elemen antara 0 dan 99"),
     en=("Matrix Spiral", "Read a matrix, then print all its entries **along a spiral path**: start at the top left, go right along the top row, down the rightmost column, left along the bottom row, up the leftmost column, then into the inner layer, and so on.\n\n"
         "For the matrix\n\n```\n1 2 3 4\n5 6 7 8\n9 10 11 12\n```\n\nthe output is `1 2 3 4 8 12 11 10 9 5 6 7`.\n\nBe careful with a matrix of a single row or a single column: no entry may be printed twice.",
         "First line: the number of rows and the number of columns. Then the matrix, row by row.", "One line: all entries in spiral order.", "1 ≤ rows, columns ≤ 10; each entry is between 0 and 99"),
     ar=("المصفوفة الحلزونية", "اقرأ مصفوفة ثم اطبع كل عناصرها **على مسار حلزوني**: ابدأ من أعلى اليسار، ثم يمينًا على الصف العلوي، ثم نزولًا على العمود الأيمن، ثم يسارًا على الصف السفلي، ثم صعودًا على العمود الأيسر، ثم إلى الطبقة الداخلية، وهكذا.\n\n"
         "للمصفوفة\n\n```\n1 2 3 4\n5 6 7 8\n9 10 11 12\n```\n\nيكون المخرج `1 2 3 4 8 12 11 10 9 5 6 7`.\n\nانتبه إلى المصفوفة ذات الصف الواحد أو العمود الواحد: لا يجوز أن يُطبع عنصر مرتين.",
         "السطر الأول: عدد الصفوف وعدد الأعمدة. ثم المصفوفة صفًّا صفًّا.", "سطر واحد: كل العناصر بالترتيب الحلزوني.", "1 ≤ الصفوف، الأعمدة ≤ 10؛ كل عنصر بين 0 و 99"))

soal("permainan-hidup", "sulit", "trimmed",
     "b, k = r.randint(1, 20), r.randint(1, 20)\np = {2: 0.0, 3: 1.0}.get(i, r.choice([0.2, 0.35, 0.5]))\ng = [''.join('#' if r.random() < p else '.' for _ in range(k)) for _ in range(b)]\nif i == 4:\n    b, k, g = 3, 3, ['.#.', '.#.', '.#.']\nif i == 5:\n    b, k, g = 4, 4, ['....', '.##.', '.##.', '....']\nprint(b, k)\nprint('\\n'.join(g))\n",
     cpp(r'''    int baris, kolom;
    cin >> baris >> kolom;
    char lama[20][21]{};
    for (int b = 0; b < baris; b++) {
        cin >> lama[b];
    }
    auto hidup = [&lama, baris, kolom](int b, int k) {
        return b >= 0 && b < baris && k >= 0 && k < kolom && lama[b][k] == '#';
    };
    for (int b = 0; b < baris; b++) {
        for (int k = 0; k < kolom; k++) {
            auto tetangga = 0;
            for (int db = -1; db <= 1; db++) {
                for (int dk = -1; dk <= 1; dk++) {
                    if ((db != 0 || dk != 0) && hidup(b + db, k + dk)) {
                        tetangga++;
                    }
                }
            }
            if (tetangga == 3 || (tetangga == 2 && hidup(b, k))) {
                cout << "#";
            } else {
                cout << ".";
            }
        }
        cout << endl;
    }'''),
     id=("Permainan Kehidupan", "*Game of Life* dimainkan di papan berpetak. Tiap petak berisi sel **hidup** (`#`) atau **mati** (`.`). Setiap petak punya paling banyak delapan tetangga: petak-petak yang bersentuhan dengannya, termasuk secara diagonal. Petak di luar papan dianggap mati.\n\n"
         "Generasi berikutnya dihitung **serentak** untuk semua petak, dari keadaan sekarang:\n\n- sel hidup dengan 2 atau 3 tetangga hidup tetap hidup;\n- sel mati dengan tepat 3 tetangga hidup menjadi hidup;\n- semua sel lain mati.\n\n"
         "Baca sebuah papan, lalu cetak papan generasi berikutnya.\n\nContoh: tiga sel tegak\n\n```\n.#.\n.#.\n.#.\n```\n\nmenjadi tiga sel mendatar\n\n```\n...\n###\n...\n```\n\n"
         "Karena serentak, hasilnya tidak boleh ditulis ke papan yang sedang dibaca.",
         "Baris pertama: banyak baris dan banyak kolom. Lalu papan, satu baris teks per baris papan.", "Papan generasi berikutnya, dengan ukuran yang sama.", "1 ≤ baris, kolom ≤ 20"),
     en=("Game of Life", "The *Game of Life* is played on a board of squares. Each square holds a **live** cell (`#`) or a **dead** one (`.`). Every square has at most eight neighbours: the squares touching it, diagonals included. Squares outside the board count as dead.\n\n"
         "The next generation is computed **at once** for all squares, from the present state:\n\n- a live cell with 2 or 3 live neighbours stays alive;\n- a dead cell with exactly 3 live neighbours becomes alive;\n- every other cell is dead.\n\n"
         "Read a board, then print the board of the next generation.\n\nExample: three cells in a column\n\n```\n.#.\n.#.\n.#.\n```\n\nbecome three cells in a row\n\n```\n...\n###\n...\n```\n\n"
         "Because everything happens at once, the result must not be written into the board that is being read.",
         "First line: the number of rows and the number of columns. Then the board, one text line per row.", "The board of the next generation, of the same size.", "1 ≤ rows, columns ≤ 20"),
     ar=("لعبة الحياة", "تُلعب *لعبة الحياة* على رقعة من مربعات. في كل مربع خلية **حية** (`#`) أو **ميتة** (`.`). لكل مربع ثمانية جيران على الأكثر: المربعات الملاصقة له ومنها القطرية. المربعات خارج الرقعة تُعدّ ميتة.\n\n"
         "يُحسب الجيل التالي **دفعة واحدة** لكل المربعات من الحالة الراهنة:\n\n- الخلية الحية التي لها جاران حيّان أو ثلاثة تبقى حية؛\n- الخلية الميتة التي لها ثلاثة جيران أحياء بالضبط تصير حية؛\n- كل خلية أخرى ميتة.\n\n"
         "اقرأ رقعة ثم اطبع رقعة الجيل التالي.\n\nمثال: ثلاث خلايا في عمود\n\n```\n.#.\n.#.\n.#.\n```\n\nتصير ثلاث خلايا في صف\n\n```\n...\n###\n...\n```\n\n"
         "لأن كل شيء يحدث دفعة واحدة فلا يجوز كتابة الناتج في الرقعة التي تُقرأ.",
         "السطر الأول: عدد الصفوف وعدد الأعمدة. ثم الرقعة، سطر نصي لكل صف.", "رقعة الجيل التالي بالحجم نفسه.", "1 ≤ الصفوف، الأعمدة ≤ 20"))

soal("sudoku-sah", "sulit", "trimmed",
     "d = list(range(1, 10))\nr.shuffle(d)\ng = [[d[(3 * (b % 3) + b // 3 + k) % 9] for k in range(9)] for b in range(9)]\n"
     "ub = [x for blok in r.sample([0, 1, 2], 3) for x in r.sample([3 * blok, 3 * blok + 1, 3 * blok + 2], 3)]\nuk = [x for blok in r.sample([0, 1, 2], 3) for x in r.sample([3 * blok, 3 * blok + 1, 3 * blok + 2], 3)]\ng = [[g[b][k] for k in uk] for b in ub]\n"
     "j = i % 4\nif j == 1:\n    b, k = r.randint(0, 8), r.randint(0, 8)\n    g[b][k] = g[b][k] % 9 + 1\nelif j == 2:\n    b = r.randint(0, 8)\n    k1 = r.randint(0, 2)\n    k2 = r.randint(3, 8)\n    g[b][k1], g[b][k2] = g[b][k2], g[b][k1]\n"
     "elif j == 3:\n    b = 3 * r.randint(0, 2)\n    g[b], g[b + 1] = g[b + 1], g[b]\n    k = r.randint(0, 8)\n    b2 = (b + 3) % 9\n    k2 = (k + 3) % 9\n    v, w = g[b][k], g[b2][k2]\n    g[b][k], g[b2][k2] = w, v\n"
     "for baris in g:\n    print(*baris)\n",
     program(r'''
bool lengkap(const int hitung[10]) {
    for (int d = 1; d <= 9; d++) {
        if (hitung[d] != 1) {
            return false;
        }
    }
    return true;
}''', r'''    int g[9][9]{};
    for (auto &baris : g) {
        for (auto &sel : baris) {
            cin >> sel;
        }
    }
    auto sah = true;
    for (int a = 0; a < 9; a++) {
        int baris[10]{}, kolom[10]{}, kotak[10]{};
        for (int b = 0; b < 9; b++) {
            baris[g[a][b]]++;
            kolom[g[b][a]]++;
            kotak[g[a / 3 * 3 + b / 3][a % 3 * 3 + b % 3]]++;
        }
        if (!lengkap(baris) || !lengkap(kolom) || !lengkap(kotak)) {
            sah = false;
        }
    }
    if (sah) {
        cout << "sah" << endl;
    } else {
        cout << "tidak sah" << endl;
    }'''),
     id=("Sudoku Sah", "Sebuah papan sudoku yang sudah terisi penuh berukuran 9 × 9. Papan itu **sah** bila angka 1 sampai 9 masing-masing muncul tepat satu kali di:\n\n- setiap baris;\n- setiap kolom;\n- setiap kotak 3 × 3 (ada sembilan kotak, tidak saling tumpang-tindih).\n\n"
         "Baca papannya, lalu cetak `sah` atau `tidak sah`.\n\nKesalahan bisa sangat halus: ada papan yang semua baris dan kolomnya benar, tetapi salah satu kotaknya tidak.",
         "Sembilan baris, masing-masing berisi sembilan angka dipisah spasi.", "`sah` atau `tidak sah`.", "Tiap angka antara 1 dan 9."),
     en=("Valid Sudoku", "A completely filled sudoku board measures 9 × 9. The board is **valid** when each of the digits 1 to 9 occurs exactly once in:\n\n- every row;\n- every column;\n- every 3 × 3 box (there are nine boxes, not overlapping).\n\n"
         "Read the board, then print `sah` (valid) or `tidak sah` (not valid).\n\nA mistake can be very subtle: some boards have all rows and columns right, but one box wrong.",
         "Nine lines, each with nine digits separated by spaces.", "`sah` or `tidak sah`.", "Each digit is between 1 and 9."),
     ar=("سودوكو صحيحة", "رقعة سودوكو مملوءة تمامًا حجمها 9 × 9. تكون الرقعة **صحيحة** إذا ظهر كل رقم من 1 إلى 9 مرة واحدة بالضبط في:\n\n- كل صف؛\n- كل عمود؛\n- كل مربع 3 × 3 (المربعات تسعة غير متداخلة).\n\n"
         "اقرأ الرقعة ثم اطبع `sah` (صحيحة) أو `tidak sah` (غير صحيحة).\n\nقد يكون الخطأ دقيقًا جدًّا: بعض الرقع كل صفوفها وأعمدتها صحيحة لكن أحد مربعاتها خاطئ.",
         "تسعة أسطر في كل منها تسعة أرقام تفصل بينها مسافات.", "`sah` أو `tidak sah`.", "كل رقم بين 1 و 9."))

soal("ratu-pertama", "sulit", "trimmed",
     "n = {2: 1, 3: 9, 4: 2, 5: 3}.get(i, r.randint(4, 6) if i % 2 == 0 else r.randint(7, 9))\nprint(n, r.randint(1, n))\n",
     program(r'''
int hitung(int baris, int n, int kolomRatu[]) {
    if (baris == n) {
        return 1;
    }
    auto banyak = 0;
    for (int k = 0; k < n; k++) {
        auto aman = true;
        for (int b = 0; b < baris; b++) {
            auto selisih = kolomRatu[b] - k;
            if (selisih == 0 || selisih == baris - b || selisih == b - baris) {
                aman = false;
            }
        }
        if (aman) {
            kolomRatu[baris] = k;
            banyak += hitung(baris + 1, n, kolomRatu);
        }
    }
    return banyak;
}''', r'''    int n, kolom;
    cin >> n >> kolom;
    int kolomRatu[9]{};
    kolomRatu[0] = kolom - 1;
    cout << hitung(1, n, kolomRatu) << endl;'''),
     id=("Ratu Pertama Sudah Ditaruh", "Dalam teka-teki *n ratu*, n buah ratu catur harus ditaruh di papan n × n sehingga **tidak ada dua ratu yang saling menyerang**: tidak sebaris, tidak sekolom, dan tidak sediagonal.\n\n"
         "Ratu di baris pertama sudah ditaruh di kolom k. Dengan berapa cara ratu-ratu di baris lainnya dapat ditaruh?\n\n"
         "Contoh: untuk papan 4 × 4, ratu pertama di kolom 2 memberi 1 cara, sedangkan ratu pertama di kolom 1 memberi 0 cara. Untuk papan 8 × 8 dengan ratu pertama di kolom 1 ada 4 cara.\n\n"
         "Gagasan: taruh ratu baris demi baris dengan fungsi rekursif. Di tiap baris, coba setiap kolom; bila aman terhadap semua ratu di baris-baris sebelumnya, lanjutkan ke baris berikutnya. Kolom ratu tiap baris cukup dicatat dalam sebuah array.",
         "Satu baris berisi n dan k.", "Satu bilangan bulat: banyak cara.", "1 ≤ k ≤ n ≤ 9"),
     en=("The First Queen is Placed", "In the *n queens* puzzle, n chess queens must be put on an n × n board so that **no two queens attack each other**: not in the same row, the same column, or the same diagonal.\n\n"
         "The queen of the first row is already placed in column k. In how many ways can the queens of the other rows be placed?\n\n"
         "Examples: on a 4 × 4 board, a first queen in column 2 gives 1 way, while a first queen in column 1 gives 0 ways. On an 8 × 8 board with the first queen in column 1 there are 4 ways.\n\n"
         "Idea: place the queens row by row with a recursive function. In each row try every column; when it is safe against all queens in the earlier rows, go on to the next row. The column of each row's queen can be kept in an array.",
         "One line with n and k.", "One integer: the number of ways.", "1 ≤ k ≤ n ≤ 9"),
     ar=("الوزير الأول موضوع", "في لغز *n وزيرًا* يجب وضع n وزيرًا من الشطرنج على رقعة n × n بحيث **لا يهاجم وزيرٌ وزيرًا آخر**: لا في الصف نفسه ولا في العمود نفسه ولا على القطر نفسه.\n\n"
         "وزير الصف الأول موضوع أصلًا في العمود k. بكم طريقة يمكن وضع وزراء الصفوف الأخرى؟\n\n"
         "أمثلة: على رقعة 4 × 4 يعطي الوزير الأول في العمود 2 طريقة واحدة، ويعطي الوزير الأول في العمود 1 صفر طرق. وعلى رقعة 8 × 8 مع الوزير الأول في العمود 1 توجد 4 طرق.\n\n"
         "الفكرة: ضع الوزراء صفًّا صفًّا بدالة عودية. في كل صف جرِّب كل عمود؛ فإذا كان آمنًا من كل وزراء الصفوف السابقة فانتقل إلى الصف التالي. يكفي حفظ عمود وزير كل صف في مصفوفة.",
         "سطر واحد فيه n و k.", "عدد صحيح واحد: عدد الطرق.", "1 ≤ k ≤ n ≤ 9"))

tulis(__file__, "PBO301", 1, "cpp", {"mudah_sekali": 8, "mudah": 8, "mudah_sedang": 6, "sedang": 6, "sulit": 4})
