#!/usr/bin/env python3
"""Menyusun docs/soal/ddp101-pertemuan-7.json.

DDP101 pertemuan 7 — Fungsi Lanjutan, Rekursi, dan Debugging. Konsep yang
diizinkan di produksi: konsep pertemuan 1–6 ditambah REKURSI dan
OVERLOAD_FUNGSI (fungsi bernama sama, parameter bawaan). Tanpa array, tanpa
variabel referensi (`REFERENSI` tidak dicentang di pertemuan 6 produksi).
32 soal: 8 / 8 / 6 / 6 / 4.

Silabus pertemuan 7: rekursi, scope dan lifetime variabel, function templates,
lambda, fungsi sebagai parameter, teknik debugging; contoh: faktorial, Menara
Hanoi. Function overloading dan default arguments dipindah ke sini dari
pertemuan 6 (peta konsep baru mengizinkannya di pertemuan 7).

Tidak dibuatkan soal, karena konsepnya tidak diizinkan di DDP101 atau tidak
bisa dinilai dari keluaran: function templates (TEMPLATE_FUNGSI), lambda
(MODERN_CPP), fungsi sebagai parameter (POINTER), debugger/GDB.

28 soal rekursi mensyaratkan REKURSI di kode mahasiswa (keputusan pemilik,
2026-11-23): keluaran benar tanpa rekursi mendapat CONCEPT_NOT_MET. Empat soal
overloading / parameter bawaan tidak mensyaratkan apa pun.

    python3 docs/soal/buat-ddp101-pertemuan-7.py
"""
from bangun import program, soal, tulis

R = ("REKURSI",)


def dua(a, m, b):
    """Dua tes contoh (INDEX 0 dan 1) diambil dari dua belahan rentang yang berbeda."""
    return f"(r.randint({a}, {m}) if i % 2 == 0 else r.randint({m + 1}, {b}))"


# ============================== MUDAH SEKALI (8) ==============================

soal("faktorial", "mudah_sekali", "trimmed", "print({2: 0, 3: 12, 4: 1}.get(i, " + dua(2, 6, 11) + "))\n",
     program(r'''
int faktorial(int n) {
    if (n <= 1) {
        return 1;
    }
    return n * faktorial(n - 1);
}''', r'''    int n;
    cin >> n;
    cout << faktorial(n) << endl;'''), wajib=R,
     id=("Faktorial", "Faktorial n, ditulis n!, adalah hasil kali semua bilangan dari 1 sampai n. Jadi 5! = 5 × 4 × 3 × 2 × 1 = 120. Menurut kesepakatan, 0! = 1.\n\n"
         "Perhatikan bahwa 5! = 5 × 4!. Faktorial bisa dihitung oleh fungsi yang **memanggil dirinya sendiri** untuk bilangan yang lebih kecil, sampai tiba di kasus yang jawabannya langsung diketahui.\n\n"
         "Tulis fungsi rekursif `int faktorial(int n)`, lalu cetak n! untuk n yang dibaca.\n\n**Soal ini mensyaratkan rekursi**: keluaran yang benar tanpa fungsi rekursif tidak mendapat nilai penuh.",
         "Satu bilangan bulat n.", "Satu bilangan bulat: n!", "0 ≤ n ≤ 12"),
     en=("Factorial", "The factorial of n, written n!, is the product of all numbers from 1 to n. So 5! = 5 × 4 × 3 × 2 × 1 = 120. By convention 0! = 1.\n\n"
         "Notice that 5! = 5 × 4!. A factorial can be computed by a function that **calls itself** for a smaller number, until it reaches a case whose answer is known at once.\n\n"
         "Write a recursive function `int faktorial(int n)`, then print n! for the n that is read.\n\n**This problem requires recursion**: correct output without a recursive function does not get full marks.",
         "One integer n.", "One integer: n!", "0 ≤ n ≤ 12"),
     ar=("المضروب", "مضروب n، ويُكتب n!، هو حاصل ضرب كل الأعداد من 1 إلى n. فمثلًا 5! = 5 × 4 × 3 × 2 × 1 = 120. واصطلاحًا 0! = 1.\n\n"
         "لاحظ أن 5! = 5 × 4!. يمكن حساب المضروب بدالة **تستدعي نفسها** لعدد أصغر، حتى تصل إلى حالة جوابها معروف مباشرة.\n\n"
         "اكتب دالة عودية `int faktorial(int n)` ثم اطبع n! للعدد n المقروء.\n\n**هذه المسألة تشترط العودية**: المخرج الصحيح بلا دالة عودية لا ينال الدرجة الكاملة.",
         "عدد صحيح واحد n.", "عدد صحيح واحد: n!", "0 ≤ n ≤ 12"))

soal("suku-ke-n", "mudah_sekali", "trimmed", "print({2: 1, 3: 25}.get(i, " + dua(2, 12, 24) + "))\n",
     program(r'''
int suku(int n) {
    if (n == 1) {
        return 3;
    }
    return 2 * suku(n - 1) + 1;
}''', r'''    int n;
    cin >> n;
    cout << suku(n) << endl;'''), wajib=R,
     id=("Suku ke-n", "Sebuah barisan bilangan dimulai dengan 3. Setiap suku berikutnya adalah **dua kali suku sebelumnya, ditambah satu**: 3, 7, 15, 31, …\n\n"
         "Tulis fungsi rekursif `int suku(int n)` yang mengembalikan suku ke-n, lalu cetak hasilnya. Aturan barisan itu sendiri sudah berbentuk rekursi: suku(n) = 2 × suku(n − 1) + 1, dengan suku(1) = 3.\n\nSuku ke-4 adalah 31.\n\n**Soal ini mensyaratkan rekursi.**",
         "Satu bilangan bulat n.", "Satu bilangan bulat: suku ke-n.", "1 ≤ n ≤ 25"),
     en=("The n-th Term", "A sequence of numbers starts with 3. Every next term is **twice the previous term, plus one**: 3, 7, 15, 31, …\n\n"
         "Write a recursive function `int suku(int n)` that returns the n-th term, then print it. The rule of the sequence is already recursive: suku(n) = 2 × suku(n − 1) + 1, with suku(1) = 3.\n\nThe 4th term is 31.\n\n**This problem requires recursion.**",
         "One integer n.", "One integer: the n-th term.", "1 ≤ n ≤ 25"),
     ar=("الحد رقم n", "متتالية أعداد تبدأ بالعدد 3. كل حد تالٍ هو **ضعف الحد السابق زائد واحد**: 3، 7، 15، 31، …\n\n"
         "اكتب دالة عودية `int suku(int n)` تُرجع الحد رقم n ثم اطبعه. قاعدة المتتالية نفسها عودية: suku(n) = 2 × suku(n − 1) + 1، مع suku(1) = 3.\n\nالحد الرابع هو 31.\n\n**هذه المسألة تشترط العودية.**",
         "عدد صحيح واحد n.", "عدد صحيح واحد: الحد رقم n.", "1 ≤ n ≤ 25"))

soal("kali-dengan-tambah", "mudah_sekali", "trimmed", "a, b = r.randint(0, 1000), r.randint(1, 1000)\nif i == 2:\n    b = 0\nif i == 3:\n    a, b = 1000, 1000\nprint(a, b)\n",
     program(r'''
int kali(int a, int b) {
    if (b == 0) {
        return 0;
    }
    return a + kali(a, b - 1);
}''', r'''    int a, b;
    cin >> a >> b;
    cout << kali(a, b) << endl;'''), wajib=R,
     id=("Kali dengan Tambah", "Perkalian adalah penjumlahan berulang: 4 × 3 = 4 + 4 + 4. Dengan kata lain, a × b = a + a × (b − 1), dan a × 0 = 0.\n\n"
         "Tulis fungsi rekursif `int kali(int a, int b)` yang menghitung a × b **tanpa operator `*`**, lalu cetak hasilnya.\n\nUntuk `4 3` jawabannya 12.\n\n**Soal ini mensyaratkan rekursi.**",
         "Satu baris berisi a dan b.", "Satu bilangan bulat: a × b.", "0 ≤ a, b ≤ 1000"),
     en=("Multiply by Adding", "Multiplication is repeated addition: 4 × 3 = 4 + 4 + 4. In other words a × b = a + a × (b − 1), and a × 0 = 0.\n\n"
         "Write a recursive function `int kali(int a, int b)` that computes a × b **without the `*` operator**, then print the result.\n\nFor `4 3` the answer is 12.\n\n**This problem requires recursion.**",
         "One line with a and b.", "One integer: a × b.", "0 ≤ a, b ≤ 1000"),
     ar=("الضرب بالجمع", "الضرب جمع متكرر: 4 × 3 = 4 + 4 + 4. أي أن a × b = a + a × (b − 1)، و a × 0 = 0.\n\n"
         "اكتب دالة عودية `int kali(int a, int b)` تحسب a × b **دون العامل `*`** ثم اطبع الناتج.\n\nعند `4 3` الجواب 12.\n\n**هذه المسألة تشترط العودية.**",
         "سطر واحد فيه a و b.", "عدد صحيح واحد: a × b.", "0 ≤ a, b ≤ 1000"))

soal("banyak-digit", "mudah_sekali", "trimmed", "print({2: 0, 3: 1000000000, 4: 9, 5: 10}.get(i, r.randint(1, 10 ** " + dua(1, 4, 9) + ")))\n",
     program(r'''
int banyakDigit(int n) {
    if (n < 10) {
        return 1;
    }
    return 1 + banyakDigit(n / 10);
}''', r'''    int n;
    cin >> n;
    cout << banyakDigit(n) << endl;'''), wajib=R,
     id=("Banyak Digit", "Bilangan yang kurang dari 10 punya satu digit. Bilangan lain punya satu digit lebih banyak daripada bilangan itu setelah digit terakhirnya dibuang (dibagi 10).\n\n"
         "Tulis fungsi rekursif `int banyakDigit(int n)` berdasarkan dua kalimat di atas, lalu cetak banyak digit n.\n\nUntuk 40726 jawabannya 5; untuk 0 jawabannya 1.\n\n**Soal ini mensyaratkan rekursi.**",
         "Satu bilangan bulat n.", "Satu bilangan bulat: banyak digit n.", "0 ≤ n ≤ 1000000000"),
     en=("Number of Digits", "A number below 10 has one digit. Any other number has one digit more than the same number with its last digit dropped (divided by 10).\n\n"
         "Write a recursive function `int banyakDigit(int n)` from those two sentences, then print how many digits n has.\n\nFor 40726 the answer is 5; for 0 it is 1.\n\n**This problem requires recursion.**",
         "One integer n.", "One integer: the number of digits of n.", "0 ≤ n ≤ 1000000000"),
     ar=("عدد الأرقام", "العدد الأصغر من 10 له رقم واحد. وأي عدد آخر يزيد برقم واحد على العدد نفسه بعد حذف رقمه الأخير (القسمة على 10).\n\n"
         "اكتب دالة عودية `int banyakDigit(int n)` من هاتين الجملتين ثم اطبع عدد أرقام n.\n\nللعدد 40726 الجواب 5، وللعدد 0 الجواب 1.\n\n**هذه المسألة تشترط العودية.**",
         "عدد صحيح واحد n.", "عدد صحيح واحد: عدد أرقام n.", "0 ≤ n ≤ 1000000000"))

soal("jumlah-kuadrat-deret", "mudah_sekali", "trimmed", "print({2: 1, 3: 1000}.get(i, " + dua(2, 500, 999) + "))\n",
     program(r'''
int jumlahKuadrat(int n) {
    if (n == 0) {
        return 0;
    }
    return n * n + jumlahKuadrat(n - 1);
}''', r'''    int n;
    cin >> n;
    cout << jumlahKuadrat(n) << endl;'''), wajib=R,
     id=("Jumlah Kuadrat 1 sampai n", "Hitung 1² + 2² + 3² + … + n².\n\nTulis fungsi rekursif `int jumlahKuadrat(int n)`: jumlah sampai n adalah n² ditambah jumlah sampai n − 1, dan jumlah sampai 0 adalah 0.\n\nUntuk n = 3 jawabannya 1 + 4 + 9 = 14.\n\n**Soal ini mensyaratkan rekursi.**",
         "Satu bilangan bulat n.", "Satu bilangan bulat: 1² + 2² + … + n².", "1 ≤ n ≤ 1000"),
     en=("Sum of Squares from 1 to n", "Compute 1² + 2² + 3² + … + n².\n\nWrite a recursive function `int jumlahKuadrat(int n)`: the sum up to n is n² plus the sum up to n − 1, and the sum up to 0 is 0.\n\nFor n = 3 the answer is 1 + 4 + 9 = 14.\n\n**This problem requires recursion.**",
         "One integer n.", "One integer: 1² + 2² + … + n².", "1 ≤ n ≤ 1000"),
     ar=("مجموع المربعات من 1 إلى n", "احسب 1² + 2² + 3² + … + n².\n\nاكتب دالة عودية `int jumlahKuadrat(int n)`: المجموع حتى n هو n² زائد المجموع حتى n − 1، والمجموع حتى 0 هو 0.\n\nعند n = 3 الجواب 1 + 4 + 9 = 14.\n\n**هذه المسألة تشترط العودية.**",
         "عدد صحيح واحد n.", "عدد صحيح واحد: 1² + 2² + … + n².", "1 ≤ n ≤ 1000"))

