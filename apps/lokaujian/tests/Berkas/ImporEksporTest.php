<?php

declare(strict_types=1);

namespace Tests\Berkas;

use Lokandita\Auth\Testing\Http;
use Tests\Support\UjianCase;

/**
 * Berkas ujian JSON (SPEC-LOKAUJIAN.md §6.3, KONTRAK.md §20).
 *
 * Gerbang tahap U2: satu soal dari tiap tipe dan satu kelompok bergambar diekspor, diimpor lagi
 * dan diekspor sekali lagi — berkasnya sama byte demi byte, dan bank tidak bertambah.
 */
final class ImporEksporTest extends UjianCase
{
    private function ekspor(Http $http, int $matkul, int $ujian): string
    {
        $r = $this->kirim($http, "/mata-kuliah/{$matkul}/ujian/{$ujian}/ekspor");
        $this->assertSame(200, $r->status, substr(strip_tags($r->body), 0, 300));

        return $r->body;
    }

    private function impor(Http $http, int $matkul, string $json, string $nama = 'ujian.json'): Http
    {
        return $this->kirimBerkas($http, "/mata-kuliah/{$matkul}/ujian/impor", [], ['berkas' => [$nama, $json]]);
    }

    /**
     * @return array<string, string>
     */
    private function hitung(): array
    {
        $h = [];
        foreach (['questions', 'question_groups', 'question_options', 'images', 'exams', 'exam_items'] as $t) {
            $h[$t] = (string) $this->db->query("SELECT COUNT(*) AS n FROM {$t}")->fetch_assoc()['n'];
        }

        return $h;
    }

    /**
     * @return array<string, mixed>
     */
    private function auditTerakhir(string $aksi): array
    {
        $b = $this->db->query("SELECT * FROM audit_log WHERE action = '{$aksi}' ORDER BY id DESC LIMIT 1")->fetch_assoc();
        $this->assertNotNull($b, 'tidak ada baris audit ' . $aksi);
        $b['detail'] = json_decode((string) $b['detail'], true);

        return $b;
    }

