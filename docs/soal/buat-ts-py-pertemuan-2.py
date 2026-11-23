#!/usr/bin/env python3
"""Menyusun docs/soal/ts-py-pertemuan-2.json.

TS-PY pertemuan 2 — Variabel, Tipe Data, Operasi Matematika, dan Satuan (RPS
Rev2 10.2): int/float/str, type casting, prioritas operator, modul math,
pembulatan dan floating-point, f-string, konversi satuan dan konsistensi
dimensi. Praktikum RPS: kalkulator luas/volume, berat sendiri, tekanan
hidrostatik.

Konsep yang diizinkan di produksi: IO_DASAR, ARITMETIKA, VARIABEL_TIPE,
KONVERSI_TIPE, FORMAT_OUTPUT. `import math` tidak dihitung konsep apa pun.
Belum ada if, perulangan, fungsi, list, atau operasi string (penyambungan,
metode string): keluaran dibentuk dengan f-string yang langsung dicetak.

Pilihan teknis (DECISIONS.md, 2026-11-23):
- Mulai pertemuan ini hasil pecahan dicetak dengan jumlah desimal yang
  ditentukan soal (f-string berformat) dan dibandingkan apa adanya (`trimmed`).
- Supaya jawaban tidak bergantung pada urutan operasi pecahan biner, generator
  tidak pernah menghasilkan masukan yang hasilnya berada dalam 0,002 satuan
  desimal terakhir dari batas pembulatan (fungsi aman()), atau dalam 0,001 dari
  bilangan bulat bila hasilnya dibulatkan ke atas (fungsi jauh()).
- Satu nilai per baris masukan, seperti pertemuan 1.
- Faktor dan rumus praktis (g = 9,81; 1 MPa = 10,197 kg/cm²; berat jenis beton
  24 kN/m³; Mononobe; Manning; jarak henti) disebut parameter latihan.

    python3 docs/soal/buat-ts-py-pertemuan-2.py
"""
from bangun import soal, tulis

CATAT = {
    "id": " Panggil `input()` tanpa teks di dalam kurungnya.",
    "en": " Call `input()` with nothing inside the brackets.",
    "ar": " استدعِ `input()` دون أي نص بين القوسين.",
}
AMAN = ("def aman(x, d):\n    y = abs(x) * 10 ** d\n    return abs(y - int(y) - 0.5) > 0.002\n\n\n"
        "def jauh(x):\n    return abs(x - round(x)) > 0.001\n\n\n")


def t(bahasa, judul, isi, masuk, keluar, batas):
    return (judul, isi, masuk + CATAT[bahasa], keluar, batas)


def py(slug, tingkat, gen, ref, id, en, ar):
    soal(slug, tingkat, "trimmed", gen, ref.lstrip("\n"), t("id", *id), t("en", *en), t("ar", *ar), waktu_ms=5000)


# ============================== MUDAH SEKALI (8) ==============================

py("format-panjang", "mudah_sekali",
   "k = r.randint(100, 99999)\nwhile k % 10 == 5:\n    k = r.randint(100, 99999)\nprint(f'{k / 1000:.3f}')\n",
   r'''
panjang_m = float(input())
print(f"Panjang: {panjang_m:.2f} m")
''',
   ("Panjang dengan Dua Desimal",
    "Alat ukur jarak mencatat panjang sampai tiga angka di belakang koma, tetapi laporan hanya memuat dua. Baca panjang itu sebagai "
    "`float`, lalu cetak dengan f-string berformat `:.2f`.\n\nContoh: masukan `12.348` menghasilkan `Panjang: 12.35 m`.",
    "Satu baris: panjang dalam meter (bilangan pecahan).",
    "Satu baris berbentuk `Panjang: P m`, dengan P tepat dua angka di belakang titik.",
    "Panjang 0.100 sampai 99.999."),
   ("Length with Two Decimals",
    "A distance meter records a length to three decimal places, but the report shows only two. Read the length as a `float`, then print it "
    "with an f-string and the format `:.2f`.\n\nExample: the input `12.348` gives `Panjang: 12.35 m`.",
    "One line: the length in metres (a decimal number).",
    "One line of the form `Panjang: P m`, with P having exactly two digits after the point (the word stays in Indonesian).",
    "The length is 0.100 to 99.999."),
   ("الطول بمنزلتين عشريتين",
    "يسجّل جهاز قياس المسافة الطول بثلاث منازل عشرية، لكن التقرير يعرض منزلتين فقط. اقرأ الطول كعدد `float` ثم اطبعه بسلسلة f "
    "وبالتنسيق `:.2f`.\n\nمثال: المدخل `12.348` يعطي `Panjang: 12.35 m`.",
    "سطر واحد: الطول بالمتر (عدد عشري).",
    "سطر واحد بالشكل `Panjang: P m` حيث P بمنزلتين عشريتين تمامًا (تبقى الكلمة بالإندونيسية).",
    "الطول من 0.100 إلى 99.999."))

py("mm-ke-m", "mudah_sekali",
   "print(r.randint(1, 99999))\n",
   r'''
panjang_mm = int(input())
panjang_m = panjang_mm / 1000
print(f"{panjang_m:.3f} m")
''',
   ("Milimeter ke Meter",
    "Gambar kerja menuliskan ukuran dalam milimeter, sedangkan volume pekerjaan dihitung dalam meter. Ubah sebuah ukuran dari mm ke m "
    "dan cetak dengan tiga angka di belakang titik.\n\nContoh: `2500` menghasilkan `2.500 m`; `75` menghasilkan `0.075 m`.",
    "Satu baris: ukuran dalam mm (bilangan bulat).",
    "Satu baris berbentuk `X m`, dengan X tepat tiga angka di belakang titik.",
    "Ukuran 1 sampai 99999."),
   ("Millimetres to Metres",
    "Working drawings give sizes in millimetres, while quantities are computed in metres. Convert a size from mm to m and print it with "
    "three digits after the point.\n\nExample: `2500` gives `2.500 m`; `75` gives `0.075 m`.",
    "One line: the size in mm (a whole number).",
    "One line of the form `X m`, with X having exactly three digits after the point.",
    "The size is 1 to 99999."),
   ("من المليمتر إلى المتر",
    "تُكتب المقاسات في المخططات التنفيذية بالمليمتر، أما الكميات فتُحسب بالمتر. حوِّل مقاسًا من المليمتر إلى المتر واطبعه بثلاث منازل عشرية.\n\n"
    "مثال: `2500` يعطي `2.500 m`؛ و`75` يعطي `0.075 m`.",
    "سطر واحد: المقاس بالمليمتر (عدد صحيح).",
    "سطر واحد بالشكل `X m` حيث X بثلاث منازل عشرية تمامًا.",
    "المقاس من 1 إلى 99999."))

py("meter-utuh", "mudah_sekali",
   "print(f'{r.randint(100, 12000) / 100:.2f}')\n",
   r'''
panjang_m = float(input())
meter_utuh = int(panjang_m)
print(meter_utuh)
''',
   ("Meter Utuh dari Sisa Besi",
    "Sisa batang besi diukur dalam meter dengan dua desimal. Gudang hanya mencatat meter utuhnya; pecahannya dibuang, bukan dibulatkan. "
    "Fungsi `int()` membuang bagian pecahan sebuah `float`.\n\nContoh: `11.75` menghasilkan `11`; `3.99` menghasilkan `3`.",
    "Satu baris: panjang sisa besi dalam meter (bilangan pecahan).",
    "Satu baris: banyaknya meter utuh (bilangan bulat).",
    "Panjang 1.00 sampai 120.00."),
   ("Whole Metres of Leftover Bar",
    "A leftover bar is measured in metres with two decimals. The store records only whole metres; the fraction is dropped, not rounded. "
    "The function `int()` drops the fractional part of a `float`.\n\nExample: `11.75` gives `11`; `3.99` gives `3`.",
    "One line: the length of the leftover bar in metres (a decimal number).",
    "One line: the number of whole metres (a whole number).",
    "The length is 1.00 to 120.00."),
   ("الأمتار الكاملة من بقية القضيب",
    "تُقاس بقية قضيب الحديد بالمتر بمنزلتين عشريتين. يسجّل المخزن الأمتار الكاملة فقط؛ يُحذف الكسر ولا يُقرَّب. "
    "الدالة `int()` تحذف الجزء الكسري من عدد `float`.\n\nمثال: `11.75` يعطي `11`؛ و`3.99` يعطي `3`.",
    "سطر واحد: طول بقية القضيب بالمتر (عدد عشري).",
    "سطر واحد: عدد الأمتار الكاملة (عدد صحيح).",
    "الطول من 1.00 إلى 120.00."))

py("km-jam-ke-m-detik", "mudah_sekali",
   "print(r.randint(10, 120))\n",
   r'''
kecepatan_km_jam = int(input())
kecepatan_m_detik = kecepatan_km_jam * 1000 / 3600
print(f"{kecepatan_m_detik:.2f} m/detik")
''',
   ("Kecepatan Rencana dalam m/detik",
    "Kecepatan rencana jalan ditulis dalam km/jam, tetapi rumus jarak pandang memakai m/detik. Ubah satuannya: 1 km = 1000 m dan "
    "1 jam = 3600 detik.\n\nContoh: `72` km/jam menghasilkan `20.00 m/detik`.",
    "Satu baris: kecepatan dalam km/jam (bilangan bulat).",
    "Satu baris berbentuk `V m/detik`, dengan V dua angka di belakang titik.",
    "Kecepatan 10 sampai 120."),
   ("Design Speed in m/s",
    "The design speed of a road is given in km/h, but sight-distance formulas use m/s. Convert the unit: 1 km = 1000 m and "
    "1 hour = 3600 s.\n\nExample: `72` km/h gives `20.00 m/detik`.",
    "One line: the speed in km/h (a whole number).",
    "One line of the form `V m/detik`, with V having two digits after the point.",
    "The speed is 10 to 120."),
   ("السرعة التصميمية بالمتر في الثانية",
    "تُكتب السرعة التصميمية للطريق بالكيلومتر في الساعة، لكن معادلات مسافة الرؤية تستعمل المتر في الثانية. حوِّل الوحدة: "
    "1 كم = 1000 م و1 ساعة = 3600 ثانية.\n\nمثال: `72` كم/س تعطي `20.00 m/detik`.",
    "سطر واحد: السرعة بالكيلومتر في الساعة (عدد صحيح).",
    "سطر واحد بالشكل `V m/detik` حيث V بمنزلتين عشريتين.",
    "السرعة من 10 إلى 120."))

py("keliling-tiang", "mudah_sekali",
   "import math\n" + AMAN + "d = r.randint(20, 120)\nwhile not aman(math.pi * d, 2):\n    d = r.randint(20, 120)\nprint(d)\n",
   r'''
import math

diameter_cm = int(input())
keliling_cm = math.pi * diameter_cm
print(f"{keliling_cm:.2f}")
''',
   ("Keliling Tiang Pancang Bulat",
    "Bekisting tiang pancang bulat dibuat dari pelat yang panjangnya sama dengan keliling tiang. Hitung keliling lingkaran, π × d, "
    "dengan nilai π dari modul `math` (`math.pi`).\n\nContoh: diameter `40` cm menghasilkan `125.66`.",
    "Satu baris: diameter tiang dalam cm (bilangan bulat).",
    "Satu baris: keliling dalam cm, dua angka di belakang titik.",
    "Diameter 20 sampai 120."),
   ("Circumference of a Round Pile",
    "The formwork of a round pile is made from a sheet as long as the circumference of the pile. Compute the circumference, π × d, "
    "with π taken from the `math` module (`math.pi`).\n\nExample: a diameter of `40` cm gives `125.66`.",
    "One line: the pile diameter in cm (a whole number).",
    "One line: the circumference in cm, two digits after the point.",
    "The diameter is 20 to 120."),
   ("محيط خازوق دائري",
    "تُصنع قوالب الخازوق الدائري من صفيحة طولها يساوي محيط الخازوق. احسب المحيط π × d، مع أخذ π من الوحدة `math` (`math.pi`).\n\n"
    "مثال: القطر `40` سم يعطي `125.66`.",
    "سطر واحد: قطر الخازوق بالسنتيمتر (عدد صحيح).",
    "سطر واحد: المحيط بالسنتيمتر بمنزلتين عشريتين.",
    "القطر من 20 إلى 120."))

