<?php

declare(strict_types=1);

namespace Tests\Akademik;

use Tests\Support\KodeCase;
use Tests\Support\SoalBantu;

/**
 * `spark benih:kurikulum` (SPEC.md §17): membuat yang belum ada, tidak menyentuh
 * yang sudah ada dan berbeda, aman diulang, dan hanya atas nama superadmin.
 */
final class BenihKurikulumTest extends KodeCase
{
    use SoalBantu;

    private const PENGAMPU = '197805192024211003';

    /**
     * @return array{superadmin: array<string, mixed>, pengampu: array<string, mixed>, tahun: int}
     */
    private function awal(): array
    {
        // identity_test tidak dikosongkan di antara tes: pengampu benih dibuat sekali, lalu dipakai lagi.
        $this->idn->query("UPDATE users SET status = 'active' WHERE nomor_induk = '" . self::PENGAMPU . "'");
        $pengampu = $this->idn->query("SELECT id, nomor_induk FROM users WHERE nomor_induk = '" . self::PENGAMPU . "'")->fetch_assoc();

        return [
            'superadmin' => $this->user('superadmin', 'Admin Uji'),
            'pengampu'   => $pengampu ?? $this->user('dosen', 'Pengampu Benih', ['nomor_induk' => self::PENGAMPU]),
            'tahun'      => $this->tahun('2026/2027'),
        ];
    }