    public function testEksporImporEksporMenghasilkanBerkasYangSama(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengampuX']);
        $m    = $d['matkulX'];

        $bs = $this->buatSoal($http, $m, 'benar_salah', ['text' => 'Air mendidih pada 100 °C di permukaan laut.', 'answer' => 'benar']);
        $pg = $this->buatSoal($http, $m, 'pilihan_ganda', [
            'text' => "Manakah simbol **keputusan**?\n\nPilih satu.", 'points' => '2', 'alt' => 'Empat simbol diagram alir',
            'opsi_teks[0]' => '', 'opsi_teks[1]' => 'Persegi panjang', 'opsi_teks[2]' => 'Tidak ada', 'benar' => '0',
            'opsi_alt[0]' => 'Belah ketupat', 'opsi_alt[1]' => 'Persegi panjang',
        ], [
            'gambar' => ['s.png', self::gambar('png', 400, 120, 'simbol')],
            'opsi_gambar_0' => ['a.png', self::gambar('png', 100, 100, 'A')], 'opsi_gambar_1' => ['b.jpg', self::gambar('jpeg', 100, 60, 'B')],
        ]);
        $is = $this->buatSoal($http, $m, 'isian', ['text' => 'ما عاصمة إندونيسيا؟', 'points' => '0,5']);
        $ur = $this->buatSoal($http, $m, 'uraian', ['text' => "Jelaskan \"rekursi\" & beri contoh.\n\n```c\nint f(int n) { return n < 2 ? 1 : n * f(n - 1); }\n```", 'points' => '10']);
        $k  = $this->buatKelompok($http, $m, 'Bacaan bersama tentang <b>sawah</b>.', ['gambar' => ['denah.png', self::gambar('png', 300, 200, 'denah')]], 'Denah sawah');
        $this->buatSoal($http, $m, 'pilihan_ganda', ['text' => 'Soal kelompok satu'], [], $k);
        $this->buatSoal($http, $m, 'isian', ['text' => 'Soal kelompok dua'], [], $k);
        $ujian = $this->buatDraf($http, $m, 'Kuis 1 — Semua tipe', ["s{$bs}", "s{$pg}", "s{$is}", "s{$ur}", "k{$k}"], 'kuis');

        // Halaman sebelum unduhan menyatakan bahwa berkas memuat kunci.
        $r = $http->get("/mata-kuliah/{$m}/ujian/{$ujian}/ekspor");
        $this->assertSame(200, $r->status);
        $this->assertStringContainsString('Berkas ini memuat semua kunci jawaban.', $r->body);

        $satu = $this->ekspor($http, $m, $ujian);
        $this->assertSame('application/json; charset=utf-8', strtolower((string) $http->header('Content-Type')));
        $this->assertSame('attachment; filename="lokaujian-UJX101-' . $ujian . '.json"', $http->header('Content-Disposition'));
        $this->assertStringContainsString('no-store', (string) $http->header('Cache-Control'));
        $audit = $this->auditTerakhir('ujian.diekspor');
        $this->assertSame([$d['pengampuX']['id'], (string) $ujian, (string) $m, 6], [$audit['actor_id'], $audit['target_id'], $audit['course_id'], $audit['detail']['soal']]);

        // Bentuk berkas (KONTRAK.md §20): urutan kunci tetap, indentasi dua spasi, tanpa id, kelas atau jadwal.
        $data = json_decode($satu, true, 16, JSON_THROW_ON_ERROR);
        $this->assertSame(['format', 'course', 'title', 'kind', 'instructions', 'settings', 'items', 'images'], array_keys($data));
        $this->assertSame(['lokaujian-ujian/1', 'UJX101', 'Kuis 1 — Semua tipe', 'kuis'], [$data['format'], $data['course'], $data['title'], $data['kind']]);
        $this->assertSame(['mode' => 'dikendalikan_dosen', 'duration_minutes' => null, 'shuffle_questions' => false, 'shuffle_options' => false], $data['settings']);
        $this->assertSame(['benar_salah', 'pilihan_ganda', 'isian', 'uraian', 'kelompok'], array_column($data['items'], 'type'));
        $this->assertSame(['type' => 'benar_salah', 'text' => 'Air mendidih pada 100 °C di permukaan laut.', 'points' => 1, 'answer' => true], $data['items'][0]);
        $this->assertSame(['type', 'text', 'points', 'options', 'image'], array_keys($data['items'][1]));
        $this->assertSame([['text' => '', 'correct' => true, 'image' => 'g2'], ['text' => 'Persegi panjang', 'image' => 'g3'], ['text' => 'Tidak ada']], $data['items'][1]['options']);
        $this->assertSame(0.5, $data['items'][2]['points']);
        $this->assertSame(['type', 'text', 'questions', 'image'], array_keys($data['items'][4]));
        $this->assertCount(2, $data['items'][4]['questions']);
        $this->assertSame(['g1', 'g2', 'g3', 'g4'], array_keys($data['images']));
        $this->assertSame(['png', 'Empat simbol diagram alir'], [$data['images']['g1']['type'], $data['images']['g1']['alt']]);
        $this->assertSame('jpeg', $data['images']['g3']['type']);
        foreach ($data['images'] as $g) {
            $this->assertSame($g['sha256'], hash('sha256', (string) base64_decode($g['data'], true)));
        }
        $this->assertStringStartsWith("{\n  \"format\": \"lokaujian-ujian/1\",\n  \"course\": \"UJX101\",\n", $satu);
        $this->assertStringEndsWith("}\n", $satu);
        $this->assertStringNotContainsString('"id"', $satu);
        $this->assertStringNotContainsString($d['pengampuX']['id'], $satu);

        // Impor berkas itu: ujian baru berstatus draf, tidak ada soal atau gambar baru di bank.
        $sebelum = $this->hitung();
        $r       = $this->impor($http, $m, $satu, 'kuis-1.json');
        $this->assertContains($r->status, [302, 303], substr(strip_tags($r->body), 0, 800));
        $this->assertMatchesRegularExpression('#/ujian/(\d+)\?pesan=diimpor$#', (string) $r->header('Location'));
        $baru    = (int) $this->db->query('SELECT MAX(id) AS id FROM exams')->fetch_assoc()['id'];
        $sesudah = $this->hitung();
        $this->assertNotSame($ujian, $baru);
        // Hanya ujian dan butirnya yang bertambah; bank (soal, kelompok, opsi, gambar) tidak berubah.
        $this->assertSame(array_merge($sebelum, ['exams' => (string) ($sebelum['exams'] + 1), 'exam_items' => (string) ($sebelum['exam_items'] + 5)]), $sesudah);
        $u = $this->db->query("SELECT status, created_by, title FROM exams WHERE id = {$baru}")->fetch_assoc();
        $this->assertSame(['draf', $d['pengampuX']['id'], 'Kuis 1 — Semua tipe'], [$u['status'], $u['created_by'], $u['title']]);
        $audit = $this->auditTerakhir('ujian.diimpor');
        $this->assertSame((string) $baru, $audit['target_id']);
        $this->assertSame(['kuis-1.json', 6, 0, 4, 0], [$audit['detail']['berkas'], $audit['detail']['soal'], $audit['detail']['soal_baru'], $audit['detail']['gambar'], $audit['detail']['gambar_baru']]);

        // Ekspor ujian hasil impor: sama byte demi byte.
        $dua = $this->ekspor($http, $m, $baru);
        $this->assertSame(hash('sha256', $satu), hash('sha256', $dua), 'ekspor → impor → ekspor mengubah berkas');
        $this->assertSame($satu, $dua);

        // Soal bank yang dipakai kedua ujian itu sama; bank menyebut keduanya.
        $this->assertSame(2, substr_count($http->get("/mata-kuliah/{$m}/bank")->body, 'Kuis 1 — Semua tipe</a>') / 5);

        // Satu soal diubah di berkas: hanya soal itu yang baru di bank; kelompok yang berubah isinya menjadi kelompok baru.
        $ubah                     = $data;
        $ubah['title']            = 'Kuis 1 — revisi';
        $ubah['items'][2]['text'] = 'Apa ibu kota Indonesia?';
        $ubah['settings']         = ['mode' => 'per_mahasiswa', 'duration_minutes' => 45, 'shuffle_questions' => true, 'shuffle_options' => true];
        $r = $this->impor($http, $m, (string) json_encode($ubah, JSON_UNESCAPED_UNICODE));
        $this->assertContains($r->status, [302, 303], substr(strip_tags($r->body), 0, 800));
        $audit = $this->auditTerakhir('ujian.diimpor');
        $this->assertSame([6, 1, 0], [$audit['detail']['soal'], $audit['detail']['soal_baru'], $audit['detail']['gambar_baru']]);
        $u = $this->db->query('SELECT mode, duration_minutes, shuffle_questions, shuffle_options FROM exams ORDER BY id DESC LIMIT 1')->fetch_assoc();
        $this->assertSame(['per_mahasiswa', '45', '1', '1'], array_values($u));

        // Soal yang diarsipkan dan isinya sama dipakai ulang, dan tetap diarsipkan.
        $this->kirim($http, "/mata-kuliah/{$m}/bank/soal/{$bs}/arsip", ['arsip' => '1']);
        $sebelum = $this->hitung();
        $this->assertContains($this->impor($http, $m, $satu)->status, [302, 303]);
        $this->assertSame($sebelum['questions'], $this->hitung()['questions']);
        $this->assertNotNull($this->db->query("SELECT archived_at FROM questions WHERE id = {$bs}")->fetch_assoc()['archived_at']);

        // Pengampu mata kuliah lain tidak dapat mengimpor berkas mata kuliah X — tidak ke mata kuliahnya, tidak ke X.
        $y = $this->sebagai($d['pengampuY']);
        $sebelum = $this->sidikBank(false);
        $r = $this->impor($y, $d['matkulY'], $satu);
        $this->assertSame(422, $r->status);
        $this->assertStringContainsString('tidak ada, atau Anda bukan pengampunya', $r->body);
        $this->assertSame(404, $this->impor($y, $m, $satu)->status);
        $this->assertSame(404, $this->kirim($y, "/mata-kuliah/{$m}/ujian/{$ujian}/ekspor")->status);
        $this->assertSame(404, $this->kirim($y, "/mata-kuliah/{$d['matkulY']}/ujian/{$ujian}/ekspor")->status);
        $this->assertSame($sebelum, $this->sidikBank(false));
        // Superadmin mengimpor ke mata kuliah Y berkas yang menyebut X: ditolak, dengan alasannya.
        $r = $this->impor($this->sebagai($d['superadmin']), $d['matkulY'], $satu);
        $this->assertSame(422, $r->status);
        $this->assertStringContainsString('bukan mata kuliah halaman ini', $r->body);
    }

