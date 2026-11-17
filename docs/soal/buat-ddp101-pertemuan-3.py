#!/usr/bin/env python3
"""Menyusun docs/soal/ddp101-pertemuan-3.json.

DDP101 pertemuan 3 — Input dan Output. Konsep yang diizinkan: IO_DASAR,
VARIABEL_TIPE, ARITMETIKA, KONVERSI_TIPE, FORMAT_OUTPUT, STRING. Solusi referensi
TIDAK memakai if, ?:, &&, ||, atau perulangan. 32 soal: 8 / 8 / 6 / 6 / 4.

Keluaran soal-soal ini harus persis (mode `trimmed`: hanya spasi di ujung baris
yang diabaikan), karena yang dilatih justru format keluaran. Teks tetap yang
dicetak program ("Halo, ", "Total", …) sama di ketiga bahasa soal.

Bilangan pecahan dipilih sehingga pembulatannya tidak pernah jatuh tepat di
tengah (…5), supaya jawaban tidak bergantung pada urutan perhitungan.

    python3 docs/soal/buat-ddp101-pertemuan-3.py
"""
from bangun import cpp, soal, tulis

NAMA = "NAMA = ['Andi', 'Budi', 'Citra', 'Dewi', 'Eko', 'Fajar', 'Gita', 'Hadi', 'Indah', 'Joko', 'Kartika', 'Lina', 'Made', 'Nanda', 'Putri', 'Rudi', 'Sari', 'Tono', 'Wulan', 'Yoga']\n"
MARGA = "MARGA = ['Saputra', 'Wijaya', 'Lestari', 'Pratama', 'Hidayat', 'Utami', 'Santoso', 'Nugroho', 'Rahayu', 'Siregar', 'Halim', 'Kusuma']\n"
KATA = "KATA = ['belajar', 'program', 'komputer', 'kampus', 'buku', 'meja', 'layar', 'kode', 'data', 'angka', 'huruf', 'kelas', 'tugas', 'ujian', 'pagi', 'malam', 'cepat', 'rapi', 'benar', 'coba']\n"
ACAK = "def acak(a, b):\n    return ''.join(r.choice('abcdefghijklmnopqrstuvwxyz') for _ in range(r.randint(a, b)))\n"
KALIMAT = KATA + "def kalimat(a, b):\n    return ' '.join(r.choice(KATA) for _ in range(r.randint(a, b)))\n"

# ============================== MUDAH SEKALI (8) ==============================

soal("sapa-nama", "mudah_sekali", "trimmed", NAMA + "print(r.choice(NAMA))\n",
     cpp("    string nama;\n    cin >> nama;\n    cout << \"Halo, \" << nama << \"!\" << endl;", "string"),
     id=("Sapa Nama", "Baca sebuah nama (satu kata), lalu sapa pemiliknya.\n\nUntuk masukan `Budi`, keluarannya persis `Halo, Budi!`\n\nTeks disimpan di variabel bertipe `string`.",
         "Satu kata: sebuah nama.", "Satu baris: `Halo, ` diikuti nama itu dan tanda seru.", "Nama terdiri dari huruf saja, paling panjang 20 huruf."),
     en=("Greet by Name", "Read a name (one word), then greet its owner.\n\nFor the input `Budi` the output is exactly `Halo, Budi!`\n\nText is stored in a variable of type `string`.",
         "One word: a name.", "One line: `Halo, ` followed by the name and an exclamation mark.", "The name has letters only, at most 20 letters."),
     ar=("التحية بالاسم", "اقرأ اسمًا (كلمة واحدة) ثم حيِّ صاحبه.\n\nعند المدخل `Budi` يكون المخرج بالضبط `Halo, Budi!`\n\nيُخزَّن النص في متغير من النوع `string`.",
         "كلمة واحدة: اسم.", "سطر واحد: `Halo, ` يليه الاسم ثم علامة تعجب.", "الاسم حروف لاتينية فقط، 20 حرفًا على الأكثر."))

soal("dua-desimal", "mudah_sekali", "trimmed",
     "k = r.randint(0, 9999900)\nif k // 10 % 10 == 5:\n    k += 10\nif i == 2:\n    k = 31416\nif i == 3:\n    k = 20000\nprint(f\"{k / 10000:.4f}\")\n",
     cpp("    double x;\n    cin >> x;\n    cout << fixed << setprecision(2) << x << endl;", "iomanip"),
     id=("Dua Angka di Belakang Koma", "Baca sebuah bilangan pecahan, lalu cetak dengan **tepat dua angka** di belakang koma.\n\n"
         "Contoh: `3.1416` dicetak `3.14`, dan `2.0000` dicetak `2.00`.\n\nPetunjuk: `fixed` dan `setprecision` dari `<iomanip>`.",
         "Satu bilangan pecahan dengan empat angka di belakang koma.", "Bilangan itu dengan dua angka di belakang koma.", "0 ≤ x < 1000"),
     en=("Two Decimal Places", "Read a real number, then print it with **exactly two digits** after the decimal point.\n\n"
         "Example: `3.1416` is printed as `3.14`, and `2.0000` as `2.00`.\n\nHint: `fixed` and `setprecision` from `<iomanip>`.",
         "One real number with four digits after the decimal point.", "The number with two digits after the decimal point.", "0 ≤ x < 1000"),
     ar=("رقمان بعد الفاصلة", "اقرأ عددًا عشريًا ثم اطبعه **برقمين بالضبط** بعد الفاصلة العشرية.\n\n"
         "مثال: `3.1416` يُطبع `3.14` و `2.0000` يُطبع `2.00`.\n\nتلميح: `fixed` و `setprecision` من `<iomanip>`.",
         "عدد عشري واحد بأربعة أرقام بعد الفاصلة.", "العدد برقمين بعد الفاصلة.", "0 ≤ x < 1000"))

soal("panjang-kata", "mudah_sekali", "trimmed", ACAK + "print(acak(1, 20))\n",
     cpp("    string kata;\n    cin >> kata;\n    cout << kata.size() << endl;", "string"),
     id=("Panjang Kata", "Baca sebuah kata, lalu cetak berapa huruf panjangnya.\n\nUntuk `program` jawabannya 7.",
         "Satu kata dari huruf kecil.", "Satu bilangan: banyak huruf kata itu.", "Panjang kata 1 sampai 20 huruf."),
     en=("Length of a Word", "Read a word, then print how many letters long it is.\n\nFor `program` the answer is 7.",
         "One word of lowercase letters.", "One number: the number of letters in the word.", "The word is 1 to 20 letters long."),
     ar=("طول الكلمة", "اقرأ كلمة ثم اطبع عدد حروفها.\n\nللكلمة `program` الجواب 7.",
         "كلمة واحدة من حروف لاتينية صغيرة.", "عدد واحد: عدد حروف الكلمة.", "طول الكلمة من 1 إلى 20 حرفًا."))

soal("huruf-pertama", "mudah_sekali", "trimmed", ACAK + "print(acak(1, 15))\n",
     cpp("    string kata;\n    cin >> kata;\n    cout << kata[0] << endl;", "string"),
     id=("Huruf Pertama", "Baca sebuah kata, lalu cetak huruf pertamanya.\n\nHuruf-huruf sebuah `string` bisa diambil dengan nomor urutnya, mulai dari 0.",
         "Satu kata dari huruf kecil.", "Satu huruf.", "Panjang kata 1 sampai 15 huruf."),
     en=("First Letter", "Read a word, then print its first letter.\n\nThe letters of a `string` can be taken by their position, counting from 0.",
         "One word of lowercase letters.", "One letter.", "The word is 1 to 15 letters long."),
     ar=("الحرف الأول", "اقرأ كلمة ثم اطبع حرفها الأول.\n\nيمكن أخذ حروف `string` برقم موضعها، والعدّ يبدأ من 0.",
         "كلمة واحدة من حروف لاتينية صغيرة.", "حرف واحد.", "طول الكلمة من 1 إلى 15 حرفًا."))

