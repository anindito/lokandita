<?php

declare(strict_types=1);

namespace Tests\Schema;

use Lokandita\Auth\Testing\TestSchema;
use PHPUnit\Framework\TestCase;

/**
 * SPEC.md §6, §7 dan §11 terhadap MySQL sungguhan: aturan jendela, kiriman dan
 * nilai yang harus dijaga basis data sendiri, juga ketika kode aplikasi keliru.
 */
final class SkemaLatihanTest extends TestCase
{
    private \mysqli $db;

    protected function setUp(): void
    {
        $this->db = TestSchema::connect((string) getenv('DB_DSN'));
        // Urutan mengikuti kunci asing. Hanya skema *_test (dijaga TestSchema).
        foreach (['ui_prefs', 'integrity_events', 'drafts', 'meeting_scores', 'problem_scores', 'submissions', 'meeting_class_windows',
            'problem_instances', 'meeting_problems', 'import_items', 'imports', 'problem_versions', 'problems',
            'meeting_concepts', 'course_base_concepts', 'meetings', 'enrollment_moves', 'enrollments', 'class_teachers', 'classes', 'courses', 'academic_years'] as $tabel) {
            $this->db->query("DELETE FROM {$tabel}");
        }
    }

    private function id(string $sql): int
    {
        $this->db->query($sql);

        return (int) $this->db->insert_id;
    }

    /**
     * Dua mata kuliah, masing-masing satu kelas dan satu pertemuan; satu soal disetujui dengan satu instans.
     *
     * @return array<string, int>
     */
    private function dunia(): array
    {
        $t  = $this->id("INSERT INTO academic_years (label, starts_on, ends_on, status, created_at, updated_at) VALUES ('2026/2027', '2026-08-01', '2027-01-31', 'active', UTC_TIMESTAMP(), UTC_TIMESTAMP())");
        $d  = [];
        foreach (['a' => 'UJI101', 'b' => 'UJI102'] as $k => $kode) {
            $d["matkul_{$k}"]    = $this->id("INSERT INTO courses (code, name, owner_id, created_at, updated_at) VALUES ('{$kode}', 'Uji', 'pengampu', UTC_TIMESTAMP(), UTC_TIMESTAMP())");
            $d["kelas_{$k}"]     = $this->id("INSERT INTO classes (course_id, academic_year_id, name, created_at, updated_at) VALUES ({$d["matkul_{$k}"]}, {$t}, 'A', UTC_TIMESTAMP(), UTC_TIMESTAMP())");
            $d["pertemuan_{$k}"] = $this->id("INSERT INTO meetings (course_id, number, title, language, created_at, updated_at) VALUES ({$d["matkul_{$k}"]}, 1, 'Uji', 'cpp', UTC_TIMESTAMP(), UTC_TIMESTAMP())");
        }
        $d['soal']  = $this->id("INSERT INTO problems (meeting_id, slug, created_at) VALUES ({$d['pertemuan_a']}, 'soal-uji', UTC_TIMESTAMP())");
        $d['versi'] = $this->id("INSERT INTO problem_versions (problem_id, version, status, language, difficulty, time_limit_ms, samples_count, hidden_count,
                params, translations, generator, reference_solution, concepts, required_in_submission, content_hash, created_by, created_at)
            VALUES ({$d['soal']}, 1, 'APPROVED', 'cpp', 'MUDAH', 2000, 2, 10, '{}', '{}', 'g', 'r', '[]', '[]', REPEAT('a', 64), 'uji', UTC_TIMESTAMP())");
        $d['mp']      = $this->id("INSERT INTO meeting_problems (meeting_id, problem_id, problem_version_id, updated_at) VALUES ({$d['pertemuan_a']}, {$d['soal']}, {$d['versi']}, UTC_TIMESTAMP())");
        $d['instans'] = $this->id("INSERT INTO problem_instances (user_id, meeting_problem_id, problem_version_id, seed, tests, created_at) VALUES ('mhs-1', {$d['mp']}, {$d['versi']}, 1, '[]', UTC_TIMESTAMP())");

        return $d;
    }

