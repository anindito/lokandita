<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tahap U4 — Safe Exam Browser LokaUjian (SPEC-LOKAUJIAN.md §9.1; KONTRAK.md §23).
 *
 * Konfigurasi SEB LokaUjian adalah PER UJIAN/KUIS (keputusan pemilik 2026-11-27), bukan per mata
 * kuliah seperti di LokaKode. Bentuk kedua tabel sama dengan KONTRAK.md §8, kecuali kolom
 * pemiliknya: exam_id. seb_exam_keys sengaja bernama dan berbentuk sama, karena pemeriksaan
 * header di paket bersama (Lokandita\Auth\Seb\Seb::periksa) membaca kunci dari tabel itu.
 */
final class BuatSeb extends Migration
{
    private const OPSI = 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci';

    public function up(): void
    {
        $this->db->query('
            CREATE TABLE seb_configs (
                id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                exam_id    BIGINT UNSIGNED NOT NULL,
                version    INT UNSIGNED NOT NULL,
                config_enc MEDIUMBLOB   NOT NULL,
                config_key CHAR(64)     NOT NULL,
                created_by CHAR(36)     NOT NULL,
                created_at DATETIME     NOT NULL,
                retired_at DATETIME     NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uq_seb_versi (exam_id, version),
                KEY ix_seb_berlaku (exam_id, retired_at),
                CONSTRAINT fk_seb_exam FOREIGN KEY (exam_id) REFERENCES exams (id) ON DELETE CASCADE
            ) ' . self::OPSI);
        $this->db->query('
            CREATE TABLE seb_exam_keys (
                id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                seb_config_id BIGINT UNSIGNED NOT NULL,
                exam_key      CHAR(64)     NOT NULL,
                created_by    CHAR(36)     NOT NULL,
                created_at    DATETIME     NOT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uq_seb_kunci (seb_config_id, exam_key),
                CONSTRAINT fk_seb_kunci_konfig FOREIGN KEY (seb_config_id) REFERENCES seb_configs (id) ON DELETE CASCADE
            ) ' . self::OPSI);
    }

    public function down(): void
    {
        // Sengaja kosong.
    }
}
