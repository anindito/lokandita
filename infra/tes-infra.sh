#!/usr/bin/env bash
# Tes infrastruktur terhadap kontainer yang sungguh berjalan (KONTRAK.md §9–§11).
# Tidak ada mock. Hanya menulis ke skema *_test.
# Menjelang akhir, host auth dibuka beberapa detik untuk menguji saklar
# pemeliharaan, lalu dikembalikan ke keadaan semula.
source "$(dirname "${BASH_SOURCE[0]}")/lib.sh"
cd "$AKAR"

lulus=0
gagal_n=0
ok() { echo "  LULUS  $*"; lulus=$((lulus + 1)); }
tidak() { echo "  GAGAL  $*"; gagal_n=$((gagal_n + 1)); }
# sama <nama tes> <didapat> <diharapkan>
sama() { if [ "$2" = "$3" ]; then ok "$1: $2"; else tidak "$1: didapat '$2', diharapkan '$3'"; fi; }

head="$(git rev-parse HEAD)"
tmp="$(mktemp -d)"
buka_awal=0
sudah_buka auth && buka_awal=1
bersih() {
	rm -rf "${tmp:?}"
	if [ "$buka_awal" = "0" ]; then rm -f "${AKAR:?}/infra/caddy/bendera/auth.buka"; fi
}
trap bersih EXIT

echo "A. kontainer, jaringan, port (§9)"
for c in caddy mysql redis auth lokakode portal; do
	sama "$c berjalan" "$(docker inspect -f '{{.State.Running}}' "$c" 2>/dev/null || echo tidak-ada)" "true"
done
jaringan() { docker inspect -f '{{range $k, $v := .NetworkSettings.Networks}}{{$k}} {{end}}' "$1" | xargs -n 1 | sort | xargs; }
sama "jaringan caddy" "$(jaringan caddy)" "edge"
sama "jaringan mysql" "$(jaringan mysql)" "data"
sama "jaringan redis" "$(jaringan redis)" "data"
for a in "${APLIKASI[@]}"; do sama "jaringan $a" "$(jaringan "$a")" "data edge internal"; done
if docker inspect -f '{{range .NetworkSettings.Networks.internal.Aliases}}{{.}} {{end}}' auth | grep -qw auth-internal; then
	ok "auth beralias auth-internal di jaringan internal"
else
	tidak "auth tidak beralias auth-internal di jaringan internal"
fi
sama "jaringan internal tanpa jalan keluar" "$(docker network inspect -f '{{.Internal}}' internal)" "true"
for a in "${APLIKASI[@]}"; do sama "$a tidak membuka port ke host" "$(docker port "$a" | wc -l)" "0"; done
sama "mysql hanya di loopback" "$(docker port mysql | xargs)" "3306/tcp -> 127.0.0.1:3306"
sama "redis hanya di loopback" "$(docker port redis | xargs)" "6379/tcp -> 127.0.0.1:6379"
for a in "${APLIKASI[@]}"; do
	if [ "$(docker exec "$a" id -u)" != "0" ]; then ok "$a berjalan bukan sebagai root (uid $(docker exec "$a" id -u))"; else tidak "$a berjalan sebagai root"; fi
done

echo "B. /healthz dan commit (§11)"
for a in "${APLIKASI[@]}"; do
	sama "$a /healthz" "$(docker exec "$a" curl -fsS --max-time 3 http://127.0.0.1:8080/healthz 2>&1 || true)" "{\"app\":\"$a\",\"commit\":\"$head\"}"
done
if COBA=1 infra/deploy.sh --verifikasi auth "$head" > /dev/null 2>&1; then ok "deploy --verifikasi menerima commit yang benar"; else tidak "deploy --verifikasi menolak commit yang benar"; fi
if COBA=1 infra/deploy.sh --verifikasi auth 0000000000000000000000000000000000000000 > /dev/null 2>&1; then
	tidak "deploy --verifikasi menerima commit yang salah"
else
	ok "deploy --verifikasi menolak commit yang salah"
