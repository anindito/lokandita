#!/usr/bin/env python3
"""Menyusun docs/soal/ddp101-pertemuan-4.json.

DDP101 pertemuan 4 — Struktur Kontrol: Percabangan. Konsep yang diizinkan:
IO_DASAR, VARIABEL_TIPE, ARITMETIKA, KONVERSI_TIPE, FORMAT_OUTPUT, STRING,
PERCABANGAN. Solusi referensi TIDAK memakai perulangan, array, atau fungsi
buatan sendiri. 32 soal: 8 / 8 / 6 / 6 / 4.

Silabus pertemuan 4: if, if-else, if-else bersarang, switch-case, operator
kondisional, operator logika; contoh: nilai huruf, pajak penghasilan,
kalkulator dengan pilihan operasi.

Kata tetap yang dicetak program ("genap", "LULUS", "sama kaki", …) sama di
ketiga bahasa soal, karena keluaran yang diharapkan hanya satu.

Generator memakai INDEX untuk memaksa setiap cabang muncul di tes tiap
mahasiswa (nilai batas, kasus sama, kasus khusus).

    python3 docs/soal/buat-ddp101-pertemuan-4.py
"""
from bangun import cpp, soal, tulis

BENIH = 's = int(os.environ["SEED"])\n'

# ============================== MUDAH SEKALI (8) ==============================

soal("tanda-bilangan", "mudah_sekali", "trimmed",
     "k = i % 3\nprint(r.randint(1, 1000) if k == 0 else -r.randint(1, 1000) if k == 1 else 0)\n",
     cpp(r'''    int n;
    cin >> n;
    if (n > 0) {
        cout << "positif" << endl;
    } else if (n < 0) {
        cout << "negatif" << endl;
    } else {
        cout << "nol" << endl;
    }'''),
     id=("Positif, Negatif, atau Nol", "Baca sebuah bilangan bulat, lalu cetak `positif`, `negatif`, atau `nol` sesuai nilainya.",
         "Satu bilangan bulat n.", "Satu kata: `positif`, `negatif`, atau `nol`.", "−1000 ≤ n ≤ 1000"),
     en=("Positive, Negative or Zero", "Read an integer, then print `positif` (positive), `negatif` (negative) or `nol` (zero) according to its value.",
         "One integer n.", "One word: `positif`, `negatif` or `nol`.", "−1000 ≤ n ≤ 1000"),
     ar=("موجب أم سالب أم صفر", "اقرأ عددًا صحيحًا ثم اطبع `positif` (موجب) أو `negatif` (سالب) أو `nol` (صفر) بحسب قيمته.",
         "عدد صحيح واحد n.", "كلمة واحدة: `positif` أو `negatif` أو `nol`.", "−1000 ≤ n ≤ 1000"))

soal("genap-atau-ganjil", "mudah_sekali", "trimmed", "print(2 * r.randint(0, 500000) + i % 2)\n",
     cpp(r'''    int n;
    cin >> n;
    if (n % 2 == 0) {
        cout << "genap" << endl;
    } else {
        cout << "ganjil" << endl;
    }'''),
     id=("Genap atau Ganjil", "Baca sebuah bilangan bulat tak negatif, lalu cetak `genap` bila bilangan itu habis dibagi dua dan `ganjil` bila tidak.\n\nNol termasuk bilangan genap.",
         "Satu bilangan bulat n.", "Satu kata: `genap` atau `ganjil`.", "0 ≤ n ≤ 1000001"),
     en=("Even or Odd", "Read a non-negative integer, then print `genap` (even) if it is divisible by two and `ganjil` (odd) if it is not.\n\nZero is an even number.",
         "One integer n.", "One word: `genap` or `ganjil`.", "0 ≤ n ≤ 1000001"),
     ar=("زوجي أم فردي", "اقرأ عددًا صحيحًا غير سالب ثم اطبع `genap` (زوجي) إذا كان يقبل القسمة على اثنين و `ganjil` (فردي) إذا لم يكن كذلك.\n\nالصفر عدد زوجي.",
         "عدد صحيح واحد n.", "كلمة واحدة: `genap` أو `ganjil`.", "0 ≤ n ≤ 1000001"))

soal("lebih-besar", "mudah_sekali", "trimmed",
     "a, b = r.randint(-1000, 1000), r.randint(-1000, 1000)\nif i == 3:\n    b = a\nprint(a, b)\n",
     cpp(r'''    int a, b;
    cin >> a >> b;
    if (a > b) {
        cout << a << endl;
    } else {
        cout << b << endl;
    }'''),
     id=("Yang Lebih Besar", "Baca dua bilangan bulat, lalu cetak yang lebih besar. Bila keduanya sama, cetak bilangan itu.\n\nUntuk `7 -3` jawabannya 7.",
         "Satu baris berisi dua bilangan bulat a dan b.", "Satu bilangan: yang lebih besar di antara a dan b.", "−1000 ≤ a, b ≤ 1000"),
     en=("The Larger One", "Read two integers, then print the larger one. If they are equal, print that number.\n\nFor `7 -3` the answer is 7.",
         "One line with two integers a and b.", "One number: the larger of a and b.", "−1000 ≤ a, b ≤ 1000"),
     ar=("الأكبر", "اقرأ عددين صحيحين ثم اطبع الأكبر منهما. إذا تساويا فاطبع ذلك العدد.\n\nعند `7 -3` الجواب 7.",
         "سطر واحد فيه عددان صحيحان a و b.", "عدد واحد: الأكبر بين a و b.", "−1000 ≤ a, b ≤ 1000"))

soal("lulus-atau-tidak", "mudah_sekali", "trimmed", "print({2: 60, 3: 59, 4: 100, 5: 0}.get(i, r.randint(0, 100)))\n",
     cpp(r'''    int nilai;
    cin >> nilai;
    if (nilai >= 60) {
        cout << "LULUS" << endl;
    } else {
        cout << "TIDAK LULUS" << endl;
    }'''),
     id=("Lulus atau Tidak", "Seorang mahasiswa lulus sebuah ujian bila nilainya **60 atau lebih**.\n\nBaca nilainya, lalu cetak `LULUS` atau `TIDAK LULUS` (huruf besar semua).",
         "Satu bilangan bulat: nilai ujian.", "`LULUS` atau `TIDAK LULUS`.", "0 ≤ nilai ≤ 100"),
     en=("Pass or Fail", "A student passes an exam with a mark of **60 or more**.\n\nRead the mark, then print `LULUS` (passed) or `TIDAK LULUS` (not passed), all in capital letters.",
         "One integer: the exam mark.", "`LULUS` or `TIDAK LULUS`.", "0 ≤ mark ≤ 100"),
     ar=("ناجح أم لا", "ينجح الطالب في الامتحان إذا كانت درجته **60 أو أكثر**.\n\nاقرأ الدرجة ثم اطبع `LULUS` (ناجح) أو `TIDAK LULUS` (غير ناجح) بحروف كبيرة.",
         "عدد صحيح واحد: درجة الامتحان.", "`LULUS` أو `TIDAK LULUS`.", "0 ≤ الدرجة ≤ 100"))

soal("nilai-mutlak", "mudah_sekali", "trimmed",
     "n = r.randint(1, 10000)\nif i % 2 == 0:\n    n = -n\nif i == 4:\n    n = 0\nprint(n)\n",
     cpp(r'''    int n;
    cin >> n;
    if (n < 0) {
        n = -n;
    }
    cout << n << endl;'''),
     id=("Nilai Mutlak", "Nilai mutlak sebuah bilangan adalah jaraknya dari nol: nilai mutlak −8 adalah 8, dan nilai mutlak 8 tetap 8.\n\nBaca sebuah bilangan bulat, lalu cetak nilai mutlaknya. Kerjakan dengan `if`, tanpa fungsi `abs`.",
         "Satu bilangan bulat n.", "Nilai mutlak n.", "−10000 ≤ n ≤ 10000"),
     en=("Absolute Value", "The absolute value of a number is its distance from zero: the absolute value of −8 is 8, and that of 8 stays 8.\n\nRead an integer, then print its absolute value. Do it with `if`, without the `abs` function.",
         "One integer n.", "The absolute value of n.", "−10000 ≤ n ≤ 10000"),
     ar=("القيمة المطلقة", "القيمة المطلقة لعدد هي بُعده عن الصفر: القيمة المطلقة للعدد −8 هي 8، وللعدد 8 تبقى 8.\n\nاقرأ عددًا صحيحًا ثم اطبع قيمته المطلقة. استخدم `if` ولا تستخدم الدالة `abs`.",
         "عدد صحيح واحد n.", "القيمة المطلقة للعدد n.", "−10000 ≤ n ≤ 10000"))

soal("tiga-sama", "mudah_sekali", "trimmed",
     "a, b, c = r.randint(1, 100), r.randint(1, 100), r.randint(1, 100)\nk = i % 3\nif k == 0:\n    b = c = a\nelif k == 1:\n    b = a\n    c = a % 100 + 1\nprint(a, b, c)\n",
     cpp(r'''    int a, b, c;
    cin >> a >> b >> c;
    if (a == b && b == c) {
        cout << "sama" << endl;
    } else {
        cout << "beda" << endl;
    }'''),
     id=("Ketiganya Sama?", "Baca tiga bilangan bulat. Cetak `sama` bila **ketiganya** bernilai sama, dan `beda` bila ada yang berbeda.\n\nUntuk `5 5 5` jawabannya `sama`; untuk `5 5 6` jawabannya `beda`.\n\nDua syarat bisa digabung dengan `&&` (dan).",
         "Satu baris berisi tiga bilangan bulat.", "`sama` atau `beda`.", "Tiap bilangan antara 1 dan 100."),
     en=("All Three Equal?", "Read three integers. Print `sama` (same) if **all three** have the same value, and `beda` (different) if any of them differs.\n\nFor `5 5 5` the answer is `sama`; for `5 5 6` it is `beda`.\n\nTwo conditions can be combined with `&&` (and).",
         "One line with three integers.", "`sama` or `beda`.", "Each number is between 1 and 100."),
     ar=("هل الثلاثة متساوية؟", "اقرأ ثلاثة أعداد صحيحة. اطبع `sama` (متساوية) إذا كانت **الثلاثة** متساوية، و `beda` (مختلفة) إذا اختلف أحدها.\n\nعند `5 5 5` الجواب `sama`، وعند `5 5 6` الجواب `beda`.\n\nيمكن جمع شرطين بالعامل `&&` (و).",
         "سطر واحد فيه ثلاثة أعداد صحيحة.", "`sama` أو `beda`.", "كل عدد بين 1 و 100."))

soal("habis-dibagi", "mudah_sekali", "trimmed",
     "b = r.randint(2, 20)\na = b * r.randint(1, 50) if i % 2 == 0 else b * r.randint(1, 50) + r.randint(1, b - 1)\nprint(a, b)\n",
     cpp(r'''    int a, b;
    cin >> a >> b;
    if (a % b == 0) {
        cout << "ya" << endl;
    } else {
        cout << "tidak" << endl;
    }'''),
     id=("Habis Dibagi", "Baca dua bilangan bulat positif **a** dan **b**. Cetak `ya` bila a habis dibagi b (sisa pembagiannya nol), dan `tidak` bila bersisa.\n\nUntuk `12 4` jawabannya `ya`; untuk `13 4` jawabannya `tidak`.",
         "Satu baris berisi a dan b.", "`ya` atau `tidak`.", "1 ≤ a ≤ 1020; 2 ≤ b ≤ 20"),
     en=("Divisible", "Read two positive integers **a** and **b**. Print `ya` (yes) if a is divisible by b (the remainder is zero), and `tidak` (no) if there is a remainder.\n\nFor `12 4` the answer is `ya`; for `13 4` it is `tidak`.",
         "One line with a and b.", "`ya` or `tidak`.", "1 ≤ a ≤ 1020; 2 ≤ b ≤ 20"),
     ar=("يقبل القسمة", "اقرأ عددين صحيحين موجبين **a** و **b**. اطبع `ya` (نعم) إذا كان a يقبل القسمة على b (الباقي صفر)، و `tidak` (لا) إذا بقي باقٍ.\n\nعند `12 4` الجواب `ya`، وعند `13 4` الجواب `tidak`.",
         "سطر واحد فيه a و b.", "`ya` أو `tidak`.", "1 ≤ a ≤ 1020؛ 2 ≤ b ≤ 20"))

