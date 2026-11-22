#!/usr/bin/env python3
"""Menyusun docs/soal/ddp101-pertemuan-6.json.

DDP101 pertemuan 6 — Fungsi dan Parameter Passing. Konsep yang diizinkan di
produksi: konsep pertemuan 1–5 ditambah FUNGSI dan PARAMETER_REF. `REFERENSI`
(variabel referensi `int &r = a;`) ada di docs/PETA-KONSEP.md tetapi TIDAK
dicentang di produksi untuk pertemuan ini; solusi referensi karena itu tidak
memakai variabel referensi, hanya parameter by-reference. Tidak ada array,
rekursi, overloading, atau parameter bawaan (dua yang terakhir baru diizinkan
di pertemuan 7). 32 soal: 8 / 8 / 6 / 6 / 4.

Silabus pertemuan 6: definisi dan deklarasi fungsi, parameter dan argumen,
nilai kembalian, pass by value dan pass by reference; contoh: fungsi
matematika dasar, menukar nilai dengan pass by reference. Function overloading
dan default arguments dari silabus pertemuan ini dipindah ke pertemuan 7.

Setiap pernyataan menyebut fungsi yang diminta. Kode mahasiswa tidak dibatasi
dan soal-soal ini tidak mensyaratkan konsep di kode mahasiswa: keluaran yang
benar tanpa fungsi tetap mendapat nilai penuh.

    python3 docs/soal/buat-ddp101-pertemuan-6.py
"""
from bangun import program, soal, tulis

NAMA = "NAMA = ['Andi', 'Budi', 'Citra', 'Dewi', 'Eko', 'Fajar', 'Gita', 'Hadi', 'Indah', 'Joko', 'Kartika', 'Lina', 'Made', 'Nanda', 'Putri', 'Rudi', 'Sari', 'Tono', 'Wulan', 'Yoga']\n"
KATA = "KATA = ['belajar', 'program', 'komputer', 'kampus', 'buku', 'meja', 'layar', 'kode', 'data', 'angka', 'huruf', 'kelas', 'tugas', 'ujian', 'pagi', 'malam', 'cepat', 'rapi', 'benar', 'coba']\n"
PRIMA = "def prima(n):\n    return n > 1 and all(n % d for d in range(2, int(n ** 0.5) + 1))\n"
ROMAWI = ("def romawi(n):\n    h = ''\n    for v, s in [(1000, 'M'), (900, 'CM'), (500, 'D'), (400, 'CD'), (100, 'C'), (90, 'XC'), (50, 'L'), (40, 'XL'), (10, 'X'), (9, 'IX'), (5, 'V'), (4, 'IV'), (1, 'I')]:\n"
          "        while n >= v:\n            h += s\n            n -= v\n    return h\n")

# ============================== MUDAH SEKALI (8) ==============================

soal("volume-kubus", "mudah_sekali", "trimmed", "print({2: 1, 3: 100}.get(i, r.randint(2, 50) if i % 2 == 0 else r.randint(51, 99)))\n",
     program(r'''
int volumeKubus(int sisi) {
    return sisi * sisi * sisi;
}''', r'''    int s;
    cin >> s;
    cout << volumeKubus(s) << endl;'''),
     id=("Volume Kubus", "Tulis fungsi `int volumeKubus(int sisi)` yang mengembalikan volume kubus dengan panjang sisi tertentu, yaitu sisi × sisi × sisi.\n\nDi `main`, baca panjang sisi, panggil fungsi itu, lalu cetak hasilnya.\n\nUntuk sisi 3 jawabannya 27.",
         "Satu bilangan bulat: panjang sisi.", "Satu bilangan bulat: volume kubus.", "1 ≤ sisi ≤ 100"),
     en=("Volume of a Cube", "Write a function `int volumeKubus(int sisi)` that returns the volume of a cube with the given side length, that is side × side × side.\n\nIn `main`, read the side length, call the function, then print the result.\n\nFor a side of 3 the answer is 27.",
         "One integer: the side length.", "One integer: the volume of the cube.", "1 ≤ side ≤ 100"),
     ar=("حجم المكعب", "اكتب دالة `int volumeKubus(int sisi)` تُرجع حجم مكعب طول ضلعه معلوم، أي الضلع × الضلع × الضلع.\n\nفي `main` اقرأ طول الضلع واستدعِ الدالة ثم اطبع الناتج.\n\nلضلع طوله 3 الجواب 27.",
         "عدد صحيح واحد: طول الضلع.", "عدد صحيح واحد: حجم المكعب.", "1 ≤ الضلع ≤ 100"))

soal("sapa-tiga-orang", "mudah_sekali", "trimmed", NAMA + "print(*r.sample(NAMA, 3))\n",
     program(r'''
void sapa(string nama) {
    cout << "Halo, " << nama << "!" << endl;
}''', r'''    string a, b, c;
    cin >> a >> b >> c;
    sapa(a);
    sapa(b);
    sapa(c);''', "string"),
     id=("Sapa Tiga Orang", "Tulis fungsi `void sapa(string nama)` yang mencetak satu baris sapaan: `Halo, ` diikuti nama dan tanda seru.\n\nDi `main`, baca tiga nama, lalu panggil `sapa` satu kali untuk tiap nama. Fungsi ditulis sekali, dipakai tiga kali.\n\nUntuk `Budi Citra Eko` keluarannya:\n\n```\nHalo, Budi!\nHalo, Citra!\nHalo, Eko!\n```",
         "Satu baris berisi tiga nama, masing-masing satu kata.", "Tiga baris sapaan, sesuai urutan nama.", "Tiap nama terdiri dari huruf saja, paling panjang 20 huruf."),
     en=("Greet Three People", "Write a function `void sapa(string nama)` that prints one greeting line: `Halo, ` followed by the name and an exclamation mark.\n\nIn `main`, read three names, then call `sapa` once for each name. The function is written once and used three times.\n\nFor `Budi Citra Eko` the output is:\n\n```\nHalo, Budi!\nHalo, Citra!\nHalo, Eko!\n```",
         "One line with three names, one word each.", "Three greeting lines, in the order of the names.", "Each name has letters only, at most 20 letters."),
     ar=("تحية ثلاثة أشخاص", "اكتب دالة `void sapa(string nama)` تطبع سطر تحية واحدًا: `Halo, ` يليه الاسم ثم علامة تعجب.\n\nفي `main` اقرأ ثلاثة أسماء ثم استدعِ `sapa` مرة لكل اسم. تُكتب الدالة مرة واحدة وتُستعمل ثلاث مرات.\n\nعند `Budi Citra Eko` يكون المخرج:\n\n```\nHalo, Budi!\nHalo, Citra!\nHalo, Eko!\n```",
         "سطر واحد فيه ثلاثة أسماء، كل اسم كلمة واحدة.", "ثلاثة أسطر تحية بترتيب الأسماء.", "كل اسم حروف لاتينية فقط، 20 حرفًا على الأكثر."))

soal("jarak-dua-bilangan", "mudah_sekali", "trimmed", "a, b = r.randint(-1000, 1000), r.randint(-1000, 1000)\nif i == 2:\n    b = a\nif i == 3:\n    a, b = max(a, b), min(a, b) - 1\nprint(a, b)\n",
     program(r'''
int jarak(int a, int b) {
    if (a > b) {
        return a - b;
    }
    return b - a;
}''', r'''    int a, b;
    cin >> a >> b;
    cout << jarak(a, b) << endl;'''),
     id=("Jarak Dua Bilangan", "Tulis fungsi `int jarak(int a, int b)` yang mengembalikan jarak dua bilangan pada garis bilangan. Jarak tidak pernah negatif: jarak 3 dan 10 sama dengan jarak 10 dan 3, yaitu 7.\n\nDi `main`, baca dua bilangan dan cetak jaraknya.\n\nContoh lain: jarak −4 dan 5 adalah 9.",
         "Satu baris berisi dua bilangan bulat a dan b.", "Satu bilangan bulat: jarak a dan b.", "−1001 ≤ a, b ≤ 1000"),
     en=("Distance between Two Numbers", "Write a function `int jarak(int a, int b)` that returns the distance between two numbers on the number line. A distance is never negative: the distance between 3 and 10 equals that between 10 and 3, namely 7.\n\nIn `main`, read two numbers and print their distance.\n\nAnother example: the distance between −4 and 5 is 9.",
         "One line with two integers a and b.", "One integer: the distance between a and b.", "−1001 ≤ a, b ≤ 1000"),
     ar=("المسافة بين عددين", "اكتب دالة `int jarak(int a, int b)` تُرجع المسافة بين عددين على خط الأعداد. المسافة لا تكون سالبة أبدًا: المسافة بين 3 و 10 تساوي المسافة بين 10 و 3، أي 7.\n\nفي `main` اقرأ عددين واطبع المسافة بينهما.\n\nمثال آخر: المسافة بين −4 و 5 هي 9.",
         "سطر واحد فيه عددان صحيحان a و b.", "عدد صحيح واحد: المسافة بين a و b.", "−1001 ≤ a, b ≤ 1000"))

soal("tiga-garis", "mudah_sekali", "trimmed", "print(r.randint(1, 30), r.randint(1, 30), r.randint(1, 30))\n",
     program(r'''
void garis(int n) {
    for (int k = 0; k < n; k++) {
        cout << "-";
    }
    cout << endl;
}''', r'''    int a, b, c;
    cin >> a >> b >> c;
    garis(a);
    garis(b);
    garis(c);'''),
     id=("Tiga Garis", "Tulis fungsi `void garis(int n)` yang mencetak satu baris berisi n tanda hubung (`-`).\n\nDi `main`, baca tiga bilangan, lalu panggil `garis` untuk masing-masing.\n\nUntuk `5 2 7` keluarannya:\n\n```\n-----\n--\n-------\n```",
         "Satu baris berisi tiga bilangan bulat.", "Tiga baris tanda hubung dengan panjang sesuai masukan.", "Tiap bilangan antara 1 dan 30."),
     en=("Three Lines", "Write a function `void garis(int n)` that prints one line of n hyphens (`-`).\n\nIn `main`, read three numbers, then call `garis` for each of them.\n\nFor `5 2 7` the output is:\n\n```\n-----\n--\n-------\n```",
         "One line with three integers.", "Three lines of hyphens with the lengths given.", "Each number is between 1 and 30."),
     ar=("ثلاثة خطوط", "اكتب دالة `void garis(int n)` تطبع سطرًا واحدًا فيه n شرطة (`-`).\n\nفي `main` اقرأ ثلاثة أعداد ثم استدعِ `garis` لكل منها.\n\nعند `5 2 7` يكون المخرج:\n\n```\n-----\n--\n-------\n```",
         "سطر واحد فيه ثلاثة أعداد صحيحة.", "ثلاثة أسطر من الشرطات بالأطوال المعطاة.", "كل عدد بين 1 و 30."))

soal("tukar-dua-bilangan", "mudah_sekali", "trimmed", "a, b = r.randint(-1000, 1000), r.randint(-1000, 1000)\nif i == 2:\n    b = a\nprint(a, b)\n",
     program(r'''
void tukar(int &a, int &b) {
    int sementara = a;
    a = b;
    b = sementara;
}''', r'''    int x, y;
    cin >> x >> y;
    tukar(x, y);
    cout << x << " " << y << endl;'''),
     id=("Tukar Dua Bilangan", "Tulis fungsi `void tukar(int &a, int &b)` yang menukar isi dua variabel.\n\nTanda `&` membuat parameter itu **merujuk ke variabel pemanggil** (pass by reference), sehingga perubahan di dalam fungsi terlihat di `main`. Tanpa `&`, fungsi hanya menukar salinannya dan `main` tidak berubah apa-apa.\n\n"
         "Di `main`, baca `x` dan `y`, panggil `tukar(x, y)`, lalu cetak `x` dan `y`.\n\nUntuk `3 8` keluarannya `8 3`.",
         "Satu baris berisi dua bilangan bulat x dan y.", "Satu baris: x dan y setelah ditukar.", "−1000 ≤ x, y ≤ 1000"),
     en=("Swap Two Numbers", "Write a function `void tukar(int &a, int &b)` that swaps the contents of two variables.\n\nThe `&` makes the parameter **refer to the caller's variable** (pass by reference), so a change inside the function is seen in `main`. Without `&` the function only swaps its copies and nothing changes in `main`.\n\n"
         "In `main`, read `x` and `y`, call `tukar(x, y)`, then print `x` and `y`.\n\nFor `3 8` the output is `8 3`.",
         "One line with two integers x and y.", "One line: x and y after the swap.", "−1000 ≤ x, y ≤ 1000"),
     ar=("تبديل عددين", "اكتب دالة `void tukar(int &a, int &b)` تبدّل محتوى متغيرين.\n\nالعلامة `&` تجعل الوسيط **يشير إلى متغير المستدعي** (التمرير بالمرجع)، فيظهر التغيير الذي يقع داخل الدالة في `main`. بلا `&` تبدّل الدالة نسختيها فقط ولا يتغير شيء في `main`.\n\n"
         "في `main` اقرأ `x` و `y` واستدعِ `tukar(x, y)` ثم اطبع `x` و `y`.\n\nعند `3 8` يكون المخرج `8 3`.",
         "سطر واحد فيه عددان صحيحان x و y.", "سطر واحد: x و y بعد التبديل.", "−1000 ≤ x, y ≤ 1000"))

