<?php

declare(strict_types=1);

namespace Tests\Akun;

use Tests\Support\AuthCase;

/**
 * Ekspor data sendiri dari LokaAuth (SPEC.md §16, KONTRAK.md §14), lewat HTTP sungguhan.
 * Yang terpenting: tidak ada rahasia (hash kata sandi, rahasia MFA, token sesi) dan
 * tidak ada data orang lain di dalam berkas.
 */
final class DataSayaTest extends AuthCase
{
    /**
     * @param array<string, mixed> $user
     *
     * @return array{0: \Lokandita\Auth\Testing\Http, 1: array<string, mixed>}
     */
    private function unduh(array $user, ?\Lokandita\Auth\Testing\Http $http = null): array
    {
        $http ??= $this->sebagai($user);
        $http->get('/data-saya');
        $r = $http->post('/data-saya/unduh', ['csrf' => (string) $http->input('csrf')]);
        $this->assertSame(200, $r->status, $r->body);

        return [$r, json_decode($r->body, true, 512, JSON_THROW_ON_ERROR)];
    }

    public function testBerkasBerisiDataSendiriTanpaRahasia(): void
    {
        $user = $this->user(['nama' => 'Ani Pemilik Data', 'email' => 'ani.' . bin2hex(random_bytes(3)) . '@contoh.test', 'global_role' => 'mahasiswa']);
        // Login sungguhan, supaya ada sesi, percobaan masuk dan baris audit yang nyata.
        $http = $this->login($user);
        $this->assertArrayHasKey('lokandita_sid', $http->cookies);
        $sid = $http->cookies['lokandita_sid'];
        [$r, $data] = $this->unduh($user, $http);

        $this->assertStringContainsString('application/json', (string) $r->header('Content-Type'));
        $this->assertSame('attachment; filename="lokandita-auth-data-saya.json"', $r->header('Content-Disposition'));
        $this->assertStringContainsString('no-store', (string) $r->header('Cache-Control'));
        $this->assertSame('auth', $data['aplikasi']);
        $this->assertSame($user['id'], $data['pengguna']);
        $this->assertSame([], $data['terpotong']);

        $this->assertSame(
            ['id', 'nomor_induk', 'nama', 'email', 'global_role', 'status', 'locale', 'idle_logout', 'paste_allowed', 'mfa_enabled', 'must_change_password', 'created_at', 'last_login_at'],
            array_keys($data['akun']),
            'kolom akun persis seperti KONTRAK.md §14',
        );
        $this->assertSame($user['nomor_induk'], $data['akun']['nomor_induk']);
        $this->assertSame('Ani Pemilik Data', $data['akun']['nama']);
        $this->assertSame($user['email'], $data['akun']['email']);
        $this->assertTrue($data['akun']['idle_logout']);
        $this->assertFalse($data['akun']['mfa_enabled']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d\d-\d\dT\d\d:\d\d:\d\dZ$/', $data['akun']['created_at']);

        $apps = array_column($data['akses_aplikasi'], 'app');
        sort($apps);
        $this->assertSame(['lokakode', 'portal'], $apps);
        $this->assertCount(1, $data['sesi']);
        $this->assertSame(['created_at', 'last_seen_at', 'expires_at', 'revoked_at', 'ip', 'user_agent'], array_keys($data['sesi'][0]));
        $this->assertSame($http->ip, $data['sesi'][0]['ip']);
        $this->assertTrue($data['percobaan_masuk'][0]['success']);
        $aksi = array_column($data['riwayat'], 'action');
        $this->assertContains('login.success', $aksi);
        $this->assertSame(['created_at', 'action', 'app', 'sebagai'], array_keys($data['riwayat'][0]));

        // Tidak ada rahasia di mana pun dalam berkas.
        $baris = $this->db->query("SELECT password_hash FROM users WHERE id = '{$user['id']}'")->fetch_assoc();
        foreach ([$baris['password_hash'], $user['password'], $sid, hash('sha256', $sid), 'password_hash', 'token_hash', 'mfa_secret', 'argon2', 'detail'] as $rahasia) {
            $this->assertStringNotContainsStringIgnoringCase((string) $rahasia, $r->body, "rahasia di berkas: {$rahasia}");
        }

        // Dicatat di log audit.
        $audit = $this->db->query("SELECT actor_id, target_id, app FROM audit_log WHERE action = 'data.diekspor'")->fetch_all(MYSQLI_ASSOC);
        $this->assertSame([['actor_id' => $user['id'], 'target_id' => $user['id'], 'app' => 'auth']], array_values(array_filter($audit, static fn ($a) => $a['actor_id'] === $user['id'])));
    }

    /**
     * Kasus IDOR: tidak ada cara meminta data orang lain, dan baris audit tentang orang lain
     * (dibuat oleh superadmin) tidak membocorkan nama atau nomor induk mereka.
     */
    public function testTidakMemuatDataOrangLainJugaBagiSuperadmin(): void
    {
        [$adminHttp, $admin] = $this->admin();
        $korban = $this->user(['nama' => 'Korban Rahasia', 'email' => 'korban.' . bin2hex(random_bytes(3)) . '@contoh.test']);
        // Superadmin bertindak atas korban: baris audit dengan detail yang menyebut korban.
        $this->db->query("INSERT INTO audit_log (actor_id, action, target_id, app, detail, created_at) VALUES ('{$admin['id']}', 'user.updated', '{$korban['id']}', 'auth', '{\"nama\":\"Korban Rahasia\",\"nomor_induk\":\"{$korban['nomor_induk']}\"}', UTC_TIMESTAMP())");

        // Parameter apa pun diabaikan: superadmin tetap hanya mendapat datanya sendiri.
        $adminHttp->get('/data-saya');
        $r = $adminHttp->post('/data-saya/unduh?user_id=' . $korban['id'], ['csrf' => (string) $adminHttp->input('csrf'), 'user_id' => $korban['id'], 'id' => $korban['id']]);
        $this->assertSame(200, $r->status);
        $data = json_decode($r->body, true);
        $this->assertSame($admin['id'], $data['pengguna']);
        $this->assertSame($admin['nomor_induk'], $data['akun']['nomor_induk']);
        $this->assertContains('user.updated', array_column($data['riwayat'], 'action'), 'tindakannya sendiri tercatat sebagai pelaku');
        foreach (['Korban Rahasia', $korban['nomor_induk'], $korban['email'], $korban['id']] as $asing) {
            $this->assertStringNotContainsString((string) $asing, $r->body, "data orang lain di berkas: {$asing}");
        }

        // Korban melihat bahwa ada tindakan atas akunnya, tanpa identitas pelakunya.
        [$rk, $dk] = $this->unduh($korban);
        $sasaran = array_values(array_filter($dk['riwayat'], static fn ($b) => $b['action'] === 'user.updated'));
        $this->assertSame('sasaran', $sasaran[0]['sebagai']);
        $this->assertStringNotContainsString($admin['id'], $rk->body);
        $this->assertStringNotContainsString($admin['nomor_induk'], $rk->body);

        // Tidak ada rute ber-id.
        $this->assertSame(404, $adminHttp->get('/data-saya/' . $korban['id'])->status);
    }

    public function testTanpaSesiTanpaCsrfDanTerlaluCepatDitolak(): void
    {
        $r = $this->http()->get('/data-saya');
        $this->assertContains($r->status, [302, 303, 307]);
        $this->assertStringContainsString('/login', (string) $r->header('Location'));
        $this->assertNotSame(200, $this->http()->post('/data-saya/unduh')->status);

        $user = $this->user();
        $http = $this->sebagai($user);
        $hal  = $http->get('/data-saya');
        $this->assertSame(200, $hal->status);
        $this->assertStringContainsString('<meta name="robots" content="noindex, nofollow">', $hal->body);
        $this->assertStringContainsString('no-store', (string) $hal->header('Cache-Control'));
        $csrf = (string) $http->input('csrf');
        $this->assertSame(403, $http->post('/data-saya/unduh')->status, 'tanpa CSRF');
        $jumlah = fn (): string => $this->db->query("SELECT COUNT(*) FROM audit_log WHERE action = 'data.diekspor' AND actor_id = '{$user['id']}'")->fetch_row()[0];
        $this->assertSame('0', $jumlah());

        $this->assertSame(200, $http->post('/data-saya/unduh', ['csrf' => $csrf])->status);
        // Sekali per 60 detik per pengguna (KONTRAK.md §14).
        $kedua = $http->post('/data-saya/unduh', ['csrf' => $csrf]);
        $this->assertSame(429, $kedua->status);
        $this->assertSame('60', $kedua->header('Retry-After'));
        $this->assertStringNotContainsString($user['nomor_induk'] . '"', $kedua->body);
        $this->assertSame('1', $jumlah());
    }

    /**
     * Wajib ganti kata sandi berlaku juga di sini: tidak ada unduhan sebelum kata sandi awal diganti.
     */
    public function testWajibGantiKataSandiLebihDulu(): void
    {
        $user = $this->user(['must_change_password' => 1]);
        $http = $this->sebagai($user);
        $this->assertStringContainsString('/ganti-password', (string) $http->get('/data-saya')->header('Location'));
    }
}
