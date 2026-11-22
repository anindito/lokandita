#!/usr/bin/env python3
"""Menyusun docs/soal/ddp101-pertemuan-5.json.

DDP101 pertemuan 5 — Struktur Kontrol: Perulangan. Konsep yang diizinkan:
konsep pertemuan 1–4 ditambah PERULANGAN, NESTED_LOOP, BREAK_CONTINUE. Solusi
referensi TIDAK memakai array atau fungsi buatan sendiri; kumpulan data dibaca
dan diolah satu per satu. 32 soal: 8 / 8 / 6 / 6 / 4.

Silabus pertemuan 5: for, while, do-while, break dan continue, perulangan
bersarang, pola perulangan umum; contoh: pola dengan nested loop, statistik
sederhana dari sekumpulan data, pencarian nilai dalam kumpulan data.

Soal pola menuntut spasi di depan baris persis; spasi di ujung baris diabaikan
(mode `trimmed`). Kata tetap yang dicetak program sama di ketiga bahasa soal.

    python3 docs/soal/buat-ddp101-pertemuan-5.py
"""
from bangun import cpp, soal, tulis

KATA = "KATA = ['belajar', 'program', 'komputer', 'kampus', 'buku', 'meja', 'layar', 'kode', 'data', 'angka', 'huruf', 'kelas', 'tugas', 'ujian', 'pagi', 'malam', 'cepat', 'rapi', 'benar', 'coba', 'ilmu', 'usaha', 'ide', 'aula']\n"
ACAK = "def acak(a, b):\n    return ''.join(r.choice('abcdefghijklmnopqrstuvwxyz') for _ in range(r.randint(a, b)))\n"
PRIMA = "def prima(n):\n    return n > 1 and all(n % d for d in range(2, int(n ** 0.5) + 1))\n"

# ============================== MUDAH SEKALI (8) ==============================

soal("hitung-sampai-n", "mudah_sekali", "trimmed", "print({2: 1}.get(i, r.randint(2, 10) if i % 2 == 0 else r.randint(11, 20)))\n",
     cpp(r'''    int n;
    cin >> n;
    for (int k = 1; k <= n; k++) {
        cout << k << endl;
    }'''),
     id=("Hitung Sampai n", "Baca sebuah bilangan n, lalu cetak bilangan 1 sampai n, masing-masing di baris sendiri.",
         "Satu bilangan bulat n.", "n baris: 1, 2, …, n.", "1 ≤ n ≤ 20"),
     en=("Count up to n", "Read a number n, then print the numbers 1 to n, each on its own line.",
         "One integer n.", "n lines: 1, 2, …, n.", "1 ≤ n ≤ 20"),
     ar=("العدّ حتى n", "اقرأ عددًا n ثم اطبع الأعداد من 1 إلى n، كل عدد في سطر.",
         "عدد صحيح واحد n.", "n سطرًا: 1، 2، …، n.", "1 ≤ n ≤ 20"))

soal("hitung-mundur", "mudah_sekali", "trimmed", "print({2: 1}.get(i, r.randint(2, 10) if i % 2 == 0 else r.randint(11, 20)))\n",
     cpp(r'''    int n;
    cin >> n;
    for (int k = n; k >= 1; k--) {
        cout << k << " ";
    }
    cout << endl;'''),
     id=("Hitung Mundur", "Baca sebuah bilangan n, lalu cetak hitungan mundur dari n sampai 1 **dalam satu baris**, dipisah satu spasi.\n\nUntuk n = 5 keluarannya `5 4 3 2 1`.",
         "Satu bilangan bulat n.", "Satu baris: n, n−1, …, 1.", "1 ≤ n ≤ 20"),
     en=("Countdown", "Read a number n, then print a countdown from n to 1 **on one line**, separated by single spaces.\n\nFor n = 5 the output is `5 4 3 2 1`.",
         "One integer n.", "One line: n, n−1, …, 1.", "1 ≤ n ≤ 20"),
     ar=("العدّ التنازلي", "اقرأ عددًا n ثم اطبع عدًّا تنازليًا من n إلى 1 **في سطر واحد** تفصل بين الأعداد مسافة واحدة.\n\nعند n = 5 يكون المخرج `5 4 3 2 1`.",
         "عدد صحيح واحد n.", "سطر واحد: n، n−1، …، 1.", "1 ≤ n ≤ 20"))

soal("jumlah-n-bilangan", "mudah_sekali", "trimmed", "n = r.randint(1, 10)\nprint(n)\nprint(*[r.randint(-100, 100) for _ in range(n)])\n",
     cpp(r'''    int n, x, jumlah = 0;
    cin >> n;
    for (int k = 0; k < n; k++) {
        cin >> x;
        jumlah += x;
    }
    cout << jumlah << endl;'''),
     id=("Jumlah n Bilangan", "Baca banyak bilangan n, lalu n bilangan bulat. Cetak jumlah semuanya.\n\nBilangan-bilangan itu tidak perlu disimpan: cukup dibaca satu per satu dan langsung ditambahkan.\n\nUntuk bilangan `4 -1 7` jumlahnya 10.",
         "Baris pertama: n. Baris kedua: n bilangan bulat.", "Satu bilangan: jumlahnya.", "1 ≤ n ≤ 10; tiap bilangan antara −100 dan 100"),
     en=("Sum of n Numbers", "Read a count n, then n integers. Print the sum of all of them.\n\nThe numbers need not be stored: read them one at a time and add each straight away.\n\nFor the numbers `4 -1 7` the sum is 10.",
         "First line: n. Second line: n integers.", "One number: the sum.", "1 ≤ n ≤ 10; each number is between −100 and 100"),
     ar=("مجموع n عددًا", "اقرأ العدد n ثم n عددًا صحيحًا. اطبع مجموعها كلها.\n\nلا حاجة إلى تخزين الأعداد: اقرأها واحدًا واحدًا وأضف كلًّا منها فورًا.\n\nللأعداد `4 -1 7` المجموع 10.",
         "السطر الأول: n. السطر الثاني: n عددًا صحيحًا.", "عدد واحد: المجموع.", "1 ≤ n ≤ 10؛ كل عدد بين −100 و 100"))

soal("garis-bintang", "mudah_sekali", "trimmed", "print({2: 1}.get(i, r.randint(2, 20) if i % 2 == 0 else r.randint(21, 40)))\n",
     cpp(r'''    int n;
    cin >> n;
    for (int k = 0; k < n; k++) {
        cout << "*";
    }
    cout << endl;'''),
     id=("Garis Bintang", "Baca sebuah bilangan n, lalu cetak satu baris berisi n tanda bintang (`*`) tanpa spasi.\n\nUntuk n = 6 keluarannya `******`.",
         "Satu bilangan bulat n.", "Satu baris berisi n bintang.", "1 ≤ n ≤ 40"),
     en=("Line of Stars", "Read a number n, then print one line of n asterisks (`*`) without spaces.\n\nFor n = 6 the output is `******`.",
         "One integer n.", "One line of n asterisks.", "1 ≤ n ≤ 40"),
     ar=("خط من النجوم", "اقرأ عددًا n ثم اطبع سطرًا واحدًا فيه n نجمة (`*`) بلا مسافات.\n\nعند n = 6 يكون المخرج `******`.",
         "عدد صحيح واحد n.", "سطر واحد فيه n نجمة.", "1 ≤ n ≤ 40"))

soal("tabel-perkalian", "mudah_sekali", "trimmed", "print(r.randint(1, 10) if i % 2 == 0 else r.randint(11, 20))\n",
     cpp(r'''    int n;
    cin >> n;
    for (int k = 1; k <= 10; k++) {
        cout << n << " x " << k << " = " << n * k << endl;
    }'''),
     id=("Tabel Perkalian", "Baca sebuah bilangan n, lalu cetak tabel perkaliannya dari 1 sampai 10. Tiap baris berbentuk `n x k = hasil`.\n\nUntuk n = 7, dua baris pertamanya:\n\n```\n7 x 1 = 7\n7 x 2 = 14\n```\n\ndan baris terakhirnya `7 x 10 = 70`.",
         "Satu bilangan bulat n.", "Sepuluh baris tabel perkalian n.", "1 ≤ n ≤ 20"),
     en=("Multiplication Table", "Read a number n, then print its multiplication table from 1 to 10. Each line has the form `n x k = result`.\n\nFor n = 7 the first two lines are:\n\n```\n7 x 1 = 7\n7 x 2 = 14\n```\n\nand the last line is `7 x 10 = 70`.",
         "One integer n.", "The ten lines of the multiplication table of n.", "1 ≤ n ≤ 20"),
     ar=("جدول الضرب", "اقرأ عددًا n ثم اطبع جدول ضربه من 1 إلى 10. كل سطر على الصورة `n x k = الناتج`.\n\nعند n = 7 يكون السطران الأولان:\n\n```\n7 x 1 = 7\n7 x 2 = 14\n```\n\nوالسطر الأخير `7 x 10 = 70`.",
         "عدد صحيح واحد n.", "الأسطر العشرة لجدول ضرب n.", "1 ≤ n ≤ 20"))