soal("diskon-belanja", "mudah_sekali", "trimmed", "print({2: 100000, 3: 99000}.get(i, r.randint(1, 300) * 1000))\n",
     cpp(r'''    int total;
    cin >> total;
    if (total >= 100000) {
        total = total - total / 10;
    }
    cout << total << endl;'''),
     id=("Diskon Belanja", "Sebuah toko memberi **diskon 10 persen** untuk belanja **100000 rupiah atau lebih**. Belanja di bawah itu tidak mendapat diskon.\n\nBaca total belanja, lalu cetak jumlah yang harus dibayar.\n\nContoh: belanja 150000 dibayar 135000; belanja 80000 tetap dibayar 80000.",
         "Satu bilangan bulat: total belanja dalam rupiah.", "Satu bilangan bulat: jumlah yang harus dibayar.", "1000 ≤ total ≤ 300000, selalu kelipatan 1000."),
     en=("Shopping Discount", "A shop gives a **10 percent discount** on purchases of **100000 rupiah or more**. Smaller purchases get no discount.\n\nRead the total, then print the amount to pay.\n\nExample: a purchase of 150000 costs 135000; a purchase of 80000 still costs 80000.",
         "One integer: the total in rupiah.", "One integer: the amount to pay.", "1000 ≤ total ≤ 300000, always a multiple of 1000."),
     ar=("خصم التسوّق", "يمنح متجر **خصمًا قدره 10 بالمئة** على المشتريات التي تبلغ **100000 روبية أو أكثر**. المشتريات الأقل لا خصم عليها.\n\nاقرأ إجمالي المشتريات ثم اطبع المبلغ الواجب دفعه.\n\nمثال: مشتريات بقيمة 150000 يُدفع عنها 135000، ومشتريات بقيمة 80000 يُدفع عنها 80000.",
         "عدد صحيح واحد: الإجمالي بالروبية.", "عدد صحيح واحد: المبلغ الواجب دفعه.", "1000 ≤ الإجمالي ≤ 300000، وهو دائمًا من مضاعفات 1000."))

# ================================= MUDAH (8) =================================

soal("terbesar-dari-tiga", "mudah", "trimmed",
     "v = [r.randint(-1000, 1000) for _ in range(3)]\nif i == 3:\n    v[1] = v[0]\nif i == 4:\n    v = [max(v), max(v), min(v)]\nif i == 5:\n    v = [v[0]] * 3\nr.shuffle(v)\nprint(*v)\n",
     cpp(r'''    int a, b, c;
    cin >> a >> b >> c;
    int besar = a;
    if (b > besar) {
        besar = b;
    }
    if (c > besar) {
        besar = c;
    }
    cout << besar << endl;'''),
     id=("Terbesar dari Tiga", "Baca tiga bilangan bulat, lalu cetak yang terbesar. Beberapa bilangan boleh bernilai sama.\n\nUntuk `4 9 -2` jawabannya 9.",
         "Satu baris berisi tiga bilangan bulat.", "Satu bilangan: yang terbesar.", "Tiap bilangan antara −1000 dan 1000."),
     en=("Largest of Three", "Read three integers, then print the largest. Some of them may be equal.\n\nFor `4 9 -2` the answer is 9.",
         "One line with three integers.", "One number: the largest.", "Each number is between −1000 and 1000."),
     ar=("الأكبر من ثلاثة", "اقرأ ثلاثة أعداد صحيحة ثم اطبع أكبرها. قد يتساوى بعضها.\n\nعند `4 9 -2` الجواب 9.",
         "سطر واحد فيه ثلاثة أعداد صحيحة.", "عدد واحد: الأكبر.", "كل عدد بين −1000 و 1000."))

soal("wujud-air", "mudah", "trimmed", "print({2: 0, 3: 100, 4: 1, 5: 99, 6: -1, 7: 101}.get(i, r.randint(-50, 150)))\n",
     cpp(r'''    int suhu;
    cin >> suhu;
    if (suhu <= 0) {
        cout << "es" << endl;
    } else if (suhu >= 100) {
        cout << "uap" << endl;
    } else {
        cout << "air" << endl;
    }'''),
     id=("Wujud Air", "Pada tekanan biasa, air membeku pada suhu 0 derajat Celsius atau kurang, dan menjadi uap pada suhu 100 derajat atau lebih. Di antara keduanya air berwujud cair.\n\nBaca suhu dalam derajat Celsius, lalu cetak `es`, `air`, atau `uap`.",
         "Satu bilangan bulat: suhu.", "Satu kata: `es`, `air`, atau `uap`.", "−50 ≤ suhu ≤ 150"),
     en=("State of Water", "At normal pressure water freezes at 0 degrees Celsius or below and becomes steam at 100 degrees or above. In between it is liquid.\n\nRead the temperature in degrees Celsius, then print `es` (ice), `air` (liquid water) or `uap` (steam).",
         "One integer: the temperature.", "One word: `es`, `air` or `uap`.", "−50 ≤ temperature ≤ 150"),
     ar=("حالة الماء", "تحت الضغط العادي يتجمد الماء عند 0 درجة مئوية أو أقل، ويصير بخارًا عند 100 درجة أو أكثر، وبينهما يكون سائلًا.\n\nاقرأ درجة الحرارة بالمئوية ثم اطبع `es` (جليد) أو `air` (ماء سائل) أو `uap` (بخار).",
         "عدد صحيح واحد: درجة الحرارة.", "كلمة واحدة: `es` أو `air` أو `uap`.", "−50 ≤ درجة الحرارة ≤ 150"))

soal("nilai-huruf", "mudah", "trimmed", "print({2: 85, 3: 84, 4: 70, 5: 69, 6: 55, 7: 54, 8: 40, 9: 39}.get(i, r.randint(0, 100)))\n",
     cpp(r'''    int nilai;
    cin >> nilai;
    if (nilai >= 85) {
        cout << "A" << endl;
    } else if (nilai >= 70) {
        cout << "B" << endl;
    } else if (nilai >= 55) {
        cout << "C" << endl;
    } else if (nilai >= 40) {
        cout << "D" << endl;
    } else {
        cout << "E" << endl;
    }'''),
     id=("Nilai Huruf", "Nilai angka diubah menjadi nilai huruf dengan aturan berikut:\n\n- 85 – 100: A\n- 70 – 84: B\n- 55 – 69: C\n- 40 – 54: D\n- 0 – 39: E\n\nBaca sebuah nilai angka, lalu cetak nilai hurufnya.",
         "Satu bilangan bulat: nilai angka.", "Satu huruf besar.", "0 ≤ nilai ≤ 100"),
     en=("Letter Grade", "A numeric mark is turned into a letter grade by this rule:\n\n- 85 – 100: A\n- 70 – 84: B\n- 55 – 69: C\n- 40 – 54: D\n- 0 – 39: E\n\nRead a numeric mark, then print its letter grade.",
         "One integer: the numeric mark.", "One capital letter.", "0 ≤ mark ≤ 100"),
     ar=("الدرجة بالحروف", "تُحوَّل الدرجة الرقمية إلى درجة بالحروف وفق القاعدة الآتية:\n\n- 85 – 100: A\n- 70 – 84: B\n- 55 – 69: C\n- 40 – 54: D\n- 0 – 39: E\n\nاقرأ درجة رقمية ثم اطبع درجتها بالحروف.",
         "عدد صحيح واحد: الدرجة الرقمية.", "حرف كبير واحد.", "0 ≤ الدرجة ≤ 100"))

soal("nama-hari", "mudah", "trimmed", BENIH + "print((i + s) % 7 + 1)\n",
     cpp(r'''    int n;
    cin >> n;
    switch (n) {
        case 1: cout << "Senin" << endl; break;
        case 2: cout << "Selasa" << endl; break;
        case 3: cout << "Rabu" << endl; break;
        case 4: cout << "Kamis" << endl; break;
        case 5: cout << "Jumat" << endl; break;
        case 6: cout << "Sabtu" << endl; break;
        default: cout << "Minggu" << endl; break;
    }'''),
     id=("Nama Hari", "Hari dalam sepekan diberi nomor 1 sampai 7: 1 = `Senin`, 2 = `Selasa`, 3 = `Rabu`, 4 = `Kamis`, 5 = `Jumat`, 6 = `Sabtu`, 7 = `Minggu`.\n\nBaca nomor hari, lalu cetak namanya persis seperti tertulis di atas.\n\nSoal seperti ini cocok dikerjakan dengan `switch`.",
         "Satu bilangan bulat n.", "Nama hari ke-n.", "1 ≤ n ≤ 7"),
     en=("Name of the Day", "The days of the week are numbered 1 to 7: 1 = `Senin`, 2 = `Selasa`, 3 = `Rabu`, 4 = `Kamis`, 5 = `Jumat`, 6 = `Sabtu`, 7 = `Minggu` (Monday to Sunday in Indonesian).\n\nRead the number of a day, then print its name exactly as written above.\n\nA problem like this suits `switch`.",
         "One integer n.", "The name of day n.", "1 ≤ n ≤ 7"),
     ar=("اسم اليوم", "أيام الأسبوع مرقّمة من 1 إلى 7: 1 = `Senin`، 2 = `Selasa`، 3 = `Rabu`، 4 = `Kamis`، 5 = `Jumat`، 6 = `Sabtu`، 7 = `Minggu` (من الاثنين إلى الأحد بالإندونيسية).\n\nاقرأ رقم اليوم ثم اطبع اسمه تمامًا كما هو مكتوب أعلاه.\n\nمسألة كهذه يناسبها `switch`.",
         "عدد صحيح واحد n.", "اسم اليوم رقم n.", "1 ≤ n ≤ 7"))

soal("tahun-kabisat", "mudah", "trimmed",
     "k = i % 4\nif k == 0:\n    t = r.choice([1700, 1800, 1900, 2100, 2200, 2300])\nelif k == 1:\n    t = r.choice([1600, 2000, 2400])\nelif k == 2:\n    t = 4 * r.randint(401, 599)\n    if t % 100 == 0:\n        t += 4\nelse:\n    t = r.randint(1601, 2399)\nprint(t)\n",
     cpp(r'''    int tahun;
    cin >> tahun;
    if (tahun % 400 == 0) {
        cout << "kabisat" << endl;
    } else if (tahun % 100 == 0) {
        cout << "bukan kabisat" << endl;
    } else if (tahun % 4 == 0) {
        cout << "kabisat" << endl;
    } else {
        cout << "bukan kabisat" << endl;
    }'''),
     id=("Tahun Kabisat", "Sebuah tahun adalah tahun kabisat bila:\n\n- habis dibagi 400; **atau**\n- habis dibagi 4 **tetapi tidak** habis dibagi 100.\n\nJadi 2024 dan 2000 kabisat, sedangkan 2023 dan 1900 bukan.\n\nBaca sebuah tahun, lalu cetak `kabisat` atau `bukan kabisat`.",
         "Satu bilangan bulat: tahun.", "`kabisat` atau `bukan kabisat`.", "1600 ≤ tahun ≤ 2400"),
     en=("Leap Year", "A year is a leap year when:\n\n- it is divisible by 400; **or**\n- it is divisible by 4 **but not** by 100.\n\nSo 2024 and 2000 are leap years, while 2023 and 1900 are not.\n\nRead a year, then print `kabisat` (leap) or `bukan kabisat` (not leap).",
         "One integer: the year.", "`kabisat` or `bukan kabisat`.", "1600 ≤ year ≤ 2400"),
     ar=("السنة الكبيسة", "تكون السنة كبيسة إذا:\n\n- قبلت القسمة على 400؛ **أو**\n- قبلت القسمة على 4 **ولم** تقبل القسمة على 100.\n\nفالسنتان 2024 و 2000 كبيستان، أما 2023 و 1900 فليستا كذلك.\n\nاقرأ سنة ثم اطبع `kabisat` (كبيسة) أو `bukan kabisat` (غير كبيسة).",
         "عدد صحيح واحد: السنة.", "`kabisat` أو `bukan kabisat`.", "1600 ≤ السنة ≤ 2400"))

