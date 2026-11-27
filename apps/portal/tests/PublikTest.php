<?php

declare(strict_types=1);

namespace Tests;

use Tests\Support\PortalCase;

/**
 * Halaman publik Portal (SPEC.md §15, KONTRAK.md §4 "Public routes"), lewat HTTP sungguhan.
 */
final class PublikTest extends PortalCase
{
    use \Lokandita\Auth\Testing\MerekTes;

    private const HALAMAN = [
        '/'          => 'Satu akun untuk semua aplikasi belajar Anda',
        '/tentang'   => 'Tentang Lokandita',
        '/privasi'   => 'Kebijakan privasi',
        '/ketentuan' => 'Ketentuan penggunaan',
    ];

    public function testSetiapHalamanPublikTerbukaTanpaSesiDanBolehDiindeks(): void
    {
        $surel = (string) getenv('CONTACT_EMAIL');
        $this->assertNotSame('', $surel);
        foreach (self::HALAMAN as $jalur => $tajuk) {
            $tamu = $this->tamu();
            $r    = $tamu->get($jalur);
            $this->assertSame(200, $r->status, $jalur);
            $this->assertStringContainsString('<html lang="id" dir="ltr">', $r->body, $jalur);
            $this->assertStringContainsString('<h1>' . $tajuk . '</h1>', $r->body, $jalur);
            $this->assertStringContainsString('<meta name="robots" content="index, follow">', $r->body, $jalur);
            $this->assertStringNotContainsString('noindex', $r->body, $jalur);
            $this->assertStringContainsString('<link rel="canonical" href="https://lokandita.com' . $jalur . '">', $r->body, $jalur);
            $this->assertMatchesRegularExpression('/<meta name="description" content="[^"]{40,}">/', $r->body, $jalur);
            // Tombol Masuk, navigasi, dan pilihan bahasa yang kembali ke halaman ini.
            $this->assertStringContainsString('<a class="tombol" href="/beranda" rel="nofollow">Masuk</a>', $r->body, $jalur);
            foreach (['/tentang', '/privasi', '/ketentuan'] as $nav) {
                $this->assertStringContainsString('href="' . $nav . '"', $r->body, $jalur);
            }
            $this->assertStringNotContainsString('href="/kontak"', $r->body, "{$jalur}: Kontak bukan halaman sendiri lagi");
            $this->assertStringContainsString('href="/bahasa/en?kembali=' . rawurlencode($jalur) . '"', $r->body, $jalur);
            // Tanpa sesi: tidak ada menu akun, tidak ada cookie sesi.
            $this->assertStringNotContainsString('data-menu-akun', $r->body, $jalur);
            $this->assertArrayNotHasKey('lokandita_sid', $tamu->cookies, $jalur);
            // Alamat kontak hanya dari CONTACT_EMAIL; tidak ada tempat isian yang tertinggal.
            $this->assertStringContainsString('mailto:' . $surel, $r->body, $jalur);
            $this->assertStringNotContainsString('{email}', $r->body, $jalur);
            // Header keamanan (SPEC.md §16).
            $this->assertMatchesRegularExpression("/script-src 'self' 'nonce-/", (string) $r->header('Content-Security-Policy'), $jalur);
            $this->assertSame('DENY', $r->header('X-Frame-Options'), $jalur);
            $this->assertSame('nosniff', $r->header('X-Content-Type-Options'), $jalur);
            $this->assertSame('same-origin', $r->header('Referrer-Policy'), $jalur);
        }
    }

