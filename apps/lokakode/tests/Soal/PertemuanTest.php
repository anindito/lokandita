<?php

declare(strict_types=1);

namespace Tests\Soal;

use Tests\Support\KodeCase;

/**
 * Pertemuan dan konsep (SPEC.md §6 bentuk minimal, §9) lewat HTTP sungguhan.
 */
final class PertemuanTest extends KodeCase
{
    public function testPengampuMembuatPertemuanDanTercatatDiAudit(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengampuX']);
        $csrf = $this->csrf($http);

        $r = $http->post('/mata-kuliah/' . $d['matkulX'] . '/pertemuan', ['csrf' => $csrf, 'number' => '4', 'title' => 'Struktur Kontrol — Percabangan', 'language' => 'cpp']);
        $this->assertSame(303, $r->status);
        $baris = $this->row("SELECT * FROM meetings WHERE course_id = {$d['matkulX']} AND number = 4");
        $this->assertSame(['Struktur Kontrol — Percabangan', 'cpp', '1'], [$baris['title'], $baris['language'], (string) $baris['enabled']]);
        $this->assertStringEndsWith('/pertemuan/' . $baris['id'] . '?pesan=disimpan', (string) $r->header('Location'));
        $audit = $this->row("SELECT actor_id, course_id FROM audit_log WHERE action = 'pertemuan.dibuat'");
        $this->assertSame([$d['pengampuX']['id'], (string) $d['matkulX']], [$audit['actor_id'], (string) $audit['course_id']]);

        $daftar = $http->get('/mata-kuliah/' . $d['matkulX'] . '/pertemuan');
        $this->assertSame(200, $daftar->status);
        $this->assertStringContainsString('Struktur Kontrol — Percabangan', $daftar->body);
    }

    public function testIsianPertemuanYangSalahDitolakDenganPesan(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['superadmin']);
        $csrf = $this->csrf($http);
        $awal = $this->hitung('SELECT COUNT(*) FROM meetings');