soal("gandakan", "mudah_sekali", "trimmed", "x, k = r.randint(1, 1000), r.randint(1, 20)\nif i == 2:\n    k = 0\nif i == 3:\n    x, k = 1000, 20\nprint(x, k)\n",
     program(r'''
void gandakan(int &x) {
    x = x * 2;
}''', r'''    int x, k;
    cin >> x >> k;
    for (int n = 0; n < k; n++) {
        gandakan(x);
    }
    cout << x << endl;'''),
     id=("Gandakan", "Tulis fungsi `void gandakan(int &x)` yang membuat isi variabel yang diberikan menjadi dua kali lipat. Fungsi ini tidak mengembalikan apa-apa: ia mengubah variabel pemanggil lewat parameter by-reference.\n\n"
         "Di `main`, baca x dan k, panggil `gandakan(x)` sebanyak k kali, lalu cetak x.\n\nUntuk `3 4` jawabannya 48 (3 → 6 → 12 → 24 → 48). Bila k = 0, x tidak berubah.",
         "Satu baris berisi x dan k.", "Nilai x setelah digandakan k kali.", "1 ≤ x ≤ 1000; 0 ≤ k ≤ 20"),
     en=("Double It", "Write a function `void gandakan(int &x)` that doubles the content of the variable it is given. It returns nothing: it changes the caller's variable through a by-reference parameter.\n\n"
         "In `main`, read x and k, call `gandakan(x)` k times, then print x.\n\nFor `3 4` the answer is 48 (3 → 6 → 12 → 24 → 48). When k = 0, x does not change.",
         "One line with x and k.", "The value of x after doubling it k times.", "1 ≤ x ≤ 1000; 0 ≤ k ≤ 20"),
     ar=("ضاعِف", "اكتب دالة `void gandakan(int &x)` تضاعف محتوى المتغير المعطى لها. لا تُرجع شيئًا: هي تغيّر متغير المستدعي عبر وسيط بالمرجع.\n\n"
         "في `main` اقرأ x و k واستدعِ `gandakan(x)` k مرة ثم اطبع x.\n\nعند `3 4` الجواب 48 (3 ← 6 ← 12 ← 24 ← 48). إذا كان k = 0 فلا يتغير x.",
         "سطر واحد فيه x و k.", "قيمة x بعد مضاعفته k مرة.", "1 ≤ x ≤ 1000؛ 0 ≤ k ≤ 20"))

soal("bandingkan-luas", "mudah_sekali", "trimmed",
     "p, l, q, m = [r.randint(1, 100) for _ in range(4)]\nk = i % 3\nif k == 2:\n    q, m = (l, p) if r.random() < 0.5 else (p, l)\nprint(p, l)\nprint(q, m)\n",
     program(r'''
int luas(int panjang, int lebar) {
    return panjang * lebar;
}''', r'''    int p1, l1, p2, l2;
    cin >> p1 >> l1;
    cin >> p2 >> l2;
    if (luas(p1, l1) > luas(p2, l2)) {
        cout << "pertama" << endl;
    } else if (luas(p1, l1) < luas(p2, l2)) {
        cout << "kedua" << endl;
    } else {
        cout << "sama" << endl;
    }'''),
     id=("Bandingkan Luas", "Tulis fungsi `int luas(int panjang, int lebar)` yang mengembalikan luas persegi panjang.\n\nDi `main`, baca ukuran dua persegi panjang. Dengan memanggil `luas` untuk masing-masing, cetak `pertama` bila yang pertama lebih luas, `kedua` bila yang kedua lebih luas, dan `sama` bila luasnya sama.\n\nContoh: persegi panjang 4 × 5 dan 3 × 6 → `pertama` (20 lawan 18).",
         "Baris pertama: panjang dan lebar persegi panjang pertama. Baris kedua: panjang dan lebar persegi panjang kedua.", "`pertama`, `kedua`, atau `sama`.", "Semua ukuran antara 1 dan 100."),
     en=("Compare Areas", "Write a function `int luas(int panjang, int lebar)` that returns the area of a rectangle.\n\nIn `main`, read the sizes of two rectangles. By calling `luas` for each, print `pertama` (first) if the first is larger, `kedua` (second) if the second is larger, and `sama` (same) if the areas are equal.\n\nExample: rectangles 4 × 5 and 3 × 6 → `pertama` (20 against 18).",
         "First line: length and width of the first rectangle. Second line: length and width of the second.", "`pertama`, `kedua` or `sama`.", "All sizes are between 1 and 100."),
     ar=("قارن المساحتين", "اكتب دالة `int luas(int panjang, int lebar)` تُرجع مساحة مستطيل.\n\nفي `main` اقرأ أبعاد مستطيلين. باستدعاء `luas` لكل منهما اطبع `pertama` (الأول) إذا كان الأول أوسع، و `kedua` (الثاني) إذا كان الثاني أوسع، و `sama` (متساويان) إذا تساوت المساحتان.\n\nمثال: المستطيلان 4 × 5 و 3 × 6 ← `pertama` (20 مقابل 18).",
         "السطر الأول: طول المستطيل الأول وعرضه. السطر الثاني: طول المستطيل الثاني وعرضه.", "`pertama` أو `kedua` أو `sama`.", "كل الأبعاد بين 1 و 100."))

soal("jumlah-kuadrat", "mudah_sekali", "trimmed", "print(r.randint(-100, 100), r.randint(-100, 100), r.randint(-100, 100))\n",
     program(r'''
int kuadrat(int x) {
    return x * x;
}''', r'''    int a, b, c;
    cin >> a >> b >> c;
    cout << kuadrat(a) + kuadrat(b) + kuadrat(c) << endl;'''),
     id=("Jumlah Kuadrat", "Tulis fungsi `int kuadrat(int x)` yang mengembalikan x dikali x.\n\nDi `main`, baca tiga bilangan, lalu cetak jumlah kuadrat ketiganya dengan memanggil `kuadrat` tiga kali. Nilai kembalian fungsi bisa langsung dipakai di dalam hitungan.\n\nUntuk `1 -2 3` jawabannya 1 + 4 + 9 = 14.",
         "Satu baris berisi tiga bilangan bulat.", "Satu bilangan bulat: jumlah kuadrat ketiganya.", "Tiap bilangan antara −100 dan 100."),
     en=("Sum of Squares", "Write a function `int kuadrat(int x)` that returns x times x.\n\nIn `main`, read three numbers, then print the sum of their squares by calling `kuadrat` three times. A function's return value can be used directly inside a calculation.\n\nFor `1 -2 3` the answer is 1 + 4 + 9 = 14.",
         "One line with three integers.", "One integer: the sum of the three squares.", "Each number is between −100 and 100."),
     ar=("مجموع المربعات", "اكتب دالة `int kuadrat(int x)` تُرجع x مضروبًا في x.\n\nفي `main` اقرأ ثلاثة أعداد ثم اطبع مجموع مربعاتها باستدعاء `kuadrat` ثلاث مرات. يمكن استعمال القيمة التي تُرجعها الدالة مباشرة داخل الحساب.\n\nعند `1 -2 3` الجواب 1 + 4 + 9 = 14.",
         "سطر واحد فيه ثلاثة أعداد صحيحة.", "عدد صحيح واحد: مجموع المربعات الثلاثة.", "كل عدد بين −100 و 100."))

# ================================= MUDAH (8) =================================

soal("selisih-dua-waktu", "mudah", "trimmed",
     "a = r.randint(0, 86399)\nb = r.randint(a, 86399)\nif i == 2:\n    b = a\nif i == 3:\n    a, b = 0, 86399\nprint(a // 3600, a // 60 % 60, a % 60)\nprint(b // 3600, b // 60 % 60, b % 60)\n",
     program(r'''
int keDetik(int jam, int menit, int detik) {
    return jam * 3600 + menit * 60 + detik;
}''', r'''    int j1, m1, d1, j2, m2, d2;
    cin >> j1 >> m1 >> d1;
    cin >> j2 >> m2 >> d2;
    cout << keDetik(j2, m2, d2) - keDetik(j1, m1, d1) << endl;'''),
     id=("Selisih Dua Waktu", "Tulis fungsi `int keDetik(int jam, int menit, int detik)` yang mengubah sebuah waktu menjadi banyak detik sejak tengah malam.\n\n"
         "Di `main`, baca dua waktu pada hari yang sama (waktu kedua tidak lebih awal), lalu cetak selisihnya dalam detik. Dengan fungsi itu, hitungan yang sama tidak perlu ditulis dua kali.\n\nContoh: dari 8:15:30 sampai 10:00:00 ada 6270 detik.",
         "Baris pertama: jam, menit, detik waktu pertama. Baris kedua: jam, menit, detik waktu kedua.", "Satu bilangan bulat: selisih dalam detik.", "0 ≤ jam ≤ 23; 0 ≤ menit, detik ≤ 59"),
     en=("Difference of Two Times", "Write a function `int keDetik(int jam, int menit, int detik)` that turns a time of day into the number of seconds since midnight.\n\n"
         "In `main`, read two times on the same day (the second is not earlier), then print the difference in seconds. With the function the same calculation need not be written twice.\n\nExample: from 8:15:30 to 10:00:00 there are 6270 seconds.",
         "First line: hours, minutes, seconds of the first time. Second line: hours, minutes, seconds of the second time.", "One integer: the difference in seconds.", "0 ≤ hours ≤ 23; 0 ≤ minutes, seconds ≤ 59"),
     ar=("الفرق بين وقتين", "اكتب دالة `int keDetik(int jam, int menit, int detik)` تحوّل وقتًا من اليوم إلى عدد الثواني منذ منتصف الليل.\n\n"
         "في `main` اقرأ وقتين في اليوم نفسه (الثاني ليس قبل الأول) ثم اطبع الفرق بالثواني. بهذه الدالة لا يُكتب الحساب نفسه مرتين.\n\nمثال: من 8:15:30 إلى 10:00:00 توجد 6270 ثانية.",
         "السطر الأول: ساعات ودقائق وثواني الوقت الأول. السطر الثاني: ساعات ودقائق وثواني الوقت الثاني.", "عدد صحيح واحد: الفرق بالثواني.", "0 ≤ الساعات ≤ 23؛ 0 ≤ الدقائق، الثواني ≤ 59"))

soal("hitung-kabisat", "mudah", "trimmed",
     "a = r.randint(1600, 2400)\nb = r.randint(a, 2400)\nif i == 2:\n    a, b = 1896, 1904\nif i == 3:\n    a = b = r.choice([1900, 2000, 2023, 2024])\nif i == 4:\n    a, b = 1600, 2400\nprint(a, b)\n",
     program(r'''
bool kabisat(int tahun) {
    return tahun % 400 == 0 || (tahun % 4 == 0 && tahun % 100 != 0);
}''', r'''    int a, b;
    cin >> a >> b;
    int banyak = 0;
    for (int t = a; t <= b; t++) {
        if (kabisat(t)) {
            banyak++;
        }
    }
    cout << banyak << endl;'''),
     id=("Hitung Tahun Kabisat", "Tulis fungsi `bool kabisat(int tahun)` yang mengembalikan `true` bila tahun itu kabisat: habis dibagi 400, atau habis dibagi 4 tetapi tidak habis dibagi 100.\n\n"
         "Di `main`, baca dua tahun a dan b, lalu cetak berapa banyak tahun kabisat dari a sampai b (keduanya ikut dihitung).\n\nContoh: dari 2000 sampai 2024 ada 7 tahun kabisat. Dari 1896 sampai 1904 ada 2, karena 1900 bukan kabisat.",
         "Satu baris berisi a dan b.", "Satu bilangan bulat: banyak tahun kabisat.", "1600 ≤ a ≤ b ≤ 2400"),
     en=("Count the Leap Years", "Write a function `bool kabisat(int tahun)` that returns `true` when the year is a leap year: divisible by 400, or divisible by 4 but not by 100.\n\n"
         "In `main`, read two years a and b, then print how many leap years there are from a to b (both included).\n\nExample: from 2000 to 2024 there are 7 leap years. From 1896 to 1904 there are 2, because 1900 is not a leap year.",
         "One line with a and b.", "One integer: the number of leap years.", "1600 ≤ a ≤ b ≤ 2400"),
     ar=("عدّ السنوات الكبيسة", "اكتب دالة `bool kabisat(int tahun)` تُرجع `true` إذا كانت السنة كبيسة: تقبل القسمة على 400، أو تقبل القسمة على 4 ولا تقبلها على 100.\n\n"
         "في `main` اقرأ سنتين a و b ثم اطبع عدد السنوات الكبيسة من a إلى b (مع احتساب الطرفين).\n\nمثال: من 2000 إلى 2024 توجد 7 سنوات كبيسة. ومن 1896 إلى 1904 توجد سنتان، لأن 1900 ليست كبيسة.",
         "سطر واحد فيه a و b.", "عدد صحيح واحد: عدد السنوات الكبيسة.", "1600 ≤ a ≤ b ≤ 2400"))

