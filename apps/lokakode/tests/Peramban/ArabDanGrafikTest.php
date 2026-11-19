<?php

declare(strict_types=1);

namespace Tests\Peramban;

use Lokandita\Auth\Testing\Fixtures;
use Tests\Support\KodeCase;
use Tests\Support\Peramban;

/**
 * Yang hanya bisa dibuktikan di peramban sungguhan untuk tahap 9b:
 *   - halaman berbahasa Arab tercermin (kanan-ke-kiri), tetapi editor, kode, contoh
 *     masukan/keluaran dan angka tetap kiri-ke-kanan, dengan angka Latin (SPEC.md §11);
 *   - grafik analitik digambar Chart.js dari host sendiri tanpa pelanggaran CSP.
 *
 * Tes ini membuktikan ARAH dan tata letak, bukan mutu bahasanya: teks Arab ditulis
 * agen dan belum dibaca penutur bahasa Arab.
 */
final class ArabDanGrafikTest extends KodeCase
{
    private const ARAH = 'getComputedStyle(document.querySelector(%s)).direction';

    private function peramban(array $pengguna): Peramban
    {
        $p = new Peramban();
        $p->kuki('lokandita_sid', Fixtures::session($this->idn, (string) $pengguna['id']));

        return $p;
    }

    private function arah(Peramban $p, string $pemilih): string
    {
        return (string) $p->js(sprintf(self::ARAH, json_encode($pemilih)));
    }

    /**
     * Tidak ada pelanggaran CSP, tidak ada galat skrip, tidak ada angka Arab-India,
     * dan halaman tidak melebar melewati layar.
     */
    private function assertHalamanBersih(Peramban $p, string $apa): void
    {
        $this->assertSame([[], []], [$p->js('window.__csp'), $p->js('window.__galat')], "{$apa}: CSP dan galat skrip");
        $this->assertSame(0, $p->js('(document.body.innerText.match(/[٠-٩۰-۹]/g) || []).length'), "{$apa}: angka harus Latin");
        $this->assertTrue($p->js('document.documentElement.scrollWidth <= document.documentElement.clientWidth + 1'), "{$apa}: halaman melebar melewati layar");
    }

