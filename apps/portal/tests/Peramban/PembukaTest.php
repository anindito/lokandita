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
                ikon: Array.prototype.filter.call(document.querySelectorAll('svg.ikon'), function (s) { return s.getClientRects().length > 0; }).map(function (s) { var r = s.getBoundingClientRect(), g = getComputedStyle(s); return { w: r.width, h: r.height, garis: s.getAttribute('stroke-width'), sembunyi: s.getAttribute('aria-hidden'), label: s.getAttribute('aria-label'), arah: s.classList.contains('ikon-arah'), ubah: g.transform }; }),
                merek: (function (g) { return g ? { muat: g.complete && g.naturalWidth > 0, sumber: g.currentSrc, kotak: kotak(g), alami: [g.naturalWidth, g.naturalHeight] } : null; })(document.querySelector('.kepala .merek img')),
                kartu: Array.prototype.map.call(document.querySelectorAll('.petak-aplikasi .kartu-aplikasi'), function (k) { var g = k.querySelector('.kartu-ikon img'), n = k.querySelector('.kartu-judul'), j = k.querySelector('.kartu-judul strong'), l = k.querySelector('.lencana'); return { kode: k.getAttribute('data-aplikasi'), tautan: k.tagName === 'A', muat: !!g && g.complete && g.naturalWidth > 0, sumber: g ? g.currentSrc : '', ikon: kotak(g), nama: kotak(n), judul: kotak(j), lencana: kotak(l), baris: j ? j.getClientRects().length : 0, saring: g ? getComputedStyle(g).filter : '', buram: g ? getComputedStyle(g).opacity : '' }; }),
                langkah: Array.prototype.map.call(document.querySelectorAll('.langkah > li'), function (li) { var n = li.querySelector('.langkah-nomor'), t = li.querySelector('span:not(.ikon-wadah):not(.langkah-nomor)'), b = Array.prototype.map.call(t.getClientRects(), function (r) { return { kiri: r.left, kanan: r.right, atas: r.top, bawah: r.bottom }; }), g = getComputedStyle(n); return { kartu: kotak(li), ikon: kotak(li.querySelector('.ikon-wadah')), nomor: kotak(n), baris: b, tebal: g.fontWeight, warna: g.color, warnaTeks: getComputedStyle(t).color, huruf: g.fontSize, hurufTeks: getComputedStyle(t).fontSize }; }),
                bagianLangkah: kotak(document.querySelector('.langkah')),
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
        // (Kartu aplikasi Portal memakai ikon merek, bukan ikon garis — diukur di bawah.)
        $this->assertGreaterThanOrEqual($situs === 'portal' ? 9 : 16, count($u['ikon']), "{$nama}: jumlah ikon");
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

        // Logo kepala: termuat dari host sendiri, ragam yang sesuai mode, tinggi 40 px, berkas 2×,
        // perbandingan sisinya tidak diubah; di tepi awal kepala (kanan untuk Arab).
        $ragam = $skema === 'dark' ? 'gelap' : 'terang';
        $this->assertTrue($u['merek']['muat'] ?? false, "{$nama}: logo kepala tidak termuat");
        $this->assertMatchesRegularExpression('#^' . preg_quote(self::SITUS[$situs], '#') . '/merek/logo-' . $ragam . '\.webp\?v=#', (string) $u['merek']['sumber'], "{$nama}: logo kepala");
        $this->assertEqualsWithDelta(40, $u['merek']['kotak']['tinggi'], 0.5, "{$nama}: tinggi logo kepala");
        $this->assertSame(80, $u['merek']['alami'][1], "{$nama}: berkas logo kepala 2×");
        $this->assertEqualsWithDelta($u['merek']['alami'][0] / 2, $u['merek']['kotak']['lebar'], 1, "{$nama}: logo kepala tidak boleh gepeng");
        $bahasa === 'ar'
            ? $this->assertGreaterThan($u['lebar'] / 2, $u['merek']['kotak']['kiri'], "{$nama}: logo kepala di kanan")
            : $this->assertLessThan($u['lebar'] / 2, $u['merek']['kotak']['kanan'], "{$nama}: logo kepala di kiri");

        // Kartu aplikasi Portal: ikon merek satu ukuran, sejajar tengah dengan blok nama-dan-status,
        // ragam sesuai mode, susunan dicerminkan untuk Arab; "Segera" lebih tenang (ikon abu-abu).
        if ($situs === 'portal') {
            $this->assertSame(['lokakode', 'lokaujian', 'lokakelas', 'lokasiber', 'lokaretas', 'lokadata'], array_column($u['kartu'], 'kode'), $nama);
            foreach ($u['kartu'] as $k) {
                $apa = "{$nama}: kartu {$k['kode']}";
                $this->assertTrue($k['muat'], $apa . ' ikon tidak termuat');
                $this->assertMatchesRegularExpression('#^https://lokandita\.com/merek/aplikasi/' . $k['kode'] . '-' . $ragam . '\.webp\?v=#', $k['sumber'], $apa);
                $this->assertEqualsWithDelta(56, $k['ikon']['lebar'], 0.5, $apa . ' lebar ikon');
                $this->assertEqualsWithDelta(56, $k['ikon']['tinggi'], 0.5, $apa . ' tinggi ikon');
                $tengah = fn (array $kotak): float => ($kotak['atas'] + $kotak['bawah']) / 2;
                $this->assertEqualsWithDelta($tengah($k['ikon']), $tengah($k['nama']), 1.5, $apa . ' nama sejajar ikon');
                $this->assertGreaterThanOrEqual($k['judul']['bawah'] - 1, $k['lencana']['atas'], $apa . ' status di bawah nama');
                $this->assertLessThan(30, $k['judul']['tinggi'], $apa . ' nama tidak boleh terpenggal menjadi beberapa baris');
                $bahasa === 'ar'
                    ? $this->assertGreaterThanOrEqual($k['nama']['kanan'], $k['ikon']['kiri'], $apa . ' ikon di kanan nama')
                    : $this->assertLessThanOrEqual($k['nama']['kiri'], $k['ikon']['kanan'], $apa . ' ikon di kiri nama');
                $this->assertSame($k['kode'] === 'lokakode', $k['tautan'], $apa . ' hanya LokaKode yang bertaut');
                $this->assertSame($k['kode'] === 'lokakode' ? 'none' : 'grayscale(1)', $k['saring'], $apa);
                $this->assertSame($k['kode'] === 'lokakode' ? '1' : '0.65', $k['buram'], $apa);
            }
            // Jarak ikon ke tepi kartu sama di semua kartu: ikon-ikon dalam satu kolom lurus.
            $this->assertEqualsWithDelta(56, $u['kartu'][0]['ikon']['lebar'], 0.5, $nama);
        }

        // Kartu langkah ("Cara memulai" di Portal, "Cara kerjanya" di LokaKode — satu gaya untuk
        // keduanya): nomor mengawali teks di baris yang sama, tebal dan berwarna aksen; baris
        // berikutnya turun di bawah nomor; ikon tetap di tepi awal kartu; untuk Arab semuanya dicerminkan.
        $this->assertCount($situs === 'portal' ? 4 : 5, $u['langkah'], $nama);
        $adaLanjutan = false;
        foreach ($u['langkah'] as $i => $l) {
            $apa   = "{$nama}: langkah " . ($i + 1);
            $awal  = $l['baris'][0];
            $this->assertEqualsWithDelta($l['nomor']['atas'], $awal['atas'], 1.5, $apa . ' teks mulai di baris nomor');
            $this->assertSame($l['hurufTeks'], $l['huruf'], $apa . ' nomor seukuran teksnya');
            $this->assertGreaterThanOrEqual(700, (int) $l['tebal'], $apa . ' nomor tebal');
            $this->assertNotSame($l['warnaTeks'], $l['warna'], $apa . ' nomor berwarna aksen');
            if ($bahasa === 'ar') {
                $this->assertGreaterThanOrEqual($awal['kanan'] - 1, $l['nomor']['kiri'], $apa . ' nomor di kanan teks');
                $this->assertLessThan(12, $l['nomor']['kiri'] - $awal['kanan'], $apa . ' teks langsung sesudah nomor');
                $this->assertGreaterThanOrEqual($l['nomor']['kanan'] - 1, $l['ikon']['kiri'], $apa . ' ikon di kanan nomor');
                $this->assertEqualsWithDelta(24, $l['kartu']['kanan'] - $l['ikon']['kanan'], 2, $apa . ' ikon di tepi awal kartu');
            } else {
                $this->assertLessThanOrEqual($awal['kiri'] + 1, $l['nomor']['kanan'], $apa . ' nomor di kiri teks');
                $this->assertLessThan(12, $awal['kiri'] - $l['nomor']['kanan'], $apa . ' teks langsung sesudah nomor');
                $this->assertLessThanOrEqual($l['nomor']['kiri'] + 1, $l['ikon']['kanan'], $apa . ' ikon di kiri nomor');
                $this->assertEqualsWithDelta(24, $l['ikon']['kiri'] - $l['kartu']['kiri'], 2, $apa . ' ikon di tepi awal kartu');
            }
            $this->assertEqualsWithDelta(24, $l['ikon']['atas'] - $l['kartu']['atas'], 2, $apa . ' ikon di atas kartu');
            // Baris kedua: semua potongan teks yang letaknya di bawah baris pertama dan paling atas
            // (teks Arab yang memuat huruf Latin terbagi menjadi beberapa potongan dalam satu baris).
            $bawah = array_values(array_filter($l['baris'], fn (array $b): bool => $b['atas'] > $awal['atas'] + 5));
            if ($bawah !== []) {
                $adaLanjutan = true;
                $atas        = min(array_column($bawah, 'atas'));
                $kedua       = array_filter($bawah, fn (array $b): bool => $b['atas'] < $atas + 5);
                $bahasa === 'ar'
                    ? $this->assertEqualsWithDelta($l['nomor']['kanan'], max(array_column($kedua, 'kanan')), 1.5, $apa . ' baris kedua turun di bawah nomor')
                    : $this->assertEqualsWithDelta($l['nomor']['kiri'], min(array_column($kedua, 'kiri')), 1.5, $apa . ' baris kedua turun di bawah nomor');
            }
        }
        $this->assertTrue($adaLanjutan, "{$nama}: tidak ada langkah yang teksnya lebih dari satu baris, jadi lipatannya tidak terukur");
        // Susunan: kartu sama lebar; satu per baris di ponsel; di layar lebar empat sebaris (Portal),
        // atau tiga lalu dua di tengah (LokaKode) — tidak ada kartu yang sendirian di barisnya.
        $baris = [];
        foreach ($u['langkah'] as $l) {
            $baris[(string) round($l['kartu']['atas'])][] = $l['kartu'];
            $this->assertEqualsWithDelta($u['langkah'][0]['kartu']['lebar'], $l['kartu']['lebar'], 0.5, "{$nama}: kartu langkah sama lebar");
        }
        $jumlah = array_map('count', array_values($baris));
        $this->assertSame($lebar >= 1024 ? ($situs === 'portal' ? [4] : [3, 2]) : array_fill(0, count($u['langkah']), 1), $jumlah, "{$nama}: kartu langkah per baris");
        foreach ($baris as $kartuBaris) {
            $kiri  = min(array_column($kartuBaris, 'kiri')) - $u['bagianLangkah']['kiri'];
            $kanan = $u['bagianLangkah']['kanan'] - max(array_column($kartuBaris, 'kanan'));
            $this->assertEqualsWithDelta($kiri, $kanan, 1.5, "{$nama}: baris kartu langkah dipusatkan");
        }

        $dir = (string) getenv('TANGKAPAN_DIR');
        if ($dir !== '' && is_dir($dir)) {
            // Seluruh halaman, bukan hanya layar pertama.
            $p->ukuran($lebar, (int) min(6000, max(900, $u['tinggi'])));
            file_put_contents("{$dir}/{$nama}.png", $p->tangkap());
            // Dan bagian langkahnya saja, dengan judul bagiannya.
            $k = $p->js('(function () { var r = document.querySelector(".langkah").getBoundingClientRect(); return { y: r.top + window.scrollY, t: r.height }; })()');
            $klip = ['x' => 0, 'y' => max(0, (float) $k['y'] - 80), 'width' => $lebar, 'height' => (float) $k['t'] + 110, 'scale' => 1];
            file_put_contents("{$dir}/langkah-{$nama}.png", (string) base64_decode((string) $p->perintah('Page.captureScreenshot', ['format' => 'png', 'clip' => $klip, 'captureBeyondViewport' => true])['data'], true));
        }
    }
}
