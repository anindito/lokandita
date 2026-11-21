<?php

declare(strict_types=1);

namespace Tests\Publik;

use Tests\Support\KodeCase;

/**
 * Ekspor data sendiri dari LokaKode (SPEC.md §16, KONTRAK.md §14), lewat HTTP sungguhan.
 * Yang terpenting: berkas seseorang tidak memuat apa pun milik orang lain, dan tidak
 * memuat isi tes tersembunyi, generator atau solusi referensi.
 */
final class DataSayaTest extends KodeCase
{
    /**
     * Mahasiswa A (kelas XA) dan B (kelas XB) di mata kuliah yang sama, masing-masing dengan kiriman, draf dan peristiwa sendiri.
     *
     * @return array<string, mixed>
     */
    private function duniaBerdua(): array
    {
        $d    = $this->dunia();
        $soal = $this->soalSiap($d['pertemuanX2'], 'soal-ekspor');
        $this->terbit($d['pertemuanX2']);
        $this->jendela($d['pertemuanX2'], $d['kelasXA']);
        foreach (['mhsA' => 'KODE_RAHASIA_ANI', 'mhsB' => 'KODE_RAHASIA_BUDI'] as $siapa => $tanda) {
            $this->kirimanDinilai($d[$siapa], $soal, $this->instans($d[$siapa], $soal), 7, 10, "int main(){/* {$tanda} */}");
            $id = $d[$siapa]['id'];
            $this->db->query("INSERT INTO drafts (user_id, meeting_problem_id, source, updated_at) VALUES ('{$id}', {$soal['mp']}, 'DRAF_{$tanda}', UTC_TIMESTAMP())");
            $this->db->query("INSERT INTO integrity_events (user_id, kind, meeting_problem_id, detail, ip, created_at) VALUES ('{$id}', 'paste', {$soal['mp']}, '{\"rahasia\":\"DETAIL_{$tanda}\"}', '203.0.113.9', UTC_TIMESTAMP())");
        }
        $d['soal'] = $soal;

        return $d;
    }

    /**
     * @return array{0: \Lokandita\Auth\Testing\Http, 1: array<string, mixed>}
     */
    private function unduh(array $user): array
    {
        $http = $this->sebagai($user);
        $r    = $http->post('/data-saya/unduh', ['csrf' => $this->csrf($http)]);
        $this->assertSame(200, $r->status, $r->body);

        return [$r, json_decode($r->body, true, 512, JSON_THROW_ON_ERROR)];
    }

