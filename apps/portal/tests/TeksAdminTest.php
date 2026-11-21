<?php

declare(strict_types=1);

namespace Tests;

use Tests\Support\PortalCase;

/**
 * Penyuntingan teks halaman publik oleh superadmin (KONTRAK.md §15), lewat HTTP sungguhan
 * ke portal-tes dengan MySQL sungguhan (portal_test).
 */
final class TeksAdminTest extends PortalCase
{
    private const RUTE_GET = ['/admin/teks', '/admin/teks/riwayat', '/admin/teks/tentang/id', '/admin/teks/depan/ar'];

    private function jumlah(string $tabel): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM {$tabel}")->fetch_row()[0];
    }

    /**
     * Uji hak akses: hanya superadmin. Dosen dan mahasiswa 403 di halaman DAN di titik akhirnya,
     * juga dengan isian yang sah; tanpa sesi ke login; tidak ada yang tersimpan.
     */
    public function testHanyaSuperadmin(): void
    {
        foreach (self::RUTE_GET as $jalur) {
            $r = $this->tamu()->get($jalur);
            $this->assertSame('https://auth.lokandita.com/login?next=' . rawurlencode('https://lokandita.com' . $jalur), $r->header('Location'), $jalur);
        }
        $isian = $this->isianBawaan('tentang');
        $isian['judul'] = 'DIBAJAK';
        foreach (['dosen', 'mahasiswa'] as $peran) {
            $http = $this->sebagai($this->user(['global_role' => $peran]));
            $csrf = (string) $http->get('/beranda')->input('csrf');
            $this->assertNotSame('', $csrf);
            $this->assertStringNotContainsString('/admin/teks', $http->body, "{$peran}: tidak ada kartu penyuntingan");
            foreach (self::RUTE_GET as $jalur) {
                $this->assertSame(403, $http->get($jalur)->status, "{$peran} GET {$jalur}");
            }
            foreach (['simpan', 'pratinjau'] as $aksi) {
                $this->assertSame(403, $http->post('/admin/teks/tentang/id', ['csrf' => $csrf, 'aksi' => $aksi, 't' => $isian])->status, "{$peran} {$aksi}");
            }
            $this->assertSame(403, $http->post('/admin/teks/tentang/id/bawaan', ['csrf' => $csrf])->status, "{$peran} bawaan");
        }
        $this->assertSame(0, $this->jumlah('teks'));
        $this->assertSame(0, $this->jumlah('audit_log'));
        $this->assertStringNotContainsString('DIBAJAK', $this->tamu()->get('/tentang')->body);

        [$admin] = $this->admin();
        foreach (self::RUTE_GET as $jalur) {
            $r = $admin->get($jalur);
            $this->assertSame(200, $r->status, "superadmin GET {$jalur}");
            $this->assertStringContainsString('<meta name="robots" content="noindex, nofollow">', $r->body, $jalur);
            $this->assertStringContainsString('no-store', (string) $r->header('Cache-Control'), $jalur);
        }
        $this->assertStringContainsString('href="/admin/teks"', $admin->get('/beranda')->body, 'kartu penyuntingan di beranda superadmin');
        // Tanpa CSRF ditolak; halaman atau bahasa yang tidak dikenal 404.
        $this->assertSame(403, $admin->post('/admin/teks/tentang/id', ['aksi' => 'simpan', 't' => $isian])->status);
        foreach (['/admin/teks/beranda/id', '/admin/teks/kontak/id', '/admin/teks/nav/id', '/admin/teks/tentang/fr', '/admin/teks/tentang'] as $jalur) {
            $this->assertSame(404, $admin->get($jalur)->status, $jalur);
        }
        $this->assertSame(404, $admin->post('/admin/teks/data/id', ['csrf' => $this->csrf($admin), 'aksi' => 'simpan', 't' => $isian])->status);
        $this->assertSame(0, $this->jumlah('teks'));
    }

    public function testPratinjauTidakMenyimpanDanSimpanLangsungTayang(): void
    {
        [$admin, $user] = $this->admin('Admin Penyunting');
        $surel = (string) getenv('CONTACT_EMAIL');
        $isian = $this->isianBawaan('tentang');
        $isian['judul']   = 'Tentang Lokandita versi baru';
        $isian['pembuka'] = "Paragraf pembuka yang baru.\n\n   \nParagraf kedua, hubungi {email}.\n<script>alert(1)</script> & <b>tebal</b>";
        $isian['bagian'][0]['butir'] = "Butir satu\nButir dua";

        // Pratinjau: tampil di halaman penyuntingan, tidak ada yang tersimpan, halaman publik tetap.
        $csrf = $this->csrf($admin);
        $r    = $admin->post('/admin/teks/tentang/id', ['csrf' => $csrf, 'aksi' => 'pratinjau', 't' => $isian]);
        $this->assertSame(200, $r->status);
        $this->assertSame(1, preg_match('/<div dir="ltr" lang="id" data-pratinjau>(.*?)<\/section>/s', $r->body, $m), 'bagian pratinjau');
        $this->assertStringContainsString('<h1>Tentang Lokandita versi baru</h1>', $m[1]);
        $this->assertStringContainsString('<li>Butir dua</li>', $m[1]);
        // Form tetap berisi apa yang diketik, siap disimpan.
        $this->assertStringContainsString('value="Tentang Lokandita versi baru"', $r->body);
        $this->assertSame(0, $this->jumlah('teks'));
        $this->assertSame(0, $this->jumlah('audit_log'));
        $this->assertStringContainsString('<h1>Tentang Lokandita</h1>', $this->tamu()->get('/tentang')->body);

        // Simpan: langsung tayang.
        $r = $admin->post('/admin/teks/tentang/id', ['csrf' => $csrf, 'aksi' => 'simpan', 't' => $isian]);
        $this->assertSame('https://lokandita.com/admin/teks?pesan=tersimpan', $r->header('Location'));
        $publik = $this->tamu()->get('/tentang');
        $this->assertSame(200, $publik->status);
        $this->assertStringContainsString('<h1>Tentang Lokandita versi baru</h1>', $publik->body);
        $this->assertStringContainsString('<title>Tentang Lokandita versi baru — Lokandita</title>', $publik->body);
        $this->assertStringContainsString('<p>Paragraf pembuka yang baru.</p>', $publik->body);
        // Baris kosong diabaikan; {email} menjadi alamat kontak dari CONTACT_EMAIL.
        $this->assertStringContainsString('<p>Paragraf kedua, hubungi <a href="mailto:' . $surel . '">' . $surel . '</a>.</p>', $publik->body);
        // HTML yang diketik tampil sebagai teks dan tidak pernah dijalankan.
        $this->assertStringContainsString('<p>&lt;script&gt;alert(1)&lt;/script&gt; &amp; &lt;b&gt;tebal&lt;/b&gt;</p>', $publik->body);
        $this->assertStringNotContainsString('<script>alert(1)', $publik->body);
        $this->assertStringNotContainsString('<b>tebal', $publik->body);
        $this->assertStringContainsString('<li>Butir satu</li>', $publik->body);
        // Halaman lain dan bahasa lain tidak ikut berubah.
        $this->assertStringContainsString('<h1>Kebijakan privasi</h1>', $this->tamu()->get('/privasi')->body);
        $en = $this->tamu();
        $en->get('/bahasa/en?kembali=/tentang');
        $this->assertStringContainsString('<h1>About Lokandita</h1>', $en->get('/tentang')->body);

        // Audit: satu baris dengan teks sebelum (bawaan) dan sesudah.
        $audit = $this->db->query('SELECT actor_id, action, halaman, bahasa, sebelum, sesudah FROM audit_log')->fetch_all(MYSQLI_ASSOC);
        $this->assertCount(1, $audit);
        $this->assertSame([$user['id'], 'teks.diubah', 'tentang', 'id'], [$audit[0]['actor_id'], $audit[0]['action'], $audit[0]['halaman'], $audit[0]['bahasa']]);
        $this->assertSame('Tentang Lokandita', json_decode($audit[0]['sebelum'], true)['judul']);
        $this->assertSame('Tentang Lokandita versi baru', json_decode($audit[0]['sesudah'], true)['judul']);

        // Daftar: siapa dan kapan; terjemahan Inggris dan Arab ditandai lebih lama dari teks Indonesia.
        $daftar = $admin->get('/admin/teks?pesan=tersimpan');
        $this->assertStringContainsString('Tersimpan. Halaman publik sudah memakai teks baru.', $daftar->body);
        $this->assertSame(1, preg_match('/<td data-teks="tentang-id">(.*?)<\/td>/s', $daftar->body, $id));
        $this->assertStringContainsString('Sudah diubah', $id[1]);
        $this->assertStringContainsString('Admin Penyunting', $id[1]);
        $this->assertStringNotContainsString('Lebih lama dari teks Indonesia', $id[1]);
        foreach (['en', 'ar'] as $b) {
            $this->assertSame(1, preg_match('/<td data-teks="tentang-' . $b . '">(.*?)<\/td>/s', $daftar->body, $sel));
            $this->assertStringContainsString('Lebih lama dari teks Indonesia', $sel[1], $b);
        }
        $this->assertSame(1, preg_match('/<td data-teks="privasi-en">(.*?)<\/td>/s', $daftar->body, $sel));
        $this->assertStringNotContainsString('Lebih lama', $sel[1]);

        // Menyimpan teks yang sama sekali lagi tidak menulis apa pun.
        $r = $admin->post('/admin/teks/tentang/id', ['csrf' => $csrf, 'aksi' => 'simpan', 't' => $isian]);
        $this->assertSame('https://lokandita.com/admin/teks?pesan=tidak-berubah', $r->header('Location'));
        $this->assertSame(1, $this->jumlah('audit_log'));
        // Form kini berisi teks yang tersimpan.
        $this->assertStringContainsString('value="Tentang Lokandita versi baru"', $admin->get('/admin/teks/tentang/id')->body);
    }

    /**
     * Bentuk halaman tetap: isian kosong ditolak, kunci asing dan bagian tambahan diabaikan.
     */
    public function testBentukHalamanTidakBisaDirusak(): void
    {
        [$admin] = $this->admin();
        $csrf    = $this->csrf($admin);
        $baik    = $this->isianBawaan('tentang');

        $salah = [];
        $salah['judul kosong']          = ['judul' => '   '] + $baik;
        $salah['judul terlalu panjang'] = ['judul' => str_repeat('a', 401)] + $baik;
        $salah['pembuka kosong']        = ['pembuka' => " \n \n"] + $baik;
        $salah['bagian tanpa judul']    = $baik;
        $salah['bagian tanpa judul']['bagian'][1]['judul'] = '';
        $salah['bagian tanpa isi']      = $baik;
        $salah['bagian tanpa isi']['bagian'][4]['isi'] = '';
        $salah['bagian hilang']         = $baik;
        unset($salah['bagian hilang']['bagian'][2]);
        $salah['paragraf terlalu panjang'] = ['pembuka' => str_repeat('k', 3001)] + $baik;
        $salah['karakter kendali']      = ['deskripsi' => "ada\x07lonceng"] + $baik;
        $salah['bukan teks']            = ['judul' => ['larik']] + $baik;
        foreach ($salah as $apa => $t) {
            foreach (['simpan', 'pratinjau'] as $aksi) {
                $r = $admin->post('/admin/teks/tentang/id', ['csrf' => $csrf, 'aksi' => $aksi, 't' => $t]);
                $this->assertSame(422, $r->status, "{$apa} ({$aksi})");
                $this->assertStringContainsString('Belum disimpan', $r->body, $apa);
                $this->assertStringNotContainsString('data-pratinjau', $r->body, $apa);
            }
        }
        $this->assertSame(422, $admin->post('/admin/teks/tentang/id', ['csrf' => $csrf, 'aksi' => 'lain', 't' => $baik])->status);
        $this->assertSame(422, $admin->post('/admin/teks/tentang/id', ['csrf' => $csrf, 'aksi' => 'simpan'])->status);
        $this->assertSame(0, $this->jumlah('teks'));
        $this->assertSame(0, $this->jumlah('audit_log'));

        // Kunci asing dan bagian kesembilan diabaikan: yang tersimpan berbentuk persis seperti bawaan.
        $t = $baik;
        $t['judul']      = 'Tentang kami';
        $t['jahat']      = 'tidak dikenal';
        $t['diperbarui'] = 'Tanggal karangan';
        $t['bagian'][8]  = ['judul' => 'Bagian selundupan', 'isi' => 'x'];
        $t['bagian'][0]['lain'] = 'x';
        $r = $admin->post('/admin/teks/tentang/id', ['csrf' => $csrf, 'aksi' => 'simpan', 't' => $t]);
        $this->assertSame('https://lokandita.com/admin/teks?pesan=tersimpan', $r->header('Location'));
        $isi    = json_decode((string) $this->db->query("SELECT isi FROM teks WHERE halaman = 'tentang' AND bahasa = 'id'")->fetch_row()[0], true);
        $bawaan = (require APPPATH . 'Language/id/Portal.php')['tentang'];
        $this->assertSame(array_keys($bawaan), array_keys($isi));
        $this->assertCount(count($bawaan['bagian']), $isi['bagian']);
        $this->assertSame(['judul', 'isi'], array_keys($isi['bagian'][4]));
        $publik = $this->tamu()->get('/tentang')->body;
        $this->assertStringContainsString('<h1>Tentang kami</h1>', $publik);
        $this->assertStringNotContainsString('Bagian selundupan', $publik);
        $this->assertStringNotContainsString('Tanggal karangan', $publik);
    }

    public function testKembaliKeBawaanDanRiwayat(): void
    {
        [$admin] = $this->admin('Admin Riwayat');
        $csrf    = $this->csrf($admin);
        $t       = ['judul' => 'Ketentuan sementara'] + $this->isianBawaan('ketentuan');
        $admin->post('/admin/teks/ketentuan/id', ['csrf' => $csrf, 'aksi' => 'simpan', 't' => $t]);
        $this->assertStringContainsString('<h1>Ketentuan sementara</h1>', $this->tamu()->get('/ketentuan')->body);
        $this->assertStringContainsString('action="/admin/teks/ketentuan/id/bawaan"', $admin->get('/admin/teks/ketentuan/id')->body);

        $r = $admin->post('/admin/teks/ketentuan/id/bawaan', ['csrf' => $csrf]);
        $this->assertSame('https://lokandita.com/admin/teks?pesan=dikembalikan', $r->header('Location'));
        $this->assertSame(0, $this->jumlah('teks'));
        $publik = $this->tamu()->get('/ketentuan')->body;
        $this->assertStringContainsString('<h1>Ketentuan penggunaan</h1>', $publik);
        $this->assertStringContainsString('Terakhir diperbarui: 21 November 2026', $publik, 'tanggal bawaan dari berkas bahasa');
        // Sekali lagi: tidak ada yang dikembalikan, tidak ada baris audit baru.
        $this->assertSame('https://lokandita.com/admin/teks?pesan=sudah-bawaan', $admin->post('/admin/teks/ketentuan/id/bawaan', ['csrf' => $csrf])->header('Location'));
        $this->assertStringNotContainsString('/bawaan"', $admin->get('/admin/teks/ketentuan/id')->body);

        $aksi = array_column($this->db->query('SELECT action FROM audit_log ORDER BY id')->fetch_all(MYSQLI_ASSOC), 'action');
        $this->assertSame(['teks.diubah', 'teks.dikembalikan'], $aksi);
        // Riwayat: susunan kata yang sudah tidak tayang masih bisa ditemukan.
        $riwayat = $admin->get('/admin/teks/riwayat');
        $this->assertSame(200, $riwayat->status);
        $this->assertSame(2, preg_match_all('/<article class="kartu" data-riwayat="/', $riwayat->body));
        $this->assertStringContainsString('judul: Ketentuan sementara', $riwayat->body);
        $this->assertStringContainsString('Admin Riwayat', $riwayat->body);
        // Tidak ada rute yang mengubah atau menghapus riwayat.
        foreach (['/admin/teks/riwayat', '/admin/teks/riwayat/1', '/admin/teks/riwayat/hapus'] as $jalur) {
            $this->assertContains($admin->post($jalur, ['csrf' => $csrf])->status, [404, 405], $jalur);
        }
        $this->assertSame(2, $this->jumlah('audit_log'));
    }

    public function testHalamanDepanKartuAplikasiDanBahasaArab(): void
    {
        [$admin] = $this->admin();
        $csrf    = $this->csrf($admin);

        // Halaman depan: tajuk baru dan langkah kelima.
        $t = $this->isianBawaan('depan');
        $t['tajuk'] = 'Selamat datang di Lokandita';
        $t['cara'] .= "\nLangkah kelima yang baru.";
        $r = $admin->post('/admin/teks/depan/id', ['csrf' => $csrf, 'aksi' => 'pratinjau', 't' => $t]);
        $this->assertStringContainsString('<h1>Selamat datang di Lokandita</h1>', $r->body);
        $admin->post('/admin/teks/depan/id', ['csrf' => $csrf, 'aksi' => 'simpan', 't' => $t]);
        $depan = $this->tamu()->get('/');
        $this->assertStringContainsString('<h1>Selamat datang di Lokandita</h1>', $depan->body);
        $this->assertStringContainsString('<span>Langkah kelima yang baru.</span>', $depan->body);
        // Lima langkah, masing-masing dengan ikon; langkah kelima (di luar empat bawaan) mendapat ikon umum.
        $langkah = (string) preg_replace('/^.*<ol class="langkah"[^>]*>(.*?)<\/ol>.*$/s', '$1', $depan->body);
        $this->assertSame(5, preg_match_all('/<li>/', $langkah));
        $this->assertSame(5, preg_match_all('/<svg class="ikon/', $langkah));

        // Keterangan kartu: tampil di halaman depan dan di beranda sesudah masuk.
        $k = ['lokakode' => 'Keterangan LokaKode yang baru.'] + $this->isianBawaan('aplikasi');
        $r = $admin->post('/admin/teks/aplikasi/id', ['csrf' => $csrf, 'aksi' => 'pratinjau', 't' => $k]);
        $this->assertStringContainsString('<span>Keterangan LokaKode yang baru.</span>', $r->body);
        $admin->post('/admin/teks/aplikasi/id', ['csrf' => $csrf, 'aksi' => 'simpan', 't' => $k]);
        $this->assertStringContainsString('<span>Keterangan LokaKode yang baru.</span>', $this->tamu()->get('/')->body);
        $mhs = $this->sebagai($this->user(['apps' => ['portal', 'lokakode']]));
        $this->assertStringContainsString('<span>Keterangan LokaKode yang baru.</span>', $mhs->get('/beranda')->body);

        // Bahasa Arab: kotak isian kanan-ke-kiri; yang berubah hanya halaman Arab.
        $form = $admin->get('/admin/teks/privasi/ar');
        $this->assertMatchesRegularExpression('/<input type="text" id="k-judul"[^>]* dir="rtl" lang="ar"/', $form->body);
        $a = ['judul' => 'سياسة الخصوصية الجديدة'] + $this->isianBawaan('privasi', 'ar');
        $admin->post('/admin/teks/privasi/ar', ['csrf' => $csrf, 'aksi' => 'simpan', 't' => $a]);
        $ar = $this->tamu();
        $ar->get('/bahasa/ar?kembali=/privasi');
        $this->assertStringContainsString('<h1>سياسة الخصوصية الجديدة</h1>', $ar->get('/privasi')->body);
        $this->assertStringContainsString('<h1>Kebijakan privasi</h1>', $this->tamu()->get('/privasi')->body);
    }

    /**
     * "Terakhir diperbarui" di privasi dan ketentuan adalah hari teks itu disimpan (Asia/Jakarta).
     */
    public function testTanggalDiperbaruiMengikutiHariSimpan(): void
    {
        [$admin] = $this->admin();
        $csrf    = $this->csrf($admin);
        foreach (['id', 'en', 'ar'] as $b) {
            $t = $this->isianBawaan('privasi', $b);
            $t['pembuka'] .= "\nTambahan.";
            $admin->post("/admin/teks/privasi/{$b}", ['csrf' => $csrf, 'aksi' => 'simpan', 't' => $t]);
        }
        // 5 Januari 2027 pukul 18.30 UTC sudah tanggal 6 di Jakarta.
        $this->db->query("UPDATE teks SET updated_at = '2027-01-05 18:30:00' WHERE halaman = 'privasi'");
        $harap = ['id' => 'Terakhir diperbarui: 6 Januari 2027', 'en' => 'Last updated: 6 January 2027', 'ar' => 'آخر تحديث: 6 يناير 2027'];
        foreach ($harap as $b => $teks) {
            $http = $this->tamu();
            $http->get("/bahasa/{$b}?kembali=/privasi");
            $this->assertStringContainsString('<p class="redup">' . $teks . '</p>', $http->get('/privasi')->body, $b);
        }
        // Halaman tanpa tanggal tidak mendapat tanggal.
        $t = ['judul' => 'Tentang kami'] + $this->isianBawaan('tentang');
        $admin->post('/admin/teks/tentang/id', ['csrf' => $csrf, 'aksi' => 'simpan', 't' => $t]);
        $this->assertStringNotContainsString('Terakhir diperbarui', $this->tamu()->get('/tentang')->body);
    }

    /**
     * Halaman publik tidak boleh gagal karena tabel teks: bila tidak terbaca, teks bawaan yang tampil.
     */
    public function testTabelTidakTerbacaHalamanPublikMemakaiBawaan(): void
    {
        [$admin] = $this->admin();
        $t       = ['judul' => 'Tentang pengganti'] + $this->isianBawaan('tentang');
        $admin->post('/admin/teks/tentang/id', ['csrf' => $this->csrf($admin), 'aksi' => 'simpan', 't' => $t]);
        $this->assertStringContainsString('<h1>Tentang pengganti</h1>', $this->tamu()->get('/tentang')->body);

        $this->db->query('RENAME TABLE teks TO teks_hilang');
        try {
            foreach (['/' => 'Satu akun untuk semua aplikasi belajar Anda', '/tentang' => 'Tentang Lokandita', '/privasi' => 'Kebijakan privasi'] as $jalur => $tajuk) {
                $r = $this->tamu()->get($jalur);
                $this->assertSame(200, $r->status, $jalur);
                $this->assertStringContainsString('<h1>' . $tajuk . '</h1>', $r->body, $jalur);
            }
            $this->assertSame(200, $this->sebagai($this->user())->get('/beranda')->status);
        } finally {
            $this->db->query('RENAME TABLE teks_hilang TO teks');
        }
        $this->assertStringContainsString('<h1>Tentang pengganti</h1>', $this->tamu()->get('/tentang')->body);
    }

    /**
     * Teks pengganti yang bentuknya tidak lagi sama dengan bawaan — halaman yang sudah tidak ada
     * (`kontak`), atau Tentang yang disimpan sebelum bagian-bagian Kontak digabungkan ke dalamnya —
     * tidak dipakai: bawaan yang tampil, lengkap dengan bagian Kontak, dan barisnya tidak dihapus.
     */
    public function testTeksPenggantiBerbentukUsangTidakDipakai(): void
    {
        [, $user] = $this->admin();
        $lama     = (require APPPATH . 'Language/id/Portal.php')['tentang'];
        $lama['judul']  = 'Tentang versi lama';
        $lama['bagian'] = array_slice($lama['bagian'], 0, 4);
        $sisip = $this->db->prepare('INSERT INTO teks (halaman, bahasa, isi, updated_by, updated_at) VALUES (?, ?, ?, ?, UTC_TIMESTAMP())');
        foreach (['tentang' => $lama, 'kontak' => ['judul' => 'Kontak lama', 'deskripsi' => 'x', 'pembuka' => ['x'], 'bagian' => []]] as $halaman => $isi) {
            $json   = json_encode($isi, JSON_UNESCAPED_UNICODE);
            $bahasa = 'id';
            $sisip->bind_param('ssss', $halaman, $bahasa, $json, $user['id']);
            $sisip->execute();
        }
        $r = $this->tamu()->get('/tentang');
        $this->assertSame(200, $r->status);
        $this->assertStringContainsString('<h1>Tentang Lokandita</h1>', $r->body);
        $this->assertStringNotContainsString('versi lama', $r->body);
        $this->assertStringContainsString('<h2>Lupa kata sandi</h2>', $r->body, 'bagian Kontak tetap tampil');
        $this->assertSame(2, $this->jumlah('teks'), 'baris lama tidak dihapus');
    }
}
