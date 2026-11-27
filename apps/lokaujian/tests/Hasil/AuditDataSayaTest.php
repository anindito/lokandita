<?php

declare(strict_types=1);

namespace Tests\Hasil;

use Tests\Support\UjianCase;

/**
 * Halaman log audit dan ekspor data sendiri (SPEC-LOKAUJIAN.md §13; KONTRAK.md §14, §24).
 */
final class AuditDataSayaTest extends UjianCase
{
    public function testHalamanAuditHanyaSuperadminDisaringDanTidakBisaDiubah(): void
    {
        $d     = $this->duniaSesi();
        $admin = $this->sebagai($d['superadmin']);
        $this->kirim($this->sebagai($d['pengajarA']), "/sesi/{$d['sesi']}/tambah-waktu", ['mahasiswa' => [(string) $d['mhsA']['id']], 'menit' => '7', 'yakin' => '1']);

        $r = $admin->get('/audit');
        $this->assertSame(200, $r->status);
        foreach (['data-aksi="ujian.diterbitkan"', 'data-aksi="sesi.dimulai"', 'data-aksi="waktu.ditambah"', 'Pengampu Xaverius', 'UJX101'] as $teks) {
            $this->assertStringContainsString($teks, $r->body, $teks);
        }
        // Saring menurut tindakan, pelaku (nomor induk), mata kuliah dan tanggal.
        $r = $admin->get('/audit?aksi=waktu.ditambah');
        $this->assertSame(1, substr_count($r->body, '<tr data-aksi='));
        $this->assertStringContainsString('Pengajar Aminah', $r->body);
        $r = $admin->get('/audit?pelaku=' . rawurlencode((string) $d['pengajarA']['nomor_induk']));
        $this->assertSame(1, substr_count($r->body, '<tr data-aksi='));
        $this->assertSame(0, substr_count($admin->get('/audit?pelaku=tidak-ada')->body, '<tr data-aksi='));
        $this->assertSame(0, substr_count($admin->get('/audit?matkul=' . $d['matkulY'])->body, '<tr data-aksi='));
        $this->assertSame(0, substr_count($admin->get('/audit?sampai=2020-01-01')->body, '<tr data-aksi='));
        $hariIni = (new \DateTimeImmutable('now', new \DateTimeZone('Asia/Jakarta')))->format('Y-m-d');
        $this->assertGreaterThan(2, substr_count($admin->get("/audit?dari={$hariIni}&sampai={$hariIni}")->body, '<tr data-aksi='));
        // Saringan yang rusak diabaikan, bukan dijalankan.
        $this->assertSame(200, $admin->get("/audit?aksi=" . rawurlencode("x' OR 1=1 --") . '&matkul=abc&dari=kemarin&hal=-3')->status);

        // Selain superadmin: 403, tanpa isi log.
        foreach (['pengampuX', 'pengajarA', 'mhsA'] as $p) {
            $r = $this->sebagai($d[$p])->get('/audit');
            $this->assertSame(403, $r->status, $p);
            $this->assertStringNotContainsString('waktu.ditambah', $r->body, $p);
        }
        // Tidak ada rute yang mengubah atau menghapus baris audit: satu-satunya rute `audit` adalah GET.
        $rute = service('routes')->loadRoutes();
        foreach (['POST', 'PUT', 'PATCH', 'DELETE'] as $metode) {
            foreach (array_keys($rute->getRoutes($metode, false)) as $pola) {
                $this->assertStringNotContainsString('audit', (string) $pola, "{$metode} {$pola}");
            }
        }
        $sebelum = $this->sidikBank();
        $this->assertContains($this->kirim($admin, '/audit', ['hapus' => '1'])->status, [404, 405]);
        $this->assertSame($sebelum, $this->sidikBank());
    }

