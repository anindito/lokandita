<?php

declare(strict_types=1);

namespace Tests\Latihan;

use App\Libraries\Penilaian;
use Tests\Support\KodeCase;

/**
 * Aturan nilai (SPEC.md §7) terhadap MySQL sungguhan. Hasil judge di sini
 * disisipkan langsung ke judge_jobs dalam bentuk KONTRAK.md §6; jalur lengkap
 * dari Submit di peramban sampai nilai ada di KirimTest.
 */
final class PenilaianTest extends KodeCase
{
    private int $detik = 0;

    /**
     * @return array<string, mixed>
     */
    private function hasil(int $lulus, int $total = 10, string $verdict = ''): array
    {
        $verdict = $verdict !== '' ? $verdict : ($lulus === $total ? 'AC' : 'WA');
        $tes     = [];
        for ($i = 0; $i < $total; $i++) {
            $tes[] = ['index' => $i, 'verdict' => $i < $lulus ? 'AC' : 'WA', 'duration_ms' => 3 + $i];
        }

        return ['verdict' => $verdict, 'compile_log' => '', 'tests' => $tes, 'passed' => $lulus, 'total' => $total, 'concepts' => null, 'missing' => []];
    }

    /**
     * Satu kiriman MENUNGGU dengan pekerjaan judge yang sudah berakhir; tiap kiriman satu detik lebih baru.
     *
     * @param array<string, mixed>      $mhs
     * @param array<string, int>        $soal  dari soalSiap()
     * @param array<string, mixed>|null $hasil null = pekerjaan FAILED
     */
    private function kirim(array $mhs, array $soal, ?array $hasil): int
    {
        $ins = $this->row("SELECT id FROM problem_instances WHERE user_id = '{$mhs['id']}' AND meeting_problem_id = {$soal['mp']}");
        if ($ins === null) {
            $this->db->query("INSERT INTO problem_instances (user_id, meeting_problem_id, problem_version_id, seed, tests, created_at)
                VALUES ('{$mhs['id']}', {$soal['mp']}, {$soal['versi']}, 1, '[]', UTC_TIMESTAMP())");
            $ins = ['id' => $this->db->insert_id];
        }
        $job   = \Lokandita\Auth\Testing\Fixtures::uuid();
        $waktu = gmdate('Y-m-d H:i:s', 1_800_000_000 + $this->detik++);
        $json  = $this->db->real_escape_string(json_encode($hasil ?? ['error' => 'judge_unavailable']));
        $this->db->query("INSERT INTO judge_jobs (id, kind, priority, status, payload, result, created_by, created_at, finished_at)
            VALUES ('{$job}', 'SUBMIT', 'normal', '" . ($hasil === null ? 'FAILED' : 'DONE') . "', '{}', '{$json}', '{$mhs['id']}', '{$waktu}', '{$waktu}')");
        $this->db->query("INSERT INTO submissions (user_id, meeting_problem_id, problem_version_id, instance_id, language, source, job_id, created_at)
            VALUES ('{$mhs['id']}', {$soal['mp']}, {$soal['versi']}, {$ins['id']}, 'cpp', 'int main(){}', '{$job}', '{$waktu}')");

        return (int) $this->db->insert_id;
    }

    /**
     * @return array<string, string>|null
     */
    private function nilaiPertemuan(array $mhs, int $pertemuan): ?array
    {
        return $this->row("SELECT total_points, counted_points, target_points, score, surplus, very_easy_counted, submissions, reached_at
            FROM meeting_scores WHERE user_id = '{$mhs['id']}' AND meeting_id = {$pertemuan}");
    }

    public function testNilaiKirimanPoinKaliBagianTesYangLulusDuaDesimal(): void
    {
        $this->assertSame(10.0, Penilaian::nilaiKiriman(10, $this->hasil(10)));
        $this->assertSame(7.0, Penilaian::nilaiKiriman(10, $this->hasil(7)));
        $this->assertSame(0.0, Penilaian::nilaiKiriman(15, $this->hasil(0)));
        // 8 × 7/12 = 4,6666… -> 4,67; 3 × 1/12 = 0,25; 5 × 11/12 = 4,58.
        $this->assertSame(4.67, Penilaian::nilaiKiriman(8, $this->hasil(7, 12)));
        $this->assertSame(0.25, Penilaian::nilaiKiriman(3, $this->hasil(1, 12)));
        $this->assertSame(4.58, Penilaian::nilaiKiriman(5, $this->hasil(11, 12)));
        // Galat kompilasi 0; konsep yang disyaratkan tidak ada: × 0,5.
        $this->assertSame(0.0, Penilaian::nilaiKiriman(15, ['verdict' => 'CE', 'tests' => [], 'passed' => 0, 'total' => 10]));
        $this->assertSame(7.5, Penilaian::nilaiKiriman(15, $this->hasil(10, 10, 'CONCEPT_NOT_MET')));
        $this->assertSame(1.5, Penilaian::nilaiKiriman(3, $this->hasil(12, 12, 'CONCEPT_NOT_MET')));
    }

    public function testNilaiPertemuanKuotaMudahSekaliBatasSeratusDanSurplus(): void
    {
        $ms = static fn (float ...$n) => array_map(static fn ($x) => ['tingkat' => 'MUDAH_SEKALI', 'nilai' => $x], $n);
        $s  = static fn (string $t, float $n) => ['tingkat' => $t, 'nilai' => $n];

        // Enam soal mudah sekali penuh: hanya 4 terhitung (12 poin), surplus 6.
        $n = Penilaian::nilaiPertemuan($ms(3, 3, 3, 3, 3, 3), 60);
        $this->assertSame(['total' => 18.0, 'terhitung' => 12.0, 'nilai' => 20.0, 'surplus' => 6.0, 'mudahSekali' => 4], $n);

        // Empat yang TERBAIK, bukan empat yang pertama: 3 + 3 + 2,5 + 2 = 10,5.
        $n = Penilaian::nilaiPertemuan($ms(0.5, 1, 3, 2, 3, 2.5), 60);
        $this->assertSame([12.0, 10.5, 17.5, 1.5, 4], [$n['total'], $n['terhitung'], $n['nilai'], $n['surplus'], $n['mudahSekali']]);

        // Kuota dihitung dari soal yang nilainya di atas nol.
        $this->assertSame(2, Penilaian::nilaiPertemuan($ms(3, 0, 1.5, 0), 60)['mudahSekali']);

        // Tepat target: 100, surplus 0.
        $n = Penilaian::nilaiPertemuan([$s('SULIT', 15), $s('SULIT', 15), $s('SEDANG', 10), $s('SEDANG', 10), $s('MUDAH_SEDANG', 8), $s('MUDAH', 2)], 60);
        $this->assertSame([60.0, 60.0, 100.0, 0.0], [$n['total'], $n['terhitung'], $n['nilai'], $n['surplus']]);

        // Di atas target: nilai tetap 100; kelebihannya surplus, juga yang dari kuota mudah sekali.
        $n = Penilaian::nilaiPertemuan(array_merge([$s('SULIT', 15), $s('SULIT', 15), $s('SULIT', 15), $s('SULIT', 15), $s('SEDANG', 10)], $ms(3, 3, 3, 3, 3)), 60);
        $this->assertSame([85.0, 82.0, 100.0, 25.0, 4], [$n['total'], $n['terhitung'], $n['nilai'], $n['surplus'], $n['mudahSekali']]);

        // Target lain, dan pembulatan dua desimal: 20 / 30 = 66,67.
        $this->assertSame(66.67, Penilaian::nilaiPertemuan([$s('SEDANG', 10), $s('SEDANG', 10)], 30)['nilai']);
        $this->assertSame(['total' => 0.0, 'terhitung' => 0.0, 'nilai' => 0.0, 'surplus' => 0.0, 'mudahSekali' => 0], Penilaian::nilaiPertemuan([], 60));
    }

    public function testNilaiTersimpanAdalahYangTerbaikDanPercobaanDicatat(): void
    {
        $d    = $this->dunia();
        $soal = $this->soalSiap($d['pertemuanX1'], 'sedang-a', 'SEDANG');

        $k1 = $this->kirim($d['mhsA'], $soal, $this->hasil(4));
        $this->assertSame(1, Penilaian::selesaikan($d['mhsA']['id']));
        $k = $this->row("SELECT status, verdict, passed, total, score, duration_ms, tests FROM submissions WHERE id = {$k1}");
        $this->assertSame(['DINILAI', 'WA', '4', '10', '4.00', '12'], [$k['status'], $k['verdict'], $k['passed'], $k['total'], $k['score'], $k['duration_ms']]);
        $this->assertCount(10, json_decode($k['tests'], true));

        $k2 = $this->kirim($d['mhsA'], $soal, $this->hasil(9));
        $this->kirim($d['mhsA'], $soal, $this->hasil(2));           // lebih buruk: tidak menurunkan nilai
        $this->kirim($d['mhsA'], $soal, ['verdict' => 'CE', 'compile_log' => 'main.cpp:1:1: error: x', 'compile_error' => 'compiler', 'tests' => [], 'passed' => 0, 'total' => 10, 'concepts' => null, 'missing' => []]);
        $k5 = $this->kirim($d['mhsA'], $soal, $this->hasil(9));     // sama dengan yang terbaik: yang pertama mencapainya tetap dipakai
        $this->assertSame(4, Penilaian::selesaikan($d['mhsA']['id']));

        $n = $this->row("SELECT * FROM problem_scores WHERE user_id = '{$d['mhsA']['id']}' AND meeting_problem_id = {$soal['mp']}");
        $this->assertSame(['9.00', (string) $k2, '5', 'SEDANG', '10'], [$n['best_score'], $n['best_submission_id'], $n['attempts'], $n['difficulty'], $n['points']]);
        $this->assertSame($this->row("SELECT judged_at FROM submissions WHERE id = {$k2}")['judged_at'], $n['reached_at']);
        $this->assertNotSame((string) $k5, $n['best_submission_id']);
        $ce = $this->row("SELECT score, verdict, compile_log FROM submissions WHERE verdict = 'CE'");
        $this->assertSame(['0.00', 'main.cpp:1:1: error: x'], [$ce['score'], $ce['compile_log']]);

        $p = $this->nilaiPertemuan($d['mhsA'], $d['pertemuanX1']);
        $this->assertSame(['9.00', '9.00', '60', '15.00', '0.00', '0', '5'],
            [$p['total_points'], $p['counted_points'], $p['target_points'], $p['score'], $p['surplus'], $p['very_easy_counted'], $p['submissions']]);
        // Nilai pertemuan 15 tercapai pada kiriman kedua dan tidak berubah sesudahnya.
        $this->assertSame($this->row("SELECT judged_at FROM submissions WHERE id = {$k2}")['judged_at'], $p['reached_at']);

        // Tidak ada yang tersisa; memanggil lagi tidak mengubah apa pun.
        $potret = [$n, $p];
        $this->assertSame(0, Penilaian::selesaikan());
        $this->assertSame($potret, [
            $this->row("SELECT * FROM problem_scores WHERE user_id = '{$d['mhsA']['id']}' AND meeting_problem_id = {$soal['mp']}"),
            $this->nilaiPertemuan($d['mhsA'], $d['pertemuanX1']),
        ]);
    }

    public function testEmpatMudahSekaliTerbaikDipilihUlangSetiapKirimanBaru(): void
    {
        $d = $this->dunia();
        $this->db->query("UPDATE meetings SET target_points = 20 WHERE id = {$d['pertemuanX1']}");
        $ms = [];
        foreach (range(1, 6) as $i) {
            $ms[$i] = $this->soalSiap($d['pertemuanX1'], "mudah-sekali-{$i}", 'MUDAH_SEKALI');
        }
        $sulit = $this->soalSiap($d['pertemuanX1'], 'sulit-a', 'SULIT');

        // Lima soal mudah sekali, nilainya 0,3 · 0,6 · 0,9 · 1,2 · 1,5 (1–5 dari 10 tes).
        foreach (range(1, 5) as $i) {
            $this->kirim($d['mhsA'], $ms[$i], $this->hasil($i));
        }
        Penilaian::selesaikan();
        $p = $this->nilaiPertemuan($d['mhsA'], $d['pertemuanX1']);
        // Terhitung: 0,6 + 0,9 + 1,2 + 1,5 = 4,2 dari total 4,5.
        $this->assertSame(['4.50', '4.20', '21.00', '0.30', '4'], [$p['total_points'], $p['counted_points'], $p['score'], $p['surplus'], $p['very_easy_counted']]);

        // Soal pertama (yang tadi tidak terhitung) diperbaiki menjadi penuh: kini ia terhitung dan 0,6 keluar.
        $this->kirim($d['mhsA'], $ms[1], $this->hasil(10));
        Penilaian::selesaikan();
        $p = $this->nilaiPertemuan($d['mhsA'], $d['pertemuanX1']);
        // Total 0,6+0,9+1,2+1,5+3 = 7,2; terhitung 3+1,5+1,2+0,9 = 6,6.
        $this->assertSame(['7.20', '6.60', '33.00', '0.60', '4'], [$p['total_points'], $p['counted_points'], $p['score'], $p['surplus'], $p['very_easy_counted']]);

        // Soal keenam penuh dan satu soal sulit penuh: terhitung 3+3+1,5+1,2 + 15 = 23,7 >= target 20.
        $this->kirim($d['mhsA'], $ms[6], $this->hasil(10));
        $terakhir = $this->kirim($d['mhsA'], $sulit, $this->hasil(10));
        Penilaian::selesaikan();
        $p = $this->nilaiPertemuan($d['mhsA'], $d['pertemuanX1']);
        // Total 25,2; nilai dibatasi 100; surplus = 25,2 − 20 = 5,2.
        $this->assertSame(['25.20', '23.70', '100.00', '5.20', '4', '8'], [$p['total_points'], $p['counted_points'], $p['score'], $p['surplus'], $p['very_easy_counted'], $p['submissions']]);
        $this->assertSame($this->row("SELECT judged_at FROM submissions WHERE id = {$terakhir}")['judged_at'], $p['reached_at']);

        // Kiriman lagi sesudah 100 menambah surplus, tidak menaikkan nilai, dan tidak menggeser waktu tercapainya.
        $this->kirim($d['mhsA'], $ms[2], $this->hasil(10));
        Penilaian::selesaikan();
        $q = $this->nilaiPertemuan($d['mhsA'], $d['pertemuanX1']);
        $this->assertSame(['100.00', '7.60', $p['reached_at']], [$q['score'], $q['surplus'], $q['reached_at']]);
    }

    public function testKonsepTidakTerpenuhiSetengahNilaiDanKegagalanJudgeBukanPercobaan(): void
    {
        $d    = $this->dunia();
        $soal = $this->soalSiap($d['pertemuanX1'], 'sulit-a', 'SULIT');

        $hasil = ['missing' => ['PERULANGAN'], 'concepts' => ['IO_DASAR']] + $this->hasil(10, 10, 'CONCEPT_NOT_MET');
        $k1    = $this->kirim($d['mhsA'], $soal, $hasil);
        $k2    = $this->kirim($d['mhsA'], $soal, null); // judge_unavailable
        $this->assertSame(2, Penilaian::selesaikan());

        $a = $this->row("SELECT status, verdict, score, missing_concepts FROM submissions WHERE id = {$k1}");
        $this->assertSame(['DINILAI', 'CONCEPT_NOT_MET', '7.50', '["PERULANGAN"]'], [$a['status'], $a['verdict'], $a['score'], $a['missing_concepts']]);
        $b = $this->row("SELECT status, verdict, score FROM submissions WHERE id = {$k2}");
        $this->assertSame(['GAGAL', 'FAILED', null], [$b['status'], $b['verdict'], $b['score']]);

        $n = $this->row('SELECT best_score, attempts FROM problem_scores');
        $this->assertSame(['7.50', '1'], [$n['best_score'], $n['attempts']], 'kegagalan judge tidak dihitung sebagai percobaan');
        $this->assertSame('1', $this->nilaiPertemuan($d['mhsA'], $d['pertemuanX1'])['submissions']);

        // Mahasiswa yang kirimannya hanya gagal di judge tidak punya nilai sama sekali.
        $this->kirim($d['mhsB'], $soal, null);
        Penilaian::selesaikan();
        $this->assertNull($this->nilaiPertemuan($d['mhsB'], $d['pertemuanX1']));
    }

    public function testKirimanYangBelumSelesaiDibiarkanDanNilaiTiapMahasiswaTerpisah(): void
    {
        $d    = $this->dunia();
        $soal = $this->soalSiap($d['pertemuanX1'], 'mudah-a', 'MUDAH');
        $kA   = $this->kirim($d['mhsA'], $soal, $this->hasil(10));
        $kB   = $this->kirim($d['mhsB'], $soal, $this->hasil(5));
        $this->db->query("UPDATE judge_jobs SET status = 'RUNNING', result = NULL WHERE id = (SELECT job_id FROM submissions WHERE id = {$kB})");

        // Hanya milik mahasiswa A yang diminta, dan hanya itu yang selesai.
        $this->assertSame(0, Penilaian::selesaikan($d['mhsY']['id']));
        $this->assertSame(1, Penilaian::selesaikan());
        $this->assertSame('MENUNGGU', $this->row("SELECT status FROM submissions WHERE id = {$kB}")['status']);
        $this->assertSame('5.00', $this->row("SELECT best_score FROM problem_scores WHERE user_id = '{$d['mhsA']['id']}'")['best_score']);
        $this->assertSame(0, $this->hitung("SELECT COUNT(*) FROM problem_scores WHERE user_id = '{$d['mhsB']['id']}'"));

        $this->db->query("UPDATE judge_jobs SET status = 'DONE', result = '" . json_encode($this->hasil(5)) . "' WHERE id = (SELECT job_id FROM submissions WHERE id = {$kB})");
        $this->assertSame(1, Penilaian::selesaikan(null, $this->row("SELECT job_id FROM submissions WHERE id = {$kB}")['job_id']));
        $this->assertSame('2.50', $this->row("SELECT best_score FROM problem_scores WHERE user_id = '{$d['mhsB']['id']}'")['best_score']);
        $this->assertSame('5.00', $this->row("SELECT best_score FROM problem_scores WHERE user_id = '{$d['mhsA']['id']}'")['best_score']);
        $this->assertSame((string) $kA, $this->row("SELECT best_submission_id FROM problem_scores WHERE user_id = '{$d['mhsA']['id']}'")['best_submission_id']);
    }

    public function testMengubahTargetMenghitungUlangSemuaMahasiswaDanTabelTurunanBisaDibangunLagi(): void
    {
        $d     = $this->dunia();
        $sulit = $this->soalSiap($d['pertemuanX1'], 'sulit-a', 'SULIT');
        $mudah = $this->soalSiap($d['pertemuanX1'], 'mudah-a', 'MUDAH');
        $this->kirim($d['mhsA'], $sulit, $this->hasil(10));
        $this->kirim($d['mhsA'], $mudah, $this->hasil(10));
        $this->kirim($d['mhsB'], $sulit, $this->hasil(5));
        Penilaian::selesaikan();
        $this->assertSame('33.33', $this->nilaiPertemuan($d['mhsA'], $d['pertemuanX1'])['score']);   // 20 / 60
        $this->assertSame('12.50', $this->nilaiPertemuan($d['mhsB'], $d['pertemuanX1'])['score']);   // 7,5 / 60

        // Pengampu menurunkan target menjadi 10 lewat halaman pertemuan.
        $http = $this->sebagai($d['pengampuX']);
        $r    = $http->post('/pertemuan/' . $d['pertemuanX1'] . '/latihan', ['csrf' => $this->csrf($http), 'target_points' => '10', 'autocomplete' => '1',
            'min_mudah_sekali' => '0', 'min_mudah' => '1', 'min_mudah_sedang' => '0', 'min_sedang' => '0', 'min_sulit' => '1']);
        $this->assertSame(303, $r->status);
        $a = $this->nilaiPertemuan($d['mhsA'], $d['pertemuanX1']);
        $this->assertSame(['100.00', '10.00', '10'], [$a['score'], $a['surplus'], $a['target_points']]);
        $this->assertSame('75.00', $this->nilaiPertemuan($d['mhsB'], $d['pertemuanX1'])['score']);

        // Tabel turunan dikosongkan lalu dibangun lagi dari kiriman: hasilnya sama persis (selain updated_at).
        $potret = fn () => [
            $this->db->query('SELECT user_id, meeting_problem_id, difficulty, points, best_score, best_submission_id, attempts, reached_at FROM problem_scores ORDER BY user_id, meeting_problem_id')->fetch_all(MYSQLI_ASSOC),
            $this->db->query('SELECT user_id, meeting_id, total_points, counted_points, target_points, score, surplus, very_easy_counted, submissions, reached_at FROM meeting_scores ORDER BY user_id')->fetch_all(MYSQLI_ASSOC),
        ];
        $awal = $potret();
        $this->db->query('DELETE FROM problem_scores');
        $this->db->query('DELETE FROM meeting_scores');
        $this->assertSame(2, Penilaian::hitungUlangPertemuan($d['pertemuanX1']));
        $this->assertSame($awal, $potret());
    }
}
