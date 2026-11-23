"""Perhitungan terpisah untuk TS-PY pertemuan 3, dipakai oleh periksa.py.

Solusi referensi memakai if / elif / else atas float. Di sini JAWAB[slug]
menempuh jalan lain: semua besaran dihitung sebagai bilangan rasional eksak
(fractions.Fraction; masukan desimal dibaca sebagai teks), kelas ditentukan
dengan tabel batas dan bisect atau dengan mengurutkan, tanggal dengan modul
datetime, dan pemenang dengan min() atas kunci. Pembulatan keluaran dilakukan
atas pecahan eksak; generator menjamin hasil tidak pernah berada di batas
pembulatan atau di batas kelas pecahan.
CONTOH memuat setiap contoh yang dikutip di pernyataan soal.
"""
from bisect import bisect_left, bisect_right
from datetime import date
from decimal import ROUND_HALF_EVEN, Decimal
from fractions import Fraction as F


def _i(m):
    return [int(x) for x in m.split()]


def f(x, d):
    """Pecahan eksak -> teks dengan d angka di belakang titik."""
    x = F(x)
    return str((Decimal(x.numerator) / Decimal(x.denominator)).quantize(Decimal(1).scaleb(-d), rounding=ROUND_HALF_EVEN))


def _muatan(m):
    muatan, kap = _i(m)
    return "BOLEH JALAN" if kap - muatan >= 0 else f"KELEBIHAN {muatan - kap} kg"


def _diskon(m):
    n, h = _i(m)
    return f"Total: Rp {int(n * h * (F(95, 100) if n > 99 else 1))}"


def _tegangan(m):
    p, a, s = _i(m)
    t = F(p * 1000, a)
    return f"Tegangan: {f(t, 2)} MPa\n" + ("TIDAK AMAN" if p * 1000 > s * a else "AMAN")


def _slump(m):
    s, lo, hi = _i(m)
    return ["TERLALU KENTAL", "SESUAI", "TERLALU ENCER"][(s >= lo) + (s > hi)]


def _galian(m):
    a, b = _i(m)
    return "RATA" if a == b else f"{'GALIAN' if a > b else 'TIMBUNAN'} {max(a, b) - min(a, b)} cm"


def _lajur(m):
    v = _i(m)
    k = v.index(max(v))
    return f"Lajur {k + 1}: {v[k]} kendaraan"


def _sewa(m):
    j, tarif = _i(m)
    return f"Jam dibayar: {max(j, 8)}\nBiaya: Rp {max(j, 8) * tarif}"


def _lereng(m):
    h, d = _i(m)
    p = F(100 * h, d)
    kelas = ["DATAR", "LANDAI", "AGAK CURAM", "CURAM", "SANGAT CURAM"][bisect_right([8, 15, 25, 45], p)]
    return f"{f(p, 1)}% {kelas}"


def _terima(m):
    fc, *u = _i(m)
    ok = sum(u) >= 3 * fc and all(2 * x >= 2 * fc - 7 for x in u)
    return f"Rata-rata: {f(F(sum(u), 3), 2)} MPa\n" + ("DITERIMA" if ok else "DITOLAK")


def _air(m):
    n = _i(m)[0]
    blok = [(10, 2500), (10, 4000), (10 ** 9, 6500)]
    biaya = 10000
    for isi, tarif in blok:
        pakai = min(n, isi)
        biaya += pakai * tarif
        n -= pakai
    return f"Tagihan: Rp {biaya}"


def _reynolds(m):
    v, d = m.split()
    re = F(v) * F(int(d), 1000) * 1000000
    return f"Re = {f(re, 0)}\n" + ("LAMINAR" if re < 2000 else "TURBULEN" if re > 4000 else "TRANSISI")


def _lembur(m):
    j, u = _i(m)
    total = sum(u * k for jam in range(1, j + 1) for k in [F(1) if jam <= 40 else F(3, 2) if jam <= 48 else F(2)])
    return f"Upah: Rp {int(total)}"


