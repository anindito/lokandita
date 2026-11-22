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
        foreach (['announcements', 'clarifications', 'notifications', 'ui_prefs', 'integrity_events', 'drafts', 'meeting_scores', 'problem_scores', 'submissions', 'meeting_class_windows',
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

    // ---- tahap 10b: KONTRAK.md §17 ----------------------------------------------------

    public function testPemberitahuanSatuPerPenggunaJenisDanRujukanDanHanyaTigaJenis(): void
    {
        $satu = fn (string $jenis = 'pertemuan_dibuka', string $ref = '7:2026-11-20 01:00:00', string $siapa = 'mhs-1') => $this->db->query(
            "INSERT INTO notifications (user_id, kind, ref, data, created_at) VALUES ('{$siapa}', '{$jenis}', '{$ref}', '{}', UTC_TIMESTAMP())"
        );
        $satu();
        $this->assertSame(1062, $this->ditolak($satu), 'pemberitahuan yang sama tidak dibuat dua kali');
        $satu('pertemuan_dibuka', '7:2026-11-21 01:00:00'); // jendela dijadwalkan ulang: rujukan lain
        $satu('tutup_24jam');
        $satu('sandi_direset', '15');
        $satu('pertemuan_dibuka', '7:2026-11-20 01:00:00', 'mhs-2');
        $this->assertSame(3819, $this->ditolak(fn () => $satu('klarifikasi_dijawab')), 'jenis di luar SPEC.md §14');
        // INSERT IGNORE — cara Notifikasi::segarkan — tidak menambah baris dan tidak gagal.
        $this->db->query("INSERT IGNORE INTO notifications (user_id, kind, ref, created_at) VALUES ('mhs-1', 'tutup_24jam', '7:2026-11-20 01:00:00', UTC_TIMESTAMP())");
        $this->assertSame('5', $this->db->query('SELECT COUNT(*) FROM notifications')->fetch_row()[0]);
        $this->assertNull($this->db->query('SELECT read_at FROM notifications LIMIT 1')->fetch_row()[0]);
    }

    public function testKlarifikasiJawabanUtuhDanTerbitHanyaBilaBerjawab(): void
    {
        $d = $this->dunia();
        $k = $this->id("INSERT INTO clarifications (meeting_problem_id, class_id, user_id, question, created_at) VALUES ({$d['mp']}, {$d['kelas_a']}, 'mhs-1', 'Apakah n bisa nol?', UTC_TIMESTAMP())");
        $b = $this->db->query("SELECT answer, visibility, published_at FROM clarifications WHERE id = {$k}")->fetch_assoc();
        $this->assertSame(['answer' => null, 'visibility' => 'private', 'published_at' => null], $b);

        // Belum dijawab: tidak bisa diterbitkan; jawaban tanpa penjawab atau tanpa waktu ditolak.
        $this->assertSame(3819, $this->ditolak(fn () => $this->db->query("UPDATE clarifications SET visibility = 'class', published_at = UTC_TIMESTAMP() WHERE id = {$k}")));
        $this->assertSame(3819, $this->ditolak(fn () => $this->db->query("UPDATE clarifications SET answer = 'Ya' WHERE id = {$k}")));
        $this->assertSame(3819, $this->ditolak(fn () => $this->db->query("UPDATE clarifications SET answer = 'Ya', answered_by = 'dosen' WHERE id = {$k}")));
        $this->db->query("UPDATE clarifications SET answer = 'Ya', answered_by = 'dosen', answered_at = UTC_TIMESTAMP() WHERE id = {$k}");
        // Terbit ke kelas harus bersama waktu terbitnya, dan sebaliknya.
        $this->assertSame(3819, $this->ditolak(fn () => $this->db->query("UPDATE clarifications SET visibility = 'class' WHERE id = {$k}")));
        $this->assertSame(3819, $this->ditolak(fn () => $this->db->query("UPDATE clarifications SET published_at = UTC_TIMESTAMP() WHERE id = {$k}")));
        $this->db->query("UPDATE clarifications SET visibility = 'class', published_at = UTC_TIMESTAMP() WHERE id = {$k}");
        $this->assertSame(1265, $this->ditolak(fn () => $this->db->query("UPDATE clarifications SET visibility = 'semua' WHERE id = {$k}")));

        $this->assertSame(1452, $this->ditolak(fn () => $this->db->query("INSERT INTO clarifications (meeting_problem_id, class_id, user_id, question, created_at) VALUES (999999, {$d['kelas_a']}, 'mhs-1', 'x', UTC_TIMESTAMP())")));
        $this->assertSame(1452, $this->ditolak(fn () => $this->db->query("INSERT INTO clarifications (meeting_problem_id, class_id, user_id, question, created_at) VALUES ({$d['mp']}, 999999, 'mhs-1', 'x', UTC_TIMESTAMP())")));
        $this->assertSame(1451, $this->ditolak(fn () => $this->db->query("DELETE FROM classes WHERE id = {$d['kelas_a']}")), 'kelas yang punya klarifikasi tidak bisa dihapus');
    }

    public function testPengumumanDitarikDenganPenarikDanMenahanPenghapusanKelas(): void
    {
        $d = $this->dunia();
        $p = $this->id("INSERT INTO announcements (class_id, author_id, title, body, created_at) VALUES ({$d['kelas_a']}, 'dosen', 'Kuis', 'Kuis diundur.', UTC_TIMESTAMP())");
        $this->assertSame(3819, $this->ditolak(fn () => $this->db->query("UPDATE announcements SET withdrawn_at = UTC_TIMESTAMP() WHERE id = {$p}")));
        $this->assertSame(3819, $this->ditolak(fn () => $this->db->query("UPDATE announcements SET withdrawn_by = 'dosen' WHERE id = {$p}")));
        $this->db->query("UPDATE announcements SET withdrawn_at = UTC_TIMESTAMP(), withdrawn_by = 'dosen' WHERE id = {$p}");
        $this->assertSame(1452, $this->ditolak(fn () => $this->db->query("INSERT INTO announcements (class_id, author_id, title, body, created_at) VALUES (999999, 'dosen', 'x', 'x', UTC_TIMESTAMP())")));
        $this->assertSame(1451, $this->ditolak(fn () => $this->db->query("DELETE FROM classes WHERE id = {$d['kelas_a']}")), 'kelas yang punya pengumuman tidak bisa dihapus');
    }

    public function testPeristiwaKetikanTakWajarDisimpanSebagaiJenisBaru(): void
    {
        $d = $this->dunia();
        $this->db->query("INSERT INTO integrity_events (user_id, kind, meeting_problem_id, detail, created_at)
            VALUES ('mhs-1', 'typing_anomaly', {$d['mp']}, '{\"karakter\":420,\"ms\":900,\"cps\":466,\"sebab\":\"ketik\"}', UTC_TIMESTAMP())");
        $b = $this->db->query("SELECT JSON_EXTRACT(detail, '$.karakter') FROM integrity_events WHERE kind = 'typing_anomaly'")->fetch_row();
        $this->assertSame('420', $b[0]);
    }
}