    /**
     * Berkas contoh di repositori (apps/lokaujian/contoh/ujian-contoh.json) memuat semua tipe soal,
     * kelompok, gambar soal, gambar kelompok dan opsi bergambar. Ke bank yang kosong ia diterima
     * seluruhnya, dan ekspornya sama dengan berkas itu sendiri.
     */
    public function testBerkasContohDiterimaDanDiimporKeBankKosong(): void
    {
        $d      = $this->dunia();
        $http   = $this->sebagai($d['pengampuX']);
        $m      = $d['matkulX'];
        $contoh = (string) file_get_contents(ROOTPATH . 'contoh/ujian-contoh.json');
        $this->assertStringContainsString('"course": "DDP101"', $contoh);
        $berkas = str_replace('"course": "DDP101"', '"course": "UJX101"', $contoh);
        $data   = json_decode($berkas, true, 16, JSON_THROW_ON_ERROR);

        // Berkas contoh memang memuat semuanya.
        $tipe = [];
        $opsiBergambar = false;
        foreach ($data['items'] as $b) {
            foreach ($b['type'] === 'kelompok' ? $b['questions'] : [$b] as $s) {
                $tipe[$s['type']] = true;
                foreach ($s['options'] ?? [] as $o) {
                    $opsiBergambar = $opsiBergambar || isset($o['image']);
                }
            }
        }
        ksort($tipe);
        $this->assertSame(['benar_salah', 'isian', 'pilihan_ganda', 'uraian'], array_keys($tipe));
        $this->assertTrue($opsiBergambar, 'berkas contoh tidak punya opsi bergambar');
        $this->assertContains('kelompok', array_column($data['items'], 'type'));
        $jumlahSoal = array_sum(array_map(static fn (array $b): int => $b['type'] === 'kelompok' ? count($b['questions']) : 1, $data['items']));

        $r = $this->impor($http, $m, $berkas, 'ujian-contoh.json');
        $this->assertContains($r->status, [302, 303], substr(strip_tags($r->body), 0, 1500));
        $audit = $this->auditTerakhir('ujian.diimpor');
        $this->assertSame([$jumlahSoal, $jumlahSoal, count($data['images']), count($data['images'])], [$audit['detail']['soal'], $audit['detail']['soal_baru'], $audit['detail']['gambar'], $audit['detail']['gambar_baru']]);
        $ujian = (int) $audit['target_id'];

        // Pratinjau seluruh ujian: semua soal bernomor, dan setiap gambarnya benar-benar keluar.
        $r = $http->get("/mata-kuliah/{$m}/ujian/{$ujian}");
        $this->assertSame(200, $r->status);
        $this->assertSame($jumlahSoal, substr_count($r->body, '<article class="soal"'));
        $this->assertStringContainsString('data-jumlah-soal>' . $jumlahSoal . '<', $r->body);
        $this->assertStringNotContainsString('data-kunci', $r->body, 'pratinjau ujian memuat kunci');
        preg_match_all('#src="(/gambar/[0-9a-f]{32})"#', $r->body, $src);
        $this->assertGreaterThanOrEqual(count($data['images']), count($src[1]));
        foreach (array_unique($src[1]) as $alamat) {
            $this->assertSame(200, $http->get($alamat)->status, $alamat);
        }

        // Ekspornya adalah berkas itu sendiri: berkas contoh sudah berbentuk baku.
        $this->assertSame($berkas, $this->ekspor($http, $m, $ujian));

        // Mengimpor berkas yang sama sekali lagi tidak menambah apa pun ke bank.
        $sebelum = $this->hitung();
        $this->assertContains($this->impor($http, $m, $berkas)->status, [302, 303]);
        $this->assertSame([$sebelum['questions'], $sebelum['images'], $sebelum['question_groups']], [$this->hitung()['questions'], $this->hitung()['images'], $this->hitung()['question_groups']]);

        // Draf dihapus: soalnya tetap di bank.
        $r = $this->kirim($http, "/mata-kuliah/{$m}/ujian/{$ujian}/hapus");
        $this->assertStringEndsWith('/ujian?pesan=dihapus', (string) $r->header('Location'));
        $this->assertSame($sebelum['questions'], $this->hitung()['questions']);
        $this->assertSame('ujian.dihapus', $this->auditTerakhir('ujian.dihapus')['action']);
        $this->assertSame(404, $http->get("/mata-kuliah/{$m}/ujian/{$ujian}")->status);
    }

