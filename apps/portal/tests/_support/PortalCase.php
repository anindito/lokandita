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
        // …dan dari foto bawaan: tidak ada foto unggahan yang tayang (KONTRAK.md §16).
        $this->db->query('DELETE FROM foto_tayang');
        $this->db->query('DELETE FROM foto');
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

    /**
     * Foto JPEG buatan tes: gradasi warna, supaya bukan bidang kosong.
     */
    protected function jpeg(int $lebar = 1800, int $tinggi = 1200, int $merah = 40): string
    {
        $g = imagecreatetruecolor($lebar, $tinggi);
        for ($i = 0; $i < 16; $i++) {
            imagefilledrectangle($g, intdiv($lebar * $i, 16), 0, intdiv($lebar * ($i + 1), 16), $tinggi, (int) imagecolorallocate($g, $merah, 60 + $i * 10, 200 - $i * 8));
        }
        ob_start();
        imagejpeg($g, null, 85);

        return (string) ob_get_clean();
    }

    /**
     * Isian yang wajib menyertai unggahan foto.
     *
     * @return array<string, string>
     */
    protected function isianFoto(string $csrf, string $sumber = 'Fotografer Uji / Arsip Uji'): array
    {
        return [
            'csrf'    => $csrf,
            'alt_id'  => 'Teks alternatif Indonesia untuk ' . $sumber,
            'alt_en'  => 'English alternative text for ' . $sumber,
            'alt_ar'  => 'نص بديل عربي ' . $sumber,
            'sumber'  => $sumber,
            'lisensi' => 'Lisensi Uji 1.0',
            'berhak'  => '1',
        ];
    }

    /**
     * Mengunggah foto sebagai superadmin $admin dan mengembalikan id-nya (dari alamat pratinjau).
     */
    protected function unggahFoto(Http $admin, string $isi, string $sumber = 'Fotografer Uji / Arsip Uji', string $nama = 'foto uji.jpg'): int
    {
        $r = $admin->upload('/admin/teks/foto', $this->isianFoto($this->csrf($admin, '/admin/teks/foto'), $sumber), 'foto', $nama, $isi);
        $this->assertContains($r->status, [302, 303], 'unggah foto: ' . substr(strip_tags($r->body), 0, 400));
        $this->assertSame(1, preg_match('#/admin/teks/foto/(\d+)\?pesan=diunggah$#', (string) $r->header('Location'), $m), (string) $r->header('Location'));

        return (int) $m[1];
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
