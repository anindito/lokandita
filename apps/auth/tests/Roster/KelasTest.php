<?php

declare(strict_types=1);

namespace Tests\Roster;

use Lokandita\Auth\Testing\Fixtures;
use Lokandita\Auth\Testing\RosterFixtures;
use Lokandita\Auth\Testing\TestSchema;
use Tests\Support\RosterCase;

/**
 * Kelas dan penugasan pengajar di Akun Lokandita (SPEC.md §5).
 */
final class KelasTest extends RosterCase
{
    public function testKelasDibuatDiubahDiarsipkanDanDibukaKembali(): void
    {
        [$http, $admin] = $this->admin();
        $d = $this->dasar();

        $this->kirim($http, "/admin/mata-kuliah/{$d['matkul']}/kelas", ['name' => 'C', 'capacity' => '30', 'tahun' => $d['tahun']]);
        $c = $this->r("SELECT * FROM classes WHERE course_id = {$d['matkul']} AND name = 'C'");
        $this->assertNotNull($c);
        $this->assertRedirect($http, "/admin/kelas/{$c['id']}?pesan=dibuat");
        $this->assertSame('30', (string) $c['capacity']);
        $audit = $this->auditRoster('kelas.dibuat');
        $this->assertSame($admin['id'], $audit['actor_id']);
        $this->assertSame((int) $c['id'], $audit['detail']['class_id']);
        $this->assertSame($d['matkul'], $audit['detail']['course_id']);

        // Nama yang sama di mata kuliah dan tahun yang sama ditolak; kapasitas nol ditolak.
        $this->kirim($http, "/admin/mata-kuliah/{$d['matkul']}/kelas", ['name' => 'A', 'tahun' => $d['tahun']]);
        $this->assertRedirect($http, "/admin/mata-kuliah/{$d['matkul']}?tahun={$d['tahun']}&galat=kelasNamaDipakai");
        $this->assertSame(422, $this->kirim($http, "/admin/kelas/{$c['id']}", ['name' => 'C', 'capacity' => '0'])->status);
        $this->assertSame(3, $this->n('classes'));

        $this->kirim($http, "/admin/kelas/{$c['id']}", ['name' => 'C Pagi', 'capacity' => '']);
        $this->assertRedirect($http, "/admin/kelas/{$c['id']}?pesan=disimpan");
        $this->assertNull($this->r("SELECT capacity FROM classes WHERE id = {$c['id']}")['capacity']);
        $this->assertSame('C', $this->auditRoster('kelas.diubah')['detail']['sebelum']['name']);

        $this->kirim($http, "/admin/kelas/{$c['id']}/arsip", ['arsip' => '1']);
        $this->assertNotNull($this->r("SELECT archived_at FROM classes WHERE id = {$c['id']}")['archived_at']);
        $this->assertNotNull($this->auditRoster('kelas.diarsipkan'));
        $this->kirim($http, "/admin/kelas/{$c['id']}/arsip", ['arsip' => '0']);
        $this->assertNull($this->r("SELECT archived_at FROM classes WHERE id = {$c['id']}")['archived_at']);
        $this->assertNotNull($this->auditRoster('kelas.dibuka'));
    }

    public function testKelasKosongDihapusKelasBerisiDitolakDenganJumlah(): void
    {
        [$http] = $this->admin();
        $d      = $this->dasar();
        $mhs    = $this->user();
        RosterFixtures::daftar($this->roster, $d['a'], $mhs['id'], ['status' => 'MENGUNDURKAN_DIRI']);

        // Berisi pendaftaran (status apa pun): ditolak, jumlahnya disebut, kelas tetap ada.
        $r = $this->kirim($http, "/admin/kelas/{$d['a']}/hapus", ['yakin' => '1']);
        $this->assertSame(409, $r->status);
        $this->assertStringContainsString('1 pendaftaran dan 0 riwayat', $this->toast($r));
        $this->assertSame(1, $this->n("classes WHERE id = {$d['a']}"));

        // Kosong tetapi belum dicentang yakin.
        $this->assertSame(422, $this->kirim($http, "/admin/kelas/{$d['b']}/hapus")->status);
        $this->assertSame(1, $this->n("classes WHERE id = {$d['b']}"));

        $dosen = $this->user(['global_role' => 'dosen']);
        RosterFixtures::pengajar($this->roster, $d['b'], $dosen['id']);
        $this->kirim($http, "/admin/kelas/{$d['b']}/hapus", ['yakin' => '1']);
        $this->assertRedirect($http, "/admin/mata-kuliah/{$d['matkul']}?tahun={$d['tahun']}&pesan=kelas-dihapus");
        $this->assertSame(0, $this->n("classes WHERE id = {$d['b']}"));
        $this->assertSame(0, $this->n("class_teachers WHERE class_id = {$d['b']}"));
        $this->assertSame([$dosen['id']], $this->auditRoster('kelas.dihapus')['detail']['pengajar']);
    }

