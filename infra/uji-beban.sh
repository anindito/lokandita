#!/usr/bin/env bash
# Uji beban LokaUjian (SPEC-LOKAUJIAN.md §14; KONTRAK.md §12): N mahasiswa tiruan mengerjakan
# satu ujian sekaligus selama M menit, dan setiap simpan yang diterima server dicocokkan dengan
# tabel `answers`.
#
#   infra/uji-beban.sh <jumlah mahasiswa> [menit, bawaan 15] [--restart]
#
#   --restart   kontainer aplikasi tes di-restart di tengah ujian (SPEC-LOKAUJIAN.md §14:
#               "halaman ujian tetap bisa dipakai melewati restart").
#
# JAUH DARI DATA PRODUKSI: hanya skema *_test dan Redis DB 15, lewat fungsi infra/tes.sh yang
# menolak nama lain; akun-akunnya karangan; kontainer yang dibebani adalah kontainer tes
# (auth-tes, lokaujian-seb-tes, jadwal-beban, caddy-beban), bukan kontainer produksi.
#
# TETAPI MESINNYA SAMA: MySQL, Redis dan keempat inti CPU dipakai bersama LokaKode produksi.
# Karena itu skrip ini
#   1. menolak mulai bila dalam 15 menit terakhir ada kiriman LokaKode, ada pekerjaan judge yang
#      belum selesai, atau ada pengerjaan LokaUjian produksi yang sedang berjalan;
#   2. memeriksa lagi tiap 30 detik dan MENGHENTIKAN uji begitu ada kiriman LokaKode atau
#      pekerjaan judge;
#   3. membatasi pembangkit beban pada satu inti CPU.
# Jangan dijalankan bersamaan dengan infra/tes.sh (skema tes yang sama).
source "$(dirname "${BASH_SOURCE[0]}")/lib.sh"
cd "$AKAR"

n="${1:-}"
[[ "$n" =~ ^[0-9]+$ ]] && [ "$n" -ge 2 ] && [ "$n" -le 400 ] || gagal "pemakaian: $0 <jumlah mahasiswa 2–400> [menit] [--restart]"
menit=15
restart=0
for a in "${@:2}"; do
	case "$a" in
		--restart) restart=1 ;;
		*) [[ "$a" =~ ^[0-9]+$ ]] && [ "$a" -ge 2 ] && [ "$a" -le 120 ] || gagal "menit harus 2–120, atau --restart"; menit="$a" ;;
	esac
done

