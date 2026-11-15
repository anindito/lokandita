<?php

declare(strict_types=1);

namespace Tests\Akademik;

use Tests\Support\KodeCase;

/**
 * Tahun ajaran dan mata kuliah (SPEC.md §5), dikelola superadmin.
 */
final class TahunDanMatkulTest extends KodeCase
{
    public function testSuperadminMembuatTahunAjaranDalamKeadaanDiarsipkan(): void
    {
        $admin = $this->user('superadmin', 'Admin Uji');
        $http  = $this->sebagai($admin);
        $csrf  = $this->csrf($http);

        $r = $http->post('/tahun-ajaran', ['csrf' => $csrf, 'label' => '2026/2027 Ganjil', 'starts_on' => '2026-08-01', 'ends_on' => '2027-01-31']);
        $this->assertSame(303, $r->status, $r->body);
        $baris = $this->row("SELECT * FROM academic_years WHERE label = '2026/2027 Ganjil'");
        $this->assertSame('archived', $baris['status']);
        $this->assertSame('2026-08-01', $baris['starts_on']);

        $audit = $this->row("SELECT * FROM audit_log WHERE action = 'tahun.dibuat'");
        $this->assertSame($admin['id'], $audit['actor_id']);
        $this->assertSame((string) $baris['id'], $audit['target_id']);

        $daftar = $http->get('/tahun-ajaran?pesan=disimpan');
        $this->assertStringContainsString('2026/2027 Ganjil', $daftar->body);
        $this->assertStringContainsString('Perubahan disimpan.', $daftar->body);
    }

    public function testIsianTahunAjaranYangSalahDitolakDenganPesan(): void
    {
        $http = $this->sebagai($this->user('superadmin', 'Admin Uji'));
        $csrf = $this->csrf($http);
        $this->tahun('Sudah Ada', 'archived');

        $kasus = [
            [['label' => 'ab', 'starts_on' => '2026-08-01', 'ends_on' => '2027-01-31'], 'harus 3 sampai 40 karakter'],
            [['label' => 'Sudah Ada', 'starts_on' => '2026-08-01', 'ends_on' => '2027-01-31'], 'Sudah ada tahun ajaran dengan nama ini.'],
            [['label' => 'Tanggal Aneh', 'starts_on' => '2026-02-30', 'ends_on' => '2027-01-31'], 'Isi tanggal yang benar.'],
            [['label' => 'Terbalik', 'starts_on' => '2027-01-31', 'ends_on' => '2026-08-01'], 'Tanggal selesai harus sesudah tanggal mulai.'],
        ];
        foreach ($kasus as [$form, $pesan]) {
            $r = $http->post('/tahun-ajaran', ['csrf' => $csrf] + $form);
            $this->assertSame(422, $r->status, $form['label']);
            $this->assertStringContainsString($pesan, $r->body, $form['label']);
            // Isian dikembalikan ke form.
            $this->assertStringContainsString('value="' . $form['label'] . '"', $r->body);
        }
        $this->assertSame(1, $this->hitung('SELECT COUNT(*) FROM academic_years'));
        $this->assertSame(0, $this->hitung('SELECT COUNT(*) FROM audit_log'));
    }

