#!/usr/bin/env bash
# Roster akademik di inti Lokandita (tahap A1, KONTRAK.md §18).
#
# Enam tabel roster — academic_years, courses, classes, class_teachers, enrollments,
# enrollment_moves — dibuat oleh migrasi LokaKode di skema `lokakode`, lalu DIPINDAHKAN oleh skrip
# ini ke skema `roster` dengan satu RENAME TABLE: atomik, hanya metadata, tidak ada baris yang
# disalin, id dan AUTO_INCREMENT tidak berubah, dan kunci asing dari tabel LokaKode
# (meeting_class_windows, clarifications, announcements, meetings, seb_configs,
# course_base_concepts) ikut menunjuk ke tempat baru.
#
#   infra/mysql-pindah-roster.sh status [lokakode|lokakode_test]
#   infra/mysql-pindah-roster.sh pindah [lokakode|lokakode_test]   tabel pindah, lalu `pasang`
#   infra/mysql-pindah-roster.sh pasang [lokakode|lokakode_test]   view dan hak saja; aman diulang
#   infra/mysql-pindah-roster.sh balik  [lokakode|lokakode_test]   mengembalikan ke keadaan sebelum A1
#
# `pasang` membuat:
#   - di skema LokaKode: enam view bernama sama dengan tabel lamanya (SQL SECURITY INVOKER, semua
#     kolom), supaya kode LokaKode berjalan tanpa perubahan;
#   - di skema roster: lima view kontrak roster_* (SQL SECURITY DEFINER, pembuat auth_app);
#   - hak lokakode_app pada keenam tabel: SELECT saja. Sejak tahap A2 hanya auth_app (Akun
#     Lokandita) yang menulis roster; hak INSERT, UPDATE, DELETE dari tahap A1 dicabut di sini;
#   - hak roster_reader pada lima view kontrak (infra/mysql-hak-roster.sh).
#
# Dijalankan sebagai root MySQL: tidak ada pengguna aplikasi yang berhak atas kedua skema.
# `pindah` dan `balik` pada skema produksi hanya berjalan dengan SETUJU=produksi, sesudah
# backup dan dengan LokaKode dan LokaUjian dalam pemeliharaan (docs/RESTORE.md).
# MYSQL_KONTAINER (bawaan: mysql) hanya diganti untuk gladi pada kontainer sementara.
source "$(dirname "${BASH_SOURCE[0]}")/lib.sh"

aksi="${1:-}"
asal="${2:-lokakode}"
K="${MYSQL_KONTAINER:-mysql}"
case "$aksi" in status | pindah | pasang | balik) ;; *) gagal "pemakaian: $0 <status|pindah|pasang|balik> [lokakode|lokakode_test]" ;; esac
case "$asal" in
	lokakode) inti=roster ;;
	lokakode_test) inti=roster_test ;;
	*) gagal "skema harus lokakode atau lokakode_test" ;;
esac

TABEL=(academic_years courses classes class_teachers enrollments enrollment_moves)
# View kontrak KONTRAK.md §18: nama => kolom => tabel. Nama dan urutan kolom adalah kontraknya.
declare -A KOLOM_KONTRAK=(
	[roster_academic_years]="id, label, starts_on, ends_on, status"
	[roster_courses]="id, code, name, owner_id"
	[roster_classes]="id, course_id, academic_year_id, name, archived_at"
	[roster_class_teachers]="class_id, user_id"
	[roster_enrollments]="id, user_id, course_id, academic_year_id, class_id, status, graduated_on"
)
declare -A TABEL_KONTRAK=(
	[roster_academic_years]=academic_years [roster_courses]=courses [roster_classes]=classes
	[roster_class_teachers]=class_teachers [roster_enrollments]=enrollments
)
VIEW_KONTRAK=(roster_academic_years roster_courses roster_classes roster_class_teachers roster_enrollments)