    public function testKelasYangDipakaiDataLokaKodeTidakBisaDihapus(): void
    {
        [$http, $admin] = $this->admin();
        $d = $this->dasar();
        // Satu pengumuman LokaKode sungguhan untuk kelas B, ditulis sebagai lokakode_app:
        // kunci asing lintas skema (KONTRAK.md §18) harus menolak penghapusan kelasnya.
        $lokakode = TestSchema::connect((string) getenv('TES_LOKAKODE_DSN'));
        Fixtures::insert($lokakode, 'announcements', [
            'class_id' => $d['b'], 'author_id' => $admin['id'], 'title' => 'Uji', 'body' => 'Uji', 'created_at' => gmdate('Y-m-d H:i:s'),
        ]);
        try {
            $sebelum = $this->jumlahAuditRoster();
            $r       = $this->kirim($http, "/admin/kelas/{$d['b']}/hapus", ['yakin' => '1']);
            $this->assertSame(409, $r->status, substr(strip_tags($r->body), 0, 300));
            $this->assertStringContainsString('LokaKode', $this->toast($r));
            $this->assertSame(1, $this->n("classes WHERE id = {$d['b']}"));
            $this->assertSame($sebelum, $this->jumlahAuditRoster(), 'Penghapusan yang ditolak tidak meninggalkan baris audit');
        } finally {
            $lokakode->query("DELETE FROM announcements WHERE class_id = {$d['b']}");
        }
    }

    public function testPengajarDitugaskanDanDicabut(): void
    {
        [$http, $admin] = $this->admin();
        $d     = $this->dasar();
        $dosen = $this->user(['global_role' => 'dosen', 'nama' => 'Dosen Pengajar']);
        $mhs   = $this->user();

        foreach ([$mhs['nomor_induk'], 'tidak-ada-999'] as $nomor) {
            $this->assertSame(422, $this->kirim($http, "/admin/kelas/{$d['a']}/pengajar", ['nomor_induk' => $nomor])->status);
        }
        $this->assertSame(0, $this->n('class_teachers'));

        $this->kirim($http, "/admin/kelas/{$d['a']}/pengajar", ['nomor_induk' => $dosen['nomor_induk']]);
        $this->assertRedirect($http, "/admin/kelas/{$d['a']}?pesan=pengajar-ditambah");
        $baris = $this->r("SELECT * FROM class_teachers WHERE class_id = {$d['a']}");
        $this->assertSame($dosen['id'], $baris['user_id']);
        $this->assertSame($admin['id'], $baris['assigned_by']);
        $audit = $this->auditRoster('pengajar.ditugaskan');
        $this->assertSame($dosen['id'], $audit['target_id']);
        $this->assertSame($d['a'], $audit['detail']['class_id']);
        $this->assertStringContainsString('Dosen Pengajar', $http->get("/admin/kelas/{$d['a']}")->body);

        // Dua kali: tidak ada baris kedua.
        $this->kirim($http, "/admin/kelas/{$d['a']}/pengajar", ['nomor_induk' => $dosen['nomor_induk']]);
        $this->assertRedirect($http, "/admin/kelas/{$d['a']}?pesan=pengajar-sudah");
        $this->assertSame(1, $this->n('class_teachers'));

        // Lintas kelas: mencabut lewat URL kelas B tidak menyentuh penugasan di kelas A dan tidak dicatat.
        $dicabut = $this->jumlahAuditRoster('pengajar.dicabut');
        $this->kirim($http, "/admin/kelas/{$d['b']}/pengajar/hapus", ['user_id' => $dosen['id']]);
        $this->assertSame(1, $this->n('class_teachers'));
        $this->assertSame($dicabut, $this->jumlahAuditRoster('pengajar.dicabut'));

        $this->kirim($http, "/admin/kelas/{$d['a']}/pengajar/hapus", ['user_id' => $dosen['id']]);
        $this->assertSame(0, $this->n('class_teachers'));
        $this->assertSame($dosen['id'], $this->auditRoster('pengajar.dicabut')['target_id']);
    }
}
