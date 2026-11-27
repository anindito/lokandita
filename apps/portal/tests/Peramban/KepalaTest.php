<?php

declare(strict_types=1);

namespace Tests\Peramban;

use Lokandita\Auth\Testing\Fixtures;
use Lokandita\Auth\Testing\Kepala;
use Lokandita\Auth\Testing\Peramban;
use Tests\Support\PortalCase;

/**
 * Kepala halaman di peramban sungguhan (Chromium tanpa layar lewat caddy-tes, seperti produksi).
 *
 * Aturannya (pemilik, 2026-11-27): kepala SELALU satu baris dan tidak ada yang bertumpuk, pada
 * lebar 360, 390, 412, 768, 1024 dan 1440 px, dalam bahasa Indonesia, Inggris dan Arab, sebelum
 * dan sesudah masuk, mode terang dan gelap. Di bawah 768 px navigasi dan pilihan bahasa halaman
 * publik pindah ke panel yang dibuka tombol menu; panel itu menutup dengan Escape, klik di luar,
 * klik pada tautannya, dan saat layar melebar.
 *
 * Tes ini mengukur kotak yang benar-benar digambar peramban. Dengan TANGKAPAN_DIR terisi
 * (infra/tes.sh, variabel TANGKAPAN), tangkapan layar tiap kombinasi disimpan di sana.
 */
final class KepalaTest extends PortalCase
{
    private const PORTAL = 'https://lokandita.com';

    private const KODE = 'https://lokakode.lokandita.com';

    private function tangkap(Peramban $p, string $nama, int $lebar, int $tinggi = 420): void
    {
        $dir = (string) getenv('TANGKAPAN_DIR');
        if ($dir !== '' && is_dir($dir)) {
            file_put_contents("{$dir}/kepala-{$nama}.png", $p->tangkap($lebar, $tinggi));
        }
    }

    private function mode(Peramban $p, string $skema): void
    {
        $p->perintah('Emulation.setEmulatedMedia', ['features' => [['name' => 'prefers-color-scheme', 'value' => $skema]]]);
    }

    /**
     * @return iterable<string, array{0: string, 1: string, 2: string, 3: int}>
     */
    public static function halamanPublik(): iterable
    {
        foreach (['portal-depan' => [self::PORTAL, '/', 3], 'portal-tentang' => [self::PORTAL, '/tentang', 3], 'lokakode-depan' => [self::KODE, '/', 0]] as $nama => [$asal, $jalur, $nav]) {
            foreach (['id', 'en', 'ar'] as $bahasa) {
                yield "{$nama}-{$bahasa}" => [$asal, $jalur, $bahasa, $nav];
            }
        }
    }

    /**
     * @dataProvider halamanPublik
     */
    public function testKepalaPublikSatuBarisDiSemuaLebar(string $asal, string $jalur, string $bahasa, int $nav): void
    {
        $nama = (string) $this->dataName();
        $p    = new Peramban($asal);
        $p->perintah('Emulation.setScrollbarsHidden', ['hidden' => true]);
        foreach (Kepala::LEBAR as $lebar) {
            $apa = "{$nama} {$lebar}px";
            $p->ukuran($lebar, 900);
            $this->assertSame(200, $p->buka('/bahasa/' . $bahasa . '?kembali=' . rawurlencode($jalur)), $apa);
            $warna = [];
            foreach (['terang' => 'light', 'gelap' => 'dark'] as $mode => $skema) {
                $this->mode($p, $skema);
                $u = $p->js(Kepala::UKUR);
                Kepala::assertSatuBaris($u, "{$apa} {$mode}");
                $warna[] = $u['latar'];
                $this->tangkap($p, "{$nama}-{$lebar}-{$mode}", $lebar, 120);
            }
            $this->assertNotSame($warna[0], $warna[1], "{$apa}: latar kepala harus berbeda di mode gelap");
            $this->assertFalse($u['buka'], "{$apa}: menu tertutup saat halaman dibuka");
            $this->assertSame('menu-kepala', $u['kendali'], $apa);
            $this->assertSame($u['kendali'], $u['panelId'], "{$apa}: aria-controls menunjuk panel");
            if ($lebar < 768) {
                // Layar sempit: merek, Masuk dan tombol menu — tautan dan bahasa ada di panel yang tertutup.
                $this->assertTrue($u['tombol'], "{$apa}: tombol menu tampak");
                $this->assertSame(['id' => 'Menu', 'en' => 'Menu', 'ar' => 'القائمة'][$bahasa], $u['label'], "{$apa}: label tombol menu");
                $this->assertNull($u['panel'], "{$apa}: panel tertutup");
                $this->assertSame(0, $u['tautan'], "{$apa}: tautan dan bahasa tidak tampak di kepala");
                $this->assertCount(3, $u['butir'], "{$apa}: merek, Masuk, tombol menu");
                // Ruang yang tersisa di antara merek dan Masuk: huruf di ponsel sungguhan bisa sedikit lebih lebar.
                $urut = $u['butir'];
                usort($urut, static fn (array $a, array $b): int => $a['kiri'] <=> $b['kiri']);
                $sisa = $bahasa === 'ar' ? $urut[2]['kiri'] - $urut[1]['kanan'] : $urut[1]['kiri'] - $urut[0]['kanan'];
                $this->assertGreaterThanOrEqual(20, $sisa, "{$apa}: ruang cadangan di antara merek dan Masuk");
            } else {
                $this->assertFalse($u['tombol'], "{$apa}: tombol menu tidak ada di layar lebar");
                $this->assertSame($nav + 3, $u['tautan'], "{$apa}: tautan navigasi dan tiga bahasa sebaris");
                $this->assertCount($nav + 5, $u['butir'], "{$apa}: merek, tautan, bahasa, Masuk");
            }
        }
    }

