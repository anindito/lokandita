<?php

declare(strict_types=1);

namespace Tests\Seb;

use Lokandita\Auth\Seb\Plist;
use Lokandita\Auth\Testing\Fixtures;
use Lokandita\Auth\Testing\Http;
use Tests\Support\KodeCase;

/**
 * Safe Exam Browser (SPEC.md §13.1, KONTRAK.md §8), lewat HTTP sungguhan ke DUA kontainer:
 *   lokakode-tes      SEB_ENFORCEMENT=disabled (APP_TES_URL)
 *   lokakode-seb-tes  SEB_ENFORCEMENT=enabled  (APP_SEB_TES_URL)
 * Klien tes bukan SEB: header dihitung tes ini dari kunci yang diketahuinya, menurut rumus
 * KONTRAK.md §8. Bahwa SEB 3.10.2 sungguhan mengirim header yang sama TIDAK dibuktikan di sini.
 */
final class SebTest extends KodeCase
{
    private const SANDI = 'kata-sandi-keluar-uji';
    private const BEK   = 'b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0';

    /**
     * @param array<string, mixed> $user
     */
    private function sebagaiSeb(array $user): Http
    {
        $http = new Http((string) getenv('APP_SEB_TES_URL'));
        $http->cookies['lokandita_sid'] = Fixtures::session($this->idn, (string) $user['id']);

        return $http;
    }

    /**
     * Header yang dikirim SEB untuk satu alamat (KONTRAK.md §8).
     *
     * @return array<string, string>
     */
    private static function header(string $jalur, string $kunciKonfig, string $bek): array
    {
        $url = rtrim((string) getenv('APP_URL'), '/') . $jalur;

        return [
            'X-SafeExamBrowser-ConfigKeyHash' => hash('sha256', $url . $kunciKonfig),
            'X-SafeExamBrowser-RequestHash'   => hash('sha256', $url . $bek),
        ];
    }

    /**
     * Pengampu membuat konfigurasi mata kuliah lewat panelnya dan mendaftarkan satu kunci.
     *
     * @param array<string, mixed> $pengampu
     *
     * @return string Config Key konfigurasi yang berlaku
     */
    private function siapkan(array $pengampu, int $matkul, ?string $bek = self::BEK): string
    {
        $http = $this->sebagai($pengampu);
        $csrf = $this->csrf($http);
        $r    = $http->post("/mata-kuliah/{$matkul}/seb", ['csrf' => $csrf, 'sandi_keluar' => self::SANDI]);
        $this->assertSame(303, $r->status, $r->body);
        if ($bek !== null) {
            $this->assertSame(303, $http->post("/mata-kuliah/{$matkul}/seb/kunci", ['csrf' => $csrf, 'kunci' => $bek])->status);
        }

        return (string) $this->row("SELECT config_key FROM seb_configs WHERE course_id = {$matkul} AND retired_at IS NULL")['config_key'];
    }

    /**
     * @return array<string, mixed> setelan dari berkas .seb yang diunduh
     */
    private function unduh(string $kode): array
    {
        $r = $this->tamu()->get("/seb/unduh/{$kode}.seb");
        $this->assertSame(200, $r->status);
        $this->assertStringContainsString("LokaKode-{$kode}.seb", (string) $r->header('Content-Disposition'));
        $dalam = gzdecode($r->body);
        $this->assertIsString($dalam, 'berkas .seb adalah arsip gzip');
        $this->assertSame('plnd', substr($dalam, 0, 4), 'awalan data tak terenkripsi');
        $xml = gzdecode(substr($dalam, 4));
        $this->assertIsString($xml);

        return PlistTest::bacaPlist($xml);
    }

    // ---- panel pengampu ---------------------------------------------------------------

