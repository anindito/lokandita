#!/usr/bin/env python3
"""Menyusun docs/soal/ddp101-pertemuan-2.json (format KONTRAK.md §7).

DDP101 pertemuan 2 — Tipe Data, Variabel, Operator. Konsep yang diizinkan:
IO_DASAR, VARIABEL_TIPE, ARITMETIKA, KONVERSI_TIPE. Jadi solusi referensi TIDAK
memakai if, ?:, &&, ||, perulangan, string, atau pengaturan format keluaran.

Tiga soal pertemuan ini sudah ada di bank (docs/contoh-soal/ddp101-pertemuan-2.json):
dua MUDAH_SEKALI dan satu MUDAH. Berkas ini menambah 6 + 7 + 6 + 6 + 4 = 29 soal,
sehingga komposisi minimum bawaan 8 / 8 / 6 / 6 / 4 terpenuhi, dan memuat ketiga soal
lama itu juga. Teks Inggris dan Arab semua 32 soal ada di terjemahan_p2.py.

    python3 docs/soal/buat-ddp101-pertemuan-2.py
"""
import json
from pathlib import Path

AWAL = ('import os\nimport random\n\n'
        'r = random.Random(int(os.environ["SEED"]) * 1000 + int(os.environ["INDEX"]))\n'
        'i = int(os.environ["INDEX"])\n')


def cpp(isi: str, kepala: str = "#include <iostream>") -> str:
    return kepala + "\nusing namespace std;\n\nint main() {\n" + isi.rstrip("\n") + "\n    return 0;\n}\n"


SOAL = []


def soal(slug, tingkat, judul, pernyataan, masukan, keluaran, batasan, gen, ref, mode="trimmed"):
    SOAL.append({
        "slug": slug, "difficulty": tingkat, "comparison_mode": mode, "time_limit_ms": 2000,
        "samples_count": 2, "hidden_count": 10, "required_concepts_in_submission": [],
        "translations": {"id": {"title": judul, "statement": pernyataan, "input_format": masukan,
                                "output_format": keluaran, "constraints": batasan}, "en": {}, "ar": {}},
        "generator": AWAL + gen, "reference_solution": ref,
    })


# ============================== MUDAH SEKALI (6) ==============================

soal("jumlah-dan-selisih", "mudah_sekali", "Jumlah dan Selisih",
     "Diberikan dua bilangan bulat **a** dan **b**. Cetak jumlahnya, lalu selisihnya (**a − b**).\n\nSelisihnya boleh negatif.",
     "Satu baris berisi a dan b.", "Baris pertama: a + b. Baris kedua: a − b.", "1 ≤ a, b ≤ 1000",
     "print(r.randint(1, 1000), r.randint(1, 1000))\n",
     cpp("    int a, b;\n    cin >> a >> b;\n    cout << a + b << endl;\n    cout << a - b << endl;"), "token")

soal("kuadrat-dan-kubik", "mudah_sekali", "Kuadrat dan Kubik",
     "Diberikan sebuah bilangan bulat **n**. Cetak **n²**, lalu **n³**.",
     "Satu bilangan bulat n.", "Baris pertama: n × n. Baris kedua: n × n × n.", "1 ≤ n ≤ 1000",
     "print(1000 if i == 5 else r.randint(1, 1000))\n",
     cpp("    int n;\n    cin >> n;\n    cout << n * n << endl;\n    cout << n * n * n << endl;"), "token")

soal("hasil-bagi-dan-sisa", "mudah_sekali", "Hasil Bagi dan Sisa",
     "Sebanyak **a** permen dibagi rata kepada **b** anak. Setiap anak mendapat jumlah yang sama dan sebanyak mungkin.\n\n"
     "Cetak berapa permen yang didapat tiap anak, lalu berapa permen yang tersisa.\n\n"
     "Di C++, `/` pada dua bilangan bulat memberi hasil bagi yang dibulatkan ke bawah, dan `%` memberi sisanya.",
     "Satu baris berisi a dan b.", "Baris pertama: permen tiap anak. Baris kedua: sisa permen.", "1 ≤ a ≤ 10000, 1 ≤ b ≤ 100",
     "print(r.randint(1, 10000), r.randint(1, 100))\n",
     cpp("    int a, b;\n    cin >> a >> b;\n    cout << a / b << endl;\n    cout << a % b << endl;"), "token")

