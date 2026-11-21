<?php

declare(strict_types=1);

namespace App\Libraries;

/**
 * Aplikasi Lokandita yang ditampilkan Portal (SPEC.md §1, §15). Kodenya sama dengan
 * nilai `app_access.app` (KONTRAK.md §3). Aplikasi tanpa alamat belum dibangun:
 * ditampilkan sebagai "segera" dan tidak bisa diklik.
 */
final class Aplikasi
{
    /**
     * @return array<string, array{nama: string, url: ?string}> dikunci dengan kode aplikasi
     */
    public static function daftar(): array
    {
        return [
            'lokakode'  => ['nama' => 'LokaKode', 'url' => rtrim((string) getenv('LOKAKODE_URL'), '/')],
            'lokaujian' => ['nama' => 'LokaUjian', 'url' => null],
            'lokasiber' => ['nama' => 'LokaSiber', 'url' => null],
        ];
    }
}
