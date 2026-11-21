<?php

declare(strict_types=1);

namespace App\Libraries\Seb;

use App\Libraries\Antrean;
use App\Libraries\Audit;
use App\Libraries\Waktu;
use CodeIgniter\HTTP\RequestInterface;
use Lokandita\Auth\Log;

/**
 * Safe Exam Browser di LokaKode (SPEC.md §13.1, KONTRAK.md §8): konfigurasi per mata
 * kuliah, berkas .seb, dan pemeriksaan kedua header pada permintaan mahasiswa.
 */
final class Seb
{
    public const HEADER_KONFIG  = 'X-SafeExamBrowser-ConfigKeyHash';
    public const HEADER_PERAMBAN = 'X-SafeExamBrowser-RequestHash';

    /** Sewa sesi SEB di Redis: diperbarui setiap permintaan yang lolos; denyut tiap 25 detik. */
    public const SEWA_DETIK   = 30;
    public const DENYUT_DETIK = 25;

    public const SANDI_KELUAR_MIN = 12;

    public static function diberlakukan(): bool
    {
        return getenv('SEB_ENFORCEMENT') !== 'disabled';
    }

    /**
     * Asal yang selalu ada di daftar URL yang diizinkan: aplikasi ini, LokaAuth (tanpa itu
     * mahasiswa tidak bisa masuk dari dalam SEB) dan Portal (tempat Keluar berakhir).
     *
     * @return list<string>
     */
    public static function asal(): array
    {
        $asal = [];
        foreach (['APP_URL', 'AUTH_PUBLIC_URL', 'PORTAL_URL'] as $var) {
            $u      = (string) getenv($var);
            $asal[] = parse_url($u, PHP_URL_SCHEME) . '://' . parse_url($u, PHP_URL_HOST);
        }

        return array_values(array_unique($asal));
    }

    /**
     * URL permintaan ini seperti yang di-hash SEB: asal dari APP_URL (header Host klien tidak
     * dipercaya) + jalur dan kueri MENTAH dari baris permintaan, tanpa fragmen.
     */
    public static function url(): string
    {
        $mentah = (string) ($_SERVER['REQUEST_URI'] ?? '/');

        return rtrim((string) getenv('APP_URL'), '/') . explode('#', $mentah, 2)[0];
    }

    // ---- konfigurasi ----------------------------------------------------------------

    /**
     * Konfigurasi yang berlaku untuk satu mata kuliah, tanpa isi terenkripsinya.
     *
     * @return array<string, mixed>|null
     */
    public static function aktif(int $matkulId): ?array
    {
        return db_connect()->query(
            'SELECT id, course_id, version, config_key, created_by, created_at FROM seb_configs WHERE course_id = ? AND retired_at IS NULL',
            [$matkulId],
        )->getRowArray();
    }

    /**
     * Membuat konfigurasi pertama atau merotasinya. Rotasi memensiunkan konfigurasi lama;
     * Browser Exam Key yang terdaftar menempel padanya dan ikut tidak berlaku.
     * Kata sandi keluar tidak disimpan: hanya hash-nya, di dalam konfigurasi.
     *
     * @return int versi konfigurasi baru
     */
    public static function buat(int $matkulId, string $sandiKeluar): int
    {
        $setelan = Setelan::bangun(
            rtrim((string) getenv('APP_URL'), '/') . '/beranda',
            rtrim((string) getenv('APP_URL'), '/') . '/seb/keluar',
            self::asal(),
            hash('sha256', $sandiKeluar),
            random_bytes(32),
        );
        $db = db_connect();
        $db->transStart();
        $lama  = $db->query('SELECT id, version FROM seb_configs WHERE course_id = ? ORDER BY version DESC LIMIT 1 FOR UPDATE', [$matkulId])->getRowArray();
        $versi = $lama === null ? 1 : (int) $lama['version'] + 1;
        $db->query('UPDATE seb_configs SET retired_at = ? WHERE course_id = ? AND retired_at IS NULL', [Waktu::kini(), $matkulId]);
        $db->query(
            'INSERT INTO seb_configs (course_id, version, config_enc, config_key, created_by, created_at) VALUES (?, ?, ?, ?, ?, ?)',
            [$matkulId, $versi, Brankas::kunci(Plist::xml($setelan)), Plist::kunciKonfig($setelan), \Lokandita\Auth\Current::identity()?->userId, Waktu::kini()],
        );
        Audit::catat($versi === 1 ? 'seb.dibuat' : 'seb.dirotasi', 'course', $matkulId, ['versi' => $versi], $matkulId);
        $db->transComplete();

        return $versi;
    }