soal("naik-turun", "mudah_sekali", "trimmed", "print({2: 1, 3: 9}.get(i, " + dua(2, 5, 8) + "))\n",
     program(r'''
void naikTurun(int n) {
    if (n == 0) {
        return;
    }
    cout << n << " ";
    naikTurun(n - 1);
    cout << n << " ";
}''', r'''    int n;
    cin >> n;
    naikTurun(n);
    cout << endl;'''), wajib=R,
     id=("Turun lalu Naik", "Tulis fungsi rekursif `void naikTurun(int n)` yang mencetak n, lalu memanggil dirinya untuk n − 1, lalu **mencetak n sekali lagi**. Untuk n = 0 fungsi tidak mencetak apa-apa.\n\n"
         "Semua bilangan dicetak dalam satu baris, dipisah satu spasi. Untuk n = 3 keluarannya `3 2 1 1 2 3`.\n\n"
         "Perhatikan urutannya: perintah sesudah pemanggilan rekursif baru dijalankan setelah semua pemanggilan yang lebih dalam selesai.\n\n**Soal ini mensyaratkan rekursi.**",
         "Satu bilangan bulat n.", "Satu baris: n, n − 1, …, 1, lalu 1, 2, …, n.", "1 ≤ n ≤ 9"),
     en=("Down then Up", "Write a recursive function `void naikTurun(int n)` that prints n, then calls itself for n − 1, then **prints n once more**. For n = 0 the function prints nothing.\n\n"
         "All numbers are printed on one line, separated by single spaces. For n = 3 the output is `3 2 1 1 2 3`.\n\n"
         "Watch the order: a statement after the recursive call runs only when all the deeper calls have finished.\n\n**This problem requires recursion.**",
         "One integer n.", "One line: n, n − 1, …, 1, then 1, 2, …, n.", "1 ≤ n ≤ 9"),
     ar=("نزولًا ثم صعودًا", "اكتب دالة عودية `void naikTurun(int n)` تطبع n ثم تستدعي نفسها للعدد n − 1 ثم **تطبع n مرة أخرى**. عند n = 0 لا تطبع الدالة شيئًا.\n\n"
         "تُطبع كل الأعداد في سطر واحد تفصل بينها مسافة واحدة. عند n = 3 يكون المخرج `3 2 1 1 2 3`.\n\n"
         "انتبه إلى الترتيب: التعليمة التي تلي الاستدعاء العودي لا تُنفَّذ إلا بعد انتهاء كل الاستدعاءات الأعمق.\n\n**هذه المسألة تشترط العودية.**",
         "عدد صحيح واحد n.", "سطر واحد: n، n − 1، …، 1 ثم 1، 2، …، n.", "1 ≤ n ≤ 9"))

soal("luas-dua-bentuk", "mudah_sekali", "trimmed", "print(1, r.randint(1, 100)) if i % 2 == 0 else print(2, r.randint(1, 100), r.randint(1, 100))\n",
     program(r'''
int luas(int sisi) {
    return sisi * sisi;
}

int luas(int panjang, int lebar) {
    return panjang * lebar;
}''', r'''    int jenis;
    cin >> jenis;
    if (jenis == 1) {
        int s;
        cin >> s;
        cout << luas(s) << endl;
    } else {
        int p, l;
        cin >> p >> l;
        cout << luas(p, l) << endl;
    }'''),
     id=("Luas Dua Bentuk", "Di C++, dua fungsi boleh **bernama sama** asalkan daftar parameternya berbeda. Ini disebut *function overloading*; kompilator memilih fungsi yang cocok dari argumen pemanggilannya.\n\n"
         "Tulis dua fungsi bernama `luas`:\n\n- `int luas(int sisi)` — luas persegi;\n- `int luas(int panjang, int lebar)` — luas persegi panjang.\n\n"
         "Masukan diawali jenis bentuk: `1` diikuti panjang sisi, atau `2` diikuti panjang dan lebar. Cetak luasnya.\n\nContoh: `1 5` → 25; `2 4 6` → 24.",
         "Satu baris: jenis bentuk (1 atau 2), lalu satu atau dua ukuran.", "Satu bilangan bulat: luasnya.", "Tiap ukuran antara 1 dan 100."),
     en=("Area of Two Shapes", "In C++ two functions may have the **same name** as long as their parameter lists differ. This is called *function overloading*; the compiler picks the matching function from the arguments of the call.\n\n"
         "Write two functions named `luas` (area):\n\n- `int luas(int sisi)` — the area of a square;\n- `int luas(int panjang, int lebar)` — the area of a rectangle.\n\n"
         "The input starts with the kind of shape: `1` followed by the side, or `2` followed by the length and the width. Print the area.\n\nExamples: `1 5` → 25; `2 4 6` → 24.",
         "One line: the kind of shape (1 or 2), then one or two sizes.", "One integer: the area.", "Each size is between 1 and 100."),
     ar=("مساحة شكلين", "في ++C يجوز أن تحمل دالتان **الاسم نفسه** ما دامت قائمتا وسائطهما مختلفتين. يسمى هذا *التحميل الزائد للدوال*؛ ويختار المترجم الدالة المناسبة من وسائط الاستدعاء.\n\n"
         "اكتب دالتين باسم `luas` (المساحة):\n\n- `int luas(int sisi)` — مساحة مربع؛\n- `int luas(int panjang, int lebar)` — مساحة مستطيل.\n\n"
         "يبدأ المدخل بنوع الشكل: `1` يليه طول الضلع، أو `2` يليه الطول والعرض. اطبع المساحة.\n\nأمثلة: `1 5` ← 25؛ `2 4 6` ← 24.",
         "سطر واحد: نوع الشكل (1 أو 2) ثم قياس واحد أو قياسان.", "عدد صحيح واحد: المساحة.", "كل قياس بين 1 و 100."))

soal("bagi-dua-berulang", "mudah_sekali", "trimmed", "print({2: 1, 3: 1000000000, 4: 2 ** r.randint(1, 29), 5: 2 ** r.randint(1, 29) - 1}.get(i, r.randint(2, 10 ** " + dua(1, 4, 9) + ")))\n",
     program(r'''
int langkah(int n) {
    if (n == 1) {
        return 0;
    }
    return 1 + langkah(n / 2);
}''', r'''    int n;
    cin >> n;
    cout << langkah(n) << endl;'''), wajib=R,
     id=("Bagi Dua Berulang", "Sebuah bilangan dibagi dua berulang-ulang (pembagian bulat, sisanya dibuang) sampai menjadi 1. Berapa kali pembagian itu dilakukan?\n\n"
         "Contoh: 10 → 5 → 2 → 1, jadi jawabannya 3. Untuk 1 jawabannya 0.\n\nTulis fungsi rekursif `int langkah(int n)` dan cetak hasilnya.\n\n**Soal ini mensyaratkan rekursi.**",
         "Satu bilangan bulat n.", "Satu bilangan bulat: banyak pembagian sampai menjadi 1.", "1 ≤ n ≤ 1000000000"),
     en=("Halving Again and Again", "A number is halved again and again (integer division, the remainder is dropped) until it becomes 1. How many divisions are made?\n\n"
         "Example: 10 → 5 → 2 → 1, so the answer is 3. For 1 the answer is 0.\n\nWrite a recursive function `int langkah(int n)` and print the result.\n\n**This problem requires recursion.**",
         "One integer n.", "One integer: the number of divisions until 1 is reached.", "1 ≤ n ≤ 1000000000"),
     ar=("التنصيف المتكرر", "يُقسم عدد على اثنين مرة بعد مرة (قسمة صحيحة يُهمل باقيها) حتى يصير 1. كم مرة تُجرى القسمة؟\n\n"
         "مثال: 10 ← 5 ← 2 ← 1، فالجواب 3. للعدد 1 الجواب 0.\n\nاكتب دالة عودية `int langkah(int n)` واطبع الناتج.\n\n**هذه المسألة تشترط العودية.**",
         "عدد صحيح واحد n.", "عدد صحيح واحد: عدد مرات القسمة حتى بلوغ 1.", "1 ≤ n ≤ 1000000000"))

# ================================= MUDAH (8) =================================

soal("fibonacci", "mudah", "trimmed", "print({2: 0, 3: 1, 4: 30}.get(i, " + dua(2, 15, 29) + "))\n",
     program(r'''
int fibonacci(int n) {
    if (n < 2) {
        return n;
    }
    return fibonacci(n - 1) + fibonacci(n - 2);
}''', r'''    int n;
    cin >> n;
    cout << fibonacci(n) << endl;'''), wajib=R,
     id=("Fibonacci", "Barisan Fibonacci dimulai dengan 0 dan 1; setiap suku berikutnya adalah jumlah dua suku sebelumnya: 0, 1, 1, 2, 3, 5, 8, 13, …\n\n"
         "Suku pertama diberi nomor 0, jadi F(0) = 0, F(1) = 1, dan F(n) = F(n − 1) + F(n − 2).\n\nTulis fungsi rekursif `int fibonacci(int n)` dan cetak F(n). Fungsi ini memanggil dirinya **dua kali**.\n\nF(10) = 55.\n\n**Soal ini mensyaratkan rekursi.**",
         "Satu bilangan bulat n.", "Satu bilangan bulat: F(n).", "0 ≤ n ≤ 30"),
     en=("Fibonacci", "The Fibonacci sequence starts with 0 and 1; every next term is the sum of the two before it: 0, 1, 1, 2, 3, 5, 8, 13, …\n\n"
         "The first term has number 0, so F(0) = 0, F(1) = 1, and F(n) = F(n − 1) + F(n − 2).\n\nWrite a recursive function `int fibonacci(int n)` and print F(n). This function calls itself **twice**.\n\nF(10) = 55.\n\n**This problem requires recursion.**",
         "One integer n.", "One integer: F(n).", "0 ≤ n ≤ 30"),
     ar=("فيبوناتشي", "تبدأ متتالية فيبوناتشي بالعددين 0 و 1، وكل حد تالٍ هو مجموع الحدين السابقين: 0، 1، 1، 2، 3، 5، 8، 13، …\n\n"
         "رقم الحد الأول 0، فيكون F(0) = 0 و F(1) = 1 و F(n) = F(n − 1) + F(n − 2).\n\nاكتب دالة عودية `int fibonacci(int n)` واطبع F(n). هذه الدالة تستدعي نفسها **مرتين**.\n\nF(10) = 55.\n\n**هذه المسألة تشترط العودية.**",
         "عدد صحيح واحد n.", "عدد صحيح واحد: F(n).", "0 ≤ n ≤ 30"))

soal("hanoi-langkah", "mudah", "trimmed", "print({2: 1, 3: 30}.get(i, " + dua(2, 15, 29) + "))\n",
     program(r'''
int langkah(int n) {
    if (n == 0) {
        return 0;
    }
    return 2 * langkah(n - 1) + 1;
}''', r'''    int n;
    cin >> n;
    cout << langkah(n) << endl;'''), wajib=R,
     id=("Menara Hanoi: Banyak Langkah", "Menara Hanoi adalah teka-teki dengan tiga tiang dan n cakram berbeda ukuran. Semua cakram mula-mula bertumpuk di tiang pertama, yang terbesar di bawah. Cakram dipindahkan satu per satu, dan cakram besar tidak boleh ditaruh di atas cakram kecil.\n\n"
         "Untuk memindahkan n cakram: pindahkan dulu n − 1 cakram teratas ke tiang cadangan, pindahkan cakram terbesar (1 langkah), lalu pindahkan n − 1 cakram tadi ke atasnya. Jadi langkah(n) = 2 × langkah(n − 1) + 1, dan langkah(0) = 0.\n\n"
         "Tulis fungsi rekursif `int langkah(int n)` dan cetak banyak langkahnya. Untuk 3 cakram jawabannya 7.\n\n"
         "Hitung langkah(n − 1) **sekali saja** lalu kalikan dua; memanggilnya dua kali membuat program terlalu lambat untuk n besar.\n\n**Soal ini mensyaratkan rekursi.**",
         "Satu bilangan bulat n.", "Satu bilangan bulat: banyak langkah.", "1 ≤ n ≤ 30"),
     en=("Tower of Hanoi: Number of Moves", "The Tower of Hanoi is a puzzle with three pegs and n discs of different sizes. All discs start stacked on the first peg, the largest at the bottom. Discs are moved one at a time, and a larger disc may never lie on a smaller one.\n\n"
         "To move n discs: first move the top n − 1 discs to the spare peg, move the largest disc (1 move), then move the n − 1 discs onto it. So langkah(n) = 2 × langkah(n − 1) + 1, and langkah(0) = 0.\n\n"
         "Write a recursive function `int langkah(int n)` and print the number of moves. For 3 discs the answer is 7.\n\n"
         "Compute langkah(n − 1) **once** and double it; calling it twice makes the program too slow for large n.\n\n**This problem requires recursion.**",
         "One integer n.", "One integer: the number of moves.", "1 ≤ n ≤ 30"),
     ar=("برج هانوي: عدد الحركات", "برج هانوي لغز فيه ثلاثة أعمدة و n قرصًا مختلفة الأحجام. تبدأ الأقراص كلها مرصوصة على العمود الأول والأكبر في الأسفل. يُنقل قرص واحد في كل مرة، ولا يجوز وضع قرص أكبر فوق قرص أصغر.\n\n"
         "لنقل n قرصًا: انقل أولًا n − 1 قرصًا العلوية إلى العمود الاحتياطي، ثم انقل القرص الأكبر (حركة واحدة)، ثم انقل الأقراص n − 1 فوقه. إذن langkah(n) = 2 × langkah(n − 1) + 1، و langkah(0) = 0.\n\n"
         "اكتب دالة عودية `int langkah(int n)` واطبع عدد الحركات. لثلاثة أقراص الجواب 7.\n\n"
         "احسب langkah(n − 1) **مرة واحدة** ثم ضاعفها؛ استدعاؤها مرتين يجعل البرنامج بطيئًا جدًّا عند n كبير.\n\n**هذه المسألة تشترط العودية.**",
         "عدد صحيح واحد n.", "عدد صحيح واحد: عدد الحركات.", "1 ≤ n ≤ 30"))

