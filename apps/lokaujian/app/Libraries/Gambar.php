<?php

declare(strict_types=1);

namespace App\Libraries;

/**
 * Mengolah satu gambar soal menjadi berkas yang boleh disimpan (SPEC-LOKAUJIAN.md §5.3).
 * Pemeriksaannya sama dengan foto Portal (KONTRAK.md §16), dengan batas 5 MB dan tanpa ukuran
 * terkecil.
 *
 * Berkas kiriman TIDAK PERNAH disimpan. Ia dibaca menjadi piksel, lalu ditulis ulang sebagai
 * SATU berkas baru: PNG bila kirimannya PNG, selain itu JPEG, sisi terpanjang paling banyak
 * 2000 piksel. Karena yang ditulis hanya piksel, semua metadata (EXIF, GPS, komentar, profil
 * warna) dan apa pun yang ditempelkan di belakang gambar ikut hilang. Nama berkasnya acak.
 */
final class Gambar
{
    public const MAKS_BYTE = 5 * 1024 * 1024;

    /** Diperiksa dari kepala berkas, sebelum gambar dibuka: GD memakai sekitar 5 byte memori per piksel. */
    public const MAKS_PIKSEL = 30_000_000;

    public const SISI_MAKS = 2000;

    /** Jenis yang diterima, menurut ISI berkas. */
    private const JENIS = [
        IMAGETYPE_JPEG => ['image/jpeg', 'jpeg'],
        IMAGETYPE_PNG  => ['image/png', 'png'],
        IMAGETYPE_WEBP => ['image/webp', 'webp'],
    ];

    /**
     * Memeriksa berkas TANPA membukanya sebagai gambar: ukuran berkas, jenis menurut isinya
     * (bukan nama atau jenis kiriman peramban) dan jumlah piksel.
     *
     * @return array{jenis: string, lebar: int, tinggi: int}|string data gambar, atau kode galat:
     *                                                             kosong, terlalu_besar, bukan_gambar, terlalu_banyak_piksel
     */
    public static function periksa(string $berkas): array|string
    {
        $byte = @filesize($berkas);
        if ($byte === false || $byte === 0) {
            return 'kosong';
        }
        if ($byte > self::MAKS_BYTE) {
            return 'terlalu_besar';
        }
        $mime   = (new \finfo(FILEINFO_MIME_TYPE))->file($berkas);
        $kepala = @getimagesize($berkas);
        if ($kepala === false || ! isset(self::JENIS[$kepala[2]]) || self::JENIS[$kepala[2]][0] !== $mime) {
            return 'bukan_gambar';
        }
        [$lebar, $tinggi] = $kepala;
        if ($lebar < 1 || $tinggi < 1) {
            return 'bukan_gambar';
        }
        if ($lebar * $tinggi > self::MAKS_PIKSEL) {
            return 'terlalu_banyak_piksel';
        }

        return ['jenis' => self::JENIS[$kepala[2]][1], 'lebar' => $lebar, 'tinggi' => $tinggi];
    }

    /**
     * Menulis "<direktori>/<32 heksadesimal acak>.png|jpg" dari piksel $berkas. Panggil periksa()
     * lebih dulu.
     *
     * @return array{kunci: string, jenis: string, lebar: int, tinggi: int, byte: int, sha256: string, jalur: string}|null
     *                                                                                                              null bila isinya tidak bisa dibaca sebagai gambar atau berkasnya tidak bisa ditulis
     */
    public static function tulis(string $berkas, string $direktori): ?array
    {
        // JPEG yang terpotong atau rusak ditolak, bukan dibuka sebisanya dengan sisa bidang abu-abu.
        ini_set('gd.jpeg_ignore_warning', '0');
        $jenis = @getimagesize($berkas)[2] ?? 0;
        $asal  = match ($jenis) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($berkas),
            IMAGETYPE_PNG  => @imagecreatefrompng($berkas),
            IMAGETYPE_WEBP => @imagecreatefromwebp($berkas),
            default        => false,
        };
        if ($asal === false) {
            return null;
        }
        if ($jenis === IMAGETYPE_JPEG) {
            $asal = self::tegakkan($asal, self::orientasi($berkas));
        }
        $png           = $jenis === IMAGETYPE_PNG;
        [$la, $ta]     = [imagesx($asal), imagesy($asal)];
        $skala         = min(1, self::SISI_MAKS / max($la, $ta));
        [$lebar, $tinggi] = [max(1, (int) round($la * $skala)), max(1, (int) round($ta * $skala))];

        $kanvas = imagecreatetruecolor($lebar, $tinggi);
        if ($png) {
            // PNG tetap PNG dan tetap tembus pandang di tempat yang tembus pandang.
            imagealphablending($kanvas, false);
            imagesavealpha($kanvas, true);
            imagefill($kanvas, 0, 0, (int) imagecolorallocatealpha($kanvas, 0, 0, 0, 127));
        } else {
            // Latar putih: bagian tembus pandang WebP tidak menjadi hitam di JPEG.
            imagefill($kanvas, 0, 0, (int) imagecolorallocate($kanvas, 255, 255, 255));
        }
        if ($skala < 1) {
            imagecopyresampled($kanvas, $asal, 0, 0, 0, 0, $lebar, $tinggi, $la, $ta);
        } else {
            imagecopy($kanvas, $asal, 0, 0, 0, 0, $la, $ta);
        }

        if (! is_dir($direktori) && ! @mkdir($direktori, 0755, true) && ! is_dir($direktori)) {
            return null;
        }
        $kunci  = bin2hex(random_bytes(16));
        $tujuan = $direktori . '/' . $kunci . ($png ? '.png' : '.jpg');
        $jadi   = $png ? @imagepng($kanvas, $tujuan, 9) : @imagejpeg($kanvas, $tujuan, 85);
        $byte   = (int) @filesize($tujuan);
        if (! $jadi || $byte === 0) {
            @unlink($tujuan);

            return null;
        }
        chmod($tujuan, 0644);

        return [
            'kunci' => $kunci, 'jenis' => $png ? 'png' : 'jpeg', 'lebar' => $lebar, 'tinggi' => $tinggi,
            'byte' => $byte, 'sha256' => (string) hash_file('sha256', $tujuan), 'jalur' => $tujuan,
        ];
    }

    /** Orientasi EXIF sebuah JPEG (1–8); 1 bila tidak ada. */
    private static function orientasi(string $berkas): int
    {
        $exif = function_exists('exif_read_data') ? @exif_read_data($berkas) : false;
        $o    = is_array($exif) ? (int) ($exif['Orientation'] ?? 1) : 1;

        return $o >= 1 && $o <= 8 ? $o : 1;
    }

    /** Menerapkan orientasi EXIF pada pikselnya, karena hasilnya tidak membawa EXIF lagi. */
    private static function tegakkan(\GdImage $g, int $orientasi): \GdImage
    {
        // imagerotate memutar berlawanan arah jarum jam.
        $putar = [3 => 180, 4 => 180, 5 => 270, 6 => 270, 7 => 90, 8 => 90][$orientasi] ?? 0;
        if ($putar !== 0) {
            $g = imagerotate($g, $putar, 0) ?: $g;
        }
        if (in_array($orientasi, [2, 4, 5, 7], true)) {
            imageflip($g, IMG_FLIP_HORIZONTAL);
        }

        return $g;
    }
}
