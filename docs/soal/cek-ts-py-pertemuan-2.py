"""Perhitungan terpisah untuk TS-PY pertemuan 2, dipakai oleh periksa.py.

Solusi referensi menghitung dengan float dan modul math. Di sini JAWAB[slug]
memakai modul decimal dengan 40 angka penting: masukan dibaca sebagai teks
desimal (bukan pecahan biner), akar dengan Decimal.sqrt, π sebagai tetapan
desimal, pangkat pecahan lewat ln/exp desimal, dan fungsi trigonometri lewat
bilangan kompleks (cmath) atau atan2 — jalan yang berbeda dari math.sin/cos/
atan milik solusi referensi. Pembulatan keluaran dengan quantize
ROUND_HALF_EVEN; generator soal menjamin hasil tidak pernah berada di batas
pembulatan, jadi aturan pembulatannya tidak menentukan.
CONTOH memuat setiap contoh yang dikutip di pernyataan soal.
"""
import cmath
import math
from decimal import ROUND_CEILING, ROUND_DOWN, ROUND_HALF_EVEN, Decimal as D, getcontext

getcontext().prec = 40
PI = D("3.14159265358979323846264338327950288419716939937510")


def _b(m):
    return [D(x) for x in m.split()]


def f(x, d):
    return str(D(x).quantize(D(1).scaleb(-d), rounding=ROUND_HALF_EVEN))


def atas(x):
    return int(D(x).to_integral_value(rounding=ROUND_CEILING))


def pangkat(x, y):
    return (D(y) * D(x).ln()).exp()


def _format_panjang(m):
    return f"Panjang: {f(_b(m)[0], 2)} m"


def _rincian(m):
    nama, jumlah, harga = m.split()
    return f"{nama}: {jumlah} x {f(D(harga), 2)} = {f(D(jumlah) * D(harga), 2)}"


def _kuitansi(m):
    nama, jumlah, harga = m.split()
    kanan = lambda teks, lebar: " " * (lebar - len(teks)) + teks
    return (nama + " " * (12 - len(nama)) + kanan(jumlah, 6) + kanan(f(D(harga), 2), 14) + "\n"
            + "Total" + " " * 13 + kanan(f(D(jumlah) * D(harga), 2), 14))


def _skala(m):
    peta, n = _b(m)
    cm = peta * n
    return f"{f(cm / 100, 1)} m\n{f(cm / 100000, 3)} km"


def _lereng(m):
    h, d = m.split()
    return f"{f(D(repr(math.degrees(math.atan2(float(h), float(d))))), 1)} derajat"


def _tegangan(m):
    x = _b(m)[0] * D("0.0980665")
    return f"{f(x, 3)} MPa\n{f(x * 1000, 1)} kPa"


def _timbunan(m):
    v, s, g, k = _b(m)
    asli = v / s
    lepas = asli * g
    return f"{f(asli, 2)}\n{f(lepas, 2)}\n{atas(lepas / k)}"


def _mononobe(R, t):
    return R / 24 * pangkat(D(24) / t, D(2) / 3)


def _manning(m):
    b, h, n, S = _b(m)
    A = b * h
    R = A / (b + h + h)
    Q = A * pangkat(R, D(2) / 3) * S.sqrt() / n
    return f"A = {f(A, 2)} m2\nR = {f(R, 3)} m\nQ = {f(Q, 3)} m3/detik"


def _lentur(m):
    q, P, L, b, h = _b(m)
    M = q * L * L / 8 + P * L / 4
    W = b * h * h / 6
    return f"M = {f(M, 2)} kNm\nTegangan = {f(M * 10 ** 6 / W, 2)} MPa"


def _kerucut(m):
    r, t, k = _b(m)
    V = PI * r * r * t / 3
    W = V * D("1.6")
    return f"{f(V, 2)} m3\n{f(W, 2)} ton\n{atas(W / k)}"


def _polar(m):
    x, y, d, a = m.split()
    # Azimut dari utara searah jarum jam: titik = utara diputar; dihitung sebagai bilangan kompleks.
    z = cmath.rect(float(d), math.pi / 2 - float(a) * math.pi / 180)
    return f"{f(D(x) + D(repr(z.real)), 3)}\n{f(D(y) + D(repr(z.imag)), 3)}"


