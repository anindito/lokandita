<?php

declare(strict_types=1);

namespace Tests\Support;

use Lokandita\Auth\Testing\Fixtures;
use Lokandita\Auth\Testing\Http;
use Lokandita\Auth\Testing\TestSchema;
use PHPUnit\Framework\TestCase;

/**
 * Dasar tes Portal: HTTP sungguhan ke portal-tes (dan auth-tes, lokakode-tes),
 * SQL langsung ke identity_test untuk menyiapkan pengguna. Tidak ada mock.
 */
abstract class PortalCase extends TestCase
{
    protected \mysqli $idn; // identity_test

    protected \mysqli $db;  // portal_test

    protected function setUp(): void
    {
        $this->idn = TestSchema::connect((string) getenv('TES_IDENTITY_DSN'));
        $this->db  = TestSchema::connect((string) getenv('DB_DSN'));
        // Setiap tes mulai dari teks bawaan: tidak ada teks pengganti, tidak ada riwayat (KONTRAK.md §15).
        $this->db->query('DELETE FROM teks');
        $this->db->query('DELETE FROM audit_log');
    }

    /**
     * Superadmin baru yang sudah masuk. Mengembalikan [klien, baris pengguna].
     *
     * @return array{0: Http, 1: array<string, mixed>}
     */
    protected function admin(string $nama = 'Admin Uji'): array
    {
        $user = $this->user(['global_role' => 'superadmin', 'mfa_enabled' => 1, 'nama' => $nama, 'apps' => []]);

        return [$this->sebagai($user), $user];
    }

    /**
     * Isian form /admin/teks yang sah untuk satu halaman: teks bawaannya, dalam bentuk
     * yang dikirim peramban (daftar = satu butir per baris).
     *
     * @return array<string, mixed>
     */
    protected function isianBawaan(string $halaman, string $bahasa = 'id'): array
    {
        $bawaan = (require APPPATH . "Language/{$bahasa}/Portal.php")[$halaman];
        $t      = [];
        foreach ($bawaan as $kunci => $nilai) {
            if ($kunci === 'diperbarui') {
                continue;
            }
            if ($kunci === 'bagian') {
                foreach ($nilai as $i => $b) {
                    $t['bagian'][$i] = ['judul' => $b['judul'], 'isi' => implode("\n", $b['isi'])] + (isset($b['butir']) ? ['butir' => implode("\n", $b['butir'])] : []);
                }
            } else {
                $t[$kunci] = is_array($nilai) ? implode("\n", $nilai) : $nilai;
            }
        }

        return $t;
    }

    protected function csrf(Http $http, string $jalur = '/admin/teks/tentang/id'): string
    {
        return (string) $http->get($jalur)->input('csrf');
    }

    protected function tamu(): Http
    {
        return new Http((string) getenv('APP_TES_URL'));
    }

    /**
     * @param array<string, mixed> $over
     *
     * @return array<string, mixed>
     */
    protected function user(array $over = []): array
    {
        return Fixtures::user($this->idn, $over);
    }

    /**
     * Klien Portal yang membawa sesi $user (sesi dibuat langsung di identity_test).
     *
     * @param array<string, mixed> $user
     */
    protected function sebagai(array $user): Http
    {
        $http = $this->tamu();
        $http->cookies['lokandita_sid'] = Fixtures::session($this->idn, (string) $user['id']);

        return $http;
    }
}
