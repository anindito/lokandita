"""Perhitungan terpisah untuk PBO301 pertemuan 1, dipakai oleh periksa.py.

JAWAB[slug](masukan) dihitung dengan Python dan dengan cara yang berbeda dari
solusi referensi C++ (sorted, statistics, Counter, int tak terbatas, numpy
tidak dipakai). CONTOH memuat setiap contoh yang dikutip di pernyataan soal.
"""
import itertools
import statistics
from collections import Counter, deque


def _angka(m):
    return [int(x) for x in m.split()]


def _isi(m):
    return _angka(m.split("\n", 1)[1])


def _baris(m):
    return [b for b in m.split("\n") if b != ""]


def _lampu(m):
    urutan = ["merah", "hijau", "kuning"]
    return urutan[(urutan.index(m.strip()) + 1) % 3]


def _frekuensi(m):
    c = Counter(m.strip())
    return "\n".join(f"{d}: {c[d]}" for d in sorted(c))


def _tertinggi(m):
    data = [b.split() for b in _baris(m)[1:]]
    return max(data, key=lambda d: int(d[1]))[0]  # max mengembalikan yang pertama bila seri


def _atas_rata(m):
    v = _isi(m)
    return str(sum(1 for x in v if x > statistics.fmean(v) + 1e-9))


def _kedua(m):
    nilai = sorted(set(_isi(m)))
    return str(nilai[-2]) if len(nilai) > 1 else "tidak ada"


def _durasi(m):
    total = sum(j * 3600 + mn * 60 + d for j, mn, d in (_angka(b) for b in _baris(m)[1:]))
    return f"{total // 3600}:{total % 3600 // 60:02d}:{total % 60:02d}"


def _gabung(m):
    b = _baris(m)
    return " ".join(map(str, sorted(_angka(b[1]) + _angka(b[2]))))


def _modus(m):
    c = Counter(_isi(m))
    banyak = max(c.values())
    return f"{min(x for x in c if c[x] == banyak)} {banyak}"


def _rotasi(m):
    b = _baris(m)
    n, k = _angka(b[0])
    d = deque(_angka(b[1]))
    d.rotate(-(k % n))
    return " ".join(map(str, d))


def _kali(m):
    b = _baris(m)
    p, t, k = _angka(b[0])
    a = [_angka(x) for x in b[1:1 + p]]
    bb = [_angka(x) for x in b[1 + p:1 + p + t]]
    kolom = list(zip(*bb))
    return "\n".join(" ".join(str(sum(x * y for x, y in zip(baris, kol))) for kol in kolom) for baris in a)


def _peringkat(m):
    data = [(b.split()[0], int(b.split()[1])) for b in _baris(m)[1:]]
    data.sort(key=lambda d: (-d[1], d[0]))
    return "\n".join(f"{k}. {n} {v}" for k, (n, v) in enumerate(data, 1))


def _pascal(n):
    baris, hasil = [1], []
    for _ in range(n):
        hasil.append(" ".join(map(str, baris)))
        baris = [1] + [a + b for a, b in zip(baris, baris[1:])] + [1]
    return "\n".join(hasil)


def _antrean(m):
    antre, hasil = deque(), []
    for b in _baris(m):
        if b == "selesai":
            break
        if b.startswith("datang"):
            antre.append(b.split()[1])
        else:
            hasil.append("Melayani " + antre.popleft() if antre else "Antrean kosong")
    return "\n".join(hasil + [f"Sisa antrean: {len(antre)}"])


def _spiral(m):
    g = [_angka(b) for b in _baris(m)[1:]]
    hasil = []
    while g:
        hasil += g.pop(0)
        g = [list(b) for b in zip(*g)][::-1]  # putar sisa matriks berlawanan arah jarum jam
    return " ".join(map(str, hasil))


