<?php

declare(strict_types=1);

namespace Tests\Seb;

use Lokandita\Auth\Seb\Plist;
use Lokandita\Auth\Testing\Http;
use Tests\Support\UjianCase;

/**
 * Safe Exam Browser LokaUjian, PER UJIAN (SPEC-LOKAUJIAN.md §9.1; KONTRAK.md §23), lewat HTTP
 * sungguhan ke lokaujian-seb-tes — image yang sama dengan penegakan AKTIF.
 *
 * Header SEB dihitung tes ini sendiri dengan rumus KONTRAK.md §8. Itu membuktikan bahwa server
 * menerima header yang benar dan menolak yang salah; TIDAK membuktikan bahwa klien SEB sungguhan
 * mengirim header yang sama. Yang terakhir adalah gerbang pemilik.
 */
final class SebUjianTest extends UjianCase
{
    /**
     * Ujian terbit dikendalikan dosen di kelas XA, sudah dimulai 30 menit, dengan konfigurasi SEB
     * dan satu kunci terdaftar.
     *
     * @return array<string, mixed>
     */
    private function duniaSeb(): array
    {
        $d           = $this->dunia();
        $d['http']   = $this->sebagai($d['pengampuX']);
        $d['ujian']  = $this->ujianTerbit($d['http'], $d['matkulX'], 'Ujian ber-SEB');
        $d['kunci']  = $this->sebSiap($d['http'], $d['matkulX'], $d['ujian']);
        $d['sesi']   = $this->beri($d['http'], $d['matkulX'], $d['ujian'], $d['kelasXA']);
        $this->kirim($d['http'], "/sesi/{$d['sesi']}/mulai", ['menit' => '30']);
        $d['mhs'] = $this->sebagaiSeb($d['mhsA']);
        $d['q']   = $this->soalBeku($d['ujian']);

        return $d;
    }

    /** GET dengan header SEB yang sah untuk alamat itu. */
    private function getSeb(Http $h, string $jalur, string $kunci, string $bek = self::SEB_BEK): Http
    {
        return $h->get($jalur, self::sebHeader($jalur, $kunci, $bek));
    }

    private function csrfSeb(Http $h): string
    {
        // Beranda mahasiswa di luar filter SEB: token CSRF dibaca dari sana.
        return preg_match('/data-csrf="([^"]+)"/', $h->get('/beranda')->body, $m) === 1 ? $m[1] : '';
    }

    /**
     * @param array<string, mixed> $isian
     */
    private function postSeb(Http $h, string $jalur, array $isian, string $kunci, string $bek = self::SEB_BEK): Http
    {
        return $h->post($jalur, $isian + ['csrf' => $this->csrfSeb($h)], self::sebHeader($jalur, $kunci, $bek));
    }

    /**
     * @return array<string, mixed>
     */
    private static function bacaPlist(string $xml): array
    {
        $dok = new \DOMDocument();
        $dok->loadXML($xml, LIBXML_NONET);
        $baca = static function (\DOMElement $e) use (&$baca): mixed {
            $anak = [];
            foreach ($e->childNodes as $n) {
                if ($n instanceof \DOMElement) {
                    $anak[] = $n;
                }
            }
            switch ($e->tagName) {
                case 'dict':
                    $hasil = [];
                    for ($i = 0; $i < count($anak); $i += 2) {
                        $hasil[$anak[$i]->textContent] = $baca($anak[$i + 1]);
                    }

                    return $hasil;

                case 'array':   return array_map($baca, $anak);

                case 'true':    return true;

                case 'false':   return false;

                case 'integer': return (int) $e->textContent;

                case 'string':  return $e->textContent;

                case 'data':    return Plist::data((string) base64_decode($e->textContent, true));

                default: throw new \RuntimeException('elemen plist tak dikenal: ' . $e->tagName);
            }
        };
        foreach ($dok->documentElement->childNodes as $n) {
            if ($n instanceof \DOMElement) {
                return $baca($n);
            }
        }

        throw new \RuntimeException('plist tanpa dict');
    }