soal("digit-genap", "mudah", "trimmed", "print({2: 0, 3: 1357913, 4: 2468024, 5: 1000000000}.get(i, r.randint(1, 10 ** " + dua(1, 4, 9) + ")))\n",
     program(r'''
int digitGenap(int n) {
    int ini = 0;
    if (n % 2 == 0) {
        ini = 1;
    }
    if (n < 10) {
        return ini;
    }
    return ini + digitGenap(n / 10);
}''', r'''    int n;
    cin >> n;
    cout << digitGenap(n) << endl;'''), wajib=R,
     id=("Banyak Digit Genap", "Tulis fungsi rekursif `int digitGenap(int n)` yang mengembalikan berapa banyak digit genap (0, 2, 4, 6, 8) pada bilangan n, lalu cetak hasilnya.\n\n"
         "Digit terakhir n genap bila n sendiri genap. Sisanya diserahkan kepada pemanggilan rekursif untuk n / 10. Bilangan satu digit adalah kasus dasarnya.\n\nUntuk 40726 jawabannya 4 (digit 4, 0, 2, 6). Untuk 0 jawabannya 1.\n\n**Soal ini mensyaratkan rekursi.**",
         "Satu bilangan bulat n.", "Satu bilangan bulat: banyak digit genap.", "0 ≤ n ≤ 1000000000"),
     en=("Number of Even Digits", "Write a recursive function `int digitGenap(int n)` that returns how many even digits (0, 2, 4, 6, 8) the number n has, then print the result.\n\n"
         "The last digit of n is even when n itself is even. The rest is left to the recursive call for n / 10. A one-digit number is the base case.\n\nFor 40726 the answer is 4 (the digits 4, 0, 2, 6). For 0 the answer is 1.\n\n**This problem requires recursion.**",
         "One integer n.", "One integer: the number of even digits.", "0 ≤ n ≤ 1000000000"),
     ar=("عدد الأرقام الزوجية", "اكتب دالة عودية `int digitGenap(int n)` تُرجع عدد الأرقام الزوجية (0، 2، 4، 6، 8) في العدد n ثم اطبع الناتج.\n\n"
         "الرقم الأخير من n زوجي إذا كان n نفسه زوجيًا. والباقي يُترك للاستدعاء العودي للعدد n / 10. العدد ذو الرقم الواحد هو الحالة الأساسية.\n\nللعدد 40726 الجواب 4 (الأرقام 4، 0، 2، 6). وللعدد 0 الجواب 1.\n\n**هذه المسألة تشترط العودية.**",
         "عدد صحيح واحد n.", "عدد صحيح واحد: عدد الأرقام الزوجية.", "0 ≤ n ≤ 1000000000"))

soal("volume-bawaan", "mudah", "trimmed", "k = i % 3 + 1\nprint(k)\nprint(*[r.randint(1, 100) for _ in range(k)])\n",
     program(r'''
int volume(int panjang, int lebar = 1, int tinggi = 1) {
    return panjang * lebar * tinggi;
}''', r'''    int k, a, b, c;
    cin >> k;
    if (k == 1) {
        cin >> a;
        cout << volume(a) << endl;
    } else if (k == 2) {
        cin >> a >> b;
        cout << volume(a, b) << endl;
    } else {
        cin >> a >> b >> c;
        cout << volume(a, b, c) << endl;
    }'''),
     id=("Parameter Bawaan", "Parameter sebuah fungsi boleh diberi **nilai bawaan**. Bila pemanggil tidak mengisinya, nilai bawaan itulah yang dipakai.\n\n"
         "Tulis satu fungsi `int volume(int panjang, int lebar = 1, int tinggi = 1)` yang mengembalikan panjang × lebar × tinggi. Fungsi yang sama lalu bisa dipanggil dengan satu, dua, atau tiga argumen.\n\n"
         "Masukan berisi banyak ukuran k, lalu k ukuran. Panggil `volume` dengan tepat k argumen dan cetak hasilnya.\n\nContoh: ukuran `7` → 7; ukuran `7 3` → 21; ukuran `7 3 2` → 42.",
         "Baris pertama: k. Baris kedua: k bilangan bulat.", "Satu bilangan bulat: hasil kali ukuran-ukuran itu.", "k adalah 1, 2, atau 3; tiap ukuran antara 1 dan 100"),
     en=("Default Parameters", "A function's parameters may be given **default values**. When the caller leaves one out, the default is used.\n\n"
         "Write one function `int volume(int panjang, int lebar = 1, int tinggi = 1)` that returns length × width × height. The same function can then be called with one, two or three arguments.\n\n"
         "The input holds the number of sizes k, then k sizes. Call `volume` with exactly k arguments and print the result.\n\nExamples: sizes `7` → 7; sizes `7 3` → 21; sizes `7 3 2` → 42.",
         "First line: k. Second line: k integers.", "One integer: the product of the sizes.", "k is 1, 2 or 3; each size is between 1 and 100"),
     ar=("الوسائط الافتراضية", "يجوز إعطاء وسائط الدالة **قيمًا افتراضية**. إذا لم يملأها المستدعي استُعملت القيمة الافتراضية.\n\n"
         "اكتب دالة واحدة `int volume(int panjang, int lebar = 1, int tinggi = 1)` تُرجع الطول × العرض × الارتفاع. يمكن بعدها استدعاء الدالة نفسها بوسيط واحد أو اثنين أو ثلاثة.\n\n"
         "في المدخل عدد القياسات k ثم k قياسًا. استدعِ `volume` بعدد k من الوسائط بالضبط واطبع الناتج.\n\nأمثلة: القياسات `7` ← 7؛ القياسات `7 3` ← 21؛ القياسات `7 3 2` ← 42.",
         "السطر الأول: k. السطر الثاني: k عددًا صحيحًا.", "عدد صحيح واحد: حاصل ضرب القياسات.", "k يساوي 1 أو 2 أو 3؛ كل قياس بين 1 و 100"))

soal("gabung-atau-jumlah", "mudah", "trimmed",
     "KATA = ['belajar', 'program', 'kampus', 'buku', 'meja', 'layar', 'kode', 'data', 'angka', 'huruf', 'kelas', 'tugas']\nif i % 2 == 0:\n    print('angka', r.randint(-1000, 1000), r.randint(-1000, 1000))\nelse:\n    print('teks', r.choice(KATA), r.choice(KATA))\n",
     program(r'''
int gabung(int a, int b) {
    return a + b;
}

string gabung(string a, string b) {
    return a + b;
}''', r'''    string jenis;
    cin >> jenis;
    if (jenis == "angka") {
        int a, b;
        cin >> a >> b;
        cout << gabung(a, b) << endl;
    } else {
        string a, b;
        cin >> a >> b;
        cout << gabung(a, b) << endl;
    }''', "string"),
     id=("Gabung atau Jumlah", "Fungsi yang di-*overload* juga bisa dibedakan dari **tipe** parameternya, tidak hanya dari banyaknya.\n\n"
         "Tulis dua fungsi bernama `gabung`:\n\n- `int gabung(int a, int b)` — mengembalikan jumlah dua bilangan;\n- `string gabung(string a, string b)` — mengembalikan dua teks yang disambung.\n\n"
         "Masukan diawali kata `angka` atau `teks`, diikuti dua nilai sejenis. Baca keduanya ke variabel bertipe sesuai, panggil `gabung`, dan cetak hasilnya.\n\nContoh: `angka 12 30` → 42; `teks buku tugas` → `bukutugas`.",
         "Satu baris: `angka` diikuti dua bilangan bulat, atau `teks` diikuti dua kata.", "Jumlah kedua bilangan, atau kedua kata disambung tanpa spasi.", "Bilangan antara −1000 dan 1000; kata paling panjang 10 huruf."),
     en=("Join or Add", "Overloaded functions can also differ in the **types** of their parameters, not only in how many there are.\n\n"
         "Write two functions named `gabung`:\n\n- `int gabung(int a, int b)` — returns the sum of two numbers;\n- `string gabung(string a, string b)` — returns the two texts joined.\n\n"
         "The input starts with the word `angka` (number) or `teks` (text), followed by two values of that kind. Read them into variables of the right type, call `gabung`, and print the result.\n\nExamples: `angka 12 30` → 42; `teks buku tugas` → `bukutugas`.",
         "One line: `angka` followed by two integers, or `teks` followed by two words.", "The sum of the numbers, or the two words joined without a space.", "Numbers are between −1000 and 1000; words have at most 10 letters."),
     ar=("وصل أم جمع", "الدوال المحمَّلة تحميلًا زائدًا يمكن أن تختلف في **أنواع** وسائطها أيضًا، لا في عددها فقط.\n\n"
         "اكتب دالتين باسم `gabung`:\n\n- `int gabung(int a, int b)` — تُرجع مجموع عددين؛\n- `string gabung(string a, string b)` — تُرجع النصين موصولين.\n\n"
         "يبدأ المدخل بالكلمة `angka` (عدد) أو `teks` (نص)، تليها قيمتان من ذلك النوع. اقرأهما في متغيرين من النوع المناسب واستدعِ `gabung` واطبع الناتج.\n\nأمثلة: `angka 12 30` ← 42؛ `teks buku tugas` ← `bukutugas`.",
         "سطر واحد: `angka` يليها عددان صحيحان، أو `teks` تليها كلمتان.", "مجموع العددين، أو الكلمتان موصولتين بلا مسافة.", "الأعداد بين −1000 و 1000؛ الكلمات 10 حروف على الأكثر."))

soal("penggaris", "mudah", "trimmed", "print({2: 1, 3: 7}.get(i, " + dua(2, 4, 6) + "))\n",
     program(r'''
void penggaris(int n) {
    if (n == 0) {
        return;
    }
    penggaris(n - 1);
    for (int k = 0; k < n; k++) {
        cout << "-";
    }
    cout << endl;
    penggaris(n - 1);
}''', r'''    int n;
    cin >> n;
    penggaris(n);'''), wajib=R,
     id=("Garis Penggaris", "Garis-garis pada penggaris punya pola: garis terpanjang di tengah, dan di kiri-kanannya pola yang sama dengan garis lebih pendek.\n\n"
         "Tulis fungsi rekursif `void penggaris(int n)`: gambar pola untuk n − 1, lalu satu baris berisi n tanda hubung, lalu pola untuk n − 1 sekali lagi. Untuk n = 0 tidak ada yang digambar.\n\n"
         "Untuk n = 3:\n\n```\n-\n--\n-\n---\n-\n--\n-\n```\n\n**Soal ini mensyaratkan rekursi.**",
         "Satu bilangan bulat n.", "Pola penggaris untuk n.", "1 ≤ n ≤ 7"),
     en=("Ruler Marks", "The marks on a ruler follow a pattern: the longest mark in the middle, and on either side of it the same pattern with shorter marks.\n\n"
         "Write a recursive function `void penggaris(int n)`: draw the pattern for n − 1, then one line of n hyphens, then the pattern for n − 1 once more. For n = 0 nothing is drawn.\n\n"
         "For n = 3:\n\n```\n-\n--\n-\n---\n-\n--\n-\n```\n\n**This problem requires recursion.**",
         "One integer n.", "The ruler pattern for n.", "1 ≤ n ≤ 7"),
     ar=("علامات المسطرة", "لعلامات المسطرة نمط: أطول علامة في الوسط، وعن جانبيها النمط نفسه بعلامات أقصر.\n\n"
         "اكتب دالة عودية `void penggaris(int n)`: ارسم نمط n − 1، ثم سطرًا فيه n شرطة، ثم نمط n − 1 مرة أخرى. عند n = 0 لا يُرسم شيء.\n\n"
         "عند n = 3:\n\n```\n-\n--\n-\n---\n-\n--\n-\n```\n\n**هذه المسألة تشترط العودية.**",
         "عدد صحيح واحد n.", "نمط المسطرة للعدد n.", "1 ≤ n ≤ 7"))

soal("fungsi-91", "mudah", "trimmed", "print({2: 100, 3: 101, 4: 1, 5: 200, 6: 91, 7: 111}.get(i, " + dua(2, 99, 199) + "))\n",
     program(r'''
int m91(int n) {
    if (n > 100) {
        return n - 10;
    }
    return m91(m91(n + 11));
}''', r'''    int n;
    cin >> n;
    cout << m91(n) << endl;'''), wajib=R,
     id=("Fungsi 91", "Fungsi berikut terkenal karena hasilnya sulit ditebak hanya dengan membaca definisinya:\n\n- bila n lebih dari 100, M(n) = n − 10;\n- bila tidak, M(n) = M(M(n + 11)).\n\n"
         "Pada baris kedua, hasil pemanggilan rekursif langsung menjadi argumen pemanggilan rekursif berikutnya.\n\nTulis fungsi rekursif `int m91(int n)` persis seperti definisi itu dan cetak M(n).\n\nContoh: M(105) = 95, dan M(99) = 91.\n\n**Soal ini mensyaratkan rekursi.**",
         "Satu bilangan bulat n.", "Satu bilangan bulat: M(n).", "1 ≤ n ≤ 200"),
     en=("The 91 Function", "The following function is famous because its result is hard to guess just by reading the definition:\n\n- if n is more than 100, M(n) = n − 10;\n- otherwise M(n) = M(M(n + 11)).\n\n"
         "In the second line the result of a recursive call at once becomes the argument of the next recursive call.\n\nWrite a recursive function `int m91(int n)` exactly as defined and print M(n).\n\nExamples: M(105) = 95, and M(99) = 91.\n\n**This problem requires recursion.**",
         "One integer n.", "One integer: M(n).", "1 ≤ n ≤ 200"),
     ar=("الدالة 91", "الدالة الآتية مشهورة لأن ناتجها يصعب تخمينه من قراءة تعريفها فقط:\n\n- إذا كان n أكبر من 100 فإن M(n) = n − 10؛\n- وإلا فإن M(n) = M(M(n + 11)).\n\n"
         "في السطر الثاني يصير ناتج الاستدعاء العودي وسيطًا للاستدعاء العودي التالي مباشرة.\n\nاكتب دالة عودية `int m91(int n)` كما في التعريف تمامًا واطبع M(n).\n\nأمثلة: M(105) = 95 و M(99) = 91.\n\n**هذه المسألة تشترط العودية.**",
         "عدد صحيح واحد n.", "عدد صحيح واحد: M(n).", "1 ≤ n ≤ 200"))