def _hidup(m):
    b = _baris(m)
    p, k = _angka(b[0])
    g = b[1:1 + p]
    hidup = {(x, y) for x in range(p) for y in range(k) if g[x][y] == "#"}
    def tetangga(x, y):
        return sum((x + dx, y + dy) in hidup for dx in (-1, 0, 1) for dy in (-1, 0, 1)) - ((x, y) in hidup)
    return "\n".join("".join("#" if tetangga(x, y) == 3 or ((x, y) in hidup and tetangga(x, y) == 2) else "." for y in range(k)) for x in range(p))


def _sudoku(m):
    g = [_angka(b) for b in _baris(m)]
    penuh = set(range(1, 10))
    kelompok = g + [list(k) for k in zip(*g)] + [[g[3 * a + x][3 * b + y] for x in range(3) for y in range(3)] for a in range(3) for b in range(3)]
    return "sah" if all(set(k) == penuh for k in kelompok) else "tidak sah"


def _ratu(m):
    n, k = _angka(m)
    banyak = 0
    for p in itertools.permutations(range(n)):
        if p[0] == k - 1 and len({p[b] + b for b in range(n)}) == n and len({p[b] - b for b in range(n)}) == n:
            banyak += 1
    return str(banyak)


JAWAB = {
    "terpanas-terdingin": lambda m: f"{max(_angka(m))} {min(_angka(m))}",
    "hitung-huruf-besar": lambda m: str(sum(c.isupper() for c in m)),
    "kuadratkan-lima": lambda m: " ".join(str(x ** 2) for x in _angka(m)),
    "rata-rata-sepuluh": lambda m: f"{statistics.fmean(_angka(m)):.2f}",
    "ganti-spasi": lambda m: m.strip("\n").replace(" ", "_"),
    "lampu-berikutnya": _lampu,
    "nolkan-negatif": lambda m: " ".join(str(max(0, x)) for x in _angka(m)),
    "jumlah-vektor": lambda m: (lambda v: f"{v[0] + v[2]} {v[1] + v[3]}")(_angka(m)),
    "balik-array": lambda m: " ".join(map(str, reversed(_isi(m)))),
    "frekuensi-digit": _frekuensi,
    "nilai-tertinggi": _tertinggi,
    "di-atas-rata-rata": _atas_rata,
    "kata-terpanjang": lambda m: max(m.split(), key=len),
    "jumlah-diagonal": lambda m: (lambda g: f"{sum(g[k][k] for k in range(len(g)))} {sum(g[k][-1 - k] for k in range(len(g)))}")([_angka(b) for b in _baris(m)[1:]]),
    "terbesar-kedua": _kedua,
    "hapus-vokal": lambda m: "".join(c for c in m.strip("\n") if c.lower() not in "aiueo"),
    "urutkan-array": lambda m: " ".join(map(str, sorted(_isi(m)))),
    "median": lambda m: f"{statistics.median(_isi(m)):.1f}",
    "total-durasi": _durasi,
    "gabung-terurut": _gabung,
    "modus": _modus,
    "rotasi-kiri": _rotasi,
    "kali-matriks": _kali,
    "peringkat-kelas": _peringkat,
    "jumlah-bilangan-besar": lambda m: str(sum(_angka(m))),
    "segitiga-pascal": lambda m: _pascal(int(m)),
    "anagram": lambda m: "anagram" if Counter(m.split()[0]) == Counter(m.split()[1]) else "bukan anagram",
    "antrean-bank": _antrean,
    "spiral-matriks": _spiral,
    "permainan-hidup": _hidup,
    "sudoku-sah": _sudoku,
    "ratu-pertama": _ratu,
}

_SUDOKU = ("5 3 4 6 7 8 9 1 2\n6 7 2 1 9 5 3 4 8\n1 9 8 3 4 2 5 6 7\n8 5 9 7 6 1 4 2 3\n4 2 6 8 5 3 7 9 1\n"
           "7 1 3 9 2 4 8 5 6\n9 6 1 5 3 7 2 8 4\n2 8 7 4 1 9 6 3 5\n3 4 5 2 8 6 1 7 9\n")

