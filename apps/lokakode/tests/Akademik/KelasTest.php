<?php

declare(strict_types=1);

namespace Tests\Akademik;

use Tests\Support\KodeCase;

/**
 * Kelas dan penugasan pengajar (SPEC.md §5), dikelola superadmin dan pengampu.
 */
final class KelasTest extends KodeCase
{
    public function testPengampuMembuatKelasDiMataKuliahnyaPadaTahunAktif(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengampuX']);
        $csrf = $this->csrf($http);

        $this->assertStringContainsString('action="/mata-kuliah/' . $d['matkulX'] . '/kelas"', $http->get('/mata-kuliah/' . $d['matkulX'])->body);

        $r = $http->post('/mata-kuliah/' . $d['matkulX'] . '/kelas', ['csrf' => $csrf, 'name' => 'XC', 'capacity' => '40']);
        $this->assertSame(303, $r->status, $r->body);
        $baris = $this->row("SELECT * FROM classes WHERE name = 'XC'");
        $this->assertSame($d['matkulX'], (int) $baris['course_id']);
        $this->assertSame($d['tahun'], (int) $baris['academic_year_id']);
        $this->assertSame(40, (int) $baris['capacity']);
        $this->assertStringEndsWith('/kelas/' . $baris['id'] . '?pesan=dibuat', (string) $r->header('Location'));

        $audit = $this->row("SELECT * FROM audit_log WHERE action = 'kelas.dibuat'");
        $this->assertSame($d['pengampuX']['id'], $audit['actor_id']);
        $this->assertSame((int) $baris['id'], (int) $audit['class_id']);
    }

    public function testNamaKelasKembarDanKapasitasSalahDitolak(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengampuX']);
        $csrf = $this->csrf($http);
        $url  = '/mata-kuliah/' . $d['matkulX'] . '/kelas';

        foreach ([[['name' => 'XA'], 'Sudah ada kelas dengan nama ini'], [['name' => ''], 'Nama kelas wajib diisi'], [['name' => 'XD', 'capacity' => '0'], 'Kapasitas harus berupa angka'], [['name' => 'XD', 'capacity' => 'banyak'], 'Kapasitas harus berupa angka']] as [$form, $pesan]) {
            $r = $http->post($url, ['csrf' => $csrf] + $form);
            $this->assertSame(303, $r->status);
            $this->assertStringContainsString($pesan, $http->get((string) preg_replace('#^https://[^/]+#', '', (string) $r->header('Location')))->body);
        }
        $this->assertSame(3, $this->hitung('SELECT COUNT(*) FROM classes'));

        // Nama yang sama di mata kuliah lain tidak menghalangi.
        $y = $this->sebagai($d['pengampuY']);
        $this->assertSame(303, $y->post('/mata-kuliah/' . $d['matkulY'] . '/kelas', ['csrf' => $this->csrf($y), 'name' => 'XA'])->status);
        $this->assertSame(4, $this->hitung('SELECT COUNT(*) FROM classes'));
    }

    public function testPengampuMengubahNamaDanKapasitas(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengampuX']);
        $csrf = $this->csrf($http);

        $this->assertStringContainsString('value="XA"', $http->get('/kelas/' . $d['kelasXA'] . '/ubah')->body);
        $r = $http->post('/kelas/' . $d['kelasXA'], ['csrf' => $csrf, 'name' => 'XA Pagi', 'capacity' => '35']);
        $this->assertSame(303, $r->status, $r->body);
        $baris = $this->row("SELECT * FROM classes WHERE id = {$d['kelasXA']}");
        $this->assertSame('XA Pagi', $baris['name']);
        $this->assertSame(35, (int) $baris['capacity']);

        // Kapasitas dikosongkan = tidak dibatasi; nama kelas lain ditolak.
        $http->post('/kelas/' . $d['kelasXA'], ['csrf' => $csrf, 'name' => 'XA Pagi', 'capacity' => '']);
        $this->assertNull($this->row("SELECT capacity FROM classes WHERE id = {$d['kelasXA']}")['capacity']);
        $r = $http->post('/kelas/' . $d['kelasXA'], ['csrf' => $csrf, 'name' => 'XB']);
        $this->assertSame(422, $r->status);
        $this->assertStringContainsString('Sudah ada kelas dengan nama ini', $r->body);
    }

