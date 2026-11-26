<?php

declare(strict_types=1);

namespace App\Libraries;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\RequestInterface;
use Lokandita\Auth\Aplikasi;
use Lokandita\Auth\Current;
use Lokandita\Auth\Seb\Brankas;
use Lokandita\Auth\Seb\Plist;
use Lokandita\Auth\Seb\Seb;
use Lokandita\Auth\Seb\Setelan;
use Lokandita\Auth\Waktu;

/**
 * Konfigurasi Safe Exam Browser PER UJIAN (SPEC-LOKAUJIAN.md §9.1; KONTRAK.md §23).
 *
 * Setiap ujian atau kuis punya konfigurasinya sendiri: berkas .seb sendiri, Config Key sendiri,
 * dan Browser Exam Key yang didaftarkan untuk berkas itu. Membuka berkasnya memulai SEB langsung
 * di alamat masuk ujian itu.
 *
 * Hak akses TIDAK diperiksa di sini: pemanggil sudah mengambil ujiannya lewat App\Libraries\Ujian
 * (yang mengelola mata kuliahnya) atau sesinya lewat App\Libraries\Pengerjaan (mahasiswa).
 * Setelan, berkas, Config Key dan enkripsinya dibuat oleh paket bersama, sama persis dengan LokaKode.
 */
final class SebUjian
{
    public const KUNCI_MAKS = 20;

    private static function db(): BaseConnection
    {
        return db_connect();
    }

    /** Alamat yang dibuka SEB ketika berkas ujian itu dijalankan. */
    public static function urlMulai(int $ujianId): string
    {
        return rtrim((string) getenv('APP_URL'), '/') . '/ujian/' . $ujianId . '/masuk';
    }

    /**
     * Konfigurasi yang berlaku untuk satu ujian, tanpa isi terenkripsinya, dengan jumlah kuncinya.
     *
     * @return array<string, mixed>|null
     */
    public static function aktif(int $ujianId): ?array
    {
        return self::db()->query(
            'SELECT c.id, c.exam_id, c.version, c.config_key, c.created_by, c.created_at,
                    (SELECT COUNT(*) FROM seb_exam_keys k WHERE k.seb_config_id = c.id) AS jumlah_kunci
             FROM seb_configs c WHERE c.exam_id = ? AND c.retired_at IS NULL',
            [$ujianId],
        )->getRowArray();
    }

    /**
     * Ujian itu sedang dijalankan: ada sesi yang menerima "Mulai", atau pengerjaan yang berjalan.
     * Selama itu konfigurasinya tidak boleh dirotasi — rotasi mematikan berkas dan kunci lama
     * seketika, dan setiap mahasiswa yang sedang mengerjakan akan terlempar.
     */
    public static function sedangDijalankan(int $ujianId): bool
    {
        $db = self::db();
        $sesi = $db->query('SELECT 1 FROM sessions s JOIN exams x ON x.id = s.exam_id WHERE s.exam_id = ? AND ' . Tenggat::menerimaMulai() . ' LIMIT 1', [$ujianId])->getRowArray();
        if ($sesi !== null) {
            return true;
        }

        return $db->query(
            'SELECT 1 FROM attempts a JOIN sessions s ON s.id = a.session_id JOIN exams x ON x.id = a.exam_id WHERE a.exam_id = ? AND ' . Tenggat::berjalan() . ' LIMIT 1',
            [$ujianId],
        )->getRowArray() !== null;
    }

    /**
     * Membuat konfigurasi pertama, atau merotasinya. Kata sandi keluar tidak disimpan: hanya
     * hash-nya, di dalam konfigurasi yang terenkripsi.
     *
     * @return int|'sedang_dijalankan' versi konfigurasi baru
     */
    public static function buat(int $matkulId, int $ujianId, string $sandiKeluar): int|string
    {
        $db = self::db();
        $db->transBegin();
        try {
            // Baris ujian dikunci: rotasi dan "Mulai" sesi tidak saling mendahului tanpa terlihat.
            $db->query('SELECT id FROM exams WHERE id = ? FOR UPDATE', [$ujianId]);
            if (self::sedangDijalankan($ujianId)) {
                $db->transRollback();

                return 'sedang_dijalankan';
            }
            $setelan = Setelan::bangun(
                self::urlMulai($ujianId),
                rtrim((string) getenv('APP_URL'), '/') . '/seb/keluar',
                Seb::asal(),
                hash('sha256', $sandiKeluar),
                random_bytes(32),
            );
            $lama  = $db->query('SELECT version FROM seb_configs WHERE exam_id = ? ORDER BY version DESC LIMIT 1', [$ujianId])->getRowArray();
            $versi = $lama === null ? 1 : (int) $lama['version'] + 1;
            $db->query('UPDATE seb_configs SET retired_at = ? WHERE exam_id = ? AND retired_at IS NULL', [Waktu::kini(), $ujianId]);
            $db->query(
                'INSERT INTO seb_configs (exam_id, version, config_enc, config_key, created_by, created_at) VALUES (?, ?, ?, ?, ?, ?)',
                [$ujianId, $versi, Brankas::kunci(Plist::xml($setelan)), Plist::kunciKonfig($setelan), (string) Current::identity()?->userId, Waktu::kini()],
            );
            Audit::catat($versi === 1 ? 'seb.dibuat' : 'seb.dirotasi', 'ujian', $ujianId, ['versi' => $versi], $matkulId);
            $db->transCommit();

            return $versi;
        } catch (\Throwable $e) {
            $db->transRollback();

            throw $e;
        }
    }

