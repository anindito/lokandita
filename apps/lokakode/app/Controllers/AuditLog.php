<?php

declare(strict_types=1);

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use Lokandita\Auth\UserDirectory;

/**
 * Log audit LokaKode: superadmin saja, hanya membaca. Tidak ada rute yang
 * mengubah atau menghapus baris audit.
 */
final class AuditLog extends BaseController
{
    private const PER_HALAMAN = 100;

    public function index(): ResponseInterface
    {
        $s = [
            'aksi'   => trim((string) $this->request->getGet('aksi')),
            'aktor'  => trim((string) $this->request->getGet('aktor')),
            'dari'   => trim((string) $this->request->getGet('dari')),
            'sampai' => trim((string) $this->request->getGet('sampai')),
        ];
        // Nomor induk pelaku diterjemahkan ke id lewat direktori LokaAuth; tidak dikenal = tidak ada hasil.
        $aktorId = null;
        if ($s['aktor'] !== '') {
            $aktorId = UserDirectory::byNomorInduk([$s['aktor']])[$s['aktor']]['id'] ?? '';
        }
        $q = function () use ($s, $aktorId) {
            $b = db_connect()->table('audit_log');
            if ($s['aksi'] !== '') {
                $b->like('action', $s['aksi'], 'after');
            }
            if ($aktorId !== null) {
                $b->where('actor_id', $aktorId);
            }
            // Tanggal diisi dalam waktu Jakarta (UTC+7); basis data menyimpan UTC.
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $s['dari']) === 1) {
                $b->where('created_at >=', gmdate('Y-m-d H:i:s', (int) strtotime($s['dari'] . ' 00:00:00 +07:00')));
            }
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $s['sampai']) === 1) {
                $b->where('created_at <', gmdate('Y-m-d H:i:s', (int) strtotime($s['sampai'] . ' 00:00:00 +07:00') + 86400));
            }

            return $b;
        };
        $jumlah = $q()->countAllResults();
        $akhir  = max(1, (int) ceil($jumlah / self::PER_HALAMAN));
        $hal    = min($akhir, max(1, (int) $this->request->getGet('hal')));
        $baris  = $q()->orderBy('id', 'DESC')->limit(self::PER_HALAMAN, ($hal - 1) * self::PER_HALAMAN)->get()->getResultArray();

        $ids = array_column($baris, 'actor_id');
        foreach ($baris as $b) {
            if ($b['target_tipe'] === 'user' && $b['target_id'] !== null) {
                $ids[] = $b['target_id'];
            }
        }

        return $this->halaman('audit', [
            'judul'  => lang('Kode.nav.audit'),
            'baris'  => $baris,
            'orang'  => UserDirectory::byIds($ids),
            'saring' => $s,
            'hal'    => $hal,
            'akhir'  => $akhir,
            'jumlah' => $jumlah,
        ]);
    }
}