soal("rentang-tiga", "mudah", "trimmed", "v = [r.randint(-1000, 1000) for _ in range(3)]\nif i == 2:\n    v = [v[0]] * 3\nif i == 3:\n    v[1] = v[0]\nprint(*v)\n",
     program(r'''
void minMaks(int a, int b, int c, int &kecil, int &besar) {
    kecil = a;
    besar = a;
    if (b < kecil) kecil = b;
    if (c < kecil) kecil = c;
    if (b > besar) besar = b;
    if (c > besar) besar = c;
}''', r'''    int a, b, c, kecil, besar;
    cin >> a >> b >> c;
    minMaks(a, b, c, kecil, besar);
    cout << kecil << " " << besar << " " << besar - kecil << endl;'''),
     id=("Terkecil, Terbesar, Rentang", "Sebuah fungsi hanya bisa mengembalikan satu nilai dengan `return`. Untuk menghasilkan **dua** nilai sekaligus, hasilnya bisa dititipkan pada parameter by-reference.\n\n"
         "Tulis fungsi `void minMaks(int a, int b, int c, int &kecil, int &besar)` yang mengisi `kecil` dengan bilangan terkecil dan `besar` dengan bilangan terbesar di antara a, b, c.\n\n"
         "Di `main`, baca tiga bilangan, panggil fungsi itu, lalu cetak yang terkecil, yang terbesar, dan selisih keduanya.\n\nUntuk `8 -1 5` keluarannya `-1 8 9`.",
         "Satu baris berisi tiga bilangan bulat.", "Satu baris: terkecil, terbesar, selisihnya.", "Tiap bilangan antara −1000 dan 1000."),
     en=("Smallest, Largest, Range", "A function can return only one value with `return`. To produce **two** values at once, the results can be handed back through by-reference parameters.\n\n"
         "Write a function `void minMaks(int a, int b, int c, int &kecil, int &besar)` that puts the smallest of a, b, c into `kecil` and the largest into `besar`.\n\n"
         "In `main`, read three numbers, call the function, then print the smallest, the largest and their difference.\n\nFor `8 -1 5` the output is `-1 8 9`.",
         "One line with three integers.", "One line: the smallest, the largest, their difference.", "Each number is between −1000 and 1000."),
     ar=("الأصغر والأكبر والمدى", "لا تُرجع الدالة بـ `return` إلا قيمة واحدة. لإنتاج **قيمتين** معًا يمكن إعادة النتائج عبر وسائط بالمرجع.\n\n"
         "اكتب دالة `void minMaks(int a, int b, int c, int &kecil, int &besar)` تضع أصغر الأعداد a و b و c في `kecil` وأكبرها في `besar`.\n\n"
         "في `main` اقرأ ثلاثة أعداد واستدعِ الدالة ثم اطبع الأصغر والأكبر والفرق بينهما.\n\nعند `8 -1 5` يكون المخرج `-1 8 9`.",
         "سطر واحد فيه ثلاثة أعداد صحيحة.", "سطر واحد: الأصغر ثم الأكبر ثم الفرق بينهما.", "كل عدد بين −1000 و 1000."))

soal("banyak-faktor", "mudah", "trimmed", "print({2: 1, 3: 10000, 4: r.choice([36, 49, 144, 9801]), 5: r.choice([7919, 9973, 97]), 6: 7560}.get(i, r.randint(2, 10000)))\n",
     program(r'''
int banyakFaktor(int n) {
    int banyak = 0;
    for (int k = 1; k <= n; k++) {
        if (n % k == 0) {
            banyak++;
        }
    }
    return banyak;
}''', r'''    int n;
    cin >> n;
    cout << banyakFaktor(n) << endl;'''),
     id=("Banyak Faktor", "Faktor sebuah bilangan adalah bilangan bulat positif yang habis membaginya. Faktor dari 12 adalah 1, 2, 3, 4, 6, 12 — ada 6.\n\nTulis fungsi `int banyakFaktor(int n)` yang mengembalikan banyak faktor n. Di `main`, baca n dan cetak hasilnya.\n\nContoh lain: 1 punya 1 faktor; 7 punya 2 faktor.",
         "Satu bilangan bulat n.", "Satu bilangan bulat: banyak faktor n.", "1 ≤ n ≤ 10000"),
     en=("Number of Divisors", "A divisor of a number is a positive integer that divides it exactly. The divisors of 12 are 1, 2, 3, 4, 6, 12 — six of them.\n\nWrite a function `int banyakFaktor(int n)` that returns how many divisors n has. In `main`, read n and print the result.\n\nMore examples: 1 has 1 divisor; 7 has 2.",
         "One integer n.", "One integer: the number of divisors of n.", "1 ≤ n ≤ 10000"),
     ar=("عدد القواسم", "قاسم العدد هو عدد صحيح موجب يقسمه بلا باقٍ. قواسم 12 هي 1، 2، 3، 4، 6، 12 — وعددها 6.\n\nاكتب دالة `int banyakFaktor(int n)` تُرجع عدد قواسم n. في `main` اقرأ n واطبع الناتج.\n\nأمثلة أخرى: للعدد 1 قاسم واحد، وللعدد 7 قاسمان.",
         "عدد صحيح واحد n.", "عدد صحيح واحد: عدد قواسم n.", "1 ≤ n ≤ 10000"))

soal("bingkai", "mudah", "trimmed", "l, t = r.randint(2, 30), r.randint(2, 10)\nif i == 2:\n    l, t = 2, 2\nif i == 3:\n    t = 2\nif i == 4:\n    l = 2\nprint(l, t)\n",
     program(r'''
void ulang(char c, int n) {
    for (int k = 0; k < n; k++) {
        cout << c;
    }
}''', r'''    int lebar, tinggi;
    cin >> lebar >> tinggi;
    ulang('#', lebar);
    cout << endl;
    for (int b = 0; b < tinggi - 2; b++) {
        cout << "#";
        ulang(' ', lebar - 2);
        cout << "#" << endl;
    }
    ulang('#', lebar);
    cout << endl;'''),
     id=("Bingkai", "Tulis fungsi `void ulang(char c, int n)` yang mencetak karakter c sebanyak n kali **tanpa** pindah baris.\n\n"
         "Dengan fungsi itu, cetak sebuah bingkai dari tanda `#`: baris teratas dan terbawah penuh, baris-baris di antaranya hanya berisi `#` di ujung kiri dan kanan dengan spasi di tengah.\n\nUntuk lebar 6 dan tinggi 4:\n\n```\n######\n#    #\n#    #\n######\n```",
         "Satu baris berisi lebar dan tinggi bingkai.", "Bingkai setinggi `tinggi` baris, tiap baris selebar `lebar` karakter.", "2 ≤ lebar ≤ 30; 2 ≤ tinggi ≤ 10"),
     en=("Frame", "Write a function `void ulang(char c, int n)` that prints the character c n times **without** a line break.\n\n"
         "With that function, print a frame of `#`: the top and bottom lines are full, the lines between them have `#` only at the left and right ends with spaces in the middle.\n\nFor width 6 and height 4:\n\n```\n######\n#    #\n#    #\n######\n```",
         "One line with the width and the height of the frame.", "A frame `height` lines high, each line `width` characters wide.", "2 ≤ width ≤ 30; 2 ≤ height ≤ 10"),
     ar=("إطار", "اكتب دالة `void ulang(char c, int n)` تطبع المحرف c عدد n من المرات **دون** الانتقال إلى سطر جديد.\n\n"
         "بهذه الدالة اطبع إطارًا من `#`: السطر الأعلى والسطر الأسفل ممتلئان، والأسطر بينهما فيها `#` في الطرفين الأيسر والأيمن فقط وبينهما مسافات.\n\nللعرض 6 والارتفاع 4:\n\n```\n######\n#    #\n#    #\n######\n```",
         "سطر واحد فيه عرض الإطار وارتفاعه.", "إطار ارتفاعه بعدد الأسطر المعطى وعرض كل سطر بعدد المحارف المعطى.", "2 ≤ العرض ≤ 30؛ 2 ≤ الارتفاع ≤ 10"))

soal("digit-terbesar", "mudah", "trimmed", "print({2: 0, 3: 1000000000, 4: r.randint(1, 9)}.get(i, r.randint(10, 10 ** r.randint(2, 9))))\n",
     program(r'''
int digitTerbesar(int n) {
    int besar = 0;
    while (n > 0) {
        if (n % 10 > besar) {
            besar = n % 10;
        }
        n /= 10;
    }
    return besar;
}''', r'''    int n;
    cin >> n;
    cout << digitTerbesar(n) << endl;'''),
     id=("Digit Terbesar", "Tulis fungsi `int digitTerbesar(int n)` yang mengembalikan digit terbesar pada bilangan n. Di `main`, baca n dan cetak hasilnya.\n\nUntuk 40726 jawabannya 7.\n\nParameter `n` adalah salinan (pass by value): fungsi boleh mengubahnya sesuka hati tanpa mengganggu variabel di `main`.",
         "Satu bilangan bulat n.", "Satu digit: digit terbesar n.", "0 ≤ n ≤ 1000000000"),
     en=("Largest Digit", "Write a function `int digitTerbesar(int n)` that returns the largest digit of the number n. In `main`, read n and print the result.\n\nFor 40726 the answer is 7.\n\nThe parameter `n` is a copy (pass by value): the function may change it freely without disturbing the variable in `main`.",
         "One integer n.", "One digit: the largest digit of n.", "0 ≤ n ≤ 1000000000"),
     ar=("أكبر رقم", "اكتب دالة `int digitTerbesar(int n)` تُرجع أكبر رقم في العدد n. في `main` اقرأ n واطبع الناتج.\n\nللعدد 40726 الجواب 7.\n\nالوسيط `n` نسخة (تمرير بالقيمة): للدالة أن تغيّره كما تشاء دون أن تمسّ المتغير في `main`.",
         "عدد صحيح واحد n.", "رقم واحد: أكبر أرقام n.", "0 ≤ n ≤ 1000000000"))

soal("poin-pertandingan", "mudah", "trimmed", "n = r.randint(1, 20)\nprint(n)\nfor _ in range(n):\n    print(r.randint(0, 4), r.randint(0, 4))\n",
     program(r'''
int poin(int golKita, int golLawan) {
    if (golKita > golLawan) {
        return 3;
    }
    if (golKita == golLawan) {
        return 1;
    }
    return 0;
}''', r'''    int n, a, b, total = 0;
    cin >> n;
    for (int k = 0; k < n; k++) {
        cin >> a >> b;
        total += poin(a, b);
    }
    cout << total << endl;'''),
     id=("Poin Pertandingan", "Di liga sepak bola, menang bernilai 3 poin, seri 1 poin, dan kalah 0 poin.\n\nTulis fungsi `int poin(int golKita, int golLawan)` yang mengembalikan poin dari satu pertandingan. Di `main`, baca hasil n pertandingan sebuah tim, lalu cetak jumlah poinnya.\n\n"
         "Contoh: tiga pertandingan dengan skor 2–1, 0–0, dan 1–3 memberi 3 + 1 + 0 = 4 poin.",
         "Baris pertama: n. Lalu n baris, masing-masing berisi gol tim itu dan gol lawannya.", "Satu bilangan bulat: jumlah poin.", "1 ≤ n ≤ 20; tiap gol antara 0 dan 4"),
     en=("Match Points", "In a football league a win is worth 3 points, a draw 1 point and a loss 0 points.\n\nWrite a function `int poin(int golKita, int golLawan)` that returns the points from one match (our goals, their goals). In `main`, read the results of a team's n matches, then print its total points.\n\n"
         "Example: three matches with scores 2–1, 0–0 and 1–3 give 3 + 1 + 0 = 4 points.",
         "First line: n. Then n lines, each with the team's goals and the opponent's goals.", "One integer: the total points.", "1 ≤ n ≤ 20; each goal count is between 0 and 4"),
     ar=("نقاط المباريات", "في دوري كرة القدم يُحتسب للفوز 3 نقاط وللتعادل نقطة واحدة وللخسارة 0.\n\nاكتب دالة `int poin(int golKita, int golLawan)` تُرجع نقاط مباراة واحدة (أهدافنا، أهداف الخصم). في `main` اقرأ نتائج n مباراة لفريق ثم اطبع مجموع نقاطه.\n\n"
         "مثال: ثلاث مباريات نتائجها 2–1 و 0–0 و 1–3 تعطي 3 + 1 + 0 = 4 نقاط.",
         "السطر الأول: n. ثم n سطرًا في كل منها أهداف الفريق وأهداف خصمه.", "عدد صحيح واحد: مجموع النقاط.", "1 ≤ n ≤ 20؛ كل عدد أهداف بين 0 و 4"))