soal("sambung-dua-kata", "mudah_sekali", "trimmed", KATA + "print(r.choice(KATA), r.choice(KATA))\n",
     cpp("    string a, b;\n    cin >> a >> b;\n    cout << a + \"-\" + b << endl;", "string"),
     id=("Sambung Dua Kata", "Baca dua kata, lalu cetak keduanya disambung dengan tanda hubung.\n\nUntuk `buku tugas` keluarannya `buku-tugas`.\n\nDua `string` bisa disambung dengan `+`.",
         "Satu baris berisi dua kata dipisah satu spasi.", "Kata pertama, tanda hubung, kata kedua, tanpa spasi.", "Tiap kata paling panjang 10 huruf."),
     en=("Join Two Words", "Read two words, then print them joined by a hyphen.\n\nFor `buku tugas` the output is `buku-tugas`.\n\nTwo `string` values can be joined with `+`.",
         "One line with two words separated by one space.", "The first word, a hyphen, the second word, without spaces.", "Each word is at most 10 letters long."),
     ar=("وصل كلمتين", "اقرأ كلمتين ثم اطبعهما موصولتين بشرطة.\n\nعند `buku tugas` يكون المخرج `buku-tugas`.\n\nيمكن وصل قيمتين من النوع `string` بالعامل `+`.",
         "سطر واحد فيه كلمتان تفصل بينهما مسافة واحدة.", "الكلمة الأولى ثم شرطة ثم الكلمة الثانية، بلا مسافات.", "كل كلمة 10 حروف على الأكثر."))

soal("rata-kanan", "mudah_sekali", "trimmed", "print({2: 7, 3: 99999, 4: 0}.get(i, r.randint(0, 10 ** r.randint(1, 5) - 1)))\n",
     cpp("    int n;\n    cin >> n;\n    cout << setw(5) << n << endl;", "iomanip"),
     id=("Rata Kanan", "Baca sebuah bilangan bulat, lalu cetak **rata kanan dalam lebar 5 karakter**: bila digitnya kurang dari lima, kekurangannya diisi spasi di sebelah kiri.\n\n"
         "Contoh: `42` dicetak sebagai tiga spasi diikuti `42`.\n\nPetunjuk: `setw` dari `<iomanip>`.",
         "Satu bilangan bulat n.", "Bilangan itu, rata kanan dalam lebar 5.", "0 ≤ n ≤ 99999"),
     en=("Right-Aligned", "Read an integer, then print it **right-aligned in a width of 5 characters**: if it has fewer than five digits, the gap is filled with spaces on the left.\n\n"
         "Example: `42` is printed as three spaces followed by `42`.\n\nHint: `setw` from `<iomanip>`.",
         "One integer n.", "The number, right-aligned in a width of 5.", "0 ≤ n ≤ 99999"),
     ar=("محاذاة إلى اليمين", "اقرأ عددًا صحيحًا ثم اطبعه **محاذًى إلى اليمين في عرض 5 محارف**: إذا كانت أرقامه أقل من خمسة فيُملأ النقص بمسافات من اليسار.\n\n"
         "مثال: `42` يُطبع ثلاث مسافات يليها `42`.\n\nتلميح: `setw` من `<iomanip>`.",
         "عدد صحيح واحد n.", "العدد محاذًى إلى اليمين في عرض 5.", "0 ≤ n ≤ 99999"))

soal("selamat-datang", "mudah_sekali", "trimmed", NAMA + MARGA + "print(r.choice(NAMA), r.choice(MARGA))\n",
     cpp("    string nama;\n    getline(cin, nama);\n    cout << \"Selamat datang, \" << nama << \"!\" << endl;", "string"),
     id=("Selamat Datang", "Baca **nama lengkap** seseorang (bisa lebih dari satu kata), lalu cetak ucapan selamat datang.\n\n"
         "Untuk `Dewi Lestari` keluarannya `Selamat datang, Dewi Lestari!`\n\n`cin >> nama` berhenti di spasi pertama. Untuk membaca satu baris penuh pakai `getline(cin, nama)`.",
         "Satu baris: nama lengkap.", "Satu baris: `Selamat datang, ` diikuti nama lengkap dan tanda seru.", "Nama paling panjang 40 karakter."),
     en=("Welcome", "Read someone's **full name** (it can be more than one word), then print a welcome.\n\n"
         "For `Dewi Lestari` the output is `Selamat datang, Dewi Lestari!`\n\n`cin >> nama` stops at the first space. To read a whole line use `getline(cin, nama)`.",
         "One line: the full name.", "One line: `Selamat datang, ` followed by the full name and an exclamation mark.", "The name is at most 40 characters long."),
     ar=("أهلًا وسهلًا", "اقرأ **الاسم الكامل** لشخص (قد يكون أكثر من كلمة) ثم اطبع عبارة ترحيب.\n\n"
         "عند `Dewi Lestari` يكون المخرج `Selamat datang, Dewi Lestari!`\n\n`cin >> nama` يتوقف عند أول مسافة. لقراءة سطر كامل استخدم `getline(cin, nama)`.",
         "سطر واحد: الاسم الكامل.", "سطر واحد: `Selamat datang, ` يليه الاسم الكامل ثم علامة تعجب.", "الاسم 40 محرفًا على الأكثر."))

soal("dua-kali", "mudah_sekali", "trimmed", KALIMAT + "print(kalimat(2, 5))\n",
     cpp("    string baris;\n    getline(cin, baris);\n    cout << baris << endl;\n    cout << baris << endl;", "string"),
     id=("Dua Kali", "Baca satu baris teks (bisa berisi spasi), lalu cetak baris itu dua kali, masing-masing di baris sendiri.",
         "Satu baris teks.", "Dua baris, keduanya sama dengan baris masukan.", "Baris paling panjang 60 karakter."),
     en=("Twice", "Read one line of text (it may contain spaces), then print that line twice, each on its own line.",
         "One line of text.", "Two lines, both equal to the input line.", "The line is at most 60 characters long."),
     ar=("مرتين", "اقرأ سطرًا واحدًا من النص (قد يحتوي على مسافات) ثم اطبعه مرتين، كل مرة في سطر.",
         "سطر واحد من النص.", "سطران، كلاهما يساوي سطر المدخل.", "السطر 60 محرفًا على الأكثر."))

# ================================= MUDAH (8) =================================

soal("total-rupiah", "mudah", "trimmed", "print(f\"{r.randint(100, 999999) / 100:.2f}\", r.randint(1, 20))\n",
     cpp("    double harga;\n    int banyak;\n    cin >> harga >> banyak;\n    cout << \"Rp \" << fixed << setprecision(2) << harga * banyak << endl;", "iomanip"),
     id=("Total dalam Rupiah", "Sebuah barang berharga **h** rupiah (bilangan pecahan dengan dua angka di belakang koma). Seorang pembeli mengambil **n** barang.\n\n"
         "Cetak totalnya dengan awalan `Rp ` dan tepat dua angka di belakang koma.\n\nContoh: `1250.50 4` menghasilkan `Rp 5002.00`.",
         "Satu baris berisi h dan n.", "Satu baris: `Rp `, lalu total dengan dua angka di belakang koma.", "1 ≤ h < 10000; 1 ≤ n ≤ 20"),
     en=("Total in Rupiah", "An item costs **h** rupiah (a real number with two digits after the decimal point). A customer takes **n** of them.\n\n"
         "Print the total with the prefix `Rp ` and exactly two digits after the decimal point.\n\nExample: `1250.50 4` gives `Rp 5002.00`.",
         "One line with h and n.", "One line: `Rp `, then the total with two digits after the decimal point.", "1 ≤ h < 10000; 1 ≤ n ≤ 20"),
     ar=("الإجمالي بالروبية", "سلعة سعرها **h** روبية (عدد عشري برقمين بعد الفاصلة). أخذ مشترٍ **n** قطعة منها.\n\n"
         "اطبع الإجمالي مسبوقًا بـ `Rp ` وبرقمين بالضبط بعد الفاصلة.\n\nمثال: `1250.50 4` يعطي `Rp 5002.00`.",
         "سطر واحد فيه h و n.", "سطر واحد: `Rp ` ثم الإجمالي برقمين بعد الفاصلة.", "1 ≤ h < 10000؛ 1 ≤ n ≤ 20"))