    /**
     * Mengganti daftar Browser Exam Key konfigurasi yang berlaku dengan $kunci.
     *
     * @param list<string> $kunci hex 64 karakter, huruf kecil
     */
    public static function daftarkanKunci(int $matkulId, array $kunci): void
    {
        $db = db_connect();
        $db->transStart();
        $aktif = $db->query('SELECT id, version FROM seb_configs WHERE course_id = ? AND retired_at IS NULL FOR UPDATE', [$matkulId])->getRowArray();
        if ($aktif === null) {
            $db->transComplete();
            throw new \LogicException('mata kuliah ini belum punya konfigurasi SEB');
        }
        $db->query('DELETE FROM seb_exam_keys WHERE seb_config_id = ?', [$aktif['id']]);
        foreach (array_values(array_unique($kunci)) as $k) {
            $db->query(
                'INSERT INTO seb_exam_keys (seb_config_id, exam_key, created_by, created_at) VALUES (?, ?, ?, ?)',
                [$aktif['id'], $k, \Lokandita\Auth\Current::identity()?->userId, Waktu::kini()],
            );
        }
        // Kunci itu sendiri tidak ditulis ke audit; jumlahnya cukup.
        Audit::catat('seb.kunci', 'course', $matkulId, ['versi' => (int) $aktif['version'], 'jumlah' => count(array_unique($kunci))], $matkulId);
        $db->transComplete();
    }

    /**
     * @return list<string>
     */
    public static function kunciTerdaftar(int $konfigId): array
    {
        return array_column(db_connect()->query('SELECT exam_key FROM seb_exam_keys WHERE seb_config_id = ? ORDER BY id', [$konfigId])->getResultArray(), 'exam_key');
    }

    /**
     * Isi berkas .seb konfigurasi yang berlaku: gzip( "plnd" + gzip(plist XML) ), tanpa kata
     * sandi setelan. Null bila mata kuliah itu tidak ada atau belum punya konfigurasi.
     *
     * @return array{nama: string, isi: string}|null
     */
    public static function berkas(string $kodeMatkul): ?array
    {
        $baris = db_connect()->query(
            'SELECT c.code, s.config_enc FROM seb_configs s JOIN courses c ON c.id = s.course_id WHERE c.code = ? AND s.retired_at IS NULL',
            [$kodeMatkul],
        )->getRowArray();
        if ($baris === null) {
            return null;
        }

        return [
            'nama' => 'LokaKode-' . preg_replace('/[^A-Za-z0-9-]/', '', (string) $baris['code']) . '.seb',
            'isi'  => (string) gzencode('plnd' . gzencode(Brankas::buka((string) $baris['config_enc']), 9), 9),
        ];
    }

    // ---- pemeriksaan ----------------------------------------------------------------

    /**
     * Konfigurasi yang berlaku bagi seorang mahasiswa: milik mata kuliah tempat ia AKTIF
     * pada tahun ajaran aktif.
     *
     * @return list<array{id: int, code: string, config_key: string}>
     */
    public static function kandidat(string $userId): array
    {
        $baris = db_connect()->query(
            "SELECT s.id, c.code, s.config_key
             FROM enrollments e
             JOIN academic_years ay ON ay.id = e.academic_year_id AND ay.status = 'active'
             JOIN courses c ON c.id = e.course_id
             JOIN seb_configs s ON s.course_id = e.course_id AND s.retired_at IS NULL
             WHERE e.user_id = ? AND e.status = 'AKTIF'
             ORDER BY s.id",
            [$userId],
        )->getResultArray();

        return array_map(static fn ($b) => ['id' => (int) $b['id'], 'code' => (string) $b['code'], 'config_key' => (string) $b['config_key']], $baris);
    }

