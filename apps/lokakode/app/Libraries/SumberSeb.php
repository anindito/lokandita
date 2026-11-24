<?php

declare(strict_types=1);

namespace App\Libraries;

use App\Policy\Akses;
use CodeIgniter\HTTP\RequestInterface;
use Lokandita\Auth\Identity;
use Lokandita\Auth\Seb\Sumber;

/**
 * Yang disediakan LokaKode kepada pemeriksaan SEB bersama (KONTRAK.md §8): mata kuliah dan
 * pendaftaran dari tabelnya sendiri, Redis-nya, tabel peristiwa integritas dan log auditnya.
 */
final class SumberSeb implements Sumber
{
    public function matkulMahasiswa(string $userId): array
    {
        $baris = db_connect()->query(
            "SELECT e.course_id
             FROM enrollments e
             JOIN academic_years ay ON ay.id = e.academic_year_id AND ay.status = 'active'
             WHERE e.user_id = ? AND e.status = 'AKTIF'",
            [$userId],
        )->getResultArray();

        return array_map('intval', array_column($baris, 'course_id'));
    }

    public function matkul(array $ids): array
    {
        if ($ids === []) {
            return [];
        }
        $baris = db_connect()->query('SELECT id, code, name FROM courses WHERE id IN (' . implode(',', array_map('intval', $ids)) . ') ORDER BY code')->getResultArray();

        return array_map(self::bentuk(...), $baris);
    }

    public function matkulDariKode(string $kode): ?array
    {
        $baris = db_connect()->query('SELECT id, code, name FROM courses WHERE code = ?', [$kode])->getRowArray();

        return $baris === null ? null : self::bentuk($baris);
    }

    public function matkulUntukPanel(Identity $siapa, int $matkulId): ?array
    {
        $baris = (new Akademik(new Akses($siapa)))->matkul($matkulId);

        return $baris === null ? null : self::bentuk($baris) + ['boleh_kelola' => (int) $baris['boleh_kelola'] === 1];
    }

    public function navigasi(Identity $siapa): string
    {
        return view('_nav', ['akses' => new Akses($siapa)]);
    }

    public function redis(): \Redis
    {
        return Antrean::redis();
    }

    public function catatPeristiwa(string $userId, string $jenis, array $detail, RequestInterface $request): void
    {
        db_connect()->table('integrity_events')->insert([
            'user_id' => $userId, 'kind' => $jenis, 'meeting_problem_id' => null,
            'detail' => json_encode($detail),
            'ip' => method_exists($request, 'getIPAddress') ? $request->getIPAddress() : null,
            'user_agent' => method_exists($request, 'getUserAgent') ? substr((string) $request->getUserAgent(), 0, 255) : null,
            'created_at' => Waktu::kini(),
        ]);
    }

    public function catatAudit(string $aksi, int $matkulId, array $detail): void
    {
        Audit::catat($aksi, 'course', $matkulId, $detail, $matkulId);
    }

    /**
     * @param array<string, mixed> $baris
     *
     * @return array{id: int, code: string, name: string}
     */
    private static function bentuk(array $baris): array
    {
        return ['id' => (int) $baris['id'], 'code' => (string) $baris['code'], 'name' => (string) $baris['name']];
    }
}
