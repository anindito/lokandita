<?php

declare(strict_types=1);

namespace Tests\Support;

use Lokandita\Auth\Testing\Fixtures;
use Lokandita\Auth\Testing\Http;
use Lokandita\Auth\Testing\TestSchema;
use PHPUnit\Framework\TestCase;

/**
 * Dasar tes LokaKode: HTTP sungguhan ke kontainer lokakode-tes, yang membaca
 * sesi dari identity_test dan berbicara dengan auth-tes; SQL langsung ke
 * lokakode_test dan identity_test untuk menyiapkan dan memeriksa data.
 */
abstract class KodeCase extends TestCase
{
    protected \mysqli $db;  // lokakode_test

    protected \mysqli $idn; // identity_test

    protected function setUp(): void
    {
        $this->db  = TestSchema::connect((string) getenv('DB_DSN'));
        $this->idn = TestSchema::connect((string) getenv('TES_IDENTITY_DSN'));
        // Urutan mengikuti kunci asing. Hanya skema *_test (dijaga TestSchema).
        foreach (['judge_jobs', 'audit_log', 'problem_instances', 'meeting_problems', 'import_items', 'imports', 'problem_versions', 'problems', 'meeting_concepts', 'course_base_concepts', 'meetings', 'enrollment_moves', 'enrollments', 'class_teachers', 'classes', 'courses', 'academic_years'] as $tabel) {
            $this->db->query("DELETE FROM {$tabel}");
        }
    }

    /**
     * @param array<string, mixed> $over
     *
     * @return array<string, mixed>
     */
    protected function user(string $peran, string $nama, array $over = []): array
    {
        return Fixtures::user($this->idn, $over + ['global_role' => $peran, 'nama' => $nama, 'mfa_enabled' => $peran === 'superadmin' ? 1 : 0]);
    }

    /**
     * Klien yang sudah masuk sebagai $user (sesi dibuat langsung di identity_test).
     *
     * @param array<string, mixed> $user
     */
    protected function sebagai(array $user): Http
    {
        $http = new Http((string) getenv('APP_TES_URL'));
        $http->cookies['lokandita_sid'] = Fixtures::session($this->idn, (string) $user['id']);

        return $http;
    }

    protected function tamu(): Http
    {
        return new Http((string) getenv('APP_TES_URL'));
    }

    /**
     * Token CSRF klien ini, dari halaman beranda.
     */
    protected function csrf(Http $http): string
    {
        return preg_match('/data-csrf="([^"]+)"/', $http->get('/beranda')->body, $m) === 1 ? $m[1] : '';
    }

    protected function tahun(string $label, string $status = 'active', string $mulai = '2026-08-01', string $akhir = '2027-01-31'): int
    {
        Fixtures::insert($this->db, 'academic_years', [
            'label' => $label, 'starts_on' => $mulai, 'ends_on' => $akhir, 'status' => $status,
            'created_at' => gmdate('Y-m-d H:i:s'), 'updated_at' => gmdate('Y-m-d H:i:s'),
        ]);

        return (int) $this->db->insert_id;
    }

    /**
     * @param array<string, mixed> $pengampu
     */
    protected function matkul(string $kode, string $nama, array $pengampu): int
    {
        Fixtures::insert($this->db, 'courses', [
            'code' => $kode, 'name' => $nama, 'owner_id' => $pengampu['id'],
            'created_at' => gmdate('Y-m-d H:i:s'), 'updated_at' => gmdate('Y-m-d H:i:s'),
        ]);

        return (int) $this->db->insert_id;
    }

    protected function kelas(int $matkul, int $tahun, string $nama, ?int $kapasitas = null): int
    {
        Fixtures::insert($this->db, 'classes', [
            'course_id' => $matkul, 'academic_year_id' => $tahun, 'name' => $nama, 'capacity' => $kapasitas,
            'created_at' => gmdate('Y-m-d H:i:s'), 'updated_at' => gmdate('Y-m-d H:i:s'),
        ]);

        return (int) $this->db->insert_id;
    }

