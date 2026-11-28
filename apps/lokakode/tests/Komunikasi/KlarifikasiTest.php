<?php

declare(strict_types=1);

namespace Tests\Komunikasi;

use Lokandita\Auth\Testing\Http;
use Tests\Support\KodeCase;

/**
 * Klarifikasi per soal (SPEC.md §14, KONTRAK.md §17) lewat HTTP sungguhan: siapa yang boleh
 * bertanya, siapa yang melihat pertanyaan dan jawabannya, dan siapa yang boleh menjawab.
 */
final class KlarifikasiTest extends KodeCase
{
    /**
     * Pertemuan X1 terbit dan terbuka untuk kelas XA dan XB; Cici sekelas dengan Ani di XA.
     *
     * @return array<string, mixed>
     */
    private function duniaTerbuka(): array
    {
        $d         = $this->dunia();
        $d['soal'] = $this->soalSiap($d['pertemuanX1'], 'jumlah-dua-bilangan');
        $d['lain'] = $this->soalSiap($d['pertemuanX1'], 'soal-lain');
        $this->terbit($d['pertemuanX1']);
        $this->jendela($d['pertemuanX1'], $d['kelasXA']);
        $this->jendela($d['pertemuanX1'], $d['kelasXB']);
        $d['cici'] = $this->user('mahasiswa', 'Mahasiswa Cici');
        $this->daftar($d['cici'], $d['kelasXA']);
        $d['api'] = '/api/soal/' . $d['soal']['mp'] . '/klarifikasi';

        return $d;
    }

    /**
     * @param array<string, mixed> $mhs
     */
    private function tanya(array $mhs, string $api, string $teks): Http
    {
        $this->lupakanLaju();
        $http = $this->sebagai($mhs);

        return $http->post($api, ['csrf' => $this->csrf($http), 'pertanyaan' => $teks]);
    }

    /**
     * @param array<string, mixed> $siapa
     *
     * @return list<array<string, mixed>>
     */
    private function daftar_(array $siapa, string $api): array
    {
        $r = $this->sebagai($siapa)->get($api);
        $this->assertSame(200, $r->status);
        $this->assertStringContainsString('no-store', (string) $r->header('Cache-Control'));

        return $r->json()['daftar'];
    }

    /**
     * @param array<string, mixed> $dosen
     */
    private function jawab(array $dosen, int $kelas, int $id, string $jawaban, string $tampil): Http
    {
        $http = $this->sebagai($dosen);

        return $http->post("/kelas/{$kelas}/klarifikasi/{$id}/jawab", ['csrf' => $this->csrf($http), 'jawaban' => $jawaban, 'tampil' => $tampil]);
    }

