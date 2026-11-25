<?php

declare(strict_types=1);

namespace Tests\Sesi;

use Tests\Support\UjianCase;

/**
 * Sesi dari sisi staf (SPEC-LOKAUJIAN.md §3, §7; KONTRAK.md §22), lewat HTTP sungguhan: memberikan
 * ujian ke kelas, kedua mode waktu dengan kendali paling sedikit, dan lingkup kelas — pengajar
 * kelas A tidak pernah menyentuh sesi kelas B.
 */
final class SesiTest extends UjianCase
{
    /**
     * @return array<string, mixed>
     */
    private function sesiRow(int $id): array
    {
        return (array) $this->db->query("SELECT s.*, UTC_TIMESTAMP() AS kini FROM sessions s WHERE id = {$id}")->fetch_assoc();
    }

    public function testMemberikanUjianKeKelas(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengampuX']);
        $x    = '/mata-kuliah/' . $d['matkulX'];
        $u    = $this->ujianTerbit($http, $d['matkulX'], 'Kuis jadwal', ['mode' => 'per_mahasiswa', 'durasi' => '20']);
        $ini  = "{$x}/ujian/{$u}";

        // per_mahasiswa wajib berjadwal, dan tutup harus sesudah buka.
        $this->assertStringEndsWith('?pesan=jadwal', (string) $this->kirim($http, $ini . '/sesi', ['kelas' => (string) $d['kelasXA']])->header('Location'));
        $this->assertStringEndsWith('?pesan=jadwal', (string) $this->kirim($http, $ini . '/sesi', ['kelas' => (string) $d['kelasXA'], 'buka' => '2026-12-01T10:00', 'tutup' => '2026-12-01T09:00'])->header('Location'));
        $this->assertSame('0', (string) $this->db->query('SELECT COUNT(*) AS n FROM sessions')->fetch_assoc()['n']);

        // Waktu form adalah WIB; yang tersimpan UTC.
        $sesi = $this->beri($http, $d['matkulX'], $u, $d['kelasXA'], '2026-12-01T10:00', '2026-12-01T12:00');
        $b    = $this->sesiRow($sesi);
        $this->assertSame(['2026-12-01 03:00:00', '2026-12-01 05:00:00'], [$b['open_at'], $b['close_at']]);
        $audit = $this->db->query("SELECT class_id, course_id FROM audit_log WHERE action = 'sesi.dibuat' AND target_id = '{$sesi}'")->fetch_assoc();
        $this->assertSame([(string) $d['kelasXA'], (string) $d['matkulX']], [(string) $audit['class_id'], (string) $audit['course_id']]);

        // Paling banyak satu sesi per ujian per kelas; kelas mata kuliah lain, kelas yang diarsipkan
        // dan kelas tahun ajaran lama ditolak.
        $jadwal = ['buka' => '2026-12-02T10:00', 'tutup' => '2026-12-02T12:00'];
        $this->assertStringEndsWith('?pesan=sudah_ada', (string) $this->kirim($http, $ini . '/sesi', ['kelas' => (string) $d['kelasXA']] + $jadwal)->header('Location'));
        foreach (['kelasYA', 'kelasXArsip', 'kelasXLama'] as $k) {
            $this->assertStringEndsWith('?pesan=kelas_asing', (string) $this->kirim($http, $ini . '/sesi', ['kelas' => (string) $d[$k]] + $jadwal)->header('Location'), $k);
        }
        $this->assertSame('1', (string) $this->db->query('SELECT COUNT(*) AS n FROM sessions')->fetch_assoc()['n']);

        // Draf tidak bisa diberikan.
        $draf = $this->buatDraf($http, $d['matkulX'], 'Masih draf', ['s' . (int) $this->db->query("SELECT id FROM questions WHERE text LIKE 'URAIAN:%'")->fetch_assoc()['id']]);
        $this->assertStringEndsWith('?pesan=bukan_terbit', (string) $this->kirim($http, "{$x}/ujian/{$draf}/sesi", ['kelas' => (string) $d['kelasXB']])->header('Location'));

        // Halaman ujian menampilkan sesinya; halaman kelas menampilkannya kepada pengajarnya.
        $this->assertStringContainsString("/sesi/{$sesi}", $http->get($ini)->body);
        $this->assertStringContainsString("/sesi/{$sesi}", $this->sebagai($d['pengajarA'])->get('/kelas/' . $d['kelasXA'])->body);
    }