    /**
     * @return iterable<string, array{0: string}>
     */
    public static function bahasaMenu(): iterable
    {
        yield 'id' => ['id'];
        yield 'ar' => ['ar'];
    }

    /**
     * @dataProvider bahasaMenu
     */
    public function testPanelMenuBukaTutupFokusDanSisi(string $bahasa): void
    {
        $p = new Peramban(self::PORTAL);
        $p->perintah('Emulation.setScrollbarsHidden', ['hidden' => true]);
        $p->ukuran(390, 900);
        $this->assertSame(200, $p->buka('/bahasa/' . $bahasa . '?kembali=' . rawurlencode('/')));

        // Buka dengan klik sungguhan: aria-expanded, panel tampak, fokus pada tautan pertama panel.
        $p->klik('[data-menu-tombol]');
        $u = $p->js(Kepala::UKUR);
        $this->assertTrue($u['buka'], 'aria-expanded true sesudah klik');
        $this->assertNotNull($u['panel'], 'panel tampak');
        $this->assertSame(6, $u['tautan'], 'tiga tautan dan tiga bahasa di panel');
        $this->assertTrue($p->js("document.activeElement === document.querySelector('#menu-kepala a')"), 'fokus pindah ke tautan pertama panel');
        // Panel di bawah kepala, seluruhnya di dalam layar, menempel di sisi AKHIR: kanan (id), kiri (ar).
        $this->assertGreaterThanOrEqual($u['kepala']['bawah'], $u['panel']['atas'], 'panel di bawah kepala');
        $this->assertGreaterThanOrEqual(0, $u['panel']['kiri']);
        $this->assertLessThanOrEqual($u['lebar'], $u['panel']['kanan']);
        if ($bahasa === 'ar') {
            $this->assertEqualsWithDelta($u['isiKiri'], $u['panel']['kiri'], 1.5, '(ar) panel menempel di tepi KIRI');
            $this->assertGreaterThan($u['lebar'] / 2, $u['butir'][0]['kiri'], '(ar) merek di kanan');
        } else {
            $this->assertEqualsWithDelta($u['isiKanan'], $u['panel']['kanan'], 1.5, '(id) panel menempel di tepi kanan');
        }
        Kepala::assertSatuBaris($u, "{$bahasa}: kepala tetap satu baris selagi panel terbuka");
        foreach (['terang' => 'light', 'gelap' => 'dark'] as $mode => $skema) {
            $this->mode($p, $skema);
            $this->tangkap($p, "panel-{$bahasa}-{$mode}", 390);
        }

        // Escape: tertutup, fokus kembali ke tombol.
        $p->tekan('Escape', 'Escape', 27);
        $u = $p->js(Kepala::UKUR);
        $this->assertFalse($u['buka'], 'Escape menutup');
        $this->assertNull($u['panel']);
        $this->assertTrue($p->js("document.activeElement === document.querySelector('[data-menu-tombol]')"), 'fokus kembali ke tombol');

        // Klik tombol lagi menutup, dan fokus tetap di tombol.
        $p->klik('[data-menu-tombol]');
        $this->assertTrue($p->js(Kepala::UKUR)['buka']);
        $p->klik('[data-menu-tombol]');
        $this->assertFalse($p->js(Kepala::UKUR)['buka'], 'klik kedua menutup');
        $this->assertTrue($p->js("document.activeElement === document.querySelector('[data-menu-tombol]')"));

        // Klik di luar menutup.
        $p->klik('[data-menu-tombol]');
        $this->assertTrue($p->js(Kepala::UKUR)['buka']);
        $p->klik('.kaki');
        $this->assertFalse($p->js(Kepala::UKUR)['buka'], 'klik di luar menutup');

        // Melebar ke 768 px menutup; tautan lalu sebaris di kepala.
        $p->js('window.scrollTo(0, 0)');
        $p->klik('[data-menu-tombol]');
        $this->assertTrue($p->js(Kepala::UKUR)['buka']);
        $p->ukuran(768, 900);
        $p->tunggu("document.querySelector('[data-menu-tombol]').getAttribute('aria-expanded') === 'false'", 5, 'menu menutup saat layar melebar');
        $u = $p->js(Kepala::UKUR);
        $this->assertFalse($u['tombol']);
        $this->assertSame(6, $u['tautan']);
        Kepala::assertSatuBaris($u, "{$bahasa}: 768 px sesudah melebar");

        // Klik tautan di panel: menu menutup dan halaman tujuannya terbuka dengan menu tertutup.
        $p->ukuran(390, 900);
        $p->klik('[data-menu-tombol]');
        $p->js("document.querySelector('#menu-kepala').addEventListener('click', function () { window.setTimeout(function () { window.__sesudahKlik = document.querySelector('[data-menu-tombol]').getAttribute('aria-expanded'); }, 0); })");
        $p->js("document.querySelector('.nav-publik a[href=\"/tentang\"]').addEventListener('click', function (e) { e.preventDefault(); })");
        $p->klik('.nav-publik a[href="/tentang"]');
        $p->tunggu("window.__sesudahKlik === 'false'", 5, 'klik tautan menutup menu');
        $this->assertNull($p->js(Kepala::UKUR)['panel']);
        $this->assertSame([[], []], [$p->js('window.__csp'), $p->js('window.__galat')]);
    }

