<?php

declare(strict_types=1);

namespace App\Libraries\Soal;

/**
 * Soal sebuah pertemuan dalam bentuk KONTRAK.md §7. Tidak memeriksa hak akses:
 * dipakai oleh perintah CLI dan oleh halaman sunting, yang sudah memeriksanya.
 */
final class Ekspor
{
    /**
     * @return array{course: string, meeting: int, language: string, problems: list<array<string, mixed>>}|null
     */
    public static function pertemuan(string $kodeMatkul, int $nomor): ?array
    {
        $db = db_connect();
        $p  = $db->query('SELECT m.id, m.number, m.language, c.code FROM meetings m JOIN courses c ON c.id = m.course_id WHERE c.code = ? AND m.number = ?', [$kodeMatkul, $nomor])->getRowArray();
        if ($p === null) {
            return null;
        }
        $versi = $db->query('
            SELECT p.slug, v.* FROM problems p
            JOIN problem_versions v ON v.problem_id = p.id
                AND v.version = (SELECT MAX(v2.version) FROM problem_versions v2 WHERE v2.problem_id = p.id)
            WHERE p.meeting_id = ? ORDER BY p.id', [$p['id']])->getResultArray();

        return [
            'course'   => (string) $p['code'],
            'meeting'  => (int) $p['number'],
            'language' => (string) $p['language'],
            'problems' => array_map(static fn (array $v): array => self::soal($v), $versi),
        ];
    }

    /**
     * Satu versi soal dalam bentuk impor.
     *
     * @param array<string, mixed> $v baris problem_versions dengan kolom slug
     *
     * @return array<string, mixed>
     */
    public static function soal(array $v): array
    {
        $terjemahan = json_decode((string) $v['translations'], true) ?? [];
        // Bahasa yang belum diterjemahkan ditulis {} supaya penulis soal melihat tempatnya.
        $terjemahan += ['en' => new \stdClass(), 'ar' => new \stdClass()];
        $params = json_decode((string) $v['params'], true) ?? [];

        return [
            'slug'                            => (string) $v['slug'],
            'difficulty'                      => strtolower((string) $v['difficulty']),
            'comparison_mode'                 => (string) $v['comparison_mode'],
            'time_limit_ms'                   => (int) $v['time_limit_ms'],
            'samples_count'                   => (int) $v['samples_count'],
            'hidden_count'                    => (int) $v['hidden_count'],
            'required_concepts_in_submission' => json_decode((string) $v['required_in_submission'], true) ?? [],
            'params'                          => $params === [] ? new \stdClass() : $params,
            'translations'                    => $terjemahan,
            'generator'                       => (string) $v['generator'],
            'reference_solution'              => (string) $v['reference_solution'],
        ];
    }
}
