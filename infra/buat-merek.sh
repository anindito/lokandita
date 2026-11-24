#!/usr/bin/env bash
# Membuat berkas merek turunan (favicon, ikon, manifest, logo kepala, gambar Open Graph, ikon
# kartu Portal) dari berkas asli di docs/branding/baru/ dan menaruhnya di apps/<app>/public/.
# Berkas asli tidak pernah diubah. Hasilnya di-commit, jadi terlihat di diff.
#
#   infra/buat-merek.sh             membuat ulang dan menimpa berkas turunan
#   infra/buat-merek.sh --periksa   hanya membandingkan; gagal bila berkas di repositori
#                                   bukan hasil dari berkas asli yang sekarang
#
# PHP tidak ada di host: skripnya berjalan di image aplikasi (GD dengan PNG dan WebP), tanpa
# jaringan, dengan berkas asli terpasang hanya-baca. Image itu harus sudah ada (infra/deploy.sh
# membuatnya). Hasil WebP bisa berbeda beberapa byte antarversi GD; --periksa memakai image yang
# sama (MEREK_IMAGE, bawaan lokandita/portal:aktif).
set -euo pipefail

AKAR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
ASAL="$AKAR/docs/branding/baru/Lokandita-Brand-Family-Variation-1/brands"
IMAGE="${MEREK_IMAGE:-lokandita/portal:aktif}"
# Yang dikelola skrip ini di setiap public/: favicon.ico dan nama-nama ini di merek/.
# (merek/hero/ dan merek/og-image.jpg Portal dibuat saat build image, bukan di sini.)
POLA=(favicon.ico 'merek/icon-*.png' merek/site.webmanifest 'merek/logo-*.png' 'merek/logo-*.webp' merek/logo.json
      merek/og-image.png 'merek/pembuka-*.png' 'merek/pembuka-*.webp' merek/aplikasi)

[ -d "$ASAL" ] || { echo "buat-merek: berkas asli tidak ada di $ASAL" >&2; exit 1; }
docker image inspect "$IMAGE" >/dev/null 2>&1 || { echo "buat-merek: image $IMAGE belum ada" >&2; exit 1; }

SEMENTARA="$(mktemp -d)"
trap 'rm -rf "${SEMENTARA:?}"' EXIT
docker run --rm --network none --read-only --cap-drop ALL --user "$(id -u):$(id -g)" \
    -v "$ASAL:/asal:ro" -v "$AKAR/infra/merek:/alat:ro" -v "$SEMENTARA:/keluar" \
    --entrypoint php "$IMAGE" /alat/buat-merek.php /asal /keluar

daftar() { # daftar <direktori public>: berkas kelolaan yang ada di sana, relatif, terurut
    ( cd "$1" && for p in "${POLA[@]}"; do
        # Pola sengaja tidak dikutip: tanda * di dalamnya harus diperluas di direktori ini.
        # shellcheck disable=SC2086
        for b in $p; do [ -e "$b" ] && find "$b" -type f; done
      done | sort -u ) || true
}

if [ "${1:-}" = "--periksa" ]; then
    beda=0
    for app in auth portal lokakode lokaujian; do
        if ! diff <(daftar "$SEMENTARA/$app") <(daftar "$AKAR/apps/$app/public"); then
            echo "BEDA  $app: daftar berkas tidak sama (< hasil dari berkas asli, > repositori)"; beda=1
            continue
        fi
        while read -r b; do
            cmp -s "$SEMENTARA/$app/$b" "$AKAR/apps/$app/public/$b" || { echo "BEDA  apps/$app/public/$b"; beda=1; }
        done < <(daftar "$SEMENTARA/$app")
    done
    if [ "$beda" = 0 ]; then
        echo "buat-merek: LULUS — berkas turunan sama dengan hasil dari berkas asli"
        exit 0
    fi
    echo "buat-merek: GAGAL" >&2
    exit 1
fi

for app in auth portal lokakode lokaujian; do
    tujuan="$AKAR/apps/$app/public"
    # Berkas kelolaan lama dibuang dulu, supaya yang tidak dibuat lagi tidak tertinggal.
    while read -r b; do rm -f "${tujuan:?}/${b:?}"; done < <(daftar "$tujuan")
    cp -r "$SEMENTARA/$app/." "$tujuan/"
    echo "$app: $(daftar "$tujuan" | wc -l) berkas"
done
