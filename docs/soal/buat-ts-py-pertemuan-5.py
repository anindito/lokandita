#!/usr/bin/env python3
"""Menyusun docs/soal/ts-py-pertemuan-5.json.

TS-PY pertemuan 5 — Struktur Data dan Fungsi (RPS Rev2 10.5): list, tuple,
dictionary, set, operasi string, fungsi dengan parameter dan nilai kembali.

Konsep yang diizinkan di produksi: sembilan konsep pertemuan 1-4 ditambah
PY_LIST, PY_DICT_SET, FUNGSI, STRING. Solusi referensi tidak memakai list
comprehension, lambda, try/except, atau pustaka di luar pustaka standar.

Pilihan teknis (DECISIONS.md, 2026-11-23):
- Beberapa nilai boleh berada di satu baris, dipisah spasi, karena
  `input().split()` sekarang sudah dipelajari.
- Lima soal yang pernyataannya meminta fungsi mewajibkan konsep FUNGSI di kode
  mahasiswa (`required_concepts_in_submission`), seperti soal rekursi DDP101.
- Semua soal `trimmed`; generator menjauhi batas pembulatan (fungsi aman()).

    python3 docs/soal/buat-ts-py-pertemuan-5.py
"""
from bangun import soal, tulis

AMAN = "def aman(x, d):\n    y = abs(x) * 10 ** d\n    return abs(y - int(y) - 0.5) > 0.002\n\n\n"
FUNGSI = {
    "id": "\n\nKode Anda harus mendefinisikan dan memakai paling sedikit satu fungsi (`def`); kiriman tanpa fungsi tidak diterima.",
    "en": "\n\nYour code must define and use at least one function (`def`); a submission without a function is not accepted.",
    "ar": "\n\nيجب أن يعرِّف برنامجك دالة واحدة على الأقل (`def`) ويستعملها؛ لا يُقبل حلّ بلا دالة.",
}


def py(slug, tingkat, gen, ref, id, en, ar, fungsi=False):
    if fungsi:
        id, en, ar = [(x[0], x[1] + FUNGSI[b]) + tuple(x[2:]) for b, x in (("id", id), ("en", en), ("ar", ar))]
    soal(slug, tingkat, "trimmed", gen, ref.lstrip("\n"), id, en, ar, waktu_ms=5000, wajib=("FUNGSI",) if fungsi else ())


# ============================== MUDAH SEKALI (8) ==============================

py("elevasi-terbalik", "mudah_sekali",
   "print(*[r.randint(100, 2500) for _ in range(1 if i == 2 else r.randint(2, 15))])\n",
   r'''
elevasi = input().split()
elevasi.reverse()
print(" ".join(elevasi))
''',
   ("Daftar Elevasi Dibalik",
    "Juru ukur mencatat elevasi dari hulu ke hilir, tetapi gambar memerlukannya dari hilir ke hulu. Baca semua elevasi ke dalam sebuah "
    "list, lalu cetak dalam urutan terbalik.\n\nContoh: `120 135 128` menghasilkan `128 135 120`.",
    "Satu baris: beberapa elevasi (cm, bilangan bulat) dipisah satu spasi.",
    "Satu baris: elevasi yang sama dalam urutan terbalik, dipisah satu spasi.",
    "1 sampai 15 elevasi; setiap elevasi 100 sampai 2500."),
   ("Elevation List Reversed",
    "A surveyor records elevations from upstream to downstream, but the drawing needs them from downstream to upstream. Read all "
    "elevations into a list, then print them in reverse order.\n\nExample: `120 135 128` gives `128 135 120`.",
    "One line: several elevations (cm, whole numbers) separated by single spaces.",
    "One line: the same elevations in reverse order, separated by single spaces.",
    "1 to 15 elevations; each is 100 to 2500."),
   ("قائمة المناسيب معكوسة",
    "يسجّل المسّاح المناسيب من أعلى المجرى إلى أسفله، لكن الرسم يحتاجها من الأسفل إلى الأعلى. اقرأ المناسيب كلها في قائمة ثم اطبعها "
    "بترتيب معكوس.\n\nمثال: `120 135 128` يعطي `128 135 120`.",
    "سطر واحد: عدة مناسيب (سم، أعداد صحيحة) تفصل بينها مسافة واحدة.",
    "سطر واحد: المناسيب نفسها بترتيب معكوس تفصل بينها مسافة واحدة.",
    "من 1 إلى 15 منسوبًا؛ وكل منسوب من 100 إلى 2500."))

py("total-beban", "mudah_sekali",
   "print(*[r.randint(2, 60) for _ in range(r.randint(1, 20))])\n",
   r'''
beban = list(map(int, input().split()))
print(f"Total: {sum(beban)} kN")
''',
   ("Jumlah Beban Terpusat",
    "Sebuah balok memikul beberapa beban terpusat. Baca semua beban ke dalam list dan cetak jumlahnya.\n\n"
    "Contoh: `12 8 15` menghasilkan `Total: 35 kN`.",
    "Satu baris: beberapa beban (kN, bilangan bulat) dipisah satu spasi.",
    "Satu baris berbentuk `Total: X kN`.",
    "1 sampai 20 beban; setiap beban 2 sampai 60."),
   ("Sum of Point Loads",
    "A beam carries several point loads. Read all loads into a list and print their sum.\n\nExample: `12 8 15` gives `Total: 35 kN`.",
    "One line: several loads (kN, whole numbers) separated by single spaces.",
    "One line of the form `Total: X kN`.",
    "1 to 20 loads; each is 2 to 60."),
   ("مجموع الأحمال المركّزة",
    "يحمل جائز عدة أحمال مركّزة. اقرأ الأحمال كلها في قائمة واطبع مجموعها.\n\nمثال: `12 8 15` يعطي `Total: 35 kN`.",
    "سطر واحد: عدة أحمال (كيلونيوتن، أعداد صحيحة) تفصل بينها مسافة واحدة.",
    "سطر واحد بالشكل `Total: X kN`.",
    "من 1 إلى 20 حملًا؛ وكل حمل من 2 إلى 60."))

py("nama-kapital", "mudah_sekali",
   "a = ['Jembatan', 'Gedung', 'Bendung', 'Jalan', 'Saluran', 'Embung', 'Terminal', 'Dermaga']\n"
   "b = ['Ciliwung', 'Kuliah Terpadu', 'Sei Wampu', 'Lingkar Luar', 'Irigasi Deli', 'Paya Geli', 'Amplas', 'Belawan']\n"
   "print(r.choice(a), r.choice(b), *(['Tahap', str(r.randint(1, 3))] if i % 3 == 0 else []))\n",
   r'''
nama = input()
print(nama.upper())
''',
   ("Nama Proyek Huruf Kapital",
    "Judul pada papan proyek ditulis dengan huruf kapital seluruhnya. Ubah nama proyek yang diketik menjadi huruf kapital.\n\n"
    "Contoh: `Jembatan Ciliwung` menghasilkan `JEMBATAN CILIWUNG`.",
    "Satu baris: nama proyek (huruf, angka, dan spasi).",
    "Satu baris: nama itu dalam huruf kapital.",
    "Panjang nama paling banyak 40 karakter."),
   ("Project Name in Capitals",
    "The title on a project board is written entirely in capital letters. Convert the typed project name to capitals.\n\n"
    "Example: `Jembatan Ciliwung` gives `JEMBATAN CILIWUNG`.",
    "One line: the project name (letters, digits and spaces).",
    "One line: that name in capital letters.",
    "The name has at most 40 characters."),
   ("اسم المشروع بأحرف كبيرة",
    "يُكتب العنوان على لوحة المشروع بأحرف كبيرة كلها. حوِّل اسم المشروع المكتوب إلى أحرف كبيرة.\n\n"
    "مثال: `Jembatan Ciliwung` يعطي `JEMBATAN CILIWUNG`.",
    "سطر واحد: اسم المشروع (حروف لاتينية وأرقام ومسافات).",
    "سطر واحد: الاسم بأحرف كبيرة.",
    "طول الاسم 40 حرفًا على الأكثر."))

py("mutu-dari-kode", "mudah_sekali",
   "print(f\"{r.choice(['BU', 'KB', 'SL'])}-{r.randint(1, 99):02d}-K{r.choice([175, 225, 250, 300, 350, 400, 500])}\")\n",
   r'''
kode = input()
bagian = kode.split("-")
print(bagian[2])
''',
   ("Mutu Beton dari Kode Benda Uji",
    "Kode benda uji terdiri atas tiga bagian yang dipisah tanda hubung: jenis, nomor urut, dan mutu beton, misalnya `BU-07-K300`. "
    "Ambil bagian mutu betonnya dengan memecah teks pada tanda hubung.\n\nContoh: `BU-07-K300` menghasilkan `K300`.",
    "Satu baris: kode benda uji.",
    "Satu baris: bagian mutu beton.",
    "Kode selalu terdiri atas tepat tiga bagian."),
   ("Concrete Grade from a Specimen Code",
    "A specimen code has three parts separated by hyphens: kind, serial number and concrete grade, for example `BU-07-K300`. "
    "Take the grade part by splitting the text at the hyphens.\n\nExample: `BU-07-K300` gives `K300`.",
    "One line: the specimen code.",
    "One line: the grade part.",
    "The code always has exactly three parts."),
   ("درجة الخرسانة من رمز العيّنة",
    "يتكوّن رمز العيّنة من ثلاثة أجزاء تفصل بينها شرطة: النوع والرقم التسلسلي ودرجة الخرسانة، مثل `BU-07-K300`. "
    "استخرج جزء الدرجة بتقسيم النص عند الشرطات.\n\nمثال: `BU-07-K300` يعطي `K300`.",
    "سطر واحد: رمز العيّنة.",
    "سطر واحد: جزء الدرجة.",
    "يتكوّن الرمز دائمًا من ثلاثة أجزاء تمامًا."))

py("fungsi-luas-pelat", "mudah_sekali",
   "print(r.randint(2, 40), r.randint(2, 40))\n",
   r'''
def luas_pelat(panjang, lebar):
    return panjang * lebar


p, l = map(int, input().split())
print(f"Luas: {luas_pelat(p, l)} m2")
''',
   ("Fungsi Luas Pelat",
    "Tulis fungsi `luas_pelat(panjang, lebar)` yang mengembalikan (`return`) luas pelat persegi panjang. Program utama membaca panjang "
    "dan lebar, memanggil fungsi itu, lalu mencetak hasilnya.\n\nContoh: `6 4` menghasilkan `Luas: 24 m2`.",
    "Satu baris: panjang dan lebar pelat (m, bilangan bulat) dipisah satu spasi.",
    "Satu baris berbentuk `Luas: X m2`.",
    "Panjang dan lebar 2 sampai 40."),
   ("A Function for Slab Area",
    "Write a function `luas_pelat(panjang, lebar)` that returns the area of a rectangular slab. The main program reads the length and "
    "the width, calls the function, then prints the result.\n\nExample: `6 4` gives `Luas: 24 m2`.",
    "One line: the length and the width of the slab (m, whole numbers) separated by one space.",
    "One line of the form `Luas: X m2` (the word stays in Indonesian).",
    "Length and width are 2 to 40."),
   ("دالة مساحة البلاطة",
    "اكتب الدالة `luas_pelat(panjang, lebar)` التي تُرجع (`return`) مساحة بلاطة مستطيلة. يقرأ البرنامج الرئيس الطول والعرض ويستدعي "
    "الدالة ثم يطبع الناتج.\n\nمثال: `6 4` يعطي `Luas: 24 m2`.",
    "سطر واحد: طول البلاطة وعرضها (م، عددان صحيحان) تفصل بينهما مسافة.",
    "سطر واحد بالشكل `Luas: X m2` (تبقى الكلمة بالإندونيسية).",
    "الطول والعرض من 2 إلى 40."),
   fungsi=True)

py("harga-material", "mudah_sekali",
   "print(['semen', 'pasir', 'kerikil', 'bata', 'besi'][i % 5])\nprint(r.randint(1, 500))\n",
   r'''
harga = {"semen": 65000, "pasir": 250000, "kerikil": 300000, "bata": 900, "besi": 14000}
nama = input()
jumlah = int(input())
print(f"Harga: Rp {harga[nama] * jumlah}")
''',
   ("Daftar Harga Material",
    "Simpan daftar harga satuan berikut dalam sebuah dictionary, lalu cetak harga total untuk material dan jumlah yang dipesan (harga satuan dikali jumlah).\n\n"
    "| Material | Harga (rupiah) |\n|---|---|\n| semen | 65000 |\n| pasir | 250000 |\n| kerikil | 300000 |\n| bata | 900 |\n| besi | 14000 |\n\n"
    "Contoh: `pasir` sebanyak `3` menghasilkan `Harga: Rp 750000`.",
    "Dua baris: nama material, ditulis dengan huruf kecil seperti di tabel; lalu jumlah yang dipesan (bilangan bulat).",
    "Satu baris berbentuk `Harga: Rp X`.",
    "Nama material selalu salah satu dari lima nama di tabel; jumlah 1 sampai 500."),
   ("Material Price List",
    "Store the unit prices below in a dictionary, then print the total price for the material and the quantity ordered (unit price times quantity).\n\n"
    "| Material | Price (rupiah) |\n|---|---|\n| semen | 65000 |\n| pasir | 250000 |\n| kerikil | 300000 |\n| bata | 900 |\n| besi | 14000 |\n\n"
    "Example: `pasir` with quantity `3` gives `Harga: Rp 750000`.",
    "Two lines: the material name, in lower case as in the table (Indonesian names); then the quantity ordered (a whole number).",
    "One line of the form `Harga: Rp X`.",
    "The name is always one of the five in the table; the quantity is 1 to 500."),
   ("قائمة أسعار المواد",
    "خزِّن أسعار الوحدة الآتية في قاموس (dictionary) ثم اطبع السعر الإجمالي للمادة والكمية المطلوبتين (سعر الوحدة مضروبًا في الكمية).\n\n"
    "| المادة | السعر (روبية) |\n|---|---|\n| semen | 65000 |\n| pasir | 250000 |\n| kerikil | 300000 |\n| bata | 900 |\n| besi | 14000 |\n\n"
    "مثال: `pasir` بكمية `3` يعطي `Harga: Rp 750000`.",
    "سطران: اسم المادة بأحرف صغيرة كما في الجدول (أسماء إندونيسية)؛ ثم الكمية المطلوبة (عدد صحيح).",
    "سطر واحد بالشكل `Harga: Rp X`.",
    "الاسم دائمًا أحد الأسماء الخمسة في الجدول؛ والكمية من 1 إلى 500."))

