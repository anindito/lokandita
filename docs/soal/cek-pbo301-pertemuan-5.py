"""Perhitungan terpisah untuk PBO301 pertemuan 5, dipakai oleh periksa.py.

JAWAB[slug](masukan) dihitung dengan Python secara prosedural — dict, list,
tuple, datetime — tanpa class dan tanpa relasi antar objek. Urutan konstruktor
dan destruktor diturunkan dari aturan bahasanya, bukan dari menjalankan objek.
CONTOH memuat setiap contoh yang dikutip di pernyataan soal.
"""
import datetime
from collections import OrderedDict


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


def _umur(m):
    b = _baris(m)
    lh, lb, lt = _angka(b[1])
    kh, kb, kt = _angka(b[2])
    umur = kt - lt - (1 if (kb, kh) < (lb, lh) else 0)
    return f"{b[0]} berumur {umur} tahun"


def _pembimbing(m):
    b = _baris(m)
    return f"{b[0]} dibimbing {b[2]}" if b[1] == "1" else f"{b[0]} belum punya pembimbing"


def _sepeda(m):
    d, b = (x >= 30 for x in _angka(m))
    return {(True, True): "siap jalan", (False, True): "pompa roda depan", (True, False): "pompa roda belakang", (False, False): "pompa kedua roda"}[(d, b)]


def _keranjang(m):
    data = [(x.split()[0], int(x.split()[1]) * int(x.split()[2])) for x in _baris(m)[1:]]
    return "\n".join([f"{n}: {s}" for n, s in data] + [f"Total: {sum(s for _, s in data)}"])


def _regu(m):
    b = _baris(m)
    n = int(b[0])
    sehat = [int(x.split()[1]) for x in b[1:1 + n]]
    anggota = [k - 1 for k in _angka(b[2 + n])]
    korban, luka = _angka(b[3 + n])
    sebelum = sum(sehat[k] for k in anggota)
    sehat[korban - 1] = max(0, sehat[korban - 1] - luka)
    return f"{sebelum}\n{sum(sehat[k] for k in anggota)}"


def _putar(m):
    data = [(x.split()[0], int(x.split()[1])) for x in _baris(m)[1:]]
    total = sum(d for _, d in data)
    return f"Total: {total // 60}:{total % 60:02d}\nTerpanjang: {max(data, key=lambda x: x[1])[0]}"


def _segitiga(m):
    (x1, y1), (x2, y2), (x3, y3) = [tuple(_angka(b)) for b in _baris(m)]
    ganda = abs((x2 - x1) * (y3 - y1) - (x3 - x1) * (y2 - y1))
    return "Ketiga titik segaris" if ganda == 0 else f"Luas x 2 = {ganda}"


def _ipk(m):
    data = [(int(x.split()[1]), "EDCBA".index(x.split()[2])) for x in _baris(m)[1:]]
    sks = sum(s for s, _ in data)
    return f"SKS: {sks}\nIPK: {sum(s * w for s, w in data) / sks:.2f}"


def _hotel(m):
    kamar, hasil = {}, []
    for p in _perintah(m):
        k = p[1]
        if p[0] == "masuk":
            if k in kamar:
                hasil.append(f"Kamar {k} sudah terisi")
            else:
                kamar[k] = p[2]
                hasil.append(f"Kamar {k}: {p[2]}")
        elif k in kamar:
            del kamar[k]
            hasil.append(f"Kamar {k} dikosongkan")
        else:
            hasil.append(f"Kamar {k} memang kosong")
    return "\n".join(hasil + [f"{k}: {kamar.get(str(k), '-')}" for k in range(1, 6)])


def _peleton(m):
    b = _baris(m)
    nama = b[0].split()
    banyak, kuat = [0, 0, 0], [0, 0, 0]
    for x in b[2:]:
        _, sehat, regu = x.split()
        banyak[int(regu) - 1] += 1
        kuat[int(regu) - 1] += int(sehat)
    return "\n".join([f"{nama[k]}: {banyak[k]} prajurit, kekuatan {kuat[k]}" for k in range(3)]
                     + [f"Terkuat: {nama[kuat.index(max(kuat))]}", f"Total: {sum(banyak)}"])


def _rumah(m):
    rumah, a, b = m.split()
    buat = [f"jendela {a}", f"kamar {a}", f"jendela {b}", f"kamar {b}", f"rumah {rumah}"]
    return "\n".join(["+" + x for x in buat] + ["-" + x for x in reversed(buat)])


