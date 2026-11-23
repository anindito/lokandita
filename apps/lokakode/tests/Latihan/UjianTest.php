<?php

declare(strict_types=1);

namespace Tests\Latihan;

use App\Libraries\Penilaian;
use Lokandita\Auth\Testing\Http;
use Tests\Support\KodeCase;

/**
 * Pertemuan berjenis ujian (SPEC.md §6 "Exam meetings", §7; KONTRAK.md §6), terhadap kontainer
 * sungguhan: MySQL, Redis, LokaAuth, dan judge worker untuk kiriman yang dinilai.
 *
 * Yang dibuktikan di sini:
 *   - mahasiswa yang TERDAFTAR tidak bisa melihat soal ujian sebelum jendela kelasnya dibuka dan
 *     sesudah ditutup — lewat halaman, setiap titik akhir soal, daftar, pemberitahuan,
 *     pengumuman, klarifikasi, maupun ekspor datanya;
 *   - selama nilai belum dirilis tidak ada nilai, poin, verdict atau hasil tes yang sampai ke mahasiswa;
 *   - nilai ujian = poin yang didapat dari seluruh poin; terbit tanpa komposisi dan tanpa 2 × target;
 *   - kiriman terlambat ditolak dan tidak ada latihan sesudah tutup, apa pun pengaturan jendelanya;
 *   - "buka selama N menit" dan rilis nilai, dengan haknya.
 */
final class UjianTest extends KodeCase
{
    private const BENAR = "#include <iostream>\nint main(){long a,b;std::cin>>a>>b;std::cout<<a+b<<\"\\n\";}\n";

    // Yang tidak boleh muncul di jawaban mana pun untuk mahasiswa di luar jendela: judul, isi dan slug soal.
    private const JEJAK = ['Jumlah Dua Bilangan', 'Sum of Two Numbers', 'Diberikan', 'ujian-debit', 'ujian-galian', 'kartu-soal'];

    /**
     * Mata kuliah X pertemuan 1 sebagai UJIAN yang sudah terbit, dengan dua soal: MUDAH (5) dan SULIT (15).
     * Belum ada jendela untuk kelas mana pun.
     *
     * @return array<string, mixed>
     */
    private function duniaUjian(): array
    {
        $d = $this->dunia();
        $this->db->query("UPDATE meetings SET kind = 'ujian', title = 'UTS Praktik' WHERE id = {$d['pertemuanX1']}");
        $d['mudah'] = $this->soalSiap($d['pertemuanX1'], 'ujian-debit', 'MUDAH');
        $d['sulit'] = $this->soalSiap($d['pertemuanX1'], 'ujian-galian', 'SULIT');
        $this->terbit($d['pertemuanX1'], ['target_points' => 20]);
        $d['hal']     = '/latihan/' . $d['pertemuanX1'];
        $d['halSoal'] = '/latihan/soal/' . $d['mudah']['mp'];
        $d['api']     = '/api/soal/' . $d['mudah']['mp'];
        $d['kelola']  = '/kelas/' . $d['kelasXA'] . '/pertemuan/' . $d['pertemuanX1'];
        $this->segar();

        return $d;
    }