py("min-maks-bacaan", "mudah_sekali",
   "print(*[r.randint(500, 3500) for _ in range(1 if i == 4 else r.randint(2, 20))])\n",
   r'''
bacaan = list(map(int, input().split()))
print(f"Min: {min(bacaan)}")
print(f"Maks: {max(bacaan)}")
''',
   ("Bacaan Terkecil dan Terbesar",
    "Dari sederet bacaan rambu ukur, cetak bacaan terkecil dan terbesar. Simpan bacaan dalam list dan pakai `min()` dan `max()`.\n\n"
    "Contoh: `1482 1507 1390 1455` menghasilkan\n\n```\nMin: 1390\nMaks: 1507\n```",
    "Satu baris: beberapa bacaan (mm, bilangan bulat) dipisah satu spasi.",
    "Dua baris: `Min: A`, lalu `Maks: B`.",
    "1 sampai 20 bacaan; setiap bacaan 500 sampai 3500."),
   ("Smallest and Largest Reading",
    "From a row of levelling-staff readings, print the smallest and the largest. Keep the readings in a list and use `min()` and "
    "`max()`.\n\nExample: `1482 1507 1390 1455` gives\n\n```\nMin: 1390\nMaks: 1507\n```",
    "One line: several readings (mm, whole numbers) separated by single spaces.",
    "Two lines: `Min: A`, then `Maks: B`.",
    "1 to 20 readings; each is 500 to 3500."),
   ("أصغر قراءة وأكبر قراءة",
    "من صفّ من قراءات القامة اطبع أصغر قراءة وأكبرها. احفظ القراءات في قائمة واستعمل `min()` و`max()`.\n\n"
    "مثال: `1482 1507 1390 1455` يعطي\n\n```\nMin: 1390\nMaks: 1507\n```",
    "سطر واحد: عدة قراءات (مم، أعداد صحيحة) تفصل بينها مسافة واحدة.",
    "سطران: `Min: A` ثم `Maks: B`.",
    "من 1 إلى 20 قراءة؛ وكل قراءة من 500 إلى 3500."))

py("bacaan-ke", "mudah_sekali",
   "n = r.randint(1, 15)\nprint(*[r.randint(10, 400) for _ in range(n)])\nprint([1, n][i] if i < 2 else r.randint(1, n))\n",
   r'''
debit = list(map(int, input().split()))
nomor = int(input())
print(f"Bacaan ke-{nomor}: {debit[nomor - 1]}")
''',
   ("Mengambil Bacaan Tertentu",
    "Pos duga air mencatat debit beberapa kali sehari. Simpan catatan itu dalam list, lalu cetak bacaan dengan nomor urut yang diminta. "
    "Nomor urut dimulai dari 1, sedangkan indeks list dimulai dari 0.\n\n"
    "Contoh: bacaan `85 92 110 97` dan nomor `3` menghasilkan `Bacaan ke-3: 110`.",
    "Baris pertama: beberapa bacaan debit (liter/detik, bilangan bulat) dipisah satu spasi. Baris kedua: nomor urut yang diminta.",
    "Satu baris berbentuk `Bacaan ke-K: X`.",
    "1 sampai 15 bacaan; nomor urut selalu ada."),
   ("Picking One Reading",
    "A gauging station records the discharge several times a day. Keep the record in a list, then print the reading with the serial "
    "number asked for. Serial numbers start at 1, while list indexes start at 0.\n\n"
    "Example: readings `85 92 110 97` and number `3` give `Bacaan ke-3: 110`.",
    "First line: several discharge readings (litres/s, whole numbers) separated by single spaces. Second line: the serial number "
    "asked for.",
    "One line of the form `Bacaan ke-K: X` (Indonesian words).",
    "1 to 15 readings; the serial number always exists."),
   ("أخذ قراءة معيّنة",
    "تسجّل محطة القياس التصريف عدة مرات في اليوم. احفظ السجل في قائمة ثم اطبع القراءة ذات الرقم التسلسلي المطلوب. "
    "تبدأ الأرقام التسلسلية من 1، أما فهارس القائمة فتبدأ من 0.\n\n"
    "مثال: القراءات `85 92 110 97` والرقم `3` تعطي `Bacaan ke-3: 110`.",
    "السطر الأول: عدة قراءات للتصريف (لتر/ث، أعداد صحيحة) تفصل بينها مسافة. السطر الثاني: الرقم التسلسلي المطلوب.",
    "سطر واحد بالشكل `Bacaan ke-K: X` (بالإندونيسية).",
    "من 1 إلى 15 قراءة؛ والرقم التسلسلي موجود دائمًا."))

# ================================= MUDAH (8) =================================

py("di-atas-rata", "mudah",
   AMAN + "while True:\n    v = [r.randint(180, 420) for _ in range(r.randint(2, 20))]\n    if i == 3:\n        v = [v[0]] * len(v)\n"
   "    if aman(sum(v) / len(v), 2):\n        break\nprint(*v)\n",
   r'''
hasil = list(map(int, input().split()))
rata_rata = sum(hasil) / len(hasil)
di_atas = 0
for nilai in hasil:
    if nilai > rata_rata:
        di_atas = di_atas + 1
print(f"Rata-rata: {rata_rata:.2f}")
print(f"Di atas rata-rata: {di_atas}")
''',
   ("Benda Uji di Atas Rata-rata",
    "Dari sederet hasil uji kuat tekan, hitung rata-ratanya, lalu hitung berapa hasil yang lebih besar dari rata-rata itu. Karena "
    "rata-rata baru diketahui setelah semua data dibaca, data perlu disimpan dalam list.\n\n"
    "Contoh: `300 320 280 340` menghasilkan\n\n```\nRata-rata: 310.00\nDi atas rata-rata: 2\n```",
    "Satu baris: beberapa hasil uji (kg/cm², bilangan bulat) dipisah satu spasi.",
    "Dua baris: `Rata-rata: R` (dua angka di belakang titik), lalu `Di atas rata-rata: K`.",
    "2 sampai 20 hasil; setiap hasil 180 sampai 420."),
   ("Specimens Above the Average",
    "From a row of compressive strength results, compute the average, then count the results that are greater than that average. "
    "Because the average is known only after all data are read, the data must be kept in a list.\n\n"
    "Example: `300 320 280 340` gives\n\n```\nRata-rata: 310.00\nDi atas rata-rata: 2\n```",
    "One line: several results (kg/cm², whole numbers) separated by single spaces.",
    "Two lines: `Rata-rata: R` (two digits after the point), then `Di atas rata-rata: K` (Indonesian words).",
    "2 to 20 results; each is 180 to 420."),
   ("العيّنات فوق المتوسط",
    "من صفّ من نتائج مقاومة الضغط احسب المتوسط ثم عُدّ النتائج الأكبر من ذلك المتوسط. ولأن المتوسط لا يُعرف إلا بعد قراءة كل "
    "البيانات فلا بدّ من حفظها في قائمة.\n\nمثال: `300 320 280 340` يعطي\n\n```\nRata-rata: 310.00\nDi atas rata-rata: 2\n```",
    "سطر واحد: عدة نتائج (كغ/سم²، أعداد صحيحة) تفصل بينها مسافة واحدة.",
    "سطران: `Rata-rata: R` (منزلتان عشريتان) ثم `Di atas rata-rata: K` (بالإندونيسية).",
    "من 2 إلى 20 نتيجة؛ وكل نتيجة من 180 إلى 420."))

py("median-kuat-tekan", "mudah",
   "print(*[r.randint(180, 420) for _ in range(1 if i == 5 else r.randint(2, 15))])\n",
   r'''
hasil = list(map(int, input().split()))
hasil.sort()
banyak = len(hasil)
if banyak % 2 == 1:
    median = hasil[banyak // 2]
else:
    median = (hasil[banyak // 2 - 1] + hasil[banyak // 2]) / 2
print(" ".join(map(str, hasil)))
print(f"Median: {median:.1f}")
''',
   ("Median Hasil Uji",
    "Urutkan hasil uji dari yang terkecil, cetak urutan itu, lalu cetak mediannya dengan satu angka di belakang titik. Median adalah "
    "nilai yang berada di tengah setelah diurutkan; bila banyaknya data genap, median adalah rata-rata dua nilai tengah.\n\n"
    "Contoh: `310 270 290 300` menghasilkan\n\n```\n270 290 300 310\nMedian: 295.0\n```",
    "Satu baris: beberapa hasil uji (kg/cm², bilangan bulat) dipisah satu spasi.",
    "Baris pertama: hasil uji terurut naik, dipisah satu spasi. Baris kedua: `Median: M` dengan satu angka di belakang titik.",
    "1 sampai 15 hasil; setiap hasil 180 sampai 420."),
   ("Median of Test Results",
    "Sort the test results from the smallest, print that order, then print the median with one digit after the point. The median is "
    "the value in the middle after sorting; when the number of data is even, the median is the average of the two middle values.\n\n"
    "Example: `310 270 290 300` gives\n\n```\n270 290 300 310\nMedian: 295.0\n```",
    "One line: several results (kg/cm², whole numbers) separated by single spaces.",
    "First line: the results in ascending order, separated by single spaces. Second line: `Median: M` with one digit after the point.",
    "1 to 15 results; each is 180 to 420."),
   ("وسيط نتائج الاختبار",
    "رتِّب نتائج الاختبار من الأصغر واطبع ذلك الترتيب، ثم اطبع الوسيط بمنزلة عشرية واحدة. الوسيط هو القيمة الواقعة في المنتصف بعد "
    "الترتيب؛ وإذا كان عدد البيانات زوجيًّا فالوسيط متوسط القيمتين الوسطيين.\n\n"
    "مثال: `310 270 290 300` يعطي\n\n```\n270 290 300 310\nMedian: 295.0\n```",
    "سطر واحد: عدة نتائج (كغ/سم²، أعداد صحيحة) تفصل بينها مسافة واحدة.",
    "السطر الأول: النتائج مرتبة تصاعديًّا تفصل بينها مسافة. السطر الثاني: `Median: M` بمنزلة عشرية واحدة.",
    "من 1 إلى 15 نتيجة؛ وكل نتيجة من 180 إلى 420."))

py("fungsi-volume-telapak", "mudah",
   "n = r.randint(1, 10)\nprint(n)\nfor _ in range(n):\n    print(r.randint(6, 30) * 10, r.randint(6, 30) * 10, r.randint(2, 8) * 10)\n",
   r'''
def volume_telapak(panjang_cm, lebar_cm, tebal_cm):
    return panjang_cm * lebar_cm * tebal_cm / 1000000


banyak = int(input())
total = 0
for nomor in range(1, banyak + 1):
    p, l, t = map(int, input().split())
    volume = volume_telapak(p, l, t)
    total = total + volume
    print(f"Pondasi {nomor}: {volume:.3f} m3")
print(f"Total: {total:.3f} m3")
''',
   ("Fungsi Volume Pondasi Telapak",
    "Tulis fungsi `volume_telapak(panjang_cm, lebar_cm, tebal_cm)` yang mengembalikan volume beton sebuah pondasi telapak dalam m³ "
    "(ukuran diberikan dalam cm; 1 m³ = 1000000 cm³). Pakai fungsi itu untuk setiap pondasi di daftar, cetak volume tiap pondasi dan "
    "jumlahnya, semuanya dengan tiga angka di belakang titik.\n\n"
    "Contoh: masukan\n\n```\n2\n100 100 30\n150 120 40\n```\n\nmenghasilkan\n\n"
    "```\nPondasi 1: 0.300 m3\nPondasi 2: 0.720 m3\nTotal: 1.020 m3\n```",
    "Baris pertama: banyaknya pondasi N. Lalu N baris, masing-masing panjang, lebar, dan tebal (cm, bilangan bulat) dipisah spasi.",
    "N baris `Pondasi K: V m3`, lalu satu baris `Total: T m3`; semua volume tiga angka di belakang titik.",
    "N 1 sampai 10; panjang dan lebar 60 sampai 300; tebal 20 sampai 80; semua ukuran kelipatan 10."),
   ("A Function for Pad Footing Volume",
    "Write a function `volume_telapak(panjang_cm, lebar_cm, tebal_cm)` that returns the concrete volume of a pad footing in m³ "
    "(sizes are given in cm; 1 m³ = 1000000 cm³). Use the function for every footing in the list, print the volume of each footing "
    "and the total, all with three digits after the point.\n\n"
    "Example: the input\n\n```\n2\n100 100 30\n150 120 40\n```\n\ngives\n\n"
    "```\nPondasi 1: 0.300 m3\nPondasi 2: 0.720 m3\nTotal: 1.020 m3\n```",
    "First line: the number of footings N. Then N lines, each the length, width and thickness (cm, whole numbers) separated by spaces.",
    "N lines `Pondasi K: V m3`, then one line `Total: T m3`; all volumes with three digits after the point (Indonesian words).",
    "N is 1 to 10; length and width 60 to 300; thickness 20 to 80; all sizes are multiples of 10."),
   ("دالة حجم القاعدة المنفردة",
    "اكتب الدالة `volume_telapak(panjang_cm, lebar_cm, tebal_cm)` التي تُرجع حجم خرسانة قاعدة منفردة بالمتر المكعب "
    "(المقاسات بالسنتيمتر؛ 1 م³ = 1000000 سم³). استعمل الدالة لكل قاعدة في القائمة واطبع حجم كل قاعدة والمجموع، كلها بثلاث "
    "منازل عشرية.\n\nمثال: المدخل\n\n```\n2\n100 100 30\n150 120 40\n```\n\nيعطي\n\n"
    "```\nPondasi 1: 0.300 m3\nPondasi 2: 0.720 m3\nTotal: 1.020 m3\n```",
    "السطر الأول: عدد القواعد N. ثم N سطرًا، في كل سطر الطول والعرض والسماكة (سم، أعداد صحيحة) تفصل بينها مسافات.",
    "N سطرًا `Pondasi K: V m3` ثم سطر `Total: T m3`؛ كل الحجوم بثلاث منازل عشرية (بالإندونيسية).",
    "N من 1 إلى 10؛ الطول والعرض من 60 إلى 300؛ السماكة من 20 إلى 80؛ وكل المقاسات من مضاعفات 10."),
   fungsi=True)