soal("vokal-konsonan", "mudah", "trimmed", "print(r.choice('aiueo') if i % 2 == 0 else r.choice('bcdfghjklmnpqrstvwxyz'))\n",
     cpp(r'''    char h;
    cin >> h;
    if (h == 'a' || h == 'i' || h == 'u' || h == 'e' || h == 'o') {
        cout << "vokal" << endl;
    } else {
        cout << "konsonan" << endl;
    }'''),
     id=("Vokal atau Konsonan", "Huruf vokal adalah `a`, `i`, `u`, `e`, dan `o`. Huruf lainnya adalah konsonan.\n\nBaca satu huruf kecil, lalu cetak `vokal` atau `konsonan`.\n\nBeberapa syarat bisa digabung dengan `||` (atau).",
         "Satu huruf kecil.", "`vokal` atau `konsonan`.", "Huruf antara `a` dan `z`."),
     en=("Vowel or Consonant", "The vowels are `a`, `i`, `u`, `e` and `o`. Every other letter is a consonant.\n\nRead one lowercase letter, then print `vokal` (vowel) or `konsonan` (consonant).\n\nSeveral conditions can be combined with `||` (or).",
         "One lowercase letter.", "`vokal` or `konsonan`.", "A letter between `a` and `z`."),
     ar=("حرف علة أم ساكن", "حروف العلة هي `a` و `i` و `u` و `e` و `o`، وكل حرف آخر ساكن.\n\nاقرأ حرفًا لاتينيًا صغيرًا واحدًا ثم اطبع `vokal` (حرف علة) أو `konsonan` (حرف ساكن).\n\nيمكن جمع عدة شروط بالعامل `||` (أو).",
         "حرف صغير واحد.", "`vokal` أو `konsonan`.", "حرف بين `a` و `z`."))

soal("kuadran", "mudah", "trimmed",
     "x, y = r.randint(1, 100), r.randint(1, 100)\nk = i % 4\nif k == 1 or k == 2:\n    x = -x\nif k == 2 or k == 3:\n    y = -y\nprint(x, y)\n",
     cpp(r'''    int x, y;
    cin >> x >> y;
    if (x > 0 && y > 0) {
        cout << 1 << endl;
    } else if (x < 0 && y > 0) {
        cout << 2 << endl;
    } else if (x < 0 && y < 0) {
        cout << 3 << endl;
    } else {
        cout << 4 << endl;
    }'''),
     id=("Kuadran", "Bidang koordinat dibagi menjadi empat kuadran:\n\n- kuadran 1: x positif, y positif;\n- kuadran 2: x negatif, y positif;\n- kuadran 3: x negatif, y negatif;\n- kuadran 4: x positif, y negatif.\n\nBaca koordinat sebuah titik, lalu cetak nomor kuadrannya. Titik tidak pernah terletak di sumbu.",
         "Satu baris berisi dua bilangan bulat x dan y.", "Satu bilangan: 1, 2, 3, atau 4.", "−100 ≤ x, y ≤ 100; x ≠ 0 dan y ≠ 0"),
     en=("Quadrant", "The coordinate plane is divided into four quadrants:\n\n- quadrant 1: x positive, y positive;\n- quadrant 2: x negative, y positive;\n- quadrant 3: x negative, y negative;\n- quadrant 4: x positive, y negative.\n\nRead the coordinates of a point, then print the number of its quadrant. The point never lies on an axis.",
         "One line with two integers x and y.", "One number: 1, 2, 3 or 4.", "−100 ≤ x, y ≤ 100; x ≠ 0 and y ≠ 0"),
     ar=("الربع", "ينقسم المستوى الإحداثي إلى أربعة أرباع:\n\n- الربع 1: x موجب و y موجب؛\n- الربع 2: x سالب و y موجب؛\n- الربع 3: x سالب و y سالب؛\n- الربع 4: x موجب و y سالب.\n\nاقرأ إحداثيي نقطة ثم اطبع رقم ربعها. النقطة لا تقع أبدًا على أحد المحورين.",
         "سطر واحد فيه عددان صحيحان x و y.", "عدد واحد: 1 أو 2 أو 3 أو 4.", "−100 ≤ x, y ≤ 100؛ x ≠ 0 و y ≠ 0"))

soal("jenis-karakter", "mudah", "trimmed",
     "print(r.choice(['ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz', '0123456789', '!@#$%&*+-=?'][i % 4]))\n",
     cpp(r'''    char c;
    cin >> c;
    if (c >= 'A' && c <= 'Z') {
        cout << "huruf besar" << endl;
    } else if (c >= 'a' && c <= 'z') {
        cout << "huruf kecil" << endl;
    } else if (c >= '0' && c <= '9') {
        cout << "angka" << endl;
    } else {
        cout << "simbol" << endl;
    }'''),
     id=("Jenis Karakter", "Baca satu karakter, lalu cetak jenisnya:\n\n- `huruf besar` untuk `A` sampai `Z`;\n- `huruf kecil` untuk `a` sampai `z`;\n- `angka` untuk `0` sampai `9`;\n- `simbol` untuk karakter lainnya.\n\nKarakter bisa dibandingkan seperti bilangan: `c >= 'a'` benar bila c adalah `a` atau huruf sesudahnya.",
         "Satu karakter, bukan spasi.", "`huruf besar`, `huruf kecil`, `angka`, atau `simbol`.", "Karakter adalah huruf, angka, atau salah satu dari `!@#$%&*+-=?`"),
     en=("Kind of Character", "Read one character, then print its kind:\n\n- `huruf besar` (capital letter) for `A` to `Z`;\n- `huruf kecil` (lowercase letter) for `a` to `z`;\n- `angka` (digit) for `0` to `9`;\n- `simbol` (symbol) for any other character.\n\nCharacters can be compared like numbers: `c >= 'a'` is true when c is `a` or a later letter.",
         "One character, not a space.", "`huruf besar`, `huruf kecil`, `angka` or `simbol`.", "The character is a letter, a digit, or one of `!@#$%&*+-=?`"),
     ar=("نوع المحرف", "اقرأ محرفًا واحدًا ثم اطبع نوعه:\n\n- `huruf besar` (حرف كبير) من `A` إلى `Z`؛\n- `huruf kecil` (حرف صغير) من `a` إلى `z`؛\n- `angka` (رقم) من `0` إلى `9`؛\n- `simbol` (رمز) لأي محرف آخر.\n\nيمكن مقارنة المحارف كالأعداد: `c >= 'a'` صحيح إذا كان c هو `a` أو حرفًا بعده.",
         "محرف واحد غير المسافة.", "`huruf besar` أو `huruf kecil` أو `angka` أو `simbol`.", "المحرف حرف أو رقم أو أحد الرموز `!@#$%&*+-=?`"))

# ============================== MUDAH-SEDANG (6) ==============================

soal("kalkulator-operator", "mudah_sedang", "trimmed",
     "a, b = r.randint(0, 1000), r.randint(1, 1000)\nop = '+-*/'[i % 4]\nif i == 7:\n    op, b = '/', 0\nif i == 11:\n    op, a = '/', b * r.randint(1, 9)\nprint(a, op, b)\n",
     cpp(r'''    int a, b;
    char op;
    cin >> a >> op >> b;
    switch (op) {
        case '+':
            cout << a + b << endl;
            break;
        case '-':
            cout << a - b << endl;
            break;
        case '*':
            cout << a * b << endl;
            break;
        default:
            if (b == 0) {
                cout << "tidak bisa dibagi nol" << endl;
            } else {
                cout << a / b << endl;
            }
            break;
    }'''),
     id=("Kalkulator dengan Operator", "Buat kalkulator sederhana. Masukannya sebuah hitungan seperti `12 + 5`: bilangan pertama, operator, bilangan kedua.\n\n"
         "Operatornya salah satu dari `+`, `-`, `*`, `/`. Pembagian adalah pembagian bilangan bulat (sisanya dibuang), jadi `17 / 5` menghasilkan 3.\n\n"
         "Bila diminta membagi dengan nol, cetak `tidak bisa dibagi nol`.\n\nContoh lain: `7 - 10` menghasilkan -3.",
         "Satu baris: bilangan bulat a, operator, bilangan bulat b, dipisah spasi.", "Hasil hitungan, atau `tidak bisa dibagi nol`.", "0 ≤ a, b ≤ 9000"),
     en=("Calculator with an Operator", "Build a simple calculator. The input is a calculation such as `12 + 5`: the first number, an operator, the second number.\n\n"
         "The operator is one of `+`, `-`, `*`, `/`. Division is integer division (the remainder is dropped), so `17 / 5` gives 3.\n\n"
         "When asked to divide by zero, print `tidak bisa dibagi nol` (cannot divide by zero).\n\nAnother example: `7 - 10` gives -3.",
         "One line: integer a, the operator, integer b, separated by spaces.", "The result, or `tidak bisa dibagi nol`.", "0 ≤ a, b ≤ 9000"),
     ar=("آلة حاسبة بعامل", "اصنع آلة حاسبة بسيطة. المدخل عملية مثل `12 + 5`: العدد الأول ثم العامل ثم العدد الثاني.\n\n"
         "العامل أحد `+` و `-` و `*` و `/`. القسمة قسمة أعداد صحيحة (يُهمل الباقي)، فالعملية `17 / 5` تعطي 3.\n\n"
         "إذا طُلبت القسمة على صفر فاطبع `tidak bisa dibagi nol` (لا تمكن القسمة على صفر).\n\nمثال آخر: `7 - 10` تعطي -3.",
         "سطر واحد: العدد الصحيح a ثم العامل ثم العدد الصحيح b، تفصل بينها مسافات.", "ناتج العملية، أو `tidak bisa dibagi nol`.", "0 ≤ a, b ≤ 9000"))

soal("jenis-segitiga", "mudah_sedang", "trimmed",
     "k = i % 4\nif k == 0:\n    a = r.randint(1, 100)\n    v = [a, a, a]\nelif k == 1:\n    a = r.randint(2, 100)\n    b = r.randint(1, min(100, 2 * a - 1))\n    if b == a:\n        b = a - 1\n    v = [a, a, b]\nelif k == 2:\n    a = r.randint(3, 98)\n    b = r.randint(a + 1, 99)\n    c = r.randint(b + 1, min(100, a + b - 1)) if a + b - 1 > b else b + 1\n    v = [a, b, c] if c < a + b else [3, 4, 5]\nelse:\n    a, b = r.randint(1, 50), r.randint(1, 50)\n    v = [a, b, r.randint(a + b, 100)]\nr.shuffle(v)\nprint(*v)\n",
     cpp(r'''    int a, b, c;
    cin >> a >> b >> c;
    if (a + b <= c || a + c <= b || b + c <= a) {
        cout << "bukan segitiga" << endl;
    } else if (a == b && b == c) {
        cout << "sama sisi" << endl;
    } else if (a == b || b == c || a == c) {
        cout << "sama kaki" << endl;
    } else {
        cout << "sembarang" << endl;
    }'''),
     id=("Jenis Segitiga", "Tiga batang dengan panjang a, b, dan c hanya bisa dirangkai menjadi segitiga bila **jumlah dua batang mana pun lebih besar daripada batang ketiga**.\n\n"
         "Baca ketiga panjang itu, lalu cetak:\n\n- `bukan segitiga` bila tidak bisa dirangkai menjadi segitiga;\n- `sama sisi` bila ketiga sisinya sama panjang;\n- `sama kaki` bila tepat dua sisinya sama panjang;\n- `sembarang` bila ketiga sisinya berbeda.\n\n"
         "Contoh: `3 4 5` sembarang, `5 5 8` sama kaki, `1 2 3` bukan segitiga.",
         "Satu baris berisi tiga bilangan bulat a, b, c.", "Jenis segitiganya.", "1 ≤ a, b, c ≤ 100"),
     en=("Kind of Triangle", "Three rods of lengths a, b and c can form a triangle only when **the sum of any two of them is greater than the third**.\n\n"
         "Read the three lengths, then print:\n\n- `bukan segitiga` (not a triangle) when they cannot form a triangle;\n- `sama sisi` (equilateral) when all three sides are equal;\n- `sama kaki` (isosceles) when exactly two sides are equal;\n- `sembarang` (scalene) when all three sides differ.\n\n"
         "Examples: `3 4 5` is sembarang, `5 5 8` is sama kaki, `1 2 3` is bukan segitiga.",
         "One line with three integers a, b, c.", "The kind of triangle.", "1 ≤ a, b, c ≤ 100"),
     ar=("نوع المثلث", "ثلاثة قضبان أطوالها a و b و c لا تشكّل مثلثًا إلا إذا كان **مجموع أي اثنين منها أكبر من الثالث**.\n\n"
         "اقرأ الأطوال الثلاثة ثم اطبع:\n\n- `bukan segitiga` (ليس مثلثًا) إذا تعذّر تشكيل مثلث؛\n- `sama sisi` (متساوي الأضلاع) إذا تساوت الأضلاع الثلاثة؛\n- `sama kaki` (متساوي الساقين) إذا تساوى ضلعان فقط؛\n- `sembarang` (مختلف الأضلاع) إذا اختلفت الأضلاع الثلاثة.\n\n"
         "أمثلة: `3 4 5` هو sembarang، و `5 5 8` هو sama kaki، و `1 2 3` هو bukan segitiga.",
         "سطر واحد فيه ثلاثة أعداد صحيحة a و b و c.", "نوع المثلث.", "1 ≤ a, b, c ≤ 100"))