soal("naik-tangga", "mudah", "trimmed", "print({2: 1, 3: 25, 4: 2, 5: 3}.get(i, " + dua(4, 14, 24) + "))\n",
     program(r'''
int cara(int n) {
    if (n < 0) {
        return 0;
    }
    if (n == 0) {
        return 1;
    }
    return cara(n - 1) + cara(n - 2) + cara(n - 3);
}''', r'''    int n;
    cin >> n;
    cout << cara(n) << endl;'''), wajib=R,
     id=("Naik Tangga", "Sebuah tangga punya n anak tangga. Sekali melangkah, Anda boleh naik **1, 2, atau 3** anak tangga. Ada berapa cara berbeda untuk sampai tepat di puncak?\n\n"
         "Untuk n = 3 ada 4 cara: 1+1+1, 1+2, 2+1, dan 3. Untuk n = 4 ada 7 cara.\n\n"
         "Pikirkan langkah **pertama**: setelah naik 1, tersisa n − 1 anak tangga; setelah naik 2, tersisa n − 2; setelah naik 3, tersisa n − 3. Banyak cara seluruhnya adalah jumlah ketiga kemungkinan itu. Bila tidak ada anak tangga tersisa, ada tepat satu cara: diam.\n\n"
         "Tulis fungsi rekursif `int cara(int n)` dan cetak hasilnya.\n\n**Soal ini mensyaratkan rekursi.**",
         "Satu bilangan bulat n.", "Satu bilangan bulat: banyak cara.", "1 ≤ n ≤ 25"),
     en=("Climbing Stairs", "A staircase has n steps. In one stride you may climb **1, 2 or 3** steps. How many different ways are there to arrive exactly at the top?\n\n"
         "For n = 3 there are 4 ways: 1+1+1, 1+2, 2+1 and 3. For n = 4 there are 7 ways.\n\n"
         "Think about the **first** stride: after climbing 1, n − 1 steps remain; after 2, n − 2 remain; after 3, n − 3 remain. The total number of ways is the sum of those three cases. When no step remains there is exactly one way: stand still.\n\n"
         "Write a recursive function `int cara(int n)` and print the result.\n\n**This problem requires recursion.**",
         "One integer n.", "One integer: the number of ways.", "1 ≤ n ≤ 25"),
     ar=("صعود الدرج", "درج فيه n درجة. في الخطوة الواحدة يجوز أن تصعد **1 أو 2 أو 3** درجات. كم طريقة مختلفة توصلك إلى القمة تمامًا؟\n\n"
         "عند n = 3 توجد 4 طرق: 1+1+1 و 1+2 و 2+1 و 3. وعند n = 4 توجد 7 طرق.\n\n"
         "فكّر في الخطوة **الأولى**: بعد صعود 1 تبقى n − 1 درجة، وبعد صعود 2 تبقى n − 2، وبعد صعود 3 تبقى n − 3. عدد الطرق كلها هو مجموع الحالات الثلاث. إذا لم تبقَ أي درجة فهناك طريقة واحدة بالضبط: الوقوف.\n\n"
         "اكتب دالة عودية `int cara(int n)` واطبع الناتج.\n\n**هذه المسألة تشترط العودية.**",
         "عدد صحيح واحد n.", "عدد صحيح واحد: عدد الطرق.", "1 ≤ n ≤ 25"))

# ============================== MUDAH-SEDANG (6) ==============================

soal("kombinasi", "mudah_sedang", "trimmed",
     "n = " + dua(1, 12, 25) + "\nk = r.randint(0, n)\nif i == 2:\n    k = 0\nif i == 3:\n    k = n\nif i == 4:\n    n, k = 25, 12\nprint(n, k)\n",
     program(r'''
int kombinasi(int n, int k) {
    if (k == 0 || k == n) {
        return 1;
    }
    return kombinasi(n - 1, k - 1) + kombinasi(n - 1, k);
}''', r'''    int n, k;
    cin >> n >> k;
    cout << kombinasi(n, k) << endl;'''), wajib=R,
     id=("Kombinasi", "Dari n orang akan dipilih k orang untuk menjadi panitia. Banyak cara memilihnya ditulis C(n, k).\n\n"
         "Perhatikan satu orang tertentu, misalnya Andi. Panitia itu memuat Andi — lalu k − 1 orang lain dipilih dari n − 1 orang sisanya — atau tidak memuat Andi, dan k orang dipilih dari n − 1 orang sisanya. Jadi:\n\n"
         "**C(n, k) = C(n − 1, k − 1) + C(n − 1, k)**\n\ndengan C(n, 0) = 1 dan C(n, n) = 1.\n\n"
         "Tulis fungsi rekursif `int kombinasi(int n, int k)` dan cetak C(n, k). Contoh: C(5, 2) = 10.\n\n**Soal ini mensyaratkan rekursi.**",
         "Satu baris berisi n dan k.", "Satu bilangan bulat: C(n, k).", "1 ≤ n ≤ 25; 0 ≤ k ≤ n"),
     en=("Combinations", "From n people, k are to be chosen for a committee. The number of ways to choose them is written C(n, k).\n\n"
         "Look at one particular person, say Andi. Either the committee includes Andi — then k − 1 others are chosen from the remaining n − 1 — or it does not, and k are chosen from the remaining n − 1. So:\n\n"
         "**C(n, k) = C(n − 1, k − 1) + C(n − 1, k)**\n\nwith C(n, 0) = 1 and C(n, n) = 1.\n\n"
         "Write a recursive function `int kombinasi(int n, int k)` and print C(n, k). Example: C(5, 2) = 10.\n\n**This problem requires recursion.**",
         "One line with n and k.", "One integer: C(n, k).", "1 ≤ n ≤ 25; 0 ≤ k ≤ n"),
     ar=("التوافيق", "من n شخصًا يُختار k شخصًا للجنة. عدد طرق الاختيار يُكتب C(n, k).\n\n"
         "انظر إلى شخص بعينه، وليكن Andi. إما أن تضم اللجنة Andi — فيُختار k − 1 آخرون من الباقين وعددهم n − 1 — وإما ألّا تضمه، فيُختار k من الباقين وعددهم n − 1. إذن:\n\n"
         "**C(n, k) = C(n − 1, k − 1) + C(n − 1, k)**\n\nمع C(n, 0) = 1 و C(n, n) = 1.\n\n"
         "اكتب دالة عودية `int kombinasi(int n, int k)` واطبع C(n, k). مثال: C(5, 2) = 10.\n\n**هذه المسألة تشترط العودية.**",
         "سطر واحد فيه n و k.", "عدد صحيح واحد: C(n, k).", "1 ≤ n ≤ 25؛ 0 ≤ k ≤ n"))

soal("format-waktu", "mudah_sedang", "trimmed", "k = i % 3 + 1\nv = [r.randint(0, 23), r.randint(0, 59), r.randint(0, 59)][:k]\nif i == 3:\n    v = [0]\nif i == 4:\n    v = [7, 5]\nprint(k)\nprint(*v)\n",
     program(r'''
string duaDigit(int x) {
    string hasil = to_string(x);
    if (x < 10) {
        hasil = "0" + hasil;
    }
    return hasil;
}

string waktu(int jam, int menit = 0, int detik = 0) {
    return duaDigit(jam) + ":" + duaDigit(menit) + ":" + duaDigit(detik);
}''', r'''    int k, a, b, c;
    cin >> k;
    if (k == 1) {
        cin >> a;
        cout << waktu(a) << endl;
    } else if (k == 2) {
        cin >> a >> b;
        cout << waktu(a, b) << endl;
    } else {
        cin >> a >> b >> c;
        cout << waktu(a, b, c) << endl;
    }''', "string"),
     id=("Format Waktu", "Tulis fungsi `string waktu(int jam, int menit = 0, int detik = 0)` yang mengembalikan waktu sebagai teks berbentuk `JJ:MM:DD`, tiap bagian selalu dua digit. Menit dan detik punya nilai bawaan 0, sehingga `waktu(7)` menghasilkan `07:00:00`.\n\n"
         "Masukan berisi banyak nilai k, lalu k nilai: jam saja, jam dan menit, atau jam, menit, dan detik. Panggil `waktu` dengan tepat k argumen dan cetak hasilnya.\n\n"
         "Contoh: nilai `7` → `07:00:00`; nilai `7 5` → `07:05:00`; nilai `13 45 9` → `13:45:09`.\n\nPetunjuk: `to_string(x)` mengubah bilangan menjadi `string`.",
         "Baris pertama: k. Baris kedua: k bilangan bulat.", "Waktu dalam bentuk `JJ:MM:DD`.", "k adalah 1, 2, atau 3; 0 ≤ jam ≤ 23; 0 ≤ menit, detik ≤ 59"),
     en=("Time Format", "Write a function `string waktu(int jam, int menit = 0, int detik = 0)` that returns a time as text of the form `HH:MM:SS`, every part always two digits. Minutes and seconds default to 0, so `waktu(7)` gives `07:00:00`.\n\n"
         "The input holds the number of values k, then k values: the hour only, hour and minutes, or hour, minutes and seconds. Call `waktu` with exactly k arguments and print the result.\n\n"
         "Examples: values `7` → `07:00:00`; values `7 5` → `07:05:00`; values `13 45 9` → `13:45:09`.\n\nHint: `to_string(x)` turns a number into a `string`.",
         "First line: k. Second line: k integers.", "The time in the form `HH:MM:SS`.", "k is 1, 2 or 3; 0 ≤ hour ≤ 23; 0 ≤ minutes, seconds ≤ 59"),
     ar=("تنسيق الوقت", "اكتب دالة `string waktu(int jam, int menit = 0, int detik = 0)` تُرجع الوقت نصًّا على الصورة `HH:MM:SS`، كل جزء برقمين دائمًا. القيمة الافتراضية للدقائق والثواني 0، فالاستدعاء `waktu(7)` يعطي `07:00:00`.\n\n"
         "في المدخل عدد القيم k ثم k قيمة: الساعة فقط، أو الساعة والدقائق، أو الساعة والدقائق والثواني. استدعِ `waktu` بعدد k من الوسائط بالضبط واطبع الناتج.\n\n"
         "أمثلة: القيم `7` ← `07:00:00`؛ القيم `7 5` ← `07:05:00`؛ القيم `13 45 9` ← `13:45:09`.\n\nتلميح: `to_string(x)` تحوّل العدد إلى `string`.",
         "السطر الأول: k. السطر الثاني: k عددًا صحيحًا.", "الوقت على الصورة `HH:MM:SS`.", "k يساوي 1 أو 2 أو 3؛ 0 ≤ الساعة ≤ 23؛ 0 ≤ الدقائق، الثواني ≤ 59"))

soal("hitung-panggilan", "mudah_sedang", "trimmed", "print({2: 0, 3: 1, 4: 25}.get(i, " + dua(2, 13, 24) + "))\n",
     program(r'''
int fib(int n, int &panggilan) {
    panggilan++;
    if (n < 2) {
        return n;
    }
    return fib(n - 1, panggilan) + fib(n - 2, panggilan);
}''', r'''    int n;
    cin >> n;
    int panggilan = 0;
    int hasil = fib(n, panggilan);
    cout << hasil << " " << panggilan << endl;'''), wajib=R,
     id=("Berapa Kali Dipanggil?", "Fungsi Fibonacci rekursif — F(0) = 0, F(1) = 1, F(n) = F(n − 1) + F(n − 2) — menghitung hal yang sama berulang kali. Seberapa boros?\n\n"
         "Tulis `int fib(int n, int &panggilan)`: fungsi Fibonacci rekursif yang **menaikkan `panggilan` satu setiap kali ia dipanggil**. Karena `panggilan` adalah parameter by-reference, semua pemanggilan menghitung pada variabel yang sama di `main`.\n\n"
         "Cetak F(n), lalu banyak pemanggilan seluruhnya (termasuk pemanggilan pertama dari `main`).\n\n"
         "Contoh: untuk n = 5 keluarannya `5 15`; untuk n = 1 keluarannya `1 1`.\n\n**Soal ini mensyaratkan rekursi.**",
         "Satu bilangan bulat n.", "Satu baris: F(n), lalu banyak pemanggilan.", "0 ≤ n ≤ 25"),
     en=("How Many Calls?", "The recursive Fibonacci function — F(0) = 0, F(1) = 1, F(n) = F(n − 1) + F(n − 2) — computes the same things over and over. How wasteful is it?\n\n"
         "Write `int fib(int n, int &panggilan)`: a recursive Fibonacci function that **raises `panggilan` by one every time it is called**. Because `panggilan` is a by-reference parameter, all calls count on the same variable in `main`.\n\n"
         "Print F(n), then the total number of calls (including the first call from `main`).\n\n"
         "Examples: for n = 5 the output is `5 15`; for n = 1 it is `1 1`.\n\n**This problem requires recursion.**",
         "One integer n.", "One line: F(n), then the number of calls.", "0 ≤ n ≤ 25"),
     ar=("كم مرة استُدعيت؟", "دالة فيبوناتشي العودية — F(0) = 0 و F(1) = 1 و F(n) = F(n − 1) + F(n − 2) — تحسب الأشياء نفسها مرارًا. فما مقدار هذا الإسراف؟\n\n"
         "اكتب `int fib(int n, int &panggilan)`: دالة فيبوناتشي عودية **تزيد `panggilan` واحدًا في كل مرة تُستدعى**. ولأن `panggilan` وسيط بالمرجع فكل الاستدعاءات تعدّ على المتغير نفسه في `main`.\n\n"
         "اطبع F(n) ثم العدد الكلي للاستدعاءات (ومنها الاستدعاء الأول من `main`).\n\n"
         "أمثلة: عند n = 5 يكون المخرج `5 15`، وعند n = 1 يكون `1 1`.\n\n**هذه المسألة تشترط العودية.**",
         "عدد صحيح واحد n.", "سطر واحد: F(n) ثم عدد الاستدعاءات.", "0 ≤ n ≤ 25"))

