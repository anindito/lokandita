<?php

declare(strict_types=1);

namespace App\Commands;

use App\Libraries\Antrean;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Mengirim satu pekerjaan RUN sungguhan lewat jalur yang sama dengan web
 * (baris judge_jobs, lalu Redis), menunggu worker menyelesaikannya, dan
 * memeriksa keluarannya. Dipakai infra/smoke.sh (cek 6, KONTRAK.md §11).
 *
 *   docker exec lokakode php spark judge:coba
 *   docker exec lokakode php spark judge:coba --validasi
 *
 * Keluar 0 hanya bila worker berdenyut dan program menghasilkan keluaran yang benar.
 *
 * Dengan --validasi yang dikirim adalah satu pekerjaan VALIDATE untuk sebuah
 * soal kecil bawaan (cek smoke 7): generator, analisis konsep, mode kelompok
 * dan solusi referensi berjalan sungguhan. Tidak ada yang disimpan ke bank soal.
 */
final class JudgeCoba extends BaseCommand
{
    private const PROGRAM = [
        'c'      => "#include <stdio.h>\nint main(void){long n;if(scanf(\"%ld\",&n)!=1)return 1;printf(\"%ld\\n\",n*2);return 0;}\n",
        'cpp'    => "#include <iostream>\nint main(){long n;std::cin>>n;std::cout<<n*2<<\"\\n\";}\n",
        'python' => "print(int(input()) * 2)\n",
    ];

    protected $group = 'Lokandita';

    protected $name = 'judge:coba';

    protected $description = 'Menjalankan satu program kecil lewat judge worker dan memeriksa hasilnya.';

    protected $usage = 'judge:coba [--bahasa c|cpp|python] [--validasi] [--tunggu <detik>]';

    protected $options = [
        '--bahasa' => 'Bahasa program uji (bawaan: c).',
        '--validasi' => 'Kirim pekerjaan VALIDATE untuk soal kecil bawaan, bukan RUN.',
        '--tunggu' => 'Berapa detik menunggu hasil (bawaan: 60; dengan --validasi 180).',
    ];

    public function run(array $params)
    {
        $bahasa = (string) (CLI::getOption('bahasa') ?? 'c');
        $tunggu = max(1, (int) (CLI::getOption('tunggu') ?? 60));
        if (! isset(self::PROGRAM[$bahasa])) {
            CLI::write('judge:coba GAGAL: --bahasa harus c, cpp atau python');

            return EXIT_ERROR;
        }

        $denyut = Antrean::denyut();
        if ($denyut === null) {
            CLI::write('judge:coba GAGAL: worker tidak berdenyut (kunci ' . Antrean::KUNCI_DENYUT . ' tidak ada di Redis)');

            return EXIT_ERROR;
        }
        CLI::write('worker: commit ' . ($denyut['commit'] ?? '?') . ', konkurensi ' . ($denyut['konkurensi'] ?? '?') . ', image ' . ($denyut['image'] ?? '?'));

        if (CLI::getOption('validasi') !== null) {
            return $this->validasi($bahasa, max(1, (int) (CLI::getOption('tunggu') ?? 180)));
        }

        $n     = random_int(1000, 999999);
        $mulai = microtime(true);
        $id    = Antrean::kirim('RUN', ['language' => $bahasa, 'source' => self::PROGRAM[$bahasa], 'stdin' => $n . "\n"], null);
        CLI::write("pekerjaan {$id} dikirim ({$bahasa}, masukan {$n})");

        $baris = null;
        while (microtime(true) - $mulai < $tunggu) {
            $baris = Antrean::sistem($id);
            if ($baris !== null && in_array($baris['status'], ['DONE', 'FAILED'], true)) {
                break;
            }
            usleep(250_000);
        }
        $lama   = (int) round((microtime(true) - $mulai) * 1000);
        $hasil  = $baris['result'] ?? [];
        $ringkas = [
            'job'     => $id,
            'status'  => $baris['status'] ?? null,
            'verdict' => $hasil['verdict'] ?? ($hasil['error'] ?? null),
            'stdout'  => $hasil['stdout'] ?? null,
            'ms'      => $lama,
        ];
        CLI::write((string) json_encode($ringkas, JSON_UNESCAPED_SLASHES));

        if (($baris['status'] ?? '') === 'DONE' && ($hasil['verdict'] ?? '') === 'OK' && ($hasil['stdout'] ?? '') === ($n * 2) . "\n") {
            CLI::write("judge:coba LULUS: keluaran benar dalam {$lama} ms");

            return EXIT_SUCCESS;
        }
        CLI::write('judge:coba GAGAL: ' . (in_array($baris['status'] ?? '', ['DONE', 'FAILED'], true) ? 'hasil tidak seperti yang diharapkan' : "tidak selesai dalam {$tunggu} detik"));

        return EXIT_ERROR;
    }

    /**
     * Satu soal kecil lewat VALIDATE: masukannya sebuah bilangan, keluarannya dua kalinya.
     */
    private function validasi(string $bahasa, int $tunggu): int
    {
        $mulai = microtime(true);
        $id    = Antrean::kirim('VALIDATE', [
            'language' => $bahasa,
            'generator' => "import os, random\nprint(random.Random(int(os.environ['SEED']) * 100 + int(os.environ['INDEX'])).randint(1, 10**6))\n",
            'reference_solution' => self::PROGRAM[$bahasa],
            'samples_count' => 2, 'hidden_count' => 10, 'time_limit_ms' => $bahasa === 'python' ? 5000 : 2000,
            'allowed_concepts' => ['IO_DASAR', 'VARIABEL_TIPE', 'ARITMETIKA', 'PERCABANGAN', 'KONVERSI_TIPE'],
            'comparison_mode' => 'trimmed',
        ], null);
        CLI::write("pekerjaan {$id} dikirim (VALIDATE, {$bahasa})");

        $baris = null;
        while (microtime(true) - $mulai < $tunggu) {
            $baris = Antrean::sistem($id);
            if ($baris !== null && in_array($baris['status'], ['DONE', 'FAILED'], true)) {
                break;
            }
            usleep(250_000);
        }
        $lama  = (int) round((microtime(true) - $mulai) * 1000);
        $hasil = $baris['result'] ?? [];
        CLI::write((string) json_encode([
            'job' => $id, 'status' => $baris['status'] ?? null, 'ok' => $hasil['ok'] ?? ($hasil['error'] ?? null),
            'tests' => $hasil['tests'] ?? null, 'reasons' => array_column($hasil['reasons'] ?? [], 'code'), 'ms' => $lama,
        ], JSON_UNESCAPED_SLASHES));

        if (($baris['status'] ?? '') === 'DONE' && ($hasil['ok'] ?? false) === true && ($hasil['tests'] ?? 0) === 240) {
            CLI::write("judge:coba LULUS: soal divalidasi dengan 240 tes dalam {$lama} ms");

            return EXIT_SUCCESS;
        }
        CLI::write('judge:coba GAGAL: ' . (in_array($baris['status'] ?? '', ['DONE', 'FAILED'], true) ? 'soal bawaan tidak lolos validasi' : "tidak selesai dalam {$tunggu} detik"));

        return EXIT_ERROR;
    }
}
