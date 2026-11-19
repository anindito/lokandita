<?php

declare(strict_types=1);

namespace Tests\Admin;

use Tests\Support\AuthCase;

/**
 * `spark akun:impor` (SPEC.md §17): akun mahasiswa dari berkas di masukan standar,
 * kata sandi awal bersama dari SANDI_AWAL, hanya atas nama superadmin.
 */
final class ImporAkunCliTest extends AuthCase
{
    private const SANDI = 'Awal-Kuliah-2026-Uji';

    /**
     * @param list<string> $argumen
     *
     * @return array{0: int, 1: string} kode keluar, stdout + stderr
     */
    private function spark(array $argumen, string $masukan, ?string $sandi = null): array
    {
        $env = getenv();
        unset($env['SANDI_AWAL']);
        if ($sandi !== null) {
            $env['SANDI_AWAL'] = $sandi;
        }
        $proses = proc_open(['php', 'spark', 'akun:impor', ...$argumen, '--no-header'], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipa, ROOTPATH, $env);
        fwrite($pipa[0], $masukan);
        fclose($pipa[0]);
        $keluar = stream_get_contents($pipa[1]) . stream_get_contents($pipa[2]);

        return [proc_close($proses), (string) $keluar];
    }

    private function jumlah(string $awalan): int
    {
        return (int) $this->row("SELECT COUNT(*) n FROM users WHERE nomor_induk LIKE '{$awalan}%'")['n'];
    }

    public function testPratinjauLaluJalankanMembuatMahasiswaDenganSandiBersamaDanUlanganTidakMengubah(): void
    {
        [, $admin] = $this->admin();
        $oleh = (string) $admin['nomor_induk'];
        $t    = 'cli' . bin2hex(random_bytes(3));
        $ada  = $this->user(['global_role' => 'mahasiswa']);
        $csv  = "nomor_induk,nama,email,global_role,apps\n"
            . "{$t}01,Mahasiswa Satu,,mahasiswa,\n"
            . "{$t}02,Mahasiswa Dua,{$t}02@contoh.test,mahasiswa,portal|lokakode\n"
            . "{$t}03,Dosen Tiga,,dosen,\n"
            . "{$ada['nomor_induk']},Sudah Ada,,mahasiswa,\n";

        // Pratinjau: jumlah saja, tidak ada nama atau nomor induk, tidak ada yang dibuat.
        [$kode, $keluar] = $this->spark(['--oleh', $oleh], $csv);
        $this->assertSame(0, $kode, $keluar);
        $this->assertStringContainsString('berkas: 4 baris data', $keluar);
        $this->assertStringContainsString('akun baru: 2 · sudah terdaftar (tidak diubah): 1 · dosen (dilewati): 1 · bermasalah: 0', $keluar);
        $this->assertStringContainsString('PRATINJAU', $keluar);
        $this->assertStringNotContainsString($t, $keluar);
        $this->assertStringNotContainsString('Mahasiswa Satu', $keluar);
        $this->assertSame(0, $this->jumlah($t));

        [$kode, $keluar] = $this->spark(['--oleh', $oleh, '--jalankan', '--nama', 'mahasiswa-uji.csv'], $csv, self::SANDI);
        $this->assertSame(0, $kode, $keluar);
        $this->assertStringContainsString('DITULIS: 2 akun dibuat, 0 dilewati', $keluar);
        $this->assertStringNotContainsString(self::SANDI, $keluar);

        $baris = $this->db->query("SELECT * FROM users WHERE nomor_induk LIKE '{$t}%' ORDER BY nomor_induk")->fetch_all(MYSQLI_ASSOC);
        $this->assertCount(2, $baris, 'baris dosen tidak dibuat');
        foreach ($baris as $b) {
            $this->assertSame(['mahasiswa', 'active', '1', '1', $admin['id']],
                [$b['global_role'], $b['status'], (string) $b['must_change_password'], (string) $b['idle_logout'], $b['created_by']]);
        }
        // Kolom apps kosong → keempat aplikasi; kolom yang diisi dipakai apa adanya.
        $apps = fn (string $id): array => array_column($this->db->query("SELECT app FROM app_access WHERE user_id = '{$id}' AND enabled = 1 ORDER BY CAST(app AS CHAR)")->fetch_all(MYSQLI_ASSOC), 'app');
        $this->assertSame(['lokakode', 'lokasiber', 'lokaujian', 'portal'], $apps($baris[0]['id']));
        $this->assertSame(['lokakode', 'portal'], $apps($baris[1]['id']));

        // Kata sandi bersama itu benar-benar berlaku, dan login pertama dipaksa menggantinya.
        $this->assertRedirect($this->login(['nomor_induk' => "{$t}01", 'password' => self::SANDI]), '/ganti-password');
        $this->assertRedirect($this->login(['nomor_induk' => "{$t}02", 'password' => self::SANDI]), '/ganti-password');

        // Audit: satu ringkasan atas nama superadmin itu, tanpa kata sandi; satu baris per akun.
        $catatan = $this->lastAudit('user.imported');
        $this->assertSame($admin['id'], $catatan['actor_id']);
        $this->assertSame(['mahasiswa-uji.csv', 2, 1, 1, 'shared', 'cli'], [$catatan['detail']['file'], $catatan['detail']['created'],
            $catatan['detail']['already_existed'], $catatan['detail']['not_student'], $catatan['detail']['password_mode'], $catatan['detail']['lewat']]);
        $this->assertSame('import', $this->lastAudit('user.created', $baris[0]['id'])['detail']['via']);
        $this->assertSame(0, (int) $this->row("SELECT COUNT(*) n FROM audit_log WHERE detail LIKE '%" . self::SANDI . "%'")['n']);

        // Diulang: semuanya sudah terdaftar, tidak ada akun baru, hash tidak berubah.
        [$kode, $keluar] = $this->spark(['--oleh', $oleh, '--jalankan'], $csv, 'Sandi-Lain-Sama-Sekali-77');
        $this->assertSame(0, $kode, $keluar);
        $this->assertStringContainsString('akun baru: 0 · sudah terdaftar (tidak diubah): 3', $keluar);
        $this->assertStringContainsString('DITULIS: 0 akun dibuat', $keluar);
        $this->assertSame($baris[0]['password_hash'], $this->userRow($baris[0]['id'])['password_hash']);
    }

