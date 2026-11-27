<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tahap U5 — kendali dosen, nilai dan rilis (SPEC-LOKAUJIAN.md §7.3, §10, §11; KONTRAK.md §24).
 *
 *   attempts  + score, scored_at        skor objektif TERSIMPAN, selalu dihitung ulang penuh;
 *             + reopen_until            tenggat pengerjaan yang dibuka ulang (menggantikan tenggat biasa);
 *             + reopened_minutes        jumlah menit semua buka ulang (untuk CSV);
 *   sessions  + release_level, released_at, released_by   rilis hasil PER KELAS;
 *   time_grants                         catatan setiap tambahan waktu dan buka ulang. Baris untuk
 *                                       mahasiswa yang belum punya pengerjaan diterapkan saat ia mulai.
 */
final class BuatKendaliNilai extends Migration
{
    private const OPSI = 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci';

    public function up(): void
    {
        $this->db->query('
            ALTER TABLE attempts
                ADD COLUMN score            DECIMAL(8,2) NULL,
                ADD COLUMN scored_at        DATETIME     NULL,
                ADD COLUMN reopen_until     DATETIME     NULL,
                ADD COLUMN reopened_minutes INT UNSIGNED NOT NULL DEFAULT 0');
        $this->db->query("
            ALTER TABLE sessions
                ADD COLUMN release_level ENUM('nilai','nilai_dan_kunci') NULL,
                ADD COLUMN released_at   DATETIME NULL,
                ADD COLUMN released_by   CHAR(36) NULL");
        $this->db->query("
            CREATE TABLE time_grants (
                id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                session_id BIGINT UNSIGNED NOT NULL,
                user_id    CHAR(36)     NOT NULL,
                kind       ENUM('tambahan','buka_ulang') NOT NULL,
                minutes    INT UNSIGNED NOT NULL,
                reason     VARCHAR(500) NULL,
                until_at   DATETIME     NULL,
                created_by CHAR(36)     NOT NULL,
                created_at DATETIME     NOT NULL,
                PRIMARY KEY (id),
                KEY ix_hibah_sesi (session_id, user_id),
                CONSTRAINT fk_hibah_sesi FOREIGN KEY (session_id) REFERENCES sessions (id) ON DELETE CASCADE
            ) " . self::OPSI);
    }

    public function down(): void
    {
        // Sengaja kosong.
    }
}
