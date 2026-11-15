#!/usr/bin/env bash
# Fungsi bersama skrip infra/. Di-source, tidak dijalankan sendiri.
set -euo pipefail

AKAR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
export AKAR

# Komponen yang punya .env sendiri (KONTRAK.md §10). judge/ menyusul di tahap 7.
KOMPONEN=(infra apps/auth apps/lokakode apps/portal)
APLIKASI=(auth lokakode portal)

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

# Host publik: portal, www, auth, lokakode.
daftar_host() {
	local domain
	domain="$(nilai_env "$AKAR/infra/.env" DOMAIN)"
	[ -n "$domain" ] || gagal "DOMAIN kosong di infra/.env"
	echo "$domain www.$domain auth.$domain lokakode.$domain"
}

# host_aplikasi <auth|lokakode|portal>
host_aplikasi() {
	local domain
	domain="$(nilai_env "$AKAR/infra/.env" DOMAIN)"
	case "$1" in
		portal) echo "$domain" ;;
		auth | lokakode) echo "$1.$domain" ;;
		*) gagal "aplikasi tidak dikenal: $1" ;;
	esac
}

# Benar bila host aplikasi itu sudah dibuka (tidak dalam mode pemeliharaan).
sudah_buka() {
	[ -e "$AKAR/infra/caddy/bendera/$1.buka" ]
}