tanya() { # SQL lewat stdin -> baris tanpa judul
	docker exec -i "$K" sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysql -uroot -N'
}
daftar_in="'academic_years','courses','classes','class_teachers','enrollments','enrollment_moves'"
hitung() { # <skema> <BASE TABLE|VIEW> -> berapa dari keenam nama itu ada di sana dengan jenis itu
	printf "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='%s' AND table_type='%s' AND table_name IN (%s);\n" "$1" "$2" "$daftar_in" | tanya
}

ada_inti="$(printf "SELECT COUNT(*) FROM information_schema.schemata WHERE schema_name='%s';\n" "$inti" | tanya)"
tabel_asal="$(hitung "$asal" 'BASE TABLE')"
view_asal="$(hitung "$asal" 'VIEW')"
tabel_inti="$(hitung "$inti" 'BASE TABLE')"
keadaan="di $asal: $tabel_asal tabel dan $view_asal view; di $inti: $tabel_inti tabel (dari enam nama roster)"

if [ "$aksi" = "status" ]; then
	echo "mysql-pindah-roster ($K): $keadaan"
	exit 0
fi
[ "$ada_inti" = "1" ] || gagal "skema $inti belum ada; jalankan infra/mysql-init.sh (atau, untuk tes, buat ulang skemanya) lebih dulu"
if [ "$K" = "mysql" ] && [ "$asal" = "lokakode" ] && [ "$aksi" != "pasang" ] && [ "${SETUJU:-}" != "produksi" ]; then
	gagal "'$aksi' pada skema produksi memerlukan SETUJU=produksi (backup dulu; LokaKode dan LokaUjian dalam pemeliharaan)"
fi

pasang() {
	[ "$(hitung "$inti" 'BASE TABLE')" = "6" ] && [ "$(hitung "$asal" 'BASE TABLE')" = "0" ] \
		|| gagal "pasang: keenam tabel roster harus sudah ada di $inti dan tidak lagi di $asal ($keadaan); jalankan '$0 pindah $asal' secara sengaja"
	local sql="" t v kolom
	for v in "${VIEW_KONTRAK[@]}"; do
		# View kontrak lama di skema LokaKode (migrasi 000009) dan hak bacanya: kontraknya kini di inti.
		sql+="DROP VIEW IF EXISTS \`$asal\`.\`$v\`;"$'\n'
		sql+="REVOKE IF EXISTS SELECT ON \`$asal\`.\`$v\` FROM 'roster_reader'@'%';"$'\n'
	done
	for t in "${TABEL[@]}"; do
		kolom="$(printf "SELECT GROUP_CONCAT(CONCAT('\`', column_name, '\`') ORDER BY ordinal_position SEPARATOR ', ') FROM information_schema.columns WHERE table_schema='%s' AND table_name='%s';\n" "$inti" "$t" | tanya)"
		[ -n "$kolom" ] && [ "$kolom" != "NULL" ] || gagal "pasang: kolom $inti.$t tidak terbaca"
		sql+="CREATE OR REPLACE DEFINER='lokakode_app'@'%' SQL SECURITY INVOKER VIEW \`$asal\`.\`$t\` AS SELECT $kolom FROM \`$inti\`.\`$t\`;"$'\n'
		sql+="GRANT SELECT ON \`$inti\`.\`$t\` TO 'lokakode_app'@'%';"$'\n'
		sql+="REVOKE IF EXISTS INSERT, UPDATE, DELETE ON \`$inti\`.\`$t\` FROM 'lokakode_app'@'%';"$'\n'
	done
	for v in "${VIEW_KONTRAK[@]}"; do
		sql+="CREATE OR REPLACE DEFINER='auth_app'@'%' SQL SECURITY DEFINER VIEW \`$inti\`.\`$v\` AS SELECT ${KOLOM_KONTRAK[$v]} FROM \`$inti\`.\`${TABEL_KONTRAK[$v]}\`;"$'\n'
	done
	printf '%s' "$sql" | tanya || gagal "pasang: pembuatan view atau pemberian hak ditolak"
	MYSQL_KONTAINER="$K" "$AKAR/infra/mysql-hak-roster.sh" "$inti"
	# View kontrak berjalan dengan hak pembuatnya (auth_app). Bila auth_app tidak berhak atas skema
	# inti (infra/mysql-init.sh belum dijalankan), view-nya ada tetapi tidak terbaca: dibuktikan di sini.
	for v in "${VIEW_KONTRAK[@]}"; do
		printf 'SELECT COUNT(*) FROM `%s`.`%s`;\n' "$inti" "$v" | tanya > /dev/null 2>&1 \
			|| gagal "pasang: view $inti.$v tidak terbaca — auth_app belum berhak atas skema $inti? jalankan infra/mysql-init.sh, lalu '$0 pasang $asal'"
	done
	echo "mysql-pindah-roster: enam view nama lama di $asal, lima view kontrak di $inti, hak lokakode_app (hanya baca) dan roster_reader terpasang"
}

