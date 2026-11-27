<?php

declare(strict_types=1);

namespace App\Libraries;

use CodeIgniter\Database\BaseConnection;

/**
 * Skor objektif (SPEC-LOKAUJIAN.md §11), di SATU tempat.
 *
 * benar_salah dan pilihan_ganda: poin penuh untuk jawaban benar, 0 selainnya, 0 tanpa jawaban.
 * isian dan uraian tidak dinilai di aplikasi. Skor satu pengerjaan selalu dihitung ULANG PENUH
 * dari jawaban dan salinan beku ujiannya — tidak pernah ditambah atau dikurangi — sehingga
 * koreksi kunci atau poin cukup memanggil fungsi yang sama.
 *
 * Tidak ada nilai total, persentase atau batas lulus.
 */
final class Nilai
{
    /** Soal yang dinilai otomatis. */
    public const OTOMATIS = ['benar_salah', 'pilihan_ganda'];

    /**
     * Menghitung ulang skor setiap pengerjaan yang memenuhi $syarat (potongan SQL tulisan kode,
     * alias `a` = attempts; bukan masukan pengguna).
     *
     * @param list<mixed> $ikat
     */
    public static function hitung(BaseConnection $db, string $syarat, array $ikat = []): void
    {
        $db->query(
            "UPDATE attempts a
             SET a.score = (
                    SELECT COALESCE(SUM(q.points), 0)
                    FROM answers j
                    JOIN exam_questions q ON q.id = j.exam_question_id
                    LEFT JOIN exam_options o ON o.id = j.option_id
                    WHERE j.attempt_id = a.id
                      AND ((q.type = 'benar_salah' AND j.benar IS NOT NULL AND j.benar = q.answer)
                        OR (q.type = 'pilihan_ganda' AND o.correct = 1))),
                 a.scored_at = UTC_TIMESTAMP()
             WHERE {$syarat}",
            $ikat,
        );
    }

    /**
     * Poin yang tersedia pada satu ujian: yang dinilai otomatis, dan yang dinilai dosen sendiri.
     *
     * @return array{otomatis: string, manual: string}
     */
    public static function tersedia(BaseConnection $db, int $ujianId): array
    {
        $b = (array) $db->query(
            "SELECT COALESCE(SUM(IF(type IN ('benar_salah','pilihan_ganda'), points, 0)), 0) AS otomatis,
                    COALESCE(SUM(IF(type IN ('benar_salah','pilihan_ganda'), 0, points)), 0) AS manual
             FROM exam_questions WHERE exam_id = ?",
            [$ujianId],
        )->getRowArray();

        return ['otomatis' => (string) $b['otomatis'], 'manual' => (string) $b['manual']];
    }

    /** Angka poin untuk layar: tanpa nol di belakang ("2.50" → "2.5", "3.00" → "3"). */
    public static function tampil(string|float|int|null $poin): string
    {
        if ($poin === null || $poin === '') {
            return '';
        }
        $t = number_format((float) $poin, 2, '.', '');

        return rtrim(rtrim($t, '0'), '.');
    }
}
