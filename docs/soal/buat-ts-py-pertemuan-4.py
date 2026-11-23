#!/usr/bin/env python3
"""Menyusun docs/soal/ts-py-pertemuan-4.json.

TS-PY pertemuan 4 — Perulangan (Iteration) dengan for dan while (RPS Rev2
10.4): for dengan range(), while, akumulasi dan pencacah, nilai sentinel,
perulangan bersarang, break dan continue.

Konsep yang diizinkan di produksi: IO_DASAR, ARITMETIKA, VARIABEL_TIPE,
KONVERSI_TIPE, FORMAT_OUTPUT, PERCABANGAN, PERULANGAN, NESTED_LOOP,
BREAK_CONTINUE. Belum ada list, fungsi, atau operasi string: data dibaca dan
diolah satu per satu di dalam perulangan.

Pilihan teknis (DECISIONS.md, 2026-11-23):
- Banyaknya data diberikan di baris pertama (atau data diakhiri nilai
  sentinel), lalu satu nilai per baris.
- Semua soal `trimmed`; hasil pecahan dicetak dengan jumlah desimal yang
  ditentukan dan generator menjauhi batas pembulatan (fungsi aman()).
- Baris yang dibentuk dari beberapa cetakan memakai `print(..., end="")`.

    python3 docs/soal/buat-ts-py-pertemuan-4.py
"""
from bangun import soal, tulis

CATAT = {
    "id": " Panggil `input()` tanpa teks di dalam kurungnya.",
    "en": " Call `input()` with nothing inside the brackets.",
    "ar": " استدعِ `input()` دون أي نص بين القوسين.",
}
AMAN = "def aman(x, d):\n    y = abs(x) * 10 ** d\n    return abs(y - int(y) - 0.5) > 0.002\n\n\n"


def t(bahasa, judul, isi, masuk, keluar, batas):
    return (judul, isi, masuk + CATAT[bahasa], keluar, batas)


def py(slug, tingkat, gen, ref, id, en, ar):
    soal(slug, tingkat, "trimmed", gen, ref.lstrip("\n"), t("id", *id), t("en", *en), t("ar", *ar), waktu_ms=5000)


# ============================== MUDAH SEKALI (8) ==============================

py("total-ritase", "mudah_sekali",
   "n = 1 if i == 3 else r.randint(2, 30)\nprint(n)\nfor _ in range(n):\n    print(r.randint(3, 12))\n",
   r'''
banyak = int(input())
total = 0
for _ in range(banyak):
    volume = int(input())
    total = total + volume
print(f"Total: {total} m3")
''',
   ("Total Volume Angkutan",
    "Mandor mencatat volume tanah setiap ritase truk. Jumlahkan semua ritase hari itu.\n\n"
    "Contoh: masukan `3`, lalu `7`, `7`, `5` menghasilkan `Total: 19 m3`.",
    "Baris pertama: banyaknya ritase N. Lalu N baris, masing-masing volume satu ritase (m³, bilangan bulat).",
    "Satu baris berbentuk `Total: X m3`.",
    "N 1 sampai 30; setiap volume 3 sampai 12."),
   ("Total Hauled Volume",
    "A foreman records the volume of soil of every truck trip. Add up all the trips of the day.\n\n"
    "Example: the input `3`, then `7`, `7`, `5` gives `Total: 19 m3`.",
    "First line: the number of trips N. Then N lines, each the volume of one trip (m³, a whole number).",
    "One line of the form `Total: X m3`.",
    "N is 1 to 30; each volume is 3 to 12."),
   ("إجمالي الحجم المنقول",
    "يسجّل رئيس العمال حجم التربة في كل رحلة شاحنة. اجمع رحلات اليوم كلها.\n\nمثال: المدخل `3` ثم `7` و`7` و`5` يعطي `Total: 19 m3`.",
    "السطر الأول: عدد الرحلات N. ثم N سطرًا، في كل سطر حجم رحلة واحدة (م³، عدد صحيح).",
    "سطر واحد بالشكل `Total: X m3`.",
    "N من 1 إلى 30؛ وكل حجم من 3 إلى 12."))

py("patok-berjarak", "mudah_sekali",
   "s = r.choice([5, 10, 20, 25, 50])\nk = r.randint(1, 20)\nL = s * k if i % 3 == 0 else s * k + r.randint(1, s - 1)\nprint(L)\nprint(s)\n",
   r'''
panjang = int(input())
jarak = int(input())
for posisi in range(0, panjang + 1, jarak):
    print(posisi)
''',
   ("Posisi Patok di Sepanjang Jalan",
    "Patok dipasang di sepanjang sumbu jalan mulai dari titik 0, lalu setiap jarak tertentu, selama posisinya tidak melewati panjang "
    "jalan. Cetak posisi setiap patok, satu per baris.\n\n"
    "Contoh: panjang `100` dan jarak `25` menghasilkan `0`, `25`, `50`, `75`, `100` (lima baris). Panjang `90` dan jarak `25` "
    "menghasilkan `0`, `25`, `50`, `75`.",
    "Dua baris: panjang jalan (m), lalu jarak antarpatok (m). Keduanya bilangan bulat.",
    "Posisi setiap patok dalam meter, satu per baris, dari yang terkecil.",
    "Jarak 5 sampai 50; panjang 5 sampai 1050."),
   ("Stake Positions Along a Road",
    "Stakes are set along the road centre line starting at point 0, then at every given spacing, as long as the position does not pass "
    "the length of the road. Print the position of each stake, one per line.\n\n"
    "Example: length `100` and spacing `25` give `0`, `25`, `50`, `75`, `100` (five lines). Length `90` and spacing `25` give `0`, "
    "`25`, `50`, `75`.",
    "Two lines: the road length (m), then the spacing between stakes (m). Both are whole numbers.",
    "The position of each stake in metres, one per line, smallest first.",
    "Spacing 5 to 50; length 5 to 1050."),
   ("مواضع الأوتاد على طول الطريق",
    "تُثبَّت الأوتاد على محور الطريق ابتداءً من النقطة 0 ثم عند كل مسافة محددة، ما دام الموضع لا يتجاوز طول الطريق. "
    "اطبع موضع كل وتد في سطر.\n\n"
    "مثال: الطول `100` والمسافة `25` يعطيان `0` و`25` و`50` و`75` و`100` (خمسة أسطر). والطول `90` والمسافة `25` يعطيان "
    "`0` و`25` و`50` و`75`.",
    "سطران: طول الطريق (م) ثم المسافة بين الأوتاد (م). كلاهما عدد صحيح.",
    "موضع كل وتد بالمتر، كلٌّ في سطر، من الأصغر.",
    "المسافة من 5 إلى 50؛ والطول من 5 إلى 1050."))

py("tabel-inci", "mudah_sekali",
   "print(1 if i == 2 else r.randint(2, 24))\n",
   r'''
banyak = int(input())
for inci in range(1, banyak + 1):
    print(f"{inci} in = {inci * 25.4:.1f} mm")
''',
   ("Tabel Inci ke Milimeter",
    "Diameter pipa sering ditulis dalam inci. Buat tabel konversi dari 1 inci sampai N inci: 1 inci = 25.4 mm. "
    "Hasil dicetak dengan satu angka di belakang titik.\n\n"
    "Contoh: `3` menghasilkan\n\n```\n1 in = 25.4 mm\n2 in = 50.8 mm\n3 in = 76.2 mm\n```",
    "Satu baris: N (bilangan bulat).",
    "N baris berbentuk `K in = X mm`, untuk K = 1 sampai N.",
    "N 1 sampai 24."),
   ("Inch to Millimetre Table",
    "Pipe diameters are often given in inches. Make a conversion table from 1 inch to N inches: 1 inch = 25.4 mm. "
    "Print the result with one digit after the point.\n\n"
    "Example: `3` gives\n\n```\n1 in = 25.4 mm\n2 in = 50.8 mm\n3 in = 76.2 mm\n```",
    "One line: N (a whole number).",
    "N lines of the form `K in = X mm`, for K = 1 to N.",
    "N is 1 to 24."),
   ("جدول التحويل من البوصة إلى المليمتر",
    "تُكتب أقطار الأنابيب غالبًا بالبوصة. أنشئ جدول تحويل من 1 بوصة إلى N بوصة: 1 بوصة = 25.4 مم. اطبع الناتج بمنزلة عشرية واحدة.\n\n"
    "مثال: `3` يعطي\n\n```\n1 in = 25.4 mm\n2 in = 50.8 mm\n3 in = 76.2 mm\n```",
    "سطر واحد: N (عدد صحيح).",
    "N سطرًا بالشكل `K in = X mm` من K = 1 إلى N.",
    "N من 1 إلى 24."))

py("rata-kuat-tekan", "mudah_sekali",
   AMAN + "while True:\n    n = r.randint(2, 20)\n    v = [r.randint(18, 42) for _ in range(n)]\n    if aman(sum(v) / n, 2):\n        break\n"
   "print(n)\nfor x in v:\n    print(x)\n",
   r'''
banyak = int(input())
jumlah = 0
for _ in range(banyak):
    jumlah = jumlah + int(input())
rata_rata = jumlah / banyak
print(f"Rata-rata: {rata_rata:.2f} MPa")
''',
   ("Rata-rata Kuat Tekan",
    "Laboratorium menguji beberapa benda uji dari satu pengecoran. Hitung rata-rata kuat tekannya dan cetak dengan dua angka di "
    "belakang titik.\n\nContoh: masukan `4`, lalu `25`, `27`, `30`, `26` menghasilkan `Rata-rata: 27.00 MPa`.",
    "Baris pertama: banyaknya benda uji N. Lalu N baris, masing-masing satu hasil uji (MPa, bilangan bulat).",
    "Satu baris berbentuk `Rata-rata: R MPa`, dengan R dua angka di belakang titik.",
    "N 2 sampai 20; setiap hasil 18 sampai 42."),
   ("Average Compressive Strength",
    "A laboratory tests several specimens from one pour. Compute the average strength and print it with two digits after the point.\n\n"
    "Example: the input `4`, then `25`, `27`, `30`, `26` gives `Rata-rata: 27.00 MPa`.",
    "First line: the number of specimens N. Then N lines, each one test result (MPa, a whole number).",
    "One line of the form `Rata-rata: R MPa`, with R having two digits after the point (the word stays in Indonesian).",
    "N is 2 to 20; each result is 18 to 42."),
   ("متوسط مقاومة الضغط",
    "يختبر المختبر عدة عيّنات من صبّة واحدة. احسب متوسط المقاومة واطبعه بمنزلتين عشريتين.\n\n"
    "مثال: المدخل `4` ثم `25` و`27` و`30` و`26` يعطي `Rata-rata: 27.00 MPa`.",
    "السطر الأول: عدد العيّنات N. ثم N سطرًا، في كل سطر نتيجة اختبار (MPa، عدد صحيح).",
    "سطر واحد بالشكل `Rata-rata: R MPa` حيث R بمنزلتين عشريتين (تبقى الكلمة بالإندونيسية).",
    "N من 2 إلى 20؛ وكل نتيجة من 18 إلى 42."))

py("hari-hujan-sebulan", "mudah_sekali",
   "n = r.randint(5, 31)\nprint(n)\nfor _ in range(n):\n    print(0 if i == 4 or r.random() < 0.45 else r.randint(1, 95))\n",
   r'''
banyak_hari = int(input())
hari_hujan = 0
for _ in range(banyak_hari):
    curah = int(input())
    if curah > 0:
        hari_hujan = hari_hujan + 1
print(f"Hari hujan: {hari_hujan}")
''',
   ("Banyaknya Hari Hujan",
    "Pos hujan mencatat curah hujan setiap hari dalam milimeter; angka 0 berarti tidak hujan. Hitung berapa hari yang hujan.\n\n"
    "Contoh: masukan `5`, lalu `0`, `12`, `0`, `3`, `40` menghasilkan `Hari hujan: 3`.",
    "Baris pertama: banyaknya hari N. Lalu N baris, masing-masing curah hujan satu hari (mm, bilangan bulat).",
    "Satu baris berbentuk `Hari hujan: K`.",
    "N 5 sampai 31; curah hujan 0 sampai 95."),
   ("Number of Rainy Days",
    "A rain gauge records the rainfall of each day in millimetres; 0 means no rain. Count the days with rain.\n\n"
    "Example: the input `5`, then `0`, `12`, `0`, `3`, `40` gives `Hari hujan: 3`.",
    "First line: the number of days N. Then N lines, each the rainfall of one day (mm, a whole number).",
    "One line of the form `Hari hujan: K` (the words stay in Indonesian).",
    "N is 5 to 31; rainfall is 0 to 95."),
   ("عدد الأيام الماطرة",
    "تسجّل محطة المطر هطول كل يوم بالمليمتر؛ والعدد 0 يعني عدم هطول. احسب عدد الأيام الماطرة.\n\n"
    "مثال: المدخل `5` ثم `0` و`12` و`0` و`3` و`40` يعطي `Hari hujan: 3`.",
    "السطر الأول: عدد الأيام N. ثم N سطرًا، في كل سطر هطول يوم واحد (مم، عدد صحيح).",
    "سطر واحد بالشكل `Hari hujan: K` (تبقى الكلمات بالإندونيسية).",
    "N من 5 إلى 31؛ والهطول من 0 إلى 95."))

py("beban-terbesar", "mudah_sekali",
   "n = r.randint(2, 25)\nv = [r.randint(40, 130) * 100 + r.randint(0, 9) * 10 for _ in range(n)]\n"
   "if i == 1:\n    v.sort()\nif i == 2:\n    v.sort(reverse=True)\nprint(n)\nfor x in v:\n    print(x)\n",
   r'''
banyak = int(input())
terbesar = int(input())
for _ in range(banyak - 1):
    beban = int(input())
    if beban > terbesar:
        terbesar = beban
print(f"Terbesar: {terbesar} kg")
''',
   ("Beban Gandar Terbesar",
    "Jembatan timbang mencatat beban gandar setiap truk yang lewat. Temukan beban yang terbesar.\n\n"
    "Contoh: masukan `4`, lalu `8200`, `7900`, `10150`, `9600` menghasilkan `Terbesar: 10150 kg`.",
    "Baris pertama: banyaknya truk N. Lalu N baris, masing-masing satu beban gandar (kg, bilangan bulat).",
    "Satu baris berbentuk `Terbesar: X kg`.",
    "N 2 sampai 25; beban 4000 sampai 13090."),
   ("The Largest Axle Load",
    "A weighbridge records the axle load of every passing truck. Find the largest load.\n\n"
    "Example: the input `4`, then `8200`, `7900`, `10150`, `9600` gives `Terbesar: 10150 kg`.",
    "First line: the number of trucks N. Then N lines, each one axle load (kg, a whole number).",
    "One line of the form `Terbesar: X kg` (the word stays in Indonesian).",
    "N is 2 to 25; loads are 4000 to 13090."),
   ("أكبر حمل محوري",
    "يسجّل الميزان الجسري الحمل المحوري لكل شاحنة تمرّ. أوجد أكبر حمل.\n\n"
    "مثال: المدخل `4` ثم `8200` و`7900` و`10150` و`9600` يعطي `Terbesar: 10150 kg`.",
    "السطر الأول: عدد الشاحنات N. ثم N سطرًا، في كل سطر حمل محوري واحد (كغ، عدد صحيح).",
    "سطر واحد بالشكل `Terbesar: X kg` (تبقى الكلمة بالإندونيسية).",
    "N من 2 إلى 25؛ والأحمال من 4000 إلى 13090."))

