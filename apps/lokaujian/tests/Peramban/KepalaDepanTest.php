<?php

declare(strict_types=1);

namespace Tests\Peramban;

use Lokandita\Auth\Testing\Kepala;
use Lokandita\Auth\Testing\Peramban;
use PHPUnit\Framework\TestCase;

/**
 * Kepala halaman depan LokaUjian di PERAMBAN SUNGGUHAN: satu baris di semua lebar dan bahasa,
 * mode terang dan gelap; di bawah 768 px pilihan bahasa ada di panel tombol menu.
 * Aturan dan ukurannya sama dengan Portal dan LokaKode (Lokandita\Auth\Testing\Kepala).
 */
final class KepalaDepanTest extends TestCase
{
    /**
     * @return iterable<string, array{0: string}>
     */
    public static function bahasa(): iterable
    {
        yield 'id' => ['id'];
        yield 'en' => ['en'];
        yield 'ar' => ['ar'];
    }

    /**
     * @dataProvider bahasa
     */
    public function testKepalaSatuBarisDiSemuaLebar(string $bahasa): void
    {
        $p = new Peramban('https://lokaujian.lokandita.com');
        $p->perintah('Emulation.setScrollbarsHidden', ['hidden' => true]);
        foreach (Kepala::LEBAR as $lebar) {
            $apa = "lokaujian-depan-{$bahasa} {$lebar}px";
            $p->ukuran($lebar, 900);
            $this->assertSame(200, $p->buka('/bahasa/' . $bahasa . '?kembali=' . rawurlencode('/')), $apa);
            foreach (['terang' => 'light', 'gelap' => 'dark'] as $mode => $skema) {
                $p->perintah('Emulation.setEmulatedMedia', ['features' => [['name' => 'prefers-color-scheme', 'value' => $skema]]]);
                $u = $p->js(Kepala::UKUR);
                Kepala::assertSatuBaris($u, "{$apa} {$mode}");
                $dir = (string) getenv('TANGKAPAN_DIR');
                if ($dir !== '' && is_dir($dir)) {
                    file_put_contents("{$dir}/kepala-lokaujian-depan-{$bahasa}-{$lebar}-{$mode}.png", $p->tangkap($lebar, 120));
                }
            }
            $this->assertSame($lebar < 768, $u['tombol'], "{$apa}: tombol menu hanya di layar sempit");
            $this->assertSame($lebar < 768 ? 0 : 3, $u['tautan'], "{$apa}: tiga bahasa sebaris hanya di layar lebar");
        }

        // Layar sempit: tombol menu membuka panel berisi tiga bahasa; Escape menutupnya.
        $p->ukuran(360, 900);
        $p->klik('[data-menu-tombol]');
        $u = $p->js(Kepala::UKUR);
        $this->assertTrue($u['buka']);
        $this->assertSame(3, $u['tautan'], 'tiga bahasa di panel');
        $this->assertGreaterThanOrEqual(0, $u['panel']['kiri']);
        $this->assertLessThanOrEqual($u['lebar'], $u['panel']['kanan']);
        $p->tekan('Escape', 'Escape', 27);
        $this->assertFalse($p->js(Kepala::UKUR)['buka']);
    }
}
