<?php

declare(strict_types=1);

namespace App\Commands;

use App\Libraries\DataAwal;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Lokandita\Auth\UserDirectory;

/**
 * Mendaftarkan mahasiswa ke kelas (SPEC.md §17, KONTRAK.md §13).
 *
 *   docker exec -i lokakode php spark data:pendaftaran --oleh <nomor induk> < berkas.csv              pratinjau saja
 *   docker exec -i lokakode php spark data:pendaftaran --oleh <nomor induk> --jalankan < berkas.csv   menulis
 *
 * Kolom: nomor_induk, mata_kuliah, kelas (kolom lain diabaikan). Berkas dibaca dari masukan standar. Satu baris yang
 * bermasalah membatalkan seluruh penulisan, supaya jumlah di basis data sama
 * dengan berkas. Aman diulang. Keluaran tidak memuat nama atau nomor induk.
 */
final class DataPendaftaran extends BaseCommand
{
    protected $group = 'Lokandita';

    protected $name = 'data:pendaftaran';

    protected $description = 'Mendaftarkan mahasiswa ke kelas dari berkas CSV di masukan standar.';

    protected $usage = 'data:pendaftaran --oleh <nomor induk superadmin> [--jalankan] < berkas.csv';

    protected $options = [
        '--oleh'     => 'Nomor induk superadmin yang aktif; tercatat sebagai pelaku (wajib).',
        '--jalankan' => 'Menulis ke basis data. Tanpa ini hanya pratinjau.',
    ];

    public function run(array $params)
    {
        $oleh   = CLI::getOption('oleh');
        $oleh   = is_string($oleh) ? $oleh : '';
        $pelaku = $oleh === '' ? null : (UserDirectory::byNomorInduk([$oleh])[$oleh] ?? null);
        if ($pelaku === null || $pelaku['status'] !== 'active' || $pelaku['global_role'] !== 'superadmin') {
            CLI::write("data:pendaftaran GAGAL: --oleh {$oleh} bukan superadmin yang aktif. Tidak ada yang diubah.");

            return EXIT_ERROR;
        }
        $tulis = CLI::getOption('jalankan') !== null;

        try {
            $baris = DataAwal::bacaCsv((string) stream_get_contents(STDIN), ['nomor_induk', 'mata_kuliah', 'kelas']);
        } catch (\RuntimeException $e) {
            CLI::write('data:pendaftaran GAGAL: ' . $e->getMessage() . '. Tidak ada yang diubah.');

            return EXIT_ERROR;
        }
        $hasil = DataAwal::pendaftaran($baris, (string) $pelaku['id'], $tulis);

        CLI::write(sprintf('berkas: %d baris data', count($baris)));
        foreach ($hasil['baris'] as $b) {
            CLI::write($b);
        }
        if (! $tulis) {
            CLI::write('PRATINJAU: tidak ada yang ditulis. Ulangi dengan --jalankan untuk menulis.');

            return EXIT_SUCCESS;
        }
        if ($hasil['salah'] > 0) {
            CLI::write('data:pendaftaran GAGAL: ada baris bermasalah. Tidak ada yang diubah.');

            return EXIT_ERROR;
        }
        CLI::write(sprintf('DITULIS: %d.', $hasil['ditulis']));

        return EXIT_SUCCESS;
    }
}