    /**
     * @return array<string, mixed>
     */
    private function bukaBerkas(Http $r, string $nama): array
    {
        $this->assertSame(200, $r->status);
        $this->assertStringContainsString($nama, (string) $r->header('Content-Disposition'));
        $this->assertStringContainsString('no-store', (string) $r->header('Cache-Control'));
        $dalam = gzdecode($r->body);
        $this->assertIsString($dalam, 'berkas .seb adalah arsip gzip');
        $this->assertSame('plnd', substr($dalam, 0, 4), 'awalan data tak terenkripsi');
        $xml = gzdecode(substr($dalam, 4));
        $this->assertIsString($xml);

        return self::bacaPlist($xml);
    }

    // ---- panel ------------------------------------------------------------------------------

    public function testPanelMembuatKonfigurasiPerUjianDanBerkasnyaMemuatKunciYangSama(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengampuX']);
        $x    = '/mata-kuliah/' . $d['matkulX'];
        $u1   = $this->ujianTerbit($http, $d['matkulX'], 'Ujian satu');
        $u2   = $this->buatDraf($http, $d['matkulX'], 'Ujian dua', ['s' . (int) $this->db->query("SELECT id FROM questions WHERE text LIKE 'URAIAN:%'")->fetch_assoc()['id']]);
        $this->kirim($http, "{$x}/ujian/{$u2}/terbit");

        // Kata sandi keluar di bawah 12 karakter ditolak; tidak ada yang tersimpan.
        $this->assertSame(422, $this->kirim($http, "{$x}/ujian/{$u1}/seb", ['sandi_keluar' => 'pendek12345'])->status);
        $this->assertSame('0', (string) $this->db->query('SELECT COUNT(*) AS n FROM seb_configs')->fetch_assoc()['n']);
        $this->assertStringContainsString('data-seb-konfig="0"', $http->get("{$x}/ujian/{$u1}/seb")->body);
        $this->assertStringContainsString('data-seb-ringkas="tanpa"', $http->get("{$x}/ujian/{$u1}")->body);
        // Kunci tanpa konfigurasi ditolak; unduhan belum ada.
        $this->assertSame(422, $this->kirim($http, "{$x}/ujian/{$u1}/seb/kunci", ['kunci' => self::SEB_BEK])->status);
        $this->assertSame(404, $http->get("{$x}/ujian/{$u1}/seb/unduh")->status);

        $k1 = $this->sebSiap($http, $d['matkulX'], $u1, null);
        $k2 = $this->sebSiap($http, $d['matkulX'], $u2, null);
        $this->assertNotSame($k1, $k2, 'dua ujian harus punya Config Key yang berbeda');
        $hal = $http->get("{$x}/ujian/{$u1}/seb")->body;
        $this->assertStringContainsString('data-seb-konfig="1"', $hal);
        $this->assertStringContainsString('data-jumlah-kunci="0"', $hal);
        $this->assertStringNotContainsString(self::SEB_SANDI, $hal);
        $this->assertStringContainsString('data-seb-ringkas="tanpa_kunci"', $http->get("{$x}/ujian/{$u1}")->body);

        // Config Key dihitung dari ISI BERKAS yang diunduh sama dengan yang disimpan server.
        foreach ([[$u1, $k1], [$u2, $k2]] as [$u, $k]) {
            $s = $this->bukaBerkas($http->get("{$x}/ujian/{$u}/seb/unduh"), "LokaUjian-UJX101-{$u}.seb");
            $this->assertSame($k, Plist::kunciKonfig($s));
            $this->assertSame("https://lokaujian.lokandita.com/ujian/{$u}/masuk", $s['startURL']);
            $this->assertSame('https://lokaujian.lokandita.com/seb/keluar', $s['quitURL']);
            $this->assertSame(hash('sha256', self::SEB_SANDI), $s['hashedQuitPassword']);
            $this->assertTrue($s['sendBrowserExamKey']);
            $ekspresi = array_column($s['URLFilterRules'], 'expression');
            $this->assertContains('https://.lokaujian.lokandita.com', $ekspresi);
            $this->assertContains('https://.auth.lokandita.com', $ekspresi);
        }
        // Tersimpan terenkripsi; kata sandi keluar tidak ada di mana pun.
        $baris = $this->db->query('SELECT config_enc FROM seb_configs LIMIT 1')->fetch_assoc();
        $this->assertStringNotContainsString('startURL', (string) $baris['config_enc']);
        $this->assertStringNotContainsString(self::SEB_SANDI, (string) $baris['config_enc']);
        $audit = $this->db->query("SELECT action, target_tipe, target_id, detail FROM audit_log WHERE action LIKE 'seb.%' ORDER BY id")->fetch_all(MYSQLI_ASSOC);
        $this->assertSame([['seb.dibuat', 'ujian', (string) $u1], ['seb.dibuat', 'ujian', (string) $u2]], array_map(static fn (array $a): array => [$a['action'], $a['target_tipe'], $a['target_id']], $audit));
        $this->assertStringNotContainsString(self::SEB_SANDI, json_encode($audit));

        // Kunci: bentuk diperiksa, paling banyak 20, daftar baru menggantikan yang lama.
        $kunci = "{$x}/ujian/{$u1}/seb/kunci";
        $this->assertSame(422, $this->kirim($http, $kunci, ['kunci' => 'bukan-kunci'])->status);
        $this->assertSame(422, $this->kirim($http, $kunci, ['kunci' => implode("\n", array_map(static fn (int $i): string => str_pad(dechex($i), 64, 'a', STR_PAD_LEFT), range(1, 21)))])->status);
        $this->assertSame('0', (string) $this->db->query('SELECT COUNT(*) AS n FROM seb_exam_keys')->fetch_assoc()['n']);
        $this->kirim($http, $kunci, ['kunci' => strtoupper(self::SEB_BEK) . "\n" . str_repeat('c1', 32) . "\n" . self::SEB_BEK]);
        $this->assertSame('2', (string) $this->db->query('SELECT COUNT(*) AS n FROM seb_exam_keys')->fetch_assoc()['n']);
        $this->assertStringContainsString('data-seb-ringkas="siap"', $http->get("{$x}/ujian/{$u1}")->body);
        $this->assertStringNotContainsString(self::SEB_BEK, json_encode($this->db->query("SELECT detail FROM audit_log WHERE action = 'seb.kunci'")->fetch_all(MYSQLI_ASSOC)));

        // Rotasi: versi 2, Config Key baru, kunci lama hilang; ujian lain tidak tersentuh.
        $this->assertStringEndsWith('?pesan=dirotasi', (string) $this->kirim($http, "{$x}/ujian/{$u1}/seb", ['sandi_keluar' => self::SEB_SANDI])->header('Location'));
        $baru = $this->db->query("SELECT version, config_key, (SELECT COUNT(*) FROM seb_exam_keys k WHERE k.seb_config_id = c.id) AS n FROM seb_configs c WHERE exam_id = {$u1} AND retired_at IS NULL")->fetch_assoc();
        $this->assertSame(['2', '0'], [(string) $baru['version'], (string) $baru['n']]);
        $this->assertNotSame($k1, $baru['config_key']);
        $this->assertSame($k2, $this->db->query("SELECT config_key FROM seb_configs WHERE exam_id = {$u2} AND retired_at IS NULL")->fetch_assoc()['config_key']);
    }