    public function testPengampuMembuatKonfigurasiDanBerkasnyaMemuatKunciYangSama(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengampuX']);
        $csrf = $this->csrf($http);

        // Kata sandi keluar di bawah 12 karakter ditolak; tidak ada yang tersimpan.
        $r = $http->post("/mata-kuliah/{$d['matkulX']}/seb", ['csrf' => $csrf, 'sandi_keluar' => 'pendek12345']);
        $this->assertSame(422, $r->status);
        $this->assertSame(0, $this->hitung('SELECT COUNT(*) FROM seb_configs'));

        $kunci = $this->siapkan($d['pengampuX'], $d['matkulX'], null);
        $hal   = $http->get("/mata-kuliah/{$d['matkulX']}/seb");
        $this->assertSame(200, $hal->status);
        $this->assertStringContainsString('data-seb-versi="1"', $hal->body);
        $this->assertStringContainsString($kunci, $hal->body);
        $this->assertStringContainsString('data-kunci-kosong', $hal->body, 'tanpa kunci terdaftar panel memperingatkan');

        // Berkas yang diunduh siapa pun: Config Key-nya, dihitung dari ISI BERKAS, sama dengan yang disimpan server.
        $s = $this->unduh('UJX101');
        $this->assertSame($kunci, Plist::kunciKonfig($s));
        $this->assertSame('https://lokakode.lokandita.com/beranda', $s['startURL']);
        $this->assertSame(hash('sha256', self::SANDI), $s['hashedQuitPassword']);
        // Daftar URL selalu memuat asal aplikasi dan asal LokaAuth (tanpa itu tidak bisa login dari SEB).
        $ekspresi = array_column($s['URLFilterRules'], 'expression');
        $this->assertContains('https://.lokakode.lokandita.com', $ekspresi);
        $this->assertContains('https://.auth.lokandita.com', $ekspresi);
        $this->assertTrue($s['createNewDesktop']);
        $this->assertTrue($s['sendBrowserExamKey']);

        // Kata sandi keluar tidak tersimpan di mana pun: tidak di kolom, tidak di audit.
        $baris = $this->row('SELECT config_enc FROM seb_configs');
        $this->assertStringNotContainsString(self::SANDI, (string) $baris['config_enc']);
        $this->assertStringNotContainsString('startURL', (string) $baris['config_enc'], 'konfigurasi tersimpan terenkripsi');
        $audit = $this->row("SELECT action, detail FROM audit_log WHERE action LIKE 'seb.%'");
        $this->assertSame('seb.dibuat', $audit['action']);
        $this->assertStringNotContainsString(self::SANDI, (string) $audit['detail']);
    }

    public function testRotasiMematikanBerkasDanKunciLama(): void
    {
        $d    = $this->dunia();
        $lama = $this->siapkan($d['pengampuX'], $d['matkulX']);
        $mhs  = $this->sebagaiSeb($d['mhsA']);
        $this->assertSame(200, $mhs->get('/beranda', self::header('/beranda', $lama, self::BEK))->status);

        $baru = $this->siapkan($d['pengampuX'], $d['matkulX'], null);
        $this->assertNotSame($lama, $baru, 'garam baru, kunci baru');
        $this->assertSame(2, (int) $this->row("SELECT version FROM seb_configs WHERE retired_at IS NULL")['version']);
        $this->assertSame(1, $this->hitung('SELECT COUNT(*) FROM seb_configs WHERE retired_at IS NOT NULL'));
        $this->assertSame($baru, Plist::kunciKonfig($this->unduh('UJX101')));

        // Berkas lama dengan kunci lama: ditolak. Berkas baru tanpa kunci terdaftar ulang: juga ditolak.
        $r = $mhs->get('/beranda', self::header('/beranda', $lama, self::BEK));
        $this->assertSame(403, $r->status);
        $this->assertStringContainsString('data-seb-wajib="kunci_konfigurasi"', $r->body);
        $r = $mhs->get('/beranda', self::header('/beranda', $baru, self::BEK));
        $this->assertSame(403, $r->status);
        $this->assertStringContainsString('data-seb-wajib="kunci_peramban"', $r->body);
    }

