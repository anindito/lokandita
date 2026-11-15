<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Struktur akademik LokaKode — SPEC.md §5. Semua waktu disimpan dalam UTC.
 *
 * Aturan yang dijaga oleh basis data sendiri, bukan hanya oleh kode:
 *   - paling banyak satu tahun ajaran aktif (kolom satu_aktif);
 *   - satu pendaftaran per mahasiswa per mata kuliah per tahun ajaran;
 *   - kelas sebuah pendaftaran selalu milik mata kuliah dan tahun ajaran
 *     pendaftaran itu (kunci asing gabungan), jadi pindah kelas tidak bisa
 *     menyeberang ke mata kuliah lain;
 *   - kelas yang masih punya pendaftaran tidak bisa dihapus.
 *
 * user_id, owner_id dan sejenisnya adalah users.id di skema identity (LokaAuth);
 * tidak ada kunci asing lintas skema.
 */
final class BuatSkemaAkademik extends Migration
{
    private const OPSI = 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci';

    public function up(): void
    {
        $this->db->query('
            CREATE TABLE academic_years (
                id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                label      VARCHAR(40) NOT NULL,
                starts_on  DATE        NOT NULL,
                ends_on    DATE        NOT NULL,
                status     ENUM(\'active\',\'archived\') NOT NULL DEFAULT \'archived\',
                satu_aktif TINYINT GENERATED ALWAYS AS (IF(status = \'active\', 1, NULL)) STORED,
                created_at DATETIME    NOT NULL,
                updated_at DATETIME    NOT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uq_academic_years_label (label),
                UNIQUE KEY uq_academic_years_satu_aktif (satu_aktif),
                CONSTRAINT ck_academic_years_rentang CHECK (ends_on > starts_on)
            ) ' . self::OPSI);

        $this->db->query('
            CREATE TABLE courses (
                id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                code       VARCHAR(20)  NOT NULL,
                name       VARCHAR(190) NOT NULL,
                owner_id   CHAR(36)     NOT NULL,
                created_at DATETIME     NOT NULL,
                updated_at DATETIME     NOT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uq_courses_code (code),
                KEY ix_courses_owner (owner_id)
            ) ' . self::OPSI);

        $this->db->query('
            CREATE TABLE classes (
                id               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                course_id        BIGINT UNSIGNED NOT NULL,
                academic_year_id BIGINT UNSIGNED NOT NULL,
                name             VARCHAR(60)  NOT NULL,
                capacity         INT UNSIGNED NULL,
                archived_at      DATETIME     NULL,
                created_at       DATETIME     NOT NULL,
                updated_at       DATETIME     NOT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uq_classes_nama (course_id, academic_year_id, name),
                UNIQUE KEY uq_classes_untuk_fk (id, course_id, academic_year_id),
                KEY ix_classes_tahun (academic_year_id),
                CONSTRAINT fk_classes_course FOREIGN KEY (course_id) REFERENCES courses (id),
                CONSTRAINT fk_classes_tahun FOREIGN KEY (academic_year_id) REFERENCES academic_years (id)
            ) ' . self::OPSI);

        $this->db->query('
            CREATE TABLE class_teachers (
                class_id    BIGINT UNSIGNED NOT NULL,
                user_id     CHAR(36) NOT NULL,
                assigned_by CHAR(36) NOT NULL,
                assigned_at DATETIME NOT NULL,
                PRIMARY KEY (class_id, user_id),
                KEY ix_class_teachers_user (user_id),
                CONSTRAINT fk_class_teachers_class FOREIGN KEY (class_id) REFERENCES classes (id) ON DELETE CASCADE
            ) ' . self::OPSI);

        $this->db->query('
            CREATE TABLE enrollments (
                id               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                user_id          CHAR(36) NOT NULL,
                course_id        BIGINT UNSIGNED NOT NULL,
                academic_year_id BIGINT UNSIGNED NOT NULL,
                class_id         BIGINT UNSIGNED NOT NULL,
                status           ENUM(\'AKTIF\',\'LULUS\',\'MENGUNDURKAN_DIRI\',\'DIPINDAHKAN\') NOT NULL DEFAULT \'AKTIF\',
                graduated_on     DATE     NULL,
                created_by       CHAR(36) NOT NULL,
                created_at       DATETIME NOT NULL,
                updated_at       DATETIME NOT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uq_enrollments_satu (user_id, course_id, academic_year_id),
                KEY ix_enrollments_kelas (class_id, status),
                CONSTRAINT fk_enrollments_kelas FOREIGN KEY (class_id, course_id, academic_year_id)
                    REFERENCES classes (id, course_id, academic_year_id)
            ) ' . self::OPSI);

        // Riwayat pindah kelas: pendaftarannya tetap satu baris, perpindahannya dicatat di sini.
        $this->db->query('
            CREATE TABLE enrollment_moves (
                id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                enrollment_id BIGINT UNSIGNED NOT NULL,
                from_class_id BIGINT UNSIGNED NOT NULL,
                to_class_id   BIGINT UNSIGNED NOT NULL,
                moved_by      CHAR(36) NOT NULL,
                moved_at      DATETIME NOT NULL,
                PRIMARY KEY (id),
                KEY ix_enrollment_moves_pendaftaran (enrollment_id, id),
                CONSTRAINT fk_enrollment_moves_pendaftaran FOREIGN KEY (enrollment_id) REFERENCES enrollments (id),
                CONSTRAINT fk_enrollment_moves_dari FOREIGN KEY (from_class_id) REFERENCES classes (id),
                CONSTRAINT fk_enrollment_moves_ke FOREIGN KEY (to_class_id) REFERENCES classes (id)
            ) ' . self::OPSI);

        // Log audit LokaKode: hanya ditambah, ditulis dalam transaksi yang sama dengan perubahannya.
        // target_tipe + target_id menunjuk baris di skema ini atau users.id di LokaAuth.
        $this->db->query('
            CREATE TABLE audit_log (
                id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                actor_id    CHAR(36)     NOT NULL,
                action      VARCHAR(60)  NOT NULL,
                target_tipe VARCHAR(30)  NULL,
                target_id   VARCHAR(36)  NULL,
                course_id   BIGINT UNSIGNED NULL,
                class_id    BIGINT UNSIGNED NULL,
                detail      JSON         NULL,
                ip          VARCHAR(45)  NULL,
                user_agent  VARCHAR(255) NULL,
                created_at  DATETIME     NOT NULL,
                PRIMARY KEY (id),
                KEY ix_audit_created (created_at),
                KEY ix_audit_actor (actor_id, created_at),
                KEY ix_audit_action (action, created_at),
                KEY ix_audit_target (target_tipe, target_id, created_at)
            ) ' . self::OPSI);
    }

    public function down(): void
    {
        // Sengaja kosong: tidak ada penghapusan permanen kelas berisi data atau baris audit (CLAUDE.md).
        throw new \RuntimeException('Migrasi skema akademik LokaKode tidak bisa dibatalkan.');
    }
}