    public function testMengaktifkanSatuTahunMengarsipkanYangLain(): void
    {
        $admin = $this->user('superadmin', 'Admin Uji');
        $http  = $this->sebagai($admin);
        $csrf  = $this->csrf($http);
        $lama  = $this->tahun('2025/2026 Genap', 'active', '2026-02-01', '2026-07-31');
        $baru  = $this->tahun('2026/2027 Ganjil', 'archived');

        $r = $http->post("/tahun-ajaran/{$baru}/aktifkan", ['csrf' => $csrf]);
        $this->assertSame(303, $r->status, $r->body);
        $this->assertSame('active', $this->row("SELECT status FROM academic_years WHERE id = {$baru}")['status']);
        $this->assertSame('archived', $this->row("SELECT status FROM academic_years WHERE id = {$lama}")['status']);
        $this->assertSame(1, $this->hitung("SELECT COUNT(*) FROM academic_years WHERE status = 'active'"));

        $audit  = $this->row("SELECT * FROM audit_log WHERE action = 'tahun.diaktifkan'");
        $detail = json_decode((string) $audit['detail'], true);
        $this->assertSame($lama, (int) $detail['diarsipkan_id']);

        // Bisa dibalik; mengaktifkan yang sudah aktif tidak mengubah apa pun.
        $http->post("/tahun-ajaran/{$lama}/aktifkan", ['csrf' => $csrf]);
        $http->post("/tahun-ajaran/{$lama}/aktifkan", ['csrf' => $csrf]);
        $this->assertSame('active', $this->row("SELECT status FROM academic_years WHERE id = {$lama}")['status']);
        $this->assertSame(1, $this->hitung("SELECT COUNT(*) FROM academic_years WHERE status = 'active'"));
        $this->assertSame(2, $this->hitung("SELECT COUNT(*) FROM audit_log WHERE action = 'tahun.diaktifkan'"));

        $this->assertSame(404, $http->post('/tahun-ajaran/999999/aktifkan', ['csrf' => $csrf])->status);
    }

    public function testTahunAjaranBisaDiubah(): void
    {
        $http = $this->sebagai($this->user('superadmin', 'Admin Uji'));
        $csrf = $this->csrf($http);
        $id   = $this->tahun('Salah Ketik', 'active');

        $this->assertStringContainsString('value="Salah Ketik"', $http->get("/tahun-ajaran/{$id}")->body);
        $r = $http->post("/tahun-ajaran/{$id}", ['csrf' => $csrf, 'label' => '2026/2027 Ganjil', 'starts_on' => '2026-08-10', 'ends_on' => '2027-01-31']);
        $this->assertSame(303, $r->status, $r->body);
        $baris = $this->row("SELECT * FROM academic_years WHERE id = {$id}");
        $this->assertSame('2026/2027 Ganjil', $baris['label']);
        $this->assertSame('2026-08-10', $baris['starts_on']);
        $this->assertSame('active', $baris['status'], 'status tidak berubah lewat form ubah');
        // Nama sendiri boleh tetap.
        $this->assertSame(303, $http->post("/tahun-ajaran/{$id}", ['csrf' => $csrf, 'label' => '2026/2027 Ganjil', 'starts_on' => '2026-08-10', 'ends_on' => '2027-01-31'])->status);
    }

    public function testSuperadminMembuatMataKuliahDenganPengampuDosen(): void
    {
        $admin = $this->user('superadmin', 'Admin Uji');
        $dosen = $this->user('dosen', 'Dosen Pengampu');
        $http  = $this->sebagai($admin);
        $csrf  = $this->csrf($http);

        $r = $http->post('/mata-kuliah', ['csrf' => $csrf, 'code' => 'ddp101', 'name' => 'Dasar-Dasar Pemrograman', 'pengampu' => $dosen['nomor_induk']]);
        $this->assertSame(303, $r->status, $r->body);
        $baris = $this->row("SELECT * FROM courses WHERE code = 'DDP101'");
        $this->assertNotNull($baris, 'kode disimpan dengan huruf besar');
        $this->assertSame($dosen['id'], $baris['owner_id']);
        $this->assertStringEndsWith('/mata-kuliah/' . $baris['id'] . '?pesan=disimpan', (string) $r->header('Location'));
        $this->assertSame(1, $this->hitung("SELECT COUNT(*) FROM audit_log WHERE action = 'matkul.dibuat' AND course_id = {$baris['id']}"));

        // Dosen itu sekarang pengampu: melihat mata kuliahnya, tetapi tidak boleh mengubahnya.
        $pengampu = $this->sebagai($dosen);
        $this->assertSame(200, $pengampu->get('/mata-kuliah/' . $baris['id'])->status);
        $this->assertStringContainsString('Anda pengampu', $pengampu->get('/mata-kuliah')->body);
        $this->assertSame(403, $pengampu->get('/mata-kuliah/' . $baris['id'] . '/ubah')->status);
    }

