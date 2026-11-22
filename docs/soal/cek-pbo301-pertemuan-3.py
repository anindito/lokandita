"""Perhitungan terpisah untuk PBO301 pertemuan 3, dipakai oleh periksa.py.

Solusi referensi C++ membiarkan konstruktor dan destruktor mencetak sendiri.
Di sini urutan yang sama DITURUNKAN dari aturan bahasanya dengan simulasi
biasa di Python (daftar, tumpukan, penghitung) — tanpa class dan tanpa
mekanisme objek apa pun — sehingga kesalahan pemahaman aturan dan kesalahan
penulisan C++ tidak saling menutupi. CONTOH memuat setiap contoh yang dikutip
di pernyataan soal.
"""
import itertools


def _angka(m):
    return [int(x) for x in m.split()]


def _baris(m):
    return [b for b in m.split("\n") if b != ""]


def _gabung(*bagian):
    return "\n".join(bagian)


def _titik(m):
    v = _angka(m)
    if v[0] == 0:
        return "Titik asal dibuat\n0"
    return f"Titik ({v[1]}, {v[2]}) dibuat\n{v[1] + v[2]}"


def _waktu(m):
    v = _angka(m)[1:] + [0]
    return "%02d:%02d" % (v[0], v[1])


def _prajurit(n):
    return _gabung(*[f"Prajurit {k} siap" for k in range(1, n + 1)], "Regu lengkap",
                   *[f"Prajurit {k} bubar" for k in range(n, 0, -1)])


def _delegasi(m):
    v = _angka(m)
    if v[0] == 1:
        return f"Konstruktor dua parameter\nKonstruktor satu parameter\nLuas: {v[1] * v[1]}"
    return f"Konstruktor dua parameter\nLuas: {v[1] * v[2]}"


def _perulangan(m):
    nama = _baris(m)[1].split()
    tengah = [x for n in nama[1:] for x in (f"Buat {n}", f"Hapus {n}")]
    return _gabung(f"Buat {nama[0]}", *tengah, "Perulangan selesai", f"Hapus {nama[0]}")


def _salin_dalam(m):
    b = _baris(m)
    asli = _angka(b[1])
    p, v = _angka(b[2])
    salinan = list(asli)
    salinan[p] = v
    return " ".join(map(str, asli)) + "\n" + " ".join(map(str, salinan))


def _penugasan(m):
    b = _baris(m)
    a, x = _angka(b[1]), int(b[4])
    return " ".join(map(str, [x] + a[1:])) + "\n" + " ".join(map(str, a))


def _rekening(m):
    b = _baris(m)
    persen = _angka(b[0])[1]
    return "\n".join(f"{n}: {s + s * persen // 100}" for n, s in (_angka(x) for x in b[1:]))


def _peti(m):
    tumpuk, dibuat, hasil = [], 0, []
    for p in m.strip():
        if p == "B":
            dibuat += 1
            tumpuk.append(dibuat)
            hasil.append(f"Buat {dibuat}")
        elif tumpuk:
            hasil.append(f"Hapus {tumpuk.pop()}")
        else:
            hasil.append("Kosong")
    hasil.append("Akhir perintah")
    hasil += [f"Hapus {k}" for k in reversed(tumpuk)]
    return "\n".join(hasil)


def _kunci(m):
    b = _baris(m)
    nama = b[0].split()[0]
    hasil, jumlah = [f"Kunci {nama} diambil"], 0
    for x in _angka(b[1]):
        if x < 0:
            return _gabung(*hasil, "Data rusak", f"Kunci {nama} dilepas", "Gagal")
        hasil.append(f"Baca {x}")
        jumlah += x
    return _gabung(*hasil, f"Kunci {nama} dilepas", f"Jumlah: {jumlah}")


def _tumbuh(m):
    data = _isi(m)
    hasil, kap = [], 1
    for banyak in range(len(data)):
        if banyak == kap:
            hasil.append(f"Tumbuh {kap} -> {kap * 2}")
            kap *= 2
    return _gabung(*hasil, "Isi: " + " ".join(map(str, data)), f"Bebas {kap}")


def _isi(m):
    return _angka(m.split("\n", 1)[1])


