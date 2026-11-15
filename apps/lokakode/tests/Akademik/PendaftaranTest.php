<?php

declare(strict_types=1);

namespace Tests\Akademik;

use App\Libraries\Pendaftaran;
use Tests\Support\KodeCase;

/**
 * Pendaftaran mahasiswa (SPEC.md §5): satu per satu atau CSV, dengan pratinjau.
 */
final class PendaftaranTest extends KodeCase
{
    public function testMembacaNomorDariTeksDanCsv(): void
    {
        $teks = "\xEF\xBB\xBFnomor_induk;nama\r\n2301001;Ani\r\n\"2301002\",Budi\n  2301001  \n\nini bukan nomor!\nab\n2301003\tCici";
        [$sah, $salah] = Pendaftaran::bacaNomor($teks);
        $this->assertSame(['2301001', '2301002', '2301003'], $sah);
        $this->assertSame(['ini bukan nomor!', 'ab'], $salah);
    }

    public function testPratinjauMenggolongkanTanpaMenyimpan(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengampuX']);
        $csrf = $this->csrf($http);

        $baru   = $this->user('mahasiswa', 'Mahasiswa Baru');
        $tanpa  = $this->user('mahasiswa', 'Tanpa Akses', ['apps' => ['portal']]);
        $mati   = $this->user('mahasiswa', 'Akun Ditangguhkan', ['status' => 'suspended']);
        $mundur = $this->user('mahasiswa', 'Pernah Mundur');
        $lulus  = $this->user('mahasiswa', 'Sudah Lulus');
        $this->daftar($mundur, $d['kelasXB'], 'MENGUNDURKAN_DIRI');
        $this->daftar($lulus, $d['kelasXB'], 'LULUS');
        $sebelum = $this->hitung('SELECT COUNT(*) FROM enrollments');

        $nomor = implode("\n", [
            $baru['nomor_induk'], $tanpa['nomor_induk'], $mati['nomor_induk'], $mundur['nomor_induk'], $lulus['nomor_induk'],
            $d['mhsA']['nomor_induk'],      // sudah di kelas ini
            $d['mhsB']['nomor_induk'],      // di kelas lain mata kuliah yang sama
            $d['mhsY']['nomor_induk'],      // di mata kuliah lain: boleh didaftarkan di sini
            $d['pengajarB']['nomor_induk'], // dosen
            'tidak-terdaftar-99',
            'bentuk salah!',
        ]);
        $r = $http->post('/kelas/' . $d['kelasXA'] . '/daftar', ['csrf' => $csrf, 'nomor' => $nomor]);
        $this->assertSame(200, $r->status, $r->body);

        $bagian = function (string $g) use ($r): string {
            $this->assertSame(1, preg_match('/<h2 data-golongan="' . $g . '">(.*?)(?=<h2 data-golongan|<form method="post" action="\/kelas\/\d+\/daftar\/jalankan")/s', $r->body, $m), "golongan {$g} tidak tampil");

            return $m[1];
        };
        $siap = $bagian('siap');
        foreach ([$baru, $tanpa, $mati, $d['mhsY']] as $u) {
            $this->assertStringContainsString($u['nomor_induk'], $siap);
        }
        $this->assertStringContainsString('(4)', $siap);
        $this->assertMatchesRegularExpression('/Tanpa Akses.*?Belum diberi akses LokaKode/s', $siap);
        $this->assertMatchesRegularExpression('/Akun Ditangguhkan.*?Akun tidak aktif/s', $siap);
        $this->assertStringContainsString($mundur['nomor_induk'], $bagian('aktifkanLagi'));
        $this->assertStringContainsString($lulus['nomor_induk'], $bagian('lulus'));
        $this->assertStringContainsString($d['mhsA']['nomor_induk'], $bagian('sudah'));
        $lain = $bagian('kelasLain');
        $this->assertStringContainsString($d['mhsB']['nomor_induk'], $lain);
        $this->assertStringContainsString('kelas XB', $lain);
        $this->assertStringContainsString($d['pengajarB']['nomor_induk'], $bagian('bukanMahasiswa'));
        $tidakAda = $bagian('tidakAda');
        $this->assertStringContainsString('tidak-terdaftar-99', $tidakAda);
        $this->assertStringContainsString('https://auth.lokandita.com/', $tidakAda, 'tautan ke LokaAuth');
        $this->assertStringContainsString('bentuk salah!', $bagian('tidakSah'));

        // Tombol menyebut jumlahnya; hanya golongan yang dikerjakan yang dibawa ke langkah kedua.
        $this->assertStringContainsString('Daftarkan 5 mahasiswa', $r->body);
        $this->assertSame(5, substr_count($r->body, 'name="nomor[]"'));
        $this->assertStringNotContainsString('name="nomor[]" value="' . $d['mhsB']['nomor_induk'] . '"', $r->body);

        $this->assertSame($sebelum, $this->hitung('SELECT COUNT(*) FROM enrollments'), 'pratinjau tidak menyimpan apa pun');
        $this->assertSame(0, $this->hitung('SELECT COUNT(*) FROM audit_log'));
    }

