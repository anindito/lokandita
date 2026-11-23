<?php

declare(strict_types=1);

namespace App\Libraries;

use Lokandita\Auth\Log;

/**
 * Sisi web dari antrean judge (KONTRAK.md §6).
 *
 * Basis data adalah sumber kebenaran: baris judge_jobs disisipkan dan
 * di-commit lebih dulu, baru id-nya didorong ke Redis. Bila Redis gagal,
 * barisnya tetap QUEUED dan pemindai worker menemukannya dalam beberapa detik;
 * pekerjaan tidak hilang.
 */
final class Antrean
{
    public const JENIS = ['RUN', 'SUBMIT', 'GENERATE', 'VALIDATE', 'ANALYZE'];

    public const KUNCI = ['normal' => 'lokandita:judge:normal', 'low' => 'lokandita:judge:low'];

    public const KUNCI_DENYUT = 'lokandita:judge:worker';

    private static ?\Redis $redis = null;

    /**
     * Membuat pekerjaan dan mengembalikan id-nya.
     *
     * @param array<string, mixed> $payload   bentuknya per jenis: KONTRAK.md §6
     * @param string|null          $pembuat   users.id, atau null untuk pekerjaan sistem
     * @param string|null          $kunciUnik idempotency_key: permintaan yang sama dua kali
     *                                        menghasilkan satu pekerjaan saja
     * @param bool                 $diDepan   masuk di depan antreannya (dikerjakan sebelum yang
     *                                        sudah menunggu di antrean itu); dipakai impor soal di
     *                                        antrean low supaya tidak berdiri di depan mahasiswa
     *                                        dan tidak menunggu di belakang pra-pembuatan instans
     */
    public static function kirim(string $jenis, array $payload, ?string $pembuat, string $prioritas = 'normal', ?string $kunciUnik = null, bool $diDepan = false): string
    {
        if (! in_array($jenis, self::JENIS, true)) {
            throw new \InvalidArgumentException("jenis pekerjaan tidak dikenal: {$jenis}");
        }
        if (! isset(self::KUNCI[$prioritas])) {
            throw new \InvalidArgumentException("prioritas tidak dikenal: {$prioritas}");
        }
        $db = db_connect();
        if ($db->transDepth > 0) {
            // LPUSH sebelum commit akan membuat worker mencari baris yang belum ada.
            throw new \LogicException('Antrean::kirim tidak boleh dipanggil di dalam transaksi.');
        }

        $id = self::uuid();
        try {
            $db->query(
                'INSERT INTO judge_jobs (id, kind, priority, status, payload, idempotency_key, created_by, created_at)
                 VALUES (?, ?, ?, \'QUEUED\', ?, ?, ?, ?)',
                [$id, $jenis, $prioritas, json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), $kunciUnik, $pembuat, Waktu::kini()],
            );
        } catch (\Throwable $e) {
            // Kunci unik sudah dipakai: pekerjaan yang sama sudah ada, dan sudah diantrekan.
            $ada = $kunciUnik === null ? null : $db->query('SELECT id FROM judge_jobs WHERE idempotency_key = ?', [$kunciUnik])->getRowArray();
            if ($ada === null) {
                throw $e;
            }

            return (string) $ada['id'];
        }

        self::dorong($prioritas, $id, $diDepan);

        return $id;
    }

    /**
     * Pekerjaan dengan kunci unik itu, bila ada: [id, status, priority].
     *
     * @return array{id: string, status: string, priority: string}|null
     */
    public static function denganKunci(string $kunciUnik): ?array
    {
        return db_connect()->query('SELECT id, status, priority FROM judge_jobs WHERE idempotency_key = ?', [$kunciUnik])->getRowArray();
    }

    /**
     * Mengantrekan lagi pekerjaan yang FAILED, di barisnya sendiri (kunci uniknya
     * tetap). Syarat status ada di dalam UPDATE: dua permintaan sekaligus hanya
     * mengantrekannya sekali.
     *
     * @param array<string, mixed> $payload
     */
    public static function ulangi(string $id, array $payload, string $prioritas): bool
    {
        $db = db_connect();
        $db->query(
            'UPDATE judge_jobs SET status = \'QUEUED\', priority = ?, payload = ?, attempts = 0, progress = NULL, result = NULL,
                    started_at = NULL, finished_at = NULL, created_at = ?
             WHERE id = ? AND status = \'FAILED\'',
            [$prioritas, json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), Waktu::kini(), $id],
        );
        if ($db->affectedRows() !== 1) {
            return false;
        }
        self::dorong($prioritas, $id);

