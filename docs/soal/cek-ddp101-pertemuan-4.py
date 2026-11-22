"""Perhitungan terpisah untuk DDP101 pertemuan 4, dipakai oleh periksa.py.

JAWAB[slug](masukan) mengembalikan keluaran yang benar, dihitung dengan Python
dan dengan cara yang berbeda dari solusi referensi C++ (pustaka standar,
tabel, sorted, datetime), supaya kesalahan yang sama tidak terulang di dua
tempat. CONTOH memuat setiap contoh yang dikutip di pernyataan soal.
"""
import calendar
import datetime


def _angka(m):
    return [int(x) for x in m.split()]


def _terbilang(n):
    satuan = ["nol", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas"]
    if n < 12:
        return satuan[n]
    if n < 20:
        return satuan[n - 10] + " belas"
    if n < 100:
        return satuan[n // 10] + " puluh" + ("" if n % 10 == 0 else " " + satuan[n % 10])
    if n < 200:
        return "seratus" + ("" if n == 100 else " " + _terbilang(n - 100))
    return satuan[n // 100] + " ratus" + ("" if n % 100 == 0 else " " + _terbilang(n % 100))


def _romawi(n):
    hasil = ""
    for nilai, lambang in [(1000, "M"), (900, "CM"), (500, "D"), (400, "CD"), (100, "C"), (90, "XC"), (50, "L"),
                           (40, "XL"), (10, "X"), (9, "IX"), (5, "V"), (4, "IV"), (1, "I")]:
        while n >= nilai:
            hasil += lambang
            n -= nilai
    return hasil


def _kalkulator(m):
    a, op, b = m.split()
    a, b = int(a), int(b)
    if op == "/":
        return "tidak bisa dibagi nol" if b == 0 else str(a // b)
    return str({"+": a + b, "-": a - b, "*": a * b}[op])


def _segitiga(m):
    a, b, c = sorted(_angka(m))
    if a + b <= c:
        return "bukan segitiga"
    return {1: "sama sisi", 2: "sama kaki", 3: "sembarang"}[len({a, b, c})]


def _imt(m):
    b, t = _angka(m)
    x = b * 10000 / (t * t)
    kategori = "kurus" if x < 18.5 else "normal" if x < 25 else "gemuk" if x < 30 else "obesitas"
    return f"{x:.1f} {kategori}"


def _jam12(m):
    j, mnt = _angka(m)
    return datetime.time(j, mnt).strftime("%I:%M %p").lstrip("0")


def _pajak(m):
    p = int(m)
    juta = sum(tarif * max(0, min(p, atas) - bawah) for bawah, atas, tarif in [(0, 60, 5), (60, 250, 15), (250, 10 ** 9, 25)])
    return str(juta * 10)  # juta × persen = 10 ribu rupiah


def _titik(m):
    x1, y1, x2, y2, px, py = _angka(m)
    def letak(p, a, b):  # 0 luar, 1 tepi, 2 dalam, pada satu sumbu
        a, b = min(a, b), max(a, b)
        return 0 if p < a or p > b else 1 if p in (a, b) else 2
    return ["di luar", "di tepi", "di dalam"][min(letak(px, x1, x2), letak(py, y1, y2))]


def _bentrok(m):
    a, b, c, d = _angka(m)
    sama = len(set(range(a, b)) & set(range(c, d)))
    return f"bentrok {sama} menit" if sama else "aman"


def _suit(m):
    a, b = m.split()
    urutan = ["batu", "gunting", "kertas"]  # tiap pilihan mengalahkan yang sesudahnya
    return ["seri", "pemain 1", "pemain 2"][(urutan.index(b) - urutan.index(a)) % 3]


def _ongkir(m):
    g, z = _angka(m)
    kg = -(-g // 1000)
    ongkos = kg * {1: 8000, 2: 12000, 3: 20000}[z]
    return str(ongkos * 9 // 10 if kg > 10 else ongkos)


def _parkir(m):
    jenis, lama = _angka(m)
    pertama, lanjut, maks = {1: (2000, 1000, 8000), 2: (5000, 3000, 30000)}[jenis]
    return str(min(maks, pertama + lanjut * (lama - 1)))


def _selisih(m):
    h1, b1, h2, b2, t = _angka(m)
    return str((datetime.date(t, b2, h2) - datetime.date(t, b1, h1)).days)


def _dadu(m):
    v = sorted(_angka(m))
    if v[0] == v[2]:
        return "kembar tiga 100"
    if len(set(v)) == 2:
        return f"sepasang {20 + v[1]}"  # nilai tengah selalu bagian dari pasangan
    if v == [v[0], v[0] + 1, v[0] + 2]:
        return "berurutan 60"
    return f"biasa {v[2]}"


def _karakter(m):
    c = m.strip()
    return "huruf besar" if c.isupper() else "huruf kecil" if c.islower() else "angka" if c.isdigit() else "simbol"


JAWAB = {
    "tanda-bilangan": lambda m: "positif" if int(m) > 0 else "negatif" if int(m) < 0 else "nol",
    "genap-atau-ganjil": lambda m: ["genap", "ganjil"][int(m) & 1],
    "lebih-besar": lambda m: str(max(_angka(m))),
    "lulus-atau-tidak": lambda m: "TIDAK LULUS" if int(m) in range(0, 60) else "LULUS",
    "nilai-mutlak": lambda m: m.strip().lstrip("-"),
    "tiga-sama": lambda m: "sama" if len(set(_angka(m))) == 1 else "beda",
    "habis-dibagi": lambda m: "ya" if divmod(*_angka(m))[1] == 0 else "tidak",
    "diskon-belanja": lambda m: str(int(m) * 90 // 100 if int(m) >= 100000 else int(m)),
    "terbesar-dari-tiga": lambda m: str(sorted(_angka(m))[-1]),
    "wujud-air": lambda m: "es" if int(m) <= 0 else "uap" if int(m) >= 100 else "air",
    "nilai-huruf": lambda m: next(h for batas, h in [(85, "A"), (70, "B"), (55, "C"), (40, "D"), (0, "E")] if int(m) >= batas),
    "nama-hari": lambda m: ["Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu", "Minggu"][int(m) - 1],
    "tahun-kabisat": lambda m: "kabisat" if calendar.isleap(int(m)) else "bukan kabisat",
    "vokal-konsonan": lambda m: "vokal" if m.strip() in "aiueo" else "konsonan",
    "kuadran": lambda m: str({(True, True): 1, (False, True): 2, (False, False): 3, (True, False): 4}[tuple(v > 0 for v in _angka(m))]),
    "jenis-karakter": _karakter,
    "kalkulator-operator": _kalkulator,
    "jenis-segitiga": _segitiga,
    "hari-dalam-bulan": lambda m: str(calendar.monthrange(_angka(m)[1], _angka(m)[0])[1]),
    "urutkan-tiga": lambda m: " ".join(str(v) for v in sorted(_angka(m))),
    "kategori-imt": _imt,
    "jam-dua-belas": _jam12,
    "pajak-penghasilan": _pajak,
    "titik-dan-persegi-panjang": _titik,
    "jadwal-bentrok": _bentrok,
    "batu-gunting-kertas": _suit,
    "ongkos-kirim": _ongkir,
    "tarif-parkir": _parkir,
    "terbilang": lambda m: _terbilang(int(m)),
    "angka-romawi": lambda m: _romawi(int(m)),
    "selisih-hari": _selisih,
    "tiga-dadu": _dadu,
}

# (slug, masukan, keluaran yang dikutip di pernyataan)
CONTOH = [
    ("lebih-besar", "7 -3\n", "7"),
    ("nilai-mutlak", "-8\n", "8"), ("nilai-mutlak", "8\n", "8"),
    ("tiga-sama", "5 5 5\n", "sama"), ("tiga-sama", "5 5 6\n", "beda"),
    ("habis-dibagi", "12 4\n", "ya"), ("habis-dibagi", "13 4\n", "tidak"),
    ("diskon-belanja", "150000\n", "135000"), ("diskon-belanja", "80000\n", "80000"),
    ("terbesar-dari-tiga", "4 9 -2\n", "9"),
    ("tahun-kabisat", "2024\n", "kabisat"), ("tahun-kabisat", "2000\n", "kabisat"),
    ("tahun-kabisat", "2023\n", "bukan kabisat"), ("tahun-kabisat", "1900\n", "bukan kabisat"),
    ("kalkulator-operator", "12 + 5\n", "17"), ("kalkulator-operator", "17 / 5\n", "3"), ("kalkulator-operator", "7 - 10\n", "-3"),
    ("kalkulator-operator", "5 / 0\n", "tidak bisa dibagi nol"),
    ("jenis-segitiga", "3 4 5\n", "sembarang"), ("jenis-segitiga", "5 5 8\n", "sama kaki"), ("jenis-segitiga", "1 2 3\n", "bukan segitiga"),
    ("hari-dalam-bulan", "2 2024\n", "29"), ("hari-dalam-bulan", "2 1900\n", "28"), ("hari-dalam-bulan", "11 2025\n", "30"),
    ("urutkan-tiga", "8 -1 5\n", "-1 5 8"),
    ("kategori-imt", "70 175\n", "22.9 normal"),
    ("jam-dua-belas", "0 5\n", "12:05 AM"), ("jam-dua-belas", "9 30\n", "9:30 AM"), ("jam-dua-belas", "15 7\n", "3:07 PM"),
    ("jam-dua-belas", "0 0\n", "12:00 AM"), ("jam-dua-belas", "12 0\n", "12:00 PM"),
    ("pajak-penghasilan", "100\n", "9000"), ("pajak-penghasilan", "300\n", "44000"),
    ("titik-dan-persegi-panjang", "5 1 1 4\n3 2\n", "di dalam"), ("titik-dan-persegi-panjang", "5 1 1 4\n1 3\n", "di tepi"),
    ("titik-dan-persegi-panjang", "5 1 1 4\n6 2\n", "di luar"),
    ("jadwal-bentrok", "480 600\n540 660\n", "bentrok 60 menit"), ("jadwal-bentrok", "480 600\n600 700\n", "aman"),
    ("batu-gunting-kertas", "kertas batu\n", "pemain 1"), ("batu-gunting-kertas", "gunting batu\n", "pemain 2"),
    ("ongkos-kirim", "2300 2\n", "36000"), ("ongkos-kirim", "10500 1\n", "79200"), ("ongkos-kirim", "2000 1\n", "16000"),
    ("tarif-parkir", "2 4\n", "14000"), ("tarif-parkir", "1 10\n", "8000"),
    ("terbilang", "7\n", "tujuh"), ("terbilang", "15\n", "lima belas"), ("terbilang", "40\n", "empat puluh"),
    ("terbilang", "111\n", "seratus sebelas"), ("terbilang", "205\n", "dua ratus lima"),
    ("terbilang", "999\n", "sembilan ratus sembilan puluh sembilan"), ("terbilang", "0\n", "nol"), ("terbilang", "10\n", "sepuluh"),
    ("terbilang", "12\n", "dua belas"), ("terbilang", "35\n", "tiga puluh lima"), ("terbilang", "200\n", "dua ratus"),
    ("angka-romawi", "4\n", "IV"), ("angka-romawi", "49\n", "XLIX"), ("angka-romawi", "1994\n", "MCMXCIV"), ("angka-romawi", "2026\n", "MMXXVI"),
    ("angka-romawi", "40\n", "XL"), ("angka-romawi", "90\n", "XC"), ("angka-romawi", "400\n", "CD"), ("angka-romawi", "900\n", "CM"),
    ("selisih-hari", "1 1 1 2 2025\n", "31"), ("selisih-hari", "17 8 10 11 2026\n", "85"),
    ("selisih-hari", "28 2 1 3 2024\n", "2"), ("selisih-hari", "28 2 1 3 2025\n", "1"),
    ("tiga-dadu", "5 5 5\n", "kembar tiga 100"), ("tiga-dadu", "4 2 3\n", "berurutan 60"),
    ("tiga-dadu", "6 2 6\n", "sepasang 26"), ("tiga-dadu", "1 6 3\n", "biasa 6"),
]
