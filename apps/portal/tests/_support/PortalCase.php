<?php

declare(strict_types=1);

namespace Tests\Support;

use Lokandita\Auth\Testing\Fixtures;
use Lokandita\Auth\Testing\Http;
use Lokandita\Auth\Testing\TestSchema;
use PHPUnit\Framework\TestCase;

/**
 * Dasar tes Portal: HTTP sungguhan ke portal-tes (dan auth-tes, lokakode-tes),
 * SQL langsung ke identity_test untuk menyiapkan pengguna. Tidak ada mock.
 */
abstract class PortalCase extends TestCase
{
    protected \mysqli $idn;

    protected function setUp(): void
    {
        $this->idn = TestSchema::connect((string) getenv('TES_IDENTITY_DSN'));
    }

    protected function tamu(): Http
    {
        return new Http((string) getenv('APP_TES_URL'));
    }

    /**
     * @param array<string, mixed> $over
     *
     * @return array<string, mixed>
     */
    protected function user(array $over = []): array
    {
        return Fixtures::user($this->idn, $over);
    }

    /**
     * Klien Portal yang membawa sesi $user (sesi dibuat langsung di identity_test).
     *
     * @param array<string, mixed> $user
     */
    protected function sebagai(array $user): Http
    {
        $http = $this->tamu();
        $http->cookies['lokandita_sid'] = Fixtures::session($this->idn, (string) $user['id']);

        return $http;
    }
}