    public function testDaftarKunciDiperiksaBentuknya(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengampuX']);
        $csrf = $this->csrf($http);
        // Belum ada konfigurasi.
        $this->assertSame(422, $http->post("/mata-kuliah/{$d['matkulX']}/seb/kunci", ['csrf' => $csrf, 'kunci' => self::BEK])->status);
        $this->siapkan($d['pengampuX'], $d['matkulX'], null);
        $this->assertSame(422, $http->post("/mata-kuliah/{$d['matkulX']}/seb/kunci", ['csrf' => $csrf, 'kunci' => "bukan-kunci\n" . self::BEK])->status);
        $this->assertSame(0, $this->hitung('SELECT COUNT(*) FROM seb_exam_keys'));
        // Huruf besar dan baris kosong diterima; daftar baru menggantikan yang lama.
        $dua = strtoupper(self::BEK) . "\r\n\r\n" . str_repeat('c1', 32);
        $this->assertSame(303, $http->post("/mata-kuliah/{$d['matkulX']}/seb/kunci", ['csrf' => $csrf, 'kunci' => $dua])->status);
        $this->assertSame(2, $this->hitung('SELECT COUNT(*) FROM seb_exam_keys'));
        $this->assertSame(303, $http->post("/mata-kuliah/{$d['matkulX']}/seb/kunci", ['csrf' => $csrf, 'kunci' => str_repeat('c1', 32)])->status);
        $this->assertSame([str_repeat('c1', 32)], array_column($this->db->query('SELECT exam_key FROM seb_exam_keys')->fetch_all(MYSQLI_ASSOC), 'exam_key'));
        // Audit menyebut jumlah, bukan kuncinya.
        $audit = $this->db->query("SELECT detail FROM audit_log WHERE action = 'seb.kunci'")->fetch_all(MYSQLI_ASSOC);
        $this->assertCount(2, $audit);
        $this->assertStringNotContainsString('c1c1', json_encode($audit));
    }

    /**
     * IDOR: pengampu mata kuliah lain tidak melihat panel itu (404) dan tidak bisa merotasi atau
     * mendaftarkan kunci; pengajar kelas di mata kuliah itu melihat mata kuliahnya tetapi mendapat 403.
     */
    public function testPanelHanyaUntukPengampunyaDanSuperadmin(): void
    {
        $d     = $this->dunia();
        $kunci = $this->siapkan($d['pengampuX'], $d['matkulX']);
        foreach (['pengampuY' => 404, 'pengajarA' => 403, 'mhsA' => 403] as $pelaku => $status) {
            $http = $this->sebagai($d[$pelaku]);
            $csrf = $this->csrf($http);
            $this->assertSame($status, $http->get("/mata-kuliah/{$d['matkulX']}/seb")->status, $pelaku);
            $this->assertSame($status, $http->post("/mata-kuliah/{$d['matkulX']}/seb", ['csrf' => $csrf, 'sandi_keluar' => self::SANDI])->status, $pelaku);
            $this->assertSame($status, $http->post("/mata-kuliah/{$d['matkulX']}/seb/kunci", ['csrf' => $csrf, 'kunci' => str_repeat('ee', 32)])->status, $pelaku);
        }
        $this->assertSame(1, $this->hitung('SELECT COUNT(*) FROM seb_configs'));
        $this->assertSame($kunci, (string) $this->row('SELECT config_key FROM seb_configs')['config_key']);
        $this->assertSame([self::BEK], array_column($this->db->query('SELECT exam_key FROM seb_exam_keys')->fetch_all(MYSQLI_ASSOC), 'exam_key'));
        $this->assertSame(200, $this->sebagai($d['superadmin'])->get("/mata-kuliah/{$d['matkulX']}/seb")->status);
    }

    // ---- penegakan (kontainer SEB_ENFORCEMENT=enabled) ----------------------------------