fi
if COBA=1 infra/deploy.sh --verifikasi lokakode "$head" > /dev/null 2>&1 && ! docker exec lokakode curl -fsS http://127.0.0.1:8080/healthz | grep -q '"app":"auth"'; then
	ok "tiap kontainer melaporkan namanya sendiri"
else
	tidak "kontainer lokakode tidak melaporkan dirinya sebagai lokakode"
fi

echo "C. HTTP antar-kontainer (§5, §9)"
for a in lokakode portal; do
	sama "$a -> http://auth-internal:8080/healthz" "$(docker exec "$a" sh -c 'curl -fsS --max-time 3 "$AUTH_INTERNAL_URL/healthz"' 2>&1 | jq -r .app 2>&1 || true)" "auth"
done
if docker exec caddy sh -c 'nc -z -w 3 mysql 3306' > /dev/null 2>&1; then tidak "caddy bisa mencapai mysql"; else ok "caddy tidak bisa mencapai mysql"; fi
if docker exec caddy sh -c 'nc -z -w 3 redis 6379' > /dev/null 2>&1; then tidak "caddy bisa mencapai redis"; else ok "caddy tidak bisa mencapai redis"; fi
if docker exec caddy sh -c 'nc -z -w 3 auth-internal 8080' > /dev/null 2>&1; then tidak "caddy bisa mencapai auth-internal"; else ok "caddy tidak bisa mencapai auth-internal"; fi
sama "caddy -> auth:8080 lewat edge" "$(docker exec caddy wget -q -O - http://auth:8080/healthz 2>&1 | jq -r .app 2>&1 || true)" "auth"

echo "D. .env benar-benar sampai ke kontainer (§10)"
for a in "${APLIKASI[@]}"; do
	sama "$a membaca APP_URL dari apps/$a/.env" "$(docker exec "$a" printenv APP_URL)" "$(nilai_env "apps/$a/.env" APP_URL)"
done

echo "E. MySQL: tiap aplikasi hanya pada skemanya sendiri"
# Dijalankan di dalam kontainer aplikasi, memakai DB_DSN milik kontainer itu.
uji_db() { # <kontainer> <skema lain>
	docker exec -i -e LAIN="$2" "$1" php 2>&1 <<'PHP' || true
<?php
mysqli_report(MYSQLI_REPORT_OFF);
$u = parse_url(getenv('DB_DSN'));
$skema = ltrim($u['path'], '/');
$m = @new mysqli($u['host'], $u['user'], $u['pass'], $skema, $u['port']);
if ($m->connect_errno) { echo 'koneksi-gagal-', $m->connect_errno; exit; }
$out = [$m->query('SELECT DATABASE()')->fetch_row()[0]];
$uji = $skema . '_test';
if (! str_ends_with($uji, '_test')) { echo 'bukan-skema-test'; exit; }
$t = "`{$uji}`.`tes_infra`";
$tulis = $m->query("CREATE TABLE {$t} (id INT PRIMARY KEY)") && $m->query("INSERT INTO {$t} VALUES (1)")
    && $m->query("SELECT id FROM {$t}")->num_rows === 1 && $m->query("DROP TABLE {$t}");
$out[] = $tulis ? 'tulis-test-ok' : 'tulis-test-gagal-' . $m->errno;
$out[] = $m->query('SELECT 1 FROM `' . getenv('LAIN') . '`.`x`') === false ? 'lain-ditolak-' . $m->errno : 'lain-TERBACA';
$out[] = $m->query('SELECT @@global.time_zone')->fetch_row()[0];
echo implode(' ', $out);
PHP
}
sama "auth" "$(uji_db auth lokakode)" "identity tulis-test-ok lain-ditolak-1142 +00:00"
sama "lokakode" "$(uji_db lokakode identity)" "lokakode tulis-test-ok lain-ditolak-1142 +00:00"
sama "portal" "$(uji_db portal identity)" "portal tulis-test-ok lain-ditolak-1142 +00:00"

echo "F. Redis: wajib kata sandi, terjangkau dari tiap aplikasi"
for a in "${APLIKASI[@]}"; do
	sama "$a -> redis" "$(docker exec -i "$a" php 2>&1 <<'PHP' || true
