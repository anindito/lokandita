#!/usr/bin/env python3
"""Menyusun docs/soal/ddp101-pertemuan-1.json.

DDP101 pertemuan 1 — Pengenalan Pemrograman dan C++. Konsep yang diizinkan:
IO_DASAR, VARIABEL_TIPE, ARITMETIKA. Solusi referensi TIDAK memakai cast, if,
perulangan, string, atau pengaturan format keluaran. 32 soal: 8 / 8 / 6 / 6 / 4.

    python3 docs/soal/buat-ddp101-pertemuan-1.py
"""
from bangun import cpp, soal, tulis

# ============================== MUDAH SEKALI (8) ==============================

soal("cetak-bilangan", "mudah_sekali", "trimmed", "print(r.randint(1, 1000000))\n",
     cpp("    int n;\n    cin >> n;\n    cout << n << endl;"),
     id=("Cetak Bilangan", "Baca sebuah bilangan bulat, lalu cetak bilangan itu kembali.\n\nIni program pertama yang membaca masukan: `cin >> n;` membaca, `cout << n;` mencetak.",
         "Satu bilangan bulat n.", "Bilangan n.", "1 ≤ n ≤ 1000000"),
     en=("Print the Number", "Read an integer, then print it back.\n\nThis is the first program that reads input: `cin >> n;` reads, `cout << n;` prints.",
         "One integer n.", "The number n.", "1 ≤ n ≤ 1000000"),
     ar=("اطبع العدد", "اقرأ عددًا صحيحًا ثم اطبعه كما هو.\n\nهذا أول برنامج يقرأ مدخلات: `cin >> n;` للقراءة و `cout << n;` للطباعة.",
         "عدد صحيح واحد n.", "العدد n.", "1 ≤ n ≤ 1000000"))

soal("bilangan-berikutnya", "mudah_sekali", "trimmed", "print(r.randint(0, 1000000))\n",
     cpp("    int n;\n    cin >> n;\n    cout << n + 1 << endl;"),
     id=("Bilangan Berikutnya", "Baca sebuah bilangan bulat **n**, lalu cetak bilangan sesudahnya, yaitu **n + 1**.",
         "Satu bilangan bulat n.", "Satu bilangan: n + 1.", "0 ≤ n ≤ 1000000"),
     en=("The Next Number", "Read an integer **n**, then print the number after it, **n + 1**.",
         "One integer n.", "One number: n + 1.", "0 ≤ n ≤ 1000000"),
     ar=("العدد التالي", "اقرأ عددًا صحيحًا **n** ثم اطبع العدد الذي يليه، أي **n + 1**.",
         "عدد صحيح واحد n.", "عدد واحد: n + 1.", "0 ≤ n ≤ 1000000"))

soal("dua-kali-lipat", "mudah_sekali", "trimmed", "print(r.randint(1, 1000000))\n",
     cpp("    int n;\n    cin >> n;\n    cout << n * 2 << endl;"),
     id=("Dua Kali Lipat", "Baca sebuah bilangan bulat **n**, lalu cetak dua kali lipatnya.",
         "Satu bilangan bulat n.", "Satu bilangan: 2 × n.", "1 ≤ n ≤ 1000000"),
     en=("Double It", "Read an integer **n**, then print twice its value.",
         "One integer n.", "One number: 2 × n.", "1 ≤ n ≤ 1000000"),
     ar=("الضعف", "اقرأ عددًا صحيحًا **n** ثم اطبع ضعفه.",
         "عدد صحيح واحد n.", "عدد واحد: 2 × n.", "1 ≤ n ≤ 1000000"))

soal("jumlah-dua-bilangan", "mudah_sekali", "trimmed", "print(r.randint(1, 1000), r.randint(1, 1000))\n",
     cpp("    int a, b;\n    cin >> a >> b;\n    cout << a + b << endl;"),
     id=("Jumlah Dua Bilangan", "Baca dua bilangan bulat, lalu cetak jumlahnya.",
         "Satu baris berisi dua bilangan bulat a dan b.", "Satu bilangan: a + b.", "1 ≤ a, b ≤ 1000"),
     en=("Sum of Two Numbers", "Read two integers, then print their sum.",
         "One line with two integers a and b.", "One number: a + b.", "1 ≤ a, b ≤ 1000"),
     ar=("مجموع عددين", "اقرأ عددين صحيحين ثم اطبع مجموعهما.",
         "سطر واحد فيه عددان صحيحان a و b.", "عدد واحد: a + b.", "1 ≤ a, b ≤ 1000"))

soal("jumlah-tiga-bilangan", "mudah_sekali", "trimmed", "print(r.randint(1, 1000), r.randint(1, 1000), r.randint(1, 1000))\n",
     cpp("    int a, b, c;\n    cin >> a >> b >> c;\n    cout << a + b + c << endl;"),
     id=("Jumlah Tiga Bilangan", "Baca tiga bilangan bulat, lalu cetak jumlah ketiganya.",
         "Satu baris berisi tiga bilangan bulat.", "Satu bilangan: jumlah ketiganya.", "1 ≤ setiap bilangan ≤ 1000"),
     en=("Sum of Three Numbers", "Read three integers, then print the sum of all three.",
         "One line with three integers.", "One number: their sum.", "1 ≤ each number ≤ 1000"),
     ar=("مجموع ثلاثة أعداد", "اقرأ ثلاثة أعداد صحيحة ثم اطبع مجموعها.",
         "سطر واحد فيه ثلاثة أعداد صحيحة.", "عدد واحد: مجموعها.", "1 ≤ كل عدد ≤ 1000"))

soal("keliling-persegi", "mudah_sekali", "trimmed", "print(r.randint(1, 10000))\n",
     cpp("    int sisi;\n    cin >> sisi;\n    cout << 4 * sisi << endl;"),
     id=("Keliling Persegi", "Sebuah persegi punya panjang sisi **s**. Cetak kelilingnya.",
         "Satu bilangan bulat s.", "Satu bilangan: keliling persegi.", "1 ≤ s ≤ 10000"),
     en=("Perimeter of a Square", "A square has side length **s**. Print its perimeter.",
         "One integer s.", "One number: the perimeter of the square.", "1 ≤ s ≤ 10000"),
     ar=("محيط المربع", "مربع طول ضلعه **s**. اطبع محيطه.",
         "عدد صحيح واحد s.", "عدد واحد: محيط المربع.", "1 ≤ s ≤ 10000"))

