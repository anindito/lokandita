#!/usr/bin/env bash
# Membuat .env tiap komponen dari .env.example, mengisi {{…}} dengan rahasia
# acak. Dijalankan SEKALI pada server baru. Tidak pernah menimpa .env yang ada
# (INSTANCE_SECRET tidak boleh berubah) dan tidak pernah mencetak rahasia.
source "$(dirname "${BASH_SOURCE[0]}")/lib.sh"
umask 077

for k in "${KOMPONEN[@]}"; do
	[ -e "$AKAR/$k/.env" ] && gagal "$k/.env sudah ada. Skrip ini hanya untuk server baru; tambahkan kunci baru secara manual."
	[ -f "$AKAR/$k/.env.example" ] || gagal "$k/.env.example tidak ada"
done

acak() { openssl rand -hex 32; }

# Rahasia yang harus sama di lebih dari satu berkas.
declare -A BERSAMA
for nama in MYSQL_ROOT_PASSWORD DB_PASSWORD_AUTH DB_PASSWORD_LOKAKODE DB_PASSWORD_PORTAL DB_PASSWORD_LOKAUJIAN \
	DB_PASSWORD_IDENTITY_READER DB_PASSWORD_JUDGE DB_PASSWORD_ROSTER_READER REDIS_PASSWORD SERVICE_TOKEN_PORTAL SERVICE_TOKEN_LOKAKODE SERVICE_TOKEN_LOKAUJIAN; do
	BERSAMA[$nama]="$(acak)"
done

for k in "${KOMPONEN[@]}"; do
	keluaran="$AKAR/$k/.env"
	: > "$keluaran"
	while IFS= read -r baris || [ -n "$baris" ]; do
		for nama in "${!BERSAMA[@]}"; do
			baris="${baris//\{\{$nama\}\}/${BERSAMA[$nama]}}"
		done
		# {{ACAK}}: rahasia yang hanya dipakai satu komponen, tiap kemunculan berbeda.
		while [[ "$baris" == *"{{ACAK}}"* ]]; do
			baris="${baris/\{\{ACAK\}\}/$(acak)}"
		done
		printf '%s\n' "$baris" >> "$keluaran"
	done < "$AKAR/$k/.env.example"
	chmod 600 "$keluaran"
	echo "dibuat: $k/.env ($(grep -c '^[A-Z][A-Z0-9_]*=' "$keluaran") kunci, mode 600)"
done

echo "Selesai. Periksa dengan infra/cek-env.sh."
