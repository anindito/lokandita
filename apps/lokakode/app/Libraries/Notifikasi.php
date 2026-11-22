<?php

declare(strict_types=1);

namespace App\Libraries;

use Lokandita\Auth\Log;

/**
 * Pemberitahuan di dalam aplikasi untuk mahasiswa (SPEC.md §14, KONTRAK.md §17). Tiga jenis saja:
 * pertemuan dibuka, 24 jam sebelum ditutup, kata sandi direset.
 *
 * Tidak ada proses latar di sisi web. Dua jenis yang bergantung waktu DIBUAT SAAT MAHASISWA
 * MEMBUKA HALAMAN (segarkan), dengan INSERT IGNORE pada kunci unik (user_id, kind, ref):
 * aman diulang, tidak pernah dobel. Jadi pemberitahuan muncul pada pemuatan halaman pertama
 * sesudah saatnya tiba — tidak lebih cepat dari itu.
 *
 * Setiap kueri di sini terikat pada satu user id; tidak ada fungsi yang menerima id baris dari luar.
 */
final class Notifikasi
{
    private const JEDA_MS = 60000;      // segarkan paling sering sekali per menit per pengguna

    private const SEHARI = 86400;

    public const BATAS_DAFTAR = 100;

    /**
     * Membuat pemberitahuan "pertemuan dibuka" dan "24 jam sebelum ditutup" yang belum ada
     * untuk mahasiswa itu. $paksa melewati pembatas satu menit (halaman pemberitahuan sendiri).
     */
    public static function segarkan(string $userId, bool $paksa = false): void
    {
        if (! $paksa && ! self::boleh($userId)) {
            return;
        }
        $kini = Waktu::kini();
        $db   = db_connect();
        // Jendela yang SEDANG terbuka (KONTRAK.md §6) pada pertemuan latihan yang terbit, untuk kelas
        // tempat mahasiswa ini AKTIF di tahun ajaran aktif.
        $baris = $db->query("
            SELECT w.id, w.open_at, w.close_at, w.override, m.id AS pertemuan, m.number, m.title, c.code
            FROM enrollments e
            JOIN classes k ON k.id = e.class_id
            JOIN academic_years t ON t.id = k.academic_year_id AND t.status = 'active'
            JOIN courses c ON c.id = k.course_id
            JOIN meeting_class_windows w ON w.class_id = k.id
            JOIN meetings m ON m.id = w.meeting_id AND m.enabled = 1 AND m.published_at IS NOT NULL
            WHERE e.user_id = ? AND e.status = 'AKTIF'
              AND (w.override = 'open' OR (w.override = 'none' AND w.open_at <= ? AND w.close_at > ?))", [$userId, $kini, $kini])->getResultArray();

        foreach ($baris as $b) {
            $terjadwal = $b['override'] === 'none';
            $data      = json_encode([
                'pertemuan' => (int) $b['pertemuan'], 'nomor' => (int) $b['number'], 'judul' => (string) $b['title'],
                'matkul' => (string) $b['code'], 'tutup' => $terjadwal ? $b['close_at'] : null,
            ], JSON_UNESCAPED_UNICODE);
            self::sisip($userId, 'pertemuan_dibuka', $b['id'] . ':' . ($terjadwal ? $b['open_at'] : 'manual'), $data, $kini);
            // Jendela yang seluruhnya tidak lebih dari 24 jam tidak diberi pengingat kedua:
            // pemberitahuan "dibuka" sudah menyebut waktu tutupnya.
            if ($terjadwal
                && strtotime($b['close_at'] . ' UTC') - strtotime($kini . ' UTC') <= self::SEHARI
                && strtotime($b['close_at'] . ' UTC') - strtotime($b['open_at'] . ' UTC') > self::SEHARI) {
                self::sisip($userId, 'tutup_24jam', $b['id'] . ':' . $b['close_at'], $data, $kini);
            }
        }
    }

    /**
     * Kata sandi mahasiswa itu baru saja direset lewat LokaKode oleh $oleh. $ref = id baris audit reset itu.
     */
    public static function sandiDireset(string $userId, string $oleh, string $kelas, int $ref): void
    {
        self::sisip($userId, 'sandi_direset', (string) $ref, json_encode(['oleh' => $oleh, 'kelas' => $kelas], JSON_UNESCAPED_UNICODE), Waktu::kini());
    }

    private static function sisip(string $userId, string $jenis, string $ref, string $data, string $kini): void
    {
        db_connect()->query('INSERT IGNORE INTO notifications (user_id, kind, ref, data, created_at) VALUES (?, ?, ?, ?, ?)', [$userId, $jenis, $ref, $data, $kini]);
    }

    private static function boleh(string $userId): bool
    {
        try {
            return Antrean::redis()->set('lokakode:laju:notif:' . $userId, '1', ['nx', 'px' => self::JEDA_MS]) !== false;
        } catch (\RedisException $e) {
            // Tanpa Redis pemberitahuan tetap dibuat, pada setiap pemuatan halaman.
            Log::event('ratelimit.unavailable', ['reason' => $e->getMessage()]);

            return true;
        }
    }

    public static function belumDibaca(string $userId): int
    {
        return (int) db_connect()->query('SELECT COUNT(*) AS n FROM notifications WHERE user_id = ? AND read_at IS NULL', [$userId])->getRow()->n;
    }

    /**
     * @return list<array<string, mixed>> terbaru lebih dulu; `data` sudah diurai
     */
    public static function daftar(string $userId): array
    {
        $baris = db_connect()->query('SELECT id, kind, data, created_at, read_at FROM notifications WHERE user_id = ? ORDER BY id DESC LIMIT ?', [$userId, self::BATAS_DAFTAR])->getResultArray();

        return array_map(static function (array $b): array {
            $d         = json_decode((string) $b['data'], true);
            $b['data'] = is_array($d) ? $d : [];

            return $b;
        }, $baris);
    }

    public static function tandaiDibaca(string $userId): void
    {
        db_connect()->query('UPDATE notifications SET read_at = ? WHERE user_id = ? AND read_at IS NULL', [Waktu::kini(), $userId]);
    }
}
