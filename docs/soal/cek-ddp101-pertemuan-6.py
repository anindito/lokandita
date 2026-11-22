"""Perhitungan terpisah untuk DDP101 pertemuan 6, dipakai oleh periksa.py.

JAWAB[slug](masukan) dihitung dengan Python dan dengan cara yang berbeda dari
solusi referensi C++ (pustaka standar: math, fractions, datetime, calendar).
CONTOH memuat setiap contoh yang dikutip di pernyataan soal.
"""
import calendar
import datetime
import math
from fractions import Fraction


def _angka(m):
    return [int(x) for x in m.split()]


def _prima(n):
    return n > 1 and all(n % d for d in range(2, math.isqrt(n) + 1))


def _pembagi(n):
    return [k for k in range(1, n + 1) if n % k == 0]


def _bingkai(m):
    l, t = _angka(m)
    return "\n".join("#" * l if b in (0, t - 1) else "#" + " " * (l - 2) + "#" for b in range(t))


def _melingkar(m):
    a, b, c, k = _angka(m)
    v = [a, b, c]
    k %= 3
    v = v[-k:] + v[:-k] if k else v
    return " ".join(map(str, v))


def _sempurna(n):
    s = sum(_pembagi(n)) - n
    return "sempurna" if s == n else "berlimpah" if s > n else "kurang"


def _geser(m):
    baris = m.strip().split("\n")
    x, y = _angka(baris[0])
    arah = {"U": (0, 1), "S": (0, -1), "T": (1, 0), "B": (-1, 0)}
    for b in baris[2:]:
        a, langkah = b.split()
        x, y = x + arah[a][0] * int(langkah), y + arah[a][1] * int(langkah)
    return f"{x} {y}"


def _akar_digital(n):
    langkah = 0
    while n >= 10:
        n, langkah = sum(int(c) for c in str(n)), langkah + 1
    return f"{n} {langkah}"


def _maju(m):
    h, b, t, k = _angka(m)
    d = datetime.date(t, b, h) + datetime.timedelta(days=k)
    return f"{d.day} {d.month} {d.year}"


def _rekap(m):
    jumlah = {1: 0, 2: 0, 3: 0}
    for b in m.strip().split("\n")[1:]:
        kode, banyak = _angka(b)
        jumlah[kode] += banyak
    nama = {1: "Kopi", 2: "Teh", 3: "Susu"}
    laris = max(nama, key=lambda k: (jumlah[k], -k))
    return "\n".join(f"{nama[k]}: {jumlah[k]}" for k in nama) + f"\nTerlaris: {nama[laris]}"


def _basis(m):
    n, b = _angka(m)
    digit = "0123456789ABCDEF"
    hasil = ""
    while True:
        n, sisa = divmod(n, b)
        hasil = digit[sisa] + hasil
        if n == 0:
            return hasil


def _keliling(m):
    v = _angka(m)
    p = [(v[0], v[1]), (v[2], v[3]), (v[4], v[5])]
    return f"{sum(math.hypot(p[a][0] - p[a - 1][0], p[a][1] - p[a - 1][1]) for a in range(3)):.2f}"


def _pecahan(m):
    kiri, op, kanan = m.split()
    a, b = Fraction(kiri), Fraction(kanan)
    h = {"+": a + b, "-": a - b, "*": a * b, "/": a / b if b else None}[op]
    return str(h)  # Fraction mencetak "5/6", "-1/6", "2", "0"


def _goldbach(n):
    return next(f"{p} {n - p}" for p in range(2, n) if _prima(p) and _prima(n - p))


def _luhn(m):
    d = [int(c) for c in m.strip()][::-1]
    total = sum(d[0::2]) + sum(sum(divmod(2 * x, 10)) for x in d[1::2])
    return "sah" if total % 10 == 0 else "tidak sah"


def _romawi(m):
    s = m.strip()
    for dua, ganti in [("CM", "DCCCC"), ("CD", "CCCC"), ("XC", "LXXXX"), ("XL", "XXXX"), ("IX", "VIIII"), ("IV", "IIII")]:
        s = s.replace(dua, ganti)
    return str(sum({"I": 1, "V": 5, "X": 10, "L": 50, "C": 100, "D": 500, "M": 1000}[c] for c in s))


