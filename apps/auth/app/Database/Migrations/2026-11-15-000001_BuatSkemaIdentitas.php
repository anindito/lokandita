<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tabel skema `identity` — KONTRAK.md §3. Semua waktu disimpan dalam UTC.
 *
 * SQL ditulis apa adanya supaya mudah dibandingkan dengan kontrak.
 */
final class BuatSkemaIdentitas extends Migration
{
    private const OPSI = 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci';

    public function up(): void
    {
        $this->db->query('
            CREATE TABLE users (
                id                   CHAR(36)     NOT NULL,
                nomor_induk          VARCHAR(40)  NOT NULL,
                email                VARCHAR(190) NULL,
                nama                 VARCHAR(190) NOT NULL,
                password_hash        VARCHAR(255) NOT NULL,
                must_change_password TINYINT(1)   NOT NULL DEFAULT 1,
                global_role          ENUM(\'superadmin\',\'dosen\',\'mahasiswa\') NOT NULL,
                status               ENUM(\'active\',\'suspended\',\'disabled\') NOT NULL DEFAULT \'active\',
                mfa_enabled          TINYINT(1)   NOT NULL DEFAULT 0,
                mfa_secret_enc       VARBINARY(255) NULL,
                failed_attempts      INT          NOT NULL DEFAULT 0,
                locked_until         DATETIME     NULL,
                idle_logout          TINYINT(1)   NOT NULL DEFAULT 0,
                locale               ENUM(\'id\',\'en\',\'ar\') NOT NULL DEFAULT \'id\',
                paste_allowed        TINYINT(1)   NOT NULL DEFAULT 0,
                last_login_at        DATETIME     NULL,
                created_by           CHAR(36)     NULL,
                created_at           DATETIME     NOT NULL,
                updated_at           DATETIME     NOT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uq_users_nomor_induk (nomor_induk),
                UNIQUE KEY uq_users_email (email),
                KEY ix_users_role_status (global_role, status),
                KEY ix_users_nama (nama)
            ) ' . self::OPSI);

        $this->db->query('
            CREATE TABLE app_access (
                user_id    CHAR(36) NOT NULL,
                app        ENUM(\'portal\',\'lokakode\',\'lokaujian\',\'lokasiber\') NOT NULL,
                enabled    TINYINT(1) NOT NULL DEFAULT 1,
                granted_by CHAR(36) NULL,
                granted_at DATETIME NOT NULL,
                PRIMARY KEY (user_id, app),
                CONSTRAINT fk_app_access_user FOREIGN KEY (user_id) REFERENCES users (id)
            ) ' . self::OPSI);

        $this->db->query('
            CREATE TABLE sessions (
                id           CHAR(36)     NOT NULL,
                token_hash   CHAR(64)     NOT NULL,
                user_id      CHAR(36)     NOT NULL,
                created_at   DATETIME     NOT NULL,
                expires_at   DATETIME     NOT NULL,
                last_seen_at DATETIME     NOT NULL,
                ip           VARCHAR(45)  NOT NULL,
                user_agent   VARCHAR(255) NOT NULL,
                revoked_at   DATETIME     NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uq_sessions_token_hash (token_hash),
                KEY ix_sessions_user (user_id, revoked_at),
                CONSTRAINT fk_sessions_user FOREIGN KEY (user_id) REFERENCES users (id)
            ) ' . self::OPSI);

        $this->db->query('
            CREATE TABLE password_history (
                id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                user_id       CHAR(36)     NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                created_at    DATETIME     NOT NULL,
                PRIMARY KEY (id),
                KEY ix_password_history_user (user_id, id),
                CONSTRAINT fk_password_history_user FOREIGN KEY (user_id) REFERENCES users (id)
            ) ' . self::OPSI);

        $this->db->query('
            CREATE TABLE mfa_backup_codes (
                id        BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                user_id   CHAR(36) NOT NULL,
                code_hash CHAR(64) NOT NULL,
                used_at   DATETIME NULL,
                PRIMARY KEY (id),
                KEY ix_mfa_backup_codes_user (user_id),
                CONSTRAINT fk_mfa_backup_codes_user FOREIGN KEY (user_id) REFERENCES users (id)
            ) ' . self::OPSI);

        $this->db->query('
            CREATE TABLE login_attempts (
                id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                nomor_induk VARCHAR(40) NOT NULL,
                ip          VARCHAR(45) NOT NULL,
                success     TINYINT(1)  NOT NULL,
                created_at  DATETIME    NOT NULL,
                PRIMARY KEY (id),
                KEY ix_login_attempts_nomor (nomor_induk, created_at),
                KEY ix_login_attempts_ip (ip, created_at)
            ) ' . self::OPSI);

        $this->db->query('
            CREATE TABLE audit_log (
                id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                actor_id   CHAR(36)     NULL,
                action     VARCHAR(60)  NOT NULL,
                target_id  CHAR(36)     NULL,
                app        VARCHAR(20)  NOT NULL,
                detail     JSON         NULL,
                ip         VARCHAR(45)  NULL,
                user_agent VARCHAR(255) NULL,
                created_at DATETIME     NOT NULL,
                PRIMARY KEY (id),
                KEY ix_audit_created (created_at),
                KEY ix_audit_actor (actor_id, created_at),
                KEY ix_audit_action (action, created_at),
                KEY ix_audit_target (target_id, created_at)
            ) ' . self::OPSI);
    }

    public function down(): void
    {
        // Sengaja kosong: tidak ada penghapusan permanen pengguna atau baris audit (CLAUDE.md).
        throw new \RuntimeException('Migrasi skema identity tidak bisa dibatalkan.');
    }
}
