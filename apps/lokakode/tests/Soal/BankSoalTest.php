<?php

declare(strict_types=1);

namespace Tests\Soal;

use Lokandita\Auth\Testing\Http;
use Tests\Support\KodeCase;
use Tests\Support\SoalBantu;

/**
 * Unggah, tinjau dan sunting soal lewat HTTP sungguhan (SPEC.md §8.1, §8.3),
 * dengan judge worker dan kontainer sungguhan di belakangnya.
 */
final class BankSoalTest extends KodeCase
{
    use SoalBantu;

    /**
     * Membuka halaman laporan berulang kali sampai impor selesai, seperti peramban.
     */
    private function tungguImpor(Http $http, string $jalur): Http
    {
        for ($i = 0; $i < 150; $i++) {
            $r = $http->get($jalur);
            $this->assertSame(200, $r->status);
            if ($r->header('Refresh') === null) {
                return $r;
            }
            $this->assertSame('2', $r->header('Refresh'));
            sleep(1);
        }
        $this->fail("impor {$jalur} tidak selesai dalam 150 detik");
    }

    public function testSuperadminMengunggahBerkasDanMelihatLaporanPerSoal(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['superadmin']);
        $json = $this->berkasImpor([
            $this->soalContoh('dua-bilangan'),
            $this->soalContoh('terbesar', ['reference_solution' => self::REF_IF]),
        ]);
        // Dipanjangkan sampai 3 MB dengan spasi: batas unggah bawaan PHP (2 MB) tidak boleh berlaku.
        $json = rtrim($json) . str_repeat(' ', 3 * 1024 * 1024) . "\n";

        $hal = $http->get('/impor');
        $this->assertSame(200, $hal->status);
        $this->assertStringContainsString('name="berkas"', $hal->body);

        $r = $http->upload('/impor', ['csrf' => $this->csrf($http)], 'berkas', 'pertemuan-1.json', $json);
        $this->assertSame(303, $r->status, $r->body);
        $jalur = (string) parse_url((string) $r->header('Location'), PHP_URL_PATH);
        $this->assertMatchesRegularExpression('#^/impor/[0-9a-f-]{36}$#', $jalur);

        $lap = $this->tungguImpor($http, $jalur);
        $this->assertStringContainsString('Selesai: 1 diterima, 1 ditolak, 0 tidak berubah, dari 2 soal.', $lap->body);
        $this->assertStringContainsString('pertemuan-1.json', $lap->body);
        $barisJson = $this->barisDari($json, '"reference_solution"', 2);
        $this->assertStringContainsString("solusi referensi, baris 6, kolom 5, baris JSON {$barisJson}:", $lap->body);
        $this->assertStringContainsString('Solusi referensi memakai konsep PERCABANGAN, yang belum diizinkan di pertemuan ini: «if».', $lap->body);
        $this->assertStringContainsString('no-store', (string) $lap->header('Cache-Control'));

        $soal = $this->row("SELECT id FROM problems WHERE slug = 'dua-bilangan'");
        $this->assertStringContainsString('href="/soal/' . $soal['id'] . '?versi=1"', $lap->body);
        $audit = json_decode($this->row("SELECT detail FROM audit_log WHERE action = 'soal.impor'")['detail'], true);
        $this->assertSame(['pertemuan-1.json', 'web', 2, 1, 1], [$audit['file_name'], $audit['source'], $audit['total'], $audit['accepted'], $audit['rejected']]);
        $this->assertStringContainsString('pertemuan-1.json', $http->get('/impor')->body);

