<?php

declare(strict_types=1);

namespace App\Commands;

use App\Libraries\Antrean;
use App\Libraries\Soal\Impor;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Lokandita\Auth\UserDirectory;

/**
 * Impor soal dari berkas JSON (SPEC.md §8.3, KONTRAK.md §7).
 *
 *   docker exec -i lokakode php spark soal:impor --oleh <nomor induk superadmin> < berkas.json
 *
 * Tanpa argumen berkas, isinya dibaca dari masukan standar, sehingga berkas di
 * host tidak perlu disalin ke dalam kontainer. --oleh wajib: setiap impor tercatat di log audit
 * atas nama seseorang, dan hanya superadmin yang boleh mengimpor.
 *
 * Keluar 0 hanya bila berkas diterima dan tidak ada soal yang ditolak.
 */
final class SoalImpor extends BaseCommand
{
    protected $group = 'Lokandita';

    protected $name = 'soal:impor';

    protected $description = 'Mengimpor soal dari berkas JSON; setiap soal divalidasi judge sebelum disimpan.';

    protected $usage = 'soal:impor [berkas] --oleh <nomor induk superadmin> [--nama <nama berkas>] [--tunggu <detik>] < berkas.json';

    protected $arguments = ['berkas' => 'Jalur berkas JSON di dalam kontainer. Bila tidak disebut, berkas dibaca dari masukan standar.'];

    protected $options = [
        '--oleh'   => 'Nomor induk superadmin yang bertanggung jawab atas impor ini (wajib).',
        '--nama'   => 'Nama berkas yang dicatat di log audit (bawaan: nama berkasnya, atau stdin.json).',
        '--tunggu' => 'Berapa detik menunggu judge (bawaan: 3600).',
    ];

    public function run(array $params)
    {
        $berkas = (string) ($params[0] ?? '-'); // '-' = masukan standar
        $oleh   = CLI::getOption('oleh');
        $oleh   = is_string($oleh) ? $oleh : '';
        $tunggu = max(10, (int) (CLI::getOption('tunggu') ?? 3600));
        if ($oleh === '') {
            CLI::write('soal:impor GAGAL: pemakaian: ' . $this->usage);

            return EXIT_ERROR;
        }
        $pelaku = UserDirectory::byNomorInduk([$oleh])[$oleh] ?? null;
        if ($pelaku === null || $pelaku['global_role'] !== 'superadmin' || $pelaku['status'] !== 'active') {
            CLI::write("soal:impor GAGAL: --oleh {$oleh} bukan superadmin yang aktif");

            return EXIT_ERROR;
        }
        $isi = $berkas === '-' ? stream_get_contents(STDIN) : @file_get_contents($berkas);
        if ($isi === false || $isi === '') {
            CLI::write("soal:impor GAGAL: berkas tidak terbaca atau kosong: {$berkas}");

            return EXIT_ERROR;
        }
        if (Antrean::denyut() === null) {
            CLI::write('soal:impor GAGAL: judge worker tidak berjalan; soal tidak bisa divalidasi');

            return EXIT_ERROR;
        }

        $impor = new Impor();
        $nama  = CLI::getOption('nama');
        $nama  = is_string($nama) && $nama !== '' ? $nama : ($berkas === '-' ? 'stdin.json' : basename($berkas));
        $id    = $impor->mulai($isi, $nama, (string) $pelaku['id'], 'cli');
        CLI::write("impor {$id}");

        $batas = time() + $tunggu;
        $lalu  = -1;
        while (true) {
            $impor->majukan($id);
            $lap = $impor->laporan($id);
            if ($lap['status'] !== 'MENUNGGU') {
                break;
            }
            if ($lap['hitung']['waiting'] !== $lalu) {
                $lalu = $lap['hitung']['waiting'];
                CLI::write("  menunggu judge: {$lalu} dari {$lap['hitung']['total']} soal");
            }
            if (time() > $batas) {
                CLI::write("soal:impor GAGAL: judge belum selesai setelah {$tunggu} detik. Impor {$id} tetap berjalan; lihat halaman /impor/{$id}.");

                return EXIT_ERROR;
            }
            sleep(1);
        }

        return self::cetak($lap) ? EXIT_SUCCESS : EXIT_ERROR;
    }

    /**
     * Mencetak laporan; benar bila berkas diterima dan tidak ada soal yang ditolak.
     *
     * @param array<string, mixed> $lap hasil Impor::laporan()
     */
    public static function cetak(array $lap): bool
    {
        if ($lap['status'] === 'DITOLAK') {
            CLI::write('BERKAS DITOLAK — tidak ada soal yang diperiksa:');
            foreach ($lap['file_errors'] as $g) {
                CLI::write('  - ' . ($g['field'] !== '' ? $g['field'] . ': ' : '') . lang('Soal.skema.' . $g['kode'], $g['argumen']));
            }

            return false;
        }
        CLI::write("{$lap['matkul_kode']} pertemuan {$lap['pertemuan_nomor']} — {$lap['file_name']}");
        $nama = ['DITERIMA' => 'DITERIMA', 'DITOLAK' => 'DITOLAK ', 'TIDAK_BERUBAH' => 'SAMA    ', 'MENUNGGU' => 'MENUNGGU'];
        foreach ($lap['butir'] as $b) {
            $versi = $b['version'] !== null ? " (versi {$b['version']}, menunggu tinjauan)" : '';
            CLI::write(sprintf('%s  %2d. %s%s', $nama[$b['state']], $b['urutan'], $b['slug'] ?? '(tanpa slug)', $versi)
                . ($b['json_line'] !== null ? "  [baris JSON {$b['json_line']}]" : ''));
            foreach ($b['reasons'] as $a) {
                $letak = Impor::letak($a);
                CLI::write('          - ' . ($letak !== '' ? $letak . ': ' : '') . Impor::kalimat($a));
            }
        }
        $h = $lap['hitung'];
        CLI::write("ringkasan: {$h['accepted']} diterima, {$h['rejected']} ditolak, {$h['unchanged']} tidak berubah, dari {$h['total']} soal");

        return $h['rejected'] === 0;
    }
}
