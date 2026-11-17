<?php

declare(strict_types=1);

namespace Tests\Latihan;

use Tests\Support\KodeCase;

/**
 * Jendela pertemuan per kelas dan pra-pembangkitan (SPEC.md §6, §8.2) lewat HTTP
 * sungguhan; instans dibuat oleh judge worker sungguhan di kontainer sungguhan.
 */
final class JendelaTest extends KodeCase
{
    public function testPengajarMenjadwalkanKelasnyaDalamWaktuJakarta(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengajarA']);
        $csrf = $this->csrf($http);
        $url  = '/kelas/' . $d['kelasXA'] . '/pertemuan/' . $d['pertemuanX1'];

        $r = $http->post($url, ['csrf' => $csrf, 'open_at' => '2030-11-20T08:00', 'close_at' => '2030-11-20T10:30', 'post_close_visibility' => 'review_only']);
        $this->assertSame(303, $r->status);
        $j = $this->row("SELECT * FROM meeting_class_windows WHERE meeting_id = {$d['pertemuanX1']} AND class_id = {$d['kelasXA']}");
        // 08:00 WIB = 01:00 UTC.
        $this->assertSame(['2030-11-20 01:00:00', '2030-11-20 03:30:00', 'none', 'review_only', $d['pengajarA']['id']],
            [$j['open_at'], $j['close_at'], $j['override'], $j['post_close_visibility'], $j['updated_by']]);
        $audit = $this->row("SELECT actor_id, class_id, course_id FROM audit_log WHERE action = 'jendela.disimpan'");
        $this->assertSame([$d['pengajarA']['id'], (string) $d['kelasXA'], (string) $d['matkulX']], [$audit['actor_id'], (string) $audit['class_id'], (string) $audit['course_id']]);

        $hal = $http->get($url);
        $this->assertSame(200, $hal->status);
        $this->assertStringContainsString('keadaan-terjadwal', $hal->body);
        $this->assertStringContainsString('value="2030-11-20T08:00"', $hal->body);
        $this->assertStringContainsString('20-11-2030 08:00 WIB', $http->get('/kelas/' . $d['kelasXA'] . '/pertemuan')->body);

        // Mengubah jadwal memakai baris yang sama.
        $http->post($url, ['csrf' => $csrf, 'open_at' => '2030-11-21T08:00', 'close_at' => '2030-11-21T10:30', 'post_close_visibility' => 'hidden']);
        $this->assertSame(1, $this->hitung('SELECT COUNT(*) FROM meeting_class_windows'));
    }

    public function testJadwalYangSalahDitolakDenganPesan(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengampuX']);
        $csrf = $this->csrf($http);
        $url  = '/kelas/' . $d['kelasXA'] . '/pertemuan/' . $d['pertemuanX1'];

        foreach ([
            [['open_at' => '2030-11-20T10:00', 'close_at' => '2030-11-20T08:00'], 'Waktu tutup harus sesudah waktu buka.'],
            [['open_at' => '2030-11-20T10:00', 'close_at' => '2030-11-20T10:00'], 'Waktu tutup harus sesudah waktu buka.'],
            [['open_at' => '2030-11-20T10:00', 'close_at' => ''], 'Isi waktu buka dan waktu tutup dua-duanya'],
            [['open_at' => 'besok pagi', 'close_at' => '2030-11-20T10:00'], 'Waktu tidak dikenali.'],
            [['open_at' => '2030-02-31T10:00', 'close_at' => '2030-03-01T10:00'], 'Waktu tidak dikenali.'],
            [['open_at' => '2030-11-20T08:00', 'close_at' => '2030-11-20T10:00', 'post_close_visibility' => 'semua'], 'Pilih salah satu dari dua pilihan.'],
        ] as [$isi, $pesan]) {
            $r = $http->post($url, ['csrf' => $csrf] + $isi + ['post_close_visibility' => 'hidden']);
            $this->assertSame(422, $r->status, $pesan);
            $this->assertStringContainsString($pesan, $r->body);
        }
        $this->assertSame(0, $this->hitung('SELECT COUNT(*) FROM meeting_class_windows'));
        $this->assertSame(422, $http->post($url . '/status', ['csrf' => $csrf, 'override' => 'mungkin'])->status);
        $this->assertSame(0, $this->hitung('SELECT COUNT(*) FROM meeting_class_windows'));
    }