    /**
     * Halaman Kontak dihapus dan isinya menjadi bagian halaman Tentang (keputusan pemilik
     * 2026-11-21). Alamat lama /kontak dialihkan untuk selamanya ke /tentang.
     */
    public function testIsiKontakAdaDiHalamanTentang(): void
    {
        $r = $this->tamu()->get('/kontak');
        $this->assertSame(301, $r->status);
        $this->assertSame('https://lokandita.com/tentang', $r->header('Location'));

        $r = $this->tamu()->get('/tentang');
        $this->assertSame(1, preg_match_all('/<h1>/', $r->body), 'satu judul utama');
        $urut = [];
        foreach (['<h1>Tentang Lokandita</h1>', '<h2>Pengelola</h2>', '<h2>Kontak</h2>', '<h2>Lupa kata sandi</h2>', '<h2>Belum punya akun</h2>', '<h2>Melaporkan masalah</h2>'] as $judul) {
            $letak = strpos($r->body, $judul);
            $this->assertNotFalse($letak, $judul);
            $urut[] = $letak;
        }
        $sudah = $urut;
        sort($sudah);
        $this->assertSame($sudah, $urut, 'isi Kontak menyusul isi Tentang, dalam urutan semula');
        $surel = (string) getenv('CONTACT_EMAIL');
        $this->assertStringContainsString('dihubungi lewat surel: <a href="mailto:' . $surel . '">' . $surel . '</a>', $r->body);
        // Navigasi: satu butir; halaman depan menaut ke halaman itu.
        $this->assertStringContainsString('<a href="/tentang" aria-current="page">Tentang</a>', $r->body);
        $this->assertStringContainsString('<a href="/tentang">Tentang</a>.', $this->tamu()->get('/')->body);
        foreach (['en' => ['Contact', 'Forgotten password'], 'ar' => ['اتصل بنا', 'نسيت كلمة المرور']] as $b => $judul) {
            $tamu = $this->tamu();
            $tamu->get('/bahasa/' . $b);
            $isi = $tamu->get('/tentang')->body;
            foreach ($judul as $j) {
                $this->assertStringContainsString('<h2>' . $j . '</h2>', $isi, $b);
            }
        }
    }

    /**
     * Tahap 10b: ketentuan dan privasi menyebut pencatatan ketikan tak wajar dalam tiga bahasa,
     * dan TIDAK menyebut pemeriksaan kemiripan kode — itu tidak dibangun (keputusan pemilik).
     */
    public function testKetentuanDanPrivasiMenyebutKetikanTakWajarDanTidakMenyebutKemiripan(): void
    {
        $harap = [
            'id' => ['/ketentuan' => 'jauh lebih cepat daripada ketikan orang', '/privasi' => 'dengan laju 150 karakter per detik atau lebih', 'tanggal' => 'Terakhir diperbarui: 22 November 2026'],
            'en' => ['/ketentuan' => 'far faster than a person can type', '/privasi' => 'at 150 characters per second or faster', 'tanggal' => 'Last updated: 22 November 2026'],
            'ar' => ['/ketentuan' => 'أسرع بكثير مما يكتبه إنسان', '/privasi' => 'بسرعة 150 حرفًا في الثانية أو أكثر', 'tanggal' => 'آخر تحديث: 22 نوفمبر 2026'],
        ];
        foreach ($harap as $bahasa => $h) {
            $tamu = $this->tamu();
            $tamu->get('/bahasa/' . $bahasa . '?kembali=' . rawurlencode('/'));
            foreach (['/ketentuan', '/privasi'] as $jalur) {
                $r = $tamu->get($jalur);
                $this->assertSame(200, $r->status, "{$bahasa} {$jalur}");
                $this->assertStringContainsString($h[$jalur], $r->body, "{$bahasa} {$jalur}");
                $this->assertStringContainsString($h['tanggal'], $r->body, "{$bahasa} {$jalur}");
                // Privasi juga mengatakan bahwa teman sekelas melihat nama penanya klarifikasi yang diterbitkan.
                if ($jalur === '/privasi') {
                    $this->assertStringContainsString(['id' => 'di samping pertanyaan klarifikasi Anda', 'en' => 'next to a clarification question you asked', 'ar' => 'بجانب سؤال الاستيضاح الذي طرحته'][$bahasa], $r->body, $bahasa);
                }
                foreach (['kemiripan', 'similarity', 'plagiar', 'تشابه'] as $bukan) {
                    $this->assertStringNotContainsString($bukan, mb_strtolower($r->body), "{$bahasa} {$jalur}: {$bukan}");
                }
            }
        }
    }

    public function testTigaBahasa(): void
    {
        $harap = [
            'en' => ['<html lang="en" dir="ltr">', ['/' => 'One account for all your learning applications', '/privasi' => 'Privacy policy', '/ketentuan' => 'Terms of use', '/tentang' => 'About Lokandita']],
            'ar' => ['<html lang="ar" dir="rtl">', ['/' => 'حساب واحد لكل تطبيقاتك التعليمية', '/privasi' => 'سياسة الخصوصية', '/ketentuan' => 'شروط الاستخدام', '/tentang' => 'عن Lokandita']],
        ];
        foreach ($harap as $bahasa => [$html, $halaman]) {
            $tamu = $this->tamu();
            $r    = $tamu->get('/bahasa/' . $bahasa . '?kembali=' . rawurlencode('/privasi'));
            $this->assertSame('https://lokandita.com/privasi', $r->header('Location'));
            foreach ($halaman as $jalur => $tajuk) {
                $r = $tamu->get($jalur);
                $this->assertSame(200, $r->status, "{$bahasa} {$jalur}");
                $this->assertStringContainsString($html, $r->body, "{$bahasa} {$jalur}");
                $this->assertStringContainsString('<h1>' . $tajuk . '</h1>', $r->body, "{$bahasa} {$jalur}");
                $this->assertDoesNotMatchRegularExpression('/\b(Portal|Lokandita)\.[a-zA-Z]+(\.[a-zA-Z0-9]+)*\b(?!\.com)/', strip_tags($r->body), "{$bahasa} {$jalur}: kunci bahasa yang tidak terjemah");
            }
        }
    }

