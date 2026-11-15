<?php

declare(strict_types=1);

namespace App\Libraries;

use Lokandita\Auth\Current;
use Lokandita\Auth\Log;

/**
 * Log audit LokaKode: hanya menambah baris. Kelas ini sengaja tidak punya
 * fungsi ubah atau hapus, dan tidak ada rute yang mengubah tabelnya.
 *
 * Dipanggil DI DALAM transaksi yang sama dengan perubahannya (koneksi yang
 * sama), sehingga perubahan tanpa baris audit tidak bisa terjadi.
 *
 * Jangan pernah memasukkan kata sandi atau token ke $detail.
 */
final class Audit
{
    /**
     * @param array<string, mixed> $detail
     */
    public static function catat(
        string $aksi,
        ?string $targetTipe = null,
        int|string|null $targetId = null,
        array $detail = [],
        ?int $matkulId = null,
        ?int $kelasId = null,
    ): void {
        $pelaku = Current::identity();
        if ($pelaku === null) {
            throw new \LogicException('Audit tanpa pelaku: rute ini tidak dipasangi filter masuk.');
        }
        $request = service('request');
        $ip      = method_exists($request, 'getIPAddress') ? $request->getIPAddress() : null;
        $agent   = method_exists($request, 'getUserAgent') ? substr((string) $request->getUserAgent(), 0, 255) : null;

        db_connect()->table('audit_log')->insert([
            'actor_id'    => $pelaku->userId,
            'action'      => $aksi,
            'target_tipe' => $targetTipe,
            'target_id'   => $targetId === null ? null : (string) $targetId,
            'course_id'   => $matkulId,
            'class_id'    => $kelasId,
            'detail'      => json_encode($detail, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'ip'          => $ip,
            'user_agent'  => $agent,
            'created_at'  => Waktu::kini(),
        ]);

        Log::event($aksi, ['actor' => $pelaku->userId, 'target' => $targetTipe . ':' . $targetId, 'ip' => $ip]);
    }
}