soal("luas-persegi", "mudah_sekali", "trimmed", "print(r.randint(1, 10000))\n",
     cpp("    int sisi;\n    cin >> sisi;\n    cout << sisi * sisi << endl;"),
     id=("Luas Persegi", "Sebuah persegi punya panjang sisi **s**. Cetak luasnya.",
         "Satu bilangan bulat s.", "Satu bilangan: luas persegi.", "1 ≤ s ≤ 10000"),
     en=("Area of a Square", "A square has side length **s**. Print its area.",
         "One integer s.", "One number: the area of the square.", "1 ≤ s ≤ 10000"),
     ar=("مساحة المربع", "مربع طول ضلعه **s**. اطبع مساحته.",
         "عدد صحيح واحد s.", "عدد واحد: مساحة المربع.", "1 ≤ s ≤ 10000"))

soal("urutan-terbalik", "mudah_sekali", "token", "print(r.randint(1, 1000), r.randint(1, 1000), r.randint(1, 1000))\n",
     cpp("    int a, b, c;\n    cin >> a >> b >> c;\n    cout << c << \" \" << b << \" \" << a << endl;"),
     id=("Urutan Terbalik", "Baca tiga bilangan bulat, lalu cetak ketiganya dengan urutan terbalik: yang terakhir dibaca dicetak lebih dulu.",
         "Satu baris berisi tiga bilangan bulat a, b, c.", "Satu baris: c, b, a, dipisah spasi.", "1 ≤ a, b, c ≤ 1000"),
     en=("Reverse Order", "Read three integers, then print them in reverse order: the last one read is printed first.",
         "One line with three integers a, b, c.", "One line: c, b, a, separated by spaces.", "1 ≤ a, b, c ≤ 1000"),
     ar=("ترتيب معكوس", "اقرأ ثلاثة أعداد صحيحة ثم اطبعها بترتيب معكوس: آخر عدد قُرئ يُطبع أولًا.",
         "سطر واحد فيه ثلاثة أعداد صحيحة a و b و c.", "سطر واحد: c ثم b ثم a، تفصل بينها مسافات.", "1 ≤ a, b, c ≤ 1000"))

# ================================= MUDAH (8) =================================

soal("umur", "mudah", "trimmed", "lahir = r.randint(1950, 2010)\nprint(lahir, r.randint(lahir, 2030))\n",
     cpp("    int lahir, sekarang;\n    cin >> lahir >> sekarang;\n    cout << sekarang - lahir << endl;"),
     id=("Umur", "Seseorang lahir pada tahun **l**. Berapa umurnya pada tahun **t**? (Anggap ia sudah berulang tahun pada tahun itu.)",
         "Satu baris berisi tahun lahir l dan tahun t.", "Satu bilangan: umur pada tahun t.", "1950 ≤ l ≤ t ≤ 2030"),
     en=("Age", "A person was born in year **l**. How old are they in year **t**? (Assume the birthday has already passed that year.)",
         "One line with the birth year l and the year t.", "One number: the age in year t.", "1950 ≤ l ≤ t ≤ 2030"),
     ar=("العمر", "وُلد شخص في السنة **l**. كم عمره في السنة **t**؟ (افترض أن عيد ميلاده قد مرّ في تلك السنة.)",
         "سطر واحد فيه سنة الميلاد l والسنة t.", "عدد واحد: العمر في السنة t.", "1950 ≤ l ≤ t ≤ 2030"))

soal("sisa-uang-saku", "mudah", "trimmed", "h, n = r.randint(1, 20) * 500, r.randint(1, 10)\nprint(h * n + r.randint(0, 100) * 500, h, n)\n",
     cpp("    int uang, harga, banyak;\n    cin >> uang >> harga >> banyak;\n    cout << uang - harga * banyak << endl;"),
     id=("Sisa Uang Saku", "Seorang siswa punya uang **u** rupiah. Ia membeli **n** buku tulis yang masing-masing berharga **h** rupiah. Cetak sisa uangnya.",
         "Satu baris berisi tiga bilangan bulat: u, h, n.", "Satu bilangan: sisa uang.", "Uangnya selalu cukup. 500 ≤ h ≤ 10000, 1 ≤ n ≤ 10."),
     en=("Pocket Money Left", "A pupil has **u** rupiah. They buy **n** notebooks that cost **h** rupiah each. Print the money left.",
         "One line with three integers: u, h, n.", "One number: the money left.", "The money is always enough. 500 ≤ h ≤ 10000, 1 ≤ n ≤ 10."),
     ar=("المصروف المتبقي", "مع تلميذ **u** روبية. اشترى **n** دفاتر سعر كل واحد منها **h** روبية. اطبع ما تبقى معه.",
         "سطر واحد فيه ثلاثة أعداد صحيحة: u و h و n.", "عدد واحد: المبلغ المتبقي.", "المبلغ يكفي دائمًا. 500 ≤ h ≤ 10000، 1 ≤ n ≤ 10."))

soal("rata-rata-dua-pecahan", "mudah", "float", "print(r.randint(0, 2000) / 2, r.randint(0, 2000) / 2)\n",
     cpp("    double x, y;\n    cin >> x >> y;\n    cout << (x + y) / 2 << endl;"),
     id=("Rata-Rata Dua Bilangan Pecahan", "Baca dua bilangan pecahan, lalu cetak rata-ratanya.\n\nBilangan pecahan disimpan di variabel bertipe `double`.",
         "Satu baris berisi dua bilangan pecahan.", "Satu bilangan: rata-rata keduanya.", "0 ≤ kedua bilangan ≤ 1000"),
     en=("Average of Two Real Numbers", "Read two real numbers, then print their average.\n\nReal numbers are stored in variables of type `double`.",
         "One line with two real numbers.", "One number: their average.", "0 ≤ both numbers ≤ 1000"),
     ar=("متوسط عددين عشريين", "اقرأ عددين عشريين ثم اطبع متوسطهما.\n\nتُخزَّن الأعداد العشرية في متغيرات من النوع `double`.",
         "سطر واحد فيه عددان عشريان.", "عدد واحد: متوسطهما.", "0 ≤ العددان ≤ 1000"))

