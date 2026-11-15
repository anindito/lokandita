<?php

declare(strict_types=1);

namespace Tests\Admin;

use App\Libraries\Tabular;
use Lokandita\Auth\Testing\Http;
use Tests\Support\AuthCase;

/**
 * SPEC.md §4.1: impor massal dengan pratinjau, kata sandi awal, dan unduhan sekali.
 */
final class ImportTest extends AuthCase
{
    private function unggah(Http $http, string $nama, string $isi): Http
    {
        return $http->upload('/admin/impor', ['csrf' => $this->csrf($http, '/admin/impor')], 'berkas', $nama, $isi);
    }

    private function token(Http $http): string
    {
        return (string) $http->input('token');
    }

    public function testPratinjauMemilahBaruSudahAdaDanBermasalah(): void
    {
        [$http] = $this->admin();
        $t      = 'imp' . bin2hex(random_bytes(3));
        $ada    = $this->user();
        $csv    = "nomor_induk,nama,email,global_role,apps\n"
            . "{$t}01,Satu Baru,,mahasiswa,portal|lokakode\n"
            . "{$t}02,Dua Baru,{$t}02@contoh.test,dosen,lokakode\n"
            . "{$ada['nomor_induk']},Sudah Ada,,mahasiswa,portal\n"
            . "{$t}01,Kembar,,mahasiswa,portal\n"
            . "x,Nomor Pendek,,mahasiswa,\n"
            . "{$t}05,,,mahasiswa,\n"
            . "{$t}06,Peran Salah,,rektor,\n"
            . "{$t}07,Calon Admin,,superadmin,\n"
            . "{$t}08,Email Salah,bukan-email,mahasiswa,\n"
            . "{$t}09,Aplikasi Gelap,,mahasiswa,lokakode|gelap\n"
            . "\n";

        $r = $this->unggah($http, 'mahasiswa.csv', $csv);
        $this->assertSame(200, $r->status, substr(strip_tags($r->body), 0, 300));
        $this->assertSame(2, preg_match_all('/<tr data-baru>/', $r->body));
        $this->assertSame(1, preg_match_all('/<tr data-ada>/', $r->body));
        $this->assertSame(7, preg_match_all('/<tr data-salah>/', $r->body));
        $this->assertMatchesRegularExpression('/data-ringkasan>2 akun baru akan dibuat, 1 sudah terdaftar[^<]*7 baris bermasalah/', $r->body);
        // Alasan menyebut apa yang salah, dan nomor baris di berkas.
        $this->assertMatchesRegularExpression('/<td>5<\/td>\s*<td class="ltr">' . $t . '01<\/td>.*?lebih dari sekali/s', $r->body);
        $this->assertStringContainsString('Superadmin tidak bisa dibuat lewat impor', $r->body);
        $this->assertStringContainsString('Nama aplikasi tidak dikenal', $r->body);

        $this->assertSame(0, (int) $this->row("SELECT COUNT(*) n FROM users WHERE nomor_induk LIKE '{$t}%'")['n'], 'pratinjau tidak menyimpan apa pun');
    }

