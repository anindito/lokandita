<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Teks halaman publik Portal yang diubah superadmin, dan log auditnya — KONTRAK.md §15.
 *
 * `teks` hanya berisi halaman yang PERNAH diubah; selebihnya tetap dibaca dari berkas
 * bahasa. `audit_log` hanya ditambah: di situlah susunan kata yang lama bisa ditemukan lagi.
 *
 * Isi disimpan sebagai teks JSON di kolom MEDIUMTEXT, bukan tipe JSON: MySQL mengurutkan
 * ulang kunci objek JSON, sedangkan urutan kunci di sini adalah urutan teks di halaman.
 */
final class BuatTeksDanAudit extends Migration
{
    private const OPSI = 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci';

    public function up(): void
    {
        $this->db->query('
            CREATE TABLE teks (
                halaman    VARCHAR(20) NOT NULL,
                bahasa     ENUM(\'id\',\'en\',\'ar\') NOT NULL,
                isi        MEDIUMTEXT NOT NULL,
                updated_by CHAR(36) NOT NULL,
                updated_at DATETIME NOT NULL,
                PRIMARY KEY (halaman, bahasa)
            ) ' . self::OPSI);

        $this->db->query('
            CREATE TABLE audit_log (
                id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                actor_id   CHAR(36)     NOT NULL,
                action     VARCHAR(60)  NOT NULL,
                halaman    VARCHAR(20)  NULL,
                bahasa     VARCHAR(2)   NULL,
                sebelum    MEDIUMTEXT   NULL,
                sesudah    MEDIUMTEXT   NULL,
                ip         VARCHAR(45)  NULL,
                user_agent VARCHAR(255) NULL,
                created_at DATETIME     NOT NULL,
                PRIMARY KEY (id),
                KEY ix_audit_created (created_at)
            ) ' . self::OPSI);
    }

    public function down(): void
    {
        // Sengaja tidak bisa dibatalkan: tabel ini memuat teks yang ditulis pemilik dan log auditnya.
        throw new \RuntimeException('Migrasi teks Portal tidak bisa dibatalkan.');
    }
}