def _henti(m):
    V, t, fr = _b(m)
    a = D("0.278") * V * t
    b = V * V / (254 * fr)
    return f"Reaksi: {f(a, 1)} m\nRem: {f(b, 1)} m\nHenti: {f(a + b, 1)} m"


def _tulangan(m):
    M, fy, d, Dia = _b(m)
    As = M * 10 ** 6 / (D("0.81") * fy * d)
    a1 = PI * Dia * Dia / 4
    n = atas(As / a1)
    return f"As perlu = {f(As, 1)} mm2\nJumlah = {n} D{int(Dia)}\nAs terpasang = {f(n * a1, 1)} mm2"


def _rasional(m):
    R, tc, C, A, h, v = _b(m)
    I = _mononobe(R, tc)
    Q = D("0.278") * C * I * A
    return f"I = {f(I, 2)} mm/jam\nQ = {f(Q, 2)} m3/detik\nb = {f(Q / (h * v), 2)} m"


def _heron(m):
    a, b, c, hg = _b(m)
    s = (a + b + c) / 2
    L = (s * (s - a) * (s - b) * (s - c)).sqrt()
    nilai = int((L * hg).quantize(D(1), rounding=ROUND_HALF_EVEN))
    ribuan = []
    while nilai >= 1000:
        ribuan.append(f"{nilai % 1000:03d}")
        nilai //= 1000
    ribuan.append(str(nilai))
    return f"Luas: {f(L, 2)} m2\nNilai: Rp {','.join(reversed(ribuan))}"


def _tempuh(m):
    d, v = _b(m)
    jam = d / v
    detik = int((jam * 3600).quantize(D(1), rounding=ROUND_HALF_EVEN))
    j, sisa = divmod(detik, 3600)
    mn, dt = divmod(sisa, 60)
    return f"{f(jam, 3)} jam\n" + ":".join(str(x).rjust(2, "0") for x in (j, mn, dt))


JAWAB = {
    "format-panjang": _format_panjang,
    "mm-ke-m": lambda m: f"{f(_b(m)[0].scaleb(-3), 3)} m",
    "meter-utuh": lambda m: str(int(_b(m)[0].to_integral_value(rounding=ROUND_DOWN))),
    "km-jam-ke-m-detik": lambda m: f"{f(_b(m)[0] / D('3.6'), 2)} m/detik",
    "keliling-tiang": lambda m: f(PI * _b(m)[0], 2),
    "mpa-ke-kgcm2": lambda m: f"{f(_b(m)[0] * D('10.197'), 1)} kg/cm2",
    "luas-ke-hektar": lambda m: f"{f(_b(m)[0].scaleb(-4), 4)} ha",
    "jam-desimal": lambda m: f"{f(_b(m)[0] / 60, 2)} jam",
    "berat-sendiri-balok": lambda m: f"{f(_b(m)[0] * _b(m)[1] * _b(m)[2] * 24 / 10000, 2)} kN",
    "tekanan-hidrostatik": lambda m: f"{f(D('9.81') * _b(m)[0], 2)} kPa",
    "kaki-kuda-kuda": lambda m: f((_b(m)[0] ** 2 + _b(m)[1] ** 2).sqrt(), 2),
    "rincian-material": _rincian,
    "debit-pipa": lambda m: f"{f(PI * _b(m)[0] ** 2 / 4 * _b(m)[1] / 1000, 2)} liter/detik",     # mm² × m/detik = 10⁻³ liter/detik
    "kadar-air-tanah": lambda m: f"{f((_b(m)[0] - _b(m)[1]) * 100 / _b(m)[1], 1)} %",
    "skala-peta": _skala,
    "sudut-lereng": _lereng,
    "satuan-tegangan": _tegangan,
    "kuat-tekan-silinder": lambda m: f"{f(_b(m)[0] * 1000 * 4 / (PI * 22500), 2)} MPa",
    "timbunan-gembur": _timbunan,
    "intensitas-mononobe": lambda m: f"{f(_mononobe(*_b(m)), 2)} mm/jam",
    "kuitansi-material": _kuitansi,
    "sudut-desimal": lambda m: f((_b(m)[0] * 3600 + _b(m)[1] * 60 + _b(m)[2]) / 3600, 4),
    "lendutan-balok": lambda m: f"{f(5 * _b(m)[0] * (_b(m)[1] * 1000) ** 4 / (384 * _b(m)[2] * _b(m)[3] * 10000), 2)} mm",
    "debit-manning": _manning,
    "tegangan-lentur": _lentur,
    "timbunan-kerucut": _kerucut,
    "koordinat-polar": _polar,
    "jarak-henti": _henti,
    "tulangan-perlu": _tulangan,
    "debit-rasional": _rasional,
    "lahan-segitiga": _heron,
    "waktu-tempuh": _tempuh,
}