soal("huruf-terakhir", "mudah", "trimmed", ACAK + "print(acak(1, 15))\n",
     cpp("    string kata;\n    cin >> kata;\n    cout << kata[kata.size() - 1] << endl;", "string"),
     id=("Huruf Terakhir", "Baca sebuah kata, lalu cetak huruf terakhirnya.\n\nHuruf pertama bernomor 0, jadi huruf terakhir bernomor panjang kata dikurangi satu.",
         "Satu kata dari huruf kecil.", "Satu huruf.", "Panjang kata 1 sampai 15 huruf."),
     en=("Last Letter", "Read a word, then print its last letter.\n\nThe first letter has position 0, so the last letter has position length minus one.",
         "One word of lowercase letters.", "One letter.", "The word is 1 to 15 letters long."),
     ar=("الحرف الأخير", "اقرأ كلمة ثم اطبع حرفها الأخير.\n\nموضع الحرف الأول 0، فموضع الحرف الأخير هو طول الكلمة ناقص واحد.",
         "كلمة واحدة من حروف لاتينية صغيرة.", "حرف واحد.", "طول الكلمة من 1 إلى 15 حرفًا."))

soal("inisial", "mudah", "trimmed", NAMA + MARGA + "print(r.choice(NAMA), r.choice(MARGA))\n",
     cpp("    string depan, belakang;\n    cin >> depan >> belakang;\n    cout << depan[0] << \".\" << belakang[0] << \".\" << endl;", "string"),
     id=("Inisial", "Baca nama depan dan nama belakang seseorang, lalu cetak inisialnya: huruf pertama tiap nama, masing-masing diikuti titik.\n\nUntuk `Dewi Lestari` keluarannya `D.L.`",
         "Satu baris berisi nama depan dan nama belakang.", "Inisial dengan titik, tanpa spasi.", "Tiap nama diawali huruf besar dan paling panjang 20 huruf."),
     en=("Initials", "Read a person's first name and last name, then print the initials: the first letter of each name, each followed by a full stop.\n\nFor `Dewi Lestari` the output is `D.L.`",
         "One line with the first name and the last name.", "The initials with full stops, without spaces.", "Each name starts with a capital letter and is at most 20 letters long."),
     ar=("الأحرف الأولى", "اقرأ الاسم الأول واسم العائلة لشخص ثم اطبع الأحرف الأولى: أول حرف من كل اسم متبوعًا بنقطة.\n\nعند `Dewi Lestari` يكون المخرج `D.L.`",
         "سطر واحد فيه الاسم الأول واسم العائلة.", "الأحرف الأولى مع النقاط، بلا مسافات.", "كل اسم يبدأ بحرف كبير وطوله 20 حرفًا على الأكثر."))

soal("tiga-digit", "mudah", "trimmed", "print({2: 7, 3: 0, 4: 999, 5: 42}.get(i, r.randint(0, 999)))\n",
     cpp("    int n;\n    cin >> n;\n    cout << setfill('0') << setw(3) << n << endl;", "iomanip"),
     id=("Tiga Digit", "Nomor urut peserta selalu ditulis tiga digit: `7` ditulis `007`, `42` ditulis `042`, `315` tetap `315`.\n\nBaca nomor urut, lalu cetak dalam bentuk tiga digit.\n\nPetunjuk: `setfill` dan `setw`.",
         "Satu bilangan bulat n.", "Bilangan itu dalam tiga digit, dengan nol di depan bila perlu.", "0 ≤ n ≤ 999"),
     en=("Three Digits", "A participant number is always written with three digits: `7` is written `007`, `42` is written `042`, `315` stays `315`.\n\nRead the number, then print it in three-digit form.\n\nHint: `setfill` and `setw`.",
         "One integer n.", "The number in three digits, with leading zeros where needed.", "0 ≤ n ≤ 999"),
     ar=("ثلاثة أرقام", "يُكتب رقم المشارك دائمًا بثلاثة أرقام: `7` يُكتب `007` و `42` يُكتب `042` و `315` يبقى `315`.\n\nاقرأ الرقم ثم اطبعه بصيغة الأرقام الثلاثة.\n\nتلميح: `setfill` و `setw`.",
         "عدد صحيح واحد n.", "العدد بثلاثة أرقام، مع أصفار في البداية عند الحاجة.", "0 ≤ n ≤ 999"))

soal("jam-digital", "mudah", "trimmed", "j, m = r.randint(0, 23), r.randint(0, 59)\nif i == 2:\n    j, m = 7, 5\nif i == 3:\n    j, m = 0, 0\nprint(j, m)\n",
     cpp("    int jam, menit;\n    cin >> jam >> menit;\n    cout << setfill('0') << setw(2) << jam << \":\" << setw(2) << menit << endl;", "iomanip"),
     id=("Jam Digital", "Jam digital menampilkan waktu sebagai `JJ:MM`, selalu dua digit untuk jam dan dua digit untuk menit.\n\nBaca jam dan menit, lalu cetak seperti jam digital. `7 5` menjadi `07:05`.\n\n"
         "Ingat: `setw` hanya berlaku untuk satu keluaran berikutnya.",
         "Satu baris berisi jam dan menit.", "Waktu dalam bentuk `JJ:MM`.", "0 ≤ jam ≤ 23; 0 ≤ menit ≤ 59"),
     en=("Digital Clock", "A digital clock shows the time as `HH:MM`, always two digits for the hour and two for the minute.\n\nRead the hour and the minute, then print them like a digital clock. `7 5` becomes `07:05`.\n\n"
         "Remember: `setw` applies only to the next output.",
         "One line with the hour and the minute.", "The time in the form `HH:MM`.", "0 ≤ hour ≤ 23; 0 ≤ minute ≤ 59"),
     ar=("ساعة رقمية", "تعرض الساعة الرقمية الوقت بالصيغة `HH:MM`، دائمًا برقمين للساعة ورقمين للدقيقة.\n\nاقرأ الساعة والدقيقة ثم اطبعهما كساعة رقمية. `7 5` يصبح `07:05`.\n\n"
         "تذكّر: `setw` يؤثر في المخرج التالي فقط.",
         "سطر واحد فيه الساعة والدقيقة.", "الوقت بالصيغة `HH:MM`.", "0 ≤ الساعة ≤ 23؛ 0 ≤ الدقيقة ≤ 59"))

soal("persen-satu-desimal", "mudah", "trimmed",
     "t = r.choice([3, 7, 9, 11, 13])\nb = {2: 0, 3: t}.get(i, r.randint(0, t))\nprint(b, t)\n",
     cpp("    int benar, total;\n    cin >> benar >> total;\n    cout << fixed << setprecision(1) << 100.0 * benar / total << \"%\" << endl;", "iomanip"),
     id=("Persen Satu Desimal", "Seorang mahasiswa menjawab benar **b** dari **t** soal. Cetak persentasenya dengan **satu angka** di belakang koma, langsung diikuti tanda `%`.\n\n"
         "Contoh: 2 benar dari 3 soal dicetak `66.7%`.",
         "Satu baris berisi b dan t.", "Persentase dengan satu angka di belakang koma dan tanda `%`.", "0 ≤ b ≤ t; t salah satu dari 3, 7, 9, 11, 13."),
     en=("Percent with One Decimal", "A student answered **b** of **t** questions correctly. Print the percentage with **one digit** after the decimal point, immediately followed by `%`.\n\n"
         "Example: 2 correct out of 3 is printed as `66.7%`.",
         "One line with b and t.", "The percentage with one digit after the decimal point and the `%` sign.", "0 ≤ b ≤ t; t is one of 3, 7, 9, 11, 13."),
     ar=("نسبة مئوية برقم عشري واحد", "أجاب طالب إجابة صحيحة عن **b** من **t** سؤالًا. اطبع النسبة المئوية **برقم واحد** بعد الفاصلة، تليها مباشرة العلامة `%`.\n\n"
         "مثال: إجابتان صحيحتان من 3 تُطبع `66.7%`.",
         "سطر واحد فيه b و t.", "النسبة المئوية برقم واحد بعد الفاصلة مع العلامة `%`.", "0 ≤ b ≤ t؛ t أحد الأعداد 3، 7، 9، 11، 13."))

