<?php

declare(strict_types=1);

namespace App\Libraries;

/**
 * Penulis CSV untuk Excel (SPEC-LOKAUJIAN.md §12), di satu tempat:
 *
 *   - UTF-8 DENGAN BOM dan akhir baris CRLF, supaya Excel menampilkan teks Indonesia dan Arab
 *     dengan benar;
 *   - pemisah titik koma (bawaan, untuk Excel berpengaturan Indonesia) atau koma;
 *   - sel dikutip bila memuat pemisah, tanda kutip, ganti baris, atau spasi di tepinya;
 *   - sel TEKS yang diawali `=`, `+`, `-`, `@`, tab atau CR diberi apostrof di depannya, sehingga
 *     jawaban mahasiswa tidak pernah dijalankan sebagai rumus di lembar kerja dosen;
 *   - ANGKA (int/float dari kode ini sendiri) ditulis polos; tanda desimalnya mengikuti pemisah:
 *     koma bersama titik koma (Excel Indonesia), titik bersama koma.
 *
 * Sel: string = teks (dijaga dari rumus), int|float = angka, null = kosong.
 */
final class Csv
{
    public const BOM     = "\xEF\xBB\xBF";
    public const PEMISAH = ['titik_koma' => ';', 'koma' => ','];

    public static function sel(string|int|float|null $nilai, string $pemisah): string
    {
        if ($nilai === null || $nilai === '') {
            return '';
        }
        if (is_int($nilai)) {
            return (string) $nilai;
        }
        if (is_float($nilai)) {
            $t = Nilai::tampil($nilai);

            return $pemisah === ';' ? str_replace('.', ',', $t) : $t;
        }
        $t = $nilai;
        if (in_array($t[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            $t = "'" . $t;
        }
        if (strpbrk($t, "\"\r\n" . $pemisah) !== false || $t !== trim($t)) {
            return '"' . str_replace('"', '""', $t) . '"';
        }

        return $t;
    }

    /**
     * @param list<string>                          $kepala
     * @param list<list<string|int|float|null>> $baris
     */
    public static function tulis(array $kepala, array $baris, string $pemisah): string
    {
        $isi = self::BOM;
        foreach (array_merge([$kepala], $baris) as $b) {
            $isi .= implode($pemisah, array_map(static fn ($v): string => self::sel($v, $pemisah), $b)) . "\r\n";
        }

        return $isi;
    }
}