    public function testRotasiDitolakSelamaUjianDijalankan(): void
    {
        $d   = $this->duniaSeb();
        $seb = "/mata-kuliah/{$d['matkulX']}/ujian/{$d['ujian']}/seb";
        // Sesi sedang berlangsung: rotasi 409, konfigurasi dan kuncinya utuh.
        $sebelum = $this->sidikBank();
        $r       = $this->kirim($d['http'], $seb, ['sandi_keluar' => self::SEB_SANDI]);
        $this->assertSame(409, $r->status);
        $this->assertSame($sebelum, $this->sidikBank());
        $this->assertStringContainsString('data-seb-dijalankan', $d['http']->get($seb)->body);
        // Kunci tetap boleh ditambah selagi ujian berjalan (versi SEB yang belum terdaftar).
        $this->assertStringEndsWith('?pesan=kunci', (string) $this->kirim($d['http'], $seb . '/kunci', ['kunci' => self::SEB_BEK . "\n" . str_repeat('d2', 32)])->header('Location'));
        // Sesi selesai dan tidak ada pengerjaan berjalan: rotasi diterima.
        $this->db->query("UPDATE sessions SET end_at = UTC_TIMESTAMP() - INTERVAL 1 MINUTE WHERE id = {$d['sesi']}");
        $this->assertStringEndsWith('?pesan=dirotasi', (string) $this->kirim($d['http'], $seb, ['sandi_keluar' => self::SEB_SANDI])->header('Location'));
    }