soal("detik-ke-menit", "mudah_sekali", "Detik ke Menit",
     "Sebuah video berdurasi **d** detik. Tuliskan durasi itu dalam menit dan detik.\n\nContoh: 135 detik adalah 2 menit 15 detik.",
     "Satu bilangan bulat d.", "Satu baris: banyak menit, satu spasi, lalu sisa detik.", "0 ≤ d ≤ 100000",
     "print(59 if i == 4 else r.randint(0, 100000))\n",
     cpp("    int d;\n    cin >> d;\n    cout << d / 60 << \" \" << d % 60 << endl;"), "token")

soal("total-harga", "mudah_sekali", "Total Harga",
     "Sebuah barang berharga **h** rupiah. Seorang pembeli mengambil **n** barang yang sama. Cetak jumlah yang harus dibayar.",
     "Satu baris berisi h dan n.", "Satu bilangan: total harga.", "500 ≤ h ≤ 50000, 1 ≤ n ≤ 100",
     "print(r.randint(1, 100) * 500, r.randint(1, 100))\n",
     cpp("    int harga, banyak;\n    cin >> harga >> banyak;\n    cout << harga * banyak << endl;"))

soal("kode-huruf", "mudah_sekali", "Kode Sebuah Huruf",
     "Di dalam komputer setiap huruf disimpan sebagai sebuah bilangan (kode ASCII). Huruf `A` disimpan sebagai 65, `B` sebagai 66, dan `a` sebagai 97.\n\n"
     "Baca satu huruf, lalu cetak bilangan yang menyimpannya.\n\n"
     "Petunjuk: simpan hurufnya di variabel bertipe `char`, lalu ubah ke `int` sebelum dicetak.",
     "Satu huruf, besar atau kecil.", "Satu bilangan: kode huruf itu.", "Huruf A–Z atau a–z.",
     "print(r.choice('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'))\n",
     cpp("    char huruf;\n    cin >> huruf;\n    cout << (int) huruf << endl;"))

# ================================= MUDAH (7) =================================

soal("celsius-ke-fahrenheit", "mudah", "Celsius ke Fahrenheit",
     "Suhu **c** derajat Celsius sama dengan **c × 9 / 5 + 32** derajat Fahrenheit.\n\n"
     "Baca c (bilangan bulat) dan cetak suhunya dalam Fahrenheit sebagai bilangan pecahan.\n\n"
     "Hati-hati: 37 derajat Celsius adalah 98.6 derajat Fahrenheit, bukan 98.",
     "Satu bilangan bulat c.", "Satu bilangan: suhu dalam Fahrenheit.", "−50 ≤ c ≤ 150",
     "print(37 if i == 3 else r.randint(-50, 150))\n",
     cpp("    int c;\n    cin >> c;\n    double f = c * 9.0 / 5 + 32;\n    cout << f << endl;"), "float")

soal("potong-dan-bulatkan", "mudah", "Potong dan Bulatkan",
     "Diberikan sebuah bilangan pecahan positif **x**. Cetak dua bilangan bulat:\n\n"
     "1. x yang **dipotong**: bagian pecahannya dibuang (7.98 menjadi 7);\n"
     "2. x yang **dibulatkan** ke bilangan bulat terdekat (7.98 menjadi 8, dan 7.50 menjadi 8).\n\n"
     "Petunjuk: mengubah `double` menjadi `int` membuang bagian pecahannya. Apa yang terjadi bila sebelum itu x ditambah 0.5?",
     "Satu bilangan pecahan x dengan dua angka di belakang koma.", "Baris pertama: x dipotong. Baris kedua: x dibulatkan.", "0 ≤ x < 1000",
     "k = {2: 750, 3: 749, 4: 99950}.get(i, r.randint(0, 99999))\nprint(f\"{k / 100:.2f}\")\n",
     cpp("    double x;\n    cin >> x;\n    cout << (int) x << endl;\n    cout << (int) (x + 0.5) << endl;"), "token")

