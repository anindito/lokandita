<?php

declare(strict_types=1);

namespace Tests\Jadwal;

use Tests\Support\UjianCase;

/**
 * Penjadwal (KONTRAK.md §21) sebagai PROSES sungguhan pada skema tes dan Redis DB 15: denyutnya,
 * dan aturan "tepat satu penjadwal". Apa yang ditandainya diuji di tests/Kerja/PengerjaanTest.
 */
final class JadwalTest extends UjianCase
{
    private function redis(): \Redis
    {
        $u = parse_url((string) getenv('REDIS_URL'));
        $this->assertSame('15', ltrim((string) $u['path'], '/'), 'tes hanya memakai Redis DB 15');
        $r = new \Redis();
        $r->connect((string) $u['host'], (int) ($u['port'] ?? 6379));
        $r->auth((string) $u['pass']);
        $r->select(15);

        return $r;
    }

    /**
     * @return array{0: resource, 1: array<int, resource>}
     */
    private function jalankan(): array
    {
        $proses = proc_open(['php', 'spark', 'ujian:jadwal', '--no-header'], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipa, ROOTPATH);
        $this->assertIsResource($proses);
        stream_set_blocking($pipa[2], false);

        return [$proses, $pipa];
    }

    public function testDenyutDanHanyaSatuPenjadwal(): void
    {
        $redis = $this->redis();
        $redis->del('lokaujian:jadwal');
        [$satu, $pipa1] = $this->jalankan();
        try {
            $denyut = null;
            for ($i = 0; $i < 100 && $denyut === null; $i++) {
                usleep(100_000);
                $isi    = $redis->get('lokaujian:jadwal');
                $denyut = is_string($isi) ? json_decode($isi, true) : null;
            }
            $this->assertIsArray($denyut, 'penjadwal tidak menulis denyut dalam 10 detik');
            $this->assertSame((string) getenv('LOKANDITA_COMMIT'), $denyut['commit']);
            $this->assertSame(proc_get_status($satu)['pid'], $denyut['pid']);
            $this->assertSame(1, $denyut['lintasan']);
            foreach (['mulai', 'waktu'] as $k) {
                $this->assertMatchesRegularExpression('/^\d{4}-\d\d-\d\dT\d\d:\d\d:\d\dZ$/', $denyut[$k]);
            }
            $umur = $redis->ttl('lokaujian:jadwal');
            $this->assertGreaterThan(40, $umur);
            $this->assertLessThanOrEqual(60, $umur);

            // Penjadwal kedua melihat denyut yang MAJU dan keluar sendiri, dengan satu baris log.
            [$dua, $pipa2] = $this->jalankan();
            $selesai = false;
            for ($i = 0; $i < 350 && ! $selesai; $i++) {
                usleep(100_000);
                $selesai = ! proc_get_status($dua)['running'];
            }
            $log = (string) stream_get_contents($pipa2[2]);
            if (! $selesai) {
                proc_terminate($dua, 9);
            }
            $this->assertTrue($selesai, 'penjadwal kedua tidak keluar dalam 35 detik: ' . $log);
            $this->assertStringContainsString('penjadwal lain masih berdenyut', $log);
            $this->assertTrue(proc_get_status($satu)['running'], 'penjadwal pertama ikut berhenti');
            $kini = json_decode((string) $redis->get('lokaujian:jadwal'), true);
            $this->assertSame($denyut['pid'], $kini['pid']);
            $this->assertGreaterThan(1, $kini['lintasan']);
        } finally {
            proc_terminate($satu, 9);
            proc_close($satu);
            $redis->del('lokaujian:jadwal');
        }
        // Tanpa penjadwal: halaman sesi dosen mengatakannya.
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengampuX']);
        $u    = $this->ujianTerbit($http, $d['matkulX'], 'Ujian tanpa penjadwal');
        $sesi = $this->beri($http, $d['matkulX'], $u, $d['kelasXA']);
        $this->assertStringContainsString('data-penjadwal="mati"', $http->get("/sesi/{$sesi}")->body);
        $redis->setex('lokaujian:jadwal', 60, (string) json_encode(['commit' => 'x', 'pid' => 1, 'nama' => 'uji', 'mulai' => 'x', 'waktu' => 'x', 'lintasan' => 1]));
        $this->assertStringNotContainsString('data-penjadwal="mati"', $http->get("/sesi/{$sesi}")->body);
        $redis->del('lokaujian:jadwal');
    }

    public function testVariabelWajibYangHilangKeluar78(): void
    {
        $proses = proc_open(['env', '-u', 'REDIS_URL', 'php', 'spark', 'ujian:jadwal', '--sekali', '--no-header'], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipa, ROOTPATH);
        $this->assertIsResource($proses);
        $galat = (string) stream_get_contents($pipa[2]);
        $this->assertSame(78, proc_close($proses));
        $this->assertStringContainsString('REDIS_URL', $galat);
    }
}
