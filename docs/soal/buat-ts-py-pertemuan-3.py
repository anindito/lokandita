#!/usr/bin/env python3
"""Menyusun docs/soal/ts-py-pertemuan-3.json.

TS-PY pertemuan 3 — Logika dan Percabangan (RPS Rev2 10.3): operator
perbandingan dan logika, if / elif / else, percabangan bersarang, pemeriksaan
syarat teknis (aman / tidak aman, klasifikasi, tarif bertingkat).

Konsep yang diizinkan di produksi: IO_DASAR, ARITMETIKA, VARIABEL_TIPE,
KONVERSI_TIPE, FORMAT_OUTPUT, PERCABANGAN. Belum ada perulangan, fungsi, list,
atau operasi string: keluaran berupa teks tetap atau f-string yang langsung
dicetak.

Pilihan teknis (DECISIONS.md, 2026-11-23):
- Semua soal `trimmed`; pecahan dicetak dengan jumlah desimal yang ditentukan.
- Generator tidak pernah menghasilkan nilai pecahan yang jatuh tepat di batas
  kelas (misalnya kemiringan tepat 8%), karena hasil pecahan biner di batas
  bergantung pada urutan operasi. Batas kelas untuk bilangan bulat tetap diuji.
- Indeks tes `i` dipakai supaya setiap cabang muncul di antara 12 tes.
- Batas kelas, tarif dan aturan penerimaan disebut parameter latihan.

    python3 docs/soal/buat-ts-py-pertemuan-3.py
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

py("cek-kuat-tekan", "mudah_sekali",
   "f = r.choice([20, 25, 30, 35, 40])\nu = f if i % 4 == 0 else f + r.randint(-6, 6)\nprint(u)\nprint(f)\n",
   r'''
hasil_uji = int(input())
rencana = int(input())
if hasil_uji >= rencana:
    print("MEMENUHI")
else:
    print("TIDAK MEMENUHI")
''',
   ("Kuat Tekan Memenuhi atau Tidak",
    "Hasil uji kuat tekan sebuah benda uji dibandingkan dengan kuat tekan rencana. Hasil dinyatakan memenuhi bila nilainya sama dengan "
    "atau lebih besar dari rencana.\n\nContoh: hasil uji `32` dengan rencana `30` menghasilkan `MEMENUHI`; hasil uji `28` dengan rencana "
    "`30` menghasilkan `TIDAK MEMENUHI`.",
    "Dua baris: hasil uji (MPa), lalu kuat tekan rencana (MPa). Keduanya bilangan bulat.",
    "Satu baris: `MEMENUHI` atau `TIDAK MEMENUHI`.",
    "Kedua nilai 14 sampai 46."),
   ("Does the Compressive Strength Comply?",
    "The compressive strength of a test specimen is compared with the specified strength. The result complies when it is equal to or "
    "greater than the specified value.\n\nExample: a result of `32` with a specified strength of `30` gives `MEMENUHI`; a result of `28` "
    "with `30` gives `TIDAK MEMENUHI`.",
    "Two lines: the test result (MPa), then the specified strength (MPa). Both are whole numbers.",
    "One line: `MEMENUHI` or `TIDAK MEMENUHI` (the words stay in Indonesian).",
    "Both values are 14 to 46."),
   ("هل مقاومة الضغط مستوفاة؟",
    "تُقارن مقاومة الضغط لعيّنة اختبار بالمقاومة التصميمية. تُعدّ النتيجة مستوفاة إذا كانت مساوية للقيمة التصميمية أو أكبر منها.\n\n"
    "مثال: نتيجة `32` مع مقاومة تصميمية `30` تعطي `MEMENUHI`؛ ونتيجة `28` مع `30` تعطي `TIDAK MEMENUHI`.",
    "سطران: نتيجة الاختبار (MPa) ثم المقاومة التصميمية (MPa). كلاهما عدد صحيح.",
    "سطر واحد: `MEMENUHI` أو `TIDAK MEMENUHI` (تبقى الكلمات بالإندونيسية).",
    "القيمتان من 14 إلى 46."))

py("hari-hujan", "mudah_sekali",
   "p = [0, r.randint(1, 9), 10, r.randint(11, 800)][i % 4]\nprint(f'{p / 10:.1f}')\n",
   r'''
curah_hujan = float(input())
if curah_hujan >= 1.0:
    print("Hari hujan")
else:
    print("Hari kering")
''',
   ("Hari Hujan atau Hari Kering",
    "Pos hujan mencatat curah hujan harian dalam milimeter. Dalam latihan ini sebuah hari dihitung sebagai hari hujan bila curah "
    "hujannya 1.0 mm atau lebih; di bawah itu disebut hari kering.\n\nContoh: `12.5` menghasilkan `Hari hujan`; `0.4` menghasilkan "
    "`Hari kering`.",
    "Satu baris: curah hujan harian dalam mm (bilangan pecahan dengan satu angka di belakang titik).",
    "Satu baris: `Hari hujan` atau `Hari kering`.",
    "Curah hujan 0.0 sampai 80.0."),
   ("Rainy Day or Dry Day",
    "A rain gauge records the daily rainfall in millimetres. In this exercise a day counts as a rainy day when its rainfall is 1.0 mm or "
    "more; below that it is a dry day.\n\nExample: `12.5` gives `Hari hujan`; `0.4` gives `Hari kering`.",
    "One line: the daily rainfall in mm (a decimal number with one digit after the point).",
    "One line: `Hari hujan` or `Hari kering` (the words stay in Indonesian).",
    "The rainfall is 0.0 to 80.0."),
   ("يوم ماطر أم يوم جاف",
    "تسجّل محطة المطر الهطول اليومي بالمليمتر. في هذا التمرين يُعدّ اليوم ماطرًا إذا كان الهطول 1.0 مم أو أكثر، وما دون ذلك يوم جاف.\n\n"
    "مثال: `12.5` يعطي `Hari hujan`؛ و`0.4` يعطي `Hari kering`.",
    "سطر واحد: الهطول اليومي بالمليمتر (عدد عشري بمنزلة واحدة).",
    "سطر واحد: `Hari hujan` أو `Hari kering` (تبقى الكلمات بالإندونيسية).",
    "الهطول من 0.0 إلى 80.0."))

py("elevasi-tertinggi", "mudah_sekali",
   "a = r.randint(0, 9000)\nb = a if i == 5 else r.randint(0, 9000)\nprint(a)\nprint(b)\n",
   r'''
elevasi_a = int(input())
elevasi_b = int(input())
if elevasi_a > elevasi_b:
    print(elevasi_a)
else:
    print(elevasi_b)
''',
   ("Elevasi yang Lebih Tinggi",
    "Dua titik diukur elevasinya dalam sentimeter. Cetak elevasi yang lebih tinggi. Bila keduanya sama, cetak nilai itu.\n\n"
    "Contoh: `1250` dan `1310` menghasilkan `1310`.",
    "Dua baris: elevasi titik A, lalu elevasi titik B (cm, bilangan bulat).",
    "Satu baris: elevasi yang lebih tinggi.",
    "Elevasi 0 sampai 9000."),
   ("The Higher Elevation",
    "The elevations of two points are measured in centimetres. Print the higher one. If they are equal, print that value.\n\n"
    "Example: `1250` and `1310` give `1310`.",
    "Two lines: the elevation of point A, then of point B (cm, whole numbers).",
    "One line: the higher elevation.",
    "Elevations are 0 to 9000."),
   ("المنسوب الأعلى",
    "قيس منسوبا نقطتين بالسنتيمتر. اطبع المنسوب الأعلى. إذا تساويا فاطبع تلك القيمة.\n\nمثال: `1250` و`1310` يعطيان `1310`.",
    "سطران: منسوب النقطة A ثم منسوب النقطة B (سم، عددان صحيحان).",
    "سطر واحد: المنسوب الأعلى.",
    "المناسيب من 0 إلى 9000."))

py("sisi-patok", "mudah_sekali",
   "print(r.randint(1, 999))\n",
   r'''
nomor = int(input())
if nomor % 2 == 1:
    print("KIRI")
else:
    print("KANAN")
''',
   ("Patok Kiri atau Kanan",
    "Patok di sepanjang rencana jalan dipasang berselang-seling: patok bernomor ganjil di sisi kiri, patok bernomor genap di sisi kanan. "
    "Tentukan sisi sebuah patok dari nomornya.\n\nContoh: `17` menghasilkan `KIRI`; `40` menghasilkan `KANAN`.",
    "Satu baris: nomor patok (bilangan bulat).",
    "Satu baris: `KIRI` atau `KANAN`.",
    "Nomor patok 1 sampai 999."),
   ("Left or Right Stake",
    "Stakes along a planned road alternate: odd-numbered stakes stand on the left side, even-numbered stakes on the right side. "
    "Decide the side of a stake from its number.\n\nExample: `17` gives `KIRI`; `40` gives `KANAN`.",
    "One line: the stake number (a whole number).",
    "One line: `KIRI` (left) or `KANAN` (right); the words stay in Indonesian.",
    "The stake number is 1 to 999."),
   ("وتد أيسر أم أيمن",
    "تُثبَّت الأوتاد على طول مسار الطريق بالتناوب: الأوتاد ذات الأرقام الفردية على الجانب الأيسر والزوجية على الجانب الأيمن. "
    "حدِّد جانب الوتد من رقمه.\n\nمثال: `17` يعطي `KIRI`؛ و`40` يعطي `KANAN`.",
    "سطر واحد: رقم الوتد (عدد صحيح).",
    "سطر واحد: `KIRI` (يسار) أو `KANAN` (يمين)؛ تبقى الكلمات بالإندونيسية.",
    "رقم الوتد من 1 إلى 999."))

py("cek-muatan-truk", "mudah_sekali",
   "k = r.choice([5000, 8000, 10000, 12000])\nm = k if i == 4 else k + r.randint(-1500, 1500)\nprint(m)\nprint(k)\n",
   r'''
muatan = int(input())
kapasitas = int(input())
if muatan <= kapasitas:
    print("BOLEH JALAN")
else:
    print(f"KELEBIHAN {muatan - kapasitas} kg")
''',
   ("Muatan Truk di Jembatan Timbang",
    "Di jembatan timbang, muatan truk dibandingkan dengan kapasitas izinnya. Bila muatan tidak melebihi kapasitas, truk boleh jalan. "
    "Bila melebihi, petugas mencatat kelebihannya.\n\nContoh: muatan `8200` dengan kapasitas `8000` menghasilkan `KELEBIHAN 200 kg`; "
    "muatan `7500` dengan kapasitas `8000` menghasilkan `BOLEH JALAN`.",
    "Dua baris: muatan (kg), lalu kapasitas izin (kg). Keduanya bilangan bulat.",
    "Satu baris: `BOLEH JALAN`, atau `KELEBIHAN X kg` dengan X selisih muatan dan kapasitas.",
    "Kapasitas 5000 sampai 12000; muatan berbeda paling banyak 1500 dari kapasitas."),
   ("Truck Load at the Weighbridge",
    "At a weighbridge the load of a truck is compared with its permitted capacity. If the load does not exceed the capacity the truck may "
    "go. If it does, the officer records the excess.\n\nExample: a load of `8200` with capacity `8000` gives `KELEBIHAN 200 kg`; a load "
    "of `7500` with capacity `8000` gives `BOLEH JALAN`.",
    "Two lines: the load (kg), then the permitted capacity (kg). Both are whole numbers.",
    "One line: `BOLEH JALAN`, or `KELEBIHAN X kg` where X is the load minus the capacity (the words stay in Indonesian).",
    "The capacity is 5000 to 12000; the load differs from it by at most 1500."),
   ("حمولة الشاحنة على الميزان الجسري",
    "على الميزان الجسري تُقارن حمولة الشاحنة بالحمولة المسموح بها. إذا لم تتجاوز الحمولةُ المسموحَ به فللشاحنة أن تسير، "
    "وإن تجاوزته سجّل الموظف مقدار الزيادة.\n\nمثال: حمولة `8200` مع سعة `8000` تعطي `KELEBIHAN 200 kg`؛ وحمولة `7500` مع سعة `8000` "
    "تعطي `BOLEH JALAN`.",
    "سطران: الحمولة (كغ) ثم الحمولة المسموح بها (كغ). كلاهما عدد صحيح.",
    "سطر واحد: `BOLEH JALAN` أو `KELEBIHAN X kg` حيث X الفرق بين الحمولة والسعة (تبقى الكلمات بالإندونيسية).",
    "السعة من 5000 إلى 12000؛ والحمولة تختلف عنها بما لا يزيد على 1500."))

py("selisih-bacaan", "mudah_sekali",
   "a = r.randint(500, 3500)\nb = a if i == 7 else r.randint(500, 3500)\nprint(a)\nprint(b)\n",
   r'''
bacaan_pertama = int(input())
bacaan_kedua = int(input())
if bacaan_pertama >= bacaan_kedua:
    selisih = bacaan_pertama - bacaan_kedua
else:
    selisih = bacaan_kedua - bacaan_pertama
print(f"Selisih: {selisih} mm")
''',
   ("Selisih Dua Bacaan Rambu",
    "Dua juru ukur membaca rambu yang sama dan hasilnya bisa berbeda. Hitung selisih kedua bacaan sebagai bilangan yang tidak negatif, "
    "tanpa peduli bacaan mana yang lebih besar.\n\nContoh: `1482` dan `1507` menghasilkan `Selisih: 25 mm`; `1507` dan `1482` juga "
    "menghasilkan `Selisih: 25 mm`.",
    "Dua baris: bacaan pertama, lalu bacaan kedua (mm, bilangan bulat).",
    "Satu baris berbentuk `Selisih: D mm`.",
    "Bacaan 500 sampai 3500."),
   ("Difference Between Two Staff Readings",
    "Two surveyors read the same levelling staff and may get different values. Compute the difference of the two readings as a "
    "non-negative number, whichever reading is larger.\n\nExample: `1482` and `1507` give `Selisih: 25 mm`; `1507` and `1482` also give "
    "`Selisih: 25 mm`.",
    "Two lines: the first reading, then the second reading (mm, whole numbers).",
    "One line of the form `Selisih: D mm` (the word stays in Indonesian).",
    "Readings are 500 to 3500."),
   ("الفرق بين قراءتين للقامة",
    "يقرأ مسّاحان القامة نفسها وقد تختلف قراءتاهما. احسب الفرق بين القراءتين عددًا غير سالب أيًّا كانت القراءة الأكبر.\n\n"
    "مثال: `1482` و`1507` يعطيان `Selisih: 25 mm`؛ و`1507` و`1482` يعطيان كذلك `Selisih: 25 mm`.",
    "سطران: القراءة الأولى ثم القراءة الثانية (مم، عددان صحيحان).",
    "سطر واحد بالشكل `Selisih: D mm` (تبقى الكلمة بالإندونيسية).",
    "القراءات من 500 إلى 3500."))

py("diskon-semen", "mudah_sekali",
   "n = [100, 99][i] if i < 2 else r.randint(10, 400)\nprint(n)\nprint(r.randint(100, 160) * 500)\n",
   r'''
jumlah_sak = int(input())
harga_per_sak = int(input())
total = jumlah_sak * harga_per_sak
if jumlah_sak >= 100:
    total = total * 95 // 100
print(f"Total: Rp {total}")
''',
   ("Potongan Harga Pembelian Semen",
    "Toko bahan bangunan memberi potongan 5% dari total harga bila pembelian semen mencapai 100 sak atau lebih. Hitung total yang harus "
    "dibayar. Harga per sak selalu kelipatan 500 rupiah, sehingga hasilnya selalu bilangan bulat; cetak tanpa angka di belakang titik.\n\n"
    "Contoh: `120` sak dengan harga `65000` menghasilkan `Total: Rp 7410000`; `80` sak dengan harga `65000` menghasilkan "
    "`Total: Rp 5200000`.",
    "Dua baris: jumlah sak, lalu harga per sak (rupiah). Keduanya bilangan bulat.",
    "Satu baris berbentuk `Total: Rp X`, dengan X bilangan bulat.",
    "Jumlah sak 10 sampai 400; harga 50000 sampai 80000, kelipatan 500."),
   ("Discount on a Cement Purchase",
    "A building-materials shop gives 5% off the total price when a purchase reaches 100 bags of cement or more. Compute the amount to "
    "pay. The price per bag is always a multiple of 500 rupiah, so the result is always a whole number; print it without a decimal "
    "point.\n\nExample: `120` bags at `65000` give `Total: Rp 7410000`; `80` bags at `65000` give `Total: Rp 5200000`.",
    "Two lines: the number of bags, then the price per bag (rupiah). Both are whole numbers.",
    "One line of the form `Total: Rp X`, where X is a whole number.",
    "Bags: 10 to 400; price: 50000 to 80000, a multiple of 500."),
   ("خصم على شراء الإسمنت",
    "يمنح متجر مواد البناء خصمًا قدره 5% من السعر الإجمالي إذا بلغ الشراء 100 كيس إسمنت أو أكثر. احسب المبلغ الواجب دفعه. "
    "سعر الكيس دائمًا من مضاعفات 500 روبية، فالناتج عدد صحيح دائمًا؛ اطبعه دون فاصلة عشرية.\n\n"
    "مثال: `120` كيسًا بسعر `65000` تعطي `Total: Rp 7410000`؛ و`80` كيسًا بسعر `65000` تعطي `Total: Rp 5200000`.",
    "سطران: عدد الأكياس ثم سعر الكيس (روبية). كلاهما عدد صحيح.",
    "سطر واحد بالشكل `Total: Rp X` حيث X عدد صحيح.",
    "عدد الأكياس من 10 إلى 400؛ والسعر من 50000 إلى 80000 ومن مضاعفات 500."))

py("lolos-saringan", "mudah_sekali",
   "p = [500, 501, 499][i] if i < 3 else r.randint(0, 1000)\nprint(f'{p / 10:.1f}')\n",
   r'''
persen_lolos = float(input())
if persen_lolos > 50.0:
    print("BERBUTIR HALUS")
else:
    print("BERBUTIR KASAR")
''',
   ("Tanah Berbutir Halus atau Kasar",
    "Pada analisis saringan, tanah disebut berbutir halus bila lebih dari 50% beratnya lolos saringan No. 200. Bila tepat 50% atau "
    "kurang, tanah itu berbutir kasar (parameter latihan).\n\nContoh: `62.5` menghasilkan `BERBUTIR HALUS`; `50.0` menghasilkan "
    "`BERBUTIR KASAR`.",
    "Satu baris: persen lolos saringan No. 200 (bilangan pecahan dengan satu angka di belakang titik).",
    "Satu baris: `BERBUTIR HALUS` atau `BERBUTIR KASAR`.",
    "Persen lolos 0.0 sampai 100.0."),
   ("Fine-Grained or Coarse-Grained Soil",
    "In a sieve analysis a soil is called fine-grained when more than 50% of its weight passes the No. 200 sieve. At exactly 50% or "
    "less it is coarse-grained (an exercise parameter).\n\nExample: `62.5` gives `BERBUTIR HALUS`; `50.0` gives `BERBUTIR KASAR`.",
    "One line: the percentage passing the No. 200 sieve (a decimal number with one digit after the point).",
    "One line: `BERBUTIR HALUS` (fine) or `BERBUTIR KASAR` (coarse); the words stay in Indonesian.",
    "The percentage is 0.0 to 100.0."),
   ("تربة ناعمة الحبيبات أم خشنة",
    "في التحليل المنخلي تُسمّى التربة ناعمة الحبيبات إذا مرّ أكثر من 50% من وزنها من المنخل رقم 200. عند 50% تمامًا أو أقل تكون خشنة "
    "الحبيبات (قيمة للتمرين).\n\nمثال: `62.5` يعطي `BERBUTIR HALUS`؛ و`50.0` يعطي `BERBUTIR KASAR`.",
    "سطر واحد: النسبة المئوية المارّة من المنخل رقم 200 (عدد عشري بمنزلة واحدة).",
    "سطر واحد: `BERBUTIR HALUS` (ناعمة) أو `BERBUTIR KASAR` (خشنة)؛ تبقى الكلمات بالإندونيسية.",
    "النسبة من 0.0 إلى 100.0."))

# ================================= MUDAH (8) =================================

py("tegangan-aman", "mudah",
   AMAN + "while True:\n    a = r.choice([200, 250, 300, 350, 400, 450, 500]) * r.choice([200, 250, 300, 400, 500, 600])\n"
   "    s = r.choice([8, 10, 12, 15])\n    p = s * a // 1000 if i == 2 else r.randint(100, 2 * s * a // 1000)\n"
   "    if aman(p * 1000 / a, 2):\n        break\nprint(p)\nprint(a)\nprint(s)\n",
   r'''
gaya_kn = int(input())
luas_mm2 = int(input())
izin_mpa = int(input())
tegangan = gaya_kn * 1000 / luas_mm2
print(f"Tegangan: {tegangan:.2f} MPa")
if tegangan <= izin_mpa:
    print("AMAN")
else:
    print("TIDAK AMAN")
''',
   ("Tegangan Kolom terhadap Tegangan Izin",
    "Sebuah kolom memikul gaya tekan P (kN) pada luas penampang A (mm²). Tegangannya adalah P × 1000 / A dalam MPa. Kolom dinyatakan "
    "aman bila tegangan itu tidak melebihi tegangan izin.\n\nContoh: P = `900`, A = `90000`, izin = `12` menghasilkan\n\n"
    "```\nTegangan: 10.00 MPa\nAMAN\n```\n\nsedangkan P = `1500`, A = `90000`, izin = `12` menghasilkan `Tegangan: 16.67 MPa` lalu "
    "`TIDAK AMAN`.",
    "Tiga baris: gaya P (kN), luas A (mm²), tegangan izin (MPa). Semuanya bilangan bulat.",
    "Baris pertama `Tegangan: T MPa` dengan T dua angka di belakang titik. Baris kedua `AMAN` atau `TIDAK AMAN`.",
    "P 100 sampai 9000; A 40000 sampai 300000; tegangan izin 8 sampai 15."),
   ("Column Stress Against the Allowable Stress",
    "A column carries a compressive force P (kN) on a cross-section of area A (mm²). Its stress is P × 1000 / A in MPa. The column is "
    "safe when that stress does not exceed the allowable stress.\n\nExample: P = `900`, A = `90000`, allowable = `12` gives\n\n"
    "```\nTegangan: 10.00 MPa\nAMAN\n```\n\nwhile P = `1500`, A = `90000`, allowable = `12` gives `Tegangan: 16.67 MPa` then "
    "`TIDAK AMAN`.",
    "Three lines: the force P (kN), the area A (mm²), the allowable stress (MPa). All are whole numbers.",
    "First line `Tegangan: T MPa` with T having two digits after the point. Second line `AMAN` or `TIDAK AMAN` (Indonesian words).",
    "P is 100 to 9000; A is 40000 to 300000; the allowable stress is 8 to 15."),
   ("إجهاد العمود مقابل الإجهاد المسموح",
    "يحمل عمود قوة ضغط P (كيلونيوتن) على مقطع مساحته A (مم²). إجهاده يساوي P × 1000 / A بالميغاباسكال. يكون العمود آمنًا إذا لم يتجاوز "
    "هذا الإجهادُ الإجهادَ المسموح.\n\nمثال: P = `900` و A = `90000` والمسموح = `12` تعطي\n\n```\nTegangan: 10.00 MPa\nAMAN\n```\n\n"
    "أما P = `1500` و A = `90000` والمسموح = `12` فتعطي `Tegangan: 16.67 MPa` ثم `TIDAK AMAN`.",
    "ثلاثة أسطر: القوة P (كيلونيوتن)، المساحة A (مم²)، الإجهاد المسموح (MPa). كلها أعداد صحيحة.",
    "السطر الأول `Tegangan: T MPa` حيث T بمنزلتين عشريتين. السطر الثاني `AMAN` أو `TIDAK AMAN` (بالإندونيسية).",
    "P من 100 إلى 9000؛ A من 40000 إلى 300000؛ الإجهاد المسموح من 8 إلى 15."))

py("cek-slump", "mudah",
   "lo = r.choice([6, 8, 10])\nhi = lo + r.choice([2, 4])\ns = [lo, hi, lo - 1, hi + 1][i] if i < 4 else r.randint(lo - 4, hi + 4)\n"
   "print(s)\nprint(lo)\nprint(hi)\n",
   r'''
slump = int(input())
batas_bawah = int(input())
batas_atas = int(input())
if slump < batas_bawah:
    print("TERLALU KENTAL")
elif slump > batas_atas:
    print("TERLALU ENCER")
else:
    print("SESUAI")
''',
   ("Pemeriksaan Slump Beton",
    "Sebelum dituang, adukan beton diuji slump. Spesifikasi memberi batas bawah dan batas atas. Slump di bawah batas bawah berarti adukan "
    "terlalu kental; di atas batas atas berarti terlalu encer; selain itu sesuai (kedua batas termasuk sesuai).\n\n"
    "Contoh: slump `14` dengan batas `8` dan `12` menghasilkan `TERLALU ENCER`; slump `8` dengan batas `8` dan `12` menghasilkan `SESUAI`.",
    "Tiga baris: slump terukur, batas bawah, batas atas (cm, bilangan bulat).",
    "Satu baris: `TERLALU KENTAL`, `TERLALU ENCER`, atau `SESUAI`.",
    "Batas bawah 6 sampai 10; batas atas 2 atau 4 cm di atasnya; slump 2 sampai 18."),
   ("Concrete Slump Check",
    "Before placing, fresh concrete is slump-tested. The specification gives a lower and an upper limit. A slump below the lower limit "
    "means the mix is too stiff; above the upper limit it is too wet; otherwise it conforms (both limits conform).\n\n"
    "Example: a slump of `14` with limits `8` and `12` gives `TERLALU ENCER`; a slump of `8` with limits `8` and `12` gives `SESUAI`.",
    "Three lines: the measured slump, the lower limit, the upper limit (cm, whole numbers).",
    "One line: `TERLALU KENTAL` (too stiff), `TERLALU ENCER` (too wet), or `SESUAI` (conforms); Indonesian words.",
    "Lower limit 6 to 10; upper limit 2 or 4 cm above it; slump 2 to 18."),
   ("فحص هبوط الخرسانة",
    "قبل الصبّ يُجرى اختبار الهبوط للخلطة الخرسانية. تحدّد المواصفة حدًّا أدنى وحدًّا أعلى. الهبوط دون الحد الأدنى يعني أن الخلطة شديدة "
    "اللزوجة، وفوق الحد الأعلى يعني أنها شديدة السيولة، وما عدا ذلك مطابق (الحدّان مطابقان).\n\n"
    "مثال: هبوط `14` مع الحدّين `8` و`12` يعطي `TERLALU ENCER`؛ وهبوط `8` مع الحدّين `8` و`12` يعطي `SESUAI`.",
    "ثلاثة أسطر: الهبوط المقيس، الحد الأدنى، الحد الأعلى (سم، أعداد صحيحة).",
    "سطر واحد: `TERLALU KENTAL` (لزجة جدًّا) أو `TERLALU ENCER` (سائلة جدًّا) أو `SESUAI` (مطابقة)؛ بالإندونيسية.",
    "الحد الأدنى من 6 إلى 10؛ والحد الأعلى فوقه بـ2 أو 4 سم؛ والهبوط من 2 إلى 18."))

py("galian-timbunan", "mudah",
   "a = r.randint(500, 3000)\nb = a if i % 5 == 3 else a + r.randint(-250, 250)\nprint(a)\nprint(b)\n",
   r'''
tanah_asli = int(input())
rencana = int(input())
if tanah_asli > rencana:
    print(f"GALIAN {tanah_asli - rencana} cm")
elif tanah_asli < rencana:
    print(f"TIMBUNAN {rencana - tanah_asli} cm")
else:
    print("RATA")
''',
   ("Galian atau Timbunan di Satu Titik",
    "Di sebuah titik, elevasi tanah asli dibandingkan dengan elevasi rencana. Bila tanah asli lebih tinggi, tanah harus digali sebesar "
    "selisihnya. Bila lebih rendah, tanah harus ditimbun. Bila sama, tidak ada pekerjaan tanah.\n\n"
    "Contoh: tanah asli `1250` dan rencana `1180` menghasilkan `GALIAN 70 cm`; tanah asli `1100` dan rencana `1180` menghasilkan "
    "`TIMBUNAN 80 cm`.",
    "Dua baris: elevasi tanah asli, lalu elevasi rencana (cm, bilangan bulat).",
    "Satu baris: `GALIAN D cm`, `TIMBUNAN D cm`, atau `RATA`.",
    "Elevasi tanah asli 500 sampai 3000; selisih paling banyak 250."),
   ("Cut or Fill at One Point",
    "At a point the existing ground elevation is compared with the design elevation. If the ground is higher, soil must be cut by the "
    "difference. If it is lower, soil must be filled. If they are equal, there is no earthwork.\n\n"
    "Example: ground `1250` and design `1180` give `GALIAN 70 cm`; ground `1100` and design `1180` give `TIMBUNAN 80 cm`.",
    "Two lines: the existing ground elevation, then the design elevation (cm, whole numbers).",
    "One line: `GALIAN D cm` (cut), `TIMBUNAN D cm` (fill), or `RATA` (level); Indonesian words.",
    "Ground elevation 500 to 3000; the difference is at most 250."),
   ("حفر أم ردم عند نقطة",
    "عند نقطة ما يُقارن منسوب الأرض الطبيعية بالمنسوب التصميمي. إذا كانت الأرض أعلى وجب الحفر بمقدار الفرق، وإذا كانت أدنى وجب الردم، "
    "وإذا تساويا فلا أعمال ترابية.\n\nمثال: أرض طبيعية `1250` وتصميمي `1180` تعطي `GALIAN 70 cm`؛ وأرض `1100` وتصميمي `1180` تعطي "
    "`TIMBUNAN 80 cm`.",
    "سطران: منسوب الأرض الطبيعية ثم المنسوب التصميمي (سم، عددان صحيحان).",
    "سطر واحد: `GALIAN D cm` (حفر) أو `TIMBUNAN D cm` (ردم) أو `RATA` (مستوٍ)؛ بالإندونيسية.",
    "منسوب الأرض من 500 إلى 3000؛ والفرق لا يزيد على 250."))

py("kelas-jalan", "mudah",
   "b = [499, 500, 2999, 3000, 19999, 20000]\nif i < 6:\n    print(b[i])\nelse:\n"
   "    print(r.choice([r.randint(50, 499), r.randint(500, 2999), r.randint(3000, 19999), r.randint(20000, 80000)]))\n",
   r'''
lhr = int(input())
if lhr < 500:
    print("LINGKUNGAN")
elif lhr < 3000:
    print("LOKAL")
elif lhr < 20000:
    print("KOLEKTOR")
else:
    print("ARTERI")
''',
   ("Kelas Jalan dari Lalu Lintas Harian",
    "Dalam latihan ini kelas fungsi jalan ditentukan dari lalu lintas harian rata-rata (LHR, kendaraan per hari):\n\n"
    "- kurang dari 500: `LINGKUNGAN`\n- 500 sampai kurang dari 3000: `LOKAL`\n- 3000 sampai kurang dari 20000: `KOLEKTOR`\n"
    "- 20000 atau lebih: `ARTERI`\n\nContoh: `3000` menghasilkan `KOLEKTOR`; `499` menghasilkan `LINGKUNGAN`.",
    "Satu baris: LHR (bilangan bulat).",
    "Satu baris: nama kelas jalan.",
    "LHR 50 sampai 80000."),
   ("Road Class from Daily Traffic",
    "In this exercise the functional class of a road is decided from its average daily traffic (LHR, vehicles per day):\n\n"
    "- below 500: `LINGKUNGAN`\n- 500 to below 3000: `LOKAL`\n- 3000 to below 20000: `KOLEKTOR`\n- 20000 or more: `ARTERI`\n\n"
    "Example: `3000` gives `KOLEKTOR`; `499` gives `LINGKUNGAN`.",
    "One line: the daily traffic (a whole number).",
    "One line: the class name (in Indonesian, as listed).",
    "The daily traffic is 50 to 80000."),
   ("صنف الطريق من المرور اليومي",
    "في هذا التمرين يُحدَّد الصنف الوظيفي للطريق من متوسط المرور اليومي (LHR، مركبة في اليوم):\n\n"
    "- أقل من 500: `LINGKUNGAN`\n- من 500 إلى أقل من 3000: `LOKAL`\n- من 3000 إلى أقل من 20000: `KOLEKTOR`\n- 20000 فأكثر: `ARTERI`\n\n"
    "مثال: `3000` يعطي `KOLEKTOR`؛ و`499` يعطي `LINGKUNGAN`.",
    "سطر واحد: المرور اليومي (عدد صحيح).",
    "سطر واحد: اسم الصنف (بالإندونيسية كما في القائمة).",
    "المرور اليومي من 50 إلى 80000."))

py("intensitas-hujan", "mudah",
   "b = [20, 21, 50, 51, 100, 101, 150, 151]\nprint(b[i] if i < 8 else r.randint(1, 300))\n",
   r'''
hujan = int(input())
if hujan <= 20:
    print("RINGAN")
elif hujan <= 50:
    print("SEDANG")
elif hujan <= 100:
    print("LEBAT")
elif hujan <= 150:
    print("SANGAT LEBAT")
else:
    print("EKSTREM")
''',
   ("Kategori Hujan Harian",
    "Curah hujan harian (mm per hari) dikelompokkan sebagai berikut (parameter latihan, mengikuti kategori yang umum dipakai):\n\n"
    "- sampai 20: `RINGAN`\n- lebih dari 20 sampai 50: `SEDANG`\n- lebih dari 50 sampai 100: `LEBAT`\n"
    "- lebih dari 100 sampai 150: `SANGAT LEBAT`\n- lebih dari 150: `EKSTREM`\n\n"
    "Contoh: `50` menghasilkan `SEDANG`; `51` menghasilkan `LEBAT`.",
    "Satu baris: curah hujan harian dalam mm (bilangan bulat).",
    "Satu baris: nama kategori.",
    "Curah hujan 1 sampai 300."),
   ("Daily Rainfall Category",
    "Daily rainfall (mm per day) is grouped as follows (exercise parameters, following commonly used categories):\n\n"
    "- up to 20: `RINGAN`\n- above 20 up to 50: `SEDANG`\n- above 50 up to 100: `LEBAT`\n- above 100 up to 150: `SANGAT LEBAT`\n"
    "- above 150: `EKSTREM`\n\nExample: `50` gives `SEDANG`; `51` gives `LEBAT`.",
    "One line: the daily rainfall in mm (a whole number).",
    "One line: the category name (in Indonesian, as listed).",
    "The rainfall is 1 to 300."),
   ("فئة الهطول اليومي",
    "يُصنَّف الهطول اليومي (مم في اليوم) كما يلي (قيم للتمرين وفق الفئات الشائعة):\n\n"
    "- حتى 20: `RINGAN`\n- أكثر من 20 حتى 50: `SEDANG`\n- أكثر من 50 حتى 100: `LEBAT`\n- أكثر من 100 حتى 150: `SANGAT LEBAT`\n"
    "- أكثر من 150: `EKSTREM`\n\nمثال: `50` يعطي `SEDANG`؛ و`51` يعطي `LEBAT`.",
    "سطر واحد: الهطول اليومي بالمليمتر (عدد صحيح).",
    "سطر واحد: اسم الفئة (بالإندونيسية كما في القائمة).",
    "الهطول من 1 إلى 300."))

py("lajur-terpadat", "mudah",
   "v = r.sample(range(80, 900), 3)\nv.sort()\nu = [v[(k + i) % 3] for k in range(3)]\nif i % 2:\n    u[0], u[1] = u[1], u[0]\n"
   "for x in u:\n    print(x)\n",
   r'''
lajur_1 = int(input())
lajur_2 = int(input())
lajur_3 = int(input())
if lajur_1 > lajur_2 and lajur_1 > lajur_3:
    print(f"Lajur 1: {lajur_1} kendaraan")
elif lajur_2 > lajur_3:
    print(f"Lajur 2: {lajur_2} kendaraan")
else:
    print(f"Lajur 3: {lajur_3} kendaraan")
''',
   ("Lajur Terpadat",
    "Survei lalu lintas mencatat jumlah kendaraan di tiga lajur selama satu jam. Tentukan lajur dengan kendaraan terbanyak dan jumlahnya. "
    "Ketiga jumlah selalu berbeda.\n\nContoh: `310`, `431`, `298` menghasilkan `Lajur 2: 431 kendaraan`.",
    "Tiga baris: jumlah kendaraan di lajur 1, lajur 2, dan lajur 3 (bilangan bulat).",
    "Satu baris berbentuk `Lajur K: N kendaraan`.",
    "Jumlah kendaraan 80 sampai 899, ketiganya berbeda."),
   ("The Busiest Lane",
    "A traffic survey counts the vehicles in three lanes during one hour. Find the lane with the most vehicles and its count. "
    "The three counts are always different.\n\nExample: `310`, `431`, `298` give `Lajur 2: 431 kendaraan`.",
    "Three lines: the vehicle counts of lane 1, lane 2 and lane 3 (whole numbers).",
    "One line of the form `Lajur K: N kendaraan` (Indonesian words).",
    "Counts are 80 to 899, all different."),
   ("الحارة الأكثر ازدحامًا",
    "يسجّل مسح مروري عدد المركبات في ثلاث حارات خلال ساعة. حدِّد الحارة ذات العدد الأكبر وعددها. الأعداد الثلاثة مختلفة دائمًا.\n\n"
    "مثال: `310` و`431` و`298` تعطي `Lajur 2: 431 kendaraan`.",
    "ثلاثة أسطر: عدد المركبات في الحارة 1 ثم 2 ثم 3 (أعداد صحيحة).",
    "سطر واحد بالشكل `Lajur K: N kendaraan` (بالإندونيسية).",
    "الأعداد من 80 إلى 899 وكلها مختلفة."))

py("sewa-alat", "mudah",
   "j = [8, 7, 9][i] if i < 3 else r.randint(1, 16)\nprint(j)\nprint(r.randint(25, 60) * 10000)\n",
   r'''
jam_pakai = int(input())
tarif = int(input())
jam_dibayar = jam_pakai
if jam_dibayar < 8:
    jam_dibayar = 8
print(f"Jam dibayar: {jam_dibayar}")
print(f"Biaya: Rp {jam_dibayar * tarif}")
''',
   ("Sewa Ekskavator dengan Jam Minimum",
    "Sewa ekskavator dihitung per jam, tetapi penyewa selalu membayar paling sedikit 8 jam sehari walaupun alat dipakai lebih singkat. "
    "Hitung jam yang dibayar dan biayanya.\n\nContoh: pemakaian `5` jam dengan tarif `450000` menghasilkan\n\n"
    "```\nJam dibayar: 8\nBiaya: Rp 3600000\n```",
    "Dua baris: jam pemakaian, lalu tarif per jam (rupiah). Keduanya bilangan bulat.",
    "Baris pertama `Jam dibayar: J`. Baris kedua `Biaya: Rp B`.",
    "Jam pemakaian 1 sampai 16; tarif 250000 sampai 600000."),
   ("Excavator Hire with a Minimum Charge",
    "An excavator is hired by the hour, but the hirer always pays for at least 8 hours a day even when the machine is used for less. "
    "Compute the hours charged and the cost.\n\nExample: `5` hours of use at a rate of `450000` give\n\n"
    "```\nJam dibayar: 8\nBiaya: Rp 3600000\n```",
    "Two lines: the hours of use, then the hourly rate (rupiah). Both are whole numbers.",
    "First line `Jam dibayar: J`. Second line `Biaya: Rp B` (Indonesian words).",
    "Hours of use 1 to 16; rate 250000 to 600000."),
   ("استئجار حفّارة بحدّ أدنى للساعات",
    "تُحسب أجرة الحفّارة بالساعة، لكن المستأجر يدفع دائمًا أجرة 8 ساعات على الأقل في اليوم ولو استُعملت الآلة مدة أقصر. "
    "احسب الساعات المدفوعة والتكلفة.\n\nمثال: استعمال `5` ساعات بأجرة `450000` يعطي\n\n```\nJam dibayar: 8\nBiaya: Rp 3600000\n```",
    "سطران: ساعات الاستعمال ثم أجرة الساعة (روبية). كلاهما عدد صحيح.",
    "السطر الأول `Jam dibayar: J`. السطر الثاني `Biaya: Rp B` (بالإندونيسية).",
    "ساعات الاستعمال من 1 إلى 16؛ والأجرة من 250000 إلى 600000."))

py("shift-kerja", "mudah",
   "b = [6, 7, 14, 15, 22, 23, 0]\nprint(b[i] if i < 7 else r.randint(0, 23))\n",
   r'''
jam = int(input())
if jam >= 7 and jam <= 14:
    print("PAGI")
elif jam >= 15 and jam <= 22:
    print("SORE")
else:
    print("MALAM")
''',
   ("Shift Kerja Pengecoran",
    "Proyek bekerja tiga shift. Truk mixer yang tiba pada jam 7 sampai 14 dilayani shift pagi, jam 15 sampai 22 shift sore, dan jam "
    "lainnya (23, serta 0 sampai 6) shift malam. Tentukan shift dari jam kedatangan (angka jamnya saja, 0 sampai 23).\n\n"
    "Contoh: `14` menghasilkan `PAGI`; `23` menghasilkan `MALAM`.",
    "Satu baris: jam kedatangan (bilangan bulat).",
    "Satu baris: `PAGI`, `SORE`, atau `MALAM`.",
    "Jam 0 sampai 23."),
   ("Concreting Work Shift",
    "A project runs three shifts. A mixer truck arriving at hour 7 to 14 is served by the morning shift, hour 15 to 22 by the afternoon "
    "shift, and any other hour (23, and 0 to 6) by the night shift. Decide the shift from the arrival hour (the hour only, 0 to 23).\n\n"
    "Example: `14` gives `PAGI`; `23` gives `MALAM`.",
    "One line: the arrival hour (a whole number).",
    "One line: `PAGI` (morning), `SORE` (afternoon), or `MALAM` (night); Indonesian words.",
    "The hour is 0 to 23."),
   ("وردية صبّ الخرسانة",
    "يعمل المشروع بثلاث ورديات. الشاحنة الخلّاطة التي تصل في الساعة 7 إلى 14 تخدمها وردية الصباح، ومن 15 إلى 22 وردية المساء، "
    "وفي غير ذلك (23، ومن 0 إلى 6) وردية الليل. حدِّد الوردية من ساعة الوصول (رقم الساعة فقط، من 0 إلى 23).\n\n"
    "مثال: `14` يعطي `PAGI`؛ و`23` يعطي `MALAM`.",
    "سطر واحد: ساعة الوصول (عدد صحيح).",
    "سطر واحد: `PAGI` (صباح) أو `SORE` (مساء) أو `MALAM` (ليل)؛ بالإندونيسية.",
    "الساعة من 0 إلى 23."))

# ============================== MUDAH SEDANG (6) ==============================

py("kelas-lereng", "mudah_sedang",
   AMAN + "batas = [(1, 7), (9, 14), (16, 24), (26, 44), (46, 90)]\nwhile True:\n    d = r.randint(10, 200)\n"
   "    lo, hi = batas[i % 5]\n    h = r.randint(max(1, lo * d // 100), max(1, hi * d // 100))\n"
   "    if h <= 150 and all(100 * h != k * d for k in (8, 15, 25, 45)) and aman(h / d * 100, 1):\n        break\n"
   "print(h)\nprint(d)\n",
   r'''
beda_tinggi = int(input())
jarak_datar = int(input())
persen = beda_tinggi / jarak_datar * 100
if persen < 8:
    kelas = "DATAR"
elif persen < 15:
    kelas = "LANDAI"
elif persen < 25:
    kelas = "AGAK CURAM"
elif persen < 45:
    kelas = "CURAM"
else:
    kelas = "SANGAT CURAM"
print(f"{persen:.1f}% {kelas}")
''',
   ("Kelas Kemiringan Lereng",
    "Kemiringan lereng dalam persen adalah beda tinggi dibagi jarak datar, dikali 100. Lereng dikelompokkan (parameter latihan):\n\n"
    "- kurang dari 8%: `DATAR`\n- 8% sampai kurang dari 15%: `LANDAI`\n- 15% sampai kurang dari 25%: `AGAK CURAM`\n"
    "- 25% sampai kurang dari 45%: `CURAM`\n- 45% atau lebih: `SANGAT CURAM`\n\n"
    "Cetak kemiringan dengan satu angka di belakang titik, tanda `%`, satu spasi, lalu kelasnya.\n\n"
    "Contoh: beda tinggi `6` dan jarak datar `50` menghasilkan `12.0% LANDAI`.",
    "Dua baris: beda tinggi (m), lalu jarak datar (m). Keduanya bilangan bulat.",
    "Satu baris berbentuk `P% KELAS`, dengan P satu angka di belakang titik.",
    "Beda tinggi 1 sampai 150; jarak datar 10 sampai 200. Kemiringan tidak pernah tepat di batas kelas."),
   ("Slope Class",
    "The slope in percent is the height difference divided by the horizontal distance, times 100. Slopes are grouped (exercise "
    "parameters):\n\n- below 8%: `DATAR`\n- 8% to below 15%: `LANDAI`\n- 15% to below 25%: `AGAK CURAM`\n"
    "- 25% to below 45%: `CURAM`\n- 45% or more: `SANGAT CURAM`\n\n"
    "Print the slope with one digit after the point, a `%` sign, one space, then the class.\n\n"
    "Example: a height difference of `6` and a horizontal distance of `50` give `12.0% LANDAI`.",
    "Two lines: the height difference (m), then the horizontal distance (m). Both are whole numbers.",
    "One line of the form `P% KELAS`, with P having one digit after the point (class names in Indonesian, as listed).",
    "Height difference 1 to 150; horizontal distance 10 to 200. The slope is never exactly on a class limit."),
   ("صنف ميل المنحدر",
    "ميل المنحدر بالنسبة المئوية هو فرق الارتفاع مقسومًا على المسافة الأفقية مضروبًا في 100. تُصنَّف المنحدرات (قيم للتمرين):\n\n"
    "- أقل من 8%: `DATAR`\n- من 8% إلى أقل من 15%: `LANDAI`\n- من 15% إلى أقل من 25%: `AGAK CURAM`\n"
    "- من 25% إلى أقل من 45%: `CURAM`\n- 45% فأكثر: `SANGAT CURAM`\n\n"
    "اطبع الميل بمنزلة عشرية واحدة ثم علامة `%` ثم مسافة ثم الصنف.\n\nمثال: فرق ارتفاع `6` ومسافة أفقية `50` يعطيان `12.0% LANDAI`.",
    "سطران: فرق الارتفاع (م) ثم المسافة الأفقية (م). كلاهما عدد صحيح.",
    "سطر واحد بالشكل `P% KELAS` حيث P بمنزلة عشرية واحدة (أسماء الأصناف بالإندونيسية كما في القائمة).",
    "فرق الارتفاع من 1 إلى 150؛ والمسافة الأفقية من 10 إلى 200. لا يقع الميل على حدّ صنف تمامًا."))

py("nilai-tengah-kubus", "mudah_sedang",
   "a = r.randint(150, 450)\nb = r.randint(150, 450)\nc = r.randint(150, 450)\nif i == 3:\n    b = a\nif i == 6:\n    c = a\n"
   "if i == 9:\n    b = a\n    c = a\nu = [a, b, c]\nr.shuffle(u)\nfor x in u:\n    print(x)\n",
   r'''
a = int(input())
b = int(input())
c = int(input())
if (a <= b and b <= c) or (c <= b and b <= a):
    tengah = b
elif (b <= a and a <= c) or (c <= a and a <= b):
    tengah = a
else:
    tengah = c
print(tengah)
''',
   ("Nilai Tengah Tiga Kubus Uji",
    "Tiga kubus uji dari adukan yang sama ditekan, dan laporan mencatat nilai tengahnya: nilai yang berada di antara dua nilai lainnya "
    "bila ketiganya diurutkan. Nilai boleh sama.\n\nContoh: `310`, `270`, `290` menghasilkan `290`; `300`, `300`, `250` menghasilkan `300`.",
    "Tiga baris: kuat tekan tiga kubus (kg/cm², bilangan bulat).",
    "Satu baris: nilai tengahnya.",
    "Setiap nilai 150 sampai 450."),
   ("Middle Value of Three Test Cubes",
    "Three test cubes from the same batch are crushed, and the report records the middle value: the one that lies between the other two "
    "when the three are put in order. Values may be equal.\n\nExample: `310`, `270`, `290` give `290`; `300`, `300`, `250` give `300`.",
    "Three lines: the strengths of the three cubes (kg/cm², whole numbers).",
    "One line: the middle value.",
    "Each value is 150 to 450."),
   ("القيمة الوسطى لثلاثة مكعبات اختبار",
    "تُكسر ثلاثة مكعبات اختبار من الخلطة نفسها، ويسجّل التقرير القيمة الوسطى: القيمة الواقعة بين القيمتين الأخريين عند ترتيب الثلاث. "
    "قد تتساوى القيم.\n\nمثال: `310` و`270` و`290` تعطي `290`؛ و`300` و`300` و`250` تعطي `300`.",
    "ثلاثة أسطر: مقاومة المكعبات الثلاثة (كغ/سم²، أعداد صحيحة).",
    "سطر واحد: القيمة الوسطى.",
    "كل قيمة من 150 إلى 450."))

py("terima-beton", "mudah_sedang",
   "f = r.choice([20, 25, 30, 35])\nwhile True:\n    if i % 4 == 0:\n        u = [f + r.randint(-3, 6) for _ in range(3)]\n"
   "    elif i % 4 == 1:\n        u = [f + r.randint(2, 8), f + r.randint(2, 8), f - r.choice([3, 4])]\n"
   "    elif i % 4 == 2:\n        u = [f + r.randint(-3, 1) for _ in range(3)]\n"
   "    else:\n        u = [f + r.randint(4, 9), f + r.randint(4, 9), f - r.randint(4, 6)]\n"
   "    if sum(u) != 3 * f:\n        break\nr.shuffle(u)\nprint(f)\nfor x in u:\n    print(x)\n",
   r'''
rencana = int(input())
uji_1 = int(input())
uji_2 = int(input())
uji_3 = int(input())
rata_rata = (uji_1 + uji_2 + uji_3) / 3
batas_tunggal = rencana - 3.5
print(f"Rata-rata: {rata_rata:.2f} MPa")
if rata_rata >= rencana and uji_1 >= batas_tunggal and uji_2 >= batas_tunggal and uji_3 >= batas_tunggal:
    print("DITERIMA")
else:
    print("DITOLAK")
''',
   ("Penerimaan Mutu Beton",
    "Mutu beton diterima bila dua syarat terpenuhi sekaligus (parameter latihan, disederhanakan dari aturan penerimaan beton):\n\n"
    "1. rata-rata tiga hasil uji tidak kurang dari kuat tekan rencana;\n"
    "2. tidak ada satu pun hasil uji yang lebih rendah dari kuat tekan rencana dikurangi 3.5 MPa.\n\n"
    "Cetak rata-ratanya dengan dua angka di belakang titik, lalu keputusannya.\n\n"
    "Contoh: rencana `25` dengan hasil `26`, `24`, `27` menghasilkan\n\n```\nRata-rata: 25.67 MPa\nDITERIMA\n```\n\n"
    "sedangkan rencana `25` dengan hasil `30`, `21`, `29` menghasilkan `Rata-rata: 26.67 MPa` lalu `DITOLAK`, karena 21 lebih rendah "
    "dari 21.5.",
    "Empat baris: kuat tekan rencana, lalu tiga hasil uji (MPa, bilangan bulat).",
    "Baris pertama `Rata-rata: R MPa` dengan R dua angka di belakang titik. Baris kedua `DITERIMA` atau `DITOLAK`.",
    "Kuat tekan rencana 20 sampai 35; hasil uji 14 sampai 44. Rata-rata tidak pernah tepat sama dengan kuat tekan rencana."),
   ("Concrete Strength Acceptance",
    "A concrete is accepted when two conditions hold together (exercise parameters, simplified from concrete acceptance rules):\n\n"
    "1. the average of three test results is not less than the specified strength;\n"
    "2. no single result is lower than the specified strength minus 3.5 MPa.\n\n"
    "Print the average with two digits after the point, then the decision.\n\n"
    "Example: specified `25` with results `26`, `24`, `27` gives\n\n```\nRata-rata: 25.67 MPa\nDITERIMA\n```\n\n"
    "while specified `25` with results `30`, `21`, `29` gives `Rata-rata: 26.67 MPa` then `DITOLAK`, because 21 is lower than 21.5.",
    "Four lines: the specified strength, then three test results (MPa, whole numbers).",
    "First line `Rata-rata: R MPa` with R having two digits after the point. Second line `DITERIMA` (accepted) or `DITOLAK` (rejected).",
    "Specified strength 20 to 35; results 14 to 44. The average is never exactly equal to the specified strength."),
   ("قبول جودة الخرسانة",
    "تُقبل الخرسانة إذا تحقق شرطان معًا (قيم للتمرين، مبسّطة من قواعد قبول الخرسانة):\n\n"
    "1. متوسط ثلاث نتائج اختبار لا يقل عن المقاومة التصميمية؛\n2. لا توجد نتيجة واحدة أدنى من المقاومة التصميمية ناقص 3.5 ميغاباسكال.\n\n"
    "اطبع المتوسط بمنزلتين عشريتين ثم القرار.\n\nمثال: تصميمية `25` مع النتائج `26` و`24` و`27` تعطي\n\n"
    "```\nRata-rata: 25.67 MPa\nDITERIMA\n```\n\nأما تصميمية `25` مع `30` و`21` و`29` فتعطي `Rata-rata: 26.67 MPa` ثم `DITOLAK` "
    "لأن 21 أدنى من 21.5.",
    "أربعة أسطر: المقاومة التصميمية ثم ثلاث نتائج اختبار (MPa، أعداد صحيحة).",
    "السطر الأول `Rata-rata: R MPa` حيث R بمنزلتين عشريتين. السطر الثاني `DITERIMA` (مقبولة) أو `DITOLAK` (مرفوضة).",
    "المقاومة التصميمية من 20 إلى 35؛ والنتائج من 14 إلى 44. المتوسط لا يساوي المقاومة التصميمية تمامًا."))

py("tarif-air", "mudah_sedang",
   "b = [0, 10, 11, 20, 21]\nprint(b[i] if i < 5 else r.randint(1, 80))\n",
   r'''
pemakaian = int(input())
if pemakaian <= 10:
    biaya_air = pemakaian * 2500
elif pemakaian <= 20:
    biaya_air = 10 * 2500 + (pemakaian - 10) * 4000
else:
    biaya_air = 10 * 2500 + 10 * 4000 + (pemakaian - 20) * 6500
print(f"Tagihan: Rp {biaya_air + 10000}")
''',
   ("Tagihan Air Bertingkat",
    "Perusahaan air minum memakai tarif bertingkat untuk pemakaian sebulan (parameter latihan):\n\n"
    "- 10 m³ pertama: Rp 2500 per m³\n- m³ ke-11 sampai ke-20: Rp 4000 per m³\n- di atas 20 m³: Rp 6500 per m³\n\n"
    "Setiap pelanggan juga membayar biaya beban Rp 10000 per bulan, walaupun tidak memakai air.\n\n"
    "Contoh: pemakaian `25` m³ menghasilkan `Tagihan: Rp 107500` (25000 + 40000 + 32500 + 10000).",
    "Satu baris: pemakaian air sebulan dalam m³ (bilangan bulat).",
    "Satu baris berbentuk `Tagihan: Rp X`.",
    "Pemakaian 0 sampai 80."),
   ("Tiered Water Bill",
    "A water utility uses tiered rates for the monthly consumption (exercise parameters):\n\n"
    "- the first 10 m³: Rp 2500 per m³\n- the 11th to the 20th m³: Rp 4000 per m³\n- above 20 m³: Rp 6500 per m³\n\n"
    "Every customer also pays a fixed charge of Rp 10000 per month, even with no consumption.\n\n"
    "Example: a consumption of `25` m³ gives `Tagihan: Rp 107500` (25000 + 40000 + 32500 + 10000).",
    "One line: the monthly consumption in m³ (a whole number).",
    "One line of the form `Tagihan: Rp X` (the word stays in Indonesian).",
    "Consumption 0 to 80."),
   ("فاتورة مياه بشرائح",
    "تستعمل شركة المياه تعرفة بشرائح للاستهلاك الشهري (قيم للتمرين):\n\n"
    "- أول 10 م³: 2500 روبية لكل م³\n- من المتر المكعب 11 إلى 20: 4000 روبية لكل م³\n- ما فوق 20 م³: 6500 روبية لكل م³\n\n"
    "ويدفع كل مشترك رسمًا ثابتًا قدره 10000 روبية شهريًّا ولو لم يستهلك ماءً.\n\n"
    "مثال: استهلاك `25` م³ يعطي `Tagihan: Rp 107500` (25000 + 40000 + 32500 + 10000).",
    "سطر واحد: الاستهلاك الشهري بالمتر المكعب (عدد صحيح).",
    "سطر واحد بالشكل `Tagihan: Rp X` (تبقى الكلمة بالإندونيسية).",
    "الاستهلاك من 0 إلى 80."))

py("bilangan-reynolds", "mudah_sedang",
   "while True:\n    d = r.randint(10, 600) if i % 3 == 2 else r.randint(10, 150)\n    if i % 3 == 0:\n        v = r.randint(1, max(1, 199 // d))\n"
   "    elif i % 3 == 1:\n        v = r.randint(200 // d + 1, max(200 // d + 1, 400 // d))\n    else:\n        v = r.randint(400 // d + 1, 300)\n"
   "    if v * d not in (200, 400) and 1 <= v <= 300:\n        break\nprint(f'{v / 100:.2f}')\nprint(d)\n",
   r'''
kecepatan = float(input())
diameter_mm = int(input())
diameter_m = diameter_mm / 1000
reynolds = kecepatan * diameter_m / 0.000001
print(f"Re = {reynolds:.0f}")
if reynolds < 2000:
    print("LAMINAR")
elif reynolds <= 4000:
    print("TRANSISI")
else:
    print("TURBULEN")
''',
   ("Jenis Aliran dalam Pipa",
    "Bilangan Reynolds aliran air dalam pipa adalah Re = v × D / ν, dengan v kecepatan (m/detik), D diameter pipa dalam meter, dan "
    "ν = 0.000001 m²/detik (kekentalan kinematik air, parameter latihan). Diameter diberikan dalam milimeter, jadi ubah dulu ke meter.\n\n"
    "- Re kurang dari 2000: `LAMINAR`\n- Re 2000 sampai 4000: `TRANSISI`\n- Re lebih dari 4000: `TURBULEN`\n\n"
    "Cetak Re tanpa angka di belakang titik (format `:.0f`), lalu jenis alirannya.\n\n"
    "Contoh: v = `0.15` dan D = `20` menghasilkan\n\n```\nRe = 3000\nTRANSISI\n```",
    "Dua baris: kecepatan v (m/detik, bilangan pecahan dengan dua angka di belakang titik), lalu diameter D (mm, bilangan bulat).",
    "Baris pertama `Re = R` dengan R tanpa angka di belakang titik. Baris kedua jenis aliran.",
    "v 0.01 sampai 3.00; D 10 sampai 600. Re tidak pernah tepat 2000 atau 4000."),
   ("Flow Regime in a Pipe",
    "The Reynolds number of water flowing in a pipe is Re = v × D / ν, where v is the velocity (m/s), D the pipe diameter in metres, and "
    "ν = 0.000001 m²/s (kinematic viscosity of water, an exercise parameter). The diameter is given in millimetres, so convert it to "
    "metres first.\n\n- Re below 2000: `LAMINAR`\n- Re 2000 to 4000: `TRANSISI`\n- Re above 4000: `TURBULEN`\n\n"
    "Print Re with no digits after the point (format `:.0f`), then the regime.\n\n"
    "Example: v = `0.15` and D = `20` give\n\n```\nRe = 3000\nTRANSISI\n```",
    "Two lines: the velocity v (m/s, a decimal number with two digits after the point), then the diameter D (mm, a whole number).",
    "First line `Re = R` with R having no digits after the point. Second line the regime (in Indonesian, as listed).",
    "v is 0.01 to 3.00; D is 10 to 600. Re is never exactly 2000 or 4000."),
   ("نوع الجريان في أنبوب",
    "عدد رينولدز لجريان الماء في أنبوب هو Re = v × D / ν، حيث v السرعة (م/ث) و D قطر الأنبوب بالمتر و ν = 0.000001 م²/ث "
    "(اللزوجة الكينماتيكية للماء، قيمة للتمرين). القطر معطى بالمليمتر فحوِّله إلى المتر أولًا.\n\n"
    "- Re أقل من 2000: `LAMINAR`\n- Re من 2000 إلى 4000: `TRANSISI`\n- Re أكبر من 4000: `TURBULEN`\n\n"
    "اطبع Re دون منازل عشرية (التنسيق `:.0f`) ثم نوع الجريان.\n\nمثال: v = `0.15` و D = `20` يعطيان\n\n```\nRe = 3000\nTRANSISI\n```",
    "سطران: السرعة v (م/ث، عدد عشري بمنزلتين) ثم القطر D (مم، عدد صحيح).",
    "السطر الأول `Re = R` حيث R دون منازل عشرية. السطر الثاني نوع الجريان (بالإندونيسية كما في القائمة).",
    "v من 0.01 إلى 3.00؛ D من 10 إلى 600. لا يساوي Re القيمة 2000 أو 4000 تمامًا."))

py("upah-lembur", "mudah_sedang",
   "b = [40, 41, 48, 49]\nprint(b[i] if i < 4 else r.randint(20, 70))\nprint(r.randint(15, 40) * 1000)\n",
   r'''
jam_kerja = int(input())
upah_per_jam = int(input())
if jam_kerja <= 40:
    upah = jam_kerja * upah_per_jam
elif jam_kerja <= 48:
    upah = 40 * upah_per_jam + (jam_kerja - 40) * upah_per_jam * 3 // 2
else:
    upah = 40 * upah_per_jam + 8 * upah_per_jam * 3 // 2 + (jam_kerja - 48) * upah_per_jam * 2
print(f"Upah: Rp {upah}")
''',
   ("Upah Mingguan dengan Lembur",
    "Seorang tukang dibayar per jam. Aturan lembur proyek (parameter latihan):\n\n"
    "- 40 jam pertama dalam seminggu dibayar dengan upah biasa;\n- jam ke-41 sampai ke-48 dibayar 1.5 kali upah biasa;\n"
    "- jam di atas 48 dibayar 2 kali upah biasa.\n\n"
    "Upah per jam selalu kelipatan 1000 rupiah, sehingga hasilnya bilangan bulat; cetak tanpa angka di belakang titik.\n\n"
    "Contoh: `50` jam dengan upah `20000` menghasilkan `Upah: Rp 1120000` (800000 + 240000 + 80000).",
    "Dua baris: jam kerja seminggu, lalu upah per jam (rupiah). Keduanya bilangan bulat.",
    "Satu baris berbentuk `Upah: Rp X`, dengan X bilangan bulat.",
    "Jam kerja 20 sampai 70; upah per jam 15000 sampai 40000."),
   ("Weekly Wage with Overtime",
    "A worker is paid by the hour. The project's overtime rule (exercise parameters):\n\n"
    "- the first 40 hours in a week are paid at the normal rate;\n- the 41st to the 48th hour are paid at 1.5 times the normal rate;\n"
    "- hours above 48 are paid at 2 times the normal rate.\n\n"
    "The hourly wage is always a multiple of 1000 rupiah, so the result is a whole number; print it without a decimal point.\n\n"
    "Example: `50` hours at `20000` give `Upah: Rp 1120000` (800000 + 240000 + 80000).",
    "Two lines: the hours worked in the week, then the hourly wage (rupiah). Both are whole numbers.",
    "One line of the form `Upah: Rp X`, where X is a whole number (the word stays in Indonesian).",
    "Hours 20 to 70; hourly wage 15000 to 40000."),
   ("الأجر الأسبوعي مع العمل الإضافي",
    "يُدفع لعامل بالساعة. قاعدة العمل الإضافي في المشروع (قيم للتمرين):\n\n"
    "- أول 40 ساعة في الأسبوع بالأجر العادي؛\n- من الساعة 41 إلى 48 بمرة ونصف الأجر العادي؛\n- ما فوق 48 ساعة بضعف الأجر العادي.\n\n"
    "أجر الساعة دائمًا من مضاعفات 1000 روبية، فالناتج عدد صحيح؛ اطبعه دون فاصلة عشرية.\n\n"
    "مثال: `50` ساعة بأجر `20000` تعطي `Upah: Rp 1120000` (800000 + 240000 + 80000).",
    "سطران: ساعات العمل في الأسبوع ثم أجر الساعة (روبية). كلاهما عدد صحيح.",
    "سطر واحد بالشكل `Upah: Rp X` حيث X عدد صحيح (تبقى الكلمة بالإندونيسية).",
    "الساعات من 20 إلى 70؛ وأجر الساعة من 15000 إلى 40000."))

# ================================ SEDANG (6) ================================

py("segitiga-patok", "sedang",
   "k = r.randint(1, 40)\nif i % 4 == 0:\n    a, b, c = r.choice([(3, 4, 5), (5, 12, 13), (8, 15, 17), (7, 24, 25), (20, 21, 29)])\n"
   "    u = [a * k, b * k, c * k]\nelif i % 4 == 1:\n    a = r.randint(50, 900)\n    b = r.randint(50, 900)\n"
   "    u = [a, b, a + b + r.choice([0, 0, 1, 37])]\nelse:\n    while True:\n        u = sorted(r.randint(60, 1200) for _ in range(3))\n"
   "        s = u[0] ** 2 + u[1] ** 2 - u[2] ** 2\n        if u[0] + u[1] > u[2] and ((i % 4 == 2 and s > 0) or (i % 4 == 3 and s < 0)):\n"
   "            break\nr.shuffle(u)\nfor x in u:\n    print(x)\n",
   r'''
a = int(input())
b = int(input())
c = int(input())
if b > a and b >= c:
    a, b = b, a
elif c > a and c > b:
    a, c = c, a
if a >= b + c:
    print("BUKAN SEGITIGA")
elif a * a == b * b + c * c:
    print("SIKU-SIKU")
elif a * a < b * b + c * c:
    print("LANCIP")
else:
    print("TUMPUL")
''',
   ("Pemeriksaan Siku Tiga Patok",
    "Tukang memeriksa kesikuan sudut bangunan dengan mengukur tiga sisi segitiga di antara tiga patok. Dari tiga panjang sisi (urutannya "
    "bebas), tentukan jenis segitiganya. Misalkan p sisi terpanjang dan q, s dua sisi lainnya:\n\n"
    "- bila p sama dengan atau lebih dari q + s, ketiga panjang itu tidak membentuk segitiga: `BUKAN SEGITIGA`\n"
    "- bila p² = q² + s²: `SIKU-SIKU`\n- bila p² < q² + s²: `LANCIP`\n- bila p² > q² + s²: `TUMPUL`\n\n"
    "Contoh: `300`, `500`, `400` menghasilkan `SIKU-SIKU`; `2`, `6`, `3` menghasilkan `BUKAN SEGITIGA`.",
    "Tiga baris: panjang tiga sisi (cm, bilangan bulat), dalam urutan apa pun.",
    "Satu baris: `BUKAN SEGITIGA`, `SIKU-SIKU`, `LANCIP`, atau `TUMPUL`.",
    "Setiap panjang 1 sampai 2000."),
   ("Squareness Check with Three Stakes",
    "A builder checks that a corner is square by measuring the three sides of the triangle between three stakes. From three side lengths "
    "(in any order), decide the kind of triangle. Let p be the longest side and q, s the other two:\n\n"
    "- if p is equal to or greater than q + s, the lengths do not form a triangle: `BUKAN SEGITIGA`\n"
    "- if p² = q² + s²: `SIKU-SIKU` (right)\n- if p² < q² + s²: `LANCIP` (acute)\n- if p² > q² + s²: `TUMPUL` (obtuse)\n\n"
    "Example: `300`, `500`, `400` give `SIKU-SIKU`; `2`, `6`, `3` give `BUKAN SEGITIGA`.",
    "Three lines: the three side lengths (cm, whole numbers), in any order.",
    "One line: `BUKAN SEGITIGA`, `SIKU-SIKU`, `LANCIP`, or `TUMPUL` (Indonesian words).",
    "Each length is 1 to 2000."),
   ("فحص التعامد بثلاثة أوتاد",
    "يتحقق البنّاء من تعامد زاوية المبنى بقياس أضلاع المثلث الثلاثة بين ثلاثة أوتاد. من ثلاثة أطوال (بأي ترتيب) حدِّد نوع المثلث. "
    "ليكن p أطول ضلع و q و s الضلعين الآخرين:\n\n"
    "- إذا كان p مساويًا لـ q + s أو أكبر فالأطوال لا تشكّل مثلثًا: `BUKAN SEGITIGA`\n- إذا كان p² = q² + s²: `SIKU-SIKU` (قائم)\n"
    "- إذا كان p² < q² + s²: `LANCIP` (حادّ)\n- إذا كان p² > q² + s²: `TUMPUL` (منفرج)\n\n"
    "مثال: `300` و`500` و`400` تعطي `SIKU-SIKU`؛ و`2` و`6` و`3` تعطي `BUKAN SEGITIGA`.",
    "ثلاثة أسطر: أطوال الأضلاع الثلاثة (سم، أعداد صحيحة) بأي ترتيب.",
    "سطر واحد: `BUKAN SEGITIGA` أو `SIKU-SIKU` أو `LANCIP` أو `TUMPUL` (بالإندونيسية).",
    "كل طول من 1 إلى 2000."))

py("kuadran-azimut", "sedang",
   "b = [0, 90, 180, 270, 359, 91, 181, 269]\nprint(b[i] if i < 8 else r.randint(0, 359))\n",
   r'''
azimut = int(input())
if azimut <= 90:
    print(f"U {azimut} T")
elif azimut <= 180:
    print(f"S {180 - azimut} T")
elif azimut < 270:
    print(f"S {azimut - 180} B")
else:
    print(f"U {360 - azimut} B")
''',
   ("Azimut ke Sudut Jurusan Kuadran",
    "Azimut diukur dari arah utara searah jarum jam, 0 sampai 359 derajat. Peta lama menuliskannya sebagai sudut kuadran: sudut 0 sampai "
    "90 derajat yang diukur dari utara (U) atau selatan (S) ke arah timur (T) atau barat (B). Aturannya:\n\n"
    "- azimut 0 sampai 90: `U a T`, dengan a = azimut\n- azimut lebih dari 90 sampai 180: `S a T`, dengan a = 180 − azimut\n"
    "- azimut lebih dari 180 dan kurang dari 270: `S a B`, dengan a = azimut − 180\n"
    "- azimut 270 sampai 359: `U a B`, dengan a = 360 − azimut\n\n"
    "Contoh: `135` menghasilkan `S 45 T`; `300` menghasilkan `U 60 B`; `180` menghasilkan `S 0 T`.",
    "Satu baris: azimut dalam derajat (bilangan bulat).",
    "Satu baris berbentuk `H a A`: huruf U atau S, sudut a, huruf T atau B, dipisah satu spasi.",
    "Azimut 0 sampai 359."),
   ("Azimuth to Quadrant Bearing",
    "An azimuth is measured clockwise from north, 0 to 359 degrees. Old maps write it as a quadrant bearing: an angle of 0 to 90 degrees "
    "measured from north (U) or south (S) towards east (T) or west (B). The rules:\n\n"
    "- azimuth 0 to 90: `U a T`, with a = azimuth\n- azimuth above 90 up to 180: `S a T`, with a = 180 − azimuth\n"
    "- azimuth above 180 and below 270: `S a B`, with a = azimuth − 180\n- azimuth 270 to 359: `U a B`, with a = 360 − azimuth\n\n"
    "Example: `135` gives `S 45 T`; `300` gives `U 60 B`; `180` gives `S 0 T`.",
    "One line: the azimuth in degrees (a whole number).",
    "One line of the form `H a A`: the letter U or S, the angle a, the letter T or B, separated by single spaces (Indonesian letters: "
    "U north, S south, T east, B west).",
    "The azimuth is 0 to 359."),
   ("من السمت إلى اتجاه الربع",
    "يُقاس السمت من الشمال باتجاه عقارب الساعة من 0 إلى 359 درجة. تكتبه الخرائط القديمة اتجاهًا ربعيًّا: زاوية من 0 إلى 90 درجة تُقاس من "
    "الشمال (U) أو الجنوب (S) نحو الشرق (T) أو الغرب (B). القواعد:\n\n"
    "- السمت من 0 إلى 90: `U a T` حيث a = السمت\n- السمت أكبر من 90 حتى 180: `S a T` حيث a = 180 − السمت\n"
    "- السمت أكبر من 180 وأقل من 270: `S a B` حيث a = السمت − 180\n- السمت من 270 إلى 359: `U a B` حيث a = 360 − السمت\n\n"
    "مثال: `135` يعطي `S 45 T`؛ و`300` يعطي `U 60 B`؛ و`180` يعطي `S 0 T`.",
    "سطر واحد: السمت بالدرجات (عدد صحيح).",
    "سطر واحد بالشكل `H a A`: الحرف U أو S ثم الزاوية a ثم الحرف T أو B، تفصل بينها مسافة واحدة "
    "(الحروف بالإندونيسية: U شمال، S جنوب، T شرق، B غرب).",
    "السمت من 0 إلى 359."))

py("rasio-tulangan", "sedang",
   AMAN + "while True:\n    b = r.choice([200, 250, 300, 350, 400])\n    d = r.choice([340, 390, 440, 490, 540, 640])\n"
   "    fy = r.choice([280, 400, 420])\n    lo, hi = [(0.0010, 1.3 / fy), (1.5 / fy, 0.0240), (0.0260, 0.0400)][i % 3]\n"
   "    a = int(r.uniform(lo, hi) * b * d)\n    rho = a / (b * d)\n"
   "    if a > 0 and abs(rho - 1.4 / fy) > 0.00001 and abs(rho - 0.025) > 0.00001 and aman(rho, 4):\n        break\n"
   "print(a)\nprint(b)\nprint(d)\nprint(fy)\n",
   r'''
luas_tulangan = int(input())
lebar = int(input())
tinggi_efektif = int(input())
fy = int(input())
rho = luas_tulangan / (lebar * tinggi_efektif)
rho_min = 1.4 / fy
print(f"rho = {rho:.4f}")
if rho < rho_min:
    print("KURANG")
elif rho > 0.025:
    print("BERLEBIH")
else:
    print("MEMENUHI")
''',
   ("Rasio Tulangan Balok",
    "Rasio tulangan balok adalah ρ = As / (b × d), dengan As luas tulangan tarik (mm²), b lebar balok (mm), dan d tinggi efektif (mm). "
    "Dalam latihan ini rasio itu harus berada di antara dua batas:\n\n"
    "- batas bawah ρ min = 1.4 / fy, dengan fy tegangan leleh baja (MPa);\n- batas atas ρ maks = 0.025.\n\n"
    "Bila ρ lebih kecil dari ρ min, tulangan `KURANG`. Bila ρ lebih besar dari ρ maks, tulangan `BERLEBIH`. Selain itu `MEMENUHI`.\n\n"
    "Contoh: As = `1200`, b = `300`, d = `500`, fy = `400` menghasilkan\n\n```\nrho = 0.0080\nMEMENUHI\n```\n\n"
    "karena ρ min = 0.0035.",
    "Empat baris: As (mm²), b (mm), d (mm), fy (MPa). Semuanya bilangan bulat.",
    "Baris pertama `rho = R` dengan R empat angka di belakang titik. Baris kedua `KURANG`, `MEMENUHI`, atau `BERLEBIH`.",
    "b 200 sampai 400; d 340 sampai 640; fy 280, 400, atau 420. ρ tidak pernah tepat di salah satu batas."),
   ("Beam Reinforcement Ratio",
    "The reinforcement ratio of a beam is ρ = As / (b × d), where As is the area of tension steel (mm²), b the beam width (mm) and d the "
    "effective depth (mm). In this exercise the ratio must lie between two limits:\n\n"
    "- lower limit ρ min = 1.4 / fy, where fy is the steel yield stress (MPa);\n- upper limit ρ max = 0.025.\n\n"
    "If ρ is smaller than ρ min the steel is `KURANG` (too little). If ρ is larger than ρ max it is `BERLEBIH` (too much). Otherwise "
    "`MEMENUHI`.\n\nExample: As = `1200`, b = `300`, d = `500`, fy = `400` give\n\n```\nrho = 0.0080\nMEMENUHI\n```\n\n"
    "because ρ min = 0.0035.",
    "Four lines: As (mm²), b (mm), d (mm), fy (MPa). All are whole numbers.",
    "First line `rho = R` with R having four digits after the point. Second line `KURANG`, `MEMENUHI`, or `BERLEBIH`.",
    "b is 200 to 400; d is 340 to 640; fy is 280, 400 or 420. ρ is never exactly on a limit."),
   ("نسبة تسليح الجائز",
    "نسبة تسليح الجائز هي ρ = As / (b × d)، حيث As مساحة حديد الشدّ (مم²) و b عرض الجائز (مم) و d العمق الفعّال (مم). "
    "في هذا التمرين يجب أن تقع النسبة بين حدّين:\n\n"
    "- الحد الأدنى ρ min = 1.4 / fy حيث fy إجهاد خضوع الحديد (MPa)؛\n- الحد الأعلى ρ max = 0.025.\n\n"
    "إذا كانت ρ أصغر من ρ min فالتسليح `KURANG` (ناقص)، وإذا كانت أكبر من ρ max فهو `BERLEBIH` (زائد)، وإلا فهو `MEMENUHI`.\n\n"
    "مثال: As = `1200` و b = `300` و d = `500` و fy = `400` تعطي\n\n```\nrho = 0.0080\nMEMENUHI\n```\n\nلأن ρ min = 0.0035.",
    "أربعة أسطر: As (مم²)، b (مم)، d (مم)، fy (MPa). كلها أعداد صحيحة.",
    "السطر الأول `rho = R` حيث R بأربع منازل عشرية. السطر الثاني `KURANG` أو `MEMENUHI` أو `BERLEBIH`.",
    "b من 200 إلى 400؛ d من 340 إلى 640؛ fy يساوي 280 أو 400 أو 420. لا تقع ρ على أحد الحدّين تمامًا."))

py("klasifikasi-tanah", "sedang",
   "if i % 2 == 0:\n    f = [50, 51, 100][i // 2] if i < 6 else r.randint(50, 100)\n    k = r.randint(0, (100 - f) // 2)\n"
   "    ll = [49, 50, 35, 70, 28, 55][i // 2]\n    pi = r.randint(0, ll - 12) if i != 8 else 7\nelse:\n"
   "    f = [4, 5, 49][i // 2] if i < 6 else r.randint(0, 49)\n    kasar = 100 - f\n"
   "    k = [kasar // 2, kasar // 2 + 1][i // 2 % 2] if i < 5 else r.randint(0, kasar)\n    ll = r.randint(15, 60)\n    pi = r.randint(0, 10)\n"
   "print(f)\nprint(k)\nprint(ll)\nprint(pi)\n",
   r'''
lolos_200 = int(input())
tertahan_4 = int(input())
batas_cair = int(input())
indeks_plastis = int(input())
if lolos_200 >= 50:
    garis_a = 0.73 * (batas_cair - 20)
    if batas_cair < 50:
        if indeks_plastis > 7 and indeks_plastis >= garis_a:
            print("CL")
        else:
            print("ML")
    else:
        if indeks_plastis >= garis_a:
            print("CH")
        else:
            print("MH")
else:
    fraksi_kasar = 100 - lolos_200
    if tertahan_4 > fraksi_kasar / 2:
        jenis = "KERIKIL"
    else:
        jenis = "PASIR"
    if lolos_200 < 5:
        print(f"{jenis} BERSIH")
    else:
        print(f"{jenis} DENGAN BUTIR HALUS")
''',
   ("Klasifikasi Tanah Sederhana",
    "Laboratorium mengelompokkan contoh tanah dengan aturan yang disederhanakan dari sistem USCS (parameter latihan). Datanya: F = persen "
    "lolos saringan No. 200, K = persen tertahan saringan No. 4, LL = batas cair, PI = indeks plastisitas.\n\n"
    "**Bila F 50 atau lebih** (tanah berbutir halus), hitung garis A = 0.73 × (LL − 20):\n\n"
    "- LL kurang dari 50: `CL` bila PI lebih dari 7 dan PI tidak kurang dari garis A; selain itu `ML`\n"
    "- LL 50 atau lebih: `CH` bila PI tidak kurang dari garis A; selain itu `MH`\n\n"
    "**Bila F kurang dari 50** (tanah berbutir kasar), fraksi kasarnya 100 − F:\n\n"
    "- jenisnya `KERIKIL` bila K lebih dari setengah fraksi kasar; selain itu `PASIR`\n"
    "- tambahkan `BERSIH` bila F kurang dari 5; selain itu tambahkan `DENGAN BUTIR HALUS`\n\n"
    "Contoh: F = `60`, K = `0`, LL = `40`, PI = `18` menghasilkan `CL` (garis A = 14.6). F = `3`, K = `60`, LL = `20`, PI = `2` "
    "menghasilkan `KERIKIL BERSIH` (setengah fraksi kasar = 48.5).",
    "Empat baris: F, K, LL, PI. Semuanya bilangan bulat.",
    "Satu baris: `CL`, `ML`, `CH`, `MH`, atau jenis tanah kasar diikuti keterangannya, misalnya `PASIR DENGAN BUTIR HALUS`.",
    "F 0 sampai 100; K 0 sampai 100 − F; LL 15 sampai 70; PI 0 sampai 58."),
   ("Simple Soil Classification",
    "A laboratory groups soil samples with rules simplified from the USCS system (exercise parameters). The data: F = percent passing "
    "the No. 200 sieve, K = percent retained on the No. 4 sieve, LL = liquid limit, PI = plasticity index.\n\n"
    "**If F is 50 or more** (fine-grained soil), compute the A-line = 0.73 × (LL − 20):\n\n"
    "- LL below 50: `CL` if PI is above 7 and PI is not below the A-line; otherwise `ML`\n"
    "- LL 50 or more: `CH` if PI is not below the A-line; otherwise `MH`\n\n"
    "**If F is below 50** (coarse-grained soil), the coarse fraction is 100 − F:\n\n"
    "- the kind is `KERIKIL` (gravel) if K is more than half the coarse fraction; otherwise `PASIR` (sand)\n"
    "- append `BERSIH` (clean) if F is below 5; otherwise append `DENGAN BUTIR HALUS` (with fines)\n\n"
    "Example: F = `60`, K = `0`, LL = `40`, PI = `18` give `CL` (A-line = 14.6). F = `3`, K = `60`, LL = `20`, PI = `2` give "
    "`KERIKIL BERSIH` (half the coarse fraction = 48.5).",
    "Four lines: F, K, LL, PI. All are whole numbers.",
    "One line: `CL`, `ML`, `CH`, `MH`, or the coarse kind followed by its note, for example `PASIR DENGAN BUTIR HALUS`.",
    "F is 0 to 100; K is 0 to 100 − F; LL is 15 to 70; PI is 0 to 58."),
   ("تصنيف مبسّط للتربة",
    "يصنّف المختبر عيّنات التربة بقواعد مبسّطة من نظام USCS (قيم للتمرين). البيانات: F = نسبة المارّ من المنخل رقم 200، "
    "K = نسبة المحجوز على المنخل رقم 4، LL = حدّ السيولة، PI = مؤشر اللدونة.\n\n"
    "**إذا كان F يساوي 50 أو أكثر** (تربة ناعمة) فاحسب الخط A = 0.73 × (LL − 20):\n\n"
    "- LL أقل من 50: `CL` إذا كان PI أكبر من 7 و PI لا يقل عن الخط A؛ وإلا `ML`\n"
    "- LL يساوي 50 أو أكثر: `CH` إذا كان PI لا يقل عن الخط A؛ وإلا `MH`\n\n"
    "**إذا كان F أقل من 50** (تربة خشنة) فالجزء الخشن يساوي 100 − F:\n\n"
    "- النوع `KERIKIL` (حصى) إذا كان K أكبر من نصف الجزء الخشن؛ وإلا `PASIR` (رمل)\n"
    "- أضف `BERSIH` (نظيف) إذا كان F أقل من 5؛ وإلا أضف `DENGAN BUTIR HALUS` (مع نواعم)\n\n"
    "مثال: F = `60` و K = `0` و LL = `40` و PI = `18` تعطي `CL` (الخط A = 14.6). و F = `3` و K = `60` و LL = `20` و PI = `2` تعطي "
    "`KERIKIL BERSIH` (نصف الجزء الخشن = 48.5).",
    "أربعة أسطر: F ثم K ثم LL ثم PI. كلها أعداد صحيحة.",
    "سطر واحد: `CL` أو `ML` أو `CH` أو `MH`، أو نوع التربة الخشنة متبوعًا بوصفه، مثل `PASIR DENGAN BUTIR HALUS`.",
    "F من 0 إلى 100؛ K من 0 إلى 100 − F؛ LL من 15 إلى 70؛ PI من 0 إلى 58."))

py("cek-saluran", "sedang",
   AMAN + "while True:\n    b = r.randint(4, 30) * 10\n    h = r.randint(5, 30) * 4\n"
   "    v = [r.uniform(0.1, 0.55), r.uniform(0.65, 2.9), r.uniform(3.1, 5.0)][i % 3]\n    q = int(v * b * h / 10)\n"
   "    if q < 1:\n        continue\n    if 100 * q != 6 * b * h and 10 * q != 3 * b * h and aman(10 * q / (b * h), 2):\n        break\n"
   "j = [h // 4, h // 4 - 1, h // 4 + 1][i % 3] if i < 6 else r.randint(5, h // 2)\nprint(b)\nprint(h)\nprint(h + max(1, j))\nprint(q)\n",
   r'''
lebar_cm = int(input())
air_cm = int(input())
tinggi_cm = int(input())
debit_liter = int(input())
luas_m2 = (lebar_cm / 100) * (air_cm / 100)
kecepatan = (debit_liter / 1000) / luas_m2
print(f"v = {kecepatan:.2f} m/detik")
if kecepatan < 0.6:
    print("MENGENDAP")
elif kecepatan > 3.0:
    print("MENGGERUS")
else:
    print("KECEPATAN AMAN")
jagaan = tinggi_cm - air_cm
if jagaan >= air_cm / 4:
    print("JAGAAN CUKUP")
else:
    print("JAGAAN KURANG")
''',
   ("Pemeriksaan Saluran Drainase",
    "Saluran persegi berlebar b dan bertinggi H mengalirkan debit Q dengan kedalaman air h. Lakukan dua pemeriksaan (parameter latihan).\n\n"
    "**Kecepatan.** v = Q / A, dengan A = b × h. Ubah dulu satuannya: b dan h dari cm ke m, Q dari liter/detik ke m³/detik.\n\n"
    "- v kurang dari 0.6 m/detik: `MENGENDAP` (lumpur mengendap)\n- v lebih dari 3.0 m/detik: `MENGGERUS` (dinding tergerus)\n"
    "- selain itu: `KECEPATAN AMAN`\n\n"
    "**Tinggi jagaan.** Jagaan = H − h. Jagaan cukup bila tidak kurang dari seperempat kedalaman air: `JAGAAN CUKUP`; selain itu "
    "`JAGAAN KURANG`.\n\n"
    "Contoh: b = `80`, h = `50`, H = `65`, Q = `600` menghasilkan\n\n```\nv = 1.50 m/detik\nKECEPATAN AMAN\nJAGAAN CUKUP\n```\n\n"
    "(jagaan 15 cm, seperempat kedalaman air 12.5 cm).",
    "Empat baris: b (cm), h (cm), H (cm), Q (liter/detik). Semuanya bilangan bulat.",
    "Tiga baris: `v = V m/detik` dengan V dua angka di belakang titik; status kecepatan; status jagaan.",
    "b 40 sampai 300; h 20 sampai 120; H lebih besar dari h. Kecepatan tidak pernah tepat 0.6 atau 3.0."),
   ("Drainage Channel Check",
    "A rectangular channel of width b and height H carries a discharge Q with a water depth h. Make two checks (exercise parameters).\n\n"
    "**Velocity.** v = Q / A, with A = b × h. Convert the units first: b and h from cm to m, Q from litres/s to m³/s.\n\n"
    "- v below 0.6 m/s: `MENGENDAP` (silt settles)\n- v above 3.0 m/s: `MENGGERUS` (the lining erodes)\n- otherwise: `KECEPATAN AMAN`\n\n"
    "**Freeboard.** Freeboard = H − h. It is sufficient when it is not less than a quarter of the water depth: `JAGAAN CUKUP`; "
    "otherwise `JAGAAN KURANG`.\n\n"
    "Example: b = `80`, h = `50`, H = `65`, Q = `600` give\n\n```\nv = 1.50 m/detik\nKECEPATAN AMAN\nJAGAAN CUKUP\n```\n\n"
    "(freeboard 15 cm, a quarter of the water depth 12.5 cm).",
    "Four lines: b (cm), h (cm), H (cm), Q (litres/s). All are whole numbers.",
    "Three lines: `v = V m/detik` with V having two digits after the point; the velocity status; the freeboard status (Indonesian words).",
    "b is 40 to 300; h is 20 to 120; H is greater than h. The velocity is never exactly 0.6 or 3.0."),
   ("فحص قناة تصريف",
    "قناة مستطيلة عرضها b وارتفاعها H تنقل تصريفًا Q بعمق ماء h. أجرِ فحصين (قيم للتمرين).\n\n"
    "**السرعة.** v = Q / A حيث A = b × h. حوِّل الوحدات أولًا: b و h من السنتيمتر إلى المتر، و Q من لتر/ث إلى م³/ث.\n\n"
    "- v أقل من 0.6 م/ث: `MENGENDAP` (يترسّب الطمي)\n- v أكبر من 3.0 م/ث: `MENGGERUS` (تتآكل الجدران)\n- غير ذلك: `KECEPATAN AMAN`\n\n"
    "**الارتفاع الحرّ.** الارتفاع الحرّ = H − h. يكون كافيًا إذا لم يقلّ عن ربع عمق الماء: `JAGAAN CUKUP`؛ وإلا `JAGAAN KURANG`.\n\n"
    "مثال: b = `80` و h = `50` و H = `65` و Q = `600` تعطي\n\n```\nv = 1.50 m/detik\nKECEPATAN AMAN\nJAGAAN CUKUP\n```\n\n"
    "(الارتفاع الحرّ 15 سم وربع عمق الماء 12.5 سم).",
    "أربعة أسطر: b (سم)، h (سم)، H (سم)، Q (لتر/ث). كلها أعداد صحيحة.",
    "ثلاثة أسطر: `v = V m/detik` حيث V بمنزلتين عشريتين؛ ثم حالة السرعة؛ ثم حالة الارتفاع الحرّ (بالإندونيسية).",
    "b من 40 إلى 300؛ h من 20 إلى 120؛ H أكبر من h. السرعة لا تساوي 0.6 أو 3.0 تمامًا."))

py("kombinasi-beban", "sedang",
   "while True:\n    d = r.randint(20, 400)\n    if i % 3 == 0:\n        l = r.randint(0, d // 14)\n        w = r.randint(0, d // 14)\n"
   "    elif i % 3 == 1:\n        l = r.randint(d // 4, 300)\n        w = r.randint(0, l // 4)\n    else:\n        l = r.randint(0, 150)\n"
   "        w = r.randint(l // 2 + 5, 250)\n    u = sorted([14 * d, 12 * d + 16 * l, 12 * d + 10 * l + 16 * w])\n"
   "    if u[2] != u[1]:\n        break\nprint(d)\nprint(l)\nprint(w)\n",
   r'''
beban_mati = int(input())
beban_hidup = int(input())
beban_angin = int(input())
u1 = 1.4 * beban_mati
u2 = 1.2 * beban_mati + 1.6 * beban_hidup
u3 = 1.2 * beban_mati + 1.0 * beban_hidup + 1.6 * beban_angin
if u1 > u2 and u1 > u3:
    print("Kombinasi 1")
    print(f"{u1:.1f} kN")
elif u2 > u3:
    print("Kombinasi 2")
    print(f"{u2:.1f} kN")
else:
    print("Kombinasi 3")
    print(f"{u3:.1f} kN")
''',
   ("Kombinasi Beban yang Menentukan",
    "Sebuah kolom memikul beban mati D, beban hidup L, dan beban angin W. Perencana memeriksa tiga kombinasi beban terfaktor "
    "(disederhanakan untuk latihan):\n\n- Kombinasi 1: 1.4 D\n- Kombinasi 2: 1.2 D + 1.6 L\n- Kombinasi 3: 1.2 D + 1.0 L + 1.6 W\n\n"
    "Kombinasi yang menentukan adalah yang nilainya terbesar. Cetak nomornya dan nilainya dengan satu angka di belakang titik.\n\n"
    "Contoh: D = `100`, L = `50`, W = `20` menghasilkan\n\n```\nKombinasi 3\n202.0 kN\n```\n\n(nilainya 140.0, 200.0, dan 202.0).",
    "Tiga baris: D, L, W (kN, bilangan bulat).",
    "Baris pertama `Kombinasi K`. Baris kedua `U kN` dengan U satu angka di belakang titik.",
    "D 20 sampai 400; L 0 sampai 300; W 0 sampai 250. Nilai terbesar tidak pernah kembar."),
   ("The Governing Load Combination",
    "A column carries a dead load D, a live load L and a wind load W. The designer checks three factored load combinations (simplified "
    "for this exercise):\n\n- Combination 1: 1.4 D\n- Combination 2: 1.2 D + 1.6 L\n- Combination 3: 1.2 D + 1.0 L + 1.6 W\n\n"
    "The governing combination is the one with the largest value. Print its number and its value with one digit after the point.\n\n"
    "Example: D = `100`, L = `50`, W = `20` give\n\n```\nKombinasi 3\n202.0 kN\n```\n\n(the values are 140.0, 200.0 and 202.0).",
    "Three lines: D, L, W (kN, whole numbers).",
    "First line `Kombinasi K`. Second line `U kN` with U having one digit after the point (the word stays in Indonesian).",
    "D is 20 to 400; L is 0 to 300; W is 0 to 250. The largest value is never tied."),
   ("تركيبة الأحمال الحاكمة",
    "يحمل عمود حملًا ميتًا D وحملًا حيًّا L وحمل رياح W. يفحص المصمّم ثلاث تراكيب أحمال مصعَّدة (مبسّطة للتمرين):\n\n"
    "- التركيبة 1: ‎1.4 D\n- التركيبة 2: ‎1.2 D + 1.6 L\n- التركيبة 3: ‎1.2 D + 1.0 L + 1.6 W\n\n"
    "التركيبة الحاكمة هي ذات القيمة الكبرى. اطبع رقمها وقيمتها بمنزلة عشرية واحدة.\n\n"
    "مثال: D = `100` و L = `50` و W = `20` تعطي\n\n```\nKombinasi 3\n202.0 kN\n```\n\n(القيم 140.0 و200.0 و202.0).",
    "ثلاثة أسطر: D ثم L ثم W (كيلونيوتن، أعداد صحيحة).",
    "السطر الأول `Kombinasi K`. السطر الثاني `U kN` حيث U بمنزلة عشرية واحدة (تبقى الكلمة بالإندونيسية).",
    "D من 20 إلى 400؛ L من 0 إلى 300؛ W من 0 إلى 250. القيمة الكبرى لا تتكرر."))

# ================================= SULIT (4) =================================

py("gaya-dalam-balok", "sulit",
   AMAN + "while True:\n    L = r.randint(4, 20)\n    a1 = r.randint(1, L - 2)\n    a2 = r.randint(a1 + 1, L - 1)\n"
   "    p1 = r.randint(5, 120)\n    p2 = r.randint(5, 120)\n"
   "    if i % 4 == 0:\n        x = r.randint(1, a1 * 10)\n    elif i % 4 == 1:\n        x = r.randint(a1 * 10 + 1, a2 * 10)\n"
   "    elif i % 4 == 2:\n        x = r.randint(a2 * 10 + 1, L * 10 - 1)\n    else:\n        x = r.choice([a1, a2]) * 10\n"
   "    x = x / 10\n    ra = (p1 * (L - a1) + p2 * (L - a2)) / L\n"
   "    if x <= a1:\n        v = ra\n        m = ra * x\n    elif x <= a2:\n        v = ra - p1\n        m = ra * x - p1 * (x - a1)\n"
   "    else:\n        v = ra - p1 - p2\n        m = ra * x - p1 * (x - a1) - p2 * (x - a2)\n"
   "    if abs(v) > 0.006 and abs(m) > 0.006 and aman(v, 2) and aman(m, 2):\n        break\n"
   "print(L)\nprint(p1)\nprint(a1)\nprint(p2)\nprint(a2)\nprint(f'{x:.1f}')\n",
   r'''
bentang = int(input())
p1 = int(input())
a1 = int(input())
p2 = int(input())
a2 = int(input())
x = float(input())
reaksi_a = (p1 * (bentang - a1) + p2 * (bentang - a2)) / bentang
if x <= a1:
    lintang = reaksi_a
    momen = reaksi_a * x
elif x <= a2:
    lintang = reaksi_a - p1
    momen = reaksi_a * x - p1 * (x - a1)
else:
    lintang = reaksi_a - p1 - p2
    momen = reaksi_a * x - p1 * (x - a1) - p2 * (x - a2)
print(f"V = {lintang:.2f} kN")
print(f"M = {momen:.2f} kNm")
''',
   ("Gaya Lintang dan Momen di Satu Potongan",
    "Balok sederhana berbentang L (tumpuan A di kiri, B di kanan) memikul dua beban terpusat: P1 pada jarak a1 dari A dan P2 pada jarak "
    "a2 dari A, dengan a1 < a2. Reaksi tumpuan A adalah\n\nRA = (P1 × (L − a1) + P2 × (L − a2)) / L.\n\n"
    "Gaya lintang V dan momen lentur M pada potongan sejauh x dari A bergantung pada letak potongan itu:\n\n"
    "- x tidak lebih dari a1: V = RA, M = RA × x\n"
    "- x lebih dari a1 dan tidak lebih dari a2: V = RA − P1, M = RA × x − P1 × (x − a1)\n"
    "- x lebih dari a2: V = RA − P1 − P2, M = RA × x − P1 × (x − a1) − P2 × (x − a2)\n\n"
    "Tepat di bawah sebuah beban, pakai rumus potongan di sebelah kirinya (seperti aturan di atas). V boleh negatif.\n\n"
    "Contoh: L = `10`, P1 = `20`, a1 = `2`, P2 = `30`, a2 = `6`, x = `4.0` menghasilkan\n\n```\nV = 8.00 kN\nM = 72.00 kNm\n```\n\n"
    "karena RA = 28.",
    "Enam baris: L (m), P1 (kN), a1 (m), P2 (kN), a2 (m), semuanya bilangan bulat; lalu x (m, bilangan pecahan dengan satu angka di "
    "belakang titik).",
    "Baris pertama `V = N kN`, baris kedua `M = N kNm`, masing-masing dengan dua angka di belakang titik.",
    "L 4 sampai 20; 0 < a1 < a2 < L; beban 5 sampai 120; 0 < x < L. V dan M tidak pernah nol."),
   ("Shear Force and Bending Moment at a Section",
    "A simply supported beam of span L (support A on the left, B on the right) carries two point loads: P1 at a distance a1 from A and P2 "
    "at a distance a2 from A, with a1 < a2. The reaction at A is\n\nRA = (P1 × (L − a1) + P2 × (L − a2)) / L.\n\n"
    "The shear force V and the bending moment M at a section x from A depend on where the section lies:\n\n"
    "- x not greater than a1: V = RA, M = RA × x\n"
    "- x greater than a1 and not greater than a2: V = RA − P1, M = RA × x − P1 × (x − a1)\n"
    "- x greater than a2: V = RA − P1 − P2, M = RA × x − P1 × (x − a1) − P2 × (x − a2)\n\n"
    "Exactly under a load, use the formula of the section to its left (as the rules above say). V may be negative.\n\n"
    "Example: L = `10`, P1 = `20`, a1 = `2`, P2 = `30`, a2 = `6`, x = `4.0` give\n\n```\nV = 8.00 kN\nM = 72.00 kNm\n```\n\n"
    "because RA = 28.",
    "Six lines: L (m), P1 (kN), a1 (m), P2 (kN), a2 (m), all whole numbers; then x (m, a decimal number with one digit after the point).",
    "First line `V = N kN`, second line `M = N kNm`, each with two digits after the point.",
    "L is 4 to 20; 0 < a1 < a2 < L; loads are 5 to 120; 0 < x < L. V and M are never zero."),
   ("قوة القص وعزم الانحناء عند مقطع",
    "جائز بسيط الارتكاز مجازه L (المسند A يسارًا و B يمينًا) يحمل حملين مركّزين: P1 على بعد a1 من A و P2 على بعد a2 من A، مع a1 < a2. "
    "ردّ الفعل عند A هو\n\nRA = (P1 × (L − a1) + P2 × (L − a2)) / L.\n\n"
    "قوة القص V وعزم الانحناء M عند مقطع يبعد x عن A يتوقفان على موضع المقطع:\n\n"
    "- x لا يزيد على a1: ‏V = RA و M = RA × x\n"
    "- x أكبر من a1 ولا يزيد على a2: ‏V = RA − P1 و M = RA × x − P1 × (x − a1)\n"
    "- x أكبر من a2: ‏V = RA − P1 − P2 و M = RA × x − P1 × (x − a1) − P2 × (x − a2)\n\n"
    "تحت الحمل تمامًا استعمل صيغة المقطع الذي عن يساره (كما في القواعد أعلاه). قد تكون V سالبة.\n\n"
    "مثال: L = `10` و P1 = `20` و a1 = `2` و P2 = `30` و a2 = `6` و x = `4.0` تعطي\n\n```\nV = 8.00 kN\nM = 72.00 kNm\n```\n\nلأن RA = 28.",
    "ستة أسطر: L (م)، P1 (كيلونيوتن)، a1 (م)، P2 (كيلونيوتن)، a2 (م) وكلها أعداد صحيحة؛ ثم x (م، عدد عشري بمنزلة واحدة).",
    "السطر الأول `V = N kN` والسطر الثاني `M = N kNm`، كلٌّ بمنزلتين عشريتين.",
    "L من 4 إلى 20؛ ‎0 < a1 < a2 < L؛ الأحمال من 5 إلى 120؛ ‎0 < x < L. لا تساوي V ولا M الصفر."))

py("umur-benda-uji", "sulit",
   "hari = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31]\nawal = r.randint(1, 330)\n"
   "umur = [7, 14, 28, 6, 8, 1, 29][i] if i < 7 else r.randint(1, 35)\n\n\ndef tanggal(n):\n    b = 0\n"
   "    while n > hari[b]:\n        n -= hari[b]\n        b += 1\n    return n, b + 1\n\n\n"
   "for n in (awal, awal + umur):\n    t, b = tanggal(n)\n    print(t)\n    print(b)\n",
   r'''
tanggal_cor = int(input())
bulan_cor = int(input())
tanggal_uji = int(input())
bulan_uji = int(input())

hari_cor = tanggal_cor
if bulan_cor > 1:
    hari_cor = hari_cor + 31
if bulan_cor > 2:
    hari_cor = hari_cor + 28
if bulan_cor > 3:
    hari_cor = hari_cor + 31
if bulan_cor > 4:
    hari_cor = hari_cor + 30
if bulan_cor > 5:
    hari_cor = hari_cor + 31
if bulan_cor > 6:
    hari_cor = hari_cor + 30
if bulan_cor > 7:
    hari_cor = hari_cor + 31
if bulan_cor > 8:
    hari_cor = hari_cor + 31
if bulan_cor > 9:
    hari_cor = hari_cor + 30
if bulan_cor > 10:
    hari_cor = hari_cor + 31
if bulan_cor > 11:
    hari_cor = hari_cor + 30

hari_uji = tanggal_uji
if bulan_uji > 1:
    hari_uji = hari_uji + 31
if bulan_uji > 2:
    hari_uji = hari_uji + 28
if bulan_uji > 3:
    hari_uji = hari_uji + 31
if bulan_uji > 4:
    hari_uji = hari_uji + 30
if bulan_uji > 5:
    hari_uji = hari_uji + 31
if bulan_uji > 6:
    hari_uji = hari_uji + 30
if bulan_uji > 7:
    hari_uji = hari_uji + 31
if bulan_uji > 8:
    hari_uji = hari_uji + 31
if bulan_uji > 9:
    hari_uji = hari_uji + 30
if bulan_uji > 10:
    hari_uji = hari_uji + 31
if bulan_uji > 11:
    hari_uji = hari_uji + 30

umur = hari_uji - hari_cor
print(f"Umur: {umur} hari")
if umur == 7 or umur == 14 or umur == 28:
    print("UJI STANDAR")
elif umur < 7:
    print("TERLALU MUDA")
else:
    print("DI LUAR JADWAL")
''',
   ("Umur Benda Uji dari Dua Tanggal",
    "Benda uji beton dicor pada satu tanggal dan diuji pada tanggal lain di tahun yang sama. Tahun itu bukan tahun kabisat, jadi "
    "banyaknya hari tiap bulan berturut-turut 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31. Hitung umur benda uji dalam hari, yaitu "
    "selisih kedua tanggal, lalu nilai jadwalnya:\n\n"
    "- umur tepat 7, 14, atau 28 hari: `UJI STANDAR`\n- umur kurang dari 7 hari: `TERLALU MUDA`\n- selain itu: `DI LUAR JADWAL`\n\n"
    "Pertemuan ini belum memakai perulangan atau list; susun perhitungan nomor hari dalam setahun dengan percabangan.\n\n"
    "Contoh: dicor tanggal `25` bulan `2` dan diuji tanggal `25` bulan `3` menghasilkan\n\n```\nUmur: 28 hari\nUJI STANDAR\n```\n\n"
    "Dicor tanggal `30` bulan `4` dan diuji tanggal `3` bulan `5` menghasilkan `Umur: 3 hari` lalu `TERLALU MUDA`.",
    "Empat baris: tanggal cor, bulan cor, tanggal uji, bulan uji (bilangan bulat).",
    "Baris pertama `Umur: N hari`. Baris kedua `UJI STANDAR`, `TERLALU MUDA`, atau `DI LUAR JADWAL`.",
    "Kedua tanggal sah dan berada di tahun yang sama. Tanggal uji 1 sampai 35 hari setelah tanggal cor."),
   ("Specimen Age from Two Dates",
    "A concrete specimen is cast on one date and tested on another date of the same year. The year is not a leap year, so the months have "
    "31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31 days in turn. Compute the age of the specimen in days, that is the difference between "
    "the two dates, then judge the schedule:\n\n"
    "- an age of exactly 7, 14 or 28 days: `UJI STANDAR`\n- an age below 7 days: `TERLALU MUDA`\n- otherwise: `DI LUAR JADWAL`\n\n"
    "This meeting does not use loops or lists yet; build the day-of-year calculation with branching.\n\n"
    "Example: cast on day `25` of month `2` and tested on day `25` of month `3` gives\n\n```\nUmur: 28 hari\nUJI STANDAR\n```\n\n"
    "Cast on day `30` of month `4` and tested on day `3` of month `5` gives `Umur: 3 hari` then `TERLALU MUDA`.",
    "Four lines: the casting day, the casting month, the test day, the test month (whole numbers).",
    "First line `Umur: N hari`. Second line `UJI STANDAR`, `TERLALU MUDA`, or `DI LUAR JADWAL` (Indonesian words).",
    "Both dates are valid and in the same year. The test date is 1 to 35 days after the casting date."),
   ("عمر عيّنة الاختبار من تاريخين",
    "صُبّت عيّنة خرسانية في تاريخ واختُبرت في تاريخ آخر من السنة نفسها. السنة ليست كبيسة، فأيام الشهور على التوالي "
    "31، 28، 31، 30، 31، 30، 31، 31، 30، 31، 30، 31. احسب عمر العيّنة بالأيام، أي الفرق بين التاريخين، ثم قيِّم الموعد:\n\n"
    "- العمر 7 أو 14 أو 28 يومًا تمامًا: `UJI STANDAR`\n- العمر أقل من 7 أيام: `TERLALU MUDA`\n- غير ذلك: `DI LUAR JADWAL`\n\n"
    "لم تُدرَس الحلقات ولا القوائم بعد؛ ابنِ حساب رقم اليوم في السنة بالتفرّع.\n\n"
    "مثال: الصبّ في اليوم `25` من الشهر `2` والاختبار في اليوم `25` من الشهر `3` يعطيان\n\n```\nUmur: 28 hari\nUJI STANDAR\n```\n\n"
    "والصبّ في اليوم `30` من الشهر `4` والاختبار في اليوم `3` من الشهر `5` يعطيان `Umur: 3 hari` ثم `TERLALU MUDA`.",
    "أربعة أسطر: يوم الصبّ، شهر الصبّ، يوم الاختبار، شهر الاختبار (أعداد صحيحة).",
    "السطر الأول `Umur: N hari`. السطر الثاني `UJI STANDAR` أو `TERLALU MUDA` أو `DI LUAR JADWAL` (بالإندونيسية).",
    "التاريخان صحيحان وفي السنة نفسها. تاريخ الاختبار بعد تاريخ الصبّ بـ1 إلى 35 يومًا."))

py("faktor-reduksi", "sulit",
   AMAN + "while True:\n    b = r.choice([250, 300, 350, 400])\n    d = r.choice([390, 440, 490, 540, 590, 640])\n"
   "    fc = [25, 28, 35, 42, 56, 60][i % 6] if i < 6 else r.randint(20, 60)\n    fy = r.choice([280, 420])\n"
   "    if fc <= 28:\n        b1 = 0.85\n    elif fc >= 56:\n        b1 = 0.65\n    else:\n        b1 = 0.85 - 0.05 * (fc - 28) / 7\n"
   "    k = [r.uniform(0.08, 0.36), r.uniform(0.39, 0.58), r.uniform(0.62, 0.80)][i % 3]\n"
   "    luas = int(k * d * b1 * 0.85 * fc * b / fy)\n    a = luas * fy / (0.85 * fc * b)\n    c = a / b1\n"
   "    et = 0.003 * (d - c) / c\n    if et >= 0.005:\n        phi = 0.9\n    elif et <= 0.002:\n        phi = 0.65\n"
   "    else:\n        phi = 0.65 + (et - 0.002) * 250 / 3\n    mn = phi * luas * fy * (d - a / 2) / 1000000\n"
   "    if abs(et - 0.005) > 0.00002 and abs(et - 0.002) > 0.00002 and aman(a, 2) and aman(et, 5) and aman(phi, 3) and aman(mn, 2):\n"
   "        break\nprint(b)\nprint(d)\nprint(luas)\nprint(fc)\nprint(fy)\n",
   r'''
lebar = int(input())
tinggi_efektif = int(input())
luas_tulangan = int(input())
fc = int(input())
fy = int(input())

a = luas_tulangan * fy / (0.85 * fc * lebar)
if fc <= 28:
    beta_1 = 0.85
elif fc >= 56:
    beta_1 = 0.65
else:
    beta_1 = 0.85 - 0.05 * (fc - 28) / 7
c = a / beta_1
regangan = 0.003 * (tinggi_efektif - c) / c

if regangan >= 0.005:
    kelas = "TERKENDALI TARIK"
    phi = 0.9
elif regangan <= 0.002:
    kelas = "TERKENDALI TEKAN"
    phi = 0.65
else:
    kelas = "TRANSISI"
    phi = 0.65 + (regangan - 0.002) * 250 / 3

momen_nominal = luas_tulangan * fy * (tinggi_efektif - a / 2) / 1000000
print(f"a = {a:.2f} mm")
print(f"et = {regangan:.5f}")
print(kelas)
print(f"phi = {phi:.3f}")
print(f"phiMn = {phi * momen_nominal:.2f} kNm")
''',
   ("Faktor Reduksi dan Kuat Lentur Balok",
    "Kuat lentur rencana balok beton bertulangan tunggal dihitung bertahap (rumus dan batas di bawah ini parameter latihan, mengikuti "
    "cara yang umum diajarkan). Datanya: lebar b (mm), tinggi efektif d (mm), luas tulangan As (mm²), mutu beton fc (MPa), mutu baja "
    "fy (MPa).\n\n"
    "1. Tinggi blok tekan: a = As × fy / (0.85 × fc × b).\n"
    "2. Faktor β1: 0.85 bila fc tidak lebih dari 28; 0.65 bila fc 56 atau lebih; selain itu 0.85 − 0.05 × (fc − 28) / 7.\n"
    "3. Letak garis netral: c = a / β1.\n"
    "4. Regangan baja tarik: εt = 0.003 × (d − c) / c.\n"
    "5. Kelas penampang dan faktor reduksi φ:\n"
    "   - εt 0.005 atau lebih: `TERKENDALI TARIK`, φ = 0.9\n"
    "   - εt 0.002 atau kurang: `TERKENDALI TEKAN`, φ = 0.65\n"
    "   - di antaranya: `TRANSISI`, φ = 0.65 + (εt − 0.002) × 250 / 3\n"
    "6. Momen nominal: Mn = As × fy × (d − a / 2), dalam N·mm; bagi 1000000 untuk mendapat kN·m. Kuat rencana = φ × Mn.\n\n"
    "Contoh: b = `300`, d = `500`, As = `1500`, fc = `30`, fy = `420` menghasilkan\n\n"
    "```\na = 82.35 mm\net = 0.01222\nTERKENDALI TARIK\nphi = 0.900\nphiMn = 260.15 kNm\n```",
    "Lima baris: b, d, As, fc, fy. Semuanya bilangan bulat.",
    "Lima baris: `a = A mm` (dua desimal), `et = E` (lima desimal), kelas penampang, `phi = F` (tiga desimal), `phiMn = M kNm` "
    "(dua desimal).",
    "b 250 sampai 400; d 390 sampai 640; fc 20 sampai 60; fy 280 atau 420. εt selalu positif dan tidak pernah tepat 0.002 atau 0.005."),
   ("Strength Reduction Factor and Flexural Strength of a Beam",
    "The design flexural strength of a singly reinforced concrete beam is computed in steps (the formulas and limits below are exercise "
    "parameters, following the commonly taught method). The data: width b (mm), effective depth d (mm), steel area As (mm²), concrete "
    "strength fc (MPa), steel yield stress fy (MPa).\n\n"
    "1. Depth of the compression block: a = As × fy / (0.85 × fc × b).\n"
    "2. Factor β1: 0.85 if fc is not above 28; 0.65 if fc is 56 or more; otherwise 0.85 − 0.05 × (fc − 28) / 7.\n"
    "3. Neutral axis depth: c = a / β1.\n"
    "4. Tension steel strain: εt = 0.003 × (d − c) / c.\n"
    "5. Section class and reduction factor φ:\n"
    "   - εt 0.005 or more: `TERKENDALI TARIK` (tension-controlled), φ = 0.9\n"
    "   - εt 0.002 or less: `TERKENDALI TEKAN` (compression-controlled), φ = 0.65\n"
    "   - in between: `TRANSISI`, φ = 0.65 + (εt − 0.002) × 250 / 3\n"
    "6. Nominal moment: Mn = As × fy × (d − a / 2), in N·mm; divide by 1000000 to get kN·m. Design strength = φ × Mn.\n\n"
    "Example: b = `300`, d = `500`, As = `1500`, fc = `30`, fy = `420` give\n\n"
    "```\na = 82.35 mm\net = 0.01222\nTERKENDALI TARIK\nphi = 0.900\nphiMn = 260.15 kNm\n```",
    "Five lines: b, d, As, fc, fy. All are whole numbers.",
    "Five lines: `a = A mm` (two decimals), `et = E` (five decimals), the section class, `phi = F` (three decimals), `phiMn = M kNm` "
    "(two decimals).",
    "b is 250 to 400; d is 390 to 640; fc is 20 to 60; fy is 280 or 420. εt is always positive and never exactly 0.002 or 0.005."),
   ("معامل التخفيض ومقاومة الانحناء لجائز",
    "تُحسب مقاومة الانحناء التصميمية لجائز خرساني مفرد التسليح على مراحل (الصيغ والحدود أدناه قيم للتمرين وفق الطريقة الشائعة في "
    "التدريس). البيانات: العرض b (مم)، العمق الفعّال d (مم)، مساحة التسليح As (مم²)، مقاومة الخرسانة fc (MPa)، إجهاد خضوع الحديد "
    "fy (MPa).\n\n"
    "1. عمق بلوك الضغط: a = As × fy / (0.85 × fc × b).\n"
    "2. المعامل β1: ‏0.85 إذا لم تزد fc على 28؛ ‏0.65 إذا كانت fc تساوي 56 أو أكثر؛ وإلا ‎0.85 − 0.05 × (fc − 28) / 7.\n"
    "3. عمق المحور المحايد: c = a / β1.\n"
    "4. انفعال حديد الشدّ: εt = 0.003 × (d − c) / c.\n"
    "5. صنف المقطع ومعامل التخفيض φ:\n"
    "   - εt يساوي 0.005 أو أكثر: `TERKENDALI TARIK` (محكوم بالشدّ)، φ = 0.9\n"
    "   - εt يساوي 0.002 أو أقل: `TERKENDALI TEKAN` (محكوم بالضغط)، φ = 0.65\n"
    "   - بينهما: `TRANSISI`، ‏φ = 0.65 + (εt − 0.002) × 250 / 3\n"
    "6. العزم الاسمي: Mn = As × fy × (d − a / 2) بوحدة نيوتن·مم؛ اقسم على 1000000 للحصول على كيلونيوتن·م. "
    "المقاومة التصميمية = φ × Mn.\n\n"
    "مثال: b = `300` و d = `500` و As = `1500` و fc = `30` و fy = `420` تعطي\n\n"
    "```\na = 82.35 mm\net = 0.01222\nTERKENDALI TARIK\nphi = 0.900\nphiMn = 260.15 kNm\n```",
    "خمسة أسطر: b ثم d ثم As ثم fc ثم fy. كلها أعداد صحيحة.",
    "خمسة أسطر: `a = A mm` (منزلتان)، `et = E` (خمس منازل)، صنف المقطع، `phi = F` (ثلاث منازل)، `phiMn = M kNm` (منزلتان).",
    "b من 250 إلى 400؛ d من 390 إلى 640؛ fc من 20 إلى 60؛ fy يساوي 280 أو 420. εt موجب دائمًا ولا يساوي 0.002 أو 0.005 تمامًا."))

py("pemenang-tender", "sulit",
   "t = r.randint(60, 240)\nh = [r.randint(400, 2000) for _ in range(3)]\n"
   "w = [t + r.randint(-40, 25) for _ in range(3)]\nif i == 1:\n    w = [t + r.randint(1, 30) for _ in range(3)]\n"
   "if i == 2:\n    h[1] = h[0]\n    h[2] = h[0]\n    w = [t - 5, t - 9, t - 9]\nif i == 3:\n    h[2] = h[0]\n    w[0] = t\n    w[2] = t - 1\n"
   "if i == 4:\n    h[1] = h[2] = min(h)\n    w[1] = w[2] = t - 3\nif i == 5:\n    k = h.index(min(h))\n    w[k] = t + 1\n"
   "print(t)\nfor k in range(3):\n    print(h[k])\n    print(w[k])\n",
   r'''
batas_waktu = int(input())
harga_1 = int(input())
waktu_1 = int(input())
harga_2 = int(input())
waktu_2 = int(input())
harga_3 = int(input())
waktu_3 = int(input())

pemenang = 0
harga_menang = 0
waktu_menang = 0
if waktu_1 <= batas_waktu:
    pemenang = 1
    harga_menang = harga_1
    waktu_menang = waktu_1
if waktu_2 <= batas_waktu:
    if pemenang == 0 or harga_2 < harga_menang or (harga_2 == harga_menang and waktu_2 < waktu_menang):
        pemenang = 2
        harga_menang = harga_2
        waktu_menang = waktu_2
if waktu_3 <= batas_waktu:
    if pemenang == 0 or harga_3 < harga_menang or (harga_3 == harga_menang and waktu_3 < waktu_menang):
        pemenang = 3
        harga_menang = harga_3
        waktu_menang = waktu_3

if pemenang == 0:
    print("TENDER GAGAL")
else:
    print(f"Pemenang: Penawar {pemenang}")
    print(f"Harga: {harga_menang} juta")
''',
   ("Pemenang Tender",
    "Tiga kontraktor memasukkan penawaran untuk sebuah pekerjaan. Setiap penawaran berisi harga (juta rupiah) dan waktu pelaksanaan "
    "(hari). Panitia memakai aturan berikut:\n\n"
    "1. Penawaran yang waktunya melebihi batas waktu gugur. Waktu yang tepat sama dengan batas masih sah.\n"
    "2. Di antara penawaran yang sah, pemenangnya yang harganya paling rendah.\n"
    "3. Bila harga terendah kembar, menang yang waktunya lebih singkat.\n"
    "4. Bila masih kembar, menang penawar dengan nomor lebih kecil.\n"
    "5. Bila tidak ada penawaran yang sah, tender gagal.\n\n"
    "Pertemuan ini belum memakai perulangan atau list.\n\n"
    "Contoh: batas waktu `120`; penawar 1 harga `950` waktu `110`; penawar 2 harga `900` waktu `130`; penawar 3 harga `950` waktu "
    "`100` menghasilkan\n\n```\nPemenang: Penawar 3\nHarga: 950 juta\n```\n\n"
    "Penawar 2 gugur karena waktunya, lalu penawar 3 menang atas penawar 1 karena lebih singkat. Bila ketiga waktu melebihi batas, "
    "keluarannya satu baris `TENDER GAGAL`.",
    "Tujuh baris: batas waktu (hari); lalu harga dan waktu penawar 1, penawar 2, penawar 3, masing-masing di barisnya sendiri. "
    "Semuanya bilangan bulat.",
    "Dua baris `Pemenang: Penawar K` dan `Harga: H juta`, atau satu baris `TENDER GAGAL`.",
    "Batas waktu 60 sampai 240; harga 400 sampai 2000; waktu 20 sampai 270."),
   ("The Winning Bid",
    "Three contractors submit bids for a job. Each bid has a price (million rupiah) and a completion time (days). The committee applies "
    "these rules:\n\n"
    "1. A bid whose time exceeds the time limit is disqualified. A time exactly equal to the limit is still valid.\n"
    "2. Among the valid bids, the lowest price wins.\n"
    "3. If the lowest price is tied, the shorter time wins.\n"
    "4. If still tied, the bidder with the smaller number wins.\n"
    "5. If no bid is valid, the tender fails.\n\n"
    "This meeting does not use loops or lists yet.\n\n"
    "Example: time limit `120`; bidder 1 price `950` time `110`; bidder 2 price `900` time `130`; bidder 3 price `950` time `100` give\n\n"
    "```\nPemenang: Penawar 3\nHarga: 950 juta\n```\n\n"
    "Bidder 2 is disqualified by its time, then bidder 3 beats bidder 1 by being shorter. If all three times exceed the limit, the "
    "output is the single line `TENDER GAGAL`.",
    "Seven lines: the time limit (days); then the price and the time of bidder 1, bidder 2, bidder 3, each on its own line. All are "
    "whole numbers.",
    "Two lines `Pemenang: Penawar K` and `Harga: H juta`, or the single line `TENDER GAGAL` (Indonesian words).",
    "Time limit 60 to 240; prices 400 to 2000; times 20 to 270."),
   ("الفائز بالمناقصة",
    "قدّم ثلاثة مقاولين عروضًا لتنفيذ عمل. يتضمن كل عرض سعرًا (مليون روبية) ومدة تنفيذ (أيام). تطبّق اللجنة القواعد الآتية:\n\n"
    "1. يُستبعد العرض الذي تتجاوز مدته المهلة المحددة. المدة المساوية للمهلة تمامًا مقبولة.\n"
    "2. من بين العروض المقبولة يفوز الأقل سعرًا.\n"
    "3. إذا تساوى أقل سعر فاز العرض الأقصر مدة.\n"
    "4. إذا استمر التساوي فاز صاحب الرقم الأصغر.\n"
    "5. إذا لم يوجد عرض مقبول فشلت المناقصة.\n\n"
    "لم تُدرَس الحلقات ولا القوائم بعد.\n\n"
    "مثال: المهلة `120`؛ المقاول 1 سعره `950` ومدته `110`؛ المقاول 2 سعره `900` ومدته `130`؛ المقاول 3 سعره `950` ومدته `100` تعطي\n\n"
    "```\nPemenang: Penawar 3\nHarga: 950 juta\n```\n\n"
    "استُبعد المقاول 2 بسبب مدته، ثم فاز المقاول 3 على المقاول 1 لأنه أقصر مدة. إذا تجاوزت المدد الثلاث المهلة فالمخرج سطر واحد "
    "`TENDER GAGAL`.",
    "سبعة أسطر: المهلة (أيام)؛ ثم سعر ومدة المقاول 1 ثم 2 ثم 3، كلٌّ في سطره. كلها أعداد صحيحة.",
    "سطران `Pemenang: Penawar K` و `Harga: H juta`، أو سطر واحد `TENDER GAGAL` (بالإندونيسية).",
    "المهلة من 60 إلى 240؛ الأسعار من 400 إلى 2000؛ المدد من 20 إلى 270."))

tulis(__file__, "TS-PY", 3, "python", {"mudah_sekali": 8, "mudah": 8, "mudah_sedang": 6, "sedang": 6, "sulit": 4})
