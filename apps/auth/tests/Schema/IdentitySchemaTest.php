<?php

declare(strict_types=1);

namespace Tests\Schema;

use Lokandita\Auth\Testing\TestSchema;
use PHPUnit\Framework\TestCase;

/**
 * KONTRAK.md §3 terhadap MySQL sungguhan: view, dan batas hak identity_reader.
 */
final class IdentitySchemaTest extends TestCase
{
    private \mysqli $db;

    private \mysqli $reader;

    protected function setUp(): void
    {
        $this->db     = TestSchema::connect((string) getenv('DB_DSN'));
        $this->reader = TestSchema::connect((string) getenv('TES_READER_DSN'));
        $this->db->query("DELETE FROM sessions WHERE user_id LIKE 'skema-%'");
        $this->db->query("DELETE FROM app_access WHERE user_id LIKE 'skema-%'");
        $this->db->query("DELETE FROM users WHERE id LIKE 'skema-%'");
    }

    private function user(string $id, string $status = 'active', int $idle = 0): void
    {
        $this->db->query("INSERT INTO users (id, nomor_induk, nama, password_hash, global_role, status, idle_logout, created_at, updated_at)
            VALUES ('{$id}', '{$id}', 'Uji Skema', 'x', 'mahasiswa', '{$status}', {$idle}, UTC_TIMESTAMP(), UTC_TIMESTAMP())");
    }

    private function session(string $userId, string $token, string $expires = '+ INTERVAL 1 HOUR', string $seen = '', string $revoked = 'NULL'): void
    {
        $this->db->query("INSERT INTO sessions (id, token_hash, user_id, created_at, expires_at, last_seen_at, ip, user_agent, revoked_at)
            VALUES (UUID(), SHA2('{$token}', 256), '{$userId}', UTC_TIMESTAMP(), UTC_TIMESTAMP() {$expires}, UTC_TIMESTAMP() {$seen}, '10.0.0.1', 'tes', {$revoked})");
    }

    private function visible(string $token): bool
    {
        return $this->reader->query("SELECT 1 FROM session_identity WHERE token_hash = SHA2('{$token}', 256)")->num_rows === 1;
    }

    public function testKolomUsersSesuaiKontrak(): void
    {
        $kolom = array_column($this->db->query('SHOW COLUMNS FROM users')->fetch_all(MYSQLI_ASSOC), 'Field');
        $this->assertSame([
            'id', 'nomor_induk', 'email', 'nama', 'password_hash', 'must_change_password', 'initial_password_expires_at', 'global_role', 'status',
            'mfa_enabled', 'mfa_secret_enc', 'failed_attempts', 'locked_until', 'idle_logout', 'locale',
            'paste_allowed', 'last_login_at', 'created_by', 'created_at', 'updated_at',
        ], $kolom);
    }

    public function testViewSesiHanyaMemuatKolomKontrak(): void
    {
        $kolom = array_column($this->reader->query('SHOW COLUMNS FROM session_identity')->fetch_all(MYSQLI_ASSOC), 'Field');
        $this->assertSame([
            'token_hash', 'expires_at', 'last_seen_at', 'user_id', 'nomor_induk', 'nama', 'global_role',
            'locale', 'idle_logout', 'paste_allowed', 'must_change_password',
        ], $kolom);
    }

    public function testSesiAktifTerlihat(): void
    {
        $this->user('skema-1');
        $this->session('skema-1', 'tok-aktif');
        $this->assertTrue($this->visible('tok-aktif'));
    }

    public function testSesiDicabutTidakTerlihat(): void
    {
        $this->user('skema-1');
        $this->session('skema-1', 'tok-cabut', revoked: 'UTC_TIMESTAMP()');
        $this->assertFalse($this->visible('tok-cabut'));
    }

    public function testSesiKedaluwarsaTidakTerlihat(): void
    {
        $this->user('skema-1');
        $this->session('skema-1', 'tok-lewat', expires: '- INTERVAL 1 SECOND');
        $this->assertFalse($this->visible('tok-lewat'));
    }

    public function testSesiPenggunaNonaktifTidakTerlihat(): void
    {
        $this->user('skema-1', 'suspended');
        $this->user('skema-2', 'disabled');
        $this->session('skema-1', 'tok-tangguh');
        $this->session('skema-2', 'tok-nonaktif');
        $this->assertFalse($this->visible('tok-tangguh'));
        $this->assertFalse($this->visible('tok-nonaktif'));
    }

    public function testSesiMenganggurHilangHanyaBilaKeluarOtomatisAktif(): void
    {
        $this->user('skema-1', idle: 1);
        $this->user('skema-2', idle: 0);
        $this->session('skema-1', 'tok-idle-31', seen: '- INTERVAL 31 MINUTE');
        $this->session('skema-1', 'tok-idle-29', seen: '- INTERVAL 29 MINUTE');
        $this->session('skema-2', 'tok-bebas-31', seen: '- INTERVAL 31 MINUTE');
        $this->assertFalse($this->visible('tok-idle-31'));
        $this->assertTrue($this->visible('tok-idle-29'));
        $this->assertTrue($this->visible('tok-bebas-31'));
    }

    public function testReaderBolehMembacaTigaObyekSaja(): void
    {
        $this->assertNotFalse($this->reader->query('SELECT 1 FROM user_directory LIMIT 1'));
        $this->assertNotFalse($this->reader->query('SELECT 1 FROM app_access LIMIT 1'));
        foreach (['users', 'sessions', 'password_history', 'mfa_backup_codes', 'login_attempts', 'audit_log'] as $tabel) {
            try {
                $this->reader->query("SELECT 1 FROM {$tabel} LIMIT 1");
                $this->fail("identity_reader bisa membaca {$tabel}");
            } catch (\mysqli_sql_exception $e) {
                $this->assertSame(1142, $e->getCode(), $tabel);
            }
        }
    }

    public function testReaderTidakBisaMenulis(): void
    {
        $this->user('skema-1');
        $perintah = [
            "INSERT INTO app_access (user_id, app, enabled, granted_at) VALUES ('skema-1', 'lokakode', 1, UTC_TIMESTAMP())",
            "UPDATE app_access SET enabled = 1",
            "DELETE FROM app_access",
            "UPDATE session_identity SET nama = 'x'",
            "UPDATE user_directory SET status = 'active'",
        ];
        foreach ($perintah as $sql) {
            try {
                $this->reader->query($sql);
                $this->fail("identity_reader bisa menjalankan: {$sql}");
            } catch (\mysqli_sql_exception $e) {
                $this->assertContains($e->getCode(), [1142, 1143], $sql);
            }
        }
    }

    public function testDirektoriTidakMemuatRahasia(): void
    {
        $kolom = array_column($this->reader->query('SHOW COLUMNS FROM user_directory')->fetch_all(MYSQLI_ASSOC), 'Field');
        $this->assertSame(['id', 'nomor_induk', 'nama', 'global_role', 'status'], $kolom);
    }
}
