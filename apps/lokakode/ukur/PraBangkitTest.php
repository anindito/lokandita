<?php

declare(strict_types=1);

namespace Ukur;

use Tests\Support\KodeCase;

/**
 * PENGUKURAN, bukan tes: berapa lama pra-pembangkitan satu kelas penuh
 * (SPEC.md §8.2) di server ini. Tidak ikut `infra/tes.sh lokakode`; dijalankan
 * sengaja, pada skema tes dan worker tes (JUDGE_CONCURRENCY yang sama dengan
 * produksi):
 *
 *   infra/tes.sh lokakode ukur/PraBangkitTest.php
 *
 * Membebani CPU server selama pengukuran. Hasilnya dicatat di PROGRESS.md.
 */
final class PraBangkitTest extends KodeCase
{
    private const MAHASISWA = 40;

    private const SOAL = 30;

    public function testPraPembangkitanSatuKelasPenuh(): void
    {
        $d = $this->dunia();
        // 30 soal (12 tes: 2 contoh + 10 tersembunyi), tersebar di lima tingkat.
        $tingkat = ['MUDAH_SEKALI', 'MUDAH', 'MUDAH_SEDANG', 'SEDANG', 'SULIT'];
        for ($i = 0; $i < self::SOAL; $i++) {
            $this->soalSiap($d['pertemuanX1'], sprintf('soal-ukur-%02d', $i), $tingkat[$i % 5]);
        }
        $this->terbit($d['pertemuanX1']);
        // Mahasiswa A sudah ada di kelas XA; ditambah sampai 40.
        for ($i = 1; $i < self::MAHASISWA; $i++) {
            $this->daftar($this->user('mahasiswa', "Mahasiswa Ukur {$i}"), $d['kelasXA']);
        }
        $harap = self::MAHASISWA * self::SOAL;

        $http  = $this->sebagai($d['pengajarA']);
        $mulai = microtime(true);
        $r     = $http->post('/kelas/' . $d['kelasXA'] . '/pertemuan/' . $d['pertemuanX1'] . '/status', ['csrf' => $this->csrf($http), 'override' => 'open']);
        $this->assertSame(303, $r->status);
        $antre = microtime(true) - $mulai;
        $this->assertSame($harap, $this->hitung("SELECT COUNT(*) FROM judge_jobs WHERE kind = 'GENERATE'"));

        $catatan = [];
        $this->tungguSampai(function () use ($harap, $mulai, &$catatan) {
            $n = $this->hitung('SELECT COUNT(*) FROM problem_instances');
            foreach ([0.25, 0.5, 0.75] as $bagian) {
                if ($n >= $harap * $bagian && ! isset($catatan[(string) $bagian])) {
                    $catatan[(string) $bagian] = microtime(true) - $mulai;
                }
            }

            return $n === $harap;
        }, 3600, 'semua instans');
        $lama = microtime(true) - $mulai;

        $gagal = $this->hitung("SELECT COUNT(*) FROM judge_jobs WHERE kind = 'GENERATE' AND status <> 'DONE'");
        $hal   = $http->get('/kelas/' . $d['kelasXA'] . '/pertemuan/' . $d['pertemuanX1'])->body;
        fwrite(STDERR, sprintf(
            "\n[ukur] pra-pembangkitan %d mahasiswa × %d soal = %d instans (12 tes per instans)\n"
            . "[ukur]   permintaan pengajar (membuat %d pekerjaan): %.1f detik\n"
            . "[ukur]   25%%: %.0f detik · 50%%: %.0f detik · 75%%: %.0f detik · selesai: %.0f detik (%.1f menit)\n"
            . "[ukur]   rata-rata %.2f detik per instans; pekerjaan tidak DONE: %d\n",
            self::MAHASISWA, self::SOAL, $harap, $harap, $antre,
            $catatan['0.25'] ?? 0, $catatan['0.5'] ?? 0, $catatan['0.75'] ?? 0, $lama, $lama / 60, $lama / $harap, $gagal,
        ));
        $this->assertSame(0, $gagal);
        $this->assertStringContainsString("{$harap} dari {$harap} soal mahasiswa sudah siap.", $hal);
    }
}