py("mpa-ke-kgcm2", "mudah_sekali",
   AMAN + "k = r.randint(100, 600)\nwhile not aman(k / 10 * 10.197, 1):\n    k = r.randint(100, 600)\nprint(f'{k / 10:.1f}')\n",
   r'''
kuat_tekan_MPa = float(input())
kuat_tekan_kg_cm2 = kuat_tekan_MPa * 10.197
print(f"{kuat_tekan_kg_cm2:.1f} kg/cm2")
''',
   ("Mutu Beton: MPa ke kg/cm²",
    "Mutu beton sekarang dinyatakan dalam MPa (fc'), tetapi banyak dokumen lama memakai kg/cm². Ubah kuat tekan dari MPa ke kg/cm² "
    "dengan 1 MPa = 10,197 kg/cm² (parameter latihan).\n\nContoh: `25.0` MPa menghasilkan `254.9 kg/cm2`.",
    "Satu baris: kuat tekan dalam MPa (bilangan pecahan).",
    "Satu baris berbentuk `K kg/cm2`, dengan K satu angka di belakang titik.",
    "Kuat tekan 10.0 sampai 60.0."),
   ("Concrete Grade: MPa to kg/cm²",
    "Concrete grade is now stated in MPa (fc'), but many older documents use kg/cm². Convert a compressive strength from MPa to kg/cm² "
    "with 1 MPa = 10.197 kg/cm² (a practice parameter).\n\nExample: `25.0` MPa gives `254.9 kg/cm2`.",
    "One line: the compressive strength in MPa (a decimal number).",
    "One line of the form `K kg/cm2`, with K having one digit after the point.",
    "The strength is 10.0 to 60.0."),
   ("رتبة الخرسانة: من MPa إلى kg/cm²",
    "تُذكر رتبة الخرسانة اليوم بالميغاباسكال (fc')، لكن كثيرًا من الوثائق القديمة تستعمل kg/cm². حوِّل مقاومة الضغط من MPa إلى kg/cm² "
    "باعتبار 1 MPa = 10.197 kg/cm² (معامل تدريبي).\n\nمثال: `25.0` MPa تعطي `254.9 kg/cm2`.",
    "سطر واحد: مقاومة الضغط بالميغاباسكال (عدد عشري).",
    "سطر واحد بالشكل `K kg/cm2` حيث K بمنزلة عشرية واحدة.",
    "المقاومة من 10.0 إلى 60.0."))

py("luas-ke-hektar", "mudah_sekali",
   "print(r.randint(100, 999999))\n",
   r'''
luas_m2 = int(input())
luas_ha = luas_m2 / 10000
print(f"{luas_ha:.4f} ha")
''',
   ("Luas Lahan dalam Hektar",
    "Luas lahan hasil pengukuran dalam meter persegi harus dilaporkan dalam hektar. Satu hektar = 10 000 m².\n\n"
    "Contoh: `12500` m² menghasilkan `1.2500 ha`.",
    "Satu baris: luas dalam m² (bilangan bulat).",
    "Satu baris berbentuk `L ha`, dengan L empat angka di belakang titik.",
    "Luas 100 sampai 999999."),
   ("Site Area in Hectares",
    "A surveyed area in square metres has to be reported in hectares. One hectare = 10 000 m².\n\nExample: `12500` m² gives `1.2500 ha`.",
    "One line: the area in m² (a whole number).",
    "One line of the form `L ha`, with L having four digits after the point.",
    "The area is 100 to 999999."),
   ("مساحة الأرض بالهكتار",
    "يجب أن تُذكر مساحة الأرض المقيسة بالمتر المربع بالهكتار. الهكتار الواحد = 10 000 م².\n\nمثال: `12500` م² تعطي `1.2500 ha`.",
    "سطر واحد: المساحة بالمتر المربع (عدد صحيح).",
    "سطر واحد بالشكل `L ha` حيث L بأربع منازل عشرية.",
    "المساحة من 100 إلى 999999."))

py("jam-desimal", "mudah_sekali",
   "print(r.randint(1, 2000))\n",
   r'''
durasi_menit = int(input())
durasi_jam = durasi_menit / 60
print(f"{durasi_jam:.2f} jam")
''',
   ("Jam Kerja Alat dalam Desimal",
    "Sewa alat berat dihitung per jam, dengan jam ditulis sebagai bilangan desimal. Operator mencatat lama kerja dalam menit. "
    "Ubah menjadi jam desimal.\n\nContoh: `135` menit menghasilkan `2.25 jam`.",
    "Satu baris: lama kerja dalam menit (bilangan bulat).",
    "Satu baris berbentuk `J jam`, dengan J dua angka di belakang titik.",
    "Lama kerja 1 sampai 2000."),
   ("Machine Hours as a Decimal",
    "Heavy equipment is hired by the hour, with hours written as a decimal number. The operator records the working time in minutes. "
    "Convert it to decimal hours.\n\nExample: `135` minutes give `2.25 jam`.",
    "One line: the working time in minutes (a whole number).",
    "One line of the form `J jam`, with J having two digits after the point.",
    "The working time is 1 to 2000."),
   ("ساعات عمل الآلة كعدد عشري",
    "تُؤجَّر المعدات الثقيلة بالساعة، وتُكتب الساعات عددًا عشريًّا. يسجّل المشغّل مدة العمل بالدقائق. حوِّلها إلى ساعات عشرية.\n\n"
    "مثال: `135` دقيقة تعطي `2.25 jam`.",
    "سطر واحد: مدة العمل بالدقائق (عدد صحيح).",
    "سطر واحد بالشكل `J jam` حيث J بمنزلتين عشريتين.",
    "مدة العمل من 1 إلى 2000."))

# ================================== MUDAH (8) ==================================

py("berat-sendiri-balok", "mudah",
   AMAN + "while True:\n    b = r.randint(4, 12) * 5\n    h = r.randint(6, 18) * 5\n    k = r.randint(20, 120)\n"
   "    if aman(b * h / 10000 * (k / 10) * 24, 2):\n        break\nprint(b)\nprint(h)\nprint(f'{k / 10:.1f}')\n",
   r'''
lebar_cm = int(input())
tinggi_cm = int(input())
panjang_m = float(input())
luas_m2 = (lebar_cm / 100) * (tinggi_cm / 100)
berat_kN = luas_m2 * panjang_m * 24
print(f"{berat_kN:.2f} kN")
''',
   ("Berat Sendiri Balok Beton",
    "Berat sendiri balok = volume × berat jenis. Untuk beton bertulang dipakai 24 kN/m³ (parameter latihan). Penampang balok diberikan "
    "dalam cm dan panjangnya dalam m: ubah dulu penampang ke meter supaya satuannya konsisten.\n\n"
    "Contoh: balok 30 cm × 50 cm sepanjang 6.5 m: 0,3 × 0,5 × 6,5 × 24 = `23.40 kN`.",
    "Tiga baris: lebar (cm, bilangan bulat), tinggi (cm, bilangan bulat), panjang (m, bilangan pecahan).",
    "Satu baris berbentuk `W kN`, dengan W dua angka di belakang titik.",
    "Lebar 20–60, tinggi 30–90, panjang 2.0–12.0."),
   ("Self-Weight of a Concrete Beam",
    "Self-weight of a beam = volume × unit weight. For reinforced concrete use 24 kN/m³ (a practice parameter). The section is given "
    "in cm and the length in m: convert the section to metres first so that the units agree.\n\n"
    "Example: a 30 cm × 50 cm beam 6.5 m long: 0.3 × 0.5 × 6.5 × 24 = `23.40 kN`.",
    "Three lines: width (cm, whole number), height (cm, whole number), length (m, decimal number).",
    "One line of the form `W kN`, with W having two digits after the point.",
    "Width 20–60, height 30–90, length 2.0–12.0."),
   ("الوزن الذاتي لكمرة خرسانية",
    "الوزن الذاتي للكمرة = الحجم × الوزن النوعي. للخرسانة المسلحة يُستعمل 24 kN/m³ (معامل تدريبي). المقطع معطى بالسنتيمتر والطول بالمتر: "
    "حوِّل المقطع إلى المتر أولًا لتتوافق الوحدات.\n\nمثال: كمرة 30 سم × 50 سم طولها 6.5 م: 0.3 × 0.5 × 6.5 × 24 = `23.40 kN`.",
    "ثلاثة أسطر: العرض (سم، عدد صحيح)، الارتفاع (سم، عدد صحيح)، الطول (م، عدد عشري).",
    "سطر واحد بالشكل `W kN` حيث W بمنزلتين عشريتين.",
    "العرض 20–60، الارتفاع 30–90، الطول 2.0–12.0."))

py("tekanan-hidrostatik", "mudah",
   AMAN + "k = r.randint(5, 400)\nwhile not aman(9.81 * k / 10, 2):\n    k = r.randint(5, 400)\nprint(f'{k / 10:.1f}')\n",
   r'''
kedalaman_m = float(input())
massa_jenis = 1000
gravitasi = 9.81
tekanan_Pa = massa_jenis * gravitasi * kedalaman_m
print(f"{tekanan_Pa / 1000:.2f} kPa")
''',
   ("Tekanan Hidrostatik di Dasar Kolam",
    "Tekanan air pada kedalaman h adalah p = ρ × g × h, dengan ρ = 1000 kg/m³ dan g = 9,81 m/s² (parameter latihan). Hasil rumus itu "
    "dalam pascal; laporkan dalam kilopascal (1 kPa = 1000 Pa).\n\nContoh: kedalaman `3.2` m: 1000 × 9,81 × 3,2 = 31 392 Pa = `31.39 kPa`.",
    "Satu baris: kedalaman air dalam meter (bilangan pecahan).",
    "Satu baris berbentuk `P kPa`, dengan P dua angka di belakang titik.",
    "Kedalaman 0.5 sampai 40.0."),
   ("Hydrostatic Pressure at the Bottom of a Basin",
    "Water pressure at depth h is p = ρ × g × h, with ρ = 1000 kg/m³ and g = 9.81 m/s² (practice parameters). The formula gives pascals; "
    "report kilopascals (1 kPa = 1000 Pa).\n\nExample: a depth of `3.2` m: 1000 × 9.81 × 3.2 = 31 392 Pa = `31.39 kPa`.",
    "One line: the water depth in metres (a decimal number).",
    "One line of the form `P kPa`, with P having two digits after the point.",
    "The depth is 0.5 to 40.0."),
   ("الضغط الهيدروستاتيكي في قاع الحوض",
    "ضغط الماء عند العمق h هو p = ρ × g × h، حيث ρ = 1000 كغ/م³ و g = 9.81 م/ث² (معاملات تدريبية). ناتج المعادلة بالباسكال؛ "
    "اذكره بالكيلوباسكال (1 kPa = 1000 Pa).\n\nمثال: العمق `3.2` م: 1000 × 9.81 × 3.2 = 31 392 Pa = `31.39 kPa`.",
    "سطر واحد: عمق الماء بالمتر (عدد عشري).",
    "سطر واحد بالشكل `P kPa` حيث P بمنزلتين عشريتين.",
    "العمق من 0.5 إلى 40.0."))

py("kaki-kuda-kuda", "mudah",
   "import math\n" + AMAN + "while True:\n    a = r.randint(15, 120)\n    t = r.randint(10, 60)\n"
   "    if aman(math.sqrt((a / 10) ** 2 + (t / 10) ** 2), 2):\n        break\nprint(f'{a / 10:.1f}')\nprint(f'{t / 10:.1f}')\n",
   r'''
import math

setengah_bentang_m = float(input())
tinggi_m = float(input())
panjang_kaki_m = math.sqrt(setengah_bentang_m ** 2 + tinggi_m ** 2)
print(f"{panjang_kaki_m:.2f}")
''',
   ("Panjang Kaki Kuda-Kuda",
    "Kuda-kuda atap berbentuk segitiga sama kaki. Panjang kaki kuda-kuda adalah sisi miring segitiga siku-siku dengan sisi datar "
    "setengah bentang dan sisi tegak tinggi kuda-kuda: akar dari (a² + t²). Gunakan `math.sqrt`.\n\n"
    "Contoh: setengah bentang `4.0` m dan tinggi `2.5` m menghasilkan `4.72`.",
    "Dua baris: setengah bentang, lalu tinggi kuda-kuda; bilangan pecahan dalam meter.",
    "Satu baris: panjang kaki kuda-kuda dalam meter, dua angka di belakang titik.",
    "Setengah bentang 1.5–12.0; tinggi 1.0–6.0."),
   ("Length of a Rafter",
    "A roof truss is an isosceles triangle. The rafter is the hypotenuse of a right triangle whose horizontal side is half the span and "
    "whose vertical side is the height of the truss: the square root of (a² + t²). Use `math.sqrt`.\n\n"
    "Example: a half span of `4.0` m and a height of `2.5` m give `4.72`.",
    "Two lines: half the span, then the truss height; decimal numbers in metres.",
    "One line: the rafter length in metres, two digits after the point.",
    "Half span 1.5–12.0; height 1.0–6.0."),
   ("طول ضلع الجمالون",
    "جمالون السقف مثلث متساوي الساقين. طول الضلع المائل هو وتر مثلث قائم ضلعه الأفقي نصف البحر وضلعه الرأسي ارتفاع الجمالون: "
    "الجذر التربيعي لـ (a² + t²). استعمل `math.sqrt`.\n\nمثال: نصف البحر `4.0` م والارتفاع `2.5` م يعطيان `4.72`.",
    "سطران: نصف البحر ثم ارتفاع الجمالون؛ عددان عشريان بالمتر.",
    "سطر واحد: طول الضلع المائل بالمتر بمنزلتين عشريتين.",
    "نصف البحر 1.5–12.0؛ الارتفاع 1.0–6.0."))