soal("tiga-huruf-pertama", "mudah", "trimmed", ACAK + "print(acak(3, 15))\n",
     cpp("    string kata;\n    cin >> kata;\n    cout << kata.substr(0, 3) << endl;", "string"),
     id=("Tiga Huruf Pertama", "Baca sebuah kata, lalu cetak tiga huruf pertamanya.\n\nUntuk `komputer` keluarannya `kom`.\n\nPetunjuk: `substr(mulai, panjang)` mengambil sebagian dari sebuah `string`.",
         "Satu kata dari huruf kecil.", "Tiga huruf.", "Panjang kata 3 sampai 15 huruf."),
     en=("First Three Letters", "Read a word, then print its first three letters.\n\nFor `komputer` the output is `kom`.\n\nHint: `substr(start, length)` takes a part of a `string`.",
         "One word of lowercase letters.", "Three letters.", "The word is 3 to 15 letters long."),
     ar=("أول ثلاثة أحرف", "اقرأ كلمة ثم اطبع أول ثلاثة أحرف منها.\n\nللكلمة `komputer` يكون المخرج `kom`.\n\nتلميح: `substr(البداية, الطول)` يأخذ جزءًا من `string`.",
         "كلمة واحدة من حروف لاتينية صغيرة.", "ثلاثة أحرف.", "طول الكلمة من 3 إلى 15 حرفًا."))

soal("panjang-kalimat", "mudah", "trimmed", KALIMAT + "print(kalimat(1, 6))\n",
     cpp("    string kalimat;\n    getline(cin, kalimat);\n    cout << kalimat.size() << endl;", "string"),
     id=("Panjang Kalimat", "Baca satu baris kalimat, lalu cetak banyak karakternya. Spasi ikut dihitung.\n\nUntuk `buku tugas` jawabannya 10.",
         "Satu baris kalimat dari huruf kecil dan spasi.", "Satu bilangan: banyak karakter di baris itu.", "Kalimat paling panjang 60 karakter, tanpa spasi di awal atau akhir."),
     en=("Length of a Sentence", "Read one line holding a sentence, then print how many characters it has. Spaces count.\n\nFor `buku tugas` the answer is 10.",
         "One line with a sentence of lowercase letters and spaces.", "One number: the number of characters in the line.", "The sentence is at most 60 characters long, with no space at the start or end."),
     ar=("طول الجملة", "اقرأ سطرًا فيه جملة ثم اطبع عدد محارفها. المسافات تُحسب.\n\nللجملة `buku tugas` الجواب 10.",
         "سطر واحد فيه جملة من حروف لاتينية صغيرة ومسافات.", "عدد واحد: عدد المحارف في السطر.", "الجملة 60 محرفًا على الأكثر، بلا مسافة في أولها أو آخرها."))

# ============================== MUDAH-SEDANG (6) ==============================

soal("baris-tabel", "mudah_sedang", "trimmed", NAMA + "print(r.choice(NAMA), {2: 100, 3: 0, 4: 7}.get(i, r.randint(0, 100)))\n",
     cpp("    string nama;\n    int nilai;\n    cin >> nama >> nilai;\n    cout << left << setw(10) << nama << right << setw(5) << nilai << endl;", "iomanip", "string"),
     id=("Baris Tabel", "Sebuah daftar nilai dicetak sebagai tabel. Tiap barisnya: **nama rata kiri dalam lebar 10**, langsung diikuti **nilai rata kanan dalam lebar 5**.\n\n"
         "Untuk `Budi 87` keluarannya `Budi` diikuti enam spasi, lalu tiga spasi dan `87` — seluruhnya 15 karakter.\n\nPetunjuk: `left`, `right`, dan `setw`.",
         "Satu baris berisi nama (satu kata) dan nilai.", "Satu baris tabel selebar 15 karakter.", "Nama paling panjang 10 huruf; 0 ≤ nilai ≤ 100"),
     en=("Table Row", "A list of marks is printed as a table. Each row is the **name left-aligned in a width of 10**, immediately followed by the **mark right-aligned in a width of 5**.\n\n"
         "For `Budi 87` the output is `Budi` followed by six spaces, then three spaces and `87` — 15 characters in all.\n\nHint: `left`, `right` and `setw`.",
         "One line with the name (one word) and the mark.", "One table row 15 characters wide.", "The name is at most 10 letters long; 0 ≤ mark ≤ 100"),
     ar=("صف في جدول", "تُطبع قائمة درجات على شكل جدول. كل صف: **الاسم محاذًى إلى اليسار في عرض 10** تليه مباشرة **الدرجة محاذاةً إلى اليمين في عرض 5**.\n\n"
         "عند `Budi 87` يكون المخرج `Budi` تليه ست مسافات ثم ثلاث مسافات و `87` — أي 15 محرفًا.\n\nتلميح: `left` و `right` و `setw`.",
         "سطر واحد فيه الاسم (كلمة واحدة) والدرجة.", "صف جدول عرضه 15 محرفًا.", "الاسم 10 حروف على الأكثر؛ 0 ≤ الدرجة ≤ 100"))

soal("tanggal", "mudah_sedang", "trimmed", "h, b, t = r.randint(1, 28), r.randint(1, 12), r.randint(1900, 2100)\nif i == 2:\n    h, b, t = 5, 8, 2026\nif i == 3:\n    h, b = 17, 11\nprint(h, b, t)\n",
     cpp("    int hari, bulan, tahun;\n    cin >> hari >> bulan >> tahun;\n    cout << setfill('0') << setw(2) << hari << \"/\" << setw(2) << bulan << \"/\" << tahun << endl;", "iomanip"),
     id=("Tanggal", "Baca hari, bulan, dan tahun, lalu cetak tanggalnya dalam bentuk `HH/BB/TTTT`: hari dan bulan selalu dua digit.\n\nUntuk `5 8 2026` keluarannya `05/08/2026`.",
         "Satu baris berisi tiga bilangan bulat: hari, bulan, tahun.", "Tanggal dalam bentuk `HH/BB/TTTT`.", "1 ≤ hari ≤ 28; 1 ≤ bulan ≤ 12; 1900 ≤ tahun ≤ 2100"),
     en=("Date", "Read the day, month and year, then print the date in the form `DD/MM/YYYY`: the day and the month always have two digits.\n\nFor `5 8 2026` the output is `05/08/2026`.",
         "One line with three integers: day, month, year.", "The date in the form `DD/MM/YYYY`.", "1 ≤ day ≤ 28; 1 ≤ month ≤ 12; 1900 ≤ year ≤ 2100"),
     ar=("التاريخ", "اقرأ اليوم والشهر والسنة ثم اطبع التاريخ بالصيغة `DD/MM/YYYY`: اليوم والشهر دائمًا برقمين.\n\nعند `5 8 2026` يكون المخرج `05/08/2026`.",
         "سطر واحد فيه ثلاثة أعداد صحيحة: اليوم، الشهر، السنة.", "التاريخ بالصيغة `DD/MM/YYYY`.", "1 ≤ اليوم ≤ 28؛ 1 ≤ الشهر ≤ 12؛ 1900 ≤ السنة ≤ 2100"))

