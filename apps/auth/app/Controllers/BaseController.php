<?php

declare(strict_types=1);

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Lokandita\Auth\Current;
use Lokandita\Auth\Locale;
use Psr\Log\LoggerInterface;

abstract class BaseController extends Controller
{
    protected $helpers = ['form'];

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        // Halaman tanpa sesi: bahasa dari cookie. Halaman bersesi sudah diatur oleh filter.
        if (Current::identity() === null) {
            Locale::apply($request);
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function halaman(string $view, array $data = [], int $status = 200): ResponseInterface
    {
        $data['namaAplikasi'] = \Lokandita\Auth\Aplikasi::nama();

        return $this->response->setStatusCode($status)->setBody(view($view, $data));
    }

    /**
     * @return list<array{jenis: string, teks: string}>
     */
    protected function galat(string $teks): array
    {
        return [['jenis' => 'galat', 'teks' => $teks]];
    }
}