py("rincian-material", "mudah",
   "print(r.choice(['Semen', 'Pasir', 'Kerikil', 'Bata', 'Besi', 'Kayu', 'Cat', 'Keramik', 'Paku', 'Genteng']))\n"
   "print(r.randint(1, 500))\nprint(f'{r.randint(50000, 9999999) / 100:.2f}')\n",
   r'''
nama = input()
jumlah = int(input())
harga_satuan = float(input())
total = jumlah * harga_satuan
print(f"{nama}: {jumlah} x {harga_satuan:.2f} = {total:.2f}")
''',
   ("Satu Baris Rincian Material",
    "Sebuah baris rincian pembelian memuat tiga jenis data: nama material (`str`), jumlah (`int`), dan harga satuan (`float`). "
    "Baca ketiganya dengan tipe yang tepat, lalu cetak satu baris rincian beserta totalnya.\n\n"
    "Contoh: `Semen`, `40`, `62500.50` menghasilkan `Semen: 40 x 62500.50 = 2500020.00`.",
    "Tiga baris: nama material (satu kata), jumlah (bilangan bulat), harga satuan (bilangan pecahan).",
    "Satu baris berbentuk `Nama: J x H = T`, dengan H dan T dua angka di belakang titik.",
    "Jumlah 1–500; harga satuan 500.00–99999.99."),
   ("One Line of a Material List",
    "A line of a purchase list holds three kinds of data: the material name (`str`), the quantity (`int`) and the unit price (`float`). "
    "Read each with the right type, then print one line with the total.\n\n"
    "Example: `Semen`, `40`, `62500.50` give `Semen: 40 x 62500.50 = 2500020.00`.",
    "Three lines: the material name (one word), the quantity (whole number), the unit price (decimal number).",
    "One line of the form `Name: J x H = T`, with H and T having two digits after the point.",
    "Quantity 1–500; unit price 500.00–99999.99."),
   ("سطر واحد من قائمة المواد",
    "يضم سطر في قائمة المشتريات ثلاثة أنواع من البيانات: اسم المادة (`str`) والكمية (`int`) وسعر الوحدة (`float`). "
    "اقرأ كلًّا منها بالنوع الصحيح ثم اطبع سطرًا واحدًا مع الإجمالي.\n\n"
    "مثال: `Semen` و`40` و`62500.50` تعطي `Semen: 40 x 62500.50 = 2500020.00`.",
    "ثلاثة أسطر: اسم المادة (كلمة واحدة)، الكمية (عدد صحيح)، سعر الوحدة (عدد عشري).",
    "سطر واحد بالشكل `Nama: J x H = T` حيث H و T بمنزلتين عشريتين.",
    "الكمية 1–500؛ سعر الوحدة 500.00–99999.99."))

py("debit-pipa", "mudah",
   "import math\n" + AMAN + "while True:\n    d = r.choice([50, 75, 100, 150, 200, 250, 300, 400, 500, 600])\n    k = r.randint(3, 40)\n"
   "    if aman(math.pi * (d / 1000) ** 2 / 4 * (k / 10) * 1000, 2):\n        break\nprint(d)\nprint(f'{k / 10:.1f}')\n",
   r'''
import math

diameter_mm = int(input())
kecepatan_m_detik = float(input())
diameter_m = diameter_mm / 1000
luas_m2 = math.pi * diameter_m ** 2 / 4
debit_liter_detik = luas_m2 * kecepatan_m_detik * 1000
print(f"{debit_liter_detik:.2f} liter/detik")
''',
   ("Debit Pipa Penuh",
    "Air mengalir penuh di dalam pipa bulat. Debit = luas penampang × kecepatan, dengan luas lingkaran π × d² ÷ 4 (`math.pi`). "
    "Diameter diberikan dalam mm; ubah ke meter sebelum dihitung. Debit dalam m³/detik lalu diubah ke liter/detik (1 m³ = 1000 liter).\n\n"
    "Contoh: diameter `150` mm dan kecepatan `1.2` m/detik menghasilkan `21.21 liter/detik`.",
    "Dua baris: diameter pipa (mm, bilangan bulat), kecepatan aliran (m/detik, bilangan pecahan).",
    "Satu baris berbentuk `Q liter/detik`, dengan Q dua angka di belakang titik.",
    "Diameter 50–600; kecepatan 0.3–4.0."),
   ("Discharge of a Full Pipe",
    "Water flows full in a round pipe. Discharge = cross-sectional area × velocity, with the area of a circle π × d² ÷ 4 (`math.pi`). "
    "The diameter is given in mm; convert it to metres before computing. The discharge in m³/s is then converted to litres per second "
    "(1 m³ = 1000 litres).\n\nExample: a diameter of `150` mm and a velocity of `1.2` m/s give `21.21 liter/detik`.",
    "Two lines: pipe diameter (mm, whole number), flow velocity (m/s, decimal number).",
    "One line of the form `Q liter/detik`, with Q having two digits after the point.",
    "Diameter 50–600; velocity 0.3–4.0."),
   ("تصريف أنبوب ممتلئ",
    "يجري الماء ممتلئًا في أنبوب دائري. التصريف = مساحة المقطع × السرعة، ومساحة الدائرة π × d² ÷ 4 (`math.pi`). القطر معطى بالمليمتر؛ "
    "حوِّله إلى المتر قبل الحساب. ثم يُحوَّل التصريف من م³/ث إلى لتر/ث (1 م³ = 1000 لتر).\n\n"
    "مثال: القطر `150` مم والسرعة `1.2` م/ث يعطيان `21.21 liter/detik`.",
    "سطران: قطر الأنبوب (مم، عدد صحيح)، سرعة الجريان (م/ث، عدد عشري).",
    "سطر واحد بالشكل `Q liter/detik` حيث Q بمنزلتين عشريتين.",
    "القطر 50–600؛ السرعة 0.3–4.0."))

py("kadar-air-tanah", "mudah",
   AMAN + "while True:\n    kering = r.randint(2000, 20000)\n    basah = kering + r.randint(100, 9000)\n"
   "    if aman((basah - kering) / kering * 100, 1):\n        break\nprint(f'{basah / 10:.1f}')\nprint(f'{kering / 10:.1f}')\n",
   r'''
berat_basah_g = float(input())
berat_kering_g = float(input())
berat_air_g = berat_basah_g - berat_kering_g
kadar_air = berat_air_g / berat_kering_g * 100
print(f"{kadar_air:.1f} %")
''',
   ("Kadar Air Tanah",
    "Di laboratorium mekanika tanah, contoh tanah ditimbang sebelum dan sesudah dikeringkan dalam oven. Kadar air = berat air ÷ berat "
    "tanah kering × 100 %, dengan berat air = berat basah − berat kering. Perhatikan: pembaginya berat KERING.\n\n"
    "Contoh: berat basah `1250.5` g dan berat kering `1040.0` g menghasilkan `20.2 %`.",
    "Dua baris: berat basah, lalu berat kering; bilangan pecahan dalam gram.",
    "Satu baris berbentuk `W %`, dengan W satu angka di belakang titik.",
    "Berat kering 200.0–2000.0; berat basah lebih besar dari berat kering."),
   ("Water Content of a Soil Sample",
    "In the soil mechanics laboratory a sample is weighed before and after oven drying. Water content = weight of water ÷ weight of dry "
    "soil × 100 %, where weight of water = wet weight − dry weight. Note: the divisor is the DRY weight.\n\n"
    "Example: a wet weight of `1250.5` g and a dry weight of `1040.0` g give `20.2 %`.",
    "Two lines: the wet weight, then the dry weight; decimal numbers in grams.",
    "One line of the form `W %`, with W having one digit after the point.",
    "Dry weight 200.0–2000.0; the wet weight is larger than the dry weight."),
   ("محتوى الماء في عينة تربة",
    "في مختبر ميكانيكا التربة تُوزن العينة قبل التجفيف في الفرن وبعده. محتوى الماء = وزن الماء ÷ وزن التربة الجافة × 100 %، "
    "ووزن الماء = الوزن الرطب − الوزن الجاف. انتبه: المقسوم عليه هو الوزن الجاف.\n\n"
    "مثال: الوزن الرطب `1250.5` غ والوزن الجاف `1040.0` غ يعطيان `20.2 %`.",
    "سطران: الوزن الرطب ثم الوزن الجاف؛ عددان عشريان بالغرام.",
    "سطر واحد بالشكل `W %` حيث W بمنزلة عشرية واحدة.",
    "الوزن الجاف 200.0–2000.0؛ والوزن الرطب أكبر من الجاف."))

py("skala-peta", "mudah",
   AMAN + "while True:\n    k = r.randint(5, 400)\n    n = r.choice([500, 1000, 2000, 2500, 5000, 10000, 25000, 50000])\n"
   "    if aman(k / 10 * n / 100000, 3):\n        break\nprint(f'{k / 10:.1f}')\nprint(n)\n",
   r'''
jarak_peta_cm = float(input())
skala = int(input())
jarak_cm = jarak_peta_cm * skala
print(f"{jarak_cm / 100:.1f} m")
print(f"{jarak_cm / 100000:.3f} km")
''',
   ("Jarak Sebenarnya dari Peta",
    "Pada peta berskala 1 : n, satu sentimeter di peta mewakili n sentimeter di lapangan. Dari jarak yang diukur di peta, hitung jarak "
    "sebenarnya dalam meter dan dalam kilometer (1 m = 100 cm; 1 km = 100 000 cm).\n\n"
    "Contoh: `7.4` cm pada peta 1 : `2500` adalah 18 500 cm, yaitu\n\n```\n185.0 m\n0.185 km\n```",
    "Dua baris: jarak di peta (cm, bilangan pecahan), lalu n dari skala 1 : n (bilangan bulat).",
    "Baris pertama `X m` dengan satu angka di belakang titik; baris kedua `Y km` dengan tiga angka di belakang titik.",
    "Jarak di peta 0.5–40.0; n antara 500 dan 50000."),
   ("True Distance from a Map",
    "On a map at scale 1 : n, one centimetre on the map stands for n centimetres on the ground. From a distance measured on the map, "
    "compute the true distance in metres and in kilometres (1 m = 100 cm; 1 km = 100 000 cm).\n\n"
    "Example: `7.4` cm on a 1 : `2500` map is 18 500 cm, that is\n\n```\n185.0 m\n0.185 km\n```",
    "Two lines: the distance on the map (cm, decimal number), then n of the scale 1 : n (whole number).",
    "First line `X m` with one digit after the point; second line `Y km` with three digits after the point.",
    "Map distance 0.5–40.0; n between 500 and 50000."),
   ("المسافة الحقيقية من الخريطة",
    "في خريطة بمقياس 1 : n يمثّل السنتيمتر الواحد على الخريطة n سنتيمترًا على الأرض. من مسافة مقيسة على الخريطة احسب المسافة الحقيقية "
    "بالمتر وبالكيلومتر (1 م = 100 سم؛ 1 كم = 100 000 سم).\n\n"
    "مثال: `7.4` سم على خريطة 1 : `2500` تساوي 18 500 سم، أي\n\n```\n185.0 m\n0.185 km\n```",
    "سطران: المسافة على الخريطة (سم، عدد عشري)، ثم n من المقياس 1 : n (عدد صحيح).",
    "السطر الأول `X m` بمنزلة عشرية واحدة؛ السطر الثاني `Y km` بثلاث منازل عشرية.",
    "المسافة على الخريطة 0.5–40.0؛ و n بين 500 و50000."))

py("sudut-lereng", "mudah",
   "import math\n" + AMAN + "while True:\n    h = r.randint(5, 200)\n    d = r.randint(10, 400)\n"
   "    if aman(math.degrees(math.atan(h / d)), 1):\n        break\nprint(f'{h / 10:.1f}')\nprint(f'{d / 10:.1f}')\n",
   r'''
import math

tinggi_m = float(input())
jarak_datar_m = float(input())
sudut_radian = math.atan(tinggi_m / jarak_datar_m)
sudut_derajat = math.degrees(sudut_radian)
print(f"{sudut_derajat:.1f} derajat")
''',
   ("Sudut Kemiringan Lereng",
    "Sudut lereng terhadap bidang datar didapat dari tinggi dan jarak datarnya: sudut = arctan(tinggi ÷ jarak datar). "
    "`math.atan` mengembalikan sudut dalam radian; ubah ke derajat dengan `math.degrees`.\n\n"
    "Contoh: tinggi `3.0` m dan jarak datar `4.5` m menghasilkan `33.7 derajat`.",
    "Dua baris: tinggi lereng, lalu jarak datarnya; bilangan pecahan dalam meter.",
    "Satu baris berbentuk `S derajat`, dengan S satu angka di belakang titik.",
    "Tinggi 0.5–20.0; jarak datar 1.0–40.0."),
   ("Slope Angle",
    "The angle of a slope to the horizontal follows from its height and horizontal distance: angle = arctan(height ÷ horizontal distance). "
    "`math.atan` returns radians; convert to degrees with `math.degrees`.\n\n"
    "Example: a height of `3.0` m and a horizontal distance of `4.5` m give `33.7 derajat`.",
    "Two lines: the height of the slope, then its horizontal distance; decimal numbers in metres.",
    "One line of the form `S derajat`, with S having one digit after the point.",
    "Height 0.5–20.0; horizontal distance 1.0–40.0."),
   ("زاوية ميل المنحدر",
    "زاوية المنحدر مع الأفق تُحسب من ارتفاعه ومسافته الأفقية: الزاوية = arctan(الارتفاع ÷ المسافة الأفقية). "
    "الدالة `math.atan` تعيد الزاوية بالراديان؛ حوِّلها إلى درجات بالدالة `math.degrees`.\n\n"
    "مثال: الارتفاع `3.0` م والمسافة الأفقية `4.5` م يعطيان `33.7 derajat`.",
    "سطران: ارتفاع المنحدر ثم مسافته الأفقية؛ عددان عشريان بالمتر.",
    "سطر واحد بالشكل `S derajat` حيث S بمنزلة عشرية واحدة.",
    "الارتفاع 0.5–20.0؛ المسافة الأفقية 1.0–40.0."))

# ============================== MUDAH-SEDANG (6) ==============================

