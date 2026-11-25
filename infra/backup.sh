#!/usr/bin/env bash
# Backup harian (SPEC.md §16): semua skema aplikasi + semua .env + foto unggahan Portal
# + gambar soal LokaUjian.
# Hasil: $BACKUP_DIR/lokandita-YYYYmmdd-HHMMSS/{db.sql.gz,env.tar.gz,media.tar.gz,media-lokaujian.tar.gz,SHA256SUMS}
# Backup yang lebih tua dari BACKUP_RETENTION_DAYS hari dihapus.
#
# Isi backup: data pribadi dan rahasia (termasuk INSTANCE_SECRET dan
# SEB_ENCRYPTION_KEY). Direktori bermode 700 dan TIDAK dienkripsi.
# Backup berada di disk yang sama dengan datanya.
source "$(dirname "${BASH_SOURCE[0]}")/lib.sh"
umask 077

ENV="$AKAR/infra/.env"
dir="$(nilai_env "$ENV" BACKUP_DIR)"
hari="$(nilai_env "$ENV" BACKUP_RETENTION_DAYS)"
[ -d "$dir" ] || gagal "BACKUP_DIR tidak ada: $dir"
[[ "$hari" =~ ^[0-9]+$ ]] || gagal "BACKUP_RETENTION_DAYS bukan bilangan"

cap="$(date -u +%Y%m%d-%H%M%S)"
tujuan="$dir/lokandita-$cap"
mkdir "$tujuan"
echo "[$(date -u +%FT%TZ)] backup -> $tujuan"

# Skema *_test sengaja tidak ikut.
docker exec mysql sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysqldump -uroot \
	--single-transaction --routines --events --triggers --set-gtid-purged=OFF \
	--databases identity lokakode portal lokaujian' | gzip > "$tujuan/db.sql.gz"

# mysqldump menulis baris ini hanya bila selesai tanpa terputus.
zcat "$tujuan/db.sql.gz" | tail -n 1 | grep -q '^-- Dump completed' \
	|| gagal "dump tidak lengkap; backup di $tujuan tidak dapat dipakai"

berkas_env=()
for k in "${KOMPONEN[@]}"; do
	[ -f "$AKAR/$k/.env" ] && berkas_env+=("$k/.env")
done
tar -C "$AKAR" -czf "$tujuan/env.tar.gz" "${berkas_env[@]}"

# Foto pembuka unggahan Portal (KONTRAK.md §16): isi volume lokandita_portal_media, dibaca
# dari kontainer portal yang sedang berjalan. Dump basis data di atas diambil lebih dulu,
# dan foto tidak pernah dihapus, jadi setiap foto yang disebut dump itu ada di arsip ini.
docker exec portal tar -C /srv/media -czf - . > "$tujuan/media.tar.gz" \
	|| gagal "volume foto Portal tidak dapat dibaca; backup di $tujuan tidak lengkap"
jumlah_media="$(tar -tzf "$tujuan/media.tar.gz" | grep -c -v '/$' || true)"

# Gambar soal LokaUjian (KONTRAK.md §20): isi volume lokandita_lokaujian_media, dibaca dari
# kontainer lokaujian yang sedang berjalan. Seperti foto Portal: dump diambil lebih dulu dan
# gambar tidak pernah dihapus, jadi setiap gambar yang disebut dump itu ada di arsip ini.
docker exec lokaujian tar -C /srv/media -czf - . > "$tujuan/media-lokaujian.tar.gz" \
	|| gagal "volume gambar LokaUjian tidak dapat dibaca; backup di $tujuan tidak lengkap"
jumlah_gambar="$(tar -tzf "$tujuan/media-lokaujian.tar.gz" | grep -c -v '/$' || true)"

(cd "$tujuan" && sha256sum db.sql.gz env.tar.gz media.tar.gz media-lokaujian.tar.gz > SHA256SUMS)
echo "  db.sql.gz    $(stat -c %s "$tujuan/db.sql.gz") byte"
echo "  env.tar.gz   $(stat -c %s "$tujuan/env.tar.gz") byte, ${#berkas_env[@]} berkas .env"
echo "  media.tar.gz $(stat -c %s "$tujuan/media.tar.gz") byte, $jumlah_media berkas foto"
echo "  media-lokaujian.tar.gz $(stat -c %s "$tujuan/media-lokaujian.tar.gz") byte, $jumlah_gambar berkas gambar soal"

# Retensi.
find "${dir:?}" -maxdepth 1 -type d -name 'lokandita-????????-??????' -mtime "+$hari" -print -exec rm -rf {} + \
	| sed 's/^/  dihapus (lewat retensi): /'
echo "  tersimpan: $(find "$dir" -maxdepth 1 -type d -name 'lokandita-*' | wc -l) backup"
echo "[$(date -u +%FT%TZ)] backup SELESAI"
