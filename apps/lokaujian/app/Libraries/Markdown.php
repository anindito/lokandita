<?php

declare(strict_types=1);

namespace App\Libraries;

use League\CommonMark\CommonMarkConverter;

/**
 * Teks soal, opsi, kelompok dan petunjuk ujian adalah Markdown (SPEC-LOKAUJIAN.md §5.1).
 *
 * HTML mentah di dalamnya TIDAK dijalankan: ia ditampilkan sebagai teks. Tautan berbahaya
 * (javascript:, data:) dibuang. Teksnya ditulis dosen atau datang dari berkas impor, dan
 * dibaca mahasiswa, jadi diperlakukan sebagai masukan yang tidak dipercaya.
 */
final class Markdown
{
    private static ?CommonMarkConverter $pengubah = null;

    /**
     * HTML yang aman untuk ditampilkan (tidak perlu di-escape lagi).
     */
    public static function html(string $teks): string
    {
        self::$pengubah ??= new CommonMarkConverter(['html_input' => 'escape', 'allow_unsafe_links' => false, 'max_nesting_level' => 20]);

        return self::$pengubah->convert($teks)->getContent();
    }

    /**
     * Kata-kata pertama sebagai teks polos, untuk daftar bank soal.
     */
    public static function ringkas(string $teks, int $panjang = 90): string
    {
        $polos = trim((string) preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags(self::html($teks)), ENT_QUOTES | ENT_HTML5)));

        return mb_strlen($polos) > $panjang ? mb_substr($polos, 0, $panjang - 1) . '…' : $polos;
    }
}
