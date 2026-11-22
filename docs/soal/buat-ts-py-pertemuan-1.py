#!/usr/bin/env python3
"""Menyusun docs/soal/ts-py-pertemuan-1.json.

TS-PY pertemuan 1 — Pengenalan Pemrograman, Algoritma, dan Python (RPS Rev2
10.1): print(), input(), alur masukan-proses-keluaran, kalkulator volume beton,
membandingkan hasil program dengan hitungan tangan.

Konsep yang diizinkan di produksi: IO_DASAR, ARITMETIKA, VARIABEL_TIPE. Karena
itu solusi referensi hanya memakai input(), int(input()), print() dan operator
+ - * / // % **. Tidak ada if, perulangan, fungsi, list, f-string berformat,
round(), int()/float() di luar pembacaan masukan, penyambungan string, maupun
pustaka apa pun (hanya pustaka standar; di pertemuan ini tidak ada impor sama
sekali).

Pilihan teknis (DECISIONS.md, 2026-11-23):
- Satu nilai per baris masukan: mahasiswa baru mengenal input() dan
  int(input()), belum split().
- FORMAT_OUTPUT belum diizinkan, jadi hasil pecahan dicetak apa adanya dan
  dibandingkan dengan mode `float` (selisih 1e-6). Masukannya bilangan bulat
  dan pembagian dilakukan sekali di akhir supaya angka contoh tampil rapi.
- Pembulatan ke atas tanpa if ditulis (a + b - 1) // b; rumusnya diberikan di
  pernyataan soal yang memerlukannya.
- Angka faktor (emp, PPN, berat besi d²/162, π = 3,14) adalah parameter
  latihan, disebut demikian di pernyataan (RPS Rev2 butir 19).

    python3 docs/soal/buat-ts-py-pertemuan-1.py
"""
from bangun import soal, tulis

CATAT = {
    "id": " Panggil `input()` tanpa teks di dalam kurungnya: teks itu ikut tercetak dan membuat jawaban dianggap salah.",
    "en": " Call `input()` with nothing inside the brackets: a prompt text would be printed too and make the answer wrong.",
    "ar": " استدعِ `input()` دون أي نص بين القوسين: فالنص يُطبع أيضًا ويجعل الإجابة خاطئة.",
}
PECAHAN = {
    "id": " Hasil tidak perlu dibulatkan: selisih sampai 0,000001 dari jawaban juri masih diterima.",
    "en": " Do not round the result: a difference of up to 0.000001 from the judge's answer is accepted.",
    "ar": " لا حاجة إلى تقريب الناتج: يُقبل فرق حتى 0.000001 عن إجابة الحكم.",
}


def t(bahasa, judul, isi, masuk, keluar, batas, pecahan=False):
    return (judul, isi, masuk + CATAT[bahasa], keluar + (PECAHAN[bahasa] if pecahan else ""), batas)


def py(slug, tingkat, mode, gen, ref, id, en, ar):
    soal(slug, tingkat, mode, gen, ref.lstrip("\n"), t("id", *id[:5], **id[5]), t("en", *en[:5], **en[5]), t("ar", *ar[:5], **ar[5]),
         waktu_ms=5000)


B = {}                    # hasil bilangan bulat / teks
P = {"pecahan": True}     # hasil pecahan, mode float

# ============================== MUDAH SEKALI (8) ==============================

py("papan-proyek", "mudah_sekali", "trimmed",
   "J = ['Jembatan', 'Gedung', 'Bendung', 'Jalan Lingkar', 'Saluran Irigasi', 'Dermaga', 'Terowongan', 'Rusunawa', 'Drainase Kota', 'Embung']\n"
   "L = ['Kali Progo', 'Sentul', 'Cibinong', 'Bogor Timur', 'Citeureup', 'Karawang', 'Serang', 'Cianjur', 'Sukabumi', 'Purwakarta']\n"
   "print(r.choice(J), r.choice(L), 'Paket', r.randint(1, 9))\n",
   r'''
nama = input()
print("Proyek:", nama)
''',
   ("Papan Nama Proyek",
    "Setiap proyek konstruksi memasang papan nama di pintu masuk lokasi. Buat program yang membaca nama proyek lalu mencetaknya "
    "kembali dengan awalan `Proyek:`.\n\nContoh: masukan `Gedung Kuliah Terpadu` menghasilkan `Proyek: Gedung Kuliah Terpadu`.",
    "Satu baris: nama proyek (boleh terdiri atas beberapa kata).",
    "Satu baris: kata `Proyek:`, satu spasi, lalu nama proyek persis seperti masukan.",
    "Nama proyek 1 sampai 60 karakter.", B),
   ("Project Signboard",
    "Every construction project puts a signboard at the site entrance. Write a program that reads the project name and prints it "
    "back with the prefix `Proyek:`.\n\nExample: the input `Gedung Kuliah Terpadu` gives `Proyek: Gedung Kuliah Terpadu`.",
    "One line: the project name (it may contain several words).",
    "One line: the word `Proyek:`, one space, then the project name exactly as it was read.",
    "The project name has 1 to 60 characters.", B),
   ("لوحة اسم المشروع",
    "يضع كل مشروع إنشائي لوحة باسمه عند مدخل الموقع. اكتب برنامجًا يقرأ اسم المشروع ثم يطبعه مسبوقًا بالكلمة `Proyek:`.\n\n"
    "مثال: المدخل `Gedung Kuliah Terpadu` يعطي `Proyek: Gedung Kuliah Terpadu`.",
    "سطر واحد: اسم المشروع (قد يتكون من عدة كلمات).",
    "سطر واحد: الكلمة `Proyek:` ثم مسافة واحدة ثم اسم المشروع كما قُرئ تمامًا.",
    "طول اسم المشروع من 1 إلى 60 حرفًا.", B))

py("luas-pelat", "mudah_sekali", "trimmed",
   "print(r.randint(1, 40))\nprint(r.randint(1, 40))\n",
   r'''
panjang_m = int(input())
lebar_m = int(input())
luas_m2 = panjang_m * lebar_m
print(luas_m2)
''',
   ("Luas Pelat Lantai",
    "Sebuah pelat lantai berbentuk persegi panjang. Hitung luasnya dalam meter persegi dari panjang dan lebarnya.\n\n"
    "Contoh: panjang 6 m dan lebar 4 m menghasilkan `24`.",
    "Dua baris: panjang pelat, lalu lebar pelat, keduanya bilangan bulat dalam meter.",
    "Satu baris: luas pelat dalam m² (angkanya saja).",
    "Panjang dan lebar 1 sampai 40.", B),
   ("Floor Slab Area",
    "A floor slab is a rectangle. Compute its area in square metres from its length and width.\n\n"
    "Example: length 6 m and width 4 m give `24`.",
    "Two lines: the slab length, then the slab width, both whole numbers of metres.",
    "One line: the slab area in m² (the number only).",
    "Length and width are 1 to 40.", B),
   ("مساحة بلاطة الأرضية",
    "بلاطة أرضية على شكل مستطيل. احسب مساحتها بالمتر المربع من طولها وعرضها.\n\nمثال: الطول 6 م والعرض 4 م يعطيان `24`.",
    "سطران: طول البلاطة ثم عرضها، وكلاهما عدد صحيح بالمتر.",
    "سطر واحد: مساحة البلاطة بالمتر المربع (العدد فقط).",
    "الطول والعرض من 1 إلى 40.", B))

py("keliling-lahan", "mudah_sekali", "trimmed",
   "print(r.randint(1, 500))\nprint(r.randint(1, 500))\n",
   r'''
panjang_m = int(input())
lebar_m = int(input())
keliling_m = 2 * (panjang_m + lebar_m)
print(keliling_m)
''',
   ("Pagar Keliling Lahan",
    "Lahan proyek berbentuk persegi panjang akan dipagari seng di sekelilingnya. Hitung panjang pagar yang dibutuhkan, "
    "yaitu keliling lahan itu.\n\nContoh: lahan 30 m × 20 m membutuhkan pagar `100` m.",
    "Dua baris: panjang lahan, lalu lebar lahan, keduanya bilangan bulat dalam meter.",
    "Satu baris: keliling lahan dalam meter (angkanya saja).",
    "Panjang dan lebar 1 sampai 500.", B),
   ("Fence Around the Site",
    "A rectangular project site will be fenced all the way round. Compute the length of fence needed, that is, the perimeter of the site.\n\n"
    "Example: a 30 m × 20 m site needs `100` m of fence.",
    "Two lines: the site length, then the site width, both whole numbers of metres.",
    "One line: the perimeter in metres (the number only).",
    "Length and width are 1 to 500.", B),
   ("سياج حول الموقع",
    "سيُحاط موقع مشروع مستطيل الشكل بسياج من جميع الجهات. احسب طول السياج المطلوب، أي محيط الموقع.\n\n"
    "مثال: موقع أبعاده 30 م × 20 م يحتاج إلى `100` م من السياج.",
    "سطران: طول الموقع ثم عرضه، وكلاهما عدد صحيح بالمتر.",
    "سطر واحد: المحيط بالمتر (العدد فقط).",
    "الطول والعرض من 1 إلى 500.", B))

py("hitung-kendaraan", "mudah_sekali", "trimmed",
   "print(r.randint(0, 2000))\nprint(r.randint(0, 1500))\nprint(r.randint(0, 300))\n",
   r'''
motor = int(input())
mobil = int(input())
truk = int(input())
print("Total kendaraan:", motor + mobil + truk)
''',
   ("Pencacahan Lalu Lintas",
    "Petugas survei mencatat jumlah sepeda motor, mobil, dan truk yang melewati sebuah ruas jalan selama satu jam. "
    "Cetak jumlah seluruh kendaraan.\n\nContoh: 120 motor, 45 mobil, dan 8 truk menghasilkan `Total kendaraan: 173`.",
    "Tiga baris: jumlah motor, jumlah mobil, jumlah truk (bilangan bulat).",
    "Satu baris berbentuk `Total kendaraan: N`.",
    "Tiap jumlah 0 sampai 2000.", B),
   ("Traffic Count",
    "A surveyor records the number of motorcycles, cars and trucks passing a road section in one hour. Print the total number of vehicles.\n\n"
    "Example: 120 motorcycles, 45 cars and 8 trucks give `Total kendaraan: 173`.",
    "Three lines: the number of motorcycles, of cars, of trucks (whole numbers).",
    "One line of the form `Total kendaraan: N` (the words stay in Indonesian).",
    "Each count is 0 to 2000.", B),
   ("عدّ المركبات",
    "يسجّل مسّاح عدد الدراجات النارية والسيارات والشاحنات التي تمر في مقطع طريق خلال ساعة واحدة. اطبع العدد الكلي للمركبات.\n\n"
    "مثال: 120 دراجة و45 سيارة و8 شاحنات تعطي `Total kendaraan: 173`.",
    "ثلاثة أسطر: عدد الدراجات النارية، عدد السيارات، عدد الشاحنات (أعداد صحيحة).",
    "سطر واحد بالشكل `Total kendaraan: N` (تبقى الكلمات بالإندونيسية).",
    "كل عدد من 0 إلى 2000.", B))

py("sisa-stok-semen", "mudah_sekali", "trimmed",
   "s = r.randint(50, 500)\nprint(s)\nprint({2: 0, 3: s}.get(i, r.randint(0, s)))\n",
   r'''
stok = int(input())
terpakai = int(input())
print("Sisa", stok - terpakai, "sak")
''',
   ("Sisa Stok Semen",
    "Gudang proyek menyimpan sejumlah sak semen. Hari ini sebagian dipakai untuk pengecoran. Cetak sisa stoknya.\n\n"
    "Contoh: stok 120 sak dan terpakai 45 sak menghasilkan `Sisa 75 sak`.",
    "Dua baris: stok awal, lalu jumlah yang terpakai (bilangan bulat, dalam sak).",
    "Satu baris berbentuk `Sisa N sak`.",
    "Stok 50 sampai 500; yang terpakai 0 sampai sebanyak stok.", B),
   ("Cement Left in Stock",
    "The site store holds a number of cement bags. Some are used for today's pour. Print how many are left.\n\n"
    "Example: 120 bags in stock and 45 used give `Sisa 75 sak`.",
    "Two lines: the opening stock, then the number used (whole numbers of bags).",
    "One line of the form `Sisa N sak` (the words stay in Indonesian).",
    "Stock is 50 to 500; the number used is 0 up to the stock.", B),
   ("المتبقي من مخزون الإسمنت",
    "يحتفظ مخزن الموقع بعدد من أكياس الإسمنت، واستُعمل بعضها في صبّ اليوم. اطبع عدد الأكياس المتبقية.\n\n"
    "مثال: مخزون 120 كيسًا واستعمال 45 كيسًا يعطيان `Sisa 75 sak`.",
    "سطران: المخزون الأول ثم العدد المستعمل (أعداد صحيحة من الأكياس).",
    "سطر واحد بالشكل `Sisa N sak` (تبقى الكلمات بالإندونيسية).",
    "المخزون من 50 إلى 500؛ والمستعمل من 0 إلى مقدار المخزون.", B))