    public function testPerMahasiswaBukaTutupDanJadwal(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengampuX']);
        $u    = $this->ujianTerbit($http, $d['matkulX'], 'Kuis buka tutup', ['mode' => 'per_mahasiswa', 'durasi' => '20']);
        $sesi = $this->beri($http, $d['matkulX'], $u, $d['kelasXA'], '2031-01-01T10:00', '2031-01-01T12:00');
        $ajar = $this->sebagai($d['pengajarA']);

        $this->assertStringContainsString('data-keadaan="terjadwal"', $ajar->get("/sesi/{$sesi}")->body);
        // Pengajar membuka dengan tangan: terbuka sejak sekarang, waktu tutup yang masih di depan tetap.
        $this->assertStringEndsWith('?pesan=dibuka', (string) $this->kirim($ajar, "/sesi/{$sesi}/buka")->header('Location'));
        $b = $this->sesiRow($sesi);
        $this->assertLessThanOrEqual(3, abs(strtotime($b['open_at']) - strtotime($b['kini'])));
        $this->assertSame('2031-01-01 05:00:00', $b['close_at']);
        $this->assertStringContainsString('data-keadaan="terbuka"', $ajar->get("/sesi/{$sesi}")->body);
        $this->assertStringEndsWith('?pesan=g_sudah', (string) $this->kirim($ajar, "/sesi/{$sesi}/buka")->header('Location'));
        // Menutup dengan tangan.
        $this->assertStringEndsWith('?pesan=ditutup', (string) $this->kirim($ajar, "/sesi/{$sesi}/tutup")->header('Location'));
        $this->assertStringContainsString('data-keadaan="tertutup"', $ajar->get("/sesi/{$sesi}")->body);
        // Membuka sesi yang waktu tutupnya sudah lewat: terbuka sampai ditutup.
        $this->kirim($ajar, "/sesi/{$sesi}/buka");
        $this->assertNull($this->sesiRow($sesi)['close_at']);
        // Tindakan mode lain tidak berlaku.
        $this->assertStringEndsWith('?pesan=g_mode', (string) $this->kirim($ajar, "/sesi/{$sesi}/mulai")->header('Location'));

        // Jadwal: pengampu boleh; bagi pengajar tindakan itu tidak ada.
        $sebelum = $this->sidikBank();
        $this->assertSame(404, $this->kirim($ajar, "/sesi/{$sesi}/jadwal", ['buka' => '2031-02-01T10:00', 'tutup' => '2031-02-01T11:00'])->status);
        $this->assertSame(404, $this->kirim($ajar, "/sesi/{$sesi}/hapus")->status);
        $this->assertSame($sebelum, $this->sidikBank(), 'pengajar mengubah jadwal atau menghapus sesi');
        $this->assertStringEndsWith('?pesan=dijadwalkan', (string) $this->kirim($http, "/sesi/{$sesi}/jadwal", ['buka' => '2031-02-01T10:00', 'tutup' => '2031-02-01T11:00'])->header('Location'));
        $this->assertSame('2031-02-01 03:00:00', $this->sesiRow($sesi)['open_at']);

        foreach (['sesi.dibuka' => 2, 'sesi.ditutup' => 1, 'sesi.dijadwalkan' => 1] as $aksi => $n) {
            $this->assertSame((string) $n, (string) $this->db->query("SELECT COUNT(*) AS n FROM audit_log WHERE action = '{$aksi}' AND target_id = '{$sesi}'")->fetch_assoc()['n'], $aksi);
        }
        // Sesi tanpa pengerjaan boleh dihapus pengampunya.
        $this->assertStringEndsWith('?pesan=sesi_dihapus', (string) $this->kirim($http, "/sesi/{$sesi}/hapus")->header('Location'));
        $this->assertSame('0', (string) $this->db->query('SELECT COUNT(*) AS n FROM sessions')->fetch_assoc()['n']);
    }

