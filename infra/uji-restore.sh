#!/usr/bin/env bash
# Uji restore (SPEC.md §16): memulihkan backup ke kontainer MySQL sementara
# yang terpisah, lalu membandingkan daftar tabel dengan database yang hidup; dan
# membongkar arsip foto Portal (KONTRAK.md §16) ke direktori sementara, lalu memeriksa
# bahwa setiap foto yang disebut basis data hasil restore ada di dalamnya, utuh; hal yang sama
# untuk gambar soal LokaUjian (KONTRAK.md §20).
# Tidak pernah menulis ke database atau volume yang hidup. Lihat docs/RESTORE.md.
#
#   infra/uji-restore.sh            backup terbaru
#   infra/uji-restore.sh <direktori backup>
source "$(dirname "${BASH_SOURCE[0]}")/lib.sh"

dir="$(nilai_env "$AKAR/infra/.env" BACKUP_DIR)"
sumber="${1:-$(find "$dir" -maxdepth 1 -type d -name 'lokandita-*' | sort | tail -n 1)}"
[ -n "$sumber" ] && [ -f "$sumber/db.sql.gz" ] || gagal "tidak ada backup untuk diuji di $dir"
echo "menguji: $sumber"

(cd "$sumber" && sha256sum -c SHA256SUMS) || gagal "checksum backup tidak cocok"
tar -tzf "$sumber/env.tar.gz" | sed 's/^/  env.tar.gz berisi: /'

[ -f "$sumber/media.tar.gz" ] || gagal "backup ini tidak memuat media.tar.gz (dibuat sebelum foto Portal ikut dibackup); buat backup baru dengan infra/backup.sh"

[ -f "$sumber/media-lokaujian.tar.gz" ] || gagal "backup ini tidak memuat media-lokaujian.tar.gz (dibuat sebelum gambar soal LokaUjian ikut dibackup); buat backup baru dengan infra/backup.sh"

NAMA=lokandita-uji-restore
IMAGE="$(docker inspect -f '{{.Config.Image}}' mysql)"
bongkar=""
bersih() {
	docker rm -f "$NAMA" > /dev/null 2>&1 || true
	if [ -n "$bongkar" ]; then rm -rf "$bongkar"; fi
}
trap bersih EXIT
bersih
# Dibuat SESUDAH pembersihan awal di atas, yang juga menghapus direktori ini.
bongkar="$(mktemp -d)"

# Server sementara saat inisialisasi image tidak mendengarkan TCP, jadi
# menunggu lewat TCP berarti menunggu server yang sebenarnya.
# Tanpa jaringan dan tanpa port: kontainer ini hanya bisa dicapai lewat docker exec.
docker run -d --name "$NAMA" --network none -e MYSQL_ROOT_PASSWORD="$(openssl rand -hex 16)" "$IMAGE" > /dev/null
for _ in $(seq 1 60); do
	docker exec "$NAMA" sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" mysql -uroot -h 127.0.0.1 --protocol=tcp -e "SELECT 1"' > /dev/null 2>&1 && break
	sleep 2
done

zcat "$sumber/db.sql.gz" | docker exec -i "$NAMA" sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysql -uroot' \
	|| gagal "dump tidak dapat dimuat"

daftar() { # <kontainer>
	docker exec -i "$1" sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysql -uroot -N' <<'SQL'
SELECT CONCAT(s.schema_name, IFNULL(CONCAT('.', t.table_name), ' (tanpa tabel)'))
  FROM information_schema.schemata s
  LEFT JOIN information_schema.tables t ON t.table_schema = s.schema_name
 WHERE s.schema_name IN ('identity','roster','lokakode','portal','lokaujian')
 ORDER BY 1;
SQL
}

hidup="$(daftar mysql)"
pulih="$(daftar "$NAMA")"
echo "hasil restore:"
sed 's/^/  /' <<< "$pulih"

if [ "$hidup" != "$pulih" ]; then
	diff <(echo "$hidup") <(echo "$pulih") | sed 's/^/  beda: /' || true
	gagal "daftar skema/tabel hasil restore berbeda dari database yang hidup (backup dari sebelum skema roster ada, atau dari sebelum tabel roster pindah, tidak cocok lagi: buat backup baru dengan infra/backup.sh)"
fi