soal("genap-sampai-n", "mudah_sekali", "trimmed", "print({2: 2, 3: 3}.get(i, r.randint(4, 25) if i % 2 == 0 else r.randint(26, 50)))\n",
     cpp(r'''    int n;
    cin >> n;
    for (int k = 2; k <= n; k += 2) {
        cout << k << " ";
    }
    cout << endl;'''),
     id=("Bilangan Genap sampai n", "Baca sebuah bilangan n, lalu cetak semua bilangan genap dari 2 sampai n dalam satu baris, dipisah satu spasi.\n\nUntuk n = 9 keluarannya `2 4 6 8`.",
         "Satu bilangan bulat n.", "Satu baris: bilangan genap dari 2 sampai n, urut naik.", "2 ≤ n ≤ 50"),
     en=("Even Numbers up to n", "Read a number n, then print every even number from 2 to n on one line, separated by single spaces.\n\nFor n = 9 the output is `2 4 6 8`.",
         "One integer n.", "One line: the even numbers from 2 to n, ascending.", "2 ≤ n ≤ 50"),
     ar=("الأعداد الزوجية حتى n", "اقرأ عددًا n ثم اطبع كل الأعداد الزوجية من 2 إلى n في سطر واحد تفصل بينها مسافة واحدة.\n\nعند n = 9 يكون المخرج `2 4 6 8`.",
         "عدد صحيح واحد n.", "سطر واحد: الأعداد الزوجية من 2 إلى n تصاعديًا.", "2 ≤ n ≤ 50"))

soal("pangkat", "mudah_sekali", "trimmed", "a, b = r.randint(1, 10), r.randint(1, 9)\nif i == 2:\n    b = 0\nif i == 3:\n    a, b = 10, 9\nprint(a, b)\n",
     cpp(r'''    int a, b;
    cin >> a >> b;
    int hasil = 1;
    for (int k = 0; k < b; k++) {
        hasil *= a;
    }
    cout << hasil << endl;'''),
     id=("Pangkat", "Baca dua bilangan a dan b, lalu cetak **a pangkat b**: a dikalikan dengan dirinya sendiri sebanyak b kali.\n\nUntuk `2 5` jawabannya 32. Bilangan apa pun pangkat 0 adalah 1.\n\nKerjakan dengan perulangan, tanpa fungsi `pow`.",
         "Satu baris berisi a dan b.", "Satu bilangan: a pangkat b.", "1 ≤ a ≤ 10; 0 ≤ b ≤ 9"),
     en=("Power", "Read two numbers a and b, then print **a to the power b**: a multiplied by itself b times.\n\nFor `2 5` the answer is 32. Any number to the power 0 is 1.\n\nUse a loop, not the `pow` function.",
         "One line with a and b.", "One number: a to the power b.", "1 ≤ a ≤ 10; 0 ≤ b ≤ 9"),
     ar=("الأُسّ", "اقرأ عددين a و b ثم اطبع **a أُسّ b**: أي a مضروبًا في نفسه b مرة.\n\nعند `2 5` الجواب 32. أي عدد أُسّ 0 يساوي 1.\n\nاستخدم حلقة ولا تستخدم الدالة `pow`.",
         "سطر واحد فيه a و b.", "عدد واحد: a أُسّ b.", "1 ≤ a ≤ 10؛ 0 ≤ b ≤ 9"))

soal("hitung-positif", "mudah_sekali", "trimmed",
     "n = r.randint(1, 12)\nv = [r.randint(-50, 50) for _ in range(n)]\nif i == 2:\n    v = [-abs(x) for x in v]\nif i == 3:\n    v[0] = 0\nprint(n)\nprint(*v)\n",
     cpp(r'''    int n, x, banyak = 0;
    cin >> n;
    for (int k = 0; k < n; k++) {
        cin >> x;
        if (x > 0) {
            banyak++;
        }
    }
    cout << banyak << endl;'''),
     id=("Hitung yang Positif", "Baca n bilangan bulat, lalu cetak berapa banyak di antaranya yang **positif** (lebih besar dari nol). Nol tidak termasuk positif.\n\nUntuk bilangan `3 -2 0 8` jawabannya 2.",
         "Baris pertama: n. Baris kedua: n bilangan bulat.", "Satu bilangan: banyak bilangan positif.", "1 ≤ n ≤ 12; tiap bilangan antara −50 dan 50"),
     en=("Count the Positives", "Read n integers, then print how many of them are **positive** (greater than zero). Zero is not positive.\n\nFor the numbers `3 -2 0 8` the answer is 2.",
         "First line: n. Second line: n integers.", "One number: how many are positive.", "1 ≤ n ≤ 12; each number is between −50 and 50"),
     ar=("عدّ الموجبة", "اقرأ n عددًا صحيحًا ثم اطبع كم منها **موجب** (أكبر من الصفر). الصفر ليس موجبًا.\n\nللأعداد `3 -2 0 8` الجواب 2.",
         "السطر الأول: n. السطر الثاني: n عددًا صحيحًا.", "عدد واحد: عدد الأعداد الموجبة.", "1 ≤ n ≤ 12؛ كل عدد بين −50 و 50"))

# ================================= MUDAH (8) =================================

soal("rata-rata-n", "mudah", "trimmed",
     "while True:\n    n = r.randint(2, 10)\n    v = [r.randint(0, 100) for _ in range(n)]\n    if abs(sum(v) * 100 / n % 1 - 0.5) > 1e-3:\n        break\nprint(n)\nprint(*v)\n",
     cpp(r'''    int n, x, jumlah = 0;
    cin >> n;
    for (int k = 0; k < n; k++) {
        cin >> x;
        jumlah += x;
    }
    cout << fixed << setprecision(2) << (double) jumlah / n << endl;''', "iomanip"),
     id=("Rata-rata n Nilai", "Baca n nilai ujian, lalu cetak rata-ratanya dengan dua angka di belakang koma.\n\nUntuk nilai `70 85 90` rata-ratanya `81.67`.\n\nHati-hati: jumlah dan banyak nilai adalah bilangan bulat, sehingga pembagiannya perlu diubah menjadi pembagian pecahan.",
         "Baris pertama: n. Baris kedua: n bilangan bulat.", "Rata-rata dengan dua angka di belakang koma.", "2 ≤ n ≤ 10; tiap nilai antara 0 dan 100"),
     en=("Average of n Marks", "Read n exam marks, then print their average with two digits after the decimal point.\n\nFor the marks `70 85 90` the average is `81.67`.\n\nCareful: the sum and the count are integers, so the division has to be turned into a real division.",
         "First line: n. Second line: n integers.", "The average with two digits after the decimal point.", "2 ≤ n ≤ 10; each mark is between 0 and 100"),
     ar=("متوسط n درجة", "اقرأ n درجة امتحان ثم اطبع متوسطها برقمين بعد الفاصلة.\n\nللدرجات `70 85 90` المتوسط `81.67`.\n\nانتبه: المجموع والعدد عددان صحيحان، فيجب تحويل القسمة إلى قسمة عشرية.",
         "السطر الأول: n. السطر الثاني: n عددًا صحيحًا.", "المتوسط برقمين بعد الفاصلة.", "2 ≤ n ≤ 10؛ كل درجة بين 0 و 100"))

soal("terbesar-dari-n", "mudah", "trimmed",
     "n = r.randint(1, 15)\nv = [r.randint(-1000, 1000) for _ in range(n)]\nif i == 2:\n    v = [-abs(x) - 1 for x in v]\nif i == 3:\n    v.sort()\nif i == 4:\n    v.sort(reverse=True)\nprint(n)\nprint(*v)\n",
     cpp(r'''    int n, x, besar;
    cin >> n;
    cin >> besar;
    for (int k = 1; k < n; k++) {
        cin >> x;
        if (x > besar) {
            besar = x;
        }
    }
    cout << besar << endl;'''),
     id=("Terbesar dari n Bilangan", "Baca n bilangan bulat, lalu cetak yang terbesar.\n\nSemua bilangan bisa saja negatif: untuk `-7 -2 -9` jawabannya −2. Karena itu, jangan memulai dari nol; mulailah dari bilangan pertama.",
         "Baris pertama: n. Baris kedua: n bilangan bulat.", "Satu bilangan: yang terbesar.", "1 ≤ n ≤ 15; tiap bilangan antara −1001 dan 1000"),
     en=("Largest of n Numbers", "Read n integers, then print the largest.\n\nAll the numbers may be negative: for `-7 -2 -9` the answer is −2. So do not start from zero; start from the first number.",
         "First line: n. Second line: n integers.", "One number: the largest.", "1 ≤ n ≤ 15; each number is between −1001 and 1000"),
     ar=("الأكبر من n عددًا", "اقرأ n عددًا صحيحًا ثم اطبع أكبرها.\n\nقد تكون الأعداد كلها سالبة: للأعداد `-7 -2 -9` الجواب −2. لذلك لا تبدأ من الصفر، بل ابدأ من العدد الأول.",
         "السطر الأول: n. السطر الثاني: n عددًا صحيحًا.", "عدد واحد: الأكبر.", "1 ≤ n ≤ 15؛ كل عدد بين −1001 و 1000"))

soal("jumlah-digit", "mudah", "trimmed", "print({2: 0, 3: 999999999, 4: 10 ** r.randint(1, 9)}.get(i, r.randint(1, 10 ** r.randint(1, 9))))\n",
     cpp(r'''    int n, jumlah = 0;
    cin >> n;
    while (n > 0) {
        jumlah += n % 10;
        n /= 10;
    }
    cout << jumlah << endl;'''),
     id=("Jumlah Digit", "Baca sebuah bilangan bulat tak negatif, lalu cetak jumlah semua digitnya.\n\nUntuk 4096 jawabannya 4 + 0 + 9 + 6 = 19.\n\nBanyak digitnya tidak diketahui lebih dulu, jadi `while` lebih cocok daripada `for`: ambil digit terakhir dengan `% 10`, buang dengan `/ 10`, ulangi sampai bilangannya habis.",
         "Satu bilangan bulat n.", "Jumlah digit n.", "0 ≤ n ≤ 1000000000"),
     en=("Sum of Digits", "Read a non-negative integer, then print the sum of all its digits.\n\nFor 4096 the answer is 4 + 0 + 9 + 6 = 19.\n\nThe number of digits is not known beforehand, so `while` suits better than `for`: take the last digit with `% 10`, drop it with `/ 10`, repeat until nothing is left.",
         "One integer n.", "The sum of the digits of n.", "0 ≤ n ≤ 1000000000"),
     ar=("مجموع الأرقام", "اقرأ عددًا صحيحًا غير سالب ثم اطبع مجموع أرقامه كلها.\n\nللعدد 4096 الجواب 4 + 0 + 9 + 6 = 19.\n\nعدد الأرقام غير معروف مسبقًا، فالحلقة `while` أنسب من `for`: خذ الرقم الأخير بـ `% 10`، واحذفه بـ `/ 10`، وكرِّر حتى ينتهي العدد.",
         "عدد صحيح واحد n.", "مجموع أرقام n.", "0 ≤ n ≤ 1000000000"))

