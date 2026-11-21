<?php

declare(strict_types=1);

namespace Tests\Peramban;

use Lokandita\Auth\Testing\Peramban;
use PHPUnit\Framework\TestCase;

/**
 * Tata letak halaman publik ketiga aplikasi dalam bahasa Arab (kanan-ke-kiri), di
 * peramban sungguhan (Chromium tanpa layar lewat caddy-tes, seperti produksi).
 *
 * Aturannya (pemilik, 2026-11-21):
 *   - kepala Arab mulai dari tepi kanan: merek di kanan, tombol dan bahasa di kiri — cermin
 *     kepala Indonesia;
 *   - kolom isi sama lebar dan sama letaknya, dicerminkan;
 *   - pembuka terbelah dua (keputusan pemilik 2026-11-21 malam, menggantikan "dipusatkan"):
 *     teksnya (judul, paragraf, tombol, catatan) mulai di tepi AWAL kolom isi — kiri untuk
 *     Indonesia dan Inggris, kanan untuk Arab — dan gambarnya di sisi seberang; versi Arab
 *     adalah cermin versi Indonesia;
 *   - kolom teks Arab mulai di tepi kanan kolom isi; kolom teks Indonesia di tengah.
 *
 * Tes ini mengukur kotak elemen yang benar-benar digambar peramban; ia tidak menilai
 * mutu bahasa Arabnya. Dengan TANGKAPAN_DIR terisi (infra/tes.sh, variabel TANGKAPAN),
 * tangkapan layar tiap halaman disimpan di sana.
 */
final class ArabPublikTest extends TestCase
{
    private const PORTAL = 'https://lokandita.com';

    private const KODE = 'https://lokakode.lokandita.com';

    private const AUTH = 'https://auth.lokandita.com';

    /** Kotak elemen dan gaya yang menentukan letaknya, diukur di halaman. */
    private const UKUR = <<<'JS'
        (function () {
            function kotak(el) {
                if (!el) { return null; }
                var r = el.getBoundingClientRect(), g = getComputedStyle(el);
                return { kiri: r.left, kanan: r.right, lebar: r.width, rata: g.textAlign, arah: g.direction,
                         isiKiri: r.left + parseFloat(g.paddingLeft), isiKanan: r.right - parseFloat(g.paddingRight) };
            }
            function semua(pemilih) { return Array.prototype.map.call(document.querySelectorAll(pemilih), kotak); }
            return {
                lebar: document.documentElement.clientWidth,
                gulir: document.documentElement.scrollWidth,
                dir: document.documentElement.getAttribute('dir'),
                kepala: kotak(document.querySelector('.kepala')),
                merek: kotak(document.querySelector('.kepala .merek')),
                aksi: kotak(document.querySelector('.kepala-publik')),
                isi: kotak(document.querySelector('main.isi')),
                pembuka: semua('.pembuka-teks > *'),
                gambar: kotak(document.querySelector('.pembuka-foto, .pembuka-logo')),
                prosa: kotak(document.querySelector('.prosa')),
                prosaAnak: semua('.prosa > h1, .prosa > h2, .prosa > p'),
                kartu: kotak(document.querySelector('.isi-sempit')),
                csp: window.__csp, galat: window.__galat
            };
        })()
        JS;

    /**
     * @return iterable<string, array{0: string, 1: string}>
     */
    public static function halaman(): iterable
    {
        yield 'portal-depan' => [self::PORTAL, '/'];
        yield 'portal-tentang' => [self::PORTAL, '/tentang'];
        yield 'portal-privasi' => [self::PORTAL, '/privasi'];
        yield 'portal-ketentuan' => [self::PORTAL, '/ketentuan'];
        yield 'lokakode-depan' => [self::KODE, '/'];
        yield 'lokaauth-login' => [self::AUTH, '/login'];
    }

    /**
     * @return array<string, mixed>
     */
    private function ukur(Peramban $p, string $bahasa, string $jalur, int $lebar, ?string $berkas = null): array
    {
        // Layar dibuat sangat tinggi supaya tidak ada bilah gulir: bilah itu menggeser kolom yang
        // dipusatkan beberapa piksel dan hanya muncul di halaman yang panjang. Tangkapan layarnya
        // hanya 900 piksel teratas.
        $p->ukuran($lebar, 6000);
        $this->assertSame(200, $p->buka('/bahasa/' . $bahasa . '?kembali=' . rawurlencode($jalur)), "{$bahasa} {$jalur}");
        $u = $p->js(self::UKUR);
        $this->assertSame($bahasa === 'ar' ? 'rtl' : 'ltr', $u['dir'], "{$bahasa} {$jalur}: arah halaman");
        $this->assertSame([[], []], [$u['csp'], $u['galat']], "{$bahasa} {$jalur}: CSP dan galat skrip");
        $dir = (string) getenv('TANGKAPAN_DIR');
        if ($berkas !== null && $dir !== '' && is_dir($dir)) {
            file_put_contents("{$dir}/{$berkas}.png", $p->tangkap($lebar, 900));
        }

        return $u;
    }

