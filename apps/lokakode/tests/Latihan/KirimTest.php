<?php

declare(strict_types=1);

namespace Tests\Latihan;

use Lokandita\Auth\Testing\Http;
use Tests\Support\KodeCase;

/**
 * Run dan Submit dari ujung ke ujung (SPEC.md §7, §10, §11), tanpa tiruan:
 * permintaan HTTP seperti dari peramban, baris di MySQL, id di Redis, judge
 * worker sungguhan yang menjalankan program di kontainer, polling tiap detik,
 * lalu nilai di basis data dan di halaman.
 *
 * Setiap isi jawaban server yang diterima selama tes dikumpulkan, dan di akhir
 * diperiksa: masukan dan keluaran tes tersembunyi tidak pernah ada di dalamnya.
 */
final class KirimTest extends KodeCase
{
    private const BENAR = "#include <iostream>\nint main(){long a,b;std::cin>>a>>b;std::cout<<a+b<<\"\\n\";}\n";

    // Salah untuk a genap. Tes tersembunyi instans uji: a = 900027, 900040, … bergantian ganjil-genap.
    private const SEBAGIAN = "#include <iostream>\nint main(){long a,b;std::cin>>a>>b;std::cout<<(a%2?a+b:0)<<\"\\n\";}\n";

    /** @var list<string> semua isi jawaban yang diterima */
    private array $jawaban = [];

    /**
     * @return array<string, mixed>
     */
    private function duniaKerja(string $tingkat = 'MUDAH', array $versi = [], array $pertemuan = []): array
    {
        $d = $this->dunia();
        $d['soal'] = $this->soalSiap($d['pertemuanX1'], 'jumlah-dua-bilangan', $tingkat, $versi);
        $this->terbit($d['pertemuanX1'], $pertemuan);
        $this->jendela($d['pertemuanX1'], $d['kelasXA']);
        $d['instans'] = $this->instans($d['mhsA'], $d['soal']);
        $d['api']     = '/api/soal/' . $d['soal']['mp'];
        $this->lupakanLaju();

        return $d;
    }

    private function catat(Http $r): Http
    {
        $this->jawaban[] = $r->body;

        return $r;
    }

    /**
     * Seperti peramban: GET /api/executions/{id} berulang sampai pekerjaan berakhir.
     *
     * @return array<string, mixed>
     */
    private function pantau(Http $http, string $id, int $detik = 120): array
    {
        $batas = microtime(true) + $detik;
        $data  = [];
        while (microtime(true) < $batas) {
            $r = $this->catat($http->get('/api/executions/' . $id));
            $this->assertSame(200, $r->status, $r->body);
            $data = $r->json();
            if (in_array($data['status'], ['DONE', 'FAILED'], true)) {
                return $data;
            }
            usleep(300_000);
        }
        $this->fail("pekerjaan {$id} tidak selesai dalam {$detik} detik: " . json_encode($data));
    }

    /**
     * Tes tersembunyi instans uji tidak ada di jawaban mana pun yang diterima tes ini.
     */
    private function assertTesTersembunyiTidakBocor(int $bibit = 0): void
    {
        $semua = implode("\n", $this->jawaban);
        $this->assertNotSame('', $semua);
        foreach (array_slice(self::tesInstans($bibit), 2) as $i => $t) {
            $this->assertStringNotContainsString(trim($t['in']), $semua, "masukan tes tersembunyi {$i} bocor");
            $this->assertStringNotContainsString(trim($t['out']), $semua, "keluaran tes tersembunyi {$i} bocor");
            $this->assertStringNotContainsString(explode(' ', trim($t['in']))[0], $semua, "bilangan pertama tes tersembunyi {$i} bocor");
        }
    }