py("anak-tangga", "mudah_sekali",
   "print(r.randint(2, 24))\nprint(r.randint(15, 20))\n",
   r'''
banyak = int(input())
tinggi_anak = int(input())
for nomor in range(1, banyak + 1):
    print(f"Anak tangga {nomor}: {nomor * tinggi_anak} cm")
''',
   ("Ketinggian Setiap Anak Tangga",
    "Sebuah tangga mempunyai N anak tangga yang tingginya sama. Cetak ketinggian permukaan setiap anak tangga dari lantai: anak tangga "
    "pertama setinggi satu tanjakan, yang kedua dua tanjakan, dan seterusnya.\n\n"
    "Contoh: `3` anak tangga dengan tanjakan `18` menghasilkan\n\n"
    "```\nAnak tangga 1: 18 cm\nAnak tangga 2: 36 cm\nAnak tangga 3: 54 cm\n```",
    "Dua baris: banyaknya anak tangga N, lalu tinggi satu tanjakan (cm). Keduanya bilangan bulat.",
    "N baris berbentuk `Anak tangga K: H cm`.",
    "N 2 sampai 24; tanjakan 15 sampai 20."),
   ("Height of Every Stair Step",
    "A staircase has N steps of equal height. Print the height of each tread above the floor: the first step is one riser high, the "
    "second two risers, and so on.\n\n"
    "Example: `3` steps with a riser of `18` give\n\n```\nAnak tangga 1: 18 cm\nAnak tangga 2: 36 cm\nAnak tangga 3: 54 cm\n```",
    "Two lines: the number of steps N, then the height of one riser (cm). Both are whole numbers.",
    "N lines of the form `Anak tangga K: H cm` (the words stay in Indonesian).",
    "N is 2 to 24; the riser is 15 to 20."),
   ("ارتفاع كل درجة في السلّم",
    "لسلّمٍ N درجة متساوية الارتفاع. اطبع ارتفاع سطح كل درجة عن الأرضية: الأولى بارتفاع قائمة واحدة، والثانية قائمتين، وهكذا.\n\n"
    "مثال: `3` درجات بقائمة `18` تعطي\n\n```\nAnak tangga 1: 18 cm\nAnak tangga 2: 36 cm\nAnak tangga 3: 54 cm\n```",
    "سطران: عدد الدرجات N ثم ارتفاع القائمة الواحدة (سم). كلاهما عدد صحيح.",
    "N سطرًا بالشكل `Anak tangga K: H cm` (تبقى الكلمات بالإندونيسية).",
    "N من 2 إلى 24؛ والقائمة من 15 إلى 20."))

py("muatan-sampai-nol", "mudah_sekali",
   "n = 0 if i == 6 else r.randint(1, 25)\nfor _ in range(n):\n    print(r.randint(5, 150) * 10)\nprint(0)\n",
   r'''
total = 0
muatan = int(input())
while muatan != 0:
    total = total + muatan
    muatan = int(input())
print(f"Total: {total} kg")
''',
   ("Menjumlah Muatan sampai Angka Nol",
    "Petugas gudang mengetik berat setiap kiriman material satu per satu dan menutup daftar dengan angka `0`. Banyaknya kiriman tidak "
    "diketahui sebelumnya. Jumlahkan semua berat sebelum angka 0 itu. Bila angka pertama sudah 0, totalnya 0.\n\n"
    "Contoh: masukan `1200`, `800`, `1500`, `0` menghasilkan `Total: 3500 kg`.",
    "Beberapa baris, masing-masing satu berat (kg, bilangan bulat positif), diakhiri satu baris berisi `0`.",
    "Satu baris berbentuk `Total: X kg`.",
    "Paling banyak 25 kiriman; setiap berat 50 sampai 1500."),
   ("Adding Loads Until a Zero",
    "A store clerk types the weight of each delivery one by one and closes the list with the number `0`. The number of deliveries is "
    "not known in advance. Add up all the weights before that 0. If the first number is already 0, the total is 0.\n\n"
    "Example: the input `1200`, `800`, `1500`, `0` gives `Total: 3500 kg`.",
    "Several lines, each one weight (kg, a positive whole number), ended by one line containing `0`.",
    "One line of the form `Total: X kg`.",
    "At most 25 deliveries; each weight is 50 to 1500."),
   ("جمع الحمولات حتى الصفر",
    "يكتب أمين المخزن وزن كل شحنة واحدة تلو الأخرى ويختم القائمة بالعدد `0`. عدد الشحنات غير معروف سلفًا. اجمع كل الأوزان التي "
    "تسبق ذلك الصفر. إذا كان العدد الأول 0 فالمجموع 0.\n\nمثال: المدخل `1200` و`800` و`1500` و`0` يعطي `Total: 3500 kg`.",
    "عدة أسطر، في كل سطر وزن (كغ، عدد صحيح موجب)، تنتهي بسطر فيه `0`.",
    "سطر واحد بالشكل `Total: X kg`.",
    "25 شحنة على الأكثر؛ وكل وزن من 50 إلى 1500."))

# ================================= MUDAH (8) =================================

py("rekap-hujan", "mudah",
   AMAN + "while True:\n    n = r.randint(3, 31)\n    v = [0 if r.random() < 0.3 else r.randint(1, 120) for _ in range(n)]\n"
   "    if aman(sum(v) / n, 1):\n        break\nprint(n)\nfor x in v:\n    print(x)\n",
   r'''
banyak_hari = int(input())
total = 0
maksimum = 0
for _ in range(banyak_hari):
    curah = int(input())
    total = total + curah
    if curah > maksimum:
        maksimum = curah
print(f"Total: {total} mm")
print(f"Rata-rata: {total / banyak_hari:.1f} mm")
print(f"Maksimum: {maksimum} mm")
''',
   ("Rekap Curah Hujan",
    "Dari catatan curah hujan harian, buat rekap: jumlah seluruhnya, rata-rata per hari (satu angka di belakang titik), dan curah "
    "hujan harian terbesar.\n\nContoh: masukan `4`, lalu `10`, `0`, `25`, `7` menghasilkan\n\n"
    "```\nTotal: 42 mm\nRata-rata: 10.5 mm\nMaksimum: 25 mm\n```",
    "Baris pertama: banyaknya hari N. Lalu N baris, masing-masing curah hujan satu hari (mm, bilangan bulat).",
    "Tiga baris: `Total: T mm`, `Rata-rata: R mm` (satu desimal), `Maksimum: M mm`.",
    "N 3 sampai 31; curah hujan 0 sampai 120."),
   ("Rainfall Summary",
    "From the daily rainfall record make a summary: the total, the average per day (one digit after the point) and the largest daily "
    "rainfall.\n\nExample: the input `4`, then `10`, `0`, `25`, `7` gives\n\n"
    "```\nTotal: 42 mm\nRata-rata: 10.5 mm\nMaksimum: 25 mm\n```",
    "First line: the number of days N. Then N lines, each the rainfall of one day (mm, a whole number).",
    "Three lines: `Total: T mm`, `Rata-rata: R mm` (one decimal), `Maksimum: M mm` (Indonesian words).",
    "N is 3 to 31; rainfall is 0 to 120."),
   ("ملخّص الهطول المطري",
    "من سجل الهطول اليومي أنشئ ملخّصًا: المجموع، والمتوسط اليومي (بمنزلة عشرية واحدة)، وأكبر هطول يومي.\n\n"
    "مثال: المدخل `4` ثم `10` و`0` و`25` و`7` يعطي\n\n```\nTotal: 42 mm\nRata-rata: 10.5 mm\nMaksimum: 25 mm\n```",
    "السطر الأول: عدد الأيام N. ثم N سطرًا، في كل سطر هطول يوم واحد (مم، عدد صحيح).",
    "ثلاثة أسطر: `Total: T mm` و`Rata-rata: R mm` (منزلة واحدة) و`Maksimum: M mm` (بالإندونيسية).",
    "N من 3 إلى 31؛ والهطول من 0 إلى 120."))

py("pompa-banjir", "mudah",
   "p = r.randint(40, 200)\na = r.randint(0, p - 10)\nk = r.randint(1, 40)\nv = (p - a) * k if i % 3 == 0 else (p - a) * k - r.randint(0, p - a - 1)\n"
   "print(max(1, v))\nprint(a)\nprint(p)\n",
   r'''
genangan = int(input())
hujan_per_jam = int(input())
pompa_per_jam = int(input())
jam = 0
while genangan > 0:
    genangan = genangan + hujan_per_jam - pompa_per_jam
    jam = jam + 1
print(f"Kering setelah {jam} jam")
''',
   ("Memompa Genangan",
    "Sebuah galian tergenang air. Setiap jam hujan menambah genangan dan pompa menguranginya; pompa selalu lebih kuat daripada hujan. "
    "Simulasikan jam demi jam: tambahkan air hujan, kurangi kapasitas pompa, dan berhenti begitu genangan habis (nol atau kurang). "
    "Cetak banyaknya jam yang diperlukan.\n\n"
    "Contoh: genangan `500`, hujan `20`, pompa `120` menghasilkan `Kering setelah 5 jam` (500, 400, 300, 200, 100, 0).",
    "Tiga baris: volume genangan awal (m³), tambahan hujan per jam (m³), kapasitas pompa per jam (m³). Semuanya bilangan bulat.",
    "Satu baris berbentuk `Kering setelah K jam`.",
    "Genangan 1 sampai 8000; pompa 40 sampai 200; hujan paling sedikit 10 lebih kecil dari pompa."),
   ("Pumping Out a Flooded Pit",
    "An excavation is flooded. Every hour rain adds to the water and a pump removes some; the pump is always stronger than the rain. "
    "Simulate hour by hour: add the rain, subtract the pump capacity, and stop as soon as the water is gone (zero or less). "
    "Print the number of hours needed.\n\n"
    "Example: water `500`, rain `20`, pump `120` give `Kering setelah 5 jam` (500, 400, 300, 200, 100, 0).",
    "Three lines: the initial water volume (m³), the rain added per hour (m³), the pump capacity per hour (m³). All whole numbers.",
    "One line of the form `Kering setelah K jam` (the words stay in Indonesian).",
    "Water 1 to 8000; pump 40 to 200; rain at least 10 less than the pump."),
   ("ضخّ المياه من حفرة مغمورة",
    "غُمرت حفرة بالماء. في كل ساعة يضيف المطر ماءً وتسحب المضخة جزءًا منه؛ والمضخة أقوى من المطر دائمًا. حاكِ العملية ساعة بساعة: "
    "أضف ماء المطر واطرح قدرة المضخة وتوقف حالما ينتهي الماء (صفر أو أقل). اطبع عدد الساعات اللازمة.\n\n"
    "مثال: ماء `500` ومطر `20` ومضخة `120` تعطي `Kering setelah 5 jam` (500، 400، 300، 200، 100، 0).",
    "ثلاثة أسطر: حجم الماء الابتدائي (م³)، إضافة المطر في الساعة (م³)، قدرة المضخة في الساعة (م³). كلها أعداد صحيحة.",
    "سطر واحد بالشكل `Kering setelah K jam` (تبقى الكلمات بالإندونيسية).",
    "الماء من 1 إلى 8000؛ المضخة من 40 إلى 200؛ والمطر أقل من المضخة بـ10 على الأقل."))

py("persen-lulus", "mudah",
   AMAN + "while True:\n    n = r.randint(3, 30)\n    b = r.choice([20, 25, 30])\n    v = [b + r.randint(-6, 8) for _ in range(n)]\n"
   "    if i == 5:\n        v = [b - r.randint(1, 6) for _ in range(n)]\n    k = sum(1 for x in v if x >= b)\n"
   "    if aman(k * 100 / n, 1):\n        break\nprint(n)\nprint(b)\nfor x in v:\n    print(x)\n",
   r'''
banyak = int(input())
batas = int(input())
lulus = 0
for _ in range(banyak):
    hasil = int(input())
    if hasil >= batas:
        lulus = lulus + 1
print(f"Lulus: {lulus} dari {banyak}")
print(f"Persentase: {lulus * 100 / banyak:.1f}%")
''',
   ("Persentase Benda Uji yang Lulus",
    "Sejumlah benda uji diperiksa terhadap satu batas kuat tekan. Benda uji lulus bila hasilnya tidak kurang dari batas. Hitung "
    "banyaknya yang lulus dan persentasenya (satu angka di belakang titik).\n\n"
    "Contoh: masukan `4`, batas `25`, lalu `26`, `24`, `25`, `30` menghasilkan\n\n```\nLulus: 3 dari 4\nPersentase: 75.0%\n```",
    "Baris pertama: banyaknya benda uji N. Baris kedua: batas (MPa). Lalu N baris hasil uji (MPa). Semuanya bilangan bulat.",
    "Dua baris: `Lulus: K dari N`, lalu `Persentase: P%` dengan P satu angka di belakang titik.",
    "N 3 sampai 30; batas 20, 25, atau 30; hasil 14 sampai 38."),
   ("Percentage of Specimens That Pass",
    "A number of specimens are checked against one strength limit. A specimen passes when its result is not below the limit. Count "
    "those that pass and their percentage (one digit after the point).\n\n"
    "Example: the input `4`, limit `25`, then `26`, `24`, `25`, `30` gives\n\n```\nLulus: 3 dari 4\nPersentase: 75.0%\n```",
    "First line: the number of specimens N. Second line: the limit (MPa). Then N lines of results (MPa). All whole numbers.",
    "Two lines: `Lulus: K dari N`, then `Persentase: P%` with P having one digit after the point (Indonesian words).",
    "N is 3 to 30; the limit is 20, 25 or 30; results are 14 to 38."),
   ("نسبة العيّنات الناجحة",
    "تُفحص عدة عيّنات مقابل حدّ واحد لمقاومة الضغط. تنجح العيّنة إذا لم تقلّ نتيجتها عن الحد. احسب عدد الناجحة ونسبتها المئوية "
    "(بمنزلة عشرية واحدة).\n\nمثال: المدخل `4` والحد `25` ثم `26` و`24` و`25` و`30` يعطي\n\n"
    "```\nLulus: 3 dari 4\nPersentase: 75.0%\n```",
    "السطر الأول: عدد العيّنات N. السطر الثاني: الحد (MPa). ثم N سطرًا للنتائج (MPa). كلها أعداد صحيحة.",
    "سطران: `Lulus: K dari N` ثم `Persentase: P%` حيث P بمنزلة عشرية واحدة (بالإندونيسية).",
    "N من 3 إلى 30؛ الحد 20 أو 25 أو 30؛ والنتائج من 14 إلى 38."))