py("satuan-tegangan", "mudah_sedang",
   AMAN + "k = r.randint(100, 9000)\nwhile not (aman(k / 10 * 0.0980665, 3) and aman(k / 10 * 98.0665, 1)):\n    k = r.randint(100, 9000)\n"
   "print(f'{k / 10:.1f}')\n",
   r'''
tegangan_kgf_cm2 = float(input())
tegangan_MPa = tegangan_kgf_cm2 * 0.0980665
tegangan_kPa = tegangan_MPa * 1000
print(f"{tegangan_MPa:.3f} MPa")
print(f"{tegangan_kPa:.1f} kPa")
''',
   ("Tegangan Izin dalam Tiga Satuan",
    "Tabel lama mencantumkan tegangan izin dalam kgf/cm². Ubah ke MPa dengan 1 kgf/cm² = 0,0980665 MPa, lalu ke kPa (1 MPa = 1000 kPa).\n\n"
    "Contoh: `250.0` kgf/cm² menghasilkan\n\n```\n24.517 MPa\n24516.6 kPa\n```",
    "Satu baris: tegangan dalam kgf/cm² (bilangan pecahan).",
    "Baris pertama `A MPa` dengan tiga angka di belakang titik; baris kedua `B kPa` dengan satu angka di belakang titik.",
    "Tegangan 10.0 sampai 900.0."),
   ("Allowable Stress in Three Units",
    "An old table lists an allowable stress in kgf/cm². Convert it to MPa with 1 kgf/cm² = 0.0980665 MPa, then to kPa (1 MPa = 1000 kPa).\n\n"
    "Example: `250.0` kgf/cm² gives\n\n```\n24.517 MPa\n24516.6 kPa\n```",
    "One line: the stress in kgf/cm² (a decimal number).",
    "First line `A MPa` with three digits after the point; second line `B kPa` with one digit after the point.",
    "The stress is 10.0 to 900.0."),
   ("الإجهاد المسموح بثلاث وحدات",
    "يذكر جدول قديم الإجهاد المسموح بوحدة kgf/cm². حوِّله إلى MPa باعتبار 1 kgf/cm² = 0.0980665 MPa، ثم إلى kPa (1 MPa = 1000 kPa).\n\n"
    "مثال: `250.0` kgf/cm² تعطي\n\n```\n24.517 MPa\n24516.6 kPa\n```",
    "سطر واحد: الإجهاد بوحدة kgf/cm² (عدد عشري).",
    "السطر الأول `A MPa` بثلاث منازل عشرية؛ السطر الثاني `B kPa` بمنزلة عشرية واحدة.",
    "الإجهاد من 10.0 إلى 900.0."))

py("kuat-tekan-silinder", "mudah_sedang",
   "import math\n" + AMAN + "k = r.randint(2000, 9000)\nwhile not aman(k / 10 * 1000 / (math.pi * 150 ** 2 / 4), 2):\n    k = r.randint(2000, 9000)\n"
   "print(f'{k / 10:.1f}')\n",
   r'''
import math

beban_kN = float(input())
diameter_mm = 150
luas_mm2 = math.pi * diameter_mm ** 2 / 4
kuat_tekan_MPa = beban_kN * 1000 / luas_mm2
print(f"{kuat_tekan_MPa:.2f} MPa")
''',
   ("Kuat Tekan Silinder Beton",
    "Benda uji silinder berdiameter 150 mm ditekan sampai hancur. Kuat tekan = beban maksimum ÷ luas penampang. Dengan beban dalam "
    "newton dan luas dalam mm², hasilnya dalam MPa. Beban dari mesin uji dalam kN (1 kN = 1000 N); luas lingkaran π × d² ÷ 4 (`math.pi`).\n\n"
    "Contoh: beban maksimum `530.0` kN menghasilkan `29.99 MPa`.",
    "Satu baris: beban maksimum dalam kN (bilangan pecahan).",
    "Satu baris berbentuk `F MPa`, dengan F dua angka di belakang titik.",
    "Beban 200.0 sampai 900.0."),
   ("Compressive Strength of a Concrete Cylinder",
    "A cylinder specimen of 150 mm diameter is loaded until it fails. Compressive strength = maximum load ÷ cross-sectional area. With the "
    "load in newtons and the area in mm², the result is in MPa. The testing machine gives kN (1 kN = 1000 N); the area of a circle is "
    "π × d² ÷ 4 (`math.pi`).\n\nExample: a maximum load of `530.0` kN gives `29.99 MPa`.",
    "One line: the maximum load in kN (a decimal number).",
    "One line of the form `F MPa`, with F having two digits after the point.",
    "The load is 200.0 to 900.0."),
   ("مقاومة الضغط لأسطوانة خرسانية",
    "تُحمَّل عينة أسطوانية قطرها 150 مم حتى الانهيار. مقاومة الضغط = الحمل الأقصى ÷ مساحة المقطع. إذا كان الحمل بالنيوتن والمساحة "
    "بالمليمتر المربع كان الناتج بالميغاباسكال. تعطي آلة الاختبار الحمل بالكيلونيوتن (1 kN = 1000 N)؛ ومساحة الدائرة π × d² ÷ 4 (`math.pi`).\n\n"
    "مثال: الحمل الأقصى `530.0` kN يعطي `29.99 MPa`.",
    "سطر واحد: الحمل الأقصى بالكيلونيوتن (عدد عشري).",
    "سطر واحد بالشكل `F MPa` حيث F بمنزلتين عشريتين.",
    "الحمل من 200.0 إلى 900.0."))

py("timbunan-gembur", "mudah_sedang",
   "import math\n" + AMAN + "while True:\n    v = r.randint(50, 5000)\n    s = r.randint(80, 95)\n    g = r.randint(110, 140)\n    k = r.randint(4, 25)\n"
   "    asli = v / (s / 100)\n    lepas = asli * (g / 100)\n    if aman(asli, 2) and aman(lepas, 2) and jauh(lepas / k):\n        break\n"
   "print(v)\nprint(f'{s / 100:.2f}')\nprint(f'{g / 100:.2f}')\nprint(k)\n",
   r'''
import math

volume_padat_m3 = int(input())
faktor_susut = float(input())
faktor_gembur = float(input())
kapasitas_truk_m3 = int(input())
volume_asli_m3 = volume_padat_m3 / faktor_susut
volume_lepas_m3 = volume_asli_m3 * faktor_gembur
jumlah_truk = math.ceil(volume_lepas_m3 / kapasitas_truk_m3)
print(f"{volume_asli_m3:.2f}")
print(f"{volume_lepas_m3:.2f}")
print(jumlah_truk)
''',
   ("Tanah Asli, Lepas, dan Padat",
    "Volume tanah berubah menurut keadaannya. Untuk mendapat timbunan padat sebanyak V, tanah asli yang harus digali adalah "
    "V ÷ faktor susut. Setelah digali tanah mengembang: volume lepas = volume asli × faktor gembur. Tanah lepas itulah yang diangkut "
    "truk; jumlah truk dibulatkan ke atas dengan `math.ceil`.\n\n"
    "Contoh: timbunan padat `450` m³, faktor susut `0.85`, faktor gembur `1.25`, truk `7` m³ menghasilkan\n\n```\n529.41\n661.76\n95\n```",
    "Empat baris: volume padat (m³, bilangan bulat), faktor susut (pecahan), faktor gembur (pecahan), kapasitas truk (m³, bilangan bulat).",
    "Tiga baris: volume asli dan volume lepas dalam m³ dengan dua angka di belakang titik, lalu jumlah truk (bilangan bulat).",
    "Volume padat 50–5000; faktor susut 0.80–0.95; faktor gembur 1.10–1.40; kapasitas truk 4–25."),
   ("Bank, Loose and Compacted Soil",
    "The volume of soil depends on its state. To obtain a compacted fill of V, the bank volume to excavate is V ÷ shrinkage factor. "
    "Once dug, soil swells: loose volume = bank volume × swell factor. Trucks carry the loose soil; the number of trucks is rounded up "
    "with `math.ceil`.\n\n"
    "Example: a compacted fill of `450` m³, shrinkage factor `0.85`, swell factor `1.25`, `7` m³ trucks give\n\n```\n529.41\n661.76\n95\n```",
    "Four lines: compacted volume (m³, whole number), shrinkage factor (decimal), swell factor (decimal), truck capacity (m³, whole number).",
    "Three lines: bank volume and loose volume in m³ with two digits after the point, then the number of trucks (a whole number).",
    "Compacted volume 50–5000; shrinkage factor 0.80–0.95; swell factor 1.10–1.40; truck capacity 4–25."),
   ("التربة الطبيعية والمفككة والمدموكة",
    "يتغير حجم التربة بحسب حالتها. للحصول على ردم مدموك حجمه V يكون حجم التربة الطبيعية الواجب حفرها V ÷ معامل الانكماش. "
    "بعد الحفر تنتفش التربة: الحجم المفكك = الحجم الطبيعي × معامل الانتفاش. الشاحنات تنقل التربة المفككة؛ ويُقرَّب عدد الشاحنات إلى الأعلى "
    "بالدالة `math.ceil`.\n\n"
    "مثال: ردم مدموك `450` م³، معامل الانكماش `0.85`، معامل الانتفاش `1.25`، شاحنات `7` م³ تعطي\n\n```\n529.41\n661.76\n95\n```",
    "أربعة أسطر: الحجم المدموك (م³، عدد صحيح)، معامل الانكماش (عشري)، معامل الانتفاش (عشري)، سعة الشاحنة (م³، عدد صحيح).",
    "ثلاثة أسطر: الحجم الطبيعي والحجم المفكك بالمتر المكعب بمنزلتين عشريتين، ثم عدد الشاحنات (عدد صحيح).",
    "الحجم المدموك 50–5000؛ معامل الانكماش 0.80–0.95؛ معامل الانتفاش 1.10–1.40؛ سعة الشاحنة 4–25."))

py("intensitas-mononobe", "mudah_sedang",
   AMAN + "while True:\n    R = r.randint(20, 300)\n    k = r.randint(5, 120)\n"
   "    if aman((R / 24) * (24 / (k / 10)) ** (2 / 3), 2):\n        break\nprint(R)\nprint(f'{k / 10:.1f}')\n",
   r'''
hujan_harian_mm = int(input())
durasi_jam = float(input())
intensitas = (hujan_harian_mm / 24) * (24 / durasi_jam) ** (2 / 3)
print(f"{intensitas:.2f} mm/jam")
''',
   ("Intensitas Hujan Rumus Mononobe",
    "Bila hanya curah hujan harian yang tersedia, intensitas hujan untuk durasi t jam ditaksir dengan rumus Mononobe:\n\n"
    "I = (R₂₄ ÷ 24) × (24 ÷ t)^(2/3)\n\n"
    "dengan R₂₄ curah hujan harian (mm). Perhatikan prioritas operator: pangkat `**` dikerjakan lebih dulu daripada kali, dan pangkatnya "
    "sendiri, 2/3, harus diberi kurung.\n\nContoh: R₂₄ = `120` mm dan t = `2.0` jam menghasilkan `26.21 mm/jam`.",
    "Dua baris: curah hujan harian (mm, bilangan bulat), durasi hujan (jam, bilangan pecahan).",
    "Satu baris berbentuk `I mm/jam`, dengan I dua angka di belakang titik.",
    "Curah hujan 20–300; durasi 0.5–12.0."),
   ("Rainfall Intensity by the Mononobe Formula",
    "When only daily rainfall is available, the rainfall intensity for a duration of t hours is estimated by the Mononobe formula:\n\n"
    "I = (R₂₄ ÷ 24) × (24 ÷ t)^(2/3)\n\n"
    "with R₂₄ the daily rainfall (mm). Mind operator precedence: the power `**` is done before multiplication, and the exponent itself, "
    "2/3, needs brackets.\n\nExample: R₂₄ = `120` mm and t = `2.0` hours give `26.21 mm/jam`.",
    "Two lines: daily rainfall (mm, whole number), rainfall duration (hours, decimal number).",
    "One line of the form `I mm/jam`, with I having two digits after the point.",
    "Rainfall 20–300; duration 0.5–12.0."),
   ("شدة المطر بمعادلة مونونوبي",
    "عندما لا يتوافر إلا الهطول اليومي تُقدَّر شدة المطر لمدة t ساعة بمعادلة مونونوبي:\n\n"
    "I = (R₂₄ ÷ 24) × (24 ÷ t)^(2/3)\n\n"
    "حيث R₂₄ الهطول اليومي (مم). انتبه إلى أولوية العوامل: الأس `**` يُنفَّذ قبل الضرب، والأس نفسه 2/3 يحتاج إلى قوسين.\n\n"
    "مثال: R₂₄ = `120` مم و t = `2.0` ساعة يعطيان `26.21 mm/jam`.",
    "سطران: الهطول اليومي (مم، عدد صحيح)، مدة المطر (ساعة، عدد عشري).",
    "سطر واحد بالشكل `I mm/jam` حيث I بمنزلتين عشريتين.",
    "الهطول 20–300؛ المدة 0.5–12.0."))