    public function testMahasiswaBertanyaDanHanyaIaYangMelihatnyaSampaiDiterbitkan(): void
    {
        $d = $this->duniaTerbuka();
        $r = $this->tanya($d['mhsA'], $d['api'], "  Apakah n bisa nol?\r\nDi contoh tidak ada.  ");
        $this->assertSame(200, $r->status, $r->body);
        $id = (int) $r->json()['id'];
        $q  = $this->row("SELECT * FROM clarifications WHERE id = {$id}");
        $this->assertSame([$d['mhsA']['id'], (string) $d['soal']['mp'], (string) $d['kelasXA'], "Apakah n bisa nol?\nDi contoh tidak ada.", null, 'private', null],
            [$q['user_id'], $q['meeting_problem_id'], $q['class_id'], $q['question'], $q['answer'], $q['visibility'], $q['published_at']]);

        // Penanya melihat pertanyaannya, belum berjawab.
        $milik = $this->daftar_($d['mhsA'], $d['api']);
        $this->assertCount(1, $milik);
        $this->assertSame([$id, "Apakah n bisa nol?\nDi contoh tidak ada.", true, 'Mahasiswa Ani', null, null, false],
            [$milik[0]['id'], $milik[0]['pertanyaan'], $milik[0]['milikSaya'], $milik[0]['penanya'], $milik[0]['jawaban'], $milik[0]['dijawab'], $milik[0]['kelas']]);
        $this->assertMatchesRegularExpression('/ WIB$/', $milik[0]['waktu']);
        // Teman sekelas, mahasiswa kelas lain: tidak melihat apa-apa. Soal lain: kosong juga bagi penanya.
        $this->assertSame([], $this->daftar_($d['cici'], $d['api']));
        $this->assertSame([], $this->daftar_($d['mhsB'], $d['api']));
        $this->assertSame([], $this->daftar_($d['mhsA'], '/api/soal/' . $d['lain']['mp'] . '/klarifikasi'));

        // Jawaban PRIBADI: hanya penanya yang melihatnya.
        $r = $this->jawab($d['pengajarA'], $d['kelasXA'], $id, 'Tidak, n paling kecil 1.', 'pribadi');
        $this->assertContains($r->status, [302, 303]);
        $milik = $this->daftar_($d['mhsA'], $d['api']);
        $this->assertSame(['Tidak, n paling kecil 1.', false], [$milik[0]['jawaban'], $milik[0]['kelas']]);
        $this->assertNotNull($milik[0]['dijawab']);
        $this->assertSame([], $this->daftar_($d['cici'], $d['api']), 'jawaban pribadi tidak terlihat teman sekelas');
        $this->assertSame([], $this->daftar_($d['mhsB'], $d['api']));

        // Diterbitkan ke kelas: teman sekelas melihat pertanyaan, jawaban dan NAMA penanya; kelas lain tetap tidak.
        $this->jawab($d['pengajarA'], $d['kelasXA'], $id, 'Tidak, n paling kecil 1.', 'kelas');
        $teman = $this->daftar_($d['cici'], $d['api']);
        $this->assertCount(1, $teman);
        $this->assertSame([false, 'Mahasiswa Ani', "Apakah n bisa nol?\nDi contoh tidak ada.", 'Tidak, n paling kecil 1.', true],
            [$teman[0]['milikSaya'], $teman[0]['penanya'], $teman[0]['pertanyaan'], $teman[0]['jawaban'], $teman[0]['kelas']]);
        $this->assertSame([], $this->daftar_($d['mhsB'], $d['api']), 'kelas lain tidak melihat jawaban kelas XA');
        // Yang sampai ke peramban tidak memuat id pengguna atau nomor induk siapa pun.
        $mentah = $this->sebagai($d['cici'])->get($d['api'])->body;
        $this->assertStringNotContainsString($d['mhsA']['id'], $mentah);
        $this->assertStringNotContainsString($d['mhsA']['nomor_induk'], $mentah);
        $this->assertStringNotContainsString($d['pengajarA']['id'], $mentah);

        // Ditarik kembali menjadi pribadi: teman sekelas tidak melihatnya lagi.
        $this->jawab($d['pengajarA'], $d['kelasXA'], $id, 'Tidak, n paling kecil 1.', 'pribadi');
        $this->assertSame([], $this->daftar_($d['cici'], $d['api']));
        $this->assertCount(1, $this->daftar_($d['mhsA'], $d['api']));

        // Audit: dijawab sekali (teks tidak berubah sesudahnya), diterbitkan, ditarik — tanpa isi pertanyaan atau jawaban.
        $audit = $this->db->query("SELECT action, actor_id, class_id, detail FROM audit_log WHERE action LIKE 'klarifikasi.%' ORDER BY id")->fetch_all(MYSQLI_ASSOC);
        $this->assertSame(['klarifikasi.dijawab', 'klarifikasi.diterbitkan', 'klarifikasi.ditarik'], array_column($audit, 'action'));
        $this->assertSame([$d['pengajarA']['id'], (string) $d['kelasXA']], [$audit[0]['actor_id'], $audit[0]['class_id']]);
        foreach ($audit as $a) {
            $this->assertStringNotContainsString('paling kecil', (string) $a['detail']);
            $this->assertStringNotContainsString('bisa nol', (string) $a['detail']);
        }
        // Tidak ada pemberitahuan untuk klarifikasi (keputusan pemilik).
        $this->assertSame(0, $this->hitung("SELECT COUNT(*) FROM notifications WHERE kind NOT IN ('pertemuan_dibuka')"));
    }