def _segitiga(m):
    q, s, p = sorted(_i(m))
    if p >= q + s:
        return "BUKAN SEGITIGA"
    beda = q * q + s * s - p * p
    return "SIKU-SIKU" if beda == 0 else "LANCIP" if beda > 0 else "TUMPUL"


def _azimut(m):
    a = _i(m)[0]
    if a in (90, 180):                      # batas kuadran ikut kuadran sebelumnya
        return f"{'U' if a == 90 else 'S'} {90 if a == 90 else 0} T"
    kuadran = a // 90
    sudut = a % 90 if kuadran in (0, 2) else 90 - a % 90
    return f"{'USSU'[kuadran]} {sudut} {'TTBB'[kuadran]}"


def _rho(m):
    a, b, d, fy = _i(m)
    rho = F(a, b * d)
    return f"rho = {f(rho, 4)}\n" + ("KURANG" if rho < F(14, 10 * fy) else "BERLEBIH" if rho > F(25, 1000) else "MEMENUHI")


def _tanah(m):
    lolos, tertahan, ll, pi = _i(m)
    if lolos > 49:
        di_atas_garis = 100 * pi >= 73 * (ll - 20)
        if ll > 49:
            return "CH" if di_atas_garis else "MH"
        return "CL" if di_atas_garis and pi >= 8 else "ML"
    jenis = "KERIKIL" if 2 * tertahan > 100 - lolos else "PASIR"
    return jenis + (" BERSIH" if lolos <= 4 else " DENGAN BUTIR HALUS")


def _saluran(m):
    b, h, tinggi, q = _i(m)
    v = F(q, 1000) / (F(b, 100) * F(h, 100))
    status = "MENGENDAP" if v < F(6, 10) else "MENGGERUS" if v > 3 else "KECEPATAN AMAN"
    return f"v = {f(v, 2)} m/detik\n{status}\n" + ("JAGAAN CUKUP" if 4 * (tinggi - h) >= h else "JAGAAN KURANG")


def _kombinasi(m):
    d, l, w = _i(m)
    u = [F(14 * d, 10), F(12 * d + 16 * l, 10), F(12 * d + 10 * l + 16 * w, 10)]
    k = u.index(max(u))
    return f"Kombinasi {k + 1}\n{f(u[k], 1)} kN"


def _balok(m):
    bentang, p1, a1, p2, a2, x = m.split()
    bentang, x = F(bentang), F(x)
    beban = [(F(p1), F(a1)), (F(p2), F(a2))]
    ra = sum(p * (bentang - a) for p, a in beban) / bentang
    kiri = [(p, a) for p, a in beban if a < x]          # beban di sebelah kiri potongan
    v = ra - sum(p for p, _ in kiri)
    mo = ra * x - sum(p * (x - a) for p, a in kiri)
    return f"V = {f(v, 2)} kN\nM = {f(mo, 2)} kNm"


def _umur(m):
    t1, b1, t2, b2 = _i(m)
    n = (date(2023, b2, t2) - date(2023, b1, t1)).days   # 2023 bukan tahun kabisat
    return f"Umur: {n} hari\n" + ("UJI STANDAR" if n in (7, 14, 28) else "TERLALU MUDA" if n < 7 else "DI LUAR JADWAL")


def _reduksi(m):
    b, d, luas, fc, fy = _i(m)
    a = F(luas * fy * 100, 85 * fc * b)
    beta = F(85, 100) - F(5, 100) * F(min(max(fc, 28), 56) - 28, 7)
    c = a / beta
    et = F(3, 1000) * (d - c) / c
    if et >= F(5, 1000):
        kelas, phi = "TERKENDALI TARIK", F(9, 10)
    elif et <= F(2, 1000):
        kelas, phi = "TERKENDALI TEKAN", F(65, 100)
    else:
        kelas, phi = "TRANSISI", F(65, 100) + (et - F(2, 1000)) * F(250, 3)
    mn = luas * fy * (d - a / 2) / 1000000
    return f"a = {f(a, 2)} mm\net = {f(et, 5)}\n{kelas}\nphi = {f(phi, 3)}\nphiMn = {f(phi * mn, 2)} kNm"


