<?php

declare(strict_types=1);

namespace App\Libraries;

use Lokandita\Auth\Log;

/**
 * Log audit: hanya menambah baris (SPEC.md §4.7). Kelas ini sengaja tidak
 * punya fungsi ubah atau hapus.
 *
 * Jangan pernah memasukkan kata sandi, token, atau kode ke $detail.
 */
final class Audit
{
    /**
     * @param array<string, mixed> $detail
     */
    public static function log(string $action, ?string $actorId, ?string $targetId = null, array $detail = [], string $app = 'auth'): void
    {
        $request = service('request');
        $ip      = method_exists($request, 'getIPAddress') ? $request->getIPAddress() : null;
        $agent   = method_exists($request, 'getUserAgent') ? substr((string) $request->getUserAgent(), 0, 255) : null;

        db_connect()->table('audit_log')->insert([
            'actor_id'   => $actorId,
            'action'     => $action,
            'target_id'  => $targetId,
            'app'        => $app,
            'detail'     => json_encode($detail, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'ip'         => $ip,
            'user_agent' => $agent,
            'created_at' => Clock::now(),
        ]);

        $konteks = ['actor' => $actorId, 'target' => $targetId, 'via' => $app, 'ip' => $ip];
        foreach ($detail as $k => $v) {
            if (is_scalar($v) || $v === null) {
                $konteks['d_' . $k] = $v;
            }
        }
        Log::event($action, $konteks);
    }
}