    public function testPengajarTidakBisaMengaturKelasLainAtauPertemuanMataKuliahLain(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengajarA']);
        $csrf = $this->csrf($http);
        $isi  = ['csrf' => $csrf, 'open_at' => '2030-11-20T08:00', 'close_at' => '2030-11-20T10:00', 'post_close_visibility' => 'hidden'];

        // Kelas XB milik pengajar B: dijawab persis seperti kelas yang tidak ada.
        $xb    = '/kelas/' . $d['kelasXB'] . '/pertemuan/' . $d['pertemuanX1'];
        $tiada = $http->get('/kelas/999999/pertemuan/' . $d['pertemuanX1']);
        $r     = $http->get($xb);
        $this->assertSame(404, $r->status);
        $this->assertSame($tiada->body, $r->body);
        $this->assertSame(404, $http->get('/kelas/' . $d['kelasXB'] . '/pertemuan')->status);
        $this->assertSame(404, $http->post($xb, $isi)->status);
        $this->assertSame(404, $http->post($xb . '/status', ['csrf' => $csrf, 'override' => 'open'])->status);
        $this->assertSame(404, $http->post($xb . '/siapkan', ['csrf' => $csrf])->status);

        // Kelas sendiri, tetapi pertemuan mata kuliah Y: tidak ada.
        $silang = '/kelas/' . $d['kelasXA'] . '/pertemuan/' . $d['pertemuanY1'];
        $this->assertSame(404, $http->get($silang)->status);
        $this->assertSame(404, $http->post($silang, $isi)->status);
        $this->assertSame(404, $http->post($silang . '/status', ['csrf' => $csrf, 'override' => 'open'])->status);

        // Pertemuan yang tidak dipakai untuk latihan tidak punya jadwal.
        $this->db->query("UPDATE meetings SET enabled = 0 WHERE id = {$d['pertemuanX2']}");
        $this->assertSame(404, $http->post('/kelas/' . $d['kelasXA'] . '/pertemuan/' . $d['pertemuanX2'], $isi)->status);

        $this->assertSame(0, $this->hitung('SELECT COUNT(*) FROM meeting_class_windows'));
        $this->assertSame(0, $this->hitung('SELECT COUNT(*) FROM audit_log'));
        $this->assertSame(1, $this->hitung('SELECT COUNT(*) FROM judge_jobs'), 'hanya pekerjaan dunia uji');
    }

    public function testTahunYangDiarsipkanHanyaBisaDibacaDosen(): void
    {
        $d = $this->dunia();
        $this->db->query("UPDATE academic_years SET status = 'archived' WHERE id = {$d['tahun']}");
        $url = '/kelas/' . $d['kelasXA'] . '/pertemuan/' . $d['pertemuanX1'];

        $http = $this->sebagai($d['pengampuX']);
        $hal  = $http->get($url);
        $this->assertSame(200, $hal->status);
        $this->assertStringNotContainsString('name="open_at"', $hal->body);
        $this->assertSame(403, $http->post($url . '/status', ['csrf' => $this->csrf($http), 'override' => 'open'])->status);
        $this->assertSame(0, $this->hitung('SELECT COUNT(*) FROM meeting_class_windows'));

        $admin = $this->sebagai($d['superadmin']);
        $this->assertSame(303, $admin->post($url . '/status', ['csrf' => $this->csrf($admin), 'override' => 'open'])->status);
    }

