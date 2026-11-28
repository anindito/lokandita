<?php

declare(strict_types=1);

namespace Tests\Akademik;

use Tests\Support\KodeCase;
use Tests\Support\SoalBantu;

/**
 * `spark data:cocokkan` (SPEC.md §17 dan gerbang §18 tahap 12): pencocokan jumlah benar-benar
 * gagal bila ada yang berbeda. Hanya membaca. Perintah yang MENULIS roster dari berkas
 * (`data:pengajar`, `data:pendaftaran`) sejak tahap A2 ada di Akun Lokandita dan diuji di sana
 * (apps/auth/tests/Roster/BerkasCliTest.php).
 */
final class DataCocokkanCliTest extends KodeCase
{
    use SoalBantu;

    /**
     * Dua dosen, lima mahasiswa, satu mata kuliah dengan kelas A dan B — dan tidak ada
     * akun aktif lain di identity_test, supaya jumlah di direktori bisa dicocokkan.
     *
     * @return array<string, mixed>
     */
    private function awal(): array
    {
        $this->idn->query("UPDATE users SET status = 'disabled'");
        $d = [
            'admin' => $this->user('superadmin', 'Admin Uji'),
            'd1'    => $this->user('dosen', 'Dosen Satu'),
            'd2'    => $this->user('dosen', 'Dosen Dua'),
            'tahun' => $this->tahun('2026/2027'),
        ];
        for ($i = 1; $i <= 5; $i++) {
            $d["m{$i}"] = $this->user('mahasiswa', "Mahasiswa Nomor {$i}");
        }
        $d['matkul'] = $this->matkul('UJX101', 'Uji Pemrograman X', $d['d1']);
        $d['A']      = $this->kelas($d['matkul'], $d['tahun'], 'A');
        $d['B']      = $this->kelas($d['matkul'], $d['tahun'], 'B');
        $d['oleh']   = (string) $d['admin']['nomor_induk'];

        $n = fn (string $k): string => (string) $d[$k]['nomor_induk'];
        // Seperti berkas asli: CRLF, dan kolom tambahan yang tidak dipakai perintah ini.
        $d['csvMhs'] = "nomor_induk,nama,global_role,mata_kuliah,kelas\r\n"
            . "{$n('m1')},Mahasiswa Nomor 1,mahasiswa,UJX101,A\r\n{$n('m2')},Mahasiswa Nomor 2,mahasiswa,UJX101,A\r\n{$n('m3')},Mahasiswa Nomor 3,mahasiswa,UJX101,A\r\n"
            . "{$n('m4')},Mahasiswa Nomor 4,mahasiswa,UJX101,B\r\n{$n('m5')},Mahasiswa Nomor 5,mahasiswa,UJX101,B\r\n";
        $d['csvAjar']  = "mata_kuliah,kelas,nip_pengajar\r\nUJX101,A,{$n('d1')}\r\nUJX101,B,{$n('d2')}\r\n";
        $d['csvDosen'] = "nomor_induk,nama,global_role\r\n{$n('d1')},Dosen Satu,dosen\r\n{$n('d2')},Dosen Dua,dosen\r\n";

        return $d;
    }

    /**
     * Roster seperti di kedua berkas, disiapkan langsung (tes ini tidak menulis lewat aplikasi).
     *
     * @param array<string, mixed> $d
     */
    private function isiSemua(array $d): void
    {
        $this->ajar($d['A'], $d['d1']);
        $this->ajar($d['B'], $d['d2']);
        foreach (['m1' => 'A', 'm2' => 'A', 'm3' => 'A', 'm4' => 'B', 'm5' => 'B'] as $m => $k) {
            $this->daftar($d[$m], $d[$k]);
        }
    }