py("hitung-golongan", "mudah",
   "g = [['MC', 'LV', 'HV'], ['MC'], ['LV', 'HV'], ['HV']][i % 4]\nprint(*[r.choice(g) for _ in range(r.randint(1, 40))])\n",
   r'''
kode = input().split()
jumlah = {"MC": 0, "LV": 0, "HV": 0}
for k in kode:
    jumlah[k] = jumlah[k] + 1
print(f"MC: {jumlah['MC']}")
print(f"LV: {jumlah['LV']}")
print(f"HV: {jumlah['HV']}")
''',
   ("Mencacah Golongan Kendaraan",
    "Surveyor lalu lintas mengetik kode setiap kendaraan yang lewat: `MC` untuk sepeda motor, `LV` untuk kendaraan ringan, `HV` untuk "
    "kendaraan berat. Hitung banyaknya tiap golongan dengan sebuah dictionary, dan cetak dalam urutan MC, LV, HV (golongan yang tidak "
    "muncul dicetak 0).\n\nContoh: `MC LV HV LV LV MC` menghasilkan\n\n```\nMC: 2\nLV: 3\nHV: 1\n```",
    "Satu baris: kode kendaraan dipisah satu spasi.",
    "Tiga baris: `MC: A`, `LV: B`, `HV: C`.",
    "1 sampai 40 kendaraan."),
   ("Counting Vehicle Classes",
    "A traffic surveyor types the code of every passing vehicle: `MC` for a motorcycle, `LV` for a light vehicle, `HV` for a heavy "
    "vehicle. Count each class with a dictionary and print them in the order MC, LV, HV (a class that does not appear prints 0).\n\n"
    "Example: `MC LV HV LV LV MC` gives\n\n```\nMC: 2\nLV: 3\nHV: 1\n```",
    "One line: vehicle codes separated by single spaces.",
    "Three lines: `MC: A`, `LV: B`, `HV: C`.",
    "1 to 40 vehicles."),
   ("عدّ أصناف المركبات",
    "يكتب مسّاح المرور رمز كل مركبة تمرّ: `MC` للدراجة النارية و`LV` للمركبة الخفيفة و`HV` للمركبة الثقيلة. عُدّ كل صنف بقاموس "
    "واطبعها بالترتيب MC ثم LV ثم HV (الصنف الذي لا يظهر يُطبع 0).\n\n"
    "مثال: `MC LV HV LV LV MC` يعطي\n\n```\nMC: 2\nLV: 3\nHV: 1\n```",
    "سطر واحد: رموز المركبات تفصل بينها مسافة واحدة.",
    "ثلاثة أسطر: `MC: A` و`LV: B` و`HV: C`.",
    "من 1 إلى 40 مركبة."))

py("diameter-unik", "mudah",
   "d = r.sample([6, 8, 10, 13, 16, 19, 22, 25, 29, 32], r.randint(1, 6))\nprint(*[r.choice(d) for _ in range(r.randint(1, 30))])\n",
   r'''
diameter = list(map(int, input().split()))
unik = sorted(set(diameter))
print(" ".join(map(str, unik)))
print(f"Jenis: {len(unik)}")
''',
   ("Diameter Besi yang Dipakai",
    "Daftar potong besi memuat diameter setiap batang, dan banyak diameter yang berulang. Bagian pembelian hanya perlu tahu diameter "
    "apa saja yang dipakai. Cetak setiap diameter satu kali, terurut dari yang terkecil, lalu banyaknya jenis diameter. "
    "Sebuah set membuang nilai yang kembar.\n\nContoh: `13 10 16 13 10 13` menghasilkan\n\n```\n10 13 16\nJenis: 3\n```",
    "Satu baris: diameter batang (mm, bilangan bulat) dipisah satu spasi.",
    "Baris pertama: diameter yang berbeda, terurut naik, dipisah satu spasi. Baris kedua: `Jenis: K`.",
    "1 sampai 30 batang; diameter 6 sampai 32."),
   ("Bar Diameters in Use",
    "A bar bending schedule lists the diameter of every bar, and many diameters repeat. The purchasing office only needs to know which "
    "diameters are used. Print each diameter once, sorted from the smallest, then the number of different diameters. A set drops "
    "repeated values.\n\nExample: `13 10 16 13 10 13` gives\n\n```\n10 13 16\nJenis: 3\n```",
    "One line: bar diameters (mm, whole numbers) separated by single spaces.",
    "First line: the different diameters in ascending order, separated by single spaces. Second line: `Jenis: K`.",
    "1 to 30 bars; diameters 6 to 32."),
   ("أقطار الحديد المستعملة",
    "تتضمن قائمة قصّ الحديد قطر كل قضيب، وتتكرر أقطار كثيرة. يحتاج قسم المشتريات فقط إلى معرفة الأقطار المستعملة. اطبع كل قطر مرة "
    "واحدة مرتبًا من الأصغر ثم عدد الأقطار المختلفة. المجموعة (set) تحذف القيم المكررة.\n\n"
    "مثال: `13 10 16 13 10 13` يعطي\n\n```\n10 13 16\nJenis: 3\n```",
    "سطر واحد: أقطار القضبان (مم، أعداد صحيحة) تفصل بينها مسافة واحدة.",
    "السطر الأول: الأقطار المختلفة مرتبة تصاعديًّا تفصل بينها مسافة. السطر الثاني: `Jenis: K`.",
    "من 1 إلى 30 قضيبًا؛ والأقطار من 6 إلى 32."))

py("sta-ke-meter", "mudah",
   "print(f'STA {[0, 0][i] if i < 2 else r.randint(0, 250)}+{[0, 7][i] if i < 2 else r.randint(0, 999):03d}')\n",
   r'''
teks = input()
bagian = teks.split()
km, meter = bagian[1].split("+")
jarak = int(km) * 1000 + int(meter)
print(f"{jarak} m")
''',
   ("Stasiun ke Meter",
    "Posisi di sepanjang jalan ditulis sebagai stasiun, misalnya `STA 12+350`: 12 kilometer ditambah 350 meter dari titik awal. "
    "Ubah tulisan stasiun menjadi jarak dalam meter.\n\nContoh: `STA 12+350` menghasilkan `12350 m`; `STA 0+007` menghasilkan `7 m`.",
    "Satu baris berbentuk `STA K+MMM`: kata STA, satu spasi, kilometer, tanda `+`, lalu meter yang ditulis tiga angka.",
    "Satu baris berbentuk `X m`.",
    "Kilometer 0 sampai 250; meter 000 sampai 999."),
   ("Station to Metres",
    "A position along a road is written as a station, for example `STA 12+350`: 12 kilometres plus 350 metres from the start. "
    "Convert the station text to a distance in metres.\n\nExample: `STA 12+350` gives `12350 m`; `STA 0+007` gives `7 m`.",
    "One line of the form `STA K+MMM`: the word STA, one space, the kilometres, a `+` sign, then the metres written with three digits.",
    "One line of the form `X m`.",
    "Kilometres 0 to 250; metres 000 to 999."),
   ("من المحطة إلى الأمتار",
    "يُكتب الموضع على طول الطريق محطةً، مثل `STA 12+350`: أي 12 كيلومترًا و350 مترًا من نقطة البداية. حوِّل نص المحطة إلى مسافة "
    "بالأمتار.\n\nمثال: `STA 12+350` يعطي `12350 m`؛ و`STA 0+007` يعطي `7 m`.",
    "سطر واحد بالشكل `STA K+MMM`: الكلمة STA ثم مسافة ثم الكيلومترات ثم علامة `+` ثم الأمتار بثلاثة أرقام.",
    "سطر واحد بالشكل `X m`.",
    "الكيلومترات من 0 إلى 250؛ والأمتار من 000 إلى 999."))

py("inisial-proyek", "mudah",
   "k = ['gedung', 'Kuliah', 'terpadu', 'Jalan', 'tol', 'Medan', 'binjai', 'Bendung', 'gerak', 'Rumah', 'sakit', 'Umum', 'pasar', 'Induk']\n"
   "print(*r.sample(k, r.randint(1, 6)))\n",
   r'''
kata = input().split()
inisial = ""
for k in kata:
    inisial = inisial + k[0].upper()
print(inisial)
''',
   ("Singkatan Nama Proyek",
    "Nama proyek yang panjang disingkat dengan mengambil huruf pertama setiap kata, ditulis dengan huruf kapital dan disambung tanpa "
    "spasi. Kata-katanya bisa diketik dengan huruf kecil atau huruf besar.\n\n"
    "Contoh: `Gedung kuliah Terpadu` menghasilkan `GKT`.",
    "Satu baris: nama proyek, kata-katanya dipisah satu spasi.",
    "Satu baris: singkatannya.",
    "1 sampai 6 kata, hanya huruf."),
   ("Project Name Abbreviation",
    "A long project name is abbreviated by taking the first letter of every word, written in capitals and joined without spaces. "
    "The words may be typed in lower or upper case.\n\nExample: `Gedung kuliah Terpadu` gives `GKT`.",
    "One line: the project name, its words separated by single spaces.",
    "One line: the abbreviation.",
    "1 to 6 words, letters only."),
   ("اختصار اسم المشروع",
    "يُختصر اسم المشروع الطويل بأخذ الحرف الأول من كل كلمة، مكتوبًا بحرف كبير وموصولًا دون مسافات. قد تُكتب الكلمات بأحرف صغيرة "
    "أو كبيرة.\n\nمثال: `Gedung kuliah Terpadu` يعطي `GKT`.",
    "سطر واحد: اسم المشروع وكلماته تفصل بينها مسافة واحدة.",
    "سطر واحد: الاختصار.",
    "من 1 إلى 6 كلمات بحروف لاتينية فقط."))

py("deviasi-rencana", "mudah",
   "n = r.randint(1, 15)\na = [r.randint(100, 900) for _ in range(n)]\nprint(*a)\n"
   "print(*[x if i == 2 or r.random() < 0.4 else x + r.randint(-9, 9) for x in a])\n",
   r'''
rencana = list(map(int, input().split()))
terpasang = list(map(int, input().split()))
selisih = []
menyimpang = 0
for k in range(len(rencana)):
    beda = terpasang[k] - rencana[k]
    selisih.append(str(beda))
    if beda != 0:
        menyimpang = menyimpang + 1
print(" ".join(selisih))
print(f"Menyimpang: {menyimpang}")
''',
   ("Selisih Elevasi Terpasang dari Rencana",
    "Setelah pemadatan, elevasi terpasang di setiap titik dibandingkan dengan elevasi rencana. Untuk setiap titik hitung selisihnya "
    "(terpasang dikurangi rencana; boleh negatif), cetak semua selisih dalam satu baris, lalu banyaknya titik yang selisihnya bukan nol.\n\n"
    "Contoh: rencana `500 520 540` dan terpasang `502 519 540` menghasilkan\n\n```\n2 -1 0\nMenyimpang: 2\n```",
    "Baris pertama: elevasi rencana tiap titik. Baris kedua: elevasi terpasang tiap titik, sama banyaknya. Semuanya bilangan bulat "
    "(mm) dipisah satu spasi.",
    "Baris pertama: selisih tiap titik dipisah satu spasi. Baris kedua: `Menyimpang: K`.",
    "1 sampai 15 titik; selisih paling besar 9 mm."),
   ("As-Built Elevation Against the Design",
    "After compaction the as-built elevation of every point is compared with the design elevation. For each point compute the "
    "difference (as-built minus design; it may be negative), print all differences on one line, then the number of points whose "
    "difference is not zero.\n\nExample: design `500 520 540` and as-built `502 519 540` give\n\n```\n2 -1 0\nMenyimpang: 2\n```",
    "First line: the design elevation of every point. Second line: the as-built elevation of every point, the same count. All whole "
    "numbers (mm) separated by single spaces.",
    "First line: the difference of every point, separated by single spaces. Second line: `Menyimpang: K` (the word stays in Indonesian).",
    "1 to 15 points; differences are at most 9 mm."),
   ("فرق المنسوب المنفَّذ عن التصميمي",
    "بعد الدمك يُقارن المنسوب المنفَّذ عند كل نقطة بالمنسوب التصميمي. احسب الفرق لكل نقطة (المنفَّذ ناقص التصميمي؛ وقد يكون سالبًا) "
    "واطبع كل الفروق في سطر واحد ثم عدد النقاط التي فرقها ليس صفرًا.\n\n"
    "مثال: التصميمي `500 520 540` والمنفَّذ `502 519 540` يعطيان\n\n```\n2 -1 0\nMenyimpang: 2\n```",
    "السطر الأول: المنسوب التصميمي لكل نقطة. السطر الثاني: المنسوب المنفَّذ لكل نقطة بالعدد نفسه. كلها أعداد صحيحة (مم) تفصل "
    "بينها مسافة واحدة.",
    "السطر الأول: فرق كل نقطة تفصل بينها مسافة. السطر الثاني: `Menyimpang: K` (تبقى الكلمة بالإندونيسية).",
    "من 1 إلى 15 نقطة؛ والفروق لا تزيد على 9 مم."))

# ============================== MUDAH SEDANG (6) ==============================

py("rekap-besi", "mudah_sedang",
   "d = r.sample([8, 10, 13, 16, 19, 22, 25], r.randint(1, 5))\nn = r.randint(1, 25)\nprint(n)\nfor _ in range(n):\n"
   "    print(r.choice(d), r.randint(1, 12))\n",
   r'''
banyak = int(input())
total = {}
for _ in range(banyak):
    diameter, panjang = map(int, input().split())
    if diameter in total:
        total[diameter] = total[diameter] + panjang
    else:
        total[diameter] = panjang
for diameter in sorted(total):
    print(f"D{diameter}: {total[diameter]} m")
''',
   ("Rekap Panjang Besi per Diameter",
    "Daftar kebutuhan besi berisi banyak baris; setiap baris menyebut diameter dan panjang satu batang. Jumlahkan panjang untuk setiap "
    "diameter dengan dictionary, lalu cetak rekapnya terurut dari diameter terkecil.\n\n"
    "Contoh: masukan\n\n```\n4\n13 6\n10 12\n13 9\n10 4\n```\n\nmenghasilkan\n\n```\nD10: 16 m\nD13: 15 m\n```",
    "Baris pertama: banyaknya baris data N. Lalu N baris, masing-masing diameter (mm) dan panjang (m), bilangan bulat dipisah spasi.",
    "Satu baris untuk setiap diameter yang muncul, berbentuk `Dd: P m`, terurut dari diameter terkecil.",
    "N 1 sampai 25; diameter 8 sampai 25; panjang 1 sampai 12."),
   ("Bar Length Summary per Diameter",
    "A bar schedule has many lines; each line gives the diameter and the length of one bar. Add up the length for every diameter with "
    "a dictionary, then print the summary sorted from the smallest diameter.\n\n"
    "Example: the input\n\n```\n4\n13 6\n10 12\n13 9\n10 4\n```\n\ngives\n\n```\nD10: 16 m\nD13: 15 m\n```",
    "First line: the number of data lines N. Then N lines, each a diameter (mm) and a length (m), whole numbers separated by a space.",
    "One line for every diameter that appears, of the form `Dd: P m`, sorted from the smallest diameter.",
    "N is 1 to 25; diameters 8 to 25; lengths 1 to 12."),
   ("ملخّص أطوال الحديد لكل قطر",
    "تتضمن قائمة الحديد أسطرًا كثيرة؛ في كل سطر قطر قضيب وطوله. اجمع الأطوال لكل قطر بقاموس ثم اطبع الملخّص مرتبًا من أصغر قطر.\n\n"
    "مثال: المدخل\n\n```\n4\n13 6\n10 12\n13 9\n10 4\n```\n\nيعطي\n\n```\nD10: 16 m\nD13: 15 m\n```",
    "السطر الأول: عدد أسطر البيانات N. ثم N سطرًا، في كل سطر قطر (مم) وطول (م)، عددان صحيحان تفصل بينهما مسافة.",
    "سطر لكل قطر يظهر بالشكل `Dd: P m` مرتبًا من أصغر قطر.",
    "N من 1 إلى 25؛ الأقطار من 8 إلى 25؛ والأطوال من 1 إلى 12."))

