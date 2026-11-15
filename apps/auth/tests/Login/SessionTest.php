<?php

declare(strict_types=1);

namespace Tests\Login;

use Tests\Support\AuthCase;

/**
 * Sesi, keluar, dan ganti kata sandi (SPEC.md §4.2, §4.4).
 */
final class SessionTest extends AuthCase
{
    private const LOGIN_NEXT_AKAR = '/login?next=https%3A%2F%2Fauth.lokandita.com%2F';

    public function testHalamanBersesiTanpaCookieDialihkanKeLogin(): void
    {
        $http = $this->http()->get('/');
        $this->assertRedirect($http, self::LOGIN_NEXT_AKAR);

        $http = $this->http();
        $http->cookies['lokandita_sid'] = str_repeat('A', 43);
        $this->assertRedirect($http->get('/'), self::LOGIN_NEXT_AKAR, 'cookie karangan');
    }

    public function testHostDariKlienTidakDipakaiUntukNext(): void
    {
        $http = $this->http()->get('/', ['Host' => 'evil.example']);
        $this->assertRedirect($http, self::LOGIN_NEXT_AKAR);
    }

    public function testKeluarMencabutSesiDanCookieLamaDitolak(): void
    {
        $user = $this->user(['global_role' => 'dosen']);
        $http = $this->login($user);
        $lama = $http->cookies['lokandita_sid'];

        $this->assertSame(200, $http->get('/')->status);
        $this->assertStringContainsString(htmlspecialchars($user['nomor_induk']), $http->body);
        $this->assertStringContainsString('no-store', (string) $http->header('Cache-Control'));

        $keluar = $http->post('/keluar', ['csrf' => $http->input('csrf')]);
        $this->assertRedirect($keluar, '/login?keluar=1');
        $this->assertArrayNotHasKey('lokandita_sid', $http->cookies, 'cookie dihapus di peramban');
        $this->assertMatchesRegularExpression('/domain=\.lokandita\.com/i', (string) $keluar->setCookieLine('lokandita_sid'));

        $sesi = $this->row("SELECT revoked_at FROM sessions WHERE token_hash = '" . hash('sha256', $lama) . "'");
        $this->assertNotNull($sesi['revoked_at'], 'sesi dicabut di server');

        // Seseorang yang menyalin cookie lama tidak bisa memakainya lagi.
        $pencuri = $this->http();
        $pencuri->cookies['lokandita_sid'] = $lama;
        $this->assertRedirect($pencuri->get('/'), self::LOGIN_NEXT_AKAR);
        $this->assertNotNull($this->lastAudit('logout', $user['id']));
    }

    public function testKeluarTanpaCsrfDitolak(): void
    {
        $http = $this->login($this->user());
        $this->assertSame(403, $http->post('/keluar')->status);
        $this->assertSame(200, $http->get('/')->status, 'sesi masih hidup');
    }

    public function testKeluarSelesaiHanyaMenghapusCookieSesiYangSudahMati(): void
    {
        $http = $this->login($this->user());
        $this->assertRedirect($http->get('/keluar-selesai'), '/');
        $this->assertArrayHasKey('lokandita_sid', $http->cookies, 'sesi hidup tidak bisa dikeluarkan lewat tautan');

        $this->db->query("UPDATE sessions SET revoked_at = UTC_TIMESTAMP() WHERE token_hash = '" . hash('sha256', $http->cookies['lokandita_sid']) . "'");
        $this->assertRedirect($http->get('/keluar-selesai'), '/login?keluar=1');
        $this->assertArrayNotHasKey('lokandita_sid', $http->cookies);
    }

    public function testSesiBergeserDanCookieDisegarkan(): void
    {
        $http = $this->login($this->user(['global_role' => 'dosen']));
        $hash = hash('sha256', $http->cookies['lokandita_sid']);
        $this->db->query("UPDATE sessions SET last_seen_at = UTC_TIMESTAMP() - INTERVAL 5 MINUTE, expires_at = UTC_TIMESTAMP() + INTERVAL 1 HOUR WHERE token_hash = '{$hash}'");

        $http->get('/');
        $this->assertSame(200, $http->status);
        $sesi = $this->row("SELECT expires_at, last_seen_at FROM sessions WHERE token_hash = '{$hash}'");
        $this->assertEqualsWithDelta(43200, strtotime($sesi['expires_at'] . ' UTC') - time(), 5);
        $this->assertEqualsWithDelta(0, strtotime($sesi['last_seen_at'] . ' UTC') - time(), 5);
        $this->assertMatchesRegularExpression('/max-age=43200/i', (string) $http->setCookieLine('lokandita_sid'));
    }

    public function testSesiKedaluwarsaDitolak(): void
    {
        $http = $this->login($this->user());
        $this->db->query("UPDATE sessions SET expires_at = UTC_TIMESTAMP() - INTERVAL 1 SECOND WHERE token_hash = '" . hash('sha256', $http->cookies['lokandita_sid']) . "'");
        $this->assertRedirect($http->get('/'), self::LOGIN_NEXT_AKAR);
    }

    public function testMahasiswaMenganggurTigaPuluhMenitDitolakDosenTidak(): void
    {
        $mhs   = $this->login($this->user(['global_role' => 'mahasiswa']));
        $dosen = $this->login($this->user(['global_role' => 'dosen']));
        foreach ([$mhs, $dosen] as $h) {
            $this->db->query("UPDATE sessions SET last_seen_at = UTC_TIMESTAMP() - INTERVAL 31 MINUTE WHERE token_hash = '" . hash('sha256', $h->cookies['lokandita_sid']) . "'");
        }
        $this->assertRedirect($mhs->get('/'), self::LOGIN_NEXT_AKAR);
        $this->assertSame(200, $dosen->get('/')->status);
    }

