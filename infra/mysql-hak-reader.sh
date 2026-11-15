#!/usr/bin/env bash
# Memberi `identity_reader` hak SELECT pada tiga obyek KONTRAK.md §3, dan tidak
# lebih. Dijalankan sebagai root MySQL setelah migrasi LokaAuth, karena
# auth_app tidak berhak memberi hak. Aman dijalankan berulang kali.
#
#   infra/mysql-hak-reader.sh [identity|identity_test]     (bawaan: identity)
source "$(dirname "${BASH_SOURCE[0]}")/lib.sh"

skema="${1:-identity}"
case "$skema" in identity | identity_test) ;; *) gagal "skema harus identity atau identity_test" ;; esac

docker exec -i mysql sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysql -uroot' <<SQL || gagal "mysql-hak-reader: pemberian hak pada $skema ditolak"
GRANT SELECT ON \`$skema\`.\`session_identity\` TO 'identity_reader'@'%';
GRANT SELECT ON \`$skema\`.\`user_directory\`   TO 'identity_reader'@'%';
GRANT SELECT ON \`$skema\`.\`app_access\`       TO 'identity_reader'@'%';
SQL
echo "mysql-hak-reader: identity_reader boleh SELECT session_identity, user_directory, app_access di $skema"
