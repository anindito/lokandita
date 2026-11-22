"""Perhitungan terpisah untuk PBO301 pertemuan 2, dipakai oleh periksa.py.

JAWAB[slug](masukan) dihitung dengan Python secara prosedural — tanpa class —
dan dengan cara yang berbeda dari solusi referensi C++ (datetime, fractions,
Counter, math.factorial, aritmetika modular). CONTOH memuat setiap contoh yang
dikutip di pernyataan soal.
"""
import datetime
import math
from collections import Counter
from fractions import Fraction


def _angka(m):
    return [int(x) for x in m.split()]


def _baris(m):
    return [b for b in m.split("\n") if b != ""]


def _tangki(m):
    b = _baris(m)
    kap, isi, hasil = int(b[0]), 0, []
    for perintah in b[2:]:
        kata, x = perintah.split()
        x = int(x)
        pindah = min(x, kap - isi) if kata == "isi" else -min(x, isi)
        isi += pindah
        hasil.append(str(abs(pindah)))
    return "\n".join(hasil + [f"Sisa: {isi}"])


def _dua_titik(m):
    ax, ay, bx, by, dx, dy = _angka(m)
    return f"{abs(ax - bx) + abs(ay - by)} {abs(ax + dx - bx) + abs(ay + dy - by)}"


def _waktu(m):
    j, mn, n = _angka(m)
    t = datetime.datetime(2000, 1, 1, j, mn) + datetime.timedelta(minutes=n)
    return t.strftime("%H:%M")


def _prajurit(m):
    b = _baris(m)
    nama, sehat = b[0].split()[0], int(b[0].split()[1])
    hasil = []
    for luka in _angka(b[2]):
        if sehat == 0:
            break
        sehat = max(0, sehat - luka)
        hasil.append(f"Kesehatan: {sehat}")
    return "\n".join(hasil + [f"{nama} {'gugur' if sehat == 0 else 'selamat'}"])


def _tiket(m):
    nama = _baris(m)[1].split()
    return "\n".join([f"Tiket {k}: {n}" for k, n in enumerate(nama, 1)] + [f"Terjual: {len(nama)}"])


def _kendaraan(m):
    b = _baris(m)
    bensin, konsumsi = _angka(b[0])
    odo, hasil = 0, []
    for km in _angka(b[2]):
        if km * konsumsi <= bensin:
            bensin, odo = bensin - km * konsumsi, odo + km
            hasil.append("jalan")
        else:
            hasil.append("bensin tidak cukup")
    return "\n".join(hasil + [f"Odometer: {odo} km, bensin: {bensin} ml"])


def _lulus(m):
    b = _baris(m)
    hasil = []
    for baris in b[1:]:
        nama, t, u, a = baris.split()
        akhir = (3 * int(t) + 3 * int(u) + 4 * int(a)) // 10
        if akhir >= 60:
            hasil.append(f"{nama} {akhir}")
    return "\n".join(hasil + [f"Lulus: {len(hasil)} dari {len(b) - 1}"])


def _terluas(m):
    luas = [p * l for p, l in (_angka(x) for x in _baris(m)[1:])]
    return f"{luas.index(max(luas)) + 1} {max(luas)}"


def _senjata(m):
    b = _baris(m)
    nama, kap = b[0].split()[0], int(b[0].split()[1])
    peluru, hasil = kap, []
    for p in b[1]:
        if p == "R":
            peluru = kap
            hasil.append(f"Isi ulang: {peluru}")
        elif peluru > 0:
            peluru -= 1
            hasil.append(f"Dor! sisa {peluru}")
        else:
            hasil.append("Klik! peluru habis")
    return "\n".join(hasil + [f"{nama}: {peluru}/{kap}"])


def _vektor(m):
    ax, ay, bx, by, k = _angka(m)
    a, b = complex(ax, ay), complex(bx, by)
    c = a + k * b
    return f"{int(c.real)} {int(c.imag)}\n{int((a * b.conjugate()).real)}"