py("kubus-uji", "mudah_sekali", "trimmed",
   "print(r.randint(5, 30))\n",
   r'''
sisi_cm = int(input())
print(sisi_cm ** 3)
''',
   ("Volume Kubus Uji Beton",
    "Kuat tekan beton diuji dengan benda uji berbentuk kubus. Hitung volume kubus dari panjang sisinya. "
    "Gunakan operator pangkat `**`.\n\nContoh: sisi 15 cm menghasilkan `3375`.",
    "Satu baris: panjang sisi kubus, bilangan bulat dalam cm.",
    "Satu baris: volume kubus dalam cm³ (angkanya saja).",
    "Sisi 5 sampai 30.", B),
   ("Volume of a Concrete Test Cube",
    "Concrete strength is tested on cube specimens. Compute the volume of a cube from the length of its side. "
    "Use the power operator `**`.\n\nExample: a side of 15 cm gives `3375`.",
    "One line: the side of the cube, a whole number of cm.",
    "One line: the volume in cm³ (the number only).",
    "The side is 5 to 30.", B),
   ("حجم مكعب اختبار الخرسانة",
    "تُختبر مقاومة الخرسانة على عينات مكعبة. احسب حجم المكعب من طول ضلعه. استعمل عامل الأس `**`.\n\nمثال: ضلع طوله 15 سم يعطي `3375`.",
    "سطر واحد: طول ضلع المكعب، عدد صحيح بالسنتيمتر.",
    "سطر واحد: الحجم بالسنتيمتر المكعب (العدد فقط).",
    "الضلع من 5 إلى 30.", B))

py("data-benda-uji", "mudah_sekali", "trimmed",
   "print(r.choice(['BU', 'KB', 'SL', 'BK', 'PL']) + '-' + str(r.randint(1, 99)))\nprint(r.choice([3, 7, 14, 21, 28, 56, 90]))\n",
   r'''
kode = input()
umur_hari = int(input())
print("Kode:", kode)
print("Umur:", umur_hari, "hari")
''',
   ("Label Benda Uji",
    "Setiap benda uji beton diberi label berisi kode dan umurnya saat diuji. Baca kode dan umur, lalu cetak labelnya dalam dua baris.\n\n"
    "Contoh: kode `BU-7` dan umur 28 menghasilkan\n\n```\nKode: BU-7\nUmur: 28 hari\n```",
    "Dua baris: kode benda uji (teks tanpa spasi), lalu umurnya dalam hari (bilangan bulat).",
    "Dua baris: `Kode: K` lalu `Umur: U hari`.",
    "Kode paling panjang 10 karakter; umur 1 sampai 90.", B),
   ("Specimen Label",
    "Every concrete specimen carries a label with its code and its age when tested. Read the code and the age, then print the label on two lines.\n\n"
    "Example: code `BU-7` and age 28 give\n\n```\nKode: BU-7\nUmur: 28 hari\n```",
    "Two lines: the specimen code (text without spaces), then its age in days (a whole number).",
    "Two lines: `Kode: K` then `Umur: U hari` (the words stay in Indonesian).",
    "The code has at most 10 characters; the age is 1 to 90.", B),
   ("ملصق عينة الاختبار",
    "تحمل كل عينة خرسانية ملصقًا فيه رمزها وعمرها عند الاختبار. اقرأ الرمز والعمر ثم اطبع الملصق في سطرين.\n\n"
    "مثال: الرمز `BU-7` والعمر 28 يعطيان\n\n```\nKode: BU-7\nUmur: 28 hari\n```",
    "سطران: رمز العينة (نص بلا مسافات) ثم عمرها بالأيام (عدد صحيح).",
    "سطران: `Kode: K` ثم `Umur: U hari` (تبقى الكلمات بالإندونيسية).",
    "طول الرمز 10 أحرف على الأكثر؛ والعمر من 1 إلى 90.", B))

py("rata-elevasi", "mudah_sekali", "float",
   "print(r.randint(0, 5000))\nprint(r.randint(0, 5000))\n",
   r'''
elevasi_a_cm = int(input())
elevasi_b_cm = int(input())
print((elevasi_a_cm + elevasi_b_cm) / 2)
''',
   ("Elevasi Rata-Rata Dua Titik",
    "Juru ukur mencatat elevasi dua titik di lapangan. Hitung elevasi rata-ratanya. Ingat urutan pengerjaan: "
    "penjumlahan harus diberi kurung supaya dikerjakan sebelum pembagian.\n\nContoh: elevasi 120 cm dan 135 cm menghasilkan `127.5`.",
    "Dua baris: elevasi titik A, lalu elevasi titik B, bilangan bulat dalam cm.",
    "Satu baris: elevasi rata-rata dalam cm.",
    "Elevasi 0 sampai 5000.", P),
   ("Mean Elevation of Two Points",
    "A surveyor records the elevation of two points. Compute their mean elevation. Mind the order of operations: "
    "the sum needs brackets so that it is done before the division.\n\nExample: elevations 120 cm and 135 cm give `127.5`.",
    "Two lines: the elevation of point A, then of point B, whole numbers of cm.",
    "One line: the mean elevation in cm.",
    "Elevations are 0 to 5000.", P),
   ("متوسط منسوب نقطتين",
    "يسجّل المسّاح منسوب نقطتين في الموقع. احسب متوسط المنسوبين. انتبه إلى ترتيب العمليات: يجب وضع الجمع بين قوسين ليُنفَّذ قبل القسمة.\n\n"
    "مثال: المنسوبان 120 سم و135 سم يعطيان `127.5`.",
    "سطران: منسوب النقطة A ثم منسوب النقطة B، عددان صحيحان بالسنتيمتر.",
    "سطر واحد: متوسط المنسوب بالسنتيمتر.",
    "المنسوب من 0 إلى 5000.", P))

# ================================== MUDAH (8) ==================================

py("volume-balok-beton", "mudah", "float",
   "print(r.randint(100, 800))\nprint(r.randint(15, 60))\nprint(r.randint(20, 90))\n",
   r'''
panjang_cm = int(input())
lebar_cm = int(input())
tinggi_cm = int(input())
volume_m3 = panjang_cm * lebar_cm * tinggi_cm / 1000000
print(volume_m3)
''',
   ("Kalkulator Volume Beton Balok",
    "Ukuran sebuah balok beton diberikan dalam sentimeter, sedangkan beton dipesan dalam meter kubik. Hitung volume balok dalam m³. "
    "Ingat bahwa 1 m³ = 1 000 000 cm³.\n\nContoh: balok 400 cm × 30 cm × 50 cm bervolume 600 000 cm³, yaitu `0.6` m³.",
    "Tiga baris: panjang, lebar, dan tinggi balok, bilangan bulat dalam cm.",
    "Satu baris: volume balok dalam m³.",
    "Panjang 100–800, lebar 15–60, tinggi 20–90.", P),
   ("Concrete Beam Volume Calculator",
    "The size of a concrete beam is given in centimetres, but concrete is ordered in cubic metres. Compute the beam volume in m³. "
    "Remember that 1 m³ = 1 000 000 cm³.\n\nExample: a 400 cm × 30 cm × 50 cm beam is 600 000 cm³, that is `0.6` m³.",
    "Three lines: the length, width and height of the beam, whole numbers of cm.",
    "One line: the beam volume in m³.",
    "Length 100–800, width 15–60, height 20–90.", P),
   ("حاسبة حجم خرسانة الكمرة",
    "تُعطى أبعاد كمرة خرسانية بالسنتيمتر، لكن الخرسانة تُطلب بالمتر المكعب. احسب حجم الكمرة بالمتر المكعب. "
    "تذكّر أن 1 م³ = 1 000 000 سم³.\n\nمثال: كمرة أبعادها 400 سم × 30 سم × 50 سم حجمها 600 000 سم³، أي `0.6` م³.",
    "ثلاثة أسطر: طول الكمرة وعرضها وارتفاعها، أعداد صحيحة بالسنتيمتر.",
    "سطر واحد: حجم الكمرة بالمتر المكعب.",
    "الطول 100–800، العرض 15–60، الارتفاع 20–90.", P))

py("truk-penuh", "mudah", "trimmed",
   "k = r.randint(3, 25)\n"
   "v = k * r.randint(1, 60) if i == 2 else (r.randint(1, k - 1) if i == 3 else r.randint(1, 5000))\n"
   "print(v)\nprint(k)\n",
   r'''
volume_m3 = int(input())
kapasitas_m3 = int(input())
print("Truk penuh:", volume_m3 // kapasitas_m3)
print("Sisa:", volume_m3 % kapasitas_m3)
''',
   ("Truk Penuh dan Sisa Tanah",
    "Tanah galian diangkut dengan truk berkapasitas tetap. Truk hanya berangkat bila terisi penuh. Hitung berapa truk penuh yang "
    "berangkat dan berapa m³ tanah yang tersisa di lokasi. Gunakan operator `//` dan `%`.\n\n"
    "Contoh: 100 m³ tanah dengan truk 7 m³ menghasilkan\n\n```\nTruk penuh: 14\nSisa: 2\n```",
    "Dua baris: volume tanah, lalu kapasitas satu truk, bilangan bulat dalam m³.",
    "Dua baris: `Truk penuh: T` lalu `Sisa: S`.",
    "Volume 1 sampai 5000; kapasitas 3 sampai 25.", B),
   ("Full Trucks and Soil Left Over",
    "Excavated soil is hauled by trucks of a fixed capacity. A truck leaves only when it is full. Compute how many full trucks leave and "
    "how many m³ of soil stay on site. Use the operators `//` and `%`.\n\n"
    "Example: 100 m³ of soil with 7 m³ trucks give\n\n```\nTruk penuh: 14\nSisa: 2\n```",
    "Two lines: the soil volume, then the capacity of one truck, whole numbers of m³.",
    "Two lines: `Truk penuh: T` then `Sisa: S` (the words stay in Indonesian).",
    "Volume 1 to 5000; capacity 3 to 25.", B),
   ("الشاحنات الممتلئة والتربة المتبقية",
    "تُنقل تربة الحفر بشاحنات ذات سعة ثابتة، ولا تنطلق الشاحنة إلا ممتلئة. احسب عدد الشاحنات الممتلئة التي تنطلق وكم مترًا مكعبًا "
    "يبقى في الموقع. استعمل العاملين `//` و`%`.\n\nمثال: 100 م³ من التربة مع شاحنات سعتها 7 م³ تعطي\n\n```\nTruk penuh: 14\nSisa: 2\n```",
    "سطران: حجم التربة ثم سعة الشاحنة الواحدة، عددان صحيحان بالمتر المكعب.",
    "سطران: `Truk penuh: T` ثم `Sisa: S` (تبقى الكلمات بالإندونيسية).",
    "الحجم من 1 إلى 5000؛ والسعة من 3 إلى 25.", B))

py("menit-ke-jam", "mudah", "trimmed",
   "print({2: 0, 3: 59, 4: 60}.get(i, r.randint(1, 2000)))\n",
   r'''
durasi_menit = int(input())
jam = durasi_menit // 60
menit = durasi_menit % 60
print(jam, "jam", menit, "menit")
''',
   ("Durasi Pengecoran",
    "Mandor mencatat lama pengecoran dalam menit. Untuk laporan harian, durasi itu harus ditulis dalam jam dan menit.\n\n"
    "Contoh: 135 menit ditulis `2 jam 15 menit`; 45 menit ditulis `0 jam 45 menit`.",
    "Satu baris: durasi dalam menit (bilangan bulat).",
    "Satu baris berbentuk `J jam M menit`, dengan M dari 0 sampai 59.",
    "Durasi 0 sampai 2000.", B),
   ("Duration of a Pour",
    "The foreman records the duration of a concrete pour in minutes. The daily report needs it in hours and minutes.\n\n"
    "Example: 135 minutes is written `2 jam 15 menit`; 45 minutes is written `0 jam 45 menit`.",
    "One line: the duration in minutes (a whole number).",
    "One line of the form `J jam M menit`, with M from 0 to 59 (the words stay in Indonesian).",
    "The duration is 0 to 2000.", B),
   ("مدة الصبّ",
    "يسجّل رئيس العمال مدة صبّ الخرسانة بالدقائق، والتقرير اليومي يحتاجها بالساعات والدقائق.\n\n"
    "مثال: 135 دقيقة تُكتب `2 jam 15 menit`؛ و45 دقيقة تُكتب `0 jam 45 menit`.",
    "سطر واحد: المدة بالدقائق (عدد صحيح).",
    "سطر واحد بالشكل `J jam M menit` حيث M من 0 إلى 59 (تبقى الكلمات بالإندونيسية).",
    "المدة من 0 إلى 2000.", B))

