<?php

declare(strict_types=1);

namespace Lokandita\Auth\Tests;

use Lokandita\Auth\AuthClient;
use Lokandita\Auth\Testing\Fixtures;
use Lokandita\Auth\Testing\Http;
use Lokandita\Auth\Testing\TestSchema;
use PHPUnit\Framework\TestCase;

/**
 * KONTRAK.md §4 dan menu akun (SPEC.md §4.5), dijalankan di setiap aplikasi klien.
 *
 * Tiga kontainer sungguhan terlibat: proses tes ini, <app>-tes (aplikasi klien
 * yang diuji), dan auth-tes (LokaAuth). Login dilakukan lewat form LokaAuth;
 * aplikasi klien berbicara dengan LokaAuth lewat auth-internal-tes.
 */
final class ClientAppTest extends TestCase
{
    private \mysqli $db;

    private string $app;

    private string $appUrl;

    protected function setUp(): void
    {
        $this->db     = TestSchema::connect((string) getenv('TES_IDENTITY_DSN'));
        $this->app    = (string) getenv('LOKANDITA_APP');
        $this->appUrl = rtrim((string) getenv('APP_URL'), '/');
    }

    /**
     * Login di LokaAuth, lalu membawa cookie itu ke aplikasi klien — seperti peramban.
     *
     * @param array<string, mixed> $over
     *
     * @return array{0: Http, 1: array<string, mixed>, 2: Http} klien aplikasi, pengguna, klien LokaAuth
     */
    private function masuk(array $over = []): array
    {
        $user = Fixtures::user($this->db, $over + ['global_role' => 'dosen']);
        $auth = Fixtures::login(new Http((string) getenv('AUTH_TES_URL')), $user);
        $this->assertArrayHasKey('lokandita_sid', $auth->cookies, 'login di LokaAuth gagal');

        $klien = new Http((string) getenv('APP_TES_URL'));
        $klien->cookies['lokandita_sid'] = $auth->cookies['lokandita_sid'];

        return [$klien, $user, $auth];
    }

    private function loginUrl(string $path = '/beranda'): string
    {
        return 'https://auth.lokandita.com/login?next=' . rawurlencode($this->appUrl . $path);
    }

    private function hash(Http $klien): string
    {
        return hash('sha256', $klien->cookies['lokandita_sid']);
    }

    public function testTanpaCookieDialihkanKeLoginDenganNext(): void
    {
        $r = (new Http((string) getenv('APP_TES_URL')))->get('/beranda?x=1');
        $this->assertContains($r->status, [302, 303, 307]);
        $this->assertSame($this->loginUrl('/beranda?x=1'), $r->header('Location'));

        // next dibangun dari APP_URL, bukan dari header Host yang dikirim klien.
        $r = (new Http((string) getenv('APP_TES_URL')))->get('/beranda', ['Host' => 'evil.example']);
        $this->assertSame($this->loginUrl(), $r->header('Location'));
    }

    public function testCookieKaranganDialihkanKeLogin(): void
    {
        $klien = new Http((string) getenv('APP_TES_URL'));
        foreach ([str_repeat('A', 43), 'pendek', "x' OR '1'='1"] as $nilai) {
            $klien->cookies['lokandita_sid'] = $nilai;
            $this->assertSame($this->loginUrl(), $klien->get('/beranda')->header('Location'), $nilai);
        }
    }

    public function testSesiLokaAuthBerlakuDiAplikasiIniTanpaLoginKedua(): void
    {
        [$klien, $user] = $this->masuk(['nama' => 'Budi Santoso']);
        $r              = $klien->get('/beranda');
        $this->assertSame(200, $r->status);
        $this->assertStringContainsString('Budi Santoso', $r->body);
        $this->assertStringContainsString($user['nomor_induk'], $r->body);
        $this->assertStringContainsString('>BS<', $r->body, 'inisial di tombol menu akun');
        $this->assertStringContainsString('no-store', (string) $r->header('Cache-Control'));
        $this->assertSame('DENY', $r->header('X-Frame-Options'));
        $this->assertMatchesRegularExpression("/script-src 'self' 'nonce-/", (string) $r->header('Content-Security-Policy'));
    }