soal("huruf-berikutnya", "mudah", "Huruf Berikutnya",
     "Baca satu huruf kecil, lalu cetak huruf yang tepat sesudahnya di dalam alfabet. Sesudah `a` adalah `b`, sesudah `k` adalah `l`.\n\n"
     "Petunjuk: sebuah `char` bisa dijumlahkan dengan bilangan. Hasilnya bilangan, jadi ubah kembali menjadi `char` sebelum dicetak.",
     "Satu huruf kecil.", "Satu huruf.", "Huruf a sampai y (bukan z).",
     "print(r.choice('abcdefghijklmnopqrstuvwxy'))\n",
     cpp("    char huruf;\n    cin >> huruf;\n    cout << (char) (huruf + 1) << endl;"))

soal("digit-terakhir", "mudah", "Digit Terakhir",
     "Diberikan sebuah bilangan bulat **n**. Cetak digit terakhirnya, lalu bilangan itu tanpa digit terakhirnya.\n\nContoh: untuk 5827, digit terakhirnya 7 dan sisanya 582.",
     "Satu bilangan bulat n.", "Baris pertama: digit terakhir n. Baris kedua: n tanpa digit terakhirnya.", "10 ≤ n ≤ 999999",
     "print(r.randint(10, 999999))\n",
     cpp("    int n;\n    cin >> n;\n    cout << n % 10 << endl;\n    cout << n / 10 << endl;"), "token")

soal("jam-menit-detik", "mudah", "Jam, Menit, Detik",
     "Sebuah stopwatch menunjukkan **d** detik. Tuliskan sebagai jam, menit, dan detik.\n\nContoh: 3725 detik adalah 1 jam 2 menit 5 detik.",
     "Satu bilangan bulat d.", "Satu baris berisi tiga bilangan dipisah spasi: jam, menit, detik.", "0 ≤ d < 86400",
     "print({2: 0, 3: 86399, 4: 3600}.get(i, r.randint(0, 86399)))\n",
     cpp("    int d;\n    cin >> d;\n    int jam = d / 3600;\n    int menit = d % 3600 / 60;\n    int detik = d % 60;\n    cout << jam << \" \" << menit << \" \" << detik << endl;"), "token")

soal("rata-rata-dua-bilangan", "mudah", "Rata-Rata Dua Bilangan",
     "Diberikan dua bilangan bulat. Cetak rata-ratanya sebagai bilangan pecahan.\n\nRata-rata 3 dan 4 adalah 3.5, bukan 3.",
     "Satu baris berisi dua bilangan bulat.", "Satu bilangan: rata-rata keduanya.", "0 ≤ kedua bilangan ≤ 1000",
     "print(r.randint(0, 1000), r.randint(0, 1000))\n",
     cpp("    int a, b;\n    cin >> a >> b;\n    double rata = (a + b) / 2.0;\n    cout << rata << endl;"), "float")

soal("luas-segitiga", "mudah", "Luas Segitiga",
     "Sebuah segitiga punya alas **a** dan tinggi **t**, keduanya bilangan bulat. Luasnya **a × t / 2**.\n\nCetak luasnya. Luas segitiga dengan alas 3 dan tinggi 5 adalah 7.5.",
     "Satu baris berisi a dan t.", "Satu bilangan: luas segitiga.", "1 ≤ a, t ≤ 300",
     "print(r.randint(1, 300), r.randint(1, 300))\n",
     cpp("    int alas, tinggi;\n    cin >> alas >> tinggi;\n    double luas = alas * tinggi / 2.0;\n    cout << luas << endl;"), "float")

# ============================== MUDAH-SEDANG (6) ==============================

soal("jumlah-tiga-digit", "mudah_sedang", "Jumlah Tiga Digit",
     "Diberikan sebuah bilangan tiga digit. Cetak jumlah ketiga digitnya.\n\nContoh: untuk 472 jawabannya 4 + 7 + 2 = 13.\n\n"
     "Petunjuk: `/` dan `%` dengan 10 dan 100 bisa memisahkan digit-digit sebuah bilangan.",
     "Satu bilangan bulat tiga digit.", "Satu bilangan: jumlah digitnya.", "100 ≤ n ≤ 999",
     "print({2: 100, 3: 999}.get(i, r.randint(100, 999)))\n",
     cpp("    int n;\n    cin >> n;\n    int ratusan = n / 100;\n    int puluhan = n / 10 % 10;\n    int satuan = n % 10;\n    cout << ratusan + puluhan + satuan << endl;"))

