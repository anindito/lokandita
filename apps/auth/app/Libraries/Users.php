<?php

declare(strict_types=1);

namespace App\Libraries;

/**
 * Akses baca tabel users yang dipakai di banyak tempat.
 */
final class Users
{
    /**
     * @return array<string, mixed>|null
     */
    public static function find(string $id): ?array
    {
        return db_connect()->table('users')->where('id', $id)->get()->getRowArray();
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function findByNomorInduk(string $nomorInduk): ?array
    {
        return db_connect()->table('users')->where('nomor_induk', $nomorInduk)->get()->getRowArray();
    }
}
