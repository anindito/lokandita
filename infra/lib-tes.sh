#!/usr/bin/env bash
# Fungsi bersama infra/tes.sh dan infra/uji-beban.sh: membangun image tes, menyiapkan .env tes,
# membuat ulang skema *_test, menjalankan dan melayani kontainer tes. Di-source, tidak dijalankan
# sendiri. Pemanggil menyediakan: $tmp (direktori sementara), $app (aplikasi yang diuji),
# $skema_auth (sesudah env_tes auth) dan sudah men-source lib.sh.
#
# Hanya skema berakhiran _test dan Redis DB 15 yang disentuh; fungsi-fungsi ini menolak yang lain.

bangun() { # <app>
	docker build --quiet -f infra/docker/app.Dockerfile \
		--build-arg APP="$1" --build-arg GIT_COMMIT=tes --build-arg COMPOSER_DEV= \
		-t "lokandita/$1:tes" . > /dev/null
	echo "image lokandita/$1:tes dibangun"
}

# env_tes <app> — menyalin .env aplikasi dengan semua tujuan diganti ke versi tes.
env_tes() {
	sed -E \
		-e 's#^(DB_DSN=.*/[a-z]+)$#\1_test#' \
		-e 's#^(IDENTITY_READER_DSN=.*/identity)$#\1_test#' \
		-e 's#^(ROSTER_READER_DSN=.*/)(roster|lokakode)$#\1roster_test#' \
		-e 's#^ROSTER_SCHEMA=roster$#ROSTER_SCHEMA=roster_test#' \
		-e 's#^(REDIS_URL=.*/)[0-9]+$#\115#' \
		-e 's#^(JUDGE_QUEUE_REDIS_URL=.*/)[0-9]+$#\115#' \
		-e 's#^AUTH_INTERNAL_URL=.*#AUTH_INTERNAL_URL=http://auth-internal-tes:8080#' \
		"apps/$1/.env" > "$tmp/$1.env"
	chmod 600 "$tmp/$1.env"
	# Tes skema LokaAuth juga masuk sebagai identity_reader, seperti aplikasi klien.
	if [ "$1" = "auth" ]; then
		sed -n -E 's#^IDENTITY_READER_DSN=(.*/identity)$#TES_READER_DSN=\1_test#p' apps/lokakode/.env >> "$tmp/$1.env"
	fi
	# Tes aplikasi klien menyiapkan pengguna langsung di identity_test, lalu login lewat HTTP ke auth-tes.
	if [ "$1" != "auth" ]; then
		sed -n -E 's#^DB_DSN=(.*/identity)$#TES_IDENTITY_DSN=\1_test#p' apps/auth/.env >> "$tmp/$1.env"
	fi
	grep -q '^DB_DSN=.*_test$' "$tmp/$1.env" || gagal "env tes $1: DB_DSN tidak berakhiran _test"
	# Akun Lokandita menulis roster (tahap A2, KONTRAK.md §18): dalam tes hanya roster_test.
	if [ "$1" = "auth" ]; then
		grep -q '^ROSTER_SCHEMA=roster_test$' "$tmp/$1.env" || gagal "env tes auth: ROSTER_SCHEMA bukan roster_test"
		# Tes hapus kelas membuktikan kunci asing lintas skema dengan satu baris LokaKode sungguhan.
		sed -n -E 's#^DB_DSN=(.*/lokakode)$#TES_LOKAKODE_DSN=\1_test#p' apps/lokakode/.env >> "$tmp/$1.env"
		grep -q '^TES_LOKAKODE_DSN=.*_test$' "$tmp/$1.env" || gagal "env tes auth: TES_LOKAKODE_DSN tidak terbentuk"
	fi
	# Pembaca roster (KONTRAK.md §18): view kontraknya ada di roster_test (tahap A1), apa pun skema
	# yang disebut .env produksi. Baris uji roster ditulis tes lewat koneksi khusus tes sebagai
	# lokakode_app, lewat view nama lama di lokakode_test; aplikasi yang diuji tidak memakai DSN itu.
	if grep -q '^ROSTER_READER_DSN=' "$tmp/$1.env"; then
		grep -q '^ROSTER_READER_DSN=.*/roster_test$' "$tmp/$1.env" || gagal "env tes $1: ROSTER_READER_DSN bukan roster_test"
		sed -n -E 's#^DB_DSN=(.*/lokakode)$#TES_ROSTER_TULIS_DSN=\1_test#p' apps/lokakode/.env >> "$tmp/$1.env"
		grep -q '^TES_ROSTER_TULIS_DSN=.*_test$' "$tmp/$1.env" || gagal "env tes $1: TES_ROSTER_TULIS_DSN tidak terbentuk"
	fi
	# Tes LokaKode memeriksa view kontrak roster dari sisi pembacanya (ViewRosterTest).
	if [ "$1" = "lokakode" ]; then
		sed -n -E 's#^ROSTER_READER_DSN=(.*/)(roster|lokakode)$#TES_ROSTER_READER_DSN=\1roster_test#p' apps/lokaujian/.env >> "$tmp/$1.env"
		grep -q '^TES_ROSTER_READER_DSN=.*/roster_test$' "$tmp/$1.env" || gagal "env tes $1: TES_ROSTER_READER_DSN tidak terbentuk"
	fi
	grep -q '^REDIS_URL=.*/15$' "$tmp/$1.env" || gagal "env tes $1: REDIS_URL bukan DB 15"
	# Antrean judge yang dibaca LokaUjian (KONTRAK.md §6): dalam tes juga DB 15, tidak pernah DB 0 produksi.
	if grep -q '^JUDGE_QUEUE_REDIS_URL=' "$tmp/$1.env"; then
		grep -q '^JUDGE_QUEUE_REDIS_URL=.*/15$' "$tmp/$1.env" || gagal "env tes $1: JUDGE_QUEUE_REDIS_URL bukan DB 15"
	fi
	# SEB (KONTRAK.md §8): tes LokaKode berjalan pada DUA kontainer sungguhan dari image yang sama —
	# lokakode-tes dengan penegakan dinonaktifkan (klien tes bukan SEB), dan lokakode-seb-tes dengan
	# penegakan aktif, tempat tes SEB mengirim header yang dihitungnya sendiri. Nilai di .env
	# produksi tidak ikut menentukan.
	if [ "$1" = "lokakode" ]; then
		sed -i -E 's#^SEB_ENFORCEMENT=.*#SEB_ENFORCEMENT=disabled#' "$tmp/$1.env"
		sed -E 's#^SEB_ENFORCEMENT=.*#SEB_ENFORCEMENT=enabled#' "$tmp/$1.env" > "$tmp/$1-seb.env"
		chmod 600 "$tmp/$1-seb.env"
		grep -q '^SEB_ENFORCEMENT=disabled$' "$tmp/$1.env" || gagal "env tes lokakode: SEB_ENFORCEMENT tidak ada"
		grep -q '^SEB_ENFORCEMENT=enabled$' "$tmp/$1-seb.env" || gagal "env tes lokakode-seb: SEB_ENFORCEMENT tidak ada"
	fi
	# LokaUjian tahap U3: klien tes bukan SEB, dan penegakan sungguhan baru ada di tahap U4.
	# Kontainer kedua dengan `enabled` (lokaujian-seb-tes) membuktikan bahwa di tahap ini
	# `enabled` berarti mahasiswa DITOLAK, bukan dibiarkan lewat tanpa pemeriksaan.
	if [ "$1" = "lokaujian" ]; then
		sed -i -E 's#^SEB_ENFORCEMENT=.*#SEB_ENFORCEMENT=disabled#' "$tmp/$1.env"
		sed -E 's#^SEB_ENFORCEMENT=.*#SEB_ENFORCEMENT=enabled#' "$tmp/$1.env" > "$tmp/$1-seb.env"
		chmod 600 "$tmp/$1-seb.env"
		grep -q '^SEB_ENFORCEMENT=disabled$' "$tmp/$1.env" || gagal "env tes lokaujian: SEB_ENFORCEMENT tidak ada"
		grep -q '^SEB_ENFORCEMENT=enabled$' "$tmp/$1-seb.env" || gagal "env tes lokaujian-seb: SEB_ENFORCEMENT tidak ada"
	fi
}

