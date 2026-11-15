<?php

declare(strict_types=1);

namespace Lokandita\Auth\Testing;

/**
 * Klien HTTP kecil untuk tes: permintaan sungguhan ke kontainer lain, tanpa mock.
 * Menyimpan cookie seperti peramban (tanpa memeriksa Domain/Secure, karena tes
 * berbicara HTTP biasa ke nama kontainer) dan tidak pernah mengikuti redirect.
 */
final class Http
{
    /** @var array<string, string> */
    public array $cookies = [];

    public int $status = 0;

    public string $body = '';

    /** @var array<string, list<string>> nama header huruf kecil => nilai-nilainya */
    public array $headers = [];

    public function __construct(private readonly string $baseUrl)
    {
    }

    /**
     * @param array<string, string> $headers
     */
    public function get(string $path, array $headers = []): self
    {
        return $this->send('GET', $path, null, $headers);
    }

    /**
     * @param array<string, mixed> $form
     * @param array<string, string> $headers
     */
    public function post(string $path, array $form = [], array $headers = []): self
    {
        return $this->send('POST', $path, http_build_query($form), $headers + ['Content-Type' => 'application/x-www-form-urlencoded']);
    }

    /**
     * @param array<string, string> $headers
     */
    public function postJson(string $path, mixed $data, array $headers = []): self
    {
        return $this->send('POST', $path, json_encode($data, JSON_THROW_ON_ERROR), $headers + ['Content-Type' => 'application/json']);
    }

    /**
     * @param array<string, string> $headers
     */
    public function send(string $method, string $path, ?string $body, array $headers = []): self
    {
        $this->headers = [];
        $lines         = [];
        foreach ($headers as $name => $value) {
            $lines[] = "{$name}: {$value}";
        }
        if ($this->cookies !== []) {
            $pairs = [];
            foreach ($this->cookies as $name => $value) {
                $pairs[] = "{$name}={$value}";
            }
            $lines[] = 'Cookie: ' . implode('; ', $pairs);
        }

        $ch = curl_init(rtrim($this->baseUrl, '/') . $path);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_HTTPHEADER     => $lines,
            CURLOPT_HEADERFUNCTION => function ($ch, string $line): int {
                if (str_contains($line, ':')) {
                    [$name, $value] = explode(':', $line, 2);
                    $name           = strtolower(trim($name));
                    $value          = trim($value);
                    $this->headers[$name][] = $value;
                    if ($name === 'set-cookie') {
                        $this->storeCookie($value);
                    }
                }

                return strlen($line);
            },
        ]);
        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }
        $result = curl_exec($ch);
        if ($result === false) {
            throw new \RuntimeException("{$method} {$this->baseUrl}{$path} gagal: " . curl_error($ch));
        }
        $this->status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $this->body   = (string) $result;
        curl_close($ch);

        return $this;
    }

    public function header(string $name): ?string
    {
        return $this->headers[strtolower($name)][0] ?? null;
    }

    /**
     * @return array<string, mixed>
     */
    public function json(): array
    {
        return json_decode($this->body, true, 32, JSON_THROW_ON_ERROR);
    }

    /**
     * Baris Set-Cookie mentah untuk cookie bernama $name, atau null.
     */
    public function setCookieLine(string $name): ?string
    {
        foreach ($this->headers['set-cookie'] ?? [] as $line) {
            if (str_starts_with($line, $name . '=')) {
                return $line;
            }
        }

        return null;
    }

    /**
     * Nilai sebuah <input name="..."> pada halaman terakhir (untuk token CSRF).
     */
    public function input(string $name): ?string
    {
        $q = preg_quote($name, '/');
        if (preg_match('/<input[^>]*name="' . $q . '"[^>]*value="([^"]*)"/', $this->body, $m) === 1
            || preg_match('/<input[^>]*value="([^"]*)"[^>]*name="' . $q . '"/', $this->body, $m) === 1) {
            return html_entity_decode($m[1], ENT_QUOTES);
        }

        return null;
    }

    private function storeCookie(string $line): void
    {
        $parts          = explode(';', $line);
        [$name, $value] = array_pad(explode('=', trim($parts[0]), 2), 2, '');
        $expired        = $value === '' || $value === 'deleted';
        foreach (array_slice($parts, 1) as $attr) {
            $attr = strtolower(trim($attr));
            if (str_starts_with($attr, 'max-age=') && (int) substr($attr, 8) <= 0) {
                $expired = true;
            }
            if (str_starts_with($attr, 'expires=') && strtotime(substr($attr, 8)) < time()) {
                $expired = true;
            }
        }
        if ($expired) {
            unset($this->cookies[$name]);
        } else {
            $this->cookies[$name] = $value;
        }
    }
}
