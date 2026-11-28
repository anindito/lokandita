<?php

declare(strict_types=1);

namespace Tests\Roster;

use Lokandita\Auth\Testing\RosterFixtures;
use Tests\Support\RosterCase;

/**
 * Setiap rute roster hanya untuk superadmin (SPEC.md §3, tahap A2), diperiksa di server.
 * Pengampu mata kuliah itu, pengajar kelas itu dan mahasiswa kelas itu sama-sama ditolak —
 * untuk kelasnya sendiri maupun kelas orang lain.
 */
final class HakRosterTest extends RosterCase
{
    /**
     * @return list<array{0: string, 1: string}>
     */
    private function rute(int $tahun, int $matkul, int $kelas): array
    {
        return [
            ['GET', '/admin/roster'],
            ['GET', '/admin/tahun-ajaran'],
            ['POST', '/admin/tahun-ajaran'],
            ['GET', '/admin/tahun-ajaran/baru'],
            ['POST', '/admin/tahun-ajaran/baru'],
            ['POST', '/admin/tahun-ajaran/baru/jalankan'],
            ['GET', "/admin/tahun-ajaran/{$tahun}"],
            ['POST', "/admin/tahun-ajaran/{$tahun}"],
            ['POST', "/admin/tahun-ajaran/{$tahun}/aktifkan"],
            ['GET', '/admin/mata-kuliah'],
            ['GET', '/admin/mata-kuliah/baru'],
            ['POST', '/admin/mata-kuliah'],
            ['GET', "/admin/mata-kuliah/{$matkul}"],
            ['GET', "/admin/mata-kuliah/{$matkul}/ubah"],
            ['POST', "/admin/mata-kuliah/{$matkul}"],
            ['POST', "/admin/mata-kuliah/{$matkul}/kelas"],
            ['GET', "/admin/kelas/{$kelas}"],
            ['GET', "/admin/kelas/{$kelas}/ubah"],
            ['POST', "/admin/kelas/{$kelas}"],
            ['POST', "/admin/kelas/{$kelas}/arsip"],
            ['POST', "/admin/kelas/{$kelas}/hapus"],
            ['POST', "/admin/kelas/{$kelas}/pengajar"],
            ['POST', "/admin/kelas/{$kelas}/pengajar/hapus"],
            ['POST', "/admin/kelas/{$kelas}/daftar"],
            ['POST', "/admin/kelas/{$kelas}/daftar/jalankan"],
            ['POST', "/admin/kelas/{$kelas}/mundur"],
            ['POST', "/admin/kelas/{$kelas}/pindah"],
            ['POST', "/admin/kelas/{$kelas}/lulus"],
            ['POST', "/admin/kelas/{$kelas}/batal-lulus"],
        ];
    }

    public function testDaftarRuteDiTesIniSamaDenganRoutesPhp(): void
    {
        preg_match_all("/routes->(get|post)\('([^']+)', 'Admin\\\\Roster\\\\/", (string) file_get_contents('app/Config/Routes.php'), $m, PREG_SET_ORDER);
        $diRute = array_map(static fn ($x) => strtoupper($x[1]) . ' /admin/' . str_replace('(:num)', '1', $x[2]), $m);
        $diTes  = array_map(static fn ($x) => $x[0] . ' ' . $x[1], $this->rute(1, 1, 1));
        sort($diRute);
        sort($diTes);
        $this->assertNotSame([], $diRute);
        $this->assertSame($diRute, $diTes, 'Setiap rute roster di Routes.php harus ada di tes hak ini');
    }

    public function testTanpaSesiDialihkanKeLoginDanPostDitolak(): void
    {
        $d     = $this->dasar();
        $audit = $this->jumlahAuditRoster();
        foreach ($this->rute($d['tahun'], $d['matkul'], $d['a']) as [$metode, $jalur]) {
            if ($metode === 'GET') {
                $r = $this->http()->get($jalur);
                $this->assertContains($r->status, [302, 303], $jalur);
                $this->assertStringStartsWith('https://auth.lokandita.com/login?next=', (string) $r->header('Location'), $jalur);
            } else {
                $this->assertSame(403, $this->http()->post($jalur, ['name' => 'Selundup', 'arsip' => '1'])->status, $jalur);
            }
        }
        $this->assertSame($audit, $this->jumlahAuditRoster());
    }

