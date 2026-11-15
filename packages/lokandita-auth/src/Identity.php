<?php

declare(strict_types=1);

namespace Lokandita\Auth;

/**
 * Siapa yang sedang masuk, sebagaimana tertulis di view `session_identity` (KONTRAK.md §3).
 */
final class Identity
{
    public function __construct(
        public readonly string $userId,
        public readonly string $nomorInduk,
        public readonly string $nama,
        public readonly string $globalRole,
        public readonly string $locale,
        public readonly bool $idleLogout,
        public readonly bool $pasteAllowed,
        public readonly bool $mustChangePassword,
        public readonly string $expiresAt,
        public readonly string $lastSeenAt,
    ) {
    }

    /**
     * @param array<string, mixed> $row satu baris session_identity
     */
    public static function fromRow(array $row): self
    {
        return new self(
            (string) $row['user_id'],
            (string) $row['nomor_induk'],
            (string) $row['nama'],
            (string) $row['global_role'],
            (string) $row['locale'],
            (bool) $row['idle_logout'],
            (bool) $row['paste_allowed'],
            (bool) $row['must_change_password'],
            (string) $row['expires_at'],
            (string) $row['last_seen_at'],
        );
    }

    /**
     * Inisial untuk tombol menu akun, paling banyak dua huruf.
     */
    public function inisial(): string
    {
        $kata = preg_split('/\s+/u', trim($this->nama)) ?: [];
        $huruf = '';
        foreach (array_slice($kata, 0, 2) as $k) {
            $huruf .= mb_strtoupper(mb_substr($k, 0, 1));
        }

        return $huruf !== '' ? $huruf : '?';
    }

    /**
     * Detik tersisa sampai sesi berakhir (waktu disimpan dalam UTC).
     */
    public function detikTersisa(): int
    {
        return max(0, (int) strtotime($this->expiresAt . ' UTC') - time());
    }
}
