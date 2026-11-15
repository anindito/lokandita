<?php

declare(strict_types=1);

namespace Tests\Internal;

use Lokandita\Auth\Testing\Http;
use Tests\Support\AuthCase;

/**
 * KONTRAK.md §5, lewat HTTP sungguhan ke auth-tes dengan token dari lingkungan.
 */
final class InternalApiTest extends AuthCase
{
    /**
     * @return array<string, string>
     */
    private function token(string $app = 'lokakode'): array
    {
        return ['X-Service-Token' => (string) getenv('SERVICE_TOKEN_' . strtoupper($app))];
    }

    private function api(): Http
    {
        // Panggilan internal tidak lewat Caddy: HTTP biasa, tanpa cookie.
        return $this->http();
    }

    public function testTanpaTokenAtauTokenSalahDitolak401DanDicatat(): void
    {
        $tanpa = $this->api()->postJson('/internal/session/touch', ['sid' => str_repeat('a', 43)]);
        $this->assertSame(401, $tanpa->status);
        $this->assertSame(['error' => 'invalid_service_token'], $tanpa->json());
        $catatan = $this->lastAudit('internal.denied');
        $this->assertSame('missing_token', $catatan['detail']['reason']);
        $this->assertSame('/internal/session/touch', $catatan['detail']['path']);
        $this->assertSame($tanpa->ip, $catatan['ip']);

        $salah = $this->api()->postJson('/internal/session/touch', ['sid' => str_repeat('a', 43)], ['X-Service-Token' => 'token-yang-salah']);
        $this->assertSame(401, $salah->status);
        $this->assertSame('wrong_token', $this->lastAudit('internal.denied')['detail']['reason']);

        $this->assertSame(401, $this->api()->get('/internal/users/' . $this->user()['id'])->status);
        $this->assertSame(401, $this->api()->get('/internal/apa-saja')->status, 'rute tak dikenal tanpa token tetap 401');
    }

    public function testCookieSesiSuperadminBukanTokenLayanan(): void
    {
        $http = $this->login($this->user(['global_role' => 'dosen']));
        $this->assertSame(401, $http->get('/internal/users/' . $this->user()['id'])->status);
    }

    public function testRuteTidakDikenalDijawab404Json(): void
    {
        foreach (['/internal/tidak-ada', '/internal', '/internal/session/touch/lagi'] as $jalur) {
            $r = $this->api()->get($jalur, $this->token());
            $this->assertSame(404, $r->status, $jalur);
            $this->assertSame(['error' => 'unknown_internal_route'], $r->json(), $jalur);
        }
        // Metode yang salah pada rute yang ada juga bukan halaman HTML.
        $r = $this->api()->get('/internal/session/touch', $this->token());
        $this->assertSame(404, $r->status);
        $this->assertSame(['error' => 'unknown_internal_route'], $r->json());
    }

    public function testValidasiMenyebutNamaKolom(): void
    {
        $r = $this->api()->postJson('/internal/session/touch', ['sid' => 'sid-palsu-untuk-smoke'], $this->token());
        $this->assertSame(422, $r->status);
        $this->assertSame('validation_failed', $r->json()['error']);
        $this->assertArrayHasKey('sid', $r->json()['fields']);

        $r = $this->api()->postJson('/internal/session/touch', [], $this->token());
        $this->assertSame(422, $r->status);

        $r = $this->api()->send('POST', '/internal/session/touch', 'bukan json', $this->token() + ['Content-Type' => 'application/json']);
        $this->assertSame(422, $r->status);
        $this->assertArrayHasKey('body', $r->json()['fields']);

        $r = $this->api()->postJson('/internal/password-reset', ['requested_by' => 'x', 'user_id' => 5], $this->token());
        $this->assertSame(422, $r->status);
        $this->assertSame(['requested_by', 'user_id'], array_keys($r->json()['fields']));

        $r = $this->api()->get('/internal/users/bukan-uuid', $this->token());
        $this->assertSame(422, $r->status);
    }

