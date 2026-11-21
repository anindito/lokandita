<?php

declare(strict_types=1);

namespace Tests;

use Tests\Support\PortalCase;

/**
 * Foto pembuka halaman depan yang bisa diganti superadmin (KONTRAK.md §16), lewat HTTP
 * sungguhan ke portal-tes dengan MySQL sungguhan (portal_test). Foto diolah oleh GD yang
 * sebenarnya di dalam kontainer portal-tes, dan hasilnya diunduh lagi lewat /media/.
 */
final class FotoAdminTest extends PortalCase
{
    private const BAWAAN = '/merek/hero/hero-1200.jpg';

    private function jumlah(string $tabel, string $di = ''): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM {$tabel} {$di}")->fetch_row()[0];
    }

    private function kunci(int $id): string
    {
        return (string) $this->db->query("SELECT kunci FROM foto WHERE id = {$id}")->fetch_row()[0];
    }

    /** Kode galat berkas pada halaman terakhir, atau ''. */
    private function galatBerkas(string $badan): string
    {
        return preg_match('/data-galat-berkas="([a-z_]+)"/', $badan, $m) === 1 ? $m[1] : '';
    }

    /**
     * JPEG dengan EXIF sungguhan: pembuat kamera dan koordinat GPS.
     */
    private function jpegBerGps(): string
    {
        $ascii = static fn (string $s): string => $s . "\0";
        $rasio = static fn (int ...$n): string => pack('V*', ...$n);
        // IFD0: Make, penunjuk ke IFD GPS. Lalu IFD GPS: lintang dan bujur.
        $make   = $ascii('KameraUji-Rahasia');
        $lat    = $rasio(6, 1, 12, 1, 3015, 100);
        $lon    = $rasio(106, 1, 50, 1, 4520, 100);
        $ifd0   = 8;
        $akhir0 = $ifd0 + 2 + 2 * 12 + 4;
        $oMake  = $akhir0;
        $oGps   = $oMake + strlen($make);
        $akhirG = $oGps + 2 + 4 * 12 + 4;
        $oLat   = $akhirG;
        $oLon   = $oLat + strlen($lat);
        $entri  = static fn (int $tag, int $jenis, int $jumlah, string $nilai): string => pack('vvV', $tag, $jenis, $jumlah) . str_pad($nilai, 4, "\0");
        $tiff   = "II*\0" . pack('V', $ifd0)
            . pack('v', 2) . $entri(0x010F, 2, strlen($make), pack('V', $oMake)) . $entri(0x8825, 4, 1, pack('V', $oGps)) . pack('V', 0)
            . $make
            . pack('v', 4) . $entri(1, 2, 2, "S\0") . $entri(2, 5, 3, pack('V', $oLat)) . $entri(3, 2, 2, "E\0") . $entri(4, 5, 3, pack('V', $oLon)) . pack('V', 0)
            . $lat . $lon;
        $app1   = "Exif\0\0" . $tiff;
        $jpeg   = $this->jpeg();
        $this->assertSame("\xFF\xD8", substr($jpeg, 0, 2));

        return "\xFF\xD8\xFF\xE1" . pack('n', strlen($app1) + 2) . $app1 . substr($jpeg, 2);
    }

    /**
     * Uji hak akses: hanya superadmin. Dosen dan mahasiswa mendapat 403 di halaman DAN di
     * titik akhirnya, juga dengan foto dan isian yang sah; tanpa sesi ke login; tanpa CSRF 403.
     */
    public function testHanyaSuperadmin(): void
    {
        [$admin] = $this->admin();
        $id      = $this->unggahFoto($admin, $this->jpeg());
        $get     = ['/admin/teks/foto', '/admin/teks/foto/' . $id];
        foreach ($get as $jalur) {
            $r = $this->tamu()->get($jalur);
            $this->assertSame('https://auth.lokandita.com/login?next=' . rawurlencode('https://lokandita.com' . $jalur), $r->header('Location'), $jalur);
        }
        // Tanpa sesi: ditolak CSRF (403) atau dibawa ke login; yang penting tidak ada yang tersimpan (diperiksa di bawah).
        $this->assertContains($this->tamu()->upload('/admin/teks/foto', $this->isianFoto('x'), 'foto', 'a.jpg', $this->jpeg())->status, [302, 303, 403], 'tamu mengunggah');

        foreach (['dosen', 'mahasiswa'] as $peran) {
            $http = $this->sebagai($this->user(['global_role' => $peran]));
            $csrf = (string) $http->get('/beranda')->input('csrf');
            $this->assertNotSame('', $csrf);
            foreach ($get as $jalur) {
                $this->assertSame(403, $http->get($jalur)->status, "{$peran} GET {$jalur}");
            }
            $this->assertSame(403, $http->upload('/admin/teks/foto', $this->isianFoto($csrf, 'DIBAJAK'), 'foto', 'a.jpg', $this->jpeg())->status, "{$peran} mengunggah");
            $this->assertSame(403, $http->post("/admin/teks/foto/{$id}/tayang", ['csrf' => $csrf])->status, "{$peran} menayangkan");
            $this->assertSame(403, $http->post('/admin/teks/foto/bawaan', ['csrf' => $csrf])->status, "{$peran} ke bawaan");
        }
        $this->assertSame(1, $this->jumlah('foto'), 'hanya foto milik superadmin');
        $this->assertSame(0, $this->jumlah('foto_tayang'));
        $this->assertStringContainsString(self::BAWAAN, $this->tamu()->get('/')->body);

        // Superadmin tanpa CSRF: ditolak, tidak ada yang berubah.
        $tanpa = $this->isianFoto('');
        unset($tanpa['csrf']);
        $this->assertSame(403, $admin->upload('/admin/teks/foto', $tanpa, 'foto', 'a.jpg', $this->jpeg())->status);
        $this->assertSame(403, $admin->post("/admin/teks/foto/{$id}/tayang")->status);
        $this->assertSame(403, $admin->post('/admin/teks/foto/bawaan')->status);
        $this->assertSame(1, $this->jumlah('foto'));
        $this->assertSame(0, $this->jumlah('foto_tayang'));

        // Halaman superadmin: tidak diindeks, tidak di-cache; foto yang tidak ada 404.
        foreach ($get as $jalur) {
            $r = $admin->get($jalur);
            $this->assertSame(200, $r->status, $jalur);
            $this->assertStringContainsString('<meta name="robots" content="noindex, nofollow">', $r->body, $jalur);
            $this->assertStringContainsString('no-store', (string) $r->header('Cache-Control'), $jalur);
        }
        $this->assertStringContainsString('data-ke-foto', $admin->get('/admin/teks')->body, 'tautan foto di halaman penyuntingan');
        $this->assertSame(404, $admin->get('/admin/teks/foto/999999')->status);
        $this->assertSame(404, $admin->post('/admin/teks/foto/999999/tayang', ['csrf' => $this->csrf($admin, '/admin/teks/foto')])->status);
    }

    /**
     * Jenis berkas ditentukan dari ISInya. Berkas PHP yang dinamai .jpg ditolak; begitu
     * juga GIF, SVG dan teks biasa. Tidak ada baris dan tidak ada yang tayang.
     */
    public function testBerkasPhpBergantiNamaDitolak(): void
    {
        [$admin] = $this->admin();
        $csrf    = $this->csrf($admin, '/admin/teks/foto');
        $gif     = (static function (): string {
            ob_start();
            imagegif(imagecreatetruecolor(1800, 1200));

            return (string) ob_get_clean();
        })();
        $palsu = [
            'foto.jpg'      => "<?php echo 'DIJALANKAN'; system(\$_GET['c']); ?>\n",
            'kepala.jpg'    => "\xFF\xD8\xFF\xE0<?php echo 'DIJALANKAN'; ?>",
            'animasi.gif'   => $gif,
            'gif-jadi.jpg'  => $gif,
            'vektor.svg'    => '<svg xmlns="http://www.w3.org/2000/svg" width="2000" height="1500"><script>alert(1)</script></svg>',
            'svg-jadi.png'  => '<?xml version="1.0"?><svg xmlns="http://www.w3.org/2000/svg" width="2000" height="1500"></svg>',
            'catatan.webp'  => str_repeat('bukan gambar ', 500),
        ];
        foreach ($palsu as $nama => $isi) {
            $r = $admin->upload('/admin/teks/foto', $this->isianFoto($csrf), 'foto', $nama, $isi);
            $this->assertSame(422, $r->status, $nama);
            $this->assertContains($this->galatBerkas($r->body), ['bukan_gambar'], $nama);
            $this->assertStringNotContainsString('DIJALANKAN', $r->body, $nama);
        }
        $this->assertSame(0, $this->jumlah('foto'));
        $this->assertSame(0, $this->jumlah('audit_log'));

        // Direktori foto murni statis: tidak ada daftar isi, dan nama yang bukan buatan server tidak dijawab.
        foreach (['/media/', '/media/hero/', '/media/hero/foto.php', '/media/hero/foto.jpg', '/media/hero/.htaccess', '/media/hero/' . str_repeat('a', 32) . '-480.php'] as $jalur) {
            $r = $this->tamu()->get($jalur);
            $this->assertContains($r->status, [403, 404], $jalur);
            $this->assertStringNotContainsString('DIJALANKAN', $r->body, $jalur);
        }
    }

    /**
     * JPEG yang sah dengan PHP ditempelkan di belakangnya diterima sebagai foto — tetapi yang
     * disajikan hanya hasil olahan, yang tidak memuat tempelan itu. Nama aslinya hanya teks.
     */
    public function testBerkasAsliTidakPernahDisajikan(): void
    {
        [$admin] = $this->admin();
        $racun   = "<?php echo 'DIJALANKAN'; ?>TEMPELAN-RAHASIA";
        $id      = $this->unggahFoto($admin, $this->jpeg() . $racun, 'Fotografer <script>alert(1)</script>', '../../../public/index.php');
        $baris   = $this->db->query("SELECT * FROM foto WHERE id = {$id}")->fetch_assoc();
        $this->assertMatchesRegularExpression('/^[0-9a-f]{32}$/', $baris['kunci'], 'nama berkas dibuat server');
        $this->assertStringNotContainsString('/', $baris['nama_asli']);
        $this->assertStringContainsString('index.php', $baris['nama_asli'], 'nama asli disimpan sebagai teks');
        $this->assertSame('jpeg', $baris['jenis']);
        $this->assertSame(['1600', '1067'], [$baris['lebar'], $baris['tinggi']]);

        $jenis = ['avif' => 'image/avif', 'webp' => 'image/webp', 'jpg' => 'image/jpeg'];
        foreach ([480, 768, 1200, 1600] as $lebar) {
            foreach ($jenis as $akhiran => $mime) {
                $jalur = "/media/hero/{$baris['kunci']}-{$lebar}.{$akhiran}";
                $r     = $this->tamu()->get($jalur);
                $this->assertSame(200, $r->status, $jalur);
                $this->assertSame($mime, $r->header('Content-Type'), $jalur);
                $this->assertSame('nosniff', $r->header('X-Content-Type-Options'), $jalur);
                $this->assertStringContainsString('immutable', (string) $r->header('Cache-Control'), $jalur);
                $this->assertNull($r->header('Set-Cookie'), "{$jalur}: berkas statis, bukan PHP");
                $this->assertStringNotContainsString('<?php', $r->body, $jalur);
                $this->assertStringNotContainsString('TEMPELAN-RAHASIA', $r->body, $jalur);
                $ukuran = getimagesizefromstring($r->body);
                $this->assertNotFalse($ukuran, "{$jalur}: gambar yang sah");
                $this->assertSame($lebar, $ukuran[0], $jalur);
            }
        }
        // Tidak ada alamat yang memberikan berkas dengan nama aslinya.
        foreach (['/media/hero/index.php', '/media/hero/foto%20uji.jpg', "/media/hero/{$baris['kunci']}.jpg", "/media/hero/{$baris['kunci']}-1600.php"] as $jalur) {
            $this->assertContains($this->tamu()->get($jalur)->status, [403, 404], $jalur);
        }

        // Sumber yang berisi HTML tampil sebagai teks, di pratinjau dan di kaki halaman depan.
        $this->assertContains($admin->post("/admin/teks/foto/{$id}/tayang", ['csrf' => $this->csrf($admin, '/admin/teks/foto')])->status, [302, 303]);
        $depan = $this->tamu()->get('/')->body;
        $this->assertStringContainsString('Fotografer &lt;script&gt;alert(1)&lt;/script&gt;', $depan);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $depan);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $admin->get('/admin/teks/foto')->body);
    }

    /**
     * Foto dengan EXIF dan koordinat GPS keluar tanpa keduanya, dalam ketiga format.
     */
    public function testExifDanGpsDibuang(): void
    {
        $asli = $this->jpegBerGps();
        $exif = exif_read_data('data://image/jpeg;base64,' . base64_encode($asli));
        $this->assertIsArray($exif, 'berkas uji memang punya EXIF');
        $this->assertSame('KameraUji-Rahasia', $exif['Make'] ?? null);
        $this->assertArrayHasKey('GPSLatitude', $exif, 'berkas uji memang punya koordinat GPS');
        $this->assertArrayHasKey('GPSLongitude', $exif);

        [$admin] = $this->admin();
        $kunci   = $this->kunci($this->unggahFoto($admin, $asli));
        foreach (['avif', 'webp', 'jpg'] as $akhiran) {
            foreach ([480, 1600] as $lebar) {
                $isi = $this->tamu()->get("/media/hero/{$kunci}-{$lebar}.{$akhiran}");
                $this->assertSame(200, $isi->status);
                $apa = "{$lebar}.{$akhiran}";
                $this->assertStringNotContainsString('KameraUji', $isi->body, $apa);
                $this->assertStringNotContainsStringIgnoringCase('exif', $isi->body, $apa);
                $this->assertStringNotContainsString('GPS', $isi->body, $apa);
            }
        }
        $hasil = @exif_read_data('data://image/jpeg;base64,' . base64_encode($this->tamu()->get("/media/hero/{$kunci}-1600.jpg")->body));
        $this->assertFalse(is_array($hasil) && (isset($hasil['Make']) || isset($hasil['GPSLatitude']) || isset($hasil['GPSLongitude'])), 'JPEG hasil tidak membawa EXIF kamera atau GPS');
    }

    /**
     * Batas: 10 MB, jumlah piksel (dibaca dari kepala berkas, sebelum gambar dibuka), ukuran
     * terkecil, dan isian yang wajib — masing-masing dengan pesan yang menyebut sebabnya.
     */
    public function testBatasDanIsianWajib(): void
    {
        [$admin] = $this->admin();
        $csrf    = $this->csrf($admin, '/admin/teks/foto');
        $kirim   = fn (string $isi, ?array $isian = null) => $admin->upload('/admin/teks/foto', $isian ?? $this->isianFoto($csrf), 'foto', 'foto.jpg', $isi);

        // Terlalu kecil: pesannya menyebut ukuran terkecil.
        $r = $kirim($this->jpeg(1200, 800));
        $this->assertSame([422, 'terlalu_kecil'], [$r->status, $this->galatBerkas($r->body)]);
        $this->assertStringContainsString('1600 × 900', $r->body);
        $r = $kirim($this->jpeg(1600, 600));
        $this->assertSame('terlalu_kecil', $this->galatBerkas($r->body), 'cukup lebar tetapi terlalu pendek');

        // PNG yang kepalanya mengaku 20000 × 20000 piksel dan tidak punya isi: ditolak dari kepalanya saja.
        $ihdr = 'IHDR' . pack('NNCCCCC', 20000, 20000, 8, 2, 0, 0, 0);
        $bom  = "\x89PNG\r\n\x1a\n" . pack('N', 13) . $ihdr . pack('N', crc32($ihdr)) . pack('N', 0) . 'IEND' . pack('N', crc32('IEND'));
        $r    = $kirim($bom);
        $this->assertSame([422, 'terlalu_banyak_piksel'], [$r->status, $this->galatBerkas($r->body)]);

        // Lebih dari 10 MB.
        $r = $kirim($this->jpeg() . random_bytes(10 * 1024 * 1024));
        $this->assertSame([422, 'terlalu_besar'], [$r->status, $this->galatBerkas($r->body)]);
        $this->assertStringContainsString('10 MB', $r->body);

        // JPEG yang terpotong di tengah: kepalanya sah, isinya tidak bisa dibuka.
        $r = $kirim(substr($this->jpeg(), 0, 700));
        $this->assertSame(422, $r->status);
        $this->assertContains($this->galatBerkas($r->body), ['tidak_terbaca', 'bukan_gambar']);

        // Tanpa berkas.
        $r = $admin->post('/admin/teks/foto', $this->isianFoto($csrf));
        $this->assertSame([422, 'kosong'], [$r->status, $this->galatBerkas($r->body)]);

        // Isian wajib: tiap teks alternatif, sumber, lisensi dan centang hak pakai.
        foreach (['alt_id', 'alt_en', 'alt_ar', 'sumber', 'lisensi', 'berhak'] as $nama) {
            $isian = $this->isianFoto($csrf);
            if ($nama === 'berhak') {
                unset($isian[$nama]);
            } else {
                $isian[$nama] = '   ';
            }
            $r = $kirim($this->jpeg(), $isian);
            $this->assertSame(422, $r->status, "tanpa {$nama}");
            $this->assertMatchesRegularExpression('/aria-describedby="g-' . $nama . '"/', $r->body, "tanpa {$nama}: isian itu ditandai");
        }
        $isian           = $this->isianFoto($csrf);
        $isian['alt_en'] = str_repeat('a', 301);
        $this->assertSame(422, $kirim($this->jpeg(), $isian)->status, 'teks alternatif terlalu panjang');

        $this->assertSame(0, $this->jumlah('foto'), 'tidak ada yang tersimpan');
        $this->assertSame(0, $this->jumlah('audit_log'));
        $this->assertStringContainsString(self::BAWAAN, $this->tamu()->get('/')->body);

        // PNG dan WebP yang sah diterima.
        foreach (['png' => 'imagepng', 'webp' => 'imagewebp'] as $jenis => $tulis) {
            $g = imagecreatetruecolor(1600, 900);
            imagefill($g, 0, 0, (int) imagecolorallocate($g, 20, 120, 90));
            ob_start();
            $tulis($g);
            $id = $this->unggahFoto($admin, (string) ob_get_clean(), 'Sumber ' . $jenis, 'foto.' . $jenis);
            $this->assertSame($jenis, $this->db->query("SELECT jenis FROM foto WHERE id = {$id}")->fetch_row()[0]);
        }
    }

    /**
     * Tanpa unggahan yang tayang, foto bawaan yang tampil. Mengunggah belum mengubah halaman
     * publik; menayangkan mengubahnya; foto sebelumnya bisa ditayangkan lagi; dan foto bawaan
     * bisa dikembalikan. Setiap unggahan, penayangan dan pemulihan tercatat di log audit.
     */
    public function testUnggahPratinjauTayangPulihkan(): void
    {
        [$admin, $user] = $this->admin('Admin Foto');
        $bawaan         = $this->tamu()->get('/');
        $this->assertSame(200, $bawaan->status);
        $this->assertStringContainsString('src="' . self::BAWAAN, $bawaan->body, 'tanpa unggahan: foto bawaan');
        $this->assertStringContainsString('Foto: Josh Sorenson / Pexels', $bawaan->body, 'kredit foto bawaan di kaki');
        $this->assertStringContainsString('alt="Deretan komputer', $bawaan->body);
        foreach (['/merek/hero/hero-480.avif' => 'image/avif', '/merek/hero/hero-768.webp' => 'image/webp', self::BAWAAN => 'image/jpeg', '/merek/hero/hero-1600.jpg' => 'image/jpeg', '/merek/og-image.jpg' => 'image/jpeg'] as $jalur => $mime) {
            $r = $this->tamu()->get($jalur);
            $this->assertSame([200, $mime], [$r->status, $r->header('Content-Type')], $jalur);
        }

        // Unggah A: tersimpan, belum tayang; pratinjau memakai tata letak halaman depan, id dan ar.
        $a  = $this->unggahFoto($admin, $this->jpeg(1800, 1200, 200), 'Fotografer A');
        $ka = $this->kunci($a);
        $this->assertSame($bawaan->body, $this->tamu()->get('/')->body, 'unggahan belum mengubah halaman publik');
        $r = $admin->get('/admin/teks/foto/' . $a);
        $this->assertSame(200, $r->status);
        foreach (['id', 'ar'] as $b) {
            $this->assertSame(1, preg_match('#<div dir="(ltr|rtl)" lang="' . $b . '" data-pratinjau="' . $b . '">(.*?)</section>#s', $r->body, $m), "pratinjau {$b}");
            $this->assertSame($b === 'ar' ? 'rtl' : 'ltr', $m[1]);
            $this->assertStringContainsString('<section class="pembuka">', $m[2], "pratinjau {$b}: tata letak halaman depan");
            $this->assertStringContainsString("/media/hero/{$ka}-1200.jpg", $m[2], "pratinjau {$b}: foto yang diunggah");
            $this->assertStringContainsString('Fotografer A', $m[2], "pratinjau {$b}: kredit");
        }
        $this->assertStringContainsString('alt="نص بديل عربي Fotografer A"', $r->body);
        $this->assertStringContainsString('action="/admin/teks/foto/' . $a . '/tayang"', $r->body);

        // Tayangkan A: halaman depan memakai foto A, teks alternatif per bahasa, kredit dari isiannya.
        $csrf = $this->csrf($admin, '/admin/teks/foto');
        $this->assertSame('https://lokandita.com/admin/teks/foto?pesan=tayang', $admin->post("/admin/teks/foto/{$a}/tayang", ['csrf' => $csrf])->header('Location'));
        $depan = $this->tamu()->get('/');
        $this->assertStringContainsString("src=\"/media/hero/{$ka}-1200.jpg\"", $depan->body);
        $this->assertStringContainsString("/media/hero/{$ka}-480.avif 480w", $depan->body);
        $this->assertStringContainsString("/media/hero/{$ka}-1600.webp 1600w", $depan->body);
        $this->assertStringContainsString('width="1600" height="1067"', $depan->body);
        $this->assertStringContainsString('alt="Teks alternatif Indonesia untuk Fotografer A"', $depan->body);
        $this->assertStringContainsString('Foto: Fotografer A', $depan->body);
        $this->assertStringNotContainsString('Josh Sorenson', $depan->body);
        $this->assertStringNotContainsString('/merek/hero/', $depan->body);
        foreach (['en' => ['English alternative text for Fotografer A', 'Photo: Fotografer A'], 'ar' => ['نص بديل عربي Fotografer A', 'الصورة: Fotografer A']] as $b => [$alt, $kredit]) {
            $tamu = $this->tamu();
            $tamu->get('/bahasa/' . $b);
            $isi = $tamu->get('/')->body;
            $this->assertStringContainsString('alt="' . $alt . '"', $isi, $b);
            $this->assertStringContainsString($kredit, $isi, $b);
        }
        // Menayangkan foto yang sedang tayang tidak mengubah dan tidak mencatat apa pun.
        $this->assertSame('https://lokandita.com/admin/teks/foto?pesan=sudah-tayang', $admin->post("/admin/teks/foto/{$a}/tayang", ['csrf' => $csrf])->header('Location'));

        // Unggah dan tayangkan B: A tetap tersimpan.
        $b  = $this->unggahFoto($admin, $this->jpeg(2000, 1500, 10), 'Fotografer B');
        $kb = $this->kunci($b);
        $admin->post("/admin/teks/foto/{$b}/tayang", ['csrf' => $csrf]);
        $depan = $this->tamu()->get('/')->body;
        $this->assertStringContainsString("/media/hero/{$kb}-1200.jpg", $depan);
        $this->assertStringContainsString('width="1600" height="1200"', $depan);
        $this->assertStringNotContainsString($ka, $depan);
        $this->assertSame(200, $this->tamu()->get("/media/hero/{$ka}-1600.jpg")->status, 'berkas foto A tetap ada');

        // Pulihkan A: foto yang lebih awal tayang lagi.
        $daftar = $admin->get('/admin/teks/foto')->body;
        $this->assertStringContainsString('action="/admin/teks/foto/' . $a . '/tayang"', $daftar, 'foto lama bisa ditayangkan lagi dari daftar');
        $this->assertSame(1, preg_match('#data-foto="' . $b . '">.*?data-tayang#s', $daftar), 'B bertanda sedang tayang');
        $admin->post("/admin/teks/foto/{$a}/tayang", ['csrf' => $csrf]);
        $depan = $this->tamu()->get('/')->body;
        $this->assertStringContainsString("/media/hero/{$ka}-1200.jpg", $depan, 'foto A tayang lagi');
        $this->assertStringContainsString('Foto: Fotografer A', $depan);
        $this->assertStringNotContainsString($kb, $depan);

        // Kembali ke foto bawaan; sekali lagi tidak mengubah apa pun.
        $this->assertSame('https://lokandita.com/admin/teks/foto?pesan=bawaan', $admin->post('/admin/teks/foto/bawaan', ['csrf' => $csrf])->header('Location'));
        $this->assertSame($bawaan->body, $this->tamu()->get('/')->body, 'kembali persis ke halaman dengan foto bawaan');
        $this->assertSame('https://lokandita.com/admin/teks/foto?pesan=sudah-bawaan', $admin->post('/admin/teks/foto/bawaan', ['csrf' => $csrf])->header('Location'));
        $this->assertSame(2, $this->jumlah('foto'), 'tidak ada foto yang dihapus');

        // Log audit: tiap unggahan, penayangan, pemulihan dan kembali-ke-bawaan, oleh superadmin itu.
        $audit = array_column($this->db->query('SELECT action FROM audit_log ORDER BY id')->fetch_all(MYSQLI_ASSOC), 'action');
        $this->assertSame(['foto.diunggah', 'foto.ditayangkan', 'foto.diunggah', 'foto.ditayangkan', 'foto.dipulihkan', 'foto.bawaan'], $audit);
        $this->assertSame(6, $this->jumlah('audit_log', "WHERE actor_id = '{$user['id']}' AND halaman = 'foto' AND bahasa IS NULL"));
        $pulih = $this->db->query("SELECT sebelum, sesudah FROM audit_log WHERE action = 'foto.dipulihkan'")->fetch_assoc();
        $this->assertSame('Fotografer B', json_decode($pulih['sebelum'], true)['sumber']);
        $this->assertSame('Fotografer A', json_decode($pulih['sesudah'], true)['sumber']);
        $riwayat = $admin->get('/admin/teks/riwayat');
        $this->assertSame(200, $riwayat->status);
        $this->assertSame(6, preg_match_all('/data-riwayat="foto\./', $riwayat->body));
        $this->assertStringContainsString('Admin Foto', $riwayat->body);
        $this->assertStringContainsString('sumber: Fotografer A', $riwayat->body);
    }

    /**
     * Halaman depan tidak boleh gagal karena foto: bila tabelnya tidak terbaca, foto bawaan yang tampil.
     */
    public function testBasisDataTidakTerbacaMemakaiFotoBawaan(): void
    {
        [$admin] = $this->admin();
        $id      = $this->unggahFoto($admin, $this->jpeg());
        $admin->post("/admin/teks/foto/{$id}/tayang", ['csrf' => $this->csrf($admin, '/admin/teks/foto')]);
        $this->assertStringContainsString('/media/hero/', $this->tamu()->get('/')->body);

        $this->db->query('RENAME TABLE foto_tayang TO foto_tayang_hilang');
        try {
            $r = $this->tamu()->get('/');
            $this->assertSame(200, $r->status);
            $this->assertStringContainsString('src="' . self::BAWAAN, $r->body);
            $this->assertStringContainsString('Foto: Josh Sorenson / Pexels', $r->body);
        } finally {
            $this->db->query('RENAME TABLE foto_tayang_hilang TO foto_tayang');
        }
        $this->assertStringContainsString('/media/hero/', $this->tamu()->get('/')->body);
    }
}
