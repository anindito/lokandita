<?php

declare(strict_types=1);

namespace Tests\Roster;

use Lokandita\Auth\Testing\RosterFixtures;
use Tests\Support\RosterCase;

/**
 * `spark data:pengajar` dan `data:pendaftaran` (SPEC.md §17, KONTRAK.md §13): roster dari berkas
 * CSV masuk lewat aturan yang sama dengan halaman kelas. Sampai tahap A1 kedua perintah ini ada
 * di LokaKode; pencocokannya (`data:cocokkan`, hanya membaca) tetap di sana.
 */
final class BerkasCliTest extends RosterCase
{
    /**
     * @param list<string> $argumen
     *
     * @return array{0: int, 1: string}
     */
    private function spark(array $argumen, string $masukan): array
    {
        $proses = proc_open(['php', 'spark', ...$argumen, '--no-header'], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipa, ROOTPATH);
        fwrite($pipa[0], $masukan);
        fclose($pipa[0]);
        $keluar = stream_get_contents($pipa[1]) . stream_get_contents($pipa[2]);

        return [proc_close($proses), (string) $keluar];
    }

    /**
     * Superadmin, dua dosen, lima mahasiswa, satu mata kuliah dengan kelas A dan B.
     *
     * @return array<string, mixed>
     */
    private function awal(): array
    {
        [, $admin] = $this->admin();
        $d         = $this->dasar();
        $d += ['admin' => $admin, 'oleh' => (string) $admin['nomor_induk'], 'd1' => $d['pengampu'], 'd2' => $this->user(['global_role' => 'dosen'])];
        for ($i = 1; $i <= 5; $i++) {
            $d["m{$i}"] = $this->user(['nama' => "Mahasiswa Nomor {$i}"]);
        }
        $n = static fn (string $k): string => (string) $d[$k]['nomor_induk'];
        // Seperti berkas asli: CRLF, dan kolom tambahan yang tidak dipakai perintah ini.
        $d['csvMhs'] = "nomor_induk,nama,global_role,mata_kuliah,kelas\r\n"
            . "{$n('m1')},Mahasiswa Nomor 1,mahasiswa,UJI101,A\r\n{$n('m2')},Mahasiswa Nomor 2,mahasiswa,UJI101,A\r\n{$n('m3')},Mahasiswa Nomor 3,mahasiswa,UJI101,A\r\n"
            . "{$n('m4')},Mahasiswa Nomor 4,mahasiswa,UJI101,B\r\n{$n('m5')},Mahasiswa Nomor 5,mahasiswa,UJI101,B\r\n";
        $d['csvAjar'] = "mata_kuliah,kelas,nip_pengajar\r\nUJI101,A,{$n('d1')}\r\nUJI101,B,{$n('d2')}\r\n";

        return $d;
    }

