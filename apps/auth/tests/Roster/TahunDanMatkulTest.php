<?php

declare(strict_types=1);

namespace Tests\Roster;

use Lokandita\Auth\Testing\RosterFixtures;
use Tests\Support\RosterCase;

/**
 * Tahun ajaran dan mata kuliah di Akun Lokandita (SPEC.md §5).
 */
final class TahunDanMatkulTest extends RosterCase
{
    public function testTahunDibuatDiarsipkanLaluDiaktifkanDanYangLamaDiarsipkan(): void
    {
        [$http, $admin] = $this->admin();
        $lama = RosterFixtures::tahun($this->roster, ['label' => '2025/2026 Genap', 'starts_on' => '2026-02-01', 'ends_on' => '2026-07-31']);

        $this->kirim($http, '/admin/tahun-ajaran', ['label' => '2026/2027 Ganjil', 'starts_on' => '2026-08-01', 'ends_on' => '2027-01-31']);
        $this->assertRedirect($http, '/admin/tahun-ajaran?pesan=disimpan');
        $baru = $this->r("SELECT * FROM academic_years WHERE label = '2026/2027 Ganjil'");
        $this->assertSame('archived', $baru['status']);

        $audit = $this->auditRoster('tahun.dibuat');
        $this->assertSame($admin['id'], $audit['actor_id']);
        $this->assertSame((string) $baru['id'], $audit['target_id']);
        $this->assertSame('academic_year', $audit['detail']['target_tipe']);

        $this->kirim($http, "/admin/tahun-ajaran/{$baru['id']}/aktifkan");
        $this->assertRedirect($http, '/admin/tahun-ajaran?pesan=diaktifkan');
        $this->assertSame('active', $this->r("SELECT status FROM academic_years WHERE id = {$baru['id']}")['status']);
        $this->assertSame('archived', $this->r("SELECT status FROM academic_years WHERE id = {$lama}")['status']);
        $this->assertSame($lama, (int) $this->auditRoster('tahun.diaktifkan')['detail']['diarsipkan_id']);

        $this->kirim($http, "/admin/tahun-ajaran/{$baru['id']}", ['label' => '2026/2027 Gasal', 'starts_on' => '2026-08-03', 'ends_on' => '2027-01-31']);
        $this->assertRedirect($http, '/admin/tahun-ajaran?pesan=disimpan');
        $this->assertSame('2026/2027 Ganjil', $this->auditRoster('tahun.diubah')['detail']['sebelum']['label']);
        $this->assertStringContainsString('2026/2027 Gasal', $http->get('/admin/tahun-ajaran')->body);
    }

    public function testTahunYangSalahDitolakDenganPesanDanTidakDisimpan(): void
    {
        [$http] = $this->admin();
        RosterFixtures::tahun($this->roster, ['label' => 'Sudah Ada']);
        $audit = $this->jumlahAuditRoster();

        foreach ([
            ['label' => 'ab', 'starts_on' => '2026-08-01', 'ends_on' => '2027-01-31'],
            ['label' => 'Sudah Ada', 'starts_on' => '2026-08-01', 'ends_on' => '2027-01-31'],
            ['label' => 'Tanggal Salah', 'starts_on' => '2026-02-30', 'ends_on' => '2027-01-31'],
            ['label' => 'Terbalik', 'starts_on' => '2027-01-31', 'ends_on' => '2026-08-01'],
        ] as $isi) {
            $this->assertSame(422, $this->kirim($http, '/admin/tahun-ajaran', $isi)->status, $isi['label']);
            $this->assertStringContainsString('galat-kolom', $http->body);
        }
        $this->assertSame(1, $this->n('academic_years'));
        $this->assertSame($audit, $this->jumlahAuditRoster());
    }