    public function testJalankanPadaTesContohTidakDinilaiDanTidakMenyentuhTesTersembunyi(): void
    {
        $d    = $this->duniaKerja();
        $http = $this->sebagai($d['mhsA']);
        $csrf = $this->csrf($http);
        $tes  = self::tesInstans();

        $r = $this->catat($http->post($d['api'] . '/jalankan', ['csrf' => $csrf, 'source' => self::BENAR]));
        $this->assertSame(202, $r->status, $r->body);
        $this->assertSame('contoh', $r->json()['kind']);
        $data = $this->pantau($http, $r->json()['id']);
        $this->assertSame(['DONE', 'RUN', 'AC'], [$data['status'], $data['kind'], $data['result']['verdict']]);
        $this->assertCount(2, $data['result']['samples']);
        foreach ($data['result']['samples'] as $i => $c) {
            $this->assertSame([$i, 'AC', $tes[$i]['out']], [$c['index'], $c['verdict'], $c['stdout']]);
        }
        $this->assertArrayNotHasKey('submission', $data);

        // Program yang salah: WA dengan keluarannya; galat kompilasi: log kompiler.
        $this->lupakanLaju();
        $salah = "#include <iostream>\nint main(){std::cout<<\"tidak tahu\\n\";}\n";
        $data  = $this->pantau($http, $this->catat($http->post($d['api'] . '/jalankan', ['csrf' => $csrf, 'source' => $salah]))->json()['id']);
        $this->assertSame(['WA', "tidak tahu\n"], [$data['result']['verdict'], $data['result']['samples'][0]['stdout']]);
        $this->lupakanLaju();
        $data = $this->pantau($http, $this->catat($http->post($d['api'] . '/jalankan', ['csrf' => $csrf, 'source' => 'int main( {']))->json()['id']);
        $this->assertSame('CE', $data['result']['verdict']);
        $this->assertStringContainsString('error', $data['result']['compile_log']);

        // Run tidak dinilai dan tidak dihitung; kode terakhir tersimpan sebagai draf.
        $this->assertSame(0, $this->hitung('SELECT COUNT(*) FROM submissions'));
        $this->assertSame(0, $this->hitung('SELECT COUNT(*) FROM problem_scores'));
        $this->assertSame('int main( {', $this->row('SELECT source FROM drafts')['source']);
        $this->assertTesTersembunyiTidakBocor();
    }

    public function testJalankanDenganMasukanKustomDanBenderaKompilerPertemuan(): void
    {
        $d    = $this->duniaKerja('MUDAH', [], ['compiler_flags' => '-Wall']);
        $http = $this->sebagai($d['mhsA']);
        $csrf = $this->csrf($http);

        $sumber = "#include <iostream>\nint main(){int tidak_dipakai;long a,b;std::cin>>a>>b;std::cout<<a*b<<\"\\n\";}\n";
        $r      = $this->catat($http->post($d['api'] . '/jalankan', ['csrf' => $csrf, 'source' => $sumber, 'kustom' => '1', 'stdin' => "6 7\r\n"]));
        $this->assertSame(202, $r->status, $r->body);
        $this->assertSame('kustom', $r->json()['kind']);
        $data = $this->pantau($http, $r->json()['id']);
        $this->assertSame(['OK', "42\n", 0], [$data['result']['verdict'], $data['result']['stdout'], $data['result']['exit_code']]);
        // -Wall dari pengaturan pertemuan sampai ke kompiler.
        $this->assertStringContainsString('unused variable', $data['result']['compile_log']);
        $payload = json_decode($this->row("SELECT payload FROM judge_jobs WHERE id = '{$data['id']}'")['payload'], true);
        $this->assertSame(['-Wall'], $payload['flags']);
        $this->assertArrayNotHasKey('instance_id', $payload);

        $this->lupakanLaju();
        $r = $http->post($d['api'] . '/jalankan', ['csrf' => $csrf, 'source' => $sumber, 'kustom' => '1', 'stdin' => str_repeat('1', 1024 * 1024 + 1)]);
        $this->assertSame(422, $r->status);
        $this->assertSame('stdin_besar', $r->json()['error']);
    }