def _gudang(m):
    b = _baris(m)
    nama, label = b[0].split()[0], b[1].split()
    return "\n".join([f"+gudang {nama}"] + [f"+peti {l}" for l in label] + [f"Gudang berisi {len(label)} peti"]
                     + [f"-peti {l}" for l in label] + [f"-gudang {nama}"])


def _pameran(m):
    b = _baris(m)
    nama, judul = b[0].split()[0], b[1].split()
    pajang = [judul[k - 1] for k in _angka(b[2])[1:]]
    return "\n".join([f"+lukisan {j}" for j in judul] + [f"+pameran {nama}"] + [f"Dipajang: {j}" for j in pajang]
                     + [f"-pameran {nama}", f"Koleksi tetap {len(judul)} lukisan"] + [f"-lukisan {j}" for j in judul])


def _pinjam(m):
    b = _baris(m)
    anggota = b[1].split()[1:]
    dipinjam, hasil = {}, []   # judul -> nama
    for p in _perintah(m, 2):
        if p[0] == "pinjam":
            if p[1] in dipinjam:
                hasil.append(f"Sedang dipinjam {dipinjam[p[1]]}")
            elif list(dipinjam.values()).count(p[2]) == 2:
                hasil.append("Batas pinjaman tercapai")
            else:
                dipinjam[p[1]] = p[2]
                hasil.append(f"Dipinjam {p[2]}")
        elif p[1] in dipinjam:
            del dipinjam[p[1]]
            hasil.append("Dikembalikan")
        else:
            hasil.append("Buku tidak sedang dipinjam")
    return "\n".join(hasil + [f"{a}: {list(dipinjam.values()).count(a)} buku" for a in anggota])


def _jadwal(m):
    b = _baris(m)
    ruang, dosen = b[0].split()[1:], b[1].split()[1:]
    kuliah = [x.split() for x in b[3:]]
    hasil = []
    for i in range(len(kuliah)):
        for j in range(i + 1, len(kuliah)):
            a, c = kuliah[i], kuliah[j]
            if a[3:] != c[3:]:
                continue
            sebab = []
            if a[1] == c[1]:
                sebab.append("ruang " + ruang[int(a[1]) - 1])
            if a[2] == c[2]:
                sebab.append("dosen " + dosen[int(a[2]) - 1])
            if sebab:
                hasil.append(f"{a[0]} dan {c[0]}: " + " dan ".join(sebab))
    return "\n".join(hasil or ["Tidak ada bentrok"])


def _peta(m):
    b = _baris(m)
    kota = b[0].split()[1:]
    jalan = {k: [] for k in kota}
    for x in b[2:]:
        a, c, km = x.split()
        jalan[a].append(int(km))
        jalan[c].append(int(km))
    sibuk = max(kota, key=lambda k: len(jalan[k]))
    return "\n".join([f"{k}: {len(jalan[k])} jalan, {sum(jalan[k])} km" for k in kota] + [f"Tersibuk: {sibuk}"])


def _kompi(m):
    b = _baris(m)
    pos, peleton = 1, []
    for _ in range(int(b[0])):
        g = int(b[pos])
        peleton.append([sum(_angka(x)[1:]) for x in b[pos + 1:pos + 1 + g]])
        pos += 1 + g
    semua = [(k, (p, g)) for p, regu in enumerate(peleton, 1) for g, k in enumerate(regu, 1)]
    k, (p, g) = min(semua, key=lambda x: x[0])
    return "\n".join([f"Peleton {n}: {sum(regu)}" for n, regu in enumerate(peleton, 1)]
                     + [f"Kompi: {sum(sum(r) for r in peleton)}", f"Terlemah: peleton {p} regu {g} ({k})"])


def _tagihan(m):
    b = _baris(m)
    k = int(b[0])
    harga = {x.split()[0]: int(x.split()[1]) for x in b[1:1 + k]}
    pesanan, hasil = OrderedDict(), []
    for nama, jumlah in _perintah(m, 1 + k):
        if nama in harga:
            pesanan[nama] = pesanan.get(nama, 0) + int(jumlah)
        else:
            hasil.append(f"Menu tidak ada: {nama}")
    sub = sum(harga[n] * j for n, j in pesanan.items())
    return "\n".join(hasil + [f"{n} x {j} = {harga[n] * j}" for n, j in pesanan.items()]
                     + [f"Subtotal: {sub}", f"Pajak: {sub // 10}", f"Total: {sub + sub // 10}"])