    public function testPengampuPengajarDanMahasiswaDitolak403DiSetiapRute(): void
    {
        $d        = $this->dasar();
        $pengajar = $this->user(['global_role' => 'dosen', 'nama' => 'Dosen Pengajar A']);
        $mhs      = $this->user(['global_role' => 'mahasiswa', 'nama' => 'Mahasiswa Kelas A']);
        RosterFixtures::pengajar($this->roster, $d['a'], $pengajar['id']);
        $daftar = RosterFixtures::daftar($this->roster, $d['a'], $mhs['id']);
        // Nomor induk pelaku sendiri tampil di menu akun; yang tidak boleh bocor adalah milik orang lain.
        $lain = $this->user(['global_role' => 'mahasiswa', 'nama' => 'Mahasiswa Lain']);
        RosterFixtures::daftar($this->roster, $d['a'], $lain['id']);
        $sebelum = $this->cap();
        $audit   = $this->jumlahAuditRoster();

        // Kelas A adalah kelas mereka sendiri; kelas B kelas orang lain. Keduanya ditolak sama.
        foreach (['pengampu' => $d['pengampu'], 'pengajar' => $pengajar, 'mahasiswa' => $mhs] as $siapa => $pelaku) {
            $http = $this->sebagai($pelaku);
            $csrf = $this->csrf($http);
            $this->assertNotSame('', $csrf);
            foreach ([$d['a'], $d['b']] as $kelas) {
                foreach ($this->rute($d['tahun'], $d['matkul'], $kelas) as [$metode, $jalur]) {
                    $r = $metode === 'GET' ? $http->get($jalur) : $http->post($jalur, [
                        'csrf' => $csrf, 'label' => 'Selundupan 2030', 'starts_on' => '2030-01-01', 'ends_on' => '2030-06-30',
                        'code' => 'SLD1', 'name' => 'Selundupan', 'pengampu' => $pelaku['nomor_induk'], 'capacity' => '5',
                        'arsip' => '1', 'yakin' => '1', 'nomor_induk' => $pelaku['nomor_induk'], 'user_id' => $pengajar['id'],
                        'nomor' => [$mhs['nomor_induk']], 'ids' => [$daftar], 'tujuan' => $d['b'], 'tanggal' => '2026-12-01', 'semua' => '1',
                    ]);
                    $this->assertSame(403, $r->status, "{$siapa} {$metode} {$jalur}");
                    $this->assertStringNotContainsString($lain['nomor_induk'], $r->body, "{$siapa} {$metode} {$jalur} membocorkan data");
                    $this->assertStringNotContainsString('UJI101', $r->body, "{$siapa} {$metode} {$jalur} membocorkan data");
                }
            }
            $this->assertSame($pelaku['id'], $this->lastAudit('access.denied')['actor_id']);
        }

        $this->assertSame($sebelum, $this->cap(), 'Tidak ada baris roster yang berubah');
        $this->assertSame($audit, $this->jumlahAuditRoster());
    }

    public function testSuperadminMembukaSetiapHalaman(): void
    {
        $d      = $this->dasar();
        [$http] = $this->admin();
        foreach ($this->rute($d['tahun'], $d['matkul'], $d['a']) as [$metode, $jalur]) {
            if ($metode === 'GET') {
                $this->assertSame(200, $http->get($jalur)->status, $jalur . ' — ' . substr(strip_tags($http->body), 0, 300));
            }
        }
        $this->assertSame(404, $http->get('/admin/kelas/999999')->status);
        $this->assertSame(404, $http->get('/admin/mata-kuliah/999999')->status);
        $this->assertSame(404, $http->get('/admin/tahun-ajaran/999999')->status);
    }

    /**
     * Sidik seluruh isi keenam tabel roster.
     */
    private function cap(): string
    {
        $isi = '';
        foreach (RosterFixtures::TABEL as $t) {
            $isi .= $t . json_encode($this->roster->query("SELECT * FROM `{$t}` ORDER BY 1, 2")->fetch_all());
        }

        return hash('sha256', $isi);
    }
}
