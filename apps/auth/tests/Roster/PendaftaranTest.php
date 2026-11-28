<?php

declare(strict_types=1);

namespace Tests\Roster;

use Lokandita\Auth\Testing\RosterFixtures;
use Tests\Support\RosterCase;

/**
 * Pendaftaran, pindah kelas dan kelulusan di Akun Lokandita (SPEC.md §5).
 */
final class PendaftaranTest extends RosterCase
{
    public function testPratinjauMenggolongkanLaluMenjalankanHanyaYangSiap(): void
    {
        [$http, $admin] = $this->admin();
        $d      = $this->dasar();
        $siap   = $this->user(['nama' => 'Siap Satu', 'apps' => ['portal']]);
        $sudah  = $this->user();
        $lain   = $this->user();
        $mundur = $this->user();
        $dosen  = $this->user(['global_role' => 'dosen']);
        RosterFixtures::daftar($this->roster, $d['a'], $sudah['id']);
        RosterFixtures::daftar($this->roster, $d['b'], $lain['id']);
        $lama = RosterFixtures::daftar($this->roster, $d['b'], $mundur['id'], ['status' => 'MENGUNDURKAN_DIRI']);

        $teks = implode("\n", ['nomor_induk', $siap['nomor_induk'], $sudah['nomor_induk'], $lain['nomor_induk'], $mundur['nomor_induk'], $dosen['nomor_induk'], 'tidak-ada-777', 'x!']);
        $r    = $this->kirim($http, "/admin/kelas/{$d['a']}/daftar", ['nomor' => $teks]);
        $this->assertSame(200, $r->status, substr(strip_tags($r->body), 0, 300));
        foreach (['siap', 'aktifkanLagi', 'sudah', 'kelasLain', 'bukanMahasiswa', 'tidakAda', 'tidakSah'] as $g) {
            $this->assertStringContainsString('data-golongan="' . $g . '"', $r->body, $g);
        }
        $this->assertStringContainsString('LokaKode, LokaUjian', $r->body, 'Akses aplikasi yang belum diberi disebut');
        $this->assertSame(3, $this->n('enrollments'), 'Pratinjau tidak menyimpan apa pun');

        // Menjalankan: isian dari peramban digolongkan ulang; yang tidak boleh ikut diabaikan.
        $this->kirim($http, "/admin/kelas/{$d['a']}/daftar/jalankan", ['nomor' => [$siap['nomor_induk'], $mundur['nomor_induk'], $lain['nomor_induk'], $dosen['nomor_induk']]]);
        $this->assertRedirect($http, "/admin/kelas/{$d['a']}?pesan=didaftarkan&n=2");
        $this->assertSame(1, $this->n("enrollments WHERE user_id = '{$siap['id']}' AND class_id = {$d['a']} AND status = 'AKTIF' AND created_by = '{$admin['id']}'"));
        $this->assertSame(1, $this->n("enrollments WHERE id = {$lama} AND class_id = {$d['a']} AND status = 'AKTIF'"));
        $this->assertSame(1, $this->n("enrollment_moves WHERE enrollment_id = {$lama} AND from_class_id = {$d['b']} AND to_class_id = {$d['a']}"));
        $this->assertSame(1, $this->n("enrollments WHERE user_id = '{$lain['id']}' AND class_id = {$d['b']}"));
        $this->assertSame(0, $this->n("enrollments WHERE user_id = '{$dosen['id']}'"));
        $this->assertSame($siap['id'], $this->auditRoster('pendaftaran.dibuat')['target_id']);
        $this->assertSame($d['a'], $this->auditRoster('pendaftaran.diaktifkan')['detail']['class_id']);

        // Pembaca melihatnya lewat view kontrak.
        $this->assertSame(3, $this->n("roster_enrollments WHERE class_id = {$d['a']} AND status = 'AKTIF'"));
    }

