<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tahap 10a — Safe Exam Browser (SPEC.md §13.1); KONTRAK.md §8.
 *
 *   seb_configs     satu baris per konfigurasi yang pernah dibuat untuk sebuah mata kuliah.
 *                   Yang berlaku adalah baris dengan retired_at NULL (paling banyak satu per
 *                   mata kuliah); rotasi mengisi retired_at baris lama dan membuat baris baru.
 *                   config_enc = plist XML, dienkripsi dengan SEB_ENCRYPTION_KEY.
 *                   config_key = Config Key konfigurasi itu (hex SHA-256), dihitung server.
 *   seb_exam_keys   Browser Exam Key yang didaftarkan pengampu untuk satu konfigurasi.
 *                   Kunci menempel pada konfigurasinya, jadi rotasi otomatis mematikannya.
 */
final class BuatSeb extends Migration
{
    private const OPSI = 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci';

    public function up(): void
    {
        $this->db->query('
            CREATE TABLE seb_configs (
                id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                course_id   BIGINT UNSIGNED NOT NULL,
                version     INT UNSIGNED NOT NULL,
                config_enc  MEDIUMBLOB NOT NULL,
                config_key  CHAR(64) NOT NULL,
                created_by  CHAR(36) NOT NULL,
                created_at  DATETIME NOT NULL,
                retired_at  DATETIME NULL,
                satu_aktif  TINYINT GENERATED ALWAYS AS (IF(retired_at IS NULL, 1, NULL)) STORED,
                PRIMARY KEY (id),
                UNIQUE KEY uq_seb_configs_versi (course_id, version),
                UNIQUE KEY uq_seb_configs_satu_aktif (course_id, satu_aktif),
                CONSTRAINT fk_seb_configs_matkul FOREIGN KEY (course_id) REFERENCES courses (id)
            ) ' . self::OPSI);

        $this->db->query('
            CREATE TABLE seb_exam_keys (
                id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                seb_config_id BIGINT UNSIGNED NOT NULL,
                exam_key      CHAR(64) NOT NULL,
                created_by    CHAR(36) NOT NULL,
                created_at    DATETIME NOT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uq_seb_exam_keys (seb_config_id, exam_key),
                CONSTRAINT fk_seb_exam_keys_config FOREIGN KEY (seb_config_id) REFERENCES seb_configs (id)
            ) ' . self::OPSI);
    }

    public function down(): void
    {
        $this->db->query('DROP TABLE seb_exam_keys');
        $this->db->query('DROP TABLE seb_configs');
    }
}