py("tabel-kecepatan", "mudah",
   "s = r.choice([5, 10, 20])\na = r.randint(2, 12) * 5\nb = a + s * r.randint(0, 8) + (r.randint(1, s - 1) if i % 2 else 0)\n"
   "print(a)\nprint(b)\nprint(s)\n",
   r'''
awal = int(input())
akhir = int(input())
langkah = int(input())
kecepatan = awal
while kecepatan <= akhir:
    print(f"{kecepatan} km/jam = {kecepatan * 1000 / 3600:.2f} m/detik")
    kecepatan = kecepatan + langkah
''',
   ("Tabel Kecepatan Rencana",
    "Perencana jalan memerlukan tabel kecepatan dalam m/detik. Mulai dari kecepatan awal, naikkan dengan langkah tetap selama belum "
    "melewati kecepatan akhir, dan cetak setiap nilainya dalam km/jam dan m/detik (dua angka di belakang titik). "
    "1 km/jam = 1000 / 3600 m/detik.\n\n"
    "Contoh: awal `40`, akhir `60`, langkah `10` menghasilkan\n\n"
    "```\n40 km/jam = 11.11 m/detik\n50 km/jam = 13.89 m/detik\n60 km/jam = 16.67 m/detik\n```",
    "Tiga baris: kecepatan awal, kecepatan akhir, langkah (km/jam, bilangan bulat).",
    "Satu baris untuk setiap kecepatan, berbentuk `V km/jam = X m/detik`.",
    "Awal 10 sampai 60; langkah 5, 10, atau 20; paling banyak 9 baris."),
   ("Design Speed Table",
    "A road designer needs a table of speeds in m/s. Start at the first speed, increase by a fixed step as long as the last speed is "
    "not passed, and print each value in km/h and m/s (two digits after the point). 1 km/h = 1000 / 3600 m/s.\n\n"
    "Example: first `40`, last `60`, step `10` give\n\n"
    "```\n40 km/jam = 11.11 m/detik\n50 km/jam = 13.89 m/detik\n60 km/jam = 16.67 m/detik\n```",
    "Three lines: the first speed, the last speed, the step (km/h, whole numbers).",
    "One line for each speed, of the form `V km/jam = X m/detik` (Indonesian unit words).",
    "First speed 10 to 60; step 5, 10 or 20; at most 9 lines."),
   ("جدول السرعة التصميمية",
    "يحتاج مصمّم الطرق إلى جدول للسرعات بوحدة م/ث. ابدأ من السرعة الأولى وزِد بخطوة ثابتة ما دامت السرعة الأخيرة لم تُتجاوز، "
    "واطبع كل قيمة بوحدة كم/س وبوحدة م/ث (بمنزلتين عشريتين). 1 كم/س = 1000 / 3600 م/ث.\n\n"
    "مثال: الأولى `40` والأخيرة `60` والخطوة `10` تعطي\n\n"
    "```\n40 km/jam = 11.11 m/detik\n50 km/jam = 13.89 m/detik\n60 km/jam = 16.67 m/detik\n```",
    "ثلاثة أسطر: السرعة الأولى، السرعة الأخيرة، الخطوة (كم/س، أعداد صحيحة).",
    "سطر لكل سرعة بالشكل `V km/jam = X m/detik` (كلمات الوحدات بالإندونيسية).",
    "السرعة الأولى من 10 إلى 60؛ الخطوة 5 أو 10 أو 20؛ 9 أسطر على الأكثر."))

py("jam-puncak", "mudah",
   "n = r.randint(3, 24)\nv = [r.randint(100, 900) for _ in range(n)]\nif i % 3 == 1:\n    k = r.randrange(n - 1)\n    m = max(v)\n"
   "    v[k] = m\n    v[r.randrange(k + 1, n)] = m\nprint(n)\nfor x in v:\n    print(x)\n",
   r'''
banyak_jam = int(input())
jam_puncak = 0
terbanyak = -1
for jam in range(1, banyak_jam + 1):
    kendaraan = int(input())
    if kendaraan > terbanyak:
        terbanyak = kendaraan
        jam_puncak = jam
print(f"Jam ke-{jam_puncak}: {terbanyak} kendaraan")
''',
   ("Jam Puncak Lalu Lintas",
    "Survei mencatat jumlah kendaraan setiap jam secara berurutan. Tentukan jam ke berapa lalu lintas paling ramai dan berapa "
    "jumlahnya. Bila ada beberapa jam dengan jumlah terbanyak yang sama, ambil jam yang paling awal.\n\n"
    "Contoh: masukan `4`, lalu `310`, `455`, `455`, `290` menghasilkan `Jam ke-2: 455 kendaraan`.",
    "Baris pertama: banyaknya jam N. Lalu N baris, jumlah kendaraan jam ke-1 sampai jam ke-N (bilangan bulat).",
    "Satu baris berbentuk `Jam ke-K: C kendaraan`.",
    "N 3 sampai 24; jumlah kendaraan 100 sampai 900."),
   ("Peak Traffic Hour",
    "A survey records the number of vehicles of each hour in order. Find which hour is the busiest and its count. If several hours "
    "share the largest count, take the earliest.\n\n"
    "Example: the input `4`, then `310`, `455`, `455`, `290` gives `Jam ke-2: 455 kendaraan`.",
    "First line: the number of hours N. Then N lines, the vehicle counts of hour 1 to hour N (whole numbers).",
    "One line of the form `Jam ke-K: C kendaraan` (Indonesian words).",
    "N is 3 to 24; counts are 100 to 900."),
   ("ساعة الذروة المرورية",
    "يسجّل مسحٌ عدد المركبات في كل ساعة بالترتيب. حدِّد أي ساعة هي الأكثر ازدحامًا وعدد مركباتها. إذا تساوت عدة ساعات في العدد "
    "الأكبر فخذ أبكرها.\n\nمثال: المدخل `4` ثم `310` و`455` و`455` و`290` يعطي `Jam ke-2: 455 kendaraan`.",
    "السطر الأول: عدد الساعات N. ثم N سطرًا، عدد المركبات من الساعة 1 إلى الساعة N (أعداد صحيحة).",
    "سطر واحد بالشكل `Jam ke-K: C kendaraan` (بالإندونيسية).",
    "N من 3 إلى 24؛ والأعداد من 100 إلى 900."))

py("stok-semen", "mudah",
   "n = r.randint(3, 20)\nv = [r.randint(5, 40) for _ in range(n)]\ns = sum(v) + r.randint(0, 60)\n"
   "m = r.randint(5, 30) if i % 3 == 0 else r.randint(40, max(41, s // 2))\nprint(s)\nprint(m)\nprint(n)\nfor x in v:\n    print(x)\n",
   r'''
stok = int(input())
batas_minimum = int(input())
banyak_hari = int(input())
hari_pesan = 0
for hari in range(1, banyak_hari + 1):
    pemakaian = int(input())
    stok = stok - pemakaian
    if stok < batas_minimum:
        hari_pesan = hari
        break
if hari_pesan == 0:
    print("STOK AMAN")
else:
    print(f"Pesan ulang pada hari {hari_pesan}")
print(f"Sisa: {stok} sak")
''',
   ("Kapan Memesan Semen Lagi",
    "Gudang proyek mempunyai stok semen awal dan rencana pemakaian per hari. Kurangi stok hari demi hari. Begitu stok turun di bawah "
    "batas minimum, semen harus dipesan ulang pada hari itu dan perhitungan berhenti (pemakaian hari-hari berikutnya tidak dihitung). "
    "Bila sampai hari terakhir stok tidak pernah di bawah batas, stok aman. Cetak juga sisa stok saat perhitungan berhenti.\n\n"
    "Contoh: stok `100`, batas `30`, `4` hari dengan pemakaian `20`, `25`, `30`, `10` menghasilkan\n\n"
    "```\nPesan ulang pada hari 3\nSisa: 25 sak\n```\n\n"
    "Dengan batas `10` hasilnya `STOK AMAN` lalu `Sisa: 15 sak`.",
    "Baris pertama: stok awal (sak). Baris kedua: batas minimum (sak). Baris ketiga: banyaknya hari N. Lalu N baris pemakaian per hari. "
    "Semuanya bilangan bulat.",
    "Baris pertama `Pesan ulang pada hari K` atau `STOK AMAN`. Baris kedua `Sisa: S sak`.",
    "N 3 sampai 20; pemakaian per hari 5 sampai 40; jumlah seluruh pemakaian tidak melebihi stok awal."),
   ("When to Reorder Cement",
    "A site store has an opening stock of cement and a planned use per day. Subtract the use day by day. As soon as the stock falls "
    "below the minimum level, cement must be reordered on that day and the calculation stops (the use of later days is not counted). "
    "If the stock never falls below the level through the last day, the stock is safe. Also print the stock left when the calculation "
    "stops.\n\nExample: stock `100`, level `30`, `4` days with uses `20`, `25`, `30`, `10` give\n\n"
    "```\nPesan ulang pada hari 3\nSisa: 25 sak\n```\n\nWith a level of `10` the result is `STOK AMAN` then `Sisa: 15 sak`.",
    "First line: the opening stock (bags). Second line: the minimum level (bags). Third line: the number of days N. Then N lines of "
    "daily use. All whole numbers.",
    "First line `Pesan ulang pada hari K` or `STOK AMAN`. Second line `Sisa: S sak` (Indonesian words).",
    "N is 3 to 20; daily use is 5 to 40; the total use never exceeds the opening stock."),
   ("متى يُعاد طلب الإسمنت",
    "في مخزن المشروع رصيد ابتدائي من الإسمنت وخطة استهلاك يومية. اطرح الاستهلاك يومًا بيوم. حالما ينزل الرصيد دون الحد الأدنى يجب "
    "إعادة الطلب في ذلك اليوم ويتوقف الحساب (لا يُحسب استهلاك الأيام التالية). إذا لم ينزل الرصيد دون الحد حتى اليوم الأخير فالرصيد "
    "آمن. اطبع كذلك الرصيد المتبقي عند توقف الحساب.\n\n"
    "مثال: رصيد `100` وحدّ `30` و`4` أيام باستهلاك `20` و`25` و`30` و`10` تعطي\n\n"
    "```\nPesan ulang pada hari 3\nSisa: 25 sak\n```\n\nومع حدّ `10` تكون النتيجة `STOK AMAN` ثم `Sisa: 15 sak`.",
    "السطر الأول: الرصيد الابتدائي (كيس). السطر الثاني: الحد الأدنى (كيس). السطر الثالث: عدد الأيام N. ثم N سطرًا للاستهلاك اليومي. "
    "كلها أعداد صحيحة.",
    "السطر الأول `Pesan ulang pada hari K` أو `STOK AMAN`. السطر الثاني `Sisa: S sak` (بالإندونيسية).",
    "N من 3 إلى 20؛ الاستهلاك اليومي من 5 إلى 40؛ ومجموع الاستهلاك لا يتجاوز الرصيد الابتدائي."))

py("pertumbuhan-lhr", "mudah",
   "print(r.randint(200, 30000))\nprint(r.randint(1, 9))\nprint(r.randint(1, 20))\n",
   r'''
lhr = int(input())
persen = int(input())
tahun = int(input())
for _ in range(tahun):
    lhr = lhr * (100 + persen) // 100
print(f"LHR tahun ke-{tahun}: {lhr}")
''',
   ("Pertumbuhan Lalu Lintas",
    "Lalu lintas harian (LHR) sebuah ruas jalan tumbuh dengan persentase tetap setiap tahun. Setiap tahun LHR baru adalah LHR lama "
    "ditambah pertumbuhannya, lalu dibulatkan ke bawah menjadi bilangan bulat sebelum dipakai untuk tahun berikutnya: "
    "`lhr = lhr * (100 + persen) // 100`. Hitung LHR setelah sejumlah tahun.\n\n"
    "Contoh: LHR `1000`, pertumbuhan `5`, selama `3` tahun menghasilkan `LHR tahun ke-3: 1157` (1050, 1102, 1157).",
    "Tiga baris: LHR sekarang, persen pertumbuhan per tahun, banyaknya tahun. Semuanya bilangan bulat.",
    "Satu baris berbentuk `LHR tahun ke-T: X`.",
    "LHR 200 sampai 30000; pertumbuhan 1 sampai 9; tahun 1 sampai 20."),
   ("Traffic Growth",
    "The daily traffic (LHR) of a road section grows by a fixed percentage every year. Each year the new LHR is the old LHR plus its "
    "growth, rounded down to a whole number before it is used for the next year: `lhr = lhr * (100 + persen) // 100`. Compute the LHR "
    "after a number of years.\n\nExample: LHR `1000`, growth `5`, for `3` years gives `LHR tahun ke-3: 1157` (1050, 1102, 1157).",
    "Three lines: the present LHR, the growth percent per year, the number of years. All whole numbers.",
    "One line of the form `LHR tahun ke-T: X` (Indonesian words).",
    "LHR 200 to 30000; growth 1 to 9; years 1 to 20."),
   ("نموّ المرور",
    "ينمو المرور اليومي (LHR) لمقطع طريق بنسبة ثابتة كل سنة. في كل سنة يكون LHR الجديد هو القديم مضافًا إليه نموّه، ثم يُقرَّب إلى "
    "الأسفل عددًا صحيحًا قبل استعماله للسنة التالية: `lhr = lhr * (100 + persen) // 100`. احسب LHR بعد عدد من السنين.\n\n"
    "مثال: LHR `1000` ونموّ `5` لمدة `3` سنوات يعطي `LHR tahun ke-3: 1157` (1050، 1102، 1157).",
    "ثلاثة أسطر: LHR الحالي، نسبة النمو السنوية، عدد السنين. كلها أعداد صحيحة.",
    "سطر واحد بالشكل `LHR tahun ke-T: X` (بالإندونيسية).",
    "LHR من 200 إلى 30000؛ النمو من 1 إلى 9؛ السنون من 1 إلى 20."))