CONTOH = [
    ("terpanas-terdingin", "30 28 33 25 31 29 27\n", "33 25"),
    ("hitung-huruf-besar", "Belajar KODE di kampus\n", "5"),
    ("kuadratkan-lima", "1 -2 3 10 0\n", "1 4 9 100 0"),
    ("rata-rata-sepuluh", "70 80 90 60 75 85 95 65 55 100\n", "77.50"),
    ("ganti-spasi", "laporan tugas akhir\n", "laporan_tugas_akhir"),
    ("lampu-berikutnya", "merah\n", "hijau"), ("lampu-berikutnya", "hijau\n", "kuning"), ("lampu-berikutnya", "kuning\n", "merah"),
    ("nolkan-negatif", "5 -3 0 12 -7 8 -1 4\n", "5 0 0 12 0 8 0 4"),
    ("jumlah-vektor", "3 4\n-1 6\n", "2 10"),
    ("balik-array", "4\n3 8 -2 5\n", "5 -2 8 3"),
    ("frekuensi-digit", "2026202\n", "0: 2\n2: 4\n6: 1"),
    ("nilai-tertinggi", "3\nAndi 80\nCitra 95\nEko 95\n", "Citra"),
    ("di-atas-rata-rata", "4\n60 70 80 90\n", "2"),
    ("kata-terpanjang", "kode program komputer kampus\n", "komputer"),
    ("jumlah-diagonal", "3\n1 2 3\n4 5 6\n7 8 9\n", "15 15"),
    ("terbesar-kedua", "4\n4 9 9 7\n", "7"), ("terbesar-kedua", "3\n5 5 5\n", "tidak ada"),
    ("hapus-vokal", "Belajar Kode\n", "Bljr Kd"),
    ("urutkan-array", "5\n5 -1 9 3 3\n", "-1 3 3 5 9"),
    ("median", "3\n7 1 5\n", "5.0"), ("median", "4\n8 2 4 6\n", "5.0"), ("median", "4\n1 2 3 6\n", "2.5"),
    ("total-durasi", "2\n0 45 50\n1 20 15\n", "2:06:05"),
    ("gabung-terurut", "3 4\n1 4 9\n2 3 10 12\n", "1 2 3 4 9 10 12"),
    ("modus", "5\n7 3 7 3 9\n", "3 2"),
    ("rotasi-kiri", "5 2\n1 2 3 4 5\n", "3 4 5 1 2"), ("rotasi-kiri", "5 7\n1 2 3 4 5\n", "3 4 5 1 2"),
    ("kali-matriks", "2 2 2\n1 2\n3 4\n5 6\n7 8\n", "19 22\n43 50"),
    ("peringkat-kelas", "3\nBudi 80\nCitra 95\nAndi 80\n", "1. Citra 95\n2. Andi 80\n3. Budi 80"),
    ("jumlah-bilangan-besar", "999\n1\n", "1000"),
    ("jumlah-bilangan-besar", "12345678901234567890\n98765432109876543210\n", "111111111011111111100"),
    ("segitiga-pascal", "5\n", "1\n1 1\n1 2 1\n1 3 3 1\n1 4 6 4 1"),
    ("anagram", "kasur rusak\n", "anagram"), ("anagram", "malam lamam\n", "anagram"), ("anagram", "buku kubus\n", "bukan anagram"),
    ("antrean-bank", "datang Budi\ndatang Citra\nlayani\nlayani\nlayani\ndatang Eko\nselesai\n", "Melayani Budi\nMelayani Citra\nAntrean kosong\nSisa antrean: 1"),
    ("spiral-matriks", "3 4\n1 2 3 4\n5 6 7 8\n9 10 11 12\n", "1 2 3 4 8 12 11 10 9 5 6 7"),
    ("permainan-hidup", "3 3\n.#.\n.#.\n.#.\n", "...\n###\n..."),
    ("sudoku-sah", _SUDOKU, "sah"),
    ("ratu-pertama", "4 2\n", "1"), ("ratu-pertama", "4 1\n", "0"), ("ratu-pertama", "8 1\n", "4"),
]