soal("hari-dalam-bulan", "mudah_sedang", "trimmed",
     BENIH + "b, t = (i + s) % 12 + 1, r.randint(1900, 2100)\nif i in (3, 6, 9):\n    b, t = 2, r.choice([1900, 2000, 2023, 2024, 2100, 2096])\nprint(b, t)\n",
     cpp(r'''    int bulan, tahun;
    cin >> bulan >> tahun;
    int hari;
    switch (bulan) {
        case 4:
        case 6:
        case 9:
        case 11:
            hari = 30;
            break;
        case 2:
            if (tahun % 400 == 0 || (tahun % 4 == 0 && tahun % 100 != 0)) {
                hari = 29;
            } else {
                hari = 28;
            }
            break;
        default:
            hari = 31;
            break;
    }
    cout << hari << endl;'''),
     id=("Banyak Hari dalam Sebulan", "Baca nomor bulan (1 = Januari, …, 12 = Desember) dan tahun, lalu cetak banyak hari dalam bulan itu.\n\n"
         "April, Juni, September, dan November berisi 30 hari. Februari berisi 28 hari, atau 29 hari pada tahun kabisat. Bulan lainnya berisi 31 hari.\n\n"
         "Tahun kabisat: habis dibagi 400, atau habis dibagi 4 tetapi tidak habis dibagi 100.\n\nContoh: `2 2024` menghasilkan 29, `2 1900` menghasilkan 28, `11 2025` menghasilkan 30.",
         "Satu baris berisi bulan dan tahun.", "Satu bilangan: banyak hari.", "1 ≤ bulan ≤ 12; 1900 ≤ tahun ≤ 2100"),
     en=("Days in a Month", "Read the number of a month (1 = January, …, 12 = December) and a year, then print how many days that month has.\n\n"
         "April, June, September and November have 30 days. February has 28 days, or 29 in a leap year. The other months have 31 days.\n\n"
         "Leap year: divisible by 400, or divisible by 4 but not by 100.\n\nExamples: `2 2024` gives 29, `2 1900` gives 28, `11 2025` gives 30.",
         "One line with the month and the year.", "One number: the number of days.", "1 ≤ month ≤ 12; 1900 ≤ year ≤ 2100"),
     ar=("عدد أيام الشهر", "اقرأ رقم الشهر (1 = يناير، …، 12 = ديسمبر) والسنة ثم اطبع عدد أيام ذلك الشهر.\n\n"
         "أبريل ويونيو وسبتمبر ونوفمبر فيها 30 يومًا. فبراير فيه 28 يومًا، أو 29 في السنة الكبيسة. بقية الشهور فيها 31 يومًا.\n\n"
         "السنة الكبيسة: تقبل القسمة على 400، أو تقبل القسمة على 4 ولا تقبلها على 100.\n\nأمثلة: `2 2024` تعطي 29، و `2 1900` تعطي 28، و `11 2025` تعطي 30.",
         "سطر واحد فيه الشهر والسنة.", "عدد واحد: عدد الأيام.", "1 ≤ الشهر ≤ 12؛ 1900 ≤ السنة ≤ 2100"))

soal("urutkan-tiga", "mudah_sedang", "trimmed",
     "v = [r.randint(-1000, 1000) for _ in range(3)]\nif i == 3:\n    v[2] = v[0]\nif i == 4:\n    v = sorted(v, reverse=True)\nif i == 5:\n    v = sorted(v)\nif i == 6:\n    v = [v[0]] * 3\nprint(*v)\n",
     cpp(r'''    int a, b, c, t;
    cin >> a >> b >> c;
    if (a > b) {
        t = a;
        a = b;
        b = t;
    }
    if (b > c) {
        t = b;
        b = c;
        c = t;
    }
    if (a > b) {
        t = a;
        a = b;
        b = t;
    }
    cout << a << " " << b << " " << c << endl;'''),
     id=("Urutkan Tiga Bilangan", "Baca tiga bilangan bulat, lalu cetak ketiganya **dari yang terkecil ke yang terbesar**, dipisah satu spasi.\n\nUntuk `8 -1 5` keluarannya `-1 5 8`.\n\n"
         "Belum perlu perulangan: tiga kali \"tukar bila urutannya salah\" sudah cukup. Pikirkan pasangan mana saja yang harus dibandingkan.",
         "Satu baris berisi tiga bilangan bulat.", "Ketiga bilangan itu, terurut naik.", "Tiap bilangan antara −1000 dan 1000; boleh ada yang sama."),
     en=("Sort Three Numbers", "Read three integers, then print them **from smallest to largest**, separated by one space.\n\nFor `8 -1 5` the output is `-1 5 8`.\n\n"
         "No loop is needed yet: three times \"swap if out of order\" is enough. Think about which pairs have to be compared.",
         "One line with three integers.", "The three numbers in ascending order.", "Each number is between −1000 and 1000; some may be equal."),
     ar=("ترتيب ثلاثة أعداد", "اقرأ ثلاثة أعداد صحيحة ثم اطبعها **من الأصغر إلى الأكبر** تفصل بينها مسافة واحدة.\n\nعند `8 -1 5` يكون المخرج `-1 5 8`.\n\n"
         "لا حاجة إلى حلقة بعد: يكفي ثلاث مرات «بدِّل إذا كان الترتيب خاطئًا». فكّر في الأزواج التي يجب مقارنتها.",
         "سطر واحد فيه ثلاثة أعداد صحيحة.", "الأعداد الثلاثة مرتبة تصاعديًا.", "كل عدد بين −1000 و 1000، وقد يتساوى بعضها."))

soal("kategori-imt", "mudah_sedang", "trimmed",
     "k = i % 4\nwhile True:\n    b, t = r.randint(35, 150), r.randint(140, 200)\n    x = b * 10000 / (t * t)\n    kat = 0 if x < 18.5 else 1 if x < 25 else 2 if x < 30 else 3\n"
     "    aman = all(abs(x - g) > 1e-3 for g in (18.5, 25, 30)) and abs(x * 10 % 1 - 0.5) > 1e-3\n    if kat == k and aman:\n        break\nprint(b, t)\n",
     cpp(r'''    int berat, tinggi;
    cin >> berat >> tinggi;
    double meter = tinggi / 100.0;
    double imt = berat / (meter * meter);
    cout << fixed << setprecision(1) << imt << " ";
    if (imt < 18.5) {
        cout << "kurus" << endl;
    } else if (imt < 25) {
        cout << "normal" << endl;
    } else if (imt < 30) {
        cout << "gemuk" << endl;
    } else {
        cout << "obesitas" << endl;
    }''', "iomanip"),
     id=("Indeks Massa Tubuh", "Indeks massa tubuh (IMT) dihitung dari berat dan tinggi badan:\n\n**IMT = berat (kg) / (tinggi (m) × tinggi (m))**\n\n"
         "Baca berat dalam kilogram dan tinggi dalam **sentimeter**. Cetak IMT dengan satu angka di belakang koma, satu spasi, lalu kategorinya:\n\n"
         "- kurang dari 18.5: `kurus`\n- 18.5 sampai kurang dari 25: `normal`\n- 25 sampai kurang dari 30: `gemuk`\n- 30 atau lebih: `obesitas`\n\n"
         "Contoh: berat 70 kg dan tinggi 175 cm memberi IMT 22.857…, sehingga keluarannya `22.9 normal`.\n\nKategori ditentukan dari IMT sebelum dibulatkan.",
         "Satu baris berisi dua bilangan bulat: berat (kg) dan tinggi (cm).", "IMT dengan satu angka di belakang koma, spasi, kategori.", "35 ≤ berat ≤ 150; 140 ≤ tinggi ≤ 200"),
     en=("Body Mass Index", "The body mass index (BMI, here IMT) is computed from weight and height:\n\n**IMT = weight (kg) / (height (m) × height (m))**\n\n"
         "Read the weight in kilograms and the height in **centimetres**. Print the IMT with one digit after the decimal point, one space, then its category:\n\n"
         "- below 18.5: `kurus`\n- 18.5 to below 25: `normal`\n- 25 to below 30: `gemuk`\n- 30 or more: `obesitas`\n\n"
         "Example: a weight of 70 kg and a height of 175 cm give an IMT of 22.857…, so the output is `22.9 normal`.\n\nThe category is decided from the IMT before rounding.",
         "One line with two integers: weight (kg) and height (cm).", "The IMT with one digit after the decimal point, a space, the category.", "35 ≤ weight ≤ 150; 140 ≤ height ≤ 200"),
     ar=("مؤشر كتلة الجسم", "يُحسب مؤشر كتلة الجسم (IMT) من الوزن والطول:\n\n**IMT = الوزن (كغ) / (الطول (م) × الطول (م))**\n\n"
         "اقرأ الوزن بالكيلوغرام والطول **بالسنتيمتر**. اطبع المؤشر برقم واحد بعد الفاصلة ثم مسافة ثم الفئة:\n\n"
         "- أقل من 18.5: `kurus`\n- من 18.5 إلى أقل من 25: `normal`\n- من 25 إلى أقل من 30: `gemuk`\n- 30 أو أكثر: `obesitas`\n\n"
         "مثال: وزن 70 كغ وطول 175 سم يعطيان مؤشرًا 22.857…، فيكون المخرج `22.9 normal`.\n\nتُحدَّد الفئة من المؤشر قبل التقريب.",
         "سطر واحد فيه عددان صحيحان: الوزن (كغ) والطول (سم).", "المؤشر برقم واحد بعد الفاصلة ثم مسافة ثم الفئة.", "35 ≤ الوزن ≤ 150؛ 140 ≤ الطول ≤ 200"))