    public function testMenuAkunLengkapDanKeluarButirTerakhir(): void
    {
        [$klien] = $this->masuk();
        $isi     = $klien->get('/beranda')->body;
        $this->assertSame(1, preg_match('/<details class="akun".*?<\/details>/s', $isi, $m));
        $menu = $m[0];
        $this->assertStringContainsString('https://auth.lokandita.com/ganti-password', $menu);
        $this->assertStringContainsString('name="locale" value="en"', $menu);
        $this->assertStringContainsString('name="idle_logout"', $menu);
        $this->assertStringContainsString('Dosen', $menu, 'peran ditampilkan');
        // Keluar: butir terakhir, berlabel teks, form POST dengan CSRF.
        $terakhir = substr($menu, (int) strrpos($menu, '<form'));
        $this->assertStringContainsString('action="/keluar"', $terakhir);
        $this->assertStringContainsString('method="post"', $terakhir);
        $this->assertStringContainsString('name="csrf"', $terakhir);
        $this->assertMatchesRegularExpression('/class="akun-butir akun-keluar">Keluar</', $terakhir);
    }

    public function testKeluarMengakhiriSesiDiSemuaAplikasi(): void
    {
        [$klien, $user, $auth] = $this->masuk();
        $sid                   = $klien->cookies['lokandita_sid'];
        $halaman               = $klien->get('/beranda');
        $this->assertSame(200, $auth->get('/')->status, 'sebelum keluar: LokaAuth menerima sesi');

        $r = $klien->post('/keluar', ['csrf' => $halaman->input('csrf')]);
        $this->assertContains($r->status, [302, 303]);
        $this->assertSame('https://auth.lokandita.com/keluar-selesai', $r->header('Location'));

        // Sesi dicabut di basis data LokaAuth lewat API internal — HTTP sungguhan antar-kontainer.
        $sesi = $this->db->query("SELECT revoked_at FROM sessions WHERE token_hash = '" . hash('sha256', $sid) . "'")->fetch_assoc();
        $this->assertNotNull($sesi['revoked_at']);

        // Cookie lama ditolak di aplikasi ini…
        $lama = new Http((string) getenv('APP_TES_URL'));
        $lama->cookies['lokandita_sid'] = $sid;
        $this->assertSame($this->loginUrl(), $lama->get('/beranda')->header('Location'));
        // …dan di LokaAuth, yang lalu menghapus cookie di peramban.
        $this->assertContains($auth->get('/')->status, [302, 303]);
        $selesai = $auth->get('/keluar-selesai');
        $this->assertSame('https://auth.lokandita.com/login?keluar=1', $selesai->header('Location'));
        $this->assertArrayNotHasKey('lokandita_sid', $auth->cookies);

        $audit = $this->db->query("SELECT app FROM audit_log WHERE action = 'logout' AND target_id = '{$user['id']}' ORDER BY id DESC LIMIT 1")->fetch_assoc();
        $this->assertSame($this->app, $audit['app'], 'audit LokaAuth mencatat aplikasi yang meminta keluar');
    }

    public function testKeluarTanpaCsrfDitolakDanSesiTetapHidup(): void
    {
        [$klien] = $this->masuk();
        $this->assertSame(403, $klien->post('/keluar')->status);
        $this->assertSame(200, $klien->get('/beranda')->status);
    }

    public function testSesiYangDicabutDiLokaAuthLangsungDitolakDiSini(): void
    {
        [$klien, , $auth] = $this->masuk();
        $this->assertSame(200, $klien->get('/beranda')->status);
        $halaman = $auth->get('/');
        $auth->post('/keluar', ['csrf' => $halaman->input('csrf')]);
        $this->assertSame($this->loginUrl(), $klien->get('/beranda')->header('Location'), 'tidak ada cache sesi di aplikasi klien');
    }

