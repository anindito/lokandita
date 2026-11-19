<?php

declare(strict_types=1);

namespace App\Libraries;

use App\Policy\Akses;
use CodeIgniter\Database\BaseConnection;

/**
 * Analitik untuk dosen (SPEC.md §12, §8.4): sebaran nilai, statistik per soal,
 * mahasiswa berisiko, kiriman dan kode sumber mahasiswa, perbandingan kelas, dan
 * rekap nilai untuk ekspor.
 *
 * Setiap kueri memuat syarat App\Policy\Akses: `lihatKelas()` untuk apa pun yang
 * per kelas (superadmin, pengampu, pengajar kelas itu), `kelolaMatkul()` untuk
 * apa pun yang lintas kelas (superadmin dan pengampu). Kelas atau mata kuliah di
 * luar hak pengguna karena itu tidak menghasilkan baris, bukan data orang lain.
 *
 * Hanya pendaftaran AKTIF yang masuk analitik (SPEC.md §5). Kiriman latihan
 * sesudah tutup tidak pernah dihitung; di daftar kiriman ia tampil dengan tandanya.
 * Tidak ada yang disimpan: semuanya dihitung dari keadaan sekarang, jadi pindah
 * kelas langsung tercermin tanpa hitung ulang.
 */
final class Analitik
{
    // Nilai pertemuan di bawah ini disebut "rendah" (keputusan pemilik, tahap 9b).
    public const NILAI_RENDAH = 50;

    // Berisiko bila nilainya rendah di sekian pertemuan atau lebih.
    public const RENDAH_BERULANG = 2;

    // Petunjuk kalibrasi (SPEC.md §8.4) baru diberikan bila sekian mahasiswa sudah mencoba.
    public const KALIBRASI_MIN = 5;

    // Persentase lulus di bawah ini: soal tampak lebih sulit daripada labelnya.
    // Hanya angka untuk MUDAH (30 %) yang berasal dari SPEC.md; lihat DECISIONS.md.
    public const TERLALU_SULIT = ['MUDAH_SEKALI' => 50, 'MUDAH' => 30, 'MUDAH_SEDANG' => 20, 'SEDANG' => 10];

    // Persentase lulus di atas ini pada soal SEDANG atau SULIT: tampak lebih mudah daripada labelnya.
    public const TERLALU_MUDAH = ['SEDANG' => 90, 'SULIT' => 90];

    private readonly BaseConnection $db;

    public function __construct(private readonly Akses $akses)
    {
        $this->db = db_connect();
    }

    // ---- hitungan murni ---------------------------------------------------------------

    /**
     * Rata-rata, median, simpangan baku (populasi: seluruh kelas, bukan sampel),
     * terkecil, terbesar, dan sebaran per sepuluh nilai (0–9, …, 80–89, 90–100).
     *
     * @param list<float> $nilai
     *
     * @return array{n: int, rata: ?float, median: ?float, simpangan: ?float, min: ?float, maks: ?float, sebaran: list<int>}
     */
    public static function statistik(array $nilai): array
    {
        $n       = count($nilai);
        $sebaran = array_fill(0, 10, 0);
        if ($n === 0) {
            return ['n' => 0, 'rata' => null, 'median' => null, 'simpangan' => null, 'min' => null, 'maks' => null, 'sebaran' => $sebaran];
        }
        $rata  = array_sum($nilai) / $n;
        $ragam = array_sum(array_map(static fn (float $x): float => ($x - $rata) ** 2, $nilai)) / $n;
        foreach ($nilai as $x) {
            $sebaran[min(9, max(0, (int) floor($x / 10)))]++;
        }

        return [
            'n' => $n, 'rata' => round($rata, 2), 'median' => round((float) self::median($nilai), 2), 'simpangan' => round(sqrt($ragam), 2),
            'min' => min($nilai), 'maks' => max($nilai), 'sebaran' => $sebaran,
        ];
    }