    public function testDataSayaHanyaMilikSendiriTanpaSoalDanKunci(): void
    {
        $d   = $this->duniaSesi();
        $s   = $d['sesi'];
        $ani = $this->sebagai($d['mhsA']);
        $cit = $this->sebagai($d['mhsC']);
        $this->mulaiKerja($ani, $s, (string) $d['mhsA']['id']);
        $this->mulaiKerja($cit, $s, (string) $d['mhsC']['id']);
        $t = $this->bukaHalamanUjian($ani, $s);
        $this->simpan($ani, $s, $t, $d['q']['BS'], 'benar', 1);
        $this->simpan($ani, $s, $t, $d['q']['PG'], (int) $this->db->query("SELECT id FROM exam_options WHERE exam_question_id = {$d['q']['PG']} AND correct = 1")->fetch_assoc()['id'], 1);
        $this->simpan($ani, $s, $t, $d['q']['URAIAN'], 'uraian milik Ani', 1);
        $this->simpan($cit, $s, $this->bukaHalamanUjian($cit, $s), $d['q']['URAIAN'], 'RAHASIA-JAWABAN-CITRA', 1);
        $ani->postJson("/api/sesi/{$s}/peristiwa", ['jenis' => 'focus_lost', 'durasi' => 4], ['X-CSRF-TOKEN' => $this->csrf($ani)]);
        $this->kirim($ani, "/ujian-saya/{$s}/kumpulkan");
        $this->kirim($cit, "/ujian-saya/{$s}/kumpulkan");

        // Halamannya di luar SEB dan mengatakan apa isi berkasnya.
        $r = $ani->get('/data-saya');
        $this->assertSame(200, $r->status);
        $this->assertStringContainsString('Jawaban Anda sendiri', $r->body);

        $r = $this->kirim($ani, '/data-saya/unduh');
        $this->assertSame(200, $r->status);
        $this->assertSame('attachment; filename="lokandita-lokaujian-data-saya.json"', $r->header('Content-Disposition'));
        $data = json_decode($r->body, true, 32, JSON_THROW_ON_ERROR);
        $this->assertSame(['lokaujian', (string) $d['mhsA']['id'], []], [$data['aplikasi'], $data['pengguna'], $data['terpotong']]);
        $this->assertCount(1, $data['pengerjaan']);
        $p = $data['pengerjaan'][0];
        $this->assertSame(['Ujian kendali', 'UJX101', 'XA', 'mahasiswa', 0], [$p['ujian'], $p['mata_kuliah'], $p['kelas'], $p['cara_kumpul'], $p['tambahan_menit']]);
        $this->assertArrayNotHasKey('nilai_objektif', $p, 'skor tidak ada selagi hasil belum dirilis');
        // Jawabannya sendiri, menurut nomor yang ia lihat; pilihan ganda sebagai huruf.
        $jawab = array_column($data['jawaban'], null, 'tipe');
        $this->assertCount(3, $data['jawaban']);
        $this->assertSame(['benar', 'B', 'uraian milik Ani'], [$jawab['benar_salah']['jawaban'], $jawab['pilihan_ganda']['jawaban'], $jawab['uraian']['jawaban']]);
        $this->assertSame([1, 2, 4], [$jawab['benar_salah']['nomor'], $jawab['pilihan_ganda']['nomor'], $jawab['uraian']['nomor']]);
        $this->assertSame([['kind' => 'focus_lost']], array_map(static fn (array $e): array => ['kind' => $e['kind']], $data['peristiwa_integritas']));
        // Tidak pernah: teks soal, opsi, kunci, poin, atau baris orang lain.
        foreach (['bumi itu bulat', 'Opsi kedua', 'correct', 'answer', 'points', 'RAHASIA-JAWABAN-CITRA', 'Mahasiswa Citra', (string) $d['mhsC']['id']] as $teks) {
            $this->assertStringNotContainsString($teks, $r->body, $teks);
        }
        $this->assertSame(1, (int) $this->db->query("SELECT COUNT(*) AS n FROM audit_log WHERE action = 'data.diekspor' AND actor_id = '{$d['mhsA']['id']}' AND target_id = '{$d['mhsA']['id']}'")->fetch_assoc()['n']);
        // Paling banyak sekali per 60 detik.
        $this->assertSame(429, $this->kirim($ani, '/data-saya/unduh')->status);

        // Sesudah hasil kelasnya dirilis, skor objektifnya ikut.
        $this->db->query("UPDATE sessions SET end_at = UTC_TIMESTAMP() - INTERVAL 1 MINUTE WHERE id = {$s}");
        $this->assertStringEndsWith('?pesan=dirilis', (string) $this->kirim($d['dosen'], "/sesi/{$s}/rilis", ['tingkat' => 'nilai'])->header('Location'));
        $this->redisTes()->del('ekspor:' . $d['mhsA']['id']);
        $p = json_decode($this->kirim($ani, '/data-saya/unduh')->body, true)['pengerjaan'][0];
        $this->assertSame([2.0, 2.0], [(float) $p['nilai_objektif'], (float) $p['poin_objektif']]);

        // Staf: sesi yang dijalankan dan ujian yang dibuat — judul dan waktu saja, tanpa jawaban siapa pun.
        $r    = $this->kirim($d['dosen'], '/data-saya/unduh');
        $data = json_decode($r->body, true);
        $this->assertSame(['Ujian kendali'], array_column($data['ujian_dibuat'], 'judul'));
        $this->assertContains('sesi.dimulai', array_column($data['sesi_dijalankan'], 'tindakan'));
        $this->assertSame([], $data['pengerjaan']);
        foreach (['uraian milik Ani', 'RAHASIA-JAWABAN-CITRA', (string) $d['mhsA']['id']] as $teks) {
            $this->assertStringNotContainsString($teks, $r->body, $teks);
        }
        // Tanpa sesi: ke halaman masuk.
        $this->assertContains($this->tamu()->get('/data-saya')->status, [302, 303, 307]);
    }
}