    public function testDikendalikanDosenMulaiAturAkhirAkhiri(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengampuX']);
        $u    = $this->ujianTerbit($http, $d['matkulX'], 'Ujian dosen');
        $sesi = $this->beri($http, $d['matkulX'], $u, $d['kelasXA']);
        $ajar = $this->sebagai($d['pengajarA']);
        $selisih = function (string $kolom) use ($sesi): int {
            $b = $this->sesiRow($sesi);

            return strtotime((string) $b[$kolom]) - strtotime((string) $b['kini']);
        };

        $this->assertStringContainsString('data-keadaan="menunggu"', $ajar->get("/sesi/{$sesi}")->body);
        $this->assertStringEndsWith('?pesan=g_sudah', (string) $this->kirim($ajar, "/sesi/{$sesi}/akhiri")->header('Location'));
        // Mulai tanpa waktu akhir: berlangsung, berakhir sendiri 8 jam kemudian.
        $this->assertStringEndsWith('?pesan=dimulai', (string) $this->kirim($ajar, "/sesi/{$sesi}/mulai")->header('Location'));
        $b = $this->sesiRow($sesi);
        $this->assertNotNull($b['started_at']);
        $this->assertNull($b['end_at']);
        $this->assertStringContainsString('data-keadaan="berlangsung"', $ajar->get("/sesi/{$sesi}")->body);
        $this->assertStringEndsWith('?pesan=g_sudah', (string) $this->kirim($ajar, "/sesi/{$sesi}/mulai")->header('Location'));

        // Menetapkan akhir: N menit dari sekarang; lalu diubah.
        $this->assertStringEndsWith('?pesan=g_akhir', (string) $this->kirim($ajar, "/sesi/{$sesi}/akhir", ['menit' => '0'])->header('Location'));
        $this->assertStringEndsWith('?pesan=akhir', (string) $this->kirim($ajar, "/sesi/{$sesi}/akhir", ['menit' => '90'])->header('Location'));
        $this->assertEqualsWithDelta(5400, $selisih('end_at'), 5);
        $this->kirim($ajar, "/sesi/{$sesi}/akhir", ['menit' => '45']);
        $this->assertEqualsWithDelta(2700, $selisih('end_at'), 5);
        // Jam dinding WIB.
        $jam = (new \DateTimeImmutable('+2 hours', new \DateTimeZone('Asia/Jakarta')))->format('H:i');
        $this->kirim($ajar, "/sesi/{$sesi}/akhir", ['jam' => $jam]);
        $this->assertEqualsWithDelta(7200, $selisih('end_at'), 61);

        // Akhiri: 30 detik dari sekarang, dengan pelakunya; tidak bisa diubah lagi sesudahnya.
        $this->assertStringEndsWith('?pesan=diakhiri', (string) $this->kirim($ajar, "/sesi/{$sesi}/akhiri")->header('Location'));
        $this->assertEqualsWithDelta(30, $selisih('end_at'), 3);
        $this->assertSame((string) $d['pengajarA']['id'], $this->sesiRow($sesi)['ended_by']);
        $this->assertStringContainsString('data-keadaan="diakhiri"', $ajar->get("/sesi/{$sesi}")->body);
        $this->assertStringEndsWith('?pesan=g_sudah', (string) $this->kirim($ajar, "/sesi/{$sesi}/akhir", ['menit' => '60'])->header('Location'));
        $this->assertEqualsWithDelta(30, $selisih('end_at'), 4);
        // Sesudah akhirnya lewat: selesai.
        $this->db->query("UPDATE sessions SET started_at = started_at - INTERVAL 1 HOUR, end_at = UTC_TIMESTAMP() - INTERVAL 1 SECOND WHERE id = {$sesi}");
        $this->assertStringContainsString('data-keadaan="selesai"', $ajar->get("/sesi/{$sesi}")->body);

        foreach (['sesi.dimulai' => 1, 'sesi.akhir_ditetapkan' => 3, 'sesi.diakhiri' => 1] as $aksi => $n) {
            $this->assertSame((string) $n, (string) $this->db->query("SELECT COUNT(*) AS n FROM audit_log WHERE action = '{$aksi}' AND target_id = '{$sesi}'")->fetch_assoc()['n'], $aksi);
        }
    }

