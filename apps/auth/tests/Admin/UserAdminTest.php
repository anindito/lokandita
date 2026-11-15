<?php

declare(strict_types=1);

namespace Tests\Admin;

use App\Libraries\Tabular;
use Lokandita\Auth\Testing\Http;
use Tests\Support\AuthCase;

/**
 * SPEC.md §4.1, §4.3, §4.7: pengelolaan pengguna oleh superadmin.
 */
final class UserAdminTest extends AuthCase
{
    private function sandiDiHalaman(Http $http): string
    {
        $this->assertSame(1, preg_match('/data-sandi-sementara>([^<]+)</', $http->body, $m), substr(strip_tags($http->body), 0, 400));

        return html_entity_decode($m[1], ENT_QUOTES);
    }

    public function testMembuatPenggunaDanIaBisaMasukDenganKataSandiSementara(): void
    {
        [$http, $admin] = $this->admin();
        $nomor = 'baru' . bin2hex(random_bytes(4));
        $r     = $http->post('/admin/pengguna', [
            'csrf' => $this->csrf($http, '/admin/pengguna/baru'), 'nomor_induk' => $nomor, 'nama' => 'Mahasiswa Baru',
            'email' => '', 'global_role' => 'mahasiswa', 'apps' => ['portal', 'lokakode'],
        ]);
        $this->assertSame(200, $r->status);
        $this->assertStringContainsString('no-store', (string) $r->header('Cache-Control'));
        $sandi = $this->sandiDiHalaman($r);

        $baris = $this->row("SELECT * FROM users WHERE nomor_induk = '{$nomor}'");
        $this->assertSame('mahasiswa', $baris['global_role']);
        $this->assertSame('1', (string) $baris['must_change_password']);
        $this->assertSame('1', (string) $baris['idle_logout'], 'keluar-otomatis wajib untuk mahasiswa');
        $this->assertNull($baris['email']);
        $this->assertSame($admin['id'], $baris['created_by']);
        $this->assertStringStartsWith('$argon2id$', $baris['password_hash']);
        $this->assertSame(2, (int) $this->row("SELECT COUNT(*) n FROM app_access WHERE user_id = '{$baris['id']}' AND enabled = 1")['n']);
        $catatan = $this->lastAudit('user.created', $baris['id']);
        $this->assertSame($admin['id'], $catatan['actor_id']);
        $this->assertStringNotContainsString($sandi, json_encode($catatan));

        $masuk = $this->login(['nomor_induk' => $nomor, 'password' => $sandi]);
        $this->assertRedirect($masuk, '/ganti-password');
    }

    public function testIsianSalahDitolakDenganPenjelasan(): void
    {
        [$http] = $this->admin();
        $ada    = $this->user(['email' => 'ada' . bin2hex(random_bytes(3)) . '@contoh.test']);
        $csrf   = $this->csrf($http, '/admin/pengguna/baru');
        $kirim  = fn (array $isi) => $http->post('/admin/pengguna', $isi + ['csrf' => $csrf, 'nomor_induk' => 'x' . bin2hex(random_bytes(4)), 'nama' => 'Uji', 'email' => '', 'global_role' => 'dosen']);

        $this->assertSame(422, $kirim(['nomor_induk' => $ada['nomor_induk']])->status);
        $this->assertStringContainsString('sudah terdaftar', $http->body);
        $this->assertSame(422, $kirim(['nomor_induk' => 'ab'])->status);
        $this->assertSame(422, $kirim(['nomor_induk' => 'ada spasi'])->status);
        $this->assertSame(422, $kirim(['nama' => ''])->status);
        $this->assertSame(422, $kirim(['email' => 'bukan-email'])->status);
        $this->assertSame(422, $kirim(['email' => $ada['email']])->status);
        $this->assertSame(422, $kirim(['global_role' => 'rektor'])->status);
        $this->assertSame(422, $kirim(['apps' => ['lokakode', 'aplikasi-gelap']])->status);
    }

