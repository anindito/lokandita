<?php

declare(strict_types=1);

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use Lokandita\Auth\UserDirectory;

/**
 * Halaman log audit (SPEC-LOKAUJIAN.md §13), hanya superadmin (filter rute). Hanya MEMBACA:
 * tidak ada rute yang mengubah atau menghapus baris audit.
 */
final class AuditLog extends BaseController
{
    private const PER_HALAMAN = 50;

    public function daftar(): ResponseInterface
    {
        $db     = db_connect();
        $syarat = ['1 = 1'];
        $ikat   = [];
        $saring = ['aksi' => '', 'matkul' => '', 'pelaku' => '', 'dari' => '', 'sampai' => ''];
        foreach (array_keys($saring) as $k) {
            $saring[$k] = trim((string) $this->request->getGet($k));
        }
        if (preg_match('/^[a-z_.]{1,60}$/', $saring['aksi']) === 1) {
            $syarat[] = 'action = ?';
            $ikat[]   = $saring['aksi'];
        }
        if (preg_match('/^\d{1,18}$/', $saring['matkul']) === 1) {
            $syarat[] = 'course_id = ?';
            $ikat[]   = (int) $saring['matkul'];
        }
        if ($saring['pelaku'] !== '') {
            // Disaring menurut nomor induk; nomor yang tidak dikenal tidak menghasilkan baris.
            $orang    = UserDirectory::byNomorInduk([$saring['pelaku']]);
            $syarat[] = 'actor_id = ?';
            $ikat[]   = (string) ($orang[$saring['pelaku']]['id'] ?? '');
        }
        // Tanggal di form adalah tanggal WIB; kolomnya UTC.
        $wib = new \DateTimeZone('Asia/Jakarta');
        foreach (['dari' => ['>=', ''], 'sampai' => ['<', '+1 day']] as $k => [$banding, $geser]) {
            $t = \DateTimeImmutable::createFromFormat('!Y-m-d', $saring[$k], $wib);
            if ($t !== false && $t->format('Y-m-d') === $saring[$k]) {
                $syarat[] = "created_at {$banding} ?";
                $ikat[]   = ($geser === '' ? $t : $t->modify($geser))->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s');
            }
        }
        $di     = implode(' AND ', $syarat);
        $jumlah = (int) $db->query("SELECT COUNT(*) AS n FROM audit_log WHERE {$di}", $ikat)->getRowArray()['n'];
        $hal    = max(1, min((int) $this->request->getGet('hal'), (int) ceil(max(1, $jumlah) / self::PER_HALAMAN)));
        $baris  = $db->query("SELECT * FROM audit_log WHERE {$di} ORDER BY id DESC LIMIT " . self::PER_HALAMAN . ' OFFSET ' . (($hal - 1) * self::PER_HALAMAN), $ikat)->getResultArray();

        return $this->halaman('audit_daftar', [
            'judul'  => lang('Hasil.audit.judul'),
            'baris'  => $baris,
            'jumlah' => $jumlah,
            'hal'    => $hal,
            'akhir'  => (int) ceil(max(1, $jumlah) / self::PER_HALAMAN),
            'saring' => $saring,
            'aksi'   => array_column($db->query('SELECT DISTINCT action FROM audit_log ORDER BY action')->getResultArray(), 'action'),
            'matkul' => array_column($this->roster()->daftarMatkul(), 'code', 'id'),
            'orang'  => UserDirectory::byIds(array_values(array_unique(array_map(static fn (array $b): string => (string) $b['actor_id'], $baris)))),
        ]);
    }
}
