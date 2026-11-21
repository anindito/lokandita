<?php

declare(strict_types=1);

namespace Lokandita\Auth\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Lokandita\Auth\Aplikasi;
use Lokandita\Auth\AuthClient;
use Lokandita\Auth\Current;
use Lokandita\Auth\Locale;
use Lokandita\Auth\Log;
use Lokandita\Auth\SessionReader;

/**
 * Cara aplikasi klien mengenali pengguna — KONTRAK.md §4, lima langkah, di satu tempat.
 *
 * Argumen opsional: peran global yang diwajibkan, mis. 'masuk:superadmin,dosen'.
 */
final class RequireLogin implements FilterInterface
{
    // Sesi diperpanjang lewat LokaAuth bila terakhir terlihat lebih dari 5 menit lalu.
    private const SENTUH_SETELAH = 300;

    public function before(RequestInterface $request, $arguments = null)
    {
        $kini  = Aplikasi::urlSekarang($request);
        $login = (string) getenv('AUTH_LOGIN_URL') . '?next=' . rawurlencode($kini);

        // 1. Cookie.
        $sid = (string) ($_COOKIE[SessionReader::COOKIE] ?? '');
        if ($sid === '') {
            Locale::apply($request);

            return redirect()->to($login);
        }

        // 2. Sesi masih hidup menurut LokaAuth?
        $identitas = SessionReader::identity($sid);
        if ($identitas === null) {
            Locale::apply($request);

            return redirect()->to($login);
        }
        Current::set($identitas);
        Locale::apply($request, $identitas->locale);

        // 3. Wajib ganti kata sandi dulu.
        if ($identitas->mustChangePassword) {
            return redirect()->to(rtrim((string) getenv('AUTH_PUBLIC_URL'), '/') . '/ganti-password?next=' . rawurlencode($kini));
        }

        // 4. Akses ke aplikasi ini. Portal dilewati (KONTRAK.md §4): setiap sesi yang hidup
        //    boleh membukanya, dan Portal hanya menampilkan aplikasi yang diizinkan app_access.
        if (Aplikasi::kode() !== 'portal' && ! SessionReader::hasAccess($identitas->userId, Aplikasi::kode())) {
            Log::event('access.denied', ['user' => $identitas->userId, 'reason' => 'no_app_access', 'path' => $request->getUri()->getPath()]);

            return self::tolak(lang('Lokandita.tidakAdaAkses'));
        }
        if ($arguments !== null && $arguments !== [] && ! in_array($identitas->globalRole, $arguments, true)) {
            Log::event('access.denied', ['user' => $identitas->userId, 'reason' => 'role', 'path' => $request->getUri()->getPath()]);

            return self::tolak(lang('Lokandita.tidakBerhak'));
        }

        // 5. Perpanjang sesi. Kegagalan di sini dicatat dan TIDAK menggagalkan permintaan.
        if (time() - (int) strtotime($identitas->lastSeenAt . ' UTC') > self::SENTUH_SETELAH) {
            if ((new AuthClient())->touch($sid) !== null) {
                SessionReader::refreshCookie(service('response'), $sid);
                Current::set(SessionReader::identity($sid) ?? $identitas);
            }
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Halaman bersesi tidak boleh tersimpan di cache peramban (komputer lab dipakai bergantian).
        $response->setHeader('Cache-Control', 'no-store');

        return $response;
    }

    private static function tolak(string $teks): ResponseInterface
    {
        return service('response')->setStatusCode(403)->setHeader('Cache-Control', 'no-store')
            ->setBody(view('Lokandita\Auth\Views\pesan', ['judul' => $teks, 'teks' => $teks]));
    }
}
