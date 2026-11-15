<?php

declare(strict_types=1);

namespace Tests\Akademik;

use Tests\Support\KodeCase;

/**
 * Alat tahun ajaran baru (SPEC.md §5): ringkasan dulu, lalu menyalin kelas dan
 * pengajar tanpa pendaftaran, dan mengarsipkan tahun lama.
 */
final class TahunBaruTest extends KodeCase
{
    private const ISIAN = ['label' => '2026/2027 Genap', 'starts_on' => '2027-02-01', 'ends_on' => '2027-07-31'];

    public function testRingkasanMenyebutJumlahDanTidakMenyimpanApaPun(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['superadmin']);
        $csrf = $this->csrf($http);
        $arsip = $this->kelas($d['matkulX'], $d['tahun'], 'XARSIP');
        $this->db->query("UPDATE classes SET archived_at = UTC_TIMESTAMP() WHERE id = {$arsip}");

        $form = $http->get('/tahun-ajaran/baru');
        $this->assertSame(200, $form->status);
        $this->assertStringContainsString('2026/2027 Ganjil', $form->body, 'tahun sumber disebut');

        $r = $http->post('/tahun-ajaran/baru', ['csrf' => $csrf] + self::ISIAN);
        $this->assertSame(200, $r->status, $r->body);
        $this->assertStringContainsString('belum ada yang disimpan', $r->body);
        $this->assertStringContainsString('3 kelas dan 2 penugasan pengajar akan disalin', $r->body);
        $this->assertStringContainsString('1 kelas yang sudah diarsipkan tidak disalin', $r->body);
        $this->assertStringContainsString('3 mahasiswa masih berstatus aktif di tahun ajaran 2026/2027 Ganjil', $r->body);
        $this->assertMatchesRegularExpression('/UJX101.*?<td>2<\/td>\s*<td>2<\/td>/s', $r->body);
        $this->assertMatchesRegularExpression('/UJY201.*?<td>1<\/td>\s*<td>0<\/td>/s', $r->body);
        $this->assertStringContainsString('Buat 2026/2027 Genap dan arsipkan 2026/2027 Ganjil', $r->body);

