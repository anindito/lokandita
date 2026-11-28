<?php

declare(strict_types=1);

namespace Tests\Akademik;

use Tests\Support\KodeCase;

/**
 * Tahap A2 (KONTRAK.md §18): roster dikelola di Akun Lokandita. Di LokaKode alamat lamanya hanya
 * mengalihkan superadmin ke sana, tidak ada yang mengubah roster, dan halaman kelas serta mata
 * kuliah hanya membaca. Bahwa MySQL sendiri menolak penulisan LokaKode: tests/Schema/ViewRosterTest.php.
 */
final class RosterHanyaBacaTest extends KodeCase
{
    private function akun(string $jalur): string
    {
        return rtrim((string) getenv('AUTH_PUBLIC_URL'), '/') . '/admin/' . $jalur;
    }

    /**
     * Isi keenam tabel roster, untuk membuktikan bahwa tidak ada yang berubah.
     *
     * @return array<string, list<array<int, mixed>>>
     */
    private function isiRoster(): array
    {
        $isi = [];
        foreach (['academic_years', 'courses', 'classes', 'class_teachers', 'enrollments', 'enrollment_moves'] as $t) {
            $isi[$t] = $this->db->query("SELECT * FROM {$t} ORDER BY 1, 2")->fetch_all();
        }

        return $isi;
    }

    public function testAlamatLamaGetMengalihkanSuperadminKeHalamanYangSamaDiAkunLokandita(): void
    {
        $d     = $this->dunia();
        $admin = $this->sebagai($d['superadmin']);
        $lama  = [
            '/tahun-ajaran'                           => 'tahun-ajaran',
            '/tahun-ajaran/baru'                      => 'tahun-ajaran/baru',
            '/tahun-ajaran/' . $d['tahun']            => 'tahun-ajaran/' . $d['tahun'],
            '/mata-kuliah/baru'                       => 'mata-kuliah/baru',
            '/mata-kuliah/' . $d['matkulX'] . '/ubah' => 'mata-kuliah/' . $d['matkulX'] . '/ubah',
            '/kelas/' . $d['kelasXA'] . '/ubah'       => 'kelas/' . $d['kelasXA'] . '/ubah',
        ];
        foreach ($lama as $jalur => $baru) {
            $r = $admin->get($jalur);
            $this->assertSame(303, $r->status, $jalur);
            $this->assertSame($this->akun($baru), (string) $r->header('Location'), $jalur);
        }
        // Halaman baca tetap di LokaKode.
        foreach (['/mata-kuliah', '/mata-kuliah/' . $d['matkulX'], '/kelas/' . $d['kelasXA']] as $jalur) {
            $this->assertSame(200, $admin->get($jalur)->status, $jalur);
        }
    }

