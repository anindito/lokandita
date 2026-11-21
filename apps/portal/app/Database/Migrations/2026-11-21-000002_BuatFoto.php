<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Foto pembuka halaman depan Portal yang diunggah superadmin — KONTRAK.md §16.
 *
 * `foto` hanya ditambah: setiap foto yang pernah diunggah tetap ada dan bisa ditayangkan
 * lagi. `foto_tayang` menunjuk foto yang sedang tayang; tanpa baris, atau dengan foto_id
 * NULL, halaman depan memakai foto bawaan yang ada di dalam image.
 * Berkasnya sendiri ada di volume lokandita_portal_media, bukan di basis data.
 */
final class BuatFoto extends Migration
{
    private const OPSI = 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci';

    public function up(): void
    {
        $this->db->query('
            CREATE TABLE foto (
                id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
                kunci        CHAR(32)     NOT NULL,
                nama_asli    VARCHAR(255) NOT NULL,
                jenis        VARCHAR(10)  NOT NULL,
                lebar        INT UNSIGNED NOT NULL,
                tinggi       INT UNSIGNED NOT NULL,
                alt_id       VARCHAR(300) NOT NULL,
                alt_en       VARCHAR(300) NOT NULL,
                alt_ar       VARCHAR(300) NOT NULL,
                sumber       VARCHAR(200) NOT NULL,
                lisensi      VARCHAR(200) NOT NULL,
                berhak       TINYINT(1)   NOT NULL,
                uploaded_by  CHAR(36)     NOT NULL,
                uploaded_at  DATETIME     NOT NULL,
                published_at DATETIME     NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uq_foto_kunci (kunci)
            ) ' . self::OPSI);

        $this->db->query('
            CREATE TABLE foto_tayang (
                slot       VARCHAR(20)  NOT NULL,
                foto_id    INT UNSIGNED NULL,
                updated_by CHAR(36)     NOT NULL,
                updated_at DATETIME     NOT NULL,
                PRIMARY KEY (slot),
                CONSTRAINT fk_tayang_foto FOREIGN KEY (foto_id) REFERENCES foto (id)
            ) ' . self::OPSI);
    }

    public function down(): void
    {
        // Sengaja tidak bisa dibatalkan: tabel ini mencatat foto yang diunggah pemilik beserta izinnya.
        throw new \RuntimeException('Migrasi foto Portal tidak bisa dibatalkan.');
    }
}
