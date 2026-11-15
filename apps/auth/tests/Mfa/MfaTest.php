<?php

declare(strict_types=1);

namespace Tests\Mfa;

use App\Libraries\SystemClock;
use Lokandita\Auth\Testing\Http;
use OTPHP\TOTP;
use Tests\Support\AuthCase;

/**
 * SPEC.md §4.3: TOTP wajib untuk superadmin, kode cadangan sekali pakai, rahasia terenkripsi.
 */
final class MfaTest extends AuthCase
{
    private function kode(string $secret): string
    {
        return TOTP::createFromSecret($secret, new SystemClock())->now();
    }

    /**
     * Kode yang sama tidak bisa dipakai dua kali; tes yang perlu login berulang menghapus catatan itu.
     */
    private function lupakanKodeTerpakai(): void
    {
        $u = parse_url((string) getenv('REDIS_URL'));
        $r = new \Redis();
        $r->connect($u['host'], (int) $u['port']);
        $r->auth($u['pass']);
        $r->select((int) ltrim($u['path'], '/'));
        $this->assertSame(15, (int) ltrim($u['path'], '/'), 'tes hanya menyentuh Redis DB 15');
        foreach ($r->keys('auth:mfa:terpakai:*') as $k) {
            $r->del($k);
        }
    }

    /**
     * Mendaftarkan TOTP untuk superadmin baru. Mengembalikan [klien, rahasia, kode cadangan].
     *
     * @param array<string, mixed> $user
     *
     * @return array{0: Http, 1: string, 2: list<string>}
     */
    private function daftar(array $user): array
    {
        $http = $this->login($user);
        $this->assertRedirect($http, '/login/mfa-daftar');
        $this->assertArrayNotHasKey('lokandita_sid', $http->cookies, 'belum ada sesi sebelum MFA selesai');

        $form = $http->get('/login/mfa-daftar');
        $this->assertSame(200, $form->status);
        $this->assertStringContainsString('<svg', $form->body);
        $this->assertSame(1, preg_match('/data-rahasia>([A-Z2-7 ]+)</', $form->body, $m));
        $secret = str_replace(' ', '', $m[1]);

        $hasil = $http->post('/login/mfa-daftar', ['csrf' => $form->input('csrf'), 'kode' => $this->kode($secret)]);
        $this->assertSame(200, $hasil->status);
        preg_match_all('/data-kode-cadangan>([a-z0-9-]+)</', $hasil->body, $k);

        return [$http, $secret, $k[1]];
    }

    public function testSuperadminMendaftarTotpPadaLoginPertama(): void
    {
        $user                   = $this->user(['global_role' => 'superadmin']);
        [$http, $secret, $kode] = $this->daftar($user);

        $this->assertCount(8, $kode);
        $this->assertCount(8, array_unique($kode));
        $this->assertArrayHasKey('lokandita_sid', $http->cookies, 'sesi dibuat setelah MFA aktif');
        $this->assertSame(200, $http->get('/')->status);

        $baris = $this->userRow($user['id']);
        $this->assertSame('1', (string) $baris['mfa_enabled']);
        $this->assertStringNotContainsString($secret, (string) $baris['mfa_secret_enc'], 'rahasia tidak tersimpan apa adanya');
        $this->assertGreaterThan(40, strlen((string) $baris['mfa_secret_enc']));

        $hash = array_column($this->db->query("SELECT code_hash FROM mfa_backup_codes WHERE user_id = '{$user['id']}'")->fetch_all(MYSQLI_ASSOC), 'code_hash');
        $this->assertCount(8, $hash);
        foreach ($kode as $k) {
            $this->assertNotContains($k, $hash, 'kode cadangan tidak tersimpan apa adanya');
            $this->assertContains(hash('sha256', str_replace('-', '', $k)), $hash);
        }
        $this->assertNotNull($this->lastAudit('mfa.enrolled', $user['id']));
        $this->assertSame('totp_enroll', $this->lastAudit('login.success', $user['id'])['detail']['method']);
    }

    public function testPendaftaranDenganKodeSalahTidakMengaktifkanApaPun(): void
    {
        $user = $this->user(['global_role' => 'superadmin']);
        $http = $this->login($user);
        $form = $http->get('/login/mfa-daftar');
        $http->post('/login/mfa-daftar', ['csrf' => $form->input('csrf'), 'kode' => '000000']);
        $this->assertSame(422, $http->status);
        $this->assertArrayNotHasKey('lokandita_sid', $http->cookies);
        $this->assertSame('0', (string) $this->userRow($user['id'])['mfa_enabled']);
    }

