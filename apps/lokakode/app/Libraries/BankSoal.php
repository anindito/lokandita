<?php

declare(strict_types=1);

namespace App\Libraries;

use App\Policy\Akses;
use CodeIgniter\Database\BaseConnection;

/**
 * Pembacaan bank soal (SPEC.md §8.1). Setiap kueri memuat syarat dari
 * App\Policy\Akses; tidak ada fungsi yang mengambil soal, versi atau impor
 * hanya berdasarkan id.
 *
 * Bank soal hanya untuk superadmin dan pengampu mata kuliah itu. Seperti
 * pertemuan, sebuah soal TERLIHAT (ada) bagi siapa pun yang melihat mata
 * kuliahnya, dan kolom `boleh_kelola` menentukan apakah isinya boleh dibuka:
 * pengendali menjawab 403 bila 0, dan 404 bila barisnya tidak kembali.
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

    /**
     * @return array<string, mixed>|null null bila tidak ada ATAU di luar hak pengguna
     */
    public function soal(int $id): ?array
    {
        return $this->db->query("
            SELECT p.*, m.number AS pertemuan_nomor, m.title AS pertemuan_judul, m.language, m.course_id,
                   c.code AS matkul_kode, ({$this->akses->kelolaMatkul()}) AS boleh_kelola,
                   mp.problem_version_id AS versi_dipakai
            FROM problems p
            JOIN meetings m ON m.id = p.meeting_id
            JOIN courses c ON c.id = m.course_id
            LEFT JOIN meeting_problems mp ON mp.problem_id = p.id
            WHERE p.id = ? AND {$this->akses->lihatMatkul()}", [$id])->getRowArray();
    }

    /**
     * Semua versi sebuah soal, terbaru lebih dulu. Syarat kelola ikut di kueri.
     *
     * @return list<array<string, mixed>>
     */
    public function versi(int $soalId): array
    {
        return $this->db->query("
            SELECT v.*, p.slug
            FROM problem_versions v
            JOIN problems p ON p.id = v.problem_id
            JOIN meetings m ON m.id = p.meeting_id
            JOIN courses c ON c.id = m.course_id
            WHERE v.problem_id = ? AND {$this->akses->kelolaMatkul()}
            ORDER BY v.version DESC", [$soalId])->getResultArray();
    }

    /**
     * Impor yang boleh dilihat pengguna ini: superadmin semua; pengampu hanya
     * suntingan (source = sunting) di mata kuliah yang diampunya.
     *
     * @return array<string, mixed>|null
     */
    public function impor(string $id): ?array
    {
        if ($this->akses->superadmin()) {
            return $this->db->query('SELECT id, source, course_id FROM imports WHERE id = ?', [$id])->getRowArray();
        }

        return $this->db->query("
            SELECT i.id, i.source, i.course_id
            FROM imports i JOIN courses c ON c.id = i.course_id
            WHERE i.id = ? AND i.source = 'sunting' AND {$this->akses->kelolaMatkul()}", [$id])->getRowArray();
    }

    /**
     * Unggahan dan impor CLI terakhir, untuk halaman impor superadmin.
     *
     * @return list<array<string, mixed>>
     */
    public function daftarImpor(int $batas = 30): array
    {
        if (! $this->akses->superadmin()) {
            return [];
        }

        return $this->db->query("
            SELECT i.*, c.code AS matkul_kode, m.number AS pertemuan_nomor,
                   (SELECT COUNT(*) FROM import_items it WHERE it.import_id = i.id) AS total,
                   (SELECT COUNT(*) FROM import_items it WHERE it.import_id = i.id AND it.state = 'DITERIMA') AS diterima,
                   (SELECT COUNT(*) FROM import_items it WHERE it.import_id = i.id AND it.state = 'DITOLAK') AS ditolak
            FROM imports i LEFT JOIN courses c ON c.id = i.course_id LEFT JOIN meetings m ON m.id = i.meeting_id
            WHERE i.source IN ('web', 'cli')
            ORDER BY i.created_at DESC, i.id LIMIT ?", [$batas])->getResultArray();
    }
}
