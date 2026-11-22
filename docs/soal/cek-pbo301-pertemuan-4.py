"""Perhitungan terpisah untuk PBO301 pertemuan 4, dipakai oleh periksa.py.

JAWAB[slug](masukan) dihitung dengan Python secara prosedural (tanpa class),
dengan struktur data bawaan — dict, list, set, OrderedDict, Fraction — dan
rumus tertutup bila ada, supaya berbeda dari solusi referensi C++. CONTOH
memuat setiap contoh yang dikutip di pernyataan soal.
"""
import re
from collections import OrderedDict
from fractions import Fraction


def _angka(m):
    return [int(x) for x in m.split()]


def _baris(m):
    return [b for b in m.split("\n") if b != ""]


def _perintah(m, awal=0):
    """Baris-baris perintah sebelum 'selesai', masing-masing sebagai daftar kata."""
    hasil = []
    for b in _baris(m)[awal:]:
        if b == "selesai":
            break
        hasil.append(b.split())
    return hasil


def _jepit(v, a, b):
    return max(a, min(b, v))


def _umur(m):
    nama, u = m.split()
    u = int(u)
    return f"{nama} {u}" if 0 <= u <= 150 else f"Umur tidak sah\n{nama} 0"


def _tak_negatif(m):
    nilai = 0
    for t in m.strip():
        nilai = nilai + 1 if t == "+" else max(0, nilai - 1)
    return str(nilai)


def _speaker(m):
    v = 5
    for t in m.strip():
        v = _jepit(v + (1 if t == "N" else -1), 0, 10)
    return str(v)


def _jam(m):
    j, mn = _angka(m)
    return "%02d:%02d" % (j, mn) if 0 <= j <= 23 and 0 <= mn <= 59 else "Waktu tidak sah\n00:00"


def _akun(m):
    b = _baris(m)
    nama, saldo = b[0].split()[0], int(b[0].split()[1])
    hasil = []
    for kata, x in _perintah(m, 1):
        x = int(x)
        sah = x > 0 and (kata == "setor" or x <= saldo)
        if sah:
            saldo += x if kata == "setor" else -x
        hasil.append(f"Saldo: {saldo}" if sah else "Ditolak")
    return "\n".join(hasil + [f"{nama}: {saldo}"])


def _membaca(m):
    b = _baris(m)
    judul, hal = b[0].split()[0], int(b[0].split()[1])
    dibaca, hasil = 0, []
    for x in _angka(b[2]):
        dibaca = min(hal, dibaca + x)
        hasil.append(f"{judul}: tamat" if dibaca == hal else f"{judul}: {dibaca * 100 // hal}% (sisa {hal - dibaca} halaman)")
    return "\n".join(hasil)


def _sensor(m):
    nilai, hasil = int(_baris(m)[0]), []
    for p in _perintah(m, 1):
        if p[0] == "baca":
            hasil.append(str(nilai))
        else:
            nilai = int(p[1])
    return "\n".join(hasil + [f"Dibaca: {len(hasil)}"])


def _auditor(m):
    isi = [sum(x for x in _angka(b)[1:] if x > 0) for b in _baris(m)[1:]]
    return f"Total: {sum(isi)}\nTerbesar: {max(isi)}"


def _rentang(m):
    bawah, atas, hasil = 0, 100, []
    for sisi, x in (b.split() for b in _baris(m)[1:]):
        x = int(x)
        if sisi == "bawah" and x <= atas:
            bawah = x
            hasil.append("ok")
        elif sisi == "atas" and x >= bawah:
            atas = x
            hasil.append("ok")
        else:
            hasil.append("ditolak")
    return "\n".join(hasil + [f"[{bawah}, {atas}]"])


def _sandi(m):
    b = _baris(m)
    kini, n, hasil = b[0], int(b[1]), []
    for lama, baru in (x.split() for x in b[2:2 + n]):
        if lama != kini:
            hasil.append("Sandi lama salah")
        elif len(baru) < 8 or not re.search(r"\d", baru):
            hasil.append("Sandi baru lemah")
        else:
            kini = baru
            hasil.append("Berhasil")
    return "\n".join(hasil + ["Masuk berhasil" if b[2 + n] == kini else "Masuk gagal"])


def _jumlah_pecahan(m):
    total, hasil = Fraction(0), []
    for a, b in (_angka(x) for x in _baris(m)[1:]):
        if b == 0:
            hasil.append("Dilewati")
        else:
            total += Fraction(a, b)
    return "\n".join(hasil + [f"{total.numerator}/{total.denominator}"])