soal("jam-menit-ke-detik", "mudah", "trimmed", "print(r.randint(0, 23), r.randint(0, 59))\n",
     cpp("    int jam, menit;\n    cin >> jam >> menit;\n    cout << jam * 3600 + menit * 60 << endl;"),
     id=("Jam dan Menit ke Detik", "Sebuah film berdurasi **j** jam **m** menit. Berapa detik lamanya?",
         "Satu baris berisi j dan m.", "Satu bilangan: lama film dalam detik.", "0 ≤ j ≤ 23, 0 ≤ m ≤ 59"),
     en=("Hours and Minutes to Seconds", "A film lasts **j** hours and **m** minutes. How many seconds is that?",
         "One line with j and m.", "One number: the length of the film in seconds.", "0 ≤ j ≤ 23, 0 ≤ m ≤ 59"),
     ar=("من الساعات والدقائق إلى الثواني", "مدة فيلم **j** ساعة و **m** دقيقة. كم ثانية تساوي؟",
         "سطر واحد فيه j و m.", "عدد واحد: مدة الفيلم بالثواني.", "0 ≤ j ≤ 23، 0 ≤ m ≤ 59"))

soal("volume-balok", "mudah", "trimmed", "print(r.randint(1, 100), r.randint(1, 100), r.randint(1, 100))\n",
     cpp("    int p, l, t;\n    cin >> p >> l >> t;\n    cout << p * l * t << endl;"),
     id=("Volume Balok", "Sebuah balok punya panjang **p**, lebar **l**, dan tinggi **t**. Cetak volumenya.",
         "Satu baris berisi p, l, t.", "Satu bilangan: volume balok.", "1 ≤ p, l, t ≤ 100"),
     en=("Volume of a Box", "A box has length **p**, width **l** and height **t**. Print its volume.",
         "One line with p, l, t.", "One number: the volume of the box.", "1 ≤ p, l, t ≤ 100"),
     ar=("حجم متوازي المستطيلات", "متوازي مستطيلات طوله **p** وعرضه **l** وارتفاعه **t**. اطبع حجمه.",
         "سطر واحد فيه p و l و t.", "عدد واحد: الحجم.", "1 ≤ p, l, t ≤ 100"))

soal("kalkulator-mini", "mudah", "token", "print(r.randint(1, 1000), r.randint(1, 1000))\n",
     cpp("    int a, b;\n    cin >> a >> b;\n    cout << a + b << endl;\n    cout << a - b << endl;\n    cout << a * b << endl;"),
     id=("Kalkulator Mini", "Baca dua bilangan bulat **a** dan **b**. Cetak jumlahnya, selisihnya (**a − b**), dan hasil kalinya, masing-masing di baris sendiri.",
         "Satu baris berisi a dan b.", "Tiga baris: a + b, a − b, a × b.", "1 ≤ a, b ≤ 1000"),
     en=("Mini Calculator", "Read two integers **a** and **b**. Print their sum, their difference (**a − b**) and their product, each on its own line.",
         "One line with a and b.", "Three lines: a + b, a − b, a × b.", "1 ≤ a, b ≤ 1000"),
     ar=("آلة حاسبة صغيرة", "اقرأ عددين صحيحين **a** و **b**. اطبع مجموعهما ثم الفرق (**a − b**) ثم حاصل ضربهما، كلٌّ في سطر.",
         "سطر واحد فيه a و b.", "ثلاثة أسطر: a + b ثم a − b ثم a × b.", "1 ≤ a, b ≤ 1000"))

soal("jarak-tempuh", "mudah", "trimmed", "print(r.randint(1, 120), r.randint(1, 24))\n",
     cpp("    int kecepatan, waktu;\n    cin >> kecepatan >> waktu;\n    cout << kecepatan * waktu << endl;"),
     id=("Jarak Tempuh", "Sebuah mobil melaju dengan kecepatan tetap **v** kilometer per jam selama **t** jam. Berapa kilometer jarak yang ditempuhnya?",
         "Satu baris berisi v dan t.", "Satu bilangan: jarak dalam kilometer.", "1 ≤ v ≤ 120, 1 ≤ t ≤ 24"),
     en=("Distance Travelled", "A car drives at a constant speed of **v** kilometres per hour for **t** hours. How many kilometres does it travel?",
         "One line with v and t.", "One number: the distance in kilometres.", "1 ≤ v ≤ 120, 1 ≤ t ≤ 24"),
     ar=("المسافة المقطوعة", "تسير سيارة بسرعة ثابتة **v** كيلومترًا في الساعة لمدة **t** ساعة. كم كيلومترًا تقطع؟",
         "سطر واحد فيه v و t.", "عدد واحد: المسافة بالكيلومترات.", "1 ≤ v ≤ 120، 1 ≤ t ≤ 24"))

soal("upah-mingguan", "mudah", "trimmed", "print(r.randint(10, 100) * 1000, r.randint(1, 12), r.randint(1, 7))\n",
     cpp("    int upah, jam, hari;\n    cin >> upah >> jam >> hari;\n    cout << upah * jam * hari << endl;"),
     id=("Upah Mingguan", "Seorang pekerja dibayar **u** rupiah per jam. Ia bekerja **j** jam sehari selama **h** hari dalam seminggu. Cetak upahnya minggu itu.",
         "Satu baris berisi u, j, h.", "Satu bilangan: upah seminggu.", "10000 ≤ u ≤ 100000, 1 ≤ j ≤ 12, 1 ≤ h ≤ 7"),
     en=("Weekly Pay", "A worker is paid **u** rupiah per hour. They work **j** hours a day for **h** days in a week. Print the pay for that week.",
         "One line with u, j, h.", "One number: the pay for the week.", "10000 ≤ u ≤ 100000, 1 ≤ j ≤ 12, 1 ≤ h ≤ 7"),
     ar=("الأجر الأسبوعي", "يتقاضى عامل **u** روبية عن كل ساعة. يعمل **j** ساعات في اليوم لمدة **h** أيام في الأسبوع. اطبع أجره في ذلك الأسبوع.",
         "سطر واحد فيه u و j و h.", "عدد واحد: أجر الأسبوع.", "10000 ≤ u ≤ 100000، 1 ≤ j ≤ 12، 1 ≤ h ≤ 7"))

# ============================== MUDAH-SEDANG (6) ==============================

