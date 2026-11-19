<?php

declare(strict_types=1);

namespace App\Commands;

use App\Libraries\BenihKurikulum as Benih;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Lokandita\Auth\UserDirectory;

/**
 * Data awal kurikulum (SPEC.md §17) dari app/Data/benih.json: mata kuliah,
 * 16 pertemuan dengan konsepnya, dan kelas di tahun ajaran yang aktif.
 *
 *   docker exec lokakode php spark benih:kurikulum --oleh <nomor induk>              pratinjau saja
 *   docker exec lokakode php spark benih:kurikulum --oleh <nomor induk> --jalankan   menulis
 *
 * Yang belum ada dibuat; yang sudah ada dan berbeda hanya dilaporkan (BEDA).
 * Aman diulang. Setiap perubahan tercatat di log audit dengan `lewat: benih`.
 */
final class BenihKurikulum extends BaseCommand
{
    protected $group = 'Lokandita';

    protected $name = 'benih:kurikulum';

    protected $description = 'Membuat mata kuliah, pertemuan, konsep dan kelas awal dari app/Data/benih.json.';

    protected $usage = 'benih:kurikulum --oleh <nomor induk superadmin> [--jalankan]';

    protected $options = [
        '--oleh'     => 'Nomor induk superadmin yang aktif; tercatat sebagai pelaku (wajib).',
        '--jalankan' => 'Menulis ke basis data. Tanpa ini hanya pratinjau.',
    ];

    public function run(array $params)
    {
        $oleh = CLI::getOption('oleh');
        $oleh = is_string($oleh) ? $oleh : '';
        if ($oleh === '') {
            CLI::write('benih:kurikulum GAGAL: pemakaian: ' . $this->usage);

            return EXIT_ERROR;
        }
        $pelaku = UserDirectory::byNomorInduk([$oleh])[$oleh] ?? null;
        if ($pelaku === null || $pelaku['status'] !== 'active' || $pelaku['global_role'] !== 'superadmin') {
            CLI::write("benih:kurikulum GAGAL: --oleh {$oleh} bukan superadmin yang aktif. Tidak ada yang diubah.");

            return EXIT_ERROR;
        }
        $tulis = CLI::getOption('jalankan') !== null;

        try {
            $hasil = Benih::terapkan(Benih::berkas(), (string) $pelaku['id'], $tulis);
        } catch (\RuntimeException|\JsonException $e) {
            CLI::write('benih:kurikulum GAGAL: ' . $e->getMessage() . '. Tidak ada yang diubah.');

            return EXIT_ERROR;
        }

        foreach ($hasil['baris'] as $baris) {
            CLI::write($baris);
        }
        $h = $hasil['hitung'];
        CLI::write(sprintf('ringkasan: %d dibuat, %d diubah, %d beda (tidak disentuh), %d sudah sama', $h['dibuat'], $h['diubah'], $h['beda'], $h['sama']));
        CLI::write($tulis ? 'DITULIS.' : 'PRATINJAU: tidak ada yang ditulis. Ulangi dengan --jalankan untuk menulis.');

        return EXIT_SUCCESS;
    }
}
