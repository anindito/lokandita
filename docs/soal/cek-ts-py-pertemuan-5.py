"""Perhitungan terpisah untuk TS-PY pertemuan 5, dipakai oleh periksa.py.

Solusi referensi memakai list, dictionary, set, fungsi dan perulangan biasa.
Di sini JAWAB[slug] menempuh jalan lain: collections.Counter dan defaultdict,
statistics, itertools.accumulate, ekspresi reguler untuk teks, bisect untuk
tabel, sorted dengan kunci, dan pecahan eksak (fractions.Fraction; akar lewat
decimal 50 angka). CONTOH memuat setiap contoh yang dikutip di pernyataan soal.
"""
import re
import statistics
from bisect import bisect_left
from collections import Counter, defaultdict
from decimal import ROUND_HALF_EVEN, Decimal as D, getcontext
from fractions import Fraction as F
from itertools import accumulate

getcontext().prec = 50


def _b(m):
    return m.strip("\n").split("\n")


def _i(s):
    return [int(x) for x in s.split()]


def f(x, d):
    if not isinstance(x, D):
        x = F(x)
        x = D(x.numerator) / D(x.denominator)
    return str(x.quantize(D(1).scaleb(-d), rounding=ROUND_HALF_EVEN))


def _di_atas(m):
    v = _i(m)
    return f"Rata-rata: {f(F(sum(v), len(v)), 2)}\nDi atas rata-rata: {len([x for x in v if x * len(v) > sum(v)])}"


def _median(m):
    v = sorted(_i(m))
    return " ".join(map(str, v)) + f"\nMedian: {f(F(statistics.median(v)), 1)}"


def _telapak(m):
    vol = [F(p * l * t, 10 ** 6) for p, l, t in (_i(s) for s in _b(m)[1:])]
    return "\n".join([f"Pondasi {k}: {f(v, 3)} m3" for k, v in enumerate(vol, 1)] + [f"Total: {f(sum(vol), 3)} m3"])


def _golongan(m):
    c = Counter(m.split())
    return "\n".join(f"{g}: {c[g]}" for g in ("MC", "LV", "HV"))


def _unik(m):
    u = sorted(Counter(_i(m)))
    return " ".join(map(str, u)) + f"\nJenis: {len(u)}"


def _sta(m):
    km, meter = re.fullmatch(r"STA (\d+)\+(\d{3})", m.strip()).groups()
    return f"{int(km + meter)} m"


def _deviasi(m):
    a, b = (_i(s) for s in _b(m))
    d = [y - x for x, y in zip(a, b)]
    return " ".join(map(str, d)) + f"\nMenyimpang: {len(d) - d.count(0)}"


def _besi(m):
    total = defaultdict(int)
    for d, p in (_i(s) for s in _b(m)[1:]):
        total[d] += p
    return "\n".join(f"D{d}: {total[d]} m" for d in sorted(total))


def _massa(m):
    harian, target = _i(_b(m)[0]), int(_b(m)[1])
    kum = list(accumulate(harian))
    k = bisect_left(kum, target)
    return " ".join(map(str, kum)) + "\n" + (f"Tercapai hari ke-{k + 1}" if k < len(kum) else "BELUM TERCAPAI")


def _gagal(m):
    b = _b(m)
    g = [s.split()[0] for s in b[2:] if int(s.split()[1]) < int(b[0])]
    return "\n".join(g + [f"Gagal: {len(g)}"]) if g else "SEMUA LULUS"


def _kemiringan(m):
    j, e = int(_b(m)[0]), _i(_b(m)[1])
    g = [F(b - a, j) for a, b in zip(e, e[1:])]
    k = max(range(len(g)), key=lambda n: (abs(g[n]), -n))
    return f"Segmen {k + 1}: {f(g[k], 2)}%"


def _ekstrem(m):
    v = _i(m)
    return f"Dibuang: {min(v)} dan {max(v)}\nRata-rata: {f(F(sum(v) - min(v) - max(v), len(v) - 2), 2)}"


