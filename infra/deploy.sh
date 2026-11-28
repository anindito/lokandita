#!/usr/bin/env bash
# Satu-satunya cara men-deploy (KONTRAK.md §11).
#
#   infra/deploy.sh infra                    MySQL, Redis, Caddy, skema dan pengguna
#   infra/deploy.sh <auth|lokakode|portal|lokaujian>
#                                            build image dari commit sekarang, jalankan,
#                                            lalu GAGAL kecuali /healthz melaporkan commit itu;
#                                            lokaujian: juga penjadwalnya (lokaujian-jadwal),
#                                            GAGAL kecuali denyutnya melaporkan commit itu
#   infra/deploy.sh judge                    venv, image judge, layanan systemd pengguna;
#                                            GAGAL kecuali denyut worker melaporkan commit itu
#   infra/deploy.sh --verifikasi <app> <commit>   hanya pemeriksaan /healthz
#
# Menolak berjalan bila ada perubahan yang belum di-commit: yang berjalan di
# server harus selalu sama dengan sesuatu yang ada di Git.
source "$(dirname "${BASH_SOURCE[0]}")/lib.sh"
cd "$AKAR"

# verifikasi_commit <app> <commit> — berhasil hanya bila kontainer yang sedang
# berjalan menjawab /healthz dengan app dan commit yang diharapkan.
verifikasi_commit() {
	local app="$1" harap="$2" coba="${COBA:-30}" jawab="" i
	for ((i = 1; i <= coba; i++)); do
		jawab="$(docker exec "$app" curl -fsS --max-time 3 http://127.0.0.1:8080/healthz 2>/dev/null || true)"
		if [ -n "$jawab" ]; then
			break
		fi
		sleep 2
	done
	if [ -z "$jawab" ]; then
		echo "verifikasi $app: /healthz tidak menjawab setelah $coba percobaan" >&2
		return 1
	fi
	echo "verifikasi $app: /healthz -> $jawab"
	if [ "$(jq -r '.app' <<< "$jawab")" != "$app" ]; then
		echo "verifikasi $app: GAGAL, kontainer mengaku sebagai aplikasi lain" >&2
		return 1
	fi
	if [ "$(jq -r '.commit' <<< "$jawab")" != "$harap" ]; then
		echo "verifikasi $app: GAGAL, commit yang berjalan bukan $harap" >&2
		return 1
	fi
	echo "verifikasi $app: LULUS, menjalankan commit $harap"
}

tunggu_sehat() { # <kontainer>
	local i s=""
	for ((i = 1; i <= 60; i++)); do
		s="$(docker inspect -f '{{.State.Health.Status}}' "$1" 2>/dev/null || true)"
		[ "$s" = "healthy" ] && { echo "$1: healthy"; return 0; }
		sleep 2
	done
	docker logs --tail 30 "$1" >&2 || true
	gagal "$1 tidak menjadi healthy (status terakhir: ${s:-tidak ada})"
}

if [ "${1:-}" = "--verifikasi" ]; then
	[ "$#" -eq 3 ] || gagal "pemakaian: $0 --verifikasi <app> <commit>"
	verifikasi_commit "$2" "$3"
	exit $?
fi

sasaran="${1:-}"
case "$sasaran" in infra | auth | lokakode | portal | lokaujian | judge) ;; *) gagal "pemakaian: $0 <infra|auth|lokakode|portal|lokaujian|judge>" ;; esac

kotor="$(git status --porcelain)"
if [ -n "$kotor" ]; then
	echo "$kotor" >&2
	gagal "ada perubahan yang belum di-commit; commit dulu, baru deploy"
fi
commit="$(git rev-parse HEAD)"
if [ -z "$(git branch -r --contains "$commit" 2>/dev/null)" ]; then
	echo "PERINGATAN: commit $commit belum ada di origin (belum di-push)" >&2
fi

"$AKAR/infra/cek-env.sh"

