#!/usr/bin/env bash
# Menjalankan PHPUnit sebuah aplikasi di dalam kontainer, terhadap MySQL, Redis
# dan HTTP antar-kontainer yang sungguhan. Tidak ada mock.
#
#   infra/tes.sh <auth|lokakode|portal|lokaujian> [argumen phpunit…]
#   infra/tes.sh judge [argumen pytest…]     worker Python: pytest di host, dengan
#                                            Docker, lokakode_test dan Redis DB 15
#
# Yang dilakukan:
#   1. membangun image lokandita/<app>:tes (dengan PHPUnit) dari pohon kerja;
#   2. membuat ulang skema *_test dari nol dan menjalankan migrasi LokaAuth;
#   3. menjalankan LokaAuth kedua bernama auth-tes (alias auth-internal-tes di
#      jaringan internal) yang memakai identity_test dan Redis DB 15;
#   4. menjalankan PHPUnit; kontainer tes dibuang sesudahnya.
# Untuk lokakode, judge worker tes juga dijalankan di host (lokakode_test, Redis
# DB 15), karena tes LokaKode mengirim pekerjaan sungguhan kepadanya; dan sebuah
# peramban Chromium tanpa layar (peramban-tes) dijalankan untuk tes yang hanya
# bisa dibuktikan di peramban: editor, CSP, blokir tempel, simpan draf otomatis.
# Untuk portal, LokaKode tes (lokakode-tes, pada lokakode_test) juga dilayani:
# gerbang tahap 11 menguji sesi yang sama di Portal dan LokaKode sungguhan.
# Untuk lokaujian, skema lokakode_test dibuat oleh migrasi LokaKode (view roster,
# KONTRAK.md §18) dan roster_reader diberi haknya di sana: LokaUjian membaca kelas
# dan pendaftaran hanya lewat view itu. LokaKode sendiri tidak dilayani.
#
# Hanya skema berakhiran _test yang disentuh; skrip menolak nama lain.
source "$(dirname "${BASH_SOURCE[0]}")/lib.sh"
cd "$AKAR"

app="${1:-}"
case "$app" in auth | lokakode | portal | lokaujian | judge) ;; *) gagal "pemakaian: $0 <auth|lokakode|portal|lokaujian|judge> [argumen]" ;; esac
shift

tmp="$(mktemp -d)"
pid_worker=""
bersih() {
	if [ -n "$pid_worker" ]; then
		kill "$pid_worker" 2> /dev/null || true
		wait "$pid_worker" 2> /dev/null || true
	fi
	docker rm -f auth-tes "$app-tes" lokakode-tes lokakode-seb-tes lokaujian-seb-tes tes-jalan caddy-tes peramban-tes > /dev/null 2>&1 || true
	rm -rf "${tmp:?}"
}
trap bersih EXIT
bersih_awal() { docker rm -f auth-tes "$app-tes" lokakode-tes lokakode-seb-tes lokaujian-seb-tes tes-jalan caddy-tes peramban-tes > /dev/null 2>&1 || true; }
bersih_awal

# Fungsi bangun, env_tes, buat_ulang_skema, jalan, migrasi, layani, worker_tes, peramban_tes.
source "$AKAR/infra/lib-tes.sh"

# Judge: tabelnya milik LokaKode, jadi skema lokakode_test dibuat oleh migrasi
# LokaKode; setelah itu pytest berjalan di host sebagai judge_worker.
if [ "$app" = "judge" ]; then
	docker info > /dev/null 2>&1 || gagal "Docker tidak tersedia: tes judge gagal, tidak dilewati"
	skema_auth=identity_test
	bangun lokakode
	env_tes lokakode
	buat_ulang_skema lokakode_test
	migrasi lokakode lokakode_test
	"$AKAR/infra/mysql-hak-judge.sh" lokakode_test
	docker exec redis redis-cli -n 15 flushdb > /dev/null
	"$AKAR/judge/siapkan-venv.sh" tes

	env_judge_tes

	echo "== pytest judge"
	kode=0
	(
		set -a
		# shellcheck disable=SC1091
		. "$tmp/judge.env"
		set +a
		cd judge && exec .venv/bin/python -m pytest "$@"
	) || kode=$?
	if [ "$kode" -ne 0 ]; then
		echo "tes judge: GAGAL (pytest keluar $kode)"
		exit "$kode"
	fi
	echo "tes judge: LULUS"
	exit 0