    public function testPengampuHarusDosenAktifYangTerdaftar(): void
    {
        $http = $this->sebagai($this->user('superadmin', 'Admin Uji'));
        $csrf = $this->csrf($http);
        $mhs  = $this->user('mahasiswa', 'Bukan Dosen');
        $mati = $this->user('dosen', 'Dosen Nonaktif', ['status' => 'disabled']);
        $ok   = $this->user('dosen', 'Dosen Baik');
        $this->matkul('ADA100', 'Sudah Ada', $ok);

        $kasus = [
            [['code' => 'UJI100', 'name' => 'Uji', 'pengampu' => 'tidak-terdaftar'], 'tidak terdaftar di LokaAuth'],
            [['code' => 'UJI100', 'name' => 'Uji', 'pengampu' => $mhs['nomor_induk']], 'harus akun dengan peran dosen'],
            [['code' => 'UJI100', 'name' => 'Uji', 'pengampu' => $mati['nomor_induk']], 'sedang tidak aktif'],
            [['code' => 'ADA100', 'name' => 'Uji', 'pengampu' => $ok['nomor_induk']], 'Sudah ada mata kuliah dengan kode ini.'],
            [['code' => 'kode salah!', 'name' => 'Uji', 'pengampu' => $ok['nomor_induk']], 'Kode berisi 2 sampai 20'],
            [['code' => 'UJI100', 'name' => 'x', 'pengampu' => $ok['nomor_induk']], 'Nama harus 3 sampai 190 karakter.'],
        ];
        foreach ($kasus as [$form, $pesan]) {
            $r = $http->post('/mata-kuliah', ['csrf' => $csrf] + $form);
            $this->assertSame(422, $r->status, $pesan);
            $this->assertStringContainsString($pesan, $r->body);
        }
        $this->assertSame(1, $this->hitung('SELECT COUNT(*) FROM courses'));
    }

    public function testMenggantiPengampuMemindahkanHakKelola(): void
    {
        $d     = $this->dunia();
        $http  = $this->sebagai($d['superadmin']);
        $csrf  = $this->csrf($http);
        $lama  = $this->sebagai($d['pengampuX']);
        $baru  = $this->sebagai($d['pengampuY']);
        $this->assertSame(404, $baru->get('/kelas/' . $d['kelasXA'])->status);

        $form = $http->get('/mata-kuliah/' . $d['matkulX'] . '/ubah');
        $this->assertStringContainsString('value="' . $d['pengampuX']['nomor_induk'] . '"', $form->body);

        $r = $http->post('/mata-kuliah/' . $d['matkulX'], ['csrf' => $csrf, 'code' => 'UJX101', 'name' => 'Uji Pemrograman X', 'pengampu' => $d['pengampuY']['nomor_induk']]);
        $this->assertSame(303, $r->status, $r->body);

        $this->assertSame(200, $baru->get('/kelas/' . $d['kelasXA'])->status, 'pengampu baru langsung melihat kelasnya');
        $this->assertSame(404, $lama->get('/kelas/' . $d['kelasXA'])->status, 'pengampu lama langsung kehilangan akses');

        $detail = json_decode((string) $this->row("SELECT detail FROM audit_log WHERE action = 'matkul.diubah'")['detail'], true);
        $this->assertSame($d['pengampuX']['id'], $detail['sebelum']['owner_id']);
        $this->assertSame($d['pengampuY']['id'], $detail['sesudah']['owner_id']);
    }

    public function testPostTanpaCsrfDitolak(): void
    {
        $http = $this->sebagai($this->user('superadmin', 'Admin Uji'));
        $this->assertSame(403, $http->post('/tahun-ajaran', ['label' => 'Tanpa CSRF', 'starts_on' => '2026-08-01', 'ends_on' => '2027-01-31'])->status);
        $this->assertSame(0, $this->hitung('SELECT COUNT(*) FROM academic_years'));
    }
}
