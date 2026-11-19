<?php

declare(strict_types=1);

namespace App\Commands;

use App\Libraries\Audit;
use App\Libraries\Importer;
use App\Libraries\Passwords;
use App\Libraries\Tabular;
use App\Libraries\UserAdmin;
use App\Libraries\Users;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Impor akun mahasiswa dari terminal server (SPEC.md §17), dengan aturan yang
 * sama dengan halaman /admin/impor: Importer::analyse lalu Importer::run.
 *
 *   docker exec -i auth php spark akun:impor --oleh <nomor induk> < berkas.csv                    pratinjau saja
 *   docker exec -i -e SANDI_AWAL auth php spark akun:impor --oleh <nomor induk> --jalankan < berkas.csv
 *
 * Berkas dibaca dari masukan standar (kolom: nomor_induk, nama, email, global_role, apps),
 * sehingga direktori data tidak pernah dipasang ke kontainer. Kata sandi awal bersama
 * dibaca dari variabel lingkungan SANDI_AWAL — tidak pernah dari argumen, dan tidak
 * pernah dicetak. Semua akun wajib mengganti kata sandi saat login pertama.
 *
 * Hanya mahasiswa yang dibuat; baris dosen dilewati dan dihitung (dosen dibuat di
 * halaman LokaAuth dengan kata sandi acak). Keluaran hanya berisi jumlah, nomor
 * baris dan alasan — tidak ada nama atau nomor induk.
 */
final class ImporAkun extends BaseCommand
{
    protected $group = 'Lokandita';

    protected $name = 'akun:impor';

    protected $description = 'Membuat akun mahasiswa dari berkas CSV/XLSX di masukan standar, dengan kata sandi awal bersama.';

    protected $usage = 'akun:impor --oleh <nomor induk superadmin> [--jalankan] [--format csv|xlsx] [--apps a,b] [--nama <nama berkas>] < berkas';

    protected $options = [
        '--oleh'     => 'Nomor induk superadmin yang aktif; tercatat sebagai pelaku (wajib).',
        '--jalankan' => 'Membuat akun. Tanpa ini hanya pratinjau. Membutuhkan SANDI_AWAL di lingkungan.',
        '--format'   => 'csv (bawaan) atau xlsx.',
        '--apps'     => 'Akses aplikasi untuk baris yang kolom apps-nya kosong. Bawaan: portal,lokakode,lokaujian,lokasiber.',
        '--nama'     => 'Nama berkas sumber, hanya untuk catatan audit.',
    ];

    public function run(array $params)
    {
        $oleh   = (string) (CLI::getOption('oleh') ?? '');
        $format = strtolower((string) (CLI::getOption('format') ?? 'csv'));
        $apps   = array_values(array_filter(explode(',', strtolower((string) (CLI::getOption('apps') ?? implode(',', UserAdmin::APPS))))));
        $tulis  = CLI::getOption('jalankan') !== null;
        if ($oleh === '' || $oleh === '1' || ! in_array($format, ['csv', 'xlsx'], true) || $apps === [] || array_diff($apps, UserAdmin::APPS) !== []) {
            return $this->gagal('pemakaian: ' . $this->usage);
        }
        $pelaku = Users::findByNomorInduk($oleh);
        if ($pelaku === null || $pelaku['status'] !== 'active' || $pelaku['global_role'] !== 'superadmin') {
            return $this->gagal("--oleh {$oleh} bukan superadmin yang aktif");
        }

        // Kata sandi diperiksa sebelum berkas dibaca, supaya salah sandi tidak membuang waktu.
        $sandi = (string) getenv('SANDI_AWAL');
        if ($tulis) {
            if ($sandi === '') {
                return $this->gagal('SANDI_AWAL kosong. Isi variabel lingkungan itu dengan kata sandi awal bersama');
            }
            $masalah = Passwords::problems($sandi);
            if ($masalah !== []) {
                return $this->gagal('SANDI_AWAL ditolak: ' . lang('Auth.sandi.' . $masalah[0], [Passwords::MIN]));
            }
        }

        // Tabular membaca dari berkas; salinan sementara dihapus begitu selesai dibaca.
        $sementara = (string) tempnam(sys_get_temp_dir(), 'impor');
        try {
            file_put_contents($sementara, (string) stream_get_contents(STDIN));
            $baris = Tabular::read($sementara, $format);
        } catch (\Throwable $e) {
            return $this->gagal('berkas tidak bisa dibaca sebagai ' . $format);
        } finally {
            @unlink($sementara);
        }
        if ($baris === []) {
            return $this->gagal('berkas kosong, atau hanya berisi baris judul');
        }
        $kurang = array_diff(['nomor_induk', 'nama', 'global_role'], array_keys($baris[0]));
        if ($kurang !== []) {
            return $this->gagal('kolom tidak ada di baris judul: ' . implode(', ', $kurang) . '. Kolom yang dikenal: ' . implode(', ', Importer::KOLOM));
        }
        if (count($baris) > Importer::MAKS_BARIS) {
            return $this->gagal('berkas berisi ' . count($baris) . ' baris; paling banyak ' . Importer::MAKS_BARIS . ' per berkas');
        }

        // Baris selain mahasiswa dipisahkan dulu: bukan kesalahan, hanya bukan urusan perintah ini.
        $mahasiswa = [];
        $lain      = 0;
        foreach ($baris as $b) {
            if (strtolower((string) $b['global_role']) === 'dosen') {
                $lain++;

                continue;
            }
            if (trim((string) ($b['apps'] ?? '')) === '') {
                $b['apps'] = implode('|', $apps);
            }
            $mahasiswa[] = $b;
        }
        $hasil = Importer::analyse($mahasiswa);

        CLI::write(sprintf('berkas: %d baris data', count($baris)));
        CLI::write(sprintf('akun baru: %d · sudah terdaftar (tidak diubah): %d · dosen (dilewati): %d · bermasalah: %d',
            count($hasil['baru']), count($hasil['ada']), $lain, count($hasil['salah'])));
        foreach ($hasil['salah'] as $s) {
            CLI::write(sprintf('  baris %d: %s', $s['baris'], implode(', ', $s['alasan'])));
        }

        if (! $tulis) {
            CLI::write('PRATINJAU: tidak ada akun yang dibuat. Ulangi dengan --jalankan untuk membuat.');

            return EXIT_SUCCESS;
        }
        if ($hasil['salah'] !== []) {
            return $this->gagal('ada baris bermasalah. Perbaiki berkasnya dulu, supaya jumlah akun sama dengan berkas');
        }

        $jalan = Importer::run($hasil['baru'], $sandi, (string) $pelaku['id']);
        Audit::log('user.imported', (string) $pelaku['id'], null, [
            'file'            => substr((string) (CLI::getOption('nama') ?? 'stdin'), 0, 190),
            'created'         => count($jalan['dibuat']),
            'skipped'         => $jalan['dilewati'],
            'already_existed' => count($hasil['ada']),
            'invalid'         => 0,
            'not_student'     => $lain,
            'password_mode'   => 'shared',
            'lewat'           => 'cli',
        ]);
        CLI::write(sprintf('DITULIS: %d akun dibuat, %d dilewati. Semua wajib mengganti kata sandi saat login pertama.', count($jalan['dibuat']), $jalan['dilewati']));

        return $jalan['dilewati'] === 0 ? EXIT_SUCCESS : EXIT_ERROR;
    }

    private function gagal(string $pesan): int
    {
        CLI::write('akun:impor GAGAL: ' . $pesan . '. Tidak ada akun yang dibuat.');

        return EXIT_ERROR;
    }
}