    public function testLoginBerikutnyaMemintaKodeDanKodeTidakBisaDipakaiUlang(): void
    {
        $user            = $this->user(['global_role' => 'superadmin']);
        [, $secret]      = $this->daftar($user);
        $kodeSaatDaftar  = $this->kode($secret);

        $http = $this->login($user);
        $this->assertRedirect($http, '/login/kode');
        $this->assertArrayNotHasKey('lokandita_sid', $http->cookies);
        $this->assertRedirect($http->get('/'), '/login?next=https%3A%2F%2Fauth.lokandita.com%2F', 'kata sandi saja belum cukup');

        $form = $http->get('/login/kode');
        $this->assertSame(200, $form->status);

        // Kode yang baru saja dipakai untuk mendaftar masih berlaku menurut jam, tetapi sudah terpakai.
        $ulang = $http->post('/login/kode', ['csrf' => $form->input('csrf'), 'kode' => $kodeSaatDaftar]);
        $this->assertSame(422, $ulang->status, 'kode yang sama ditolak untuk kedua kalinya');
        $this->assertArrayNotHasKey('lokandita_sid', $http->cookies);

        $this->lupakanKodeTerpakai();
        $this->db->query("UPDATE users SET failed_attempts = 0 WHERE id = '{$user['id']}'");
        $form = $http->get('/login/kode');
        $ok   = $http->post('/login/kode', ['csrf' => $form->input('csrf'), 'kode' => $this->kode($secret)]);
        $this->assertRedirect($ok, 'https://lokandita.com/');
        $this->assertArrayHasKey('lokandita_sid', $http->cookies);
        $this->assertSame('totp', $this->lastAudit('login.success', $user['id'])['detail']['method']);
    }

    public function testKodeSalahMemberiTeksYangSamaDenganKataSandiSalahDanMengunci(): void
    {
        $user       = $this->user(['global_role' => 'superadmin']);
        [, $secret] = $this->daftar($user);

        $teksSandiSalah = $this->toast($this->login($user, password: 'bukan-kata-sandinya'));
        $this->db->query("UPDATE users SET failed_attempts = 0 WHERE id = '{$user['id']}'");

        $http = $this->login($user);
        for ($i = 1; $i <= 5; $i++) {
            $form  = $http->get('/login/kode');
            $salah = $http->post('/login/kode', ['csrf' => $form->input('csrf'), 'kode' => '123123']);
            $this->assertSame(422, $salah->status);
            $this->assertSame($teksSandiSalah, $this->toast($salah), 'teks sama untuk kode salah dan kata sandi salah');
        }
        $this->assertSame('wrong_totp', $this->lastAudit('login.failed', $user['id'])['detail']['reason']);
        $this->assertNotNull($this->userRow($user['id'])['locked_until'], '5 kode salah mengunci akun');

        // Setelah terkunci, langkah kode tidak bisa dilanjutkan walau kodenya benar.
        $this->lupakanKodeTerpakai();
        $this->assertRedirect($http->get('/login/kode'), '/login');
        $http->post('/login/kode', ['csrf' => (string) $http->get('/login')->input('csrf'), 'kode' => $this->kode($secret)]);
        $this->assertArrayNotHasKey('lokandita_sid', $http->cookies);
    }

    public function testKodeCadanganHanyaSekaliPakai(): void
    {
        $user        = $this->user(['global_role' => 'superadmin']);
        [, , $kode]  = $this->daftar($user);

        $http = $this->login($user);
        $form = $http->get('/login/kode');
        $this->assertRedirect($http->post('/login/kode', ['csrf' => $form->input('csrf'), 'kode' => strtoupper($kode[0])]), 'https://lokandita.com/');
        $this->assertSame('backup_code', $this->lastAudit('login.success', $user['id'])['detail']['method']);
        $this->assertNotNull($this->lastAudit('mfa.backup_code_used', $user['id']));

        $lagi = $this->login($user);
        $form = $lagi->get('/login/kode');
        $this->assertSame(422, $lagi->post('/login/kode', ['csrf' => $form->input('csrf'), 'kode' => $kode[0]])->status, 'kode cadangan yang sama ditolak');
        $form = $lagi->get('/login/kode');
        $this->assertRedirect($lagi->post('/login/kode', ['csrf' => $form->input('csrf'), 'kode' => $kode[1]]), 'https://lokandita.com/');
    }

