<?php

declare(strict_types=1);

namespace Tests\Komunikasi;

use Lokandita\Auth\Testing\Fixtures;
use Lokandita\Auth\Testing\Http;
use Tests\Support\KodeCase;

/**
 * Pemberitahuan di dalam aplikasi (SPEC.md §14, KONTRAK.md §17) lewat HTTP sungguhan:
 * tiga jenis saja, dibuat saat mahasiswa memuat halaman, tidak pernah dobel, dan tidak
 * pernah terlihat oleh orang lain. Reset kata sandi melewati auth-tes yang sungguhan.
 */
final class NotifikasiTest extends KodeCase
{
    private static function utc(string $geser): string
    {
        return gmdate('Y-m-d H:i:s', (int) strtotime($geser));
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function baris(string $userId): array
    {
        return $this->db->query("SELECT kind, ref, data, read_at FROM notifications WHERE user_id = '{$userId}' ORDER BY id")->fetch_all(MYSQLI_ASSOC);
    }

    private static function belum(string $isi): ?int
    {
        return preg_match('/data-notif-belum="(\d+)"/', $isi, $m) === 1 ? (int) $m[1] : null;
    }

    public function testPertemuanDibukaDiberitahukanSekaliSaatHalamanDimuat(): void
    {
        $d = $this->dunia();
        $this->terbit($d['pertemuanX1']);
        $a = $this->sebagai($d['mhsA']);

        // Belum ada jendela: tidak ada pemberitahuan, hitungannya nol.
        $r = $a->get('/beranda');
        $this->assertSame([200, 0], [$r->status, self::belum($r->body)]);
        $this->assertSame([], $this->baris($d['mhsA']['id']));

        // Pengajar membuka pertemuan untuk kelas XA. Dalam menit yang sama beranda belum memeriksa lagi…
        $j = $this->jendela($d['pertemuanX1'], $d['kelasXA']);
        $this->assertSame(0, self::belum($a->get('/beranda')->body));
        // …tetapi halaman pemberitahuan selalu memeriksa.
        $r = $a->get('/notifikasi');
        $this->assertSame(200, $r->status);
        $baris = $this->baris($d['mhsA']['id']);
        $this->assertSame([['pertemuan_dibuka', "{$j}:manual"]], array_map(static fn (array $b): array => [$b['kind'], $b['ref']], $baris));
        $this->assertEquals(['pertemuan' => $d['pertemuanX1'], 'nomor' => 1, 'judul' => 'Pertemuan uji', 'matkul' => 'UJX101', 'tutup' => null], json_decode((string) $baris[0]['data'], true));
        $this->assertStringContainsString('UJX101 — Pertemuan 1: Pertemuan uji sudah dibuka untuk kelas Anda.', $r->body);
        $this->assertStringContainsString('href="/latihan/' . $d['pertemuanX1'] . '"', $r->body);
        $this->assertSame(1, self::belum($r->body));
        $this->assertStringContainsString('1 belum dibaca', $r->body);

        // Dimuat berkali-kali, juga sesudah pembatas satu menit dilupakan: tetap satu baris.
        $this->lupakanLaju();
        foreach (['/beranda', '/latihan/' . $d['pertemuanX1'], '/notifikasi', '/beranda'] as $jalur) {
            $this->assertSame(1, self::belum($a->get($jalur)->body), $jalur);
        }
        $this->assertCount(1, $this->baris($d['mhsA']['id']));

        // Ditandai sudah dibaca: hitungan nol, barisnya tetap ada.
        $r = $a->post('/notifikasi/baca', ['csrf' => $this->csrf($a)]);
        $this->assertContains($r->status, [302, 303]);
        $this->assertNotNull($this->baris($d['mhsA']['id'])[0]['read_at']);
        $r = $a->get('/notifikasi');
        $this->assertSame(0, self::belum($r->body));
        $this->assertStringContainsString('sudah dibuka untuk kelas Anda', $r->body);
        $this->assertStringNotContainsString('Tandai semua sudah dibaca', $r->body);

        // Mahasiswa kelas lain (XB tidak dibuka) dan mata kuliah lain tidak mendapat apa-apa; staf tidak punya pemberitahuan.
        foreach (['mhsB', 'mhsY'] as $lain) {
            $r = $this->sebagai($d[$lain])->get('/notifikasi');
            $this->assertSame(200, $r->status);
            $this->assertStringContainsString('Belum ada pemberitahuan.', $r->body);
            $this->assertStringNotContainsString('UJX101', $r->body);
        }
        $this->sebagai($d['pengajarA'])->get('/beranda');
        $this->assertSame(1, $this->hitung('SELECT COUNT(*) FROM notifications'));
    }

    public function testJendelaTerjadwalDanPengingatDuaPuluhEmpatJam(): void
    {
        $d = $this->dunia();
        $this->terbit($d['pertemuanX1']);
        $this->terbit($d['pertemuanX2']);
        $a    = $this->sebagai($d['mhsA']);
        $jenis = fn (): array => array_map(static fn (array $b): string => $b['kind'] . ' ' . $b['ref'], $this->baris($d['mhsA']['id']));

        // Belum waktunya dibuka: tidak ada.
        $buka  = self::utc('+2 hours');
        $tutup = self::utc('+5 days');
        $j1    = $this->jendela($d['pertemuanX1'], $d['kelasXA'], ['override' => 'none', 'open_at' => $buka, 'close_at' => $tutup]);
        $a->get('/notifikasi');
        $this->assertSame([], $jenis());

        // Sudah dibuka, tutup lima hari lagi: hanya "dibuka", dengan waktu tutupnya.
        $buka = self::utc('-1 hour');
        $this->db->query("UPDATE meeting_class_windows SET open_at = '{$buka}' WHERE id = {$j1}");
        $r = $a->get('/notifikasi');
        $this->assertSame(["pertemuan_dibuka {$j1}:{$buka}"], $jenis());
        $this->assertStringContainsString('Ditutup pada ', $r->body);
        $this->assertStringContainsString(' WIB', $r->body);

        // Tutupnya kini sepuluh jam lagi: pengingat 24 jam, sekali.
        $tutup = self::utc('+10 hours');
        $this->db->query("UPDATE meeting_class_windows SET open_at = '" . self::utc('-3 days') . "', close_at = '{$tutup}' WHERE id = {$j1}");
        $this->db->query("DELETE FROM notifications"); // mulai bersih: jadwalnya memang baru
        $a->get('/notifikasi');
        $r = $a->get('/notifikasi');
        $this->assertSame(['pertemuan_dibuka ' . $j1 . ':' . self::tanggalJendela($this, $j1, 'open_at'), "tutup_24jam {$j1}:{$tutup}"], $jenis());
        $this->assertStringContainsString('akan ditutup kurang dari 24 jam lagi', $r->body);

        // Waktu tutup diundur dua jam: jadwal baru, pengingat baru; yang lama tetap ada.
        $tutup2 = self::utc('+12 hours');
        $this->db->query("UPDATE meeting_class_windows SET close_at = '{$tutup2}' WHERE id = {$j1}");
        $a->get('/notifikasi');
        $this->assertContains("tutup_24jam {$j1}:{$tutup2}", $jenis());
        $this->assertCount(3, $jenis());

        // Pertemuan 2: sesi praktikum dua jam. "Dibuka" saja — pengingat 24 jam tidak ada artinya.
        $j2 = $this->jendela($d['pertemuanX2'], $d['kelasXA'], ['override' => 'none', 'open_at' => self::utc('-1 hour'), 'close_at' => self::utc('+1 hour')]);
        $a->get('/notifikasi');
        $this->assertCount(4, $jenis());
        $this->assertSame(1, $this->hitung("SELECT COUNT(*) FROM notifications WHERE ref LIKE '{$j2}:%'"));
        $this->assertSame(0, $this->hitung("SELECT COUNT(*) FROM notifications WHERE kind = 'tutup_24jam' AND ref LIKE '{$j2}:%'"));
    }

    private static function tanggalJendela(self $t, int $id, string $kolom): string
    {
        return (string) $t->row("SELECT {$kolom} FROM meeting_class_windows WHERE id = {$id}")[$kolom];
    }

    public function testYangTidakBolehDikerjakanTidakDiberitahukan(): void
    {
        $d = $this->dunia();
        $a = $this->sebagai($d['mhsA']);
        $periksa = function (string $apa) use ($a, $d): void {
            $this->db->query('DELETE FROM notifications');
            $a->get('/notifikasi');
            $this->assertSame([], $this->baris($d['mhsA']['id']), $apa);
        };

        // Pertemuan belum diterbitkan.
        $j = $this->jendela($d['pertemuanX1'], $d['kelasXA']);
        $periksa('belum terbit');
        // Terbit tetapi ditutup manual, atau sudah lewat waktu tutupnya.
        $this->terbit($d['pertemuanX1']);
        $this->db->query("UPDATE meeting_class_windows SET override = 'closed' WHERE id = {$j}");
        $periksa('ditutup manual');
        $this->db->query("UPDATE meeting_class_windows SET override = 'none', open_at = '2020-01-01 00:00:00', close_at = '2020-01-02 00:00:00' WHERE id = {$j}");
        $periksa('sudah lewat');
        // Terbuka, tetapi pertemuannya dinonaktifkan untuk latihan.
        $this->db->query("UPDATE meeting_class_windows SET override = 'open' WHERE id = {$j}");
        $this->db->query("UPDATE meetings SET enabled = 0 WHERE id = {$d['pertemuanX1']}");
        $periksa('pertemuan nonaktif');
        $this->db->query("UPDATE meetings SET enabled = 1 WHERE id = {$d['pertemuanX1']}");
        // Mahasiswa yang mengundurkan diri atau sudah lulus.
        foreach (['MENGUNDURKAN_DIRI', 'LULUS'] as $status) {
            $this->db->query("UPDATE enrollments SET status = '{$status}' WHERE id = {$d['daftarA']}");
            $periksa($status);
        }
        // Tahun ajaran diarsipkan.
        $this->db->query("UPDATE enrollments SET status = 'AKTIF' WHERE id = {$d['daftarA']}");
        $this->db->query("UPDATE academic_years SET status = 'archived' WHERE id = {$d['tahun']}");
        $periksa('tahun arsip');
        // Pembanding: dengan semuanya kembali normal, pemberitahuannya ada.
        $this->db->query("UPDATE academic_years SET status = 'active' WHERE id = {$d['tahun']}");
        $a->get('/notifikasi');
        $this->assertCount(1, $this->baris($d['mhsA']['id']));
    }

    public function testResetKataSandiOlehPengajarDiberitahukanKepadaMahasiswaItuSaja(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengajarA']);
        $r    = $http->post('/kelas/' . $d['kelasXA'] . '/reset-sandi', ['csrf' => $this->csrf($http), 'user_id' => $d['mhsA']['id'], 'yakin' => '1']);
        $this->assertSame(200, $r->status, $r->body);
        $this->assertSame(1, preg_match('/data-sandi-sementara>([^<]+)</', $r->body, $m));
        $sandi = html_entity_decode($m[1], ENT_QUOTES);

        $baris = $this->baris($d['mhsA']['id']);
        $this->assertCount(1, $baris);
        $audit = $this->row("SELECT id FROM audit_log WHERE action = 'sandi.direset'");
        $this->assertSame(['sandi_direset', (string) $audit['id']], [$baris[0]['kind'], $baris[0]['ref']]);
        $this->assertEquals(['oleh' => $d['pengajarA']['id'], 'kelas' => 'UJX101 XA'], json_decode((string) $baris[0]['data'], true));
        $this->assertStringNotContainsString($sandi, (string) $baris[0]['data'], 'kata sandi sementara tidak pernah masuk pemberitahuan');

        // Mahasiswa masuk dengan kata sandi sementara di LokaAuth sungguhan, menggantinya, lalu membuka LokaKode.
        $masuk = Fixtures::login(new Http((string) getenv('AUTH_TES_URL')), $d['mhsA'], null, $sandi);
        $this->assertArrayHasKey('lokandita_sid', $masuk->cookies);
        $a = new Http((string) getenv('APP_TES_URL'));
        $a->cookies['lokandita_sid'] = $masuk->cookies['lokandita_sid'];
        $this->assertContains($a->get('/beranda')->status, [302, 303, 307], 'sebelum mengganti kata sandi ia belum bisa masuk LokaKode');
        $this->idn->query("UPDATE users SET must_change_password = 0 WHERE id = '{$d['mhsA']['id']}'");
        $r = $a->get('/beranda');
        $this->assertSame([200, 1], [$r->status, self::belum($r->body)]);
        $r = $a->get('/notifikasi');
        $this->assertStringContainsString('Kata sandi Anda direset oleh Pengajar Aminah (kelas UJX101 XA).', $r->body);
        $this->assertStringNotContainsString($sandi, $r->body);

        // Mahasiswa lain tidak melihatnya, dan "tandai dibaca" miliknya tidak menyentuh baris orang lain.
        $b = $this->sebagai($d['mhsB']);
        $r = $b->get('/notifikasi');
        $this->assertStringNotContainsString('Kata sandi Anda direset', $r->body);
        $this->assertStringNotContainsString('Pengajar Aminah', $r->body);
        $b->post('/notifikasi/baca', ['csrf' => $this->csrf($b)]);
        $this->assertNull($this->baris($d['mhsA']['id'])[0]['read_at']);

        // Reset yang gagal (sasaran bukan mahasiswa kelas itu) tidak membuat pemberitahuan.
        $http->post('/kelas/' . $d['kelasXA'] . '/reset-sandi', ['csrf' => $this->csrf($http), 'user_id' => $d['mhsB']['id'], 'yakin' => '1']);
        $this->assertSame(1, $this->hitung('SELECT COUNT(*) FROM notifications'));
    }

    public function testTeksPemberitahuanDiEscapeDanTanpaCsrfDitolak(): void
    {
        $d = $this->dunia();
        $this->db->query("UPDATE meetings SET title = '<script>alert(1)</script>' WHERE id = {$d['pertemuanX1']}");
        $this->terbit($d['pertemuanX1']);
        $this->jendela($d['pertemuanX1'], $d['kelasXA']);
        $a = $this->sebagai($d['mhsA']);
        $r = $a->get('/notifikasi');
        $this->assertStringNotContainsString('<script>alert(1)</script>', $r->body);
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $r->body);
        // Tanpa token CSRF tidak ada yang ditandai.
        $this->assertSame(403, $a->post('/notifikasi/baca', [])->status);
        $this->assertNull($this->baris($d['mhsA']['id'])[0]['read_at']);
    }
}
