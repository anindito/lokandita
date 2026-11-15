<?php

declare(strict_types=1);

namespace Lokandita\Auth;

/**
 * Pemeriksaan variabel lingkungan saat aplikasi mulai (KONTRAK.md §10).
 *
 * Daftar variabel wajib dibaca dari .env.example milik aplikasi, sehingga
 * .env.example adalah satu-satunya daftar: menambah kunci di sana otomatis
 * membuatnya wajib saat start.
 */
final class EnvCheck
{
    /**
     * Nama-nama kunci yang tercantum di berkas .env.example.
     *
     * @return list<string>
     */
    public static function keysFromExample(string $exampleFile): array
    {
        $lines = @file($exampleFile, FILE_IGNORE_NEW_LINES);
        if ($lines === false) {
            throw new \RuntimeException("Berkas contoh env tidak terbaca: {$exampleFile}");
        }

        $keys = [];
        foreach ($lines as $line) {
            if (preg_match('/^([A-Z][A-Z0-9_]*)=/', $line, $m) === 1) {
                $keys[] = $m[1];
            }
        }

        return $keys;
    }

    /**
     * Kunci yang tidak ada atau kosong di lingkungan proses ini.
     *
     * @param list<string> $keys
     *
     * @return list<string>
     */
    public static function missing(array $keys): array
    {
        $missing = [];
        foreach ($keys as $key) {
            $value = getenv($key);
            if ($value === false || trim($value) === '') {
                $missing[] = $key;
            }
        }

        return $missing;
    }
}