    public function testTouchMemperpanjangSesiYangHidup(): void
    {
        $http = $this->login($this->user());
        $sid  = $http->cookies['lokandita_sid'];
        $hash = hash('sha256', $sid);
        $this->db->query("UPDATE sessions SET last_seen_at = UTC_TIMESTAMP() - INTERVAL 10 MINUTE, expires_at = UTC_TIMESTAMP() + INTERVAL 1 HOUR WHERE token_hash = '{$hash}'");

        $r = $this->api()->postJson('/internal/session/touch', ['sid' => $sid], $this->token('portal'));
        $this->assertSame(200, $r->status);
        $this->assertEqualsWithDelta(43200, strtotime($r->json()['expires_at']) - time(), 5);
        $sesi = $this->row("SELECT expires_at, last_seen_at FROM sessions WHERE token_hash = '{$hash}'");
        $this->assertEqualsWithDelta(43200, strtotime($sesi['expires_at'] . ' UTC') - time(), 5);
        $this->assertEqualsWithDelta(0, strtotime($sesi['last_seen_at'] . ' UTC') - time(), 5);
    }

    public function testTouchTidakMenghidupkanSesiYangSudahMati(): void
    {
        $http = $this->login($this->user());
        $sid  = $http->cookies['lokandita_sid'];
        $hash = hash('sha256', $sid);

        $this->db->query("UPDATE sessions SET expires_at = UTC_TIMESTAMP() - INTERVAL 1 SECOND WHERE token_hash = '{$hash}'");
        $r = $this->api()->postJson('/internal/session/touch', ['sid' => $sid], $this->token());
        $this->assertSame(404, $r->status);
        $this->assertSame(['error' => 'session_not_found'], $r->json());
        $this->assertLessThan(time(), strtotime($this->row("SELECT expires_at FROM sessions WHERE token_hash = '{$hash}'")['expires_at'] . ' UTC'));

        // Mahasiswa yang menganggur 31 menit: touch tidak boleh menghidupkannya kembali.
        $mhs  = $this->login($this->user(['global_role' => 'mahasiswa']));
        $h2   = hash('sha256', $mhs->cookies['lokandita_sid']);
        $this->db->query("UPDATE sessions SET last_seen_at = UTC_TIMESTAMP() - INTERVAL 31 MINUTE WHERE token_hash = '{$h2}'");
        $this->assertSame(404, $this->api()->postJson('/internal/session/touch', ['sid' => $mhs->cookies['lokandita_sid']], $this->token())->status);

        $this->assertSame(404, $this->api()->postJson('/internal/session/touch', ['sid' => str_repeat('z', 43)], $this->token())->status);
    }

    public function testRevokeMengakhiriSesiDiSemuaAplikasi(): void
    {
        $user = $this->user();
        $http = $this->login($user);
        $sid  = $http->cookies['lokandita_sid'];
        $this->assertSame(200, $http->get('/')->status);

        $r = $this->api()->postJson('/internal/session/revoke', ['sid' => $sid], $this->token('lokakode'));
        $this->assertSame(200, $r->status);
        $this->assertSame(['ok' => true], $r->json());

        $this->assertRedirect($http->get('/'), '/login?next=https%3A%2F%2Fauth.lokandita.com%2F', 'LokaAuth sendiri menolak sesi itu');
        $this->assertSame(0, (int) $this->row("SELECT COUNT(*) n FROM session_identity WHERE token_hash = '" . hash('sha256', $sid) . "'")['n'], 'view yang dibaca aplikasi klien juga kosong');

        $catatan = $this->lastAudit('logout', $user['id']);
        $this->assertSame('lokakode', $catatan['app'], 'audit mencatat aplikasi pemanggil');

        // Idempoten.
        $this->assertSame(200, $this->api()->postJson('/internal/session/revoke', ['sid' => $sid], $this->token())->status);
        $this->assertSame(200, $this->api()->postJson('/internal/session/revoke', ['sid' => str_repeat('q', 43)], $this->token())->status);
    }