soal("kembalian", "mudah_sedang", "trimmed",
     "a, b, c = (r.randint(1, 60) * 500 for _ in range(3))\nprint(a + b + c + r.randint(0, 40) * 500, a, b, c)\n",
     cpp("    int bayar, a, b, c;\n    cin >> bayar >> a >> b >> c;\n    cout << bayar - (a + b + c) << endl;"),
     id=("Kembalian", "Seorang pembeli mengambil tiga barang dengan harga **a**, **b**, dan **c** rupiah, lalu membayar dengan uang **u** rupiah. Cetak kembaliannya.",
         "Satu baris berisi empat bilangan bulat: u, a, b, c.", "Satu bilangan: kembalian.", "Uang yang dibayarkan selalu cukup. 500 ≤ a, b, c ≤ 30000."),
     en=("Change", "A customer takes three items priced **a**, **b** and **c** rupiah, then pays with **u** rupiah. Print the change.",
         "One line with four integers: u, a, b, c.", "One number: the change.", "The amount paid is always enough. 500 ≤ a, b, c ≤ 30000."),
     ar=("الباقي", "أخذ مشترٍ ثلاث سلع أسعارها **a** و **b** و **c** روبية، ثم دفع **u** روبية. اطبع الباقي.",
         "سطر واحد فيه أربعة أعداد صحيحة: u و a و b و c.", "عدد واحد: الباقي.", "المبلغ المدفوع يكفي دائمًا. 500 ≤ a, b, c ≤ 30000."))

soal("ganjil-dan-genap-ke-n", "mudah_sedang", "token", "print({2: 1, 3: 100000}.get(i, r.randint(1, 100000)))\n",
     cpp("    int n;\n    cin >> n;\n    cout << 2 * n - 1 << endl;\n    cout << 2 * n << endl;"),
     id=("Bilangan Ganjil dan Genap ke-n", "Bilangan ganjil positif berurutan: 1, 3, 5, 7, … Bilangan genap positif berurutan: 2, 4, 6, 8, …\n\n"
         "Diberikan **n**, cetak bilangan ganjil ke-n, lalu bilangan genap ke-n.\n\nContoh: untuk n = 4 jawabannya 7 dan 8.",
         "Satu bilangan bulat n.", "Baris pertama: bilangan ganjil ke-n. Baris kedua: bilangan genap ke-n.", "1 ≤ n ≤ 100000"),
     en=("The n-th Odd and Even Number", "The positive odd numbers in order are 1, 3, 5, 7, … The positive even numbers in order are 2, 4, 6, 8, …\n\n"
         "Given **n**, print the n-th odd number, then the n-th even number.\n\nExample: for n = 4 the answers are 7 and 8.",
         "One integer n.", "First line: the n-th odd number. Second line: the n-th even number.", "1 ≤ n ≤ 100000"),
     ar=("العدد الفردي والزوجي رقم n", "الأعداد الفردية الموجبة بالترتيب: 1، 3، 5، 7، … والأعداد الزوجية الموجبة بالترتيب: 2، 4، 6، 8، …\n\n"
         "إذا أُعطيت **n** فاطبع العدد الفردي رقم n ثم العدد الزوجي رقم n.\n\nمثال: عند n = 4 الجوابان 7 و 8.",
         "عدد صحيح واحد n.", "السطر الأول: العدد الفردي رقم n. السطر الثاني: العدد الزوجي رقم n.", "1 ≤ n ≤ 100000"))

soal("jumlah-satu-sampai-n", "mudah_sedang", "trimmed", "print({2: 1, 3: 10000, 4: 100}.get(i, r.randint(1, 10000)))\n",
     cpp("    int n;\n    cin >> n;\n    cout << n * (n + 1) / 2 << endl;"),
     id=("Jumlah 1 sampai n", "Cetak jumlah **1 + 2 + 3 + … + n**.\n\nPerulangan belum dipelajari, tetapi ada rumusnya: jumlah itu sama dengan **n × (n + 1) / 2**. "
         "Untuk n = 100 hasilnya 5050.",
         "Satu bilangan bulat n.", "Satu bilangan: 1 + 2 + … + n.", "1 ≤ n ≤ 10000"),
     en=("Sum of 1 to n", "Print the sum **1 + 2 + 3 + … + n**.\n\nLoops have not been taught yet, but there is a formula: the sum equals **n × (n + 1) / 2**. "
         "For n = 100 the result is 5050.",
         "One integer n.", "One number: 1 + 2 + … + n.", "1 ≤ n ≤ 10000"),
     ar=("مجموع الأعداد من 1 إلى n", "اطبع المجموع **1 + 2 + 3 + … + n**.\n\nلم ندرس الحلقات بعد، لكن توجد صيغة: المجموع يساوي **n × (n + 1) / 2**. "
         "عند n = 100 الناتج 5050.",
         "عدد صحيح واحد n.", "عدد واحد: 1 + 2 + … + n.", "1 ≤ n ≤ 10000"))

soal("luas-trapesium", "mudah_sedang", "float", "print(r.randint(1, 300), r.randint(1, 300), r.randint(1, 100))\n",
     cpp("    int a, b, t;\n    cin >> a >> b >> t;\n    double luas = (a + b) * t / 2.0;\n    cout << luas << endl;"),
     id=("Luas Trapesium", "Sebuah trapesium punya dua sisi sejajar sepanjang **a** dan **b**, dan tinggi **t**. Luasnya **(a + b) × t / 2**.\n\n"
         "Cetak luasnya. Hasilnya bisa berupa bilangan pecahan: untuk a = 3, b = 4, t = 5 luasnya 17.5.\n\n"
         "Petunjuk: membagi dengan `2.0` (bukan `2`) membuat hasilnya bilangan pecahan.",
         "Satu baris berisi a, b, t.", "Satu bilangan: luas trapesium.", "1 ≤ a, b ≤ 300, 1 ≤ t ≤ 100"),
     en=("Area of a Trapezoid", "A trapezoid has two parallel sides of length **a** and **b**, and height **t**. Its area is **(a + b) × t / 2**.\n\n"
         "Print the area. It can be a fraction: for a = 3, b = 4, t = 5 the area is 17.5.\n\n"
         "Hint: dividing by `2.0` (not `2`) makes the result a real number.",
         "One line with a, b, t.", "One number: the area of the trapezoid.", "1 ≤ a, b ≤ 300, 1 ≤ t ≤ 100"),
     ar=("مساحة شبه المنحرف", "شبه منحرف طولا ضلعيه المتوازيين **a** و **b** وارتفاعه **t**. مساحته **(a + b) × t / 2**.\n\n"
         "اطبع المساحة. قد تكون عددًا عشريًا: عند a = 3 و b = 4 و t = 5 المساحة 17.5.\n\n"
         "تلميح: القسمة على `2.0` (لا على `2`) تجعل الناتج عددًا عشريًا.",
         "سطر واحد فيه a و b و t.", "عدد واحد: مساحة شبه المنحرف.", "1 ≤ a, b ≤ 300، 1 ≤ t ≤ 100"))

