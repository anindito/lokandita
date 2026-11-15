<?php

declare(strict_types=1);

namespace Tests\Akademik;

use Lokandita\Auth\Testing\Fixtures;
use Lokandita\Auth\Testing\Http;
use Tests\Support\KodeCase;

/**
 * Pindah kelas, kelulusan dan reset kata sandi mahasiswa (SPEC.md §3, §5).
 */
final class KelulusanDanSandiTest extends KodeCase
{
    public function testPindahKelasDiDalamMataKuliahMenjagaBarisPendaftaran(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengampuX']);
        $csrf = $this->csrf($http);

        $hal = $http->get('/kelas/' . $d['kelasXA'])->body;
        $this->assertMatchesRegularExpression('/<select id="tujuan" name="tujuan">\s*<option value="' . $d['kelasXB'] . '">XB/', $hal);
        $this->assertStringNotContainsString('<option value="' . $d['kelasYA'] . '"', $hal, 'kelas mata kuliah lain bukan tujuan');

        $r = $http->post('/kelas/' . $d['kelasXA'] . '/pindah', ['csrf' => $csrf, 'ids' => [$d['daftarA']], 'tujuan' => (string) $d['kelasXB']]);
        $this->assertSame(303, $r->status, $r->body);
        $this->assertStringEndsWith('pesan=dipindah&n=1', (string) $r->header('Location'));

        $e = $this->row("SELECT * FROM enrollments WHERE user_id = '{$d['mhsA']['id']}'");
        $this->assertSame($d['daftarA'], (int) $e['id'], 'baris pendaftaran yang sama');
        $this->assertSame($d['kelasXB'], (int) $e['class_id']);
        $this->assertSame('AKTIF', $e['status']);
        $pindah = $this->row('SELECT * FROM enrollment_moves');
        $this->assertSame([$d['daftarA'], $d['kelasXA'], $d['kelasXB'], $d['pengampuX']['id']], [(int) $pindah['enrollment_id'], (int) $pindah['from_class_id'], (int) $pindah['to_class_id'], $pindah['moved_by']]);
        $this->assertSame(1, $this->hitung("SELECT COUNT(*) FROM audit_log WHERE action = 'pendaftaran.dipindah' AND target_id = '{$d['mhsA']['id']}' AND class_id = {$d['kelasXB']}"));

        // Hak pengajar ikut berpindah bersama mahasiswanya.
        $this->assertStringNotContainsString('Mahasiswa Ani', $this->sebagai($d['pengajarA'])->get('/kelas/' . $d['kelasXA'])->body);
        $this->assertStringContainsString('Mahasiswa Ani', $this->sebagai($d['pengajarB'])->get('/kelas/' . $d['kelasXB'])->body);
        // Kelas asal kini punya riwayat pindah: tidak bisa dihapus walau kosong.
        $r = $http->post('/kelas/' . $d['kelasXA'] . '/hapus', ['csrf' => $csrf, 'yakin' => '1']);
        $this->assertSame(409, $r->status);
        $this->assertStringContainsString('0 pendaftaran dan 1 riwayat pindah kelas', $r->body);
    }

    public function testPindahKeMataKuliahLainAtauKelasTakTerlihatDitolak(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengampuX']);
        $csrf = $this->csrf($http);
        $lama = $this->tahun('2025/2026 Genap', 'archived', '2026-02-01', '2026-07-31');
        $xl   = $this->kelas($d['matkulX'], $lama, 'XLAMA');

        // Kelas mata kuliah Y: pengampu X tidak melihatnya.
        $this->assertSame(404, $http->post('/kelas/' . $d['kelasXA'] . '/pindah', ['csrf' => $csrf, 'ids' => [$d['daftarA']], 'tujuan' => (string) $d['kelasYA']])->status);
        // Kelas mata kuliah yang sama di tahun yang sudah diarsipkan.
        $this->assertSame(403, $http->post('/kelas/' . $d['kelasXA'] . '/pindah', ['csrf' => $csrf, 'ids' => [$d['daftarA']], 'tujuan' => (string) $xl])->status);
        // Ke kelas itu sendiri.
        $r = $http->post('/kelas/' . $d['kelasXA'] . '/pindah', ['csrf' => $csrf, 'ids' => [$d['daftarA']], 'tujuan' => (string) $d['kelasXA']]);
        $this->assertStringEndsWith('pesan=tujuanSalah', (string) $r->header('Location'));
        // Tujuan diarsipkan.
        $this->db->query("UPDATE classes SET archived_at = UTC_TIMESTAMP() WHERE id = {$d['kelasXB']}");
        $r = $http->post('/kelas/' . $d['kelasXA'] . '/pindah', ['csrf' => $csrf, 'ids' => [$d['daftarA']], 'tujuan' => (string) $d['kelasXB']]);
        $this->assertStringEndsWith('pesan=tujuanArsip', (string) $r->header('Location'));

        // Superadmin melihat semua kelas, tetapi lintas mata kuliah tetap ditolak.
        $admin = $this->sebagai($d['superadmin']);
        $r     = $admin->post('/kelas/' . $d['kelasXA'] . '/pindah', ['csrf' => $this->csrf($admin), 'ids' => [$d['daftarA']], 'tujuan' => (string) $d['kelasYA']]);
        $this->assertStringEndsWith('pesan=tujuanSalah', (string) $r->header('Location'));

        $this->assertSame($d['kelasXA'], (int) $this->row("SELECT class_id FROM enrollments WHERE id = {$d['daftarA']}")['class_id']);
        $this->assertSame(0, $this->hitung('SELECT COUNT(*) FROM enrollment_moves'));
    }

