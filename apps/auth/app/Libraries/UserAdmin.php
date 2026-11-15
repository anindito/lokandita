<?php

declare(strict_types=1);

namespace App\Libraries;

/**
 * Pengelolaan pengguna oleh superadmin (SPEC.md §4.1). Semua perubahan lewat
 * kelas ini supaya aturan dan pencatatan audit ada di satu tempat.
 *
 * Tidak ada fungsi hapus: akun hanya bisa dinonaktifkan (nilai di aplikasi lain merujuk ID pengguna).
 */
final class UserAdmin
{
    public const APPS = ['portal', 'lokakode', 'lokaujian', 'lokasiber'];

    public const PERAN = ['superadmin', 'dosen', 'mahasiswa'];

    public const STATUS = ['active', 'suspended', 'disabled'];

    public const POLA_NOMOR = '/^[A-Za-z0-9._-]{3,40}$/';

    /**
     * Memeriksa isian satu pengguna. Mengembalikan kolom => kunci bahasa `Admin.galat.*`.
     *
     * @param array<string, mixed>      $in       nomor_induk, nama, email, global_role, apps (list)
     * @param array<string, mixed>|null $existing baris yang sedang disunting, atau null untuk akun baru
     *
     * @return array<string, string>
     */
    public static function validate(array $in, ?array $existing, bool $allowSuperadmin): array
    {
        $galat = [];
        $nomor = (string) ($in['nomor_induk'] ?? '');
        if (preg_match(self::POLA_NOMOR, $nomor) !== 1) {
            $galat['nomor_induk'] = 'nomorInduk';
        } else {
            $ada = Users::findByNomorInduk($nomor);
            if ($ada !== null && $ada['id'] !== ($existing['id'] ?? null)) {
                $galat['nomor_induk'] = 'nomorIndukAda';
            }
        }
        $nama = trim((string) ($in['nama'] ?? ''));
        if ($nama === '' || mb_strlen($nama) > 190) {
            $galat['nama'] = 'nama';
        }
        $email = trim((string) ($in['email'] ?? ''));
        if ($email !== '') {
            if (filter_var($email, FILTER_VALIDATE_EMAIL) === false || strlen($email) > 190) {
                $galat['email'] = 'email';
            } else {
                $ada = db_connect()->table('users')->where('email', $email)->get()->getRowArray();
                if ($ada !== null && $ada['id'] !== ($existing['id'] ?? null)) {
                    $galat['email'] = 'emailAda';
                }
            }
        }
        $peran = (string) ($in['global_role'] ?? '');
        if (! in_array($peran, self::PERAN, true)) {
            $galat['global_role'] = 'peran';
        } elseif ($peran === 'superadmin' && ! $allowSuperadmin) {
            $galat['global_role'] = 'superadminLewatImpor';
        }
        foreach ((array) ($in['apps'] ?? []) as $app) {
            if (! in_array($app, self::APPS, true)) {
                $galat['apps'] = 'apps';
            }
        }

        return $galat;
    }

    /**
     * Membuat akun dengan hash kata sandi yang sudah jadi. Selalu wajib ganti kata sandi.
     *
     * @param array<string, mixed> $in isian yang sudah lolos validate()
     */
    public static function create(array $in, string $passwordHash, ?string $actorId, string $via): string
    {
        $id    = Uuid::v4();
        $email = trim((string) ($in['email'] ?? ''));
        $db    = db_connect();
        $db->transStart();
        $db->table('users')->insert([
            'id'                   => $id,
            'nomor_induk'          => $in['nomor_induk'],
            'email'                => $email === '' ? null : $email,
            'nama'                 => trim((string) $in['nama']),
            'password_hash'        => $passwordHash,
            'must_change_password' => 1,
            'global_role'          => $in['global_role'],
            'status'               => 'active',
            // Keluar-otomatis wajib menyala untuk mahasiswa (SPEC.md §4.4).
            'idle_logout'          => $in['global_role'] === 'mahasiswa' ? 1 : 0,
            'created_by'           => $actorId,
            'created_at'           => Clock::now(),
            'updated_at'           => Clock::now(),
        ]);
        $db->table('password_history')->insert(['user_id' => $id, 'password_hash' => $passwordHash, 'created_at' => Clock::now()]);
        foreach (array_unique((array) ($in['apps'] ?? [])) as $app) {
            $db->table('app_access')->insert(['user_id' => $id, 'app' => $app, 'enabled' => 1, 'granted_by' => $actorId, 'granted_at' => Clock::now()]);
        }
        $db->transComplete();
        Audit::log('user.created', $actorId, $id, ['via' => $via, 'global_role' => $in['global_role'], 'nomor_induk' => $in['nomor_induk'], 'apps' => implode(',', (array) ($in['apps'] ?? []))]);

        return $id;
    }

    /**
     * Mengubah nama, email dan peran. Mengembalikan kunci galat, atau null bila berhasil.
     *
     * @param array<string, mixed> $user baris sekarang
     * @param array<string, mixed> $in   isian yang sudah lolos validate()
     */
    public static function updateProfile(array $user, array $in, string $actorId): ?string
    {
        $peranBaru = (string) $in['global_role'];
        if ($peranBaru !== $user['global_role']) {
            if ($user['id'] === $actorId) {
                return 'diriSendiri';
            }
            if ($user['global_role'] === 'superadmin' && self::isLastActiveSuperadmin($user)) {
                return 'superadminTerakhir';
            }
        }
        $email = trim((string) ($in['email'] ?? ''));
        $baru  = [
            'nama'        => trim((string) $in['nama']),
            'email'       => $email === '' ? null : $email,
            'global_role' => $peranBaru,
        ];
        if ($peranBaru === 'mahasiswa') {
            $baru['idle_logout'] = 1;
        }
        $ubah = [];
        foreach ($baru as $k => $v) {
            if ((string) $v !== (string) $user[$k]) {
                $ubah[$k] = $v;
            }
        }
        if ($ubah === []) {
            return null;
        }
        db_connect()->table('users')->where('id', $user['id'])->update($ubah + ['updated_at' => Clock::now()]);
        Audit::log('user.updated', $actorId, $user['id'], ['changed' => implode(',', array_keys($ubah))] + (isset($ubah['global_role']) ? ['role_from' => $user['global_role'], 'role_to' => $peranBaru] : []));
        if (isset($ubah['global_role'])) {
            // Peran berubah: sesi lama memuat hak lama, jadi diakhiri.
            Sessions::revokeAllFor((string) $user['id']);
        }

        return null;
    }

