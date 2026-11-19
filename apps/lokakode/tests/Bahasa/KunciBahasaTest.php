<?php

declare(strict_types=1);

namespace Tests\Bahasa;

use Lokandita\Auth\Locale;
use PHPUnit\Framework\TestCase;

/**
 * Tiga bahasa (SPEC.md §11): setiap teks antarmuka LokaKode dan paket bersama ada
 * dalam `id`, `en` dan `ar`, dengan kunci dan tempat isian ({0}, {1}) yang sama, dan
 * setiap kunci yang dipanggil kode memang ada. Tes ini tidak menilai MUTU terjemahan;
 * teks Arab ditulis agen dan belum dibaca penutur bahasa Arab (PROGRESS.md).
 */
final class KunciBahasaTest extends TestCase
{
    private const BAHASA = ['id', 'en', 'ar'];

    /**
     * @return array<string, string> nama => direktori Language
     */
    private static function direktori(): array
    {
        return [
            'lokakode' => APPPATH . 'Language',
            'paket'    => dirname((string) (new \ReflectionClass(Locale::class))->getFileName()) . '/Language',
        ];
    }

    /**
     * @param array<string, mixed> $a
     *
     * @return array<string, string>
     */
    private static function ratakan(array $a, string $awalan = ''): array
    {
        $hasil = [];
        foreach ($a as $k => $v) {
            if (is_array($v)) {
                $hasil += self::ratakan($v, $awalan . $k . '.');
            } else {
                $hasil[$awalan . $k] = (string) $v;
            }
        }

        return $hasil;
    }

    /**
     * @return array<string, array<string, array<string, string>>> berkas => bahasa => kunci => teks
     */
    private static function semua(): array
    {
        $semua = [];
        foreach (self::direktori() as $dir) {
            foreach (glob($dir . '/id/*.php') ?: [] as $berkas) {
                $nama = basename($berkas, '.php');
                foreach (self::BAHASA as $b) {
                    $jalur = "{$dir}/{$b}/{$nama}.php";
                    self::assertFileExists($jalur, "berkas bahasa {$b}/{$nama}.php tidak ada");
                    $semua[$nama][$b] = self::ratakan(require $jalur);
                }
            }
        }

        return $semua;
    }

    public function testKetigaBahasaPunyaKunciDanTempatIsianYangSama(): void
    {
        $semua = self::semua();
        $this->assertArrayHasKey('Latihan', $semua);
        $this->assertArrayHasKey('Papan', $semua);
        $jumlah = 0;
        foreach ($semua as $nama => $per) {
            foreach (['en', 'ar'] as $b) {
                $this->assertSame([], array_values(array_diff(array_keys($per['id']), array_keys($per[$b]))), "{$nama}: kunci yang tidak ada dalam {$b}");
                $this->assertSame([], array_values(array_diff(array_keys($per[$b]), array_keys($per['id']))), "{$nama}: kunci {$b} yang tidak ada dalam id");
            }
            foreach ($per['id'] as $kunci => $teks) {
                $jumlah++;
                preg_match_all('/\{\d+\}/', $teks, $isian);
                $harap = array_values(array_unique($isian[0]));
                sort($harap);
                foreach (self::BAHASA as $b) {
                    $t = $per[$b][$kunci];
                    $this->assertNotSame('', trim($t), "{$nama}.{$kunci} kosong dalam {$b}");
                    preg_match_all('/\{\d+\}/', $t, $ada);
                    $ada = array_values(array_unique($ada[0]));
                    sort($ada);
                    $this->assertSame($harap, $ada, "{$nama}.{$kunci}: tempat isian dalam {$b} berbeda dari id");
                }
                // Teks Arab yang sama persis dengan teks Indonesia berarti belum diterjemahkan — kecuali teks
                // yang memang sama di ketiga bahasa (pintasan papan ketik, nama format, singkatan).
                if ($per['ar'][$kunci] === $teks && $per['en'][$kunci] !== $teks && preg_match('/[A-Za-z]{3,}/', $teks) === 1) {
                    $this->fail("{$nama}.{$kunci} dalam ar sama dengan id: «{$teks}»");
                }
            }
        }
        $this->assertGreaterThan(700, $jumlah, 'jumlah kunci yang diperiksa');
    }

    public function testSetiapKunciYangDipanggilKodeAda(): void
    {
        $semua = self::semua();
        $berkas = [];
        foreach ([APPPATH . 'Views', APPPATH . 'Controllers', APPPATH . 'Libraries', APPPATH . 'Commands'] as $dir) {
            if (! is_dir($dir)) {
                continue;
            }
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)) as $f) {
                if ($f->getExtension() === 'php' && ! str_contains($f->getPathname(), '/errors/')) {
                    $berkas[] = $f->getPathname();
                }
            }
        }
        $diperiksa = 0;
        foreach ($berkas as $jalur) {
            // Hanya kunci yang ditulis utuh; kunci yang disusun (`'Latihan.mode.' . $mode`) diuji lewat halamannya.
            preg_match_all("/lang\\('([A-Z][A-Za-z]+)\\.([A-Za-z0-9_.]+)'\\s*[,)]/", (string) file_get_contents($jalur), $m, PREG_SET_ORDER);
            foreach ($m as [, $nama, $kunci]) {
                if (! isset($semua[$nama])) {
                    continue;   // berkas bahasa milik CodeIgniter sendiri
                }
                $diperiksa++;
                // Kunci satu teks, atau satu bagian utuh (lang('Latihan.js') untuk skrip halaman soal).
                $ada = isset($semua[$nama]['id'][$kunci])
                    || array_filter(array_keys($semua[$nama]['id']), static fn (string $k): bool => str_starts_with($k, $kunci . '.')) !== [];
                $this->assertTrue($ada, basename($jalur) . " memanggil {$nama}.{$kunci} yang tidak ada");
            }
        }
        $this->assertGreaterThan(500, $diperiksa);
    }
}