soal("suhu-dua-satuan", "mudah_sedang", "trimmed", "k = {2: 3650, 3: 0, 4: -4000}.get(i, r.randint(-5000, 15000))\nprint(f\"{k / 100:.2f}\")\n",
     cpp("    double c;\n    cin >> c;\n    double f = c * 9 / 5 + 32;\n    cout << fixed << setprecision(2) << c << \" C = \" << f << \" F\" << endl;", "iomanip"),
     id=("Suhu dalam Dua Satuan", "Baca suhu dalam Celsius (bilangan pecahan), lalu cetak suhu itu dan padanannya dalam Fahrenheit, keduanya dengan dua angka di belakang koma.\n\n"
         "Fahrenheit = Celsius × 9 / 5 + 32. Untuk `36.50` keluarannya `36.50 C = 97.70 F`.",
         "Satu bilangan pecahan: suhu dalam Celsius, dengan dua angka di belakang koma.", "Satu baris berbentuk `<celsius> C = <fahrenheit> F`.", "−50 ≤ suhu ≤ 150"),
     en=("Temperature in Two Units", "Read a temperature in Celsius (a real number), then print it and its equivalent in Fahrenheit, both with two digits after the decimal point.\n\n"
         "Fahrenheit = Celsius × 9 / 5 + 32. For `36.50` the output is `36.50 C = 97.70 F`.",
         "One real number: the temperature in Celsius, with two digits after the decimal point.", "One line of the form `<celsius> C = <fahrenheit> F`.", "−50 ≤ temperature ≤ 150"),
     ar=("الحرارة بوحدتين", "اقرأ درجة حرارة بالمئوي (عدد عشري) ثم اطبعها مع ما يقابلها بالفهرنهايت، كلتاهما برقمين بعد الفاصلة.\n\n"
         "الفهرنهايت = المئوي × 9 / 5 + 32. عند `36.50` يكون المخرج `36.50 C = 97.70 F`.",
         "عدد عشري واحد: الحرارة بالمئوي، برقمين بعد الفاصلة.", "سطر واحد بالصيغة `<celsius> C = <fahrenheit> F`.", "−50 ≤ الحرارة ≤ 150"))

soal("huruf-tengah", "mudah_sedang", "trimmed", "n = {2: 1, 3: 15}.get(i, r.randint(0, 7) * 2 + 1)\nprint(''.join(r.choice('abcdefghijklmnopqrstuvwxyz') for _ in range(n)))\n",
     cpp("    string kata;\n    cin >> kata;\n    cout << kata[kata.size() / 2] << endl;", "string"),
     id=("Huruf Tengah", "Baca sebuah kata yang banyak hurufnya ganjil, lalu cetak huruf yang tepat berada di tengahnya.\n\nUntuk `layar` (5 huruf) jawabannya `y`.",
         "Satu kata dari huruf kecil.", "Satu huruf.", "Panjang kata ganjil, 1 sampai 15 huruf."),
     en=("Middle Letter", "Read a word with an odd number of letters, then print the letter exactly in the middle.\n\nFor `layar` (5 letters) the answer is `y`.",
         "One word of lowercase letters.", "One letter.", "The length of the word is odd, 1 to 15 letters."),
     ar=("الحرف الأوسط", "اقرأ كلمة عدد حروفها فردي ثم اطبع الحرف الواقع في وسطها تمامًا.\n\nللكلمة `layar` (5 حروف) الجواب `y`.",
         "كلمة واحدة من حروف لاتينية صغيرة.", "حرف واحد.", "طول الكلمة فردي، من 1 إلى 15 حرفًا."))

soal("alamat-surel", "mudah_sedang", "trimmed",
     "depan = ['andi', 'budi', 'citra', 'dewi', 'eko', 'gita', 'hadi', 'lina', 'rudi', 'sari']\nbelakang = ['saputra', 'wijaya', 'lestari', 'pratama', 'utami', 'halim']\n"
     "domain = ['kampus.ac.id', 'lokandita.com', 'surat.id', 'contoh.org']\nprint(r.choice(depan), r.choice(belakang), r.choice(domain))\n",
     cpp("    string depan, belakang, domain;\n    cin >> depan >> belakang >> domain;\n    string alamat = depan + \".\" + belakang + \"@\" + domain;\n    cout << alamat << endl;\n    cout << alamat.size() << endl;", "string"),
     id=("Alamat Surel", "Sebuah kampus membuat alamat surel mahasiswa dari nama depan, nama belakang, dan nama domain: `depan.belakang@domain`.\n\n"
         "Baca ketiganya, cetak alamat surelnya, lalu di baris berikutnya cetak panjang alamat itu.\n\nUntuk `dewi lestari kampus.ac.id`: `dewi.lestari@kampus.ac.id` dan `25`.",
         "Satu baris berisi tiga kata: nama depan, nama belakang, domain.", "Baris pertama: alamat surel. Baris kedua: banyak karakternya.", "Tiap kata paling panjang 20 karakter, tanpa spasi."),
     en=("Email Address", "A campus builds student email addresses from the first name, the last name and the domain name: `first.last@domain`.\n\n"
         "Read the three, print the email address, then on the next line print the length of that address.\n\nFor `dewi lestari kampus.ac.id`: `dewi.lestari@kampus.ac.id` and `25`.",
         "One line with three words: first name, last name, domain.", "First line: the email address. Second line: its number of characters.", "Each word is at most 20 characters long, without spaces."),
     ar=("عنوان البريد الإلكتروني", "تُنشئ جامعة عناوين بريد الطلاب من الاسم الأول واسم العائلة واسم النطاق: `first.last@domain`.\n\n"
         "اقرأ الثلاثة واطبع عنوان البريد، ثم اطبع في السطر التالي طول ذلك العنوان.\n\nعند `dewi lestari kampus.ac.id`: `dewi.lestari@kampus.ac.id` و `25`.",
         "سطر واحد فيه ثلاث كلمات: الاسم الأول، اسم العائلة، النطاق.", "السطر الأول: عنوان البريد. السطر الثاني: عدد محارفه.", "كل كلمة 20 محرفًا على الأكثر، بلا مسافات."))

soal("rata-rata-dua-desimal", "mudah_sedang", "trimmed", "print(r.randint(0, 100), r.randint(0, 100), r.randint(0, 100))\n",
     cpp("    int a, b, c;\n    cin >> a >> b >> c;\n    double rata = (double) (a + b + c) / 3;\n    cout << fixed << setprecision(2) << rata << endl;", "iomanip"),
     id=("Rata-Rata Dua Desimal", "Tiga nilai ujian berupa bilangan bulat. Cetak rata-ratanya dengan tepat dua angka di belakang koma.\n\nUntuk `80 75 91` keluarannya `82.00`; untuk `70 80 85` keluarannya `78.33`.",
         "Satu baris berisi tiga bilangan bulat.", "Rata-rata dengan dua angka di belakang koma.", "0 ≤ setiap nilai ≤ 100"),
     en=("Average with Two Decimals", "Three exam marks are integers. Print their average with exactly two digits after the decimal point.\n\nFor `80 75 91` the output is `82.00`; for `70 80 85` it is `78.33`.",
         "One line with three integers.", "The average with two digits after the decimal point.", "0 ≤ each mark ≤ 100"),
     ar=("المتوسط برقمين عشريين", "ثلاث درجات امتحان أعداد صحيحة. اطبع متوسطها برقمين بالضبط بعد الفاصلة.\n\nعند `80 75 91` يكون المخرج `82.00`، وعند `70 80 85` يكون `78.33`.",
         "سطر واحد فيه ثلاثة أعداد صحيحة.", "المتوسط برقمين بعد الفاصلة.", "0 ≤ كل درجة ≤ 100"))

# ================================= SEDANG (6) =================================