    public function testHalamanMahasiswaBerbahasaArabTercerminTetapiKodeTetapKiriKeKanan(): void
    {
        $d    = $this->dunia();
        $soal = $this->soalSiap($d['pertemuanX1'], 'jumlah-dua-bilangan', 'MUDAH');
        // Versi dengan teks Arab yang memuat kode dan angka di tengah kalimat.
        $v  = $this->row("SELECT translations FROM problem_versions WHERE id = {$soal['versi']}");
        $tr = json_decode((string) $v['translations'], true);
        $tr['ar'] = ['title' => 'مجموع عددين', 'statement' => "اقرأ العددين `a` و `b` ثم اطبع مجموعهما حتى 1000.\n\n```\ncout << a + b;\n```",
            'input_format' => 'سطر واحد.', 'output_format' => 'عدد واحد.', 'constraints' => ''];
        $this->db->query("UPDATE problem_versions SET translations = '" . $this->db->real_escape_string(json_encode($tr, JSON_UNESCAPED_UNICODE)) . "' WHERE id = {$soal['versi']}");
        $this->terbit($d['pertemuanX1']);
        $this->jendela($d['pertemuanX1'], $d['kelasXA']);
        $arab = $this->user('mahasiswa', 'طالبة عربية', ['locale' => 'ar']);
        $this->daftar($arab, $d['kelasXA']);
        $ins = $this->instans($arab, $soal);
        $this->kirimanDinilai($arab, $soal, $ins, 7);
        $this->lupakanLaju();
        $p = $this->peramban($arab);
        $p->ukuran(1366, 800);

        // Beranda dan halaman pertemuan: tercermin, angka dan kode mata kuliah tetap kiri-ke-kanan.
        foreach (['/beranda', '/latihan/' . $d['pertemuanX1'], '/latihan/' . $d['pertemuanX1'] . '/papan'] as $jalur) {
            $this->assertSame(200, $p->buka($jalur), $jalur);
            $this->assertSame(['rtl', 'ar'], [$p->js('document.documentElement.dir'), $p->js('document.documentElement.lang')], $jalur);
            $this->assertSame('rtl', $this->arah($p, 'main, body'), $jalur);
            $this->assertSame('ltr', $this->arah($p, '.ltr'), $jalur);
            $this->assertHalamanBersih($p, $jalur);
        }
        // Panah "kembali" menunjuk ke kanan di halaman kanan-ke-kiri.
        $this->assertSame('"→"', $p->js("getComputedStyle(document.querySelector('.panah-balik'), '::before').content"));
        // Papan: nilai 3,5 dari 5 poin → 5,83; ditulis dengan angka Latin dan titik.
        $this->assertStringContainsString('5.83', (string) $p->js('document.querySelector("table[data-papan]").innerText'));
        $this->assertStringContainsString('لوحة الترتيب', (string) $p->js('document.querySelector("h1").innerText'));

        // Halaman soal.
        $this->assertSame(200, $p->buka('/latihan/soal/' . $soal['mp']));
        $p->tunggu("document.documentElement.dataset.editorSiap === '1'", 30, 'Monaco siap');
        $this->assertSame('rtl', $p->js('document.documentElement.dir'));
        // Pernyataan berbahasa Arab kanan-ke-kiri; kode di dalamnya kiri-ke-kanan.
        $this->assertSame('ar', $p->js('document.querySelector("[data-pernyataan]:not([hidden])").lang'));
        $this->assertSame('rtl', $this->arah($p, '[data-pernyataan="ar"]'));
        $this->assertSame('ltr', $this->arah($p, '[data-pernyataan="ar"] pre'));
        $this->assertSame('ltr', $this->arah($p, '[data-pernyataan="ar"] code'));
        // Editor, contoh masukan/keluaran: selalu kiri-ke-kanan dan rata kiri.
        foreach (['.editor-wadah', '.monaco-editor', '.monaco-editor .view-lines', 'pre.kode-sumber'] as $pemilih) {
            $this->assertSame('ltr', $this->arah($p, $pemilih), $pemilih);
        }
        $this->assertSame('left', $p->js("getComputedStyle(document.querySelector('pre.kode-sumber')).textAlign"));
        // Tata letak tercermin: panel soal di KANAN, editor di kiri.
        $this->assertTrue($p->js("document.querySelector('.panel-soal').getBoundingClientRect().left > document.querySelector('.panel-editor').getBoundingClientRect().left"), 'panel soal harus di kanan editor');
        // Pernyataan bahasa Indonesia, bila dipilih, tampil kiri-ke-kanan di halaman yang sama.
        $p->klik('[data-bahasa-soal="id"]');
        $this->assertSame('ltr', $this->arah($p, '[data-pernyataan="id"]'));
        $p->klik('[data-bahasa-soal="ar"]');
        $this->assertHalamanBersih($p, 'halaman soal');

        // Menjalankan program: keluaran program kiri-ke-kanan walau halamannya Arab.
        $p->js("window.lokakodeEditor.setValue('#include <iostream>\\nint main(){long a,b;std::cin>>a>>b;std::cout<<a+b<<\"\\\\n\";}\\n'); true");
        $p->klik('[data-aksi="jalankan"]');
        $p->tunggu("document.querySelectorAll('#isi-keluaran pre, [data-keluaran] pre').length > 0", 90, 'keluaran program');
        $this->assertSame('ltr', $this->arah($p, '#isi-keluaran pre, [data-keluaran] pre'));
        $this->assertHalamanBersih($p, 'sesudah Jalankan');

        // Layar sempit: tetap tidak melebar.
        $p->ukuran(390, 800);
        $this->assertSame(200, $p->buka('/latihan/' . $d['pertemuanX1']));
        $this->assertHalamanBersih($p, 'halaman pertemuan di layar sempit');
    }