def _interpolasi(m):
    b = _b(m)
    tabel = [tuple(_i(s)) for s in b[1:-1]]
    x = F(b[-1])
    for (x1, y1), (x2, y2) in zip(tabel, tabel[1:]):
        if x1 <= x <= x2:
            return f"Volume: {f(((x2 - x) * y1 + (x - x1) * y2) / (x2 - x1), 2)} ribu m3"


def _histogram(m):
    c = Counter(x - x % 20 for x in _i(m))
    return "\n".join(f"{lo}-{lo + 19}: {c[lo]}" + (" " + "#" * c[lo] if c[lo] else "") for lo in range(0, 100, 20))


def _kurang(m):
    b = _b(m)
    s = int(b[0])
    stok = {x.split()[0]: int(x.split()[1]) for x in b[1:1 + s]}
    butuh = Counter()
    for x in b[2 + s:]:
        butuh[x.split()[0]] += int(x.split()[1])
    baris = [f"{n}: kurang {butuh[n] - stok.get(n, 0)}" for n in sorted(butuh) if butuh[n] > stok.get(n, 0)]
    return "\n".join(baris) if baris else "CUKUP"


def _inersia(m):
    bagian = [tuple(_i(s)) for s in _b(m)[1:]]
    luas = sum(b * h for b, h, y in bagian)
    yb = F(sum(b * h * y for b, h, y in bagian), luas)
    # teorema sumbu sejajar dari sisi bawah: I = jumlah(I sendiri + A y^2) - A total ybar^2
    ix = sum(F(b * h ** 3, 12) + b * h * y * y for b, h, y in bagian) - luas * yb * yb
    return f"A = {luas} cm2\nybar = {f(yb, 2)} cm\nI = {f(ix, 1)} cm4"


def _grid(m):
    b = _b(m)
    sisi = int(b[1])
    petak = [(d, r, c) for r, s in enumerate(b[2:], 1) for c, d in enumerate(_i(s), 1)]
    d, r, c = min(petak, key=lambda p: (-p[0], p[1], p[2]))
    return f"Volume: {f(F(sisi * sisi * sum(p[0] for p in petak), 100), 2)} m3\nTerdalam: baris {r} kolom {c}"


def _peringkat(m):
    data = sorted((s.split() for s in _b(m)[1:]), key=lambda x: (int(x[1]), x[0]))
    return "\n".join(f"{k}. {n} {h}" for k, (n, h) in enumerate(data, 1))


def _poligon(m):
    t = [tuple(_i(s)) for s in _b(m)[1:]]
    sisi = list(zip(t, t[1:] + t[:1]))
    # luas lewat trapesium di bawah tiap sisi, bukan lewat hasil kali silang
    dua_luas = abs(sum((x2 - x1) * (y1 + y2) for (x1, y1), (x2, y2) in sisi))
    keliling = sum(D((x2 - x1) ** 2 + (y2 - y1) ** 2).sqrt() for (x1, y1), (x2, y2) in sisi)
    return f"Luas: {f(F(dua_luas, 2), 1)} m2\nKeliling: {f(keliling, 2)} m"


def _mutu(m):
    grup = defaultdict(list)
    for s in _b(m)[1:]:
        _, mutu, nilai = s.split()
        grup[int(mutu.lstrip("K"))].append(int(nilai))
    return "\n".join(
        f"K{t}: {len(v)} benda uji, rata-rata {f(F(sum(v), len(v)), 1)}, terendah {sorted(v)[0]}, "
        + ("MEMENUHI" if sum(v) >= t * len(v) else "DI BAWAH") for t, v in sorted(grup.items()))


def _jadwal(m):
    selesai, baris = {}, []
    for k, s in enumerate(_b(m)[1:], 1):
        durasi, *pendahulu = _i(s)
        mulai = max([selesai[p] for p in pendahulu], default=0)
        selesai[k] = mulai + durasi
        baris.append(f"Kegiatan {k}: mulai {mulai}, selesai {selesai[k]}")
    return "\n".join(baris + [f"Durasi proyek: {max(selesai.values())} hari"])


