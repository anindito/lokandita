#!/usr/bin/env bash
# Uji asap dengan permintaan sungguhan (KONTRAK.md §11). Jalankan setelah
# setiap deploy. Keluar 0 hanya bila semua cek yang AKTIF lulus.
#
# Cek 3–5 membutuhkan LokaAuth (tahap 5) dan cek 6 membutuhkan judge (tahap 7).
# Sampai saat itu keduanya dicetak sebagai "BELUM AKTIF" — bukan lulus.
# Mengaktifkan: ubah angka di bawah menjadi 1 pada tahap yang bersangkutan.
CEK_AUTH_AKTIF=1   # tahap 5: cek 3, 4, 5 — aktif sejak tahap 5
CEK_JUDGE_AKTIF=1  # tahap 7: cek 6 — aktif sejak tahap 7; cek 7 (VALIDATE) sejak tahap 8
# Cek 8 (halaman publik, tahap 11) selalu aktif.

source "$(dirname "${BASH_SOURCE[0]}")/lib.sh"

lulus=0
gagal_n=0
tunda=0
ok() { echo "  LULUS  $*"; lulus=$((lulus + 1)); }
tidak() { echo "  GAGAL  $*"; gagal_n=$((gagal_n + 1)); }
belum() { echo "  BELUM AKTIF  $*"; tunda=$((tunda + 1)); }

domain="$(nilai_env "$AKAR/infra/.env" DOMAIN)"
kode() { curl -sS -o /dev/null --max-time 15 -w '%{http_code}' "$1" 2>&1 || true; }

echo "0. kontainer aplikasi menjalankan commit HEAD"
head="$(git -C "$AKAR" rev-parse HEAD)"
for a in "${APLIKASI[@]}"; do
	jawab="$(docker exec "$a" curl -fsS --max-time 3 http://127.0.0.1:8080/healthz 2>/dev/null || true)"
	if [ "$(jq -r '.commit' <<< "$jawab" 2>/dev/null)" = "$head" ]; then
		ok "$a -> $jawab"
	else
		tidak "$a seharusnya commit $head, /healthz menjawab: ${jawab:-<tidak ada jawaban>}"
	fi
done

echo "1. setiap host publik menjawab lewat HTTPS dengan sertifikat yang sah"
for a in "${APLIKASI[@]}"; do
	h="$(host_aplikasi "$a")"
	k="$(kode "https://$h/")"
	if sudah_buka "$a"; then
		case "$k" in 2?? | 3??) ok "https://$h/ -> $k (dibuka)" ;; *) tidak "https://$h/ -> $k (dibuka, seharusnya 2xx/3xx)" ;; esac
	else
		if [ "$k" = "503" ]; then ok "https://$h/ -> 503 (halaman pemeliharaan)"; else tidak "https://$h/ -> $k (seharusnya 503 pemeliharaan)"; fi
	fi
done
k="$(curl -sS -o /dev/null --max-time 15 -w '%{http_code} %{redirect_url}' "https://www.$domain/" 2>&1 || true)"
if [ "$k" = "301 https://$domain/" ]; then ok "https://www.$domain/ -> $k"; else tidak "https://www.$domain/ -> $k (seharusnya 301 ke https://$domain/)"; fi

echo "2. /internal/* dijawab 404 dari internet di setiap host"
for h in $(daftar_host); do
	k="$(kode "https://$h/internal/session/touch")"
	if [ "$k" = "404" ]; then ok "https://$h/internal/session/touch -> 404"; else tidak "https://$h/internal/session/touch -> $k (seharusnya 404)"; fi
done

# Dari dalam kontainer klien, dengan token milik kontainer itu sendiri.
# Mencetak hanya kode status dan jenis isi, tidak pernah tokennya.
sentuh() { # <kontainer> <benar|salah>
	docker exec -e MODE="$2" "$1" sh -c '
		token="$AUTH_SERVICE_TOKEN"
		[ "$MODE" = "salah" ] && token="token-yang-salah"
		curl -s --max-time 5 -o /tmp/smoke-body -w "%{http_code}" -X POST \
			-H "Content-Type: application/json" -H "X-Service-Token: $token" \
			-d "{\"sid\":\"sid-palsu-untuk-smoke\"}" "$AUTH_INTERNAL_URL/internal/session/touch"
		if head -c 1 /tmp/smoke-body | grep -q "{"; then echo " json"; else echo " bukan-json"; fi
	' 2>&1 || true
}

echo "3. lokakode, portal dan lokaujian mencapai API internal LokaAuth dengan token masing-masing"
if [ "$CEK_AUTH_AKTIF" = "1" ]; then
	for a in lokakode portal lokaujian; do
		j="$(sentuh "$a" benar)"
		case "$j" in "422 json" | "404 json") ok "$a -> $j" ;; *) tidak "$a -> $j (seharusnya 422, atau 404 berisi JSON; 401 berarti token salah)" ;; esac
	done
else
	belum "menunggu API internal LokaAuth (tahap 5)"
fi

echo "4. token yang salah ditolak 401"
if [ "$CEK_AUTH_AKTIF" = "1" ]; then
	for a in lokakode portal lokaujian; do
		j="$(sentuh "$a" salah)"
		case "$j" in "401 json") ok "$a -> $j" ;; *) tidak "$a -> $j (seharusnya 401)" ;; esac
	done
else
	belum "menunggu API internal LokaAuth (tahap 5)"
fi