py("naik-turun", "mudah",
   "n = r.randint(2, 25)\ne = r.randint(500, 3000)\nprint(n)\nfor k in range(n):\n    print(e)\n"
   "    e += 0 if r.random() < 0.15 else (r.randint(1, 80) if i == 2 else -r.randint(1, 80) if i == 3 else r.randint(-80, 80))\n",
   r'''
banyak_titik = int(input())
sebelumnya = int(input())
naik = 0
turun = 0
for _ in range(banyak_titik - 1):
    elevasi = int(input())
    if elevasi > sebelumnya:
        naik = naik + elevasi - sebelumnya
    else:
        turun = turun + sebelumnya - elevasi
    sebelumnya = elevasi
print(f"Naik: {naik} cm")
print(f"Turun: {turun} cm")
''',
   ("Total Tanjakan dan Turunan",
    "Elevasi sumbu jalan diukur di titik-titik yang berurutan. Hitung jumlah seluruh kenaikan dan jumlah seluruh penurunan di antara "
    "titik-titik yang bersebelahan.\n\n"
    "Contoh: masukan `5`, lalu `100`, `130`, `120`, `160`, `150` menghasilkan\n\n```\nNaik: 70 cm\nTurun: 20 cm\n```\n\n"
    "(naik 30 dan 40; turun 10 dan 10).",
    "Baris pertama: banyaknya titik N. Lalu N baris elevasi berurutan (cm, bilangan bulat).",
    "Dua baris: `Naik: A cm`, lalu `Turun: B cm`.",
    "N 2 sampai 25; beda elevasi dua titik bersebelahan paling banyak 80."),
   ("Total Rise and Fall",
    "The elevation of a road centre line is measured at consecutive points. Compute the sum of all rises and the sum of all falls "
    "between neighbouring points.\n\n"
    "Example: the input `5`, then `100`, `130`, `120`, `160`, `150` gives\n\n```\nNaik: 70 cm\nTurun: 20 cm\n```\n\n"
    "(rises of 30 and 40; falls of 10 and 10).",
    "First line: the number of points N. Then N lines of consecutive elevations (cm, whole numbers).",
    "Two lines: `Naik: A cm` (rise), then `Turun: B cm` (fall); Indonesian words.",
    "N is 2 to 25; neighbouring elevations differ by at most 80."),
   ("مجموع الصعود والهبوط",
    "قيس منسوب محور الطريق عند نقاط متتالية. احسب مجموع كل الارتفاعات ومجموع كل الانخفاضات بين النقاط المتجاورة.\n\n"
    "مثال: المدخل `5` ثم `100` و`130` و`120` و`160` و`150` يعطي\n\n```\nNaik: 70 cm\nTurun: 20 cm\n```\n\n"
    "(صعود 30 و40؛ هبوط 10 و10).",
    "السطر الأول: عدد النقاط N. ثم N سطرًا للمناسيب المتتالية (سم، أعداد صحيحة).",
    "سطران: `Naik: A cm` (صعود) ثم `Turun: B cm` (هبوط)؛ بالإندونيسية.",
    "N من 2 إلى 25؛ والفرق بين نقطتين متجاورتين لا يزيد على 80."))

# ============================== MUDAH SEDANG (6) ==============================

py("kemarau-terpanjang", "mudah_sedang",
   "n = r.randint(5, 40)\np = [0.5, 0.2, 0.8, 0.0, 1.0][i % 5]\nv = [0 if r.random() < p else r.randint(1, 90) for _ in range(n)]\n"
   "print(n)\nfor x in v:\n    print(x)\n",
   r'''
banyak_hari = int(input())
berturut = 0
terpanjang = 0
for _ in range(banyak_hari):
    curah = int(input())
    if curah == 0:
        berturut = berturut + 1
        if berturut > terpanjang:
            terpanjang = berturut
    else:
        berturut = 0
print(f"Kering terpanjang: {terpanjang} hari")
''',
   ("Deret Hari Kering Terpanjang",
    "Untuk merencanakan penyiraman timbunan, pelaksana ingin tahu deret hari kering terpanjang: berapa hari berturut-turut paling lama "
    "curah hujannya 0. Bila tidak ada hari kering, jawabannya 0.\n\n"
    "Contoh: masukan `8`, lalu `0`, `0`, `5`, `0`, `0`, `0`, `12`, `0` menghasilkan `Kering terpanjang: 3 hari`.",
    "Baris pertama: banyaknya hari N. Lalu N baris curah hujan harian (mm, bilangan bulat; 0 berarti kering).",
    "Satu baris berbentuk `Kering terpanjang: K hari`.",
    "N 5 sampai 40; curah hujan 0 sampai 90."),
   ("The Longest Dry Spell",
    "To plan the watering of a fill, the contractor wants the longest dry spell: the largest number of consecutive days with a "
    "rainfall of 0. If there is no dry day, the answer is 0.\n\n"
    "Example: the input `8`, then `0`, `0`, `5`, `0`, `0`, `0`, `12`, `0` gives `Kering terpanjang: 3 hari`.",
    "First line: the number of days N. Then N lines of daily rainfall (mm, whole numbers; 0 means dry).",
    "One line of the form `Kering terpanjang: K hari` (Indonesian words).",
    "N is 5 to 40; rainfall is 0 to 90."),
   ("أطول فترة جفاف",
    "لتخطيط رشّ الردم يريد المنفّذ معرفة أطول فترة جفاف: أكبر عدد من الأيام المتتالية التي هطولها 0. إذا لم يوجد يوم جاف فالجواب 0.\n\n"
    "مثال: المدخل `8` ثم `0` و`0` و`5` و`0` و`0` و`0` و`12` و`0` يعطي `Kering terpanjang: 3 hari`.",
    "السطر الأول: عدد الأيام N. ثم N سطرًا للهطول اليومي (مم، أعداد صحيحة؛ 0 يعني جافًّا).",
    "سطر واحد بالشكل `Kering terpanjang: K hari` (بالإندونيسية).",
    "N من 5 إلى 40؛ والهطول من 0 إلى 90."))

py("luas-penampang-sungai", "mudah_sedang",
   "n = r.randint(3, 20)\nv = [r.randint(1, 45) * 10 for _ in range(n)]\nif i % 2 == 0:\n    v[0] = 0\n    v[-1] = 0\n"
   "print(r.randint(1, 5))\nprint(n)\nfor x in v:\n    print(x)\n",
   r'''
jarak = int(input())
banyak_titik = int(input())
sebelumnya = int(input())
jumlah_cm = 0
for _ in range(banyak_titik - 1):
    kedalaman = int(input())
    jumlah_cm = jumlah_cm + (sebelumnya + kedalaman) / 2
    sebelumnya = kedalaman
luas = jarak * jumlah_cm / 100
print(f"Luas: {luas:.2f} m2")
''',
   ("Luas Penampang Sungai",
    "Kedalaman sungai diukur di beberapa titik yang berjarak sama dari tepi ke tepi. Luas penampang basah dihitung dengan aturan "
    "trapesium: untuk setiap dua titik bersebelahan, luas pias = jarak × (kedalaman kiri + kedalaman kanan) / 2; jumlahkan semua pias. "
    "Kedalaman diberikan dalam sentimeter dan jarak dalam meter; cetak luas dalam m² dengan dua angka di belakang titik.\n\n"
    "Contoh: jarak `2`, `4` titik dengan kedalaman `0`, `120`, `180`, `0` menghasilkan `Luas: 6.00 m2` "
    "(pias 1.20 + 3.00 + 1.80 m²).",
    "Baris pertama: jarak antartitik (m). Baris kedua: banyaknya titik N. Lalu N baris kedalaman (cm). Semuanya bilangan bulat.",
    "Satu baris berbentuk `Luas: A m2`, dengan A dua angka di belakang titik.",
    "Jarak 1 sampai 5; N 3 sampai 20; kedalaman 0 sampai 450, kelipatan 10."),
   ("Cross-Section Area of a River",
    "The depth of a river is measured at several equally spaced points from bank to bank. The wetted area is computed with the "
    "trapezoid rule: for each pair of neighbouring points, strip area = spacing × (left depth + right depth) / 2; add all strips. "
    "Depths are given in centimetres and the spacing in metres; print the area in m² with two digits after the point.\n\n"
    "Example: spacing `2`, `4` points with depths `0`, `120`, `180`, `0` give `Luas: 6.00 m2` (strips 1.20 + 3.00 + 1.80 m²).",
    "First line: the spacing between points (m). Second line: the number of points N. Then N lines of depths (cm). All whole numbers.",
    "One line of the form `Luas: A m2`, with A having two digits after the point (the word stays in Indonesian).",
    "Spacing 1 to 5; N is 3 to 20; depths 0 to 450, multiples of 10."),
   ("مساحة المقطع العرضي لنهر",
    "قيس عمق النهر عند عدة نقاط متساوية التباعد من ضفة إلى ضفة. تُحسب المساحة المبتلّة بقاعدة شبه المنحرف: لكل نقطتين متجاورتين "
    "مساحة الشريحة = التباعد × (العمق الأيسر + العمق الأيمن) / 2؛ ثم تُجمع الشرائح. الأعماق بالسنتيمتر والتباعد بالمتر؛ اطبع المساحة "
    "بالمتر المربع بمنزلتين عشريتين.\n\n"
    "مثال: تباعد `2` و`4` نقاط بأعماق `0` و`120` و`180` و`0` تعطي `Luas: 6.00 m2` (الشرائح 1.20 + 3.00 + 1.80 م²).",
    "السطر الأول: التباعد بين النقاط (م). السطر الثاني: عدد النقاط N. ثم N سطرًا للأعماق (سم). كلها أعداد صحيحة.",
    "سطر واحد بالشكل `Luas: A m2` حيث A بمنزلتين عشريتين (تبقى الكلمة بالإندونيسية).",
    "التباعد من 1 إلى 5؛ N من 3 إلى 20؛ الأعماق من 0 إلى 450 ومن مضاعفات 10."))

py("truk-bersamaan", "mudah_sedang",
   "a = r.randint(4, 30)\nb = r.randint(4, 30)\nif i == 2:\n    b = a\nt = [r.randint(30, 480), a * b - 1, a * b][i % 3]\n"
   "print(a)\nprint(b)\nprint(max(10, t))\n",
   r'''
selang_a = int(input())
selang_b = int(input())
batas = int(input())
jumlah = 0
for menit in range(1, batas + 1):
    if menit % selang_a == 0 and menit % selang_b == 0:
        print(menit)
        jumlah = jumlah + 1
print(f"Jumlah: {jumlah}")
''',
   ("Dua Truk Tiba Bersamaan",
    "Dua truk mixer berangkat bersama pada menit 0 dan bolak-balik ke lokasi pengecoran. Truk A tiba setiap beberapa menit sekali, "
    "truk B setiap beberapa menit yang lain. Bila keduanya tiba pada menit yang sama, pompa beton harus antre. Cetak semua menit "
    "(setelah menit 0, sampai batas waktu) saat keduanya tiba bersamaan, satu per baris, lalu banyaknya.\n\n"
    "Contoh: selang A `12`, selang B `18`, batas `100` menghasilkan\n\n```\n36\n72\nJumlah: 2\n```\n\n"
    "Bila tidak pernah bersamaan, keluarannya hanya `Jumlah: 0`.",
    "Tiga baris: selang kedatangan truk A, selang kedatangan truk B, batas waktu (menit, bilangan bulat).",
    "Satu baris untuk setiap menit kedatangan bersama, dari yang terkecil, lalu satu baris `Jumlah: K`.",
    "Selang 4 sampai 30; batas waktu 10 sampai 900."),
   ("Two Trucks Arriving Together",
    "Two mixer trucks leave together at minute 0 and shuttle to the pour. Truck A arrives every so many minutes, truck B every other "
    "number of minutes. When both arrive in the same minute, the concrete pump has a queue. Print every minute (after minute 0, up to "
    "the time limit) at which both arrive together, one per line, then how many there are.\n\n"
    "Example: interval A `12`, interval B `18`, limit `100` give\n\n```\n36\n72\nJumlah: 2\n```\n\n"
    "If they never arrive together, the output is only `Jumlah: 0`.",
    "Three lines: the arrival interval of truck A, the arrival interval of truck B, the time limit (minutes, whole numbers).",
    "One line for each common arrival minute, smallest first, then one line `Jumlah: K` (the word stays in Indonesian).",
    "Intervals 4 to 30; time limit 10 to 900."),
   ("وصول شاحنتين معًا",
    "تنطلق شاحنتان خلّاطتان معًا في الدقيقة 0 وتتردّدان على موقع الصبّ. تصل الشاحنة A كل عدد من الدقائق والشاحنة B كل عدد آخر. "
    "إذا وصلتا في الدقيقة نفسها اصطفّتا عند مضخة الخرسانة. اطبع كل دقيقة (بعد الدقيقة 0 وحتى المهلة) تصلان فيها معًا، كلٌّ في سطر، "
    "ثم عددها.\n\nمثال: فاصل A `12` وفاصل B `18` والمهلة `100` تعطي\n\n```\n36\n72\nJumlah: 2\n```\n\n"
    "إذا لم تصلا معًا أبدًا فالمخرج `Jumlah: 0` فقط.",
    "ثلاثة أسطر: فاصل وصول الشاحنة A، فاصل وصول الشاحنة B، المهلة (دقائق، أعداد صحيحة).",
    "سطر لكل دقيقة وصول مشترك من الأصغر، ثم سطر `Jumlah: K` (تبقى الكلمة بالإندونيسية).",
    "الفواصل من 4 إلى 30؛ والمهلة من 10 إلى 900."))

py("penyusutan-alat", "mudah_sedang",
   "h = r.randint(200, 5000)\np = r.randint(5, 40)\nb = r.randint(h // 10, h - 1)\nprint(h)\nprint(p)\nprint(b)\n",
   r'''
nilai = int(input())
persen_susut = int(input())
batas = int(input())
tahun = 0
while nilai >= batas:
    tahun = tahun + 1
    nilai = nilai * (100 - persen_susut) // 100
    print(f"Tahun {tahun}: {nilai}")
''',
   ("Penyusutan Nilai Alat Berat",
    "Nilai sebuah alat berat menyusut dengan persentase tetap setiap tahun. Setiap tahun nilai baru dibulatkan ke bawah: "
    "`nilai = nilai * (100 - persen) // 100`. Cetak nilai di akhir setiap tahun, dan berhenti setelah mencetak nilai pertama yang "
    "berada di bawah batas (saat itu alat dijual).\n\n"
    "Contoh: nilai `1000`, susut `20`, batas `500` menghasilkan\n\n"
    "```\nTahun 1: 800\nTahun 2: 640\nTahun 3: 512\nTahun 4: 409\n```",
    "Tiga baris: nilai awal (juta rupiah), persen penyusutan per tahun, batas nilai jual (juta rupiah). Semuanya bilangan bulat.",
    "Satu baris untuk setiap tahun, berbentuk `Tahun K: N`; baris terakhir adalah nilai pertama yang di bawah batas.",
    "Nilai awal 200 sampai 5000; penyusutan 5 sampai 40; batas lebih kecil dari nilai awal."),
   ("Depreciation of Heavy Equipment",
    "The value of a machine depreciates by a fixed percentage every year. Each year the new value is rounded down: "
    "`nilai = nilai * (100 - persen) // 100`. Print the value at the end of each year, and stop after printing the first value that "
    "is below the limit (the machine is then sold).\n\n"
    "Example: value `1000`, depreciation `20`, limit `500` give\n\n"
    "```\nTahun 1: 800\nTahun 2: 640\nTahun 3: 512\nTahun 4: 409\n```",
    "Three lines: the initial value (million rupiah), the depreciation percent per year, the selling limit (million rupiah). "
    "All whole numbers.",
    "One line for each year, of the form `Tahun K: N`; the last line is the first value below the limit (the word stays in Indonesian).",
    "Initial value 200 to 5000; depreciation 5 to 40; the limit is smaller than the initial value."),
   ("اهتلاك قيمة آلة ثقيلة",
    "تهتلك قيمة آلة ثقيلة بنسبة ثابتة كل سنة. في كل سنة تُقرَّب القيمة الجديدة إلى الأسفل: `nilai = nilai * (100 - persen) // 100`. "
    "اطبع القيمة في نهاية كل سنة وتوقف بعد طباعة أول قيمة دون الحد (عندها تُباع الآلة).\n\n"
    "مثال: قيمة `1000` واهتلاك `20` وحدّ `500` تعطي\n\n```\nTahun 1: 800\nTahun 2: 640\nTahun 3: 512\nTahun 4: 409\n```",
    "ثلاثة أسطر: القيمة الابتدائية (مليون روبية)، نسبة الاهتلاك السنوية، حدّ البيع (مليون روبية). كلها أعداد صحيحة.",
    "سطر لكل سنة بالشكل `Tahun K: N`؛ والسطر الأخير هو أول قيمة دون الحد (تبقى الكلمة بالإندونيسية).",
    "القيمة الابتدائية من 200 إلى 5000؛ الاهتلاك من 5 إلى 40؛ والحد أصغر من القيمة الابتدائية."))