    /**
     * @param list<float|int> $nilai
     */
    public static function median(array $nilai): ?float
    {
        $n = count($nilai);
        if ($n === 0) {
            return null;
        }
        sort($nilai);
        $tengah = intdiv($n, 2);

        return $n % 2 === 1 ? (float) $nilai[$tengah] : ($nilai[$tengah - 1] + $nilai[$tengah]) / 2;
    }

    /**
     * Petunjuk kalibrasi untuk satu soal: 'sulit' (tampak lebih sulit daripada labelnya),
     * 'mudah' (tampak lebih mudah), atau null.
     */
    public static function petunjuk(string $tingkat, int $mencoba, ?float $persenLulus): ?string
    {
        if ($mencoba < self::KALIBRASI_MIN || $persenLulus === null) {
            return null;
        }
        if (isset(self::TERLALU_SULIT[$tingkat]) && $persenLulus < self::TERLALU_SULIT[$tingkat]) {
            return 'sulit';
        }

        return isset(self::TERLALU_MUDAH[$tingkat]) && $persenLulus > self::TERLALU_MUDAH[$tingkat] ? 'mudah' : null;
    }

    // ---- per kelas, satu pertemuan ----------------------------------------------------

    /**
     * Setiap mahasiswa AKTIF kelas itu dengan nilai pertemuannya (null bila belum
     * punya kiriman yang dinilai), urut nilai lalu id.
     *
     * @return list<array<string, mixed>>
     */
    public function nilaiKelas(int $kelasId, int $pertemuanId): array
    {
        return $this->db->query("
            SELECT e.user_id, ms.score, ms.total_points, ms.counted_points, ms.surplus, ms.submissions, ms.reached_at
            FROM enrollments e
            JOIN classes k ON k.id = e.class_id
            JOIN courses c ON c.id = k.course_id
            JOIN meetings m ON m.course_id = k.course_id AND m.id = ? AND m.enabled = 1
            LEFT JOIN meeting_scores ms ON ms.user_id = e.user_id AND ms.meeting_id = m.id
            WHERE k.id = ? AND e.status = 'AKTIF' AND {$this->akses->lihatKelas()}
            ORDER BY ms.score IS NULL, ms.score DESC, e.id", [$pertemuanId, $kelasId])->getResultArray();
    }

    /**
     * Statistik per soal pertemuan itu untuk mahasiswa AKTIF satu kelas.
     *
     * @return list<array<string, mixed>>
     */
    public function soalKelas(int $kelasId, int $pertemuanId): array
    {
        $baris = $this->db->query("
            SELECT ps.meeting_problem_id, ps.points, ps.best_score, ps.attempts, s.passed, s.total, s.duration_ms
            FROM enrollments e
            JOIN classes k ON k.id = e.class_id
            JOIN courses c ON c.id = k.course_id
            JOIN problem_scores ps ON ps.user_id = e.user_id AND ps.meeting_id = ?
            JOIN submissions s ON s.id = ps.best_submission_id
            WHERE k.id = ? AND e.status = 'AKTIF' AND {$this->akses->lihatKelas()}", [$pertemuanId, $kelasId])->getResultArray();
        $soal = $this->db->query("
            SELECT mp.id, p.slug, v.difficulty, v.translations
            FROM classes k
            JOIN courses c ON c.id = k.course_id
            JOIN meetings m ON m.course_id = k.course_id AND m.id = ?
            JOIN meeting_problems mp ON mp.meeting_id = m.id
            JOIN problems p ON p.id = mp.problem_id
            JOIN problem_versions v ON v.id = mp.problem_version_id
            WHERE k.id = ? AND {$this->akses->lihatKelas()}
            ORDER BY FIELD(v.difficulty, 'MUDAH_SEKALI', 'MUDAH', 'MUDAH_SEDANG', 'SEDANG', 'SULIT'), p.slug", [$pertemuanId, $kelasId])->getResultArray();

        return self::rangkumSoal($soal, $baris);
    }

    /**
     * Kalibrasi per soal untuk seluruh mata kuliah dalam satu tahun ajaran (pengampu, SPEC.md §8.4).
     *
     * @return list<array<string, mixed>>
     */
    public function soalMatkul(int $pertemuanId, int $tahunId): array
    {
        $baris = $this->db->query("
            SELECT ps.meeting_problem_id, ps.points, ps.best_score, ps.attempts, s.passed, s.total, s.duration_ms
            FROM meetings m
            JOIN courses c ON c.id = m.course_id
            JOIN classes k ON k.course_id = c.id AND k.academic_year_id = ?
            JOIN enrollments e ON e.class_id = k.id AND e.status = 'AKTIF'
            JOIN problem_scores ps ON ps.user_id = e.user_id AND ps.meeting_id = m.id
            JOIN submissions s ON s.id = ps.best_submission_id
            WHERE m.id = ? AND {$this->akses->kelolaMatkul()}", [$tahunId, $pertemuanId])->getResultArray();
        $soal = $this->db->query("
            SELECT mp.id, p.slug, v.difficulty, v.translations
            FROM meetings m
            JOIN courses c ON c.id = m.course_id
            JOIN meeting_problems mp ON mp.meeting_id = m.id
            JOIN problems p ON p.id = mp.problem_id
            JOIN problem_versions v ON v.id = mp.problem_version_id
            WHERE m.id = ? AND {$this->akses->kelolaMatkul()}
            ORDER BY FIELD(v.difficulty, 'MUDAH_SEKALI', 'MUDAH', 'MUDAH_SEDANG', 'SEDANG', 'SULIT'), p.slug", [$pertemuanId])->getResultArray();

        return self::rangkumSoal($soal, $baris);
    }

    /**
     * SPEC.md §8.4 per soal: persentase lulus (nilai penuh di antara yang mencoba),
     * rata-rata bagian tes yang lulus pada kiriman terbaik, rata-rata percobaan,
     * median waktu jalan kiriman terbaik, dan petunjuk bila labelnya tampak salah.
     *
     * @param list<array<string, mixed>> $soal  soal pertemuan
     * @param list<array<string, mixed>> $baris satu baris per mahasiswa per soal yang dicobanya
     *
     * @return list<array<string, mixed>>
     */
    private static function rangkumSoal(array $soal, array $baris): array
    {
        $per = [];
        foreach ($baris as $b) {
            $per[(int) $b['meeting_problem_id']][] = $b;
        }
        $hasil = [];
        foreach ($soal as $s) {
            $milik   = $per[(int) $s['id']] ?? [];
            $mencoba = count($milik);
            $lulus   = count(array_filter($milik, static fn (array $b): bool => (float) $b['best_score'] >= (float) $b['points']));
            $persen  = $mencoba === 0 ? null : round($lulus / $mencoba * 100, 1);
            $tes     = array_map(static fn (array $b): float => (int) $b['total'] > 0 ? (int) $b['passed'] / (int) $b['total'] * 100 : 0.0, $milik);
            $waktu   = array_values(array_filter(array_map(static fn (array $b) => $b['duration_ms'] === null ? null : (int) $b['duration_ms'], $milik), static fn ($w) => $w !== null));
            $hasil[] = [
                'id'        => (int) $s['id'],
                'judul'     => Ruang::terjemahan(json_decode((string) $s['translations'], true) ?? [])['title'] ?? $s['slug'],
                'tingkat'   => $s['difficulty'],
                'mencoba'   => $mencoba,
                'lulus'     => $lulus,
                'persen'    => $persen,
                'rataTes'   => $mencoba === 0 ? null : round(array_sum($tes) / $mencoba, 1),
                'rataCoba'  => $mencoba === 0 ? null : round(array_sum(array_map(static fn (array $b): int => (int) $b['attempts'], $milik)) / $mencoba, 1),
                'medianMs'  => self::median($waktu),
                'petunjuk'  => self::petunjuk($s['difficulty'], $mencoba, $persen),
            ];
        }

        return $hasil;
    }

    // ---- per kelas, semua pertemuan ---------------------------------------------------

    /**
     * Pertemuan latihan yang sudah terbit di mata kuliah kelas itu, dengan jendela kelasnya.
     *
     * @return list<array<string, mixed>>
     */
    public function pertemuanKelas(int $kelasId): array
    {
        return $this->db->query("
            SELECT m.id, m.number, m.title, w.open_at, w.close_at, w.override
            FROM classes k
            JOIN courses c ON c.id = k.course_id
            JOIN meetings m ON m.course_id = k.course_id AND m.enabled = 1 AND m.published_at IS NOT NULL
            LEFT JOIN meeting_class_windows w ON w.meeting_id = m.id AND w.class_id = k.id
            WHERE k.id = ? AND {$this->akses->lihatKelas()}
            ORDER BY m.number", [$kelasId])->getResultArray();
    }

    /**
     * Rekap nilai kelas: satu baris per mahasiswa, nilai tiap pertemuan.
     * $status: status pendaftaran yang ikut (analitik: AKTIF saja; ekspor: AKTIF dan LULUS).
     *
     * @param list<string> $status
     *
     * @return array<string, array{user_id: string, status: string, kelas: string, nilai: array<int, array<string, mixed>>}> dikunci user_id
     */
    public function rekapKelas(int $kelasId, array $status = ['AKTIF']): array
    {
        $baris = $this->db->query("
            SELECT e.user_id, e.status, k.name AS kelas, ms.meeting_id, ms.score, ms.total_points, ms.surplus, ms.submissions
            FROM enrollments e
            JOIN classes k ON k.id = e.class_id
            JOIN courses c ON c.id = k.course_id
            LEFT JOIN (meeting_scores ms JOIN meetings m ON m.id = ms.meeting_id AND m.enabled = 1)
                   ON ms.user_id = e.user_id AND m.course_id = k.course_id
            WHERE k.id = ? AND e.status IN ? AND {$this->akses->lihatKelas()}
            ORDER BY e.id", [$kelasId, $status])->getResultArray();

        return self::susunRekap($baris);
    }

    /**
     * Rekap nilai seluruh kelas sebuah mata kuliah dalam satu tahun ajaran (pengampu dan superadmin).
     *
     * @param list<string> $status
     *
     * @return array<string, array<string, mixed>>
     */
    public function rekapMatkul(int $matkulId, int $tahunId, array $status = ['AKTIF']): array
    {
        $baris = $this->db->query("
            SELECT e.user_id, e.status, k.name AS kelas, ms.meeting_id, ms.score, ms.total_points, ms.surplus, ms.submissions
            FROM enrollments e
            JOIN classes k ON k.id = e.class_id
            JOIN courses c ON c.id = k.course_id
            LEFT JOIN (meeting_scores ms JOIN meetings m ON m.id = ms.meeting_id AND m.enabled = 1)
                   ON ms.user_id = e.user_id AND m.course_id = k.course_id
            WHERE c.id = ? AND k.academic_year_id = ? AND e.status IN ? AND {$this->akses->kelolaMatkul()}
            ORDER BY k.name, e.id", [$matkulId, $tahunId, $status])->getResultArray();

        return self::susunRekap($baris);
    }

    /**
     * @param list<array<string, mixed>> $baris
     *
     * @return array<string, array<string, mixed>>
     */
    private static function susunRekap(array $baris): array
    {
        $rekap = [];
        foreach ($baris as $b) {
            $u = (string) $b['user_id'];
            $rekap[$u] ??= ['user_id' => $u, 'status' => $b['status'], 'kelas' => $b['kelas'], 'nilai' => []];
            if ($b['meeting_id'] !== null) {
                $rekap[$u]['nilai'][(int) $b['meeting_id']] = [
                    'nilai' => (float) $b['score'], 'total' => (float) $b['total_points'],
                    'surplus' => (float) $b['surplus'], 'kiriman' => (int) $b['submissions'],
                ];
            }
        }

        return $rekap;
    }

    /**
     * Mahasiswa berisiko di sebuah kelas (SPEC.md §12), dari pertemuan yang jendelanya
     * SUDAH TUTUP untuk kelas itu: tidak mengirim apa pun di salah satunya, atau nilainya
     * di bawah NILAI_RENDAH di RENDAH_BERULANG pertemuan atau lebih.
     *
     * @param list<array<string, mixed>>         $pertemuan dari pertemuanKelas()
     * @param array<string, array<string, mixed>> $rekap     dari rekapKelas() (AKTIF)
     *
     * @return list<array{user_id: string, tanpa: list<int>, rendah: list<int>}> nomor pertemuan
     */
    public static function berisiko(array $pertemuan, array $rekap, ?string $kini = null): array
    {
        $tutup = array_values(array_filter($pertemuan, static fn (array $p): bool => Latihan::keadaan($p, $kini) === 'tutup'));
        $hasil = [];
        foreach ($rekap as $r) {
            $tanpa  = [];
            $rendah = [];
            foreach ($tutup as $p) {
                $n = $r['nilai'][(int) $p['id']] ?? null;
                if ($n === null) {
                    $tanpa[] = (int) $p['number'];
                } elseif ($n['nilai'] < self::NILAI_RENDAH) {
                    $rendah[] = (int) $p['number'];
                }
            }
            if ($tanpa !== [] || count($rendah) >= self::RENDAH_BERULANG) {
                $hasil[] = ['user_id' => $r['user_id'], 'tanpa' => $tanpa, 'rendah' => $rendah];
            }
        }
        usort($hasil, static fn (array $a, array $b): int => [count($b['tanpa']), count($b['rendah'])] <=> [count($a['tanpa']), count($a['rendah'])]);

        return $hasil;
    }

    // ---- kiriman mahasiswa ------------------------------------------------------------

    /**
     * Kiriman mahasiswa kelas itu pada sebuah pertemuan, terbaru lebih dulu, tanpa kode
     * sumber. Mahasiswa yang sudah pindah ke kelas lain tidak ikut: kirimannya ikut
     * dia ke kelas barunya.
     *
     * @return list<array<string, mixed>>
     */
    public function kirimanKelas(int $kelasId, int $pertemuanId, ?string $userId = null, ?int $mpId = null, int $batas = 300): array
    {
        $where = '';
        $ikat  = [$pertemuanId, $kelasId];
        if ($userId !== null) {
            $where .= ' AND s.user_id = ?';
            $ikat[] = $userId;
        }
        if ($mpId !== null) {
            $where .= ' AND s.meeting_problem_id = ?';
            $ikat[] = $mpId;
        }
        $ikat[] = $batas;

        return $this->db->query("
            SELECT s.id, s.user_id, s.meeting_problem_id, s.status, s.verdict, s.passed, s.total, s.score, s.late, s.practice,
                   s.created_at, p.slug, v.difficulty, v.translations
            FROM enrollments e
            JOIN classes k ON k.id = e.class_id
            JOIN courses c ON c.id = k.course_id
            JOIN meeting_problems mp ON mp.meeting_id = ?
            JOIN meetings m ON m.id = mp.meeting_id AND m.course_id = k.course_id
            JOIN problems p ON p.id = mp.problem_id
            JOIN submissions s ON s.meeting_problem_id = mp.id AND s.user_id = e.user_id
            JOIN problem_versions v ON v.id = s.problem_version_id
            WHERE k.id = ? AND e.status IN ('AKTIF', 'LULUS') AND {$this->akses->lihatKelas()} {$where}
            ORDER BY s.id DESC LIMIT ?", $ikat)->getResultArray();
    }

    /**
     * Satu kiriman mahasiswa kelas itu, dengan kode sumber, log kompilasi dan status
     * per tes (SPEC.md §12). Tidak ada isi tes: tabel submissions tidak menyimpannya.
     *
     * @return array<string, mixed>|null null bila tidak ada, bukan milik mahasiswa kelas itu,
     *                                   atau kelasnya di luar hak pengguna
     */
    public function kiriman(int $kelasId, int $id): ?array
    {
        return $this->db->query("
            SELECT s.id, s.user_id, s.meeting_problem_id, s.status, s.verdict, s.passed, s.total, s.score, s.late, s.late_factor,
                   s.practice, s.duration_ms, s.compile_log, s.tests, s.missing_concepts, s.source, s.language, s.created_at, s.judged_at,
                   p.slug, v.difficulty, v.translations, m.id AS pertemuan_id, m.number, m.title
            FROM submissions s
            JOIN meeting_problems mp ON mp.id = s.meeting_problem_id
            JOIN meetings m ON m.id = mp.meeting_id
            JOIN problems p ON p.id = mp.problem_id
            JOIN problem_versions v ON v.id = s.problem_version_id
            JOIN enrollments e ON e.user_id = s.user_id AND e.status IN ('AKTIF', 'LULUS')
            JOIN classes k ON k.id = e.class_id AND k.course_id = m.course_id
            JOIN courses c ON c.id = k.course_id
            WHERE s.id = ? AND k.id = ? AND {$this->akses->lihatKelas()}", [$id, $kelasId])->getRowArray();
    }

    // ---- lintas kelas (pengampu) ------------------------------------------------------

    /**
     * Perbandingan antarkelas sebuah mata kuliah dalam satu tahun ajaran: per pertemuan
     * terbit, per kelas — jumlah mahasiswa aktif, yang sudah mengirim, rata-rata dan median.
     * Seperti sebaran nilai, mahasiswa aktif tanpa kiriman dihitung 0.
     *
     * @return array{kelas: list<array<string, mixed>>, pertemuan: list<array<string, mixed>>, sel: array<int, array<int, array<string, mixed>>>}
     */
    public function banding(int $matkulId, int $tahunId): array
    {
        $kelas = $this->db->query("
            SELECT k.id, k.name, (SELECT COUNT(*) FROM enrollments e WHERE e.class_id = k.id AND e.status = 'AKTIF') AS aktif
            FROM classes k JOIN courses c ON c.id = k.course_id
            WHERE c.id = ? AND k.academic_year_id = ? AND k.archived_at IS NULL AND {$this->akses->kelolaMatkul()}
            ORDER BY k.name", [$matkulId, $tahunId])->getResultArray();
        $pertemuan = $this->db->query("
            SELECT m.id, m.number, m.title
            FROM meetings m JOIN courses c ON c.id = m.course_id
            WHERE c.id = ? AND m.enabled = 1 AND m.published_at IS NOT NULL AND {$this->akses->kelolaMatkul()}
            ORDER BY m.number", [$matkulId])->getResultArray();
        $nilai = $this->db->query("
            SELECT k.id AS kelas_id, ms.meeting_id, ms.score
            FROM classes k
            JOIN courses c ON c.id = k.course_id
            JOIN enrollments e ON e.class_id = k.id AND e.status = 'AKTIF'
            JOIN meeting_scores ms ON ms.user_id = e.user_id
            JOIN meetings m ON m.id = ms.meeting_id AND m.course_id = c.id
            WHERE c.id = ? AND k.academic_year_id = ? AND {$this->akses->kelolaMatkul()}", [$matkulId, $tahunId])->getResultArray();

        $per = [];
        foreach ($nilai as $n) {
            $per[(int) $n['meeting_id']][(int) $n['kelas_id']][] = (float) $n['score'];
        }
        $sel = [];
        foreach ($pertemuan as $p) {
            foreach ($kelas as $k) {
                $ada   = $per[(int) $p['id']][(int) $k['id']] ?? [];
                $semua = array_merge($ada, array_fill(0, max(0, (int) $k['aktif'] - count($ada)), 0.0));
                $sel[(int) $p['id']][(int) $k['id']] = ['mengirim' => count($ada)] + self::statistik($semua);
            }
        }

        return ['kelas' => $kelas, 'pertemuan' => $pertemuan, 'sel' => $sel];
    }
}