py("luas-dinding-bersih", "mudah", "trimmed",
   "print(r.randint(200, 1000))\nprint(r.randint(250, 400))\nprint(r.randint(60, 180))\nprint(r.randint(80, 220))\n",
   r'''
panjang_cm = int(input())
tinggi_cm = int(input())
lebar_bukaan_cm = int(input())
tinggi_bukaan_cm = int(input())
luas_bersih = panjang_cm * tinggi_cm - lebar_bukaan_cm * tinggi_bukaan_cm
print(luas_bersih)
''',
   ("Luas Bersih Dinding",
    "Sebuah dinding akan diplester. Di dinding itu ada satu bukaan pintu yang tidak diplester. "
    "Hitung luas bersih dinding: luas dinding dikurangi luas bukaan.\n\n"
    "Contoh: dinding 400 cm × 300 cm dengan bukaan 90 cm × 210 cm mempunyai luas bersih 120 000 − 18 900 = `101100` cm².",
    "Empat baris: panjang dinding, tinggi dinding, lebar bukaan, tinggi bukaan; bilangan bulat dalam cm.",
    "Satu baris: luas bersih dalam cm² (angkanya saja).",
    "Panjang 200–1000, tinggi 250–400, lebar bukaan 60–180, tinggi bukaan 80–220.", B),
   ("Net Wall Area",
    "A wall is going to be plastered. It has one door opening that is not plastered. Compute the net area of the wall: "
    "the wall area minus the area of the opening.\n\n"
    "Example: a 400 cm × 300 cm wall with a 90 cm × 210 cm opening has a net area of 120 000 − 18 900 = `101100` cm².",
    "Four lines: wall length, wall height, opening width, opening height; whole numbers of cm.",
    "One line: the net area in cm² (the number only).",
    "Length 200–1000, height 250–400, opening width 60–180, opening height 80–220.", B),
   ("المساحة الصافية للجدار",
    "سيُلَيَّس جدار فيه فتحة باب واحدة لا تُليَّس. احسب المساحة الصافية للجدار: مساحة الجدار ناقص مساحة الفتحة.\n\n"
    "مثال: جدار 400 سم × 300 سم فيه فتحة 90 سم × 210 سم مساحته الصافية 120 000 − 18 900 = `101100` سم².",
    "أربعة أسطر: طول الجدار، ارتفاع الجدار، عرض الفتحة، ارتفاع الفتحة؛ أعداد صحيحة بالسنتيمتر.",
    "سطر واحد: المساحة الصافية بالسنتيمتر المربع (العدد فقط).",
    "الطول 200–1000، الارتفاع 250–400، عرض الفتحة 60–180، ارتفاع الفتحة 80–220.", B))

py("kemiringan-persen", "mudah", "float",
   "print(r.randint(1, 500))\nprint(r.randint(100, 20000))\n",
   r'''
beda_tinggi_cm = int(input())
jarak_datar_cm = int(input())
kemiringan_persen = beda_tinggi_cm * 100 / jarak_datar_cm
print(kemiringan_persen)
''',
   ("Kemiringan Saluran dalam Persen",
    "Kemiringan sebuah saluran dinyatakan dalam persen: beda tinggi dibagi jarak datar, dikali 100. "
    "Kedua ukuran harus dalam satuan yang sama.\n\nContoh: beda tinggi 50 cm pada jarak datar 2000 cm berarti kemiringan `2.5` persen.",
    "Dua baris: beda tinggi, lalu jarak datar, bilangan bulat dalam cm.",
    "Satu baris: kemiringan dalam persen.",
    "Beda tinggi 1 sampai 500; jarak datar 100 sampai 20000.", P),
   ("Channel Slope in Per Cent",
    "The slope of a channel is given in per cent: the drop divided by the horizontal distance, times 100. "
    "Both lengths must be in the same unit.\n\nExample: a drop of 50 cm over 2000 cm is a slope of `2.5` per cent.",
    "Two lines: the drop, then the horizontal distance, whole numbers of cm.",
    "One line: the slope in per cent.",
    "Drop 1 to 500; horizontal distance 100 to 20000.", P),
   ("ميل القناة بالنسبة المئوية",
    "يُعبَّر عن ميل القناة بالنسبة المئوية: فرق الارتفاع مقسومًا على المسافة الأفقية، مضروبًا في 100. يجب أن يكون القياسان بالوحدة نفسها.\n\n"
    "مثال: فرق ارتفاع 50 سم على مسافة أفقية 2000 سم يعني ميلًا مقداره `2.5` بالمئة.",
    "سطران: فرق الارتفاع ثم المسافة الأفقية، عددان صحيحان بالسنتيمتر.",
    "سطر واحد: الميل بالنسبة المئوية.",
    "فرق الارتفاع من 1 إلى 500؛ المسافة الأفقية من 100 إلى 20000.", P))

py("tegangan-kolom", "mudah", "float",
   "print(r.randint(50, 3000))\nprint(r.randint(15, 80) * 10)\n",
   r'''
beban_kN = int(input())
sisi_mm = int(input())
luas_mm2 = sisi_mm * sisi_mm
tegangan_MPa = beban_kN * 1000 / luas_mm2
print(tegangan_MPa)
''',
   ("Tegangan Tekan Kolom Persegi",
    "Sebuah kolom berpenampang bujur sangkar memikul beban tekan. Tegangan tekannya adalah gaya dibagi luas penampang. "
    "Dengan gaya dalam newton dan luas dalam mm², hasilnya dalam MPa (1 MPa = 1 N/mm²). Ingat 1 kN = 1000 N.\n\n"
    "Contoh: beban 900 kN pada kolom bersisi 300 mm: 900 000 N ÷ 90 000 mm² = `10.0` MPa.",
    "Dua baris: beban dalam kN, lalu sisi penampang dalam mm (bilangan bulat).",
    "Satu baris: tegangan tekan dalam MPa.",
    "Beban 50 sampai 3000; sisi 150 sampai 800.", P),
   ("Compressive Stress in a Square Column",
    "A column with a square cross-section carries a compressive load. Its compressive stress is the force divided by the cross-sectional area. "
    "With the force in newtons and the area in mm², the result is in MPa (1 MPa = 1 N/mm²). Remember 1 kN = 1000 N.\n\n"
    "Example: 900 kN on a column with a 300 mm side: 900 000 N ÷ 90 000 mm² = `10.0` MPa.",
    "Two lines: the load in kN, then the side of the section in mm (whole numbers).",
    "One line: the compressive stress in MPa.",
    "Load 50 to 3000; side 150 to 800.", P),
   ("إجهاد الضغط في عمود مربع",
    "يحمل عمود مقطعه مربع حمل ضغط. إجهاد الضغط هو القوة مقسومة على مساحة المقطع. إذا كانت القوة بالنيوتن والمساحة بالمليمتر المربع "
    "كان الناتج بالميغاباسكال (1 MPa = 1 N/mm²). تذكّر أن 1 kN = 1000 N.\n\n"
    "مثال: حمل 900 kN على عمود ضلعه 300 مم: 900 000 N ÷ 90 000 mm² = `10.0` MPa.",
    "سطران: الحمل بالكيلونيوتن ثم ضلع المقطع بالمليمتر (عددان صحيحان).",
    "سطر واحد: إجهاد الضغط بالميغاباسكال.",
    "الحمل من 50 إلى 3000؛ الضلع من 150 إلى 800.", P))

py("debit-saluran", "mudah", "float",
   "print(r.randint(20, 300))\nprint(r.randint(5, 150))\nprint(r.randint(10, 300))\n",
   r'''
lebar_cm = int(input())
kedalaman_cm = int(input())
kecepatan_cm_s = int(input())
debit_liter_s = lebar_cm * kedalaman_cm * kecepatan_cm_s / 1000
print(debit_liter_s)
''',
   ("Debit Saluran Persegi",
    "Debit aliran adalah luas penampang basah dikali kecepatan aliran. Sebuah saluran berpenampang persegi panjang diukur lebar, "
    "kedalaman air, dan kecepatan alirannya. Hitung debitnya dalam liter per detik. Ingat 1 liter = 1000 cm³.\n\n"
    "Contoh: lebar 80 cm, kedalaman 50 cm, kecepatan 120 cm/detik: 80 × 50 × 120 = 480 000 cm³/detik = `480.0` liter/detik.",
    "Tiga baris: lebar saluran (cm), kedalaman air (cm), kecepatan aliran (cm/detik); bilangan bulat.",
    "Satu baris: debit dalam liter per detik.",
    "Lebar 20–300, kedalaman 5–150, kecepatan 10–300.", P),
   ("Discharge of a Rectangular Channel",
    "Discharge is the wetted cross-sectional area times the flow velocity. For a rectangular channel the width, the water depth and "
    "the velocity were measured. Compute the discharge in litres per second. Remember 1 litre = 1000 cm³.\n\n"
    "Example: width 80 cm, depth 50 cm, velocity 120 cm/s: 80 × 50 × 120 = 480 000 cm³/s = `480.0` litres per second.",
    "Three lines: channel width (cm), water depth (cm), flow velocity (cm/s); whole numbers.",
    "One line: the discharge in litres per second.",
    "Width 20–300, depth 5–150, velocity 10–300.", P),
   ("تصريف قناة مستطيلة",
    "التصريف هو مساحة المقطع المبلول مضروبة في سرعة الجريان. قيس لقناة مستطيلة المقطع عرضُها وعمق الماء فيها وسرعة الجريان. "
    "احسب التصريف باللتر في الثانية. تذكّر أن 1 لتر = 1000 سم³.\n\n"
    "مثال: العرض 80 سم والعمق 50 سم والسرعة 120 سم/ث: 80 × 50 × 120 = 480 000 سم³/ث = `480.0` لتر/ث.",
    "ثلاثة أسطر: عرض القناة (سم)، عمق الماء (سم)، سرعة الجريان (سم/ث)؛ أعداد صحيحة.",
    "سطر واحد: التصريف باللتر في الثانية.",
    "العرض 20–300، العمق 5–150، السرعة 10–300.", P))

py("bacaan-tertukar", "mudah", "trimmed",
   "a = r.randint(500, 2500)\nb = r.randint(500, 2500)\nwhile b == a:\n    b = r.randint(500, 2500)\nprint(a)\nprint(b)\n",
   r'''
belakang_mm = int(input())
muka_mm = int(input())
belakang_mm, muka_mm = muka_mm, belakang_mm
print(belakang_mm, muka_mm)
print(belakang_mm - muka_mm)
''',
   ("Bacaan Rambu yang Tertukar",
    "Pada pengukuran sipat datar, beda tinggi dua titik adalah bacaan rambu belakang dikurangi bacaan rambu muka. "
    "Seorang asisten menulis kedua bacaan di kolom yang salah: angka yang tercatat sebagai bacaan belakang sebenarnya bacaan muka, "
    "dan sebaliknya. Tukar isi kedua variabel, lalu cetak bacaan yang benar dan beda tingginya.\n\n"
    "Contoh: tercatat belakang 1250 dan muka 1875. Yang benar: belakang 1875 dan muka 1250, sehingga keluarannya\n\n```\n1875 1250\n625\n```",
    "Dua baris: angka yang tercatat sebagai bacaan belakang, lalu angka yang tercatat sebagai bacaan muka; bilangan bulat dalam mm.",
    "Baris pertama: bacaan belakang yang benar dan bacaan muka yang benar, dipisah satu spasi. "
    "Baris kedua: beda tinggi (belakang − muka, boleh negatif).",
    "Bacaan 500 sampai 2500; keduanya berbeda.", B),
   ("Swapped Staff Readings",
    "In levelling, the height difference between two points is the backsight reading minus the foresight reading. "
    "An assistant wrote the two readings in the wrong columns: the number recorded as the backsight is really the foresight, and the other way round. "
    "Swap the contents of the two variables, then print the correct readings and the height difference.\n\n"
    "Example: recorded backsight 1250 and foresight 1875. Correct: backsight 1875 and foresight 1250, so the output is\n\n```\n1875 1250\n625\n```",
    "Two lines: the number recorded as the backsight, then the number recorded as the foresight; whole numbers of mm.",
    "First line: the correct backsight and the correct foresight, separated by one space. "
    "Second line: the height difference (backsight − foresight, may be negative).",
    "Readings are 500 to 2500; the two differ.", B),
   ("قراءتا القامة المتبادلتان",
    "في التسوية، فرق الارتفاع بين نقطتين هو القراءة الخلفية ناقص القراءة الأمامية. كتب مساعدٌ القراءتين في العمودين الخطأ: "
    "العدد المسجَّل قراءةً خلفية هو في الحقيقة القراءة الأمامية، والعكس. بدّل محتوى المتغيرين ثم اطبع القراءتين الصحيحتين وفرق الارتفاع.\n\n"
    "مثال: سُجّلت الخلفية 1250 والأمامية 1875. الصحيح: الخلفية 1875 والأمامية 1250، فيكون المخرج\n\n```\n1875 1250\n625\n```",
    "سطران: العدد المسجَّل قراءةً خلفية ثم العدد المسجَّل قراءةً أمامية؛ عددان صحيحان بالمليمتر.",
    "السطر الأول: القراءة الخلفية الصحيحة والقراءة الأمامية الصحيحة تفصل بينهما مسافة واحدة. "
    "السطر الثاني: فرق الارتفاع (الخلفية − الأمامية، وقد يكون سالبًا).",
    "القراءات من 500 إلى 2500؛ والقراءتان مختلفتان.", B))