buat_ulang_skema() { # <skema_test>
	[[ "$1" =~ ^[a-z]+_test$ ]] || gagal "menolak membuat ulang skema '$1': bukan skema *_test"
	printf 'DROP DATABASE IF EXISTS `%s`; CREATE DATABASE `%s` CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;\n' "$1" "$1" \
		| docker exec -i mysql sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysql -uroot' \
		|| gagal "tidak bisa membuat ulang skema $1"
	echo "skema $1 dibuat ulang (kosong)"
}

# skema_lokakode_tes — lokakode_test dan roster_test dari nol, dalam tata letak tahap A1
# (KONTRAK.md §18): migrasi LokaKode membuat semua tabel di lokakode_test, lalu keenam tabel roster
# dipindahkan ke roster_test dan view serta haknya dipasang, persis seperti di produksi.
# lokakode_test dibuang lebih dulu: tabel anaknya memegang kunci asing ke roster_test.
skema_lokakode_tes() {
	buat_ulang_skema lokakode_test
	buat_ulang_skema roster_test
	migrasi lokakode lokakode_test
	"$AKAR/infra/mysql-pindah-roster.sh" pindah lokakode_test
}

# jalan <app> <perintah…> — sekali jalan di image tes, di jaringan data dan internal.
# Larik JALAN_TAMBAHAN (bila ada) berisi argumen `docker create` tambahan; dipakai uji beban.
jalan() {
	local a="$1"
	shift
	docker rm -f tes-jalan > /dev/null 2>&1 || true
	docker create --name tes-jalan --network data --env-file "$tmp/$a.env" \
		-e AUTH_TES_URL=http://auth-tes:8080 -e AUTH_TES_SKEMA="$skema_auth" -e APP_TES_URL="http://$app-tes:8080" \
		-e LOKAKODE_TES_URL=http://lokakode-tes:8080 \
		-e APP_SEB_TES_URL="http://$( [ "$app" = "lokaujian" ] && echo lokaujian || echo lokakode )-seb-tes:8080" \
		-e PERAMBAN_TES_URL="$url_peramban" \
		${TANGKAPAN:+-v "$TANGKAPAN:/tangkapan" -e TANGKAPAN_DIR=/tangkapan} \
		${JALAN_TAMBAHAN[@]+"${JALAN_TAMBAHAN[@]}"} \
		--entrypoint "$1" "lokandita/$a:tes" "${@:2}" > /dev/null
	docker network connect internal tes-jalan
	if [ -n "$url_peramban" ]; then docker network connect tes-peramban tes-jalan; fi
	local kode=0
	docker start -a tes-jalan || kode=$?
	docker rm -f tes-jalan > /dev/null 2>&1 || true
	return "$kode"
}

