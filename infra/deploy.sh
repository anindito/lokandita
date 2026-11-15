#!/usr/bin/env bash
# Satu-satunya cara men-deploy (KONTRAK.md §11).
#
#   infra/deploy.sh infra                    MySQL, Redis, Caddy, skema dan pengguna
#   infra/deploy.sh <auth|lokakode|portal>   build image dari commit sekarang, jalankan,
#                                            lalu GAGAL kecuali /healthz melaporkan commit itu
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
case "$sasaran" in infra | auth | lokakode | portal) ;; *) gagal "pemakaian: $0 <infra|auth|lokakode|portal>" ;; esac

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

echo "== deploy $sasaran @ $commit"
docker build --quiet \
	-f infra/docker/app.Dockerfile \
	--build-arg APP="$sasaran" \
	--build-arg GIT_COMMIT="$commit" \
	-t "lokandita/$sasaran:$commit" \
	-t "lokandita/$sasaran:aktif" \
	.
compose up -d --no-build --force-recreate --no-deps "$sasaran"

if ! verifikasi_commit "$sasaran" "$commit"; then
	docker logs --tail 30 "$sasaran" >&2 || true
	gagal "deploy $sasaran: kontainer tidak menjalankan commit $commit"
fi
echo "deploy $sasaran: SELESAI"
