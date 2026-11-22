<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tahap 10b — integritas dan komunikasi (SPEC.md §13.2, §14); KONTRAK.md §17.
 *
 *   notifications    pemberitahuan di dalam aplikasi untuk satu pengguna. Tiga jenis saja:
 *                    pertemuan dibuka, 24 jam sebelum ditutup, kata sandi direset.
 *                    UNIQUE(user_id, kind, ref) membuat pembuatannya aman diulang.
 *   clarifications   pertanyaan mahasiswa tentang satu soal. Jawabannya pribadi (hanya penanya)
 *                    atau diterbitkan ke kelas penanya. class_id = kelas penanya saat bertanya.
 *   announcements    pengumuman untuk satu kelas. Tidak disunting dan tidak dihapus;
 *                    "tarik" mengisi withdrawn_at sehingga mahasiswa tidak melihatnya lagi.
 *
 * integrity_events tidak berubah bentuk: jenis baru `typing_anomaly` hanya nilai baru kolom kind.
 * Kemiripan kode (SPEC.md §13.2) TIDAK dibangun — keputusan pemilik 2026-11-22.
 */
final class BuatIntegritasDanKomunikasi extends Migration
{
    private const OPSI = 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci';

    public function up(): void
    {
        $this->db->query('
            CREATE TABLE notifications (
                id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                user_id    CHAR(36) NOT NULL,
                kind       VARCHAR(40) NOT NULL,
                ref        VARCHAR(80) NOT NULL,
                data       JSON NULL,
                created_at DATETIME NOT NULL,
                read_at    DATETIME NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uq_notifications_satu (user_id, kind, ref),
                KEY ix_notifications_pengguna (user_id, read_at, id),
                CONSTRAINT ck_notifications_jenis CHECK (kind IN (\'pertemuan_dibuka\', \'tutup_24jam\', \'sandi_direset\'))
            ) ' . self::OPSI);

        $this->db->query('
            CREATE TABLE clarifications (
                id                 BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                meeting_problem_id BIGINT UNSIGNED NOT NULL,
                class_id           BIGINT UNSIGNED NOT NULL,
                user_id            CHAR(36) NOT NULL,
                question           TEXT NOT NULL,
                created_at         DATETIME NOT NULL,
                answer             TEXT NULL,
                answered_by        CHAR(36) NULL,
                answered_at        DATETIME NULL,
                visibility         ENUM(\'private\',\'class\') NOT NULL DEFAULT \'private\',
                published_at       DATETIME NULL,
                PRIMARY KEY (id),
                KEY ix_clarifications_kelas (class_id, answered_at, id),
                KEY ix_clarifications_soal (meeting_problem_id, class_id, visibility),
                KEY ix_clarifications_penanya (user_id, meeting_problem_id),
                CONSTRAINT fk_clarifications_mp FOREIGN KEY (meeting_problem_id) REFERENCES meeting_problems (id),
                CONSTRAINT fk_clarifications_kelas FOREIGN KEY (class_id) REFERENCES classes (id),
                CONSTRAINT ck_clarifications_jawaban CHECK ((answer IS NULL) = (answered_by IS NULL) AND (answer IS NULL) = (answered_at IS NULL)),
                CONSTRAINT ck_clarifications_terbit CHECK ((visibility = \'class\') = (published_at IS NOT NULL)),
                CONSTRAINT ck_clarifications_terbit_berjawab CHECK (visibility = \'private\' OR answer IS NOT NULL)
            ) ' . self::OPSI);

        $this->db->query('
            CREATE TABLE announcements (
                id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                class_id     BIGINT UNSIGNED NOT NULL,
                author_id    CHAR(36) NOT NULL,
                title        VARCHAR(150) NOT NULL,
                body         TEXT NOT NULL,
                created_at   DATETIME NOT NULL,
                withdrawn_at DATETIME NULL,
                withdrawn_by CHAR(36) NULL,
                PRIMARY KEY (id),
                KEY ix_announcements_kelas (class_id, withdrawn_at, id),
                CONSTRAINT fk_announcements_kelas FOREIGN KEY (class_id) REFERENCES classes (id),
                CONSTRAINT ck_announcements_tarik CHECK ((withdrawn_at IS NULL) = (withdrawn_by IS NULL))
            ) ' . self::OPSI);
    }

    public function down(): void
    {
        $this->db->query('DROP TABLE announcements');
        $this->db->query('DROP TABLE clarifications');
        $this->db->query('DROP TABLE notifications');
    }
}