    /**
     * Daftar aplikasi (Config\Aplikasi, docs/branding/LOKANDITA-BRAND.md): enam kartu dalam urutan
     * yang ditetapkan; LokaKode dan (sejak LokaUjian tahap U6) LokaUjian hidup dan bertaut, empat
     * lainnya "Segera" dan bukan tautan. Setiap
     * kartu punya ikon mereknya sendiri (terang dan gelap), nama, satu baris keterangan dan status.
     */
    public function testKartuAplikasiDiHalamanDepan(): void
    {
        $urutan = ['lokakode' => 'LokaKode', 'lokaujian' => 'LokaUjian', 'lokakelas' => 'LokaKelas', 'lokasiber' => 'LokaSiber', 'lokaretas' => 'LokaRetas', 'lokadata' => 'LokaData'];
        $status = ['id' => ['Tersedia', 'Segera'], 'en' => ['Available', 'Coming soon'], 'ar' => ['متاح', 'قريبًا']];
        foreach ($status as $bahasa => [$tersedia, $segera]) {
            $tamu = $this->tamu();
            $tamu->get('/bahasa/' . $bahasa . '?kembali=/');
            $r   = $tamu->get('/');
            $ket = (require APPPATH . "Language/{$bahasa}/Portal.php")['aplikasi'];
            $this->assertSame(array_keys($urutan), array_keys($ket), "{$bahasa}: satu keterangan per aplikasi, urutan yang sama");

            $this->assertSame(1, preg_match('#<ul class="petak petak-aplikasi">(.*?)</ul>#s', $r->body, $m), $bahasa);
            preg_match_all('#<li>\s*<(a|div) class="kartu-aplikasi[^"]*"([^>]*)data-aplikasi="([a-z]+)">(.*?)</\1>\s*</li>#s', $m[1], $kartu, PREG_SET_ORDER);
            $this->assertSame(array_keys($urutan), array_column($kartu, 3), "{$bahasa}: urutan kartu");
            foreach ($kartu as [, $tag, $atribut, $kode, $isi]) {
                $apa = "{$bahasa}/{$kode}";
                // Ikon merek aplikasi itu sendiri: 56 × 56, ragam gelap dan terang, hiasan (alt kosong).
                $this->assertSame(2, preg_match_all('#<source media="\(prefers-color-scheme: dark\)"[^>]*srcset="/merek/aplikasi/' . $kode . '-gelap\.(webp|png)\?v=#', $isi), $apa);
                $this->assertMatchesRegularExpression('#<img src="/merek/aplikasi/' . $kode . '-terang\.png\?v=[^"]*" alt="" width="56" height="56" loading="lazy" decoding="async">#', $isi, $apa);
                $this->assertStringContainsString('<strong>' . $urutan[$kode] . '</strong>', $isi, $apa);
                $this->assertStringContainsString('<span>' . htmlspecialchars($ket[$kode], ENT_QUOTES) . '</span>', $isi, $apa);
                $this->assertStringNotContainsString('<a ', $isi, $apa);
                if ($kode === 'lokakode' || $kode === 'lokaujian') {
                    $this->assertSame('a', $tag, $apa);
                    $this->assertStringContainsString('href="https://' . $kode . '.lokandita.com/"', $atribut, $apa);
                    $this->assertStringContainsString('<span class="lencana lencana-tersedia">' . $tersedia . '</span>', $isi, $apa);
                } else {
                    $this->assertSame('div', $tag, "{$apa} tidak boleh berupa tautan");
                    $this->assertStringNotContainsString('href', $atribut, $apa);
                    $this->assertStringContainsString('<span class="lencana lencana-segera">' . $segera . '</span>', $isi, $apa);
                }
            }
            $this->assertDoesNotMatchRegularExpression('/href="[^"]*(lokakelas|lokasiber|lokaretas|lokadata)/i', $r->body, $bahasa);
        }
        // Setiap ikon kartu dijawab, 112 × 112 (2× ukuran tampil), PNG dan WebP.
        foreach (array_keys($urutan) as $kode) {
            foreach (['terang', 'gelap'] as $ragam) {
                foreach (['png' => 'image/png', 'webp' => 'image/webp'] as $akhiran => $tipe) {
                    $b = $this->tamu()->get("/merek/aplikasi/{$kode}-{$ragam}.{$akhiran}");
                    $this->assertSame(200, $b->status, $kode);
                    $this->assertSame($tipe, $b->header('Content-Type'), $kode);
                    $this->assertSame([112, 112], array_slice(getimagesizefromstring($b->body), 0, 2), $kode);
                }
            }
        }
    }