CONTOH = [
    ("format-panjang", "12.348\n", "Panjang: 12.35 m"),
    ("mm-ke-m", "2500\n", "2.500 m"),
    ("mm-ke-m", "75\n", "0.075 m"),
    ("meter-utuh", "11.75\n", "11"),
    ("meter-utuh", "3.99\n", "3"),
    ("km-jam-ke-m-detik", "72\n", "20.00 m/detik"),
    ("keliling-tiang", "40\n", "125.66"),
    ("mpa-ke-kgcm2", "25.0\n", "254.9 kg/cm2"),
    ("luas-ke-hektar", "12500\n", "1.2500 ha"),
    ("jam-desimal", "135\n", "2.25 jam"),
    ("berat-sendiri-balok", "30\n50\n6.5\n", "23.40 kN"),
    ("tekanan-hidrostatik", "3.2\n", "31.39 kPa"),
    ("kaki-kuda-kuda", "4.0\n2.5\n", "4.72"),
    ("rincian-material", "Semen\n40\n62500.50\n", "Semen: 40 x 62500.50 = 2500020.00"),
    ("debit-pipa", "150\n1.2\n", "21.21 liter/detik"),
    ("kadar-air-tanah", "1250.5\n1040.0\n", "20.2 %"),
    ("skala-peta", "7.4\n2500\n", "185.0 m\n0.185 km"),
    ("sudut-lereng", "3.0\n4.5\n", "33.7 derajat"),
    ("satuan-tegangan", "250.0\n", "24.517 MPa\n24516.6 kPa"),
    ("kuat-tekan-silinder", "530.0\n", "29.99 MPa"),
    ("timbunan-gembur", "450\n0.85\n1.25\n7\n", "529.41\n661.76\n95"),
    ("intensitas-mononobe", "120\n2.0\n", "26.21 mm/jam"),
    ("kuitansi-material", "Semen\n40\n62500.50\n", "Semen           40      62500.50\nTotal                 2500020.00"),
    ("sudut-desimal", "112\n30\n45\n", "112.5125"),
    ("lendutan-balok", "12.0\n6.0\n25000\n180000\n", "4.50 mm"),
    ("debit-manning", "2.0\n1.2\n0.015\n0.001\n", "A = 2.40 m2\nR = 0.545 m\nQ = 3.378 m3/detik"),
    ("tegangan-lentur", "10.0\n20.0\n6.0\n300\n500\n", "M = 75.00 kNm\nTegangan = 6.00 MPa"),
    ("timbunan-kerucut", "6.0\n4.0\n8\n", "150.80 m3\n241.27 ton\n31"),
    ("koordinat-polar", "1000\n2000\n150.0\n60\n", "1129.904\n2075.000"),
    ("jarak-henti", "60\n2.5\n0.35\n", "Reaksi: 41.7 m\nRem: 40.5 m\nHenti: 82.2 m"),
    ("tulangan-perlu", "85.0\n400\n440\n16\n", "As perlu = 596.2 mm2\nJumlah = 3 D16\nAs terpasang = 603.2 mm2"),
    ("debit-rasional", "100\n1.5\n0.60\n2.4\n1.0\n1.5\n", "I = 26.46 mm/jam\nQ = 10.59 m3/detik\nb = 7.06 m"),
    ("lahan-segitiga", "30.0\n40.0\n50.0\n1250000\n", "Luas: 600.00 m2\nNilai: Rp 750,000,000"),
    ("waktu-tempuh", "87.5\n42\n", "2.083 jam\n02:05:00"),
]
