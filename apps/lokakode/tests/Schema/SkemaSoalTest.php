<?php

declare(strict_types=1);

namespace Tests\Schema;

use Lokandita\Auth\Testing\RosterFixtures;
use Lokandita\Auth\Testing\TestSchema;
use PHPUnit\Framework\TestCase;

/**
 * SPEC.md §8–§9 terhadap MySQL sungguhan: aturan soal dan konsep yang harus
 * dijaga basis data sendiri, juga ketika kode aplikasi keliru.
 */
final class SkemaSoalTest extends TestCase
{
    private \mysqli $db;     // lokakode_app pada lokakode_test

    // auth_app pada roster_test: sejak tahap A2 hanya auth_app yang menulis roster (KONTRAK.md §18).
    private \mysqli $roster;

    protected function setUp(): void
    {
        $this->db = TestSchema::connect((string) getenv('DB_DSN'));
        $this->roster = TestSchema::connect((string) getenv('TES_ROSTER_TULIS_DSN'));
        // Urutan mengikuti kunci asing. Hanya skema *_test (dijaga TestSchema).
        foreach (['announcements', 'clarifications', 'notifications', 'ui_prefs', 'integrity_events', 'drafts', 'meeting_scores', 'problem_scores', 'submissions', 'meeting_class_windows', 'problem_instances', 'meeting_problems', 'import_items', 'imports', 'problem_versions', 'problems',
            'meeting_concepts', 'course_base_concepts', 'meetings'] as $tabel) {
            $this->db->query("DELETE FROM {$tabel}");
        }
        RosterFixtures::kosongkan($this->roster);
    }

