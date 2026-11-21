<?php

declare(strict_types=1);

namespace App\Libraries;

/**
 * Mengolah satu foto menjadi berkas-berkas yang boleh disajikan (KONTRAK.md §16).
 *
 * Berkas asli TIDAK PERNAH disajikan. Ia dibaca menjadi piksel, lalu ditulis ulang
 * sebagai AVIF, WebP dan JPEG dalam beberapa lebar. Karena yang ditulis hanya piksel,
 * semua metadata (EXIF, GPS, komentar, profil warna) dan apa pun yang ditempelkan di
 * belakang gambar ikut hilang.
 *
 * Kelas ini sengaja tidak memakai CodeIgniter: infra/docker/foto-bawaan.php memakainya
 * saat build untuk foto bawaan, sehingga foto bawaan dan foto unggahan dibuat oleh kode
 * yang sama.
 */
final class Gambar
{
    /** Lebar (piksel) berkas yang dibuat. Yang terbesar sama dengan lebar minimum unggahan: tidak ada yang diperbesar. */
    public const LEBAR = [480, 768, 1200, 1600];

    public const FORMAT = ['avif', 'webp', 'jpg'];

    public const MAKS_BYTE = 10 * 1024 * 1024;

    /** Diperiksa dari kepala berkas, sebelum gambar dibuka: GD memakai sekitar 5 byte memori per piksel. */
    public const MAKS_PIKSEL = 30_000_000;

    public const MIN_LEBAR = 1600;

    public const MIN_TINGGI = 900;

    /** Jenis yang diterima, menurut ISI berkas. */
    private const JENIS = [
        IMAGETYPE_JPEG => ['image/jpeg', 'jpeg'],
        IMAGETYPE_PNG  => ['image/png', 'png'],
        IMAGETYPE_WEBP => ['image/webp', 'webp'],
    ];

    /**
     * Memeriksa berkas TANPA membukanya sebagai gambar: ukuran berkas, jenis menurut isinya
     * (bukan nama atau jenis kiriman peramban), jumlah piksel dan ukuran terkecil.
     *
     * @return array{jenis: string, lebar: int, tinggi: int}|string data gambar, atau kode galat:
     *                                                             kosong, terlalu_besar, bukan_gambar, terlalu_banyak_piksel, terlalu_kecil
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
        // Foto yang direbahkan lewat EXIF (potret dari ponsel) diukur sesudah ditegakkan.
        if ($kepala[2] === IMAGETYPE_JPEG && self::orientasi($berkas) >= 5) {
            [$lebar, $tinggi] = [$tinggi, $lebar];
        }
        if ($lebar < self::MIN_LEBAR || $tinggi < self::MIN_TINGGI) {
            return 'terlalu_kecil';
        }

        return ['jenis' => self::JENIS[$kepala[2]][1], 'lebar' => $lebar, 'tinggi' => $tinggi];
    }

    /**
     * Membuat "<awalan>-<lebar>.<format>" untuk setiap lebar dan format. Panggil periksa() lebih dulu.
     * Bila gagal di tengah jalan, berkas yang sudah dibuat dihapus lagi.
     *
     * @return array{lebar: int, tinggi: int}|null ukuran berkas terbesar; null bila isinya tidak bisa dibaca sebagai gambar
     */
    public static function buat(string $berkas, string $awalan): ?array
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

        $dibuat = [];
        $ukuran = null;
        $sumber = $asal;
        // Dari yang terbesar: tiap ukuran berikutnya dikecilkan dari hasil sebelumnya.
        foreach (array_reverse(self::LEBAR) as $lebar) {
            $tinggi = max(1, (int) round($lebar * imagesy($sumber) / imagesx($sumber)));
            $kanvas = imagecreatetruecolor($lebar, $tinggi);
            // Latar putih: bagian tembus pandang PNG atau WebP tidak menjadi hitam di JPEG.
            imagefill($kanvas, 0, 0, (int) imagecolorallocate($kanvas, 255, 255, 255));
            imagecopyresampled($kanvas, $sumber, 0, 0, 0, 0, $lebar, $tinggi, imagesx($sumber), imagesy($sumber));
            $ukuran ??= ['lebar' => $lebar, 'tinggi' => $tinggi];
            $sumber   = $kanvas;

            imageinterlace($kanvas, true);
            $tulis = [
                'avif' => static fn (string $f): bool => imageavif($kanvas, $f, 60, 6),
                'webp' => static fn (string $f): bool => imagewebp($kanvas, $f, 80),
                'jpg'  => static fn (string $f): bool => imagejpeg($kanvas, $f, 82),
            ];
            foreach (self::FORMAT as $format) {
                $tujuan   = "{$awalan}-{$lebar}.{$format}";
                $dibuat[] = $tujuan;
                if (! @$tulis[$format]($tujuan) || (int) @filesize($tujuan) === 0) {
                    array_map(static fn (string $f): bool => @unlink($f), $dibuat);

                    return null;
                }
                chmod($tujuan, 0644);
            }
        }

        return $ukuran;
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
