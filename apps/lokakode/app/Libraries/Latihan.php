<?php

declare(strict_types=1);

namespace App\Libraries;

/**
 * Aturan latihan sebuah pertemuan yang tidak bergantung pada siapa yang
 * bertanya (SPEC.md §6, §7): poin per tingkat, komposisi minimum, kesiapan
 * untuk diterbitkan, templat awal, bendera kompiler, keadaan jendela.
 *
 * Fungsi di sini menerima baris pertemuan atau jendela yang SUDAH lolos
 * App\Policy\Akses di pemanggilnya; tidak ada yang mengambil baris dari id saja.
 */
final class Latihan
{
    public const TINGKAT = ['MUDAH_SEKALI', 'MUDAH', 'MUDAH_SEDANG', 'SEDANG', 'SULIT'];

    // SPEC.md §7.
    public const POIN = ['MUDAH_SEKALI' => 3, 'MUDAH' => 5, 'MUDAH_SEDANG' => 8, 'SEDANG' => 10, 'SULIT' => 15];

    public const KOLOM_MIN = [
        'MUDAH_SEKALI' => 'min_mudah_sekali', 'MUDAH' => 'min_mudah', 'MUDAH_SEDANG' => 'min_mudah_sedang',
        'SEDANG' => 'min_sedang', 'SULIT' => 'min_sulit',
    ];

    // Hanya 4 soal MUDAH_SEKALI terbaik yang dihitung ke nilai pertemuan (SPEC.md §7).
    public const KUOTA_MUDAH_SEKALI = 4;

    // Bendera kompiler yang boleh dipilih per pertemuan. Daftar yang sama
    // diperiksa lagi oleh worker (KONTRAK.md §6, judge/lokandita_judge/mesin.py).
    public const BENDERA_UMUM = ['-Wall', '-Wextra', '-Wshadow', '-Wpedantic', '-O0', '-O1', '-O2', '-lm'];

    public const BENDERA_STD = ['c' => ['c11', 'c17', 'gnu17'], 'cpp' => ['c++14', 'c++17', 'c++20', 'gnu++17']];

    public const BENDERA_MAKS = 8;

    public const TEMPLAT_MAKS = 20000; // karakter

    private const TEMPLAT = [
        'c'      => "#include <stdio.h>\n\nint main(void) {\n\n    return 0;\n}\n",
        'cpp'    => "#include <iostream>\nusing namespace std;\n\nint main() {\n\n    return 0;\n}\n",
        'python' => "\n",
    ];

    /**
     * Templat awal editor: milik pertemuan bila diisi, kalau tidak bawaan bahasanya.
     *
     * @param array<string, mixed> $pertemuan baris meetings
     */
    public static function templat(array $pertemuan): string
    {
        $milik = (string) ($pertemuan['starter_template'] ?? '');

        return $milik !== '' ? $milik : self::TEMPLAT[$pertemuan['language']];
    }

    /**
     * @return list<string> bendera yang sah untuk bahasa itu
     */
    public static function benderaSah(string $bahasa): array
    {
        if (! isset(self::BENDERA_STD[$bahasa])) {
            return [];
        }

        return array_merge(self::BENDERA_UMUM, array_map(static fn ($s) => '-std=' . $s, self::BENDERA_STD[$bahasa]));
    }

    /**
     * Teks bendera (dipisah spasi) menjadi daftar, atau null bila ada yang tidak diizinkan.
     *
     * @return list<string>|null
     */
    public static function bendera(string $teks, string $bahasa): ?array
    {
        $daftar = preg_split('/\s+/', trim($teks), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        if (count($daftar) > self::BENDERA_MAKS || array_diff($daftar, self::benderaSah($bahasa)) !== []) {
            return null;
        }

        return array_values($daftar);
    }

    /**
     * Apakah pertemuan boleh diterbitkan, dan apa persisnya yang kurang (SPEC.md §6):
     *   - tiap tingkat punya soal DISETUJUI sebanyak komposisi minimumnya;
     *   - poin yang tersedia paling sedikit 2 × target (peringatan di bawah 3 ×).
     *
     * @param array<string, mixed> $pertemuan baris meetings (nilai target dan minimum yang mau diperiksa)
     *
     * @return array{komposisi: array<string, array{ada: int, minimum: int, kurang: int}>, poin: int, target: int,
     *               butuh: int, kurangPoin: int, peringatan: bool, siap: bool}
     */
    public static function kesiapan(array $pertemuan): array
    {
        $ada = array_fill_keys(self::TINGKAT, 0);
        $baris = db_connect()->query('
            SELECT v.difficulty, COUNT(*) AS n
            FROM meeting_problems mp JOIN problem_versions v ON v.id = mp.problem_version_id
            WHERE mp.meeting_id = ? AND v.status = \'APPROVED\'
            GROUP BY v.difficulty', [(int) $pertemuan['id']])->getResultArray();
        foreach ($baris as $b) {
            $ada[$b['difficulty']] = (int) $b['n'];
        }

        $komposisi = [];
        $poin      = 0;
        $siap      = true;
        foreach (self::TINGKAT as $t) {
            $min           = (int) $pertemuan[self::KOLOM_MIN[$t]];
            $kurang        = max(0, $min - $ada[$t]);
            $komposisi[$t] = ['ada' => $ada[$t], 'minimum' => $min, 'kurang' => $kurang];
            $poin         += $ada[$t] * self::POIN[$t];
            $siap          = $siap && $kurang === 0;
        }
        $target = (int) $pertemuan['target_points'];
        $butuh  = 2 * $target;

        return [
            'komposisi'  => $komposisi,
            'poin'       => $poin,
            'target'     => $target,
            'butuh'      => $butuh,
            'kurangPoin' => max(0, $butuh - $poin),
            'peringatan' => $poin >= $butuh && $poin < 3 * $target,
            'siap'       => $siap && $poin >= $butuh,
        ];
    }

    /**
     * Keadaan jendela sebuah pertemuan untuk satu kelas (KONTRAK.md §6):
     *   belum      tidak ada jendela, atau belum dijadwalkan dan tidak dibuka manual
     *   terjadwal  waktu bukanya belum tiba
     *   buka       dibuka manual, atau open_at <= kini < close_at
     *   tutup      ditutup manual, atau close_at sudah lewat
     *
     * @param array<string, mixed>|null $jendela baris meeting_class_windows (kolom override, open_at, close_at), atau null
     */
    public static function keadaan(?array $jendela, ?string $kini = null): string
    {
        if ($jendela === null || ($jendela['override'] ?? null) === null) {
            return 'belum';
        }
        if ($jendela['override'] === 'open') {
            return 'buka';
        }
        if ($jendela['override'] === 'closed') {
            return 'tutup';
        }
        if ($jendela['open_at'] === null || $jendela['close_at'] === null) {
            return 'belum';
        }
        $kini ??= Waktu::kini();
        if ($kini < $jendela['open_at']) {
            return 'terjadwal';
        }

        return $kini < $jendela['close_at'] ? 'buka' : 'tutup';
    }
}
