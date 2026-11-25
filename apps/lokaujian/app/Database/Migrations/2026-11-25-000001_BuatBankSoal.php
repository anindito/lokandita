<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tahap U2 — bank soal (SPEC-LOKAUJIAN.md §5, §5.4, §6.3); KONTRAK.md §20.
 *
 *   audit_log         hanya bertambah; ditulis dalam transaksi yang sama dengan perubahannya.
 *   images            satu baris per BERKAS gambar buatan server di volume (/srv/media/soal).
 *                     Teks alternatif tidak di sini: ia milik soal, kelompok atau opsi yang
 *                     memakai gambar itu, sehingga satu berkas bisa dipakai dengan teks berbeda.
 *   question_groups   kelompok soal: teks bersama dan/atau satu gambar.
 *   questions         soal bank. group_id + position bila soal itu anggota kelompok.
 *   question_options  opsi pilihan ganda, 2–6, tepat satu benar (dijaga App\Libraries\Aturan).
 *   exams             ujian. Tahap U2 hanya mengenal status 'draf'; terbit dan arsip tahap U3.
 *   exam_items        butir ujian berurutan: satu soal lepas ATAU satu kelompok.
 *
 * course_id adalah id roster (KONTRAK.md §18) dan user id adalah users.id LokaAuth: tanpa kunci
 * asing lintas skema. content_hash = SHA-256 isi soal/kelompok dalam bentuk baku; impor memakainya
 * untuk memakai ulang soal yang sama, bukan menggandakannya.
 */
final class BuatBankSoal extends Migration
{
    private const OPSI = 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci';