soal("kelipatan-terdekat", "mudah", "trimmed", "k = r.randint(1, 100)\nn = r.randint(1, 10000)\nif i % 3 == 2:\n    n = k * r.randint(1, 100)\nif i == 3:\n    k = 1\nprint(n, k)\n",
     program(r'''
int bulatkanKeAtas(int n, int k) {
    if (n % k == 0) {
        return n;
    }
    return (n / k + 1) * k;
}''', r'''    int n, k;
    cin >> n >> k;
    cout << bulatkanKeAtas(n, k) << endl;'''),
     id=("Kelipatan Terdekat ke Atas", "Barang dijual dalam kemasan berisi k buah, jadi pesanan harus dibulatkan ke atas ke kelipatan k.\n\nTulis fungsi `int bulatkanKeAtas(int n, int k)` yang mengembalikan kelipatan k terkecil yang tidak kurang dari n. Di `main`, baca n dan k dan cetak hasilnya.\n\nContoh: `17 5` → 20; `20 5` → 20 (sudah kelipatan 5).",
         "Satu baris berisi n dan k.", "Satu bilangan bulat: kelipatan k terkecil yang ≥ n.", "1 ≤ n ≤ 10000; 1 ≤ k ≤ 100"),
     en=("Next Multiple", "Goods are sold in packs of k, so an order has to be rounded up to a multiple of k.\n\nWrite a function `int bulatkanKeAtas(int n, int k)` that returns the smallest multiple of k that is not less than n. In `main`, read n and k and print the result.\n\nExamples: `17 5` → 20; `20 5` → 20 (already a multiple of 5).",
         "One line with n and k.", "One integer: the smallest multiple of k that is ≥ n.", "1 ≤ n ≤ 10000; 1 ≤ k ≤ 100"),
     ar=("المضاعف التالي", "تُباع البضاعة في عبوات من k قطعة، فيجب تقريب الطلب إلى الأعلى إلى مضاعف للعدد k.\n\nاكتب دالة `int bulatkanKeAtas(int n, int k)` تُرجع أصغر مضاعف للعدد k لا يقل عن n. في `main` اقرأ n و k واطبع الناتج.\n\nأمثلة: `17 5` ← 20؛ `20 5` ← 20 (هو مضاعف للعدد 5 أصلًا).",
         "سطر واحد فيه n و k.", "عدد صحيح واحد: أصغر مضاعف للعدد k يكون ≥ n.", "1 ≤ n ≤ 10000؛ 1 ≤ k ≤ 100"))

# ============================== MUDAH-SEDANG (6) ==============================

soal("tukar-melingkar", "mudah_sedang", "trimmed", "v = r.sample(range(1, 100), 3)\nk = {2: 0, 3: 3, 4: 20}.get(i, r.randint(1, 20))\nprint(*v)\nprint(k)\n",
     program(r'''
void putar(int &a, int &b, int &c) {
    int sementara = c;
    c = b;
    b = a;
    a = sementara;
}''', r'''    int a, b, c, k;
    cin >> a >> b >> c;
    cin >> k;
    for (int n = 0; n < k; n++) {
        putar(a, b, c);
    }
    cout << a << " " << b << " " << c << endl;'''),
     id=("Tukar Melingkar", "Tiga orang duduk melingkar dan masing-masing memegang sebuah kartu bernomor. Pada satu putaran, setiap orang menyerahkan kartunya ke orang di sebelah kanannya: kartu a pindah ke b, kartu b pindah ke c, dan kartu c pindah ke a.\n\n"
         "Tulis fungsi `void putar(int &a, int &b, int &c)` yang melakukan satu putaran. Di `main`, baca ketiga nomor kartu dan banyak putaran k, panggil `putar` k kali, lalu cetak kartu yang kini dipegang a, b, c.\n\n"
         "Contoh: kartu `1 2 3` setelah 1 putaran menjadi `3 1 2`, setelah 2 putaran menjadi `2 3 1`.",
         "Baris pertama: tiga bilangan bulat, kartu a, b, c. Baris kedua: k.", "Satu baris: kartu a, b, c setelah k putaran.", "Nomor kartu antara 1 dan 99; 0 ≤ k ≤ 20"),
     en=("Rotate Three", "Three people sit in a circle, each holding a numbered card. In one round everyone passes their card to the person on the right: card a moves to b, card b moves to c, and card c moves to a.\n\n"
         "Write a function `void putar(int &a, int &b, int &c)` that performs one round. In `main`, read the three card numbers and the number of rounds k, call `putar` k times, then print the cards now held by a, b, c.\n\n"
         "Example: cards `1 2 3` become `3 1 2` after 1 round and `2 3 1` after 2 rounds.",
         "First line: three integers, the cards of a, b, c. Second line: k.", "One line: the cards of a, b, c after k rounds.", "Card numbers are between 1 and 99; 0 ≤ k ≤ 20"),
     ar=("تبديل دائري", "ثلاثة أشخاص يجلسون في دائرة وبيد كل منهم بطاقة مرقّمة. في الجولة الواحدة يسلّم كل شخص بطاقته إلى من على يمينه: بطاقة a تنتقل إلى b، وبطاقة b إلى c، وبطاقة c إلى a.\n\n"
         "اكتب دالة `void putar(int &a, int &b, int &c)` تنفّذ جولة واحدة. في `main` اقرأ أرقام البطاقات الثلاث وعدد الجولات k واستدعِ `putar` k مرة ثم اطبع البطاقات التي صارت بيد a و b و c.\n\n"
         "مثال: البطاقات `1 2 3` تصير `3 1 2` بعد جولة واحدة و `2 3 1` بعد جولتين.",
         "السطر الأول: ثلاثة أعداد صحيحة هي بطاقات a و b و c. السطر الثاني: k.", "سطر واحد: بطاقات a و b و c بعد k جولة.", "أرقام البطاقات بين 1 و 99؛ 0 ≤ k ≤ 20"))

soal("urutkan-empat", "mudah_sedang", "trimmed", "v = [r.randint(-1000, 1000) for _ in range(4)]\nif i == 2:\n    v.sort(reverse=True)\nif i == 3:\n    v[3] = v[0]\nif i == 4:\n    v.sort()\nprint(*v)\n",
     program(r'''
void urutkan(int &a, int &b) {
    if (a > b) {
        int sementara = a;
        a = b;
        b = sementara;
    }
}''', r'''    int a, b, c, d;
    cin >> a >> b >> c >> d;
    urutkan(a, b);
    urutkan(c, d);
    urutkan(a, c);
    urutkan(b, d);
    urutkan(b, c);
    cout << a << " " << b << " " << c << " " << d << endl;'''),
     id=("Urutkan Empat Bilangan", "Tulis fungsi `void urutkan(int &a, int &b)` yang memastikan a tidak lebih besar daripada b: bila urutannya salah, isi keduanya ditukar.\n\n"
         "Dengan **hanya memanggil fungsi itu beberapa kali**, urutkan empat bilangan dari yang terkecil ke yang terbesar, lalu cetak hasilnya. Lima panggilan sudah cukup bila pasangannya dipilih dengan cermat.\n\nUntuk `7 -2 9 4` keluarannya `-2 4 7 9`.",
         "Satu baris berisi empat bilangan bulat.", "Keempat bilangan itu, terurut naik.", "Tiap bilangan antara −1000 dan 1000; boleh ada yang sama."),
     en=("Sort Four Numbers", "Write a function `void urutkan(int &a, int &b)` that makes sure a is not larger than b: if they are out of order, their contents are swapped.\n\n"
         "**Only by calling that function several times**, sort four numbers from smallest to largest, then print them. Five calls are enough when the pairs are chosen with care.\n\nFor `7 -2 9 4` the output is `-2 4 7 9`.",
         "One line with four integers.", "The four numbers in ascending order.", "Each number is between −1000 and 1000; some may be equal."),
     ar=("ترتيب أربعة أعداد", "اكتب دالة `void urutkan(int &a, int &b)` تضمن ألّا يكون a أكبر من b: إذا كان الترتيب خاطئًا بُدِّل محتواهما.\n\n"
         "**باستدعاء هذه الدالة عدة مرات فقط** رتِّب أربعة أعداد من الأصغر إلى الأكبر ثم اطبعها. خمسة استدعاءات تكفي إذا اختيرت الأزواج بعناية.\n\nعند `7 -2 9 4` يكون المخرج `-2 4 7 9`.",
         "سطر واحد فيه أربعة أعداد صحيحة.", "الأعداد الأربعة مرتبة تصاعديًا.", "كل عدد بين −1000 و 1000، وقد يتساوى بعضها."))

soal("bilangan-sempurna", "mudah_sedang", "trimmed",
     "n = r.randint(2, 10000)\nif i % 3 == 0:\n    n = r.choice([6, 28, 496, 8128])\nif i == 4:\n    n = r.choice([12, 18, 20, 945])\nif i == 5:\n    n = r.choice([2, 8, 9973])\nprint(n)\n",
     program(r'''
int jumlahPembagi(int n) {
    int jumlah = 0;
    for (int k = 1; k < n; k++) {
        if (n % k == 0) {
            jumlah += k;
        }
    }
    return jumlah;
}''', r'''    int n;
    cin >> n;
    int jumlah = jumlahPembagi(n);
    if (jumlah == n) {
        cout << "sempurna" << endl;
    } else if (jumlah > n) {
        cout << "berlimpah" << endl;
    } else {
        cout << "kurang" << endl;
    }'''),
     id=("Bilangan Sempurna", "Jumlahkan semua pembagi sebuah bilangan **selain bilangan itu sendiri**. Bila jumlahnya sama dengan bilangan itu, bilangan itu disebut *sempurna*; bila lebih besar, *berlimpah*; bila lebih kecil, *kurang*.\n\n"
         "- 6 sempurna: 1 + 2 + 3 = 6.\n- 12 berlimpah: 1 + 2 + 3 + 4 + 6 = 16.\n- 8 kurang: 1 + 2 + 4 = 7.\n\n"
         "Tulis fungsi `int jumlahPembagi(int n)` yang mengembalikan jumlah itu. Di `main`, baca n dan cetak `sempurna`, `berlimpah`, atau `kurang`.",
         "Satu bilangan bulat n.", "`sempurna`, `berlimpah`, atau `kurang`.", "2 ≤ n ≤ 10000"),
     en=("Perfect Number", "Add up all divisors of a number **except the number itself**. If the sum equals the number, the number is called *perfect*; if it is larger, *abundant*; if smaller, *deficient*.\n\n"
         "- 6 is perfect: 1 + 2 + 3 = 6.\n- 12 is abundant: 1 + 2 + 3 + 4 + 6 = 16.\n- 8 is deficient: 1 + 2 + 4 = 7.\n\n"
         "Write a function `int jumlahPembagi(int n)` that returns that sum. In `main`, read n and print `sempurna` (perfect), `berlimpah` (abundant) or `kurang` (deficient).",
         "One integer n.", "`sempurna`, `berlimpah` or `kurang`.", "2 ≤ n ≤ 10000"),
     ar=("العدد التام", "اجمع كل قواسم عدد **عدا العدد نفسه**. إذا ساوى المجموعُ العددَ سُمّي العدد *تامًّا*، وإذا زاد عليه سُمّي *زائدًا*، وإذا نقص عنه سُمّي *ناقصًا*.\n\n"
         "- 6 تام: 1 + 2 + 3 = 6.\n- 12 زائد: 1 + 2 + 3 + 4 + 6 = 16.\n- 8 ناقص: 1 + 2 + 4 = 7.\n\n"
         "اكتب دالة `int jumlahPembagi(int n)` تُرجع ذلك المجموع. في `main` اقرأ n واطبع `sempurna` (تام) أو `berlimpah` (زائد) أو `kurang` (ناقص).",
         "عدد صحيح واحد n.", "`sempurna` أو `berlimpah` أو `kurang`.", "2 ≤ n ≤ 10000"))