    public function testMengubahPenggunaDanNomorIndukTetap(): void
    {
        [$http, $admin] = $this->admin();
        $user  = $this->user(['global_role' => 'dosen', 'apps' => ['portal']]);
        $sesi  = $this->sebagai($user);
        $form  = $http->get('/admin/pengguna/' . $user['id']);
        $this->assertSame(200, $form->status);
        // Http mengembalikan dirinya sendiri: token diambil sekarang, sebelum permintaan berikutnya menimpa badan halaman.
        $csrf = $form->input('csrf');

        $r = $http->post('/admin/pengguna/' . $user['id'], [
            'csrf' => $csrf, 'nomor_induk' => 'diganti-paksa', 'nama' => 'Nama Baru', 'email' => 'baru' . bin2hex(random_bytes(3)) . '@contoh.test',
            'global_role' => 'dosen', 'status' => 'active', 'apps' => ['lokakode'], 'paste_allowed' => '1',
        ]);
        $this->assertRedirect($r, '/admin/pengguna/' . $user['id'] . '?pesan=disimpan');
        $baris = $this->userRow($user['id']);
        $this->assertSame($user['nomor_induk'], $baris['nomor_induk'], 'nomor induk tidak bisa diubah');
        $this->assertSame('Nama Baru', $baris['nama']);
        $this->assertSame('1', (string) $baris['paste_allowed']);
        $this->assertSame(['lokakode'], array_column($this->db->query("SELECT app FROM app_access WHERE user_id = '{$user['id']}' AND enabled = 1")->fetch_all(MYSQLI_ASSOC), 'app'));
        $this->assertSame($admin['id'], $this->lastAudit('user.paste_allowed_changed', $user['id'])['actor_id']);
        $this->assertSame('lokakode', $this->lastAudit('access.granted', $user['id'])['detail']['app']);
        $this->assertSame('portal', $this->lastAudit('access.revoked', $user['id'])['detail']['app']);
        $this->assertSame(200, $sesi->get('/')->status, 'perubahan nama tidak mengeluarkan pengguna');

        // Peran berubah: sesi lama diakhiri.
        $r = $http->post('/admin/pengguna/' . $user['id'], ['csrf' => $csrf, 'nama' => 'Nama Baru', 'email' => '', 'global_role' => 'mahasiswa', 'status' => 'active', 'apps' => ['lokakode']]);
        $this->assertRedirect($r, '/admin/pengguna/' . $user['id'] . '?pesan=disimpan');
        $baris = $this->userRow($user['id']);
        $this->assertSame('mahasiswa', $baris['global_role']);
        $this->assertSame('1', (string) $baris['idle_logout']);
        $this->assertSame('0', (string) $baris['paste_allowed'], 'kotak yang tidak dicentang mencabut izin');
        $this->assertContains($sesi->get('/')->status, [302, 303]);
    }

    public function testMenangguhkanMengeluarkanPenggunaDanMencegahLogin(): void
    {
        [$http] = $this->admin();
        $user   = $this->user(['global_role' => 'dosen']);
        $sesi   = $this->sebagai($user);
        $this->assertSame(200, $sesi->get('/')->status);

        $http->post('/admin/pengguna/' . $user['id'], ['csrf' => $this->csrf($http, '/admin/pengguna/' . $user['id']), 'nama' => $user['nama'], 'email' => '', 'global_role' => 'dosen', 'status' => 'suspended']);
        $this->assertSame('suspended', $this->userRow($user['id'])['status']);
        $this->assertContains($sesi->get('/')->status, [302, 303], 'langsung dikeluarkan');
        $this->assertSame(422, $this->login($user)->status);
        $this->assertSame('suspended', $this->lastAudit('user.status_changed', $user['id'])['detail']['to']);

        $http->post('/admin/pengguna/' . $user['id'], ['csrf' => $this->csrf($http, '/admin/pengguna/' . $user['id']), 'nama' => $user['nama'], 'email' => '', 'global_role' => 'dosen', 'status' => 'active']);
        $this->assertRedirect($this->login($user), 'https://lokandita.com/');
    }

