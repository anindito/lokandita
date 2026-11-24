<?php

declare(strict_types=1);

namespace App\Filters;

use App\Libraries\Audit;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * API internal (KONTRAK.md §5): pemanggil dikenali dari X-Service-Token saja,
 * tidak pernah dari alamat IP atau subnet. Penolakan tidak pernah diam-diam.
 */
final class ServiceToken implements FilterInterface
{
    // Nama aplikasi => variabel lingkungan yang memuat tokennya.
    private const TOKEN = ['portal' => 'SERVICE_TOKEN_PORTAL', 'lokakode' => 'SERVICE_TOKEN_LOKAKODE', 'lokaujian' => 'SERVICE_TOKEN_LOKAUJIAN'];

    private static ?string $app = null;

    /**
     * Aplikasi yang memanggil permintaan ini (setelah filter lolos).
     */
    public static function app(): string
    {
        return (string) self::$app;
    }

    public function before(RequestInterface $request, $arguments = null)
    {
        $token = $request->getHeaderLine('X-Service-Token');

        if ($token !== '') {
            foreach (self::TOKEN as $app => $variabel) {
                $benar = (string) getenv($variabel);
                if ($benar !== '' && hash_equals($benar, $token)) {
                    self::$app = $app;

                    return null;
                }
            }
        }

        Audit::log('internal.denied', null, null, [
            'reason' => $token === '' ? 'missing_token' : 'wrong_token',
            'path'   => '/' . ltrim($request->getUri()->getPath(), '/'),
        ]);

        return service('response')->setStatusCode(401)->setHeader('Cache-Control', 'no-store')
            ->setJSON(['error' => 'invalid_service_token']);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return $response;
    }
}