soal("hitung-vokal", "mudah", "trimmed", KATA + "print(' '.join(r.choice(KATA) for _ in range(r.randint(1, 6))))\n",
     cpp(r'''    string baris;
    getline(cin, baris);
    int banyak = 0;
    for (int k = 0; k < (int) baris.size(); k++) {
        char c = baris[k];
        if (c == 'a' || c == 'i' || c == 'u' || c == 'e' || c == 'o') {
            banyak++;
        }
    }
    cout << banyak << endl;''', "string"),
     id=("Hitung Huruf Vokal", "Baca satu baris teks berhuruf kecil (bisa berisi spasi), lalu cetak berapa banyak huruf vokalnya (`a`, `i`, `u`, `e`, `o`).\n\nUntuk `buku tugas` jawabannya 4.",
         "Satu baris teks.", "Satu bilangan: banyak huruf vokal.", "Teks terdiri dari huruf kecil dan spasi, paling panjang 60 karakter."),
     en=("Count the Vowels", "Read one line of lowercase text (it may contain spaces), then print how many vowels it has (`a`, `i`, `u`, `e`, `o`).\n\nFor `buku tugas` the answer is 4.",
         "One line of text.", "One number: how many vowels.", "The text has lowercase letters and spaces, at most 60 characters."),
     ar=("عدّ حروف العلة", "اقرأ سطرًا واحدًا من نص بحروف صغيرة (قد يحتوي على مسافات) ثم اطبع عدد حروف العلة فيه (`a` و `i` و `u` و `e` و `o`).\n\nللنص `buku tugas` الجواب 4.",
         "سطر واحد من النص.", "عدد واحد: عدد حروف العلة.", "النص حروف لاتينية صغيرة ومسافات، 60 محرفًا على الأكثر."))

soal("persegi-bintang", "mudah", "trimmed", "print({2: 1}.get(i, r.randint(2, 5) if i % 2 == 0 else r.randint(6, 10)))\n",
     cpp(r'''    int n;
    cin >> n;
    for (int b = 0; b < n; b++) {
        for (int k = 0; k < n; k++) {
            cout << "*";
        }
        cout << endl;
    }'''),
     id=("Persegi Bintang", "Baca sebuah bilangan n, lalu cetak persegi berukuran n × n dari tanda bintang: n baris, masing-masing berisi n bintang tanpa spasi.\n\nUntuk n = 3:\n\n```\n***\n***\n***\n```\n\nPerulangan di dalam perulangan: yang luar untuk baris, yang dalam untuk bintang di satu baris.",
         "Satu bilangan bulat n.", "n baris, masing-masing n bintang.", "1 ≤ n ≤ 10"),
     en=("Square of Stars", "Read a number n, then print an n × n square of asterisks: n lines, each with n asterisks and no spaces.\n\nFor n = 3:\n\n```\n***\n***\n***\n```\n\nA loop inside a loop: the outer one for the lines, the inner one for the asterisks of one line.",
         "One integer n.", "n lines of n asterisks each.", "1 ≤ n ≤ 10"),
     ar=("مربع من النجوم", "اقرأ عددًا n ثم اطبع مربعًا n × n من النجوم: n سطرًا في كل منها n نجمة بلا مسافات.\n\nعند n = 3:\n\n```\n***\n***\n***\n```\n\nحلقة داخل حلقة: الخارجية للأسطر والداخلية لنجوم السطر الواحد.",
         "عدد صحيح واحد n.", "n سطرًا في كل منها n نجمة.", "1 ≤ n ≤ 10"))

soal("baca-sampai-nol", "mudah", "trimmed",
     "n = 0 if i == 2 else r.randint(1, 12)\nfor _ in range(n):\n    print(r.choice([-1, 1]) * r.randint(1, 100))\nprint(0)\n",
     cpp(r'''    int x, jumlah = 0;
    cin >> x;
    while (x != 0) {
        jumlah += x;
        cin >> x;
    }
    cout << jumlah << endl;'''),
     id=("Baca sampai Nol", "Kali ini banyak bilangan tidak diberitahukan. Baca bilangan bulat satu per satu **sampai bertemu 0**, lalu cetak jumlah semua bilangan sebelum 0 itu.\n\nUntuk masukan `5`, `-2`, `10`, `0` jawabannya 13. Bila bilangan pertama sudah 0, jawabannya 0.",
         "Beberapa baris, masing-masing satu bilangan bulat. Baris terakhir berisi 0.", "Satu bilangan: jumlah semua bilangan sebelum 0.", "Paling banyak 12 bilangan sebelum 0; tiap bilangan antara −100 dan 100"),
     en=("Read until Zero", "This time the count is not given. Read integers one at a time **until a 0 appears**, then print the sum of all the numbers before that 0.\n\nFor the input `5`, `-2`, `10`, `0` the answer is 13. If the first number is already 0, the answer is 0.",
         "Several lines with one integer each. The last line holds 0.", "One number: the sum of all numbers before the 0.", "At most 12 numbers before the 0; each number is between −100 and 100"),
     ar=("اقرأ حتى الصفر", "هذه المرة لا يُعطى عدد الأعداد. اقرأ أعدادًا صحيحة واحدًا واحدًا **حتى يظهر 0** ثم اطبع مجموع كل الأعداد التي قبله.\n\nللمدخل `5` ثم `-2` ثم `10` ثم `0` الجواب 13. إذا كان العدد الأول 0 فالجواب 0.",
         "عدة أسطر في كل منها عدد صحيح واحد. السطر الأخير فيه 0.", "عدد واحد: مجموع كل الأعداد التي قبل الصفر.", "12 عددًا على الأكثر قبل الصفر؛ كل عدد بين −100 و 100"))

soal("balik-kata", "mudah", "trimmed", ACAK + "print(acak(1, 15))\n",
     cpp(r'''    string kata;
    cin >> kata;
    for (int k = (int) kata.size() - 1; k >= 0; k--) {
        cout << kata[k];
    }
    cout << endl;''', "string"),
     id=("Balik Kata", "Baca sebuah kata, lalu cetak kata itu dengan urutan huruf terbalik.\n\nUntuk `program` keluarannya `margorp`.",
         "Satu kata dari huruf kecil.", "Kata itu, dibaca dari belakang.", "Panjang kata 1 sampai 15 huruf."),
     en=("Reverse a Word", "Read a word, then print it with its letters in reverse order.\n\nFor `program` the output is `margorp`.",
         "One word of lowercase letters.", "The word read backwards.", "The word is 1 to 15 letters long."),
     ar=("عكس كلمة", "اقرأ كلمة ثم اطبعها بترتيب حروف معكوس.\n\nللكلمة `program` يكون المخرج `margorp`.",
         "كلمة واحدة من حروف لاتينية صغيرة.", "الكلمة مقروءة من آخرها.", "طول الكلمة من 1 إلى 15 حرفًا."))

soal("jumlah-kelipatan", "mudah", "trimmed", "print({2: 1, 3: 3, 4: 1000, 5: 15}.get(i, r.randint(2, 1000)))\n",
     cpp(r'''    int n, jumlah = 0;
    cin >> n;
    for (int k = 1; k <= n; k++) {
        if (k % 3 == 0 || k % 5 == 0) {
            jumlah += k;
        }
    }
    cout << jumlah << endl;'''),
     id=("Jumlah Kelipatan 3 atau 5", "Baca sebuah bilangan n, lalu cetak jumlah semua bilangan dari 1 sampai n yang merupakan **kelipatan 3 atau kelipatan 5**. Bilangan yang kelipatan keduanya (misalnya 15) dihitung satu kali saja.\n\nUntuk n = 10 bilangannya 3, 5, 6, 9, 10, sehingga jawabannya 33.",
         "Satu bilangan bulat n.", "Satu bilangan: jumlahnya (0 bila tidak ada).", "1 ≤ n ≤ 1000"),
     en=("Sum of Multiples of 3 or 5", "Read a number n, then print the sum of all numbers from 1 to n that are **multiples of 3 or of 5**. A number that is a multiple of both (such as 15) is counted once only.\n\nFor n = 10 the numbers are 3, 5, 6, 9, 10, so the answer is 33.",
         "One integer n.", "One number: the sum (0 if there is none).", "1 ≤ n ≤ 1000"),
     ar=("مجموع مضاعفات 3 أو 5", "اقرأ عددًا n ثم اطبع مجموع كل الأعداد من 1 إلى n التي هي **من مضاعفات 3 أو من مضاعفات 5**. العدد الذي هو مضاعف لكليهما (مثل 15) يُحسب مرة واحدة فقط.\n\nعند n = 10 الأعداد هي 3، 5، 6، 9، 10، فالجواب 33.",
         "عدد صحيح واحد n.", "عدد واحد: المجموع (0 إن لم يوجد).", "1 ≤ n ≤ 1000"))