soal("bilangan-armstrong", "mudah_sedang", "trimmed",
     "n = r.randint(10, 999999)\nif i % 2 == 0:\n    n = r.choice([153, 370, 371, 407, 1634, 8208, 9474, 54748, 92727, 93084, 548834, r.randint(1, 9)])\nif i == 3:\n    n = r.choice([154, 372, 1635, 100, 999999])\nprint(n)\n",
     program(r'''
int banyakDigit(int n) {
    int banyak = 0;
    while (n > 0) {
        banyak++;
        n /= 10;
    }
    return banyak;
}

int pangkat(int a, int b) {
    int hasil = 1;
    for (int k = 0; k < b; k++) {
        hasil *= a;
    }
    return hasil;
}''', r'''    int n;
    cin >> n;
    int d = banyakDigit(n);
    int jumlah = 0;
    int sisa = n;
    while (sisa > 0) {
        jumlah += pangkat(sisa % 10, d);
        sisa /= 10;
    }
    if (jumlah == n) {
        cout << "armstrong" << endl;
    } else {
        cout << "bukan armstrong" << endl;
    }'''),
     id=("Bilangan Armstrong", "Sebuah bilangan dengan d digit disebut bilangan Armstrong bila **jumlah tiap digitnya dipangkatkan d** sama dengan bilangan itu sendiri.\n\n"
         "- 153 punya 3 digit, dan 1³ + 5³ + 3³ = 1 + 125 + 27 = 153: Armstrong.\n- 1634 punya 4 digit, dan 1⁴ + 6⁴ + 3⁴ + 4⁴ = 1634: Armstrong.\n- 154 bukan, karena 1 + 125 + 64 = 190.\n\n"
         "Tulis dua fungsi: `int banyakDigit(int n)` dan `int pangkat(int a, int b)`. Dengan keduanya, tentukan di `main` apakah n bilangan Armstrong, lalu cetak `armstrong` atau `bukan armstrong`.",
         "Satu bilangan bulat n.", "`armstrong` atau `bukan armstrong`.", "1 ≤ n ≤ 999999"),
     en=("Armstrong Number", "A number with d digits is an Armstrong number when **the sum of its digits, each raised to the power d**, equals the number itself.\n\n"
         "- 153 has 3 digits, and 1³ + 5³ + 3³ = 1 + 125 + 27 = 153: Armstrong.\n- 1634 has 4 digits, and 1⁴ + 6⁴ + 3⁴ + 4⁴ = 1634: Armstrong.\n- 154 is not, because 1 + 125 + 64 = 190.\n\n"
         "Write two functions: `int banyakDigit(int n)` (number of digits) and `int pangkat(int a, int b)` (power). With them, decide in `main` whether n is an Armstrong number, then print `armstrong` or `bukan armstrong`.",
         "One integer n.", "`armstrong` or `bukan armstrong`.", "1 ≤ n ≤ 999999"),
     ar=("عدد أرمسترونغ", "العدد المكوّن من d رقمًا يسمى عدد أرمسترونغ إذا كان **مجموع أرقامه، كلٌّ منها مرفوعًا إلى الأُسّ d**، يساوي العدد نفسه.\n\n"
         "- 153 فيه 3 أرقام، و 1³ + 5³ + 3³ = 1 + 125 + 27 = 153: عدد أرمسترونغ.\n- 1634 فيه 4 أرقام، و 1⁴ + 6⁴ + 3⁴ + 4⁴ = 1634: عدد أرمسترونغ.\n- 154 ليس كذلك، لأن 1 + 125 + 64 = 190.\n\n"
         "اكتب دالتين: `int banyakDigit(int n)` (عدد الأرقام) و `int pangkat(int a, int b)` (الأُسّ). بهما حدِّد في `main` هل n عدد أرمسترونغ ثم اطبع `armstrong` أو `bukan armstrong`.",
         "عدد صحيح واحد n.", "`armstrong` أو `bukan armstrong`.", "1 ≤ n ≤ 999999"))

soal("geser-titik", "mudah_sedang", "trimmed",
     "print(r.randint(-50, 50), r.randint(-50, 50))\nn = r.randint(1, 15)\nprint(n)\nfor _ in range(n):\n    print(r.choice('USTB'), r.randint(1, 20))\n",
     program(r'''
void geser(int &x, int &y, char arah, int langkah) {
    if (arah == 'U') {
        y += langkah;
    } else if (arah == 'S') {
        y -= langkah;
    } else if (arah == 'T') {
        x += langkah;
    } else {
        x -= langkah;
    }
}''', r'''    int x, y, n, langkah;
    char arah;
    cin >> x >> y;
    cin >> n;
    for (int k = 0; k < n; k++) {
        cin >> arah >> langkah;
        geser(x, y, arah, langkah);
    }
    cout << x << " " << y << endl;'''),
     id=("Geser Titik", "Sebuah robot berada di titik (x, y) dan menerima n perintah gerak. Tiap perintah berisi arah dan banyak langkah:\n\n"
         "- `U` (utara): y bertambah;\n- `S` (selatan): y berkurang;\n- `T` (timur): x bertambah;\n- `B` (barat): x berkurang.\n\n"
         "Tulis fungsi `void geser(int &x, int &y, char arah, int langkah)` yang menjalankan satu perintah dengan mengubah x dan y milik pemanggil. Di `main`, jalankan semua perintah lalu cetak posisi akhir robot.\n\n"
         "Contoh: mulai dari (0, 0), perintah `U 3`, `T 2`, `S 5` membawa robot ke (2, −2).",
         "Baris pertama: x dan y awal. Baris kedua: n. Lalu n baris, masing-masing berisi arah dan banyak langkah.", "Satu baris: x dan y akhir.", "−50 ≤ x, y awal ≤ 50; 1 ≤ n ≤ 15; 1 ≤ langkah ≤ 20"),
     en=("Move a Point", "A robot stands at the point (x, y) and receives n move commands. Each command has a direction and a number of steps:\n\n"
         "- `U` (utara, north): y increases;\n- `S` (selatan, south): y decreases;\n- `T` (timur, east): x increases;\n- `B` (barat, west): x decreases.\n\n"
         "Write a function `void geser(int &x, int &y, char arah, int langkah)` that carries out one command by changing the caller's x and y. In `main`, carry out all commands, then print the robot's final position.\n\n"
         "Example: starting at (0, 0), the commands `U 3`, `T 2`, `S 5` take the robot to (2, −2).",
         "First line: the starting x and y. Second line: n. Then n lines, each with a direction and a number of steps.", "One line: the final x and y.", "−50 ≤ starting x, y ≤ 50; 1 ≤ n ≤ 15; 1 ≤ steps ≤ 20"),
     ar=("تحريك نقطة", "يقف روبوت عند النقطة (x, y) ويتلقى n أمر حركة. في كل أمر اتجاه وعدد خطوات:\n\n"
         "- `U` (الشمال): يزداد y؛\n- `S` (الجنوب): ينقص y؛\n- `T` (الشرق): يزداد x؛\n- `B` (الغرب): ينقص x.\n\n"
         "اكتب دالة `void geser(int &x, int &y, char arah, int langkah)` تنفّذ أمرًا واحدًا بتغيير x و y الخاصين بالمستدعي. في `main` نفِّذ كل الأوامر ثم اطبع الموضع النهائي للروبوت.\n\n"
         "مثال: بدءًا من (0, 0) تنقل الأوامر `U 3` و `T 2` و `S 5` الروبوت إلى (2, −2).",
         "السطر الأول: x و y الابتدائيان. السطر الثاني: n. ثم n سطرًا في كل منها اتجاه وعدد خطوات.", "سطر واحد: x و y النهائيان.", "−50 ≤ x, y الابتدائيان ≤ 50؛ 1 ≤ n ≤ 15؛ 1 ≤ الخطوات ≤ 20"))

soal("akar-digital", "mudah_sedang", "trimmed", "print({2: r.randint(0, 9), 3: 999999999, 4: 1000000000, 5: 199}.get(i, r.randint(10, 10 ** r.randint(2, 9))))\n",
     program(r'''
int jumlahDigit(int n) {
    int jumlah = 0;
    while (n > 0) {
        jumlah += n % 10;
        n /= 10;
    }
    return jumlah;
}''', r'''    int n;
    cin >> n;
    int langkah = 0;
    while (n >= 10) {
        n = jumlahDigit(n);
        langkah++;
    }
    cout << n << " " << langkah << endl;'''),
     id=("Akar Digital", "Jumlahkan digit-digit sebuah bilangan. Bila hasilnya masih lebih dari satu digit, jumlahkan lagi digit-digitnya, dan seterusnya sampai tersisa satu digit. Digit terakhir itu disebut *akar digital*.\n\n"
         "Contoh: 9875 → 9 + 8 + 7 + 5 = 29 → 2 + 9 = 11 → 1 + 1 = 2. Akar digitalnya 2, diperoleh dalam 3 langkah.\n\n"
         "Tulis fungsi `int jumlahDigit(int n)`. Di `main`, panggil fungsi itu berulang-ulang, lalu cetak akar digital dan banyak langkahnya. Bilangan yang sudah satu digit tidak memerlukan langkah apa pun: untuk 7 keluarannya `7 0`.",
         "Satu bilangan bulat n.", "Satu baris: akar digital n, lalu banyak langkah.", "0 ≤ n ≤ 1000000000"),
     en=("Digital Root", "Add up the digits of a number. If the result still has more than one digit, add up its digits again, and so on until one digit is left. That last digit is called the *digital root*.\n\n"
         "Example: 9875 → 9 + 8 + 7 + 5 = 29 → 2 + 9 = 11 → 1 + 1 = 2. The digital root is 2, reached in 3 steps.\n\n"
         "Write a function `int jumlahDigit(int n)` (sum of digits). In `main`, call it again and again, then print the digital root and the number of steps. A number that already has one digit needs no step: for 7 the output is `7 0`.",
         "One integer n.", "One line: the digital root of n, then the number of steps.", "0 ≤ n ≤ 1000000000"),
     ar=("الجذر الرقمي", "اجمع أرقام عدد. إذا بقي الناتج أكثر من رقم واحد فاجمع أرقامه مرة أخرى، وهكذا حتى يبقى رقم واحد. ذلك الرقم الأخير يسمى *الجذر الرقمي*.\n\n"
         "مثال: 9875 ← 9 + 8 + 7 + 5 = 29 ← 2 + 9 = 11 ← 1 + 1 = 2. الجذر الرقمي 2، ويُبلغ في 3 خطوات.\n\n"
         "اكتب دالة `int jumlahDigit(int n)` (مجموع الأرقام). في `main` استدعِها مرة بعد مرة ثم اطبع الجذر الرقمي وعدد الخطوات. العدد الذي هو رقم واحد أصلًا لا يحتاج إلى أي خطوة: للعدد 7 يكون المخرج `7 0`.",
         "عدد صحيح واحد n.", "سطر واحد: الجذر الرقمي للعدد n ثم عدد الخطوات.", "0 ≤ n ≤ 1000000000"))

# ================================ SEDANG (6) ================================

soal("maju-k-hari", "sedang", "trimmed",
     "import datetime\nt = datetime.date(1900, 1, 1) + datetime.timedelta(days=r.randint(0, 73000))\nk = r.randint(0, 1000)\nif i == 2:\n    t, k = datetime.date(2024, 2, 28), r.randint(1, 2)\nif i == 3:\n    t, k = datetime.date(r.randint(1900, 2099), 12, 31), 1\nif i == 4:\n    t, k = datetime.date(1900, 2, 28), 1\nif i == 5:\n    k = 0\nif i == 6:\n    t, k = datetime.date(2099, 6, 1), 1000\nprint(t.day, t.month, t.year, k)\n",
     program(r'''
bool kabisat(int tahun) {
    return tahun % 400 == 0 || (tahun % 4 == 0 && tahun % 100 != 0);
}

int hariDalamBulan(int bulan, int tahun) {
    if (bulan == 2) {
        if (kabisat(tahun)) {
            return 29;
        }
        return 28;
    }
    if (bulan == 4 || bulan == 6 || bulan == 9 || bulan == 11) {
        return 30;
    }
    return 31;
}

void besok(int &hari, int &bulan, int &tahun) {
    hari++;
    if (hari > hariDalamBulan(bulan, tahun)) {
        hari = 1;
        bulan++;
        if (bulan > 12) {
            bulan = 1;
            tahun++;
        }
    }
}''', r'''    int hari, bulan, tahun, k;
    cin >> hari >> bulan >> tahun >> k;
    for (int n = 0; n < k; n++) {
        besok(hari, bulan, tahun);
    }
    cout << hari << " " << bulan << " " << tahun << endl;'''),
     id=("Maju k Hari", "Diberikan sebuah tanggal dan bilangan k. Tanggal berapakah k hari sesudahnya?\n\n"
         "Susun program dari tiga fungsi, masing-masing memakai fungsi sebelumnya:\n\n"
         "- `bool kabisat(int tahun)` — tahun kabisat: habis dibagi 400, atau habis dibagi 4 tetapi tidak habis dibagi 100;\n"
         "- `int hariDalamBulan(int bulan, int tahun)` — 28 atau 29 untuk Februari; 30 untuk April, Juni, September, November; 31 untuk lainnya;\n"
         "- `void besok(int &hari, int &bulan, int &tahun)` — memajukan tanggal milik pemanggil satu hari.\n\n"
         "Di `main`, panggil `besok` sebanyak k kali, lalu cetak tanggalnya.\n\n"
         "Contoh: `28 2 2024 2` → `1 3 2024`; `31 12 2025 1` → `1 1 2026`.",
         "Satu baris berisi empat bilangan bulat: hari, bulan, tahun, dan k.", "Satu baris: hari, bulan, tahun dari tanggal k hari kemudian.", "Tanggal sah antara tahun 1900 dan 2099; 0 ≤ k ≤ 1000"),
     en=("k Days Later", "A date and a number k are given. What is the date k days later?\n\n"
         "Build the program from three functions, each using the one before:\n\n"
         "- `bool kabisat(int tahun)` — leap year: divisible by 400, or divisible by 4 but not by 100;\n"
         "- `int hariDalamBulan(int bulan, int tahun)` — days in a month: 28 or 29 for February; 30 for April, June, September, November; 31 for the others;\n"
         "- `void besok(int &hari, int &bulan, int &tahun)` — moves the caller's date forward by one day.\n\n"
         "In `main`, call `besok` k times, then print the date.\n\n"
         "Examples: `28 2 2024 2` → `1 3 2024`; `31 12 2025 1` → `1 1 2026`.",
         "One line with four integers: day, month, year and k.", "One line: day, month, year of the date k days later.", "A valid date between the years 1900 and 2099; 0 ≤ k ≤ 1000"),
     ar=("بعد k يومًا", "يُعطى تاريخ وعدد k. ما التاريخ بعد k يومًا؟\n\n"
         "ابنِ البرنامج من ثلاث دوال، كل واحدة تستعمل التي قبلها:\n\n"
         "- `bool kabisat(int tahun)` — السنة الكبيسة: تقبل القسمة على 400، أو تقبل القسمة على 4 ولا تقبلها على 100؛\n"
         "- `int hariDalamBulan(int bulan, int tahun)` — عدد أيام الشهر: 28 أو 29 لفبراير؛ 30 لأبريل ويونيو وسبتمبر ونوفمبر؛ 31 للبقية؛\n"
         "- `void besok(int &hari, int &bulan, int &tahun)` — تقدّم تاريخ المستدعي يومًا واحدًا.\n\n"
         "في `main` استدعِ `besok` k مرة ثم اطبع التاريخ.\n\n"
         "أمثلة: `28 2 2024 2` ← `1 3 2024`؛ `31 12 2025 1` ← `1 1 2026`.",
         "سطر واحد فيه أربعة أعداد صحيحة: اليوم والشهر والسنة و k.", "سطر واحد: يوم وشهر وسنة التاريخ بعد k يومًا.", "تاريخ صحيح بين عامي 1900 و 2099؛ 0 ≤ k ≤ 1000"))