sql() { docker exec -i mysql sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysql -uroot -N -B' <<< "$1"; }

# Hanya membaca, hanya angka: kiriman LokaKode <menit> terakhir, pekerjaan judge belum selesai,
# pengerjaan LokaUjian produksi yang belum dikumpulkan.
produksi() { # <menit>
	sql "SELECT (SELECT COUNT(*) FROM lokakode.submissions WHERE created_at > UTC_TIMESTAMP() - INTERVAL $1 MINUTE),
	            (SELECT COUNT(*) FROM lokakode.judge_jobs WHERE status IN ('QUEUED','RUNNING')),
	            (SELECT COUNT(*) FROM lokaujian.attempts WHERE submitted_at IS NULL);"
}

if docker ps -a --format '{{.Names}}' | grep -q -x -E 'auth-tes|lokaujian-tes|lokaujian-seb-tes|lokakode-tes|portal-tes|tes-jalan|jadwal-beban|caddy-beban'; then
	gagal "kontainer tes sedang ada (infra/tes.sh atau uji beban lain berjalan?); uji beban memakai skema tes yang sama"
fi
read -r kiriman judge ujian <<< "$(produksi 15)"
echo "sebelum mulai: $kiriman kiriman LokaKode dalam 15 menit terakhir, $judge pekerjaan judge belum selesai, $ujian pengerjaan LokaUjian produksi berjalan"
[ "$kiriman" = "0" ] && [ "$judge" = "0" ] && [ "$ujian" = "0" ] || gagal "produksi sedang dipakai; uji beban tidak dimulai"

akhiran=""
if [ "$restart" = 1 ]; then akhiran="-restart"; fi
hasil="${HASIL:-$AKAR/infra/beban-hasil}/$(date +%Y%m%d-%H%M%S)-n$n-m$menit$akhiran"
mkdir -p "$hasil"
tmp="$(mktemp -d)"
app=lokaujian
url_peramban=""
pid_sampel=""
pid_restart=""
bersih() {
	[ -n "$pid_sampel" ] && kill "$pid_sampel" 2> /dev/null || true
	[ -n "$pid_restart" ] && kill "$pid_restart" 2> /dev/null || true
	docker rm -f auth-tes lokaujian-tes lokaujian-seb-tes jadwal-beban caddy-beban tes-jalan > /dev/null 2>&1 || true
	rm -rf "${tmp:?}"
}
trap bersih EXIT

source "$AKAR/infra/lib-tes.sh"

bangun auth
env_tes auth
bangun lokaujian
env_tes lokaujian
skema_auth="$(sed -n 's#^DB_DSN=.*/##p' "$tmp/auth.env")"
buat_ulang_skema "$skema_auth"
migrasi auth "$skema_auth"
"$AKAR/infra/mysql-hak-reader.sh" "$skema_auth"
docker exec redis redis-cli -n 15 flushdb > /dev/null
layani auth auth-tes auth-internal-tes
buat_ulang_skema lokaujian_test
migrasi lokaujian lokaujian_test
bangun lokakode
env_tes lokakode
skema_lokakode_tes
# Yang dibebani: image yang sama dengan penegakan SEB AKTIF, seperti produksi.
layani lokaujian lokaujian-seb-tes "" lokaujian-seb

# Penjadwal sungguhan pada skema tes (KONTRAK.md §21), seperti lokaujian-jadwal di produksi.
docker run -d --name jadwal-beban --network data --env-file "$tmp/lokaujian-seb.env" \
	--entrypoint php lokandita/lokaujian:tes /srv/app/spark ujian:jadwal --no-header > /dev/null
echo "jadwal-beban berjalan (penjadwal pada lokaujian_test, Redis DB 15)"

# Caddy di depan kedua aplikasi, seperti di produksi: peramban tidak pernah berbicara langsung
# dengan Apache. HTTP biasa (tanpa TLS); header X-Forwarded-* dari pembangkit dipercaya.
cat > "$tmp/Caddyfile" <<- 'CADDY'
	{
		admin off
		auto_https off
		servers {
			trusted_proxies static private_ranges
		}
	}
	http://:8081 {
		reverse_proxy lokaujian-seb-tes:8080
	}
	http://:8082 {
		reverse_proxy auth-tes:8080
	}
CADDY
chmod 644 "$tmp/Caddyfile"
chmod 711 "$tmp"
docker run -d --name caddy-beban --network data --network-alias caddy-beban -v "$tmp/Caddyfile:/etc/caddy/Caddyfile:ro" caddy:2.11.9-alpine > /dev/null
for ((i = 1; i <= 40; i++)); do
	if docker exec caddy-beban wget -q -O /dev/null http://127.0.0.1:8081/healthz 2> /dev/null && docker exec caddy-beban wget -q -O /dev/null http://127.0.0.1:8082/healthz 2> /dev/null; then break; fi
	[ "$i" -lt 40 ] || { docker logs --tail 20 caddy-beban >&2; gagal "caddy-beban tidak siap"; }
	sleep 0.5
done
echo "caddy-beban meneruskan ke lokaujian-seb-tes (:8081) dan auth-tes (:8082)"

