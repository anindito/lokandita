"""Perhitungan terpisah untuk PBO301 pertemuan 7, dipakai oleh periksa.py.

JAWAB[slug](masukan) dihitung dengan Python secara prosedural — dict, list,
tuple, fungsi biasa — tanpa class, tanpa pewarisan dan tanpa pemanggilan
virtual: pilihan perilaku ditulis sebagai tabel atau percabangan atas kata
jenis. Akibat aturan bahasa (irisan objek, virtual di konstruktor, method biasa
lewat pointer induk, urutan destruktor) ditulis langsung dari aturannya.
CONTOH memuat setiap contoh yang dikutip di pernyataan soal.
"""


def _angka(m):
    return [int(x) for x in m.split()]


def _baris(m):
    return [b for b in m.split("\n") if b != ""]


def _perintah(m, awal=0):
    hasil = []
    for b in _baris(m)[awal:]:
        if b == "selesai":
            break
        hasil.append(b.split())
    return hasil


SUARA = {"kucing": "meong", "anjing": "guk", "burung": "cuit", "hewan": "..."}


def _luas(kata):
    """(nama, luas) dari ['persegi', s] / ['panjang', p, l] / ['segitiga', a, t]."""
    v = [int(x) for x in kata[1:]]
    if kata[0] == "persegi":
        return "persegi", v[0] * v[0]
    if kata[0] == "panjang":
        return "persegi panjang", v[0] * v[1]
    return "segitiga", v[0] * v[1] // 2


def _parkir(m):
    jenis, jam = m.split()
    jam = int(jam)
    return str({"mobil": 5000 + 2000 * (jam - 1), "motor": min(10000, 2000 * jam), "sepeda": 1000 * jam}[jenis])


def _catatan(m):
    jenis, pesan = m.split()
    return ("PENTING!\n" if jenis == "penting" else "") + f"Catatan: {pesan}"


def _larik_bentuk(m):
    data = [_luas(b.split()) for b in _baris(m)[1:]]
    return "\n".join([f"{n}: {l}" for n, l in data] + [f"Total: {sum(l for _, l in data)}"])


def _hancur(m):
    data = [b.split() for b in _baris(m)[1:]]
    kelas = {"tank": "Tank", "infanteri": "Infanteri"}
    naik = [x for j, n in data for x in (f"+Unit {n}", f"+{kelas[j]} {n}")]
    turun = [x for j, n in reversed(data) for x in (f"-{kelas[j]} {n}", f"-Unit {n}")]
    return "\n".join(naik + turun)


def _paket(m):
    keluar, terbaik = [], None
    for jenis, kode, berat in (b.split() for b in _baris(m)[1:]):
        berat = int(berat)
        ongkos = {"biasa": berat * 5000, "kilat": berat * 10000 + 10000, "hemat": max(9000, berat * 3000)}[jenis]
        keluar.append(f"{kode}: {ongkos}")
        if terbaik is None or ongkos > terbaik[0]:
            terbaik = (ongkos, kode)
    return "\n".join(keluar + [f"Termahal: {terbaik[1]}"])


def _kucing(m):
    data = [b.split() for b in _baris(m)[1:]]
    keluar = []
    for jenis, nama in data:
        keluar.append(f"{nama}: {SUARA[jenis]}")
        if jenis == "kucing":
            keluar.append(f"{nama} mencakar")
    return "\n".join(keluar + [f"Kucing: {sum(1 for j, _ in data if j == 'kucing')} dari {len(data)}"])


def _timpa(m):
    tabel = {"dasar": "satu=Dasar dua=Dasar", "tengah": "satu=Tengah dua=Dasar", "atas": "satu=Tengah dua=Atas"}
    return "\n".join(tabel[k] for k in _baris(m)[1].split())


def _sensor(m):
    keluar = []
    for jenis, mentah in (b.split() for b in _baris(m)[1:]):
        mentah = int(mentah)
        if jenis == "umum":
            keluar.append(f"sensor: {mentah}")
        else:
            keluar.append(f"{'suhu' if jenis == 'suhu' else 'suhu luar'}: {mentah // 10 - 40}")
    return "\n".join(keluar)