        return true;
    }

    /**
     * Pekerjaan antrean low yang masih menunggu dipindahkan ke antrean normal:
     * mahasiswanya sedang menunggu di depan layar. Id-nya boleh ada di dua
     * antrean; worker hanya mengklaimnya sekali (KONTRAK.md §6).
     */
    public static function dahulukan(string $id): void
    {
        $db = db_connect();
        $db->query('UPDATE judge_jobs SET priority = \'normal\' WHERE id = ? AND status = \'QUEUED\' AND priority = \'low\'', [$id]);
        if ($db->affectedRows() === 1) {
            self::dorong('normal', $id);
        }
    }

    private static function dorong(string $prioritas, string $id, bool $diDepan = false): void
    {
        try {
            // Worker mengambil dari ujung kanan (BRPOP): LPUSH = belakang antrean, RPUSH = depan.
            if ($diDepan) {
                self::redis()->rPush(self::KUNCI[$prioritas], $id);
            } else {
                self::redis()->lPush(self::KUNCI[$prioritas], $id);
            }
        } catch (\RedisException $e) {
            self::$redis = null;
            Log::event('judge.lpush_failed', ['job' => $id, 'reason' => $e->getMessage()]);
        }
    }

    /**
     * Pekerjaan milik pengguna ini. Syarat pemilik ada di dalam kueri: id milik
     * orang lain dan id yang tidak ada sama-sama menghasilkan null.
     *
     * @return array{id: string, kind: string, status: string, progress: mixed, result: mixed}|null
     */
    public static function milik(string $id, string $userId): ?array
    {
        return self::bentuk(db_connect()->query(
            'SELECT id, kind, status, progress, result FROM judge_jobs WHERE id = ? AND created_by = ?',
            [$id, $userId],
        )->getRowArray());
    }

    /**
     * Pekerjaan sistem (created_by NULL); hanya untuk perintah spark, tidak pernah untuk rute web.
     *
     * @return array{id: string, kind: string, status: string, progress: mixed, result: mixed}|null
     */
    public static function sistem(string $id): ?array
    {
        return self::bentuk(db_connect()->query(
            'SELECT id, kind, status, progress, result FROM judge_jobs WHERE id = ? AND created_by IS NULL',
            [$id],
        )->getRowArray());
    }

    /**
     * Denyut worker, atau null bila worker tidak berjalan (kuncinya hidup 15 detik).
     *
     * @return array<string, mixed>|null
     */
    public static function denyut(): ?array
    {
        $json = self::redis()->get(self::KUNCI_DENYUT);

        return is_string($json) ? json_decode($json, true) : null;
    }

    /**
     * @param array<string, mixed>|null $baris
     *
     * @return array{id: string, kind: string, status: string, progress: mixed, result: mixed}|null
     */
    private static function bentuk(?array $baris): ?array
    {
        if ($baris === null) {
            return null;
        }

        return [
            'id'       => (string) $baris['id'],
            'kind'     => (string) $baris['kind'],
            'status'   => (string) $baris['status'],
            'progress' => $baris['progress'] === null ? null : json_decode((string) $baris['progress'], true),
            'result'   => $baris['result'] === null ? null : json_decode((string) $baris['result'], true),
        ];
    }

    public static function uuidSah(string $id): bool
    {
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $id) === 1;
    }

    public static function uuid(): string
    {
        $b    = random_bytes(16);
        $b[6] = chr((ord($b[6]) & 0x0F) | 0x40);
        $b[8] = chr((ord($b[8]) & 0x3F) | 0x80);
        $h    = bin2hex($b);

        return sprintf('%s-%s-%s-%s-%s', substr($h, 0, 8), substr($h, 8, 4), substr($h, 12, 4), substr($h, 16, 4), substr($h, 20, 12));
    }

    /**
     * Koneksi Redis dari REDIS_URL (KONTRAK.md §10): redis://:<sandi>@redis:6379/<db>.
     */
    public static function redis(): \Redis
    {
        if (self::$redis === null) {
            $u = parse_url((string) getenv('REDIS_URL'));
            $r = new \Redis();
            $r->connect((string) $u['host'], (int) ($u['port'] ?? 6379), 2.0);
            if (($u['pass'] ?? '') !== '') {
                $r->auth($u['pass']);
            }
            $r->select((int) ltrim((string) ($u['path'] ?? '/0'), '/'));
            self::$redis = $r;
        }

        return self::$redis;
    }
}
