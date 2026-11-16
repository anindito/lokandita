<?php

declare(strict_types=1);

namespace Tests\Soal;

use Tests\Support\KodeCase;
use Tests\Support\SoalBantu;

/**
 * Impor soal dari ujung ke ujung (SPEC.md §8.3): `spark soal:impor` sungguhan,
 * MySQL, Redis, judge worker dan kontainer judge sungguhan. Tidak ada yang ditiru.
 */
final class ImporTest extends KodeCase
{
    use SoalBantu;

    public function testGerbangBerkasContohDiimporDanSoalSalahDitolakDenganNomorBaris(): void
    {
        $d    = $this->dunia();
        $json = $this->berkasImpor([
            $this->soalContoh('dua-bilangan'),                                              // cout << x << " " << y, double
            $this->soalContoh('terbesar', ['reference_solution' => self::REF_IF]),          // if: belum diizinkan
            $this->soalContoh('jumlah', ['reference_solution' => self::REF_JUMLAH, 'difficulty' => 'mudah_sekali']),
        ]);
        [$kode, $keluar] = $this->impor($json, $d['superadmin'], 'pertemuan-1.json');

        $this->assertSame(1, $kode, $keluar); // ada yang ditolak
        $this->assertStringContainsString('ringkasan: 2 diterima, 1 ditolak, 0 tidak berubah, dari 3 soal', $keluar);

        // Gerbang: literal string di antara dua double diterima di pertemuan tanpa STRING.
        $this->assertMatchesRegularExpression('/DITERIMA\s+1\. dua-bilangan \(versi 1, menunggu tinjauan\)/', $keluar);
        $v = $this->row("SELECT v.* FROM problem_versions v JOIN problems p ON p.id = v.problem_id WHERE p.slug = 'dua-bilangan'");
        $this->assertSame(['PENDING_REVIEW', 'cpp', 'MUDAH', '1'], [$v['status'], $v['language'], $v['difficulty'], (string) $v['version']]);
        $konsep = json_decode($v['concepts'], true);
        sort($konsep);
        $this->assertSame(['IO_DASAR', 'VARIABEL_TIPE'], $konsep);
        $this->assertSame($d['superadmin']['id'], $v['created_by']);

        // Soal yang salah: konstruksi, baris dan kolom di sumbernya, dan baris JSON-nya.
        $barisJson = $this->barisDari($json, '"reference_solution"', 2);
        $this->assertMatchesRegularExpression('/DITOLAK\s+2\. terbesar\s+\[baris JSON \d+\]/', $keluar);
        $this->assertStringContainsString("solusi referensi, baris 6, kolom 5, baris JSON {$barisJson}: Solusi referensi memakai konsep PERCABANGAN, yang belum diizinkan di pertemuan ini: «if».", $keluar);
        $this->assertSame(0, $this->hitung("SELECT COUNT(*) FROM problems WHERE slug = 'terbesar'"));

        // Tidak ada yang sampai ke mahasiswa tanpa persetujuan.
        $this->assertSame(0, $this->hitung('SELECT COUNT(*) FROM meeting_problems'));
        $this->assertSame(2, $this->hitung("SELECT COUNT(*) FROM problem_versions WHERE status = 'PENDING_REVIEW' AND import_id IS NOT NULL"));

        // Audit: siapa, nama berkas, jumlah.
        $audit  = $this->row("SELECT * FROM audit_log WHERE action = 'soal.impor'");
        $detail = json_decode($audit['detail'], true);
        $this->assertSame($d['superadmin']['id'], $audit['actor_id']);
        $this->assertSame(['pertemuan-1.json', 'cli', 3, 2, 1, 0], [$detail['file_name'], $detail['source'], $detail['total'], $detail['accepted'], $detail['rejected'], $detail['unchanged']]);
        $this->assertSame(1, $this->hitung("SELECT COUNT(*) FROM audit_log WHERE action LIKE 'soal.impor%'"));
        $this->assertSame('SELESAI', $this->row("SELECT status FROM imports WHERE source = 'cli'")['status']);

        // Idempoten menurut slug: impor ulang berkas yang sama tidak membuat versi baru.
        $baik = $this->berkasImpor([$this->soalContoh('dua-bilangan'), $this->soalContoh('jumlah', ['reference_solution' => self::REF_JUMLAH, 'difficulty' => 'mudah_sekali'])]);
        [$kode, $keluar] = $this->impor($baik, $d['superadmin']);
        $this->assertSame(0, $kode, $keluar);
        $this->assertStringContainsString('ringkasan: 0 diterima, 0 ditolak, 2 tidak berubah, dari 2 soal', $keluar);
        $this->assertSame(2, $this->hitung('SELECT COUNT(*) FROM problem_versions WHERE import_id IS NOT NULL'));

        // Isi yang berubah membuat versi baru untuk soal itu saja.
        $soal = $this->soalContoh('dua-bilangan');
        $soal['translations']['id']['statement'] = 'Pernyataan yang diperbaiki.';
        [$kode, $keluar] = $this->impor($this->berkasImpor([$soal, $this->soalContoh('jumlah', ['reference_solution' => self::REF_JUMLAH, 'difficulty' => 'mudah_sekali'])]), $d['superadmin']);
        $this->assertSame(0, $kode, $keluar);
        $this->assertStringContainsString('ringkasan: 1 diterima, 0 ditolak, 1 tidak berubah, dari 2 soal', $keluar);
        $this->assertMatchesRegularExpression('/DITERIMA\s+1\. dua-bilangan \(versi 2, menunggu tinjauan\)/', $keluar);
        $versi = array_column($this->db->query("SELECT v.version FROM problem_versions v JOIN problems p ON p.id = v.problem_id WHERE p.slug = 'dua-bilangan' ORDER BY v.version")->fetch_all(), 0);
        $this->assertSame(['1', '2'], array_map('strval', $versi));

        // Ekspor lalu impor lagi: tidak ada yang berubah (SPEC.md §8.3).
        [$kode, $ekspor, $galat] = $this->spark(['soal:ekspor', 'UJX101', '1']);
        $this->assertSame(0, $kode, $galat);
        $data = json_decode($ekspor, true, 16, JSON_THROW_ON_ERROR);
        $this->assertSame(['UJX101', 1, 'cpp'], [$data['course'], $data['meeting'], $data['language']]);
        // jumlah-dua adalah soal bawaan dunia uji (disisipkan tanpa judge); ia ikut diekspor.
        $this->assertSame(['jumlah-dua', 'dua-bilangan', 'jumlah'], array_column($data['problems'], 'slug'));
        $this->assertSame('Pernyataan yang diperbaiki.', $data['problems'][1]['translations']['id']['statement']);
        $data['problems'] = array_slice($data['problems'], 1);
        $ekspor = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        [$kode, $keluar] = $this->impor($ekspor, $d['superadmin']);
        $this->assertSame(0, $kode, $keluar);
        $this->assertStringContainsString('ringkasan: 0 diterima, 0 ditolak, 2 tidak berubah, dari 2 soal', $keluar);
    }

