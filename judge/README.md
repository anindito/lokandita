# judge

Worker penilai (Python 3.12) — `SPEC.md` §10, `KONTRAK.md` §6.

- `lokandita_judge/` — kode worker.
- `tests/` — pytest terhadap MySQL, Redis dan Docker sungguhan. Jalankan hanya dengan `infra/tes.sh judge`.
- `siapkan-venv.sh` — membuat `judge/.venv` dari `requirements.txt` (versi dan hash dikunci).
- `.env.example` — variabel wajib (`KONTRAK.md` §10).
- `images/` — Dockerfile `judge-c`, `judge-cpp`, `judge-python`, dan `pelari.c` (pengawas di dalam kontainer). Bangun dengan `judge/bangun-image.sh`.
- `seccomp/` — `judge.json` dibuat oleh `buat-profil.py` dari profil bawaan Docker; jangan disunting dengan tangan.