soal("rekap-penjualan", "sedang", "trimmed",
     "n = r.randint(1, 20)\nbaris = [(r.randint(1, 3), r.randint(1, 20)) for _ in range(n)]\nif i == 2:\n    baris = [(1, 5), (2, 5), (3, 5)]\nif i == 3:\n    baris = [(3, 7), (2, 7)]\nif i == 4:\n    baris = [(2, r.randint(1, 20))]\nprint(len(baris))\nfor k, b in baris:\n    print(k, b)\n",
     program(r'''
void catat(int kode, int banyak, int &kopi, int &teh, int &susu) {
    if (kode == 1) {
        kopi += banyak;
    } else if (kode == 2) {
        teh += banyak;
    } else {
        susu += banyak;
    }
}''', r'''    int n, kode, banyak;
    int kopi = 0, teh = 0, susu = 0;
    cin >> n;
    for (int k = 0; k < n; k++) {
        cin >> kode >> banyak;
        catat(kode, banyak, kopi, teh, susu);
    }
    cout << "Kopi: " << kopi << endl;
    cout << "Teh: " << teh << endl;
    cout << "Susu: " << susu << endl;
    if (kopi >= teh && kopi >= susu) {
        cout << "Terlaris: Kopi" << endl;
    } else if (teh >= susu) {
        cout << "Terlaris: Teh" << endl;
    } else {
        cout << "Terlaris: Susu" << endl;
    }'''),
     id=("Rekap Penjualan", "Sebuah kedai menjual tiga minuman dengan kode 1 = Kopi, 2 = Teh, 3 = Susu. Setiap transaksi dicatat sebagai kode minuman dan banyak gelas.\n\n"
         "Tulis fungsi `void catat(int kode, int banyak, int &kopi, int &teh, int &susu)` yang menambahkan satu transaksi ke penghitung yang sesuai. Ketiga penghitung adalah variabel di `main`.\n\n"
         "Setelah semua transaksi dicatat, cetak empat baris: jumlah gelas tiap minuman, lalu minuman terlaris. Bila ada yang sama banyak, pilih yang lebih dulu dalam urutan Kopi, Teh, Susu.\n\n"
         "Untuk transaksi `1 3`, `3 2`, `1 4`, `2 7`:\n\n```\nKopi: 7\nTeh: 7\nSusu: 2\nTerlaris: Kopi\n```",
         "Baris pertama: n. Lalu n baris, masing-masing berisi kode minuman dan banyak gelas.", "Empat baris seperti contoh.", "1 ≤ n ≤ 20; kode 1, 2, atau 3; 1 ≤ banyak ≤ 20"),
     en=("Sales Summary", "A café sells three drinks with the codes 1 = Kopi (coffee), 2 = Teh (tea), 3 = Susu (milk). Every sale is recorded as a drink code and a number of glasses.\n\n"
         "Write a function `void catat(int kode, int banyak, int &kopi, int &teh, int &susu)` that adds one sale to the right counter. The three counters are variables in `main`.\n\n"
         "After all sales are recorded, print four lines: the glasses sold of each drink, then the best seller (`Terlaris`). On a tie choose the one that comes first in the order Kopi, Teh, Susu.\n\n"
         "For the sales `1 3`, `3 2`, `1 4`, `2 7`:\n\n```\nKopi: 7\nTeh: 7\nSusu: 2\nTerlaris: Kopi\n```",
         "First line: n. Then n lines, each with a drink code and a number of glasses.", "Four lines as in the example.", "1 ≤ n ≤ 20; the code is 1, 2 or 3; 1 ≤ glasses ≤ 20"),
     ar=("ملخص المبيعات", "يبيع مقهى ثلاثة مشروبات رموزها 1 = Kopi (قهوة)، 2 = Teh (شاي)، 3 = Susu (حليب). تُسجَّل كل عملية بيع برمز المشروب وعدد الأكواب.\n\n"
         "اكتب دالة `void catat(int kode, int banyak, int &kopi, int &teh, int &susu)` تضيف عملية بيع واحدة إلى العدّاد المناسب. العدّادات الثلاثة متغيرات في `main`.\n\n"
         "بعد تسجيل كل العمليات اطبع أربعة أسطر: عدد أكواب كل مشروب ثم الأكثر مبيعًا (`Terlaris`). عند التساوي اختر الأسبق في الترتيب Kopi ثم Teh ثم Susu.\n\n"
         "للعمليات `1 3` و `3 2` و `1 4` و `2 7`:\n\n```\nKopi: 7\nTeh: 7\nSusu: 2\nTerlaris: Kopi\n```",
         "السطر الأول: n. ثم n سطرًا في كل منها رمز المشروب وعدد الأكواب.", "أربعة أسطر كما في المثال.", "1 ≤ n ≤ 20؛ الرمز 1 أو 2 أو 3؛ 1 ≤ عدد الأكواب ≤ 20"))

soal("kpk-tiga-bilangan", "sedang", "trimmed",
     "v = [r.randint(1, 1000) for _ in range(3)]\nif i == 2:\n    v = [997, 991, 983]\nif i == 3:\n    v = [v[0]] * 3\nif i == 4:\n    g = r.randint(2, 30)\n    v = [g * r.randint(1, 33) for _ in range(3)]\nif i == 5:\n    v = [1, 1, r.randint(1, 1000)]\nprint(*v)\n",
     program(r'''
int fpb(int a, int b) {
    while (b != 0) {
        int sisa = a % b;
        a = b;
        b = sisa;
    }
    return a;
}

int kpk(int a, int b) {
    return a / fpb(a, b) * b;
}''', r'''    int a, b, c;
    cin >> a >> b >> c;
    cout << kpk(kpk(a, b), c) << endl;'''),
     id=("KPK Tiga Bilangan", "Kelipatan persekutuan terkecil (KPK) beberapa bilangan adalah bilangan positif terkecil yang habis dibagi semuanya. KPK dari 4, 6, dan 10 adalah 60.\n\n"
         "Tulis dua fungsi:\n\n- `int fpb(int a, int b)` — faktor persekutuan terbesar;\n- `int kpk(int a, int b)` — memakai `fpb`, karena KPK(a, b) = a × b / FPB(a, b).\n\n"
         "KPK tiga bilangan diperoleh dengan memanggil `kpk` dua kali: KPK(KPK(a, b), c).\n\nHati-hati: hasil a × b bisa melewati batas `int` walaupun KPK-nya sendiri tidak. Bagi dulu, baru kalikan.",
         "Satu baris berisi tiga bilangan bulat.", "Satu bilangan bulat: KPK ketiganya.", "Tiap bilangan antara 1 dan 1000."),
     en=("Least Common Multiple of Three", "The least common multiple (KPK in Indonesian) of several numbers is the smallest positive number divisible by all of them. The least common multiple of 4, 6 and 10 is 60.\n\n"
         "Write two functions:\n\n- `int fpb(int a, int b)` — the greatest common divisor;\n- `int kpk(int a, int b)` — which uses `fpb`, because KPK(a, b) = a × b / FPB(a, b).\n\n"
         "The least common multiple of three numbers comes from calling `kpk` twice: KPK(KPK(a, b), c).\n\nCareful: a × b can exceed the range of `int` even when the result itself does not. Divide first, then multiply.",
         "One line with three integers.", "One integer: their least common multiple.", "Each number is between 1 and 1000."),
     ar=("المضاعف المشترك الأصغر لثلاثة أعداد", "المضاعف المشترك الأصغر (KPK بالإندونيسية) لعدة أعداد هو أصغر عدد موجب يقبل القسمة عليها كلها. المضاعف المشترك الأصغر للأعداد 4 و 6 و 10 هو 60.\n\n"
         "اكتب دالتين:\n\n- `int fpb(int a, int b)` — القاسم المشترك الأكبر؛\n- `int kpk(int a, int b)` — تستعمل `fpb`، لأن KPK(a, b) = a × b / FPB(a, b).\n\n"
         "المضاعف المشترك الأصغر لثلاثة أعداد يُحسب باستدعاء `kpk` مرتين: KPK(KPK(a, b), c).\n\nانتبه: قد يتجاوز a × b مجال `int` حتى لو لم يتجاوزه الناتج نفسه. اقسم أولًا ثم اضرب.",
         "سطر واحد فيه ثلاثة أعداد صحيحة.", "عدد صحيح واحد: مضاعفها المشترك الأصغر.", "كل عدد بين 1 و 1000."))

soal("hitung-kata", "sedang", "trimmed",
     KATA + "n = r.randint(1, 8)\nt = ' ' * (r.randint(0, 3) if i % 2 else 0)\nfor k in range(n):\n    t += r.choice(KATA) + ' ' * (r.randint(1, 3) if (k < n - 1 or i % 3 == 0) else 0)\nif i == 2:\n    t = r.choice(KATA)\nprint(t)\n",
     program(r'''
int banyakKata(string baris) {
    int banyak = 0;
    bool dalamKata = false;
    for (int k = 0; k < (int) baris.size(); k++) {
        if (baris[k] == ' ') {
            dalamKata = false;
        } else if (!dalamKata) {
            dalamKata = true;
            banyak++;
        }
    }
    return banyak;
}''', r'''    string baris;
    getline(cin, baris);
    cout << banyakKata(baris) << endl;''', "string"),
     id=("Hitung Kata", "Tulis fungsi `int banyakKata(string baris)` yang mengembalikan banyak kata dalam satu baris teks. Kata adalah rangkaian huruf yang tidak terputus spasi.\n\n"
         "Kata-kata bisa dipisah **lebih dari satu spasi**, dan baris bisa diawali atau diakhiri spasi, jadi menghitung spasi saja tidak cukup.\n\n"
         "Untuk baris `  belajar   kode  hari ini ` (dua spasi di depan, satu di belakang) jawabannya 4.\n\nBaca barisnya dengan `getline`.",
         "Satu baris teks.", "Satu bilangan bulat: banyak kata.", "Baris terdiri dari huruf kecil dan spasi, paling panjang 100 karakter, dan berisi paling sedikit satu kata."),
     en=("Count the Words", "Write a function `int banyakKata(string baris)` that returns the number of words in one line of text. A word is a run of letters not broken by a space.\n\n"
         "Words may be separated by **more than one space**, and the line may begin or end with spaces, so counting spaces alone is not enough.\n\n"
         "For the line `  belajar   kode  hari ini ` (two spaces in front, one at the end) the answer is 4.\n\nRead the line with `getline`.",
         "One line of text.", "One integer: the number of words.", "The line has lowercase letters and spaces, at most 100 characters, and holds at least one word."),
     ar=("عدّ الكلمات", "اكتب دالة `int banyakKata(string baris)` تُرجع عدد الكلمات في سطر من النص. الكلمة سلسلة حروف لا تقطعها مسافة.\n\n"
         "قد تفصل بين الكلمات **أكثر من مسافة واحدة**، وقد يبدأ السطر أو ينتهي بمسافات، فلا يكفي عدّ المسافات وحده.\n\n"
         "للسطر `  belajar   kode  hari ini ` (مسافتان في أوله وواحدة في آخره) الجواب 4.\n\nاقرأ السطر بـ `getline`.",
         "سطر واحد من النص.", "عدد صحيح واحد: عدد الكلمات.", "السطر حروف لاتينية صغيرة ومسافات، 100 محرف على الأكثر، وفيه كلمة واحدة على الأقل."))

