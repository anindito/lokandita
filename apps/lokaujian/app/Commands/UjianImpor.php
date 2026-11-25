<?php

declare(strict_types=1);

namespace App\Commands;

use App\Libraries\Berkas\Impor;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Impor satu ujian dari berkas JSON (SPEC-LOKAUJIAN.md §6.3, KONTRAK.md §20).
 *
 *   docker exec -i lokaujian php spark ujian:impor --oleh <nomor induk> < ujian.json
 *
 * Tanpa argumen berkas, isinya dibaca dari masukan standar, sehingga berkas di host tidak perlu
 * disalin ke dalam kontainer. --oleh wajib: superadmin yang aktif, atau pengampu mata kuliah
 * yang disebut berkas itu. Keluar 0 hanya bila berkas diterima; ujian barunya berstatus draf.
 */
final class UjianImpor extends BaseCommand
{
    protected $group = 'Lokandita';

    protected $name = 'ujian:impor';

    protected $description = 'Mengimpor satu ujian dari berkas JSON menjadi draf baru.';

    protected $usage = 'ujian:impor [berkas] --oleh <nomor induk> [--nama <nama berkas>] < ujian.json';

    protected $arguments = ['berkas' => 'Jalur berkas JSON di dalam kontainer. Bila tidak disebut, berkas dibaca dari masukan standar.'];

    protected $options = [
        '--oleh' => 'Nomor induk superadmin atau pengampu yang bertanggung jawab atas impor ini (wajib).',
        '--nama' => 'Nama berkas yang dicatat di log audit (bawaan: nama berkasnya, atau stdin.json).',
    ];

    public function run(array $params)
    {
        $berkas = (string) ($params[0] ?? '-'); // '-' = masukan standar
        $oleh   = CLI::getOption('oleh');
        $akses  = Pelaku::pasang(is_string($oleh) ? $oleh : '');
        if (is_string($akses)) {
            CLI::write('ujian:impor GAGAL: ' . $akses);

            return EXIT_ERROR;
        }
        $isi = $berkas === '-' ? stream_get_contents(STDIN) : @file_get_contents($berkas);
        if ($isi === false || $isi === '') {
            CLI::write("ujian:impor GAGAL: berkas tidak terbaca atau kosong: {$berkas}");

            return EXIT_ERROR;
        }
        $nama  = CLI::getOption('nama');
        $nama  = is_string($nama) && $nama !== '' ? $nama : ($berkas === '-' ? 'stdin.json' : basename($berkas));
        $hasil = (new Impor($akses))->jalankan($isi, $nama);
        if (! $hasil['ok']) {
            CLI::write('ujian:impor DITOLAK: ' . count($hasil['galat']) . ' kesalahan; tidak ada yang disimpan');
            foreach ($hasil['galat'] as $g) {
                CLI::write('  ' . ($g['jalur'] === '' ? '(berkas)' : $g['jalur']) . ' (baris ' . $g['baris'] . '): ' . $g['pesan']);
            }

            return EXIT_ERROR;
        }
        CLI::write("ujian:impor DITERIMA: ujian {$hasil['ujian']} (draf); {$hasil['soal']} soal, {$hasil['soal_baru']} baru di bank; {$hasil['gambar']} gambar, {$hasil['gambar_baru']} baru");

        return EXIT_SUCCESS;
    }
}