def _kereta(m):
    b = _baris(m)
    kap = _angka(b[0])[1:]
    isi, hasil = [0] * len(kap), []
    for nomor, orang in enumerate(_angka(b[2]), 1):
        bagian = []
        for k in range(len(kap)):
            naik = min(orang, kap[k] - isi[k])
            if naik > 0:
                isi[k] += naik
                orang -= naik
                bagian.append(f"gerbong {k + 1} ({naik})")
        if orang > 0:
            bagian.append(f"tertinggal {orang}")
        hasil.append(f"Rombongan {nomor}: " + " ".join(bagian))
    return "\n".join(hasil + [f"Gerbong {k + 1}: {isi[k]}/{kap[k]}" for k in range(len(kap))])


def _proyek(m):
    b = _baris(m)
    proyek = OrderedDict((p, []) for p in b[1].split()[1:])
    hasil = []
    for kata, k, p in _perintah(m, 2):
        ikut = sum(k in anggota for anggota in proyek.values())
        if kata == "gabung":
            if k in proyek[p]:
                hasil.append("Sudah bergabung")
            elif ikut == 2:
                hasil.append("Karyawan penuh")
            elif len(proyek[p]) == 3:
                hasil.append("Proyek penuh")
            else:
                proyek[p].append(k)
                hasil.append("Bergabung")
        elif k in proyek[p]:
            proyek[p].remove(k)
            hasil.append("Keluar")
        else:
            hasil.append("Bukan anggota")
    return "\n".join(hasil + [f"{p}: " + (" ".join(a) if a else "-") for p, a in proyek.items()])


def _rakitan(m):
    b = _baris(m)
    nb = int(b[0])
    harga = [int(x.split()[1]) for x in b[1:1 + nb]]
    hasil = []
    for x in b[2 + nb:]:
        k = x.split()
        isi = [int(v) for v in k[3:]]
        hasil.append((k[0], int(k[1]) + sum(harga[isi[p] - 1] * isi[p + 1] for p in range(0, len(isi), 2))))
    return "\n".join([f"{n}: {v}" for n, v in hasil] + [f"Termahal: {max(hasil, key=lambda h: h[1])[0]}"])


def _milik(m):
    bebas, pemilik, hasil = [], OrderedDict(), []   # pemilik: nama -> daftar bagian yang dimiliki
    for p in _perintah(m):
        if p[0] == "bagian":
            bebas.append(p[1])
            hasil.append("+" + p[1])
        elif p[0] == "pemilik":
            pemilik[p[1]] = []
            hasil.append("+" + p[1])
        elif p[0] == "hapus":
            if p[1] in pemilik:
                hasil += ["-" + p[1]] + ["-" + x for x in pemilik.pop(p[1])]
            else:
                hasil.append("Tidak bisa")
        elif p[1] not in pemilik or p[2] not in bebas:
            hasil.append("Tidak bisa")
        elif p[0] == "miliki":
            bebas.remove(p[2])
            pemilik[p[1]].append(p[2])
    for nama, milik in pemilik.items():
        hasil += ["-" + nama] + ["-" + x for x in milik]
    return "\n".join(hasil + ["-" + x for x in bebas])


def _bank(m):
    nasabah, rek, hasil = OrderedDict(), [], []   # nasabah: nama -> daftar indeks rekening; rek: [saldo, pemilik]
    for p in _perintah(m):
        if p[0] == "nasabah":
            if p[1] in nasabah:
                hasil.append("Sudah terdaftar")
            else:
                nasabah[p[1]] = []
                hasil.append(f"Nasabah {p[1]} terdaftar")
        elif p[0] == "buka":
            if p[1] not in nasabah:
                hasil.append("Nasabah tidak ada")
            elif len(nasabah[p[1]]) == 3:
                hasil.append("Batas rekening tercapai")
            else:
                rek.append([int(p[2]), p[1]])
                nasabah[p[1]].append(len(rek) - 1)
                hasil.append(f"Rekening {len(rek)} dibuka")
        elif p[0] == "transfer":
            a, c, x = int(p[1]), int(p[2]), int(p[3])
            if not (1 <= a <= len(rek) and 1 <= c <= len(rek)):
                hasil.append("Rekening tidak ada")
            elif a == c or x > rek[a - 1][0]:
                hasil.append("Ditolak")
            else:
                rek[a - 1][0] -= x
                rek[c - 1][0] += x
                hasil.append("Pindah buku" if rek[a - 1][1] == rek[c - 1][1] else f"Transfer ke {rek[c - 1][1]}")
        else:
            n, x = int(p[1]), int(p[2])
            if not 1 <= n <= len(rek):
                hasil.append("Rekening tidak ada")
            elif p[0] == "tarik" and x > rek[n - 1][0]:
                hasil.append("Ditolak")
            else:
                rek[n - 1][0] += x if p[0] == "setor" else -x
                hasil.append(f"Saldo {n}: {rek[n - 1][0]}")
    return "\n".join(hasil + [f"{n}: {len(r)} rekening, total {sum(rek[k][0] for k in r)}" for n, r in nasabah.items()])