# ============================== MUDAH-SEDANG (6) ==============================

soal("segitiga-bintang", "mudah_sedang", "trimmed", "print({2: 1}.get(i, r.randint(2, 6) if i % 2 == 0 else r.randint(7, 12)))\n",
     cpp(r'''    int n;
    cin >> n;
    for (int b = 1; b <= n; b++) {
        for (int k = 0; k < b; k++) {
            cout << "*";
        }
        cout << endl;
    }'''),
     id=("Segitiga Bintang", "Baca sebuah bilangan n, lalu cetak segitiga siku-siku setinggi n baris: baris pertama berisi 1 bintang, baris kedua 2 bintang, dan seterusnya.\n\nUntuk n = 4:\n\n```\n*\n**\n***\n****\n```",
         "Satu bilangan bulat n.", "n baris; baris ke-b berisi b bintang.", "1 ≤ n ≤ 12"),
     en=("Triangle of Stars", "Read a number n, then print a right-angled triangle n lines high: the first line has 1 asterisk, the second has 2, and so on.\n\nFor n = 4:\n\n```\n*\n**\n***\n****\n```",
         "One integer n.", "n lines; line b has b asterisks.", "1 ≤ n ≤ 12"),
     ar=("مثلث من النجوم", "اقرأ عددًا n ثم اطبع مثلثًا قائم الزاوية ارتفاعه n سطرًا: في السطر الأول نجمة واحدة، وفي الثاني نجمتان، وهكذا.\n\nعند n = 4:\n\n```\n*\n**\n***\n****\n```",
         "عدد صحيح واحد n.", "n سطرًا؛ في السطر رقم b عدد b من النجوم.", "1 ≤ n ≤ 12"))

soal("bilangan-prima", "mudah_sedang", "trimmed",
     PRIMA + "n = r.randint(2, 1000000)\nif i % 2 == 0:\n    while not prima(n):\n        n -= 1\nn = {2: 1, 3: 2, 4: r.choice([49, 121, 961, 994009]), 5: 999983, 6: r.choice([9, 15, 21, 91])}.get(i, n)\nprint(n)\n",
     cpp(r'''    int n;
    cin >> n;
    bool prima = n > 1;
    for (int k = 2; k * k <= n; k++) {
        if (n % k == 0) {
            prima = false;
            break;
        }
    }
    if (prima) {
        cout << "prima" << endl;
    } else {
        cout << "bukan prima" << endl;
    }'''),
     id=("Bilangan Prima", "Bilangan prima adalah bilangan yang lebih besar dari 1 dan hanya habis dibagi 1 dan dirinya sendiri: 2, 3, 5, 7, 11, …\n\nBaca sebuah bilangan, lalu cetak `prima` atau `bukan prima`. Bilangan 1 bukan prima.\n\nBegitu satu pembagi ditemukan, perulangan tidak perlu diteruskan: hentikan dengan `break`.",
         "Satu bilangan bulat n.", "`prima` atau `bukan prima`.", "1 ≤ n ≤ 1000000"),
     en=("Prime Number", "A prime number is greater than 1 and divisible only by 1 and itself: 2, 3, 5, 7, 11, …\n\nRead a number, then print `prima` (prime) or `bukan prima` (not prime). The number 1 is not prime.\n\nAs soon as one divisor is found the loop need not go on: stop it with `break`.",
         "One integer n.", "`prima` or `bukan prima`.", "1 ≤ n ≤ 1000000"),
     ar=("العدد الأولي", "العدد الأولي عدد أكبر من 1 لا يقبل القسمة إلا على 1 وعلى نفسه: 2، 3، 5، 7، 11، …\n\nاقرأ عددًا ثم اطبع `prima` (أولي) أو `bukan prima` (غير أولي). العدد 1 ليس أوليًا.\n\nمتى وُجد قاسم واحد فلا حاجة إلى متابعة الحلقة: أوقفها بـ `break`.",
         "عدد صحيح واحد n.", "`prima` أو `bukan prima`.", "1 ≤ n ≤ 1000000"))

soal("fpb", "mudah_sedang", "trimmed",
     "g = r.randint(1, 1000)\nx, y = r.randint(1, 1000000 // g), r.randint(1, 1000000 // g)\nif i == 2:\n    y = x\nif i == 3:\n    g, x = 1, 1\nprint(g * x, g * y)\n",
     cpp(r'''    int a, b;
    cin >> a >> b;
    while (b != 0) {
        int sisa = a % b;
        a = b;
        b = sisa;
    }
    cout << a << endl;'''),
     id=("Faktor Persekutuan Terbesar", "Faktor persekutuan terbesar (FPB) dua bilangan adalah bilangan terbesar yang habis membagi keduanya. FPB dari 12 dan 18 adalah 6.\n\nBaca dua bilangan bulat positif, lalu cetak FPB-nya.\n\n"
         "Cara cepatnya (algoritma Euclid): selama b belum nol, ganti pasangan (a, b) dengan (b, a mod b). Ketika b menjadi nol, a adalah FPB-nya. Untuk 12 dan 18: (12, 18) → (18, 12) → (12, 6) → (6, 0).",
         "Satu baris berisi dua bilangan bulat a dan b.", "Satu bilangan: FPB dari a dan b.", "1 ≤ a, b ≤ 1000000"),
     en=("Greatest Common Divisor", "The greatest common divisor (FPB in Indonesian) of two numbers is the largest number that divides both. The greatest common divisor of 12 and 18 is 6.\n\nRead two positive integers, then print their greatest common divisor.\n\n"
         "The fast way (Euclid's algorithm): while b is not zero, replace the pair (a, b) by (b, a mod b). When b becomes zero, a is the answer. For 12 and 18: (12, 18) → (18, 12) → (12, 6) → (6, 0).",
         "One line with two integers a and b.", "One number: the greatest common divisor of a and b.", "1 ≤ a, b ≤ 1000000"),
     ar=("القاسم المشترك الأكبر", "القاسم المشترك الأكبر لعددين هو أكبر عدد يقسم كليهما. القاسم المشترك الأكبر للعددين 12 و 18 هو 6.\n\nاقرأ عددين صحيحين موجبين ثم اطبع قاسمهما المشترك الأكبر.\n\n"
         "الطريقة السريعة (خوارزمية إقليدس): ما دام b ليس صفرًا فاستبدل بالزوج (a, b) الزوج (b, a mod b). عندما يصير b صفرًا يكون a هو الجواب. للعددين 12 و 18: (12, 18) ← (18, 12) ← (12, 6) ← (6, 0).",
         "سطر واحد فيه عددان صحيحان a و b.", "عدد واحد: القاسم المشترك الأكبر للعددين.", "1 ≤ a, b ≤ 1000000"))

soal("cari-pertama", "mudah_sedang", "trimmed",
     "n = r.randint(1, 15)\nv = [r.randint(1, 12) for _ in range(n)]\nx = r.choice(v) if i % 3 != 2 else 13\nif i == 3:\n    v[-1] = x = 20\nif i == 4:\n    v[0] = x\nprint(n, x)\nprint(*v)\n",
     cpp(r'''    int n, x, nilai;
    cin >> n >> x;
    int posisi = 0;
    for (int k = 1; k <= n; k++) {
        cin >> nilai;
        if (nilai == x) {
            posisi = k;
            break;
        }
    }
    if (posisi == 0) {
        cout << "tidak ada" << endl;
    } else {
        cout << posisi << endl;
    }'''),
     id=("Cari yang Pertama", "Diberikan n bilangan dan sebuah bilangan x yang dicari. Cetak **posisi pertama** x di antara n bilangan itu; posisi dihitung mulai dari 1. Bila x tidak ada, cetak `tidak ada`.\n\n"
         "Contoh: mencari 7 di antara `4 7 1 7` menghasilkan 2. Mencari 9 di antara bilangan yang sama menghasilkan `tidak ada`.",
         "Baris pertama: n dan x. Baris kedua: n bilangan bulat.", "Posisi pertama x, atau `tidak ada`.", "1 ≤ n ≤ 15; semua bilangan antara 1 dan 20"),
     en=("Find the First", "You are given n numbers and a number x to look for. Print the **first position** of x among the n numbers; positions are counted from 1. If x does not occur, print `tidak ada` (not there).\n\n"
         "Example: looking for 7 among `4 7 1 7` gives 2. Looking for 9 among the same numbers gives `tidak ada`.",
         "First line: n and x. Second line: n integers.", "The first position of x, or `tidak ada`.", "1 ≤ n ≤ 15; all numbers are between 1 and 20"),
     ar=("ابحث عن الأول", "تُعطى n عددًا وعددًا x يُبحث عنه. اطبع **أول موضع** للعدد x بين الأعداد؛ المواضع تُعدّ من 1. إذا لم يوجد x فاطبع `tidak ada` (غير موجود).\n\n"
         "مثال: البحث عن 7 بين `4 7 1 7` يعطي 2. والبحث عن 9 بين الأعداد نفسها يعطي `tidak ada`.",
         "السطر الأول: n و x. السطر الثاني: n عددًا صحيحًا.", "أول موضع للعدد x، أو `tidak ada`.", "1 ≤ n ≤ 15؛ كل الأعداد بين 1 و 20"))