        // Soal yang diterima muncul di bank soal pertemuan, menunggu tinjauan; belum dipakai.
        $bank = $http->get('/pertemuan/' . $d['pertemuanX1'])->body;
        $this->assertStringContainsString('dua-bilangan', $bank);
        $this->assertSame(0, $this->hitung('SELECT COUNT(*) FROM meeting_problems'));
    }

    public function testUnggahanYangBukanBerkasJsonDitolakTanpaMenyimpanApaPun(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['superadmin']);
        $csrf = $this->csrf($http);
        $awal = $this->hitung('SELECT COUNT(*) FROM imports');

        $r = $http->upload('/impor', ['csrf' => $csrf], 'berkas', 'soal.txt', $this->berkasImpor([$this->soalContoh('dua-bilangan')]));
        $this->assertSame(422, $r->status);
        $this->assertStringContainsString('Berkas harus berakhiran .json.', $r->body);
        $r = $http->post('/impor', ['csrf' => $csrf]);
        $this->assertSame(422, $r->status);
        $this->assertStringContainsString('Tidak ada berkas yang diterima.', $r->body);
        $this->assertSame($awal, $this->hitung('SELECT COUNT(*) FROM imports'));

        // Berkas JSON yang isinya salah diterima sebagai unggahan, lalu ditolak seluruhnya dengan pesan.
        $r = $http->upload('/impor', ['csrf' => $csrf], 'berkas', 'salah.json', $this->berkasImpor([$this->soalContoh('dua-bilangan')], ['meeting' => '1 — Pengenalan']));
        $this->assertSame(303, $r->status);
        $lap = $http->get((string) parse_url((string) $r->header('Location'), PHP_URL_PATH));
        $this->assertNull($lap->header('Refresh'));
        $this->assertStringContainsString('Berkas ditolak seluruhnya.', $lap->body);
        $this->assertStringContainsString('meeting harus bilangan bulat 1, tertulis &quot;1 — Pengenalan&quot;.', $lap->body);
        $this->assertSame(0, $this->hitung("SELECT COUNT(*) FROM judge_jobs WHERE kind = 'VALIDATE'"));
    }

    public function testPengampuDanPengajarTidakBisaMengunggah(): void
    {
        $d    = $this->dunia();
        $json = $this->berkasImpor([$this->soalContoh('dua-bilangan')]);
        $awal = [$this->hitung('SELECT COUNT(*) FROM imports'), $this->hitung('SELECT COUNT(*) FROM judge_jobs')];
        foreach (['pengampuX', 'pengajarA', 'mhsA'] as $p) {
            $http = $this->sebagai($d[$p]);
            $this->assertSame(403, $http->get('/impor')->status, $p);
            $r = $http->upload('/impor', ['csrf' => $this->csrf($http)], 'berkas', 'soal.json', $json);
            $this->assertSame(403, $r->status, $p);
        }
        $this->assertSame($awal, [$this->hitung('SELECT COUNT(*) FROM imports'), $this->hitung('SELECT COUNT(*) FROM judge_jobs')]);
        // Pengampu tidak melihat tautan impor; superadmin melihatnya.
        $this->assertStringNotContainsString('href="/impor"', $this->sebagai($d['pengampuX'])->get('/pertemuan/' . $d['pertemuanX1'])->body);
        $this->assertStringContainsString('href="/impor"', $this->sebagai($d['superadmin'])->get('/pertemuan/' . $d['pertemuanX1'])->body);
    }

    public function testHalamanSoalMenampilkanPernyataanDenganAman(): void
    {
        $d   = $this->dunia();
        $hal = $this->sebagai($d['pengampuX'])->get('/soal/' . $d['soalX']);
        $this->assertSame(200, $hal->status);
        // Markdown dirender, parameter {{n}} diisi, HTML mentah tidak dijalankan.
        $this->assertStringContainsString('Diberikan <strong>dua</strong> bilangan sampai 100.', $hal->body);
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $hal->body);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $hal->body);
        // Terjemahan: Inggris bertanda mesin, Arab belum ada.
        $this->assertStringContainsString('Terjemahan mesin', $hal->body);
        $this->assertStringContainsString('belum diterjemahkan', $hal->body);
        $this->assertStringContainsString('Menunggu tinjauan', $hal->body);
        // Kode sumber di-escape.
        $this->assertStringContainsString('#include &lt;iostream&gt;', $hal->body);
    }

    public function testPengampuMenyetujuiDanPertemuanMemakaiVersiItu(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengampuX']);
        $csrf = $this->csrf($http);

        $r = $http->post('/soal/' . $d['soalX'] . '/setujui', ['csrf' => $csrf, 'versi_id' => (string) $d['versiX']]);
        $this->assertSame(303, $r->status);
        $this->assertStringEndsWith('?pesan=disetujui', (string) $r->header('Location'));
        $v = $this->row("SELECT status, reviewed_by FROM problem_versions WHERE id = {$d['versiX']}");
        $this->assertSame(['APPROVED', $d['pengampuX']['id']], [$v['status'], $v['reviewed_by']]);
        $mp = $this->row('SELECT meeting_id, problem_id, problem_version_id FROM meeting_problems');
        $this->assertSame([(string) $d['pertemuanX1'], (string) $d['soalX'], (string) $d['versiX']], array_map('strval', array_values($mp)));
        $this->assertSame(1, $this->hitung("SELECT COUNT(*) FROM audit_log WHERE action = 'soal.disetujui' AND actor_id = '{$d['pengampuX']['id']}'"));

        // Menyetujui lagi, atau menolak yang sudah disetujui: tidak ada yang berubah.
        $r = $http->post('/soal/' . $d['soalX'] . '/setujui', ['csrf' => $csrf, 'versi_id' => (string) $d['versiX']]);
        $this->assertStringEndsWith('?pesan=bukan-menunggu', (string) $r->header('Location'));
        $r = $http->post('/soal/' . $d['soalX'] . '/tolak', ['csrf' => $csrf, 'versi_id' => (string) $d['versiX'], 'catatan' => 'Terlambat']);
        $this->assertStringEndsWith('?pesan=bukan-menunggu', (string) $r->header('Location'));
        $this->assertSame('APPROVED', $this->row("SELECT status FROM problem_versions WHERE id = {$d['versiX']}")['status']);
        $this->assertSame(1, $this->hitung("SELECT COUNT(*) FROM audit_log WHERE action LIKE 'soal.di%'"));

        // Versi 2 ditolak: pertemuan tetap memakai versi 1. Versi 3 disetujui: pertemuan pindah ke versi 3.
        $v2 = $this->versiBaris($d['soalX'], 2);
        $r  = $http->post('/soal/' . $d['soalX'] . '/tolak', ['csrf' => $csrf, 'versi_id' => (string) $v2, 'catatan' => 'x']);
        $this->assertStringContainsString('pesan=catatan', (string) $r->header('Location'));
        $this->assertSame('PENDING_REVIEW', $this->row("SELECT status FROM problem_versions WHERE id = {$v2}")['status']);
        $http->post('/soal/' . $d['soalX'] . '/tolak', ['csrf' => $csrf, 'versi_id' => (string) $v2, 'catatan' => 'Batasan tidak jelas']);
        $baris = $this->row("SELECT status, review_note FROM problem_versions WHERE id = {$v2}");
        $this->assertSame(['REJECTED', 'Batasan tidak jelas'], [$baris['status'], $baris['review_note']]);
        $this->assertSame((string) $d['versiX'], (string) $this->row('SELECT problem_version_id FROM meeting_problems')['problem_version_id']);
        $v3 = $this->versiBaris($d['soalX'], 3);
        $http->post('/soal/' . $d['soalX'] . '/setujui', ['csrf' => $csrf, 'versi_id' => (string) $v3]);
        $this->assertSame((string) $v3, (string) $this->row('SELECT problem_version_id FROM meeting_problems')['problem_version_id']);
        $this->assertSame(1, $this->hitung('SELECT COUNT(*) FROM meeting_problems'));
        $this->assertStringContainsString('Batasan tidak jelas', $http->get('/soal/' . $d['soalX'] . '?versi=2')->body);
    }

    public function testVersiSoalLainTidakBisaDisetujuiLewatSoalSendiri(): void
    {
        $d = $this->dunia();
        // Soal milik mata kuliah Y; pengampu X mencoba menyetujuinya lewat soal miliknya sendiri.
        $y    = $this->soalBaris($d['pertemuanY1'], 'soal-y');
        $http = $this->sebagai($d['pengampuX']);
        $csrf = $this->csrf($http);
        $r    = $http->post('/soal/' . $d['soalX'] . '/setujui', ['csrf' => $csrf, 'versi_id' => (string) $y['versi']]);
        $this->assertStringEndsWith('?pesan=bukan-menunggu', (string) $r->header('Location'));
        $this->assertSame(404, $http->post('/soal/' . $y['soal'] . '/setujui', ['csrf' => $csrf, 'versi_id' => (string) $y['versi']])->status);
        $this->assertSame('PENDING_REVIEW', $this->row("SELECT status FROM problem_versions WHERE id = {$y['versi']}")['status']);
        $this->assertSame(0, $this->hitung('SELECT COUNT(*) FROM meeting_problems'));
    }

    public function testPengajarDanPengampuLainTidakMelihatSoal(): void
    {
        $d = $this->dunia();
        $r = $this->sebagai($d['pengajarA'])->get('/soal/' . $d['soalX']);
        $this->assertSame(403, $r->status);
        $this->assertStringNotContainsString('Jumlah Dua Bilangan', $r->body);
        $this->assertStringNotContainsString('iostream', $r->body);
        $luar = $this->sebagai($d['pengampuY'])->get('/soal/' . $d['soalX']);
        $this->assertSame(404, $luar->status);
        $this->assertStringNotContainsString('Jumlah Dua Bilangan', $luar->body);
        $this->assertSame(404, $this->sebagai($d['pengampuY'])->get('/soal/999999')->status);
    }

    public function testTandaTerjemahanMesinDihapusOlehPengampu(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengampuX']);
        $r    = $http->post('/soal/' . $d['soalX'] . '/terjemahan', ['csrf' => $this->csrf($http), 'versi_id' => (string) $d['versiX'], 'bahasa' => 'en']);
        $this->assertSame(303, $r->status);
        $t = json_decode($this->row("SELECT translations FROM problem_versions WHERE id = {$d['versiX']}")['translations'], true);
        $this->assertFalse($t['en']['machine']);
        $this->assertSame('Sum of Two Numbers', $t['en']['title']);
        $this->assertSame(1, $this->hitung("SELECT COUNT(*) FROM audit_log WHERE action = 'soal.terjemahanDiperiksa'"));
        $this->assertStringNotContainsString('Terjemahan mesin</span>', $http->get('/soal/' . $d['soalX'])->body);
        $this->assertSame(1, $this->hitung("SELECT COUNT(*) FROM problem_versions WHERE problem_id = {$d['soalX']}"), 'tidak ada versi baru');
    }

    /**
     * @return array<string, string>
     */
    private function isianSunting(string $pernyataan, string $solusi): array
    {
        return [
            'difficulty' => 'mudah_sedang', 'comparison_mode' => 'trimmed', 'time_limit_ms' => '2000', 'samples_count' => '2', 'hidden_count' => '10',
            'params' => '{"n": 1000}', 't_id_title' => 'Jumlah Dua Bilangan', 't_id_statement' => $pernyataan,
            't_id_input_format' => 'Dua bilangan.', 't_id_output_format' => 'Jumlahnya.', 't_id_constraints' => '',
            'generator' => self::GEN, 'reference_solution' => $solusi,
        ];
    }

    public function testPengampuMenyuntingSoalMenjadiVersiBaruYangDivalidasi(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengampuX']);
        $csrf = $this->csrf($http);
        $http->post('/soal/' . $d['soalX'] . '/setujui', ['csrf' => $csrf, 'versi_id' => (string) $d['versiX']]);

        $form = $http->get('/soal/' . $d['soalX'] . '/ubah');
        $this->assertSame(200, $form->status);
        $this->assertStringContainsString('Diberikan **dua** bilangan sampai {{n}}.', $form->body);

        $r = $http->post('/soal/' . $d['soalX'] . '/ubah', ['csrf' => $csrf] + $this->isianSunting('Jumlahkan dua bilangan sampai {{n}}.', self::REF_JUMLAH));
        $this->assertSame(303, $r->status, $r->body);
        $jalur = (string) parse_url((string) $r->header('Location'), PHP_URL_PATH);
        $lap   = $this->tungguImpor($http, $jalur);
        $this->assertStringContainsString('Selesai: 1 diterima, 0 ditolak', $lap->body);

        $v2 = $this->row("SELECT * FROM problem_versions WHERE problem_id = {$d['soalX']} AND version = 2");
        $this->assertSame(['PENDING_REVIEW', 'MUDAH_SEDANG', $d['pengampuX']['id']], [$v2['status'], $v2['difficulty'], $v2['created_by']]);
        $this->assertStringContainsString('Jumlahkan dua bilangan', $v2['translations']);
        // Mahasiswa tetap memakai versi 1 sampai versi 2 disetujui.
        $this->assertSame((string) $d['versiX'], (string) $this->row('SELECT problem_version_id FROM meeting_problems')['problem_version_id']);
        $this->assertSame(1, $this->hitung("SELECT COUNT(*) FROM audit_log WHERE action = 'soal.disunting' AND actor_id = '{$d['pengampuX']['id']}'"));

        // Laporan suntingan: pengampu X dan superadmin melihatnya; pengampu Y dan pengajar A tidak.
        $this->assertSame(200, $this->sebagai($d['superadmin'])->get($jalur)->status);
        $this->assertSame(404, $this->sebagai($d['pengampuY'])->get($jalur)->status);
        $this->assertSame(404, $this->sebagai($d['pengajarA'])->get($jalur)->status);

        // Solusi yang memakai konsep terlarang ditolak dengan alasan; tidak ada versi 3.
        $r   = $http->post('/soal/' . $d['soalX'] . '/ubah', ['csrf' => $csrf] + $this->isianSunting('Cetak yang terbesar.', self::REF_IF));
        $lap = $this->tungguImpor($http, (string) parse_url((string) $r->header('Location'), PHP_URL_PATH));
        $this->assertStringContainsString('Selesai: 0 diterima, 1 ditolak', $lap->body);
        $this->assertStringContainsString('<span class="letak">solusi referensi, baris 6, kolom 5:</span> Solusi referensi memakai konsep PERCABANGAN', $lap->body);
        $this->assertSame(2, $this->hitung("SELECT COUNT(*) FROM problem_versions WHERE problem_id = {$d['soalX']}"));
    }

    public function testSuntinganBerbentukSalahDitolakSebelumSampaiKeJudge(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengampuX']);
        $awal = $this->hitung('SELECT COUNT(*) FROM judge_jobs');
        $isi  = ['hidden_count' => '3', 't_id_statement' => 'Batasnya {{maks}}.'] + $this->isianSunting('x', self::REF_JUMLAH);
        $r    = $http->post('/soal/' . $d['soalX'] . '/ubah', ['csrf' => $this->csrf($http)] + $isi);
        $this->assertSame(422, $r->status);
        $this->assertStringContainsString('hidden_count harus bilangan bulat 10 sampai 50', $r->body);
        $this->assertStringContainsString('memakai parameter «maks», tetapi «maks» tidak ada di params.', $r->body);
        // Isian tidak hilang.
        $this->assertStringContainsString('Batasnya {{maks}}.', $r->body);
        $this->assertSame($awal, $this->hitung('SELECT COUNT(*) FROM judge_jobs'));
        $this->assertSame(1, $this->hitung("SELECT COUNT(*) FROM problem_versions WHERE problem_id = {$d['soalX']}"));
    }
}
