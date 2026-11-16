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
 *
 * Keluar 0 hanya bila worker berdenyut dan program menghasilkan keluaran yang benar.
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

    protected $usage = 'judge:coba [--bahasa c|cpp|python] [--tunggu <detik>]';

    protected $options = [
        '--bahasa' => 'Bahasa program uji (bawaan: c).',
        '--tunggu' => 'Berapa detik menunggu hasil (bawaan: 60).',
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
}
