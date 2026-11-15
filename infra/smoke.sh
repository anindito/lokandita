#!/usr/bin/env bash
# Uji asap dengan permintaan sungguhan (KONTRAK.md §11). Jalankan setelah
# setiap deploy. Keluar 0 hanya bila semua cek yang AKTIF lulus.
#
# Cek 3–5 membutuhkan LokaAuth (tahap 5) dan cek 6 membutuhkan judge (tahap 7).
# Sampai saat itu keduanya dicetak sebagai "BELUM AKTIF" — bukan lulus.
# Mengaktifkan: ubah angka di bawah menjadi 1 pada tahap yang bersangkutan.
CEK_AUTH_AKTIF=1   # tahap 5: cek 3, 4, 5 — aktif sejak tahap 5
CEK_JUDGE_AKTIF=0  # tahap 7: cek 6

source "$(dirname "${BASH_SOURCE[0]}")/lib.sh"

lulus=0
gagal_n=0
tunda=0
ok() { echo "  LULUS  $*"; lulus=$((lulus + 1)); }
tidak() { echo "  GAGAL  $*"; gagal_n=$((gagal_n + 1)); }
belum() { echo "  BELUM AKTIF  $*"; tunda=$((tunda + 1)); }

domain="$(nilai_env "$AKAR/infra/.env" DOMAIN)"
kode() { curl -sS -o /dev/null --max-time 15 -w '%{http_code}' "$1" 2>&1 || true; }

echo "0. kontainer aplikasi menjalankan commit HEAD"
head="$(git -C "$AKAR" rev-parse HEAD)"
for a in "${APLIKASI[@]}"; do
	jawab="$(docker exec "$a" curl -fsS --max-time 3 http://127.0.0.1:8080/healthz 2>/dev/null || true)"
	if [ "$(jq -r '.commit' <<< "$jawab" 2>/dev/null)" = "$head" ]; then
		ok "$a -> $jawab"
	else
		tidak "$a seharusnya commit $head, /healthz menjawab: ${jawab:-<tidak ada jawaban>}"
	fi
done

echo "1. setiap host publik menjawab lewat HTTPS dengan sertifikat yang sah"
for a in "${APLIKASI[@]}"; do
	h="$(host_aplikasi "$a")"
	k="$(kode "https://$h/")"
	if sudah_buka "$a"; then
		case "$k" in 2?? | 3??) ok "https://$h/ -> $k (dibuka)" ;; *) tidak "https://$h/ -> $k (dibuka, seharusnya 2xx/3xx)" ;; esac
	else
		if [ "$k" = "503" ]; then ok "https://$h/ -> 503 (halaman pemeliharaan)"; else tidak "https://$h/ -> $k (seharusnya 503 pemeliharaan)"; fi
	fi
done
k="$(curl -sS -o /dev/null --max-time 15 -w '%{http_code} %{redirect_url}' "https://www.$domain/" 2>&1 || true)"
if [ "$k" = "301 https://$domain/" ]; then ok "https://www.$domain/ -> $k"; else tidak "https://www.$domain/ -> $k (seharusnya 301 ke https://$domain/)"; fi

echo "2. /internal/* dijawab 404 dari internet di setiap host"
for h in $(daftar_host); do
	k="$(kode "https://$h/internal/session/touch")"
	if [ "$k" = "404" ]; then ok "https://$h/internal/session/touch -> 404"; else tidak "https://$h/internal/session/touch -> $k (seharusnya 404)"; fi
done

# Dari dalam kontainer klien, dengan token milik kontainer itu sendiri.
# Mencetak hanya kode status dan jenis isi, tidak pernah tokennya.
sentuh() { # <kontainer> <benar|salah>
	docker exec -e MODE="$2" "$1" sh -c '
		token="$AUTH_SERVICE_TOKEN"
		[ "$MODE" = "salah" ] && token="token-yang-salah"
		curl -s --max-time 5 -o /tmp/smoke-body -w "%{http_code}" -X POST \
			-H "Content-Type: application/json" -H "X-Service-Token: $token" \
			-d "{\"sid\":\"sid-palsu-untuk-smoke\"}" "$AUTH_INTERNAL_URL/internal/session/touch"
		if head -c 1 /tmp/smoke-body | grep -q "{"; then echo " json"; else echo " bukan-json"; fi
	' 2>&1 || true
}

echo "3. lokakode dan portal mencapai API internal LokaAuth dengan token masing-masing"
if [ "$CEK_AUTH_AKTIF" = "1" ]; then
	for a in lokakode portal; do
		j="$(sentuh "$a" benar)"
		case "$j" in "422 json" | "404 json") ok "$a -> $j" ;; *) tidak "$a -> $j (seharusnya 422, atau 404 berisi JSON; 401 berarti token salah)" ;; esac
	done
else
	belum "menunggu API internal LokaAuth (tahap 5)"
fi

echo "4. token yang salah ditolak 401"
if [ "$CEK_AUTH_AKTIF" = "1" ]; then
	for a in lokakode portal; do
		j="$(sentuh "$a" salah)"
		case "$j" in "401 json") ok "$a -> $j" ;; *) tidak "$a -> $j (seharusnya 401)" ;; esac
	done
else
	belum "menunggu API internal LokaAuth (tahap 5)"
fi

echo "5. identity_reader bisa membaca session_identity dan tidak bisa membaca users"
if [ "$CEK_AUTH_AKTIF" = "1" ]; then
	j="$(docker exec -i lokakode php <<'PHP' 2>&1 || true
<?php
mysqli_report(MYSQLI_REPORT_OFF);
$u = parse_url(getenv('IDENTITY_READER_DSN'));
$m = @new mysqli($u['host'], $u['user'], $u['pass'], ltrim($u['path'], '/'), $u['port']);
if ($m->connect_errno) { echo 'koneksi gagal: ', $m->connect_errno; exit; }
$a = $m->query('SELECT 1 FROM session_identity LIMIT 1') !== false ? 'view-terbaca' : 'view-ditolak-' . $m->errno;
$b = $m->query('SELECT 1 FROM users LIMIT 1') === false ? 'users-ditolak-' . $m->errno : 'users-TERBACA';
echo $a, ' ', $b;
PHP
)"
	case "$j" in "view-terbaca users-ditolak-1142") ok "$j" ;; *) tidak "$j" ;; esac
else
	belum "menunggu skema identity dan view-nya (tahap 5)"
fi

echo "6. judge worker berjalan dan satu job RUN sederhana selesai"
if [ "$CEK_JUDGE_AKTIF" = "1" ]; then
	tidak "cek 6 belum ditulis; tulis bersama judge worker di tahap 7"
else
	belum "menunggu judge worker (tahap 7)"
fi

echo
echo "smoke: $lulus lulus, $gagal_n gagal, $tunda belum aktif"
if [ "$gagal_n" -gt 0 ]; then
	echo "smoke: GAGAL"
	exit 1
fi
if [ "$tunda" -gt 0 ]; then
	echo "smoke: LULUS untuk cek yang aktif — $tunda cek BELUM AKTIF dan belum membuktikan apa pun"
else
	echo "smoke: LULUS"
fi