    public function testKelulusanSatuBanyakDanSeluruhKelasLaluDibatalkan(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengampuX']);
        $csrf = $this->csrf($http);
        $e2   = $this->daftar($this->user('mahasiswa', 'Mahasiswa Dua'), $d['kelasXA']);
        $e3   = $this->daftar($this->user('mahasiswa', 'Mahasiswa Tiga'), $d['kelasXA']);
        $e4   = $this->daftar($this->user('mahasiswa', 'Sudah Mundur'), $d['kelasXA'], 'MENGUNDURKAN_DIRI');

        // Satu orang, dengan tanggal.
        $r = $http->post('/kelas/' . $d['kelasXA'] . '/lulus', ['csrf' => $csrf, 'ids' => [$d['daftarA']], 'tanggal' => '2027-01-20']);
        $this->assertStringEndsWith('pesan=lulus&n=1', (string) $r->header('Location'));
        $e = $this->row("SELECT status, graduated_on FROM enrollments WHERE id = {$d['daftarA']}");
        $this->assertSame(['LULUS', '2027-01-20'], [$e['status'], $e['graduated_on']]);
        $hal = $http->get('/kelas/' . $d['kelasXA'])->body;
        $this->assertStringContainsString('lulus 20-01-2027', $hal);
        $this->assertStringContainsString('4 mahasiswa, 2 aktif', $hal);

        // Seluruh kelas: hanya yang AKTIF; yang mundur tidak ikut lulus.
        $r = $http->post('/kelas/' . $d['kelasXA'] . '/lulus', ['csrf' => $csrf, 'semua' => '1', 'tanggal' => '2027-01-31']);
        $this->assertStringEndsWith('pesan=lulus&n=2', (string) $r->header('Location'));
        $this->assertSame(3, $this->hitung("SELECT COUNT(*) FROM enrollments WHERE class_id = {$d['kelasXA']} AND status = 'LULUS'"));
        $this->assertSame('MENGUNDURKAN_DIRI', $this->row("SELECT status FROM enrollments WHERE id = {$e4}")['status']);
        $this->assertSame('2027-01-20', $this->row("SELECT graduated_on FROM enrollments WHERE id = {$d['daftarA']}")['graduated_on'], 'yang sudah lulus tidak ditimpa');
        $this->assertSame(3, $this->hitung("SELECT COUNT(*) FROM audit_log WHERE action = 'pendaftaran.lulus'"));
        $this->assertStringContainsString('data-ringkas="mahasiswa">1<', $this->sebagai($d['pengampuX'])->get('/beranda')->body, 'yang lulus tidak dihitung aktif');

        // Dibatalkan untuk dua orang.
        $r = $http->post('/kelas/' . $d['kelasXA'] . '/batal-lulus', ['csrf' => $csrf, 'ids' => [$e2, $e3, $e4]]);
        $this->assertStringEndsWith('pesan=batal-lulus&n=2', (string) $r->header('Location'));
        $e = $this->row("SELECT status, graduated_on FROM enrollments WHERE id = {$e2}");
        $this->assertSame(['AKTIF', null], [$e['status'], $e['graduated_on']]);
        $this->assertSame(2, $this->hitung("SELECT COUNT(*) FROM audit_log WHERE action = 'pendaftaran.batal-lulus'"));

        // Mahasiswa yang lulus tetap melihat kelasnya, dengan status lulus.
        $this->assertStringContainsString('Lulus', $this->sebagai($d['mhsA'])->get('/beranda')->body);
    }