    public function testAkunDinonaktifkanKehilanganSesinya(): void
    {
        $user = $this->user();
        $http = $this->login($user);
        $this->db->query("UPDATE users SET status = 'suspended' WHERE id = '{$user['id']}'");
        $this->assertRedirect($http->get('/'), self::LOGIN_NEXT_AKAR);
    }

    public function testWajibGantiKataSandiSebelumHalLain(): void
    {
        $user = $this->user(['must_change_password' => 1, 'global_role' => 'dosen']);
        $next = 'https://lokakode.lokandita.com/kelas';
        $http = $this->login($user, $next);
        $this->assertRedirect($http, '/ganti-password?next=' . rawurlencode($next));
        $this->assertRedirect($http->get('/'), '/ganti-password?next=https%3A%2F%2Fauth.lokandita.com%2F', 'halaman lain dialihkan');

        $form = $http->get('/ganti-password?next=' . rawurlencode($next));
        $this->assertSame(200, $form->status);
        $this->assertSame($next, $form->input('next'));

        $kirim = function (string $lama, string $baru, ?string $ulang = null) use ($http, $next) {
            $http->get('/ganti-password');

            return $http->post('/ganti-password', [
                'csrf' => $http->input('csrf'), 'next' => $next,
                'password_lama' => $lama, 'password_baru' => $baru, 'password_ulang' => $ulang ?? $baru,
            ]);
        };

        $this->assertSame(422, $kirim('bukan-yang-lama', 'Kalimat-Baru-7788')->status, 'kata sandi lama salah');
        $this->assertSame(422, $kirim($user['password'], 'pendek-9')->status, 'kurang dari 10 karakter');
        $this->assertSame(422, $kirim($user['password'], 'password123')->status, 'kata sandi umum');
        $this->assertSame(422, $kirim($user['password'], 'Mahasiswa123')->status, 'kata sandi umum, huruf besar diabaikan');
        $this->assertSame(422, $kirim($user['password'], '1234567890')->status, 'deret angka');
        $this->assertSame(422, $kirim($user['password'], $user['nomor_induk'])->status, 'sama dengan nomor induk');
        $this->assertSame(422, $kirim($user['password'], $user['password'])->status, 'sama dengan kata sandi sekarang');
        $this->assertSame(422, $kirim($user['password'], 'Kalimat-Baru-7788', 'Kalimat-Baru-7789')->status, 'ulangan tidak sama');
        $this->assertSame('1', (string) $this->userRow($user['id'])['must_change_password']);

        $this->assertRedirect($kirim($user['password'], 'Kalimat-Baru-7788'), $next);
        $this->assertSame('0', (string) $this->userRow($user['id'])['must_change_password']);
        $this->assertSame(200, $http->get('/')->status, 'sesi ini tetap hidup');
        $this->assertNotNull($this->lastAudit('password.changed', $user['id']));

        $this->assertSame(422, $this->login($user)->status, 'kata sandi lama tidak berlaku lagi');
        $this->assertRedirect($this->login($user, password: 'Kalimat-Baru-7788'), 'https://lokandita.com/');
    }

    public function testTigaKataSandiTerakhirDitolak(): void
    {
        $user = $this->user(['global_role' => 'dosen', 'password' => 'Sandi-Pertama-01']);
        $http = $this->login($user);
        $ganti = function (string $lama, string $baru) use ($http) {
            $http->get('/ganti-password');

            return $http->post('/ganti-password', ['csrf' => $http->input('csrf'), 'password_lama' => $lama, 'password_baru' => $baru, 'password_ulang' => $baru])->status;
        };
        $this->assertNotSame(422, $ganti('Sandi-Pertama-01', 'Sandi-Kedua-02'));
        $this->assertNotSame(422, $ganti('Sandi-Kedua-02', 'Sandi-Ketiga-03'));
        $this->assertSame(422, $ganti('Sandi-Ketiga-03', 'Sandi-Pertama-01'), 'masih termasuk tiga terakhir');
        $this->assertSame(422, $ganti('Sandi-Ketiga-03', 'Sandi-Kedua-02'));
        $this->assertNotSame(422, $ganti('Sandi-Ketiga-03', 'Sandi-Keempat-04'));
        $this->assertNotSame(422, $ganti('Sandi-Keempat-04', 'Sandi-Pertama-01'), 'yang pertama sudah keluar dari tiga terakhir');
    }

    public function testGantiKataSandiMencabutSesiLain(): void
    {
        $user   = $this->user(['global_role' => 'dosen']);
        $laptop = $this->login($user);
        $ponsel = $this->login($user);
        $this->assertSame(200, $ponsel->get('/')->status);

        $laptop->get('/ganti-password');
        $laptop->post('/ganti-password', [
            'csrf' => $laptop->input('csrf'), 'password_lama' => $user['password'],
            'password_baru' => 'Kalimat-Baru-4455', 'password_ulang' => 'Kalimat-Baru-4455',
        ]);

        $this->assertSame(200, $laptop->get('/')->status, 'sesi yang mengganti tetap hidup');
        $this->assertRedirect($ponsel->get('/'), self::LOGIN_NEXT_AKAR, 'sesi lain dicabut');
        $this->assertSame(1, $this->lastAudit('password.changed', $user['id'])['detail']['other_sessions_revoked']);
    }

    public function testSudahMasukTidakMelihatFormLoginLagi(): void
    {
        $http = $this->login($this->user(['global_role' => 'dosen']));
        $this->assertRedirect($http->get('/login'), 'https://lokandita.com/');
        $this->assertRedirect($http->get('/login?next=' . rawurlencode('https://auth.lokandita.com/')), 'https://auth.lokandita.com/');
    }
}
