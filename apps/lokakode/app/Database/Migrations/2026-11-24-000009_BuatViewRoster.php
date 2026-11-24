<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * View roster akademik (KONTRAK.md §18): struktur akademik SPEC.md §5 seperti yang DIBACA
 * aplikasi lain (LokaUjian). View-nya adalah kontrak; tabel di baliknya bukan.
 *
 * Hanya menambah lima view; tidak ada tabel atau baris yang berubah, dan LokaKode sendiri
 * tetap membaca dan menulis tabelnya seperti sebelumnya.
 *
 * SQL SECURITY DEFINER (pembuatnya lokakode_app): pembaca cukup diberi SELECT pada view,
 * tanpa hak apa pun pada tabel. Haknya diberikan infra/mysql-hak-roster.sh kepada
 * `roster_reader`, sesudah migrasi ini.
 *
 * Nama dan urutan kolom di sini harus sama dengan tabel di KONTRAK.md §18. Saat tabel-tabel
 * ini pindah ke inti Lokandita (tahap A1), hanya definisi view yang berubah.
 */
final class BuatViewRoster extends Migration
{
    private const VIEW = [
        'roster_academic_years' => 'SELECT id, label, starts_on, ends_on, status FROM academic_years',
        'roster_courses'        => 'SELECT id, code, name, owner_id FROM courses',
        'roster_classes'        => 'SELECT id, course_id, academic_year_id, name, archived_at FROM classes',
        'roster_class_teachers' => 'SELECT class_id, user_id FROM class_teachers',
        'roster_enrollments'    => 'SELECT id, user_id, course_id, academic_year_id, class_id, status, graduated_on FROM enrollments',
    ];

    public function up(): void
    {
        foreach (self::VIEW as $nama => $pilih) {
            $this->db->query("CREATE SQL SECURITY DEFINER VIEW {$nama} AS {$pilih}");
        }
    }

    public function down(): void
    {
        foreach (array_keys(self::VIEW) as $nama) {
            $this->db->query("DROP VIEW {$nama}");
        }
    }
}
