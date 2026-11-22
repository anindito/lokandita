#!/usr/bin/env python3
"""Lembar tinjauan untuk pengampu dari satu berkas impor soal.

    python3 docs/soal/lembar-tinjauan.py docs/soal/<matkul>-pertemuan-<n>.json

Menulis docs/soal/tinjauan/<matkul>-pertemuan-<n>.md: jumlah soal per tingkat,
daftar semua soal, dan pernyataan Indonesia lengkap dari dua soal per tingkat
(yang pertama dan yang terakhir di tingkat itu). Isinya diambil dari berkas
impor apa adanya; `{{param}}` sudah diisi.
"""
import json
import sys
from pathlib import Path

TINGKAT = [("mudah_sekali", "Mudah sekali", 3), ("mudah", "Mudah", 5), ("mudah_sedang", "Mudah-sedang", 8),
           ("sedang", "Sedang", 10), ("sulit", "Sulit", 15)]


def isi(teks: str, params: dict) -> str:
    for k, v in params.items():
        teks = teks.replace("{{" + k + "}}", str(v))
    return teks


def main() -> None:
    berkas = Path(sys.argv[1]).resolve()
    data = json.loads(berkas.read_text(encoding="utf-8"))
    soal = data["problems"]
    b = [f"# Lembar tinjauan — {data['course']} pertemuan {data['meeting']}", "",
         f"Sumber: `docs/soal/{berkas.name}`. Status setelah impor: menunggu tinjauan (`PENDING_REVIEW`).", "",
         "| Tingkat | Jumlah | Poin |", "|---|---|---|"]
    total = 0
    for kode, nama, poin in TINGKAT:
        n = sum(1 for s in soal if s["difficulty"] == kode)
        total += n * poin
        b.append(f"| {nama} | {n} | {n * poin} |")
    b += [f"| **Semua** | **{len(soal)}** | **{total}** |", "", "## Semua soal", ""]
    for kode, nama, _ in TINGKAT:
        b.append(f"- **{nama}:** " + "; ".join(
            f"{s['translations']['id']['title']} (`{s['slug']}`)" for s in soal if s["difficulty"] == kode))
    b += ["", "## Pernyataan lengkap: dua soal per tingkat", ""]
    for kode, nama, _ in TINGKAT:
        tingkat = [s for s in soal if s["difficulty"] == kode]
        for s in ([tingkat[0], tingkat[-1]] if len(tingkat) > 1 else tingkat):
            t, p = s["translations"]["id"], s.get("params", {})
            b += [f"### {nama} — {t['title']} (`{s['slug']}`)", "", isi(t["statement"], p), "",
                  f"**Masukan.** {isi(t['input_format'], p)}", "", f"**Keluaran.** {isi(t['output_format'], p)}", "",
                  f"**Batasan.** {isi(t['constraints'], p)}", ""]
            if s["required_concepts_in_submission"]:
                b += ["**Konsep yang wajib ada di kode mahasiswa.** " + ", ".join(f"`{k}`" for k in s["required_concepts_in_submission"]), ""]
            b += [f"Mode banding `{s['comparison_mode']}`, batas waktu {s['time_limit_ms']} ms, "
                  f"{s['samples_count']} tes contoh dan {s['hidden_count']} tes tersembunyi.", ""]
    tujuan = berkas.parent / "tinjauan" / (berkas.stem + ".md")
    tujuan.parent.mkdir(exist_ok=True)
    tujuan.write_text("\n".join(b), encoding="utf-8")
    print(f"{tujuan.relative_to(berkas.parents[2])}: {len(soal)} soal, {total} poin")


if __name__ == "__main__":
    main()
