<?php

declare(strict_types=1);

namespace Tests\Login;

use Tests\Support\AuthCase;

/**
 * SPEC.md §4.1, §4.2, §4.6 dan KONTRAK.md §2.
 */
final class LoginTest extends AuthCase
{
    private const PORTAL = 'https://lokandita.com/';

    public function testFormLoginMembawaHeaderKeamanan(): void
    {
        $http = $this->http()->get('/login');
        $this->assertSame(200, $http->status);
        $csp = (string) $http->header('Content-Security-Policy');
        $this->assertMatchesRegularExpression("/script-src 'self' 'nonce-[A-Za-z0-9_-]{20,}'/", $csp);
        $this->assertStringNotContainsString('unsafe-inline', $csp);
        $this->assertStringContainsString("frame-ancestors 'none'", $csp);
        $this->assertSame('nosniff', $http->header('X-Content-Type-Options'));
        $this->assertSame('DENY', $http->header('X-Frame-Options'));
        $this->assertSame('same-origin', $http->header('Referrer-Policy'));
        $this->assertStringContainsString('no-store', (string) $http->header('Cache-Control'));
        $this->assertStringNotContainsString('<script>', $http->body, 'tidak boleh ada skrip sebaris tanpa nonce');
        $this->assertDoesNotMatchRegularExpression('#(src|href)="https?://#', $http->body, 'tidak boleh ada aset dari host lain');
    }

    public function testNonceBerbedaTiapPermintaan(): void
    {
        $a = (string) $this->http()->get('/login')->header('Content-Security-Policy');
        $b = (string) $this->http()->get('/login')->header('Content-Security-Policy');
        $this->assertNotSame($a, $b);
    }

    public function testLoginBerhasilMemasangCookieSesuaiKontrak(): void
    {
        $user = $this->user(['global_role' => 'dosen']);
        $http = $this->login($user);

        $this->assertRedirect($http, self::PORTAL);
        $baris = (string) $http->setCookieLine('lokandita_sid');
        $nilai = $http->cookies['lokandita_sid'] ?? '';
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9_-]{43}$/', $nilai);
        $this->assertMatchesRegularExpression('/;\s*domain=\.lokandita\.com/i', $baris);
        $this->assertMatchesRegularExpression('/;\s*path=\/(;|$)/i', $baris);
        $this->assertMatchesRegularExpression('/;\s*httponly/i', $baris);
        $this->assertMatchesRegularExpression('/;\s*secure/i', $baris);
        $this->assertMatchesRegularExpression('/;\s*samesite=lax/i', $baris);
        $this->assertMatchesRegularExpression('/;\s*max-age=43200/i', $baris);

        // Basis data menyimpan SHA-256 heksadesimal huruf kecil, bukan nilainya.
        $sesi = $this->row("SELECT * FROM sessions WHERE user_id = '{$user['id']}'");
        $this->assertSame(hash('sha256', $nilai), $sesi['token_hash']);
        $this->assertSame(0, (int) $this->row("SELECT COUNT(*) n FROM sessions WHERE token_hash = '" . $this->db->real_escape_string($nilai) . "'")['n']);
        $this->assertEqualsWithDelta(43200, strtotime($sesi['expires_at'] . ' UTC') - time(), 5);
        $this->assertSame($http->ip, $sesi['ip'], 'alamat klien berasal dari X-Forwarded-For milik proksi');