    public function testPratinjauTidakMenulisJalankanMenulisDenganAuditDanUlanganTidakMengubah(): void
    {
        $d     = $this->awal();
        $audit = $this->jumlahAuditRoster();

        // Pratinjau tidak menulis, dan keluarannya tidak memuat data pribadi.
        [$kode, $keluar] = $this->spark(['data:pengajar', '--oleh', $d['oleh']], $d['csvAjar']);
        $this->assertSame(0, $kode, $keluar);
        $this->assertStringContainsString('penugasan baru: 2 · sudah ada: 0 · bermasalah: 0', $keluar);
        [$kode, $keluar] = $this->spark(['data:pendaftaran', '--oleh', $d['oleh']], $d['csvMhs']);
        $this->assertSame(0, $kode, $keluar);
        $this->assertStringContainsString('berkas: 5 baris data', $keluar);
        $this->assertStringContainsString('pendaftaran baru: 5 · diaktifkan lagi: 0 · sudah terdaftar: 0 · bermasalah: 0', $keluar);
        $this->assertStringContainsString('UJI101 A: 3 di berkas — 3 baru', $keluar);
        $this->assertStringContainsString('PRATINJAU', $keluar);
        $this->assertStringNotContainsString((string) $d['m1']['nomor_induk'], $keluar);
        $this->assertStringNotContainsString('Mahasiswa Nomor', $keluar);
        $this->assertSame(0, $this->n('enrollments') + $this->n('class_teachers'));
        $this->assertSame($audit, $this->jumlahAuditRoster());

        // Menulis.
        [$kode, $keluar] = $this->spark(['data:pengajar', '--oleh', $d['oleh'], '--jalankan'], $d['csvAjar']);
        $this->assertSame(0, $kode, $keluar);
        $this->assertStringContainsString('DITULIS: 2.', $keluar);
        [$kode, $keluar] = $this->spark(['data:pendaftaran', '--oleh', $d['oleh'], '--jalankan'], $d['csvMhs']);
        $this->assertSame(0, $kode, $keluar);
        $this->assertStringContainsString('DITULIS: 5.', $keluar);
        $this->assertSame(3, $this->n("enrollments WHERE class_id = {$d['a']} AND status = 'AKTIF'"));
        $this->assertSame(2, $this->n("enrollments WHERE class_id = {$d['b']} AND status = 'AKTIF'"));
        $this->assertSame(1, $this->n("enrollments WHERE class_id = {$d['b']} AND user_id = '{$d['m4']['id']}'"));
        $this->assertSame(1, $this->n("class_teachers WHERE class_id = {$d['b']} AND user_id = '{$d['d2']['id']}'"));

        // Audit seperti lewat halaman web: app roster, atas nama superadmin itu, bertanda lewat cli.
        $this->assertSame($audit + 7, $this->jumlahAuditRoster());
        foreach (['pengajar.ditugaskan', 'pendaftaran.dibuat'] as $aksi) {
            $a = $this->auditRoster($aksi);
            $this->assertSame([$d['admin']['id'], 'cli'], [$a['actor_id'], $a['detail']['lewat']], $aksi);
        }

        // Diulang: tidak ada yang baru, tidak ada audit baru.
        [$kode, $keluar] = $this->spark(['data:pendaftaran', '--oleh', $d['oleh'], '--jalankan'], $d['csvMhs']);
        $this->assertSame(0, $kode, $keluar);
        $this->assertStringContainsString('pendaftaran baru: 0 · diaktifkan lagi: 0 · sudah terdaftar: 5', $keluar);
        [$kode, $keluar] = $this->spark(['data:pengajar', '--oleh', $d['oleh'], '--jalankan'], $d['csvAjar']);
        $this->assertSame(0, $kode, $keluar);
        $this->assertStringContainsString('penugasan baru: 0 · sudah ada: 2', $keluar);
        $this->assertSame($audit + 7, $this->jumlahAuditRoster());
    }

    public function testBarisBermasalahDisebutDanMembatalkanSeluruhPenulisan(): void
    {
        $d     = $this->awal();
        $audit = $this->jumlahAuditRoster();
        $n     = static fn (string $k): string => (string) $d[$k]['nomor_induk'];
        RosterFixtures::daftar($this->roster, $d['b'], $d['m3']['id']); // sudah aktif di kelas lain mata kuliah yang sama

        $csv = "nomor_induk,nama,global_role,mata_kuliah,kelas\n"
            . "{$n('m1')},x,mahasiswa,UJI101,A\n"          // 2: baik
            . "{$n('m2')},x,mahasiswa,UJI101,C\n"          // 3: kelas tidak ada
            . "takterdaftar01,x,mahasiswa,UJI101,A\n"      // 4: akun tidak ada
            . "{$n('d2')},x,mahasiswa,UJI101,A\n"          // 5: bukan mahasiswa
            . "{$n('m3')},x,mahasiswa,UJI101,A\n"          // 6: aktif di kelas B
            . "{$n('m1')},x,mahasiswa,UJI101,B\n";         // 7: dua kelas dalam satu mata kuliah
        [$kode, $keluar] = $this->spark(['data:pendaftaran', '--oleh', $d['oleh'], '--jalankan'], $csv);
        $this->assertSame(1, $kode, $keluar);
        $this->assertStringContainsString('bermasalah: 5', $keluar);
        $this->assertStringContainsString('baris 3 (UJI101 C): kelasTidakAda', $keluar);
        $this->assertStringContainsString('baris 4 (UJI101 A): tidakAda', $keluar);
        $this->assertStringContainsString('baris 5 (UJI101 A): bukanMahasiswa', $keluar);
        $this->assertStringContainsString('baris 6 (UJI101 A): kelasLain', $keluar);
        $this->assertStringContainsString('baris 7 (UJI101 B): gandaDalamMataKuliah', $keluar);
        $this->assertStringContainsString('Tidak ada yang diubah.', $keluar);
        $this->assertSame(1, $this->n('enrollments'), 'baris yang baik pun tidak ditulis');

        $ajar = "mata_kuliah,kelas,nip_pengajar\nUJI101,A,{$n('d1')}\nUJI101,C,{$n('d1')}\nUJI101,B,takterdaftar01\nUJI101,B,{$n('m1')}\nUJI101,A,{$n('d1')}\n";
        [$kode, $keluar] = $this->spark(['data:pengajar', '--oleh', $d['oleh'], '--jalankan'], $ajar);
        $this->assertSame(1, $kode, $keluar);
        $this->assertStringContainsString('baris 3 (UJI101 C): kelasTidakAda', $keluar);
        $this->assertStringContainsString('baris 4 (UJI101 B): pengajarTidakAda', $keluar);
        $this->assertStringContainsString('baris 5 (UJI101 B): pengajarBukanDosen', $keluar);
        $this->assertStringContainsString('baris 6 (UJI101 A): barisGanda', $keluar);
        $this->assertSame(0, $this->n('class_teachers'));
        $this->assertSame($audit, $this->jumlahAuditRoster());
    }

