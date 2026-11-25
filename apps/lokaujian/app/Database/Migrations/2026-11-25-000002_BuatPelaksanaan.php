<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tahap U3 — penerbitan dan pelaksanaan ujian (SPEC-LOKAUJIAN.md §6, §7, §8, §9.2); KONTRAK.md §22.
 *
 *   exam_groups, exam_questions, exam_options
 *                     SALINAN BEKU isi ujian, dibuat saat ujian diterbitkan. Sesudah itu suntingan
 *                     di bank tidak pernah sampai ke ujian. Menarik ujian ke draf menghapusnya.
 *   sessions          satu ujian terbit yang diberikan kepada satu kelas (paling banyak satu per
 *                     ujian per kelas).
 *   attempts          pengerjaan: satu per mahasiswa per ujian, dijaga UNIQUE(exam_id, user_id).
 *                     Tenggatnya TIDAK disimpan: selalu dihitung dari waktu (App\Libraries\Tenggat).
 *   answers           satu baris per pengerjaan per soal, dengan pencacah simpan.
 *   integrity_events  peristiwa integritas; tahap U3 mencatat paste_blocked dan page_taken_over.
 *
 * class_id, course_id dan user_id adalah id roster dan LokaAuth: tanpa kunci asing lintas skema.
 * Semua waktu UTC.
 */
final class BuatPelaksanaan extends Migration
{
    private const OPSI = 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci';