def _gudang(m):
    stok, hasil = OrderedDict(), []
    for kata, nama, x in _perintah(m):
        x = int(x)
        if kata == "masuk" and x > 0:
            stok[nama] = stok.get(nama, 0) + x
        elif kata == "keluar" and nama in stok and 0 < x <= stok[nama]:
            stok[nama] -= x
        else:
            hasil.append("Ditolak")
    return "\n".join(hasil + [f"{n}: {s}" for n, s in stok.items()])


def _termostat(m):
    target, hasil = 24, []
    for p in _perintah(m):
        if p[0] == "naik":
            target = min(30, target + 1)
        elif p[0] == "turun":
            target = max(16, target - 1)
        else:
            s = int(p[1])
            hasil.append(f"{target}: " + ("mendinginkan" if s > target else "memanaskan" if s < target else "diam"))
    return "\n".join(hasil)


def _antrean(m):
    antre, hasil = [], []
    for p in _perintah(m):
        if p[0] == "masuk":
            if len(antre) == 5:
                hasil.append("Penuh")
            else:
                antre.append(p[1])
        else:
            hasil.append(antre.pop(0) if antre else "Kosong")
    return "\n".join(hasil + [f"Sisa: {len(antre)}"])


def _statistik(m):
    v = _angka(m)[1:]
    if not v:
        return "Belum ada data"
    return f"Min: {min(v)}\nMaks: {max(v)}\nRata-rata: {sum(v) / len(v):.2f}"


def _gembok(m):
    b = _baris(m)
    kode = _angka(b[0])
    cocok = 0
    for k, angka in enumerate(_angka(b[2]), 1):
        cocok = cocok + 1 if angka == kode[cocok] else (1 if angka == kode[0] else 0)
        if cocok == 3:
            return f"Terbuka pada putaran {k}"
    return "Tetap terkunci"


def _mutasi(m):
    saldo, ditarik, riwayat, hasil = int(_baris(m)[0]), 0, [], []
    for kata, x in _perintah(m, 1):
        x = int(x)
        if kata == "setor" and x > 0:
            saldo += x
            riwayat.append(f"+{x}")
        elif kata == "tarik" and 0 < x <= saldo and ditarik + x <= 1000000:
            saldo, ditarik = saldo - x, ditarik + x
            riwayat.append(f"-{x}")
        else:
            hasil.append("Ditolak")
    return "\n".join(hasil + riwayat + [f"Saldo: {saldo}"])


def _xo(m):
    GARIS = [(0, 1, 2), (3, 4, 5), (6, 7, 8), (0, 3, 6), (1, 4, 7), (2, 5, 8), (0, 4, 8), (2, 4, 6)]
    papan, giliran, hasil = ["."] * 9, "X", []

    def pemenang():
        for a, b, c in GARIS:
            if papan[a] != "." and papan[a] == papan[b] == papan[c]:
                return papan[a]
        return None

    for b, k in (_angka(x) for x in _baris(m)[1:]):
        boleh = pemenang() is None and "." in papan and 1 <= b <= 3 and 1 <= k <= 3 and papan[(b - 1) * 3 + k - 1] == "."
        if boleh:
            papan[(b - 1) * 3 + k - 1] = giliran
            giliran = "O" if giliran == "X" else "X"
        else:
            hasil.append("Langkah ditolak")
    hasil += ["".join(papan[p:p + 3]) for p in (0, 3, 6)]
    p = pemenang()
    hasil.append(f"{p} menang" if p else "Seri" if "." not in papan else "Belum selesai")
    return "\n".join(hasil)


def _himpunan(m):
    h, hasil = set(), []
    for kata, x in _perintah(m):
        x = int(x)
        if kata == "tambah":
            if x in h:
                hasil.append("sudah ada")
            h.add(x)
        elif kata == "hapus":
            if x not in h:
                hasil.append("tidak ada")
            h.discard(x)
        else:
            hasil.append("ya" if x in h else "tidak")
    return "\n".join(hasil + ["{" + ", ".join(map(str, sorted(h))) + "}"])


