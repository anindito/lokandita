"""Perhitungan terpisah untuk TS-PY pertemuan 1, dipakai oleh periksa.py.

Solusi referensi soal-soal ini memakai bilangan bulat dan pecahan biner dengan
operator dasar. Di sini JAWAB[slug](masukan) dihitung dengan cara lain:
bilangan rasional eksak (fractions.Fraction) yang baru diubah ke pecahan biner
saat dicetak, satuan diubah ke meter LEBIH DULU (bukan di akhir), pembulatan ke
atas dengan math.ceil atas pecahan eksak, waktu dengan datetime, dan hitungan
"berapa kali muat" dengan simulasi perulangan. CONTOH memuat setiap contoh yang
dikutip di pernyataan soal.
"""
import math
from datetime import datetime, timedelta
from fractions import Fraction as F


def _i(m):
    return [int(x) for x in m.split()]


def _f(x):
    return repr(float(x))


def _papan(m):
    return "Proyek: " + m.rstrip("\n")


def _data(m):
    kode, umur = m.split()
    return f"Kode: {kode}\nUmur: {umur} hari"


def _truk(m):
    v, k = _i(m)
    penuh = 0
    while v >= k:          # simulasi: berangkatkan truk selama masih bisa penuh
        v -= k
        penuh += 1
    return f"Truk penuh: {penuh}\nSisa: {v}"


def _menit(m):
    j, s = divmod(_i(m)[0], 60)
    return f"{j} jam {s} menit"


def _tertukar(m):
    tercatat_belakang, tercatat_muka = _i(m)
    return f"{tercatat_muka} {tercatat_belakang}\n{tercatat_muka - tercatat_belakang}"


def _campuran(m):
    a, b, c, v = _i(m)
    satu_bagian = F(v, a + b + c)
    return "\n".join(_f(satu_bagian * x) for x in (a, b, c))


def _hujan(m):
    d, a = _i(m)
    return f"{d * a}\n{_f(F(d, 1000) * a)}"       # mm → m dulu, lalu × m²


def _rab(m):
    v = _i(m)
    jumlah = sum(v[k] * v[k + 1] for k in (0, 2, 4))
    ppn = math.floor(F(11, 100) * jumlah)
    return f"Jumlah: {jumlah}\nPPN: {ppn}\nTotal: {jumlah + ppn}"


def _keramik(m):
    p, l, s, k = _i(m)
    n = math.ceil(F(p, s)) * math.ceil(F(l, s))
    return f"Keramik: {n}\nDus: {math.ceil(F(n, k))}"


def _sengkang(m):
    L, s, b, h, c, k = _i(m)
    n = len(range(0, L + 1, s))                   # posisi 0, s, 2s, … yang masih di dalam balok
    satu = sum(2 * (x - c - c) for x in (b, h)) + k + k
    return f"Jumlah: {n}\nPanjang satu: {satu} mm\nTotal: {n * satu} mm"


def _ritase(m):
    d, v, muat, kap = _i(m)
    sekali = math.floor(F(d, v) * 60)
    siklus = sekali + sekali + muat
    ritase, terpakai = 0, 0
    while terpakai + siklus <= 480:
        terpakai += siklus
        ritase += 1
    return f"Siklus: {siklus} menit\nRitase: {ritase}\nVolume: {ritase * kap} m3"


def _potong(m):
    p, n = _i(m)
    per, sisa_batang = 0, 12000
    while sisa_batang >= p:                       # potong satu batang sampai tidak cukup lagi
        sisa_batang -= p
        per += 1
    batang = math.ceil(F(n, per))
    return f"Potongan per batang: {per}\nBatang: {batang}\nSisa: {batang * 12000 - n * p} mm"


def _selesai(m):
    j, mn, d = _i(m)
    awal = datetime(2001, 1, 1, j, mn)
    akhir = awal + timedelta(minutes=d)
    return f"{akhir.hour} {akhir.minute}\n{(akhir.date() - awal.date()).days}"


def _galian(m):
    p, l, b, d, t, k = _i(m)
    as_m = F(2 * (p + l))
    galian = as_m * F(b, 100) * F(d, 100)         # semua ke meter dulu
    pasir = as_m * F(b, 100) * F(t, 100)
    return f"{_f(galian)}\n{_f(pasir)}\n{math.ceil(galian / k)}"


def _tangki(m):
    a, i, v = _i(m)
    debit = a * i
    menit = math.ceil(F(v, debit) * 60)
    return f"Debit: {debit} liter/jam\nPenuh: {menit // 60} jam {menit - 60 * (menit // 60)} menit"


def _kolom(m):
    d, t, n = _i(m)
    jari_m = F(d, 200)
    return _f(F(314, 100) * jari_m * jari_m * F(t, 100) * n)


def _interpolasi(m):
    x1, e1, x2, e2, x = _i(m)
    bagian = F(x - x1, x2 - x1)                   # 0 di patok pertama, 1 di patok kedua
    return _f((1 - bagian) * e1 + bagian * e2)


