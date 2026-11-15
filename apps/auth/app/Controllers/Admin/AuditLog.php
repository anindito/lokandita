<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Halaman audit (SPEC.md §4.7). Hanya membaca; tidak ada rute yang mengubah atau menghapus baris audit.
 */
final class AuditLog extends BaseController
{
    private const PER_HALAMAN = 100;

    public function index(): ResponseInterface
    {
        $s = [
            'aksi'   => trim((string) $this->request->getGet('aksi')),
            'aktor'  => trim((string) $this->request->getGet('aktor')),
            'target' => trim((string) $this->request->getGet('target')),
            'app'    => trim((string) $this->request->getGet('app')),
            'dari'   => trim((string) $this->request->getGet('dari')),
            'sampai' => trim((string) $this->request->getGet('sampai')),
        ];
        $q = function () use ($s) {
            $b = db_connect()->table('audit_log a')
                ->join('users ak', 'ak.id = a.actor_id', 'left')->join('users tg', 'tg.id = a.target_id', 'left');
            if ($s['aksi'] !== '') {
                $b->like('a.action', $s['aksi'], 'after');
            }
            if ($s['aktor'] !== '') {
                $b->where('ak.nomor_induk', $s['aktor']);
            }
            if ($s['target'] !== '') {
                $b->where('tg.nomor_induk', $s['target']);
            }
            if ($s['app'] !== '') {
                $b->where('a.app', $s['app']);
            }
            // Tanggal diisi dalam waktu Jakarta (UTC+7); basis data menyimpan UTC.
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $s['dari']) === 1) {
                $b->where('a.created_at >=', gmdate('Y-m-d H:i:s', (int) strtotime($s['dari'] . ' 00:00:00 +07:00')));
            }
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $s['sampai']) === 1) {
                $b->where('a.created_at <', gmdate('Y-m-d H:i:s', (int) strtotime($s['sampai'] . ' 00:00:00 +07:00') + 86400));
            }

            return $b;
        };
        $jumlah = $q()->countAllResults();
        $akhir  = max(1, (int) ceil($jumlah / self::PER_HALAMAN));
        $hal    = min($akhir, max(1, (int) $this->request->getGet('hal')));
        $baris  = $q()->select('a.*, ak.nomor_induk AS aktor_nomor, ak.nama AS aktor_nama, tg.nomor_induk AS target_nomor, tg.nama AS target_nama')
            ->orderBy('a.id', 'DESC')->limit(self::PER_HALAMAN, ($hal - 1) * self::PER_HALAMAN)->get()->getResultArray();

        return $this->halaman('admin/audit', [
            'judul' => lang('Admin.audit.judul'), 'baris' => $baris, 'saring' => $s, 'hal' => $hal, 'akhir' => $akhir, 'jumlah' => $jumlah, 'pesan' => [],
        ]);
    }
}
