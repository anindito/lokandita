<?php

declare(strict_types=1);

namespace Tests;

use Lokandita\Auth\Testing\Fixtures;
use Lokandita\Auth\Testing\Http;
use Tests\Support\PortalCase;

/**
 * Gerbang tahap 11 (SPEC.md §18): login dari Portal sampai ke LokaKode tanpa login kedua.
 *
 * Empat kontainer sungguhan: proses tes ini (berperan sebagai peramban), portal-tes,
 * auth-tes dan lokakode-tes. Satu-satunya yang dibawa dari satu aplikasi ke aplikasi
 * lain adalah cookie `lokandita_sid`, seperti yang dilakukan peramban untuk
 * `.lokandita.com`. Tidak ada yang di-mock.
 */
final class GerbangTest extends PortalCase
{
    private function lokakode(): Http
    {
        $url = (string) getenv('LOKAKODE_TES_URL');
        $this->assertNotSame('', $url, 'LOKAKODE_TES_URL kosong: infra/tes.sh portal harus melayani lokakode-tes');

        return new Http($url);
    }

    /**
     * @return iterable<string, array{0: string}>
     */
    public static function peran(): iterable
    {
        yield 'mahasiswa' => ['mahasiswa'];
        yield 'dosen' => ['dosen'];
    }

    /**
     * @dataProvider peran
     */
    public function testLoginDariPortalSampaiKeLokaKodeTanpaLoginKedua(string $peran): void
    {
        $user = $this->user(['nama' => 'Gerbang ' . ucfirst($peran), 'global_role' => $peran, 'apps' => ['portal', 'lokakode']]);

        // 1. Pengunjung membuka Portal dan menekan Masuk.
        $portal = $this->tamu();
        $depan  = $portal->get('/');
        $this->assertSame(200, $depan->status);
        $this->assertStringContainsString('<a class="tombol" href="/beranda" rel="nofollow">Masuk</a>', $depan->body);
        $keLogin = (string) $portal->get('/beranda')->header('Location');
        $next    = 'https://lokandita.com/beranda';
        $this->assertSame('https://auth.lokandita.com/login?next=' . rawurlencode($next), $keLogin);

        // 2. Login di LokaAuth (form sungguhan) dengan next itu; LokaAuth mengembalikannya ke Portal.
        $auth = Fixtures::login(new Http((string) getenv('AUTH_TES_URL')), $user, $next);
        $this->assertArrayHasKey('lokandita_sid', $auth->cookies, 'login di LokaAuth gagal');
        $this->assertSame($next, $auth->header('Location'), 'sesudah login kembali ke beranda Portal');
        $sid = $auth->cookies['lokandita_sid'];

        // 3. Portal mengenali sesi itu: nama, peran, dan kartu LokaKode.
        $portal->cookies['lokandita_sid'] = $sid;
        $beranda = $portal->get('/beranda');
        $this->assertSame(200, $beranda->status);
        $this->assertStringContainsString('Gerbang ' . ucfirst($peran), $beranda->body);
        $this->assertSame(1, preg_match('/<a class="kartu-aplikasi" href="([^"]+)" data-aplikasi="lokakode">/', $beranda->body, $m), 'kartu LokaKode');
        $this->assertSame('https://lokakode.lokandita.com/beranda', $m[1]);
        // Klien tes memakai satu objek untuk setiap jawaban, jadi token diambil sebelum permintaan berikutnya.
        $csrf = (string) $portal->input('csrf');
        $this->assertNotSame('', $csrf);
        // Halaman depan Portal kini membawa pemegang cookie langsung ke beranda.
        $this->assertSame($next, $portal->get('/')->header('Location'));

        // 4. Kartu itu diklik: LokaKode menerima cookie yang SAMA dan langsung menampilkan beranda —
        //    tidak ada pengalihan ke login.
        $kode = $this->lokakode();
        $kode->cookies['lokandita_sid'] = $sid;
        $r = $kode->get('/beranda');
        $this->assertSame(200, $r->status, 'LokaKode meminta login lagi: ' . $r->header('Location'));
        $this->assertNull($r->header('Location'));
        $this->assertStringContainsString('Gerbang ' . ucfirst($peran), $r->body);
        $this->assertStringContainsString($user['nomor_induk'], $r->body);
        // Halaman depan LokaKode pun membawanya ke beranda.
        $this->assertSame('https://lokakode.lokandita.com/beranda', $kode->get('/')->header('Location'));

        // "Tanpa login kedua", dibuktikan di basis data: satu sesi, satu login yang berhasil.
        $id = $user['id'];
        $this->assertSame('1', $this->idn->query("SELECT COUNT(*) FROM sessions WHERE user_id = '{$id}'")->fetch_row()[0]);
        $this->assertSame('1', $this->idn->query("SELECT COUNT(*) FROM login_attempts WHERE nomor_induk = '{$user['nomor_induk']}' AND success = 1")->fetch_row()[0]);
        $this->assertSame('1', $this->idn->query("SELECT COUNT(*) FROM audit_log WHERE actor_id = '{$id}' AND action = 'login.success'")->fetch_row()[0]);

        // 5. Keluar ditekan di Portal: sesi dicabut di LokaAuth, dan LokaKode pun menolaknya.
        $keluar = $portal->post('/keluar', ['csrf' => $csrf]);
        $this->assertSame('https://auth.lokandita.com/keluar-selesai', $keluar->header('Location'));
        $this->assertNotNull($this->idn->query("SELECT revoked_at FROM sessions WHERE user_id = '{$id}'")->fetch_row()[0]);
        $this->assertSame('https://auth.lokandita.com/login?next=' . rawurlencode('https://lokakode.lokandita.com/beranda'), $kode->get('/beranda')->header('Location'));
        $this->assertSame('https://auth.lokandita.com/login?next=' . rawurlencode($next), $portal->get('/beranda')->header('Location'));
    }

    /**
     * Arah sebaliknya dan batasnya: tanpa akses `lokakode`, Portal tidak menawarkan LokaKode
     * dan LokaKode sendiri menolak sesi itu dengan 403 — hak tidak hanya disembunyikan di tampilan.
     */
    public function testTanpaAksesLokaKodeTidakDitawarkanDanDitolakDiLokaKode(): void
    {
        $user   = $this->user(['nama' => 'Hanya Portal', 'apps' => ['portal']]);
        $portal = $this->sebagai($user);
        $r      = $portal->get('/beranda');
        $this->assertSame(200, $r->status);
        $this->assertStringNotContainsString('lokakode.lokandita.com', $r->body);

        $kode = $this->lokakode();
        $kode->cookies['lokandita_sid'] = $portal->cookies['lokandita_sid'];
        $r = $kode->get('/beranda');
        $this->assertSame(403, $r->status);
        $this->assertStringContainsString('href="https://lokandita.com"', $r->body, 'tautan kembali ke Portal');
    }

    /**
     * Login tanpa `next` (mis. dari markah auth.lokandita.com/login) mendarat di Portal (SPEC.md §4.6).
     */
    public function testLoginTanpaNextMendaratDiPortal(): void
    {
        $user = $this->user(['global_role' => 'dosen']);
        $auth = Fixtures::login(new Http((string) getenv('AUTH_TES_URL')), $user);
        $this->assertSame('https://lokandita.com/', $auth->header('Location'));

        $portal = $this->tamu();
        $portal->cookies['lokandita_sid'] = $auth->cookies['lokandita_sid'];
        $this->assertSame('https://lokandita.com/beranda', $portal->get('/')->header('Location'));
        $this->assertSame(200, $portal->get('/beranda')->status);
    }
}
