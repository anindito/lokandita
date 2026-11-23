"""Perhitungan terpisah untuk TS-PY pertemuan 4, dipakai oleh periksa.py.

Solusi referensi mengolah data satu per satu di dalam for / while, tanpa list.
Di sini JAWAB[slug] menempuh jalan lain: seluruh masukan dibaca ke dalam list
lalu diolah dengan sum, max, sorted, itertools dan rumus tertutup (pembagian
ke atas, FPB dari math.gcd, deret); pecahan dihitung eksak dengan
fractions.Fraction; kedalaman normal dicari dengan decimal 50 angka dan 400
langkah bagi dua atas pangkat lewat ln/exp. CONTOH memuat setiap contoh yang
dikutip di pernyataan soal.
"""
import math
from decimal import ROUND_HALF_EVEN, Decimal as D, getcontext
from fractions import Fraction as F
from itertools import groupby, product

getcontext().prec = 50
PI = F("3.14159265358979323846264338327950288419716939937510")


def _i(m):
    return [int(x) for x in m.split()]


def f(x, d):
    x = F(x)
    return str((D(x.numerator) / D(x.denominator)).quantize(D(1).scaleb(-d), rounding=ROUND_HALF_EVEN))


def _pompa(m):
    v, a, p = _i(m)
    return f"Kering setelah {-(-v // (p - a))} jam"


def _persen(m):
    n, b, *v = _i(m)
    k = len([x for x in v if x >= b])
    return f"Lulus: {k} dari {n}\nPersentase: {f(F(100 * k, n), 1)}%"


def _kecepatan(m):
    a, b, s = _i(m)
    return "\n".join(f"{v} km/jam = {f(F(v * 5, 18), 2)} m/detik" for v in range(a, b + 1, s))


def _puncak(m):
    v = _i(m)[1:]
    return f"Jam ke-{v.index(max(v)) + 1}: {max(v)} kendaraan"


def _stok(m):
    s, batas, n, *v = _i(m)
    sisa = [s - sum(v[:k]) for k in range(1, n + 1)]
    kurang = [k for k, x in enumerate(sisa, 1) if x < batas]
    if kurang:
        return f"Pesan ulang pada hari {kurang[0]}\nSisa: {sisa[kurang[0] - 1]} sak"
    return f"STOK AMAN\nSisa: {sisa[-1]} sak"


def _lhr(m):
    lhr, p, t = _i(m)
    for _ in range(t):
        lhr = math.floor(F(lhr) * F(100 + p, 100))
    return f"LHR tahun ke-{t}: {lhr}"


def _naik(m):
    e = _i(m)[1:]
    beda = [b - a for a, b in zip(e, e[1:])]
    return f"Naik: {sum(x for x in beda if x > 0)} cm\nTurun: {-sum(x for x in beda if x < 0)} cm"


def _kemarau(m):
    v = _i(m)[1:]
    deret = [len(list(g)) for kering, g in groupby(v, key=lambda x: x == 0) if kering]
    return f"Kering terpanjang: {max(deret, default=0)} hari"


def _sungai(m):
    d, n, *h = _i(m)
    luas = F(d) * (sum(h) - F(h[0] + h[-1], 2)) / 100        # aturan trapesium bentuk tertutup
    return f"Luas: {f(luas, 2)} m2"


def _bersama(m):
    a, b, t = _i(m)
    kpk = a * b // math.gcd(a, b)
    menit = list(range(kpk, t + 1, kpk))
    return "\n".join([str(x) for x in menit] + [f"Jumlah: {len(menit)}"])


def _susut(m):
    nilai, p, batas = _i(m)
    baris = []
    while not baris or nilai >= batas:
        nilai = math.floor(F(nilai * (100 - p), 100))
        baris.append(f"Tahun {len(baris) + 1}: {nilai}")
    return "\n".join(baris)


def _grid(m):
    baris, kolom, sx, sy = _i(m)
    titik = [f"{x * sx} {y * sy}" for y, x in product(range(baris), range(kolom))]
    return "\n".join(titik + [f"Jumlah: {len(titik)}"])


def _sensor(m):
    v = [x for x in _i(m)[1:] if x != -1]
    return f"Sah: {len(v)}\n" + (f"Rata-rata: {f(F(sum(v), len(v)), 1)} cm" if v else "TIDAK ADA DATA")


def _denah(m):
    p, l, s = _i(m)
    baris = ["".join("K" if x % s == 0 else "." for x in range(p + 1)) if y % s == 0 else "." * (p + 1) for y in range(l + 1)]
    return "\n".join(baris + [f"Kolom: {(p // s + 1) * (l // s + 1)}"])


def _cicilan(m):
    sisa, b, a = _i(m)
    bulan = 0
    while True:
        bulan += 1
        tagihan = sisa + math.floor(F(sisa * b, 100))
        if tagihan - a <= 0:
            return f"Lunas dalam {bulan} bulan\nAngsuran terakhir: {tagihan}"
        sisa = tagihan - a


def _tiga_hari(m):
    v = _i(m)[1:]
    jumlah = [sum(v[k:k + 3]) for k in range(len(v) - 2)]
    return f"Maksimum 3 hari: {max(jumlah)} mm\nMulai hari ke-{jumlah.index(max(jumlah)) + 1}"