    /**
     * @return array<string, mixed>
     */
    private static function dasar(): array
    {
        $png = self::gambar('png', 60, 40, 'g');

        return [
            'format' => 'lokaujian-ujian/1', 'course' => 'UJX101', 'title' => 'Ujian uji', 'kind' => 'ujian', 'instructions' => '',
            'settings' => ['mode' => 'per_mahasiswa', 'duration_minutes' => 30, 'shuffle_questions' => false, 'shuffle_options' => false],
            'items' => [
                ['type' => 'benar_salah', 'text' => 'Satu', 'points' => 1, 'answer' => true],
                ['type' => 'pilihan_ganda', 'text' => 'Dua', 'points' => 2, 'image' => 'g1', 'options' => [['text' => 'a'], ['text' => 'b', 'correct' => true]]],
                ['type' => 'isian', 'text' => 'Tiga'],
                ['type' => 'kelompok', 'text' => 'Bacaan', 'questions' => [['type' => 'uraian', 'text' => 'Empat', 'points' => 5]]],
            ],
            'images' => ['g1' => ['type' => 'png', 'alt' => 'Gambar', 'sha256' => hash('sha256', $png), 'data' => base64_encode($png)]],
        ];
    }

    /**
     * Importir ketat: SATU kesalahan di mana pun menolak seluruh berkas, tidak ada yang tersimpan,
     * dan pesannya menyebut jalur JSON serta apa yang harus diubah.
     */
    public function testSatuKesalahanMenolakSeluruhBerkas(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengampuX']);
        $m    = $d['matkulX'];
        $jpeg = self::gambar('jpeg', 60, 40);
        $rusak = substr(self::gambar('png', 900, 700), 0, 400);
        $gambar = static fn (string $isi, array $ganti = []): array => $ganti + ['type' => 'png', 'alt' => 'Gambar', 'sha256' => hash('sha256', $isi), 'data' => base64_encode($isi)];

        // Berkas dasar sendiri diterima — kalau tidak, penolakan di bawah tidak membuktikan apa pun.
        $this->assertContains($this->impor($http, $m, (string) json_encode(self::dasar()))->status, [302, 303]);
        $this->kosongkanBank();

        // nama => [pengubah, jalur yang disebut, potongan pesan]
        $kasus = [
            'kunci asing di tingkat atas' => [static function (array &$b): void { $b['clases'] = []; }, 'clases', 'kunci ini tidak dikenal'],
            'format lain'                 => [static function (array &$b): void { $b['format'] = 'lokaujian-ujian/2'; }, 'format', 'harus persis'],
            'mata kuliah tidak ada'       => [static function (array &$b): void { $b['course'] = 'TIDAKADA'; }, 'course', 'tidak ada, atau Anda bukan pengampunya'],
            'mata kuliah beda huruf'      => [static function (array &$b): void { $b['course'] = 'ujx101'; }, 'course', 'tidak ada, atau Anda bukan pengampunya'],
            'mata kuliah orang lain'      => [static function (array &$b): void { $b['course'] = 'UJY201'; }, 'course', 'bukan pengampunya'],
            'judul kosong'                => [static function (array &$b): void { $b['title'] = ' '; }, 'title', 'tidak boleh kosong'],
            'judul bukan teks'            => [static function (array &$b): void { $b['title'] = 12; }, 'title', 'harus diisi dengan teks'],
            'jenis lain'                  => [static function (array &$b): void { $b['kind'] = 'tes'; }, 'kind', 'ujian'],
            'mode lain'                   => [static function (array &$b): void { $b['settings']['mode'] = 'bebas'; }, 'settings.mode', 'per_mahasiswa'],
            'per mahasiswa tanpa durasi'  => [static function (array &$b): void { $b['settings']['duration_minutes'] = null; }, 'settings.duration_minutes', 'wajib punya durasi'],
            'durasi 4 menit'              => [static function (array &$b): void { $b['settings']['duration_minutes'] = 4; }, 'settings.duration_minutes', 'bilangan bulat 5 sampai 600'],
            'acak bukan boolean'          => [static function (array &$b): void { $b['settings']['shuffle_options'] = 'ya'; }, 'settings.shuffle_options', 'true atau false'],
            'kunci asing di setelan'      => [static function (array &$b): void { $b['settings']['kelas'] = 'A'; }, 'settings.kelas', 'kunci ini tidak dikenal'],
            'tanpa soal'                  => [static function (array &$b): void { $b['items'] = []; $b['images'] = []; }, 'items', '1 sampai 200 soal'],
            'tipe tidak dikenal'          => [static function (array &$b): void { $b['items'][2]['type'] = 'esai'; }, 'items[2].type', 'tipe soal harus salah satu dari'],
            'soal tanpa teks'             => [static function (array &$b): void { unset($b['items'][2]['text']); }, 'items[2].text', 'harus diisi dengan teks'],
            'poin 0,3'                    => [static function (array &$b): void { $b['items'][0]['points'] = 0.3; }, 'items[0].points', 'kelipatan 0,25'],
            'poin sebagai teks'           => [static function (array &$b): void { $b['items'][0]['points'] = '2'; }, 'items[0].points', 'kelipatan 0,25'],
            'poin 0'                      => [static function (array &$b): void { $b['items'][0]['points'] = 0; }, 'items[0].points', 'kelipatan 0,25'],
            'kunci B/S sebagai teks'      => [static function (array &$b): void { $b['items'][0]['answer'] = 'true'; }, 'items[0].answer', 'true atau false'],
            'B/S tanpa kunci'             => [static function (array &$b): void { unset($b['items'][0]['answer']); }, 'items[0].answer', 'pilih kunci jawabannya'],
            'answer pada pilihan ganda'   => [static function (array &$b): void { $b['items'][1]['answer'] = true; }, 'items[1].answer', 'kunci ini tidak dikenal'],
            'options pada isian'          => [static function (array &$b): void { $b['items'][2]['options'] = [['text' => 'a'], ['text' => 'b', 'correct' => true]]; }, 'items[2].options', 'kunci ini tidak dikenal'],
            'satu opsi'                   => [static function (array &$b): void { $b['items'][1]['options'] = [['text' => 'b', 'correct' => true]]; }, 'items[1].options', 'harus ada 2 sampai 6 opsi; sekarang 1'],
            'tujuh opsi'                  => [static function (array &$b): void { $b['items'][1]['options'] = array_merge(array_fill(0, 6, ['text' => 'a']), [['text' => 'b', 'correct' => true]]); }, 'items[1].options', 'sekarang 7'],
            'dua opsi benar'              => [static function (array &$b): void { $b['items'][1]['options'][0]['correct'] = true; }, 'items[1].options', 'tepat satu opsi harus ditandai sebagai jawaban benar; sekarang 2'],
            'tanpa opsi benar'            => [static function (array &$b): void { unset($b['items'][1]['options'][1]['correct']); }, 'items[1].options', 'sekarang 0'],
            'opsi kosong tanpa gambar'    => [static function (array &$b): void { $b['items'][1]['options'][0]['text'] = ''; }, 'items[1].options[0].text', 'tidak boleh kosong'],
            'correct sebagai teks'        => [static function (array &$b): void { $b['items'][1]['options'][0]['correct'] = 'false'; }, 'items[1].options[0].correct', 'true atau false'],
            'kunci asing di opsi'         => [static function (array &$b): void { $b['items'][1]['options'][0]['benar'] = true; }, 'items[1].options[0].benar', 'kunci ini tidak dikenal'],
            'poin pada kelompok'          => [static function (array &$b): void { $b['items'][3]['points'] = 5; }, 'items[3].points', 'kunci ini tidak dikenal'],
            'kelompok di dalam kelompok'  => [static function (array &$b): void { $b['items'][3]['questions'][0] = ['type' => 'kelompok', 'text' => 'x', 'questions' => []]; }, 'items[3].questions[0].type', 'tipe soal harus salah satu dari'],
            'kelompok tanpa soal'         => [static function (array &$b): void { $b['items'][3]['questions'] = []; }, 'items[3].questions', '1 sampai 20 soal'],
            'kelompok 21 soal'            => [static function (array &$b): void { $b['items'][3]['questions'] = array_fill(0, 21, ['type' => 'isian', 'text' => 'x']); }, 'items[3].questions', '1 sampai 20 soal'],
            'kelompok tanpa teks & gambar' => [static function (array &$b): void { $b['items'][3]['text'] = ''; }, 'items[3].text', 'tidak boleh kosong'],
            'gambar yang tidak ada'       => [static function (array &$b): void { $b['items'][2]['image'] = 'g9'; }, 'items[2].image', 'tidak ada di bagian'],
            'gambar tidak dipakai'        => [static function (array &$b): void { unset($b['items'][1]['image']); }, 'images.g1', 'tidak dipakai soal mana pun'],
            'nama gambar'                 => [static function (array &$b): void { $b['images']['G 1'] = $b['images']['g1']; unset($b['images']['g1']); $b['items'][1]['image'] = 'G 1'; }, 'images.G 1', 'huruf kecil'],
            'jenis gif'                   => [static function (array &$b): void { $b['images']['g1']['type'] = 'gif'; }, 'images.g1.type', 'png'],
            'sha256 tidak cocok'          => [static function (array &$b): void { $b['images']['g1']['sha256'] = str_repeat('0', 64); }, 'images.g1.sha256', 'tidak sama dengan SHA-256'],
            'data bukan Base64'           => [static function (array &$b): void { $b['images']['g1']['data'] = '%%% bukan base64 %%%'; }, 'images.g1.data', 'Base64'],
            'gambar tanpa teks alternatif' => [static function (array &$b): void { $b['images']['g1']['alt'] = ''; }, 'images.g1.alt', 'tidak boleh kosong'],
            'kunci asing di gambar'       => [static function (array &$b): void { $b['images']['g1']['nama'] = 'x.png'; }, 'images.g1.nama', 'kunci ini tidak dikenal'],
            'ditulis png, isinya jpeg'    => [static function (array &$b) use ($gambar, $jpeg): void { $b['images']['g1'] = $gambar($jpeg); }, 'images.g1.type', 'isi gambar ini bukan png'],
            'gambar rusak'                => [static function (array &$b) use ($gambar, $rusak): void { $b['images']['g2'] = $gambar($rusak); $b['items'][0]['image'] = 'g2'; }, 'images.g2.data', 'tidak dapat dibaca'],
        ];
        $kosong = $this->sidikBank(false);
        foreach ($kasus as $nama => [$ubah, $jalur, $pesan]) {
            $berkas = self::dasar();
            $ubah($berkas);
            $r = $this->impor($http, $m, (string) json_encode($berkas, JSON_UNESCAPED_UNICODE), 'salah.json');
            $this->assertSame(422, $r->status, $nama);
            $this->assertSame(1, preg_match('#<div class="kartu" role="alert" data-galat-impor>.*?</div>#s', $r->body, $kotak), $nama);
            $this->assertStringContainsString('<code class="ltr">' . $jalur . '</code>', $kotak[0], $nama);
            $this->assertStringContainsString($pesan, html_entity_decode($kotak[0], ENT_QUOTES), $nama);
            $this->assertStringContainsString('salah.json', $kotak[0], $nama);
            $this->assertSame($kosong, $this->sidikBank(false), $nama . ': berkas ditolak tetapi ada yang tersimpan');
        }
        // Setiap penolakan tercatat di log audit, tanpa isi berkasnya.
        $this->assertSame((string) count($kasus), $this->db->query("SELECT COUNT(*) AS n FROM audit_log WHERE action = 'ujian.impor.ditolak'")->fetch_assoc()['n']);
        $audit = $this->auditTerakhir('ujian.impor.ditolak');
        $this->assertSame(['salah.json', 'gambar_tidak_terbaca', 'images.g2.data'], [$audit['detail']['berkas'], $audit['detail']['alasan'], $audit['detail']['di']]);
        $this->assertSame($d['pengampuX']['id'], $audit['actor_id']);

        // Semua kesalahan dilaporkan sekaligus, masing-masing dengan barisnya di berkas.
        $berkas = "{\n  \"format\": \"lokaujian-ujian/1\",\n  \"course\": \"UJX101\",\n  \"title\": \"Dua kesalahan\",\n  \"settings\": { \"mode\": \"dikendalikan_dosen\" },\n"
            . "  \"items\": [\n    { \"type\": \"isian\", \"text\": \"baik\" },\n    {\n      \"type\": \"benar_salah\",\n      \"text\": \"tanpa kunci\",\n      \"points\": 7.1\n    },\n"
            . "    { \"type\": \"pilihan_ganda\", \"text\": \"x\",\n      \"options\": [ { \"text\": \"a\", \"correct\": true }, { \"text\": \"b\", \"correct\": true } ] }\n  ]\n}\n";
        $r = $this->impor($http, $m, $berkas);
        $this->assertSame(422, $r->status);
        $teks = html_entity_decode((string) preg_replace('/\s+/', ' ', strip_tags($r->body)), ENT_QUOTES);
        $this->assertStringContainsString('items[1].points (baris 11): poin harus angka', $teks);
        $this->assertStringContainsString('items[1].answer (baris 8): pilih kunci jawabannya', $teks);
        $this->assertStringContainsString('items[2].options (baris 14): tepat satu opsi harus ditandai sebagai jawaban benar; sekarang 2', $teks);

        // Kunci yang ditulis dua kali: json_decode diam-diam memakai yang terakhir; importir menolaknya.
        $ganda = str_replace('"title":"Ujian uji",', '"title":"Ujian uji","title":"Judul kedua",', (string) json_encode(self::dasar()));
        $this->assertStringContainsString('Judul kedua', $ganda);
        $r = $this->impor($http, $m, $ganda);
        $this->assertSame(422, $r->status);
        $this->assertStringContainsString('ditulis dua kali', $r->body);

        // Bukan JSON, larik di tingkat atas, dan tanpa berkas.
        foreach (['{ "format": ', '[1, 2]', 'null', ''] as $isi) {
            $this->assertSame(422, $this->impor($http, $m, $isi)->status, 'isi: ' . $isi);
        }
        $this->assertSame(422, $this->kirim($http, "/mata-kuliah/{$m}/ujian/impor")->status);
        $this->assertSame($kosong, $this->sidikBank(false));
    }

