<?php

declare(strict_types=1);

namespace Tests\Schema;

use Lokandita\Auth\Testing\TestSchema;
use PHPUnit\Framework\TestCase;

/**
 * View roster (KONTRAK.md §18) terhadap MySQL sungguhan: kelima view ada, kolom dan tipenya
 * persis seperti di kontrak, dan isinya adalah isi tabel LokaKode — tidak lebih, tidak kurang.
 * Bahwa `roster_reader` hanya bisa membaca view ini dibuktikan dari sisi pembacanya
 * (apps/lokaujian/tests) dan oleh infra/smoke.sh cek 13.
 */
final class ViewRosterTest extends TestCase
{
    /** Nama kolom => tipe kolom MySQL, dalam urutan kontrak. */
    private const KONTRAK = [
        'roster_academic_years' => ['id' => 'bigint unsigned', 'label' => 'varchar(40)', 'starts_on' => 'date', 'ends_on' => 'date', 'status' => "enum('active','archived')"],
        'roster_courses'        => ['id' => 'bigint unsigned', 'code' => 'varchar(20)', 'name' => 'varchar(190)', 'owner_id' => 'char(36)'],
        'roster_classes'        => ['id' => 'bigint unsigned', 'course_id' => 'bigint unsigned', 'academic_year_id' => 'bigint unsigned', 'name' => 'varchar(60)', 'archived_at' => 'datetime'],
        'roster_class_teachers' => ['class_id' => 'bigint unsigned', 'user_id' => 'char(36)'],
        'roster_enrollments'    => ['id' => 'bigint unsigned', 'user_id' => 'char(36)', 'course_id' => 'bigint unsigned', 'academic_year_id' => 'bigint unsigned', 'class_id' => 'bigint unsigned',
            'status' => "enum('AKTIF','LULUS','MENGUNDURKAN_DIRI','DIPINDAHKAN')", 'graduated_on' => 'date'],
    ];

    private const TABEL = [
        'roster_academic_years' => 'academic_years', 'roster_courses' => 'courses', 'roster_classes' => 'classes',
        'roster_class_teachers' => 'class_teachers', 'roster_enrollments' => 'enrollments',
    ];

    private \mysqli $db;

    protected function setUp(): void
    {
        $this->db = TestSchema::connect((string) getenv('DB_DSN'));
        // Urutan mengikuti kunci asing. Hanya skema *_test (dijaga TestSchema).
        foreach (['announcements', 'clarifications', 'notifications', 'seb_exam_keys', 'seb_configs', 'ui_prefs', 'integrity_events', 'drafts', 'meeting_scores', 'problem_scores', 'submissions',
            'meeting_class_windows', 'problem_instances', 'meeting_problems', 'import_items', 'imports', 'problem_versions', 'problems',
            'meeting_concepts', 'course_base_concepts', 'meetings', 'enrollment_moves', 'enrollments', 'class_teachers', 'classes', 'courses', 'academic_years'] as $tabel) {
            $this->db->query("DELETE FROM {$tabel}");
        }
    }

    public function testKelimaViewAdaDenganKolomDanTipeKontrak(): void
    {
        $ada = array_column($this->db->query("SELECT table_name FROM information_schema.views WHERE table_schema = DATABASE() AND table_name LIKE 'roster\\_%' ORDER BY table_name")->fetch_all(), 0);
        $harap = array_keys(self::KONTRAK);
        sort($harap);
        $this->assertSame($harap, $ada, 'view roster_* di skema ini harus persis kelima view kontrak');

        foreach (self::KONTRAK as $view => $kolom) {
            $baris = $this->db->query("SELECT column_name, column_type FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = '{$view}' ORDER BY ordinal_position")->fetch_all();
            $this->assertSame($kolom, array_column($baris, 1, 0), "kolom {$view}");
        }
    }

