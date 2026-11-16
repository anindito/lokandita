<?php

declare(strict_types=1);

namespace Tests\Judge;

use App\Libraries\Antrean;
use Tests\Support\KodeCase;

/**
 * Web ↔ judge worker (KONTRAK.md §6), dari ujung ke ujung dan tanpa tiruan:
 * baris judge_jobs di MySQL, id di Redis, worker Python sungguhan di host yang
 * menjalankan program di kontainer, dan hasilnya dibaca lewat HTTP.
 */
final class EksekusiTest extends KodeCase
{
    private const PROGRAM = "#include <stdio.h>\nint main(void){int n;scanf(\"%d\",&n);printf(\"%d\\n\",n+1);return 0;}\n";

    /**
     * Seperti peramban: bertanya tiap 250 ms sampai pekerjaan selesai.
     *
     * @return array<string, mixed>
     */
    private function tunggu(\Lokandita\Auth\Testing\Http $http, string $id, int $detik = 40): array
    {
        $batas = microtime(true) + $detik;
        $data  = [];
        while (microtime(true) < $batas) {
            $r = $http->get('/api/executions/' . $id);
            $this->assertSame(200, $r->status, $r->body);
            $data = json_decode($r->body, true, 512, JSON_THROW_ON_ERROR);
            if (in_array($data['status'], ['DONE', 'FAILED'], true)) {
                return $data;
            }
            usleep(250_000);
        }
        $this->fail("pekerjaan {$id} tidak selesai dalam {$detik} detik: " . json_encode($data));
    }

    public function testRunDariWebSampaiHasilDibacaLewatHttp(): void
    {
        $mhs  = $this->user('mahasiswa', 'Mahasiswa Rani');
        $http = $this->sebagai($mhs);

        $id = Antrean::kirim('RUN', ['language' => 'c', 'source' => self::PROGRAM, 'stdin' => "41\n"], $mhs['id']);

        $baris = $this->row("SELECT kind, priority, status, created_by FROM judge_jobs WHERE id = '{$id}'");
        $this->assertSame(['kind' => 'RUN', 'priority' => 'normal', 'created_by' => $mhs['id']], array_diff_key($baris, ['status' => 1]));

        $data = $this->tunggu($http, $id);
        $this->assertSame('DONE', $data['status']);
        $this->assertSame('RUN', $data['kind']);
        // assertEquals: MySQL menyimpan kunci JSON dalam urutannya sendiri.
        $this->assertEquals(['state' => 'done', 'current' => 1, 'total' => 1], $data['progress']);
        $this->assertSame('OK', $data['result']['verdict']);
        $this->assertSame("42\n", $data['result']['stdout']);
        $this->assertSame(['id', 'kind', 'status', 'progress', 'result'], array_keys($data));
    }

    public function testJawabanTidakPernahMemuatPayload(): void
    {
        $mhs = $this->user('mahasiswa', 'Mahasiswa Rani');
        $r   = $this->sebagai($mhs)->get('/api/executions/' . $this->job($mhs));
        $this->assertSame(200, $r->status);
        $this->assertStringContainsString('no-store', (string) $r->header('Cache-Control'));
        $this->assertStringNotContainsString('SUMBER-RAHASIA', $r->body);
        $this->assertStringNotContainsString('MASUKAN-RAHASIA', $r->body);
        $this->assertStringNotContainsString('payload', $r->body);
    }

    public function testPekerjaanOrangLainDijawabSamaDenganYangTidakAda(): void
    {
        $d   = $this->dunia();
        $job = $this->job($d['mhsA']);
        $ada = $this->sebagai($d['mhsA'])->get('/api/executions/' . $job);
        $this->assertSame(200, $ada->status);

        $tidakAda = $this->sebagai($d['mhsB'])->get('/api/executions/' . \Lokandita\Auth\Testing\Fixtures::uuid());
        $this->assertSame(404, $tidakAda->status);
        $this->assertSame('{"error":"execution_not_found"}', $tidakAda->body);

        // Mahasiswa lain, pengajar kelasnya, pengampu mata kuliahnya dan superadmin: semuanya 404 yang sama.
        foreach (['mhsB', 'pengajarA', 'pengampuX', 'superadmin'] as $p) {
            $r = $this->sebagai($d[$p])->get('/api/executions/' . $job);
            $this->assertSame(404, $r->status, $p);
            $this->assertSame($tidakAda->body, $r->body, $p);
            $this->assertStringNotContainsString('hasil', $r->body, $p);
        }
    }

    public function testPekerjaanSistemTidakTerbacaSiapaPun(): void
    {
        $d   = $this->dunia();
        $job = $this->job(null);
        foreach (['mhsA', 'pengampuX', 'superadmin'] as $p) {
            $this->assertSame(404, $this->sebagai($d[$p])->get('/api/executions/' . $job)->status, $p);
        }
    }

