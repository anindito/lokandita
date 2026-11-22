"""Perhitungan terpisah untuk DDP101 pertemuan 5, dipakai oleh periksa.py.

JAWAB[slug](masukan) dihitung dengan Python dan dengan cara yang berbeda dari
solusi referensi C++ (rumus tertutup, pustaka standar, fungsi bawaan). CONTOH
memuat setiap contoh yang dikutip di pernyataan soal.
"""
import itertools
import math
import re


def _angka(m):
    return [int(x) for x in m.split()]


def _isi(m):
    """Bilangan-bilangan sesudah baris pertama."""
    return _angka(m.split("\n", 1)[1])


def _prima(n):
    return n > 1 and all(n % d for d in range(2, math.isqrt(n) + 1))


def _faktor(n):
    hasil, d = [], 2
    while n > 1:
        if n % d == 0:
            hasil.append(d)
            n //= d
        else:
            d += 1 if d * d <= n else n - d  # lompat ke n bila tidak ada pembagi lagi
    return " ".join(map(str, hasil))


def _collatz(n):
    langkah = 0
    while n != 1:
        n = n // 2 if n % 2 == 0 else 3 * n + 1
        langkah += 1
    return str(langkah)


def _cari(m):
    (n, x), v = _angka(m.split("\n")[0]), _angka(m.split("\n")[1])
    return str(v.index(x) + 1) if x in v else "tidak ada"


def _statistik(m):
    v = _isi(m)
    return (f"Terendah: {min(v)}\nTertinggi: {max(v)}\nRata-rata: {sum(v) / len(v):.2f}\n"
            f"Lulus: {len([x for x in v if x >= 60])}")


def _palindrom(m):
    bersih = m.strip().replace(" ", "").lower()
    return "palindrom" if bersih == bersih[::-1] else "bukan palindrom"


def _ketupat(n):
    lebar = 2 * n - 1
    baris = []
    for b in list(range(n)) + list(range(n - 2, -1, -1)):
        kanvas = [" "] * lebar
        kanvas[n - 1 - b] = kanvas[n - 1 + b] = "*"
        baris.append("".join(kanvas).rstrip())
    return "\n".join(baris)


def _tripel(n):
    kuadrat = {c * c for c in range(1, n + 1)}
    return str(sum(1 for a, b in itertools.combinations(range(1, n + 1), 2) if a * a + b * b in kuadrat))


def _atm(m):
    baris = m.strip().split("\n")
    saldo, ditolak, beruntun, status = int(baris[0]), 0, 0, "normal"
    for perintah in baris[1:]:
        if perintah == "selesai":
            break
        kata, x = perintah.split()
        x = int(x)
        sah = kata == "setor" or (x % 50000 == 0 and x <= saldo)
        if sah:
            saldo += x if kata == "setor" else -x
            beruntun = 0
        else:
            ditolak, beruntun = ditolak + 1, beruntun + 1
            if beruntun >= 3:
                status = "diblokir"
                break
    return f"Saldo akhir: {saldo}\nDitolak: {ditolak}\nStatus: {status}"


def _runtun(m):
    v = _isi(m)
    naik = "".join("n" if b > a else "x" for a, b in zip(v, v[1:]))
    return str(1 + max(len(bagian) for bagian in naik.split("x")))


JAWAB = {
    "hitung-sampai-n": lambda m: "\n".join(str(k) for k in range(1, int(m) + 1)),
    "hitung-mundur": lambda m: " ".join(str(k) for k in reversed(range(1, int(m) + 1))),
    "jumlah-n-bilangan": lambda m: str(sum(_isi(m))),
    "garis-bintang": lambda m: "*" * int(m),
    "tabel-perkalian": lambda m: "\n".join(f"{int(m)} x {k} = {int(m) * k}" for k in range(1, 11)),
    "genap-sampai-n": lambda m: " ".join(str(k) for k in range(1, int(m) + 1) if k % 2 == 0),
    "pangkat": lambda m: str(_angka(m)[0] ** _angka(m)[1]),
    "hitung-positif": lambda m: str(len([x for x in _isi(m) if x > 0])),
    "rata-rata-n": lambda m: f"{sum(_isi(m)) / len(_isi(m)):.2f}",
    "terbesar-dari-n": lambda m: str(max(_isi(m))),
    "jumlah-digit": lambda m: str(sum(int(c) for c in m.strip())),
    "hitung-vokal": lambda m: str(len(re.findall("[aiueo]", m))),
    "persegi-bintang": lambda m: "\n".join(["*" * int(m)] * int(m)),
    "baca-sampai-nol": lambda m: str(sum(_angka(m))),
    "balik-kata": lambda m: m.strip()[::-1],
    "jumlah-kelipatan": lambda m: str(sum(set(range(3, int(m) + 1, 3)) | set(range(5, int(m) + 1, 5)))),
    "segitiga-bintang": lambda m: "\n".join("*" * b for b in range(1, int(m) + 1)),
    "bilangan-prima": lambda m: "prima" if _prima(int(m)) else "bukan prima",
    "fpb": lambda m: str(math.gcd(*_angka(m))),
    "cari-pertama": _cari,
    "balik-bilangan": lambda m: str(int(m.strip()[::-1])),
    "langkah-collatz": lambda m: _collatz(int(m)),
    "kompres-huruf": lambda m: "".join(f"{k}{len(list(g))}" for k, g in itertools.groupby(m.strip())),
    "faktor-prima": lambda m: _faktor(int(m)),
    "statistik-nilai": _statistik,
    "palindrom-kalimat": _palindrom,
    "prima-di-rentang": lambda m: str(sum(_prima(n) for n in range(_angka(m)[0], _angka(m)[1] + 1))),
    "papan-catur": lambda m: "\n".join("".join("#." [(b + k) % 2] for k in range(_angka(m)[1])) for b in range(_angka(m)[0])),
    "ketupat-berongga": lambda m: _ketupat(int(m)),
    "tripel-pythagoras": lambda m: _tripel(int(m)),
    "mesin-atm": _atm,
    "runtun-naik-terpanjang": _runtun,
}