    public function testBarisBermasalahDisebutDenganNomorBarisDanMembatalkanPenulisan(): void
    {
        [, $admin] = $this->admin();
        $t   = 'slh' . bin2hex(random_bytes(3));
        $csv = "nomor_induk;nama;global_role\n{$t}01;Baik;mahasiswa\n{$t}01;Kembar;mahasiswa\n{$t}03;;mahasiswa\n{$t}04;Calon Admin;superadmin\n";

        [$kode, $keluar] = $this->spark(['--oleh', (string) $admin['nomor_induk'], '--jalankan'], $csv, self::SANDI);
        $this->assertSame(1, $kode, $keluar);
        $this->assertStringContainsString('akun baru: 1 · sudah terdaftar (tidak diubah): 0 · dosen (dilewati): 0 · bermasalah: 3', $keluar);
        $this->assertStringContainsString('  baris 3: nomorIndukGanda', $keluar);
        $this->assertStringContainsString('  baris 4: nama', $keluar);
        $this->assertStringContainsString('  baris 5: superadminLewatImpor', $keluar);
        $this->assertStringContainsString('ada baris bermasalah', $keluar);
        $this->assertSame(0, $this->jumlah($t), 'tidak ada akun yang dibuat selama berkas masih bermasalah');
    }

    public function testDitolakTanpaSuperadminTanpaSandiDenganSandiLemahAtauBerkasYangSalah(): void
    {
        [, $admin] = $this->admin();
        $oleh  = (string) $admin['nomor_induk'];
        $dosen = $this->user(['global_role' => 'dosen']);
        $mati  = $this->user(['global_role' => 'superadmin', 'mfa_enabled' => 1, 'status' => 'disabled']);
        $t     = 'tlk' . bin2hex(random_bytes(3));
        $csv   = "nomor_induk,nama,global_role\n{$t}01,Satu,mahasiswa\n";

        foreach ([(string) $dosen['nomor_induk'], (string) $mati['nomor_induk'], 'tidak-ada'] as $bukan) {
            [$kode, $keluar] = $this->spark(['--oleh', $bukan, '--jalankan'], $csv, self::SANDI);
            $this->assertSame(1, $kode, $bukan);
            $this->assertStringContainsString('bukan superadmin yang aktif', $keluar);
        }
        [$kode] = $this->spark(['--jalankan'], $csv, self::SANDI);
        $this->assertSame(1, $kode, 'tanpa --oleh');

        [$kode, $keluar] = $this->spark(['--oleh', $oleh, '--jalankan'], $csv);
        $this->assertSame(1, $kode);
        $this->assertStringContainsString('SANDI_AWAL kosong', $keluar);

        foreach (['pendek', 'password123', '1234567890'] as $lemah) {
            [$kode, $keluar] = $this->spark(['--oleh', $oleh, '--jalankan'], $csv, $lemah);
            $this->assertSame(1, $kode, $lemah);
            $this->assertStringContainsString('SANDI_AWAL ditolak', $keluar);
            $this->assertStringNotContainsString($lemah, $keluar);
        }

        [$kode, $keluar] = $this->spark(['--oleh', $oleh, '--jalankan'], "nim,nama\n{$t}01,Satu\n", self::SANDI);
        $this->assertSame(1, $kode);
        $this->assertStringContainsString('kolom tidak ada di baris judul: nomor_induk, global_role', $keluar);

        [$kode, $keluar] = $this->spark(['--oleh', $oleh, '--jalankan'], '', self::SANDI);
        $this->assertSame(1, $kode);
        $this->assertStringContainsString('berkas kosong', $keluar);

        $this->assertSame(0, $this->jumlah($t));
    }
}