py("kuitansi-material", "mudah_sedang",
   "print(r.choice(['Semen', 'Pasir', 'Kerikil', 'Bata', 'Besi', 'Kayu', 'Cat', 'Keramik', 'Paku', 'Genteng', 'Tripleks']))\n"
   "print(r.randint(1, 999))\nprint(f'{r.randint(50000, 9999999) / 100:.2f}')\n",
   r'''
nama = input()
jumlah = int(input())
harga_satuan = float(input())
total = jumlah * harga_satuan
print(f"{nama:<12}{jumlah:>6}{harga_satuan:>14.2f}")
print(f"{'Total':<12}{'':>6}{total:>14.2f}")
''',
   ("Kuitansi Berkolom Rapi",
    "Kuitansi toko bangunan dicetak dalam kolom berlebar tetap supaya angkanya lurus. Cetak dua baris, masing-masing 32 karakter:\n\n"
    "- baris pertama: nama material rata kiri selebar 12 karakter (`:<12`), jumlah rata kanan selebar 6 (`:>6`), harga satuan rata kanan "
    "selebar 14 dengan dua desimal (`:>14.2f`);\n"
    "- baris kedua: kata `Total` rata kiri selebar 12, lalu 6 spasi, lalu total (jumlah × harga satuan) rata kanan selebar 14 dengan dua desimal.\n\n"
    "Contoh: `Semen`, `40`, `62500.50` menghasilkan\n\n```\nSemen           40      62500.50\nTotal                 2500020.00\n```",
    "Tiga baris: nama material (satu kata, paling panjang 10 huruf), jumlah (bilangan bulat), harga satuan (bilangan pecahan).",
    "Dua baris seperti dijelaskan. Spasi di antara kolom harus tepat; spasi di ujung kanan baris diabaikan.",
    "Jumlah 1–999; harga satuan 500.00–99999.99."),
   ("A Receipt in Neat Columns",
    "A builder's merchant prints receipts in fixed-width columns so that the numbers line up. Print two lines of 32 characters each:\n\n"
    "- first line: the material name left-aligned in 12 characters (`:<12`), the quantity right-aligned in 6 (`:>6`), the unit price "
    "right-aligned in 14 with two decimals (`:>14.2f`);\n"
    "- second line: the word `Total` left-aligned in 12, then 6 spaces, then the total (quantity × unit price) right-aligned in 14 with two decimals.\n\n"
    "Example: `Semen`, `40`, `62500.50` give\n\n```\nSemen           40      62500.50\nTotal                 2500020.00\n```",
    "Three lines: the material name (one word, at most 10 letters), the quantity (whole number), the unit price (decimal number).",
    "Two lines as described. The spaces between columns must be exact; spaces at the right end of a line are ignored.",
    "Quantity 1–999; unit price 500.00–99999.99."),
   ("إيصال بأعمدة مرتبة",
    "يطبع متجر مواد البناء الإيصالات في أعمدة ثابتة العرض لتتحاذى الأرقام. اطبع سطرين طول كل منهما 32 محرفًا:\n\n"
    "- السطر الأول: اسم المادة بمحاذاة اليسار في 12 محرفًا (`:<12`)، والكمية بمحاذاة اليمين في 6 (`:>6`)، وسعر الوحدة بمحاذاة اليمين "
    "في 14 بمنزلتين عشريتين (`:>14.2f`)؛\n"
    "- السطر الثاني: الكلمة `Total` بمحاذاة اليسار في 12، ثم 6 مسافات، ثم الإجمالي (الكمية × سعر الوحدة) بمحاذاة اليمين في 14 بمنزلتين عشريتين.\n\n"
    "مثال: `Semen` و`40` و`62500.50` تعطي\n\n```\nSemen           40      62500.50\nTotal                 2500020.00\n```",
    "ثلاثة أسطر: اسم المادة (كلمة واحدة من 10 أحرف على الأكثر)، الكمية (عدد صحيح)، سعر الوحدة (عدد عشري).",
    "سطران كما وُصف. يجب أن تكون المسافات بين الأعمدة مضبوطة؛ وتُهمَل المسافات في نهاية السطر.",
    "الكمية 1–999؛ سعر الوحدة 500.00–99999.99."))

py("sudut-desimal", "mudah_sedang",
   AMAN + "while True:\n    d = r.randint(0, 359)\n    m = r.randint(0, 59)\n    s = r.randint(0, 59)\n"
   "    if aman(d + m / 60 + s / 3600, 4):\n        break\nprint(d)\nprint(m)\nprint(s)\n",
   r'''
derajat = int(input())
menit = int(input())
detik = int(input())
sudut = derajat + menit / 60 + detik / 3600
print(f"{sudut:.4f}")
''',
   ("Azimut ke Derajat Desimal",
    "Teodolit menampilkan sudut dalam derajat, menit, dan detik, tetapi fungsi trigonometri memerlukan derajat desimal. "
    "Satu derajat = 60 menit = 3600 detik.\n\nContoh: 112° 30′ 45″ adalah 112 + 30 ÷ 60 + 45 ÷ 3600 = `112.5125`.",
    "Tiga baris: derajat, menit, detik (bilangan bulat).",
    "Satu baris: sudut dalam derajat desimal, empat angka di belakang titik.",
    "Derajat 0–359; menit dan detik 0–59."),
   ("Azimuth to Decimal Degrees",
    "A theodolite shows an angle in degrees, minutes and seconds, but trigonometric functions need decimal degrees. "
    "One degree = 60 minutes = 3600 seconds.\n\nExample: 112° 30′ 45″ is 112 + 30 ÷ 60 + 45 ÷ 3600 = `112.5125`.",
    "Three lines: degrees, minutes, seconds (whole numbers).",
    "One line: the angle in decimal degrees, four digits after the point.",
    "Degrees 0–359; minutes and seconds 0–59."),
   ("السمت بالدرجات العشرية",
    "يعرض التيودوليت الزاوية بالدرجات والدقائق والثواني، لكن الدوال المثلثية تحتاج إلى درجات عشرية. الدرجة = 60 دقيقة = 3600 ثانية.\n\n"
    "مثال: 112° 30′ 45″ تساوي 112 + 30 ÷ 60 + 45 ÷ 3600 = `112.5125`.",
    "ثلاثة أسطر: الدرجات، الدقائق، الثواني (أعداد صحيحة).",
    "سطر واحد: الزاوية بالدرجات العشرية بأربع منازل عشرية.",
    "الدرجات 0–359؛ الدقائق والثواني 0–59."))

# ================================= SEDANG (6) =================================

py("lendutan-balok", "sedang",
   AMAN + "while True:\n    q = r.randint(20, 400)\n    L = r.randint(20, 100)\n    E = r.choice([20000, 23500, 25000, 30000, 200000])\n"
   "    I = r.randint(20, 900) * 1000\n    if aman(5 * (q / 10) * (L / 10 * 1000) ** 4 / (384 * E * I * 10000), 2):\n        break\n"
   "print(f'{q / 10:.1f}')\nprint(f'{L / 10:.1f}')\nprint(E)\nprint(I)\n",
   r'''
beban_kN_m = float(input())
bentang_m = float(input())
modulus_MPa = int(input())
inersia_cm4 = int(input())
beban_N_mm = beban_kN_m
bentang_mm = bentang_m * 1000
inersia_mm4 = inersia_cm4 * 10000
lendutan_mm = 5 * beban_N_mm * bentang_mm ** 4 / (384 * modulus_MPa * inersia_mm4)
print(f"{lendutan_mm:.2f} mm")
''',
   ("Lendutan Balok Sederhana",
    "Lendutan maksimum balok di atas dua tumpuan dengan beban merata adalah\n\nδ = 5 × q × L⁴ ÷ (384 × E × I)\n\n"
    "Rumus ini hanya benar bila semua besaran memakai satuan yang konsisten. Pakai N dan mm:\n\n"
    "- q diberikan dalam kN/m; 1 kN/m sama dengan 1 N/mm;\n- L diberikan dalam m; ubah ke mm;\n"
    "- E diberikan dalam MPa, yaitu N/mm²;\n- I diberikan dalam cm⁴; 1 cm⁴ = 10 000 mm⁴.\n\n"
    "Contoh: q = `12.0`, L = `6.0`, E = `25000`, I = `180000` menghasilkan `4.50 mm`.",
    "Empat baris: q (kN/m, pecahan), L (m, pecahan), E (MPa, bilangan bulat), I (cm⁴, bilangan bulat).",
    "Satu baris berbentuk `D mm`, dengan D dua angka di belakang titik.",
    "q 2.0–40.0; L 2.0–10.0; E 20000–200000; I 20000–900000."),
   ("Deflection of a Simply Supported Beam",
    "The maximum deflection of a simply supported beam under a uniform load is\n\nδ = 5 × q × L⁴ ÷ (384 × E × I)\n\n"
    "The formula is right only when all quantities use consistent units. Use N and mm:\n\n"
    "- q is given in kN/m; 1 kN/m equals 1 N/mm;\n- L is given in m; convert it to mm;\n"
    "- E is given in MPa, that is N/mm²;\n- I is given in cm⁴; 1 cm⁴ = 10 000 mm⁴.\n\n"
    "Example: q = `12.0`, L = `6.0`, E = `25000`, I = `180000` give `4.50 mm`.",
    "Four lines: q (kN/m, decimal), L (m, decimal), E (MPa, whole number), I (cm⁴, whole number).",
    "One line of the form `D mm`, with D having two digits after the point.",
    "q 2.0–40.0; L 2.0–10.0; E 20000–200000; I 20000–900000."),
   ("ترخيم كمرة بسيطة الارتكاز",
    "الترخيم الأقصى لكمرة على ركيزتين تحت حمل موزع بانتظام هو\n\nδ = 5 × q × L⁴ ÷ (384 × E × I)\n\n"
    "لا تصح المعادلة إلا إذا كانت الوحدات كلها متوافقة. استعمل N و mm:\n\n"
    "- q معطاة بوحدة kN/m؛ و1 kN/m يساوي 1 N/mm؛\n- L معطاة بالمتر؛ حوِّلها إلى المليمتر؛\n"
    "- E معطاة بوحدة MPa، أي N/mm²؛\n- I معطاة بوحدة cm⁴؛ و1 cm⁴ = 10 000 mm⁴.\n\n"
    "مثال: q = `12.0` و L = `6.0` و E = `25000` و I = `180000` تعطي `4.50 mm`.",
    "أربعة أسطر: q (kN/m، عشري)، L (م، عشري)، E (MPa، عدد صحيح)، I (cm⁴، عدد صحيح).",
    "سطر واحد بالشكل `D mm` حيث D بمنزلتين عشريتين.",
    "q من 2.0 إلى 40.0؛ L من 2.0 إلى 10.0؛ E من 20000 إلى 200000؛ I من 20000 إلى 900000."))

py("debit-manning", "sedang",
   AMAN + "while True:\n    b = r.randint(5, 60)\n    h = r.randint(3, 30)\n    n = r.choice([0.012, 0.013, 0.015, 0.017, 0.020, 0.025, 0.030])\n"
   "    S = r.choice([0.0005, 0.001, 0.002, 0.0025, 0.004, 0.005, 0.01])\n    A = (b / 10) * (h / 10)\n    R = A / (b / 10 + 2 * h / 10)\n"
   "    if aman(R, 3) and aman(A * R ** (2 / 3) * S ** 0.5 / n, 3):\n        break\n"
   "print(f'{b / 10:.1f}')\nprint(f'{h / 10:.1f}')\nprint(n)\nprint(S)\n",
   r'''
lebar_m = float(input())
kedalaman_m = float(input())
kekasaran = float(input())
kemiringan = float(input())
luas_m2 = lebar_m * kedalaman_m
keliling_basah_m = lebar_m + 2 * kedalaman_m
jari_hidraulik_m = luas_m2 / keliling_basah_m
debit_m3_detik = (1 / kekasaran) * luas_m2 * jari_hidraulik_m ** (2 / 3) * kemiringan ** 0.5
print(f"A = {luas_m2:.2f} m2")
print(f"R = {jari_hidraulik_m:.3f} m")
print(f"Q = {debit_m3_detik:.3f} m3/detik")
''',
   ("Debit Saluran dengan Rumus Manning",
    "Debit saluran terbuka berpenampang persegi panjang dihitung dengan rumus Manning:\n\n"
    "- luas basah A = b × h;\n- keliling basah P = b + 2 × h;\n- jari-jari hidraulik R = A ÷ P;\n"
    "- debit Q = (1 ÷ n) × A × R^(2/3) × S^(1/2),\n\n"
    "dengan b lebar dasar, h kedalaman air, n koefisien kekasaran Manning, dan S kemiringan dasar saluran.\n\n"
    "Contoh: b = `2.0`, h = `1.2`, n = `0.015`, S = `0.001` menghasilkan\n\n```\nA = 2.40 m2\nR = 0.545 m\nQ = 3.378 m3/detik\n```",
    "Empat baris: b (m), h (m), n, S; bilangan pecahan.",
    "Tiga baris: `A = … m2` dengan dua desimal, `R = … m` dengan tiga desimal, `Q = … m3/detik` dengan tiga desimal.",
    "b 0.5–6.0; h 0.3–3.0; n 0.012–0.030; S 0.0005–0.01."),
   ("Channel Discharge by the Manning Formula",
    "The discharge of an open channel with a rectangular section is computed with the Manning formula:\n\n"
    "- wetted area A = b × h;\n- wetted perimeter P = b + 2 × h;\n- hydraulic radius R = A ÷ P;\n"
    "- discharge Q = (1 ÷ n) × A × R^(2/3) × S^(1/2),\n\n"
    "where b is the bottom width, h the water depth, n the Manning roughness coefficient and S the bed slope.\n\n"
    "Example: b = `2.0`, h = `1.2`, n = `0.015`, S = `0.001` give\n\n```\nA = 2.40 m2\nR = 0.545 m\nQ = 3.378 m3/detik\n```",
    "Four lines: b (m), h (m), n, S; decimal numbers.",
    "Three lines: `A = … m2` with two decimals, `R = … m` with three decimals, `Q = … m3/detik` with three decimals.",
    "b 0.5–6.0; h 0.3–3.0; n 0.012–0.030; S 0.0005–0.01."),
   ("تصريف القناة بمعادلة مانينغ",
    "يُحسب تصريف قناة مكشوفة مستطيلة المقطع بمعادلة مانينغ:\n\n"
    "- المساحة المبلولة A = b × h؛\n- المحيط المبلول P = b + 2 × h؛\n- نصف القطر الهيدروليكي R = A ÷ P؛\n"
    "- التصريف Q = (1 ÷ n) × A × R^(2/3) × S^(1/2)،\n\n"
    "حيث b عرض القاع و h عمق الماء و n معامل خشونة مانينغ و S ميل قاع القناة.\n\n"
    "مثال: b = `2.0` و h = `1.2` و n = `0.015` و S = `0.001` تعطي\n\n```\nA = 2.40 m2\nR = 0.545 m\nQ = 3.378 m3/detik\n```",
    "أربعة أسطر: b (م)، h (م)، n، S؛ أعداد عشرية.",
    "ثلاثة أسطر: `A = … m2` بمنزلتين عشريتين، `R = … m` بثلاث منازل، `Q = … m3/detik` بثلاث منازل.",
    "b من 0.5 إلى 6.0؛ h من 0.3 إلى 3.0؛ n من 0.012 إلى 0.030؛ S من 0.0005 إلى 0.01."))