py("grid-tiang", "mudah_sedang",
   "print(r.randint(1, 6))\nprint(r.randint(1, 8))\nprint(r.choice([90, 100, 120, 150, 180]))\nprint(r.choice([90, 100, 120, 150, 200]))\n",
   r'''
baris = int(input())
kolom = int(input())
jarak_x = int(input())
jarak_y = int(input())
for b in range(baris):
    for k in range(kolom):
        print(f"{k * jarak_x} {b * jarak_y}")
print(f"Jumlah: {baris * kolom}")
''',
   ("Koordinat Tiang Pancang",
    "Tiang pancang sebuah pondasi disusun dalam beberapa baris dan kolom dengan jarak tetap. Tiang pertama berada di koordinat (0, 0). "
    "Cetak koordinat x dan y setiap tiang, baris demi baris: semua tiang di baris pertama (y = 0) dari kiri ke kanan, lalu baris "
    "berikutnya. Akhiri dengan banyaknya tiang.\n\n"
    "Contoh: `2` baris, `3` kolom, jarak x `150`, jarak y `200` menghasilkan\n\n"
    "```\n0 0\n150 0\n300 0\n0 200\n150 200\n300 200\nJumlah: 6\n```",
    "Empat baris: banyaknya baris, banyaknya kolom, jarak arah x (cm), jarak arah y (cm). Semuanya bilangan bulat.",
    "Satu baris `X Y` untuk setiap tiang, lalu satu baris `Jumlah: N`.",
    "Baris 1 sampai 6; kolom 1 sampai 8; jarak 90 sampai 200."),
   ("Pile Coordinates",
    "The piles of a foundation are arranged in rows and columns at fixed spacings. The first pile is at coordinate (0, 0). Print the x "
    "and y coordinate of every pile, row by row: all piles of the first row (y = 0) from left to right, then the next row. End with "
    "the number of piles.\n\n"
    "Example: `2` rows, `3` columns, x spacing `150`, y spacing `200` give\n\n"
    "```\n0 0\n150 0\n300 0\n0 200\n150 200\n300 200\nJumlah: 6\n```",
    "Four lines: the number of rows, the number of columns, the spacing along x (cm), the spacing along y (cm). All whole numbers.",
    "One line `X Y` for each pile, then one line `Jumlah: N` (the word stays in Indonesian).",
    "Rows 1 to 6; columns 1 to 8; spacings 90 to 200."),
   ("إحداثيات الخوازيق",
    "تُرتَّب خوازيق أساسٍ في صفوف وأعمدة بتباعد ثابت. الخازوق الأول عند الإحداثي (0، 0). اطبع الإحداثيين x و y لكل خازوق صفًّا صفًّا: "
    "كل خوازيق الصف الأول (y = 0) من اليسار إلى اليمين ثم الصف التالي. اختم بعدد الخوازيق.\n\n"
    "مثال: `2` صفّان و`3` أعمدة وتباعد x `150` وتباعد y `200` تعطي\n\n"
    "```\n0 0\n150 0\n300 0\n0 200\n150 200\n300 200\nJumlah: 6\n```",
    "أربعة أسطر: عدد الصفوف، عدد الأعمدة، التباعد باتجاه x (سم)، التباعد باتجاه y (سم). كلها أعداد صحيحة.",
    "سطر `X Y` لكل خازوق ثم سطر `Jumlah: N` (تبقى الكلمة بالإندونيسية).",
    "الصفوف من 1 إلى 6؛ الأعمدة من 1 إلى 8؛ والتباعد من 90 إلى 200."))

py("saring-sensor", "mudah_sedang",
   AMAN + "while True:\n    n = r.randint(3, 30)\n    p = [0.25, 0.0, 1.0, 0.6][i % 4]\n"
   "    v = [-1 if r.random() < p else r.randint(20, 400) for _ in range(n)]\n    s = [x for x in v if x >= 0]\n"
   "    if not s or aman(sum(s) / len(s), 1):\n        break\nprint(n)\nfor x in v:\n    print(x)\n",
   r'''
banyak = int(input())
sah = 0
jumlah = 0
for _ in range(banyak):
    bacaan = int(input())
    if bacaan == -1:
        continue
    sah = sah + 1
    jumlah = jumlah + bacaan
print(f"Sah: {sah}")
if sah == 0:
    print("TIDAK ADA DATA")
else:
    print(f"Rata-rata: {jumlah / sah:.1f} cm")
''',
   ("Menyaring Bacaan Sensor Muka Air",
    "Sensor muka air sungai mengirim bacaan tinggi air dalam sentimeter. Bila sensor gagal membaca, ia mengirim `-1`. Lewati bacaan "
    "yang gagal, lalu cetak banyaknya bacaan yang sah dan rata-ratanya (satu angka di belakang titik). Bila tidak ada bacaan yang sah, "
    "cetak `TIDAK ADA DATA` sebagai baris kedua.\n\n"
    "Contoh: masukan `5`, lalu `120`, `-1`, `130`, `-1`, `125` menghasilkan\n\n```\nSah: 3\nRata-rata: 125.0 cm\n```",
    "Baris pertama: banyaknya bacaan N. Lalu N baris bacaan (cm, bilangan bulat; `-1` berarti gagal).",
    "Baris pertama `Sah: K`. Baris kedua `Rata-rata: R cm` (satu desimal) atau `TIDAK ADA DATA`.",
    "N 3 sampai 30; bacaan sah 20 sampai 400."),
   ("Filtering Water-Level Sensor Readings",
    "A river water-level sensor sends readings of the water height in centimetres. When the sensor fails to read, it sends `-1`. Skip "
    "the failed readings, then print the number of valid readings and their average (one digit after the point). If there is no valid "
    "reading, print `TIDAK ADA DATA` as the second line.\n\n"
    "Example: the input `5`, then `120`, `-1`, `130`, `-1`, `125` gives\n\n```\nSah: 3\nRata-rata: 125.0 cm\n```",
    "First line: the number of readings N. Then N lines of readings (cm, whole numbers; `-1` means failed).",
    "First line `Sah: K`. Second line `Rata-rata: R cm` (one decimal) or `TIDAK ADA DATA` (Indonesian words).",
    "N is 3 to 30; valid readings are 20 to 400."),
   ("تنقية قراءات حسّاس منسوب الماء",
    "يرسل حسّاس منسوب ماء النهر قراءات ارتفاع الماء بالسنتيمتر. إذا فشل في القراءة أرسل `-1`. تجاوز القراءات الفاشلة ثم اطبع عدد "
    "القراءات الصحيحة ومتوسطها (بمنزلة عشرية واحدة). إذا لم توجد قراءة صحيحة فاطبع `TIDAK ADA DATA` سطرًا ثانيًا.\n\n"
    "مثال: المدخل `5` ثم `120` و`-1` و`130` و`-1` و`125` يعطي\n\n```\nSah: 3\nRata-rata: 125.0 cm\n```",
    "السطر الأول: عدد القراءات N. ثم N سطرًا للقراءات (سم، أعداد صحيحة؛ `-1` تعني الفشل).",
    "السطر الأول `Sah: K`. السطر الثاني `Rata-rata: R cm` (منزلة واحدة) أو `TIDAK ADA DATA` (بالإندونيسية).",
    "N من 3 إلى 30؛ والقراءات الصحيحة من 20 إلى 400."))

# ================================ SEDANG (6) ================================

py("denah-kolom", "sedang",
   "s = r.randint(2, 6)\np = s * r.randint(1, 5) + (r.randint(1, s - 1) if i % 3 == 1 else 0)\n"
   "l = s * r.randint(1, 4) + (r.randint(1, s - 1) if i % 3 == 2 else 0)\nprint(p)\nprint(l)\nprint(s)\n",
   r'''
panjang = int(input())
lebar = int(input())
jarak = int(input())
kolom = 0
for y in range(lebar + 1):
    for x in range(panjang + 1):
        if x % jarak == 0 and y % jarak == 0:
            print("K", end="")
            kolom = kolom + 1
        else:
            print(".", end="")
    print()
print(f"Kolom: {kolom}")
''',
   ("Denah Titik Kolom",
    "Denah sebuah bangunan digambar sebagai kisi titik berjarak 1 m: titik (x, y) dengan x dari 0 sampai panjang dan y dari 0 sampai "
    "lebar. Kolom berdiri di setiap titik yang x dan y-nya sama-sama kelipatan jarak kolom (0 termasuk kelipatan). Gambar denah itu "
    "baris demi baris, mulai dari y = 0: huruf `K` untuk titik kolom dan `.` untuk titik lain, tanpa spasi. Lalu cetak banyaknya "
    "kolom.\n\nContoh: panjang `6`, lebar `3`, jarak `3` menghasilkan\n\n"
    "```\nK..K..K\n.......\n.......\nK..K..K\nKolom: 6\n```\n\n"
    "Untuk mencetak tanpa pindah baris pakai `print(\"K\", end=\"\")`; `print()` tanpa isi memindahkan baris.",
    "Tiga baris: panjang (m), lebar (m), jarak kolom (m). Semuanya bilangan bulat.",
    "Lebar + 1 baris gambar, masing-masing panjang + 1 karakter, lalu satu baris `Kolom: N`.",
    "Jarak 2 sampai 6; panjang dan lebar 2 sampai 35."),
   ("Column Layout Plan",
    "The plan of a building is drawn as a grid of points 1 m apart: point (x, y) with x from 0 to the length and y from 0 to the "
    "width. A column stands at every point whose x and y are both multiples of the column spacing (0 counts as a multiple). Draw the "
    "plan row by row, starting at y = 0: the letter `K` for a column point and `.` for any other point, without spaces. Then print "
    "the number of columns.\n\nExample: length `6`, width `3`, spacing `3` give\n\n"
    "```\nK..K..K\n.......\n.......\nK..K..K\nKolom: 6\n```\n\n"
    "To print without moving to a new line use `print(\"K\", end=\"\")`; an empty `print()` moves to a new line.",
    "Three lines: the length (m), the width (m), the column spacing (m). All whole numbers.",
    "Width + 1 lines of drawing, each of length + 1 characters, then one line `Kolom: N` (the word stays in Indonesian).",
    "Spacing 2 to 6; length and width 2 to 35."),
   ("مخطط مواضع الأعمدة",
    "يُرسم مخطط مبنى شبكةً من نقاط تتباعد 1 م: النقطة (x، y) حيث x من 0 إلى الطول و y من 0 إلى العرض. يقوم عمود عند كل نقطة يكون "
    "فيها x و y كلاهما من مضاعفات تباعد الأعمدة (والصفر من المضاعفات). ارسم المخطط سطرًا سطرًا ابتداءً من y = 0: الحرف `K` لنقطة "
    "العمود و`.` لغيرها، دون مسافات. ثم اطبع عدد الأعمدة.\n\nمثال: طول `6` وعرض `3` وتباعد `3` تعطي\n\n"
    "```\nK..K..K\n.......\n.......\nK..K..K\nKolom: 6\n```\n\n"
    "للطباعة دون الانتقال إلى سطر جديد استعمل `print(\"K\", end=\"\")`؛ و`print()` فارغة تنقل إلى سطر جديد.",
    "ثلاثة أسطر: الطول (م)، العرض (م)، تباعد الأعمدة (م). كلها أعداد صحيحة.",
    "عدد (العرض + 1) من أسطر الرسم، في كل سطر (الطول + 1) حرفًا، ثم سطر `Kolom: N` (تبقى الكلمة بالإندونيسية).",
    "التباعد من 2 إلى 6؛ والطول والعرض من 2 إلى 35."))

