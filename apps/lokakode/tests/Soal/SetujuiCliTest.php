<?php

declare(strict_types=1);

namespace Tests\Soal;

use Tests\Support\KodeCase;
use Tests\Support\SoalBantu;

/**
 * `spark soal:setujui` (SPEC.md §8.1): persetujuan lewat perintah sama dengan
 * lewat halaman web, hanya oleh superadmin atau pengampu, dan selalu tercatat.
 */
final class SetujuiCliTest extends KodeCase
{
    use SoalBantu;

    /**
     * Dunia uji dengan empat soal di pertemuan X1: menunggu (dari dunia), menunggu, ditolak, dan
     * satu yang versi 1-nya disetujui dan versi 2-nya menunggu.
     *
     * @return array<string, mixed>
     */
    private function duniaTinjau(): array
    {
        $d = $this->dunia();
        $d['b'] = $this->soalBaris($d['pertemuanX1'], 'soal-b');
        $d['c'] = $this->soalBaris($d['pertemuanX1'], 'soal-c', 'REJECTED');
        $d['e'] = $this->soalSiap($d['pertemuanX1'], 'soal-e');
        $d['e2'] = $this->versiBaris($d['e']['soal'], 2);

        return $d;
    }

    /**
     * @return array<string, string> slug => status versi terakhir
     */
    private function statusSoal(): array
    {
        return array_column($this->db->query('
            SELECT p.slug, v.status FROM problems p JOIN problem_versions v ON v.problem_id = p.id
                AND v.version = (SELECT MAX(v2.version) FROM problem_versions v2 WHERE v2.problem_id = p.id) ORDER BY p.slug')->fetch_all(MYSQLI_ASSOC), 'status', 'slug');
    }

    public function testSuperadminMenyetujuiSemuaYangMenungguDanTercatat(): void
    {
        $d = $this->duniaTinjau();
        [$kode, $keluar] = $this->spark(['soal:setujui', 'ujx101', '1', '--oleh', (string) $d['superadmin']['nomor_induk']]);
        $this->assertSame(0, $kode, $keluar);
        $this->assertStringContainsString('ringkasan: 3 disetujui, 1 dilewati, dari 4 soal', $keluar);
        $this->assertStringContainsString('DILEWATI   soal-c (versi 1: REJECTED)', $keluar);
        $this->assertSame(['jumlah-dua' => 'APPROVED', 'soal-b' => 'APPROVED', 'soal-c' => 'REJECTED', 'soal-e' => 'APPROVED'], $this->statusSoal());

        // Pertemuan memakai versi yang baru disetujui; soal-e berpindah ke versi 2.
        $this->assertSame(3, $this->hitung("SELECT COUNT(*) FROM meeting_problems WHERE meeting_id = {$d['pertemuanX1']}"));
        $this->assertSame((string) $d['e2'], $this->row("SELECT problem_version_id FROM meeting_problems WHERE problem_id = {$d['e']['soal']}")['problem_version_id']);
        $v = $this->row("SELECT reviewed_by, reviewed_at FROM problem_versions WHERE id = {$d['b']['versi']}");
        $this->assertSame($d['superadmin']['id'], $v['reviewed_by']);
        $this->assertNotNull($v['reviewed_at']);

        // Tiga baris audit, atas nama superadmin itu, bertanda lewat cli.
        $audit = $this->db->query("SELECT actor_id, course_id, detail FROM audit_log WHERE action = 'soal.disetujui' ORDER BY id")->fetch_all(MYSQLI_ASSOC);
        $this->assertCount(3, $audit);
        foreach ($audit as $a) {
            $this->assertSame([$d['superadmin']['id'], (string) $d['matkulX'], 'cli'], [$a['actor_id'], (string) $a['course_id'], json_decode($a['detail'], true)['lewat']]);
        }

        // Dijalankan lagi: tidak ada yang berubah, tidak ada audit baru, tetap keluar 0.
        [$kode, $keluar] = $this->spark(['soal:setujui', 'UJX101', '1', '--oleh', (string) $d['superadmin']['nomor_induk']]);
        $this->assertSame(0, $kode);
        $this->assertStringContainsString('ringkasan: 0 disetujui, 4 dilewati, dari 4 soal', $keluar);
        $this->assertSame(3, $this->hitung("SELECT COUNT(*) FROM audit_log WHERE action = 'soal.disetujui'"));
    }

    public function testPengampuBolehDanPilihanSlugMembatasi(): void
    {
        $d = $this->duniaTinjau();
        [$kode, $keluar] = $this->spark(['soal:setujui', 'UJX101', '1', '--oleh', (string) $d['pengampuX']['nomor_induk'], '--slug', 'soal-b']);
        $this->assertSame(0, $kode, $keluar);
        $this->assertStringContainsString('ringkasan: 1 disetujui, 0 dilewati, dari 1 soal', $keluar);
        $this->assertSame(['jumlah-dua' => 'PENDING_REVIEW', 'soal-b' => 'APPROVED', 'soal-c' => 'REJECTED', 'soal-e' => 'PENDING_REVIEW'], $this->statusSoal());
        $this->assertSame($d['pengampuX']['id'], $this->row("SELECT actor_id FROM audit_log WHERE action = 'soal.disetujui'")['actor_id']);

        // Slug yang tidak ada membatalkan seluruh perintah.
        [$kode, $keluar] = $this->spark(['soal:setujui', 'UJX101', '1', '--oleh', (string) $d['pengampuX']['nomor_induk'], '--slug', 'jumlah-dua,tidak-ada']);
        $this->assertSame(1, $kode);
        $this->assertStringContainsString('slug tidak ada di pertemuan ini: tidak-ada. Tidak ada yang disetujui.', $keluar);
        $this->assertSame('PENDING_REVIEW', $this->statusSoal()['jumlah-dua']);
    }

    public function testYangTidakBerhakDitolakDanTidakAdaYangBerubah(): void
    {
        $d    = $this->duniaTinjau();
        $awal = [$this->statusSoal(), $this->hitung('SELECT COUNT(*) FROM meeting_problems'), $this->hitung('SELECT COUNT(*) FROM audit_log')];
        $this->idn->query("UPDATE users SET status = 'suspended' WHERE id = '{$d['pengampuY']['id']}'");
        $tersuspen = $this->user('superadmin', 'Admin Tersuspen', ['status' => 'suspended']);

        // Pengampu mata kuliah lain, pengajar kelasnya, mahasiswa, superadmin yang tidak aktif, nomor yang tidak dikenal.
        foreach ([$d['pengajarA']['nomor_induk'], $d['pengajarB']['nomor_induk'], $d['mhsA']['nomor_induk'], $tersuspen['nomor_induk'], '000000-tidak-ada'] as $oleh) {
            [$kode, $keluar] = $this->spark(['soal:setujui', 'UJX101', '1', '--oleh', (string) $oleh]);
            $this->assertSame(1, $kode, (string) $oleh);
            $this->assertStringContainsString('bukan superadmin yang aktif dan bukan pengampu UJX101', $keluar);
        }
        $this->idn->query("UPDATE users SET status = 'active' WHERE id = '{$d['pengampuY']['id']}'");
        [$kode] = $this->spark(['soal:setujui', 'UJX101', '1', '--oleh', (string) $d['pengampuY']['nomor_induk']]);
        $this->assertSame(1, $kode, 'pengampu mata kuliah lain');

        foreach ([['UJX101', '9'], ['TIDAKADA', '1'], ['UJX101', 'dua'], ['UJX101']] as $argumen) {
            [$kode] = $this->spark(['soal:setujui', ...$argumen, '--oleh', (string) $d['superadmin']['nomor_induk']]);
            $this->assertSame(1, $kode, implode(' ', $argumen));
        }
        [$kode] = $this->spark(['soal:setujui', 'UJX101', '1']);
        $this->assertSame(1, $kode, 'tanpa --oleh');

        $this->assertSame($awal, [$this->statusSoal(), $this->hitung('SELECT COUNT(*) FROM meeting_problems'), $this->hitung('SELECT COUNT(*) FROM audit_log')]);
    }

    public function testPersetujuanLewatHalamanWebTetapSamaDanTanpaTandaCli(): void
    {
        $d    = $this->duniaTinjau();
        $http = $this->sebagai($d['pengampuX']);
        $r    = $http->post('/soal/' . $d['b']['soal'] . '/setujui', ['csrf' => $this->csrf($http), 'versi_id' => (string) $d['b']['versi']]);
        $this->assertSame(303, $r->status);
        $this->assertStringEndsWith('?pesan=disetujui', (string) $r->header('Location'));
        $this->assertSame('APPROVED', $this->statusSoal()['soal-b']);
        $a = $this->row("SELECT actor_id, detail FROM audit_log WHERE action = 'soal.disetujui'");
        $this->assertSame($d['pengampuX']['id'], $a['actor_id']);
        $this->assertArrayNotHasKey('lewat', json_decode($a['detail'], true));
    }
}