soal("ke-basis", "sedang", "trimmed",
     "n, b = r.randint(1, 1000000), r.randint(2, 16)\nif i == 2:\n    n = 0\nif i == 3:\n    n, b = 255, 16\nif i == 4:\n    b = 2\nif i == 5:\n    n, b = 1000000, 2\nif i == 6:\n    b = 16\nprint(n, b)\n",
     program(r'''
string keBasis(int n, int basis) {
    if (n == 0) {
        return "0";
    }
    string hasil = "";
    while (n > 0) {
        int d = n % basis;
        char c;
        if (d < 10) {
            c = (char) ('0' + d);
        } else {
            c = (char) ('A' + d - 10);
        }
        hasil = c + hasil;
        n /= basis;
    }
    return hasil;
}''', r'''    int n, basis;
    cin >> n >> basis;
    cout << keBasis(n, basis) << endl;''', "string"),
     id=("Ubah ke Basis Lain", "Tulis fungsi `string keBasis(int n, int basis)` yang mengembalikan bilangan n ditulis dalam basis tertentu (2 sampai 16).\n\n"
         "Caranya: bagi n dengan basis berulang-ulang; sisa-sisa pembagiannya, **dibaca dari yang terakhir**, adalah digit-digit hasilnya. Digit bernilai 10 sampai 15 ditulis dengan huruf besar `A` sampai `F`.\n\n"
         "Contoh: 10 dalam basis 2 adalah `1010`; 255 dalam basis 16 adalah `FF`; 100 dalam basis 7 adalah `202`; 0 dalam basis apa pun adalah `0`.",
         "Satu baris berisi n dan basis.", "n ditulis dalam basis itu.", "0 ≤ n ≤ 1000000; 2 ≤ basis ≤ 16"),
     en=("Convert to Another Base", "Write a function `string keBasis(int n, int basis)` that returns the number n written in a given base (2 to 16).\n\n"
         "The method: divide n by the base again and again; the remainders, **read from the last one**, are the digits of the result. Digits worth 10 to 15 are written with the capital letters `A` to `F`.\n\n"
         "Examples: 10 in base 2 is `1010`; 255 in base 16 is `FF`; 100 in base 7 is `202`; 0 in any base is `0`.",
         "One line with n and the base.", "n written in that base.", "0 ≤ n ≤ 1000000; 2 ≤ base ≤ 16"),
     ar=("التحويل إلى أساس آخر", "اكتب دالة `string keBasis(int n, int basis)` تُرجع العدد n مكتوبًا في أساس معيّن (من 2 إلى 16).\n\n"
         "الطريقة: اقسم n على الأساس مرة بعد مرة؛ بواقي القسمة، **مقروءةً من آخرها**، هي أرقام الناتج. الأرقام التي قيمتها من 10 إلى 15 تُكتب بالحروف الكبيرة من `A` إلى `F`.\n\n"
         "أمثلة: 10 في الأساس 2 هو `1010`؛ 255 في الأساس 16 هو `FF`؛ 100 في الأساس 7 هو `202`؛ 0 في أي أساس هو `0`.",
         "سطر واحد فيه n والأساس.", "العدد n مكتوبًا في ذلك الأساس.", "0 ≤ n ≤ 1000000؛ 2 ≤ الأساس ≤ 16"))

soal("keliling-segitiga-titik", "sedang", "trimmed",
     "import math\nwhile True:\n    p = [(r.randint(-20, 20), r.randint(-20, 20)) for _ in range(3)]\n    if i == 2:\n        p = [(0, 0), (3 * (i + 1), 0), (0, 4 * (i + 1))]\n    k = sum(math.dist(p[a], p[(a + 1) % 3]) for a in range(3))\n    if len(set(p)) == 3 and abs(k * 100 % 1 - 0.5) > 1e-3:\n        break\nfor x, y in p:\n    print(x, y)\n",
     program(r'''
double jarak(int x1, int y1, int x2, int y2) {
    int dx = x2 - x1;
    int dy = y2 - y1;
    return sqrt((double) (dx * dx + dy * dy));
}''', r'''    int x1, y1, x2, y2, x3, y3;
    cin >> x1 >> y1;
    cin >> x2 >> y2;
    cin >> x3 >> y3;
    double keliling = jarak(x1, y1, x2, y2) + jarak(x2, y2, x3, y3) + jarak(x3, y3, x1, y1);
    cout << fixed << setprecision(2) << keliling << endl;''', "cmath", "iomanip"),
     id=("Keliling Segitiga dari Titik", "Jarak dua titik (x1, y1) dan (x2, y2) adalah akar dari (x2 − x1)² + (y2 − y1)².\n\n"
         "Tulis fungsi `double jarak(int x1, int y1, int x2, int y2)`. Fungsi `sqrt` untuk akar kuadrat ada di `<cmath>`.\n\n"
         "Di `main`, baca tiga titik sudut sebuah segitiga, lalu cetak kelilingnya dengan dua angka di belakang koma. Fungsi `jarak` dipanggil tiga kali, sekali untuk tiap sisi.\n\n"
         "Contoh: titik (0, 0), (3, 0), dan (0, 4) membentuk segitiga dengan sisi 3, 4, 5, sehingga kelilingnya `12.00`.",
         "Tiga baris, masing-masing berisi x dan y sebuah titik sudut.", "Keliling segitiga dengan dua angka di belakang koma.", "Semua koordinat bilangan bulat antara −20 dan 20; ketiga titik berbeda."),
     en=("Perimeter from Points", "The distance between two points (x1, y1) and (x2, y2) is the square root of (x2 − x1)² + (y2 − y1)².\n\n"
         "Write a function `double jarak(int x1, int y1, int x2, int y2)`. The square-root function `sqrt` is in `<cmath>`.\n\n"
         "In `main`, read the three corners of a triangle, then print its perimeter with two digits after the decimal point. The function `jarak` is called three times, once per side.\n\n"
         "Example: the points (0, 0), (3, 0) and (0, 4) form a triangle with sides 3, 4, 5, so the perimeter is `12.00`.",
         "Three lines, each with the x and y of one corner.", "The perimeter with two digits after the decimal point.", "All coordinates are integers between −20 and 20; the three points are different."),
     ar=("محيط المثلث من نقاطه", "المسافة بين نقطتين (x1, y1) و (x2, y2) هي الجذر التربيعي للمقدار (x2 − x1)² + (y2 − y1)².\n\n"
         "اكتب دالة `double jarak(int x1, int y1, int x2, int y2)`. دالة الجذر التربيعي `sqrt` موجودة في `<cmath>`.\n\n"
         "في `main` اقرأ رؤوس مثلث الثلاثة ثم اطبع محيطه برقمين بعد الفاصلة. تُستدعى الدالة `jarak` ثلاث مرات، مرة لكل ضلع.\n\n"
         "مثال: النقاط (0, 0) و (3, 0) و (0, 4) تشكّل مثلثًا أضلاعه 3 و 4 و 5، فمحيطه `12.00`.",
         "ثلاثة أسطر في كل منها x و y لأحد الرؤوس.", "محيط المثلث برقمين بعد الفاصلة.", "كل الإحداثيات أعداد صحيحة بين −20 و 20؛ النقاط الثلاث مختلفة."))

# ================================= SULIT (4) =================================

soal("kalkulator-pecahan", "sulit", "trimmed",
     "a, b, c, d = [r.randint(1, 100) for _ in range(4)]\nop = '+-*/'[i % 4]\nif i == 4:\n    a, b, c, d, op = 3, 7, 3, 7, '-'\nif i == 5:\n    c, d, op = b, a, '*'\nif i == 6:\n    a, b, c, d, op = 1, 3, 1, 2, '-'\nif i == 7:\n    b, d = 1, 1\nprint(f'{a}/{b} {op} {c}/{d}')\n",
     program(r'''
int fpb(int a, int b) {
    while (b != 0) {
        int sisa = a % b;
        a = b;
        b = sisa;
    }
    return a;
}

void sederhanakan(int &pembilang, int &penyebut) {
    int positif = pembilang;
    if (positif < 0) {
        positif = -positif;
    }
    int f = fpb(positif, penyebut);
    pembilang /= f;
    penyebut /= f;
}''', r'''    int a, b, c, d;
    char garis, op;
    cin >> a >> garis >> b >> op >> c >> garis >> d;
    int p, q;
    if (op == '+') {
        p = a * d + c * b;
        q = b * d;
    } else if (op == '-') {
        p = a * d - c * b;
        q = b * d;
    } else if (op == '*') {
        p = a * c;
        q = b * d;
    } else {
        p = a * d;
        q = b * c;
    }
    sederhanakan(p, q);
    if (q == 1) {
        cout << p << endl;
    } else {
        cout << p << "/" << q << endl;
    }'''),
     id=("Kalkulator Pecahan", "Buat kalkulator untuk dua pecahan. Masukannya berbentuk `a/b op c/d`, misalnya `1/2 + 1/3`, dengan `op` salah satu dari `+`, `-`, `*`, `/`.\n\n"
         "Hasilnya harus berupa pecahan **paling sederhana**:\n\n- pembilang dan penyebut tidak punya faktor persekutuan selain 1;\n- tanda minus, bila ada, ditulis di depan pembilang;\n- bila penyebutnya 1, cukup tulis pembilangnya saja.\n\n"
         "Tulis fungsi `int fpb(int a, int b)` dan `void sederhanakan(int &pembilang, int &penyebut)` yang menyederhanakan pecahan milik pemanggil.\n\n"
         "Contoh:\n\n- `1/2 + 1/3` → `5/6`\n- `1/3 - 1/2` → `-1/6`\n- `3/4 * 2/3` → `1/2`\n- `1/2 / 1/4` → `2`\n- `3/7 - 3/7` → `0`\n\n"
         "Petunjuk membaca masukan: `cin >> a >> garis >> b >> op >> c >> garis >> d` dengan `garis` dan `op` bertipe `char`.",
         "Satu baris: pecahan pertama, operator, pecahan kedua, dipisah spasi.", "Hasilnya dalam bentuk paling sederhana.", "Semua pembilang dan penyebut di masukan antara 1 dan 100."),
     en=("Fraction Calculator", "Build a calculator for two fractions. The input has the form `a/b op c/d`, for example `1/2 + 1/3`, where `op` is one of `+`, `-`, `*`, `/`.\n\n"
         "The result must be a fraction **in lowest terms**:\n\n- numerator and denominator have no common factor other than 1;\n- a minus sign, if any, is written in front of the numerator;\n- when the denominator is 1, write the numerator only.\n\n"
         "Write the functions `int fpb(int a, int b)` (greatest common divisor) and `void sederhanakan(int &pembilang, int &penyebut)`, which reduces the caller's fraction.\n\n"
         "Examples:\n\n- `1/2 + 1/3` → `5/6`\n- `1/3 - 1/2` → `-1/6`\n- `3/4 * 2/3` → `1/2`\n- `1/2 / 1/4` → `2`\n- `3/7 - 3/7` → `0`\n\n"
         "Hint for reading the input: `cin >> a >> garis >> b >> op >> c >> garis >> d` with `garis` and `op` of type `char`.",
         "One line: the first fraction, the operator, the second fraction, separated by spaces.", "The result in lowest terms.", "Every numerator and denominator in the input is between 1 and 100."),
     ar=("حاسبة الكسور", "اصنع آلة حاسبة لكسرين. المدخل على الصورة `a/b op c/d`، مثل `1/2 + 1/3`، حيث `op` أحد `+` و `-` و `*` و `/`.\n\n"
         "يجب أن يكون الناتج كسرًا **في أبسط صورة**:\n\n- لا عامل مشترك بين البسط والمقام غير 1؛\n- إشارة السالب، إن وُجدت، تُكتب أمام البسط؛\n- إذا كان المقام 1 فاكتب البسط وحده.\n\n"
         "اكتب الدالتين `int fpb(int a, int b)` (القاسم المشترك الأكبر) و `void sederhanakan(int &pembilang, int &penyebut)` التي تبسّط كسر المستدعي.\n\n"
         "أمثلة:\n\n- `1/2 + 1/3` ← `5/6`\n- `1/3 - 1/2` ← `-1/6`\n- `3/4 * 2/3` ← `1/2`\n- `1/2 / 1/4` ← `2`\n- `3/7 - 3/7` ← `0`\n\n"
         "تلميح لقراءة المدخل: `cin >> a >> garis >> b >> op >> c >> garis >> d` مع `garis` و `op` من النوع `char`.",
         "سطر واحد: الكسر الأول ثم العامل ثم الكسر الثاني، تفصل بينها مسافات.", "الناتج في أبسط صورة.", "كل بسط ومقام في المدخل بين 1 و 100."))