    public function testKelasBerisiTidakBisaDihapusDanJumlahnyaDisebut(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengampuX']);
        $csrf = $this->csrf($http);
        $this->daftar($this->user('mahasiswa', 'Sudah Mundur'), $d['kelasXA'], 'MENGUNDURKAN_DIRI');

        $r = $http->post('/kelas/' . $d['kelasXA'] . '/hapus', ['csrf' => $csrf, 'yakin' => '1']);
        $this->assertSame(409, $r->status);
        $this->assertStringContainsString('masih ada 2 pendaftaran dan 0 riwayat pindah kelas', $r->body);
        $this->assertSame(1, $this->hitung("SELECT COUNT(*) FROM classes WHERE id = {$d['kelasXA']}"));
        $this->assertSame(0, $this->hitung("SELECT COUNT(*) FROM audit_log WHERE action = 'kelas.dihapus'"));
    }

    public function testKelasKosongBisaDihapusSetelahDinyatakanYakin(): void
    {
        $d      = $this->dunia();
        $http   = $this->sebagai($d['pengampuX']);
        $csrf   = $this->csrf($http);
        $kosong = $this->kelas($d['matkulX'], $d['tahun'], 'XKOSONG');
        $this->ajar($kosong, $d['pengajarB']);

        $r = $http->post("/kelas/{$kosong}/hapus", ['csrf' => $csrf]);
        $this->assertSame(422, $r->status, 'tanpa centang yakin');
        $this->assertSame(1, $this->hitung("SELECT COUNT(*) FROM classes WHERE id = {$kosong}"));

        $r = $http->post("/kelas/{$kosong}/hapus", ['csrf' => $csrf, 'yakin' => '1']);
        $this->assertSame(303, $r->status, $r->body);
        $this->assertSame(0, $this->hitung("SELECT COUNT(*) FROM classes WHERE id = {$kosong}"));
        $this->assertSame(0, $this->hitung("SELECT COUNT(*) FROM class_teachers WHERE class_id = {$kosong}"));

        $detail = json_decode((string) $this->row("SELECT detail FROM audit_log WHERE action = 'kelas.dihapus'")['detail'], true);
        $this->assertSame('XKOSONG', $detail['name']);
        $this->assertSame([$d['pengajarB']['id']], $detail['pengajar']);
        $this->assertStringContainsString('Kelas dihapus.', $http->get('/mata-kuliah/' . $d['matkulX'] . '?pesan=kelas-dihapus')->body);
    }

    public function testArsipDanBukaKembali(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengampuX']);
        $csrf = $this->csrf($http);

        $this->assertSame(303, $http->post('/kelas/' . $d['kelasXA'] . '/arsip', ['csrf' => $csrf, 'arsip' => '1'])->status);
        $this->assertNotNull($this->row("SELECT archived_at FROM classes WHERE id = {$d['kelasXA']}")['archived_at']);
        $this->assertStringContainsString('Diarsipkan', $http->get('/kelas/' . $d['kelasXA'])->body);

        $http->post('/kelas/' . $d['kelasXA'] . '/arsip', ['csrf' => $csrf, 'arsip' => '0']);
        $this->assertNull($this->row("SELECT archived_at FROM classes WHERE id = {$d['kelasXA']}")['archived_at']);
        $this->assertSame(1, $this->hitung("SELECT COUNT(*) FROM audit_log WHERE action = 'kelas.diarsipkan'"));
        $this->assertSame(1, $this->hitung("SELECT COUNT(*) FROM audit_log WHERE action = 'kelas.dibuka'"));
    }

    public function testPengampuMenugaskanDanMencabutPengajar(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengampuX']);
        $csrf = $this->csrf($http);
        $baru = $this->user('dosen', 'Dosen Cahyo');
        $c    = $this->sebagai($baru);
        $this->assertSame(404, $c->get('/kelas/' . $d['kelasXA'])->status);

        $r = $http->post('/kelas/' . $d['kelasXA'] . '/pengajar', ['csrf' => $csrf, 'nomor_induk' => $baru['nomor_induk']]);
        $this->assertSame(303, $r->status, $r->body);
        $baris = $this->row("SELECT * FROM class_teachers WHERE class_id = {$d['kelasXA']} AND user_id = '{$baru['id']}'");
        $this->assertSame($d['pengampuX']['id'], $baris['assigned_by']);
        $this->assertSame(200, $c->get('/kelas/' . $d['kelasXA'])->status, 'pengajar baru langsung bisa membuka kelasnya');
        $this->assertSame(404, $c->get('/kelas/' . $d['kelasXB'])->status);
        $this->assertSame(1, $this->hitung("SELECT COUNT(*) FROM audit_log WHERE action = 'pengajar.ditugaskan' AND target_id = '{$baru['id']}'"));

        // Dua kali: tidak ada baris kedua.
        $http->post('/kelas/' . $d['kelasXA'] . '/pengajar', ['csrf' => $csrf, 'nomor_induk' => $baru['nomor_induk']]);
        $this->assertSame(2, $this->hitung("SELECT COUNT(*) FROM class_teachers WHERE class_id = {$d['kelasXA']}"));

        $r = $http->post('/kelas/' . $d['kelasXA'] . '/pengajar/hapus', ['csrf' => $csrf, 'user_id' => $baru['id']]);
        $this->assertSame(303, $r->status);
        $this->assertSame(404, $c->get('/kelas/' . $d['kelasXA'])->status, 'tugas dicabut: langsung tidak bisa membuka');
        $this->assertSame(1, $this->hitung("SELECT COUNT(*) FROM audit_log WHERE action = 'pengajar.dicabut'"));
    }

