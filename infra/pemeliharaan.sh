#!/usr/bin/env bash
# Mode pemeliharaan per host. Bawaan: pemeliharaan menyala (aman).
#
#   infra/pemeliharaan.sh status
#   infra/pemeliharaan.sh <auth|lokakode|portal|lokaujian> on    -> halaman pemeliharaan (503)
#   infra/pemeliharaan.sh <auth|lokakode|portal|lokaujian> off   -> Caddy meneruskan ke aplikasi
#
# Berlaku langsung; Caddy memeriksa berkas bendera pada setiap permintaan.
source "$(dirname "${BASH_SOURCE[0]}")/lib.sh"
BENDERA="$AKAR/infra/caddy/bendera"

status() {
	for a in "${APLIKASI[@]}"; do
		if sudah_buka "$a"; then s="DIBUKA (diteruskan ke kontainer $a)"; else s="pemeliharaan (503)"; fi
		printf '%-28s %s\n' "$(host_aplikasi "$a")" "$s"
	done
}

if [ "${1:-}" = "status" ]; then
	status
	exit 0
fi

app="${1:-}"
aksi="${2:-}"
case "$app" in auth | lokakode | portal | lokaujian) ;; *) gagal "pemakaian: $0 status | <auth|lokakode|portal|lokaujian> <on|off>" ;; esac

case "$aksi" in
	on)
		rm -f "${BENDERA:?}/${app:?}.buka"
		;;
	off)
		sehat="$(docker inspect -f '{{.State.Health.Status}}' "$app" 2>/dev/null || true)"
		[ "$sehat" = "healthy" ] || gagal "kontainer $app tidak sehat (status: ${sehat:-tidak ada}); host tidak dibuka"
		touch "$BENDERA/$app.buka"
		;;
	*) gagal "pemakaian: $0 status | <auth|lokakode|portal|lokaujian> <on|off>" ;;
esac
status
