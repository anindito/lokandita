# judge

Worker penilai (Python 3.12) — `SPEC.md` §10, `KONTRAK.md` §6.

- `lokandita_judge/` — kode worker.
- `tests/` — pytest terhadap MySQL, Redis dan Docker sungguhan. Jalankan hanya dengan `infra/tes.sh judge`.
- `siapkan-venv.sh` — membuat `judge/.venv` dari `requirements.txt` (versi dan hash dikunci).
- `.env.example` — variabel wajib (`KONTRAK.md` §10).
