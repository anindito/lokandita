<?php

declare(strict_types=1);

namespace Lokandita\Auth\Testing;

/**
 * Data uji, ditulis langsung ke skema identity_test.
 */
final class Fixtures
{
    /**
     * Membuat pengguna. Mengembalikan barisnya ditambah 'password' (teks asli).
     *
     * @param array<string, mixed> $over  kolom users yang ingin diganti; 'password' dan 'apps' juga diterima
     *
     * @return array<string, mixed>
     */
    public static function user(\mysqli $db, array $over = []): array
    {
        $password = (string) ($over['password'] ?? 'Sandi-Uji-' . bin2hex(random_bytes(4)));
        $apps     = $over['apps'] ?? ['portal', 'lokakode'];
        unset($over['password'], $over['apps']);

        $role = $over['global_role'] ?? 'mahasiswa';
        $row  = $over + [
            'id'                   => self::uuid(),
            'nomor_induk'          => 'tes' . bin2hex(random_bytes(5)),
            'nama'                 => 'Pengguna Uji',
            'password_hash'        => password_hash($password, PASSWORD_ARGON2ID),
            'must_change_password' => 0,
            'global_role'          => $role,
            'status'               => 'active',
            'idle_logout'          => $role === 'mahasiswa' ? 1 : 0,
            'created_at'           => gmdate('Y-m-d H:i:s'),
            'updated_at'           => gmdate('Y-m-d H:i:s'),
        ];

        self::insert($db, 'users', $row);
        self::insert($db, 'password_history', ['user_id' => $row['id'], 'password_hash' => $row['password_hash'], 'created_at' => $row['created_at']]);
        foreach ($apps as $app) {
            self::insert($db, 'app_access', ['user_id' => $row['id'], 'app' => $app, 'enabled' => 1, 'granted_at' => $row['created_at']]);
        }

        return $row + ['password' => $password];
    }

    /**
     * Login lewat form sungguhan. Mengembalikan klien yang sama (cookie tersimpan di dalamnya).
     *
     * @param array<string, mixed> $user hasil self::user()
     */
    public static function login(Http $http, array $user, ?string $next = null, ?string $password = null): Http
    {
        $http->get('/login');
        $form = ['csrf' => (string) $http->input('csrf'), 'nomor_induk' => $user['nomor_induk'], 'password' => $password ?? $user['password']];
        if ($next !== null) {
            $form['next'] = $next;
        }

        return $http->post('/login', $form);
    }

    /**
     * Membuat sesi langsung di basis data (tanpa form login) dan mengembalikan nilai cookie-nya.
     * Untuk tes yang bukan tentang login itu sendiri.
     */
    public static function session(\mysqli $db, string $userId): string
    {
        $token = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
        self::insert($db, 'sessions', [
            'id'           => self::uuid(),
            'token_hash'   => hash('sha256', $token),
            'user_id'      => $userId,
            'created_at'   => gmdate('Y-m-d H:i:s'),
            'expires_at'   => gmdate('Y-m-d H:i:s', time() + 43200),
            'last_seen_at' => gmdate('Y-m-d H:i:s'),
            'ip'           => '203.0.113.9',
            'user_agent'   => 'tes',
        ]);

        return $token;
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function insert(\mysqli $db, string $table, array $row): void
    {
        $kolom = implode(', ', array_map(static fn ($k) => "`{$k}`", array_keys($row)));
        $tanda = implode(', ', array_fill(0, count($row), '?'));
        $stmt  = $db->prepare("INSERT INTO `{$table}` ({$kolom}) VALUES ({$tanda})");
        $stmt->execute(array_values($row));
    }

    public static function uuid(): string
    {
        $h = bin2hex(random_bytes(16));

        return sprintf('%s-%s-4%s-a%s-%s', substr($h, 0, 8), substr($h, 8, 4), substr($h, 13, 3), substr($h, 17, 3), substr($h, 20, 12));
    }
}