soal("balik-tiga-digit", "mudah_sedang", "Balik Tiga Digit",
     "Diberikan sebuah bilangan tiga digit. Cetak bilangan yang digit-digitnya dibalik urutannya.\n\n"
     "Contoh: 472 menjadi 274. Hasilnya tetap sebuah bilangan, jadi 120 menjadi 21 (bukan 021).",
     "Satu bilangan bulat tiga digit.", "Satu bilangan: bilangan itu dengan digit terbalik.", "100 ≤ n ≤ 999",
     "print({2: 120, 3: 500, 4: 707}.get(i, r.randint(100, 999)))\n",
     cpp("    int n;\n    cin >> n;\n    int ratusan = n / 100;\n    int puluhan = n / 10 % 10;\n    int satuan = n % 10;\n    cout << satuan * 100 + puluhan * 10 + ratusan << endl;"))

soal("pecahan-uang", "mudah_sedang", "Pecahan Uang",
     "Sebuah mesin ATM mengeluarkan uang sebanyak **n** rupiah dengan lembaran **sesedikit mungkin**. Pecahan yang tersedia: "
     "100000, 50000, 20000, 10000, 5000, 2000, dan 1000.\n\n"
     "Cetak berapa lembar yang keluar untuk tiap pecahan, mulai dari pecahan terbesar.\n\n"
     "Contoh: 188000 = 1 × 100000 + 1 × 50000 + 1 × 20000 + 1 × 10000 + 1 × 5000 + 1 × 2000 + 1 × 1000.",
     "Satu bilangan bulat n, kelipatan 1000.", "Tujuh baris: banyak lembar 100000, 50000, 20000, 10000, 5000, 2000, 1000.", "1000 ≤ n ≤ 5000000",
     "print({2: 188000, 3: 1000, 4: 4999000}.get(i, r.randint(1, 5000) * 1000))\n",
     cpp("    int n;\n    cin >> n;\n    cout << n / 100000 << endl;\n    n = n % 100000;\n    cout << n / 50000 << endl;\n    n = n % 50000;\n"
         "    cout << n / 20000 << endl;\n    n = n % 20000;\n    cout << n / 10000 << endl;\n    n = n % 10000;\n"
         "    cout << n / 5000 << endl;\n    n = n % 5000;\n    cout << n / 2000 << endl;\n    n = n % 2000;\n    cout << n / 1000 << endl;"), "token")

soal("huruf-kecil-ke-besar", "mudah_sedang", "Huruf Kecil ke Huruf Besar",
     "Baca satu huruf kecil dan cetak huruf besarnya: `a` menjadi `A`, `m` menjadi `M`.\n\n"
     "Petunjuk: kode huruf kecil berurutan dari `'a'` sampai `'z'`, dan kode huruf besar berurutan dari `'A'` sampai `'Z'`. "
     "Jarak sebuah huruf kecil dari `'a'` sama dengan jarak huruf besarnya dari `'A'`.",
     "Satu huruf kecil.", "Satu huruf besar.", "Huruf a sampai z.",
     "print({2: 'a', 3: 'z'}.get(i, r.choice('abcdefghijklmnopqrstuvwxyz')))\n",
     cpp("    char kecil;\n    cin >> kecil;\n    char besar = (char) (kecil - 'a' + 'A');\n    cout << besar << endl;"))

