<?php

declare(strict_types=1);

namespace App\Filters;

use App\Libraries\Clock;
use App\Libraries\Sessions;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Lokandita\Auth\Current;
use Lokandita\Auth\Locale;

/**
 * Halaman LokaAuth yang butuh sesi.
 *
 * Argumen opsional: peran global yang diwajibkan, mis. 'sesi:superadmin'.
 * Aplikasi klien memakai filter di packages/lokandita-auth, bukan yang ini.
 */
final class RequireSession implements FilterInterface
{
    // Halaman yang tetap boleh dibuka selama kata sandi wajib diganti.
    private const BOLEH_SAAT_WAJIB_GANTI = ['ganti-password', 'keluar'];

    public function before(RequestInterface $request, $arguments = null)
    {
        $token     = Sessions::tokenFrom($request);
        $identitas = Sessions::identity($token);
        $kini      = self::currentUrl($request);

        if ($identitas === null) {
            Locale::apply($request);

            return redirect()->to('/login?next=' . rawurlencode($kini));
        }

        // Sesi bergeser: diperpanjang paling sering sekali semenit, cookie ikut disegarkan.
        if (time() - Clock::toUnix($identitas->lastSeenAt) > 60 && Sessions::touch($token) !== null) {
            Sessions::setCookie(service('response'), $token);
            $identitas = Sessions::identity($token) ?? $identitas;
        }

        Current::set($identitas);
        Locale::apply($request, $identitas->locale);

        $jalur = trim($request->getUri()->getPath(), '/');
        if ($identitas->mustChangePassword && ! in_array($jalur, self::BOLEH_SAAT_WAJIB_GANTI, true)) {
            return redirect()->to('/ganti-password?next=' . rawurlencode($kini));
        }

        if ($arguments !== null && $arguments !== [] && ! in_array($identitas->globalRole, $arguments, true)) {
            \App\Libraries\Audit::log('access.denied', $identitas->userId, null, ['path' => '/' . $jalur, 'need' => implode(',', $arguments)]);

            return service('response')->setStatusCode(403)
                ->setBody(view('galat', ['judul' => lang('Lokandita.tidakBerhak'), 'teks' => lang('Lokandita.tidakBerhak')]));
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Halaman bersesi tidak boleh tersimpan di cache peramban (komputer lab dipakai bergantian).
        $response->setHeader('Cache-Control', 'no-store');

        return $response;
    }

    /**
     * URL mutlak permintaan ini, dibangun dari APP_URL — header Host dari klien tidak dipercaya.
     */
    public static function currentUrl(RequestInterface $request): string
    {
        $uri   = $request->getUri();
        $query = $uri->getQuery();

        return rtrim((string) getenv('APP_URL'), '/') . '/' . ltrim($uri->getPath(), '/') . ($query !== '' ? '?' . $query : '');
    }
}
