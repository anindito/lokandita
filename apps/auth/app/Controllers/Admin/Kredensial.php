<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\Audit;
use App\Libraries\Brankas;
use App\Libraries\Tabular;
use CodeIgniter\HTTP\ResponseInterface;
use Lokandita\Auth\Current;

/**
 * Unduhan kata sandi sementara: hanya sekali, hanya oleh superadmin yang membuatnya (SPEC.md §4.1).
 */
final class Kredensial extends BaseController
{
    public function unduh(string $token): ResponseInterface
    {
        $aktor = Current::identity()->userId;
        $baris = Brankas::ambil('kredensial', $aktor, $token);
        if ($baris === null) {
            Audit::log('credentials.download_refused', $aktor);

            return $this->halaman('galat', ['judul' => lang('Admin.kredensial.tidakAdaJudul'), 'teks' => lang('Admin.kredensial.tidakAda')], 410);
        }
        Audit::log('credentials.downloaded', $aktor, null, ['rows' => count($baris)]);

        return Tabular::download(
            $this->response,
            $this->request->getGet('format') === 'xlsx' ? 'xlsx' : 'csv',
            'kata-sandi-sementara-' . gmdate('Ymd-His'),
            ['nomor_induk', 'nama', 'kata_sandi_sementara'],
            array_map(static fn ($b) => [$b['nomor_induk'], $b['nama'], $b['password']], $baris),
        );
    }
}