def _terurut(m):
    data = [_luas(b.split()) for b in _baris(m)[1:]]
    return "\n".join(f"{n} {l}" for n, l in sorted(data, key=lambda x: x[1]))


def _salin(m):
    k = m.split()
    v = [int(x) for x in k[1:]]
    if k[0] == "persegi":
        return f"Asli: persegi {v[0] * v[1]}\nSalinan: persegi {v[0]}"
    return f"Asli: persegi panjang {v[0] * v[2]} x {v[1] * v[2]}\nSalinan: persegi panjang {v[0]} x {v[1]}"


def _dokumen(m):
    k = m.split()
    if k[0] == "surat":
        return f"== {k[1]} ==\nKepada {k[2]}\nHormat kami"
    if k[0] == "laporan":
        return f"LAPORAN: {k[1]}\n{k[2]} halaman\n--"
    return f"== {k[1]} ==\n(kosong)\n--"


def _kalkulator(m):
    keluar = []
    for kata, a, b in (x.split() for x in _baris(m)[1:]):
        a, b = int(a), int(b)
        simbol, hasil = {"tambah": ("+", a + b), "kurang": ("-", a - b), "kali": ("*", a * b), "bagi": ("/", a // b), "sisa": ("%", a % b)}[kata]
        keluar.append(f"{a} {simbol} {b} = {hasil}")
    return "\n".join(keluar)


def _bayar(m):
    b = _baris(m)
    saldo, terbayar, keluar = int(b[0]), 0, []
    for cara, jumlah in (x.split() for x in b[2:]):
        jumlah = int(jumlah)
        ok = cara == "tunai" or (cara == "kartu" and jumlah <= 500000) or (cara == "dompet" and jumlah <= saldo)
        if ok and cara == "dompet":
            saldo -= jumlah
        if ok:
            terbayar += jumlah
        keluar.append(f"{cara.capitalize()}: {'berhasil' if ok else 'ditolak'}")
    return "\n".join(keluar + [f"Terbayar: {terbayar}"])


def _gambar(m):
    keluar = []
    for jenis, u in (x.split() for x in _baris(m)[1:]):
        u = int(u)
        keluar.append(f"{jenis} {u}")
        if jenis == "garis":
            keluar.append("#" * u)
        elif jenis == "persegi":
            keluar += ["#" * u] * u
        else:
            keluar += ["#" * k for k in range(1, u + 1)]
    return "\n".join(keluar)


def _batal(m):
    nilai = int(_baris(m)[0])
    riwayat, keluar = [], []
    for k in _perintah(m, 1):
        if k[0] == "batal":
            if not riwayat:
                keluar.append("Tidak ada yang dibatalkan")
                continue
            nilai = riwayat.pop()
        else:
            riwayat.append(nilai)
            nilai = nilai + int(k[1]) if k[0] == "tambah" else nilai * int(k[1])
        keluar.append(str(nilai))
    return "\n".join(keluar + [f"Akhir: {nilai}, riwayat {len(riwayat)}"])


def _filter(m):
    b = _baris(m)
    f = int(b[0])
    rantai = [x.split() for x in b[1:1 + f]]
    keluar = []
    for x in _angka(b[2 + f]):
        for k in rantai:
            if k[0] == "kali":
                x *= int(k[1])
            elif k[0] == "tambah":
                x += int(k[1])
            elif k[0] == "batas":
                x = min(max(x, int(k[1])), int(k[2]))
            else:
                x = abs(x)
        keluar.append(str(x))
    return "\n".join(keluar)


def _pabrik(m):
    keluar, dibuat = [], []
    for jenis, nama in (x.split() for x in _baris(m)[1:]):
        if jenis in ("kucing", "anjing", "burung"):
            keluar.append(f"{nama}: {SUARA[jenis]}")
            dibuat.append(nama)
        else:
            keluar.append(f"Jenis {jenis} tidak dikenal")
    return "\n".join(keluar + [f"Dibuat: {len(dibuat)}"] + [f"{n} pulang" for n in dibuat])


def _suit(m):
    kalah = {("batu", "gunting"), ("gunting", "kertas"), ("kertas", "batu")}
    skor = {"A menang": 0, "B menang": 0, "Seri": 0}
    keluar = []
    for a, b in (x.split() for x in _baris(m)[1:]):
        hasil = "A menang" if (a, b) in kalah else "B menang" if (b, a) in kalah else "Seri"
        skor[hasil] += 1
        keluar.append(hasil)
    return "\n".join(keluar + [f"A {skor['A menang']}, B {skor['B menang']}, seri {skor['Seri']}"])


def _irisan_larik(m):
    data = [(j, int(x)) for j, x in (b.split() for b in _baris(m)[1:])]
    utuh = sum({"biasa": x, "ganda": 2 * x, "bonus": x + 10}[j] for j, x in data)
    return f"Terpotong: {sum(x for _, x in data)}\nUtuh: {utuh}"


def _editor(m):
    bentuk, keluar = [], []            # tiap bentuk: list ukuran, atau None bila dihapus
    def teks(v):
        return f"persegi {v[0] * v[0]}" if len(v) == 1 else f"persegi panjang {v[0] * v[1]}"
    for k in _perintah(m):
        if k[0] in ("persegi", "panjang"):
            bentuk.append([int(x) for x in k[1:]])
            keluar.append(f"Bentuk {len(bentuk)} ditambahkan")
            continue
        nomor = int(k[1])
        if not (1 <= nomor <= len(bentuk)) or bentuk[nomor - 1] is None:
            keluar.append(f"Tidak ada bentuk {nomor}")
        elif k[0] == "skala":
            bentuk[nomor - 1] = [x * int(k[2]) for x in bentuk[nomor - 1]]
            keluar.append(f"Bentuk {nomor}: {teks(bentuk[nomor - 1])}")
        else:
            bentuk[nomor - 1] = None
            keluar.append(f"Bentuk {nomor} dihapus")
    total = 0
    for nomor, v in enumerate(bentuk, 1):
        if v is not None:
            keluar.append(f"{nomor}. {teks(v)}")
            total += int(teks(v).split()[-1])
    return "\n".join(keluar + [f"Total luas: {total}"])


def _ekspresi(m):
    token = m.split()
    # notasi awalan dievaluasi dari belakang dengan tumpukan, tanpa pohon dan tanpa rekursi
    tumpukan = []
    for t in reversed(token):
        if t in "+-*":
            (ta, na), (tb, nb) = tumpukan.pop(), tumpukan.pop()
            tumpukan.append((f"({ta} {t} {tb})", {"+": na + nb, "-": na - nb, "*": na * nb}[t]))
        else:
            tumpukan.append((t, int(t)))
    return f"{tumpukan[0][0]} = {tumpukan[0][1]}"


def _berkas(m):
    induk = {"akar": None}              # nama -> nama induk
    jenis = {"akar": "folder"}
    besar = {}
    anak = {"akar": []}
    keluar = []
    for k in _perintah(m):
        nama, tempat = k[1], k[-1]
        if jenis.get(tempat) != "folder":
            keluar.append(f"Gagal: {nama}")
            continue
        jenis[nama] = k[0]
        induk[nama] = tempat
        anak[tempat].append(nama)
        if k[0] == "folder":
            anak[nama] = []
        else:
            besar[nama] = int(k[2])
    def ukuran(nama):
        return besar[nama] if jenis[nama] == "berkas" else sum(ukuran(a) for a in anak[nama])
    def cetak(nama, tingkat):
        if jenis[nama] == "berkas":
            keluar.append("--" * tingkat + f"{nama} ({besar[nama]})")
        else:
            keluar.append("--" * tingkat + f"{nama}/ ({ukuran(nama)})")
            for a in anak[nama]:
                cetak(a, tingkat + 1)
    cetak("akar", 0)
    return "\n".join(keluar)


def _bidak(m):
    b = _baris(m)
    n = int(b[0])
    pos = {}
    for jenis, br, kl in (x.split() for x in b[1:1 + n]):
        pos[(int(br), int(kl))] = jenis
    keluar = []
    for b1, k1, b2, k2 in (_angka(x) for x in b[2 + n:]):
        jenis = pos.get((b1, k1))
        if jenis is None:
            keluar.append("Kosong")
            continue
        a, c = abs(b2 - b1), abs(k2 - k1)
        sah = {"benteng": a == 0 or c == 0, "gajah": a == c, "kuda": {a, c} == {1, 2}, "raja": a <= 1 and c <= 1}[jenis]
        if (a == 0 and c == 0) or not sah:
            keluar.append("Langkah tidak sah")
            continue
        antara = []
        if jenis != "kuda":
            langkah = max(a, c)
            antara = [(b1 + (b2 - b1) // langkah * s, k1 + (k2 - k1) // langkah * s) for s in range(1, langkah)]
        if any(p in pos for p in antara):
            keluar.append("Terhalang")
        elif (b2, k2) in pos:
            keluar.append("Petak terisi")
        else:
            pos[(b2, k2)] = pos.pop((b1, k1))
            keluar.append(f"{jenis[0].upper()} ke {b2} {k2}")
    for br in range(1, 9):
        keluar.append("".join(pos[(br, kl)][0].upper() if (br, kl) in pos else "." for kl in range(1, 9)))
    return "\n".join(keluar)


def _jadwal(m):
    b = _baris(m)
    n, lama = _angka(b[0])
    tugas = [x.split() for x in b[1:1 + n]]
    hidup = [True] * n
    sisa = [int(t[3]) if t[0] == "terbatas" else None for t in tugas]
    keluar = []
    for t in range(1, lama + 1):
        for k, tg in enumerate(tugas):
            if not hidup[k]:
                continue
            jenis = tg[0]
            if jenis == "sekali" and t != int(tg[2]):
                continue
            if jenis in ("berulang", "terbatas") and t % int(tg[2]) != 0:
                continue
            keluar.append(f"{t}: {tg[1]}")
            tuntas = jenis == "sekali"
            if jenis == "terbatas":
                sisa[k] -= 1
                tuntas = sisa[k] == 0
            if tuntas:
                keluar.append(f"{t}: {tg[1]} selesai")
                hidup[k] = False
    return "\n".join(keluar + [f"Tersisa: {sum(hidup)}"])


JAWAB = {
    "suara-hewan": lambda m: (lambda j, n: f"{n}: {SUARA[j]}")(*m.split()),
    "luas-lewat-pointer": lambda m: str(_luas(m.split())[1]),
    "virtual-atau-biasa": lambda m: (lambda j, n: f"{n} biasa: Unit\n{n} virtual: {j.capitalize()}")(*m.split()),
    "destruktor-virtual": lambda m: (lambda n: f"+Unit {n}\n+Infanteri {n}\nBertugas\n-Infanteri {n}\n-Unit {n}")(m.strip()),
    "salam-bahasa": lambda m: (lambda k, n: f"{ {'id': 'Halo', 'en': 'Hello', 'ar': 'Marhaban'}[k] }, {n}!")(*m.split()),
    "sajikan-minuman": lambda m: (lambda j, g: f"Menyajikan {g} gelas { {'kopi': 'kopi', 'teh': 'teh', 'air': 'air putih'}[j] }")(*m.split()),
    "tarif-parkir": _parkir,
    "catatan-penting": _catatan,
    "larik-bentuk": _larik_bentuk,
    "irisan-objek": lambda m: (lambda j, n: f"{n} lewat nilai: ...\n{n} lewat rujukan: {SUARA[j]}")(*m.split()),
    "hancur-berurutan": _hancur,
    "virtual-di-konstruktor": lambda m: (lambda j, n: f"{n} saat dibangun: Induk\n{n} setelah jadi: {j.capitalize()}")(*m.split()),
    "paket-termahal": _paket,
    "hitung-kucing": _kucing,
    "timpa-sebagian": _timpa,
    "sensor-final": _sensor,
    "bentuk-terurut": _terurut,
    "salin-virtual": _salin,
    "dokumen-template": _dokumen,
    "kalkulator-operasi": _kalkulator,
    "metode-pembayaran": _bayar,
    "gambar-bentuk": _gambar,
    "batal-perintah": _batal,
    "filter-berantai": _filter,
    "pabrik-hewan": _pabrik,
    "suit-batu-gunting-kertas": _suit,
    "irisan-larik": _irisan_larik,
    "editor-bentuk": _editor,
    "pohon-ekspresi": _ekspresi,
    "pohon-berkas": _berkas,
    "papan-bidak": _bidak,
    "penjadwal-tugas": _jadwal,
}

CONTOH = [
    ("suara-hewan", "kucing Oyen\n", "Oyen: meong"), ("suara-hewan", "hewan Koko\n", "Koko: ..."),
    ("luas-lewat-pointer", "persegi 7\n", "49"), ("luas-lewat-pointer", "panjang 3 5\n", "15"),
    ("virtual-atau-biasa", "tank Garuda\n", "Garuda biasa: Unit\nGaruda virtual: Tank"),
    ("destruktor-virtual", "Garuda\n", "+Unit Garuda\n+Infanteri Garuda\nBertugas\n-Infanteri Garuda\n-Unit Garuda"),
    ("salam-bahasa", "en Budi\n", "Hello, Budi!"), ("salam-bahasa", "id Citra\n", "Halo, Citra!"),
    ("sajikan-minuman", "kopi 2\n", "Menyajikan 2 gelas kopi"), ("sajikan-minuman", "air 1\n", "Menyajikan 1 gelas air putih"),
    ("tarif-parkir", "mobil 3\n", "9000"), ("tarif-parkir", "motor 8\n", "10000"), ("tarif-parkir", "sepeda 4\n", "4000"),
    ("catatan-penting", "penting rapat\n", "PENTING!\nCatatan: rapat"), ("catatan-penting", "biasa belanja\n", "Catatan: belanja"),
    ("larik-bentuk", "3\npersegi 4\npanjang 2 5\nsegitiga 6 3\n", "persegi: 16\npersegi panjang: 10\nsegitiga: 9\nTotal: 35"),
    ("irisan-objek", "kucing Oyen\n", "Oyen lewat nilai: ...\nOyen lewat rujukan: meong"),
    ("hancur-berurutan", "2\ntank Alfa\ninfanteri Bravo\n", "+Unit Alfa\n+Tank Alfa\n+Unit Bravo\n+Infanteri Bravo\n-Infanteri Bravo\n-Unit Bravo\n-Tank Alfa\n-Unit Alfa"),
    ("virtual-di-konstruktor", "anak Budi\n", "Budi saat dibangun: Induk\nBudi setelah jadi: Anak"),
    ("paket-termahal", "3\nbiasa P01 2\nkilat P02 1\nhemat P03 1\n", "P01: 10000\nP02: 20000\nP03: 9000\nTermahal: P02"),
    ("hitung-kucing", "3\nkucing Oyen\nanjing Bruno\nkucing Belang\n", "Oyen: meong\nOyen mencakar\nBruno: guk\nBelang: meong\nBelang mencakar\nKucing: 2 dari 3"),
    ("timpa-sebagian", "3\ndasar atas tengah\n", "satu=Dasar dua=Dasar\nsatu=Tengah dua=Atas\nsatu=Tengah dua=Dasar"),
    ("sensor-final", "3\numum 512\nsuhu 655\nluar 409\n", "sensor: 512\nsuhu: 25\nsuhu luar: 0"),
    ("bentuk-terurut", "3\npersegi 4\nsegitiga 6 3\npanjang 2 5\n", "segitiga 9\npersegi panjang 10\npersegi 16"),
    ("salin-virtual", "persegi 3 2\n", "Asli: persegi 6\nSalinan: persegi 3"),
    ("salin-virtual", "panjang 2 5 3\n", "Asli: persegi panjang 6 x 15\nSalinan: persegi panjang 2 x 5"),
    ("dokumen-template", "surat Undangan Budi\n", "== Undangan ==\nKepada Budi\nHormat kami"),
    ("dokumen-template", "laporan Keuangan 12\n", "LAPORAN: Keuangan\n12 halaman\n--"),
    ("kalkulator-operasi", "2\nkali 6 7\nsisa 17 5\n", "6 * 7 = 42\n17 % 5 = 2"),
    ("metode-pembayaran", "300000\n4\ndompet 200000\ndompet 150000\nkartu 600000\ntunai 50000\n", "Dompet: berhasil\nDompet: ditolak\nKartu: ditolak\nTunai: berhasil\nTerbayar: 250000"),
    ("gambar-bentuk", "2\nsegitiga 3\ngaris 4\n", "segitiga 3\n#\n##\n###\ngaris 4\n####"),
    ("batal-perintah", "5\ntambah 3\nkali 4\nbatal\nbatal\nbatal\nselesai\n", "8\n32\n8\n5\nTidak ada yang dibatalkan\nAkhir: 5, riwayat 0"),
    ("filter-berantai", "3\nkali 2\ntambah -5\nbatas 0 10\n3\n1 4 9\n", "0\n3\n10"),
    ("pabrik-hewan", "3\nkucing Oyen\nnaga Api\nburung Kiki\n", "Oyen: meong\nJenis naga tidak dikenal\nKiki: cuit\nDibuat: 2\nOyen pulang\nKiki pulang"),
    ("suit-batu-gunting-kertas", "3\nbatu gunting\nkertas gunting\nbatu batu\n", "A menang\nB menang\nSeri\nA 1, B 1, seri 1"),
    ("irisan-larik", "3\nbiasa 5\nganda 5\nbonus 5\n", "Terpotong: 15\nUtuh: 30"),
    ("editor-bentuk", "persegi 2\npanjang 2 3\nskala 1 3\nhapus 2\nskala 2 2\nselesai\n", "Bentuk 1 ditambahkan\nBentuk 2 ditambahkan\nBentuk 1: persegi 36\nBentuk 2 dihapus\nTidak ada bentuk 2\n1. persegi 36\nTotal luas: 36"),
    ("pohon-ekspresi", "+ 3 * 4 5\n", "(3 + (4 * 5)) = 23"), ("pohon-ekspresi", "- 2 9\n", "(2 - 9) = -7"), ("pohon-ekspresi", "7\n", "7 = 7"),
    ("pohon-berkas", "folder dok akar\nberkas a.txt 10 dok\nberkas b.txt 5 akar\nberkas c.txt 7 a.txt\nfolder x hilang\nselesai\n", "Gagal: c.txt\nGagal: x\nakar/ (15)\n--dok/ (10)\n----a.txt (10)\n--b.txt (5)"),
    ("papan-bidak", "2\nbenteng 1 1\nkuda 1 2\n5\n1 1 1 5\n1 2 3 3\n1 1 1 5\n1 5 2 6\n4 4 5 5\n", "Terhalang\nK ke 3 3\nB ke 1 5\nLangkah tidak sah\nKosong\n....B...\n........\n..K.....\n........\n........\n........\n........\n........"),
    ("penjadwal-tugas", "3 6\nsekali cadang 2\nberulang surel 3\nterbatas lapor 2 2\n", "2: cadang\n2: cadang selesai\n2: lapor\n3: surel\n4: lapor\n4: lapor selesai\n6: surel\nTersisa: 1"),
]