    public function up(): void
    {
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

        $this->db->query("
            CREATE TABLE images (
                id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                course_id   BIGINT UNSIGNED NOT NULL,
                kunci       CHAR(32)     NOT NULL,
                jenis       ENUM('png','jpeg') NOT NULL,
                lebar       INT UNSIGNED NOT NULL,
                tinggi      INT UNSIGNED NOT NULL,
                byte        INT UNSIGNED NOT NULL,
                sha256      CHAR(64)     NOT NULL,
                uploaded_by CHAR(36)     NOT NULL,
                uploaded_at DATETIME     NOT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uq_images_kunci (kunci),
                UNIQUE KEY uq_images_isi (course_id, sha256)
            ) " . self::OPSI);

        $this->db->query('
            CREATE TABLE question_groups (
                id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                course_id    BIGINT UNSIGNED NOT NULL,
                text         MEDIUMTEXT   NOT NULL,
                image_id     BIGINT UNSIGNED NULL,
                image_alt    VARCHAR(300) NULL,
                content_hash CHAR(64)     NOT NULL,
                archived_at  DATETIME     NULL,
                created_by   CHAR(36)     NOT NULL,
                created_at   DATETIME     NOT NULL,
                updated_by   CHAR(36)     NOT NULL,
                updated_at   DATETIME     NOT NULL,
                PRIMARY KEY (id),
                KEY ix_groups_matkul (course_id, archived_at, id),
                KEY ix_groups_isi (course_id, content_hash),
                CONSTRAINT fk_groups_image FOREIGN KEY (image_id) REFERENCES images (id),
                CONSTRAINT ck_groups_alt CHECK ((image_id IS NULL) = (image_alt IS NULL))
            ) ' . self::OPSI);

        $this->db->query("
            CREATE TABLE questions (
                id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                course_id    BIGINT UNSIGNED NOT NULL,
                group_id     BIGINT UNSIGNED NULL,
                position     SMALLINT UNSIGNED NULL,
                type         ENUM('benar_salah','pilihan_ganda','isian','uraian') NOT NULL,
                text         TEXT         NOT NULL,
                points       DECIMAL(5,2) NOT NULL DEFAULT 1.00,
                answer       TINYINT(1)   NULL,
                image_id     BIGINT UNSIGNED NULL,
                image_alt    VARCHAR(300) NULL,
                content_hash CHAR(64)     NOT NULL,
                archived_at  DATETIME     NULL,
                created_by   CHAR(36)     NOT NULL,
                created_at   DATETIME     NOT NULL,
                updated_by   CHAR(36)     NOT NULL,
                updated_at   DATETIME     NOT NULL,
                PRIMARY KEY (id),
                KEY ix_questions_matkul (course_id, group_id, archived_at, id),
                KEY ix_questions_isi (course_id, content_hash),
                UNIQUE KEY uq_questions_urutan (group_id, position),
                CONSTRAINT fk_questions_group FOREIGN KEY (group_id) REFERENCES question_groups (id),
                CONSTRAINT fk_questions_image FOREIGN KEY (image_id) REFERENCES images (id),
                CONSTRAINT ck_questions_alt CHECK ((image_id IS NULL) = (image_alt IS NULL)),
                CONSTRAINT ck_questions_urutan CHECK ((group_id IS NULL) = (position IS NULL)),
                CONSTRAINT ck_questions_kunci CHECK ((type = 'benar_salah') = (answer IS NOT NULL)),
                CONSTRAINT ck_questions_poin CHECK (points >= 0.25 AND points <= 100)
            ) " . self::OPSI);

        $this->db->query('
            CREATE TABLE question_options (
                id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                question_id BIGINT UNSIGNED NOT NULL,
                position    TINYINT UNSIGNED NOT NULL,
                text        VARCHAR(1000) NOT NULL,
                correct     TINYINT(1)   NOT NULL DEFAULT 0,
                image_id    BIGINT UNSIGNED NULL,
                image_alt   VARCHAR(300) NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uq_options_urutan (question_id, position),
                CONSTRAINT fk_options_question FOREIGN KEY (question_id) REFERENCES questions (id) ON DELETE CASCADE,
                CONSTRAINT fk_options_image FOREIGN KEY (image_id) REFERENCES images (id),
                CONSTRAINT ck_options_alt CHECK ((image_id IS NULL) = (image_alt IS NULL))
            ) ' . self::OPSI);

        $this->db->query("
            CREATE TABLE exams (
                id                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                course_id         BIGINT UNSIGNED NOT NULL,
                title             VARCHAR(190) NOT NULL,
                kind              ENUM('ujian','kuis') NOT NULL DEFAULT 'ujian',
                instructions      TEXT         NOT NULL,
                mode              ENUM('per_mahasiswa','dikendalikan_dosen') NOT NULL,
                duration_minutes  SMALLINT UNSIGNED NULL,
                shuffle_questions TINYINT(1)   NOT NULL DEFAULT 0,
                shuffle_options   TINYINT(1)   NOT NULL DEFAULT 0,
                status            ENUM('draf','terbit','arsip') NOT NULL DEFAULT 'draf',
                created_by        CHAR(36)     NOT NULL,
                created_at        DATETIME     NOT NULL,
                updated_at        DATETIME     NOT NULL,
                PRIMARY KEY (id),
                KEY ix_exams_matkul (course_id, status, id),
                CONSTRAINT ck_exams_durasi CHECK (duration_minutes IS NULL OR (duration_minutes BETWEEN 5 AND 600))
            ) " . self::OPSI);

        $this->db->query('
            CREATE TABLE exam_items (
                id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                exam_id     BIGINT UNSIGNED NOT NULL,
                position    SMALLINT UNSIGNED NOT NULL,
                question_id BIGINT UNSIGNED NULL,
                group_id    BIGINT UNSIGNED NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uq_items_urutan (exam_id, position),
                KEY ix_items_soal (question_id),
                KEY ix_items_kelompok (group_id),
                CONSTRAINT fk_items_exam FOREIGN KEY (exam_id) REFERENCES exams (id) ON DELETE CASCADE,
                CONSTRAINT fk_items_question FOREIGN KEY (question_id) REFERENCES questions (id),
                CONSTRAINT fk_items_group FOREIGN KEY (group_id) REFERENCES question_groups (id),
                CONSTRAINT ck_items_satu CHECK ((question_id IS NULL) <> (group_id IS NULL))
            ) ' . self::OPSI);
    }

    public function down(): void
    {
        // Sengaja kosong: tidak ada penghapusan permanen baris audit (CLAUDE.md).
    }
}