soal("fungsi-bercabang", "mudah_sedang", "trimmed", "print({2: 0, 3: 1, 4: 100000, 5: 6}.get(i, r.randint(2, 10 ** " + dua(1, 2, 5) + ")))\n",
     program(r'''
int f(int n) {
    if (n == 0) {
        return 0;
    }
    return n + f(n / 2) + f(n / 3);
}''', r'''    int n;
    cin >> n;
    cout << f(n) << endl;'''), wajib=R,
     id=("Fungsi Bercabang", "Sebuah fungsi didefinisikan sebagai berikut, dengan pembagian bulat (sisa dibuang):\n\n- f(0) = 0;\n- f(n) = n + f(n / 2) + f(n / 3) untuk n lebih dari 0.\n\n"
         "Tulis fungsi rekursif `int f(int n)` dan cetak f(n).\n\n"
         "Contoh: f(1) = 1 + f(0) + f(0) = 1. f(2) = 2 + f(1) + f(0) = 3. f(3) = 3 + f(1) + f(1) = 5. f(6) = 6 + f(3) + f(2) = 14.\n\n**Soal ini mensyaratkan rekursi.**",
         "Satu bilangan bulat n.", "Satu bilangan bulat: f(n).", "0 ≤ n ≤ 100000"),
     en=("A Branching Function", "A function is defined as follows, with integer division (the remainder is dropped):\n\n- f(0) = 0;\n- f(n) = n + f(n / 2) + f(n / 3) for n greater than 0.\n\n"
         "Write a recursive function `int f(int n)` and print f(n).\n\n"
         "Examples: f(1) = 1 + f(0) + f(0) = 1. f(2) = 2 + f(1) + f(0) = 3. f(3) = 3 + f(1) + f(1) = 5. f(6) = 6 + f(3) + f(2) = 14.\n\n**This problem requires recursion.**",
         "One integer n.", "One integer: f(n).", "0 ≤ n ≤ 100000"),
     ar=("دالة متفرعة", "تُعرَّف دالة كما يأتي، والقسمة قسمة صحيحة (يُهمل الباقي):\n\n- f(0) = 0؛\n- f(n) = n + f(n / 2) + f(n / 3) عندما يكون n أكبر من 0.\n\n"
         "اكتب دالة عودية `int f(int n)` واطبع f(n).\n\n"
         "أمثلة: f(1) = 1 + f(0) + f(0) = 1. و f(2) = 2 + f(1) + f(0) = 3. و f(3) = 3 + f(1) + f(1) = 5. و f(6) = 6 + f(3) + f(2) = 14.\n\n**هذه المسألة تشترط العودية.**",
         "عدد صحيح واحد n.", "عدد صحيح واحد: f(n).", "0 ≤ n ≤ 100000"))

soal("tebak-biner", "mudah_sedang", "trimmed",
     "n = r.randint(1, 10 ** " + dua(1, 3, 6) + ")\nt = r.randint(1, n)\nif i == 2:\n    t = 1\nif i == 3:\n    t = n\nif i == 4:\n    n, t = 100, 37\nif i == 5:\n    n, t = 1, 1\nprint(n, t)\n",
     program(r'''
void tebak(int bawah, int atas, int target) {
    int tengah = (bawah + atas) / 2;
    cout << tengah << " ";
    if (tengah == target) {
        return;
    }
    if (tengah < target) {
        tebak(tengah + 1, atas, target);
    } else {
        tebak(bawah, tengah - 1, target);
    }
}''', r'''    int n, target;
    cin >> n >> target;
    tebak(1, n, target);
    cout << endl;'''), wajib=R,
     id=("Tebak Angka", "Dalam permainan tebak angka, sebuah bilangan rahasia dipilih antara 1 dan n. Penebak yang cerdik selalu menebak **bilangan di tengah** rentang yang masih mungkin: tengah = (bawah + atas) / 2, dengan pembagian bulat.\n\n"
         "- Bila tebakannya tepat, permainan selesai.\n- Bila tebakannya terlalu kecil, rentangnya menjadi tengah + 1 sampai atas.\n- Bila terlalu besar, rentangnya menjadi bawah sampai tengah − 1.\n\n"
         "Tulis fungsi rekursif `void tebak(int bawah, int atas, int target)` yang mencetak setiap tebakan lalu memanggil dirinya untuk rentang yang baru. Cetak semua tebakan dalam satu baris, dipisah spasi.\n\n"
         "Contoh: n = 100 dan bilangan rahasia 37 → `50 25 37`.\n\n**Soal ini mensyaratkan rekursi.**",
         "Satu baris berisi n dan bilangan rahasia.", "Satu baris: semua tebakan, berurutan.", "1 ≤ bilangan rahasia ≤ n ≤ 1000000"),
     en=("Guess the Number", "In a guessing game a secret number is chosen between 1 and n. A clever guesser always guesses the **middle** of the range still possible: middle = (low + high) / 2, with integer division.\n\n"
         "- If the guess is right, the game ends.\n- If the guess is too small, the range becomes middle + 1 to high.\n- If it is too large, the range becomes low to middle − 1.\n\n"
         "Write a recursive function `void tebak(int bawah, int atas, int target)` that prints each guess and then calls itself for the new range. Print all guesses on one line, separated by spaces.\n\n"
         "Example: n = 100 and secret number 37 → `50 25 37`.\n\n**This problem requires recursion.**",
         "One line with n and the secret number.", "One line: all guesses in order.", "1 ≤ secret number ≤ n ≤ 1000000"),
     ar=("خمِّن العدد", "في لعبة التخمين يُختار عدد سري بين 1 و n. المخمِّن الذكي يخمّن دائمًا **منتصف** المجال الذي ما زال ممكنًا: المنتصف = (الأدنى + الأعلى) / 2 بقسمة صحيحة.\n\n"
         "- إذا أصاب التخمين انتهت اللعبة.\n- إذا كان التخمين أصغر صار المجال من المنتصف + 1 إلى الأعلى.\n- إذا كان أكبر صار المجال من الأدنى إلى المنتصف − 1.\n\n"
         "اكتب دالة عودية `void tebak(int bawah, int atas, int target)` تطبع كل تخمين ثم تستدعي نفسها للمجال الجديد. اطبع كل التخمينات في سطر واحد تفصل بينها مسافات.\n\n"
         "مثال: n = 100 والعدد السري 37 ← `50 25 37`.\n\n**هذه المسألة تشترط العودية.**",
         "سطر واحد فيه n والعدد السري.", "سطر واحد: كل التخمينات بالترتيب.", "1 ≤ العدد السري ≤ n ≤ 1000000"))

soal("pemisah-ribuan", "mudah_sedang", "trimmed", "print({2: 0, 3: 1000, 4: 5003020, 5: 2000000000, 6: 999, 7: 1000000}.get(i, r.randint(1, 10 ** " + dua(1, 5, 9) + ")))\n",
     program(r'''
void cetak(int n) {
    if (n < 1000) {
        cout << n;
        return;
    }
    cetak(n / 1000);
    int sisa = n % 1000;
    cout << ".";
    if (sisa < 100) {
        cout << "0";
    }
    if (sisa < 10) {
        cout << "0";
    }
    cout << sisa;
}''', r'''    int n;
    cin >> n;
    cetak(n);
    cout << endl;'''), wajib=R,
     id=("Pemisah Ribuan", "Bilangan besar lebih mudah dibaca bila diberi titik setiap tiga digit dari kanan: 1234567 ditulis `1.234.567`.\n\n"
         "Tulis fungsi rekursif `void cetak(int n)`: bilangan di bawah 1000 dicetak apa adanya; bilangan lain dicetak sebagai bagian depannya (n / 1000, oleh pemanggilan rekursif), lalu titik, lalu tiga digit terakhirnya.\n\n"
         "Hati-hati dengan nol: 5003020 harus menjadi `5.003.020`, bukan `5.3.20`.\n\nContoh lain: 999 → `999`; 1000 → `1.000`.\n\n**Soal ini mensyaratkan rekursi.**",
         "Satu bilangan bulat n.", "n dengan titik pemisah ribuan.", "0 ≤ n ≤ 2000000000"),
     en=("Thousands Separator", "A large number is easier to read with a dot after every three digits from the right, as written in Indonesia: 1234567 becomes `1.234.567`.\n\n"
         "Write a recursive function `void cetak(int n)`: a number below 1000 is printed as it is; any other number is printed as its front part (n / 1000, by the recursive call), then a dot, then its last three digits.\n\n"
         "Mind the zeros: 5003020 must become `5.003.020`, not `5.3.20`.\n\nMore examples: 999 → `999`; 1000 → `1.000`.\n\n**This problem requires recursion.**",
         "One integer n.", "n with dots as thousands separators.", "0 ≤ n ≤ 2000000000"),
     ar=("فاصل الآلاف", "العدد الكبير أسهل قراءةً إذا وُضعت نقطة بعد كل ثلاثة أرقام من اليمين، كما يُكتب في إندونيسيا: 1234567 يصير `1.234.567`.\n\n"
         "اكتب دالة عودية `void cetak(int n)`: العدد الأصغر من 1000 يُطبع كما هو؛ وأي عدد آخر يُطبع جزؤه الأول (n / 1000، بالاستدعاء العودي) ثم نقطة ثم أرقامه الثلاثة الأخيرة.\n\n"
         "انتبه إلى الأصفار: 5003020 يجب أن يصير `5.003.020` لا `5.3.20`.\n\nأمثلة أخرى: 999 ← `999`؛ 1000 ← `1.000`.\n\n**هذه المسألة تشترط العودية.**",
         "عدد صحيح واحد n.", "العدد n بنقاط تفصل الآلاف.", "0 ≤ n ≤ 2000000000"))

# ================================ SEDANG (6) ================================

soal("menara-hanoi", "sedang", "trimmed", "print({2: 1, 3: 10}.get(i, " + dua(2, 5, 9) + "))\n",
     program(r'''
void pindah(int n, char asal, char tujuan, char cadangan) {
    if (n == 0) {
        return;
    }
    pindah(n - 1, asal, cadangan, tujuan);
    cout << "Cakram " << n << ": " << asal << " -> " << tujuan << endl;
    pindah(n - 1, cadangan, tujuan, asal);
}''', r'''    int n;
    cin >> n;
    pindah(n, 'A', 'C', 'B');'''), wajib=R,
     id=("Menara Hanoi", "Tiga tiang bernama A, B, dan C. Di tiang A bertumpuk n cakram bernomor 1 (terkecil, paling atas) sampai n (terbesar, paling bawah). Pindahkan semuanya ke tiang C. Cakram dipindahkan satu per satu, dan cakram besar tidak boleh berada di atas cakram kecil.\n\n"
         "Cara memindahkan n cakram dari tiang asal ke tiang tujuan:\n\n1. pindahkan n − 1 cakram teratas dari asal ke tiang cadangan;\n2. pindahkan cakram n dari asal ke tujuan;\n3. pindahkan n − 1 cakram tadi dari cadangan ke tujuan.\n\n"
         "Tulis fungsi rekursif `void pindah(int n, char asal, char tujuan, char cadangan)` yang mencetak setiap langkah di baris sendiri, berbentuk `Cakram k: X -> Y`.\n\n"
         "Untuk n = 2:\n\n```\nCakram 1: A -> B\nCakram 2: A -> C\nCakram 1: B -> C\n```\n\n**Soal ini mensyaratkan rekursi.**",
         "Satu bilangan bulat n.", "Semua langkah, satu per baris, sesuai urutan cara di atas.", "1 ≤ n ≤ 10"),
     en=("Tower of Hanoi", "Three pegs are named A, B and C. On peg A lie n discs numbered 1 (smallest, on top) to n (largest, at the bottom). Move them all to peg C. Discs are moved one at a time, and a larger disc may never lie on a smaller one.\n\n"
         "How to move n discs from a source peg to a target peg:\n\n1. move the top n − 1 discs from the source to the spare peg;\n2. move disc n from the source to the target;\n3. move the n − 1 discs from the spare peg to the target.\n\n"
         "Write a recursive function `void pindah(int n, char asal, char tujuan, char cadangan)` (source, target, spare) that prints every move on its own line, in the form `Cakram k: X -> Y`.\n\n"
         "For n = 2:\n\n```\nCakram 1: A -> B\nCakram 2: A -> C\nCakram 1: B -> C\n```\n\n**This problem requires recursion.**",
         "One integer n.", "All moves, one per line, in the order of the method above.", "1 ≤ n ≤ 10"),
     ar=("برج هانوي", "ثلاثة أعمدة أسماؤها A و B و C. على العمود A عدد n من الأقراص مرقّمة من 1 (الأصغر، في الأعلى) إلى n (الأكبر، في الأسفل). انقلها كلها إلى العمود C. يُنقل قرص واحد في كل مرة، ولا يجوز أن يعلو قرصٌ أكبر قرصًا أصغر.\n\n"
         "طريقة نقل n قرصًا من عمود المصدر إلى عمود الهدف:\n\n1. انقل n − 1 قرصًا العلوية من المصدر إلى العمود الاحتياطي؛\n2. انقل القرص n من المصدر إلى الهدف؛\n3. انقل الأقراص n − 1 من الاحتياطي إلى الهدف.\n\n"
         "اكتب دالة عودية `void pindah(int n, char asal, char tujuan, char cadangan)` (المصدر، الهدف، الاحتياطي) تطبع كل حركة في سطر على الصورة `Cakram k: X -> Y`.\n\n"
         "عند n = 2:\n\n```\nCakram 1: A -> B\nCakram 2: A -> C\nCakram 1: B -> C\n```\n\n**هذه المسألة تشترط العودية.**",
         "عدد صحيح واحد n.", "كل الحركات، حركة في كل سطر، بترتيب الطريقة أعلاه.", "1 ≤ n ≤ 10"))

