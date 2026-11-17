<?php

declare(strict_types=1);

if (! function_exists('angka')) {
    /**
     * Angka nilai untuk ditampilkan: paling banyak dua desimal, tanpa nol di
     * belakang (5 · 2.5 · 4.67). Titik sebagai tanda desimal di ketiga bahasa,
     * supaya sama dengan yang ditampilkan skrip halaman soal.
     */
    function angka(float|int|string|null $n): string
    {
        if ($n === null || $n === '') {
            return '—';
        }
        $teks = number_format((float) $n, 2, '.', '');

        return str_contains($teks, '.') ? rtrim(rtrim($teks, '0'), '.') : $teks;
    }
}