py("tegangan-lentur", "sedang",
   AMAN + "while True:\n    q = r.randint(20, 300)\n    P = r.randint(0, 800)\n    L = r.randint(20, 100)\n    b = r.randint(15, 50) * 10\n"
   "    h = r.randint(25, 90) * 10\n    M = (q / 10) * (L / 10) ** 2 / 8 + (P / 10) * (L / 10) / 4\n"
   "    if aman(M, 2) and aman(M * 1e6 / (b * h * h / 6), 2):\n        break\n"
   "print(f'{q / 10:.1f}')\nprint(f'{P / 10:.1f}')\nprint(f'{L / 10:.1f}')\nprint(b)\nprint(h)\n",
   r'''
beban_merata_kN_m = float(input())
beban_titik_kN = float(input())
bentang_m = float(input())
lebar_mm = int(input())
tinggi_mm = int(input())
momen_kNm = beban_merata_kN_m * bentang_m ** 2 / 8 + beban_titik_kN * bentang_m / 4
momen_Nmm = momen_kNm * 1000000
modulus_penampang_mm3 = lebar_mm * tinggi_mm ** 2 / 6
tegangan_MPa = momen_Nmm / modulus_penampang_mm3
print(f"M = {momen_kNm:.2f} kNm")
print(f"Tegangan = {tegangan_MPa:.2f} MPa")
''',
   ("Momen dan Tegangan Lentur Balok",
    "Balok di atas dua tumpuan memikul beban merata q di sepanjang bentang dan satu beban terpusat P di tengah bentang.\n\n"
    "- Momen maksimum: M = q × L² ÷ 8 + P × L ÷ 4 (kNm).\n"
    "- Modulus penampang persegi panjang: W = b × h² ÷ 6 (mm³).\n"
    "- Tegangan lentur: σ = M ÷ W. Supaya hasilnya MPa, M harus dalam Nmm: 1 kNm = 1 000 000 Nmm.\n\n"
    "Contoh: q = `10.0`, P = `20.0`, L = `6.0`, b = `300`, h = `500` menghasilkan\n\n```\nM = 75.00 kNm\nTegangan = 6.00 MPa\n```",
    "Lima baris: q (kN/m, pecahan), P (kN, pecahan), L (m, pecahan), b (mm, bilangan bulat), h (mm, bilangan bulat).",
    "Dua baris: `M = … kNm` dan `Tegangan = … MPa`, keduanya dengan dua desimal.",
    "q 2.0–30.0; P 0.0–80.0; L 2.0–10.0; b 150–500; h 250–900."),
   ("Bending Moment and Bending Stress of a Beam",
    "A simply supported beam carries a uniform load q along the span and one point load P at midspan.\n\n"
    "- Maximum moment: M = q × L² ÷ 8 + P × L ÷ 4 (kNm).\n"
    "- Section modulus of a rectangle: W = b × h² ÷ 6 (mm³).\n"
    "- Bending stress: σ = M ÷ W. For a result in MPa, M must be in Nmm: 1 kNm = 1 000 000 Nmm.\n\n"
    "Example: q = `10.0`, P = `20.0`, L = `6.0`, b = `300`, h = `500` give\n\n```\nM = 75.00 kNm\nTegangan = 6.00 MPa\n```",
    "Five lines: q (kN/m, decimal), P (kN, decimal), L (m, decimal), b (mm, whole number), h (mm, whole number).",
    "Two lines: `M = … kNm` and `Tegangan = … MPa`, both with two decimals (the word stays in Indonesian).",
    "q 2.0–30.0; P 0.0–80.0; L 2.0–10.0; b 150–500; h 250–900."),
   ("عزم الانحناء وإجهاد الانحناء في كمرة",
    "كمرة على ركيزتين تحمل حملًا موزعًا q على طول البحر وحملًا مركزًا P في منتصف البحر.\n\n"
    "- العزم الأقصى: M = q × L² ÷ 8 + P × L ÷ 4 (kNm).\n"
    "- معاير المقطع المستطيل: W = b × h² ÷ 6 (mm³).\n"
    "- إجهاد الانحناء: σ = M ÷ W. ليكون الناتج بالميغاباسكال يجب أن يكون M بوحدة Nmm: 1 kNm = 1 000 000 Nmm.\n\n"
    "مثال: q = `10.0` و P = `20.0` و L = `6.0` و b = `300` و h = `500` تعطي\n\n```\nM = 75.00 kNm\nTegangan = 6.00 MPa\n```",
    "خمسة أسطر: q (kN/m، عشري)، P (kN، عشري)، L (م، عشري)، b (مم، عدد صحيح)، h (مم، عدد صحيح).",
    "سطران: `M = … kNm` و `Tegangan = … MPa`، كلاهما بمنزلتين عشريتين (تبقى الكلمة بالإندونيسية).",
    "q من 2.0 إلى 30.0؛ P من 0.0 إلى 80.0؛ L من 2.0 إلى 10.0؛ b من 150 إلى 500؛ h من 250 إلى 900."))

py("timbunan-kerucut", "sedang",
   "import math\n" + AMAN + "while True:\n    rr = r.randint(10, 150)\n    tt = r.randint(5, 80)\n    k = r.randint(4, 30)\n"
   "    V = math.pi * (rr / 10) ** 2 * (tt / 10) / 3\n    if aman(V, 2) and aman(V * 1.6, 2) and jauh(V * 1.6 / k):\n        break\n"
   "print(f'{rr / 10:.1f}')\nprint(f'{tt / 10:.1f}')\nprint(k)\n",
   r'''
import math

jari_m = float(input())
tinggi_m = float(input())
daya_angkut_ton = int(input())
volume_m3 = math.pi * jari_m ** 2 * tinggi_m / 3
berat_ton = volume_m3 * 1.6
jumlah_truk = math.ceil(berat_ton / daya_angkut_ton)
print(f"{volume_m3:.2f} m3")
print(f"{berat_ton:.2f} ton")
print(jumlah_truk)
''',
   ("Timbunan Pasir Berbentuk Kerucut",
    "Pasir yang ditumpahkan dari satu titik membentuk kerucut. Volume kerucut = π × r² × t ÷ 3. Dengan berat isi pasir 1,6 ton/m³ "
    "(parameter latihan), hitung berat timbunan itu dan jumlah truk untuk mengangkut seluruhnya, bila daya angkut truk dibatasi "
    "berdasarkan berat. Jumlah truk dibulatkan ke atas (`math.ceil`).\n\n"
    "Contoh: jari-jari `6.0` m, tinggi `4.0` m, daya angkut `8` ton menghasilkan\n\n```\n150.80 m3\n241.27 ton\n31\n```",
    "Tiga baris: jari-jari alas (m, pecahan), tinggi (m, pecahan), daya angkut satu truk (ton, bilangan bulat).",
    "Tiga baris: `V m3` dan `W ton` dengan dua desimal, lalu jumlah truk (bilangan bulat).",
    "Jari-jari 1.0–15.0; tinggi 0.5–8.0; daya angkut 4–30."),
   ("A Conical Sand Stockpile",
    "Sand tipped from one point forms a cone. Volume of a cone = π × r² × t ÷ 3. With a unit weight of sand of 1.6 t/m³ "
    "(a practice parameter), compute the weight of the stockpile and the number of trucks needed to haul all of it when the truck load "
    "is limited by weight. The number of trucks is rounded up (`math.ceil`).\n\n"
    "Example: radius `6.0` m, height `4.0` m, load `8` t give\n\n```\n150.80 m3\n241.27 ton\n31\n```",
    "Three lines: base radius (m, decimal), height (m, decimal), load of one truck (tonnes, whole number).",
    "Three lines: `V m3` and `W ton` with two decimals, then the number of trucks (a whole number).",
    "Radius 1.0–15.0; height 0.5–8.0; load 4–30."),
   ("كومة رمل مخروطية",
    "الرمل المفرَّغ من نقطة واحدة يشكّل مخروطًا. حجم المخروط = π × r² × t ÷ 3. باعتبار وزن الرمل الحجمي 1.6 طن/م³ (معامل تدريبي) "
    "احسب وزن الكومة وعدد الشاحنات اللازمة لنقلها كلها إذا كانت حمولة الشاحنة محدودة بالوزن. يُقرَّب عدد الشاحنات إلى الأعلى (`math.ceil`).\n\n"
    "مثال: نصف القطر `6.0` م والارتفاع `4.0` م والحمولة `8` أطنان تعطي\n\n```\n150.80 m3\n241.27 ton\n31\n```",
    "ثلاثة أسطر: نصف قطر القاعدة (م، عشري)، الارتفاع (م، عشري)، حمولة الشاحنة الواحدة (طن، عدد صحيح).",
    "ثلاثة أسطر: `V m3` و `W ton` بمنزلتين عشريتين، ثم عدد الشاحنات (عدد صحيح).",
    "نصف القطر 1.0–15.0؛ الارتفاع 0.5–8.0؛ الحمولة 4–30."))

py("koordinat-polar", "sedang",
   "import math\n" + AMAN + "while True:\n    x = r.randint(0, 5000)\n    y = r.randint(0, 5000)\n    d = r.randint(50, 5000)\n    a = r.randint(0, 359)\n"
   "    xb = x + d / 10 * math.sin(math.radians(a))\n    yb = y + d / 10 * math.cos(math.radians(a))\n"
   "    if xb > 1 and yb > 1 and aman(xb, 3) and aman(yb, 3):\n        break\nprint(x)\nprint(y)\nprint(f'{d / 10:.1f}')\nprint(a)\n",
   r'''
import math

x_a = int(input())
y_a = int(input())
jarak_m = float(input())
azimut_derajat = int(input())
azimut_radian = math.radians(azimut_derajat)
x_b = x_a + jarak_m * math.sin(azimut_radian)
y_b = y_a + jarak_m * math.cos(azimut_radian)
print(f"{x_b:.3f}")
print(f"{y_b:.3f}")
''',
   ("Koordinat Titik dari Jarak dan Azimut",
    "Dari titik A yang koordinatnya diketahui, juru ukur mengukur jarak dan azimut ke titik B. Azimut diukur dari arah utara (sumbu Y) "
    "searah jarum jam, sehingga\n\n- X_B = X_A + d × sin(α)\n- Y_B = Y_A + d × cos(α)\n\n"
    "Fungsi `math.sin` dan `math.cos` memerlukan sudut dalam radian: ubah dulu dengan `math.radians`.\n\n"
    "Contoh: A (`1000`, `2000`), d = `150.0` m, α = `60`° menghasilkan\n\n```\n1129.904\n2075.000\n```",
    "Empat baris: X_A (m, bilangan bulat), Y_A (m, bilangan bulat), jarak d (m, pecahan), azimut α (derajat, bilangan bulat).",
    "Dua baris: X_B lalu Y_B, masing-masing tiga angka di belakang titik.",
    "Koordinat A 0–5000; jarak 5.0–500.0; azimut 0–359. Koordinat B selalu positif."),
   ("Coordinates from Distance and Azimuth",
    "From point A with known coordinates a surveyor measures the distance and the azimuth to point B. The azimuth is measured from north "
    "(the Y axis) clockwise, so\n\n- X_B = X_A + d × sin(α)\n- Y_B = Y_A + d × cos(α)\n\n"
    "`math.sin` and `math.cos` need the angle in radians: convert it first with `math.radians`.\n\n"
    "Example: A (`1000`, `2000`), d = `150.0` m, α = `60`° give\n\n```\n1129.904\n2075.000\n```",
    "Four lines: X_A (m, whole number), Y_A (m, whole number), distance d (m, decimal), azimuth α (degrees, whole number).",
    "Two lines: X_B then Y_B, each with three digits after the point.",
    "Coordinates of A 0–5000; distance 5.0–500.0; azimuth 0–359. The coordinates of B are always positive."),
   ("إحداثيات نقطة من المسافة والسمت",
    "من النقطة A المعلومة الإحداثيات يقيس المسّاح المسافة والسمت إلى النقطة B. يُقاس السمت من اتجاه الشمال (المحور Y) باتجاه عقارب الساعة، فيكون\n\n"
    "- X_B = X_A + d × sin(α)\n- Y_B = Y_A + d × cos(α)\n\n"
    "الدالتان `math.sin` و `math.cos` تحتاجان إلى الزاوية بالراديان: حوِّلها أولًا بالدالة `math.radians`.\n\n"
    "مثال: A (`1000`, `2000`) و d = `150.0` م و α = `60`° تعطي\n\n```\n1129.904\n2075.000\n```",
    "أربعة أسطر: X_A (م، عدد صحيح)، Y_A (م، عدد صحيح)، المسافة d (م، عشري)، السمت α (درجات، عدد صحيح).",
    "سطران: X_B ثم Y_B، كل منهما بثلاث منازل عشرية.",
    "إحداثيات A من 0 إلى 5000؛ المسافة 5.0–500.0؛ السمت 0–359. إحداثيات B موجبة دائمًا."))