soal("harga-setelah-diskon", "mudah_sedang", "trimmed", "print(r.randint(1, 5000) * 100, {2: 0, 3: 100}.get(i, r.randint(0, 100)))\n",
     cpp("    int harga, diskon;\n    cin >> harga >> diskon;\n    cout << harga - harga * diskon / 100 << endl;"),
     id=("Harga Setelah Diskon", "Sebuah barang berharga **h** rupiah dan sedang didiskon **d** persen. Cetak harga yang harus dibayar.\n\n"
         "Contoh: barang 80000 rupiah dengan diskon 25 persen menjadi 60000 rupiah.",
         "Satu baris berisi h dan d.", "Satu bilangan: harga setelah diskon.", "100 ≤ h ≤ 500000 dan h kelipatan 100; 0 ≤ d ≤ 100"),
     en=("Price After Discount", "An item costs **h** rupiah and is discounted by **d** percent. Print the price to pay.\n\n"
         "Example: an item of 80000 rupiah with a 25 percent discount costs 60000 rupiah.",
         "One line with h and d.", "One number: the price after the discount.", "100 ≤ h ≤ 500000 and h is a multiple of 100; 0 ≤ d ≤ 100"),
     ar=("السعر بعد الخصم", "سلعة سعرها **h** روبية وعليها خصم **d** بالمئة. اطبع السعر الواجب دفعه.\n\n"
         "مثال: سلعة بسعر 80000 روبية وخصم 25 بالمئة تصبح 60000 روبية.",
         "سطر واحد فيه h و d.", "عدد واحد: السعر بعد الخصم.", "100 ≤ h ≤ 500000 و h من مضاعفات 100؛ 0 ≤ d ≤ 100"))

soal("nilai-uang-logam", "mudah_sedang", "trimmed", "print(r.randint(0, 100), r.randint(0, 100), r.randint(0, 100), r.randint(0, 100))\n",
     cpp("    int seribu, limaRatus, duaRatus, seratus;\n    cin >> seribu >> limaRatus >> duaRatus >> seratus;\n"
         "    cout << seribu * 1000 + limaRatus * 500 + duaRatus * 200 + seratus * 100 << endl;"),
     id=("Nilai Uang Logam", "Di dalam celengan ada **a** keping uang logam 1000 rupiah, **b** keping 500 rupiah, **c** keping 200 rupiah, dan **d** keping 100 rupiah. "
         "Berapa rupiah isi celengan itu?",
         "Satu baris berisi empat bilangan bulat: a, b, c, d.", "Satu bilangan: jumlah uang dalam rupiah.", "0 ≤ a, b, c, d ≤ 100"),
     en=("Value of the Coins", "A piggy bank holds **a** coins of 1000 rupiah, **b** coins of 500 rupiah, **c** coins of 200 rupiah and **d** coins of 100 rupiah. "
         "How many rupiah are in it?",
         "One line with four integers: a, b, c, d.", "One number: the amount in rupiah.", "0 ≤ a, b, c, d ≤ 100"),
     ar=("قيمة العملات المعدنية", "في حصّالة **a** قطعة من فئة 1000 روبية و **b** قطعة من فئة 500 و **c** قطعة من فئة 200 و **d** قطعة من فئة 100. "
         "كم روبية في الحصّالة؟",
         "سطر واحد فيه أربعة أعداد صحيحة: a و b و c و d.", "عدد واحد: المبلغ بالروبية.", "0 ≤ a, b, c, d ≤ 100"))

# ================================= SEDANG (6) =================================

soal("lingkaran", "sedang", "float", "print({2: 1, 3: 30, 4: 10}.get(i, r.randint(1, 30)))\n",
     cpp("    int r;\n    cin >> r;\n    double pi = 3.14;\n    cout << pi * r * r << endl;\n    cout << 2 * pi * r << endl;"),
     id=("Luas dan Keliling Lingkaran", "Sebuah lingkaran punya jari-jari **r**. Dengan **π = 3.14**, cetak luasnya (**π × r × r**), lalu kelilingnya (**2 × π × r**).\n\n"
         "Simpan nilai π di sebuah variabel bertipe `double`.",
         "Satu bilangan bulat r.", "Baris pertama: luas lingkaran. Baris kedua: kelilingnya.", "1 ≤ r ≤ 30"),
     en=("Area and Circumference of a Circle", "A circle has radius **r**. Using **π = 3.14**, print its area (**π × r × r**), then its circumference (**2 × π × r**).\n\n"
         "Store the value of π in a variable of type `double`.",
         "One integer r.", "First line: the area of the circle. Second line: its circumference.", "1 ≤ r ≤ 30"),
     ar=("مساحة الدائرة ومحيطها", "دائرة نصف قطرها **r**. باستخدام **π = 3.14** اطبع مساحتها (**π × r × r**) ثم محيطها (**2 × π × r**).\n\n"
         "خزِّن قيمة π في متغير من النوع `double`.",
         "عدد صحيح واحد r.", "السطر الأول: مساحة الدائرة. السطر الثاني: محيطها.", "1 ≤ r ≤ 30"))

soal("rata-rata-lima-nilai", "sedang", "float", "print(*(r.randint(0, 100) for _ in range(5)))\n",
     cpp("    int a, b, c, d, e;\n    cin >> a >> b >> c >> d >> e;\n    double rata = (a + b + c + d + e) / 5.0;\n    cout << rata << endl;"),
     id=("Rata-Rata Lima Nilai", "Lima nilai kuis berupa bilangan bulat. Cetak rata-ratanya sebagai bilangan pecahan.\n\n"
         "Contoh: rata-rata dari 70, 80, 90, 85, 78 adalah 80.6.",
         "Satu baris berisi lima bilangan bulat.", "Satu bilangan: rata-rata kelimanya.", "0 ≤ setiap nilai ≤ 100"),
     en=("Average of Five Scores", "Five quiz scores are integers. Print their average as a real number.\n\n"
         "Example: the average of 70, 80, 90, 85, 78 is 80.6.",
         "One line with five integers.", "One number: the average of the five.", "0 ≤ each score ≤ 100"),
     ar=("متوسط خمس درجات", "خمس درجات اختبار أعداد صحيحة. اطبع متوسطها كعدد عشري.\n\n"
         "مثال: متوسط 70 و 80 و 90 و 85 و 78 هو 80.6.",
         "سطر واحد فيه خمسة أعداد صحيحة.", "عدد واحد: متوسط الخمسة.", "0 ≤ كل درجة ≤ 100"))