soal("balik-bilangan", "mudah_sedang", "trimmed",
     "n = r.randint(1, 10 ** r.randint(1, 9) - 1)\nif i == 2:\n    n = r.randint(1, 9999) * 100\nif i == 3:\n    n = r.randint(1, 9)\nif i == 4:\n    n = 999999999\nprint(n)\n",
     cpp(r'''    int n, balik = 0;
    cin >> n;
    while (n > 0) {
        balik = balik * 10 + n % 10;
        n /= 10;
    }
    cout << balik << endl;'''),
     id=("Balik Bilangan", "Baca sebuah bilangan bulat positif, lalu cetak bilangan yang digit-digitnya dibalik. Hasilnya adalah **bilangan**, jadi nol di depan tidak ditulis.\n\nContoh: 1234 menjadi 4321, dan 1200 menjadi 21.",
         "Satu bilangan bulat n.", "Bilangan n dengan urutan digit dibalik.", "1 ≤ n ≤ 999999999"),
     en=("Reverse a Number", "Read a positive integer, then print the number with its digits reversed. The result is a **number**, so leading zeros are not written.\n\nExamples: 1234 becomes 4321, and 1200 becomes 21.",
         "One integer n.", "The number n with its digits in reverse order.", "1 ≤ n ≤ 999999999"),
     ar=("عكس عدد", "اقرأ عددًا صحيحًا موجبًا ثم اطبع العدد بعد عكس ترتيب أرقامه. الناتج **عدد**، فلا تُكتب الأصفار في أوله.\n\nأمثلة: 1234 يصير 4321، و 1200 يصير 21.",
         "عدد صحيح واحد n.", "العدد n بترتيب أرقام معكوس.", "1 ≤ n ≤ 999999999"))

soal("langkah-collatz", "mudah_sedang", "trimmed", "print({2: 1, 3: 27, 4: 6171, 5: 2 ** r.randint(1, 13)}.get(i, r.randint(2, 10000)))\n",
     cpp(r'''    int n, langkah = 0;
    cin >> n;
    while (n != 1) {
        if (n % 2 == 0) {
            n /= 2;
        } else {
            n = 3 * n + 1;
        }
        langkah++;
    }
    cout << langkah << endl;'''),
     id=("Langkah Collatz", "Mulai dari sebuah bilangan n, ulangi langkah berikut sampai bilangannya menjadi 1:\n\n- bila genap, bagi dua;\n- bila ganjil, kalikan tiga lalu tambah satu.\n\n"
         "Cetak berapa langkah yang diperlukan.\n\nContoh: dari 6 urutannya 6 → 3 → 10 → 5 → 16 → 8 → 4 → 2 → 1, yaitu 8 langkah. Dari 1 tidak perlu langkah apa pun, jadi jawabannya 0.",
         "Satu bilangan bulat n.", "Banyak langkah sampai mencapai 1.", "1 ≤ n ≤ 10000"),
     en=("Collatz Steps", "Starting from a number n, repeat this step until the number becomes 1:\n\n- if it is even, halve it;\n- if it is odd, multiply by three and add one.\n\n"
         "Print how many steps are needed.\n\nExample: from 6 the sequence is 6 → 3 → 10 → 5 → 16 → 8 → 4 → 2 → 1, which is 8 steps. From 1 no step is needed, so the answer is 0.",
         "One integer n.", "The number of steps until 1 is reached.", "1 ≤ n ≤ 10000"),
     ar=("خطوات كولاتز", "ابدأ من عدد n وكرِّر الخطوة الآتية حتى يصير العدد 1:\n\n- إذا كان زوجيًا فاقسمه على اثنين؛\n- إذا كان فرديًا فاضربه في ثلاثة ثم أضف واحدًا.\n\n"
         "اطبع عدد الخطوات اللازمة.\n\nمثال: من 6 يكون التسلسل 6 ← 3 ← 10 ← 5 ← 16 ← 8 ← 4 ← 2 ← 1، أي 8 خطوات. من 1 لا حاجة إلى أي خطوة، فالجواب 0.",
         "عدد صحيح واحد n.", "عدد الخطوات حتى بلوغ 1.", "1 ≤ n ≤ 10000"))

# ================================ SEDANG (6) ================================

soal("kompres-huruf", "sedang", "trimmed",
     "hasil, lalu = '', ''\nfor _ in range(r.randint(1, 7)):\n    h = r.choice([c for c in 'abcdefgh' if c != lalu])\n    hasil += h * (1 if i == 2 else r.randint(1, 9))\n    lalu = h\nprint(hasil)\n",
     cpp(r'''    string s;
    cin >> s;
    int n = s.size();
    int k = 0;
    while (k < n) {
        int banyak = 1;
        while (k + banyak < n && s[k + banyak] == s[k]) {
            banyak++;
        }
        cout << s[k] << banyak;
        k += banyak;
    }
    cout << endl;''', "string"),
     id=("Kompres Huruf", "Sebuah kata berisi huruf-huruf yang sering berulang berturutan, misalnya `aaabbc`. Kata seperti itu bisa dipendekkan dengan menulis tiap huruf **sekali**, diikuti berapa kali ia berulang berturutan.\n\n"
         "`aaabbc` menjadi `a3b2c1`. `abca` menjadi `a1b1c1a1`: huruf `a` yang terpisah dihitung sendiri-sendiri.\n\nBaca sebuah kata, lalu cetak bentuk pendeknya.",
         "Satu kata dari huruf kecil.", "Bentuk pendek kata itu.", "Panjang kata paling banyak 63 huruf; satu huruf berulang paling banyak 9 kali berturutan."),
     en=("Compress Letters", "A word holds letters that often repeat in a row, for example `aaabbc`. Such a word can be shortened by writing each letter **once**, followed by how many times it repeats in a row.\n\n"
         "`aaabbc` becomes `a3b2c1`. `abca` becomes `a1b1c1a1`: the separate `a`s are counted on their own.\n\nRead a word, then print its short form.",
         "One word of lowercase letters.", "The short form of the word.", "The word has at most 63 letters; a letter repeats at most 9 times in a row."),
     ar=("ضغط الحروف", "كلمة فيها حروف تتكرر متتالية كثيرًا، مثل `aaabbc`. يمكن اختصار كلمة كهذه بكتابة كل حرف **مرة واحدة** يليه عدد مرات تكراره المتتالي.\n\n"
         "`aaabbc` تصير `a3b2c1`. و `abca` تصير `a1b1c1a1`: الحرفان `a` المتباعدان يُعدّ كل منهما وحده.\n\nاقرأ كلمة ثم اطبع صورتها المختصرة.",
         "كلمة واحدة من حروف لاتينية صغيرة.", "الصورة المختصرة للكلمة.", "الكلمة 63 حرفًا على الأكثر؛ يتكرر الحرف 9 مرات متتالية على الأكثر."))

soal("faktor-prima", "sedang", "trimmed",
     PRIMA + "n = r.randint(2, 1000000)\nif i == 2:\n    n = 2 ** r.randint(1, 19)\nif i == 3:\n    n = 999983\nif i == 4:\n    n = r.choice([997 * 991, 3 ** 12, 2 * 3 * 5 * 7 * 11 * 13])\nif i == 5:\n    while not prima(n):\n        n -= 1\nprint(n)\n",
     cpp(r'''    int n;
    cin >> n;
    for (int k = 2; k * k <= n; k++) {
        while (n % k == 0) {
            cout << k << " ";
            n /= k;
        }
    }
    if (n > 1) {
        cout << n;
    }
    cout << endl;'''),
     id=("Faktor Prima", "Setiap bilangan bulat yang lebih besar dari 1 dapat ditulis sebagai hasil kali bilangan-bilangan prima. Misalnya 360 = 2 × 2 × 2 × 3 × 3 × 5.\n\n"
         "Baca sebuah bilangan, lalu cetak faktor-faktor primanya **dari yang terkecil**, dipisah satu spasi. Faktor yang muncul beberapa kali ditulis beberapa kali.\n\n"
         "Contoh: 360 → `2 2 2 3 3 5`; 97 → `97`; 1001 → `7 11 13`.",
         "Satu bilangan bulat n.", "Satu baris: faktor-faktor prima n, urut naik.", "2 ≤ n ≤ 1000000"),
     en=("Prime Factors", "Every integer greater than 1 can be written as a product of primes. For example 360 = 2 × 2 × 2 × 3 × 3 × 5.\n\n"
         "Read a number, then print its prime factors **from the smallest**, separated by single spaces. A factor that occurs several times is written several times.\n\n"
         "Examples: 360 → `2 2 2 3 3 5`; 97 → `97`; 1001 → `7 11 13`.",
         "One integer n.", "One line: the prime factors of n in ascending order.", "2 ≤ n ≤ 1000000"),
     ar=("العوامل الأولية", "كل عدد صحيح أكبر من 1 يمكن كتابته حاصلَ ضرب أعداد أولية. مثلًا 360 = 2 × 2 × 2 × 3 × 3 × 5.\n\n"
         "اقرأ عددًا ثم اطبع عوامله الأولية **من الأصغر** تفصل بينها مسافة واحدة. العامل الذي يتكرر يُكتب بعدد مرات تكراره.\n\n"
         "أمثلة: 360 ← `2 2 2 3 3 5`؛ 97 ← `97`؛ 1001 ← `7 11 13`.",
         "عدد صحيح واحد n.", "سطر واحد: العوامل الأولية للعدد n تصاعديًا.", "2 ≤ n ≤ 1000000"))

