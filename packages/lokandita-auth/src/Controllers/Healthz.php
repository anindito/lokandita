<?php

declare(strict_types=1);

namespace Lokandita\Auth\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * GET /healthz — KONTRAK.md §11.
 *
 * Nama aplikasi dan commit ditanam ke image saat build oleh infra/deploy.sh,
 * bukan dibaca dari .env, sehingga jawaban ini selalu menggambarkan kode yang
 * benar-benar sedang berjalan.
 */
final class Healthz extends Controller
{
    public function index(): ResponseInterface
    {
        return $this->response
            ->setHeader('Cache-Control', 'no-store')
            ->setJSON([
                'app'    => (string) getenv('LOKANDITA_APP'),
                'commit' => (string) getenv('LOKANDITA_COMMIT'),
            ]);
    }
}
