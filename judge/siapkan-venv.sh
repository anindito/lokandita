#!/usr/bin/env bash
# Membuat judge/.venv dan memasang pustaka yang dikunci (versi dan hash).
# Host tidak punya pip; venv membawa pip-nya sendiri. Aman dijalankan berulang.
#
#   judge/siapkan-venv.sh        pustaka worker saja
#   judge/siapkan-venv.sh tes    ditambah pytest
set -euo pipefail
cd "$(dirname "${BASH_SOURCE[0]}")"

[ -x .venv/bin/python ] || python3 -m venv .venv
berkas=(-r requirements.txt)
[ "${1:-}" = "tes" ] && berkas+=(-r requirements-tes.txt)
.venv/bin/python -m pip install --quiet --disable-pip-version-check --require-hashes "${berkas[@]}"
echo "judge/.venv siap: $(.venv/bin/python --version), $(.venv/bin/python -m pip list --format=freeze 2>/dev/null | grep -c -v '^pip==') pustaka"