    // ---- penegakan --------------------------------------------------------------------------

    /**
     * Dengan header yang sah seorang mahasiswa memulai, menyimpan dan mengumpulkan; setiap
     * penyimpangan ditolak dengan alasannya.
     */
    public function testDenganHeaderSahMahasiswaMengerjakanDanSetiapPenyimpanganDitolak(): void
    {
        $d    = $this->duniaSeb();
        $mhs  = $d['mhs'];
        $k    = $d['kunci'];
        $sesi = "/ujian-saya/{$d['sesi']}";
        $api  = "/api/sesi/{$d['sesi']}";

        // Di luar SEB: beranda (daftar, hanya dibaca) terbuka dan menawarkan berkasnya; ujiannya tidak.
        $beranda = $mhs->get('/beranda');
        $this->assertSame(200, $beranda->status);
        $this->assertStringContainsString("{$sesi}/seb", $beranda->body);
        $this->assertStringContainsString('data-seb-info', $beranda->body);
        $this->assertStringNotContainsString('MODE UJI', $beranda->body);
        $tolak = $mhs->get($sesi);
        $this->assertSame(403, $tolak->status);
        $this->assertStringContainsString('data-seb-wajib="tanpa_header"', $tolak->body);
        $this->assertStringNotContainsString('Kerjakan dengan jujur', $tolak->body);

        // Di dalam SEB dengan berkas ujian ini.
        $r = $this->getSeb($mhs, $sesi, $k);
        $this->assertSame(200, $r->status, substr(strip_tags($r->body), 0, 300));
        $this->assertStringContainsString('/js/seb.js', $r->body, 'halaman di dalam SEB memuat skrip denyut');
        $this->assertStringNotContainsString('MODE UJI', $r->body);
        $r = $this->postSeb($mhs, "{$sesi}/mulai", [], $k);
        $this->assertStringEndsWith("{$sesi}/kerjakan", (string) $r->header('Location'));
        $hal = $this->getSeb($mhs, "{$sesi}/kerjakan", $k);
        $this->assertSame(200, $hal->status);
        $this->assertSame(1, preg_match('/data-halaman="([0-9a-f]{32})"/', $hal->body, $m));
        $token = $m[1];
        $csrf  = $this->csrfSeb($mhs);
        $simpan = fn (array $header, int $n, string $teks): Http => $mhs->postJson("{$api}/jawaban", ['halaman' => $token, 'soal' => $d['q']['ISIAN'], 'nilai' => $teks, 'ragu' => false, 'n' => $n], ['X-CSRF-TOKEN' => $csrf] + $header);
        $this->assertSame(200, $simpan(self::sebHeader("{$api}/jawaban", $k), 1, 'di dalam SEB')->status);
        // Alamat dengan query di-hash bersama query-nya.
        $this->assertSame(200, $this->getSeb($mhs, "{$api}/keadaan?halaman={$token}", $k)->status);
        $denyut = $this->postSeb($mhs, '/api/seb/denyut', [], $k);
        $this->assertSame(200, $denyut->status);
        $this->assertSame(['ok' => true, 'sewa' => 30], $denyut->json());

        // Penyimpangan: tanpa header; header untuk alamat lain; Config Key salah; BEK tak terdaftar.
        $sebelum = $this->sidikBank(false);
        foreach ([
            'tanpa header'        => [[], 'seb_wajib'],
            'header alamat lain'  => [self::sebHeader("{$api}/keadaan", $k), 'seb_wajib'],
            'Config Key salah'    => [self::sebHeader("{$api}/jawaban", str_repeat('0', 64)), 'seb_wajib'],
            'BEK tidak terdaftar' => [self::sebHeader("{$api}/jawaban", $k, str_repeat('e3', 32)), 'seb_wajib'],
        ] as $apa => [$header, $galat]) {
            $r = $simpan($header, 9, 'dari luar SEB');
            $this->assertSame(403, $r->status, $apa);
            $this->assertSame($galat, $r->json()['error'], $apa);
        }
        $this->assertSame('di dalam SEB', $this->db->query("SELECT text FROM answers WHERE exam_question_id = {$d['q']['ISIAN']}")->fetch_assoc()['text']);
        $this->assertSame(403, $mhs->post("{$sesi}/kumpulkan", ['csrf' => $csrf])->status);
        $this->assertSame(403, $mhs->post('/api/seb/denyut', ['csrf' => $csrf])->status);
        // Tabel selain peristiwa integritas tidak berubah oleh permintaan yang ditolak.
        $tanpaPeristiwa = static fn (string $sidik): string => (string) preg_replace('/integrity_events=\S+ /', '', $sidik);
        $this->assertSame($tanpaPeristiwa($sebelum), $tanpaPeristiwa($this->sidikBank(false)));
        // Alasan pada halaman: Config Key cocok tetapi BEK tidak → kunci_peramban; sebaliknya → kunci_konfigurasi.
        $this->assertStringContainsString('data-seb-wajib="kunci_peramban"', $mhs->get($sesi, self::sebHeader($sesi, $k, str_repeat('e3', 32)))->body);
        $this->assertStringContainsString('data-seb-wajib="kunci_konfigurasi"', $mhs->get($sesi, self::sebHeader($sesi, str_repeat('0', 64)))->body);

        // Mengumpulkan dari dalam SEB; tanda terimanya pun di dalam SEB.
        $this->assertStringEndsWith($sesi, (string) $this->postSeb($mhs, "{$sesi}/kumpulkan", [], $k)->header('Location'));
        $this->assertStringContainsString('data-tanda-terima', $this->getSeb($mhs, $sesi, $k)->body);
        $this->assertSame('mahasiswa', $this->pengerjaan($d['sesi'], (string) $d['mhsA']['id'])['submit_how']);

        // Staf tidak pernah diperiksa; logout mahasiswa selalu jalan.
        $staf = new Http((string) getenv('APP_SEB_TES_URL'));
        $staf->cookies = $d['http']->cookies;
        $this->assertSame(200, $staf->get("/sesi/{$d['sesi']}")->status);
        $this->assertContains($mhs->post('/keluar', ['csrf' => $csrf])->status, [302, 303]);
    }