    public function testMenjalankanImporDenganKataSandiAcak(): void
    {
        [$http, $admin] = $this->admin();
        $t   = 'jal' . bin2hex(random_bytes(3));
        $csv = "nomor_induk;nama;email;global_role;apps\n{$t}01;Satu;;mahasiswa;portal|lokakode\n{$t}02;Dua;;dosen;portal\n{$t}03;Admin;;superadmin;portal\n";
        $r   = $this->unggah($http, 'titik-koma.csv', $csv);
        $this->assertSame(2, preg_match_all('/<tr data-baru>/', $r->body), 'pemisah titik koma dikenali');

        // Http mengembalikan dirinya sendiri: token diambil sekarang, sebelum permintaan berikutnya menimpa badan halaman.
        $csrf  = (string) $r->input('csrf');
        $token = $this->token($r);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{32,}$/', $token);

        $hasil = $http->post('/admin/impor/jalankan', ['csrf' => $csrf, 'token' => $token, 'mode' => 'acak']);
        $this->assertSame(200, $hasil->status);
        $this->assertMatchesRegularExpression('/data-hasil>2 akun dibuat/', $hasil->body);

        $baris = $this->db->query("SELECT * FROM users WHERE nomor_induk LIKE '{$t}%' ORDER BY nomor_induk")->fetch_all(MYSQLI_ASSOC);
        $this->assertCount(2, $baris, 'baris superadmin tidak ikut dibuat');
        $this->assertSame(['mahasiswa', 'dosen'], array_column($baris, 'global_role'));
        $this->assertSame(['1', '1'], array_map('strval', array_column($baris, 'must_change_password')));
        $this->assertSame(['1', '0'], array_map('strval', array_column($baris, 'idle_logout')));
        $this->assertSame($admin['id'], $baris[0]['created_by']);
        $this->assertNotSame($baris[0]['password_hash'], $baris[1]['password_hash']);

        $catatan = $this->lastAudit('user.imported');
        $this->assertSame(['titik-koma.csv', 2, 1, 'random'], [$catatan['detail']['file'], $catatan['detail']['created'], $catatan['detail']['invalid'], $catatan['detail']['password_mode']]);
        $this->assertSame($admin['id'], $catatan['actor_id']);

        $this->assertSame(1, preg_match('#href="(/admin/kredensial/[0-9a-f]{48})\?format=csv"#', $hasil->body, $m));
        $csvSandi = $http->get($m[1] . '?format=csv');
        $daftar   = array_column(array_slice(array_map('str_getcsv', array_filter(explode("\n", trim(substr($csvSandi->body, 3))))), 1), 2, 0);
        $this->assertCount(2, $daftar);
        $this->assertNotSame($daftar["{$t}01"], $daftar["{$t}02"]);
        $this->assertRedirect($this->login(['nomor_induk' => "{$t}01", 'password' => $daftar["{$t}01"]]), '/ganti-password');
        $this->assertSame(410, $http->get($m[1])->status);

        // Pratinjau yang sama tidak bisa dijalankan dua kali.
        $lagi = $http->post('/admin/impor/jalankan', ['csrf' => $csrf, 'token' => $token, 'mode' => 'acak']);
        $this->assertSame(410, $lagi->status);
        $this->assertSame(2, (int) $this->row("SELECT COUNT(*) n FROM users WHERE nomor_induk LIKE '{$t}%'")['n']);
    }

    public function testKataSandiBersamaHarusLolosAturanDanBerlakuUntukSemua(): void
    {
        [$http] = $this->admin();
        $t = 'sam' . bin2hex(random_bytes(3));
        $r = $this->unggah($http, 'a.csv', "nomor_induk,nama,global_role\n{$t}01,Satu,mahasiswa\n{$t}02,Dua,mahasiswa\n");
        $this->assertSame(2, preg_match_all('/<tr data-baru>/', $r->body), 'kolom email dan apps boleh tidak ada');
        $token = $this->token($r);
        $csrf  = (string) $r->input('csrf');

        foreach (['pendek', 'password123', '1234567890'] as $lemah) {
            $tolak = $http->post('/admin/impor/jalankan', ['csrf' => $csrf, 'token' => $token, 'mode' => 'sama', 'sandi_bersama' => $lemah]);
            $this->assertSame(422, $tolak->status, $lemah);
        }
        $this->assertSame(0, (int) $this->row("SELECT COUNT(*) n FROM users WHERE nomor_induk LIKE '{$t}%'")['n']);

        $ok = $http->post('/admin/impor/jalankan', ['csrf' => $csrf, 'token' => $token, 'mode' => 'sama', 'sandi_bersama' => 'Awal-Semester-Ganjil']);
        $this->assertMatchesRegularExpression('/data-hasil>2 akun dibuat/', $ok->body);
        foreach (["{$t}01", "{$t}02"] as $nomor) {
            $this->assertRedirect($this->login(['nomor_induk' => $nomor, 'password' => 'Awal-Semester-Ganjil']), '/ganti-password', $nomor);
        }
        $this->assertSame('shared', $this->lastAudit('user.imported')['detail']['password_mode']);
        $this->assertStringNotContainsString('Awal-Semester-Ganjil', json_encode($this->lastAudit('user.imported')));
    }

