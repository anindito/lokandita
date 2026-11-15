<?php

declare(strict_types=1);

namespace App\Libraries;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Langkah terakhir login: semua pemeriksaan sudah lolos, sesi dibuat.
 */
final class SignIn
{
    /**
     * Membuat sesi dan memasang cookie pada $response.
     *
     * @param array<string, mixed> $user
     */
    public static function complete(array $user, RequestInterface $request, ResponseInterface $response, string $cara): void
    {
        LoginGuard::recordSuccess($user);
        $token = Sessions::create((string) $user['id'], $request->getIPAddress(), (string) $request->getUserAgent());
        Sessions::setCookie($response, $token);
        Audit::log('login.success', $user['id'], $user['id'], ['method' => $cara]);
    }

    /**
     * Ke mana pengguna dibawa setelah masuk.
     *
     * @param array<string, mixed> $user
     */
    public static function target(array $user, ?string $next): string
    {
        if ((int) $user['must_change_password'] === 1) {
            return '/ganti-password' . ($next !== null ? '?next=' . rawurlencode($next) : '');
        }

        return NextUrl::orPortal($next);
    }
}
