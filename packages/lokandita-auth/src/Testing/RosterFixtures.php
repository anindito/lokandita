<?php

declare(strict_types=1);

namespace Lokandita\Auth\Testing;

/**
 * Baris uji roster akademik (KONTRAK.md §18), untuk tes aplikasi mana pun yang membaca atau
 * mengelola roster. Ada SATU kali di sini supaya setiap aplikasi menyiapkan roster dengan cara
 * yang sama.
 *
 * Koneksinya harus koneksi ke skema roster tes (roster_test) dengan hak tulis: sejak tahap A2
 * hanya auth_app yang punya hak itu. TestSchema::connect() menolak skema yang bukan *_test.
 */
final class RosterFixtures
{
    /** Urutan aman untuk mengosongkan: anak lebih dulu. */
    public const TABEL = ['enrollment_moves', 'enrollments', 'class_teachers', 'classes', 'courses', 'academic_years'];

    public static function kosongkan(\mysqli $roster): void
    {
        foreach (self::TABEL as $t) {
            $roster->query("DELETE FROM `{$t}`");
        }
    }

    /**
     * @param array<string, mixed> $over
     */
    public static function tahun(\mysqli $roster, array $over = []): int
    {
        Fixtures::insert($roster, 'academic_years', $over + [
            'label' => 'Tahun ' . bin2hex(random_bytes(4)), 'starts_on' => '2026-08-01', 'ends_on' => '2027-01-31',
            'status' => 'active', 'created_at' => self::kini(), 'updated_at' => self::kini(),
        ]);

        return (int) $roster->insert_id;
    }

    /**
     * @param array<string, mixed> $over
     */
    public static function matkul(\mysqli $roster, string $pengampuId, array $over = []): int
    {
        Fixtures::insert($roster, 'courses', $over + [
            'code' => 'MK' . strtoupper(bin2hex(random_bytes(3))), 'name' => 'Mata Kuliah Uji', 'owner_id' => $pengampuId,
            'created_at' => self::kini(), 'updated_at' => self::kini(),
        ]);

        return (int) $roster->insert_id;
    }

    /**
     * @param array<string, mixed> $over
     */
    public static function kelas(\mysqli $roster, int $matkulId, int $tahunId, array $over = []): int
    {
        Fixtures::insert($roster, 'classes', $over + [
            'course_id' => $matkulId, 'academic_year_id' => $tahunId, 'name' => 'K' . bin2hex(random_bytes(3)),
            'created_at' => self::kini(), 'updated_at' => self::kini(),
        ]);

        return (int) $roster->insert_id;
    }

    public static function pengajar(\mysqli $roster, int $kelasId, string $userId): void
    {
        Fixtures::insert($roster, 'class_teachers', [
            'class_id' => $kelasId, 'user_id' => $userId, 'assigned_by' => $userId, 'assigned_at' => self::kini(),
        ]);
    }

    /**
     * Mendaftarkan seorang mahasiswa ke kelas; mata kuliah dan tahunnya dibaca dari kelas itu.
     *
     * @param array<string, mixed> $over
     */
    public static function daftar(\mysqli $roster, int $kelasId, string $userId, array $over = []): int
    {
        $k = $roster->query("SELECT course_id, academic_year_id FROM classes WHERE id = {$kelasId}")->fetch_assoc();
        Fixtures::insert($roster, 'enrollments', $over + [
            'user_id' => $userId, 'course_id' => $k['course_id'], 'academic_year_id' => $k['academic_year_id'], 'class_id' => $kelasId,
            'status' => 'AKTIF', 'created_by' => $userId, 'created_at' => self::kini(), 'updated_at' => self::kini(),
        ]);

        return (int) $roster->insert_id;
    }

    private static function kini(): string
    {
        return gmdate('Y-m-d H:i:s');
    }
}
