<?php

declare(strict_types=1);

namespace App\Libraries;

use App\Policy\Akses;
use Lokandita\Auth\RosterDb;

/**
 * Pembacaan struktur akademik lewat lima view roster (KONTRAK.md §18), sebagai `roster_reader`.
 * Setiap kueri yang menyentuh mata kuliah, kelas atau pendaftaran memuat syarat dari
 * App\Policy\Akses; tidak ada fungsi di sini yang mengambil baris hanya berdasarkan id.
 *
 * Hanya nama view yang disebut, tanpa nama skema, dan tidak ada join dengan skema LokaUjian.
 */
final class Roster
{
    public function __construct(private readonly Akses $akses)
    {
    }

    /**
     * @param list<int|string> $ikat
     *
     * @return list<array<string, mixed>>
     */
    private function baris(string $sql, array $ikat = []): array
    {
        return RosterDb::conn()->execute_query($sql, $ikat)->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function tahunAktif(): ?array
    {
        return $this->baris("SELECT id, label, status FROM roster_academic_years WHERE status = 'active'")[0] ?? null;
    }

    /**
     * Mata kuliah yang boleh dilihat pengguna ini, dengan tanda `diampu`.
     *
     * @return list<array<string, mixed>>
     */
    public function daftarMatkul(): array
    {
        return $this->baris("
            SELECT c.id, c.code, c.name, c.owner_id, ({$this->akses->ampuMatkul()}) AS diampu
            FROM roster_courses c
            WHERE {$this->akses->lihatMatkul()}
            ORDER BY c.code");
    }

    /**
     * @return array<string, mixed>|null null bila tidak ada ATAU di luar hak pengguna
     */
    public function matkul(int $id): ?array
    {
        return $this->baris("
            SELECT c.id, c.code, c.name, c.owner_id, ({$this->akses->ampuMatkul()}) AS diampu
            FROM roster_courses c
            WHERE c.id = ? AND {$this->akses->lihatMatkul()}", [$id])[0] ?? null;
    }

    /**
     * Kelas yang boleh dilihat pengguna ini pada satu tahun ajaran; bila $matkulId diisi,
     * hanya kelas mata kuliah itu.
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

        return $this->baris("
            SELECT k.id, k.course_id, k.name, k.archived_at, c.code AS matkul_kode, c.name AS matkul_nama, c.owner_id,
                   (SELECT COUNT(*) FROM roster_enrollments ex WHERE ex.class_id = k.id AND ex.status = 'AKTIF') AS jumlah_aktif
            FROM roster_classes k JOIN roster_courses c ON c.id = k.course_id
            WHERE k.academic_year_id = ?{$syarat} AND {$this->akses->lihatKelas()}
            ORDER BY c.code, k.name", $ikat);
    }

    /**
     * @return array<string, mixed>|null null bila tidak ada ATAU di luar hak pengguna
     */
    public function kelas(int $id): ?array
    {
        return $this->baris("
            SELECT k.id, k.course_id, k.name, k.archived_at, c.code AS matkul_kode, c.name AS matkul_nama, c.owner_id,
                   t.label AS tahun_label, t.status AS tahun_status
            FROM roster_classes k
            JOIN roster_courses c ON c.id = k.course_id
            JOIN roster_academic_years t ON t.id = k.academic_year_id
            WHERE k.id = ? AND {$this->akses->lihatKelas()}", [$id])[0] ?? null;
    }

    /**
     * id pengajar per kelas. Syarat lihat ikut di kueri: kelas di luar hak pengguna tidak
     * menghasilkan baris.
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
        $tanda = implode(',', array_fill(0, count($kelasIds), '?'));
        $hasil = [];
        $baris = $this->baris("
            SELECT ct.class_id, ct.user_id
            FROM roster_class_teachers ct
            JOIN roster_classes k ON k.id = ct.class_id
            JOIN roster_courses c ON c.id = k.course_id
            WHERE ct.class_id IN ({$tanda}) AND {$this->akses->lihatKelas()}
            ORDER BY ct.class_id, ct.user_id", $kelasIds);
        foreach ($baris as $b) {
            $hasil[(int) $b['class_id']][] = (string) $b['user_id'];
        }

        return $hasil;
    }

    /**
     * id mahasiswa AKTIF satu kelas, dengan syarat lihat yang sama.
     *
     * @return list<string>
     */
    public function mahasiswaAktif(int $kelasId): array
    {
        return array_map('strval', array_column($this->baris("
            SELECT e.user_id
            FROM roster_enrollments e
            JOIN roster_classes k ON k.id = e.class_id
            JOIN roster_courses c ON c.id = k.course_id
            WHERE e.class_id = ? AND e.status = 'AKTIF' AND {$this->akses->lihatKelas()}
            ORDER BY e.id", [$kelasId]), 'user_id'));
    }

    /**
     * Kelas-kelas pengguna ini sebagai mahasiswa (SPEC-LOKAUJIAN.md §3).
     *
     * @return list<array<string, mixed>>
     */
    public function kelasSaya(): array
    {
        return $this->baris("
            SELECT k.id AS kelas_id, k.name AS kelas_nama, c.code AS matkul_kode, c.name AS matkul_nama
            FROM roster_enrollments e
            JOIN roster_classes k ON k.id = e.class_id
            JOIN roster_courses c ON c.id = k.course_id
            JOIN roster_academic_years t ON t.id = k.academic_year_id
            WHERE {$this->akses->kelasMahasiswa()}
            ORDER BY c.code, k.name");
    }
}