    public function testMenjalankanMendaftarkanDanMengauditSetiapMahasiswa(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengampuX']);
        $csrf = $this->csrf($http);
        $m1   = $this->user('mahasiswa', 'Mahasiswa Satu');
        $m2   = $this->user('mahasiswa', 'Mahasiswa Dua');

        $r = $http->post('/kelas/' . $d['kelasXA'] . '/daftar/jalankan', ['csrf' => $csrf, 'nomor' => [$m1['nomor_induk'], $m2['nomor_induk']]]);
        $this->assertSame(303, $r->status, $r->body);
        $this->assertStringEndsWith('/kelas/' . $d['kelasXA'] . '?pesan=didaftarkan&n=2', (string) $r->header('Location'));

        foreach ([$m1, $m2] as $m) {
            $e = $this->row("SELECT * FROM enrollments WHERE user_id = '{$m['id']}'");
            $this->assertSame('AKTIF', $e['status']);
            $this->assertSame($d['kelasXA'], (int) $e['class_id']);
            $this->assertSame($d['matkulX'], (int) $e['course_id']);
            $this->assertSame($d['pengampuX']['id'], $e['created_by']);
            $this->assertSame(1, $this->hitung("SELECT COUNT(*) FROM audit_log WHERE action = 'pendaftaran.dibuat' AND target_id = '{$m['id']}' AND class_id = {$d['kelasXA']}"));
        }

        $kelas = $http->get('/kelas/' . $d['kelasXA'] . '?pesan=didaftarkan&n=2');
        $this->assertStringContainsString('2 mahasiswa didaftarkan ke kelas ini.', $kelas->body);
        $this->assertStringContainsString('Mahasiswa Satu', $kelas->body);

        // Mahasiswa itu kini melihat kelasnya di beranda.
        $this->assertStringContainsString('UJX101', $this->sebagai($m1)->get('/beranda')->body);
    }

    public function testLangkahKeduaTidakMempercayaiIsiPratinjau(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengampuX']);
        $csrf = $this->csrf($http);

        // Peramban mengirim nomor yang di pratinjau TIDAK termasuk "akan didaftarkan".
        $r = $http->post('/kelas/' . $d['kelasXA'] . '/daftar/jalankan', ['csrf' => $csrf, 'nomor' => [
            $d['mhsB']['nomor_induk'],      // aktif di kelas XB
            $d['pengajarB']['nomor_induk'], // dosen
            'tidak-terdaftar-99',
            $d['mhsA']['nomor_induk'],      // sudah di kelas ini
        ]]);
        $this->assertSame(303, $r->status);
        $this->assertStringEndsWith('n=0', (string) $r->header('Location'));
        $this->assertSame(3, $this->hitung('SELECT COUNT(*) FROM enrollments'));
        $this->assertSame($d['kelasXB'], (int) $this->row("SELECT class_id FROM enrollments WHERE id = {$d['daftarB']}")['class_id'], 'mahasiswa kelas lain tidak ikut terpindah');
        $this->assertSame(0, $this->hitung("SELECT COUNT(*) FROM enrollments WHERE user_id = '{$d['pengajarB']['id']}'"));
    }

    public function testUnggahCsv(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengampuX']);
        $csrf = $this->csrf($http);
        $m1   = $this->user('mahasiswa', 'Dari Berkas Satu');
        $m2   = $this->user('mahasiswa', 'Dari Berkas Dua');

        $csv = "nomor_induk,nama\n{$m1['nomor_induk']},x\n{$m2['nomor_induk']},y\n";
        $r   = $http->upload('/kelas/' . $d['kelasXA'] . '/daftar', ['csrf' => $csrf, 'nomor' => ''], 'berkas', 'kelas-xa.csv', $csv);
        $this->assertSame(200, $r->status, $r->body);
        $this->assertStringContainsString('Dari Berkas Satu', $r->body);
        $this->assertStringContainsString('Daftarkan 2 mahasiswa', $r->body);

        // Bukan .csv: ditolak dengan pesan, tidak ada pratinjau.
        $r = $http->upload('/kelas/' . $d['kelasXA'] . '/daftar', ['csrf' => $csrf, 'nomor' => ''], 'berkas', 'kelas.xlsx', 'PK');
        $this->assertSame(303, $r->status);
        $this->assertStringContainsString('Berkas harus berformat .csv', $http->get('/kelas/' . $d['kelasXA'] . '?pesan=berkasCsv')->body);

        // Kosong sama sekali.
        $r = $http->post('/kelas/' . $d['kelasXA'] . '/daftar', ['csrf' => $csrf, 'nomor' => "  \n"]);
        $this->assertStringEndsWith('pesan=nomorKosong', (string) $r->header('Location'));
    }

