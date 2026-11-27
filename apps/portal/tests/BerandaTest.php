<?php

declare(strict_types=1);

namespace Tests;

use Tests\Support\PortalCase;

/**
 * Portal setelah masuk (SPEC.md §15): nama, peran, dan aplikasi yang diizinkan app_access.
 * Sesi, menu akun, Keluar dan wajib-ganti-kata-sandi diuji ClientAppTest di paket bersama.
 */
final class BerandaTest extends PortalCase
{
    public function testTanpaSesiDibawaKeLoginDenganNext(): void
    {
        foreach (['/beranda', '/data-saya'] as $jalur) {
            $r = $this->tamu()->get($jalur);
            $this->assertContains($r->status, [302, 303, 307], $jalur);
            $this->assertSame('https://auth.lokandita.com/login?next=' . rawurlencode('https://lokandita.com' . $jalur), $r->header('Location'), $jalur);
        }
    }

    public function testNamaPeranDanAplikasiYangDiizinkan(): void
    {
        $user = $this->user(['nama' => 'Siti Rahmawati', 'global_role' => 'mahasiswa', 'apps' => ['portal', 'lokakode', 'lokaujian']]);
        $r    = $this->sebagai($user)->get('/beranda');
        $this->assertSame(200, $r->status);
        $this->assertStringContainsString('<h1>Halo, Siti Rahmawati</h1>', $r->body);
        $this->assertStringContainsString('Mahasiswa', $r->body);
        $this->assertStringContainsString($user['nomor_induk'], $r->body);
        // Halaman bersesi: tidak diindeks, tidak disimpan peramban.
        $this->assertStringContainsString('<meta name="robots" content="noindex, nofollow">', $r->body);
        $this->assertStringContainsString('no-store', (string) $r->header('Cache-Control'));

        // LokaKode: diizinkan dan sudah dibangun -> tautan ke berandanya.
        $this->assertStringContainsString('<a class="kartu-aplikasi" href="https://lokakode.lokandita.com/beranda" data-aplikasi="lokakode">', $r->body);
        // LokaUjian: diizinkan dan, sejak tahap U6, dibuka -> tautan ke berandanya.
        $this->assertStringContainsString('<a class="kartu-aplikasi" href="https://lokaujian.lokandita.com/beranda" data-aplikasi="lokaujian">', $r->body);
        // LokaSiber: tidak diizinkan -> tidak ditampilkan sama sekali.
        $this->assertStringNotContainsString('LokaSiber', $r->body);
        $this->assertStringNotContainsString('data-aplikasi="auth"', $r->body, 'tautan admin hanya untuk superadmin');
    }

    public function testAksesYangDimatikanAtauTidakAdaTidakDitampilkan(): void
    {
        $user = $this->user(['nama' => 'Tanpa Aplikasi', 'apps' => ['portal']]);
        $this->idn->query("INSERT INTO app_access (user_id, app, enabled, granted_at) VALUES ('{$user['id']}', 'lokakode', 0, UTC_TIMESTAMP())");
        $r = $this->sebagai($user)->get('/beranda');
        $this->assertSame(200, $r->status);
        $this->assertStringNotContainsString('lokakode.lokandita.com', $r->body);
        $this->assertStringNotContainsString('data-aplikasi=', $r->body);
        $this->assertStringContainsString('belum diberi akses ke aplikasi mana pun', $r->body);
        $this->assertStringContainsString('mailto:' . getenv('CONTACT_EMAIL'), $r->body);

        // Hak dibaca pada setiap permintaan: begitu dinyalakan di LokaAuth, kartunya muncul.
        $this->idn->query("UPDATE app_access SET enabled = 1 WHERE user_id = '{$user['id']}' AND app = 'lokakode'");
        $this->assertStringContainsString('data-aplikasi="lokakode"', $this->sebagai($user)->get('/beranda')->body);
    }

    /**
     * Keputusan pemilik 2026-11-21 (KONTRAK.md §4 langkah 4): tanpa akses `portal` pun
     * beranda terbuka, dan tetap hanya menampilkan aplikasi yang diizinkan.
     */
    public function testTanpaAksesPortalTetapMelihatAplikasinyaSendiri(): void
    {
        $user = $this->user(['nama' => 'Dosen Tanpa Portal', 'global_role' => 'dosen', 'apps' => ['lokakode']]);
        $r    = $this->sebagai($user)->get('/beranda');
        $this->assertSame(200, $r->status);
        $this->assertStringContainsString('Dosen Tanpa Portal', $r->body);
        $this->assertStringContainsString('data-aplikasi="lokakode"', $r->body);
        $this->assertSame(1, preg_match_all('/data-aplikasi=/', $r->body));
    }

    public function testSuperadminMendapatTautanPengelolaanDiLokaAuth(): void
    {
        $admin = $this->user(['nama' => 'Admin Uji', 'global_role' => 'superadmin', 'mfa_enabled' => 1, 'apps' => []]);
        $r     = $this->sebagai($admin)->get('/beranda');
        $this->assertSame(200, $r->status);
        $this->assertStringContainsString('<a class="kartu-aplikasi" href="https://auth.lokandita.com/admin/pengguna" data-aplikasi="auth">', $r->body);
        $this->assertStringNotContainsString('belum diberi akses', $r->body);
    }

    public function testDataSayaMenautkanKeEksporTiapAplikasi(): void
    {
        $user = $this->user(['apps' => ['portal', 'lokakode']]);
        $r    = $this->sebagai($user)->get('/data-saya');
        $this->assertSame(200, $r->status);
        $this->assertStringContainsString('href="https://auth.lokandita.com/data-saya"', $r->body);
        $this->assertStringContainsString('href="https://lokakode.lokandita.com/data-saya"', $r->body);
        $this->assertStringNotContainsString('lokaujian.lokandita.com', $r->body, 'tanpa akses lokaujian tidak ada tautannya');
        $this->assertStringContainsString('<meta name="robots" content="noindex, nofollow">', $r->body);

        // Sejak LokaUjian tahap U6: pengguna dengan akses lokaujian mendapat tautan ekspornya juga.
        $ujian = $this->user(['apps' => ['portal', 'lokaujian']]);
        $r     = $this->sebagai($ujian)->get('/data-saya');
        $this->assertStringContainsString('href="https://lokaujian.lokandita.com/data-saya"', $r->body);
        $this->assertStringNotContainsString('lokakode.lokandita.com', $r->body);

        $tanpa = $this->user(['apps' => ['portal']]);
        $r     = $this->sebagai($tanpa)->get('/data-saya');
        $this->assertStringContainsString('href="https://auth.lokandita.com/data-saya"', $r->body);
        $this->assertStringNotContainsString('lokakode.lokandita.com', $r->body);
    }

    public function testBerandaDalamBahasaAkun(): void
    {
        $user = $this->user(['nama' => 'Layla Hasan', 'locale' => 'ar', 'apps' => ['lokakode', 'lokasiber']]);
        $r    = $this->sebagai($user)->get('/beranda');
        $this->assertStringContainsString('<html lang="ar" dir="rtl">', $r->body);
        $this->assertStringContainsString('تطبيقاتك', $r->body);
        $this->assertStringContainsString('قريبًا', $r->body);
    }
}