soal("lama-perjalanan", "mudah_sedang", "Lama Perjalanan",
     "Sebuah kereta berangkat pukul **j1:m1** dan tiba pukul **j2:m2** pada hari yang sama. Cetak lama perjalanannya dalam jam dan menit.\n\n"
     "Contoh: berangkat 07:45 dan tiba 10:20 berarti perjalanan 2 jam 35 menit.\n\n"
     "Petunjuk: ubah kedua waktu menjadi menit sejak tengah malam.",
     "Satu baris berisi empat bilangan bulat: j1 m1 j2 m2.", "Satu baris: banyak jam, satu spasi, lalu banyak menit.",
     "0 ≤ j1, j2 ≤ 23; 0 ≤ m1, m2 ≤ 59; waktu tiba tidak lebih awal dari waktu berangkat.",
     "a = r.randint(0, 1439)\nb = r.randint(a, 1439)\nif i == 2:\n    a, b = 7 * 60 + 45, 10 * 60 + 20\nif i == 3:\n    b = a\nprint(a // 60, a % 60, b // 60, b % 60)\n",
     cpp("    int j1, m1, j2, m2;\n    cin >> j1 >> m1 >> j2 >> m2;\n    int lama = (j2 * 60 + m2) - (j1 * 60 + m1);\n    cout << lama / 60 << \" \" << lama % 60 << endl;"), "token")

soal("persentase-benar", "mudah_sedang", "Persentase Jawaban Benar",
     "Dalam sebuah kuis, seorang mahasiswa menjawab benar **b** dari **t** soal. Cetak persentase jawaban benarnya sebagai bilangan pecahan.\n\n"
     "Contoh: 3 benar dari 8 soal adalah 37.5 persen.\n\nHati-hati dengan pembagian dua bilangan bulat.",
     "Satu baris berisi b dan t.", "Satu bilangan: persentase jawaban benar.", "0 ≤ b ≤ t; t salah satu dari 4, 5, 8, 10, 20, 25, 40, 50.",
     "t = r.choice([4, 5, 8, 10, 20, 25, 40, 50])\nb = {2: 0, 3: t}.get(i, r.randint(0, t))\nprint(b, t)\n",
     cpp("    int benar, total;\n    cin >> benar >> total;\n    double persen = 100.0 * benar / total;\n    cout << persen << endl;"), "float")

# ================================= SEDANG (6) =================================

soal("hari-jam-menit-detik", "sedang", "Hari, Jam, Menit, Detik",
     "Sebuah server sudah menyala selama **d** detik. Tuliskan lamanya dalam hari, jam, menit, dan detik.\n\n"
     "Perhatikan batas d: nilainya bisa jauh lebih besar daripada yang muat di sebuah `int`.",
     "Satu bilangan bulat d.", "Satu baris berisi empat bilangan dipisah spasi: hari, jam, menit, detik.", "0 ≤ d ≤ 10¹²",
     "print({2: 0, 3: 10**12, 4: 86399, 5: 2147483648}.get(i, r.randint(0, 10**12)))\n",
     cpp("    long long d;\n    cin >> d;\n    long long hari = d / 86400;\n    long long jam = d % 86400 / 3600;\n    long long menit = d % 3600 / 60;\n    long long detik = d % 60;\n"
         "    cout << hari << \" \" << jam << \" \" << menit << \" \" << detik << endl;"), "token")

soal("hasil-kali-besar", "sedang", "Hasil Kali yang Besar",
     "Diberikan dua bilangan bulat **a** dan **b**. Cetak **a × b**.\n\n"
     "Kedua bilangan muat di `int`, tetapi hasil kalinya belum tentu. Hasil kali dua `int` dihitung sebagai `int`, "
     "walaupun kemudian disimpan di variabel yang lebih besar — jadi salah satunya harus diubah tipenya lebih dulu.",
     "Satu baris berisi a dan b.", "Satu bilangan: a × b.", "1 ≤ a, b ≤ 10⁹",
     "a, b = r.randint(1, 10**9), r.randint(1, 10**9)\nif i == 2:\n    a, b = 10**9, 10**9\nif i == 3:\n    a, b = 46341, 46341\nprint(a, b)\n",
     cpp("    int a, b;\n    cin >> a >> b;\n    long long hasil = (long long) a * b;\n    cout << hasil << endl;"))