    private function jendela(int $pertemuan, int $kelas, int $matkul, string $buka = "'2026-11-20 01:00:00'", string $tutup = "'2026-11-20 03:00:00'"): int
    {
        return $this->id("INSERT INTO meeting_class_windows (meeting_id, class_id, course_id, open_at, close_at, updated_by, created_at, updated_at)
            VALUES ({$pertemuan}, {$kelas}, {$matkul}, {$buka}, {$tutup}, 'uji', UTC_TIMESTAMP(), UTC_TIMESTAMP())");
    }

    private function kiriman(array $d, string $job = 'NULL'): int
    {
        return $this->id("INSERT INTO submissions (user_id, meeting_problem_id, problem_version_id, instance_id, language, source, job_id, created_at)
            VALUES ('mhs-1', {$d['mp']}, {$d['versi']}, {$d['instans']}, 'cpp', 'int main(){}', {$job}, UTC_TIMESTAMP())");
    }

    private function ditolak(callable $aksi): int
    {
        try {
            $aksi();
        } catch (\mysqli_sql_exception $e) {
            return $e->getCode();
        }
        $this->fail('MySQL menerima perubahan yang seharusnya ditolak');
    }

    public function testPertemuanMendapatNilaiBawaanSpek(): void
    {
        $d = $this->dunia();
        $m = $this->db->query("SELECT * FROM meetings WHERE id = {$d['pertemuan_a']}")->fetch_assoc();
        $this->assertSame(['60', '8', '8', '6', '6', '4'], [$m['target_points'], $m['min_mudah_sekali'], $m['min_mudah'], $m['min_mudah_sedang'], $m['min_sedang'], $m['min_sulit']]);
        $this->assertNull($m['published_at'], 'pertemuan baru belum diterbitkan');
        $this->assertNull($m['starter_template']);
        $this->assertSame(['', '1', '0', '0'], [$m['compiler_flags'], $m['autocomplete'], $m['anonymize_leaderboard'], $m['practice_after_close']]);
        $this->assertSame(3819, $this->ditolak(fn () => $this->db->query("UPDATE meetings SET target_points = 0 WHERE id = {$d['pertemuan_a']}")));
    }

    public function testSatuJendelaPerPertemuanPerKelasDanKelasHarusDariMataKuliahYangSama(): void
    {
        $d = $this->dunia();
        $j = $this->jendela($d['pertemuan_a'], $d['kelas_a'], $d['matkul_a']);
        $baris = $this->db->query("SELECT override, late_policy, late_factor, post_close_visibility FROM meeting_class_windows WHERE id = {$j}")->fetch_assoc();
        $this->assertSame(['override' => 'none', 'late_policy' => 'reject', 'late_factor' => '0.50', 'post_close_visibility' => 'hidden'], $baris);

        $this->assertSame(1062, $this->ditolak(fn () => $this->jendela($d['pertemuan_a'], $d['kelas_a'], $d['matkul_a'])), 'jendela kedua untuk pasangan yang sama');
        // Kelas mata kuliah lain: ditolak, dengan course_id mana pun.
        $this->assertSame(1452, $this->ditolak(fn () => $this->jendela($d['pertemuan_a'], $d['kelas_b'], $d['matkul_a'])));
        $this->assertSame(1452, $this->ditolak(fn () => $this->jendela($d['pertemuan_a'], $d['kelas_b'], $d['matkul_b'])));
        $this->jendela($d['pertemuan_b'], $d['kelas_b'], $d['matkul_b']);
    }

    public function testWaktuTutupHarusSesudahWaktuBukaDanFaktorAntaraNolDanSatu(): void
    {
        $d = $this->dunia();
        $this->assertSame(3819, $this->ditolak(fn () => $this->jendela($d['pertemuan_a'], $d['kelas_a'], $d['matkul_a'], "'2026-11-20 03:00:00'", "'2026-11-20 03:00:00'")));
        $j = $this->jendela($d['pertemuan_a'], $d['kelas_a'], $d['matkul_a'], 'NULL', 'NULL'); // belum dijadwalkan: boleh
        $this->assertSame(3819, $this->ditolak(fn () => $this->db->query("UPDATE meeting_class_windows SET late_factor = 1.5 WHERE id = {$j}")));
        $this->assertSame(1265, $this->ditolak(fn () => $this->db->query("UPDATE meeting_class_windows SET override = 'maybe' WHERE id = {$j}")));
    }

    public function testKirimanSatuPerJobDanTidakBisaDihapusSelamaDirujukNilai(): void
    {
        $d = $this->dunia();
        $k = $this->kiriman($d, "'job-1'");
        $this->kiriman($d);
        $this->kiriman($d); // job_id NULL boleh berulang
        $this->assertSame(1062, $this->ditolak(fn () => $this->kiriman($d, "'job-1'")));
        $this->assertSame('MENUNGGU', $this->db->query("SELECT status FROM submissions WHERE id = {$k}")->fetch_row()[0]);

        $nilai = fn () => $this->db->query("INSERT INTO problem_scores (user_id, meeting_problem_id, meeting_id, difficulty, points, best_score, best_submission_id, attempts, reached_at, updated_at)
            VALUES ('mhs-1', {$d['mp']}, {$d['pertemuan_a']}, 'MUDAH', 5, 2.50, {$k}, 1, UTC_TIMESTAMP(), UTC_TIMESTAMP())");
        $nilai();
        $this->assertSame(1062, $this->ditolak($nilai), 'nilai kedua untuk mahasiswa dan soal yang sama');
        $this->assertSame(1451, $this->ditolak(fn () => $this->db->query("DELETE FROM submissions WHERE id = {$k}")));
        $this->assertSame(1451, $this->ditolak(fn () => $this->db->query("DELETE FROM problem_instances WHERE id = {$d['instans']}")));
    }

    public function testSatuNilaiPertemuanDanSatuDrafPerMahasiswa(): void
    {
        $d = $this->dunia();
        $nilai = fn () => $this->db->query("INSERT INTO meeting_scores (user_id, meeting_id, total_points, counted_points, target_points, score, surplus, very_easy_counted, submissions, updated_at)
            VALUES ('mhs-1', {$d['pertemuan_a']}, 5, 5, 60, 8.33, 0, 0, 1, UTC_TIMESTAMP())");
        $nilai();
        $this->assertSame(1062, $this->ditolak($nilai));

        $draf = fn () => $this->db->query("INSERT INTO drafts (user_id, meeting_problem_id, source, updated_at) VALUES ('mhs-1', {$d['mp']}, 'x', UTC_TIMESTAMP())");
        $draf();
        $this->assertSame(1062, $this->ditolak($draf));
        $this->assertSame(1452, $this->ditolak(fn () => $this->db->query("INSERT INTO drafts (user_id, meeting_problem_id, source, updated_at) VALUES ('mhs-1', 999999, 'x', UTC_TIMESTAMP())")));
    }

    public function testPeristiwaIntegritasDanPreferensiPanel(): void
    {
        $d = $this->dunia();
        $this->db->query("INSERT INTO integrity_events (user_id, kind, meeting_problem_id, detail, created_at) VALUES ('mhs-1', 'paste_blocked', {$d['mp']}, '{\"jalur\":\"paste\"}', UTC_TIMESTAMP())");
        $this->db->query("INSERT INTO integrity_events (user_id, kind, created_at) VALUES ('mhs-1', 'paste_blocked', UTC_TIMESTAMP())");
        $this->assertSame(1452, $this->ditolak(fn () => $this->db->query("INSERT INTO integrity_events (user_id, kind, meeting_problem_id, created_at) VALUES ('mhs-1', 'paste_blocked', 999999, UTC_TIMESTAMP())")));

        $this->db->query("INSERT INTO ui_prefs (user_id, panels, updated_at) VALUES ('mhs-1', '{\"kiri\":40}', UTC_TIMESTAMP())");
        $this->assertSame(1062, $this->ditolak(fn () => $this->db->query("INSERT INTO ui_prefs (user_id, panels, updated_at) VALUES ('mhs-1', '{}', UTC_TIMESTAMP())")));
    }
}