    /**
     * Setiap jalan seorang mahasiswa menuju soal itu: semuanya harus menjawab seperti soal yang tidak ada,
     * dan halaman-halaman daftar tidak boleh memuat jejak soal.
     *
     * @param array<string, mixed> $d
     */
    private function pastikanTersembunyi(Http $http, array $d, string $keadaan, ?int $kiriman = null): void
    {
        $csrf = $this->csrf($http);
        $this->segar();
        $kode = 'int main(){return 0;}';

        $this->assertSame(404, $http->get($d['halSoal'])->status, "{$keadaan}: halaman soal");
        $this->assertSame(404, $http->get('/latihan/soal/' . $d['sulit']['mp'])->status, "{$keadaan}: halaman soal kedua");
        $this->assertSame(404, $http->get($d['hal'] . '/papan')->status, "{$keadaan}: papan peringkat");
        foreach ([
            ['GET', '/keadaan', []], ['POST', '/draf', ['source' => $kode]], ['POST', '/jalankan', ['source' => $kode]],
            ['POST', '/jalankan', ['source' => $kode, 'kustom' => '1', 'stdin' => '1 2']], ['POST', '/kirim', ['source' => $kode]],
            ['GET', '/riwayat', []], ['GET', '/riwayat/' . ($kiriman ?? 1), []], ['GET', '/klarifikasi', []],
            ['POST', '/klarifikasi', ['pertanyaan' => 'Apa maksud soal ini?']],
        ] as [$metode, $akhir, $isi]) {
            $r = $metode === 'GET' ? $http->get($d['api'] . $akhir) : $http->post($d['api'] . $akhir, ['csrf' => $csrf] + $isi);
            $this->assertSame(404, $r->status, "{$keadaan}: {$metode} {$akhir}");
            $this->pastikanTanpaJejak($r->body, "{$keadaan}: {$metode} {$akhir}");
        }
        $r = $http->post('/api/integritas', ['csrf' => $csrf, 'mp' => (string) $d['mudah']['mp'], 'jalur' => 'paste']);
        $this->assertSame(404, $r->status, "{$keadaan}: api/integritas tidak membocorkan adanya soal");

        // Halaman pertemuan ada (nomor dan judul PERTEMUAN saja), tanpa kartu atau judul soal.
        $r = $http->get($d['hal']);
        $this->assertSame(200, $r->status, "{$keadaan}: halaman pertemuan");
        $this->assertStringContainsString('UTS Praktik', $r->body);
        $this->pastikanTanpaJejak($r->body, "{$keadaan}: halaman pertemuan");
        foreach (['/beranda', '/notifikasi', '/pengumuman'] as $alamat) {
            $r = $http->get($alamat);
            $this->assertSame(200, $r->status, "{$keadaan}: {$alamat}");
            $this->pastikanTanpaJejak($r->body, "{$keadaan}: {$alamat}");
        }
        // Ekspor data sendiri (KONTRAK.md §14): tidak ada judul, isi atau slug soal ujian.
        $this->segar();
        $r = $http->post('/data-saya/unduh', ['csrf' => $csrf]);
        $this->assertSame(200, $r->status, "{$keadaan}: ekspor");
        $this->pastikanTanpaJejak($r->body, "{$keadaan}: ekspor data sendiri");
        $this->segar();
    }

    /**
     * Melupakan pembatas laju Run/Submit dan pembatas ekspor (sekali per 60 detik, KONTRAK.md §14),
     * supaya tes tidak perlu menunggu.
     */
    private function segar(): void
    {
        $this->lupakanLaju();
        $r = \App\Libraries\Antrean::redis();
        foreach ($r->keys('ekspor:*') as $kunci) {
            $r->del($kunci);
        }
    }