    public function testSesiBerakhirSendiriDelapanJamSesudahMulai(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengampuX']);
        $u    = $this->ujianTerbit($http, $d['matkulX'], 'Ujian lupa diakhiri');
        $sesi = $this->beri($http, $d['matkulX'], $u, $d['kelasXA']);
        $this->kirim($http, "/sesi/{$sesi}/mulai");
        $this->db->query("UPDATE sessions SET started_at = UTC_TIMESTAMP() - INTERVAL 7 HOUR - INTERVAL 59 MINUTE WHERE id = {$sesi}");
        $this->assertStringContainsString('data-keadaan="berlangsung"', $http->get("/sesi/{$sesi}")->body);
        $this->db->query("UPDATE sessions SET started_at = UTC_TIMESTAMP() - INTERVAL 8 HOUR - INTERVAL 1 SECOND WHERE id = {$sesi}");
        $this->assertStringContainsString('data-keadaan="selesai"', $http->get("/sesi/{$sesi}")->body);
        // Waktu akhir tidak bisa melewati 8 jam sejak mulai.
        $this->db->query("UPDATE sessions SET started_at = UTC_TIMESTAMP() - INTERVAL 7 HOUR WHERE id = {$sesi}");
        $this->kirim($http, "/sesi/{$sesi}/akhir", ['menit' => '300']);
        $b = $this->sesiRow($sesi);
        $this->assertSame(8 * 3600, strtotime($b['end_at']) - strtotime($b['started_at']));
    }

    /**
     * Gerbang lingkup: pengajar B dan pengampu mata kuliah lain tidak melihat dan tidak mengubah
     * sesi kelas XA; jawabannya sama dengan sesi yang tidak ada.
     */
    public function testSesiKelasLainTidakTerlihatDanTidakTersentuh(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengampuX']);
        $u    = $this->ujianTerbit($http, $d['matkulX'], 'Rahasia sesi XA');
        $sesi = $this->beri($http, $d['matkulX'], $u, $d['kelasXA']);

        foreach (['pengajarB', 'pengampuY'] as $siapa) {
            $asing   = $this->sebagai($d[$siapa]);
            $ada     = self::inti($asing->get("/sesi/{$sesi}")->body, "/sesi/{$sesi}");
            $tidak   = self::inti($asing->get('/sesi/99999999')->body, '/sesi/99999999');
            $this->assertSame(404, $asing->get("/sesi/{$sesi}")->status, $siapa);
            $this->assertSame($tidak, $ada, "{$siapa}: sesi kelas lain harus dijawab sama dengan sesi yang tidak ada");
            $this->assertStringNotContainsString('Rahasia sesi XA', $ada);
            $this->assertSame(404, $asing->get("/sesi/{$sesi}/pratinjau")->status);
            $sebelum = $this->sidikBank();
            foreach (['mulai', 'akhir', 'akhiri', 'buka', 'tutup', 'jadwal', 'hapus'] as $aksi) {
                $this->assertSame(404, $this->kirim($asing, "/sesi/{$sesi}/{$aksi}", ['menit' => '5'])->status, "{$siapa} {$aksi}");
            }
            $this->assertSame($sebelum, $this->sidikBank(), "{$siapa} mengubah sesi kelas lain");
        }
        // Pengajar yang penugasannya dicabut ditolak pada permintaan berikutnya.
        $ajar = $this->sebagai($d['pengajarA']);
        $this->assertSame(200, $ajar->get("/sesi/{$sesi}")->status);
        $this->assertSame(200, $ajar->get("/sesi/{$sesi}/pratinjau")->status);
        $this->roster->query("DELETE FROM class_teachers WHERE class_id = {$d['kelasXA']}");
        $this->assertSame(404, $ajar->get("/sesi/{$sesi}")->status);
        $this->assertSame(404, $this->kirim($ajar, "/sesi/{$sesi}/mulai")->status);
        $this->assertNull($this->sesiRow($sesi)['started_at']);
    }

    public function testPratinjauPengajarTanpaKunci(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengampuX']);
        $u    = $this->ujianTerbit($http, $d['matkulX'], 'Ujian pratinjau');
        $sesi = $this->beri($http, $d['matkulX'], $u, $d['kelasXA']);
        $r    = $this->sebagai($d['pengajarA'])->get("/sesi/{$sesi}/pratinjau");
        $this->assertSame(200, $r->status);
        $this->assertSame(6, substr_count($r->body, '<article class="soal"'));
        $this->assertStringNotContainsString(' checked', $r->body);
        $this->assertStringNotContainsString('kunci-staf', $r->body);
        // Pengajar tetap tidak punya jalan ke bank atau ke halaman ujian pengampu.
        $this->assertSame(403, $this->sebagai($d['pengajarA'])->get('/mata-kuliah/' . $d['matkulX'] . "/ujian/{$u}")->status);
    }
}
