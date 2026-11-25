<?php

declare(strict_types=1);

namespace App\Commands;

use App\Policy\Akses;
use Lokandita\Auth\Current;
use Lokandita\Auth\Identity;
use Lokandita\Auth\UserDirectory;

/**
 * Pelaku sebuah perintah spark: orang yang disebut --oleh. Ia dipasang sebagai identitas yang
 * sedang berlaku, sehingga hak aksesnya (App\Policy\Akses) dan baris auditnya sama dengan bila ia
 * melakukannya dari web. Tidak ada sesi yang dibuat.
 */
final class Pelaku
{
    /**
     * @return Akses|string hak akses pelaku, atau kalimat galat
     */
    public static function pasang(string $nomorInduk): Akses|string
    {
        if ($nomorInduk === '') {
            return '--oleh <nomor induk> wajib';
        }
        $u = UserDirectory::byNomorInduk([$nomorInduk])[$nomorInduk] ?? null;
        if ($u === null || $u['status'] !== 'active' || ! in_array($u['global_role'], ['superadmin', 'dosen'], true)) {
            return "--oleh {$nomorInduk} bukan superadmin atau dosen yang aktif";
        }
        if (UserDirectory::withAccess([(string) $u['id']], 'lokaujian') === []) {
            return "--oleh {$nomorInduk} tidak punya akses ke LokaUjian";
        }
        $kini      = gmdate('Y-m-d H:i:s');
        $identitas = new Identity((string) $u['id'], (string) $u['nomor_induk'], (string) $u['nama'], (string) $u['global_role'], 'id', false, false, false, $kini, $kini);
        Current::set($identitas);

        return new Akses($identitas);
    }
}