    /**
     * @return array<string, list<string>> "KODE nomor" => konsep tambahan, terurut
     */
    private function konsepPertemuan(): array
    {
        $hasil = [];
        foreach ($this->db->query('
            SELECT CONCAT(c.code, " ", m.number) AS kunci, mc.concept_code
            FROM meetings m JOIN courses c ON c.id = m.course_id LEFT JOIN meeting_concepts mc ON mc.meeting_id = m.id
            ORDER BY mc.concept_code')->fetch_all(MYSQLI_ASSOC) as $b) {
            $hasil[$b['kunci']] ??= [];
            if ($b['concept_code'] !== null) {
                $hasil[$b['kunci']][] = $b['concept_code'];
            }
        }

        return $hasil;
    }

    public function testPratinjauTidakMenulisLaluJalankanMembuatSemuanyaDanUlanganTidakMengubah(): void
    {
        $d = $this->awal();
        $oleh = (string) $d['superadmin']['nomor_induk'];

        [$kode, $keluar] = $this->spark(['benih:kurikulum', '--oleh', $oleh]);
        $this->assertSame(0, $kode, $keluar);
        $this->assertStringContainsString('PRATINJAU: tidak ada yang ditulis', $keluar);
        // 3 mata kuliah + 48 pertemuan + 9 kelas dibuat; konsep dasar PBO301 diisi.
        $this->assertStringContainsString('ringkasan: 60 dibuat, 1 diubah, 0 beda', $keluar);
        $this->assertSame(0, $this->hitung('SELECT COUNT(*) FROM courses'));
        $this->assertSame(0, $this->hitung('SELECT COUNT(*) FROM audit_log'));

        [$kode, $keluar] = $this->spark(['benih:kurikulum', '--oleh', $oleh, '--jalankan']);
        $this->assertSame(0, $kode, $keluar);
        $this->assertStringContainsString('DITULIS.', $keluar);
        $this->assertStringContainsString('ringkasan: 60 dibuat, 1 diubah, 0 beda', $keluar);

        // Tiga mata kuliah milik pengampu itu, 16 pertemuan masing-masing dalam bahasanya.
        $matkul = $this->db->query('
            SELECT c.code, c.name, c.owner_id, COUNT(m.id) AS n, MIN(m.language) AS bahasa, COUNT(DISTINCT m.language) AS ragam
            FROM courses c JOIN meetings m ON m.course_id = c.id GROUP BY c.id ORDER BY c.code')->fetch_all(MYSQLI_ASSOC);
        $this->assertSame([
            ['code' => 'DDP101', 'name' => 'Dasar-Dasar Pemrograman', 'owner_id' => $d['pengampu']['id'], 'n' => '16', 'bahasa' => 'cpp', 'ragam' => '1'],
            ['code' => 'PBO301', 'name' => 'Pemrograman Berorientasi Obyek', 'owner_id' => $d['pengampu']['id'], 'n' => '16', 'bahasa' => 'cpp', 'ragam' => '1'],
            ['code' => 'TS-PY', 'name' => 'Pemrograman untuk Teknik Sipil', 'owner_id' => $d['pengampu']['id'], 'n' => '16', 'bahasa' => 'python', 'ragam' => '1'],
        ], $matkul);

        // Kelas di tahun aktif: DDP101 A–D, PBO301 A–D, TS-PY A.
        $kelas = array_column($this->db->query("
            SELECT CONCAT(c.code, ' ', k.name) AS k FROM classes k JOIN courses c ON c.id = k.course_id
            WHERE k.academic_year_id = {$d['tahun']} AND k.archived_at IS NULL ORDER BY 1")->fetch_all(MYSQLI_ASSOC), 'k');
        $this->assertSame(['DDP101 A', 'DDP101 B', 'DDP101 C', 'DDP101 D', 'PBO301 A', 'PBO301 B', 'PBO301 C', 'PBO301 D', 'TS-PY A'], $kelas);

        // UTS, UAS dan yang tidak bisa dinilai otomatis ada tetapi dimatikan untuk latihan.
        $mati = array_column($this->db->query('
            SELECT CONCAT(c.code, " ", m.number) AS k FROM meetings m JOIN courses c ON c.id = m.course_id
            WHERE m.enabled = 0 ORDER BY c.code, m.number')->fetch_all(MYSQLI_ASSOC), 'k');
        $this->assertSame(['DDP101 8', 'DDP101 15', 'DDP101 16', 'PBO301 8', 'PBO301 15', 'PBO301 16',
            'TS-PY 7', 'TS-PY 11', 'TS-PY 12', 'TS-PY 13', 'TS-PY 14', 'TS-PY 15', 'TS-PY 16'], $mati);
        $this->assertSame(0, $this->hitung('SELECT COUNT(*) FROM meetings WHERE published_at IS NOT NULL'));

        // Beberapa baris peta konsep, dibaca dari docs/PETA-KONSEP.md, bukan dari benih.
        $konsep = $this->konsepPertemuan();
        $this->assertSame(['ARITMETIKA', 'IO_DASAR', 'VARIABEL_TIPE'], $konsep['DDP101 1']);
        $this->assertSame(['BREAK_CONTINUE', 'NESTED_LOOP', 'PERULANGAN'], $konsep['DDP101 5']);
        $this->assertSame([], $konsep['DDP101 8']);
        $this->assertSame(['SMART_POINTER', 'STL_ALGORITMA', 'STL_CONTAINER'], $konsep['PBO301 13']);
        $this->assertSame(['FUNGSI', 'PY_DICT_SET', 'PY_LIST', 'STRING'], $konsep['TS-PY 5']);
        $this->assertSame(['PY_TKINTER'], $konsep['TS-PY 11']);

        // PBO301 berangkat dari konsep DDP101; yang diajarkannya sendiri tidak termasuk.
        $dasar = array_column($this->db->query("
            SELECT b.concept_code FROM course_base_concepts b JOIN courses c ON c.id = b.course_id WHERE c.code = 'PBO301'")->fetch_all(MYSQLI_ASSOC), 'concept_code');
        $this->assertCount(21, $dasar);
        $this->assertContains('PERCABANGAN', $dasar);
        $this->assertNotContains('CLASS_OBJECT', $dasar);
        $this->assertSame(21, $this->hitung('SELECT COUNT(*) FROM course_base_concepts'));

        // Setiap perubahan tercatat atas nama superadmin itu, bertanda lewat benih.
        $audit = $this->db->query('SELECT action, actor_id, detail FROM audit_log')->fetch_all(MYSQLI_ASSOC);
        $this->assertCount(61, $audit);
        foreach ($audit as $a) {
            $this->assertSame([$d['superadmin']['id'], 'benih'], [$a['actor_id'], json_decode($a['detail'], true)['lewat']]);
        }

        // Diulang: tidak ada yang berubah dan tidak ada audit baru.
        [$kode, $keluar] = $this->spark(['benih:kurikulum', '--oleh', $oleh, '--jalankan']);
        $this->assertSame(0, $kode, $keluar);
        $this->assertStringContainsString('ringkasan: 0 dibuat, 0 diubah, 0 beda (tidak disentuh), 63 sudah sama', $keluar);
        $this->assertSame(61, $this->hitung('SELECT COUNT(*) FROM audit_log'));
    }

    public function testYangSudahAdaHanyaDilengkapiDanPerbedaanDilaporkanTanpaDiubah(): void
    {
        $d = $this->awal();
        // Keadaan seperti produksi: DDP101 sudah ada dengan beberapa pertemuan buatan tangan.
        $ddp = $this->matkul('DDP101', 'Dasar-Dasar Pemrograman', $d['pengampu']);
        $p1  = $this->pertemuan($ddp, 1, 'cpp', ['IO_DASAR', 'VARIABEL_TIPE', 'ARITMETIKA'], 'IO_DASAR, VARIABEL_TIPE, ARITMETIKA');
        $p4  = $this->pertemuan($ddp, 4, 'cpp', ['PERCABANGAN', 'STRING'], 'Struktur Kontrol — Percabangan');
        $p9  = $this->pertemuan($ddp, 9, 'cpp', [], 'Array 1D');
        $p15 = $this->pertemuan($ddp, 15, 'c', [], 'C++ di Platform Lain, Tools');
        $lama = $this->kelas($ddp, $d['tahun'], '7A');
        $this->soalBaris($p1, 'sudah-ada');

        [$kode, $keluar] = $this->spark(['benih:kurikulum', '--oleh', (string) $d['superadmin']['nomor_induk'], '--jalankan']);
        $this->assertSame(0, $kode, $keluar);

        // Judul disamakan; konsep yang sudah benar tidak disentuh.
        $this->assertStringContainsString('JUDUL  DDP101 pertemuan 1: "IO_DASAR, VARIABEL_TIPE, ARITMETIKA" → "Pengenalan Pemrograman dan C++"', $keluar);
        $this->assertSame('Pengenalan Pemrograman dan C++', $this->row("SELECT title FROM meetings WHERE id = {$p1}")['title']);
        $this->assertSame(['ARITMETIKA', 'IO_DASAR', 'VARIABEL_TIPE'], $this->konsepPertemuan()['DDP101 1']);
        $this->assertSame(1, $this->hitung("SELECT COUNT(*) FROM problems WHERE meeting_id = {$p1}"));

        // Konsep yang berbeda dari peta dilaporkan dan dibiarkan.
        $this->assertStringContainsString('BEDA   DDP101 pertemuan 4 konsep: tersimpan PERCABANGAN, STRING, benih PERCABANGAN; tidak diubah', $keluar);
        $this->assertSame(['PERCABANGAN', 'STRING'], $this->konsepPertemuan()['DDP101 4']);

        // Pertemuan tanpa konsep diisi dari peta.
        $this->assertStringContainsString('ISI    DDP101 pertemuan 9 konsep: ARRAY_1D', $keluar);
        $this->assertSame(['ARRAY_1D'], $this->konsepPertemuan()['DDP101 9']);

        // Bahasa dan sakelar latihan yang berbeda dilaporkan dan dibiarkan.
        $this->assertStringContainsString('BEDA   DDP101 pertemuan 15: bahasanya c, benih cpp; tidak diubah', $keluar);
        $this->assertStringContainsString('BEDA   DDP101 pertemuan 15: dipakai untuk latihan, benih mematikannya; tidak diubah', $keluar);
        $this->assertSame(['language' => 'c', 'enabled' => '1'], $this->row("SELECT language, enabled FROM meetings WHERE id = {$p15}"));

        // Kelas lama tetap ada di samping A–D; tidak ada mata kuliah atau pertemuan ganda.
        $this->assertSame(5, $this->hitung("SELECT COUNT(*) FROM classes WHERE course_id = {$ddp}"));
        $this->assertNull($this->row("SELECT archived_at FROM classes WHERE id = {$lama}")['archived_at']);
        $this->assertSame(3, $this->hitung('SELECT COUNT(*) FROM courses'));
        $this->assertSame(16, $this->hitung("SELECT COUNT(*) FROM meetings WHERE course_id = {$ddp}"));
        $this->assertSame($p4, (int) $this->row("SELECT id FROM meetings WHERE course_id = {$ddp} AND number = 4")['id']);
        $this->assertSame($p9, (int) $this->row("SELECT id FROM meetings WHERE course_id = {$ddp} AND number = 9")['id']);
    }

    public function testMataKuliahMilikDosenLainDilaporkanDanTidakDiambilAlih(): void
    {
        $d    = $this->awal();
        $lain = $this->user('dosen', 'Dosen Lain');
        $ddp  = $this->matkul('DDP101', 'Dasar-Dasar Pemrograman', $lain);

        [$kode, $keluar] = $this->spark(['benih:kurikulum', '--oleh', (string) $d['superadmin']['nomor_induk'], '--jalankan']);
        $this->assertSame(0, $kode, $keluar);
        $this->assertStringContainsString('BEDA   DDP101 mata kuliah: pengampunya bukan ' . self::PENGAMPU . '; tidak diubah', $keluar);
        $this->assertSame($lain['id'], $this->row("SELECT owner_id FROM courses WHERE id = {$ddp}")['owner_id']);
    }

    public function testDitolakTanpaSuperadminAktifTanpaPengampuAtauTanpaTahunAktif(): void
    {
        $d = $this->awal();
        $mhs    = $this->user('mahasiswa', 'Mahasiswa Uji');
        $mati   = $this->user('superadmin', 'Admin Nonaktif', ['status' => 'disabled']);

        foreach ([(string) $d['pengampu']['nomor_induk'], (string) $mhs['nomor_induk'], (string) $mati['nomor_induk'], 'tidak-ada'] as $oleh) {
            [$kode, $keluar] = $this->spark(['benih:kurikulum', '--oleh', $oleh, '--jalankan']);
            $this->assertSame(1, $kode, $oleh);
            $this->assertStringContainsString('bukan superadmin yang aktif. Tidak ada yang diubah.', $keluar);
        }
        [$kode] = $this->spark(['benih:kurikulum', '--jalankan']);
        $this->assertSame(1, $kode);

        // Tanpa tahun ajaran aktif.
        $this->db->query("UPDATE academic_years SET status = 'archived'");
        [$kode, $keluar] = $this->spark(['benih:kurikulum', '--oleh', (string) $d['superadmin']['nomor_induk'], '--jalankan']);
        $this->assertSame(1, $kode);
        $this->assertStringContainsString('belum ada tahun ajaran yang aktif', $keluar);

        // Pengampu benih bukan dosen aktif.
        $this->db->query("UPDATE academic_years SET status = 'active'");
        $this->idn->query("UPDATE users SET status = 'disabled' WHERE nomor_induk = '" . self::PENGAMPU . "'");
        [$kode, $keluar] = $this->spark(['benih:kurikulum', '--oleh', (string) $d['superadmin']['nomor_induk'], '--jalankan']);
        $this->assertSame(1, $kode);
        $this->assertStringContainsString('bukan dosen yang aktif di LokaAuth', $keluar);

        $this->assertSame(0, $this->hitung('SELECT COUNT(*) FROM courses'));
        $this->assertSame(0, $this->hitung('SELECT COUNT(*) FROM audit_log'));
    }
}
