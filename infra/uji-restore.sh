#!/usr/bin/env bash
# Uji restore (SPEC.md §16): memulihkan backup ke kontainer MySQL sementara
# yang terpisah, lalu membandingkan daftar tabel dengan database yang hidup; dan
# membongkar arsip foto Portal (KONTRAK.md §16) ke direktori sementara, lalu memeriksa
# bahwa setiap foto yang disebut basis data hasil restore ada di dalamnya, utuh.
# Tidak pernah menulis ke database atau volume yang hidup. Lihat docs/RESTORE.md.
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

[ -f "$sumber/media.tar.gz" ] || gagal "backup ini tidak memuat media.tar.gz (dibuat sebelum foto Portal ikut dibackup); buat backup baru dengan infra/backup.sh"

NAMA=lokandita-uji-restore
IMAGE="$(docker inspect -f '{{.Config.Image}}' mysql)"
bongkar=""
bersih() {
	docker rm -f "$NAMA" > /dev/null 2>&1 || true
	if [ -n "$bongkar" ]; then rm -rf "$bongkar"; fi
}
trap bersih EXIT
bersih
# Dibuat SESUDAH pembersihan awal di atas, yang juga menghapus direktori ini.
bongkar="$(mktemp -d)"

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

# --- Foto Portal -----------------------------------------------------------
# 1. Arsip dibongkar ke direktori sementara di host (bukan ke volume yang hidup).
tar -C "$bongkar" -xzf "$sumber/media.tar.gz" || gagal "media.tar.gz tidak dapat dibongkar"
jumlah="$(find "$bongkar" -type f | wc -l)"
echo "foto hasil restore: $jumlah berkas"

# 2. Hanya berkas buatan server yang boleh ada di dalamnya.
asing="$(find "$bongkar" -type f ! -regex '.*/hero/[0-9a-f]+-[0-9]+\.\(avif\|webp\|jpg\)' | sed "s#^$bongkar/##")"
[ -z "$asing" ] || gagal "arsip foto memuat berkas yang bukan buatan server: $asing"

# 3. Setiap foto di basis data HASIL RESTORE harus lengkap di arsip: 4 lebar × 3 format, tidak kosong.
kunci="$(docker exec -i "$NAMA" sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysql -uroot -N' <<< 'SELECT kunci FROM portal.foto ORDER BY id;')" \
	|| gagal "tabel portal.foto tidak terbaca di hasil restore"
foto=0
for k in $kunci; do
	for l in 480 768 1200 1600; do
		for f in avif webp jpg; do
			[ -s "$bongkar/hero/$k-$l.$f" ] || gagal "foto $k: berkas $k-$l.$f tidak ada di arsip atau kosong"
		done
	done
	foto=$((foto + 1))
done
echo "  $foto foto di portal.foto, semuanya lengkap di arsip (12 berkas per foto)"

# 4. Foto yang sedang tayang menurut hasil restore harus ikut pulih.
tayang="$(docker exec -i "$NAMA" sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysql -uroot -N' <<< "SELECT f.kunci FROM portal.foto_tayang t JOIN portal.foto f ON f.id = t.foto_id WHERE t.slot = 'hero';")"
if [ -n "$tayang" ]; then
	[ -s "$bongkar/hero/$tayang-1600.jpg" ] || gagal "foto yang sedang tayang ($tayang) tidak ada di arsip"
	echo "  foto yang sedang tayang ikut pulih"
else
	echo "  tidak ada foto unggahan yang tayang: halaman depan memakai foto bawaan dari image"
fi

# 5. Isi arsip sama, berkas demi berkas, dengan volume yang hidup — kecuali foto yang
#    diunggah sesudah backup dibuat, yang hanya boleh ADA di volume, tidak boleh berbeda.
hidup_media="$(docker exec portal sh -c 'cd /srv/media && find . -type f -exec sha256sum {} + | sort -k 2')"
pulih_media="$(cd "$bongkar" && find . -type f -exec sha256sum {} + | sort -k 2)"
beda="$(comm -13 <(echo "$hidup_media") <(echo "$pulih_media"))"
[ -z "$beda" ] || gagal "berkas di arsip tidak ada di volume yang hidup atau isinya berbeda: $beda"
baru="$(comm -23 <(echo "$hidup_media") <(echo "$pulih_media") | grep -c . || true)"
echo "  isi arsip sama dengan volume yang hidup ($baru berkas di volume lebih baru daripada backup)"

echo "uji-restore: LULUS — $(wc -l <<< "$pulih") skema/tabel pulih dan sama dengan yang hidup; $jumlah berkas foto pulih"