        foreach ([
            [['number' => '17', 'title' => 'Judul sah', 'language' => 'cpp'], 'Nomor pertemuan harus angka 1 sampai 16.'],
            [['number' => '1', 'title' => 'Judul sah', 'language' => 'cpp'], 'sudah punya pertemuan dengan nomor itu'],
            [['number' => '5', 'title' => 'x', 'language' => 'cpp'], 'Judul harus 3 sampai 190 karakter.'],
            [['number' => '5', 'title' => 'Judul sah', 'language' => 'java'], 'Pilih C, C++, atau Python.'],
        ] as [$isi, $pesan]) {
            $r = $http->post('/mata-kuliah/' . $d['matkulX'] . '/pertemuan', ['csrf' => $csrf] + $isi);
            $this->assertSame(422, $r->status, $pesan);
            $this->assertStringContainsString($pesan, $r->body);
        }
        $this->assertSame($awal, $this->hitung('SELECT COUNT(*) FROM meetings'));
    }

    public function testKonsepKumulatifDitampilkanTercentangDanTerkunci(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengampuX']);

        // Pertemuan 2: IO_DASAR diwarisi dari pertemuan 1; KONVERSI_TIPE miliknya sendiri.
        $r = $http->get('/pertemuan/' . $d['pertemuanX2']);
        $this->assertSame(200, $r->status);
        $this->assertMatchesRegularExpression('/value="IO_DASAR"\s+checked disabled/', $r->body);
        $this->assertMatchesRegularExpression('/value="KONVERSI_TIPE"\s+checked >/', $r->body);
        $this->assertMatchesRegularExpression('/value="STRING"\s+>/', $r->body);
        // Katalog disaring menurut bahasa pertemuan: konsep khusus Python tidak ditawarkan untuk C++.
        $this->assertStringNotContainsString('value="PY_LIST"', $r->body);
        $this->assertStringContainsString('value="PY_LIST"', $this->sebagai($d['pengampuY'])->get('/pertemuan/' . $d['pertemuanY1'])->body);
    }

    public function testMenyimpanKonsepMengabaikanYangDiwarisiDanYangBukanBahasanya(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengampuX']);
        $r    = $http->post('/pertemuan/' . $d['pertemuanX2'], [
            'csrf' => $this->csrf($http), 'number' => '2', 'title' => 'Tipe Data', 'language' => 'cpp', 'enabled' => '1',
            // IO_DASAR diwarisi, PY_LIST bukan konsep C++, TIDAK_ADA bukan konsep: ketiganya diabaikan.
            'konsep' => ['KONVERSI_TIPE', 'FORMAT_OUTPUT', 'IO_DASAR', 'PY_LIST', 'TIDAK_ADA'],
        ]);
        $this->assertSame(303, $r->status);
        $kode = array_column($this->db->query("SELECT concept_code FROM meeting_concepts WHERE meeting_id = {$d['pertemuanX2']} ORDER BY concept_code")->fetch_all(), 0);
        $this->assertSame(['FORMAT_OUTPUT', 'KONVERSI_TIPE'], $kode);
        $this->assertSame('Tipe Data', $this->row("SELECT title FROM meetings WHERE id = {$d['pertemuanX2']}")['title']);
        $this->assertSame(1, $this->hitung("SELECT COUNT(*) FROM audit_log WHERE action = 'pertemuan.diubah'"));
    }

    public function testKonsepDasarMataKuliahDiwarisiSemuaPertemuan(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['superadmin']);
        $r    = $http->post('/mata-kuliah/' . $d['matkulX'] . '/konsep-dasar', ['csrf' => $this->csrf($http), 'konsep' => ['PERCABANGAN', 'PERULANGAN', 'TIDAK_ADA']]);
        $this->assertSame(303, $r->status);
        $this->assertSame(2, $this->hitung("SELECT COUNT(*) FROM course_base_concepts WHERE course_id = {$d['matkulX']}"));

        $hal = $http->get('/pertemuan/' . $d['pertemuanX1'])->body;
        $this->assertMatchesRegularExpression('/value="PERCABANGAN"\s+checked disabled/', $hal);
        // Mata kuliah lain tidak terpengaruh.
        $this->assertSame(0, $this->hitung("SELECT COUNT(*) FROM course_base_concepts WHERE course_id = {$d['matkulY']}"));
    }

    public function testPertemuanBersoalTidakBisaGantiNomorAtauBahasa(): void
    {
        $d = $this->dunia();
        $this->db->query("INSERT INTO problems (meeting_id, slug, created_at) VALUES ({$d['pertemuanX1']}, 'soal-uji', UTC_TIMESTAMP())");
        $http = $this->sebagai($d['pengampuX']);
        $r    = $http->post('/pertemuan/' . $d['pertemuanX1'], ['csrf' => $this->csrf($http), 'number' => '3', 'title' => 'Judul baru', 'language' => 'python', 'enabled' => '1']);
        $this->assertSame(422, $r->status);
        $this->assertStringContainsString('Nomor tidak bisa diubah', $r->body);
        $this->assertStringContainsString('Bahasa tidak bisa diubah', $r->body);
        $baris = $this->row("SELECT number, language, title FROM meetings WHERE id = {$d['pertemuanX1']}");
        $this->assertSame(['1', 'cpp', 'Pertemuan uji'], [(string) $baris['number'], $baris['language'], $baris['title']]);
    }

    public function testPengajarDanPengampuLainTidakMelihatPertemuan(): void
    {
        $d = $this->dunia();
        // Pengajar A mengajar kelas di mata kuliah X: melihat mata kuliahnya, tetapi bukan bank soalnya.
        $r = $this->sebagai($d['pengajarA'])->get('/pertemuan/' . $d['pertemuanX1']);
        $this->assertSame(403, $r->status);
        $this->assertStringNotContainsString('Pertemuan uji', $r->body);
        $this->assertStringNotContainsString('pertemuan', $this->sebagai($d['pengajarA'])->get('/mata-kuliah/' . $d['matkulX'])->body);
        // Pengampu Y: pertemuan mata kuliah X sama seperti tidak ada.
        $luar   = $this->sebagai($d['pengampuY'])->get('/pertemuan/' . $d['pertemuanX1']);
        $kosong = $this->sebagai($d['pengampuY'])->get('/pertemuan/999999');
        $this->assertSame(404, $luar->status);
        // Sama persis, kecuali token CSRF, sisa waktu sesi dan jalur yang diulang menu akun.
        $baku = static fn (string $b): string => preg_replace(['/csrf(" value)?="[0-9a-f]+"/', '/data-sesi-sisa="\d+"/', '#/pertemuan/\d+#'], ['CSRF', 'SISA', '/pertemuan/N'], $b);
        $this->assertSame($baku($kosong->body), $baku($luar->body));
        $this->assertStringNotContainsString('Pertemuan uji', $luar->body);
    }
}