# ============================== MUDAH-SEDANG (6) ==============================

ATAS_ID = "Pembulatan ke atas dari a ÷ b untuk bilangan bulat positif dapat ditulis tanpa `if`: `(a + b - 1) // b`."
ATAS_EN = "Rounding a ÷ b up for positive whole numbers can be written without `if`: `(a + b - 1) // b`."
ATAS_AR = "تقريب a ÷ b إلى الأعلى للأعداد الصحيحة الموجبة يمكن كتابته دون `if`: `(a + b - 1) // b`."

py("kebutuhan-bata", "mudah_sedang", "trimmed",
   "print(r.randint(200, 1200))\nprint(r.randint(200, 400))\nprint(r.randint(20, 25))\nprint(r.randint(6, 12))\n",
   r'''
panjang_cm = int(input())
tinggi_cm = int(input())
panjang_bata_cm = int(input())
tinggi_bata_cm = int(input())
luas_dinding = panjang_cm * tinggi_cm
luas_bata = panjang_bata_cm * tinggi_bata_cm
jumlah_bata = (luas_dinding + luas_bata - 1) // luas_bata
print(jumlah_bata)
''',
   ("Kebutuhan Bata Dinding",
    "Jumlah bata untuk sebuah dinding ditaksir dengan membagi luas dinding dengan luas satu bata (ukuran bata sudah termasuk siar). "
    "Bata tidak bisa dibeli sebagian, jadi hasil bagi dibulatkan ke atas.\n\n" + ATAS_ID + "\n\n"
    "Contoh: dinding 300 cm × 300 cm dengan bata 22 cm × 7 cm: 90 000 ÷ 154 = 584,4…, jadi dibutuhkan `585` bata.",
    "Empat baris: panjang dinding, tinggi dinding, panjang bata, tinggi bata; bilangan bulat dalam cm.",
    "Satu baris: jumlah bata (angkanya saja).",
    "Panjang dinding 200–1200, tinggi dinding 200–400, panjang bata 20–25, tinggi bata 6–12.", B),
   ("Bricks for a Wall",
    "The number of bricks for a wall is estimated by dividing the wall area by the area of one brick (the brick size already includes the joint). "
    "Bricks cannot be bought in parts, so the quotient is rounded up.\n\n" + ATAS_EN + "\n\n"
    "Example: a 300 cm × 300 cm wall with 22 cm × 7 cm bricks: 90 000 ÷ 154 = 584.4…, so `585` bricks are needed.",
    "Four lines: wall length, wall height, brick length, brick height; whole numbers of cm.",
    "One line: the number of bricks (the number only).",
    "Wall length 200–1200, wall height 200–400, brick length 20–25, brick height 6–12.", B),
   ("الطوب اللازم للجدار",
    "يُقدَّر عدد الطوب اللازم لجدار بقسمة مساحة الجدار على مساحة طوبة واحدة (مقاس الطوبة يشمل اللحام). "
    "لا يمكن شراء جزء من طوبة، لذلك يُقرَّب خارج القسمة إلى الأعلى.\n\n" + ATAS_AR + "\n\n"
    "مثال: جدار 300 سم × 300 سم وطوب 22 سم × 7 سم: 90 000 ÷ 154 = 584.4…، فيلزم `585` طوبة.",
    "أربعة أسطر: طول الجدار، ارتفاع الجدار، طول الطوبة، ارتفاع الطوبة؛ أعداد صحيحة بالسنتيمتر.",
    "سطر واحد: عدد الطوب (العدد فقط).",
    "طول الجدار 200–1200، ارتفاع الجدار 200–400، طول الطوبة 20–25، ارتفاع الطوبة 6–12.", B))

py("berat-tulangan", "mudah_sedang", "float",
   "print(r.choice([6, 8, 10, 12, 13, 16, 19, 22, 25]))\nprint(r.randint(1, 12))\nprint(r.randint(1, 500))\n",
   r'''
diameter_mm = int(input())
panjang_m = int(input())
jumlah_batang = int(input())
berat_kg = jumlah_batang * panjang_m * diameter_mm ** 2 / 162
print(berat_kg)
''',
   ("Berat Besi Tulangan",
    "Di lapangan, berat besi tulangan per meter ditaksir dengan rumus praktis d² ÷ 162 kg/m, dengan d diameter besi dalam mm "
    "(parameter latihan, bukan standar desain). Hitung berat total sejumlah batang yang sama panjang.\n\n"
    "Contoh: 20 batang berdiameter 10 mm sepanjang 12 m: 20 × 12 × 100 ÷ 162 ≈ `148.148148` kg.",
    "Tiga baris: diameter besi (mm), panjang satu batang (m), jumlah batang; bilangan bulat.",
    "Satu baris: berat total dalam kg.",
    "Diameter 6–25, panjang 1–12, jumlah batang 1–500.", P),
   ("Weight of Reinforcing Bars",
    "On site, the weight of a reinforcing bar per metre is estimated with the rule of thumb d² ÷ 162 kg/m, where d is the bar diameter in mm "
    "(a practice parameter, not a design standard). Compute the total weight of a number of bars of the same length.\n\n"
    "Example: 20 bars of 10 mm diameter, 12 m long: 20 × 12 × 100 ÷ 162 ≈ `148.148148` kg.",
    "Three lines: bar diameter (mm), length of one bar (m), number of bars; whole numbers.",
    "One line: the total weight in kg.",
    "Diameter 6–25, length 1–12, number of bars 1–500.", P),
   ("وزن حديد التسليح",
    "في الموقع يُقدَّر وزن قضيب التسليح لكل متر بالقاعدة العملية d² ÷ 162 كغ/م، حيث d قطر القضيب بالمليمتر "
    "(معامل تدريبي وليس معيار تصميم). احسب الوزن الكلي لعدد من القضبان المتساوية الطول.\n\n"
    "مثال: 20 قضيبًا قطرها 10 مم وطولها 12 م: 20 × 12 × 100 ÷ 162 ≈ `148.148148` كغ.",
    "ثلاثة أسطر: قطر القضيب (مم)، طول القضيب الواحد (م)، عدد القضبان؛ أعداد صحيحة.",
    "سطر واحد: الوزن الكلي بالكيلوغرام.",
    "القطر 6–25، الطول 1–12، عدد القضبان 1–500.", P))

py("campuran-beton", "mudah_sedang", "float",
   "print(r.randint(1, 3))\nprint(r.randint(1, 4))\nprint(r.randint(2, 6))\nprint(r.randint(6, 600) * 10)\n",
   r'''
bagian_semen = int(input())
bagian_pasir = int(input())
bagian_kerikil = int(input())
volume_liter = int(input())
jumlah_bagian = bagian_semen + bagian_pasir + bagian_kerikil
print(volume_liter * bagian_semen / jumlah_bagian)
print(volume_liter * bagian_pasir / jumlah_bagian)
print(volume_liter * bagian_kerikil / jumlah_bagian)
''',
   ("Takaran Campuran Beton",
    "Campuran beton sederhana ditakar dengan perbandingan volume semen : pasir : kerikil. Dari perbandingan itu dan volume total "
    "bahan kering, hitung volume tiap bahan. Volume satu bahan = volume total × bagiannya ÷ jumlah semua bagian.\n\n"
    "Contoh: perbandingan 1 : 2 : 3 dan volume total 300 liter menghasilkan\n\n```\n50.0\n100.0\n150.0\n```",
    "Empat baris: bagian semen, bagian pasir, bagian kerikil, lalu volume total dalam liter; bilangan bulat.",
    "Tiga baris: volume semen, volume pasir, volume kerikil, dalam liter.",
    "Bagian semen 1–3, pasir 1–4, kerikil 2–6; volume total 60–6000.", P),
   ("Concrete Mix Proportions",
    "A simple concrete mix is batched by a volume ratio cement : sand : gravel. From the ratio and the total volume of dry material, "
    "compute the volume of each material. Volume of one material = total volume × its part ÷ sum of all parts.\n\n"
    "Example: a ratio of 1 : 2 : 3 and a total of 300 litres give\n\n```\n50.0\n100.0\n150.0\n```",
    "Four lines: the cement part, the sand part, the gravel part, then the total volume in litres; whole numbers.",
    "Three lines: the volume of cement, of sand, of gravel, in litres.",
    "Cement part 1–3, sand 1–4, gravel 2–6; total volume 60–6000.", P),
   ("نِسَب خلطة الخرسانة",
    "تُعايَر خلطة الخرسانة البسيطة بنسبة حجمية إسمنت : رمل : حصى. من النسبة والحجم الكلي للمواد الجافة احسب حجم كل مادة. "
    "حجم المادة = الحجم الكلي × جزؤها ÷ مجموع الأجزاء كلها.\n\n"
    "مثال: النسبة 1 : 2 : 3 والحجم الكلي 300 لتر يعطيان\n\n```\n50.0\n100.0\n150.0\n```",
    "أربعة أسطر: جزء الإسمنت، جزء الرمل، جزء الحصى، ثم الحجم الكلي باللتر؛ أعداد صحيحة.",
    "ثلاثة أسطر: حجم الإسمنت ثم حجم الرمل ثم حجم الحصى، باللتر.",
    "جزء الإسمنت 1–3، الرمل 1–4، الحصى 2–6؛ الحجم الكلي 60–6000.", P))

py("volume-hujan-atap", "mudah_sedang", "float",
   "print(r.randint(1, 150))\nprint(r.randint(20, 2000))\n",
   r'''
hujan_mm = int(input())
luas_atap_m2 = int(input())
volume_liter = hujan_mm * luas_atap_m2
print(volume_liter)
print(volume_liter / 1000)
''',
   ("Volume Air Hujan di Atap",
    "Curah hujan 1 mm yang jatuh di atas bidang seluas 1 m² sama dengan 1 liter air. Dari curah hujan harian dan luas atap sebuah gedung, "
    "hitung volume air hujan yang jatuh di atap itu, dalam liter dan dalam meter kubik (1 m³ = 1000 liter).\n\n"
    "Contoh: hujan 25 mm di atap 120 m² menghasilkan\n\n```\n3000\n3.0\n```",
    "Dua baris: curah hujan (mm), lalu luas atap (m²); bilangan bulat.",
    "Baris pertama: volume dalam liter (bilangan bulat). Baris kedua: volume dalam m³.",
    "Curah hujan 1–150; luas atap 20–2000.", P),
   ("Rainwater on a Roof",
    "A rainfall of 1 mm on an area of 1 m² equals 1 litre of water. From the daily rainfall and the roof area of a building, "
    "compute the volume of rain falling on the roof, in litres and in cubic metres (1 m³ = 1000 litres).\n\n"
    "Example: 25 mm of rain on a 120 m² roof give\n\n```\n3000\n3.0\n```",
    "Two lines: the rainfall (mm), then the roof area (m²); whole numbers.",
    "First line: the volume in litres (a whole number). Second line: the volume in m³.",
    "Rainfall 1–150; roof area 20–2000.", P),
   ("مياه الأمطار على السطح",
    "هطول 1 مم على مساحة 1 م² يساوي لترًا واحدًا من الماء. من الهطول اليومي ومساحة سطح مبنى احسب حجم المطر الساقط على السطح "
    "باللتر وبالمتر المكعب (1 م³ = 1000 لتر).\n\nمثال: هطول 25 مم على سطح مساحته 120 م² يعطي\n\n```\n3000\n3.0\n```",
    "سطران: الهطول (مم) ثم مساحة السطح (م²)؛ عددان صحيحان.",
    "السطر الأول: الحجم باللتر (عدد صحيح). السطر الثاني: الحجم بالمتر المكعب.",
    "الهطول 1–150؛ مساحة السطح 20–2000.", P))