    public function testKirimDariPermintaanSampaiNilaiDiBasisDataDanDiHalaman(): void
    {
        $d    = $this->duniaKerja();
        $http = $this->sebagai($d['mhsA']);
        $csrf = $this->csrf($http);
        $mp   = $d['soal']['mp'];

        // 1. Sebagian benar: tes tersembunyi dengan a genap salah.
        $genap = array_map(static fn ($t) => (int) explode(' ', $t['in'])[0] % 2 === 0, array_slice(self::tesInstans(), 2));
        $lulus = 10 - count(array_filter($genap));
        $this->assertGreaterThan(0, $lulus);
        $this->assertLessThan(10, $lulus);

        $r = $this->catat($http->post($d['api'] . '/kirim', ['csrf' => $csrf, 'source' => self::SEBAGIAN]));
        $this->assertSame(202, $r->status, $r->body);
        $kirim = $r->json();
        $baris = $this->row("SELECT * FROM submissions WHERE id = {$kirim['submission']}");
        $this->assertSame([$d['mhsA']['id'], (string) $mp, (string) $d['instans'], 'cpp', self::SEBAGIAN, $kirim['id'], 'MENUNGGU'],
            [$baris['user_id'], $baris['meeting_problem_id'], $baris['instance_id'], $baris['language'], $baris['source'], $baris['job_id'], $baris['status']]);

        $data = $this->pantau($http, $kirim['id']);
        $this->assertSame(['DONE', 'SUBMIT'], [$data['status'], $data['kind']]);
        $k = $data['submission'];
        $harap = round(5 * $lulus / 10, 2);
        $this->assertSame(['DINILAI', 'WA', $lulus, 10, 5], [$k['status'], $k['verdict'], $k['passed'], $k['total'], $k['points']]);
        $this->assertEqualsWithDelta($harap, $k['score'], 0.001);
        // SPEC.md §18: tes tersembunyi hanya menampilkan STATUS.
        $this->assertCount(10, $k['tests']);
        foreach ($k['tests'] as $i => $t) {
            $this->assertSame(['index', 'verdict', 'duration_ms'], array_keys($t));
            $this->assertSame($genap[$i] ? 'WA' : 'AC', $t['verdict']);
        }
        foreach ($data['result']['tests'] as $t) {
            $this->assertSame(['index', 'verdict', 'duration_ms'], array_keys($t));
        }
        $this->assertEqualsWithDelta($harap, $data['meeting']['terhitung'], 0.001);
        $this->assertEqualsWithDelta(round($harap / 60 * 100, 2), $data['meeting']['nilai'], 0.001);

        // 2. Galat kompilasi: 0, log kompiler ditampilkan, nilai terbaik tidak turun.
        $this->lupakanLaju();
        $data = $this->pantau($http, $this->catat($http->post($d['api'] . '/kirim', ['csrf' => $csrf, 'source' => "int main() { ini bukan C++; }\n"]))->json()['id']);
        $this->assertSame(['CE', 0.0, []], [$data['submission']['verdict'], (float) $data['submission']['score'], $data['submission']['tests']]);
        $this->assertStringContainsString('main.cpp:1', $data['submission']['compile_log']);
        $this->assertEqualsWithDelta($harap, $data['meeting']['terhitung'], 0.001);

        // 3. Benar: nilai penuh.
        $this->lupakanLaju();
        $data = $this->pantau($http, $this->catat($http->post($d['api'] . '/kirim', ['csrf' => $csrf, 'source' => self::BENAR]))->json()['id']);
        $this->assertSame(['AC', 10, 10], [$data['submission']['verdict'], $data['submission']['passed'], $data['submission']['total']]);
        $this->assertEqualsWithDelta(5.0, $data['submission']['score'], 0.001);
        $this->assertEqualsWithDelta(8.33, $data['meeting']['nilai'], 0.001);
        $this->assertEqualsWithDelta(55.0, $data['meeting']['kurang'], 0.001);

        // Basis data: nilai terbaik, tiga percobaan, nilai pertemuan.
        $n = $this->row("SELECT best_score, attempts, points FROM problem_scores WHERE user_id = '{$d['mhsA']['id']}' AND meeting_problem_id = {$mp}");
        $this->assertSame(['5.00', '3', '5'], [$n['best_score'], $n['attempts'], $n['points']]);
        $p = $this->row("SELECT score, counted_points, surplus, submissions FROM meeting_scores WHERE user_id = '{$d['mhsA']['id']}'");
        $this->assertSame(['8.33', '5.00', '0.00', '3'], [$p['score'], $p['counted_points'], $p['surplus'], $p['submissions']]);

        // Riwayat dan satu kiriman (dengan kode sumbernya sendiri).
        $riwayat = $this->catat($http->get($d['api'] . '/riwayat'))->json();
        $this->assertSame(5, $riwayat['points']);
        $this->assertSame(['AC', 'CE', 'WA'], array_column($riwayat['submissions'], 'verdict'));
        $this->assertArrayNotHasKey('source', $riwayat['submissions'][0]);
        $satu = $this->catat($http->get($d['api'] . '/riwayat/' . $kirim['submission']))->json();
        $this->assertSame([self::SEBAGIAN, 'WA', $lulus], [$satu['source'], $satu['verdict'], $satu['passed']]);

        // Halaman pertemuan dan halaman soal menampilkan nilai itu.
        $hal = $this->catat($http->get('/latihan/' . $d['pertemuanX1']));
        foreach (['Selesai', 'Didapat 5 dari 5', '3 kali kirim', '5 dari 60 poin', 'Masih perlu 55 poin'] as $teks) {
            $this->assertStringContainsString($teks, $hal->body, $teks);
        }
        $this->assertStringContainsString('Nilai terbaik: 5 dari 5', $this->catat($http->get('/latihan/soal/' . $mp))->body);

        // Worker tidak menyentuh tabel kiriman; log kerjanya tidak dibaca tes ini. Yang diperiksa: semua jawaban server.
        $this->assertTesTersembunyiTidakBocor();
        $this->assertSame(3, $this->hitung("SELECT COUNT(*) FROM judge_jobs WHERE kind = 'SUBMIT' AND status = 'DONE' AND created_by = '{$d['mhsA']['id']}'"));
    }