soal("jam-dua-belas", "mudah_sedang", "trimmed",
     "j, m = r.randint(0, 23), r.randint(0, 59)\nj = {2: 0, 3: 12, 4: 11, 5: 13, 6: 23}.get(i, j)\nif i == 7:\n    m = r.randint(0, 9)\nprint(j, m)\n",
     cpp(r'''    int jam, menit;
    cin >> jam >> menit;
    int j = jam % 12;
    if (j == 0) {
        j = 12;
    }
    cout << j << ":" << setfill('0') << setw(2) << menit;
    if (jam < 12) {
        cout << " AM" << endl;
    } else {
        cout << " PM" << endl;
    }''', "iomanip"),
     id=("Jam Dua Belas", "Ubah waktu format 24 jam menjadi format 12 jam dengan keterangan `AM` atau `PM`.\n\n"
         "- Jam 0 sampai 11 adalah `AM`, jam 12 sampai 23 adalah `PM`.\n- Jam 0 ditulis 12 (tengah malam adalah `12:00 AM`), dan jam 12 tetap 12 (tengah hari adalah `12:00 PM`).\n- Jam 13 sampai 23 ditulis 1 sampai 11.\n\n"
         "Jam ditulis apa adanya, menit selalu dua digit.\n\nContoh: `0 5` menjadi `12:05 AM`, `9 30` menjadi `9:30 AM`, `15 7` menjadi `3:07 PM`.",
         "Satu baris berisi jam dan menit.", "Waktu dalam format 12 jam, misalnya `3:07 PM`.", "0 ≤ jam ≤ 23; 0 ≤ menit ≤ 59"),
     en=("Twelve-Hour Clock", "Turn a 24-hour time into the 12-hour form with `AM` or `PM`.\n\n"
         "- Hours 0 to 11 are `AM`, hours 12 to 23 are `PM`.\n- Hour 0 is written 12 (midnight is `12:00 AM`), and hour 12 stays 12 (noon is `12:00 PM`).\n- Hours 13 to 23 are written 1 to 11.\n\n"
         "The hour is written as it is, the minutes always with two digits.\n\nExamples: `0 5` becomes `12:05 AM`, `9 30` becomes `9:30 AM`, `15 7` becomes `3:07 PM`.",
         "One line with the hour and the minutes.", "The time in 12-hour form, for example `3:07 PM`.", "0 ≤ hour ≤ 23; 0 ≤ minutes ≤ 59"),
     ar=("ساعة الاثني عشر", "حوِّل وقتًا بنظام 24 ساعة إلى نظام 12 ساعة مع `AM` أو `PM`.\n\n"
         "- الساعات من 0 إلى 11 هي `AM`، ومن 12 إلى 23 هي `PM`.\n- الساعة 0 تُكتب 12 (منتصف الليل `12:00 AM`)، والساعة 12 تبقى 12 (الظهر `12:00 PM`).\n- الساعات من 13 إلى 23 تُكتب من 1 إلى 11.\n\n"
         "تُكتب الساعة كما هي، والدقائق دائمًا برقمين.\n\nأمثلة: `0 5` تصير `12:05 AM`، و `9 30` تصير `9:30 AM`، و `15 7` تصير `3:07 PM`.",
         "سطر واحد فيه الساعة والدقائق.", "الوقت بنظام 12 ساعة، مثل `3:07 PM`.", "0 ≤ الساعة ≤ 23؛ 0 ≤ الدقائق ≤ 59"))

# ================================ SEDANG (6) ================================

soal("pajak-penghasilan", "sedang", "trimmed", "print({2: 60, 3: 61, 4: 250, 5: 251, 6: 1, 7: 1000}.get(i, r.randint(1, 1000)))\n",
     cpp(r'''    int p;
    cin >> p;
    int pajak;
    if (p <= 60) {
        pajak = p * 50;
    } else if (p <= 250) {
        pajak = 60 * 50 + (p - 60) * 150;
    } else {
        pajak = 60 * 50 + 190 * 150 + (p - 250) * 250;
    }
    cout << pajak << endl;'''),
     id=("Pajak Penghasilan", "Sebuah negara memungut pajak penghasilan **berlapis**. Penghasilan setahun dipotong per lapisan:\n\n"
         "- 60 juta pertama: 5 persen\n- di atas 60 juta sampai 250 juta: 15 persen\n- di atas 250 juta: 25 persen\n\n"
         "Tarif yang lebih tinggi hanya berlaku untuk **bagian** penghasilan di lapisan itu, bukan untuk seluruh penghasilan.\n\n"
         "Baca penghasilan setahun dalam **juta rupiah**, lalu cetak pajaknya dalam **ribu rupiah**.\n\n"
         "Contoh: penghasilan 100 juta. Lapisan pertama 60 juta × 5% = 3 juta; sisanya 40 juta × 15% = 6 juta. Pajak 9 juta, jadi keluarannya 9000.\n\n"
         "Contoh lain: penghasilan 300 juta menghasilkan 44000.",
         "Satu bilangan bulat p: penghasilan dalam juta rupiah.", "Satu bilangan bulat: pajak dalam ribu rupiah.", "1 ≤ p ≤ 1000"),
     en=("Income Tax", "A country collects income tax **in brackets**. A year's income is taxed bracket by bracket:\n\n"
         "- the first 60 million: 5 percent\n- above 60 million up to 250 million: 15 percent\n- above 250 million: 25 percent\n\n"
         "A higher rate applies only to the **part** of the income inside that bracket, not to the whole income.\n\n"
         "Read the yearly income in **millions of rupiah**, then print the tax in **thousands of rupiah**.\n\n"
         "Example: an income of 100 million. First bracket 60 million × 5% = 3 million; the remaining 40 million × 15% = 6 million. The tax is 9 million, so the output is 9000.\n\n"
         "Another example: an income of 300 million gives 44000.",
         "One integer p: the income in millions of rupiah.", "One integer: the tax in thousands of rupiah.", "1 ≤ p ≤ 1000"),
     ar=("ضريبة الدخل", "تفرض دولة ضريبة دخل **على شرائح**. يُقتطع من الدخل السنوي شريحةً شريحة:\n\n"
         "- أول 60 مليونًا: 5 بالمئة\n- ما فوق 60 مليونًا حتى 250 مليونًا: 15 بالمئة\n- ما فوق 250 مليونًا: 25 بالمئة\n\n"
         "النسبة الأعلى تُطبَّق على **الجزء** الواقع في تلك الشريحة فقط، لا على الدخل كله.\n\n"
         "اقرأ الدخل السنوي **بملايين الروبيات** ثم اطبع الضريبة **بآلاف الروبيات**.\n\n"
         "مثال: دخل قدره 100 مليون. الشريحة الأولى 60 مليونًا × 5% = 3 ملايين، والباقي 40 مليونًا × 15% = 6 ملايين. الضريبة 9 ملايين، فالمخرج 9000.\n\n"
         "مثال آخر: دخل قدره 300 مليون يعطي 44000.",
         "عدد صحيح واحد p: الدخل بملايين الروبيات.", "عدد صحيح واحد: الضريبة بآلاف الروبيات.", "1 ≤ p ≤ 1000"))

soal("titik-dan-persegi-panjang", "sedang", "trimmed",
     "xs, ys = sorted(r.sample(range(-20, 21), 2)), sorted(r.sample(range(-20, 21), 2))\nk = i % 3\nif k == 0 and (xs[1] - xs[0] < 2 or ys[1] - ys[0] < 2):\n    k = 1\n"
     "if k == 0:\n    px, py = r.randint(xs[0] + 1, xs[1] - 1), r.randint(ys[0] + 1, ys[1] - 1)\n"
     "elif k == 1:\n    if r.random() < 0.5:\n        px, py = r.randint(xs[0], xs[1]), r.choice(ys)\n    else:\n        px, py = r.choice(xs), r.randint(ys[0], ys[1])\n"
     "else:\n    while True:\n        px, py = r.randint(-25, 25), r.randint(-25, 25)\n        if not (xs[0] <= px <= xs[1] and ys[0] <= py <= ys[1]):\n            break\n"
     "r.shuffle(xs)\nr.shuffle(ys)\nprint(xs[0], ys[0], xs[1], ys[1])\nprint(px, py)\n",
     cpp(r'''    int x1, y1, x2, y2, px, py;
    cin >> x1 >> y1 >> x2 >> y2;
    cin >> px >> py;
    int kiri = x1, kanan = x2, bawah = y1, atas = y2;
    if (x1 > x2) {
        kiri = x2;
        kanan = x1;
    }
    if (y1 > y2) {
        bawah = y2;
        atas = y1;
    }
    if (px < kiri || px > kanan || py < bawah || py > atas) {
        cout << "di luar" << endl;
    } else if (px == kiri || px == kanan || py == bawah || py == atas) {
        cout << "di tepi" << endl;
    } else {
        cout << "di dalam" << endl;
    }'''),
     id=("Titik dan Persegi Panjang", "Sebuah persegi panjang yang sisi-sisinya sejajar sumbu koordinat ditentukan oleh **dua titik sudut yang berseberangan**. Kedua sudut itu bisa diberikan dalam urutan apa pun: yang pertama belum tentu di kiri bawah.\n\n"
         "Baca kedua sudut itu, lalu sebuah titik P. Cetak:\n\n- `di dalam` bila P berada di dalam persegi panjang;\n- `di tepi` bila P tepat di salah satu sisinya (termasuk di sudut);\n- `di luar` bila P di luar.\n\n"
         "Contoh: sudut (5, 1) dan (1, 4). Titik (3, 2) di dalam, titik (1, 3) di tepi, titik (6, 2) di luar.",
         "Baris pertama: x1 y1 x2 y2, koordinat dua sudut yang berseberangan. Baris kedua: koordinat titik P.", "`di dalam`, `di tepi`, atau `di luar`.", "Semua koordinat bilangan bulat antara −25 dan 25; x1 ≠ x2 dan y1 ≠ y2"),
     en=("Point and Rectangle", "A rectangle with sides parallel to the coordinate axes is given by **two opposite corners**. The corners can come in any order: the first one is not necessarily the lower left.\n\n"
         "Read the two corners, then a point P. Print:\n\n- `di dalam` (inside) when P is inside the rectangle;\n- `di tepi` (on the edge) when P lies exactly on one of its sides (corners included);\n- `di luar` (outside) when P is outside.\n\n"
         "Example: corners (5, 1) and (1, 4). The point (3, 2) is inside, (1, 3) is on the edge, (6, 2) is outside.",
         "First line: x1 y1 x2 y2, the coordinates of two opposite corners. Second line: the coordinates of P.", "`di dalam`, `di tepi` or `di luar`.", "All coordinates are integers between −25 and 25; x1 ≠ x2 and y1 ≠ y2"),
     ar=("نقطة ومستطيل", "مستطيل أضلاعه توازي محوري الإحداثيات يُحدَّد **برأسين متقابلين**. قد يُعطى الرأسان بأي ترتيب: الأول ليس بالضرورة الأسفل الأيسر.\n\n"
         "اقرأ الرأسين ثم نقطة P. اطبع:\n\n- `di dalam` (في الداخل) إذا كانت P داخل المستطيل؛\n- `di tepi` (على الحافة) إذا كانت P على أحد أضلاعه تمامًا (ومنها الرؤوس)؛\n- `di luar` (في الخارج) إذا كانت P خارجه.\n\n"
         "مثال: الرأسان (5, 1) و (1, 4). النقطة (3, 2) في الداخل، والنقطة (1, 3) على الحافة، والنقطة (6, 2) في الخارج.",
         "السطر الأول: x1 y1 x2 y2، إحداثيات رأسين متقابلين. السطر الثاني: إحداثيا النقطة P.", "`di dalam` أو `di tepi` أو `di luar`.", "كل الإحداثيات أعداد صحيحة بين −25 و 25؛ x1 ≠ x2 و y1 ≠ y2"))