echo "5. identity_reader bisa membaca session_identity dan tidak bisa membaca users"
if [ "$CEK_AUTH_AKTIF" = "1" ]; then
	for a in lokakode lokaujian; do
	j="$(docker exec -i "$a" php <<'PHP' 2>&1 || true
<?php
mysqli_report(MYSQLI_REPORT_OFF);
$u = parse_url(getenv('IDENTITY_READER_DSN'));
$m = @new mysqli($u['host'], $u['user'], $u['pass'], ltrim($u['path'], '/'), $u['port']);
if ($m->connect_errno) { echo 'koneksi gagal: ', $m->connect_errno; exit; }
$a = $m->query('SELECT 1 FROM session_identity LIMIT 1') !== false ? 'view-terbaca' : 'view-ditolak-' . $m->errno;
$b = $m->query('SELECT 1 FROM users LIMIT 1') === false ? 'users-ditolak-' . $m->errno : 'users-TERBACA';
echo $a, ' ', $b;
PHP
)"
	case "$j" in "view-terbaca users-ditolak-1142") ok "$a: $j" ;; *) tidak "$a: $j" ;; esac
	done
else
	belum "menunggu skema identity dan view-nya (tahap 5)"
fi

echo "6. judge worker berjalan dan satu job RUN sederhana selesai"
if [ "$CEK_JUDGE_AKTIF" = "1" ]; then
	status="$(systemctl --user is-active "$LAYANAN_JUDGE" 2>&1 || true)"
	if [ "$status" = "active" ]; then ok "layanan $LAYANAN_JUDGE: active"; else tidak "layanan $LAYANAN_JUDGE: $status (seharusnya active)"; fi

	denyut="$(denyut_judge)"
	if [ -n "$denyut" ] && [ "$(jq -r '.commit' <<< "$denyut" 2>/dev/null)" = "$head" ]; then
		ok "denyut worker -> $denyut"
	else
		tidak "worker seharusnya commit $head, denyut di Redis: ${denyut:-<tidak ada>}"
	fi

	if linger_aktif; then
		ok "linger aktif: worker tetap hidup tanpa sesi login dan menyala setelah reboot"
	else
		tidak "linger belum aktif: worker mati saat tidak ada sesi login. Pemilik: sudo loginctl enable-linger $(id -un)"
	fi

	# Pekerjaan RUN sungguhan lewat jalur web: baris judge_jobs, Redis, worker, kontainer.
	keluaran="$(docker exec lokakode php spark judge:coba 2>&1)" && kode_coba=0 || kode_coba=$?
	ringkas="$(grep -E '^(\{"job"|judge:coba )' <<< "$keluaran" | tr '\n' ' ')"
	if [ "$kode_coba" = "0" ] && grep -q 'judge:coba LULUS' <<< "$keluaran"; then
		ok "RUN lewat lokakode -> $ringkas"
	else
		tidak "RUN lewat lokakode gagal (kode $kode_coba): ${ringkas:-$(tail -n 3 <<< "$keluaran" | tr '\n' ' ')}"
	fi

else
	belum "menunggu judge worker (tahap 7)"
fi

echo "7. judge memvalidasi satu soal kecil (VALIDATE: generator, konsep, 240 tes)"
if [ "$CEK_JUDGE_AKTIF" = "1" ]; then
	# Soal bawaan, lewat jalur yang sama dengan impor: generator, analisis konsep,
	# mode kelompok, solusi referensi. Tidak ada yang disimpan ke bank soal.
	keluaran="$(docker exec lokakode php spark judge:coba --validasi 2>&1)" && kode_coba=0 || kode_coba=$?
	ringkas="$(grep -E '^(\{"job"|judge:coba )' <<< "$keluaran" | tr '\n' ' ')"
	if [ "$kode_coba" = "0" ] && grep -q 'judge:coba LULUS' <<< "$keluaran"; then
		ok "VALIDATE lewat lokakode -> $ringkas"
	else
		tidak "VALIDATE lewat lokakode gagal (kode $kode_coba): ${ringkas:-$(tail -n 3 <<< "$keluaran" | tr '\n' ' ')}"
	fi

	sisa="$(docker ps -a --filter label=lokandita.judge=true --filter label=lokandita.judge.instans=lokakode --format '{{.Names}}' | wc -l)"
	if [ "$sisa" = "0" ]; then ok "tidak ada kontainer judge yang tertinggal"; else tidak "$sisa kontainer judge tertinggal"; fi
else
	belum "menunggu judge worker (tahap 7)"
fi

