<?php

declare(strict_types=1);

namespace Tests\Peramban;

use Lokandita\Auth\Testing\Fixtures;
use Tests\Support\KodeCase;
use Tests\Support\Peramban;

/**
 * Tab Klarifikasi di halaman soal (SPEC.md §14) di PERAMBAN SUNGGUHAN: bertanya dari tab itu,
 * dan melihat jawaban yang diterbitkan dosen — ditulis sebagai teks, tidak pernah sebagai HTML.
 */
final class KlarifikasiTabTest extends KodeCase
{
    /**
     * @param array<string, mixed> $mhs
     */
    private function bukaTab(array $mhs, string $jalur): Peramban
    {
        $p = new Peramban();
        $p->kuki('lokandita_sid', Fixtures::session($this->idn, (string) $mhs['id']));
        $this->assertSame(200, $p->buka($jalur));
        $p->tunggu("document.documentElement.dataset.editorSiap === '1'", 30, 'Monaco siap');
        $p->klik('#tab-klarifikasi');
        $p->tunggu("!document.getElementById('isi-klarifikasi').hidden && document.querySelector('[data-klarifikasi-daftar]').childElementCount > 0", 10, 'tab klarifikasi dimuat');

        return $p;
    }

    public function testBertanyaDariTabDanMelihatJawabanYangDiterbitkanSebagaiTeks(): void
    {
        $d    = $this->dunia();
        $soal = $this->soalSiap($d['pertemuanX1'], 'jumlah-dua-bilangan');
        $this->terbit($d['pertemuanX1']);
        $this->jendela($d['pertemuanX1'], $d['kelasXA']);
        $cici = $this->user('mahasiswa', 'Mahasiswa Cici');
        $this->daftar($cici, $d['kelasXA']);
        $this->instans($d['mhsA'], $soal);
        $this->instans($cici, $soal, 3);
        $this->lupakanLaju();
        $jalur = '/latihan/soal/' . $soal['mp'];
        $teks  = fn (Peramban $p): string => (string) $p->js("document.querySelector('[data-klarifikasi-daftar]').textContent");

        // Ani: tab kosong, lalu bertanya dengan papan ketik.
        $p = $this->bukaTab($d['mhsA'], $jalur);
        $this->assertSame('Belum ada pertanyaan untuk soal ini.', $teks($p));
        $p->klik('[data-klarifikasi-isian]');
        $p->ketik('Apakah <b>n</b> bisa nol?');
        $p->klik('[data-klarifikasi-form] button[type=submit]');
        $p->tunggu("document.querySelectorAll('[data-klarifikasi]').length === 1", 10, 'pertanyaan tampil di daftar');
        $this->assertSame('', (string) $p->js("document.querySelector('[data-klarifikasi-isian]').value"), 'kotak isian dikosongkan');
        $this->assertStringContainsString('Pertanyaan Anda', $teks($p));
        $this->assertStringContainsString('Apakah <b>n</b> bisa nol?', $teks($p));
        $this->assertStringContainsString('Belum dijawab', $teks($p));
        $this->assertSame(0, $p->js("document.querySelectorAll('[data-klarifikasi-daftar] b').length"), 'HTML dalam pertanyaan tidak dijalankan');
        $q = $this->row('SELECT * FROM clarifications');
        $this->assertSame([$d['mhsA']['id'], 'Apakah <b>n</b> bisa nol?', (string) $d['kelasXA']], [$q['user_id'], $q['question'], $q['class_id']]);
        // Pertanyaan kedua dalam satu menit: ditolak dengan pesan, tidak diam.
        $p->klik('[data-klarifikasi-isian]');
        $p->ketik('Lagi');
        $p->klik('[data-klarifikasi-form] button[type=submit]');
        $p->tunggu("Array.prototype.some.call(document.querySelectorAll('[data-toast-soal] .toast'), function (t) { return t.textContent.indexOf('Tunggu') === 0; })", 10, 'pesan batas laju');
        $this->assertSame(1, $this->hitung('SELECT COUNT(*) FROM clarifications'));
        $this->assertSame([[], []], [$p->js('window.__csp'), $p->js('window.__galat')]);

        // Cici belum melihat apa-apa.
        $c = $this->bukaTab($cici, $jalur);
        $this->assertSame('Belum ada pertanyaan untuk soal ini.', $teks($c));

        // Pengajar menerbitkan jawabannya ke kelas (lewat halaman dosen, HTTP sungguhan).
        $dosen = $this->sebagai($d['pengajarA']);
        $r     = $dosen->post('/kelas/' . $d['kelasXA'] . '/klarifikasi/' . $q['id'] . '/jawab', ['csrf' => $this->csrf($dosen), 'jawaban' => "Tidak. <img src=x onerror=alert(1)>\nPaling kecil 1.", 'tampil' => 'kelas']);
        $this->assertContains($r->status, [302, 303]);

        // Cici membuka tab lagi: pertanyaan, nama penanya dan jawaban — sebagai teks.
        unset($c);
        $c = $this->bukaTab($cici, $jalur);
        $this->assertStringContainsString('Pertanyaan Mahasiswa Ani', $teks($c));
        $this->assertStringContainsString('Untuk seluruh kelas', $teks($c));
        $this->assertStringContainsString('Jawaban dosen', $teks($c));
        $this->assertStringContainsString("Tidak. <img src=x onerror=alert(1)>\nPaling kecil 1.", $teks($c));
        $this->assertSame(0, $c->js("document.querySelectorAll('[data-klarifikasi-daftar] img').length"), 'HTML dalam jawaban tidak dijalankan');
        $this->assertSame('pre-wrap', $c->js("getComputedStyle(document.querySelector('.klarifikasi-jawaban')).whiteSpace"), 'baris baru jawaban dipertahankan');
        $this->assertSame([[], []], [$c->js('window.__csp'), $c->js('window.__galat')]);
    }
}