    /**
     * Mengganti daftar Browser Exam Key konfigurasi yang berlaku. Kuncinya sendiri tidak pernah
     * ditulis ke audit; jumlahnya cukup.
     *
     * @param list<string> $kunci hex 64 karakter, huruf kecil
     *
     * @return bool false bila ujian itu belum punya konfigurasi
     */
    public static function daftarkanKunci(int $matkulId, int $ujianId, array $kunci): bool
    {
        $db = self::db();
        $db->transBegin();
        try {
            $aktif = $db->query('SELECT id, version FROM seb_configs WHERE exam_id = ? AND retired_at IS NULL FOR UPDATE', [$ujianId])->getRowArray();
            if ($aktif === null) {
                $db->transRollback();

                return false;
            }
            $kunci = array_values(array_unique($kunci));
            $db->query('DELETE FROM seb_exam_keys WHERE seb_config_id = ?', [$aktif['id']]);
            foreach ($kunci as $k) {
                $db->query('INSERT INTO seb_exam_keys (seb_config_id, exam_key, created_by, created_at) VALUES (?, ?, ?, ?)', [$aktif['id'], $k, (string) Current::identity()?->userId, Waktu::kini()]);
            }
            Audit::catat('seb.kunci', 'ujian', $ujianId, ['versi' => (int) $aktif['version'], 'jumlah' => count($kunci)], $matkulId);
            $db->transCommit();

            return true;
        } catch (\Throwable $e) {
            $db->transRollback();

            throw $e;
        }
    }

    /**
     * @return list<string>
     */
    public static function kunciTerdaftar(int $konfigId): array
    {
        return Seb::kunciTerdaftar($konfigId);
    }

    /**
     * Berkas .seb konfigurasi yang berlaku: gzip( "plnd" + gzip(plist XML) ), tanpa kata sandi
     * setelan, sehingga klik ganda membukanya. Null bila ujian itu belum punya konfigurasi.
     *
     * @return array{nama: string, isi: string}|null
     */
    public static function berkas(int $ujianId, string $kodeMatkul): ?array
    {
        $baris = self::db()->query('SELECT config_enc FROM seb_configs WHERE exam_id = ? AND retired_at IS NULL', [$ujianId])->getRowArray();
        if ($baris === null) {
            return null;
        }

        return [
            'nama' => Aplikasi::nama() . '-' . preg_replace('/[^A-Za-z0-9-]/', '', $kodeMatkul) . '-' . $ujianId . '.seb',
            'isi'  => (string) gzencode('plnd' . gzencode(Brankas::buka((string) $baris['config_enc']), 9), 9),
        ];
    }

    /**
     * Kandidat untuk Seb::periksa: konfigurasi yang berlaku dari ujian-ujian itu.
     *
     * @param list<int> $ujianIds
     *
     * @return list<array{id: int, code: string, config_key: string}>
     */
    public static function kandidat(array $ujianIds): array
    {
        $ujianIds = array_values(array_unique(array_map('intval', $ujianIds)));
        if ($ujianIds === []) {
            return [];
        }
        $baris = self::db()->query('SELECT id, exam_id, config_key FROM seb_configs WHERE retired_at IS NULL AND exam_id IN (' . implode(',', $ujianIds) . ') ORDER BY id')->getResultArray();

        return array_map(static fn (array $b): array => ['id' => (int) $b['id'], 'code' => 'ujian-' . (int) $b['exam_id'], 'config_key' => (string) $b['config_key']], $baris);
    }

    /**
     * Keadaan konfigurasi tiap ujian, untuk daftar: id ujian => ['versi', 'kunci'].
     *
     * @param list<int> $ujianIds
     *
     * @return array<int, array{versi: int, kunci: int}>
     */
    public static function ringkas(array $ujianIds): array
    {
        $ujianIds = array_values(array_unique(array_map('intval', $ujianIds)));
        if ($ujianIds === []) {
            return [];
        }
        $hasil = [];
        $baris = self::db()->query(
            'SELECT c.exam_id, c.version, (SELECT COUNT(*) FROM seb_exam_keys k WHERE k.seb_config_id = c.id) AS n
             FROM seb_configs c WHERE c.retired_at IS NULL AND c.exam_id IN (' . implode(',', $ujianIds) . ')',
        )->getResultArray();
        foreach ($baris as $b) {
            $hasil[(int) $b['exam_id']] = ['versi' => (int) $b['version'], 'kunci' => (int) $b['n']];
        }

        return $hasil;
    }

    /**
     * Menulis satu peristiwa integritas (seb_rejected, seb_terminated) dengan IP dan agen pengguna.
     *
     * @param array<string, mixed> $detail
     */
    public static function catatPeristiwa(string $userId, string $jenis, array $detail, ?int $sesiId, ?int $attemptId, RequestInterface $request): void
    {
        self::db()->query(
            'INSERT INTO integrity_events (user_id, attempt_id, session_id, kind, detail, ip, user_agent, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, UTC_TIMESTAMP())',
            [
                $userId, $attemptId, $sesiId, $jenis, json_encode($detail, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                method_exists($request, 'getIPAddress') ? $request->getIPAddress() : null,
                method_exists($request, 'getUserAgent') ? substr((string) $request->getUserAgent(), 0, 255) : null,
            ],
        );
    }
}