# migrasi <app> <skema_test> — spark keluar 0 walau koneksi gagal, jadi hasilnya
# dibuktikan dengan melihat tabel `migrations` di skema itu.
migrasi() {
	local keluaran
	keluaran="$(jalan "$1" php spark migrate --all 2>&1)" || { echo "$keluaran" >&2; gagal "migrasi $1 gagal"; }
	if grep -qi 'exception\|error' <<< "$keluaran"; then
		echo "$keluaran" >&2
		gagal "migrasi $1 melaporkan galat"
	fi
	local ada
	ada="$(printf "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='%s' AND table_name='migrations';\n" "$2" \
		| docker exec -i mysql sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysql -uroot -N')"
	[ "$ada" = "1" ] || gagal "migrasi $1: tabel migrations tidak ada di $2"
	echo "migrasi $1 pada $2: selesai"
}

# layani <app> <nama kontainer> [alias di jaringan internal] [nama berkas env di $tmp, tanpa .env]
layani() {
	docker run -d --name "$2" --network data --network-alias "$2" --env-file "$tmp/${4:-$1}.env" "lokandita/$1:tes" > /dev/null
	docker network connect ${3:+--alias "$3"} internal "$2"
	local i
	for ((i = 1; i <= 30; i++)); do
		if docker exec "$2" curl -fsS --max-time 2 http://127.0.0.1:8080/healthz > /dev/null 2>&1; then
			echo "$2 melayani HTTP"
			return 0
		fi
		sleep 1
	done
	docker logs --tail 30 "$2" >&2 || true
	gagal "$2 tidak menjawab /healthz"
}