    public function testTidakBisaMengubahDiriSendiriAtauSuperadminTerakhir(): void
    {
        [$http, $admin] = $this->admin();
        $csrf = $this->csrf($http, '/admin/pengguna/' . $admin['id']);
        $isi  = ['csrf' => $csrf, 'nama' => $admin['nama'], 'email' => ''];

        $this->assertSame(422, $http->post('/admin/pengguna/' . $admin['id'], $isi + ['global_role' => 'superadmin', 'status' => 'disabled'])->status);
        $this->assertSame(422, $http->post('/admin/pengguna/' . $admin['id'], $isi + ['global_role' => 'dosen', 'status' => 'active'])->status);
        $baris = $this->userRow($admin['id']);
        $this->assertSame(['superadmin', 'active'], [$baris['global_role'], $baris['status']]);

        // Superadmin terakhir: semua superadmin lain disingkirkan sementara, hanya di dalam tes ini.
        $lain = $this->user(['global_role' => 'superadmin', 'mfa_enabled' => 1]);
        $this->db->query("UPDATE users SET status = 'suspended' WHERE global_role = 'superadmin' AND status = 'active' AND id NOT IN ('{$admin['id']}', '{$lain['id']}')");
        try {
            // Dua superadmin aktif: yang lain boleh diturunkan…
            $this->db->query("UPDATE users SET status = 'suspended' WHERE id = '{$admin['id']}'");
            $kedua = $this->sebagai($this->user(['global_role' => 'superadmin', 'mfa_enabled' => 1]));
            $this->db->query("UPDATE users SET status = 'suspended' WHERE id = (SELECT user_id FROM sessions WHERE token_hash = '" . hash('sha256', $kedua->cookies['lokandita_sid']) . "')");
            // …sekarang $lain satu-satunya yang aktif. Sesi $http (admin pertama) dihidupkan lagi sebagai pelaku yang ditangguhkan tidak bisa, jadi pakai SQL untuk memeriksa aturan lewat pelaku aktif kedua.
            $this->db->query("UPDATE users SET status = 'active' WHERE id = '{$admin['id']}'");
            $this->db->query("UPDATE users SET status = 'suspended' WHERE id = '{$lain['id']}'");
            // Kini $admin satu-satunya superadmin aktif; $lain (ditangguhkan) tidak dihitung.
            $pelaku = $this->user(['global_role' => 'superadmin', 'mfa_enabled' => 1]);
            $h      = $this->sebagai($pelaku);
            $this->db->query("UPDATE users SET status = 'suspended' WHERE id = '{$pelaku['id']}'");
            $this->assertContains($h->get('/admin/pengguna')->status, [302, 303], 'superadmin yang ditangguhkan tidak bisa bertindak');

            $this->db->query("UPDATE users SET status = 'active' WHERE id = '{$pelaku['id']}'");
            $h = $this->sebagai($pelaku);
            // Dua aktif ($admin dan $pelaku): menonaktifkan $admin boleh.
            $r = $h->post('/admin/pengguna/' . $admin['id'], ['csrf' => $this->csrf($h, '/admin/pengguna/' . $admin['id']), 'nama' => $admin['nama'], 'email' => '', 'global_role' => 'superadmin', 'status' => 'disabled']);
            $this->assertContains($r->status, [302, 303]);
            $this->assertSame('disabled', $this->userRow($admin['id'])['status']);
            // Sekarang $pelaku yang terakhir; aturan "superadmin terakhir" diuji langsung pada kelas aturan.
            $this->assertTrue(\App\Libraries\UserAdmin::isLastActiveSuperadmin($this->userRow($pelaku['id'])));
            $this->assertSame('superadminTerakhir', \App\Libraries\UserAdmin::setStatus($this->userRow($pelaku['id']), 'disabled', $admin['id']));
            $this->assertSame('superadminTerakhir', \App\Libraries\UserAdmin::updateProfile($this->userRow($pelaku['id']), ['nama' => 'x', 'email' => '', 'global_role' => 'dosen'], $admin['id']));
            $this->assertSame('active', $this->userRow($pelaku['id'])['status']);
        } finally {
            $this->db->query("UPDATE users SET status = 'active' WHERE global_role = 'superadmin' AND status = 'suspended'");
        }
    }