def _tulangan(m):
    perlu = _i(m)[0]
    pilihan = sorted((n * d * d, n, d) for d in (10, 13, 16, 19, 22, 25) for n in range(2, 9) if n * d * d * PI >= 4 * perlu)
    if not pilihan:
        return "TIDAK ADA"
    nd2, n, d = pilihan[0]
    return f"{n} D{d}\nAs = {f(nd2 * PI / 4, 1)} mm2"


def _potong(m):
    v = _i(m)[1:]
    batang = []                         # isi tiap batang yang dibuka
    for p in v:
        if batang and sum(batang[-1]) + p <= 1200:
            batang[-1].append(p)
        else:
            batang.append([p])
    return f"Batang: {len(batang)}\nSisa total: {sum(1200 - sum(b) for b in batang)} cm"


def _ubin(m):
    p, l = _i(m)
    g = math.gcd(p, l)
    return f"Sisi ubin: {g} cm\nJumlah: {p * l // (g * g)}"


def _waduk(m):
    c, s, rel, n, *masuk = _i(m)
    limpas, kurang = [], 0
    for q in masuk:
        s += q
        kurang += s < rel
        s = max(0, s - rel)
        limpas.append(max(0, s - c))
        s = min(s, c)
    return (f"Tampungan akhir: {s}\nTotal limpasan: {sum(limpas)}\nJam melimpas: {len([x for x in limpas if x])}\n"
            f"Jam kekurangan: {kurang}")


def _biseksi(m):
    b, n, s, q = [D(x) for x in m.split()]

    def debit(h):
        a = b * h
        return a * ((D(2) / D(3)) * (a / (b + 2 * h)).ln()).exp() * s.sqrt() / n

    lo, hi = D(0), D(10)
    for _ in range(400):
        mid = (lo + hi) / 2
        if mid > 0 and debit(mid) < q:
            lo = mid
        else:
            hi = mid
    return f"h = {((lo + hi) / 2).quantize(D('0.001'), rounding=ROUND_HALF_EVEN)} m"


