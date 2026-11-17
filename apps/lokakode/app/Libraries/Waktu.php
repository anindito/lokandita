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
     * Isian <input type="datetime-local"> (waktu Asia/Jakarta, "2026-11-20T08:00")
     * menjadi DATETIME UTC. null bila bentuknya salah atau bukan waktu yang ada.
     */
    public static function dariIsian(string $isian): ?string
    {
        if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $isian) !== 1) {
            return null;
        }
        $t = \DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $isian, new \DateTimeZone('Asia/Jakarta'));
        if ($t === false || $t->format('Y-m-d\TH:i') !== $isian) {
            return null;
        }

        return $t->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s');
    }

    /**
     * DATETIME UTC menjadi nilai untuk <input type="datetime-local"> dalam Asia/Jakarta.
     */
    public static function keIsian(?string $utc): string
    {
        if ($utc === null || $utc === '') {
            return '';
        }
        $t = new \DateTimeImmutable($utc, new \DateTimeZone('UTC'));

        return $t->setTimezone(new \DateTimeZone('Asia/Jakarta'))->format('Y-m-d\TH:i');
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