    public function testImporDanEksporLewatPerintah(): void
    {
        $d      = $this->dunia();
        $m      = $d['matkulX'];
        $berkas = str_replace('"course": "DDP101"', '"course": "UJX101"', (string) file_get_contents(ROOTPATH . 'contoh/ujian-contoh.json'));
        $oleh   = (string) $d['pengampuX']['nomor_induk'];

        // Yang bukan superadmin atau pengampu mata kuliah itu ditolak; tidak ada yang tersimpan.
        foreach (['pengajarA', 'pengampuY', 'mhsA'] as $p) {
            [$kode, $keluar] = $this->spark(['ujian:impor', '--oleh', (string) $d[$p]['nomor_induk']], $berkas);
            $this->assertSame(1, $kode, $p . ': ' . $keluar);
        }
        [$kode, $keluar] = $this->spark(['ujian:impor'], $berkas);
        $this->assertSame(1, $kode);
        $this->assertStringContainsString('--oleh', $keluar);
        $this->assertSame(['0', '0'], [$this->hitung()['exams'], $this->hitung()['questions']]);

        [$kode, $keluar, $galat] = $this->spark(['ujian:impor', '--oleh', $oleh, '--nama', 'contoh.json'], $berkas);
        $this->assertSame(0, $kode, $keluar . $galat);
        $this->assertMatchesRegularExpression('/ujian:impor DITERIMA: ujian (\d+) \(draf\)/', $keluar);
        $audit = $this->auditTerakhir('ujian.diimpor');
        $this->assertSame([$d['pengampuX']['id'], 'cli', 'contoh.json', (string) $m], [$audit['actor_id'], $audit['detail']['lewat'], $audit['detail']['berkas'], $audit['course_id']]);
        $this->assertNull($audit['ip']);
        $ujian = (int) $audit['target_id'];

        // Ekspor lewat perintah: berkas yang sama, di keluaran standar saja.
        [$kode, $keluar, $galat] = $this->spark(['ujian:ekspor', (string) $ujian, '--oleh', $oleh]);
        $this->assertSame(0, $kode, $galat);
        $this->assertSame($berkas, $keluar);
        $this->assertSame('cli', $this->auditTerakhir('ujian.diekspor')['detail']['lewat']);
        // Superadmin boleh; pengampu mata kuliah lain dan ujian yang tidak ada dijawab sama.
        $this->assertSame(0, $this->spark(['ujian:ekspor', (string) $ujian, '--oleh', (string) $d['superadmin']['nomor_induk']])[0]);
        [$kodeLain, $keluarLain, $galatLain] = $this->spark(['ujian:ekspor', (string) $ujian, '--oleh', (string) $d['pengampuY']['nomor_induk']]);
        [$kodeTiada, , $galatTiada]          = $this->spark(['ujian:ekspor', '99999999', '--oleh', (string) $d['pengampuY']['nomor_induk']]);
        $this->assertSame([1, 1, ''], [$kodeLain, $kodeTiada, $keluarLain]);
        $this->assertSame(str_replace('99999999', (string) $ujian, $galatTiada), $galatLain);

        // Berkas yang salah: keluar 1, pesan dengan jalur dan baris, tidak ada yang tersimpan.
        $sebelum = $this->sidikBank(false);
        [$kode, $keluar] = $this->spark(['ujian:impor', '--oleh', $oleh], str_replace('"kind": "kuis"', '"kind": "latihan"', $berkas));
        $this->assertSame(1, $kode);
        $this->assertStringContainsString('ujian:impor DITOLAK', $keluar);
        $this->assertStringContainsString('kind (baris 5)', $keluar);
        // Lebih dari 24 MB ditolak sebelum dibaca sebagai JSON.
        [$kode, $keluar] = $this->spark(['ujian:impor', '--oleh', $oleh], $berkas . str_repeat(' ', 24 * 1024 * 1024));
        $this->assertSame(1, $kode);
        $this->assertStringContainsString('24 MB', $keluar);
        $this->assertSame($sebelum, $this->sidikBank(false));
    }
}
