<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tahap 9b — kebijakan terlambat dan latihan sesudah tutup (SPEC.md §6, §7); KONTRAK.md §6.
 *
 *   meeting_class_windows.late_until   sampai kapan kiriman terlambat diterima
 *                                      (NULL = sampai jendela ditutup manual)
 *   submissions.late_factor            faktor yang berlaku SAAT kiriman dibuat; nilai
 *                                      kiriman tidak berubah bila pengajar kemudian
 *                                      mengubah faktor jendelanya
 *   submissions.practice               1 = latihan sesudah tutup: dinilai judge dan
 *                                      ditampilkan kepada mahasiswa, tetapi tidak pernah
 *                                      masuk nilai, jumlah percobaan, papan peringkat
 *                                      atau analitik
 *
 * Kolom late_policy, late_factor, practice_after_close, anonymize_leaderboard dan
 * submissions.late sudah ada sejak tahap 9a.
 */
final class TambahTelatDanLatihanBebas extends Migration
{
    public function up(): void
    {
        $this->db->query('
            ALTER TABLE meeting_class_windows
                ADD COLUMN late_until DATETIME NULL AFTER late_factor,
                ADD CONSTRAINT ck_windows_telat CHECK (late_until IS NULL OR close_at IS NULL OR late_until > close_at)');

        $this->db->query('
            ALTER TABLE submissions
                ADD COLUMN late_factor DECIMAL(3,2) NULL AFTER late,
                ADD COLUMN practice    TINYINT(1) NOT NULL DEFAULT 0 AFTER late_factor,
                ADD CONSTRAINT ck_submissions_telat CHECK ((late = 0 AND late_factor IS NULL) OR (late = 1 AND late_factor BETWEEN 0 AND 1)),
                ADD CONSTRAINT ck_submissions_latihan CHECK (practice = 0 OR late = 0)');
    }

    public function down(): void
    {
        // Sengaja tidak bisa dibatalkan: kolom ini menentukan nilai kiriman yang sudah ada.
        throw new \RuntimeException('Migrasi telat dan latihan bebas tidak bisa dibatalkan.');
    }
}
