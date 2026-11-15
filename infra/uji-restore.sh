#!/usr/bin/env bash
# Uji restore (SPEC.md §16): memulihkan backup ke kontainer MySQL sementara
# yang terpisah, lalu membandingkan daftar tabel dengan database yang hidup.
# Tidak pernah menulis ke database yang hidup. Lihat docs/RESTORE.md.
#
#   infra/uji-restore.sh            backup terbaru
#   infra/uji-restore.sh <direktori backup>
source "$(dirname "${BASH_SOURCE[0]}")/lib.sh"

dir="$(nilai_env "$AKAR/infra/.env" BACKUP_DIR)"
sumber="${1:-$(find "$dir" -maxdepth 1 -type d -name 'lokandita-*' | sort | tail -n 1)}"
[ -n "$sumber" ] && [ -f "$sumber/db.sql.gz" ] || gagal "tidak ada backup untuk diuji di $dir"
echo "menguji: $sumber"

(cd "$sumber" && sha256sum -c SHA256SUMS) || gagal "checksum backup tidak cocok"
tar -tzf "$sumber/env.tar.gz" | sed 's/^/  env.tar.gz berisi: /'

NAMA=lokandita-uji-restore
IMAGE="$(docker inspect -f '{{.Config.Image}}' mysql)"
bersih() { docker rm -f "$NAMA" > /dev/null 2>&1 || true; }
trap bersih EXIT
bersih

# Server sementara saat inisialisasi image tidak mendengarkan TCP, jadi
# menunggu lewat TCP berarti menunggu server yang sebenarnya.
# Tanpa jaringan dan tanpa port: kontainer ini hanya bisa dicapai lewat docker exec.
docker run -d --name "$NAMA" --network none -e MYSQL_ROOT_PASSWORD="$(openssl rand -hex 16)" "$IMAGE" > /dev/null
for _ in $(seq 1 60); do
	docker exec "$NAMA" sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" mysql -uroot -h 127.0.0.1 --protocol=tcp -e "SELECT 1"' > /dev/null 2>&1 && break
	sleep 2
done

zcat "$sumber/db.sql.gz" | docker exec -i "$NAMA" sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysql -uroot' \
	|| gagal "dump tidak dapat dimuat"

daftar() { # <kontainer>
	docker exec -i "$1" sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysql -uroot -N' <<'SQL'
SELECT CONCAT(s.schema_name, IFNULL(CONCAT('.', t.table_name), ' (tanpa tabel)'))
  FROM information_schema.schemata s
  LEFT JOIN information_schema.tables t ON t.table_schema = s.schema_name
 WHERE s.schema_name IN ('identity','lokakode','portal')
 ORDER BY 1;
SQL
}

hidup="$(daftar mysql)"
pulih="$(daftar "$NAMA")"
echo "hasil restore:"
sed 's/^/  /' <<< "$pulih"

if [ "$hidup" != "$pulih" ]; then
	diff <(echo "$hidup") <(echo "$pulih") | sed 's/^/  beda: /' || true
	gagal "daftar skema/tabel hasil restore berbeda dari database yang hidup"
fi
echo "uji-restore: LULUS — $(wc -l <<< "$pulih") skema/tabel pulih dan sama dengan yang hidup"