soal("struk-belanja", "sedang", "trimmed",
     "barang = ['Pensil', 'Buku', 'Penghapus', 'Penggaris', 'Spidol', 'Map', 'Kertas', 'Tinta']\nprint(r.choice(barang), r.randint(1, 200) * 500, {2: 1, 3: 99}.get(i, r.randint(1, 99)))\n",
     cpp("    string barang;\n    int harga, banyak;\n    cin >> barang >> harga >> banyak;\n"
         "    cout << left << setw(12) << barang << right << setw(3) << banyak << endl;\n"
         "    cout << left << setw(12) << \"Total\" << right << setw(10) << harga * banyak << endl;", "iomanip", "string"),
     id=("Struk Belanja", "Kasir mencetak struk dua baris untuk satu jenis barang.\n\n"
         "- Baris pertama: **nama barang rata kiri dalam lebar 12**, lalu **banyaknya rata kanan dalam lebar 3**.\n"
         "- Baris kedua: kata `Total` **rata kiri dalam lebar 12**, lalu **harga × banyak rata kanan dalam lebar 10**.\n\n"
         "Baca nama barang, harga satuan, dan banyaknya.",
         "Satu baris berisi nama barang (satu kata), harga satuan, dan banyaknya.", "Dua baris seperti dijelaskan di atas.", "Nama barang paling panjang 12 huruf; 500 ≤ harga ≤ 100000; 1 ≤ banyak ≤ 99"),
     en=("Shopping Receipt", "A cashier prints a two-line receipt for one kind of item.\n\n"
         "- First line: the **item name left-aligned in a width of 12**, then the **quantity right-aligned in a width of 3**.\n"
         "- Second line: the word `Total` **left-aligned in a width of 12**, then **price × quantity right-aligned in a width of 10**.\n\n"
         "Read the item name, the unit price and the quantity.",
         "One line with the item name (one word), the unit price and the quantity.", "Two lines as described above.", "The item name is at most 12 letters long; 500 ≤ price ≤ 100000; 1 ≤ quantity ≤ 99"),
     ar=("إيصال الشراء", "يطبع أمين الصندوق إيصالًا من سطرين لنوع واحد من السلع.\n\n"
         "- السطر الأول: **اسم السلعة محاذًى إلى اليسار في عرض 12** ثم **الكمية محاذاةً إلى اليمين في عرض 3**.\n"
         "- السطر الثاني: الكلمة `Total` **محاذاةً إلى اليسار في عرض 12** ثم **السعر × الكمية محاذًى إلى اليمين في عرض 10**.\n\n"
         "اقرأ اسم السلعة وسعر الوحدة والكمية.",
         "سطر واحد فيه اسم السلعة (كلمة واحدة) وسعر الوحدة والكمية.", "سطران كما وُصف أعلاه.", "اسم السلعة 12 حرفًا على الأكثر؛ 500 ≤ السعر ≤ 100000؛ 1 ≤ الكمية ≤ 99"))

soal("sensor-kata", "sedang", "trimmed", ACAK + "print(acak(3, 15))\n",
     cpp("    string kata;\n    cin >> kata;\n    cout << kata[0] << \"***\" << kata[kata.size() - 1] << endl;", "string"),
     id=("Sensor Kata", "Sebuah kata sandi ditampilkan tersamar: hanya huruf pertama dan huruf terakhirnya yang terlihat, dan di antaranya selalu tertulis tiga bintang, berapa pun panjang katanya.\n\n"
         "Untuk `komputer` keluarannya `k***r`.",
         "Satu kata dari huruf kecil.", "Huruf pertama, tiga bintang, huruf terakhir.", "Panjang kata 3 sampai 15 huruf."),
     en=("Masked Word", "A password is shown masked: only its first and last letters are visible, and between them there are always three asterisks, whatever the length of the word.\n\n"
         "For `komputer` the output is `k***r`.",
         "One word of lowercase letters.", "The first letter, three asterisks, the last letter.", "The word is 3 to 15 letters long."),
     ar=("كلمة محجوبة", "تُعرض كلمة مرور محجوبة: لا يظهر منها إلا حرفها الأول وحرفها الأخير، وبينهما دائمًا ثلاث نجمات مهما كان طول الكلمة.\n\n"
         "للكلمة `komputer` يكون المخرج `k***r`.",
         "كلمة واحدة من حروف لاتينية صغيرة.", "الحرف الأول ثم ثلاث نجمات ثم الحرف الأخير.", "طول الكلمة من 3 إلى 15 حرفًا."))

soal("stopwatch", "sedang", "trimmed", "print({2: 3725, 3: 0, 4: 359999, 5: 59}.get(i, r.randint(0, 359999)))\n",
     cpp("    int d;\n    cin >> d;\n    cout << setfill('0') << setw(2) << d / 3600 << \":\" << setw(2) << d % 3600 / 60 << \":\" << setw(2) << d % 60 << endl;", "iomanip"),
     id=("Stopwatch", "Sebuah stopwatch sudah berjalan **d** detik. Cetak tampilannya dalam bentuk `JJ:MM:DD` — jam, menit, dan detik, masing-masing dua digit.\n\nUntuk `3725` keluarannya `01:02:05`.",
         "Satu bilangan bulat d.", "Waktu dalam bentuk `JJ:MM:DD`.", "0 ≤ d < 360000"),
     en=("Stopwatch", "A stopwatch has been running for **d** seconds. Print its display in the form `HH:MM:SS` — hours, minutes and seconds, two digits each.\n\nFor `3725` the output is `01:02:05`.",
         "One integer d.", "The time in the form `HH:MM:SS`.", "0 ≤ d < 360000"),
     ar=("ساعة الإيقاف", "ساعة إيقاف تعمل منذ **d** ثانية. اطبع ما تعرضه بالصيغة `HH:MM:SS` — الساعات والدقائق والثواني، كلٌّ برقمين.\n\nعند `3725` يكون المخرج `01:02:05`.",
         "عدد صحيح واحد d.", "الوقت بالصيغة `HH:MM:SS`.", "0 ≤ d < 360000"))

soal("nama-belakang-dulu", "sedang", "trimmed", NAMA + MARGA + "print(r.choice(NAMA), r.choice(MARGA))\n",
     cpp("    string depan, belakang;\n    cin >> depan >> belakang;\n    cout << belakang << \", \" << depan[0] << \".\" << endl;", "string"),
     id=("Nama Belakang Dulu", "Dalam daftar pustaka, nama penulis ditulis dengan nama belakang lebih dulu, diikuti koma, spasi, inisial nama depan, dan titik.\n\n"
         "Untuk `Dewi Lestari` keluarannya `Lestari, D.`",
         "Satu baris berisi nama depan dan nama belakang.", "Nama dalam bentuk daftar pustaka.", "Tiap nama diawali huruf besar dan paling panjang 20 huruf."),
     en=("Last Name First", "In a bibliography an author is written with the last name first, followed by a comma, a space, the initial of the first name and a full stop.\n\n"
         "For `Dewi Lestari` the output is `Lestari, D.`",
         "One line with the first name and the last name.", "The name in bibliography form.", "Each name starts with a capital letter and is at most 20 letters long."),
     ar=("اسم العائلة أولًا", "في قائمة المراجع يُكتب اسم المؤلف باسم العائلة أولًا، تليه فاصلة ثم مسافة ثم الحرف الأول من الاسم الأول ثم نقطة.\n\n"
         "عند `Dewi Lestari` يكون المخرج `Lestari, D.`",
         "سطر واحد فيه الاسم الأول واسم العائلة.", "الاسم بصيغة قائمة المراجع.", "كل اسم يبدأ بحرف كبير وطوله 20 حرفًا على الأكثر."))

soal("ipk", "sedang", "trimmed", "s = r.choice([18, 19, 20, 21, 22, 23])\nprint({2: 4 * s, 3: 0}.get(i, r.randint(0, 4 * s)), s)\n",
     cpp("    int mutu, sks;\n    cin >> mutu >> sks;\n    cout << \"IPK: \" << fixed << setprecision(2) << (double) mutu / sks << endl;", "iomanip"),
     id=("IPK", "Indeks prestasi adalah jumlah angka mutu dibagi jumlah SKS. Keduanya bilangan bulat, tetapi hasilnya bilangan pecahan.\n\n"
         "Baca jumlah angka mutu dan jumlah SKS, lalu cetak `IPK: ` diikuti indeks prestasi dengan dua angka di belakang koma.\n\nUntuk `69 20` keluarannya `IPK: 3.45`.",
         "Satu baris berisi jumlah angka mutu dan jumlah SKS.", "Satu baris: `IPK: ` lalu nilainya dengan dua angka di belakang koma.", "18 ≤ SKS ≤ 23; 0 ≤ angka mutu ≤ 4 × SKS"),
     en=("Grade Point Average", "A grade point average is the total grade points divided by the total credits. Both are integers, but the result is a real number.\n\n"
         "Read the total grade points and the total credits, then print `IPK: ` followed by the average with two digits after the decimal point.\n\nFor `69 20` the output is `IPK: 3.45`.",
         "One line with the total grade points and the total credits.", "One line: `IPK: ` then the value with two digits after the decimal point.", "18 ≤ credits ≤ 23; 0 ≤ grade points ≤ 4 × credits"),
     ar=("المعدل التراكمي", "المعدل التراكمي هو مجموع نقاط التقدير مقسومًا على مجموع الساعات المعتمدة. كلاهما عدد صحيح لكن الناتج عدد عشري.\n\n"
         "اقرأ مجموع النقاط ومجموع الساعات ثم اطبع `IPK: ` يليه المعدل برقمين بعد الفاصلة.\n\nعند `69 20` يكون المخرج `IPK: 3.45`.",
         "سطر واحد فيه مجموع النقاط ومجموع الساعات المعتمدة.", "سطر واحد: `IPK: ` ثم القيمة برقمين بعد الفاصلة.", "18 ≤ الساعات ≤ 23؛ 0 ≤ النقاط ≤ 4 × الساعات"))

