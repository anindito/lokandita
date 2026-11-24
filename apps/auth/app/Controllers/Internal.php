<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Filters\ServiceToken;
use App\Libraries\Audit;
use App\Libraries\Passwords;
use App\Libraries\Sessions;
use App\Libraries\Users;
use App\Libraries\Uuid;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;
use Lokandita\Auth\Locale;
use Lokandita\Auth\Log;

/**
 * API internal LokaAuth — KONTRAK.md §5. JSON masuk, JSON keluar.
 *
 * Setiap kegagalan punya kode galat sendiri: 401 token, 404 rute/obyek tidak
 * ada, 422 isian salah (dengan nama kolomnya).
 */
final class Internal extends Controller
{
    // Kolom pengguna yang boleh keluar lewat API ini. Tidak pernah hash kata sandi atau rahasia MFA.
    private const KOLOM_PENGGUNA = ['id', 'nomor_induk', 'nama', 'global_role', 'status'];

    public function touch(): ResponseInterface
    {
        [$body, $galat] = $this->body(['sid' => 'sid']);
        if ($galat !== null) {
            return $galat;
        }
        $expires = Sessions::touch($body['sid']);
        if ($expires === null) {
            return $this->gagal(404, 'session_not_found');
        }

        return $this->ok(['expires_at' => gmdate('Y-m-d\TH:i:s\Z', (int) strtotime($expires . ' UTC'))]);
    }

    public function revoke(): ResponseInterface
    {
        [$body, $galat] = $this->body(['sid' => 'sid']);
        if ($galat !== null) {
            return $galat;
        }
        // Mencabut sesi yang sudah mati atau tidak ada tetap dijawab ok: tujuannya tercapai.
        $userId = Sessions::revoke($body['sid']);
        Audit::log('logout', $userId, $userId, ['found' => $userId !== null], ServiceToken::app());

        return $this->ok(['ok' => true]);
    }

    public function prefs(): ResponseInterface
    {
        [$body, $galat] = $this->body(['sid' => 'sid', 'locale' => '?locale', 'idle_logout' => '?bool']);
        if ($galat !== null) {
            return $galat;
        }
        $identitas = Sessions::identity($body['sid']);
        if ($identitas === null) {
            return $this->gagal(404, 'session_not_found');
        }
        // Keluar-otomatis wajib menyala untuk mahasiswa (SPEC.md §4.4).
        if (($body['idle_logout'] ?? null) === false && $identitas->globalRole === 'mahasiswa') {
            return $this->gagal(422, 'validation_failed', ['idle_logout' => 'forced_on_for_mahasiswa']);
        }

        $ubah = [];
        if (isset($body['locale'])) {
            $ubah['locale'] = $body['locale'];
        }
        if (isset($body['idle_logout'])) {
            $ubah['idle_logout'] = $body['idle_logout'] ? 1 : 0;
        }
        if ($ubah !== []) {
            db_connect()->table('users')->where('id', $identitas->userId)->update($ubah + ['updated_at' => \App\Libraries\Clock::now()]);
            Audit::log('prefs.changed', $identitas->userId, $identitas->userId, $ubah, ServiceToken::app());
        }
        $user = Users::find($identitas->userId);

        return $this->ok(['locale' => $user['locale'], 'idle_logout' => (bool) $user['idle_logout']]);
    }

    public function user(string $id): ResponseInterface
    {
        if (! Uuid::isValid($id)) {
            return $this->gagal(422, 'validation_failed', ['id' => 'must_be_uuid']);
        }
        $user = Users::find($id);
        if ($user === null) {
            return $this->gagal(404, 'user_not_found');
        }

        return $this->ok(array_intersect_key($user, array_flip(self::KOLOM_PENGGUNA)));
    }

    public function lookup(): ResponseInterface
    {
        [$body, $galat] = $this->body(['nomor_induk' => 'daftar']);
        if ($galat !== null) {
            return $galat;
        }
        $diminta = array_values(array_unique($body['nomor_induk']));
        $rows    = $diminta === [] ? [] : db_connect()->table('users')->select('id, nomor_induk, nama, global_role')
            ->whereIn('nomor_induk', $diminta)->get()->getResultArray();
        $ada     = array_map('strtolower', array_column($rows, 'nomor_induk'));
        $hilang  = array_values(array_filter($diminta, static fn ($n) => ! in_array(strtolower($n), $ada, true)));

        return $this->ok(['found' => $rows, 'missing' => $hilang]);
    }