    public function testMengundurkanDiriLaluDiaktifkanLagiTetapSatuBaris(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengampuX']);
        $csrf = $this->csrf($http);

        $r = $http->post('/kelas/' . $d['kelasXA'] . '/mundur', ['csrf' => $csrf, 'ids' => [$d['daftarA']]]);
        $this->assertSame(303, $r->status, $r->body);
        $this->assertStringEndsWith('pesan=mundur&n=1', (string) $r->header('Location'));
        $this->assertSame('MENGUNDURKAN_DIRI', $this->row("SELECT status FROM enrollments WHERE id = {$d['daftarA']}")['status']);
        $this->assertSame(1, $this->hitung("SELECT COUNT(*) FROM audit_log WHERE action = 'pendaftaran.mundur' AND target_id = '{$d['mhsA']['id']}'"));
        $this->assertStringContainsString('data-ringkas="mahasiswa">1<', $this->sebagai($d['pengampuX'])->get('/beranda')->body, 'tidak lagi dihitung');

        // Didaftarkan lagi, kali ini ke kelas XB: baris yang sama, aktif lagi, perpindahannya tercatat.
        $r = $http->post('/kelas/' . $d['kelasXB'] . '/daftar/jalankan', ['csrf' => $csrf, 'nomor' => [$d['mhsA']['nomor_induk']]]);
        $this->assertStringEndsWith('n=1', (string) $r->header('Location'));
        $this->assertSame(1, $this->hitung("SELECT COUNT(*) FROM enrollments WHERE user_id = '{$d['mhsA']['id']}'"));
        $e = $this->row("SELECT * FROM enrollments WHERE id = {$d['daftarA']}");
        $this->assertSame('AKTIF', $e['status']);
        $this->assertSame($d['kelasXB'], (int) $e['class_id']);
        $pindah = $this->row("SELECT * FROM enrollment_moves WHERE enrollment_id = {$d['daftarA']}");
        $this->assertSame([$d['kelasXA'], $d['kelasXB']], [(int) $pindah['from_class_id'], (int) $pindah['to_class_id']]);
    }

    public function testPendaftaranKelasLainTidakBisaDiubahLewatIdDiForm(): void
    {
        $d    = $this->dunia();
        // Pengampu Y mengelola kelas YA, lalu menyelipkan id pendaftaran kelas XA dan XB.
        $http = $this->sebagai($d['pengampuY']);
        $r    = $http->post('/kelas/' . $d['kelasYA'] . '/mundur', ['csrf' => $this->csrf($http), 'ids' => [$d['daftarA'], $d['daftarB']]]);
        $this->assertSame(303, $r->status);
        $this->assertStringContainsString('pesan=tidak-ada-pilihan', (string) $r->header('Location'));
        $this->assertSame('AKTIF', $this->row("SELECT status FROM enrollments WHERE id = {$d['daftarA']}")['status']);
        $this->assertSame('AKTIF', $this->row("SELECT status FROM enrollments WHERE id = {$d['daftarB']}")['status']);
        $this->assertSame(0, $this->hitung("SELECT COUNT(*) FROM audit_log WHERE action = 'pendaftaran.mundur'"));

        // Di dalam satu mata kuliah pun: id pendaftaran kelas XB lewat URL kelas XA tidak berlaku.
        $x = $this->sebagai($d['pengampuX']);
        $x->post('/kelas/' . $d['kelasXA'] . '/mundur', ['csrf' => $this->csrf($x), 'ids' => [$d['daftarB']]]);
        $this->assertSame('AKTIF', $this->row("SELECT status FROM enrollments WHERE id = {$d['daftarB']}")['status']);
    }

    public function testKelasDiarsipkanTidakMenerimaMahasiswaDanKapasitasHanyaMemperingatkan(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengampuX']);
        $csrf = $this->csrf($http);
        $m1   = $this->user('mahasiswa', 'Mahasiswa Satu');
        $m2   = $this->user('mahasiswa', 'Mahasiswa Dua');

        $this->db->query("UPDATE classes SET capacity = 2 WHERE id = {$d['kelasXA']}");
        $r = $http->post('/kelas/' . $d['kelasXA'] . '/daftar', ['csrf' => $csrf, 'nomor' => $m1['nomor_induk'] . "\n" . $m2['nomor_induk']]);
        $this->assertStringContainsString('akan berisi 3 mahasiswa aktif, melebihi kapasitasnya (2)', $r->body);
        $this->assertStringContainsString('Daftarkan 2 mahasiswa', $r->body);

        $this->db->query("UPDATE classes SET archived_at = UTC_TIMESTAMP() WHERE id = {$d['kelasXA']}");
        $r = $http->post('/kelas/' . $d['kelasXA'] . '/daftar/jalankan', ['csrf' => $csrf, 'nomor' => [$m1['nomor_induk']]]);
        $this->assertStringEndsWith('pesan=kelasArsip', (string) $r->header('Location'));
        $this->assertSame(0, $this->hitung("SELECT COUNT(*) FROM enrollments WHERE user_id = '{$m1['id']}'"));
        $this->assertStringNotContainsString('action="/kelas/' . $d['kelasXA'] . '/daftar"', $http->get('/kelas/' . $d['kelasXA'])->body);
    }
}