echo "8. halaman publik terbuka dan beranda mengarah ke LokaAuth (tahap 11)"
# Ditanyakan langsung ke kontainer aplikasi dari kontainer aplikasi lain, dengan header yang
# dikirim Caddy, supaya cek ini juga berjalan selagi host-nya masih pemeliharaan.
# tanya <kontainer penanya> <kontainer tujuan> <jalur>  ->  "<status> <tujuan redirect>"
tanya() { docker exec "$1" curl -sS -o /tmp/smoke-publik --max-time 10 -H 'X-Forwarded-Proto: https' -w '%{http_code} %{redirect_url}' "http://$2:8080$3" 2>&1 || true; }
isi() { docker exec "$1" cat /tmp/smoke-publik 2>/dev/null || true; }
login_url="$(nilai_env "$AKAR/apps/portal/.env" AUTH_LOGIN_URL)"
for pasangan in "lokakode portal" "portal lokakode"; do
	read -r penanya a <<< "$pasangan"
	url="$(nilai_env "$AKAR/apps/$a/.env" APP_URL)"
	j="$(tanya "$penanya" "$a" /)"
	if [ "$j" = "200 " ] && isi "$penanya" | grep -q '<meta name="robots" content="index, follow">'; then
		ok "$a GET / tanpa cookie -> 200, halaman publik yang boleh diindeks"
	else
		tidak "$a GET / tanpa cookie -> '$j' (seharusnya 200 dengan robots index, follow)"
	fi
	j="$(tanya "$penanya" "$a" /beranda)"
	harap="302 $login_url?next=$(jq -rn --arg u "$url/beranda" '$u|@uri')"
	if [ "$j" = "$harap" ]; then ok "$a GET /beranda tanpa cookie -> $j"; else tidak "$a GET /beranda tanpa cookie -> '$j' (seharusnya '$harap')"; fi
done
j="$(tanya lokakode portal /privasi)"
if [ "$j" = "200 " ]; then ok "portal GET /privasi -> 200"; else tidak "portal GET /privasi -> '$j' (seharusnya 200)"; fi

echo "9. foto pembuka Portal (tahap 11c, KONTRAK.md §16)"
# Foto yang ditunjuk halaman depan (bawaan atau unggahan) harus benar-benar terjawab sebagai gambar.
tanya lokakode portal / > /dev/null
foto="$(isi lokakode | grep -o '<source type="image/avif" srcset="[^" ]*' | head -n 1 | sed 's/.*srcset="//; s/&amp;/\&/g')"
if [ -n "$foto" ]; then
	j="$(docker exec lokakode curl -sS -o /dev/null --max-time 10 -w '%{http_code} %{content_type}' "http://portal:8080$foto" 2>&1 || true)"
	if [ "$j" = "200 image/avif" ]; then ok "portal: foto pembuka halaman depan ($foto) -> $j"; else tidak "portal: foto pembuka $foto -> '$j' (seharusnya 200 image/avif)"; fi
else
	tidak "portal: halaman depan tidak memuat <source type=\"image/avif\"> untuk foto pembuka"
fi
j="$(docker exec lokakode curl -sS -o /dev/null --max-time 10 -w '%{http_code}' "http://portal:8080/media/hero/smoke.php" 2>&1 || true)"
if [ "$j" = "403" ]; then ok "portal: /media/ hanya menjawab berkas foto buatan server (smoke.php -> 403)"; else tidak "portal: /media/hero/smoke.php -> '$j' (seharusnya 403)"; fi

echo "10. Safe Exam Browser (tahap 10a, KONTRAK.md §8)"
# Halaman petunjuk dan halaman periksa terjawab, dan keadaan penegakan yang BERJALAN di kontainer
# sama dengan yang tertulis di apps/lokakode/.env. Keadaan "disabled" lulus, tetapi disebut jelas.
seb_env="$(nilai_env "$AKAR/apps/lokakode/.env" SEB_ENFORCEMENT)"
seb_jalan="$(docker exec lokakode printenv SEB_ENFORCEMENT 2>/dev/null || true)"
j="$(tanya portal lokakode /seb)"
case "$seb_jalan" in enabled) harap_keadaan=berlaku ;; disabled) harap_keadaan=belum ;; *) harap_keadaan=tidak-sah ;; esac
if [ "$j" = "200 " ] && isi portal | grep -q "data-seb-keadaan=\"$harap_keadaan\""; then
	ok "lokakode GET /seb -> 200, halaman petunjuk menyebut keadaan '$harap_keadaan'"
else
	tidak "lokakode GET /seb -> '$j' atau keadaan di halaman bukan '$harap_keadaan'"
fi
j="$(tanya portal lokakode /seb/periksa)"
if [ "$j" = "200 " ] && isi portal | grep -q 'data-seb-periksa="tanpa_'; then
	ok "lokakode GET /seb/periksa tanpa header SEB -> 200, melaporkan tidak ada header atau konfigurasi"
else
	tidak "lokakode GET /seb/periksa -> '$j' (seharusnya 200 dengan hasil tanpa_header atau tanpa_konfigurasi)"
fi
if [ -n "$seb_jalan" ] && [ "$seb_jalan" = "$seb_env" ]; then
	if [ "$seb_jalan" = "disabled" ]; then
		ok "SEB_ENFORCEMENT=disabled di .env dan di kontainer — PERINGATAN: MODE UJI, mahasiswa TIDAK diwajibkan memakai SEB"
	else
		ok "SEB_ENFORCEMENT=$seb_jalan di .env dan di kontainer: mahasiswa wajib memakai SEB"
	fi
else
	tidak "SEB_ENFORCEMENT di kontainer ('$seb_jalan') berbeda dari apps/lokakode/.env ('$seb_env'): deploy ulang lokakode"
fi