def _log(m):
    catatan = [s.split() for s in _b(m)[1:]]
    rit = Counter(p for _, p, _ in catatan)
    vol = Counter()
    for _, p, v in catatan:
        vol[p] += int(v)
    jam = Counter(w[:2] for w, _, _ in catatan)
    sibuk = min(jam, key=lambda j: (-jam[j], j))
    baris = [f"{p}: {rit[p]} rit, {vol[p]} m3" for p in sorted(rit, key=lambda p: (-vol[p], p))]
    return "\n".join(baris + [f"Jam tersibuk: {sibuk} ({jam[sibuk]} rit)"])


HARGA = dict(zip("semen pasir kerikil bata besi".split(), (65000, 250000, 300000, 900, 14000)))

JAWAB = {
    "elevasi-terbalik": lambda m: " ".join(m.split()[::-1]),
    "total-beban": lambda m: f"Total: {sum(_i(m))} kN",
    "nama-kapital": lambda m: "".join(chr(ord(c) - 32) if "a" <= c <= "z" else c for c in m.strip("\n")),
    "mutu-dari-kode": lambda m: m.strip()[m.strip().rindex("-") + 1:],
    "fungsi-luas-pelat": lambda m: f"Luas: {_i(m)[0] * _i(m)[1]} m2",
    "harga-material": lambda m: f"Harga: Rp {sum([HARGA[m.split()[0]]] * int(m.split()[1]))}",
    "min-maks-bacaan": lambda m: f"Min: {sorted(_i(m))[0]}\nMaks: {sorted(_i(m))[-1]}",
    "bacaan-ke": lambda m: f"Bacaan ke-{int(_b(m)[1])}: {_i(_b(m)[0])[int(_b(m)[1]) - 1]}",
    "di-atas-rata": _di_atas,
    "median-kuat-tekan": _median,
    "fungsi-volume-telapak": _telapak,
    "hitung-golongan": _golongan,
    "diameter-unik": _unik,
    "sta-ke-meter": _sta,
    "inisial-proyek": lambda m: "".join(w[:1] for w in m.split()).upper(),
    "deviasi-rencana": _deviasi,
    "rekap-besi": _besi,
    "kurva-massa-hujan": _massa,
    "benda-uji-gagal": _gagal,
    "kode-gambar": lambda m: "SAH" if re.fullmatch(r"[ASME]-[0-9]{3}", m.strip("\n")) else "TIDAK SAH",
    "fungsi-kemiringan": _kemiringan,
    "rata-tanpa-ekstrem": _ekstrem,
    "interpolasi-tabel": _interpolasi,
    "histogram-hujan": _histogram,
    "material-kurang": _kurang,
    "inersia-komposit": _inersia,
    "grid-galian": _grid,
    "peringkat-penawaran": _peringkat,
    "luas-poligon": _poligon,
    "rekap-per-mutu": _mutu,
    "jadwal-maju": _jadwal,
    "log-ritase": _log,
}