    public function testTanggalKelulusanWajibSahDanTanpaPilihanTidakMengubahApaPun(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengampuX']);
        $csrf = $this->csrf($http);

        $r = $http->post('/kelas/' . $d['kelasXA'] . '/lulus', ['csrf' => $csrf, 'semua' => '1', 'tanggal' => '31/01/2027']);
        $this->assertStringEndsWith('pesan=tanggalLulus', (string) $r->header('Location'));
        $r = $http->post('/kelas/' . $d['kelasXA'] . '/lulus', ['csrf' => $csrf, 'tanggal' => '2027-01-31']);
        $this->assertStringContainsString('pesan=tidak-ada-pilihan', (string) $r->header('Location'));
        // id pendaftaran kelas XB lewat URL kelas XA.
        $http->post('/kelas/' . $d['kelasXA'] . '/lulus', ['csrf' => $csrf, 'ids' => [$d['daftarB']], 'tanggal' => '2027-01-31']);
        $this->assertSame(3, $this->hitung("SELECT COUNT(*) FROM enrollments WHERE status = 'AKTIF'"));
    }

    public function testPengajarMeresetKataSandiMahasiswaKelasnya(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengajarA']);
        $csrf = $this->csrf($http);
        $mhs  = $d['mhsA'];
        // Mahasiswa sedang masuk.
        $sesiMhs = $this->sebagai($mhs);
        $this->assertSame(200, $sesiMhs->get('/beranda')->status);

        // Langkah 1: konfirmasi, belum ada yang berubah.
        $r = $http->post('/kelas/' . $d['kelasXA'] . '/reset-sandi', ['csrf' => $csrf, 'user_id' => $mhs['id']]);
        $this->assertSame(200, $r->status, $r->body);
        $this->assertStringContainsString('Reset kata sandi mahasiswa ini?', $r->body);
        $this->assertStringContainsString($mhs['nomor_induk'], $r->body);
        $this->assertSame($mhs['password_hash'], $this->idn->query("SELECT password_hash FROM users WHERE id = '{$mhs['id']}'")->fetch_row()[0]);

        // Langkah 2: LokaAuth yang mereset, lewat HTTP antar-kontainer.
        $r = $http->post('/kelas/' . $d['kelasXA'] . '/reset-sandi', ['csrf' => $csrf, 'user_id' => $mhs['id'], 'yakin' => '1']);
        $this->assertSame(200, $r->status, $r->body);
        $this->assertSame(1, preg_match('/data-sandi-sementara>([^<]+)</', $r->body, $m));
        $sandi = html_entity_decode($m[1], ENT_QUOTES);
        $this->assertStringContainsString('no-store', (string) $r->header('Cache-Control'));

        $akun = $this->idn->query("SELECT password_hash, must_change_password FROM users WHERE id = '{$mhs['id']}'")->fetch_assoc();
        $this->assertTrue(password_verify($sandi, $akun['password_hash']), 'kata sandi yang ditampilkan adalah yang berlaku');
        $this->assertSame(1, (int) $akun['must_change_password']);
        // Sesi lama mahasiswa berakhir di semua aplikasi.
        $this->assertContains($sesiMhs->get('/beranda')->status, [302, 303, 307]);
        // Login dengan kata sandi sementara berhasil di LokaAuth yang sungguhan.
        $masuk = Fixtures::login(new Http((string) getenv('AUTH_TES_URL')), $mhs, null, $sandi);
        $this->assertArrayHasKey('lokandita_sid', $masuk->cookies);

        // Dua log audit: LokaAuth mencatat pemintanya dan aplikasinya; LokaKode mencatat tanpa kata sandi.
        $a = $this->idn->query("SELECT actor_id, app FROM audit_log WHERE action = 'password.reset' AND target_id = '{$mhs['id']}'")->fetch_assoc();
        $this->assertSame([$d['pengajarA']['id'], 'lokakode'], [$a['actor_id'], $a['app']]);
        $b = $this->row("SELECT * FROM audit_log WHERE action = 'sandi.direset'");
        $this->assertSame([$d['pengajarA']['id'], $mhs['id']], [$b['actor_id'], $b['target_id']]);
        $this->assertStringNotContainsString($sandi, (string) $b['detail']);
    }