    public function testResetKataSandiDanHapusMfa(): void
    {
        [$http, $admin] = $this->admin();
        $user = $this->user(['global_role' => 'dosen']);
        $sesi = $this->sebagai($user);

        $r     = $http->post('/admin/pengguna/' . $user['id'] . '/reset-sandi', ['csrf' => $this->csrf($http, '/admin/pengguna/' . $user['id'])]);
        $sandi = $this->sandiDiHalaman($r);
        $this->assertContains($sesi->get('/')->status, [302, 303], 'sesi lama dicabut');
        $this->assertSame(422, $this->login($user)->status, 'kata sandi lama tidak berlaku');
        $this->assertRedirect($this->login($user, password: $sandi), '/ganti-password');
        $catatan = $this->lastAudit('password.reset', $user['id']);
        $this->assertSame($admin['id'], $catatan['actor_id']);
        $this->assertSame('auth', $catatan['app']);
        $this->assertStringNotContainsString($sandi, json_encode($catatan));

        // Hapus MFA superadmin lain.
        $lain = $this->user(['global_role' => 'superadmin', 'mfa_enabled' => 1]);
        $this->db->query("INSERT INTO mfa_backup_codes (user_id, code_hash) VALUES ('{$lain['id']}', REPEAT('a', 64))");
        $sesiLain = $this->sebagai($lain);
        $r = $http->post('/admin/pengguna/' . $lain['id'] . '/hapus-mfa', ['csrf' => $this->csrf($http, '/admin/pengguna/' . $lain['id'])]);
        $this->assertRedirect($r, '/admin/pengguna/' . $lain['id'] . '?pesan=mfa-dihapus');
        $baris = $this->userRow($lain['id']);
        $this->assertSame('0', (string) $baris['mfa_enabled']);
        $this->assertNull($baris['mfa_secret_enc']);
        $this->assertSame(0, (int) $this->row("SELECT COUNT(*) n FROM mfa_backup_codes WHERE user_id = '{$lain['id']}'")['n']);
        $this->assertContains($sesiLain->get('/')->status, [302, 303]);
        $this->assertSame($admin['id'], $this->lastAudit('mfa.cleared', $lain['id'])['actor_id']);
        // Login berikutnya superadmin itu wajib mendaftar ulang.
        $this->assertRedirect($this->login($lain), '/login/mfa-daftar');
    }

    public function testDaftarCariSaringDanAkunNonaktifTersembunyi(): void
    {
        [$http] = $this->admin();
        $tanda  = 'cari' . bin2hex(random_bytes(3));
        $a = $this->user(['nomor_induk' => $tanda . '1', 'nama' => 'Ani ' . $tanda, 'global_role' => 'dosen']);
        $b = $this->user(['nomor_induk' => $tanda . '2', 'nama' => 'Budi ' . $tanda, 'global_role' => 'mahasiswa', 'status' => 'suspended']);
        $c = $this->user(['nomor_induk' => $tanda . '3', 'nama' => 'Cici ' . $tanda, 'global_role' => 'mahasiswa', 'status' => 'disabled']);

        $isi = $http->get('/admin/pengguna?q=' . $tanda)->body;
        $this->assertStringContainsString($a['nomor_induk'], $isi);
        $this->assertStringContainsString($b['nomor_induk'], $isi);
        $this->assertStringNotContainsString($c['nomor_induk'], $isi, 'dinonaktifkan: tidak tampil secara bawaan');

        $this->assertStringContainsString($c['nomor_induk'], $http->get('/admin/pengguna?q=' . $tanda . '&status=semua')->body);
        $this->assertStringContainsString($c['nomor_induk'], $http->get('/admin/pengguna?q=' . $tanda . '&status=disabled')->body);
        $isi = $http->get('/admin/pengguna?q=' . $tanda . '&peran=dosen')->body;
        $this->assertStringContainsString($a['nomor_induk'], $isi);
        $this->assertStringNotContainsString($b['nomor_induk'], $isi);
        $this->assertStringContainsString($b['nomor_induk'], $http->get('/admin/pengguna?q=' . rawurlencode('Budi ' . $tanda))->body, 'cari dengan nama');
        $this->assertStringNotContainsString('argon2', $isi);

        // Percobaan injeksi SQL lewat kolom cari hanya dianggap teks.
        $r = $http->get('/admin/pengguna?q=' . rawurlencode("' OR 1=1 -- "));
        $this->assertSame(200, $r->status);
        $this->assertStringNotContainsString($a['nomor_induk'], $r->body);
    }