fi

bangun auth
env_tes auth
[ "$app" = "auth" ] || { bangun "$app"; env_tes "$app"; }

skema_auth="$(sed -n 's#^DB_DSN=.*/##p' "$tmp/auth.env")"
buat_ulang_skema "$skema_auth"
migrasi auth "$skema_auth"
if [ -x "$AKAR/infra/mysql-hak-reader.sh" ]; then "$AKAR/infra/mysql-hak-reader.sh" "$skema_auth"; fi
if [ -f "apps/auth/app/Database/Seeds/TesSeeder.php" ]; then
	jalan auth php spark db:seed TesSeeder 2>&1 | grep -v '^$' | tail -n 3
fi
docker exec redis redis-cli -n 15 flushdb > /dev/null
layani auth auth-tes auth-internal-tes

if [ "$app" != "auth" ]; then
	skema_app="$(sed -n 's#^DB_DSN=.*/##p' "$tmp/$app.env")"
	buat_ulang_skema "$skema_app"
	if ls "apps/$app/app/Database/Migrations/"*.php > /dev/null 2>&1; then
		migrasi "$app" "$skema_app"
	fi
	# LokaUjian membaca roster dari view di skema LokaKode (KONTRAK.md §18): skema itu dibuat
	# oleh migrasi LokaKode sendiri, lalu roster_reader diberi hak SELECT pada kelima view.
	if [ "$app" = "lokaujian" ]; then
		bangun lokakode
		env_tes lokakode
		buat_ulang_skema lokakode_test
		migrasi lokakode lokakode_test
		"$AKAR/infra/mysql-hak-roster.sh" lokakode_test
	fi
	layani "$app" "$app-tes"
	if [ "$app" = "lokaujian" ]; then
		layani lokaujian lokaujian-seb-tes "" lokaujian-seb
		# Halaman ujian (simpan otomatis, hitung mundur, lanjut, tanpa sambungan, blokir tempel)
		# hanya bisa dibuktikan di peramban (KONTRAK.md §12).
		peramban_tes lokaujian.lokandita.com=lokaujian-tes
		# Penjadwal (KONTRAK.md §21) dijalankan sekali pada skema tes; tes-tesnya sendiri
		# menjalankannya lagi sesudah menggeser waktu.
		echo "== penjadwal sekali jalan pada skema tes"
		jalan lokaujian php spark ujian:jadwal --sekali --no-header || gagal "ujian:jadwal --sekali gagal pada skema tes"
	fi
	if [ "$app" = "lokakode" ]; then
		layani lokakode lokakode-seb-tes "" lokakode-seb
		worker_tes
		peramban_tes lokakode.lokandita.com=lokakode-tes
	fi
	# Gerbang tahap 11 (SPEC.md §18): "login dari Portal sampai ke LokaKode tanpa login
	# kedua" hanya bisa dibuktikan dengan ketiga aplikasi berjalan sungguhan. Untuk
	# Portal, LokaKode tes juga dilayani (lokakode-tes, pada lokakode_test); tesnya
	# menemukannya lewat LOKAKODE_TES_URL. Judge worker tidak diperlukan di sini.
	if [ "$app" = "portal" ]; then
		bangun lokakode
		env_tes lokakode
		buat_ulang_skema lokakode_test
		migrasi lokakode lokakode_test
		layani lokakode lokakode-tes
		# Tata letak halaman publik (kanan-ke-kiri) hanya bisa dibuktikan di peramban.
		peramban_tes lokandita.com=portal-tes lokakode.lokandita.com=lokakode-tes auth.lokandita.com=auth-tes
	fi
fi

echo "== phpunit $app"
kode=0
jalan "$app" vendor/bin/phpunit --no-progress --testdox "$@" || kode=$?
if [ "$kode" -ne 0 ]; then
	echo "tes $app: GAGAL (phpunit keluar $kode)"
	exit "$kode"
fi
echo "tes $app: LULUS"