soal("jadwal-bentrok", "sedang", "trimmed",
     "k = i % 4\na = r.randint(0, 700)\nb = a + r.randint(30, 300)\nif k == 0:\n    c = r.randint(a + 1, b - 1)\n    d = b + r.randint(1, 120)\nelif k == 1:\n    c = r.randint(a, b - 20)\n    d = r.randint(c + 10, b)\n"
     "elif k == 2:\n    c = b + r.randint(1, 100)\n    d = c + r.randint(30, 200)\nelse:\n    c = b\n    d = c + r.randint(30, 200)\nif r.random() < 0.5:\n    a, b, c, d = c, d, a, b\nprint(a, b)\nprint(c, d)\n",
     cpp(r'''    int a, b, c, d;
    cin >> a >> b;
    cin >> c >> d;
    int mulai = a;
    if (c > mulai) {
        mulai = c;
    }
    int selesai = b;
    if (d < selesai) {
        selesai = d;
    }
    if (selesai > mulai) {
        cout << "bentrok " << selesai - mulai << " menit" << endl;
    } else {
        cout << "aman" << endl;
    }'''),
     id=("Jadwal Bentrok", "Dua kegiatan dijadwalkan pada hari yang sama. Waktu dinyatakan sebagai **menit sejak tengah malam** (misalnya 480 berarti pukul 08.00).\n\n"
         "Kegiatan pertama berlangsung dari menit a sampai menit b, kegiatan kedua dari menit c sampai menit d.\n\n"
         "Bila ada waktu yang dipakai keduanya, cetak `bentrok X menit` dengan X lama waktu yang bertabrakan. Bila tidak, cetak `aman`. "
         "Kegiatan yang mulai tepat saat kegiatan lain selesai **tidak** bentrok.\n\n"
         "Contoh: kegiatan 480–600 dan 540–660 bentrok 60 menit. Kegiatan 480–600 dan 600–700 aman.",
         "Baris pertama: a dan b. Baris kedua: c dan d.", "`bentrok X menit` atau `aman`.", "0 ≤ a < b ≤ 1440; 0 ≤ c < d ≤ 1440"),
     en=("Clashing Schedule", "Two activities are scheduled on the same day. Times are given as **minutes since midnight** (480 means 08:00).\n\n"
         "The first activity runs from minute a to minute b, the second from minute c to minute d.\n\n"
         "If some time is used by both, print `bentrok X menit` (clash of X minutes), where X is the length of the overlap. Otherwise print `aman` (safe). "
         "An activity that starts exactly when the other ends does **not** clash.\n\n"
         "Example: activities 480–600 and 540–660 clash for 60 minutes. Activities 480–600 and 600–700 are aman.",
         "First line: a and b. Second line: c and d.", "`bentrok X menit` or `aman`.", "0 ≤ a < b ≤ 1440; 0 ≤ c < d ≤ 1440"),
     ar=("تعارض المواعيد", "نشاطان مقرران في اليوم نفسه. تُعطى الأوقات **بالدقائق منذ منتصف الليل** (480 تعني الساعة 08:00).\n\n"
         "النشاط الأول يمتد من الدقيقة a إلى الدقيقة b، والثاني من الدقيقة c إلى الدقيقة d.\n\n"
         "إذا وُجد وقت يشغله النشاطان معًا فاطبع `bentrok X menit` (تعارض X دقيقة) حيث X مدة التداخل، وإلا فاطبع `aman` (آمن). "
         "النشاط الذي يبدأ لحظة انتهاء الآخر **لا** يتعارض معه.\n\n"
         "مثال: النشاطان 480–600 و 540–660 يتعارضان 60 دقيقة. النشاطان 480–600 و 600–700 آمنان.",
         "السطر الأول: a و b. السطر الثاني: c و d.", "`bentrok X menit` أو `aman`.", "0 ≤ a < b ≤ 1440؛ 0 ≤ c < d ≤ 1440"))

soal("batu-gunting-kertas", "sedang", "trimmed",
     BENIH + "L = ['batu', 'gunting', 'kertas']\na = (i + s) % 3\nb = (a + (i // 3 + s // 3) % 3) % 3\nprint(L[a], L[b])\n",
     cpp(r'''    string a, b;
    cin >> a >> b;
    if (a == b) {
        cout << "seri" << endl;
    } else if ((a == "batu" && b == "gunting") || (a == "gunting" && b == "kertas") || (a == "kertas" && b == "batu")) {
        cout << "pemain 1" << endl;
    } else {
        cout << "pemain 2" << endl;
    }''', "string"),
     id=("Batu Gunting Kertas", "Dua pemain bermain batu-gunting-kertas. Aturannya: `batu` mengalahkan `gunting`, `gunting` mengalahkan `kertas`, dan `kertas` mengalahkan `batu`. Pilihan yang sama berarti seri.\n\n"
         "Baca pilihan pemain 1 dan pemain 2, lalu cetak pemenangnya: `pemain 1`, `pemain 2`, atau `seri`.\n\nContoh: `kertas batu` dimenangkan pemain 1; `gunting batu` dimenangkan pemain 2.\n\nDua `string` bisa dibandingkan dengan `==`.",
         "Satu baris berisi dua kata: pilihan pemain 1 dan pilihan pemain 2.", "`pemain 1`, `pemain 2`, atau `seri`.", "Tiap kata adalah `batu`, `gunting`, atau `kertas`."),
     en=("Rock Paper Scissors", "Two players play rock-paper-scissors, with the Indonesian words `batu` (rock), `gunting` (scissors) and `kertas` (paper). `batu` beats `gunting`, `gunting` beats `kertas`, and `kertas` beats `batu`. Equal choices are a draw.\n\n"
         "Read the choices of player 1 and player 2, then print the winner: `pemain 1`, `pemain 2`, or `seri` (draw).\n\nExample: `kertas batu` is won by pemain 1; `gunting batu` is won by pemain 2.\n\nTwo `string` values can be compared with `==`.",
         "One line with two words: the choice of player 1 and the choice of player 2.", "`pemain 1`, `pemain 2` or `seri`.", "Each word is `batu`, `gunting` or `kertas`."),
     ar=("حجر ورقة مقص", "يلعب لاعبان حجر-ورقة-مقص بالكلمات الإندونيسية `batu` (حجر) و `gunting` (مقص) و `kertas` (ورقة). `batu` يغلب `gunting`، و `gunting` يغلب `kertas`، و `kertas` تغلب `batu`. الاختياران المتساويان تعادل.\n\n"
         "اقرأ اختيار اللاعب 1 واختيار اللاعب 2 ثم اطبع الفائز: `pemain 1` أو `pemain 2` أو `seri` (تعادل).\n\nمثال: `kertas batu` يفوز بها pemain 1، و `gunting batu` يفوز بها pemain 2.\n\nيمكن مقارنة قيمتين من النوع `string` بالعامل `==`.",
         "سطر واحد فيه كلمتان: اختيار اللاعب 1 ثم اختيار اللاعب 2.", "`pemain 1` أو `pemain 2` أو `seri`.", "كل كلمة هي `batu` أو `gunting` أو `kertas`."))

soal("ongkos-kirim", "sedang", "trimmed",
     "g, z = r.randint(1, 30000), r.randint(1, 3)\ng = {2: r.randint(1, 30) * 1000, 3: 10000, 4: 10001, 5: r.randint(1, 999), 6: r.randint(10001, 30000)}.get(i, g)\nprint(g, z)\n",
     cpp(r'''    int gram, zona;
    cin >> gram >> zona;
    int kg = gram / 1000;
    if (gram % 1000 != 0) {
        kg = kg + 1;
    }
    int tarif;
    if (zona == 1) {
        tarif = 8000;
    } else if (zona == 2) {
        tarif = 12000;
    } else {
        tarif = 20000;
    }
    int ongkos = kg * tarif;
    if (kg > 10) {
        ongkos = ongkos - ongkos / 10;
    }
    cout << ongkos << endl;'''),
     id=("Ongkos Kirim", "Sebuah jasa pengiriman menghitung ongkos kirim dengan aturan berikut.\n\n"
         "1. Berat paket **dibulatkan ke atas** ke kilogram penuh: 2300 gram dihitung 3 kg, 2000 gram tetap 2 kg.\n"
         "2. Tarif per kilogram bergantung pada zona tujuan: zona 1 = 8000, zona 2 = 12000, zona 3 = 20000 rupiah.\n"
         "3. Paket yang beratnya (setelah dibulatkan) **lebih dari 10 kg** mendapat potongan 10 persen dari seluruh ongkos.\n\n"
         "Baca berat paket dalam gram dan zona tujuannya, lalu cetak ongkos kirimnya.\n\n"
         "Contoh: `2300 2` → 3 kg × 12000 = 36000. `10500 1` → 11 kg × 8000 = 88000, dipotong 10 persen menjadi 79200.",
         "Satu baris berisi berat (gram) dan zona.", "Satu bilangan bulat: ongkos kirim dalam rupiah.", "1 ≤ berat ≤ 30000; zona 1, 2, atau 3"),
     en=("Shipping Cost", "A courier computes its shipping cost by these rules.\n\n"
         "1. The weight of a parcel is **rounded up** to whole kilograms: 2300 grams count as 3 kg, 2000 grams stay 2 kg.\n"
         "2. The rate per kilogram depends on the destination zone: zone 1 = 8000, zone 2 = 12000, zone 3 = 20000 rupiah.\n"
         "3. A parcel that weighs (after rounding) **more than 10 kg** gets 10 percent off the whole cost.\n\n"
         "Read the weight in grams and the destination zone, then print the shipping cost.\n\n"
         "Examples: `2300 2` → 3 kg × 12000 = 36000. `10500 1` → 11 kg × 8000 = 88000, less 10 percent is 79200.",
         "One line with the weight (grams) and the zone.", "One integer: the shipping cost in rupiah.", "1 ≤ weight ≤ 30000; the zone is 1, 2 or 3"),
     ar=("أجرة الشحن", "تحسب شركة شحن الأجرة وفق القواعد الآتية.\n\n"
         "1. يُقرَّب وزن الطرد **إلى الأعلى** إلى كيلوغرامات كاملة: 2300 غرام تُحسب 3 كغ، و 2000 غرام تبقى 2 كغ.\n"
         "2. سعر الكيلوغرام يتبع منطقة الوجهة: المنطقة 1 = 8000، المنطقة 2 = 12000، المنطقة 3 = 20000 روبية.\n"
         "3. الطرد الذي وزنه (بعد التقريب) **أكثر من 10 كغ** يُخصم له 10 بالمئة من الأجرة كلها.\n\n"
         "اقرأ وزن الطرد بالغرام ومنطقة الوجهة ثم اطبع أجرة الشحن.\n\n"
         "أمثلة: `2300 2` ← 3 كغ × 12000 = 36000. و `10500 1` ← 11 كغ × 8000 = 88000، وبعد خصم 10 بالمئة تصير 79200.",
         "سطر واحد فيه الوزن (بالغرام) والمنطقة.", "عدد صحيح واحد: أجرة الشحن بالروبية.", "1 ≤ الوزن ≤ 30000؛ المنطقة 1 أو 2 أو 3"))

soal("tarif-parkir", "sedang", "trimmed",
     "j, l = 1 + i % 2, r.randint(1, 24)\nl = {2: 1, 3: 1, 4: 7, 5: 9, 6: 8, 7: 10, 8: 24, 9: 24}.get(i, l)\nprint(j, l)\n",
     cpp(r'''    int jenis, lama;
    cin >> jenis >> lama;
    int pertama, berikutnya, maksimum;
    if (jenis == 1) {
        pertama = 2000;
        berikutnya = 1000;
        maksimum = 8000;
    } else {
        pertama = 5000;
        berikutnya = 3000;
        maksimum = 30000;
    }
    int biaya = pertama + (lama - 1) * berikutnya;
    if (biaya > maksimum) {
        biaya = maksimum;
    }
    cout << biaya << endl;'''),
     id=("Tarif Parkir", "Sebuah gedung parkir memasang tarif berikut.\n\n"
         "- 1 = motor: jam pertama 2000, tiap jam berikutnya 1000, paling banyak 8000 sehari\n- 2 = mobil: jam pertama 5000, tiap jam berikutnya 3000, paling banyak 30000 sehari\n\n"
         "Biaya tidak pernah melebihi batas \"paling banyak sehari\".\n\n"
         "Baca jenis kendaraan (1 atau 2) dan lama parkir dalam jam, lalu cetak biayanya.\n\n"
         "Contoh: mobil 4 jam → 5000 + 3 × 3000 = 14000. Motor 10 jam → 2000 + 9 × 1000 = 11000, tetapi yang dibayar hanya 8000.",
         "Satu baris berisi jenis kendaraan dan lama parkir (jam).", "Satu bilangan bulat: biaya parkir dalam rupiah.", "jenis 1 atau 2; 1 ≤ lama ≤ 24"),
     en=("Parking Fee", "A car park charges as follows.\n\n"
         "- 1 = motorcycle: first hour 2000, each further hour 1000, at most 8000 per day\n- 2 = car: first hour 5000, each further hour 3000, at most 30000 per day\n\n"
         "The fee never exceeds the \"at most per day\" limit.\n\n"
         "Read the kind of vehicle (1 or 2) and the parking time in hours, then print the fee.\n\n"
         "Examples: a car for 4 hours → 5000 + 3 × 3000 = 14000. A motorcycle for 10 hours → 2000 + 9 × 1000 = 11000, but only 8000 is paid.",
         "One line with the kind of vehicle and the parking time (hours).", "One integer: the parking fee in rupiah.", "kind 1 or 2; 1 ≤ time ≤ 24"),
     ar=("رسوم الوقوف", "يتقاضى موقف سيارات الرسوم الآتية.\n\n"
         "- 1 = دراجة نارية: الساعة الأولى 2000، كل ساعة تالية 1000، وبحد أقصى 8000 في اليوم\n- 2 = سيارة: الساعة الأولى 5000، كل ساعة تالية 3000، وبحد أقصى 30000 في اليوم\n\n"
         "لا تتجاوز الرسوم أبدًا «الحد الأقصى في اليوم».\n\n"
         "اقرأ نوع المركبة (1 أو 2) ومدة الوقوف بالساعات ثم اطبع الرسوم.\n\n"
         "أمثلة: سيارة 4 ساعات ← 5000 + 3 × 3000 = 14000. دراجة نارية 10 ساعات ← 2000 + 9 × 1000 = 11000، لكن المدفوع 8000 فقط.",
         "سطر واحد فيه نوع المركبة ومدة الوقوف (بالساعات).", "عدد صحيح واحد: رسوم الوقوف بالروبية.", "النوع 1 أو 2؛ 1 ≤ المدة ≤ 24"))