    public function testKonsepYangDisyaratkanTidakDipakaiMenjadiSetengahNilai(): void
    {
        $d    = $this->duniaKerja('SEDANG', ['required_in_submission' => '["PERULANGAN"]']);
        $http = $this->sebagai($d['mhsA']);
        $csrf = $this->csrf($http);

        $data = $this->pantau($http, $this->catat($http->post($d['api'] . '/kirim', ['csrf' => $csrf, 'source' => self::BENAR]))->json()['id']);
        $k    = $data['submission'];
        $this->assertSame(['CONCEPT_NOT_MET', 10, 10, ['PERULANGAN']], [$k['verdict'], $k['passed'], $k['total'], $k['missing']]);
        $this->assertEqualsWithDelta(5.0, $k['score'], 0.001, 'SEDANG 10 poin × 0,5');

        $this->lupakanLaju();
        $loop = "#include <iostream>\nint main(){long a,b,s=0;std::cin>>a>>b;for(int i=0;i<1;i++){s=a+b;}std::cout<<s<<\"\\n\";}\n";
        $data = $this->pantau($http, $this->catat($http->post($d['api'] . '/kirim', ['csrf' => $csrf, 'source' => $loop]))->json()['id']);
        $this->assertSame(['AC', []], [$data['submission']['verdict'], $data['submission']['missing']]);
        $this->assertEqualsWithDelta(10.0, $data['submission']['score'], 0.001);
        $this->assertSame('10.00', $this->row('SELECT best_score FROM problem_scores')['best_score']);
        $this->assertTesTersembunyiTidakBocor();
    }