    public function testPengajarHarusDosenAktif(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengampuX']);
        $csrf = $this->csrf($http);
        $mati = $this->user('dosen', 'Dosen Nonaktif', ['status' => 'suspended']);

        foreach ([['tidak-ada', 'tidak terdaftar di LokaAuth'], [$d['mhsB']['nomor_induk'], 'harus akun dengan peran dosen'], [$mati['nomor_induk'], 'sedang tidak aktif']] as [$nomor, $pesan]) {
            $r = $http->post('/kelas/' . $d['kelasXA'] . '/pengajar', ['csrf' => $csrf, 'nomor_induk' => $nomor]);
            $this->assertSame(422, $r->status, $nomor);
            $this->assertStringContainsString($pesan, $r->body);
        }
        $this->assertSame(1, $this->hitung("SELECT COUNT(*) FROM class_teachers WHERE class_id = {$d['kelasXA']}"));
    }

    public function testPengajarTidakMelihatTombolKelola(): void
    {
        $d = $this->dunia();
        $a = $this->sebagai($d['pengajarA'])->get('/kelas/' . $d['kelasXA'])->body;
        $this->assertStringNotContainsString('/kelas/' . $d['kelasXA'] . '/ubah', $a);
        $this->assertStringNotContainsString('/pengajar/hapus', $a);
        $this->assertStringContainsString('Perubahan kelas dan pendaftaran dilakukan oleh pengampu', $a);
        $this->assertStringNotContainsString('/kelas"', $this->sebagai($d['pengajarA'])->get('/mata-kuliah/' . $d['matkulX'])->body);

        $p = $this->sebagai($d['pengampuX'])->get('/kelas/' . $d['kelasXA'])->body;
        $this->assertStringContainsString('/kelas/' . $d['kelasXA'] . '/ubah', $p);
    }

    public function testTahunDiarsipkanHanyaBisaDibacaDosen(): void
    {
        $d    = $this->dunia();
        $lama = $this->tahun('2025/2026 Genap', 'archived', '2026-02-01', '2026-07-31');
        $kl   = $this->kelas($d['matkulX'], $lama, 'XLAMA');
        $http = $this->sebagai($d['pengampuX']);
        $csrf = $this->csrf($http);

        $lihat = $http->get("/kelas/{$kl}");
        $this->assertSame(200, $lihat->status, 'tetap bisa dibaca');
        $this->assertStringNotContainsString("/kelas/{$kl}/ubah", $lihat->body);

        $r = $http->post("/kelas/{$kl}", ['csrf' => $csrf, 'name' => 'DIUBAH']);
        $this->assertSame(403, $r->status);
        $this->assertStringContainsString('sudah diarsipkan', $r->body);
        $this->assertSame(403, $http->post("/kelas/{$kl}/hapus", ['csrf' => $csrf, 'yakin' => '1'])->status);
        $this->assertSame('XLAMA', $this->row("SELECT name FROM classes WHERE id = {$kl}")['name']);

        // Kelas baru dari pengampu selalu masuk tahun aktif, walau kolom tahun dipalsukan.
        $http->post('/mata-kuliah/' . $d['matkulX'] . '/kelas', ['csrf' => $csrf, 'name' => 'XPALSU', 'tahun' => (string) $lama]);
        $this->assertSame($d['tahun'], (int) $this->row("SELECT academic_year_id FROM classes WHERE name = 'XPALSU'")['academic_year_id']);

        // Superadmin tetap boleh membetulkan data tahun lama.
        $admin = $this->sebagai($d['superadmin']);
        $this->assertSame(303, $admin->post("/kelas/{$kl}", ['csrf' => $this->csrf($admin), 'name' => 'XLAMA-2'])->status);
    }
}