JAWAB = {
    "papan-proyek": _papan,
    "luas-pelat": lambda m: str(math.prod(_i(m))),
    "keliling-lahan": lambda m: str(sum(_i(m) * 2)),
    "hitung-kendaraan": lambda m: f"Total kendaraan: {sum(_i(m))}",
    "sisa-stok-semen": lambda m: "Sisa {} sak".format(_i(m)[0] - _i(m)[1]),
    "kubus-uji": lambda m: str(pow(_i(m)[0], 3)),
    "data-benda-uji": _data,
    "rata-elevasi": lambda m: _f(F(sum(_i(m)), 2)),
    "volume-balok-beton": lambda m: _f(math.prod(F(x, 100) for x in _i(m))),
    "truk-penuh": _truk,
    "menit-ke-jam": _menit,
    "luas-dinding-bersih": lambda m: str(math.prod(_i(m)[:2]) - math.prod(_i(m)[2:])),
    "kemiringan-persen": lambda m: _f(F(_i(m)[0], _i(m)[1]) * 100),
    "tegangan-kolom": lambda m: _f(F(_i(m)[0] * 1000, _i(m)[1] ** 2)),
    "debit-saluran": lambda m: _f(math.prod(F(x, 100) for x in _i(m)) * 1000),     # m³/detik × 1000 liter
    "bacaan-tertukar": _tertukar,
    "kebutuhan-bata": lambda m: str(math.ceil(F(math.prod(_i(m)[:2]), math.prod(_i(m)[2:])))),
    "berat-tulangan": lambda m: _f(F(_i(m)[0] ** 2, 162) * _i(m)[1] * _i(m)[2]),
    "campuran-beton": _campuran,
    "volume-hujan-atap": _hujan,
    "smp-lalu-lintas": lambda m: _f(F(1, 4) * _i(m)[0] + _i(m)[1] + F(5, 2) * _i(m)[2]),
    "rab-sederhana": _rab,
    "volume-saluran": lambda m: _f(F(_i(m)[0] + _i(m)[1], 200) * F(_i(m)[2], 100) * _i(m)[3]),
    "keramik-lantai": _keramik,
    "sengkang-balok": _sengkang,
    "ritase-truk": _ritase,
    "kolom-bulat": _kolom,
    "interpolasi-elevasi": _interpolasi,
    "potong-besi": _potong,
    "selesai-pengecoran": _selesai,
    "galian-pondasi": _galian,
    "tangki-hujan": _tangki,
}

CONTOH = [
    ("papan-proyek", "Gedung Kuliah Terpadu\n", "Proyek: Gedung Kuliah Terpadu"),
    ("luas-pelat", "6\n4\n", "24"),
    ("keliling-lahan", "30\n20\n", "100"),
    ("hitung-kendaraan", "120\n45\n8\n", "Total kendaraan: 173"),
    ("sisa-stok-semen", "120\n45\n", "Sisa 75 sak"),
    ("kubus-uji", "15\n", "3375"),
    ("data-benda-uji", "BU-7\n28\n", "Kode: BU-7\nUmur: 28 hari"),
    ("rata-elevasi", "120\n135\n", "127.5"),
    ("volume-balok-beton", "400\n30\n50\n", "0.6"),
    ("truk-penuh", "100\n7\n", "Truk penuh: 14\nSisa: 2"),
    ("menit-ke-jam", "135\n", "2 jam 15 menit"),
    ("menit-ke-jam", "45\n", "0 jam 45 menit"),
    ("luas-dinding-bersih", "400\n300\n90\n210\n", "101100"),
    ("kemiringan-persen", "50\n2000\n", "2.5"),
    ("tegangan-kolom", "900\n300\n", "10.0"),
    ("debit-saluran", "80\n50\n120\n", "480.0"),
    ("bacaan-tertukar", "1250\n1875\n", "1875 1250\n625"),
    ("kebutuhan-bata", "300\n300\n22\n7\n", "585"),
    ("berat-tulangan", "10\n12\n20\n", "148.148148"),
    ("campuran-beton", "1\n2\n3\n300\n", "50.0\n100.0\n150.0"),
    ("volume-hujan-atap", "25\n120\n", "3000\n3.0"),
    ("smp-lalu-lintas", "200\n150\n20\n", "250.0"),
    ("rab-sederhana", "10\n850\n25\n120\n4\n300\n", "Jumlah: 12700\nPPN: 1397\nTotal: 14097"),
    ("volume-saluran", "120\n60\n80\n50\n", "36.0"),
    ("keramik-lantai", "410\n300\n40\n6\n", "Keramik: 88\nDus: 15"),
    ("sengkang-balok", "4000\n150\n250\n400\n30\n60\n", "Jumlah: 27\nPanjang satu: 1180 mm\nTotal: 31860 mm"),
    ("ritase-truk", "12\n40\n15\n7\n", "Siklus: 51 menit\nRitase: 9\nVolume: 63 m3"),
    ("kolom-bulat", "40\n350\n8\n", "3.5168"),
    ("interpolasi-elevasi", "0\n1200\n50\n1300\n20\n", "1240.0"),
    ("potong-besi", "2750\n10\n", "Potongan per batang: 4\nBatang: 3\nSisa: 8500 mm"),
    ("selesai-pengecoran", "22\n30\n200\n", "1 50\n1"),
    ("galian-pondasi", "10\n8\n80\n100\n10\n6\n", "28.8\n2.88\n5"),
    ("tangki-hujan", "100\n12\n2000\n", "Debit: 1200 liter/jam\nPenuh: 1 jam 40 menit"),
]