    /**
     * Aplikasi tanpa ikon mendapat lingkaran netral seukuran — bukan logo karangan, bukan gambar
     * rusak. Tujuh merek sekarang semuanya punya ikon, jadi yang diuji potongan tampilannya sendiri
     * dan gaya yang disajikan; tidak ada halaman hidup yang menampilkannya.
     */
    public function testAplikasiTanpaIkonMendapatLingkaranNetral(): void
    {
        $ikon = null;
        ob_start();
        include APPPATH . 'Views/publik/_kartu_ikon.php';
        $html = trim((string) ob_get_clean());
        $this->assertSame('<span class="kartu-ikon kartu-ikon-kosong" aria-hidden="true"></span>', $html);
        $gaya = $this->tamu()->get('/aset/lokandita.css')->body;
        $this->assertMatchesRegularExpression('/\.kartu-ikon\{[^}]*width:3\.5rem[^}]*height:3\.5rem/', $gaya);
        $this->assertMatchesRegularExpression('/\.kartu-ikon-kosong\{[^}]*border-radius:50%/', $gaya);
    }

    /**
     * "LokaAuth" bukan nama untuk pengguna lagi: tidak ada di halaman publik mana pun dalam tiga
     * bahasa; halaman Tentang dan Privasi menyebut "Akun Lokandita" (id) dan padanannya.
     */
    public function testNamaLokaAuthTidakTampilLagi(): void
    {
        $pengganti = ['id' => 'Akun Lokandita', 'en' => 'Lokandita account', 'ar' => 'حساب Lokandita'];
        foreach ($pengganti as $bahasa => $nama) {
            $tamu = $this->tamu();
            $tamu->get('/bahasa/' . $bahasa . '?kembali=/');
            foreach (array_keys(self::HALAMAN) as $jalur) {
                $r = $tamu->get($jalur);
                $this->assertSame(200, $r->status, "{$bahasa}{$jalur}");
                $this->assertStringNotContainsStringIgnoringCase('LokaAuth', $r->body, "{$bahasa}{$jalur}");
                if ($jalur === '/tentang' || $jalur === '/privasi') {
                    $this->assertStringContainsString($nama, $r->body, "{$bahasa}{$jalur}");
                }
            }
            $isi = require APPPATH . "Language/{$bahasa}/Portal.php";
            array_walk_recursive($isi, function ($teks) use ($bahasa): void {
                $this->assertStringNotContainsStringIgnoringCase('LokaAuth', (string) $teks, $bahasa);
            });
        }
    }

    /**
     * Portal membawa merek induk. Gambar Open Graph-nya tetap potongan foto pembuka (keputusan
     * pemilik 2026-11-22), jadi tidak ada og-image.png dari berkas merek di sini.
     */
    public function testIkonLogoDanManifestPortal(): void
    {
        $csp = "default-src 'self'; script-src 'self' 'nonce-N'; style-src 'self'; img-src 'self' data:; font-src 'self'; connect-src 'self'; object-src 'none'; base-uri 'self'; frame-ancestors 'none'; form-action 'self' https://lokandita.com https://auth.lokandita.com";
        $this->periksaMerek($this->tamu(), '/', 'Lokandita', null, $csp);
        $r = $this->tamu()->get('/');
        $this->assertStringContainsString('<meta property="og:image" content="https://lokandita.com/merek/og-image.jpg">', $r->body);
        $this->assertSame(404, $this->tamu()->get('/merek/og-image.png')->status);
        $this->assertSame(404, $this->tamu()->get('/merek/apple-touch-icon.png')->status);
    }