def _tender(m):
    v = _i(m)
    sah = [(v[k], v[k + 1], k // 2 + 1) for k in (1, 3, 5) if v[k + 1] <= v[0]]
    if not sah:
        return "TENDER GAGAL"
    harga, _, nomor = min(sah)
    return f"Pemenang: Penawar {nomor}\nHarga: {harga} juta"


JAWAB = {
    "cek-kuat-tekan": lambda m: "MEMENUHI" if _i(m)[0] - _i(m)[1] >= 0 else "TIDAK MEMENUHI",
    "hari-hujan": lambda m: "Hari hujan" if F(m.strip()) >= 1 else "Hari kering",
    "elevasi-tertinggi": lambda m: str(max(_i(m))),
    "sisi-patok": lambda m: ["KANAN", "KIRI"][_i(m)[0] & 1],
    "cek-muatan-truk": _muatan,
    "selisih-bacaan": lambda m: f"Selisih: {abs(_i(m)[0] - _i(m)[1])} mm",
    "diskon-semen": _diskon,
    "lolos-saringan": lambda m: "BERBUTIR HALUS" if F(m.strip()) > 50 else "BERBUTIR KASAR",
    "tegangan-aman": _tegangan,
    "cek-slump": _slump,
    "galian-timbunan": _galian,
    "kelas-jalan": lambda m: ["LINGKUNGAN", "LOKAL", "KOLEKTOR", "ARTERI"][bisect_right([500, 3000, 20000], _i(m)[0])],
    "intensitas-hujan": lambda m: ["RINGAN", "SEDANG", "LEBAT", "SANGAT LEBAT", "EKSTREM"][bisect_left([20, 50, 100, 150], _i(m)[0])],
    "lajur-terpadat": _lajur,
    "sewa-alat": _sewa,
    "shift-kerja": lambda m: "MALAM" if (_i(m)[0] - 7) % 24 >= 16 else "PAGI" if (_i(m)[0] - 7) % 24 < 8 else "SORE",
    "kelas-lereng": _lereng,
    "nilai-tengah-kubus": lambda m: str(sorted(_i(m))[1]),
    "terima-beton": _terima,
    "tarif-air": _air,
    "bilangan-reynolds": _reynolds,
    "upah-lembur": _lembur,
    "segitiga-patok": _segitiga,
    "kuadran-azimut": _azimut,
    "rasio-tulangan": _rho,
    "klasifikasi-tanah": _tanah,
    "cek-saluran": _saluran,
    "kombinasi-beban": _kombinasi,
    "gaya-dalam-balok": _balok,
    "umur-benda-uji": _umur,
    "faktor-reduksi": _reduksi,
    "pemenang-tender": _tender,
}

CONTOH = [
    ("cek-kuat-tekan", "32\n30\n", "MEMENUHI"),
    ("cek-kuat-tekan", "28\n30\n", "TIDAK MEMENUHI"),
    ("hari-hujan", "12.5\n", "Hari hujan"),
    ("hari-hujan", "0.4\n", "Hari kering"),
    ("elevasi-tertinggi", "1250\n1310\n", "1310"),
    ("sisi-patok", "17\n", "KIRI"),
    ("sisi-patok", "40\n", "KANAN"),
    ("cek-muatan-truk", "8200\n8000\n", "KELEBIHAN 200 kg"),
    ("cek-muatan-truk", "7500\n8000\n", "BOLEH JALAN"),
    ("selisih-bacaan", "1482\n1507\n", "Selisih: 25 mm"),
    ("selisih-bacaan", "1507\n1482\n", "Selisih: 25 mm"),
    ("diskon-semen", "120\n65000\n", "Total: Rp 7410000"),
    ("diskon-semen", "80\n65000\n", "Total: Rp 5200000"),
    ("lolos-saringan", "62.5\n", "BERBUTIR HALUS"),
    ("lolos-saringan", "50.0\n", "BERBUTIR KASAR"),
    ("tegangan-aman", "900\n90000\n12\n", "Tegangan: 10.00 MPa\nAMAN"),
    ("tegangan-aman", "1500\n90000\n12\n", "Tegangan: 16.67 MPa\nTIDAK AMAN"),
    ("cek-slump", "14\n8\n12\n", "TERLALU ENCER"),
    ("cek-slump", "8\n8\n12\n", "SESUAI"),
    ("galian-timbunan", "1250\n1180\n", "GALIAN 70 cm"),
    ("galian-timbunan", "1100\n1180\n", "TIMBUNAN 80 cm"),
    ("kelas-jalan", "3000\n", "KOLEKTOR"),
    ("kelas-jalan", "499\n", "LINGKUNGAN"),
    ("intensitas-hujan", "50\n", "SEDANG"),
    ("intensitas-hujan", "51\n", "LEBAT"),
    ("lajur-terpadat", "310\n431\n298\n", "Lajur 2: 431 kendaraan"),
    ("sewa-alat", "5\n450000\n", "Jam dibayar: 8\nBiaya: Rp 3600000"),
    ("shift-kerja", "14\n", "PAGI"),
    ("shift-kerja", "23\n", "MALAM"),
    ("kelas-lereng", "6\n50\n", "12.0% LANDAI"),
    ("nilai-tengah-kubus", "310\n270\n290\n", "290"),
    ("nilai-tengah-kubus", "300\n300\n250\n", "300"),
    ("terima-beton", "25\n26\n24\n27\n", "Rata-rata: 25.67 MPa\nDITERIMA"),
    ("terima-beton", "25\n30\n21\n29\n", "Rata-rata: 26.67 MPa\nDITOLAK"),
    ("tarif-air", "25\n", "Tagihan: Rp 107500"),
    ("bilangan-reynolds", "0.15\n20\n", "Re = 3000\nTRANSISI"),
    ("upah-lembur", "50\n20000\n", "Upah: Rp 1120000"),
    ("segitiga-patok", "300\n500\n400\n", "SIKU-SIKU"),
    ("segitiga-patok", "2\n6\n3\n", "BUKAN SEGITIGA"),
    ("kuadran-azimut", "135\n", "S 45 T"),
    ("kuadran-azimut", "300\n", "U 60 B"),
    ("kuadran-azimut", "180\n", "S 0 T"),
    ("rasio-tulangan", "1200\n300\n500\n400\n", "rho = 0.0080\nMEMENUHI"),
    ("klasifikasi-tanah", "60\n0\n40\n18\n", "CL"),
    ("klasifikasi-tanah", "3\n60\n20\n2\n", "KERIKIL BERSIH"),
    ("cek-saluran", "80\n50\n65\n600\n", "v = 1.50 m/detik\nKECEPATAN AMAN\nJAGAAN CUKUP"),
    ("kombinasi-beban", "100\n50\n20\n", "Kombinasi 3\n202.0 kN"),
    ("gaya-dalam-balok", "10\n20\n2\n30\n6\n4.0\n", "V = 8.00 kN\nM = 72.00 kNm"),
    ("umur-benda-uji", "25\n2\n25\n3\n", "Umur: 28 hari\nUJI STANDAR"),
    ("umur-benda-uji", "30\n4\n3\n5\n", "Umur: 3 hari\nTERLALU MUDA"),
    ("faktor-reduksi", "300\n500\n1500\n30\n420\n", "a = 82.35 mm\net = 0.01222\nTERKENDALI TARIK\nphi = 0.900\nphiMn = 260.15 kNm"),
    ("pemenang-tender", "120\n950\n110\n900\n130\n950\n100\n", "Pemenang: Penawar 3\nHarga: 950 juta"),
]