soal("potong-kalimat", "sedang", "trimmed", KALIMAT + "k = kalimat(3, 8)\nprint(k)\nprint({2: 1, 3: len(k)}.get(i, r.randint(1, len(k))))\n",
     cpp("    string kalimat;\n    int n;\n    getline(cin, kalimat);\n    cin >> n;\n    cout << kalimat.substr(0, n) << \"...\" << endl;", "string"),
     id=("Potong Kalimat", "Sebuah judul berita terlalu panjang untuk layar ponsel. Tampilkan hanya **n karakter pertamanya**, langsung diikuti tiga titik.\n\n"
         "Untuk kalimat `belajar program komputer` dan n = 7 keluarannya `belajar...`",
         "Baris pertama: kalimat. Baris kedua: bilangan bulat n.", "n karakter pertama kalimat itu, diikuti `...`", "Kalimat paling panjang 80 karakter; 1 ≤ n ≤ panjang kalimat"),
     en=("Cut the Sentence", "A news headline is too long for a phone screen. Show only its **first n characters**, immediately followed by three dots.\n\n"
         "For the sentence `belajar program komputer` and n = 7 the output is `belajar...`",
         "First line: the sentence. Second line: an integer n.", "The first n characters of the sentence, followed by `...`", "The sentence is at most 80 characters long; 1 ≤ n ≤ the length of the sentence"),
     ar=("اقتطاع الجملة", "عنوان خبر أطول من أن تعرضه شاشة الهاتف. اعرض **أول n محرفًا** منه فقط، تليها مباشرة ثلاث نقاط.\n\n"
         "للجملة `belajar program komputer` و n = 7 يكون المخرج `belajar...`",
         "السطر الأول: الجملة. السطر الثاني: عدد صحيح n.", "أول n محرفًا من الجملة تليها `...`", "الجملة 80 محرفًا على الأكثر؛ 1 ≤ n ≤ طول الجملة"))

# ================================== SULIT (4) ==================================

soal("kartu-mahasiswa", "sulit", "trimmed", NAMA + MARGA + "print(r.randint(17, 30))\nprint(r.choice(NAMA), r.choice(MARGA))\nprint(f\"{r.randint(1400, 1950) / 10:.1f}\")\n",
     cpp("    int umur;\n    string nama;\n    double tinggi;\n    cin >> umur;\n    cin.ignore();\n    getline(cin, nama);\n    cin >> tinggi;\n"
         "    cout << \"Nama   : \" << nama << endl;\n    cout << \"Umur   : \" << umur << \" tahun\" << endl;\n"
         "    cout << \"Tinggi : \" << fixed << setprecision(1) << tinggi << \" cm\" << endl;", "iomanip", "string"),
     id=("Kartu Mahasiswa", "Data seorang mahasiswa diberikan dalam tiga baris: **umur**, lalu **nama lengkap** (bisa lebih dari satu kata), lalu **tinggi badan** dalam sentimeter.\n\n"
         "Cetak kartunya persis seperti ini (untuk masukan `19`, `Dewi Lestari`, `158.5`):\n\n```\nNama   : Dewi Lestari\nUmur   : 19 tahun\nTinggi : 158.5 cm\n```\n\n"
         "Tinggi dicetak dengan satu angka di belakang koma.\n\n"
         "Hati-hati: sesudah `cin >> umur`, sisa baris pertama (tanda ganti baris) masih tertinggal, dan `getline` berikutnya akan membacanya sebagai baris kosong. "
         "`cin.ignore()` membuang satu karakter yang tertinggal itu.",
         "Tiga baris: umur (bilangan bulat), nama lengkap, tinggi (satu angka di belakang koma).", "Tiga baris seperti contoh.", "17 ≤ umur ≤ 30; 140 ≤ tinggi < 200; nama paling panjang 40 karakter"),
     en=("Student Card", "A student's data comes in three lines: the **age**, then the **full name** (it can be more than one word), then the **height** in centimetres.\n\n"
         "Print the card exactly like this (for the input `19`, `Dewi Lestari`, `158.5`):\n\n```\nNama   : Dewi Lestari\nUmur   : 19 tahun\nTinggi : 158.5 cm\n```\n\n"
         "The height is printed with one digit after the decimal point.\n\n"
         "Careful: after `cin >> umur`, the rest of the first line (the line break) is still waiting, and the next `getline` would read it as an empty line. "
         "`cin.ignore()` throws away that one waiting character.",
         "Three lines: the age (an integer), the full name, the height (one digit after the decimal point).", "Three lines as in the example.", "17 ≤ age ≤ 30; 140 ≤ height < 200; the name is at most 40 characters long"),
     ar=("بطاقة الطالب", "تُعطى بيانات طالب في ثلاثة أسطر: **العمر** ثم **الاسم الكامل** (قد يكون أكثر من كلمة) ثم **الطول** بالسنتيمتر.\n\n"
         "اطبع البطاقة هكذا بالضبط (للمدخل `19` و `Dewi Lestari` و `158.5`):\n\n```\nNama   : Dewi Lestari\nUmur   : 19 tahun\nTinggi : 158.5 cm\n```\n\n"
         "يُطبع الطول برقم واحد بعد الفاصلة.\n\n"
         "انتبه: بعد `cin >> umur` يبقى ما تبقّى من السطر الأول (علامة نهاية السطر) في الانتظار، وسيقرؤه `getline` التالي كسطر فارغ. "
         "`cin.ignore()` يتخلص من ذلك المحرف المنتظر.",
         "ثلاثة أسطر: العمر (عدد صحيح)، الاسم الكامل، الطول (برقم واحد بعد الفاصلة).", "ثلاثة أسطر كما في المثال.", "17 ≤ العمر ≤ 30؛ 140 ≤ الطول < 200؛ الاسم 40 محرفًا على الأكثر"))