    public function testWajibGantiKataSandiDibawaKeLokaAuth(): void
    {
        [$klien] = $this->masuk(['must_change_password' => 1]);
        $r       = $klien->get('/beranda');
        $this->assertSame('https://auth.lokandita.com/ganti-password?next=' . rawurlencode($this->appUrl . '/beranda'), $r->header('Location'));
    }

    public function testTanpaAksesAplikasiDitolak403DenganTautanPortal(): void
    {
        $lain = $this->app === 'lokakode' ? 'portal' : 'lokakode';
        [$klien, $user] = $this->masuk(['apps' => [$lain]]);
        $r = $klien->get('/beranda');
        $this->assertSame(403, $r->status);
        $this->assertStringContainsString('href="https://lokandita.com"', $r->body);
        $this->assertStringNotContainsString('class="kartu">' . "\n" . '    <h1>Halo', $r->body);

        // Akses yang dimatikan (enabled = 0) sama dengan tidak punya akses.
        $this->db->query("INSERT INTO app_access (user_id, app, enabled, granted_at) VALUES ('{$user['id']}', '{$this->app}', 0, UTC_TIMESTAMP())");
        $this->assertSame(403, $klien->get('/beranda')->status);
        $this->db->query("UPDATE app_access SET enabled = 1 WHERE user_id = '{$user['id']}' AND app = '{$this->app}'");
        $this->assertSame(200, $klien->get('/beranda')->status);
    }

    public function testAkunDitangguhkanDanSesiKedaluwarsaDitolak(): void
    {
        [$klien, $user] = $this->masuk();
        $this->db->query("UPDATE users SET status = 'suspended' WHERE id = '{$user['id']}'");
        $this->assertSame($this->loginUrl(), $klien->get('/beranda')->header('Location'));

        [$klien] = $this->masuk();
        $this->db->query("UPDATE sessions SET expires_at = UTC_TIMESTAMP() - INTERVAL 1 SECOND WHERE token_hash = '{$this->hash($klien)}'");
        $this->assertSame($this->loginUrl(), $klien->get('/beranda')->header('Location'));
    }

    public function testMahasiswaMenganggurTigaPuluhMenitDitolak(): void
    {
        [$klien] = $this->masuk(['global_role' => 'mahasiswa']);
        $this->db->query("UPDATE sessions SET last_seen_at = UTC_TIMESTAMP() - INTERVAL 29 MINUTE WHERE token_hash = '{$this->hash($klien)}'");
        $this->assertSame(200, $klien->get('/beranda')->status, '29 menit: masih masuk, dan permintaan ini memperpanjang sesi');
        $this->db->query("UPDATE sessions SET last_seen_at = UTC_TIMESTAMP() - INTERVAL 31 MINUTE WHERE token_hash = '{$this->hash($klien)}'");
        $this->assertSame($this->loginUrl(), $klien->get('/beranda')->header('Location'));
    }