py("jarak-henti", "sedang",
   AMAN + "while True:\n    V = r.randint(3, 12) * 10\n    t = r.randint(10, 30)\n    f = r.randint(28, 40)\n"
   "    a = 0.278 * V * (t / 10)\n    b = V ** 2 / (254 * (f / 100))\n    if aman(a, 1) and aman(b, 1) and aman(a + b, 1):\n        break\n"
   "print(V)\nprint(f'{t / 10:.1f}')\nprint(f'{f / 100:.2f}')\n",
   r'''
kecepatan_km_jam = int(input())
waktu_reaksi_detik = float(input())
koefisien_gesek = float(input())
jarak_reaksi_m = 0.278 * kecepatan_km_jam * waktu_reaksi_detik
jarak_rem_m = kecepatan_km_jam ** 2 / (254 * koefisien_gesek)
print(f"Reaksi: {jarak_reaksi_m:.1f} m")
print(f"Rem: {jarak_rem_m:.1f} m")
print(f"Henti: {jarak_reaksi_m + jarak_rem_m:.1f} m")
''',
   ("Jarak Pandang Henti",
    "Jarak pandang henti terdiri atas jarak yang ditempuh selama pengemudi bereaksi dan jarak pengereman. Dengan kecepatan V dalam km/jam:\n\n"
    "- jarak reaksi = 0,278 × V × t (0,278 mengubah km/jam ke m/detik);\n- jarak pengereman = V² ÷ (254 × f);\n"
    "- jarak henti = jumlah keduanya,\n\n"
    "dengan t waktu reaksi (detik) dan f koefisien gesek memanjang (rumus dan angka: parameter latihan).\n\n"
    "Contoh: V = `60`, t = `2.5`, f = `0.35` menghasilkan\n\n```\nReaksi: 41.7 m\nRem: 40.5 m\nHenti: 82.2 m\n```",
    "Tiga baris: V (km/jam, bilangan bulat), t (detik, pecahan), f (pecahan).",
    "Tiga baris: `Reaksi: … m`, `Rem: … m`, `Henti: … m`, masing-masing satu angka di belakang titik. "
    "Jarak henti dihitung dari nilai yang belum dibulatkan.",
    "V 30–120; t 1.0–3.0; f 0.28–0.40."),
   ("Stopping Sight Distance",
    "Stopping sight distance is the distance travelled while the driver reacts plus the braking distance. With the speed V in km/h:\n\n"
    "- reaction distance = 0.278 × V × t (0.278 turns km/h into m/s);\n- braking distance = V² ÷ (254 × f);\n"
    "- stopping distance = the sum of the two,\n\n"
    "where t is the reaction time (s) and f the longitudinal friction coefficient (formula and numbers: practice parameters).\n\n"
    "Example: V = `60`, t = `2.5`, f = `0.35` give\n\n```\nReaksi: 41.7 m\nRem: 40.5 m\nHenti: 82.2 m\n```",
    "Three lines: V (km/h, whole number), t (seconds, decimal), f (decimal).",
    "Three lines: `Reaksi: … m`, `Rem: … m`, `Henti: … m`, each with one digit after the point (the words stay in Indonesian). "
    "The stopping distance is computed from the unrounded values.",
    "V 30–120; t 1.0–3.0; f 0.28–0.40."),
   ("مسافة رؤية التوقف",
    "مسافة رؤية التوقف هي المسافة المقطوعة أثناء ردّ فعل السائق مضافًا إليها مسافة الكبح. مع السرعة V بالكيلومتر في الساعة:\n\n"
    "- مسافة ردّ الفعل = 0.278 × V × t (العدد 0.278 يحوّل كم/س إلى م/ث)؛\n- مسافة الكبح = V² ÷ (254 × f)؛\n"
    "- مسافة التوقف = مجموعهما،\n\n"
    "حيث t زمن ردّ الفعل (ثانية) و f معامل الاحتكاك الطولي (المعادلة والأرقام: معاملات تدريبية).\n\n"
    "مثال: V = `60` و t = `2.5` و f = `0.35` تعطي\n\n```\nReaksi: 41.7 m\nRem: 40.5 m\nHenti: 82.2 m\n```",
    "ثلاثة أسطر: V (كم/س، عدد صحيح)، t (ثانية، عشري)، f (عشري).",
    "ثلاثة أسطر: `Reaksi: … m` و `Rem: … m` و `Henti: … m`، كل منها بمنزلة عشرية واحدة (تبقى الكلمات بالإندونيسية). "
    "تُحسب مسافة التوقف من القيم غير المقرَّبة.",
    "V من 30 إلى 120؛ t من 1.0 إلى 3.0؛ f من 0.28 إلى 0.40."))

# ================================== SULIT (4) ==================================

py("tulangan-perlu", "sulit",
   "import math\n" + AMAN + "while True:\n    M = r.randint(200, 4000)\n    fy = r.choice([280, 400, 420])\n    d = r.randint(25, 70) * 10\n"
   "    D = r.choice([10, 13, 16, 19, 22, 25])\n    As = (M / 10) * 1e6 / (0.9 * fy * 0.9 * d)\n    a1 = math.pi * D * D / 4\n"
   "    n = math.ceil(As / a1)\n    if n <= 12 and aman(As, 1) and aman(n * a1, 1) and jauh(As / a1):\n        break\n"
   "print(f'{M / 10:.1f}')\nprint(fy)\nprint(d)\nprint(D)\n",
   r'''
import math

momen_kNm = float(input())
fy_MPa = int(input())
tinggi_efektif_mm = int(input())
diameter_mm = int(input())
momen_Nmm = momen_kNm * 1000000
luas_perlu_mm2 = momen_Nmm / (0.9 * fy_MPa * 0.9 * tinggi_efektif_mm)
luas_satu_mm2 = math.pi * diameter_mm ** 2 / 4
jumlah_batang = math.ceil(luas_perlu_mm2 / luas_satu_mm2)
luas_terpasang_mm2 = jumlah_batang * luas_satu_mm2
print(f"As perlu = {luas_perlu_mm2:.1f} mm2")
print(f"Jumlah = {jumlah_batang} D{diameter_mm}")
print(f"As terpasang = {luas_terpasang_mm2:.1f} mm2")
''',
   ("Taksiran Tulangan Lentur Balok",
    "Luas tulangan tarik sebuah balok dapat ditaksir dengan rumus pendekatan (parameter latihan, bukan prosedur desain):\n\n"
    "As = M ÷ (0,9 × fy × 0,9 × d)\n\n"
    "dengan M momen dalam Nmm, fy tegangan leleh baja dalam MPa, dan d tinggi efektif dalam mm; hasilnya mm². Momen diberikan dalam kNm "
    "(1 kNm = 1 000 000 Nmm).\n\nTulangan dipasang dari batang berdiameter D mm. Luas satu batang π × D² ÷ 4; jumlah batang dibulatkan "
    "ke atas (`math.ceil`). Hitung juga luas tulangan yang benar-benar terpasang.\n\n"
    "Contoh: M = `85.0`, fy = `400`, d = `440`, D = `16` menghasilkan\n\n```\nAs perlu = 596.2 mm2\nJumlah = 3 D16\nAs terpasang = 603.2 mm2\n```",
    "Empat baris: M (kNm, pecahan), fy (MPa, bilangan bulat), d (mm, bilangan bulat), D (mm, bilangan bulat).",
    "Tiga baris: `As perlu = … mm2` (satu desimal), `Jumlah = N D…` (N jumlah batang, lalu huruf D langsung diikuti diameternya), "
    "`As terpasang = … mm2` (satu desimal).",
    "M 20.0–400.0; fy 280, 400 atau 420; d 250–700; D 10–25. Jumlah batang tidak lebih dari 12."),
   ("Estimating Beam Flexural Reinforcement",
    "The area of tension steel in a beam can be estimated with an approximate formula (a practice parameter, not a design procedure):\n\n"
    "As = M ÷ (0.9 × fy × 0.9 × d)\n\n"
    "with M the moment in Nmm, fy the yield stress of steel in MPa and d the effective depth in mm; the result is in mm². The moment is given "
    "in kNm (1 kNm = 1 000 000 Nmm).\n\nThe steel is placed as bars of diameter D mm. Area of one bar π × D² ÷ 4; the number of bars is "
    "rounded up (`math.ceil`). Also compute the area actually provided.\n\n"
    "Example: M = `85.0`, fy = `400`, d = `440`, D = `16` give\n\n```\nAs perlu = 596.2 mm2\nJumlah = 3 D16\nAs terpasang = 603.2 mm2\n```",
    "Four lines: M (kNm, decimal), fy (MPa, whole number), d (mm, whole number), D (mm, whole number).",
    "Three lines: `As perlu = … mm2` (one decimal), `Jumlah = N D…` (N the number of bars, then the letter D directly followed by the diameter), "
    "`As terpasang = … mm2` (one decimal). The words stay in Indonesian.",
    "M 20.0–400.0; fy 280, 400 or 420; d 250–700; D 10–25. There are at most 12 bars."),
   ("تقدير تسليح الانحناء لكمرة",
    "يمكن تقدير مساحة حديد الشد في كمرة بمعادلة تقريبية (معامل تدريبي وليس إجراء تصميم):\n\n"
    "As = M ÷ (0.9 × fy × 0.9 × d)\n\n"
    "حيث M العزم بوحدة Nmm و fy إجهاد خضوع الحديد بوحدة MPa و d العمق الفعّال بالمليمتر؛ والناتج بالمليمتر المربع. العزم معطى بوحدة kNm "
    "(1 kNm = 1 000 000 Nmm).\n\nيُوضع الحديد قضبانًا قطرها D مم. مساحة القضيب الواحد π × D² ÷ 4؛ ويُقرَّب عدد القضبان إلى الأعلى (`math.ceil`). "
    "احسب أيضًا المساحة المركَّبة فعلًا.\n\n"
    "مثال: M = `85.0` و fy = `400` و d = `440` و D = `16` تعطي\n\n```\nAs perlu = 596.2 mm2\nJumlah = 3 D16\nAs terpasang = 603.2 mm2\n```",
    "أربعة أسطر: M (kNm، عشري)، fy (MPa، عدد صحيح)، d (مم، عدد صحيح)، D (مم، عدد صحيح).",
    "ثلاثة أسطر: `As perlu = … mm2` (منزلة عشرية واحدة)، `Jumlah = N D…` (N عدد القضبان ثم الحرف D متبوعًا مباشرة بالقطر)، "
    "`As terpasang = … mm2` (منزلة عشرية واحدة). تبقى الكلمات بالإندونيسية.",
    "M من 20.0 إلى 400.0؛ fy يساوي 280 أو 400 أو 420؛ d من 250 إلى 700؛ D من 10 إلى 25. عدد القضبان لا يتجاوز 12."))