def _truk(m):
    v = _i(m)[0]
    cara = [(k, s, b) for b in range(v // 12 + 1) for s in range(v // 8 + 1) for k in range(v // 5 + 1) if 5 * k + 8 * s + 12 * b == v]
    if not cara:
        return "Cara: 0\nTIDAK BISA"
    k, s, b = min(cara, key=lambda c: (sum(c), -c[2], -c[1]))
    return f"Cara: {len(cara)}\nTersedikit: {k + s + b} truk ({k} kecil, {s} sedang, {b} besar)"


def _mixer(m):
    n, d, w, *tiba = _i(m)
    bebas, tunggu, tolak = 0, [], 0
    for t in tiba:
        if max(bebas, t) - t > w:
            tolak += 1
        else:
            tunggu.append(max(bebas, t) - t)
            bebas = max(bebas, t) + d
    return f"Dilayani: {len(tunggu)}\nDitolak: {tolak}\nTunggu terlama: {max(tunggu)} menit\nSelesai: menit {bebas}"


JAWAB = {
    "total-ritase": lambda m: f"Total: {sum(_i(m)[1:])} m3",
    "patok-berjarak": lambda m: "\n".join(str(k * _i(m)[1]) for k in range(_i(m)[0] // _i(m)[1] + 1)),
    "tabel-inci": lambda m: "\n".join(f"{k} in = {f(F(254 * k, 10), 1)} mm" for k in range(1, _i(m)[0] + 1)),
    "rata-kuat-tekan": lambda m: f"Rata-rata: {f(F(sum(_i(m)[1:]), _i(m)[0]), 2)} MPa",
    "hari-hujan-sebulan": lambda m: f"Hari hujan: {len([x for x in _i(m)[1:] if x])}",
    "beban-terbesar": lambda m: f"Terbesar: {sorted(_i(m)[1:])[-1]} kg",
    "anak-tangga": lambda m: "\n".join(f"Anak tangga {k}: {k * _i(m)[1]} cm" for k in range(1, _i(m)[0] + 1)),
    "muatan-sampai-nol": lambda m: f"Total: {sum(_i(m))} kg",
    "rekap-hujan": lambda m: (f"Total: {sum(_i(m)[1:])} mm\nRata-rata: {f(F(sum(_i(m)[1:]), _i(m)[0]), 1)} mm\n"
                              f"Maksimum: {max(_i(m)[1:])} mm"),
    "pompa-banjir": _pompa,
    "persen-lulus": _persen,
    "tabel-kecepatan": _kecepatan,
    "jam-puncak": _puncak,
    "stok-semen": _stok,
    "pertumbuhan-lhr": _lhr,
    "naik-turun": _naik,
    "kemarau-terpanjang": _kemarau,
    "luas-penampang-sungai": _sungai,
    "truk-bersamaan": _bersama,
    "penyusutan-alat": _susut,
    "grid-tiang": _grid,
    "saring-sensor": _sensor,
    "denah-kolom": _denah,
    "cicilan-alat": _cicilan,
    "hujan-tiga-hari": _tiga_hari,
    "pilih-tulangan": _tulangan,
    "potong-besi-berurutan": _potong,
    "ubin-terbesar": _ubin,
    "routing-waduk": _waduk,
    "kedalaman-biseksi": _biseksi,
    "kombinasi-truk": _truk,
    "antrean-mixer": _mixer,
}

CONTOH = [
    ("total-ritase", "3\n7\n7\n5\n", "Total: 19 m3"),
    ("patok-berjarak", "100\n25\n", "0\n25\n50\n75\n100"),
    ("patok-berjarak", "90\n25\n", "0\n25\n50\n75"),
    ("tabel-inci", "3\n", "1 in = 25.4 mm\n2 in = 50.8 mm\n3 in = 76.2 mm"),
    ("rata-kuat-tekan", "4\n25\n27\n30\n26\n", "Rata-rata: 27.00 MPa"),
    ("hari-hujan-sebulan", "5\n0\n12\n0\n3\n40\n", "Hari hujan: 3"),
    ("beban-terbesar", "4\n8200\n7900\n10150\n9600\n", "Terbesar: 10150 kg"),
    ("anak-tangga", "3\n18\n", "Anak tangga 1: 18 cm\nAnak tangga 2: 36 cm\nAnak tangga 3: 54 cm"),
    ("muatan-sampai-nol", "1200\n800\n1500\n0\n", "Total: 3500 kg"),
    ("rekap-hujan", "4\n10\n0\n25\n7\n", "Total: 42 mm\nRata-rata: 10.5 mm\nMaksimum: 25 mm"),
    ("pompa-banjir", "500\n20\n120\n", "Kering setelah 5 jam"),
    ("persen-lulus", "4\n25\n26\n24\n25\n30\n", "Lulus: 3 dari 4\nPersentase: 75.0%"),
    ("tabel-kecepatan", "40\n60\n10\n", "40 km/jam = 11.11 m/detik\n50 km/jam = 13.89 m/detik\n60 km/jam = 16.67 m/detik"),
    ("jam-puncak", "4\n310\n455\n455\n290\n", "Jam ke-2: 455 kendaraan"),
    ("stok-semen", "100\n30\n4\n20\n25\n30\n10\n", "Pesan ulang pada hari 3\nSisa: 25 sak"),
    ("stok-semen", "100\n10\n4\n20\n25\n30\n10\n", "STOK AMAN\nSisa: 15 sak"),
    ("pertumbuhan-lhr", "1000\n5\n3\n", "LHR tahun ke-3: 1157"),
    ("naik-turun", "5\n100\n130\n120\n160\n150\n", "Naik: 70 cm\nTurun: 20 cm"),
    ("kemarau-terpanjang", "8\n0\n0\n5\n0\n0\n0\n12\n0\n", "Kering terpanjang: 3 hari"),
    ("luas-penampang-sungai", "2\n4\n0\n120\n180\n0\n", "Luas: 6.00 m2"),
    ("truk-bersamaan", "12\n18\n100\n", "36\n72\nJumlah: 2"),
    ("penyusutan-alat", "1000\n20\n500\n", "Tahun 1: 800\nTahun 2: 640\nTahun 3: 512\nTahun 4: 409"),
    ("grid-tiang", "2\n3\n150\n200\n", "0 0\n150 0\n300 0\n0 200\n150 200\n300 200\nJumlah: 6"),
    ("saring-sensor", "5\n120\n-1\n130\n-1\n125\n", "Sah: 3\nRata-rata: 125.0 cm"),
    ("denah-kolom", "6\n3\n3\n", "K..K..K\n.......\n.......\nK..K..K\nKolom: 6"),
    ("cicilan-alat", "1000\n2\n300\n", "Lunas dalam 4 bulan\nAngsuran terakhir: 144"),
    ("hujan-tiga-hari", "6\n10\n0\n30\n45\n5\n20\n", "Maksimum 3 hari: 80 mm\nMulai hari ke-3"),
    ("pilih-tulangan", "750\n", "2 D22\nAs = 760.3 mm2"),
    ("potong-besi-berurutan", "5\n500\n400\n400\n700\n300\n", "Batang: 3\nSisa total: 1300 cm"),
    ("ubin-terbesar", "360\n240\n", "Sisi ubin: 120 cm\nJumlah: 6"),
    ("routing-waduk", "100\n60\n20\n4\n50\n80\n0\n5\n", "Tampungan akhir: 65\nTotal limpasan: 50\nJam melimpas: 1\nJam kekurangan: 0"),
    ("kedalaman-biseksi", "2\n0.015\n0.001\n3.00\n", "h = 1.096 m"),
    ("kombinasi-truk", "29\n", "Cara: 2\nTersedikit: 3 truk (1 kecil, 0 sedang, 2 besar)"),
    ("kombinasi-truk", "3\n", "Cara: 0\nTIDAK BISA"),
    ("antrean-mixer", "4\n20\n15\n0\n10\n15\n50\n", "Dilayani: 3\nDitolak: 1\nTunggu terlama: 10 menit\nSelesai: menit 70"),
]