soal("statistik-nilai", "sedang", "trimmed",
     "while True:\n    n = r.randint(1, 20)\n    v = [r.randint(0, 100) for _ in range(n)]\n    if i == 2:\n        v = [r.randint(0, 59) for _ in range(n)]\n    if i == 3:\n        v[0] = 60\n    if abs(sum(v) * 100 / n % 1 - 0.5) > 1e-3:\n        break\nprint(n)\nprint(*v)\n",
     cpp(r'''    int n, x;
    cin >> n;
    int rendah = 100, tinggi = 0, jumlah = 0, lulus = 0;
    for (int k = 0; k < n; k++) {
        cin >> x;
        if (x < rendah) rendah = x;
        if (x > tinggi) tinggi = x;
        jumlah += x;
        if (x >= 60) lulus++;
    }
    cout << "Terendah: " << rendah << endl;
    cout << "Tertinggi: " << tinggi << endl;
    cout << "Rata-rata: " << fixed << setprecision(2) << (double) jumlah / n << endl;
    cout << "Lulus: " << lulus << endl;''', "iomanip"),
     id=("Statistik Nilai", "Seorang dosen ingin ringkasan nilai ujian satu kelas. Baca n nilai, lalu cetak empat baris:\n\n"
         "```\nTerendah: …\nTertinggi: …\nRata-rata: …\nLulus: …\n```\n\n"
         "Rata-rata ditulis dengan dua angka di belakang koma. `Lulus` adalah banyak mahasiswa yang nilainya 60 atau lebih.\n\n"
         "Untuk nilai `55 80 72 40`:\n\n```\nTerendah: 40\nTertinggi: 80\nRata-rata: 61.75\nLulus: 2\n```\n\nSemua itu bisa dihitung dalam satu kali perulangan, tanpa menyimpan nilainya.",
         "Baris pertama: n. Baris kedua: n bilangan bulat.", "Empat baris seperti contoh.", "1 ≤ n ≤ 20; tiap nilai antara 0 dan 100"),
     en=("Mark Statistics", "A lecturer wants a summary of one class's exam marks. Read n marks, then print four lines:\n\n"
         "```\nTerendah: …\nTertinggi: …\nRata-rata: …\nLulus: …\n```\n\n"
         "(lowest, highest, average, passed). The average has two digits after the decimal point. `Lulus` is the number of students with a mark of 60 or more.\n\n"
         "For the marks `55 80 72 40`:\n\n```\nTerendah: 40\nTertinggi: 80\nRata-rata: 61.75\nLulus: 2\n```\n\nAll of this can be computed in a single loop, without storing the marks.",
         "First line: n. Second line: n integers.", "Four lines as in the example.", "1 ≤ n ≤ 20; each mark is between 0 and 100"),
     ar=("إحصاء الدرجات", "يريد محاضر ملخصًا لدرجات امتحان صف. اقرأ n درجة ثم اطبع أربعة أسطر:\n\n"
         "```\nTerendah: …\nTertinggi: …\nRata-rata: …\nLulus: …\n```\n\n"
         "(الأدنى، الأعلى، المتوسط، الناجحون). يُكتب المتوسط برقمين بعد الفاصلة. `Lulus` هو عدد الطلاب الذين درجتهم 60 أو أكثر.\n\n"
         "للدرجات `55 80 72 40`:\n\n```\nTerendah: 40\nTertinggi: 80\nRata-rata: 61.75\nLulus: 2\n```\n\nيمكن حساب ذلك كله في حلقة واحدة دون تخزين الدرجات.",
         "السطر الأول: n. السطر الثاني: n عددًا صحيحًا.", "أربعة أسطر كما في المثال.", "1 ≤ n ≤ 20؛ كل درجة بين 0 و 100"))

soal("palindrom-kalimat", "sedang", "trimmed",
     "FRASA = ['Kasur ini rusak', 'Ibu Ratna antar ubi', 'Malam', 'Katak', 'Aku suka', 'Kodok', 'Radar', 'Ini kali pertama', 'Taat', 'Makam', 'Kasur Nababan rusak', 'Level', 'Belajar kode']\n"
     "if i < 4:\n    t = FRASA[(i * 5 + int(os.environ['SEED'])) % len(FRASA)]\nelse:\n    h = [r.choice('abdeiklmnorstu') for _ in range(r.randint(1, 8))]\n    h = h + ([r.choice('aiu')] if r.random() < 0.5 else []) + h[::-1]\n"
     "    if i % 2 == 1 and len(h) > 1:\n        p = r.randint(0, len(h) // 2 - 1)\n        h[p] = 'z' if h[p] != 'z' else 'y'\n"
     "    t = ''\n    for k, c in enumerate(h):\n        t += c.upper() if r.random() < 0.2 else c\n        if k < len(h) - 1 and r.random() < 0.25:\n            t += ' '\nprint(t)\n",
     cpp(r'''    string s;
    getline(cin, s);
    int kiri = 0, kanan = (int) s.size() - 1;
    bool palindrom = true;
    while (kiri < kanan) {
        if (s[kiri] == ' ') {
            kiri++;
            continue;
        }
        if (s[kanan] == ' ') {
            kanan--;
            continue;
        }
        char a = s[kiri], b = s[kanan];
        if (a >= 'A' && a <= 'Z') a = a - 'A' + 'a';
        if (b >= 'A' && b <= 'Z') b = b - 'A' + 'a';
        if (a != b) {
            palindrom = false;
            break;
        }
        kiri++;
        kanan--;
    }
    if (palindrom) {
        cout << "palindrom" << endl;
    } else {
        cout << "bukan palindrom" << endl;
    }''', "string"),
     id=("Kalimat Palindrom", "Palindrom adalah teks yang sama saja dibaca dari depan maupun dari belakang, misalnya `katak`. Sebuah kalimat pun bisa palindrom bila **spasi diabaikan** dan **huruf besar dianggap sama dengan huruf kecil**: `Kasur ini rusak` adalah palindrom.\n\n"
         "Baca satu baris teks, lalu cetak `palindrom` atau `bukan palindrom`.\n\nContoh lain: `Ibu Ratna antar ubi` palindrom; `Belajar kode` bukan palindrom.",
         "Satu baris teks.", "`palindrom` atau `bukan palindrom`.", "Teks terdiri dari huruf dan spasi, paling panjang 40 karakter, diawali dan diakhiri huruf."),
     en=("Palindrome Sentence", "A palindrome is a text that reads the same forwards and backwards, such as `katak`. A sentence can be a palindrome too when **spaces are ignored** and **capital letters count as lowercase**: `Kasur ini rusak` is a palindrome.\n\n"
         "Read one line of text, then print `palindrom` or `bukan palindrom` (not a palindrome).\n\nMore examples: `Ibu Ratna antar ubi` is a palindrome; `Belajar kode` is not.",
         "One line of text.", "`palindrom` or `bukan palindrom`.", "The text has letters and spaces, at most 40 characters, and starts and ends with a letter."),
     ar=("جملة متناظرة", "النص المتناظر يُقرأ من أوله كما يُقرأ من آخره، مثل `katak`. والجملة أيضًا قد تكون متناظرة إذا **أُهملت المسافات** و**عُدَّت الحروف الكبيرة كالصغيرة**: `Kasur ini rusak` متناظرة.\n\n"
         "اقرأ سطرًا واحدًا من النص ثم اطبع `palindrom` (متناظر) أو `bukan palindrom` (غير متناظر).\n\nأمثلة أخرى: `Ibu Ratna antar ubi` متناظرة، و `Belajar kode` غير متناظرة.",
         "سطر واحد من النص.", "`palindrom` أو `bukan palindrom`.", "النص حروف لاتينية ومسافات، 40 محرفًا على الأكثر، يبدأ وينتهي بحرف."))

soal("prima-di-rentang", "sedang", "trimmed",
     "a = r.randint(1, 5000)\nb = r.randint(a, 5000)\nif i == 2:\n    a, b = 1, 5000\nif i == 3:\n    a = b = r.choice([1, 2, 97, 100, 4999])\nif i == 4:\n    a, b = r.choice([(24, 28), (90, 96), (114, 126), (1328, 1360)])\nprint(a, b)\n",
     cpp(r'''    int a, b;
    cin >> a >> b;
    int banyak = 0;
    for (int n = a; n <= b; n++) {
        if (n < 2) {
            continue;
        }
        bool prima = true;
        for (int k = 2; k * k <= n; k++) {
            if (n % k == 0) {
                prima = false;
                break;
            }
        }
        if (prima) {
            banyak++;
        }
    }
    cout << banyak << endl;'''),
     id=("Prima di Rentang", "Baca dua bilangan a dan b, lalu cetak berapa banyak bilangan prima dari a sampai b (keduanya ikut dihitung).\n\n"
         "Contoh: dari 1 sampai 10 ada 4 bilangan prima (2, 3, 5, 7). Dari 10 sampai 20 juga ada 4 (11, 13, 17, 19). Dari 24 sampai 28 tidak ada, jadi jawabannya 0.",
         "Satu baris berisi a dan b.", "Satu bilangan: banyak bilangan prima di rentang itu.", "1 ≤ a ≤ b ≤ 5000"),
     en=("Primes in a Range", "Read two numbers a and b, then print how many primes there are from a to b (both included).\n\n"
         "Examples: from 1 to 10 there are 4 primes (2, 3, 5, 7). From 10 to 20 there are also 4 (11, 13, 17, 19). From 24 to 28 there is none, so the answer is 0.",
         "One line with a and b.", "One number: how many primes lie in the range.", "1 ≤ a ≤ b ≤ 5000"),
     ar=("الأعداد الأولية في مجال", "اقرأ عددين a و b ثم اطبع كم عددًا أوليًا يوجد من a إلى b (مع احتساب الطرفين).\n\n"
         "أمثلة: من 1 إلى 10 توجد 4 أعداد أولية (2، 3، 5، 7). ومن 10 إلى 20 توجد 4 أيضًا (11، 13، 17، 19). ومن 24 إلى 28 لا يوجد أي عدد، فالجواب 0.",
         "سطر واحد فيه a و b.", "عدد واحد: عدد الأعداد الأولية في المجال.", "1 ≤ a ≤ b ≤ 5000"))