py("smp-lalu-lintas", "mudah_sedang", "float",
   "print(r.randint(0, 3000))\nprint(r.randint(0, 1500))\nprint(r.randint(0, 300))\n",
   r'''
motor = int(input())
mobil = int(input())
truk = int(input())
smp = motor * 0.25 + mobil * 1 + truk * 2.5
print(smp)
''',
   ("Satuan Mobil Penumpang",
    "Agar arus lalu lintas campuran bisa dibandingkan, tiap jenis kendaraan dikonversi ke satuan mobil penumpang (smp) dengan faktor "
    "ekuivalen. Pada latihan ini faktornya: sepeda motor 0,25; mobil 1; truk 2,5 (parameter latihan, bukan standar). "
    "Hitung arus total dalam smp.\n\nContoh: 200 motor, 150 mobil, dan 20 truk: 50 + 150 + 50 = `250.0` smp.",
    "Tiga baris: jumlah motor, jumlah mobil, jumlah truk; bilangan bulat.",
    "Satu baris: arus total dalam smp.",
    "Motor 0–3000, mobil 0–1500, truk 0–300.", P),
   ("Passenger Car Units",
    "To compare mixed traffic flows, each vehicle type is converted to passenger car units (smp) with an equivalence factor. "
    "In this exercise the factors are: motorcycle 0.25; car 1; truck 2.5 (practice parameters, not a standard). "
    "Compute the total flow in smp.\n\nExample: 200 motorcycles, 150 cars and 20 trucks: 50 + 150 + 50 = `250.0` smp.",
    "Three lines: the number of motorcycles, of cars, of trucks; whole numbers.",
    "One line: the total flow in smp.",
    "Motorcycles 0–3000, cars 0–1500, trucks 0–300.", P),
   ("وحدات سيارات الركاب",
    "لمقارنة تدفقات المرور المختلطة يُحوَّل كل نوع من المركبات إلى وحدات سيارات الركاب (smp) بمعامل تكافؤ. "
    "المعاملات في هذا التمرين: الدراجة النارية 0.25؛ السيارة 1؛ الشاحنة 2.5 (معاملات تدريبية وليست معيارًا). احسب التدفق الكلي بوحدة smp.\n\n"
    "مثال: 200 دراجة و150 سيارة و20 شاحنة: 50 + 150 + 50 = `250.0` smp.",
    "ثلاثة أسطر: عدد الدراجات النارية، عدد السيارات، عدد الشاحنات؛ أعداد صحيحة.",
    "سطر واحد: التدفق الكلي بوحدة smp.",
    "الدراجات 0–3000، السيارات 0–1500، الشاحنات 0–300.", P))

py("rab-sederhana", "mudah_sedang", "trimmed",
   "for _ in range(3):\n    print(r.randint(1, 200))\n    print(r.randint(5, 150) * 10)\n",
   r'''
volume_1 = int(input())
harga_1 = int(input())
volume_2 = int(input())
harga_2 = int(input())
volume_3 = int(input())
harga_3 = int(input())
jumlah = volume_1 * harga_1 + volume_2 * harga_2 + volume_3 * harga_3
ppn = jumlah * 11 // 100
print("Jumlah:", jumlah)
print("PPN:", ppn)
print("Total:", jumlah + ppn)
''',
   ("Rencana Anggaran Biaya Sederhana",
    "Sebuah rencana anggaran biaya (RAB) memuat tiga pekerjaan. Tiap pekerjaan punya volume dan harga satuan (dalam ribu rupiah). "
    "Hitung jumlah biaya ketiganya, PPN 11 % dari jumlah itu dibulatkan ke bawah dengan `//`, dan total setelah PPN "
    "(tarif di sini parameter latihan).\n\n"
    "Contoh: pekerjaan (10, 850), (25, 120), dan (4, 300): 8500 + 3000 + 1200 = 12 700; PPN = 12 700 × 11 // 100 = 1397. Keluarannya\n\n"
    "```\nJumlah: 12700\nPPN: 1397\nTotal: 14097\n```",
    "Enam baris: volume dan harga satuan pekerjaan pertama, lalu pekerjaan kedua, lalu pekerjaan ketiga; bilangan bulat.",
    "Tiga baris: `Jumlah: J`, `PPN: P`, `Total: T`, semuanya dalam ribu rupiah.",
    "Volume 1–200; harga satuan 50–1500.", B),
   ("A Simple Bill of Quantities",
    "A bill of quantities (RAB) lists three work items. Each item has a quantity and a unit price (in thousand rupiah). "
    "Compute the sum of the three costs, VAT of 11 % of that sum rounded down with `//`, and the total including VAT "
    "(the rate here is a practice parameter).\n\n"
    "Example: items (10, 850), (25, 120) and (4, 300): 8500 + 3000 + 1200 = 12 700; VAT = 12 700 × 11 // 100 = 1397. The output is\n\n"
    "```\nJumlah: 12700\nPPN: 1397\nTotal: 14097\n```",
    "Six lines: quantity and unit price of the first item, then of the second, then of the third; whole numbers.",
    "Three lines: `Jumlah: J`, `PPN: P`, `Total: T`, all in thousand rupiah (the words stay in Indonesian).",
    "Quantity 1–200; unit price 50–1500.", B),
   ("جدول كميات مبسّط",
    "يضم جدول الكميات والتكاليف (RAB) ثلاثة بنود عمل، ولكل بند كمية وسعر وحدة (بآلاف الروبيات). احسب مجموع تكاليف البنود الثلاثة، "
    "وضريبة القيمة المضافة 11 % من المجموع مقرَّبةً إلى الأسفل بالعامل `//`، والإجمالي بعد الضريبة (النسبة هنا معامل تدريبي).\n\n"
    "مثال: البنود (10, 850) و(25, 120) و(4, 300): 8500 + 3000 + 1200 = 12 700؛ الضريبة = 12 700 × 11 // 100 = 1397. فيكون المخرج\n\n"
    "```\nJumlah: 12700\nPPN: 1397\nTotal: 14097\n```",
    "ستة أسطر: كمية البند الأول وسعر وحدته، ثم الثاني، ثم الثالث؛ أعداد صحيحة.",
    "ثلاثة أسطر: `Jumlah: J` ثم `PPN: P` ثم `Total: T`، كلها بآلاف الروبيات (تبقى الكلمات بالإندونيسية).",
    "الكمية 1–200؛ سعر الوحدة 50–1500.", B))

# ================================= SEDANG (6) =================================

py("volume-saluran", "sedang", "float",
   "a = r.randint(60, 400)\nprint(a)\nprint(r.randint(30, a))\nprint(r.randint(30, 200))\nprint(r.randint(10, 2000))\n",
   r'''
lebar_atas_cm = int(input())
lebar_bawah_cm = int(input())
kedalaman_cm = int(input())
panjang_m = int(input())
luas_cm2 = (lebar_atas_cm + lebar_bawah_cm) * kedalaman_cm / 2
volume_m3 = luas_cm2 / 10000 * panjang_m
print(volume_m3)
''',
   ("Volume Galian Saluran Trapesium",
    "Saluran drainase digali dengan penampang trapesium. Luas trapesium = (lebar atas + lebar bawah) × kedalaman ÷ 2. "
    "Volume galian = luas penampang × panjang saluran. Ukuran penampang diberikan dalam cm dan panjang dalam m; "
    "hasil diminta dalam m³ (1 m² = 10 000 cm²).\n\n"
    "Contoh: lebar atas 120 cm, lebar bawah 60 cm, kedalaman 80 cm, panjang 50 m: luas 7200 cm² = 0,72 m², volume `36.0` m³.",
    "Empat baris: lebar atas (cm), lebar bawah (cm), kedalaman (cm), panjang saluran (m); bilangan bulat.",
    "Satu baris: volume galian dalam m³.",
    "Lebar atas 60–400, lebar bawah 30 sampai lebar atas, kedalaman 30–200, panjang 10–2000.", P),
   ("Excavation Volume of a Trapezoidal Channel",
    "A drainage channel is dug with a trapezoidal cross-section. Area of a trapezoid = (top width + bottom width) × depth ÷ 2. "
    "Excavation volume = cross-sectional area × channel length. The section is given in cm and the length in m; "
    "the result is wanted in m³ (1 m² = 10 000 cm²).\n\n"
    "Example: top width 120 cm, bottom width 60 cm, depth 80 cm, length 50 m: area 7200 cm² = 0.72 m², volume `36.0` m³.",
    "Four lines: top width (cm), bottom width (cm), depth (cm), channel length (m); whole numbers.",
    "One line: the excavation volume in m³.",
    "Top width 60–400, bottom width 30 up to the top width, depth 30–200, length 10–2000.", P),
   ("حجم حفر قناة شبه منحرفة",
    "تُحفر قناة تصريف بمقطع شبه منحرف. مساحة شبه المنحرف = (العرض العلوي + العرض السفلي) × العمق ÷ 2. "
    "حجم الحفر = مساحة المقطع × طول القناة. أبعاد المقطع بالسنتيمتر والطول بالمتر، والمطلوب الناتج بالمتر المكعب (1 م² = 10 000 سم²).\n\n"
    "مثال: العرض العلوي 120 سم، العرض السفلي 60 سم، العمق 80 سم، الطول 50 م: المساحة 7200 سم² = 0.72 م²، والحجم `36.0` م³.",
    "أربعة أسطر: العرض العلوي (سم)، العرض السفلي (سم)، العمق (سم)، طول القناة (م)؛ أعداد صحيحة.",
    "سطر واحد: حجم الحفر بالمتر المكعب.",
    "العرض العلوي 60–400، العرض السفلي من 30 إلى العرض العلوي، العمق 30–200، الطول 10–2000.", P))

py("keramik-lantai", "sedang", "trimmed",
   "print(r.randint(200, 1500))\nprint(r.randint(200, 1500))\nprint(r.choice([20, 25, 30, 40, 50, 60]))\nprint(r.choice([4, 5, 6, 10, 11, 25]))\n",
   r'''
panjang_cm = int(input())
lebar_cm = int(input())
sisi_cm = int(input())
isi_dus = int(input())
searah_panjang = (panjang_cm + sisi_cm - 1) // sisi_cm
searah_lebar = (lebar_cm + sisi_cm - 1) // sisi_cm
jumlah_keramik = searah_panjang * searah_lebar
jumlah_dus = (jumlah_keramik + isi_dus - 1) // isi_dus
print("Keramik:", jumlah_keramik)
print("Dus:", jumlah_dus)
''',
   ("Keramik Lantai dan Jumlah Dus",
    "Lantai persegi panjang dipasangi keramik bujur sangkar. Keramik dipasang berbaris: jumlah keramik searah panjang adalah "
    "panjang lantai ÷ sisi keramik dibulatkan ke atas (keramik terakhir dipotong), begitu pula searah lebar. "
    "Jumlah keramik = hasil kali keduanya. Keramik dijual per dus, jadi jumlah dus juga dibulatkan ke atas.\n\n" + ATAS_ID + "\n\n"
    "Contoh: lantai 410 cm × 300 cm, keramik 40 cm, satu dus berisi 6: searah panjang 11, searah lebar 8, jadi 88 keramik dan 15 dus:\n\n"
    "```\nKeramik: 88\nDus: 15\n```",
    "Empat baris: panjang lantai (cm), lebar lantai (cm), sisi keramik (cm), isi satu dus; bilangan bulat.",
    "Dua baris: `Keramik: K` lalu `Dus: D`.",
    "Panjang dan lebar 200–1500; sisi keramik 20–60; isi dus 4–25.", B),
   ("Floor Tiles and Boxes",
    "A rectangular floor is covered with square tiles laid in rows: the number of tiles along the length is "
    "floor length ÷ tile side rounded up (the last tile is cut), and the same along the width. "
    "Number of tiles = the product of the two. Tiles are sold by the box, so the number of boxes is rounded up as well.\n\n" + ATAS_EN + "\n\n"
    "Example: a 410 cm × 300 cm floor, 40 cm tiles, 6 per box: 11 along the length, 8 along the width, so 88 tiles and 15 boxes:\n\n"
    "```\nKeramik: 88\nDus: 15\n```",
    "Four lines: floor length (cm), floor width (cm), tile side (cm), tiles per box; whole numbers.",
    "Two lines: `Keramik: K` then `Dus: D` (the words stay in Indonesian).",
    "Length and width 200–1500; tile side 20–60; tiles per box 4–25.", B),
   ("بلاط الأرضية وعدد الصناديق",
    "تُغطّى أرضية مستطيلة ببلاط مربع يُرصّ صفوفًا: عدد البلاطات باتجاه الطول هو طول الأرضية ÷ ضلع البلاطة مقرَّبًا إلى الأعلى "
    "(تُقصّ البلاطة الأخيرة)، وكذلك باتجاه العرض. عدد البلاطات = حاصل ضرب العددين. يُباع البلاط بالصندوق، فيُقرَّب عدد الصناديق إلى الأعلى أيضًا.\n\n"
    + ATAS_AR + "\n\n"
    "مثال: أرضية 410 سم × 300 سم، بلاط 40 سم، في الصندوق 6: باتجاه الطول 11 وباتجاه العرض 8، أي 88 بلاطة و15 صندوقًا:\n\n"
    "```\nKeramik: 88\nDus: 15\n```",
    "أربعة أسطر: طول الأرضية (سم)، عرض الأرضية (سم)، ضلع البلاطة (سم)، عدد البلاطات في الصندوق؛ أعداد صحيحة.",
    "سطران: `Keramik: K` ثم `Dus: D` (تبقى الكلمات بالإندونيسية).",
    "الطول والعرض 200–1500؛ ضلع البلاطة 20–60؛ محتوى الصندوق 4–25.", B))

