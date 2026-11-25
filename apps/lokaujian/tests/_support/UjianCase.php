<?php

declare(strict_types=1);

namespace Tests\Support;

use Lokandita\Auth\Testing\Fixtures;
use Lokandita\Auth\Testing\Http;
use Lokandita\Auth\Testing\TestSchema;
use PHPUnit\Framework\TestCase;

/**
 * Dasar tes LokaUjian: HTTP sungguhan ke kontainer lokaujian-tes, yang membaca sesi dari
 * identity_test, berbicara dengan auth-tes, dan membaca roster lewat lima view di lokakode_test
 * sebagai roster_reader.
 *
 * Baris roster disiapkan tes lewat TES_ROSTER_TULIS_DSN (lokakode_app pada lokakode_test);
 * aplikasi yang diuji tidak memegang DSN itu (KONTRAK.md §12).
 */
abstract class UjianCase extends TestCase
{
    protected \mysqli $roster; // lokakode_test, sebagai lokakode_app — hanya untuk menyiapkan baris uji

    protected \mysqli $idn;    // identity_test

    protected function setUp(): void
    {
        $this->roster = TestSchema::connect((string) getenv('TES_ROSTER_TULIS_DSN'));
        $this->idn    = TestSchema::connect((string) getenv('TES_IDENTITY_DSN'));
        // Urutan mengikuti kunci asing. Hanya skema *_test (dijaga TestSchema).
        foreach (['enrollment_moves', 'enrollments', 'class_teachers', 'classes', 'courses', 'academic_years'] as $t) {
            $this->roster->query("DELETE FROM {$t}");
        }
    }

