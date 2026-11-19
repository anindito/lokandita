<?php

declare(strict_types=1);

namespace App\Libraries;

use App\Policy\Akses;
use CodeIgniter\Database\BaseConnection;

/**
 * Jendela pertemuan per kelas (SPEC.md §6), dibaca dari sisi staf. Setiap kueri
 * memuat syarat App\Policy\Akses::lihatKelas(): membuka, menutup dan
 * pra-pembangkitan boleh dilakukan siapa pun yang melihat kelas itu —
 * superadmin, pengampu mata kuliahnya, dan pengajar kelas itu (SPEC.md §3).
 */
final class Jendela
{
    private const KOLOM = 'm.id AS pertemuan_id, m.number, m.title, m.language, m.published_at, m.course_id, m.anonymize_leaderboard, m.practice_after_close,
        w.id AS jendela_id, w.open_at, w.close_at, w.override, w.post_close_visibility, w.late_policy, w.late_factor, w.late_until,
        w.updated_by, w.updated_at';

    private readonly BaseConnection $db;

    public function __construct(private readonly Akses $akses)
    {
        $this->db = db_connect();
    }

    /**
     * Semua pertemuan yang dipakai untuk latihan di mata kuliah kelas itu, dengan jendelanya (bila ada).
     *
     * @return list<array<string, mixed>>
     */
    public function daftar(int $kelasId): array
    {
        return $this->db->query('
            SELECT ' . self::KOLOM . ",
                   (SELECT COUNT(*) FROM meeting_problems mp WHERE mp.meeting_id = m.id) AS jumlah_soal
            FROM classes k
            JOIN courses c ON c.id = k.course_id
            JOIN meetings m ON m.course_id = k.course_id AND m.enabled = 1
            LEFT JOIN meeting_class_windows w ON w.meeting_id = m.id AND w.class_id = k.id
            WHERE k.id = ? AND {$this->akses->lihatKelas()}
            ORDER BY m.number", [$kelasId])->getResultArray();
    }

    /**
     * @return array<string, mixed>|null null bila kelas di luar hak pengguna, atau pertemuan itu
     *                                   bukan pertemuan latihan mata kuliah kelas tersebut
     */
    public function satu(int $kelasId, int $pertemuanId): ?array
    {
        return $this->db->query('
            SELECT ' . self::KOLOM . ",
                   (SELECT COUNT(*) FROM meeting_problems mp WHERE mp.meeting_id = m.id) AS jumlah_soal
            FROM classes k
            JOIN courses c ON c.id = k.course_id
            JOIN meetings m ON m.course_id = k.course_id AND m.enabled = 1
            LEFT JOIN meeting_class_windows w ON w.meeting_id = m.id AND w.class_id = k.id
            WHERE k.id = ? AND m.id = ? AND {$this->akses->lihatKelas()}", [$kelasId, $pertemuanId])->getRowArray();
    }

    /**
     * Menyimpan (membuat atau mengubah) jendela. Dipanggil di dalam transaksi pemanggil.
     *
     * @param array<string, mixed> $j     baris dari satu()
     * @param array<string, mixed> $nilai kolom yang diubah
     */
    public function simpan(int $kelasId, array $j, array $nilai): void
    {
        $nilai += ['updated_by' => $this->akses->identitas()->userId, 'updated_at' => Waktu::kini()];
        if ($j['jendela_id'] !== null) {
            $this->db->table('meeting_class_windows')->where('id', (int) $j['jendela_id'])->update($nilai);

            return;
        }
        $this->db->table('meeting_class_windows')->insert($nilai + [
            'meeting_id' => (int) $j['pertemuan_id'], 'class_id' => $kelasId, 'course_id' => (int) $j['course_id'],
            'created_at' => Waktu::kini(),
        ]);
    }
}
