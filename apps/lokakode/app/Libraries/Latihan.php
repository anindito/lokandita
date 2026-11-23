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
        $ujian = self::ujian($pertemuan);
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
            // Ujian tidak punya komposisi minimum (SPEC.md §6).
            $min           = $ujian ? 0 : (int) $pertemuan[self::KOLOM_MIN[$t]];
            $kurang        = max(0, $min - $ada[$t]);
            $komposisi[$t] = ['ada' => $ada[$t], 'minimum' => $min, 'kurang' => $kurang];
            $poin         += $ada[$t] * self::POIN[$t];
            $siap          = $siap && $kurang === 0;
        }
        // Ujian: nilainya poin yang didapat dari SELURUH poin yang ada, jadi target = jumlah poin
        // dan syaratnya hanya "ada soal yang disetujui"; aturan 2 × target tidak berlaku.
        if ($ujian) {
            return ['komposisi' => $komposisi, 'poin' => $poin, 'target' => $poin, 'butuh' => $poin, 'kurangPoin' => 0,
                'peringatan' => false, 'siap' => $poin > 0, 'ujian' => true];
        }
        $target = (int) $pertemuan['target_points'];
        $butuh  = 2 * $target;

        return [
            'ujian'      => false,
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
     * @param array<string, mixed> $baris baris yang memuat meetings.kind
     */
    public static function ujian(array $baris): bool
    {
        return ($baris['kind'] ?? 'latihan') === 'ujian';
    }

    /**
     * Target sebuah pertemuan UJIAN selalu sama dengan jumlah poin soal yang terpasang (SPEC.md §6).
     * Dipanggil setiap kali jenis pertemuan atau daftar soalnya berubah, dan saat diterbitkan.
     *
     * @return bool benar bila target berubah (nilai mahasiswa lalu perlu dihitung ulang)
     */
    public static function selaraskanTargetUjian(int $pertemuanId): bool
    {
        $db = db_connect();
        $b  = $db->query("
            SELECT m.target_points,
                   (SELECT COALESCE(SUM(CASE v.difficulty WHEN 'MUDAH_SEKALI' THEN 3 WHEN 'MUDAH' THEN 5 WHEN 'MUDAH_SEDANG' THEN 8
                                                        WHEN 'SEDANG' THEN 10 ELSE 15 END), 0)
                    FROM meeting_problems mp JOIN problem_versions v ON v.id = mp.problem_version_id
                    WHERE mp.meeting_id = m.id AND v.status = 'APPROVED') AS poin
            FROM meetings m WHERE m.id = ? AND m.kind = 'ujian'", [$pertemuanId])->getRowArray();
        if ($b === null || (int) $b['poin'] < 1 || (int) $b['poin'] === (int) $b['target_points']) {
            return false;
        }
        $db->query('UPDATE meetings SET target_points = ?, updated_at = ? WHERE id = ?', [min(1000, (int) $b['poin']), Waktu::kini(), $pertemuanId]);

        return true;
    }

    /**
     * Syarat SQL "mahasiswa $pengguna boleh melihat nilainya di pertemuan $m": selalu untuk latihan;
     * untuk ujian hanya sesudah nilai dirilis untuk kelas tempat ia terdaftar (SPEC.md §6).
     * Satu-satunya tempat aturan ini ditulis dalam SQL; padanan PHP-nya App\Libraries\Ruang::nilaiTampak().
     *
     * @param string $m        alias tabel meetings
     * @param string $pengguna ekspresi SQL berisi user id (kolom, bukan masukan pengguna)
     */
    public static function sqlNilaiTampak(string $m, string $pengguna): string
    {
        return "({$m}.kind = 'latihan' OR EXISTS (SELECT 1 FROM enrollments er JOIN meeting_class_windows wr ON wr.class_id = er.class_id
                 AND wr.meeting_id = {$m}.id WHERE er.user_id = {$pengguna} AND wr.scores_released_at IS NOT NULL))";
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
