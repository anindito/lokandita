<?php

declare(strict_types=1);

namespace App\Commands;

use App\Libraries\Audit;
use App\Libraries\Clock;
use App\Libraries\Passwords;
use App\Libraries\Users;
use App\Libraries\Uuid;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Membuat akun superadmin dari terminal server. Inilah satu-satunya cara
 * membuat superadmin pertama; tidak ada pendaftaran lewat web (SPEC.md §1).
 *
 *   docker exec -it auth php spark akun:superadmin
 */
final class BuatSuperadmin extends BaseCommand
{
    protected $group = 'Lokandita';

    protected $name = 'akun:superadmin';

    protected $description = 'Membuat akun superadmin baru dengan kata sandi sementara.';

    protected $usage = 'akun:superadmin [--nomor <nomor induk>] [--nama <nama>]';

    protected $options = [
        '--nomor' => 'Nomor induk (NIP). Ditanyakan bila tidak diberikan.',
        '--nama'  => 'Nama lengkap. Ditanyakan bila tidak diberikan.',
    ];

    public function run(array $params)
    {
        $nomor = trim((string) (CLI::getOption('nomor') ?? CLI::prompt('Nomor induk (NIP)')));
        $nama  = trim((string) (CLI::getOption('nama') ?? CLI::prompt('Nama lengkap')));

        if (preg_match('/^[A-Za-z0-9._-]{3,40}$/', $nomor) !== 1) {
            CLI::error('Nomor induk harus 3–40 karakter: huruf, angka, titik, garis bawah, atau tanda hubung.');

            return EXIT_ERROR;
        }
        if ($nama === '' || mb_strlen($nama) > 190) {
            CLI::error('Nama tidak boleh kosong dan paling panjang 190 karakter.');

            return EXIT_ERROR;
        }
        if (Users::findByNomorInduk($nomor) !== null) {
            CLI::error("Nomor induk {$nomor} sudah terdaftar. Tidak ada yang diubah.");

            return EXIT_ERROR;
        }

        $id    = Uuid::v4();
        $sandi = Passwords::random();
        $db    = db_connect();
        $db->transStart();
        $db->table('users')->insert([
            'id'                   => $id,
            'nomor_induk'          => $nomor,
            'nama'                 => $nama,
            'password_hash'        => 'x',
            'must_change_password' => 1,
            'global_role'          => 'superadmin',
            'status'               => 'active',
            'created_at'           => Clock::now(),
            'updated_at'           => Clock::now(),
        ]);
        Passwords::set($id, $sandi, true);
        foreach (['portal', 'lokakode'] as $app) {
            $db->table('app_access')->insert(['user_id' => $id, 'app' => $app, 'enabled' => 1, 'granted_at' => Clock::now()]);
        }
        $db->transComplete();
        if (! $db->transStatus()) {
            CLI::error('Gagal menyimpan. Tidak ada akun yang dibuat.');

            return EXIT_ERROR;
        }
        Audit::log('user.created', null, $id, ['via' => 'cli', 'global_role' => 'superadmin', 'nomor_induk' => $nomor]);

        CLI::write('Akun superadmin dibuat.', 'green');
        CLI::write('Nomor induk          : ' . $nomor);
        CLI::write('Kata sandi sementara : ' . $sandi);
        CLI::write('Kata sandi ini hanya ditampilkan sekali dan wajib diganti saat login pertama.');
        CLI::write('Pada login pertama Anda juga akan diminta mengaktifkan verifikasi dua langkah (siapkan aplikasi autentikator di ponsel).');

        return EXIT_SUCCESS;
    }
}
