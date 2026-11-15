<?php

declare(strict_types=1);

namespace App\Libraries;

use App\Policy\Akses;
use CodeIgniter\Database\BaseConnection;

/**
 * Pembacaan struktur akademik (SPEC.md §5). Setiap kueri yang menyentuh mata
 * kuliah, kelas atau pendaftaran memuat syarat dari App\Policy\Akses; tidak
 * ada fungsi di sini yang mengambil baris hanya berdasarkan id.
 */
final class Akademik
{
    private readonly BaseConnection $db;

    public function __construct(private readonly Akses $akses)
    {
        $this->db = db_connect();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function tahunAktif(): ?array
    {
        return $this->db->query("SELECT * FROM academic_years WHERE status = 'active'")->getRowArray();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function tahun(int $id): ?array
    {
        return $this->db->query('SELECT * FROM academic_years WHERE id = ?', [$id])->getRowArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function semuaTahun(): array
    {
        return $this->db->query('SELECT * FROM academic_years ORDER BY starts_on DESC, id DESC')->getResultArray();
    }

    /**
     * Mata kuliah yang boleh dilihat pengguna ini, dengan tanda boleh_kelola.
     *
     * @return list<array<string, mixed>>
     */
    public function daftarMatkul(): array
    {
        return $this->db->query("
            SELECT c.*, ({$this->akses->kelolaMatkul()}) AS boleh_kelola
            FROM courses c
            WHERE {$this->akses->lihatMatkul()}
            ORDER BY c.code")->getResultArray();
    }

    /**
     * @return array<string, mixed>|null null bila tidak ada ATAU di luar hak pengguna
     */
    public function matkul(int $id): ?array
    {
        return $this->db->query("
            SELECT c.*, ({$this->akses->kelolaMatkul()}) AS boleh_kelola
            FROM courses c
            WHERE c.id = ? AND {$this->akses->lihatMatkul()}", [$id])->getRowArray();
    }

    /**
     * Kelas yang boleh dilihat pengguna ini pada satu tahun ajaran; bila
     * $matkulId diisi, hanya kelas mata kuliah itu.
     *
     * @return list<array<string, mixed>>
     */
    public function daftarKelas(int $tahunId, ?int $matkulId = null): array
    {
        $ikat   = [$tahunId];
        $syarat = '';
        if ($matkulId !== null) {
            $syarat = ' AND k.course_id = ?';
            $ikat[] = $matkulId;
        }

        return $this->db->query("
            SELECT k.*, c.code AS matkul_kode, c.name AS matkul_nama, c.owner_id,
                   ({$this->akses->kelolaKelas()}) AS boleh_kelola,
                   (SELECT COUNT(*) FROM enrollments e WHERE e.class_id = k.id AND e.status = 'AKTIF') AS jumlah_aktif,
                   (SELECT COUNT(*) FROM class_teachers ct WHERE ct.class_id = k.id) AS jumlah_pengajar
            FROM classes k JOIN courses c ON c.id = k.course_id
            WHERE k.academic_year_id = ?{$syarat} AND {$this->akses->lihatKelas()}
            ORDER BY c.code, k.name", $ikat)->getResultArray();
    }

    /**
     * @return array<string, mixed>|null null bila tidak ada ATAU di luar hak pengguna
     */
    public function kelas(int $id): ?array
    {
        return $this->db->query("
            SELECT k.*, c.code AS matkul_kode, c.name AS matkul_nama, c.owner_id,
                   t.label AS tahun_label, t.status AS tahun_status,
                   ({$this->akses->kelolaKelas()}) AS boleh_kelola
            FROM classes k
            JOIN courses c ON c.id = k.course_id
            JOIN academic_years t ON t.id = k.academic_year_id
            WHERE k.id = ? AND {$this->akses->lihatKelas()}", [$id])->getRowArray();
    }

    /**
     * id pengajar per kelas. Syarat lihat ikut di kueri: kelas di luar hak
     * pengguna tidak menghasilkan baris.
     *
     * @param list<int> $kelasIds
     *
     * @return array<int, list<string>>
     */
    public function pengajar(array $kelasIds): array
    {
        $kelasIds = array_values(array_unique(array_map('intval', $kelasIds)));
        if ($kelasIds === []) {
            return [];
        }
        $hasil = [];
        $baris = $this->db->query("
            SELECT ct.class_id, ct.user_id
            FROM class_teachers ct
            JOIN classes k ON k.id = ct.class_id
            JOIN courses c ON c.id = k.course_id
            WHERE ct.class_id IN ? AND {$this->akses->lihatKelas()}
            ORDER BY ct.assigned_at, ct.user_id", [$kelasIds])->getResultArray();
        foreach ($baris as $b) {
            $hasil[(int) $b['class_id']][] = (string) $b['user_id'];
        }

        return $hasil;
    }

    /**
     * Pendaftaran satu kelas, dengan syarat lihat yang sama.
     *
     * @return list<array<string, mixed>>
     */
    public function pendaftaranKelas(int $kelasId): array
    {
        return $this->db->query("
            SELECT e.*
            FROM enrollments e
            JOIN classes k ON k.id = e.class_id
            JOIN courses c ON c.id = k.course_id
            WHERE e.class_id = ? AND {$this->akses->lihatKelas()}
            ORDER BY e.id", [$kelasId])->getResultArray();
    }

    /**
     * Kelas-kelas pengguna ini sebagai mahasiswa.
     *
     * @return list<array<string, mixed>>
     */
    public function kelasSaya(): array
    {
        return $this->db->query("
            SELECT e.status, e.graduated_on, k.id AS kelas_id, k.name AS kelas_nama,
                   c.code AS matkul_kode, c.name AS matkul_nama, c.owner_id,
                   t.label AS tahun_label, t.status AS tahun_status
            FROM enrollments e
            JOIN classes k ON k.id = e.class_id
            JOIN courses c ON c.id = k.course_id
            JOIN academic_years t ON t.id = k.academic_year_id
            WHERE {$this->akses->pendaftaranSendiri()}
            ORDER BY t.starts_on DESC, c.code")->getResultArray();
    }
}