soal("sandi-geser", "sedang", "Sandi Geser",
     "Sebuah sandi sederhana menggeser huruf besar sejauh **k** langkah di dalam alfabet. Sesudah `Z` kembali ke `A`.\n\n"
     "Contoh: `A` digeser 3 menjadi `D`; `Y` digeser 3 menjadi `B`; `C` digeser 26 tetap `C`.\n\n"
     "Baca satu huruf besar dan k, lalu cetak huruf hasil geserannya.\n\n"
     "Petunjuk: hitung dulu urutan hurufnya (A = 0, B = 1, …, Z = 25). Operator `%` membuat hitungan kembali ke 0 sesudah 25.",
     "Satu baris berisi satu huruf besar dan bilangan bulat k, dipisah satu spasi.", "Satu huruf besar.", "Huruf A–Z; 0 ≤ k ≤ 1000",
     "h = r.choice('ABCDEFGHIJKLMNOPQRSTUVWXYZ')\nk = r.randint(0, 1000)\nif i == 2:\n    h, k = 'Y', 3\nif i == 3:\n    h, k = 'C', 26\nif i == 4:\n    h, k = 'Z', 1\nprint(h, k)\n",
     cpp("    char huruf;\n    int k;\n    cin >> huruf >> k;\n    int urutan = (huruf - 'A' + k) % 26;\n    cout << (char) ('A' + urutan) << endl;"))

soal("jam-selesai", "sedang", "Jam Selesai",
     "Sebuah proses mulai pukul **j:m** dan berjalan selama **d** menit. Proses itu bisa berjalan melewati tengah malam, bahkan berhari-hari.\n\n"
     "Cetak berapa kali tengah malam terlewati, lalu jam dan menit saat proses selesai.\n\n"
     "Contoh: mulai 22:30 dan berjalan 200 menit, selesai pukul 01:50 keesokan harinya: `1 1 50`.",
     "Satu baris berisi tiga bilangan bulat: j m d.", "Satu baris berisi tiga bilangan dipisah spasi: banyak tengah malam yang terlewati, jam selesai, menit selesai.",
     "0 ≤ j ≤ 23; 0 ≤ m ≤ 59; 0 ≤ d ≤ 100000",
     "j, m, d = r.randint(0, 23), r.randint(0, 59), r.randint(0, 100000)\nif i == 2:\n    j, m, d = 22, 30, 200\nif i == 3:\n    j, m, d = 23, 59, 1\nif i == 4:\n    d = 0\nprint(j, m, d)\n",
     cpp("    int j, m, d;\n    cin >> j >> m >> d;\n    int total = j * 60 + m + d;\n    cout << total / 1440 << \" \" << total % 1440 / 60 << \" \" << total % 60 << endl;"), "token")

soal("nilai-akhir", "sedang", "Nilai Akhir",
     "Nilai akhir sebuah mata kuliah dihitung dari tiga nilai bulat: tugas (bobot 20%), UTS (bobot 30%), dan UAS (bobot 50%).\n\n"
     "Cetak nilai akhirnya sebagai bilangan pecahan.\n\nContoh: tugas 80, UTS 75, UAS 91 memberi 16 + 22.5 + 45.5 = 84.",
     "Satu baris berisi tiga bilangan bulat: tugas, UTS, UAS.", "Satu bilangan: nilai akhir.", "0 ≤ setiap nilai ≤ 100",
     "t, u, a = r.randint(0, 100), r.randint(0, 100), r.randint(0, 100)\nif i == 2:\n    t, u, a = 80, 75, 91\nif i == 3:\n    t, u, a = 100, 100, 100\nif i == 4:\n    t, u, a = 1, 0, 0\nprint(t, u, a)\n",
     cpp("    int tugas, uts, uas;\n    cin >> tugas >> uts >> uas;\n    double akhir = (2 * tugas + 3 * uts + 5 * uas) / 10.0;\n    cout << akhir << endl;"), "float")