py("kurva-massa-hujan", "mudah_sedang",
   "v = [0 if r.random() < 0.3 else r.randint(1, 80) for _ in range(r.randint(1, 20))]\nprint(*v)\n"
   "print([sum(v) + 1, max(1, sum(v))][i] if i < 2 else r.randint(1, max(1, sum(v)) + 40))\n",
   r'''
harian = list(map(int, input().split()))
target = int(input())
kumulatif = []
jumlah = 0
hari_tercapai = 0
for k in range(len(harian)):
    jumlah = jumlah + harian[k]
    kumulatif.append(str(jumlah))
    if hari_tercapai == 0 and jumlah >= target:
        hari_tercapai = k + 1
print(" ".join(kumulatif))
if hari_tercapai == 0:
    print("BELUM TERCAPAI")
else:
    print(f"Tercapai hari ke-{hari_tercapai}")
''',
   ("Kurva Massa Hujan",
    "Kurva massa hujan adalah jumlah kumulatif curah hujan dari hari pertama. Dari curah hujan harian, buat list kumulatifnya dan "
    "cetak dalam satu baris. Lalu tentukan hari pertama saat kumulatif mencapai atau melampaui target; bila sampai hari terakhir "
    "belum tercapai, cetak `BELUM TERCAPAI`.\n\n"
    "Contoh: harian `10 0 25 5` dan target `30` menghasilkan\n\n```\n10 10 35 40\nTercapai hari ke-3\n```",
    "Baris pertama: curah hujan harian (mm, bilangan bulat) dipisah satu spasi. Baris kedua: target kumulatif (mm).",
    "Baris pertama: kumulatif tiap hari dipisah satu spasi. Baris kedua: `Tercapai hari ke-K` atau `BELUM TERCAPAI`.",
    "1 sampai 20 hari; curah hujan 0 sampai 80; target paling sedikit 1."),
   ("Rainfall Mass Curve",
    "A rainfall mass curve is the cumulative rainfall from the first day. From the daily rainfall, build the cumulative list and "
    "print it on one line. Then find the first day on which the cumulative value reaches or exceeds the target; if it is not reached "
    "by the last day, print `BELUM TERCAPAI`.\n\n"
    "Example: daily `10 0 25 5` and target `30` give\n\n```\n10 10 35 40\nTercapai hari ke-3\n```",
    "First line: the daily rainfall (mm, whole numbers) separated by single spaces. Second line: the cumulative target (mm).",
    "First line: the cumulative value of each day, separated by single spaces. Second line: `Tercapai hari ke-K` or "
    "`BELUM TERCAPAI` (Indonesian words).",
    "1 to 20 days; rainfall 0 to 80; the target is at least 1."),
   ("منحنى الكتلة المطرية",
    "منحنى الكتلة المطرية هو المجموع التراكمي للهطول منذ اليوم الأول. من الهطول اليومي أنشئ القائمة التراكمية واطبعها في سطر واحد. "
    "ثم حدِّد أول يوم يبلغ فيه التراكمي الهدف أو يتجاوزه؛ وإذا لم يُبلَغ حتى اليوم الأخير فاطبع `BELUM TERCAPAI`.\n\n"
    "مثال: اليومي `10 0 25 5` والهدف `30` يعطيان\n\n```\n10 10 35 40\nTercapai hari ke-3\n```",
    "السطر الأول: الهطول اليومي (مم، أعداد صحيحة) تفصل بينها مسافة. السطر الثاني: الهدف التراكمي (مم).",
    "السطر الأول: القيمة التراكمية لكل يوم تفصل بينها مسافة. السطر الثاني: `Tercapai hari ke-K` أو `BELUM TERCAPAI` (بالإندونيسية).",
    "من 1 إلى 20 يومًا؛ الهطول من 0 إلى 80؛ والهدف 1 على الأقل."))

py("benda-uji-gagal", "mudah_sedang",
   "b = r.choice([225, 250, 300, 350])\nn = r.randint(1, 20)\nprint(b)\nprint(n)\nfor k in range(1, n + 1):\n"
   "    print(f'BU-{k:02d}', b + (r.randint(0, 60) if i == 1 else r.randint(-40, 60)))\n",
   r'''
batas = int(input())
banyak = int(input())
gagal = []
for _ in range(banyak):
    kode, nilai = input().split()
    if int(nilai) < batas:
        gagal.append(kode)
if len(gagal) == 0:
    print("SEMUA LULUS")
else:
    for kode in gagal:
        print(kode)
    print(f"Gagal: {len(gagal)}")
''',
   ("Daftar Benda Uji yang Gagal",
    "Setiap baris data memuat kode benda uji dan hasil uji kuat tekannya. Kumpulkan dalam list kode benda uji yang hasilnya lebih "
    "rendah dari batas, lalu cetak kode-kode itu sesuai urutan data, diikuti banyaknya. Bila tidak ada yang gagal, cetak "
    "`SEMUA LULUS` saja.\n\nContoh: masukan\n\n```\n300\n3\nBU-01 312\nBU-02 287\nBU-03 299\n```\n\nmenghasilkan\n\n"
    "```\nBU-02\nBU-03\nGagal: 2\n```",
    "Baris pertama: batas (kg/cm²). Baris kedua: banyaknya benda uji N. Lalu N baris, masing-masing kode dan hasil uji dipisah spasi.",
    "Kode setiap benda uji yang gagal, satu per baris, lalu `Gagal: K`; atau satu baris `SEMUA LULUS`.",
    "N 1 sampai 20; hasil uji 185 sampai 410."),
   ("List of Failed Specimens",
    "Each data line holds a specimen code and its compressive strength result. Collect in a list the codes of the specimens whose "
    "result is below the limit, then print those codes in the order of the data, followed by how many there are. If none fails, "
    "print only `SEMUA LULUS`.\n\nExample: the input\n\n```\n300\n3\nBU-01 312\nBU-02 287\nBU-03 299\n```\n\ngives\n\n"
    "```\nBU-02\nBU-03\nGagal: 2\n```",
    "First line: the limit (kg/cm²). Second line: the number of specimens N. Then N lines, each a code and a result separated by a space.",
    "The code of every failed specimen, one per line, then `Gagal: K`; or the single line `SEMUA LULUS` (Indonesian words).",
    "N is 1 to 20; results are 185 to 410."),
   ("قائمة العيّنات الراسبة",
    "في كل سطر بيانات رمز عيّنة ونتيجة اختبار مقاومتها. اجمع في قائمة رموز العيّنات التي نتيجتها أدنى من الحد، ثم اطبع تلك الرموز "
    "بترتيب البيانات متبوعة بعددها. إذا لم ترسب أي عيّنة فاطبع `SEMUA LULUS` فقط.\n\n"
    "مثال: المدخل\n\n```\n300\n3\nBU-01 312\nBU-02 287\nBU-03 299\n```\n\nيعطي\n\n```\nBU-02\nBU-03\nGagal: 2\n```",
    "السطر الأول: الحد (كغ/سم²). السطر الثاني: عدد العيّنات N. ثم N سطرًا، في كل سطر رمز ونتيجة تفصل بينهما مسافة.",
    "رمز كل عيّنة راسبة في سطر، ثم `Gagal: K`؛ أو سطر واحد `SEMUA LULUS` (بالإندونيسية).",
    "N من 1 إلى 20؛ والنتائج من 185 إلى 410."))

py("kode-gambar", "mudah_sedang",
   "sah = r.choice('ASME') + '-' + f'{r.randint(0, 999):03d}'\nrusak = [sah, 'X' + sah[1:], sah[0] + '_' + sah[2:], sah[:4], sah + '0', sah[:3] + 'A' + sah[4:],\n"
   "         sah.lower(), sah[0] + sah[0] + sah[2:], sah, sah[:2] + '1O1', sah, 'S-' + sah[2:]]\nprint(rusak[i])\n",
   r'''
kode = input()
sah = True
if len(kode) != 5:
    sah = False
elif kode[0] not in "ASME":
    sah = False
elif kode[1] != "-":
    sah = False
elif not kode[2:].isdigit():
    sah = False
if sah:
    print("SAH")
else:
    print("TIDAK SAH")
''',
   ("Memeriksa Kode Gambar",
    "Setiap lembar gambar proyek diberi kode dengan aturan berikut:\n\n"
    "- panjangnya tepat 5 karakter;\n- karakter pertama salah satu huruf kapital `A` (arsitektur), `S` (struktur), `M` (mekanikal), "
    "atau `E` (elektrikal);\n- karakter kedua tanda hubung `-`;\n- tiga karakter terakhir semuanya angka.\n\n"
    "Periksa sebuah kode dan nyatakan sah atau tidak.\n\n"
    "Contoh: `S-101` menghasilkan `SAH`; `s-101`, `S101`, `S-1A1`, dan `S-1010` masing-masing menghasilkan `TIDAK SAH`.",
    "Satu baris: kode gambar (huruf, angka, dan tanda baca, tanpa spasi).",
    "Satu baris: `SAH` atau `TIDAK SAH`.",
    "Panjang kode 1 sampai 10 karakter."),
   ("Checking a Drawing Code",
    "Every drawing sheet of a project gets a code that follows these rules:\n\n"
    "- it is exactly 5 characters long;\n- the first character is one of the capital letters `A` (architecture), `S` (structure), "
    "`M` (mechanical) or `E` (electrical);\n- the second character is a hyphen `-`;\n- the last three characters are all digits.\n\n"
    "Check a code and state whether it is valid.\n\n"
    "Example: `S-101` gives `SAH`; `s-101`, `S101`, `S-1A1` and `S-1010` each give `TIDAK SAH`.",
    "One line: the drawing code (letters, digits and punctuation, no spaces).",
    "One line: `SAH` (valid) or `TIDAK SAH` (not valid); Indonesian words.",
    "The code is 1 to 10 characters long."),
   ("فحص رمز المخطط",
    "تُعطى كل ورقة مخطط في المشروع رمزًا وفق القواعد الآتية:\n\n"
    "- طوله 5 أحرف تمامًا؛\n- الحرف الأول أحد الأحرف الكبيرة `A` (معماري) أو `S` (إنشائي) أو `M` (ميكانيكي) أو `E` (كهربائي)؛\n"
    "- الحرف الثاني شرطة `-`؛\n- الأحرف الثلاثة الأخيرة كلها أرقام.\n\n"
    "افحص رمزًا وبيِّن هل هو صحيح.\n\n"
    "مثال: `S-101` يعطي `SAH`؛ وكلٌّ من `s-101` و`S101` و`S-1A1` و`S-1010` يعطي `TIDAK SAH`.",
    "سطر واحد: رمز المخطط (حروف وأرقام وعلامات ترقيم بلا مسافات).",
    "سطر واحد: `SAH` (صحيح) أو `TIDAK SAH` (غير صحيح)؛ بالإندونيسية.",
    "طول الرمز من 1 إلى 10 أحرف."))

py("fungsi-kemiringan", "mudah_sedang",
   AMAN + "while True:\n    j = r.choice([20, 25, 40, 50, 100])\n    e = [r.randint(1000, 3000)]\n    for _ in range(r.randint(1, 14)):\n"
   "        e.append(e[-1] + r.randint(-300, 300))\n    if i == 3 and len(e) > 3:\n        e[2] = e[1] + 250\n        e[3] = e[2] - 250\n"
   "    if all(aman((b - a) / j, 2) for a, b in zip(e, e[1:])):\n        break\nprint(j)\nprint(*e)\n",
   r'''
def kemiringan(elevasi_awal, elevasi_akhir, jarak_m):
    return (elevasi_akhir - elevasi_awal) / (jarak_m * 100) * 100


jarak = int(input())
elevasi = list(map(int, input().split()))
segmen_terjal = 1
terjal = kemiringan(elevasi[0], elevasi[1], jarak)
for k in range(1, len(elevasi) - 1):
    g = kemiringan(elevasi[k], elevasi[k + 1], jarak)
    if abs(g) > abs(terjal):
        terjal = g
        segmen_terjal = k + 1
print(f"Segmen {segmen_terjal}: {terjal:.2f}%")
''',
   ("Fungsi Kemiringan Memanjang",
    "Elevasi sumbu jalan diukur di titik-titik berjarak sama. Tulis fungsi `kemiringan(elevasi_awal, elevasi_akhir, jarak_m)` yang "
    "mengembalikan kemiringan sebuah segmen dalam persen: beda elevasi dibagi jarak (dalam satuan yang sama), dikali 100. Elevasi "
    "diberikan dalam cm dan jarak dalam m. Kemiringan negatif berarti turun.\n\n"
    "Pakai fungsi itu untuk setiap segmen (segmen 1 antara titik 1 dan 2, dan seterusnya), lalu cetak segmen yang paling terjal, "
    "yaitu yang nilai mutlak kemiringannya terbesar, beserta kemiringannya (dengan tandanya, dua angka di belakang titik). Bila ada "
    "yang sama terjal, ambil segmen yang paling awal.\n\n"
    "Contoh: jarak `50` dan elevasi `1000 1100 1050 1250` menghasilkan `Segmen 3: 4.00%` (kemiringan 2.00, -1.00, 4.00).",
    "Baris pertama: jarak antartitik (m, bilangan bulat). Baris kedua: elevasi tiap titik (cm, bilangan bulat) dipisah satu spasi.",
    "Satu baris berbentuk `Segmen K: G%`, dengan G dua angka di belakang titik.",
    "2 sampai 15 titik; jarak 20 sampai 100; beda elevasi dua titik bersebelahan paling banyak 300."),
   ("A Function for Longitudinal Grade",
    "The elevation of a road centre line is measured at equally spaced points. Write a function "
    "`kemiringan(elevasi_awal, elevasi_akhir, jarak_m)` that returns the grade of a segment in percent: the elevation difference "
    "divided by the distance (in the same unit), times 100. Elevations are given in cm and the distance in m. A negative grade means "
    "downhill.\n\n"
    "Use the function for every segment (segment 1 between points 1 and 2, and so on), then print the steepest segment, the one "
    "whose grade has the largest absolute value, with its grade (with its sign, two digits after the point). If several are equally "
    "steep, take the earliest.\n\n"
    "Example: distance `50` and elevations `1000 1100 1050 1250` give `Segmen 3: 4.00%` (grades 2.00, -1.00, 4.00).",
    "First line: the distance between points (m, a whole number). Second line: the elevation of every point (cm, whole numbers) "
    "separated by single spaces.",
    "One line of the form `Segmen K: G%`, with G having two digits after the point (the word stays in Indonesian).",
    "2 to 15 points; distance 20 to 100; neighbouring elevations differ by at most 300."),
   ("دالة الميل الطولي",
    "قيس منسوب محور الطريق عند نقاط متساوية التباعد. اكتب الدالة `kemiringan(elevasi_awal, elevasi_akhir, jarak_m)` التي تُرجع ميل "
    "قطعة بالنسبة المئوية: فرق المنسوب مقسومًا على المسافة (بالوحدة نفسها) مضروبًا في 100. المناسيب بالسنتيمتر والمسافة بالمتر. "
    "الميل السالب يعني نزولًا.\n\n"
    "استعمل الدالة لكل قطعة (القطعة 1 بين النقطتين 1 و2، وهكذا) ثم اطبع أشدّ القطع ميلًا، أي التي قيمة ميلها المطلقة هي الكبرى، "
    "مع ميلها (بإشارته وبمنزلتين عشريتين). إذا تساوت قطع في الشدة فخذ أبكرها.\n\n"
    "مثال: مسافة `50` ومناسيب `1000 1100 1050 1250` تعطي `Segmen 3: 4.00%` (الميول 2.00 و-1.00 و4.00).",
    "السطر الأول: المسافة بين النقاط (م، عدد صحيح). السطر الثاني: منسوب كل نقطة (سم، أعداد صحيحة) تفصل بينها مسافة.",
    "سطر واحد بالشكل `Segmen K: G%` حيث G بمنزلتين عشريتين (تبقى الكلمة بالإندونيسية).",
    "من 2 إلى 15 نقطة؛ المسافة من 20 إلى 100؛ والفرق بين نقطتين متجاورتين لا يزيد على 300."),
   fungsi=True)

