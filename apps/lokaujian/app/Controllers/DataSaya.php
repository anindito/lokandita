<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\Audit;
use App\Libraries\DataSaya as Susun;
use App\Libraries\Jadwal;
use CodeIgniter\HTTP\ResponseInterface;
use Lokandita\Auth\Ekspor;

/**
 * Ekspor data sendiri (SPEC-LOKAUJIAN.md §13, KONTRAK.md §14). Tidak ada id di alamatnya: yang
 * diekspor selalu pengguna sesi ini. Di luar SEB, seperti menu akun.
 */
final class DataSaya extends BaseController
{
    public function index(): ResponseInterface
    {
        return $this->halaman('Lokandita\Auth\Views\data_saya', [
            'judul' => lang('Lokandita.dataSaya.judul'),
            'butir' => lang('Hasil.dataSaya'),
        ]);
    }

    public function unduh(): ResponseInterface
    {
        $id = $this->akses()->identitas()->userId;
        if (! Ekspor::bolehSekarang(static fn (): \Redis => Jadwal::redis(), $id)) {
            return Ekspor::terlaluCepat($this->response);
        }
        [$isi, $terpotong] = Susun::susun($id);
        Audit::catat('data.diekspor', 'user', $id, ['terpotong' => $terpotong]);

        return Ekspor::berkas($this->response, $id, $isi, $terpotong);
    }
}