if [ "$sasaran" = "infra" ]; then
	echo "== deploy infra @ $commit"
	compose up -d --no-build mysql redis
	tunggu_sehat mysql
	tunggu_sehat redis
	"$AKAR/infra/mysql-init.sh"
	compose up -d --no-build caddy
	tunggu_sehat caddy
	docker exec caddy caddy validate --config /srv/caddy/Caddyfile --adapter caddyfile 2>&1 | tail -n 1
	docker exec caddy caddy reload --config /srv/caddy/Caddyfile --adapter caddyfile 2>&1 | tail -n 1
	docker ps --filter name='^(caddy|mysql|redis)$' --format 'table {{.Names}}\t{{.Image}}\t{{.Status}}'
	echo "deploy infra: SELESAI"
	exit 0
fi

if [ "$sasaran" = "judge" ]; then
	echo "== deploy judge @ $commit"
	# Tabel yang disentuh worker (KONTRAK.md §6) dibuat oleh migrasi LokaKode.
	ada="$(printf "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='lokakode' AND table_name IN ('judge_jobs','problem_versions','problem_instances');\n" \
		| docker exec -i mysql sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysql -uroot -N')"
	[ "$ada" = "3" ] || gagal "tabel judge_jobs, problem_versions atau problem_instances belum ada di lokakode; jalankan infra/deploy.sh lokakode lebih dulu"
	"$AKAR/infra/mysql-hak-judge.sh" lokakode
	"$AKAR/judge/siapkan-venv.sh"
	if (cd "$AKAR/judge" && python3 -m lokandita_judge.citra periksa) 2> /dev/null; then :; else "$AKAR/judge/bangun-image.sh"; fi

	mkdir -p "$HOME/.config/systemd/user"
	ln -sfn "$AKAR/judge/$LAYANAN_JUDGE" "$HOME/.config/systemd/user/$LAYANAN_JUDGE"
	systemctl --user daemon-reload
	systemctl --user enable "$LAYANAN_JUDGE" > /dev/null 2>&1
	systemctl --user restart "$LAYANAN_JUDGE"

	# Bukti: denyut di Redis harus berasal dari proses layanan ini dan menyebut commit ini.
	lulus=0
	for ((i = 1; i <= 30; i++)); do
		pid="$(systemctl --user show "$LAYANAN_JUDGE" -p MainPID --value)"
		denyut="$(denyut_judge)"
		if [ -n "$denyut" ] && [ "$(jq -r '.pid' <<< "$denyut")" = "$pid" ] && [ "$(jq -r '.commit' <<< "$denyut")" = "$commit" ]; then
			lulus=1
			break
		fi
		sleep 1
	done
	if [ "$lulus" != "1" ]; then
		journalctl --user -u "$LAYANAN_JUDGE" -n 20 --no-pager >&2 || true
		gagal "deploy judge: worker tidak berdenyut dengan commit $commit (denyut terakhir: ${denyut:-tidak ada})"
	fi
	echo "verifikasi judge: denyut -> $denyut"
	echo "verifikasi judge: LULUS, worker (pid $pid) menjalankan commit $commit"
	# Worker baru sudah berjalan dengan image bertag sekarang; yang lama boleh dibuang.
	(cd "$AKAR/judge" && python3 -m lokandita_judge.citra bersih) || true
	if ! linger_aktif; then
		echo "PERINGATAN: linger belum aktif untuk $(id -un). Worker akan MATI saat tidak ada sesi login" >&2
		echo "            dan tidak menyala setelah reboot. Pemilik: sudo loginctl enable-linger $(id -un)" >&2
	fi
	echo "deploy judge: SELESAI"
	exit 0
fi

echo "== deploy $sasaran @ $commit"
docker build --quiet \
	-f infra/docker/app.Dockerfile \
	--build-arg APP="$sasaran" \
	--build-arg GIT_COMMIT="$commit" \
	-t "lokandita/$sasaran:$commit" \
	-t "lokandita/$sasaran:aktif" \
	.

