<?php

declare(strict_types=1);

namespace App\Libraries;

/**
 * Impor massal pengguna (SPEC.md §4.1). Kolom: nomor_induk, nama, email, global_role, apps.
 * `apps` berisi nama aplikasi dipisah `|`, `;`, koma atau spasi, mis. `portal|lokakode`.
 *
 * Superadmin tidak bisa dibuat lewat impor (keputusan pemilik 2026-11-15).
 */
final class Importer
{
    public const KOLOM = ['nomor_induk', 'nama', 'email', 'global_role', 'apps'];

    public const MAKS_BARIS = 2000;

    /**
     * Memilah baris menjadi: baru (akan dibuat), ada (sudah terdaftar, tidak diubah), salah (dengan alasan).
     *
     * @param list<array<string, string|int>> $baris hasil Tabular::read()
     *
     * @return array{baru: list<array<string, mixed>>, ada: list<array<string, mixed>>, salah: list<array<string, mixed>>}
     */
    public static function analyse(array $baris): array
    {
        $hasil       = ['baru' => [], 'ada' => [], 'salah' => []];
        $nomorTerbaca = [];
        $emailTerbaca = [];

        // Satu query untuk semua nomor induk dan email yang sudah ada.
        $semuaNomor = array_values(array_filter(array_map(static fn ($b) => (string) ($b['nomor_induk'] ?? ''), $baris)));
        $semuaEmail = array_values(array_filter(array_map(static fn ($b) => strtolower((string) ($b['email'] ?? '')), $baris)));
        $db         = db_connect();
        $nomorAda   = $semuaNomor === [] ? [] : array_map('strtolower', array_column($db->table('users')->select('nomor_induk')->whereIn('nomor_induk', $semuaNomor)->get()->getResultArray(), 'nomor_induk'));
        $emailAda   = $semuaEmail === [] ? [] : array_map('strtolower', array_column($db->table('users')->select('email')->whereIn('email', $semuaEmail)->get()->getResultArray(), 'email'));

        foreach ($baris as $b) {
            $data = [
                'baris'       => (int) $b['_baris'],
                'nomor_induk' => (string) ($b['nomor_induk'] ?? ''),
                'nama'        => (string) ($b['nama'] ?? ''),
                'email'       => (string) ($b['email'] ?? ''),
                'global_role' => strtolower((string) ($b['global_role'] ?? '')),
                'apps'        => array_values(array_filter(preg_split('/[|;,\s]+/', strtolower((string) ($b['apps'] ?? ''))) ?: [])),
            ];
            $alasan = [];
            $nomor  = strtolower($data['nomor_induk']);
            $email  = strtolower($data['email']);

            if (preg_match(UserAdmin::POLA_NOMOR, $data['nomor_induk']) !== 1) {
                $alasan[] = 'nomorInduk';
            } elseif (isset($nomorTerbaca[$nomor])) {
                $alasan[] = 'nomorIndukGanda';
            }
            if (trim($data['nama']) === '' || mb_strlen($data['nama']) > 190) {
                $alasan[] = 'nama';
            }
            if ($email !== '') {
                if (filter_var($data['email'], FILTER_VALIDATE_EMAIL) === false || strlen($data['email']) > 190) {
                    $alasan[] = 'email';
                } elseif (isset($emailTerbaca[$email])) {
                    $alasan[] = 'emailGanda';
                } elseif (in_array($email, $emailAda, true) && ! in_array($nomor, $nomorAda, true)) {
                    $alasan[] = 'emailAda';
                }
            }
            if ($data['global_role'] === 'superadmin') {
                $alasan[] = 'superadminLewatImpor';
            } elseif (! in_array($data['global_role'], ['dosen', 'mahasiswa'], true)) {
                $alasan[] = 'peran';
            }
            if (array_diff($data['apps'], UserAdmin::APPS) !== []) {
                $alasan[] = 'apps';
            }

            if ($alasan !== []) {
                $hasil['salah'][] = $data + ['alasan' => $alasan];

                continue;
            }
            $nomorTerbaca[$nomor] = true;
            if ($email !== '') {
                $emailTerbaca[$email] = true;
            }
            if (in_array($nomor, $nomorAda, true)) {
                $hasil['ada'][] = $data;
            } else {
                $hasil['baru'][] = $data;
            }
        }

        return $hasil;
    }

    /**
     * Membuat akun untuk baris 'baru'. Baris yang sementara itu sudah terdaftar dilewati.
     *
     * @param list<array<string, mixed>> $baru
     * @param string|null                $sandiBersama null = kata sandi acak per pengguna
     *
     * @return array{dibuat: list<array{nomor_induk: string, nama: string, password: string}>, dilewati: int}
     */
    public static function run(array $baru, ?string $sandiBersama, string $actorId): array
    {
        // Kata sandi bersama: satu hash berbiaya penuh dipakai untuk semua baris. Semua orang
        // memang tahu kata sandinya sama, dan masing-masing wajib menggantinya saat login pertama.
        $hashBersama = $sandiBersama === null ? null : Passwords::hash($sandiBersama);
        $dibuat      = [];
        $dilewati    = 0;

        foreach ($baru as $b) {
            if (UserAdmin::validate($b, null, false) !== []) {
                $dilewati++;

                continue;
            }
            $sandi = $sandiBersama ?? Passwords::random();
            UserAdmin::create($b, $hashBersama ?? Passwords::hashTemporary($sandi), $actorId, 'import');
            $dibuat[] = ['nomor_induk' => (string) $b['nomor_induk'], 'nama' => (string) $b['nama'], 'password' => $sandi];
        }

        return ['dibuat' => $dibuat, 'dilewati' => $dilewati];
    }
}