# Pencuplik: tiap 5 detik angka mesin; tiap 30 detik penjaga produksi.
status_mysql() { sql "SELECT VARIABLE_VALUE FROM performance_schema.global_status WHERE VARIABLE_NAME = '$1';"; }
maks_awal="$(status_mysql Max_used_connections)"
host_lokakode="$(host_aplikasi lokakode)"
cuplik() {
	echo "waktu,load1,mysql_sambungan,pekerja_apache_ujian,pekerja_apache_auth,cpu_ujian,mem_ujian_mb,cpu_auth,cpu_mysql,mem_mysql_mb,cpu_pembangkit,lokakode_depan_ms" > "$hasil/sampel.csv"
	local k=0
	while :; do
		local stat load sambung apu apa lk
		stat="$(docker stats --no-stream --format '{{.Name}} {{.CPUPerc}} {{.MemUsage}}' lokaujian-seb-tes auth-tes mysql tes-jalan 2> /dev/null || true)"
		ambil() { awk -v n="$1" -v f="$2" '$1 == n { v = $f; gsub(/%/, "", v); if (v ~ /GiB/) { sub(/GiB/, "", v); v = v * 1024 } else { sub(/MiB/, "", v) } print v }' <<< "$stat"; }
		load="$(cut -d ' ' -f 1 /proc/loadavg)"
		sambung="$(status_mysql Threads_connected 2> /dev/null || echo '')"
		apu="$(docker top lokaujian-seb-tes 2> /dev/null | grep -c apache2 || true)"
		apa="$(docker top auth-tes 2> /dev/null | grep -c apache2 || true)"
		lk="$(curl -s -o /dev/null --max-time 10 -w '%{time_total}' "https://$host_lokakode/" 2> /dev/null | awk '{ printf "%d", $1 * 1000 }' || true)"
		echo "$(date +%H:%M:%S),$load,$sambung,$apu,$apa,$(ambil lokaujian-seb-tes 2),$(ambil lokaujian-seb-tes 3),$(ambil auth-tes 2),$(ambil mysql 2),$(ambil mysql 3),$(ambil tes-jalan 2),$lk" >> "$hasil/sampel.csv"
		k=$((k + 1))
		if [ $((k % 6)) -eq 0 ]; then
			local a b c
			read -r a b c <<< "$(produksi 2)"
			if [ "$a" != "0" ] || [ "$b" != "0" ]; then
				echo "DIHENTIKAN $(date +%H:%M:%S): $a kiriman LokaKode dalam 2 menit terakhir, $b pekerjaan judge belum selesai" | tee "$hasil/DIHENTIKAN" >&2
				docker rm -f tes-jalan > /dev/null 2>&1 || true
				return 0
			fi
		fi
		sleep 3
	done
}
cuplik &
pid_sampel=$!

if [ "$restart" = 1 ]; then
	(
		sleep $((75 + menit * 30))
		echo "RESTART $(date +%H:%M:%S): docker restart lokaujian-seb-tes" | tee "$hasil/RESTART" >&2
		docker restart lokaujian-seb-tes > /dev/null
		echo "RESTART selesai $(date +%H:%M:%S)" | tee -a "$hasil/RESTART" >&2
	) &
	pid_restart=$!
fi

echo "== uji beban: $n mahasiswa, $menit menit${akhiran:+, dengan restart di tengah} — hasil di $hasil"
JALAN_TAMBAHAN=(--cpus 1 -e BEBAN_N="$n" -e BEBAN_MENIT="$menit" -e BEBAN_APP_URL=http://caddy-beban:8081 -e BEBAN_AUTH_URL=http://caddy-beban:8082)
kode=0
jalan lokaujian vendor/bin/phpunit --no-progress --testdox --testsuite Beban 2>&1 | tee "$hasil/keluaran.txt" || kode=$?
kill "$pid_sampel" 2> /dev/null || true
pid_sampel=""

echo "== angka mesin selama uji (puncak; $(($(wc -l < "$hasil/sampel.csv") - 1)) cuplikan, tiap ±5 detik)"
awk -F, 'NR > 1 { for (i = 2; i <= NF; i++) if ($i + 0 > m[i]) m[i] = $i + 0 }
	END { printf "  load 1 menit %.2f; sambungan MySQL %d; pekerja Apache lokaujian %d, auth %d;\n  CPU lokaujian %.0f%%, auth %.0f%%, mysql %.0f%%, pembangkit %.0f%%; memori lokaujian %.0f MB, mysql %.0f MB;\n  halaman depan LokaKode produksi paling lambat %d md\n", m[2], m[3], m[4], m[5], m[6], m[8], m[9], m[11], m[7], m[10], m[12] }' "$hasil/sampel.csv"
echo "  Max_used_connections MySQL (sejak server mulai): $maks_awal sebelum, $(status_mysql Max_used_connections) sesudah; batas $(sql 'SELECT @@max_connections;')"
docker logs lokaujian-seb-tes 2>&1 | grep -c -i 'MaxRequestWorkers' | sed 's/^/  baris "MaxRequestWorkers" di log Apache lokaujian: /' || true
docker logs lokaujian-seb-tes > "$hasil/log-lokaujian.txt" 2>&1 || true
docker logs auth-tes > "$hasil/log-auth.txt" 2>&1 || true
docker logs jadwal-beban > "$hasil/log-jadwal.txt" 2>&1 || true

if [ -e "$hasil/DIHENTIKAN" ]; then
	echo "uji beban: DIHENTIKAN oleh penjaga produksi — bukan hasil"
	exit 2
fi
if [ "$kode" -ne 0 ]; then
	echo "uji beban: GAGAL (phpunit keluar $kode)"
	exit "$kode"
fi
echo "uji beban: LULUS"