    /**
     * Konfigurasi per ujian: SEB yang dibuka dengan berkas ujian A tidak membuka ujian B, walau
     * keduanya milik kelas yang sama dan keduanya sah.
     */
    public function testBerkasUjianLainTidakMembukaUjianIni(): void
    {
        $d    = $this->duniaSeb();
        $http = $d['http'];
        $soal = (int) $this->db->query("SELECT id FROM questions WHERE text LIKE 'URAIAN:%'")->fetch_assoc()['id'];
        $uB   = $this->buatDraf($http, $d['matkulX'], 'Ujian B', ["s{$soal}"]);
        $this->kirim($http, "/mata-kuliah/{$d['matkulX']}/ujian/{$uB}/terbit");
        $kB    = $this->sebSiap($http, $d['matkulX'], $uB);
        $sesiB = $this->beri($http, $d['matkulX'], $uB, $d['kelasXA']);
        $this->kirim($http, "/sesi/{$sesiB}/mulai", ['menit' => '30']);
        $mhs = $d['mhs'];
        $a   = "/ujian-saya/{$d['sesi']}";
        $b   = "/ujian-saya/{$sesiB}";

        $this->assertSame(200, $this->getSeb($mhs, $a, $d['kunci'])->status);
        $this->assertSame(200, $this->getSeb($mhs, $b, $kB)->status);
        $silang = $this->getSeb($mhs, $b, $d['kunci']);
        $this->assertSame(403, $silang->status, 'berkas ujian A membuka ujian B');
        $this->assertStringContainsString('data-seb-wajib="kunci_konfigurasi"', $silang->body);
        $this->assertSame(403, $this->postSeb($mhs, "{$b}/mulai", [], $d['kunci'])->status);
        $this->assertSame('0', (string) $this->db->query('SELECT COUNT(*) AS n FROM attempts')->fetch_assoc()['n']);
        $this->assertSame(403, $this->getSeb($mhs, "/api/sesi/{$sesiB}/keadaan", $d['kunci'])->status);
        // Denyut tidak terikat satu ujian: SEB salah satu ujiannya cukup.
        $this->assertSame(200, $this->postSeb($mhs, '/api/seb/denyut', [], $d['kunci'])->status);
        $this->assertSame(200, $this->postSeb($mhs, '/api/seb/denyut', [], $kB)->status);
        // Ujian tanpa konfigurasi: tidak seorang pun bisa memulainya.
        $uC = $this->buatDraf($http, $d['matkulX'], 'Ujian C', ["s{$soal}"]);
        $this->kirim($http, "/mata-kuliah/{$d['matkulX']}/ujian/{$uC}/terbit");
        $sesiC = $this->beri($http, $d['matkulX'], $uC, $d['kelasXA']);
        $r     = $this->getSeb($mhs, "/ujian-saya/{$sesiC}", $d['kunci']);
        $this->assertSame(403, $r->status);
        $this->assertStringContainsString('data-seb-wajib="tanpa_konfigurasi"', $r->body);
        $this->assertStringContainsString('data-seb-belum', $mhs->get('/beranda')->body);
        $this->assertStringContainsString('data-seb-ringkas="tanpa"', $http->get("/sesi/{$sesiC}")->body);
    }