    /**
     * Pengguna dengan akses ke LokaUjian.
     *
     * @param array<string, mixed> $over
     *
     * @return array<string, mixed>
     */
    protected function user(string $peran, string $nama, array $over = []): array
    {
        return Fixtures::user($this->idn, $over + [
            'global_role' => $peran, 'nama' => $nama, 'mfa_enabled' => $peran === 'superadmin' ? 1 : 0,
            'apps' => ['portal', 'lokakode', 'lokaujian'],
        ]);
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

    private static function kini(): string
    {
        return gmdate('Y-m-d H:i:s');
    }

    protected function tahun(string $label, string $status = 'active', string $mulai = '2026-08-01', string $akhir = '2027-01-31'): int
    {
        Fixtures::insert($this->roster, 'academic_years', [
            'label' => $label, 'starts_on' => $mulai, 'ends_on' => $akhir, 'status' => $status,
            'created_at' => self::kini(), 'updated_at' => self::kini(),
        ]);

        return (int) $this->roster->insert_id;
    }

    /**
     * @param array<string, mixed> $pengampu
     */
    protected function matkul(string $kode, string $nama, array $pengampu): int
    {
        Fixtures::insert($this->roster, 'courses', [
            'code' => $kode, 'name' => $nama, 'owner_id' => $pengampu['id'],
            'created_at' => self::kini(), 'updated_at' => self::kini(),
        ]);

        return (int) $this->roster->insert_id;
    }

    protected function kelas(int $matkul, int $tahun, string $nama, bool $diarsipkan = false): int
    {
        Fixtures::insert($this->roster, 'classes', [
            'course_id' => $matkul, 'academic_year_id' => $tahun, 'name' => $nama,
            'archived_at' => $diarsipkan ? self::kini() : null,
            'created_at' => self::kini(), 'updated_at' => self::kini(),
        ]);

        return (int) $this->roster->insert_id;
    }

    /**
     * @param array<string, mixed> $dosen
     */
    protected function ajar(int $kelas, array $dosen): void
    {
        Fixtures::insert($this->roster, 'class_teachers', [
            'class_id' => $kelas, 'user_id' => $dosen['id'], 'assigned_by' => 'uji', 'assigned_at' => self::kini(),
        ]);
    }

    /**
     * @param array<string, mixed> $mahasiswa
     */
    protected function daftar(array $mahasiswa, int $kelas, string $status = 'AKTIF'): int
    {
        $k = $this->roster->query("SELECT course_id, academic_year_id FROM classes WHERE id = {$kelas}")->fetch_assoc();
        Fixtures::insert($this->roster, 'enrollments', [
            'user_id' => $mahasiswa['id'], 'course_id' => $k['course_id'], 'academic_year_id' => $k['academic_year_id'],
            'class_id' => $kelas, 'status' => $status, 'graduated_on' => $status === 'LULUS' ? '2026-11-01' : null,
            'created_by' => 'uji', 'created_at' => self::kini(), 'updated_at' => self::kini(),
        ]);

        return (int) $this->roster->insert_id;
    }

    /**
     * Isi halaman tanpa bagian yang memang berbeda di setiap permintaan (sisa sesi, token CSRF)
     * dan tanpa jalur yang diminta sendiri. Dipakai untuk membuktikan bahwa dua jawaban tidak
     * bisa dibedakan.
     *
     * PERHATIAN: Lokandita\Auth\Testing\Http mengembalikan DIRINYA SENDIRI. Salin isi jawaban
     * pertama (string) sebelum mengirim permintaan kedua.
     */
    protected static function inti(string $isi, string $jalur): string
    {
        $isi = (string) preg_replace(['/data-sesi-sisa="\d+"/', '/data-csrf="[0-9a-f]+"/', '/name="csrf" value="[0-9a-f]+"/'], ['', 'data-csrf=""', 'name="csrf" value=""'], $isi);

        return str_replace('value="' . $jalur . '"', 'value="JALUR"', $isi);
    }

    /**
     * Dunia uji: dua mata kuliah, masing-masing dengan pengampunya; mata kuliah X punya kelas XA
     * (pengajar A) dan XB (pengajar B), mata kuliah Y punya kelas YA. Ditambah yang TIDAK boleh
     * dihitung sebagai kelas seorang mahasiswa: kelas yang diarsipkan, tahun ajaran yang
     * diarsipkan, dan pendaftaran yang sudah lulus atau mengundurkan diri.
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
            'mhsLulus'   => $this->user('mahasiswa', 'Mahasiswa Lulusan'),
            'mhsMundur'  => $this->user('mahasiswa', 'Mahasiswa Mundur'),
            'mhsArsip'   => $this->user('mahasiswa', 'Mahasiswa Kelasarsip'),
            'mhsLama'    => $this->user('mahasiswa', 'Mahasiswa Tahunlama'),
        ];
        $d['tahun']     = $this->tahun('2026/2027 Ganjil');
        $d['tahunLama'] = $this->tahun('2025/2026 Genap', 'archived', '2026-02-01', '2026-07-31');
        $d['matkulX']   = $this->matkul('UJX101', 'Uji Ujian X', $d['pengampuX']);
        $d['matkulY']   = $this->matkul('UJY201', 'Uji Ujian Y', $d['pengampuY']);
        $d['kelasXA']   = $this->kelas($d['matkulX'], $d['tahun'], 'XA');
        $d['kelasXB']   = $this->kelas($d['matkulX'], $d['tahun'], 'XB');
        $d['kelasYA']   = $this->kelas($d['matkulY'], $d['tahun'], 'YA');
        $d['kelasXArsip'] = $this->kelas($d['matkulX'], $d['tahun'], 'XZ-arsip', true);
        $d['kelasXLama']  = $this->kelas($d['matkulX'], $d['tahunLama'], 'XL-lama');
        $this->ajar($d['kelasXA'], $d['pengajarA']);
        $this->ajar($d['kelasXB'], $d['pengajarB']);
        $this->ajar($d['kelasXLama'], $d['pengajarA']);
        $this->daftar($d['mhsA'], $d['kelasXA']);
        $this->daftar($d['mhsB'], $d['kelasXB']);
        $this->daftar($d['mhsY'], $d['kelasYA']);
        $this->daftar($d['mhsLulus'], $d['kelasXA'], 'LULUS');
        $this->daftar($d['mhsMundur'], $d['kelasXA'], 'MENGUNDURKAN_DIRI');
        $this->daftar($d['mhsArsip'], $d['kelasXArsip']);
        $this->daftar($d['mhsLama'], $d['kelasXLama']);

        return $d;
    }
}
