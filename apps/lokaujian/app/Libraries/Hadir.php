<?php

declare(strict_types=1);

namespace App\Libraries;

/**
 * Tanda "terakhir terlihat" tiap mahasiswa di satu sesi, untuk tampilan langsung dosen
 * (SPEC-LOKAUJIAN.md §10: menunggu, tersambung, terputus sejak hh:mm).
 *
 * Disimpan di Redis saja, ditulis oleh permintaan yang memang sudah dikirim halaman mahasiswa
 * (tanya keadaan tiap 5 atau 10 detik, dan setiap simpan). Tidak ada keputusan yang bergantung
 * padanya: bila Redis kosong atau mati, kolom itu menjadi "tidak diketahui" sampai permintaan
 * berikutnya, dan tidak ada yang lain yang berubah (SPEC-LOKAUJIAN.md §14).
 */
final class Hadir
{
    /** Terlihat dalam sekian detik terakhir = tersambung. Halaman ujian bertanya tiap 10 detik. */
    public const SEGAR = 30;

    private const UMUR = 43_200;

    private static function kunci(int $sesiId, string $userId): string
    {
        return 'lokaujian:hadir:' . $sesiId . ':' . $userId;
    }

    /**
     * @param 'tunggu'|'kerja' $keadaan
     */
    public static function catat(int $sesiId, string $userId, string $keadaan): void
    {
        try {
            Jadwal::redis()->setex(self::kunci($sesiId, $userId), self::UMUR, $keadaan . '|' . time());
        } catch (\Throwable) {
            // Hanya tampilan; lihat keterangan kelas.
        }
    }

    /**
     * @param list<string> $userIds
     *
     * @return array<string, array{keadaan: string, waktu: int}>|null null: Redis tidak terjangkau
     */
    public static function baca(int $sesiId, array $userIds): ?array
    {
        if ($userIds === []) {
            return [];
        }
        try {
            $nilai = Jadwal::redis()->mGet(array_map(static fn (string $u): string => self::kunci($sesiId, $u), $userIds));
        } catch (\Throwable) {
            return null;
        }
        $hasil = [];
        foreach ($userIds as $i => $u) {
            $v = is_array($nilai) ? ($nilai[$i] ?? false) : false;
            if (is_string($v) && preg_match('/^(tunggu|kerja)\|(\d+)$/', $v, $m) === 1) {
                $hasil[$u] = ['keadaan' => $m[1], 'waktu' => (int) $m[2]];
            }
        }

        return $hasil;
    }
}
