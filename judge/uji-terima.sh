#!/usr/bin/env bash
# Uji terima judge (SPEC.md §10) — gerbang tahap 7, dijalankan pemilik di server ini.
#
#   judge/uji-terima.sh
#
# Menjalankan program jahat SUNGGUHAN di kontainer sungguhan lewat infra/tes.sh
# judge (skema lokakode_test, Redis DB 15; data produksi tidak disentuh), lalu
# mencetak satu baris per butir uji terima. Keluar 0 hanya bila semuanya lulus.
# Butir 9 membebani CPU server selama kira-kira 2 menit.
set -uo pipefail
AKAR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

# "butir|tes pytest (boleh lebih dari satu, dipisah spasi)"
BUTIR=(
	"1. while(1); dimatikan tepat waktu, worker tetap sehat|tests/test_kotak_jahat.py::test_putaran_tanpa_akhir_dimatikan_tepat_waktu_lalu_program_lain_tetap_jalan tests/test_worker.py::test_while_1_menjadi_tle_dan_worker_tetap_sehat"
	"2. bom fork terkurung|tests/test_kotak_jahat.py::test_bom_fork_terkurung tests/test_kotak_jahat.py::test_paling_banyak_64_proses"
	"3. panggilan jaringan gagal|tests/test_kotak_jahat.py::test_soket_jaringan_ditolak tests/test_kotak_jahat.py::test_python_tidak_bisa_menghubungi_jaringan"
	"4. /etc/passwd tidak terbaca, langsung maupun lewat symlink di /box|tests/test_kotak_jahat.py::test_etc_passwd_tidak_terbaca_langsung_maupun_lewat_symlink"
	"5. menulis 100 MB gagal di batas ukuran berkas|tests/test_kotak_jahat.py::test_menulis_100_mb_gagal_di_batas_8_mb"
	"6. banjir stdout dihentikan di 1 MB|tests/test_kotak_jahat.py::test_banjir_stdout_dihentikan_di_1_mb"
	"7. kehabisan memori terkurung|tests/test_kotak_jahat.py::test_kehabisan_memori_c_terkurung tests/test_kotak_jahat.py::test_swap_tidak_bisa_dipakai_untuk_melewati_batas_memori"
	"8. pengawas kompilasi bekerja pada 10 detik|tests/test_kotak_jahat.py::test_pengawas_kompilasi_bekerja_pada_10_detik"
	"9. 200 pekerjaan sekaligus semuanya selesai|tests/test_worker.py::test_200_pekerjaan_sekaligus_semuanya_selesai_dan_tidak_ada_kontainer_tersisa"
)

semua=()
for b in "${BUTIR[@]}"; do
	# shellcheck disable=SC2206
	semua+=(${b#*|})
done

log="$(mktemp)"
trap 'rm -f "$log"' EXIT
echo "Menjalankan ${#semua[@]} tes dengan program jahat sungguhan (sekitar 3 menit) …"
"$AKAR/infra/tes.sh" judge -v -rP "${semua[@]}" > "$log" 2>&1
kode=$?

echo
echo "Uji terima judge — SPEC.md §10"
gagal=0
for b in "${BUTIR[@]}"; do
	nama="${b%%|*}"
	hasil="LULUS"
	for t in ${b#*|}; do
		grep -q -F "$t PASSED" "$log" || hasil="GAGAL"
	done
	[ "$hasil" = "LULUS" ] || gagal=$((gagal + 1))
	printf '  %-5s  %s\n' "$hasil" "$nama"
done

# Butir 10: setelah semua itu tidak boleh ada kontainer judge, milik worker mana pun.
sisa="$(docker ps -a --filter label=lokandita.judge=true --format '{{.Names}}' | wc -l)"
if [ "$sisa" = "0" ]; then
	printf '  %-5s  %s\n' "LULUS" "10. tidak ada kontainer judge yang tertinggal (docker ps: 0)"
else
	printf '  %-5s  %s\n' "GAGAL" "10. $sisa kontainer judge tertinggal"
	gagal=$((gagal + 1))
fi

grep -E '^200 pekerjaan RUN selesai' "$log" | sed 's/^/  catatan: /'
echo
if [ "$kode" -ne 0 ] || [ "$gagal" -gt 0 ]; then
	echo "uji terima: GAGAL ($gagal butir gagal, infra/tes.sh keluar $kode). Keluaran lengkap:"
	tail -n 40 "$log"
	exit 1
fi
echo "uji terima: LULUS — 10 dari 10 butir"