    public function testSesiDiperpanjangLewatLokaAuthSetelahLimaMenit(): void
    {
        [$klien] = $this->masuk();
        $h       = $this->hash($klien);
        $baca    = fn () => $this->db->query("SELECT TIMESTAMPDIFF(SECOND, last_seen_at, UTC_TIMESTAMP()) AS lalu, TIMESTAMPDIFF(SECOND, UTC_TIMESTAMP(), expires_at) AS sisa FROM sessions WHERE token_hash = '{$h}'")->fetch_assoc();

        $this->db->query("UPDATE sessions SET last_seen_at = UTC_TIMESTAMP() - INTERVAL 4 MINUTE, expires_at = UTC_TIMESTAMP() + INTERVAL 1 HOUR WHERE token_hash = '{$h}'");
        $r = $klien->get('/beranda');
        $this->assertGreaterThan(200, (int) $baca()['lalu'], 'belum 5 menit: tidak ada panggilan touch');
        $this->assertNull($r->setCookieLine('lokandita_sid'));

        $this->db->query("UPDATE sessions SET last_seen_at = UTC_TIMESTAMP() - INTERVAL 6 MINUTE WHERE token_hash = '{$h}'");
        $r = $klien->get('/beranda');
        $this->assertSame(200, $r->status);
        $this->assertLessThan(10, (int) $baca()['lalu'], 'lebih dari 5 menit: aplikasi memanggil touch di LokaAuth');
        $this->assertEqualsWithDelta(43200, (int) $baca()['sisa'], 10);
        $cookie = (string) $r->setCookieLine('lokandita_sid');
        $this->assertMatchesRegularExpression('/max-age=43200/i', $cookie, 'cookie yang sama dikirim ulang dengan umur baru');
        $this->assertMatchesRegularExpression('/domain=\.lokandita\.com/i', $cookie);
        $this->assertMatchesRegularExpression('/httponly/i', $cookie);
        $this->assertStringContainsString($klien->cookies['lokandita_sid'], $cookie);
    }

    public function testTetapMasukMemperpanjangSesi(): void
    {
        [$klien] = $this->masuk();
        $h       = $this->hash($klien);
        $halaman = $klien->get('/beranda');
        $this->assertSame(1, preg_match('/data-sesi-sisa="(\d+)"/', $halaman->body, $m));
        $this->assertEqualsWithDelta(43200, (int) $m[1], 30);
        $this->assertStringContainsString('data-sesi-tetap', $halaman->body);

        $this->db->query("UPDATE sessions SET expires_at = UTC_TIMESTAMP() + INTERVAL 90 SECOND WHERE token_hash = '{$h}'");
        $r = $klien->post('/sesi/perpanjang', ['csrf' => $halaman->input('csrf')]);
        $this->assertSame(200, $r->status);
        $this->assertEqualsWithDelta(43200, $r->json()['sisa'], 10);
        $sisa = (int) $this->db->query("SELECT TIMESTAMPDIFF(SECOND, UTC_TIMESTAMP(), expires_at) AS s FROM sessions WHERE token_hash = '{$h}'")->fetch_assoc()['s'];
        $this->assertEqualsWithDelta(43200, $sisa, 10);
        $this->assertMatchesRegularExpression('/max-age=43200/i', (string) $r->setCookieLine('lokandita_sid'));

        $this->assertSame(403, $klien->post('/sesi/perpanjang')->status, 'tanpa CSRF ditolak');
    }

    public function testPreferensiBahasaDisimpanDiLokaAuth(): void
    {
        [$klien, $user] = $this->masuk();
        $halaman        = $klien->get('/beranda');
        $this->assertStringContainsString('<html lang="id" dir="ltr">', $halaman->body);

        $r = $klien->post('/preferensi', ['csrf' => $halaman->input('csrf'), 'locale' => 'ar', 'kembali' => '/beranda']);
        $this->assertSame($this->appUrl . '/beranda', $r->header('Location'));
        $this->assertSame('ar', $this->db->query("SELECT locale FROM users WHERE id = '{$user['id']}'")->fetch_assoc()['locale']);
        $arab = $klien->get('/beranda')->body;
        $this->assertStringContainsString('<html lang="ar" dir="rtl">', $arab);
        $this->assertStringContainsString('تسجيل الخروج', $arab);

        // Bahasa mengikuti akun: LokaAuth pun kini berbahasa Arab untuk pengguna ini.
        $auth = new Http((string) getenv('AUTH_TES_URL'));
        $auth->cookies['lokandita_sid'] = $klien->cookies['lokandita_sid'];
        $this->assertStringContainsString('<html lang="ar" dir="rtl">', $auth->get('/')->body);

        $klien->post('/preferensi', ['csrf' => $klien->get('/beranda')->input('csrf'), 'locale' => 'xx', 'kembali' => '/beranda']);
        $this->assertSame('ar', $this->db->query("SELECT locale FROM users WHERE id = '{$user['id']}'")->fetch_assoc()['locale'], 'kode bahasa tak dikenal diabaikan');
    }