def _transpos(m):
    g = [_angka(b) for b in _baris(m)[1:]]
    t = list(zip(*g))
    return _gabung(*[" ".join(map(str, b)) for b in t], f"Sudut: {t[0][0]} {g[0][0] + 1000}")


def _bocor(m):
    hidup, hasil = [], []
    for b in _baris(m):
        if b == "selesai":
            break
        perintah, nama = b.split()
        if perintah == "buat":
            hidup.append(nama)
            hasil.append("+" + nama)
        elif nama in hidup:
            hidup.remove(nama)
            hasil.append("-" + nama)
        else:
            hasil.append("Tidak ada " + nama)
    return _gabung(*hasil, f"Bocor: {len(hidup)}", *["-" + n for n in hidup], "Hidup: 0")


def _cadangan(m):
    utama, cadangan, hasil = [], [], []
    for b in _baris(m):
        k = b.split()
        if k[0] == "selesai":
            break
        if k[0] == "dorong":
            utama.append(k[1])
        elif k[0] == "ambil":
            hasil.append(utama.pop() if utama else "kosong")
        elif k[0] == "simpan":
            cadangan = list(utama)
        else:
            utama = list(cadangan)
    return _gabung(*hasil, " ".join(["Isi:"] + utama))


def _statis(n):
    hasil = ["Buat global", "main mulai"]
    for k in range(1, n + 1):
        if k == 1:
            hasil.append("Buat statis")
        hasil += ["Buat lokal", f"Panggilan {k}", "Hapus lokal"]
    hasil.append("main selesai")
    if n > 0:
        hasil.append("Hapus statis")
    return _gabung(*hasil, "Hapus global")


def _urut(m):
    v = _isi(m)
    inversi = sum(1 for a, b in itertools.combinations(v, 2) if a > b)
    return _gabung(" ".join(map(str, sorted(v))), f"Salin: {inversi}", f"Tugas: {2 * inversi}")


def _cakupan(m):
    blok, hasil = [], []
    for c in m.strip():
        if c == "{":
            blok.append([])
        elif c == "}":
            hasil += [f"Hapus {n}" for n in reversed(blok.pop())]
        else:
            blok[-1].append(c)
            hasil.append(f"Buat {c}")
    return "\n".join(hasil)


def _batal(m):
    kini, riwayat, ulang, hasil = "", [], [], []
    for b in _baris(m):
        k = b.split()
        if k[0] == "selesai":
            break
        if k[0] == "tulis":
            riwayat.append(kini)
            ulang = []
            kini = (kini + " " + k[1]).strip()
        elif k[0] == "batal":
            if riwayat:
                ulang.append(kini)
                kini = riwayat.pop()
            else:
                hasil.append("Tidak bisa batal")
        elif k[0] == "ulang":
            if ulang:
                riwayat.append(kini)
                kini = ulang.pop()
            else:
                hasil.append("Tidak bisa ulang")
        else:
            hasil.append(kini or "(kosong)")
    return "\n".join(hasil)


def _pelacak(m):
    daftar, dibuat, hasil = [], 0, []   # tiap objek: [id, nilai]
    for b in _baris(m):
        k = b.split()
        if k[0] == "selesai":
            break
        if k[0] == "buat":
            dibuat += 1
            daftar.append([dibuat, int(k[1])])
            hasil.append(f"+{dibuat}")
        elif k[0] == "salin":
            asal = daftar[int(k[1]) - 1]
            dibuat += 1
            daftar.append([dibuat, asal[1]])
            hasil.append(f"+{dibuat} salin {asal[0]}")
        elif k[0] == "tugas":
            a, c = daftar[int(k[1]) - 1], daftar[int(k[2]) - 1]
            a[1] = c[1]
            hasil.append(f"{a[0]} = {c[0]}")
        elif k[0] == "hapus":
            hasil.append(f"-{daftar.pop()[0]}" if daftar else "kosong")
        else:
            hasil.append(" ".join(["Isi:"] + [f"{i}:{v}" for i, v in daftar]))
    return _gabung(*hasil, *[f"-{i}" for i, _ in reversed(daftar)])


