<?php

declare(strict_types=1);

namespace App\Libraries;

use CodeIgniter\Database\BaseConnection;

/**
 * Penilaian (SPEC.md §7). Satu-satunya tempat nilai dihitung.
 *
 * Worker hanya melaporkan hasil SUBMIT di judge_jobs (KONTRAK.md §6). Di sini
 * hasil itu disalin ke `submissions`, lalu nilai soal dan nilai pertemuan
 * mahasiswa itu dihitung ULANG SELURUHNYA dari kirimannya:
 *
 *   nilai kiriman   = poin × (tes tersembunyi lulus / jumlah tes), 2 desimal
 *                     galat kompilasi 0; CONCEPT_NOT_MET × 0,5
 *   nilai soal      = yang terbaik dari semua kiriman; jumlah percobaan dicatat
 *   poin terhitung  = jumlah nilai soal, hanya 4 soal MUDAH_SEKALI terbaik yang dihitung
 *   nilai pertemuan = min(poin terhitung / target, 1) × 100
 *   surplus         = seluruh poin − min(poin terhitung, target)
 *
 * problem_scores dan meeting_scores adalah tabel turunan: boleh dikosongkan dan
 * dihitung ulang kapan saja dengan hasil yang sama.
 *
 * Fungsi di sini tidak memeriksa hak; pemanggilnya (pengendali mahasiswa,
 * lewat App\Libraries\Ruang) sudah melakukannya.
 */
final class Penilaian
{
    /**
     * Nilai satu kiriman dari hasil judge.
     *
     * @param array<string, mixed> $hasil judge_jobs.result sebuah SUBMIT
     */
    public static function nilaiKiriman(int $poin, array $hasil, ?float $faktorTelat = null): float
    {
        $total = (int) ($hasil['total'] ?? 0);
        $lulus = (int) ($hasil['passed'] ?? 0);
        if (($hasil['verdict'] ?? '') === 'CE' || $total <= 0) {
            return 0.0;
        }
        $nilai = $poin * min($lulus, $total) / $total;
        if ($hasil['verdict'] === 'CONCEPT_NOT_MET') {
            $nilai *= 0.5;
        }
        // Kiriman terlambat di bawah kebijakan `multiplier` (SPEC.md §7).
        if ($faktorTelat !== null) {
            $nilai *= $faktorTelat;
        }

        return round($nilai, 2);
    }

    /**
     * Nilai pertemuan dari nilai terbaik tiap soal.
     *
     * @param list<array{tingkat: string, nilai: float}> $soal
     *
     * @return array{total: float, terhitung: float, nilai: float, surplus: float, mudahSekali: int}
     */
    public static function nilaiPertemuan(array $soal, int $target, bool $ujian = false): array
    {
        // Ujian (SPEC.md §7): semua soal dihitung, tanpa kuota Mudah sekali dan tanpa surplus.
        if ($ujian) {
            $total = round(array_sum(array_column($soal, 'nilai')), 2);

            return ['total' => $total, 'terhitung' => $total, 'nilai' => round(min($total / $target, 1) * 100, 2), 'surplus' => 0.0, 'mudahSekali' => 0];
        }
        $total       = 0.0;
        $terhitung   = 0.0;
        $mudahSekali = [];
        foreach ($soal as $s) {
            $total += $s['nilai'];
            if ($s['tingkat'] === 'MUDAH_SEKALI') {
                $mudahSekali[] = $s['nilai'];
            } else {
                $terhitung += $s['nilai'];
            }
        }
        // Empat yang terbaik, dipilih ulang setiap kali — bukan menurut urutan pengerjaan.
        rsort($mudahSekali);
        $dipakai    = array_slice($mudahSekali, 0, Latihan::KUOTA_MUDAH_SEKALI);
        $terhitung += array_sum($dipakai);
        $total      = round($total, 2);
        $terhitung  = round($terhitung, 2);

        return [
            'total'       => $total,
            'terhitung'   => $terhitung,
            'nilai'       => round(min($terhitung / $target, 1) * 100, 2),
            'surplus'     => round($total - min($terhitung, $target), 2),
            'mudahSekali' => count(array_filter($dipakai, static fn ($n) => $n > 0)),
        ];
    }