py("rata-tanpa-ekstrem", "mudah_sedang",
   AMAN + "while True:\n    v = [r.randint(180, 420) for _ in range(r.randint(3, 15))]\n    if i == 2:\n        v = [v[0]] * len(v)\n"
   "    if aman((sum(v) - min(v) - max(v)) / (len(v) - 2), 2):\n        break\nprint(*v)\n",
   r'''
hasil = list(map(int, input().split()))
hasil.sort()
tengah = hasil[1:-1]
rata_rata = sum(tengah) / len(tengah)
print(f"Dibuang: {hasil[0]} dan {hasil[-1]}")
print(f"Rata-rata: {rata_rata:.2f}")
''',
   ("Rata-rata Tanpa Nilai Ekstrem",
    "Supaya satu benda uji yang cacat tidak merusak hasil, laboratorium membuang satu nilai terkecil dan satu nilai terbesar, lalu "
    "merata-ratakan sisanya. Bila nilai terkecil atau terbesar muncul lebih dari sekali, hanya satu yang dibuang. Urutkan list, "
    "ambil irisannya, lalu cetak kedua nilai yang dibuang dan rata-rata sisanya (dua angka di belakang titik).\n\n"
    "Contoh: `300 250 310 290 400` menghasilkan\n\n```\nDibuang: 250 dan 400\nRata-rata: 300.00\n```",
    "Satu baris: beberapa hasil uji (kg/cm², bilangan bulat) dipisah satu spasi.",
    "Baris pertama: `Dibuang: A dan B` (terkecil lalu terbesar). Baris kedua: `Rata-rata: R` dengan dua angka di belakang titik.",
    "3 sampai 15 hasil; setiap hasil 180 sampai 420."),
   ("Average Without the Extremes",
    "So that one defective specimen does not spoil the result, a laboratory drops one smallest and one largest value, then averages "
    "the rest. If the smallest or the largest value occurs more than once, only one is dropped. Sort the list, take a slice of it, "
    "then print the two dropped values and the average of the rest (two digits after the point).\n\n"
    "Example: `300 250 310 290 400` gives\n\n```\nDibuang: 250 dan 400\nRata-rata: 300.00\n```",
    "One line: several results (kg/cm², whole numbers) separated by single spaces.",
    "First line: `Dibuang: A dan B` (smallest then largest). Second line: `Rata-rata: R` with two digits after the point "
    "(Indonesian words).",
    "3 to 15 results; each is 180 to 420."),
   ("المتوسط دون القيم المتطرفة",
    "حتى لا تُفسد عيّنة معيبة النتيجة، يحذف المختبر قيمة صغرى واحدة وقيمة كبرى واحدة ثم يحسب متوسط الباقي. إذا تكررت القيمة الصغرى "
    "أو الكبرى فتُحذف واحدة فقط. رتِّب القائمة وخذ شريحة منها ثم اطبع القيمتين المحذوفتين ومتوسط الباقي (بمنزلتين عشريتين).\n\n"
    "مثال: `300 250 310 290 400` يعطي\n\n```\nDibuang: 250 dan 400\nRata-rata: 300.00\n```",
    "سطر واحد: عدة نتائج (كغ/سم²، أعداد صحيحة) تفصل بينها مسافة واحدة.",
    "السطر الأول: `Dibuang: A dan B` (الصغرى ثم الكبرى). السطر الثاني: `Rata-rata: R` بمنزلتين عشريتين (بالإندونيسية).",
    "من 3 إلى 15 نتيجة؛ وكل نتيجة من 180 إلى 420."))

# ================================ SEDANG (6) ================================

py("interpolasi-tabel", "sedang",
   AMAN + "while True:\n    n = r.randint(2, 10)\n    e = [r.randint(10, 60)]\n    v = [r.randint(0, 50)]\n    for _ in range(n - 1):\n"
   "        e.append(e[-1] + r.randint(1, 6))\n        v.append(v[-1] + r.randint(5, 400))\n"
   "    q = [e[0] * 10, e[-1] * 10, e[r.randrange(n)] * 10][i] if i < 3 else r.randint(e[0] * 10, e[-1] * 10)\n"
   "    k = max(j for j in range(n) if e[j] * 10 <= q)\n    k = min(k, n - 2)\n"
   "    y = v[k] + (q / 10 - e[k]) * (v[k + 1] - v[k]) / (e[k + 1] - e[k])\n    if aman(y, 2):\n        break\n"
   "print(n)\nfor a, b in zip(e, v):\n    print(a, b)\nprint(f'{q / 10:.1f}')\n",
   r'''
def interpolasi(x, x1, y1, x2, y2):
    return y1 + (x - x1) * (y2 - y1) / (x2 - x1)


banyak = int(input())
elevasi = []
volume = []
for _ in range(banyak):
    e, v = map(int, input().split())
    elevasi.append(e)
    volume.append(v)
dicari = float(input())
k = 0
while k < banyak - 2 and dicari > elevasi[k + 1]:
    k = k + 1
hasil = interpolasi(dicari, elevasi[k], volume[k], elevasi[k + 1], volume[k + 1])
print(f"Volume: {hasil:.2f} ribu m3")
''',
   ("Interpolasi Tabel Tampungan Waduk",
    "Tabel lengkung kapasitas waduk memberi volume tampungan pada beberapa elevasi muka air, terurut naik. Untuk elevasi di antara dua "
    "baris tabel, volume dihitung dengan interpolasi linear antara baris tepat di bawah dan tepat di atasnya.\n\n"
    "Tulis fungsi `interpolasi(x, x1, y1, x2, y2)` yang mengembalikan y1 + (x − x1) × (y2 − y1) / (x2 − x1). Simpan tabel dalam "
    "dua list, cari dua baris yang mengapit elevasi yang ditanyakan, lalu panggil fungsi itu. Bila elevasi tepat sama dengan sebuah "
    "baris tabel, hasilnya volume baris itu.\n\n"
    "Contoh: masukan\n\n```\n3\n20 100\n25 400\n30 900\n27.0\n```\n\nmenghasilkan `Volume: 600.00 ribu m3`.",
    "Baris pertama: banyaknya baris tabel N. Lalu N baris, masing-masing elevasi (m) dan volume (ribu m³), bilangan bulat dipisah "
    "spasi, elevasi terurut naik. Baris terakhir: elevasi yang ditanyakan (m, bilangan pecahan dengan satu angka di belakang titik).",
    "Satu baris berbentuk `Volume: V ribu m3`, dengan V dua angka di belakang titik.",
    "N 2 sampai 10; elevasi yang ditanyakan selalu di antara elevasi pertama dan terakhir tabel (keduanya termasuk)."),
   ("Interpolating a Reservoir Storage Table",
    "A reservoir capacity table gives the storage volume at several water-surface elevations, in ascending order. For an elevation "
    "between two table rows, the volume is found by linear interpolation between the row just below and the row just above.\n\n"
    "Write a function `interpolasi(x, x1, y1, x2, y2)` that returns y1 + (x − x1) × (y2 − y1) / (x2 − x1). Keep the table in two "
    "lists, find the two rows that bracket the elevation asked for, then call the function. If the elevation is exactly equal to a "
    "table row, the result is the volume of that row.\n\n"
    "Example: the input\n\n```\n3\n20 100\n25 400\n30 900\n27.0\n```\n\ngives `Volume: 600.00 ribu m3`.",
    "First line: the number of table rows N. Then N lines, each an elevation (m) and a volume (thousand m³), whole numbers separated "
    "by a space, elevations ascending. Last line: the elevation asked for (m, a decimal number with one digit after the point).",
    "One line of the form `Volume: V ribu m3`, with V having two digits after the point (`ribu` means thousand).",
    "N is 2 to 10; the elevation asked for is always between the first and the last elevation of the table (both included)."),
   ("الاستيفاء في جدول تخزين الخزّان",
    "يعطي جدول منحنى السعة للخزّان حجم التخزين عند عدة مناسيب لسطح الماء مرتبة تصاعديًّا. لمنسوبٍ بين صفّين من الجدول يُحسب الحجم "
    "بالاستيفاء الخطي بين الصف الذي تحته مباشرة والذي فوقه مباشرة.\n\n"
    "اكتب الدالة `interpolasi(x, x1, y1, x2, y2)` التي تُرجع y1 + (x − x1) × (y2 − y1) / (x2 − x1). احفظ الجدول في قائمتين "
    "وابحث عن الصفّين اللذين يحصران المنسوب المطلوب ثم استدعِ الدالة. إذا ساوى المنسوب صفًّا من الجدول تمامًا فالناتج حجم ذلك الصف.\n\n"
    "مثال: المدخل\n\n```\n3\n20 100\n25 400\n30 900\n27.0\n```\n\nيعطي `Volume: 600.00 ribu m3`.",
    "السطر الأول: عدد صفوف الجدول N. ثم N سطرًا، في كل سطر منسوب (م) وحجم (ألف م³)، عددان صحيحان تفصل بينهما مسافة والمناسيب "
    "تصاعدية. السطر الأخير: المنسوب المطلوب (م، عدد عشري بمنزلة واحدة).",
    "سطر واحد بالشكل `Volume: V ribu m3` حيث V بمنزلتين عشريتين (`ribu` تعني ألفًا).",
    "N من 2 إلى 10؛ والمنسوب المطلوب دائمًا بين أول منسوب وآخر منسوب في الجدول (شاملًا إياهما)."),
   fungsi=True)

py("histogram-hujan", "sedang",
   "lo, hi = [(0, 99), (0, 39), (60, 99), (20, 59)][i % 4]\nprint(*[r.randint(lo, hi) for _ in range(r.randint(1, 30))])\n",
   r'''
curah = list(map(int, input().split()))
jumlah = [0, 0, 0, 0, 0]
for nilai in curah:
    kelas = nilai // 20
    jumlah[kelas] = jumlah[kelas] + 1
for kelas in range(5):
    bawah = kelas * 20
    baris = f"{bawah}-{bawah + 19}: {jumlah[kelas]}"
    if jumlah[kelas] > 0:
        baris = baris + " " + "#" * jumlah[kelas]
    print(baris)
''',
   ("Histogram Curah Hujan",
    "Curah hujan harian (0 sampai 99 mm) dikelompokkan ke lima kelas selebar 20 mm: 0-19, 20-39, 40-59, 60-79, 80-99. Hitung "
    "banyaknya hari di setiap kelas dengan sebuah list, lalu cetak histogram: untuk setiap kelas, rentangnya, banyaknya hari, dan "
    "tanda `#` sebanyak hari itu. Kelas yang kosong dicetak dengan angka 0 tanpa tanda `#`.\n\n"
    "Contoh: `5 12 25 47 81 30 18` menghasilkan\n\n"
    "```\n0-19: 3 ###\n20-39: 2 ##\n40-59: 1 #\n60-79: 0\n80-99: 1 #\n```",
    "Satu baris: curah hujan harian (mm, bilangan bulat) dipisah satu spasi.",
    "Lima baris, satu untuk setiap kelas, berbentuk `A-B: N` diikuti satu spasi dan N tanda `#` bila N lebih dari 0.",
    "1 sampai 30 hari; curah hujan 0 sampai 99."),
   ("Rainfall Histogram",
    "Daily rainfall (0 to 99 mm) is grouped into five classes 20 mm wide: 0-19, 20-39, 40-59, 60-79, 80-99. Count the days in each "
    "class with a list, then print a histogram: for each class its range, the number of days, and as many `#` signs as days. An "
    "empty class prints the number 0 without any `#`.\n\n"
    "Example: `5 12 25 47 81 30 18` gives\n\n"
    "```\n0-19: 3 ###\n20-39: 2 ##\n40-59: 1 #\n60-79: 0\n80-99: 1 #\n```",
    "One line: the daily rainfall (mm, whole numbers) separated by single spaces.",
    "Five lines, one for each class, of the form `A-B: N` followed by one space and N `#` signs when N is more than 0.",
    "1 to 30 days; rainfall 0 to 99."),
   ("المدرّج التكراري للهطول",
    "يُصنَّف الهطول اليومي (من 0 إلى 99 مم) في خمس فئات عرض كلٍّ منها 20 مم: 0-19 و20-39 و40-59 و60-79 و80-99. عُدّ الأيام في كل "
    "فئة بقائمة ثم اطبع مدرّجًا تكراريًّا: لكل فئة مداها وعدد الأيام وعلامات `#` بعدد تلك الأيام. الفئة الفارغة تُطبع بالعدد 0 دون `#`.\n\n"
    "مثال: `5 12 25 47 81 30 18` يعطي\n\n```\n0-19: 3 ###\n20-39: 2 ##\n40-59: 1 #\n60-79: 0\n80-99: 1 #\n```",
    "سطر واحد: الهطول اليومي (مم، أعداد صحيحة) تفصل بينها مسافة واحدة.",
    "خمسة أسطر، سطر لكل فئة، بالشكل `A-B: N` متبوعًا بمسافة و N علامة `#` إذا كان N أكبر من 0.",
    "من 1 إلى 30 يومًا؛ والهطول من 0 إلى 99."))