    public function testAlamatLamaPostTidakMengubahApaPunDanMengalihkanKeHalamanInduk(): void
    {
        $d       = $this->dunia();
        $admin   = $this->sebagai($d['superadmin']);
        $csrf    = $this->csrf($admin);
        $sebelum = $this->isiRoster();
        $xa      = $d['kelasXA'];
        $baru    = $this->user('mahasiswa', 'Mahasiswa Baru');

        // Isian yang dulu SAH dan mengubah roster; kini dibuang.
        $lama = [
            ['/tahun-ajaran', ['label' => '2027/2028', 'starts_on' => '2027-08-01', 'ends_on' => '2028-01-31'], 'tahun-ajaran'],
            ['/tahun-ajaran/baru', ['label' => '2027/2028', 'starts_on' => '2027-08-01', 'ends_on' => '2028-01-31'], 'tahun-ajaran'],
            ['/tahun-ajaran/baru/jalankan', ['label' => '2027/2028', 'starts_on' => '2027-08-01', 'ends_on' => '2028-01-31'], 'tahun-ajaran'],
            ['/tahun-ajaran/' . $d['tahun'], ['label' => 'Diganti', 'starts_on' => '2026-08-01', 'ends_on' => '2027-01-31'], 'tahun-ajaran/' . $d['tahun']],
            ['/tahun-ajaran/' . $d['tahun'] . '/aktifkan', [], 'tahun-ajaran/' . $d['tahun']],
            ['/mata-kuliah', ['code' => 'BARU101', 'name' => 'Mata Kuliah Baru', 'pengampu' => $d['pengampuY']['nomor_induk']], 'mata-kuliah'],
            ['/mata-kuliah/' . $d['matkulX'], ['code' => 'UJX101', 'name' => 'Diganti', 'pengampu' => $d['pengampuY']['nomor_induk']], 'mata-kuliah/' . $d['matkulX']],
            ['/mata-kuliah/' . $d['matkulX'] . '/kelas', ['name' => 'XC'], 'mata-kuliah/' . $d['matkulX']],
            ["/kelas/{$xa}", ['name' => 'Diganti', 'capacity' => '5'], "kelas/{$xa}"],
            ["/kelas/{$xa}/arsip", ['arsip' => '1'], "kelas/{$xa}"],
            ["/kelas/{$xa}/hapus", ['yakin' => '1'], "kelas/{$xa}"],
            ["/kelas/{$xa}/pengajar", ['nomor_induk' => $d['pengajarB']['nomor_induk']], "kelas/{$xa}"],
            ["/kelas/{$xa}/pengajar/hapus", ['user_id' => $d['pengajarA']['id']], "kelas/{$xa}"],
            ["/kelas/{$xa}/daftar", ['nomor' => $baru['nomor_induk']], "kelas/{$xa}"],
            ["/kelas/{$xa}/daftar/jalankan", ['nomor' => [$baru['nomor_induk']]], "kelas/{$xa}"],
            ["/kelas/{$xa}/mundur", ['ids' => [$d['daftarA']]], "kelas/{$xa}"],
            ["/kelas/{$xa}/pindah", ['ids' => [$d['daftarA']], 'tujuan' => (string) $d['kelasXB']], "kelas/{$xa}"],
            ["/kelas/{$xa}/lulus", ['ids' => [$d['daftarA']], 'tanggal' => '2027-01-31'], "kelas/{$xa}"],
            ["/kelas/{$xa}/batal-lulus", ['ids' => [$d['daftarA']]], "kelas/{$xa}"],
        ];
        foreach ($lama as [$jalur, $isian, $induk]) {
            $r = $admin->post($jalur, $isian + ['csrf' => $csrf]);
            $this->assertSame(303, $r->status, $jalur);
            $this->assertSame($this->akun($induk), (string) $r->header('Location'), $jalur);
        }
        $this->assertSame($sebelum, $this->isiRoster(), 'tidak ada baris roster yang berubah');
        $this->assertSame(0, $this->hitung('SELECT COUNT(*) FROM audit_log'), 'tidak ada yang diaudit karena tidak ada yang terjadi');

        // Pengampu mata kuliah itu sendiri: 403 di setiap alamat lama, dan tetap tidak ada yang berubah.
        $x     = $this->sebagai($d['pengampuX']);
        $csrfX = $this->csrf($x);
        foreach ($lama as [$jalur, $isian]) {
            $this->assertSame(403, $x->post($jalur, $isian + ['csrf' => $csrfX])->status, "pengampu {$jalur}");
        }
        $this->assertSame($sebelum, $this->isiRoster());
    }

    public function testHalamanKelasDanMataKuliahHanyaMembacaDanMenunjukKeAkunLokandita(): void
    {
        $d = $this->dunia();

        // Superadmin: tautan ke halaman yang sama di Akun Lokandita.
        $admin = $this->sebagai($d['superadmin']);
        $kelas = $admin->get('/kelas/' . $d['kelasXA'])->body;
        $this->assertStringContainsString('href="' . $this->akun('kelas/' . $d['kelasXA']) . '"', $kelas);
        $this->assertStringContainsString('Mahasiswa Ani', $kelas);
        $matkul = $admin->get('/mata-kuliah/' . $d['matkulX'])->body;
        $this->assertStringContainsString('href="' . $this->akun('mata-kuliah/' . $d['matkulX']) . '"', $matkul);
        $this->assertStringContainsString('href="' . $this->akun('mata-kuliah') . '"', $admin->get('/mata-kuliah')->body);
        $this->assertStringContainsString('href="' . $this->akun('roster') . '"', $admin->get('/beranda')->body);

        // Dosen: tidak ada tautan ke Akun (di sana ia ditolak); diberi tahu siapa yang mengelola.
        foreach (['pengampuX', 'pengajarA'] as $siapa) {
            $http = $this->sebagai($d[$siapa]);
            foreach (['/kelas/' . $d['kelasXA'], '/mata-kuliah/' . $d['matkulX'], '/mata-kuliah', '/beranda'] as $jalur) {
                $hal = $http->get($jalur);
                $this->assertSame(200, $hal->status, "{$siapa} {$jalur}");
                $this->assertStringNotContainsString('/admin/', $hal->body, "{$siapa} {$jalur}");
            }
            $this->assertStringContainsString('dikelola superadmin di Akun Lokandita', $http->get('/kelas/' . $d['kelasXA'])->body, $siapa);
        }

        // Tidak ada form yang menuju alamat pengelolaan lama, untuk peran mana pun.
        foreach (['superadmin', 'pengampuX', 'pengajarA'] as $siapa) {
            $http = $this->sebagai($d[$siapa]);
            $hal  = $http->get('/kelas/' . $d['kelasXA'])->body . $http->get('/mata-kuliah/' . $d['matkulX'])->body . $http->get('/mata-kuliah')->body;
            $this->assertSame(0, preg_match('#method="post" action="/(tahun-ajaran|mata-kuliah(/\d+)?(/kelas)?|kelas/\d+(/(arsip|hapus|pengajar|pengajar/hapus|daftar|mundur|pindah|lulus|batal-lulus))?)"#', $hal), $siapa);
        }
    }
}