    public function up(): void
    {
        $this->db->query('ALTER TABLE exams ADD COLUMN published_at DATETIME NULL AFTER status, ADD COLUMN published_by CHAR(36) NULL AFTER published_at');

        $this->db->query('
            CREATE TABLE exam_groups (
                id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                exam_id         BIGINT UNSIGNED NOT NULL,
                text            MEDIUMTEXT   NOT NULL,
                image_id        BIGINT UNSIGNED NULL,
                image_alt       VARCHAR(300) NULL,
                source_group_id BIGINT UNSIGNED NULL,
                PRIMARY KEY (id),
                KEY ix_egroups_exam (exam_id),
                CONSTRAINT fk_egroups_exam FOREIGN KEY (exam_id) REFERENCES exams (id) ON DELETE CASCADE,
                CONSTRAINT fk_egroups_image FOREIGN KEY (image_id) REFERENCES images (id),
                CONSTRAINT ck_egroups_alt CHECK ((image_id IS NULL) = (image_alt IS NULL))
            ) ' . self::OPSI);
        $this->db->query("
            CREATE TABLE exam_questions (
                id                 BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                exam_id            BIGINT UNSIGNED NOT NULL,
                position           SMALLINT UNSIGNED NOT NULL,
                exam_group_id      BIGINT UNSIGNED NULL,
                type               ENUM('benar_salah','pilihan_ganda','isian','uraian') NOT NULL,
                text               TEXT         NOT NULL,
                points             DECIMAL(5,2) NOT NULL,
                answer             TINYINT(1)   NULL,
                image_id           BIGINT UNSIGNED NULL,
                image_alt          VARCHAR(300) NULL,
                source_question_id BIGINT UNSIGNED NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uq_equestions_urutan (exam_id, position),
                KEY ix_equestions_group (exam_group_id),
                CONSTRAINT fk_equestions_exam FOREIGN KEY (exam_id) REFERENCES exams (id) ON DELETE CASCADE,
                CONSTRAINT fk_equestions_group FOREIGN KEY (exam_group_id) REFERENCES exam_groups (id) ON DELETE CASCADE,
                CONSTRAINT fk_equestions_image FOREIGN KEY (image_id) REFERENCES images (id),
                CONSTRAINT ck_equestions_alt CHECK ((image_id IS NULL) = (image_alt IS NULL)),
                CONSTRAINT ck_equestions_kunci CHECK ((type = 'benar_salah') = (answer IS NOT NULL))
            ) " . self::OPSI);
        $this->db->query('
            CREATE TABLE exam_options (
                id               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                exam_question_id BIGINT UNSIGNED NOT NULL,
                position         TINYINT UNSIGNED NOT NULL,
                text             VARCHAR(1000) NOT NULL,
                correct          TINYINT(1)   NOT NULL DEFAULT 0,
                image_id         BIGINT UNSIGNED NULL,
                image_alt        VARCHAR(300) NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uq_eoptions_urutan (exam_question_id, position),
                CONSTRAINT fk_eoptions_question FOREIGN KEY (exam_question_id) REFERENCES exam_questions (id) ON DELETE CASCADE,
                CONSTRAINT fk_eoptions_image FOREIGN KEY (image_id) REFERENCES images (id),
                CONSTRAINT ck_eoptions_alt CHECK ((image_id IS NULL) = (image_alt IS NULL))
            ) ' . self::OPSI);

        // per_mahasiswa: terbuka untuk MULAI selama open_at <= kini < close_at (close_at NULL =
        // sampai ditutup). Membuka atau menutup dengan tangan mengubah kedua waktu itu.
        // dikendalikan_dosen: started_at diisi tombol Mulai; berakhir pada end_at, atau 8 jam
        // sesudah mulai bila end_at kosong. ended_by diisi tombol Akhiri.
        $this->db->query('
            CREATE TABLE sessions (
                id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                exam_id    BIGINT UNSIGNED NOT NULL,
                course_id  BIGINT UNSIGNED NOT NULL,
                class_id   BIGINT UNSIGNED NOT NULL,
                open_at    DATETIME NULL,
                close_at   DATETIME NULL,
                started_at DATETIME NULL,
                end_at     DATETIME NULL,
                ended_by   CHAR(36) NULL,
                created_by CHAR(36) NOT NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uq_sessions_ujian_kelas (exam_id, class_id),
                KEY ix_sessions_kelas (class_id),
                CONSTRAINT fk_sessions_exam FOREIGN KEY (exam_id) REFERENCES exams (id)
            ) ' . self::OPSI);

        // urutan: {"soal": [id exam_questions menurut urutan mahasiswa ini], "opsi": {"<id soal>": [id exam_options]}}
        // ditetapkan sekali saat pengerjaan dibuat. page_token: halaman yang sedang memegang
        // pengerjaan ini; halaman lama dengan token lain berhenti.
        $this->db->query("
            CREATE TABLE attempts (
                id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                session_id    BIGINT UNSIGNED NOT NULL,
                exam_id       BIGINT UNSIGNED NOT NULL,
                class_id      BIGINT UNSIGNED NOT NULL,
                user_id       CHAR(36)     NOT NULL,
                started_at    DATETIME     NOT NULL,
                extra_minutes INT UNSIGNED NOT NULL DEFAULT 0,
                submitted_at  DATETIME     NULL,
                submit_how    ENUM('mahasiswa','waktu_habis','diakhiri_dosen') NULL,
                urutan        JSON         NOT NULL,
                page_token    CHAR(32)     NOT NULL,
                last_saved_at DATETIME     NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uq_attempts_satu (exam_id, user_id),
                KEY ix_attempts_sesi (session_id, submitted_at),
                KEY ix_attempts_user (user_id),
                CONSTRAINT fk_attempts_session FOREIGN KEY (session_id) REFERENCES sessions (id),
                CONSTRAINT fk_attempts_exam FOREIGN KEY (exam_id) REFERENCES exams (id),
                CONSTRAINT ck_attempts_kumpul CHECK ((submitted_at IS NULL) = (submit_how IS NULL))
            ) " . self::OPSI);
        $this->db->query('
            CREATE TABLE answers (
                id               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                attempt_id       BIGINT UNSIGNED NOT NULL,
                exam_question_id BIGINT UNSIGNED NOT NULL,
                option_id        BIGINT UNSIGNED NULL,
                benar            TINYINT(1)   NULL,
                text             MEDIUMTEXT   NULL,
                ragu             TINYINT(1)   NOT NULL DEFAULT 0,
                counter          INT UNSIGNED NOT NULL,
                saved_at         DATETIME     NOT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uq_answers_satu (attempt_id, exam_question_id),
                CONSTRAINT fk_answers_attempt FOREIGN KEY (attempt_id) REFERENCES attempts (id),
                CONSTRAINT fk_answers_question FOREIGN KEY (exam_question_id) REFERENCES exam_questions (id),
                CONSTRAINT fk_answers_option FOREIGN KEY (option_id) REFERENCES exam_options (id)
            ) ' . self::OPSI);
        $this->db->query('
            CREATE TABLE integrity_events (
                id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                user_id    CHAR(36)     NOT NULL,
                attempt_id BIGINT UNSIGNED NULL,
                session_id BIGINT UNSIGNED NULL,
                kind       VARCHAR(30)  NOT NULL,
                detail     JSON         NULL,
                ip         VARCHAR(45)  NULL,
                user_agent VARCHAR(255) NULL,
                created_at DATETIME     NOT NULL,
                PRIMARY KEY (id),
                KEY ix_events_attempt (attempt_id, created_at),
                KEY ix_events_sesi (session_id, created_at),
                KEY ix_events_user (user_id, created_at)
            ) ' . self::OPSI);
    }

    public function down(): void
    {
        // Sengaja kosong: pengerjaan, jawaban dan peristiwa integritas tidak pernah dihapus permanen.
    }
}