py("material-kurang", "sedang",
   "nama = ['semen', 'pasir', 'kerikil', 'bata', 'besi', 'kayu', 'paku', 'cat', 'keramik', 'pipa']\n"
   "s = r.sample(nama, r.randint(1, 8))\nprint(len(s))\nfor x in s:\n    print(x, r.randint(0, 500) if i != 1 else 5000)\n"
   "m = r.randint(1, 15)\nprint(m)\nfor _ in range(m):\n    print(r.choice(nama if i % 2 == 0 else s), r.randint(1, 200))\n",
   r'''
stok = {}
banyak_stok = int(input())
for _ in range(banyak_stok):
    nama, jumlah = input().split()
    stok[nama] = int(jumlah)
butuh = {}
banyak_butuh = int(input())
for _ in range(banyak_butuh):
    nama, jumlah = input().split()
    butuh[nama] = butuh.get(nama, 0) + int(jumlah)
ada_kurang = False
for nama in sorted(butuh):
    kurang = butuh[nama] - stok.get(nama, 0)
    if kurang > 0:
        print(f"{nama}: kurang {kurang}")
        ada_kurang = True
if not ada_kurang:
    print("CUKUP")
''',
   ("Material yang Kurang",
    "Gudang mempunyai daftar stok material, dan pelaksana menyerahkan daftar kebutuhan minggu ini. Sebuah material boleh muncul "
    "beberapa kali di daftar kebutuhan (jumlahkan), dan boleh tidak ada di daftar stok (stoknya dianggap 0). Dengan dictionary, "
    "tentukan material yang stoknya kurang dari kebutuhan dan berapa kekurangannya. Cetak terurut menurut abjad nama material. "
    "Bila semua kebutuhan terpenuhi, cetak `CUKUP`.\n\n"
    "Contoh: masukan\n\n```\n2\nsemen 100\npasir 20\n3\nsemen 80\nbata 500\nsemen 40\n```\n\nmenghasilkan\n\n"
    "```\nbata: kurang 500\nsemen: kurang 20\n```",
    "Baris pertama: banyaknya baris stok S. Lalu S baris: nama material dan jumlah stok. Baris berikutnya: banyaknya baris kebutuhan "
    "K. Lalu K baris: nama material dan jumlah yang dibutuhkan. Nama berupa satu kata huruf kecil; jumlah bilangan bulat.",
    "Satu baris `nama: kurang X` untuk setiap material yang kurang, terurut menurut abjad; atau satu baris `CUKUP`.",
    "S 1 sampai 8, tidak ada nama stok yang berulang; K 1 sampai 15."),
   ("Materials in Short Supply",
    "A store has a stock list of materials, and the site hands in this week's list of needs. A material may appear several times in "
    "the needs (add them up), and may be missing from the stock list (its stock counts as 0). With dictionaries, find the materials "
    "whose stock is less than the need, and by how much. Print them in alphabetical order of the material name. If every need is "
    "met, print `CUKUP`.\n\n"
    "Example: the input\n\n```\n2\nsemen 100\npasir 20\n3\nsemen 80\nbata 500\nsemen 40\n```\n\ngives\n\n"
    "```\nbata: kurang 500\nsemen: kurang 20\n```",
    "First line: the number of stock lines S. Then S lines: a material name and its stock. Next line: the number of need lines K. "
    "Then K lines: a material name and the quantity needed. Names are one lower-case word; quantities are whole numbers.",
    "One line `nama: kurang X` for every material in short supply, in alphabetical order; or the single line `CUKUP` "
    "(Indonesian words).",
    "S is 1 to 8, no stock name repeats; K is 1 to 15."),
   ("المواد الناقصة",
    "في المخزن قائمة بأرصدة المواد، ويقدّم المنفّذ قائمة احتياجات هذا الأسبوع. قد تظهر المادة عدة مرات في قائمة الاحتياجات (تُجمع)، "
    "وقد لا تكون في قائمة الأرصدة (يُعدّ رصيدها 0). باستعمال القواميس حدِّد المواد التي رصيدها أقل من الحاجة ومقدار النقص. "
    "اطبعها مرتبة أبجديًّا بحسب اسم المادة. إذا لُبّيت كل الاحتياجات فاطبع `CUKUP`.\n\n"
    "مثال: المدخل\n\n```\n2\nsemen 100\npasir 20\n3\nsemen 80\nbata 500\nsemen 40\n```\n\nيعطي\n\n"
    "```\nbata: kurang 500\nsemen: kurang 20\n```",
    "السطر الأول: عدد أسطر الأرصدة S. ثم S سطرًا: اسم المادة ورصيدها. السطر التالي: عدد أسطر الاحتياجات K. ثم K سطرًا: اسم المادة "
    "والكمية المطلوبة. الأسماء كلمة واحدة بأحرف صغيرة؛ والكميات أعداد صحيحة.",
    "سطر `nama: kurang X` لكل مادة ناقصة بالترتيب الأبجدي؛ أو سطر واحد `CUKUP` (بالإندونيسية).",
    "S من 1 إلى 8 ولا يتكرر اسم في الأرصدة؛ K من 1 إلى 15."))

py("inersia-komposit", "sedang",
   AMAN + "while True:\n    n = r.randint(1, 4)\n    bagian = []\n    dasar = 0\n    for _ in range(n):\n        b = r.randint(2, 30) * 2\n"
   "        h = r.randint(2, 20) * 2\n        bagian.append((b, h, dasar + h // 2))\n        dasar += h\n"
   "    luas = sum(b * h for b, h, y in bagian)\n    yb = sum(b * h * y for b, h, y in bagian) / luas\n"
   "    ix = sum(b * h ** 3 / 12 + b * h * (y - yb) ** 2 for b, h, y in bagian)\n    if aman(yb, 2) and aman(ix, 1):\n        break\n"
   "print(n)\nfor b, h, y in bagian:\n    print(b, h, y)\n",
   r'''
banyak = int(input())
lebar = []
tinggi = []
pusat = []
for _ in range(banyak):
    b, h, y = map(int, input().split())
    lebar.append(b)
    tinggi.append(h)
    pusat.append(y)
luas_total = 0
momen_statis = 0
for k in range(banyak):
    luas = lebar[k] * tinggi[k]
    luas_total = luas_total + luas
    momen_statis = momen_statis + luas * pusat[k]
y_bar = momen_statis / luas_total
inersia = 0
for k in range(banyak):
    luas = lebar[k] * tinggi[k]
    inersia = inersia + lebar[k] * tinggi[k] ** 3 / 12 + luas * (pusat[k] - y_bar) ** 2
print(f"A = {luas_total} cm2")
print(f"ybar = {y_bar:.2f} cm")
print(f"I = {inersia:.1f} cm4")
''',
   ("Titik Berat dan Momen Inersia Penampang Tersusun",
    "Sebuah penampang (misalnya balok T) disusun dari beberapa persegi panjang. Untuk setiap persegi panjang diketahui lebar b, "
    "tinggi h, dan tinggi titik beratnya y dari sisi bawah penampang. Hitung:\n\n"
    "1. luas total A = jumlah b × h;\n"
    "2. letak titik berat ȳ = jumlah (b × h × y) dibagi A;\n"
    "3. momen inersia terhadap sumbu mendatar lewat titik berat: I = jumlah (b × h³ / 12 + b × h × (y − ȳ)²).\n\n"
    "Karena ȳ diperlukan untuk langkah ketiga, data perlu disimpan dalam list.\n\n"
    "Contoh: masukan\n\n```\n2\n20 40 20\n60 10 45\n```\n\n(badan 20 × 40 dan sayap 60 × 10 di atasnya) menghasilkan\n\n"
    "```\nA = 1400 cm2\nybar = 30.71 cm\nI = 325952.4 cm4\n```",
    "Baris pertama: banyaknya persegi panjang N. Lalu N baris, masing-masing b, h, dan y (cm, bilangan bulat) dipisah spasi.",
    "Tiga baris: `A = X cm2` (bilangan bulat), `ybar = Y cm` (dua desimal), `I = Z cm4` (satu desimal).",
    "N 1 sampai 4; b 4 sampai 60; h 4 sampai 40."),
   ("Centroid and Moment of Inertia of a Built-Up Section",
    "A section (a T-beam, for example) is built from several rectangles. For each rectangle the width b, the height h and the height "
    "y of its centroid above the bottom of the section are known. Compute:\n\n"
    "1. the total area A = sum of b × h;\n"
    "2. the centroid position ȳ = sum of (b × h × y) divided by A;\n"
    "3. the moment of inertia about the horizontal axis through the centroid: I = sum of (b × h³ / 12 + b × h × (y − ȳ)²).\n\n"
    "Because ȳ is needed for the third step, the data must be kept in lists.\n\n"
    "Example: the input\n\n```\n2\n20 40 20\n60 10 45\n```\n\n(a 20 × 40 web with a 60 × 10 flange on top) gives\n\n"
    "```\nA = 1400 cm2\nybar = 30.71 cm\nI = 325952.4 cm4\n```",
    "First line: the number of rectangles N. Then N lines, each b, h and y (cm, whole numbers) separated by spaces.",
    "Three lines: `A = X cm2` (a whole number), `ybar = Y cm` (two decimals), `I = Z cm4` (one decimal).",
    "N is 1 to 4; b is 4 to 60; h is 4 to 40."),
   ("مركز الثقل وعزم القصور الذاتي لمقطع مركّب",
    "يتكوّن مقطع (جائز T مثلًا) من عدة مستطيلات. لكل مستطيل يُعرف العرض b والارتفاع h وارتفاع مركز ثقله y عن أسفل المقطع. احسب:\n\n"
    "1. المساحة الكلية A = مجموع b × h؛\n"
    "2. موضع مركز الثقل ȳ = مجموع (b × h × y) مقسومًا على A؛\n"
    "3. عزم القصور الذاتي حول المحور الأفقي المارّ بمركز الثقل: I = مجموع (b × h³ / 12 + b × h × (y − ȳ)²).\n\n"
    "ولأن ȳ لازم للخطوة الثالثة فلا بدّ من حفظ البيانات في قوائم.\n\n"
    "مثال: المدخل\n\n```\n2\n20 40 20\n60 10 45\n```\n\n(جذع 20 × 40 فوقه جناح 60 × 10) يعطي\n\n"
    "```\nA = 1400 cm2\nybar = 30.71 cm\nI = 325952.4 cm4\n```",
    "السطر الأول: عدد المستطيلات N. ثم N سطرًا، في كل سطر b و h و y (سم، أعداد صحيحة) تفصل بينها مسافات.",
    "ثلاثة أسطر: `A = X cm2` (عدد صحيح) و`ybar = Y cm` (منزلتان) و`I = Z cm4` (منزلة واحدة).",
    "N من 1 إلى 4؛ b من 4 إلى 60؛ h من 4 إلى 40."))

py("grid-galian", "sedang",
   "br = r.randint(1, 6)\nkl = r.randint(1, 8)\nprint(br, kl)\nprint(r.choice([2, 5, 10, 20]))\nm = r.randint(50, 400)\n"
   "for b in range(br):\n    print(*[m if i == 2 else r.randint(0, 400) for _ in range(kl)])\n",
   r'''
baris, kolom = map(int, input().split())
sisi = int(input())
petak = []
for _ in range(baris):
    petak.append(list(map(int, input().split())))
jumlah_cm = 0
terdalam = -1
baris_terdalam = 0
kolom_terdalam = 0
for b in range(baris):
    for k in range(kolom):
        jumlah_cm = jumlah_cm + petak[b][k]
        if petak[b][k] > terdalam:
            terdalam = petak[b][k]
            baris_terdalam = b + 1
            kolom_terdalam = k + 1
volume = sisi * sisi * jumlah_cm / 100
print(f"Volume: {volume:.2f} m3")
print(f"Terdalam: baris {baris_terdalam} kolom {kolom_terdalam}")
''',
   ("Volume Galian dari Petak-petak",
    "Lahan dibagi menjadi petak-petak persegi yang sama besar, tersusun dalam baris dan kolom. Untuk setiap petak diketahui kedalaman "
    "galian rata-ratanya dalam cm. Simpan data dalam list di dalam list. Hitung volume galian seluruhnya (luas satu petak × jumlah "
    "kedalaman, dalam m³ dengan dua angka di belakang titik) dan tentukan petak yang paling dalam. Bila ada beberapa yang sama "
    "dalam, ambil yang pertama ditemui saat membaca baris demi baris dari kiri ke kanan.\n\n"
    "Contoh: masukan\n\n```\n2 3\n10\n50 120 80\n200 90 200\n```\n\nmenghasilkan\n\n"
    "```\nVolume: 740.00 m3\nTerdalam: baris 2 kolom 1\n```",
    "Baris pertama: banyaknya baris R dan kolom C. Baris kedua: sisi petak (m). Lalu R baris, masing-masing C kedalaman (cm) dipisah "
    "spasi. Semuanya bilangan bulat.",
    "Dua baris: `Volume: V m3` (dua desimal), lalu `Terdalam: baris B kolom K`.",
    "R 1 sampai 6; C 1 sampai 8; sisi 2 sampai 20; kedalaman 0 sampai 400."),
   ("Excavation Volume from a Grid",
    "A site is divided into equal square cells arranged in rows and columns. For each cell the average excavation depth in cm is "
    "known. Keep the data in a list of lists. Compute the total excavation volume (area of one cell × sum of the depths, in m³ with "
    "two digits after the point) and find the deepest cell. If several are equally deep, take the first one met when reading row by "
    "row from left to right.\n\n"
    "Example: the input\n\n```\n2 3\n10\n50 120 80\n200 90 200\n```\n\ngives\n\n"
    "```\nVolume: 740.00 m3\nTerdalam: baris 2 kolom 1\n```",
    "First line: the number of rows R and columns C. Second line: the side of a cell (m). Then R lines, each with C depths (cm) "
    "separated by spaces. All whole numbers.",
    "Two lines: `Volume: V m3` (two decimals), then `Terdalam: baris B kolom K` (Indonesian words: baris row, kolom column).",
    "R is 1 to 6; C is 1 to 8; side 2 to 20; depths 0 to 400."),
   ("حجم الحفر من شبكة مربعات",
    "قُسِّم الموقع إلى مربعات متساوية مرتبة في صفوف وأعمدة. لكل مربع يُعرف متوسط عمق الحفر بالسنتيمتر. احفظ البيانات في قائمة من "
    "القوائم. احسب حجم الحفر الكلي (مساحة المربع الواحد × مجموع الأعماق، بالمتر المكعب وبمنزلتين عشريتين) وحدِّد أعمق مربع. إذا تساوت "
    "مربعات في العمق فخذ أول ما يُصادَف عند القراءة صفًّا صفًّا من اليسار إلى اليمين.\n\n"
    "مثال: المدخل\n\n```\n2 3\n10\n50 120 80\n200 90 200\n```\n\nيعطي\n\n"
    "```\nVolume: 740.00 m3\nTerdalam: baris 2 kolom 1\n```",
    "السطر الأول: عدد الصفوف R وعدد الأعمدة C. السطر الثاني: ضلع المربع (م). ثم R سطرًا، في كل سطر C عمقًا (سم) تفصل بينها مسافات. "
    "كلها أعداد صحيحة.",
    "سطران: `Volume: V m3` (منزلتان) ثم `Terdalam: baris B kolom K` (بالإندونيسية: baris صف، kolom عمود).",
    "R من 1 إلى 6؛ C من 1 إلى 8؛ الضلع من 2 إلى 20؛ والأعماق من 0 إلى 400."))