py("sengkang-balok", "sedang", "trimmed",
   "print(r.randint(10, 120) * 100)\nprint(r.choice([75, 100, 125, 150, 175, 200, 250, 300]))\n"
   "print(r.randint(15, 50) * 10)\nprint(r.randint(20, 80) * 10)\nprint(r.choice([20, 25, 30, 40, 50]))\nprint(r.choice([50, 60, 75, 100]))\n",
   r'''
panjang_balok_mm = int(input())
jarak_mm = int(input())
lebar_mm = int(input())
tinggi_mm = int(input())
selimut_mm = int(input())
kait_mm = int(input())
jumlah = panjang_balok_mm // jarak_mm + 1
panjang_satu = 2 * (lebar_mm - 2 * selimut_mm) + 2 * (tinggi_mm - 2 * selimut_mm) + 2 * kait_mm
print("Jumlah:", jumlah)
print("Panjang satu:", panjang_satu, "mm")
print("Total:", jumlah * panjang_satu, "mm")
''',
   ("Sengkang Balok",
    "Sengkang dipasang di sepanjang balok dengan jarak tetap, mulai tepat di ujung balok. Jumlah sengkang = panjang balok // jarak + 1 "
    "(satu sengkang di ujung awal, lalu satu setiap jarak).\n\n"
    "Satu sengkang berbentuk persegi panjang yang mengikuti penampang balok dikurangi selimut beton di kedua sisi, ditambah dua kait: "
    "panjang satu sengkang = 2 × (lebar − 2 × selimut) + 2 × (tinggi − 2 × selimut) + 2 × kait.\n\n"
    "Contoh: balok 4000 mm, jarak 150 mm, penampang 250 × 400 mm, selimut 30 mm, kait 60 mm: jumlah 4000 // 150 + 1 = 27; "
    "panjang satu 2 × 190 + 2 × 340 + 120 = 1180 mm. Keluarannya\n\n```\nJumlah: 27\nPanjang satu: 1180 mm\nTotal: 31860 mm\n```",
    "Enam baris: panjang balok, jarak sengkang, lebar penampang, tinggi penampang, selimut beton, panjang satu kait; bilangan bulat dalam mm.",
    "Tiga baris: `Jumlah: N`, `Panjang satu: P mm`, `Total: T mm`.",
    "Panjang balok 1000–12000, jarak 75–300, lebar 150–500, tinggi 200–800, selimut 20–50, kait 50–100.", B),
   ("Beam Stirrups",
    "Stirrups are placed along a beam at a fixed spacing, starting exactly at the end of the beam. Number of stirrups = beam length // spacing + 1 "
    "(one at the start, then one every spacing).\n\n"
    "One stirrup is a rectangle following the beam section less the concrete cover on both sides, plus two hooks: "
    "length of one stirrup = 2 × (width − 2 × cover) + 2 × (height − 2 × cover) + 2 × hook.\n\n"
    "Example: a 4000 mm beam, spacing 150 mm, section 250 × 400 mm, cover 30 mm, hook 60 mm: number 4000 // 150 + 1 = 27; "
    "one stirrup 2 × 190 + 2 × 340 + 120 = 1180 mm. The output is\n\n```\nJumlah: 27\nPanjang satu: 1180 mm\nTotal: 31860 mm\n```",
    "Six lines: beam length, stirrup spacing, section width, section height, concrete cover, length of one hook; whole numbers of mm.",
    "Three lines: `Jumlah: N`, `Panjang satu: P mm`, `Total: T mm` (the words stay in Indonesian).",
    "Beam length 1000–12000, spacing 75–300, width 150–500, height 200–800, cover 20–50, hook 50–100.", B),
   ("كانات الكمرة",
    "تُوضع الكانات على طول الكمرة بتباعد ثابت ابتداءً من طرف الكمرة تمامًا. عدد الكانات = طول الكمرة // التباعد + 1 "
    "(كانة عند البداية ثم كانة عند كل تباعد).\n\n"
    "الكانة الواحدة مستطيل يتبع مقطع الكمرة مطروحًا منه الغطاء الخرساني من الجهتين، مضافًا إليه خطافان: "
    "طول الكانة = 2 × (العرض − 2 × الغطاء) + 2 × (الارتفاع − 2 × الغطاء) + 2 × الخطاف.\n\n"
    "مثال: كمرة 4000 مم، تباعد 150 مم، مقطع 250 × 400 مم، غطاء 30 مم، خطاف 60 مم: العدد 4000 // 150 + 1 = 27؛ "
    "وطول الكانة 2 × 190 + 2 × 340 + 120 = 1180 مم. فيكون المخرج\n\n```\nJumlah: 27\nPanjang satu: 1180 mm\nTotal: 31860 mm\n```",
    "ستة أسطر: طول الكمرة، تباعد الكانات، عرض المقطع، ارتفاع المقطع، الغطاء الخرساني، طول الخطاف الواحد؛ أعداد صحيحة بالمليمتر.",
    "ثلاثة أسطر: `Jumlah: N` ثم `Panjang satu: P mm` ثم `Total: T mm` (تبقى الكلمات بالإندونيسية).",
    "طول الكمرة 1000–12000، التباعد 75–300، العرض 150–500، الارتفاع 200–800، الغطاء 20–50، الخطاف 50–100.", B))

py("ritase-truk", "sedang", "trimmed",
   "print(r.randint(1, 60))\nprint(r.choice([20, 25, 30, 35, 40, 45, 50, 60]))\nprint(r.randint(5, 40))\nprint(r.randint(4, 25))\n",
   r'''
jarak_km = int(input())
kecepatan_km_jam = int(input())
muat_bongkar_menit = int(input())
kapasitas_m3 = int(input())
sekali_jalan_menit = jarak_km * 60 // kecepatan_km_jam
siklus_menit = 2 * sekali_jalan_menit + muat_bongkar_menit
ritase = 480 // siklus_menit
print("Siklus:", siklus_menit, "menit")
print("Ritase:", ritase)
print("Volume:", ritase * kapasitas_m3, "m3")
''',
   ("Ritase Truk Pengangkut Tanah",
    "Sebuah truk mengangkut tanah dari lokasi galian ke tempat pembuangan selama 8 jam kerja (480 menit).\n\n"
    "- Waktu sekali jalan (menit) = jarak × 60 // kecepatan, dibulatkan ke bawah.\n"
    "- Satu siklus = pergi + pulang + waktu muat dan bongkar.\n"
    "- Ritase = banyaknya siklus penuh dalam 480 menit.\n"
    "- Volume terangkut = ritase × kapasitas truk.\n\n"
    "Contoh: jarak 12 km, kecepatan 40 km/jam, muat-bongkar 15 menit, kapasitas 7 m³: sekali jalan 18 menit, siklus 51 menit, "
    "ritase 480 // 51 = 9. Keluarannya\n\n```\nSiklus: 51 menit\nRitase: 9\nVolume: 63 m3\n```",
    "Empat baris: jarak (km), kecepatan (km/jam), waktu muat dan bongkar per siklus (menit), kapasitas truk (m³); bilangan bulat.",
    "Tiga baris: `Siklus: S menit`, `Ritase: R`, `Volume: V m3`.",
    "Jarak 1–60, kecepatan 20–60, muat-bongkar 5–40, kapasitas 4–25.", B),
   ("Truck Trips for Earth Hauling",
    "A truck hauls soil from the excavation to the disposal area during an 8-hour working day (480 minutes).\n\n"
    "- One-way travel time (minutes) = distance × 60 // speed, rounded down.\n"
    "- One cycle = going + returning + loading and unloading time.\n"
    "- Trips (ritase) = the number of complete cycles in 480 minutes.\n"
    "- Volume hauled = trips × truck capacity.\n\n"
    "Example: distance 12 km, speed 40 km/h, loading and unloading 15 minutes, capacity 7 m³: one way 18 minutes, cycle 51 minutes, "
    "trips 480 // 51 = 9. The output is\n\n```\nSiklus: 51 menit\nRitase: 9\nVolume: 63 m3\n```",
    "Four lines: distance (km), speed (km/h), loading and unloading time per cycle (minutes), truck capacity (m³); whole numbers.",
    "Three lines: `Siklus: S menit`, `Ritase: R`, `Volume: V m3` (the words stay in Indonesian).",
    "Distance 1–60, speed 20–60, loading and unloading 5–40, capacity 4–25.", B),
   ("رحلات شاحنة نقل التربة",
    "تنقل شاحنة التربة من موقع الحفر إلى موقع الطرح خلال يوم عمل من 8 ساعات (480 دقيقة).\n\n"
    "- زمن الذهاب (بالدقائق) = المسافة × 60 // السرعة، مقرَّبًا إلى الأسفل.\n"
    "- الدورة الواحدة = الذهاب + العودة + زمن التحميل والتفريغ.\n"
    "- عدد الرحلات (ritase) = عدد الدورات الكاملة في 480 دقيقة.\n"
    "- الحجم المنقول = عدد الرحلات × سعة الشاحنة.\n\n"
    "مثال: المسافة 12 كم، السرعة 40 كم/س، التحميل والتفريغ 15 دقيقة، السعة 7 م³: الذهاب 18 دقيقة، الدورة 51 دقيقة، "
    "الرحلات 480 // 51 = 9. فيكون المخرج\n\n```\nSiklus: 51 menit\nRitase: 9\nVolume: 63 m3\n```",
    "أربعة أسطر: المسافة (كم)، السرعة (كم/س)، زمن التحميل والتفريغ لكل دورة (دقيقة)، سعة الشاحنة (م³)؛ أعداد صحيحة.",
    "ثلاثة أسطر: `Siklus: S menit` ثم `Ritase: R` ثم `Volume: V m3` (تبقى الكلمات بالإندونيسية).",
    "المسافة 1–60، السرعة 20–60، التحميل والتفريغ 5–40، السعة 4–25.", B))