# Migrasi dijalankan dari image yang baru, sebelum kontainer diganti.
# `spark migrate` keluar 0 walau gagal tersambung, jadi keluarannya diperiksa
# dan tabel `migrations` harus benar-benar ada.
if ls "apps/$sasaran/app/Database/Migrations/"*.php > /dev/null 2>&1; then
	skema="$(sed -n 's#^DB_DSN=.*/##p' "apps/$sasaran/.env")"
	keluaran="$(docker run --rm --network data --env-file "apps/$sasaran/.env" --entrypoint php \
		"lokandita/$sasaran:$commit" spark migrate --all 2>&1)" || { echo "$keluaran" >&2; gagal "migrasi $sasaran gagal"; }
	if grep -qi 'exception\|error' <<< "$keluaran"; then
		echo "$keluaran" >&2
		gagal "migrasi $sasaran melaporkan galat; kontainer lama tetap berjalan"
	fi
	ada="$(printf "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='%s' AND table_name='migrations';\n" "$skema" \
		| docker exec -i mysql sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysql -uroot -N')"
	[ "$ada" = "1" ] || gagal "migrasi $sasaran: tabel migrations tidak ada di skema $skema"
	echo "migrasi $sasaran pada skema $skema: selesai"
	if [ "$sasaran" = "auth" ]; then "$AKAR/infra/mysql-hak-reader.sh" identity; fi
	if [ "$sasaran" = "lokakode" ]; then
		"$AKAR/infra/mysql-hak-judge.sh" lokakode
		# Roster ada di skema inti `roster` (tahap A1, KONTRAK.md §18): view nama lama di skema
		# LokaKode, view kontrak dan haknya dipasang ulang di sini. Ini TIDAK memindahkan tabel;
		# bila tabelnya masih di lokakode (server baru), skrip gagal dan menyebut caranya.
		"$AKAR/infra/mysql-pindah-roster.sh" pasang lokakode
	fi
fi

compose up -d --no-build --force-recreate --no-deps "$sasaran"

if ! verifikasi_commit "$sasaran" "$commit"; then
	docker logs --tail 30 "$sasaran" >&2 || true
	gagal "deploy $sasaran: kontainer tidak menjalankan commit $commit"
fi
# Penjadwal LokaUjian (KONTRAK.md §21) berjalan dari image yang sama dan diganti bersama
# aplikasinya. Deploy GAGAL kecuali denyut di Redis membawa commit ini DAN ditulis kontainer yang
# baru (lib.sh: denyut_jadwal_sah). Penjadwal baru mengamati denyut lama sampai 25 detik sebelum
# mengambil alih, jadi ditunggu sampai 90 detik.
if [ "$sasaran" = "lokaujian" ]; then
	compose up -d --no-build --force-recreate --no-deps lokaujian-jadwal
	denyut=""
	for ((i = 1; i <= 45; i++)); do
		denyut="$(denyut_jadwal)"
		if denyut_jadwal_sah "$denyut" "$commit"; then break; fi
		sleep 2
	done
	if ! denyut_jadwal_sah "$denyut" "$commit"; then
		docker logs --tail 30 lokaujian-jadwal >&2 || true
		gagal "deploy lokaujian: kontainer penjadwal yang baru tidak berdenyut dengan commit $commit (denyut terakhir: ${denyut:-tidak ada})"
	fi
	echo "verifikasi lokaujian-jadwal: denyut -> $denyut"
	echo "verifikasi lokaujian-jadwal: LULUS, penjadwal menjalankan commit $commit"
fi
# Image lama aplikasi ini tidak dipakai lagi; kembali ke versi lama = checkout lalu deploy.
docker images --format '{{.Repository}}:{{.Tag}}' "lokandita/$sasaran" \
	| grep -v -e ":$commit\$" -e ':aktif$' | xargs -r docker rmi > /dev/null || true
echo "deploy $sasaran: SELESAI"