py("cicilan-alat", "sedang",
   "p = r.randint(200, 9000)\nb = r.randint(1, 3)\na = p // r.randint(2, 30) + p * b // 100 + r.randint(5, 50)\nif i == 1:\n    a = p + p * b // 100\n"
   "if i == 2:\n    a = p + p * b // 100 + 40\nprint(p)\nprint(b)\nprint(a)\n",
   r'''
sisa = int(input())
bunga_persen = int(input())
angsuran = int(input())
bulan = 0
terakhir = 0
while sisa > 0:
    bulan = bulan + 1
    tagihan = sisa + sisa * bunga_persen // 100
    if tagihan <= angsuran:
        terakhir = tagihan
        sisa = 0
    else:
        sisa = tagihan - angsuran
print(f"Lunas dalam {bulan} bulan")
print(f"Angsuran terakhir: {terakhir}")
''',
   ("Cicilan Pembelian Alat",
    "Kontraktor membeli alat dengan pinjaman. Setiap bulan terjadi hal berikut, berurutan:\n\n"
    "1. bunga bulan itu ditambahkan ke sisa pinjaman: `tagihan = sisa + sisa * bunga // 100` (bunga dibulatkan ke bawah);\n"
    "2. bila tagihan tidak lebih dari angsuran tetap, kontraktor membayar tepat sebesar tagihan itu dan pinjaman lunas;\n"
    "3. bila lebih, ia membayar angsuran tetap dan sisanya dibawa ke bulan berikutnya.\n\n"
    "Hitung berapa bulan sampai lunas dan besar pembayaran terakhir.\n\n"
    "Contoh: pinjaman `1000`, bunga `2`, angsuran `300` menghasilkan\n\n```\nLunas dalam 4 bulan\nAngsuran terakhir: 144\n```\n\n"
    "(sisa setelah tiga bulan: 720, 434, 142; tagihan bulan keempat 142 + 2 = 144).",
    "Tiga baris: pinjaman (ribu rupiah), bunga per bulan (persen), angsuran tetap per bulan (ribu rupiah). Semuanya bilangan bulat.",
    "Dua baris: `Lunas dalam K bulan`, lalu `Angsuran terakhir: X`.",
    "Pinjaman 200 sampai 9000; bunga 1 sampai 3; angsuran selalu lebih besar dari bunga bulan pertama."),
   ("Equipment Loan Instalments",
    "A contractor buys a machine with a loan. Each month the following happens, in order:\n\n"
    "1. the interest of that month is added to the balance: `tagihan = sisa + sisa * bunga // 100` (interest rounded down);\n"
    "2. if this amount is not more than the fixed instalment, the contractor pays exactly that amount and the loan is settled;\n"
    "3. if it is more, the fixed instalment is paid and the rest is carried to the next month.\n\n"
    "Compute how many months it takes to settle the loan and the size of the last payment.\n\n"
    "Example: loan `1000`, interest `2`, instalment `300` give\n\n```\nLunas dalam 4 bulan\nAngsuran terakhir: 144\n```\n\n"
    "(balances after three months: 720, 434, 142; the amount due in month four is 142 + 2 = 144).",
    "Three lines: the loan (thousand rupiah), the interest per month (percent), the fixed monthly instalment (thousand rupiah). "
    "All whole numbers.",
    "Two lines: `Lunas dalam K bulan`, then `Angsuran terakhir: X` (Indonesian words).",
    "Loan 200 to 9000; interest 1 to 3; the instalment is always larger than the interest of the first month."),
   ("أقساط شراء آلة",
    "يشتري مقاول آلة بقرض. في كل شهر يحدث ما يلي بالترتيب:\n\n"
    "1. تُضاف فائدة الشهر إلى الرصيد: `tagihan = sisa + sisa * bunga // 100` (تُقرَّب الفائدة إلى الأسفل)؛\n"
    "2. إذا لم يزد هذا المبلغ على القسط الثابت دفع المقاول ذلك المبلغ تمامًا وسُدِّد القرض؛\n"
    "3. إذا زاد دفع القسط الثابت ورُحِّل الباقي إلى الشهر التالي.\n\n"
    "احسب عدد الأشهر حتى السداد ومقدار الدفعة الأخيرة.\n\n"
    "مثال: قرض `1000` وفائدة `2` وقسط `300` تعطي\n\n```\nLunas dalam 4 bulan\nAngsuran terakhir: 144\n```\n\n"
    "(الأرصدة بعد ثلاثة أشهر: 720، 434، 142؛ والمستحق في الشهر الرابع 142 + 2 = 144).",
    "ثلاثة أسطر: القرض (ألف روبية)، الفائدة الشهرية (بالمئة)، القسط الشهري الثابت (ألف روبية). كلها أعداد صحيحة.",
    "سطران: `Lunas dalam K bulan` ثم `Angsuran terakhir: X` (بالإندونيسية).",
    "القرض من 200 إلى 9000؛ الفائدة من 1 إلى 3؛ والقسط أكبر دائمًا من فائدة الشهر الأول."))

py("hujan-tiga-hari", "sedang",
   "n = 3 if i == 4 else r.randint(4, 31)\nv = [0 if r.random() < 0.3 else r.randint(1, 130) for _ in range(n)]\n"
   "if i == 5 and n >= 7:\n    v[1:4] = [50, 60, 70]\n    v[4:7] = [70, 60, 50]\n    v = [min(x, 60) if k > 6 else x for k, x in enumerate(v)]\n"
   "print(n)\nfor x in v:\n    print(x)\n",
   r'''
banyak_hari = int(input())
dua_hari_lalu = int(input())
kemarin = int(input())
terbesar = -1
mulai = 0
for hari in range(3, banyak_hari + 1):
    hari_ini = int(input())
    jumlah = dua_hari_lalu + kemarin + hari_ini
    if jumlah > terbesar:
        terbesar = jumlah
        mulai = hari - 2
    dua_hari_lalu = kemarin
    kemarin = hari_ini
print(f"Maksimum 3 hari: {terbesar} mm")
print(f"Mulai hari ke-{mulai}")
''',
   ("Hujan Tiga Harian Maksimum",
    "Perencanaan drainase memakai hujan tiga harian maksimum: jumlah curah hujan terbesar dari tiga hari yang berurutan. Dari catatan "
    "harian, cari jumlah itu dan hari pertama dari tiga hari tersebut. Bila ada beberapa deret tiga hari dengan jumlah terbesar yang "
    "sama, ambil yang paling awal. Pertemuan ini belum memakai list: simpan dua hari terakhir dalam variabel.\n\n"
    "Contoh: masukan `6`, lalu `10`, `0`, `30`, `45`, `5`, `20` menghasilkan\n\n"
    "```\nMaksimum 3 hari: 80 mm\nMulai hari ke-3\n```\n\n(jumlah tiga harian: 40, 75, 80, 70).",
    "Baris pertama: banyaknya hari N. Lalu N baris curah hujan harian (mm, bilangan bulat).",
    "Dua baris: `Maksimum 3 hari: X mm`, lalu `Mulai hari ke-K`.",
    "N 3 sampai 31; curah hujan 0 sampai 130."),
   ("Maximum Three-Day Rainfall",
    "Drainage design uses the maximum three-day rainfall: the largest total of three consecutive days. From the daily record, find "
    "that total and the first day of those three. If several runs of three days share the largest total, take the earliest. This "
    "meeting does not use lists yet: keep the last two days in variables.\n\n"
    "Example: the input `6`, then `10`, `0`, `30`, `45`, `5`, `20` gives\n\n"
    "```\nMaksimum 3 hari: 80 mm\nMulai hari ke-3\n```\n\n(three-day totals: 40, 75, 80, 70).",
    "First line: the number of days N. Then N lines of daily rainfall (mm, whole numbers).",
    "Two lines: `Maksimum 3 hari: X mm`, then `Mulai hari ke-K` (Indonesian words).",
    "N is 3 to 31; rainfall is 0 to 130."),
   ("أقصى هطول لثلاثة أيام",
    "يُستعمل في تصميم التصريف أقصى هطول لثلاثة أيام: أكبر مجموع لهطول ثلاثة أيام متتالية. من السجل اليومي أوجد ذلك المجموع واليوم "
    "الأول من تلك الثلاثة. إذا تساوت عدة فترات في المجموع الأكبر فخذ أبكرها. لم تُدرَس القوائم بعد: احتفظ باليومين الأخيرين في "
    "متغيرين.\n\nمثال: المدخل `6` ثم `10` و`0` و`30` و`45` و`5` و`20` يعطي\n\n"
    "```\nMaksimum 3 hari: 80 mm\nMulai hari ke-3\n```\n\n(مجاميع الأيام الثلاثة: 40، 75، 80، 70).",
    "السطر الأول: عدد الأيام N. ثم N سطرًا للهطول اليومي (مم، أعداد صحيحة).",
    "سطران: `Maksimum 3 hari: X mm` ثم `Mulai hari ke-K` (بالإندونيسية).",
    "N من 3 إلى 31؛ والهطول من 0 إلى 130."))

py("pilih-tulangan", "sedang",
   AMAN + "import math\nwhile True:\n    a = [r.randint(100, 3900), r.randint(3930, 4500), r.randint(100, 160)][i % 3 if i < 6 else 0]\n"
   "    pilih = [n * math.pi * d * d / 4 for d in range(10, 26, 3) for n in range(2, 9) if n * math.pi * d * d / 4 >= a]\n"
   "    if not pilih or aman(min(pilih), 1):\n        break\nprint(a)\n",
   r'''
import math

luas_perlu = int(input())
luas_terbaik = 0.0
banyak_terbaik = 0
diameter_terbaik = 0
for diameter in range(10, 26, 3):
    for banyak in range(2, 9):
        luas = banyak * math.pi * diameter * diameter / 4
        if luas >= luas_perlu:
            if banyak_terbaik == 0 or luas < luas_terbaik:
                luas_terbaik = luas
                banyak_terbaik = banyak
                diameter_terbaik = diameter
if banyak_terbaik == 0:
    print("TIDAK ADA")
else:
    print(f"{banyak_terbaik} D{diameter_terbaik}")
    print(f"As = {luas_terbaik:.1f} mm2")
''',
   ("Memilih Tulangan Balok",
    "Perhitungan memberi luas tulangan perlu (mm²). Di gudang tersedia baja berdiameter 10, 13, 16, 19, 22, dan 25 mm, dan dalam satu "
    "lapis balok boleh dipasang 2 sampai 8 batang berdiameter sama. Luas n batang berdiameter d adalah n × π × d² / 4 (pakai `math.pi`).\n\n"
    "Periksa semua pasangan diameter dan banyak batang, lalu pilih pasangan yang luasnya paling kecil di antara yang tidak kurang "
    "dari luas perlu. Tidak ada dua pasangan yang luasnya sama. Diameter 10, 13, …, 25 dapat dihasilkan dengan `range(10, 26, 3)`.\n\n"
    "Contoh: luas perlu `750` menghasilkan\n\n```\n2 D22\nAs = 760.3 mm2\n```\n\n"
    "Bila 8 batang D25 pun masih kurang, keluarannya satu baris `TIDAK ADA`.",
    "Satu baris: luas tulangan perlu (mm², bilangan bulat).",
    "Baris pertama `N Dd` (banyak batang, spasi, huruf D diikuti diameter). Baris kedua `As = L mm2` dengan L satu angka di belakang "
    "titik. Atau satu baris `TIDAK ADA`.",
    "Luas perlu 100 sampai 4500."),
   ("Choosing Beam Reinforcement",
    "A calculation gives the required steel area (mm²). The store has bars of diameter 10, 13, 16, 19, 22 and 25 mm, and one layer "
    "of a beam may hold 2 to 8 bars of the same diameter. The area of n bars of diameter d is n × π × d² / 4 (use `math.pi`).\n\n"
    "Check every pair of diameter and number of bars, then choose the pair with the smallest area among those not below the required "
    "area. No two pairs have the same area. The diameters 10, 13, …, 25 can be produced with `range(10, 26, 3)`.\n\n"
    "Example: a required area of `750` gives\n\n```\n2 D22\nAs = 760.3 mm2\n```\n\n"
    "If even 8 bars D25 are not enough, the output is the single line `TIDAK ADA`.",
    "One line: the required steel area (mm², a whole number).",
    "First line `N Dd` (number of bars, a space, the letter D followed by the diameter). Second line `As = L mm2` with L having one "
    "digit after the point. Or the single line `TIDAK ADA`.",
    "The required area is 100 to 4500."),
   ("اختيار تسليح الجائز",
    "يعطي الحساب مساحة التسليح المطلوبة (مم²). في المخزن قضبان بأقطار 10 و13 و16 و19 و22 و25 مم، ويجوز في الطبقة الواحدة من الجائز "
    "وضع 2 إلى 8 قضبان من القطر نفسه. مساحة n قضيبًا قطرها d هي n × π × d² / 4 (استعمل `math.pi`).\n\n"
    "افحص كل أزواج القطر وعدد القضبان، ثم اختر الزوج ذا المساحة الصغرى من بين ما لا يقل عن المساحة المطلوبة. لا يتساوى زوجان في "
    "المساحة. يمكن توليد الأقطار 10، 13، …، 25 بـ `range(10, 26, 3)`.\n\n"
    "مثال: مساحة مطلوبة `750` تعطي\n\n```\n2 D22\nAs = 760.3 mm2\n```\n\n"
    "إذا لم تكفِ حتى 8 قضبان D25 فالمخرج سطر واحد `TIDAK ADA`.",
    "سطر واحد: مساحة التسليح المطلوبة (مم²، عدد صحيح).",
    "السطر الأول `N Dd` (عدد القضبان ثم مسافة ثم الحرف D متبوعًا بالقطر). السطر الثاني `As = L mm2` حيث L بمنزلة عشرية واحدة. "
    "أو سطر واحد `TIDAK ADA`.",
    "المساحة المطلوبة من 100 إلى 4500."))

py("potong-besi-berurutan", "sedang",
   "n = r.randint(1, 30)\nv = [r.randint(4, 120) * 10 for _ in range(n)]\nif i == 3:\n    v = [600] * n\nif i == 4:\n    v = [1200] * n\n"
   "print(n)\nfor x in v:\n    print(x)\n",
   r'''
banyak = int(input())
batang = 0
sisa_batang = 0
total_potongan = 0
for _ in range(banyak):
    potongan = int(input())
    total_potongan = total_potongan + potongan
    if potongan > sisa_batang:
        batang = batang + 1
        sisa_batang = 1200
    sisa_batang = sisa_batang - potongan
print(f"Batang: {batang}")
print(f"Sisa total: {batang * 1200 - total_potongan} cm")
''',
   ("Memotong Besi Berurutan",
    "Tukang besi memotong potongan-potongan tulangan sesuai urutan daftar dari batang standar 1200 cm. Ia bekerja dengan aturan "
    "sederhana: potongan berikutnya diambil dari batang yang sedang dipakai bila sisanya masih cukup; bila tidak cukup, sisa batang "
    "itu disingkirkan dan batang baru dibuka. Ia tidak pernah kembali ke batang lama. Hitung banyaknya batang yang dibuka dan "
    "jumlah seluruh sisa (panjang semua batang yang dibuka dikurangi jumlah semua potongan).\n\n"
    "Contoh: masukan `5`, lalu `500`, `400`, `400`, `700`, `300` menghasilkan\n\n```\nBatang: 3\nSisa total: 1300 cm\n```\n\n"
    "(batang 1: 500 + 400; batang 2: 400 + 700; batang 3: 300).",
    "Baris pertama: banyaknya potongan N. Lalu N baris panjang potongan sesuai urutan (cm, bilangan bulat).",
    "Dua baris: `Batang: K`, lalu `Sisa total: S cm`.",
    "N 1 sampai 30; panjang potongan 40 sampai 1200."),
   ("Cutting Bars in Order",
    "A steel fixer cuts reinforcement pieces in the order of a list from standard bars of 1200 cm. He follows a simple rule: the next "
    "piece is taken from the bar in use if what is left of it is enough; if not, the rest of that bar is put aside and a new bar is "
    "opened. He never goes back to an old bar. Compute the number of bars opened and the total waste (the length of all opened bars "
    "minus the sum of all pieces).\n\n"
    "Example: the input `5`, then `500`, `400`, `400`, `700`, `300` gives\n\n```\nBatang: 3\nSisa total: 1300 cm\n```\n\n"
    "(bar 1: 500 + 400; bar 2: 400 + 700; bar 3: 300).",
    "First line: the number of pieces N. Then N lines of piece lengths in order (cm, whole numbers).",
    "Two lines: `Batang: K`, then `Sisa total: S cm` (Indonesian words).",
    "N is 1 to 30; piece lengths are 40 to 1200."),
   ("قصّ الحديد بالترتيب",
    "يقصّ الحدّاد قطع التسليح بترتيب القائمة من قضبان قياسية طولها 1200 سم. يتبع قاعدة بسيطة: تؤخذ القطعة التالية من القضيب "
    "المستعمل إذا كان الباقي منه كافيًا؛ وإلا يُنحّى باقي ذلك القضيب ويُفتح قضيب جديد. لا يعود إلى قضيب قديم أبدًا. احسب عدد القضبان "
    "المفتوحة ومجموع الهدر (طول كل القضبان المفتوحة ناقص مجموع القطع).\n\n"
    "مثال: المدخل `5` ثم `500` و`400` و`400` و`700` و`300` يعطي\n\n```\nBatang: 3\nSisa total: 1300 cm\n```\n\n"
    "(القضيب 1: 500 + 400؛ القضيب 2: 400 + 700؛ القضيب 3: 300).",
    "السطر الأول: عدد القطع N. ثم N سطرًا لأطوال القطع بالترتيب (سم، أعداد صحيحة).",
    "سطران: `Batang: K` ثم `Sisa total: S cm` (بالإندونيسية).",
    "N من 1 إلى 30؛ وأطوال القطع من 40 إلى 1200."))

