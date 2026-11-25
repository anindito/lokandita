<?php

declare(strict_types=1);

namespace App\Libraries;

use CodeIgniter\Database\BaseConnection;

/**
 * Pekerjaan penjadwal LokaUjian (SPEC-LOKAUJIAN.md §7.5; KONTRAK.md §21).
 *
 * Penjadwal TIDAK memutuskan apa pun: boleh-tidaknya menjawab selalu dihitung dari waktu
 * (App\Libraries\Tenggat) pada setiap baca dan setiap tulis. Yang dikerjakan di sini hanyalah
 * membawa keadaan TERSIMPAN menyusul: pengerjaan yang tenggatnya sudah lewat ditandai
 * dikumpulkan. Keadaan sesi (terbuka, tertutup, berlangsung, selesai) tidak disimpan sama
 * sekali, jadi tidak ada yang perlu dimutakhirkan untuknya. Penilaian menyusul di tahap U5.
 *
 * Setiap lintasan idempoten; menjalankannya dua kali, atau dari dua tempat sekaligus, tidak
 * mengubah hasilnya. Halaman sesi dosen memanggilnya juga, supaya tetap benar ketika kontainer
 * penjadwal mati.
 */
final class Jadwal
{
    public const KUNCI_DENYUT = 'lokaujian:jadwal';
    public const UMUR_DENYUT  = 60;
    public const JEDA_DETIK   = 10;

    /**
     * Satu lintasan. Mengembalikan jumlah pengerjaan yang ditandai.
     *
     * Pengerjaan baru ditandai sesudah tenggang simpan (10 detik) lewat, supaya simpan otomatis
     * terakhir yang masih di jalan tidak ditolak oleh tanda ini. Waktu kumpulnya adalah
     * tenggatnya sendiri, bukan saat penjadwal sempat lewat.
     */
    public static function lintasan(?BaseConnection $db = null): int
    {
        $db ??= db_connect();
        $tenggat = Tenggat::tenggat();
        $db->query(
            "UPDATE attempts a JOIN sessions s ON s.id = a.session_id JOIN exams x ON x.id = a.exam_id
             SET a.submit_how = IF(x.mode = 'dikendalikan_dosen' AND s.ended_by IS NOT NULL AND a.extra_minutes = 0, 'diakhiri_dosen', 'waktu_habis'),
                 a.submitted_at = {$tenggat}
             WHERE a.submitted_at IS NULL AND UTC_TIMESTAMP() >= {$tenggat} + INTERVAL " . Tenggat::TENGGANG_DETIK . ' SECOND',
        );

        return $db->affectedRows();
    }

    public static function redis(): \Redis
    {
        $u = parse_url((string) getenv('REDIS_URL'));
        $r = new \Redis();
        $r->connect((string) $u['host'], (int) ($u['port'] ?? 6379), 2.0);
        if (($u['pass'] ?? '') !== '') {
            $r->auth($u['pass']);
        }
        $r->select((int) ltrim((string) ($u['path'] ?? '/0'), '/'));

        return $r;
    }

    /**
     * Denyut penjadwal yang sedang hidup, atau null. Redis yang tidak terjangkau dibaca sebagai
     * "tidak ada penjadwal": halaman dosen menampilkannya, tidak gagal karenanya.
     *
     * @return array{commit: string, pid: int, mulai: string, waktu: string, lintasan: int}|null
     */
    public static function denyut(): ?array
    {
        try {
            $isi = self::redis()->get(self::KUNCI_DENYUT);
        } catch (\Throwable) {
            return null;
        }
        $d = is_string($isi) ? json_decode($isi, true) : null;

        return is_array($d) && isset($d['commit'], $d['pid'], $d['waktu']) ? $d : null;
    }
}