    /**
     * Sesudah masuk kepala hanya berisi merek dan menu akun: tetap satu baris di semua lebar.
     *
     * @dataProvider bahasaMasuk
     */
    public function testKepalaSesudahMasukSatuBaris(string $bahasa): void
    {
        $user = $this->user(['nama' => 'Nama Mahasiswa Yang Cukup Panjang Sekali', 'locale' => $bahasa, 'apps' => ['portal', 'lokakode']]);
        $p    = new Peramban(self::PORTAL);
        $p->perintah('Emulation.setScrollbarsHidden', ['hidden' => true]);
        $p->kuki('lokandita_sid', Fixtures::session($this->idn, (string) $user['id']));
        foreach (Kepala::LEBAR as $lebar) {
            $p->ukuran($lebar, 900);
            $this->assertSame(200, $p->buka('/beranda'), "masuk {$bahasa} {$lebar}px");
            foreach (['terang' => 'light', 'gelap' => 'dark'] as $mode => $skema) {
                $this->mode($p, $skema);
                $u = $p->js(Kepala::UKUR);
                Kepala::assertSatuBaris($u, "masuk {$bahasa} {$lebar}px {$mode}");
                $this->assertCount(2, $u['butir'], "masuk {$bahasa} {$lebar}px: merek dan menu akun");
                $this->assertFalse($u['tombol'], 'tidak ada tombol menu sesudah masuk');
                $this->tangkap($p, "masuk-{$bahasa}-{$lebar}-{$mode}", $lebar, 120);
            }
        }
    }

    /**
     * @return iterable<string, array{0: string}>
     */
    public static function bahasaMasuk(): iterable
    {
        yield 'id' => ['id'];
        yield 'en' => ['en'];
        yield 'ar' => ['ar'];
    }
}
