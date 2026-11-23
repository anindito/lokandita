<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Pertemuan berjenis ujian (SPEC.md §6 "Exam meetings"; KONTRAK.md §6).
 *
 *   meetings.kind                              'latihan' (bawaan) atau 'ujian'
 *   meeting_class_windows.scores_released_at   kapan nilai ujian dirilis untuk kelas itu;
 *                                              NULL = mahasiswa kelas itu belum melihat nilai apa pun
 *   meeting_class_windows.scores_released_by   siapa yang merilisnya
 *
 * Kedua kolom rilis hanya berarti untuk pertemuan ujian.
 */
final class TambahUjian extends Migration
{
    public function up(): void
    {
        $this->db->query("ALTER TABLE meetings ADD COLUMN kind ENUM('latihan', 'ujian') NOT NULL DEFAULT 'latihan' AFTER enabled");
        $this->db->query('
            ALTER TABLE meeting_class_windows
                ADD COLUMN scores_released_at DATETIME NULL AFTER post_close_visibility,
                ADD COLUMN scores_released_by CHAR(36) NULL AFTER scores_released_at');
    }

    public function down(): void
    {
        // Sengaja tidak bisa dibatalkan: jenis pertemuan menentukan cara nilai yang sudah ada dihitung.
        throw new \RuntimeException('Migrasi pertemuan ujian tidak bisa dibatalkan.');
    }
}