def _uang(m):
    def tulis(sen):
        return f"Rp {sen // 100:,}".replace(",", ".") + f",{sen % 100:02d}"
    awal = _angka(_baris(m)[0])
    sen, hasil = awal[0] * 100 + awal[1], []
    for p in _perintah(m, 1):
        if p[0] == "kali":
            sen *= int(p[1])
        else:
            lain = int(p[1]) * 100 + int(p[2])
            if p[0] == "tambah":
                sen += lain
            elif lain > sen:
                hasil.append("Ditolak")
                continue
            else:
                sen -= lain
        hasil.append(tulis(sen))
    return "\n".join(hasil)


def _kartu(m):
    beredar, berikut, hasil = OrderedDict(), 0, []
    for kata, x in _perintah(m):
        if kata == "buat":
            if len(beredar) == 3:
                hasil.append("Kuota penuh")
            else:
                berikut += 1
                beredar[berikut] = x
                hasil.append(f"Kartu {berikut} untuk {x}")
        elif int(x) in beredar:
            del beredar[int(x)]
            hasil.append(f"Kartu {x} dihapus")
        else:
            hasil.append(f"Tidak ada kartu {x}")
    return "\n".join(hasil + [f"{n} {p}" for n, p in beredar.items()])


def _larik(m):
    data, hasil = [0] * int(_baris(m)[0]), []
    for p in _perintah(m, 1):
        if p[0] == "ukuran":
            n = int(p[1])
            data = (data + [0] * n)[:n]
            continue
        i = int(p[1])
        if not 0 <= i < len(data):
            hasil.append("Indeks di luar batas")
        elif p[0] == "atur":
            data[i] = int(p[2])
        else:
            hasil.append(str(data[i]))
    return "\n".join(hasil + [" ".join(["Isi:"] + [str(x) for x in data])])


def _atm(m):
    pin, saldo = _angka(_baris(m)[0])
    salah, blokir, masuk, hasil = 0, False, False, []
    for p in _perintah(m, 1):
        if p[0] == "masuk":
            if blokir:
                hasil.append("Kartu diblokir")
            elif masuk:
                hasil.append("Sudah masuk")
            elif int(p[1]) == pin:
                salah, masuk = 0, True
                hasil.append("Selamat datang")
            else:
                salah += 1
                blokir = salah == 3
                hasil.append("Kartu diblokir" if blokir else "PIN salah")
        elif not masuk:
            hasil.append("Belum masuk")
        elif p[0] == "tarik":
            x = int(p[1])
            if x <= 0 or x % 50000 or x > saldo:
                hasil.append("Ditolak")
            else:
                saldo -= x
                hasil.append(f"Sisa saldo {saldo}")
        elif p[0] == "ganti":
            lama, baru = int(p[1]), int(p[2])
            if lama != pin:
                hasil.append("PIN lama salah")
            elif not 1000 <= baru <= 9999 or baru == pin:
                hasil.append("PIN baru tidak sah")
            else:
                pin = baru
                hasil.append("PIN diganti")
        else:
            masuk = False
            hasil.append("Sampai jumpa")
    return "\n".join(hasil)


def _ruang(m):
    pesanan, hasil = {}, []   # mulai -> akhir
    for p in _perintah(m):
        if p[0] == "pesan":
            a, b = int(p[1]), int(p[2])
            if a < 0 or b > 24 or a >= b:
                hasil.append("Tidak sah")
            elif any(set(range(a, b)) & set(range(x, y)) for x, y in pesanan.items()):
                hasil.append("Bentrok")
            else:
                pesanan[a] = b
                hasil.append("Dipesan")
        elif int(p[1]) in pesanan:
            del pesanan[int(p[1])]
            hasil.append("Dibatalkan")
        else:
            hasil.append("Tidak ada")
    return "\n".join(hasil + ([f"{a}-{pesanan[a]}" for a in sorted(pesanan)] or ["Kosong"]))


def _lift(m):
    b = _baris(m)
    n, awal = _angka(b[0])
    diminta, hasil = [], []
    for l in _angka(b[2]):
        if 1 <= l <= n and l != awal and l not in diminta:
            diminta.append(l)
        else:
            hasil.append(f"Ditolak {l}")
    atas = sorted(l for l in diminta if l > awal)
    bawah = sorted((l for l in diminta if l < awal), reverse=True)
    langkah = 0
    if atas:
        langkah += atas[-1] - awal
    if bawah:
        langkah += (atas[-1] if atas else awal) - bawah[-1]
    return "\n".join(hasil + [" ".join(["Urutan:"] + [str(l) for l in atas + bawah]), f"Langkah: {langkah}"])