# env_judge_tes — judge/.env dengan skema dan Redis diganti ke versi tes.
env_judge_tes() {
	sed -E -e 's#^(DB_DSN=.*/[a-z]+)$#\1_test#' -e 's#^(REDIS_URL=.*/)[0-9]+$#\115#' judge/.env > "$tmp/judge.env"
	# Tes judge juga berperan sebagai web (yang menyisipkan baris): lokakode_app, lewat 127.0.0.1.
	sed -n -E 's#^DB_DSN=(.*)@mysql:3306/(lokakode_test)$#TES_APP_DSN=\1@127.0.0.1:3306/\2#p' "$tmp/lokakode.env" >> "$tmp/judge.env"
	chmod 600 "$tmp/judge.env"
	grep -q '^DB_DSN=.*_test$' "$tmp/judge.env" || gagal "env tes judge: DB_DSN tidak berakhiran _test"
	grep -q '^REDIS_URL=.*/15$' "$tmp/judge.env" || gagal "env tes judge: REDIS_URL bukan DB 15"
	grep -q '^TES_APP_DSN=.*_test$' "$tmp/judge.env" || gagal "env tes judge: TES_APP_DSN tidak terbentuk"
}

# worker_tes — judge worker sungguhan di host, pada lokakode_test dan Redis DB 15.
# Bila Docker atau image judge tidak ada, worker menolak mulai dan tes GAGAL.
worker_tes() {
	"$AKAR/infra/mysql-hak-judge.sh" lokakode_test
	"$AKAR/judge/siapkan-venv.sh"
	env_judge_tes
	(
		set -a
		# shellcheck disable=SC1091
		. "$tmp/judge.env"
		set +a
		cd judge && exec .venv/bin/python -m lokandita_judge.worker
	) > "$tmp/worker.log" 2>&1 &
	pid_worker=$!
	local i
	for ((i = 1; i <= 60; i++)); do
		if ! kill -0 "$pid_worker" 2> /dev/null; then
			cat "$tmp/worker.log" >&2
			pid_worker=""
			gagal "judge worker tes tidak mau mulai"
		fi
		if docker exec redis redis-cli -n 15 get lokandita:judge:worker 2> /dev/null | grep -q "\"pid\": $pid_worker,"; then
			echo "judge worker tes berjalan (pid $pid_worker, lokakode_test, Redis DB 15)"
			return 0
		fi
		sleep 0.5
	done
	cat "$tmp/worker.log" >&2
	gagal "judge worker tes tidak berdenyut dalam 30 detik"
}

# Peramban untuk tes LokaKode. Versinya dipatok dengan digest (Chromium 158.0.8127.37).
PERAMBAN_IMAGE="chromedp/headless-shell@sha256:8c574e39f0db5c1d9efe7c472c6e0fcfc1d91ac0be70eb1dead0f2d52cf104cc"
url_peramban=""

