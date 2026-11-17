<?php

declare(strict_types=1);

namespace App\Libraries;

use App\Policy\Akses;
use CodeIgniter\Database\BaseConnection;

/**
 * Ruang latihan mahasiswa (SPEC.md §11): pertemuan, soal, kiriman dan draf
 * MILIKNYA SENDIRI. Setiap kueri berangkat dari pendaftaran pengguna itu
 * (App\Policy\Akses::pendaftaranSendiri) dan menuntut semua ini sekaligus:
 *
 *   - pendaftarannya AKTIF atau LULUS;
 *   - pertemuannya dipakai untuk latihan dan sudah diterbitkan;
 *   - kelasnya punya jendela untuk pertemuan itu.
 *
 * Soal pertemuan lain, kelas lain atau mahasiswa lain karena itu tidak pernah
 * terbaca: fungsinya mengembalikan null, dan pengendali menjawab 404.
 *
 * Apa yang boleh dilakukan ditentukan `mode`:
 *   tunggu  jendela belum terbuka: hanya judul dan waktu bukanya
 *   kerja   jendela terbuka dan pendaftaran AKTIF: Run dan Submit
 *   baca    terbuka tetapi sudah LULUS, atau sudah tutup dengan review_only:
 *           soal dan kiriman sendiri terbaca, tidak bisa Run atau Submit
 *   nilai   sudah tutup dengan hidden: hanya nilainya
 */
final class Ruang
{
    private const DARI = '
        FROM enrollments e
        JOIN classes k ON k.id = e.class_id
        JOIN courses c ON c.id = k.course_id
        JOIN academic_years t ON t.id = k.academic_year_id
        JOIN meetings m ON m.course_id = k.course_id AND m.enabled = 1 AND m.published_at IS NOT NULL
        JOIN meeting_class_windows w ON w.meeting_id = m.id AND w.class_id = k.id';

    private const KOLOM = 'm.id AS pertemuan_id, m.number, m.title, m.language, m.target_points, m.starter_template,
        m.compiler_flags, m.autocomplete, c.code AS matkul_kode, c.name AS matkul_nama, k.id AS kelas_id, k.name AS kelas_nama,
        e.status AS status_daftar, t.status AS tahun_status, w.open_at, w.close_at, w.override, w.post_close_visibility';

    // Pendaftaran di tahun ajaran aktif didahulukan bila mahasiswa terdaftar di mata kuliah yang sama dua kali (dua tahun).
    private const URUT = "(t.status = 'active') DESC, e.id DESC";

    private readonly BaseConnection $db;

    private readonly string $syarat;

    public function __construct(private readonly Akses $akses)
    {
        $this->db     = db_connect();
        $this->syarat = $akses->pendaftaranSendiri() . " AND e.status IN ('AKTIF', 'LULUS')";
    }

    /**
     * @param array<string, mixed> $baris baris berisi kolom jendela, status_daftar, tahun_status
     */
    public static function mode(array $baris): string
    {
        $keadaan = Latihan::keadaan($baris);
        if ($keadaan === 'belum' || $keadaan === 'terjadwal') {
            return 'tunggu';
        }
        // Tahun ajaran yang sudah diarsipkan tidak menerima kiriman, apa pun keadaan jendelanya.
        if ($keadaan === 'buka' && $baris['tahun_status'] === 'active') {
            return $baris['status_daftar'] === 'AKTIF' ? 'kerja' : 'baca';
        }

        return $baris['post_close_visibility'] === 'review_only' || $keadaan === 'buka' ? 'baca' : 'nilai';
    }

