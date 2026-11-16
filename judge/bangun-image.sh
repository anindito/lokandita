#!/usr/bin/env bash
# Membangun judge-c, judge-cpp, judge-python dan judge-analisis (SPEC.md §9, §10). Membutuhkan
# jaringan hanya di sini (apt dan PyPI); kontainer judge tidak pernah punya jaringan.
set -euo pipefail
cd "$(dirname "${BASH_SOURCE[0]}")"
exec python3 -m lokandita_judge.citra bangun