# --- Roster di inti (tahap A1, KONTRAK.md §18) --------------------------------
# Enam tabel roster ada di skema `roster`; LokaKode memakainya lewat view bernama sama dan lewat
# kunci asing lintas skema. Daftar tabel yang sama belum berarti keduanya pulih utuh: di hasil
# restore view-nya harus terbaca dengan jumlah baris tabelnya, dan kunci asingnya harus ada.
# Pendaftaran dan riwayat pindah kelas tidak pernah dihapus, jadi hasil restore tidak boleh
# memuat lebih banyak daripada yang hidup. Hanya angka yang dicetak.
roster_tabel=(academic_years courses classes class_teachers enrollments enrollment_moves)
hitung_roster() { # <kontainer> <skema>
	local sql="" t
	for t in "${roster_tabel[@]}"; do sql+="SELECT '$t', COUNT(*) FROM \`$2\`.\`$t\`;"; done
	docker exec -i "$1" sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysql -uroot -N' <<< "$sql"
}
satu() { docker exec -i "$1" sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysql -uroot -N' <<< "$2"; }
di_inti="$(satu "$NAMA" "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='roster' AND table_type='BASE TABLE';")"
if [ "$di_inti" = "6" ]; then
	roster_pulih="$(hitung_roster "$NAMA" roster)"
	roster_hidup="$(hitung_roster mysql roster)"
	lewat_view="$(hitung_roster "$NAMA" lokakode)" || gagal "view nama lama di lokakode tidak terbaca di hasil restore"
	echo "baris tabel roster (hasil restore / hidup):"
	paste <(echo "$roster_pulih") <(echo "$roster_hidup") | awk '{ printf "  %-18s %s / %s\n", $1, $2, $4 }'
	[ "$roster_pulih" = "$lewat_view" ] || gagal "hasil restore: jumlah baris lewat view nama lama di lokakode berbeda dari tabel di roster"
	for t in enrollments enrollment_moves; do
		p="$(awk -v t="$t" '$1 == t { print $2 }' <<< "$roster_pulih")"
		h="$(awk -v t="$t" '$1 == t { print $2 }' <<< "$roster_hidup")"
		[ "$p" -le "$h" ] || gagal "roster.$t: hasil restore memuat $p baris, yang hidup hanya $h — baris tabel ini tidak pernah dihapus"
	done
	kunci_pulih="$(satu "$NAMA" "SELECT COUNT(*) FROM information_schema.referential_constraints WHERE constraint_schema='lokakode' AND unique_constraint_schema='roster';")"
	kunci_hidup="$(satu mysql "SELECT COUNT(*) FROM information_schema.referential_constraints WHERE constraint_schema='lokakode' AND unique_constraint_schema='roster';")"
	[ "$kunci_pulih" = "$kunci_hidup" ] && [ "$kunci_pulih" = "6" ] || gagal "kunci asing dari lokakode ke roster: $kunci_pulih di hasil restore, $kunci_hidup hidup (seharusnya 6 dan 6)"
	echo "  view nama lama di lokakode terbaca dengan jumlah yang sama; $kunci_pulih kunci asing lintas skema ikut pulih"
else
	echo "roster: backup ini dibuat SEBELUM tabel roster pindah ke inti (tahap A1) — skema roster-nya kosong, keenam tabel masih di lokakode; tidak ada yang dibandingkan untuk roster"
fi

# --- Isi tabel LokaUjian (tahap U6) -----------------------------------------
# Daftar tabel yang sama belum berarti isinya kembali. Untuk tabel LokaUjian yang barisnya tidak
# pernah dihapus (SPEC-LOKAUJIAN.md §13) jumlah baris hasil restore tidak boleh melebihi yang
# hidup; dan bila jumlah jawaban sama serta tidak ada pengerjaan yang sedang berjalan, sidik
# seluruh tabel `answers` dan `attempts` harus sama: setiap jawaban kembali persis.
# Hanya angka yang dicetak.
hitung() { # <kontainer>
	docker exec -i "$1" sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysql -uroot -N' <<'SQL'
SELECT 'exams', COUNT(*) FROM lokaujian.exams UNION ALL
SELECT 'exam_questions', COUNT(*) FROM lokaujian.exam_questions UNION ALL
SELECT 'sessions', COUNT(*) FROM lokaujian.sessions UNION ALL
SELECT 'attempts', COUNT(*) FROM lokaujian.attempts UNION ALL
SELECT 'answers', COUNT(*) FROM lokaujian.answers UNION ALL
SELECT 'integrity_events', COUNT(*) FROM lokaujian.integrity_events UNION ALL
SELECT 'time_grants', COUNT(*) FROM lokaujian.time_grants UNION ALL
SELECT 'images', COUNT(*) FROM lokaujian.images UNION ALL
SELECT 'seb_configs', COUNT(*) FROM lokaujian.seb_configs UNION ALL
SELECT 'audit_log', COUNT(*) FROM lokaujian.audit_log;
SQL
}
sidik() { # <kontainer>
	docker exec -i "$1" sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysql -uroot -N' <<< 'CHECKSUM TABLE lokaujian.answers, lokaujian.attempts;' | awk '{ print $2 }' | paste -sd ' '
}
hitung_hidup="$(hitung mysql)"
hitung_pulih="$(hitung "$NAMA")"
echo "baris tabel LokaUjian (hasil restore / hidup):"
paste <(echo "$hitung_pulih") <(echo "$hitung_hidup") | awk '{ printf "  %-18s %s / %s\n", $1, $2, $4 }'
for t in attempts answers integrity_events images audit_log; do
	p="$(awk -v t="$t" '$1 == t { print $2 }' <<< "$hitung_pulih")"
	h="$(awk -v t="$t" '$1 == t { print $2 }' <<< "$hitung_hidup")"
	[ "$p" -le "$h" ] || gagal "lokaujian.$t: hasil restore memuat $p baris, yang hidup hanya $h — baris tabel ini tidak pernah dihapus"
done
berjalan="$(docker exec -i mysql sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysql -uroot -N' <<< 'SELECT COUNT(*) FROM lokaujian.attempts WHERE submitted_at IS NULL;')"
if [ "$berjalan" = "0" ] && [ "$(grep -E '^(attempts|answers)\s' <<< "$hitung_pulih")" = "$(grep -E '^(attempts|answers)\s' <<< "$hitung_hidup")" ]; then
	[ "$(sidik mysql)" = "$(sidik "$NAMA")" ] || gagal "isi lokaujian.answers atau lokaujian.attempts hasil restore berbeda dari yang hidup, padahal jumlah barisnya sama dan tidak ada pengerjaan berjalan"
	echo "  sidik answers dan attempts hasil restore sama dengan yang hidup: setiap jawaban kembali persis"
else
	echo "  sidik answers/attempts TIDAK dibandingkan: ada pengerjaan berjalan ($berjalan) atau ada jawaban sesudah backup dibuat"
fi

# --- Foto Portal -----------------------------------------------------------
# 1. Arsip dibongkar ke direktori sementara di host (bukan ke volume yang hidup).
tar -C "$bongkar" -xzf "$sumber/media.tar.gz" || gagal "media.tar.gz tidak dapat dibongkar"
jumlah="$(find "$bongkar" -type f | wc -l)"
echo "foto hasil restore: $jumlah berkas"

# 2. Hanya berkas buatan server yang boleh ada di dalamnya.
asing="$(find "$bongkar" -type f ! -regex '.*/hero/[0-9a-f]+-[0-9]+\.\(avif\|webp\|jpg\)' | sed "s#^$bongkar/##")"
[ -z "$asing" ] || gagal "arsip foto memuat berkas yang bukan buatan server: $asing"

# 3. Setiap foto di basis data HASIL RESTORE harus lengkap di arsip: 4 lebar × 3 format, tidak kosong.
kunci="$(docker exec -i "$NAMA" sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysql -uroot -N' <<< 'SELECT kunci FROM portal.foto ORDER BY id;')" \
	|| gagal "tabel portal.foto tidak terbaca di hasil restore"
foto=0
for k in $kunci; do
	for l in 480 768 1200 1600; do
		for f in avif webp jpg; do
			[ -s "$bongkar/hero/$k-$l.$f" ] || gagal "foto $k: berkas $k-$l.$f tidak ada di arsip atau kosong"
		done
	done
	foto=$((foto + 1))
done
echo "  $foto foto di portal.foto, semuanya lengkap di arsip (12 berkas per foto)"

# 4. Foto yang sedang tayang menurut hasil restore harus ikut pulih.
tayang="$(docker exec -i "$NAMA" sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysql -uroot -N' <<< "SELECT f.kunci FROM portal.foto_tayang t JOIN portal.foto f ON f.id = t.foto_id WHERE t.slot = 'hero';")"
if [ -n "$tayang" ]; then
	[ -s "$bongkar/hero/$tayang-1600.jpg" ] || gagal "foto yang sedang tayang ($tayang) tidak ada di arsip"
	echo "  foto yang sedang tayang ikut pulih"
else
	echo "  tidak ada foto unggahan yang tayang: halaman depan memakai foto bawaan dari image"
fi

# 5. Isi arsip sama, berkas demi berkas, dengan volume yang hidup — kecuali foto yang
#    diunggah sesudah backup dibuat, yang hanya boleh ADA di volume, tidak boleh berbeda.
hidup_media="$(docker exec portal sh -c 'cd /srv/media && find . -type f -exec sha256sum {} + | sort -k 2')"
pulih_media="$(cd "$bongkar" && find . -type f -exec sha256sum {} + | sort -k 2)"
beda="$(comm -13 <(echo "$hidup_media") <(echo "$pulih_media"))"
[ -z "$beda" ] || gagal "berkas di arsip tidak ada di volume yang hidup atau isinya berbeda: $beda"
baru="$(comm -23 <(echo "$hidup_media") <(echo "$pulih_media") | grep -c . || true)"
echo "  isi arsip sama dengan volume yang hidup ($baru berkas di volume lebih baru daripada backup)"

# --- Gambar soal LokaUjian ---------------------------------------------------
# 1. Arsip dibongkar ke direktori sementara tersendiri di host (bukan ke volume yang hidup).
bongkar_ujian="$bongkar/lokaujian"
mkdir "$bongkar_ujian"
tar -C "$bongkar_ujian" -xzf "$sumber/media-lokaujian.tar.gz" || gagal "media-lokaujian.tar.gz tidak dapat dibongkar"
jumlah_gambar="$(find "$bongkar_ujian" -type f | wc -l)"
echo "gambar soal hasil restore: $jumlah_gambar berkas"

# 2. Hanya berkas buatan server yang boleh ada di dalamnya: soal/<32 heks>.png|jpg.
asing="$(find "$bongkar_ujian" -type f -regextype posix-extended ! -regex '.*/soal/[0-9a-f]{32}\.(png|jpg)' | sed "s#^$bongkar_ujian/##")"
[ -z "$asing" ] || gagal "arsip gambar soal memuat berkas yang bukan buatan server: $asing"

# 3. Setiap baris lokaujian.images di basis data HASIL RESTORE harus punya berkasnya di arsip,
#    dengan ukuran dan SHA-256 yang tercatat di baris itu. Hanya jumlah yang dicetak.
baris="$(docker exec -i "$NAMA" sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysql -uroot -N' <<< "SELECT kunci, IF(jenis = 'png', 'png', 'jpg'), byte, sha256 FROM lokaujian.images ORDER BY id;")" \
	|| gagal "tabel lokaujian.images tidak terbaca di hasil restore"
gambar=0
while read -r kunci akhiran byte sha; do
	[ -n "$kunci" ] || continue
	f="$bongkar_ujian/soal/$kunci.$akhiran"
	[ -s "$f" ] || gagal "gambar soal $kunci: berkas $kunci.$akhiran tidak ada di arsip atau kosong"
	[ "$(stat -c %s "$f")" = "$byte" ] || gagal "gambar soal $kunci: ukuran berkas di arsip berbeda dari yang tercatat"
	[ "$(sha256sum "$f" | cut -d ' ' -f 1)" = "$sha" ] || gagal "gambar soal $kunci: isi berkas di arsip berbeda dari yang tercatat"
	gambar=$((gambar + 1))
done <<< "$baris"
echo "  $gambar gambar di lokaujian.images, semuanya ada di arsip dengan isi yang tercatat"

# 4. Isi arsip sama, berkas demi berkas, dengan volume yang hidup — kecuali gambar yang
#    diunggah sesudah backup dibuat, yang hanya boleh ADA di volume, tidak boleh berbeda.
hidup_ujian="$(docker exec lokaujian sh -c 'cd /srv/media && find . -type f -exec sha256sum {} + | sort -k 2')"
pulih_ujian="$(cd "$bongkar_ujian" && find . -type f -exec sha256sum {} + | sort -k 2)"
beda="$(comm -13 <(echo "$hidup_ujian") <(echo "$pulih_ujian"))"
[ -z "$beda" ] || gagal "berkas di arsip gambar soal tidak ada di volume yang hidup atau isinya berbeda: $beda"
baru="$(comm -23 <(echo "$hidup_ujian") <(echo "$pulih_ujian") | grep -c . || true)"
echo "  isi arsip sama dengan volume yang hidup ($baru berkas di volume lebih baru daripada backup)"

echo "uji-restore: LULUS — $(wc -l <<< "$pulih") skema/tabel pulih dan sama dengan yang hidup; $jumlah berkas foto dan $jumlah_gambar berkas gambar soal pulih"