    public function testGambarDanPengerjaanSesudahKeluarKelas(): void
    {
        $d    = $this->dunia();
        // Pengampu bekerja lewat kontainer ber-SEB juga: kontainer tes tidak berbagi volume gambar,
        // jadi berkas gambar harus diunggah ke kontainer yang nanti menyajikannya.
        $http = $this->sebagaiSeb($d['pengampuX']);
        $x    = $d['matkulX'];
        $s    = $this->buatSoal($http, $x, 'isian', ['text' => 'Lihat gambar', 'alt' => 'Gambar soal'], ['gambar' => ['a.png', self::gambar()]]);
        $u    = $this->buatDraf($http, $x, 'Kuis bergambar SEB', ["s{$s}"]);
        $this->kirim($http, "/mata-kuliah/{$x}/ujian/{$u}/terbit");
        $k    = $this->sebSiap($http, $x, $u);
        $sesi = $this->beri($http, $x, $u, $d['kelasXA']);
        $this->kirim($http, "/sesi/{$sesi}/mulai", ['menit' => '30']);
        $kunciGambar = (string) $this->db->query('SELECT kunci FROM images')->fetch_assoc()['kunci'];
        $mhs = $this->sebagaiSeb($d['mhsA']);
        $this->postSeb($mhs, "/ujian-saya/{$sesi}/mulai", [], $k);
        // Gambar soal: bagi mahasiswa hanya dari dalam SEB.
        $this->assertSame(403, $mhs->get("/gambar/{$kunciGambar}")->status);
        $this->assertSame(200, $this->getSeb($mhs, "/gambar/{$kunciGambar}", $k)->status);
        // Staf mengambil gambar yang sama dengan peramban biasa.
        $this->assertSame(200, $http->get("/gambar/{$kunciGambar}")->status);
        // Mahasiswa yang keluar dari kelas sesudah mulai tetap memegang pengerjaannya — juga di dalam SEB.
        $this->roster->query("UPDATE enrollments SET status = 'MENGUNDURKAN_DIRI' WHERE user_id = '{$d['mhsA']['id']}'");
        $this->assertSame(200, $this->getSeb($mhs, "/ujian-saya/{$sesi}/kerjakan", $k)->status);
        $this->assertSame(200, $this->postSeb($mhs, '/api/seb/denyut', [], $k)->status);
    }