# peramban_tes — Chromium tanpa layar yang membuka LokaKode tes lewat HTTPS, persis
# seperti peramban mahasiswa:
#   caddy-tes     menerima https://lokakode.lokandita.com (sertifikat internal Caddy)
#                 dan meneruskannya ke lokakode-tes, seperti Caddy produksi;
#   peramban-tes  hanya berada di jaringan tes-peramban; nama lokakode.lokandita.com
#                 dipetakan ke caddy-tes dan SEMUA nama lain dibuat tidak dikenal,
#                 sehingga tes tidak mungkin menyentuh situs produksi.
# PHPUnit mengendalikannya lewat Chrome DevTools Protocol (tests/_support/Peramban.php).
# Argumen: pasangan <host>=<kontainer tes>. Untuk tes Portal ketiga host dipetakan
# (lokandita.com, lokakode.lokandita.com, auth.lokandita.com), karena halaman publik
# ketiga aplikasi diperiksa di sana.
peramban_tes() {
	docker network inspect tes-peramban > /dev/null 2>&1 || docker network create tes-peramban > /dev/null
	local pasangan host_pertama="${1%%=*}" alias_jaringan=() aturan=""
	printf '{\n\tadmin off\n\tauto_https disable_redirects\n}\n' > "$tmp/Caddyfile"
	for pasangan in "$@"; do
		printf 'https://%s {\n\ttls internal\n\treverse_proxy %s:8080\n}\n' "${pasangan%%=*}" "${pasangan#*=}" >> "$tmp/Caddyfile"
		alias_jaringan+=(--alias "${pasangan%%=*}")
	done
	chmod 644 "$tmp/Caddyfile"
	chmod 711 "$tmp"
	docker run -d --name caddy-tes --network data -v "$tmp/Caddyfile:/etc/caddy/Caddyfile:ro" caddy:2.11.9-alpine > /dev/null
	docker network connect "${alias_jaringan[@]}" tes-peramban caddy-tes
	local ip_caddy
	ip_caddy="$(docker inspect -f '{{(index .NetworkSettings.Networks "tes-peramban").IPAddress}}' caddy-tes)"
	[ -n "$ip_caddy" ] || gagal "caddy-tes tidak mendapat alamat di jaringan tes-peramban"
	for pasangan in "$@"; do aturan+="MAP ${pasangan%%=*} $ip_caddy, "; done
	docker run -d --name peramban-tes --network tes-peramban --shm-size 512m --entrypoint /bin/bash "$PERAMBAN_IMAGE" -c \
		"socat TCP4-LISTEN:9222,fork TCP4:127.0.0.1:9223 & exec /headless-shell/headless-shell --no-sandbox --use-gl=angle --use-angle=swiftshader --remote-debugging-port=9223 --ignore-certificate-errors --disable-background-networking --lang=id '--host-resolver-rules=${aturan}MAP * ~NOTFOUND' about:blank" > /dev/null
	local ip_peramban i
	ip_peramban="$(docker inspect -f '{{(index .NetworkSettings.Networks "tes-peramban").IPAddress}}' peramban-tes)"
	for ((i = 1; i <= 40; i++)); do
		if docker exec peramban-tes bash -c 'exec 3<>/dev/tcp/127.0.0.1/9223' 2> /dev/null \
			&& docker exec caddy-tes wget -q -O /dev/null --no-check-certificate "https://$host_pertama/healthz" 2> /dev/null; then
			url_peramban="http://$ip_peramban:9222"
			echo "peramban-tes berjalan (Chromium tanpa layar; hanya mengenal: $*)"
			return 0
		fi
		sleep 0.5
	done
	docker logs --tail 20 peramban-tes >&2 || true
	docker logs --tail 20 caddy-tes >&2 || true
	gagal "peramban-tes atau caddy-tes tidak siap dalam 20 detik"
}
