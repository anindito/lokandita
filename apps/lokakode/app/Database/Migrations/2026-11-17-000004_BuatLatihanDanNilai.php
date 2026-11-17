<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tahap 9a — pengaturan pertemuan yang tersisa (SPEC.md §6), jendela per kelas,
 * kiriman, nilai, draf dan peristiwa integritas (SPEC.md §7, §11); KONTRAK.md §6.
 * Semua waktu disimpan dalam UTC.
 *
 * Aturan yang dijaga basis data sendiri:
 *   - satu jendela per pertemuan per kelas, dan kelasnya harus kelas mata
 *     kuliah pertemuan itu (kunci asing gabungan);
 *   - waktu tutup sesudah waktu buka;
 *   - satu baris nilai per mahasiswa per soal, dan per mahasiswa per pertemuan;
 *   - satu draf per mahasiswa per soal;
 *   - kiriman tidak bisa dihapus selama nilainya masih merujuknya.
 *
 * problem_scores dan meeting_scores adalah TURUNAN dari submissions: selalu
 * dihitung ulang seluruhnya oleh App\Libraries\Penilaian, tidak pernah ditambah
 * sedikit demi sedikit. Menghapus isinya dan menghitung ulang memberi hasil sama.
 *
 * Kolom late_policy, late_factor, practice_after_close dan anonymize_leaderboard
 * sudah ada dengan nilai bawaannya, tetapi baru dipakai di tahap 9b.
 *
 * Judge worker tidak mendapat hak atas tabel baru mana pun: hasil SUBMIT ditulis
 * ke judge_jobs dan dipindahkan ke submissions oleh web.
 */
final class BuatLatihanDanNilai extends Migration
{
    private const OPSI = 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci';

