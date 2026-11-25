#!/usr/bin/env bash
# Fungsi bersama skrip infra/. Di-source, tidak dijalankan sendiri.
set -euo pipefail

AKAR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
export AKAR

# Komponen yang punya .env sendiri (KONTRAK.md §10).
KOMPONEN=(infra apps/auth apps/lokakode apps/portal apps/lokaujian judge)
APLIKASI=(auth lokakode portal lokaujian)

gagal() {
	echo "GAGAL: $*" >&2
	exit 1
}

compose() {
	docker compose --project-directory "$AKAR/infra" -f "$AKAR/infra/compose.yml" "$@"
}

# nilai_env <berkas> <KUNCI> — mencetak nilainya ke stdout. Jangan pernah
# meneruskan keluaran fungsi ini ke layar untuk kunci rahasia.
nilai_env() {
	sed -n "s/^$2=//p" "$1" | head -n 1
}

# Host publik: portal, www, auth, lokakode, lokaujian.
daftar_host() {
	local domain
	domain="$(nilai_env "$AKAR/infra/.env" DOMAIN)"
	[ -n "$domain" ] || gagal "DOMAIN kosong di infra/.env"
	echo "$domain www.$domain auth.$domain lokakode.$domain lokaujian.$domain"
}

# host_aplikasi <auth|lokakode|portal|lokaujian>
host_aplikasi() {
	local domain
	domain="$(nilai_env "$AKAR/infra/.env" DOMAIN)"
	case "$1" in
		portal) echo "$domain" ;;
		auth | lokakode | lokaujian) echo "$1.$domain" ;;
		*) gagal "aplikasi tidak dikenal: $1" ;;
	esac
}

# Benar bila host aplikasi itu sudah dibuka (tidak dalam mode pemeliharaan).
sudah_buka() {
	[ -e "$AKAR/infra/caddy/bendera/$1.buka" ]
}

# ---- judge worker (layanan systemd pengguna, KONTRAK.md §6) ----
LAYANAN_JUDGE=lokandita-judge.service
export XDG_RUNTIME_DIR="${XDG_RUNTIME_DIR:-/run/user/$(id -u)}"

# Denyut worker dari Redis DB 0 (JSON), atau kosong bila worker tidak berjalan.
denyut_judge() {
	docker exec redis redis-cli -n 0 get lokandita:judge:worker 2> /dev/null || true
}

# Denyut penjadwal LokaUjian dari Redis DB 3 (JSON, KONTRAK.md §21), atau kosong bila tidak berjalan.
denyut_jadwal() {
	docker exec redis redis-cli -n 3 get lokaujian:jadwal 2> /dev/null || true
}

# Benar bila layanan pengguna tetap hidup tanpa ada yang login.
linger_aktif() {
	[ "$(loginctl show-user "$(id -un)" -p Linger --value 2> /dev/null)" = "yes" ]
}