soal("pangkat-cepat", "sedang", "trimmed",
     "a, b, m = r.randint(1, 30000), r.randint(1, 10 ** " + dua(1, 4, 9) + "), r.randint(2, 30000)\nif i == 2:\n    b = 0\nif i == 3:\n    a, b, m = 29999, 1000000000, 30000\nif i == 4:\n    a = m * r.randint(1, 3)\nif i == 5:\n    b = 2 ** r.randint(1, 29)\nprint(a, b, m)\n",
     program(r'''
int pangkatMod(int a, int b, int m) {
    if (b == 0) {
        return 1 % m;
    }
    int setengah = pangkatMod(a, b / 2, m);
    int hasil = setengah * setengah % m;
    if (b % 2 == 1) {
        hasil = hasil * (a % m) % m;
    }
    return hasil;
}''', r'''    int a, b, m;
    cin >> a >> b >> m;
    cout << pangkatMod(a, b, m) << endl;'''), wajib=R,
     id=("Pangkat Cepat", "Hitung **sisa pembagian a pangkat b oleh m**. Pangkatnya bisa sampai satu miliar, jadi mengalikan a satu per satu sebanyak b kali terlalu lambat, dan hasil pangkatnya sendiri jauh melampaui batas `int`.\n\n"
         "Dua gagasan menyelesaikannya:\n\n- a pangkat b sama dengan (a pangkat b/2) dikuadratkan — dan dikali a sekali lagi bila b ganjil. Tiap pemanggilan rekursif memotong b menjadi setengahnya, jadi sekitar 30 pemanggilan sudah cukup.\n"
         "- Sisa pembagian boleh diambil setelah setiap perkalian, sehingga bilangannya tidak pernah menjadi besar.\n\n"
         "Tulis fungsi rekursif `int pangkatMod(int a, int b, int m)` dan cetak hasilnya. Hitung pangkat setengahnya **sekali**, simpan di variabel, lalu kuadratkan.\n\n"
         "Contoh: `2 10 1000` → 24 (karena 2¹⁰ = 1024); `7 0 5` → 1; `3 200 13` → 9.\n\n**Soal ini mensyaratkan rekursi.**",
         "Satu baris berisi a, b, dan m.", "Satu bilangan bulat: sisa pembagian a pangkat b oleh m.", "1 ≤ a ≤ 90000; 0 ≤ b ≤ 1000000000; 2 ≤ m ≤ 30000"),
     en=("Fast Power", "Compute **the remainder of a to the power b divided by m**. The exponent can be as large as one billion, so multiplying by a one at a time, b times, is too slow, and the power itself is far beyond the range of `int`.\n\n"
         "Two ideas solve it:\n\n- a to the power b equals (a to the power b/2) squared — times a once more when b is odd. Every recursive call halves b, so about 30 calls are enough.\n"
         "- The remainder may be taken after every multiplication, so the numbers never grow large.\n\n"
         "Write a recursive function `int pangkatMod(int a, int b, int m)` and print the result. Compute the half power **once**, keep it in a variable, then square it.\n\n"
         "Examples: `2 10 1000` → 24 (because 2¹⁰ = 1024); `7 0 5` → 1; `3 200 13` → 9.\n\n**This problem requires recursion.**",
         "One line with a, b and m.", "One integer: the remainder of a to the power b divided by m.", "1 ≤ a ≤ 90000; 0 ≤ b ≤ 1000000000; 2 ≤ m ≤ 30000"),
     ar=("الأُسّ السريع", "احسب **باقي قسمة a أُسّ b على m**. قد يبلغ الأُسّ مليارًا، فالضرب في a مرة مرة b من المرات بطيء جدًّا، والقوة نفسها تتجاوز مجال `int` بكثير.\n\n"
         "فكرتان تحلّان المسألة:\n\n- a أُسّ b يساوي مربع (a أُسّ b/2) — مضروبًا في a مرة أخرى إذا كان b فرديًا. كل استدعاء عودي ينصّف b، فيكفي نحو 30 استدعاءً.\n"
         "- يجوز أخذ الباقي بعد كل عملية ضرب، فلا تكبر الأعداد أبدًا.\n\n"
         "اكتب دالة عودية `int pangkatMod(int a, int b, int m)` واطبع الناتج. احسب القوة النصفية **مرة واحدة** واحفظها في متغير ثم ربِّعها.\n\n"
         "أمثلة: `2 10 1000` ← 24 (لأن 2¹⁰ = 1024)؛ `7 0 5` ← 1؛ `3 200 13` ← 9.\n\n**هذه المسألة تشترط العودية.**",
         "سطر واحد فيه a و b و m.", "عدد صحيح واحد: باقي قسمة a أُسّ b على m.", "1 ≤ a ≤ 90000؛ 0 ≤ b ≤ 1000000000؛ 2 ≤ m ≤ 30000"))

soal("ackermann", "sedang", "trimmed", "m = (i + int(os.environ['SEED'])) % 4\nn = r.randint(0, 7)\nif i == 2:\n    m, n = 3, 7\nif i == 3:\n    m, n = 0, 0\nprint(m, n)\n",
     program(r'''
int ackermann(int m, int n) {
    if (m == 0) {
        return n + 1;
    }
    if (n == 0) {
        return ackermann(m - 1, 1);
    }
    return ackermann(m - 1, ackermann(m, n - 1));
}''', r'''    int m, n;
    cin >> m >> n;
    cout << ackermann(m, n) << endl;'''), wajib=R,
     id=("Fungsi Ackermann", "Fungsi Ackermann adalah contoh klasik fungsi yang definisinya pendek tetapi rekursinya sangat dalam:\n\n"
         "- A(0, n) = n + 1;\n- A(m, 0) = A(m − 1, 1) untuk m lebih dari 0;\n- A(m, n) = A(m − 1, A(m, n − 1)) untuk m dan n lebih dari 0.\n\n"
         "Tulis fungsi rekursif `int ackermann(int m, int n)` persis menurut tiga aturan itu dan cetak A(m, n).\n\n"
         "Contoh: A(1, 2) = 4; A(2, 3) = 9; A(3, 3) = 61.\n\n**Soal ini mensyaratkan rekursi.**",
         "Satu baris berisi m dan n.", "Satu bilangan bulat: A(m, n).", "0 ≤ m ≤ 3; 0 ≤ n ≤ 7"),
     en=("The Ackermann Function", "The Ackermann function is the classic example of a function with a short definition and very deep recursion:\n\n"
         "- A(0, n) = n + 1;\n- A(m, 0) = A(m − 1, 1) for m greater than 0;\n- A(m, n) = A(m − 1, A(m, n − 1)) for m and n greater than 0.\n\n"
         "Write a recursive function `int ackermann(int m, int n)` exactly by those three rules and print A(m, n).\n\n"
         "Examples: A(1, 2) = 4; A(2, 3) = 9; A(3, 3) = 61.\n\n**This problem requires recursion.**",
         "One line with m and n.", "One integer: A(m, n).", "0 ≤ m ≤ 3; 0 ≤ n ≤ 7"),
     ar=("دالة أكرمان", "دالة أكرمان هي المثال المعروف لدالة تعريفها قصير وعوديتها عميقة جدًّا:\n\n"
         "- A(0, n) = n + 1؛\n- A(m, 0) = A(m − 1, 1) عندما يكون m أكبر من 0؛\n- A(m, n) = A(m − 1, A(m, n − 1)) عندما يكون m و n أكبر من 0.\n\n"
         "اكتب دالة عودية `int ackermann(int m, int n)` وفق هذه القواعد الثلاث تمامًا واطبع A(m, n).\n\n"
         "أمثلة: A(1, 2) = 4؛ A(2, 3) = 9؛ A(3, 3) = 61.\n\n**هذه المسألة تشترط العودية.**",
         "سطر واحد فيه m و n.", "عدد صحيح واحد: A(m, n).", "0 ≤ m ≤ 3؛ 0 ≤ n ≤ 7"))

soal("josephus", "sedang", "trimmed",
     "n, k = r.randint(1, 10 ** " + dua(1, 2, 3) + "), r.randint(1, 100)\nif i == 2:\n    n = 1\nif i == 3:\n    n, k = 5000, 100\nif i == 4:\n    k = 1\nif i == 5:\n    n, k = 7, 3\nif i == 6:\n    k = 2\nprint(n, k)\n",
     program(r'''
int selamat(int n, int k) {
    if (n == 1) {
        return 0;
    }
    return (selamat(n - 1, k) + k) % n;
}''', r'''    int n, k;
    cin >> n >> k;
    cout << selamat(n, k) + 1 << endl;'''), wajib=R,
     id=("Lingkaran Josephus", "Sebanyak n orang berdiri melingkar, bernomor 1 sampai n. Mulai dari orang nomor 1, hitungan berjalan searah nomor: setiap orang **ke-k** keluar dari lingkaran, lalu hitungan dilanjutkan dari orang sesudahnya. Ini diulang sampai tersisa satu orang. Siapa dia?\n\n"
         "Contoh: n = 7 dan k = 3. Yang keluar berturut-turut 3, 6, 2, 7, 5, 1; yang tersisa nomor 4.\n\n"
         "Mensimulasikan lingkarannya membutuhkan array. Ada jalan rekursif yang jauh lebih pendek, bila posisi dihitung **mulai dari 0**:\n\n- dengan 1 orang, yang selamat ada di posisi 0;\n- dengan n orang, posisinya adalah (posisi untuk n − 1 orang + k) mod n.\n\n"
         "Tulis fungsi rekursif `int selamat(int n, int k)` yang mengembalikan posisi itu, lalu cetak nomor orangnya (posisi + 1).\n\n**Soal ini mensyaratkan rekursi.**",
         "Satu baris berisi n dan k.", "Satu bilangan bulat: nomor orang yang tersisa.", "1 ≤ n ≤ 5000; 1 ≤ k ≤ 100"),
     en=("The Josephus Circle", "n people stand in a circle, numbered 1 to n. Starting from person 1 the count runs in numbering order: every **k-th** person leaves the circle, and the count goes on from the next person. This repeats until one person is left. Who is it?\n\n"
         "Example: n = 7 and k = 3. Those who leave are, in order, 3, 6, 2, 7, 5, 1; number 4 remains.\n\n"
         "Simulating the circle needs an array. There is a much shorter recursive way when positions are counted **from 0**:\n\n- with 1 person, the survivor is at position 0;\n- with n people, the position is (the position for n − 1 people + k) mod n.\n\n"
         "Write a recursive function `int selamat(int n, int k)` that returns that position, then print the person's number (position + 1).\n\n**This problem requires recursion.**",
         "One line with n and k.", "One integer: the number of the person who remains.", "1 ≤ n ≤ 5000; 1 ≤ k ≤ 100"),
     ar=("دائرة يوسيفوس", "يقف n شخصًا في دائرة، مرقّمين من 1 إلى n. بدءًا من الشخص رقم 1 يجري العدّ بترتيب الأرقام: كل شخص **ترتيبه k** يخرج من الدائرة، ثم يستمر العدّ من الشخص التالي. يتكرر ذلك حتى يبقى شخص واحد. من هو؟\n\n"
         "مثال: n = 7 و k = 3. الخارجون على التوالي 3، 6، 2، 7، 5، 1، ويبقى رقم 4.\n\n"
         "محاكاة الدائرة تحتاج إلى مصفوفة. وهناك طريق عودي أقصر بكثير إذا عُدَّت المواضع **من 0**:\n\n- مع شخص واحد يكون الناجي في الموضع 0؛\n- مع n شخصًا يكون الموضع (موضع الناجي مع n − 1 شخصًا + k) mod n.\n\n"
         "اكتب دالة عودية `int selamat(int n, int k)` تُرجع ذلك الموضع ثم اطبع رقم الشخص (الموضع + 1).\n\n**هذه المسألة تشترط العودية.**",
         "سطر واحد فيه n و k.", "عدد صحيح واحد: رقم الشخص الباقي.", "1 ≤ n ≤ 5000؛ 1 ≤ k ≤ 100"))