    public function testDaftarDanEksporTetapCepatPadaLimaRibuBaris(): void
    {
        [$http] = $this->admin();
        if ((int) $this->row("SELECT COUNT(*) n FROM users WHERE nomor_induk LIKE 'besar%'")['n'] < 5000) {
            $nilai = [];
            for ($i = 0; $i < 5000; $i++) {
                $nilai[] = sprintf("(UUID(), 'besar%05d', 'Mahasiswa Besar %05d', 'x', 'mahasiswa', UTC_TIMESTAMP(), UTC_TIMESTAMP())", $i, $i);
            }
            $this->db->query('INSERT INTO users (id, nomor_induk, nama, password_hash, global_role, created_at, updated_at) VALUES ' . implode(',', $nilai));
        }

        $mulai = microtime(true);
        $r     = $http->get('/admin/pengguna?q=besar&per=200&hal=13');
        $lama  = microtime(true) - $mulai;
        $this->assertSame(200, $r->status);
        $this->assertLessThan(2.0, $lama, 'halaman daftar pada 5.000 baris');
        $this->assertSame(200, preg_match_all('/name="ids\[\]"/', $r->body));
        $this->assertStringContainsString('besar02400', $r->body);
        $this->assertStringContainsString('Halaman 13 dari 25', $r->body);

        $mulai = microtime(true);
        $csv   = $http->get('/admin/pengguna/ekspor?format=csv&q=besar');
        $this->assertLessThan(5.0, microtime(true) - $mulai, 'ekspor CSV 5.000 baris');
        $this->assertSame(200, $csv->status);
        $this->assertSame(5001, count(array_filter(explode("\n", $csv->body))));
        $this->assertStringContainsString('nomor_induk,nama,email,global_role,status,apps', $csv->body);
        $this->assertStringNotContainsString('password', $csv->body);

        $xlsx = $http->get('/admin/pengguna/ekspor?format=xlsx&q=besar0001');
        $this->assertSame(200, $xlsx->status);
        $this->assertStringStartsWith('PK', $xlsx->body, 'berkas XLSX adalah arsip zip');
        $berkas = tempnam(sys_get_temp_dir(), 'x');
        file_put_contents($berkas, $xlsx->body);
        $baca = Tabular::read($berkas, 'xlsx');
        unlink($berkas);
        $this->assertCount(10, $baca);
        $this->assertSame('besar00010', $baca[0]['nomor_induk']);
        $this->assertNotNull($this->lastAudit('user.exported'));
    }

    public function testTindakanMassalMenyebutJumlahDanBaruBerlakuSetelahDikonfirmasi(): void
    {
        [$http, $admin] = $this->admin();
        $u = [$this->user(), $this->user(), $this->user()];
        $ids = array_column($u, 'id');
        $csrf = $this->csrf($http, '/admin/pengguna');

        $konfirmasi = $http->post('/admin/pengguna/massal', ['csrf' => $csrf, 'aksi' => 'status:suspended', 'ids' => $ids]);
        $this->assertSame(200, $konfirmasi->status);
        $this->assertMatchesRegularExpression('/data-konfirmasi><strong>[^<]*\b3 pengguna/', $konfirmasi->body, 'konfirmasi menyebut jumlah baris');
        $this->assertSame('active', $this->userRow($ids[0])['status'], 'belum ada yang berubah sebelum dikonfirmasi');

        // Termasuk diri sendiri: yang lain berubah, diri sendiri ditolak dengan alasan.
        $hasil = $http->post('/admin/pengguna/massal', ['csrf' => $csrf, 'aksi' => 'status:suspended', 'ids' => [...$ids, $admin['id']], 'yakin' => '1']);
        $this->assertSame(200, $hasil->status);
        foreach ($ids as $id) {
            $this->assertSame('suspended', $this->userRow($id)['status']);
        }
        $this->assertSame('active', $this->userRow($admin['id'])['status']);
        $this->assertStringContainsString($admin['nomor_induk'], $hasil->body);
        $catatan = $this->lastAudit('user.bulk_action');
        $this->assertSame(['status:suspended', 4, 3, 1], [$catatan['detail']['action'], $catatan['detail']['selected'], $catatan['detail']['done'], $catatan['detail']['refused']]);

        $http->post('/admin/pengguna/massal', ['csrf' => $csrf, 'aksi' => 'status:active', 'ids' => $ids, 'yakin' => '1']);
        $http->post('/admin/pengguna/massal', ['csrf' => $csrf, 'aksi' => 'akses:-:lokakode', 'ids' => $ids, 'yakin' => '1']);
        $this->assertSame(0, (int) $this->row("SELECT COUNT(*) n FROM app_access WHERE app = 'lokakode' AND enabled = 1 AND user_id IN ('" . implode("','", $ids) . "')")['n']);
        $http->post('/admin/pengguna/massal', ['csrf' => $csrf, 'aksi' => 'akses:+:lokaujian', 'ids' => $ids, 'yakin' => '1']);
        $this->assertSame(3, (int) $this->row("SELECT COUNT(*) n FROM app_access WHERE app = 'lokaujian' AND enabled = 1 AND user_id IN ('" . implode("','", $ids) . "')")['n']);

        foreach (['hapus', 'status:dihapus', 'akses:+:gelap', ''] as $aksi) {
            $this->assertRedirect($http->post('/admin/pengguna/massal', ['csrf' => $csrf, 'aksi' => $aksi, 'ids' => $ids, 'yakin' => '1']), '/admin/pengguna?pesan=massal-kosong', $aksi);
        }
        $this->assertRedirect($http->post('/admin/pengguna/massal', ['csrf' => $csrf, 'aksi' => 'reset', 'yakin' => '1']), '/admin/pengguna?pesan=massal-kosong');
    }

