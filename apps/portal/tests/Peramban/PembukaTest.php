<?php

declare(strict_types=1);

namespace Tests\Peramban;

use Lokandita\Auth\Testing\Peramban;
use PHPUnit\Framework\TestCase;

/**
 * Halaman depan Portal dan LokaKode di peramban sungguhan (Chromium tanpa layar lewat
 * caddy-tes, seperti produksi): foto pembuka, ikon, kontras, dan pergeseran tata letak —
 * dalam bahasa Indonesia dan Arab, mode terang dan gelap, selebar desktop dan ponsel.
 *
 * Dengan TANGKAPAN_DIR terisi (infra/tes.sh, variabel TANGKAPAN), tangkapan layar tiap
 * kombinasi disimpan di sana: <situs>-<bahasa>-<mode>-<lebar>.png.
 */
final class PembukaTest extends TestCase
{
    private const SITUS = ['portal' => 'https://lokandita.com', 'lokakode' => 'https://lokakode.lokandita.com'];

    private const UKUR = <<<'JS'
        (function () {
            function kotak(el) { if (!el) { return null; } var r = el.getBoundingClientRect(); return { kiri: r.left, kanan: r.right, atas: r.top, bawah: r.bottom, lebar: r.width, tinggi: r.height }; }
            function rgb(s) { var m = s.match(/[\d.]+/g).map(Number); return { r: m[0], g: m[1], b: m[2], a: m.length > 3 ? m[3] : 1 }; }
            function kilau(c) { return [c.r, c.g, c.b].map(function (v) { v /= 255; return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4); }).reduce(function (t, v, i) { return t + v * [0.2126, 0.7152, 0.0722][i]; }, 0); }
            // Latar yang sebenarnya di belakang elemen: leluhur pertama yang latarnya tidak tembus pandang.
            function latar(el) { for (; el; el = el.parentElement) { var c = rgb(getComputedStyle(el).backgroundColor); if (c.a === 1) { return c; } } return { r: 255, g: 255, b: 255, a: 1 }; }
            function kontras(el) { var a = kilau(rgb(getComputedStyle(el).color)), b = kilau(latar(el)); return (Math.max(a, b) + 0.05) / (Math.min(a, b) + 0.05); }
            var img = document.querySelector('.pembuka-foto img, .pembuka-logo img');
            var teks = Array.prototype.slice.call(document.querySelectorAll('.pembuka-teks > *, .judul-bagian, .kartu-aplikasi strong, .kartu-aplikasi span:not(.ikon-wadah), .langkah li > span:not(.ikon-wadah), .bantuan, .kaki span, .kaki a, .kepala a'));
            return {
                teks: kotak(document.querySelector('.pembuka-teks')),
                gambar: kotak(img),
                img: img ? { muat: img.complete && img.naturalWidth > 0, sumber: img.currentSrc, w: img.getAttribute('width'), h: img.getAttribute('height'), alt: img.getAttribute('alt'), cocok: getComputedStyle(img).objectFit } : null,
                kontras: teks.filter(function (el) { return el.textContent.trim() !== '' && el.getClientRects().length; }).map(function (el) { return { apa: (el.className || el.tagName) + ': ' + el.textContent.trim().slice(0, 30), nilai: kontras(el) }; }),
                ikon: Array.prototype.map.call(document.querySelectorAll('svg.ikon'), function (s) { var r = s.getBoundingClientRect(), g = getComputedStyle(s); return { w: r.width, h: r.height, garis: s.getAttribute('stroke-width'), sembunyi: s.getAttribute('aria-hidden'), label: s.getAttribute('aria-label'), arah: s.classList.contains('ikon-arah'), ubah: g.transform }; }),
                geser: window.__geser, geserApa: window.__geserApa, lebar: document.documentElement.clientWidth, gulir: document.documentElement.scrollWidth, tinggi: document.documentElement.scrollHeight,
                csp: window.__csp, galat: window.__galat, latar: getComputedStyle(document.body).backgroundColor
            };
        })()
        JS;

    /**
     * @return iterable<string, array{0: string, 1: string, 2: string, 3: int}>
     */
    public static function kombinasi(): iterable
    {
        foreach (array_keys(self::SITUS) as $situs) {
            foreach (['id', 'ar'] as $bahasa) {
                foreach (['terang' => 'light', 'gelap' => 'dark'] as $mode => $skema) {
                    foreach (['desktop' => 1280, 'ponsel' => 390] as $nama => $lebar) {
                        yield "{$situs}-{$bahasa}-{$mode}-{$nama}" => [$situs, $bahasa, $skema, $lebar];
                    }
                }
            }
        }
    }

    /**
     * @dataProvider kombinasi
     */
    public function testHalamanDepan(string $situs, string $bahasa, string $skema, int $lebar): void
    {
        $nama = (string) $this->dataName();
        $p    = new Peramban(self::SITUS[$situs]);
        $p->ukuran($lebar, 900);
        $p->perintah('Emulation.setEmulatedMedia', ['features' => [['name' => 'prefers-color-scheme', 'value' => $skema]]]);
        // Bilah gulir disembunyikan, seperti di ponsel (di sana bilah itu melayang di atas halaman).
        // Dengan bilah gulir klasik, kemunculannya saat halaman panjang selesai digambar menggeser kepala
        // dan kolom isi beberapa piksel — itu bukan pergeseran karena foto, dan diukur terpisah dari sini.
        $p->perintah('Emulation.setScrollbarsHidden', ['hidden' => true]);
        // Pergeseran tata letak dicatat sejak dokumen mulai dimuat.
        $p->perintah('Page.addScriptToEvaluateOnNewDocument', ['source' => 'window.__geser = 0; window.__geserApa = []; new PerformanceObserver(function (l) { l.getEntries().forEach(function (e) { if (!e.hadRecentInput) { window.__geser += e.value; (e.sources || []).forEach(function (s) { var n = s.node; window.__geserApa.push(n ? (n.nodeName + "." + (n.className && n.className.baseVal !== undefined ? n.className.baseVal : n.className || "")) : "?"); }); } }); }).observe({ type: "layout-shift", buffered: true });']);
        $this->assertSame(200, $p->buka('/bahasa/' . $bahasa . '?kembali=' . rawurlencode('/')), $nama);
        $p->tunggu("(function () { var i = document.querySelector('.pembuka-foto img, .pembuka-logo img'); return !!i && i.complete && i.naturalWidth > 0; })()", 20, "{$nama}: gambar pembuka dimuat");
        $u = $p->js(self::UKUR);

        $this->assertSame([[], []], [$u['csp'], $u['galat']], "{$nama}: CSP dan galat skrip");
        $this->assertLessThanOrEqual($u['lebar'] + 1, $u['gulir'], "{$nama}: halaman melebar melewati layar");
        // Mode gelap benar-benar berlaku: latar halaman gelap.
        preg_match_all('/[\d.]+/', (string) $u['latar'], $m);
        $terang = array_sum(array_slice($m[0], 0, 3)) / 3;
        $skema === 'dark' ? $this->assertLessThan(60, $terang, "{$nama}: latar gelap") : $this->assertGreaterThan(200, $terang, "{$nama}: latar terang");

        // Gambar: dimuat, dari host sendiri, format modern, ukuran tertulis, punya teks alternatif.
        $this->assertTrue($u['img']['muat'], "{$nama}: gambar dimuat");
        $this->assertStringStartsWith(self::SITUS[$situs] . '/', $u['img']['sumber'], $nama);
        $this->assertMatchesRegularExpression('/\.(avif|webp)(\?|$)/', $u['img']['sumber'], "{$nama}: peramban memilih AVIF atau WebP");
        $this->assertGreaterThan(0, (int) $u['img']['w'], $nama);
        $this->assertGreaterThan(0, (int) $u['img']['h'], $nama);
        $this->assertNotSame('', trim((string) $u['img']['alt']), "{$nama}: teks alternatif");
        if ($situs === 'portal') {
            $this->assertSame('cover', $u['img']['cocok'], $nama);
            $this->assertEqualsWithDelta(4 / 3, $u['gambar']['lebar'] / $u['gambar']['tinggi'], 0.02, "{$nama}: kotak foto 4:3");
            // Di ponsel peramban tidak mengambil berkas terbesar.
            if ($lebar === 390) {
                $this->assertMatchesRegularExpression('/-(480|768)\./', $u['img']['sumber'], "{$nama}: berkas kecil untuk layar kecil");
            }
        }
        // Tidak ada pergeseran tata letak.
        $this->assertLessThan(0.01, (float) $u['geser'], "{$nama}: pergeseran tata letak (CLS); yang bergeser: " . implode(', ', (array) $u['geserApa']));

        // Teks tidak pernah berada di atas gambar: berdampingan di layar lebar, bertumpuk di layar sempit.
        if ($lebar >= 900) {
            $bahasa === 'ar'
                ? $this->assertLessThanOrEqual($u['teks']['kiri'] + 1, $u['gambar']['kanan'], "{$nama}: gambar di kiri teks")
                : $this->assertGreaterThanOrEqual($u['teks']['kanan'] - 1, $u['gambar']['kiri'], "{$nama}: gambar di kanan teks");
        } else {
            $this->assertGreaterThanOrEqual($u['teks']['bawah'] - 1, $u['gambar']['atas'], "{$nama}: gambar di bawah teks");
        }

        // Kontras WCAG AA (4,5 : 1) untuk teks pembuka, kartu, langkah, kepala dan kaki.
        $this->assertGreaterThan(10, count($u['kontras']), $nama);
        foreach ($u['kontras'] as $k) {
            $this->assertGreaterThanOrEqual(4.5, $k['nilai'], "{$nama}: kontras «{$k['apa']}» = " . round($k['nilai'], 2));
        }

        // Ikon: satu ukuran, satu tebal garis; hiasan disembunyikan dari pembaca layar atau berlabel;
        // ikon berarah dibalik hanya di halaman kanan-ke-kiri.
        $this->assertGreaterThanOrEqual($situs === 'portal' ? 12 : 16, count($u['ikon']), "{$nama}: jumlah ikon");
        $berarah = 0;
        foreach ($u['ikon'] as $i => $ik) {
            $apa = "{$nama}: ikon ke-" . ($i + 1);
            $this->assertEqualsWithDelta(20, $ik['w'], 0.5, $apa . ' lebar');
            $this->assertEqualsWithDelta(20, $ik['h'], 0.5, $apa . ' tinggi');
            $this->assertSame('2', $ik['garis'], $apa . ' tebal garis');
            $this->assertTrue($ik['sembunyi'] === 'true' || trim((string) $ik['label']) !== '', $apa . ' tersembunyi atau berlabel');
            if ($ik['arah']) {
                $berarah++;
                $this->assertSame($bahasa === 'ar' ? 'matrix(-1, 0, 0, 1, 0, 0)' : 'none', $ik['ubah'], $apa . ' (berarah)');
            } else {
                $this->assertSame('none', $ik['ubah'], $apa);
            }
        }
        $this->assertGreaterThanOrEqual(3, $berarah, "{$nama}: ikon berarah");

        $dir = (string) getenv('TANGKAPAN_DIR');
        if ($dir !== '' && is_dir($dir)) {
            // Seluruh halaman, bukan hanya layar pertama.
            $p->ukuran($lebar, (int) min(6000, max(900, $u['tinggi'])));
            file_put_contents("{$dir}/{$nama}.png", $p->tangkap());
        }
    }
}