    /**
     * Halaman publik tidak membaca sesi: dengan cookie karangan pun isinya sama dan tanpa menu akun.
     * (Halaman depan `/` sengaja lain: pembawa cookie dibawa ke /beranda — ClientAppTest.)
     */
    public function testHalamanPublikTidakMembacaSesi(): void
    {
        $bersih = $this->tamu()->get('/tentang')->body;
        $http   = $this->tamu();
        $http->cookies['lokandita_sid'] = str_repeat('A', 43);
        $r = $http->get('/tentang');
        $this->assertSame(200, $r->status);
        $this->assertSame($bersih, $r->body);

        // Juga bagi sesi yang hidup: halaman publik tetap halaman publik, tanpa nama pengguna.
        $user  = $this->user(['nama' => 'Nama Tidak Boleh Muncul']);
        $masuk = $this->sebagai($user);
        $r     = $masuk->get('/privasi');
        $this->assertSame(200, $r->status);
        $this->assertStringNotContainsString('Nama Tidak Boleh Muncul', $r->body);
        $this->assertStringNotContainsString($user['nomor_induk'], $r->body);
    }

    /**
     * CSP melarang CDN: tidak ada skrip, gaya, gambar atau bingkai dari host lain.
     */
    public function testTidakAdaAsetDariHostLain(): void
    {
        foreach (array_keys(self::HALAMAN) as $jalur) {
            $r = $this->tamu()->get($jalur);
            preg_match_all('#<(?:script|link|img|iframe|source)\b[^>]*?\b(?:src|href)="([^"]+)"#i', $r->body, $m);
            $this->assertNotSame([], $m[1], $jalur);
            foreach ($m[1] as $alamat) {
                $this->assertTrue(
                    (str_starts_with($alamat, '/') && ! str_starts_with($alamat, '//')) || str_starts_with($alamat, 'https://lokandita.com/'),
                    "{$jalur}: aset dari host lain: {$alamat}",
                );
            }
            // srcset (foto pembuka): setiap alamatnya juga dari host sendiri.
            preg_match_all('#\bsrcset="([^"]+)"#i', $r->body, $s);
            foreach ($s[1] as $daftar) {
                foreach (explode(',', $daftar) as $butir) {
                    $alamat = trim(explode(' ', trim($butir))[0]);
                    $this->assertTrue(str_starts_with($alamat, '/') && ! str_starts_with($alamat, '//'), "{$jalur}: srcset dari host lain: {$alamat}");
                }
            }
            $this->assertStringNotContainsString(' style="', $r->body, "{$jalur}: gaya sebaris");
            $this->assertStringNotContainsString('<style', $r->body, $jalur);
            $this->assertDoesNotMatchRegularExpression('/<script(?![^>]*\bsrc=)/', $r->body, "{$jalur}: skrip sebaris");
        }
        foreach (['/aset/lokandita.css', '/aset/lokandita.js', '/favicon.ico', '/merek/icon-192.png'] as $aset) {
            $this->assertSame(200, $this->tamu()->get($aset)->status, $aset);
        }
    }

    public function testPetaSitusDanRobots(): void
    {
        $r = $this->tamu()->get('/sitemap.xml');
        $this->assertSame(200, $r->status);
        $this->assertStringContainsString('xml', (string) $r->header('Content-Type'));
        preg_match_all('#<loc>([^<]+)</loc>#', $r->body, $m);
        $this->assertSame(['https://lokandita.com/', 'https://lokandita.com/tentang', 'https://lokandita.com/privasi', 'https://lokandita.com/ketentuan'], $m[1]);

        $r = $this->tamu()->get('/robots.txt');
        $this->assertSame(200, $r->status);
        $this->assertStringContainsString('text/plain', (string) $r->header('Content-Type'));
        $this->assertStringContainsString("Disallow: /beranda\n", $r->body);
        $this->assertStringContainsString("Disallow: /data-saya\n", $r->body);
        $this->assertStringContainsString('Sitemap: https://lokandita.com/sitemap.xml', $r->body);
        $this->assertStringNotContainsString("Disallow: /\n", $r->body);
    }

    public function testAlamatYangTidakAdaDijawab404(): void
    {
        foreach (['/tidak-ada', '/privasi/lagi', '/admin', '/internal/session/touch'] as $jalur) {
            $this->assertSame(404, $this->tamu()->get($jalur)->status, $jalur);
        }
    }
}