    public function testMahasiswaTanpaSebDitolakDanStafTidak(): void
    {
        $d = $this->dunia();
        $this->siapkan($d['pengampuX'], $d['matkulX']);

        $mhs = $this->sebagaiSeb($d['mhsA']);
        $r   = $mhs->get('/beranda');
        $this->assertSame(403, $r->status);
        $this->assertStringContainsString('data-seb-wajib="tanpa_header"', $r->body);
        $this->assertStringContainsString('href="/seb"', $r->body);
        // User-Agent tidak pernah dipercaya.
        $this->assertSame(403, $mhs->get('/beranda', ['User-Agent' => 'Mozilla/5.0 SEB/3.10.2 (x64)'])->status);
        // Titik akhir API dijawab JSON.
        $r = $mhs->get('/api/executions/' . $d['jobA']);
        $this->assertSame(403, $r->status);
        $this->assertSame('seb_wajib', $r->json()['error']);
        $csrf = $this->csrf($this->sebagai($d['mhsA']));
        $this->assertNotSame('', $csrf);

        // Penolakan adalah peristiwa integritas; paling banyak satu per menit per pengguna.
        $ev = $this->db->query('SELECT user_id, kind, detail FROM integrity_events')->fetch_all(MYSQLI_ASSOC);
        $this->assertCount(1, $ev);
        $this->assertSame([$d['mhsA']['id'], 'seb_rejected'], [$ev[0]['user_id'], $ev[0]['kind']]);
        $this->assertSame('tanpa_header', json_decode($ev[0]['detail'], true)['alasan']);

        // Staf memakai peramban biasa: pengampu, pengajar dan superadmin tidak diperiksa.
        foreach (['pengampuX', 'pengajarA', 'superadmin'] as $staf) {
            $this->assertSame(200, $this->sebagaiSeb($d[$staf])->get('/beranda')->status, $staf);
        }
        // Rute publik tetap terbuka tanpa sesi dan tanpa SEB, dan Keluar tetap bekerja bagi mahasiswa.
        $tamu = new Http((string) getenv('APP_SEB_TES_URL'));
        foreach (['/', '/seb', '/seb/periksa', '/seb/keluar', '/seb/unduh/UJX101.seb'] as $publik) {
            $this->assertSame(200, $tamu->get($publik)->status, $publik);
        }
        // Ekspor data sendiri tidak bergantung pada SEB.
        $this->assertSame(200, $mhs->get('/data-saya')->status);
    }

    public function testKeduaHeaderHarusCocokUntukAlamatItu(): void
    {
        $d     = $this->dunia();
        $kunci = $this->siapkan($d['pengampuX'], $d['matkulX']);
        $mhs   = $this->sebagaiSeb($d['mhsA']);

        $r = $mhs->get('/beranda', self::header('/beranda', $kunci, self::BEK));
        $this->assertSame(200, $r->status, 'kedua header cocok');
        $this->assertStringNotContainsString('MODE UJI', $r->body);
        $this->assertStringContainsString('/js/seb.js', $r->body, 'halaman di dalam SEB memuat skrip denyut');
        $this->assertStringContainsString('data-seb-keluar', $r->body);

        // Config Key benar, Browser Exam Key salah — dan sebaliknya.
        $r = $mhs->get('/beranda', self::header('/beranda', $kunci, str_repeat('ab', 32)));
        $this->assertSame(403, $r->status);
        $this->assertStringContainsString('data-seb-wajib="kunci_peramban"', $r->body);
        $r = $mhs->get('/beranda', self::header('/beranda', str_repeat('cd', 32), self::BEK));
        $this->assertSame(403, $r->status);
        $this->assertStringContainsString('data-seb-wajib="kunci_konfigurasi"', $r->body);
        // Hanya satu header.
        $h = self::header('/beranda', $kunci, self::BEK);
        $this->assertSame(403, $mhs->get('/beranda', ['X-SafeExamBrowser-ConfigKeyHash' => $h['X-SafeExamBrowser-ConfigKeyHash']])->status);
        // Header yang sah untuk alamat LAIN tidak bisa dipakai ulang; kueri ikut di-hash.
        $this->assertSame(403, $mhs->get('/beranda', self::header('/latihan/1', $kunci, self::BEK))->status);
        $this->assertSame(403, $mhs->get('/beranda?x=1', self::header('/beranda', $kunci, self::BEK))->status);
        $this->assertSame(200, $mhs->get('/beranda?x=1', self::header('/beranda?x=1', $kunci, self::BEK))->status);
        // Header Host dari klien tidak ikut menentukan alamat yang di-hash.
        $this->assertSame(200, $mhs->get('/beranda', self::header('/beranda', $kunci, self::BEK) + ['Host' => 'jahat.example'])->status);
    }