    public function testBatasLajuDanSatuKirimanYangSedangDinilai(): void
    {
        $d    = $this->duniaKerja();
        $http = $this->sebagai($d['mhsA']);
        $csrf = $this->csrf($http);

        // Run: sekali per 3 detik.
        $this->assertSame(202, $http->post($d['api'] . '/jalankan', ['csrf' => $csrf, 'source' => self::BENAR, 'kustom' => '1', 'stdin' => "1 2\n"])->status);
        $r = $http->post($d['api'] . '/jalankan', ['csrf' => $csrf, 'source' => self::BENAR, 'kustom' => '1', 'stdin' => "1 2\n"]);
        $this->assertSame(429, $r->status);
        $this->assertSame('terlalu_cepat', $r->json()['error']);
        $this->assertContains((int) $r->header('Retry-After'), [1, 2, 3]);
        $this->assertMatchesRegularExpression('/^Tunggu [123] detik sebelum mencoba lagi\.$/', $r->json()['message']);
        $this->assertSame(1, $this->hitung("SELECT COUNT(*) FROM judge_jobs WHERE kind = 'RUN' AND created_by = '{$d['mhsA']['id']}' AND status <> 'DONE' OR (kind = 'RUN' AND payload LIKE '%1 2%')"));

        // Submit: sekali per 10 detik per soal.
        $r = $http->post($d['api'] . '/kirim', ['csrf' => $csrf, 'source' => self::BENAR]);
        $this->assertSame(202, $r->status, $r->body);
        $job = $r->json()['id'];
        $r   = $http->post($d['api'] . '/kirim', ['csrf' => $csrf, 'source' => self::BENAR]);
        // Kiriman pertama masih dinilai (409) atau, bila sudah selesai, masih dalam jeda 10 detik (429).
        $this->assertContains($r->status, [409, 429], $r->body);
        if ($r->status === 429) {
            $this->assertLessThanOrEqual(10, (int) $r->header('Retry-After'));
        }
        // Tanpa pembatas Redis pun, penjaga di basis data menahan kiriman kedua selama yang pertama dinilai.
        $this->db->query("UPDATE judge_jobs SET status = 'RUNNING', result = NULL WHERE id = '{$job}'");
        $this->db->query("UPDATE submissions SET status = 'MENUNGGU' WHERE job_id = '{$job}'");
        $this->lupakanLaju();
        $r = $http->post($d['api'] . '/kirim', ['csrf' => $csrf, 'source' => self::BENAR]);
        $this->assertSame(409, $r->status);
        $this->assertSame(['masih_dinilai', $job], [$r->json()['error'], $r->json()['id']]);
        $this->assertSame(1, $this->hitung('SELECT COUNT(*) FROM submissions'));
        $this->db->query("UPDATE judge_jobs SET status = 'FAILED', result = '{\"error\": \"judge_unavailable\"}' WHERE id = '{$job}'");

        // Kode kosong dan kode raksasa ditolak sebelum apa pun dibuat.
        $this->lupakanLaju();
        foreach ([['', 'sumber_kosong'], ["  \n\t", 'sumber_kosong'], [str_repeat('x', 256 * 1024 + 1), 'sumber_besar']] as [$sumber, $kode]) {
            foreach (['jalankan', 'kirim'] as $aksi) {
                $r = $http->post($d['api'] . '/' . $aksi, ['csrf' => $csrf, 'source' => $sumber]);
                $this->assertSame(422, $r->status, "{$aksi} {$kode}");
                $this->assertSame($kode, $r->json()['error']);
            }
        }
        $this->assertSame(1, $this->hitung('SELECT COUNT(*) FROM submissions'));
    }