    public function testBerkasCsvDiterimaYangBukanCsvDanKelasArsipDitolak(): void
    {
        [$http] = $this->admin();
        $d      = $this->dasar();
        $mhs    = $this->user();
        $csrf   = $this->csrf($http, '/admin/roster');

        $r = $http->upload("/admin/kelas/{$d['a']}/daftar", ['csrf' => $csrf, 'nomor' => ''], 'berkas', 'kelas.csv', "\xEF\xBB\xBFnomor_induk;nama\r\n{$mhs['nomor_induk']};Abaikan\r\n");
        $this->assertSame(200, $r->status);
        $this->assertStringContainsString('data-golongan="siap"', $r->body);

        $http->upload("/admin/kelas/{$d['a']}/daftar", ['csrf' => $this->csrf($http, '/admin/roster'), 'nomor' => ''], 'berkas', 'kelas.xlsx', 'bukan csv');
        $this->assertRedirect($http, "/admin/kelas/{$d['a']}?pesan=berkasCsv");

        $this->kirim($http, "/admin/kelas/{$d['a']}/daftar", ['nomor' => '']);
        $this->assertRedirect($http, "/admin/kelas/{$d['a']}?pesan=nomorKosong");

        $this->roster->query("UPDATE classes SET archived_at = NOW() WHERE id = {$d['a']}");
        $this->kirim($http, "/admin/kelas/{$d['a']}/daftar/jalankan", ['nomor' => [$mhs['nomor_induk']]]);
        $this->assertRedirect($http, "/admin/kelas/{$d['a']}?pesan=kelasArsip");
        $this->assertSame(0, $this->n('enrollments'));
    }

    public function testPindahKelasHanyaDalamMataKuliahDanTahunYangSama(): void
    {
        [$http, $admin] = $this->admin();
        $d    = $this->dasar();
        $satu = $this->user();
        $dua  = $this->user();
        $e1   = RosterFixtures::daftar($this->roster, $d['a'], $satu['id']);
        $e2   = RosterFixtures::daftar($this->roster, $d['b'], $dua['id']);
        $lainMatkul = RosterFixtures::kelas($this->roster, RosterFixtures::matkul($this->roster, $d['pengampu']['id']), $d['tahun']);
        $lainTahun  = RosterFixtures::kelas($this->roster, $d['matkul'], RosterFixtures::tahun($this->roster, ['status' => 'archived']));
        $arsip      = RosterFixtures::kelas($this->roster, $d['matkul'], $d['tahun'], ['name' => 'Arsip', 'archived_at' => gmdate('Y-m-d H:i:s')]);

        foreach ([$lainMatkul, $lainTahun, $d['a'], 999999, 0] as $tujuan) {
            $this->kirim($http, "/admin/kelas/{$d['a']}/pindah", ['ids' => [$e1], 'tujuan' => $tujuan]);
            $this->assertRedirect($http, "/admin/kelas/{$d['a']}?pesan=tujuanSalah", "tujuan {$tujuan}");
        }
        $this->kirim($http, "/admin/kelas/{$d['a']}/pindah", ['ids' => [$e1], 'tujuan' => $arsip]);
        $this->assertRedirect($http, "/admin/kelas/{$d['a']}?pesan=tujuanArsip");
        $this->assertSame(0, $this->n('enrollment_moves'));

        // Lintas kelas: id pendaftaran kelas B dikirim ke URL kelas A — tidak berubah.
        $this->kirim($http, "/admin/kelas/{$d['a']}/pindah", ['ids' => [$e2], 'tujuan' => $d['b']]);
        $this->assertRedirect($http, "/admin/kelas/{$d['a']}?pesan=tidak-ada-pilihan&n=0");
        $this->assertSame(0, $this->n('enrollment_moves'));

        $this->kirim($http, "/admin/kelas/{$d['a']}/pindah", ['ids' => [$e1, $e2], 'tujuan' => $d['b']]);
        $this->assertRedirect($http, "/admin/kelas/{$d['a']}?pesan=dipindah&n=1");
        $this->assertSame($d['b'], (int) $this->r("SELECT class_id FROM enrollments WHERE id = {$e1}")['class_id']);
        $pindah = $this->r('SELECT * FROM enrollment_moves');
        $this->assertSame([$e1, $d['a'], $d['b'], $admin['id']], [(int) $pindah['enrollment_id'], (int) $pindah['from_class_id'], (int) $pindah['to_class_id'], $pindah['moved_by']]);
        $audit = $this->auditRoster('pendaftaran.dipindah');
        $this->assertSame($satu['id'], $audit['target_id']);
        $this->assertSame([$d['a'], $d['b']], [$audit['detail']['dari_id'], $audit['detail']['ke_id']]);
    }