    /**
     * @param array<string, mixed> $dosen
     */
    protected function ajar(int $kelas, array $dosen): void
    {
        Fixtures::insert($this->db, 'class_teachers', [
            'class_id' => $kelas, 'user_id' => $dosen['id'], 'assigned_by' => 'uji', 'assigned_at' => gmdate('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @param array<string, mixed> $mahasiswa
     */
    protected function daftar(array $mahasiswa, int $kelas, string $status = 'AKTIF'): int
    {
        $k = $this->row("SELECT course_id, academic_year_id FROM classes WHERE id = {$kelas}");
        Fixtures::insert($this->db, 'enrollments', [
            'user_id' => $mahasiswa['id'], 'course_id' => $k['course_id'], 'academic_year_id' => $k['academic_year_id'],
            'class_id' => $kelas, 'status' => $status, 'created_by' => 'uji',
            'created_at' => gmdate('Y-m-d H:i:s'), 'updated_at' => gmdate('Y-m-d H:i:s'),
        ]);

        return (int) $this->db->insert_id;
    }

    /**
     * Pertemuan dengan konsep tambahannya.
     *
     * @param list<string> $konsep
     */
    protected function pertemuan(int $matkul, int $nomor, string $bahasa = 'cpp', array $konsep = [], string $judul = 'Pertemuan uji'): int
    {
        Fixtures::insert($this->db, 'meetings', [
            'course_id' => $matkul, 'number' => $nomor, 'title' => $judul, 'language' => $bahasa, 'enabled' => 1,
            'created_at' => gmdate('Y-m-d H:i:s'), 'updated_at' => gmdate('Y-m-d H:i:s'),
        ]);
        $id = (int) $this->db->insert_id;
        foreach ($konsep as $kode) {
            Fixtures::insert($this->db, 'meeting_concepts', ['meeting_id' => $id, 'concept_code' => $kode]);
        }

        return $id;
    }

    /**
     * Baris judge_jobs yang sudah selesai, milik $pembuat (null = pekerjaan sistem). Tidak diantrekan.
     *
     * @param array<string, mixed>|null $pembuat
     */
    protected function job(?array $pembuat, string $status = 'DONE'): string
    {
        $id = Fixtures::uuid();
        Fixtures::insert($this->db, 'judge_jobs', [
            'id' => $id, 'kind' => 'RUN', 'priority' => 'normal', 'status' => $status,
            'payload' => '{"language":"c","source":"SUMBER-RAHASIA","stdin":"MASUKAN-RAHASIA"}',
            'progress' => '{"state":"done","current":1,"total":1}',
            'result' => '{"verdict":"OK","stdout":"hasil\\n"}',
            'created_by' => $pembuat['id'] ?? null, 'created_at' => gmdate('Y-m-d H:i:s'),
        ]);

        return $id;
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function row(string $sql): ?array
    {
        return $this->db->query($sql)->fetch_assoc();
    }

    protected function hitung(string $sql): int
    {
        return (int) $this->db->query($sql)->fetch_row()[0];
    }

    /**
     * Dunia uji baku: dua mata kuliah, tiga kelas, dan satu orang untuk setiap peran lingkup.
     *
     *   UJX101 (pengampuX): kelas XA (pengajarA, mahasiswa mhsA), kelas XB (pengajarB, mahasiswa mhsB)
     *   UJY201 (pengampuY): kelas YA (tanpa pengajar, mahasiswa mhsY)
     *
     * @return array<string, mixed>
     */
    protected function dunia(): array
    {
        $d = [
            'superadmin' => $this->user('superadmin', 'Admin Uji'),
            'pengampuX'  => $this->user('dosen', 'Pengampu Xaverius'),
            'pengampuY'  => $this->user('dosen', 'Pengampu Yohana'),
            'pengajarA'  => $this->user('dosen', 'Pengajar Aminah'),
            'pengajarB'  => $this->user('dosen', 'Pengajar Bambang'),
            'mhsA'       => $this->user('mahasiswa', 'Mahasiswa Ani'),
            'mhsB'       => $this->user('mahasiswa', 'Mahasiswa Budi'),
            'mhsY'       => $this->user('mahasiswa', 'Mahasiswa Yudi'),
        ];
        $d['tahun']   = $this->tahun('2026/2027 Ganjil');
        $d['matkulX'] = $this->matkul('UJX101', 'Uji Pemrograman X', $d['pengampuX']);
        $d['matkulY'] = $this->matkul('UJY201', 'Uji Pemrograman Y', $d['pengampuY']);
        $d['kelasXA'] = $this->kelas($d['matkulX'], $d['tahun'], 'XA');
        $d['kelasXB'] = $this->kelas($d['matkulX'], $d['tahun'], 'XB');
        $d['kelasYA'] = $this->kelas($d['matkulY'], $d['tahun'], 'YA');
        $this->ajar($d['kelasXA'], $d['pengajarA']);
        $this->ajar($d['kelasXB'], $d['pengajarB']);
        $d['daftarA'] = $this->daftar($d['mhsA'], $d['kelasXA']);
        $d['daftarB'] = $this->daftar($d['mhsB'], $d['kelasXB']);
        $d['daftarY'] = $this->daftar($d['mhsY'], $d['kelasYA']);
        $d['jobA']    = $this->job($d['mhsA']);
        // Pertemuan 1 dan 2 mata kuliah X (C++), pertemuan 1 mata kuliah Y (Python).
        $d['pertemuanX1'] = $this->pertemuan($d['matkulX'], 1, 'cpp', ['IO_DASAR', 'VARIABEL_TIPE', 'ARITMETIKA']);
        $d['pertemuanX2'] = $this->pertemuan($d['matkulX'], 2, 'cpp', ['KONVERSI_TIPE']);
        $d['pertemuanY1'] = $this->pertemuan($d['matkulY'], 1, 'python', ['IO_DASAR', 'VARIABEL_TIPE', 'ARITMETIKA']);

        return $d;
    }
}