    public function testKunciMataKuliahLainTidakBerlaku(): void
    {
        $d      = $this->dunia();
        $kunciX = $this->siapkan($d['pengampuX'], $d['matkulX']);
        // Mahasiswa Y (mata kuliah Y, belum punya konfigurasi) dengan berkas mata kuliah X.
        $mhsY = $this->sebagaiSeb($d['mhsY']);
        $r    = $mhsY->get('/beranda', self::header('/beranda', $kunciX, self::BEK));
        $this->assertSame(403, $r->status);
        $this->assertStringContainsString('data-seb-wajib="tanpa_konfigurasi"', $r->body);

        $kunciY = $this->siapkan($d['pengampuY'], $d['matkulY'], str_repeat('d2', 32));
        $this->assertSame(403, $mhsY->get('/beranda', self::header('/beranda', $kunciX, self::BEK))->status);
        // Config Key mata kuliah Y dengan Browser Exam Key mata kuliah X: keduanya harus dari konfigurasi yang SAMA.
        $this->assertSame(403, $mhsY->get('/beranda', self::header('/beranda', $kunciY, self::BEK))->status);
        $this->assertSame(200, $mhsY->get('/beranda', self::header('/beranda', $kunciY, str_repeat('d2', 32)))->status);
        // Mahasiswa yang mundur dari mata kuliahnya tidak lagi punya konfigurasi.
        $this->roster->query("UPDATE enrollments SET status = 'MENGUNDURKAN_DIRI' WHERE id = {$d['daftarY']}");
        $this->assertSame(403, $mhsY->get('/beranda', self::header('/beranda', $kunciY, str_repeat('d2', 32)))->status);
    }

    /**
     * Denyut dan sewa: sesi yang tadinya di dalam SEB lalu kehilangan headernya dicatat sebagai
     * "diakhiri", bukan sekadar "ditolak"; draf tidak tersentuh.
     */
    public function testDenyutDanPengakhiranSesi(): void
    {
        $d     = $this->dunia();
        $kunci = $this->siapkan($d['pengampuX'], $d['matkulX']);
        $mhs   = $this->sebagaiSeb($d['mhsA']);
        $r     = $mhs->get('/beranda', self::header('/beranda', $kunci, self::BEK));
        $this->assertSame(200, $r->status);
        $csrf = preg_match('/data-csrf="([^"]+)"/', $r->body, $m) === 1 ? $m[1] : '';

        $r = $mhs->post('/api/seb/denyut', ['csrf' => $csrf], self::header('/api/seb/denyut', $kunci, self::BEK));
        $this->assertSame(200, $r->status, $r->body);
        $this->assertSame(['ok' => true, 'sewa' => 30], $r->json());
        $sewa = \App\Libraries\Antrean::redis()->keys('lokakode:seb:sewa:' . $d['mhsA']['id'] . ':*');
        $this->assertCount(1, $sewa);
        $ttl = \App\Libraries\Antrean::redis()->ttl($sewa[0]);
        $this->assertTrue($ttl > 0 && $ttl <= 30, "sewa 30 detik, tersisa {$ttl}");

        // Denyut berikutnya tanpa header yang sah: 403 JSON, peristiwa "diakhiri", sewa hilang.
        $r = $mhs->post('/api/seb/denyut', ['csrf' => $csrf]);
        $this->assertSame(403, $r->status);
        $this->assertSame('seb_wajib', $r->json()['error']);
        $this->assertSame(['seb_terminated'], array_column($this->db->query('SELECT kind FROM integrity_events')->fetch_all(MYSQLI_ASSOC), 'kind'));
        $this->assertSame([], \App\Libraries\Antrean::redis()->keys('lokakode:seb:sewa:' . $d['mhsA']['id'] . ':*'));
        // Permintaan berikutnya, tetap tanpa SEB, hanyalah "ditolak".
        $this->assertSame(403, $mhs->get('/beranda')->status);
        $this->assertSame(['seb_terminated', 'seb_rejected'], array_column($this->db->query('SELECT kind FROM integrity_events ORDER BY id')->fetch_all(MYSQLI_ASSOC), 'kind'));
        // Sesi LokaAuth tidak dicabut: mahasiswa cukup membuka SEB lagi.
        $this->assertSame(200, $mhs->get('/beranda', self::header('/beranda', $kunci, self::BEK))->status);
    }