    public function testTujuanKembaliHanyaJalurLokal(): void
    {
        [$klien] = $this->masuk();
        foreach (['https://evil.example/', '//evil.example/', '/\\evil.example', 'evil'] as $jahat) {
            $r = $klien->post('/preferensi', ['csrf' => $klien->get('/beranda')->input('csrf'), 'locale' => 'id', 'kembali' => $jahat]);
            $this->assertSame($this->appUrl . '/', $r->header('Location'), $jahat);
        }
    }

    public function testKeluarOtomatisBisaDiubahDosenTetapiTidakMahasiswa(): void
    {
        [$dosen, $d] = $this->masuk(['global_role' => 'dosen']);
        $dosen->post('/preferensi', ['csrf' => $dosen->get('/beranda')->input('csrf'), 'idle_logout' => '1', 'kembali' => '/beranda']);
        $this->assertSame('1', (string) $this->db->query("SELECT idle_logout FROM users WHERE id = '{$d['id']}'")->fetch_assoc()['idle_logout']);
        $this->assertStringContainsString('data-sesi-idle="1"', $dosen->get('/beranda')->body);
        $dosen->post('/preferensi', ['csrf' => $dosen->get('/beranda')->input('csrf'), 'idle_logout' => '0', 'kembali' => '/beranda']);
        $this->assertSame('0', (string) $this->db->query("SELECT idle_logout FROM users WHERE id = '{$d['id']}'")->fetch_assoc()['idle_logout']);

        [$mhs, $m] = $this->masuk(['global_role' => 'mahasiswa']);
        $halaman   = $mhs->get('/beranda');
        $this->assertMatchesRegularExpression('/name="idle_logout"[^>]*disabled/', $halaman->body, 'saklar terkunci untuk mahasiswa');
        $mhs->post('/preferensi', ['csrf' => $halaman->input('csrf'), 'idle_logout' => '0', 'kembali' => '/beranda']);
        $this->assertSame('1', (string) $this->db->query("SELECT idle_logout FROM users WHERE id = '{$m['id']}'")->fetch_assoc()['idle_logout'], 'ditolak LokaAuth walau form dipalsukan');
    }

    public function testAkarDialihkanKeBeranda(): void
    {
        $r = (new Http((string) getenv('APP_TES_URL')))->get('/');
        $this->assertSame($this->appUrl . '/beranda', $r->header('Location'));
    }

    public function testKlienLokaAuthMemakaiTokenAplikasiIni(): void
    {
        $user  = Fixtures::user($this->db);
        $klien = new AuthClient();
        $this->assertSame($user['nomor_induk'], $klien->user($user['id'])['nomor_induk']);
        $hasil = $klien->lookup([$user['nomor_induk'], 'tidak-ada']);
        $this->assertSame(['tidak-ada'], $hasil['missing']);
        $this->assertNull($klien->touch(str_repeat('n', 43)), 'sesi tidak dikenal');
    }

    public function testKegagalanKlienTidakMelemparGalat(): void
    {
        // Token salah: 401 dari LokaAuth. Alamat mati: tidak tersambung. Keduanya dijawab, bukan dilempar.
        $salahToken = new AuthClient(null, 'token-yang-salah');
        $this->assertNull($salahToken->touch(str_repeat('n', 43)));
        $this->assertFalse($salahToken->revoke(str_repeat('n', 43)));
        $this->assertNull($salahToken->user(Fixtures::uuid()));

        $mati = new AuthClient('http://auth-internal-tes:9', null, 1);
        $this->assertNull($mati->touch(str_repeat('n', 43)));
        $this->assertFalse($mati->revoke(str_repeat('n', 43)), 'keluar tidak boleh dilaporkan berhasil bila LokaAuth tidak terjangkau');
        $this->assertSame(0, $mati->passwordReset(Fixtures::uuid(), Fixtures::uuid())['status']);
    }
}