def _lru(m):
    cache, hasil = OrderedDict(), []   # urutan: paling lama → paling baru
    for p in _perintah(m):
        k = int(p[1])
        if p[0] == "taruh":
            if k not in cache and len(cache) == 3:
                buang, _ = cache.popitem(last=False)
                hasil.append(f"Buang {buang}")
            cache[k] = int(p[2])
            cache.move_to_end(k)
        elif k in cache:
            cache.move_to_end(k)
            hasil.append(str(cache[k]))
        else:
            hasil.append("tidak ada")
    return "\n".join(hasil + [" ".join(["Isi:"] + [f"{k}={v}" for k, v in reversed(cache.items())])])


JAWAB = {
    "kotak-terlindung": lambda m: (lambda s: f"{s} {s * s}")(int(m) if int(m) > 0 else 1),
    "umur-valid": _umur,
    "penghitung-tak-negatif": _tak_negatif,
    "nilai-terbatas": lambda m: " ".join(str(_jepit(x, 0, 100)) for x in _angka(m)[1:]),
    "pin-kartu": lambda m: "\n".join("benar" if t == _angka(m)[0] else "salah" for t in _angka(m)[1:]),
    "volume-speaker": _speaker,
    "jam-valid": _jam,
    "persegi-baca-saja": lambda m: (lambda p, l: f"{p} x {l}\nLuas: {p * l}\nKeliling: {2 * (p + l)}")(*_angka(m)),
    "akun-bank": _akun,
    "kemajuan-membaca": _membaca,
    "sensor-mutable": _sensor,
    "pecahan-sama": lambda m: (lambda a, b, c, d: "sama" if Fraction(a, b) == Fraction(c, d) else "beda")(*_angka(m)),
    "auditor-brankas": _auditor,
    "rentang-terjaga": _rentang,
    "warna-heks": lambda m: "#%02X%02X%02X" % tuple(_jepit(v, 0, 255) for v in _angka(m)),
    "ganti-sandi": _sandi,
    "jumlah-pecahan": _jumlah_pecahan,
    "stok-gudang": _gudang,
    "termostat": _termostat,
    "antrean-terbatas": _antrean,
    "statistik-terlindung": _statistik,
    "gembok-kombinasi": _gembok,
    "mutasi-rekening": _mutasi,
    "papan-xo": _xo,
    "himpunan-bilangan": _himpunan,
    "kelas-uang": _uang,
    "pabrik-kartu": _kartu,
    "larik-aman": _larik,
    "kartu-atm": _atm,
    "pesan-ruang": _ruang,
    "lift": _lift,
    "cache-lru": _lru,
}