    public function testPrefsMengubahBahasaDanKeluarOtomatis(): void
    {
        $dosen = $this->user(['global_role' => 'dosen']);
        $sid   = $this->login($dosen)->cookies['lokandita_sid'];

        $r = $this->api()->postJson('/internal/session/prefs', ['sid' => $sid, 'locale' => 'ar', 'idle_logout' => true], $this->token('portal'));
        $this->assertSame(200, $r->status);
        $this->assertSame(['locale' => 'ar', 'idle_logout' => true], $r->json());
        $baris = $this->userRow($dosen['id']);
        $this->assertSame('ar', $baris['locale']);
        $this->assertSame('1', (string) $baris['idle_logout']);
        $this->assertSame('portal', $this->lastAudit('prefs.changed', $dosen['id'])['app']);

        $r = $this->api()->postJson('/internal/session/prefs', ['sid' => $sid, 'idle_logout' => false], $this->token());
        $this->assertSame(['locale' => 'ar', 'idle_logout' => false], $r->json());

        $r = $this->api()->postJson('/internal/session/prefs', ['sid' => $sid, 'locale' => 'fr'], $this->token());
        $this->assertSame(422, $r->status);
        $this->assertArrayHasKey('locale', $r->json()['fields']);
        $r = $this->api()->postJson('/internal/session/prefs', ['sid' => $sid, 'idle_logout' => 'ya'], $this->token());
        $this->assertSame(422, $r->status);

        $this->assertSame(404, $this->api()->postJson('/internal/session/prefs', ['sid' => str_repeat('k', 43), 'locale' => 'en'], $this->token())->status);
    }

    public function testMahasiswaTidakBisaMematikanKeluarOtomatis(): void
    {
        $mhs = $this->user(['global_role' => 'mahasiswa']);
        $sid = $this->login($mhs)->cookies['lokandita_sid'];
        $r   = $this->api()->postJson('/internal/session/prefs', ['sid' => $sid, 'idle_logout' => false], $this->token());
        $this->assertSame(422, $r->status);
        $this->assertSame('forced_on_for_mahasiswa', $r->json()['fields']['idle_logout']);
        $this->assertSame('1', (string) $this->userRow($mhs['id'])['idle_logout']);

        $r = $this->api()->postJson('/internal/session/prefs', ['sid' => $sid, 'locale' => 'en'], $this->token());
        $this->assertSame(['locale' => 'en', 'idle_logout' => true], $r->json());
    }

    public function testPenggunaHanyaLimaKolom(): void
    {
        $user = $this->user(['nama' => 'Siti Uji', 'global_role' => 'dosen']);
        $r    = $this->api()->get('/internal/users/' . $user['id'], $this->token());
        $this->assertSame(200, $r->status);
        $this->assertSame(
            ['id' => $user['id'], 'nomor_induk' => $user['nomor_induk'], 'nama' => 'Siti Uji', 'global_role' => 'dosen', 'status' => 'active'],
            $r->json(),
        );
        $this->assertStringNotContainsString('argon2', $r->body);

        $r = $this->api()->get('/internal/users/' . \Lokandita\Auth\Testing\Fixtures::uuid(), $this->token());
        $this->assertSame(404, $r->status);
        $this->assertSame(['error' => 'user_not_found'], $r->json());
    }

    public function testLookupMemisahkanYangAdaDanYangTidak(): void
    {
        $a = $this->user();
        $b = $this->user(['status' => 'disabled']);
        $r = $this->api()->postJson('/internal/users/lookup', ['nomor_induk' => [$a['nomor_induk'], 'tidak-ada-1', $b['nomor_induk'], 'tidak-ada-2', $a['nomor_induk']]], $this->token());
        $this->assertSame(200, $r->status);
        $hasil = $r->json();
        $this->assertEqualsCanonicalizing([$a['nomor_induk'], $b['nomor_induk']], array_column($hasil['found'], 'nomor_induk'));
        $this->assertSame(['id', 'nomor_induk', 'nama', 'global_role'], array_keys($hasil['found'][0]));
        $this->assertSame(['tidak-ada-1', 'tidak-ada-2'], $hasil['missing']);

        $this->assertSame(['found' => [], 'missing' => []], $this->api()->postJson('/internal/users/lookup', ['nomor_induk' => []], $this->token())->json());
        $this->assertSame(422, $this->api()->postJson('/internal/users/lookup', ['nomor_induk' => 'satu'], $this->token())->status);
        $this->assertSame(422, $this->api()->postJson('/internal/users/lookup', ['nomor_induk' => [1, 2]], $this->token())->status);
    }

