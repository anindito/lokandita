"""Perhitungan terpisah untuk PBO301 pertemuan 6, dipakai oleh periksa.py.

JAWAB[slug](masukan) dihitung dengan Python secara prosedural — list, dict,
tuple — tanpa class dan tanpa pewarisan. Urutan konstruktor dan destruktor
diturunkan dari aturan bahasanya (induk menurut daftar induk, lalu anggota
menurut deklarasi, lalu badan; penghancuran terbalik; induk virtual paling
dulu), bukan dari menjalankan objek.
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


def _cermin(naik, tengah=()):
    """Baris '+…' saat dibangun, lalu yang sama dengan '-' dalam urutan terbalik."""
    return "\n".join(["+" + x for x in naik] + list(tengah) + ["-" + x for x in reversed(naik)])


def _roda(m):
    jenis, merek = m.split()
    return f"{merek} beroda {4 if jenis == 'mobil' else 2}"


def _infanteri_tank(m):
    k = m.split()
    if k[0] == "infanteri":
        return str(int(k[1]) * 2)
    return str(int(k[1]) * 2 + int(k[2]) * 10)


def _tanpa_virtual(m):
    jenis, nama = m.split()
    return f"{nama} langsung: {'meong' if jenis == 'kucing' else 'guk'}\n{nama} lewat Hewan: ..."


def _giro(m):
    saldo, batas = _angka(_baris(m)[0])
    keluar = []
    for kata, x in _perintah(m, 1):
        x = int(x)
        if kata == "setor":
            saldo += x
        elif saldo - x >= -batas:
            saldo -= x
        else:
            keluar.append("Ditolak")
            continue
        keluar.append(f"Saldo: {saldo}")
    return "\n".join(keluar)


def _atur(m):
    nilai, label = "0", "-"
    for jenis, x in (b.split() for b in _baris(m)[1:]):
        if jenis == "angka":
            nilai = x
        else:
            label = x
    return f"{nilai} {label}"


def _pegawai(m):
    keluar, total = [], 0
    for b in _baris(m)[1:]:
        k = b.split()
        if k[0] == "tetap":
            gaji = int(k[2]) + int(k[3])
        elif k[0] == "kontrak":
            gaji = int(k[2]) * int(k[3])
        else:
            gaji = int(k[2]) // 2
        keluar.append(f"{k[1]}: {gaji}")
        total += gaji
    return "\n".join(keluar + [f"Total: {total}"])


def _ruang(m):
    isi = []
    for b in _baris(m)[1:]:
        v = [int(x) for x in b.split()[1:]]
        isi.append(v[0] ** 3 if len(v) == 1 else v[0] * v[1] * v[2])
    return "\n".join([str(x) for x in isi] + [f"Total: {sum(isi)}"])


def _privat(m):
    antre, tumpuk, keluar = [], [], []
    for k in _perintah(m):
        if k[0] == "antre":
            antre.append(k[1])
        elif k[0] == "tumpuk":
            tumpuk.append(k[1])
        elif k[0] == "layani":
            keluar.append(antre.pop(0) if antre else "antrean kosong")
        else:
            keluar.append(tumpuk.pop() if tumpuk else "tumpukan kosong")
    return "\n".join(keluar + [f"Antrean: {len(antre)}", f"Tumpukan: {len(tumpuk)}"])


def _serbaguna(m):
    nama, kertas = _baris(m)[0].split()
    kertas, dipindai, tengah = int(kertas), 0, []
    for kata, h in _perintah(m, 1):
        h = int(h)
        if kata in ("pindai", "salin"):
            dipindai += h
            tengah.append(f"Memindai {h} halaman")
        if kata in ("cetak", "salin"):
            if h > kertas:
                tengah.append("Kertas kurang")
            else:
                kertas -= h
                tengah.append(f"Mencetak {h} halaman")
    tengah.append(f"Kertas: {kertas}, dipindai: {dipindai}")
    return _cermin(["Printer", "Pemindai", f"MesinSerbaguna {nama}"], tengah)


def _amfibi(m):
    nama, darat, air = m.split()
    return _cermin([f"Kendaraan {nama}", "Darat", "Air", "Amfibi"], [f"{nama} melaju {darat} km/jam", f"{nama} berlayar {air} knot"])


def _berjenjang(m):
    bobot = [1, 5, 10]
    isi = [sum(int(x) * w for x, w in zip(b.split()[1:], bobot)) for b in _baris(m)[1:]]
    return "\n".join([str(x) for x in isi] + [f"Total: {sum(isi)}"])


def _hitung(m):
    kata = _baris(m)[1].split()
    return (f"Unit: {len(kata)}\nUnitDarat: {len(kata) - kata.count('unit')}\n"
            f"Tank: {kata.count('tank')}\nInfanteri: {kata.count('infanteri')}")


def _matriks(m):
    b = _baris(m)
    n = int(b[0])
    g = [_angka(x) for x in b[1:1 + n]]
    simetris = all(g[a][c] == g[c][a] for a in range(n) for c in range(n))
    return (f"Jumlah: {sum(sum(x) for x in g)}\nJejak: {sum(g[a][a] for a in range(n))}\n"
            + ("simetris" if simetris else "tidak simetris"))


def _lengkap(m):
    nama, nomor, tenaga, frekuensi = m.split()
    return _cermin(["Kendaraan", f"Terdaftar {nomor}", f"Mesin {tenaga}", f"Radio {frekuensi}", f"Mobil {nama}"], ["Mobil siap"])


def _inventaris(m):
    keluar, total = [], 0
    for b in _baris(m)[1:]:
        k = b.split()
        teks = f"{k[1]} Rp {k[2]}"
        if len(k) > 3:
            teks += f", garansi {k[3]} bulan"
        if len(k) > 4:
            teks += f", RAM {k[4]} GB"
        keluar.append(teks)
        total += int(k[2])
    return "\n".join(keluar + [f"Total: Rp {total}"])


def _tiga_rekening(m):
    saldo = dict(zip("tgd", _angka(_baris(m)[0])))
    lantai = {"t": 50000, "g": -500000}
    keluar = []
    for kata, mana, x in _perintah(m, 1):
        x = int(x)
        if mana == "d":
            keluar.append("Ditolak")
        elif kata == "setor":
            saldo[mana] += x
            keluar.append(str(saldo[mana]))
        elif saldo[mana] - x >= lantai[mana]:
            saldo[mana] -= x
            keluar.append(str(saldo[mana]))
        else:
            keluar.append("Ditolak")
    return "\n".join(keluar + [f"Tabungan: {saldo['t']}", f"Giro: {saldo['g']}", f"Deposito: {saldo['d']}"])


def _serangan(m):
    b = _baris(m)
    persen = int(b[0].split()[1])
    pembagi = {"infanteri": 1, "artileri": 2, "tank": 4}
    keluar, total = [], 0
    for jenis, nama, personel in (x.split() for x in b[1:]):
        personel = int(personel)
        sisa = personel - personel * (persen // pembagi[jenis]) // 100
        keluar.append(f"{nama}: {sisa}")
        total += sisa
    return "\n".join(keluar + [f"Total: {total}"])


def _prioritas(m):
    biasa, prio, keluar = [], [], []
    urut = 0
    for k in _perintah(m):
        if k[0] == "biasa":
            biasa.append(int(k[1]))
        elif k[0] == "prio":
            urut += 1
            prio.append((-int(k[1]), urut))
        elif k[1] == "biasa":
            keluar.append(str(biasa.pop(0)) if biasa else "kosong")
        elif prio:
            terbaik = min(prio)
            prio.remove(terbaik)
            keluar.append(str(-terbaik[0]))
        else:
            keluar.append("kosong")
    return "\n".join(keluar + [f"Biasa: {len(biasa)}", f"Prioritas: {len(prio)}"])


def _duel(m):
    b = _baris(m)
    ks, hp_k, zirah = b[0].split()[0], int(b[0].split()[1]), int(b[0].split()[2])
    pn, hp_p, mana = b[1].split()[0], int(b[1].split()[1]), int(b[1].split()[2])
    keluar = []
    ronde = 0
    while ronde < 6 and hp_k > 0 and hp_p > 0:
        ronde += 1
        hp_p = max(0, hp_p - 15)
        keluar.append(f"{ks} menyerang: {pn} {hp_p}")
        if hp_p == 0:
            break
        if mana >= 10:
            mana, pukul = mana - 10, 30
        else:
            pukul = 5
        hp_k = max(0, hp_k - max(1, pukul - zirah))
        keluar.append(f"{pn} menyerang: {ks} {hp_k}")
    keluar.append(f"{ks} menang" if hp_p == 0 else f"{pn} menang" if hp_k == 0 else "Seri")
    return "\n".join(keluar)


def _berkelana(m):
    nama, bensin = _baris(m)[0].split()
    bensin = int(bensin)
    km = {"darat": 0, "air": 0}
    boros = {"darat": 2, "air": 5}
    mode, tengah = "darat", []
    for k in _perintah(m, 1):
        if k[0] == "ganti":
            mode = "air" if mode == "darat" else "darat"
            tengah.append(f"Mode {mode}")
            continue
        jarak = int(k[1])
        if jarak * boros[mode] > bensin:
            tengah.append("Bensin tidak cukup")
        else:
            bensin -= jarak * boros[mode]
            km[mode] += jarak
            tengah.append(f"Maju {jarak} km, bensin {bensin}")
    tengah.append(f"Darat {km['darat']} km, air {km['air']} km")
    return _cermin([f"Kendaraan {nama}", "Darat", "Air", "Amfibi"], tengah)


def _akademik(m):
    orang = []                      # [jenis, nama, nomor] menurut urutan pendaftaran
    pembimbing, beban = {}, {}
    keluar = []
    for k in _perintah(m):
        if k[0] != "bimbing":
            orang.append(k)
            if k[0] == "dosen":
                beban[k[1]] = 0
            continue
        a, b = k[1], k[2]
        pasca = [o[1] for o in orang if o[0] == "pasca"]
        if a not in pasca or b not in beban:
            keluar.append("Tidak ditemukan")
        elif a in pembimbing:
            keluar.append("Sudah punya pembimbing")
        elif beban[b] == 2:
            keluar.append("Dosen penuh")
        else:
            pembimbing[a] = b
            beban[b] += 1
            keluar.append(f"{a} dibimbing {b}")
    for jenis, nama, nomor in orang:
        if jenis == "dosen":
            keluar.append(f"{nama} (dosen, NIDN {nomor}), membimbing {beban[nama]}")
        elif jenis == "mahasiswa":
            keluar.append(f"{nama} (mahasiswa, NIM {nomor})")
        else:
            keluar.append(f"{nama} (mahasiswa, NIM {nomor}), pascasarjana, pembimbing {pembimbing.get(nama, '-')}")
    jenis = [o[0] for o in orang]
    keluar.append(f"Orang: {len(orang)}, dosen: {jenis.count('dosen')}, mahasiswa: {len(orang) - jenis.count('dosen')}, pascasarjana: {jenis.count('pasca')}")
    return "\n".join(keluar)


def _robot(m):
    baterai, kapasitas = _angka(_baris(m)[0])
    posisi = muatan = terantar = ditolak = 0
    keluar = []
    for k in _perintah(m, 1):
        if k[0] == "taruh":
            if muatan == 0:
                keluar.append("Tidak membawa apa-apa")
            else:
                terantar += muatan
                keluar.append(f"Menaruh {muatan} kg di posisi {posisi}")
                muatan = 0
        elif k[0] == "angkat":
            berat = int(k[1])
            if muatan == 0 and berat <= kapasitas:
                muatan = berat
                keluar.append(f"Mengangkat {berat} kg")
            else:
                keluar.append("Tidak bisa mengangkat")
        else:
            langkah = int(k[1])
            pakai = langkah * (2 if muatan else 1)
            if baterai - pakai < 10:
                ditolak += 1
                keluar.append("Baterai dijaga")
            else:
                baterai -= pakai
                posisi += langkah
                keluar.append(f"Posisi {posisi}, baterai {baterai}")
    keluar.append(f"Terantar {terantar} kg, ditolak {ditolak} kali")
    return "\n".join(keluar)


def _pustaka(m):
    media = []                      # [jenis, judul, tahun, a, episode, ada]
    keluar = []
    for k in _perintah(m):
        if k[0] == "hapus":
            cocok = [x for x in media if x[5] and x[1] == k[1]]
            for x in cocok:
                x[5] = False
            keluar.append(f"{'Dihapus' if cocok else 'Tidak ada'}: {k[1]}")
        else:
            media.append([k[0], k[1], k[2], int(k[3]), int(k[4]) if k[0] == "seri" else 1, True])
    menit = 0
    for jenis, kepala in (("buku", "Buku:"), ("film", "Film:"), ("seri", "Seri:")):
        keluar.append(kepala)
        for j, judul, tahun, a, episode, ada in media:
            if j != jenis or not ada:
                continue
            if j == "buku":
                keluar.append(f"{judul} ({tahun}), {a} halaman")
                menit += a * 2
            elif j == "film":
                keluar.append(f"{judul} ({tahun}), {a} menit")
                menit += a
            else:
                keluar.append(f"{judul} ({tahun}), {a} menit x {episode} episode")
                menit += a * episode
    keluar.append(f"Total: {menit // 60} jam {menit % 60} menit")
    return "\n".join(keluar)


JAWAB = {
    "hewan-kucing": lambda m: f"{m.strip()} makan\n{m.strip()} mengeong",
    "manajer-gaji": lambda m: (lambda n, g, t: f"{n}: {(int(g) + int(t)) * 12}")(*m.split()),
    "urutan-induk-anak": lambda m: (lambda n: f"Unit {n} dibentuk\nInfanteri {n} siap\nInfanteri {n} mundur\nUnit {n} dibubarkan")(m.strip()),
    "bentuk-persegi": lambda m: f"Ini persegi\nLuas: {int(m) ** 2}",
    "roda-kendaraan": _roda,
    "tabungan-protected": lambda m: (lambda s, p: str(s + s * p // 100))(*_angka(m)),
    "panggil-induk": lambda m: (lambda n, k: f"Saya {n}\nNIM saya {k}")(*m.split()),
    "infanteri-atau-tank": _infanteri_tank,
    "tiga-tingkat": lambda m: (lambda n: _cermin([f"Unit {n}", f"UnitDarat {n}", f"Infanteri {n}"], ["Siap tempur"]))(m.strip()),
    "sembunyi-nama": lambda m: (lambda x, y: f"Anak: {y}\nInduk: {x}")(*m.split()),
    "tanpa-virtual": _tanpa_virtual,
    "induk-anggota-diri": lambda m: (lambda n, t: _cermin(["Kendaraan", f"Mesin {t}", f"Mobil {n}"]))(*m.split()),
    "giro-cerukan": _giro,
    "atur-dua-cara": _atur,
    "pegawai-tiga-jenis": _pegawai,
    "bangun-ruang": _ruang,
    "pewarisan-privat": _privat,
    "mesin-serbaguna": _serbaguna,
    "kendaraan-amfibi": _amfibi,
    "kekuatan-berjenjang": _berjenjang,
    "hitung-per-tingkat": _hitung,
    "matriks-persegi": _matriks,
    "urutan-lengkap": _lengkap,
    "inventaris-berjenjang": _inventaris,
    "tiga-rekening": _tiga_rekening,
    "serangan-udara": _serangan,
    "antrean-prioritas": _prioritas,
    "duel-ksatria-penyihir": _duel,
    "amfibi-berkelana": _berkelana,
    "sistem-akademik": _akademik,
    "robot-bertingkat": _robot,
    "pustaka-media": _pustaka,
}

CONTOH = [
    ("hewan-kucing", "Oyen\n", "Oyen makan\nOyen mengeong"),
    ("manajer-gaji", "Budi 5000000 1000000\n", "Budi: 72000000"),
    ("urutan-induk-anak", "Garuda\n", "Unit Garuda dibentuk\nInfanteri Garuda siap\nInfanteri Garuda mundur\nUnit Garuda dibubarkan"),
    ("bentuk-persegi", "7\n", "Ini persegi\nLuas: 49"),
    ("roda-kendaraan", "mobil Kijang\n", "Kijang beroda 4"), ("roda-kendaraan", "motor Vario\n", "Vario beroda 2"),
    ("tabungan-protected", "200000 5\n", "210000"),
    ("panggil-induk", "Dewi 23017\n", "Saya Dewi\nNIM saya 23017"),
    ("infanteri-atau-tank", "infanteri 30\n", "60"), ("infanteri-atau-tank", "tank 4 12\n", "128"),
    ("tiga-tingkat", "Elang\n", "+Unit Elang\n+UnitDarat Elang\n+Infanteri Elang\nSiap tempur\n-Infanteri Elang\n-UnitDarat Elang\n-Unit Elang"),
    ("sembunyi-nama", "5 8\n", "Anak: 8\nInduk: 5"),
    ("tanpa-virtual", "kucing Oyen\n", "Oyen langsung: meong\nOyen lewat Hewan: ..."),
    ("induk-anggota-diri", "Kijang 100\n", "+Kendaraan\n+Mesin 100\n+Mobil Kijang\n-Mobil Kijang\n-Mesin 100\n-Kendaraan"),
    ("giro-cerukan", "100000 200000\ntarik 250000\ntarik 100000\nsetor 50000\nselesai\n", "Saldo: -150000\nDitolak\nSaldo: -100000"),
    ("atur-dua-cara", "3\nangka 5\nteks merah\nangka -2\n", "-2 merah"),
    ("pegawai-tiga-jenis", "3\ntetap Andi 5000000 1000000\nkontrak Budi 100 50000\nmagang Citra 3000000\n", "Andi: 6000000\nBudi: 5000000\nCitra: 1500000\nTotal: 12500000"),
    ("bangun-ruang", "2\nkubus 3\nbalok 2 3 4\n", "27\n24\nTotal: 51"),
    ("pewarisan-privat", "antre 1\nantre 2\ntumpuk 1\ntumpuk 2\nlayani\nangkat\nselesai\n", "1\n2\nAntrean: 1\nTumpukan: 1"),
    ("mesin-serbaguna", "Lab 5\nsalin 3\ncetak 4\nselesai\n", "+Printer\n+Pemindai\n+MesinSerbaguna Lab\nMemindai 3 halaman\nMencetak 3 halaman\nKertas kurang\nKertas: 2, dipindai: 3\n-MesinSerbaguna Lab\n-Pemindai\n-Printer"),
    ("kendaraan-amfibi", "Hiu 80 12\n", "+Kendaraan Hiu\n+Darat\n+Air\n+Amfibi\nHiu melaju 80 km/jam\nHiu berlayar 12 knot\n-Amfibi\n-Air\n-Darat\n-Kendaraan Hiu"),
    ("kekuatan-berjenjang", "3\nunit 40\ndarat 30 4\ntank 4 1 12\n", "40\n50\n129\nTotal: 219"),
    ("hitung-per-tingkat", "4\ntank infanteri unit tank\n", "Unit: 4\nUnitDarat: 3\nTank: 2\nInfanteri: 1"),
    ("matriks-persegi", "2\n1 2\n2 5\n", "Jumlah: 10\nJejak: 6\nsimetris"),
    ("urutan-lengkap", "Kijang D5678AB 100 99\n", "+Kendaraan\n+Terdaftar D5678AB\n+Mesin 100\n+Radio 99\n+Mobil Kijang\nMobil siap\n-Mobil Kijang\n-Radio 99\n-Mesin 100\n-Terdaftar D5678AB\n-Kendaraan"),
    ("inventaris-berjenjang", "2\nbarang Meja 500000\nlaptop Aspire 9000000 12 16\n", "Meja Rp 500000\nAspire Rp 9000000, garansi 12 bulan, RAM 16 GB\nTotal: Rp 9500000"),
    ("tiga-rekening", "200000 0 1000000\ntarik t 160000\ntarik g 300000\nsetor d 50000\ntarik t 150000\nselesai\n", "Ditolak\n-300000\nDitolak\n50000\nTabungan: 50000\nGiro: -300000\nDeposito: 1000000"),
    ("serangan-udara", "3 40\ninfanteri Alfa 500\ntank Bravo 200\nartileri Delta 300\n", "Alfa: 300\nBravo: 180\nDelta: 240\nTotal: 720"),
    ("antrean-prioritas", "biasa 3\nbiasa 9\nprio 3\nprio 9\nprio 5\nlayani biasa\nlayani prio\nlayani prio\nselesai\n", "3\n9\n5\nBiasa: 1\nPrioritas: 1"),
    ("duel-ksatria-penyihir", "Andi 50 5\nBudi 40 10\n", "Andi menyerang: Budi 25\nBudi menyerang: Andi 25\nAndi menyerang: Budi 10\nBudi menyerang: Andi 24\nAndi menyerang: Budi 0\nAndi menang"),
    ("amfibi-berkelana", "Hiu 50\nmaju 10\nganti\nmaju 10\nmaju 5\nselesai\n", "+Kendaraan Hiu\n+Darat\n+Air\n+Amfibi\nMaju 10 km, bensin 30\nMode air\nBensin tidak cukup\nMaju 5 km, bensin 5\nDarat 10 km, air 5 km\n-Amfibi\n-Air\n-Darat\n-Kendaraan Hiu"),
    ("sistem-akademik", "dosen Sari 1111\npasca Andi 2222\nmahasiswa Budi 3333\nbimbing Andi Sari\nbimbing Budi Sari\nselesai\n", "Andi dibimbing Sari\nTidak ditemukan\nSari (dosen, NIDN 1111), membimbing 1\nAndi (mahasiswa, NIM 2222), pascasarjana, pembimbing Sari\nBudi (mahasiswa, NIM 3333)\nOrang: 3, dosen: 1, mahasiswa: 2, pascasarjana: 1"),
    ("robot-bertingkat", "40 20\nmaju 5\nangkat 25\nangkat 10\nmaju 10\nmaju 8\ntaruh\nselesai\n", "Posisi 5, baterai 35\nTidak bisa mengangkat\nMengangkat 10 kg\nPosisi 15, baterai 15\nBaterai dijaga\nMenaruh 10 kg di posisi 15\nTerantar 10 kg, ditolak 1 kali"),
    ("pustaka-media", "buku Arus 2001 150\nseri Kabut 2020 45 8\nfilm Bumi 1999 120\nhapus Bumi\nhapus Bumi\nselesai\n", "Dihapus: Bumi\nTidak ada: Bumi\nBuku:\nArus (2001), 150 halaman\nFilm:\nSeri:\nKabut (2020), 45 menit x 8 episode\nTotal: 11 jam 0 menit"),
]
