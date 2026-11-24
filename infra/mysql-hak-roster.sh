#!/usr/bin/env bash
# Memberi `roster_reader` hak SELECT pada lima view roster KONTRAK.md §18, dan tidak
# lebih: tidak ada hak pada tabel mana pun, tidak ada hak menulis. Dijalankan sebagai root
# MySQL setelah migrasi LokaKode (view-nya dibuat migrasi itu), karena lokakode_app tidak
# berhak memberi hak. Aman dijalankan berulang kali.
#
#   infra/mysql-hak-roster.sh [lokakode|lokakode_test]     (bawaan: lokakode)
#
# Saat tabel roster pindah ke inti Lokandita (tahap A1), skema di sini ikut berganti.
source "$(dirname "${BASH_SOURCE[0]}")/lib.sh"

skema="${1:-lokakode}"
case "$skema" in lokakode | lokakode_test) ;; *) gagal "skema harus lokakode atau lokakode_test" ;; esac

sql=""
for v in roster_academic_years roster_courses roster_classes roster_class_teachers roster_enrollments; do
	sql+="GRANT SELECT ON \`$skema\`.\`$v\` TO 'roster_reader'@'%';"$'\n'
done
printf '%s' "$sql" | docker exec -i mysql sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysql -uroot' \
	|| gagal "mysql-hak-roster: pemberian hak pada $skema ditolak (view roster belum ada? jalankan migrasi LokaKode dulu)"
echo "mysql-hak-roster: roster_reader boleh SELECT lima view roster_* di $skema"