    // ---- berkas untuk mahasiswa -------------------------------------------------------------

    public function testMahasiswaMengunduhBerkasUjiannyaSendiriDanAlamatMasuk(): void
    {
        $d    = $this->duniaSeb();
        $mhs  = $d['mhs'];
        $staf = $d['http']->get("/mata-kuliah/{$d['matkulX']}/ujian/{$d['ujian']}/seb/unduh")->body;
        // Diunduh di peramban biasa (tanpa header SEB), isinya sama dengan yang diunduh pengampu.
        $r = $mhs->get("/ujian-saya/{$d['sesi']}/seb");
        $s = $this->bukaBerkas($r, "LokaUjian-UJX101-{$d['ujian']}.seb");
        $this->assertSame($staf, $r->body);
        $this->assertSame($d['kunci'], Plist::kunciKonfig($s));
        // Mahasiswa kelas lain: 404, sama dengan sesi yang tidak ada. Tanpa sesi: ke halaman masuk.
        $asing = $this->sebagaiSeb($d['mhsB']);
        $this->assertSame(404, $asing->get("/ujian-saya/{$d['sesi']}/seb")->status);
        $this->assertContains((new Http((string) getenv('APP_SEB_TES_URL')))->get("/ujian-saya/{$d['sesi']}/seb")->status, [302, 303, 307]);
        // Tidak ada daftar atau unduhan publik: halaman petunjuk tidak menyebut ujian mana pun.
        $tamu = new Http((string) getenv('APP_SEB_TES_URL'));
        $pet  = $tamu->get('/seb');
        $this->assertSame(200, $pet->status);
        $this->assertStringContainsString('data-seb-keadaan="berlaku"', $pet->body);
        $this->assertStringNotContainsString('Ujian ber-SEB', $pet->body);
        $this->assertStringNotContainsString('.seb"', $pet->body);
        $this->assertStringContainsString('noindex', $pet->body);
        $this->assertSame(404, $tamu->get('/seb/unduh/UJX101.seb')->status);
        // Halaman periksa hanya melaporkan apakah header tiba.
        $this->assertStringContainsString('data-seb-periksa="tanpa_header"', $tamu->get('/seb/periksa')->body);
        $this->assertStringContainsString('data-seb-periksa="ada_header"', $tamu->get('/seb/periksa', self::sebHeader('/seb/periksa', $d['kunci']))->body);
        $this->assertSame(200, $tamu->get('/seb/keluar')->status);

        // Alamat masuk (startURL): mahasiswanya ke sesinya; yang lain ke beranda, tanpa pembeda.
        $masuk = "/ujian/{$d['ujian']}/masuk";
        $this->assertStringEndsWith("/ujian-saya/{$d['sesi']}", (string) $this->getSeb($mhs, $masuk, $d['kunci'])->header('Location'));
        $this->assertStringEndsWith("/ujian-saya/{$d['sesi']}", (string) $mhs->get($masuk)->header('Location'));
        $this->assertStringEndsWith('/beranda', (string) $asing->get($masuk)->header('Location'));
        $this->assertStringEndsWith('/beranda', (string) $asing->get('/ujian/99999999/masuk')->header('Location'));
        $this->assertStringEndsWith('/beranda', (string) $d['http']->get($masuk)->header('Location'));
    }

