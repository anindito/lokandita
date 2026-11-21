<?php

declare(strict_types=1);

namespace Tests\Admin;

use Tests\Support\AuthCase;

/**
 * `spark akun:batas-sandi-awal` dan akibatnya pada login (KONTRAK.md §13):
 * kata sandi awal yang tidak diganti sampai batasnya tidak bisa lagi dipakai.
 */
final class BatasSandiAwalTest extends AuthCase
{
    /**
     * @param list<string> $argumen
     *
     * @return array{0: int, 1: string} kode keluar, stdout + stderr
     */
    private function spark(array $argumen): array
    {
        $proses = proc_open(['php', 'spark', 'akun:batas-sandi-awal', ...$argumen, '--no-header'], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipa, ROOTPATH);
        $keluar = stream_get_contents($pipa[1]) . stream_get_contents($pipa[2]);

        return [proc_close($proses), (string) $keluar];
    }

    private function batas(string $id): ?string
    {
        return $this->userRow($id)['initial_password_expires_at'];
    }

    public function testBatasHanyaMengenaiMahasiswaYangMasihMemakaiSandiAwalDanLoginDitolakSesudahnya(): void
    {
        [$http, $admin] = $this->admin();
        $oleh = (string) $admin['nomor_induk'];

        $awal   = $this->user(['must_change_password' => 1]);
        $ganti  = $this->user(['must_change_password' => 0]);
        $dosen  = $this->user(['must_change_password' => 1, 'global_role' => 'dosen']);
        $mati   = $this->user(['must_change_password' => 1, 'status' => 'disabled']);
        $direset = $this->user(['must_change_password' => 1]);
        $this->db->query("INSERT INTO password_history (user_id, password_hash, created_at) VALUES ('{$direset['id']}', 'x', UTC_TIMESTAMP())");

        // Pratinjau tidak menulis apa pun dan tidak menyebut siapa pun.
        [$kode, $keluar] = $this->spark(['--oleh', $oleh, '--sampai', '2031-03-05']);
        $this->assertSame(0, $kode, $keluar);
        $this->assertStringContainsString('kata sandi awal berlaku sampai: 05-03-2031 23:59 WIB (tersimpan 2031-03-05 17:00:00 UTC)', $keluar);
        $this->assertStringContainsString('PRATINJAU', $keluar);
        $this->assertStringNotContainsString((string) $awal['nomor_induk'], $keluar);
        $this->assertNull($this->batas($awal['id']));

        [$kode, $keluar] = $this->spark(['--oleh', $oleh, '--sampai', '2031-03-05', '--jalankan']);
        $this->assertSame(0, $kode, $keluar);
        $this->assertStringContainsString('DITULIS:', $keluar);
        $this->assertStringNotContainsString((string) $awal['nomor_induk'], $keluar);
        $this->assertStringNotContainsString('{"t"', $keluar);
        $this->assertSame('2031-03-05 17:00:00', $this->batas($awal['id']));
        foreach ([$ganti, $dosen, $mati, $direset] as $bukan) {
            $this->assertNull($this->batas($bukan['id']), 'hanya mahasiswa aktif yang masih memakai kata sandi awalnya');
        }
        $catatan = $this->lastAudit('password.initial_deadline');
        $this->assertSame([$admin['id'], '2031-03-05 17:00:00', 'cli'], [$catatan['actor_id'], $catatan['detail']['until'], $catatan['detail']['lewat']]);

        // Diulang dengan tanggal yang sama: tidak ada yang berubah. Dengan tanggal lain: batasnya pindah.
        [, $keluar] = $this->spark(['--oleh', $oleh, '--sampai', '2031-03-05', '--jalankan']);
        $this->assertStringContainsString('DITULIS: 0 akun diberi batas', $keluar);
        $this->spark(['--oleh', $oleh, '--sampai', '2031-03-06', '--jalankan']);
        $this->assertSame('2031-03-06 17:00:00', $this->batas($awal['id']));

        // Sebelum batas: kata sandi awal masih berlaku dan dipaksa diganti.
        $this->assertRedirect($this->login($awal), '/ganti-password');

        // Sesudah batas: ditolak dengan teks yang sama seperti kata sandi salah, tanpa menambah hitungan kunci.
        $this->db->query("UPDATE users SET initial_password_expires_at = UTC_TIMESTAMP() - INTERVAL 1 MINUTE WHERE id = '{$awal['id']}'");
        $r = $this->login($awal);
        $this->assertSame(422, $r->status);
        $this->assertSame('initial_password_expired', $this->lastAudit('login.failed', $awal['id'])['detail']['reason']);
        $this->assertSame(0, (int) $this->userRow($awal['id'])['failed_attempts']);
        // Kata sandi yang salah tetap dicatat sebagai salah.
        $this->login($awal, null, 'bukan-sandinya-sama-sekali');
        $this->assertSame('wrong_password', $this->lastAudit('login.failed', $awal['id'])['detail']['reason']);

        // Batas yang sudah lewat tidak mengganggu akun yang sudah mengganti kata sandinya.
        $this->db->query("UPDATE users SET initial_password_expires_at = UTC_TIMESTAMP() - INTERVAL 1 MINUTE WHERE id = '{$ganti['id']}'");
        $this->assertNotSame(422, $this->login($ganti)->status);

        // Reset oleh admin memberi kata sandi baru dan menghapus batasnya.
        $r = $http->post('/admin/pengguna/' . $awal['id'] . '/reset-sandi', ['csrf' => $this->csrf($http, '/admin/pengguna/' . $awal['id'])]);
        $this->assertLessThan(400, $r->status);
        $this->assertNull($this->batas($awal['id']));
        $this->assertSame(1, (int) $this->userRow($awal['id'])['must_change_password']);
    }

    public function testDitolakTanpaSuperadminAtauDenganTanggalYangSalah(): void
    {
        [, $admin] = $this->admin();
        $oleh  = (string) $admin['nomor_induk'];
        $dosen = $this->user(['global_role' => 'dosen']);
        $siswa = $this->user(['must_change_password' => 1]);

        foreach ([['--oleh', (string) $dosen['nomor_induk'], '--sampai', '2031-03-05'], ['--oleh', 'tidak-ada', '--sampai', '2031-03-05'],
            ['--sampai', '2031-03-05'], ['--oleh', $oleh], ['--oleh', $oleh, '--sampai', '05-03-2031'], ['--oleh', $oleh, '--sampai', '2031-02-30'],
            ['--oleh', $oleh, '--sampai', '2020-01-01']] as $argumen) {
            [$kode, $keluar] = $this->spark([...$argumen, '--jalankan']);
            $this->assertSame(1, $kode, implode(' ', $argumen));
            $this->assertStringContainsString('GAGAL', $keluar);
        }
        $this->assertNull($this->batas($siswa['id']));
    }
}