# ================================= SULIT (4) =================================

soal("terbilang", "sulit", "trimmed",
     "n = r.randint(1, 999)\nn = {2: 0, 3: 11, 4: 100, 5: 110, 6: r.randint(12, 19), 7: 999, 8: r.randint(2, 9) * 10, 9: r.randint(1, 9) * 100 + r.randint(1, 9), 10: 10, 11: r.randint(101, 199)}.get(i, n)\nprint(n)\n",
     cpp(r'''    int n;
    cin >> n;
    int ratusan = n / 100;
    int sisa = n % 100;
    int puluhan = sisa / 10;
    int satuan = sisa % 10;

    string kataRatusan, kataPuluhan, kataSatuan;
    switch (ratusan) {
        case 2: kataRatusan = "dua"; break;
        case 3: kataRatusan = "tiga"; break;
        case 4: kataRatusan = "empat"; break;
        case 5: kataRatusan = "lima"; break;
        case 6: kataRatusan = "enam"; break;
        case 7: kataRatusan = "tujuh"; break;
        case 8: kataRatusan = "delapan"; break;
        case 9: kataRatusan = "sembilan"; break;
    }
    switch (puluhan) {
        case 2: kataPuluhan = "dua"; break;
        case 3: kataPuluhan = "tiga"; break;
        case 4: kataPuluhan = "empat"; break;
        case 5: kataPuluhan = "lima"; break;
        case 6: kataPuluhan = "enam"; break;
        case 7: kataPuluhan = "tujuh"; break;
        case 8: kataPuluhan = "delapan"; break;
        case 9: kataPuluhan = "sembilan"; break;
    }
    switch (satuan) {
        case 1: kataSatuan = "satu"; break;
        case 2: kataSatuan = "dua"; break;
        case 3: kataSatuan = "tiga"; break;
        case 4: kataSatuan = "empat"; break;
        case 5: kataSatuan = "lima"; break;
        case 6: kataSatuan = "enam"; break;
        case 7: kataSatuan = "tujuh"; break;
        case 8: kataSatuan = "delapan"; break;
        case 9: kataSatuan = "sembilan"; break;
    }

    string depan = "";
    if (ratusan == 1) {
        depan = "seratus";
    } else if (ratusan > 1) {
        depan = kataRatusan + " ratus";
    }

    string belakang = "";
    if (sisa == 10) {
        belakang = "sepuluh";
    } else if (sisa == 11) {
        belakang = "sebelas";
    } else if (sisa > 11 && sisa < 20) {
        belakang = kataSatuan + " belas";
    } else if (sisa >= 20) {
        belakang = kataPuluhan + " puluh";
        if (satuan > 0) {
            belakang = belakang + " " + kataSatuan;
        }
    } else if (sisa > 0) {
        belakang = kataSatuan;
    }

    if (n == 0) {
        cout << "nol" << endl;
    } else if (depan != "" && belakang != "") {
        cout << depan << " " << belakang << endl;
    } else {
        cout << depan << belakang << endl;
    }''', "string"),
     id=("Terbilang", "Di kuitansi, jumlah uang ditulis juga dengan kata-kata. Baca sebuah bilangan dari 0 sampai 999, lalu cetak cara membacanya dalam bahasa Indonesia, dengan huruf kecil dan satu spasi di antara kata.\n\n"
         "Aturannya:\n\n- 0 dibaca `nol`; 1 sampai 9: `satu`, `dua`, `tiga`, `empat`, `lima`, `enam`, `tujuh`, `delapan`, `sembilan`;\n"
         "- 10 dibaca `sepuluh`, 11 dibaca `sebelas`, 12 sampai 19 memakai `belas`: `dua belas`, `tiga belas`, …;\n"
         "- puluhan memakai `puluh`: `dua puluh`, `tiga puluh lima`;\n- 100 sampai 199 diawali `seratus`; ratusan lain memakai `ratus`: `dua ratus`, `sembilan ratus`.\n\n"
         "Contoh: 7 → `tujuh`; 15 → `lima belas`; 40 → `empat puluh`; 111 → `seratus sebelas`; 205 → `dua ratus lima`; 999 → `sembilan ratus sembilan puluh sembilan`.\n\n"
         "Kerjakan tanpa perulangan dan tanpa array. Pecah bilangan menjadi ratusan, puluhan, dan satuan terlebih dahulu.",
         "Satu bilangan bulat n.", "Satu baris: n dalam kata-kata.", "0 ≤ n ≤ 999"),
     en=("Numbers in Words", "On a receipt the amount is also written in words. Read a number from 0 to 999, then print how it is read in Indonesian, in lowercase with one space between words.\n\n"
         "The rules:\n\n- 0 is `nol`; 1 to 9: `satu`, `dua`, `tiga`, `empat`, `lima`, `enam`, `tujuh`, `delapan`, `sembilan`;\n"
         "- 10 is `sepuluh`, 11 is `sebelas`, 12 to 19 use `belas`: `dua belas`, `tiga belas`, …;\n"
         "- tens use `puluh`: `dua puluh`, `tiga puluh lima`;\n- 100 to 199 start with `seratus`; other hundreds use `ratus`: `dua ratus`, `sembilan ratus`.\n\n"
         "Examples: 7 → `tujuh`; 15 → `lima belas`; 40 → `empat puluh`; 111 → `seratus sebelas`; 205 → `dua ratus lima`; 999 → `sembilan ratus sembilan puluh sembilan`.\n\n"
         "Do it without loops and without arrays. First split the number into hundreds, tens and units.",
         "One integer n.", "One line: n in words.", "0 ≤ n ≤ 999"),
     ar=("العدد بالكلمات", "في الإيصالات يُكتب المبلغ بالكلمات أيضًا. اقرأ عددًا من 0 إلى 999 ثم اطبع طريقة قراءته بالإندونيسية، بحروف صغيرة ومسافة واحدة بين الكلمات.\n\n"
         "القواعد:\n\n- 0 يُقرأ `nol`؛ ومن 1 إلى 9: `satu` و `dua` و `tiga` و `empat` و `lima` و `enam` و `tujuh` و `delapan` و `sembilan`؛\n"
         "- 10 يُقرأ `sepuluh`، و 11 يُقرأ `sebelas`، ومن 12 إلى 19 تُستعمل `belas`: `dua belas` و `tiga belas` …؛\n"
         "- العشرات تستعمل `puluh`: `dua puluh` و `tiga puluh lima`؛\n- من 100 إلى 199 تبدأ بـ `seratus`، وبقية المئات تستعمل `ratus`: `dua ratus` و `sembilan ratus`.\n\n"
         "أمثلة: 7 ← `tujuh`؛ 15 ← `lima belas`؛ 40 ← `empat puluh`؛ 111 ← `seratus sebelas`؛ 205 ← `dua ratus lima`؛ 999 ← `sembilan ratus sembilan puluh sembilan`.\n\n"
         "حُلَّها بلا حلقات وبلا مصفوفات. قسِّم العدد أولًا إلى مئات وعشرات وآحاد.",
         "عدد صحيح واحد n.", "سطر واحد: العدد n بالكلمات.", "0 ≤ n ≤ 999"))

soal("angka-romawi", "sulit", "trimmed",
     "n = r.randint(1, 3999)\nn = {2: 4, 3: 9, 4: 1994, 5: 3999, 6: 40, 7: 400, 8: r.randint(1, 3) * 1000, 9: 90, 10: 900, 11: 3888}.get(i, n)\nprint(n)\n",
     cpp(r'''    int n;
    cin >> n;
    string hasil = "";
    switch (n / 1000) {
        case 1: hasil = hasil + "M"; break;
        case 2: hasil = hasil + "MM"; break;
        case 3: hasil = hasil + "MMM"; break;
    }
    switch (n / 100 % 10) {
        case 1: hasil = hasil + "C"; break;
        case 2: hasil = hasil + "CC"; break;
        case 3: hasil = hasil + "CCC"; break;
        case 4: hasil = hasil + "CD"; break;
        case 5: hasil = hasil + "D"; break;
        case 6: hasil = hasil + "DC"; break;
        case 7: hasil = hasil + "DCC"; break;
        case 8: hasil = hasil + "DCCC"; break;
        case 9: hasil = hasil + "CM"; break;
    }
    switch (n / 10 % 10) {
        case 1: hasil = hasil + "X"; break;
        case 2: hasil = hasil + "XX"; break;
        case 3: hasil = hasil + "XXX"; break;
        case 4: hasil = hasil + "XL"; break;
        case 5: hasil = hasil + "L"; break;
        case 6: hasil = hasil + "LX"; break;
        case 7: hasil = hasil + "LXX"; break;
        case 8: hasil = hasil + "LXXX"; break;
        case 9: hasil = hasil + "XC"; break;
    }
    switch (n % 10) {
        case 1: hasil = hasil + "I"; break;
        case 2: hasil = hasil + "II"; break;
        case 3: hasil = hasil + "III"; break;
        case 4: hasil = hasil + "IV"; break;
        case 5: hasil = hasil + "V"; break;
        case 6: hasil = hasil + "VI"; break;
        case 7: hasil = hasil + "VII"; break;
        case 8: hasil = hasil + "VIII"; break;
        case 9: hasil = hasil + "IX"; break;
    }
    cout << hasil << endl;''', "string"),
     id=("Angka Romawi", "Ubah sebuah bilangan menjadi angka Romawi.\n\nLambangnya: `I` = 1, `V` = 5, `X` = 10, `L` = 50, `C` = 100, `D` = 500, `M` = 1000.\n\n"
         "Tiap digit bilangan ditulis sendiri-sendiri, dari ribuan ke satuan, lalu disambung. Pola untuk satuan:\n\n"
         "1 = `I`, 2 = `II`, 3 = `III`, 4 = `IV`, 5 = `V`, 6 = `VI`, 7 = `VII`, 8 = `VIII`, 9 = `IX`\n\n"
         "Puluhan memakai pola yang sama dengan `X`, `L`, `C` (misalnya 40 = `XL`, 90 = `XC`), ratusan dengan `C`, `D`, `M` (400 = `CD`, 900 = `CM`), dan ribuan cukup `M`, `MM`, atau `MMM`.\n\n"
         "Contoh: 4 → `IV`; 49 → `XLIX`; 1994 → `MCMXCIV`; 2026 → `MMXXVI`.\n\nKerjakan tanpa perulangan dan tanpa array.",
         "Satu bilangan bulat n.", "n dalam angka Romawi.", "1 ≤ n ≤ 3999"),
     en=("Roman Numerals", "Turn a number into Roman numerals.\n\nThe symbols: `I` = 1, `V` = 5, `X` = 10, `L` = 50, `C` = 100, `D` = 500, `M` = 1000.\n\n"
         "Each digit of the number is written on its own, from thousands to units, and the parts are joined. The pattern for units:\n\n"
         "1 = `I`, 2 = `II`, 3 = `III`, 4 = `IV`, 5 = `V`, 6 = `VI`, 7 = `VII`, 8 = `VIII`, 9 = `IX`\n\n"
         "Tens follow the same pattern with `X`, `L`, `C` (40 = `XL`, 90 = `XC`), hundreds with `C`, `D`, `M` (400 = `CD`, 900 = `CM`), and thousands are simply `M`, `MM` or `MMM`.\n\n"
         "Examples: 4 → `IV`; 49 → `XLIX`; 1994 → `MCMXCIV`; 2026 → `MMXXVI`.\n\nDo it without loops and without arrays.",
         "One integer n.", "n in Roman numerals.", "1 ≤ n ≤ 3999"),
     ar=("الأرقام الرومانية", "حوِّل عددًا إلى الأرقام الرومانية.\n\nالرموز: `I` = 1، `V` = 5، `X` = 10، `L` = 50، `C` = 100، `D` = 500، `M` = 1000.\n\n"
         "يُكتب كل رقم من أرقام العدد على حدة، من الآلاف إلى الآحاد، ثم توصل الأجزاء. نمط الآحاد:\n\n"
         "1 = `I`, 2 = `II`, 3 = `III`, 4 = `IV`, 5 = `V`, 6 = `VI`, 7 = `VII`, 8 = `VIII`, 9 = `IX`\n\n"
         "العشرات تتبع النمط نفسه بالرموز `X` و `L` و `C` (40 = `XL` و 90 = `XC`)، والمئات بالرموز `C` و `D` و `M` (400 = `CD` و 900 = `CM`)، والآلاف هي `M` أو `MM` أو `MMM` فقط.\n\n"
         "أمثلة: 4 ← `IV`؛ 49 ← `XLIX`؛ 1994 ← `MCMXCIV`؛ 2026 ← `MMXXVI`.\n\nحُلَّها بلا حلقات وبلا مصفوفات.",
         "عدد صحيح واحد n.", "العدد n بالأرقام الرومانية.", "1 ≤ n ≤ 3999"))

