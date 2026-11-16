<?php

declare(strict_types=1);

namespace App\Commands;

use App\Libraries\Soal\Ekspor;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Ekspor soal satu pertemuan ke bentuk KONTRAK.md §7 (SPEC.md §8.3).
 *
 *   docker exec lokakode php spark soal:ekspor DDP101 4 > ddp101-04.json
 *
 * Yang diekspor adalah versi TERAKHIR setiap soal, apa pun statusnya. Berkas
 * hasilnya bisa disunting lalu diimpor lagi; soal yang tidak diubah dilaporkan
 * "tidak berubah". JSON ditulis ke keluaran standar, pesan lain ke stderr.
 */
final class SoalEkspor extends BaseCommand
{
    protected $group = 'Lokandita';

    protected $name = 'soal:ekspor';

    protected $description = 'Mengekspor soal satu pertemuan sebagai JSON berbentuk KONTRAK.md §7.';

    protected $usage = 'soal:ekspor <kode mata kuliah> <nomor pertemuan>';

    protected $arguments = ['kode' => 'Kode mata kuliah, misalnya DDP101.', 'pertemuan' => 'Nomor pertemuan, 1–16.'];

    public function run(array $params)
    {
        $kode  = strtoupper((string) ($params[0] ?? ''));
        $nomor = (int) ($params[1] ?? 0);
        $data  = Ekspor::pertemuan($kode, $nomor);
        if ($data === null) {
            fwrite(STDERR, "soal:ekspor GAGAL: mata kuliah {$kode} pertemuan {$nomor} tidak ada\n");

            return EXIT_ERROR;
        }
        fwrite(STDOUT, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n");
        fwrite(STDERR, 'soal:ekspor: ' . count($data['problems']) . " soal\n");

        return EXIT_SUCCESS;
    }
}
