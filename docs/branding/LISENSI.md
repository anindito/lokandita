# Lisensi aset pihak ketiga

Sumber dan lisensi setiap foto dan ikon yang tampil di Lokandita. Tambahkan baris di sini **sebelum** aset baru dipakai.

Foto yang diunggah superadmin lewat `/admin/teks/foto` tidak dicatat di berkas ini: sumber, lisensi dan pernyataan hak pakainya diisi saat mengunggah dan tersimpan di tabel `portal.foto` serta log audit Portal (`KONTRAK.md` §16).

## Foto

| Berkas | Judul | Fotografer | Sumber | Lisensi | Diunduh |
|---|---|---|---|---|---|
| `docs/branding/foto/hero-portal.jpg` | "Modern Office With Desktop Computers and Keyboards" | Josh Sorenson | Pexels — https://www.pexels.com/photo/modern-office-with-desktop-computers-and-keyboards-990423/ | Pexels License — https://www.pexels.com/license/ | 2026-11-21 |

Catatan untuk `hero-portal.jpg`:

- Dipakai sebagai foto pembuka **bawaan** halaman depan Portal, dan — dipotong menjadi 1200 × 630 tanpa tulisan — sebagai gambar Open Graph Portal (`/merek/og-image.jpg`).
- Berkas di repositori adalah berkas asli dari Pexels (5184 × 3456), semula bernama `pexels-joshsorenson-990423.jpg`. Yang disajikan adalah turunannya, dibuat saat build image Portal (`infra/docker/foto-bawaan.php`): AVIF, WebP dan JPEG selebar 480, 768, 1200 dan 1600 piksel, tanpa metadata.
- Tanggal unduh di atas adalah tanggal berkas itu tiba di server ini; pemilik yang mengunduhnya dari Pexels. Bila tanggal unduh sebenarnya berbeda, betulkan di sini.
- Fotonya tidak dipotong. Diperiksa pada ukuran penuh, 2026-11-21: tidak ada logo pabrikan yang terbaca — tanda kecil di bingkai bawah monitor dan bagian belakang monitor di kejauhan berada di luar fokus.
- Kredit "Foto: Josh Sorenson / Pexels" tampil di kaki halaman depan Portal selama foto ini yang tayang. Lisensi Pexels tidak mewajibkan kredit; kredit itu permintaan pemilik.

## Ikon

| Kumpulan | Versi | Lisensi | Sumber | Letak di repositori |
|---|---|---|---|---|
| Lucide (`lucide-static`) | 1.77.0 | ISC (sebagian ikon turunan Feather, MIT) — teks lengkap di `packages/lokandita-auth/ikon/LICENSE-lucide.txt` | https://lucide.dev — paket `https://registry.npmjs.org/lucide-static/-/lucide-static-1.77.0.tgz`, sha256 `04322da4737e0f505646ac2dbbc273c8512b95ec737a462248621a2a32c65da1` | `packages/lokandita-auth/ikon/*.svg` |

- 24 berkas SVG disalin apa adanya dari `package/icons/` paket itu pada 2026-11-21: `arrow-right`, `book-open`, `camera`, `circle-check`, `circle-check-big`, `clipboard-check`, `code-xml`, `dices`, `external-link`, `file-text`, `key-round`, `layout-grid`, `lock`, `log-in`, `log-out`, `mail`, `play`, `refresh-cw`, `repeat`, `send`, `shield-check`, `square-terminal`, `target`, `trophy`.
- Disajikan dari host sendiri, ditulis langsung ke dalam HTML oleh `Lokandita\Auth\Ikon`. Tidak ada font ikon dan tidak ada CDN.
- Menambah ikon: ambil berkasnya dari paket versi yang sama (atau naikkan versinya dan ganti semua berkas sekaligus), lalu perbarui daftar ini.

## Merek

Logo dan ikon LokaAuth dan LokaKode di `docs/branding/branding-siap-pakai.zip` adalah milik pemilik Lokandita. Tidak ada logo, favicon atau gambar Open Graph khusus untuk Portal (Lokandita) di dalamnya.
