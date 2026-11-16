<?php

declare(strict_types=1);

namespace App\Libraries\Soal;

/**
 * Seed per mahasiswa per versi soal (SPEC.md §8.2, KONTRAK.md §6):
 *
 *   HMAC_SHA256(INSTANCE_SECRET, user_id + "|" + problem_version_id)
 *
 * Delapan byte pertamanya dibaca sebagai bilangan big-endian dengan bit
 * teratas dinolkan, sehingga hasilnya 0 … 2^63 − 1: muat di int PHP, di JSON,
 * dan di kolom BIGINT UNSIGNED. Mahasiswa yang sama dan versi yang sama selalu
 * menghasilkan seed yang sama; INSTANCE_SECRET karena itu tidak boleh berubah
 * setelah ada instans (KONTRAK.md §10).
 */
final class Benih
{
    public static function untuk(string $userId, int $versiId): int
    {
        $rahasia = (string) getenv('INSTANCE_SECRET');
        if ($rahasia === '') {
            throw new \RuntimeException('INSTANCE_SECRET kosong: seed tidak bisa dihitung.');
        }
        $hmac = hash_hmac('sha256', $userId . '|' . $versiId, $rahasia, true);

        return unpack('J', substr($hmac, 0, 8))[1] & PHP_INT_MAX;
    }
}