soal("km-m-cm", "sedang", "token", "print({2: 0, 3: 123456, 4: 99, 5: 100000}.get(i, r.randint(0, 10**7)))\n",
     cpp("    int cm;\n    cin >> cm;\n    cout << cm / 100000 << \" \" << cm % 100000 / 100 << \" \" << cm % 100 << endl;"),
     id=("Kilometer, Meter, Sentimeter", "Sebuah jarak diukur **c** sentimeter. Tuliskan jarak itu dalam kilometer, meter, dan sentimeter.\n\n"
         "Contoh: 123456 sentimeter adalah 1 kilometer 234 meter 56 sentimeter.\n\n"
         "Petunjuk: pada bilangan bulat, `/` memberi hasil bagi tanpa pecahan dan `%` memberi sisa pembagian.",
         "Satu bilangan bulat c.", "Satu baris berisi tiga bilangan dipisah spasi: kilometer, meter, sentimeter.", "0 ≤ c ≤ 10000000"),
     en=("Kilometres, Metres, Centimetres", "A distance measures **c** centimetres. Write it in kilometres, metres and centimetres.\n\n"
         "Example: 123456 centimetres is 1 kilometre 234 metres 56 centimetres.\n\n"
         "Hint: on integers, `/` gives the quotient without the fraction and `%` gives the remainder.",
         "One integer c.", "One line with three numbers separated by spaces: kilometres, metres, centimetres.", "0 ≤ c ≤ 10000000"),
     ar=("كيلومتر ومتر وسنتيمتر", "مسافة طولها **c** سنتيمترًا. اكتبها بالكيلومترات والأمتار والسنتيمترات.\n\n"
         "مثال: 123456 سنتيمترًا هي 1 كيلومتر و 234 مترًا و 56 سنتيمترًا.\n\n"
         "تلميح: مع الأعداد الصحيحة يعطي `/` خارج القسمة بلا كسر ويعطي `%` باقي القسمة.",
         "عدد صحيح واحد c.", "سطر واحد فيه ثلاثة أعداد تفصل بينها مسافات: الكيلومترات ثم الأمتار ثم السنتيمترات.", "0 ≤ c ≤ 10000000"))

soal("bunga-tabungan", "sedang", "trimmed", "print(r.randint(1, 1000) * 1000, r.randint(1, 20), r.randint(1, 10))\n",
     cpp("    int pokok, bunga, tahun;\n    cin >> pokok >> bunga >> tahun;\n    cout << pokok + pokok / 100 * bunga * tahun << endl;"),
     id=("Bunga Tabungan", "Seseorang menabung **p** rupiah. Setiap tahun ia mendapat bunga **b** persen dari tabungan awalnya (bunga tidak ikut berbunga). "
         "Cetak jumlah tabungannya setelah **t** tahun.\n\nContoh: 1000000 rupiah dengan bunga 5 persen selama 3 tahun menjadi 1150000 rupiah.",
         "Satu baris berisi p, b, t.", "Satu bilangan: tabungan setelah t tahun.", "1000 ≤ p ≤ 1000000 dan p kelipatan 1000; 1 ≤ b ≤ 20; 1 ≤ t ≤ 10"),
     en=("Savings Interest", "Someone saves **p** rupiah. Every year they receive **b** percent interest on the original amount (interest earns no interest). "
         "Print the savings after **t** years.\n\nExample: 1000000 rupiah at 5 percent for 3 years becomes 1150000 rupiah.",
         "One line with p, b, t.", "One number: the savings after t years.", "1000 ≤ p ≤ 1000000 and p is a multiple of 1000; 1 ≤ b ≤ 20; 1 ≤ t ≤ 10"),
     ar=("فائدة الادخار", "ادّخر شخص **p** روبية. يحصل كل سنة على فائدة **b** بالمئة من المبلغ الأصلي (الفائدة لا تُحتسب عليها فائدة). "
         "اطبع مدّخراته بعد **t** سنة.\n\nمثال: 1000000 روبية بفائدة 5 بالمئة لمدة 3 سنوات تصبح 1150000 روبية.",
         "سطر واحد فيه p و b و t.", "عدد واحد: المدّخرات بعد t سنة.", "1000 ≤ p ≤ 1000000 و p من مضاعفات 1000؛ 1 ≤ b ≤ 20؛ 1 ≤ t ≤ 10"))

soal("titik-tengah", "sedang", "float", "print(*(r.randint(-500, 500) for _ in range(4)))\n",
     cpp("    int x1, y1, x2, y2;\n    cin >> x1 >> y1 >> x2 >> y2;\n    cout << (x1 + x2) / 2.0 << \" \" << (y1 + y2) / 2.0 << endl;"),
     id=("Titik Tengah", "Dua titik pada bidang punya koordinat **(x1, y1)** dan **(x2, y2)**. Cetak koordinat titik yang tepat berada di tengah keduanya.\n\n"
         "Contoh: titik tengah dari (1, 2) dan (4, 6) adalah (2.5, 4).",
         "Satu baris berisi empat bilangan bulat: x1 y1 x2 y2.", "Satu baris: koordinat x titik tengah, satu spasi, lalu koordinat y-nya.", "−500 ≤ setiap koordinat ≤ 500"),
     en=("Midpoint", "Two points in the plane have coordinates **(x1, y1)** and **(x2, y2)**. Print the coordinates of the point exactly halfway between them.\n\n"
         "Example: the midpoint of (1, 2) and (4, 6) is (2.5, 4).",
         "One line with four integers: x1 y1 x2 y2.", "One line: the x coordinate of the midpoint, a space, then its y coordinate.", "−500 ≤ each coordinate ≤ 500"),
     ar=("نقطة المنتصف", "نقطتان في المستوي إحداثياتهما **(x1, y1)** و **(x2, y2)**. اطبع إحداثيات النقطة الواقعة في منتصف المسافة بينهما تمامًا.\n\n"
         "مثال: منتصف (1, 2) و (4, 6) هو (2.5, 4).",
         "سطر واحد فيه أربعة أعداد صحيحة: x1 y1 x2 y2.", "سطر واحد: الإحداثي x لنقطة المنتصف ثم مسافة ثم الإحداثي y.", "−500 ≤ كل إحداثي ≤ 500"))