    public function testResetKataSandiHanyaUntukMahasiswaAktifDiKelasYangTerlihat(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengajarA']);
        $csrf = $this->csrf($http);
        $hash = fn (array $u) => $this->idn->query("SELECT password_hash FROM users WHERE id = '{$u['id']}'")->fetch_row()[0];

        // Mahasiswa kelas XB lewat URL kelas XA (kelasnya sendiri): bukan mahasiswa kelas itu.
        $this->assertSame(404, $http->post('/kelas/' . $d['kelasXA'] . '/reset-sandi', ['csrf' => $csrf, 'user_id' => $d['mhsB']['id'], 'yakin' => '1'])->status);
        // Mahasiswa kelas XB lewat URL kelas XB: kelas itu tidak terlihat olehnya.
        $this->assertSame(404, $http->post('/kelas/' . $d['kelasXB'] . '/reset-sandi', ['csrf' => $csrf, 'user_id' => $d['mhsB']['id'], 'yakin' => '1'])->status);
        // Dosen lain dan superadmin sebagai sasaran.
        foreach (['pengajarB', 'pengampuX', 'superadmin'] as $s) {
            $this->assertSame(404, $http->post('/kelas/' . $d['kelasXA'] . '/reset-sandi', ['csrf' => $csrf, 'user_id' => $d[$s]['id'], 'yakin' => '1'])->status, $s);
            $this->assertSame($d[$s]['password_hash'], $hash($d[$s]), $s);
        }
        // Mahasiswa yang sudah mengundurkan diri dari kelasnya.
        $mundur = $this->user('mahasiswa', 'Sudah Mundur');
        $this->daftar($mundur, $d['kelasXA'], 'MENGUNDURKAN_DIRI');
        $this->assertSame(404, $http->post('/kelas/' . $d['kelasXA'] . '/reset-sandi', ['csrf' => $csrf, 'user_id' => $mundur['id'], 'yakin' => '1'])->status);

        $this->assertSame($d['mhsB']['password_hash'], $hash($d['mhsB']));
        $this->assertSame($mundur['password_hash'], $hash($mundur));
        $this->assertSame(0, $this->hitung("SELECT COUNT(*) FROM audit_log WHERE action = 'sandi.direset'"));

        // Pengampu boleh untuk seluruh mata kuliahnya; pengampu mata kuliah lain tidak.
        $x = $this->sebagai($d['pengampuX']);
        $this->assertSame(200, $x->post('/kelas/' . $d['kelasXB'] . '/reset-sandi', ['csrf' => $this->csrf($x), 'user_id' => $d['mhsB']['id']])->status);
        $y = $this->sebagai($d['pengampuY']);
        $this->assertSame(404, $y->post('/kelas/' . $d['kelasXB'] . '/reset-sandi', ['csrf' => $this->csrf($y), 'user_id' => $d['mhsB']['id'], 'yakin' => '1'])->status);
        $this->assertSame($d['mhsB']['password_hash'], $hash($d['mhsB']));
    }

    public function testLapisKeduaLokaAuthMenolakSasaranBukanMahasiswa(): void
    {
        // Andai pemeriksaan LokaKode keliru: sebuah akun dosen yang (salah) tercatat sebagai pendaftaran aktif.
        $d = $this->dunia();
        $this->daftar($d['pengajarB'], $d['kelasXA']);
        $http = $this->sebagai($d['pengampuX']);

        $r = $http->post('/kelas/' . $d['kelasXA'] . '/reset-sandi', ['csrf' => $this->csrf($http), 'user_id' => $d['pengajarB']['id'], 'yakin' => '1']);
        $this->assertSame(403, $r->status);
        $this->assertStringContainsString('bukan akun mahasiswa', $r->body);
        $this->assertStringNotContainsString('data-sandi-sementara', $r->body);
        $this->assertSame($d['pengajarB']['password_hash'], $this->idn->query("SELECT password_hash FROM users WHERE id = '{$d['pengajarB']['id']}'")->fetch_row()[0]);
        $this->assertSame(0, $this->hitung("SELECT COUNT(*) FROM audit_log WHERE action = 'sandi.direset'"));
    }

    public function testPengajarMelihatTombolResetTetapiBukanTindakanPengelola(): void
    {
        $d   = $this->dunia();
        $hal = $this->sebagai($d['pengajarA'])->get('/kelas/' . $d['kelasXA'])->body;
        $this->assertStringContainsString('formaction="/kelas/' . $d['kelasXA'] . '/reset-sandi"', $hal);
        foreach (['/mundur"', '/pindah"', '/lulus"', '/batal-lulus"', 'name="ids[]"', '/daftar"'] as $tidak) {
            $this->assertStringNotContainsString($tidak, $hal, $tidak);
        }
    }
}
