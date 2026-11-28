#!/usr/bin/env bash
# Membuat skema dan pengguna MySQL. Aman dijalankan berulang kali.
# Tidak menyentuh tabel atau data. Kata sandi dibaca dari infra/.env dan
# dikirim lewat stdin, tidak lewat argumen perintah.
#
#   identity, identity_test  -> auth_app
#   roster, roster_test      -> auth_app (inti Lokandita: roster akademik, KONTRAK.md §18; tabelnya
#                               dipindahkan ke sana oleh infra/mysql-pindah-roster.sh, yang juga
#                               memberi lokakode_app haknya pada tabel-tabel itu)
#   lokakode, lokakode_test  -> lokakode_app
#   portal,   portal_test    -> portal_app
#   lokaujian, lokaujian_test -> lokaujian_app (tidak ada hak pada skema lain; roster dibaca
#                               sebagai roster_reader)
#   identity_reader          -> dibuat TANPA hak; hak SELECT pada view
#                               diberikan oleh infra/mysql-hak-reader.sh (KONTRAK.md §3)
#   judge_worker             -> dibuat TANPA hak; hak pada judge_jobs diberikan
#                               oleh infra/mysql-hak-judge.sh (KONTRAK.md §6)
#   roster_reader            -> dibuat TANPA hak; hak SELECT pada lima view roster di skema
#                               roster diberikan oleh infra/mysql-hak-roster.sh (KONTRAK.md §18)
source "$(dirname "${BASH_SOURCE[0]}")/lib.sh"
ENV="$AKAR/infra/.env"

sql=""
tambah() { sql+="$1"$'\n'; }

buat_pengguna() { # <pengguna> <KUNCI kata sandi>
	local sandi
	sandi="$(nilai_env "$ENV" "$2")"
	[ -n "$sandi" ] || gagal "$2 kosong di infra/.env"
	tambah "CREATE USER IF NOT EXISTS '$1'@'%' IDENTIFIED BY '$sandi';"
	tambah "ALTER USER '$1'@'%' IDENTIFIED BY '$sandi';"
}

for pasangan in "identity:auth_app:DB_PASSWORD_AUTH" "lokakode:lokakode_app:DB_PASSWORD_LOKAKODE" "portal:portal_app:DB_PASSWORD_PORTAL" "lokaujian:lokaujian_app:DB_PASSWORD_LOKAUJIAN"; do
	IFS=: read -r skema pengguna kunci <<< "$pasangan"
	buat_pengguna "$pengguna" "$kunci"
	for s in "$skema" "${skema}_test"; do
		tambah "CREATE DATABASE IF NOT EXISTS \`$s\` CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;"
		tambah "GRANT ALL PRIVILEGES ON \`${s//_/\\_}\`.* TO '$pengguna'@'%';"
	done
done
# Skema inti untuk roster akademik: milik auth_app, seperti identity.
for s in roster roster_test; do
	tambah "CREATE DATABASE IF NOT EXISTS \`$s\` CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;"
	tambah "GRANT ALL PRIVILEGES ON \`${s//_/\\_}\`.* TO 'auth_app'@'%';"
done
buat_pengguna identity_reader DB_PASSWORD_IDENTITY_READER
buat_pengguna judge_worker DB_PASSWORD_JUDGE
buat_pengguna roster_reader DB_PASSWORD_ROSTER_READER

printf '%s' "$sql" | docker exec -i mysql sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysql -uroot' \
	|| gagal "mysql-init: perintah SQL ditolak"

echo "mysql-init: skema dan pengguna saat ini"
docker exec -i mysql sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysql -uroot -t' <<'SQL'
SELECT schema_name AS skema, default_collation_name AS kolasi
  FROM information_schema.schemata
 WHERE schema_name NOT IN ('mysql','sys','information_schema','performance_schema')
 ORDER BY 1;
SELECT user AS pengguna, host FROM mysql.user
 WHERE user IN ('auth_app','lokakode_app','portal_app','lokaujian_app','identity_reader','judge_worker','roster_reader') ORDER BY 1;
SQL