soal("luas-permukaan-balok", "sedang", "trimmed", "print(r.randint(1, 1000), r.randint(1, 1000), r.randint(1, 1000))\n",
     cpp("    int p, l, t;\n    cin >> p >> l >> t;\n    cout << 2 * (p * l + p * t + l * t) << endl;"),
     id=("Luas Permukaan Balok", "Sebuah kotak kado berbentuk balok dengan panjang **p**, lebar **l**, dan tinggi **t** akan dibungkus kertas. "
         "Berapa luas kertas yang dibutuhkan untuk menutup seluruh permukaannya tepat satu lapis?\n\n"
         "Sebuah balok punya enam sisi yang berpasang-pasangan.",
         "Satu baris berisi p, l, t.", "Satu bilangan: luas permukaan balok.", "1 ≤ p, l, t ≤ 1000"),
     en=("Surface Area of a Box", "A gift box with length **p**, width **l** and height **t** is to be wrapped in paper. "
         "How much paper is needed to cover its whole surface with exactly one layer?\n\n"
         "A box has six faces that come in pairs.",
         "One line with p, l, t.", "One number: the surface area of the box.", "1 ≤ p, l, t ≤ 1000"),
     ar=("مساحة سطح متوازي المستطيلات", "علبة هدية على شكل متوازي مستطيلات طولها **p** وعرضها **l** وارتفاعها **t** ستُغلَّف بالورق. "
         "ما مساحة الورق اللازمة لتغطية سطحها كله بطبقة واحدة تمامًا؟\n\n"
         "لمتوازي المستطيلات ستة أوجه متقابلة مثنى مثنى.",
         "سطر واحد فيه p و l و t.", "عدد واحد: مساحة السطح.", "1 ≤ p, l, t ≤ 1000"))

# ================================== SULIT (4) ==================================

soal("paket-lusinan", "sulit", "trimmed",
     "s = r.randint(1, 20) * 500\nprint({2: 30, 3: 11, 4: 12, 5: 0}.get(i, r.randint(0, 1000)), s, s * 12 - r.randint(1, 10) * 500)\n",
     cpp("    int n, satuan, lusin;\n    cin >> n >> satuan >> lusin;\n    cout << n / 12 * lusin + n % 12 * satuan << endl;"),
     id=("Paket Lusinan", "Sebuah toko menjual pensil **s** rupiah per batang, atau **l** rupiah per lusin (12 batang). Harga selusin selalu lebih murah daripada 12 batang satuan.\n\n"
         "Seorang pembeli memerlukan tepat **n** batang. Ia membeli sebanyak mungkin lusin, dan sisanya satuan. Cetak jumlah yang harus dibayarnya.\n\n"
         "Contoh: 30 batang dengan harga 2000 per batang dan 20000 per lusin: 2 lusin dan 6 batang, jadi 52000 rupiah.",
         "Satu baris berisi n, s, l.", "Satu bilangan: jumlah yang harus dibayar.", "0 ≤ n ≤ 1000; 500 ≤ s ≤ 10000; l < 12 × s"),
     en=("Buying by the Dozen", "A shop sells pencils for **s** rupiah each, or **l** rupiah per dozen (12 pencils). A dozen is always cheaper than 12 single pencils.\n\n"
         "A customer needs exactly **n** pencils. They buy as many dozens as possible and the rest one by one. Print the amount to pay.\n\n"
         "Example: 30 pencils at 2000 each and 20000 per dozen: 2 dozens and 6 singles, so 52000 rupiah.",
         "One line with n, s, l.", "One number: the amount to pay.", "0 ≤ n ≤ 1000; 500 ≤ s ≤ 10000; l < 12 × s"),
     ar=("الشراء بالدزينة", "يبيع متجر القلم الواحد بـ **s** روبية، أو الدزينة (12 قلمًا) بـ **l** روبية. سعر الدزينة دائمًا أرخص من 12 قلمًا مفردًا.\n\n"
         "يحتاج مشترٍ إلى **n** قلمًا بالضبط. يشتري أكبر عدد ممكن من الدزينات والباقي بالمفرد. اطبع المبلغ الواجب دفعه.\n\n"
         "مثال: 30 قلمًا بسعر 2000 للقلم و 20000 للدزينة: دزينتان و 6 أقلام، أي 52000 روبية.",
         "سطر واحد فيه n و s و l.", "عدد واحد: المبلغ الواجب دفعه.", "0 ≤ n ≤ 1000؛ 500 ≤ s ≤ 10000؛ l < 12 × s"))

soal("jumlah-deret", "sulit", "trimmed", "a, b, n = r.randint(1, 1000), r.randint(0, 100), r.randint(1, 1000)\nif i == 2:\n    a, b, n = 3, 4, 5\nif i == 3:\n    n = 1\nprint(a, b, n)\n",
     cpp("    int a, b, n;\n    cin >> a >> b >> n;\n    cout << n * (2 * a + (n - 1) * b) / 2 << endl;"),
     id=("Jumlah Deret", "Sebuah deret dimulai dari **a**, dan setiap suku berikutnya lebih besar **b** daripada suku sebelumnya: a, a + b, a + 2b, …\n\n"
         "Cetak jumlah **n** suku pertamanya.\n\nContoh: a = 3, b = 4, n = 5 memberi 3 + 7 + 11 + 15 + 19 = 55.\n\n"
         "Perulangan belum dipelajari. Perhatikan: suku pertama ditambah suku terakhir sama dengan suku kedua ditambah suku kedua dari belakang, dan seterusnya.",
         "Satu baris berisi a, b, n.", "Satu bilangan: jumlah n suku pertama.", "1 ≤ a ≤ 1000; 0 ≤ b ≤ 100; 1 ≤ n ≤ 1000"),
     en=("Sum of a Sequence", "A sequence starts at **a**, and every next term is **b** larger than the one before: a, a + b, a + 2b, …\n\n"
         "Print the sum of its first **n** terms.\n\nExample: a = 3, b = 4, n = 5 gives 3 + 7 + 11 + 15 + 19 = 55.\n\n"
         "Loops have not been taught yet. Notice: the first term plus the last term equals the second term plus the second-to-last term, and so on.",
         "One line with a, b, n.", "One number: the sum of the first n terms.", "1 ≤ a ≤ 1000; 0 ≤ b ≤ 100; 1 ≤ n ≤ 1000"),
     ar=("مجموع متتالية", "متتالية تبدأ بـ **a** وكل حدّ تالٍ يزيد **b** على الحدّ الذي قبله: a، a + b، a + 2b، …\n\n"
         "اطبع مجموع أول **n** حدًّا منها.\n\nمثال: a = 3 و b = 4 و n = 5 تعطي 3 + 7 + 11 + 15 + 19 = 55.\n\n"
         "لم ندرس الحلقات بعد. لاحظ: الحدّ الأول مع الحدّ الأخير يساوي الحدّ الثاني مع الحدّ قبل الأخير، وهكذا.",
         "سطر واحد فيه a و b و n.", "عدد واحد: مجموع أول n حدًّا.", "1 ≤ a ≤ 1000؛ 0 ≤ b ≤ 100؛ 1 ≤ n ≤ 1000"))