    public function testYangDiterbitkanMengikutiKelasPenanyaBukanKelasPembaca(): void
    {
        $d  = $this->duniaTerbuka();
        $id = (int) $this->tanya($d['mhsA'], $d['api'], 'Format keluarannya bagaimana?')->json()['id'];
        $this->jawab($d['pengajarA'], $d['kelasXA'], $id, 'Satu bilangan per baris.', 'kelas');
        // Budi (kelas XB) bertanya juga; jawabannya diterbitkan ke kelas XB.
        $idB = (int) $this->tanya($d['mhsB'], $d['api'], 'Pertanyaan dari kelas XB')->json()['id'];
        $this->jawab($d['pengajarB'], $d['kelasXB'], $idB, 'Jawaban untuk XB.', 'kelas');

        $this->assertSame(['Format keluarannya bagaimana?'], array_column($this->daftar_($d['cici'], $d['api']), 'pertanyaan'));
        $this->assertSame(['Pertanyaan dari kelas XB'], array_column($this->daftar_($d['mhsB'], $d['api']), 'pertanyaan'));

        // Cici pindah ke kelas XB: ia kini melihat terbitan kelas XB, tidak lagi terbitan kelas XA.
        $this->roster->query("UPDATE enrollments SET class_id = {$d['kelasXB']} WHERE user_id = '{$d['cici']['id']}'");
        $this->assertSame(['Pertanyaan dari kelas XB'], array_column($this->daftar_($d['cici'], $d['api']), 'pertanyaan'));
        // Ani pindah ke XB: pertanyaannya sendiri tetap miliknya, dan tetap tercatat di kelas XA tempat ia bertanya.
        $this->roster->query("UPDATE enrollments SET class_id = {$d['kelasXB']} WHERE user_id = '{$d['mhsA']['id']}'");
        $ani = $this->daftar_($d['mhsA'], $d['api']);
        $this->assertEqualsCanonicalizing(['Format keluarannya bagaimana?', 'Pertanyaan dari kelas XB'], array_column($ani, 'pertanyaan'));
        $this->assertStringContainsString('Format keluarannya bagaimana?', $this->sebagai($d['pengajarA'])->get('/kelas/' . $d['kelasXA'] . '/klarifikasi')->body);
    }

    public function testPertanyaanDiperiksaDibatasiDanHanyaUntukSoalYangBolehDibuka(): void
    {
        $d    = $this->duniaTerbuka();
        $http = $this->sebagai($d['mhsA']);
        $csrf = $this->csrf($http);

        $this->assertSame(422, $http->post($d['api'], ['csrf' => $csrf, 'pertanyaan' => "  \n "])->status);
        $this->assertSame(422, $http->post($d['api'], ['csrf' => $csrf])->status);
        $this->assertSame(422, $http->post($d['api'], ['csrf' => $csrf, 'pertanyaan' => str_repeat('a', 2001)])->status);
        $this->assertSame(403, $http->post($d['api'], ['pertanyaan' => 'tanpa csrf'])->status);
        $this->assertSame(0, $this->hitung('SELECT COUNT(*) FROM clarifications'));

        // 2.000 karakter (bukan byte) diterima; yang kedua dalam satu menit ditolak dengan waktu tunggunya.
        $this->assertSame(200, $http->post($d['api'], ['csrf' => $csrf, 'pertanyaan' => str_repeat('é', 2000)])->status);
        $r = $http->post($d['api'], ['csrf' => $csrf, 'pertanyaan' => 'Satu lagi']);
        $this->assertSame([429, 'terlalu_cepat'], [$r->status, $r->json()['error']]);
        $this->assertGreaterThan(0, $r->json()['retry_after']);
        $this->assertSame(1, $this->hitung('SELECT COUNT(*) FROM clarifications'));

        // Soal mata kuliah lain, soal yang tidak ada: 404 untuk membaca dan bertanya. Staf: 403 (bukan rute mereka).
        $soalY = $this->soalSiap($d['pertemuanY1'], 'soal-y');
        $this->terbit($d['pertemuanY1']);
        $this->jendela($d['pertemuanY1'], $d['kelasYA']);
        $this->lupakanLaju();
        foreach (['/api/soal/' . $soalY['mp'] . '/klarifikasi', '/api/soal/999999/klarifikasi'] as $api) {
            $this->assertSame(404, $http->get($api)->status, $api);
            $this->assertSame(404, $http->post($api, ['csrf' => $csrf, 'pertanyaan' => 'Boleh?'])->status, $api);
        }
        $dosen = $this->sebagai($d['pengajarA']);
        $this->assertSame(403, $dosen->get($d['api'])->status);
        $this->assertSame(403, $dosen->post($d['api'], ['csrf' => $this->csrf($dosen), 'pertanyaan' => 'x'])->status);
        // Pertemuan belum dibuka untuk kelasnya (kelas XA ditutup): halaman soalnya pun 404, jadi tidak bisa bertanya.
        $this->db->query("UPDATE meeting_class_windows SET override = 'closed' WHERE class_id = {$d['kelasXA']}");
        $this->assertSame(404, $http->post($d['api'], ['csrf' => $csrf, 'pertanyaan' => 'Sesudah tutup'])->status);
        $this->assertSame(1, $this->hitung('SELECT COUNT(*) FROM clarifications'));
    }