py("kolom-bulat", "sedang", "float",
   "print(r.randint(4, 24) * 5)\nprint(r.randint(25, 60) * 10)\nprint(r.randint(1, 60))\n",
   r'''
diameter_cm = int(input())
tinggi_cm = int(input())
jumlah_kolom = int(input())
luas_cm2 = 3.14 * diameter_cm * diameter_cm / 4
volume_m3 = jumlah_kolom * luas_cm2 * tinggi_cm / 1000000
print(volume_m3)
''',
   ("Beton untuk Kolom Bulat",
    "Sebuah gedung mempunyai beberapa kolom bulat yang sama. Volume satu kolom = luas lingkaran × tinggi, dengan "
    "luas lingkaran = π × d² ÷ 4. Gunakan π = 3,14 (parameter latihan). Ukuran diberikan dalam cm; hitung volume beton "
    "seluruh kolom dalam m³ (1 m³ = 1 000 000 cm³).\n\n"
    "Contoh: 8 kolom berdiameter 40 cm setinggi 350 cm: luas 1256 cm², satu kolom 439 600 cm³, seluruhnya `3.5168` m³.",
    "Tiga baris: diameter kolom (cm), tinggi kolom (cm), jumlah kolom; bilangan bulat.",
    "Satu baris: volume beton seluruh kolom dalam m³.",
    "Diameter 20–120, tinggi 250–600, jumlah kolom 1–60.", P),
   ("Concrete for Round Columns",
    "A building has several identical round columns. Volume of one column = area of the circle × height, with "
    "area of a circle = π × d² ÷ 4. Use π = 3.14 (a practice parameter). Sizes are given in cm; compute the concrete volume "
    "of all columns in m³ (1 m³ = 1 000 000 cm³).\n\n"
    "Example: 8 columns of 40 cm diameter and 350 cm height: area 1256 cm², one column 439 600 cm³, all together `3.5168` m³.",
    "Three lines: column diameter (cm), column height (cm), number of columns; whole numbers.",
    "One line: the concrete volume of all columns in m³.",
    "Diameter 20–120, height 250–600, number of columns 1–60.", P),
   ("خرسانة الأعمدة الدائرية",
    "في مبنى عدة أعمدة دائرية متماثلة. حجم العمود الواحد = مساحة الدائرة × الارتفاع، ومساحة الدائرة = π × d² ÷ 4. "
    "استعمل π = 3.14 (معامل تدريبي). الأبعاد بالسنتيمتر؛ احسب حجم خرسانة جميع الأعمدة بالمتر المكعب (1 م³ = 1 000 000 سم³).\n\n"
    "مثال: 8 أعمدة قطرها 40 سم وارتفاعها 350 سم: المساحة 1256 سم²، والعمود الواحد 439 600 سم³، والمجموع `3.5168` م³.",
    "ثلاثة أسطر: قطر العمود (سم)، ارتفاع العمود (سم)، عدد الأعمدة؛ أعداد صحيحة.",
    "سطر واحد: حجم خرسانة جميع الأعمدة بالمتر المكعب.",
    "القطر 20–120، الارتفاع 250–600، عدد الأعمدة 1–60.", P))

py("interpolasi-elevasi", "sedang", "float",
   "x1 = r.randint(0, 50) * 10\nx2 = x1 + r.randint(2, 40) * 5\nx = {2: x1, 3: x2}.get(i, r.randint(x1, x2))\n"
   "print(x1)\nprint(r.randint(500, 9000))\nprint(x2)\nprint(r.randint(500, 9000))\nprint(x)\n",
   r'''
sta_1 = int(input())
elevasi_1 = int(input())
sta_2 = int(input())
elevasi_2 = int(input())
sta = int(input())
elevasi = elevasi_1 + (elevasi_2 - elevasi_1) * (sta - sta_1) / (sta_2 - sta_1)
print(elevasi)
''',
   ("Elevasi di Antara Dua Patok",
    "Elevasi tanah diketahui di dua patok. Elevasi sebuah titik di antara keduanya ditaksir dengan interpolasi linear, "
    "seolah-olah tanah di antara kedua patok berupa garis lurus:\n\n"
    "elevasi = e₁ + (e₂ − e₁) × (x − x₁) ÷ (x₂ − x₁)\n\n"
    "dengan x₁, x₂ posisi kedua patok, e₁, e₂ elevasinya, dan x posisi titik yang ditanyakan.\n\n"
    "Contoh: patok di 0 m berelevasi 1200 cm dan patok di 50 m berelevasi 1300 cm; titik di 20 m: 1200 + 100 × 20 ÷ 50 = `1240.0` cm.",
    "Lima baris: x₁ (m), e₁ (cm), x₂ (m), e₂ (cm), lalu x (m); bilangan bulat.",
    "Satu baris: elevasi titik itu dalam cm.",
    "x₁ < x₂; x₁ ≤ x ≤ x₂; posisi 0–700; elevasi 500–9000.", P),
   ("Elevation Between Two Pegs",
    "The ground elevation is known at two pegs. The elevation of a point between them is estimated by linear interpolation, "
    "as if the ground between the pegs were a straight line:\n\n"
    "elevation = e₁ + (e₂ − e₁) × (x − x₁) ÷ (x₂ − x₁)\n\n"
    "where x₁, x₂ are the positions of the pegs, e₁, e₂ their elevations, and x the position of the point asked for.\n\n"
    "Example: a peg at 0 m with elevation 1200 cm and a peg at 50 m with elevation 1300 cm; the point at 20 m: 1200 + 100 × 20 ÷ 50 = `1240.0` cm.",
    "Five lines: x₁ (m), e₁ (cm), x₂ (m), e₂ (cm), then x (m); whole numbers.",
    "One line: the elevation of the point in cm.",
    "x₁ < x₂; x₁ ≤ x ≤ x₂; positions 0–700; elevations 500–9000.", P),
   ("المنسوب بين وتدين",
    "منسوب الأرض معلوم عند وتدين. يُقدَّر منسوب نقطة بينهما بالاستيفاء الخطي، كأن الأرض بين الوتدين خط مستقيم:\n\n"
    "المنسوب = e₁ + (e₂ − e₁) × (x − x₁) ÷ (x₂ − x₁)\n\n"
    "حيث x₁ و x₂ موضعا الوتدين، و e₁ و e₂ منسوباهما، و x موضع النقطة المطلوبة.\n\n"
    "مثال: وتد عند 0 م منسوبه 1200 سم ووتد عند 50 م منسوبه 1300 سم؛ النقطة عند 20 م: 1200 + 100 × 20 ÷ 50 = `1240.0` سم.",
    "خمسة أسطر: x₁ (م)، e₁ (سم)، x₂ (م)، e₂ (سم)، ثم x (م)؛ أعداد صحيحة.",
    "سطر واحد: منسوب النقطة بالسنتيمتر.",
    "x₁ < x₂؛ x₁ ≤ x ≤ x₂؛ المواضع 0–700؛ المناسيب 500–9000.", P))

# ================================== SULIT (4) ==================================

py("potong-besi", "sulit", "trimmed",
   "print({2: 6000, 3: 4000, 4: 6001}.get(i, r.randint(300, 6000)))\nprint({5: 1}.get(i, r.randint(1, 400)))\n",
   r'''
panjang_potong_mm = int(input())
jumlah_potong = int(input())
per_batang = 12000 // panjang_potong_mm
batang = (jumlah_potong + per_batang - 1) // per_batang
sisa_mm = batang * 12000 - jumlah_potong * panjang_potong_mm
print("Potongan per batang:", per_batang)
print("Batang:", batang)
print("Sisa:", sisa_mm, "mm")
''',
   ("Memotong Besi 12 Meter",
    "Besi tulangan dijual dalam batang sepanjang 12 m (12 000 mm). Proyek membutuhkan sejumlah potongan yang sama panjang. "
    "Potongan tidak boleh disambung, jadi dari satu batang hanya didapat 12000 // panjang potongan.\n\n"
    "Hitung: (1) banyaknya potongan dari satu batang; (2) jumlah batang yang harus dibeli, dibulatkan ke atas; "
    "(3) panjang besi yang tidak terpakai: seluruh panjang batang yang dibeli dikurangi seluruh panjang potongan yang dibutuhkan.\n\n"
    + ATAS_ID + "\n\n"
    "Contoh: 10 potongan sepanjang 2750 mm: satu batang menghasilkan 4 potongan, jadi dibeli 3 batang; sisa 36 000 − 27 500 = 8500 mm:\n\n"
    "```\nPotongan per batang: 4\nBatang: 3\nSisa: 8500 mm\n```",
    "Dua baris: panjang satu potongan (mm), lalu jumlah potongan yang dibutuhkan; bilangan bulat.",
    "Tiga baris: `Potongan per batang: A`, `Batang: B`, `Sisa: C mm`.",
    "Panjang potongan 300–6001; jumlah potongan 1–400.", B),
   ("Cutting 12-Metre Bars",
    "Reinforcing steel is sold in bars 12 m long (12 000 mm). The project needs a number of pieces of the same length. "
    "Pieces may not be spliced, so one bar gives only 12000 // piece length.\n\n"
    "Compute: (1) the number of pieces from one bar; (2) the number of bars to buy, rounded up; "
    "(3) the length of steel left unused: the whole length of the bars bought minus the whole length of the pieces needed.\n\n"
    + ATAS_EN + "\n\n"
    "Example: 10 pieces of 2750 mm: one bar gives 4 pieces, so 3 bars are bought; left over 36 000 − 27 500 = 8500 mm:\n\n"
    "```\nPotongan per batang: 4\nBatang: 3\nSisa: 8500 mm\n```",
    "Two lines: the length of one piece (mm), then the number of pieces needed; whole numbers.",
    "Three lines: `Potongan per batang: A`, `Batang: B`, `Sisa: C mm` (the words stay in Indonesian).",
    "Piece length 300–6001; number of pieces 1–400.", B),
   ("قصّ قضبان طولها 12 مترًا",
    "يُباع حديد التسليح قضبانًا طول الواحد منها 12 م (12 000 مم). يحتاج المشروع إلى عدد من القطع المتساوية الطول. "
    "لا يجوز وصل القطع، فالقضيب الواحد يعطي 12000 // طول القطعة فقط.\n\n"
    "احسب: (1) عدد القطع من القضيب الواحد؛ (2) عدد القضبان الواجب شراؤها مقرَّبًا إلى الأعلى؛ "
    "(3) طول الحديد غير المستعمل: مجموع أطوال القضبان المشتراة ناقص مجموع أطوال القطع المطلوبة.\n\n"
    + ATAS_AR + "\n\n"
    "مثال: 10 قطع طولها 2750 مم: القضيب يعطي 4 قطع، فيُشترى 3 قضبان؛ والمتبقي 36 000 − 27 500 = 8500 مم:\n\n"
    "```\nPotongan per batang: 4\nBatang: 3\nSisa: 8500 mm\n```",
    "سطران: طول القطعة الواحدة (مم) ثم عدد القطع المطلوبة؛ عددان صحيحان.",
    "ثلاثة أسطر: `Potongan per batang: A` ثم `Batang: B` ثم `Sisa: C mm` (تبقى الكلمات بالإندونيسية).",
    "طول القطعة 300–6001؛ عدد القطع 1–400.", B))

py("selesai-pengecoran", "sulit", "trimmed",
   "if i == 2:\n    print(23)\n    print(59)\n    print(1)\nelif i == 3:\n    print(0)\n    print(0)\n    print(2880)\nelse:\n"
   "    print(r.randint(0, 23))\n    print(r.randint(0, 59))\n    print(r.randint(1, 5000))\n",
   r'''
jam_mulai = int(input())
menit_mulai = int(input())
durasi_menit = int(input())
total_menit = jam_mulai * 60 + menit_mulai + durasi_menit
hari = total_menit // 1440
sisa_menit = total_menit % 1440
print(sisa_menit // 60, sisa_menit % 60)
print(hari)
''',
   ("Kapan Pengecoran Selesai",
    "Pengecoran besar berlangsung tanpa henti dan bisa melewati tengah malam, bahkan beberapa hari. Dari jam mulai dan lama pengecoran "
    "dalam menit, tentukan pukul berapa pengecoran selesai dan berapa kali tengah malam terlewati. Satu hari = 1440 menit.\n\n"
    "Ubah jam mulai menjadi menit sejak pukul 00.00, tambahkan durasinya, lalu uraikan kembali dengan `//` dan `%`.\n\n"
    "Contoh: mulai pukul 22.30 selama 200 menit: 1350 + 200 = 1550 menit, yaitu 1 hari lebih 110 menit, jadi selesai pukul 01.50 "
    "pada hari berikutnya:\n\n```\n1 50\n1\n```",
    "Tiga baris: jam mulai (0–23), menit mulai (0–59), lama pengecoran (menit); bilangan bulat.",
    "Baris pertama: jam dan menit selesai, dipisah satu spasi, tanpa angka nol di depan (pukul 01.05 ditulis `1 5`). "
    "Baris kedua: banyaknya tengah malam yang terlewati (0 bila selesai pada hari yang sama).",
    "Lama pengecoran 1 sampai 5000.", B),
   ("When the Pour Ends",
    "A large pour runs without stopping and may pass midnight, even several days. From the starting time and the duration in minutes, "
    "find at what time the pour ends and how many midnights are passed. One day = 1440 minutes.\n\n"
    "Turn the starting time into minutes since 00:00, add the duration, then split it again with `//` and `%`.\n\n"
    "Example: starting at 22:30 for 200 minutes: 1350 + 200 = 1550 minutes, which is 1 day and 110 minutes, so it ends at 01:50 "
    "on the next day:\n\n```\n1 50\n1\n```",
    "Three lines: starting hour (0–23), starting minute (0–59), duration of the pour (minutes); whole numbers.",
    "First line: the hour and minute of the end, separated by one space, without leading zeros (01:05 is written `1 5`). "
    "Second line: the number of midnights passed (0 if it ends on the same day).",
    "The duration is 1 to 5000.", B),
   ("متى ينتهي الصبّ",
    "يستمر الصبّ الكبير دون توقف وقد يتجاوز منتصف الليل، بل عدة أيام. من وقت البدء ومدة الصبّ بالدقائق حدِّد في أي ساعة ينتهي الصبّ "
    "وكم مرة يُتجاوَز منتصف الليل. اليوم = 1440 دقيقة.\n\n"
    "حوِّل وقت البدء إلى دقائق منذ الساعة 00:00، أضف المدة، ثم فكِّك الناتج بالعاملين `//` و`%`.\n\n"
    "مثال: البدء عند 22:30 لمدة 200 دقيقة: 1350 + 200 = 1550 دقيقة، أي يوم واحد و110 دقائق، فينتهي عند 01:50 في اليوم التالي:\n\n"
    "```\n1 50\n1\n```",
    "ثلاثة أسطر: ساعة البدء (0–23)، دقيقة البدء (0–59)، مدة الصبّ (بالدقائق)؛ أعداد صحيحة.",
    "السطر الأول: ساعة الانتهاء ودقيقته تفصل بينهما مسافة واحدة، دون أصفار في البداية (الساعة 01:05 تُكتب `1 5`). "
    "السطر الثاني: عدد مرات تجاوز منتصف الليل (0 إذا انتهى في اليوم نفسه).",
    "مدة الصبّ من 1 إلى 5000.", B))