    /**
     * Semua konfigurasi yang berlaku (halaman periksa publik).
     *
     * @return list<array{id: int, code: string, config_key: string}>
     */
    public static function semuaAktif(): array
    {
        $baris = db_connect()->query('SELECT s.id, c.code, s.config_key FROM seb_configs s JOIN courses c ON c.id = s.course_id WHERE s.retired_at IS NULL ORDER BY c.code')->getResultArray();

        return array_map(static fn ($b) => ['id' => (int) $b['id'], 'code' => (string) $b['code'], 'config_key' => (string) $b['config_key']], $baris);
    }

    /**
     * Memeriksa kedua header permintaan ini terhadap $kandidat (KONTRAK.md §8). Lolos hanya
     * bila KEDUANYA cocok untuk konfigurasi yang sama. User-Agent tidak pernah dilihat.
     *
     * @param list<array{id: int, code: string, config_key: string}> $kandidat
     *
     * @return array{lolos: bool, alasan: string, rincian: list<array{code: string, konfig: bool, peramban: bool, kunci: int}>}
     *         alasan: ok | tanpa_konfigurasi | tanpa_header | kunci_konfigurasi | kunci_peramban
     */
    public static function periksa(RequestInterface $request, array $kandidat): array
    {
        $url      = self::url();
        $hKonfig  = strtolower(trim($request->getHeaderLine(self::HEADER_KONFIG)));
        $hPeramban = strtolower(trim($request->getHeaderLine(self::HEADER_PERAMBAN)));
        $rincian  = [];
        $lolos    = false;
        $adaKonfig = false;
        foreach ($kandidat as $k) {
            $konfig   = hash_equals(hash('sha256', $url . $k['config_key']), $hKonfig);
            $kunci    = self::kunciTerdaftar($k['id']);
            $peramban = false;
            foreach ($kunci as $bek) {
                $peramban = hash_equals(hash('sha256', $url . $bek), $hPeramban) || $peramban;
            }
            $rincian[] = ['code' => $k['code'], 'konfig' => $konfig, 'peramban' => $peramban, 'kunci' => count($kunci)];
            $lolos     = $lolos || ($konfig && $peramban);
            $adaKonfig = $adaKonfig || $konfig;
        }
        $alasan = match (true) {
            $lolos                               => 'ok',
            $kandidat === []                     => 'tanpa_konfigurasi',
            $hKonfig === '' && $hPeramban === '' => 'tanpa_header',
            ! $adaKonfig                         => 'kunci_konfigurasi',
            default                              => 'kunci_peramban',
        };

        return ['lolos' => $lolos, 'alasan' => $alasan, 'rincian' => $rincian];
    }

    // ---- sewa -----------------------------------------------------------------------

    private static function kunciSewa(string $userId): string
    {
        return 'lokakode:seb:sewa:' . $userId . ':' . hash('sha256', (string) ($_COOKIE['lokandita_sid'] ?? ''));
    }

    /**
     * Permintaan lolos: sewa sesi ini diperpanjang 30 detik.
     */
    public static function perpanjangSewa(string $userId): void
    {
        try {
            Antrean::redis()->setex(self::kunciSewa($userId), self::SEWA_DETIK, '1');
        } catch (\RedisException $e) {
            Log::event('seb.sewa_gagal', ['reason' => $e->getMessage()]);
        }
    }

    /**
     * Permintaan ditolak: benar bila sesi ini masih memegang sewa (tadinya di dalam SEB yang
     * sah — ini "sesi diakhiri"); sewanya dihapus. Salah bila memang belum pernah lolos.
     */
    public static function akhiriSewa(string $userId): bool
    {
        try {
            return (int) Antrean::redis()->del(self::kunciSewa($userId)) > 0;
        } catch (\RedisException $e) {
            Log::event('seb.sewa_gagal', ['reason' => $e->getMessage()]);

            return false;
        }
    }
}
