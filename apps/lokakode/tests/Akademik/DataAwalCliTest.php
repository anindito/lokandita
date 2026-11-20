<?php

declare(strict_types=1);

namespace Tests\Akademik;

use Tests\Support\KodeCase;
use Tests\Support\SoalBantu;

/**
 * `spark data:pengajar`, `data:pendaftaran` dan `data:cocokkan` (SPEC.md §17 dan
 * gerbang §18 tahap 12): data dari berkas masuk lewat aturan yang sama dengan
 * halaman web, dan pencocokan jumlah benar-benar gagal bila ada yang berbeda.
 */
final class DataAwalCliTest extends KodeCase
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
     * @param array<string, mixed> $d
     */
    private function isiSemua(array $d): void
    {
        foreach ([['data:pengajar', $d['csvAjar']], ['data:pendaftaran', $d['csvMhs']]] as [$perintah, $csv]) {
            [$kode, $keluar] = $this->spark([$perintah, '--oleh', $d['oleh'], '--jalankan'], $csv);
            $this->assertSame(0, $kode, $keluar);
        }
    }

    public function testPratinjauLaluJalankanLaluCocokDanUlanganTidakMengubah(): void
    {
        $d = $this->awal();

        // Sebelum apa pun ditulis, pencocokan harus gagal: gerbang tidak boleh lulus pada basis data kosong.
        [$kode, $keluar] = $this->spark(['data:cocokkan', 'mahasiswa'], $d['csvMhs']);
        $this->assertSame(1, $kode, $keluar);
        $this->assertStringContainsString('data:cocokkan mahasiswa: BEDA', $keluar);
        [$kode] = $this->spark(['data:cocokkan', 'pengajar'], $d['csvAjar']);
        $this->assertSame(1, $kode);

        // Pratinjau tidak menulis.
        [$kode, $keluar] = $this->spark(['data:pengajar', '--oleh', $d['oleh']], $d['csvAjar']);
        $this->assertSame(0, $kode, $keluar);
        $this->assertStringContainsString('penugasan baru: 2 · sudah ada: 0 · bermasalah: 0', $keluar);
        [$kode, $keluar] = $this->spark(['data:pendaftaran', '--oleh', $d['oleh']], $d['csvMhs']);
        $this->assertSame(0, $kode, $keluar);
        $this->assertStringContainsString('berkas: 5 baris data', $keluar);
        $this->assertStringContainsString('pendaftaran baru: 5 · diaktifkan lagi: 0 · sudah terdaftar: 0 · bermasalah: 0', $keluar);
        $this->assertStringContainsString('UJX101 A: 3 di berkas — 3 baru', $keluar);
        $this->assertStringContainsString('PRATINJAU', $keluar);
        // Keluaran tidak memuat data pribadi.
        $this->assertStringNotContainsString((string) $d['m1']['nomor_induk'], $keluar);
        $this->assertStringNotContainsString('Mahasiswa Nomor', $keluar);
        $this->assertSame(0, $this->hitung('SELECT COUNT(*) FROM enrollments') + $this->hitung('SELECT COUNT(*) FROM class_teachers') + $this->hitung('SELECT COUNT(*) FROM audit_log'));

        // Menulis.
        [$kode, $keluar] = $this->spark(['data:pengajar', '--oleh', $d['oleh'], '--jalankan'], $d['csvAjar']);
        $this->assertSame(0, $kode, $keluar);
        $this->assertStringContainsString('DITULIS: 2.', $keluar);
        [$kode, $keluar] = $this->spark(['data:pendaftaran', '--oleh', $d['oleh'], '--jalankan'], $d['csvMhs']);
        $this->assertSame(0, $kode, $keluar);
        $this->assertStringContainsString('DITULIS: 5.', $keluar);

        $this->assertSame(3, $this->hitung("SELECT COUNT(*) FROM enrollments WHERE class_id = {$d['A']} AND status = 'AKTIF'"));
        $this->assertSame(2, $this->hitung("SELECT COUNT(*) FROM enrollments WHERE class_id = {$d['B']} AND status = 'AKTIF'"));
        $this->assertSame((string) $d['B'], $this->row("SELECT class_id FROM enrollments WHERE user_id = '{$d['m4']['id']}'")['class_id']);
        $this->assertSame($d['d2']['id'], $this->row("SELECT user_id FROM class_teachers WHERE class_id = {$d['B']}")['user_id']);

        // Audit seperti lewat halaman web, atas nama superadmin itu, bertanda lewat cli.
        $audit = $this->db->query('SELECT action, actor_id, detail FROM audit_log')->fetch_all(MYSQLI_ASSOC);
        $this->assertSame(['pengajar.ditugaskan' => 2, 'pendaftaran.dibuat' => 5], array_count_values(array_column($audit, 'action')));
        foreach ($audit as $a) {
            $this->assertSame([$d['admin']['id'], 'cli'], [$a['actor_id'], json_decode($a['detail'], true)['lewat']]);
        }

        // Gerbang: ketiga berkas cocok dengan basis data.
        [$kode, $keluar] = $this->spark(['data:cocokkan', 'mahasiswa'], $d['csvMhs']);
        $this->assertSame(0, $kode, $keluar);
        $this->assertStringContainsString('COCOK  akun mahasiswa: nomor induk unik di berkas ↔ mahasiswa aktif di LokaAuth: 5 ↔ 5', $keluar);
        $this->assertStringContainsString('COCOK  kelas UJX101 A: berkas 3 · AKTIF di basis data 3 · orang yang sama 3', $keluar);
        $this->assertStringContainsString('COCOK  kelas UJX101 B: berkas 2 · AKTIF di basis data 2 · orang yang sama 2', $keluar);
        $this->assertStringContainsString('data:cocokkan mahasiswa: COCOK', $keluar);
        $this->assertStringNotContainsString('BEDA', $keluar);
        [$kode, $keluar] = $this->spark(['data:cocokkan', 'dosen'], $d['csvDosen']);
        $this->assertSame(0, $kode, $keluar);
        $this->assertStringContainsString('dosen aktif di LokaAuth: 2 ↔ 2', $keluar);
        [$kode, $keluar] = $this->spark(['data:cocokkan', 'pengajar'], $d['csvAjar']);
        $this->assertSame(0, $kode, $keluar);
        $this->assertStringContainsString('data:cocokkan pengajar: COCOK', $keluar);

        // Diulang: tidak ada yang baru, tidak ada audit baru.
        [$kode, $keluar] = $this->spark(['data:pendaftaran', '--oleh', $d['oleh'], '--jalankan'], $d['csvMhs']);
        $this->assertSame(0, $kode, $keluar);
        $this->assertStringContainsString('pendaftaran baru: 0 · diaktifkan lagi: 0 · sudah terdaftar: 5', $keluar);
        [$kode, $keluar] = $this->spark(['data:pengajar', '--oleh', $d['oleh'], '--jalankan'], $d['csvAjar']);
        $this->assertSame(0, $kode, $keluar);
        $this->assertStringContainsString('penugasan baru: 0 · sudah ada: 2', $keluar);
        $this->assertSame(7, $this->hitung('SELECT COUNT(*) FROM audit_log'));
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
        $this->db->query("UPDATE enrollments SET status = 'MENGUNDURKAN_DIRI' WHERE user_id = '{$d['m2']['id']}'");
        [$kode, $keluar] = $cocok('mahasiswa', $d['csvMhs']);
        $this->assertSame(1, $kode, $keluar);
        $this->assertStringContainsString('baris 3 (UJX101 A): tidakTerdaftarAktifDiKelasIni', $keluar);
        $this->db->query("UPDATE enrollments SET status = 'AKTIF' WHERE user_id = '{$d['m2']['id']}'");

        // Orangnya tertukar kelas, jumlah per kelas tetap sama.
        $this->db->query("UPDATE enrollments SET class_id = {$d['B']} WHERE user_id = '{$d['m1']['id']}'");
        $this->db->query("UPDATE enrollments SET class_id = {$d['A']} WHERE user_id = '{$d['m4']['id']}'");
        [$kode, $keluar] = $cocok('mahasiswa', $d['csvMhs']);
        $this->assertSame(1, $kode, $keluar);
        $this->assertStringContainsString('BEDA   kelas UJX101 A: berkas 3 · AKTIF di basis data 3 · orang yang sama 2', $keluar);
        $this->db->query("UPDATE enrollments SET class_id = {$d['A']} WHERE user_id = '{$d['m1']['id']}'");
        $this->db->query("UPDATE enrollments SET class_id = {$d['B']} WHERE user_id = '{$d['m4']['id']}'");
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
        $this->db->query("UPDATE classes SET archived_at = UTC_TIMESTAMP() WHERE id IN ({$sisa}, {$lainKelas})");
        $this->assertSame(0, $cocok('pengajar', $d['csvAjar'])[0]);

        // Pengajar yang salah orang, dan pengajar tambahan.
        $this->db->query("UPDATE class_teachers SET user_id = '{$d['d1']['id']}' WHERE class_id = {$d['B']}");
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

    public function testBarisBermasalahDisebutDanMembatalkanSeluruhPenulisan(): void
    {
        $d = $this->awal();
        $n = fn (string $k): string => (string) $d[$k]['nomor_induk'];
        $this->daftar($d['m3'], $d['B']); // sudah aktif di kelas lain mata kuliah yang sama

        $csv = "nomor_induk,nama,global_role,mata_kuliah,kelas\n"
            . "{$n('m1')},x,mahasiswa,UJX101,A\n"          // 2: baik
            . "{$n('m2')},x,mahasiswa,UJX101,C\n"          // 3: kelas tidak ada
            . "takterdaftar01,x,mahasiswa,UJX101,A\n"      // 4: akun tidak ada
            . "{$n('d2')},x,mahasiswa,UJX101,A\n"          // 5: bukan mahasiswa
            . "{$n('m3')},x,mahasiswa,UJX101,A\n"          // 6: aktif di kelas B
            . "{$n('m1')},x,mahasiswa,UJX101,B\n";         // 7: dua kelas dalam satu mata kuliah
        [$kode, $keluar] = $this->spark(['data:pendaftaran', '--oleh', $d['oleh'], '--jalankan'], $csv);
        $this->assertSame(1, $kode, $keluar);
        $this->assertStringContainsString('bermasalah: 5', $keluar);
        $this->assertStringContainsString('baris 3 (UJX101 C): kelasTidakAda', $keluar);
        $this->assertStringContainsString('baris 4 (UJX101 A): tidakAda', $keluar);
        $this->assertStringContainsString('baris 5 (UJX101 A): bukanMahasiswa', $keluar);
        $this->assertStringContainsString('baris 6 (UJX101 A): kelasLain', $keluar);
        $this->assertStringContainsString('baris 7 (UJX101 B): gandaDalamMataKuliah', $keluar);
        $this->assertStringContainsString('Tidak ada yang diubah.', $keluar);
        $this->assertSame(1, $this->hitung('SELECT COUNT(*) FROM enrollments'), 'baris yang baik pun tidak ditulis');

        $ajar = "mata_kuliah,kelas,nip_pengajar\nUJX101,A,{$n('d1')}\nUJX101,C,{$n('d1')}\nUJX101,B,takterdaftar01\nUJX101,B,{$n('m1')}\nUJX101,A,{$n('d1')}\n";
        [$kode, $keluar] = $this->spark(['data:pengajar', '--oleh', $d['oleh'], '--jalankan'], $ajar);
        $this->assertSame(1, $kode, $keluar);
        $this->assertStringContainsString('baris 3 (UJX101 C): kelasTidakAda', $keluar);
        $this->assertStringContainsString('baris 4 (UJX101 B): pengajarTidakAda', $keluar);
        $this->assertStringContainsString('baris 5 (UJX101 B): pengajarBukanDosen', $keluar);
        $this->assertStringContainsString('baris 6 (UJX101 A): barisGanda', $keluar);
        $this->assertSame(0, $this->hitung('SELECT COUNT(*) FROM class_teachers'));
        $this->assertSame(0, $this->hitung('SELECT COUNT(*) FROM audit_log'));
    }

    public function testMahasiswaYangMengundurkanDiriDiaktifkanLagiDiKelasBerkas(): void
    {
        $d = $this->awal();
        $lama  = $this->kelas($d['matkul'], $d['tahun'], '7A');
        $pernah = $this->daftar($d['m1'], $lama, 'MENGUNDURKAN_DIRI');
        $this->db->query("UPDATE classes SET archived_at = UTC_TIMESTAMP() WHERE id = {$lama}");

        [$kode, $keluar] = $this->spark(['data:pendaftaran', '--oleh', $d['oleh'], '--jalankan'], $d['csvMhs']);
        $this->assertSame(0, $kode, $keluar);
        $this->assertStringContainsString('pendaftaran baru: 4 · diaktifkan lagi: 1', $keluar);
        // Baris pendaftarannya tetap satu dan sama, kini di kelas A; perpindahannya tercatat.
        $this->assertSame(['id' => (string) $pernah, 'class_id' => (string) $d['A'], 'status' => 'AKTIF'],
            $this->row("SELECT id, class_id, status FROM enrollments WHERE user_id = '{$d['m1']['id']}'"));
        $this->assertSame(1, $this->hitung("SELECT COUNT(*) FROM enrollment_moves WHERE enrollment_id = {$pernah} AND from_class_id = {$lama} AND to_class_id = {$d['A']}"));
        $this->assertSame(0, $this->spark(['data:cocokkan', 'mahasiswa'], $d['csvMhs'])[0]);
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
        [$kode, $keluar] = $this->spark(['data:cocokkan', 'nilai'], $d['csvMhs']);
        $this->assertSame(1, $kode);
        [$kode, $keluar] = $this->spark(['data:cocokkan', 'pengajar'], '');
        $this->assertSame(1, $kode);

        $this->assertSame(0, $this->hitung('SELECT COUNT(*) FROM enrollments') + $this->hitung('SELECT COUNT(*) FROM class_teachers'));
    }
}