    public function testMundurLulusDanBatalLulusHanyaUntukPendaftaranKelasItu(): void
    {
        [$http] = $this->admin();
        $d    = $this->dasar();
        $satu = $this->user();
        $dua  = $this->user();
        $tiga = $this->user();
        $e1   = RosterFixtures::daftar($this->roster, $d['a'], $satu['id']);
        $e2   = RosterFixtures::daftar($this->roster, $d['a'], $dua['id']);
        $e3   = RosterFixtures::daftar($this->roster, $d['b'], $tiga['id']);
        $status = fn (int $id) => $this->r("SELECT status, graduated_on FROM enrollments WHERE id = {$id}");

        // id milik kelas B lewat URL kelas A: dilewati.
        $this->kirim($http, "/admin/kelas/{$d['a']}/mundur", ['ids' => [$e3]]);
        $this->assertRedirect($http, "/admin/kelas/{$d['a']}?pesan=tidak-ada-pilihan&n=0");
        $this->assertSame('AKTIF', $status($e3)['status']);

        $this->kirim($http, "/admin/kelas/{$d['a']}/mundur", ['ids' => [$e1, $e3]]);
        $this->assertRedirect($http, "/admin/kelas/{$d['a']}?pesan=mundur&n=1");
        $this->assertSame('MENGUNDURKAN_DIRI', $status($e1)['status']);
        $this->assertSame($satu['id'], $this->auditRoster('pendaftaran.mundur')['target_id']);

        $this->kirim($http, "/admin/kelas/{$d['a']}/lulus", ['ids' => [$e2], 'tanggal' => 'kemarin']);
        $this->assertRedirect($http, "/admin/kelas/{$d['a']}?pesan=tanggalLulus");

        // Seluruh kelas: hanya yang AKTIF di kelas A.
        $this->kirim($http, "/admin/kelas/{$d['a']}/lulus", ['semua' => '1', 'tanggal' => '2027-01-20']);
        $this->assertRedirect($http, "/admin/kelas/{$d['a']}?pesan=lulus&n=1");
        $this->assertSame(['status' => 'LULUS', 'graduated_on' => '2027-01-20'], $status($e2));
        $this->assertSame('MENGUNDURKAN_DIRI', $status($e1)['status']);
        $this->assertSame('AKTIF', $status($e3)['status']);
        $this->assertSame('2027-01-20', $this->auditRoster('pendaftaran.lulus')['detail']['graduated_on']);

        $this->kirim($http, "/admin/kelas/{$d['a']}/batal-lulus", ['ids' => [$e2, $e1]]);
        $this->assertRedirect($http, "/admin/kelas/{$d['a']}?pesan=batal-lulus&n=1");
        $this->assertSame(['status' => 'AKTIF', 'graduated_on' => null], $status($e2));
        $this->assertNotNull($this->auditRoster('pendaftaran.batal-lulus'));

        // Halaman kelas menampilkan daftar dan tautan ke akun.
        $halaman = $http->get("/admin/kelas/{$d['a']}")->body;
        $this->assertStringContainsString($satu['nomor_induk'], $halaman);
        $this->assertStringContainsString('/admin/pengguna/' . $satu['id'], $halaman);
    }

    public function testHalamanRosterTersediaDalamTigaBahasaDanArabKananKeKiri(): void
    {
        [$http, $admin] = $this->admin();
        $d = $this->dasar();
        foreach (['id' => 'Daftarkan mahasiswa', 'en' => 'Enroll students', 'ar' => 'dir="rtl"'] as $bahasa => $tanda) {
            // Bahasa pengguna yang masuk berasal dari akunnya (menu akun), bukan dari cookie.
            $this->db->query("UPDATE users SET locale = '{$bahasa}' WHERE id = '{$admin['id']}'");
            foreach (['/admin/roster', '/admin/tahun-ajaran', '/admin/tahun-ajaran/baru', '/admin/mata-kuliah', "/admin/mata-kuliah/{$d['matkul']}", "/admin/kelas/{$d['a']}", "/admin/kelas/{$d['a']}/ubah"] as $jalur) {
                $r = $http->get($jalur);
                $this->assertSame(200, $r->status, "{$bahasa} {$jalur}");
                $this->assertDoesNotMatchRegularExpression('/Roster\.[a-zA-Z]+\.[a-zA-Z]+/', strip_tags($r->body), "{$bahasa} {$jalur}: ada kunci bahasa yang tidak diterjemahkan");
            }
            $this->assertStringContainsString($tanda, $http->get("/admin/kelas/{$d['a']}")->body, $bahasa);
        }
    }
}