    public function testBukaTutupManualMengalahkanJadwal(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengajarA']);
        $csrf = $this->csrf($http);
        $url  = '/kelas/' . $d['kelasXA'] . '/pertemuan/' . $d['pertemuanX1'];

        // Tanpa jadwal: buka manual membuat barisnya.
        $this->assertSame(303, $http->post($url . '/status', ['csrf' => $csrf, 'override' => 'open'])->status);
        $this->assertSame('open', $this->row('SELECT override FROM meeting_class_windows')['override']);
        $this->assertStringContainsString('keadaan-buka', $http->get($url)->body);

        // Jadwal yang sudah lewat + buka manual = tetap terbuka; ikuti jadwal = tertutup.
        $http->post($url, ['csrf' => $csrf, 'open_at' => '2020-01-01T08:00', 'close_at' => '2020-01-01T10:00', 'post_close_visibility' => 'hidden']);
        $this->assertStringContainsString('keadaan-buka', $http->get($url)->body);
        $http->post($url . '/status', ['csrf' => $csrf, 'override' => 'none']);
        $this->assertStringContainsString('keadaan-tutup', $http->get($url)->body);

        // Jadwal yang sedang berjalan + tutup manual = tertutup.
        $http->post($url, ['csrf' => $csrf, 'open_at' => '2020-01-01T08:00', 'close_at' => '2099-01-01T10:00', 'post_close_visibility' => 'hidden']);
        $this->assertStringContainsString('keadaan-buka', $http->get($url)->body);
        $http->post($url . '/status', ['csrf' => $csrf, 'override' => 'closed']);
        $this->assertStringContainsString('keadaan-tutup', $http->get($url)->body);

        $aksi = array_column($this->db->query("SELECT action FROM audit_log WHERE action LIKE 'jendela.%' ORDER BY id")->fetch_all(MYSQLI_ASSOC), 'action');
        $this->assertSame(['jendela.dibuka', 'jendela.disimpan', 'jendela.ikutJadwal', 'jendela.disimpan', 'jendela.ditutup'], $aksi);
    }

    public function testMembukaJendelaMenyiapkanInstansSemuaMahasiswaAktifKelasItuSaja(): void
    {
        $d  = $this->dunia();
        $s1 = $this->soalSiap($d['pertemuanX1'], 'jumlah-a');
        $s2 = $this->soalSiap($d['pertemuanX1'], 'jumlah-b', 'SULIT');
        $this->terbit($d['pertemuanX1']);
        $mhsA2 = $this->user('mahasiswa', 'Mahasiswa Andi');
        $this->daftar($mhsA2, $d['kelasXA']);
        $mundur = $this->user('mahasiswa', 'Mahasiswa Mundur');
        $this->daftar($mundur, $d['kelasXA'], 'MENGUNDURKAN_DIRI');

        $http = $this->sebagai($d['pengajarA']);
        $csrf = $this->csrf($http);
        $url  = '/kelas/' . $d['kelasXA'] . '/pertemuan/' . $d['pertemuanX1'];
        $this->assertSame(303, $http->post($url . '/status', ['csrf' => $csrf, 'override' => 'open'])->status);

        // 2 mahasiswa AKTIF × 2 soal, di antrean prioritas rendah, atas nama pengajar.
        $job = $this->db->query("SELECT priority, created_by, idempotency_key FROM judge_jobs WHERE kind = 'GENERATE' ORDER BY idempotency_key")->fetch_all(MYSQLI_ASSOC);
        $this->assertCount(4, $job);
        $this->assertSame(['low'], array_values(array_unique(array_column($job, 'priority'))));
        $this->assertSame([$d['pengajarA']['id']], array_values(array_unique(array_column($job, 'created_by'))));
        $kunci = [];
        foreach ([$d['mhsA']['id'], $mhsA2['id']] as $u) {
            foreach ([$s1['mp'], $s2['mp']] as $mp) {
                $kunci[] = "gen:{$mp}:{$u}";
            }
        }
        sort($kunci);
        $this->assertSame($kunci, array_column($job, 'idempotency_key'));

        $mulai = microtime(true);
        $this->tungguSampai(fn () => $this->hitung('SELECT COUNT(*) FROM problem_instances') === 4, 180, 'empat instans');
        fwrite(STDERR, sprintf("\n[ukur] 4 pekerjaan GENERATE (2 mahasiswa × 2 soal, 12 tes): %.1f detik\n", microtime(true) - $mulai));

        $hal = $http->get($url);
        $this->assertStringContainsString('4 dari 4 soal mahasiswa sudah siap.', $hal->body);
        $this->assertStringContainsString('(2 mahasiswa aktif × 2 soal)', $hal->body);
        $this->assertNull($hal->header('Refresh'));
        $this->assertStringNotContainsString('Siapkan sekarang', $hal->body);

        // Hanya mahasiswa AKTIF kelas XA; tidak ada untuk mahasiswa kelas XB atau yang mengundurkan diri.
        $pemilik = array_column($this->db->query('SELECT DISTINCT user_id FROM problem_instances ORDER BY user_id')->fetch_all(MYSQLI_ASSOC), 'user_id');
        $harap   = [$d['mhsA']['id'], $mhsA2['id']];
        sort($harap);
        $this->assertSame($harap, $pemilik);

        // Tiap mahasiswa mendapat tes yang berbeda untuk soal yang sama (SPEC.md §8.2).
        $tes = array_column($this->db->query("SELECT user_id, tests FROM problem_instances WHERE meeting_problem_id = {$s1['mp']}")->fetch_all(MYSQLI_ASSOC), 'tests', 'user_id');
        $this->assertNotSame($tes[$d['mhsA']['id']], $tes[$mhsA2['id']]);
        $this->assertCount(12, json_decode($tes[$d['mhsA']['id']], true));

        // Tes tidak pernah ada di halaman pengajar.
        $contoh = trim(json_decode($tes[$d['mhsA']['id']], true)[5]['in']);
        $this->assertStringNotContainsString($contoh, $hal->body);

        // Menyiapkan lagi tidak membuat apa-apa: instans tidak pernah dibuat ulang.
        $potret = $this->db->query('SELECT id, tests FROM problem_instances ORDER BY id')->fetch_all(MYSQLI_ASSOC);
        $r      = $http->post($url . '/siapkan', ['csrf' => $csrf]);
        $this->assertStringEndsWith('?pesan=disiapkan&n=0', (string) $r->header('Location'));
        $this->assertSame(4, $this->hitung("SELECT COUNT(*) FROM judge_jobs WHERE kind = 'GENERATE'"));
        $this->assertSame($potret, $this->db->query('SELECT id, tests FROM problem_instances ORDER BY id')->fetch_all(MYSQLI_ASSOC));
    }

