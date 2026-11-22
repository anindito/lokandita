"""Perhitungan terpisah untuk DDP101 pertemuan 7, dipakai oleh periksa.py.

JAWAB[slug](masukan) dihitung dengan Python TANPA rekursi di mana pun solusi
referensi memakai rekursi: rumus tertutup, perulangan, simulasi, atau pustaka
standar (math.comb, itertools.permutations, pow dengan modulus). CONTOH memuat
setiap contoh yang dikutip di pernyataan soal.
"""
import itertools
import math


def _angka(m):
    return [int(x) for x in m.split()]


def _fib(n):
    a, b = 0, 1
    for _ in range(n):
        a, b = b, a + b
    return a


def _tangga(n):
    t = [1, 1, 2]
    while len(t) <= n:
        t.append(t[-1] + t[-2] + t[-3])
    return t[n]


def _penggaris(n):
    # Panjang garis ke-j (j = 1, 2, …) adalah 1 + banyak faktor 2 pada j.
    return "\n".join("-" * ((j & -j).bit_length()) for j in range(1, 2 ** n))


def _bercabang(n):
    nilai = [0] * (n + 1)
    for k in range(1, n + 1):
        nilai[k] = k + nilai[k // 2] + nilai[k // 3]
    return nilai[n]


def _tebak(m):
    n, t = _angka(m)
    bawah, atas, hasil = 1, n, []
    while True:
        tengah = (bawah + atas) // 2
        hasil.append(tengah)
        if tengah == t:
            return " ".join(map(str, hasil))
        if tengah < t:
            bawah = tengah + 1
        else:
            atas = tengah - 1


def _hanoi(n):
    # Simulasi dengan tiga tumpukan dan aturan iteratif: cakram 1 selalu bergerak
    # melingkar, dan di antaranya dilakukan satu-satunya langkah sah lain.
    tiang = {"A": list(range(n, 0, -1)), "B": [], "C": []}
    arah = ["A", "C", "B"] if n % 2 == 1 else ["A", "B", "C"]
    letak, hasil = 0, []
    for langkah in range(1, 2 ** n):
        if langkah % 2 == 1:
            asal, tujuan = arah[letak], arah[(letak + 1) % 3]
            letak = (letak + 1) % 3
        else:
            x, y = [t for t in "ABC" if t != arah[letak]]
            if not tiang[x] or (tiang[y] and tiang[y][-1] < tiang[x][-1]):
                x, y = y, x
            asal, tujuan = x, y
        cakram = tiang[asal].pop()
        tiang[tujuan].append(cakram)
        hasil.append(f"Cakram {cakram}: {asal} -> {tujuan}")
    assert tiang["C"] == list(range(n, 0, -1))
    return "\n".join(hasil)


def _ackermann(m, n):
    return [n + 1, n + 2, 2 * n + 3, 2 ** (n + 3) - 3][m]


def _josephus(m):
    n, k = _angka(m)
    orang, p = list(range(1, n + 1)), 0
    while len(orang) > 1:
        p = (p + k - 1) % len(orang)
        orang.pop(p)
    return str(orang[0])


def _partisi(n):
    cara = [1] + [0] * n
    for suku in range(1, n + 1):
        for total in range(suku, n + 1):
            cara[total] += cara[total - suku]
    return cara[n]


def _kurung(n):
    hasil = []
    for bit in itertools.product("()", repeat=2 * n):
        saldo = 0
        for c in bit:
            saldo += 1 if c == "(" else -1
            if saldo < 0:
                break
        else:
            if saldo == 0:
                hasil.append("".join(bit))
    return "\n".join(hasil)


def _cantor(n):
    baris = ["#" * 3 ** n]
    for t in range(1, n + 1):
        ruas = 3 ** (n - t)
        lama = baris[-1]
        baru = ""
        for p in range(0, 3 ** n, 3 * ruas):
            blok = lama[p:p + 3 * ruas]
            baru += blok[:ruas] + (" " * ruas if blok.strip() else blok[ruas:2 * ruas]) + blok[2 * ruas:]
        baris.append(baru)
    return "\n".join(b.rstrip() for b in baris)


def _ribuan(n):
    return f"{n:,}".replace(",", ".")


def _waktu(m):
    v = _angka(m.split("\n", 1)[1]) + [0, 0]
    return "%02d:%02d:%02d" % tuple(v[:3])


def _gabung(m):
    jenis, a, b = m.split()
    return str(int(a) + int(b)) if jenis == "angka" else a + b


def _luas(m):
    v = _angka(m)
    return str(v[1] ** 2 if v[0] == 1 else v[1] * v[2])


JAWAB = {
    "faktorial": lambda m: str(math.factorial(int(m))),
    "suku-ke-n": lambda m: str(2 ** (int(m) + 1) - 1),
    "kali-dengan-tambah": lambda m: str(math.prod(_angka(m))),
    "banyak-digit": lambda m: str(len(m.strip())),
    "jumlah-kuadrat-deret": lambda m: (lambda n: str(n * (n + 1) * (2 * n + 1) // 6))(int(m)),
    "naik-turun": lambda m: (lambda v: " ".join(map(str, v[::-1] + v)))(list(range(1, int(m) + 1))),
    "luas-dua-bentuk": _luas,
    "bagi-dua-berulang": lambda m: str(int(m).bit_length() - 1),
    "fibonacci": lambda m: str(_fib(int(m))),
    "hanoi-langkah": lambda m: str(2 ** int(m) - 1),
    "digit-genap": lambda m: str(sum(c in "02468" for c in m.strip())),
    "volume-bawaan": lambda m: str(math.prod(_angka(m.split("\n", 1)[1]))),
    "gabung-atau-jumlah": _gabung,
    "penggaris": lambda m: _penggaris(int(m)),
    "fungsi-91": lambda m: str(int(m) - 10 if int(m) > 100 else 91),
    "naik-tangga": lambda m: str(_tangga(int(m))),
    "kombinasi": lambda m: str(math.comb(*_angka(m))),
    "format-waktu": _waktu,
    "hitung-panggilan": lambda m: f"{_fib(int(m))} {2 * _fib(int(m) + 1) - 1}",
    "fungsi-bercabang": lambda m: str(_bercabang(int(m))),
    "tebak-biner": _tebak,
    "pemisah-ribuan": lambda m: _ribuan(int(m)),
    "menara-hanoi": lambda m: _hanoi(int(m)),
    "pangkat-cepat": lambda m: str(pow(*_angka(m))),
    "ackermann": lambda m: str(_ackermann(*_angka(m))),
    "josephus": _josephus,
    "partisi": lambda m: str(_partisi(int(m))),
    "catalan": lambda m: (lambda n: str(math.comb(2 * n, n) // (n + 1)))(int(m)),
    "permutasi-huruf": lambda m: "\n".join(sorted("".join(p) for p in itertools.permutations(m.strip()))),
    "kurung-seimbang": lambda m: _kurung(int(m)),
    "himpunan-cantor": lambda m: _cantor(int(m)),
    "kalkulator-rekursif": lambda m: str(eval(m.strip(), {"__builtins__": {}})),  # masukan hanya dari generator dan CONTOH di berkas ini
}

CONTOH = [
    ("faktorial", "5\n", "120"), ("faktorial", "0\n", "1"),
    ("suku-ke-n", "4\n", "31"), ("suku-ke-n", "1\n", "3"),
    ("kali-dengan-tambah", "4 3\n", "12"),
    ("banyak-digit", "40726\n", "5"), ("banyak-digit", "0\n", "1"),
    ("jumlah-kuadrat-deret", "3\n", "14"),
    ("naik-turun", "3\n", "3 2 1 1 2 3"),
    ("luas-dua-bentuk", "1 5\n", "25"), ("luas-dua-bentuk", "2 4 6\n", "24"),
    ("bagi-dua-berulang", "10\n", "3"), ("bagi-dua-berulang", "1\n", "0"),
    ("fibonacci", "10\n", "55"),
    ("hanoi-langkah", "3\n", "7"),
    ("digit-genap", "40726\n", "4"), ("digit-genap", "0\n", "1"),
    ("volume-bawaan", "1\n7\n", "7"), ("volume-bawaan", "2\n7 3\n", "21"), ("volume-bawaan", "3\n7 3 2\n", "42"),
    ("gabung-atau-jumlah", "angka 12 30\n", "42"), ("gabung-atau-jumlah", "teks buku tugas\n", "bukutugas"),
    ("penggaris", "3\n", "-\n--\n-\n---\n-\n--\n-"),
    ("fungsi-91", "105\n", "95"), ("fungsi-91", "99\n", "91"),
    ("naik-tangga", "3\n", "4"), ("naik-tangga", "4\n", "7"),
    ("kombinasi", "5 2\n", "10"),
    ("format-waktu", "1\n7\n", "07:00:00"), ("format-waktu", "2\n7 5\n", "07:05:00"), ("format-waktu", "3\n13 45 9\n", "13:45:09"),
    ("hitung-panggilan", "5\n", "5 15"), ("hitung-panggilan", "1\n", "1 1"),
    ("fungsi-bercabang", "1\n", "1"), ("fungsi-bercabang", "2\n", "3"), ("fungsi-bercabang", "3\n", "5"), ("fungsi-bercabang", "6\n", "14"),
    ("tebak-biner", "100 37\n", "50 25 37"),
    ("pemisah-ribuan", "1234567\n", "1.234.567"), ("pemisah-ribuan", "5003020\n", "5.003.020"),
    ("pemisah-ribuan", "999\n", "999"), ("pemisah-ribuan", "1000\n", "1.000"),
    ("menara-hanoi", "2\n", "Cakram 1: A -> B\nCakram 2: A -> C\nCakram 1: B -> C"),
    ("pangkat-cepat", "2 10 1000\n", "24"), ("pangkat-cepat", "7 0 5\n", "1"), ("pangkat-cepat", "3 200 13\n", "9"),
    ("ackermann", "1 2\n", "4"), ("ackermann", "2 3\n", "9"), ("ackermann", "3 3\n", "61"),
    ("josephus", "7 3\n", "4"),
    ("partisi", "5\n", "7"),
    ("catalan", "2\n", "2"), ("catalan", "3\n", "5"), ("catalan", "4\n", "14"),
    ("permutasi-huruf", "cab\n", "abc\nacb\nbac\nbca\ncab\ncba"),
    ("kurung-seimbang", "3\n", "((()))\n(()())\n(())()\n()(())\n()()()"),
    ("himpunan-cantor", "2\n", "#########\n###   ###\n# #   # #"), ("himpunan-cantor", "0\n", "#"),
    ("kalkulator-rekursif", "2*(3+4)-5\n", "9"), ("kalkulator-rekursif", "2+3*4\n", "14"), ("kalkulator-rekursif", "(2+3)*4\n", "20"),
    ("kalkulator-rekursif", "9-4-3\n", "2"), ("kalkulator-rekursif", "2*(3+(4-1))*5\n", "60"),
]
