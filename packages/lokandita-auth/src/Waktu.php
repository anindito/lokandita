<?php

declare(strict_types=1);

namespace Lokandita\Auth;

/**
 * Waktu disimpan dalam UTC (DATETIME MySQL) dan ditampilkan dalam Asia/Jakarta.
 * Hanya dua fungsi yang dipakai kode bersama; hasilnya sama dengan App\Libraries\Waktu LokaKode.
 */
final class Waktu
{
    public static function kini(): string
    {
        return gmdate('Y-m-d H:i:s');
    }

    public static function lokal(?string $utc): string
    {
        if ($utc === null || $utc === '') {
            return '—';
        }
        $t = new \DateTimeImmutable($utc, new \DateTimeZone('UTC'));

        return $t->setTimezone(new \DateTimeZone('Asia/Jakarta'))->format('d-m-Y H:i') . ' WIB';
    }
}
