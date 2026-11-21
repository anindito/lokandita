<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Batas waktu kata sandi awal (KONTRAK.md §13): sesudah waktu ini, kata sandi yang
 * masih wajib diganti tidak bisa lagi dipakai untuk login. NULL = tanpa batas.
 * Kolom ini tidak ikut di view untuk aplikasi klien.
 */
final class TambahBatasSandiAwal extends Migration
{
    public function up(): void
    {
        $this->db->query('ALTER TABLE users ADD COLUMN initial_password_expires_at DATETIME NULL AFTER must_change_password');
    }

    public function down(): void
    {
        $this->db->query('ALTER TABLE users DROP COLUMN initial_password_expires_at');
    }
}