    public function testKegagalanJudgeBukanPercobaanDanDilaporkanDenganJelas(): void
    {
        $d    = $this->duniaKerja();
        $http = $this->sebagai($d['mhsA']);
        $r    = $http->post($d['api'] . '/kirim', ['csrf' => $this->csrf($http), 'source' => self::BENAR]);
        $job  = $r->json()['id'];
        // Judge menyerah pada pekerjaan ini (KONTRAK.md §6: judge_unavailable).
        $this->tungguSampai(fn () => in_array($this->row("SELECT status FROM judge_jobs WHERE id = '{$job}'")['status'], ['DONE', 'FAILED'], true), 120, 'pekerjaan selesai');
        $this->db->query("UPDATE judge_jobs SET status = 'FAILED', result = '{\"error\": \"judge_unavailable\"}' WHERE id = '{$job}'");
        $this->db->query("UPDATE submissions SET status = 'MENUNGGU', verdict = NULL, score = NULL WHERE job_id = '{$job}'");
        $this->db->query('DELETE FROM problem_scores');
        $this->db->query('DELETE FROM meeting_scores');

        $data = $http->get('/api/executions/' . $job)->json();
        $this->assertSame(['FAILED', 'judge_unavailable'], [$data['status'], $data['result']['error']]);
        $this->assertSame(['GAGAL', 'FAILED', null], [$data['submission']['status'], $data['submission']['verdict'], $data['submission']['score']]);
        $this->assertSame(0, $this->hitung('SELECT COUNT(*) FROM problem_scores'), 'kegagalan judge bukan percobaan');
        $this->assertStringContainsString('Belum dicoba', $http->get('/latihan/' . $d['pertemuanX1'])->body);
    }

    public function testHasilTidakHilangBilaTabDitutupSebelumPenilaianSelesai(): void
    {
        $d    = $this->duniaKerja();
        $http = $this->sebagai($d['mhsA']);
        $r    = $http->post($d['api'] . '/kirim', ['csrf' => $this->csrf($http), 'source' => self::BENAR]);
        $this->assertSame(202, $r->status);
        $job = $r->json()['id'];

        // Peramban tidak pernah bertanya lagi. Judge selesai; hasilnya ada di judge_jobs, kiriman masih MENUNGGU.
        $this->tungguSampai(fn () => $this->row("SELECT status FROM judge_jobs WHERE id = '{$job}'")['status'] === 'DONE', 120, 'judge selesai');
        $this->assertSame('MENUNGGU', $this->row('SELECT status FROM submissions')['status']);
        $this->assertSame(0, $this->hitung('SELECT COUNT(*) FROM problem_scores'));

        // Mahasiswa kembali dan membuka halaman pertemuan: nilainya sudah ada.
        $hal = $http->get('/latihan/' . $d['pertemuanX1']);
        $this->assertStringContainsString('Didapat 5 dari 5', $hal->body);
        $this->assertSame(['DINILAI', '5.00'], array_values($this->row('SELECT status, score FROM submissions')));
        $this->assertSame('5.00', $this->row('SELECT best_score FROM problem_scores')['best_score']);
    }

    public function testPythonDariKirimSampaiNilai(): void
    {
        $d = $this->dunia();
        $d['soal'] = $this->soalSiap($d['pertemuanY1'], 'jumlah-python', 'MUDAH_SEDANG', ['language' => 'python', 'time_limit_ms' => 5000,
            'reference_solution' => "a, b = map(int, input().split())\nprint(a + b)\n"]);
        $this->terbit($d['pertemuanY1']);
        $this->jendela($d['pertemuanY1'], $d['kelasYA']);
        $this->instans($d['mhsY'], $d['soal']);
        $this->lupakanLaju();
        $http = $this->sebagai($d['mhsY']);
        $api  = '/api/soal/' . $d['soal']['mp'];

        $hal = $http->get('/latihan/soal/' . $d['soal']['mp']);
        $this->assertSame(200, $hal->status);
        $this->assertStringContainsString('"bahasa":"python"', $hal->body);

        $r = $this->catat($http->post($api . '/kirim', ['csrf' => $this->csrf($http), 'source' => "a, b = map(int, input().split())\nprint(a + b)\n"]));
        $this->assertSame(202, $r->status, $r->body);
        $data = $this->pantau($http, $r->json()['id'], 180);
        $this->assertSame(['AC', 10, 8], [$data['submission']['verdict'], $data['submission']['passed'], $data['submission']['points']]);
        $this->assertEqualsWithDelta(8.0, $data['submission']['score'], 0.001);
        $payload = json_decode($this->row("SELECT payload FROM judge_jobs WHERE id = '{$data['id']}'")['payload'], true);
        $this->assertSame(['python', []], [$payload['language'], $payload['flags']]);
        $this->assertTesTersembunyiTidakBocor();
    }
}
