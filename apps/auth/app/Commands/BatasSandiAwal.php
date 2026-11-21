<?php

declare(strict_types=1);

namespace App\Commands;

use App\Libraries\Audit;
use App\Libraries\Clock;
use App\Libraries\Users;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Memberi batas waktu pada kata sandi awal mahasiswa (KONTRAK.md §13).
 *
 *   docker exec auth php spark akun:batas-sandi-awal --oleh <nomor induk> --sampai 2026-12-01              pratinjau saja
 *   docker exec auth php spark akun:batas-sandi-awal --oleh <nomor induk> --sampai 2026-12-01 --jalankan
 *
 * Yang dikenai: mahasiswa aktif yang masih wajib mengganti kata sandi dan belum pernah
 * mendapat kata sandi lain sejak akunnya dibuat (satu baris saja di password_history).
 * Kata sandi awal itu masih bisa dipakai sampai akhir tanggal --sampai (WIB). Sesudahnya
 * login ditolak seperti kata sandi yang salah, sampai pengajar atau superadmin mereset
 * kata sandinya. Mengganti atau mereset kata sandi menghapus batas itu.
 *
 * Boleh diulang dengan tanggal lain. Keluaran hanya berisi jumlah.
 */
final class BatasSandiAwal extends BaseCommand
{
    protected $group = 'Lokandita';

    protected $name = 'akun:batas-sandi-awal';

    protected $description = 'Memberi batas waktu pada kata sandi awal mahasiswa yang belum diganti.';

    protected $usage = 'akun:batas-sandi-awal --oleh <nomor induk superadmin> --sampai <YYYY-MM-DD> [--jalankan]';

    protected $options = [
        '--oleh'     => 'Nomor induk superadmin yang aktif; tercatat sebagai pelaku (wajib).',
        '--sampai'   => 'Tanggal terakhir kata sandi awal masih berlaku, YYYY-MM-DD, menurut WIB (wajib).',
        '--jalankan' => 'Menulis batasnya. Tanpa ini hanya pratinjau.',
    ];

    private const SASARAN = "global_role = 'mahasiswa' AND status = 'active' AND must_change_password = 1
        AND (SELECT COUNT(*) FROM password_history h WHERE h.user_id = users.id) = 1";

    public function run(array $params)
    {
        $oleh   = (string) (CLI::getOption('oleh') ?? '');
        $sampai = (string) (CLI::getOption('sampai') ?? '');
        $tulis  = CLI::getOption('jalankan') !== null;

        $hari = \DateTimeImmutable::createFromFormat('!Y-m-d', $sampai, new \DateTimeZone('Asia/Jakarta'));
        if ($oleh === '' || $oleh === '1' || $hari === false || $hari->format('Y-m-d') !== $sampai) {
            return $this->gagal('pemakaian: ' . $this->usage);
        }
        $pelaku = Users::findByNomorInduk($oleh);
        if ($pelaku === null || $pelaku['status'] !== 'active' || $pelaku['global_role'] !== 'superadmin') {
            return $this->gagal("--oleh {$oleh} bukan superadmin yang aktif");
        }
        // Berlaku sampai akhir hari itu di WIB = awal hari berikutnya, disimpan dalam UTC.
        $batas = $hari->modify('+1 day')->setTimezone(new \DateTimeZone('UTC'));
        if ($batas->getTimestamp() <= time()) {
            return $this->gagal('tanggal --sampai sudah lewat');
        }
        $batasUtc = $batas->format('Y-m-d H:i:s');

        $db     = db_connect();
        $jumlah = (int) $db->query('SELECT COUNT(*) AS n FROM users WHERE ' . self::SASARAN)->getRow()->n;
        $sudah  = (int) $db->query('SELECT COUNT(*) AS n FROM users WHERE ' . self::SASARAN . ' AND initial_password_expires_at = ?', [$batasUtc])->getRow()->n;

        CLI::write(sprintf('mahasiswa aktif yang masih memakai kata sandi awal: %d (sudah berbatas waktu ini: %d)', $jumlah, $sudah));
        CLI::write(sprintf('kata sandi awal berlaku sampai: %s 23:59 WIB (tersimpan %s UTC)', $hari->format('d-m-Y'), $batasUtc));

        if (! $tulis) {
            CLI::write('PRATINJAU: tidak ada yang ditulis. Ulangi dengan --jalankan untuk menulis.');

            return EXIT_SUCCESS;
        }

        $db->transStart();
        $db->query('UPDATE users SET initial_password_expires_at = ?, updated_at = ? WHERE ' . self::SASARAN
            . ' AND (initial_password_expires_at IS NULL OR initial_password_expires_at <> ?)', [$batasUtc, Clock::now(), $batasUtc]);
        $diubah = $db->affectedRows();
        Audit::log('password.initial_deadline', (string) $pelaku['id'], null, [
            'until' => $batasUtc, 'accounts' => $jumlah, 'changed' => $diubah, 'lewat' => 'cli',
        ]);
        $db->transComplete();
        if ($db->transStatus() === false) {
            return $this->gagal('basis data menolak perubahan');
        }
        CLI::write(sprintf('DITULIS: %d akun diberi batas, %d sudah berbatas sama.', $diubah, $jumlah - $diubah));

        return EXIT_SUCCESS;
    }

    private function gagal(string $pesan): int
    {
        CLI::write('akun:batas-sandi-awal GAGAL: ' . $pesan . '. Tidak ada yang ditulis.');

        return EXIT_ERROR;
    }
}