soal("partisi", "sedang", "trimmed", "print({2: 1, 3: 60, 4: 5}.get(i, " + dua(2, 30, 59) + "))\n",
     program(r'''
int partisi(int n, int maks) {
    if (n == 0) {
        return 1;
    }
    if (maks == 0) {
        return 0;
    }
    if (maks > n) {
        return partisi(n, n);
    }
    return partisi(n - maks, maks) + partisi(n, maks - 1);
}''', r'''    int n;
    cin >> n;
    cout << partisi(n, n) << endl;'''), wajib=R,
     id=("Partisi Bilangan", "Partisi sebuah bilangan n adalah cara menulis n sebagai jumlah bilangan-bilangan bulat positif, **tanpa memperhatikan urutan**. Bilangan 5 punya 7 partisi:\n\n"
         "5; 4 + 1; 3 + 2; 3 + 1 + 1; 2 + 2 + 1; 2 + 1 + 1 + 1; 1 + 1 + 1 + 1 + 1.\n\n"
         "Supaya urutan tidak terhitung dua kali, hitung partisi yang suku-sukunya **tidak lebih dari suatu batas**. Misalkan P(n, maks) adalah banyak partisi n dengan suku paling besar maks. Setiap partisi seperti itu memakai suku maks paling tidak sekali, atau tidak memakainya sama sekali:\n\n"
         "**P(n, maks) = P(n − maks, maks) + P(n, maks − 1)**\n\n"
         "Kasus dasarnya: P(0, maks) = 1 (tidak ada yang perlu ditulis lagi); P(n, 0) = 0 untuk n lebih dari 0; dan bila maks lebih besar dari n, P(n, maks) = P(n, n).\n\n"
         "Tulis fungsi rekursif `int partisi(int n, int maks)` dan cetak P(n, n).\n\n**Soal ini mensyaratkan rekursi.**",
         "Satu bilangan bulat n.", "Satu bilangan bulat: banyak partisi n.", "1 ≤ n ≤ 60"),
     en=("Partitions of a Number", "A partition of a number n is a way of writing n as a sum of positive integers, **ignoring the order**. The number 5 has 7 partitions:\n\n"
         "5; 4 + 1; 3 + 2; 3 + 1 + 1; 2 + 2 + 1; 2 + 1 + 1 + 1; 1 + 1 + 1 + 1 + 1.\n\n"
         "So that orders are not counted twice, count the partitions whose terms are **no larger than some limit**. Let P(n, maks) be the number of partitions of n with terms of at most maks. Every such partition either uses the term maks at least once, or does not use it at all:\n\n"
         "**P(n, maks) = P(n − maks, maks) + P(n, maks − 1)**\n\n"
         "The base cases: P(0, maks) = 1 (nothing is left to write); P(n, 0) = 0 for n greater than 0; and when maks is larger than n, P(n, maks) = P(n, n).\n\n"
         "Write a recursive function `int partisi(int n, int maks)` and print P(n, n).\n\n**This problem requires recursion.**",
         "One integer n.", "One integer: the number of partitions of n.", "1 ≤ n ≤ 60"),
     ar=("تجزئات عدد", "تجزئة العدد n هي طريقة لكتابة n مجموعَ أعداد صحيحة موجبة **دون اعتبار الترتيب**. للعدد 5 سبع تجزئات:\n\n"
         "5؛ 4 + 1؛ 3 + 2؛ 3 + 1 + 1؛ 2 + 2 + 1؛ 2 + 1 + 1 + 1؛ 1 + 1 + 1 + 1 + 1.\n\n"
         "حتى لا يُحسب الترتيب مرتين، عُدَّ التجزئات التي **لا تزيد حدودها على حدٍّ معيّن**. ليكن P(n, maks) عدد تجزئات n بحدود لا تتجاوز maks. كل تجزئة كهذه إما أن تستعمل الحد maks مرة على الأقل وإما ألّا تستعمله أبدًا:\n\n"
         "**P(n, maks) = P(n − maks, maks) + P(n, maks − 1)**\n\n"
         "الحالات الأساسية: P(0, maks) = 1 (لم يبقَ ما يُكتب)؛ P(n, 0) = 0 عندما يكون n أكبر من 0؛ وإذا كان maks أكبر من n فإن P(n, maks) = P(n, n).\n\n"
         "اكتب دالة عودية `int partisi(int n, int maks)` واطبع P(n, n).\n\n**هذه المسألة تشترط العودية.**",
         "عدد صحيح واحد n.", "عدد صحيح واحد: عدد تجزئات n.", "1 ≤ n ≤ 60"))

soal("catalan", "sedang", "trimmed", "print({2: 0, 3: 15, 4: 1}.get(i, " + dua(2, 8, 14) + "))\n",
     program(r'''
int catalan(int n) {
    if (n == 0) {
        return 1;
    }
    int jumlah = 0;
    for (int k = 0; k < n; k++) {
        jumlah += catalan(k) * catalan(n - 1 - k);
    }
    return jumlah;
}''', r'''    int n;
    cin >> n;
    cout << catalan(n) << endl;'''), wajib=R,
     id=("Bilangan Catalan", "Sebuah segi banyak cembung dengan n + 2 sisi dapat dipotong menjadi segitiga-segitiga dengan menarik diagonal yang tidak saling berpotongan. Banyak cara memotongnya adalah bilangan Catalan ke-n, C(n).\n\n"
         "Segi empat (n = 2) bisa dipotong dengan 2 cara; segi lima (n = 3) dengan 5 cara; segi enam (n = 4) dengan 14 cara.\n\n"
         "Bilangan Catalan memenuhi C(0) = 1 dan\n\n**C(n) = C(0) × C(n − 1) + C(1) × C(n − 2) + … + C(n − 1) × C(0)**\n\n"
         "Tulis fungsi rekursif `int catalan(int n)` yang menghitung jumlah itu dengan sebuah perulangan yang memanggil dirinya sendiri di dalamnya, lalu cetak C(n).\n\n**Soal ini mensyaratkan rekursi.**",
         "Satu bilangan bulat n.", "Satu bilangan bulat: C(n).", "0 ≤ n ≤ 15"),
     en=("Catalan Numbers", "A convex polygon with n + 2 sides can be cut into triangles by drawing diagonals that do not cross. The number of ways to do so is the n-th Catalan number, C(n).\n\n"
         "A quadrilateral (n = 2) can be cut in 2 ways; a pentagon (n = 3) in 5 ways; a hexagon (n = 4) in 14 ways.\n\n"
         "The Catalan numbers satisfy C(0) = 1 and\n\n**C(n) = C(0) × C(n − 1) + C(1) × C(n − 2) + … + C(n − 1) × C(0)**\n\n"
         "Write a recursive function `int catalan(int n)` that computes that sum with a loop calling the function itself inside, then print C(n).\n\n**This problem requires recursion.**",
         "One integer n.", "One integer: C(n).", "0 ≤ n ≤ 15"),
     ar=("أعداد كاتالان", "مضلع محدب له n + 2 ضلعًا يمكن تقطيعه إلى مثلثات برسم أقطار لا تتقاطع. عدد طرق التقطيع هو عدد كاتالان رقم n، ويُكتب C(n).\n\n"
         "الرباعي (n = 2) يُقطَّع بطريقتين؛ والخماسي (n = 3) بخمس طرق؛ والسداسي (n = 4) بأربع عشرة طريقة.\n\n"
         "أعداد كاتالان تحقق C(0) = 1 و\n\n**C(n) = C(0) × C(n − 1) + C(1) × C(n − 2) + … + C(n − 1) × C(0)**\n\n"
         "اكتب دالة عودية `int catalan(int n)` تحسب هذا المجموع بحلقة تستدعي الدالة نفسها بداخلها ثم اطبع C(n).\n\n**هذه المسألة تشترط العودية.**",
         "عدد صحيح واحد n.", "عدد صحيح واحد: C(n).", "0 ≤ n ≤ 15"))

# ================================= SULIT (4) =================================

soal("permutasi-huruf", "sulit", "trimmed",
     "n = {2: 1, 3: 6}.get(i, " + dua(2, 3, 5) + ")\nh = r.sample('abcdefghijklmnopqrstuvwxyz', n)\nif i == 4:\n    h.sort()\nif i == 5:\n    h.sort(reverse=True)\nprint(''.join(h))\n",
     program(r'''
void susun(string awalan, string sisa) {
    if (sisa.size() == 0) {
        cout << awalan << endl;
        return;
    }
    for (char c = 'a'; c <= 'z'; c++) {
        int posisi = -1;
        for (int k = 0; k < (int) sisa.size(); k++) {
            if (sisa[k] == c) {
                posisi = k;
            }
        }
        if (posisi >= 0) {
            susun(awalan + c, sisa.substr(0, posisi) + sisa.substr(posisi + 1));
        }
    }
}''', r'''    string kata;
    cin >> kata;
    susun("", kata);''', "string"), wajib=R,
     id=("Semua Susunan Huruf", "Baca sebuah kata yang semua hurufnya berbeda, lalu cetak **semua susunan** huruf-hurufnya, satu per baris, **dalam urutan kamus**.\n\n"
         "Untuk `cab` keluarannya:\n\n```\nabc\nacb\nbac\nbca\ncab\ncba\n```\n\n"
         "Gagasan rekursifnya: sebuah susunan terdiri dari satu huruf pilihan, diikuti susunan dari huruf-huruf sisanya. Tulis `void susun(string awalan, string sisa)`: bila `sisa` kosong, `awalan` sudah lengkap dan dicetak; "
         "bila tidak, untuk setiap huruf di `sisa` — **dicoba menurut abjad** — panggil `susun` dengan huruf itu ditambahkan ke `awalan` dan dibuang dari `sisa`.\n\n"
         "Kata dengan 6 huruf punya 720 susunan.\n\n**Soal ini mensyaratkan rekursi.**",
         "Satu kata dari huruf kecil yang semuanya berbeda; urutannya bisa acak.", "Semua susunan hurufnya, satu per baris, urut kamus.", "Panjang kata 1 sampai 6 huruf."),
     en=("All Arrangements of Letters", "Read a word whose letters are all different, then print **every arrangement** of its letters, one per line, **in dictionary order**.\n\n"
         "For `cab` the output is:\n\n```\nabc\nacb\nbac\nbca\ncab\ncba\n```\n\n"
         "The recursive idea: an arrangement is one chosen letter followed by an arrangement of the remaining letters. Write `void susun(string awalan, string sisa)` (prefix, rest): when `sisa` is empty, `awalan` is complete and is printed; "
         "otherwise, for each letter in `sisa` — **tried in alphabetical order** — call `susun` with that letter added to `awalan` and removed from `sisa`.\n\n"
         "A word of 6 letters has 720 arrangements.\n\n**This problem requires recursion.**",
         "One word of lowercase letters, all different; their order may be arbitrary.", "Every arrangement of the letters, one per line, in dictionary order.", "The word is 1 to 6 letters long."),
     ar=("كل ترتيبات الحروف", "اقرأ كلمة حروفها كلها مختلفة ثم اطبع **كل ترتيبات** حروفها، ترتيبًا في كل سطر، **بترتيب المعجم**.\n\n"
         "للكلمة `cab` يكون المخرج:\n\n```\nabc\nacb\nbac\nbca\ncab\ncba\n```\n\n"
         "الفكرة العودية: الترتيب حرف مختار يليه ترتيب للحروف الباقية. اكتب `void susun(string awalan, string sisa)` (البادئة، الباقي): إذا كان `sisa` فارغًا فقد اكتملت `awalan` وتُطبع؛ "
         "وإلا فلكل حرف في `sisa` — **مجرَّبًا بالترتيب الأبجدي** — استدعِ `susun` بعد إضافة ذلك الحرف إلى `awalan` وحذفه من `sisa`.\n\n"
         "للكلمة ذات 6 حروف 720 ترتيبًا.\n\n**هذه المسألة تشترط العودية.**",
         "كلمة واحدة من حروف لاتينية صغيرة كلها مختلفة، وقد يكون ترتيبها عشوائيًا.", "كل ترتيبات الحروف، ترتيب في كل سطر، بترتيب المعجم.", "طول الكلمة من 1 إلى 6 حروف."))

soal("kurung-seimbang", "sulit", "trimmed", "print({2: 1, 3: 7}.get(i, " + dua(2, 4, 6) + "))\n",
     program(r'''
void buat(string sejauhIni, int buka, int tutup, int n) {
    if (buka == n && tutup == n) {
        cout << sejauhIni << endl;
        return;
    }
    if (buka < n) {
        buat(sejauhIni + "(", buka + 1, tutup, n);
    }
    if (tutup < buka) {
        buat(sejauhIni + ")", buka, tutup + 1, n);
    }
}''', r'''    int n;
    cin >> n;
    buat("", 0, 0, n);''', "string"), wajib=R,
     id=("Kurung Seimbang", "Rangkaian tanda kurung disebut seimbang bila setiap kurung buka punya kurung tutup pasangannya, dan tidak ada kurung tutup yang muncul sebelum pasangannya dibuka. `(())` dan `()()` seimbang; `())(` tidak.\n\n"
         "Baca n, lalu cetak **semua** rangkaian seimbang yang terdiri dari n pasang kurung, satu per baris. Urutannya: di setiap posisi, rangkaian dengan `(` dicetak lebih dulu daripada rangkaian dengan `)`.\n\n"
         "Untuk n = 3:\n\n```\n((()))\n(()())\n(())()\n()(())\n()()()\n```\n\n"
         "Bangun rangkaiannya karakter demi karakter dengan fungsi rekursif yang mengingat berapa kurung buka dan kurung tutup yang sudah dipakai. Kurung buka boleh ditambahkan selama belum ada n; kurung tutup boleh ditambahkan selama banyaknya masih kurang dari kurung buka.\n\n"
         "**Soal ini mensyaratkan rekursi.**",
         "Satu bilangan bulat n.", "Semua rangkaian seimbang dengan n pasang kurung, satu per baris, sesuai urutan di atas.", "1 ≤ n ≤ 7"),
     en=("Balanced Brackets", "A string of brackets is balanced when every opening bracket has its closing partner, and no closing bracket appears before its partner was opened. `(())` and `()()` are balanced; `())(` is not.\n\n"
         "Read n, then print **all** balanced strings made of n pairs of brackets, one per line. The order: at every position, strings with `(` are printed before strings with `)`.\n\n"
         "For n = 3:\n\n```\n((()))\n(()())\n(())()\n()(())\n()()()\n```\n\n"
         "Build the strings character by character with a recursive function that remembers how many opening and closing brackets have been used. An opening bracket may be added while there are fewer than n; a closing bracket may be added while there are fewer of them than opening ones.\n\n"
         "**This problem requires recursion.**",
         "One integer n.", "All balanced strings of n pairs of brackets, one per line, in the order above.", "1 ≤ n ≤ 7"),
     ar=("الأقواس المتوازنة", "تكون سلسلة الأقواس متوازنة إذا كان لكل قوس فتح قوسُ إغلاق يقابله، ولم يظهر قوس إغلاق قبل فتح مقابله. `(())` و `()()` متوازنتان، أما `())(` فلا.\n\n"
         "اقرأ n ثم اطبع **كل** السلاسل المتوازنة المكوّنة من n زوجًا من الأقواس، سلسلة في كل سطر. الترتيب: في كل موضع تُطبع السلاسل التي فيها `(` قبل السلاسل التي فيها `)`.\n\n"
         "عند n = 3:\n\n```\n((()))\n(()())\n(())()\n()(())\n()()()\n```\n\n"
         "ابنِ السلاسل محرفًا محرفًا بدالة عودية تتذكر عدد أقواس الفتح والإغلاق المستعملة. يجوز إضافة قوس فتح ما دام عددها أقل من n، ويجوز إضافة قوس إغلاق ما دام عددها أقل من عدد أقواس الفتح.\n\n"
         "**هذه المسألة تشترط العودية.**",
         "عدد صحيح واحد n.", "كل السلاسل المتوازنة من n زوجًا من الأقواس، سلسلة في كل سطر، بالترتيب المذكور.", "1 ≤ n ≤ 7"))

