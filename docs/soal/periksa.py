#!/usr/bin/env python3
"""Memeriksa satu berkas impor soal SEBELUM diimpor. Tidak menulis ke basis data.

    judge/.venv/bin/python docs/soal/periksa.py docs/soal/<matkul>-pertemuan-<n>.json KONSEP,KONSEP,…

Argumen kedua adalah konsep yang diizinkan di pertemuan itu (kumulatif), dibaca
dari produksi oleh penyusun. Untuk tiap soal, di kontainer judge yang sama
dengan yang dipakai importer:

1. `soal.validasi` — pemeriksaan yang sama dengan pekerjaan VALIDATE importer:
   solusi referensi terkompilasi, hanya memakai konsep yang diizinkan, generator
   deterministik, 20 seed acak selesai tanpa galat. Peringatan dihitung gagal.
2. Keluaran solusi referensi untuk tes dari seed 1, 2, 3 dibandingkan dengan
   perhitungan terpisah di `cek-<matkul>-pertemuan-<n>.py` (JAWAB[slug], Python,
   ditulis tanpa melihat solusi C++-nya), memakai mode banding soal itu.
3. Setiap contoh yang dikutip di pernyataan soal (CONTOH di berkas cek yang sama)
   dijalankan pada solusi referensi dan dibandingkan dengan keluaran yang dikutip.
4. Konsep yang disyaratkan di kode mahasiswa harus ada di solusi referensi sendiri.
5. Dua tes contoh sebuah soal tidak boleh sama.
"""
import importlib.util
import json
import sys
from concurrent.futures import ThreadPoolExecutor
from pathlib import Path

AKAR = Path(__file__).resolve().parents[2]
sys.path.insert(0, str(AKAR / "judge"))
from lokandita_judge import banding, kotak, soal as soal_judge  # noqa: E402

SEED_BANDING = [1, 2, 3]


def periksa_satu(p: dict, bahasa: str, diizinkan: list[str], jawab, contoh: list[tuple[str, str]]) -> dict:
    galat: list[str] = []
    n = p["samples_count"] + p["hidden_count"]
    mode = p["comparison_mode"]
    v = soal_judge.validasi({
        "language": bahasa, "generator": p["generator"], "reference_solution": p["reference_solution"],
        "samples_count": p["samples_count"], "hidden_count": p["hidden_count"], "time_limit_ms": p["time_limit_ms"],
        "allowed_concepts": diizinkan, "comparison_mode": mode,
    })
    for a in v["reasons"]:
        galat.append("validasi: " + json.dumps(a, ensure_ascii=False))
    for a in v["warnings"]:
        galat.append("peringatan: " + json.dumps(a, ensure_ascii=False))
    kurang = sorted(set(p["required_concepts_in_submission"]) - set(v["concepts"]))
    if kurang:
        galat.append(f"konsep yang disyaratkan tidak ada di solusi referensi: {kurang}")

    dibanding = 0
    if jawab is None:
        galat.append("tidak ada perhitungan terpisah (JAWAB) untuk soal ini")
    d = kotak.siapkan_dir_job()
    try:
        k = kotak.kompilasi(bahasa, p["reference_solution"], d)
        if not k.ok:
            galat.append("solusi referensi tidak terkompilasi")
        else:
            gen = kotak.jalankan_generator(p["generator"], SEED_BANDING, n, d)
            if any(h.status != "OK" for h in gen):
                galat.append("generator gagal pada seed 1–3")
            else:
                masukan = [h.stdout for h in gen] + [c[0].encode("utf-8") for c in contoh]
                for j in range(len(SEED_BANDING)):
                    sampel = masukan[j * n:j * n + p["samples_count"]]
                    if len(set(sampel)) < len(sampel):
                        galat.append(f"tes contoh kembar pada seed {SEED_BANDING[j]}")
                hasil = kotak.jalankan_kelompok(bahasa, d, masukan, cpu_ms=p["time_limit_ms"])
                for h, m in zip(hasil, masukan):
                    teks_masuk = m.decode("utf-8")
                    if h.status != "OK":
                        galat.append(f"solusi referensi {h.status} untuk masukan {teks_masuk!r}")
                        continue
                    keluar = h.stdout.decode("utf-8")
                    if h.nomor >= len(gen):
                        harap = contoh[h.nomor - len(gen)][1]
                        if not banding.sama(keluar, harap, mode):
                            galat.append(f"contoh di pernyataan: masukan {teks_masuk!r} → {keluar!r}, pernyataan menyebut {harap!r}")
                    if jawab is not None:
                        harap = jawab(teks_masuk)
                        dibanding += 1
                        if not banding.sama(keluar, harap, mode):
                            galat.append(f"beda dengan perhitungan terpisah: masukan {teks_masuk!r} → referensi {keluar!r}, terpisah {harap!r}")
    finally:
        kotak.buang_dir_job(d)
    return {"slug": p["slug"], "galat": galat, "tes_validasi": v["tests"], "dibanding": dibanding,
            "contoh": len(contoh), "konsep": v["concepts"], "cpu_ms": v["max_cpu_ms"]}


def main() -> int:
    berkas = Path(sys.argv[1]).resolve()
    diizinkan = [k for k in sys.argv[2].split(",") if k]
    data = json.loads(berkas.read_text(encoding="utf-8"))
    jalur_cek = berkas.with_name("cek-" + berkas.stem + ".py")
    spek = importlib.util.spec_from_file_location("cek", jalur_cek)
    cek = importlib.util.module_from_spec(spek)
    spek.loader.exec_module(cek)

    slug = {p["slug"] for p in data["problems"]}
    asing = sorted((set(cek.JAWAB) | {c[0] for c in cek.CONTOH}) - slug)
    if asing:
        print(f"GAGAL: berkas cek menyebut slug yang tidak ada: {asing}")
        return 1

    def kerja(p):
        contoh = [(c[1], c[2]) for c in cek.CONTOH if c[0] == p["slug"]]
        try:
            return periksa_satu(p, data["language"], diizinkan, cek.JAWAB.get(p["slug"]), contoh)
        except Exception as e:  # galat infrastruktur dilaporkan per soal, tidak menghentikan yang lain
            return {"slug": p["slug"], "galat": [f"{type(e).__name__}: {e}"], "tes_validasi": 0, "dibanding": 0,
                    "contoh": len(contoh), "konsep": [], "cpu_ms": 0}

    with ThreadPoolExecutor(max_workers=2) as kolam:
        hasil = list(kolam.map(kerja, data["problems"]))

    gagal = 0
    for p, h in zip(data["problems"], hasil):
        status = "GAGAL" if h["galat"] else "lulus"
        gagal += bool(h["galat"])
        print(f"{status}  {p['difficulty']:<13} {h['slug']:<28} validasi {h['tes_validasi']} tes, terpisah {h['dibanding']} sama, "
              f"contoh {h['contoh']}, {h['cpu_ms']} ms, {','.join(sorted(h['konsep']))}")
        for g in h["galat"]:
            print("       - " + g[:600])
    print(f"\n{berkas.name}: {len(hasil) - gagal} dari {len(hasil)} soal lulus; "
          f"contoh pernyataan diperiksa: {sum(h['contoh'] for h in hasil)}; "
          f"konsep diizinkan: {','.join(diizinkan)}")
    return 1 if gagal else 0


if __name__ == "__main__":
    sys.exit(main())
