<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Antrean pekerjaan judge — KONTRAK.md §6. Basis data adalah sumber kebenaran;
 * Redis hanya membawa id baris ini.
 *
 * created_by adalah users.id (LokaAuth) dan NULL untuk pekerjaan sistem
 * (misalnya cek smoke), yang karena itu tidak bisa dibaca pengguna mana pun.
 *
 * Worker masuk sebagai `judge_worker`, yang hanya boleh SELECT dan UPDATE pada
 * tabel ini (infra/mysql-hak-judge.sh).
 */
final class BuatJudgeJobs extends Migration
{
    public function up(): void
    {
        $this->db->query('
            CREATE TABLE judge_jobs (
                id              CHAR(36) NOT NULL,
                kind            ENUM(\'RUN\',\'SUBMIT\',\'GENERATE\',\'VALIDATE\',\'ANALYZE\') NOT NULL,
                priority        ENUM(\'normal\',\'low\') NOT NULL DEFAULT \'normal\',
                status          ENUM(\'QUEUED\',\'RUNNING\',\'DONE\',\'FAILED\') NOT NULL DEFAULT \'QUEUED\',
                payload         JSON NOT NULL,
                progress        JSON NULL,
                result          JSON NULL,
                attempts        INT UNSIGNED NOT NULL DEFAULT 0,
                idempotency_key VARCHAR(100) NULL,
                created_by      CHAR(36) NULL,
                created_at      DATETIME NOT NULL,
                started_at      DATETIME NULL,
                finished_at     DATETIME NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uq_judge_jobs_idempotensi (idempotency_key),
                KEY ix_judge_jobs_status (status, created_at),
                KEY ix_judge_jobs_pembuat (created_by, created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci');
    }

    public function down(): void
    {
        // Sengaja tidak bisa dibatalkan: tabel ini kelak memuat riwayat penilaian mahasiswa.
        throw new \RuntimeException('Migrasi judge_jobs tidak bisa dibatalkan.');
    }
}