soal("himpunan-cantor", "sulit", "trimmed", "print({2: 0, 3: 5, 4: 1}.get(i, " + dua(2, 3, 4) + "))\n",
     program(r'''
void baris(int panjang, int tingkat) {
    if (tingkat == 0) {
        for (int k = 0; k < panjang; k++) {
            cout << "#";
        }
        return;
    }
    baris(panjang / 3, tingkat - 1);
    for (int k = 0; k < panjang / 3; k++) {
        cout << " ";
    }
    baris(panjang / 3, tingkat - 1);
}''', r'''    int n;
    cin >> n;
    int panjang = 1;
    for (int k = 0; k < n; k++) {
        panjang *= 3;
    }
    for (int tingkat = 0; tingkat <= n; tingkat++) {
        baris(panjang, tingkat);
        cout << endl;
    }'''), wajib=R,
     id=("Himpunan Cantor", "Mulai dari sebuah ruas garis. Buang **sepertiga bagian tengahnya**, sehingga tersisa dua ruas. Lakukan hal yang sama pada tiap ruas yang tersisa, dan seterusnya. Gambar yang muncul disebut himpunan Cantor.\n\n"
         "Baca n. Cetak n + 1 baris selebar 3ⁿ karakter: baris pertama berisi ruas utuh dari tanda `#`; setiap baris berikutnya adalah baris sebelumnya dengan sepertiga tengah tiap ruasnya diganti spasi.\n\n"
         "Untuk n = 2:\n\n```\n#########\n###   ###\n# #   # #\n```\n\n"
         "Tulis fungsi rekursif `void baris(int panjang, int tingkat)` yang mencetak **satu** baris: pada tingkat 0 ia mencetak ruas utuh; pada tingkat lain ia mencetak baris tingkat di bawahnya untuk sepertiga panjang, lalu spasi sepanjang sepertiga, lalu baris yang sama sekali lagi.\n\n"
         "Spasi di ujung baris tidak perlu dicetak; untuk n = 0 keluarannya satu `#`.\n\n**Soal ini mensyaratkan rekursi.**",
         "Satu bilangan bulat n.", "n + 1 baris gambar himpunan Cantor.", "0 ≤ n ≤ 5"),
     en=("The Cantor Set", "Start with a line segment. Remove its **middle third**, so that two segments remain. Do the same to every remaining segment, and so on. The picture that appears is called the Cantor set.\n\n"
         "Read n. Print n + 1 lines 3ⁿ characters wide: the first line is a whole segment of `#`; every next line is the previous one with the middle third of each segment replaced by spaces.\n\n"
         "For n = 2:\n\n```\n#########\n###   ###\n# #   # #\n```\n\n"
         "Write a recursive function `void baris(int panjang, int tingkat)` (length, level) that prints **one** line: at level 0 it prints a whole segment; at any other level it prints the line of the level below for a third of the length, then spaces for a third, then the same line once more.\n\n"
         "Spaces at the end of a line need not be printed; for n = 0 the output is a single `#`.\n\n**This problem requires recursion.**",
         "One integer n.", "The n + 1 lines of the Cantor set picture.", "0 ≤ n ≤ 5"),
     ar=("مجموعة كانتور", "ابدأ بقطعة مستقيمة. احذف **ثلثها الأوسط** فتبقى قطعتان. افعل الشيء نفسه بكل قطعة باقية، وهكذا. الشكل الناتج يسمى مجموعة كانتور.\n\n"
         "اقرأ n. اطبع n + 1 سطرًا عرض كل منها 3ⁿ محرفًا: السطر الأول قطعة كاملة من `#`؛ وكل سطر تالٍ هو السطر السابق بعد استبدال مسافات بالثلث الأوسط من كل قطعة فيه.\n\n"
         "عند n = 2:\n\n```\n#########\n###   ###\n# #   # #\n```\n\n"
         "اكتب دالة عودية `void baris(int panjang, int tingkat)` (الطول، المستوى) تطبع سطرًا **واحدًا**: في المستوى 0 تطبع قطعة كاملة؛ وفي أي مستوى آخر تطبع سطر المستوى الأدنى لثلث الطول، ثم مسافات بطول الثلث، ثم السطر نفسه مرة أخرى.\n\n"
         "لا حاجة إلى طباعة المسافات في آخر السطر؛ وعند n = 0 يكون المخرج `#` واحدة.\n\n**هذه المسألة تشترط العودية.**",
         "عدد صحيح واحد n.", "n + 1 سطرًا لشكل مجموعة كانتور.", "0 ≤ n ≤ 5"))

soal("kalkulator-rekursif", "sulit", "trimmed",
     "def e(d):\n    if d == 0 or r.random() < 0.25:\n        return str(r.randint(0, 9))\n    if r.random() < 0.3:\n        return '(' + e(d - 1) + ')'\n    return e(d - 1) + r.choice('+-*') + e(d - 1)\n"
     "t = e(3)\nif i == 2:\n    t = str(r.randint(0, 9))\nif i == 3:\n    t = '((' + e(2) + '))*' + e(1)\nif i == 4:\n    t = e(2) + '-' + e(1) + '-' + e(1)\nif i == 5:\n    t = e(1) + '-(' + e(2) + ')'\nprint(t)\n",
     program(r'''
int ekspresi(const string &s, int &posisi);

int faktor(const string &s, int &posisi) {
    if (s[posisi] == '(') {
        posisi++;
        int nilai = ekspresi(s, posisi);
        posisi++;
        return nilai;
    }
    int nilai = 0;
    while (posisi < (int) s.size() && s[posisi] >= '0' && s[posisi] <= '9') {
        nilai = nilai * 10 + (s[posisi] - '0');
        posisi++;
    }
    return nilai;
}

int suku(const string &s, int &posisi) {
    int nilai = faktor(s, posisi);
    while (posisi < (int) s.size() && s[posisi] == '*') {
        posisi++;
        nilai = nilai * faktor(s, posisi);
    }
    return nilai;
}

int ekspresi(const string &s, int &posisi) {
    int nilai = suku(s, posisi);
    while (posisi < (int) s.size() && (s[posisi] == '+' || s[posisi] == '-')) {
        char op = s[posisi];
        posisi++;
        int kanan = suku(s, posisi);
        if (op == '+') {
            nilai = nilai + kanan;
        } else {
            nilai = nilai - kanan;
        }
    }
    return nilai;
}''', r'''    string s;
    cin >> s;
    int posisi = 0;
    cout << ekspresi(s, posisi) << endl;''', "string"), wajib=R,
     id=("Kalkulator dengan Kurung", "Baca sebuah hitungan seperti `2*(3+4)-5` dan cetak hasilnya. Hitungan itu berisi bilangan bulat tak negatif, operator `+`, `-`, `*`, dan tanda kurung, tanpa spasi.\n\n"
         "Aturannya seperti di matematika: perkalian dikerjakan sebelum penjumlahan dan pengurangan; operator yang setingkat dikerjakan dari kiri ke kanan; isi kurung dikerjakan lebih dulu.\n\n"
         "Contoh: `2+3*4` → 14; `(2+3)*4` → 20; `9-4-3` → 2; `2*(3+(4-1))*5` → 60.\n\n"
         "Hitungan seperti ini punya susunan yang rekursif:\n\n- sebuah **ekspresi** adalah satu atau beberapa *suku* yang dipisah `+` atau `-`;\n- sebuah **suku** adalah satu atau beberapa *faktor* yang dipisah `*`;\n- sebuah **faktor** adalah sebuah bilangan, atau `(`, sebuah *ekspresi*, lalu `)`.\n\n"
         "Tulis satu fungsi untuk tiap bagian itu. Ketiganya membaca teks yang sama dan berbagi satu penunjuk posisi, misalnya `int ekspresi(const string &s, int &posisi)`. Fungsi `faktor` memanggil `ekspresi`, yang memanggil `suku`, yang memanggil `faktor` lagi: rekursi lewat fungsi lain. "
         "Karena saling memanggil, salah satu fungsi perlu dideklarasikan lebih dulu sebelum didefinisikan.\n\n**Soal ini mensyaratkan rekursi.**",
         "Satu baris: hitungan tanpa spasi.", "Satu bilangan bulat: hasilnya.", "Panjang hitungan paling banyak 60 karakter; tiap bilangan di dalamnya satu digit (0 sampai 9); hasil dan semua hasil antara muat dalam `int`."),
     en=("Calculator with Brackets", "Read a calculation such as `2*(3+4)-5` and print its result. The calculation holds non-negative integers, the operators `+`, `-`, `*`, and brackets, without spaces.\n\n"
         "The rules are those of mathematics: multiplication comes before addition and subtraction; operators of the same level are applied from left to right; the contents of brackets come first.\n\n"
         "Examples: `2+3*4` → 14; `(2+3)*4` → 20; `9-4-3` → 2; `2*(3+(4-1))*5` → 60.\n\n"
         "Such a calculation has a recursive structure:\n\n- an **expression** (`ekspresi`) is one or more *terms* separated by `+` or `-`;\n- a **term** (`suku`) is one or more *factors* separated by `*`;\n- a **factor** (`faktor`) is a number, or `(`, an *expression*, then `)`.\n\n"
         "Write one function for each of those parts. All three read the same text and share one position marker, for example `int ekspresi(const string &s, int &posisi)`. The function `faktor` calls `ekspresi`, which calls `suku`, which calls `faktor` again: recursion through other functions. "
         "Because they call each other, one of them has to be declared before it is defined.\n\n**This problem requires recursion.**",
         "One line: the calculation, without spaces.", "One integer: the result.", "The calculation is at most 60 characters long; every number in it is a single digit (0 to 9); the result and all intermediate results fit in `int`."),
     ar=("آلة حاسبة بأقواس", "اقرأ عملية حسابية مثل `2*(3+4)-5` واطبع ناتجها. في العملية أعداد صحيحة غير سالبة والعوامل `+` و `-` و `*` وأقواس، بلا مسافات.\n\n"
         "القواعد قواعد الرياضيات: الضرب قبل الجمع والطرح؛ والعوامل ذات المرتبة الواحدة تُنفَّذ من اليسار إلى اليمين؛ وما بين القوسين يُحسب أولًا.\n\n"
         "أمثلة: `2+3*4` ← 14؛ `(2+3)*4` ← 20؛ `9-4-3` ← 2؛ `2*(3+(4-1))*5` ← 60.\n\n"
         "لعملية كهذه بنية عودية:\n\n- **التعبير** (`ekspresi`) حدٌّ واحد أو عدة *حدود* يفصل بينها `+` أو `-`؛\n- **الحد** (`suku`) عامل واحد أو عدة *عوامل* يفصل بينها `*`؛\n- **العامل** (`faktor`) عدد، أو `(` ثم *تعبير* ثم `)`.\n\n"
         "اكتب دالة لكل جزء من هذه الأجزاء. الدوال الثلاث تقرأ النص نفسه وتتشارك مؤشر موضع واحدًا، مثل `int ekspresi(const string &s, int &posisi)`. الدالة `faktor` تستدعي `ekspresi` التي تستدعي `suku` التي تستدعي `faktor` من جديد: عودية عبر دوال أخرى. "
         "ولأنها يستدعي بعضها بعضًا فلا بد من التصريح بإحداها قبل تعريفها.\n\n**هذه المسألة تشترط العودية.**",
         "سطر واحد: العملية بلا مسافات.", "عدد صحيح واحد: الناتج.", "طول العملية 60 محرفًا على الأكثر؛ كل عدد فيها رقم واحد (من 0 إلى 9)؛ الناتج وكل النواتج الوسيطة يسعها `int`."))

tulis(__file__, "DDP101", 7, "cpp", {"mudah_sekali": 8, "mudah": 8, "mudah_sedang": 6, "sedang": 6, "sulit": 4})