<?php
$u = parse_url(getenv('REDIS_URL'));
try {
    $r = new Redis();
    $r->connect($u['host'], $u['port'], 3);
    $r->auth($u['pass']);
    $r->select((int) ltrim($u['path'], '/'));
    echo $r->ping() ? 'PONG' : 'tidak-ada-jawaban';
} catch (Throwable $e) {
    echo 'gagal: ', $e->getMessage();
}
PHP
)" "PONG"
done
if docker exec redis sh -c 'unset REDISCLI_AUTH; redis-cli ping' 2>&1 | grep -q NOAUTH; then ok "redis menolak tanpa kata sandi"; else tidak "redis menjawab tanpa kata sandi"; fi

echo "G. aplikasi menolak mulai bila ada variabel yang hilang (§10)"
for a in "${APLIKASI[@]}"; do
	grep -v '^APP_URL=' "apps/$a/.env" > "$tmp/kurang.env"
	chmod 600 "$tmp/kurang.env"
	set +e
	keluaran="$(docker run --rm --network none --env-file "$tmp/kurang.env" "lokandita/$a:aktif" 2>&1)"
	kode=$?
	set -e
	if [ "$kode" -ne 0 ] && grep -q 'APP_URL' <<< "$keluaran"; then ok "$a: keluar $kode — $keluaran"; else tidak "$a: keluar $kode — $keluaran"; fi
done

echo "H. cek-env.sh"
if infra/cek-env.sh > /dev/null 2>&1; then ok "cek-env lulus pada .env yang sekarang"; else tidak "cek-env gagal pada .env yang sekarang"; fi
mkdir "$tmp/komponen"
cp apps/auth/.env.example "$tmp/komponen/.env.example"
grep -v '^MFA_ENCRYPTION_KEY=' apps/auth/.env > "$tmp/komponen/.env"
chmod 600 "$tmp/komponen/.env"
set +e
keluaran="$(infra/cek-env.sh "$tmp/komponen" 2>&1)"
kode=$?
set -e
if [ "$kode" -ne 0 ] && grep -q 'MFA_ENCRYPTION_KEY ada di .env.example tetapi tidak ada di .env' <<< "$keluaran"; then
	ok "cek-env gagal dan menyebut kunci yang hilang"
else
	tidak "cek-env tidak menangkap kunci yang hilang (keluar $kode)"
fi
chmod 644 "$tmp/komponen/.env"
keluaran="$(infra/cek-env.sh "$tmp/komponen" 2>&1 || true)"
if grep -q 'bermode 644' <<< "$keluaran"; then ok "cek-env menangkap mode berkas 644"; else tidak "cek-env tidak menangkap mode berkas 644"; fi
bocor="$(git ls-files | grep -E '(^|/)\.env$' || true)"
sama "tidak ada .env di Git" "${bocor:-tidak ada}" "tidak ada"

echo "I. saklar pemeliharaan dan tepi (§5)"
h="$(host_aplikasi auth)"
kode() { curl -sS -o /dev/null --max-time 15 -w '%{http_code}' "$1" 2>&1 || true; }
infra/pemeliharaan.sh auth off > /dev/null
sleep 1
sama "auth dibuka: https://$h/healthz" "$(curl -sS --max-time 15 "https://$h/healthz" 2>&1 | jq -r .app 2>&1 || true)" "auth"
sama "auth dibuka: /internal/x tetap 404 dari internet" "$(kode "https://$h/internal/x")" "404"
sama "auth dibuka: host lain tetap pemeliharaan" "$(kode "https://$(host_aplikasi lokakode)/healthz")" "503"
if [ "$buka_awal" = "0" ]; then
	infra/pemeliharaan.sh auth on > /dev/null
	sleep 1
	sama "auth ditutup lagi: https://$h/healthz" "$(kode "https://$h/healthz")" "503"
fi

echo
echo "tes-infra: $lulus lulus, $gagal_n gagal"
[ "$gagal_n" -eq 0 ] || { echo "tes-infra: GAGAL"; exit 1; }
echo "tes-infra: LULUS"