    private function pastikanTanpaJejak(string $isi, string $apa): void
    {
        foreach (self::JEJAK as $jejak) {
            $this->assertStringNotContainsString($jejak, $isi, "{$apa} memuat \"{$jejak}\"");
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function kirimDanTunggu(Http $http, string $api, string $csrf, string $sumber = self::BENAR): array
    {
        $this->segar();
        $r = $http->post($api . '/kirim', ['csrf' => $csrf, 'source' => $sumber]);
        $this->assertSame(202, $r->status, $r->body);
        $id    = $r->json()['id'];
        $batas = microtime(true) + 120;
        while (microtime(true) < $batas) {
            $data = $http->get('/api/executions/' . $id)->json();
            // Selama berjalan pun hasil mentah judge tidak boleh ikut.
            $this->assertNull($data['result'], 'hasil mentah judge untuk kiriman ujian');
            if (in_array($data['status'], ['DONE', 'FAILED'], true)) {
                return $data;
            }
            usleep(300_000);
        }
        $this->fail('kiriman tidak selesai dinilai dalam 120 detik');
    }

    // ---- bukti utama: soal tersembunyi sebelum dibuka dan sesudah ditutup ---------------------------

    public function testMahasiswaTerdaftarTidakMelihatSoalUjianSebelumJendelaDibuka(): void
    {
        $d    = $this->duniaUjian();
        $http = $this->sebagai($d['mhsA']);

        // 1. Terbit, kelasnya belum punya jendela sama sekali.
        $this->pastikanTersembunyi($http, $d, 'tanpa jendela');

        // 2. Jendela ada tetapi belum dibuka dan tidak dijadwalkan.
        $j = $this->jendela($d['pertemuanX1'], $d['kelasXA'], ['override' => 'none']);
        $this->pastikanTersembunyi($http, $d, 'jendela belum dibuka');

        // 3. Instans sudah disiapkan lebih dulu (pra-pembangkitan): tetap tidak terbaca.
        $this->instans($d['mhsA'], $d['mudah']);
        $this->pastikanTersembunyi($http, $d, 'instans sudah disiapkan');

        // 4. Ditutup manual sebelum pernah dibuka.
        $this->db->query("UPDATE meeting_class_windows SET override = 'closed' WHERE id = {$j}");
        $this->pastikanTersembunyi($http, $d, 'ditutup manual');

        // 5. Belum diterbitkan sama sekali, jendela "terbuka": tetap tidak terbaca.
        $this->db->query("UPDATE meeting_class_windows SET override = 'open' WHERE id = {$j}");
        $this->db->query("UPDATE meetings SET published_at = NULL WHERE id = {$d['pertemuanX1']}");
        $this->pastikanTersembunyi($http, $d, 'belum terbit');

        $this->assertSame(0, $this->hitung('SELECT COUNT(*) FROM submissions'));
        $this->assertSame(0, $this->hitung('SELECT COUNT(*) FROM drafts'));
        $this->assertSame(0, $this->hitung('SELECT COUNT(*) FROM clarifications'));
        $this->assertSame(0, $this->hitung('SELECT COUNT(*) FROM integrity_events'));
    }

    public function testMahasiswaTerdaftarTidakMelihatSoalUjianSesudahJendelaDitutup(): void
    {
        $d    = $this->duniaUjian();
        $j    = $this->jendela($d['pertemuanX1'], $d['kelasXA']);
        $http = $this->sebagai($d['mhsA']);
        $csrf = $this->csrf($http);

        // Selama terbuka mahasiswa mengerjakan: soal terlihat, ada draf, kiriman, pertanyaan dan peristiwa tempel.
        $ins = $this->instans($d['mhsA'], $d['mudah']);
        $r   = $http->get($d['halSoal']);
        $this->assertSame(200, $r->status);
        $this->assertStringContainsString('Jumlah Dua Bilangan', $r->body);
        $kiriman = $this->kirimanDinilai($d['mhsA'], $d['mudah'], $ins, 4, 10, 'KODE_UJIAN_ANI');
        $this->assertSame(200, $http->post($d['api'] . '/draf', ['csrf' => $csrf, 'source' => 'DRAF_UJIAN_ANI'])->status);
        $this->assertSame(200, $http->post($d['api'] . '/klarifikasi', ['csrf' => $csrf, 'pertanyaan' => 'TANYA_UJIAN_ANI'])->status);
        $this->assertSame(200, $http->post('/api/integritas', ['csrf' => $csrf, 'mp' => (string) $d['mudah']['mp'], 'jalur' => 'paste'])->status);
        // Jawaban dosen yang diterbitkan ke kelas, dan pengumuman kelas, tidak menjadi jalan ke soal.
        $this->db->query("UPDATE clarifications SET answer = 'JAWABAN_DOSEN', answered_by = '{$d['pengajarA']['id']}', answered_at = UTC_TIMESTAMP(),
            visibility = 'class', published_at = UTC_TIMESTAMP()");

        // Ditutup manual oleh pengajar.
        $this->db->query("UPDATE meeting_class_windows SET override = 'closed' WHERE id = {$j}");
        $this->pastikanTersembunyi($http, $d, 'ditutup manual', $kiriman);

        // Waktu "buka selama N menit" habis.
        $this->db->query("UPDATE meeting_class_windows SET override = 'none', open_at = '2020-01-01 00:00:00', close_at = '2020-01-01 02:30:00' WHERE id = {$j}");
        $this->pastikanTersembunyi($http, $d, 'waktu habis', $kiriman);

        // Pengaturan yang melonggarkan pertemuan LATIHAN tidak berlaku untuk ujian: "masih bisa membaca",
        // kiriman terlambat, dan latihan sesudah tutup.
        $this->db->query("UPDATE meeting_class_windows SET post_close_visibility = 'review_only', late_policy = 'multiplier', late_factor = 0.5, late_until = NULL WHERE id = {$j}");
        $this->db->query("UPDATE meetings SET practice_after_close = 1 WHERE id = {$d['pertemuanX1']}");
        $this->pastikanTersembunyi($http, $d, 'tutup dengan pengaturan longgar', $kiriman);
        $this->assertSame(1, $this->hitung('SELECT COUNT(*) FROM submissions'), 'tidak ada kiriman terlambat atau latihan yang diterima');

        // Sesudah nilai dirilis pun soalnya tetap tersembunyi: hanya nilai total dan poin per nomor.
        $this->db->query("UPDATE meeting_class_windows SET scores_released_at = UTC_TIMESTAMP(), scores_released_by = '{$d['pengampuX']['id']}' WHERE id = {$j}");
        $this->pastikanTersembunyi($http, $d, 'tutup dan nilai dirilis', $kiriman);

        // Ekspor: kiriman dan drafnya sendiri ada (itu datanya), tetapi soalnya disebut tanpa slug.
        $this->segar();
        $data = json_decode($http->post('/data-saya/unduh', ['csrf' => $csrf])->body, true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('soal-' . $d['mudah']['mp'], $data['kiriman'][0]['soal']);
        $this->assertSame('KODE_UJIAN_ANI', $data['kiriman'][0]['source']);
        $this->assertSame('soal-' . $d['mudah']['mp'], $data['draf'][0]['soal']);
        $this->assertSame('soal-' . $d['mudah']['mp'], $data['klarifikasi'][0]['soal']);
        $this->assertSame('soal-' . $d['mudah']['mp'], $data['peristiwa_integritas'][0]['soal']);
    }

    public function testKelasLainTidakMelihatUjianYangSedangDibukaUntukKelasIni(): void
    {
        $d = $this->duniaUjian();
        $this->jendela($d['pertemuanX1'], $d['kelasXA']);
        $this->instans($d['mhsA'], $d['mudah']);

        // Mahasiswa A (kelas XA) sedang ujian.
        $this->assertSame(200, $this->sebagai($d['mhsA'])->get($d['halSoal'])->status);
        // Mahasiswa B terdaftar di mata kuliah yang sama, kelas XB: jendelanya belum dibuka.
        $this->pastikanTersembunyi($this->sebagai($d['mhsB']), $d, 'kelas lain, mata kuliah sama');
        // Mahasiswa mata kuliah lain: pertemuannya pun tidak ada baginya.
        $y = $this->sebagai($d['mhsY']);
        $this->assertSame(404, $y->get($d['hal'])->status);
        $this->assertSame(404, $y->get($d['halSoal'])->status);
        $this->assertSame(404, $y->get($d['api'] . '/riwayat')->status);
    }

    // ---- nilai tersembunyi sampai dirilis ----------------------------------------------------------

    public function testSelamaUjianMahasiswaHanyaTahuKirimannyaDiterima(): void
    {
        $d    = $this->duniaUjian();
        $j    = $this->jendela($d['pertemuanX1'], $d['kelasXA']);
        $this->instans($d['mhsA'], $d['mudah']);
        $http = $this->sebagai($d['mhsA']);
        $csrf = $this->csrf($http);

        // Kiriman yang benar, dinilai judge sungguhan: yang kembali hanya "diterima".
        $data = $this->kirimDanTunggu($http, $d['api'], $csrf);
        $k    = $data['submission'];
        $this->assertSame(['DINILAI', 'TERKIRIM', null, null, null, [], [], true], [$k['status'], $k['verdict'], $k['score'], $k['passed'], $k['total'], $k['tests'], $k['missing'], $k['rahasia']]);
        $this->assertArrayNotHasKey('meeting', $data);
        // Di basis data nilainya ada dan penuh: 5 dari 20 poin = 25.
        $this->assertSame(['score' => '25.00', 'total_points' => '5.00', 'counted_points' => '5.00', 'surplus' => '0.00', 'target_points' => '20'],
            $this->row("SELECT score, total_points, counted_points, surplus, target_points FROM meeting_scores WHERE user_id = '{$d['mhsA']['id']}'"));

        // Kiriman yang salah tampak sama; galat kompilasi diberitahukan bersama log kompilernya.
        $salah = $this->kirimDanTunggu($http, $d['api'], $csrf, "#include <iostream>\nint main(){std::cout<<0<<\"\\n\";}\n")['submission'];
        $this->assertSame(['TERKIRIM', null, []], [$salah['verdict'], $salah['score'], $salah['tests']]);
        $ce = $this->kirimDanTunggu($http, $d['api'], $csrf, "int main(){ ini bukan C++ }\n")['submission'];
        $this->assertSame(['CE', null, null], [$ce['verdict'], $ce['score'], $ce['passed']]);
        $this->assertNotSame('', $ce['compile_log']);

        // Riwayat dan satu kiriman: tanpa nilai, tanpa jumlah tes, tanpa verdict yang sebenarnya.
        $riwayat = $http->get($d['api'] . '/riwayat')->json()['submissions'];
        $this->assertCount(3, $riwayat);
        $this->assertSame(['CE', 'TERKIRIM', 'TERKIRIM'], array_column($riwayat, 'verdict'));
        $this->assertSame([null, null, null], array_column($riwayat, 'score'));
        $this->assertSame([null, null, null], array_column($riwayat, 'passed'));
        $satu = $http->get($d['api'] . '/riwayat/' . $k['id'])->json();
        $this->assertSame(['TERKIRIM', null, [], self::BENAR], [$satu['verdict'], $satu['score'], $satu['tests'], $satu['source']]);

        // Halaman: tidak ada ringkasan nilai, tidak ada poin yang didapat, tidak ada tautan papan; papan 404.
        foreach ([$d['hal'], $d['halSoal'], '/beranda'] as $alamat) {
            $r = $http->get($alamat);
            $this->assertSame(200, $r->status);
            $this->assertStringNotContainsString('data-ringkasan', $r->body, $alamat);
            $this->assertStringNotContainsString('Didapat', $r->body, $alamat);
            $this->assertStringNotContainsString('dari 20 poin', $r->body, $alamat);
            $this->assertStringNotContainsString('/papan', $r->body, $alamat);
            $this->assertDoesNotMatchRegularExpression('/>\s*25\s*</', $r->body, $alamat . ' memuat nilai 25');
        }
        $r = $http->get($d['hal']);
        $this->assertStringContainsString('Nilai dan hasil penilaian tidak ditampilkan sampai dosen merilisnya', $r->body);
        $this->assertStringContainsString('Sudah dikirim', $r->body);
        $this->assertSame(404, $http->get($d['hal'] . '/papan')->status);

        // Ekspor data sendiri: tidak ada baris nilai; kirimannya ada tanpa verdict dan nilai.
        $this->segar();
        $eks = json_decode($http->post('/data-saya/unduh', ['csrf' => $csrf])->body, true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame([[], []], [$eks['nilai_pertemuan'], $eks['nilai_soal']]);
        $this->assertCount(3, $eks['kiriman']);
        foreach ($eks['kiriman'] as $baris) {
            $this->assertSame([null, null, null, null], [$baris['verdict'], $baris['score'], $baris['passed'], $baris['total']]);
        }

        // Dosen tetap melihat semuanya: papan kelas memuat nilainya.
        $r = $this->sebagai($d['pengajarA'])->get($d['kelola'] . '/papan');
        $this->assertSame(200, $r->status);
        $this->assertStringContainsString('Mahasiswa Ani', $r->body);

        // Rilis: pengajar kelas 403, pengampu boleh; tercatat di audit.
        $pengajar = $this->sebagai($d['pengajarA']);
        $this->assertSame(403, $pengajar->post($d['kelola'] . '/rilis', ['csrf' => $this->csrf($pengajar), 'rilis' => '1'])->status);
        $this->assertNull($this->row("SELECT scores_released_at FROM meeting_class_windows WHERE id = {$j}")['scores_released_at']);
        $pengampu = $this->sebagai($d['pengampuX']);
        $r        = $pengampu->post($d['kelola'] . '/rilis', ['csrf' => $this->csrf($pengampu), 'rilis' => '1']);
        $this->assertSame(303, $r->status, $r->body);
        $this->assertNotNull($this->row("SELECT scores_released_at FROM meeting_class_windows WHERE id = {$j}")['scores_released_at']);
        $this->assertSame(1, $this->hitung("SELECT COUNT(*) FROM audit_log WHERE action = 'ujian.nilaiDirilis' AND class_id = {$d['kelasXA']}"));

        // Sesudah dirilis dan ditutup: nilai total dan poin per NOMOR soal; soal tetap tersembunyi.
        $this->db->query("UPDATE meeting_class_windows SET override = 'closed' WHERE id = {$j}");
        $r = $http->get($d['hal']);
        $this->assertStringContainsString('Nilai ujian Anda: 25 dari 100 (5 dari 20 poin).', $r->body);
        $this->assertStringContainsString('5 dari 5 poin', $r->body);
        $this->assertStringContainsString('tidak dikirim', $r->body);
        $this->pastikanTanpaJejak($r->body, 'halaman nilai ujian');
        $this->assertSame(404, $http->get($d['halSoal'])->status);
        $this->assertSame(404, $http->get($d['hal'] . '/papan')->status, 'ujian tidak pernah punya papan untuk mahasiswa');
        $this->segar();
        $eks = json_decode($http->post('/data-saya/unduh', ['csrf' => $csrf])->body, true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('25.00', $eks['nilai_pertemuan'][0]['nilai']);

        // Rilis ditarik: nilainya tersembunyi lagi.
        $this->assertSame(303, $pengampu->post($d['kelola'] . '/rilis', ['csrf' => $this->csrf($pengampu), 'rilis' => '0'])->status);
        $r = $http->get($d['hal']);
        $this->assertStringContainsString('Nilai ujian belum dirilis oleh dosen.', $r->body);
        $this->assertStringNotContainsString('dari 20 poin', $r->body);
    }

    // ---- penilaian dan penerbitan ------------------------------------------------------------------

    public function testNilaiUjianDariSeluruhPoinTanpaKuotaMudahSekaliDanTanpaSurplus(): void
    {
        $enam = array_fill(0, 6, ['tingkat' => 'MUDAH_SEKALI', 'nilai' => 3.0]);
        // Latihan: hanya 4 yang dihitung. Ujian: semuanya, dan tidak ada surplus.
        $this->assertSame(['total' => 18.0, 'terhitung' => 12.0, 'nilai' => 66.67, 'surplus' => 6.0, 'mudahSekali' => 4], Penilaian::nilaiPertemuan($enam, 18));
        $this->assertSame(['total' => 18.0, 'terhitung' => 18.0, 'nilai' => 100.0, 'surplus' => 0.0, 'mudahSekali' => 0], Penilaian::nilaiPertemuan($enam, 18, true));

        // Sampai ke basis data: lima soal Mudah sekali dan satu Sulit, target 30; empat + sebagian.
        $d = $this->dunia();
        $this->db->query("UPDATE meetings SET kind = 'ujian' WHERE id = {$d['pertemuanX1']}");
        $ms = [];
        foreach (range(1, 5) as $i) {
            $ms[] = $this->soalSiap($d['pertemuanX1'], 'ujian-ms-' . $i, 'MUDAH_SEKALI');
        }
        $sulit = $this->soalSiap($d['pertemuanX1'], 'ujian-sulit', 'SULIT');
        $this->terbit($d['pertemuanX1'], ['target_points' => 30]);
        $this->jendela($d['pertemuanX1'], $d['kelasXA']);
        foreach ($ms as $s) {
            $this->kirimanDinilai($d['mhsA'], $s, $this->instans($d['mhsA'], $s), 10);
        }
        $this->kirimanDinilai($d['mhsA'], $sulit, $this->instans($d['mhsA'], $sulit), 4);
        // 5 × 3 + 15 × 0,4 = 21 dari 30 = 70.
        $this->assertSame(['score' => '70.00', 'total_points' => '21.00', 'counted_points' => '21.00', 'surplus' => '0.00', 'very_easy_counted' => '0'],
            $this->row("SELECT score, total_points, counted_points, surplus, very_easy_counted FROM meeting_scores WHERE user_id = '{$d['mhsA']['id']}'"));
    }

    public function testUjianTerbitTanpaKomposisiDenganTargetSamaDenganJumlahPoin(): void
    {
        $d = $this->dunia();
        $this->soalSiap($d['pertemuanX1'], 'ujian-debit', 'MUDAH');      // 5
        $this->soalSiap($d['pertemuanX1'], 'ujian-galian', 'SULIT');     // 15
        $http  = $this->sebagai($d['pengampuX']);
        $csrf  = $this->csrf($http);
        $pokok = ['csrf' => $csrf, 'number' => '1', 'title' => 'UTS Praktik', 'language' => 'cpp', 'enabled' => '1', 'konsep' => ['IO_DASAR', 'VARIABEL_TIPE', 'ARITMETIKA']];
        $alamat = '/pertemuan/' . $d['pertemuanX1'];

        // Sebagai latihan: 20 poin terhadap target 60 dan komposisi 8/8/6/6/4 — ditolak.
        $this->assertSame(422, $http->post($alamat . '/terbitkan', ['csrf' => $csrf])->status);

        // Jenis diubah menjadi ujian oleh pengampu (pengajar tidak bisa: 403).
        $pengajar = $this->sebagai($d['pengajarA']);
        $this->assertSame(403, $pengajar->post($alamat, ['csrf' => $this->csrf($pengajar), 'kind' => 'ujian'] + $pokok)->status);
        $this->assertSame(422, $http->post($alamat, ['kind' => 'kuis'] + $pokok)->status);
        $this->assertSame(303, $http->post($alamat, ['kind' => 'ujian'] + $pokok)->status);
        $this->assertSame(['kind' => 'ujian', 'target_points' => '20', 'practice_after_close' => '0'],
            $this->row("SELECT kind, target_points, practice_after_close FROM meetings WHERE id = {$d['pertemuanX1']}"));
        $r = $http->get($alamat);
        $this->assertStringContainsString('nilai = poin yang didapat dari seluruh 20 poin yang tersedia', $r->body);

        // Terbit: tanpa komposisi minimum, tanpa 2 × target.
        $this->assertSame(303, $http->post($alamat . '/terbitkan', ['csrf' => $csrf])->status);
        $this->assertNotNull($this->row("SELECT published_at FROM meetings WHERE id = {$d['pertemuanX1']}")['published_at']);

        // Pengaturan latihan tidak bisa mengubah target ujian atau menyalakan latihan sesudah tutup.
        $r = $http->post($alamat . '/latihan', ['csrf' => $csrf, 'target_points' => '5', 'min_mudah_sekali' => '0', 'min_mudah' => '0', 'min_mudah_sedang' => '0',
            'min_sedang' => '0', 'min_sulit' => '0', 'starter_template' => '', 'compiler_flags' => '', 'autocomplete' => '1', 'practice_after_close' => '1']);
        $this->assertSame(303, $r->status, $r->body);
        $this->assertSame(['target_points' => '20', 'practice_after_close' => '0'], $this->row("SELECT target_points, practice_after_close FROM meetings WHERE id = {$d['pertemuanX1']}"));

        // Sesudah terbit jenisnya terkunci.
        $r = $http->post($alamat, ['kind' => 'latihan'] + $pokok);
        $this->assertSame(422, $r->status);
        $this->assertStringContainsString('Jenis tidak bisa diubah', $r->body);
        $this->assertSame('ujian', $this->row("SELECT kind FROM meetings WHERE id = {$d['pertemuanX1']}")['kind']);

        // Ujian tanpa soal yang disetujui tidak bisa terbit.
        $this->db->query("UPDATE meetings SET kind = 'ujian' WHERE id = {$d['pertemuanX2']}");
        $r = $http->post('/pertemuan/' . $d['pertemuanX2'] . '/terbitkan', ['csrf' => $csrf]);
        $this->assertSame(422, $r->status);
        $this->assertStringContainsString('Belum ada soal yang disetujui.', $r->body);
    }

    // ---- jendela ujian -----------------------------------------------------------------------------

    public function testBukaSelamaMenutupSendiriDanJadwalDitolak(): void
    {
        $d    = $this->duniaUjian();
        $this->instans($d['mhsA'], $d['mudah']);
        $guru = $this->sebagai($d['pengajarA']);
        $csrf = $this->csrf($guru);

        // Form jadwal dan kebijakan terlambat tidak berlaku untuk ujian.
        $r = $guru->post($d['kelola'], ['csrf' => $csrf, 'open_at' => '2026-12-01T08:00', 'close_at' => '2026-12-01T10:30', 'post_close_visibility' => 'review_only',
            'late_policy' => 'multiplier', 'late_factor' => '0.5']);
        $this->assertSame(409, $r->status);
        $this->assertSame(0, $this->hitung('SELECT COUNT(*) FROM meeting_class_windows'));

        // Lama yang salah ditolak.
        foreach (['', '5', '601', 'abc'] as $menit) {
            $this->assertSame(422, $guru->post($d['kelola'] . '/buka-selama', ['csrf' => $csrf, 'menit' => $menit])->status, "menit '{$menit}'");
        }
        $this->assertSame(0, $this->hitung('SELECT COUNT(*) FROM meeting_class_windows'));

        // Pengajar kelas membuka selama 150 menit.
        $r = $guru->post($d['kelola'] . '/buka-selama', ['csrf' => $csrf, 'menit' => '150']);
        $this->assertSame(303, $r->status, $r->body);
        $j = $this->row('SELECT id, override, late_policy, post_close_visibility, TIMESTAMPDIFF(SECOND, open_at, close_at) AS lama,
            ABS(TIMESTAMPDIFF(SECOND, open_at, UTC_TIMESTAMP())) AS selisih FROM meeting_class_windows');
        $this->assertSame(['none', 'reject', 'hidden', '9000'], [$j['override'], $j['late_policy'], $j['post_close_visibility'], $j['lama']]);
        $this->assertLessThan(10, (int) $j['selisih']);
        $this->assertSame(1, $this->hitung("SELECT COUNT(*) FROM audit_log WHERE action = 'jendela.dibukaSelama' AND class_id = {$d['kelasXA']}"));
        $r = $guru->get($d['kelola']);
        $this->assertStringContainsString('Ini pertemuan ujian', $r->body);
        $this->assertStringContainsString('Nilai belum dirilis', $r->body);

        // Mahasiswa: terbuka, dengan hitung mundur ke waktu tutup.
        $http = $this->sebagai($d['mhsA']);
        $mcsrf = $this->csrf($http);
        $r = $http->get($d['hal']);
        $this->assertStringContainsString('data-hitung-mundur=', $r->body);
        $this->assertStringContainsString('kartu-soal', $r->body);
        $this->assertSame(200, $http->get($d['halSoal'])->status);

        // Waktunya habis (tanpa ada yang menekan Tutup): kiriman ditolak dan soal tersembunyi.
        $this->db->query("UPDATE meeting_class_windows SET open_at = open_at - INTERVAL 3 HOUR, close_at = close_at - INTERVAL 3 HOUR WHERE id = {$j['id']}");
        $this->segar();
        $this->assertSame(404, $http->post($d['api'] . '/kirim', ['csrf' => $mcsrf, 'source' => self::BENAR])->status);
        $this->assertSame(404, $http->get($d['halSoal'])->status);
        $this->assertSame(0, $this->hitung('SELECT COUNT(*) FROM submissions'));

        // "Buka selama" hanya untuk ujian; pertemuan latihan menjawab 409 dan tidak berubah.
        $lain = '/kelas/' . $d['kelasXA'] . '/pertemuan/' . $d['pertemuanX2'];
        $this->assertSame(409, $guru->post($lain . '/buka-selama', ['csrf' => $csrf, 'menit' => '150'])->status);
        $pengampu = $this->sebagai($d['pengampuX']);
        $this->assertSame(409, $pengampu->post($lain . '/rilis', ['csrf' => $this->csrf($pengampu), 'rilis' => '1'])->status);
        $this->assertSame(1, $this->hitung('SELECT COUNT(*) FROM meeting_class_windows'));
    }
}
