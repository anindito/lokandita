#!/usr/bin/env bash
# Uji asap dengan permintaan sungguhan (KONTRAK.md §11). Jalankan setelah
# setiap deploy. Keluar 0 hanya bila semua cek yang AKTIF lulus.
#
# Cek 3–5 membutuhkan LokaAuth (tahap 5) dan cek 6 membutuhkan judge (tahap 7).
# Sampai saat itu keduanya dicetak sebagai "BELUM AKTIF" — bukan lulus.
# Mengaktifkan: ubah angka di bawah menjadi 1 pada tahap yang bersangkutan.
CEK_AUTH_AKTIF=1   # tahap 5: cek 3, 4, 5 — aktif sejak tahap 5
CEK_JUDGE_AKTIF=1  # tahap 7: cek 6 — aktif sejak tahap 7; cek 7 (VALIDATE) sejak tahap 8
# Cek 8 (halaman publik, tahap 11) selalu aktif.

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
	status="$(systemctl --user is-active "$LAYANAN_JUDGE" 2>&1 || true)"
	if [ "$status" = "active" ]; then ok "layanan $LAYANAN_JUDGE: active"; else tidak "layanan $LAYANAN_JUDGE: $status (seharusnya active)"; fi

	denyut="$(denyut_judge)"
	if [ -n "$denyut" ] && [ "$(jq -r '.commit' <<< "$denyut" 2>/dev/null)" = "$head" ]; then
		ok "denyut worker -> $denyut"
	else
		tidak "worker seharusnya commit $head, denyut di Redis: ${denyut:-<tidak ada>}"
	fi

	if linger_aktif; then
		ok "linger aktif: worker tetap hidup tanpa sesi login dan menyala setelah reboot"
	else
		tidak "linger belum aktif: worker mati saat tidak ada sesi login. Pemilik: sudo loginctl enable-linger $(id -un)"
	fi

	# Pekerjaan RUN sungguhan lewat jalur web: baris judge_jobs, Redis, worker, kontainer.
	keluaran="$(docker exec lokakode php spark judge:coba 2>&1)" && kode_coba=0 || kode_coba=$?
	ringkas="$(grep -E '^(\{"job"|judge:coba )' <<< "$keluaran" | tr '\n' ' ')"
	if [ "$kode_coba" = "0" ] && grep -q 'judge:coba LULUS' <<< "$keluaran"; then
		ok "RUN lewat lokakode -> $ringkas"
	else
		tidak "RUN lewat lokakode gagal (kode $kode_coba): ${ringkas:-$(tail -n 3 <<< "$keluaran" | tr '\n' ' ')}"
	fi

else
	belum "menunggu judge worker (tahap 7)"
fi

echo "7. judge memvalidasi satu soal kecil (VALIDATE: generator, konsep, 240 tes)"
if [ "$CEK_JUDGE_AKTIF" = "1" ]; then
	# Soal bawaan, lewat jalur yang sama dengan impor: generator, analisis konsep,
	# mode kelompok, solusi referensi. Tidak ada yang disimpan ke bank soal.
	keluaran="$(docker exec lokakode php spark judge:coba --validasi 2>&1)" && kode_coba=0 || kode_coba=$?
	ringkas="$(grep -E '^(\{"job"|judge:coba )' <<< "$keluaran" | tr '\n' ' ')"
	if [ "$kode_coba" = "0" ] && grep -q 'judge:coba LULUS' <<< "$keluaran"; then
		ok "VALIDATE lewat lokakode -> $ringkas"
	else
		tidak "VALIDATE lewat lokakode gagal (kode $kode_coba): ${ringkas:-$(tail -n 3 <<< "$keluaran" | tr '\n' ' ')}"
	fi

	sisa="$(docker ps -a --filter label=lokandita.judge=true --filter label=lokandita.judge.instans=lokakode --format '{{.Names}}' | wc -l)"
	if [ "$sisa" = "0" ]; then ok "tidak ada kontainer judge yang tertinggal"; else tidak "$sisa kontainer judge tertinggal"; fi
else
	belum "menunggu judge worker (tahap 7)"
fi

echo "8. halaman publik terbuka dan beranda mengarah ke LokaAuth (tahap 11)"
# Ditanyakan langsung ke kontainer aplikasi dari kontainer aplikasi lain, dengan header yang
# dikirim Caddy, supaya cek ini juga berjalan selagi host-nya masih pemeliharaan.
# tanya <kontainer penanya> <kontainer tujuan> <jalur>  ->  "<status> <tujuan redirect>"
tanya() { docker exec "$1" curl -sS -o /tmp/smoke-publik --max-time 10 -H 'X-Forwarded-Proto: https' -w '%{http_code} %{redirect_url}' "http://$2:8080$3" 2>&1 || true; }
isi() { docker exec "$1" cat /tmp/smoke-publik 2>/dev/null || true; }
login_url="$(nilai_env "$AKAR/apps/portal/.env" AUTH_LOGIN_URL)"
for pasangan in "lokakode portal" "portal lokakode"; do
	read -r penanya a <<< "$pasangan"
	url="$(nilai_env "$AKAR/apps/$a/.env" APP_URL)"
	j="$(tanya "$penanya" "$a" /)"
	if [ "$j" = "200 " ] && isi "$penanya" | grep -q '<meta name="robots" content="index, follow">'; then
		ok "$a GET / tanpa cookie -> 200, halaman publik yang boleh diindeks"
	else
		tidak "$a GET / tanpa cookie -> '$j' (seharusnya 200 dengan robots index, follow)"
	fi
	j="$(tanya "$penanya" "$a" /beranda)"
	harap="302 $login_url?next=$(jq -rn --arg u "$url/beranda" '$u|@uri')"
	if [ "$j" = "$harap" ]; then ok "$a GET /beranda tanpa cookie -> $j"; else tidak "$a GET /beranda tanpa cookie -> '$j' (seharusnya '$harap')"; fi
done
j="$(tanya lokakode portal /privasi)"
if [ "$j" = "200 " ]; then ok "portal GET /privasi -> 200"; else tidak "portal GET /privasi -> '$j' (seharusnya 200)"; fi

echo "9. foto pembuka Portal (tahap 11c, KONTRAK.md §16)"
# Foto yang ditunjuk halaman depan (bawaan atau unggahan) harus benar-benar terjawab sebagai gambar.
tanya lokakode portal / > /dev/null
foto="$(isi lokakode | grep -o '<source type="image/avif" srcset="[^" ]*' | head -n 1 | sed 's/.*srcset="//; s/&amp;/\&/g')"
if [ -n "$foto" ]; then
	j="$(docker exec lokakode curl -sS -o /dev/null --max-time 10 -w '%{http_code} %{content_type}' "http://portal:8080$foto" 2>&1 || true)"
	if [ "$j" = "200 image/avif" ]; then ok "portal: foto pembuka halaman depan ($foto) -> $j"; else tidak "portal: foto pembuka $foto -> '$j' (seharusnya 200 image/avif)"; fi
else
	tidak "portal: halaman depan tidak memuat <source type=\"image/avif\"> untuk foto pembuka"
fi
j="$(docker exec lokakode curl -sS -o /dev/null --max-time 10 -w '%{http_code}' "http://portal:8080/media/hero/smoke.php" 2>&1 || true)"
if [ "$j" = "403" ]; then ok "portal: /media/ hanya menjawab berkas foto buatan server (smoke.php -> 403)"; else tidak "portal: /media/hero/smoke.php -> '$j' (seharusnya 403)"; fi

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