    public function testMatkulDibuatDanDiubahPengampuHarusDosenAktif(): void
    {
        [$http, $admin] = $this->admin();
        $dosen = $this->user(['global_role' => 'dosen', 'nama' => 'Dosen Satu']);
        $dua   = $this->user(['global_role' => 'dosen', 'nama' => 'Dosen Dua']);
        $mati  = $this->user(['global_role' => 'dosen', 'status' => 'disabled']);
        $mhs   = $this->user(['global_role' => 'mahasiswa']);

        foreach ([$mhs['nomor_induk'], $mati['nomor_induk'], 'tidak-ada-123', ''] as $nomor) {
            $this->assertSame(422, $this->kirim($http, '/admin/mata-kuliah', ['code' => 'PBO301', 'name' => 'Pemrograman', 'pengampu' => $nomor])->status, $nomor);
        }
        $this->assertSame(422, $this->kirim($http, '/admin/mata-kuliah', ['code' => 'x', 'name' => 'Pemrograman', 'pengampu' => $dosen['nomor_induk']])->status);
        $this->assertSame(0, $this->n('courses'));

        $this->kirim($http, '/admin/mata-kuliah', ['code' => 'pbo301', 'name' => 'Pemrograman Berorientasi Obyek', 'pengampu' => $dosen['nomor_induk']]);
        $m = $this->r("SELECT * FROM courses WHERE code = 'PBO301'");
        $this->assertNotNull($m);
        $this->assertRedirect($http, "/admin/mata-kuliah/{$m['id']}?pesan=disimpan");
        $this->assertSame($dosen['id'], $m['owner_id']);
        $this->assertSame($admin['id'], $this->auditRoster('matkul.dibuat')['actor_id']);
        $this->assertSame((int) $m['id'], $this->auditRoster('matkul.dibuat')['detail']['course_id']);

        // Kode yang sama ditolak.
        $this->assertSame(422, $this->kirim($http, '/admin/mata-kuliah', ['code' => 'PBO301', 'name' => 'Kembaran', 'pengampu' => $dosen['nomor_induk']])->status);

        $this->kirim($http, "/admin/mata-kuliah/{$m['id']}", ['code' => 'PBO301', 'name' => 'PBO', 'pengampu' => $dua['nomor_induk']]);
        $this->assertRedirect($http, "/admin/mata-kuliah/{$m['id']}?pesan=disimpan");
        $this->assertSame($dua['id'], $this->r("SELECT owner_id FROM courses WHERE id = {$m['id']}")['owner_id']);
        $this->assertSame($dosen['id'], $this->auditRoster('matkul.diubah')['detail']['sebelum']['owner_id']);

        $halaman = $http->get('/admin/mata-kuliah')->body;
        $this->assertStringContainsString('PBO301', $halaman);
        $this->assertStringContainsString('Dosen Dua', $halaman);
    }

    public function testTahunBaruMenyalinKelasDanPengajarTanpaMahasiswa(): void
    {
        [$http] = $this->admin();
        $d      = $this->dasar();
        $dosen  = $this->user(['global_role' => 'dosen']);
        $mhs    = $this->user();
        RosterFixtures::pengajar($this->roster, $d['a'], $dosen['id']);
        RosterFixtures::daftar($this->roster, $d['a'], $mhs['id']);
        $arsip = RosterFixtures::kelas($this->roster, $d['matkul'], $d['tahun'], ['name' => 'Lama', 'archived_at' => gmdate('Y-m-d H:i:s')]);
        $isi   = ['label' => '2026/2027 Genap', 'starts_on' => '2027-02-01', 'ends_on' => '2027-07-31'];

        // Ringkasan dulu: tidak ada yang disimpan.
        $r = $this->kirim($http, '/admin/tahun-ajaran/baru', $isi);
        $this->assertSame(200, $r->status);
        $this->assertSame(1, preg_match('/data-ringkas="salin"[^>]*>([^<]+)</', $r->body, $m));
        $this->assertStringContainsString('2 kelas dan 1 penugasan', $m[1]);
        $this->assertStringContainsString('data-ringkas="masih-aktif"', $r->body);
        $this->assertSame(1, $this->n('academic_years'));

        $this->kirim($http, '/admin/tahun-ajaran/baru/jalankan', $isi);
        $this->assertRedirect($http, '/admin/tahun-ajaran?pesan=tahun-baru&n=2');
        $baru = $this->r("SELECT * FROM academic_years WHERE label = '2026/2027 Genap'");
        $this->assertSame('active', $baru['status']);
        $this->assertSame('archived', $this->r("SELECT status FROM academic_years WHERE id = {$d['tahun']}")['status']);
        $this->assertSame(2, $this->n("classes WHERE academic_year_id = {$baru['id']}"));
        $this->assertSame(0, $this->n("classes WHERE academic_year_id = {$baru['id']} AND name = 'Lama'"));
        $this->assertSame(1, $this->n("class_teachers ct JOIN classes k ON k.id = ct.class_id WHERE k.academic_year_id = {$baru['id']} AND k.name = 'A' AND ct.user_id = '{$dosen['id']}'"));
        $this->assertSame(0, $this->n("enrollments WHERE academic_year_id = {$baru['id']}"));
        // Tahun lama tidak disentuh.
        $this->assertSame(1, $this->n("enrollments WHERE class_id = {$d['a']} AND status = 'AKTIF'"));
        $this->assertSame(1, $this->n("classes WHERE id = {$arsip}"));
        $audit = $this->auditRoster('tahun.baru');
        $this->assertSame(2, $audit['detail']['kelas_disalin']);
        $this->assertSame(1, $audit['detail']['penugasan_disalin']);

        // Ringkasan Akun menunjukkan tahun baru.
        $this->assertStringContainsString('2026/2027 Genap', $http->get('/admin/roster')->body);
    }
}