soal("tukar-dua-digit", "sulit", "trimmed", "print({2: 47, 3: 90, 4: 55, 5: 10}.get(i, r.randint(10, 99)))\n",
     cpp("    int n;\n    cin >> n;\n    int puluhan = n / 10;\n    int satuan = n % 10;\n    cout << satuan * 10 + puluhan << endl;"),
     id=("Tukar Dua Digit", "Diberikan sebuah bilangan dua digit. Tukar kedua digitnya, lalu cetak bilangan yang terbentuk.\n\n"
         "Contoh: 47 menjadi 74. Hasilnya tetap sebuah bilangan, jadi 90 menjadi 9.\n\n"
         "Petunjuk: pada bilangan bulat, `n / 10` membuang digit terakhir dan `n % 10` mengambil digit terakhir.",
         "Satu bilangan bulat dua digit.", "Satu bilangan: bilangan itu dengan kedua digitnya ditukar.", "10 ≤ n ≤ 99"),
     en=("Swap Two Digits", "Given a two-digit number, swap its two digits and print the number that results.\n\n"
         "Example: 47 becomes 74. The result is still a number, so 90 becomes 9.\n\n"
         "Hint: on integers, `n / 10` drops the last digit and `n % 10` takes the last digit.",
         "One two-digit integer.", "One number: the number with its two digits swapped.", "10 ≤ n ≤ 99"),
     ar=("تبديل رقمين", "إذا أُعطيت عددًا من رقمين فبدِّل رقميه واطبع العدد الناتج.\n\n"
         "مثال: 47 يصبح 74. الناتج يبقى عددًا، لذلك 90 يصبح 9.\n\n"
         "تلميح: مع الأعداد الصحيحة يحذف `n / 10` الرقم الأخير ويأخذ `n % 10` الرقم الأخير.",
         "عدد صحيح واحد من رقمين.", "عدد واحد: العدد بعد تبديل رقميه.", "10 ≤ n ≤ 99"))

soal("bagi-kelompok", "sulit", "token",
     "n, k = r.randint(1, 1000), r.randint(2, 50)\nif i == 2:\n    n, k = 23, 5\nif i == 3:\n    n, k = 40, 8\nif i == 4:\n    n, k = 3, 10\nprint(n, k)\n",
     cpp("    int n, k;\n    cin >> n >> k;\n    cout << n / k << endl;\n    cout << n % k << endl;\n    cout << (k - n % k) % k << endl;"),
     id=("Bagi Kelompok", "Sebanyak **n** mahasiswa dibagi ke dalam kelompok yang masing-masing berisi tepat **k** orang. Cetak tiga bilangan:\n\n"
         "1. banyak kelompok yang penuh;\n2. banyak mahasiswa yang belum mendapat kelompok;\n"
         "3. banyak mahasiswa tambahan yang dibutuhkan supaya tidak ada yang tersisa (0 bila sudah pas).\n\n"
         "Contoh: 23 mahasiswa dengan kelompok berisi 5 orang: 4 kelompok, 3 tersisa, butuh 2 orang lagi.\n\n"
         "Percabangan belum dipelajari; bilangan ketiga bisa dihitung dengan `%` saja.",
         "Satu baris berisi n dan k.", "Tiga baris: kelompok penuh, mahasiswa tersisa, tambahan yang dibutuhkan.", "1 ≤ n ≤ 1000; 2 ≤ k ≤ 50"),
     en=("Forming Groups", "**n** students are split into groups of exactly **k** people each. Print three numbers:\n\n"
         "1. the number of full groups;\n2. the number of students left without a group;\n"
         "3. the number of extra students needed so that nobody is left over (0 if it already fits).\n\n"
         "Example: 23 students in groups of 5: 4 groups, 3 left over, 2 more needed.\n\n"
         "Branching has not been taught yet; the third number can be computed with `%` alone.",
         "One line with n and k.", "Three lines: full groups, students left over, extra students needed.", "1 ≤ n ≤ 1000; 2 ≤ k ≤ 50"),
     ar=("تقسيم المجموعات", "يُقسَّم **n** طالبًا إلى مجموعات في كل منها **k** أشخاص بالضبط. اطبع ثلاثة أعداد:\n\n"
         "1. عدد المجموعات المكتملة؛\n2. عدد الطلاب الذين بقوا بلا مجموعة؛\n"
         "3. عدد الطلاب الإضافيين اللازمين حتى لا يبقى أحد (0 إذا كان العدد مناسبًا أصلًا).\n\n"
         "مثال: 23 طالبًا في مجموعات من 5: أربع مجموعات، يبقى 3، ونحتاج إلى 2 آخرين.\n\n"
         "لم ندرس التفرّع بعد؛ يمكن حساب العدد الثالث باستخدام `%` وحده.",
         "سطر واحد فيه n و k.", "ثلاثة أسطر: المجموعات المكتملة، الطلاب المتبقون، الطلاب الإضافيون اللازمون.", "1 ≤ n ≤ 1000؛ 2 ≤ k ≤ 50"))

tulis(__file__, "DDP101", 1, "cpp", {"mudah_sekali": 8, "mudah": 8, "mudah_sedang": 6, "sedang": 6, "sulit": 4})