    private function assertDekat(float $harap, float $nyata, string $apa): void
    {
        $this->assertEqualsWithDelta($harap, $nyata, 1.5, $apa);
    }

    /**
     * @dataProvider halaman
     */
    public function testTataLetakArabDanIndonesia(string $asal, string $jalur): void
    {
        $nama = (string) $this->dataName();
        $p    = new Peramban($asal);
        $id   = $this->ukur($p, 'id', $jalur, 1280, $nama . '-id');
        $ar   = $this->ukur($p, 'ar', $jalur, 1280, $nama . '-ar');
        // Lebar layar tiap halaman diukur sendiri: bilah gulir bisa ada di satu bahasa dan tidak di yang lain.
        $L    = (float) $ar['lebar'];
        $Lid  = (float) $id['lebar'];
        $this->assertLessThanOrEqual($L + 1, $ar['gulir'], "{$nama}: halaman Arab melebar melewati layar");

        // Kepala: merek menempel di tepi kanan isi kepala; di versi Indonesia di tepi kiri.
        $this->assertDekat($id['kepala']['isiKiri'], $id['merek']['kiri'], "{$nama}: (id) merek mulai di tepi kiri kepala");
        $this->assertDekat($ar['kepala']['isiKanan'], $ar['merek']['kanan'], "{$nama}: (ar) merek harus mulai di tepi KANAN kepala");
        $this->assertDekat($id['merek']['kiri'], $L - $ar['merek']['kanan'], "{$nama}: merek Arab cermin merek Indonesia");
        if ($ar['aksi'] !== null) {
            // Tombol Masuk dan pilihan bahasa: di tepi kiri, dan seluruhnya di kiri merek.
            $this->assertDekat($ar['kepala']['isiKiri'], $ar['aksi']['kiri'], "{$nama}: (ar) tombol dan bahasa harus berakhir di tepi KIRI kepala");
            $this->assertLessThan($ar['merek']['kiri'], $ar['aksi']['kanan'], "{$nama}: (ar) tombol dan bahasa harus di kiri merek");
            $this->assertDekat($id['kepala']['isiKanan'], $id['aksi']['kanan'], "{$nama}: (id) tombol dan bahasa berakhir di tepi kanan kepala");
        }

        // Kolom isi: sama lebar, letaknya dicerminkan.
        $this->assertDekat($id['isi']['lebar'], $ar['isi']['lebar'], "{$nama}: lebar kolom isi");
        $this->assertDekat($Lid - $id['isi']['kanan'], $ar['isi']['kiri'], "{$nama}: letak kolom isi (cermin)");

        // Pembuka (hero): tiap anaknya rata kanan dan mulai di tepi kanan kolom isi.
        if (in_array($nama, ['portal-depan', 'lokakode-depan'], true)) {
            $this->assertGreaterThanOrEqual(4, count($ar['pembuka']), "{$nama}: judul, paragraf, tombol dan catatan pembuka");
        }
        $this->assertCount(count($id['pembuka']), $ar['pembuka']);
        foreach ($ar['pembuka'] as $i => $k) {
            $apa = "{$nama}: pembuka anak ke-" . ($i + 1);
            $this->assertContains($k['rata'], ['right', 'start'], "{$apa} harus rata kanan, ternyata {$k['rata']}");
            $this->assertSame('rtl', $k['arah'], $apa);
            $this->assertDekat($ar['isi']['isiKanan'], $k['kanan'], "{$apa} harus mulai di tepi KANAN kolom isi");
            // Versi Indonesia: rata kiri dari tepi kiri kolom isi — dan versi Arab adalah cerminnya.
            $t = $id['pembuka'][$i];
            $this->assertContains($t['rata'], ['left', 'start'], "{$apa} (id) harus rata kiri, ternyata {$t['rata']}");
            $this->assertDekat($id['isi']['isiKiri'], $t['kiri'], "{$apa} (id) harus mulai di tepi KIRI kolom isi");
        }
        // Gambar pembuka di sisi seberang teks: kanan untuk Indonesia, kiri untuk Arab, sama lebar.
        if ($ar['gambar'] !== null) {
            $this->assertGreaterThan($id['pembuka'][0]['kanan'] - 1, $id['gambar']['kiri'], "{$nama}: (id) gambar di kanan teks");
            $this->assertLessThan($ar['pembuka'][0]['kiri'] + 1, $ar['gambar']['kanan'], "{$nama}: (ar) gambar di KIRI teks");
            $this->assertDekat($id['gambar']['lebar'], $ar['gambar']['lebar'], "{$nama}: lebar gambar");
            $this->assertDekat($Lid - $id['gambar']['kanan'], $ar['gambar']['kiri'], "{$nama}: letak gambar (cermin)");
        }

        // Halaman teks: kolom prosa dicerminkan dan isinya rata kanan dari tepi kanannya.
        if ($ar['prosa'] !== null) {
            $this->assertDekat($id['prosa']['lebar'], $ar['prosa']['lebar'], "{$nama}: lebar kolom teks");
            $this->assertDekat($ar['isi']['isiKanan'], $ar['prosa']['kanan'], "{$nama}: kolom teks harus mulai di tepi KANAN kolom isi");
            $this->assertDekat(($id['isi']['isiKiri'] + $id['isi']['isiKanan']) / 2, ($id['prosa']['kiri'] + $id['prosa']['kanan']) / 2, "{$nama}: (id) kolom teks di tengah kolom isi");
            $this->assertNotSame([], $ar['prosaAnak']);
            foreach ($ar['prosaAnak'] as $i => $k) {
                $this->assertContains($k['rata'], ['right', 'start'], "{$nama}: teks ke-" . ($i + 1) . ' rata kanan');
                $this->assertDekat($ar['prosa']['kanan'], $k['kanan'], "{$nama}: teks ke-" . ($i + 1) . ' mulai di tepi kanan kolom teks');
            }
        }
        // Kartu login LokaAuth: di tengah pada kedua bahasa, jadi cerminnya adalah dirinya sendiri.
        if ($ar['kartu'] !== null) {
            $this->assertDekat($Lid - $id['kartu']['kanan'], $ar['kartu']['kiri'], "{$nama}: letak kartu (cermin)");
        }
    }

