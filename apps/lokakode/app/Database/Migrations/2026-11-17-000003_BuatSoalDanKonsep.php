<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Konsep, pertemuan (bentuk minimal), bank soal dan impor — SPEC.md §8, §9;
 * KONTRAK.md §6 dan §7. Semua waktu disimpan dalam UTC.
 *
 * Aturan yang dijaga basis data sendiri:
 *   - satu nomor pertemuan per mata kuliah, nomor 1–16;
 *   - slug soal unik di dalam satu pertemuan;
 *   - nomor versi unik per soal;
 *   - satu instans per mahasiswa per soal pertemuan (membuka ulang tidak
 *     pernah membuat instans baru);
 *   - soal, versi dan instans tidak bisa dihapus selama masih dirujuk.
 *
 * Pertemuan di sini baru berisi apa yang dibutuhkan importir: nomor, judul,
 * bahasa, konsep, aktif/tidak. Target poin, komposisi, jendela per kelas dan
 * penerbitan (SPEC.md §6) ditambahkan di tahap 9.
 *
 * Judge worker (`judge_worker`) hanya mendapat hak atas problem_versions
 * (baca) dan problem_instances (baca, sisip) — infra/mysql-hak-judge.sh.
 */
final class BuatSoalDanKonsep extends Migration
{
    private const OPSI = 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci';

