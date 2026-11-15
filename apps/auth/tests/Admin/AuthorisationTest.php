<?php

declare(strict_types=1);

namespace Tests\Admin;

use App\Libraries\Brankas;
use Tests\Support\AuthCase;

/**
 * Setiap rute pengelolaan hanya untuk superadmin (SPEC.md §3), diperiksa di server.
 */
final class AuthorisationTest extends AuthCase
{
    /**
     * @return list<array{0: string, 1: string}>
     */
    private function rute(string $idPengguna): array
    {
        return [
            ['GET', '/admin/pengguna'],
            ['GET', '/admin/pengguna/ekspor?format=csv'],
            ['GET', '/admin/pengguna/baru'],
            ['POST', '/admin/pengguna'],
            ['POST', '/admin/pengguna/massal'],
            ['GET', '/admin/pengguna/' . $idPengguna],
            ['POST', '/admin/pengguna/' . $idPengguna],
            ['POST', '/admin/pengguna/' . $idPengguna . '/reset-sandi'],
            ['POST', '/admin/pengguna/' . $idPengguna . '/hapus-mfa'],
            ['GET', '/admin/impor'],
            ['POST', '/admin/impor'],
            ['POST', '/admin/impor/jalankan'],
            ['GET', '/admin/kredensial/' . str_repeat('a', 48)],
            ['GET', '/admin/audit'],
        ];
    }

    public function testTanpaSesiSemuaRuteDialihkanKeLogin(): void
    {
        $sasaran = $this->user();
        foreach ($this->rute($sasaran['id']) as [$metode, $jalur]) {
            if ($metode === 'POST') {
                continue; // POST tanpa sesi berhenti di CSRF; diuji terpisah di bawah.
            }
            $r = $this->http()->get($jalur);
            $this->assertContains($r->status, [302, 303], $jalur);
            $this->assertStringStartsWith('https://auth.lokandita.com/login?next=', (string) $r->header('Location'), $jalur);
        }
        $this->assertSame(403, $this->http()->post('/admin/pengguna/' . $sasaran['id'] . '/reset-sandi')->status);
    }

    public function testDosenDanMahasiswaDitolak403DiSetiapRute(): void
    {
        $sasaran = $this->user(['global_role' => 'mahasiswa']);
        $sandi   = $sasaran['password_hash'];

        foreach (['dosen', 'mahasiswa'] as $peran) {
            $pelaku = $this->user(['global_role' => $peran]);
            $http   = $this->sebagai($pelaku);
            $csrf   = $this->csrf($http);
            $this->assertNotSame('', $csrf);

            foreach ($this->rute($sasaran['id']) as [$metode, $jalur]) {
                $r = $metode === 'GET' ? $http->get($jalur) : $http->post($jalur, [
                    'csrf' => $csrf, 'nomor_induk' => 'selundup' . bin2hex(random_bytes(3)), 'nama' => 'Selundupan', 'global_role' => 'superadmin',
                    'status' => 'disabled', 'aksi' => 'status:disabled', 'ids' => [$sasaran['id']], 'yakin' => '1',
                ]);
                $this->assertSame(403, $r->status, "{$peran} {$metode} {$jalur}");
                $this->assertStringNotContainsString($sasaran['nomor_induk'], $r->body, "{$peran} {$metode} {$jalur} membocorkan data");
            }
            $this->assertSame('/admin/audit', $this->lastAudit('access.denied')['detail']['path']);
            $this->assertSame($pelaku['id'], $this->lastAudit('access.denied')['actor_id']);
        }

        // Tidak ada yang berubah.
        $baris = $this->userRow($sasaran['id']);
        $this->assertSame('active', $baris['status']);
        $this->assertSame($sandi, $baris['password_hash']);
        $this->assertSame(0, (int) $this->row("SELECT COUNT(*) n FROM users WHERE nama = 'Selundupan'")['n']);
    }

    public function testSuperadminBisaMembukaSemuaHalaman(): void
    {
        [$http]  = $this->admin();
        $sasaran = $this->user();
        foreach (['/admin/pengguna', '/admin/pengguna/baru', '/admin/pengguna/' . $sasaran['id'], '/admin/impor', '/admin/audit'] as $jalur) {
            $this->assertSame(200, $http->get($jalur)->status, $jalur);
        }
        $this->assertSame(404, $http->get('/admin/pengguna/' . \Lokandita\Auth\Testing\Fixtures::uuid())->status);
        $this->assertSame(404, $http->get('/admin/pengguna/bukan-uuid')->status);
    }

    public function testSuperadminYangWajibGantiSandiBelumBisaMengelola(): void
    {
        $user = $this->user(['global_role' => 'superadmin', 'mfa_enabled' => 1, 'must_change_password' => 1]);
        $r    = $this->sebagai($user)->get('/admin/pengguna');
        $this->assertStringStartsWith('https://auth.lokandita.com/ganti-password', (string) $r->header('Location'));
    }

    public function testDaftarKataSandiHanyaUntukSuperadminPembuatnyaDanHanyaSekali(): void
    {
        [$a, $adminA] = $this->admin();
        [$b]          = $this->admin();
        // Disimpan oleh kode aplikasi yang sama, di Redis DB 15 milik tes.
        $token = Brankas::simpan('kredensial', $adminA['id'], [['nomor_induk' => 'x1', 'nama' => 'Uji', 'password' => 'Rahasia-Sekali-1']]);

        $r = $b->get('/admin/kredensial/' . $token);
        $this->assertSame(410, $r->status, 'superadmin lain tidak bisa mengunduh');
        $this->assertStringNotContainsString('Rahasia-Sekali-1', $r->body);

        $r = $a->get('/admin/kredensial/' . $token . '?format=csv');
        $this->assertSame(200, $r->status, 'pemiliknya masih bisa, walau orang lain sudah mencoba');
        $this->assertStringContainsString('Rahasia-Sekali-1', $r->body);
        $this->assertStringContainsString('attachment', (string) $r->header('Content-Disposition'));
        $this->assertStringContainsString('no-store', (string) $r->header('Cache-Control'));

        $this->assertSame(410, $a->get('/admin/kredensial/' . $token)->status, 'hanya sekali');
        $this->assertNotNull($this->lastAudit('credentials.downloaded'));
        $this->assertStringNotContainsString('Rahasia-Sekali-1', json_encode($this->lastAudit('credentials.downloaded')));
    }

    public function testTidakAdaRuteUntukMengubahAtauMenghapusAudit(): void
    {
        [$http] = $this->admin();
        $csrf   = $this->csrf($http);
        $jumlah = (int) $this->row('SELECT COUNT(*) n FROM audit_log')['n'];
        foreach (['POST', 'DELETE', 'PUT'] as $metode) {
            $r = $http->send($metode, '/admin/audit', http_build_query(['csrf' => $csrf]), ['Content-Type' => 'application/x-www-form-urlencoded']);
            $this->assertContains($r->status, [403, 404, 405], $metode);
            $r = $http->send($metode, '/admin/audit/1', http_build_query(['csrf' => $csrf]), ['Content-Type' => 'application/x-www-form-urlencoded']);
            $this->assertContains($r->status, [403, 404, 405], $metode);
        }
        $this->assertGreaterThanOrEqual($jumlah, (int) $this->row('SELECT COUNT(*) n FROM audit_log')['n']);
    }
}
