#!/usr/bin/env bash
# Memeriksa .env tiap komponen terhadap .env.example (KONTRAK.md §10).
# Gagal bila: .env tidak ada, ada kunci contoh yang hilang atau kosong, masih
# ada {{…}}, nilai diberi tanda kutip, atau mode berkas bukan 600.
# Tidak pernah mencetak nilai.
#
# Pemakaian: infra/cek-env.sh            (semua komponen)
#            infra/cek-env.sh <direktori> (satu direktori, dipakai oleh tes)
source "$(dirname "${BASH_SOURCE[0]}")/lib.sh"

if [ "$#" -gt 0 ]; then
	direktori=("$@")
else
	direktori=()
	for k in "${KOMPONEN[@]}"; do direktori+=("$AKAR/$k"); done
fi

salah=0
lapor() {
	echo "  SALAH: $*"
	salah=$((salah + 1))
}

for d in "${direktori[@]}"; do
	label="${d#"$AKAR"/}"
	echo "$label"
	contoh="$d/.env.example"
	env="$d/.env"
	[ -f "$contoh" ] || { lapor ".env.example tidak ada"; continue; }
	[ -f "$env" ] || { lapor ".env tidak ada (buat dari .env.example)"; continue; }

	mode="$(stat -c %a "$env")"
	[ "$mode" = "600" ] || lapor ".env bermode $mode, seharusnya 600"

	jumlah=0
	while IFS= read -r kunci; do
		jumlah=$((jumlah + 1))
		if ! grep -q "^$kunci=" "$env"; then
			lapor "$kunci ada di .env.example tetapi tidak ada di .env"
			continue
		fi
		nilai="$(nilai_env "$env" "$kunci")"
		if [ -z "$nilai" ]; then
			lapor "$kunci kosong"
		elif [[ "$nilai" == *"{{"* ]]; then
			lapor "$kunci masih berisi {{…}} yang belum diganti"
		elif [[ "$nilai" == \"* || "$nilai" == \'* ]]; then
			lapor "$kunci diberi tanda kutip; tulis nilainya tanpa kutip"
		fi
	done < <(sed -n 's/^\([A-Z][A-Z0-9_]*\)=.*/\1/p' "$contoh")

	while IFS= read -r kunci; do
		grep -q "^$kunci=" "$contoh" || echo "  catatan: $kunci ada di .env tetapi tidak di .env.example"
	done < <(sed -n 's/^\([A-Z][A-Z0-9_]*\)=.*/\1/p' "$env")

	echo "  $jumlah kunci diperiksa"
done

if [ "$salah" -gt 0 ]; then
	echo "cek-env: GAGAL ($salah masalah)"
	exit 1
fi
echo "cek-env: LULUS"