soal("papan-catur", "sedang", "trimmed", "b, k = r.randint(1, 12), r.randint(1, 12)\nif i == 2:\n    b, k = 1, 1\nif i == 3:\n    b, k = 8, 8\nprint(b, k)\n",
     cpp(r'''    int baris, kolom;
    cin >> baris >> kolom;
    for (int b = 0; b < baris; b++) {
        for (int k = 0; k < kolom; k++) {
            if ((b + k) % 2 == 0) {
                cout << "#";
            } else {
                cout << ".";
            }
        }
        cout << endl;
    }'''),
     id=("Papan Catur", "Cetak pola papan catur berukuran b baris dan k kolom. Petak gelap ditulis `#`, petak terang ditulis `.`, dan keduanya berselang-seling ke kanan maupun ke bawah. Petak kiri atas selalu `#`.\n\n"
         "Untuk 3 baris dan 5 kolom:\n\n```\n#.#.#\n.#.#.\n#.#.#\n```",
         "Satu baris berisi b dan k.", "b baris, masing-masing k karakter.", "1 ≤ b, k ≤ 12"),
     en=("Chessboard", "Print a chessboard pattern of b rows and k columns. A dark square is written `#`, a light one `.`, and they alternate both to the right and downwards. The top-left square is always `#`.\n\n"
         "For 3 rows and 5 columns:\n\n```\n#.#.#\n.#.#.\n#.#.#\n```",
         "One line with b and k.", "b lines of k characters each.", "1 ≤ b, k ≤ 12"),
     ar=("رقعة الشطرنج", "اطبع نمط رقعة شطرنج من b صفًّا و k عمودًا. المربع الداكن يُكتب `#` والفاتح `.`، ويتناوبان يمينًا ونزولًا. المربع العلوي الأيسر دائمًا `#`.\n\n"
         "لثلاثة صفوف وخمسة أعمدة:\n\n```\n#.#.#\n.#.#.\n#.#.#\n```",
         "سطر واحد فيه b و k.", "b سطرًا في كل منها k محرفًا.", "1 ≤ b, k ≤ 12"))

# ================================= SULIT (4) =================================

soal("ketupat-berongga", "sulit", "trimmed", "print({2: 1, 3: 2, 4: 10}.get(i, r.randint(3, 6) if i % 2 == 0 else r.randint(7, 10)))\n",
     cpp(r'''    int n;
    cin >> n;
    for (int b = 1; b <= 2 * n - 1; b++) {
        int t = b;
        if (b > n) {
            t = 2 * n - b;
        }
        for (int k = 0; k < n - t; k++) {
            cout << " ";
        }
        cout << "*";
        if (t > 1) {
            for (int k = 0; k < 2 * t - 3; k++) {
                cout << " ";
            }
            cout << "*";
        }
        cout << endl;
    }'''),
     id=("Ketupat Berongga", "Cetak belah ketupat berongga dari tanda bintang. Bilangan n adalah banyak baris dari puncak sampai baris terlebar; seluruhnya ada 2n − 1 baris.\n\n"
         "Untuk n = 3:\n\n```\n  *\n * *\n*   *\n * *\n  *\n```\n\nUntuk n = 1 keluarannya satu bintang saja.\n\n"
         "Baris teratas dan terbawah berisi satu bintang; baris lainnya dua bintang dengan spasi di antaranya. Spasi di **depan** tiap baris harus tepat; jangan menulis apa pun setelah bintang terakhir di tiap baris.",
         "Satu bilangan bulat n.", "2n − 1 baris yang membentuk belah ketupat berongga.", "1 ≤ n ≤ 10"),
     en=("Hollow Diamond", "Print a hollow diamond of asterisks. The number n is the number of lines from the top to the widest line; there are 2n − 1 lines in all.\n\n"
         "For n = 3:\n\n```\n  *\n * *\n*   *\n * *\n  *\n```\n\nFor n = 1 the output is a single asterisk.\n\n"
         "The top and bottom lines have one asterisk; the others have two with spaces between them. The spaces **in front of** each line must be exact; write nothing after the last asterisk of a line.",
         "One integer n.", "2n − 1 lines forming a hollow diamond.", "1 ≤ n ≤ 10"),
     ar=("معيَّن مجوَّف", "اطبع معيَّنًا مجوَّفًا من النجوم. العدد n هو عدد الأسطر من القمة إلى أعرض سطر، والمجموع 2n − 1 سطرًا.\n\n"
         "عند n = 3:\n\n```\n  *\n * *\n*   *\n * *\n  *\n```\n\nعند n = 1 يكون المخرج نجمة واحدة فقط.\n\n"
         "في السطر الأعلى والسطر الأسفل نجمة واحدة، وفي بقية الأسطر نجمتان بينهما مسافات. المسافات **في أول** كل سطر يجب أن تكون مضبوطة، ولا تكتب شيئًا بعد آخر نجمة في السطر.",
         "عدد صحيح واحد n.", "2n − 1 سطرًا تشكّل معيَّنًا مجوَّفًا.", "1 ≤ n ≤ 10"))

soal("tripel-pythagoras", "sulit", "trimmed", "print({2: 4, 3: 5, 4: 300, 5: 20}.get(i, r.randint(6, 300)))\n",
     cpp(r'''    int n;
    cin >> n;
    int banyak = 0;
    for (int a = 1; a <= n; a++) {
        for (int b = a + 1; b <= n; b++) {
            int kuadrat = a * a + b * b;
            for (int c = b + 1; c <= n; c++) {
                if (c * c == kuadrat) {
                    banyak++;
                }
                if (c * c >= kuadrat) {
                    break;
                }
            }
        }
    }
    cout << banyak << endl;'''),
     id=("Tripel Pythagoras", "Tiga bilangan bulat positif a < b < c disebut tripel Pythagoras bila a² + b² = c², misalnya (3, 4, 5) dan (5, 12, 13). Ketiganya bisa menjadi sisi segitiga siku-siku.\n\n"
         "Baca sebuah bilangan n, lalu cetak berapa banyak tripel Pythagoras yang **ketiga bilangannya tidak lebih dari n**.\n\n"
         "Contoh: untuk n = 20 ada 6 tripel: (3, 4, 5), (6, 8, 10), (5, 12, 13), (9, 12, 15), (8, 15, 17), (12, 16, 20). Untuk n = 4 belum ada, jadi jawabannya 0.\n\n"
         "Program harus selesai dalam batas waktu untuk n = 300; hentikan perulangan terdalam begitu tidak mungkin lagi menemukan jawaban.",
         "Satu bilangan bulat n.", "Banyak tripel Pythagoras dengan c ≤ n.", "1 ≤ n ≤ 300"),
     en=("Pythagorean Triples", "Three positive integers a < b < c form a Pythagorean triple when a² + b² = c², for example (3, 4, 5) and (5, 12, 13). They can be the sides of a right-angled triangle.\n\n"
         "Read a number n, then print how many Pythagorean triples have **all three numbers not larger than n**.\n\n"
         "Example: for n = 20 there are 6 triples: (3, 4, 5), (6, 8, 10), (5, 12, 13), (9, 12, 15), (8, 15, 17), (12, 16, 20). For n = 4 there is none yet, so the answer is 0.\n\n"
         "The program must finish within the time limit for n = 300; stop the innermost loop as soon as no answer can be found any more.",
         "One integer n.", "The number of Pythagorean triples with c ≤ n.", "1 ≤ n ≤ 300"),
     ar=("ثلاثيات فيثاغورس", "ثلاثة أعداد صحيحة موجبة a < b < c تسمى ثلاثية فيثاغورس إذا كان a² + b² = c²، مثل (3, 4, 5) و (5, 12, 13). ويمكن أن تكون أضلاع مثلث قائم الزاوية.\n\n"
         "اقرأ عددًا n ثم اطبع عدد ثلاثيات فيثاغورس التي **لا يزيد أي من أعدادها الثلاثة على n**.\n\n"
         "مثال: عند n = 20 توجد 6 ثلاثيات: (3, 4, 5)، (6, 8, 10)، (5, 12, 13)، (9, 12, 15)، (8, 15, 17)، (12, 16, 20). وعند n = 4 لا توجد أي ثلاثية، فالجواب 0.\n\n"
         "يجب أن ينتهي البرنامج ضمن حد الوقت عند n = 300؛ أوقف الحلقة الداخلية متى استحال إيجاد جواب.",
         "عدد صحيح واحد n.", "عدد ثلاثيات فيثاغورس التي فيها c ≤ n.", "1 ≤ n ≤ 300"))