def _bertingkat(m):
    b = _baris(m)
    nb = int(b[0])
    bahan = [(x.split()[0], int(x.split()[1])) for x in b[1:1 + nb]]
    biaya, pertama, nama = [], [], []   # per rakitan: biaya total dan kebutuhan bahan nomor 1
    for x in b[2 + nb:]:
        k = x.split()
        nama.append(k[0])
        total, butuh = 0, 0
        for p in range(2, len(k), 3):
            nomor, jumlah = int(k[p + 1]), int(k[p + 2])
            if k[p] == "B":
                total += bahan[nomor - 1][1] * jumlah
                butuh += jumlah if nomor == 1 else 0
            else:
                total += biaya[nomor - 1] * jumlah
                butuh += pertama[nomor - 1] * jumlah
        biaya.append(total)
        pertama.append(butuh)
    return "\n".join([f"{n}: {v}" for n, v in zip(nama, biaya)] + [f"{bahan[0][0]} untuk {nama[-1]}: {pertama[-1]}"])


def _klinik(m):
    b = _baris(m)
    antre = OrderedDict((d, []) for d in b[0].split()[1:])   # tiap entri: (nama, darurat)
    hasil = []
    for p in _perintah(m, 1):
        if p[0] == "datang":
            dokter = min(antre, key=lambda d: len(antre[d]))
            darurat = p[2] == "darurat"
            posisi = sum(1 for _, dr in antre[dokter] if dr) if darurat else len(antre[dokter])
            antre[dokter].insert(posisi, (p[1], darurat))
            hasil.append(f"{p[1]} -> Dokter {dokter} (urutan {posisi + 1})")
        elif p[1] in antre:
            if antre[p[1]]:
                hasil.append(f"Dokter {p[1]} memeriksa {antre[p[1]].pop(0)[0]}")
            else:
                hasil.append(f"Dokter {p[1]} tidak punya pasien")
    return "\n".join(hasil + [f"{d}: " + (" ".join(n for n, _ in a) if a else "-") for d, a in antre.items()])


JAWAB = {
    "garis-dua-titik": lambda m: (lambda v: str(abs(v[0] - v[2]) + abs(v[1] - v[3])))(_angka(m)),
    "alamat-mahasiswa": lambda m: "{} tinggal di {} ({})".format(*m.split()),
    "mesin-mobil": lambda m: (lambda n, t: f"Mesin {t} dibuat\nMobil {n} dibuat\nMobil {n} dihancurkan\nMesin {t} dihancurkan")(*m.split()),
    "tim-pelatih": lambda m: (lambda t, p: f"Pelatih {p} datang\nTim {t} dibentuk bersama {p}\nTim {t} bubar\nPelatih {p} masih ada\nPelatih {p} pulang")(*m.split()),
    "luas-dari-sudut": lambda m: (lambda v: f"{(v[2] - v[0]) * (v[3] - v[1])} {2 * (v[2] - v[0] + v[3] - v[1])}")(_angka(m)),
    "umur-orang": _umur,
    "dosen-pembimbing": _pembimbing,
    "sepeda-roda": _sepeda,
    "keranjang-belanja": _keranjang,
    "urutan-komponen": lambda m: (lambda n, s, g: f"+Prosesor {s}\n+Memori {g}\n+Komputer {n}\nKomputer menyala\n-Komputer {n}\n-Memori {g}\n-Prosesor {s}")(*m.split()),
    "regu-prajurit": _regu,
    "salinan-atau-rujukan": lambda m: (lambda j, lama, baru: f"Salinan: {j} oleh {lama}\nRujukan: {j} oleh {baru}")(*m.split()),
    "daftar-putar": _putar,
    "segitiga-titik": _segitiga,
    "transkrip-ipk": _ipk,
    "kamar-hotel": _hotel,
    "peleton-regu": _peleton,
    "rumah-kamar": _rumah,
    "gudang-pemilik-peti": _gudang,
    "pameran-lukisan": _pameran,
    "peminjaman-buku": _pinjam,
    "jadwal-kuliah-bentrok": _jadwal,
    "peta-jalan": _peta,
    "struktur-kompi": _kompi,
    "tagihan-meja": _tagihan,
    "kereta-gerbong": _kereta,
    "tim-proyek": _proyek,
    "biaya-rakitan": _rakitan,
    "simulasi-kepemilikan": _milik,
    "bank-nasabah": _bank,
    "rakitan-bertingkat": _bertingkat,
    "klinik-antrean": _klinik,
}

