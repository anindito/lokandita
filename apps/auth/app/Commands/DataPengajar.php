<?php

declare(strict_types=1);

namespace App\Commands;

use App\Libraries\RosterBerkas;
use App\Libraries\Users;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Menugaskan pengajar ke kelas dari berkas CSV (SPEC.md §17, KONTRAK.md §13). Sejak tahap A2 perintah ini
 * ada di Akun Lokandita, satu-satunya penulis roster (KONTRAK.md §18).
 *
 *   docker exec -i auth php spark data:pengajar --oleh <nomor induk> < berkas.csv              pratinjau saja
 *   docker exec -i auth php spark data:pengajar --oleh <nomor induk> --jalankan < berkas.csv   menulis
 *
 * Kolom: mata_kuliah, kelas, nip_pengajar. Berkas dibaca dari masukan standar. Satu baris yang bermasalah
 * membatalkan seluruh penulisan, supaya jumlah di basis data sama dengan berkas. Aman diulang.
 * Keluaran tidak memuat nama atau nomor induk.
 */
final class DataPengajar extends BaseCommand
{
    protected $group = 'Lokandita';

    protected $name = 'data:pengajar';

    protected $description = 'Menugaskan pengajar ke kelas dari berkas CSV di masukan standar.';

    protected $usage = 'data:pengajar --oleh <nomor induk superadmin> [--jalankan] < berkas.csv';

    protected $options = [
        '--oleh'     => 'Nomor induk superadmin yang aktif; tercatat sebagai pelaku (wajib).',
        '--jalankan' => 'Menulis ke basis data. Tanpa ini hanya pratinjau.',
    ];

    public function run(array $params)
    {
        $oleh   = CLI::getOption('oleh');
        $oleh   = is_string($oleh) ? $oleh : '';
        $pelaku = $oleh === '' ? null : Users::findByNomorInduk($oleh);
        if ($pelaku === null || $pelaku['status'] !== 'active' || $pelaku['global_role'] !== 'superadmin') {
            CLI::write("data:pengajar GAGAL: --oleh {$oleh} bukan superadmin yang aktif. Tidak ada yang diubah.");

            return EXIT_ERROR;
        }
        $tulis = CLI::getOption('jalankan') !== null;

        try {
            $baris = RosterBerkas::bacaCsv((string) stream_get_contents(STDIN), ['mata_kuliah', 'kelas', 'nip_pengajar']);
        } catch (\RuntimeException $e) {
            CLI::write('data:pengajar GAGAL: ' . $e->getMessage() . '. Tidak ada yang diubah.');

            return EXIT_ERROR;
        }
        $hasil = RosterBerkas::pengajar($baris, (string) $pelaku['id'], $tulis);

        CLI::write(sprintf('berkas: %d baris data', count($baris)));
        foreach ($hasil['baris'] as $b) {
            CLI::write($b);
        }
        if (! $tulis) {
            CLI::write('PRATINJAU: tidak ada yang ditulis. Ulangi dengan --jalankan untuk menulis.');

            return EXIT_SUCCESS;
        }
        if ($hasil['salah'] > 0) {
            CLI::write('data:pengajar GAGAL: ada baris bermasalah. Tidak ada yang diubah.');

            return EXIT_ERROR;
        }
        CLI::write(sprintf('DITULIS: %d.', $hasil['ditulis']));

        return EXIT_SUCCESS;
    }
}