echo "11. integritas dan komunikasi (tahap 10b, KONTRAK.md §17)"
# Tabelnya ada di skema yang sungguhan (migrasi sudah berjalan), rute barunya terdaftar di kode yang
# BERJALAN (tanpa cookie: dialihkan ke login, bukan 404), dan halaman ketentuan memuat kalimat barunya.
tabel="$(docker exec mysql sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysql -uroot -N -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = '"'"'lokakode'"'"' AND table_name IN ('"'"'notifications'"'"', '"'"'clarifications'"'"', '"'"'announcements'"'"')"' 2>/dev/null || true)"
if [ "$tabel" = "3" ]; then ok "skema lokakode punya notifications, clarifications, announcements"; else tidak "skema lokakode: $tabel dari 3 tabel tahap 10b ada (migrasi belum berjalan?)"; fi
url_kode="$(nilai_env "$AKAR/apps/lokakode/.env" APP_URL)"
for jalur in /notifikasi /pengumuman /kelas/1/integritas /kelas/1/klarifikasi /kelas/1/pengumuman; do
	j="$(tanya portal lokakode "$jalur")"
	harap="302 $login_url?next=$(jq -rn --arg u "$url_kode$jalur" '$u|@uri')"
	if [ "$j" = "$harap" ]; then ok "lokakode GET $jalur tanpa cookie -> 302 ke login"; else tidak "lokakode GET $jalur tanpa cookie -> '$j' (seharusnya '$harap')"; fi
done
j="$(tanya lokakode portal /ketentuan)"
if [ "$j" = "200 " ] && isi lokakode | grep -q 'jauh lebih cepat daripada ketikan orang'; then
	ok "portal GET /ketentuan -> 200, memuat kalimat tentang ketikan tak wajar"
else
	tidak "portal GET /ketentuan -> '$j' atau tidak memuat kalimat tentang ketikan tak wajar (bila teksnya diubah lewat /admin/teks, tambahkan kalimat itu di sana)"
fi
# Sejak LokaUjian tahap U6: halaman privasi menyebut LokaUjian dan penyimpanan jawaban ujian.
j="$(tanya lokakode portal /privasi)"
if [ "$j" = "200 " ] && isi lokakode | grep -q 'Data ujian di LokaUjian' && isi lokakode | grep -q 'tidak dihapus secara otomatis'; then
	ok "portal GET /privasi -> 200, menyebut LokaUjian dan penyimpanan jawaban ujian"
else
	tidak "portal GET /privasi -> '$j' atau tidak menyebut LokaUjian dan penyimpanan jawaban (bila teksnya diubah lewat /admin/teks, tambahkan di sana)"
fi

echo "12. merek (KONTRAK.md §16 \"Brand files\")"
# Setiap situs menyajikan ikon, manifest dan logo kepalanya dari kode yang BERJALAN; halaman depan
# Portal memuat keenam kartu aplikasi dalam urutannya; nama lama "LokaAuth" tidak tampil — juga
# tidak lewat teks pengganti dari /admin/teks.
for pasangan in "lokakode auth" "lokakode portal" "portal lokakode" "portal lokaujian"; do
	read -r penanya a <<< "$pasangan"
	kurang=""
	for jalur in /favicon.ico /merek/icon-16.png /merek/icon-32.png /merek/icon-180.png /merek/icon-192.png /merek/icon-512.png /merek/logo-terang.webp /merek/logo-gelap.webp; do
		j="$(tanya "$penanya" "$a" "$jalur")"
		[ "$j" = "200 " ] || kurang="$kurang $jalur($j)"
	done
	j="$(tanya "$penanya" "$a" /merek/site.webmanifest)"
	if [ "$j" != "200 " ] || ! isi "$penanya" | jq -e '.name and (.icons | length == 2)' > /dev/null 2>&1; then kurang="$kurang /merek/site.webmanifest($j)"; fi
	if [ -z "$kurang" ]; then ok "$a: favicon, ikon 16/32/180/192/512, logo kepala dan manifest terjawab"; else tidak "$a: berkas merek tidak terjawab:$kurang"; fi
done
tanya lokakode portal / > /dev/null
kartu="$(isi lokakode | grep -o 'data-aplikasi="[a-z]*"' | sed 's/.*="//; s/"//' | tr '\n' ' ')"
if [ "$kartu" = "lokakode lokaujian lokakelas lokasiber lokaretas lokadata " ]; then ok "portal GET /: enam kartu aplikasi dalam urutannya"; else tidak "portal GET /: kartu aplikasi '$kartu'"; fi
# Sejak LokaUjian tahap U6 kartu LokaUjian adalah tautan ke host-nya (KONTRAK.md §16).
if isi lokakode | grep -q "<a class=\"kartu-aplikasi\" href=\"https://$(host_aplikasi lokaujian)/\" data-aplikasi=\"lokaujian\">"; then ok "portal GET /: kartu LokaUjian bertaut ke https://$(host_aplikasi lokaujian)/"; else tidak "portal GET /: kartu LokaUjian bukan tautan ke https://$(host_aplikasi lokaujian)/"; fi
sisa=""
for jalur in / /tentang /privasi /ketentuan; do
	tanya lokakode portal "$jalur" > /dev/null
	if isi lokakode | grep -qi 'lokaauth'; then sisa="$sisa $jalur"; fi
done
tanya portal lokakode / > /dev/null
if isi portal | grep -qi 'lokaauth'; then sisa="$sisa lokakode:/"; fi
if [ -z "$sisa" ]; then ok "halaman publik tidak memuat nama lama LokaAuth"; else tidak "nama lama LokaAuth masih tampil di:$sisa (bila dari /admin/teks, betulkan di sana)"; fi

echo "19. MySQL mengizinkan sekurangnya 500 sambungan (KONTRAK.md §9)"
maks="$(docker exec mysql sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysql -uroot -N -e "SELECT @@global.max_connections"' 2> /dev/null || true)"
if [[ "$maks" =~ ^[0-9]+$ ]] && [ "$maks" -ge 500 ]; then ok "max_connections = $maks"; else tidak "max_connections = '${maks:-tidak terbaca}' (seharusnya >= 500; infra/compose.yml)"; fi