py("galian-pondasi", "sulit", "float",
   "print(r.randint(4, 40))\nprint(r.randint(4, 40))\nprint(r.randint(12, 24) * 5)\nprint(r.randint(12, 30) * 5)\n"
   "print(r.randint(5, 15))\nprint(r.randint(4, 8))\n",
   r'''
panjang_m = int(input())
lebar_m = int(input())
lebar_galian_cm = int(input())
dalam_galian_cm = int(input())
tebal_pasir_cm = int(input())
kapasitas_truk_m3 = int(input())
panjang_as_m = 2 * (panjang_m + lebar_m)
galian_x10000 = panjang_as_m * lebar_galian_cm * dalam_galian_cm
pasir_x10000 = panjang_as_m * lebar_galian_cm * tebal_pasir_cm
truk = (galian_x10000 + kapasitas_truk_m3 * 10000 - 1) // (kapasitas_truk_m3 * 10000)
print(galian_x10000 / 10000)
print(pasir_x10000 / 10000)
print(truk)
''',
   ("Galian Pondasi Menerus",
    "Pondasi menerus dibuat di sekeliling bangunan persegi panjang. Panjang galian dihitung menurut garis as: 2 × (panjang + lebar) bangunan.\n\n"
    "- Volume galian = panjang as × lebar galian × kedalaman galian.\n"
    "- Volume pasir urug di dasar galian = panjang as × lebar galian × tebal pasir.\n"
    "- Seluruh tanah galian dibuang dengan truk; jumlah truk dibulatkan ke atas.\n\n"
    "Ukuran bangunan dalam m, ukuran galian dalam cm, volume diminta dalam m³. Supaya jumlah truk tepat, bulatkan ke atas dengan "
    "bilangan bulat, bukan dengan hasil pecahan. " + ATAS_ID + "\n\n"
    "Contoh: bangunan 10 m × 8 m, galian selebar 80 cm sedalam 100 cm, pasir 10 cm, truk 6 m³: panjang as 36 m, galian 28,8 m³, "
    "pasir 2,88 m³, truk 28,8 ÷ 6 = 4,8 dibulatkan menjadi 5:\n\n```\n28.8\n2.88\n5\n```",
    "Enam baris: panjang bangunan (m), lebar bangunan (m), lebar galian (cm), kedalaman galian (cm), tebal pasir urug (cm), "
    "kapasitas truk (m³); bilangan bulat.",
    "Tiga baris: volume galian (m³), volume pasir urug (m³), jumlah truk (bilangan bulat).",
    "Panjang dan lebar bangunan 4–40; lebar galian 60–120; kedalaman 60–150; tebal pasir 5–15; kapasitas truk 4–8.", P),
   ("Strip Footing Excavation",
    "A strip footing runs all round a rectangular building. The length of the trench is taken along the centre line: 2 × (length + width) of the building.\n\n"
    "- Excavation volume = centre-line length × trench width × trench depth.\n"
    "- Volume of the sand bed at the bottom = centre-line length × trench width × sand thickness.\n"
    "- All excavated soil is removed by truck; the number of trucks is rounded up.\n\n"
    "The building is given in m, the trench in cm, and volumes are wanted in m³. To get the number of trucks exactly right, round up with "
    "whole numbers, not with the fractional result. " + ATAS_EN + "\n\n"
    "Example: a 10 m × 8 m building, a trench 80 cm wide and 100 cm deep, 10 cm of sand, 6 m³ trucks: centre line 36 m, excavation 28.8 m³, "
    "sand 2.88 m³, trucks 28.8 ÷ 6 = 4.8 rounded up to 5:\n\n```\n28.8\n2.88\n5\n```",
    "Six lines: building length (m), building width (m), trench width (cm), trench depth (cm), sand thickness (cm), "
    "truck capacity (m³); whole numbers.",
    "Three lines: excavation volume (m³), sand volume (m³), number of trucks (a whole number).",
    "Building length and width 4–40; trench width 60–120; depth 60–150; sand thickness 5–15; truck capacity 4–8.", P),
   ("حفر الأساس الشريطي",
    "يُنشأ أساس شريطي حول مبنى مستطيل. يُحسب طول الحفر على خط المحور: 2 × (طول المبنى + عرضه).\n\n"
    "- حجم الحفر = طول المحور × عرض الحفر × عمق الحفر.\n"
    "- حجم فرشة الرمل في قاع الحفر = طول المحور × عرض الحفر × سماكة الرمل.\n"
    "- تُنقل تربة الحفر كلها بالشاحنات؛ ويُقرَّب عدد الشاحنات إلى الأعلى.\n\n"
    "أبعاد المبنى بالمتر وأبعاد الحفر بالسنتيمتر والحجوم مطلوبة بالمتر المكعب. ليكون عدد الشاحنات مضبوطًا قرِّب إلى الأعلى "
    "بالأعداد الصحيحة لا بالناتج الكسري. " + ATAS_AR + "\n\n"
    "مثال: مبنى 10 م × 8 م، حفر عرضه 80 سم وعمقه 100 سم، رمل 10 سم، شاحنات 6 م³: طول المحور 36 م، الحفر 28.8 م³، "
    "الرمل 2.88 م³، الشاحنات 28.8 ÷ 6 = 4.8 تُقرَّب إلى 5:\n\n```\n28.8\n2.88\n5\n```",
    "ستة أسطر: طول المبنى (م)، عرض المبنى (م)، عرض الحفر (سم)، عمق الحفر (سم)، سماكة فرشة الرمل (سم)، سعة الشاحنة (م³)؛ أعداد صحيحة.",
    "ثلاثة أسطر: حجم الحفر (م³)، حجم الرمل (م³)، عدد الشاحنات (عدد صحيح).",
    "طول المبنى وعرضه 4–40؛ عرض الحفر 60–120؛ العمق 60–150؛ سماكة الرمل 5–15؛ سعة الشاحنة 4–8.", P))

py("tangki-hujan", "sulit", "trimmed",
   "print(r.randint(20, 800))\nprint(r.randint(2, 80))\nprint(r.randint(2, 200) * 100)\n",
   r'''
luas_atap_m2 = int(input())
intensitas_mm_jam = int(input())
volume_tangki_liter = int(input())
liter_per_jam = luas_atap_m2 * intensitas_mm_jam
menit_penuh = (volume_tangki_liter * 60 + liter_per_jam - 1) // liter_per_jam
print("Debit:", liter_per_jam, "liter/jam")
print("Penuh:", menit_penuh // 60, "jam", menit_penuh % 60, "menit")
''',
   ("Tangki Penampung Air Hujan",
    "Air hujan dari atap dialirkan ke tangki kosong. Hujan 1 mm di atas 1 m² sama dengan 1 liter, jadi air yang masuk tiap jam "
    "(liter/jam) = luas atap (m²) × intensitas hujan (mm/jam).\n\n"
    "Hitung debit itu, lalu berapa lama sampai tangki penuh bila hujan turun terus dengan intensitas tetap. Lamanya dihitung dalam "
    "menit penuh, dibulatkan ke atas: menit = volume tangki × 60 ÷ debit, lalu ditulis sebagai jam dan menit.\n\n" + ATAS_ID + "\n\n"
    "Contoh: atap 100 m², hujan 12 mm/jam, tangki 2000 liter: debit 1200 liter/jam; 2000 × 60 ÷ 1200 = 100 menit:\n\n"
    "```\nDebit: 1200 liter/jam\nPenuh: 1 jam 40 menit\n```",
    "Tiga baris: luas atap (m²), intensitas hujan (mm/jam), volume tangki (liter); bilangan bulat.",
    "Dua baris: `Debit: D liter/jam` lalu `Penuh: J jam M menit`, dengan M dari 0 sampai 59.",
    "Luas atap 20–800; intensitas 2–80; volume tangki 200–20000.", B),
   ("Rainwater Storage Tank",
    "Rain from a roof is led into an empty tank. 1 mm of rain on 1 m² equals 1 litre, so the water coming in each hour "
    "(litres per hour) = roof area (m²) × rainfall intensity (mm per hour).\n\n"
    "Compute that inflow, then how long it takes until the tank is full if it keeps raining at a constant intensity. The time is counted in "
    "whole minutes, rounded up: minutes = tank volume × 60 ÷ inflow, then written as hours and minutes.\n\n" + ATAS_EN + "\n\n"
    "Example: a 100 m² roof, 12 mm/h of rain, a 2000 litre tank: inflow 1200 litres per hour; 2000 × 60 ÷ 1200 = 100 minutes:\n\n"
    "```\nDebit: 1200 liter/jam\nPenuh: 1 jam 40 menit\n```",
    "Three lines: roof area (m²), rainfall intensity (mm per hour), tank volume (litres); whole numbers.",
    "Two lines: `Debit: D liter/jam` then `Penuh: J jam M menit`, with M from 0 to 59 (the words stay in Indonesian).",
    "Roof area 20–800; intensity 2–80; tank volume 200–20000.", B),
   ("خزان تجميع مياه الأمطار",
    "تُصرَّف مياه المطر من السطح إلى خزان فارغ. هطول 1 مم على 1 م² يساوي لترًا واحدًا، فالماء الداخل كل ساعة "
    "(لتر/ساعة) = مساحة السطح (م²) × شدة المطر (مم/ساعة).\n\n"
    "احسب هذا التدفق، ثم المدة حتى يمتلئ الخزان إذا استمر المطر بشدة ثابتة. تُحسب المدة بالدقائق الكاملة مقرَّبةً إلى الأعلى: "
    "الدقائق = حجم الخزان × 60 ÷ التدفق، ثم تُكتب بالساعات والدقائق.\n\n" + ATAS_AR + "\n\n"
    "مثال: سطح 100 م²، مطر 12 مم/ساعة، خزان 2000 لتر: التدفق 1200 لتر/ساعة؛ 2000 × 60 ÷ 1200 = 100 دقيقة:\n\n"
    "```\nDebit: 1200 liter/jam\nPenuh: 1 jam 40 menit\n```",
    "ثلاثة أسطر: مساحة السطح (م²)، شدة المطر (مم/ساعة)، حجم الخزان (لتر)؛ أعداد صحيحة.",
    "سطران: `Debit: D liter/jam` ثم `Penuh: J jam M menit` حيث M من 0 إلى 59 (تبقى الكلمات بالإندونيسية).",
    "مساحة السطح 20–800؛ الشدة 2–80؛ حجم الخزان 200–20000.", B))

tulis(__file__, "TS-PY", 1, "python", {"mudah_sekali": 8, "mudah": 8, "mudah_sedang": 6, "sedang": 6, "sulit": 4})
