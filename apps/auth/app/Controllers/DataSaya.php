<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\Audit;
use App\Libraries\DataSaya as Susun;
use App\Libraries\RedisConn;
use CodeIgniter\HTTP\ResponseInterface;
use Lokandita\Auth\Current;
use Lokandita\Auth\Ekspor;

/**
 * Ekspor data sendiri (SPEC.md §16, KONTRAK.md §14). Tidak ada id di alamatnya:
 * yang diekspor selalu pengguna sesi ini, siapa pun dia.
 */
final class DataSaya extends BaseController
{
    public function index(): ResponseInterface
    {
        return $this->halaman('Lokandita\Auth\Views\data_saya', [
            'judul' => lang('Lokandita.dataSaya.judul'),
            'butir' => lang('Auth.dataSaya'),
        ])->setHeader('Cache-Control', 'no-store');
    }

    public function unduh(): ResponseInterface
    {
        $id = Current::identity()->userId;
        if (! Ekspor::bolehSekarang(static fn (): \Redis => RedisConn::get(), $id)) {
            return Ekspor::terlaluCepat($this->response);
        }
        [$isi, $terpotong] = Susun::susun($id);
        Audit::log('data.diekspor', $id, $id, ['terpotong' => implode(',', $terpotong)]);

        return Ekspor::berkas($this->response, $id, $isi, $terpotong);
    }
}