soal("goldbach", "sulit", "trimmed", "print({2: 4, 3: 100000, 4: 6, 5: 98, 6: 63274}.get(i, 2 * r.randint(4, 50000)))\n",
     program(r'''
bool prima(int n) {
    if (n < 2) {
        return false;
    }
    for (int k = 2; k * k <= n; k++) {
        if (n % k == 0) {
            return false;
        }
    }
    return true;
}''', r'''    int n;
    cin >> n;
    for (int p = 2; p <= n / 2; p++) {
        if (prima(p) && prima(n - p)) {
            cout << p << " " << n - p << endl;
            break;
        }
    }'''),
     id=("Dugaan Goldbach", "Dugaan Goldbach menyatakan bahwa setiap bilangan genap yang lebih besar dari 2 dapat ditulis sebagai jumlah dua bilangan prima. Belum ada yang berhasil membuktikannya, tetapi untuk bilangan sebesar apa pun yang pernah dicoba, dugaan itu selalu benar.\n\n"
         "Tulis fungsi `bool prima(int n)`. Di `main`, baca bilangan genap n, lalu cetak dua bilangan prima p dan q dengan p + q = n dan p ≤ q. Bila ada beberapa pasangan, cetak yang **p-nya paling kecil**.\n\n"
         "Contoh: 28 → `5 23` (3 + 25 tidak bisa karena 25 bukan prima); 4 → `2 2`; 100 → `3 97`.",
         "Satu bilangan genap n.", "Satu baris: p dan q.", "4 ≤ n ≤ 100000; n genap"),
     en=("Goldbach's Conjecture", "Goldbach's conjecture says that every even number greater than 2 can be written as the sum of two primes. Nobody has proved it yet, but for every number ever tried it has held.\n\n"
         "Write a function `bool prima(int n)` (is n prime). In `main`, read an even number n, then print two primes p and q with p + q = n and p ≤ q. If there are several pairs, print the one with the **smallest p**.\n\n"
         "Examples: 28 → `5 23` (3 + 25 fails because 25 is not prime); 4 → `2 2`; 100 → `3 97`.",
         "One even number n.", "One line: p and q.", "4 ≤ n ≤ 100000; n is even"),
     ar=("حدسية غولدباخ", "تقول حدسية غولدباخ إن كل عدد زوجي أكبر من 2 يمكن كتابته مجموعَ عددين أوليين. لم يبرهنها أحد بعد، لكنها صحّت لكل عدد جُرِّب.\n\n"
         "اكتب دالة `bool prima(int n)` (هل n أولي). في `main` اقرأ عددًا زوجيًا n ثم اطبع عددين أوليين p و q بحيث p + q = n و p ≤ q. إذا وُجدت عدة أزواج فاطبع الزوج الذي فيه **p أصغر ما يمكن**.\n\n"
         "أمثلة: 28 ← `5 23` (لا يصلح 3 + 25 لأن 25 ليس أوليًا)؛ 4 ← `2 2`؛ 100 ← `3 97`.",
         "عدد زوجي واحد n.", "سطر واحد: p و q.", "4 ≤ n ≤ 100000؛ n زوجي"))

soal("luhn", "sulit", "trimmed",
     "def cek(d):\n    t = 0\n    for k, c in enumerate(reversed(d)):\n        x = int(c)\n        if k % 2 == 1:\n            x = x * 2 - (9 if x > 4 else 0)\n        t += x\n    return t % 10 == 0\n"
     "badan = ''.join(r.choice('0123456789') for _ in range(r.randint(7, 18)))\nsah = [badan + str(c) for c in range(10) if cek(badan + str(c))][0]\nif i % 2 == 0:\n    nomor = sah\nelse:\n    p = r.randint(0, len(sah) - 1)\n    nomor = sah[:p] + str((int(sah[p]) + r.randint(1, 9)) % 10) + sah[p + 1:]\nprint(nomor)\n",
     program(r'''
bool luhn(string nomor) {
    int jumlah = 0;
    bool gandakan = false;
    for (int k = (int) nomor.size() - 1; k >= 0; k--) {
        int d = nomor[k] - '0';
        if (gandakan) {
            d = d * 2;
            if (d > 9) {
                d -= 9;
            }
        }
        jumlah += d;
        gandakan = !gandakan;
    }
    return jumlah % 10 == 0;
}''', r'''    string nomor;
    cin >> nomor;
    if (luhn(nomor)) {
        cout << "sah" << endl;
    } else {
        cout << "tidak sah" << endl;
    }''', "string"),
     id=("Algoritma Luhn", "Nomor kartu pembayaran punya digit pengaman di ujungnya, supaya salah ketik satu digit langsung ketahuan. Pemeriksaannya disebut algoritma Luhn:\n\n"
         "1. Mulai dari digit **paling kanan**, berjalan ke kiri.\n2. Digit pertama (paling kanan) dipakai apa adanya; digit kedua **digandakan**; digit ketiga apa adanya; digit keempat digandakan; dan seterusnya berselang-seling.\n"
         "3. Bila hasil penggandaan lebih dari 9, kurangi 9 (misalnya 8 → 16 → 7).\n4. Jumlahkan semuanya. Nomor itu sah bila jumlahnya habis dibagi 10.\n\n"
         "Contoh: `79927398713`. Dari kanan: 3, 1→2, 7, 8→7, 9, 3→6, 7, 2→4, 9, 9→9, 7. Jumlahnya 70, jadi nomor itu sah. Nomor `79927398710` tidak sah (jumlahnya 67).\n\n"
         "Tulis fungsi `bool luhn(string nomor)`. Di `main`, baca nomornya **sebagai teks** (terlalu panjang untuk `int`), lalu cetak `sah` atau `tidak sah`.\n\nPetunjuk: nilai sebuah karakter digit `c` adalah `c - '0'`.",
         "Satu baris berisi nomor: deretan digit tanpa spasi.", "`sah` atau `tidak sah`.", "Panjang nomor 8 sampai 19 digit."),
     en=("The Luhn Algorithm", "Payment card numbers end with a check digit, so that one mistyped digit is noticed at once. The check is called the Luhn algorithm:\n\n"
         "1. Start at the **rightmost** digit and walk to the left.\n2. The first digit (rightmost) is used as it is; the second is **doubled**; the third as it is; the fourth doubled; and so on, alternating.\n"
         "3. If a doubled digit is more than 9, subtract 9 (for example 8 → 16 → 7).\n4. Add everything up. The number is valid when the sum is divisible by 10.\n\n"
         "Example: `79927398713`. From the right: 3, 1→2, 7, 8→7, 9, 3→6, 7, 2→4, 9, 9→9, 7. The sum is 70, so the number is valid. The number `79927398710` is not valid (the sum is 67).\n\n"
         "Write a function `bool luhn(string nomor)`. In `main`, read the number **as text** (it is too long for `int`), then print `sah` (valid) or `tidak sah` (not valid).\n\nHint: the value of a digit character `c` is `c - '0'`.",
         "One line with the number: a run of digits without spaces.", "`sah` or `tidak sah`.", "The number is 8 to 19 digits long."),
     ar=("خوارزمية لون", "تنتهي أرقام بطاقات الدفع برقم تحقق، حتى يُكتشف فورًا أي خطأ في كتابة رقم واحد. يسمى هذا الفحص خوارزمية لون (Luhn):\n\n"
         "1. ابدأ من الرقم **الأيمن** واتجه يسارًا.\n2. الرقم الأول (الأيمن) يؤخذ كما هو؛ الثاني **يُضاعف**؛ الثالث كما هو؛ الرابع يُضاعف؛ وهكذا بالتناوب.\n"
         "3. إذا زاد الرقم المضاعف على 9 فاطرح منه 9 (مثل 8 ← 16 ← 7).\n4. اجمع الكل. يكون الرقم صحيحًا إذا قبل المجموع القسمة على 10.\n\n"
         "مثال: `79927398713`. من اليمين: 3، 1←2، 7، 8←7، 9، 3←6، 7، 2←4، 9، 9←9، 7. المجموع 70، فالرقم صحيح. أما `79927398710` فغير صحيح (المجموع 67).\n\n"
         "اكتب دالة `bool luhn(string nomor)`. في `main` اقرأ الرقم **نصًّا** (فهو أطول من أن يسعه `int`) ثم اطبع `sah` (صحيح) أو `tidak sah` (غير صحيح).\n\nتلميح: قيمة محرف الرقم `c` هي `c - '0'`.",
         "سطر واحد فيه الرقم: سلسلة أرقام بلا مسافات.", "`sah` أو `tidak sah`.", "طول الرقم من 8 إلى 19 رقمًا."))

soal("romawi-ke-angka", "sulit", "trimmed",
     ROMAWI + "n = {2: 4, 3: 1994, 4: 3999, 5: 49, 6: 944, 7: 3888, 8: 1}.get(i, r.randint(1, 3999))\nprint(romawi(n))\n",
     program(r'''
int nilai(char c) {
    switch (c) {
        case 'I': return 1;
        case 'V': return 5;
        case 'X': return 10;
        case 'L': return 50;
        case 'C': return 100;
        case 'D': return 500;
        default: return 1000;
    }
}''', r'''    string s;
    cin >> s;
    int n = s.size();
    int hasil = 0;
    for (int k = 0; k < n; k++) {
        if (k + 1 < n && nilai(s[k]) < nilai(s[k + 1])) {
            hasil -= nilai(s[k]);
        } else {
            hasil += nilai(s[k]);
        }
    }
    cout << hasil << endl;''', "string"),
     id=("Angka Romawi ke Bilangan", "Kebalikan dari soal pertemuan lalu: baca sebuah angka Romawi, lalu cetak nilainya sebagai bilangan biasa.\n\n"
         "Nilai lambangnya: `I` = 1, `V` = 5, `X` = 10, `L` = 50, `C` = 100, `D` = 500, `M` = 1000.\n\n"
         "Aturannya sederhana: lambang-lambang dijumlahkan, **kecuali** lambang yang nilainya lebih kecil daripada lambang tepat di sebelah kanannya — lambang seperti itu dikurangkan. "
         "Pada `XIV`, `I` berada di kiri `V` yang lebih besar, sehingga nilainya 10 − 1 + 5 = 14.\n\n"
         "Tulis fungsi `int nilai(char c)` yang mengembalikan nilai satu lambang, lalu pakai di `main`.\n\n"
         "Contoh: `XLIX` → 49; `MCMXCIV` → 1994; `MMXXVI` → 2026.",
         "Satu baris: angka Romawi yang ditulis dengan benar, huruf besar.", "Satu bilangan bulat: nilainya.", "Nilainya antara 1 dan 3999."),
     en=("Roman Numerals to a Number", "The reverse of a problem from an earlier meeting: read a Roman numeral, then print its value as an ordinary number.\n\n"
         "The symbol values: `I` = 1, `V` = 5, `X` = 10, `L` = 50, `C` = 100, `D` = 500, `M` = 1000.\n\n"
         "The rule is simple: the symbols are added up, **except** a symbol worth less than the symbol directly to its right — such a symbol is subtracted. "
         "In `XIV` the `I` stands left of the larger `V`, so the value is 10 − 1 + 5 = 14.\n\n"
         "Write a function `int nilai(char c)` that returns the value of one symbol, then use it in `main`.\n\n"
         "Examples: `XLIX` → 49; `MCMXCIV` → 1994; `MMXXVI` → 2026.",
         "One line: a correctly written Roman numeral in capital letters.", "One integer: its value.", "The value is between 1 and 3999."),
     ar=("من الأرقام الرومانية إلى عدد", "عكس مسألة من لقاء سابق: اقرأ عددًا بالأرقام الرومانية ثم اطبع قيمته عددًا عاديًا.\n\n"
         "قيم الرموز: `I` = 1، `V` = 5، `X` = 10، `L` = 50، `C` = 100، `D` = 500، `M` = 1000.\n\n"
         "القاعدة بسيطة: تُجمع الرموز، **إلا** الرمز الذي قيمته أصغر من الرمز الذي على يمينه مباشرة — فمثل هذا الرمز يُطرح. "
         "في `XIV` يقع `I` إلى يسار `V` الأكبر منه، فالقيمة 10 − 1 + 5 = 14.\n\n"
         "اكتب دالة `int nilai(char c)` تُرجع قيمة رمز واحد ثم استعملها في `main`.\n\n"
         "أمثلة: `XLIX` ← 49؛ `MCMXCIV` ← 1994؛ `MMXXVI` ← 2026.",
         "سطر واحد: عدد روماني مكتوب بصورة صحيحة وبحروف كبيرة.", "عدد صحيح واحد: قيمته.", "القيمة بين 1 و 3999."))

tulis(__file__, "DDP101", 6, "cpp", {"mudah_sekali": 8, "mudah": 8, "mudah_sedang": 6, "sedang": 6, "sulit": 4})