    public function testMahasiswaYangMengundurkanDiriDiaktifkanLagiDiKelasBerkas(): void
    {
        $d      = $this->awal();
        $lama   = RosterFixtures::kelas($this->roster, $d['matkul'], $d['tahun'], ['name' => '7A']);
        $pernah = RosterFixtures::daftar($this->roster, $lama, $d['m1']['id'], ['status' => 'MENGUNDURKAN_DIRI']);
        $this->roster->query("UPDATE classes SET archived_at = UTC_TIMESTAMP() WHERE id = {$lama}");

        [$kode, $keluar] = $this->spark(['data:pendaftaran', '--oleh', $d['oleh'], '--jalankan'], $d['csvMhs']);
        $this->assertSame(0, $kode, $keluar);
        $this->assertStringContainsString('pendaftaran baru: 4 · diaktifkan lagi: 1', $keluar);
        // Baris pendaftarannya tetap satu dan sama, kini di kelas A; perpindahannya tercatat.
        $this->assertSame(1, $this->n("enrollments WHERE id = {$pernah} AND class_id = {$d['a']} AND status = 'AKTIF'"));
        $this->assertSame(1, $this->n("enrollments WHERE user_id = '{$d['m1']['id']}'"));
        $this->assertSame(1, $this->n("enrollment_moves WHERE enrollment_id = {$pernah} AND from_class_id = {$lama} AND to_class_id = {$d['a']}"));
    }

    public function testDitolakTanpaSuperadminAktifDanDenganBerkasTanpaKolom(): void
    {
        $d = $this->awal();
        foreach (['data:pengajar' => $d['csvAjar'], 'data:pendaftaran' => $d['csvMhs']] as $perintah => $csv) {
            foreach ([(string) $d['d1']['nomor_induk'], (string) $d['m1']['nomor_induk'], 'tidak-ada'] as $bukan) {
                [$kode, $keluar] = $this->spark([$perintah, '--oleh', $bukan, '--jalankan'], $csv);
                $this->assertSame(1, $kode, "{$perintah} {$bukan}");
                $this->assertStringContainsString('bukan superadmin yang aktif', $keluar);
            }
            [$kode] = $this->spark([$perintah, '--jalankan'], $csv);
            $this->assertSame(1, $kode);
        }
        [$kode, $keluar] = $this->spark(['data:pendaftaran', '--oleh', $d['oleh'], '--jalankan'], "nim;kelas\n1;A\n");
        $this->assertSame(1, $kode);
        $this->assertStringContainsString('kolom tidak ada di baris judul: nomor_induk, mata_kuliah', $keluar);

        $this->assertSame(0, $this->n('enrollments') + $this->n('class_teachers'));
    }
}
