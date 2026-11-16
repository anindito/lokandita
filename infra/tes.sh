#!/usr/bin/env bash
# Menjalankan PHPUnit sebuah aplikasi di dalam kontainer, terhadap MySQL, Redis
# dan HTTP antar-kontainer yang sungguhan. Tidak ada mock.
#
#   infra/tes.sh <auth|lokakode|portal> [argumen phpunit…]
#   infra/tes.sh judge [argumen pytest…]     worker Python: pytest di host, dengan
#                                            Docker, lokakode_test dan Redis DB 15
#
# Yang dilakukan:
#   1. membangun image lokandita/<app>:tes (dengan PHPUnit) dari pohon kerja;
#   2. membuat ulang skema *_test dari nol dan menjalankan migrasi LokaAuth;
#   3. menjalankan LokaAuth kedua bernama auth-tes (alias auth-internal-tes di
#      jaringan internal) yang memakai identity_test dan Redis DB 15;
#   4. menjalankan PHPUnit; kontainer tes dibuang sesudahnya.
# Untuk lokakode, judge worker tes juga dijalankan di host (lokakode_test, Redis
# DB 15), karena tes LokaKode mengirim pekerjaan sungguhan kepadanya.
#
# Hanya skema berakhiran _test yang disentuh; skrip menolak nama lain.
source "$(dirname "${BASH_SOURCE[0]}")/lib.sh"
cd "$AKAR"

app="${1:-}"
case "$app" in auth | lokakode | portal | judge) ;; *) gagal "pemakaian: $0 <auth|lokakode|portal|judge> [argumen]" ;; esac
shift

tmp="$(mktemp -d)"
pid_worker=""
bersih() {
	if [ -n "$pid_worker" ]; then
		kill "$pid_worker" 2> /dev/null || true
		wait "$pid_worker" 2> /dev/null || true
	fi
	docker rm -f auth-tes "$app-tes" tes-jalan > /dev/null 2>&1 || true
	rm -rf "${tmp:?}"
}
trap bersih EXIT
bersih_awal() { docker rm -f auth-tes "$app-tes" tes-jalan > /dev/null 2>&1 || true; }
bersih_awal

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
		-e 's#^(REDIS_URL=.*/)[0-9]+$#\115#' \
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
	grep -q '^REDIS_URL=.*/15$' "$tmp/$1.env" || gagal "env tes $1: REDIS_URL bukan DB 15"
}

buat_ulang_skema() { # <skema_test>
	[[ "$1" =~ ^[a-z]+_test$ ]] || gagal "menolak membuat ulang skema '$1': bukan skema *_test"
	printf 'DROP DATABASE IF EXISTS `%s`; CREATE DATABASE `%s` CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;\n' "$1" "$1" \
		| docker exec -i mysql sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysql -uroot' \
		|| gagal "tidak bisa membuat ulang skema $1"
	echo "skema $1 dibuat ulang (kosong)"
}

# jalan <app> <perintah…> — sekali jalan di image tes, di jaringan data dan internal.
jalan() {
	local a="$1"
	shift
	docker rm -f tes-jalan > /dev/null 2>&1 || true
	docker create --name tes-jalan --network data --env-file "$tmp/$a.env" \
		-e AUTH_TES_URL=http://auth-tes:8080 -e AUTH_TES_SKEMA="$skema_auth" -e APP_TES_URL="http://$app-tes:8080" \
		--entrypoint "$1" "lokandita/$a:tes" "${@:2}" > /dev/null
	docker network connect internal tes-jalan
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

# layani <app> <nama kontainer> [alias di jaringan internal]
layani() {
	docker run -d --name "$2" --network data --network-alias "$2" --env-file "$tmp/$1.env" "lokandita/$1:tes" > /dev/null
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

# Judge: tabelnya milik LokaKode, jadi skema lokakode_test dibuat oleh migrasi
# LokaKode; setelah itu pytest berjalan di host sebagai judge_worker.
if [ "$app" = "judge" ]; then
	docker info > /dev/null 2>&1 || gagal "Docker tidak tersedia: tes judge gagal, tidak dilewati"
	skema_auth=identity_test
	bangun lokakode
	env_tes lokakode
	buat_ulang_skema lokakode_test
	migrasi lokakode lokakode_test
	"$AKAR/infra/mysql-hak-judge.sh" lokakode_test
	docker exec redis redis-cli -n 15 flushdb > /dev/null
	"$AKAR/judge/siapkan-venv.sh" tes

	env_judge_tes

	echo "== pytest judge"
	kode=0
	(
		set -a
		# shellcheck disable=SC1091
		. "$tmp/judge.env"
		set +a
		cd judge && exec .venv/bin/python -m pytest "$@"
	) || kode=$?
	if [ "$kode" -ne 0 ]; then
		echo "tes judge: GAGAL (pytest keluar $kode)"
		exit "$kode"
	fi
	echo "tes judge: LULUS"
	exit 0
fi

bangun auth
env_tes auth
[ "$app" = "auth" ] || { bangun "$app"; env_tes "$app"; }

skema_auth="$(sed -n 's#^DB_DSN=.*/##p' "$tmp/auth.env")"
buat_ulang_skema "$skema_auth"
migrasi auth "$skema_auth"
if [ -x "$AKAR/infra/mysql-hak-reader.sh" ]; then "$AKAR/infra/mysql-hak-reader.sh" "$skema_auth"; fi
if [ -f "apps/auth/app/Database/Seeds/TesSeeder.php" ]; then
	jalan auth php spark db:seed TesSeeder 2>&1 | grep -v '^$' | tail -n 3
fi
docker exec redis redis-cli -n 15 flushdb > /dev/null
layani auth auth-tes auth-internal-tes

if [ "$app" != "auth" ]; then
	skema_app="$(sed -n 's#^DB_DSN=.*/##p' "$tmp/$app.env")"
	buat_ulang_skema "$skema_app"
	if ls "apps/$app/app/Database/Migrations/"*.php > /dev/null 2>&1; then
		migrasi "$app" "$skema_app"
	fi
	layani "$app" "$app-tes"
	if [ "$app" = "lokakode" ]; then worker_tes; fi
fi

echo "== phpunit $app"
kode=0
jalan "$app" vendor/bin/phpunit --no-progress --testdox "$@" || kode=$?
if [ "$kode" -ne 0 ]; then
	echo "tes $app: GAGAL (phpunit keluar $kode)"
	exit "$kode"
fi
echo "tes $app: LULUS"