    public function testHalamanDosenHanyaUntukYangMelihatKelasItu(): void
    {
        $d   = $this->duniaTerbuka();
        $id  = (int) $this->tanya($d['mhsA'], $d['api'], 'Pertanyaan <b>Ani</b> & kawan')->json()['id'];
        $idB = (int) $this->tanya($d['mhsB'], $d['api'], 'Pertanyaan rahasia kelas XB')->json()['id'];
        $alamat = '/kelas/' . $d['kelasXA'] . '/klarifikasi';

        foreach (['superadmin', 'pengampuX', 'pengajarA'] as $siapa) {
            $r = $this->sebagai($d[$siapa])->get($alamat);
            $this->assertSame(200, $r->status, $siapa);
            $this->assertStringContainsString('Pertanyaan &lt;b&gt;Ani&lt;/b&gt; &amp; kawan', $r->body, 'teks pertanyaan di-escape');
            $this->assertStringContainsString('Mahasiswa Ani', $r->body);
            $this->assertStringContainsString('Belum dijawab', $r->body);
            $this->assertStringNotContainsString('rahasia kelas XB', $r->body);
            $this->assertStringContainsString('no-store', (string) $r->header('Cache-Control'));
        }
        // Halaman kelas menunjukkan jumlah yang belum dijawab.
        $this->assertStringContainsString('data-klarifikasi-belum="1"', $this->sebagai($d['pengajarA'])->get('/kelas/' . $d['kelasXA'])->body);

        // IDOR: pengajar kelas lain dan pengampu mata kuliah lain 404; mahasiswa 403 — membaca maupun menjawab.
        foreach (['pengajarB' => 404, 'pengampuY' => 404, 'mhsA' => 403] as $siapa => $harap) {
            $http = $this->sebagai($d[$siapa]);
            $this->assertSame($harap, $http->get($alamat)->status, $siapa);
            $this->assertSame($harap, $http->post("{$alamat}/{$id}/jawab", ['csrf' => $this->csrf($http), 'jawaban' => 'Dari luar', 'tampil' => 'kelas'])->status, $siapa);
        }
        // Pengajar A tidak bisa menjawab pertanyaan kelas XB lewat alamat kelasnya sendiri, atau lewat alamat kelas XB.
        $a = $this->sebagai($d['pengajarA']);
        $this->assertSame(404, $a->post("{$alamat}/{$idB}/jawab", ['csrf' => $this->csrf($a), 'jawaban' => 'Menyusup', 'tampil' => 'kelas'])->status);
        $this->assertSame(404, $a->post('/kelas/' . $d['kelasXB'] . "/klarifikasi/{$idB}/jawab", ['csrf' => $this->csrf($a), 'jawaban' => 'Menyusup', 'tampil' => 'kelas'])->status);
        $this->assertSame(404, $a->post("{$alamat}/999999/jawab", ['csrf' => $this->csrf($a), 'jawaban' => 'x', 'tampil' => 'kelas'])->status);
        $this->assertSame(0, $this->hitung('SELECT COUNT(*) FROM clarifications WHERE answer IS NOT NULL'));
        $this->assertSame(0, $this->hitung("SELECT COUNT(*) FROM audit_log WHERE action LIKE 'klarifikasi.%'"));

        // Jawaban kosong, terlalu panjang atau pilihan tampil yang tidak dikenal: tidak disimpan, dan dikatakan.
        foreach ([['', 'kelas'], [str_repeat('a', 2001), 'pribadi'], ['Baik', 'semua']] as [$jawaban, $tampil]) {
            $r = $this->jawab($d['pengajarA'], $d['kelasXA'], $id, $jawaban, $tampil);
            $this->assertContains($r->status, [302, 303]);
            $this->assertStringContainsString('pesan=kosong', (string) $r->header('Location'));
        }
        $this->assertStringContainsString('Jawaban tidak tersimpan', $a->get($alamat . '?pesan=kosong')->body);
        $this->assertNull($this->row("SELECT answer FROM clarifications WHERE id = {$id}")['answer']);

        // Pengampu menjawab; pengajar mengubah jawabannya: penjawab terakhir tercatat, dan jumlah belum dijawab nol.
        $this->jawab($d['pengampuX'], $d['kelasXA'], $id, 'Jawaban pertama', 'pribadi');
        $this->jawab($d['pengajarA'], $d['kelasXA'], $id, 'Jawaban <i>kedua</i>', 'kelas');
        $q = $this->row("SELECT answer, answered_by, visibility FROM clarifications WHERE id = {$id}");
        $this->assertSame(['Jawaban <i>kedua</i>', $d['pengajarA']['id'], 'class'], array_values($q));
        $r = $a->get($alamat);
        $this->assertStringContainsString('Jawaban &lt;i&gt;kedua&lt;/i&gt;', $r->body);
        $this->assertStringContainsString('Dijawab oleh Pengajar Aminah', $r->body);
        $this->assertStringContainsString('data-klarifikasi-belum="0"', $a->get('/kelas/' . $d['kelasXA'])->body);
        $this->assertSame(['klarifikasi.dijawab', 'klarifikasi.dijawab', 'klarifikasi.diterbitkan'],
            array_column($this->db->query("SELECT action FROM audit_log WHERE action LIKE 'klarifikasi.%' ORDER BY id")->fetch_all(MYSQLI_ASSOC), 'action'));

        // Saringan pertemuan.
        $this->assertStringContainsString('kawan', $a->get($alamat . '?pertemuan=' . $d['pertemuanX1'])->body);
        $this->assertStringNotContainsString('kawan', $a->get($alamat . '?pertemuan=' . $d['pertemuanX2'])->body);
    }