    /**
     * Bahasa Inggris mengikuti Indonesia: teks pembuka mulai di tepi kiri, gambar di kanannya.
     */
    public function testPembukaInggrisSepertiIndonesia(): void
    {
        foreach ([[self::PORTAL, 'portal-depan-en'], [self::KODE, 'lokakode-depan-en']] as [$asal, $nama]) {
            $en = $this->ukur(new Peramban($asal), 'en', '/', 1280, $nama);
            $this->assertGreaterThanOrEqual(4, count($en['pembuka']), $nama);
            foreach ($en['pembuka'] as $i => $t) {
                $this->assertContains($t['rata'], ['left', 'start'], "{$nama}: pembuka anak ke-" . ($i + 1));
                $this->assertDekat($en['isi']['isiKiri'], $t['kiri'], "{$nama}: pembuka anak ke-" . ($i + 1) . ' mulai di tepi kiri kolom isi');
            }
            $this->assertGreaterThan($en['pembuka'][0]['kanan'] - 1, $en['gambar']['kiri'], "{$nama}: gambar di kanan teks");
        }
    }

    /**
     * Di layar sempit (ponsel) halaman Arab tidak melebar dan pembukanya tetap mulai di kanan.
     *
     * @dataProvider halaman
     */
    public function testHalamanArabDiLayarSempit(string $asal, string $jalur): void
    {
        $nama = (string) $this->dataName();
        $p    = new Peramban($asal);
        $ar   = $this->ukur($p, 'ar', $jalur, 390, $nama . '-ar-sempit');
        $this->assertLessThanOrEqual($ar['lebar'] + 1, $ar['gulir'], "{$nama}: halaman Arab melebar melewati layar sempit");
        $this->assertGreaterThan($ar['lebar'] / 2, $ar['merek']['kanan'], "{$nama}: merek di sisi kanan");
        foreach ($ar['pembuka'] as $i => $k) {
            $this->assertContains($k['rata'], ['right', 'start'], "{$nama}: pembuka anak ke-" . ($i + 1));
            $this->assertDekat($ar['isi']['isiKanan'], $k['kanan'], "{$nama}: pembuka anak ke-" . ($i + 1) . ' mulai di tepi kanan');
        }
    }
}