def _dua(pola):
    """Keluaran dari pola seperti 'Ba Bb Hb Ha' untuk nama-nama di masukan."""
    def f(m):
        nama = dict(zip("abc", m.split()))
        return "\n".join(("Buat " if p[0] == "B" else "Hapus ") + nama[p[1]] if p[0] in "BH" else p.replace("_", " ") for p in pola.split())
    return f


JAWAB = {
    "mulai-selesai": lambda m: "Mulai {0}\nMengerjakan {0}\nSelesai {0}".format(m.strip()),
    "titik-asal": _titik,
    "urutan-dua-objek": _dua("Ba Bb Hb Ha"),
    "nomor-seri": lambda m: "\n".join(f"{n} #{k}" for k, n in enumerate(_baris(m)[1].split(), 1)),
    "blok-cakupan": _dua("Ba Bb Di_dalam_blok Hb Di_luar_blok Ha"),
    "objek-dinamis": _dua("Ba Bb Dua_objek_di_heap Ha Hb"),
    "salin-dengan-pesan": lambda m: "Buat {0}\nSalin {0}\nHapus {0}-salinan\nHapus {0}".format(m.strip()),
    "waktu-bawaan": _waktu,
    "barisan-prajurit": lambda m: _prajurit(int(m)),
    "parameter-nilai": lambda m: "Buat {0}\nSalin {0}\nPeriksa {0}\nHapus {0}\nKembali ke main\nHapus {0}".format(m.strip()),
    "hitung-salinan": lambda m: f"Panggilan: {len(m.strip())}\nSalinan: {m.count('N')}",
    "konstruktor-delegasi": _delegasi,
    "objek-dalam-perulangan": _perulangan,
    "salin-dalam": _salin_dalam,
    "penugasan-salin": _penugasan,
    "rekening-const": _rekening,
    "tumpukan-objek": _peti,
    "aturan-tiga-teks": lambda m: (lambda w: f"{w[:-1]}{w[-1].upper()}\n{w}\n{w.capitalize()}")(m.split()[0]),
    "penugasan-berantai": lambda m: (lambda z: f"b <- c ({z})\na <- b ({z})\n{z} {z} {z}")(m.split()[2]),
    "kunci-otomatis": _kunci,
    "objek-sementara": _dua("Ba Bb Hb Setelah_objek_sementara Bc Hc Ha"),
    "daftar-tumbuh": _tumbuh,
    "matriks-transpos": _transpos,
    "pelacak-bocor": _bocor,
    "tumpukan-cadangan": _cadangan,
    "objek-statis": lambda m: _statis(int(m)),
    "hitung-salinan-urut": _urut,
    "teks-gabung": lambda m: (lambda t: f"{t}\nPanjang: {len(t)}")("-".join(_baris(m)[1].split())),
    "simulasi-cakupan": _cakupan,
    "batal-ulang": _batal,
    "pangkat-besar": lambda m: str(_angka(m)[0] ** _angka(m)[1]),
    "pelacak-objek": _pelacak,
}