    private function matkul(string $kode): int
    {
        $this->roster->query("INSERT INTO courses (code, name, owner_id, created_at, updated_at)
            VALUES ('{$kode}', 'Mata Kuliah Uji', 'pengampu-uji', UTC_TIMESTAMP(), UTC_TIMESTAMP())");

        return (int) $this->roster->insert_id;
    }

    private function pertemuan(int $matkul, int $nomor, string $bahasa = 'cpp'): int
    {
        $this->db->query("INSERT INTO meetings (course_id, number, title, language, created_at, updated_at)
            VALUES ({$matkul}, {$nomor}, 'Pertemuan uji', '{$bahasa}', UTC_TIMESTAMP(), UTC_TIMESTAMP())");

        return (int) $this->db->insert_id;
    }

    private function soal(int $pertemuan, string $slug): int
    {
        $this->db->query("INSERT INTO problems (meeting_id, slug, created_at) VALUES ({$pertemuan}, '{$slug}', UTC_TIMESTAMP())");

        return (int) $this->db->insert_id;
    }

    private function versi(int $soal, int $nomor, int $contoh = 2, int $tersembunyi = 10, string $status = 'PENDING_REVIEW'): int
    {
        $this->db->query("INSERT INTO problem_versions (problem_id, version, status, language, difficulty, time_limit_ms,
                samples_count, hidden_count, params, translations, generator, reference_solution, concepts,
                required_in_submission, content_hash, created_by, created_at)
            VALUES ({$soal}, {$nomor}, '{$status}', 'cpp', 'MUDAH', 2000, {$contoh}, {$tersembunyi}, '{}', '{}', 'g', 'r', '[]', '[]',
                REPEAT('a', 64), 'uji', UTC_TIMESTAMP())");

        return (int) $this->db->insert_id;
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

    public function testKatalogKonsepDimuatMigrasi(): void
    {
        $katalog = json_decode((string) file_get_contents(__DIR__ . '/../../app/Data/konsep.json'), true);
        $this->assertCount(45, $katalog);
        $this->assertSame(45, (int) $this->db->query('SELECT COUNT(*) FROM concepts')->fetch_row()[0]);
        $baris = $this->db->query("SELECT grup, languages FROM concepts WHERE code = 'REFERENSI'")->fetch_assoc();
        $this->assertSame(['grup' => 'DATA', 'languages' => 'cpp'], $baris);
        $this->assertSame('c,cpp,python', $this->db->query("SELECT languages FROM concepts WHERE code = 'VARIABEL_TIPE'")->fetch_row()[0]);
    }

    public function testNomorPertemuanUnikPerMataKuliahDanAntaraSatuSampaiEnamBelas(): void
    {
        $m = $this->matkul('UJI101');
        $this->pertemuan($m, 1);
        $this->pertemuan($m, 16);
        $this->pertemuan($this->matkul('UJI102'), 1); // mata kuliah lain: boleh

        $this->assertSame(1062, $this->ditolak(fn () => $this->pertemuan($m, 1)));
        $this->assertSame(3819, $this->ditolak(fn () => $this->pertemuan($m, 0)));
        $this->assertSame(3819, $this->ditolak(fn () => $this->pertemuan($m, 17)));
        $this->assertSame(1265, $this->ditolak(fn () => $this->pertemuan($m, 2, 'java')));
    }

    public function testKonsepPertemuanHarusAdaDiKatalog(): void
    {
        $p = $this->pertemuan($this->matkul('UJI101'), 1);
        $this->db->query("INSERT INTO meeting_concepts VALUES ({$p}, 'IO_DASAR')");
        $this->assertSame(1452, $this->ditolak(fn () => $this->db->query("INSERT INTO meeting_concepts VALUES ({$p}, 'TIDAK_ADA')")));
        $this->assertSame(1062, $this->ditolak(fn () => $this->db->query("INSERT INTO meeting_concepts VALUES ({$p}, 'IO_DASAR')")));
    }

    public function testSlugUnikDiDalamPertemuanDanNomorVersiUnikPerSoal(): void
    {
        $m  = $this->matkul('UJI101');
        $p1 = $this->pertemuan($m, 1);
        $p2 = $this->pertemuan($m, 2);
        $s  = $this->soal($p1, 'genap-ganjil');
        $this->soal($p2, 'genap-ganjil'); // pertemuan lain: boleh
        $this->assertSame(1062, $this->ditolak(fn () => $this->soal($p1, 'genap-ganjil')));

        $this->versi($s, 1);
        $this->versi($s, 2);
        $this->assertSame(1062, $this->ditolak(fn () => $this->versi($s, 2)));
    }

    public function testJumlahTesDanStatusVersiDijagaBasisData(): void
    {
        $s = $this->soal($this->pertemuan($this->matkul('UJI101'), 1), 'soal-uji');
        $this->assertSame(3819, $this->ditolak(fn () => $this->versi($s, 1, 1, 10)), 'contoh kurang dari 2');
        $this->assertSame(3819, $this->ditolak(fn () => $this->versi($s, 1, 4, 10)), 'contoh lebih dari 3');
        $this->assertSame(3819, $this->ditolak(fn () => $this->versi($s, 1, 2, 9)), 'tes tersembunyi kurang dari 10');
        $this->assertSame(1265, $this->ditolak(fn () => $this->versi($s, 1, 2, 10, 'DRAFT')));
        $this->versi($s, 1, 3, 12, 'APPROVED');
    }

    public function testSatuInstansPerMahasiswaPerSoalPertemuan(): void
    {
        $p  = $this->pertemuan($this->matkul('UJI101'), 1);
        $s  = $this->soal($p, 'soal-uji');
        $v  = $this->versi($s, 1, 2, 10, 'APPROVED');
        $this->db->query("INSERT INTO meeting_problems (meeting_id, problem_id, problem_version_id, updated_at) VALUES ({$p}, {$s}, {$v}, UTC_TIMESTAMP())");
        $mp = (int) $this->db->insert_id;
        $sisip = fn (string $user) => $this->db->query("INSERT INTO problem_instances (user_id, meeting_problem_id, problem_version_id, seed, tests, created_at)
            VALUES ('{$user}', {$mp}, {$v}, 18446744073709551615, '[]', UTC_TIMESTAMP())");
        $sisip('mhs-1');
        $sisip('mhs-2');
        $this->assertSame(1062, $this->ditolak(fn () => $sisip('mhs-1')));

        // Yang masih dirujuk tidak bisa dihapus: versi, soal pertemuan, soal, pertemuan.
        $this->assertSame(1451, $this->ditolak(fn () => $this->db->query("DELETE FROM problem_versions WHERE id = {$v}")));
        $this->assertSame(1451, $this->ditolak(fn () => $this->db->query("DELETE FROM meeting_problems WHERE id = {$mp}")));
        $this->assertSame(1451, $this->ditolak(fn () => $this->db->query("DELETE FROM problems WHERE id = {$s}")));
        $this->assertSame(1451, $this->ditolak(fn () => $this->db->query("DELETE FROM meetings WHERE id = {$p}")));
    }

    public function testSatuVersiTerpakaiPerSoal(): void
    {
        $p  = $this->pertemuan($this->matkul('UJI101'), 1);
        $s  = $this->soal($p, 'soal-uji');
        $v1 = $this->versi($s, 1, 2, 10, 'APPROVED');
        $v2 = $this->versi($s, 2, 2, 10, 'APPROVED');
        $this->db->query("INSERT INTO meeting_problems (meeting_id, problem_id, problem_version_id, updated_at) VALUES ({$p}, {$s}, {$v1}, UTC_TIMESTAMP())");
        $this->assertSame(1062, $this->ditolak(fn () => $this->db->query("INSERT INTO meeting_problems (meeting_id, problem_id, problem_version_id, updated_at) VALUES ({$p}, {$s}, {$v2}, UTC_TIMESTAMP())")));
    }
}