    public function testGrafikAnalitikDigambarDariHostSendiriTanpaPelanggaranCsp(): void
    {
        $d = $this->dunia();
        $this->soalSiap($d['pertemuanX1'], 'jumlah-dua-bilangan', 'MUDAH');
        $this->terbit($d['pertemuanX1']);
        $this->jendela($d['pertemuanX1'], $d['kelasXA']);
        foreach ([['mhsA', 95], ['mhsB', 40]] as [$siapa, $nilai]) {
            Fixtures::insert($this->db, 'meeting_scores', [
                'user_id' => $d[$siapa]['id'], 'meeting_id' => $d['pertemuanX1'], 'total_points' => 57, 'counted_points' => 57, 'target_points' => 60,
                'score' => $nilai, 'surplus' => 0, 'very_easy_counted' => 0, 'submissions' => 1, 'reached_at' => '2026-11-18 03:00:00', 'updated_at' => gmdate('Y-m-d H:i:s'),
            ]);
        }
        $dosenArab = $this->user('dosen', 'محاضر', ['locale' => 'ar']);
        $this->ajar($d['kelasXA'], $dosenArab);

        foreach ([[$d['pengajarA'], 'ltr'], [$dosenArab, 'rtl']] as [$dosen, $arahHalaman]) {
            $p = $this->peramban($dosen);
            $p->ukuran(1280, 900);
            $this->assertSame(200, $p->buka('/kelas/' . $d['kelasXA'] . '/pertemuan/' . $d['pertemuanX1'] . '/analitik'));
            $p->tunggu("window.Chart && !!window.Chart.getChart(document.querySelector('canvas[data-grafik=\"sebaran\"]'))", 20, 'grafik sebaran');
            $this->assertSame($arahHalaman, $p->js('document.documentElement.dir'));
            // Grafik memuat angka yang sama dengan tabel di bawahnya: satu mahasiswa di 90–100.
            $this->assertSame([0, 0, 0, 0, 0, 0, 0, 0, 0, 1], $p->js("window.Chart.getChart(document.querySelector('canvas[data-grafik=\"sebaran\"]')).data.datasets[0].data"));
            $this->assertSame('90–100', $p->js("window.Chart.getChart(document.querySelector('canvas[data-grafik=\"sebaran\"]')).data.labels[9]"));
            $this->assertTrue($p->js("(function () { var c = document.querySelector('canvas[data-grafik=\"sebaran\"]'); return c.width > 200 && c.height > 100; })()"), 'kanvas punya ukuran');
            // Kanvas tidak kosong: ada piksel yang digambar.
            $this->assertTrue($p->js("(function () { var c = document.querySelector('canvas[data-grafik=\"sebaran\"]'); var d = c.getContext('2d').getImageData(0, 0, c.width, c.height).data; for (var i = 3; i < d.length; i += 4) { if (d[i] !== 0) { return true; } } return false; })()"), 'grafik tergambar');
            $this->assertSame('ltr', $this->arah($p, '.grafik-wadah'));
            $this->assertHalamanBersih($p, 'analitik pertemuan (' . $arahHalaman . ')');
            // Semua sumber daya dari host aplikasi sendiri.
            $this->assertSame([], $p->js("performance.getEntriesByType('resource').map(function (r) { return new URL(r.name).origin; }).filter(function (o) { return o !== location.origin; })"));
        }

        // Perbandingan kelas (pengampu): satu deret per kelas.
        $p = $this->peramban($d['pengampuX']);
        $p->ukuran(1280, 900);
        $this->assertSame(200, $p->buka('/mata-kuliah/' . $d['matkulX'] . '/analitik'));
        $p->tunggu("window.Chart && !!window.Chart.getChart(document.querySelector('canvas[data-grafik=\"banding\"]'))", 20, 'grafik perbandingan');
        $this->assertSame(['XA', 'XB'], $p->js("window.Chart.getChart(document.querySelector('canvas[data-grafik=\"banding\"]')).data.datasets.map(function (s) { return s.label; })"));
        $this->assertSame([[95], [40]], $p->js("window.Chart.getChart(document.querySelector('canvas[data-grafik=\"banding\"]')).data.datasets.map(function (s) { return s.data; })"));
        $this->assertHalamanBersih($p, 'perbandingan kelas');
    }
}
