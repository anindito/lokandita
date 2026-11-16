<?php

declare(strict_types=1);

namespace Tests\Soal;

use App\Libraries\Soal\Benih;
use PHPUnit\Framework\TestCase;

/**
 * Seed per mahasiswa per versi soal (KONTRAK.md §6).
 */
final class BenihTest extends TestCase
{
    private string|false $asli;

    protected function setUp(): void
    {
        $this->asli = getenv('INSTANCE_SECRET');
    }

    protected function tearDown(): void
    {
        putenv($this->asli === false ? 'INSTANCE_SECRET' : 'INSTANCE_SECRET=' . $this->asli);
    }

    public function testSeedAdalahDelapanByteHmacDenganBitTeratasNol(): void
    {
        putenv('INSTANCE_SECRET=rahasia-uji');
        $user = '06efcaf8-5c6d-4732-abd6-74453cb213d6';
        // Dihitung terpisah dari implementasinya: hex 16 digit pertama HMAC, bit teratas dinolkan.
        $hex    = substr(hash_hmac('sha256', $user . '|41', 'rahasia-uji'), 0, 16);
        // 15 digit hex terakhir muat di 60 bit; digit pertama menyumbang 3 bit sisanya.
        $harap  = ((hexdec($hex[0]) & 7) << 60) | hexdec(substr($hex, 1));
        $this->assertSame($harap, Benih::untuk($user, 41));
        $this->assertGreaterThanOrEqual(0, Benih::untuk($user, 41));
    }

    public function testSeedTetapUntukPasanganYangSamaDanBerbedaUntukYangLain(): void
    {
        putenv('INSTANCE_SECRET=rahasia-uji');
        $a = 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa';
        $b = 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb';
        $this->assertSame(Benih::untuk($a, 7), Benih::untuk($a, 7));
        $this->assertNotSame(Benih::untuk($a, 7), Benih::untuk($b, 7));
        $this->assertNotSame(Benih::untuk($a, 7), Benih::untuk($a, 8));
        // "1|23" dan "12|3" tidak boleh bertabrakan dengan pemisah yang dipakai.
        $this->assertNotSame(Benih::untuk('1', 23), Benih::untuk('12', 3));
        putenv('INSTANCE_SECRET=rahasia-lain');
        $this->assertNotSame(Benih::untuk($a, 7), (function () use ($a) {
            putenv('INSTANCE_SECRET=rahasia-uji');

            return Benih::untuk($a, 7);
        })());
    }

    public function testTanpaRahasiaSeedTidakDihitung(): void
    {
        putenv('INSTANCE_SECRET=');
        $this->expectException(\RuntimeException::class);
        Benih::untuk('x', 1);
    }
}