    public function testKonsepKumulatifDanKonsepDasarIkutMenentukanYangDiizinkan(): void
    {
        $d = $this->dunia();
        // PERCABANGAN diizinkan sejak pertemuan 1 lewat konsep dasar mata kuliah X: REF_IF sekarang diterima di pertemuan 2.
        $this->db->query("INSERT INTO course_base_concepts VALUES ({$d['matkulX']}, 'PERCABANGAN')");
        $json = $this->berkasImpor([$this->soalContoh('terbesar', ['reference_solution' => self::REF_IF])], ['meeting' => 2]);
        [$kode, $keluar] = $this->impor($json, $d['superadmin']);
        $this->assertSame(0, $kode, $keluar);
        $v = $this->row("SELECT v.concepts, p.meeting_id FROM problem_versions v JOIN problems p ON p.id = v.problem_id WHERE p.slug = 'terbesar'");
        $this->assertContains('PERCABANGAN', json_decode($v['concepts'], true));
        $this->assertSame((string) $d['pertemuanX2'], (string) $v['meeting_id']);
    }

    public function testBerkasYangSalahDitolakSeluruhnyaDenganPesanYangMenyebutPerbaikannya(): void
    {
        $d    = $this->dunia();
        $soal = [$this->soalContoh('dua-bilangan')];
        foreach ([
            [$this->berkasImpor($soal, ['meeting' => '1 — Pengenalan']), 'meeting: meeting harus bilangan bulat 1, tertulis "1 — Pengenalan". Tulis angkanya saja'],
            [$this->berkasImpor($soal, ['course' => 'TIDAK-ADA']), 'Tidak ada mata kuliah berkode «TIDAK-ADA». Kode yang ada: UJX101, UJY201.'],
            [$this->berkasImpor($soal, ['course' => 'Uji Pemrograman X']), 'course harus kode mata kuliah, misalnya "DDP101" (bukan namanya)'],
            [$this->berkasImpor($soal, ['meeting' => 9]), 'Mata kuliah UJX101 belum punya pertemuan 9.'],
            [$this->berkasImpor($soal, ['language' => 'python']), 'Pertemuan 1 memakai bahasa «cpp», tetapi berkas ini menyebut «python».'],
            [$this->berkasImpor($soal, ['languge' => 'cpp']), 'Kunci «languge» tidak dikenal. Maksud Anda «language»?'],
            ['{"course": "UJX101", ', 'Berkas bukan JSON yang sah'],
        ] as [$json, $pesan]) {
            [$kode, $keluar] = $this->impor($json, $d['superadmin']);
            $this->assertSame(1, $kode, $pesan);
            $this->assertStringContainsString('BERKAS DITOLAK', $keluar);
            $this->assertStringContainsString($pesan, $keluar);
        }
        $this->assertSame(0, $this->hitung("SELECT COUNT(*) FROM problems WHERE slug <> 'jumlah-dua'") + $this->hitung("SELECT COUNT(*) FROM judge_jobs WHERE kind = 'VALIDATE'") + $this->hitung('SELECT COUNT(*) FROM import_items'));
        $this->assertSame(7, $this->hitung("SELECT COUNT(*) FROM audit_log WHERE action = 'soal.impor.ditolak'"));
        $this->assertSame(7, $this->hitung("SELECT COUNT(*) FROM imports WHERE status = 'DITOLAK'"));
    }