CONTOH = [
    ("hitung-mundur", "5\n", "5 4 3 2 1"),
    ("jumlah-n-bilangan", "3\n4 -1 7\n", "10"),
    ("garis-bintang", "6\n", "******"),
    ("tabel-perkalian", "7\n", "7 x 1 = 7\n7 x 2 = 14\n7 x 3 = 21\n7 x 4 = 28\n7 x 5 = 35\n7 x 6 = 42\n7 x 7 = 49\n7 x 8 = 56\n7 x 9 = 63\n7 x 10 = 70"),
    ("genap-sampai-n", "9\n", "2 4 6 8"),
    ("pangkat", "2 5\n", "32"), ("pangkat", "7 0\n", "1"),
    ("hitung-positif", "4\n3 -2 0 8\n", "2"),
    ("rata-rata-n", "3\n70 85 90\n", "81.67"),
    ("terbesar-dari-n", "3\n-7 -2 -9\n", "-2"),
    ("jumlah-digit", "4096\n", "19"),
    ("hitung-vokal", "buku tugas\n", "4"),
    ("persegi-bintang", "3\n", "***\n***\n***"),
    ("baca-sampai-nol", "5\n-2\n10\n0\n", "13"), ("baca-sampai-nol", "0\n", "0"),
    ("balik-kata", "program\n", "margorp"),
    ("jumlah-kelipatan", "10\n", "33"),
    ("segitiga-bintang", "4\n", "*\n**\n***\n****"),
    ("bilangan-prima", "1\n", "bukan prima"), ("bilangan-prima", "11\n", "prima"),
    ("fpb", "12 18\n", "6"),
    ("cari-pertama", "4 7\n4 7 1 7\n", "2"), ("cari-pertama", "4 9\n4 7 1 7\n", "tidak ada"),
    ("balik-bilangan", "1234\n", "4321"), ("balik-bilangan", "1200\n", "21"),
    ("langkah-collatz", "6\n", "8"), ("langkah-collatz", "1\n", "0"),
    ("kompres-huruf", "aaabbc\n", "a3b2c1"), ("kompres-huruf", "abca\n", "a1b1c1a1"),
    ("faktor-prima", "360\n", "2 2 2 3 3 5"), ("faktor-prima", "97\n", "97"), ("faktor-prima", "1001\n", "7 11 13"),
    ("statistik-nilai", "4\n55 80 72 40\n", "Terendah: 40\nTertinggi: 80\nRata-rata: 61.75\nLulus: 2"),
    ("palindrom-kalimat", "katak\n", "palindrom"), ("palindrom-kalimat", "Kasur ini rusak\n", "palindrom"),
    ("palindrom-kalimat", "Ibu Ratna antar ubi\n", "palindrom"), ("palindrom-kalimat", "Belajar kode\n", "bukan palindrom"),
    ("prima-di-rentang", "1 10\n", "4"), ("prima-di-rentang", "10 20\n", "4"), ("prima-di-rentang", "24 28\n", "0"),
    ("papan-catur", "3 5\n", "#.#.#\n.#.#.\n#.#.#"),
    ("ketupat-berongga", "3\n", "  *\n * *\n*   *\n * *\n  *"), ("ketupat-berongga", "1\n", "*"),
    ("tripel-pythagoras", "20\n", "6"), ("tripel-pythagoras", "4\n", "0"),
    ("mesin-atm", "200000\ntarik 150000\ntarik 100000\nsetor 30000\ntarik 75000\nselesai\n", "Saldo akhir: 80000\nDitolak: 2\nStatus: normal"),
    ("runtun-naik-terpanjang", "6\n3 5 5 6 7 2\n", "3"), ("runtun-naik-terpanjang", "3\n9 9 4\n", "1"),
]
