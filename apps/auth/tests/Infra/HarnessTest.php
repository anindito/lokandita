<?php

declare(strict_types=1);

namespace Tests\Infra;

use Lokandita\Auth\Testing\Http;
use Lokandita\Auth\Testing\TestSchema;
use PHPUnit\Framework\TestCase;

/**
 * Membuktikan alat tesnya sendiri: skema *_test wajib, dan kontainer auth-tes
 * dijangkau lewat HTTP sungguhan.
 */
final class HarnessTest extends TestCase
{
    public function testMenolakSkemaYangBukanTest(): void
    {
        $this->expectException(\RuntimeException::class);
        TestSchema::dsn('MySQLi://auth_app:x@mysql:3306/identity');
    }

    public function testMenerimaSkemaTest(): void
    {
        $this->assertSame('identity_test', TestSchema::schemaOf(TestSchema::dsn('MySQLi://auth_app:x@mysql:3306/identity_test')));
    }

    public function testProsesTesIniMemakaiSkemaTest(): void
    {
        $db = TestSchema::connect((string) getenv('DB_DSN'));
        $this->assertSame('identity_test', $db->query('SELECT DATABASE()')->fetch_row()[0]);
        $this->assertSame('identity_test', config('Database')->tests['database']);
        $this->assertSame('identity_test', db_connect()->query('SELECT DATABASE() AS d')->getRow()->d);
    }

    public function testAuthTesMenjawabHealthzLewatHttp(): void
    {
        $http = (new Http((string) getenv('AUTH_TES_URL')))->get('/healthz');
        $this->assertSame(200, $http->status);
        $this->assertSame('auth', $http->json()['app']);
    }

    public function testAuthTesMemakaiSkemaTest(): void
    {
        // Kontainer auth-tes melaporkan skema yang dipakainya; tes tidak boleh
        // berbicara dengan LokaAuth yang memakai data sungguhan.
        $this->assertSame('identity_test', getenv('AUTH_TES_SKEMA'));
    }
}