    public function testSoalBerbentukSalahDitolakTanpaMenyentuhJudgeDanMenyebutBarisJson(): void
    {
        $d    = $this->dunia();
        $json = $this->berkasImpor([
            $this->soalContoh('tingkat-salah', ['difficulty' => 'Mudah Sekali']),
            $this->soalContoh('tes-kurang', ['hidden_count' => 5]),
            $this->soalContoh('tanpa-indonesia', ['translations' => ['en' => ['title' => 'Two', 'statement' => 'Text']]]),
            $this->soalContoh('kembar'),
            $this->soalContoh('kembar'),
        ]);
        // "kembar" yang pertama sah dan akan divalidasi judge; empat lainnya ditolak di sini.
        [$kode, $keluar] = $this->impor($json, $d['superadmin']);
        $this->assertSame(1, $kode, $keluar);
        $this->assertStringContainsString('ringkasan: 1 diterima, 4 ditolak, 0 tidak berubah, dari 5 soal', $keluar);
        $this->assertStringContainsString('difficulty, baris JSON ' . $this->barisDari($json, '"difficulty"', 1) . ': difficulty harus ditulis "mudah_sekali", tertulis "Mudah Sekali".', $keluar);
        $this->assertStringContainsString('hidden_count, baris JSON ' . $this->barisDari($json, '"hidden_count"', 2) . ': hidden_count harus bilangan bulat 10 sampai 50 (tanpa tanda kutip), tertulis 5.', $keluar);
        $this->assertStringContainsString('translations, baris JSON ' . $this->barisDari($json, '"translations"', 3) . ': translations harus obyek dan wajib memuat bahasa Indonesia', $keluar);
        $this->assertStringContainsString('slug «kembar» sudah dipakai soal nomor 4 di berkas ini.', $keluar);
        $this->assertSame(1, $this->hitung("SELECT COUNT(*) FROM judge_jobs WHERE kind = 'VALIDATE'"));
        $this->assertSame(['kembar'], array_column($this->db->query("SELECT slug FROM problems WHERE slug <> 'jumlah-dua'")->fetch_all(), 0));
    }

    public function testGeneratorYangTidakDeterministikDitolakDenganPenjelasan(): void
    {
        $d    = $this->dunia();
        $json = $this->berkasImpor([$this->soalContoh('acak', ['generator' => "import random\nprint(random.randint(1, 10**9), random.randint(1, 10**9))\n"])]);
        [$kode, $keluar] = $this->impor($json, $d['superadmin']);
        $this->assertSame(1, $kode, $keluar);
        $this->assertStringContainsString('generator, baris JSON ' . $this->barisDari($json, '"generator"') . ': Generator tidak deterministik', $keluar);
        $this->assertSame(0, $this->hitung("SELECT COUNT(*) FROM problems WHERE slug = 'acak'"));
    }

    public function testLarikBerisiSatuObyekDiterima(): void
    {
        $d    = $this->dunia();
        $json = '[' . $this->berkasImpor([$this->soalContoh('dua-bilangan')]) . ']';
        [$kode, $keluar] = $this->impor($json, $d['superadmin']);
        $this->assertSame(0, $kode, $keluar);
        $this->assertSame(1, $this->hitung('SELECT COUNT(*) FROM problem_versions WHERE import_id IS NOT NULL'));
    }

    public function testHanyaSuperadminAktifYangBolehMengimporLewatCli(): void
    {
        $d    = $this->dunia();
        $json = $this->berkasImpor([$this->soalContoh('dua-bilangan')]);
        foreach ([$d['pengampuX'], $d['mhsA'], ['nomor_induk' => 'tidak-terdaftar']] as $bukan) {
            [$kode, $keluar] = $this->impor($json, $bukan);
            $this->assertSame(1, $kode);
            $this->assertStringContainsString('bukan superadmin yang aktif', $keluar);
        }
        $this->idn->query("UPDATE users SET status = 'disabled' WHERE id = '{$d['superadmin']['id']}'");
        $this->assertSame(1, $this->impor($json, $d['superadmin'])[0]);
        $this->assertSame(0, $this->hitung("SELECT COUNT(*) FROM imports WHERE source = 'cli'") + $this->hitung("SELECT COUNT(*) FROM judge_jobs WHERE kind = 'VALIDATE'"));
    }

    public function testEksporPertemuanYangTidakAdaGagalDenganJelas(): void
    {
        $this->dunia();
        [$kode, $keluar, $galat] = $this->spark(['soal:ekspor', 'UJX101', '9']);
        $this->assertSame(1, $kode);
        $this->assertSame('', $keluar);
        $this->assertStringContainsString('mata kuliah UJX101 pertemuan 9 tidak ada', $galat);
    }
}
