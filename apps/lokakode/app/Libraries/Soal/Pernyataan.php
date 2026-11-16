<?php

declare(strict_types=1);

namespace App\Libraries\Soal;

use League\CommonMark\CommonMarkConverter;

/**
 * Pernyataan soal: parameter {{nama}} lalu Markdown (keputusan pemilik, tahap 8).
 *
 * HTML mentah di dalam teks soal TIDAK dijalankan: ia ditampilkan sebagai teks.
 * Tautan berbahaya (javascript:, data:) dibuang. Teks soal datang dari berkas
 * yang diunggah, jadi diperlakukan sebagai masukan yang tidak dipercaya.
 */
final class Pernyataan
{
    private static ?CommonMarkConverter $markdown = null;

    /**
     * Mengganti {{nama}} dengan nilainya. Nilainya sama untuk semua mahasiswa.
     *
     * @param array<string, mixed> $params
     */
    public static function isi(string $teks, array $params): string
    {
        return (string) preg_replace_callback(
            '/\{\{\s*([a-z_][a-z0-9_]*)\s*\}\}/',
            static fn (array $m): string => array_key_exists($m[1], $params) ? (string) $params[$m[1]] : $m[0],
            $teks,
        );
    }

    /**
     * HTML yang aman untuk ditampilkan (tidak perlu di-escape lagi).
     *
     * @param array<string, mixed> $params
     */
    public static function html(string $teks, array $params = []): string
    {
        self::$markdown ??= new CommonMarkConverter(['html_input' => 'escape', 'allow_unsafe_links' => false, 'max_nesting_level' => 20]);

        return self::$markdown->convert(self::isi($teks, $params))->getContent();
    }
}