    public function testHalamanMemuatUlangDirinyaSelamaMasihAdaYangDibuat(): void
    {
        $d = $this->dunia();
        $s = $this->soalSiap($d['pertemuanX1'], 'jumlah-a');
        $this->terbit($d['pertemuanX1']);
        $this->jendela($d['pertemuanX1'], $d['kelasXA']);
        // Pekerjaan yang "sedang berjalan" (tidak ada di Redis, jadi worker tes tidak menyentuhnya dalam 10 detik pertama).
        $this->db->query("INSERT INTO judge_jobs (id, kind, priority, status, payload, idempotency_key, created_at, started_at)
            VALUES (UUID(), 'GENERATE', 'low', 'RUNNING', '{}', 'gen:{$s['mp']}:{$d['mhsA']['id']}', UTC_TIMESTAMP(), UTC_TIMESTAMP())");

        $hal = $this->sebagai($d['pengajarA'])->get('/kelas/' . $d['kelasXA'] . '/pertemuan/' . $d['pertemuanX1']);
        $this->assertSame(200, $hal->status);
        $this->assertSame('4; url=/kelas/' . $d['kelasXA'] . '/pertemuan/' . $d['pertemuanX1'], $hal->header('Refresh'));
        $this->assertStringContainsString('0 dari 1 soal mahasiswa sudah siap.', $hal->body);
        $this->assertStringContainsString('1 sedang dibuat.', $hal->body);
        $this->assertStringContainsString('Perkiraan selesai: sekitar 1 menit.', $hal->body);
    }

    public function testPertemuanYangBelumTerbitTidakDisiapkan(): void
    {
        $d = $this->dunia();
        $this->soalSiap($d['pertemuanX1'], 'jumlah-a');
        $http = $this->sebagai($d['pengajarA']);
        $csrf = $this->csrf($http);
        $url  = '/kelas/' . $d['kelasXA'] . '/pertemuan/' . $d['pertemuanX1'];

        $this->assertSame(303, $http->post($url . '/status', ['csrf' => $csrf, 'override' => 'open'])->status);
        $this->assertSame(0, $this->hitung("SELECT COUNT(*) FROM judge_jobs WHERE kind = 'GENERATE'"));
        $r = $http->post($url . '/siapkan', ['csrf' => $csrf]);
        $this->assertSame(409, $r->status);
        $this->assertStringContainsString('Pertemuan ini belum diterbitkan.', $r->body);
        $this->assertStringContainsString('Pertemuan ini belum diterbitkan.', $http->get($url)->body);
        $this->assertSame(0, $this->hitung("SELECT COUNT(*) FROM judge_jobs WHERE kind = 'GENERATE'"));

        // Begitu pengampu menerbitkannya, kelas yang jendelanya sudah diatur langsung disiapkan.
        $this->db->query("UPDATE meetings SET target_points = 2, min_mudah_sekali = 0, min_mudah = 1, min_mudah_sedang = 0, min_sedang = 0, min_sulit = 0 WHERE id = {$d['pertemuanX1']}");
        $pengampu = $this->sebagai($d['pengampuX']);
        $this->assertSame(303, $pengampu->post('/pertemuan/' . $d['pertemuanX1'] . '/terbitkan', ['csrf' => $this->csrf($pengampu)])->status);
        $this->assertSame(1, $this->hitung("SELECT COUNT(*) FROM judge_jobs WHERE kind = 'GENERATE' AND created_by = '{$d['pengampuX']['id']}'"));
        $this->tungguSampai(fn () => $this->hitung('SELECT COUNT(*) FROM problem_instances') === 1, 120, 'satu instans');
    }

    public function testSoalYangGagalDibuatDilaporkanDanBisaDicobaLagi(): void
    {
        $d = $this->dunia();
        $s = $this->soalSiap($d['pertemuanX1'], 'rusak', 'MUDAH', ['generator' => "import sys\nsys.exit('RAHASIA-GENERATOR')\n"]);
        $this->terbit($d['pertemuanX1']);
        $http = $this->sebagai($d['pengajarA']);
        $csrf = $this->csrf($http);
        $url  = '/kelas/' . $d['kelasXA'] . '/pertemuan/' . $d['pertemuanX1'];

        $http->post($url . '/status', ['csrf' => $csrf, 'override' => 'open']);
        $this->tungguSampai(fn () => $this->hitung("SELECT COUNT(*) FROM judge_jobs WHERE kind = 'GENERATE' AND status = 'FAILED'") === 1, 120, 'pekerjaan gagal');
        $hal = $http->get($url);
        $this->assertStringContainsString('1 soal gagal dibuat.', $hal->body);
        $this->assertStringContainsString('0 dari 1 soal mahasiswa sudah siap.', $hal->body);
        $this->assertStringNotContainsString('RAHASIA-GENERATOR', $hal->body);

        // Generatornya diperbaiki; "Siapkan sekarang" mengantrekan lagi pekerjaan yang sama.
        $gen = $this->db->real_escape_string(self::GEN_ACAK);
        $this->db->query("UPDATE problem_versions SET generator = '{$gen}' WHERE id = {$s['versi']}");
        $r = $http->post($url . '/siapkan', ['csrf' => $csrf]);
        $this->assertStringEndsWith('?pesan=disiapkan&n=1', (string) $r->header('Location'));
        $this->assertSame(1, $this->hitung("SELECT COUNT(*) FROM judge_jobs WHERE kind = 'GENERATE'"), 'tetap satu pekerjaan');
        $this->tungguSampai(fn () => $this->hitung('SELECT COUNT(*) FROM problem_instances') === 1, 120, 'instans sesudah diperbaiki');
        $this->assertStringContainsString('1 dari 1 soal mahasiswa sudah siap.', $http->get($url)->body);
        $this->assertSame(1, $this->hitung("SELECT COUNT(*) FROM audit_log WHERE action = 'instans.disiapkan'"));
    }
}
