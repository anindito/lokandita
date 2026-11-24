<?php

declare(strict_types=1);

namespace Tests;

use Lokandita\Auth\Dsn;
use Lokandita\Auth\RosterDb;
use Lokandita\Auth\Testing\TestSchema;
use PHPUnit\Framework\TestCase;

/**
 * KONTRAK.md §18 dari sisi PEMBACA, terhadap MySQL sungguhan: LokaUjian membaca kelas dan
 * pendaftaran hanya lewat lima view roster, sebagai `roster_reader`. Pengguna itu tidak bisa
 * membaca tabel LokaKode mana pun dan tidak bisa menulis; `lokaujian_app` tidak bisa menyentuh
 * skema LokaKode sama sekali.
 *
 * Baris uji ditulis ke lokakode_test lewat koneksi khusus tes sebagai lokakode_app
 * (TES_ROSTER_TULIS_DSN dari infra/tes.sh). Aplikasi yang diuji tidak memegang DSN itu.
 */
final class RosterTest extends TestCase
{
    private const VIEW = ['roster_academic_years', 'roster_class_teachers', 'roster_classes', 'roster_courses', 'roster_enrollments'];

    private const DITOLAK_TABEL = 1142;  // SELECT/INSERT/UPDATE/DELETE command denied … for table
    private const DITOLAK_KOLOM = 1143;
    private const DITOLAK_SKEMA = 1044;  // Access denied for user … to database

    private \mysqli $tulis;   // lokakode_app pada lokakode_test — hanya untuk menyiapkan baris uji

    private \mysqli $roster;  // roster_reader, seperti aplikasinya

    protected function setUp(): void
    {
        $this->tulis  = TestSchema::connect((string) getenv('TES_ROSTER_TULIS_DSN'));
        $this->roster = TestSchema::connect((string) getenv('ROSTER_READER_DSN'));
        foreach (['enrollment_moves', 'enrollments', 'class_teachers', 'classes', 'courses', 'academic_years'] as $t) {
            $this->tulis->query("DELETE FROM {$t}");
        }
        $this->tulis->query("INSERT INTO academic_years (label, starts_on, ends_on, status, created_at, updated_at) VALUES ('2026/2027 Ganjil', '2026-08-01', '2027-01-31', 'active', UTC_TIMESTAMP(), UTC_TIMESTAMP())");
        $tahun = (int) $this->tulis->insert_id;
        $this->tulis->query("INSERT INTO courses (code, name, owner_id, created_at, updated_at) VALUES ('UJI101', 'Mata Kuliah Uji', 'pengampu-1', UTC_TIMESTAMP(), UTC_TIMESTAMP())");
        $matkul = (int) $this->tulis->insert_id;
        $this->tulis->query("INSERT INTO classes (course_id, academic_year_id, name, capacity, created_at, updated_at) VALUES ({$matkul}, {$tahun}, 'A', 40, UTC_TIMESTAMP(), UTC_TIMESTAMP())");
        $kelas = (int) $this->tulis->insert_id;
        $this->tulis->query("INSERT INTO class_teachers (class_id, user_id, assigned_by, assigned_at) VALUES ({$kelas}, 'pengajar-1', 'pengampu-1', UTC_TIMESTAMP())");
        $this->tulis->query("INSERT INTO enrollments (user_id, course_id, academic_year_id, class_id, status, created_by, created_at, updated_at) VALUES ('mhs-1', {$matkul}, {$tahun}, {$kelas}, 'AKTIF', 'pengampu-1', UTC_TIMESTAMP(), UTC_TIMESTAMP())");
    }

    private function galat(\mysqli $db, string $sql): int
    {
        try {
            $db->query($sql);
        } catch (\mysqli_sql_exception $e) {
            return $e->getCode();
        }
        $this->fail("MySQL menerima: {$sql}");
    }