soal("selisih-hari", "sulit", "trimmed",
     "import datetime\nt = r.choice([2023, 2024, 2025, 2026, 2028, 1900, 2000, 2100])\nif i in (2, 3):\n    t = 2024\nif i in (4, 5):\n    t = 2100\n"
     "n = 366 if (t % 400 == 0 or (t % 4 == 0 and t % 100 != 0)) else 365\na = r.randint(1, n)\nb = r.randint(a, n)\nif i == 2:\n    a, b = r.randint(1, 59), r.randint(61, n)\nif i == 4:\n    a, b = r.randint(1, 59), r.randint(60, n)\nif i == 6:\n    b = a\nif i == 7:\n    a, b = 1, n\n"
     "awal = datetime.date(t, 1, 1)\nx, y = awal + datetime.timedelta(days=a - 1), awal + datetime.timedelta(days=b - 1)\nprint(x.day, x.month, y.day, y.month, t)\n",
     cpp(r'''    int h1, b1, h2, b2, tahun;
    cin >> h1 >> b1 >> h2 >> b2 >> tahun;
    int kabisat = 0;
    if (tahun % 400 == 0 || (tahun % 4 == 0 && tahun % 100 != 0)) {
        kabisat = 1;
    }

    int ke1 = h1;
    switch (b1) {
        case 2: ke1 = ke1 + 31; break;
        case 3: ke1 = ke1 + 59; break;
        case 4: ke1 = ke1 + 90; break;
        case 5: ke1 = ke1 + 120; break;
        case 6: ke1 = ke1 + 151; break;
        case 7: ke1 = ke1 + 181; break;
        case 8: ke1 = ke1 + 212; break;
        case 9: ke1 = ke1 + 243; break;
        case 10: ke1 = ke1 + 273; break;
        case 11: ke1 = ke1 + 304; break;
        case 12: ke1 = ke1 + 334; break;
    }
    if (b1 > 2) {
        ke1 = ke1 + kabisat;
    }

    int ke2 = h2;
    switch (b2) {
        case 2: ke2 = ke2 + 31; break;
        case 3: ke2 = ke2 + 59; break;
        case 4: ke2 = ke2 + 90; break;
        case 5: ke2 = ke2 + 120; break;
        case 6: ke2 = ke2 + 151; break;
        case 7: ke2 = ke2 + 181; break;
        case 8: ke2 = ke2 + 212; break;
        case 9: ke2 = ke2 + 243; break;
        case 10: ke2 = ke2 + 273; break;
        case 11: ke2 = ke2 + 304; break;
        case 12: ke2 = ke2 + 334; break;
    }
    if (b2 > 2) {
        ke2 = ke2 + kabisat;
    }

    cout << ke2 - ke1 << endl;'''),
     id=("Selisih Hari", "Diberikan dua tanggal **pada tahun yang sama**; tanggal kedua tidak lebih awal daripada tanggal pertama. Hitung berapa hari jarak keduanya.\n\n"
         "Banyak hari tiap bulan, dari Januari sampai Desember: 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31. Pada tahun kabisat Februari berisi 29 hari. "
         "Tahun kabisat: habis dibagi 400, atau habis dibagi 4 tetapi tidak habis dibagi 100.\n\n"
         "Contoh:\n\n- 1 Januari sampai 1 Februari 2025: 31 hari;\n- 17 Agustus sampai 10 November 2026: 85 hari;\n- 28 Februari sampai 1 Maret 2024: 2 hari (2024 kabisat);\n- 28 Februari sampai 1 Maret 2025: 1 hari.\n\n"
         "Kerjakan tanpa perulangan. Petunjuk: hitung dulu tiap tanggal itu hari ke berapa dalam tahunnya.",
         "Satu baris berisi lima bilangan bulat: tanggal dan bulan pertama, tanggal dan bulan kedua, lalu tahun.", "Satu bilangan bulat: banyak hari dari tanggal pertama ke tanggal kedua.", "Kedua tanggal sah; 1900 ≤ tahun ≤ 2100"),
     en=("Days Between", "Two dates **in the same year** are given; the second is not earlier than the first. Compute how many days lie between them.\n\n"
         "Days per month, January to December: 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31. In a leap year February has 29 days. "
         "Leap year: divisible by 400, or divisible by 4 but not by 100.\n\n"
         "Examples:\n\n- 1 January to 1 February 2025: 31 days;\n- 17 August to 10 November 2026: 85 days;\n- 28 February to 1 March 2024: 2 days (2024 is a leap year);\n- 28 February to 1 March 2025: 1 day.\n\n"
         "Do it without loops. Hint: first work out which day of its year each date is.",
         "One line with five integers: day and month of the first date, day and month of the second date, then the year.", "One integer: the number of days from the first date to the second.", "Both dates are valid; 1900 ≤ year ≤ 2100"),
     ar=("الفرق بالأيام", "يُعطى تاريخان **في السنة نفسها**، والثاني ليس قبل الأول. احسب عدد الأيام بينهما.\n\n"
         "عدد أيام الشهور من يناير إلى ديسمبر: 31، 28، 31، 30، 31، 30، 31، 31، 30، 31، 30، 31. في السنة الكبيسة يكون فبراير 29 يومًا. "
         "السنة الكبيسة: تقبل القسمة على 400، أو تقبل القسمة على 4 ولا تقبلها على 100.\n\n"
         "أمثلة:\n\n- من 1 يناير إلى 1 فبراير 2025: 31 يومًا؛\n- من 17 أغسطس إلى 10 نوفمبر 2026: 85 يومًا؛\n- من 28 فبراير إلى 1 مارس 2024: يومان (2024 كبيسة)؛\n- من 28 فبراير إلى 1 مارس 2025: يوم واحد.\n\n"
         "حُلَّها بلا حلقات. تلميح: احسب أولًا ترتيب كل تاريخ بين أيام سنته.",
         "سطر واحد فيه خمسة أعداد صحيحة: يوم وشهر التاريخ الأول، ثم يوم وشهر التاريخ الثاني، ثم السنة.", "عدد صحيح واحد: عدد الأيام من التاريخ الأول إلى الثاني.", "التاريخان صحيحان؛ 1900 ≤ السنة ≤ 2100"))

soal("tiga-dadu", "sulit", "trimmed",
     "k = i % 4\nif k == 0:\n    a = r.randint(1, 6)\n    v = [a, a, a]\nelif k == 1:\n    a = r.randint(1, 4)\n    v = [a, a + 1, a + 2]\nelif k == 2:\n    a, b = r.sample(range(1, 7), 2)\n    v = [a, a, b]\n"
     "else:\n    while True:\n        v = sorted(r.sample(range(1, 7), 3))\n        if v[2] - v[0] != 2:\n            break\nr.shuffle(v)\nprint(*v)\n",
     cpp(r'''    int a, b, c;
    cin >> a >> b >> c;
    int besar = a, kecil = a;
    if (b > besar) besar = b;
    if (c > besar) besar = c;
    if (b < kecil) kecil = b;
    if (c < kecil) kecil = c;

    if (a == b && b == c) {
        cout << "kembar tiga 100" << endl;
    } else if (a == b || b == c || a == c) {
        int pasangan;
        if (a == b) {
            pasangan = a;
        } else {
            pasangan = c;
        }
        cout << "sepasang " << 20 + pasangan << endl;
    } else if (besar - kecil == 2) {
        cout << "berurutan 60" << endl;
    } else {
        cout << "biasa " << besar << endl;
    }'''),
     id=("Tiga Dadu", "Dalam sebuah permainan, tiga dadu dilempar sekaligus. Hasilnya dinilai dengan aturan berikut, diperiksa dari atas ke bawah:\n\n"
         "- `kembar tiga` — ketiga dadu sama; poin: 100\n- `sepasang` — tepat dua dadu sama; poin: 20 + mata dadu yang kembar\n- `berurutan` — tiga mata dadu berurutan, dalam urutan lemparan apa pun (misalnya 4, 2, 3); poin: 60\n- `biasa` — selain itu; poin: mata dadu terbesar\n\n"
         "Baca ketiga mata dadu, lalu cetak nama hasilnya dan poinnya, dipisah satu spasi.\n\n"
         "Contoh: `5 5 5` → `kembar tiga 100`; `4 2 3` → `berurutan 60`; `6 2 6` → `sepasang 26`; `1 6 3` → `biasa 6`.\n\n"
         "Kerjakan tanpa perulangan dan tanpa array.",
         "Satu baris berisi tiga bilangan bulat: mata ketiga dadu.", "Nama hasil, satu spasi, lalu poinnya.", "Tiap mata dadu antara 1 dan 6."),
     en=("Three Dice", "In a game three dice are thrown at once. The throw is scored by these rules, checked from top to bottom:\n\n"
         "- `kembar tiga` — all three dice equal; points: 100\n- `sepasang` — exactly two dice equal; points: 20 + the value of the pair\n- `berurutan` — three consecutive values, thrown in any order (for example 4, 2, 3); points: 60\n- `biasa` — anything else; points: the largest value\n\n"
         "Read the three values, then print the name of the result and its points, separated by one space.\n\n"
         "Examples: `5 5 5` → `kembar tiga 100`; `4 2 3` → `berurutan 60`; `6 2 6` → `sepasang 26`; `1 6 3` → `biasa 6`.\n\n"
         "Do it without loops and without arrays.",
         "One line with three integers: the values of the three dice.", "The name of the result, one space, then the points.", "Each value is between 1 and 6."),
     ar=("ثلاثة أحجار نرد", "في لعبة تُرمى ثلاثة أحجار نرد معًا. تُقيَّم الرمية بالقواعد الآتية، وتُفحص من الأعلى إلى الأسفل:\n\n"
         "- `kembar tiga` — الأحجار الثلاثة متساوية؛ النقاط: 100\n- `sepasang` — حجران فقط متساويان؛ النقاط: 20 + قيمة الحجرين المتساويين\n- `berurutan` — ثلاث قيم متتالية بأي ترتيب للرمي (مثل 4، 2، 3)؛ النقاط: 60\n- `biasa` — غير ذلك؛ النقاط: أكبر قيمة\n\n"
         "اقرأ القيم الثلاث ثم اطبع اسم النتيجة ونقاطها تفصل بينهما مسافة واحدة.\n\n"
         "أمثلة: `5 5 5` ← `kembar tiga 100`؛ `4 2 3` ← `berurutan 60`؛ `6 2 6` ← `sepasang 26`؛ `1 6 3` ← `biasa 6`.\n\n"
         "حُلَّها بلا حلقات وبلا مصفوفات.",
         "سطر واحد فيه ثلاثة أعداد صحيحة: قيم الأحجار الثلاثة.", "اسم النتيجة ثم مسافة ثم النقاط.", "كل قيمة بين 1 و 6."))

tulis(__file__, "DDP101", 4, "cpp", {"mudah_sekali": 8, "mudah": 8, "mudah_sedang": 6, "sedang": 6, "sulit": 4})