soal("mesin-atm", "sulit", "trimmed",
     "saldo = r.randint(0, 40) * 50000\nprint(saldo)\nm = r.randint(1, 12)\nblokir = r.randint(0, m - 1) if i % 3 == 0 else -1\nfor k in range(m):\n    if k == blokir:\n        for _ in range(3):\n            print('tarik', r.randint(0, 5) * 50000 + r.randint(1, 49) * 1000)\n        continue\n"
     "    p = r.random()\n    if p < 0.35:\n        print('setor', r.randint(1, 100) * 10000)\n    elif p < 0.75:\n        print('tarik', r.randint(1, 20) * 50000)\n    elif p < 0.9:\n        print('tarik', r.randint(1, 20) * 50000 + r.choice([10000, 20000, 25000]))\n    else:\n        print('tarik', 2500000 + r.randint(1, 9) * 50000)\nprint('selesai')\n",
     cpp(r'''    int saldo;
    cin >> saldo;
    int ditolak = 0, beruntun = 0;
    bool diblokir = false;
    string perintah;
    while (cin >> perintah) {
        if (perintah == "selesai") {
            break;
        }
        int jumlah;
        cin >> jumlah;
        if (perintah == "setor") {
            saldo += jumlah;
            beruntun = 0;
        } else if (jumlah % 50000 != 0 || jumlah > saldo) {
            ditolak++;
            beruntun++;
            if (beruntun == 3) {
                diblokir = true;
                break;
            }
        } else {
            saldo -= jumlah;
            beruntun = 0;
        }
    }
    cout << "Saldo akhir: " << saldo << endl;
    cout << "Ditolak: " << ditolak << endl;
    if (diblokir) {
        cout << "Status: diblokir" << endl;
    } else {
        cout << "Status: normal" << endl;
    }''', "string"),
     id=("Mesin ATM", "Tirukan sebuah mesin ATM. Baris pertama masukan adalah saldo awal. Sesudah itu ada perintah, satu per baris:\n\n"
         "- `setor x` — saldo bertambah x. Selalu diterima.\n- `tarik x` — saldo berkurang x. **Ditolak** bila x bukan kelipatan 50000, atau bila x lebih besar daripada saldo. Penarikan yang ditolak tidak mengubah saldo.\n- `selesai` — tidak ada perintah lagi.\n\n"
         "Bila **tiga penarikan berturut-turut ditolak**, kartu diblokir dan mesin berhenti saat itu juga: perintah sesudahnya tidak dijalankan. Perintah yang diterima (setor atau tarik) memutus hitungan berturut-turut itu.\n\n"
         "Cetak tiga baris: saldo akhir, banyak penarikan yang ditolak, dan status kartu (`normal` atau `diblokir`).\n\n"
         "Contoh masukan:\n\n```\n200000\ntarik 150000\ntarik 100000\nsetor 30000\ntarik 75000\nselesai\n```\n\nKeluarannya:\n\n```\nSaldo akhir: 80000\nDitolak: 2\nStatus: normal\n```\n\n"
         "Penarikan 100000 ditolak karena saldo tinggal 50000; penarikan 75000 ditolak karena bukan kelipatan 50000.",
         "Baris pertama: saldo awal. Baris-baris berikutnya: perintah, diakhiri `selesai`.", "Tiga baris: `Saldo akhir: …`, `Ditolak: …`, `Status: …`.", "0 ≤ saldo awal ≤ 2000000; paling banyak 14 perintah sebelum `selesai`; 1000 ≤ x ≤ 3000000"),
     en=("Cash Machine", "Imitate a cash machine. The first input line is the opening balance. Then come commands, one per line:\n\n"
         "- `setor x` (deposit) — the balance grows by x. Always accepted.\n- `tarik x` (withdraw) — the balance shrinks by x. **Refused** when x is not a multiple of 50000, or when x is larger than the balance. A refused withdrawal leaves the balance unchanged.\n- `selesai` (done) — no more commands.\n\n"
         "When **three withdrawals in a row are refused**, the card is blocked and the machine stops at once: later commands are not carried out. An accepted command (deposit or withdrawal) breaks the run.\n\n"
         "Print three lines: the closing balance, the number of refused withdrawals, and the card status (`normal` or `diblokir`, blocked).\n\n"
         "Example input:\n\n```\n200000\ntarik 150000\ntarik 100000\nsetor 30000\ntarik 75000\nselesai\n```\n\nIts output:\n\n```\nSaldo akhir: 80000\nDitolak: 2\nStatus: normal\n```\n\n"
         "The withdrawal of 100000 is refused because only 50000 is left; the withdrawal of 75000 is refused because it is not a multiple of 50000.",
         "First line: the opening balance. Following lines: commands, ending with `selesai`.", "Three lines: `Saldo akhir: …`, `Ditolak: …`, `Status: …`.", "0 ≤ opening balance ≤ 2000000; at most 14 commands before `selesai`; 1000 ≤ x ≤ 3000000"),
     ar=("آلة الصرّاف", "حاكِ آلة صرّاف آلي. السطر الأول من المدخل هو الرصيد الابتدائي. ثم تأتي أوامر، أمر في كل سطر:\n\n"
         "- `setor x` (إيداع) — يزيد الرصيد بمقدار x. يُقبل دائمًا.\n- `tarik x` (سحب) — ينقص الرصيد بمقدار x. **يُرفض** إذا لم يكن x من مضاعفات 50000، أو إذا كان x أكبر من الرصيد. السحب المرفوض لا يغيّر الرصيد.\n- `selesai` (انتهى) — لا أوامر بعده.\n\n"
         "إذا **رُفضت ثلاث عمليات سحب متتالية** تُحجب البطاقة وتتوقف الآلة فورًا: لا تُنفَّذ الأوامر التالية. الأمر المقبول (إيداع أو سحب) يقطع التتالي.\n\n"
         "اطبع ثلاثة أسطر: الرصيد النهائي، وعدد عمليات السحب المرفوضة، وحالة البطاقة (`normal` أو `diblokir` أي محجوبة).\n\n"
         "مثال للمدخل:\n\n```\n200000\ntarik 150000\ntarik 100000\nsetor 30000\ntarik 75000\nselesai\n```\n\nومخرجه:\n\n```\nSaldo akhir: 80000\nDitolak: 2\nStatus: normal\n```\n\n"
         "رُفض سحب 100000 لأن الرصيد صار 50000 فقط، ورُفض سحب 75000 لأنه ليس من مضاعفات 50000.",
         "السطر الأول: الرصيد الابتدائي. الأسطر التالية: أوامر تنتهي بـ `selesai`.", "ثلاثة أسطر: `Saldo akhir: …` و `Ditolak: …` و `Status: …`.", "0 ≤ الرصيد الابتدائي ≤ 2000000؛ 14 أمرًا على الأكثر قبل `selesai`؛ 1000 ≤ x ≤ 3000000"))

soal("runtun-naik-terpanjang", "sulit", "trimmed",
     "n = r.randint(1, 30)\nv = [r.randint(1, 20) for _ in range(n)]\nif i == 2:\n    v = sorted(set(v))\nif i == 3:\n    v = sorted(v, reverse=True)\nif i == 4:\n    v = [v[0]] * len(v)\nif i == 5:\n    v = v + list(range(1, 12))\nprint(len(v))\nprint(*v)\n",
     cpp(r'''    int n, x, lalu;
    cin >> n;
    cin >> lalu;
    int panjang = 1, terbaik = 1;
    for (int k = 1; k < n; k++) {
        cin >> x;
        if (x > lalu) {
            panjang++;
        } else {
            panjang = 1;
        }
        if (panjang > terbaik) {
            terbaik = panjang;
        }
        lalu = x;
    }
    cout << terbaik << endl;'''),
     id=("Runtun Naik Terpanjang", "Suhu sebuah kota dicatat setiap hari. Carilah **runtun hari berturutan terpanjang** yang suhunya terus naik: tiap hari harus lebih tinggi daripada hari sebelumnya (sama saja tidak dihitung naik).\n\n"
         "Baca n catatan suhu, lalu cetak panjang runtun naik terpanjang itu.\n\n"
         "Contoh: untuk `3 5 5 6 7 2` runtun terpanjangnya `5 6 7`, jadi jawabannya 3. Bila suhunya tidak pernah naik, jawabannya 1.\n\n"
         "Kerjakan tanpa array: cukup ingat suhu hari sebelumnya dan panjang runtun saat ini.",
         "Baris pertama: n. Baris kedua: n bilangan bulat.", "Satu bilangan: panjang runtun naik terpanjang.", "1 ≤ n ≤ 41; tiap bilangan antara 1 dan 20"),
     en=("Longest Rising Run", "A city's temperature is recorded every day. Find the **longest run of consecutive days** on which the temperature keeps rising: each day must be higher than the day before (equal does not count as rising).\n\n"
         "Read n temperature records, then print the length of the longest rising run.\n\n"
         "Example: for `3 5 5 6 7 2` the longest run is `5 6 7`, so the answer is 3. If the temperature never rises, the answer is 1.\n\n"
         "Do it without an array: it is enough to remember the previous day's temperature and the length of the current run.",
         "First line: n. Second line: n integers.", "One number: the length of the longest rising run.", "1 ≤ n ≤ 41; each number is between 1 and 20"),
     ar=("أطول تتابع صاعد", "تُسجَّل درجة حرارة مدينة كل يوم. أوجد **أطول تتابع من الأيام المتتالية** تواصل فيه الحرارة الارتفاع: كل يوم يجب أن يكون أعلى من سابقه (التساوي لا يُعدّ ارتفاعًا).\n\n"
         "اقرأ n تسجيلًا للحرارة ثم اطبع طول أطول تتابع صاعد.\n\n"
         "مثال: للأعداد `3 5 5 6 7 2` أطول تتابع هو `5 6 7`، فالجواب 3. إذا لم ترتفع الحرارة أبدًا فالجواب 1.\n\n"
         "حُلَّها بلا مصفوفة: يكفي أن تتذكر حرارة اليوم السابق وطول التتابع الحالي.",
         "السطر الأول: n. السطر الثاني: n عددًا صحيحًا.", "عدد واحد: طول أطول تتابع صاعد.", "1 ≤ n ≤ 41؛ كل عدد بين 1 و 20"))

tulis(__file__, "DDP101", 5, "cpp", {"mudah_sekali": 8, "mudah": 8, "mudah_sedang": 6, "sedang": 6, "sulit": 4})