    public function up(): void
    {
        // Katalog konsep adalah data (docs/PETA-KONSEP.md), bukan enum di kode.
        $this->db->query('
            CREATE TABLE concepts (
                code      VARCHAR(40)  NOT NULL,
                grup      VARCHAR(20)  NOT NULL,
                name      VARCHAR(120) NOT NULL,
                name_en   VARCHAR(120) NULL,
                name_ar   VARCHAR(120) NULL,
                languages SET(\'c\',\'cpp\',\'python\') NOT NULL,
                urutan    INT UNSIGNED NOT NULL,
                PRIMARY KEY (code)
            ) ' . self::OPSI);

        $this->db->query('
            CREATE TABLE meetings (
                id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                course_id  BIGINT UNSIGNED NOT NULL,
                number     TINYINT UNSIGNED NOT NULL,
                title      VARCHAR(190) NOT NULL,
                language   ENUM(\'c\',\'cpp\',\'python\') NOT NULL,
                enabled    TINYINT(1) NOT NULL DEFAULT 1,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uq_meetings_nomor (course_id, number),
                CONSTRAINT fk_meetings_course FOREIGN KEY (course_id) REFERENCES courses (id),
                CONSTRAINT ck_meetings_nomor CHECK (number BETWEEN 1 AND 16)
            ) ' . self::OPSI);

        // Konsep yang BARU diizinkan di pertemuan itu. Yang diizinkan seluruhnya =
        // konsep dasar mata kuliah + konsep semua pertemuan bernomor <= pertemuan ini.
        $this->db->query('
            CREATE TABLE meeting_concepts (
                meeting_id   BIGINT UNSIGNED NOT NULL,
                concept_code VARCHAR(40) NOT NULL,
                PRIMARY KEY (meeting_id, concept_code),
                CONSTRAINT fk_meeting_concepts_meeting FOREIGN KEY (meeting_id) REFERENCES meetings (id) ON DELETE CASCADE,
                CONSTRAINT fk_meeting_concepts_concept FOREIGN KEY (concept_code) REFERENCES concepts (code)
            ) ' . self::OPSI);

        // Konsep yang sudah dikuasai sebelum pertemuan 1 (PBO301: seluruh konsep DDP101).
        $this->db->query('
            CREATE TABLE course_base_concepts (
                course_id    BIGINT UNSIGNED NOT NULL,
                concept_code VARCHAR(40) NOT NULL,
                PRIMARY KEY (course_id, concept_code),
                CONSTRAINT fk_course_base_course FOREIGN KEY (course_id) REFERENCES courses (id) ON DELETE CASCADE,
                CONSTRAINT fk_course_base_concept FOREIGN KEY (concept_code) REFERENCES concepts (code)
            ) ' . self::OPSI);

        // Identitas soal di bank; isinya ada di problem_versions.
        $this->db->query('
            CREATE TABLE problems (
                id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                meeting_id BIGINT UNSIGNED NOT NULL,
                slug       VARCHAR(80) NOT NULL,
                created_at DATETIME NOT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uq_problems_slug (meeting_id, slug),
                CONSTRAINT fk_problems_meeting FOREIGN KEY (meeting_id) REFERENCES meetings (id)
            ) ' . self::OPSI);

        // Unggahan JSON, impor lewat CLI, dan suntingan satu soal (source = sunting).
        $this->db->query('
            CREATE TABLE imports (
                id          CHAR(36) NOT NULL,
                source      ENUM(\'web\',\'cli\',\'sunting\') NOT NULL,
                file_name   VARCHAR(190) NOT NULL,
                actor_id    CHAR(36) NOT NULL,
                course_id   BIGINT UNSIGNED NULL,
                meeting_id  BIGINT UNSIGNED NULL,
                status      ENUM(\'MENUNGGU\',\'SELESAI\',\'DITOLAK\') NOT NULL,
                file_errors JSON NULL,
                created_at  DATETIME NOT NULL,
                finished_at DATETIME NULL,
                PRIMARY KEY (id),
                KEY ix_imports_waktu (created_at),
                KEY ix_imports_pertemuan (meeting_id, created_at)
            ) ' . self::OPSI);

        $this->db->query('
            CREATE TABLE problem_versions (
                id                     BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                problem_id             BIGINT UNSIGNED NOT NULL,
                version                INT UNSIGNED NOT NULL,
                status                 ENUM(\'PENDING_REVIEW\',\'APPROVED\',\'REJECTED\') NOT NULL DEFAULT \'PENDING_REVIEW\',
                language               ENUM(\'c\',\'cpp\',\'python\') NOT NULL,
                difficulty             ENUM(\'MUDAH_SEKALI\',\'MUDAH\',\'MUDAH_SEDANG\',\'SEDANG\',\'SULIT\') NOT NULL,
                comparison_mode        ENUM(\'exact\',\'trimmed\',\'token\',\'float\') NOT NULL DEFAULT \'trimmed\',
                time_limit_ms          INT UNSIGNED NOT NULL,
                samples_count          TINYINT UNSIGNED NOT NULL,
                hidden_count           TINYINT UNSIGNED NOT NULL,
                params                 JSON NOT NULL,
                translations           JSON NOT NULL,
                generator              MEDIUMTEXT NOT NULL,
                reference_solution     MEDIUMTEXT NOT NULL,
                concepts               JSON NOT NULL,
                required_in_submission JSON NOT NULL,
                content_hash           CHAR(64) NOT NULL,
                import_id              CHAR(36) NULL,
                created_by             CHAR(36) NOT NULL,
                created_at             DATETIME NOT NULL,
                reviewed_by            CHAR(36) NULL,
                reviewed_at            DATETIME NULL,
                review_note            VARCHAR(500) NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uq_problem_versions_nomor (problem_id, version),
                KEY ix_problem_versions_status (status),
                CONSTRAINT fk_problem_versions_problem FOREIGN KEY (problem_id) REFERENCES problems (id),
                CONSTRAINT ck_problem_versions_contoh CHECK (samples_count BETWEEN 2 AND 3),
                CONSTRAINT ck_problem_versions_tersembunyi CHECK (hidden_count BETWEEN 10 AND 50)
            ) ' . self::OPSI);

        // Versi yang dipakai sebuah pertemuan: versi terakhir yang DISETUJUI.
        // Baris ini baru ada setelah persetujuan pertama.
        $this->db->query('
            CREATE TABLE meeting_problems (
                id                 BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                meeting_id         BIGINT UNSIGNED NOT NULL,
                problem_id         BIGINT UNSIGNED NOT NULL,
                problem_version_id BIGINT UNSIGNED NOT NULL,
                updated_at         DATETIME NOT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uq_meeting_problems_soal (problem_id),
                KEY ix_meeting_problems_pertemuan (meeting_id),
                CONSTRAINT fk_meeting_problems_meeting FOREIGN KEY (meeting_id) REFERENCES meetings (id),
                CONSTRAINT fk_meeting_problems_problem FOREIGN KEY (problem_id) REFERENCES problems (id),
                CONSTRAINT fk_meeting_problems_version FOREIGN KEY (problem_version_id) REFERENCES problem_versions (id)
            ) ' . self::OPSI);

        // Satu baris per soal di dalam sebuah impor. `data` memuat soal itu
        // sampai judge selesai memvalidasinya; baru sesudah itu versi disimpan.
        $this->db->query('
            CREATE TABLE import_items (
                id                 BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                import_id          CHAR(36) NOT NULL,
                urutan             INT UNSIGNED NOT NULL,
                slug               VARCHAR(80) NULL,
                json_line          INT UNSIGNED NULL,
                state              ENUM(\'MENUNGGU\',\'DITERIMA\',\'DITOLAK\',\'TIDAK_BERUBAH\') NOT NULL,
                job_id             CHAR(36) NULL,
                data               JSON NULL,
                reasons            JSON NULL,
                problem_version_id BIGINT UNSIGNED NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uq_import_items_urutan (import_id, urutan),
                CONSTRAINT fk_import_items_import FOREIGN KEY (import_id) REFERENCES imports (id)
            ) ' . self::OPSI);

        // Instans per mahasiswa (SPEC.md §8.2). Ditulis oleh judge worker, bukan
        // oleh web: masukan dan keluaran tes tidak pernah lewat judge_jobs.result,
        // yang bisa dibaca pembuat pekerjaannya.
        $this->db->query('
            CREATE TABLE problem_instances (
                id                 BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                user_id            CHAR(36) NOT NULL,
                meeting_problem_id BIGINT UNSIGNED NOT NULL,
                problem_version_id BIGINT UNSIGNED NOT NULL,
                seed               BIGINT UNSIGNED NOT NULL,
                tests              LONGTEXT NOT NULL,
                created_at         DATETIME NOT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uq_problem_instances_satu (user_id, meeting_problem_id),
                CONSTRAINT fk_problem_instances_mp FOREIGN KEY (meeting_problem_id) REFERENCES meeting_problems (id),
                CONSTRAINT fk_problem_instances_version FOREIGN KEY (problem_version_id) REFERENCES problem_versions (id)
            ) ' . self::OPSI);

        $this->muatKatalog();
    }

    /**
     * Katalog awal dari app/Data/konsep.json (sumbernya docs/PETA-KONSEP.md §1).
     */
    private function muatKatalog(): void
    {
        $katalog = json_decode((string) file_get_contents(APPPATH . 'Data/konsep.json'), true, 16, JSON_THROW_ON_ERROR);
        foreach ($katalog as $urutan => $k) {
            $this->db->query(
                'INSERT INTO concepts (code, grup, name, name_en, name_ar, languages, urutan) VALUES (?, ?, ?, ?, ?, ?, ?)',
                [$k['code'], $k['grup'], $k['name'], $k['name_en'], $k['name_ar'], implode(',', $k['languages']), $urutan + 1],
            );
        }
    }

    public function down(): void
    {
        // Sengaja tidak bisa dibatalkan: tabel ini memuat soal dan instans mahasiswa.
        throw new \RuntimeException('Migrasi soal dan konsep tidak bisa dibatalkan.');
    }
}
