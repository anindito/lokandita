<?php

declare(strict_types=1);

namespace Tests\Latihan;

use Tests\Support\KodeCase;

/**
 * Pengaturan latihan dan penerbitan pertemuan (SPEC.md §6) lewat HTTP sungguhan.
 */
final class PenerbitanTest extends KodeCase
{
    private const NOL = ['min_mudah_sekali' => '0', 'min_mudah' => '0', 'min_mudah_sedang' => '0', 'min_sedang' => '0', 'min_sulit' => '0'];

    public function testTerbitDitolakDenganMenyebutPersisApaYangKurang(): void
    {
        $d = $this->dunia();
        $this->soalSiap($d['pertemuanX1'], 'satu-mudah', 'MUDAH');          // 5 poin
        $this->soalSiap($d['pertemuanX1'], 'satu-sulit', 'SULIT');          // 15 poin
        $http = $this->sebagai($d['pengampuX']);

        $r = $http->post('/pertemuan/' . $d['pertemuanX1'] . '/terbitkan', ['csrf' => $this->csrf($http)]);
        $this->assertSame(422, $r->status);
        $this->assertStringContainsString('Pertemuan belum bisa diterbitkan.', $r->body);
        // Bawaan 8 / 8 / 6 / 6 / 4: yang ada 0 / 1 / 0 / 0 / 1.
        $this->assertSame(1, substr_count($r->body, 'kurang 8 soal'));
        $this->assertSame(1, substr_count($r->body, 'kurang 7 soal'));
        $this->assertSame(2, substr_count($r->body, 'kurang 6 soal'));
        $this->assertSame(1, substr_count($r->body, 'kurang 3 soal'));
        // 20 poin tersedia, target 60: butuh 120, kurang 100.
        $this->assertStringContainsString('Poin yang tersedia: 20. Target: 60. Untuk terbit dibutuhkan paling sedikit 120 poin', $r->body);
        $this->assertStringContainsString('Kurang 100 poin.', $r->body);
        $this->assertNull($this->row("SELECT published_at FROM meetings WHERE id = {$d['pertemuanX1']}")['published_at']);
        $this->assertSame(0, $this->hitung("SELECT COUNT(*) FROM audit_log WHERE action = 'pertemuan.diterbitkan'"));
    }

    public function testKomposisiBolehDiturunkanSelamaDuaKaliTargetLaluTerbitDanTarik(): void
    {
        $d = $this->dunia();
        $this->soalSiap($d['pertemuanX1'], 'satu-mudah', 'MUDAH');
        $this->soalSiap($d['pertemuanX1'], 'satu-sulit', 'SULIT');
        $http = $this->sebagai($d['pengampuX']);
        $csrf = $this->csrf($http);
        $p    = '/pertemuan/' . $d['pertemuanX1'];
        $isi  = fn (string $target, string $mudah = '1') => ['csrf' => $csrf, 'target_points' => $target, 'min_mudah' => $mudah, 'min_sulit' => '1', 'autocomplete' => '1'] + self::NOL;

        // Komposisi diturunkan, tetapi target 11 butuh 22 poin dan hanya ada 20: tetap ditolak.
        $this->assertSame(303, $http->post($p . '/latihan', $isi('11'))->status);
        $r = $http->post($p . '/terbitkan', ['csrf' => $csrf]);
        $this->assertSame(422, $r->status);
        $this->assertStringContainsString('Kurang 2 poin.', $r->body);
        $this->assertStringNotContainsString('kurang 1 soal', $r->body);

        // Target 10: 20 poin = 2 × target. Boleh terbit, dengan peringatan di bawah 3 × target.
        $this->assertSame(303, $http->post($p . '/latihan', $isi('10'))->status);
        $this->assertStringContainsString('sebaiknya tersedia 30 poin', $http->get($p)->body);
        $r = $http->post($p . '/terbitkan', ['csrf' => $csrf]);
        $this->assertSame(303, $r->status);
        $baris = $this->row("SELECT published_at, published_by FROM meetings WHERE id = {$d['pertemuanX1']}");
        $this->assertNotNull($baris['published_at']);
        $this->assertSame($d['pengampuX']['id'], $baris['published_by']);
        $this->assertSame(1, $this->hitung("SELECT COUNT(*) FROM audit_log WHERE action = 'pertemuan.diterbitkan' AND actor_id = '{$d['pengampuX']['id']}'"));
        $this->assertStringContainsString('Sudah terbit', $http->get($p)->body);

        // Sesudah terbit, pengaturan tidak boleh diubah menjadi melanggar syarat terbit.
        $r = $http->post($p . '/latihan', $isi('11'));
        $this->assertSame(422, $r->status);
        $this->assertStringContainsString('tidak lagi memenuhi syarat terbit', $r->body);
        $this->assertSame(422, $http->post($p . '/latihan', $isi('10', '2'))->status);
        $m = $this->row("SELECT target_points, min_mudah FROM meetings WHERE id = {$d['pertemuanX1']}");
        $this->assertSame(['10', '1'], [$m['target_points'], $m['min_mudah']]);

        $this->assertSame(303, $http->post($p . '/tarik', ['csrf' => $csrf])->status);
        $this->assertNull($this->row("SELECT published_at FROM meetings WHERE id = {$d['pertemuanX1']}")['published_at']);
        $this->assertSame(1, $this->hitung("SELECT COUNT(*) FROM audit_log WHERE action = 'pertemuan.ditarik'"));
    }