soal("besar-dan-kecil", "sedang", "Yang Besar dan Yang Kecil",
     "Diberikan dua bilangan bulat **a** dan **b**. Cetak yang lebih besar, lalu yang lebih kecil.\n\n"
     "Percabangan belum dipelajari, tetapi soal ini bisa diselesaikan dengan aritmetika saja. Perhatikan: "
     "jumlah kedua bilangan ditambah jaraknya (**|a − b|**) sama dengan dua kali bilangan yang lebih besar.\n\n"
     "Fungsi `abs` dari `<cstdlib>` memberi nilai mutlak sebuah bilangan bulat.",
     "Satu baris berisi a dan b.", "Baris pertama: bilangan yang lebih besar. Baris kedua: yang lebih kecil.", "−1000000 ≤ a, b ≤ 1000000",
     "a, b = r.randint(-10**6, 10**6), r.randint(-10**6, 10**6)\nif i == 2:\n    b = a\nif i == 3:\n    a, b = -5, -9\nprint(a, b)\n",
     cpp("    int a, b;\n    cin >> a >> b;\n    int jarak = abs(a - b);\n    cout << (a + b + jarak) / 2 << endl;\n    cout << (a + b - jarak) / 2 << endl;", "#include <iostream>\n#include <cstdlib>"), "token")

# ================================== SULIT (4) ==================================

soal("tukar-digit-ujung", "sulit", "Tukar Digit Ujung",
     "Diberikan sebuah bilangan lima digit. Tukar digit pertamanya dengan digit terakhirnya, lalu cetak bilangan yang terbentuk.\n\n"
     "Contoh: 52819 menjadi 92815. Tiga digit di tengah tidak berubah.",
     "Satu bilangan bulat lima digit yang digit terakhirnya bukan 0.", "Satu bilangan lima digit.", "10000 ≤ n ≤ 99999; n tidak habis dibagi 10.",
     "n = r.randint(1000, 9999) * 10 + r.randint(1, 9)\nif i == 2:\n    n = 52819\nif i == 3:\n    n = 10001\nif i == 4:\n    n = 90009\nprint(n)\n",
     cpp("    int n;\n    cin >> n;\n    int pertama = n / 10000;\n    int tengah = n / 10 % 1000;\n    int terakhir = n % 10;\n    cout << terakhir * 10000 + tengah * 10 + pertama << endl;"))

soal("jam-mundur", "sulit", "Jam Mundur",
     "Sekarang pukul **j:m**. Pukul berapakah **k** menit yang lalu? Nilai k bisa sangat besar: berhari-hari, bahkan bertahun-tahun yang lalu.\n\n"
     "Cetak jam dan menitnya.\n\nContoh: 100 menit sebelum pukul 01:20 adalah pukul 23:40 hari sebelumnya.\n\n"
     "Hati-hati: di C++, `%` pada bilangan negatif memberi hasil negatif atau nol, bukan sisa yang positif.",
     "Satu baris berisi tiga bilangan bulat: j m k.", "Satu baris: jam, satu spasi, lalu menit.", "0 ≤ j ≤ 23; 0 ≤ m ≤ 59; 0 ≤ k ≤ 10⁹",
     "j, m, k = r.randint(0, 23), r.randint(0, 59), r.randint(0, 10**9)\nif i == 2:\n    j, m, k = 1, 20, 100\nif i == 3:\n    k = 0\nif i == 4:\n    j, m, k = 0, 0, 1\nif i == 5:\n    k = 1440 * r.randint(1, 1000)\nif i == 6:\n    k = r.randint(0, 3000)\nprint(j, m, k)\n",
     cpp("    int j, m, k;\n    cin >> j >> m >> k;\n    int sekarang = j * 60 + m;\n    int dulu = (sekarang - k % 1440 + 1440) % 1440;\n    cout << dulu / 60 << \" \" << dulu % 60 << endl;"), "token")