echo "13. view roster (KONTRAK.md §18): lokaujian membaca kelima view sebagai roster_reader, dan tidak lebih"
# Dari dalam kontainer lokaujian, lewat ROSTER_READER_DSN miliknya — sejak tahap A1 skema inti
# `roster`. Hanya jumlah baris yang dicetak.
j="$(docker exec -i lokaujian php <<'PHP' 2>&1 || true
<?php
mysqli_report(MYSQLI_REPORT_OFF);
$u = parse_url(getenv('ROSTER_READER_DSN'));
$m = @new mysqli($u['host'], $u['user'], $u['pass'], ltrim($u['path'], '/'), $u['port']);
if ($m->connect_errno) { echo 'koneksi gagal: ', $m->connect_errno; exit; }
$n = [];
foreach (['roster_academic_years', 'roster_courses', 'roster_classes', 'roster_class_teachers', 'roster_enrollments'] as $v) {
    $r = $m->query("SELECT COUNT(*) FROM $v");
    $n[] = $r === false ? 'ditolak-' . $m->errno : $r->fetch_row()[0];
}
$tolak = [];
foreach (['SELECT 1 FROM enrollments LIMIT 1', 'SELECT 1 FROM submissions LIMIT 1', "INSERT INTO roster_classes (course_id, academic_year_id, name) VALUES (1, 1, 'smoke')"] as $q) {
    $tolak[] = $m->query($q) === false ? $m->errno : 'DITERIMA';
}
echo $u['user'], ' ', implode(',', $n), ' ', implode(',', $tolak);
PHP
)"
read -r siapa hitung tolak <<< "$j"
# Jumlah yang sama ditanyakan lewat lokakode, dengan nama lama tabelnya (sejak tahap A1 view
# di atas tabel inti): kedua aplikasi melihat roster yang sama.
tabel="$(docker exec -i lokakode php <<'PHP' 2>&1 || true
<?php
mysqli_report(MYSQLI_REPORT_OFF);
$u = parse_url(getenv('DB_DSN'));
$m = @new mysqli($u['host'], $u['user'], $u['pass'], ltrim($u['path'], '/'), $u['port']);
if ($m->connect_errno) { echo 'koneksi gagal: ', $m->connect_errno; exit; }
$n = [];
foreach (['academic_years', 'courses', 'classes', 'class_teachers', 'enrollments'] as $t) { $n[] = $m->query("SELECT COUNT(*) FROM $t")->fetch_row()[0]; }
echo implode(',', $n);
PHP
)"
if [ "$siapa" = "roster_reader" ] && [[ "$hitung" =~ ^[0-9]+(,[0-9]+){4}$ ]]; then
	ok "roster_reader membaca kelima view (tahun, mata kuliah, kelas, pengajar, pendaftaran): $hitung baris"
else
	tidak "lokaujian tidak bisa membaca view roster: '$j' (ROSTER_READER_DSN harus menunjuk skema roster; lihat infra/mysql-pindah-roster.sh status)"
fi
if [ "$tolak" = "1142,1142,1142" ]; then ok "roster_reader ditolak membaca tabel enrollments dan submissions, dan ditolak menulis ke view (1142)"; else tidak "roster_reader seharusnya ditolak tiga kali (1142,1142,1142): '$tolak'"; fi
if [ -n "$hitung" ] && [ "$hitung" = "$tabel" ]; then ok "jumlah baris tiap view sama dengan tabelnya, ditanyakan lewat lokakode"; else tidak "jumlah baris view ($hitung) berbeda dari tabelnya ($tabel)"; fi

echo "20. roster di inti Lokandita (tahap A1, KONTRAK.md §18)"
# Ditanyakan sebagai root MySQL: letak tabel, view nama lama, kunci asing lintas skema, dan hak —
# persis yang disebut kontrak, tidak lebih. Hanya angka dan nama obyek yang dicetak.
mysql_root() { docker exec -i mysql sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysql -uroot -N' 2> /dev/null || true; }
enam="'academic_years','courses','classes','class_teachers','enrollments','enrollment_moves'"
letak="$(mysql_root <<SQL
SELECT CONCAT_WS(',',
 (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='roster' AND table_type='BASE TABLE' AND table_name IN ($enam)),
 (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='roster' AND table_type='BASE TABLE'),
 (SELECT COUNT(*) FROM information_schema.views WHERE table_schema='lokakode' AND security_type='INVOKER' AND table_name IN ($enam)),
 (SELECT COUNT(*) FROM information_schema.views WHERE table_schema='lokakode'),
 (SELECT COUNT(*) FROM information_schema.views WHERE table_schema='roster' AND security_type='DEFINER' AND definer='auth_app@%' AND table_name LIKE 'roster\\_%'),
 (SELECT COUNT(*) FROM information_schema.views WHERE table_schema='roster'));
SQL
)"
if [ "$letak" = "6,6,6,6,5,5" ]; then
	ok "enam tabel roster di skema roster; di lokakode keenam nama lama adalah view (INVOKER) dan tidak ada view lain; lima view kontrak di roster"
else
	tidak "letak roster: '$letak' (seharusnya 6,6,6,6,5,5 = tabel roster, semua tabel di roster, view nama lama, semua view di lokakode, view kontrak, semua view di roster)"