case "$aksi" in
	pindah)
		[ "$tabel_asal" = "6" ] && [ "$tabel_inti" = "0" ] && [ "$view_asal" = "0" ] \
			|| gagal "pindah: keenam tabel harus masih di $asal dan $inti belum memuatnya ($keadaan)"
		sql="RENAME TABLE"
		for t in "${TABEL[@]}"; do sql+=" \`$asal\`.\`$t\` TO \`$inti\`.\`$t\`,"; done
		printf '%s;\n' "${sql%,}" | tanya || gagal "pindah: RENAME TABLE ditolak; tidak ada tabel yang berpindah (satu pernyataan atomik)"
		echo "mysql-pindah-roster: enam tabel roster pindah dari $asal ke $inti (RENAME TABLE, id tidak berubah)"
		pasang
		;;
	pasang)
		pasang
		;;
	balik)
		[ "$tabel_inti" = "6" ] && [ "$tabel_asal" = "0" ] \
			|| gagal "balik: keenam tabel harus ada di $inti dan tidak di $asal ($keadaan)"
		sql=""
		for v in "${VIEW_KONTRAK[@]}"; do
			sql+="DROP VIEW IF EXISTS \`$inti\`.\`$v\`;"$'\n'
			sql+="REVOKE IF EXISTS SELECT ON \`$inti\`.\`$v\` FROM 'roster_reader'@'%';"$'\n'
		done
		for t in "${TABEL[@]}"; do
			sql+="DROP VIEW IF EXISTS \`$asal\`.\`$t\`;"$'\n'
			sql+="REVOKE IF EXISTS SELECT, INSERT, UPDATE, DELETE ON \`$inti\`.\`$t\` FROM 'lokakode_app'@'%';"$'\n'
		done
		sql+="RENAME TABLE"
		for t in "${TABEL[@]}"; do sql+=" \`$inti\`.\`$t\` TO \`$asal\`.\`$t\`,"; done
		sql="${sql%,};"$'\n'
		# Keadaan sebelum A1: lima view kontrak di skema LokaKode, pembuat lokakode_app (migrasi 000009).
		for v in "${VIEW_KONTRAK[@]}"; do
			sql+="CREATE DEFINER='lokakode_app'@'%' SQL SECURITY DEFINER VIEW \`$asal\`.\`$v\` AS SELECT ${KOLOM_KONTRAK[$v]} FROM \`$asal\`.\`${TABEL_KONTRAK[$v]}\`;"$'\n'
			sql+="GRANT SELECT ON \`$asal\`.\`$v\` TO 'roster_reader'@'%';"$'\n'
		done
		printf '%s' "$sql" | tanya || gagal "balik: ditolak di tengah jalan; periksa dengan '$0 status $asal'"
		echo "mysql-pindah-roster: enam tabel roster KEMBALI ke $asal; view kontrak dan hak roster_reader kembali ke $asal (ROSTER_READER_DSN harus menunjuk $asal lagi)"
		;;
esac