    // ---- peristiwa --------------------------------------------------------------------------

    public function testPenolakanDanPengakhiranMenjadiPeristiwaIntegritas(): void
    {
        $d    = $this->duniaSeb();
        $mhs  = $d['mhs'];
        $k    = $d['kunci'];
        $sesi = "/ujian-saya/{$d['sesi']}";
        $peristiwa = fn (): array => $this->db->query('SELECT kind, detail, user_id, session_id, attempt_id, ip FROM integrity_events ORDER BY id')->fetch_all(MYSQLI_ASSOC);

        // Peramban biasa yang mencoba berkali-kali: seb_rejected paling banyak satu per menit.
        for ($i = 0; $i < 3; $i++) {
            $this->assertSame(403, $mhs->get($sesi)->status);
        }
        $e = $peristiwa();
        $this->assertCount(1, $e);
        $this->assertSame(['seb_rejected', (string) $d['mhsA']['id'], (string) $d['sesi'], null], [$e[0]['kind'], $e[0]['user_id'], (string) $e[0]['session_id'], $e[0]['attempt_id']]);
        // MySQL mengurutkan kunci kolom JSON sendiri: isinya yang dibandingkan, bukan urutannya.
        $this->assertEquals(['alasan' => 'tanpa_header', 'jalur' => $sesi], json_decode((string) $e[0]['detail'], true));
        $this->assertNotSame('', (string) $e[0]['ip']);

        // Di dalam SEB yang sah lalu header hilang: seb_terminated, selalu dicatat, dengan pengerjaannya.
        $this->postSeb($mhs, "{$sesi}/mulai", [], $k);
        $this->assertSame(200, $this->getSeb($mhs, "{$sesi}/kerjakan", $k)->status);
        $this->assertSame(403, $mhs->get("/api/sesi/{$d['sesi']}/keadaan")->status);
        $e = $peristiwa();
        $this->assertCount(2, $e);
        $a = $this->pengerjaan($d['sesi'], (string) $d['mhsA']['id']);
        $this->assertSame(['seb_terminated', (string) $a['id']], [$e[1]['kind'], (string) $e[1]['attempt_id']]);
        // Sewanya sudah dihapus: penolakan berikutnya kembali seb_rejected dan masih dibatasi lajunya.
        $this->assertSame(403, $mhs->get("/api/sesi/{$d['sesi']}/keadaan")->status);
        $this->assertCount(2, $peristiwa());
        // Jawaban tersimpan tidak tersentuh dan jam terus berjalan: kembali ke SEB, lanjut.
        $this->assertSame(200, $this->getSeb($mhs, "{$sesi}/kerjakan", $k)->status);

        // Daftar peristiwa sesi: pengajar kelasnya melihat; pengajar kelas lain 404.
        $daftar = $this->sebagai($d['pengajarA'])->get("/sesi/{$d['sesi']}/peristiwa");
        $this->assertSame(200, $daftar->status);
        $this->assertSame(2, substr_count($daftar->body, '<tr data-jenis='));
        $this->assertStringContainsString('data-jenis="seb_terminated"', $daftar->body);
        $this->assertStringContainsString((string) $d['mhsA']['nomor_induk'], $daftar->body);
        $this->assertSame(404, $this->sebagai($d['pengajarB'])->get("/sesi/{$d['sesi']}/peristiwa")->status);
        $this->assertStringContainsString('data-peristiwa-taut', $this->sebagai($d['pengajarA'])->get("/sesi/{$d['sesi']}")->body);
    }
}