    public function testResetMassalMemberiUnduhanSekali(): void
    {
        [$http] = $this->admin();
        $u      = [$this->user(), $this->user()];
        $hasil  = $http->post('/admin/pengguna/massal', ['csrf' => $this->csrf($http, '/admin/pengguna'), 'aksi' => 'reset', 'ids' => array_column($u, 'id'), 'yakin' => '1']);
        $this->assertSame(1, preg_match('#href="(/admin/kredensial/[0-9a-f]{48})\?format=csv"#', $hasil->body, $m));
        $this->assertStringNotContainsString('data-sandi-sementara', $hasil->body);

        $csv = $http->get($m[1] . '?format=csv');
        $this->assertSame(200, $csv->status);
        $baris = array_map('str_getcsv', array_filter(explode("\n", trim(substr($csv->body, 3)))));
        $this->assertCount(3, $baris);
        $sandi = array_column(array_slice($baris, 1), 2, 0);
        foreach ($u as $x) {
            $this->assertRedirect($this->login($x, password: $sandi[$x['nomor_induk']]), '/ganti-password', 'kata sandi dari berkas berlaku');
        }
        $this->assertSame(410, $http->get($m[1] . '?format=csv')->status, 'unduhan kedua ditolak');
    }

    public function testHalamanAuditBisaDisaring(): void
    {
        [$http, $admin] = $this->admin();
        $user = $this->user(['global_role' => 'dosen']);
        $http->post('/admin/pengguna/' . $user['id'] . '/reset-sandi', ['csrf' => $this->csrf($http, '/admin/pengguna/' . $user['id'])]);

        $r = $http->get('/admin/audit?aksi=password.&target=' . $user['nomor_induk']);
        $this->assertSame(200, $r->status);
        $this->assertSame(1, preg_match_all('/data-audit="password\.reset"/', $r->body));
        $this->assertStringContainsString($admin['nomor_induk'], $r->body, 'pelaku ditampilkan');
        $this->assertStringContainsString('WIB', $r->body, 'waktu ditampilkan dalam zona Jakarta');

        $this->assertSame(0, preg_match_all('/data-audit=/', $http->get('/admin/audit?target=' . $user['nomor_induk'] . '&aksi=login.')->body));
        $this->assertSame(1, preg_match_all('/data-audit=/', $http->get('/admin/audit?target=' . $user['nomor_induk'] . '&aktor=' . $admin['nomor_induk'] . '&app=auth&dari=' . gmdate('Y-m-d', time() + 7 * 3600) . '&sampai=' . gmdate('Y-m-d', time() + 7 * 3600))->body));
        $this->assertSame(0, preg_match_all('/data-audit=/', $http->get('/admin/audit?target=' . $user['nomor_induk'] . '&sampai=2020-01-01')->body));
        $this->assertSame(0, preg_match_all('/data-audit=/', $http->get('/admin/audit?target=' . $user['nomor_induk'] . '&app=lokakode')->body));
    }

    public function testAngkaTetapLatinDalamBahasaArab(): void
    {
        [$http, $admin] = $this->admin();
        $this->db->query("UPDATE users SET locale = 'ar' WHERE id = '{$admin['id']}'");
        $r = $http->get('/ganti-password');
        $this->assertStringContainsString('<html lang="ar" dir="rtl">', $r->body);
        $this->assertStringContainsString('10 أحرف', $r->body);
        $this->assertDoesNotMatchRegularExpression('/[٠-٩]/u', $http->get('/admin/pengguna')->body);
    }
}