fi
kunci="$(mysql_root <<< "SELECT COUNT(*) FROM information_schema.referential_constraints WHERE constraint_schema='lokakode' AND unique_constraint_schema='roster';")"
if [ "$kunci" = "6" ]; then ok "enam kunci asing tabel LokaKode menunjuk ke roster.classes dan roster.courses"; else tidak "kunci asing dari lokakode ke roster: '$kunci' (seharusnya 6)"; fi
hak="$(mysql_root <<SQL
SELECT CONCAT_WS(',',
 (SELECT COUNT(*) FROM information_schema.table_privileges WHERE table_schema='roster' AND grantee="'lokakode_app'@'%'" AND privilege_type IN ('SELECT','INSERT','UPDATE','DELETE') AND table_name IN ($enam)),
 (SELECT COUNT(*) FROM information_schema.table_privileges WHERE table_schema='roster' AND grantee="'roster_reader'@'%'" AND privilege_type='SELECT' AND table_name LIKE 'roster\\_%'),
 (SELECT COUNT(*) FROM information_schema.table_privileges WHERE table_schema='roster'),
 (SELECT COUNT(*) FROM information_schema.table_privileges WHERE grantee="'roster_reader'@'%'" AND table_schema NOT IN ('roster','roster_test')),
 (SELECT COUNT(*) FROM information_schema.schema_privileges WHERE table_schema IN ('roster','roster\\_test') AND grantee <> "'auth_app'@'%'"),
 (SELECT COUNT(*) FROM information_schema.column_privileges WHERE table_schema='roster'));
SQL
)"
if [ "$hak" = "24,5,29,0,0,0" ]; then
	ok "hak di skema roster: lokakode_app baca-tulis pada enam tabel (sampai A2), roster_reader SELECT pada lima view, tidak ada hak lain; hanya auth_app berhak atas skemanya"
else
	tidak "hak di skema roster: '$hak' (seharusnya 24,5,29,0,0,0 = lokakode_app, roster_reader, semua hak tabel, hak roster_reader di skema lain, hak skema selain auth_app, hak kolom)"