    public function testBasisDataKosongTidakCocokLaluCocokSesudahDiisi(): void
    {
        $d = $this->awal();

        // Sebelum apa pun ada, pencocokan harus gagal: gerbang tidak boleh lulus pada basis data kosong.
        [$kode, $keluar] = $this->spark(['data:cocokkan', 'mahasiswa'], $d['csvMhs']);
        $this->assertSame(1, $kode, $keluar);
        $this->assertStringContainsString('data:cocokkan mahasiswa: BEDA', $keluar);
        [$kode] = $this->spark(['data:cocokkan', 'pengajar'], $d['csvAjar']);
        $this->assertSame(1, $kode);

        $this->isiSemua($d);
        [$kode, $keluar] = $this->spark(['data:cocokkan', 'mahasiswa'], $d['csvMhs']);
        $this->assertSame(0, $kode, $keluar);
        $this->assertStringContainsString('COCOK  akun mahasiswa: nomor induk unik di berkas ↔ mahasiswa aktif di LokaAuth: 5 ↔ 5', $keluar);
        $this->assertStringContainsString('COCOK  kelas UJX101 A: berkas 3 · AKTIF di basis data 3 · orang yang sama 3', $keluar);
        $this->assertStringContainsString('COCOK  kelas UJX101 B: berkas 2 · AKTIF di basis data 2 · orang yang sama 2', $keluar);
        $this->assertStringContainsString('data:cocokkan mahasiswa: COCOK', $keluar);
        $this->assertStringNotContainsString('BEDA', $keluar);
        // Keluaran tidak memuat data pribadi.
        $this->assertStringNotContainsString((string) $d['m1']['nomor_induk'], $keluar);
        $this->assertStringNotContainsString('Mahasiswa Nomor', $keluar);
        [$kode, $keluar] = $this->spark(['data:cocokkan', 'dosen'], $d['csvDosen']);
        $this->assertSame(0, $kode, $keluar);
        $this->assertStringContainsString('dosen aktif di LokaAuth: 2 ↔ 2', $keluar);
        [$kode, $keluar] = $this->spark(['data:cocokkan', 'pengajar'], $d['csvAjar']);
        $this->assertSame(0, $kode, $keluar);
        $this->assertStringContainsString('data:cocokkan pengajar: COCOK', $keluar);

        // Perintah yang menulis roster tidak ada lagi di LokaKode.
        foreach (['data:pengajar', 'data:pendaftaran'] as $perintah) {
            [, $keluar, $galat] = $this->spark([$perintah, '--oleh', $d['oleh'], '--jalankan'], $d['csvAjar']);
            $this->assertStringContainsString('not found', $keluar . $galat, $perintah);
        }
        $this->assertSame(5, $this->hitung('SELECT COUNT(*) FROM enrollments'));
        $this->assertSame(2, $this->hitung('SELECT COUNT(*) FROM class_teachers'));
    }

