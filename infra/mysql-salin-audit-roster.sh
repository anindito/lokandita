#!/usr/bin/env bash
# Riwayat audit roster menjadi satu (tahap A2, KONTRAK.md §18).
#
# Sampai tahap A1 perubahan roster (tahun ajaran, mata kuliah, kelas, pengajar, pendaftaran)
# dicatat LokaKode di `lokakode.audit_log`. Sejak A2 Akun Lokandita yang mencatatnya, di
# `identity.audit_log` dengan app = 'roster' dan nama aksi yang sama. Skrip ini MENYALIN baris
# roster lama ke sana, supaya /admin/audit menampilkan satu riwayat yang bersambung.
#
#   infra/mysql-salin-audit-roster.sh hitung [lokakode|lokakode_test]   hanya menghitung
#   infra/mysql-salin-audit-roster.sh salin  [lokakode|lokakode_test]   menyalin yang belum disalin
#
# - Hanya INSERT. Tidak ada baris yang diubah atau dihapus; baris asal di LokaKode TETAP ada.
# - Aman diulang: setiap salinan membawa detail.asal = 'lokakode' dan detail.asal_id = id baris
#   asalnya, dan baris yang sudah punya salinan dilewati.
# - Waktu, pelaku, sasaran, IP dan agen pengguna disalin apa adanya. Kolom LokaKode yang tidak
#   ada di identity.audit_log (target_tipe, course_id, class_id) masuk ke detail, seperti pada
#   baris yang ditulis Akun Lokandita sendiri.
# - Yang disalin hanya aksi roster. Aksi LokaKode lainnya (pertemuan, soal, jendela, sandi, …)
#   tetap hanya di LokaKode; `matkul.konsepDasar` adalah kurikulum, bukan roster.
#
# Dijalankan sebagai root MySQL: tidak ada pengguna aplikasi yang berhak atas kedua skema.
# `salin` pada skema produksi hanya berjalan dengan SETUJU=produksi.
source "$(dirname "${BASH_SOURCE[0]}")/lib.sh"

aksi="${1:-}"
asal="${2:-lokakode}"
K="${MYSQL_KONTAINER:-mysql}"
case "$aksi" in hitung | salin) ;; *) gagal "pemakaian: $0 <hitung|salin> [lokakode|lokakode_test]" ;; esac
case "$asal" in
	lokakode) tujuan=identity ;;
	lokakode_test) tujuan=identity_test ;;
	*) gagal "skema harus lokakode atau lokakode_test" ;;
esac
if [ "$K" = "mysql" ] && [ "$asal" = "lokakode" ] && [ "$aksi" = "salin" ] && [ "${SETUJU:-}" != "produksi" ]; then
	gagal "'salin' pada skema produksi memerlukan SETUJU=produksi"
fi

tanya() { # SQL lewat stdin -> baris tanpa judul
	docker exec -i "$K" sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysql -uroot -N'
}

ROSTER="(a.action LIKE 'tahun.%' OR a.action LIKE 'kelas.%' OR a.action LIKE 'pengajar.%' OR a.action LIKE 'pendaftaran.%' OR a.action IN ('matkul.dibuat', 'matkul.diubah'))"
SUDAH="EXISTS (SELECT 1 FROM \`$tujuan\`.audit_log s WHERE s.app = 'roster' AND s.detail->>'\$.asal' = 'lokakode' AND CAST(s.detail->>'\$.asal_id' AS UNSIGNED) = a.id)"

hitung() {
	printf "SELECT
		(SELECT COUNT(*) FROM \`%s\`.audit_log a WHERE %s),
		(SELECT COUNT(*) FROM \`%s\`.audit_log a WHERE %s AND %s),
		(SELECT COUNT(*) FROM \`%s\`.audit_log WHERE app = 'roster'),
		(SELECT COUNT(*) FROM \`%s\`.audit_log),
		(SELECT COUNT(*) FROM \`%s\`.audit_log);\n" \
		"$asal" "$ROSTER" "$asal" "$ROSTER" "$SUDAH" "$tujuan" "$tujuan" "$asal" | tanya
}
lapor() { # <judul>
	read -r n_asal n_sudah n_roster n_tujuan n_semua_asal <<< "$(hitung)"
	echo "$1: baris roster di $asal.audit_log: $n_asal (sudah disalin: $n_sudah) · $tujuan.audit_log: $n_tujuan baris, app=roster $n_roster · $asal.audit_log seluruhnya: $n_semua_asal"
}

lapor "mysql-salin-audit-roster ($K) sebelum"
[ "$aksi" = "hitung" ] && exit 0
semua_asal_sebelum="$n_semua_asal"
tujuan_sebelum="$n_tujuan"
belum=$((n_asal - n_sudah))

printf "INSERT INTO \`%s\`.audit_log (actor_id, action, target_id, app, detail, ip, user_agent, created_at)
	SELECT a.actor_id, a.action, a.target_id, 'roster',
		JSON_SET(COALESCE(a.detail, JSON_OBJECT()), '\$.target_tipe', a.target_tipe, '\$.course_id', a.course_id, '\$.class_id', a.class_id, '\$.asal', 'lokakode', '\$.asal_id', a.id),
		a.ip, a.user_agent, a.created_at
	FROM \`%s\`.audit_log a WHERE %s AND NOT %s ORDER BY a.id;\n" "$tujuan" "$asal" "$ROSTER" "$SUDAH" | tanya \
	|| gagal "salin: INSERT ditolak; tidak ada yang disalin (satu pernyataan)"

lapor "mysql-salin-audit-roster ($K) sesudah"
[ "$n_sudah" = "$n_asal" ] || gagal "salin: $n_asal baris roster di $asal tetapi $n_sudah yang punya salinan"
[ "$n_tujuan" = "$((tujuan_sebelum + belum))" ] || gagal "salin: $tujuan.audit_log bertambah $((n_tujuan - tujuan_sebelum)), seharusnya $belum"
[ "$n_semua_asal" = "$semua_asal_sebelum" ] || gagal "salin: jumlah baris $asal.audit_log berubah ($semua_asal_sebelum -> $n_semua_asal)"
echo "mysql-salin-audit-roster: $belum baris disalin; baris asal di $asal.audit_log tidak disentuh"
