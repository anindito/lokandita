<?php

declare(strict_types=1);

namespace App\Commands;

use App\Libraries\DataAwal;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Gerbang tahap 12 (SPEC.md §18): jumlah akun dan kelas sama dengan berkas sumber.
 *
 *   docker exec -i lokakode php spark data:cocokkan mahasiswa < import-mahasiswa.csv
 *   docker exec -i lokakode php spark data:cocokkan dosen     < import-dosen.csv
 *   docker exec -i lokakode php spark data:cocokkan pengajar  < penugasan-pengajar.csv
 *
 * Hanya membaca. Keluar 0 hanya bila semua baris COCOK. Keluaran berisi jumlah,
 * nama kelas dan nomor baris berkas — tidak ada nama atau nomor induk.
 */
final class DataCocokkan extends BaseCommand
{
    private const KOLOM = [
        'mahasiswa' => ['nomor_induk', 'mata_kuliah', 'kelas'],
        'dosen'     => ['nomor_induk'],
        'pengajar'  => ['mata_kuliah', 'kelas', 'nip_pengajar'],
    ];

    protected $group = 'Lokandita';

    protected $name = 'data:cocokkan';

    protected $description = 'Mencocokkan berkas sumber (mahasiswa, dosen atau pengajar) dengan basis data. Hanya membaca.';

    protected $usage = 'data:cocokkan <mahasiswa|dosen|pengajar> < berkas.csv';

    protected $arguments = ['jenis' => 'mahasiswa, dosen atau pengajar.'];

    public function run(array $params)
    {
        $jenis = (string) ($params[0] ?? '');
        if (! isset(self::KOLOM[$jenis])) {
            CLI::write('data:cocokkan GAGAL: pemakaian: ' . $this->usage);

            return EXIT_ERROR;
        }
        try {
            $baris = DataAwal::bacaCsv((string) stream_get_contents(STDIN), self::KOLOM[$jenis]);
        } catch (\RuntimeException $e) {
            CLI::write('data:cocokkan GAGAL: ' . $e->getMessage());

            return EXIT_ERROR;
        }
        $hasil = DataAwal::cocokkan($jenis, $baris);
        CLI::write(sprintf('berkas %s: %d baris data', $jenis, count($baris)));
        foreach ($hasil['baris'] as $b) {
            CLI::write($b);
        }
        CLI::write('data:cocokkan ' . $jenis . ': ' . ($hasil['cocok'] ? 'COCOK' : 'BEDA'));

        return $hasil['cocok'] ? EXIT_SUCCESS : EXIT_ERROR;
    }
}