py("ubin-terbesar", "sedang",
   "g = r.choice([5, 10, 15, 20, 25, 30, 40, 60])\na = r.randint(2, 40)\nb = r.randint(2, 40)\nif i == 2:\n    b = a\n"
   "if i == 3:\n    b = a * r.randint(2, 4)\nprint(g * a)\nprint(g * b)\n",
   r'''
panjang = int(input())
lebar = int(input())
a = panjang
b = lebar
while b != 0:
    sisa = a % b
    a = b
    b = sisa
print(f"Sisi ubin: {a} cm")
print(f"Jumlah: {(panjang // a) * (lebar // a)}")
''',
   ("Ubin Persegi Terbesar",
    "Sebuah lantai berbentuk persegi panjang akan ditutup ubin persegi yang semuanya utuh, tanpa dipotong. Cari sisi ubin terbesar "
    "yang memungkinkan itu, yaitu faktor persekutuan terbesar dari panjang dan lebar lantai, lalu hitung banyaknya ubin.\n\n"
    "Faktor persekutuan terbesar dapat dicari dengan perulangan: selama b bukan 0, ganti (a, b) dengan (b, sisa a dibagi b); "
    "jawabannya nilai a yang terakhir.\n\n"
    "Contoh: lantai `360` × `240` menghasilkan\n\n```\nSisi ubin: 120 cm\nJumlah: 6\n```",
    "Dua baris: panjang lantai, lalu lebar lantai (cm, bilangan bulat).",
    "Dua baris: `Sisi ubin: S cm`, lalu `Jumlah: N`.",
    "Panjang dan lebar 10 sampai 2400."),
   ("The Largest Square Tile",
    "A rectangular floor is to be covered with whole square tiles, none of them cut. Find the largest tile side that allows this, "
    "which is the greatest common divisor of the floor length and width, then compute the number of tiles.\n\n"
    "The greatest common divisor can be found with a loop: while b is not 0, replace (a, b) by (b, the remainder of a divided by b); "
    "the answer is the last value of a.\n\n"
    "Example: a floor of `360` × `240` gives\n\n```\nSisi ubin: 120 cm\nJumlah: 6\n```",
    "Two lines: the floor length, then the floor width (cm, whole numbers).",
    "Two lines: `Sisi ubin: S cm`, then `Jumlah: N` (Indonesian words).",
    "Length and width are 10 to 2400."),
   ("أكبر بلاطة مربعة",
    "تُغطّى أرضية مستطيلة ببلاط مربع كامل لا يُقصّ منه شيء. أوجد أكبر ضلع بلاطة يتيح ذلك، وهو القاسم المشترك الأكبر لطول الأرضية "
    "وعرضها، ثم احسب عدد البلاطات.\n\n"
    "يمكن إيجاد القاسم المشترك الأكبر بحلقة: ما دام b لا يساوي 0 استبدل (a، b) بـ (b، باقي قسمة a على b)؛ والجواب آخر قيمة لـ a.\n\n"
    "مثال: أرضية `360` × `240` تعطي\n\n```\nSisi ubin: 120 cm\nJumlah: 6\n```",
    "سطران: طول الأرضية ثم عرضها (سم، عددان صحيحان).",
    "سطران: `Sisi ubin: S cm` ثم `Jumlah: N` (بالإندونيسية).",
    "الطول والعرض من 10 إلى 2400."))

# ================================= SULIT (4) =================================

py("routing-waduk", "sulit",
   "c = r.randint(80, 600)\ns = r.randint(0, c)\nrel = r.randint(5, 60)\nn = r.randint(3, 30)\n"
   "m = [3 * rel, rel // 2, 2 * rel, 0][i % 4]\nprint(c)\nprint(s)\nprint(rel)\nprint(n)\nfor _ in range(n):\n    print(r.randint(0, m) if m else 0)\n",
   r'''
kapasitas = int(input())
tampungan = int(input())
kebutuhan = int(input())
banyak_jam = int(input())
total_limpasan = 0
jam_melimpas = 0
jam_kekurangan = 0
for _ in range(banyak_jam):
    masuk = int(input())
    tampungan = tampungan + masuk
    if tampungan >= kebutuhan:
        tampungan = tampungan - kebutuhan
    else:
        tampungan = 0
        jam_kekurangan = jam_kekurangan + 1
    if tampungan > kapasitas:
        total_limpasan = total_limpasan + tampungan - kapasitas
        tampungan = kapasitas
        jam_melimpas = jam_melimpas + 1
print(f"Tampungan akhir: {tampungan}")
print(f"Total limpasan: {total_limpasan}")
print(f"Jam melimpas: {jam_melimpas}")
print(f"Jam kekurangan: {jam_kekurangan}")
''',
   ("Simulasi Tampungan Embung",
    "Sebuah embung mempunyai kapasitas tertentu dan tampungan awal. Setiap jam terjadi hal berikut, berurutan:\n\n"
    "1. air masuk jam itu ditambahkan ke tampungan;\n"
    "2. embung melepas air untuk kebutuhan irigasi: bila tampungan tidak kurang dari kebutuhan, kebutuhan dipenuhi seluruhnya; bila "
    "kurang, semua air yang ada dilepas (tampungan menjadi 0) dan jam itu dihitung sebagai jam kekurangan;\n"
    "3. bila setelah itu tampungan melebihi kapasitas, kelebihannya melimpas lewat pelimpah, tampungan menjadi sama dengan kapasitas, "
    "dan jam itu dihitung sebagai jam melimpas.\n\n"
    "Cetak tampungan akhir, jumlah seluruh limpasan, banyaknya jam melimpas, dan banyaknya jam kekurangan.\n\n"
    "Contoh: kapasitas `100`, tampungan awal `60`, kebutuhan `20`, `4` jam dengan air masuk `50`, `80`, `0`, `5` menghasilkan\n\n"
    "```\nTampungan akhir: 65\nTotal limpasan: 50\nJam melimpas: 1\nJam kekurangan: 0\n```\n\n"
    "(tampungan di akhir tiap jam: 90, 100 dengan limpasan 50, 80, 65).",
    "Baris pertama: kapasitas. Baris kedua: tampungan awal. Baris ketiga: kebutuhan per jam. Baris keempat: banyaknya jam N. "
    "Lalu N baris air masuk per jam. Semuanya bilangan bulat dalam ribu m³.",
    "Empat baris: `Tampungan akhir: S`, `Total limpasan: L`, `Jam melimpas: A`, `Jam kekurangan: B`.",
    "Kapasitas 80 sampai 600; tampungan awal tidak melebihi kapasitas; kebutuhan 5 sampai 60; N 3 sampai 30; air masuk 0 sampai 180."),
   ("Simulating a Small Reservoir",
    "A small reservoir has a given capacity and an initial storage. Every hour the following happens, in order:\n\n"
    "1. the inflow of that hour is added to the storage;\n"
    "2. the reservoir releases water for irrigation: if the storage is not less than the demand, the demand is met in full; if it is "
    "less, all the water is released (the storage becomes 0) and the hour counts as a shortage hour;\n"
    "3. if the storage then exceeds the capacity, the excess spills over the spillway, the storage becomes equal to the capacity, "
    "and the hour counts as a spill hour.\n\n"
    "Print the final storage, the total spill, the number of spill hours and the number of shortage hours.\n\n"
    "Example: capacity `100`, initial storage `60`, demand `20`, `4` hours with inflows `50`, `80`, `0`, `5` give\n\n"
    "```\nTampungan akhir: 65\nTotal limpasan: 50\nJam melimpas: 1\nJam kekurangan: 0\n```\n\n"
    "(storage at the end of each hour: 90, 100 with a spill of 50, 80, 65).",
    "First line: the capacity. Second line: the initial storage. Third line: the demand per hour. Fourth line: the number of hours N. "
    "Then N lines of inflow per hour. All whole numbers in thousand m³.",
    "Four lines: `Tampungan akhir: S`, `Total limpasan: L`, `Jam melimpas: A`, `Jam kekurangan: B` (Indonesian words).",
    "Capacity 80 to 600; the initial storage does not exceed the capacity; demand 5 to 60; N is 3 to 30; inflow 0 to 180."),
   ("محاكاة خزّان صغير",
    "لخزّان صغير سعة محددة ومخزون ابتدائي. في كل ساعة يحدث ما يلي بالترتيب:\n\n"
    "1. يُضاف الوارد في تلك الساعة إلى المخزون؛\n"
    "2. يُطلق الخزّان الماء لحاجة الري: إذا لم يقلّ المخزون عن الحاجة لُبّيت كاملة؛ وإذا قلّ أُطلق كل الماء الموجود (يصير المخزون 0) "
    "وعُدّت الساعة ساعة نقص؛\n"
    "3. إذا تجاوز المخزون بعد ذلك السعة فاض الزائد عبر المفيض وصار المخزون مساويًا للسعة وعُدّت الساعة ساعة فيضان.\n\n"
    "اطبع المخزون النهائي ومجموع الفيضان وعدد ساعات الفيضان وعدد ساعات النقص.\n\n"
    "مثال: سعة `100` ومخزون ابتدائي `60` وحاجة `20` و`4` ساعات بوارد `50` و`80` و`0` و`5` تعطي\n\n"
    "```\nTampungan akhir: 65\nTotal limpasan: 50\nJam melimpas: 1\nJam kekurangan: 0\n```\n\n"
    "(المخزون في نهاية كل ساعة: 90، ثم 100 مع فيضان 50، ثم 80، ثم 65).",
    "السطر الأول: السعة. الثاني: المخزون الابتدائي. الثالث: الحاجة في الساعة. الرابع: عدد الساعات N. ثم N سطرًا للوارد في كل ساعة. "
    "كلها أعداد صحيحة بألف م³.",
    "أربعة أسطر: `Tampungan akhir: S` و`Total limpasan: L` و`Jam melimpas: A` و`Jam kekurangan: B` (بالإندونيسية).",
    "السعة من 80 إلى 600؛ المخزون الابتدائي لا يتجاوز السعة؛ الحاجة من 5 إلى 60؛ N من 3 إلى 30؛ والوارد من 0 إلى 180."))

py("kedalaman-biseksi", "sulit",
   AMAN + "def debit(h, b, n, s):\n    a = b * h\n    return a * (a / (b + 2 * h)) ** (2 / 3) * s ** 0.5 / n\n\n\n"
   "while True:\n    b = r.randint(1, 8)\n    n = r.choice([0.013, 0.015, 0.02, 0.025, 0.03])\n    s = r.choice([0.0005, 0.001, 0.002, 0.004, 0.01])\n"
   "    q = round(debit(r.uniform(0.2, 4.0), b, n, s), 2)\n    if q < 0.05 or q >= debit(10, b, n, s) * 0.9:\n        continue\n"
   "    lo, hi = 0.0, 10.0\n    for _ in range(200):\n        m = (lo + hi) / 2\n        if debit(m, b, n, s) < q:\n            lo = m\n"
   "        else:\n            hi = m\n    if aman((lo + hi) / 2, 3):\n        break\nprint(b)\nprint(n)\nprint(s)\nprint(f'{q:.2f}')\n",
   r'''
lebar = int(input())
kekasaran = float(input())
kemiringan = float(input())
debit_rencana = float(input())
bawah = 0.0
atas = 10.0
for _ in range(50):
    tengah = (bawah + atas) / 2
    luas = lebar * tengah
    keliling = lebar + 2 * tengah
    jari_hidraulis = luas / keliling
    debit = luas * jari_hidraulis ** (2 / 3) * kemiringan ** 0.5 / kekasaran
    if debit < debit_rencana:
        bawah = tengah
    else:
        atas = tengah
kedalaman = (bawah + atas) / 2
print(f"h = {kedalaman:.3f} m")
''',
   ("Kedalaman Normal dengan Metode Bagi Dua",
    "Debit saluran persegi menurut Manning adalah Q = A × R^(2/3) × S^(1/2) / n, dengan A = b × h, keliling basah P = b + 2h, dan "
    "R = A / P. Bila Q diketahui dan kedalaman h yang dicari, rumus itu tidak dapat dibalik secara langsung. Karena debit selalu "
    "bertambah bila h bertambah, h dapat dicari dengan metode bagi dua:\n\n"
    "1. mulai dengan batas bawah 0 dan batas atas 10 (meter);\n"
    "2. ulangi 50 kali: ambil titik tengah kedua batas dan hitung debit pada kedalaman itu; bila debit itu lebih kecil dari debit "
    "rencana, titik tengah menjadi batas bawah yang baru; bila tidak, ia menjadi batas atas yang baru;\n"
    "3. jawabannya titik tengah kedua batas terakhir.\n\n"
    "Cetak kedalaman dengan tiga angka di belakang titik.\n\n"
    "Contoh: b = `2`, n = `0.015`, S = `0.001`, Q = `3.00` menghasilkan `h = 1.096 m`.",
    "Empat baris: lebar b (m, bilangan bulat), koefisien kekasaran n, kemiringan dasar S, debit rencana Q (m³/detik). Tiga yang "
    "terakhir bilangan pecahan.",
    "Satu baris berbentuk `h = H m`, dengan H tiga angka di belakang titik.",
    "b 1 sampai 8; n 0.013 sampai 0.03; S 0.0005 sampai 0.01; kedalaman yang dicari selalu di antara 0 dan 10 m."),
   ("Normal Depth by Bisection",
    "The discharge of a rectangular channel by Manning is Q = A × R^(2/3) × S^(1/2) / n, with A = b × h, wetted perimeter P = b + 2h, "
    "and R = A / P. When Q is known and the depth h is wanted, the formula cannot be inverted directly. Because the discharge always "
    "grows when h grows, h can be found by bisection:\n\n"
    "1. start with a lower bound of 0 and an upper bound of 10 (metres);\n"
    "2. repeat 50 times: take the midpoint of the two bounds and compute the discharge at that depth; if it is smaller than the "
    "design discharge, the midpoint becomes the new lower bound; otherwise it becomes the new upper bound;\n"
    "3. the answer is the midpoint of the last two bounds.\n\n"
    "Print the depth with three digits after the point.\n\n"
    "Example: b = `2`, n = `0.015`, S = `0.001`, Q = `3.00` give `h = 1.096 m`.",
    "Four lines: the width b (m, a whole number), the roughness coefficient n, the bed slope S, the design discharge Q (m³/s). The "
    "last three are decimal numbers.",
    "One line of the form `h = H m`, with H having three digits after the point.",
    "b is 1 to 8; n is 0.013 to 0.03; S is 0.0005 to 0.01; the depth sought is always between 0 and 10 m."),
   ("العمق الطبيعي بطريقة التنصيف",
    "تصريف قناة مستطيلة وفق مانينغ هو Q = A × R^(2/3) × S^(1/2) / n، حيث A = b × h والمحيط المبتلّ P = b + 2h و R = A / P. "
    "إذا عُلم Q وطُلب العمق h فلا يمكن عكس الصيغة مباشرة. ولأن التصريف يزداد دائمًا بازدياد h يمكن إيجاد h بطريقة التنصيف:\n\n"
    "1. ابدأ بحدّ أدنى 0 وحدّ أعلى 10 (أمتار)؛\n"
    "2. كرِّر 50 مرة: خذ منتصف الحدّين واحسب التصريف عند ذلك العمق؛ إذا كان أصغر من التصريف التصميمي صار المنتصف الحدّ الأدنى "
    "الجديد؛ وإلا صار الحدّ الأعلى الجديد؛\n"
    "3. الجواب هو منتصف الحدّين الأخيرين.\n\n"
    "اطبع العمق بثلاث منازل عشرية.\n\n"
    "مثال: b = `2` و n = `0.015` و S = `0.001` و Q = `3.00` تعطي `h = 1.096 m`.",
    "أربعة أسطر: العرض b (م، عدد صحيح)، معامل الخشونة n، ميل القاع S، التصريف التصميمي Q (م³/ث). الثلاثة الأخيرة أعداد عشرية.",
    "سطر واحد بالشكل `h = H m` حيث H بثلاث منازل عشرية.",
    "b من 1 إلى 8؛ n من 0.013 إلى 0.03؛ S من 0.0005 إلى 0.01؛ والعمق المطلوب بين 0 و10 م دائمًا."))