    public function testPencocokanGagalUntukSetiapJenisPerbedaan(): void
    {
        $d = $this->awal();
        $this->isiSemua($d);
        $cocok = function (string $jenis, string $csv): array {
            return $this->spark(['data:cocokkan', $jenis], $csv);
        };
        $this->assertSame(0, $cocok('mahasiswa', $d['csvMhs'])[0]);

        // Berkas kehilangan satu baris: basis data punya satu lebih banyak.
        $kurang = implode("\r\n", array_slice(explode("\r\n", $d['csvMhs']), 0, 5)) . "\r\n";
        [$kode, $keluar] = $cocok('mahasiswa', $kurang);
        $this->assertSame(1, $kode, $keluar);
        $this->assertStringContainsString('BEDA   kelas UJX101 B: berkas 1 · AKTIF di basis data 2 · orang yang sama 1', $keluar);
        $this->assertStringContainsString('mahasiswa aktif di LokaAuth: 4 ↔ 5', $keluar);

        // Seorang mahasiswa mengundurkan diri: baris berkasnya disebut.
        $this->roster->query("UPDATE enrollments SET status = 'MENGUNDURKAN_DIRI' WHERE user_id = '{$d['m2']['id']}'");
        [$kode, $keluar] = $cocok('mahasiswa', $d['csvMhs']);
        $this->assertSame(1, $kode, $keluar);
        $this->assertStringContainsString('baris 3 (UJX101 A): tidakTerdaftarAktifDiKelasIni', $keluar);
        $this->roster->query("UPDATE enrollments SET status = 'AKTIF' WHERE user_id = '{$d['m2']['id']}'");

        // Orangnya tertukar kelas, jumlah per kelas tetap sama.
        $this->roster->query("UPDATE enrollments SET class_id = {$d['B']} WHERE user_id = '{$d['m1']['id']}'");
        $this->roster->query("UPDATE enrollments SET class_id = {$d['A']} WHERE user_id = '{$d['m4']['id']}'");
        [$kode, $keluar] = $cocok('mahasiswa', $d['csvMhs']);
        $this->assertSame(1, $kode, $keluar);
        $this->assertStringContainsString('BEDA   kelas UJX101 A: berkas 3 · AKTIF di basis data 3 · orang yang sama 2', $keluar);
        $this->roster->query("UPDATE enrollments SET class_id = {$d['A']} WHERE user_id = '{$d['m1']['id']}'");
        $this->roster->query("UPDATE enrollments SET class_id = {$d['B']} WHERE user_id = '{$d['m4']['id']}'");
        $this->assertSame(0, $cocok('mahasiswa', $d['csvMhs'])[0]);

        // Akun dinonaktifkan, atau ada mahasiswa aktif di luar berkas.
        $this->idn->query("UPDATE users SET status = 'suspended' WHERE id = '{$d['m5']['id']}'");
        [$kode, $keluar] = $cocok('mahasiswa', $d['csvMhs']);
        $this->assertSame(1, $kode, $keluar);
        $this->assertStringContainsString('baris 6: akunTidakAktif', $keluar);
        $this->idn->query("UPDATE users SET status = 'active' WHERE id = '{$d['m5']['id']}'");
        $luar = $this->user('mahasiswa', 'Mahasiswa Di Luar Berkas');
        [$kode, $keluar] = $cocok('mahasiswa', $d['csvMhs']);
        $this->assertSame(1, $kode, $keluar);
        $this->assertStringContainsString('mahasiswa aktif di LokaAuth: 5 ↔ 6', $keluar);

        // Kelas sisa (di luar berkas) dengan pendaftaran aktif dan pengajar.
        $sisa = $this->kelas($d['matkul'], $d['tahun'], '7A');
        $this->idn->query("UPDATE users SET status = 'disabled' WHERE id = '{$luar['id']}'");
        $this->assertSame(0, $cocok('mahasiswa', $d['csvMhs'])[0]);
        $ujy = $this->matkul('UJY201', 'Uji Y', $d['d1']);
        $lainKelas = $this->kelas($ujy, $d['tahun'], 'Z');
        $this->daftar($d['m1'], $lainKelas);
        [$kode, $keluar] = $cocok('mahasiswa', $d['csvMhs']);
        $this->assertSame(1, $kode, $keluar);
        $this->assertStringContainsString('semua pendaftaran AKTIF tahun ajaran aktif: 5 ↔ 6', $keluar);

        [$kode, $keluar] = $cocok('pengajar', $d['csvAjar']);
        $this->assertSame(1, $kode, $keluar);
        $this->assertStringContainsString('kelas di berkas ↔ kelas aktif tahun ajaran aktif: 2 ↔ 4', $keluar);
        $this->roster->query("UPDATE classes SET archived_at = UTC_TIMESTAMP() WHERE id IN ({$sisa}, {$lainKelas})");
        $this->assertSame(0, $cocok('pengajar', $d['csvAjar'])[0]);

        // Pengajar yang salah orang, dan pengajar tambahan.
        $this->roster->query("UPDATE class_teachers SET user_id = '{$d['d1']['id']}' WHERE class_id = {$d['B']}");
        [$kode, $keluar] = $cocok('pengajar', $d['csvAjar']);
        $this->assertSame(1, $kode, $keluar);
        $this->assertStringContainsString('BEDA   kelas UJX101 B: berkas 1 pengajar · basis data 1 (orangnya berbeda)', $keluar);
        $this->ajar($d['B'], $d['d2']);
        [$kode, $keluar] = $cocok('pengajar', $d['csvAjar']);
        $this->assertSame(1, $kode, $keluar);
        $this->assertStringContainsString('semua penugasan di kelas aktif: 2 ↔ 3', $keluar);

        // Dosen: akun di luar berkas, atau akun berkas yang tidak aktif.
        $this->assertSame(0, $cocok('dosen', $d['csvDosen'])[0]);
        $this->user('dosen', 'Dosen Di Luar Berkas');
        [$kode, $keluar] = $cocok('dosen', $d['csvDosen']);
        $this->assertSame(1, $kode, $keluar);
        $this->assertStringContainsString('dosen aktif di LokaAuth: 2 ↔ 3', $keluar);
    }

    public function testPemakaianSalahDitolak(): void
    {
        $d = $this->awal();
        [$kode] = $this->spark(['data:cocokkan', 'nilai'], $d['csvMhs']);
        $this->assertSame(1, $kode);
        [$kode] = $this->spark(['data:cocokkan', 'pengajar'], '');
        $this->assertSame(1, $kode);
        [$kode, $keluar] = $this->spark(['data:cocokkan', 'mahasiswa'], "nim;kelas\n1;A\n");
        $this->assertSame(1, $kode);
        $this->assertStringContainsString('kolom tidak ada di baris judul: nomor_induk, mata_kuliah', $keluar);
    }
}
