<?php

declare(strict_types=1);

namespace Tests\Support;

use Lokandita\Auth\Testing\Fixtures;
use Lokandita\Auth\Testing\Http;
use Lokandita\Auth\Testing\TestSchema;
use PHPUnit\Framework\TestCase;

/**
 * Dasar tes LokaAuth: HTTP sungguhan ke kontainer auth-tes, SQL langsung ke identity_test.
 */
abstract class AuthCase extends TestCase
{
    protected \mysqli $db;

    protected function setUp(): void
    {
        $this->db = TestSchema::connect((string) getenv('DB_DSN'));
    }

    protected function http(?string $ip = null): Http
    {
        return new Http((string) getenv('AUTH_TES_URL'), $ip);
    }

    /**
     * @param array<string, mixed> $over
     *
     * @return array<string, mixed>
     */
    protected function user(array $over = []): array
    {
        return Fixtures::user($this->db, $over);
    }

    /**
     * @param array<string, mixed> $user
     */
    protected function login(array $user, ?string $next = null, ?string $password = null, ?Http $http = null): Http
    {
        return Fixtures::login($http ?? $this->http(), $user, $next, $password);
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function row(string $sql): ?array
    {
        return $this->db->query($sql)->fetch_assoc();
    }

    /**
     * @return array<string, mixed>
     */
    protected function userRow(string $id): array
    {
        return $this->row("SELECT * FROM users WHERE id = '{$id}'");
    }

    /**
     * Baris audit terakhir dengan aksi ini untuk target ini.
     *
     * @return array<string, mixed>|null
     */
    protected function lastAudit(string $action, ?string $targetId = null): ?array
    {
        $where = $targetId === null ? '' : " AND target_id = '{$targetId}'";
        $row   = $this->row("SELECT * FROM audit_log WHERE action = '{$action}'{$where} ORDER BY id DESC LIMIT 1");
        if ($row !== null) {
            $row['detail'] = json_decode((string) $row['detail'], true);
        }

        return $row;
    }

    protected function assertRedirect(Http $http, string $location, string $message = ''): void
    {
        $this->assertContains($http->status, [302, 303, 307], $message . ' — status ' . $http->status . ', isi: ' . substr(strip_tags($http->body), 0, 300));
        // CI4 selalu menulis Location mutlak, dibangun dari APP_URL (bukan dari header Host klien).
        if (str_starts_with($location, '/')) {
            $location = rtrim((string) getenv('APP_URL'), '/') . $location;
        }
        $this->assertSame($location, $http->header('Location'), $message);
    }

    /**
     * Teks toast galat pada halaman terakhir.
     */
    protected function toast(Http $http): string
    {
        return preg_match('/<div class="toast toast-galat"[^>]*>\s*<span>(.*?)<\/span>/s', $http->body, $m) === 1 ? html_entity_decode($m[1], ENT_QUOTES) : '';
    }
}