    public function testTahunAjaranArsipHanyaBacaBagiDosen(): void
    {
        $d  = $this->duniaTerbuka();
        $id = (int) $this->tanya($d['mhsA'], $d['api'], 'Sebelum diarsipkan')->json()['id'];
        $this->roster->query("UPDATE academic_years SET status = 'archived' WHERE id = {$d['tahun']}");

        $r = $this->sebagai($d['pengajarA'])->get('/kelas/' . $d['kelasXA'] . '/klarifikasi');
        $this->assertSame(200, $r->status);
        $this->assertStringContainsString('Sebelum diarsipkan', $r->body);
        $this->assertStringNotContainsString('name="jawaban"', $r->body);
        $this->assertSame(403, $this->jawab($d['pengajarA'], $d['kelasXA'], $id, 'Terlambat', 'kelas')->status);
        $this->assertSame(403, $this->jawab($d['pengampuX'], $d['kelasXA'], $id, 'Terlambat', 'kelas')->status);
        $this->assertNull($this->row("SELECT answer FROM clarifications WHERE id = {$id}")['answer']);
        // Superadmin masih bisa.
        $this->assertContains($this->jawab($d['superadmin'], $d['kelasXA'], $id, 'Dari pengelola', 'pribadi')->status, [302, 303]);
        $this->assertSame('Dari pengelola', $this->row("SELECT answer FROM clarifications WHERE id = {$id}")['answer']);
    }
}