fi
skema_dsn="$(docker exec lokaujian sh -c 'printf %s "${ROSTER_READER_DSN##*/}"' 2> /dev/null || true)"
if [ "$skema_dsn" = "roster" ]; then ok "ROSTER_READER_DSN di kontainer lokaujian menunjuk skema roster"; else tidak "ROSTER_READER_DSN di kontainer lokaujian menunjuk skema '$skema_dsn' (seharusnya roster)"; fi
jadwal_dsn="$(docker exec lokaujian-jadwal sh -c 'printf %s "${ROSTER_READER_DSN##*/}"' 2> /dev/null || true)"
if [ "$jadwal_dsn" = "roster" ]; then ok "ROSTER_READER_DSN di kontainer lokaujian-jadwal menunjuk skema roster"; else tidak "ROSTER_READER_DSN di kontainer lokaujian-jadwal menunjuk skema '$jadwal_dsn' (seharusnya roster)"; fi

echo "14. jalan masuk LokaUjian (tahap U1, KONTRAK.md §19)"
# Ditanyakan seperti cek 8: langsung ke kontainer lokaujian dari kontainer portal, dengan header
# yang dikirim Caddy, supaya cek ini juga berjalan selagi host-nya masih pemeliharaan.
url="$(nilai_env "$AKAR/apps/lokaujian/.env" APP_URL)"
login_url="$(nilai_env "$AKAR/apps/lokaujian/.env" AUTH_LOGIN_URL)"
j="$(tanya portal lokaujian /)"
if [ "$j" = "200 " ] && isi portal | grep -q '<meta name="robots" content="noindex, nofollow">' && isi portal | grep -q 'href="/beranda"'; then
	ok "lokaujian GET / tanpa cookie -> 200, halaman depan dengan tombol Masuk, tidak untuk diindeks"
else
	tidak "lokaujian GET / tanpa cookie -> '$j' (seharusnya 200 dengan robots noindex dan tautan ke /beranda)"
fi
# Tanpa cookie setiap halaman bersesi mengarah ke LokaAuth dengan next ke alamat itu sendiri:
# rutenya ada di kode yang berjalan, dan tidak ada yang terbuka tanpa masuk.
for jalur in /beranda /mata-kuliah /mata-kuliah/1 /kelas/1; do
	j="$(tanya portal lokaujian "$jalur")"
	harap="302 $login_url?next=$(jq -rn --arg u "$url$jalur" '$u|@uri')"
	if [ "$j" = "$harap" ]; then ok "lokaujian GET $jalur tanpa cookie -> $j"; else tidak "lokaujian GET $jalur tanpa cookie -> '$j' (seharusnya '$harap')"; fi
done

echo "15. gambar soal LokaUjian (tahap U2, KONTRAK.md §20)"
# Gambar soal bukan berkas statis: di bawah /media/ Apache menjawab 403 apa pun namanya, dan
# satu-satunya jalan keluarnya, /gambar/<kunci>, meminta masuk lebih dulu. Ditanyakan seperti cek 8.
for jalur in /media/soal/smoke.png /media/soal/smoke.php; do
	j="$(tanya portal lokaujian "$jalur")"
	if [ "$j" = "403 " ]; then ok "lokaujian GET $jalur -> 403 (bukan berkas statis)"; else tidak "lokaujian GET $jalur -> '$j' (seharusnya 403)"; fi
done
jalur="/gambar/00000000000000000000000000000000"
j="$(tanya portal lokaujian "$jalur")"
harap="302 $login_url?next=$(jq -rn --arg u "$url$jalur" '$u|@uri')"
if [ "$j" = "$harap" ]; then ok "lokaujian GET $jalur tanpa cookie -> $j"; else tidak "lokaujian GET $jalur tanpa cookie -> '$j' (seharusnya '$harap')"; fi
# Rute bank soal dan draf ujian ada di kode yang berjalan, dan tidak terbuka tanpa masuk.
for jalur in /mata-kuliah/1/bank /mata-kuliah/1/ujian; do
	j="$(tanya portal lokaujian "$jalur")"
	harap="302 $login_url?next=$(jq -rn --arg u "$url$jalur" '$u|@uri')"
	if [ "$j" = "$harap" ]; then ok "lokaujian GET $jalur tanpa cookie -> $j"; else tidak "lokaujian GET $jalur tanpa cookie -> '$j' (seharusnya '$harap')"; fi
done
# Volume gambar terpasang: tanpa volume, gambar hilang pada deploy berikutnya.
vol="$(docker inspect -f '{{range .Mounts}}{{if eq .Destination "/srv/media"}}{{.Name}}{{end}}{{end}}' lokaujian 2> /dev/null || true)"
if [ "$vol" = "lokandita_lokaujian_media" ]; then ok "lokaujian: /srv/media adalah volume $vol"; else tidak "lokaujian: /srv/media bukan volume lokandita_lokaujian_media ('$vol')"; fi

echo "16. penjadwal LokaUjian (tahap U3, KONTRAK.md §21)"
# Denyutnya di Redis ada, lebih muda dari 60 detik, membawa commit HEAD, dan ditulis kontainer yang berjalan.
denyut="$(denyut_jadwal)"
if denyut_jadwal_sah "$denyut" "$head"; then
	ok "denyut penjadwal, dari kontainer yang berjalan -> $denyut"
else
	tidak "penjadwal seharusnya commit $head dan berdenyut dari kontainer lokaujian-jadwal yang berjalan; denyut di Redis: ${denyut:-<tidak ada>} (infra/deploy.sh lokaujian)"
fi
umur="$(jq -r '(now - (.waktu | fromdateiso8601)) | floor' <<< "$denyut" 2> /dev/null || true)"
if [[ "$umur" =~ ^-?[0-9]+$ ]] && [ "$umur" -ge -5 ] && [ "$umur" -lt 60 ]; then ok "denyut penjadwal berumur $umur detik (batas 60)"; else tidak "denyut penjadwal tidak ada atau terlalu tua ('${umur:-tidak ada}' detik)"; fi
s="$(docker inspect -f '{{.State.Status}}' lokaujian-jadwal 2> /dev/null || true)"
if [ "$s" = "running" ]; then ok "kontainer lokaujian-jadwal: running"; else tidak "kontainer lokaujian-jadwal: '${s:-tidak ada}' (seharusnya running)"; fi

echo "18. rute LokaUjian ada di kode yang berjalan (tahap U3 dan U5, KONTRAK.md §22, §24)"
# Ditanyakan seperti cek 8. Tanpa cookie setiap rute staf dan mahasiswa mengarah ke LokaAuth
# dengan next ke alamat itu sendiri: rutenya terdaftar, dan tidak ada yang terbuka tanpa masuk.
for jalur in /mata-kuliah/1/ujian/1/sunting /sesi/1 /sesi/1/pratinjau /ujian-saya/1 /ujian-saya/1/kerjakan /api/sesi/1/keadaan; do
	j="$(tanya portal lokaujian "$jalur")"
	harap="302 $login_url?next=$(jq -rn --arg u "$url$jalur" '$u|@uri')"
	if [ "$j" = "$harap" ]; then ok "lokaujian GET $jalur tanpa cookie -> $j"; else tidak "lokaujian GET $jalur tanpa cookie -> '$j' (seharusnya '$harap')"; fi
done
# Tabel tahap U3 ada di skema yang sungguhan (migrasi sudah berjalan).
ada="$(printf "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='lokaujian' AND table_name IN ('exam_questions','exam_groups','exam_options','sessions','attempts','answers','integrity_events');\n" \
	| docker exec -i mysql sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysql -uroot -N' 2> /dev/null || true)"
if [ "$ada" = "7" ]; then ok "skema lokaujian memuat ketujuh tabel tahap U3"; else tidak "skema lokaujian: $ada dari 7 tabel tahap U3 (infra/deploy.sh lokaujian menjalankan migrasinya)"; fi
# Tahap U5 (KONTRAK.md §24): tampilan langsung, koreksi, tinjauan, log audit, data saya — rutenya
# terdaftar dan tidak ada yang terbuka tanpa masuk; migrasinya sudah berjalan.
for jalur in /sesi/1/langsung /mata-kuliah/1/ujian/1/koreksi /ujian-saya/1/tinjau /audit /data-saya; do
	j="$(tanya portal lokaujian "$jalur")"
	harap="302 $login_url?next=$(jq -rn --arg u "$url$jalur" '$u|@uri')"
	if [ "$j" = "$harap" ]; then ok "lokaujian GET $jalur tanpa cookie -> $j"; else tidak "lokaujian GET $jalur tanpa cookie -> '$j' (seharusnya '$harap')"; fi
done
ada="$(printf "SELECT (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='lokaujian' AND table_name='time_grants') + (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema='lokaujian' AND ((table_name='attempts' AND column_name IN ('score','reopen_until')) OR (table_name='sessions' AND column_name='release_level')));\n" \
	| docker exec -i mysql sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysql -uroot -N' 2> /dev/null || true)"
if [ "$ada" = "4" ]; then ok "skema lokaujian memuat time_grants dan kolom nilai, buka ulang dan rilis (tahap U5)"; else tidak "skema lokaujian: $ada dari 4 tanda migrasi tahap U5 (infra/deploy.sh lokaujian menjalankan migrasinya)"; fi

echo "17. Safe Exam Browser LokaUjian (tahap U4, KONTRAK.md §23)"
# Seperti cek 10, untuk lokaujian — dengan satu beda: konfigurasinya per ujian, jadi tidak ada
# daftar atau unduhan publik. Ditanyakan seperti cek 8.
seb_env="$(nilai_env "$AKAR/apps/lokaujian/.env" SEB_ENFORCEMENT)"
seb_jalan="$(docker exec lokaujian printenv SEB_ENFORCEMENT 2> /dev/null || true)"
harap_keadaan="berlaku"
if [ "$seb_jalan" = "disabled" ]; then harap_keadaan="belum"; fi
j="$(tanya portal lokaujian /seb)"
if [ "$j" = "200 " ] && isi portal | grep -q "data-seb-keadaan=\"$harap_keadaan\"" && ! isi portal | grep -q '\.seb"'; then
	ok "lokaujian GET /seb -> 200, menyebut keadaan penegakan ($harap_keadaan), tanpa tautan berkas ujian"
else
	tidak "lokaujian GET /seb -> '$j' (seharusnya 200 dengan data-seb-keadaan=\"$harap_keadaan\" dan tanpa tautan berkas)"
fi
j="$(tanya portal lokaujian /seb/periksa)"
if [ "$j" = "200 " ] && isi portal | grep -q 'data-seb-periksa="tanpa_header"'; then
	ok "lokaujian GET /seb/periksa tanpa header SEB -> 200, melaporkan tidak ada header"
else
	tidak "lokaujian GET /seb/periksa -> '$j' (seharusnya 200 dengan hasil tanpa_header)"
fi
j="$(tanya portal lokaujian /seb/unduh/UJI.seb)"
if [ "$j" = "404 " ]; then ok "lokaujian GET /seb/unduh/UJI.seb -> 404 (tidak ada unduhan publik)"; else tidak "lokaujian GET /seb/unduh/UJI.seb -> '$j' (seharusnya 404)"; fi
for jalur in /mata-kuliah/1/ujian/1/seb /sesi/1/peristiwa /ujian-saya/1/seb /ujian/1/masuk; do
	j="$(tanya portal lokaujian "$jalur")"
	harap="302 $login_url?next=$(jq -rn --arg u "$url$jalur" '$u|@uri')"
	if [ "$j" = "$harap" ]; then ok "lokaujian GET $jalur tanpa cookie -> $j"; else tidak "lokaujian GET $jalur tanpa cookie -> '$j' (seharusnya '$harap')"; fi
done
ada="$(printf "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='lokaujian' AND table_name IN ('seb_configs','seb_exam_keys');\n" \
	| docker exec -i mysql sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysql -uroot -N' 2> /dev/null || true)"
if [ "$ada" = "2" ]; then ok "skema lokaujian memuat seb_configs dan seb_exam_keys"; else tidak "skema lokaujian: $ada dari 2 tabel SEB (infra/deploy.sh lokaujian menjalankan migrasinya)"; fi
# Kunci enkripsi ada di kontainer (nilainya tidak pernah ditampilkan).
if docker exec lokaujian sh -c 'printenv SEB_ENCRYPTION_KEY | grep -Eq "^[0-9a-f]{64}$"' 2> /dev/null; then ok "lokaujian: SEB_ENCRYPTION_KEY ada di kontainer (64 karakter heksadesimal; nilainya tidak ditampilkan)"; else tidak "lokaujian: SEB_ENCRYPTION_KEY tidak ada atau bukan 64 karakter heksadesimal di kontainer"; fi
if [ -n "$seb_jalan" ] && [ "$seb_jalan" = "$seb_env" ]; then
	if [ "$seb_jalan" = "disabled" ]; then
		ok "lokaujian: SEB_ENFORCEMENT=disabled di .env dan di kontainer — PERINGATAN: MODE UJI, mahasiswa TIDAK diwajibkan memakai SEB"
	else
		ok "lokaujian: SEB_ENFORCEMENT=$seb_jalan di .env dan di kontainer: mahasiswa wajib memakai SEB dengan berkas ujiannya"
	fi
else
	tidak "lokaujian: SEB_ENFORCEMENT di kontainer ('$seb_jalan') berbeda dari apps/lokaujian/.env ('$seb_env'): deploy ulang lokaujian"
fi

echo
echo "smoke: $lulus lulus, $gagal_n gagal, $tunda belum aktif"
if [ "$gagal_n" -gt 0 ]; then
	echo "smoke: GAGAL"
	exit 1
fi
if [ "$tunda" -gt 0 ]; then
	echo "smoke: LULUS untuk cek yang aktif — $tunda cek BELUM AKTIF dan belum membuktikan apa pun"
else
	echo "smoke: LULUS"
fi
