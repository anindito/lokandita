<?php

declare(strict_types=1);

namespace Lokandita\Auth;

/**
 * Nama dan nomor induk pengguna, dibaca dari view `user_directory` sebagai
 * `identity_reader` (KONTRAK.md §3). Aplikasi klien tidak menyalin data ini:
 * LokaAuth tetap satu-satunya sumber.
 *
 * Setiap baris: id, nomor_induk, nama, global_role, status.
 */
final class UserDirectory
{
    private const POTONG = 500;

    /**
     * @param list<string> $ids
     *
     * @return array<string, array<string, string>> dikunci dengan id
     */
    public static function byIds(array $ids): array
    {
        return self::cari('id', $ids);
    }

    /**
     * @param list<string> $nomorInduk
     *
     * @return array<string, array<string, string>> dikunci dengan nomor induk
     */
    public static function byNomorInduk(array $nomorInduk): array
    {
        return self::cari('nomor_induk', $nomorInduk);
    }

    /**
     * Dari $ids, siapa saja yang boleh masuk ke $app.
     *
     * @param list<string> $ids
     *
     * @return array<string, true> dikunci dengan id
     */
    public static function withAccess(array $ids, string $app): array
    {
        $hasil = [];
        foreach (array_chunk(array_values(array_unique($ids)), self::POTONG) as $potong) {
            $tanda = implode(',', array_fill(0, count($potong), '?'));
            $stmt  = ReaderDb::conn()->prepare("SELECT user_id FROM app_access WHERE app = ? AND enabled = 1 AND user_id IN ({$tanda})");
            $stmt->execute([$app, ...$potong]);
            foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $b) {
                $hasil[(string) $b['user_id']] = true;
            }
        }

        return $hasil;
    }

    /**
     * @param 'id'|'nomor_induk' $kolom
     * @param list<string>       $nilai
     *
     * @return array<string, array<string, string>>
     */
    private static function cari(string $kolom, array $nilai): array
    {
        $hasil = [];
        foreach (array_chunk(array_values(array_unique($nilai)), self::POTONG) as $potong) {
            $tanda = implode(',', array_fill(0, count($potong), '?'));
            $stmt  = ReaderDb::conn()->prepare("SELECT id, nomor_induk, nama, global_role, status FROM user_directory WHERE {$kolom} IN ({$tanda})");
            $stmt->execute($potong);
            foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $b) {
                $hasil[(string) $b[$kolom]] = array_map('strval', $b);
            }
        }

        return $hasil;
    }
}
