<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Policy\Akses;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;
use Lokandita\Auth\Current;

/**
 * Dasar semua halaman bersesi LokaUjian. Setiap rute di sini berada di belakang filter
 * `masuk` (KONTRAK.md §4), jadi identitas selalu ada.
 */
abstract class BaseController extends Controller
{
    private ?Akses $akses = null;

    protected function akses(): Akses
    {
        if ($this->akses === null) {
            $identitas = Current::identity();
            if ($identitas === null) {
                throw new \LogicException('Rute ini tidak dipasangi filter masuk.');
            }
            $this->akses = new Akses($identitas);
        }

        return $this->akses;
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function halaman(string $view, array $data = [], int $status = 200): ResponseInterface
    {
        $data['akses'] = $this->akses();

        return $this->response->setStatusCode($status)->setBody(view($view, $data));
    }
}