    public function passwordReset(): ResponseInterface
    {
        // LokaUjian tidak mereset kata sandi (KONTRAK.md §5): itu tetap di LokaAuth dan LokaKode.
        // Tokennya sah untuk sesi, tetapi tidak untuk rute ini — ditolak dan dicatat, tidak diam-diam.
        if (ServiceToken::app() === 'lokaujian') {
            Audit::log('internal.denied', null, null, ['reason' => 'app_not_allowed', 'path' => '/internal/password-reset'], ServiceToken::app());

            return $this->gagal(403, 'app_not_allowed');
        }
        [$body, $galat] = $this->body(['requested_by' => 'uuid', 'user_id' => 'uuid']);
        if ($galat !== null) {
            return $galat;
        }
        $peminta = Users::find($body['requested_by']);
        $sasaran = Users::find($body['user_id']);
        if ($peminta === null || $peminta['status'] !== 'active') {
            return $this->gagal(422, 'validation_failed', ['requested_by' => 'unknown_or_inactive_user']);
        }
        if ($sasaran === null) {
            return $this->gagal(404, 'user_not_found');
        }
        // Siapa boleh mereset siapa diputuskan aplikasi pemanggil; tetapi lewat API ini
        // hanya akun mahasiswa yang bisa direset. Akun lain direset superadmin di LokaAuth.
        if ($sasaran['global_role'] !== 'mahasiswa') {
            Audit::log('internal.denied', $peminta['id'], $sasaran['id'], ['reason' => 'reset_target_not_mahasiswa', 'path' => '/internal/password-reset'], ServiceToken::app());

            return $this->gagal(403, 'target_not_allowed');
        }

        $sandi = Passwords::random();
        Passwords::set((string) $sasaran['id'], $sandi, true);
        $dicabut = Sessions::revokeAllFor((string) $sasaran['id']);
        Audit::log('password.reset', $peminta['id'], $sasaran['id'], ['sessions_revoked' => $dicabut], ServiceToken::app());

        return $this->ok(['temp_password' => $sandi]);
    }

    public function unknown(): ResponseInterface
    {
        Log::event('internal.unknown_route', ['via' => ServiceToken::app(), 'path' => '/' . ltrim($this->request->getUri()->getPath(), '/')]);

        return $this->gagal(404, 'unknown_internal_route');
    }

    /**
     * Membaca dan memeriksa badan JSON.
     *
     * Aturan: 'sid', 'uuid', 'daftar' (list nomor induk), dan dengan awalan '?' berarti boleh tidak ada:
     * '?locale', '?bool'.
     *
     * @param array<string, string> $aturan
     *
     * @return array{0: array<string, mixed>, 1: ResponseInterface|null}
     */
    private function body(array $aturan): array
    {
        try {
            $data = json_decode((string) $this->request->getBody(), true, 8, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return [[], $this->gagal(422, 'validation_failed', ['body' => 'must_be_json_object'])];
        }
        if (! is_array($data) || array_is_list($data)) {
            return [[], $this->gagal(422, 'validation_failed', ['body' => 'must_be_json_object'])];
        }

        $galat = [];
        foreach ($aturan as $kolom => $jenis) {
            $opsional = str_starts_with($jenis, '?');
            $jenis    = ltrim($jenis, '?');
            if (! array_key_exists($kolom, $data)) {
                if (! $opsional) {
                    $galat[$kolom] = 'required';
                }

                continue;
            }
            $v = $data[$kolom];
            $salah = match ($jenis) {
                'sid'    => is_string($v) && Sessions::looksValid($v) ? null : 'must_be_session_cookie_value',
                'uuid'   => is_string($v) && Uuid::isValid($v) ? null : 'must_be_uuid',
                'locale' => in_array($v, Locale::DAFTAR, true) ? null : 'must_be_id_en_or_ar',
                'bool'   => is_bool($v) ? null : 'must_be_boolean',
                'daftar' => is_array($v) && array_is_list($v) && count($v) <= 1000
                    && array_filter($v, static fn ($n) => ! is_string($n) || $n === '' || strlen($n) > 40) === []
                    ? null : 'must_be_list_of_up_to_1000_strings',
                default  => 'unknown_rule',
            };
            if ($salah !== null) {
                $galat[$kolom] = $salah;
            }
        }
        if ($galat !== []) {
            return [[], $this->gagal(422, 'validation_failed', $galat)];
        }

        return [$data, null];
    }

    /**
     * @param array<string, mixed> $data
     */
    private function ok(array $data): ResponseInterface
    {
        return $this->response->setStatusCode(200)->setHeader('Cache-Control', 'no-store')->setJSON($data);
    }

    /**
     * @param array<string, string> $fields
     */
    private function gagal(int $status, string $error, array $fields = []): ResponseInterface
    {
        $isi = ['error' => $error];
        if ($fields !== []) {
            $isi['fields'] = $fields;
        }
        Log::event('internal.failed', ['via' => ServiceToken::app(), 'status' => $status, 'error' => $error,
            'path' => '/' . ltrim($this->request->getUri()->getPath(), '/'), 'fields' => implode(',', array_keys($fields))]);

        return $this->response->setStatusCode($status)->setHeader('Cache-Control', 'no-store')->setJSON($isi);
    }
}
