<?php

declare(strict_types=1);

namespace App\Commands;

use App\Libraries\Jadwal;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Penjadwal LokaUjian (KONTRAK.md §21), dijalankan kontainer `lokaujian-jadwal`:
 *
 *   php spark ujian:jadwal            satu putaran tanpa akhir, satu lintasan tiap 10 detik
 *   php spark ujian:jadwal --sekali   satu lintasan lalu keluar (tes; tanpa denyut)
 *   php spark ujian:jadwal --periksa  healthcheck kontainer: 0 bila denyutnya ada
 *
 * Tidak punya keadaan sendiri: MySQL sumber kebenarannya. Sesudah setiap lintasan ia menulis
 * denyut ke Redis (`lokaujian:jadwal`, hidup 60 detik) yang dibaca infra/deploy.sh,
 * infra/smoke.sh dan halaman sesi dosen. Penjadwal kedua yang menemukan denyut milik proses lain
 * yang masih MAJU keluar dengan satu baris log. Proses ini tidak menangkap sinyal: saat kontainer
 * dihentikan ia langsung mati (setiap lintasan satu pernyataan SQL), dan denyutnya hilang sendiri
 * sesudah 60 detik.
 *
 * Kode keluar 78: variabel lingkungan wajib tidak ada (seperti judge worker).
 */
final class UjianJadwal extends BaseCommand
{
    protected $group = 'Lokandita';

    protected $name = 'ujian:jadwal';

    protected $description = 'Penjadwal LokaUjian: menandai pengerjaan yang tenggatnya lewat (KONTRAK.md §21).';

    protected $usage = 'ujian:jadwal [--sekali|--periksa]';

    protected $options = [
        '--sekali'  => 'Satu lintasan lalu keluar, tanpa denyut.',
        '--periksa' => 'Tidak menjalankan apa pun: keluar 0 bila denyut penjadwal kontainer ini ada (healthcheck).',
    ];

    private static function log(string $pesan): void
    {
        fwrite(STDERR, '[lokaujian-jadwal] ' . gmdate('Y-m-d\TH:i:s\Z') . ' ' . $pesan . "\n");
    }

    public function run(array $params)
    {
        foreach (['DB_DSN', 'REDIS_URL'] as $v) {
            if ((string) getenv($v) === '') {
                self::log("GAGAL MULAI: variabel {$v} tidak ada");

                return 78;
            }
        }
        if (CLI::getOption('periksa') !== null) {
            // Pemeriksaan kesehatan kontainer: denyut ada dan milik proses penjadwal mesin ini.
            $d = Jadwal::denyut();
            if ($d === null || ! str_starts_with((string) ($d['nama'] ?? ''), gethostname() . ':')) {
                CLI::write('tidak ada denyut penjadwal dari kontainer ini');

                return EXIT_ERROR;
            }
            CLI::write((string) json_encode($d));

            return EXIT_SUCCESS;
        }
        if (CLI::getOption('sekali') !== null) {
            $n = Jadwal::lintasan();
            CLI::write("lintasan selesai: {$n} pengerjaan ditandai");

            return EXIT_SUCCESS;
        }

        $commit = (string) getenv('LOKANDITA_COMMIT');
        $pid    = (int) getmypid();
        $nama   = gethostname() . ':' . $pid;
        $redis  = Jadwal::redis();
        // Tepat satu penjadwal per skema. Denyut milik proses lain dianggap HIDUP hanya bila ia
        // maju (lintasannya bertambah) selama 25 detik pengamatan; denyut yang diam adalah sisa
        // penjadwal yang sudah mati (misalnya kontainer lama saat deploy) dan diambil alih.
        $lain = Jadwal::denyut();
        if ($lain !== null && ($lain['nama'] ?? '') !== $nama) {
            for ($i = 0; $i < 25; $i++) {
                sleep(1);
                $kini = Jadwal::denyut();
                if ($kini === null) {
                    break;
                }
                if (($kini['nama'] ?? '') === ($lain['nama'] ?? '') && (int) $kini['lintasan'] !== (int) $lain['lintasan']) {
                    self::log('penjadwal lain masih berdenyut (' . ($kini['nama'] ?? '?') . ', commit ' . substr((string) $kini['commit'], 0, 12) . '); proses ini keluar');

                    return EXIT_SUCCESS;
                }
            }
        }
        $mulai    = gmdate('Y-m-d\TH:i:s\Z');
        $lintasan = 0;
        self::log('mulai, commit ' . substr($commit, 0, 12));
        // @phpstan-ignore-next-line  putaran ini berakhir bersama prosesnya (SIGTERM dari Docker)
        while (true) {
            try {
                $n = Jadwal::lintasan();
                $lintasan++;
                if ($n > 0) {
                    self::log("{$n} pengerjaan ditandai dikumpulkan");
                }
                $redis->setex(Jadwal::KUNCI_DENYUT, Jadwal::UMUR_DENYUT, (string) json_encode([
                    'commit' => $commit, 'pid' => $pid, 'nama' => $nama, 'mulai' => $mulai, 'waktu' => gmdate('Y-m-d\TH:i:s\Z'), 'lintasan' => $lintasan,
                ]));
            } catch (\Throwable $e) {
                // MySQL atau Redis sedang tidak terjangkau: dicoba lagi pada lintasan berikutnya,
                // dengan sambungan baru. Tidak ada jawaban yang bergantung pada lintasan ini.
                self::log('lintasan gagal: ' . $e->getMessage());
                try {
                    db_connect()->reconnect();
                    $redis = Jadwal::redis();
                } catch (\Throwable) {
                }
            }
            sleep(Jadwal::JEDA_DETIK);
        }
    }
}