soal("nilai-tengah", "sulit", "Nilai Tengah",
     "Diberikan tiga bilangan bulat. Cetak **nilai tengahnya**: bilangan yang, bila ketiganya diurutkan, berada di tengah.\n\n"
     "Contoh: nilai tengah dari 7, 2, 5 adalah 5. Nilai tengah dari 4, 9, 4 adalah 4.\n\n"
     "Percabangan belum dipelajari. Pikirkan: jumlah ketiga bilangan dikurangi yang terbesar dan yang terkecil adalah nilai tengahnya. "
     "Yang lebih besar dari dua bilangan bisa dihitung dari jumlah dan jaraknya (lihat soal *Yang Besar dan Yang Kecil*).",
     "Satu baris berisi tiga bilangan bulat.", "Satu bilangan: nilai tengah.", "−1000000 ≤ setiap bilangan ≤ 1000000",
     "a, b, c = (r.randint(-10**6, 10**6) for _ in range(3))\nif i == 2:\n    a, b, c = 7, 2, 5\nif i == 3:\n    a, b, c = 4, 9, 4\nif i == 4:\n    b, c = a, a\nif i == 5:\n    a, b, c = -3, -1, -2\nprint(a, b, c)\n",
     cpp("    int a, b, c;\n    cin >> a >> b >> c;\n    int besarAB = (a + b + abs(a - b)) / 2;\n    int kecilAB = (a + b - abs(a - b)) / 2;\n"
         "    int terbesar = (besarAB + c + abs(besarAB - c)) / 2;\n    int terkecil = (kecilAB + c - abs(kecilAB - c)) / 2;\n"
         "    cout << a + b + c - terbesar - terkecil << endl;", "#include <iostream>\n#include <cstdlib>"))

soal("kode-tiga-huruf", "sulit", "Kode Tiga Huruf",
     "Sebuah gudang memberi setiap rak kode tiga huruf besar, berurutan seperti angka pada odometer: "
     "rak ke-0 berkode `AAA`, rak ke-1 `AAB`, …, rak ke-25 `AAZ`, rak ke-26 `ABA`, dan seterusnya sampai `ZZZ`.\n\n"
     "Diberikan nomor rak **n**, cetak kodenya.\n\nContoh: rak ke-28 berkode `ABC`, dan rak ke-17575 berkode `ZZZ`.\n\n"
     "Petunjuk: ini sama dengan memisahkan digit sebuah bilangan, tetapi setiap \"digit\" punya 26 kemungkinan, bukan 10.",
     "Satu bilangan bulat n.", "Tiga huruf besar tanpa spasi.", "0 ≤ n ≤ 17575",
     "print({2: 28, 3: 0, 4: 17575, 5: 26, 6: 676}.get(i, r.randint(0, 17575)))\n",
     cpp("    int n;\n    cin >> n;\n    char pertama = (char) ('A' + n / 676);\n    char kedua = (char) ('A' + n / 26 % 26);\n    char ketiga = (char) ('A' + n % 26);\n    cout << pertama << kedua << ketiga << endl;"))

# Tiga soal yang sudah ada di bank sejak gerbang tahap 8 ikut di berkas ini, supaya
# terjemahannya bisa ditambahkan (isi yang sama, versi baru karena teksnya bertambah).
for lama in json.loads(Path(__file__).parents[1].joinpath("contoh-soal", "ddp101-pertemuan-2.json").read_text(encoding="utf-8"))["problems"]:
    SOAL.append(lama)

from terjemahan_p2 import TERJEMAHAN  # noqa: E402

KUNCI = ("title", "statement", "input_format", "output_format", "constraints")
for s in SOAL:
    for bahasa in ("en", "ar"):
        # Teks Inggris dan Arab ditulis agen penyusun: ditandai terjemahan mesin sampai diperiksa pengampu.
        s["translations"][bahasa] = dict(zip(KUNCI, TERJEMAHAN[s["slug"]][bahasa]), machine=True)
assert set(TERJEMAHAN) == {s["slug"] for s in SOAL}

berkas = {"course": "DDP101", "meeting": 2, "language": "cpp", "problems": SOAL}
tujuan = Path(__file__).with_name("ddp101-pertemuan-2.json")
tujuan.write_text(json.dumps(berkas, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
hitung = {}
for s in SOAL:
    hitung[s["difficulty"]] = hitung.get(s["difficulty"], 0) + 1
print(f"{tujuan.name}: {len(SOAL)} soal", hitung, f"{tujuan.stat().st_size} byte")
assert len({s['slug'] for s in SOAL}) == len(SOAL)
assert hitung == {"mudah_sekali": 8, "mudah": 8, "mudah_sedang": 6, "sedang": 6, "sulit": 4}, hitung
