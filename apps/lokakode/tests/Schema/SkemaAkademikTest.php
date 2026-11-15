<?php

declare(strict_types=1);

namespace Tests\Schema;

use Lokandita\Auth\Testing\TestSchema;
use PHPUnit\Framework\TestCase;

/**
 * SPEC.md §5 terhadap MySQL sungguhan: aturan yang harus dijaga basis data sendiri,
 * juga ketika kode aplikasi keliru.
 */
final class SkemaAkademikTest extends TestCase
{
    private \mysqli $db;

    protected function setUp(): void
    {
        $this->db = TestSchema::connect((string) getenv('DB_DSN'));
        // Urutan mengikuti kunci asing. Hanya skema *_test (dijaga TestSchema).
        foreach (['enrollment_moves', 'enrollments', 'class_teachers', 'classes', 'courses', 'academic_years'] as $tabel) {
            $this->db->query("DELETE FROM {$tabel}");
        }
    }

    private function tahun(string $label, string $status = 'archived'): int
    {
        $this->db->query("INSERT INTO academic_years (label, starts_on, ends_on, status, created_at, updated_at)
            VALUES ('{$label}', '2026-08-01', '2027-01-31', '{$status}', UTC_TIMESTAMP(), UTC_TIMESTAMP())");

        return (int) $this->db->insert_id;
    }

    private function matkul(string $kode): int
    {
        $this->db->query("INSERT INTO courses (code, name, owner_id, created_at, updated_at)
            VALUES ('{$kode}', 'Mata Kuliah Uji', 'pengampu-uji', UTC_TIMESTAMP(), UTC_TIMESTAMP())");

        return (int) $this->db->insert_id;
    }

    private function kelas(int $matkul, int $tahun, string $nama): int
    {
        $this->db->query("INSERT INTO classes (course_id, academic_year_id, name, created_at, updated_at)
            VALUES ({$matkul}, {$tahun}, '{$nama}', UTC_TIMESTAMP(), UTC_TIMESTAMP())");

        return (int) $this->db->insert_id;
    }

    private function daftar(string $user, int $matkul, int $tahun, int $kelas): int
    {
        $this->db->query("INSERT INTO enrollments (user_id, course_id, academic_year_id, class_id, created_by, created_at, updated_at)
            VALUES ('{$user}', {$matkul}, {$tahun}, {$kelas}, 'uji', UTC_TIMESTAMP(), UTC_TIMESTAMP())");

        return (int) $this->db->insert_id;
    }

    /**
     * Menjalankan SQL yang harus ditolak MySQL dan mengembalikan nomor galatnya.
     */
    private function ditolak(callable $aksi): int
    {
        try {
            $aksi();
        } catch (\mysqli_sql_exception $e) {
            return $e->getCode();
        }
        $this->fail('MySQL menerima perubahan yang seharusnya ditolak');
    }

    public function testTabelSkemaAkademikAda(): void
    {
        $tabel = array_column($this->db->query('SHOW TABLES')->fetch_all(), 0);
        foreach (['academic_years', 'courses', 'classes', 'class_teachers', 'enrollments', 'enrollment_moves', 'audit_log'] as $t) {
            $this->assertContains($t, $tabel);
        }
    }

    public function testHanyaSatuTahunAjaranAktif(): void
    {
        $this->tahun('2025/2026 Genap');
        $this->tahun('2026/2027 Ganjil', 'active');
        $this->tahun('2024/2025 Genap'); // arsip boleh banyak

        $this->assertSame(1062, $this->ditolak(fn () => $this->tahun('2026/2027 Genap', 'active')));
        $this->assertSame(1062, $this->ditolak(fn () => $this->db->query("UPDATE academic_years SET status = 'active' WHERE label = '2025/2026 Genap'")));

        $aktif = $this->db->query("SELECT label FROM academic_years WHERE status = 'active'")->fetch_all();
        $this->assertSame([['2026/2027 Ganjil']], $aktif);
    }

    public function testTahunAjaranBerakhirSesudahMulai(): void
    {
        $this->assertSame(3819, $this->ditolak(fn () => $this->db->query("INSERT INTO academic_years (label, starts_on, ends_on, created_at, updated_at)
            VALUES ('terbalik', '2027-01-31', '2026-08-01', UTC_TIMESTAMP(), UTC_TIMESTAMP())")));
    }

    public function testNamaKelasUnikPerMataKuliahDanTahun(): void
    {
        $t1 = $this->tahun('T1', 'active');
        $t2 = $this->tahun('T2');
        $m  = $this->matkul('UJI101');
        $this->kelas($m, $t1, 'A');
        $this->kelas($m, $t2, 'A'); // tahun lain: boleh
        $this->kelas($this->matkul('UJI102'), $t1, 'A'); // mata kuliah lain: boleh

        $this->assertSame(1062, $this->ditolak(fn () => $this->kelas($m, $t1, 'A')));
    }

    public function testSatuPendaftaranPerMahasiswaPerMataKuliahPerTahun(): void
    {
        $t = $this->tahun('T1', 'active');
        $m = $this->matkul('UJI101');
        $a = $this->kelas($m, $t, 'A');
        $b = $this->kelas($m, $t, 'B');
        $this->daftar('mhs-1', $m, $t, $a);

        $this->assertSame(1062, $this->ditolak(fn () => $this->daftar('mhs-1', $m, $t, $b)), 'kelas kedua di mata kuliah yang sama');

        // Mata kuliah lain pada tahun yang sama: boleh.
        $m2 = $this->matkul('UJI102');
        $this->daftar('mhs-1', $m2, $t, $this->kelas($m2, $t, 'A'));
        $this->assertSame(2, (int) $this->db->query("SELECT COUNT(*) FROM enrollments WHERE user_id = 'mhs-1'")->fetch_row()[0]);
    }

    public function testPendaftaranTidakBisaMenunjukKelasMataKuliahLain(): void
    {
        $t      = $this->tahun('T1', 'active');
        $m1     = $this->matkul('UJI101');
        $m2     = $this->matkul('UJI102');
        $kelas1 = $this->kelas($m1, $t, 'A');
        $kelas2 = $this->kelas($m2, $t, 'A');

        // Mendaftar ke mata kuliah 1 dengan kelas milik mata kuliah 2.
        $this->assertSame(1452, $this->ditolak(fn () => $this->daftar('mhs-1', $m1, $t, $kelas2)));

        // Pindah kelas menyeberang mata kuliah.
        $id = $this->daftar('mhs-1', $m1, $t, $kelas1);
        $this->assertSame(1452, $this->ditolak(fn () => $this->db->query("UPDATE enrollments SET class_id = {$kelas2} WHERE id = {$id}")));

        // Pindah di dalam mata kuliah yang sama: boleh.
        $kelas1b = $this->kelas($m1, $t, 'B');
        $this->db->query("UPDATE enrollments SET class_id = {$kelas1b} WHERE id = {$id}");
        $this->assertSame($kelas1b, (int) $this->db->query("SELECT class_id FROM enrollments WHERE id = {$id}")->fetch_row()[0]);
    }

    public function testKelasBerisiPendaftaranTidakBisaDihapus(): void
    {
        $t = $this->tahun('T1', 'active');
        $m = $this->matkul('UJI101');
        $a = $this->kelas($m, $t, 'A');
        $b = $this->kelas($m, $t, 'B');
        $this->daftar('mhs-1', $m, $t, $a);
        $this->db->query("INSERT INTO class_teachers (class_id, user_id, assigned_by, assigned_at) VALUES ({$b}, 'dosen-1', 'uji', UTC_TIMESTAMP())");

        $this->assertSame(1451, $this->ditolak(fn () => $this->db->query("DELETE FROM classes WHERE id = {$a}")));

        // Kelas kosong boleh dihapus; penugasan pengajarnya ikut terhapus.
        $this->db->query("DELETE FROM classes WHERE id = {$b}");
        $this->assertSame(0, (int) $this->db->query('SELECT COUNT(*) FROM class_teachers')->fetch_row()[0]);
    }

    public function testStatusPendaftaranHanyaEmpatNilai(): void
    {
        $t  = $this->tahun('T1', 'active');
        $m  = $this->matkul('UJI101');
        $id = $this->daftar('mhs-1', $m, $t, $this->kelas($m, $t, 'A'));
        foreach (['LULUS', 'MENGUNDURKAN_DIRI', 'DIPINDAHKAN', 'AKTIF'] as $s) {
            $this->db->query("UPDATE enrollments SET status = '{$s}' WHERE id = {$id}");
        }
        $this->assertSame(1265, $this->ditolak(fn () => $this->db->query("UPDATE enrollments SET status = 'HAPUS' WHERE id = {$id}")));
    }
}