    public function testViewDibuatDenganHakPembuatnya(): void
    {
        $baris = $this->db->query("SELECT table_name, security_type FROM information_schema.views WHERE table_schema = DATABASE() AND table_name LIKE 'roster\\_%'")->fetch_all();
        $this->assertCount(5, $baris);
        foreach ($baris as [$view, $jenis]) {
            $this->assertSame('DEFINER', $jenis, "{$view}: pembaca tidak boleh memerlukan hak pada tabel");
        }
    }

    public function testIsiViewAdalahIsiTabel(): void
    {
        $this->db->query("INSERT INTO academic_years (label, starts_on, ends_on, status, created_at, updated_at) VALUES ('2026/2027 Ganjil', '2026-08-01', '2027-01-31', 'active', UTC_TIMESTAMP(), UTC_TIMESTAMP())");
        $tahun = (int) $this->db->insert_id;
        $this->db->query("INSERT INTO academic_years (label, starts_on, ends_on, status, created_at, updated_at) VALUES ('2025/2026 Genap', '2026-02-01', '2026-07-31', 'archived', UTC_TIMESTAMP(), UTC_TIMESTAMP())");
        $this->db->query("INSERT INTO courses (code, name, owner_id, created_at, updated_at) VALUES ('UJI101', 'Mata Kuliah Uji', 'pengampu-1', UTC_TIMESTAMP(), UTC_TIMESTAMP())");
        $matkul = (int) $this->db->insert_id;
        $this->db->query("INSERT INTO classes (course_id, academic_year_id, name, capacity, archived_at, created_at, updated_at) VALUES ({$matkul}, {$tahun}, 'A', 40, NULL, UTC_TIMESTAMP(), UTC_TIMESTAMP())");
        $kelasA = (int) $this->db->insert_id;
        $this->db->query("INSERT INTO classes (course_id, academic_year_id, name, archived_at, created_at, updated_at) VALUES ({$matkul}, {$tahun}, 'B', '2026-11-01 02:03:04', UTC_TIMESTAMP(), UTC_TIMESTAMP())");
        $this->db->query("INSERT INTO class_teachers (class_id, user_id, assigned_by, assigned_at) VALUES ({$kelasA}, 'pengajar-1', 'pengampu-1', UTC_TIMESTAMP())");
        $this->db->query("INSERT INTO enrollments (user_id, course_id, academic_year_id, class_id, status, graduated_on, created_by, created_at, updated_at)
            VALUES ('mhs-1', {$matkul}, {$tahun}, {$kelasA}, 'AKTIF', NULL, 'pengampu-1', UTC_TIMESTAMP(), UTC_TIMESTAMP()),
                   ('mhs-2', {$matkul}, {$tahun}, {$kelasA}, 'LULUS', '2026-11-20', 'pengampu-1', UTC_TIMESTAMP(), UTC_TIMESTAMP())");

        foreach (self::KONTRAK as $view => $kolom) {
            $daftar = implode(', ', array_keys($kolom));
            $urut   = array_key_first($kolom) . ', ' . array_keys($kolom)[1];
            $dariView  = $this->db->query("SELECT {$daftar} FROM {$view} ORDER BY {$urut}")->fetch_all();
            $dariTabel = $this->db->query("SELECT {$daftar} FROM " . self::TABEL[$view] . " ORDER BY {$urut}")->fetch_all();
            $this->assertNotSame([], $dariView, "{$view} berisi baris uji");
            $this->assertSame($dariTabel, $dariView, "{$view} sama dengan tabelnya");
        }
        // Yang sengaja TIDAK ada di kontrak tidak boleh terbaca lewat view.
        $this->assertSame(1054, $this->galat('SELECT capacity FROM roster_classes'));
        $this->assertSame(1054, $this->galat('SELECT created_by FROM roster_enrollments'));
        $this->assertSame(1054, $this->galat('SELECT assigned_by FROM roster_class_teachers'));
    }

    private function galat(string $sql): int
    {
        try {
            $this->db->query($sql);
        } catch (\mysqli_sql_exception $e) {
            return $e->getCode();
        }
        $this->fail("MySQL menerima: {$sql}");
    }
}