    public function testTanpaSesiDialihkanKeLoginDanIdAnehDitolak(): void
    {
        $mhs = $this->user('mahasiswa', 'Mahasiswa Rani');
        $job = $this->job($mhs);
        $r   = $this->tamu()->get('/api/executions/' . $job);
        $this->assertSame(302, $r->status);
        $this->assertStringContainsString('/login?next=', (string) $r->header('Location'));
        $this->assertStringNotContainsString('hasil', $r->body);

        $http = $this->sebagai($mhs);
        foreach (['abc', '1', "{$job}x", strtoupper($job)] as $aneh) {
            $this->assertSame(404, $http->get('/api/executions/' . $aneh)->status, $aneh);
        }
        // Tanda kutip di URL sudah ditolak CodeIgniter (400) sebelum sampai ke rute.
        $r = $http->get("/api/executions/'%20OR%201=1--");
        $this->assertContains($r->status, [400, 404]);
        $this->assertStringNotContainsString('hasil', $r->body);
    }

    public function testKunciUnikMenghasilkanSatuPekerjaan(): void
    {
        $mhs  = $this->user('mahasiswa', 'Mahasiswa Rani');
        $isi  = ['language' => 'c', 'source' => self::PROGRAM, 'stdin' => "1\n"];
        $satu = Antrean::kirim('RUN', $isi, $mhs['id'], 'normal', 'uji-kunci-unik');
        $dua  = Antrean::kirim('RUN', $isi, $mhs['id'], 'normal', 'uji-kunci-unik');
        $this->assertSame($satu, $dua);
        $this->assertSame(1, $this->hitung("SELECT COUNT(*) FROM judge_jobs WHERE idempotency_key = 'uji-kunci-unik'"));
        $data = $this->tunggu($this->sebagai($mhs), $satu);
        $this->assertSame("2\n", $data['result']['stdout']);
        $this->assertSame(1, (int) $this->row("SELECT attempts FROM judge_jobs WHERE id = '{$satu}'")['attempts']);
    }

    public function testPrioritasRendahMasukAntreanLowDanTetapDikerjakan(): void
    {
        $mhs = $this->user('mahasiswa', 'Mahasiswa Rani');
        $id  = Antrean::kirim('RUN', ['language' => 'python', 'source' => "print(int(input()) + 1)\n", 'stdin' => "6\n"], $mhs['id'], 'low');
        $this->assertSame('low', $this->row("SELECT priority FROM judge_jobs WHERE id = '{$id}'")['priority']);
        $this->assertSame("7\n", $this->tunggu($this->sebagai($mhs), $id)['result']['stdout']);
    }

    public function testJenisYangBelumDidukungDijawabFailedBukanDiam(): void
    {
        $mhs  = $this->user('mahasiswa', 'Mahasiswa Rani');
        $id   = Antrean::kirim('SUBMIT', ['language' => 'c', 'source' => self::PROGRAM, 'instance_id' => 1, 'submission_id' => 1], $mhs['id']);
        $data = $this->tunggu($this->sebagai($mhs), $id);
        $this->assertSame('FAILED', $data['status']);
        $this->assertSame('unsupported_kind', $data['result']['error']);
    }

    public function testKirimMenolakJenisPrioritasDanTransaksiTerbuka(): void
    {
        foreach ([['HAPUS', 'normal'], ['RUN', 'tinggi']] as [$jenis, $prioritas]) {
            try {
                Antrean::kirim($jenis, [], null, $prioritas);
                $this->fail("{$jenis}/{$prioritas} seharusnya ditolak");
            } catch (\InvalidArgumentException) {
                $this->addToAssertionCount(1);
            }
        }
        $db = db_connect();
        $db->transBegin();
        try {
            Antrean::kirim('RUN', ['language' => 'c', 'source' => 'x'], null);
            $this->fail('kirim di dalam transaksi seharusnya ditolak');
        } catch (\LogicException) {
            $this->addToAssertionCount(1);
        } finally {
            $db->transRollback();
        }
        $this->assertSame(0, $this->hitung('SELECT COUNT(*) FROM judge_jobs'));
    }

    public function testPerintahJudgeCobaLulusDanGagalDenganKodeKeluarYangBenar(): void
    {
        foreach (['c', 'cpp', 'python'] as $bahasa) {
            $keluaran = [];
            exec('cd ' . escapeshellarg(ROOTPATH) . ' && php spark judge:coba --bahasa ' . $bahasa . ' 2>&1', $keluaran, $kode);
            $teks = implode("\n", $keluaran);
            $this->assertSame(0, $kode, $teks);
            $this->assertStringContainsString('judge:coba LULUS', $teks);
            $this->assertMatchesRegularExpression('/worker: commit [0-9a-f]{40},/', $teks);
        }
        $this->assertSame(3, $this->hitung("SELECT COUNT(*) FROM judge_jobs WHERE created_by IS NULL AND status = 'DONE'"));

        // Redis DB 14 kosong: tidak ada denyut worker di sana, jadi perintah harus gagal tanpa mengirim apa pun.
        $tanpaWorker = preg_replace('#/15$#', '/14', (string) getenv('REDIS_URL'));
        $keluaran    = [];
        exec('cd ' . escapeshellarg(ROOTPATH) . ' && REDIS_URL=' . escapeshellarg($tanpaWorker) . ' php spark judge:coba 2>&1', $keluaran, $kode);
        $this->assertNotSame(0, $kode);
        $this->assertStringContainsString('judge:coba GAGAL: worker tidak berdenyut', implode("\n", $keluaran));
        $this->assertSame(3, $this->hitung('SELECT COUNT(*) FROM judge_jobs'));
    }
}