CONTOH = [
    ("elevasi-terbalik", "120 135 128\n", "128 135 120"),
    ("total-beban", "12 8 15\n", "Total: 35 kN"),
    ("nama-kapital", "Jembatan Ciliwung\n", "JEMBATAN CILIWUNG"),
    ("mutu-dari-kode", "BU-07-K300\n", "K300"),
    ("fungsi-luas-pelat", "6 4\n", "Luas: 24 m2"),
    ("harga-material", "pasir\n3\n", "Harga: Rp 750000"),
    ("min-maks-bacaan", "1482 1507 1390 1455\n", "Min: 1390\nMaks: 1507"),
    ("bacaan-ke", "85 92 110 97\n3\n", "Bacaan ke-3: 110"),
    ("di-atas-rata", "300 320 280 340\n", "Rata-rata: 310.00\nDi atas rata-rata: 2"),
    ("median-kuat-tekan", "310 270 290 300\n", "270 290 300 310\nMedian: 295.0"),
    ("fungsi-volume-telapak", "2\n100 100 30\n150 120 40\n", "Pondasi 1: 0.300 m3\nPondasi 2: 0.720 m3\nTotal: 1.020 m3"),
    ("hitung-golongan", "MC LV HV LV LV MC\n", "MC: 2\nLV: 3\nHV: 1"),
    ("diameter-unik", "13 10 16 13 10 13\n", "10 13 16\nJenis: 3"),
    ("sta-ke-meter", "STA 12+350\n", "12350 m"),
    ("sta-ke-meter", "STA 0+007\n", "7 m"),
    ("inisial-proyek", "Gedung kuliah Terpadu\n", "GKT"),
    ("deviasi-rencana", "500 520 540\n502 519 540\n", "2 -1 0\nMenyimpang: 2"),
    ("rekap-besi", "4\n13 6\n10 12\n13 9\n10 4\n", "D10: 16 m\nD13: 15 m"),
    ("kurva-massa-hujan", "10 0 25 5\n30\n", "10 10 35 40\nTercapai hari ke-3"),
    ("benda-uji-gagal", "300\n3\nBU-01 312\nBU-02 287\nBU-03 299\n", "BU-02\nBU-03\nGagal: 2"),
    ("kode-gambar", "S-101\n", "SAH"),
    ("kode-gambar", "s-101\n", "TIDAK SAH"),
    ("kode-gambar", "S101\n", "TIDAK SAH"),
    ("kode-gambar", "S-1A1\n", "TIDAK SAH"),
    ("kode-gambar", "S-1010\n", "TIDAK SAH"),
    ("fungsi-kemiringan", "50\n1000 1100 1050 1250\n", "Segmen 3: 4.00%"),
    ("rata-tanpa-ekstrem", "300 250 310 290 400\n", "Dibuang: 250 dan 400\nRata-rata: 300.00"),
    ("interpolasi-tabel", "3\n20 100\n25 400\n30 900\n27.0\n", "Volume: 600.00 ribu m3"),
    ("histogram-hujan", "5 12 25 47 81 30 18\n", "0-19: 3 ###\n20-39: 2 ##\n40-59: 1 #\n60-79: 0\n80-99: 1 #"),
    ("material-kurang", "2\nsemen 100\npasir 20\n3\nsemen 80\nbata 500\nsemen 40\n", "bata: kurang 500\nsemen: kurang 20"),
    ("inersia-komposit", "2\n20 40 20\n60 10 45\n", "A = 1400 cm2\nybar = 30.71 cm\nI = 325952.4 cm4"),
    ("grid-galian", "2 3\n10\n50 120 80\n200 90 200\n", "Volume: 740.00 m3\nTerdalam: baris 2 kolom 1"),
    ("peringkat-penawaran", "3\nPT-DAYA 950\nCV-ERA 900\nPT-ADHI 950\n", "1. CV-ERA 900\n2. PT-ADHI 950\n3. PT-DAYA 950"),
    ("luas-poligon", "4\n0 0\n40 0\n40 30\n0 30\n", "Luas: 1200.0 m2\nKeliling: 140.00 m"),
    ("rekap-per-mutu", "4\nBU-01 K300 312\nBU-02 K225 230\nBU-03 K300 281\nBU-04 K300 300\n",
     "K225: 1 benda uji, rata-rata 230.0, terendah 230, MEMENUHI\nK300: 3 benda uji, rata-rata 297.7, terendah 281, DI BAWAH"),
    ("jadwal-maju", "4\n5\n3 1\n6 1\n2 2 3\n",
     "Kegiatan 1: mulai 0, selesai 5\nKegiatan 2: mulai 5, selesai 8\nKegiatan 3: mulai 5, selesai 11\n"
     "Kegiatan 4: mulai 11, selesai 13\nDurasi proyek: 13 hari"),
    ("log-ritase", "4\n07:42 BK8123AB 7\n07:55 BK9001CD 12\n08:10 BK8123AB 7\n08:40 BK7745EF 12\n",
     "BK8123AB: 2 rit, 14 m3\nBK7745EF: 1 rit, 12 m3\nBK9001CD: 1 rit, 12 m3\nJam tersibuk: 07 (2 rit)"),
]
