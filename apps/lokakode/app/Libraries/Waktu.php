<?php

declare(strict_types=1);

namespace App\Libraries;

/**
 * Waktu disimpan dalam UTC (DATETIME MySQL) dan ditampilkan dalam Asia/Jakarta.
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

    /**
     * Tanggal (tanpa jam) dari kolom DATE, untuk ditampilkan.
     */
    public static function tanggal(?string $tanggal): string
    {
        return $tanggal === null || $tanggal === '' ? '—' : date('d-m-Y', (int) strtotime($tanggal));
    }

    /**
     * Benar bila $s adalah tanggal kalender yang sah berformat YYYY-MM-DD.
     */
    public static function tanggalSah(string $s): bool
    {
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $s, $m) !== 1) {
            return false;
        }

        return checkdate((int) $m[2], (int) $m[3], (int) $m[1]);
    }
}