        $this->assertNotNull($this->lastAudit('login.success', $user['id']));
        $this->assertNotNull($this->userRow($user['id'])['last_login_at']);
    }

    public function testCookieCsrfMemakaiAwalanHost(): void
    {
        $http  = $this->http()->get('/login');
        $baris = (string) $http->setCookieLine('__Host-csrf');
        $this->assertNotSame('', $baris);
        $this->assertDoesNotMatchRegularExpression('/domain=/i', $baris);
        $this->assertMatchesRegularExpression('/;\s*secure/i', $baris);
        $this->assertMatchesRegularExpression('/;\s*path=\/(;|$)/i', $baris);
    }

    public function testPostTanpaTokenCsrfDitolak(): void
    {
        $user = $this->user();
        $http = $this->http()->post('/login', ['nomor_induk' => $user['nomor_induk'], 'password' => $user['password']]);
        $this->assertSame(403, $http->status);
        $this->assertArrayNotHasKey('lokandita_sid', $http->cookies);
    }

    public function testKataSandiDisimpanDenganArgon2id(): void
    {
        $user = $this->user(['must_change_password' => 1]);
        $http = $this->login($user);
        $http->get('/ganti-password');
        $http->post('/ganti-password', [
            'csrf' => $http->input('csrf'), 'password_lama' => $user['password'],
            'password_baru' => 'Kalimat-Baru-9911', 'password_ulang' => 'Kalimat-Baru-9911',
        ]);
        $this->assertStringStartsWith('$argon2id$', $this->userRow($user['id'])['password_hash']);
    }

    public function testSalahSandiTidakDikenalDanTerkunciMemberiTeksYangSama(): void
    {
        $user = $this->user();

        $salah = $this->login($user, password: 'bukan-kata-sandinya');
        $this->assertSame(422, $salah->status);
        $this->assertArrayNotHasKey('lokandita_sid', $salah->cookies);
        $teks = $this->toast($salah);
        $this->assertNotSame('', $teks);
        $this->assertSame('wrong_password', $this->lastAudit('login.failed', $user['id'])['detail']['reason']);

        $tidakAda = $this->login(['nomor_induk' => 'tidak-ada-' . bin2hex(random_bytes(3)), 'password' => 'apa-saja-12345']);
        $this->assertSame(422, $tidakAda->status);
        $this->assertSame($teks, $this->toast($tidakAda));

        $this->db->query("UPDATE users SET locked_until = UTC_TIMESTAMP() + INTERVAL 10 MINUTE WHERE id = '{$user['id']}'");
        $terkunci = $this->login($user);
        $this->assertSame(422, $terkunci->status, 'kata sandi benar tetap ditolak selama terkunci');
        $this->assertSame($teks, $this->toast($terkunci));
        $this->assertArrayNotHasKey('lokandita_sid', $terkunci->cookies);
        $this->assertSame('locked', $this->lastAudit('login.failed', $user['id'])['detail']['reason']);
    }

    public function testKunciBertahapSatuLimaLimaBelasEnamPuluhMenit(): void
    {
        $user = $this->user();
        $http = $this->http();

        foreach ([1, 5, 15, 60, 60] as $putaran => $menit) {
            for ($i = 1; $i <= 5; $i++) {
                $kunci = $this->userRow($user['id'])['locked_until'];
                $this->assertTrue($kunci === null || strtotime($kunci . ' UTC') < time(), "belum terkunci sebelum gagal ke-{$i} putaran {$putaran}");
                $this->login($user, password: 'salah-salah-salah', http: $http);
            }
            $baris = $this->userRow($user['id']);
            $this->assertNotNull($baris['locked_until'], "terkunci setelah 5 kegagalan, putaran {$putaran}");
            $this->assertEqualsWithDelta($menit * 60, strtotime($baris['locked_until'] . ' UTC') - time(), 10, "putaran {$putaran} mengunci {$menit} menit");

            // Selama terkunci, percobaan tidak menambah hitungan dan tidak memperpanjang kunci.
            $this->login($user, password: 'salah-salah-salah', http: $http);
            $this->assertSame($baris['locked_until'], $this->userRow($user['id'])['locked_until']);
            $this->assertSame($baris['failed_attempts'], $this->userRow($user['id'])['failed_attempts']);

            // Waktu kunci habis.
            $this->db->query("UPDATE users SET locked_until = UTC_TIMESTAMP() - INTERVAL 1 SECOND WHERE id = '{$user['id']}'");
        }

        // Login yang benar setelah kunci habis mengembalikan hitungan ke nol.
        $ok = $this->login($user, http: $http);
        $this->assertRedirect($ok, self::PORTAL);
        $this->assertSame('0', (string) $this->userRow($user['id'])['failed_attempts']);
        $this->assertNull($this->userRow($user['id'])['locked_until']);
    }

    public function testKunciDisimpanDiBasisDataBukanHanyaRedis(): void
    {
        $user = $this->user();
        for ($i = 0; $i < 5; $i++) {
            $this->login($user, password: 'salah-salah-salah');
        }
        // Tiap percobaan datang dari alamat berbeda dan Redis tidak dipakai: kunci tetap berlaku.
        $this->assertNotNull($this->userRow($user['id'])['locked_until']);
        $this->assertSame(422, $this->login($user)->status);
    }

    public function testAkunDitangguhkanDanDinonaktifkanTidakBisaMasuk(): void
    {
        foreach (['suspended', 'disabled'] as $status) {
            $user = $this->user(['status' => $status]);
            $http = $this->login($user);
            $this->assertSame(422, $http->status, $status);
            $this->assertArrayNotHasKey('lokandita_sid', $http->cookies);
            $this->assertSame('status_' . $status, $this->lastAudit('login.failed', $user['id'])['detail']['reason']);
        }
    }

    public function testBatasPerIp(): void
    {
        $ip   = '198.51.100.' . random_int(1, 254);
        $user = $this->user();
        $stmt = $this->db->prepare("INSERT INTO login_attempts (nomor_induk, ip, success, created_at) VALUES ('semprot', ?, 0, UTC_TIMESTAMP())");
        for ($i = 0; $i < 99; $i++) {
            $stmt->execute([$ip]);
        }
        $this->assertRedirect($this->login($user, http: $this->http($ip)), self::PORTAL, '99 kegagalan: masih boleh');

        $stmt->execute([$ip]);
        $ditolak = $this->login($user, http: $this->http($ip));
        $this->assertSame(422, $ditolak->status, '100 kegagalan dari satu alamat: ditolak walau kata sandi benar');
        $this->assertSame('ip_limited', $this->lastAudit('login.failed')['detail']['reason']);
        $this->assertNull($this->userRow($user['id'])['locked_until'], 'batas IP tidak mengunci akun orang');

        $this->assertRedirect($this->login($user), self::PORTAL, 'alamat lain tidak terpengaruh');

        $this->db->query("UPDATE login_attempts SET created_at = UTC_TIMESTAMP() - INTERVAL 16 MINUTE WHERE ip = '{$ip}'");
        $this->assertRedirect($this->login($user, http: $this->http($ip)), self::PORTAL, 'setelah 15 menit hitungan hilang');
    }

    public function testNextHanyaDiterimaUntukHostYangDiizinkan(): void
    {
        $user = $this->user(['global_role' => 'dosen']);

        foreach (['https://lokakode.lokandita.com/kelas/3?tab=nilai', 'https://auth.lokandita.com/', 'https://lokandita.com/apa'] as $next) {
            $this->assertRedirect($this->login($user, $next), $next, $next);
        }

        $jahat = [
            'http://lokakode.lokandita.com/',
            'https://evil.example/',
            'https://lokandita.com.evil.example/',
            'https://evil.example/?https://lokandita.com/',
            'https://lokandita.com@evil.example/',
            'https://evil.example\\@lokandita.com/',
            '//evil.example/',
            '/\\evil.example',
            'javascript:alert(1)',
            'https://lokandita.com:8443/',
            'https://LOKANDITA.COM.evil.example',
            "https://lokandita.com/\r\nSet-Cookie: x=1",
            'https://lokaujian.lokandita.com/',
        ];
        foreach ($jahat as $next) {
            $this->assertRedirect($this->login($user, $next), self::PORTAL, 'harus ke Portal: ' . json_encode($next));
        }
    }

    public function testFormLoginMembuangNextYangTidakSah(): void
    {
        $http = $this->http()->get('/login?next=' . rawurlencode('https://evil.example/'));
        $this->assertNull($http->input('next'));
        $http = $this->http()->get('/login?next=' . rawurlencode('https://lokakode.lokandita.com/x'));
        $this->assertSame('https://lokakode.lokandita.com/x', $http->input('next'));
    }

    public function testSuperadminTidakMendapatSesiTanpaMfa(): void
    {
        $user = $this->user(['global_role' => 'superadmin']);
        $http = $this->login($user);
        $this->assertArrayNotHasKey('lokandita_sid', $http->cookies);
        $this->assertSame(0, (int) $this->row("SELECT COUNT(*) n FROM sessions WHERE user_id = '{$user['id']}'")['n']);
    }

    public function testBahasaHalamanLogin(): void
    {
        $http = $this->http();
        $this->assertStringContainsString('<html lang="id" dir="ltr">', $http->get('/login')->body);
        $http->get('/bahasa/en');
        $this->assertStringContainsString('<html lang="en" dir="ltr">', $http->get('/login')->body);
        $this->assertStringContainsString('Sign in', $http->body);
        $http->get('/bahasa/ar');
        $this->assertStringContainsString('<html lang="ar" dir="rtl">', $http->get('/login')->body);
        $http->get('/bahasa/xx');
        $this->assertStringContainsString('<html lang="ar" dir="rtl">', $http->get('/login')->body, 'kode bahasa tak dikenal diabaikan');
    }
}