CONTOH = [
    ("garis-dua-titik", "1 2\n4 6\n", "7"),
    ("alamat-mahasiswa", "Citra Bandung 40132\n", "Citra tinggal di Bandung (40132)"),
    ("mesin-mobil", "Kijang 100\n", "Mesin 100 dibuat\nMobil Kijang dibuat\nMobil Kijang dihancurkan\nMesin 100 dihancurkan"),
    ("tim-pelatih", "Garuda Budi\n", "Pelatih Budi datang\nTim Garuda dibentuk bersama Budi\nTim Garuda bubar\nPelatih Budi masih ada\nPelatih Budi pulang"),
    ("luas-dari-sudut", "1 1\n5 4\n", "12 14"),
    ("umur-orang", "Andi\n15 6 2000\n14 6 2026\n", "Andi berumur 25 tahun"), ("umur-orang", "Andi\n15 6 2000\n15 6 2026\n", "Andi berumur 26 tahun"),
    ("dosen-pembimbing", "Andi\n1\nSari\n", "Andi dibimbing Sari"), ("dosen-pembimbing", "Andi\n0\n", "Andi belum punya pembimbing"),
    ("sepeda-roda", "35 20\n", "pompa roda belakang"),
    ("keranjang-belanja", "2\nbuku 12000 2\npena 3500 4\n", "buku: 24000\npena: 14000\nTotal: 38000"),
    ("urutan-komponen", "Lab i5 16\n", "+Prosesor i5\n+Memori 16\n+Komputer Lab\nKomputer menyala\n-Komputer Lab\n-Memori 16\n-Prosesor i5"),
    ("regu-prajurit", "3\nAndi 50\nBudi 80\nCitra 60\n2\n1 3\n3 25\n", "110\n85"),
    ("salinan-atau-rujukan", "Fisika Budi Citra\n", "Salinan: Fisika oleh Budi\nRujukan: Fisika oleh Citra"),
    ("daftar-putar", "3\nPagi 200\nSenja 245\nHujan 185\n", "Total: 10:30\nTerpanjang: Senja"),
    ("segitiga-titik", "0 0\n4 0\n0 3\n", "Luas x 2 = 12"), ("segitiga-titik", "0 0\n2 2\n5 5\n", "Ketiga titik segaris"),
    ("transkrip-ipk", "3\nMK101 3 A\nMK102 2 C\nMK103 4 B\n", "SKS: 9\nIPK: 3.11"),
    ("kamar-hotel", "masuk 2 Andi\nmasuk 2 Budi\nkeluar 4\nkeluar 2\nmasuk 5 Citra\nselesai\n",
     "Kamar 2: Andi\nKamar 2 sudah terisi\nKamar 4 memang kosong\nKamar 2 dikosongkan\nKamar 5: Citra\n1: -\n2: -\n3: -\n4: -\n5: Citra"),
    ("peleton-regu", "Alfa Bravo Charlie\n3\nAndi 50 1\nBudi 80 3\nCitra 60 1\n",
     "Alfa: 2 prajurit, kekuatan 110\nBravo: 0 prajurit, kekuatan 0\nCharlie: 1 prajurit, kekuatan 80\nTerkuat: Alfa\nTotal: 3"),
    ("rumah-kamar", "Melati tamu tidur\n", "+jendela tamu\n+kamar tamu\n+jendela tidur\n+kamar tidur\n+rumah Melati\n-rumah Melati\n-kamar tidur\n-jendela tidur\n-kamar tamu\n-jendela tamu"),
    ("gudang-pemilik-peti", "Utara 2\nmeja kursi\n", "+gudang Utara\n+peti meja\n+peti kursi\nGudang berisi 2 peti\n-peti meja\n-peti kursi\n-gudang Utara"),
    ("pameran-lukisan", "Alam 2\nSenja Ombak\n1 2\n", "+lukisan Senja\n+lukisan Ombak\n+pameran Alam\nDipajang: Ombak\n-pameran Alam\nKoleksi tetap 2 lukisan\n-lukisan Senja\n-lukisan Ombak"),
    ("peminjaman-buku", "3 Fisika Logika Biologi\n2 Andi Budi\npinjam Fisika Andi\npinjam Fisika Budi\npinjam Logika Andi\npinjam Biologi Andi\nkembali Fisika\nkembali Fisika\nselesai\n",
     "Dipinjam Andi\nSedang dipinjam Andi\nDipinjam Andi\nBatas pinjaman tercapai\nDikembalikan\nBuku tidak sedang dipinjam\nAndi: 1 buku\nBudi: 0 buku"),
    ("jadwal-kuliah-bentrok", "2 R101 Lab1\n2 Andi Budi\n4\nMK1 1 1 2 8\nMK2 1 2 2 8\nMK3 2 1 2 8\nMK4 1 1 3 8\n", "MK1 dan MK2: ruang R101\nMK1 dan MK3: dosen Andi"),
    ("peta-jalan", "3 Bandung Garut Solo\n2\nBandung Garut 60\nBandung Solo 450\n", "Bandung: 2 jalan, 510 km\nGarut: 1 jalan, 60 km\nSolo: 1 jalan, 450 km\nTersibuk: Bandung"),
    ("struktur-kompi", "2\n2\n2 50 60\n1 40\n1\n3 30 30 30\n", "Peleton 1: 150\nPeleton 2: 90\nKompi: 240\nTerlemah: peleton 1 regu 2 (40)"),
    ("tagihan-meja", "2\nnasi 15000\nteh 5000\nnasi 2\nkopi 1\nteh 3\nnasi 1\nselesai\n", "Menu tidak ada: kopi\nnasi x 3 = 45000\nteh x 3 = 15000\nSubtotal: 60000\nPajak: 6000\nTotal: 66000"),
    ("kereta-gerbong", "2 4 3\n3\n3 5 2\n", "Rombongan 1: gerbong 1 (3)\nRombongan 2: gerbong 1 (1) gerbong 2 (3) tertinggal 1\nRombongan 3: tertinggal 2\nGerbong 1: 4/4\nGerbong 2: 3/3"),
    ("tim-proyek", "2 Andi Budi\n2 Atlas Delta\ngabung Andi Atlas\ngabung Andi Atlas\ngabung Budi Atlas\nkeluar Budi Delta\nkeluar Andi Atlas\nselesai\n",
     "Bergabung\nSudah bergabung\nBergabung\nBukan anggota\nKeluar\nAtlas: Budi\nDelta: -"),
    ("biaya-rakitan", "2\npapan 20000\nbaut 500\n2\nRak 10000 2 1 3 2 12\nKursi 5000 1 1 1\n", "Rak: 76000\nKursi: 25000\nTermahal: Rak"),
    ("simulasi-kepemilikan", "bagian a\nbagian b\npemilik P\nmiliki P a\npinjam P b\nmiliki P a\nhapus P\nselesai\n", "+a\n+b\n+P\nTidak bisa\n-P\n-a\n-b"),
    ("bank-nasabah", "nasabah Andi\nnasabah Budi\nbuka Andi 100000\nbuka Andi 0\nbuka Budi 50000\ntransfer 1 2 30000\ntransfer 1 3 50000\ntarik 1 50000\nselesai\n",
     "Nasabah Andi terdaftar\nNasabah Budi terdaftar\nRekening 1 dibuka\nRekening 2 dibuka\nRekening 3 dibuka\nPindah buku\nTransfer ke Budi\nDitolak\nAndi: 2 rekening, total 50000\nBudi: 1 rekening, total 100000"),
    ("rakitan-bertingkat", "2\nbaut 500\npapan 10000\n2\nR1 2 B 1 4 B 2 1\nR2 2 R 1 3 B 1 2\n", "R1: 12000\nR2: 37000\nbaut untuk R2: 14"),
    ("klinik-antrean", "2 Hasan Ratna\ndatang Andi biasa\ndatang Budi biasa\ndatang Citra darurat\ndatang Dewi darurat\npanggil Hasan\npanggil Wati\nselesai\n",
     "Andi -> Dokter Hasan (urutan 1)\nBudi -> Dokter Ratna (urutan 1)\nCitra -> Dokter Hasan (urutan 1)\nDewi -> Dokter Ratna (urutan 1)\nDokter Hasan memeriksa Citra\nHasan: Andi\nRatna: Dewi Budi"),
]
