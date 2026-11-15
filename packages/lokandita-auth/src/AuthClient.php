<?php

declare(strict_types=1);

namespace Lokandita\Auth;

/**
 * Klien API internal LokaAuth (KONTRAK.md §5). Satu-satunya tempat aplikasi
 * klien berbicara dengan LokaAuth.
 *
 * Tidak ada kegagalan yang diam-diam: setiap jawaban selain 2xx, dan setiap
 * kegagalan jaringan, ditulis ke log dengan alasannya (tanpa token).
 */
final class AuthClient
{
    public function __construct(
        private readonly ?string $baseUrl = null,
        private readonly ?string $token = null,
        private readonly int $timeout = 3,
    ) {
    }

    /**
     * Memperpanjang sesi. Mengembalikan expires_at (ISO 8601 UTC), atau null bila gagal.
     */
    public function touch(string $sid): ?string
    {
        $r = $this->call('POST', '/internal/session/touch', ['sid' => $sid]);

        return $r['status'] === 200 ? (string) ($r['json']['expires_at'] ?? '') : null;
    }

    /**
     * Mencabut sesi di LokaAuth, sehingga berakhir di semua aplikasi. Benar hanya bila LokaAuth menjawab ok.
     */
    public function revoke(string $sid): bool
    {
        $r = $this->call('POST', '/internal/session/revoke', ['sid' => $sid]);

        return $r['status'] === 200 && ($r['json']['ok'] ?? false) === true;
    }

    /**
     * @param array{locale?: string, idle_logout?: bool} $prefs
     *
     * @return array{status: int, json: array<string, mixed>|null}
     */
    public function prefs(string $sid, array $prefs): array
    {
        return $this->call('POST', '/internal/session/prefs', ['sid' => $sid] + $prefs);
    }

    /**
     * @return array<string, mixed>|null {id, nomor_induk, nama, global_role, status}
     */
    public function user(string $id): ?array
    {
        $r = $this->call('GET', '/internal/users/' . rawurlencode($id), null);

        return $r['status'] === 200 ? $r['json'] : null;
    }

    /**
     * @param list<string> $nomorInduk
     *
     * @return array{found: list<array<string, mixed>>, missing: list<string>}|null null bila LokaAuth tidak menjawab dengan benar
     */
    public function lookup(array $nomorInduk): ?array
    {
        $r = $this->call('POST', '/internal/users/lookup', ['nomor_induk' => array_values($nomorInduk)]);

        return $r['status'] === 200 ? $r['json'] : null;
    }

    /**
     * Mereset kata sandi seorang mahasiswa. Aplikasi pemanggil WAJIB sudah
     * memeriksa bahwa $requestedBy berhak atas $userId sebelum memanggil ini.
     *
     * @return array{status: int, json: array<string, mixed>|null}
     */
    public function passwordReset(string $requestedBy, string $userId): array
    {
        return $this->call('POST', '/internal/password-reset', ['requested_by' => $requestedBy, 'user_id' => $userId]);
    }

    /**
     * @param array<string, mixed>|null $body
     *
     * @return array{status: int, json: array<string, mixed>|null}  status 0 = tidak tersambung
     */
    private function call(string $method, string $path, ?array $body): array
    {
        $base  = rtrim($this->baseUrl ?? (string) getenv('AUTH_INTERNAL_URL'), '/');
        $token = $this->token ?? (string) getenv('AUTH_SERVICE_TOKEN');

        $ch = curl_init($base . $path);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => $this->timeout,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_HTTPHEADER     => ['X-Service-Token: ' . $token, 'Content-Type: application/json', 'Accept: application/json'],
        ]);
        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body, JSON_THROW_ON_ERROR));
        }
        $raw    = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $galat  = $raw === false ? curl_error($ch) : '';
        curl_close($ch);

        $json = is_string($raw) ? json_decode($raw, true) : null;
        if (! is_array($json)) {
            $json = null;
        }

        if ($status < 200 || $status >= 300) {
            Log::event('authclient.failed', [
                'path'   => $path,
                'status' => $status,
                'error'  => $galat !== '' ? $galat : (string) ($json['error'] ?? 'bukan_json'),
                'fields' => isset($json['fields']) && is_array($json['fields']) ? implode(',', array_keys($json['fields'])) : '',
            ]);
        }

        return ['status' => $status, 'json' => $json];
    }
}