    public function testLangkahKeduaTidakBisaDibukaTanpaLangkahPertama(): void
    {
        $http = $this->http();
        $this->assertRedirect($http->get('/login/kode'), '/login');
        $this->assertRedirect($http->get('/login/mfa-daftar'), '/login');
        $http->cookies['__Host-tunda'] = str_repeat('B', 43);
        $this->assertRedirect($http->get('/login/kode'), '/login');

        $csrf = (string) $http->get('/login')->input('csrf');
        $this->assertRedirect($http->post('/login/kode', ['csrf' => $csrf, 'kode' => '123456']), '/login');
        $this->assertArrayNotHasKey('lokandita_sid', $http->cookies);
    }

    public function testLoginTertundaTidakBisaDipakaiUntukTahapLain(): void
    {
        // Superadmin yang sudah punya MFA tidak bisa memakai halaman pendaftaran untuk mengganti rahasianya.
        $user = $this->user(['global_role' => 'superadmin']);
        $this->daftar($user);
        $http = $this->login($user);
        $this->assertRedirect($http->get('/login/mfa-daftar'), '/login');
    }

    public function testDosenDanMahasiswaTidakDimintaKode(): void
    {
        foreach (['dosen', 'mahasiswa'] as $peran) {
            $http = $this->login($this->user(['global_role' => $peran]));
            $this->assertRedirect($http, 'https://lokandita.com/', $peran);
            $this->assertArrayHasKey('lokandita_sid', $http->cookies);
        }
    }

    public function testSkemaMengizinkanMfaUntukPeranLainTanpaMigrasi(): void
    {
        // SPEC.md §4.3: MFA bisa dinyalakan per pengguna kelak. Dosen dengan mfa_enabled = 1 diminta kode.
        $user   = $this->user(['global_role' => 'dosen']);
        $secret = \App\Libraries\Mfa::newSecret();
        $stmt   = $this->db->prepare('UPDATE users SET mfa_enabled = 1, mfa_secret_enc = ? WHERE id = ?');
        $stmt->execute([\App\Libraries\Mfa::encrypt($secret), $user['id']]);

        $http = $this->login($user);
        $this->assertRedirect($http, '/login/kode');
        $form = $http->get('/login/kode');
        $this->assertRedirect($http->post('/login/kode', ['csrf' => $form->input('csrf'), 'kode' => $this->kode($secret)]), 'https://lokandita.com/');
    }

    public function testPerintahAkunSuperadmin(): void
    {
        $nomor  = 'adm' . bin2hex(random_bytes(4));
        $keluar = (string) shell_exec('php spark akun:superadmin --nomor ' . escapeshellarg($nomor) . ' --nama ' . escapeshellarg('Admin Uji') . ' 2>&1');
        $this->assertSame(1, preg_match('/Kata sandi sementara : (\S+)/', $keluar, $m), $keluar);

        $baris = $this->row("SELECT * FROM users WHERE nomor_induk = '{$nomor}'");
        $this->assertSame('superadmin', $baris['global_role']);
        $this->assertSame('1', (string) $baris['must_change_password']);
        $this->assertStringStartsWith('$argon2id$', $baris['password_hash']);
        $this->assertSame('cli', $this->lastAudit('user.created', $baris['id'])['detail']['via']);

        // Alur lengkap pemilik: kata sandi sementara -> daftar TOTP -> wajib ganti kata sandi.
        [$http] = $this->daftar(['nomor_induk' => $nomor, 'password' => $m[1]]);
        $this->assertRedirect($http->get('/'), '/ganti-password?next=https%3A%2F%2Fauth.lokandita.com%2F');

        $lagi = (string) shell_exec('php spark akun:superadmin --nomor ' . escapeshellarg($nomor) . ' --nama x 2>&1');
        $this->assertStringContainsString('sudah terdaftar', $lagi);
        $this->assertSame(1, (int) $this->row("SELECT COUNT(*) n FROM users WHERE nomor_induk = '{$nomor}'")['n']);
    }
}