CONTOH = [
    ("mulai-selesai", "laporan\n", "Mulai laporan\nMengerjakan laporan\nSelesai laporan"),
    ("titik-asal", "2\n3 -5\n", "Titik (3, -5) dibuat\n-2"), ("titik-asal", "0\n", "Titik asal dibuat\n0"),
    ("urutan-dua-objek", "meja kursi\n", "Buat meja\nBuat kursi\nHapus kursi\nHapus meja"),
    ("nomor-seri", "2\nBudi Citra\n", "Budi #1\nCitra #2"),
    ("blok-cakupan", "meja kursi\n", "Buat meja\nBuat kursi\nDi dalam blok\nHapus kursi\nDi luar blok\nHapus meja"),
    ("objek-dinamis", "meja kursi\n", "Buat meja\nBuat kursi\nDua objek di heap\nHapus meja\nHapus kursi"),
    ("salin-dengan-pesan", "buku\n", "Buat buku\nSalin buku\nHapus buku-salinan\nHapus buku"),
    ("waktu-bawaan", "1\n7\n", "07:00"), ("waktu-bawaan", "2\n7 5\n", "07:05"),
    ("barisan-prajurit", "2\n", "Prajurit 1 siap\nPrajurit 2 siap\nRegu lengkap\nPrajurit 2 bubar\nPrajurit 1 bubar"),
    ("parameter-nilai", "buku\n", "Buat buku\nSalin buku\nPeriksa buku\nHapus buku\nKembali ke main\nHapus buku"),
    ("hitung-salinan", "NRNNR\n", "Panggilan: 5\nSalinan: 3"),
    ("konstruktor-delegasi", "1\n5\n", "Konstruktor dua parameter\nKonstruktor satu parameter\nLuas: 25"),
    ("objek-dalam-perulangan", "3\nmeja kursi lampu\n", "Buat meja\nBuat kursi\nHapus kursi\nBuat lampu\nHapus lampu\nPerulangan selesai\nHapus meja"),
    ("salin-dalam", "3\n5 6 7\n1 900\n", "5 6 7\n5 900 7"),
    ("penugasan-salin", "3\n1 2 3\n2\n7 8\n500\n", "500 2 3\n1 2 3"),
    ("rekening-const", "1 5\n1234 200000\n", "1234: 210000"),
    ("tumpukan-objek", "BBHBHHH\n", "Buat 1\nBuat 2\nHapus 2\nBuat 3\nHapus 3\nHapus 1\nKosong\nAkhir perintah"),
    ("aturan-tiga-teks", "meja kursi\n", "mejA\nmeja\nMeja"),
    ("penugasan-berantai", "1 2 3\n", "b <- c (3)\na <- b (3)\n3 3 3"),
    ("kunci-otomatis", "gudang 3\n4 -1 7\n", "Kunci gudang diambil\nBaca 4\nData rusak\nKunci gudang dilepas\nGagal"),
    ("objek-sementara", "meja kursi lampu\n", "Buat meja\nBuat kursi\nHapus kursi\nSetelah objek sementara\nBuat lampu\nHapus lampu\nHapus meja"),
    ("daftar-tumbuh", "3\n5 6 7\n", "Tumbuh 1 -> 2\nTumbuh 2 -> 4\nIsi: 5 6 7\nBebas 4"),
    ("matriks-transpos", "2 3\n1 2 3\n4 5 6\n", "1 4\n2 5\n3 6\nSudut: 1 1001"),
    ("pelacak-bocor", "buat meja\nbuat kursi\nhapus lampu\nhapus meja\nbuat pena\nselesai\n", "+meja\n+kursi\nTidak ada lampu\n-meja\n+pena\nBocor: 2\n-kursi\n-pena\nHidup: 0"),
    ("tumpukan-cadangan", "dorong 1\ndorong 2\nsimpan\nambil\ndorong 9\npulihkan\ndorong 3\nselesai\n", "2\nIsi: 1 2 3"),
    ("objek-statis", "2\n", "Buat global\nmain mulai\nBuat statis\nBuat lokal\nPanggilan 1\nHapus lokal\nBuat lokal\nPanggilan 2\nHapus lokal\nmain selesai\nHapus statis\nHapus global"),
    ("objek-statis", "0\n", "Buat global\nmain mulai\nmain selesai\nHapus global"),
    ("hitung-salinan-urut", "3\n3 1 2\n", "1 2 3\nSalin: 2\nTugas: 4"),
    ("teks-gabung", "3\nmeja kursi lampu\n", "meja-kursi-lampu\nPanjang: 16"),
    ("simulasi-cakupan", "{a{bc}d}\n", "Buat a\nBuat b\nBuat c\nHapus c\nHapus b\nBuat d\nHapus d\nHapus a"),
    ("batal-ulang", "tulis meja\ntulis kursi\nbatal\ncetak\nulang\ncetak\nbatal\ntulis lampu\nulang\ncetak\nselesai\n", "meja\nmeja kursi\nTidak bisa ulang\nmeja lampu"),
    ("pangkat-besar", "2 10\n", "1024"), ("pangkat-besar", "7 0\n", "1"), ("pangkat-besar", "99 3\n", "970299"),
    ("pangkat-besar", "2 100\n", "1267650600228229401496703205376"),
    ("pelacak-objek", "buat 5\nbuat 8\nsalin 1\ntugas 2 3\ncetak\nhapus\nselesai\n", "+1\n+2\n+3 salin 1\n2 = 3\nIsi: 1:5 2:5 3:5\n-3\n-2\n-1"),
]