CONTOH = [
    ("kotak-terlindung", "5\n", "5 25"), ("kotak-terlindung", "-7\n", "1 1"),
    ("umur-valid", "Budi 20\n", "Budi 20"), ("umur-valid", "Citra -3\n", "Umur tidak sah\nCitra 0"),
    ("penghitung-tak-negatif", "-+--++\n", "2"),
    ("nilai-terbatas", "4\n85 -3 120 100\n", "85 0 100 100"),
    ("pin-kartu", "4321\n1234 4321 1111\n", "salah\nbenar\nsalah"),
    ("volume-speaker", "NNNNNNNT\n", "9"),
    ("jam-valid", "9 5\n", "09:05"), ("jam-valid", "12 60\n", "Waktu tidak sah\n00:00"),
    ("persegi-baca-saja", "4 6\n", "4 x 6\nLuas: 24\nKeliling: 20"),
    ("akun-bank", "Budi 100000\ntarik 150000\nsetor -5000\nsetor 25000\ntarik 125000\nselesai\n", "Ditolak\nDitolak\nSaldo: 125000\nSaldo: 0\nBudi: 0"),
    ("kemajuan-membaca", "Fisika 200\n3\n50 120 90\n", "Fisika: 25% (sisa 150 halaman)\nFisika: 85% (sisa 30 halaman)\nFisika: tamat"),
    ("sensor-mutable", "20\nbaca\nubah 35\nbaca\nbaca\nselesai\n", "20\n35\n35\nDibaca: 3"),
    ("pecahan-sama", "1 2\n3 6\n", "sama"), ("pecahan-sama", "2 3\n3 4\n", "beda"),
    ("auditor-brankas", "2\n2 5000 3000\n3 10000 -2000 1000\n", "Total: 19000\nTerbesar: 11000"),
    ("rentang-terjaga", "4\natas 50\nbawah 70\nbawah 50\natas 10\n", "ok\nditolak\nok\nditolak\n[50, 50]"),
    ("warna-heks", "255 128 0\n", "#FF8000"), ("warna-heks", "300 -20 10\n", "#FF000A"),
    ("ganti-sandi", "rahasia123\n3\nsalah999x kuat12345\nrahasia123 pendek1\nrahasia123 sandibaru7\nsandibaru7\n", "Sandi lama salah\nSandi baru lemah\nBerhasil\nMasuk berhasil"),
    ("jumlah-pecahan", "4\n1 2\n1 -3\n5 0\n1 6\n", "Dilewati\n1/3"),
    ("stok-gudang", "masuk kopi 10\nkeluar teh 2\nkeluar kopi 15\nmasuk gula 5\nkeluar kopi 4\nselesai\n", "Ditolak\nDitolak\nkopi: 6\ngula: 5"),
    ("termostat", "naik\nruang 27\nturun\nturun\nruang 23\nruang 20\nselesai\n", "25: mendinginkan\n23: diam\n23: memanaskan"),
    ("antrean-terbatas", "keluar\nmasuk 7\nmasuk 9\nkeluar\nselesai\n", "Kosong\n7\nSisa: 1"),
    ("statistik-terlindung", "3\n4 -2 7\n", "Min: -2\nMaks: 7\nRata-rata: 3.00"), ("statistik-terlindung", "0\n\n", "Belum ada data"),
    ("gembok-kombinasi", "3 3 7\n4\n3 3 3 7\n", "Tetap terkunci"), ("gembok-kombinasi", "4 1 9\n5\n2 4 1 9 5\n", "Terbuka pada putaran 4"),
    ("mutasi-rekening", "2000000\ntarik 700000\ntarik 400000\nsetor 100000\ntarik 300000\nselesai\n", "Ditolak\n-700000\n+100000\n-300000\nSaldo: 1100000"),
    ("papan-xo", "6\n1 1\n2 1\n1 2\n2 2\n1 3\n3 3\n", "Langkah ditolak\nXXX\nOO.\n...\nX menang"),
    ("himpunan-bilangan", "tambah 7\ntambah 3\ntambah 7\npunya 3\nhapus 5\ntambah 1\nselesai\n", "sudah ada\nya\ntidak ada\n{1, 3, 7}"),
    ("kelas-uang", "12500 50\ntambah 1000000 75\nkurang 2000000 0\nkali 3\nselesai\n", "Rp 1.012.501,25\nDitolak\nRp 3.037.503,75"),
    ("pabrik-kartu", "buat Andi\nbuat Budi\nbuat Citra\nbuat Dewi\nhapus 2\nbuat Dewi\nhapus 2\nselesai\n",
     "Kartu 1 untuk Andi\nKartu 2 untuk Budi\nKartu 3 untuk Citra\nKuota penuh\nKartu 2 dihapus\nKartu 4 untuk Dewi\nTidak ada kartu 2\n1 Andi\n3 Citra\n4 Dewi"),
    ("larik-aman", "3\natur 1 5\natur 3 9\nukuran 5\natur 3 9\nambil 1\nambil -1\nselesai\n", "Indeks di luar batas\n5\nIndeks di luar batas\nIsi: 0 5 0 9 0"),
    ("kartu-atm", "1234 500000\ntarik 100000\nmasuk 1111\nmasuk 1234\ntarik 75000\ntarik 200000\nganti 1234 99\nkeluar\nselesai\n",
     "Belum masuk\nPIN salah\nSelamat datang\nDitolak\nSisa saldo 300000\nPIN baru tidak sah\nSampai jumpa"),
    ("pesan-ruang", "pesan 13 15\npesan 9 11\npesan 10 12\npesan 11 13\npesan 16 16\nbatal 9\nbatal 9\nselesai\n", "Dipesan\nDipesan\nBentrok\nDipesan\nTidak sah\nDibatalkan\nTidak ada\n11-13\n13-15"),
    ("lift", "10 4\n6\n2 9 4 6 9 12\n", "Ditolak 4\nDitolak 9\nDitolak 12\nUrutan: 6 9 2\nLangkah: 12"),
    ("cache-lru", "taruh 1 10\ntaruh 2 20\ntaruh 3 30\nambil 1\ntaruh 4 40\nambil 2\nselesai\n", "10\nBuang 2\ntidak ada\nIsi: 4=40 1=10 3=30"),
]
