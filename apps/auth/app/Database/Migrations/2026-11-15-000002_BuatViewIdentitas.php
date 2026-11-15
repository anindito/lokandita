<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * View baca-saja untuk aplikasi klien — KONTRAK.md §3.
 *
 * Hak SELECT untuk `identity_reader` diberikan oleh infra/mysql-hak-reader.sh
 * (sebagai root), karena auth_app tidak berhak memberi hak.
 */
final class BuatViewIdentitas extends Migration
{
    public function up(): void
    {
        // Sesi yang menganggur lebih dari 30 menit tidak terlihat bila pengguna
        // memakai keluar-otomatis (wajib untuk mahasiswa, SPEC.md §4.4).
        $this->db->query('
            CREATE OR REPLACE SQL SECURITY DEFINER VIEW session_identity AS
            SELECT s.token_hash, s.expires_at, s.last_seen_at,
                   u.id AS user_id, u.nomor_induk, u.nama, u.global_role,
                   u.locale, u.idle_logout, u.paste_allowed, u.must_change_password
            FROM sessions s JOIN users u ON u.id = s.user_id
            WHERE s.revoked_at IS NULL
              AND s.expires_at > UTC_TIMESTAMP()
              AND u.status = \'active\'
              AND (u.idle_logout = 0 OR s.last_seen_at > UTC_TIMESTAMP() - INTERVAL 30 MINUTE)');

        $this->db->query('
            CREATE OR REPLACE SQL SECURITY DEFINER VIEW user_directory AS
            SELECT id, nomor_induk, nama, global_role, status FROM users');
    }

    public function down(): void
    {
        $this->db->query('DROP VIEW IF EXISTS session_identity');
        $this->db->query('DROP VIEW IF EXISTS user_directory');
    }
}
