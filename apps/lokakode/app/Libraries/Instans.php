<?php

declare(strict_types=1);

namespace App\Libraries;

use App\Libraries\Soal\Benih;

/**
 * Instans soal per mahasiswa (SPEC.md §8.2): meminta judge membuatnya, dan
 * menghitung kemajuan pra-pembangkitan sebuah kelas.
 *
 * Instansnya sendiri ditulis oleh judge worker (KONTRAK.md §6). Di sini hanya
 * pekerjaan GENERATE yang dibuat: satu per mahasiswa per soal, dengan kunci
 * unik `gen:<meeting_problem_id>:<user_id>`, sehingga permintaan yang sama
 * berulang kali tetap satu pekerjaan.
 *
 * Pemanggil sudah memeriksa hak (pengajar atas kelasnya, mahasiswa atas
 * soalnya); fungsi di sini tidak dipanggil dengan id mentah dari URL.
 */
final class Instans
{
    // Dipakai bila belum ada pekerjaan GENERATE yang selesai untuk diukur.
    private const DETIK_BAWAAN = 3.0;

    public static function kunci(int $mpId, string $userId): string
    {
        return 'gen:' . $mpId . ':' . $userId;
    }

    /**
     * Memastikan ada pekerjaan GENERATE untuk mahasiswa dan soal ini. Aman dipanggil berulang.
     *
     *   - belum ada pekerjaan      -> dibuat di antrean $prioritas;
     *   - pekerjaannya FAILED      -> diantrekan lagi (dengan versi soal yang sekarang);
     *   - menunggu di antrean low dan sekarang diminta `normal` -> didahulukan.
     *
     * @param array<string, mixed> $mp baris meeting_problems (id, problem_version_id)
     */
    public static function minta(array $mp, string $userId, ?string $pembuat, string $prioritas): string
    {
        $payload = [
            'problem_version_id' => (int) $mp['problem_version_id'],
            'user_id'            => $userId,
            'meeting_problem_id' => (int) $mp['id'],
            'seed'               => Benih::untuk($userId, (int) $mp['problem_version_id']),
        ];
        $kunci = self::kunci((int) $mp['id'], $userId);
        $ada   = Antrean::denganKunci($kunci);
        if ($ada === null) {
            return Antrean::kirim('GENERATE', $payload, $pembuat, $prioritas, $kunci);
        }
        if ($ada['status'] === 'FAILED') {
            Antrean::ulangi($ada['id'], $payload, $prioritas);
        } elseif ($prioritas === 'normal') {
            Antrean::dahulukan($ada['id']);
        }

        return (string) $ada['id'];
    }

    /**
     * Pra-pembangkitan (SPEC.md §8.2): instans semua mahasiswa AKTIF kelas itu
     * untuk semua soal pertemuan itu, di antrean prioritas rendah.
     *
     * @return int jumlah pekerjaan yang diminta (instans yang sudah ada tidak dihitung)
     */
    public static function siapkan(int $pertemuanId, int $kelasId, string $pembuat): int
    {
        $kurang = db_connect()->query('
            SELECT mp.id, mp.problem_version_id, e.user_id
            FROM meeting_problems mp
            JOIN enrollments e ON e.class_id = ? AND e.status = \'AKTIF\'
            LEFT JOIN problem_instances i ON i.meeting_problem_id = mp.id AND i.user_id = e.user_id
            WHERE mp.meeting_id = ? AND i.id IS NULL
            ORDER BY e.user_id, mp.id', [$kelasId, $pertemuanId])->getResultArray();
        foreach ($kurang as $b) {
            self::minta($b, (string) $b['user_id'], $pembuat, 'low');
        }

        return count($kurang);
    }

    /**
     * @return array{mahasiswa: int, soal: int, harapan: int, siap: int, antre: int, gagal: int, perkiraan: int|null, worker: bool}
     */
    public static function kemajuan(int $pertemuanId, int $kelasId): array
    {
        $db   = db_connect();
        $satu = static fn (string $sql, array $ikat): int => (int) ($db->query($sql, $ikat)->getRowArray()['n'] ?? 0);

        $mahasiswa = $satu('SELECT COUNT(*) AS n FROM enrollments WHERE class_id = ? AND status = \'AKTIF\'', [$kelasId]);
        $soal      = $satu('SELECT COUNT(*) AS n FROM meeting_problems WHERE meeting_id = ?', [$pertemuanId]);
        $siap      = $satu('
            SELECT COUNT(*) AS n FROM problem_instances i
            JOIN meeting_problems mp ON mp.id = i.meeting_problem_id AND mp.meeting_id = ?
            JOIN enrollments e ON e.user_id = i.user_id AND e.class_id = ? AND e.status = \'AKTIF\'', [$pertemuanId, $kelasId]);
        $pekerjaan = $db->query('
            SELECT j.status, COUNT(*) AS n
            FROM meeting_problems mp
            JOIN enrollments e ON e.class_id = ? AND e.status = \'AKTIF\'
            JOIN judge_jobs j ON j.idempotency_key = CONCAT(\'gen:\', mp.id, \':\', e.user_id)
            WHERE mp.meeting_id = ?
            GROUP BY j.status', [$kelasId, $pertemuanId])->getResultArray();
        $per   = array_column($pekerjaan, 'n', 'status');
        $antre = (int) ($per['QUEUED'] ?? 0) + (int) ($per['RUNNING'] ?? 0);

        return [
            'mahasiswa' => $mahasiswa,
            'soal'      => $soal,
            'harapan'   => $mahasiswa * $soal,
            'siap'      => $siap,
            'antre'     => $antre,
            'gagal'     => (int) ($per['FAILED'] ?? 0),
            'perkiraan' => $antre > 0 ? self::perkiraan($antre) : null,
            'worker'    => self::workerHidup(),
        ];
    }

    public static function workerHidup(): bool
    {
        try {
            return Antrean::denyut() !== null;
        } catch (\RedisException) {
            return false;
        }
    }

    /**
     * Perkiraan jujur lama menunggu $jumlah pekerjaan GENERATE, dalam detik:
     * rata-rata lama 50 pekerjaan GENERATE terakhir yang selesai, dibagi jumlah
     * pekerjaan yang dikerjakan worker sekaligus. null bila worker tidak berjalan.
     */
    public static function perkiraan(int $jumlah): ?int
    {
        try {
            $denyut = Antrean::denyut();
        } catch (\RedisException) {
            $denyut = null;
        }
        if ($denyut === null) {
            return null;
        }
        $baris = db_connect()->query('
            SELECT AVG(lama) AS rata, COUNT(*) AS n FROM (
                SELECT GREATEST(TIMESTAMPDIFF(SECOND, started_at, finished_at), 1) AS lama
                FROM judge_jobs WHERE kind = \'GENERATE\' AND status = \'DONE\' AND started_at IS NOT NULL
                    AND JSON_EXTRACT(result, \'$.existed\') = false
                ORDER BY finished_at DESC LIMIT 50) t')->getRowArray();
        $rata = (int) ($baris['n'] ?? 0) > 0 ? (float) $baris['rata'] : self::DETIK_BAWAAN;

        return (int) ceil($jumlah * $rata / max(1, (int) ($denyut['konkurensi'] ?? 1)));
    }
}