    public function testPertemuanYangTidakDipakaiLatihanTidakBisaTerbit(): void
    {
        $d = $this->dunia();
        $this->soalSiap($d['pertemuanX1'], 'satu-sulit', 'SULIT');
        $this->db->query("UPDATE meetings SET enabled = 0, target_points = 5, min_mudah_sekali = 0, min_mudah = 0, min_mudah_sedang = 0, min_sedang = 0, min_sulit = 0 WHERE id = {$d['pertemuanX1']}");
        $http = $this->sebagai($d['superadmin']);
        $r    = $http->post('/pertemuan/' . $d['pertemuanX1'] . '/terbitkan', ['csrf' => $this->csrf($http)]);
        $this->assertSame(422, $r->status);
        $this->assertStringContainsString('tidak dipakai untuk latihan, jadi tidak bisa diterbitkan', $r->body);
        $this->assertNull($this->row("SELECT published_at FROM meetings WHERE id = {$d['pertemuanX1']}")['published_at']);
    }

    public function testPengaturanLatihanDisimpanDanIsianYangSalahDitolak(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengampuX']);
        $csrf = $this->csrf($http);
        $p    = '/pertemuan/' . $d['pertemuanX1'] . '/latihan';
        $sah  = ['csrf' => $csrf, 'target_points' => '40', 'autocomplete' => '0', 'compiler_flags' => '  -Wall   -std=c++20 ',
            'starter_template' => "#include <iostream>\r\nint main() {\r\n}\r\n",
            'min_mudah_sekali' => '4', 'min_mudah' => '4', 'min_mudah_sedang' => '3', 'min_sedang' => '3', 'min_sulit' => '2'];

        $this->assertSame(303, $http->post($p, $sah)->status);
        $m = $this->row("SELECT * FROM meetings WHERE id = {$d['pertemuanX1']}");
        $this->assertSame(['40', '4', '4', '3', '3', '2', '0'], [$m['target_points'], $m['min_mudah_sekali'], $m['min_mudah'], $m['min_mudah_sedang'], $m['min_sedang'], $m['min_sulit'], $m['autocomplete']]);
        $this->assertSame('-Wall -std=c++20', $m['compiler_flags']);
        $this->assertSame("#include <iostream>\nint main() {\n}\n", $m['starter_template'], 'akhir baris disimpan sebagai LF');
        $audit = $this->row("SELECT detail FROM audit_log WHERE action = 'pertemuan.latihanDiubah'");
        $this->assertStringNotContainsString('iostream', $audit['detail'], 'isi templat tidak masuk log audit');

        foreach ([
            [['target_points' => '0'], 'Target poin harus angka 1 sampai 1000.'],
            [['target_points' => '1001'], 'Target poin harus angka 1 sampai 1000.'],
            [['min_sulit' => '51'], 'Isi dengan angka 0 sampai 50.'],
            [['min_mudah' => '-1'], 'Isi dengan angka 0 sampai 50.'],
            [['compiler_flags' => '-Wall -o /tmp/x'], 'Ada bendera yang tidak diizinkan.'],
            [['compiler_flags' => '-include /etc/passwd'], 'Ada bendera yang tidak diizinkan.'],
            [['compiler_flags' => '-std=c17'], 'Ada bendera yang tidak diizinkan.'], // bendera C di pertemuan C++
            [['starter_template' => str_repeat('x', 20001)], 'Templat paling panjang 20.000 karakter.'],
        ] as [$ganti, $pesan]) {
            $r = $http->post($p, $ganti + $sah);
            $this->assertSame(422, $r->status, $pesan);
            $this->assertStringContainsString($pesan, $r->body);
        }
        $this->assertSame($m, $this->row("SELECT * FROM meetings WHERE id = {$d['pertemuanX1']}"), 'isian yang ditolak tidak mengubah apa pun');

        // Templat dikosongkan = kembali ke templat bawaan bahasa (NULL).
        $this->assertSame(303, $http->post($p, ['starter_template' => "  \r\n"] + $sah)->status);
        $this->assertNull($this->row("SELECT starter_template FROM meetings WHERE id = {$d['pertemuanX1']}")['starter_template']);
    }

    public function testPertemuanPythonTidakMenerimaBenderaKompiler(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengampuY']);
        $csrf = $this->csrf($http);
        $isi  = ['csrf' => $csrf, 'target_points' => '60', 'autocomplete' => '1',
            'min_mudah_sekali' => '8', 'min_mudah' => '8', 'min_mudah_sedang' => '6', 'min_sedang' => '6', 'min_sulit' => '4'];

        $r = $http->post('/pertemuan/' . $d['pertemuanY1'] . '/latihan', $isi + ['compiler_flags' => '-Wall']);
        $this->assertSame(422, $r->status);
        $this->assertStringContainsString('Python tidak memakai bendera kompiler.', $r->body);
        $this->assertSame(303, $http->post('/pertemuan/' . $d['pertemuanY1'] . '/latihan', $isi + ['compiler_flags' => ''])->status);
        $this->assertStringNotContainsString('name="compiler_flags"', $http->get('/pertemuan/' . $d['pertemuanY1'])->body);
    }
}