    public function testImporXlsxMenjagaNolDiDepanNomorInduk(): void
    {
        [$http] = $this->admin();
        $t    = '00' . random_int(10000000, 99999999);
        $xlsx = Tabular::xlsx(['nomor_induk', 'nama', 'email', 'global_role', 'apps'], [[$t, 'Nol Depan', '', 'mahasiswa', 'lokakode']]);
        $r    = $this->unggah($http, 'daftar.xlsx', $xlsx);
        $this->assertSame(200, $r->status, substr(strip_tags($r->body), 0, 300));
        $this->assertStringContainsString($t, $r->body);
        $http->post('/admin/impor/jalankan', ['csrf' => $r->input('csrf'), 'token' => $this->token($r), 'mode' => 'acak']);
        $this->assertSame('Nol Depan', $this->row("SELECT nama FROM users WHERE nomor_induk = '{$t}'")['nama']);
    }

    public function testBerkasYangSalahDitolakDenganPenjelasan(): void
    {
        [$http] = $this->admin();
        $this->assertSame(422, $this->unggah($http, 'daftar.txt', "nomor_induk,nama,global_role\na1234,A,mahasiswa\n")->status);
        $this->assertSame(422, $this->unggah($http, 'kosong.csv', "nomor_induk,nama,global_role\n")->status);
        $r = $this->unggah($http, 'kolom.csv', "nim,nama lengkap\n123,Budi\n");
        $this->assertSame(422, $r->status);
        $this->assertStringContainsString('nomor_induk', $r->body, 'pesan menyebut kolom yang kurang');
        $this->assertSame(422, $this->unggah($http, 'palsu.xlsx', 'ini bukan xlsx')->status);
        $this->assertSame(422, $this->unggah($http, 'besar.csv', "nomor_induk,nama,global_role\n" . str_repeat("a12345,Nama,mahasiswa\n", 2001))->status);
    }

    public function testPratinjauMilikSuperadminLainTidakBisaDijalankan(): void
    {
        [$a] = $this->admin();
        [$b] = $this->admin();
        $t   = 'idr' . bin2hex(random_bytes(3));
        $r   = $this->unggah($a, 'a.csv', "nomor_induk,nama,global_role\n{$t}01,Satu,mahasiswa\n");
        $curi = $b->post('/admin/impor/jalankan', ['csrf' => $this->csrf($b, '/admin/impor'), 'token' => $this->token($r), 'mode' => 'acak']);
        $this->assertSame(410, $curi->status);
        $this->assertSame(0, (int) $this->row("SELECT COUNT(*) n FROM users WHERE nomor_induk = '{$t}01'")['n']);
        // Pemiliknya masih bisa menjalankannya.
        $ok = $a->post('/admin/impor/jalankan', ['csrf' => $r->input('csrf'), 'token' => $this->token($r), 'mode' => 'acak']);
        $this->assertMatchesRegularExpression('/data-hasil>1 akun dibuat/', $ok->body);
    }

    public function testNilaiBerbahayaDiCsvTidakMenjadiRumus(): void
    {
        $csv = Tabular::csv(['nama'], [['=HYPERLINK("http://evil.example","klik")'], ['+62812'], ['Biasa']]);
        $this->assertStringContainsString("\"'=HYPERLINK", $csv);
        $this->assertStringContainsString("'+62812", $csv);
        $this->assertStringContainsString("\nBiasa", $csv);
    }
}
