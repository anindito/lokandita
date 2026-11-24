<?php

declare(strict_types=1);

namespace Tests\Peramban;

use Lokandita\Auth\Testing\Fixtures;
use Tests\Support\KodeCase;
use Tests\Support\Peramban;

/**
 * Pertemuan UJIAN di PERAMBAN SUNGGUHAN (SPEC.md §6): yang dikerjakan JavaScript halaman
 * dan tidak terbukti dengan permintaan HTTP saja — hitung mundur sisa waktu, dan apa yang
 * tampil setelah Submit (hanya "kiriman diterima", atau log kompiler; tanpa nilai, tanpa
 * hasil tes), dalam bahasa Indonesia dan dalam halaman berbahasa Arab.
 */
final class UjianPerambanTest extends KodeCase
{
    private const BENAR = "#include <iostream>\nint main(){long a,b;std::cin>>a>>b;std::cout<<a+b<<\"\\n\";}\n";

    private const SALAH = "#include <iostream>\nint main(){std::cout<<0<<\"\\n\";}\n";

    private const DITERIMA = [
        'id' => 'Kiriman Anda diterima. Hasil penilaian ujian tidak ditampilkan sampai dosen merilis nilainya.',
        'ar' => 'تم استلام إرسالك.',
    ];

    /**
     * Ujian terbit dengan satu soal, terbuka untuk kelas XA selama 150 menit sejak sekarang.
     *
     * @return array<string, mixed>
     */
    private function duniaUjian(): array
    {
        $d = $this->dunia();
        $this->db->query("UPDATE meetings SET kind = 'ujian', title = 'UTS Praktik' WHERE id = {$d['pertemuanX1']}");
        $d['soal'] = $this->soalSiap($d['pertemuanX1'], 'ujian-debit', 'MUDAH');
        $this->terbit($d['pertemuanX1'], ['target_points' => 5]);
        $this->jendela($d['pertemuanX1'], $d['kelasXA'], [
            'override' => 'none', 'open_at' => gmdate('Y-m-d H:i:s', time() - 60), 'close_at' => gmdate('Y-m-d H:i:s', time() + 150 * 60),
        ]);

        return $d;
    }

    /**
     * @param array<string, mixed> $mhs
     */
    private function peramban(array $mhs): Peramban
    {
        $p = new Peramban();
        $p->kuki('lokandita_sid', Fixtures::session($this->idn, (string) $mhs['id']));

        return $p;
    }

    /**
     * Mengisi editor, menekan tombol Submit, dan menunggu sampai panel hasil memuat $tanda.
     */
    private function kirim(Peramban $p, string $sumber, string $tanda, string $apa): string
    {
        $this->lupakanLaju();
        $p->js('window.lokakodeEditor.setValue(' . json_encode($sumber) . ') || true');
        // Hasil kiriman sebelumnya dibuang dulu, supaya yang ditunggu pasti hasil kiriman INI.
        $p->tunggu("document.querySelector('[data-aksi=\"kirim\"]').disabled === false", 120, 'tombol Submit siap');
        $p->js("(function () { document.querySelector('[data-keluaran]').textContent = ''; return true; })()");
        $p->klik('[data-aksi="kirim"]');
        try {
            $p->tunggu("document.querySelector('[data-keluaran]').textContent.indexOf(" . json_encode($tanda) . ') >= 0', 120, $apa);
        } catch (\RuntimeException $e) {
            $this->fail($apa . ': panel hasil berisi "' . $p->js("document.querySelector('[data-keluaran]').textContent")
                . '"; pesan: "' . $p->js("(document.querySelector('[data-toast-soal]') || {}).textContent") . '"');
        }

        return (string) $p->js("document.querySelector('[data-keluaran]').textContent");
    }

    /**
     * Panel hasil tidak memuat nilai, daftar tes, atau verdict benar/salah.
     */
    private function pastikanTanpaHasil(Peramban $p, string $apa): void
    {
        $this->assertSame([0, 0, 0, 0], [
            $p->js("document.querySelectorAll('[data-keluaran] .hasil-nilai').length"),
            $p->js("document.querySelectorAll('[data-keluaran] .tes-daftar').length"),
            $p->js("document.querySelectorAll('[data-keluaran] .verdict-ac').length"),
            $p->js("document.querySelectorAll('[data-keluaran] .verdict-wa').length"),
        ], $apa);
    }

