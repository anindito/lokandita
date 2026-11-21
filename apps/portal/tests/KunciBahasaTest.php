<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

/**
 * Tiga bahasa (SPEC.md §15): setiap teks Portal ada dalam `id`, `en` dan `ar` dengan
 * susunan kunci yang sama, tidak ada yang kosong, dan alamat kontak tidak ditulis di
 * berkas bahasa (SPEC.md §1: satu nilai konfigurasi). Tes ini tidak menilai MUTU
 * terjemahan; teks Inggris dan Arab ditulis agen dan belum ditinjau (PROGRESS.md).
 */
final class KunciBahasaTest extends TestCase
{
    /**
     * @param array<int|string, mixed> $a
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

    public function testKetigaBahasaPunyaKunciYangSamaDanTidakKosong(): void
    {
        $per = [];
        foreach (['id', 'en', 'ar'] as $b) {
            $jalur = APPPATH . "Language/{$b}/Portal.php";
            $this->assertFileExists($jalur);
            $this->assertDoesNotMatchRegularExpression('/[A-Za-z0-9._-]+@[A-Za-z0-9-]+\.[a-z]/', (string) file_get_contents($jalur), "alamat surel ditulis langsung di {$b}/Portal.php");
            $per[$b] = self::ratakan(require $jalur);
        }
        $this->assertGreaterThan(100, count($per['id']));
        foreach (['en', 'ar'] as $b) {
            $this->assertSame(array_keys($per['id']), array_keys($per[$b]), "susunan kunci {$b} berbeda dari id");
        }
        foreach ($per['id'] as $kunci => $teks) {
            foreach (['id', 'en', 'ar'] as $b) {
                $t = $per[$b][$kunci];
                $this->assertNotSame('', trim($t), "Portal.{$kunci} kosong dalam {$b}");
                $this->assertSame(substr_count($teks, '{email}'), substr_count($t, '{email}'), "Portal.{$kunci}: {email} dalam {$b}");
                $this->assertSame(substr_count($teks, '{0}'), substr_count($t, '{0}'), "Portal.{$kunci}: {0} dalam {$b}");
            }
            if ($per['ar'][$kunci] === $teks && preg_match('/[A-Za-z]{3,}/', $teks) === 1) {
                $this->fail("Portal.{$kunci} dalam ar sama dengan id: «{$teks}»");
            }
        }
    }
}
