#!/usr/bin/env bash
# Memberi `roster_reader` hak SELECT pada lima view roster KONTRAK.md §18, dan tidak
# lebih: tidak ada hak pada tabel mana pun, tidak ada hak menulis. Dijalankan sebagai root
# MySQL. Aman dijalankan berulang kali.
#
#   infra/mysql-hak-roster.sh [roster|roster_test]     (bawaan: roster)
#
# Sejak tahap A1 view kontraknya ada di skema inti `roster`, dibuat oleh
# infra/mysql-pindah-roster.sh, yang juga memanggil skrip ini. Aplikasi pembaca baru cukup
# memakai ROSTER_READER_DSN yang sama: tidak ada view atau hak baru.
source "$(dirname "${BASH_SOURCE[0]}")/lib.sh"

skema="${1:-roster}"
case "$skema" in roster | roster_test) ;; *) gagal "skema harus roster atau roster_test" ;; esac

sql=""
for v in roster_academic_years roster_courses roster_classes roster_class_teachers roster_enrollments; do
	sql+="GRANT SELECT ON \`$skema\`.\`$v\` TO 'roster_reader'@'%';"$'\n'
done
printf '%s' "$sql" | docker exec -i "${MYSQL_KONTAINER:-mysql}" sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysql -uroot' \
	|| gagal "mysql-hak-roster: pemberian hak pada $skema ditolak (view roster belum ada? jalankan infra/mysql-pindah-roster.sh)"
echo "mysql-hak-roster: roster_reader boleh SELECT lima view roster_* di $skema"