py("debit-rasional", "sulit",
   AMAN + "while True:\n    R = r.randint(40, 250)\n    tc = r.randint(5, 60)\n    C = r.randint(20, 95)\n    A = r.randint(2, 80)\n"
   "    h = r.randint(5, 25)\n    v = r.randint(8, 30)\n    I = (R / 24) * (24 / (tc / 10)) ** (2 / 3)\n    Q = 0.278 * (C / 100) * I * (A / 10)\n"
   "    if aman(I, 2) and aman(Q, 2) and aman(Q / ((h / 10) * (v / 10)), 2):\n        break\n"
   "print(R)\nprint(f'{tc / 10:.1f}')\nprint(f'{C / 100:.2f}')\nprint(f'{A / 10:.1f}')\nprint(f'{h / 10:.1f}')\nprint(f'{v / 10:.1f}')\n",
   r'''
hujan_harian_mm = int(input())
waktu_konsentrasi_jam = float(input())
koefisien_limpasan = float(input())
luas_km2 = float(input())
kedalaman_m = float(input())
kecepatan_m_detik = float(input())
intensitas = (hujan_harian_mm / 24) * (24 / waktu_konsentrasi_jam) ** (2 / 3)
debit = 0.278 * koefisien_limpasan * intensitas * luas_km2
lebar_m = debit / (kedalaman_m * kecepatan_m_detik)
print(f"I = {intensitas:.2f} mm/jam")
print(f"Q = {debit:.2f} m3/detik")
print(f"b = {lebar_m:.2f} m")
''',
   ("Dari Hujan Harian ke Lebar Saluran",
    "Saluran drainase direncanakan dalam tiga langkah (semua rumus: parameter latihan).\n\n"
    "1. Intensitas hujan menurut Mononobe: I = (R₂₄ ÷ 24) × (24 ÷ tc)^(2/3), dengan R₂₄ hujan harian (mm) dan tc waktu konsentrasi (jam).\n"
    "2. Debit puncak metode rasional: Q = 0,278 × C × I × A, dengan C koefisien limpasan dan A luas daerah tangkapan dalam km²; Q dalam m³/detik.\n"
    "3. Lebar saluran persegi panjang yang mengalirkan Q pada kedalaman h dan kecepatan v: b = Q ÷ (h × v).\n\n"
    "Setiap langkah memakai hasil langkah sebelumnya yang BELUM dibulatkan.\n\n"
    "Contoh: R₂₄ = `100`, tc = `1.5`, C = `0.60`, A = `2.4`, h = `1.0`, v = `1.5` menghasilkan\n\n"
    "```\nI = 26.46 mm/jam\nQ = 10.59 m3/detik\nb = 7.06 m\n```",
    "Enam baris: R₂₄ (mm, bilangan bulat), tc (jam), C, A (km²), h (m), v (m/detik); lima yang terakhir bilangan pecahan.",
    "Tiga baris: `I = … mm/jam`, `Q = … m3/detik`, `b = … m`, masing-masing dua angka di belakang titik.",
    "R₂₄ 40–250; tc 0.5–6.0; C 0.20–0.95; A 0.2–8.0; h 0.5–2.5; v 0.8–3.0."),
   ("From Daily Rainfall to Channel Width",
    "A drainage channel is sized in three steps (all formulas: practice parameters).\n\n"
    "1. Rainfall intensity by Mononobe: I = (R₂₄ ÷ 24) × (24 ÷ tc)^(2/3), with R₂₄ the daily rainfall (mm) and tc the time of concentration (hours).\n"
    "2. Peak discharge by the rational method: Q = 0.278 × C × I × A, with C the runoff coefficient and A the catchment area in km²; Q in m³/s.\n"
    "3. Width of a rectangular channel carrying Q at depth h and velocity v: b = Q ÷ (h × v).\n\n"
    "Each step uses the UNROUNDED result of the step before.\n\n"
    "Example: R₂₄ = `100`, tc = `1.5`, C = `0.60`, A = `2.4`, h = `1.0`, v = `1.5` give\n\n"
    "```\nI = 26.46 mm/jam\nQ = 10.59 m3/detik\nb = 7.06 m\n```",
    "Six lines: R₂₄ (mm, whole number), tc (hours), C, A (km²), h (m), v (m/s); the last five are decimal numbers.",
    "Three lines: `I = … mm/jam`, `Q = … m3/detik`, `b = … m`, each with two digits after the point.",
    "R₂₄ 40–250; tc 0.5–6.0; C 0.20–0.95; A 0.2–8.0; h 0.5–2.5; v 0.8–3.0."),
   ("من الهطول اليومي إلى عرض القناة",
    "تُصمَّم قناة التصريف في ثلاث خطوات (كل المعادلات: معاملات تدريبية).\n\n"
    "1. شدة المطر بمعادلة مونونوبي: I = (R₂₄ ÷ 24) × (24 ÷ tc)^(2/3)، حيث R₂₄ الهطول اليومي (مم) و tc زمن التركيز (ساعة).\n"
    "2. التصريف الأقصى بالطريقة العقلانية: Q = 0.278 × C × I × A، حيث C معامل الجريان السطحي و A مساحة الحوض بالكيلومتر المربع؛ و Q بوحدة م³/ث.\n"
    "3. عرض قناة مستطيلة تمرّر Q عند العمق h والسرعة v: b = Q ÷ (h × v).\n\n"
    "كل خطوة تستعمل ناتج الخطوة السابقة غير المقرَّب.\n\n"
    "مثال: R₂₄ = `100` و tc = `1.5` و C = `0.60` و A = `2.4` و h = `1.0` و v = `1.5` تعطي\n\n"
    "```\nI = 26.46 mm/jam\nQ = 10.59 m3/detik\nb = 7.06 m\n```",
    "ستة أسطر: R₂₄ (مم، عدد صحيح)، tc (ساعة)، C، A (كم²)، h (م)، v (م/ث)؛ الخمسة الأخيرة أعداد عشرية.",
    "ثلاثة أسطر: `I = … mm/jam` و `Q = … m3/detik` و `b = … m`، كل منها بمنزلتين عشريتين.",
    "R₂₄ من 40 إلى 250؛ tc من 0.5 إلى 6.0؛ C من 0.20 إلى 0.95؛ A من 0.2 إلى 8.0؛ h من 0.5 إلى 2.5؛ v من 0.8 إلى 3.0."))

py("lahan-segitiga", "sulit",
   "import math\n" + AMAN + "while True:\n    a = r.randint(100, 2000)\n    b = r.randint(100, 2000)\n    c = r.randint(max(abs(a - b) + 20, 100), min(a + b - 20, 2000))\n"
   "    hg = r.randint(5, 400) * 10000\n    s = (a + b + c) / 20\n    L = math.sqrt(s * (s - a / 10) * (s - b / 10) * (s - c / 10))\n"
   "    if L > 100 and aman(L, 2) and aman(L * hg, 0):\n        break\n"
   "print(f'{a / 10:.1f}')\nprint(f'{b / 10:.1f}')\nprint(f'{c / 10:.1f}')\nprint(hg)\n",
   r'''
import math

sisi_a = float(input())
sisi_b = float(input())
sisi_c = float(input())
harga_per_m2 = int(input())
s = (sisi_a + sisi_b + sisi_c) / 2
luas_m2 = math.sqrt(s * (s - sisi_a) * (s - sisi_b) * (s - sisi_c))
nilai_rupiah = luas_m2 * harga_per_m2
print(f"Luas: {luas_m2:.2f} m2")
print(f"Nilai: Rp {nilai_rupiah:,.0f}")
''',
   ("Luas dan Nilai Lahan Segitiga",
    "Sebidang lahan berbentuk segitiga hanya diukur panjang ketiga sisinya. Luasnya dihitung dengan rumus Heron:\n\n"
    "s = (a + b + c) ÷ 2, lalu luas = akar dari s × (s − a) × (s − b) × (s − c).\n\n"
    "Hitung juga nilai lahan itu: luas (yang belum dibulatkan) × harga per m², dibulatkan ke rupiah terdekat dan dicetak dengan koma "
    "sebagai pemisah ribuan. Format f-string `:,.0f` melakukan keduanya.\n\n"
    "Contoh: sisi `30.0`, `40.0`, `50.0` m dan harga `1250000` per m² menghasilkan\n\n```\nLuas: 600.00 m2\nNilai: Rp 750,000,000\n```",
    "Empat baris: sisi a, b, c (m, bilangan pecahan), lalu harga per m² (rupiah, bilangan bulat).",
    "Dua baris: `Luas: … m2` dengan dua desimal, dan `Nilai: Rp …` tanpa desimal dengan koma pemisah ribuan.",
    "Sisi 10.0–200.0 dan selalu membentuk segitiga; harga 50000–4000000."),
   ("Area and Value of a Triangular Plot",
    "For a triangular plot only the lengths of the three sides were measured. Its area follows from Heron's formula:\n\n"
    "s = (a + b + c) ÷ 2, then area = square root of s × (s − a) × (s − b) × (s − c).\n\n"
    "Also compute the value of the plot: the (unrounded) area × the price per m², rounded to the nearest rupiah and printed with commas "
    "as thousands separators. The f-string format `:,.0f` does both.\n\n"
    "Example: sides `30.0`, `40.0`, `50.0` m and a price of `1250000` per m² give\n\n```\nLuas: 600.00 m2\nNilai: Rp 750,000,000\n```",
    "Four lines: sides a, b, c (m, decimal numbers), then the price per m² (rupiah, whole number).",
    "Two lines: `Luas: … m2` with two decimals, and `Nilai: Rp …` without decimals and with commas as thousands separators (the words stay in Indonesian).",
    "Sides 10.0–200.0, always forming a triangle; price 50000–4000000."),
   ("مساحة قطعة أرض مثلثة وقيمتها",
    "قطعة أرض مثلثة قيست أطوال أضلاعها الثلاثة فقط. تُحسب مساحتها بمعادلة هيرون:\n\n"
    "s = (a + b + c) ÷ 2، ثم المساحة = الجذر التربيعي لـ s × (s − a) × (s − b) × (s − c).\n\n"
    "احسب أيضًا قيمة القطعة: المساحة (غير المقرَّبة) × السعر لكل متر مربع، مقرَّبةً إلى أقرب روبية ومطبوعةً بفواصل بين الآلاف. "
    "تنسيق سلسلة f `:,.0f` يفعل الأمرين.\n\n"
    "مثال: الأضلاع `30.0` و`40.0` و`50.0` م والسعر `1250000` لكل م² تعطي\n\n```\nLuas: 600.00 m2\nNilai: Rp 750,000,000\n```",
    "أربعة أسطر: الأضلاع a و b و c (م، أعداد عشرية)، ثم السعر لكل متر مربع (روبية، عدد صحيح).",
    "سطران: `Luas: … m2` بمنزلتين عشريتين، و `Nilai: Rp …` بلا منازل عشرية ومع فواصل بين الآلاف (تبقى الكلمات بالإندونيسية).",
    "الأضلاع 10.0–200.0 وتشكّل مثلثًا دائمًا؛ السعر 50000–4000000."))

py("waktu-tempuh", "sulit",
   AMAN + "while True:\n    d = r.randint(5, 4000)\n    v = r.randint(15, 100)\n    if aman(d / 10 / v * 3600, 0) and aman(d / 10 / v, 3):\n        break\n"
   "print(f'{d / 10:.1f}')\nprint(v)\n",
   r'''
jarak_km = float(input())
kecepatan_km_jam = int(input())
waktu_jam = jarak_km / kecepatan_km_jam
total_detik = round(waktu_jam * 3600)
jam = total_detik // 3600
menit = total_detik % 3600 // 60
detik = total_detik % 60
print(f"{waktu_jam:.3f} jam")
print(f"{jam:02d}:{menit:02d}:{detik:02d}")
''',
   ("Waktu Tempuh Truk Molen",
    "Beton segar harus tiba di lokasi sebelum mulai mengeras, jadi waktu tempuh truk molen dihitung dengan teliti. "
    "Waktu tempuh (jam) = jarak ÷ kecepatan.\n\n"
    "Cetak waktu itu dua kali: sebagai jam desimal dengan tiga angka di belakang titik, dan sebagai `JJ:MM:DD`. Untuk bentuk kedua, ubah "
    "waktu ke detik dan bulatkan ke detik terdekat dengan `round()`, lalu uraikan dengan `//` dan `%`. Jam, menit, dan detik masing-masing "
    "ditulis dua angka dengan nol di depan (format `:02d`).\n\n"
    "Contoh: jarak `87.5` km dengan kecepatan `42` km/jam menghasilkan\n\n```\n2.083 jam\n02:05:00\n```",
    "Dua baris: jarak (km, bilangan pecahan), lalu kecepatan rata-rata (km/jam, bilangan bulat).",
    "Baris pertama `T jam` dengan tiga angka di belakang titik; baris kedua `JJ:MM:DD`.",
    "Jarak 0.5–400.0; kecepatan 15–100. Waktu tempuh kurang dari 100 jam."),
   ("Travel Time of a Mixer Truck",
    "Fresh concrete has to arrive before it starts to set, so the travel time of a mixer truck is computed carefully. "
    "Travel time (hours) = distance ÷ speed.\n\n"
    "Print the time twice: as decimal hours with three digits after the point, and as `HH:MM:SS`. For the second form turn the time into "
    "seconds and round to the nearest second with `round()`, then split it with `//` and `%`. Hours, minutes and seconds are each written "
    "with two digits and a leading zero (format `:02d`).\n\n"
    "Example: a distance of `87.5` km at `42` km/h gives\n\n```\n2.083 jam\n02:05:00\n```",
    "Two lines: the distance (km, decimal number), then the average speed (km/h, whole number).",
    "First line `T jam` with three digits after the point; second line `HH:MM:SS`.",
    "Distance 0.5–400.0; speed 15–100. The travel time is under 100 hours."),
   ("زمن رحلة شاحنة الخلاطة",
    "يجب أن تصل الخرسانة الطازجة قبل أن تبدأ بالتصلّب، لذلك يُحسب زمن رحلة شاحنة الخلاطة بدقة. زمن الرحلة (ساعة) = المسافة ÷ السرعة.\n\n"
    "اطبع الزمن مرتين: ساعاتٍ عشريةً بثلاث منازل عشرية، وبالشكل `HH:MM:SS`. للشكل الثاني حوِّل الزمن إلى ثوانٍ وقرِّبه إلى أقرب ثانية "
    "بالدالة `round()`، ثم فكِّكه بالعاملين `//` و`%`. تُكتب الساعات والدقائق والثواني كل منها برقمين مع صفر في البداية (التنسيق `:02d`).\n\n"
    "مثال: مسافة `87.5` كم بسرعة `42` كم/س تعطي\n\n```\n2.083 jam\n02:05:00\n```",
    "سطران: المسافة (كم، عدد عشري)، ثم السرعة المتوسطة (كم/س، عدد صحيح).",
    "السطر الأول `T jam` بثلاث منازل عشرية؛ السطر الثاني `HH:MM:SS`.",
    "المسافة 0.5–400.0؛ السرعة 15–100. زمن الرحلة أقل من 100 ساعة."))

tulis(__file__, "TS-PY", 2, "python", {"mudah_sekali": 8, "mudah": 8, "mudah_sedang": 6, "sedang": 6, "sulit": 4})