py("kombinasi-truk", "sulit",
   "print([29, 3, 60, 7, 120][i] if i < 5 else r.randint(1, 300))\n",
   r'''
volume = int(input())
cara = 0
tersedikit = 0
kecil_terbaik = 0
sedang_terbaik = 0
besar_terbaik = 0
for besar in range(volume // 12, -1, -1):
    for sedang in range((volume - 12 * besar) // 8, -1, -1):
        sisa = volume - 12 * besar - 8 * sedang
        if sisa % 5 != 0:
            continue
        kecil = sisa // 5
        cara = cara + 1
        banyak = kecil + sedang + besar
        if tersedikit == 0 or banyak < tersedikit:
            tersedikit = banyak
            kecil_terbaik = kecil
            sedang_terbaik = sedang
            besar_terbaik = besar
print(f"Cara: {cara}")
if cara == 0:
    print("TIDAK BISA")
else:
    print(f"Tersedikit: {tersedikit} truk ({kecil_terbaik} kecil, {sedang_terbaik} sedang, {besar_terbaik} besar)")
''',
   ("Kombinasi Truk Bermuatan Penuh",
    "Tanah galian harus diangkut habis dengan truk yang semuanya bermuatan penuh. Tersedia tiga ukuran truk: kecil 5 m³, sedang 8 m³, "
    "dan besar 12 m³; setiap ukuran boleh dipakai berapa kali pun, termasuk tidak dipakai. Sebuah cara adalah tiga bilangan (kecil, "
    "sedang, besar) yang total muatannya tepat sama dengan volume tanah.\n\n"
    "Hitung banyaknya cara. Lalu cetak cara dengan jumlah truk paling sedikit; bila ada beberapa, pilih yang truk besarnya paling "
    "banyak, dan bila masih sama, yang truk sedangnya paling banyak. Bila tidak ada cara, cetak `TIDAK BISA`.\n\n"
    "Contoh: volume `29` menghasilkan\n\n```\nCara: 2\nTersedikit: 3 truk (1 kecil, 0 sedang, 2 besar)\n```\n\n"
    "(cara lainnya 1 kecil dan 3 sedang, 4 truk). Volume `3` menghasilkan `Cara: 0` lalu `TIDAK BISA`.",
    "Satu baris: volume tanah (m³, bilangan bulat).",
    "Baris pertama `Cara: C`. Baris kedua `Tersedikit: T truk (K kecil, S sedang, B besar)` atau `TIDAK BISA`.",
    "Volume 1 sampai 300."),
   ("Combinations of Full Trucks",
    "Excavated soil must be hauled away completely with trucks that are all fully loaded. Three truck sizes are available: small 5 m³, "
    "medium 8 m³ and large 12 m³; each size may be used any number of times, including not at all. A way is three numbers (small, "
    "medium, large) whose total load is exactly the soil volume.\n\n"
    "Count the ways. Then print the way with the fewest trucks; if there are several, choose the one with the most large trucks, and "
    "if still equal, the one with the most medium trucks. If there is no way, print `TIDAK BISA`.\n\n"
    "Example: a volume of `29` gives\n\n```\nCara: 2\nTersedikit: 3 truk (1 kecil, 0 sedang, 2 besar)\n```\n\n"
    "(the other way is 1 small and 3 medium, 4 trucks). A volume of `3` gives `Cara: 0` then `TIDAK BISA`.",
    "One line: the soil volume (m³, a whole number).",
    "First line `Cara: C`. Second line `Tersedikit: T truk (K kecil, S sedang, B besar)` or `TIDAK BISA` (Indonesian words: kecil "
    "small, sedang medium, besar large).",
    "The volume is 1 to 300."),
   ("تراكيب الشاحنات الممتلئة",
    "يجب نقل تربة الحفر كلها بشاحنات ممتلئة تمامًا. تتوفر ثلاثة مقاسات: صغيرة 5 م³ ومتوسطة 8 م³ وكبيرة 12 م³؛ ويجوز استعمال كل مقاس "
    "أي عدد من المرات أو عدم استعماله. الطريقة هي ثلاثة أعداد (صغيرة، متوسطة، كبيرة) مجموع حمولتها يساوي حجم التربة تمامًا.\n\n"
    "احسب عدد الطرق. ثم اطبع الطريقة ذات أقل عدد من الشاحنات؛ وإذا تعددت فاختر ذات أكثر عدد من الشاحنات الكبيرة، وإذا تساوت أيضًا "
    "فذات أكثر عدد من المتوسطة. إذا لم توجد طريقة فاطبع `TIDAK BISA`.\n\n"
    "مثال: حجم `29` يعطي\n\n```\nCara: 2\nTersedikit: 3 truk (1 kecil, 0 sedang, 2 besar)\n```\n\n"
    "(الطريقة الأخرى صغيرة واحدة و3 متوسطة، أي 4 شاحنات). وحجم `3` يعطي `Cara: 0` ثم `TIDAK BISA`.",
    "سطر واحد: حجم التربة (م³، عدد صحيح).",
    "السطر الأول `Cara: C`. السطر الثاني `Tersedikit: T truk (K kecil, S sedang, B besar)` أو `TIDAK BISA` "
    "(بالإندونيسية: kecil صغيرة، sedang متوسطة، besar كبيرة).",
    "الحجم من 1 إلى 300."))

py("antrean-mixer", "sulit",
   "n = r.randint(2, 25)\nd = r.randint(8, 30)\nw = r.randint(5, 45)\nt = 0\nprint(n)\nprint(d)\nprint(w)\nfor k in range(n):\n"
   "    print(t)\n    t += r.randint(0, [d // 2, 2 * d, d, 3][i % 4])\n",
   r'''
banyak = int(input())
lama_tuang = int(input())
batas_tunggu = int(input())
pompa_bebas = 0
dilayani = 0
ditolak = 0
tunggu_terlama = 0
for _ in range(banyak):
    tiba = int(input())
    mulai = tiba
    if pompa_bebas > tiba:
        mulai = pompa_bebas
    tunggu = mulai - tiba
    if tunggu > batas_tunggu:
        ditolak = ditolak + 1
        continue
    dilayani = dilayani + 1
    if tunggu > tunggu_terlama:
        tunggu_terlama = tunggu
    pompa_bebas = mulai + lama_tuang
print(f"Dilayani: {dilayani}")
print(f"Ditolak: {ditolak}")
print(f"Tunggu terlama: {tunggu_terlama} menit")
print(f"Selesai: menit {pompa_bebas}")
''',
   ("Antrean Truk Mixer di Pompa Beton",
    "Satu pompa beton melayani truk mixer satu per satu sesuai urutan kedatangan. Menuang satu truk selalu memakan waktu yang sama. "
    "Untuk setiap truk:\n\n"
    "1. truk mulai menuang pada menit ia tiba bila pompa sedang bebas, atau pada menit pompa selesai dengan truk sebelumnya bila "
    "pompa masih sibuk;\n"
    "2. waktu tunggunya adalah menit mulai dikurangi menit tiba;\n"
    "3. bila waktu tunggu itu melebihi batas tunggu, beton di truk itu sudah terlalu lama: truk ditolak, langsung pergi, dan tidak "
    "memakai pompa sama sekali;\n"
    "4. bila tidak, truk dilayani dan pompa baru bebas lagi setelah lama menuang berlalu.\n\n"
    "Cetak banyaknya truk yang dilayani, banyaknya yang ditolak, waktu tunggu terlama di antara truk yang dilayani, dan menit pompa "
    "selesai dengan truk terakhir yang dilayani.\n\n"
    "Contoh: `4` truk, lama menuang `20`, batas tunggu `15`, tiba pada menit `0`, `10`, `15`, `50` menghasilkan\n\n"
    "```\nDilayani: 3\nDitolak: 1\nTunggu terlama: 10 menit\nSelesai: menit 70\n```\n\n"
    "(truk kedua menunggu 10 menit; truk ketiga harus menunggu 25 menit sehingga ditolak; truk keempat langsung dilayani).",
    "Baris pertama: banyaknya truk N. Baris kedua: lama menuang (menit). Baris ketiga: batas tunggu (menit). Lalu N baris menit "
    "kedatangan, tidak menurun. Semuanya bilangan bulat.",
    "Empat baris: `Dilayani: A`, `Ditolak: B`, `Tunggu terlama: W menit`, `Selesai: menit T`.",
    "N 2 sampai 25; lama menuang 8 sampai 30; batas tunggu 5 sampai 45. Truk pertama selalu dilayani."),
   ("Mixer Truck Queue at the Concrete Pump",
    "One concrete pump serves mixer trucks one at a time in their order of arrival. Unloading a truck always takes the same time. "
    "For each truck:\n\n"
    "1. the truck starts unloading at the minute it arrives if the pump is free, or at the minute the pump finishes the previous "
    "truck if the pump is still busy;\n"
    "2. its waiting time is the start minute minus the arrival minute;\n"
    "3. if that waiting time exceeds the waiting limit, the concrete in the truck is too old: the truck is rejected, leaves at once, "
    "and does not use the pump at all;\n"
    "4. otherwise the truck is served and the pump becomes free again only after the unloading time has passed.\n\n"
    "Print the number of trucks served, the number rejected, the longest waiting time among the trucks served, and the minute the "
    "pump finishes the last truck served.\n\n"
    "Example: `4` trucks, unloading time `20`, waiting limit `15`, arriving at minutes `0`, `10`, `15`, `50` give\n\n"
    "```\nDilayani: 3\nDitolak: 1\nTunggu terlama: 10 menit\nSelesai: menit 70\n```\n\n"
    "(the second truck waits 10 minutes; the third would wait 25 minutes and is rejected; the fourth is served at once).",
    "First line: the number of trucks N. Second line: the unloading time (minutes). Third line: the waiting limit (minutes). Then N "
    "lines of arrival minutes, never decreasing. All whole numbers.",
    "Four lines: `Dilayani: A`, `Ditolak: B`, `Tunggu terlama: W menit`, `Selesai: menit T` (Indonesian words).",
    "N is 2 to 25; unloading time 8 to 30; waiting limit 5 to 45. The first truck is always served."),
   ("طابور الشاحنات الخلّاطة عند مضخة الخرسانة",
    "تخدم مضخة خرسانة واحدة الشاحنات الخلّاطة واحدة تلو الأخرى بترتيب وصولها. تفريغ الشاحنة يستغرق الزمن نفسه دائمًا. لكل شاحنة:\n\n"
    "1. تبدأ الشاحنة التفريغ في دقيقة وصولها إذا كانت المضخة حرّة، أو في الدقيقة التي تنتهي فيها المضخة من الشاحنة السابقة إذا "
    "كانت مشغولة؛\n"
    "2. زمن انتظارها هو دقيقة البدء ناقص دقيقة الوصول؛\n"
    "3. إذا تجاوز زمن الانتظار حدّ الانتظار فخرسانة الشاحنة قد قدُمت: تُرفض الشاحنة وتغادر فورًا ولا تستعمل المضخة إطلاقًا؛\n"
    "4. وإلا تُخدَم الشاحنة ولا تصير المضخة حرّة إلا بعد انقضاء زمن التفريغ.\n\n"
    "اطبع عدد الشاحنات المخدومة، وعدد المرفوضة، وأطول زمن انتظار بين المخدومة، والدقيقة التي تنتهي فيها المضخة من آخر شاحنة مخدومة.\n\n"
    "مثال: `4` شاحنات وزمن تفريغ `20` وحدّ انتظار `15` ووصول في الدقائق `0` و`10` و`15` و`50` تعطي\n\n"
    "```\nDilayani: 3\nDitolak: 1\nTunggu terlama: 10 menit\nSelesai: menit 70\n```\n\n"
    "(الشاحنة الثانية تنتظر 10 دقائق؛ الثالثة كانت ستنتظر 25 دقيقة فرُفضت؛ الرابعة خُدمت فورًا).",
    "السطر الأول: عدد الشاحنات N. الثاني: زمن التفريغ (دقائق). الثالث: حدّ الانتظار (دقائق). ثم N سطرًا لدقائق الوصول غير "
    "متناقصة. كلها أعداد صحيحة.",
    "أربعة أسطر: `Dilayani: A` و`Ditolak: B` و`Tunggu terlama: W menit` و`Selesai: menit T` (بالإندونيسية).",
    "N من 2 إلى 25؛ زمن التفريغ من 8 إلى 30؛ حدّ الانتظار من 5 إلى 45. الشاحنة الأولى تُخدَم دائمًا."))

tulis(__file__, "TS-PY", 4, "python", {"mudah_sekali": 8, "mudah": 8, "mudah_sedang": 6, "sedang": 6, "sulit": 4})