def _detak(m):
    j, mn, d, n = _angka(m)
    total = (j * 3600 + mn * 60 + d + n) % 86400
    return "%02d:%02d:%02d" % (total // 3600, total // 60 % 60, total % 60)


def _gudang(m):
    data = [(b.split()[0], int(b.split()[1]) * int(b.split()[2])) for b in _baris(m)[1:]]
    return f"Total: {sum(v for _, v in data)}\nTerbesar: {max(data, key=lambda d: d[1])[0]}"


def _matriks(m):
    a, b, c, d, n = _angka(m)
    x = [[1, 0], [0, 1]]
    for _ in range(n):
        x = [[x[0][0] * a + x[0][1] * c, x[0][0] * b + x[0][1] * d], [x[1][0] * a + x[1][1] * c, x[1][0] * b + x[1][1] * d]]
    return f"{x[0][0]} {x[0][1]}\n{x[1][0]} {x[1][1]}\n{(a * d - b * c) ** n}"


def _tumpukan(m):
    t, hasil = [], []
    for b in _baris(m):
        if b == "selesai":
            break
        if b.startswith("dorong"):
            t.append(int(b.split()[1]))
        elif not t:
            hasil.append("kosong")
        else:
            hasil.append(str(t.pop() if b == "ambil" else t[-1]))
    return "\n".join(hasil + [f"Isi: {len(t)}"])


def _polinom(m):
    b = _baris(m)
    koef, x = _angka(b[1]), int(b[2])
    d = len(koef) - 1
    p = sum(a * x ** (d - k) for k, a in enumerate(koef))
    t = sum(a * (d - k) * x ** (d - k - 1) for k, a in enumerate(koef) if d - k > 0)
    return f"{p}\n{t}"


def _hari(m):
    h, b, t = _angka(m)
    return ["Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu", "Minggu"][datetime.date(t, b, h).weekday()]


def _bank(m):
    b = _baris(m)
    saldo, hasil = _angka(b[1]), []
    for perintah in b[2:]:
        k = perintah.split()
        if k[0] == "selesai":
            break
        if k[0] == "setor":
            saldo[int(k[1]) - 1] += int(k[2])
        elif k[0] == "tarik":
            if int(k[2]) > saldo[int(k[1]) - 1]:
                hasil.append("Gagal")
            else:
                saldo[int(k[1]) - 1] -= int(k[2])
        else:
            a, c, x = int(k[1]) - 1, int(k[2]) - 1, int(k[3])
            if x > saldo[a]:
                hasil.append("Gagal")
            else:
                saldo[a], saldo[c] = saldo[a] - x, saldo[c] + x
    return "\n".join(hasil + [f"Rekening {k}: {s}" for k, s in enumerate(saldo, 1)])


def _robot(m):
    pos, arah = 0j, 1j  # utara = +i
    for p in m.strip():
        if p == "M":
            pos += arah
        else:
            arah *= 1j if p == "L" else -1j
    nama = {1j: "utara", 1: "timur", -1j: "selatan", -1: "barat"}[arah]
    return f"{int(pos.real)} {int(pos.imag)} {nama}"


def _poker(m):
    kartu = [(int(b.split()[0]), b.split()[1]) for b in _baris(m)]
    nilai = sorted(n for n, _ in kartu)
    pola = sorted(Counter(nilai).values(), reverse=True)
    sewarna = len({j for _, j in kartu}) == 1
    urut = nilai == list(range(nilai[0], nilai[0] + 5))
    if urut and sewarna:
        return "straight flush"
    if pola == [4, 1]:
        return "four of a kind"
    if pola == [3, 2]:
        return "full house"
    if sewarna:
        return "flush"
    if urut:
        return "straight"
    return {(3, 1, 1): "three of a kind", (2, 2, 1): "two pair", (2, 1, 1, 1): "one pair"}.get(tuple(pola), "high card")


def _penjadwal(m):
    b = _baris(m)
    n, q = _angka(b[0])
    antre = [[x.split()[0], int(x.split()[1])] for x in b[1:]]
    waktu, hasil = 0, []
    while antre:
        sisa = []
        for nama, perlu in antre:
            jatah = min(q, perlu)
            waktu += jatah
            if perlu == jatah:
                hasil.append(f"{nama} {waktu}")
            else:
                sisa.append([nama, perlu - jatah])
        antre = sisa
    return "\n".join(hasil)


def _mesin(m):
    b = _baris(m)
    k = int(b[0])
    slot = [[x.split()[0], int(x.split()[1]), int(x.split()[2])] for x in b[1:1 + k]]
    saldo, dapat, hasil = 0, 0, []
    for perintah in b[1 + k:]:
        kata = perintah.split()
        if kata[0] == "selesai":
            break
        if kata[0] == "uang":
            saldo += int(kata[1])
        elif kata[0] == "batal":
            hasil.append(f"Kembali: {saldo}")
            saldo = 0
        else:
            s = slot[int(kata[1]) - 1]
            if s[2] == 0:
                hasil.append("Habis")
            elif saldo < s[1]:
                hasil.append("Uang kurang")
            else:
                s[2], saldo, dapat = s[2] - 1, saldo - s[1], dapat + s[1]
                hasil.append(f"Keluar: {s[0]}")
    return "\n".join(hasil + [f"Pendapatan: {dapat}"])


JAWAB = {
    "kelas-persegi": lambda m: f"{int(m) ** 2} {4 * int(m)}",
    "kelas-balok": lambda m: (lambda p, l, t: f"{p * l * t} {2 * (p * l + p * t + l * t)}")(*_angka(m)),
    "perkenalan": lambda m: "Halo, saya {} angkatan {}.".format(*m.split()),
    "penghitung": lambda m: str(m.count("+") - m.count("-")),
    "kelas-suhu": lambda m: f"{int(Fraction(int(m) * 9, 5)) + 32} {int(m) + 273}",
    "jarak-manhattan": lambda m: str(sum(abs(v) for v in _angka(m))),
    "saklar-lampu": lambda m: "menyala" if int(m) % 2 else "padam",
    "persegi-atau-bukan": lambda m: (lambda p, l: f"{'persegi' if p == l else 'bukan persegi'} {p * l}")(*_angka(m)),
    "tangki-air": _tangki,
    "jarak-dua-titik": _dua_titik,
    "waktu-tambah-menit": _waktu,
    "kelas-pecahan": lambda m: (lambda f: f"{f.numerator}/{f.denominator}")(Fraction(*_angka(m))),
    "prajurit": _prajurit,
    "rantai-method": lambda m: (lambda a, b, c, d: str((a + b) * c + d))(*_angka(m)),
    "tiket-statis": _tiket,
    "kendaraan-bensin": _kendaraan,
    "daftar-lulus": _lulus,
    "persegi-panjang-terluas": _terluas,
    "senjata": _senjata,
    "operasi-vektor": _vektor,
    "jam-berdetak": _detak,
    "nilai-gudang": _gudang,
    "matriks-pangkat": _matriks,
    "kelas-tumpukan": _tumpukan,
    "kelas-polinom": _polinom,
    "nama-hari-tanggal": _hari,
    "bank-mini": _bank,
    "robot-penjelajah": _robot,
    "tangan-poker": _poker,
    "penjadwal-bergilir": _penjadwal,
    "faktorial-besar": lambda m: str(math.factorial(int(m))),
    "mesin-penjual": _mesin,
}

CONTOH = [
    ("kelas-persegi", "5\n", "25 20"),
    ("kelas-balok", "2 3 4\n", "24 52"),
    ("perkenalan", "Citra 2025\n", "Halo, saya Citra angkatan 2025."),
    ("penghitung", "++-+\n", "2"),
    ("kelas-suhu", "25\n", "77 298"),
    ("jarak-manhattan", "3 -4\n", "7"),
    ("saklar-lampu", "3\n", "menyala"), ("saklar-lampu", "0\n", "padam"),
    ("persegi-atau-bukan", "5 5\n", "persegi 25"), ("persegi-atau-bukan", "4 6\n", "bukan persegi 24"),
    ("tangki-air", "100\n4\nisi 70\nisi 50\npakai 120\npakai 5\n", "70\n30\n100\n0\nSisa: 0"),
    ("jarak-dua-titik", "1 2\n4 6\n3 0\n", "7 4"),
    ("waktu-tambah-menit", "9 45 30\n", "10:15"), ("waktu-tambah-menit", "23 50 20\n", "00:10"),
    ("kelas-pecahan", "12 18\n", "2/3"), ("kelas-pecahan", "10 5\n", "2/1"),
    ("prajurit", "Budi 50\n3\n20 40 10\n", "Kesehatan: 30\nKesehatan: 0\nBudi gugur"),
    ("rantai-method", "2 3 4 1\n", "21"),
    ("tiket-statis", "2\nBudi Citra\n", "Tiket 1: Budi\nTiket 2: Citra\nTerjual: 2"),
    ("kendaraan-bensin", "10000 80\n3\n50 100 60\n", "jalan\nbensin tidak cukup\njalan\nOdometer: 110 km, bensin: 1200 ml"),
    ("daftar-lulus", "2\nAndi 80 70 60\nBudi 40 50 55\n", "Andi 69\nLulus: 1 dari 2"),
    ("persegi-panjang-terluas", "3\n4 6\n3 9\n5 5\n", "2 27"),
    ("senjata", "Pindad 2\nTTTRT\n", "Dor! sisa 1\nDor! sisa 0\nKlik! peluru habis\nIsi ulang: 2\nDor! sisa 1\nPindad: 1/2"),
    ("operasi-vektor", "1 2\n3 -1\n2\n", "7 0\n1"),
    ("jam-berdetak", "9 59 58 3\n", "10:00:01"), ("jam-berdetak", "23 59 59 1\n", "00:00:00"),
    ("nilai-gudang", "3\nBeras 12000 10\nGula 15000 4\nKopi 40000 3\n", "Total: 300000\nTerbesar: Beras"),
    ("matriks-pangkat", "1 1 1 0\n5\n", "8 5\n5 3\n-1"),
    ("kelas-tumpukan", "dorong 5\ndorong 8\npuncak\nambil\nambil\nambil\nselesai\n", "8\n8\n5\nkosong\nIsi: 0"),
    ("kelas-polinom", "3\n2 0 -1 5\n2\n", "19\n23"),
    ("nama-hari-tanggal", "17 8 1945\n", "Jumat"), ("nama-hari-tanggal", "1 1 2000\n", "Sabtu"), ("nama-hari-tanggal", "1 1 1900\n", "Senin"),
    ("bank-mini", "2\n100000 50000\ntarik 2 80000\ntransfer 1 2 30000\nselesai\n", "Gagal\nRekening 1: 70000\nRekening 2: 80000"),
    ("robot-penjelajah", "MMRMLM\n", "1 3 utara"), ("robot-penjelajah", "RRM\n", "0 -1 selatan"),
    ("tangan-poker", "10 H\n11 H\n12 H\n13 H\n14 H\n", "straight flush"), ("tangan-poker", "5 S\n5 D\n9 C\n9 H\n2 S\n", "two pair"),
    ("tangan-poker", "14 S\n2 D\n3 C\n4 H\n5 S\n", "high card"),
    ("penjadwal-bergilir", "3 3\nP1 5\nP2 2\nP3 4\n", "P2 5\nP1 10\nP3 11"),
    ("faktorial-besar", "5\n", "120"), ("faktorial-besar", "25\n", "15511210043330985984000000"), ("faktorial-besar", "0\n", "1"),
    ("mesin-penjual", "1\nTeh 3000 1\nuang 2000\npilih 1\nuang 2000\npilih 1\npilih 1\nbatal\nselesai\n", "Uang kurang\nKeluar: Teh\nHabis\nKembali: 1000\nPendapatan: 3000"),
]