    public function testHitungMundurDanSetelahSubmitHanyaKirimanDiterima(): void
    {
        $d = $this->duniaUjian();
        $this->instans($d['mhsA'], $d['soal']);
        $p = $this->peramban($d['mhsA']);

        // Halaman pertemuan: sisa waktu dihitung mundur dari 150 menit (2 j 28 m atau 2 j 29 m).
        $this->assertSame(200, $p->buka('/latihan/' . $d['pertemuanX1']));
        $p->tunggu("document.querySelector('[data-hitung-mundur]').textContent !== ''", 10, 'hitung mundur terisi');
        $this->assertMatchesRegularExpression('/^2 j 2[89] m$/', (string) $p->js("document.querySelector('[data-hitung-mundur]').textContent"));
        $this->assertStringContainsString('Ini ujian.', (string) $p->js('document.body.textContent'));

        // Halaman soal: Submit yang benar dan yang salah tampak sama.
        $this->assertSame(200, $p->buka('/latihan/soal/' . $d['soal']['mp']));
        $p->tunggu("document.documentElement.dataset.editorSiap === '1'", 30, 'Monaco siap');
        $benar = $this->kirim($p, self::BENAR, self::DITERIMA['id'], 'kiriman benar diterima');
        $this->pastikanTanpaHasil($p, 'kiriman benar');
        $salah = $this->kirim($p, self::SALAH, self::DITERIMA['id'], 'kiriman salah diterima');
        $this->pastikanTanpaHasil($p, 'kiriman salah');
        $this->assertSame($benar, $salah, 'panel hasil kiriman benar dan salah sama persis');
        $this->assertStringContainsString('Kiriman diterima', $benar);
        $this->assertStringNotContainsString('tes tersembunyi', $benar);
        $this->assertStringNotContainsString('Nilai kiriman', $benar);

        // Galat kompilasi: log kompiler tampil, tetap tanpa nilai.
        $ce = $this->kirim($p, "int main(){ ini bukan C++ }\n", 'Log kompiler', 'galat kompilasi');
        $this->assertStringContainsString(self::DITERIMA['id'], $ce);
        $this->pastikanTanpaHasil($p, 'galat kompilasi');

        // Di basis data nilainya ada (5 dari 5 poin), jadi yang disembunyikan memang hanya tampilannya.
        $this->assertSame('100.00', $this->row("SELECT score FROM meeting_scores WHERE user_id = '{$d['mhsA']['id']}'")['score']);
        $this->assertSame([], $p->js('window.__galat'));
    }

    public function testHalamanUjianBerbahasaArab(): void
    {
        $d    = $this->duniaUjian();
        $arab = $this->user('mahasiswa', 'طالبة عربية', ['locale' => 'ar']);
        $this->daftar($arab, $d['kelasXA']);
        $this->instans($arab, $d['soal']);
        $p = $this->peramban($arab);

        $this->assertSame(200, $p->buka('/latihan/' . $d['pertemuanX1']));
        $this->assertSame(['rtl', 'ar'], [$p->js('document.documentElement.dir'), $p->js('document.documentElement.lang')]);
        $p->tunggu("document.querySelector('[data-hitung-mundur]').textContent !== ''", 10, 'hitung mundur terisi');
        // Angka sisa waktu tetap angka Latin dan kiri-ke-kanan.
        $this->assertMatchesRegularExpression('/[0-9]/', (string) $p->js("document.querySelector('[data-hitung-mundur]').textContent"));
        $this->assertDoesNotMatchRegularExpression('/[٠-٩]/u', (string) $p->js("document.querySelector('[data-hitung-mundur]').textContent"));
        $this->assertSame('ltr', $p->js("getComputedStyle(document.querySelector('[data-hitung-mundur]')).direction"));

        $this->assertSame(200, $p->buka('/latihan/soal/' . $d['soal']['mp']));
        $p->tunggu("document.documentElement.dataset.editorSiap === '1'", 30, 'Monaco siap');
        $hasil = $this->kirim($p, self::BENAR, self::DITERIMA['ar'], 'pesan diterima berbahasa Arab');
        $this->pastikanTanpaHasil($p, 'kiriman pada halaman Arab');
        $this->assertStringNotContainsString('Kiriman Anda diterima', $hasil, 'tidak ada teks Indonesia yang tertinggal');
        $this->assertSame([], $p->js('window.__galat'));
    }
}