    /**
     * Menyelesaikan kiriman yang pekerjaan judge-nya sudah berakhir. Aman
     * dipanggil berulang dan dari beberapa permintaan sekaligus: baris kiriman
     * dikunci, dan hanya yang masih MENUNGGU yang diubah.
     *
     * $userId dan $jobId mempersempit pencarian; tanpa keduanya semua kiriman
     * yang tertinggal diselesaikan.
     *
     * @return int jumlah kiriman yang diselesaikan
     */
    public static function selesaikan(?string $userId = null, ?string $jobId = null): int
    {
        $db    = db_connect();
        $where = '';
        $ikat  = [];
        if ($userId !== null) {
            $where .= ' AND s.user_id = ?';
            $ikat[] = $userId;
        }
        if ($jobId !== null) {
            $where .= ' AND s.job_id = ?';
            $ikat[] = $jobId;
        }
        $siap = $db->query("
            SELECT s.id FROM submissions s JOIN judge_jobs j ON j.id = s.job_id
            WHERE s.status = 'MENUNGGU' AND j.status IN ('DONE', 'FAILED') {$where}
            ORDER BY s.id LIMIT 200", $ikat)->getResultArray();

        $jumlah = 0;
        foreach ($siap as $b) {
            $jumlah += self::selesaikanSatu($db, (int) $b['id']) ? 1 : 0;
        }

        return $jumlah;
    }

    private static function selesaikanSatu(BaseConnection $db, int $id): bool
    {
        $db->transStart();
        $k = $db->query("
            SELECT s.id, s.user_id, s.meeting_problem_id, s.late, s.late_factor, mp.meeting_id, v.difficulty,
                   j.status AS job_status, j.result, j.finished_at
            FROM submissions s
            JOIN judge_jobs j ON j.id = s.job_id
            JOIN meeting_problems mp ON mp.id = s.meeting_problem_id
            JOIN problem_versions v ON v.id = s.problem_version_id
            WHERE s.id = ? AND s.status = 'MENUNGGU' AND j.status IN ('DONE', 'FAILED')
            FOR UPDATE", [$id])->getRowArray();
        if ($k === null) {
            $db->transComplete();

            return false;
        }
        $hasil = json_decode((string) $k['result'], true);
        $waktu = $k['finished_at'] ?? Waktu::kini();

        if ($k['job_status'] === 'FAILED' || ! is_array($hasil) || ! isset($hasil['verdict'])) {
            // Kegagalan judge bukan percobaan: tidak dinilai dan tidak dihitung (SPEC.md §10).
            $db->table('submissions')->where('id', $id)->update(['status' => 'GAGAL', 'verdict' => 'FAILED', 'judged_at' => $waktu]);
            $db->transComplete();

            return true;
        }

        $tes = $hasil['tests'] ?? [];
        $db->table('submissions')->where('id', $id)->update([
            'status'           => 'DINILAI',
            'verdict'          => substr((string) $hasil['verdict'], 0, 20),
            'passed'           => (int) ($hasil['passed'] ?? 0),
            'total'            => (int) ($hasil['total'] ?? 0),
            'score'            => self::nilaiKiriman(Latihan::POIN[$k['difficulty']], $hasil, (int) $k['late'] === 1 ? (float) $k['late_factor'] : null),
            'duration_ms'      => $tes === [] ? null : max(array_map(static fn ($t) => (int) ($t['duration_ms'] ?? 0), $tes)),
            'compile_log'      => mb_strcut((string) ($hasil['compile_log'] ?? ''), 0, 64 * 1024),
            'tests'            => json_encode($tes),
            'missing_concepts' => json_encode($hasil['missing'] ?? []),
            'judged_at'        => $waktu,
        ]);
        self::hitungUlang($db, (string) $k['user_id'], (int) $k['meeting_id']);
        $db->transComplete();
        // Papan peringkat disegarkan setiap kali sebuah kiriman selesai dinilai (SPEC.md §12).
        Papan::lupakan((int) $k['meeting_id']);

        return true;
    }

    /**
     * Nilai semua soal dan nilai pertemuan satu mahasiswa, dihitung ulang dari
     * kirimannya yang sudah DINILAI. Dipanggil di dalam transaksi.
     */
    public static function hitungUlang(BaseConnection $db, string $userId, int $pertemuanId): void
    {
        $p      = $db->query('SELECT target_points, kind FROM meetings WHERE id = ?', [$pertemuanId])->getRowArray();
        $target = (int) ($p['target_points'] ?? 0);
        if ($target < 1) {
            return;
        }
        $ujian = Latihan::ujian($p);
        $kiriman = $db->query("
            SELECT s.id, s.meeting_problem_id, s.score, s.judged_at, v.difficulty
            FROM submissions s
            JOIN meeting_problems mp ON mp.id = s.meeting_problem_id AND mp.meeting_id = ?
            JOIN problem_versions v ON v.id = s.problem_version_id
            WHERE s.user_id = ? AND s.status = 'DINILAI' AND s.practice = 0
            ORDER BY s.judged_at, s.id", [$pertemuanId, $userId])->getResultArray();

        // Kiriman diputar ulang menurut urutan penilaiannya: dari situ didapat nilai
        // terbaik tiap soal, kapan tercapai, dan kapan nilai pertemuan yang sekarang tercapai.
        $soal         = [];
        $nilaiTerakhir = null;
        $tercapai     = null;
        foreach ($kiriman as $k) {
            $mp    = (int) $k['meeting_problem_id'];
            $nilai = (float) $k['score'];
            if (! isset($soal[$mp])) {
                $soal[$mp] = ['tingkat' => $k['difficulty'], 'nilai' => $nilai, 'kiriman' => (int) $k['id'], 'pada' => $k['judged_at'], 'percobaan' => 0];
            } elseif ($nilai > $soal[$mp]['nilai']) {
                $soal[$mp] = ['nilai' => $nilai, 'kiriman' => (int) $k['id'], 'pada' => $k['judged_at']] + $soal[$mp];
            }
            $soal[$mp]['percobaan']++;
            $kini = self::nilaiPertemuan(array_values($soal), $target, $ujian)['nilai'];
            if ($kini !== $nilaiTerakhir) {
                $nilaiTerakhir = $kini;
                $tercapai      = $k['judged_at'];
            }
        }

        $sekarang = Waktu::kini();
        $db->query('DELETE FROM problem_scores WHERE user_id = ? AND meeting_id = ?', [$userId, $pertemuanId]);
        foreach ($soal as $mp => $s) {
            $db->table('problem_scores')->insert([
                'user_id' => $userId, 'meeting_problem_id' => $mp, 'meeting_id' => $pertemuanId,
                'difficulty' => $s['tingkat'], 'points' => Latihan::POIN[$s['tingkat']], 'best_score' => $s['nilai'],
                'best_submission_id' => $s['kiriman'], 'attempts' => $s['percobaan'], 'reached_at' => $s['pada'], 'updated_at' => $sekarang,
            ]);
        }

        $db->query('DELETE FROM meeting_scores WHERE user_id = ? AND meeting_id = ?', [$userId, $pertemuanId]);
        if ($soal === []) {
            return;
        }
        $n = self::nilaiPertemuan(array_values($soal), $target, $ujian);
        $db->table('meeting_scores')->insert([
            'user_id' => $userId, 'meeting_id' => $pertemuanId, 'total_points' => $n['total'], 'counted_points' => $n['terhitung'],
            'target_points' => $target, 'score' => $n['nilai'], 'surplus' => $n['surplus'], 'very_easy_counted' => $n['mudahSekali'],
            'submissions' => count($kiriman), 'reached_at' => $tercapai, 'updated_at' => $sekarang,
        ]);
    }

    /**
     * Semua mahasiswa sebuah pertemuan, misalnya sesudah target poinnya diubah.
     */
    public static function hitungUlangPertemuan(int $pertemuanId): int
    {
        $db    = db_connect();
        $orang = $db->query("
            SELECT DISTINCT s.user_id FROM submissions s
            JOIN meeting_problems mp ON mp.id = s.meeting_problem_id AND mp.meeting_id = ?
            WHERE s.status = 'DINILAI' AND s.practice = 0", [$pertemuanId])->getResultArray();
        foreach ($orang as $o) {
            $db->transStart();
            self::hitungUlang($db, (string) $o['user_id'], $pertemuanId);
            $db->transComplete();
        }
        Papan::lupakan($pertemuanId);

        return count($orang);
    }
}
