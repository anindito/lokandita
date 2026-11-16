<?php

declare(strict_types=1);

namespace Tests\Support;

/**
 * Bahan uji impor soal: soal contoh berbentuk KONTRAK.md §7 dan cara menjalankan
 * `spark soal:impor` sungguhan (basis data, Redis, judge worker dan kontainer nyata).
 */
trait SoalBantu
{
    protected const GEN = "import os, random\nr = random.Random(int(os.environ[\"SEED\"]) * 1000 + int(os.environ[\"INDEX\"]))\nprint(r.randint(1, 1000), r.randint(1, 1000))\n";

    // Gerbang tahap 8: literal string di antara dua double bukan STRING.
    protected const REF_GERBANG = "#include <iostream>\nusing namespace std;\nint main() {\n    double x, y;\n    cin >> x >> y;\n    cout << x << \" \" << y << endl;\n    return 0;\n}\n";

    protected const REF_JUMLAH = "#include <iostream>\nusing namespace std;\nint main() {\n    long a, b;\n    cin >> a >> b;\n    cout << a + b << endl;\n    return 0;\n}\n";

    // Memakai if di baris 6, kolom 5: PERCABANGAN belum diizinkan di pertemuan 1 dan 2 dunia uji.
    protected const REF_IF = "#include <iostream>\nusing namespace std;\nint main() {\n    long a, b;\n    cin >> a >> b;\n    if (a > b) cout << a << endl;\n    else cout << b << endl;\n    return 0;\n}\n";

    /**
     * @param array<string, mixed> $ganti
     *
     * @return array<string, mixed>
     */
    protected function soalContoh(string $slug, array $ganti = []): array
    {
        return $ganti + [
            'slug' => $slug, 'difficulty' => 'mudah', 'comparison_mode' => 'trimmed', 'time_limit_ms' => 2000,
            'samples_count' => 2, 'hidden_count' => 10, 'required_concepts_in_submission' => [],
            'translations' => [
                'id' => ['title' => 'Dua Bilangan', 'statement' => 'Diberikan dua bilangan **x** dan **y**. Cetak keduanya.',
                    'input_format' => 'Satu baris berisi x dan y.', 'output_format' => 'x dan y dipisah satu spasi.', 'constraints' => '1 ≤ x, y ≤ 1000'],
            ],
            'generator' => self::GEN, 'reference_solution' => self::REF_GERBANG,
        ];
    }

    /**
     * @param list<mixed>          $soal
     * @param array<string, mixed> $ganti
     */
    protected function berkasImpor(array $soal, array $ganti = []): string
    {
        return json_encode($ganti + ['course' => 'UJX101', 'meeting' => 1, 'language' => 'cpp', 'problems' => $soal],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n";
    }

    /**
     * Baris (mulai dari 1) tempat kemunculan ke-$ke dari $cari di $teks.
     */
    protected function barisDari(string $teks, string $cari, int $ke = 1): int
    {
        $pos = -1;
        for ($i = 0; $i < $ke; $i++) {
            $pos = strpos($teks, $cari, $pos + 1);
            $this->assertNotFalse($pos, "tidak ada kemunculan ke-{$ke} dari {$cari}");
        }

        return substr_count($teks, "\n", 0, (int) $pos) + 1;
    }

    /**
     * Menjalankan `php spark <argumen>` di kontainer ini dengan $masukan sebagai stdin.
     *
     * @param list<string> $argumen
     *
     * @return array{0: int, 1: string, 2: string} kode keluar, stdout, stderr
     */
    protected function spark(array $argumen, string $masukan = ''): array
    {
        $proses = proc_open(['php', 'spark', ...$argumen, '--no-header'], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipa, ROOTPATH);
        fwrite($pipa[0], $masukan);
        fclose($pipa[0]);
        $keluar = stream_get_contents($pipa[1]);
        $galat  = stream_get_contents($pipa[2]);

        return [proc_close($proses), (string) $keluar, (string) $galat];
    }

    /**
     * `spark soal:impor --oleh <superadmin>` dengan $json di stdin.
     *
     * @param array<string, mixed> $superadmin
     *
     * @return array{0: int, 1: string}
     */
    protected function impor(string $json, array $superadmin, string $nama = 'uji.json'): array
    {
        [$kode, $keluar, $galat] = $this->spark(['soal:impor', '--oleh', (string) $superadmin['nomor_induk'], '--nama', $nama, '--tunggu', '300'], $json);

        return [$kode, $keluar . $galat];
    }
}