py("peringkat-penawaran", "sedang",
   "nama = ['PT-ADHI', 'PT-BINA', 'CV-CIPTA', 'PT-DAYA', 'CV-ERA', 'PT-FAJAR', 'CV-GUNA', 'PT-HASTA', 'CV-INTI', 'PT-JAYA']\n"
   "s = r.sample(nama, r.randint(1, 10))\nh = r.randint(400, 3000)\nprint(len(s))\nfor x in s:\n"
   "    print(x, h if i == 1 else r.choice([h, h + 5, r.randint(400, 3000)]))\n",
   r'''
banyak = int(input())
penawaran = []
for _ in range(banyak):
    nama, harga = input().split()
    penawaran.append((int(harga), nama))
penawaran.sort()
for k in range(banyak):
    harga, nama = penawaran[k]
    print(f"{k + 1}. {nama} {harga}")
''',
   ("Peringkat Penawaran",
    "Panitia tender menerima penawaran harga dari beberapa perusahaan dan menyusun peringkatnya: harga terendah di peringkat 1. "
    "Bila dua harga sama, perusahaan yang namanya lebih dahulu menurut abjad didahulukan. Simpan setiap penawaran sebagai tuple "
    "`(harga, nama)` dalam sebuah list; list berisi tuple diurutkan menurut unsur pertama, lalu unsur kedua.\n\n"
    "Contoh: masukan\n\n```\n3\nPT-DAYA 950\nCV-ERA 900\nPT-ADHI 950\n```\n\nmenghasilkan\n\n"
    "```\n1. CV-ERA 900\n2. PT-ADHI 950\n3. PT-DAYA 950\n```",
    "Baris pertama: banyaknya penawaran N. Lalu N baris, masing-masing nama perusahaan (tanpa spasi, huruf kapital dan tanda hubung) "
    "dan harga (juta rupiah, bilangan bulat).",
    "N baris berbentuk `K. NAMA HARGA`, dari peringkat 1.",
    "N 1 sampai 10; nama perusahaan tidak berulang; harga 400 sampai 3005."),
   ("Ranking the Bids",
    "A tender committee receives price bids from several companies and ranks them: the lowest price is rank 1. When two prices are "
    "equal, the company whose name comes first alphabetically goes first. Keep each bid as a tuple `(harga, nama)` in a list; a list "
    "of tuples is sorted by the first item, then by the second.\n\n"
    "Example: the input\n\n```\n3\nPT-DAYA 950\nCV-ERA 900\nPT-ADHI 950\n```\n\ngives\n\n"
    "```\n1. CV-ERA 900\n2. PT-ADHI 950\n3. PT-DAYA 950\n```",
    "First line: the number of bids N. Then N lines, each a company name (no spaces, capital letters and hyphens) and a price "
    "(million rupiah, a whole number).",
    "N lines of the form `K. NAMA HARGA` (rank, name, price), from rank 1.",
    "N is 1 to 10; company names do not repeat; prices 400 to 3005."),
   ("ترتيب العروض",
    "تتلقى لجنة المناقصة عروض أسعار من عدة شركات وترتّبها: السعر الأدنى في المرتبة 1. إذا تساوى سعران قُدِّمت الشركة التي يسبق اسمها "
    "أبجديًّا. احفظ كل عرض صفًّا (tuple) بالشكل `(harga, nama)` في قائمة؛ وقائمة الصفوف تُرتَّب بحسب العنصر الأول ثم الثاني.\n\n"
    "مثال: المدخل\n\n```\n3\nPT-DAYA 950\nCV-ERA 900\nPT-ADHI 950\n```\n\nيعطي\n\n"
    "```\n1. CV-ERA 900\n2. PT-ADHI 950\n3. PT-DAYA 950\n```",
    "السطر الأول: عدد العروض N. ثم N سطرًا، في كل سطر اسم الشركة (بلا مسافات، أحرف كبيرة وشرطات) والسعر (مليون روبية، عدد صحيح).",
    "N سطرًا بالشكل `K. NAMA HARGA` (المرتبة ثم الاسم ثم السعر) ابتداءً من المرتبة 1.",
    "N من 1 إلى 10؛ لا تتكرر أسماء الشركات؛ والأسعار من 400 إلى 3005."))

# ================================= SULIT (4) =================================

py("luas-poligon", "sulit",
   AMAN + "import math\nwhile True:\n    n = r.randint(3, 10)\n    sudut = sorted(r.sample(range(0, 360, 5), n))\n"
   "    if i == 1:\n        sudut.reverse()\n    titik = []\n    for s in sudut:\n        jari = r.randint(20, 200)\n"
   "        titik.append((round(jari * math.cos(math.radians(s))) + 250, round(jari * math.sin(math.radians(s))) + 250))\n"
   "    if len(set(titik)) < n:\n        continue\n"
   "    kel = sum(math.hypot(titik[k][0] - titik[k - 1][0], titik[k][1] - titik[k - 1][1]) for k in range(n))\n"
   "    dua = sum(titik[k - 1][0] * titik[k][1] - titik[k][0] * titik[k - 1][1] for k in range(n))\n"
   "    if dua != 0 and aman(kel, 2):\n        break\nprint(n)\nfor x, y in titik:\n    print(x, y)\n",
   r'''
import math


def jarak(x1, y1, x2, y2):
    return math.sqrt((x2 - x1) ** 2 + (y2 - y1) ** 2)


def luas_poligon(xs, ys):
    jumlah = 0
    for k in range(len(xs)):
        berikut = (k + 1) % len(xs)
        jumlah = jumlah + xs[k] * ys[berikut] - xs[berikut] * ys[k]
    return abs(jumlah) / 2


banyak = int(input())
xs = []
ys = []
for _ in range(banyak):
    x, y = map(int, input().split())
    xs.append(x)
    ys.append(y)
keliling = 0
for k in range(banyak):
    berikut = (k + 1) % banyak
    keliling = keliling + jarak(xs[k], ys[k], xs[berikut], ys[berikut])
print(f"Luas: {luas_poligon(xs, ys):.1f} m2")
print(f"Keliling: {keliling:.2f} m")
''',
   ("Luas dan Keliling Lahan Poligon",
    "Batas sebidang lahan diukur sebagai titik-titik sudut berkoordinat (x, y) yang berurutan mengelilingi lahan; titik terakhir "
    "tersambung kembali ke titik pertama. Luasnya dihitung dengan rumus koordinat:\n\n"
    "Luas = | jumlah (x_k × y_(k+1) − x_(k+1) × y_k) | / 2,\n\n"
    "dijumlahkan untuk setiap titik k dengan titik berikutnya (titik setelah yang terakhir adalah yang pertama). Tanda mutlak "
    "diperlukan karena titik boleh diberikan searah atau berlawanan arah jarum jam. Keliling adalah jumlah panjang semua sisi.\n\n"
    "Tulis fungsi `luas_poligon(xs, ys)` yang menerima list koordinat x dan list koordinat y, dan fungsi `jarak(x1, y1, x2, y2)`.\n\n"
    "Contoh: masukan\n\n```\n4\n0 0\n40 0\n40 30\n0 30\n```\n\nmenghasilkan\n\n```\nLuas: 1200.0 m2\nKeliling: 140.00 m\n```",
    "Baris pertama: banyaknya titik N. Lalu N baris, masing-masing x dan y (m, bilangan bulat) dipisah spasi.",
    "Dua baris: `Luas: A m2` (satu desimal), lalu `Keliling: K m` (dua desimal).",
    "N 3 sampai 10; koordinat 0 sampai 500; sisi-sisi lahan tidak saling berpotongan."),
   ("Area and Perimeter of a Polygonal Plot",
    "The boundary of a plot is surveyed as corner points with coordinates (x, y) in order around the plot; the last point joins back "
    "to the first. The area is computed with the coordinate formula:\n\n"
    "Area = | sum of (x_k × y_(k+1) − x_(k+1) × y_k) | / 2,\n\n"
    "summed over every point k with the next point (the point after the last is the first). The absolute value is needed because "
    "the points may be given clockwise or anticlockwise. The perimeter is the sum of the lengths of all sides.\n\n"
    "Write a function `luas_poligon(xs, ys)` that takes the list of x coordinates and the list of y coordinates, and a function "
    "`jarak(x1, y1, x2, y2)`.\n\n"
    "Example: the input\n\n```\n4\n0 0\n40 0\n40 30\n0 30\n```\n\ngives\n\n```\nLuas: 1200.0 m2\nKeliling: 140.00 m\n```",
    "First line: the number of points N. Then N lines, each x and y (m, whole numbers) separated by a space.",
    "Two lines: `Luas: A m2` (one decimal), then `Keliling: K m` (two decimals); Indonesian words.",
    "N is 3 to 10; coordinates 0 to 500; the sides of the plot do not cross each other."),
   ("مساحة ومحيط قطعة أرض مضلّعة",
    "تُمسح حدود قطعة أرض نقاطَ زوايا بإحداثيات (x، y) مرتبة حول القطعة؛ والنقطة الأخيرة تتصل بالأولى. تُحسب المساحة بصيغة الإحداثيات:\n\n"
    "المساحة = | مجموع (x_k × y_(k+1) − x_(k+1) × y_k) | / 2،\n\n"
    "بالجمع على كل نقطة k مع النقطة التالية (النقطة بعد الأخيرة هي الأولى). القيمة المطلقة لازمة لأن النقاط قد تُعطى باتجاه عقارب "
    "الساعة أو عكسه. المحيط هو مجموع أطوال الأضلاع كلها.\n\n"
    "اكتب الدالة `luas_poligon(xs, ys)` التي تستقبل قائمة إحداثيات x وقائمة إحداثيات y، والدالة `jarak(x1, y1, x2, y2)`.\n\n"
    "مثال: المدخل\n\n```\n4\n0 0\n40 0\n40 30\n0 30\n```\n\nيعطي\n\n```\nLuas: 1200.0 m2\nKeliling: 140.00 m\n```",
    "السطر الأول: عدد النقاط N. ثم N سطرًا، في كل سطر x و y (م، عددان صحيحان) تفصل بينهما مسافة.",
    "سطران: `Luas: A m2` (منزلة واحدة) ثم `Keliling: K m` (منزلتان)؛ بالإندونيسية.",
    "N من 3 إلى 10؛ الإحداثيات من 0 إلى 500؛ وأضلاع القطعة لا تتقاطع."),
   fungsi=True)

py("rekap-per-mutu", "sulit",
   AMAN + "while True:\n    mutu = r.sample([175, 225, 250, 300, 350, 400], r.randint(1, 4))\n    n = r.randint(1, 20)\n"
   "    data = [(m, m + r.randint(-35, 45)) for m in [r.choice(mutu) for _ in range(n)]]\n    if i == 1:\n        data = [(m, m) for m, _ in data]\n"
   "    ok = True\n    for m in mutu:\n        v = [x for a, x in data if a == m]\n        if v and not aman(sum(v) / len(v), 1):\n            ok = False\n"
   "    if ok:\n        break\nprint(n)\nfor k, (m, x) in enumerate(data, 1):\n    print(f'BU-{k:02d}', f'K{m}', x)\n",
   r'''
banyak = int(input())
nilai_per_mutu = {}
for _ in range(banyak):
    kode, mutu, nilai = input().split()
    target = int(mutu[1:])
    if target not in nilai_per_mutu:
        nilai_per_mutu[target] = []
    nilai_per_mutu[target].append(int(nilai))
for target in sorted(nilai_per_mutu):
    daftar = nilai_per_mutu[target]
    rata_rata = sum(daftar) / len(daftar)
    if rata_rata >= target:
        status = "MEMENUHI"
    else:
        status = "DI BAWAH"
    print(f"K{target}: {len(daftar)} benda uji, rata-rata {rata_rata:.1f}, terendah {min(daftar)}, {status}")
''',
   ("Rekap Hasil Uji per Mutu Beton",
    "Buku laboratorium memuat satu baris untuk setiap benda uji: kode benda uji, mutu beton, dan hasil uji kuat tekan, misalnya "
    "`BU-03 K300 312`. Angka di belakang huruf K adalah kuat tekan yang ditargetkan (kg/cm²).\n\n"
    "Kelompokkan hasil uji menurut mutu dengan dictionary yang nilainya list. Untuk setiap mutu, terurut dari angka mutu terkecil, "
    "cetak banyaknya benda uji, rata-rata hasilnya (satu angka di belakang titik), hasil terendah, dan statusnya: `MEMENUHI` bila "
    "rata-rata tidak kurang dari target, selain itu `DI BAWAH`.\n\n"
    "Contoh: masukan\n\n```\n4\nBU-01 K300 312\nBU-02 K225 230\nBU-03 K300 281\nBU-04 K300 300\n```\n\nmenghasilkan\n\n"
    "```\nK225: 1 benda uji, rata-rata 230.0, terendah 230, MEMENUHI\nK300: 3 benda uji, rata-rata 297.7, terendah 281, DI BAWAH\n```",
    "Baris pertama: banyaknya benda uji N. Lalu N baris, masing-masing kode, mutu (huruf K diikuti angka), dan hasil uji (bilangan "
    "bulat), dipisah satu spasi.",
    "Satu baris untuk setiap mutu yang muncul, berbentuk `Kxxx: N benda uji, rata-rata R, terendah T, STATUS`, terurut menurut "
    "angka mutu.",
    "N 1 sampai 20; mutu K175 sampai K400; hasil uji 140 sampai 445."),
   ("Test Result Summary per Concrete Grade",
    "A laboratory book has one line for every specimen: the specimen code, the concrete grade and the compressive strength result, "
    "for example `BU-03 K300 312`. The number after the letter K is the target strength (kg/cm²).\n\n"
    "Group the results by grade with a dictionary whose values are lists. For each grade, sorted from the smallest grade number, "
    "print the number of specimens, the average result (one digit after the point), the lowest result and the status: `MEMENUHI` "
    "when the average is not below the target, otherwise `DI BAWAH`.\n\n"
    "Example: the input\n\n```\n4\nBU-01 K300 312\nBU-02 K225 230\nBU-03 K300 281\nBU-04 K300 300\n```\n\ngives\n\n"
    "```\nK225: 1 benda uji, rata-rata 230.0, terendah 230, MEMENUHI\nK300: 3 benda uji, rata-rata 297.7, terendah 281, DI BAWAH\n```",
    "First line: the number of specimens N. Then N lines, each a code, a grade (the letter K followed by a number) and a result "
    "(a whole number), separated by single spaces.",
    "One line for every grade that appears, of the form `Kxxx: N benda uji, rata-rata R, terendah T, STATUS`, sorted by grade number "
    "(Indonesian words).",
    "N is 1 to 20; grades K175 to K400; results 140 to 445."),
   ("ملخّص نتائج الاختبار لكل درجة خرسانة",
    "في سجل المختبر سطر لكل عيّنة: رمز العيّنة ودرجة الخرسانة ونتيجة مقاومة الضغط، مثل `BU-03 K300 312`. العدد بعد الحرف K هو "
    "المقاومة المستهدفة (كغ/سم²).\n\n"
    "صنِّف النتائج بحسب الدرجة بقاموس قيمه قوائم. لكل درجة، مرتبةً من أصغر رقم درجة، اطبع عدد العيّنات ومتوسط النتائج (بمنزلة عشرية "
    "واحدة) وأدنى نتيجة والحالة: `MEMENUHI` إذا لم يقلّ المتوسط عن المستهدف، وإلا `DI BAWAH`.\n\n"
    "مثال: المدخل\n\n```\n4\nBU-01 K300 312\nBU-02 K225 230\nBU-03 K300 281\nBU-04 K300 300\n```\n\nيعطي\n\n"
    "```\nK225: 1 benda uji, rata-rata 230.0, terendah 230, MEMENUHI\nK300: 3 benda uji, rata-rata 297.7, terendah 281, DI BAWAH\n```",
    "السطر الأول: عدد العيّنات N. ثم N سطرًا، في كل سطر رمز ودرجة (الحرف K متبوعًا بعدد) ونتيجة (عدد صحيح) تفصل بينها مسافة واحدة.",
    "سطر لكل درجة تظهر بالشكل `Kxxx: N benda uji, rata-rata R, terendah T, STATUS` مرتبًا بحسب رقم الدرجة (بالإندونيسية).",
    "N من 1 إلى 20؛ الدرجات من K175 إلى K400؛ والنتائج من 140 إلى 445."))

