<?php

declare(strict_types=1);

namespace Lokandita\Auth;

/**
 * Hal yang disisipkan sebuah aplikasi ke tata letak bersama untuk SATU permintaan, tanpa
 * menyentuh setiap view: pita peringatan di bawah kepala halaman, dan skrip tambahan.
 * Diisi oleh filter aplikasi (mis. filter SEB LokaKode); tata letak hanya menampilkannya.
 */
final class Sisipan
{
    /** @var list<string> */
    private static array $pita = [];

    /** @var list<string> */
    private static array $skrip = [];

    public static function pita(string $teks): void
    {
        if (! in_array($teks, self::$pita, true)) {
            self::$pita[] = $teks;
        }
    }

    /**
     * @param string $jalur jalur skrip di aplikasi itu sendiri, mis. /js/seb.js
     */
    public static function skrip(string $jalur): void
    {
        if (preg_match('#^/[A-Za-z0-9/_.-]+\.js$#', $jalur) !== 1) {
            throw new \InvalidArgumentException('skrip sisipan harus berkas .js di aplikasi ini sendiri');
        }
        if (! in_array($jalur, self::$skrip, true)) {
            self::$skrip[] = $jalur;
        }
    }

    /**
     * @return list<string>
     */
    public static function semuaPita(): array
    {
        return self::$pita;
    }

    /**
     * @return list<string>
     */
    public static function semuaSkrip(): array
    {
        return self::$skrip;
    }
}