    public function testResetKataSandiMahasiswa(): void
    {
        $dosen = $this->user(['global_role' => 'dosen']);
        $mhs   = $this->user(['global_role' => 'mahasiswa']);
        $sesi  = $this->login($mhs);
        $this->db->query("UPDATE users SET failed_attempts = 7, locked_until = UTC_TIMESTAMP() + INTERVAL 1 HOUR WHERE id = '{$mhs['id']}'");

        $r = $this->api()->postJson('/internal/password-reset', ['requested_by' => $dosen['id'], 'user_id' => $mhs['id']], $this->token('lokakode'));
        $this->assertSame(200, $r->status);
        $sementara = $r->json()['temp_password'];
        $this->assertGreaterThanOrEqual(10, strlen($sementara));

        $baris = $this->userRow($mhs['id']);
        $this->assertSame('1', (string) $baris['must_change_password']);
        $this->assertNull($baris['locked_until'], 'reset juga membuka kunci');
        $this->assertRedirect($sesi->get('/'), '/login?next=https%3A%2F%2Fauth.lokandita.com%2F', 'sesi lama mahasiswa dicabut');

        $catatan = $this->lastAudit('password.reset', $mhs['id']);
        $this->assertSame($dosen['id'], $catatan['actor_id']);
        $this->assertSame('lokakode', $catatan['app']);
        $this->assertStringNotContainsString($sementara, json_encode($catatan), 'kata sandi tidak masuk log audit');

        $this->assertSame(422, $this->login($mhs)->status, 'kata sandi lama tidak berlaku');
        $baru = $this->login($mhs, password: $sementara);
        $this->assertRedirect($baru, '/ganti-password', 'wajib ganti setelah reset');
    }

    public function testResetLewatApiHanyaUntukMahasiswa(): void
    {
        $dosen = $this->user(['global_role' => 'dosen']);
        foreach (['dosen', 'superadmin'] as $peran) {
            $sasaran = $this->user(['global_role' => $peran]);
            $r       = $this->api()->postJson('/internal/password-reset', ['requested_by' => $dosen['id'], 'user_id' => $sasaran['id']], $this->token());
            $this->assertSame(403, $r->status, $peran);
            $this->assertSame(['error' => 'target_not_allowed'], $r->json());
            $this->assertSame($sasaran['password_hash'], $this->userRow($sasaran['id'])['password_hash'], 'kata sandi tidak berubah');
        }
        $this->assertSame('reset_target_not_mahasiswa', $this->lastAudit('internal.denied')['detail']['reason']);

        $mhs = $this->user();
        $r   = $this->api()->postJson('/internal/password-reset', ['requested_by' => \Lokandita\Auth\Testing\Fixtures::uuid(), 'user_id' => $mhs['id']], $this->token());
        $this->assertSame(422, $r->status, 'peminta tidak dikenal');
        $r = $this->api()->postJson('/internal/password-reset', ['requested_by' => $dosen['id'], 'user_id' => \Lokandita\Auth\Testing\Fixtures::uuid()], $this->token());
        $this->assertSame(404, $r->status);
    }

    public function testApiInternalTidakButuhCsrfDanTidakMemasangCookie(): void
    {
        // Panggilan antar-kontainer memakai HTTP biasa, tanpa header dari Caddy.
        $ch = curl_init(getenv('AUTH_TES_URL') . '/internal/session/touch');
        curl_setopt_array($ch, [
            CURLOPT_POST => true, CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true,
            CURLOPT_POSTFIELDS => '{"sid":"sid-palsu-untuk-smoke"}',
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'X-Service-Token: ' . getenv('SERVICE_TOKEN_LOKAKODE')],
        ]);
        $jawab = (string) curl_exec($ch);
        $this->assertSame(422, curl_getinfo($ch, CURLINFO_RESPONSE_CODE));
        $this->assertStringNotContainsStringIgnoringCase('set-cookie', $jawab);
        $this->assertStringContainsStringIgnoringCase('content-type: application/json', $jawab);
    }
}