    public function testBerkasHanyaBerisiDataPemiliknya(): void
    {
        $d = $this->duniaBerdua();
        [$r, $data] = $this->unduh($d['mhsA']);

        $this->assertStringContainsString('application/json', (string) $r->header('Content-Type'));
        $this->assertSame('attachment; filename="lokandita-lokakode-data-saya.json"', $r->header('Content-Disposition'));
        $this->assertStringContainsString('no-store', (string) $r->header('Cache-Control'));
        $this->assertSame('lokakode', $data['aplikasi']);
        $this->assertSame($d['mhsA']['id'], $data['pengguna']);
        $this->assertSame([], $data['terpotong']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d\d-\d\dT\d\d:\d\d:\d\dZ$/', $data['dibuat']);
        foreach (['pendaftaran', 'mengajar', 'mengampu', 'nilai_pertemuan', 'nilai_soal', 'kiriman', 'draf', 'peristiwa_integritas', 'preferensi_tampilan'] as $kunci) {
            $this->assertArrayHasKey($kunci, $data);
        }

        // Miliknya sendiri ada, lengkap dengan kode sumbernya.
        $this->assertCount(1, $data['kiriman']);
        $this->assertStringContainsString('KODE_RAHASIA_ANI', $data['kiriman'][0]['source']);
        $this->assertSame('soal-ekspor', $data['kiriman'][0]['soal']);
        $this->assertSame('DRAF_KODE_RAHASIA_ANI', $data['draf'][0]['source']);
        $this->assertSame('paste', $data['peristiwa_integritas'][0]['kind']);
        $this->assertNotSame([], $data['pendaftaran']);
        $this->assertSame('AKTIF', $data['pendaftaran'][0]['status']);
        $this->assertSame([], $data['mengajar']);

        // Milik orang lain tidak ada — kasus IDOR ekspor ini.
        foreach (['KODE_RAHASIA_BUDI', $d['mhsB']['id'], $d['mhsB']['nomor_induk'], $d['mhsB']['nama'], $d['pengajarA']['id'], $d['pengampuX']['id']] as $asing) {
            $this->assertStringNotContainsString($asing, $r->body, "data orang lain di berkas: {$asing}");
        }
        // Isi tes, generator, solusi referensi dan detail peristiwa tidak pernah ikut.
        $tes = (string) $this->row("SELECT tests FROM problem_instances WHERE user_id = '{$d['mhsA']['id']}'")['tests'];
        $this->assertNotSame('', $tes);
        $satu = json_decode($tes, true)[0];
        foreach ([explode(' ', $satu['in'])[0], trim($satu['out']), 'random.Random', 'std::cin>>a>>b', 'DETAIL_KODE_RAHASIA', '203.0.113.9', '"tests"', '"generator"', '"reference_solution"'] as $rahasia) {
            $this->assertStringNotContainsString((string) $rahasia, $r->body, "rahasia di berkas: {$rahasia}");
        }

        // Dicatat di log audit LokaKode.
        $audit = $this->row("SELECT actor_id, target_tipe, target_id FROM audit_log WHERE action = 'data.diekspor'");
        $this->assertSame([$d['mhsA']['id'], 'user', $d['mhsA']['id']], array_values((array) $audit));
    }

    public function testTidakAdaCaraMemintaDataOrangLain(): void
    {
        $d = $this->duniaBerdua();
        // Parameter apa pun diabaikan: yang keluar tetap data pengguna sesi itu, juga bagi superadmin.
        foreach (['mhsB', 'pengajarA', 'superadmin'] as $siapa) {
            $http = $this->sebagai($d[$siapa]);
            $r    = $http->post('/data-saya/unduh?user_id=' . $d['mhsA']['id'], ['csrf' => $this->csrf($http), 'user_id' => $d['mhsA']['id'], 'pengguna' => $d['mhsA']['id']]);
            $this->assertSame(200, $r->status, $siapa);
            $this->assertSame($d[$siapa]['id'], json_decode($r->body, true)['pengguna'], $siapa);
            $this->assertStringNotContainsString('KODE_RAHASIA_ANI', $r->body, $siapa);
            $this->assertStringNotContainsString($d['mhsA']['id'], $r->body, $siapa);
        }
        // Dosen mendapat kelas yang diajarnya, bukan daftar mahasiswanya.
        [, $data] = $this->unduh($d['pengampuX']);
        $this->assertNotSame([], $data['mengampu']);
        $this->assertSame([], $data['kiriman']);
        // Tidak ada rute ber-id.
        $http = $this->sebagai($d['superadmin']);
        $this->assertSame(404, $http->get('/data-saya/' . $d['mhsA']['id'])->status);
        $this->assertSame(404, $http->post('/data-saya/unduh/' . $d['mhsA']['id'], ['csrf' => $this->csrf($http)])->status);
    }

    public function testTanpaSesiTanpaCsrfDanTerlaluCepatDitolak(): void
    {
        $d = $this->duniaBerdua();
        $r = $this->tamu()->get('/data-saya');
        $this->assertStringStartsWith('https://auth.lokandita.com/login?next=', (string) $r->header('Location'));
        $tamu = $this->tamu();
        $this->assertNotSame(200, $tamu->post('/data-saya/unduh')->status);

        $http = $this->sebagai($d['mhsA']);
        $hal  = $http->get('/data-saya');
        $this->assertSame(200, $hal->status);
        $this->assertStringContainsString('<meta name="robots" content="noindex, nofollow">', $hal->body);
        $this->assertStringContainsString('action="/data-saya/unduh"', $hal->body);
        $this->assertSame(403, $http->post('/data-saya/unduh')->status, 'tanpa CSRF');
        $this->assertSame(0, $this->hitung("SELECT COUNT(*) FROM audit_log WHERE action = 'data.diekspor'"));

        $csrf = $this->csrf($http);
        $this->assertSame(200, $http->post('/data-saya/unduh', ['csrf' => $csrf])->status);
        // Sekali per 60 detik per pengguna (KONTRAK.md §14); pengguna lain tidak terpengaruh.
        $kedua = $http->post('/data-saya/unduh', ['csrf' => $csrf]);
        $this->assertSame(429, $kedua->status);
        $this->assertSame('60', $kedua->header('Retry-After'));
        $this->assertStringNotContainsString('KODE_RAHASIA_ANI', $kedua->body);
        $this->assertSame(1, $this->hitung("SELECT COUNT(*) FROM audit_log WHERE action = 'data.diekspor'"));
        $this->unduh($d['mhsB']);
    }
}
