<?php

declare(strict_types=1);

namespace Tests\Akademik;

use Lokandita\Auth\Testing\Fixtures;
use Lokandita\Auth\Testing\Http;
use Tests\Support\KodeCase;

/**
 * Reset kata sandi mahasiswa dari halaman kelas (SPEC.md §3). Pindah kelas dan kelulusan sejak
 * tahap A2 ada di Akun Lokandita (apps/auth/tests/Roster/PendaftaranTest.php).
 */
final class ResetSandiTest extends KodeCase
{
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

    public function testHalamanKelasHanyaMemuatTombolResetBukanTindakanPengelola(): void
    {
        $d = $this->dunia();
        // Sejak tahap A2 berlaku untuk SEMUA peran staf: pengelolaannya ada di Akun Lokandita.
        foreach (['pengajarA', 'pengampuX', 'superadmin'] as $siapa) {
            $hal = $this->sebagai($d[$siapa])->get('/kelas/' . $d['kelasXA'])->body;
            $this->assertStringContainsString('formaction="/kelas/' . $d['kelasXA'] . '/reset-sandi"', $hal, $siapa);
            foreach (['/mundur"', '/pindah"', '/lulus"', '/batal-lulus"', 'name="ids[]"', '/daftar"', '/arsip"', '/hapus"', '/pengajar"', '/ubah"'] as $tidak) {
                $this->assertStringNotContainsString($tidak, $hal, "{$siapa}: {$tidak}");
            }
        }
    }
}