soal("nomor-antrean", "sulit", "trimmed", "print(r.choice('ABCDEFGH'), {2: 7, 3: 99, 4: 998, 5: 0}.get(i, r.randint(0, 998)))\n",
     cpp("    char loket;\n    int nomor;\n    cin >> loket >> nomor;\n    cout << setfill('0');\n    cout << loket << \"-\" << setw(3) << nomor << endl;\n    cout << loket << \"-\" << setw(3) << nomor + 1 << endl;", "iomanip"),
     id=("Nomor Antrean", "Mesin antrean sebuah bank mencetak nomor berbentuk huruf loket, tanda hubung, dan nomor urut tiga digit: `A-007`.\n\n"
         "Baca huruf loket dan nomor urut yang sedang dilayani. Cetak nomor antrean itu, lalu di baris berikutnya nomor antrean sesudahnya.\n\n"
         "Untuk `A 7` keluarannya `A-007` dan `A-008`; untuk `C 99` keluarannya `C-099` dan `C-100`.",
         "Satu baris berisi satu huruf besar dan nomor urut.", "Dua baris: nomor antrean sekarang, lalu nomor antrean berikutnya.", "Huruf A–H; 0 ≤ nomor urut ≤ 998"),
     en=("Queue Number", "A bank's queue machine prints numbers made of a counter letter, a hyphen and a three-digit sequence number: `A-007`.\n\n"
         "Read the counter letter and the sequence number now being served. Print that queue number, then on the next line the queue number after it.\n\n"
         "For `A 7` the output is `A-007` and `A-008`; for `C 99` it is `C-099` and `C-100`.",
         "One line with one uppercase letter and the sequence number.", "Two lines: the current queue number, then the next one.", "A letter A–H; 0 ≤ sequence number ≤ 998"),
     ar=("رقم الدور", "تطبع آلة الدور في مصرف أرقامًا مكوّنة من حرف الشبّاك ثم شرطة ثم رقم تسلسلي من ثلاثة أرقام: `A-007`.\n\n"
         "اقرأ حرف الشبّاك والرقم التسلسلي الجاري خدمته. اطبع رقم الدور ذلك، ثم في السطر التالي رقم الدور الذي يليه.\n\n"
         "عند `A 7` يكون المخرج `A-007` و `A-008`، وعند `C 99` يكون `C-099` و `C-100`.",
         "سطر واحد فيه حرف لاتيني كبير والرقم التسلسلي.", "سطران: رقم الدور الحالي ثم الذي يليه.", "حرف من A–H؛ 0 ≤ الرقم التسلسلي ≤ 998"))

soal("kode-buku", "sulit", "trimmed",
     "judul = ['Pemrograman', 'Algoritma', 'Kalkulus', 'Statistika', 'Jaringan', 'Basisdata', 'Fisika', 'Logika', 'Grafika', 'Robotika']\n"
     "t, n = r.randint(1990, 2029), r.randint(1, 9999)\nif i == 2:\n    t, n = 2026, 42\nif i == 3:\n    t, n = 2005, 7\nif i == 4:\n    t = 2000\nprint(r.choice(judul), t, n)\n",
     cpp("    string judul;\n    int tahun, nomor;\n    cin >> judul >> tahun >> nomor;\n"
         "    cout << judul.substr(0, 3) << \"-\" << setfill('0') << setw(2) << tahun % 100 << \"-\" << setw(4) << nomor << endl;", "iomanip", "string"),
     id=("Kode Buku", "Perpustakaan memberi setiap buku kode yang terdiri dari tiga bagian yang dipisah tanda hubung:\n\n"
         "1. tiga huruf pertama judulnya;\n2. dua digit terakhir tahun terbitnya;\n3. nomor urut buku, selalu empat digit.\n\n"
         "Untuk `Pemrograman 2026 42` kodenya `Pem-26-0042`. Untuk `Fisika 2005 7` kodenya `Fis-05-0007`.",
         "Satu baris berisi judul (satu kata), tahun terbit, dan nomor urut.", "Kode buku.", "Judul 3 sampai 20 huruf; 1990 ≤ tahun ≤ 2029; 1 ≤ nomor urut ≤ 9999"),
     en=("Book Code", "A library gives every book a code made of three parts separated by hyphens:\n\n"
         "1. the first three letters of its title;\n2. the last two digits of its year of publication;\n3. the sequence number of the book, always four digits.\n\n"
         "For `Pemrograman 2026 42` the code is `Pem-26-0042`. For `Fisika 2005 7` the code is `Fis-05-0007`.",
         "One line with the title (one word), the year of publication and the sequence number.", "The book code.", "The title is 3 to 20 letters long; 1990 ≤ year ≤ 2029; 1 ≤ sequence number ≤ 9999"),
     ar=("رمز الكتاب", "تعطي مكتبة كل كتاب رمزًا من ثلاثة أجزاء تفصل بينها شرطات:\n\n"
         "1. أول ثلاثة أحرف من عنوانه؛\n2. آخر رقمين من سنة نشره؛\n3. الرقم التسلسلي للكتاب، دائمًا بأربعة أرقام.\n\n"
         "عند `Pemrograman 2026 42` يكون الرمز `Pem-26-0042`. وعند `Fisika 2005 7` يكون `Fis-05-0007`.",
         "سطر واحد فيه العنوان (كلمة واحدة) وسنة النشر والرقم التسلسلي.", "رمز الكتاب.", "العنوان من 3 إلى 20 حرفًا؛ 1990 ≤ السنة ≤ 2029؛ 1 ≤ الرقم التسلسلي ≤ 9999"))

soal("kuitansi", "sulit", "trimmed", "h, n, p = r.randint(1, 400) * 250, r.randint(1, 50), r.choice([10, 11, 12])\nif i == 2:\n    h, n, p = 12500, 3, 11\nprint(h, n, p)\n",
     cpp("    double harga;\n    int banyak, pajak;\n    cin >> harga >> banyak >> pajak;\n    double subtotal = harga * banyak;\n    double nilaiPajak = subtotal * pajak / 100;\n"
         "    cout << fixed << setprecision(2);\n"
         "    cout << left << setw(10) << \"Subtotal\" << right << setw(12) << subtotal << endl;\n"
         "    cout << left << setw(10) << \"Pajak\" << right << setw(12) << nilaiPajak << endl;\n"
         "    cout << left << setw(10) << \"Total\" << right << setw(12) << subtotal + nilaiPajak << endl;", "iomanip"),
     id=("Kuitansi", "Sebuah toko mencetak kuitansi tiga baris. Tiap baris berisi **keterangan rata kiri dalam lebar 10**, lalu **jumlah uang rata kanan dalam lebar 12 dengan dua angka di belakang koma**.\n\n"
         "Keterangannya berturut-turut `Subtotal` (harga × banyak), `Pajak` (subtotal × persen pajak / 100), dan `Total` (subtotal ditambah pajak).\n\n"
         "Untuk harga 12500, banyak 3, dan pajak 11 persen:\n\n```\nSubtotal      37500.00\nPajak          4125.00\nTotal         41625.00\n```",
         "Satu baris berisi harga satuan, banyak barang, dan persen pajak.", "Tiga baris seperti contoh.", "250 ≤ harga ≤ 100000; 1 ≤ banyak ≤ 50; pajak 10, 11, atau 12 persen"),
     en=("Receipt", "A shop prints a three-line receipt. Each line holds a **label left-aligned in a width of 10**, then an **amount right-aligned in a width of 12 with two digits after the decimal point**.\n\n"
         "The labels are, in order, `Subtotal` (price × quantity), `Pajak` (subtotal × tax percent / 100) and `Total` (subtotal plus tax).\n\n"
         "For price 12500, quantity 3 and 11 percent tax:\n\n```\nSubtotal      37500.00\nPajak          4125.00\nTotal         41625.00\n```",
         "One line with the unit price, the quantity and the tax percentage.", "Three lines as in the example.", "250 ≤ price ≤ 100000; 1 ≤ quantity ≤ 50; the tax is 10, 11 or 12 percent"),
     ar=("إيصال", "يطبع متجر إيصالًا من ثلاثة أسطر. في كل سطر **بيان محاذًى إلى اليسار في عرض 10** ثم **مبلغ محاذًى إلى اليمين في عرض 12 برقمين بعد الفاصلة**.\n\n"
         "البيانات على الترتيب: `Subtotal` (السعر × الكمية) و `Pajak` (المجموع الفرعي × نسبة الضريبة / 100) و `Total` (المجموع الفرعي مع الضريبة).\n\n"
         "للسعر 12500 والكمية 3 والضريبة 11 بالمئة:\n\n```\nSubtotal      37500.00\nPajak          4125.00\nTotal         41625.00\n```",
         "سطر واحد فيه سعر الوحدة والكمية ونسبة الضريبة.", "ثلاثة أسطر كما في المثال.", "250 ≤ السعر ≤ 100000؛ 1 ≤ الكمية ≤ 50؛ الضريبة 10 أو 11 أو 12 بالمئة"))

tulis(__file__, "DDP101", 3, "cpp", {"mudah_sekali": 8, "mudah": 8, "mudah_sedang": 6, "sedang": 6, "sulit": 4})