    // ---- mode uji (kontainer SEB_ENFORCEMENT=disabled) ----------------------------------

    public function testModeUjiMelewatiPemeriksaanDanMemasangPitaMerahUntukMahasiswa(): void
    {
        $d = $this->dunia();
        $this->siapkan($d['pengampuX'], $d['matkulX']);

        $r = $this->sebagai($d['mhsA'])->get('/beranda');
        $this->assertSame(200, $r->status, 'tanpa SEB mahasiswa tetap masuk');
        $this->assertMatchesRegularExpression('/<div class="pita-bahaya" role="alert" data-pita>MODE UJI: penegakan SEB dinonaktifkan<\/div>/', $r->body);
        $this->assertStringNotContainsString('/js/seb.js', $r->body);
        $this->assertSame(0, $this->hitung('SELECT COUNT(*) FROM integrity_events'));
        // Staf tidak mendapat pita; panel pengampu menyebut keadaannya.
        $this->assertStringNotContainsString('data-pita', $this->sebagai($d['pengajarA'])->get('/beranda')->body);
        $this->assertStringContainsString('data-seb-mode-uji', $this->sebagai($d['pengampuX'])->get("/mata-kuliah/{$d['matkulX']}/seb")->body);
        $this->assertStringContainsString('data-seb-keadaan="belum"', $this->tamu()->get('/seb')->body);
    }

    // ---- halaman publik -----------------------------------------------------------------

    public function testHalamanPetunjukDanPeriksa(): void
    {
        $d     = $this->dunia();
        $tamu  = new Http((string) getenv('APP_SEB_TES_URL'));
        $r     = $tamu->get('/seb');
        $this->assertSame(200, $r->status);
        $this->assertStringContainsString('3.10.x', $r->body);
        $this->assertStringContainsString('Windows 64-bit (x64)', $r->body);
        $this->assertStringContainsString('data-seb-keadaan="berlaku"', $r->body);
        $this->assertStringNotContainsString('.seb"', $r->body, 'belum ada mata kuliah berkonfigurasi');
        $this->assertSame(404, $tamu->get('/seb/unduh/UJX101.seb')->status);
        $this->assertSame(404, $tamu->get('/seb/unduh/..%2Fx.seb')->status);

        $kunci = $this->siapkan($d['pengampuX'], $d['matkulX']);
        $r     = $tamu->get('/seb');
        $this->assertStringContainsString('href="/seb/unduh/UJX101.seb"', $r->body);
        // Halaman publik tidak menyebut orang.
        foreach (['pengampuX', 'mhsA'] as $orang) {
            $this->assertStringNotContainsString((string) $d[$orang]['nama'], $r->body);
        }

        // Halaman periksa: peramban biasa; lalu "SEB" dengan kedua header.
        $r = $tamu->get('/seb/periksa');
        $this->assertStringContainsString('data-seb-periksa="tanpa_header"', $r->body);
        $this->assertStringContainsString('noindex', $r->body);
        $r = $tamu->get('/seb/periksa', self::header('/seb/periksa', $kunci, self::BEK));
        $this->assertStringContainsString('data-seb-periksa="lolos"', $r->body);
        $this->assertMatchesRegularExpression('/data-matkul="UJX101">.*?data-konfig="1".*?data-peramban="1"/s', $r->body);
        $r = $tamu->get('/seb/periksa', self::header('/seb/periksa', $kunci, str_repeat('ab', 32)));
        $this->assertMatchesRegularExpression('/data-matkul="UJX101">.*?data-konfig="1".*?data-peramban="0"/s', $r->body);
        // Tidak ada kunci di halaman itu.
        $this->assertStringNotContainsString($kunci, $r->body);
        $this->assertStringNotContainsString(self::BEK, $r->body);
    }
}