        $this->assertSame(1, $this->hitung('SELECT COUNT(*) FROM academic_years'));
        $this->assertSame(4, $this->hitung('SELECT COUNT(*) FROM classes'));
        $this->assertSame(0, $this->hitung('SELECT COUNT(*) FROM audit_log'));
    }

    public function testMenjalankanMenyalinKelasDanPengajarTanpaMahasiswa(): void
    {
        $d     = $this->dunia();
        $http  = $this->sebagai($d['superadmin']);
        $csrf  = $this->csrf($http);
        $this->db->query("UPDATE classes SET capacity = 40 WHERE id = {$d['kelasXA']}");
        $arsip = $this->kelas($d['matkulX'], $d['tahun'], 'XARSIP');
        $this->db->query("UPDATE classes SET archived_at = UTC_TIMESTAMP() WHERE id = {$arsip}");

        $r = $http->post('/tahun-ajaran/baru/jalankan', ['csrf' => $csrf] + self::ISIAN);
        $this->assertSame(303, $r->status, $r->body);
        $this->assertStringEndsWith('/tahun-ajaran?pesan=tahun-baru&n=3', (string) $r->header('Location'));

        $baru = $this->row("SELECT * FROM academic_years WHERE label = '2026/2027 Genap'");
        $this->assertSame('active', $baru['status']);
        $this->assertSame('archived', $this->row("SELECT status FROM academic_years WHERE id = {$d['tahun']}")['status']);
        $this->assertSame(1, $this->hitung("SELECT COUNT(*) FROM academic_years WHERE status = 'active'"));

        $salinan = $this->db->query("SELECT c.code, k.name, k.capacity, k.archived_at FROM classes k JOIN courses c ON c.id = k.course_id WHERE k.academic_year_id = {$baru['id']} ORDER BY c.code, k.name")->fetch_all(MYSQLI_ASSOC);
        $this->assertSame([
            ['code' => 'UJX101', 'name' => 'XA', 'capacity' => '40', 'archived_at' => null],
            ['code' => 'UJX101', 'name' => 'XB', 'capacity' => null, 'archived_at' => null],
            ['code' => 'UJY201', 'name' => 'YA', 'capacity' => null, 'archived_at' => null],
        ], $salinan);

        $tugas = $this->db->query("SELECT k.name, ct.user_id, ct.assigned_by FROM class_teachers ct JOIN classes k ON k.id = ct.class_id WHERE k.academic_year_id = {$baru['id']} ORDER BY k.name")->fetch_all(MYSQLI_ASSOC);
        $this->assertSame([
            ['name' => 'XA', 'user_id' => $d['pengajarA']['id'], 'assigned_by' => $d['superadmin']['id']],
            ['name' => 'XB', 'user_id' => $d['pengajarB']['id'], 'assigned_by' => $d['superadmin']['id']],
        ], $tugas);

        // Pendaftaran tidak disalin dan yang lama tidak disentuh.
        $this->assertSame(0, $this->hitung("SELECT COUNT(*) FROM enrollments WHERE academic_year_id = {$baru['id']}"));
        $this->assertSame(3, $this->hitung("SELECT COUNT(*) FROM enrollments WHERE academic_year_id = {$d['tahun']} AND status = 'AKTIF'"));
        // Kelas lama tetap ada.
        $this->assertSame(4, $this->hitung("SELECT COUNT(*) FROM classes WHERE academic_year_id = {$d['tahun']}"));

        $detail = json_decode((string) $this->row("SELECT detail FROM audit_log WHERE action = 'tahun.baru'")['detail'], true);
        $this->assertSame([3, 2, $d['tahun']], [$detail['kelas_disalin'], $detail['penugasan_disalin'], $detail['dari_id']]);

        // Pengajar A kini melihat kelas XA yang baru (kosong) di beranda, dan kelas lamanya tetap bisa dibaca.
        $a       = $this->sebagai($d['pengajarA']);
        $beranda = $a->get('/beranda')->body;
        $this->assertStringContainsString('2026/2027 Genap', $beranda);
        $this->assertStringContainsString('data-ringkas="mahasiswa">0<', $beranda);
        $this->assertSame(200, $a->get('/kelas/' . $d['kelasXA'])->status);
        $this->assertStringContainsString('2 kelas disalin', $http->get('/tahun-ajaran?pesan=tahun-baru&n=2')->body);
    }

    public function testIsianSalahAtauTanpaTahunAktifTidakMembuatApaPun(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['superadmin']);
        $csrf = $this->csrf($http);

        // Nama sama dengan tahun yang ada; tanggal terbalik.
        foreach ([['label' => '2026/2027 Ganjil'] + self::ISIAN, ['starts_on' => '2027-08-01'] + self::ISIAN] as $isian) {
            foreach (['/tahun-ajaran/baru', '/tahun-ajaran/baru/jalankan'] as $jalur) {
                $this->assertSame(422, $http->post($jalur, ['csrf' => $csrf] + $isian)->status, $jalur);
            }
        }
        $this->assertSame(1, $this->hitung('SELECT COUNT(*) FROM academic_years'));

        // Tanpa tahun aktif tidak ada yang bisa disalin.
        $this->db->query("UPDATE academic_years SET status = 'archived'");
        $r = $http->post('/tahun-ajaran/baru/jalankan', ['csrf' => $csrf] + self::ISIAN);
        $this->assertSame(422, $r->status);
        $this->assertStringContainsString('Belum ada tahun ajaran aktif untuk disalin', $r->body);
        $this->assertSame(1, $this->hitung('SELECT COUNT(*) FROM academic_years'));
        $this->assertSame(3, $this->hitung('SELECT COUNT(*) FROM classes'));
    }
}