py("jadwal-maju", "sulit",
   "n = r.randint(1, 12)\nprint(n)\nfor k in range(1, n + 1):\n    p = [] if k == 1 or (i != 2 and r.random() < 0.25) else r.sample(range(1, k), r.randint(1, min(3, k - 1)))\n"
   "    if i == 2 and k > 1:\n        p = [k - 1]\n    print(r.randint(1, 20), *sorted(p))\n",
   r'''
banyak = int(input())
mulai = []
selesai = []
for nomor in range(1, banyak + 1):
    bagian = list(map(int, input().split()))
    durasi = bagian[0]
    paling_awal = 0
    for pendahulu in bagian[1:]:
        if selesai[pendahulu - 1] > paling_awal:
            paling_awal = selesai[pendahulu - 1]
    mulai.append(paling_awal)
    selesai.append(paling_awal + durasi)
    print(f"Kegiatan {nomor}: mulai {paling_awal}, selesai {paling_awal + durasi}")
print(f"Durasi proyek: {max(selesai)} hari")
''',
   ("Jadwal Proyek: Hitungan Maju",
    "Sebuah proyek terdiri atas kegiatan bernomor 1 sampai N. Setiap kegiatan mempunyai durasi (hari) dan boleh mempunyai beberapa "
    "kegiatan pendahulu, yang nomornya selalu lebih kecil. Sebuah kegiatan baru boleh mulai setelah semua pendahulunya selesai:\n\n"
    "- waktu mulai paling awal = waktu selesai terbesar di antara pendahulunya, atau 0 bila tidak mempunyai pendahulu;\n"
    "- waktu selesai = waktu mulai + durasi.\n\n"
    "Cetak waktu mulai dan selesai setiap kegiatan, lalu durasi proyek, yaitu waktu selesai yang terbesar. Simpan waktu selesai "
    "dalam list supaya dapat dilihat lagi oleh kegiatan berikutnya.\n\n"
    "Contoh: masukan\n\n```\n4\n5\n3 1\n6 1\n2 2 3\n```\n\n(kegiatan 4 berdurasi 2 dan didahului kegiatan 2 dan 3) menghasilkan\n\n"
    "```\nKegiatan 1: mulai 0, selesai 5\nKegiatan 2: mulai 5, selesai 8\nKegiatan 3: mulai 5, selesai 11\n"
    "Kegiatan 4: mulai 11, selesai 13\nDurasi proyek: 13 hari\n```",
    "Baris pertama: banyaknya kegiatan N. Lalu N baris untuk kegiatan 1 sampai N: durasi, diikuti nomor-nomor pendahulunya (boleh "
    "tidak ada), semuanya bilangan bulat dipisah spasi.",
    "N baris `Kegiatan K: mulai M, selesai S`, lalu satu baris `Durasi proyek: D hari`.",
    "N 1 sampai 12; durasi 1 sampai 20; paling banyak 3 pendahulu per kegiatan."),
   ("Project Schedule: Forward Pass",
    "A project consists of activities numbered 1 to N. Each activity has a duration (days) and may have several predecessor "
    "activities, whose numbers are always smaller. An activity may start only after all its predecessors have finished:\n\n"
    "- earliest start = the largest finish time among its predecessors, or 0 if it has none;\n"
    "- finish = start + duration.\n\n"
    "Print the start and the finish of every activity, then the project duration, which is the largest finish time. Keep the finish "
    "times in a list so that later activities can look them up.\n\n"
    "Example: the input\n\n```\n4\n5\n3 1\n6 1\n2 2 3\n```\n\n(activity 4 lasts 2 and follows activities 2 and 3) gives\n\n"
    "```\nKegiatan 1: mulai 0, selesai 5\nKegiatan 2: mulai 5, selesai 8\nKegiatan 3: mulai 5, selesai 11\n"
    "Kegiatan 4: mulai 11, selesai 13\nDurasi proyek: 13 hari\n```",
    "First line: the number of activities N. Then N lines for activities 1 to N: the duration, followed by the numbers of its "
    "predecessors (possibly none), all whole numbers separated by spaces.",
    "N lines `Kegiatan K: mulai M, selesai S`, then one line `Durasi proyek: D hari` (Indonesian words: mulai start, selesai finish).",
    "N is 1 to 12; durations 1 to 20; at most 3 predecessors per activity."),
   ("جدول المشروع: الحساب الأمامي",
    "يتكوّن مشروع من أنشطة مرقّمة من 1 إلى N. لكل نشاط مدة (أيام) وقد يكون له عدة أنشطة سابقة أرقامها أصغر دائمًا. لا يبدأ النشاط "
    "إلا بعد انتهاء كل سوابقه:\n\n"
    "- أبكر بداية = أكبر زمن انتهاء بين سوابقه، أو 0 إذا لم تكن له سوابق؛\n- الانتهاء = البداية + المدة.\n\n"
    "اطبع بداية كل نشاط وانتهاءه ثم مدة المشروع، وهي أكبر زمن انتهاء. احفظ أزمنة الانتهاء في قائمة لتراجعها الأنشطة اللاحقة.\n\n"
    "مثال: المدخل\n\n```\n4\n5\n3 1\n6 1\n2 2 3\n```\n\n(النشاط 4 مدته 2 ويسبقه النشاطان 2 و3) يعطي\n\n"
    "```\nKegiatan 1: mulai 0, selesai 5\nKegiatan 2: mulai 5, selesai 8\nKegiatan 3: mulai 5, selesai 11\n"
    "Kegiatan 4: mulai 11, selesai 13\nDurasi proyek: 13 hari\n```",
    "السطر الأول: عدد الأنشطة N. ثم N سطرًا للأنشطة من 1 إلى N: المدة متبوعة بأرقام سوابقه (وقد لا توجد)، كلها أعداد صحيحة تفصل "
    "بينها مسافات.",
    "N سطرًا `Kegiatan K: mulai M, selesai S` ثم سطر `Durasi proyek: D hari` (بالإندونيسية: mulai بداية، selesai انتهاء).",
    "N من 1 إلى 12؛ المدد من 1 إلى 20؛ و3 سوابق على الأكثر لكل نشاط."))

py("log-ritase", "sulit",
   "plat = r.sample(['BK8123AB', 'BK9001CD', 'BK7745EF', 'BK8830GH', 'BK9412JK', 'BK7007LM'], r.randint(1, 6))\nn = r.randint(1, 30)\n"
   "t = r.randint(6, 9) * 60\nprint(n)\nfor _ in range(n):\n    t += r.randint(0, 25)\n"
   "    print(f'{t // 60:02d}:{t % 60:02d}', r.choice(plat), 7 if i == 1 else r.choice([5, 7, 8, 12]))\n",
   r'''
banyak = int(input())
rit = {}
volume = {}
per_jam = {}
for _ in range(banyak):
    waktu, plat, muatan = input().split()
    rit[plat] = rit.get(plat, 0) + 1
    volume[plat] = volume.get(plat, 0) + int(muatan)
    jam = waktu.split(":")[0]
    per_jam[jam] = per_jam.get(jam, 0) + 1
urutan = []
for plat in rit:
    urutan.append((-volume[plat], plat))
urutan.sort()
for negatif, plat in urutan:
    print(f"{plat}: {rit[plat]} rit, {volume[plat]} m3")
jam_tersibuk = ""
terbanyak = 0
for jam in sorted(per_jam):
    if per_jam[jam] > terbanyak:
        terbanyak = per_jam[jam]
        jam_tersibuk = jam
print(f"Jam tersibuk: {jam_tersibuk} ({terbanyak} rit)")
''',
   ("Rekap Log Ritase Truk",
    "Pos jaga proyek mencatat setiap truk tanah yang keluar: waktu (`JJ:MM`), nomor polisi tanpa spasi, dan volume muatannya, "
    "misalnya `07:42 BK8123AB 7`. Dari log satu hari, buat rekap:\n\n"
    "1. untuk setiap truk: banyaknya ritase dan jumlah volumenya, terurut dari jumlah volume terbesar; bila jumlah volume sama, "
    "terurut menurut nomor polisi secara abjad;\n"
    "2. jam tersibuk: jam (dua angka di depan tanda titik dua) dengan ritase terbanyak dan banyaknya ritase pada jam itu; bila ada "
    "beberapa, ambil jam yang paling awal.\n\n"
    "Contoh: masukan\n\n```\n4\n07:42 BK8123AB 7\n07:55 BK9001CD 12\n08:10 BK8123AB 7\n08:40 BK7745EF 12\n```\n\nmenghasilkan\n\n"
    "```\nBK8123AB: 2 rit, 14 m3\nBK7745EF: 1 rit, 12 m3\nBK9001CD: 1 rit, 12 m3\nJam tersibuk: 07 (2 rit)\n```",
    "Baris pertama: banyaknya catatan N. Lalu N baris, masing-masing waktu, nomor polisi, dan volume (m³, bilangan bulat) dipisah "
    "satu spasi, terurut menurut waktu.",
    "Satu baris `PLAT: R rit, V m3` untuk setiap truk, lalu satu baris `Jam tersibuk: JJ (K rit)`.",
    "N 1 sampai 30; paling banyak 6 truk; waktu antara 06:00 dan 23:59."),
   ("Summary of a Truck Trip Log",
    "A site gate records every soil truck that leaves: the time (`HH:MM`), the plate number without spaces and the volume of its "
    "load, for example `07:42 BK8123AB 7`. From the log of one day make a summary:\n\n"
    "1. for every truck: its number of trips and its total volume, sorted from the largest total volume; when totals are equal, "
    "sorted alphabetically by plate number;\n"
    "2. the busiest hour: the hour (the two digits before the colon) with the most trips and the number of trips in that hour; if "
    "there are several, take the earliest hour.\n\n"
    "Example: the input\n\n```\n4\n07:42 BK8123AB 7\n07:55 BK9001CD 12\n08:10 BK8123AB 7\n08:40 BK7745EF 12\n```\n\ngives\n\n"
    "```\nBK8123AB: 2 rit, 14 m3\nBK7745EF: 1 rit, 12 m3\nBK9001CD: 1 rit, 12 m3\nJam tersibuk: 07 (2 rit)\n```",
    "First line: the number of records N. Then N lines, each a time, a plate number and a volume (m³, a whole number) separated by "
    "single spaces, in order of time.",
    "One line `PLAT: R rit, V m3` for every truck, then one line `Jam tersibuk: JJ (K rit)` (Indonesian words: rit trip).",
    "N is 1 to 30; at most 6 trucks; times between 06:00 and 23:59."),
   ("ملخّص سجل رحلات الشاحنات",
    "يسجّل مركز الحراسة في المشروع كل شاحنة تربة تخرج: الوقت (`HH:MM`) ورقم اللوحة بلا مسافات وحجم الحمولة، مثل "
    "`07:42 BK8123AB 7`. من سجل يوم واحد أنشئ ملخّصًا:\n\n"
    "1. لكل شاحنة: عدد رحلاتها ومجموع حجمها، مرتبة من أكبر مجموع حجم؛ وإذا تساوت المجاميع فبحسب رقم اللوحة أبجديًّا؛\n"
    "2. أكثر الساعات ازدحامًا: الساعة (الرقمان قبل النقطتين) ذات أكبر عدد من الرحلات وعدد الرحلات فيها؛ وإذا تعددت فخذ أبكرها.\n\n"
    "مثال: المدخل\n\n```\n4\n07:42 BK8123AB 7\n07:55 BK9001CD 12\n08:10 BK8123AB 7\n08:40 BK7745EF 12\n```\n\nيعطي\n\n"
    "```\nBK8123AB: 2 rit, 14 m3\nBK7745EF: 1 rit, 12 m3\nBK9001CD: 1 rit, 12 m3\nJam tersibuk: 07 (2 rit)\n```",
    "السطر الأول: عدد السجلات N. ثم N سطرًا، في كل سطر وقت ورقم لوحة وحجم (م³، عدد صحيح) تفصل بينها مسافة واحدة، مرتبة بحسب الوقت.",
    "سطر `PLAT: R rit, V m3` لكل شاحنة ثم سطر `Jam tersibuk: JJ (K rit)` (بالإندونيسية: rit رحلة).",
    "N من 1 إلى 30؛ 6 شاحنات على الأكثر؛ والأوقات بين 06:00 و23:59."))

tulis(__file__, "TS-PY", 5, "python", {"mudah_sekali": 8, "mudah": 8, "mudah_sedang": 6, "sedang": 6, "sulit": 4})
