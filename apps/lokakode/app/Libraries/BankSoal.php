<?php

declare(strict_types=1);

namespace App\Libraries;

use App\Policy\Akses;
use CodeIgniter\Database\BaseConnection;

/**
 * Pembacaan bank soal (SPEC.md §8.1). Setiap kueri memuat syarat KELOLA mata
 * kuliah dari App\Policy\Akses: bank soal hanya untuk superadmin dan pengampu
 * mata kuliah itu. Tidak ada fungsi yang mengambil soal hanya berdasarkan id.
 */
final class BankSoal
{
    private readonly BaseConnection $db;

    public function __construct(private readonly Akses $akses)
    {
        $this->db = db_connect();
    }

    /**
     * Soal sebuah pertemuan dengan versi terakhirnya dan versi yang sedang dipakai.
     *
     * @return list<array<string, mixed>>
     */
    public function daftarSoal(int $pertemuanId): array
    {
        return $this->db->query("
            SELECT p.id, p.slug, v.id AS versi_id, v.version, v.status, v.difficulty, v.translations,
                   mp.problem_version_id AS versi_dipakai
            FROM problems p
            JOIN meetings m ON m.id = p.meeting_id
            JOIN courses c ON c.id = m.course_id
            JOIN problem_versions v ON v.problem_id = p.id
                AND v.version = (SELECT MAX(v2.version) FROM problem_versions v2 WHERE v2.problem_id = p.id)
            LEFT JOIN meeting_problems mp ON mp.problem_id = p.id
            WHERE p.meeting_id = ? AND {$this->akses->kelolaMatkul()}
            ORDER BY FIELD(v.difficulty, 'MUDAH_SEKALI', 'MUDAH', 'MUDAH_SEDANG', 'SEDANG', 'SULIT'), p.slug", [$pertemuanId])->getResultArray();
    }
}