    /**
     * Pertemuan yang terlihat oleh mahasiswa ini, dengan nilainya.
     *
     * @return list<array<string, mixed>>
     */
    public function daftarPertemuan(): array
    {
        $baris = $this->db->query('
            SELECT ' . self::KOLOM . ', ms.score, ms.counted_points, ms.total_points
            ' . self::DARI . "
            LEFT JOIN meeting_scores ms ON ms.meeting_id = m.id AND ms.user_id = e.user_id
            WHERE {$this->syarat}
            ORDER BY " . self::URUT . ', c.code, m.number')->getResultArray();
        $hasil = [];
        foreach ($baris as $b) {
            $hasil[(int) $b['pertemuan_id']] ??= $b + ['mode' => self::mode($b)];
        }

        return array_values($hasil);
    }

    /**
     * @return array<string, mixed>|null null bila tidak ada ATAU bukan pertemuan kelas mahasiswa ini
     */
    public function pertemuan(int $id): ?array
    {
        $b = $this->db->query('
            SELECT ' . self::KOLOM . self::DARI . "
            WHERE m.id = ? AND {$this->syarat}
            ORDER BY " . self::URUT . ' LIMIT 1', [$id])->getRowArray();

        return $b === null ? null : $b + ['mode' => self::mode($b)];
    }

    /**
     * Nilai pertemuan mahasiswa ini, atau nol semuanya bila belum ada kiriman yang dinilai.
     *
     * @param array<string, mixed> $p baris dari pertemuan()
     *
     * @return array{nilai: float, terhitung: float, total: float, target: int, kurang: float, surplus: float, mudahSekali: int, kiriman: int}
     */
    public function nilai(array $p): array
    {
        $n = $this->db->query("
            SELECT ms.* FROM meeting_scores ms
            WHERE ms.meeting_id = ? AND {$this->akses->milikSendiri('ms')}", [(int) $p['pertemuan_id']])->getRowArray();
        $target    = (int) $p['target_points'];
        $terhitung = (float) ($n['counted_points'] ?? 0);

        return [
            'nilai'       => (float) ($n['score'] ?? 0),
            'terhitung'   => $terhitung,
            'total'       => (float) ($n['total_points'] ?? 0),
            'target'      => $target,
            'kurang'      => max(0.0, round($target - $terhitung, 2)),
            'surplus'     => (float) ($n['surplus'] ?? 0),
            'mudahSekali' => (int) ($n['very_easy_counted'] ?? 0),
            'kiriman'     => (int) ($n['submissions'] ?? 0),
        ];
    }

    /**
     * Kartu soal sebuah pertemuan: judul, tingkat, poin, nilai dan status
     * mahasiswa ini. Isi soal TIDAK ikut (SPEC.md §11: hanya soal yang dibuka
     * yang dikirim ke peramban).
     *
     * @param array<string, mixed> $p baris dari pertemuan()
     *
     * @return list<array<string, mixed>>
     */
    public function kartu(array $p): array
    {
        $baris = $this->db->query("
            SELECT mp.id, p.slug, v.difficulty, v.translations, ps.best_score, ps.attempts,
                   (SELECT COUNT(*) FROM submissions s WHERE s.meeting_problem_id = mp.id AND s.status = 'MENUNGGU'
                        AND {$this->akses->milikSendiri('s')}) AS menunggu
            FROM meeting_problems mp
            JOIN problems p ON p.id = mp.problem_id
            LEFT JOIN problem_instances i ON i.meeting_problem_id = mp.id AND {$this->akses->milikSendiri('i')}
            JOIN problem_versions v ON v.id = COALESCE(i.problem_version_id, mp.problem_version_id)
            LEFT JOIN problem_scores ps ON ps.meeting_problem_id = mp.id AND {$this->akses->milikSendiri('ps')}
            WHERE mp.meeting_id = ?
            ORDER BY FIELD(v.difficulty, 'MUDAH_SEKALI', 'MUDAH', 'MUDAH_SEDANG', 'SEDANG', 'SULIT'), p.slug", [(int) $p['pertemuan_id']])->getResultArray();

        $kartu = [];
        foreach ($baris as $i => $b) {
            $poin  = Latihan::POIN[$b['difficulty']];
            $nilai = $b['best_score'] === null ? null : (float) $b['best_score'];
            $kartu[] = [
                'id'        => (int) $b['id'],
                'nomor'     => $i + 1,
                'judul'     => self::terjemahan(json_decode((string) $b['translations'], true) ?? [])['title'] ?? $b['slug'],
                'tingkat'   => $b['difficulty'],
                'poin'      => $poin,
                'nilai'     => $nilai,
                'percobaan' => (int) ($b['attempts'] ?? 0),
                'status'    => (int) $b['menunggu'] > 0 ? 'dinilai' : ($nilai === null ? 'belum' : ($nilai >= $poin ? 'selesai' : 'dicoba')),
            ];
        }

        return $kartu;
    }

    /**
     * Satu soal pertemuan untuk mahasiswa ini: baris pertemuannya (dengan mode),
     * versi yang berlaku baginya, dan instansnya bila sudah ada.
     *
     * Versinya adalah versi instansnya — soal yang sudah dibuka tidak pernah
     * berubah (SPEC.md §8.2) — atau, sebelum instans ada, versi yang dipakai pertemuan.
     *
     * @return array<string, mixed>|null null bila tidak ada ATAU bukan soal kelas mahasiswa ini
     */
    public function soal(int $mpId): ?array
    {
        $b = $this->db->query('
            SELECT ' . self::KOLOM . ', mp.id AS mp_id, mp.problem_version_id AS versi_pertemuan, p.slug,
                   i.id AS instans_id, i.tests AS instans_tes,
                   v.id AS versi_id, v.difficulty, v.translations, v.params, v.samples_count, v.hidden_count, v.time_limit_ms, v.language AS versi_bahasa
            ' . self::DARI . "
            JOIN meeting_problems mp ON mp.meeting_id = m.id
            JOIN problems p ON p.id = mp.problem_id
            LEFT JOIN problem_instances i ON i.meeting_problem_id = mp.id AND i.user_id = e.user_id
            JOIN problem_versions v ON v.id = COALESCE(i.problem_version_id, mp.problem_version_id)
            WHERE mp.id = ? AND {$this->syarat}
            ORDER BY " . self::URUT . ' LIMIT 1', [$mpId])->getRowArray();

        return $b === null ? null : $b + ['mode' => self::mode($b)];
    }

    /**
     * Tes CONTOH instans (masukan dan keluaran yang diharapkan). Tes tersembunyi
     * dipotong di sini dan tidak pernah meninggalkan fungsi ini.
     *
     * @param array<string, mixed> $soal baris dari soal()
     *
     * @return list<array{in: string, out: string}>
     */
    public static function contoh(array $soal): array
    {
        if ($soal['instans_tes'] === null) {
            return [];
        }
        $tes = json_decode((string) $soal['instans_tes'], true) ?? [];

        return array_values(array_map(
            static fn (array $t): array => ['in' => (string) $t['in'], 'out' => (string) $t['out']],
            array_slice($tes, 0, (int) $soal['samples_count']),
        ));
    }

    /**
     * Terjemahan dalam bahasa tampilan; kembali ke bahasa Indonesia bila tidak ada.
     *
     * @param array<string, mixed> $semua kolom translations sebuah versi
     *
     * @return array<string, mixed>
     */
    public static function terjemahan(array $semua, ?string $bahasa = null): array
    {
        $bahasa ??= service('request')->getLocale();
        $t        = $semua[$bahasa] ?? [];

        return trim((string) ($t['title'] ?? '')) !== '' && trim((string) ($t['statement'] ?? '')) !== '' ? $t : ($semua['id'] ?? []);
    }

    /**
     * Draf editor mahasiswa ini untuk soal itu, atau null.
     */
    public function draf(int $mpId): ?string
    {
        $b = $this->db->query("SELECT d.source FROM drafts d WHERE d.meeting_problem_id = ? AND {$this->akses->milikSendiri('d')}", [$mpId])->getRowArray();

        return $b === null ? null : (string) $b['source'];
    }

    public function simpanDraf(int $mpId, string $sumber): void
    {
        $this->db->query(
            'INSERT INTO drafts (user_id, meeting_problem_id, source, updated_at) VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE source = VALUES(source), updated_at = VALUES(updated_at)',
            [$this->akses->identitas()->userId, $mpId, $sumber, Waktu::kini()],
        );
    }

    /**
     * Riwayat kiriman mahasiswa ini untuk soal itu, terbaru lebih dulu. Tanpa kode sumber.
     *
     * @return list<array<string, mixed>>
     */
    public function riwayat(int $mpId, int $batas = 50): array
    {
        return $this->db->query("
            SELECT s.id, s.status, s.verdict, s.passed, s.total, s.score, s.duration_ms, s.created_at, s.job_id
            FROM submissions s
            WHERE s.meeting_problem_id = ? AND {$this->akses->milikSendiri('s')}
            ORDER BY s.id DESC LIMIT ?", [$mpId, $batas])->getResultArray();
    }

    /**
     * Satu kiriman mahasiswa ini, dengan kode sumber dan hasil per tes.
     *
     * @return array<string, mixed>|null
     */
    public function kiriman(int $mpId, int $id): ?array
    {
        return $this->db->query("
            SELECT s.id, s.status, s.verdict, s.passed, s.total, s.score, s.duration_ms, s.compile_log, s.tests,
                   s.missing_concepts, s.source, s.created_at, s.job_id
            FROM submissions s
            WHERE s.id = ? AND s.meeting_problem_id = ? AND {$this->akses->milikSendiri('s')}", [$id, $mpId])->getRowArray();
    }

    /**
     * Kiriman milik pengguna ini yang dikerjakan pekerjaan judge itu (untuk GET /api/executions/{id}).
     *
     * @return array<string, mixed>|null
     */
    public function kirimanDariJob(string $jobId): ?array
    {
        return $this->db->query("
            SELECT s.id, s.meeting_problem_id, s.status, s.verdict, s.passed, s.total, s.score, s.duration_ms,
                   s.compile_log, s.tests, s.missing_concepts, v.difficulty
            FROM submissions s JOIN problem_versions v ON v.id = s.problem_version_id
            WHERE s.job_id = ? AND {$this->akses->milikSendiri('s')}", [$jobId])->getRowArray();
    }

    /**
     * Membuat kiriman dan pekerjaan SUBMIT-nya. Pemanggil sudah memastikan
     * mode `kerja` dan instans yang ada.
     *
     * @param array<string, mixed> $soal baris dari soal()
     *
     * @return array{kiriman: int, job: string}
     */
    public function kirim(array $soal, string $sumber): array
    {
        $user = $this->akses->identitas()->userId;
        $this->db->table('submissions')->insert([
            'user_id' => $user, 'meeting_problem_id' => (int) $soal['mp_id'], 'problem_version_id' => (int) $soal['versi_id'],
            'instance_id' => (int) $soal['instans_id'], 'language' => $soal['versi_bahasa'], 'source' => $sumber,
            'status' => 'MENUNGGU', 'created_at' => Waktu::kini(),
        ]);
        $id = (int) $this->db->insertID();
        try {
            $job = Antrean::kirim('SUBMIT', [
                'language' => $soal['versi_bahasa'], 'source' => $sumber, 'instance_id' => (int) $soal['instans_id'],
                'submission_id' => $id, 'flags' => self::bendera($soal),
            ], $user);
        } catch (\Throwable $e) {
            // Tanpa pekerjaan judge kiriman ini tidak akan pernah dinilai: tandai gagal, jangan dibiarkan menunggu.
            $this->db->table('submissions')->where('id', $id)->update(['status' => 'GAGAL', 'verdict' => 'FAILED', 'judged_at' => Waktu::kini()]);

            throw $e;
        }
        $this->db->table('submissions')->where('id', $id)->update(['job_id' => $job]);

        return ['kiriman' => $id, 'job' => $job];
    }

    /**
     * Bendera kompiler pertemuan sebagai daftar (kosong untuk Python).
     *
     * @param array<string, mixed> $soal
     *
     * @return list<string>
     */
    public static function bendera(array $soal): array
    {
        return Latihan::bendera((string) $soal['compiler_flags'], (string) $soal['versi_bahasa']) ?? [];
    }
}