JAWAB = {
    "volume-kubus": lambda m: str(int(m) ** 3),
    "sapa-tiga-orang": lambda m: "\n".join(f"Halo, {n}!" for n in m.split()),
    "jarak-dua-bilangan": lambda m: str(abs(_angka(m)[0] - _angka(m)[1])),
    "tiga-garis": lambda m: "\n".join("-" * n for n in _angka(m)),
    "tukar-dua-bilangan": lambda m: " ".join(reversed(m.split())),
    "gandakan": lambda m: str(_angka(m)[0] << _angka(m)[1]),
    "bandingkan-luas": lambda m: (lambda v: "pertama" if v[0] * v[1] > v[2] * v[3] else "kedua" if v[0] * v[1] < v[2] * v[3] else "sama")(_angka(m)),
    "jumlah-kuadrat": lambda m: str(sum(x ** 2 for x in _angka(m))),
    "selisih-dua-waktu": lambda m: (lambda v: str(int((datetime.timedelta(hours=v[3], minutes=v[4], seconds=v[5])
                                                       - datetime.timedelta(hours=v[0], minutes=v[1], seconds=v[2])).total_seconds())))(_angka(m)),
    "hitung-kabisat": lambda m: str(calendar.leapdays(_angka(m)[0], _angka(m)[1] + 1)),
    "rentang-tiga": lambda m: (lambda v: f"{min(v)} {max(v)} {max(v) - min(v)}")(_angka(m)),
    "banyak-faktor": lambda m: str(len(_pembagi(int(m)))),
    "bingkai": _bingkai,
    "digit-terbesar": lambda m: max(m.strip()),
    "poin-pertandingan": lambda m: str(sum(3 if a > b else 1 if a == b else 0 for a, b in (_angka(x) for x in m.strip().split("\n")[1:]))),
    "kelipatan-terdekat": lambda m: (lambda n, k: str(math.ceil(Fraction(n, k)) * k))(*_angka(m)),
    "tukar-melingkar": _melingkar,
    "urutkan-empat": lambda m: " ".join(map(str, sorted(_angka(m)))),
    "bilangan-sempurna": lambda m: _sempurna(int(m)),
    "bilangan-armstrong": lambda m: "armstrong" if sum(int(c) ** len(m.strip()) for c in m.strip()) == int(m) else "bukan armstrong",
    "geser-titik": _geser,
    "akar-digital": lambda m: _akar_digital(int(m)),
    "maju-k-hari": _maju,
    "rekap-penjualan": _rekap,
    "kpk-tiga-bilangan": lambda m: str(math.lcm(*_angka(m))),
    "hitung-kata": lambda m: str(len(m.split())),
    "ke-basis": _basis,
    "keliling-segitiga-titik": _keliling,
    "kalkulator-pecahan": _pecahan,
    "goldbach": lambda m: _goldbach(int(m)),
    "luhn": _luhn,
    "romawi-ke-angka": _romawi,
}

CONTOH = [
    ("volume-kubus", "3\n", "27"),
    ("sapa-tiga-orang", "Budi Citra Eko\n", "Halo, Budi!\nHalo, Citra!\nHalo, Eko!"),
    ("jarak-dua-bilangan", "3 10\n", "7"), ("jarak-dua-bilangan", "10 3\n", "7"), ("jarak-dua-bilangan", "-4 5\n", "9"),
    ("tiga-garis", "5 2 7\n", "-----\n--\n-------"),
    ("tukar-dua-bilangan", "3 8\n", "8 3"),
    ("gandakan", "3 4\n", "48"), ("gandakan", "5 0\n", "5"),
    ("bandingkan-luas", "4 5\n3 6\n", "pertama"),
    ("jumlah-kuadrat", "1 -2 3\n", "14"),
    ("selisih-dua-waktu", "8 15 30\n10 0 0\n", "6270"),
    ("hitung-kabisat", "2000 2024\n", "7"), ("hitung-kabisat", "1896 1904\n", "2"),
    ("rentang-tiga", "8 -1 5\n", "-1 8 9"),
    ("banyak-faktor", "12\n", "6"), ("banyak-faktor", "1\n", "1"), ("banyak-faktor", "7\n", "2"),
    ("bingkai", "6 4\n", "######\n#    #\n#    #\n######"),
    ("digit-terbesar", "40726\n", "7"),
    ("poin-pertandingan", "3\n2 1\n0 0\n1 3\n", "4"),
    ("kelipatan-terdekat", "17 5\n", "20"), ("kelipatan-terdekat", "20 5\n", "20"),
    ("tukar-melingkar", "1 2 3\n1\n", "3 1 2"), ("tukar-melingkar", "1 2 3\n2\n", "2 3 1"),
    ("urutkan-empat", "7 -2 9 4\n", "-2 4 7 9"),
    ("bilangan-sempurna", "6\n", "sempurna"), ("bilangan-sempurna", "12\n", "berlimpah"), ("bilangan-sempurna", "8\n", "kurang"),
    ("bilangan-armstrong", "153\n", "armstrong"), ("bilangan-armstrong", "1634\n", "armstrong"), ("bilangan-armstrong", "154\n", "bukan armstrong"),
    ("geser-titik", "0 0\n3\nU 3\nT 2\nS 5\n", "2 -2"),
    ("akar-digital", "9875\n", "2 3"), ("akar-digital", "7\n", "7 0"),
    ("maju-k-hari", "28 2 2024 2\n", "1 3 2024"), ("maju-k-hari", "31 12 2025 1\n", "1 1 2026"),
    ("rekap-penjualan", "4\n1 3\n3 2\n1 4\n2 7\n", "Kopi: 7\nTeh: 7\nSusu: 2\nTerlaris: Kopi"),
    ("kpk-tiga-bilangan", "4 6 10\n", "60"),
    ("hitung-kata", "  belajar   kode  hari ini \n", "4"),
    ("ke-basis", "10 2\n", "1010"), ("ke-basis", "255 16\n", "FF"), ("ke-basis", "100 7\n", "202"), ("ke-basis", "0 5\n", "0"),
    ("keliling-segitiga-titik", "0 0\n3 0\n0 4\n", "12.00"),
    ("kalkulator-pecahan", "1/2 + 1/3\n", "5/6"), ("kalkulator-pecahan", "1/3 - 1/2\n", "-1/6"), ("kalkulator-pecahan", "3/4 * 2/3\n", "1/2"),
    ("kalkulator-pecahan", "1/2 / 1/4\n", "2"), ("kalkulator-pecahan", "3/7 - 3/7\n", "0"),
    ("goldbach", "28\n", "5 23"), ("goldbach", "4\n", "2 2"), ("goldbach", "100\n", "3 97"),
    ("luhn", "79927398713\n", "sah"), ("luhn", "79927398710\n", "tidak sah"),
    ("romawi-ke-angka", "XIV\n", "14"), ("romawi-ke-angka", "XLIX\n", "49"), ("romawi-ke-angka", "MCMXCIV\n", "1994"), ("romawi-ke-angka", "MMXXVI\n", "2026"),
]