    /**
     * @param array<string, mixed> $user
     */
    public static function setStatus(array $user, string $status, string $actorId): ?string
    {
        if (! in_array($status, self::STATUS, true)) {
            return 'status';
        }
        if ($status === $user['status']) {
            return null;
        }
        if ($status !== 'active') {
            if ($user['id'] === $actorId) {
                return 'diriSendiri';
            }
            if ($user['global_role'] === 'superadmin' && self::isLastActiveSuperadmin($user)) {
                return 'superadminTerakhir';
            }
        }
        db_connect()->table('users')->where('id', $user['id'])->update(['status' => $status, 'updated_at' => Clock::now()]);
        $dicabut = $status === 'active' ? 0 : Sessions::revokeAllFor((string) $user['id']);
        Audit::log('user.status_changed', $actorId, $user['id'], ['from' => $user['status'], 'to' => $status, 'sessions_revoked' => $dicabut]);

        return null;
    }

    /**
     * Benar bila $user adalah satu-satunya superadmin yang masih aktif.
     *
     * @param array<string, mixed> $user
     */
    public static function isLastActiveSuperadmin(array $user): bool
    {
        if ($user['global_role'] !== 'superadmin' || $user['status'] !== 'active') {
            return false;
        }

        return db_connect()->table('users')->where('global_role', 'superadmin')->where('status', 'active')->countAllResults() <= 1;
    }

    public static function setAccess(string $userId, string $app, bool $enabled, string $actorId): bool
    {
        if (! in_array($app, self::APPS, true)) {
            return false;
        }
        $db  = db_connect();
        $ada = $db->table('app_access')->where('user_id', $userId)->where('app', $app)->get()->getRowArray();
        if ($ada === null && ! $enabled) {
            return false;
        }
        if ($ada !== null && (bool) $ada['enabled'] === $enabled) {
            return false;
        }
        if ($ada === null) {
            $db->table('app_access')->insert(['user_id' => $userId, 'app' => $app, 'enabled' => 1, 'granted_by' => $actorId, 'granted_at' => Clock::now()]);
        } else {
            $db->table('app_access')->where('user_id', $userId)->where('app', $app)
                ->update(['enabled' => $enabled ? 1 : 0, 'granted_by' => $actorId, 'granted_at' => Clock::now()]);
        }
        Audit::log($enabled ? 'access.granted' : 'access.revoked', $actorId, $userId, ['app' => $app]);

        return true;
    }

    /**
     * @return list<string> aplikasi yang aksesnya menyala
     */
    public static function apps(string $userId): array
    {
        return array_column(
            db_connect()->table('app_access')->select('app')->where('user_id', $userId)->where('enabled', 1)->orderBy('app')->get()->getResultArray(),
            'app',
        );
    }

    /**
     * Reset kata sandi: kata sandi sementara acak, wajib ganti, semua sesi dicabut.
     * Mengembalikan kata sandi sementara (hanya untuk ditampilkan/diunduh sekali).
     *
     * @param array<string, mixed> $user
     */
    public static function resetPassword(array $user, string $actorId): string
    {
        $sandi = Passwords::random();
        Passwords::setHash((string) $user['id'], Passwords::hashTemporary($sandi));
        $dicabut = Sessions::revokeAllFor((string) $user['id']);
        Audit::log('password.reset', $actorId, $user['id'], ['sessions_revoked' => $dicabut]);

        return $sandi;
    }

    public static function setPasteAllowed(array $user, bool $allowed, string $actorId): void
    {
        if ((bool) $user['paste_allowed'] === $allowed) {
            return;
        }
        db_connect()->table('users')->where('id', $user['id'])->update(['paste_allowed' => $allowed ? 1 : 0, 'updated_at' => Clock::now()]);
        Audit::log('user.paste_allowed_changed', $actorId, $user['id'], ['to' => $allowed]);
    }

    /**
     * Menerapkan saringan daftar pengguna pada sebuah query.
     *
     * @param array{q?: string, peran?: string, status?: string} $saring
     */
    public static function filter(\CodeIgniter\Database\BaseBuilder $q, array $saring): \CodeIgniter\Database\BaseBuilder
    {
        $cari = trim((string) ($saring['q'] ?? ''));
        if ($cari !== '') {
            $q->groupStart()->like('nomor_induk', $cari, 'after')->orLike('nama', $cari)->orLike('email', $cari)->groupEnd();
        }
        if (in_array($saring['peran'] ?? '', self::PERAN, true)) {
            $q->where('global_role', $saring['peran']);
        }
        $status = (string) ($saring['status'] ?? '');
        if (in_array($status, self::STATUS, true)) {
            $q->where('status', $status);
        } elseif ($status !== 'semua') {
            // Bawaan: akun yang dinonaktifkan tidak ditampilkan (keputusan pemilik 2026-11-15).
            $q->where('status !=', 'disabled');
        }

        return $q;
    }
}