    public function up(): void
    {
        // Komposisi minimum bawaan 8 / 8 / 6 / 6 / 4 (SPEC.md §6).
        // published_at NULL = belum diterbitkan: mahasiswa tidak melihat apa pun.
        $this->db->query('
            ALTER TABLE meetings
                ADD COLUMN target_points         INT UNSIGNED NOT NULL DEFAULT 60 AFTER enabled,
                ADD COLUMN min_mudah_sekali      TINYINT UNSIGNED NOT NULL DEFAULT 8 AFTER target_points,
                ADD COLUMN min_mudah             TINYINT UNSIGNED NOT NULL DEFAULT 8 AFTER min_mudah_sekali,
                ADD COLUMN min_mudah_sedang      TINYINT UNSIGNED NOT NULL DEFAULT 6 AFTER min_mudah,
                ADD COLUMN min_sedang            TINYINT UNSIGNED NOT NULL DEFAULT 6 AFTER min_mudah_sedang,
                ADD COLUMN min_sulit             TINYINT UNSIGNED NOT NULL DEFAULT 4 AFTER min_sedang,
                ADD COLUMN starter_template      MEDIUMTEXT NULL AFTER min_sulit,
                ADD COLUMN compiler_flags        VARCHAR(190) NOT NULL DEFAULT \'\' AFTER starter_template,
                ADD COLUMN autocomplete          TINYINT(1) NOT NULL DEFAULT 1 AFTER compiler_flags,
                ADD COLUMN anonymize_leaderboard TINYINT(1) NOT NULL DEFAULT 0 AFTER autocomplete,
                ADD COLUMN practice_after_close  TINYINT(1) NOT NULL DEFAULT 0 AFTER anonymize_leaderboard,
                ADD COLUMN published_at          DATETIME NULL AFTER practice_after_close,
                ADD COLUMN published_by          CHAR(36) NULL AFTER published_at,
                ADD UNIQUE KEY uq_meetings_untuk_fk (id, course_id),
                ADD CONSTRAINT ck_meetings_target CHECK (target_points BETWEEN 1 AND 1000)');

        $this->db->query('ALTER TABLE classes ADD UNIQUE KEY uq_classes_matkul_fk (id, course_id)');

        // Jendela sebuah pertemuan untuk satu kelas (SPEC.md §6). Terbuka bila
        // override = open, atau override = none dan open_at <= kini < close_at.
        $this->db->query('
            CREATE TABLE meeting_class_windows (
                id                    BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                meeting_id            BIGINT UNSIGNED NOT NULL,
                class_id              BIGINT UNSIGNED NOT NULL,
                course_id             BIGINT UNSIGNED NOT NULL,
                open_at               DATETIME NULL,
                close_at              DATETIME NULL,
                override              ENUM(\'none\',\'open\',\'closed\') NOT NULL DEFAULT \'none\',
                late_policy           ENUM(\'reject\',\'multiplier\') NOT NULL DEFAULT \'reject\',
                late_factor           DECIMAL(3,2) NOT NULL DEFAULT 0.50,
                post_close_visibility ENUM(\'hidden\',\'review_only\') NOT NULL DEFAULT \'hidden\',
                updated_by            CHAR(36) NOT NULL,
                created_at            DATETIME NOT NULL,
                updated_at            DATETIME NOT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uq_windows_satu (meeting_id, class_id),
                KEY ix_windows_kelas (class_id),
                CONSTRAINT fk_windows_meeting FOREIGN KEY (meeting_id, course_id) REFERENCES meetings (id, course_id),
                CONSTRAINT fk_windows_kelas FOREIGN KEY (class_id, course_id) REFERENCES classes (id, course_id),
                CONSTRAINT ck_windows_rentang CHECK (open_at IS NULL OR close_at IS NULL OR close_at > open_at),
                CONSTRAINT ck_windows_faktor CHECK (late_factor BETWEEN 0 AND 1)
            ) ' . self::OPSI);

        // Satu baris per Submit. Hasilnya diisi web dari judge_jobs.result
        // (App\Libraries\Penilaian::selesaikan); tidak pernah memuat isi tes.
        $this->db->query('
            CREATE TABLE submissions (
                id                 BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                user_id            CHAR(36) NOT NULL,
                meeting_problem_id BIGINT UNSIGNED NOT NULL,
                problem_version_id BIGINT UNSIGNED NOT NULL,
                instance_id        BIGINT UNSIGNED NOT NULL,
                language           ENUM(\'c\',\'cpp\',\'python\') NOT NULL,
                source             MEDIUMTEXT NOT NULL,
                job_id             CHAR(36) NULL,
                status             ENUM(\'MENUNGGU\',\'DINILAI\',\'GAGAL\') NOT NULL DEFAULT \'MENUNGGU\',
                verdict            VARCHAR(20) NULL,
                passed             TINYINT UNSIGNED NULL,
                total              TINYINT UNSIGNED NULL,
                score              DECIMAL(5,2) NULL,
                late               TINYINT(1) NOT NULL DEFAULT 0,
                duration_ms        INT UNSIGNED NULL,
                compile_log        MEDIUMTEXT NULL,
                tests              JSON NULL,
                missing_concepts   JSON NULL,
                created_at         DATETIME NOT NULL,
                judged_at          DATETIME NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uq_submissions_job (job_id),
                KEY ix_submissions_milik (user_id, meeting_problem_id, id),
                KEY ix_submissions_status (status, created_at),
                KEY ix_submissions_soal (meeting_problem_id, status),
                CONSTRAINT fk_submissions_mp FOREIGN KEY (meeting_problem_id) REFERENCES meeting_problems (id),
                CONSTRAINT fk_submissions_version FOREIGN KEY (problem_version_id) REFERENCES problem_versions (id),
                CONSTRAINT fk_submissions_instance FOREIGN KEY (instance_id) REFERENCES problem_instances (id)
            ) ' . self::OPSI);

        // Nilai terbaik per mahasiswa per soal (SPEC.md §7). `points` adalah poin
        // tingkat kesulitan versi yang dikerjakan mahasiswa itu.
        $this->db->query('
            CREATE TABLE problem_scores (
                user_id            CHAR(36) NOT NULL,
                meeting_problem_id BIGINT UNSIGNED NOT NULL,
                meeting_id         BIGINT UNSIGNED NOT NULL,
                difficulty         ENUM(\'MUDAH_SEKALI\',\'MUDAH\',\'MUDAH_SEDANG\',\'SEDANG\',\'SULIT\') NOT NULL,
                points             TINYINT UNSIGNED NOT NULL,
                best_score         DECIMAL(5,2) NOT NULL,
                best_submission_id BIGINT UNSIGNED NOT NULL,
                attempts           INT UNSIGNED NOT NULL,
                reached_at         DATETIME NOT NULL,
                updated_at         DATETIME NOT NULL,
                PRIMARY KEY (user_id, meeting_problem_id),
                KEY ix_problem_scores_pertemuan (meeting_id, user_id),
                KEY ix_problem_scores_soal (meeting_problem_id),
                CONSTRAINT fk_problem_scores_mp FOREIGN KEY (meeting_problem_id) REFERENCES meeting_problems (id),
                CONSTRAINT fk_problem_scores_meeting FOREIGN KEY (meeting_id) REFERENCES meetings (id),
                CONSTRAINT fk_problem_scores_submission FOREIGN KEY (best_submission_id) REFERENCES submissions (id)
            ) ' . self::OPSI);

        // Nilai pertemuan per mahasiswa (SPEC.md §7):
        //   counted_points = jumlah nilai terbaik, hanya 4 soal MUDAH_SEKALI terbaik yang dihitung
        //   score          = min(counted_points / target, 1) × 100
        //   surplus        = total_points − min(counted_points, target)
        // reached_at = saat nilai `score` yang sekarang pertama kali tercapai (untuk peringkat, tahap 9b).
        $this->db->query('
            CREATE TABLE meeting_scores (
                user_id           CHAR(36) NOT NULL,
                meeting_id        BIGINT UNSIGNED NOT NULL,
                total_points      DECIMAL(7,2) NOT NULL,
                counted_points    DECIMAL(7,2) NOT NULL,
                target_points     INT UNSIGNED NOT NULL,
                score             DECIMAL(5,2) NOT NULL,
                surplus           DECIMAL(7,2) NOT NULL,
                very_easy_counted TINYINT UNSIGNED NOT NULL,
                submissions       INT UNSIGNED NOT NULL,
                reached_at        DATETIME NULL,
                updated_at        DATETIME NOT NULL,
                PRIMARY KEY (user_id, meeting_id),
                KEY ix_meeting_scores_pertemuan (meeting_id, score),
                CONSTRAINT fk_meeting_scores_meeting FOREIGN KEY (meeting_id) REFERENCES meetings (id)
            ) ' . self::OPSI);

        // Draf editor, disimpan otomatis tiap 5 detik (SPEC.md §11).
        $this->db->query('
            CREATE TABLE drafts (
                user_id            CHAR(36) NOT NULL,
                meeting_problem_id BIGINT UNSIGNED NOT NULL,
                source             MEDIUMTEXT NOT NULL,
                updated_at         DATETIME NOT NULL,
                PRIMARY KEY (user_id, meeting_problem_id),
                CONSTRAINT fk_drafts_mp FOREIGN KEY (meeting_problem_id) REFERENCES meeting_problems (id)
            ) ' . self::OPSI);

        // Peristiwa integritas (SPEC.md §11, §13). Tahap 9a hanya mencatat percobaan
        // tempel; jenis lain dan halaman pengajarnya menyusul di tahap 10. Hanya ditambah.
        $this->db->query('
            CREATE TABLE integrity_events (
                id                 BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                user_id            CHAR(36) NOT NULL,
                kind               VARCHAR(40) NOT NULL,
                meeting_problem_id BIGINT UNSIGNED NULL,
                detail             JSON NULL,
                ip                 VARCHAR(45) NULL,
                user_agent         VARCHAR(255) NULL,
                created_at         DATETIME NOT NULL,
                PRIMARY KEY (id),
                KEY ix_integrity_pengguna (user_id, created_at),
                KEY ix_integrity_soal (meeting_problem_id, created_at),
                CONSTRAINT fk_integrity_mp FOREIGN KEY (meeting_problem_id) REFERENCES meeting_problems (id)
            ) ' . self::OPSI);

        // Ukuran panel halaman soal, per pengguna (SPEC.md §11).
        $this->db->query('
            CREATE TABLE ui_prefs (
                user_id    CHAR(36) NOT NULL,
                panels     JSON NOT NULL,
                updated_at DATETIME NOT NULL,
                PRIMARY KEY (user_id)
            ) ' . self::OPSI);
    }

    public function down(): void
    {
        // Sengaja tidak bisa dibatalkan: tabel ini memuat kiriman dan nilai mahasiswa.
        throw new \RuntimeException('Migrasi latihan dan nilai tidak bisa dibatalkan.');
    }
}