    public function testPembacaAdalahRosterReaderDanMembacaKelimaView(): void
    {
        $this->assertSame('roster_reader', Dsn::parse((string) getenv('ROSTER_READER_DSN'))['username']);
        foreach (self::VIEW as $v) {
            $n = (int) $this->roster->query("SELECT COUNT(*) FROM {$v}")->fetch_row()[0];
            $this->assertSame(1, $n, $v);
        }
        $baris = $this->roster->query('SELECT e.user_id, e.status, k.name, c.code, t.status AS tahun FROM roster_enrollments e
            JOIN roster_classes k ON k.id = e.class_id JOIN roster_courses c ON c.id = e.course_id JOIN roster_academic_years t ON t.id = e.academic_year_id')->fetch_assoc();
        $this->assertSame(['user_id' => 'mhs-1', 'status' => 'AKTIF', 'name' => 'A', 'code' => 'UJI101', 'tahun' => 'active'], $baris);
    }

    public function testPembacaHanyaMelihatKelimaView(): void
    {
        $terlihat = array_column($this->roster->query('SHOW TABLES')->fetch_all(), 0);
        sort($terlihat);
        $this->assertSame(self::VIEW, $terlihat);
    }

    public function testPembacaTidakBisaMembacaTabelLokaKode(): void
    {
        foreach (['academic_years', 'courses', 'classes', 'class_teachers', 'enrollments', 'enrollment_moves', 'submissions', 'audit_log', 'problem_versions', 'seb_configs', 'integrity_events', 'migrations'] as $t) {
            $this->assertSame(self::DITOLAK_TABEL, $this->galat($this->roster, "SELECT COUNT(*) FROM {$t}"), $t);
        }
        // Kolom yang sengaja di luar kontrak tidak ada di view.
        $this->assertSame(1054, $this->galat($this->roster, 'SELECT capacity FROM roster_classes'));
    }

    public function testPembacaTidakBisaMenulis(): void
    {
        $this->assertSame(self::DITOLAK_TABEL, $this->galat($this->roster, "INSERT INTO roster_classes (course_id, academic_year_id, name) VALUES (1, 1, 'Z')"));
        $this->assertContains($this->galat($this->roster, "UPDATE roster_enrollments SET status = 'LULUS'"), [self::DITOLAK_TABEL, self::DITOLAK_KOLOM]);
        $this->assertSame(self::DITOLAK_TABEL, $this->galat($this->roster, 'DELETE FROM roster_class_teachers'));
        $this->assertSame(self::DITOLAK_TABEL, $this->galat($this->roster, 'DROP VIEW roster_courses'));
        $this->assertSame(self::DITOLAK_TABEL, $this->galat($this->roster, 'CREATE TABLE coba (id INT)'));
        // Tidak ada yang berubah.
        $this->assertSame('AKTIF', $this->tulis->query('SELECT status FROM enrollments')->fetch_row()[0]);
        $this->assertSame(1, (int) $this->tulis->query('SELECT COUNT(*) FROM class_teachers')->fetch_row()[0]);
    }

    public function testPenggunaSkemaLokaUjianTidakMenyentuhSkemaRoster(): void
    {
        $app    = TestSchema::connect((string) getenv('DB_DSN'));
        $this->assertSame('lokaujian_app', Dsn::parse((string) getenv('DB_DSN'))['username']);
        $roster = TestSchema::schemaOf((string) getenv('ROSTER_READER_DSN'));
        $this->assertNotSame(TestSchema::schemaOf((string) getenv('DB_DSN')), $roster);
        foreach (["SELECT COUNT(*) FROM `{$roster}`.enrollments", "SELECT COUNT(*) FROM `{$roster}`.roster_enrollments", "INSERT INTO `{$roster}`.courses (code, name, owner_id, created_at, updated_at) VALUES ('X', 'X', 'x', NOW(), NOW())"] as $sql) {
            $this->assertSame(self::DITOLAK_TABEL, $this->galat($app, $sql), $sql);
        }
        $this->assertSame(self::DITOLAK_SKEMA, $this->galat($app, "USE `{$roster}`"));
        // …dan pembaca roster tidak menyentuh skema LokaUjian.
        $this->assertSame(self::DITOLAK_SKEMA, $this->galat($this->roster, 'USE `' . TestSchema::schemaOf((string) getenv('DB_DSN')) . '`'));
    }

    public function testKoneksiPaketBersamaMemakaiDsnRoster(): void
    {
        $db = RosterDb::conn();
        $this->assertSame('roster_reader@%', $db->query('SELECT CURRENT_USER()')->fetch_row()[0]);
        $this->assertSame(1, (int) $db->query('SELECT COUNT(*) FROM roster_courses')->fetch_row()[0]);
        $this->assertSame('+00:00', $db->query('SELECT @@session.time_zone')->fetch_row()[0]);
    }
}
